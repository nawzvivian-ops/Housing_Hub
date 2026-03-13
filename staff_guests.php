<?php
session_start();
include "db_connect.php";

# --- 1. Check staff login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);
$userQ = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($userQ);

if (!$user || strtolower($user['role']) !== 'staff') {
    echo "<h2 style='color:red;text-align:center;'>Access Denied!</h2>";
    exit();
}

# --- 2. Handle Approve / Reject Actions
if (isset($_GET['action']) && isset($_GET['id'])) {

    $guest_id = intval($_GET['id']);
    $action   = $_GET['action'];

    if ($action == "approve") {
        $new_status = "Approved";
    } elseif ($action == "reject") {
        $new_status = "Rejected";
    } else {
        $new_status = "Pending";
    }

    # Update guest request status
    mysqli_query($conn, "
        UPDATE guests 
        SET status='$new_status'
        WHERE id='$guest_id'
    ");

    # --- Notify tenant after approval/rejection
    $guestData = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT * FROM guests WHERE id='$guest_id'
    "));

    if ($guestData && $guestData['visiting_tenant_id'] > 0) {

        $tenant_id = $guestData['visiting_tenant_id'];
        $guest_name = $guestData['fullname'];

        $title = "Guest Request Update";
        $message = "Your visitor request from $guest_name has been $new_status.";

        mysqli_query($conn, "
            INSERT INTO notifications (tenant_id, title, message)
            VALUES ('$tenant_id', '$title', '$message')
        ");
    }

    header("Location: staff_guests.php");
    exit();
}

# --- 3. Fetch Guest Requests
$requests = mysqli_query($conn, "
    SELECT g.*, 
           t.fullname AS tenant_name,
           p.property_name
    FROM guests g
    LEFT JOIN tenants t ON g.visiting_tenant_id = t.id
    LEFT JOIN properties p ON g.property_id = p.id
    ORDER BY g.id DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Guest Approvals</title>
    <style>
        body{
            font-family:Segoe UI;
            background:lightblue;
            padding:30px;
        }
        h1{
            color:black;
        }
        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
            background:white;
            border-radius:10px;
            overflow:hidden;
            
        }
        th, td{
            padding:12px;
            border-bottom:1px solid #1a1919;
            text-align:left;
        }
        th{
            background:#2563eb;
            color:white;
        }
        .status-pending{color:orange;font-weight:bold;}
        .status-approved{color:green;font-weight:bold;}
        .status-rejected{color:red;font-weight:bold;}

        .btn{
            padding:7px 12px;
            border-radius:8px;
            text-decoration:none;
            color:white;
            font-size:14px;
        }
        .approve{background:green;}
        .reject{background:red;}
    </style>
</head>

<body>
<a href="staff_dashboard.php"style="color:blue;">---PREVIOUS</a>
<h1>Guest Approval Requests</h1>


<table>
    <tr>
        <th>#</th>
        <th>Guest Name</th>
        <th>Phone</th>
        <th>Email</th>
        <th>Tenant Visited</th>
        <th>Property</th>
        <th>Visit Type</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>

    <?php $i=1; while($g = mysqli_fetch_assoc($requests)): ?>
    <tr>
        <td><?= $i++ ?></td>
        <td><?= htmlspecialchars($g['fullname']) ?></td>
        <td><?= htmlspecialchars($g['phone']) ?></td>
        <td><?= htmlspecialchars($g['email']) ?></td>
        <td><?= htmlspecialchars($g['tenant_name'] ?? '-') ?></td>
        <td><?= htmlspecialchars($g['property_name'] ?? '-') ?></td>
        <td><?= htmlspecialchars($g['visit_type']) ?></td>

        <td class="
            <?php
                if($g['status']=="Pending") echo "status-pending";
                if($g['status']=="Approved") echo "status-approved";
                if($g['status']=="Rejected") echo "status-rejected";
            ?>">
            <?= htmlspecialchars($g['status']) ?>
        </td>

        <td>
            <?php if($g['status']=="Pending"): ?>
                <a class="btn approve" href="?action=approve&id=<?= $g['id'] ?>">Approve</a>
                <a class="btn reject" href="?action=reject&id=<?= $g['id'] ?>">Reject</a>
            <?php else: ?>
                Done
            <?php endif; ?>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>

