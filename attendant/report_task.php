<?php
session_start();
if ($_SESSION['role'] !== 'attendant') {
    header("Location: ../login.php");
    exit();
}
include '../db.php';

$staff_id = $_SESSION['staff_id'];

// Fetch all flights and their incomplete tasks for this attendant
$sql = "
    SELECT f.flight_id, f.flight_number, t.task_id, t.task_name
    FROM Flight f
    JOIN Task t ON f.flight_id = t.flight_id
    WHERE f.staff_id = ? AND t.completed = 0
    ORDER BY f.date DESC, f.time ASC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

$flights_tasks = [];
while ($row = $result->fetch_assoc()) {
    $fid = $row['flight_id'];
    if (!isset($flights_tasks[$fid])) {
        $flights_tasks[$fid] = [
            'flight_number' => $row['flight_number'],
            'tasks' => []
        ];
    }
    $flights_tasks[$fid]['tasks'][] = [
        'task_id' => $row['task_id'],
        'task_name' => $row['task_name']
    ];
}
// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flight_id = $_POST['flight_id'];
    $task_id = $_POST['task_id'];
    $status = $_POST['status'];
    $reason = $_POST['reason'];

    $stmt = $conn->prepare("INSERT INTO Report (staff_id, flight_id, task_id, status, reason_if_incomplete, submission_time)
                            VALUES (?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("siiss", $staff_id, $flight_id, $task_id, $status, $reason);
    $stmt->execute();

    header("Location: attendant_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Submit Report</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        const tasksByFlight = <?= json_encode($flights_tasks) ?>;

        function updateTasks() {
            const flightSelect = document.getElementById("flightSelect");
            const taskSelect = document.getElementById("taskSelect");
            const selectedFlight = flightSelect.value;

            taskSelect.innerHTML = "";

            if (tasksByFlight[selectedFlight]) {
                tasksByFlight[selectedFlight].tasks.forEach(task => {
                    const opt = document.createElement("option");
                    opt.value = task.task_id;
                    opt.text = task.task_name;
                    taskSelect.appendChild(opt);
                });
            }
        }
    </script>
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
        .report-form-container {
            max-width: 900px;
            margin: 3rem auto;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 51, 102, 0.1);
        }

        .report-form-container h2 {
            color: #003366;
            margin-bottom: 2rem;
            text-align: center;
        }

        .report-form-container .form-label {
            color: #003366;
            font-weight: 600;
        }

        .report-form-container .form-select,
        .report-form-container .form-control {
            border: 1px solid #99ccff;
            border-radius: 8px;
            padding: 0.5rem;
        }

        .report-form-container .btn-primary {
            background-color: #003366;
            border: none;
            border-radius: 8px;
            padding: 0.6rem 1.2rem;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .report-form-container .btn-primary:hover {
            background-color: #0059b3;
        }

        .report-form-container .btn-danger {
            margin-top: 1rem;
            background-color: #cc0000;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            color: #fff;
            transition: background 0.3s ease;
        }

        .report-form-container .btn-danger:hover {
            background-color: #990000;
        }
        </style>
</head>
<body class="bg-light">

<div class="container report-form-container">
    <h2>Submit Task Report</h2>
    <form method="POST" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Flight</label>
            <select name="flight_id" class="form-select" id="flightSelect" onchange="updateTasks()" required>
                <option value="">Select a flight</option>
                <?php foreach ($flights_tasks as $flight_id => $flight): ?>
                    <option value="<?= $flight_id ?>">Flight <?= htmlspecialchars($flight['flight_number']) ?> (ID: <?= $flight_id ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label">Task</label>
            <select name="task_id" class="form-select" id="taskSelect" required>
                <option value="">Select a task</option>
            </select>
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
        
    <a class="mt-2 btn btn-primary btn-danger" href="attendant_dashboard.php">Return To Dashboard</a>        
        
</div>

</body>
</html>
