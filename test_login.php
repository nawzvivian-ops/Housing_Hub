<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include "db_connect.php";

echo "<h2>Login System Debug Test</h2>";

// Test 1: Database Connection
if ($conn) {
    echo "✓ Database connection successful<br><br>";
} else {
    echo "✗ Database connection failed<br><br>";
    exit;
}

// Test 2: Check if users table exists
$table_check = mysqli_query($conn, "SHOW TABLES LIKE 'users'");
if (mysqli_num_rows($table_check) > 0) {
    echo "✓ Users table exists<br><br>";
} else {
    echo "✗ Users table does NOT exist<br><br>";
    exit;
}

// Test 3: Count users
$count_query = mysqli_query($conn, "SELECT COUNT(*) as total FROM users");
$count = mysqli_fetch_assoc($count_query)['total'];
echo "✓ Total users in database: $count<br><br>";

// Test 4: Show all users
if ($count > 0) {
    echo "<h3>Registered Users:</h3>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><th>ID</th><th>Full Name</th><th>Email</th><th>Role</th></tr>";
    
    $users = mysqli_query($conn, "SELECT id, fullname, email, role FROM users");
    while ($user = mysqli_fetch_assoc($users)) {
        echo "<tr>";
        echo "<td>" . $user['id'] . "</td>";
        echo "<td>" . htmlspecialchars($user['fullname']) . "</td>";
        echo "<td>" . htmlspecialchars($user['email']) . "</td>";
        echo "<td>" . htmlspecialchars($user['role']) . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
}

// Test 5: Session status
echo "<h3>Session Status:</h3>";
if (isset($_SESSION['user_id'])) {
    echo "✓ You are logged in<br>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "Role: " . $_SESSION['role'] . "<br>";
    echo "<br><a href='dashboard.php'>Go to Dashboard</a> | <a href='logout.php'>Logout</a>";
} else {
    echo "✗ You are NOT logged in<br>";
    echo "<br><a href='login.php'>Go to Login</a> | <a href='register.php'>Register</a>";
}
?>