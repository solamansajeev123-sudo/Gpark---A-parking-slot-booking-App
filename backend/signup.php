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

$name = trim((string) ($request['name'] ?? ''));
$email = trim((string) ($request['email'] ?? ''));
$password = (string) ($request['password'] ?? '');
$confirmPassword = (string) ($request['confirm_password'] ?? '');

if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
	respond(400, ['success' => false, 'message' => 'All fields are required.']);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	respond(400, ['success' => false, 'message' => 'Please enter a valid email address.']);
}

if ($password !== $confirmPassword) {
	respond(400, ['success' => false, 'message' => 'Passwords do not match.']);
}

$passwordHash = password_hash($password, PASSWORD_DEFAULT);

try {
	$statement = $pdo->prepare(
		'INSERT INTO users (name, email, password) VALUES (:name, :email, :password)'
	);
	$statement->execute([
		':name' => $name,
		':email' => $email,
		':password' => $passwordHash,
	]);

	respond(201, ['success' => true, 'message' => 'Account created successfully.']);
} catch (PDOException $exception) {
	if ($exception->getCode() === '23000') {
		respond(409, ['success' => false, 'message' => 'An account with this email already exists.']);
	}

	respond(500, ['success' => false, 'message' => 'Unable to create the account.']);
}
