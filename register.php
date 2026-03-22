<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HousingHub | Register</title>
    <style>
                 body { 
    font-family: Arial; 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    height: 100vh; 
    margin: 0;
    position: relative;
 
    background-image: url("image/gf.png");
    background-size: 120%;
    background-position: center;
    background-repeat: no-repeat;
    animation: moveBg 15s infinite alternate;
}
 
 
body::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.3);
    z-index: 0;
}
 
 
form {
    position: relative;
    z-index: 2;
 
    background: colorless;
    padding: 30px;
    border-radius: 10px;
    box-shadow: 0 3px 20px rgba(247, 166, 91, 0.99);
    min-width: 350px;
}
        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            width: 100%;
            padding: 10px;
            border: none;
            border-radius: 5px;
            background: #0b1e88;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background: #0b1e88;
        }
        .error {
            color: red;
            background: #fee;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 10px;
            text-align: center;
        }
        p { text-align: center; }
        a { color: #f1f1f5; text-decoration: none; }
        a:hover { text-decoration: underline; }
        h2 { text-align: center; color: #0b1e88; }
        .password-box {
    position: relative;
}
 
.password-box input {
    width: 92%;
    padding-right: 40px;
}
 
.toggle-eye {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
    color: #0b1e88;
}
 
.toggle-eye:hover {
    color: #0b1e88;
}
 
/* ── FORGOT PASSWORD ── */
.panel { display: none; }
.panel.active { display: block; }
 
.back-btn {
    background: none;
    border: none;
    color: #0b1e88;
    font-size: 13px;
    cursor: pointer;
    padding: 0;
    margin-bottom: 14px;
    text-decoration: underline;
    font-family: Arial;
}
.back-btn:hover { color: #000; }
 
.success {
    color: green;
    background: #efe;
    padding: 10px;
    border-radius: 5px;
    margin-bottom: 10px;
    text-align: center;
}
 
.forgot-link {
    display: block;
    text-align: center;
    font-size: 18px;
    color: #eeeff3;
    text-decoration: none;
    margin-top: 6px;
}
.forgot-link:hover { text-decoration: underline; }
    </style>
</head>
<body>
 
<!-- ══ REGISTER PANEL ══ -->
<form method="POST" action="auth.php" id="panel-register" class="panel active">
    <h2>CREATE ACCOUNT</h2>
 
    <?php
    if (isset($_SESSION['error'])) {
        echo "<div class='error'>" . htmlspecialchars($_SESSION['error']) . "</div>";
        unset($_SESSION['error']);
    }
    if (isset($_SESSION['success'])) {
        echo "<div class='success'>" . htmlspecialchars($_SESSION['success']) . "</div>";
        unset($_SESSION['success']);
    }
    ?>
 
    <input type="text" name="fullname" placeholder="Full Name" required>
    <input type="email" name="email" placeholder="Email" required>
    <div class="password-box">
        <input type="password" name="password" id="password" placeholder="Password" required>
        <span class="toggle-eye" onclick="togglePassword()">👁</span>
    </div>
 
    <select name="role" required>
        <option value="">Select Role</option>
        <option value="admin">Admin</option>
        <option value="staff">Staff</option>
        <option value="tenant">Tenant</option>
        <option value="guest">Guest</option>
        <option value="broker">Broker</option>
        <option value="propertyowner">Propertyowner</option>
    </select>
    <input type="password" name="admin_secret" placeholder="Admin Secret Key">
    <button type="submit" name="register">Create Account</button>
 
    <p>Already have an account? <a href="login.php">Login here</a></p>
    <a href="#" class="forgot-link" onclick="showPanel('forgot');return false;">Forgot password?</a>
</form>
 
<!-- ══ FORGOT PASSWORD PANEL ══ -->
<div id="panel-forgot" class="panel" style="position:relative;z-index:2;background:colorless;padding:30px;border-radius:10px;box-shadow:0 3px 20px rgba(247,166,91,0.99);min-width:350px;">
    <button  onclick="showPanel('register')"> Back to Register</button>
    <h2>Reset Password</h2>
    <p style="text-align:center;color:white;font-size:13px;margin-bottom:14px;">Enter your email and we'll send you a reset code.</p>
    <div id="forgot-msg"></div>
    <form onsubmit="sendReset(event)">
        <input type="email" id="reset-email" placeholder="Your email address" required>
        <button type="submit" id="reset-btn">Send Reset Code</button>
    </form>
</div>
 
<!-- ══ NEW PASSWORD PANEL ══ -->
<div id="panel-newpw" class="panel" style="position:relative;z-index:2;background:colorless;padding:30px;border-radius:10px;box-shadow:0 3px 20px rgba(247,166,91,0.99);min-width:350px;">
    <button class="back-btn" onclick="showPanel('register')"> Back to Register</button>
    <h2>Set New Password</h2>
    <p style="text-align:center;color:white;font-size:13px;margin-bottom:14px;">Enter the code from your email and your new password.</p>
    <div id="newpw-msg"></div>
    <form onsubmit="submitNewPw(event)">
        <input type="text" id="reset-token" placeholder="Reset code (from email)" required>
        <div class="password-box">
            <input type="password" id="new-pw" placeholder="New password" required style="width:92%">
            <span class="toggle-eye" onclick="toggleEye('new-pw',this)">👁</span>
        </div>
        <div class="password-box">
            <input type="password" id="confirm-pw" placeholder="Confirm new password" required style="width:92%">
            <span class="toggle-eye" onclick="toggleEye('confirm-pw',this)">👁</span>
        </div>
        <button type="submit" id="newpw-btn" style="margin-top:10px">Set New Password</button>
    </form>
</div>
 
<script>
const roleSelect = document.querySelector('select[name="role"]');
const adminSecretInput = document.querySelector('input[name="admin_secret"]');
 
adminSecretInput.style.display = 'none';
 
roleSelect.addEventListener('change', () => {
    if (roleSelect.value === 'admin') {
        adminSecretInput.style.display = 'block';
        adminSecretInput.required = true;
    } else {
        adminSecretInput.style.display = 'none';
        adminSecretInput.required = false;
    }
});
 
function togglePassword() {
    let passField = document.getElementById("password");
    passField.type = passField.type === "password" ? "text" : "password";
}
 
function toggleEye(id, el) {
    let f = document.getElementById(id);
    f.type = f.type === "password" ? "text" : "password";
    el.textContent = f.type === "password" ? "👁" : "🙈";
}
 
function showPanel(name) {
    document.querySelectorAll('.panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panel-' + name).classList.add('active');
}
 
function sendReset(e) {
    e.preventDefault();
    const email = document.getElementById('reset-email').value.trim();
    const btn   = document.getElementById('reset-btn');
    const msg   = document.getElementById('forgot-msg');
    btn.disabled = true; btn.textContent = 'Sending...';
    msg.innerHTML = '';
    fetch('forgot_password.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'email=' + encodeURIComponent(email)
    })
    .then(r => r.json())
    .then(d => {
        msg.innerHTML = '<div class="' + (d.success ? 'success' : 'error') + '">' + d.message + '</div>';
        if (d.success) setTimeout(() => showPanel('newpw'), 1800);
    })
    .catch(() => {
        msg.innerHTML = '<div class="error">Network error. Please try again.</div>';
    })
    .finally(() => { btn.disabled = false; btn.textContent = 'Send Reset Code'; });
}
 
function submitNewPw(e) {
    e.preventDefault();
    const token   = document.getElementById('reset-token').value.trim();
    const newpw   = document.getElementById('new-pw').value;
    const confirm = document.getElementById('confirm-pw').value;
    const btn     = document.getElementById('newpw-btn');
    const msg     = document.getElementById('newpw-msg');
    if (newpw !== confirm) { msg.innerHTML = '<div class="error">Passwords do not match.</div>'; return; }
    if (newpw.length < 6)  { msg.innerHTML = '<div class="error">Password must be at least 6 characters.</div>'; return; }
    btn.disabled = true; btn.textContent = 'Updating...';
    msg.innerHTML = '';
    fetch('reset_password.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'token=' + encodeURIComponent(token) + '&password=' + encodeURIComponent(newpw)
    })
    .then(r => r.json())
    .then(d => {
        msg.innerHTML = '<div class="' + (d.success ? 'success' : 'error') + '">' + d.message + '</div>';
        if (d.success) setTimeout(() => { window.location.href = 'login.php'; }, 2200);
    })
    .catch(() => {
        msg.innerHTML = '<div class="error">Network error. Please try again.</div>';
    })
    .finally(() => { btn.disabled = false; btn.textContent = 'Set New Password'; });
}
</script>
</body>
</html>