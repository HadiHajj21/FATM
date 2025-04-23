<?php
session_start();
if (!isset($_SESSION['staff_id']) || $_SESSION['role'] !== 'attendant') {
    header("Location: ../login.php");
    exit();
}
include '../db.php';

$staff_id = $_SESSION['staff_id'];

// Fetch all flights and their tasks assigned to this attendant
$sql = "
    SELECT f.flight_id, f.flight_number, f.date, f.time, t.task_name, t.task_id, t.completed
    FROM Flight f
    LEFT JOIN Task t ON f.flight_id = t.flight_id
    WHERE f.staff_id = ?
    ORDER BY f.date DESC, f.time ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

// Group tasks by flight
$flights = [];
while ($row = $result->fetch_assoc()) {
    $fid = $row['flight_id'];
    if (!isset($flights[$fid])) {
        $flights[$fid] = [
            'flight_number' => $row['flight_number'],
            'date' => $row['date'],
            'time' => $row['time'],
            'tasks' => []
        ];
    }
    if ($row['task_id']) {
        $flights[$fid]['tasks'][] = [
            'task_id' => $row['task_id'],
            'task_name' => $row['task_name'],
            'completed' => $row['completed']
        ];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Attendant Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Function to update task status via AJAX
        function updateTaskStatus(task_id, isChecked) {
            $.ajax({
                url: 'update_task.php', // URL to PHP file that handles task update
                type: 'POST',
                data: {
                    task_id: task_id,
                    completed: isChecked ? 1 : 0
                },
                success: function(response) {
                    if (response == 'success') {
                        console.log('Task status updated successfully');
                    } else {
                        console.log('Failed to update task status');
                    }
                }
            });
        }

        // When a checkbox is clicked, update the task status
        $(document).ready(function() {
            $('input[type="checkbox"]').change(function() {
                var task_id = $(this).data('task-id');  // Get the task ID from the checkbox's data attribute
                var isChecked = $(this).prop('checked');  // Get whether the checkbox is checked or not
                updateTaskStatus(task_id, isChecked);
            });
        });
    </script>
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2>Attendant Dashboard</h2>

    <?php if (empty($flights)): ?>
        <p>No tasks assigned to you yet.</p>
    <?php else: ?>
        <?php foreach ($flights as $flight_id => $flight): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Flight <?= htmlspecialchars($flight['flight_number']) ?></strong> |
                    Date: <?= $flight['date'] ?> |
                    Time: <?= $flight['time'] ?>
                </div>
                <div class="card-body">
                    <?php if (empty($flight['tasks'])): ?>
                        <p>No tasks assigned for this flight.</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($flight['tasks'] as $task): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($task['task_name']) ?>
                                    <div>
                                        <input type="checkbox" class="task-checkbox" data-task-id="<?= $task['task_id'] ?>"
                                            <?= $task['completed'] ? 'checked' : '' ?>>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>
