<?php

namespace Config;

class Constants {
    // User Roles
    const ROLE_ADMIN = 'admin';
    const ROLE_CONSULTANT = 'consultant';
    const ROLE_REGISTRAR = 'registrar';
    const ROLE_MEDICAL_OFFICER = 'medical_officer';
    const ROLE_ASSISTANT_REGISTRAR = 'assistant_registrar';
    const ROLE_INTERN = 'intern';
    const ROLE_NURSE = 'nurse';
    const ROLE_RECEPTION = 'reception';
    const ROLE_PATIENT = 'patient';

    const ALL_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_CONSULTANT,
        self::ROLE_REGISTRAR,
        self::ROLE_MEDICAL_OFFICER,
        self::ROLE_ASSISTANT_REGISTRAR,
        self::ROLE_INTERN,
        self::ROLE_NURSE,
        self::ROLE_RECEPTION,
        self::ROLE_PATIENT,
    ];

    // Patient Types
    const PATIENT_TYPE_ADMITTED = 'admitted';
    const PATIENT_TYPE_OUTDOOR = 'outdoor';

    // Visit Types
    const VISIT_TYPE_ADMITTED = 'admitted';
    const VISIT_TYPE_OUTDOOR = 'outdoor';

    // Gender
    const GENDER_MALE = 'male';
    const GENDER_FEMALE = 'female';
    const GENDER_OTHER = 'other';

    // Vital Statuses
    const VITAL_STATUS_DRAFTED = 'drafted';
    const VITAL_STATUS_PENDING = 'pending_review';
    const VITAL_STATUS_VERIFIED = 'verified';
    const VITAL_STATUS_REJECTED = 'rejected';

    // SOAP Note Statuses
    const SOAP_STATUS_DRAFT = 'draft';
    const SOAP_STATUS_INTERN_DRAFT = 'intern_draft';
    const SOAP_STATUS_MO_REVIEW = 'mo_review';
    const SOAP_STATUS_FINALIZED = 'finalized';

    // Bed Types
    const BED_TYPE_ICU = 'icu';
    const BED_TYPE_HDU = 'hdu';
    const BED_TYPE_CABIN = 'cabin';
    const BED_TYPE_GENERAL = 'general';

    // Bed Status
    const BED_STATUS_AVAILABLE = 'available';
    const BED_STATUS_OCCUPIED = 'occupied';
    const BED_STATUS_RESERVED = 'reserved';
    const BED_STATUS_CLEANING = 'cleaning';

    // Clinical Alert Types
    const ALERT_SEPSIS = 'sepsis';
    const ALERT_AKI = 'aki';
    const ALERT_NEWS2 = 'news2_high';
    const ALERT_HYPOGLYCEMIA = 'hypoglycemia';
    const ALERT_HYPERTENSION = 'hypertension';

    // Alert Severity
    const SEVERITY_INFO = 'info';
    const SEVERITY_WARNING = 'warning';
    const SEVERITY_CRITICAL = 'critical';

    // Invoice Status
    const INVOICE_STATUS_DRAFT = 'draft';
    const INVOICE_STATUS_ISSUED = 'issued';
    const INVOICE_STATUS_PARTIAL = 'partial';
    const INVOICE_STATUS_PAID = 'paid';
    const INVOICE_STATUS_CANCELLED = 'cancelled';

    // Payment Methods
    const PAYMENT_METHOD_CASH = 'cash';
    const PAYMENT_METHOD_CARD = 'card';
    const PAYMENT_METHOD_CHECK = 'check';
    const PAYMENT_METHOD_TRANSFER = 'transfer';
    const PAYMENT_METHOD_ONLINE = 'online';
}
