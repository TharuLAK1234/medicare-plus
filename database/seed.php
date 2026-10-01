<?php
/**
 * Database Seeder
 *
 * Run ONCE from the browser: http://localhost/medicare-plus/database/seed.php
 * DELETE this file after running — leaving a seeder publicly accessible
 * on a production server is a security risk.
 *
 * Seed data:
 *   1 admin · 7 doctors · 5 patients · 8 services
 *   availability schedules · 12 appointments · 5 reports
 *   3 message threads · 6 messages · 4 ratings
 *
 * All seed user passwords: Password@123
 */

declare(strict_types=1);
require_once dirname(__DIR__) . '/config/config.php';
require_once CORE_PATH . '/Database.php';

// ── Guard: prevent re-running ─────────────────────────────────────────────
$existing = Database::queryOne("SELECT COUNT(*) AS n FROM users");
if ((int)($existing['n'] ?? 0) > 0) {
    die('<p style="font-family:sans-serif;color:red"><b>Already seeded.</b> '
      . 'Drop and recreate the database, re-import schema.sql, then run again.</p>');
}

$hash = password_hash('Password@123', PASSWORD_BCRYPT, ['cost' => 12]);

Database::transaction(function () use ($hash) {

    // ── Services ─────────────────────────────────────────────────────────
    $services = [
        ['Cardiology',       'cardiology',       'bi-heart-pulse',    'Diagnosis and treatment of heart conditions and cardiovascular disease.'],
        ['Pediatrics',       'pediatrics',       'bi-emoji-smile',    'Medical care for infants, children, and adolescents.'],
        ['Radiology',        'radiology',        'bi-radioactive',    'Medical imaging including X-ray, MRI, CT scans, and ultrasound.'],
        ['Dermatology',      'dermatology',      'bi-person',         'Treatment of skin, hair, and nail conditions.'],
        ['Orthopedics',      'orthopedics',      'bi-bandaid',        'Musculoskeletal disorders, fractures, and joint replacement surgery.'],
        ['General Medicine', 'general-medicine', 'bi-clipboard2-pulse','Routine check-ups and management of common illnesses.'],
        ['Neurology',        'neurology',        'bi-cpu',            'Disorders of the brain, spinal cord, and peripheral nervous system.'],
        ['Gynecology',       'gynecology',       'bi-gender-female',  'Women\'s reproductive health, obstetrics, and prenatal care.'],
    ];

    $serviceIds = [];
    foreach ($services as [$name, $slug, $icon, $desc]) {
        $serviceIds[$slug] = (int)Database::insert(
            "INSERT INTO services (name, slug, icon, description) VALUES (?,?,?,?)",
            [$name, $slug, $icon, $desc]
        );
    }

    // ── Admin ─────────────────────────────────────────────────────────────
    Database::insert(
        "INSERT INTO users (name, email, password_hash, role, phone) VALUES (?,?,?,?,?)",
        ['System Admin', 'admin@medicare.lk', $hash, 'admin', '+94112345678']
    );

    // ── Doctor users + profiles ───────────────────────────────────────────
    $doctorData = [
        [
            'name' => 'Dr. Amara Perera',       'email' => 'amara@medicare.lk',
            'phone' => '+94771234001',           'slug'  => 'cardiology',
            'spec' => 'Cardiologist',            'exp'   => 12,
            'quals'=> 'MBBS (University of Colombo), MD Cardiology (PGIM), Fellow of Sri Lanka College of Cardiology',
            'fee'  => 2500.00, 'loc' => 'Colombo', 'featured' => 1,
            'bio'  => 'Senior cardiologist specialising in interventional cardiology and heart failure management.',
        ],
        [
            'name' => 'Dr. Nimal Silva',         'email' => 'nimal@medicare.lk',
            'phone' => '+94771234002',           'slug'  => 'pediatrics',
            'spec' => 'Pediatrician',            'exp'   => 8,
            'quals'=> 'MBBS (University of Kelaniya), DCH (UK), MRCPCH',
            'fee'  => 1800.00, 'loc' => 'Kandy', 'featured' => 1,
            'bio'  => 'Specialist in childhood infections, developmental disorders, and neonatal care.',
        ],
        [
            'name' => 'Dr. Kumari Fernando',    'email' => 'kumari@medicare.lk',
            'phone' => '+94771234003',           'slug'  => 'dermatology',
            'spec' => 'Dermatologist',           'exp'   => 10,
            'quals'=> 'MBBS (University of Colombo), MD Dermatology (PGIM)',
            'fee'  => 2200.00, 'loc' => 'Colombo', 'featured' => 1,
            'bio'  => 'Expert in acne, eczema, psoriasis, and cosmetic dermatology procedures.',
        ],
        [
            'name' => 'Dr. Roshan Jayawardena', 'email' => 'roshan@medicare.lk',
            'phone' => '+94771234004',           'slug'  => 'orthopedics',
            'spec' => 'Orthopaedic Surgeon',     'exp'   => 15,
            'quals'=> 'MBBS (University of Colombo), MS Orthopaedics (PGIM), FRCS (Edinburgh)',
            'fee'  => 3500.00, 'loc' => 'Galle', 'featured' => 0,
            'bio'  => 'Specialises in knee and hip joint replacement and sports injury rehabilitation.',
        ],
        [
            'name' => 'Dr. Tharaka Bandara',    'email' => 'tharaka@medicare.lk',
            'phone' => '+94771234005',           'slug'  => 'radiology',
            'spec' => 'Radiologist',             'exp'   => 7,
            'quals'=> 'MBBS (University of Ruhuna), MD Radiology (PGIM)',
            'fee'  => 2000.00, 'loc' => 'Colombo', 'featured' => 0,
            'bio'  => 'Diagnostic imaging specialist with expertise in MRI, CT, and interventional radiology.',
        ],
        [
            'name' => 'Dr. Sanduni Wickramasinghe', 'email' => 'sanduni@medicare.lk',
            'phone' => '+94771234006',           'slug'  => 'general-medicine',
            'spec' => 'General Practitioner',    'exp'   => 5,
            'quals'=> 'MBBS (University of Colombo), MRCP (UK)',
            'fee'  => 1200.00, 'loc' => 'Kandy', 'featured' => 1,
            'bio'  => 'Holistic primary care physician focused on preventive health and chronic disease management.',
        ],
        [
            'name' => 'Dr. Pradeep Rajapaksa',  'email' => 'pradeep@medicare.lk',
            'phone' => '+94771234007',           'slug'  => 'neurology',
            'spec' => 'Neurologist',             'exp'   => 11,
            'quals'=> 'MBBS (University of Colombo), MD Neurology (PGIM), FRCP (London)',
            'fee'  => 3000.00, 'loc' => 'Colombo', 'featured' => 1,
            'bio'  => 'Specialist in stroke management, epilepsy, Parkinson\'s disease, and movement disorders.',
        ],
    ];

    $doctorIds = [];
    foreach ($doctorData as $d) {
        $uid = (int)Database::insert(
            "INSERT INTO users (name, email, password_hash, role, phone) VALUES (?,?,?,?,?)",
            [$d['name'], $d['email'], $hash, 'doctor', $d['phone']]
        );
        $did = (int)Database::insert(
            "INSERT INTO doctors
             (user_id, service_id, specialization, experience_years,
              qualifications, fee, bio, location, is_featured)
             VALUES (?,?,?,?,?,?,?,?,?)",
            [$uid, $serviceIds[$d['slug']], $d['spec'], $d['exp'],
             $d['quals'], $d['fee'], $d['bio'], $d['loc'], $d['featured']]
        );
        $doctorIds[$d['email']] = $did;
    }

    // ── Availability schedules ────────────────────────────────────────────
    $availability = [
        ['amara@medicare.lk',   'Monday',    '09:00:00', '13:00:00', 30],
        ['amara@medicare.lk',   'Wednesday', '14:00:00', '17:00:00', 30],
        ['amara@medicare.lk',   'Thursday',  '09:00:00', '12:00:00', 30],
        ['nimal@medicare.lk',   'Tuesday',   '08:00:00', '12:00:00', 30],
        ['nimal@medicare.lk',   'Friday',    '13:00:00', '17:00:00', 30],
        ['kumari@medicare.lk',  'Monday',    '10:00:00', '14:00:00', 30],
        ['kumari@medicare.lk',  'Saturday',  '08:00:00', '12:00:00', 30],
        ['roshan@medicare.lk',  'Wednesday', '08:00:00', '13:00:00', 45],
        ['tharaka@medicare.lk', 'Tuesday',   '09:00:00', '13:00:00', 30],
        ['tharaka@medicare.lk', 'Thursday',  '14:00:00', '17:00:00', 30],
        ['sanduni@medicare.lk', 'Monday',    '08:00:00', '17:00:00', 15],
        ['sanduni@medicare.lk', 'Wednesday', '08:00:00', '17:00:00', 15],
        ['sanduni@medicare.lk', 'Friday',    '08:00:00', '13:00:00', 15],
        ['pradeep@medicare.lk', 'Thursday',  '09:00:00', '13:00:00', 30],
        ['pradeep@medicare.lk', 'Saturday',  '08:00:00', '11:00:00', 30],
    ];

    foreach ($availability as [$email, $day, $start, $end, $slot]) {
        Database::execute(
            "INSERT INTO doctor_availability
             (doctor_id, day_of_week, start_time, end_time, slot_minutes)
             VALUES (?,?,?,?,?)",
            [$doctorIds[$email], $day, $start, $end, $slot]
        );
    }

    // ── Patients ──────────────────────────────────────────────────────────
    $patientData = [
        ['Kasun Dissanayake',   'kasun@gmail.com',  '+94701234001',
         '1992-05-14', 'male',   'B+',  'Penicillin',  'No. 12, Galle Road, Colombo 05',
         'Nimali Dissanayake',  '+94701234099'],
        ['Amali Perera',        'amali@gmail.com',  '+94701234002',
         '1988-11-23', 'female', 'O+',  'None',        '45/A, Peradeniya Road, Kandy',
         'Ranjith Perera',      '+94701234088'],
        ['Ruwan Thilakarathne', 'ruwan@gmail.com',  '+94701234003',
         '1995-03-08', 'male',   'A-',  'Sulfa drugs', '78, Matara Road, Galle',
         'Sunethra Silva',      '+94701234077'],
        ['Kamala Senanayake',   'kamala@gmail.com', '+94701234004',
         '1975-07-30', 'female', 'AB+', 'Aspirin',     '22, Baudhaloka Mawatha, Colombo 03',
         'Priyantha Senanayake','+94701234066'],
        ['Dinesh Rathnayake',   'dinesh@gmail.com', '+94701234005',
         '2001-09-12', 'male',   'O-',  'None',        '5, Beach Road, Matara',
         'Sumedha Rathnayake',  '+94701234055'],
    ];

    $patientIds = [];
    foreach ($patientData as [$name,$email,$phone,$dob,$gender,$bg,$allergy,$addr,$ecName,$ecPhone]) {
        $uid = (int)Database::insert(
            "INSERT INTO users (name, email, password_hash, role, phone) VALUES (?,?,?,?,?)",
            [$name, $email, $hash, 'patient', $phone]
        );
        Database::execute(
            "INSERT INTO patients
             (user_id, dob, gender, blood_group, allergies, address,
              emergency_contact_name, emergency_contact_phone)
             VALUES (?,?,?,?,?,?,?,?)",
            [$uid, $dob, $gender, $bg, $allergy, $addr, $ecName, $ecPhone]
        );
        $patientIds[$email] = $uid;
    }

    // ── Appointments ──────────────────────────────────────────────────────
    $appts = [
        ['MP-2024-00101','kasun@gmail.com', 'amara@medicare.lk',  '2024-09-10','09:00:00','completed','Chest pain follow-up'],
        ['MP-2024-00102','amali@gmail.com', 'nimal@medicare.lk',  '2024-09-12','08:30:00','completed','Child vaccination review'],
        ['MP-2024-00103','ruwan@gmail.com', 'roshan@medicare.lk', '2024-09-18','08:00:00','completed','Knee pain assessment'],
        ['MP-2024-00104','kamala@gmail.com','kumari@medicare.lk', '2024-09-20','10:00:00','completed','Skin rash assessment'],
        ['MP-2024-00201','dinesh@gmail.com','sanduni@medicare.lk','2024-10-01','08:00:00','cancelled', 'General check-up'],
        ['MP-2024-00202','kasun@gmail.com', 'pradeep@medicare.lk','2024-10-03','09:00:00','completed','Headaches and dizziness'],
        ['MP-2024-00301','amali@gmail.com', 'amara@medicare.lk',  '2024-10-17','09:30:00','confirmed','ECG review'],
        ['MP-2024-00302','kasun@gmail.com', 'amara@medicare.lk',  '2024-10-17','10:00:00','confirmed','Blood pressure monitoring'],
        ['MP-2024-00303','ruwan@gmail.com', 'tharaka@medicare.lk','2024-10-22','09:00:00','pending',  'X-ray results review'],
        ['MP-2024-00304','kamala@gmail.com','sanduni@medicare.lk','2024-10-23','08:00:00','pending',  'Routine check-up'],
        ['MP-2024-00305','dinesh@gmail.com','nimal@medicare.lk',  '2024-10-25','08:30:00','pending',  'Annual physical examination'],
        ['MP-2024-00306','ruwan@gmail.com', 'roshan@medicare.lk', '2024-10-30','08:00:00','pending',  'Post-surgery follow-up'],
    ];

    $apptIds = [];
    foreach ($appts as [$ref,$patEmail,$docEmail,$date,$time,$status,$reason]) {
        $aid = (int)Database::insert(
            "INSERT INTO appointments
             (ref_no, patient_id, doctor_id, appt_date, appt_time, status, reason)
             VALUES (?,?,?,?,?,?,?)",
            [$ref, $patientIds[$patEmail], $doctorIds[$docEmail], $date, $time, $status, $reason]
        );
        $apptIds[$ref] = $aid;
    }

    // ── Reports ───────────────────────────────────────────────────────────
    $adminId = 1; // admin is always the first inserted user
    $reports = [
        [$patientIds['kasun@gmail.com'],  $doctorIds['amara@medicare.lk'],  $apptIds['MP-2024-00101'],
         'ECG & Lipid Panel Results',       'lab',          'reports/sample_ecg.pdf',       'ECG_Lipid_Panel_Sep24.pdf'],
        [$patientIds['kasun@gmail.com'],  $doctorIds['amara@medicare.lk'],  $apptIds['MP-2024-00101'],
         'Cardiology Prescription',         'prescription', 'reports/sample_rx.pdf',        'Prescription_Sep24.pdf'],
        [$patientIds['amali@gmail.com'],  $doctorIds['nimal@medicare.lk'],  $apptIds['MP-2024-00102'],
         'Child Vaccination Record',        'summary',      'reports/sample_vaccine.pdf',   'Vaccination_Oct24.pdf'],
        [$patientIds['kamala@gmail.com'], $doctorIds['kumari@medicare.lk'], $apptIds['MP-2024-00104'],
         'Skin Biopsy Report',              'lab',          'reports/sample_biopsy.pdf',    'Biopsy_Result_Sep24.pdf'],
        [$patientIds['kasun@gmail.com'],  $doctorIds['pradeep@medicare.lk'],$apptIds['MP-2024-00202'],
         'MRI Brain Imaging Report',        'imaging',      'reports/sample_mri.pdf',       'MRI_Brain_Oct24.pdf'],
    ];

    foreach ($reports as [$pid, $did, $apptId, $title, $type, $path, $fname]) {
        Database::execute(
            "INSERT INTO reports
             (patient_id, doctor_id, appointment_id, title, type,
              file_path, file_name, file_size, mime_type, uploaded_by)
             VALUES (?,?,?,?,?,?,?,?,?,?)",
            [$pid, $did, $apptId, $title, $type,
             $path, $fname, 204800, 'application/pdf', $adminId]
        );
    }

    // ── Message threads + messages ────────────────────────────────────────
    $t1 = (int)Database::insert(
        "INSERT INTO message_threads (patient_id, doctor_id, subject) VALUES (?,?,?)",
        [$patientIds['kasun@gmail.com'], $doctorIds['amara@medicare.lk'],
         'Follow-up: Blood pressure medication']
    );
    $t2 = (int)Database::insert(
        "INSERT INTO message_threads (patient_id, doctor_id, subject) VALUES (?,?,?)",
        [$patientIds['amali@gmail.com'], $doctorIds['nimal@medicare.lk'],
         "Regarding child's persistent fever"]
    );
    $t3 = (int)Database::insert(
        "INSERT INTO message_threads (patient_id, doctor_id, subject) VALUES (?,?,?)",
        [$patientIds['kamala@gmail.com'], $doctorIds['kumari@medicare.lk'],
         'Skin condition follow-up query']
    );

    // Fetch doctor user IDs for message sender attribution
    $getDocUserId = function (int $doctorId): int {
        $row = Database::queryOne(
            "SELECT user_id FROM doctors WHERE id = ?", [$doctorId]
        );
        return (int)($row['user_id'] ?? 0);
    };

    $amaraUserId  = $getDocUserId($doctorIds['amara@medicare.lk']);
    $nimalUserId  = $getDocUserId($doctorIds['nimal@medicare.lk']);
    $kumariUserId = $getDocUserId($doctorIds['kumari@medicare.lk']);

    $msgs = [
        [$t1, $patientIds['kasun@gmail.com'], 'Doctor, I have been experiencing dizziness since starting the new medication. Should I stop taking it?', 1],
        [$t1, $amaraUserId,                   'Please do not stop the medication suddenly. Reduce to half the dose and monitor for 3 days. If dizziness continues, visit the clinic immediately.', 1],
        [$t1, $patientIds['kasun@gmail.com'], 'Understood, thank you doctor. I will monitor and book an appointment if symptoms persist.', 0],
        [$t2, $patientIds['amali@gmail.com'], 'My daughter has had a fever of 38.5°C for two days and has lost her appetite. Should I be worried?', 1],
        [$t2, $nimalUserId,                   'Please give Paracetamol 250mg every 6 hours and ensure she stays hydrated. If the fever exceeds 39°C or continues beyond tomorrow, bring her in immediately.', 0],
        [$t3, $patientIds['kamala@gmail.com'],'The rash has spread slightly since my last visit. I have noticed it is worse after eating certain foods.', 0],
    ];

    foreach ($msgs as [$tid, $sid, $body, $read]) {
        Database::execute(
            "INSERT INTO messages (thread_id, sender_id, body, is_read) VALUES (?,?,?,?)",
            [$tid, $sid, $body, $read]
        );
    }

    // ── Ratings (completed appointments only) ─────────────────────────────
    $ratings = [
        [$apptIds['MP-2024-00101'], $patientIds['kasun@gmail.com'],  $doctorIds['amara@medicare.lk'],
         5, 'Dr. Perera was extremely thorough and took time to explain every detail of my condition. Highly recommend.'],
        [$apptIds['MP-2024-00102'], $patientIds['amali@gmail.com'],  $doctorIds['nimal@medicare.lk'],
         5, 'My daughter loves Dr. Silva. He is very gentle and patient with children. We always feel at ease.'],
        [$apptIds['MP-2024-00103'], $patientIds['ruwan@gmail.com'],  $doctorIds['roshan@medicare.lk'],
         4, 'Very professional consultation. Waiting time was a bit long but the treatment advice was excellent.'],
        [$apptIds['MP-2024-00202'], $patientIds['kasun@gmail.com'],  $doctorIds['pradeep@medicare.lk'],
         4, 'Dr. Rajapaksa explained my MRI results clearly and put my mind at ease. Would recommend.'],
    ];

    foreach ($ratings as [$aid, $pid, $did, $stars, $review]) {
        Database::execute(
            "INSERT INTO ratings
             (appointment_id, patient_id, doctor_id, stars, review)
             VALUES (?,?,?,?,?)",
            [$aid, $pid, $did, $stars, $review]
        );
    }

}); // end transaction

echo '<!DOCTYPE html><html><body style="font-family:sans-serif;max-width:600px;margin:40px auto;padding:20px">';
echo '<h2 style="color:green">✅ MediCare Plus seeded successfully!</h2>';
echo '<table border="1" cellpadding="8" cellspacing="0" style="border-collapse:collapse;width:100%">';
echo '<tr><th>Role</th><th>Email</th><th>Password</th></tr>';
echo '<tr><td>Admin</td><td>admin@medicare.lk</td><td>Password@123</td></tr>';
echo '<tr><td>Doctor</td><td>amara@medicare.lk</td><td>Password@123</td></tr>';
echo '<tr><td>Doctor</td><td>nimal@medicare.lk</td><td>Password@123</td></tr>';
echo '<tr><td>Patient</td><td>kasun@gmail.com</td><td>Password@123</td></tr>';
echo '<tr><td>Patient</td><td>amali@gmail.com</td><td>Password@123</td></tr>';
echo '</table>';
echo '<p style="color:red;margin-top:20px"><strong>⚠️ Delete this file now from your server!</strong></p>';
echo '</body></html>';
