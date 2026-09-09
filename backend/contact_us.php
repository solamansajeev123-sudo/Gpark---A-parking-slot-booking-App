<?php

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request"
    ]);

    exit;
}


$name = trim($_POST["name"] ?? "");
$email = trim($_POST["email"] ?? "");
$message = trim($_POST["message"] ?? "");


if ($name === "" || $email === "" || $message === "") {

    echo json_encode([
        "success" => false,
        "message" => "Please fill in all fields"
    ]);

    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo json_encode([
        "success" => false,
        "message" => "Please enter a valid email"
    ]);

    exit;
}


/*
   Database code will be added here
   by your backend/database team.
*/


echo json_encode([
    "success" => true,
    "message" => "Thanks — we'll get back to you shortly."
]);

?>