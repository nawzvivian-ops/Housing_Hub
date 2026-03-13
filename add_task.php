<?php
session_start();
include "db_connect.php";

if (isset($_POST['add_task'])) {

    $title       = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $assigned_to = intval($_POST['assigned_to']);
    $due_date    = mysqli_real_escape_string($conn, $_POST['due_date']);
    $priority    = mysqli_real_escape_string($conn, $_POST['priority']);

    mysqli_query($conn, "
        INSERT INTO tasks (title, description, assigned_to, due_date, priority, status, assigned_by)
        VALUES ('$title','$description',$assigned_to,'$due_date','$priority','Pending','Admin')
    ");

    header("Location: admin_dashboard.php?page=staff_tasks");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Assign Task</title>
<style>
body {font-family:Segoe UI; background:lightblue; padding:30px;}
form {width:500px; margin:auto; background:white;  border: 3px solid blue;background: linear-gradient(135deg, #495757, #0ea5e9); padding:25px;
border-radius:12px; box-shadow:0 5px 15px rgba(27, 25, 25, 0.94);}
input, select, textarea {width:98%; padding:10px; margin:10px 0;}
button {width:100%; padding:12px; background:black; border:none;
color:white; font-size:16px; border-radius:6px;}
</style>
</head>
<body>

<form method="POST">
<h2 style="text-align:center;">TASK ASIGNMENT</h2>

<input type="text" name="title" placeholder="Task Title" required>

<textarea name="description" placeholder="Task Description"></textarea>

<select name="assigned_to" required>
<option value="">-- Select Staff --</option>
<?php
$staff = mysqli_query($conn, "SELECT id, fullname FROM users WHERE role='staff'");
while ($s = mysqli_fetch_assoc($staff)) {
    echo "<option value='{$s['id']}'>".htmlspecialchars($s['fullname'])."</option>";
}
?>
</select>

<input type="date" name="due_date">

<select name="priority">
<option value="Low">Low</option>
<option value="Medium" selected>Medium</option>
<option value="High">High</option>
</select>

<button type="submit" name="add_task">Assign Task</button>
</form>

</body>
</html>