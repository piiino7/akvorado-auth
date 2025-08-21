<?php

namespace Classes;
use Firebase\JWT\JWT;

class Logger
{
    private $db;
    private $secret;

    public function __construct($db, $secret) {
        $this->db = $db;
        $this->secret = $secret;
    }

    public function attempt($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'message' => 'Пользователь не найден'];
        }

        if (password_verify($password, $user['password'])) {
            $payload = [
                "iss" => "akv-logger",
                "sub" => $user['id'],
                "iat" => time(),
                "exp" => time() + 36000
            ];

            $jwt = JWT::encode($payload, $this->secret, 'HS256');
            return ['success' => true, 'token' => $jwt];
        } else {
            return ['success' => false, 'message' => 'Неверный пароль'];
        }
    }
}