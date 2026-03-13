<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

$type = $_POST['type'] ?? '';
$id   = intval($_POST['id'] ?? 0);

if ($id <= 0 || empty($type)) {
    die("Invalid request");
}

/* ==========================================
   UPDATE FORM SUBMISSION
========================================== */
if (isset($_POST['update'])) {

    /* -------- PROPERTIES -------- */
    if ($type === "property") {

        $property_name = mysqli_real_escape_string($conn, $_POST['property_name']);
        $property_type = mysqli_real_escape_string($conn, $_POST['property_type']);
        $address       = mysqli_real_escape_string($conn, $_POST['address']);
        $units         = intval($_POST['units']);
        $rent_amount   = floatval($_POST['rent_amount']);

        mysqli_query($conn, "
            UPDATE properties 
            SET property_name='$property_name',
                property_type='$property_type',
                address='$address',
                units=$units,
                rent_amount=$rent_amount
            WHERE id=$id
        ");

        header("Location: admin_dashboard.php?page=properties");
        exit();
    }
      
    /* -------- STAFF -------- */
if ($type === "staff") {
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $role     = mysqli_real_escape_string($conn, $_POST['role']);
    $salary   = floatval($_POST['salary']);

    mysqli_query($conn, "
        UPDATE users
        SET fullname='$fullname',
            role='$role',
            salary=$salary
        WHERE id=$id
    ");

    header("Location: admin_dashboard.php?page=staff_roles");
    exit();
}
   
   /* -------- TASKS -------- */
if ($type === "task") {

    $title       = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $assigned_to = intval($_POST['assigned_to']);
    $due_date    = mysqli_real_escape_string($conn, $_POST['due_date']);
    $priority    = mysqli_real_escape_string($conn, $_POST['priority']);
    $status      = mysqli_real_escape_string($conn, $_POST['status']);

    mysqli_query($conn, "
        UPDATE tasks
        SET title='$title',
            description='$description',
            assigned_to=$assigned_to,
            due_date='$due_date',
            priority='$priority',
            status='$status'
        WHERE id=$id
    ");

    header("Location: admin_dashboard.php?page=staff_tasks");
    exit();
}

    /* -------- TENANTS -------- */
    if ($type === "tenant") {

        $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
        $phone    = mysqli_real_escape_string($conn, $_POST['phone']);
        $email    = mysqli_real_escape_string($conn, $_POST['email']);

        mysqli_query($conn, "
            UPDATE tenants
            SET fullname='$fullname',
                phone='$phone',
                email='$email'
            WHERE id=$id
        ");

        header("Location: admin_dashboard.php?page=tenants");
        exit();
    }

    /* -------- COMPLAINTS -------- */
    if ($type === "complaint") {

        $status = mysqli_real_escape_string($conn, $_POST['status']);

        mysqli_query($conn, "
            UPDATE complaints
            SET status='$status'
            WHERE id=$id
        ");

        header("Location: admin_dashboard.php?page=complaints");
        exit();
    }

    /* -------- MAINTENANCE -------- */
    if ($type === "maintenance") {

        $status   = mysqli_real_escape_string($conn, $_POST['status']);
        $priority = mysqli_real_escape_string($conn, $_POST['priority']);

        mysqli_query($conn, "
            UPDATE maintenance_requests
            SET status='$status',
                priority='$priority'
            WHERE id=$id
        ");

        header("Location: admin_dashboard.php?page=maintenance");
        exit();
    }

    /* -------- PAYMENTS -------- */
    if ($type === "payment") {

        $amount = floatval($_POST['amount']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);

        mysqli_query($conn, "
            UPDATE payments
            SET amount=$amount,
                status='$status'
            WHERE id=$id
        ");

        header("Location: admin_dashboard.php?page=payments");
        exit();
    }
    /* -------- INSPECTION -------- */
if ($type === "inspection") {

    $status    = mysqli_real_escape_string($conn, $_POST['status']);
    $situation = mysqli_real_escape_string($conn, $_POST['situation']);
    $notes     = mysqli_real_escape_string($conn, $_POST['notes']);

    mysqli_query($conn, "
        UPDATE inspections
        SET status='$status',
            situation='$situation',
            notes='$notes'
        WHERE id=$id
    ");

    header("Location: admin_dashboard.php?page=inspections");
    exit();
}
}

/* ==========================================
   FETCH RECORD DATA
========================================== */
if ($type === "staff") {
    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id=$id"));
}
   
 if ($type === "task") {
    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tasks WHERE id=$id"));
}

if ($type === "inspection") {
    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM inspections WHERE id=$id"));
}

if ($type === "property") {
    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM properties WHERE id=$id"));
}

if ($type === "tenant") {
    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM tenants WHERE id=$id"));
}

if ($type === "complaint") {
    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM complaints WHERE id=$id"));
}

if ($type === "maintenance") {
    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM maintenance_requests WHERE id=$id"));
}

if ($type === "payment") {
    $data = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM payments WHERE id=$id"));
}

if (!$data) die("Record not found!");

?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit <?= ucfirst($type) ?></title>
    <style>
        body {
            font-family: Segoe UI;
            background: #f4f7fb;
            padding: 30px;
        }

        form {
            background: white;
            padding: 25px;
            width: 480px;
            margin: auto;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }

        h2 {
            text-align: center;
            color: #0ea5e9;
        }

        input, select {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            width: 100%;
            padding: 12px;
            background: #0ea5e9;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 6px;
            cursor: pointer;
        }

        button:hover {
            background: #0284c7;
        }
    </style>
</head>

<body>

<form method="POST">
    <h2>Edit <?= ucfirst($type) ?></h2>

    <!-- PROPERTY -->
    <?php if ($type === "property"): ?>
        <input type="text" name="property_name" value="<?= $data['property_name'] ?>" required>
        <input type="text" name="property_type" value="<?= $data['property_type'] ?>">
        <input type="text" name="address" value="<?= $data['address'] ?>">
        <input type="number" name="units" value="<?= $data['units'] ?>">
        <input type="number" name="rent_amount" value="<?= $data['rent_amount'] ?>">
    <?php endif; ?>

    <!-- STAFF -->
<?php if ($type === "staff"): ?>
    <input type="text" name="fullname" value="<?= $data['fullname'] ?>" required>
    <select name="role" required>
        <option value="staff" <?= ($data['role']=="staff")?"selected":"" ?>>Staff</option>
        <option value="broker" <?= ($data['role']=="broker")?"selected":"" ?>>Broker</option>
        <option value="owner" <?= ($data['role']=="owner")?"selected":"" ?>>Owner</option>
        <option value="admin" <?= ($data['role']=="admin")?"selected":"" ?>>Admin</option>
    </select>
    <input type="number" name="salary" value="<?= $data['salary'] ?? 0 ?>" placeholder="Salary (UGX)">
<?php endif; ?>
     
    <?php if ($type === "task"): ?>

    <input type="text" name="title"
           value="<?= htmlspecialchars($data['title'] ?? '') ?>"
           placeholder="Task Title" required>

    <textarea name="description"
              placeholder="Task Description"
              style="width:100%; padding:10px; border-radius:6px; margin:10px 0;"
    ><?= htmlspecialchars($data['description'] ?? '') ?></textarea>

    <!-- Assign Staff -->
    <select name="assigned_to" required>
        <option value="">-- Select Staff Member --</option>

        <?php
        $staff = mysqli_query($conn, "SELECT id, fullname FROM users WHERE role='staff'");
        while ($s = mysqli_fetch_assoc($staff)) {

            $selected = ($s['id'] == $data['assigned_to']) ? "selected" : "";

            echo "<option value='{$s['id']}' $selected>
                    ".htmlspecialchars($s['fullname'])."
                  </option>";
        }
        ?>
    </select>

    <input type="date" name="due_date"
           value="<?= htmlspecialchars($data['due_date'] ?? '') ?>">

    <!-- Priority -->
    <select name="priority">
        <option value="Low" <?= ($data['priority']=="Low")?"selected":"" ?>>Low</option>
        <option value="Medium" <?= ($data['priority']=="Medium")?"selected":"" ?>>Medium</option>
        <option value="High" <?= ($data['priority']=="High")?"selected":"" ?>>High</option>
    </select>

    <!-- Status -->
    <select name="status">
        <option value="Pending" <?= ($data['status']=="Pending")?"selected":"" ?>>Pending</option>
        <option value="In Progress" <?= ($data['status']=="In Progress")?"selected":"" ?>>In Progress</option>
        <option value="Completed" <?= ($data['status']=="Completed")?"selected":"" ?>>Completed</option>
    </select>

<?php endif; ?>
       
       <?php if ($type === "inspection"): ?>

    <input type="text" name="situation"
           value="<?= htmlspecialchars($data['situation']) ?>" required>

    <select name="status">
        <option value="Pending" <?= ($data['status']=="Pending")?"selected":"" ?>>Pending</option>
        <option value="Completed" <?= ($data['status']=="Completed")?"selected":"" ?>>Completed</option>
    </select>

    <textarea name="notes"
      style="width:100%; padding:10px; border-radius:6px;"
    ><?= htmlspecialchars($data['notes'] ?? '') ?></textarea>

<?php endif; ?>
     
    <!-- TENANT -->
    <?php if ($type === "tenant"): ?>
        <input type="text" name="fullname" value="<?= $data['fullname'] ?>" required>
        <input type="text" name="phone" value="<?= $data['phone'] ?>">
        <input type="email" name="email" value="<?= $data['email'] ?>">
    <?php endif; ?>

    <!-- COMPLAINT -->
    <?php if ($type === "complaint"): ?>
        <select name="status">
            <option value="pending" <?= ($data['status']=="pending")?"selected":"" ?>>Pending</option>
            <option value="resolved" <?= ($data['status']=="resolved")?"selected":"" ?>>Resolved</option>
        </select>
    <?php endif; ?>

    <!-- MAINTENANCE -->
    <?php if ($type === "maintenance"): ?>
        <select name="priority">
            <option value="low" <?= ($data['priority']=="low")?"selected":"" ?>>Low</option>
            <option value="medium" <?= ($data['priority']=="medium")?"selected":"" ?>>Medium</option>
            <option value="high" <?= ($data['priority']=="high")?"selected":"" ?>>High</option>
        </select>

        <select name="status">
            <option value="pending" <?= ($data['status']=="pending")?"selected":"" ?>>Pending</option>
            <option value="in_progress" <?= ($data['status']=="in_progress")?"selected":"" ?>>In Progress</option>
            <option value="completed" <?= ($data['status']=="completed")?"selected":"" ?>>Completed</option>
        </select>
    <?php endif; ?>

    <!-- PAYMENT -->
    <?php if ($type === "payment"): ?>
        <input type="number" name="amount" value="<?= $data['amount'] ?>" required>

        <select name="status">
            <option value="pending" <?= ($data['status']=="pending")?"selected":"" ?>>Pending</option>
            <option value="paid" <?= ($data['status']=="paid")?"selected":"" ?>>Paid</option>
        </select>
    <?php endif; ?>

    <button type="submit" name="update">Update</button>
</form>

</body>
</html>