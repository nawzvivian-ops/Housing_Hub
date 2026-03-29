<?php
declare(strict_types=1);

require_once __DIR__ . "/db_connect.php";

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
    render_page('Invalid Request', 'This page only accepts inspection form submissions.', false);
}

mysqli_set_charset($conn, "utf8mb4");

mysqli_query($conn, "
    CREATE TABLE IF NOT EXISTS property_viewing_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        property_id INT DEFAULT NULL,
        fullname VARCHAR(200) NOT NULL,
        phone VARCHAR(50) NOT NULL,
        email VARCHAR(200) DEFAULT NULL,
        property_name VARCHAR(200) NOT NULL,
        inspection_date DATE NOT NULL,
        inspection_time TIME NOT NULL,
        visitor_type VARCHAR(100) NOT NULL,
        assigned_host VARCHAR(200) DEFAULT NULL,
        purpose_notes TEXT DEFAULT NULL,
        status VARCHAR(50) DEFAULT 'pending',
        admin_notes TEXT DEFAULT NULL,
        reviewed_by VARCHAR(200) DEFAULT NULL,
        reviewed_at DATETIME DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX(property_id),
        INDEX(status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

$inspect_name     = clean($_POST['inspect_name'] ?? '');
$inspect_phone    = clean($_POST['inspect_phone'] ?? '');
$inspect_email    = clean($_POST['inspect_email'] ?? '');
$inspect_property = clean($_POST['inspect_property'] ?? '');
$inspect_date     = clean($_POST['inspect_date'] ?? '');
$inspect_time     = clean($_POST['inspect_time'] ?? '');
$inspect_type     = clean($_POST['inspect_type'] ?? '');
$inspect_host     = clean($_POST['inspect_host'] ?? '');
$inspect_purpose  = clean($_POST['inspect_purpose'] ?? '');

if (
    $inspect_name === '' ||
    $inspect_phone === '' ||
    $inspect_property === '' ||
    $inspect_date === '' ||
    $inspect_time === '' ||
    $inspect_type === ''
) {
    render_page('Missing Required Fields', 'Please complete all required inspection fields before submitting.', false);
}

if ($inspect_email !== '' && !filter_var($inspect_email, FILTER_VALIDATE_EMAIL)) {
    render_page('Invalid Email', 'Please enter a valid email address.', false);
}

$property_id = null;
$lookup = $conn->prepare("SELECT id FROM properties WHERE property_name = ? LIMIT 1");
if ($lookup) {
    $lookup->bind_param("s", $inspect_property);
    $lookup->execute();
    $result = $lookup->get_result();
    if ($row = $result->fetch_assoc()) {
        $property_id = (int)$row['id'];
    }
    $lookup->close();
}

$stmt = $conn->prepare("
    INSERT INTO property_viewing_requests
    (property_id, fullname, phone, email, property_name, inspection_date, inspection_time, visitor_type, assigned_host, purpose_notes, status)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
");

if (!$stmt) {
    render_page('Database Error', 'Could not prepare the inspection request query.', false);
}

$stmt->bind_param(
    "isssssssss",
    $property_id,
    $inspect_name,
    $inspect_phone,
    $inspect_email,
    $inspect_property,
    $inspect_date,
    $inspect_time,
    $inspect_type,
    $inspect_host,
    $inspect_purpose
);

if (!$stmt->execute()) {
    $stmt->close();
    render_page('Submission Failed', 'Your inspection request could not be saved. Please try again.', false);
}

$stmt->close();

render_page(
    'Inspection Request Submitted',
    'Your property viewing request has been submitted successfully.'
);