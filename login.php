<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HousingHub | Login</title>
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
    box-shadow: 0 5px 15px rgba(240, 145, 22, 0.99);
    min-width: 350px;
}
        input {
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
            background: #344cd3;
            color: #ffffff;
            font-weight: bold;
            cursor: pointer;
        }
        button:hover {
            background: #344cd3;
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
        a { color: #344cd3; text-decoration: none; }
        a:hover { text-decoration: underline; }
        h2 { text-align: center; color: #344cd3; }
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
    color: #3023e2;
}

.toggle-eye:hover {
    color: #000;
}


    </style>
</head>
<body>

<form method="POST" action="auth.php">
    <h2>LOGIN TO HOUSINGHUB</h2>

    <?php
    if (isset($_SESSION['error'])) {
        echo "<div class='error'>" . htmlspecialchars($_SESSION['error']) . "</div>";
        unset($_SESSION['error']);
    }
    ?>

    <input type="email" name="email" placeholder="Email" required>
   <div class="password-box">
    <input type="password" name="password" id="password" placeholder="Password" required>
    <span class="toggle-eye" onclick="togglePassword()">👁</span>
</div>
    <button type="submit" name="login">Login</button>

    <p>Don't have an account? <a href="register.php">Create Account</a></p>
    <p><a href="index.php">← Back to Home</a></p>
</form>
<script>
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