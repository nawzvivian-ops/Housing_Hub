<?php
session_start();
include "db_connect.php";

// Ensure the user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch tenant info using user_id
$tenant_result = mysqli_query($conn, "SELECT * FROM tenants WHERE user_id='$user_id'");
$tenant = mysqli_fetch_assoc($tenant_result);

if (!$tenant) {
    die("Tenant information not found. Please make sure your tenant profile is linked to your account.");
}

// Placeholder: fetch payments (later connect to actual payments table)
$payments_result = mysqli_query($conn, "SELECT * FROM payments WHERE tenant_id='".$tenant['id']."' ORDER BY id DESC");

// Placeholder: notifications/messages
$tenant_id = $tenant['id']; // from session fetch

$notif_result = mysqli_query($conn, "
    SELECT * FROM notifications 
    WHERE tenant_id='$tenant_id' 
    ORDER BY date DESC
");

$notifications = mysqli_fetch_all($notif_result, MYSQLI_ASSOC);

   $maintenance_result = mysqli_query($conn, "
    SELECT * FROM maintenance_requests 
    WHERE property_id='".$tenant['property_id']."' 
    ORDER BY created_at DESC
");
$maintenance_requests = mysqli_fetch_all($maintenance_result, MYSQLI_ASSOC);
   // Fetch tenant documents
    $doc_result = mysqli_query($conn, "
    SELECT * FROM tenant_documents 
    WHERE tenant_id='$tenant_id' 
    ORDER BY id DESC
");

$documents = mysqli_fetch_all($doc_result, MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Tenant Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<style>
body {
    font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background:lightblue;
    margin:0;
    color:#333;
}
.container {max-width:1200px; margin:30px auto; padding:0 20px;}
h1 {color:#4f46e5; margin-bottom:20px; text-align:center;}
.card {background:#fff; padding:20px; border-radius:12px; font-family: Times New Roman, Times, serif;border:3px solid blue; box-shadow:0 6px 15px rgba(223, 209, 23, 0.1); margin-bottom:20px;}
.card h2 {margin-bottom:15px; color:#1e293b;}
.card p {margin-bottom:10px; font-size:16px;}
.card table {width:100%; border-collapse:collapse;}
.card table th, .card table td {padding:10px; border-bottom:1px solid #ddd;}
.card table th {background:lightblue; color:#fff;}
.card table tr:hover {background:#f0f4f8;}
.action-btn {padding:6px 12px; background:black; color:#fff; border:none; border-radius:5px; cursor:pointer; text-decoration:none;}
.action-btn:hover {background:#3b36e0;}
.logout-btn {display:block; width:150px; margin:20px auto 0; padding:10px; background:black; color:#fff; text-align:center; border:none; border-radius:8px; text-decoration:none;}
.logout-btn:hover {background:#dc2626;}
.section-grid {display:grid; grid-template-columns: repeat(auto-fit,minmax(280px,1fr)); gap:20px;}
/* ── FIXED HEADER — cannot scroll with content ─────────────── */
body { padding-top: 106px !important; }
header {
  position: fixed !important;
  top: 0 !important;
  left: 0 !important;
  right: 0 !important;
  width: 100% !important;
  z-index: 99999 !important;
  box-shadow: 0 2px 28px rgba(0,0,0,.28) !important;
}
nav { position: relative !important; z-index: 100000 !important; }
.dropdown { z-index: 100001 !important; }
.dd-menu { z-index: 100002 !important; }
@media(max-width:900px){ body { padding-top: 80px !important; } }
</style>
</head>
<body>

<div class="container">

     
       <div class="card" style="border:3px solid #105ceb;">
         <h1 style="color:black; max-width:1200px; margin:10px auto; padding:0 10px;"><img src="image/tenant.png" alt="Photo" width="70px" height="90px">TENANT DASHBOARD<img src="image/tenant.png" alt="Photo" width="70px" height="90px"></h1>
        </div>

       <div class="card" style="border:3px solid black;">
        <h1 style="color:deepblue; font-size:30px;font-family: Times New Roman, Times, serif;border:1px solid #0e2ee7;">DELIGHTED, WELCOME: <?php echo htmlspecialchars($tenant['fullname']); ?></h1>
        </div>

    <!-- Personal Info -->
    <div class="card"style="border:3px solid #105ceb;">
        <h2 style="font-family: Times New Roman, Times, serif;">Personal Information</h2>
        <p><strong>Full Name:</strong> <?php echo htmlspecialchars($tenant['fullname']); ?></p>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($tenant['email']); ?></p>
        <p><strong>Phone:</strong> <?php echo htmlspecialchars($tenant['phone']); ?></p>
        <p><strong>Date Joined:</strong> <?php echo htmlspecialchars($tenant['created_at'] ?? ''); ?></p>
        <p><strong>Address:</strong> <?php echo htmlspecialchars($tenant['address'] ?? ''); ?></p>
         <a href="edit_tenant.php" class="action-btn">Edit Profile</a>
    </div>

     <!-- Property / Lease / Rent / Buy Info -->
<?php
// Fetch property info for the tenant
$property_result = mysqli_query($conn, "
    SELECT * FROM properties 
    WHERE id = '".$tenant['property_id']."'
");
$property = mysqli_fetch_assoc($property_result);

// Determine what to show
if ($property) {
    echo "<div class='card'>";

    switch($property['purpose']) {
        case 'Lease':
            echo "<h2>Lease Information</h2>";
            echo "<p><strong>Property:</strong> ".$property['property_name']."</p>";
            echo "<p><strong>Lease Start:</strong> ".$tenant['lease_start']."</p>";
            echo "<p><strong>Lease End:</strong> ".$tenant['lease_end']."</p>";
            echo "<p><strong>Rent Amount:</strong> $".$property['rent_amount']."</p>";
            if ($property['purpose'] == 'Lease') {
             echo '<a href="download_file.php?type=lease" class="action-btn">Download Lease</a>';
                 }
            break;

        case 'Rent':
            echo "<h2>Rental Information</h2>";
            echo "<p><strong>Property:</strong> ".$property['property_name']."</p>";
            echo "<p><strong>Monthly Rent:</strong> $".$property['rent_amount']."</p>";
            echo "<p><strong>Next Payment Due:</strong> ".date('Y-m-d', strtotime('+30 days'))."</p>";
              if ($property['purpose'] == 'Rent') {
            echo '<a href="download_file.php?type=Rent" class="action-btn">Download Rent</a>';
            }
            break;

        case 'Buy':
            echo "<h2>Purchase Information</h2>";
            echo "<p><strong>Property:</strong> ".$property['property_name']."</p>";
            echo "<p><strong>Purchase Date:</strong> ".$tenant['lease_start']."</p>";
            echo "<p><strong>Price Paid:</strong> $".$property['rent_amount']."</p>";
            if ($property['purpose'] == 'Buy') {
            echo '<a href="download_file.php?type=Buy" class="action-btn">Download Buy</a>';
             }
            break;

        default:
            echo "<p>No property info available.</p>";
    }

    echo "</div>";
} else {
    echo "<div class='card'><p>No property information found for this tenant.</p></div>";
}
?>

    <!-- Payments Summary -->
<div class="card"style="border:3px solid #105ceb;">
    <h2 style="font-family: Times New Roman, Times, serif;">Payments Summary</h2>

    <?php
    // Calculate total paid
    $total_paid = 0;
    $payments_result = mysqli_query($conn, "SELECT * FROM payments WHERE tenant_id='".$tenant['id']."' ORDER BY date DESC");
    $payments = mysqli_fetch_all($payments_result, MYSQLI_ASSOC);

    foreach($payments as $p){
        if($p['status'] == 'paid') $total_paid += $p['amount'];
    }

    $outstanding = ($tenant['rent_amount'] ?? 0) - $total_paid;
    $next_due = date('Y-m-d', strtotime('+30 days'));
    ?>

    <p><strong>Total Paid:</strong> UGX <?= number_format($total_paid,2) ?></p>
    <p><strong>Next Payment Due:</strong> <?= $next_due ?></p>
    <p><strong>Outstanding Balance:</strong> UGX <?= number_format($outstanding,2) ?></p>

    <!-- Button to view full payment history -->
    <a href="payment_history.php" class="action-btn">View Payment History</a>
</div>

    <!-- Notifications / Messages -->
    <div class="card"style="border:3px solid black;">
        <h2 style="font-family: Times New Roman, Times, serif;">Notifications</h2>
        <table>
            <tr><th>Date</th><th>Title</th><th>Message</th></tr>
            <?php foreach($notifications as $note): ?>
                <tr>
                    <td><?php echo $note['date']; ?></td>
                    <td><?php echo $note['title']; ?></td>
                    <td><?php echo $note['message']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>    
         
    <div class="section" style="background:white; padding: 20px; border:3px solid blue;font-family: Times New Roman, Times, serif; border: radius 20px;">
  <h2 style="color:#1e293b;"> Maintenance Tips</h2>
  <ul style="margin-left: 20px; line-height: 1.6;">
    <li>Report any leaks or damage immediately.</li>
    <li>Keep electrical appliances in good condition.</li>
    <li>Avoid damaging walls, doors, or fixtures.</li>
    <li>Follow proper trash disposal rules.</li>
  </ul>
</div><br>

    <!-- Maintenance Requests -->
    <div class="card"style="border:3px solid black;">
        <h2 style="font-family: Times New Roman, Times, serif;">Maintenance Requests</h2>
        <table>
            <tr><th>Date</th><th>Title</th><th>Status</th></tr>
            <?php foreach($maintenance_requests as $req): ?>
                <tr>
                    <td><?php echo $req['date']; ?></td>
                    <td><?php echo $req['title']; ?></td>
                    <td><?php echo $req['status']; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <div style="margin-top: 15px; text-align: left;">
        <a href="submit_request.php" class="action-btn">Submit New Request</a>
    </div>
    </div>

    <!-- Optional Extras in Grid -->
    <div class="section-grid">
        <div class="card">
    <h2 style="font-family: Times New Roman, Times, serif;">Documents</h2>
    <?php if(count($documents) > 0): ?>
        <ul>
        <?php foreach($documents as $doc): ?>
            <li>
                <?= htmlspecialchars($doc['document_name'] ?? 'Unnamed Document') ?>
                <a href="download_document.php?id=<?= $doc['id'] ?>" class="action-btn">Download</a>
            </li>
        <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p>No documents uploaded yet.</p>
    <?php endif; ?>
</div>
             <div class="card">
    <h2 style="font-family: Times New Roman, Times, serif;">Support / Contact</h2>
    <p>Contact or ask questions for assistance.</p>
    <button class="action-btn" id="contactBtn">Send Message</button>

    <!-- Contact Form (hidden by default) -->
    <div id="contactForm" style="display:none; margin-top:20px;">
        <form method="POST" action="send_message.php">
            <label for="subject"><strong>Subject:</strong></label><br>
            <input type="text" name="subject" id="subject" required style="width:100%; padding:8px; margin:5px 0; border-radius:6px;"><br>
            
            <label for="message"><strong>Message:</strong></label><br>
            <textarea name="message" id="message" rows="5" required style="width:100%; padding:8px; margin:5px 0; border-radius:6px;"></textarea><br>
            
            <button type="submit" class="action-btn">Send</button>
        </form>
    </div>
</div>
        
        <div class="card">
    <h2 style="font-family: Times New Roman, Times, serif;">Profile Settings</h2>
    <p>Update your personal information.</p>
    <a href="change_password.php?id=<?= $tenant['id'] ?>" class="action-btn">Change Password</a>
</div>
        
   <div class="section" style="background:#fef9c3;font-family: Times New Roman, Times, serif; border-radius:20px; padding:20px; border:3px solid blue">
  <h3>Emergency Contacts</h3>
  <ul style="line-height:1.6;">
    <li>Security: +256 700 123 456</li>
    <li>Fire Department: 999</li>
    <li>Hospital: +256 701 234 567</li>
    <li>Property Manager: +256 741 035 928</li>
  </ul>
</div>

    <a href="logout.php" class="logout-btn"style="border:3px solid #105ceb;">Logout</a>
</div>
<script>
document.getElementById('contactBtn').addEventListener('click', function() {
    const form = document.getElementById('contactForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
});
</script>
</body>
</html>