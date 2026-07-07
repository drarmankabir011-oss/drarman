<?php

namespace Models;

class User extends BaseModel {
    protected $table = 'users';

    public function create($data) {
        $sql = "
            INSERT INTO users (email, password_hash, full_name, role, phone, created_at, updated_at)
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ";

        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            $data['email'],
            $data['password_hash'],
            $data['full_name'],
            $data['role'],
            $data['phone'] ?? null,
        ]);

        if (!$result) {
            throw new \Exception('Failed to create user');
        }

        return $this->db->lastInsertId();
    }

    public function getByEmail($email) {
        $sql = "SELECT * FROM users WHERE email = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function update($id, $data) {
        $allowed = ['full_name', 'phone', 'role', 'on_duty'];
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

        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($values);
    }

    public function getByRole($role) {
        $sql = "SELECT * FROM users WHERE role = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    }
}
