<?php
session_start();
include "db_connect.php";

// FIX: Check session instead of POST to eliminate the "Undefined array key" error
//if (!isset($_SESSION['user_id'])) {
   // header("Location: login.php");
   // exit();
//}

$tenant_id = $_SESSION['user_id'];

// Handle Form Submission
if (isset($_POST['send'])) {
    $issue = mysqli_real_escape_string($conn, $_POST['issue']);

    $insert = mysqli_query($conn,
        "INSERT INTO maintenance (tenant_id, issue, status, created_at)
         VALUES ('$tenant_id','$issue','Pending',NOW())"
    );

    if($insert) {
        echo "<script>alert('Maintenance Request Submitted');</script>";
    }
}

// Fetch history
$requests = mysqli_query($conn,
    "SELECT * FROM maintenance WHERE tenant_id='$tenant_id' ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance &ndash; HousingHub</title>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Outfit:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --ink: #04091a;
            --gold: #c8a43c;
            --gold-l: #e0c06a;
            --white: #fff;
            --muted: rgba(255,255,255,.45);
            --border: rgba(255,255,255,.07);
        }

        *,*::before,*::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Outfit', sans-serif;
            background: var(--ink);
            color: var(--white);
            min-height: 100vh;
            overflow-x: hidden;
            cursor: none; /* Custom Cursor */
        }

        /* Luxury Backgrounds */
        .page-bg { position: fixed; inset: 0; z-index: -1; background: radial-gradient(ellipse 100% 60% at 80% 10%,rgba(14,90,200,0.15) 0%,transparent 55%), var(--ink); }
        .page-grid { position: fixed; inset: 0; z-index: -1; background-image: linear-gradient(rgba(255,255,255,.02) 1px,transparent 1px), linear-gradient(90deg,rgba(255,255,255,.02) 1px,transparent 1px); background-size: 60px 60px; }

        /* Custom Cursor Elements */
        #cur-dot, #cur-ring { position: fixed; top: 0; left: 0; pointer-events: none; z-index: 9999; transform: translate(-50%, -50%); }
        #cur-dot { width: 8px; height: 8px; background: var(--gold); border-radius: 50%; }
        #cur-ring { width: 25px; height: 25px; border: 1.5px solid var(--gold); border-radius: 50%; transition: width 0.3s, height 0.3s; }

        .container { max-width: 700px; margin: 60px auto; padding: 0 20px; position: relative; z-index: 10; }
        
        .header { margin-bottom: 40px; }
        .header h2 { font-family: 'Cormorant Garamond', serif; font-size: 36px; color: var(--gold); }
        .header p { color: var(--muted); font-size: 14px; letter-spacing: 1px; text-transform: uppercase; }

        .card { background: rgba(255,255,255,0.03); border: 1px solid var(--border); padding: 30px; border-radius: 16px; backdrop-filter: blur(10px); }

        textarea {
            width: 100%; height: 120px;
            background: rgba(255,255,255,0.05); border: 1px solid var(--border);
            border-radius: 12px; color: white; padding: 15px; font-family: inherit;
            margin-bottom: 20px; outline: none; transition: border-color 0.3s;
        }
        textarea:focus { border-color: var(--gold); }

        button {
            width: 100%; padding: 16px; background: var(--gold); color: var(--ink);
            border: none; border-radius: 8px; font-weight: 700; text-transform: uppercase;
            letter-spacing: 2px; cursor: pointer; transition: 0.3s;
        }
        button:hover { background: var(--gold-l); transform: translateY(-2px); }

        .history { margin-top: 50px; }
        .history h3 { font-family: 'Cormorant Garamond', serif; font-size: 24px; margin-bottom: 20px; color: var(--gold); }

        .request-item {
            background: rgba(255,255,255,0.02); border: 1px solid var(--border);
            padding: 15px 20px; border-radius: 10px; margin-bottom: 12px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .status-badge {
            font-size: 10px; font-weight: 700; text-transform: uppercase;
            padding: 4px 10px; border-radius: 20px; letter-spacing: 1px;
            background: rgba(200,164,60,0.1); color: var(--gold);
        }

        .back-link { display: inline-block; margin-top: 30px; color: var(--muted); text-decoration: none; font-size: 12px; text-transform: uppercase; letter-spacing: 1.5px; }
        .back-link:hover { color: var(--gold); }
    </style>
</head>
<body>

<div id="cur-dot"></div>
<div id="cur-ring"></div>
<div class="page-bg"></div>
<div class="page-grid"></div>

<div class="container">
    <div class="header">
        <p>Tenant Services</p>
        <h2>Maintenance <em>Requests</em></h2>
    </div>

    <div class="card">
        <form method="POST">
            <textarea name="issue" placeholder="Describe the issue (e.g., Leaking tap in the master bathroom)..." required></textarea>
            <button type="submit" name="send">Submit Request</button>
        </form>
    </div>

    <div class="history">
        <h3>Request History</h3>
        <?php if(mysqli_num_rows($requests) > 0): ?>
            <?php while($r=mysqli_fetch_assoc($requests)): ?>
                <div class="request-item">
                    <span><?php echo htmlspecialchars($r['issue']); ?></span>
                    <span class="status-badge"><?php echo $r['status']; ?></span>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="color:var(--muted);">No requests found.</p>
        <?php endif; ?>
    </div>

    <a href="Tenant.php" class="back-link">&larr; Back to Dashboard</a>
</div>

<script>
    // Custom Cursor Logic
    const dot = document.getElementById('cur-dot');
    const ring = document.getElementById('cur-ring');
    let mx = 0, my = 0, rx = 0, ry = 0;

    window.addEventListener('mousemove', (e) => {
        mx = e.clientX;
        my = e.clientY;
        dot.style.transform = `translate3d(${mx}px, ${my}px, 0) translate(-50%, -50%)`;
    });

    function animate() {
        rx += (mx - rx) * 0.15;
        ry += (my - ry) * 0.15;
        ring.style.transform = `translate3d(${rx}px, ${ry}px, 0) translate(-50%, -50%)`;
        requestAnimationFrame(animate);
    }
    animate();
</script>

</body>
</html>