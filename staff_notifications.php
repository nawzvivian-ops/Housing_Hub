
<?php
session_start();
include "db_connect.php";
mysqli_report(MYSQLI_REPORT_OFF);
 
if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
 
$user_id = intval($_SESSION['user_id']);
$userQ   = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
$user    = mysqli_fetch_assoc($userQ);
 
if (!$user || strtolower($user['role']) !== 'staff') {
    echo "<h2 style='color:red;text-align:center;padding:40px'>Access Denied!</h2>"; exit();
}
 
// Send message
if (isset($_POST['send_message'])) {
    $tenant_id = intval($_POST['tenant_id']);
    $msg       = mysqli_real_escape_string($conn, $_POST['message']);
    mysqli_query($conn, "INSERT INTO messages (sender_role, sender_id, receiver_id, message)
        VALUES ('staff', '$user_id', '$tenant_id', '$msg')");
    header("Location: staff_notifications.php?chat=$tenant_id"); exit();
}
 
// Mark read via POST
if (isset($_POST['read'])) {
    $notif_id = intval($_POST['read']);
    mysqli_query($conn, "UPDATE notifications SET is_read=1, status='read' WHERE id='$notif_id'");
    header("Location: staff_notifications.php"); exit();
}
 
// Mark all read
if (isset($_GET['mark_all'])) {
    mysqli_query($conn, "UPDATE notifications SET is_read=1, status='read'");
    header("Location: staff_notifications.php"); exit();
}
 
$notifications = mysqli_query($conn, "SELECT n.*, t.fullname AS tenant_name FROM notifications n LEFT JOIN tenants t ON n.tenant_id=t.id ORDER BY n.id DESC");
$tenants       = mysqli_query($conn, "SELECT id, fullname FROM tenants ORDER BY fullname ASC");
$chatTenant    = intval($_GET['chat'] ?? 0);
$chatMessages  = null;
if ($chatTenant > 0) {
    $chatMessages = mysqli_query($conn, "SELECT * FROM messages
        WHERE (sender_role='staff' AND sender_id='$user_id' AND receiver_id='$chatTenant')
           OR (sender_role='tenant' AND sender_id='$chatTenant' AND receiver_id='$user_id')
        ORDER BY created_at ASC");
}
 
$unread_count = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM notifications WHERE is_read=0 OR status='unread'"))['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Notifications & Messages | HousingHub Staff</title>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{--ink:#04091a;--gold:#c8a43c;--gold-l:#e0c06a;--white:#fff;--muted:rgba(255,255,255,.45);--border:rgba(255,255,255,.08);--gb:rgba(200,164,60,.25);--sw:260px}
html,body{height:100%;font-family:"Outfit",sans-serif;background:var(--ink);color:var(--white);overflow-x:hidden}
body::before{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background:radial-gradient(ellipse 80% 60% at 80% 5%,rgba(14,90,200,.15),transparent 55%),radial-gradient(ellipse 50% 70% at 5% 95%,rgba(180,140,40,.1),transparent 50%)}
body::after{content:"";position:fixed;inset:0;z-index:0;pointer-events:none;background-image:linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);background-size:72px 72px}
 
/* TOPBAR */
.topbar{position:sticky;top:0;z-index:100;display:flex;align-items:center;justify-content:space-between;padding:15px 36px;background:var(--gold);border-bottom:1px solid rgba(0,0,0,.1);box-shadow:0 2px 20px rgba(0,0,0,.3)}
.topbar-title{font-family:"Cormorant Garamond",serif;font-size:20px;font-weight:700;color:var(--ink)}
.topbar-right{display:flex;align-items:center;gap:10px}
.back-btn{padding:8px 16px;background:rgba(4,9,26,.15);border:1px solid rgba(4,9,26,.2);border-radius:6px;color:var(--ink);font-size:12px;font-weight:700;text-decoration:none;letter-spacing:1px;transition:all .2s}
.back-btn:hover{background:rgba(4,9,26,.25)}
 
/* MAIN */
.main{position:relative;z-index:10;padding:28px 36px;display:grid;grid-template-columns:1fr 1fr;gap:20px;max-width:1200px;margin:0 auto}
 
/* CARDS */
.card{background:rgba(255,255,255,.03);border:1px solid var(--border);border-radius:12px;overflow:hidden}
.card-head{padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;background:rgba(200,164,60,.05)}
.card-head h2{font-family:"Cormorant Garamond",serif;font-size:18px;font-weight:700;color:var(--white)}
.mark-all{font-size:11px;color:var(--gold);text-decoration:none;font-weight:600;letter-spacing:.5px}
.mark-all:hover{color:var(--gold-l)}
.card-body{padding:0;max-height:520px;overflow-y:auto}
.card-body::-webkit-scrollbar{width:3px}.card-body::-webkit-scrollbar-thumb{background:var(--gb)}
 
/* NOTIFICATION ROWS */
.notif-row{display:flex;align-items:flex-start;gap:12px;padding:14px 20px;border-bottom:1px solid rgba(255,255,255,.04);transition:background .2s}
.notif-row:last-child{border-bottom:none}
.notif-row.unread{background:rgba(200,164,60,.04)}
.notif-row:hover{background:rgba(255,255,255,.03)}
.notif-dot{width:8px;height:8px;border-radius:50%;background:var(--gold);flex-shrink:0;margin-top:5px}
.notif-dot.read{background:rgba(255,255,255,.15)}
.notif-info{flex:1;min-width:0}
.notif-title{font-size:13px;font-weight:600;color:var(--white);margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.notif-tenant{font-size:11px;color:var(--muted)}
.notif-status{font-size:10px;font-weight:700;letter-spacing:.5px;text-transform:uppercase;padding:2px 8px;border-radius:10px}
.notif-status.unread{background:rgba(200,164,60,.15);color:var(--gold);border:1px solid var(--gb)}
.notif-status.read{background:rgba(22,163,74,.1);color:#86efac;border:1px solid rgba(22,163,74,.3)}
.mark-read-btn{background:none;border:none;color:var(--gold);font-size:11px;font-weight:600;cursor:pointer;font-family:"Outfit",sans-serif;padding:0;white-space:nowrap;text-decoration:underline}
.mark-read-btn:hover{color:var(--gold-l)}
.empty-state{text-align:center;padding:40px;color:var(--muted);font-size:14px}
 
/* CHAT */
.tenant-select-wrap{padding:16px 20px;border-bottom:1px solid var(--border)}
.tenant-select-wrap label{display:block;font-size:10px;font-weight:700;letter-spacing:1.5px;text-transform:uppercase;color:var(--gold);margin-bottom:6px}
.tenant-select-wrap select{width:100%;padding:10px 13px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none}
.tenant-select-wrap select option{background:var(--ink)}
.chat-area{padding:16px;max-height:320px;overflow-y:auto;display:flex;flex-direction:column;gap:8px}
.chat-area::-webkit-scrollbar{width:3px}.chat-area::-webkit-scrollbar-thumb{background:var(--gb)}
.msg-wrap{display:flex}
.msg-wrap.staff{justify-content:flex-end}
.msg-wrap.tenant{justify-content:flex-start}
.bubble{max-width:75%;padding:10px 14px;border-radius:12px;font-size:13px;line-height:1.5}
.bubble.staff{background:var(--gold);color:var(--ink);border-radius:12px 12px 2px 12px;font-weight:500}
.bubble.tenant{background:rgba(255,255,255,.08);color:var(--white);border:1px solid var(--border);border-radius:12px 12px 12px 2px}
.chat-form{padding:14px 20px;border-top:1px solid var(--border);display:flex;gap:10px}
.chat-form input{flex:1;padding:10px 14px;background:rgba(255,255,255,.05);border:1px solid var(--border);border-radius:7px;color:var(--white);font-family:"Outfit",sans-serif;font-size:13px;outline:none;transition:border-color .2s}
.chat-form input:focus{border-color:var(--gb)}
.chat-form input::placeholder{color:var(--muted)}
.send-btn{padding:10px 20px;background:var(--gold);border:none;border-radius:7px;color:var(--ink);font-family:"Outfit",sans-serif;font-size:12px;font-weight:700;letter-spacing:1px;cursor:pointer;transition:all .2s;white-space:nowrap}
.send-btn:hover{background:var(--gold-l);transform:translateY(-1px)}
.no-chat{padding:32px;text-align:center;color:var(--muted);font-size:13px}
 
@media(max-width:900px){
  .main{grid-template-columns:1fr;padding:16px}
  .topbar{padding:14px 20px}
}
</style>
</head>
<body>
 
<!-- TOPBAR -->
<div class="topbar">
  <div class="topbar-title">🔔 Notifications &amp; Messages</div>
  <div class="topbar-right">
    <span style="font-size:12px;color:rgba(4,9,26,.6)"><?= date('l, d F Y') ?></span>
    <a href="staff_dashboard.php" class="back-btn">← Dashboard</a>
  </div>
</div>
 
<div class="main">
 
  <!-- NOTIFICATIONS -->
  <div class="card">
    <div class="card-head">
      <h2>🔔 Notifications
        <?php if($unread_count > 0): ?>
          <span style="font-size:12px;font-family:'Outfit',sans-serif;font-weight:400;color:var(--muted);margin-left:8px"><?= $unread_count ?> unread</span>
        <?php endif; ?>
      </h2>
      <?php if($unread_count > 0): ?>
        <a href="?mark_all=1" class="mark-all">✓ Mark All Read</a>
      <?php endif; ?>
    </div>
    <div class="card-body">
      <?php
      $has_notifs = false;
      while($n = mysqli_fetch_assoc($notifications)):
        $has_notifs = true;
        $is_unread = ($n['status']==='unread' || $n['status']==='Unread' || $n['is_read']==0);
      ?>
      <div class="notif-row <?= $is_unread ? 'unread' : '' ?>">
        <div class="notif-dot <?= $is_unread ? '' : 'read' ?>"></div>
        <div class="notif-info">
          <div class="notif-title"><?= htmlspecialchars($n['title'] ?? '—') ?></div>
          <div class="notif-tenant"><?= htmlspecialchars($n['tenant_name'] ?? 'System') ?></div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;flex-shrink:0">
          <span class="notif-status <?= $is_unread ? 'unread' : 'read' ?>"><?= $is_unread ? 'Unread' : 'Read' ?></span>
          <?php if($is_unread): ?>
          <form method="POST" style="margin:0">
            <input type="hidden" name="read" value="<?= $n['id'] ?>">
            <button type="submit" class="mark-read-btn">Mark Read</button>
          </form>
          <?php endif; ?>
        </div>
      </div>
      <?php endwhile; ?>
      <?php if(!$has_notifs): ?>
        <div class="empty-state">🔔 No notifications yet.</div>
      <?php endif; ?>
    </div>
  </div>
 
  <!-- MESSAGES -->
  <div class="card">
    <div class="card-head"><h2>💬 Messages</h2></div>
 
    <div class="tenant-select-wrap">
      <label>Select Tenant to Chat</label>
      <form method="GET">
        <select name="chat" onchange="this.form.submit()">
          <option value="">— Choose a tenant —</option>
          <?php
          mysqli_data_seek($tenants, 0);
          while($t = mysqli_fetch_assoc($tenants)):
          ?>
          <option value="<?= $t['id'] ?>" <?= ($chatTenant==$t['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($t['fullname']) ?>
          </option>
          <?php endwhile; ?>
        </select>
      </form>
    </div>
 
    <?php if($chatTenant > 0): ?>
      <div class="chat-area" id="chatArea">
        <?php if(mysqli_num_rows($chatMessages) === 0): ?>
          <div style="text-align:center;color:var(--muted);font-size:13px;padding:20px">No messages yet. Start the conversation!</div>
        <?php else: while($m = mysqli_fetch_assoc($chatMessages)): ?>
          <div class="msg-wrap <?= $m['sender_role'] === 'staff' ? 'staff' : 'tenant' ?>">
            <div class="bubble <?= $m['sender_role'] === 'staff' ? 'staff' : 'tenant' ?>">
              <?= htmlspecialchars($m['message']) ?>
            </div>
          </div>
        <?php endwhile; endif; ?>
      </div>
      <form method="POST" class="chat-form">
        <input type="hidden" name="tenant_id" value="<?= $chatTenant ?>">
        <input type="text" name="message" placeholder="Type a message..." required autocomplete="off">
        <button type="submit" name="send_message" class="send-btn">Send ✈️</button>
      </form>
    <?php else: ?>
      <div class="no-chat">💬 Select a tenant above to start chatting.</div>
    <?php endif; ?>
  </div>
 
</div>
 
<script>
// Auto-scroll chat to bottom
const ca = document.getElementById('chatArea');
if (ca) ca.scrollTop = ca.scrollHeight;
</script>
</body>
</html>