<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

$userQ = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($userQ);

if (!$user || $user['role'] !== 'staff') {
    echo "<h2 style='color:red;text-align:center;'>Access Denied!</h2>";
    exit();
}

   // --- Quick Stats Queries ---

// Pending inspections
$pendingInspectionsQ = mysqli_query($conn, "SELECT COUNT(*) AS total FROM inspections WHERE status='Pending'");
$pendingInspections = mysqli_fetch_assoc($pendingInspectionsQ)['total'];

// Maintenance requests pending or in progress
$pendingMaintenanceQ = mysqli_query($conn, "SELECT COUNT(*) AS total FROM maintenance WHERE status='pending' OR status='in_progress'");
$pendingMaintenance = mysqli_fetch_assoc($pendingMaintenanceQ)['total'];

// Visitors scheduled today
$today = date('Y-m-d');
$visitorsTodayQ = mysqli_query($conn, "SELECT COUNT(*) AS total FROM guests WHERE DATE(check_in) = '$today'");
$visitorsToday = mysqli_fetch_assoc($visitorsTodayQ)['total'];

// Unread notifications
$unreadNotificationsQ = mysqli_query($conn, "SELECT COUNT(*) AS total FROM notifications WHERE user_id='$user_id' AND is_read=0");
$unreadNotifications = mysqli_fetch_assoc($unreadNotificationsQ)['total'];

// Payments due this week
$startOfWeek = date('Y-m-d', strtotime('monday this week'));
$endOfWeek = date('Y-m-d', strtotime('sunday this week'));
$paymentsDueQ = mysqli_query($conn, "SELECT COUNT(*) AS total FROM payments WHERE due_date BETWEEN '$startOfWeek' AND '$endOfWeek'");
$paymentsDue = mysqli_fetch_assoc($paymentsDueQ)['total'];


?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Dashboard</title>
    <style>
        body{
            font-family:Segoe UI;
            background:lightblue;
            padding:30px;
        }
        .header{
            background:#2563eb;
            color:white;
            padding:25px;
            border-radius:15px;
            text-align:center;
            border:3px solid #105ceb;
        }
        .grid{
            display:grid;
            grid-template-columns:repeat(auto-fit,minmax(250px,1fr));
            gap:20px;
            margin-top:30px;
        }
        .card{
            background:white;
            padding:20px;
            border-radius:15px;
            box-shadow:0 4px 10px rgba(245, 184, 18, 0.15);
            text-align:center;
            transition:0.3s;
            border:3px solid #105ceb;
        }
        .card:hover{
            transform:translateY(-5px);
        }
        .card h3{
            margin-bottom:10px;
            color:#1e293b;
        }
        .card a{
            display:inline-block;
            margin-top:10px;
            padding:10px 15px;
            background:#2563eb;
            color:white;
            border-radius:10px;
            text-decoration:none;
        }
        .circular-card {
    width: 150px;          /* adjust size */
    height: 150px;         /* adjust size */
    border-radius: 50%;    /* makes it circular */
    background: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    border: 3px solid #105ceb;
    box-shadow: 0 4px 10px rgba(0,0,0,0.15);
    margin: 10px;
    transition: transform 0.3s;
}

.circular-card:hover {
    transform: scale(1.05);
}

.circular-card h3 {
    margin: 5px 0;
    font-size: 16px;
}

.circular-card p {
    font-size: 24px;
    font-weight: bold;
    margin: 0;
}
    </style>
</head>

<body>

 <a href="dashboard.php" style="
    display:inline-block;
    margin-top:10px;
    ">
    ← PREVIOUS
     </a>
<br><br>
<div class="header" style="border:3px solid black; border-radius:70px;">
    <h1>WELCOME STAFF MEMBER:</h1><?php echo htmlspecialchars($user['fullname']); ?> </p>
    
</div>
<br>


<?php
// --- Fetch upcoming events for calendar ---
$calendarEvents = [];

// Upcoming Inspections
$inspections = mysqli_query($conn, "SELECT property_id, inspection_date, inspector_name FROM inspections WHERE inspection_date >= CURDATE() ORDER BY inspection_date ASC");
while ($i = mysqli_fetch_assoc($inspections)) {
    $calendarEvents[] = [
        'type' => 'Inspection',
        'date' => $i['inspection_date'],
        'info' => "Property ID: ".$i['property_id']." | Inspector: ".$i['inspector_name']
    ];
}

// Maintenance Tasks
$maintenance = mysqli_query($conn, "SELECT property_id, status, created_at FROM maintenance WHERE status='pending' OR status='in_progress' ORDER BY created_at ASC");
while ($m = mysqli_fetch_assoc($maintenance)) {
    $calendarEvents[] = [
        'type' => 'Maintenance',
        'date' => $m['created_at'],
        'info' => "Property ID: ".$m['property_id']." | Status: ".$m['status']
    ];
}

