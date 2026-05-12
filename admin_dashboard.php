<?php
session_start();
include "db_connect.php";
require_once "send_mail.php";

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Thu, 01 Jan 1970 00:00:00 GMT");

if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit(); }

$user_id = $_SESSION['user_id'];
$result  = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user    = mysqli_fetch_assoc($result);
$role    = strtolower(trim($user['role']));

if ($role !== 'admin') { header("Location: dashboard.php"); exit(); }

// --- Corrected Verification Logic ---

// Verify request
if (isset($_GET['verify'])) {
    $id = (int)$_GET['verify']; // Get the ID directly from the 'verify' parameter
    mysqli_query($conn, "UPDATE verification_requests SET status='verified' WHERE id=$id");
    
    // Fetch user info for email
    $res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT full_name, email FROM verification_requests WHERE id=$id"));
    if ($res && !empty($res['email'])) {
        $subject = "Verification Successful - HousingHub";
        $message = "Dear " . htmlspecialchars($res['full_name']) . ",\n\n"
                 . "Congratulations! Your verification request has been approved. You can now access all features.\n\n"
                 . "Best regards,\nHousingHub Team";
        send_mail($res['email'], $subject, $message);
    }
    $_SESSION['admin_success'] = "Broker verified successfully.";
    header("Location: admin_dashboard.php?page=broker_documents");
    exit;
}

