<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/conn.php';

header('Content-Type: application/json; charset=UTF-8');
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin === 'null' || preg_match('/^https?:\/\/(localhost|127\.0\.0\.1|172\.30\.3\.21)(:\d+)?$/', $origin) === 1) {
	header('Access-Control-Allow-Origin: ' . ($origin === '' ? 'null' : $origin));
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

if (empty($_SESSION['user_id'])) {
	respond(401, ['success' => false, 'message' => 'You must be logged in to create a booking.']);
}

$request = json_decode(file_get_contents('php://input'), true);
if (!is_array($request)) {
	$request = $_POST;
}

$bookingId = trim((string) ($request['booking_id'] ?? ''));
$slot = trim((string) ($request['slot'] ?? ''));
$vehicleType = filter_var($request['vehicle_type'] ?? null, FILTER_VALIDATE_INT);
$vehicleModel = trim((string) ($request['vehicle_model'] ?? ''));
$duration = filter_var($request['duration'] ?? null, FILTER_VALIDATE_INT);

if ($bookingId === '' || strlen($bookingId) > 10) {
	respond(400, ['success' => false, 'message' => 'A valid booking ID is required.']);
}

if ($slot === '' || strlen($slot) > 10) {
	respond(400, ['success' => false, 'message' => 'A valid slot is required.']);
}

if ($vehicleType === false || $vehicleType === null || !in_array($vehicleType, [0, 1], true)) {
	respond(400, ['success' => false, 'message' => 'A valid vehicle type is required.']);
}

if ($vehicleModel === '' || strlen($vehicleModel) > 10) {
	respond(400, ['success' => false, 'message' => 'A valid vehicle model is required.']);
}

if ($duration === false || $duration === null || $duration <= 0) {
	respond(400, ['success' => false, 'message' => 'Duration must be greater than zero.']);
}

$bookedBy = trim((string) ($_SESSION['user_name'] ?? ''));
if ($bookedBy === '') {
	respond(401, ['success' => false, 'message' => 'User name is missing from the current session.']);
}

try {
	$statement = $pdo->prepare(
		'INSERT INTO booking(booking_id, slot, vehicle_type, vehicle_model, duration, booked_by)
		 VALUES (:booking_id, :slot, :vehicle_type, :vehicle_model, :duration, :booked_by)'
	);
	$statement->execute([
		':booking_id' => $bookingId,
		':slot' => $slot,
		':vehicle_type' => $vehicleType,
		':vehicle_model' => $vehicleModel,
		':duration' => $duration,
		':booked_by' => $bookedBy,
	]);

	respond(201, [
		'success' => true,
		'message' => 'Booking created successfully.',
		'booking' => [
			'booking_id' => $bookingId,
			'slot' => $slot,
			'vehicle_type' => $vehicleType,
			'vehicle_model' => $vehicleModel,
			'duration' => $duration,
			'booked_by' => $bookedBy,
		],
	]);
} catch (PDOException $exception) {
	error_log('add_booking insert failed: ' . $exception->getMessage());
	respond(500, [
		'success' => false,
		'message' => 'Unable to create the booking: ' . $exception->getMessage(),
	]);
}
