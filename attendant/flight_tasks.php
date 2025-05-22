<?php
session_start();
if (!isset($_SESSION['staff_id']) || $_SESSION['role'] !== 'attendant') {
    header("Location: ../login.php");
    exit();
}
include '../db.php';

$staff_id = $_SESSION['staff_id'];

// Get flights assigned to this attendant
$sql_flights = "SELECT * FROM Flight WHERE staff_id = ?";
$stmt_flights = $conn->prepare($sql_flights);
$stmt_flights->bind_param("i", $staff_id);
$stmt_flights->execute();
$result_flights = $stmt_flights->get_result();

$flights = [];
while ($flight = $result_flights->fetch_assoc()) {
    $flights[] = $flight;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Flight Tasks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(to bottom right, #cce7ff, #ffffff);
            padding: 2rem;
        }
        .tasks {
            max-width: 900px;
            margin: 2rem auto;
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 51, 102, 0.1);
        }

        .tasks h2 {
            text-align: center;
            color: #003366;
            margin-bottom: 2rem;
        }

        .tasks__flight {
            margin-bottom: 2rem;
            border-left: 6px solid #003366;
            padding: 1.5rem;
            border-radius: 10px;
            background-color: #f0f8ff;
        }

        .tasks__flight-header {
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 1rem;
            color: #003366;
        }

        .tasks__group {
            margin-top: 1rem;
        }

        .tasks__group-title {
            font-weight: 600;
            color: #0059b3;
            margin-bottom: 0.5rem;
            font-size: 1.05rem;
            border-bottom: 2px solid #99ccff;
            padding-bottom: 0.3rem;
        }

        .tasks__item {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            padding: 0.5rem;
            background-color: #ffffff;
            border: 1px solid #99ccff;
            border-radius: 8px;
        }

        .tasks__checkbox {
            margin-right: 0.75rem;
            transform: scale(1.2);
        }

        .tasks__submit {
            margin-top: 2rem;
            background-color: #003366;
            border: none;
            padding: 0.6rem 1.5rem;
            color: #ffffff;
            border-radius: 8px;
            font-weight: 600;
            display: block;
            margin-left: auto;
            margin-right: auto;
            transition: background 0.3s ease;
        }

        .tasks__submit:hover {
            background-color: #0059b3;
        }
        .dashboard__nav {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin-top: 1rem;
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
    </style>

</head>
<body class="bg-light">
    

<div class="container tasks">
    <h2>Flight Tasks</h2>

    <?php if (empty($flights)): ?>
        <p>No flights assigned to you at the moment.</p>
    <?php else: ?>
        <form action="update_task.php" method="POST">
            <?php foreach ($flights as $flight): ?>
                <div class="tasks__flight">
                    <div class="tasks__flight-header">
                        Flight #<?= htmlspecialchars($flight['flight_number']) ?>
                        - <?= htmlspecialchars($flight['date']) ?> <?= htmlspecialchars($flight['time']) ?>
                        (<?= ucfirst($flight['flight_type']) ?> flight)
                    </div>

                    <?php
                    // Get tasks for this flight
                    $sql_tasks = "SELECT * FROM Task WHERE flight_id = ?";
                    $stmt_tasks = $conn->prepare($sql_tasks);
                    $stmt_tasks->bind_param("i", $flight['flight_id']);
                    $stmt_tasks->execute();
                    $result_tasks = $stmt_tasks->get_result();

                    $tasks_pre = [];
                    $tasks_during = [];

                    // Categorize tasks
                    while ($task = $result_tasks->fetch_assoc()) {
                        $task_name = $task['task_name'];

                        // Define category by task name (adjust as needed)
                        $pre_flight_keywords = ['Safety', 'Security', 'Boarding', 'Seat belt', 'Last cabin'];
                        $is_pre = false;
                        foreach ($pre_flight_keywords as $keyword) {
                            if (stripos($task_name, $keyword) !== false) {
                                $is_pre = true;
                                break;
                            }
                        }
                        if ($is_pre) {
                            $tasks_pre[] = $task;
                        } else {
                            $tasks_during[] = $task;
                        }
                    }
                    ?>

                    <?php if (!empty($tasks_pre)): ?>
                        <div class="tasks__group">
                            <div class="tasks__group-title">Pre-flight Tasks</div>
                            <?php foreach ($tasks_pre as $task): ?>
                                <div class="tasks__item">
                                    <input
                                        type="checkbox"
                                        class="tasks__checkbox"
                                        id="task_<?= $task['task_id'] ?>"
                                        name="tasks[<?= $task['task_id'] ?>]"
                                        value="1"
                                        <?= $task['completed'] ? 'checked' : '' ?>
                                    />
                                    <label for="task_<?= $task['task_id'] ?>"><?= htmlspecialchars($task['task_name']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($tasks_during)): ?>
                        <div class="tasks__group">
                            <div class="tasks__group-title">During Flight Tasks</div>
                            <?php foreach ($tasks_during as $task): ?>
                                <div class="tasks__item">
                                    <input
                                        type="checkbox"
                                        class="tasks__checkbox"
                                        id="task_<?= $task['task_id'] ?>"
                                        name="tasks[<?= $task['task_id'] ?>]"
                                        value="1"
                                        <?= $task['completed'] ? 'checked' : '' ?>
                                    />
                                    <label for="task_<?= $task['task_id'] ?>"><?= htmlspecialchars($task['task_name']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>

            <button type="submit" class="btn btn-primary tasks__submit">Update Task Status</button>
        </form>

        <nav class="dashboard__nav">
            <a class="dashboard__link" href="attendant_dashboard.php">Return To Dashboard</a>        
        </nav>
    <?php endif; ?>

</div>

</body>
</html>
