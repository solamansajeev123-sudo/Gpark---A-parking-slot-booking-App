<?php
declare(strict_types=1);

require_once __DIR__ . '/conn.php';

header('Content-Type: application/json; charset=UTF-8');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin === 'null' || preg_match('/^https?:\/\/(localhost|127\.0\.0\.1|172\.30\.3\.21)(:\d+)?$/', $origin) === 1) {
	header('Access-Control-Allow-Origin: ' . ($origin === '' ? 'null' : $origin));
}
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

try {
	$statement = $pdo->query(
		"SELECT DISTINCT slot FROM booking WHERE slot IS NOT NULL AND slot <> '' ORDER BY slot"
	);
	$occupiedSlots = array_map(
		static fn (array $row): string => (string) $row['slot'],
		$statement->fetchAll(PDO::FETCH_ASSOC)
	);

	respond(200, [
		'success' => true,
		'occupied_slots' => $occupiedSlots,
	]);
} catch (PDOException $exception) {
	error_log('get_booking failed: ' . $exception->getMessage());
	respond(500, ['success' => false, 'message' => 'Unable to load occupied slots.']);
}
