<?php
session_start();
if (!isset($_SESSION['staff_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $flight_number = $_POST['flight_number'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $flight_type = $_POST['flight_type'];
    $staff_id = $_POST['staff_id'];

    $stmt = $conn->prepare("INSERT INTO Flight (flight_number, date, time, flight_type, staff_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $flight_number, $date, $time, $flight_type, $staff_id);

    if ($stmt->execute()) {
        $flight_id = $stmt->insert_id;
        $tasks = ($flight_type === 'long') ? ($_POST['tasks_long'] ?? []) : ($_POST['tasks_short'] ?? []);

        if (!empty($tasks)) {
            $stmt_task = $conn->prepare("INSERT INTO Task (flight_id, task_name, category) VALUES (?, ?, ?)");
            foreach ($tasks as $task) {
                list($task_name, $category) = explode('|', $task);
                $stmt_task->bind_param("iss", $flight_id, $task_name, $category);
                $stmt_task->execute();
            }
        }

        header("Location: admin_dashboard.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Create Flight</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e6f0fa; 
            color: #003366; 
        }

        h2, h4 {
            color: #003366; 
        }

        .form-label {
            color: #003366;
            font-weight: 500;
        }

        .form-control, .form-select {
            border: 1px solid #003366;
            background-color: #ffffff; 
            color: #003366;
        }

        .form-check-input:checked {
            background-color: #003366;
            border-color: #003366;
        }

        .form-check-label {
            color: #003366;
        }

        .btn-primary {
            background-color: #003366;
            border-color: #003366;
        }

        .btn-primary:hover {
            background-color: #002244;
            border-color: #002244;
        }

        .container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
    </style>
    <script>
        function toggleChecklist() {
            const flightType = document.querySelector('input[name="flight_type"]:checked').value;
            document.getElementById('long_flight_tasks').style.display = (flightType === "long") ? 'block' : 'none';
            document.getElementById('short_flight_tasks').style.display = (flightType === "short") ? 'block' : 'none';
        }
    </script>
</head>
<body>

<div class="container py-5">
    <div class="card p-4">
        <h2 class="mb-4 text-center">Create New Flight</h2>

        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Flight Number</label>
                <input type="text" name="flight_number" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label class="form-label">Time</label>
                <input type="time" name="time" class="form-control" required>
            </div>

            <div class="col-12">
                <label class="form-label">Flight Type</label><br>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="flight_type" value="long" onclick="toggleChecklist()" required id="flight_long">
                    <label class="form-check-label" for="flight_long">Long Flight</label>
                </div>
                <div class="form-check form-check-inline ms-3">
                    <input class="form-check-input" type="radio" name="flight_type" value="short" onclick="toggleChecklist()" required id="flight_short">
                    <label class="form-check-label" for="flight_short">Short Flight</label>
                </div>
            </div>

            <!-- Long Flight Tasks -->
            <div id="long_flight_tasks" style="display:none;">
                <div class="task-section">
                    <h4>Pre-flight Tasks (Long Flight)</h4>
                    <?php
                    $long_pre = ["Safety equipment", "Security", "Boarding", "Seat belt check", "Last cabin check"];
                    foreach ($long_pre as $i => $task) {
                        echo "<div class='form-check'>
                                <input class='form-check-input' type='checkbox' name='tasks_long[]' value='$task|Pre-flight' id='long_pre$i'>
                                <label class='form-check-label' for='long_pre$i'>$task</label>
                              </div>";
                    }
                    ?>
                    <h4>During Flight Tasks (Long Flight)</h4>
                    <?php
                    $long_during = ["Distributing earphones", "Prepare trolleys", "Prepare minerals", "Distributing food", "Answering calls"];
                    foreach ($long_during as $i => $task) {
                        echo "<div class='form-check'>
                                <input class='form-check-input' type='checkbox' name='tasks_long[]' value='$task|During flight' id='long_during$i'>
                                <label class='form-check-label' for='long_during$i'>$task</label>
                              </div>";
                    }
                    ?>
                </div>
            </div>

            <!-- Short Flight Tasks -->
            <div id="short_flight_tasks" style="display:none;">
                <div class="task-section">
                    <h4>Pre-flight Tasks (Short Flight)</h4>
                    <?php
                    $short_pre = ["Safety equipment", "Security", "Boarding", "Seat belt check"];
                    foreach ($short_pre as $i => $task) {
                        echo "<div class='form-check'>
                                <input class='form-check-input' type='checkbox' name='tasks_short[]' value='$task|Pre-flight' id='short_pre$i'>
                                <label class='form-check-label' for='short_pre$i'>$task</label>
                              </div>";
                    }
                    ?>
                    <h4>During Flight Tasks (Short Flight)</h4>
                    <?php
                    $short_during = ["Distributing earphones", "Prepare trolleys", "Distributing food"];
                    foreach ($short_during as $i => $task) {
                        echo "<div class='form-check'>
                                <input class='form-check-input' type='checkbox' name='tasks_short[]' value='$task|During flight' id='short_during$i'>
                                <label class='form-check-label' for='short_during$i'>$task</label>
                              </div>";
                    }
                    ?>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Assign Attendant</label>
                <select name="staff_id" class="form-select" required>
                    <option value="">Select Attendant</option>
                    <?php
                    $staff_query = "SELECT staff_id, first_name, last_name FROM Staff WHERE role = 'attendant'";
                    $staff_result = $conn->query($staff_query);
                    while ($staff = $staff_result->fetch_assoc()) {
                        echo "<option value='{$staff['staff_id']}'>{$staff['first_name']} {$staff['last_name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="col-12 text-center mt-4">
                <button type="submit" class="btn btn-primary px-5">Create Flight</button>
            </div>
        </form>
    </div>
</div>

</body>
</html>
