<?php
session_start();
include "db_connect.php";

# --- 1. Staff Login Check
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

# --- 2. Send Message to Tenant
if (isset($_POST['send_message'])) {

    $tenant_id = intval($_POST['tenant_id']);
    $msg = mysqli_real_escape_string($conn, $_POST['message']);

    mysqli_query($conn, "
        INSERT INTO messages (sender_role, sender_id, receiver_id, message)
        VALUES ('staff', '$user_id', '$tenant_id', '$msg')
    ");

    header("Location: staff_notifications.php?chat=$tenant_id");
    exit();
}

# --- 3. Mark Notification as Read
if (isset($_POST['read'])) {
    $notif_id = intval($_POST['read']);

    mysqli_query($conn, "
        UPDATE notifications 
        SET status='Read'
        WHERE id='$notif_id'
    ");

    header("Location: staff_notifications.php");
    exit();
}

# --- 4. Fetch Notifications
$notifications = mysqli_query($conn, "
    SELECT n.*, t.fullname AS tenant_name
    FROM notifications n
    LEFT JOIN tenants t ON n.tenant_id = t.id
    ORDER BY n.id DESC
");

# --- 5. Fetch Tenants for Messaging Dropdown
$tenants = mysqli_query($conn, "
    SELECT id, fullname 
    FROM tenants 
    ORDER BY fullname ASC
");

# --- 6. Chat Tenant Selection
$chatTenant = intval($_GET['chat'] ?? 0);

# --- 7. Fetch Messages if Tenant Selected
$chatMessages = null;

if ($chatTenant > 0) {
    $chatMessages = mysqli_query($conn, "
        SELECT * FROM messages
        WHERE (sender_role='staff' AND sender_id='$user_id' AND receiver_id='$chatTenant')
           OR (sender_role='tenant' AND sender_id='$chatTenant' AND receiver_id='$user_id')
        ORDER BY created_at ASC
    ");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Staff Notifications & Messages</title>
    <style>
        body{
            font-family:Segoe UI;
            background:lightblue;
            padding:30px;
        }

        h1{
            color:black;
        }

        .container{
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:20px;
            margin-top:20px;
        }

        .box{
            background:white;
            padding:20px;
            border-radius:15px;
            box-shadow:0 4px 10px rgba(0,0,0,0.1);
        }

        table{
            width:100%;
            border-collapse:collapse;
        }

        th, td{
            padding:10px;
            border-bottom:1px solid #ddd;
        }

        th{
            background:#2563eb;
            color:white;
        }

        .unread{
            color:red;
            font-weight:bold;
        }

        .read{
            color:green;
        }

        .chat-area{
            height:300px;
            overflow-y:auto;
            padding:10px;
            border:1px solid #ddd;
            border-radius:10px;
            background:#f9fafb;
            margin-bottom:10px;
        }

        .msg-staff{
            text-align:right;
            margin:8px;
            color:white;
        }

        .msg-staff span{
            background:#2563eb;
            padding:8px 12px;
            border-radius:12px;
            display:inline-block;
        }

        .msg-tenant{
            text-align:left;
            margin:8px;
        }

        .msg-tenant span{
            background:#e5e7eb;
            padding:8px 12px;
            border-radius:12px;
            display:inline-block;
        }

        input, select{
            width:100%;
            padding:10px;
            margin-top:8px;
            border-radius:8px;
            border:1px solid #ccc;
        }

        button{
            padding:10px 15px;
            background:#2563eb;
            color:white;
            border:none;
            border-radius:8px;
            margin-top:10px;
            cursor:pointer;
        }

        button:hover{
            background:#1d4ed8;
        }

        a{
            text-decoration:none;
            color:#2563eb;
        }
    </style>
</head>

<body>
<a href="staff_dashboard.php">← PREVIOUS</a>
<h1>Staff Notifications & Messages</h1>

<div class="container" >

    <!-- LEFT: Notifications -->
    <div class="box"style="border:3px solid #162cf5;">
        <h2><img src="images/notification.png" alt="Photo" width="80px" height="70px"> Notifications</h2>

        <table>
            <tr>
                <th>Tenant</th>
                <th>Title</th>
                <th>Status</th>
                <th>Action</th>
            </tr>

            <?php while($n = mysqli_fetch_assoc($notifications)): ?>
            <tr>
                <td><?= htmlspecialchars($n['tenant_name'] ?? '-') ?></td>
                <td><?= htmlspecialchars($n['title']) ?></td>

                <td class="<?= strtolower($n['status']) ?>">
                    <?= $n['status'] ?>
                </td>

                <td>
                    <?php if($n['status']=="Unread"): ?>
                        <a href="?read=<?= $n['id'] ?>">Mark Read</a>
                    <?php else: ?>
                        Done
                    <?php endif; ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </table>
    </div>


    <!-- RIGHT: Messaging -->
    <div class="box"style="border:3px solid #162cf5;">
        <h2><img src="images/message.png" alt="Photo" width="80px" height="70px"> Messages</h2>

        <form method="GET">
            <label>Select Tenant to Chat:</label>
            <select name="chat" onchange="this.form.submit()">
                <option value="">--Choose Tenant--</option>
                <?php
                mysqli_data_seek($tenants, 0);
                while($t = mysqli_fetch_assoc($tenants)):
                ?>
                    <option value="<?= $t['id'] ?>"
                        <?= ($chatTenant==$t['id']) ? "selected" : "" ?>>
                        <?= htmlspecialchars($t['fullname']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </form>

        <?php if($chatTenant > 0): ?>

            <div class="chat-area">
                <?php while($m = mysqli_fetch_assoc($chatMessages)): ?>

                    <?php if($m['sender_role']=="staff"): ?>
                        <div class="msg-staff">
                            <span><?= htmlspecialchars($m['message']) ?></span>
                        </div>
                    <?php else: ?>
                        <div class="msg-tenant">
                            <span><?= htmlspecialchars($m['message']) ?></span>
                        </div>
                    <?php endif; ?>

                <?php endwhile; ?>
            </div>

            <form method="POST">
                <input type="hidden" name="tenant_id" value="<?= $chatTenant ?>">
                <input type="text" name="message" placeholder="Type message..." required>
                <button type="submit" name="send_message">Send</button>
            </form>

        <?php else: ?>
            <p style="color:gray;">Select a tenant to start chatting.</p>
        <?php endif; ?>
    </div>

</div>

</body>
</html>