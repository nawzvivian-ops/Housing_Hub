<?php
session_start();
include "db_connect.php";
require_once "send_mail.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
header('Content-Type: application/json');
 
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit();
}
 
// Auto-create table if not exists (UPDATED with terms_agreed)
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS property_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(200) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(200) DEFAULT NULL,
    occupation VARCHAR(200) DEFAULT NULL,
    owner_location VARCHAR(200) DEFAULT NULL,
    property_name VARCHAR(200) NOT NULL,
    property_type VARCHAR(100) DEFAULT NULL,
    property_address TEXT DEFAULT NULL,
    units INT DEFAULT 1,
    rent_amount DECIMAL(12,2) DEFAULT 0,
    bedrooms INT DEFAULT 0,
    property_status VARCHAR(200) DEFAULT NULL,
    amenities TEXT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    services_needed VARCHAR(200) DEFAULT NULL,
    start_timeline VARCHAR(100) DEFAULT NULL,
    referral_source VARCHAR(100) DEFAULT NULL,
    questions TEXT DEFAULT NULL,
    terms_agreed TINYINT(1) DEFAULT 0, -- Added column
    status VARCHAR(50) DEFAULT 'pending',
    admin_notes TEXT DEFAULT NULL,
    reviewed_by VARCHAR(200) DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT NOW()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
 
// Collect fields
$fullname         = mysqli_real_escape_string($conn, trim($_POST['fullname'] ?? ''));
$phone            = mysqli_real_escape_string($conn, trim($_POST['phone'] ?? ''));
$email            = mysqli_real_escape_string($conn, trim($_POST['email'] ?? ''));
$occupation       = mysqli_real_escape_string($conn, trim($_POST['occupation'] ?? ''));
$owner_location   = mysqli_real_escape_string($conn, trim($_POST['owner_location'] ?? ''));
$property_name    = mysqli_real_escape_string($conn, trim($_POST['property_name'] ?? ''));
$property_type    = mysqli_real_escape_string($conn, trim($_POST['property_type'] ?? ''));
$property_address = mysqli_real_escape_string($conn, trim($_POST['property_address'] ?? ''));
$units            = (int)($_POST['units'] ?? 1);
$rent_amount      = (float)($_POST['rent_amount'] ?? 0);
$bedrooms         = (int)($_POST['bedrooms'] ?? 0);
$property_status  = mysqli_real_escape_string($conn, trim($_POST['property_status'] ?? ''));
$amenities        = mysqli_real_escape_string($conn, trim($_POST['amenities'] ?? ''));
$description      = mysqli_real_escape_string($conn, trim($_POST['description'] ?? ''));
$services_needed  = mysqli_real_escape_string($conn, trim($_POST['services_needed'] ?? ''));
$start_timeline   = mysqli_real_escape_string($conn, trim($_POST['start_timeline'] ?? ''));
$referral_source  = mysqli_real_escape_string($conn, trim($_POST['referral_source'] ?? ''));
$questions        = mysqli_real_escape_string($conn, trim($_POST['questions'] ?? ''));
$terms_agreed     = isset($_POST['terms_agreed']) ? 1 : 0; // Added field
 
// Validate required
if (!$fullname || !$phone || !$property_name || !$property_address) {
    echo json_encode(['success' => false, 'message' => '⚠️ Please fill in all required fields (name, phone, property name, address).']);
    exit();
}

// Added validation for the agreement checkbox
if ($terms_agreed === 0) {
    echo json_encode(['success' => false, 'message' => '⚠️ You must agree to the Management Terms to proceed.']);
    exit();
}
 
// Insert (UPDATED to include terms_agreed)
$q = mysqli_query($conn, "INSERT INTO property_applications
    (fullname, phone, email, occupation, owner_location, property_name, property_type, property_address,
     units, rent_amount, bedrooms, property_status, amenities, description, services_needed, start_timeline, referral_source, questions, terms_agreed)
    VALUES
    ('$fullname','$phone','$email','$occupation','$owner_location','$property_name','$property_type','$property_address',
     $units,$rent_amount,$bedrooms,'$property_status','$amenities','$description','$services_needed','$start_timeline','$referral_source','$questions', $terms_agreed)");
 
if (!$q) {
    echo json_encode(['success' => false, 'message' => '❌ Database error. Please try again or contact us directly.']);
    exit();
}
 
// Send confirmation email to applicant
if (!empty($email)) {
    $email_body = "Dear $fullname,\n\n"
        . "Thank you for submitting your property management application to HousingHub!\n\n"
        . "════════════════════════════════════════\n"
        . "   APPLICATION RECEIVED ✅\n"
        . "════════════════════════════════════════\n"
        . "Property : $property_name\n"
        . "Address  : $property_address\n"
        . "Units    : $units\n"
        . "Type     : $property_type\n"
        . "════════════════════════════════════════\n\n"
        . "WHAT HAPPENS NEXT:\n"
        . "1. Our team will review your application within 24 hours.\n"
        . "2. We will call you on $phone to discuss your property and management plan.\n"
        . "3. Once agreed, your property will be added to HousingHub and management begins.\n\n"
        . "If you have any questions in the meantime:\n"
        . "📧 owners@housinghuborg.ug\n"
        . "📱 +256 700 000 000\n\n"
        . "We look forward to managing your property!\n\n"
        . "Warm regards,\nHousingHub Team\nsupport@housinghuborg.ug";
 
    send_mail($email, "Your HousingHub Property Application Has Been Received!", $email_body);
}
 
// Notify admin
$admin_email = "nawzvivian@gmail.com";
$admin_body  = "New Property Management Application Received!\n\n"
    . "From   : $fullname\n"
    . "Phone  : $phone\n"
    . "Email  : $email\n"
    . "Property: $property_name ($property_type)\n"
    . "Address: $property_address\n"
    . "Units  : $units | Rent: UGX $rent_amount\n"
    . "Status : $property_status\n"
    . "Services: $services_needed\n"
    . "Timeline: $start_timeline\n\n"
    . "Terms Accepted: Yes\n\n" // Added for clarity
    . "Login to admin panel → Property Applications to review.\n";
 
send_mail($admin_email, "🏠 New Property Application — $fullname", $admin_body);
 
echo json_encode([
    'success' => true,
    'message' => '✅ Application submitted successfully! Our team will call you within 24 hours on ' . htmlspecialchars($_POST['phone']) . ' to discuss next steps. Check your email for a confirmation.'
]);