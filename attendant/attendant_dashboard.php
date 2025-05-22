<?php
session_start();
if (!isset($_SESSION['staff_id']) || $_SESSION['role'] !== 'attendant') {
    header("Location: ../login.php");
    exit();
}
include '../db.php';

$staff_id = $_SESSION['staff_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['task_id'])) {
    $task_id = $_POST['task_id'];
    $completed = isset($_POST['completed']) ? 1 : 0;

    $stmt = $conn->prepare("UPDATE Task SET completed = ? WHERE task_id = ?");
    $stmt->bind_param("ii", $completed, $task_id);
    $stmt->execute();

    header("Location: attendant_dashboard.php");
    exit();
}

$sql = "
    SELECT f.flight_id, f.flight_number, f.date, f.time, t.task_name, t.task_id, t.completed, t.category
    FROM Flight f
    JOIN Attend a ON f.flight_id = a.flight_id
    LEFT JOIN Task t ON f.flight_id = t.flight_id
    WHERE a.staff_id = ?
    ORDER BY f.date DESC, f.time ASC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

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
            'completed' => $row['completed'],
            'category' => $row['category']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Attendant Dashboard</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body.dashboard {
            background: linear-gradient(to bottom right, #cce7ff, #ffffff);
            padding: 2rem;
        }

        .dashboard__container {
            background-color: #ffffff;
            max-width: 1000px;
            margin: 0 auto;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 51, 102, 0.15);
        }

        .dashboard__title {
            color: #003366;
            text-align: center;
            font-size: 2rem;
            margin-bottom: 1.5rem;
        }

        .dashboard__nav {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .dashboard__link {
            background-color: #003366;
            color: #ffffff;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .dashboard__link:hover {
            background-color: #0059b3;
        }

        .flight {
            background-color: #f0f8ff;
            border-left: 6px solid #003366;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 10px;
        }

        .flight__header {
            display: flex;
            justify-content: space-between;
            font-weight: bold;
            font-size: 1.1rem;
            color: #003366;
            margin-bottom: 1rem;
        }

        .task-category {
            margin-bottom: 1.2rem;
        }

        .task-category__title {
            color: #0059b3;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
            border-bottom: 2px solid #99ccff;
            padding-bottom: 0.3rem;
        }

        .task {
            background-color: #ffffff;
            border: 1px solid #99ccff;
            padding: 0.7rem;
            border-radius: 8px;
            margin-bottom: 0.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .task__name {
            color: #003366;
            font-weight: 500;
        }

        .task__status {
            font-size: 0.95rem;
            color: #003366;
        }

    </style>
</head>
<body class="dashboard">
<div class="dashboard__container">
    <h1 class="dashboard__title">Welcome, Attendant</h1>

    <nav class="dashboard__nav">
        <a class="dashboard__link" href="flight_tasks.php">Flight Tasks</a>
        <a class="dashboard__link" href="report_task.php">Submit Report</a>
        <a class="dashboard__link" href="view_notes.php">Notes</a>
        <a class="dashboard__link" href="../logout.php">Logout</a> 
        
    </nav>

    <?php if (empty($flights)): ?>
        <p>No tasks assigned yet.</p>
    <?php else: ?>
        <?php foreach ($flights as $flight): ?>
            <div class="flight">
                <div class="flight__header">
                    <strong>Flight <?= htmlspecialchars($flight['flight_number']) ?></strong>
                    <span><?= $flight['date'] ?> | <?= $flight['time'] ?></span>
                </div>
                <div class="flight__tasks">
                    <div class="task-category">
                        <h4 class="task-category__title">Pre-Flight Tasks</h4>
                        <?php foreach ($flight['tasks'] as $task): ?>
                            <?php if (strtolower($task['category']) === 'pre-flight'): ?>
                                <div class="task">
                                    <span class="task__name"><?= htmlspecialchars($task['task_name']) ?></span>
                                    <span class="task__status">
                                        <?= $task['completed'] ? '✔️ Completed' : '❌ Not Completed' ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="task-category">
                        <h4 class="task-category__title">During Flight Tasks</h4>
                        <?php foreach ($flight['tasks'] as $task): ?>
                             <?php if (strtolower($task['category']) === 'during flight'): ?>
                                <div class="task">
                                    <span class="task__name"><?= htmlspecialchars($task['task_name']) ?></span>
                                    <span class="task__status">
                                        <?= $task['completed'] ? '✔️ Completed' : '❌ Not Completed' ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
</body>
</html>
