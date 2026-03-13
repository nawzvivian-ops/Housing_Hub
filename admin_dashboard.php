<?php
session_start();
include "db_connect.php";

// Redirect if not logged in or not admin
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($result);

$role = strtolower(trim($user['role'])); // normalize role

if ($role !== 'admin') {
    // Not an admin → send to user dashboard
    header("Location: dashboard.php");
    exit();
}

// Fetch statistics for dashboard
// ---- Extra Admin Overview Stats ----

// Total Brokers
$total_brokers = mysqli_fetch_assoc(mysqli_query(
    $conn, "SELECT COUNT(*) as count FROM users WHERE role='broker'"
))['count'];

// Total Property Owners
$total_owners = mysqli_fetch_assoc(mysqli_query(
    $conn, "SELECT COUNT(*) as count FROM users WHERE role='owner'"
))['count'];

// Total Guests
$total_guests = mysqli_fetch_assoc(mysqli_query(
    $conn, "SELECT COUNT(*) as count FROM guests"
))['count'];

// Total Complaints Pending
$total_complaints = mysqli_fetch_assoc(mysqli_query(
    $conn, "SELECT COUNT(*) as count FROM complaints WHERE status='pending'"
))['count'];

// Unread Notifications
$total_notifications = mysqli_fetch_assoc(mysqli_query(
    $conn, "SELECT COUNT(*) as count FROM notifications WHERE is_read=0"
))['count'];

// Pending Payments
$pending_payments = mysqli_fetch_assoc(mysqli_query(
    $conn, "SELECT COUNT(*) as count FROM payments WHERE status='pending'"
))['count'];

$total_properties = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties"))['count'];
$total_tenants = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tenants"))['count'];
$total_staff = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='staff'"))['count'];
$pending_applications = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM job_applications WHERE status='pending'"))['count'];
$pending_requests = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM maintenance_requests WHERE status='pending'"))['count'];
$revenue = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(amount) as total FROM payments"))['total'];

