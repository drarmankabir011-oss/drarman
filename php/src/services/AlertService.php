<?php

namespace Services;

use Config\Database;
use Config\Constants;

class AlertService {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Calculate NEWS2 (National Early Warning Score 2) from vitals
     */
    public function calculateNEWS2($vitals) {
        $score = 0;

        // Respiration Rate scoring
        $rr = (int)$vitals['respiratory_rate'];
        if ($rr <= 8 || $rr >= 25) $score += 3;
        elseif ($rr <= 11 || $rr >= 20) $score += 1;

        // Oxygen saturation scoring
        $spo2 = (int)$vitals['oxygen_saturation'];
        if ($spo2 <= 91 || $spo2 >= 96) $score += 3;
        elseif ($spo2 == 92 || $spo2 == 95) $score += 1;

        // Heart rate scoring
        $pulse = (int)$vitals['pulse'];
        if ($pulse <= 40 || $pulse >= 131) $score += 3;
        elseif ($pulse <= 50 || $pulse >= 110) $score += 1;

        // Systolic BP scoring
        $bp = explode('/', $vitals['blood_pressure'] ?? '0/0');
        $systolic = (int)$bp[0];
        if ($systolic <= 90 || $systolic >= 220) $score += 3;
        elseif ($systolic <= 100 || $systolic >= 200) $score += 1;

        // Temperature scoring
        $temp = (float)$vitals['temperature'];
        if ($temp <= 35.0 || $temp >= 39.0) $score += 3;
        elseif ($temp <= 36.0 || $temp >= 38.5) $score += 1;

        return [
            'score' => $score,
            'alert' => $score >= 5,
            'severity' => $score >= 7 ? Constants::SEVERITY_CRITICAL : ($score >= 5 ? Constants::SEVERITY_WARNING : Constants::SEVERITY_INFO),
        ];
    }

    /**
     * Check for sepsis indicators
     */
    public function checkSepsis($vitals, $investigations = []) {
        $indicators = [];

        if ($vitals['temperature'] > 38 || $vitals['temperature'] < 36) {
            $indicators[] = 'abnormal_temp';
        }

        if ($vitals['pulse'] > 90) {
            $indicators[] = 'tachycardia';
        }

        if ($vitals['respiratory_rate'] > 20) {
            $indicators[] = 'tachypnea';
        }

        return [
            'alert' => count($indicators) >= 2,
            'indicators' => $indicators,
            'severity' => count($indicators) >= 2 ? Constants::SEVERITY_CRITICAL : Constants::SEVERITY_INFO,
        ];
    }

    /**
     * Check for AKI (Acute Kidney Injury)
     */
    public function checkAKI($investigations) {
        $alerts = [];

        foreach ($investigations as $inv) {
            if ($inv['test_code'] === 'CREAT' && (float)$inv['result_value'] > 1.5) {
                $alerts[] = 'elevated_creatinine';
            }
            if ($inv['test_code'] === 'BUN' && (float)$inv['result_value'] > 20) {
                $alerts[] = 'elevated_bun';
            }
        }

        return [
            'alert' => count($alerts) > 0,
            'indicators' => $alerts,
            'severity' => count($alerts) >= 2 ? Constants::SEVERITY_CRITICAL : Constants::SEVERITY_WARNING,
        ];
    }

    public function createAlert($patientId, $alertType, $severity, $vitalId = null) {
        $sql = "
            INSERT INTO clinical_alerts (patient_id, alert_type, severity, triggered_by_vital_id, triggered_at)
            VALUES (?, ?, ?, ?, NOW())
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$patientId, $alertType, $severity, $vitalId]);
    }

    public function getActiveAlerts($patientId) {
        $sql = "
            SELECT * FROM clinical_alerts
            WHERE patient_id = ? AND dismissed_at IS NULL
            ORDER BY triggered_at DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([$patientId]);
        return $stmt->fetchAll();
    }

    public function dismissAlert($alertId, $userId) {
        $sql = "
            UPDATE clinical_alerts
            SET dismissed_at = NOW(), dismissed_by = ?
            WHERE id = ?
        ";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$userId, $alertId]);
    }
}
