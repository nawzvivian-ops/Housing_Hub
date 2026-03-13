<?php
// ==================== ERROR REPORTING ====================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include "db_connect.php";

// ==================== CHECK LOGIN ====================
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = intval($_SESSION['user_id']);
$userQ = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($userQ);

if (!$user) {
    echo "User not found!";
    exit;
}

// ==================== ACCESS CONTROL ====================
// Get role from database and store in $role variable
$role = strtolower($user['role']);

if (!in_array($role, ['propertyowner', 'broker', 'admin'])) {
    echo "Access denied! Your role (" . htmlspecialchars($user['role']) . ") does not have permission.";
    exit;
}

// ==================== ADD PROPERTY (PROPERTYOWNER / BROKER) ====================
if (isset($_POST['add_property']) && in_array($role, ['propertyowner','broker'])) {
    $property_name = mysqli_real_escape_string($conn, $_POST['property_name']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $property_type = mysqli_real_escape_string($conn, $_POST['property_type']); // Rent / Lease / Buy
    $rent_amount = floatval($_POST['rent_amount']);
    $bedrooms = intval($_POST['bedrooms']);
    $size_sqft = intval($_POST['size_sqft']);
    $purpose = mysqli_real_escape_string($conn, $_POST['purpose']);

    mysqli_query($conn, "
        INSERT INTO properties
        (owner_id, property_name, address, property_type, rent_amount, bedrooms, size_sqft, purpose)
        VALUES
        ('$user_id', '$property_name', '$address', '$property_type', '$rent_amount', '$bedrooms', '$size_sqft', '$purpose')
    ");
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ==================== EDIT PROPERTY ====================
if (isset($_POST['edit_property']) && in_array($role, ['propertyowner','broker'])) {
    $pid = intval($_POST['property_id']);
    mysqli_query($conn, "
        UPDATE properties SET
        property_name='".mysqli_real_escape_string($conn,$_POST['property_name'])."',
        address='".mysqli_real_escape_string($conn,$_POST['address'])."',
        property_type='".mysqli_real_escape_string($conn,$_POST['property_type'])."',
        rent_amount='".floatval($_POST['rent_amount'])."',
        bedrooms='".intval($_POST['bedrooms'])."',
        size_sqft='".intval($_POST['size_sqft'])."',
        purpose='".mysqli_real_escape_string($conn,$_POST['purpose'])."'
        WHERE id='$pid' AND owner_id='$user_id'
    ");
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ==================== ARCHIVE PROPERTY ====================
if (isset($_GET['archive']) && in_array($role, ['propertyowner','broker'])) {
    $pid = intval($_GET['archive']);
    mysqli_query($conn, "
        UPDATE properties SET status='archived'
        WHERE id='$pid' AND owner_id='$user_id'
    ");
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ==================== UPLOAD PROPERTY IMAGE ====================
if (isset($_POST['upload_image']) && in_array($role, ['propertyowner','broker'])) {
    $pid = intval($_POST['property_id']);
    if (!is_dir('uploads')) mkdir('uploads', 0777, true);
    
    // Validate file type
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    
    if (in_array($ext, $allowed) && $_FILES['image']['error'] == 0) {
        $img = time() . '_' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], "uploads/$img");

        mysqli_query($conn, "
            INSERT INTO property_images (property_id, image_path)
            VALUES ('$pid', '$img')
        ");
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ==================== ADD AMENITY ====================
if (isset($_POST['add_amenity']) && in_array($role, ['propertyowner','broker'])) {
    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $cost_type = mysqli_real_escape_string($conn,$_POST['cost_type']); // Free / Paid
    mysqli_query($conn, "
        INSERT INTO amenities (name, cost_type)
        VALUES ('$name', '$cost_type')
    ");
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ==================== ASSIGN AMENITY TO PROPERTY ====================
if (isset($_POST['assign_amenity']) && in_array($role, ['propertyowner','broker'])) {
    $property_id = intval($_POST['property_id']);
    $amenity_id = intval($_POST['amenity_id']);
    mysqli_query($conn, "
        INSERT INTO property_amenities (property_id, amenity_id)
        VALUES ('$property_id', '$amenity_id')
    ");
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ==================== ADD LEAD (BROKER) ====================
if (isset($_POST['add_lead']) && $role === 'broker') {
    $property_id = intval($_POST['property_id']);
    $name = mysqli_real_escape_string($conn,$_POST['name']);
    $phone = mysqli_real_escape_string($conn,$_POST['phone']);
    $email = mysqli_real_escape_string($conn,$_POST['email']);
    $notes = mysqli_real_escape_string($conn,$_POST['notes']);
    $follow_up_date = mysqli_real_escape_string($conn, $_POST['follow_up_date']);

    mysqli_query($conn, "
        INSERT INTO leads
        (broker_id, property_id, name, phone, email, notes, follow_up_date)
        VALUES
        ('$user_id', '$property_id', '$name', '$phone', '$email', '$notes', '$follow_up_date')
    ");
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// ==================== FETCH DATA ====================
$properties = mysqli_query($conn, "
    SELECT * FROM properties
    WHERE owner_id='$user_id' OR broker_id='$user_id'
");

$leads = mysqli_query($conn, "
    SELECT * FROM leads WHERE broker_id='$user_id'
");

$amenities = mysqli_query($conn, "SELECT * FROM amenities");

$property_images = mysqli_query($conn, "SELECT * FROM property_images");

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Brokers & PropertyOwners</title>
<style>
body{font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;background:lightblue;padding:20px;margin:0;}
.container{max-width:1200px;margin:0 auto;}
h2{margin-top:30px;color:black;font: size 30px;}
.card{background:#fff;padding:20px;border-radius:8px;margin-bottom:20px;box-shadow:0 2px 4px rgba(0,0,0,0.1);}
input,select,textarea{width:95%;padding:10px;margin:6px 0;border:1px solid #ddd;border-radius:10px;box-sizing:border-box;}
button{padding:10px 15px;background:#4f46e5;color:#fff;border:none;border-radius:6px;cursor:pointer;margin-top:10px;}
button:hover{background:#4338ca;}
table{width:100%;border-collapse:collapse;margin-top:10px;}
table,th,td{border:1px solid black;}
th,td{padding:10px;text-align:left;}
th{background:#f8f9fa;font-weight:600;}
a{color:#4f46e5;text-decoration:none;}
a:hover{text-decoration:underline;}
.success{color:green;padding:10px;background:#d4edda;border-radius:4px;margin:10px 0;}
.error{color:red;padding:10px;background:#f8d7da;border-radius:4px;margin:10px 0;}
</style>
</head>
<body>

<div class="container">
     <div class="card" style="border:5px solid black; text-align:center; padding:10px; width: 95%; border-radius:50px;  background:white; color:black;">
        <h2><img src="images/hand.png" alt="Photo" width="60px" height="50px">WELCOME;<img src="images/hand.png" alt="Photo" width="60px" height="50px"></h2>
         <h2>THANKS FOR JOINING US, <?php echo htmlspecialchars($user['fullname']); ?>!</h2>
        <p><strong>ROLE: <strong><?php echo htmlspecialchars(ucfirst($user['role'])); ?></strong></p>
    </div>
<hr style="color:black;">
<hr style="color:black;">
<hr style="color:black;">

<h2>Your Properties</h2>
<div class="card"style="border:5px solid black;  background:white; color:black;">
<table>
<tr>
<th>ID</th><th>Name</th><th>Type</th><th>Rent/Price</th><th>Status</th><th>Actions</th>
</tr>
<?php while($row = mysqli_fetch_assoc($properties)): ?>
<tr>
<td><?php echo $row['id']; ?></td>
<td><?php echo htmlspecialchars($row['property_name']); ?></td>
<td><?php echo htmlspecialchars($row['property_type']); ?></td>
<td><?php echo htmlspecialchars($row['rent_amount']); ?></td>
<td><?php echo htmlspecialchars($row['status'] ?? 'active'); ?></td>
<td>
<?php if(in_array($role, ['propertyowner','broker'])): ?>
<a href="?archive=<?php echo $row['id']; ?>" onclick="return confirm('Are you sure you want to archive this property?')">Archive</a>
<?php endif; ?>
</td>
</tr>
<?php endwhile; ?>
</table>
</div>

<?php if(in_array($role, ['propertyowner','broker'])): ?>
<h2>Add New Property</h2>
<div class="card"style="border:5px solid black; ">
<form method="POST">
<input type="text" name="property_name" placeholder="Property Name" required>
<input type="text" name="address" placeholder="Address" required>
<select name="property_type" required>
<option value="">Select Type</option>
<option value="Rent">Rent</option>
<option value="Lease">Lease</option>
<option value="Buy">Buy</option>
</select>
<input type="number" name="rent_amount" placeholder="Rent/Price Amount" step="0.01" required>
<input type="number" name="bedrooms" placeholder="Number of Bedrooms" required>
<input type="number" name="size_sqft" placeholder="Size (sq ft)" required>
<input type="text" name="purpose" placeholder="Purpose" required>
<button type="submit" name="add_property">Add Property</button>
</form>
</div>
<?php endif; ?>

<h2>Amenities</h2>
<div class="card"style="border:5px solid black;">
<table>
<tr><th>ID</th><th>Name</th><th>Cost Type</th></tr>
<?php 
mysqli_data_seek($amenities, 0);
while($a = mysqli_fetch_assoc($amenities)): 
?>
<tr>
<td><?php echo $a['id']; ?></td>
<td><?php echo htmlspecialchars($a['name']); ?></td>
<td><?php echo htmlspecialchars($a['cost_type']); ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

<?php if(in_array($role, ['propertyowner','broker'])): ?>
<h2>Add New Amenity</h2>
<div class="card"style="border:5px solid black;">
<form method="POST">
<input type="text" name="name" placeholder="Amenity Name" required>
<select name="cost_type" required>
<option value="">Select Cost Type</option>
<option value="Free">Free</option>
<option value="Paid">Paid</option>
</select>
<button type="submit" name="add_amenity">Add Amenity</button>
</form>
</div>
<?php endif; ?>

<h2>Property Images</h2>
<div class="card"style="border:5px solid black;">
<table>
<tr><th>ID</th><th>Property ID</th><th>Image</th></tr>
<?php while($img = mysqli_fetch_assoc($property_images)): ?>
<tr>
<td><?php echo $img['id']; ?></td>
<td><?php echo $img['property_id']; ?></td>
<td><img src="uploads/<?php echo htmlspecialchars($img['image_path']); ?>" width="100" alt="Property Image"></td>
</tr>
<?php endwhile; ?>
</table>
</div>

<?php if(in_array($role, ['propertyowner','broker'])): ?>
<h2>Upload Property Image</h2>
<div class="card" style="border:5px solid black;>
<form method="POST" enctype="multipart/form-data">
<input type="number" name="property_id" placeholder="Property ID" required>
<input type="file" name="image" accept="image/*" required>
<button type="submit" name="upload_image">Upload Image</button>
</form>
</div>
<?php endif; ?>

<?php if($role === 'broker'): ?>
<h2>Leads(potential/interested clients)</h2>
<div class="card"style="border:5px solid black;">
<table>
<tr><th>ID</th><th>Name</th><th>Phone</th><th>Email</th><th>Property ID</th><th>Follow Up Date</th></tr>
<?php 
mysqli_data_seek($leads, 0);
while($lead = mysqli_fetch_assoc($leads)): 
?>
<tr>
<td><?php echo $lead['id']; ?></td>
<td><?php echo htmlspecialchars($lead['name']); ?></td>
<td><?php echo htmlspecialchars($lead['phone']); ?></td>
<td><?php echo htmlspecialchars($lead['email']); ?></td>
<td><?php echo $lead['property_id']; ?></td>
<td><?php echo htmlspecialchars($lead['follow_up_date']); ?></td>
</tr>
<?php endwhile; ?>
</table>
</div>

<h2>Add New Lead</h2>
<div class="card" style="border:5px solid black;">
<form method="POST">
<input type="number" name="property_id" placeholder="Property ID" required>
<input type="text" name="name" placeholder="Lead Name" required>
<input type="text" name="phone" placeholder="Phone Number" required>
<input type="email" name="email" placeholder="Email" required>
<textarea name="notes" placeholder="Notes" rows="3"></textarea>
<input type="date" name="follow_up_date" required>
<button type="submit" name="add_lead">Add Lead</button>
</form>
</div>
<?php endif; ?>

</div>
<?php if($role === 'broker'): ?>
<h2>Commission</h2>
<div class="card" style="border:5px solid black;">
<?php
$commissions = mysqli_query($conn, "
    SELECT id, property_name, rent_amount, commission_rate, (rent_amount * commission_rate / 100) AS amount
    FROM properties
    WHERE broker_id='$user_id' AND status='occupied'
");
if(mysqli_num_rows($commissions) > 0):
?>
<table>
<tr>
<th>ID</th><th>Property Name</th><th>Rent/Price</th><th>Rate (%)</th><th>Amount Earned</th></tr>
<?php while($c = mysqli_fetch_assoc($commissions)): ?>
<tr>
<td><?php echo $c['id']; ?></td>
<td><?php echo htmlspecialchars($c['property_name']); ?></td>
<td><?php echo htmlspecialchars($c['rent_amount']); ?></td>
<td><?php echo htmlspecialchars($c['commission_rate']); ?></td>
<td><?php echo htmlspecialchars($c['amount']); ?></td>
</tr>
<?php endwhile; ?>
</table>
<?php else: ?>
<p>No commission earned yet. Property must be rented, leased, or bought first.</p>
<?php endif; ?>
</div>
<?php endif; ?>

</body>
</html>