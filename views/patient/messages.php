<?php
require VIEW_PATH . '/layouts/header.php';
require VIEW_PATH . '/layouts/navbar.php';
?>

<div class="mp-page-wrap">
<div class="container-xl">

  <div class="d-flex align-items-center justify-content-between mb-4">
    <h1 style="font-size:1.5rem;margin:0">Messages</h1>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newThreadModal">
      <i class="bi bi-plus-lg me-1"></i> New Message
    </button>
  </div>

  <div class="row g-4" style="min-height:500px">

    <!-- Thread list -->
    <div class="col-lg-4">
      <div class="mp-card" style="padding:0;overflow:hidden">
        <div class="p-3 border-bottom" style="font-weight:700;font-size:.875rem">Conversations</div>
        <?php if (empty($threads)): ?>
          <div class="text-center py-5" style="color:var(--mp-text-3);font-size:.875rem">
            <i class="bi bi-chat-dots fs-2 d-block mb-2 opacity-25"></i>
            No conversations yet.
          </div>
        <?php else: ?>
          <?php foreach ($threads as $t):
            $isActive = $thread && $thread['id'] == $t['id'];
          ?>
          <a href="<?= url('messages/' . $t['id']) ?>" style="text-decoration:none">
            <div class="p-3 border-bottom <?= $isActive ? '' : '' ?>"
                 style="background:<?= $isActive ? 'var(--mp-primary-light)' : 'transparent' ?>;
                        transition:background .1s"
                 onmouseover="<?= $isActive ? '' : "this.style.background='#f8fafc'" ?>"
                 onmouseout="<?= $isActive ? '' : "this.style.background='transparent'" ?>">
              <div class="d-flex justify-content-between align-items-start">
                <div style="font-weight:600;font-size:.875rem;color:var(--mp-text-1)">
                  Dr. <?= e($t['other_name']) ?>
                </div>
                <?php if ($t['unread'] > 0): ?>
                <span style="background:var(--mp-primary);color:#fff;border-radius:9999px;
                             font-size:.7rem;font-weight:700;padding:.15rem .5rem">
                  <?= $t['unread'] ?>
                </span>
                <?php endif; ?>
              </div>
              <div style="font-size:.78rem;color:var(--mp-primary);margin:.1rem 0 .25rem">
                <?= e($t['specialization'] ?? '') ?>
              </div>
              <div style="font-size:.78rem;color:var(--mp-text-3);
                          white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                <?= $t['subject'] ?>
              </div>
              <?php if ($t['last_msg']): ?>
              <div style="font-size:.75rem;color:var(--mp-text-3);margin-top:.15rem;
                          white-space:nowrap;overflow:hidden;text-overflow:ellipsis">
                <?= e(mb_substr($t['last_msg'], 0, 60)) ?>
              </div>
              <?php endif; ?>
              <?php if ($t['last_at']): ?>
              <div style="font-size:.7rem;color:var(--mp-text-3);margin-top:.2rem;text-align:right">
                <?= formatDate($t['last_at']) ?>
              </div>
              <?php endif; ?>
            </div>
          </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>

    <!-- Message pane -->
    <div class="col-lg-8">
      <?php if ($thread): ?>
      <div class="mp-card" style="padding:0;overflow:hidden;display:flex;flex-direction:column;height:100%">
        <!-- Header -->
        <div class="p-4 border-bottom d-flex justify-content-between align-items-center"
             style="flex-shrink:0">
          <div>
            <div style="font-weight:700">Dr. <?= e($thread['doctor_name']) ?></div>
            <div style="font-size:.8rem;color:var(--mp-text-3)"><?= e($thread['subject']) ?></div>
          </div>
          <span class="mp-badge teal"><?= e($thread['specialization'] ?? '') ?></span>
        </div>
        <!-- Messages scroll -->
        <div id="msgScroll" style="flex:1;overflow-y:auto;padding:1.25rem;min-height:300px;max-height:420px">
          <?php foreach ($msgs as $m):
            $mine = $m['sender_id'] == Auth::id();
          ?>
          <div class="d-flex mb-3 <?= $mine ? 'justify-content-end' : '' ?>">
            <div style="max-width:75%">
              <?php if (!$mine): ?>
              <div style="font-size:.72rem;color:var(--mp-text-3);margin-bottom:.2rem">
                Dr. <?= e($m['sender_name']) ?>
              </div>
              <?php endif; ?>
              <div style="background:<?= $mine ? 'var(--mp-primary)' : '#f1f5f9' ?>;
                          color:<?= $mine ? '#fff' : 'var(--mp-text-1)' ?>;
                          border-radius:<?= $mine ? '14px 14px 4px 14px' : '14px 14px 14px 4px' ?>;
                          padding:.65rem 1rem;font-size:.875rem;line-height:1.6">
                <?= nl2br(e($m['body'])) ?>
              </div>
              <div style="font-size:.7rem;color:var(--mp-text-3);margin-top:.2rem;
                          text-align:<?= $mine ? 'right' : 'left' ?>">
                <?= date('d M H:i', strtotime($m['created_at'])) ?>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <!-- Reply box -->
        <div class="p-3 border-top" style="flex-shrink:0">
          <form method="POST" action="<?= url('messages/' . $thread['id'] . '/reply') ?>"
                class="d-flex gap-2">
            <?= CSRF::field() ?>
            <textarea name="body" class="form-control" rows="2"
                      placeholder="Type your message…" required
                      style="resize:none;border-radius:10px"></textarea>
            <button type="submit" class="btn btn-primary align-self-end px-3">
              <i class="bi bi-send-fill"></i>
            </button>
          </form>
        </div>
      </div>
      <?php else: ?>
      <div class="mp-card text-center" style="padding:4rem">
        <i class="bi bi-chat-text fs-1 d-block mb-3 opacity-25" style="color:var(--mp-text-3)"></i>
        <div style="font-weight:600">Select a conversation</div>
        <p style="color:var(--mp-text-3);font-size:.875rem;margin-bottom:1.5rem">
          Or start a new message with one of your doctors.
        </p>
        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newThreadModal">
          <i class="bi bi-plus-lg me-1"></i>New Message
        </button>
      </div>
      <?php endif; ?>
    </div>

  </div>
</div>
</div>

<!-- New Thread Modal -->
<div class="modal fade" id="newThreadModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title" style="font-family:var(--font-display);font-weight:700">
          New Message
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="<?= url('messages/new') ?>">
        <?= CSRF::field() ?>
        <div class="modal-body">
          <div class="mb-3">
            <label class="form-label" style="font-weight:600">Doctor</label>
            <?php $doctors = Doctor::allWithStats(); ?>
            <select name="doctor_id" class="form-select" required>
              <option value="">— Select doctor —</option>
              <?php foreach ($doctors as $d): ?>
              <option value="<?= $d['id'] ?>">Dr. <?= e($d['name']) ?> — <?= e($d['service_name']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label" style="font-weight:600">Subject</label>
            <input type="text" name="subject" class="form-control" required
                   placeholder="e.g. Follow-up question about my prescription">
          </div>
          <div class="mb-3">
            <label class="form-label" style="font-weight:600">Message</label>
            <textarea name="body" class="form-control" rows="4" required
                      placeholder="Write your message…"></textarea>
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-send me-1"></i>Send Message
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
// Auto-scroll to bottom of message pane
const ms = document.getElementById('msgScroll');
if (ms) ms.scrollTop = ms.scrollHeight;
</script>

<?php require VIEW_PATH . '/layouts/footer.php'; ?>