// Scheduled Visits
$visits = mysqli_query($conn, "SELECT property_id, visiting_tenant_id, check_in FROM guests WHERE check_in >= NOW() ORDER BY check_in ASC");
while ($v = mysqli_fetch_assoc($visits)) {
    $calendarEvents[] = [
        'type' => 'Visit',
        'date' => $v['check_in'],
        'info' => "Property ID: ".$v['property_id']." | Tenant ID: ".$v['visiting_tenant_id']
    ];
}
?>

<!-- Mini Calendar / Upcoming Events -->
<div class="calendar" style="background:white; border-radius:12px; padding:15px; margin-bottom:20px; border:3px solid #2563eb;">
    <h3 style="text-align:center; margin-bottom:10px;">Upcoming Events</h3>
    <?php if(empty($calendarEvents)): ?>
        <p style="text-align:center;">No upcoming events.</p>
    <?php else: ?>
        <?php foreach($calendarEvents as $event): ?>
            <div style="padding:8px; margin:5px 0; border-left:5px solid #2563eb; background:#f0f4ff; border-radius:5px;">
                <span style="font-weight:bold;"><?= htmlspecialchars($event['type']) ?></span> - <?= date("d M Y H:i", strtotime($event['date'])) ?><br>
                <?= htmlspecialchars($event['info']) ?>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
      
    <div class="grid" style="display:flex; gap:20px; flex-wrap: wrap; justify-content:center; margin-bottom:30px;">
    <div class="circular-card" style="border:3px solid orange;">
        <h3>Pending Inspections</h3>
        <p><?= $pendingInspections ?></p>
    </div>
    <div class="circular-card" style="border:3px solid red;">
        <h3>Pending / In-Progress Maintenance</h3>
        <p><?= $pendingMaintenance ?></p>
    </div>
    <div class="circular-card" style="border:3px solid green;">
        <h3>Visitors Today</h3>
        <p><?= $visitorsToday ?></p>
    </div>
    <div class="circular-card" style="border:3px solid blue;">
        <h3>Unread Notifications</h3>
        <p><?= $unreadNotifications ?></p>
    </div>
    <div class="circular-card" style="border:3px solid purple;">
        <h3>Payments Due This Week</h3>
        <p><?= $paymentsDue ?></p>
    </div>
</div>

<div class="grid">
    
    <div class="card">
    <h3>Task Manager</h3>
    <p>View and update your tasks</p>
    <a href="staff_tasks.php">Open</a>
</div>

     <div class="card">
    <h3>Tenant Profiles</h3>
    <p>View and manage tenant information</p>
    <a href="staff_tenants.php">Open</a>
</div>
     
   <div class="card">
    <h3>Payments</h3>
    <p>Check tenant payments and dues</p>
    <a href="staff_payments.php">Open</a>
</div>
      
     <div class="card">
    <h3>Property Inspections</h3>
    <p>Track and update inspection schedules</p>
    <a href="staff_inspections.php">Open</a>
</div>

    <div class="card">
        <h3>Maintenance Requests</h3>
        <p>View tenant repair requests</p>
        <a href="staff_maintenance.php">Open</a>
    </div>

    <div class="card">
        <h3>Guest Approvals</h3>
        <p>Monitor visitor check-ins</p>
        <a href="staff_guests.php">Open</a>
    </div>

    <div class="card">
        <h3>Notifications</h3>
        <p>See new tasks assigned</p>
        <a href="staff_notifications.php">Open</a>
    </div>
     
     
    <div class="card">
    <h3>Schedule</h3>
    <p>Check upcoming appointments and events</p>
    <a href="staff_schedule.php">Open</a>
</div>

    <div class="card">
    <h3>Reports</h3>
    <p>Generate activity and maintenance reports</p>
    <a href="staff_reports.php">Open</a>
</div>
   
<div class="card" style="border:3px solid #2563eb; background:white;">
    <h3>Staff Guidelines / Rules</h3>
    <ul style="line-height:1.6; padding-left:20px;">
        <li>Always update tenant and property information accurately.</li>
        <li>Respond to maintenance requests within 24 hours.</li>
        <li>Keep inspection records up-to-date.</li>
        <li>Notify management of any unusual activity on properties.</li>
        <li>Respect tenant privacy and confidentiality at all times.</li>
        <li>Log out after completing your tasks to secure your account.</li>
    </ul>
</div>
  <div class="card" style="border:3px solid #2563eb; background:white;">
    <h3>Payment & Payroll Information</h3>
    <ul style="line-height:1.6; padding-left:20px;">
        <li>Staff salaries are processed on the last day of each month.</li>
        <li>Ensure all your task updates and reports are submitted before payroll cut-off.</li>
        <li>Overtime or bonuses will be reflected in your monthly pay where applicable.</li>
        <li>For any payment disatisfaction, contact HR or management immediately.</li>
    </ul>
</div>
    <div class="card">
        <h3>Logout</h3>
        <p>Exit Staff Portal</p>
        <a href="logout.php" style="background:red;">Logout</a>
    </div>

</div>


</body>
</html>