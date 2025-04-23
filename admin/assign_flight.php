<?php
session_start();
if ($_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $staff_id = $_POST['staff_id'];
    $flight_id = $_POST['flight_id'];

    $stmt = $conn->prepare("INSERT INTO Attend (staff_id, flight_id) VALUES (?, ?)");
    $stmt->bind_param("si", $staff_id, $flight_id);
    if ($stmt->execute()) {
        header("Location: dashboard.php");
    } else {
        echo "Error assigning staff: " . $conn->error;
    }
}
?>
