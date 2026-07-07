-- ========================================
-- USERS & AUTHENTICATION
-- ========================================

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    role ENUM('admin','consultant','registrar','medical_officer','assistant_registrar','intern','nurse','reception','patient') NOT NULL,
    phone VARCHAR(20),
    on_duty BOOLEAN DEFAULT 0,
    last_login DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX(email),
    INDEX(role),
    INDEX(created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS user_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token VARCHAR(255) UNIQUE NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX(token),
    INDEX(expires_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- PATIENTS & DEMOGRAPHICS
-- ========================================

CREATE TABLE IF NOT EXISTS patients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id_unique VARCHAR(50) UNIQUE NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    full_name_bn VARCHAR(255),
    date_of_birth DATE,
    gender ENUM('male','female','other'),
    phone VARCHAR(20),
    email VARCHAR(255),
    address TEXT,
    blood_group VARCHAR(10),
    weight FLOAT,
    height FLOAT,
    allergies JSON,
    chronic_conditions JSON,
    past_surgical_history TEXT,
    patient_type ENUM('admitted','outdoor') DEFAULT 'outdoor',
    consultant_id INT,
    registration_fee DECIMAL(10,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    FOREIGN KEY(consultant_id) REFERENCES users(id),
    FOREIGN KEY(created_by) REFERENCES users(id),
    INDEX(patient_id_unique),
    INDEX(phone),
    INDEX(patient_type),
    FULLTEXT INDEX ft_fullname (full_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- ADMISSIONS & BEDS
-- ========================================

CREATE TABLE IF NOT EXISTS admissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    admission_date DATETIME NOT NULL,
    discharge_date DATETIME,
    bed_id INT,
    chief_complaint TEXT,
    admission_type ENUM('emergency','routine','transfer') DEFAULT 'routine',
    discharge_summary TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(patient_id) REFERENCES patients(id),
    FOREIGN KEY(bed_id) REFERENCES beds(id),
    FOREIGN KEY(created_by) REFERENCES users(id),
    INDEX(patient_id),
    INDEX(admission_date),
    INDEX(discharge_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS beds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bed_number VARCHAR(50) UNIQUE NOT NULL,
    bed_type ENUM('icu','hdu','cabin','general') NOT NULL,
    status ENUM('available','occupied','reserved','cleaning') DEFAULT 'available',
    patient_id INT,
    reserved_until DATETIME,
    cleaning_until DATETIME,
    updated_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY(patient_id) REFERENCES patients(id),
    FOREIGN KEY(updated_by) REFERENCES users(id),
    INDEX(bed_type),
    INDEX(status),
    INDEX(bed_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- VITALS & CLINICAL MONITORING
-- ========================================

CREATE TABLE IF NOT EXISTS vitals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    admission_id INT,
    blood_pressure VARCHAR(20),
    pulse INT,
    temperature FLOAT,
    respiratory_rate INT,
    oxygen_saturation INT,
    random_blood_sugar INT,
    gcs_score INT,
    status ENUM('drafted','pending_review','verified','rejected') DEFAULT 'drafted',
    recorded_at DATETIME NOT NULL,
    verified_by INT,
    verified_at DATETIME,
    recorded_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY(patient_id) REFERENCES patients(id),
    FOREIGN KEY(admission_id) REFERENCES admissions(id),
    FOREIGN KEY(recorded_by) REFERENCES users(id),
    FOREIGN KEY(verified_by) REFERENCES users(id),
    INDEX(patient_id),
    INDEX(recorded_at),
    INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- VISITS & CONSULTATIONS
-- ========================================

CREATE TABLE IF NOT EXISTS visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    admission_id INT,
    visit_date DATETIME NOT NULL,
    visit_type ENUM('outdoor','admitted') DEFAULT 'outdoor',
    chief_complaint TEXT NOT NULL,
    history_of_present_illness TEXT,
    physical_examination TEXT,
    diagnosis TEXT,
    notes TEXT,
    consultant_id INT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY(patient_id) REFERENCES patients(id),
    FOREIGN KEY(admission_id) REFERENCES admissions(id),
    FOREIGN KEY(consultant_id) REFERENCES users(id),
    FOREIGN KEY(created_by) REFERENCES users(id),
    INDEX(patient_id),
    INDEX(visit_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- SOAP NOTES & WARD ROUNDS
-- ========================================

CREATE TABLE IF NOT EXISTS soap_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    admission_id INT,
    round_date DATETIME NOT NULL,
    subjective TEXT,
    objective TEXT,
    assessment TEXT,
    plan TEXT,
    status ENUM('draft','intern_draft','mo_review','finalized') DEFAULT 'draft',
    intern_id INT,
    medical_officer_id INT,
    consultant_id INT,
    intern_completed_at DATETIME,
    mo_reviewed_at DATETIME,
    finalized_at DATETIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY(patient_id) REFERENCES patients(id),
    FOREIGN KEY(admission_id) REFERENCES admissions(id),
    FOREIGN KEY(intern_id) REFERENCES users(id),
    FOREIGN KEY(medical_officer_id) REFERENCES users(id),
    FOREIGN KEY(consultant_id) REFERENCES users(id),
    INDEX(patient_id),
    INDEX(round_date),
    INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- INVESTIGATIONS & LAB RESULTS
-- ========================================

CREATE TABLE IF NOT EXISTS investigations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    admission_id INT,
    test_name VARCHAR(255) NOT NULL,
    test_code VARCHAR(50),
    result_value VARCHAR(255),
    result_unit VARCHAR(50),
    reference_range VARCHAR(100),
    status ENUM('ordered','pending','completed','cancelled') DEFAULT 'ordered',
    ordered_date DATETIME,
    completed_date DATETIME,
    ordered_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY(patient_id) REFERENCES patients(id),
    FOREIGN KEY(admission_id) REFERENCES admissions(id),
    FOREIGN KEY(ordered_by) REFERENCES users(id),
    INDEX(patient_id),
    INDEX(test_code),
    INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- MEDICATIONS & PRESCRIPTIONS
-- ========================================

CREATE TABLE IF NOT EXISTS prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    visit_id INT,
    admission_id INT,
    prescription_date DATETIME NOT NULL,
    diagnosis TEXT,
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY(patient_id) REFERENCES patients(id),
    FOREIGN KEY(visit_id) REFERENCES visits(id),
    FOREIGN KEY(admission_id) REFERENCES admissions(id),
    FOREIGN KEY(created_by) REFERENCES users(id),
    INDEX(patient_id),
    INDEX(prescription_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS prescription_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id INT NOT NULL,
    medication_name VARCHAR(255) NOT NULL,
    dose VARCHAR(100),
    frequency VARCHAR(100),
    duration VARCHAR(100),
    instructions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(prescription_id) REFERENCES prescriptions(id) ON DELETE CASCADE,
    INDEX(prescription_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- CLINICAL ALERTS
-- ========================================

CREATE TABLE IF NOT EXISTS clinical_alerts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    alert_type ENUM('sepsis','aki','news2_high','hypoglycemia','hypertension','bradycardia','tachycardia') NOT NULL,
    severity ENUM('info','warning','critical') DEFAULT 'warning',
    triggered_by_vital_id INT,
    triggered_at DATETIME NOT NULL,
    acknowledged_at DATETIME,
    acknowledged_by INT,
    dismissed_at DATETIME,
    dismissed_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(patient_id) REFERENCES patients(id),
    FOREIGN KEY(triggered_by_vital_id) REFERENCES vitals(id),
    FOREIGN KEY(acknowledged_by) REFERENCES users(id),
    FOREIGN KEY(dismissed_by) REFERENCES users(id),
    INDEX(patient_id),
    INDEX(alert_type),
    INDEX(triggered_at),
    INDEX(severity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- APPOINTMENTS
-- ========================================

CREATE TABLE IF NOT EXISTS appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    consultant_id INT NOT NULL,
    appointment_date DATETIME NOT NULL,
    appointment_type ENUM('consultation','procedure','follow_up') DEFAULT 'consultation',
    status ENUM('scheduled','completed','cancelled','no_show') DEFAULT 'scheduled',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY(patient_id) REFERENCES patients(id),
    FOREIGN KEY(consultant_id) REFERENCES users(id),
    FOREIGN KEY(created_by) REFERENCES users(id),
    INDEX(appointment_date),
    INDEX(status),
    INDEX(patient_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- BILLING & PAYMENTS
-- ========================================

CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(50) UNIQUE NOT NULL,
    patient_id INT NOT NULL,
    admission_id INT,
    invoice_date DATETIME NOT NULL,
    due_date DATE,
    total_amount DECIMAL(12,2) NOT NULL,
    paid_amount DECIMAL(12,2) DEFAULT 0,
    status ENUM('draft','issued','partial','paid','cancelled') DEFAULT 'draft',
    notes TEXT,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY(patient_id) REFERENCES patients(id),
    FOREIGN KEY(admission_id) REFERENCES admissions(id),
    FOREIGN KEY(created_by) REFERENCES users(id),
    INDEX(invoice_number),
    INDEX(invoice_date),
    INDEX(status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity INT DEFAULT 1,
    unit_price DECIMAL(12,2) NOT NULL,
    tax_percentage DECIMAL(5,2) DEFAULT 0,
    amount DECIMAL(12,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(invoice_id) REFERENCES invoices(id) ON DELETE CASCADE,
    INDEX(invoice_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    payment_date DATETIME NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method ENUM('cash','card','check','transfer','online') DEFAULT 'cash',
    transaction_id VARCHAR(255),
    notes TEXT,
    received_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(invoice_id) REFERENCES invoices(id),
    FOREIGN KEY(received_by) REFERENCES users(id),
    INDEX(invoice_id),
    INDEX(payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- AUDIT LOGGING
-- ========================================

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255) NOT NULL,
    entity_type VARCHAR(100),
    entity_id INT,
    old_values JSON,
    new_values JSON,
    ip_address VARCHAR(50),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(user_id) REFERENCES users(id),
    INDEX(user_id),
    INDEX(created_at),
    INDEX(entity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- SETTINGS
-- ========================================

CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(255) UNIQUE NOT NULL,
    setting_value TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default settings
INSERT IGNORE INTO settings (setting_key, setting_value, description) VALUES
('hospital_name', 'Dr. Arman Kabir\'s Care', 'Hospital name'),
('hospital_phone', '', 'Hospital contact number'),
('hospital_address', '', 'Hospital address'),
('enable_whatsapp', '1', 'Enable WhatsApp invoice sharing'),
('enable_investigations', '1', 'Enable investigation module'),
('default_currency', 'BDT', 'Default currency code');
