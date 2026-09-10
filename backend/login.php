<?php
declare(strict_types=1);

require_once __DIR__ . '/conn.php';

header('Content-Type: application/json; charset=UTF-8');
$allowedOrigins = [
	'localhost',
	'127.0.0.1',
	'172.30.3.21',
];
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

$originHost = parse_url($origin, PHP_URL_HOST);
if ($origin === 'null' || in_array($originHost, $allowedOrigins, true)) {
	header('Access-Control-Allow-Origin: ' . ($origin ?: 'null'));
	header('Vary: Origin');
}
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Allow-Methods: POST, OPTIONS');

function respond(int $status, array $data): never
{
	http_response_code($status);
	echo json_encode($data);
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
	http_response_code(204);
	exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Allow: POST');
	respond(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

$request = json_decode(file_get_contents('php://input'), true);
if (!is_array($request)) {
	$request = $_POST;
}

$email = trim((string) ($request['email'] ?? ''));
$password = (string) ($request['password'] ?? '');

if ($email === '' || $password === '') {
	respond(400, ['success' => false, 'message' => 'Email and password are required.']);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	respond(400, ['success' => false, 'message' => 'Please enter a valid email address.']);
}

$statement = $pdo->prepare(
	'SELECT id, name, email, password FROM users WHERE email = :email LIMIT 1'
);
$statement->execute([':email' => $email]);
$user = $statement->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, (string) $user['password'])) {
	respond(401, ['success' => false, 'message' => 'Invalid email or password.']);
}

session_start();
$_SESSION['user_id'] = (int) $user['id'];
$_SESSION['user_name'] = (string) $user['name'];
$_SESSION['user_email'] = (string) $user['email'];

respond(200, [
	'success' => true,
	'message' => 'Login successful.',
	'user' => [
		'id' => (int) $user['id'],
		'name' => (string) $user['name'],
		'email' => (string) $user['email'],
	],
]);
