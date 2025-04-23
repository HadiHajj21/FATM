<?php
session_start();
if (!isset($_SESSION['staff_id']) || $_SESSION['role'] !== 'attendant') {
    header("Location: ../login.php");
    exit();
}
include '../db.php';

$staff_id = $_SESSION['staff_id'];

// Fetch tasks for each flight and categorize them
$sql = "
    SELECT f.flight_id, f.flight_number, f.date, f.time, t.task_name, t.completed
    FROM Flight f
    LEFT JOIN Task t ON f.flight_id = t.flight_id
    WHERE f.staff_id = ?
    ORDER BY f.date DESC, f.time ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

// Group tasks by flight and type
$flights = [];
while ($row = $result->fetch_assoc()) {
    $fid = $row['flight_id'];
    $flight_type = (strpos($row['task_name'], "Pre-flight") !== false) ? 'Pre-Flight' : 'During Flight';
    if (!isset($flights[$fid])) {
        $flights[$fid] = [
            'flight_number' => $row['flight_number'],
            'date' => $row['date'],
            'time' => $row['time'],
            'pre_flight' => [],
            'during_flight' => []
        ];
    }

    if ($flight_type == 'Pre-Flight') {
        $flights[$fid]['pre_flight'][] = [
            'task_name' => $row['task_name'],
            'completed' => $row['completed']
        ];
    } else {
        $flights[$fid]['during_flight'][] = [
            'task_name' => $row['task_name'],
            'completed' => $row['completed']
        ];
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Flight Tasks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2>Flight Tasks</h2>

    <?php if (empty($flights)): ?>
        <p>No tasks assigned yet.</p>
    <?php else: ?>
        <?php foreach ($flights as $flight_id => $flight): ?>
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Flight <?= htmlspecialchars($flight['flight_number']) ?></strong> |
                    Date: <?= $flight['date'] ?> |
                    Time: <?= $flight['time'] ?>
                </div>
                <div class="card-body">
                    <h4>Pre-Flight Tasks</h4>
                    <?php if (empty($flight['pre_flight'])): ?>
                        <p>No pre-flight tasks assigned.</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($flight['pre_flight'] as $task): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($task['task_name']) ?>
                                    <div>
                                        <?= $task['completed'] ? 'Completed' : 'Pending' ?>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>

                    <h4 class="mt-3">During Flight Tasks</h4>
                    <?php if (empty($flight['during_flight'])): ?>
                        <p>No during-flight tasks assigned.</p>
                    <?php else: ?>
                        <ul class="list-group">
                            <?php foreach ($flight['during_flight'] as $task): ?>
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <?= htmlspecialchars($task['task_name']) ?>
                                    <div>
                                        <?= $task['completed'] ? 'Completed' : 'Pending' ?>
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
