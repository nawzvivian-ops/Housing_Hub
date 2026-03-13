<?php
session_start();
include "db_connect.php"; // missing in your previous dashboard.php

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    $result = mysqli_query($conn, "SELECT * FROM users WHERE id='$user_id'");
    $user = mysqli_fetch_assoc($result);
} else {
    $user = ['fullname' => '']; // fallback if somehow user_id is missing
}

// ===== DASHBOARD COUNTS =====

// Total Properties
$properties_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM properties")
)['total'];

// Total Tenants
$tenants_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM tenants")
)['total'];

// Total Staff
$staff_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM staff")
)['total'];

// Total Guests
$guests_count = mysqli_fetch_assoc(
    mysqli_query($conn, "SELECT COUNT(*) AS total FROM guests")
)['total'];

// Language handling
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'en'; // Default language
}

if (isset($_GET['lang'])) {
    $_SESSION['lang'] = $_GET['lang'];
}

// Language translations
$translations = [
    'en' => [
        'home' => 'HOME',
        'properties' => 'Properties',
        'tenants' => 'Tenants',
        'guests' => 'Guests',
        'staff' => 'Staff',
        'brokers' => 'Brokers/Property Owners',
        'admin' => 'Admin Panel',
        'welcome' => 'Welcome to HousingHub',
        'dashboard_desc' => 'Manage your properties efficiently'
    ],
    'fr' => [
        'home' => 'ACCUEIL',
        'properties' => 'Propriétés',
        'tenants' => 'Locataires',
        'guests' => 'Invités',
        'staff' => 'Personnel',
        'brokers' => 'Courtiers/Propriétaires',
        'admin' => 'Panneau Admin',
        'welcome' => 'Bienvenue à HousingHub',
        'dashboard_desc' => 'Gérez vos propriétés efficacement'
    ],
    'sw' => [
        'home' => 'NYUMBANI',
        'properties' => 'Mali',
        'tenants' => 'Wapangaji',
        'guests' => 'Wageni',
        'staff' => 'Wafanyakazi',
        'brokers' => 'Madalali/Wamiliki',
        'admin' => 'Paneli ya Msimamizi',
        'welcome' => 'Karibu HousingHub',
        'dashboard_desc' => 'Simamia mali zako kwa ufanisi'
    ],
    'es' => [
        'home' => 'INICIO',
        'properties' => 'Propiedades',
        'tenants' => 'Inquilinos',
        'guests' => 'Invitados',
        'staff' => 'Personal',
        'brokers' => 'Corredores/Propietarios',
        'admin' => 'Panel de Admin',
        'welcome' => 'Bienvenido a HousingHub',
        'dashboard_desc' => 'Gestiona tus propiedades eficientemente'
    ],
    'de' => [
        'home' => 'STARTSEITE',
        'properties' => 'Immobilien',
        'tenants' => 'Mieter',
        'guests' => 'Gäste',
        'staff' => 'Personal',
        'brokers' => 'Makler/Eigentümer',
        'admin' => 'Admin-Panel',
        'welcome' => 'Willkommen bei HousingHub',
        'dashboard_desc' => 'Verwalten Sie Ihre Immobilien effizient'
    ],
    'ar' => [
        'home' => 'الرئيسية',
        'properties' => 'العقارات',
        'tenants' => 'المستأجرون',
        'guests' => 'الضيوف',
        'staff' => 'الموظفون',
        'brokers' => 'الوسطاء/المالكون',
        'admin' => 'لوحة الإدارة',
        'welcome' => 'مرحبا بك في HousingHub',
        'dashboard_desc' => 'إدارة الممتلكات الخاصة بك بكفاءة'
    ],
    'zh' => [
        'home' => '首页',
        'properties' => '物业',
        'tenants' => '租户',
        'guests' => '客人',
        'staff' => '员工',
        'brokers' => '经纪人/业主',
        'admin' => '管理面板',
        'welcome' => '欢迎来到HousingHub',
        'dashboard_desc' => '高效管理您的物业'
    ],
    'lg' => [
        'home' => 'EWAKA',
        'properties' => 'Ebintu',
        'tenants' => 'Abapangisa',
        'guests' => 'Abagenyi',
        'staff' => 'Abakozi',
        'brokers' => 'Abatundaganyi/Bannannyini',
        'admin' => 'Ekitebe kya Admin',
        'welcome' => 'Tukusanyukidde ku HousingHub',
        'dashboard_desc' => 'Ddukanya ebintu byo obulungi'
    ]
];