// Reject request
if (isset($_GET['reject'])) {
    $id = (int)$_GET['reject']; // Get the ID directly from the 'reject' parameter
    mysqli_query($conn, "UPDATE verification_requests SET status='rejected' WHERE id=$id");
    
    $res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT full_name, email FROM verification_requests WHERE id=$id"));
    if ($res && !empty($res['email'])) {
        $subject = "Verification Update - HousingHub";
        $message = "Dear " . htmlspecialchars($res['full_name']) . ",\n\n"
                 . "We regret to inform you that your verification request was not successful. Please try again or contact support.\n\n"
                 . "Best,\nHousingHub Team";
        send_mail($res['email'], $subject, $message);
    }
    $_SESSION['admin_error'] = "Broker verification rejected.";
    header("Location: admin_dashboard.php?page=broker_documents");
    exit;
}
// ── Manual Payment Approval Handler ──
if (isset($_GET['pay_action']) && isset($_GET['pay_id'])) {
    $pay_id = (int)$_GET['pay_id'];
    $pay_action = mysqli_real_escape_string($conn, $_GET['pay_action']);

    if ($pay_action === 'approve') {
        // Mark as paid
        mysqli_query($conn, "UPDATE payments SET status='paid', updated_at=NOW() WHERE id=$pay_id");
        
        // Fetch user and property info for notification
        $pay_info = mysqli_fetch_assoc(mysqli_query($conn, "SELECT p.*, u.fullname, u.email, pr.property_name 
            FROM payments p 
            JOIN users u ON p.tenant_id = u.id 
            JOIN properties pr ON p.property_id = pr.id 
            WHERE p.id=$pay_id"));

        if ($pay_info) {
            // Send Email Confirmation
            $subj = "Payment Verified - HousingHub";
            $body = "Dear " . $pay_info['fullname'] . ",\n\nWe have verified your payment for " . $pay_info['property_name'] . " (Trans ID: " . $pay_info['transaction_ref'] . ").\n\nYour access is now active.";
            send_mail($pay_info['email'], $subj, $body);

            // Add Portal Notification
            $u_id = $pay_info['tenant_id'];
            mysqli_query($conn, "INSERT INTO notifications (user_id, title, message, status, date) 
                VALUES ($u_id, 'Payment Received', 'Your payment for " . mysqli_real_escape_string($conn, $pay_info['property_name']) . " was verified. Confirm with the support team to ensure the receipt is received.', 'unread', NOW())");
        }
        
        $_SESSION['admin_success'] = "Payment approved and user notified.";

    } elseif ($pay_action === 'reject') {
        // Mark as failed
        mysqli_query($conn, "UPDATE payments SET status='failed', updated_at=NOW() WHERE id=$pay_id");
        $_SESSION['admin_error'] = "Payment marked as failed/invalid.";
    }

    header("Location: admin_dashboard.php?page=payments"); 
    exit();
}

// ── Stats ──
$total_brokers       = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM users WHERE role='broker'"))['count'];
$total_owners        = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM users WHERE role='owner'"))['count'];
$total_guests        = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM guests"))['count'];
$total_complaints    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM complaints WHERE status='pending'"))['count'];
$total_notifications = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM notifications WHERE is_read=0"))['count'];
// New: Count both 'pending' (started) and 'pending_verification' (user submitted ID)
$pending_payments = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM payments WHERE status='pending'"))['count'];
$awaiting_verify = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM payments WHERE status='pending_verification'"))['count'];
$total_properties    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM properties"))['count'];
$total_tenants       = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM tenants"))['count'];
$total_staff         = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM users WHERE role='staff'"))['count'];
$pending_applications= mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM job_applications WHERE status='pending'"))['count'];
$pending_requests    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM maintenance_requests WHERE status='pending'"))['count'];
// Safe check — tenant_applications table may not exist yet
$pending_tenant_apps = 0;
$_ta_check = mysqli_query($conn, "SHOW TABLES LIKE 'tenant_applications'");
if ($_ta_check && mysqli_num_rows($_ta_check) > 0) {
    $pending_tenant_apps = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM tenant_applications WHERE status='pending'"))['count'] ?? 0;
}

// Safe check — lease_applications table
$pending_lease_apps = 0;
$_la_check = mysqli_query($conn, "SHOW TABLES LIKE 'lease_applications'");
if ($_la_check && mysqli_num_rows($_la_check) > 0) {
    $pending_lease_apps = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM lease_applications WHERE status='pending'"))['count'] ?? 0;
}
$revenue             = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) as total FROM payments"))['total'];
$unlinked_count      = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM tenants WHERE user_id IS NULL OR user_id = 0"))['count'];
// Property applications count
$pending_prop_apps = 0;
$_pa_check = mysqli_query($conn, "SHOW TABLES LIKE 'property_applications'");
if ($_pa_check && mysqli_num_rows($_pa_check) > 0) {
    $pending_prop_apps = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as count FROM property_applications WHERE status='pending'"))['count'] ?? 0;
}

$page = $_GET['page'] ?? 'dashboard';
// Fetch count of pending viewing requests
$pending_viewing_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM property_viewing_requests WHERE status='pending'"))['c'] ?? 0;

// Fetch count of pending guest requests
$pending_guest_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM tenant_guest_requests WHERE status='pending'"))['c'] ?? 0;
// ── User agreements handlers (before HTML) ──
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS user_agreements (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, property_id INT DEFAULT NULL, agreed TINYINT(1) DEFAULT 0, agreed_at DATETIME DEFAULT NULL, ip_address VARCHAR(60) DEFAULT NULL, agreement_type VARCHAR(100) DEFAULT 'lease_terms', notes TEXT DEFAULT NULL, created_at DATETIME DEFAULT NOW()) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
// Add created_at column if table was created by an older version without it
$_ua_cols = mysqli_query($conn, "SHOW COLUMNS FROM user_agreements LIKE 'created_at'");
if (!$_ua_cols || mysqli_num_rows($_ua_cols) === 0) {
    mysqli_query($conn, "ALTER TABLE user_agreements ADD COLUMN created_at DATETIME DEFAULT NOW()");
}
if (isset($_GET['delete_agreement'])) { $del_id=(int)$_GET['delete_agreement']; mysqli_query($conn,"DELETE FROM user_agreements WHERE id=$del_id"); $_SESSION['admin_success']="Agreement deleted."; header("Location: admin_dashboard.php?page=agreed_users"); exit(); }
if (isset($_GET['mark_agreed']) && isset($_GET['ua_id'])) { $ua_id=(int)$_GET['ua_id']; $now=date('Y-m-d H:i:s'); $rb=mysqli_real_escape_string($conn,$user['fullname']); mysqli_query($conn,"UPDATE user_agreements SET agreed=1,agreed_at='$now',notes=CONCAT(COALESCE(notes,''),' [Confirmed by $rb]') WHERE id=$ua_id"); $_SESSION['admin_success']="Agreement #$ua_id marked as agreed."; header("Location: admin_dashboard.php?page=agreed_users"); exit(); }
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_agreement'])) { $ua_uid=(int)$_POST['ua_user_id']; $ua_pid=(int)($_POST['ua_property_id']??0); $ua_type=mysqli_real_escape_string($conn,trim($_POST['ua_type']??'lease_terms')); $ua_ok=isset($_POST['ua_agreed'])?1:0; $ua_notes=mysqli_real_escape_string($conn,trim($_POST['ua_notes']??'')); if($ua_uid>0){ $av=$ua_ok?"'".date('Y-m-d H:i:s')."'":'NULL'; $pv=$ua_pid>0?$ua_pid:'NULL'; mysqli_query($conn,"INSERT INTO user_agreements (user_id,property_id,agreed,agreed_at,agreement_type,notes) VALUES ($ua_uid,$pv,$ua_ok,$av,'$ua_type','$ua_notes')"); $_SESSION['admin_success']="Agreement record added."; } header("Location: admin_dashboard.php?page=agreed_users"); exit(); }


// ── Handle tenant link ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'link_account') {
    $tenant_id    = (int)$_POST['tenant_id'];
    $link_user_id = (int)$_POST['link_user_id'];
    if ($tenant_id > 0 && $link_user_id > 0) {
        $check = mysqli_query($conn,"SELECT id FROM tenants WHERE user_id='$link_user_id' AND id!='$tenant_id' LIMIT 1");
        if (mysqli_num_rows($check) > 0) {
            $_SESSION['admin_error'] = "This user account is already linked to another tenant.";
        } else {
            mysqli_query($conn,"UPDATE tenants SET user_id='$link_user_id' WHERE id='$tenant_id'");
            $_SESSION['admin_success'] = "Account linked successfully!";
        }
    }
    header("Location: admin_dashboard.php?page=tenants"); exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'unlink_account') {
    $tenant_id = (int)$_POST['tenant_id'];
    if ($tenant_id > 0) {
        mysqli_query($conn,"UPDATE tenants SET user_id=NULL WHERE id='$tenant_id'");
        $_SESSION['admin_success'] = "Account unlinked.";
    }
    header("Location: admin_dashboard.php?page=tenants"); exit();
}

// ── Auto-create user account and link to tenant ──
if (isset($_GET['auto_link'])) {
    $al_tid = (int)$_GET['auto_link'];
    if ($al_tid > 0) {
        $al_tenant = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tenants WHERE id=$al_tid LIMIT 1"));
        if ($al_tenant && !empty($al_tenant['email'])) {
            $al_email = mysqli_real_escape_string($conn, $al_tenant['email']);
            $al_name  = mysqli_real_escape_string($conn, $al_tenant['fullname']);
            $al_phone = mysqli_real_escape_string($conn, $al_tenant['phone'] ?? '');
            // Check if email already exists
            $al_exists = mysqli_fetch_assoc(mysqli_query($conn, "SELECT id FROM users WHERE email='$al_email' LIMIT 1"));
            if ($al_exists) {
                // Just link to existing user
                $al_uid = (int)$al_exists['id'];
                mysqli_query($conn, "UPDATE tenants SET user_id=$al_uid WHERE id=$al_tid");
                $_SESSION['admin_success'] = "Linked tenant <strong>" . htmlspecialchars($al_tenant['fullname']) . "</strong> to existing account.";
            } else {
                // Create new user account
                $al_pass = password_hash('housing123', PASSWORD_DEFAULT);
                mysqli_query($conn, "INSERT INTO users (fullname, email, phone, password, role, created_at) VALUES ('$al_name','$al_email','$al_phone','$al_pass','tenant',NOW())");
                $al_uid = mysqli_insert_id($conn);
                if ($al_uid > 0) {
                    mysqli_query($conn, "UPDATE tenants SET user_id=$al_uid WHERE id=$al_tid");
                    $_SESSION['admin_success'] = "✅ Account created and linked for <strong>" . htmlspecialchars($al_tenant['fullname']) . "</strong>. Login: <strong>" . htmlspecialchars($al_tenant['email']) . "</strong> | Password: <strong>housing123</strong> — Please inform the tenant to change this password after first login.";
                } else {
                    $_SESSION['admin_error'] = "Could not create account. Check that the email is valid.";
                }
            }
        } else {
            $_SESSION['admin_error'] = "Tenant not found or has no email address. Add their email first via Edit.";
        }
    }
    header("Location: admin_dashboard.php?page=tenants"); exit();
}

// ── Handle assign property to owner ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['owner_id']) && isset($_POST['property_id'])) {
    $ow_id = (int)$_POST['owner_id'];
    $pr_id = (int)$_POST['property_id'];
    if ($ow_id <= 0) {
        $_SESSION['admin_error'] = "No owner selected.";
        header("Location: admin_dashboard.php?page=propertyowners"); exit();
    }
    if ($pr_id <= 0) {
        $_SESSION['admin_error'] = "Please select a property from the dropdown first.";
        header("Location: admin_dashboard.php?page=propertyowners"); exit();
    }
    $ow_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT fullname FROM users WHERE id=$ow_id LIMIT 1"));
    $pr_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT property_name FROM properties WHERE id=$pr_id LIMIT 1"));
    if (!$ow_row || !$pr_row) {
        $_SESSION['admin_error'] = "Owner or property not found.";
        header("Location: admin_dashboard.php?page=propertyowners"); exit();
    }
    mysqli_query($conn, "UPDATE properties SET owner_id=$ow_id WHERE id=$pr_id");
    $pn_safe = mysqli_real_escape_string($conn, $pr_row['property_name']);
    mysqli_query($conn, "INSERT INTO notifications (user_id, tenant_id, title, message, status, date)
        VALUES ($ow_id, 0, 'Property Assigned',
        'The property $pn_safe has been linked to your account. Log in to view your dashboard.',
        'unread', NOW())");
    $_SESSION['admin_success'] = "✅ <strong>" . htmlspecialchars($pr_row['property_name']) . "</strong> assigned to <strong>" . htmlspecialchars($ow_row['fullname']) . "</strong>. Dashboard is now active.";
    header("Location: admin_dashboard.php?page=propertyowners"); exit();
}

// ── Handle notice board post ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'post_notice') {
    $ntitle = mysqli_real_escape_string($conn, trim($_POST['notice_title']));
    $nmsg   = mysqli_real_escape_string($conn, trim($_POST['notice_message']));
    if ($ntitle && $nmsg) {
        mysqli_query($conn,"INSERT INTO notifications (user_id, tenant_id, title, message, status, date)
            VALUES (0, 0, '$ntitle', '$nmsg', 'unread', NOW())");
        $_SESSION['admin_success'] = "Notice posted to all staff successfully.";
    }
    header("Location: admin_dashboard.php?page=notice_board"); exit();
}
// Delete notice
if (isset($_GET['delete_notice'])) {
    $nid = (int)$_GET['delete_notice'];
    mysqli_query($conn,"DELETE FROM notifications WHERE id=$nid AND user_id=0 AND tenant_id=0");
    $_SESSION['admin_success'] = "Notice deleted.";
    header("Location: admin_dashboard.php?page=notice_board"); exit();
}

// ── Tenant Application: status update ──
if (isset($_GET['app_action']) && isset($_GET['app_id'])) {
    $app_id     = (int)$_GET['app_id'];
    $app_action = mysqli_real_escape_string($conn, $_GET['app_action']);
    $valid_actions = ['approved','rejected','pending','reviewing'];
    if (in_array($app_action, $valid_actions)) {
        $reviewed_at = date('Y-m-d H:i:s');
        $reviewed_by = mysqli_real_escape_string($conn, $user['fullname']);
        mysqli_query($conn, "UPDATE tenant_applications SET status='$app_action', reviewed_by='$reviewed_by', reviewed_at='$reviewed_at' WHERE id=$app_id");
        $app_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT fullname, email, property_id FROM tenant_applications WHERE id=$app_id LIMIT 1"));
        if ($app_row) {
            $aname      = $app_row['fullname'];
            $aemail     = $app_row['email'] ?? '';
            $aname_safe = mysqli_real_escape_string($conn, $aname);

            if ($app_action === 'approved') {
                $title      = "Application Approved";
                $msg        = "Congratulations $aname_safe! Your rental application has been approved. Our team will contact you shortly to proceed with the lease signing.";
                $email_subj = "Your HousingHub Application Has Been Approved!";
                $email_body = "Dear $aname,\n\nGreat news!\n\nYour rental application submitted on HousingHub has been APPROVED.\n\n"
                    . "================================================\n"
                    . "APPLICATION APPROVED\n"
                    . "================================================\n"
                    . "Applicant  : $aname\n"
                    . "Status     : APPROVED\n"
                    . "Reviewed on: " . date('d M Y, H:i') . "\n"
                    . "================================================\n\n"
                    . "NEXT STEPS:\n"
                    . "1. Our team will contact you within 24 hours to arrange lease signing.\n"
                    . "2. Prepare your National ID and any required deposit.\n"
                    . "3. Once the lease is signed, you will receive your move-in details.\n\n"
                    . "If you have any questions, contact us at support@housinghuborg.ug\n\n"
                    . "Welcome to HousingHub!\n\nWarm regards,\nHousingHub Team\nsupport@housinghuborg.ug";

            } elseif ($app_action === 'rejected') {
                $title      = "Application Update";
                $msg        = "Dear $aname_safe, after careful review we regret to inform you that your application was not successful at this time. You are welcome to apply for other properties.";
                $email_subj = "Update on Your HousingHub Application";
                $email_body = "Dear $aname,\n\nThank you for applying through HousingHub.\n\n"
                    . "After careful review, we regret to inform you that we are unable to proceed with your application at this time.\n\n"
                    . "================================================\n"
                    . "APPLICATION STATUS UPDATE\n"
                    . "================================================\n"
                    . "Applicant  : $aname\n"
                    . "Status     : Unsuccessful\n"
                    . "Reviewed on: " . date('d M Y, H:i') . "\n"
                    . "================================================\n\n"
                    . "This does not prevent you from applying for other properties on HousingHub.\n\n"
                    . "Browse listings at: https://housinghuborg.ug/properties.php\n\n"
                    . "For feedback, contact us at support@housinghuborg.ug\n\n"
                    . "We wish you all the best.\n\nRegards,\nHousingHub Team\nsupport@housinghuborg.ug";

            } elseif ($app_action === 'reviewing') {
                $title      = "Application Under Review";
                $msg        = "Dear $aname_safe, your application is currently under review. We will get back to you within 24-48 hours.";
                $email_subj = "Your HousingHub Application Is Under Review";
                $email_body = "Dear $aname,\n\nThank you for submitting your rental application through HousingHub.\n\n"
                    . "================================================\n"
                    . "APPLICATION UNDER REVIEW\n"
                    . "================================================\n"
                    . "Applicant  : $aname\n"
                    . "Status     : Under Review\n"
                    . "Updated on : " . date('d M Y, H:i') . "\n"
                    . "================================================\n\n"
                    . "Our team is reviewing your application. You can expect a decision within 24-48 hours.\n\n"
                    . "We will contact you via email or phone once the review is complete.\n\n"
                    . "If you have questions, reach us at support@housinghuborg.ug\n\n"
                    . "Regards,\nHousingHub Team\nsupport@housinghuborg.ug";

            } else {
                $title      = "Application Status Update";
                $msg        = "Your application status has been updated to: " . ucfirst($app_action) . ".";
                $email_subj = "HousingHub Application Update";
                $email_body = "Dear $aname,\n\nYour application status has been updated to: " . ucfirst($app_action) . ".\n\nRegards,\nHousingHub Team";
            }

            // ── Save portal notification ──
            $msg_safe   = mysqli_real_escape_string($conn, $msg);
            $title_safe = mysqli_real_escape_string($conn, $title);
            mysqli_query($conn, "INSERT INTO notifications (user_id, tenant_id, title, message, status, date)
                VALUES (0, 0, '$title_safe', '$msg_safe', 'unread', NOW())");

            // ── Send email ──
            if (!empty($aemail)) {
                $email_sent = send_mail($aemail, $email_subj, $email_body);
                $_SESSION['admin_success'] = "Application #$app_id marked as <strong>" . ucfirst($app_action) . "</strong>."
                    . ($email_sent ? " Email sent to <strong>$aemail</strong>." : " <em style='color:#fca5a5'>Email could not be sent (check PHP mail config).</em>");
            } else {
                $_SESSION['admin_success'] = "Application #$app_id marked as <strong>" . ucfirst($app_action) . "</strong>. No email on file.";
            }
        } else {
            $_SESSION['admin_success'] = "Application #$app_id marked as <strong>" . ucfirst($app_action) . "</strong>.";
        }
    }
    header("Location: admin_dashboard.php?page=tenant_applications"); exit();
}

// ── Tenant Application: save admin notes ──
if (isset($_POST['save_app_notes'])) {
    $app_id    = (int)$_POST['app_id'];
    $app_notes = mysqli_real_escape_string($conn, trim($_POST['admin_notes'] ?? ''));
    mysqli_query($conn, "UPDATE tenant_applications SET admin_notes='$app_notes' WHERE id=$app_id");
    $_SESSION['admin_success'] = "Notes saved for application #$app_id.";
    header("Location: admin_dashboard.php?page=tenant_applications"); exit();
}

// Verify request
if (isset($_GET['verify']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    mysqli_query($conn, "UPDATE verification_requests SET status='verified' WHERE id=$id");
    // Fetch user info
    $res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT full_name, email FROM verification_requests WHERE id=$id"));
    if ($res && !empty($res['email'])) {
        $subject = "Verification Successful - HousingPlatform";
        $message = "Dear " . htmlspecialchars($res['full_name']) . ",\n\n"
                 . "Congratulations! Your verification request has been approved. You can now access all features.\n\n"
                 . "Best regards,\nHousingPlatform Team";
        send_mail($res['email'], $subject, $message);
    }
    header("Location: admin_dashboard.php?page=broker_documents");
    exit;
}

// Reject request
if (isset($_GET['reject']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    mysqli_query($conn, "UPDATE verification_requests SET status='rejected' WHERE id=$id");
    $res = mysqli_fetch_assoc(mysqli_query($conn, "SELECT full_name, email FROM verification_requests WHERE id=$id"));
    if ($res && !empty($res['email'])) {
        $subject = "Verification Update - HousingPlatform";
        $message = "Dear " . htmlspecialchars($res['full_name']) . ",\n\n"
                 . "We regret to inform you that your verification request was not successful. Please try again or contact support.\n\n"
                 . "Best,\nHousingPlatform Team";
        send_mail($res['email'], $subject, $message);
    }
    header("Location: admin_dashboard.php?page=broker_documents");
    exit;
}

// ── Handle property application status update ──
if (isset($_GET['pa_action']) && isset($_GET['pa_id'])) {
    $pa_id     = (int)$_GET['pa_id'];
    $pa_action = mysqli_real_escape_string($conn, $_GET['pa_action']);
    if (in_array($pa_action, ['approved','rejected','pending','reviewing','contacted'])) {
        $rb = mysqli_real_escape_string($conn, $user['fullname']);
        $ra = date('Y-m-d H:i:s');
        mysqli_query($conn, "UPDATE property_applications SET status='$pa_action', reviewed_by='$rb', reviewed_at='$ra' WHERE id=$pa_id");
        if ($pa_action === 'approved') {
            $pa_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM property_applications WHERE id=$pa_id LIMIT 1"));
            if ($pa_row && !empty($pa_row['email'])) {
                send_mail($pa_row['email'],
                    "Your HousingHub Property Application Has Been Approved!",
                    "Dear {$pa_row['fullname']},\n\nYour application to have HousingHub manage {$pa_row['property_name']} has been approved. Our team will contact you shortly.\n\nHousingHub Team"
                );
            }
        }
        $_SESSION['admin_success'] = "Application #$pa_id marked as <strong>" . ucfirst($pa_action) . "</strong>.";
    }
    header("Location: admin_dashboard.php?page=property_applications"); exit();
}

// ── Handle property application admin notes ──
if (isset($_POST['save_pa_notes'])) {
    $pa_id    = (int)$_POST['pa_id'];
    $pa_notes = mysqli_real_escape_string($conn, trim($_POST['pa_admin_notes'] ?? ''));
    mysqli_query($conn, "UPDATE property_applications SET admin_notes='$pa_notes' WHERE id=$pa_id");
    $_SESSION['admin_success'] = "Notes saved.";
    header("Location: admin_dashboard.php?page=property_applications"); exit();
}
// Assign property to broker
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_property_broker'])) {
    $b_id  = (int)$_POST['broker_id_assign'];
    $pr_id = (int)$_POST['property_id_broker'];
    if ($b_id > 0 && $pr_id > 0) {
        $brow = mysqli_fetch_assoc(mysqli_query($conn,"SELECT fullname FROM users WHERE id=$b_id LIMIT 1"));
        $prow = mysqli_fetch_assoc(mysqli_query($conn,"SELECT property_name FROM properties WHERE id=$pr_id LIMIT 1"));
        mysqli_query($conn,"UPDATE properties SET broker_id=$b_id WHERE id=$pr_id");
        $pname_s = mysqli_real_escape_string($conn, $prow['property_name'] ?? '');
        mysqli_query($conn,"INSERT INTO notifications (user_id,tenant_id,title,message,status,date)
            VALUES ($b_id,0,'Property Assigned','The property $pname_s has been assigned to your broker account.','unread',NOW())");
        $_SESSION['admin_success'] = "Property <strong>".$prow['property_name']."</strong> assigned to broker <strong>".$brow['fullname']."</strong>.";
    }
    header("Location: admin_dashboard.php?page=broker_management"); exit();
}
 
// Update broker commission rate
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_commission'])) {
    $b_id   = (int)$_POST['broker_id_commission'];
    $rate   = min(50, max(0, (float)$_POST['commission_rate']));
    if ($b_id > 0) {
        mysqli_query($conn,"UPDATE users SET commission_rate=$rate WHERE id=$b_id AND role='broker'");
        $_SESSION['admin_success'] = "Commission rate updated to {$rate}%.";
    }
    header("Location: admin_dashboard.php?page=broker_management"); exit();
}
 
// Suspend / Activate broker
if (isset($_GET['broker_action']) && isset($_GET['broker_id'])) {
    $b_id    = (int)$_GET['broker_id'];
    $b_act   = mysqli_real_escape_string($conn, $_GET['broker_action']);
    if (in_array($b_act, ['suspend','activate']) && $b_id > 0) {
        $new_status = ($b_act === 'suspend') ? 'suspended' : 'active';
        mysqli_query($conn,"UPDATE users SET status='$new_status' WHERE id=$b_id AND role='broker'");
        $msg = $b_act === 'suspend' ? 'Broker account suspended.' : 'Broker account reactivated.';
        $_SESSION['admin_success'] = $msg;
    }
    header("Location: admin_dashboard.php?page=broker_management"); exit();
}
 
// Remove broker from property
if (isset($_GET['unassign_broker']) && isset($_GET['prop_id'])) {
    $prop_id = (int)$_GET['prop_id'];
    mysqli_query($conn,"UPDATE properties SET broker_id=NULL WHERE id=$prop_id");
    $_SESSION['admin_success'] = "Broker unassigned from property.";
    header("Location: admin_dashboard.php?page=broker_management"); exit();
}
// Auto-create broker submissions table
mysqli_query($conn, "CREATE TABLE IF NOT EXISTS broker_property_submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    broker_id INT NOT NULL,
    property_name VARCHAR(200) NOT NULL,
    property_type VARCHAR(100) DEFAULT NULL,
    address TEXT DEFAULT NULL,
    units INT DEFAULT 1,
    rent_amount DECIMAL(12,2) DEFAULT 0,
    bedrooms INT DEFAULT 0,
    size_sqft INT DEFAULT NULL,
    amenities TEXT DEFAULT NULL,
    description TEXT DEFAULT NULL,
    purpose VARCHAR(50) DEFAULT 'rent',
    latitude VARCHAR(50) DEFAULT NULL,
    longitude VARCHAR(50) DEFAULT NULL,
    commission_rate DECIMAL(5,2) DEFAULT 10,
    commission_percentage DECIMAL(5,2) DEFAULT 10,
    property_image VARCHAR(300) DEFAULT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    admin_notes TEXT DEFAULT NULL,
    reviewed_by VARCHAR(200) DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT NOW()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
 
// Handle submission status update
if (isset($_GET['bps_action']) && isset($_GET['bps_id'])) {
    $bps_id     = (int)$_GET['bps_id'];
    $bps_action = mysqli_real_escape_string($conn, $_GET['bps_action']);
 
    if (in_array($bps_action, ['approved', 'rejected', 'pending', 'reviewing'])) {
        $rb = mysqli_real_escape_string($conn, $user['fullname']);
        $ra = date('Y-m-d H:i:s');
        mysqli_query($conn, "UPDATE broker_property_submissions SET status='$bps_action', reviewed_by='$rb', reviewed_at='$ra' WHERE id=$bps_id");
 
        // If approved → copy into properties table
        if ($bps_action === 'approved') {
            $sub = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM broker_property_submissions WHERE id=$bps_id LIMIT 1"));
            if ($sub) {
                $pn  = mysqli_real_escape_string($conn, $sub['property_name']);
                $pt  = mysqli_real_escape_string($conn, $sub['property_type'] ?? '');
                $pa  = mysqli_real_escape_string($conn, $sub['address'] ?? '');
                $pu  = (int)$sub['units'];
                $pr  = (float)$sub['rent_amount'];
                $pb  = (int)$sub['bedrooms'];
                $psq = $sub['size_sqft'] ? (int)$sub['size_sqft'] : 'NULL';
                $pam = mysqli_real_escape_string($conn, $sub['amenities'] ?? '');
                $pd  = mysqli_real_escape_string($conn, $sub['description'] ?? '');
                $pp  = mysqli_real_escape_string($conn, $sub['purpose'] ?? 'rent');
                $plat = mysqli_real_escape_string($conn, $sub['latitude'] ?? '');
                $plng = mysqli_real_escape_string($conn, $sub['longitude'] ?? '');
                $pcp = (float)$sub['commission_percentage'];
                $pimg = mysqli_real_escape_string($conn, $sub['property_image'] ?? '');
                $bid  = (int)$sub['broker_id'];
 
                // Insert into properties
                $insert = mysqli_query($conn,
                    "INSERT INTO properties
                        (property_name, property_type, address, units, rent_amount, bedrooms,
                         size_sqft, amenities, description, purpose, latitude, longitude,
                         commission_percentage, property_image, broker_id, status, created_at)
                     VALUES
                        ('$pn','$pt','$pa',$pu,$pr,$pb,
                         ".($sub['size_sqft'] ? (int)$sub['size_sqft'] : 'NULL').",'$pam','$pd','$pp','$plat','$plng',
                         $pcp,'$pimg',$bid,'available',NOW())"
                );
 
                if ($insert) {
                    $new_prop_id = mysqli_insert_id($conn);
                    // Notify the broker
                    $pn_safe = mysqli_real_escape_string($conn, $sub['property_name']);
                    mysqli_query($conn,
                        "INSERT INTO notifications (user_id, tenant_id, title, message, status, date)
                         VALUES ($bid, 0, 'Property Approved',
                         'Your submitted property \"$pn_safe\" has been approved and is now live on HousingHub.',
                         'unread', NOW())"
                    );
                    $_SESSION['admin_success'] = "Submission approved and property <strong>$pn</strong> is now live. Property ID: #$new_prop_id";
                } else {
                    $_SESSION['admin_error'] = "Submission approved but failed to copy to properties table. Check DB structure.";
                }
            }
        } elseif ($bps_action === 'rejected') {
            $sub = mysqli_fetch_assoc(mysqli_query($conn, "SELECT broker_id, property_name FROM broker_property_submissions WHERE id=$bps_id LIMIT 1"));
            if ($sub) {
                $bid    = (int)$sub['broker_id'];
                $pn_rej = mysqli_real_escape_string($conn, $sub['property_name']);
                mysqli_query($conn,
                    "INSERT INTO notifications (user_id, tenant_id, title, message, status, date)
                     VALUES ($bid, 0, 'Property Submission Rejected',
                     'Your submitted property \"$pn_rej\" was not approved. Please check admin notes or contact support.',
                     'unread', NOW())"
                );
            }
            $_SESSION['admin_success'] = "Submission #$bps_id rejected and broker notified.";
        } else {
            $_SESSION['admin_success'] = "Submission #$bps_id marked as <strong>" . ucfirst($bps_action) . "</strong>.";
        }
    }
    header("Location: admin_dashboard.php?page=broker_submissions"); exit();
}
 
// Handle admin notes save
if (isset($_POST['save_bps_notes'])) {
    $bps_id    = (int)$_POST['bps_id'];
    $bps_notes = mysqli_real_escape_string($conn, trim($_POST['bps_admin_notes'] ?? ''));
    mysqli_query($conn, "UPDATE broker_property_submissions SET admin_notes='$bps_notes' WHERE id=$bps_id");
    $_SESSION['admin_success'] = "Notes saved for submission #$bps_id.";
    header("Location: admin_dashboard.php?page=broker_submissions"); exit();
}
 
// Handle delete
if (isset($_GET['delete_bps'])) {
    $bps_id = (int)$_GET['delete_bps'];
    $sub = mysqli_fetch_assoc(mysqli_query($conn, "SELECT property_image FROM broker_property_submissions WHERE id=$bps_id LIMIT 1"));
    if ($sub && !empty($sub['property_image']) && file_exists($sub['property_image'])) {
        @unlink($sub['property_image']);
    }
    mysqli_query($conn, "DELETE FROM broker_property_submissions WHERE id=$bps_id");
    $_SESSION['admin_success'] = "Submission deleted.";
    header("Location: admin_dashboard.php?page=broker_submissions"); exit();
}

?>

 


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | HousingHub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25);--red:#ef4444;--green:#16a34a;--sw:260px}
html,body{height:100%;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden}
body::before{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
.sidebar{position:fixed;left:0;top:0;width:var(--sw);height:100%;background:rgba(4,9,26,.98);border-right:1px solid var(--border);color:var(--white);display:flex;flex-direction:column;overflow-y:auto;z-index:1000}
.sidebar::-webkit-scrollbar{width:3px}.sidebar::-webkit-scrollbar-thumb{background:var(--gb);border-radius:2px}
.sidebar h2{text-align:center;padding:24px 20px 20px;font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--gold);border-bottom:1px solid var(--border)}
.sidebar a{color:var(--muted);padding:11px 22px;text-decoration:none;display:block;transition:all .2s;font-size:13px;font-weight:500;border-left:3px solid transparent}
.sidebar a:hover{color:var(--white);background:rgba(255,255,255,.04);border-left-color:var(--gb)}
.sidebar a.active{color:var(--gold);background:rgba(200,164,60,.08);border-left-color:var(--gold)}
.sidebar .sb-section{font-size:9px;font-weight:700;letter-spacing:2.5px;text-transform:uppercase;color:rgba(255,255,255,.18);padding:14px 22px 4px;margin-top:6px}
.header{display:flex;justify-content:space-between;align-items:center;background:var(--gold);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);color:var(--white);padding:16px 36px;position:sticky;top:0;z-index:100;margin-left:var(--sw);box-shadow:0 2px 20px rgba(0,0,0,.3)}
.header h1{font-family:"Cormorant Garamond",serif;font-size:30px;font-weight:900;color:deepblue;letter-spacing:1px}
.header-right{display:flex;align-items:center;gap:10px}
.header-date{font-size:18px;color:var(--white)}
.logout-btn{color:var(--white);text-decoration:none;background:rgba(37, 34, 34, 0.97);border:1px solid rgba(239,68,68,.3);padding:9px 20px;border-radius:6px;font-size:12px;font-weight:600;letter-spacing:1px;text-transform:uppercase;transition:all .3s}
.logout-btn:hover{background:rgba(239,68,68,.3)}
.main-content{margin-left:var(--sw);padding:32px 40px;min-height:calc(100vh - 60px);position:relative;z-index:10}
section h2{margin-bottom:24px;font-family:"Cormorant Garamond",serif;font-size:28px;font-weight:700;color:var(--white);border-bottom:2px solid var(--gb);padding-bottom:12px}
.overview-cards{display:flex;flex-wrap:wrap;gap:20px;justify-content:center;margin-bottom:40px}
.circular-card{width:150px;height:150px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.15),rgba(14,90,200,.15));border:2px solid var(--gb);color:var(--white);display:flex;flex-direction:column;justify-content:center;align-items:center;text-align:center;box-shadow:0 8px 24px rgba(0,0,0,.4);transition:transform .3s,box-shadow .3s}
.circular-card:hover{transform:scale(1.06);box-shadow:0 12px 32px rgba(200,164,60,.2)}
.circular-card h3{margin:0 0 6px;font-size:12px;font-weight:500;letter-spacing:.5px;color:var(--muted);padding:0 10px;line-height:1.3}
.circular-card p{font-family:"Cormorant Garamond",serif;font-size:26px;font-weight:700;color:var(--gold);margin:0}
table{width:100%;border-collapse:collapse;margin-bottom:36px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.2)}
table th{background:rgba(200,164,60,.1);color:var(--gold);font-weight:600;font-size:11px;letter-spacing:1.5px;text-transform:uppercase;padding:14px 16px;text-align:left;border-bottom:1px solid var(--border)}
table td{padding:13px 16px;font-size:13px;color:rgba(255,255,255,.8);border-bottom:1px solid rgba(255,255,255,.04)}
table tr:last-child td{border-bottom:none}
table tr:hover td{background:rgba(200,164,60,.04)}
.action-btn{display:inline-block;padding:7px 14px;border-radius:6px;text-decoration:none;color:var(--white);background:rgba(200,164,60,.2);border:1px solid var(--gb);transition:all .25s;margin-right:4px;font-size:12px;font-weight:600;cursor:pointer;font-family:"Outfit",sans-serif}
.action-btn:hover{background:rgba(200,164,60,.35);transform:translateY(-2px)}
.alert{padding:14px 20px;border-radius:8px;margin-bottom:20px;font-size:13px;font-weight:500}
.alert.success{background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac}
.alert.error{background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5}
.link-badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;letter-spacing:.5px}
.link-badge.linked{background:rgba(22,163,74,.1);color:#86efac;border:1px solid rgba(22,163,74,.3)}
.link-badge.unlinked{background:rgba(200,164,60,.1);color:var(--gold);border:1px solid var(--gb)}
.link-form{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-top:6px}
.link-form select{padding:6px 10px;border-radius:6px;border:1px solid var(--border);font-size:12px;background:rgba(255,255,255,.06);color:var(--white);min-width:180px;font-family:"Outfit",sans-serif}
.link-form select option{background:var(--ink)}
.link-form button{padding:7px 14px;border-radius:6px;border:none;cursor:pointer;font-size:12px;font-weight:700;color:var(--white);font-family:"Outfit",sans-serif}
.btn-link{background:rgba(14,90,200,.4)}.btn-link:hover{background:rgba(14,90,200,.6)}
.btn-unlink{background:rgba(239,68,68,.2);color:#fca5a5}.btn-unlink:hover{background:rgba(239,68,68,.35)}
.unlinked-banner{background:rgba(200,164,60,.08);border:1px solid var(--gb);border-radius:10px;padding:14px 20px;margin-bottom:24px;display:flex;align-items:center;gap:12px;font-size:13px;color:var(--gold)}
input,select,textarea{background:rgba(255,255,255,.05);border:1px solid var(--border);color:var(--white);border-radius:6px;padding:10px 13px;font-family:"Outfit",sans-serif;font-size:13px;width:100%;outline:none;transition:border-color .25s;margin-bottom:12px}
input:focus,select:focus,textarea:focus{border-color:var(--gb)}
input::placeholder,textarea::placeholder{color:var(--muted)}
select option{background:var(--ink)}
label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
/* NOTICE BOARD */
.notice-card{background:rgba(14,90,200,.06);border:1px solid rgba(14,90,200,.2);border-radius:10px;padding:18px 20px;margin-bottom:14px;display:flex;justify-content:space-between;align-items:flex-start;gap:16px}
.notice-card:hover{border-color:rgba(14,90,200,.4)}
.notice-title{font-size:15px;font-weight:700;color:var(--white);margin-bottom:5px}
.notice-msg{font-size:13px;color:var(--muted);line-height:1.6}
.notice-date{font-size:11px;color:rgba(255,255,255,.2);margin-top:6px}
/* STAT ROW */
.stat-row{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:28px}
.stat-box{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;padding:18px;text-align:center;transition:border-color .3s}
.stat-box:hover{border-color:var(--gb)}
.stat-box-val{font-family:"Cormorant Garamond",serif;font-size:32px;font-weight:700;color:var(--gold);line-height:1}
.stat-box-lbl{font-size:11px;color:var(--muted);margin-top:4px;letter-spacing:.5px}
@media(max-width:900px){
  :root{--sw:0px}
  .sidebar{transform:translateX(-260px);width:260px}
  .main-content,.header{margin-left:0}
  table{font-size:12px}
  table th,table td{padding:10px}
  .main-content{padding:20px 16px}
  .stat-row{grid-template-columns:1fr 1fr}
}
</style>
</head>
<body>

<div class="sidebar">
  <h2>ADMIN PANEL</h2>
  <div class="sb-section">Overview</div>
  <a href="admin_dashboard.php?page=dashboard" <?php echo ($page==='dashboard')?'class="active"':''; ?>> Home</a>

  <div class="sb-section">People</div>
  <a href="admin_dashboard.php?page=users" <?php echo ($page==='users')?'class="active"':''; ?>>Manage Users</a>
  <a href="admin_dashboard.php?page=tenants" <?php echo ($page==='tenants')?'class="active"':''; ?>>
    Manage Tenants
    <?php if($unlinked_count > 0): ?><span style="background:#ef4444;color:white;border-radius:10px;padding:2px 8px;font-size:11px;margin-left:6px"><?= $unlinked_count ?></span><?php endif; ?>
  </a>
  <a href="admin_dashboard.php?page=brokers" <?php echo ($page==='brokers')?'class="active"':''; ?>> Brokers</a>
  <a href="admin_dashboard.php?page=broker_submissions" <?php echo ($page==='broker_submissions')?'class="active"':''; ?>>
  Broker Submissions
  <?php
      $pending_bps = 0;
     $bps_chk = mysqli_query($conn,"SHOW TABLES LIKE 'broker_property_submissions'");
     if ($bps_chk && mysqli_num_rows($bps_chk) > 0)
         $pending_bps = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM broker_property_submissions WHERE status='pending'"))['c'] ?? 0;      if($pending_bps > 0): ?>
        <span style="background:#ef4444;color:white;border-radius:10px;padding:2px 8px;font-size:11px;margin-left:6px"><?= $pending_bps ?></span>
   <?php endif; ?>
</a>
  <a href="admin_dashboard.php?page=broker_management" <?php echo ($page==='broker_management')?'class="active"':''; ?>>Broker Management</a>
  <a href="admin_dashboard.php?page=propertyowners" <?php echo ($page==='propertyowners')?'class="active"':''; ?>> Property Owners</a>

  <div class="sb-section">Staff</div>
  <a href="admin_dashboard.php?page=staff_roles" <?php echo ($page==='staff_roles')?'class="active"':''; ?>> Staff Roles & Payroll</a>
  <a href="admin_dashboard.php?page=staff_tasks" <?php echo ($page==='staff_tasks')?'class="active"':''; ?>> Staff Tasks</a>
  <a href="admin_dashboard.php?page=employee_performance" <?php echo ($page==='employee_performance')?'class="active"':''; ?>> Employee Performance</a>
  <a href="admin_dashboard.php?page=notice_board" <?php echo ($page==='notice_board')?'class="active"':''; ?>> Notice Board</a>
  <a href="admin_dashboard.php?page=jobs" <?php echo ($page==='jobs')?'class="active"':''; ?>>
     Employment Applications
    <?php if($pending_applications > 0): ?><span style="background:#ef4444;color:white;border-radius:10px;padding:2px 8px;font-size:11px;margin-left:6px"><?= $pending_applications ?></span><?php endif; ?>
  </a>

  <a href="admin_dashboard.php?page=tenant_applications" <?php echo ($page==='tenant_applications')?'class="active"':''; ?>>
    Tenant Applications
    <?php if(!empty($pending_tenant_apps) && $pending_tenant_apps > 0): ?><span style="background:#ef4444;color:white;border-radius:10px;padding:2px 8px;font-size:11px;margin-left:6px"><?= $pending_tenant_apps ?></span><?php endif; ?>
  </a>
  <a href="admin_dashboard.php?page=lease_applications" <?php echo ($page==='lease_applications')?'class="active"':''; ?>>
     Lease Applications
    <?php if(!empty($pending_lease_apps) && $pending_lease_apps > 0): ?><span style="background:#ef4444;color:white;border-radius:10px;padding:2px 8px;font-size:11px;margin-left:6px"><?= $pending_lease_apps ?></span><?php endif; ?>
  </a>
  <a href="admin_dashboard.php?page=property_applications" <?php echo ($page==='property_applications')?'class="active"':''; ?>>
     Property Applications
    <?php if(!empty($pending_prop_apps) && $pending_prop_apps > 0): ?><span style="background:#ef4444;color:white;border-radius:10px;padding:2px 8px;font-size:11px;margin-left:6px"><?= $pending_prop_apps ?></span><?php endif; ?>
  </a>

  <div class="sb-section">Viewings &amp; Guests</div>
  <a href="admin_dashboard.php?page=viewing_requests" <?php echo ($page==='viewing_requests')?'class="active"':''; ?>> Viewing Requests
    <?php if($pending_viewing_requests>0): ?><span style="background:#ef4444;color:white;border-radius:10px;padding:2px 8px;font-size:11px;margin-left:6px"><?=$pending_viewing_requests?></span><?php endif; ?>
  </a>
  <a href="admin_dashboard.php?page=guest_requests" <?php echo ($page==='guest_requests')?'class="active"':''; ?>> Guest Requests
    <?php if($pending_guest_requests>0): ?><span style="background:#ef4444;color:white;border-radius:10px;padding:2px 8px;font-size:11px;margin-left:6px"><?=$pending_guest_requests?></span><?php endif; ?>
  </a>
  <div class="sb-section">Properties</div>
  <a href="admin_dashboard.php?page=properties" <?php echo ($page==='properties')?'class="active"':''; ?>>Manage Properties</a>
  
  <a href="admin_dashboard.php?page=inspections" <?php echo ($page==='inspections')?'class="active"':''; ?>> Property Inspections</a>
  <a href="admin_dashboard.php?page=maintenance" <?php echo ($page==='maintenance')?'class="active"':''; ?>> Maintenance Requests</a>

  <div class="sb-section">Finance</div>
  <a href="admin_dashboard.php?page=tenant_payments" <?php echo ($page==='tenant_payments')?'class="active"':''; ?>> Tenant Payments</a>
  <a href="admin_dashboard.php?page=payments" <?php echo ($page==='payments')?'class="active"':''; ?>> Rent Tracking</a>
  <a href="admin_dashboard.php?page=revenue_reports" <?php echo ($page==='revenue_reports')?'class="active"':''; ?>> Revenue Reports</a>

  <div class="sb-section">Other</div>
  <a href="admin_dashboard.php?page=guests" <?php echo ($page==='guests')?'class="active"':''; ?>> Guest Approvals</a>
  <a href="admin_dashboard.php?page=complaints" <?php echo ($page==='complaints')?'class="active"':''; ?>>Complaints & Feedback</a>
  <a href="admin_dashboard.php?page=tenant_documents" <?php echo ($page==='tenant_documents')?'class="active"':''; ?>> Tenant Documents</a>
  <a href="admin_dashboard.php?page=broker_documents" <?php echo ($page==='broker_documents')?'class="active"':''; ?>>Broker Documents</a>
  <a href="admin_dashboard.php?page=notifications" <?php echo ($page==='notifications')?'class="active"':''; ?>> Notifications</a>
  <a href="admin_dashboard.php?page=agreed_users" <?php echo ($page==='agreed_users')?'class="active"':''; ?>> Agreed Users</a>
  <a href="admin_dashboard.php?page=settings" <?php echo ($page==='settings')?'class="active"':''; ?>> System Settings</a>
  <a href="admin_dashboard.php?page=backups" <?php echo ($page==='backups')?'class="active"':''; ?>> Backup / Export</a>
  <a href="logout.php" style="color:#fca5a5;margin-top:10px;border-top:1px solid var(--border)">Logout</a>
</div>

<div class="header">
  <h1>Welcome, <?php echo htmlspecialchars($user['fullname']); ?> &mdash; Admin</h1>
  <div class="header-right">
    <span class="header-date"><?= date('l, d F Y') ?></span>
    <a href="logout.php" class="logout-btn">Logout</a>
  </div>
</div>

<div class="main-content">
  <?php if($page === 'broker_management'): ?>
<section id="broker_management">
  <h2 style="text-align:center;color:var(--gold)">🏢 BROKER MANAGEMENT</h2>
  <p style="text-align:center;font-size:13px;color:var(--muted);margin-bottom:28px">Full control over all broker accounts — assign properties, set commissions, suspend or activate accounts.</p>
 
  <?php
  // Ensure commission_rate and status columns exist
  $cols = mysqli_query($conn,"SHOW COLUMNS FROM users LIKE 'commission_rate'");
  if (!$cols || mysqli_num_rows($cols) === 0)
      mysqli_query($conn,"ALTER TABLE users ADD COLUMN commission_rate DECIMAL(5,2) DEFAULT 10.00");
 
  $scols = mysqli_query($conn,"SHOW COLUMNS FROM users LIKE 'status'");
  if (!$scols || mysqli_num_rows($scols) === 0)
      mysqli_query($conn,"ALTER TABLE users ADD COLUMN status VARCHAR(50) DEFAULT 'active'");
 
  // Broker stats
  $total_b     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM users WHERE role='broker'"))['c'] ?? 0;
  $active_b    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM users WHERE role='broker' AND (status='active' OR status IS NULL OR status='')"))['c'] ?? 0;
  $suspended_b = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM users WHERE role='broker' AND status='suspended'"))['c'] ?? 0;
 
  $r_bi = mysqli_query($conn,"SHOW COLUMNS FROM properties LIKE 'broker_id'");
  if (!$r_bi || mysqli_num_rows($r_bi) === 0)
      mysqli_query($conn,"ALTER TABLE properties ADD COLUMN broker_id INT DEFAULT NULL");
 
  $assigned_props = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM properties WHERE broker_id IS NOT NULL AND broker_id > 0"))['c'] ?? 0;
  ?>
 
  <!-- STAT ROW -->
  <div class="stat-row" style="margin-bottom:28px">
    <div class="stat-box"><div class="stat-box-val"><?= $total_b ?></div><div class="stat-box-lbl">Total Brokers</div></div>
    <div class="stat-box" style="border-color:rgba(22,163,74,.3)"><div class="stat-box-val" style="color:#86efac"><?= $active_b ?></div><div class="stat-box-lbl">Active</div></div>
    <div class="stat-box" style="border-color:rgba(239,68,68,.3)"><div class="stat-box-val" style="color:#fca5a5"><?= $suspended_b ?></div><div class="stat-box-lbl">Suspended</div></div>
    <div class="stat-box" style="border-color:var(--gb)"><div class="stat-box-val" style="color:var(--gold)"><?= $assigned_props ?></div><div class="stat-box-lbl">Assigned Properties</div></div>
  </div>
 
  <!-- ADD BROKER BUTTON -->
  <div style="text-align:center;margin-bottom:28px">
    <a href="add_user.php?role=broker" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4)">+++ ADD NEW BROKER</a>
  </div>
 
  <!-- BROKERS TABLE -->
  <?php
  $brokers_q = mysqli_query($conn,
      "SELECT u.*,
              COUNT(p.id) AS prop_count,
              SUM(pay.amount * u.commission_rate / 100) AS total_earned
       FROM users u
       LEFT JOIN properties p ON p.broker_id = u.id
       LEFT JOIN payments pay ON pay.property_id = p.id AND pay.status IN ('paid','completed')
       WHERE u.role='broker'
       GROUP BY u.id
       ORDER BY u.created_at DESC");
 
  // All unassigned or any property list for dropdown
  $all_props_drop = mysqli_query($conn,"SELECT id, property_name FROM properties ORDER BY property_name ASC");
  $all_props_arr  = [];
  while($pp = mysqli_fetch_assoc($all_props_drop)) $all_props_arr[] = $pp;
  ?>
 
  <table>
    <tr>
      <th>Broker</th>
      <th>Contact</th>
      <th>Status</th>
      <th>Commission Rate</th>
      <th>Properties</th>
      <th>Lifetime Earned (UGX)</th>
      <th>Assign Property</th>
      <th>Actions</th>
    </tr>
    <?php while($b = mysqli_fetch_assoc($brokers_q)):
      $bid      = $b['id'];
      $bstatus  = strtolower($b['status'] ?? 'active');
      $is_susp  = ($bstatus === 'suspended');
      $b_deals  = mysqli_fetch_assoc(mysqli_query($conn,
          "SELECT COUNT(*) AS c FROM payments pay
           JOIN properties pr ON pay.property_id=pr.id
           WHERE pr.broker_id=$bid AND pay.status IN ('paid','completed')"))['c'] ?? 0;
      $tier     = $b_deals < 5 ? 'New' : ($b_deals < 20 ? 'Silver' : 'Gold');
 
      // Broker's assigned properties
      $b_props = mysqli_query($conn,"SELECT p.id, p.property_name FROM properties p WHERE p.broker_id=$bid ORDER BY p.property_name ASC");
    ?>
    <tr style="<?= $is_susp ? 'opacity:.6' : '' ?>">
      <td>
        <div style="font-weight:600"><?= htmlspecialchars($b['fullname']) ?></div>
        <div style="font-size:11px;color:var(--muted);margin-top:2px"><?= $tier ?> Broker · <?= $b_deals ?> deals</div>
      </td>
      <td>
        <div style="font-size:12px"><?= htmlspecialchars($b['email']??'—') ?></div>
        <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($b['phone']??'—') ?></div>
      </td>
      <td>
        <?php if($is_susp): ?>
          <span style="padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);color:#fca5a5;text-transform:uppercase">Suspended</span>
        <?php else: ?>
          <span style="padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac;text-transform:uppercase">Active</span>
        <?php endif; ?>
      </td>
      <td>
        <!-- Inline rate editor -->
        <form method="POST" style="display:flex;align-items:center;gap:6px">
          <input type="hidden" name="update_commission" value="1">
          <input type="hidden" name="broker_id_commission" value="<?= $bid ?>">
          <input type="number" name="commission_rate" value="<?= (float)($b['commission_rate'] ?? 10) ?>" min="0" max="50" step="0.5"
                 style="width:60px;padding:5px 8px;border-radius:5px;border:1px solid var(--border);font-size:12px;background:rgba(255,255,255,.06);color:var(--white);font-family:'Outfit',sans-serif">
          <span style="font-size:12px;color:var(--muted)">%</span>
          <button type="submit" class="action-btn" style="padding:5px 10px;font-size:11px;background:rgba(200,164,60,.2);border:1px solid var(--gb)">✓</button>
        </form>
      </td>
      <td>
        <div style="font-size:13px;font-weight:600;color:var(--gold)"><?= (int)$b['prop_count'] ?> assigned</div>
        <?php if(mysqli_num_rows($b_props) > 0): ?>
        <div style="margin-top:5px;display:flex;flex-direction:column;gap:3px">
          <?php mysqli_data_seek($b_props,0); while($bp = mysqli_fetch_assoc($b_props)): ?>
          <div style="display:flex;align-items:center;gap:6px">
            <span style="font-size:11px;color:var(--muted)"><?= htmlspecialchars(substr($bp['property_name'],0,22)) ?></span>
            <a href="admin_dashboard.php?page=broker_management&unassign_broker=1&prop_id=<?= $bp['id'] ?>"
               onclick="return confirm('Remove broker from this property?')"
               style="font-size:10px;color:#fca5a5;text-decoration:none;border:1px solid rgba(239,68,68,.2);border-radius:4px;padding:1px 6px">✕</a>
          </div>
          <?php endwhile; ?>
        </div>
        <?php endif; ?>
      </td>
      <td style="font-size:13px;color:#86efac;font-weight:600">UGX <?= number_format($b['total_earned'] ?? 0) ?></td>
      <td>
        <?php if(!empty($all_props_arr)): ?>
        <form method="POST">
          <input type="hidden" name="assign_property_broker" value="1">
          <input type="hidden" name="broker_id_assign" value="<?= $bid ?>">
          <select name="property_id_broker" required style="padding:6px 8px;border-radius:5px;border:1px solid var(--border);font-size:11px;background:rgba(255,255,255,.06);color:var(--white);width:100%;margin-bottom:5px;font-family:'Outfit',sans-serif">
            <option value="">— Select property —</option>
            <?php foreach($all_props_arr as $pp): ?>
            <option value="<?= $pp['id'] ?>"><?= htmlspecialchars(substr($pp['property_name'],0,28)) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="action-btn" style="width:100%;font-size:11px;padding:6px;background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac"
                  onclick="return confirm('Assign this property?')">✓ Assign</button>
        </form>
        <?php else: ?>
          <span style="font-size:11px;color:var(--muted)"><a href="add_property.php" style="color:var(--gold)">+ Add property first</a></span>
        <?php endif; ?>
      </td>
      <td style="white-space:nowrap">
        <a href="edit_user.php?id=<?= $bid ?>" class="action-btn" style="font-size:11px">Edit</a>
        <?php if($is_susp): ?>
        <a href="admin_dashboard.php?page=broker_management&broker_action=activate&broker_id=<?= $bid ?>"
           class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac;font-size:11px"
           onclick="return confirm('Reactivate this broker account?')">✓ Activate</a>
        <?php else: ?>
        <a href="admin_dashboard.php?page=broker_management&broker_action=suspend&broker_id=<?= $bid ?>"
           class="action-btn" style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5;font-size:11px"
           onclick="return confirm('Suspend this broker account?')">⊘ Suspend</a>
        <?php endif; ?>
        <a href="delete_user.php?id=<?= $bid ?>" class="action-btn"
           style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;font-size:11px"
           onclick="return confirm('Permanently delete this broker?')">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
 
  <!-- BROKER PERFORMANCE OVERVIEW -->
  <h3 style="font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:700;color:var(--white);margin:28px 0 16px;padding-bottom:10px;border-bottom:1px solid var(--border)">Performance Overview</h3>
  <table>
    <tr>
      <th>Broker</th>
      <th>Properties</th>
      <th>Deals Closed</th>
      <th>Commissions Earned</th>
      <th>Pending Applications</th>
      <th>Tier</th>
    </tr>
    <?php
    $perf_q = mysqli_query($conn,
        "SELECT u.id, u.fullname, u.commission_rate,
                COUNT(DISTINCT p.id) AS prop_count
         FROM users u
         LEFT JOIN properties p ON p.broker_id=u.id
         WHERE u.role='broker'
         GROUP BY u.id ORDER BY u.fullname ASC");
    while($bp = mysqli_fetch_assoc($perf_q)):
      $bpid = $bp['id'];
      $bp_deals = mysqli_fetch_assoc(mysqli_query($conn,
          "SELECT COUNT(*) AS c FROM payments pay JOIN properties pr ON pay.property_id=pr.id
           WHERE pr.broker_id=$bpid AND pay.status IN ('paid','completed')"))['c'] ?? 0;
      $bp_earn  = mysqli_fetch_assoc(mysqli_query($conn,
          "SELECT SUM(pay.amount * {$bp['commission_rate']} / 100) AS total FROM payments pay
           JOIN properties pr ON pay.property_id=pr.id
           WHERE pr.broker_id=$bpid AND pay.status IN ('paid','completed')"))['total'] ?? 0;
      $bp_pend  = 0;
      $ta_chk = mysqli_query($conn,"SHOW TABLES LIKE 'tenant_applications'");
      if ($ta_chk && mysqli_num_rows($ta_chk) > 0)
        $bp_pend = mysqli_fetch_assoc(mysqli_query($conn,
            "SELECT COUNT(*) AS c FROM tenant_applications ta JOIN properties pr ON ta.property_id=pr.id
             WHERE pr.broker_id=$bpid AND ta.status='pending'"))['c'] ?? 0;
      $bp_tier = $bp_deals < 5 ? 'New Broker' : ($bp_deals < 20 ? 'Silver' : 'Gold');
      $tier_col = $bp_deals < 5 ? 'var(--gold)' : ($bp_deals < 20 ? 'silver' : 'var(--gold-l)');
    ?>
    <tr>
      <td style="font-weight:600"><?= htmlspecialchars($bp['fullname']) ?></td>
      <td style="color:var(--gold)"><?= (int)$bp['prop_count'] ?></td>
      <td style="color:#86efac"><?= $bp_deals ?></td>
      <td style="color:#86efac;font-weight:600">UGX <?= number_format($bp_earn) ?></td>
      <td><span style="padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;background:<?=$bp_pend>0?'rgba(200,164,60,.1)':'rgba(22,163,74,.1)'?>;color:<?=$bp_pend>0?'var(--gold)':'#86efac'?>;border:1px solid <?=$bp_pend>0?'var(--gb)':'rgba(22,163,74,.25)'?>"><?= $bp_pend ?> pending</span></td>
      <td style="color:<?= $tier_col ?>;font-weight:600"><?= $bp_tier ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>
<?php endif; ?>
 
<?php if($page === 'broker_documents'): ?>
<section id="broker_documents">
  <h2 style="text-align:center;color:var(--gold)">Broker Verification Requests</h2>

  <?php
  $docs = mysqli_query($conn, "SELECT * FROM verification_requests ORDER BY submitted_at DESC");
  ?>

  <table>
    <tr>
      <th>Type</th>
      <th>Name</th>
      <th>Email</th>
      <th>ID / Business Info</th>
      <th>Documents</th>
      <th>Status</th>
      <th>Submitted At</th>
      <th>Actions</th>
    </tr>

    <?php while($row = mysqli_fetch_assoc($docs)): ?>
    <tr>
      <td><?= htmlspecialchars(ucfirst($row['type'])) ?></td>

      <td>
        <?= htmlspecialchars($row['type'] === 'business' ? $row['business_name'] : $row['full_name']) ?>
      </td>

      <td><?= htmlspecialchars($row['email'] ?? '') ?></td>

      <td>
        <?php if ($row['type'] === 'business'): ?>
          Years in business: <?= htmlspecialchars($row['duration_years'] ?? '') ?>
        <?php else: ?>
          ID Type: <?= htmlspecialchars($row['id_type'] ?? '') ?><br>
          Phone: <?= htmlspecialchars($row['phone'] ?? '') ?>
        <?php endif; ?>
      </td>

      <td>
        <?php if ($row['type'] === 'business'): ?>
          <a href="<?= htmlspecialchars($row['b_reg_path']) ?>" target="_blank">Business Reg</a><br>
          <a href="<?= htmlspecialchars($row['owner_id_path']) ?>" target="_blank">Owner ID</a><br>
          <?php if (!empty($row['additional_doc_path'])): ?>
            <a href="<?= htmlspecialchars($row['additional_doc_path']) ?>" target="_blank">Additional Doc</a>
          <?php endif; ?>
        <?php else: ?>
          <a href="<?= htmlspecialchars($row['id_doc_path']) ?>" target="_blank">View ID Document</a>
        <?php endif; ?>
      </td>

      <td><?= htmlspecialchars($row['status'] ?? 'pending') ?></td>
      <td><?= htmlspecialchars($row['submitted_at']) ?></td>

      <td>
        <?php if (($row['status'] ?? 'pending') === 'pending'): ?>
          <a class="action-btn" href="admin_dashboard.php?page=broker_documents&verify=<?= $row['id'] ?>" onclick="return confirm('Verify this request?')">Verify</a>
          <a class="action-btn" href="admin_dashboard.php?page=broker_documents&reject=<?= $row['id'] ?>" onclick="return confirm('Reject this request?')" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5">Reject</a>
        <?php endif; ?>

        <a class="action-btn" href="delete_record.php?table=verification_requests&id=<?= $row['id'] ?>" onclick="return confirm('Delete this request?')">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>
<?php endif; ?>


<?php
if (isset($_SESSION['admin_success'])):
    echo '<div class="alert success">✅ ' . $_SESSION['admin_success'] . '</div>';
    unset($_SESSION['admin_success']);
endif;
if (isset($_SESSION['admin_error'])):
    echo '<div class="alert error">⚠️ ' . $_SESSION['admin_error'] . '</div>';
    unset($_SESSION['admin_error']);
endif;
?>

<?php if($page === 'dashboard'): ?>
<section id="dashboard">
  <h2 style="text-align:center;margin-bottom:30px">OVERVIEW</h2>
  <div class="overview-cards">
    <div class="circular-card"><h3>Total Properties</h3><p><?= $total_properties ?></p></div>
    <div class="circular-card"><h3>Total Tenants</h3><p><?= $total_tenants ?></p></div>
    <div class="circular-card"><h3>Total Staff</h3><p><?= $total_staff ?></p></div>
    <div class="circular-card"><h3>Total Brokers</h3><p><?= $total_brokers ?></p></div>
    <div class="circular-card"><h3>Property Owners</h3><p><?= $total_owners ?></p></div>
    <div class="circular-card"><h3>Total Guests</h3><p><?= $total_guests ?></p></div>
    <div class="circular-card"><h3>Pending Complaints</h3><p><?= $total_complaints ?></p></div>
    <div class="circular-card"><h3>Unread Alerts</h3><p><?= $total_notifications ?></p></div>
    <div class="circular-card"><h3>Pending Payments</h3><p><?= $pending_payments ?></p></div>
    <div class="circular-card"><h3>Pending Applications</h3><p><?= $pending_applications ?></p></div>
    <div class="circular-card"><h3>Pending Maintenance</h3><p><?= $pending_requests ?></p></div>
    <div class="circular-card"><h3>Revenue Collected</h3><p>UGX <?= number_format($revenue ?? 0) ?></p></div>
  </div>
</section>

<?php elseif($page === 'notice_board'): ?>
<section id="notice_board">
  <h2 style="color:var(--gold)">📢 Notice Board</h2>
  <p style="font-size:14px;color:var(--muted);margin-bottom:24px">Post announcements that all staff members will see on their dashboard.</p>

  <!-- POST NEW NOTICE FORM -->
  <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:24px;max-width:600px;margin-bottom:32px">
    <div style="font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:700;color:var(--white);margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">📌 Post a New Notice</div>
    <form method="POST">
      <input type="hidden" name="action" value="post_notice">
      <label>Notice Title</label>
      <input type="text" name="notice_title" placeholder="e.g. Monthly meeting this Friday" required>
      <label>Message</label>
      <textarea name="notice_message" rows="4" placeholder="Write the full notice here..." required style="resize:vertical"></textarea>
      <button type="submit" class="action-btn" style="background:rgba(200,164,60,.3);border:1px solid var(--gb);width:100%;padding:12px;font-size:13px">📢 Post Notice to All Staff</button>
    </form>
  </div>

  
  <!-- EXISTING NOTICES -->
  <?php
  $board_notices = mysqli_query($conn,"SELECT * FROM notifications WHERE user_id=0 AND tenant_id=0 ORDER BY date DESC");
  $board_count   = mysqli_num_rows($board_notices);
  ?>
  <div style="font-family:'Cormorant Garamond',serif;font-size:20px;font-weight:700;color:var(--white);margin-bottom:16px">
    Posted Notices <span style="font-size:14px;color:var(--muted);font-family:'Outfit',sans-serif;font-weight:400">(<?= $board_count ?> total)</span>
  </div>
  <?php if($board_count === 0): ?>
    <div style="text-align:center;padding:40px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;color:var(--muted)">No notices posted yet. Use the form above to post your first notice.</div>
  <?php else: while($n = mysqli_fetch_assoc($board_notices)): ?>
    <div class="notice-card">
      <div>
        <div class="notice-title"><?= htmlspecialchars($n['title']) ?></div>
        <div class="notice-msg"><?= htmlspecialchars($n['message']) ?></div>
        <div class="notice-date">Posted <?= $n['date'] ? date('d M Y, H:i', strtotime($n['date'])) : '' ?></div>
      </div>
      <a href="admin_dashboard.php?page=notice_board&delete_notice=<?= $n['id'] ?>"
         onclick="return confirm('Delete this notice?')"
         style="flex-shrink:0;padding:6px 12px;background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);border-radius:6px;color:#fca5a5;font-size:12px;font-weight:600;text-decoration:none;white-space:nowrap">
        🗑 Delete
      </a>
    </div>
  <?php endwhile; endif; ?>
</section>

<?php elseif($page === 'users'): ?>
<section id="users">
  <h2 style="text-align:center;color:var(--gold)">USER MANAGEMENT</h2>
  <div style="text-align:center;margin-bottom:20px">
    <a href="add_user.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4)">+++ ADD NEW USER</a>
  </div>
  <div style="background:rgba(14,90,200,.06);border:1px solid rgba(14,90,200,.2);border-radius:10px;padding:12px 18px;margin-bottom:16px;font-size:13px;color:var(--muted)">
    <strong style="color:#5b9cff">ℹ️ Tip:</strong> To link a tenant user to their tenant record, go to
    <a href="admin_dashboard.php?page=tenants" style="color:var(--gold);font-weight:600">🏘 Manage Tenants</a> and use the <strong style="color:#86efac">⚡ Auto-Create &amp; Link</strong> or dropdown there.
  </div>
  <table>
    <tr><th>Full Name</th><th>Role</th><th>Email</th><th>Phone</th><th>Tenant Link</th><th>Joined</th><th>Actions</th></tr>
    <?php $users_q = mysqli_query($conn,"SELECT * FROM users ORDER BY created_at DESC");
    while($u = mysqli_fetch_assoc($users_q)):
        $linked = null;
        if (strtolower($u['role']) === 'tenant') {
            $lq = mysqli_query($conn,"SELECT id,fullname FROM tenants WHERE user_id='".(int)$u['id']."' LIMIT 1");
            $linked = mysqli_fetch_assoc($lq);
        }
    ?>
    <tr>
      <td style="font-weight:600"><?= htmlspecialchars($u['fullname']) ?></td>
      <td>
        <span style="padding:3px 8px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;
          background:<?= strtolower($u['role'])==='admin'?'rgba(239,68,68,.1)':(strtolower($u['role'])==='propertyowner'?'rgba(200,164,60,.1)':'rgba(14,90,200,.1)') ?>;
          color:<?= strtolower($u['role'])==='admin'?'#fca5a5':(strtolower($u['role'])==='propertyowner'?'var(--gold)':'#5b9cff') ?>;
          border:1px solid <?= strtolower($u['role'])==='admin'?'rgba(239,68,68,.3)':(strtolower($u['role'])==='propertyowner'?'var(--gb)':'rgba(14,90,200,.3)') ?>">
          <?= htmlspecialchars($u['role']) ?>
        </span>
      </td>
      <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($u['email']) ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($u['phone']??'—') ?></td>
      <td>
        <?php if(strtolower($u['role'])==='tenant'): ?>
          <?php if($linked): ?>
            <span class="link-badge linked">✓ <?= htmlspecialchars($linked['fullname']) ?></span>
          <?php else: ?>
            <span class="link-badge unlinked">⏳ Not linked</span>
            <a href="admin_dashboard.php?page=tenants" style="font-size:11px;color:var(--gold);text-decoration:none;display:block;margin-top:3px">→ Link in Manage Tenants</a>
          <?php endif; ?>
        <?php else: ?>
          <span style="color:#444;font-size:12px">—</span>
        <?php endif; ?>
      </td>
      <td style="font-size:11px;color:var(--muted)"><?= $u['created_at']?date('d M Y',strtotime($u['created_at'])):'—' ?></td>
      <td style="white-space:nowrap">
        <a href="edit_user.php?id=<?= $u['id'] ?>" class="action-btn" style="font-size:11px">Edit</a>
        <a href="delete_user.php?id=<?= $u['id'] ?>" class="action-btn" onclick="return confirm('Delete this user?')" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;font-size:11px">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>

<?php elseif($page === 'tenants'): ?>
<section id="tenants">
  <h2 style="text-align:center;color:var(--gold)">MANAGE TENANTS</h2>
  <?php if($unlinked_count > 0): ?>
  <div class="unlinked-banner">⚠️ <div><strong><?= $unlinked_count ?> tenant<?= $unlinked_count>1?'s':'' ?> not linked to a user account.</strong> Use the Link Account dropdown below to connect them.</div></div>
  <?php endif; ?>
  <div style="text-align:center;margin-bottom:20px"><a href="add_tenant.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4)">+++ ADD NEW TENANT</a></div>
  <?php
  // All tenant-role users not yet linked to any tenant record
  $linked_user_ids_q = mysqli_query($conn,"SELECT user_id FROM tenants WHERE user_id IS NOT NULL AND user_id > 0");
  $linked_ids = [];
  while($lid = mysqli_fetch_assoc($linked_user_ids_q)) $linked_ids[] = (int)$lid['user_id'];
  $excl = !empty($linked_ids) ? implode(',', $linked_ids) : '0';
  $tenant_users = mysqli_query($conn,"SELECT id,fullname,email FROM users WHERE role='tenant' AND id NOT IN ($excl) ORDER BY fullname ASC");
  $available_users = [];
  while($tu = mysqli_fetch_assoc($tenant_users)) $available_users[] = $tu;
  $tenants_q = mysqli_query($conn,"SELECT t.*,p.property_name,u.fullname AS linked_username,u.email AS linked_email FROM tenants t LEFT JOIN properties p ON t.property_id=p.id LEFT JOIN users u ON t.user_id=u.id ORDER BY t.created_at DESC");
  ?>
  <!-- HOW LINKING WORKS guide -->
  <div style="background:rgba(14,90,200,.06);border:1px solid rgba(14,90,200,.2);border-radius:10px;padding:14px 20px;margin-bottom:20px;font-size:13px;color:var(--muted);line-height:1.8">
    <strong style="color:#5b9cff">ℹ️ How Tenant Linking Works:</strong><br>
    Every tenant in this list needs a <strong style="color:var(--white)">login account</strong> to access their dashboard.<br>
    <strong style="color:var(--white)">Option A:</strong> Go to <a href="add_user.php" style="color:var(--gold)">Add User</a>, create an account with role <em>tenant</em>, then come back here and link them from the dropdown.<br>
    <strong style="color:var(--white)">Option B:</strong> Click <strong style="color:#86efac">⚡ Auto-Create &amp; Link</strong> below to instantly create a login account for that tenant using their email and a default password, then link them automatically.
  </div>

  <table>
    <tr><th>Tenant</th><th>Contact</th><th>Property</th><th>Account Status</th><th>Link / Create Account</th><th>Actions</th></tr>
    <?php while($t = mysqli_fetch_assoc($tenants_q)): ?>
    <tr>
      <td>
        <div style="font-weight:600;color:var(--white)"><?= htmlspecialchars($t['fullname']) ?></div>
        <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($t['national_id']??'') ?></div>
      </td>
      <td>
        <div style="font-size:12px"><?= htmlspecialchars($t['phone']??'N/A') ?></div>
        <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($t['email']??'N/A') ?></div>
      </td>
      <td><?= htmlspecialchars($t['property_name']??'Unassigned') ?></td>
      <td>
        <?php if(!empty($t['user_id']) && $t['user_id']>0): ?>
          <span class="link-badge linked">✓ Linked</span>
          <div style="font-size:11px;color:var(--muted);margin-top:3px">
            <?= htmlspecialchars($t['linked_username']??'') ?><br>
            <em style="font-size:10px;color:rgba(255,255,255,.3)"><?= htmlspecialchars($t['linked_email']??'') ?></em>
          </div>
        <?php else: ?>
          <span class="link-badge unlinked">⏳ No Login Account</span>
        <?php endif; ?>
      </td>
      <td>
        <?php if(!empty($t['user_id']) && $t['user_id']>0): ?>
          <form method="POST" action="admin_dashboard.php?page=tenants" onsubmit="return confirm('Remove this account link? The tenant will lose dashboard access.')">
            <input type="hidden" name="action" value="unlink_account">
            <input type="hidden" name="tenant_id" value="<?= $t['id'] ?>">
            <button type="submit" class="action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;font-size:11px;padding:5px 10px">✕ Unlink</button>
          </form>
        <?php else: ?>
          <?php if(!empty($available_users)): ?>
          <form method="POST" action="admin_dashboard.php?page=tenants" class="link-form" style="margin-bottom:8px">
            <input type="hidden" name="action" value="link_account">
            <input type="hidden" name="tenant_id" value="<?= $t['id'] ?>">
            <select name="link_user_id" required style="width:100%;margin-bottom:5px">
              <option value="">— Link existing account —</option>
              <?php foreach($available_users as $au): ?>
              <option value="<?= $au['id'] ?>"><?= htmlspecialchars($au['fullname']) ?> · <?= htmlspecialchars($au['email']) ?></option>
              <?php endforeach; ?>
            </select>
            <button type="submit" class="action-btn" style="background:rgba(14,90,200,.3);border:1px solid rgba(14,90,200,.4);font-size:11px;padding:5px 10px;width:100%">✓ Link Account</button>
          </form>
          <?php endif; ?>
          <?php if(!empty($t['email'])): ?>
          <a href="admin_dashboard.php?page=tenants&auto_link=<?= $t['id'] ?>"
             onclick="return confirm('Auto-create a login account for <?= htmlspecialchars(addslashes($t['fullname'])) ?> using <?= htmlspecialchars(addslashes($t['email']??'')) ?> and link them? Default password will be: housing123')"
             class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac;font-size:11px;padding:5px 10px;display:block;text-align:center;text-decoration:none">⚡ Auto-Create &amp; Link</a>
          <?php else: ?>
          <a href="add_user.php" class="action-btn" style="background:rgba(200,164,60,.15);border:1px solid var(--gb);color:var(--gold);font-size:11px;padding:5px 10px;display:block;text-align:center;text-decoration:none">+ Add email first</a>
          <?php endif; ?>
        <?php endif; ?>
      </td>
      <td style="white-space:nowrap">
        <a href="edit_records.php?type=tenant&id=<?= $t['id'] ?>" class="action-btn" style="font-size:11px">Edit</a>
        <a href="delete_record.php?table=tenants&id=<?= $t['id'] ?>" class="action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5;font-size:11px" onclick="return confirm('Delete this tenant?')">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>

<?php elseif($page === 'properties'): ?>
<section id="properties">
  <h2>Manage Properties</h2>
  <div style="text-align:center;margin-bottom:20px">
    <a href="add_property.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4)">+++ ADD NEW PROPERTY</a>
  </div>
  <table>
    <tr><th>Property Name</th><th>Type</th><th>Address</th><th>Units</th><th>Rent (UGX)</th><th>Owner</th><th>Created At</th><th>Actions</th></tr>
    <?php $properties = mysqli_query($conn,"SELECT p.*,u.fullname FROM properties p LEFT JOIN users u ON p.owner_id=u.id ORDER BY p.created_at DESC");
    while($prop = mysqli_fetch_assoc($properties)): ?>
    <tr>
      <td><?= htmlspecialchars($prop['property_name']) ?></td>
      <td><?= htmlspecialchars($prop['property_type']??'N/A') ?></td>
      <td><?= htmlspecialchars($prop['address']??'N/A') ?></td>
      <td><?= (int)$prop['units'] ?></td>
      <td><?= number_format($prop['rent_amount']??0) ?></td>
      <td><?= htmlspecialchars($prop['fullname']??'Unassigned') ?></td>
      <td><?= htmlspecialchars($prop['created_at']) ?></td>
      <td>
        <a href="edit_records.php?type=property&id=<?= $prop['id'] ?>" class="action-btn">Edit</a>
        <a href="delete_record.php?table=properties&id=<?= $prop['id'] ?>" class="action-btn" onclick="return confirm('Delete?')" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>

<?php elseif($page === 'staff_roles'): ?>
<section id="staff_roles">
  <h2>Staff Roles & Payroll</h2>
  <div style="text-align:center;margin-bottom:20px"><a href="add_staff.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4)">+++ ADD NEW STAFF</a></div>
  <!-- PAYROLL INFO BOX -->
  <div style="background:rgba(200,164,60,.06);border:1px solid var(--gb);border-radius:10px;padding:16px 20px;margin-bottom:20px;font-size:13px;color:var(--muted);line-height:1.8">
    <strong style="color:var(--gold)">💰 How Payroll Works:</strong><br>
    Click <strong style="color:var(--white)">📧 Send Payslip</strong> next to any staff member to email them their monthly payslip automatically.
    The email includes their salary, pay period, and monthly task performance summary.
    A notification is also saved to their staff portal.
  </div>

  <table>
    <tr><th>Full Name</th><th>Role</th><th>Salary (UGX)</th><th>Email</th><th>Phone</th><th>Created At</th><th>Actions</th></tr>
    <?php $staff = mysqli_query($conn,"SELECT * FROM users WHERE role='staff' ORDER BY created_at DESC");
    while($s = mysqli_fetch_assoc($staff)): ?>
    <tr>
      <td style="font-weight:600"><?= htmlspecialchars($s['fullname']??'N/A') ?></td>
      <td><?= htmlspecialchars($s['role']??'Staff') ?></td>
      <td style="color:#86efac;font-weight:600">UGX <?= number_format($s['salary']??0) ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($s['email']??'N/A') ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($s['phone']??'N/A') ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= $s['created_at']?date('d M Y',strtotime($s['created_at'])):'N/A' ?></td>
      <td style="white-space:nowrap">
        <a href="send_payslip.php?id=<?= $s['id'] ?>" class="action-btn"
           style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac"
           onclick="return confirm('Send payslip email to <?= htmlspecialchars(addslashes($s['fullname'])) ?> at <?= htmlspecialchars(addslashes($s['email']??'')) ?>?')">
           📧 Send Payslip
        </a>
        <a href="edit_records.php?type=staff&id=<?= $s['id'] ?>" class="action-btn">Edit</a>
        <a href="delete_record.php?table=users&id=<?= $s['id'] ?>" class="action-btn" onclick="return confirm('Delete?')" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>

<?php elseif($page === 'staff_tasks'): ?>
<section id="staff_tasks">
  <h2 style="text-align:center">Staff Tasks & Schedule</h2>
  <div style="text-align:center;margin-bottom:20px"><a href="add_task.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4)">+ Assign New Task</a></div>
  <table>
    <tr><th>Task Title</th><th>Staff Assigned</th><th>Due Date</th><th>Priority</th><th>Status</th><th>Assigned By</th><th>Actions</th></tr>
    <?php $tasks = mysqli_query($conn,"SELECT t.*,u.fullname AS staff_name FROM tasks t LEFT JOIN users u ON t.assigned_to=u.id ORDER BY t.due_date ASC");
    while($task = mysqli_fetch_assoc($tasks)): ?>
    <tr>
      <td><?= htmlspecialchars($task['title']) ?></td>
      <td><?= htmlspecialchars($task['staff_name']??'N/A') ?></td>
      <td><?= htmlspecialchars($task['due_date']??'-') ?></td>
      <td><?= htmlspecialchars($task['priority']) ?></td>
      <td><?= htmlspecialchars($task['status']) ?></td>
      <td><?= htmlspecialchars($task['assigned_by']) ?></td>
      <td>
        <a href="edit_records.php?type=task&id=<?= $task['id'] ?>" class="action-btn">Edit</a>
        <a href="delete_record.php?table=tasks&id=<?= $task['id'] ?>" class="action-btn" onclick="return confirm('Delete?')" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5">Delete</a>
        <?php if($task['status']!='Completed'): ?><a href="mark_task_complete.php?id=<?= $task['id'] ?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3)">Complete</a><?php endif; ?>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>

<?php elseif($page === 'employee_performance'): ?>
<section id="employee_performance">
  <h2>Employee Performance</h2>
  <table>
    <tr><th>Staff Member</th><th>Email</th><th>Tasks Completed</th><th>Tasks Pending</th><th>Overdue</th><th>Rating</th></tr>
    <?php $staff = mysqli_query($conn,"SELECT * FROM users WHERE role='staff'");
    while($s = mysqli_fetch_assoc($staff)):
      $sid  = $s['id'];
      $done = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM tasks WHERE assigned_to='$sid' AND status='Completed'"))['c'];
      $pend = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM tasks WHERE assigned_to='$sid' AND status!='Completed'"))['c'];
      $over = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM tasks WHERE assigned_to='$sid' AND status!='Completed' AND due_date < CURDATE()"))['c'];
      $rating = $over>0?'⚠️ Needs Improvement':($done>=$pend?'✅ Good':'🟡 Fair');
    ?>
    <tr>
      <td style="font-weight:600"><?= htmlspecialchars($s['fullname']) ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($s['email']??'') ?></td>
      <td style="color:#86efac"><?= $done ?></td>
      <td style="color:var(--gold)"><?= $pend ?></td>
      <td style="color:<?= $over>0?'#fca5a5':'var(--muted)' ?>"><?= $over ?></td>
      <td><?= $rating ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>

<?php elseif($page === 'jobs'): ?>
<section id="jobs">
  <h2>Employment Applications</h2>
  <?php
  $total_apps    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM job_applications"))['c'];
  $pending_apps  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM job_applications WHERE status='pending'"))['c'];
  $approved_apps = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM job_applications WHERE status='approved'"))['c'];
  $rejected_apps = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM job_applications WHERE status='rejected'"))['c'];
  ?>
  <div class="stat-row">
    <div class="stat-box"><div class="stat-box-val"><?= $total_apps ?></div><div class="stat-box-lbl">Total Applications</div></div>
    <div class="stat-box" style="border-color:var(--gb)"><div class="stat-box-val"><?= $pending_apps ?></div><div class="stat-box-lbl">Pending Review</div></div>
    <div class="stat-box" style="border-color:rgba(22,163,74,.3)"><div class="stat-box-val" style="color:#86efac"><?= $approved_apps ?></div><div class="stat-box-lbl">Approved</div></div>
    <div class="stat-box" style="border-color:rgba(239,68,68,.3)"><div class="stat-box-val" style="color:#fca5a5"><?= $rejected_apps ?></div><div class="stat-box-lbl">Rejected</div></div>
  </div>
  <?php $filter = $_GET['filter'] ?? 'all'; ?>
  <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php foreach(['all'=>'All','pending'=>'Pending','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$label):
      $active = $filter===$k;
      $kc = $k!=='all' ? mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM job_applications WHERE status='".mysqli_real_escape_string($conn,$k)."'"))['c'] : ''; ?>
    <a href="admin_dashboard.php?page=jobs&filter=<?=$k?>" style="padding:7px 16px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;background:<?=$active?'rgba(200,164,60,.2)':'rgba(255,255,255,.04)'?>;border:1px solid <?=$active?'var(--gb)':'var(--border)'?>;color:<?=$active?'var(--gold)':'var(--muted)'?>">
      <?=$label?><?php if($k!=='all') echo " <span style='background:rgba(255,255,255,.1);border-radius:10px;padding:1px 6px;font-size:10px;margin-left:4px'>$kc</span>"; ?>
    </a>
    <?php endforeach; ?>
  </div>
  <?php
  $where = $filter!=='all' ? "WHERE status='".mysqli_real_escape_string($conn,$filter)."'" : '';
  $apps  = mysqli_query($conn,"SELECT * FROM job_applications $where ORDER BY created_at DESC");
  $app_count = $apps ? mysqli_num_rows($apps) : 0;
  ?>
  <?php if($app_count==0): ?>
  <div style="text-align:center;padding:40px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;color:var(--muted)">No <?=$filter!=='all'?$filter:''?> applications found.</div>
  <?php else: ?>
  <table>
    <tr><th>#</th><th>Applicant</th><th>Position</th><th>Phone</th><th>Applied</th><th>Resume</th><th>Status</th><th>Actions</th></tr>
    <?php $i=1; while($app = mysqli_fetch_assoc($apps)):
      $st  = strtolower($app['status']??'pending');
      $sc  = ($st==='approved'?'#86efac':($st==='rejected'?'#fca5a5':'var(--gold)'));
      $sbg = ($st==='approved'?'rgba(22,163,74,.1)':($st==='rejected'?'rgba(239,68,68,.1)':'rgba(200,164,60,.1)'));
      $sbd = ($st==='approved'?'rgba(22,163,74,.3)':($st==='rejected'?'rgba(239,68,68,.3)':'var(--gb)'));
    ?>
    <tr>
      <td style="color:var(--muted)"><?= $i++ ?></td>
      <td><div style="font-weight:600"><?= htmlspecialchars($app['full_name']) ?></div><div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($app['email']??'—') ?></div></td>
      <td><?= htmlspecialchars($app['position']??'N/A') ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($app['phone']??'—') ?></td>
      <td style="font-size:12px;color:var(--muted);white-space:nowrap"><?= $app['created_at']?date('d M Y',strtotime($app['created_at'])):'N/A' ?></td>
      <td><?php if(!empty($app['resume'])): ?><a href="uploads/<?= htmlspecialchars($app['resume']) ?>" target="_blank" style="font-size:12px;color:var(--gold);text-decoration:none">📄 View</a><?php else: ?><span style="font-size:11px;color:var(--muted)">None</span><?php endif; ?></td>
      <td><span style="padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;background:<?=$sbg?>;color:<?=$sc?>;border:1px solid <?=$sbd?>"><?= ucfirst($st) ?></span></td>
      <td style="white-space:nowrap">
        <a href="view_application.php?id=<?= $app['id'] ?>" class="action-btn" style="background:rgba(14,90,200,.3);border-color:rgba(14,90,200,.4)">👁 View</a>
        <?php if($st==='pending'): ?>
        <a href="approve_application.php?id=<?= $app['id'] ?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac" onclick="return confirm('Approve and send congratulations email?')">✓ Approve</a>
        <a href="reject_application.php?id=<?= $app['id'] ?>" class="action-btn" style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5" onclick="return confirm('Reject and send decline email?')">✕ Reject</a>
        <?php elseif($st==='approved'): ?><span style="font-size:11px;color:#86efac">✓ Hired</span>
        <?php elseif($st==='rejected'): ?><span style="font-size:11px;color:#fca5a5">✕ Declined</span><?php endif; ?>
        <a href="delete_record.php?table=job_applications&id=<?= $app['id'] ?>" class="action-btn" style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.25);color:#fca5a5" onclick="return confirm('Delete application?')">🗑</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
  <?php endif; ?>
</section>

<?php elseif($page === 'inspections'): ?>
<section id="inspections">
  <h2 style="text-align:center">Property Inspections</h2>
  <div style="text-align:center;margin-bottom:20px"><a href="add_inspection.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4)">+ Schedule New Inspection</a></div>
  <table>
    <tr><th>Property</th><th>Tenant</th><th>Inspector</th><th>Date</th><th>Situation</th><th>Status</th><th>Notified</th><th>Actions</th></tr>
    <?php $inspections = mysqli_query($conn,"SELECT i.*,p.property_name,t.fullname AS tenant_name FROM inspections i LEFT JOIN properties p ON i.property_id=p.id LEFT JOIN tenants t ON i.tenant_id=t.id ORDER BY i.inspection_date DESC");
    while($i = mysqli_fetch_assoc($inspections)): ?>
    <tr>
      <td><?= htmlspecialchars($i['property_name']??'N/A') ?></td>
      <td><?= htmlspecialchars($i['tenant_name']??'None') ?></td>
      <td><?= htmlspecialchars($i['inspector_name']) ?></td>
      <td><?= htmlspecialchars($i['inspection_date']) ?></td>
      <td><?= htmlspecialchars($i['condition']??$i['situation']??'—') ?></td>
      <td><?= htmlspecialchars($i['status']) ?></td>
      <td><?= ($i['notified']==1)?"Yes":"No" ?></td>
      <td>
        <a href="edit_records.php?type=inspection&id=<?= $i['id'] ?>" class="action-btn">Edit</a>
        <a href="delete_record.php?table=inspections&id=<?= $i['id'] ?>" class="action-btn" onclick="return confirm('Delete?')" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5">Delete</a>
        <?php if($i['status']!="Completed"): ?><a href="mark_inspection_complete.php?id=<?= $i['id'] ?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3)">Complete</a><?php endif; ?>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>

<?php elseif($page === 'maintenance'): ?>
<section id="maintenance">
  <h2>Maintenance Requests</h2>
  <table>
    <tr><th>Property</th><th>Issue</th><th>Priority</th><th>Assigned Staff</th><th>Status</th><th>Date</th><th>Actions</th></tr>
    <?php $requests = mysqli_query($conn,"SELECT m.*,u.fullname as staff_name,p.property_name FROM maintenance_requests m LEFT JOIN users u ON m.assigned_staff=u.id LEFT JOIN properties p ON m.property_id=p.id ORDER BY m.created_at DESC");
    while($r = mysqli_fetch_assoc($requests)): ?>
    <tr>
      <td><?= htmlspecialchars($r['property_name']??'N/A') ?></td>
      <td><?= htmlspecialchars($r['issue']) ?></td>
      <td><?= htmlspecialchars($r['priority']??'medium') ?></td>
      <td><?= htmlspecialchars($r['staff_name']??'Unassigned') ?></td>
      <td><?= htmlspecialchars($r['status']) ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= $r['created_at']?date('d M Y',strtotime($r['created_at'])):'-' ?></td>
      <td>
        <a href="assign_staff.php?id=<?= $r['id'] ?>" class="action-btn">Assign</a>
        <a href="mark_complete.php?id=<?= $r['id'] ?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3)">Complete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>

<?php elseif($page === 'tenant_payments'): ?>
<section id="tenant_payments">
  <h2 style="text-align:center;color:var(--gold)">TENANT PAYMENTS</h2>
  
  <div style="text-align:center;margin-bottom:20px">
    <a href="add_payment.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4)">+++ RECORD NEW PAYMENT</a>
  </div>

  <table>
    <thead>
      <tr>
        <th>Tenant</th>
        <th>Property</th>
        <th>Amount (UGX)</th>
        <th>SMS ID (Ref)</th> <th>Date</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php 
      // Query remains mostly the same, ensuring we get the status and transaction_ref
      $payments = mysqli_query($conn,"SELECT pay.*, t.fullname as tenant_name, p.property_name 
                                      FROM payments pay 
                                      LEFT JOIN tenants t ON pay.tenant_id=t.id 
                                      LEFT JOIN properties p ON pay.property_id=p.id 
                                      ORDER BY pay.date DESC");
      
      while($pay = mysqli_fetch_assoc($payments)): ?>
      <tr>
        <td><?= htmlspecialchars($pay['tenant_name']??'N/A') ?></td>
        <td><?= htmlspecialchars($pay['property_name']??'N/A') ?></td>
        <td><strong><?= number_format($pay['amount']) ?></strong></td>
        
        <td style="color:var(--gold); font-family:monospace; font-weight:bold;">
            <?= htmlspecialchars($pay['transaction_ref'] ?? '—') ?>
        </td>
        
        <td><?= date('d M, Y', strtotime($pay['date'])) ?></td>
        
        <td>
            <span class="status-badge status-<?= strtolower($pay['status'] ?? 'pending') ?>">
                <?= htmlspecialchars($pay['status'] ?? 'Pending') ?>
            </span>
        </td>
        
        <td>
          <?php if($pay['status'] === 'pending_verification' || $pay['status'] === 'pending'): ?>
            <a href="admin_dashboard.php?pay_action=approve&pay_id=<?= $pay['id'] ?>&page=tenant_payments" 
               class="action-btn" style="background:rgba(34,197,94,.2); border:1px solid #22c55e; color:#4ade80;"
               onclick="return confirm('Verify UGX <?= number_format($pay['amount']) ?>?')">Verify</a>
          <?php endif; ?>

          <a href="edit_records.php?type=payment&id=<?= $pay['id'] ?>" class="action-btn">Edit</a>
          
          <a href="delete_record.php?table=payments&id=<?= $pay['id'] ?>" class="action-btn" 
             style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5" 
             onclick="return confirm('Delete this record?')">Delete</a>
        </td>
      </tr>
      <?php endwhile; ?>
    </tbody>
  </table>
</section>

<?php elseif($page === 'payments'): ?>
<section id="payments">
  <h2>Payments / Rent Tracking</h2>
  <table class="admin-table">
    <tr>
      <th>Tenant</th>
      <th>Property</th>
      <th>Amount (UGX)</th>
      <th>SMS ID (Ref)</th> <th>Date</th>
      <th>Status</th>
      <th>Action</th> </tr>
    <?php 
    $payments = mysqli_query($conn,"SELECT pay.*, t.fullname as tenant_name, p.property_name 
                                    FROM payments pay 
                                    LEFT JOIN tenants t ON pay.tenant_id=t.id 
                                    LEFT JOIN properties p ON pay.property_id=p.id 
                                    ORDER BY pay.date DESC");
    while($pay = mysqli_fetch_assoc($payments)): ?>
    <tr>
      <td><?= htmlspecialchars($pay['tenant_name']??'N/A') ?></td>
      <td><?= htmlspecialchars($pay['property_name']??'N/A') ?></td>
      <td><?= number_format($pay['amount']) ?></td>
      
      <td style="font-weight:bold; color:#2563eb;"><?= htmlspecialchars($pay['transaction_ref'] ?? 'N/A') ?></td>
      
      <td><?= htmlspecialchars($pay['date']) ?></td>
      <td>
        <span class="status-badge status-<?= $pay['status'] ?>">
            <?= htmlspecialchars($pay['status']??'pending') ?>
        </span>
      </td>
      <td>
        <?php if($pay['status'] === 'pending_verification'): ?>
            <a href="admin_dashboard.php?pay_action=approve&pay_id=<?= $pay['id'] ?>&page=payments" 
               onclick="return confirm('Verify this payment?')" style="color:green;">Confirm</a> | 
            <a href="admin_dashboard.php?pay_action=reject&pay_id=<?= $pay['id'] ?>&page=payments" 
               style="color:red;">Reject</a>
        <?php elseif($pay['status'] === 'paid'): ?>
            <span style="color:gray;">Verified ✅</span>
        <?php endif; ?>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>

<?php elseif($page === 'complaints'): ?>
<section id="complaints">
  <h2 style="text-align:center;color:var(--gold)">COMPLAINTS & FEEDBACK</h2>
  <table>
    <tr><th>Tenant</th><th>Category</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr>
    <?php $complaints = mysqli_query($conn,"SELECT c.*,t.fullname as tenant_name FROM complaints c LEFT JOIN tenants t ON c.tenant_id=t.id ORDER BY c.created_at DESC");
    while($c = mysqli_fetch_assoc($complaints)): ?>
    <tr>
      <td><?= htmlspecialchars($c['tenant_name']??'N/A') ?></td>
      <td><?= htmlspecialchars($c['category']??'N/A') ?></td>
      <td><?= htmlspecialchars(substr($c['message']??'',0,60)) ?>...</td>
      <td><?= htmlspecialchars($c['status']??'pending') ?></td>
      <td><?= htmlspecialchars($c['created_at']??'N/A') ?></td>
      <td>
        <a href="view_complaint.php?id=<?= $c['id'] ?>" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4)">View</a>
        <a href="resolve_complaint.php?id=<?= $c['id'] ?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3)" onclick="return confirm('Mark resolved?')">Resolve</a>
        <a href="delete_record.php?table=complaints&id=<?= $c['id'] ?>" class="action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5" onclick="return confirm('Delete?')">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>

<?php elseif($page === 'guests'): ?>
<section id="guests">
  <h2 style="text-align:center;color:var(--gold)">GUEST / VISITOR APPROVALS</h2>
  <div style="text-align:center;margin-bottom:20px"><a href="add_guest.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4)">+++ ADD NEW GUEST</a></div>
  <?php $guests = mysqli_query($conn,"SELECT g.*,t.fullname AS tenant_name,p.property_name FROM guests g LEFT JOIN tenants t ON g.tenant_id=t.id LEFT JOIN properties p ON g.property_id=p.id ORDER BY g.created_at DESC"); ?>
  <table>
    <tr><th>Guest Name</th><th>Email</th><th>Phone</th><th>Tenant</th><th>Property</th><th>Check-in</th><th>Check-out</th><th>Status</th><th>Actions</th></tr>
    <?php while($g = mysqli_fetch_assoc($guests)): ?>
    <tr>
      <td><?= htmlspecialchars($g['fullname']) ?></td>
      <td><?= htmlspecialchars($g['email']??'N/A') ?></td>
      <td><?= htmlspecialchars($g['phone']??'N/A') ?></td>
      <td><?= htmlspecialchars($g['tenant_name']??'N/A') ?></td>
      <td><?= htmlspecialchars($g['property_name']??'N/A') ?></td>
      <td><?= htmlspecialchars($g['check_in']??'-') ?></td>
      <td><?= htmlspecialchars($g['check_out']??'-') ?></td>
      <td><?= htmlspecialchars($g['status']??'Pending') ?></td>
      <td>
        <a href="approve_guest.php?id=<?= $g['id'] ?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3)">Approve</a>
        <a href="reject_guest.php?id=<?= $g['id'] ?>" class="action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5">Reject</a>
        <a href="delete_record.php?table=guests&id=<?= $g['id'] ?>" class="action-btn" style="background:rgba(239,68,68,.25);border:1px solid rgba(239,68,68,.4);color:#fca5a5" onclick="return confirm('Delete?')">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>

<?php elseif($page === 'brokers'): ?>
<section id="brokers">
  <h2 style="text-align:center;color:var(--gold)">MANAGE BROKERS / AGENTS</h2>
  <div style="text-align:center;margin-bottom:20px"><a href="add_user.php?role=broker" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4)">+++ ADD NEW BROKER</a></div>
  <table>
    <tr><th>Full Name</th><th>Email</th><th>Phone</th><th>Properties</th><th>Commission (UGX)</th><th>Actions</th></tr>
    <?php $brokers = mysqli_query($conn,"SELECT * FROM users WHERE role='broker' ORDER BY created_at DESC");
    while($b = mysqli_fetch_assoc($brokers)):
      $bid = $b['id'];
      $pc  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) as c FROM properties WHERE broker_id='$bid'"))['c'];
      $ct  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount*commission_percentage/100) AS total FROM payments p JOIN properties pr ON p.property_id=pr.id WHERE pr.broker_id='$bid'"))['total']??0;
    ?>
    <tr>
      <td><?= htmlspecialchars($b['fullname']) ?></td>
      <td><?= htmlspecialchars($b['email']??'N/A') ?></td>
      <td><?= htmlspecialchars($b['phone']??'N/A') ?></td>
      <td><?= $pc ?></td>
      <td><?= number_format($ct) ?></td>
      <td>
        <a href="edit_user.php?id=<?= $bid ?>" class="action-btn">Edit</a>
        <a href="delete_user.php?id=<?= $bid ?>" class="action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5" onclick="return confirm('Delete broker?')">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>

<?php elseif($page === 'propertyowners'): ?>
<section id="propertyowners">
  <h2 style="text-align:center;color:var(--gold)">PROPERTY OWNERS</h2>

  <?php
  // ── Stats ──
  $total_owners_count    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM users WHERE role='propertyowner'"))['c'] ?? 0;
  $verified_owners_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(DISTINCT owner_id) AS c FROM properties WHERE owner_id IS NOT NULL AND owner_id > 0"))['c'] ?? 0;
  $pending_owners_count  = $total_owners_count - $verified_owners_count;
  ?>

  <!-- STAT ROW -->
  <div class="stat-row" style="margin-bottom:24px">
    <div class="stat-box"><div class="stat-box-val"><?= $total_owners_count ?></div><div class="stat-box-lbl">Total Owners</div></div>
    <div class="stat-box" style="border-color:rgba(22,163,74,.3)"><div class="stat-box-val" style="color:#86efac"><?= $verified_owners_count ?></div><div class="stat-box-lbl">Verified (Have Properties)</div></div>
    <div class="stat-box" style="border-color:var(--gb)"><div class="stat-box-val" style="color:var(--gold)"><?= $pending_owners_count ?></div><div class="stat-box-lbl">Pending Verification</div></div>
    <div class="stat-box"><div class="stat-box-val"><?= $total_properties ?></div><div class="stat-box-lbl">Total Properties</div></div>
  </div>

  <!-- INFO BOX -->
  <div style="background:rgba(200,164,60,.06);border:1px solid var(--gb);border-radius:10px;padding:14px 20px;margin-bottom:20px;font-size:13px;color:var(--muted);line-height:1.8">
    <strong style="color:var(--gold)">ℹ️ How it works:</strong><br>
    <strong style="color:var(--white)">Step 1:</strong> Add a property using the <strong style="color:var(--gold)">🏠 ADD NEW PROPERTY</strong> button below.<br>
    <strong style="color:var(--white)">Step 2:</strong> Add a property owner account using <strong style="color:var(--gold)">ADD NEW PROPERTY OWNER</strong>.<br>
    <strong style="color:var(--white)">Step 3:</strong> Find the owner in the table, pick a property from their dropdown, click <strong style="color:#86efac">✓ Assign Property</strong>.<br>
    <strong style="color:#86efac">Done:</strong> Their dashboard activates immediately.
  </div>

  <div style="text-align:center;margin-bottom:20px;display:flex;gap:12px;justify-content:center;flex-wrap:wrap">
    <a href="add_propertyowner.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4)">+++ ADD NEW PROPERTY OWNER</a>
    <a href="add_property.php" class="action-btn" style="background:rgba(200,164,60,.2);border:1px solid var(--gb);color:var(--gold)">🏠 ADD NEW PROPERTY</a>
  </div>

  <table>
    <tr>
      <th>Full Name</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Verification</th>
      <th>Properties</th>
      <th>Revenue (UGX)</th>
      <th>Assign Property</th>
      <th>Actions</th>
    </tr>
    <?php
    $owners = mysqli_query($conn,"SELECT u.*,COUNT(p.id) AS properties_count FROM users u LEFT JOIN properties p ON u.id=p.owner_id WHERE u.role='propertyowner' GROUP BY u.id ORDER BY u.created_at DESC");
    // Fetch unassigned properties for dropdown
    $unassigned_props = mysqli_query($conn,"SELECT p.id, p.property_name, u.fullname AS current_owner FROM properties p LEFT JOIN users u ON p.owner_id=u.id ORDER BY p.property_name ASC");
    $unassigned_list = [];
    while($up = mysqli_fetch_assoc($unassigned_props)) $unassigned_list[] = $up;
    while($owner = mysqli_fetch_assoc($owners)):
      $oid = $owner['id'];
      $pc  = (int)$owner['properties_count'];
      $rev = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(pay.amount) AS total FROM payments pay JOIN properties pr ON pay.property_id=pr.id WHERE pr.owner_id=$oid AND pay.status='paid'"))['total'] ?? 0;
      $is_verified = $pc > 0;
    ?>
    <tr>
      <td style="font-weight:600"><?= htmlspecialchars($owner['fullname']) ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($owner['email']??'N/A') ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($owner['phone']??'N/A') ?></td>
      <td>
        <?php if($is_verified): ?>
          <span style="padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;background:rgba(22,163,74,.1);color:#86efac;border:1px solid rgba(22,163,74,.3)">✓ Verified</span>
        <?php else: ?>
          <span style="padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;background:rgba(200,164,60,.1);color:var(--gold);border:1px solid var(--gb)">⏳ Pending</span>
        <?php endif; ?>
      </td>
      <td style="text-align:center;color:<?= $pc>0?'#86efac':'var(--muted)' ?>;font-weight:600"><?= $pc ?></td>
      <td style="font-size:12px;color:#86efac">UGX <?= number_format($rev) ?></td>
      <td>
        <?php if(!empty($unassigned_list)): ?>
        <form method="POST" action="admin_dashboard.php?page=propertyowners" onsubmit="var s=this.querySelector('select[name=property_id]');if(!s.value){alert('Please select a property first.');return false;}return confirm('Assign to <?= addslashes(htmlspecialchars($owner['fullname'])) ?>?')">
          <input type="hidden" name="owner_id" value="<?= $oid ?>">
          <select name="property_id" required style="padding:6px 10px;border-radius:6px;border:1px solid var(--border);font-size:12px;background:rgba(255,255,255,.06);color:var(--white);font-family:'Outfit',sans-serif;width:100%;margin-bottom:6px">
            <option value="">— Select property —</option>
            <?php foreach($unassigned_list as $up): ?>
            <option value="<?= $up['id'] ?>"><?= htmlspecialchars($up['property_name']) ?></option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="action-btn" style="background:rgba(22,163,74,.25);border:1px solid rgba(22,163,74,.4);color:#86efac;font-size:12px;padding:8px 16px;width:100%">✓ Assign Property</button>
        </form>
        <?php else: ?>
          <div style="font-size:12px;color:var(--muted)">No properties yet.<br>
          <a href="add_property.php" style="color:var(--gold)">+ Add a property first</a></div>
        <?php endif; ?>
      </td>
      <td style="white-space:nowrap">
        <a href="edit_records.php?type=propertyowner&id=<?= $oid ?>" class="action-btn">Edit</a>
        <a href="delete_record.php?table=users&id=<?= $oid ?>" class="action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5" onclick="return confirm('Delete this property owner?')">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>

<?php elseif($page === 'tenant_documents'): ?>
<section id="tenant_documents">
  <h2 style="text-align:center;color:var(--gold)">TENANT DOCUMENTS</h2>
  <div style="text-align:center;margin-bottom:20px"><a href="add_document.php" class="action-btn" style="background:rgba(14,90,200,.4);border:1px solid rgba(14,90,200,.4)">+++ ADD NEW DOCUMENT</a></div>
  <table>
    <tr><th>Tenant</th><th>Document Name</th><th>File</th><th>Uploaded At</th><th>Actions</th></tr>
    <?php $docs = mysqli_query($conn,"SELECT d.*,t.fullname AS tenant_name FROM tenant_documents d LEFT JOIN tenants t ON d.tenant_id=t.id ORDER BY d.uploaded_at DESC");
    while($doc = mysqli_fetch_assoc($docs)): ?>
    <tr>
      <td><?= htmlspecialchars($doc['tenant_name']??'N/A') ?></td>
      <td><?= htmlspecialchars($doc['document_name']??'Unnamed') ?></td>
      <td><?php if(!empty($doc['file_path'])): ?><a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank" style="color:var(--gold)">View</a><?php else: ?>N/A<?php endif; ?></td>
      <td><?= htmlspecialchars($doc['uploaded_at']??'-') ?></td>
      <td>
        <a href="edit_records.php?type=document&id=<?= $doc['id'] ?>" class="action-btn">Edit</a>
        <a href="delete_record.php?table=tenant_documents&id=<?= $doc['id'] ?>" class="action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5" onclick="return confirm('Delete?')">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>

<?php elseif($page === 'notifications'): ?>
<section id="notifications">
  <h2 style="text-align:center;color:var(--gold)">NOTIFICATIONS</h2>

  <?php
  $unread_notif_count = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM notifications WHERE (status='unread' OR is_read=0)"))['c'] ?? 0;
  ?>

  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px">
    <div style="font-size:13px;color:var(--muted)">
      <?php if($unread_notif_count > 0): ?>
        <span style="color:var(--gold);font-weight:600"><?= $unread_notif_count ?> unread</span> notification<?= $unread_notif_count>1?'s':'' ?>
      <?php else: ?>
        <span style="color:#86efac">All notifications read ✓</span>
      <?php endif; ?>
    </div>
    <?php if($unread_notif_count > 0): ?>
    <a href="mark_notification_read.php?all=1" class="action-btn"
       style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac"
       onclick="return confirm('Mark all notifications as read?')">
      ✓ Mark All as Read
    </a>
    <?php endif; ?>
  </div>

  <?php $notifs = mysqli_query($conn,"SELECT n.*,u.fullname AS sender_name,t.fullname AS tenant_name FROM notifications n LEFT JOIN users u ON n.user_id=u.id LEFT JOIN tenants t ON n.tenant_id=t.id ORDER BY n.date DESC"); ?>
  <table>
    <tr><th>Recipient</th><th>Tenant</th><th>Title</th><th>Message</th><th>Status</th><th>Date</th><th>Actions</th></tr>
    <?php while($n = mysqli_fetch_assoc($notifs)):
      $is_unread = ($n['status']==='unread' || $n['is_read']==0);
    ?>
    <tr style="<?= $is_unread ? 'background:rgba(200,164,60,.04)' : '' ?>">
      <td><?= htmlspecialchars($n['sender_name']??'System') ?></td>
      <td><?= htmlspecialchars($n['tenant_name']??'-') ?></td>
      <td style="font-weight:<?= $is_unread?'600':'400' ?>"><?= htmlspecialchars($n['title']??'-') ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars(substr($n['message']??'',0,60)) ?>...</td>
      <td>
        <?php if($is_unread): ?>
          <span style="padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;background:rgba(200,164,60,.1);border:1px solid var(--gb);color:var(--gold)">Unread</span>
        <?php else: ?>
          <span style="padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac">Read</span>
        <?php endif; ?>
      </td>
      <td style="font-size:12px;color:var(--muted)"><?= $n['date'] ? date('d M Y, H:i', strtotime($n['date'])) : '-' ?></td>
      <td style="white-space:nowrap">
        <?php if($is_unread): ?>
        <a href="mark_notification_read.php?id=<?= $n['id'] ?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac">✓ Mark Read</a>
        <?php endif; ?>
        <a href="delete_record.php?table=notifications&id=<?= $n['id'] ?>" class="action-btn" style="background:rgba(239,68,68,.2);border:1px solid rgba(239,68,68,.3);color:#fca5a5" onclick="return confirm('Delete?')">Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>
<?php elseif($page === 'broker_submissions'): ?>
<section id="broker_submissions">
<h2 style="text-align:center;color:var(--gold)">🏠 BROKER PROPERTY SUBMISSIONS</h2>
<p style="text-align:center;font-size:13px;color:var(--muted);margin-bottom:28px">
  Properties submitted by brokers awaiting your review. Approved submissions are automatically added to the live listings.
</p>
 
<?php
$bps_total     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM broker_property_submissions"))['c'] ?? 0;
$bps_pending   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM broker_property_submissions WHERE status='pending'"))['c'] ?? 0;
$bps_reviewing = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM broker_property_submissions WHERE status='reviewing'"))['c'] ?? 0;
$bps_approved  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM broker_property_submissions WHERE status='approved'"))['c'] ?? 0;
$bps_rejected  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM broker_property_submissions WHERE status='rejected'"))['c'] ?? 0;
 
$view_bps = null;
if (isset($_GET['view_bps'])) {
    $vbid = (int)$_GET['view_bps'];
    $view_bps = mysqli_fetch_assoc(mysqli_query($conn,
        "SELECT s.*, u.fullname AS broker_name, u.email AS broker_email, u.phone AS broker_phone,
                u.commission_rate AS broker_rate
         FROM broker_property_submissions s
         LEFT JOIN users u ON s.broker_id = u.id
         WHERE s.id = $vbid LIMIT 1"));
}
 
$bps_filter = $_GET['filter'] ?? 'all';
$bps_where  = $bps_filter !== 'all' ? "WHERE s.status='" . mysqli_real_escape_string($conn,$bps_filter) . "'" : '';
$bps_q = mysqli_query($conn,
    "SELECT s.*, u.fullname AS broker_name
     FROM broker_property_submissions s
     LEFT JOIN users u ON s.broker_id = u.id
     $bps_where
     ORDER BY s.submission_date DESC");
     ?>
 
<div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:28px">
  <div class="stat-box"><div class="stat-box-val"><?= $bps_total ?></div><div class="stat-box-lbl">Total</div></div>
  <div class="stat-box" style="border-color:var(--gb)"><div class="stat-box-val"><?= $bps_pending ?></div><div class="stat-box-lbl">Pending</div></div>
  <div class="stat-box" style="border-color:rgba(59,130,246,.3)"><div class="stat-box-val" style="color:#5b9cff"><?= $bps_reviewing ?></div><div class="stat-box-lbl">Reviewing</div></div>
  <div class="stat-box" style="border-color:rgba(22,163,74,.3)"><div class="stat-box-val" style="color:#86efac"><?= $bps_approved ?></div><div class="stat-box-lbl">Approved & Live</div></div>
  <div class="stat-box" style="border-color:rgba(239,68,68,.3)"><div class="stat-box-val" style="color:#fca5a5"><?= $bps_rejected ?></div><div class="stat-box-lbl">Rejected</div></div>
</div>
 
<?php if ($view_bps): ?>
<div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:14px;padding:28px;margin-bottom:24px">
  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:22px;padding-bottom:18px;border-bottom:1px solid var(--border)">
    <div>
      <div style="font-family:'Cormorant Garamond',serif;font-size:26px;font-weight:700;color:var(--white);margin-bottom:4px"><?= htmlspecialchars($view_bps['property_name']) ?></div>
      <div style="font-size:12px;color:var(--muted)">
        Submission #<?= $view_bps['id'] ?> · Submitted <?= $view_bps['created_at'] ? date('d M Y, H:i', strtotime($view_bps['created_at'])) : '—' ?>
        <?php if($view_bps['reviewed_at']): ?> · Reviewed <?= date('d M Y', strtotime($view_bps['reviewed_at'])) ?> by <?= htmlspecialchars($view_bps['reviewed_by'] ?? '—') ?><?php endif; ?>
      </div>
    </div>
    <div style="display:flex;align-items:center;gap:10px">
      <?php
      $st=$view_bps['status']??'pending';
      $sc=$st==='approved'?'#86efac':($st==='rejected'?'#fca5a5':($st==='reviewing'?'#5b9cff':'var(--gold)'));
      $sbg=$st==='approved'?'rgba(22,163,74,.12)':($st==='rejected'?'rgba(239,68,68,.12)':($st==='reviewing'?'rgba(59,130,246,.12)':'rgba(200,164,60,.12)'));
      $sbd=$st==='approved'?'rgba(22,163,74,.35)':($st==='rejected'?'rgba(239,68,68,.35)':($st==='reviewing'?'rgba(59,130,246,.35)':'var(--gb)'));
      ?>
      <span style="padding:5px 14px;border-radius:20px;font-size:11px;font-weight:700;text-transform:uppercase;background:<?=$sbg?>;color:<?=$sc?>;border:1px solid <?=$sbd?>"><?= ucfirst($st) ?></span>
      <a href="admin_dashboard.php?page=broker_submissions" class="action-btn" style="font-size:12px">← Back</a>
    </div>
  </div>
 
  <div style="display:flex;align-items:center;gap:16px;padding:14px 18px;background:rgba(14,90,200,.06);border:1px solid rgba(14,90,200,.2);border-radius:10px;margin-bottom:20px">
    <div style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg,rgba(200,164,60,.4),rgba(14,90,200,.3));display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:17px;font-weight:700;color:var(--white);flex-shrink:0"><?= strtoupper(substr($view_bps['broker_name']??'B',0,1)) ?></div>
    <div>
      <div style="font-size:13px;font-weight:600;color:var(--white)"><?= htmlspecialchars($view_bps['broker_name'] ?? 'Unknown') ?></div>
      <div style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($view_bps['broker_email']??'—') ?> · <?= htmlspecialchars($view_bps['broker_phone']??'—') ?></div>
      <div style="font-size:11px;color:#5b9cff;margin-top:2px">Commission Rate: <?= (float)($view_bps['broker_rate'] ?? $view_bps['commission_rate']) ?>%</div>
    </div>
    <a href="admin_dashboard.php?page=broker_management" style="margin-left:auto;font-size:11px;color:var(--gold);text-decoration:none;border:1px solid var(--gb);padding:5px 10px;border-radius:6px">View Broker →</a>
  </div>
 
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-bottom:18px">
    <?php foreach([
      'Property Type'=>$view_bps['property_type']??'—','Purpose'=>ucfirst($view_bps['purpose']??'rent'),
      'Rent (UGX)'=>number_format($view_bps['rent_amount']??0),'Units'=>$view_bps['units']??1,
      'Bedrooms'=>$view_bps['bedrooms']??0,'Size (sqft)'=>$view_bps['size_sqft']?number_format($view_bps['size_sqft']):'—',
      'Commission Rate'=>($view_bps['commission_rate']??10).'%','Commission %'=>($view_bps['commission_percentage']??10).'%',
      'Latitude'=>$view_bps['latitude']??'—','Longitude'=>$view_bps['longitude']??'—',
      'Submitted'=>$view_bps['created_at']?date('d M Y',strtotime($view_bps['created_at'])):'—','Status'=>ucfirst($view_bps['status']??'pending'),
    ] as $lbl=>$val): ?>
    <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:12px">
      <div style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:5px"><?= $lbl ?></div>
      <div style="font-size:13px;color:var(--white)"><?= htmlspecialchars((string)$val) ?></div>
    </div>
    <?php endforeach; ?>
  </div>
 
  <?php if(!empty($view_bps['address'])): ?>
  <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:14px;margin-bottom:12px">
    <div style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:5px">📍 Address</div>
    <div style="font-size:13px;color:var(--white)"><?= htmlspecialchars($view_bps['address']) ?></div>
    <?php if(!empty($view_bps['latitude'])&&!empty($view_bps['longitude'])): ?>
    <a href="https://maps.google.com/?q=<?= urlencode($view_bps['latitude'].','.$view_bps['longitude']) ?>" target="_blank" style="display:inline-block;margin-top:8px;font-size:11px;color:#5b9cff;text-decoration:none;border:1px solid rgba(59,130,246,.3);padding:4px 10px;border-radius:5px">🗺️ View on Google Maps →</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
 
  <?php foreach(['Amenities'=>'amenities','Description'=>'description'] as $lbl=>$key): if(!empty($view_bps[$key])): ?>
  <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:14px;margin-bottom:12px">
    <div style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:5px"><?= $lbl ?></div>
    <div style="font-size:13px;color:rgba(255,255,255,.8);line-height:1.65"><?= htmlspecialchars($view_bps[$key]) ?></div>
  </div>
  <?php endif; endforeach; ?>
 
  <?php if(!empty($view_bps['property_image'])): ?>
  <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:8px;padding:14px;margin-bottom:18px">
    <div style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:10px">Property Image</div>
    <img src="<?= htmlspecialchars($view_bps['property_image']) ?>" style="max-width:100%;max-height:300px;border-radius:8px;object-fit:cover;display:block" onerror="this.style.display='none'">
    <a href="<?= htmlspecialchars($view_bps['property_image']) ?>" target="_blank" style="display:inline-block;margin-top:8px;font-size:11px;color:var(--gold);text-decoration:none;border:1px solid var(--gb);padding:4px 10px;border-radius:5px">🖼️ Open Full Image →</a>
  </div>
  <?php endif; ?>
 
  <div style="display:flex;gap:10px;flex-wrap:wrap;padding:18px 0;border-top:1px solid var(--border);border-bottom:1px solid var(--border);margin-bottom:18px">
    <?php if($st!=='reviewing'): ?><a href="admin_dashboard.php?page=broker_submissions&bps_action=reviewing&bps_id=<?=$view_bps['id']?>" class="action-btn" style="background:rgba(59,130,246,.2);border:1px solid rgba(59,130,246,.3);color:#5b9cff">🔍 Under Review</a><?php endif; ?>
    <?php if($st!=='approved'): ?><a href="admin_dashboard.php?page=broker_submissions&bps_action=approved&bps_id=<?=$view_bps['id']?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac" onclick="return confirm('Approve & publish? Broker will be notified.')">✓ Approve & Publish</a><?php endif; ?>
    <?php if($st!=='rejected'): ?><a href="admin_dashboard.php?page=broker_submissions&bps_action=rejected&bps_id=<?=$view_bps['id']?>" class="action-btn" style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5" onclick="return confirm('Reject? Broker will be notified.')">✕ Reject</a><?php endif; ?>
    <a href="admin_dashboard.php?page=broker_submissions&delete_bps=<?=$view_bps['id']?>" class="action-btn" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5" onclick="return confirm('Delete permanently?')">🗑 Delete</a>
    <?php if($st==='approved'): ?><a href="admin_dashboard.php?page=properties" class="action-btn" style="background:rgba(200,164,60,.2);border:1px solid var(--gb);margin-left:auto">View in Properties →</a><?php endif; ?>
  </div>
 
  <form method="POST">
    <input type="hidden" name="bps_id" value="<?= $view_bps['id'] ?>">
    <label style="display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px">Admin Notes (Internal Only)</label>
    <textarea name="bps_admin_notes" rows="3" style="width:100%;padding:10px 13px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:'Outfit',sans-serif;font-size:13px;outline:none;resize:vertical;margin-bottom:10px" placeholder="Add review notes..."><?= htmlspecialchars($view_bps['admin_notes']??'') ?></textarea>
    <button type="submit" name="save_bps_notes" class="action-btn" style="background:rgba(200,164,60,.2);border:1px solid var(--gb)">💾 Save Notes</button>
  </form>
</div>
 
<?php else: ?>
<div style="display:flex;gap:8px;margin-bottom:22px;flex-wrap:wrap">
  <?php foreach(['all'=>'All','pending'=>'Pending','reviewing'=>'Reviewing','approved'=>'Approved & Live','rejected'=>'Rejected'] as $k=>$lbl):
    $act=$bps_filter===$k;
    $cnt=$k==='all'?$bps_total:($k==='approved'?$bps_approved:($k==='rejected'?$bps_rejected:($k==='reviewing'?$bps_reviewing:$bps_pending)));
  ?>
  <a href="admin_dashboard.php?page=broker_submissions&filter=<?=$k?>" style="padding:7px 16px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;background:<?=$act?'rgba(200,164,60,.2)':'rgba(255,255,255,.04)'?>;border:1px solid <?=$act?'var(--gb)':'var(--border)'?>;color:<?=$act?'var(--gold)':'var(--muted)'?>"><?=$lbl?> <span style="background:rgba(255,255,255,.12);border-radius:10px;padding:1px 7px;font-size:10px;margin-left:4px"><?=$cnt?></span></a>
  <?php endforeach; ?>
</div>
 
<div style="background:rgba(200,164,60,.06);border:1px solid var(--gb);border-radius:10px;padding:13px 18px;margin-bottom:20px;font-size:13px;color:var(--muted);display:flex;align-items:center;gap:10px">
  <span>ℹ️</span>
  <div>Approved submissions are <strong style="color:#86efac">automatically published</strong> to the live property catalogue and the broker is notified. Rejected submissions notify the broker to review their listing.</div>
</div>
 
<?php if(!$bps_q||mysqli_num_rows($bps_q)===0): ?>
<div style="text-align:center;padding:60px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px">
  <div style="font-size:48px;margin-bottom:16px">🏠</div>
  <div style="font-size:17px;font-weight:600;color:var(--white);margin-bottom:8px">No broker submissions yet</div>
  <div style="font-size:13px;color:var(--muted)">When brokers submit properties through their dashboard, they will appear here.</div>
</div>
<?php else: ?>
<table>
  <tr><th>#</th><th>Property</th><th>Broker</th><th>Type / Purpose</th><th>Rent (UGX)</th><th>Beds / Units</th><th>Submitted</th><th>Status</th><th>Actions</th></tr>
  <?php $i=1; while($bps=mysqli_fetch_assoc($bps_q)):
    $st=strtolower($bps['status']??'pending');
    $sc=$st==='approved'?'#86efac':($st==='rejected'?'#fca5a5':($st==='reviewing'?'#5b9cff':'var(--gold))'));
    $sbg=$st==='approved'?'rgba(22,163,74,.1)':($st==='rejected'?'rgba(239,68,68,.1)':($st==='reviewing'?'rgba(59,130,246,.1)':'rgba(200,164,60,.1)'));
    $sbd=$st==='approved'?'rgba(22,163,74,.3)':($st==='rejected'?'rgba(239,68,68,.3)':($st==='reviewing'?'rgba(59,130,246,.3)':'var(--gb)'));
  ?>
  <tr>
    <td style="color:var(--muted)"><?=$i++?></td>
    <td>
      <div style="font-weight:600;color:var(--white)"><?=htmlspecialchars($bps['property_name'])?></div>
      <div style="font-size:11px;color:var(--muted);margin-top:2px">📍 <?=htmlspecialchars(substr($bps['address']??'—',0,35))?><?=strlen($bps['address']??'')>35?'...':''?></div>
      <?php if(!empty($bps['property_image'])): ?><div style="font-size:10px;color:#5b9cff;margin-top:2px">📷 Has image</div><?php endif; ?>
    </td>
    <td style="font-size:12px"><div style="color:var(--white)"><?=htmlspecialchars($bps['broker_name']??'—')?></div><div style="font-size:10px;color:var(--muted)">Broker</div></td>
    <td style="font-size:12px;color:var(--muted)"><?=htmlspecialchars($bps['property_type']??'—')?><br><span style="font-size:10px;padding:1px 6px;background:rgba(255,255,255,.06);border-radius:4px"><?=ucfirst($bps['purpose']??'rent')?></span></td>
    <td style="color:var(--gold);font-weight:600"><?=number_format($bps['rent_amount']??0)?></td>
    <td style="text-align:center;color:var(--muted)"><?=(int)($bps['bedrooms']??0)?> bd / <?=(int)($bps['units']??1)?> u</td>
    <td style="font-size:11px;color:var(--muted)"><?=$bps['created_at']?date('d M Y',strtotime($bps['created_at'])):'—'?></td>
    <td><span style="padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;background:<?=$sbg?>;color:<?=$sc?>;border:1px solid <?=$sbd?>"><?=ucfirst($st)?></span></td>
    <td style="white-space:nowrap">
      <a href="admin_dashboard.php?page=broker_submissions&view_bps=<?=$bps['id']?>" class="action-btn" style="background:rgba(14,90,200,.3);border-color:rgba(14,90,200,.4)">👁 View</a>
      <?php if($st==='pending'): ?>
      <a href="admin_dashboard.php?page=broker_submissions&bps_action=reviewing&bps_id=<?=$bps['id']?>" class="action-btn" style="background:rgba(59,130,246,.2);border:1px solid rgba(59,130,246,.3);color:#5b9cff;font-size:11px">Review</a>
      <?php elseif($st==='reviewing'): ?>
      <a href="admin_dashboard.php?page=broker_submissions&bps_action=approved&bps_id=<?=$bps['id']?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac;font-size:11px" onclick="return confirm('Approve & publish?')">✓</a>
      <a href="admin_dashboard.php?page=broker_submissions&bps_action=rejected&bps_id=<?=$bps['id']?>" class="action-btn" style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5;font-size:11px" onclick="return confirm('Reject?')">✕</a>
      <?php endif; ?>
      <a href="admin_dashboard.php?page=broker_submissions&delete_bps=<?=$bps['id']?>" class="action-btn" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5;font-size:11px" onclick="return confirm('Delete?')">🗑</a>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
<?php endif; ?>
<?php endif; ?>
</section>
 
<?php elseif($page === 'revenue_reports'): ?>
<section id="revenue_reports">
  <h2 style="text-align:center;color:var(--gold)">REVENUE REPORTS</h2>
  <?php
  $total_rev   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) AS total FROM payments"))['total']??0;
  $pending_rev = mysqli_fetch_assoc(mysqli_query($conn,"SELECT SUM(amount) AS total FROM payments WHERE status='pending'"))['total']??0;
  $collected   = $total_rev - $pending_rev;
  ?>
  <div class="stat-row" style="margin-bottom:32px">
    <div class="stat-box"><div class="stat-box-val" style="font-size:22px">UGX <?= number_format($total_rev) ?></div><div class="stat-box-lbl">Total Revenue</div></div>
    <div class="stat-box" style="border-color:rgba(22,163,74,.3)"><div class="stat-box-val" style="font-size:22px;color:#86efac">UGX <?= number_format($collected) ?></div><div class="stat-box-lbl">Collected</div></div>
    <div class="stat-box" style="border-color:rgba(239,68,68,.3)"><div class="stat-box-val" style="font-size:22px;color:#fca5a5">UGX <?= number_format($pending_rev) ?></div><div class="stat-box-lbl">Pending</div></div>
    <div class="stat-box"><div class="stat-box-val"><?= $total_tenants ?></div><div class="stat-box-lbl">Total Tenants</div></div>
  </div>
  <h3 style="margin-bottom:16px;color:var(--white)">Revenue by Property</h3>
  <table>
    <tr><th>Property Name</th><th>Total Paid (UGX)</th><th>Pending (UGX)</th></tr>
    <?php $props = mysqli_query($conn,"SELECT pr.property_name,SUM(CASE WHEN p.status='paid' THEN p.amount ELSE 0 END) AS paid,SUM(CASE WHEN p.status='pending' THEN p.amount ELSE 0 END) AS pending FROM properties pr LEFT JOIN payments p ON pr.id=p.property_id GROUP BY pr.id ORDER BY pr.property_name ASC");
    while($prop = mysqli_fetch_assoc($props)): ?>
    <tr>
      <td><?= htmlspecialchars($prop['property_name']) ?></td>
      <td style="color:#86efac"><?= number_format($prop['paid']??0) ?></td>
      <td style="color:#fca5a5"><?= number_format($prop['pending']??0) ?></td>
    </tr>
    <?php endwhile; ?>
  </table>
</section>

<?php elseif($page === 'settings'): ?>
<section>
  <h2 style="text-align:center">SYSTEM SETTINGS</h2>
  <?php
  $settingsQuery = mysqli_query($conn,"SELECT * FROM system_settings LIMIT 1");
  $settings = ($settingsQuery && mysqli_num_rows($settingsQuery)>0) ? mysqli_fetch_assoc($settingsQuery) : ["site_name"=>"HousingHub","email"=>"","notification_email"=>"","backup_frequency"=>"weekly"];
  ?>
  <form method="POST" action="save_settings.php" style="max-width:500px;margin:auto;border:1px solid var(--border);padding:28px;border-radius:12px;background:rgba(255,255,255,.03)">
    <label>Site Name</label><input type="text" name="site_name" value="<?= htmlspecialchars($settings['site_name']) ?>" required>
    <label>System Email</label><input type="email" name="email" value="<?= htmlspecialchars($settings['email']??'') ?>">
    <label>Notification Email</label><input type="email" name="notification_email" value="<?= htmlspecialchars($settings['notification_email']??'') ?>">
    <label>Backup Frequency</label>
    <select name="backup_frequency">
      <option value="daily" <?= ($settings['backup_frequency']=="daily")?"selected":"" ?>>Daily</option>
      <option value="weekly" <?= ($settings['backup_frequency']=="weekly")?"selected":"" ?>>Weekly</option>
      <option value="monthly" <?= ($settings['backup_frequency']=="monthly")?"selected":"" ?>>Monthly</option>
    </select>
    <button type="submit" name="save_settings" class="action-btn" style="width:100%;padding:12px;font-size:13px">SAVE SETTINGS</button>
  </form>
</section>

<?php elseif($page === 'backups'): ?>
<section id="backups">
  <h2 style="text-align:center;color:var(--gold)">BACKUP / EXPORT DATA</h2>
  <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:14px;max-width:700px;margin:0 auto">
    <a href="export_sql.php" class="action-btn" style="background:rgba(14,90,200,.3);border:1px solid rgba(14,90,200,.4);display:block;text-align:center;padding:16px">💾 Full Database (SQL)</a>
    <a href="export_csv.php?table=users" class="action-btn" style="display:block;text-align:center;padding:16px">👤 Users CSV</a>
    <a href="export_csv.php?table=tenants" class="action-btn" style="display:block;text-align:center;padding:16px">🏘 Tenants CSV</a>
    <a href="export_csv.php?table=properties" class="action-btn" style="display:block;text-align:center;padding:16px">🏠 Properties CSV</a>
    <a href="export_csv.php?table=payments" class="action-btn" style="display:block;text-align:center;padding:16px">💳 Payments CSV</a>
    <a href="export_csv.php?table=complaints" class="action-btn" style="display:block;text-align:center;padding:16px">📩 Complaints CSV</a>
  </div>
  <div style="text-align:center;margin-top:24px;font-size:13px;color:var(--muted)">SQL exports can restore the full database. CSV exports can be opened in Excel or Google Sheets.</div>
</section>

<?php elseif($page === 'tenant_applications'): ?>
<section id="tenant_applications">
  <h2 style="text-align:center;color:var(--gold)">TENANT APPLICATIONS</h2>

  <?php
  // ── Auto-create table if missing ──
  mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `tenant_applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `fullname` VARCHAR(200) NOT NULL,
    `email` VARCHAR(200) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `national_id` VARCHAR(100) DEFAULT NULL,
    `occupation` VARCHAR(200) DEFAULT NULL,
    `employer` VARCHAR(200) DEFAULT NULL,
    `monthly_income` VARCHAR(100) DEFAULT NULL,
    `property_id` INT DEFAULT NULL,
    `desired_move_in` DATE DEFAULT NULL,
    `lease_duration` VARCHAR(100) DEFAULT NULL,
    `num_occupants` INT DEFAULT 1,
    `previous_address` TEXT DEFAULT NULL,
    `reason_for_moving` TEXT DEFAULT NULL,
    `reference_name` VARCHAR(200) DEFAULT NULL,
    `reference_phone` VARCHAR(100) DEFAULT NULL,
    `additional_notes` TEXT DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'pending',
    `admin_notes` TEXT DEFAULT NULL,
    `reviewed_by` VARCHAR(200) DEFAULT NULL,
    `reviewed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT NOW()
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  // ── Stats ──
  $ta_total     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM tenant_applications"))['c'] ?? 0;
  $ta_pending   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM tenant_applications WHERE status='pending'"))['c'] ?? 0;
  $ta_reviewing = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM tenant_applications WHERE status='reviewing'"))['c'] ?? 0;
  $ta_approved  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM tenant_applications WHERE status='approved'"))['c'] ?? 0;
  $ta_rejected  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM tenant_applications WHERE status='rejected'"))['c'] ?? 0;

  // ── View single application ──
  $view_app = null;
  if (isset($_GET['view_app'])) {
      $vid = (int)$_GET['view_app'];
      $view_app = mysqli_fetch_assoc(mysqli_query($conn,
          "SELECT ta.*, p.property_name, p.address, p.rent_amount
           FROM tenant_applications ta
           LEFT JOIN properties p ON ta.property_id = p.id
           WHERE ta.id = $vid LIMIT 1"));
  }
  ?>

  <!-- STATS -->
  <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:24px">
    <div class="stat-box"><div class="stat-box-val"><?= $ta_total ?></div><div class="stat-box-lbl">Total Applications</div></div>
    <div class="stat-box" style="border-color:var(--gb)"><div class="stat-box-val"><?= $ta_pending ?></div><div class="stat-box-lbl">Pending Review</div></div>
    <div class="stat-box" style="border-color:rgba(59,130,246,.3)"><div class="stat-box-val" style="color:#5b9cff"><?= $ta_reviewing ?></div><div class="stat-box-lbl">Under Review</div></div>
    <div class="stat-box" style="border-color:rgba(22,163,74,.3)"><div class="stat-box-val" style="color:#86efac"><?= $ta_approved ?></div><div class="stat-box-lbl">Approved</div></div>
    <div class="stat-box" style="border-color:rgba(239,68,68,.3)"><div class="stat-box-val" style="color:#fca5a5"><?= $ta_rejected ?></div><div class="stat-box-lbl">Rejected</div></div>
  </div>

  <?php if($view_app): ?>
  <!-- ── SINGLE APPLICATION VIEW ── -->
  <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:28px;margin-bottom:24px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--border)">
      <div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;color:var(--white);margin-bottom:4px"><?= htmlspecialchars($view_app['fullname']) ?></div>
        <div style="font-size:12px;color:var(--muted)">Application #<?= $view_app['id'] ?> · Submitted <?= $view_app['created_at'] ? date('d M Y, H:i', strtotime($view_app['created_at'])) : '—' ?></div>
      </div>
      <a href="admin_dashboard.php?page=tenant_applications" class="action-btn" style="font-size:12px">← Back to List</a>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:20px">
      <?php
      $fields = [
        'Email' => $view_app['email'],
        'Phone' => $view_app['phone'],
        'National ID' => $view_app['national_id'],
        'Occupation' => $view_app['occupation'],
        'Employer' => $view_app['employer'],
        'Monthly Income' => $view_app['monthly_income'] ? 'UGX ' . $view_app['monthly_income'] : '—',
        'Property Applied' => $view_app['property_name'] ?? '—',
        'Property Address' => $view_app['address'] ?? '—',
        'Rent Amount' => $view_app['rent_amount'] ? 'UGX ' . number_format($view_app['rent_amount']) . '/mo' : '—',
        'Desired Move-in' => $view_app['desired_move_in'] ? date('d M Y', strtotime($view_app['desired_move_in'])) : '—',
        'Lease Duration' => $view_app['lease_duration'],
        'No. of Occupants' => $view_app['num_occupants'],
        'Reference Name' => $view_app['reference_name'],
        'Reference Phone' => $view_app['reference_phone'],
        'Status' => ucfirst($view_app['status'] ?? 'pending'),
      ];
      foreach($fields as $label => $val): ?>
      <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:7px;padding:12px">
        <div style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:5px"><?= $label ?></div>
        <div style="font-size:13px;color:var(--white)"><?= htmlspecialchars($val ?? '—') ?></div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php foreach(['Previous Address'=>'previous_address','Reason for Moving'=>'reason_for_moving','Additional Notes'=>'additional_notes'] as $lbl=>$key): if(!empty($view_app[$key])): ?>
    <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:7px;padding:14px;margin-bottom:12px">
      <div style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px"><?= $lbl ?></div>
      <div style="font-size:13px;color:rgba(255,255,255,.8);line-height:1.6"><?= htmlspecialchars($view_app[$key]) ?></div>
    </div>
    <?php endif; endforeach; ?>

    <!-- ACTION BUTTONS -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:20px;padding-top:18px;border-top:1px solid var(--border)">
      <?php $st = strtolower($view_app['status']??'pending'); ?>
      <?php if($st!=='reviewing'): ?>
      <a href="admin_dashboard.php?page=tenant_applications&view_app=<?= $view_app['id'] ?>&app_action=reviewing&app_id=<?= $view_app['id'] ?>" class="action-btn" style="background:rgba(59,130,246,.2);border:1px solid rgba(59,130,246,.3);color:#5b9cff">🔍 Mark Under Review</a>
      <?php endif; ?>
      <?php if($st!=='approved'): ?>
      <a href="admin_dashboard.php?page=tenant_applications&app_action=approved&app_id=<?= $view_app['id'] ?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac" onclick="return confirm('Approve this application?')">✓ Approve</a>
      <?php endif; ?>
      <?php if($st!=='rejected'): ?>
      <a href="admin_dashboard.php?page=tenant_applications&app_action=rejected&app_id=<?= $view_app['id'] ?>" class="action-btn" style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5" onclick="return confirm('Reject this application?')">✕ Reject</a>
      <?php endif; ?>
      <a href="delete_record.php?table=tenant_applications&id=<?= $view_app['id'] ?>&redirect=admin_dashboard.php?page=tenant_applications" class="action-btn" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5" onclick="return confirm('Delete this application permanently?')">🗑 Delete</a>
    </div>

    <!-- ADMIN NOTES -->
    <div style="margin-top:20px">
      <form method="POST">
        <input type="hidden" name="app_id" value="<?= $view_app['id'] ?>">
        <label style="display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px">Admin Notes (Internal Only)</label>
        <textarea name="admin_notes" rows="3" style="width:100%;padding:10px 13px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:'Outfit',sans-serif;font-size:13px;outline:none;resize:vertical;margin-bottom:10px"><?= htmlspecialchars($view_app['admin_notes'] ?? '') ?></textarea>
        <button type="submit" name="save_app_notes" class="action-btn" style="background:rgba(200,164,60,.2);border:1px solid var(--gb)">💾 Save Notes</button>
      </form>
    </div>
  </div>

  <?php else: ?>
  <!-- ── APPLICATION LIST ── -->
  <?php
  $filter_status = $_GET['filter'] ?? 'all';
  $where = $filter_status !== 'all' ? "WHERE ta.status='" . mysqli_real_escape_string($conn,$filter_status) . "'" : '';
  $apps_q = mysqli_query($conn, "SELECT ta.*, p.property_name FROM tenant_applications ta LEFT JOIN properties p ON ta.property_id=p.id $where ORDER BY ta.created_at DESC");
  ?>

  <!-- Filter tabs -->
  <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php foreach(['all'=>'All','pending'=>'Pending','reviewing'=>'Reviewing','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$lbl):
      $active = $filter_status===$k;
      $cnt = $k!=='all' ? mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM tenant_applications WHERE status='".mysqli_real_escape_string($conn,$k)."'"))['c'] : $ta_total;
    ?>
    <a href="admin_dashboard.php?page=tenant_applications&filter=<?=$k?>" style="padding:7px 16px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;background:<?=$active?'rgba(200,164,60,.2)':'rgba(255,255,255,.04)'?>;border:1px solid <?=$active?'var(--gb)':'var(--border)'?>;color:<?=$active?'var(--gold)':'var(--muted)'?>">
      <?=$lbl?> <span style="background:rgba(255,255,255,.1);border-radius:10px;padding:1px 7px;font-size:10px;margin-left:4px"><?=$cnt?></span>
    </a>
    <?php endforeach; ?>
  </div>

  <table>
    <tr>
      <th>#</th><th>Applicant</th><th>Property</th><th>Move-in</th>
      <th>Income</th><th>Occupants</th><th>Applied</th><th>Status</th><th>Actions</th>
    </tr>
    <?php if(!$apps_q || mysqli_num_rows($apps_q)==0): ?>
    <tr><td colspan="9" style="text-align:center;padding:32px;color:var(--muted)">No applications found.</td></tr>
    <?php else: $i=1; while($app = mysqli_fetch_assoc($apps_q)):
      $st  = strtolower($app['status']??'pending');
      $sc  = ($st==='approved'?'#86efac':($st==='rejected'?'#fca5a5':($st==='reviewing'?'#5b9cff':'var(--gold)')));
      $sbg = ($st==='approved'?'rgba(22,163,74,.1)':($st==='rejected'?'rgba(239,68,68,.1)':($st==='reviewing'?'rgba(59,130,246,.1)':'rgba(200,164,60,.1)')));
      $sbd = ($st==='approved'?'rgba(22,163,74,.3)':($st==='rejected'?'rgba(239,68,68,.3)':($st==='reviewing'?'rgba(59,130,246,.3)':'var(--gb)')));
    ?>
    <tr>
      <td style="color:var(--muted)"><?= $i++ ?></td>
      <td>
        <div style="font-weight:600"><?= htmlspecialchars($app['fullname']) ?></div>
        <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($app['email']??'—') ?></div>
        <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($app['phone']??'—') ?></div>
      </td>
      <td style="font-size:12px"><?= htmlspecialchars($app['property_name']??'—') ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= $app['desired_move_in'] ? date('d M Y',strtotime($app['desired_move_in'])) : '—' ?></td>
      <td style="font-size:12px;color:#86efac"><?= $app['monthly_income'] ? 'UGX '.$app['monthly_income'] : '—' ?></td>
      <td style="text-align:center"><?= (int)$app['num_occupants'] ?></td>
      <td style="font-size:11px;color:var(--muted);white-space:nowrap"><?= $app['created_at'] ? date('d M Y',strtotime($app['created_at'])) : '—' ?></td>
      <td><span style="padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;background:<?=$sbg?>;color:<?=$sc?>;border:1px solid <?=$sbd?>"><?= ucfirst($st) ?></span></td>
      <td style="white-space:nowrap">
        <a href="admin_dashboard.php?page=tenant_applications&view_app=<?= $app['id'] ?>" class="action-btn" style="background:rgba(14,90,200,.3);border-color:rgba(14,90,200,.4)">👁 View</a>
        <?php if($st==='pending'): ?>
        <a href="admin_dashboard.php?page=tenant_applications&app_action=reviewing&app_id=<?= $app['id'] ?>" class="action-btn" style="background:rgba(59,130,246,.2);border:1px solid rgba(59,130,246,.3);color:#5b9cff;font-size:11px">Review</a>
        <?php elseif($st==='reviewing'): ?>
        <a href="admin_dashboard.php?page=tenant_applications&app_action=approved&app_id=<?= $app['id'] ?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac;font-size:11px" onclick="return confirm('Approve?')">✓</a>
        <a href="admin_dashboard.php?page=tenant_applications&app_action=rejected&app_id=<?= $app['id'] ?>" class="action-btn" style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5;font-size:11px" onclick="return confirm('Reject?')">✕</a>
        <?php endif; ?>
        <a href="delete_record.php?table=tenant_applications&id=<?= $app['id'] ?>&redirect=admin_dashboard.php?page=tenant_applications" class="action-btn" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5;font-size:11px" onclick="return confirm('Delete?')">🗑</a>
      </td>
    </tr>
    <?php endwhile; endif; ?>
  </table>
  <?php endif; ?>
</section>

<?php elseif($page === 'lease_applications'): ?>
<section id="lease_applications">
  <h2 style="text-align:center;color:var(--gold)">LEASE APPLICATIONS</h2>

  <?php
  mysqli_query($conn, "CREATE TABLE IF NOT EXISTS `lease_applications` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `fullname` VARCHAR(200) NOT NULL,
    `email` VARCHAR(200) DEFAULT NULL,
    `phone` VARCHAR(50) DEFAULT NULL,
    `national_id` VARCHAR(100) DEFAULT NULL,
    `property_id` INT DEFAULT NULL,
    `lease_start` DATE DEFAULT NULL,
    `lease_end` DATE DEFAULT NULL,
    `lease_duration` VARCHAR(100) DEFAULT NULL,
    `num_occupants` INT DEFAULT 1,
    `desired_move_in` DATE DEFAULT NULL,
    `previous_address` TEXT DEFAULT NULL,
    `purpose_of_tenancy` VARCHAR(200) DEFAULT NULL,
    `digital_signature` VARCHAR(200) DEFAULT NULL,
    `terms_agreed` TINYINT DEFAULT 0,
    `additional_notes` TEXT DEFAULT NULL,
    `status` VARCHAR(50) DEFAULT 'pending',
    `admin_notes` TEXT DEFAULT NULL,
    `signed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT NOW()
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  // Handle status update
  if (isset($_GET['la_action']) && isset($_GET['la_id'])) {
      $la_id     = (int)$_GET['la_id'];
      $la_action = mysqli_real_escape_string($conn, $_GET['la_action']);
      if (in_array($la_action, ['approved','rejected','pending','reviewing'])) {
          $rb = mysqli_real_escape_string($conn, $user['fullname']);
          $ra = date('Y-m-d H:i:s');
          mysqli_query($conn, "UPDATE lease_applications SET status='$la_action', admin_notes=COALESCE(admin_notes,''), reviewed_by='$rb', reviewed_at='$ra' WHERE id=$la_id");

          $la_row = mysqli_fetch_assoc(mysqli_query($conn, "SELECT la.*, p.property_name FROM lease_applications la LEFT JOIN properties p ON la.property_id=p.id WHERE la.id=$la_id LIMIT 1"));
          if ($la_row && !empty($la_row['email'])) {
              $aname  = $la_row['fullname'];
              $aemail = $la_row['email'];
              $pname  = $la_row['property_name'] ?? 'your selected property';
              $asig   = $la_row['digital_signature'] ?? '';

              if ($la_action === 'approved') {
                  $subj = "Your Lease Application Has Been Approved — HousingHub";
                  $body = "Dear $aname,

Great news! Your lease application for $pname has been APPROVED.

"
                        . "════════════════════════════════
"
                        . "  LEASE APPLICATION APPROVED ✅
"
                        . "════════════════════════════════
"
                        . "Applicant   : $aname
"
                        . "Property    : $pname
"
                        . "Signed      : $asig
"
                        . "Approved on : " . date('d M Y, H:i') . "
"
                        . "════════════════════════════════

"
                        . "NEXT STEPS:
"
                        . "1. Our team will contact you within 24 hours to finalise your lease.
"
                        . "2. Prepare your National ID and any required deposit.
"
                        . "3. Your official lease document will be sent to this email.
"
                        . "4. Once fully signed, you will receive move-in instructions.

"
                        . "Welcome to HousingHub!

HousingHub Team
support@housinghuborg.ug";
              } elseif ($la_action === 'rejected') {
                  $subj = "Update on Your Lease Application — HousingHub";
                  $body = "Dear $aname,

Thank you for applying through HousingHub.

"
                        . "After review, we regret to inform you that your lease application for $pname was unsuccessful at this time.

"
                        . "You are welcome to apply for other available properties at: http://localhost/housinghub/properties.php

"
                        . "For feedback or queries, contact us at support@housinghuborg.ug

HousingHub Team";
              } elseif ($la_action === 'reviewing') {
                  $subj = "Your Lease Application Is Under Review — HousingHub";
                  $body = "Dear $aname,

Thank you for submitting your lease application.

Your application for $pname is currently under review. You can expect a decision within 24–48 hours.

HousingHub Team
support@housinghuborg.ug";
              }

              if (isset($subj)) {
                  require_once __DIR__ . "/send_mail.php";
                  send_mail($aemail, $subj, $body);
              }
          }
          $_SESSION['admin_success'] = "Lease application #$la_id marked as <strong>" . ucfirst($la_action) . "</strong>.";
      }
      header("Location: admin_dashboard.php?page=lease_applications"); exit();
  }

  // Handle admin notes
  if (isset($_POST['save_la_notes'])) {
      $la_id    = (int)$_POST['la_id'];
      $la_notes = mysqli_real_escape_string($conn, trim($_POST['la_admin_notes'] ?? ''));
      mysqli_query($conn, "UPDATE lease_applications SET admin_notes='$la_notes' WHERE id=$la_id");
      $_SESSION['admin_success'] = "Notes saved for lease application #$la_id.";
      header("Location: admin_dashboard.php?page=lease_applications"); exit();
  }

  $la_total     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM lease_applications"))['c'] ?? 0;
  $la_pending   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM lease_applications WHERE status='pending'"))['c'] ?? 0;
  $la_reviewing = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM lease_applications WHERE status='reviewing'"))['c'] ?? 0;
  $la_approved  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM lease_applications WHERE status='approved'"))['c'] ?? 0;
  $la_rejected  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM lease_applications WHERE status='rejected'"))['c'] ?? 0;

  $view_la = null;
  if (isset($_GET['view_la'])) {
      $vlid = (int)$_GET['view_la'];
      $view_la = mysqli_fetch_assoc(mysqli_query($conn,
          "SELECT la.*, p.property_name, p.address, p.rent_amount
           FROM lease_applications la LEFT JOIN properties p ON la.property_id=p.id
           WHERE la.id=$vlid LIMIT 1"));
  }
  ?>

  <div style="display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:24px">
    <div class="stat-box"><div class="stat-box-val"><?= $la_total ?></div><div class="stat-box-lbl">Total</div></div>
    <div class="stat-box" style="border-color:var(--gb)"><div class="stat-box-val"><?= $la_pending ?></div><div class="stat-box-lbl">Pending</div></div>
    <div class="stat-box" style="border-color:rgba(59,130,246,.3)"><div class="stat-box-val" style="color:#5b9cff"><?= $la_reviewing ?></div><div class="stat-box-lbl">Reviewing</div></div>
    <div class="stat-box" style="border-color:rgba(22,163,74,.3)"><div class="stat-box-val" style="color:#86efac"><?= $la_approved ?></div><div class="stat-box-lbl">Approved</div></div>
    <div class="stat-box" style="border-color:rgba(239,68,68,.3)"><div class="stat-box-val" style="color:#fca5a5"><?= $la_rejected ?></div><div class="stat-box-lbl">Rejected</div></div>
  </div>

  <?php if($view_la): ?>
  <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:28px;margin-bottom:24px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--border)">
      <div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;color:var(--white)"><?= htmlspecialchars($view_la['fullname']) ?></div>
        <div style="font-size:12px;color:var(--muted)">Lease Application #<?= $view_la['id'] ?> · <?= $view_la['created_at'] ? date('d M Y, H:i', strtotime($view_la['created_at'])) : '—' ?></div>
      </div>
      <a href="admin_dashboard.php?page=lease_applications" class="action-btn" style="font-size:12px">← Back</a>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:18px">
      <?php foreach([
        'Email'=>$view_la['email'],'Phone'=>$view_la['phone'],'National ID'=>$view_la['national_id'],
        'Property'=>$view_la['property_name']??'—','Address'=>$view_la['address']??'—',
        'Rent'=>$view_la['rent_amount']?'UGX '.number_format($view_la['rent_amount']).'/mo':'—',
        'Lease Start'=>$view_la['lease_start']?date('d M Y',strtotime($view_la['lease_start'])):'—',
        'Lease End'=>$view_la['lease_end']?date('d M Y',strtotime($view_la['lease_end'])):'—',
        'Duration'=>$view_la['lease_duration'],'Move-in'=>$view_la['desired_move_in']?date('d M Y',strtotime($view_la['desired_move_in'])):'—',
        'Occupants'=>$view_la['num_occupants'],'Purpose'=>$view_la['purpose_of_tenancy'],
        'Terms Agreed'=>$view_la['terms_agreed']?'✅ Yes':'❌ No',
        'Digital Signature'=>$view_la['digital_signature'],'Status'=>ucfirst($view_la['status']??'pending')
      ] as $lbl=>$val): ?>
      <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:7px;padding:12px">
        <div style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:5px"><?= $lbl ?></div>
        <div style="font-size:13px;color:var(--white)"><?= htmlspecialchars($val ?? '—') ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php if(!empty($view_la['previous_address'])): ?>
    <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:7px;padding:14px;margin-bottom:12px">
      <div style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:5px">Previous Address</div>
      <div style="font-size:13px;color:rgba(255,255,255,.8)"><?= htmlspecialchars($view_la['previous_address']) ?></div>
    </div>
    <?php endif; ?>
    <?php if(!empty($view_la['additional_notes'])): ?>
    <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:7px;padding:14px;margin-bottom:18px">
      <div style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:5px">Additional Notes</div>
      <div style="font-size:13px;color:rgba(255,255,255,.8)"><?= htmlspecialchars($view_la['additional_notes']) ?></div>
    </div>
    <?php endif; ?>
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-bottom:20px">
      <?php $lst = strtolower($view_la['status']??'pending'); ?>
      <?php if($lst!=='reviewing'): ?><a href="admin_dashboard.php?page=lease_applications&la_action=reviewing&la_id=<?= $view_la['id'] ?>" class="action-btn" style="background:rgba(59,130,246,.2);border:1px solid rgba(59,130,246,.3);color:#5b9cff">🔍 Under Review</a><?php endif; ?>
      <?php if($lst!=='approved'): ?><a href="admin_dashboard.php?page=lease_applications&la_action=approved&la_id=<?= $view_la['id'] ?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac" onclick="return confirm('Approve this lease application?')">✓ Approve</a><?php endif; ?>
      <?php if($lst!=='rejected'): ?><a href="admin_dashboard.php?page=lease_applications&la_action=rejected&la_id=<?= $view_la['id'] ?>" class="action-btn" style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5" onclick="return confirm('Reject?')">✕ Reject</a><?php endif; ?>
      <a href="delete_record.php?table=lease_applications&id=<?= $view_la['id'] ?>&redirect=admin_dashboard.php?page=lease_applications" class="action-btn" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5" onclick="return confirm('Delete permanently?')">🗑 Delete</a>
    </div>
    <form method="POST">
      <input type="hidden" name="la_id" value="<?= $view_la['id'] ?>">
      <label style="display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px">Admin Notes (Internal Only)</label>
      <textarea name="la_admin_notes" rows="3" style="width:100%;padding:10px 13px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:'Outfit',sans-serif;font-size:13px;outline:none;resize:vertical;margin-bottom:10px"><?= htmlspecialchars($view_la['admin_notes']??'') ?></textarea>
      <button type="submit" name="save_la_notes" class="action-btn" style="background:rgba(200,164,60,.2);border:1px solid var(--gb)">💾 Save Notes</button>
    </form>
  </div>

  <?php else: ?>
  <?php
  $la_filter = $_GET['filter'] ?? 'all';
  $la_where  = $la_filter !== 'all' ? "WHERE la.status='" . mysqli_real_escape_string($conn,$la_filter) . "'" : '';
  $las_q = mysqli_query($conn, "SELECT la.*, p.property_name FROM lease_applications la LEFT JOIN properties p ON la.property_id=p.id $la_where ORDER BY la.created_at DESC");
  ?>
  <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php foreach(['all'=>'All','pending'=>'Pending','reviewing'=>'Reviewing','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$lbl):
      $act = $la_filter===$k;
      $cnt = $k!=='all' ? (mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM lease_applications WHERE status='".mysqli_real_escape_string($conn,$k)."'"))['c']??0) : $la_total;
    ?>
    <a href="admin_dashboard.php?page=lease_applications&filter=<?=$k?>" style="padding:7px 16px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;background:<?=$act?'rgba(200,164,60,.2)':'rgba(255,255,255,.04)'?>;border:1px solid <?=$act?'var(--gb)':'var(--border)'?>;color:<?=$act?'var(--gold)':'var(--muted)'?>">
      <?=$lbl?> <span style="background:rgba(255,255,255,.1);border-radius:10px;padding:1px 7px;font-size:10px;margin-left:4px"><?=$cnt?></span>
    </a>
    <?php endforeach; ?>
  </div>
  <table>
    <tr><th>#</th><th>Applicant</th><th>Property</th><th>Duration</th><th>Move-in</th><th>Signature</th><th>Applied</th><th>Status</th><th>Actions</th></tr>
    <?php if(!$las_q || mysqli_num_rows($las_q)==0): ?>
    <tr><td colspan="9" style="text-align:center;padding:32px;color:var(--muted)">No lease applications found.</td></tr>
    <?php else: $i=1; while($la = mysqli_fetch_assoc($las_q)):
      $st  = strtolower($la['status']??'pending');
      $sc  = ($st==='approved'?'#86efac':($st==='rejected'?'#fca5a5':($st==='reviewing'?'#5b9cff':'var(--gold)')));
      $sbg = ($st==='approved'?'rgba(22,163,74,.1)':($st==='rejected'?'rgba(239,68,68,.1)':($st==='reviewing'?'rgba(59,130,246,.1)':'rgba(200,164,60,.1)')));
      $sbd = ($st==='approved'?'rgba(22,163,74,.3)':($st==='rejected'?'rgba(239,68,68,.3)':($st==='reviewing'?'rgba(59,130,246,.3)':'var(--gb)')));
    ?>
    <tr>
      <td style="color:var(--muted)"><?= $i++ ?></td>
      <td><div style="font-weight:600"><?= htmlspecialchars($la['fullname']) ?></div><div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($la['email']??'—') ?></div></td>
      <td style="font-size:12px"><?= htmlspecialchars($la['property_name']??'—') ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars($la['lease_duration']??'—') ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= $la['desired_move_in']?date('d M Y',strtotime($la['desired_move_in'])):'—' ?></td>
      <td style="font-size:12px;color:var(--gold);font-style:italic"><?= htmlspecialchars($la['digital_signature']??'—') ?></td>
      <td style="font-size:11px;color:var(--muted)"><?= $la['created_at']?date('d M Y',strtotime($la['created_at'])):'—' ?></td>
      <td><span style="padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;background:<?=$sbg?>;color:<?=$sc?>;border:1px solid <?=$sbd?>"><?= ucfirst($st) ?></span></td>
      <td style="white-space:nowrap">
        <a href="admin_dashboard.php?page=lease_applications&view_la=<?= $la['id'] ?>" class="action-btn" style="background:rgba(14,90,200,.3);border-color:rgba(14,90,200,.4)">👁 View</a>
        <?php if($st==='pending'): ?>
        <a href="admin_dashboard.php?page=lease_applications&la_action=reviewing&la_id=<?= $la['id'] ?>" class="action-btn" style="background:rgba(59,130,246,.2);border:1px solid rgba(59,130,246,.3);color:#5b9cff;font-size:11px">Review</a>
        <?php elseif($st==='reviewing'): ?>
        <a href="admin_dashboard.php?page=lease_applications&la_action=approved&la_id=<?= $la['id'] ?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac;font-size:11px" onclick="return confirm('Approve?')">✓</a>
        <a href="admin_dashboard.php?page=lease_applications&la_action=rejected&la_id=<?= $la['id'] ?>" class="action-btn" style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5;font-size:11px" onclick="return confirm('Reject?')">✕</a>
        <?php endif; ?>
        <a href="delete_record.php?table=lease_applications&id=<?= $la['id'] ?>&redirect=admin_dashboard.php?page=lease_applications" class="action-btn" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5;font-size:11px" onclick="return confirm('Delete?')">🗑</a>
      </td>
    </tr>
    <?php endwhile; endif; ?>
  </table>
  <?php endif; ?>
</section>

<?php elseif($page === 'property_applications'): ?>
<section id="property_applications">
  <h2 style="text-align:center;color:var(--gold)">🏠 PROPERTY APPLICATIONS</h2>
  <p style="text-align:center;font-size:13px;color:var(--muted);margin-bottom:24px">Applications from property owners who want HousingHub to manage their properties.</p>

  <?php
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
    status VARCHAR(50) DEFAULT 'pending',
    admin_notes TEXT DEFAULT NULL,
    reviewed_by VARCHAR(200) DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT NOW()
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // [handlers moved to top]

  // Handle admin notes
  if (isset($_POST['save_pa_notes'])) {
      $pa_id    = (int)$_POST['pa_id'];
      $pa_notes = mysqli_real_escape_string($conn, trim($_POST['pa_admin_notes'] ?? ''));
      mysqli_query($conn, "UPDATE property_applications SET admin_notes='$pa_notes' WHERE id=$pa_id");
      $_SESSION['admin_success'] = "Notes saved.";
      header("Location: admin_dashboard.php?page=property_applications"); exit();
  }

  $pa_total     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM property_applications"))['c'] ?? 0;
  $pa_pending   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM property_applications WHERE status='pending'"))['c'] ?? 0;
  $pa_reviewing = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM property_applications WHERE status IN ('reviewing','contacted')"))['c'] ?? 0;
  $pa_approved  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM property_applications WHERE status='approved'"))['c'] ?? 0;
  $pa_rejected  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM property_applications WHERE status='rejected'"))['c'] ?? 0;
  ?>

  <div class="stat-row" style="margin-bottom:24px">
    <div class="stat-box"><div class="stat-box-val"><?= $pa_total ?></div><div class="stat-box-lbl">Total</div></div>
    <div class="stat-box" style="border-color:var(--gb)"><div class="stat-box-val"><?= $pa_pending ?></div><div class="stat-box-lbl">Pending</div></div>
    <div class="stat-box" style="border-color:rgba(59,130,246,.3)"><div class="stat-box-val" style="color:#5b9cff"><?= $pa_reviewing ?></div><div class="stat-box-lbl">In Progress</div></div>
    <div class="stat-box" style="border-color:rgba(22,163,74,.3)"><div class="stat-box-val" style="color:#86efac"><?= $pa_approved ?></div><div class="stat-box-lbl">Approved</div></div>
  </div>

  <?php
  $view_pa = null;
  if (isset($_GET['view_pa'])) {
      $vpid = (int)$_GET['view_pa'];
      $view_pa = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM property_applications WHERE id=$vpid LIMIT 1"));
  }
  ?>

  <?php if($view_pa): ?>
  <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:28px;margin-bottom:24px">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;padding-bottom:16px;border-bottom:1px solid var(--border)">
      <div>
        <div style="font-family:'Cormorant Garamond',serif;font-size:22px;font-weight:700;color:var(--white)"><?= htmlspecialchars($view_pa['fullname']) ?></div>
        <div style="font-size:12px;color:var(--muted)">Application #<?= $view_pa['id'] ?> · <?= $view_pa['created_at'] ? date('d M Y, H:i', strtotime($view_pa['created_at'])) : '—' ?></div>
      </div>
      <a href="admin_dashboard.php?page=property_applications" class="action-btn" style="font-size:12px">← Back</a>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:18px">
      <?php foreach([
        'Phone'=>$view_pa['phone'],'Email'=>$view_pa['email'],'Occupation'=>$view_pa['occupation'],
        'Owner Location'=>$view_pa['owner_location'],'Property Name'=>$view_pa['property_name'],
        'Property Type'=>$view_pa['property_type'],'Address'=>$view_pa['property_address'],
        'Units'=>$view_pa['units'],'Rent/Unit'=>$view_pa['rent_amount']?'UGX '.number_format($view_pa['rent_amount']):'—',
        'Bedrooms'=>$view_pa['bedrooms'],'Current Status'=>$view_pa['property_status'],
        'Services Needed'=>$view_pa['services_needed'],'Start Timeline'=>$view_pa['start_timeline'],
        'Referral Source'=>$view_pa['referral_source'],'App Status'=>ucfirst($view_pa['status']??'pending'),
      ] as $lbl=>$val): ?>
      <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:7px;padding:12px">
        <div style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:5px"><?= $lbl ?></div>
        <div style="font-size:13px;color:var(--white)"><?= htmlspecialchars($val ?? '—') ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php foreach(['Amenities'=>'amenities','Description'=>'description','Questions/Notes'=>'questions'] as $lbl=>$key): if(!empty($view_pa[$key])): ?>
    <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:7px;padding:14px;margin-bottom:12px">
      <div style="font-size:9px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:5px"><?= $lbl ?></div>
      <div style="font-size:13px;color:rgba(255,255,255,.8);line-height:1.6"><?= htmlspecialchars($view_pa[$key]) ?></div>
    </div>
    <?php endif; endforeach; ?>

    <!-- QUICK ACTIONS -->
    <div style="display:flex;gap:10px;flex-wrap:wrap;margin-top:20px;padding-top:18px;border-top:1px solid var(--border)">
      <?php $pst = strtolower($view_pa['status']??'pending'); ?>
      <?php if($pst!=='contacted'): ?><a href="admin_dashboard.php?page=property_applications&pa_action=contacted&pa_id=<?= $view_pa['id'] ?>" class="action-btn" style="background:rgba(59,130,246,.2);border:1px solid rgba(59,130,246,.3);color:#5b9cff">📞 Mark Contacted</a><?php endif; ?>
      <?php if($pst!=='reviewing'): ?><a href="admin_dashboard.php?page=property_applications&pa_action=reviewing&pa_id=<?= $view_pa['id'] ?>" class="action-btn" style="background:rgba(59,130,246,.2);border:1px solid rgba(59,130,246,.3);color:#5b9cff">🔍 Reviewing</a><?php endif; ?>
      <?php if($pst!=='approved'): ?><a href="admin_dashboard.php?page=property_applications&pa_action=approved&pa_id=<?= $view_pa['id'] ?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac" onclick="return confirm('Approve and send congratulations email?')">✓ Approve</a><?php endif; ?>
      <?php if($pst!=='rejected'): ?><a href="admin_dashboard.php?page=property_applications&pa_action=rejected&pa_id=<?= $view_pa['id'] ?>" class="action-btn" style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5" onclick="return confirm('Reject?')">✕ Reject</a><?php endif; ?>
      <a href="admin_dashboard.php?page=propertyowners" class="action-btn" style="background:rgba(200,164,60,.2);border:1px solid var(--gb)">🏢 Add as Property Owner</a>
      <a href="delete_record.php?table=property_applications&id=<?= $view_pa['id'] ?>&redirect=admin_dashboard.php?page=property_applications" class="action-btn" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5" onclick="return confirm('Delete?')">🗑 Delete</a>
    </div>

    <!-- ADMIN NOTES -->
    <div style="margin-top:20px">
      <form method="POST">
        <input type="hidden" name="pa_id" value="<?= $view_pa['id'] ?>">
        <label style="display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px">Admin Notes (Internal)</label>
        <textarea name="pa_admin_notes" rows="3" style="width:100%;padding:10px 13px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:'Outfit',sans-serif;font-size:13px;outline:none;resize:vertical;margin-bottom:10px"><?= htmlspecialchars($view_pa['admin_notes']??'') ?></textarea>
        <button type="submit" name="save_pa_notes" class="action-btn" style="background:rgba(200,164,60,.2);border:1px solid var(--gb)">💾 Save Notes</button>
      </form>
    </div>
  </div>

  <?php else: ?>
  <?php
  $pa_filter = $_GET['filter'] ?? 'all';
  $pa_where  = $pa_filter !== 'all' ? "WHERE status='" . mysqli_real_escape_string($conn,$pa_filter) . "'" : '';
  $pa_q = mysqli_query($conn, "SELECT * FROM property_applications $pa_where ORDER BY created_at DESC");
  ?>
  <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php foreach(['all'=>'All','pending'=>'Pending','contacted'=>'Contacted','reviewing'=>'Reviewing','approved'=>'Approved','rejected'=>'Rejected'] as $k=>$lbl):
      $act = $pa_filter===$k;
      $cnt = $k!=='all' ? (mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM property_applications WHERE status='".mysqli_real_escape_string($conn,$k)."'"))['c']??0) : $pa_total;
    ?>
    <a href="admin_dashboard.php?page=property_applications&filter=<?=$k?>" style="padding:7px 16px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;background:<?=$act?'rgba(200,164,60,.2)':'rgba(255,255,255,.04)'?>;border:1px solid <?=$act?'var(--gb)':'var(--border)'?>;color:<?=$act?'var(--gold)':'var(--muted)'?>">
      <?=$lbl?> <span style="background:rgba(255,255,255,.1);border-radius:10px;padding:1px 7px;font-size:10px;margin-left:4px"><?=$cnt?></span>
    </a>
    <?php endforeach; ?>
  </div>
  <table>
    <tr><th>#</th><th>Applicant</th><th>Property</th><th>Units</th><th>Services</th><th>Timeline</th><th>Applied</th><th>Status</th><th>Actions</th></tr>
    <?php if(!$pa_q || mysqli_num_rows($pa_q)==0): ?>
    <tr><td colspan="9" style="text-align:center;padding:32px;color:var(--muted)">No property applications yet. Share <strong>get_started.php</strong> with property owners.</td></tr>
    <?php else: $i=1; while($pa = mysqli_fetch_assoc($pa_q)):
      $st  = strtolower($pa['status']??'pending');
      $sc  = ($st==='approved'?'#86efac':($st==='rejected'?'#fca5a5':(in_array($st,['reviewing','contacted'])?'#5b9cff':'var(--gold)')));
      $sbg = ($st==='approved'?'rgba(22,163,74,.1)':($st==='rejected'?'rgba(239,68,68,.1)':(in_array($st,['reviewing','contacted'])?'rgba(59,130,246,.1)':'rgba(200,164,60,.1)')));
      $sbd = ($st==='approved'?'rgba(22,163,74,.3)':($st==='rejected'?'rgba(239,68,68,.3)':(in_array($st,['reviewing','contacted'])?'rgba(59,130,246,.3)':'var(--gb)')));
    ?>
    <tr>
      <td style="color:var(--muted)"><?= $i++ ?></td>
      <td>
        <div style="font-weight:600"><?= htmlspecialchars($pa['fullname']) ?></div>
        <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($pa['phone']) ?></div>
        <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($pa['email']??'—') ?></div>
      </td>
      <td>
        <div style="font-size:13px;font-weight:600"><?= htmlspecialchars($pa['property_name']) ?></div>
        <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($pa['property_type']??'—') ?> · <?= htmlspecialchars($pa['property_address']??'—') ?></div>
      </td>
      <td style="text-align:center;color:var(--gold)"><?= (int)$pa['units'] ?></td>
      <td style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($pa['services_needed']??'—') ?></td>
      <td style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($pa['start_timeline']??'—') ?></td>
      <td style="font-size:11px;color:var(--muted)"><?= $pa['created_at']?date('d M Y',strtotime($pa['created_at'])):'—' ?></td>
      <td><span style="padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;background:<?=$sbg?>;color:<?=$sc?>;border:1px solid <?=$sbd?>"><?= ucfirst($st) ?></span></td>
      <td style="white-space:nowrap">
        <a href="admin_dashboard.php?page=property_applications&view_pa=<?= $pa['id'] ?>" class="action-btn" style="background:rgba(14,90,200,.3);border-color:rgba(14,90,200,.4)">👁 View</a>
        <?php if($st==='pending'): ?>
        <a href="admin_dashboard.php?page=property_applications&pa_action=contacted&pa_id=<?= $pa['id'] ?>" class="action-btn" style="background:rgba(59,130,246,.2);border:1px solid rgba(59,130,246,.3);color:#5b9cff;font-size:11px">📞</a>
        <?php elseif(in_array($st,['contacted','reviewing'])): ?>
        <a href="admin_dashboard.php?page=property_applications&pa_action=approved&pa_id=<?= $pa['id'] ?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac;font-size:11px" onclick="return confirm('Approve?')">✓</a>
        <a href="admin_dashboard.php?page=property_applications&pa_action=rejected&pa_id=<?= $pa['id'] ?>" class="action-btn" style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5;font-size:11px" onclick="return confirm('Reject?')">✕</a>
        <?php endif; ?>
      </td>
    </tr>
    <?php endwhile; endif; ?>
  </table>
  <?php endif; ?>
</section>


<?php elseif($page === 'viewing_requests'): ?>
<section id="viewing_requests">
  <h2 style="text-align:center;color:var(--gold)">PROPERTY VIEWING REQUESTS</h2>
  <?php
  mysqli_query($conn, "CREATE TABLE IF NOT EXISTS property_viewing_requests (
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
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  if (isset($_GET['vr_action']) && isset($_GET['vr_id'])) {
    $vr_id = (int)$_GET['vr_id'];
    $vr_action = mysqli_real_escape_string($conn, $_GET['vr_action']);
    if (in_array($vr_action, ['pending','approved','contacted','completed','rejected'])) {
      $rb = mysqli_real_escape_string($conn, $user['fullname']);
      $ra = date('Y-m-d H:i:s');
      mysqli_query($conn,"UPDATE property_viewing_requests SET status='$vr_action',reviewed_by='$rb',reviewed_at='$ra' WHERE id=$vr_id");
      $_SESSION['admin_success'] = "Viewing request #$vr_id marked as <strong>".ucfirst($vr_action)."</strong>.";
    }
    header("Location: admin_dashboard.php?page=viewing_requests"); exit();
  }
  if (isset($_GET['delete_vr'])) {
    $vr_id = (int)$_GET['delete_vr'];
    mysqli_query($conn,"DELETE FROM property_viewing_requests WHERE id=$vr_id");
    $_SESSION['admin_success'] = "Viewing request deleted.";
    header("Location: admin_dashboard.php?page=viewing_requests"); exit();
  }

  $vr_total    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM property_viewing_requests"))['c'] ?? 0;
  $vr_pending  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM property_viewing_requests WHERE status='pending'"))['c'] ?? 0;
  $vr_approved = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM property_viewing_requests WHERE status='approved'"))['c'] ?? 0;
  $vr_contact  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM property_viewing_requests WHERE status='contacted'"))['c'] ?? 0;
  $vr_done     = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM property_viewing_requests WHERE status='completed'"))['c'] ?? 0;

  $vr_filter = $_GET['filter'] ?? 'all';
  $vr_where  = $vr_filter !== 'all' ? "WHERE status='".mysqli_real_escape_string($conn,$vr_filter)."'" : '';
  $vr_q = mysqli_query($conn,"SELECT * FROM property_viewing_requests $vr_where ORDER BY created_at DESC");
  ?>
  <div class="stat-row" style="margin-bottom:24px">
    <div class="stat-box"><div class="stat-box-val"><?= $vr_total ?></div><div class="stat-box-lbl">Total</div></div>
    <div class="stat-box" style="border-color:var(--gb)"><div class="stat-box-val"><?= $vr_pending ?></div><div class="stat-box-lbl">Pending</div></div>
    <div class="stat-box" style="border-color:rgba(22,163,74,.3)"><div class="stat-box-val" style="color:#86efac"><?= $vr_approved ?></div><div class="stat-box-lbl">Approved</div></div>
    <div class="stat-box"><div class="stat-box-val"><?= $vr_done ?></div><div class="stat-box-lbl">Completed</div></div>
  </div>
  <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php foreach(['all'=>'All','pending'=>'Pending','approved'=>'Approved','contacted'=>'Contacted','completed'=>'Completed','rejected'=>'Rejected'] as $k=>$lbl):
      $act = $vr_filter===$k;
      $cnt = $k==='all' ? $vr_total : (mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM property_viewing_requests WHERE status='".mysqli_real_escape_string($conn,$k)."'"))['c']??0);
    ?><a href="admin_dashboard.php?page=viewing_requests&filter=<?=$k?>" style="padding:7px 16px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;background:<?=$act?'rgba(200,164,60,.2)':'rgba(255,255,255,.04)'?>;border:1px solid <?=$act?'var(--gb)':'var(--border)'?>;color:<?=$act?'var(--gold)':'var(--muted)'?>"><?=$lbl?> <span style="background:rgba(255,255,255,.1);border-radius:10px;padding:1px 7px;font-size:10px;margin-left:4px"><?=$cnt?></span></a>
    <?php endforeach; ?>
  </div>
  <table>
    <tr><th>#</th><th>Visitor</th><th>Property</th><th>Date & Time</th><th>Type</th><th>Host</th><th>Status</th><th>Actions</th></tr>
    <?php if(!$vr_q||mysqli_num_rows($vr_q)===0): ?>
    <tr><td colspan="8" style="text-align:center;padding:32px;color:var(--muted)">No viewing requests found.</td></tr>
    <?php else: $i=1; while($vr=mysqli_fetch_assoc($vr_q)):
      $st=strtolower($vr['status']??'pending');
      $sc=($st==='approved'?'#86efac':($st==='rejected'?'#fca5a5':($st==='contacted'?'#5b9cff':($st==='completed'?'#e0c06a':'var(--gold)'))));
      $sbg=($st==='approved'?'rgba(22,163,74,.1)':($st==='rejected'?'rgba(239,68,68,.1)':($st==='contacted'?'rgba(59,130,246,.1)':'rgba(200,164,60,.1)')));
      $sbd=($st==='approved'?'rgba(22,163,74,.3)':($st==='rejected'?'rgba(239,68,68,.3)':($st==='contacted'?'rgba(59,130,246,.3)':'var(--gb)')));
    ?>
    <tr>
      <td style="color:var(--muted)"><?=$i++?></td>
      <td><div style="font-weight:600"><?=htmlspecialchars($vr['fullname'])?></div><div style="font-size:11px;color:var(--muted)"><?=htmlspecialchars($vr['phone'])?></div><div style="font-size:11px;color:var(--muted)"><?=htmlspecialchars($vr['email']?:'—')?></div></td>
      <td><?=htmlspecialchars($vr['property_name'])?></td>
      <td style="font-size:12px;color:var(--muted)"><?=$vr['inspection_date']?date('d M Y',strtotime($vr['inspection_date'])):'—'?><br><?=htmlspecialchars($vr['inspection_time'])?></td>
      <td><?=htmlspecialchars($vr['visitor_type'])?></td>
      <td><?=htmlspecialchars($vr['assigned_host']?:'—')?></td>
      <td><span style="padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;background:<?=$sbg?>;color:<?=$sc?>;border:1px solid <?=$sbd?>"><?=ucfirst($st)?></span></td>
      <td style="white-space:nowrap">
        <a href="admin_dashboard.php?page=viewing_requests&vr_action=approved&vr_id=<?=$vr['id']?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac">Approve</a>
        <a href="admin_dashboard.php?page=viewing_requests&vr_action=contacted&vr_id=<?=$vr['id']?>" class="action-btn" style="background:rgba(59,130,246,.2);border:1px solid rgba(59,130,246,.3);color:#5b9cff">Contacted</a>
        <a href="admin_dashboard.php?page=viewing_requests&vr_action=completed&vr_id=<?=$vr['id']?>" class="action-btn">Complete</a>
        <a href="admin_dashboard.php?page=viewing_requests&vr_action=rejected&vr_id=<?=$vr['id']?>" class="action-btn" style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5">Reject</a>
        <a href="admin_dashboard.php?page=viewing_requests&delete_vr=<?=$vr['id']?>" class="action-btn" onclick="return confirm('Delete?')" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5">Delete</a>
      </td>
    </tr>
    <?php endwhile; endif; ?>
  </table>
</section>

<?php elseif($page === 'guest_requests'): ?>
<section id="guest_requests">
  <h2 style="text-align:center;color:var(--gold)">TENANT GUEST REQUESTS</h2>
  <?php
  mysqli_query($conn, "CREATE TABLE IF NOT EXISTS tenant_guest_requests (
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
    reviewed_by VARCHAR(200) DEFAULT NULL,
    reviewed_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

  if (isset($_GET['gr_action']) && isset($_GET['gr_id'])) {
    $gr_id = (int)$_GET['gr_id'];
    $gr_action = mysqli_real_escape_string($conn, $_GET['gr_action']);
    if (in_array($gr_action, ['pending','approved','checked_in','checked_out','rejected'])) {
      $rb = mysqli_real_escape_string($conn, $user['fullname']);
      $ra = date('Y-m-d H:i:s');
      mysqli_query($conn,"UPDATE tenant_guest_requests SET status='$gr_action',reviewed_by='$rb',reviewed_at='$ra' WHERE id=$gr_id");
      $_SESSION['admin_success'] = "Guest request #$gr_id marked as <strong>".ucfirst(str_replace('_',' ',$gr_action))."</strong>.";
    }
    header("Location: admin_dashboard.php?page=guest_requests"); exit();
  }
  if (isset($_GET['delete_gr'])) {
    $gr_id = (int)$_GET['delete_gr'];
    mysqli_query($conn,"DELETE FROM tenant_guest_requests WHERE id=$gr_id");
    $_SESSION['admin_success'] = "Guest request deleted.";
    header("Location: admin_dashboard.php?page=guest_requests"); exit();
  }

  $gr_total    = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM tenant_guest_requests"))['c'] ?? 0;
  $gr_pending  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM tenant_guest_requests WHERE status='pending'"))['c'] ?? 0;
  $gr_approved = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM tenant_guest_requests WHERE status='approved'"))['c'] ?? 0;
  $gr_in       = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM tenant_guest_requests WHERE status='checked_in'"))['c'] ?? 0;
  $gr_out      = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM tenant_guest_requests WHERE status='checked_out'"))['c'] ?? 0;

  $gr_filter = $_GET['filter'] ?? 'all';
  $gr_where  = $gr_filter !== 'all' ? "WHERE status='".mysqli_real_escape_string($conn,$gr_filter)."'" : '';
  $gr_q = mysqli_query($conn,"SELECT * FROM tenant_guest_requests $gr_where ORDER BY created_at DESC");
  ?>
  <div class="stat-row" style="margin-bottom:24px">
    <div class="stat-box"><div class="stat-box-val"><?=$gr_total?></div><div class="stat-box-lbl">Total</div></div>
    <div class="stat-box" style="border-color:var(--gb)"><div class="stat-box-val"><?=$gr_pending?></div><div class="stat-box-lbl">Pending</div></div>
    <div class="stat-box" style="border-color:rgba(22,163,74,.3)"><div class="stat-box-val" style="color:#86efac"><?=$gr_approved?></div><div class="stat-box-lbl">Approved</div></div>
    <div class="stat-box" style="border-color:rgba(59,130,246,.3)"><div class="stat-box-val" style="color:#5b9cff"><?=$gr_in?></div><div class="stat-box-lbl">Checked In</div></div>
    <div class="stat-box"><div class="stat-box-val"><?=$gr_out?></div><div class="stat-box-lbl">Checked Out</div></div>
  </div>
  <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php foreach(['all'=>'All','pending'=>'Pending','approved'=>'Approved','checked_in'=>'Checked In','checked_out'=>'Checked Out','rejected'=>'Rejected'] as $k=>$lbl):
      $act=$gr_filter===$k;
      $cnt=$k==='all'?$gr_total:(mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM tenant_guest_requests WHERE status='".mysqli_real_escape_string($conn,$k)."'"))['c']??0);
    ?><a href="admin_dashboard.php?page=guest_requests&filter=<?=$k?>" style="padding:7px 16px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;background:<?=$act?'rgba(200,164,60,.2)':'rgba(255,255,255,.04)'?>;border:1px solid <?=$act?'var(--gb)':'var(--border)'?>;color:<?=$act?'var(--gold)':'var(--muted)'?>"><?=$lbl?> <span style="background:rgba(255,255,255,.1);border-radius:10px;padding:1px 7px;font-size:10px;margin-left:4px"><?=$cnt?></span></a>
    <?php endforeach; ?>
  </div>
  <table>
    <tr><th>#</th><th>Guest</th><th>Tenant / Unit</th><th>Date & Time</th><th>Relationship</th><th>Notes</th><th>Status</th><th>Actions</th></tr>
    <?php if(!$gr_q||mysqli_num_rows($gr_q)===0): ?>
    <tr><td colspan="8" style="text-align:center;padding:32px;color:var(--muted)">No guest requests found.</td></tr>
    <?php else: $i=1; while($gr=mysqli_fetch_assoc($gr_q)):
      $st=strtolower($gr['status']??'pending');
      $sc=($st==='approved'?'#86efac':($st==='rejected'?'#fca5a5':($st==='checked_in'?'#5b9cff':($st==='checked_out'?'#e0c06a':'var(--gold)'))));
      $sbg=($st==='approved'?'rgba(22,163,74,.1)':($st==='rejected'?'rgba(239,68,68,.1)':($st==='checked_in'?'rgba(59,130,246,.1)':'rgba(200,164,60,.1)')));
      $sbd=($st==='approved'?'rgba(22,163,74,.3)':($st==='rejected'?'rgba(239,68,68,.3)':($st==='checked_in'?'rgba(59,130,246,.3)':'var(--gb)')));
    ?>
    <tr>
      <td style="color:var(--muted)"><?=$i++?></td>
      <td><div style="font-weight:600"><?=htmlspecialchars($gr['guest_name'])?></div><div style="font-size:11px;color:var(--muted)"><?=htmlspecialchars($gr['guest_phone'])?></div></td>
      <td><div><?=htmlspecialchars($gr['tenant_name'])?></div><div style="font-size:11px;color:var(--muted)">Unit: <?=htmlspecialchars($gr['unit_number']?:'—')?></div></td>
      <td style="font-size:12px;color:var(--muted)"><?=$gr['visit_date']?date('d M Y',strtotime($gr['visit_date'])):'—'?><br>In: <?=htmlspecialchars($gr['arrival_time'])?><br>Out: <?=htmlspecialchars($gr['departure_time']?:'—')?></td>
      <td><?=htmlspecialchars($gr['guest_relationship'])?></td>
      <td style="font-size:11px;color:var(--muted)"><?=htmlspecialchars(substr($gr['guest_notes']??'',0,50))?><?=!empty($gr['guest_notes'])&&strlen($gr['guest_notes'])>50?'...':''?></td>
      <td><span style="padding:4px 10px;border-radius:20px;font-size:10px;font-weight:700;text-transform:uppercase;background:<?=$sbg?>;color:<?=$sc?>;border:1px solid <?=$sbd?>"><?=ucfirst(str_replace('_',' ',$st))?></span></td>
      <td style="white-space:nowrap">
        <a href="admin_dashboard.php?page=guest_requests&gr_action=approved&gr_id=<?=$gr['id']?>" class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac">Approve</a>
        <a href="admin_dashboard.php?page=guest_requests&gr_action=checked_in&gr_id=<?=$gr['id']?>" class="action-btn" style="background:rgba(59,130,246,.2);border:1px solid rgba(59,130,246,.3);color:#5b9cff">Check In</a>
        <a href="admin_dashboard.php?page=guest_requests&gr_action=checked_out&gr_id=<?=$gr['id']?>" class="action-btn">Check Out</a>
        <a href="admin_dashboard.php?page=guest_requests&gr_action=rejected&gr_id=<?=$gr['id']?>" class="action-btn" style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5">Reject</a>
        <a href="admin_dashboard.php?page=guest_requests&delete_gr=<?=$gr['id']?>" class="action-btn" onclick="return confirm('Delete?')" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.2);color:#fca5a5">Delete</a>
      </td>
    </tr>
    <?php endwhile; endif; ?>
  </table>
</section>

<?php elseif($page === 'agreed_users'): ?>
<section id="agreed_users">
  <h2 style="text-align:center;color:var(--gold)">AGREED USER AGREEMENTS</h2>
  <p style="text-align:center;font-size:13px;color:var(--muted);margin-bottom:24px">Track which users have agreed to property lease terms or platform agreements.</p>

  <?php
  // Stats
  $ua_total   = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM user_agreements"))['c'] ?? 0;
  $ua_agreed  = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(*) AS c FROM user_agreements WHERE agreed=1"))['c'] ?? 0;
  $ua_pending = $ua_total - $ua_agreed;
  $ua_users_with = mysqli_fetch_assoc(mysqli_query($conn,"SELECT COUNT(DISTINCT user_id) AS c FROM user_agreements"))['c'] ?? 0;

  // Filter
  $ua_filter = $_GET['filter'] ?? 'all';
  $ua_where  = ($ua_filter==='agreed') ? "WHERE a.agreed=1" : (($ua_filter==='pending') ? "WHERE a.agreed=0" : "");
  ?>

  <!-- STATS -->
  <div class="stat-row" style="margin-bottom:24px">
    <div class="stat-box"><div class="stat-box-val"><?= $ua_total ?></div><div class="stat-box-lbl">Total Records</div></div>
    <div class="stat-box" style="border-color:rgba(22,163,74,.3)"><div class="stat-box-val" style="color:#86efac"><?= $ua_agreed ?></div><div class="stat-box-lbl">Agreed ✓</div></div>
    <div class="stat-box" style="border-color:var(--gb)"><div class="stat-box-val"><?= $ua_pending ?></div><div class="stat-box-lbl">Pending</div></div>
    <div class="stat-box"><div class="stat-box-val"><?= $ua_users_with ?></div><div class="stat-box-lbl">Unique Users</div></div>
  </div>

  <!-- ADD AGREEMENT FORM -->
  <div style="background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;padding:24px;max-width:640px;margin-bottom:28px">
    <div style="font-family:'Cormorant Garamond',serif;font-size:18px;font-weight:700;color:var(--white);margin-bottom:16px;padding-bottom:10px;border-bottom:1px solid var(--border)">📝 Add Agreement Record</div>
    <form method="POST">
      <input type="hidden" name="add_agreement" value="1">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        <div>
          <label>User</label>
          <select name="ua_user_id" required>
            <option value="">— Select User —</option>
            <?php $ua_users_q = mysqli_query($conn,"SELECT id,fullname,email,role FROM users ORDER BY fullname ASC");
            while($uu = mysqli_fetch_assoc($ua_users_q)): ?>
            <option value="<?= $uu['id'] ?>"><?= htmlspecialchars($uu['fullname']) ?> (<?= htmlspecialchars($uu['email']) ?> — <?= $uu['role'] ?>)</option>
            <?php endwhile; ?>
          </select>
        </div>
        <div>
          <label>Property (Optional)</label>
          <select name="ua_property_id">
            <option value="">— No specific property —</option>
            <?php $ua_props_q = mysqli_query($conn,"SELECT id,property_name FROM properties ORDER BY property_name ASC");
            while($up = mysqli_fetch_assoc($ua_props_q)): ?>
            <option value="<?= $up['id'] ?>"><?= htmlspecialchars($up['property_name']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div>
          <label>Agreement Type</label>
          <select name="ua_type">
            <option value="lease_terms">Lease Terms</option>
            <option value="platform_terms">Platform Terms &amp; Conditions</option>
            <option value="privacy_policy">Privacy Policy</option>
            <option value="tenancy_agreement">Tenancy Agreement</option>
            <option value="other">Other</option>
          </select>
        </div>
        <div style="display:flex;align-items:center;gap:10px;padding-top:20px">
          <input type="checkbox" name="ua_agreed" id="ua_agreed_chk" style="width:auto;margin:0">
          <label for="ua_agreed_chk" style="font-size:12px;letter-spacing:0;text-transform:none;color:var(--white)">Mark as already agreed</label>
        </div>
      </div>
      <label style="margin-top:10px;display:block">Notes (optional)</label>
      <textarea name="ua_notes" rows="2" placeholder="Any relevant notes about this agreement..."></textarea>
      <button type="submit" class="action-btn" style="background:rgba(200,164,60,.3);border:1px solid var(--gb);width:100%;padding:12px;font-size:13px;margin-top:4px">+ Add Agreement Record</button>
    </form>
  </div>

  <!-- FILTER TABS -->
  <div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap">
    <?php foreach(['all'=>'All','agreed'=>'Agreed','pending'=>'Pending'] as $k=>$lbl):
      $act = $ua_filter===$k;
      $cnt = ($k==='agreed') ? $ua_agreed : (($k==='pending') ? $ua_pending : $ua_total);
    ?>
    <a href="admin_dashboard.php?page=agreed_users&filter=<?=$k?>" style="padding:7px 16px;border-radius:6px;font-size:12px;font-weight:600;text-decoration:none;background:<?=$act?'rgba(200,164,60,.2)':'rgba(255,255,255,.04)'?>;border:1px solid <?=$act?'var(--gb)':'var(--border)'?>;color:<?=$act?'var(--gold)':'var(--muted)'?>">
      <?=$lbl?> <span style="background:rgba(255,255,255,.1);border-radius:10px;padding:1px 7px;font-size:10px;margin-left:4px"><?=$cnt?></span>
    </a>
    <?php endforeach; ?>
  </div>

  <!-- AGREEMENTS TABLE -->
  <?php
  $ua_q = mysqli_query($conn,
    "SELECT a.*, u.fullname AS user_name, u.email AS user_email, u.role AS user_role,
            p.property_name
     FROM user_agreements a
     LEFT JOIN users u ON a.user_id = u.id
     LEFT JOIN properties p ON a.property_id = p.id
     $ua_where
     ORDER BY a.id DESC"
  );
  ?>

  <?php if(!$ua_q || mysqli_num_rows($ua_q)===0): ?>
  <div style="text-align:center;padding:48px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px">
    <div style="font-size:40px;margin-bottom:14px">📋</div>
    <div style="font-size:16px;color:var(--white);font-weight:600;margin-bottom:8px">No agreement records yet</div>
    <div style="font-size:13px;color:var(--muted)">Use the form above to add agreement records, or they will be created automatically when users accept terms on the platform.</div>
  </div>
  <?php else: ?>
  <table>
    <tr>
      <th>#</th>
      <th>User</th>
      <th>Role</th>
      <th>Property</th>
      <th>Agreement Type</th>
      <th>Status</th>
      <th>Agreed At</th>
      <th>IP Address</th>
      <th>Notes</th>
      <th>Actions</th>
    </tr>
    <?php $i=1; while($ua = mysqli_fetch_assoc($ua_q)):
      $agreed = (int)($ua['agreed'] ?? 0);
    ?>
    <tr style="<?= $agreed ? '' : 'background:rgba(200,164,60,.03)' ?>">
      <td style="color:var(--muted)"><?= $i++ ?></td>
      <td>
        <div style="font-weight:600"><?= htmlspecialchars($ua['user_name'] ?? 'Deleted User') ?></div>
        <div style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($ua['user_email'] ?? '—') ?></div>
      </td>
      <td>
        <span style="padding:2px 8px;border-radius:10px;font-size:10px;font-weight:700;text-transform:uppercase;
          background:<?= $ua['user_role']==='admin'?'rgba(239,68,68,.1)':($ua['user_role']==='propertyowner'?'rgba(200,164,60,.1)':'rgba(14,90,200,.1)') ?>;
          color:<?= $ua['user_role']==='admin'?'#fca5a5':($ua['user_role']==='propertyowner'?'var(--gold)':'#5b9cff') ?>;
          border:1px solid <?= $ua['user_role']==='admin'?'rgba(239,68,68,.3)':($ua['user_role']==='propertyowner'?'var(--gb)':'rgba(14,90,200,.3)') ?>">
          <?= htmlspecialchars($ua['user_role'] ?? '—') ?>
        </span>
      </td>
      <td style="font-size:12px"><?= htmlspecialchars($ua['property_name'] ?? '— (Platform-wide)') ?></td>
      <td style="font-size:12px;color:var(--muted)"><?= htmlspecialchars(ucwords(str_replace('_',' ',$ua['agreement_type'] ?? 'lease_terms'))) ?></td>
      <td>
        <?php if($agreed): ?>
          <span style="padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;background:rgba(22,163,74,.1);border:1px solid rgba(22,163,74,.3);color:#86efac">✓ Agreed</span>
        <?php else: ?>
          <span style="padding:3px 10px;border-radius:20px;font-size:10px;font-weight:700;background:rgba(200,164,60,.1);border:1px solid var(--gb);color:var(--gold)">⏳ Pending</span>
        <?php endif; ?>
      </td>
      <td style="font-size:12px;color:var(--muted)">
        <?= $ua['agreed_at'] ? date('d M Y, H:i', strtotime($ua['agreed_at'])) : '—' ?>
      </td>
      <td style="font-size:11px;color:var(--muted)"><?= htmlspecialchars($ua['ip_address'] ?? '—') ?></td>
      <td style="font-size:11px;color:var(--muted);max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap" title="<?= htmlspecialchars($ua['notes'] ?? '') ?>">
        <?= htmlspecialchars(substr($ua['notes'] ?? '—', 0, 50)) ?>
      </td>
      <td style="white-space:nowrap">
        <?php if(!$agreed): ?>
        <a href="admin_dashboard.php?page=agreed_users&mark_agreed=1&ua_id=<?= $ua['id'] ?>"
           class="action-btn" style="background:rgba(22,163,74,.2);border:1px solid rgba(22,163,74,.3);color:#86efac"
           onclick="return confirm('Mark this as agreed manually?')">✓ Mark Agreed</a>
        <?php endif; ?>
        <a href="admin_dashboard.php?page=agreed_users&delete_agreement=<?= $ua['id'] ?>"
           class="action-btn" style="background:rgba(239,68,68,.15);border:1px solid rgba(239,68,68,.3);color:#fca5a5"
           onclick="return confirm('Delete this agreement record?')">🗑 Delete</a>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
  <?php endif; ?>

  <!-- SUMMARY LEGEND -->
  <div style="margin-top:20px;padding:16px;background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:10px;font-size:12px;color:var(--muted);line-height:1.8">
    <strong style="color:var(--gold)">ℹ️ How user agreements work:</strong><br>
    • Records are created automatically when a user accepts terms on the platform (lease_apply.php, register.php, etc.).<br>
    • You can also add records manually using the form above for offline agreements.<br>
    • <strong style="color:var(--white)">Mark Agreed</strong> lets you manually confirm an agreement that was accepted outside the platform.<br>
    • <strong style="color:var(--white)">Property</strong> can be blank for platform-wide agreements (T&amp;Cs, privacy policy, etc.).
  </div>
</section>

<?php endif; ?>

</div>
</body>
</html>