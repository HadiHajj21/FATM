<?php
session_start();
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['task_id']) && isset($_POST['completed'])) {
        $task_id = $_POST['task_id'];
        $completed = $_POST['completed'];

        // Update the task completion status in the database
        $update_task = $conn->prepare("UPDATE Task SET completed = ? WHERE task_id = ?");
        $update_task->bind_param("ii", $completed, $task_id);

        if ($update_task->execute()) {
            echo 'success';
        } else {
            echo 'error';
        }
    }
}
?>
