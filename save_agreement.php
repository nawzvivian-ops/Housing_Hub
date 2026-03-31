<?php
session_start();
include 'db_connect.php';

$data = json_decode(file_get_contents('php://input'), true);

$user_id = intval($data['user_id']);
$property_id = intval($data['property_id']);
$agreed = intval($data['agreed']);

if (!$user_id || !$property_id) {
  echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
  exit;
}

// Optional: Add a unique index on (user_id, property_id) for ON DUPLICATE KEY
$stmt = $conn->prepare("INSERT INTO user_agreements (user_id, property_id, agreed, agreed_at) VALUES (?, ?, ?, NOW())
  ON DUPLICATE KEY UPDATE agreed=VALUES(agreed), agreed_at=NOW()");
$stmt->bind_param("iii", $user_id, $property_id, $agreed);
$stmt->execute();

echo json_encode(['status' => 'success']);
?>