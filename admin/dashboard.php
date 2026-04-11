<?php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>HousingHub | Dashboard</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
:root {
    --primary: #1e3a8a;
    --secondary: #38bdf8;
    --dark: #0f172a;
    --light: #f4f7fb;
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: "Segoe UI", sans-serif;
}

body {
    background: var(--light);
    display: flex;
}

/* ===== SIDEBAR ===== */
.sidebar {
    width: 250px;
    background: var(--dark);
    min-height: 100vh;
    padding: 30px 20px;
    color: white;
}

.sidebar h2 {
    text-align: center;
    margin-bottom: 40px;
    color: var(--secondary);
    font-style: italic;
}

.sidebar a {
    display: block;
    color: #cbd5f5;
    text-decoration: none;
    padding: 12px 15px;
    margin-bottom: 10px;
    border-radius: 10px;
    transition: 0.3s;
}

.sidebar a:hover,
.sidebar a.active {
    background: var(--secondary);
    color: var(--dark);
}

/* ===== MAIN ===== */
.main {
    flex: 1;
    padding: 30px;
}

/* ===== TOP BAR ===== */
.topbar {
    background: white;
    padding: 20px 25px;
    border-radius: 15px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    box-shadow: 0 10px 25px rgba(0,0,0,0.08);
}

.topbar h3 span {
    color: var(--primary);
}

.logout {
    background: var(--primary);
    color: white;
    padding: 10px 18px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: bold;
}

.logout:hover {
    background: var(--dark);
}

/* ===== STATS ===== */
.stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 25px;
    margin-top: 30px;
}

.stat-card {
    background: white;
    padding: 25px;
    border-radius: 20px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.08);
    transition: 0.3s;
}

.stat-card:hover {
    transform: translateY(-6px);
}

.stat-card h4 {
    color: #64748b;
    margin-bottom: 10px;
}

.stat-card h2 {
    color: var(--primary);
    font-size: 32px;
}

/* ===== TABLE ===== */
.table-box {
    background: white;
    margin-top: 40px;
    padding: 25px;
    border-radius: 20px;
    box-shadow: 0 15px 30px rgba(0,0,0,0.08);
}

table {
    width: 100%;
    border-collapse: collapse;
}

th, td {
    padding: 14px;
    text-align: left;
}

th {
    background: var(--light);
    color: #334155;
}

tr:not(:last-child) {
    border-bottom: 1px solid #e5e7eb;
}

.status {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 13px;
    font-weight: bold;
}

.paid { background: #dcfce7; color: #166534; }
.pending { background: #fef3c7; color: #92400e; }
.issue { background: #fee2e2; color: #991b1b; }

/* ===== RESPONSIVE ===== */
@media(max-width: 900px) {
    .sidebar {
        display: none;
    }
    body {
        flex-direction: column;
    }
}
</style>
</head>

<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <h2>HousingHub</h2>
    <a href="#" class="active">Dashboard</a>
    <a href="#">Properties</a>
    <a href="#">Tenants</a>
    <a href="#">Payments</a>
    <a href="#">Maintenance</a>
    <a href="#">Reports</a>
    <a href="../logout.php">Logout</a>
</div>

<!-- MAIN -->
<div class="main">

    <!-- TOP BAR -->
    <div class="topbar">
        <h3>Welcome, <span><?= htmlspecialchars($_SESSION['user']); ?></span></h3>
        <div>
            Role: <strong><?= htmlspecialchars($_SESSION['role']); ?></strong>
        </div>
    </div>

    <!-- STATS -->
    <div class="stats">
        <div class="stat-card">
            <h4>Total Properties</h4>
            <h2>24</h2>
        </div>
        <div class="stat-card">
            <h4>Total Tenants</h4>
            <h2>87</h2>
        </div>
        <div class="stat-card">
            <h4>Monthly Income</h4>
            <h2>$12,450</h2>
        </div>
        <div class="stat-card">
            <h4>Maintenance Requests</h4>
            <h2>5</h2>
        </div>
    </div>

    <!-- RECENT ACTIVITY -->
    <div class="table-box">
        <h3 style="margin-bottom:20px;">Recent Payments</h3>
        <table>
            <tr>
                <th>Tenant</th>
                <th>Property</th>
                <th>Amount</th>
                <th>Status</th>
            </tr>
            <tr>
                <td>John Doe</td>
                <td>Apartment A2</td>
                <td>$450</td>
                <td><span class="status paid">Paid</span></td>
            </tr>
            <tr>
                <td>Mary Smith</td>
                <td>House B4</td>
                <td>$600</td>
                <td><span class="status pending">Pending</span></td>
            </tr>
            <tr>
                <td>David Lee</td>
                <td>Shop C1</td>
                <td>$350</td>
                <td><span class="status issue">Issue</span></td>
            </tr>
        </table>
    </div>

</div>

</body>
</html>