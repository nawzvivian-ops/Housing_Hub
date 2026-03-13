<?php
echo "<h2>📁 Files in Your Project Folder</h2>";
echo "<p>Location: " . __DIR__ . "</p>";
echo "<hr>";

$files = scandir(__DIR__);

echo "<ul style='list-style: none; font-family: monospace;'>";
foreach($files as $file) {
    if($file != '.' && $file != '..') {
        $icon = is_dir($file) ? "📁" : "📄";
        echo "<li>$icon $file</li>";
    }
}
echo "</ul>";

echo "<hr>";
echo "<h3>✓ Files You Should Have:</h3>";
echo "<ul>";

$required_files = [
    'auth.php',
    'db_connect.php', 
    'login.php',
    'register.php',
    'dashboard.php',
    'logout.php',
    'index.php'
];

foreach($required_files as $file) {
    if(file_exists($file)) {
        echo "<li style='color: green;'>✓ $file EXISTS</li>";
    } else {
        echo "<li style='color: red;'>✗ $file MISSING - You need to create this!</li>";
    }
}

echo "</ul>";
?>