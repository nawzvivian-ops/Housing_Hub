<?php
session_start();

// Servers often prefer index.php over index.html for the bare domain.
// Send plain site visits to the main public home page.
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH);
if ($requestPath === '/' || $requestPath === '') {
    header("Location: index.html");
    exit();
}

function dashboard_for_role($role) {
    $role = strtolower(trim((string)$role));
    if ($role === 'admin') return 'admin_dashboard.php';
    if ($role === 'staff') return 'staff_dashboard.php';
    if ($role === 'broker') return 'broker_dashboard.php';
    if ($role === 'guest') return 'guests.php';
    if ($role === 'propertyowner' || $role === 'owner') return 'propertyowner_dashboard.php';
    return 'dashboard.php';
}

// If already logged in, redirect to the dashboard for their role
if(isset($_SESSION['user_id'])){
    header("Location: " . dashboard_for_role($_SESSION['role'] ?? 'tenant'));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>HousingHub | Home</title>
    <style>
         body { 
    font-family: Arial; 
    display: flex; 
    justify-content: center; 
    align-items: center; 
    height: 100vh; 
    margin: 0;
    position: relative;

    background-image: url("images/bes.png");
    background-size: 120%;
    background-position: center;
    background-repeat: no-repeat;
    animation: moveBg 15s infinite alternate;
}

@keyframes moveBg {
    from {
        background-position: center;
    }
    to {
        background-position: top;
    }
}
body::before {
    content: "";
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.3);
    z-index: 0;
}

.container {
    position: relative;
    z-index: 2;

    text-align: center;
    background: rgba(199, 189, 167, 0.92);
    padding: 50px;
    border-radius: 10px;
    box-shadow: 0 10px 40px rgba(0,0,0,0.3);
    max-width: 500px;
}
        h1 { 
            color: #333; 
            margin-bottom: 10px;
            font-size: 42px;
        }
        p { 
            color: #666; 
            margin-bottom: 30px;
            font-size: 18px;
        }
        .buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-bottom: 30px;
        }
        a, button { 
            padding: 15px 40px; 
            text-decoration: none; 
            border-radius: 8px; 
            font-weight: bold;
            font-size: 16px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }
        .btn-login {
            background: #f85f06;
            color: white;
        }
        .btn-login:hover {
            background: #172010;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }
        .btn-register {
            background: #080f6d;
            color: white;
        }
        .btn-register:hover {
            background: #172010;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.3);
        }
        .btn-test {
            background: #f59e0b;
            color: white;
            display: inline-block;
            padding: 10px 30px;
            font-size: 14px;
        }
        .btn-test:hover {
            background: #d97706;
        }
        .divider {
            margin: 20px 0;
            color: #160202;
            
        }
        .info {
            background: #f0f2f7;
            padding: 15px;
            border-radius: 8px;
            margin-top: 20px;
            font-size: 14px;
            color: #120788;
        }
        .header-logo {
    display: flex;
    align-items: center; /* aligns logo and text vertically */
    gap: 10px;
    justify-content: flex-start;
}

.logo-circle {
    width: 70px;
    height: 70px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #ec981b; /* optional */
}

.logo-text-container {
    display: flex;
    flex-direction: column; /* text stacked */
    justify-content: center;
}

.logo-text {
    color: hsl(184, 78%, 51%);
    font-family: 'Times New Roman', Times, serif;
    font-style: italic;
    font-weight: bold;
    font-size: 28px;
    line-height: 1.1;
}

.logo-slogan {
    font-size: 12px;
    color: #a5f3fc;
    font-style: italic;
    margin-top: 2px;
}


.logo-text-container {
    display: flex;
    flex-direction: column; /* text stacked */
    justify-content: center;
}

.logo-text {
    color: hsl(0, 0%, 4%);
    font-family: 'Times New Roman', Times, serif;
    font-style: italic;
    font-weight: bold;
    font-size: 28px;
    line-height: 1.1;
}

.logo-slogan {
    font-size: 12px;
    color: #032327;
    font-style: italic;
    margin-top: 2px;
}
    </style>
</head>
<body>
    <div class="container">
        <img src="image/hub.jpg" alt="Photo" class="logo-circle">
        <div class="logo-text-container">
            <h1 class="logo-text">HOUSING HUB</h1>
            <h2 class="logo-slogan">"Your Property, Our Priority"</h2>
    </div>
    
        
        <div class="buttons">
            <a href="login.php" class="btn-login">Login</a>
            <a href="register.php" class="btn-register">Register</a>
        </div>
        
        <div class="divider">───────────</div>
        
        
        <div class="info">
            <strong>HOME OF COMFORT</strong><br>
        
        </div>
    </div>
</body>
</html>
