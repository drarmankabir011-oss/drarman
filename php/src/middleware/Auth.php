<?php

namespace Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class Auth {
    private $secret;
    private $algorithm = 'HS256';

    public function __construct() {
        $this->secret = getenv('JWT_SECRET');
        if (!$this->secret) {
            throw new \Exception('JWT_SECRET not set in environment');
        }
    }

    public function generateToken($user) {
        $issuedAt = time();
        $expire = $issuedAt + (24 * 60 * 60); // 24 hours
        
        $payload = [
            'iat' => $issuedAt,
            'exp' => $expire,
            'user_id' => $user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        return JWT::encode($payload, $this->secret, $this->algorithm);
    }

    public function verifyToken($token) {
        try {
            return JWT::decode($token, new Key($this->secret, $this->algorithm));
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getCurrentUser() {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? null;

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
            return null;
        }

        return $this->verifyToken($matches[1]);
    }

    public function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }

    public function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
}
