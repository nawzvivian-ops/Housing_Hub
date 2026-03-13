<?php
session_start();
include "db_connect.php";

/*  Staff Login Check */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

/* confirm Staff Role */
$userQ = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($userQ);

if (!$user || strtolower($user['role']) !== 'staff') {
    echo "<h2 style='color:red;text-align:center;'>Access Denied!</h2>";
    exit();
}

/* Update Request Status */
if (isset($_GET['update'])) {
    $id = intval($_GET['update']);
    $new_status = $_GET['status'];

    mysqli_query($conn, "
        UPDATE maintenance_requests 
        SET status='$new_status'
        WHERE id='$id'
    ");

    header("Location: staff_maintenance.php");
    exit();
}

/*  Delete Request */
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    mysqli_query($conn, "DELETE FROM maintenance_requests WHERE id='$id'");

    header("Location: staff_maintenance.php");
    exit();
}

/* Fetch Maintenance Requests */
$requests = mysqli_query($conn, "
    SELECT m.*, p.property_name, u.fullname AS staff_name
    FROM maintenance_requests m
    LEFT JOIN properties p ON m.property_id = p.id
    LEFT JOIN users u ON m.assigned_staff = u.id
    ORDER BY m.created_at DESC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Maintenance Requests</title>
    <style>
        body{
            font-family:Segoe UI;
            background:lightblue;
            padding:30px;
        }
        h1{
            text-align:center;
            color:#2563eb;
        }
        table{
            width:100%;
            border-collapse:collapse;
            margin-top:20px;
            background:white;
            border-radius:10px;
            overflow:hidden;
        }
        th,td{
            padding:12px;
            border-bottom:1px solid #111010;
            text-align:left;
        }
        th{
            background:blue;
            color:white;
        }

        a.btn{
            padding:6px 12px;
            border-radius:8px;
            text-decoration:none;
            color:white;
            font-size:13px;
        }
        .pending{background:orange;}
        .progress{background:blue;}
        .done{background:green;}
        .delete{background:red;}
    </style>
</head>
<body>
    <a href="staff_dashboard.php">← PREVIOUS</a>
    <br><br>
     <div class="card"style="border:3px solid black; padding: 40px; width: 40%; border-radius:40px; background:white;">
        <h2 style="font-family: Times New Roman, Times, serif;"><img src="images/maintenance.png" alt="Photo" width="70px" height="80px">MAINTENANCE REQUESTS<img src="images/maintenance.png" alt="Photo" width="70px" height="80px"></h2>
        
    </div>
 <hr>
 

<table>
<tr>
    <th>#</th>
    <th>Property</th>
    <th>Issue</th>
    <th>Priority</th>
    <th>Assigned Staff</th>
    <th>Status</th>
    <th>Actions</th>
</tr>

<?php $i=1; while($r=mysqli_fetch_assoc($requests)): ?>
<tr>
    <td><?= $i++ ?></td>

    <td><?= htmlspecialchars($r['property_name'] ?? "Unknown") ?></td>

    <td><?= htmlspecialchars($r['issue']) ?></td>

    <td><b><?= strtoupper($r['priority']) ?></b></td>

    <td><?= htmlspecialchars($r['staff_name'] ?? "Not Assigned") ?></td>

    <td><b><?= strtoupper($r['status']) ?></b></td>

    <td>
        <a class="btn pending"
           href="?update=<?= $r['id'] ?>&status=pending">Pending</a>

        <a class="btn progress"
           href="?update=<?= $r['id'] ?>&status=in_progress">In Progress</a>

        <a class="btn done"
           href="?update=<?= $r['id'] ?>&status=completed">Completed</a>

        <a class="btn delete"
           href="?delete=<?= $r['id'] ?>"
           onclick="return confirm('Delete this request?')">
           Delete
        </a>
    </td>
</tr>
<?php endwhile; ?>

</table>

</body>
</html>

