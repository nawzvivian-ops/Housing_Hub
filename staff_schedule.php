<?php
session_start();
include "db_connect.php";

# --- Staff Login Check
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = intval($_SESSION['user_id']);

$userQ = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user = mysqli_fetch_assoc($userQ);

if (!$user || strtolower($user['role']) !== 'staff') {
    echo "<h2 style='color:red;text-align:center;'>Access Denied!</h2>";
    exit();
}

# --- Add New Event
if (isset($_POST['add_event'])) {

    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $event_date = $_POST['event_date'];
    $event_time = $_POST['event_time'];
    $location = mysqli_real_escape_string($conn, $_POST['location']);
    $notes = mysqli_real_escape_string($conn, $_POST['notes']);

    mysqli_query($conn, "
        INSERT INTO schedule (staff_id, title, event_date, event_time, location, notes)
        VALUES ('$user_id', '$title', '$event_date', '$event_time', '$location', '$notes')
    ");

    header("Location: staff_schedule.php");
    exit();
}

# --- Mark Completed
if (isset($_POST['done'])) {
    $id = intval($_POST['done']);
    mysqli_query($conn, "UPDATE schedule SET status='Completed' WHERE id='$id'");
    header("Location: staff_schedule.php");
    exit();
}

# --- Delete Event
if (isset($_POST['delete'])) {
    $id = intval($_POST['delete']);
    mysqli_query($conn, "DELETE FROM schedule WHERE id='$id'");
    header("Location: staff_schedule.php");
    exit();
}

# --- Fetch Events
$events = mysqli_query($conn, "
    SELECT * FROM schedule
    WHERE staff_id='$user_id'
    ORDER BY event_date ASC, event_time ASC
");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Schedule</title>
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

        .wrapper{
            display:grid;
            grid-template-columns:1fr 2fr;
            gap:20px;
            margin-top:30px;
        }

        .card{
            background:white;
            padding:20px;
            border-radius:15px;
            box-shadow:0 4px 12px rgba(0,0,0,0.1);
        }

        input, textarea{
            width:98%;
            padding:10px;
            margin:10px 0;
            border-radius:10px;
            border:1px solid #ccc;
        }

        button{
            width:98%;
            padding:12px;
            background:#2563eb;
            color:white;
            border:none;
            border-radius:10px;
            cursor:pointer;
        }

        button:hover{
            background:#1d4ed8;
        }

        table{
            width:100%;
            border-collapse:collapse;
            margin-top:15px;
        }

        th, td{
            padding:10px;
            border-bottom:1px solid #ddd;
            text-align:left;
        }

        th{
            background:#2563eb;
            color:white;
        }

        .status-upcoming{
            color:orange;
            font-weight:bold;
        }

        .status-completed{
            color:green;
            font-weight:bold;
        }

        .btn{
            padding:6px 10px;
            border-radius:8px;
            text-decoration:none;
            font-size:14px;
        }

        .done{
            background:green;
            color:white;
        }

        .delete{
            background:red;
            color:white;
        }
    </style>
</head>
<body>

<h1>Staff Schedule</h1>
<p style="text-align:center;">Welcome <b><?= htmlspecialchars($user['fullname']) ?></b></p>

<div class="wrapper">

    <!-- Add Event -->
    <div class="card" style="border:3px solid #0730e6;">
        <h2>Add New Appointment</h2>

        <form method="POST">
            <input type="text" name="title" placeholder="Event Title" required>

            <input type="date" name="event_date" required>

            <input type="time" name="event_time" required>

            <input type="text" name="location" placeholder="Location (optional)">

            <textarea name="notes" placeholder="Extra notes..."></textarea>

            <button type="submit" name="add_event">Add Event</button>
        </form>
    </div>


    <!-- Schedule List -->
    <div class="card" style="border:3px solid #0730e6;">
        <h2>Upcoming Events</h2>

        <table>
            <tr>
                <th>Title</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>

            <?php while($e = mysqli_fetch_assoc($events)): ?>
            <tr>
                <td><?= htmlspecialchars($e['title']) ?></td>
                <td><?= htmlspecialchars($e['event_date']) ?></td>
                <td><?= htmlspecialchars($e['event_time']) ?></td>

                <td class="status-<?= strtolower($e['status']) ?>">
                    <?= $e['status'] ?>
                </td>

                <td>
                    <?php if($e['status']=="Upcoming"): ?>
                        <a class="btn done" href="?done=<?= $e['id'] ?>">✔ Done</a>
                    <?php endif; ?>

                    <a class="btn delete" href="?delete=<?= $e['id'] ?>"
                       onclick="return confirm('Delete this event?')">
                       ✖ Delete
                    </a>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>

    </div>

</div>

</body>
</html>