// Determine which page to show
$page = $_GET['page'] ?? 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Dashboard | HousingHub</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
/* General styling */
body { font-family: "Segoe UI", sans-serif; margin:0; padding:0; background: #dde8f7; color: #333; }

/* Sidebar */
.sidebar {position: fixed; left: 0; top: 0;  width: 250px; height: 100%; background: #020816; color: white; display: flex; flex-direction: column; padding-top: 20px; overflow-y: auto; z-index: 1000;}
.sidebar h2 {text-align: center; margin-bottom: 20px;color: #38bdf8;}
.sidebar a { color: white; padding: 15px 20px;  text-decoration: none; display: block; transition: all 0.3s;}
.sidebar a:hover, .sidebar a.active {background: #38bdf8; color: #0f172a;}

/* Header */
.header { 
    display: flex; justify-content: space-between; align-items: center;background: #020816; color: #fff;padding: 15px 30px; position: sticky;top: 0; z-index: 100; margin-left: 250px;box-shadow: 0 2px 10px rgba(0,0,0,0.1);}
.header h1 { font-size: 24px; color: #38bdf8;}
.logout-btn { color: #fff; text-decoration: none; background: #dc2626; padding: 10px 20px; border-radius: 6px; transition: background 0.3s;}
.logout-btn:hover {background: #b91c1c; }

/* Main content */
.main-content { margin-left: 250px; padding: 30px 40px; min-height: calc(100vh - 80px);}

/* Overview cards */
.cards { 
    display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));  gap: 20px;  margin-bottom: 40px; }
.card { 
    background: linear-gradient(135deg, #38bdf8, #0ea5e9);color: white;padding: 25px;  border-radius: 15px;  text-align: center;  box-shadow: 0 5px 15px rgba(56, 189, 248, 0.3); transition: transform 0.3s, box-shadow 0.3s;}
.card:hover { transform: translateY(-5px);box-shadow: 0 8px 25px rgba(56, 189, 248, 0.4);}
.card h3 {margin-bottom: 10px;  font-size: 16px;font-weight: 500; opacity: 0.9;}
.card p {font-size: 32px;  font-weight: bold;}

/* Section headers */
section h2 { margin-bottom: 20px;color: #0f172a;font-size: 28px;border-bottom: 3px solid #38bdf8;padding-bottom: 10px;}

/* Tables */
table {  width: 100%;  border: 3px solid #0c0c0c;border-collapse: collapse;  margin-bottom: 40px;  background: white; border-radius: 0px; overflow: hidden;box-shadow: 0 4px 15px rgba(99, 111, 224, 0.08)}
table th, table td {padding: 15px;  text-align: left;}
table th {background: #0f172a; color: #fff; font-weight: 600;}
table tr:nth-child(even) { background: #87b4df;}
table tr:hover {background: #87b4df;}

.action-btn {display: inline-block;padding: 8px 16px;  border-radius: 6px;  text-decoration: none; color: white;  background: #366d21; transition: 0.3s; margin-right: 5px;font-size: 14px;}
.action-btn:hover {background: #366d21; transform: translateY(-2px);}

/* Charts placeholder */
.chart-container { margin-bottom: 40px; background: white;padding: 30px;border-radius: 10px;box-shadow: 0 4px 15px rgba(0,0,0,0.08);}

/* Responsive */
@media (max-width: 768px) {
    .sidebar { 
        width: 100%; 
        height: auto; 
        position: relative; 
    }
    .main-content { 
        margin-left: 0; 
    }
    .header { 
        margin-left: 0; 
        flex-direction: column;
        gap: 10px;
        padding: 15px;
    }
    .cards {
        grid-template-columns: 1fr;
    }
    table {
        font-size: 14px;
    }
    table th, table td {
        padding: 10px;
    }
}
    .overview-cards {
    display: flex;
    flex-wrap: wrap;         /* allow wrapping on small screens */
    gap: 20px;               /* spacing between cards */
    justify-content: center; /* center the cards */
    margin-bottom: 40px;
}

.circular-card {
    width: 150px;            /* circle size */
    height: 150px;
    border-radius: 50%;      /* makes it circular */
    background: linear-gradient(135deg, #38bdf8, #0ea5e9);
    color: white;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    text-align: center;
    box-shadow: 0 8px 10px rgba(17, 16, 16, 0.97);
    transition: transform 0.3s;
}

.circular-card:hover {
    transform: scale(1.05);
}

.circular-card h3 {
    margin: 5px 0;
    font-size: 14px;
    font-weight: 500;
    opacity: 0.9;
}

.circular-card p {
    font-size: 24px;
    font-weight: bold;
    margin: 0;
}
</style>
</head>
<body>

   <div class="sidebar">
    <h2>ADMIN PANEL</h2>

    <!-- Dashboard -->
    <a href="admin_dashboard.php?page=dashboard" <?php echo ($page === 'dashboard') ? 'class="active"' : ''; ?>> HOME</a>
    
    <!-- Users -->
    <a href="admin_dashboard.php?page=users" <?php echo ($page === 'users') ? 'class="active"' : ''; ?>> Manage Users</a>
    
    <!-- Properties -->
    <a href="admin_dashboard.php?page=properties" <?php echo ($page === 'properties') ? 'class="active"' : ''; ?>>Manage Properties</a>
     
    <!--  Staff -->
     <a href="admin_dashboard.php?page=staff_roles" <?php echo ($page === 'staff_roles') ? 'class="active"' : ''; ?>> Staff Roles & Payroll</a>
    <a href="admin_dashboard.php?page=staff_tasks" <?php echo ($page === 'staff_tasks') ? 'class="active"' : ''; ?>> Staff Tasks / Schedule</a>
    
    <a href="admin_dashboard.php?page=inspections" <?php echo ($page === 'inspections') ? 'class="active"' : ''; ?>> Property Inspections</a>
    <a href="admin_dashboard.php?page=maintenance" <?php echo ($page === 'maintenance') ? 'class="active"' : ''; ?>> Maintenance Requests</a>
    
    
    <!-- Tenants -->
    <a href="admin_dashboard.php?page=tenants" <?php echo ($page === 'tenants') ? 'class="active"' : ''; ?>>Manage Tenants</a>
    <a href="admin_dashboard.php?page=tenant_documents" <?php echo ($page === 'tenant_documents') ? 'class="active"' : ''; ?>> Tenant Documents</a>
    <a href="admin_dashboard.php?page=tenant_payments" <?php echo ($page === 'tenant_payments') ? 'class="active"' : ''; ?>> Tenant Payments</a>
    <a href="admin_dashboard.php?page=complaints" <?php echo ($page === 'complaints') ? 'class="active"' : ''; ?>>Complaints & Feedback</a>

     <!--  Guest Management -->
    <a href="admin_dashboard.php?page=guests"<?php echo ($page === 'guests') ? 'class="active"' : ''; ?>>Guest / Visitor Approvals</a>

    <!--  Brokers -->
    <a href="admin_dashboard.php?page=brokers"<?php echo ($page === 'brokers') ? 'class="active"' : ''; ?>> Brokers / Agents</a>

    <!--  Property Owners -->
    <a href="admin_dashboard.php?page=propertyowners"<?php echo ($page === 'propertyowners') ? 'class="active"' : ''; ?>> Property Owners</a>

    <!-- Employment -->
    <a href="admin_dashboard.php?page=jobs" <?php echo ($page === 'jobs') ? 'class="active"' : ''; ?>> Employment Applications</a>
    <a href="admin_dashboard.php?page=employee_performance" <?php echo ($page === 'employee_performance') ? 'class="active"' : ''; ?>> Employee Performance</a>
    
    <!-- Financials -->
    <a href="admin_dashboard.php?page=payments" <?php echo ($page === 'payments') ? 'class="active"' : ''; ?>> Payments / Rent Tracking</a>
    <a href="admin_dashboard.php?page=revenue_reports" <?php echo ($page === 'revenue_reports') ? 'class="active"' : ''; ?>> Revenue Reports</a>
    
    <!-- System / Settings -->
    <a href="admin_dashboard.php?page=settings" <?php echo ($page === 'settings') ? 'class="active"' : ''; ?>> System Settings</a>
    <a href="admin_dashboard.php?page=notifications" <?php echo ($page === 'notifications') ? 'class="active"' : ''; ?>> Notifications</a>
    <a href="admin_dashboard.php?page=backups" <?php echo ($page === 'backups') ? 'class="active"' : ''; ?>>Backup / Export Data</a>
</div>



<div class="header">
    <h1>Welcome, <?php echo htmlspecialchars($user['fullname']); ?></h1>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

<div class="main-content">

<?php if($page === 'dashboard'): ?>
    <section id="dashboard">
    <h2 style="text-align:center; margin-bottom:30px;">OVERVIEW</h2>
    <div class="overview-cards">
        <div class="circular-card">
            <h3>Total Properties</h3>
            <p><?php echo $total_properties; ?></p>
        </div>
        <div class="circular-card">
            <h3>Total Tenants</h3>
            <p><?php echo $total_tenants; ?></p>
        </div>
          <div class="circular-card">
    <h3>Total Staff</h3>
    <p><?php echo $total_staff; ?></p>
</div>

<div class="circular-card">
    <h3>Total Brokers</h3>
    <p><?php echo $total_brokers; ?></p>
</div>

<div class="circular-card">
    <h3>Property Owners</h3>
    <p><?php echo $total_owners; ?></p>
</div>

<div class="circular-card">
    <h3>Total Guests</h3>
    <p><?php echo $total_guests; ?></p>
</div>

<div class="circular-card">
    <h3>Pending Complaints</h3>
    <p><?php echo $total_complaints; ?></p>
</div>

<div class="circular-card">
    <h3>Unread Alerts</h3>
    <p><?php echo $total_notifications; ?></p>
</div>

<div class="circular-card">
    <h3>Pending Payments</h3>
    <p><?php echo $pending_payments; ?></p>
</div>
        
        <div class="circular-card">
            <h3>Pending Applications</h3>
            <p><?php echo $pending_applications; ?></p>
        </div>
        <div class="circular-card">
            <h3>Pending Maintenance</h3>
            <p><?php echo $pending_requests; ?></p>
        </div>
        <div class="circular-card">
            <h3>Revenue Collected</h3>
            <p>UGX <?php echo number_format($revenue ?? 0); ?></p>
        </div>
    </div>
</section>
 <hr style="color:#38bdf8; size:90px;">
     <?php elseif($page === 'users'): ?>
<section id="users">
    <h2 style="text-align:center; margin-bottom:30px; color:#366d21;">USER MANAGEMENT</h2>

    <!-- Add new user button -->
    <div style="text-align:center; margin-bottom:20px;">
        <a href="add_user.php" class="action-btn" style="background: #0ea5e9;">+++ADD NEW USER</a>
    </div>

    <table>
        <tr>
            <th>Fullname</th>
            <th>Role</th>
            <th>Email</th>
            <th>Actions</th>
        </tr>
        <?php
        $users = mysqli_query($conn, "SELECT * FROM users ORDER BY created_at DESC");
        while ($u = mysqli_fetch_assoc($users)) {
            echo "<tr>
                    <td>".htmlspecialchars($u['fullname'])."</td>
                    <td>".htmlspecialchars($u['role'])."</td>
                    <td>".htmlspecialchars($u['email'])."</td>
                    <td>
                        <a href='edit_user.php?id=".$u['id']."' class='action-btn'>Edit</a>
                        <a href='delete_user.php?id=".$u['id']."' class='action-btn' 
                           onclick=\"return confirm('Are you sure you want to delete this user?')\" 
                           style='background:#ef4444;'>Delete</a>
                    </td>
                  </tr>";
        }
        ?>
    </table>
</section>

<?php elseif($page === 'properties'): ?>
    <section id="properties">
        <h2>Manage Properties</h2>
        <table>
            <tr>
                <th>Property Name</th>
                <th>Type</th>
                <th>Address</th>
                <th>Units</th>
                <th>Rent (UGX)</th>
                <th>propertyowner / Broker</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            <?php
            $properties = mysqli_query($conn, "
                SELECT 
                    p.id,
                    p.property_name,
                    p.property_type,
                    p.address,
                    p.units,
                    p.rent_amount,
                    p.created_at,
                    u.fullname
                FROM properties p
                LEFT JOIN users u ON p.owner_id = u.id
                ORDER BY p.created_at DESC
            ");

            while ($prop = mysqli_fetch_assoc($properties)) {
            ?>
            <tr>
                <td><?= htmlspecialchars($prop['property_name']) ?></td>
                <td><?= htmlspecialchars($prop['property_type'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($prop['address'] ?? 'N/A') ?></td>
                <td><?= (int)$prop['units'] ?></td>
                <td><?= number_format($prop['rent_amount'] ?? 0) ?></td>
                <td><?= htmlspecialchars($prop['fullname'] ?? 'Unassigned') ?></td>
                <td><?= htmlspecialchars($prop['created_at']) ?></td>
                <td>
                    <a href="edit_records.php?type=property&id=<?= $prop['id'] ?>" class="action-btn">Edit</a>
                    <br>
                     <a href="delete_record.php?table=properties&id=<?= $prop['id'] ?>"
   class="action-btn" onclick="return confirm('Delete this property?')">Delete</a>
                </td>
            </tr>
            <?php } ?>
        </table>
    </section>
     
    <?php elseif($page === 'staff_roles'): ?>
<section id="staff_roles">
    <h2>Staff Roles & Payroll</h2>
    <table>
        <tr>
            <th>Full Name</th>
            <th>Role / Position</th>
            <th>Salary (UGX)</th>
            <th>Email</th>
            <th>Created At</th>
            <th>Actions</th>
        </tr>
        <?php
        // Fetch all staff users
        $staff = mysqli_query($conn, "SELECT * FROM users WHERE role='staff' ORDER BY created_at DESC");

        while ($s = mysqli_fetch_assoc($staff)) {
            // Fallbacks for empty fields
            $fullname = htmlspecialchars($s['fullname'] ?? 'N/A');
            $role = htmlspecialchars($s['role'] ?? 'Staff');
            $salary = number_format($s['salary'] ?? 0);
            $email = htmlspecialchars($s['email'] ?? 'N/A');
            $created_at = htmlspecialchars($s['created_at'] ?? 'N/A');
        ?>
        <tr>
            <td><?= $fullname ?></td>
            <td><?= $role ?></td>
            <td><?= $salary ?></td>
            <td><?= $email ?></td>
            <td><?= $created_at ?></td>
            <td>
                <a href="edit_records.php?type=staff&id=<?= $s['id'] ?>" class="action-btn">Edit</a>
                <a href="delete_record.php?table=users&id=<?= $s['id'] ?>" class="action-btn" 
                   onclick="return confirm('Delete this staff member?')" style="background:#ef4444;">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>

    <!-- Option to add new staff -->
    <div style="text-align:center; margin-top:20px;">
        <a href="add_staff.php" class="action-btn" style="background: #0ea5e9;">+++ADD NEW STAFF</a>
    </div>
</section>
    
  <?php elseif($page === 'inspections'): ?>
<section id="inspections">
    <h2 style="text-align:center;">🏠 Property Inspections</h2>

    <!-- Add New Inspection Button -->
    <div style="text-align:center; margin-bottom:20px;">
        <a href="add_inspection.php" class="action-btn" style="background:#0ea5e9;">
            + Schedule New Inspection
        </a>
    </div>

    <table>
        <tr>
            <th>Property</th>
            <th>Tenant</th>
            <th>Inspector</th>
            <th>Date</th>
            <th>Situation</th>
            <th>Status</th>
            <th>Notified</th>
            <th>Actions</th>
        </tr>

        <?php
        $inspections = mysqli_query($conn, "
            SELECT i.*, 
                   p.property_name,
                   t.fullname AS tenant_name
            FROM inspections i
            LEFT JOIN properties p ON i.property_id = p.id
            LEFT JOIN tenants t ON i.tenant_id = t.id
            ORDER BY i.inspection_date DESC
        ");

        while($i = mysqli_fetch_assoc($inspections)):
        ?>
        <tr>
            <td><?= htmlspecialchars($i['property_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($i['tenant_name'] ?? 'None') ?></td>
            <td><?= htmlspecialchars($i['inspector_name']) ?></td>
            <td><?= htmlspecialchars($i['inspection_date']) ?></td>
            <td><?= htmlspecialchars($i['situation']) ?></td>
            <td><?= htmlspecialchars($i['status']) ?></td>
            <td><?= ($i['notified'] == 1) ? "Yes" : "No" ?></td>

            <td>
                <a href="edit_records.php?type=inspection&id=<?= $i['id'] ?>"
                   class="action-btn">Edit</a>

                <a href="delete_record.php?table=inspections&id=<?= $i['id'] ?>"
                   class="action-btn"
                   onclick="return confirm('Delete this inspection?')"
                   style="background:#ef4444;">
                   Delete
                </a>

                <?php if($i['status'] != "Completed"): ?>
                    <a href="mark_inspection_complete.php?id=<?= $i['id'] ?>"
                       class="action-btn"
                       style="background:#16a34a;">
                       Complete
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</section>


  <?php elseif($page === 'staff_tasks'): ?>
<section id="staff_tasks">
    <h2 style="text-align:center;">Staff Tasks & Schedule</h2>

    <!-- Add New Task Button -->
    <div style="text-align:center; margin-bottom:20px;">
        <a href="add_task.php" class="action-btn" style="background:#0ea5e9;">
            + Assign New Task
        </a>
    </div>

    <table>
        <tr>
            <th>Task Title</th>
            <th>Staff Assigned</th>
            <th>Due Date</th>
            <th>Priority</th>
            <th>Status</th>
            <th>Assigned By</th>
            <th>Actions</th>
        </tr>

        <?php
        $tasks = mysqli_query($conn, "
            SELECT t.*, u.fullname AS staff_name
            FROM tasks t
            LEFT JOIN users u ON t.assigned_to = u.id
            ORDER BY t.due_date ASC
        ");

        while($task = mysqli_fetch_assoc($tasks)) {
        ?>
        <tr>
            <td><?= htmlspecialchars($task['title']) ?></td>
            <td><?= htmlspecialchars($task['staff_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($task['due_date'] ?? '-') ?></td>
            <td><?= htmlspecialchars($task['priority']) ?></td>
            <td><?= htmlspecialchars($task['status']) ?></td>
            <td><?= htmlspecialchars($task['assigned_by']) ?></td>

            <td>
                <a href="edit_records.php?type=task&id=<?= $task['id'] ?>" class="action-btn">
                    Edit
                </a>

                <a href="delete_record.php?table=tasks&id=<?= $task['id'] ?>"
                   class="action-btn"
                   onclick="return confirm('Delete this task?')"
                   style="background:#ef4444;">
                   Delete
                </a>

                <?php if($task['status'] != 'Completed'): ?>
                    <a href="mark_task_complete.php?id=<?= $task['id'] ?>"
                       class="action-btn"
                       style="background:#16a34a;">
                       Complete
                    </a>
                <?php endif; ?>
            </td>
        </tr>
        <?php } ?>
    </table>
</section>
 
<?php elseif($page === 'tenants'): ?>
<section id="tenants">
    <h2 style="text-align:center; margin-bottom:30px; color:#366d21;">MANAGE TENANTS</h2>

    <!-- Add new tenant button -->
    <div style="text-align:center; margin-bottom:20px;">
        <a href="add_tenant.php" class="action-btn" style="background: #0ea5e9;">+++ ADD NEW TENANT</a>
    </div>

    <!-- Tenants Table -->
    <?php
    $tenants = mysqli_query($conn, "
        SELECT t.*, p.property_name
        FROM tenants t
        LEFT JOIN properties p ON t.property_id = p.id
        ORDER BY t.created_at DESC
    ");
    ?>

    <table>
        <tr>
            <th>Fullname</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Property</th>
            <th>Actions</th>
        </tr>
        <?php while($tenant = mysqli_fetch_assoc($tenants)): ?>
        <tr>
            <td><?= htmlspecialchars($tenant['fullname']) ?></td>
            <td><?= htmlspecialchars($tenant['phone'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($tenant['email'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($tenant['property_name'] ?? 'Unassigned') ?></td>
            <td>
                <a href="edit_records.php?type=tenant&id=<?= $tenant['id'] ?>" class="action-btn">Edit</a>
                <a href="delete_record.php?table=tenants&id=<?= $tenant['id'] ?>" 
                   class="action-btn" style="background:#ef4444;"
                   onclick="return confirm('Are you sure you want to delete this tenant?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</section>

   <?php elseif($page === 'tenant_documents'): ?>
<section id="tenant_documents">
    <h2 style="text-align:center; margin-bottom:30px; color:#366d21;">TENANT DOCUMENTS</h2>

    <!-- Add New Document Button -->
    <div style="text-align:center; margin-bottom:20px;">
        <a href="add_document.php" class="action-btn" style="background: #0ea5e9;">+++ ADD NEW DOCUMENT</a>
    </div>

    <!-- Documents Table -->
    <table>
        <tr>
            <th>Tenant</th>
            <th>Document Name</th>
            <th>File</th>
            <th>Uploaded At</th>
            <th>Actions</th>
        </tr>
        <?php
        // Fetch tenant documents with tenant name
        $docs = mysqli_query($conn, "
            SELECT d.*, t.fullname AS tenant_name
            FROM tenant_documents d
            LEFT JOIN tenants t ON d.tenant_id = t.id
            ORDER BY d.uploaded_at DESC
        ");

        while($doc = mysqli_fetch_assoc($docs)) {
        ?>
        <tr>
            <td><?= htmlspecialchars($doc['tenant_name'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($doc['document_name'] ?? 'Unnamed') ?></td>
            <td>
                <?php if(!empty($doc['file_path'])): ?>
                    <a href="<?= htmlspecialchars($doc['file_path']) ?>" target="_blank">View</a>
                <?php else: ?>
                    N/A
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($doc['uploaded_at'] ?? '-') ?></td>
            <td>
                <a href="edit_records.php?type=document&id=<?= $doc['id'] ?>" class="action-btn">Edit</a>
                <a href="delete_record.php?table=tenant_documents&id=<?= $doc['id'] ?>" 
                   class="action-btn" style="background:#ef4444;"
                   onclick="return confirm('Are you sure you want to delete this document?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</section>

<?php elseif($page === 'jobs'): ?>
    <section id="jobs">
        <h2>Employment Applications</h2>
        <table>
            <tr>
                <th>Applicant</th>
                <th>Position</th>
                <th>Status</th>
                <th>Applied Date</th>
                <th>Actions</th>
            </tr>
            <?php
            $apps = mysqli_query($conn, "SELECT * FROM job_applications ORDER BY created_at DESC");
            while ($app = mysqli_fetch_assoc($apps)) {
                echo "<tr>
                        <td>".htmlspecialchars($app['full_name'])."</td>
                        <td>".htmlspecialchars($app['position'])."</td>
                        <td>".htmlspecialchars($app['status'])."</td>
                        <td>".htmlspecialchars($app['created_at'] ?? 'N/A')."</td>
                        <td>
                            <a href='view_application.php?id=".$app['id']."' class='action-btn'>View</a>
                            <a href='approve_application.php?id=".$app['id']."' class='action-btn'>Approve</a>
                            <a href='reject_application.php?id=".$app['id']."' class='action-btn'>Reject</a>
                        </td>
                      </tr>";
            }
            ?>
        </table>
    </section>

<?php elseif($page === 'maintenance'): ?>
    <section id="maintenance">
        <h2>Maintenance Requests</h2>
        <table>
            <tr>
                <th>Property</th>
                <th>Issue</th>
                <th>Priority</th>
                <th>Assigned Staff</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            <?php
            $requests = mysqli_query($conn, "
                SELECT m.*, u.fullname as staff_name, p.property_name as property_name
                FROM maintenance_requests m
                LEFT JOIN users u ON m.assigned_staff = u.id
                LEFT JOIN properties p ON m.property_id = p.id
                ORDER BY m.created_at DESC
            ");
            while ($r = mysqli_fetch_assoc($requests)) {
                echo "<tr>
                        <td>".htmlspecialchars($r['property_name'] ?? 'N/A')."</td>
                        <td>".htmlspecialchars($r['issue'])."</td>
                        <td>".htmlspecialchars($r['priority'] ?? 'medium')."</td>
                        <td>".htmlspecialchars($r['staff_name'] ?? 'Unassigned')."</td>
                        <td>".htmlspecialchars($r['status'])."</td>
                        <td>
                            <a href='assign_staff.php?id=".$r['id']."' class='action-btn'>Assign</a>
                            <a href='mark_complete.php?id=".$r['id']."' class='action-btn'>Complete</a>
                        </td>
                      </tr>";
            }
            ?>
        </table>
    </section>

       <?php elseif($page === 'tenant_payments'): ?>
<section id="tenant_payments">
    <h2 style="text-align:center; margin-bottom:30px; color:#366d21;">TENANT PAYMENTS</h2>

    <!-- Add new payment button -->
    <div style="text-align:center; margin-bottom:20px;">
        <a href="add_payment.php" class="action-btn" style="background: #0ea5e9;">+++ RECORD NEW PAYMENT</a>
    </div>

    <!-- Payments Table -->
    <table>
        <tr>
            <th>Tenant</th>
            <th>Property</th>
            <th>Amount (UGX)</th>
            <th>Date</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php
        $payments = mysqli_query($conn, "
            SELECT pay.*, t.fullname as tenant_name, p.property_name
            FROM payments pay
            LEFT JOIN tenants t ON pay.tenant_id = t.id
            LEFT JOIN properties p ON pay.property_id = p.id
            ORDER BY pay.date DESC
        ");
        while ($pay = mysqli_fetch_assoc($payments)) {
            echo "<tr>
                    <td>".htmlspecialchars($pay['tenant_name'] ?? 'N/A')."</td>
                    <td>".htmlspecialchars($pay['property_name'] ?? 'N/A')."</td>
                    <td>".number_format($pay['amount'])."</td>
                    <td>".htmlspecialchars($pay['date'])."</td>
                    <td>".htmlspecialchars($pay['status'] ?? 'Pending')."</td>
                    <td>
                        <a href='edit_records.php?type=payment&id=".$pay['id']."' class='action-btn'>Edit</a>
                        <a href='delete_record.php?table=payments&id=".$pay['id']."' class='action-btn' 
                           style='background:#ef4444;' 
                           onclick=\"return confirm('Delete this payment?')\">Delete</a>
                    </td>
                  </tr>";
        }
        ?>
    </table>
</section>

 <?php elseif($page === 'complaints'): ?>
<section id="complaints">
    <h2 style="text-align:center; margin-bottom:30px; color:#366d21;">COMPLAINTS & FEEDBACK</h2>

    <!-- Complaints Table -->
    <table>
        <tr>
            <th>Tenant</th>
            <th>Category</th>
            <th>Message</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>
        <?php
        $complaints = mysqli_query($conn, "
            SELECT c.*, t.fullname as tenant_name
            FROM complaints c
            LEFT JOIN tenants t ON c.tenant_id = t.id
            ORDER BY c.created_at DESC
        ");
        while ($c = mysqli_fetch_assoc($complaints)) {
            echo "<tr>
                    <td>".htmlspecialchars($c['tenant_name'] ?? 'N/A')."</td>
                    <td>".htmlspecialchars($c['category'] ?? 'N/A')."</td>
                    <td>".htmlspecialchars(substr($c['message'] ?? '', 0, 50))."...</td>
                    <td>".htmlspecialchars($c['status'] ?? 'pending')."</td>
                    <td>".htmlspecialchars($c['created_at'] ?? 'N/A')."</td>
                    <td>
                        <a href='view_complaint.php?id=".$c['id']."' class='action-btn' style='background:#0ea5e9;'>View</a>
                        <a href='resolve_complaint.php?id=".$c['id']."' class='action-btn' style='background:#16a34a;' 
                           onclick=\"return confirm('Mark this complaint as resolved?')\">Resolve</a>
                        <a href='delete_record.php?table=complaints&id=".$c['id']."' class='action-btn' 
                           style='background:#ef4444;' 
                           onclick=\"return confirm('Delete this complaint?')\">Delete</a>
                    </td>
                  </tr>";
        }
        ?>
    </table>
</section>
   
<?php elseif($page === 'guests'): ?>
<section id="guests">
    <h2 style="text-align:center; margin-bottom:30px; color:#366d21;">GUEST / VISITOR APPROVALS</h2>

    <!-- Add new guest button -->
    <div style="text-align:center; margin-bottom:20px;">
        <a href="add_guest.php" class="action-btn" style="background: #0ea5e9;">+++ ADD NEW GUEST</a>
    </div>

    <?php
    // Fetch guests with tenant name and property name if available
    $guests_sql = "
        SELECT g.*, t.fullname AS tenant_name, p.property_name
        FROM guests g
        LEFT JOIN tenants t ON g.tenant_id = t.id
        LEFT JOIN properties p ON g.property_id = p.id
        ORDER BY g.created_at DESC
    ";

    $guests = mysqli_query($conn, $guests_sql);

    if (!$guests) {
        echo "<p style='color:red;'>Error fetching guests: " . mysqli_error($conn) . "</p>";
    }
    ?>

    <table>
        <tr>
            <th>Guest Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Tenant / Visiting Tenant</th>
            <th>Property</th>
            <th>Check-in</th>
            <th>Check-out</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>

        <?php if($guests): ?>
            <?php while($g = mysqli_fetch_assoc($guests)): ?>
            <tr>
                <td><?= htmlspecialchars($g['fullname']) ?></td>
                <td><?= htmlspecialchars($g['email'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($g['phone'] ?? 'N/A') ?></td>
                <td>
                    <?= htmlspecialchars($g['tenant_name'] ?? 'N/A') ?>
                    <?php if(!empty($g['visiting_tenant_id'])): ?>
                        (Visitor)
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($g['property_name'] ?? 'N/A') ?></td>
                <td><?= htmlspecialchars($g['check_in'] ?? '-') ?></td>
                <td><?= htmlspecialchars($g['check_out'] ?? '-') ?></td>
                <td><?= htmlspecialchars($g['status'] ?? 'Pending') ?></td>
                <td>
                    <a href="approve_guest.php?id=<?= $g['id'] ?>" class="action-btn" style="background:#16a34a;">Approve</a>
                    <a href="reject_guest.php?id=<?= $g['id'] ?>" class="action-btn" style="background:#ef4444;">Reject</a>
                    <a href="delete_record.php?table=guests&id=<?= $g['id'] ?>" class="action-btn" style="background:#b91c1c;" onclick="return confirm('Delete this guest?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        <?php endif; ?>
    </table>
</section>

<?php elseif($page === 'brokers'): ?>
<section id="brokers">
    <h2 style="text-align:center; margin-bottom:30px; color:#366d21;">MANAGE BROKERS / AGENTS</h2>

    <!-- Add new broker button -->
    <div style="text-align:center; margin-bottom:20px;">
        <a href="add_user.php?role=broker" class="action-btn" style="background: #0ea5e9;">+++ ADD NEW BROKER</a>
    </div>

    <table>
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Assigned Properties</th>
            <th>Commission (UGX)</th>
            <th>Actions</th>
        </tr>
        <?php
        $brokers = mysqli_query($conn, "SELECT * FROM users WHERE role='broker' ORDER BY created_at DESC");

        while ($b = mysqli_fetch_assoc($brokers)) {
            $broker_id = $b['id'];
            
            // Count assigned properties
            $prop_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM properties WHERE broker_id='$broker_id'"))['count'];

            // Calculate total commission from payments
            $commission_total = mysqli_fetch_assoc(mysqli_query($conn, "
                SELECT SUM(amount * commission_percentage / 100) AS total
                FROM payments p
                JOIN properties pr ON p.property_id = pr.id
                WHERE pr.broker_id='$broker_id'
            "))['total'] ?? 0;
        ?>
        <tr>
            <td><?= htmlspecialchars($b['fullname']) ?></td>
            <td><?= htmlspecialchars($b['email'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($b['phone'] ?? 'N/A') ?></td>
            <td><?= $prop_count ?></td>
            <td><?= number_format($commission_total) ?></td>
            <td>
                <a href="edit_user.php?id=<?= $broker_id ?>" class="action-btn">Edit</a>
                <a href="delete_user.php?id=<?= $broker_id ?>" class="action-btn" 
                   style="background:#ef4444;"
                   onclick="return confirm('Are you sure you want to delete this broker?')">Delete</a>
            </td>
        </tr>
        <?php } ?>
    </table>
</section>
  
 <?php elseif($page === 'propertyowners'): ?>
<section id="propertyowners">
    <h2 style="text-align:center; margin-bottom:30px; color:#366d21;">PROPERTY OWNERS</h2>

    <!-- Add New Property Owner Button -->
    <div style="text-align:center; margin-bottom:20px;">
        <a href="add_propertyowner.php" class="action-btn" style="background: #0ea5e9;">+++ ADD NEW PROPERTY OWNER</a>
    </div>

    <table>
        <tr>
            <th>Full Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Properties Owned</th>
            <th>Actions</th>
        </tr>

        <?php
        // Fetch all property owners
        $owners = mysqli_query($conn, "
            SELECT u.*, COUNT(p.id) AS properties_count
            FROM users u
            LEFT JOIN properties p ON u.id = p.owner_id
            WHERE u.role='propertyowner'
            GROUP BY u.id
            ORDER BY u.created_at DESC
        ");

        while ($owner = mysqli_fetch_assoc($owners)):
        ?>
        <tr>
            <td><?= htmlspecialchars($owner['fullname']) ?></td>
            <td><?= htmlspecialchars($owner['email'] ?? 'N/A') ?></td>
            <td><?= htmlspecialchars($owner['phone'] ?? 'N/A') ?></td>
            <td><?= (int)$owner['properties_count'] ?></td>
            <td>
                <a href="edit_records.php?type=propertyowner&id=<?= $owner['id'] ?>" class="action-btn">Edit</a>
                <a href="delete_record.php?table=users&id=<?= $owner['id'] ?>" 
                   class="action-btn" style="background:#ef4444;"
                   onclick="return confirm('Are you sure you want to delete this property owner?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</section>

<?php elseif($page === 'payments'): ?>
    <section id="payments">
        <h2>Payments / Rent Tracking</h2>
        <table>
            <tr>
                <th>Tenant</th>
                <th>Property</th>
                <th>Amount (UGX)</th>
                <th>Date</th>
                <th>Status</th>
            </tr>
            <?php
            $payments = mysqli_query($conn, "
                SELECT pay.*, t.fullname as tenant_name, p.property_name as property_name
                FROM payments pay
                LEFT JOIN tenants t ON pay.tenant_id = t.id
                LEFT JOIN properties p ON pay.property_id = p.id
                ORDER BY pay.date DESC
            ");
            while ($pay = mysqli_fetch_assoc($payments)) {
                echo "<tr>
                        <td>".htmlspecialchars($pay['tenant_name'] ?? 'N/A')."</td>
                        <td>".htmlspecialchars($pay['property_name'] ?? 'N/A')."</td>
                        <td>".number_format($pay['amount'])."</td>
                        <td>".htmlspecialchars($pay['date'])."</td>
                        <td>".htmlspecialchars($pay['status'] ?? 'pending')."</td>
                      </tr>";
            }
            ?>
        </table>
    </section>

    <?php elseif($page === 'employee_performance'): ?>
<section id="employee_performance">
    <h2>Employee Performance</h2>
    <table>
        <tr>
            <th>Staff Member</th>
            <th>Task Completed</th>
            <th>Tasks Pending</th>
            <th>Overall Status</th>
        </tr>
        <?php
        $staff = mysqli_query($conn, "SELECT * FROM users WHERE role='staff'");
        while ($s = mysqli_fetch_assoc($staff)) {
            $staff_id = $s['id'];

            $tasks_completed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE assigned_to='$staff_id' AND status='Completed'"))['count'];
            $tasks_pending = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as count FROM tasks WHERE assigned_to='$staff_id' AND status!='Completed'"))['count'];

            $overall_status = $tasks_pending == 0 ? 'Excellent' : ($tasks_completed >= $tasks_pending ? 'Good' : 'Needs Improvement');
        ?>
        <tr>
            <td><?= htmlspecialchars($s['fullname']) ?></td>
            <td><?= $tasks_completed ?></td>
            <td><?= $tasks_pending ?></td>
            <td><?= $overall_status ?></td>
        </tr>
        <?php } ?>
    </table>
</section>

 <?php elseif($page === 'revenue_reports'): ?>
<section id="revenue_reports">
    <h2 style="text-align:center; margin-bottom:30px; color:#366d21;">REVENUE REPORTS</h2>

    <?php
    // Fetch total revenue
    $total_revenue = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT SUM(amount) AS total FROM payments
    "))['total'] ?? 0;

    // Fetch pending revenue
    $pending_revenue = mysqli_fetch_assoc(mysqli_query($conn, "
        SELECT SUM(amount) AS total FROM payments WHERE status='pending'
    "))['total'] ?? 0;

    // Collected revenue
    $collected_revenue = $total_revenue - $pending_revenue;
    ?>

    <div class="overview-cards" style="justify-content: space-around; margin-bottom:40px;">
        <div class="circular-card">
            <h3>Total Revenue</h3>
            <p>UGX <?= number_format($total_revenue) ?></p>
        </div>
        <div class="circular-card">
            <h3>Collected</h3>
            <p>UGX <?= number_format($collected_revenue) ?></p>
        </div>
        <div class="circular-card">
            <h3>Pending</h3>
            <p>UGX <?= number_format($pending_revenue) ?></p>
        </div>
    </div>

    <h3 style="margin-bottom:20px;">Revenue by Property</h3>
    <table>
        <tr>
            <th>Property Name</th>
            <th>Total Paid (UGX)</th>
            <th>Pending (UGX)</th>
        </tr>
        <?php
        $properties = mysqli_query($conn, "
            SELECT pr.property_name,
                   SUM(CASE WHEN p.status='paid' THEN p.amount ELSE 0 END) AS paid,
                   SUM(CASE WHEN p.status='pending' THEN p.amount ELSE 0 END) AS pending
            FROM properties pr
            LEFT JOIN payments p ON pr.id = p.property_id
            GROUP BY pr.id
            ORDER BY pr.property_name ASC
        ");
        while($prop = mysqli_fetch_assoc($properties)):
        ?>
        <tr>
            <td><?= htmlspecialchars($prop['property_name']) ?></td>
            <td><?= number_format($prop['paid'] ?? 0) ?></td>
            <td><?= number_format($prop['pending'] ?? 0) ?></td>
        </tr>
        <?php endwhile; ?>
    </table>

</section>

<?php elseif($page === "settings"): ?>

<section>
    <h2 style="text-align:center;"> SYSYTEM SETTINGS</h2>

    <?php
    $settingsQuery = mysqli_query($conn, "SELECT * FROM system_settings LIMIT 1");

    if ($settingsQuery && mysqli_num_rows($settingsQuery) > 0) {
        $settings = mysqli_fetch_assoc($settingsQuery);
    } else {
        $settings = [
            "site_name" => "HousingHub",
            "email" => "",
            "notification_email" => "",
            "backup_frequency" => "weekly"
        ];
    }
    ?>

    <form method="POST" action="save_settings.php"
          style="max-width:500px; margin:auto; color:black;border: 4px solid #0ea5e9;
                 padding:25px; border-radius:12px;background: linear-gradient(135deg, #3f4242, #0ea5e9);
                 box-shadow:0 4px 15px rgba(0, 0, 0, 0.97);">

        <!-- Site Name -->
        <label><b>Site Name</b></label>
        <input type="text" name="site_name"
               value="<?= htmlspecialchars($settings['site_name']) ?>"
               required
               style="width:100%; padding:10px; margin:10px 0;">

        <!-- Admin Email -->
        <label><b>System Email</b></label>
        <input type="email" name="email"
               value="<?= htmlspecialchars($settings['email'] ?? '') ?>"
               style="width:100%; padding:10px; margin:10px 0;">

        <!-- Notification Email -->
        <label><b>Notification Email</b></label>
        <input type="email" name="notification_email"
               value="<?= htmlspecialchars($settings['notification_email'] ?? '') ?>"
               style="width:100%; padding:10px; margin:10px 0;">

        <!-- Backup Frequency -->
        <label><b>Backup Frequency</b></label>
        <select name="backup_frequency"
                style="width:100%; padding:10px; margin:10px 0;">

            <option value="daily"
                <?= ($settings['backup_frequency']=="daily")?"selected":"" ?>>
                Daily
            </option>

            <option value="weekly"
                <?= ($settings['backup_frequency']=="weekly")?"selected":"" ?>>
                Weekly
            </option>

            <option value="monthly"
                <?= ($settings['backup_frequency']=="monthly")?"selected":"" ?>>
                Monthly
            </option>

        </select>

        <button type="submit" name="save_settings"
                class="action-btn"
                style="width:100%; background:black;">
            SAVE
        </button>

    </form>
</section>


<?php elseif($page === 'reports'): ?>
    <section id="reports">
        <h2>Reports & Analytics</h2>
        <div class="chart-container">
            <h3>Coming Soon</h3>
            <p>Charts and analytics will be displayed here (e.g., occupancy rates, revenue trends, property performance).</p>
        </div>
    </section>

<?php elseif($page === 'complaints'): ?>
    <section id="complaints">
        <h2>Complaints & Feedback</h2>
        <table>
            <tr>
                <th>Tenant</th>
                <th>Category</th>
                <th>Message</th>
                <th>Status</th>
                <th>Date</th>
                <th>Actions</th>
            </tr>
            <?php
            $complaints = mysqli_query($conn, "
                SELECT c.*, t.fullname as tenant_name
                FROM complaints c
                LEFT JOIN tenants t ON c.tenant_id = t.id
                ORDER BY c.created_at DESC
            ");
            while ($c = mysqli_fetch_assoc($complaints)) {
                echo "<tr>
                        <td>".htmlspecialchars($c['tenant_name'] ?? 'N/A')."</td>
                        <td>".htmlspecialchars($c['category'] ?? 'N/A')."</td>
                        <td>".htmlspecialchars(substr($c['message'] ?? '', 0, 50))."...</td>
                        <td>".htmlspecialchars($c['status'] ?? 'pending')."</td>
                        <td>".htmlspecialchars($c['created_at'] ?? 'N/A')."</td>
                        <td>
                            <a href='resolve_complaint.php?id=".$c['id']."' class='action-btn'>Resolve</a>
                        </td>
                      </tr>";
            }
            ?>
        </table>
    </section>
   
    <?php elseif($page === 'notifications'): ?>
<section id="notifications">
    <h2 style="text-align:center; margin-bottom:30px; color:#366d21;">NOTIFICATIONS</h2>

    <?php
    $notifications = mysqli_query($conn, "
        SELECT n.*, u.fullname AS sender_name, t.fullname AS tenant_name
        FROM notifications n
        LEFT JOIN users u ON n.user_id = u.id
        LEFT JOIN tenants t ON n.tenant_id = t.id
        ORDER BY n.date DESC
    ");
    ?>

    <table>
        <tr>
            <th>Recipient</th>
            <th>Tenant</th>
            <th>Title</th>
            <th>Message</th>
            <th>Status</th>
            <th>Date</th>
            <th>Actions</th>
        </tr>

        <?php while($n = mysqli_fetch_assoc($notifications)): ?>
        <tr>
            <td><?= htmlspecialchars($n['sender_name'] ?? 'System') ?></td>
            <td><?= htmlspecialchars($n['tenant_name'] ?? '-') ?></td>
            <td><?= htmlspecialchars($n['title'] ?? '-') ?></td>
            <td><?= htmlspecialchars(substr($n['message'], 0, 50)) ?>...</td>
            <td><?= htmlspecialchars(ucfirst($n['status'] ?? 'unread')) ?></td>
            <td><?= htmlspecialchars($n['date'] ?? '-') ?></td>
            <td>
                <?php if($n['status'] === 'unread'): ?>
                    <a href="mark_notification_read.php?id=<?= $n['id'] ?>" class="action-btn" style="background:#16a34a;">Mark Read</a>
                <?php endif; ?>
                <a href="delete_record.php?table=notifications&id=<?= $n['id'] ?>" class="action-btn" style="background:#ef4444;" onclick="return confirm('Delete this notification?')">Delete</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</section>
  
   <?php elseif($page === 'backups'): ?>
<section id="backups">
    <h2 style="text-align:center; margin-bottom:30px; color:#366d21;">BACKUP / EXPORT DATA</h2>

    <div style="text-align:center; margin-bottom:20px;">
        <p>Click a button below to export your data.</p>

        <!-- Export full database as SQL -->
        <a href="export_sql.php" class="action-btn" style="background:#0ea5e9; margin-bottom:10px;">Export Full Database (SQL)</a>
        <br>

        <!-- Export individual tables as CSV -->
        <a href="export_csv.php?table=users" class="action-btn" style="background:#38bdf8; margin-bottom:10px;">Export Users (CSV)</a>
        <br>
        <a href="export_csv.php?table=tenants" class="action-btn" style="background:#38bdf8; margin-bottom:10px;">Export Tenants (CSV)</a>
        <br>
        <a href="export_csv.php?table=properties" class="action-btn" style="background:#38bdf8; margin-bottom:10px;">Export Properties (CSV)</a>
        <br>
        <a href="export_csv.php?table=payments" class="action-btn" style="background:#38bdf8; margin-bottom:10px;">Export Payments (CSV)</a>
        <br>
        <a href="export_csv.php?table=complaints" class="action-btn" style="background:#38bdf8;">Export Complaints (CSV)</a>
    </div>

    <div class="chart-container" style="text-align:center;">
        <h3>Info</h3>
        <p>Exported SQL can be used to restore the database. CSV exports can be opened in Excel or Google Sheets.</p>
    </div>
</section>

<?php elseif($page === 'settings'): ?>
    <section id="settings">
        <h2>System Settings</h2>
        <div class="chart-container">
            <h3>Configuration Options</h3>
            <p>Settings page content goes here (e.g., system preferences, email settings, backup options).</p>
        </div>
    </section>

<?php endif; ?>

</div>

</body>
</html>