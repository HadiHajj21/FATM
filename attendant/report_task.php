<?php
session_start();
if ($_SESSION['role'] !== 'attendant') {
    header("Location: ../login.php");
    exit();
}
include '../db.php';

$staff_id = $_SESSION['staff_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flight_id = $_POST['flight_id'];
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];
    $reason = $_POST['reason'];

    $stmt = $conn->prepare("INSERT INTO Report (staff_id, flight_id, task_id, status, reason_if_incomplete, submission_time)
                            VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("siiss", $staff_id, $flight_id, $task_id, $status, $reason);
    $stmt->execute();

    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Submit Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2>Submit Task Report</h2>
    <form method="POST" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Flight ID</label>
            <input type="number" name="flight_id" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Task ID</label>
            <input type="number" name="task_id" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Status</label>
            <select name="status" class="form-select">
                <option value="completed">Completed</option>
                <option value="pending">Pending</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Reason if Incomplete</label>
            <input type="text" name="reason" class="form-control">
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Submit Report</button>
        </div>
    </form>
</div>

</body>
</html>
