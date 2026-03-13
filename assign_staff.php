<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

/* ==========================================
   GET REQUEST ID
========================================== */
$request_id = intval($_GET['id'] ?? 0);

if ($request_id <= 0) {
    die("Invalid request!");
}

/* ==========================================
   ASSIGN STAFF FORM SUBMISSION
========================================== */
if (isset($_POST['assign'])) {

    $staff_id = intval($_POST['staff_id']);

    mysqli_query($conn, "
        UPDATE maintenance_requests
        SET assigned_staff = $staff_id,
            status = 'In Progress'
        WHERE id = $request_id
    ");

    header("Location: admin_dashboard.php?page=maintenance");
    exit();
}

/* ==========================================
   FETCH REQUEST DETAILS
========================================== */
$request = mysqli_fetch_assoc(mysqli_query($conn, "
    SELECT m.*, p.property_name, t.fullname AS tenant_name
    FROM maintenance_requests m
    JOIN properties p ON m.property_id = p.id
    JOIN tenants t ON m.tenant_id = t.id
    WHERE m.id = $request_id
"));

if (!$request) {
    die("Maintenance request not found!");
}

/* ==========================================
   FETCH STAFF LIST
========================================== */
$staff = mysqli_query($conn, "
    SELECT id, fullname, role
    FROM users
    WHERE role='staff'
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Staff</title>
    <style>
        body {
            font-family: Segoe UI;
            background: #f4f7fb;
            padding: 30px;
        }

        form {
            width: 520px;
            margin: auto;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        h2 {
            text-align: center;
            color: #0ea5e9;
        }

        select, button {
            width: 100%;
            padding: 12px;
            margin: 12px 0;
            border-radius: 8px;
            font-size: 15px;
        }

        button {
            background: #0ea5e9;
            border: none;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background: #0284c7;
        }

        .info {
            background: #f1f5f9;
            padding: 12px;
            border-radius: 8px;
            margin-bottom: 15px;
        }
    </style>
</head>

<body>

<form method="POST">

    <h2>Assign Staff Member</h2>

    <div class="info">
        <p><b>Property:</b> <?= $request['property_name'] ?></p>
        <p><b>Tenant:</b> <?= $request['tenant_name'] ?></p>
        <p><b>Issue:</b> <?= $request['issue'] ?></p>
        <p><b>Status:</b> <?= $request['status'] ?></p>
    </div>

    <label>Select Staff Member:</label>

    <select name="staff_id" required>
        <option value="">-- Choose Staff --</option>

        <?php while ($s = mysqli_fetch_assoc($staff)) { ?>
            <option value="<?= $s['id'] ?>">
                <?= $s['fullname'] ?> (<?= $s['role'] ?>)
            </option>
        <?php } ?>
    </select>

    <button type="submit" name="assign">Assign Staff</button>

</form>

</body>
</html>

