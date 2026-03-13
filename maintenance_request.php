<?php
session_start();
include "db_connect.php";

$id = $_POST['id'];

if (isset($_POST['send'])) {
    $issue = $_POST['issue'];

    mysqli_query($conn,
        "INSERT INTO maintenance (tenant_id, issue, status, created_at)
         VALUES ('$id','$issue','Pending',NOW())"
    );

    echo "<script>alert('Request Submitted');</script>";
}

$requests = mysqli_query($conn,
    "SELECT * FROM maintenance WHERE tenant_id='$id' ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html>
<head>
<title>Maintenance Requests</title>
<style>
body{font-family:Arial;background:#f4f7fb;}
.box{
    max-width:800px;
    margin:30px auto;
    background:white;
    padding:20px;
    border-radius:12px;
}
textarea{width:100%;padding:10px;}
button{
    padding:10px 15px;
    border:none;
    border-radius:8px;
    background:#f97316;
    color:white;
}
</style>
</head>
<body>

<div class="box">
<h2>🛠 Maintenance Requests</h2>

<form method="POST">
    <textarea name="issue" placeholder="Describe issue..." required></textarea><br><br>
    <button name="send">Submit Request</button>
</form>

<h3>Request History</h3>
<ul>
<?php while($r=mysqli_fetch_assoc($requests)): ?>
<li>
<b><?php echo $r['issue']; ?></b> -
Status: <?php echo $r['status']; ?>
</li>
<?php endwhile; ?>
</ul>

<a href="tenants.php">⬅ Back</a>
</div>

</body>
</html>