<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: null');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Allow-Methods: GET, OPTIONS');

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

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    respond(405, ['success' => false, 'message' => 'Method Not Allowed']);
}

session_start();

if (!isset($_SESSION['user_id'], $_SESSION['user_name'], $_SESSION['user_email'])) {
    respond(401, ['success' => false, 'message' => 'You are not logged in.']);
}

respond(200, [
    'success' => true,
    'user' => [
        'id' => (int) $_SESSION['user_id'],
        'name' => (string) $_SESSION['user_name'],
        'email' => (string) $_SESSION['user_email'],
    ],
]);
