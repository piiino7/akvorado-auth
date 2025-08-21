<?php

namespace Classes;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Exception;

class Validator
{
    private $secret;

    public function __construct($secret)
    {
        $this->secret = $secret;
    }

    public function checkToken($jwt)
    {
        if (empty($jwt)) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'JWT token missing']);
            exit;
        }

        try {
            $decoded = JWT::decode($jwt, new Key($this->secret, 'HS256'));
            $decoded_array = (array)$decoded;

            if (isset($decoded_array['exp']) && $decoded_array['exp'] < time()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Token expired']);
                exit;
            }

            return [
                'user_id' => $decoded_array['sub'] ?? 'unknown',
                'username' => $decoded_array['username'] ?? 'unknown',
                'exp' => $decoded_array['exp'] ?? 0
            ];
        } catch (Exception $e) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Invalid token: ' . $e->getMessage()]);
            exit;
        }
    }
}