<?php
session_start();
include '../db.php';

if (!isset($_SESSION['staff_id']) || $_SESSION['role'] !== 'attendant') {
    header("Location: ../login.php");
    exit();
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['tasks'])) {
    header("Location: flight_tasks.php");
    exit();
}


$checked_tasks = $_POST['tasks']; // tasks marked completed
$staff_id = $_SESSION['staff_id'];

// Step 1: Get all tasks assigned to this staff's flights
$sql_flights = "SELECT flight_id FROM Flight WHERE staff_id = ?";
$stmt_flights = $conn->prepare($sql_flights);
$stmt_flights->bind_param("i", $staff_id);
$stmt_flights->execute();
$result_flights = $stmt_flights->get_result();

$flight_ids = [];
while ($row = $result_flights->fetch_assoc()) {
    $flight_ids[] = $row['flight_id'];
}
$stmt_flights->close();

if (empty($flight_ids)) {
    // No flights assigned - nothing to update
    header("Location: flight_tasks.php?error=noflights");
    exit();
}

// Convert flight_ids array to a comma-separated string for SQL IN clause
$flight_ids_placeholder = implode(',', array_fill(0, count($flight_ids), '?'));

// Step 2: Get all tasks belonging to those flights
// Prepare the IN statement dynamically
$sql_tasks = "SELECT task_id FROM Task WHERE flight_id IN ($flight_ids_placeholder)";
$stmt_tasks = $conn->prepare($sql_tasks);

// Dynamically bind parameters
$types = str_repeat('i', count($flight_ids));
$stmt_tasks->bind_param($types, ...$flight_ids);
$stmt_tasks->execute();
$result_tasks = $stmt_tasks->get_result();

$all_task_ids = [];
while ($row = $result_tasks->fetch_assoc()) {
    $all_task_ids[] = $row['task_id'];
}
$stmt_tasks->close();

// Step 3: Update each task's completed status based on submitted checkboxes
// Assuming Task table has a 'completed' boolean or tinyint(1) column

$stmt_update = $conn->prepare("UPDATE Task SET completed = ? WHERE task_id = ?");

foreach ($all_task_ids as $task_id) {
    // If task_id is in the checked array, completed = 1, else 0
    $completed = isset($checked_tasks[$task_id]) ? 1 : 0;
    $stmt_update->bind_param("ii", $completed, $task_id);
    $stmt_update->execute();
}

$stmt_update->close();

// Redirect back with success message
header("Location: flight_tasks.php?success=1");
exit();