$current_lang = $_SESSION['lang'];
$t = $translations[$current_lang];
?>
<!DOCTYPE html>
<html lang="<?php echo $current_lang; ?>">
<head>
<meta charset="UTF-8">
<title>HousingHub | Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
/* --- Reset & Base --- */
* {margin: 0; padding: 0; box-sizing: border-box;}
body {font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #f0f4f8; color: #333; display: flex; min-height: 100vh;}

/* --- Sidebar Navigation --- */
.sidebar {
    width: 250px;
    background: black;
    color: #fff;
    padding: 20px 0;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    transition: all 0.3s ease;
    z-index: 999;
}
.sidebar.collapsed {
    width: 70px;
}
.sidebar.collapsed .sidebar-header h2,
.sidebar.collapsed .sidebar-menu a span:not(.icon) {
    display: none;
}
.sidebar.collapsed .sidebar-menu a {
    justify-content: center;
    padding: 12px 10px;
}
.sidebar.collapsed .sidebar-menu .icon {
    margin-right: 0;
}
.sidebar-header {
    padding: 0 20px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.sidebar-header h2 {
    font-size: 22px;
    color: #4f44ef;
}
.toggle-btn {
    background: none;
    border: none;
    color: #fff;
    font-size: 24px;
    cursor: pointer;
    padding: 5px;
    transition: transform 0.3s;
}
.toggle-btn:hover {
    transform: scale(1.1);
}
.sidebar-menu {
    list-style: none;
    padding: 20px 0;
}
.sidebar-menu li {
    margin-bottom: 5px;
}
.sidebar-menu a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #cbd5e1;
    text-decoration: none;
    transition: all 0.3s;
}
.sidebar-menu a:hover, .sidebar-menu a.active {
    background: black;
    color: #fff;
    border-left: 4px solid #4463ef;
}
.sidebar-menu .icon {
    margin-right: 12px;
    font-size: 20px;
}

/* --- Main Content --- */
.main-content {
    margin-left: 250px;
    flex: 1;
    transition: margin-left 0.3s ease;
}
.main-content.expanded {
    margin-left: 70px;
}

/* --- Top Navigation Header --- */
.top-nav {
    background: #fff;
    padding: 15px 30px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    z-index: 998;
}

.top-nav .page-title {
    font-size: 24px;
    color: #1e293b;
    font-weight: 600;
}

/* --- Language Switcher --- */
.language-switcher {
    position: relative;
}

.lang-btn {
    background: linear-gradient(135deg, #4f46e5, #3b82f6);
    color: #fff;
    border: none;
    padding: 10px 20px;
    border-radius: 25px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 10px rgba(79, 70, 229, 0.3);
}

.lang-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 15px rgba(79, 70, 229, 0.4);
}

.lang-btn i {
    font-size: 16px;
}

.lang-dropdown {
    position: absolute;
    top: 50px;
    right: 0;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    min-width: 200px;
    opacity: 0;
    visibility: hidden;
    transform: translateY(-10px);
    transition: all 0.3s ease;
    z-index: 1000;
}

.language-switcher:hover .lang-dropdown {
    opacity: 1;
    visibility: visible;
    transform: translateY(0);
}

.lang-dropdown a {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: #333;
    text-decoration: none;
    transition: all 0.3s;
    gap: 10px;
}

.lang-dropdown a:hover {
    background: #f0f4f8;
    color: #4f46e5;
}

.lang-dropdown a:first-child {
    border-radius: 12px 12px 0 0;
}

.lang-dropdown a:last-child {
    border-radius: 0 0 12px 12px;
}

.lang-flag {
    font-size: 20px;
}

.lang-dropdown a.active {
    background: #e0e7ff;
    color: #4f46e5;
    font-weight: 600;
}

/* --- Mobile Menu Toggle --- */
.menu-toggle {
    display: none;
    position: fixed;
    top: 15px;
    left: 15px;
    z-index: 1001;
    background: #1e293b;
    color: #fff;
    border: none;
    padding: 10px 15px;
    border-radius: 8px;
    cursor: pointer;
    font-size: 20px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}
.menu-toggle:hover {
    background: #334155;
}

/* --- Welcome Card --- */
.welcome-card {
    background: linear-gradient(135deg, #4f46e5, #3b82f6);
    color: #fff;
    padding: 30px;
    border-radius: 12px;
    margin: 30px;
    box-shadow: 0 8px 20px rgba(79, 70, 229, 0.3);
}
.welcome-card h2 {margin-bottom: 10px; font-size: 28px;}
.welcome-card p {font-size: 16px; opacity: 0.9;}

/* --- Dashboard Grid --- */
.dashboard {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 25px;
    padding: 0 30px 30px 30px;
}

/* --- Responsive --- */
@media (max-width: 768px) {
    .sidebar {
        transform: translateX(-100%);
        width: 250px;
    }
    .sidebar.active {
        transform: translateX(0);
    }
    .sidebar.collapsed {
        transform: translateX(-100%);
    }
    .main-content, .main-content.expanded {
        margin-left: 0;
    }
    .menu-toggle {
        display: block;
    }
    .dashboard {
        grid-template-columns: 1fr;
    }
    .top-nav {
        padding: 15px;
    }
    .top-nav .page-title {
        font-size: 18px;
    }
    .lang-btn {
        padding: 8px 15px;
        font-size: 12px;
    }
}

/* RTL Support for Arabic */
[lang="ar"] {
    direction: rtl;
}
[lang="ar"] .sidebar {
    right: 0;
    left: auto;
}
[lang="ar"] .main-content {
    margin-right: 250px;
    margin-left: 0;
}
[lang="ar"] .main-content.expanded {
    margin-right: 70px;
}
[lang="ar"] .sidebar-menu a:hover,
[lang="ar"] .sidebar-menu a.active {
    border-left: none;
    border-right: 4px solid #4463ef;
}
.slideshow-container {
    width: 100%;
    max-width: 900px;
    height: 400px;
    margin: 30px auto;
    position: relative;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 8px 20px rgba(0,0,0,0.2);
}

.slideshow-container .slide {
    position: absolute;
    width: 100%;
    height: 100%;
    object-fit: cover;
    top: 0;
    left: 0;
    opacity: 0;
    transition: opacity 1s ease-in-out;
}

.slideshow-container .slide.active {
    opacity: 1;
}
.card {
    background: lightblue;
    padding: 25px;
    border-radius: 18px;
    box-shadow: 0 6px 15px rgba(35, 13, 231, 0.97);
    text-align: center;
    transition: 0.3s ease;
}

.card:hover {
    transform: translateY(-6px);
    box-shadow: 0 10px 25px rgba(21, 43, 245, 0.97);
}

.card h3 {
    font-size: 18px;
    margin-bottom: 12px;
    color: #1e293b;
    font-weight: 600;
}

.card p {
    font-size: 34px;
    font-weight: bold;
    color: #4f46e5;
}
</style>
</head>
<body>
<!-- Mobile Menu Toggle -->
<button class="menu-toggle" id="menuToggle" onclick="toggleMenu()">☰</button>

<!-- Sidebar Navigation -->
<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <h2>HOUSING HUB</h2>
        <button class="toggle-btn" onclick="toggleSidebar()">☰</button>
    </div>
    <ul class="sidebar-menu">
        <li><a href="dashboard.php" class="active"><img src="image/hme.png" alt="Photo" width="40" height="30"><span class="icon"></span> <span><?php echo $t['home']; ?></span></a></li>
        <li><a href="properties.php"><span class="icon"><img src="image/prpty.png" alt="Photo" width="60" height="50"></span> <span><?php echo $t['properties']; ?></span></a></li>
        <li><a href="tenants.php"><span class="icon"><img src="image/tennt.png" alt="Photo" width="60" height="40"></span> <span><?php echo $t['tenants']; ?></span></a></li>
        <li><a href="guests.php"><span class="icon"><img src="image/gust.png" alt="Photo" width="60" height="40"></span> <span><?php echo $t['guests']; ?></span></a></li>
        <li><a href="brokers_propertyowners.php"><span class="icon"><img src="image/men.png" alt="Photo" width="70" height="50"></span> <span><?php echo $t['brokers']; ?></span></a></li>
        <?php if(isset($user['role']) && $user['role'] === 'admin'): ?>
        <li><a href="admin.php"><span class="icon"></span> <span><?php echo $t['admin']; ?></span></a></li>
        <?php endif; ?>
       
    </ul>
</aside>

<!-- Main Content -->
<main class="main-content" id="mainContent">
    <!-- Top Navigation Header -->
    <div class="top-nav">
        <div class="page-title">
            <i class="fas fa-home"></i> Dashboard

        </div>
        <!-- Logout Button -->
        <a href="logout.php" class="lang-btn" style="background:#ef4444;">
           <i class="fas fa-sign-out-alt"></i> Logout
         </a>
         
        <!-- Language Switcher -->
        <div class="language-switcher">
            <button class="lang-btn">
                <i class="fas fa-globe"></i>
                <span>
                    <?php 
                    $lang_names = [
                        'en' => 'English',
                        'fr' => 'Français',
                        'sw' => 'Kiswahili',
                        'es' => 'Español',
                        'de' => 'Deutsch',
                        'ar' => 'العربية',
                        'zh' => '中文',
                        'lg' => 'Oluganda'
                    ];
                    echo $lang_names[$current_lang];
                    ?>
                </span>
                <i class="fas fa-chevron-down"></i>
            </button>
            <div class="lang-dropdown">
                <a href="?lang=en" class="<?php echo $current_lang == 'en' ? 'active' : ''; ?>">
                    <span class="lang-flag">🇬🇧</span> English
                </a>
                <a href="?lang=fr" class="<?php echo $current_lang == 'fr' ? 'active' : ''; ?>">
                    <span class="lang-flag">🇫🇷</span> French
                </a>
                <a href="?lang=sw" class="<?php echo $current_lang == 'sw' ? 'active' : ''; ?>">
                    <span class="lang-flag">🇰🇪</span> Kiswahili
                </a>
                <a href="?lang=es" class="<?php echo $current_lang == 'es' ? 'active' : ''; ?>">
                    <span class="lang-flag">🇪🇸</span> spanish
                </a>
                <a href="?lang=de" class="<?php echo $current_lang == 'de' ? 'active' : ''; ?>">
                    <span class="lang-flag">🇩🇪</span> German
                </a>
                <a href="?lang=ar" class="<?php echo $current_lang == 'ar' ? 'active' : ''; ?>">
                    <span class="lang-flag">🇸🇦</span> العربية
                </a>
                <a href="?lang=zh" class="<?php echo $current_lang == 'zh' ? 'active' : ''; ?>">
                    <span class="lang-flag">🇨🇳</span> 中文
                </a>
                <a href="?lang=lg" class="<?php echo $current_lang == 'lg' ? 'active' : ''; ?>">
                    <span class="lang-flag">🇺🇬</span> luganda
                </a>
            </div>
        </div>
    </div>

    <!-- Welcome Card -->
    <div class="welcome-card">
        <h2>
    <?php 
        $name = isset($user['fullname']) ? htmlspecialchars($user['fullname']) : '';
        echo $t['welcome'] . ($name ? ', ' . $name : '') . '!';
    ?>
</h2>
    </div>

       <!-- Video-like Slideshow -->
<div class="slideshow-container">
    <img src="property_media/bed2.png" class="slide active">
    <img src="property_media/apartment.png" class="slide">
    <img src="image/rrr.png" class="slide">
    <img src="image/nm.png" class="slide">
    <img src="images/b9.png" class="slide">
    <img src="images/dd.png" class="slide">
    <img src="images/ddd.png" class="slide">
    <img src="property_media/pool.png" class="slide">
    <img src="property_media/bed1.png" class="slide">
    <img src="property_media/lvp.png" class="slide">
</div>

    <!-- Dashboard Grid -->
    <div class="dashboard">
        <!-- Add your dashboard content here -->
         <!-- Properties Card -->
    <div class="card">
        <h3> <?php echo $t['properties']; ?></h3>
        <p><?php echo $properties_count; ?></p>
    </div>

    <!-- Tenants Card -->
    <div class="card">
        <h3><?php echo $t['tenants']; ?></h3>
        <p><?php echo $tenants_count; ?></p>
    </div>

    <!-- Staff Card -->
    <?php if($user['role'] === 'admin'): ?>
<div class="card">
    <h3><?php echo $t['staff']; ?></h3>
    <p><?php echo $staff_count; ?></p>
</div>
<?php endif; ?>

    <!-- Guests Card -->
    <div class="card">
        <h3><?php echo $t['guests']; ?></h3>
        <p><?php echo $guests_count; ?></p>
    </div>

</div>

</main>
 
<script>
// Sidebar collapse (desktop)
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');

    sidebar.classList.toggle('collapsed');
    mainContent.classList.toggle('expanded');
}

// Mobile menu toggle
function toggleMenu() {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');

    sidebar.classList.toggle('active');

    // Change button icon dynamically
    menuToggle.textContent = sidebar.classList.contains('active') ? '✖' : '☰';
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const sidebar = document.getElementById('sidebar');
    const menuToggle = document.getElementById('menuToggle');

    if (window.innerWidth <= 768) {
        if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
            sidebar.classList.remove('active');
            menuToggle.textContent = '☰';
        }
    }
});
let slides = document.querySelectorAll('.slide');
let current = 0;

function showNextSlide() {
    slides[current].classList.remove('active');
    current = (current + 1) % slides.length;
    slides[current].classList.add('active');
}

// Change image every 3 seconds (adjust as needed)
setInterval(showNextSlide, 5000);
</script>
</body>
</html>