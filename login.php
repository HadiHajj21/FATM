<?php
session_start();
// Check if the user is already logged in
if (isset($_SESSION['staff_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/admin_dashboard.php");
        exit();
    } else {
        header("Location: attendant/attendant_dashboard.php");
        exit();
    }
}
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM Staff WHERE email=? AND password=?");
    $stmt->bind_param("ss", $email, $password); // WARNING: hash passwords in real apps!
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        session_start();
        $_SESSION['staff_id'] = $row['staff_id'];
        $_SESSION['role'] = $row['role'];

        if ($row['role'] == 'admin') {
            header("Location: admin/admin_dashboard.php");
            die;
        } else {
            // Assuming 'attendant' is the role for attendants
            header("Location: attendant/attendant_dashboard.php");
            die;
        }
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<!-- Basic HTML Form -->
<form method="POST">
    Email: <input type="email" name="email" required><br>
    Password: <input type="password" name="password" required><br>
    <button type="submit">Login</button>
</form>
<?php if (isset($error)) echo "<p>$error</p>"; ?>
