-- ============================================================
-- MediCare Plus — Database Schema
-- MySQL 8.0+ / MariaDB 10.6+  |  Engine: InnoDB
-- Charset: utf8mb4  (full Unicode incl. emoji, multi-byte)
-- Collation: utf8mb4_unicode_ci  (case-insensitive sorting)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;

DROP DATABASE IF EXISTS medicare_plus;
CREATE DATABASE medicare_plus
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;
USE medicare_plus;

-- ── 1. users ─────────────────────────────────────────────────
-- Base authentication table shared by all three roles.
-- Role-specific data lives in separate profile tables (patients, doctors)
-- to avoid NULL columns and satisfy 3NF.
CREATE TABLE users (
    id            INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    name          VARCHAR(120)    NOT NULL,
    email         VARCHAR(180)    NOT NULL,
    password_hash VARCHAR(255)    NOT NULL,
    role          ENUM('admin','doctor','patient') NOT NULL DEFAULT 'patient',
    phone         VARCHAR(20)         NULL,
    status        ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
    created_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP
                                          ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE  KEY uq_email  (email),
    INDEX   idx_role      (role),
    INDEX   idx_status    (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 2. patients ───────────────────────────────────────────────
-- Extended health profile; user_id is both PK and FK (1-to-1 with users).
CREATE TABLE patients (
    user_id                  INT UNSIGNED NOT NULL,
    dob                      DATE             NULL,
    gender                   ENUM('male','female','other') NULL,
    blood_group              ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-') NULL,
    allergies                TEXT             NULL,
    address                  TEXT             NULL,
    emergency_contact_name   VARCHAR(120)     NULL,
    emergency_contact_phone  VARCHAR(20)      NULL,
    PRIMARY KEY (user_id),
    CONSTRAINT fk_pat_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 3. services ───────────────────────────────────────────────
-- Medical specialties / departments offered by the hospital.
CREATE TABLE services (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name        VARCHAR(100) NOT NULL,
    slug        VARCHAR(100) NOT NULL,
    description TEXT             NULL,
    icon        VARCHAR(60)      NULL,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 4. doctors ────────────────────────────────────────────────
CREATE TABLE doctors (
    id               INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    user_id          INT UNSIGNED    NOT NULL,
    service_id       INT UNSIGNED    NOT NULL,
    specialization   VARCHAR(150)    NOT NULL,
    experience_years TINYINT UNSIGNED NOT NULL DEFAULT 0,
    qualifications   TEXT                NULL,
    fee              DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    bio              TEXT                NULL,
    photo            VARCHAR(255)        NULL,
    location         VARCHAR(150)        NULL,
    is_featured      TINYINT(1)      NOT NULL DEFAULT 0,
    created_at       TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE  KEY uq_user    (user_id),
    INDEX   idx_service    (service_id),
    INDEX   idx_featured   (is_featured),
    INDEX   idx_location   (location(50)),
    CONSTRAINT fk_doc_user    FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_doc_service FOREIGN KEY (service_id)
        REFERENCES services(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 5. doctor_availability ────────────────────────────────────
CREATE TABLE doctor_availability (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    doctor_id    INT UNSIGNED NOT NULL,
    day_of_week  ENUM('Monday','Tuesday','Wednesday',
                      'Thursday','Friday','Saturday','Sunday') NOT NULL,
    start_time   TIME         NOT NULL,
    end_time     TIME         NOT NULL,
    slot_minutes TINYINT UNSIGNED NOT NULL DEFAULT 30,
    PRIMARY KEY (id),
    INDEX idx_doc_day (doctor_id, day_of_week),
    CONSTRAINT fk_avail_doc FOREIGN KEY (doctor_id)
        REFERENCES doctors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 6. appointments ───────────────────────────────────────────
-- UNIQUE(doctor_id, appt_date, appt_time) prevents double-booking
-- at the database engine level — PHP checks alone are not safe under
-- concurrent requests.
CREATE TABLE appointments (
    id          INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    ref_no      VARCHAR(20)   NOT NULL,
    patient_id  INT UNSIGNED  NOT NULL,
    doctor_id   INT UNSIGNED  NOT NULL,
    appt_date   DATE          NOT NULL,
    appt_time   TIME          NOT NULL,
    fee         DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status      ENUM('pending','confirmed','completed','cancelled')
                              NOT NULL DEFAULT 'pending',
    reason      TEXT              NULL,
    notes       TEXT              NULL,
    created_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP
                                         ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_ref         (ref_no),
    UNIQUE KEY uq_slot        (doctor_id, appt_date, appt_time),
    INDEX idx_patient         (patient_id),
    INDEX idx_doctor_date     (doctor_id, appt_date),
    INDEX idx_status          (status),
    CONSTRAINT fk_appt_patient FOREIGN KEY (patient_id)
        REFERENCES patients(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_appt_doctor  FOREIGN KEY (doctor_id)
        REFERENCES doctors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 7. reports ────────────────────────────────────────────────
-- Files stored OUTSIDE web root; served via PHP download controller.
CREATE TABLE reports (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    patient_id     INT UNSIGNED NOT NULL,
    doctor_id      INT UNSIGNED     NULL,
    appointment_id INT UNSIGNED     NULL,
    title          VARCHAR(255) NOT NULL,
    type           ENUM('lab','prescription','summary','imaging','other')
                                NOT NULL DEFAULT 'lab',
    file_path      VARCHAR(500) NOT NULL,
    file_name      VARCHAR(255) NOT NULL,
    file_size      INT UNSIGNED     NULL,
    mime_type      VARCHAR(100)     NULL,
    uploaded_by    INT UNSIGNED NOT NULL,
    created_at     TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_patient  (patient_id),
    INDEX idx_doctor   (doctor_id),
    CONSTRAINT fk_rep_patient  FOREIGN KEY (patient_id)
        REFERENCES patients(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_rep_doctor   FOREIGN KEY (doctor_id)
        REFERENCES doctors(id) ON DELETE SET NULL,
    CONSTRAINT fk_rep_appt     FOREIGN KEY (appointment_id)
        REFERENCES appointments(id) ON DELETE SET NULL,
    CONSTRAINT fk_rep_uploader FOREIGN KEY (uploaded_by)
        REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 8. message_threads ────────────────────────────────────────
CREATE TABLE message_threads (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    patient_id INT UNSIGNED NOT NULL,
    doctor_id  INT UNSIGNED NOT NULL,
    subject    VARCHAR(255) NOT NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_patient (patient_id),
    INDEX idx_doctor  (doctor_id),
    CONSTRAINT fk_thr_patient FOREIGN KEY (patient_id)
        REFERENCES patients(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_thr_doctor  FOREIGN KEY (doctor_id)
        REFERENCES doctors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 9. messages ───────────────────────────────────────────────
CREATE TABLE messages (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    thread_id  INT UNSIGNED NOT NULL,
    sender_id  INT UNSIGNED NOT NULL,
    body       TEXT         NOT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_thread (thread_id),
    INDEX idx_read   (is_read),
    CONSTRAINT fk_msg_thread FOREIGN KEY (thread_id)
        REFERENCES message_threads(id) ON DELETE CASCADE,
    CONSTRAINT fk_msg_sender FOREIGN KEY (sender_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 10. ratings ───────────────────────────────────────────────
-- UNIQUE(appointment_id) = one review per completed appointment.
CREATE TABLE ratings (
    id             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    appointment_id INT UNSIGNED  NOT NULL,
    patient_id     INT UNSIGNED  NOT NULL,
    doctor_id      INT UNSIGNED  NOT NULL,
    stars          TINYINT UNSIGNED NOT NULL,
    review         TEXT              NULL,
    is_approved    TINYINT(1)    NOT NULL DEFAULT 1,
    created_at     TIMESTAMP     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_appt      (appointment_id),
    INDEX      idx_doctor   (doctor_id),
    INDEX      idx_approved (is_approved),
    CONSTRAINT chk_stars CHECK (stars BETWEEN 1 AND 5),
    CONSTRAINT fk_rat_appt    FOREIGN KEY (appointment_id)
        REFERENCES appointments(id) ON DELETE CASCADE,
    CONSTRAINT fk_rat_patient FOREIGN KEY (patient_id)
        REFERENCES patients(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_rat_doctor  FOREIGN KEY (doctor_id)
        REFERENCES doctors(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 11. error_logs ────────────────────────────────────────────
CREATE TABLE error_logs (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    level      ENUM('error','warning','info') NOT NULL DEFAULT 'error',
    message    TEXT         NOT NULL,
    context    JSON             NULL,
    user_id    INT UNSIGNED     NULL,
    ip_address VARCHAR(45)      NULL,
    user_agent TEXT             NULL,
    created_at TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_level   (level),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
