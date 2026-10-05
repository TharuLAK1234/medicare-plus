<?php
class MessageController
{
    // GET /messages  — thread list for current user
    public function index(): void
    {
        Middleware::requireAuth();
        $role    = Auth::role();
        $threads = Message::threadsForUser(Auth::id(), $role);

        $view = $role === 'doctor' ? 'doctor.messages' : 'patient.messages';
        view($view, [
            'title'   => 'Messages — MediCare Plus',
            'threads' => $threads,
            'thread'  => null,
            'msgs'    => [],
        ]);
    }

    // GET /messages/:id  — open a thread
    public function show(int $threadId): void
    {
        Middleware::requireAuth();

        $thread = Message::thread($threadId);
        if (!$thread) {
            http_response_code(404);
            view('errors.404', ['title' => 'Thread Not Found']);
            return;
        }

        // Ownership check
        $uid = Auth::id();
        $doc = Auth::role() === 'doctor' ? Doctor::findByUserId($uid) : null;
        $allowed = ($thread['patient_id'] == $uid) ||
                   ($doc && $thread['doctor_id'] == $doc['id']) ||
                   Auth::role() === 'admin';

        if (!$allowed) {
            http_response_code(403);
            view('errors.403', ['title' => 'Access Denied']);
            return;
        }

        Message::markRead($threadId, $uid);
        $msgs    = Message::inThread($threadId);
        $threads = Message::threadsForUser($uid, Auth::role());

        $view = Auth::role() === 'doctor' ? 'doctor.messages' : 'patient.messages';
        view($view, [
            'title'   => 'Messages — MediCare Plus',
            'threads' => $threads,
            'thread'  => $thread,
            'msgs'    => $msgs,
        ]);
    }

    // POST /messages/new  — patient starts a new thread
    public function newThread(): void
    {
        Middleware::requireRole('patient');
        CSRF::verifyOrFail();

        $doctorId = (int)($_POST['doctor_id'] ?? 0);
        $subject  = trim($_POST['subject'] ?? '');
        $body     = trim($_POST['body']    ?? '');

        if (!$doctorId || !$subject || !$body) {
            Session::flash('error', 'Doctor, subject and message are all required.');
            redirect(url('messages'));
            return;
        }

        $doctor = Doctor::findById($doctorId);
        if (!$doctor) { redirect(url('messages')); return; }

        $threadId = Message::createThread(Auth::id(), $doctorId, $subject);
        Message::send($threadId, Auth::id(), $body);

        Session::flash('success', 'Message sent to Dr. ' . $doctor['name'] . '.');
        redirect(url('messages/' . $threadId));
    }

    // POST /messages/:id/reply  — reply to a thread
    public function reply(int $threadId): void
    {
        Middleware::requireAuth();
        CSRF::verifyOrFail();

        $thread = Message::thread($threadId);
        if (!$thread) { redirect(url('messages')); return; }

        $uid  = Auth::id();
        $doc  = Auth::role() === 'doctor' ? Doctor::findByUserId($uid) : null;
        $ok   = ($thread['patient_id'] == $uid) ||
                ($doc && $thread['doctor_id'] == $doc['id']);

        if (!$ok) { http_response_code(403); exit('Forbidden'); }

        $body = trim($_POST['body'] ?? '');
        if (!$body) {
            redirect(url('messages/' . $threadId));
            return;
        }

        Message::send($threadId, $uid, $body);
        redirect(url('messages/' . $threadId));
    }
}
