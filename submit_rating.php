<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'tenant') {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: dashboard.php");
    exit();
}

$user_id = (int)$_SESSION['user_id'];
$property_id = (int)($_POST['property_id'] ?? 0);
$cleanliness = max(1, min(5, (int)($_POST['rating_cleanliness'] ?? 0)));
$security = max(1, min(5, (int)($_POST['rating_security'] ?? 0)));
$value = max(1, min(5, (int)($_POST['rating_value'] ?? 0)));
$comment = trim($_POST['comment'] ?? '');

if ($property_id <= 0) {
    $_SESSION['error'] = "No property is linked to your tenant account.";
    header("Location: dashboard.php#pg-complaints");
    exit();
}

$stmt = $conn->prepare("
    INSERT INTO ratings (property_id, user_id, rating_cleanliness, rating_security, rating_value, comment, created_at)
    VALUES (?, ?, ?, ?, ?, ?, NOW())
    ON DUPLICATE KEY UPDATE
        rating_cleanliness = VALUES(rating_cleanliness),
        rating_security = VALUES(rating_security),
        rating_value = VALUES(rating_value),
        comment = VALUES(comment),
        created_at = NOW()
");

if (!$stmt) {
    $_SESSION['error'] = "Could not prepare rating.";
    header("Location: dashboard.php#pg-complaints");
    exit();
}

$stmt->bind_param("iiiiis", $property_id, $user_id, $cleanliness, $security, $value, $comment);
$ok = $stmt->execute();
$_SESSION[$ok ? 'success' : 'error'] = $ok ? "Rating submitted." : "Could not submit rating.";

header("Location: dashboard.php#pg-complaints");
exit();
?>
