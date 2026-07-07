<?php

namespace Models;

class Patient extends BaseModel {
    protected $table = 'patients';

    public function create($data) {
        $sql = "
            INSERT INTO patients (
                patient_id_unique, full_name, full_name_bn, date_of_birth,
                gender, phone, email, address, blood_group, weight, height,
                allergies, chronic_conditions, past_surgical_history,
                patient_type, consultant_id, created_by, created_at, updated_at
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $this->generatePatientId(),
            $data['full_name'],
            $data['full_name_bn'] ?? null,
            $data['date_of_birth'] ?? null,
            $data['gender'],
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['address'] ?? null,
            $data['blood_group'] ?? null,
            $data['weight'] ?? null,
            $data['height'] ?? null,
            json_encode($data['allergies'] ?? []),
            json_encode($data['chronic_conditions'] ?? []),
            $data['past_surgical_history'] ?? null,
            $data['patient_type'] ?? 'outdoor',
            $data['consultant_id'] ?? null,
            $data['created_by'],
        ]);

        if (!$result) {
            throw new \Exception('Failed to create patient');
        }

        return $this->db->lastInsertId();
    }

    public function search($query, $limit = 50, $offset = 0) {
        $searchTerm = "%{$query}%";
        $sql = "
            SELECT * FROM patients
            WHERE full_name LIKE ? OR phone LIKE ? OR patient_id_unique LIKE ?
            ORDER BY created_at DESC
            LIMIT ? OFFSET ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $limit, $offset]);
        return $stmt->fetchAll();
    }

    public function searchCount($query) {
        $searchTerm = "%{$query}%";
        $sql = "
            SELECT COUNT(*) as total FROM patients
            WHERE full_name LIKE ? OR phone LIKE ? OR patient_id_unique LIKE ?
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        $result = $stmt->fetch();
        return $result['total'] ?? 0;
    }

    public function update($id, $data) {
        $allowed = ['full_name', 'phone', 'email', 'address', 'blood_group', 'weight', 'height', 'consultant_id'];
        $fields = [];
        $values = [];

        foreach ($data as $key => $value) {
            if (in_array($key, $allowed)) {
                $fields[] = "$key = ?";
                $values[] = $value;
            }
        }

        if (empty($fields)) {
            return false;
        }

        $fields[] = "updated_at = NOW()";
        $values[] = $id;

        $sql = "UPDATE patients SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    private function generatePatientId() {
        return 'PAT-' . date('Ym') . '-' . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}
