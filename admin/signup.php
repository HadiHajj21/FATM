<?php
session_start();
if (!isset($_SESSION['staff_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../db.php';

$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    // Check if email already exists
    $check = $conn->prepare("SELECT * FROM Staff WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();
    if ($result->num_rows > 0) {
        $message = "Email already registered!";
    } else {
        $stmt = $conn->prepare("INSERT INTO Staff (first_name, last_name, email, password, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $first_name, $last_name, $email, $password, $role);
        if ($stmt->execute()) {
            $message = "User registered successfully!";
        } else {
            $message = "Registration failed: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin - Register New User</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2>Register New Staff / Admin</h2>

    <?php if ($message): ?>
        <div class="alert alert-info mt-3"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3 mt-3">
        <div class="col-md-6">
            <label class="form-label">First Name</label>
            <input type="text" name="first_name" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Last Name</label>
            <input type="text" name="last_name" class="form-control" required>
        </div>
        <div class="col-md-12">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="col-md-12">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Role</label>
            <select name="role" class="form-select" required>
                <option value="attendant">Attendant</option>
                <option value="admin">Admin</option>
            </select>
        </div>
        <div class="col-12">
            <button class="btn btn-primary" type="submit">Register</button>
        </div>
    </form>
</div>

</body>
</html>
