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


/* Form should appear above overlay */
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
            background: #3b82f6;
            color: #fff;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background: #2563eb;
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
        a { color: #3b82f6; text-decoration: none; }
        a:hover { text-decoration: underline; }
        h2 { text-align: center; color: #3b82f8; }
        .password-box {
    position: relative;
}

.password-box input {
    width: 92%;
    padding-right: 40px; /* space for eye */
}

.toggle-eye {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    font-size: 18px;
    color: #555555;
}

.toggle-eye:hover {
    color: #000;
}
    </style>
</head>
<body>

<form method="POST" action="auth.php">
    <h2>CREATE ACCOUNT</h2>

    <?php
    if (isset($_SESSION['error'])) {
        echo "<div class='error'>" . htmlspecialchars($_SESSION['error']) . "</div>";
        unset($_SESSION['error']);
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
</form>

<!-- Place the script here -->
<script>
const roleSelect = document.querySelector('select[name="role"]');
const adminSecretInput = document.querySelector('input[name="admin_secret"]');

adminSecretInput.style.display = 'none'; // hide initially

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

    if (passField.type === "password") {
        passField.type = "text"; // show password
    } else {
        passField.type = "password"; // hide password
    }
}

</script>
</body>
</html>