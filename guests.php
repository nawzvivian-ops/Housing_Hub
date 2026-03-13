<?php
include "db_connect.php";

$success = '';
$error = '';
$guest_name = "";

// Handle guest visit submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
    $email    = mysqli_real_escape_string($conn, $_POST['email']);

    $property_id = intval($_POST['property_id'] ?? 0);
    $visiting_tenant_id = intval($_POST['visiting_tenant_id'] ?? 0);
    $visit_type = $_POST['visit_type'] ?? 'property';

    $check_in = $_POST['check_in'] ?? null;
    $check_out = $_POST['check_out'] ?? null;

    if (!$fullname || !$email || !$phone) {

        $error = "Please fill all required fields.";

    } else {

        // Insert visit request
        $stmt = mysqli_prepare($conn, "
            INSERT INTO guests 
            (fullname, phone, email, visiting_tenant_id, property_id, check_in, check_out, visit_type, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending')
        ");

        mysqli_stmt_bind_param(
            $stmt,
            "sssiiiss",
            $fullname,
            $phone,
            $email,
            $visiting_tenant_id,
            $property_id,
            $check_in,
            $check_out,
            $visit_type
        );

        if (mysqli_stmt_execute($stmt)) {

    $success = "Your visit request has been submitted successfully!";
    $guest_name = $fullname;

    // ✅ AUTO NOTIFICATION TO TENANT
    if ($visiting_tenant_id > 0) {

        $title = "Visitor Request";
        $message = "You have a visitor request from $fullname.";

        $notif_stmt = mysqli_prepare($conn, "
            INSERT INTO notifications (tenant_id, title, message)
            VALUES (?, ?, ?)
        ");

        mysqli_stmt_bind_param($notif_stmt, "iss",
            $visiting_tenant_id,
            $title,
            $message
        );

        mysqli_stmt_execute($notif_stmt);
    }
     
    // AUTO NOTIFICATION TO STAFF
$staff_result = mysqli_query($conn, "SELECT id FROM users WHERE role='staff'");
$title = "New Visitor Request";
$message = "$fullname submitted a visit request.";
while($staff = mysqli_fetch_assoc($staff_result)){
    $notif_stmt = mysqli_prepare($conn, "INSERT INTO notifications (staff_id, title, message) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($notif_stmt, "iss", $staff['id'], $title, $message);
    mysqli_stmt_execute($notif_stmt);
}
}
    }
}

/* Fetch dropdown data */
$properties = mysqli_query($conn, "SELECT id, property_name, address FROM properties ORDER BY property_name ASC");

$tenants = mysqli_query($conn, "SELECT id, fullname FROM tenants ORDER BY fullname ASC");

/* Guest Requests Table */
$guestRequests = mysqli_query($conn, "SELECT * FROM guests ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Guest / Visitor Portal</title>
<style>
body {background-image: url('images/flo.png'); background-size: 110% 110%;background-position: center; animation: zoomBackground 20s ease-in-out infinite alternate;}
@keyframes zoomBackground {0% { background-size: 100% 100%; } 50% { background-size: 120% 120%; } 100% { background-size: 100% 100%; }}
body {font-family:Segoe UI, sans-serif;  padding:20px;}
h2 {text-align:center; color:#1e293b;}
form {max-width:600px; margin:20px auto; background:#fff; border:5px solid black; padding:20px; border-radius:10px; box-shadow:0 4px 15px rgba(0,0,0,0.1);}
input, select {width:98%; padding:10px; margin:10px 0; border:1px solid #ccc; border-radius:10px;}
button {padding:10px 20px; background:#4f46e5; color:#fff; border:none; border-radius:5px; cursor:pointer;}
button:hover {background:#4338ca;}
.section {background:#fff; padding:20px; margin:20px 0; border-radius:10px;}
table {width:100%; border-collapse:collapse; margin-top:15px;}
th, td {padding:10px; text-align:left; border-bottom:1px solid #ccc;}
th {background:#f1f5f9;}
.status-pending {color:orange; font-weight:bold;}
.status-approved {color:green; font-weight:bold;}
.status-rejected {color:red; font-weight:bold;}

/* Toast Notification */
#toast {position: fixed; bottom: 20px;right: 20px;background: #3e34f5;color: #fff;padding: 15px 25px;border-radius: 8px;display: none;box-shadow: 0 4px 10px rgba(0,0,0,0.2);font-weight: bold;z-index: 9999;}
</style>
</head>
<body>

      <div class="card" style="border:5px solid black; text-align:center; padding:40px; width:50%; border-radius:12px;  background:white;">
        <h1><img src="images/guest.png" alt="Photo" width="60px" height="50px">GUEST DASHBOARD<img src="images/guest.png" alt="Photo" width="60px" height="50px"></h1>
        </div><br><br>



       <div class="card" style="border:5px solid blue; padding: 30px;border-radius:30px;font-family: Times New Roman, Times, serif; width:70%">
        <h1> Welcome to the Guest / Visitor Portal:<?php if(!empty($guest_name)) echo " - " . htmlspecialchars($guest_name); ?></h1>
        </div>

<!-- Toast container -->
<div id="toast"></div>

<!-- Guest Visit Form -->
<form method="POST">
    <h3><strong>SUBMIT YOUR VISIT</strong></h3>

    <label>Full Name </label>
    <input type="text" name="fullname" required>

    <label>Phone </label>
    <input type="text" name="phone" required>

    <label>Email </label>
    <input type="email" name="email" required>

    <label>Visit Type </label>
    <select name="visit_type" onchange="toggleVisit(this.value)">
        <option value="property">Visit a Property</option>
        <option value="tenant">Visit a Tenant / Person</option>
    </select>

    <div id="propertySelect">
        <label>Property </label>
        <select name="property_id">
            <?php while($p = mysqli_fetch_assoc($properties)): ?>
                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['property_name'].' | '.$p['address']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <div id="tenantSelect" style="display:none;">
        <label>Tenant / Person</label>
        <select name="visiting_tenant_id">
            <?php while($t = mysqli_fetch_assoc($tenants)): ?>
                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['fullname']) ?></option>
            <?php endwhile; ?>
        </select>
    </div>

    <label>Check-in (optional)</label>
    <input type="datetime-local" name="check_in">

    <label>Check-out (optional)</label>
    <input type="datetime-local" name="check_out">

    <button type="submit" style="bg:blue;">Submit Visit Request</button>
</form>

     <div class="section" style="background:white; border:5px solid blue;  width:80%;">
        <h3>Visitor Tips</h3>
         <ul>
             <li>Bring a valid ID for check-in.</li>
             <li>Respect check-in and check-out times.</li>
             <li>Notify the tenant if your plans change.</li>
        </ul>
    </div>   
     
    <div class="section" style="background:white; border-left:5px solid #0ea5e9; width:85%; border:5px solid blue;">
  <h3 style="color:#1e293b;">Property Tips</h3>
  <ul style="margin-left: 20px; line-height: 1.6;">
    <li>Always check the property amenities before visiting.</li>
    <li>Inspect key areas like kitchen, bathroom, and electrical fittings.</li>
    <li>Note the cleanliness and general maintenance of the property.</li>
    <li>Verify property rules and regulations in advance.</li>
    <li>Ensure parking and safety arrangements are in place.</li>
  </ul>
</div>
       
    <div class="section" style="background:white; border:5px solid blue;width:90%;">
  <h3>Featured Properties</h3>
  <p>Check out our best properties available for visits this week!</p>
  <ul>
      <?php
       mysqli_data_seek($properties, 0); // reset pointer if already used in dropdown
       while($prop = mysqli_fetch_assoc($properties)):
       ?>
  <li><?= htmlspecialchars($prop['property_name'] . ' | ' . $prop['address']) ?></li>
<?php endwhile; ?>
  </ul>
</div>

<div class="section"style=" border:5px solid blue;">
    <h3>Property Rules & Guidelines</h3>
    <p>Please follow all property rules for your safety. No loud noise, no unauthorized visitors, and follow check-in/out timings.</p>
</div>

<script>
function toggleVisit(type){
    if(type === 'tenant'){
        document.getElementById('tenantSelect').style.display = 'block';
        document.getElementById('propertySelect').style.display = 'none';
    } else {
        document.getElementById('tenantSelect').style.display = 'none';
        document.getElementById('propertySelect').style.display = 'block';
    }
}

// Toast notification function
function showToast(message, duration=4000){
    const toast = document.getElementById('toast');
    toast.innerText = message;
    toast.style.display = 'block';
    setTimeout(()=>{ toast.style.display = 'none'; }, duration);
}

// Trigger toast on submission
<?php if($success): ?>
showToast("<?= $success ?>\nStatus: Pending");
<?php endif; ?>

<?php if($error): ?>
showToast("<?= $error ?>");
<?php endif; ?>
</script>

</body>
</html>