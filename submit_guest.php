<?php
declare(strict_types=1);

require_once __DIR__ . "/db_connect.php";
require_once __DIR__ . "/mail_helper.php";
function clean(?string $value): string {
    return trim((string)$value);
}

function render_page(string $title, string $message, bool $success = true): void {
    $color = $success ? '#c8a43c' : '#ff6b6b';

    echo <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        *{box-sizing:border-box;margin:0;padding:0}
        body{
            font-family:Arial,sans-serif;
            background:#04091a;
            color:#fff;
            display:flex;
            justify-content:center;
            align-items:center;
            min-height:100vh;
            padding:24px;
        }
        .card{
            width:100%;
            max-width:720px;
            background:rgba(255,255,255,.04);
            border:1px solid rgba(255,255,255,.08);
            border-radius:18px;
            padding:40px;
            box-shadow:0 20px 60px rgba(0,0,0,.35);
        }
        h1{
            color:{$color};
            margin-bottom:16px;
            font-size:32px;
        }
        p{
            color:rgba(255,255,255,.8);
            line-height:1.7;
            margin-bottom:24px;
            font-size:16px;
        }
        a{
            display:inline-block;
            text-decoration:none;
            background:#c8a43c;
            color:#04091a;
            padding:13px 22px;
            border-radius:8px;
            font-weight:700;
        }
        a:hover{background:#e0c06a}
    </style>
</head>
<body>
    <div class="card">
        <h1>{$title}</h1>
        <p>{$message}</p>
        <a href="visitor.php">Back to Visitor Page</a>
    </div>
</body>
</html>
HTML;
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    render_page('Invalid Request', 'This page only accepts guest form submissions.', false);
}

mysqli_set_charset($conn, "utf8mb4");

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS tenant_guest_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tenant_id INT DEFAULT NULL,
        property_id INT DEFAULT NULL,
        guest_name VARCHAR(200) NOT NULL,
        guest_phone VARCHAR(50) NOT NULL,
        tenant_name VARCHAR(200) NOT NULL,
        unit_number VARCHAR(100) DEFAULT NULL,
        guest_relationship VARCHAR(100) NOT NULL,
        visit_date DATE NOT NULL,
        arrival_time TIME NOT NULL,
        departure_time TIME DEFAULT NULL,
        guest_notes TEXT DEFAULT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        admin_notes TEXT DEFAULT NULL,
        reviewed_by VARCHAR(200) DEFAULT NULL,
        reviewed_at DATETIME DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX(tenant_id),
        INDEX(property_id),
        INDEX(status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$guest_name         = clean($_POST['guest_name'] ?? '');
$guest_phone        = clean($_POST['guest_phone'] ?? '');
$tenant_name        = clean($_POST['tenant_name'] ?? '');
$unit_number        = clean($_POST['unit_number'] ?? '');
$guest_relationship = clean($_POST['guest_relationship'] ?? '');
$guest_date         = clean($_POST['guest_date'] ?? '');
$guest_time         = clean($_POST['guest_time'] ?? '');
$guest_departure    = clean($_POST['guest_departure'] ?? '');
$guest_notes        = clean($_POST['guest_notes'] ?? '');

if (
    $guest_name === '' ||
    $guest_phone === '' ||
    $tenant_name === '' ||
    $unit_number === '' ||
    $guest_relationship === '' ||
    $guest_date === '' ||
    $guest_time === ''
) {
    render_page('Missing Required Fields', 'Please complete all required guest fields before submitting.', false);
}

$tenant_id = null;
$property_id = null;

$lookup = $conn->prepare("SELECT id, property_id FROM tenants WHERE fullname = ? LIMIT 1");
if ($lookup) {
    $lookup->bind_param("s", $tenant_name);
    $lookup->execute();
    $result = $lookup->get_result();
    if ($row = $result->fetch_assoc()) {
        $tenant_id = (int)$row['id'];
        $property_id = !empty($row['property_id']) ? (int)$row['property_id'] : null;
    }
    $lookup->close();
}

$stmt = $conn->prepare("
    INSERT INTO tenant_guest_requests
    (tenant_id, property_id, guest_name, guest_phone, tenant_name, unit_number, guest_relationship, visit_date, arrival_time, departure_time, guest_notes, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
");

if (!$stmt) {
    render_page('Database Error', 'Could not prepare the guest request query.', false);
}

$stmt->bind_param(
    "iisssssssss",
    $tenant_id,
    $property_id,
    $guest_name,
    $guest_phone,
    $tenant_name,
    $unit_number,
    $guest_relationship,
    $guest_date,
    $guest_time,
    $guest_departure,
    $guest_notes
);

if (!$stmt->execute()) {
    $stmt->close();
    render_page('Submission Failed', 'Your guest request could not be saved. Please try again.', false);
}

$stmt->close();
/* =========================
   TENANT DASHBOARD NOTIFICATION
========================= */

if ($tenant_id !== null) {

    $title = "New Guest Registered";

    $message = "Guest {$guest_name} is scheduled to visit you on {$guest_date} at {$guest_time}. Unit: {$unit_number}.";

    $notif = $conn->prepare("
        INSERT INTO notifications 
        (user_id, tenant_id, title, message, is_read, status, date)
        VALUES (?, ?, ?, ?, 0, 'unread', NOW())
    ");

    if ($notif) {
        $notif->bind_param("iiss", $tenant_id, $tenant_id, $title, $message);
        $notif->execute();
        $notif->close();
    }
}
/* =========================
   SEND EMAIL TO GUEST
========================= */

$guest_email = clean($_POST['guest_email'] ?? '');

if (!empty($guest_email)) {

    $subject = "Guest Visit Confirmation - HousingHub";

    $body = "
        <h2>Hello {$guest_name},</h2>

        <p>You have been successfully registered as a guest.</p>

        <p><strong>Tenant:</strong> {$tenant_name}</p>
        <p><strong>Unit:</strong> {$unit_number}</p>
        <p><strong>Date:</strong> {$guest_date}</p>
        <p><strong>Arrival Time:</strong> {$guest_time}</p>

        <br>
        <p>Please present yourself at the property security upon arrival and to avoid inconviences you may 
        show the message upon arrival for a gate pass.</p>

        <p>Thank you,<br>HousingHub</p>
    ";

    send_mail($guest_email, $subject, $body, true);
}

render_page(
    'Guest Registration Submitted',
    'Your guest registration has been submitted successfully.'
);