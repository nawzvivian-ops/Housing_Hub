<?php
session_start();
include "db_connect.php";

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
 
// Check if user is staff
$user_id = intval($_SESSION['user_id']);
$userQ = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($userQ);
if (!$user || strtolower($user['role']) !== 'staff') {
    echo "<h2 style='color:red;text-align:center;'>Access Denied!</h2>";
    exit();
}

// Handle new task submission
if (isset($_POST['add_task'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $priority = mysqli_real_escape_string($conn, $_POST['priority']);
    $due_date = mysqli_real_escape_string($conn, $_POST['due_date']);

    mysqli_query($conn, "INSERT INTO tasks (staff_id, title, description, priority, due_date, assigned_by) VALUES ('$user_id', '$title', '$description', '$priority', '$due_date', 'Admin')");
    header("Location: staff_tasks.php");
    exit();
}

// Handle marking task complete
if (isset($_POST['complete'])) {
    $task_id = intval($_POST['complete']);
    mysqli_query($conn, "UPDATE tasks SET status='completed' WHERE id='$task_id' AND staff_id='$user_id'");
    header("Location: staff_tasks.php");
    exit();
}

// Handle deleting task
if (isset($_POST['delete'])) {
    $task_id = intval($_POST['delete']);
    mysqli_query($conn, "DELETE FROM tasks WHERE id='$task_id' AND staff_id='$user_id'");
    header("Location: staff_tasks.php");
    exit();
}

// Filters & Search
$filter_status = $_POST['status'] ?? '';
$filter_priority = $_POST['priority'] ?? '';
$search = $_POST['search'] ?? '';

$where = "WHERE staff_id='$user_id'";
if ($filter_status) $where .= " AND status='$filter_status'";
if ($filter_priority) $where .= " AND priority='$filter_priority'";
if ($search) $where .= " AND (title LIKE '%$search%' OR description LIKE '%$search%')";

$tasksQ = mysqli_query($conn, "SELECT * FROM tasks $where ORDER BY created_at DESC");

// Quick stats
$totalQ = mysqli_query($conn, "SELECT COUNT(*) as total FROM tasks WHERE staff_id='$user_id'");
$completedQ = mysqli_query($conn, "SELECT COUNT(*) as completed FROM tasks WHERE staff_id='$user_id' AND status='completed'");
$pendingQ = mysqli_query($conn, "SELECT COUNT(*) as pending FROM tasks WHERE staff_id='$user_id' AND status='pending'");
$overdueQ = mysqli_query($conn, "SELECT COUNT(*) as overdue FROM tasks WHERE staff_id='$user_id' AND status='pending' AND due_date < CURDATE()");

$total = mysqli_fetch_assoc($totalQ)['total'];
$completed = mysqli_fetch_assoc($completedQ)['completed'];
$pending = mysqli_fetch_assoc($pendingQ)['pending'];
$overdue = mysqli_fetch_assoc($overdueQ)['overdue'];

?>

<!DOCTYPE html>
<html>
<head>
    <title>Task Manager</title>
    <style>
        body { font-family:Segoe UI; background:Lightblue; padding:30px; }
        h1 { color:#2563eb; }
        form { background:white; padding:20px; border:5px solid #0e0d0d; margin-bottom:20px; }
        input, textarea, select { width:96%; padding:10px; margin:10px 0; border-radius:5px; border:1px solid #ccc; }
        button { padding:10px 20px; border:none; background:#2563eb; color:white; border-radius:5px; cursor:pointer; }
        button:hover { background:#1d4ed8; }
        table { width:96%; border-collapse:collapse; margin-top:20px; }
        th, td { border:1px solid #ccc; padding:10px; text-align:left; }
        th { background:#2563eb; color:white; }
        a { text-decoration:none; color:black; }
        a:hover { text-decoration:underline; }
        .completed { text-decoration:line-through; color:gray; }
        .overdue { background:#fee; }
        .stats { background:white; padding:15px; border:3px solid #111010;border-radius:30px; margin-bottom:20px; display:flex; gap:20px; }
        .stats div { flex:1; text-align:center;border:1px solid #141414; padding:10px; border-radius:50%; background:#2563eb; color:white; }
    </style>
</head>
<body>
    <a href="staff_dashboard.php">← Back to Staff Dashboard</a><br><br>

<div class="stats">
   <h1>Task Manager</h1>
   <p>Staff, <?php echo htmlspecialchars($user['fullname']); ?></p> 
</div>

<!-- Quick Stats -->
<div class="stats">
    <div>Total: <?php echo $total; ?></div>
    <div>Completed: <?php echo $completed; ?></div>
    <div>Pending: <?php echo $pending; ?></div>
    <div>Overdue: <?php echo $overdue; ?></div>
</div>

<!-- Filter & Search -->
<form method="POST" action="">
    <h3>Filter / Search Tasks</h3>
    <select name="status">
        <option value="">All Status</option>
        <option value="pending" <?php if($filter_status=='pending') echo 'selected'; ?>>Pending</option>
        <option value="completed" <?php if($filter_status=='completed') echo 'selected'; ?>>Completed</option>
    </select>
    <select name="priority">
        <option value="">All Priority</option>
        <option value="Low" <?php if($filter_priority=='Low') echo 'selected'; ?>>Low</option>
        <option value="Medium" <?php if($filter_priority=='Medium') echo 'selected'; ?>>Medium</option>
        <option value="High" <?php if($filter_priority=='High') echo 'selected'; ?>>High</option>
    </select>
    <input type="text" name="search" placeholder="Search by title or description" value="<?php echo htmlspecialchars($search); ?>">
    <button type="submit">Apply</button>
</form>

<!-- Add Task Form -->
<form method="POST" action="">
    <h3>Add New Task</h3>
    <input type="text" name="title" placeholder="Task Title" required>
    <textarea name="description" placeholder="Task Description"></textarea>
    <select name="priority" required>
        <option value="Low">Low</option>
        <option value="Medium" selected>Medium</option>
        <option value="High">High</option>
    </select>
    <input type="date" name="due_date" required>
    <button type="submit" name="add_task">Add Task</button>
</form>

<!-- Task List -->
<table>
    <tr>
        <th>#</th>
        <th>Title</th>
        <th>Description</th>
        <th>Priority</th>
        <th>Due Date</th>
        <th>Status</th>
        <th>Assigned By</th>
        <th>Actions</th>
    </tr>
    <?php $i=1; while($task = mysqli_fetch_assoc($tasksQ)): ?>
    <tr class="<?php if($task['status']=='pending' && $task['due_date']<date('Y-m-d')) echo 'overdue'; ?>">
        <td><?php echo $i++; ?></td>
        <td class="<?php echo $task['status']=='completed' ? 'completed' : ''; ?>"><?php echo htmlspecialchars($task['title']); ?></td>
        <td class="<?php echo $task['status']=='completed' ? 'completed' : ''; ?>"><?php echo htmlspecialchars($task['description']); ?></td>
        <td><?php echo $task['priority']; ?></td>
        <td><?php echo $task['due_date']; ?></td>
        <td><?php echo ucfirst($task['status']); ?></td>
        <td><?php echo htmlspecialchars($task['assigned_by']); ?></td>
        <td>
            <?php if($task['status'] != 'completed'): ?>
                <a href="?complete=<?php echo $task['id']; ?>">Mark Complete</a> |
            <?php endif; ?>
            <a href="?delete=<?php echo $task['id']; ?>" onclick="return confirm('Delete this task?')">Delete</a>
        </td>
    </tr>
    <?php endwhile; ?>
</table>

</body>
</html>