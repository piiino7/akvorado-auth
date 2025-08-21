<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once dirname(__DIR__) . '/vendor/autoload.php';

require_once(dirname(__FILE__).'/api-functions.php');
$config = require dirname(__DIR__) . '/src/config.php';

use Classes\Logger;
//use Classes\Register;
use Classes\Validator;
use Classes\Database;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Access-Control-Allow-Origin: http://akvorado-test:81');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uriParts = explode('/', trim($uri, '/'));

if ($uriParts[0] !== 'api') {
    response(404,'Not an API route');
    exit;
}

switch ($uriParts[1]) {
    case 'auth':
        // GET /api/auth
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                http_response_code(405);
                header('Content-Type: application/json');
                echo json_encode(['error' => 'Method not allowed']);
                exit;
            }

            $secret = $config['secret_key'];
            $validate = new Validator($secret);

            $headers = getallheaders();
            $authHeader = $headers['Authorization'] ?? '';

            if (empty($authHeader)) {
                http_response_code(401);
                echo json_encode(['success' => false, 'message' => 'Authorization header missing']);
                exit;
            }

            $token = str_replace('Bearer ', '', $authHeader);
            $result = $validate->checkToken($token);

            header('X-User: ' . ($result['user_id'] ?? 'unknown'));
            header('X-Username: ' . ($result['username'] ?? 'unknown'));
            http_response_code(200);
            echo json_encode(['success' => true, 'message' => 'Authorized']);
        } catch (Throwable $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
        break;
    case 'login':
        // POST /api/login
        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode(['error' => 'Method not allowed']);
                exit;
            }

            $secret = $config['secret_key'];
            $pdo = new Database($config['db']['host'], $config['db']['base'], $config['db']['user'], $config['db']['pass'], $config['db']['charset']);
            $db = $pdo->connection();
            $login = new Logger($db, $secret);
            $data = json_decode(file_get_contents('php://input'), true);

            if (!$data || empty($data['username']) || empty($data['password'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Username or password missing']);
                exit;
            }

            $result = $login->attempt($data['username'], $data['password']);

            if ($result['success']) {
                setcookie('jwt', $result['token'], [
                    'expires' => time() + 7200,
                    'path' => '/',
                    'domain' => 'akvorado-test',
                    'secure' => false,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);

                http_response_code(200);
                echo json_encode(['success' => true, 'message' => 'Success login!']);
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Login failed', 'message' => $result['message']]);
            }

        } catch (Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Server error: ' . $e->getMessage()]);
        }
        break;
    default:
        response(404,'Not Found Api Route');
        break;
}