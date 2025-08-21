<?php

namespace Classes;
use Firebase\JWT\JWT;

class Register
{
    private $db;
    private $secret;

    public function __construct($db, $secret) {
        $this->db = $db;
        $this->secret = $secret;
    }

    public function register($username, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (`username`, `password`) VALUES (?, ?)");
        $newUserStatus = $stmt->execute([$username, $hash]);
        $userId = $this->db->lastInsertId();

        if (!$newUserStatus) {
            return ['success' => false, 'message' => 'Пользователь не создан'];
        }

        $payload = [
            "iss" => "akv-register",
            "sub" => $userId,
            "iat" => time(),
            "exp" => time() + 36000
        ];

        $jwt = JWT::encode($payload, $this->secret, 'HS256');
        return ['success' => true, 'token' => $jwt];
    }
}
