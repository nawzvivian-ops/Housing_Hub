<?php
// contact_submit.php
// Handles the Contact Us form submission and saves to notifications table

// ── DB CONNECTION ─────────────────────────────────────────────
$host = "localhost";
$db   = "housinghub";   // ← change this
$user = "root";         // ← change this
$pass = "";     // ← change this

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Connection failed."]);
    exit;
}

// ── ONLY ACCEPT POST ──────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}

// ── SANITIZE INPUT ────────────────────────────────────────────
$first_name = trim($conn->real_escape_string($_POST["first_name"] ?? ""));
$last_name  = trim($conn->real_escape_string($_POST["last_name"]  ?? ""));
$email      = trim($conn->real_escape_string($_POST["email"]      ?? ""));
$phone      = trim($conn->real_escape_string($_POST["phone"]      ?? ""));
$role       = trim($conn->real_escape_string($_POST["role"]       ?? ""));
$subject    = trim($conn->real_escape_string($_POST["subject"]    ?? ""));
$message    = trim($conn->real_escape_string($_POST["message"]    ?? ""));

// ── VALIDATE REQUIRED FIELDS ──────────────────────────────────
if (!$first_name || !$last_name || !$email || !$subject || !$message) {
    echo json_encode(["success" => false, "message" => "Please fill in all required fields."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(["success" => false, "message" => "Invalid email address."]);
    exit;
}

// ── BUILD NOTIFICATION CONTENT ────────────────────────────────
// Combines all form fields into a readable message for the notifications table
$full_name     = $first_name . " " . $last_name;
$notif_title   = "Contact Form: " . $subject;
$notif_message = "From: $full_name\n"
               . "Email: $email\n"
               . ($phone ? "Phone: $phone\n" : "")
               . ($role  ? "Role: $role\n"   : "")
               . "\nMessage:\n$message";

// ── INSERT INTO notifications TABLE ───────────────────────────
// user_id = 0 means it's from a public visitor (not a logged-in user)
// tenant_id = 0 for the same reason
$stmt = $conn->prepare(
    "INSERT INTO notifications (user_id, is_read, tenant_id, title, message, date, status)
     VALUES (?, 0, 0, ?, ?, NOW(), 'unread')"
);

// user_id = 0 (public/guest visitor)
$user_id = 0;
$stmt->bind_param("iss", $user_id, $notif_title, $notif_message);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Your message has been sent successfully!"]);
} else {
    echo json_encode(["success" => false, "message" => "Failed to send message. Please try again."]);
}

$stmt->close();
$conn->close();
?>