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
            $stmt_task = $conn->prepare("INSERT INTO Task (flight_id, task_name) VALUES (?, ?)");
            foreach ($tasks as $task) {
                $stmt_task->bind_param("is", $flight_id, $task);
                $stmt_task->execute();
            }
        }

        header("Location: dashboard.php");
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
    <script>
        function toggleChecklist() {
            var flightType = document.querySelector('input[name="flight_type"]:checked').value;
            document.getElementById('long_flight_tasks').style.display = (flightType === "long") ? 'block' : 'none';
            document.getElementById('short_flight_tasks').style.display = (flightType === "short") ? 'block' : 'none';
        }
    </script>
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2>Create New Flight</h2>

    <form method="POST" class="row g-3 mt-3">
        <div class="col-md-6">
            <label class="form-label">Flight Number</label>
            <input type="text" name="flight_number" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Date</label>
            <input type="date" name="date" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label">Time</label>
            <input type="time" name="time" class="form-control" required>
        </div>

        <div class="col-md-12">
            <label class="form-label">Flight Type</label>
            <div>
                <input type="radio" name="flight_type" value="long" id="long" onclick="toggleChecklist()" required> Long Flight
                <input type="radio" name="flight_type" value="short" id="short" onclick="toggleChecklist()" required class="ms-3"> Short Flight
            </div>
        </div>

        <!-- Long Flight Tasks -->
        <div id="long_flight_tasks" style="display:none;">
            <h4 class="mt-3">Pre-flight Tasks (Long Flight)</h4>
            <?php
            $long_pre = [
                "Safety equipment", "Security", "Boarding", "Seat belt check", "Last cabin check"
            ];
            foreach ($long_pre as $i => $task) {
                echo '<div class="form-check">
                        <input type="checkbox" class="form-check-input" name="tasks_long[]" value="'. $task .'" id="long_pre'.$i.'">
                        <label class="form-check-label" for="long_pre'.$i.'">'. $task .'</label>
                      </div>';
            }
            ?>
            <h4 class="mt-3">During Flight Tasks (Long Flight)</h4>
            <?php
            $long_during = [
                "Distributing earphones", "Prepare trolleys", "Prepare minerals", "Distributing food", "Answering calls"
            ];
            foreach ($long_during as $i => $task) {
                echo '<div class="form-check">
                        <input type="checkbox" class="form-check-input" name="tasks_long[]" value="'. $task .'" id="long_during'.$i.'">
                        <label class="form-check-label" for="long_during'.$i.'">'. $task .'</label>
                      </div>';
            }
            ?>
        </div>

        <!-- Short Flight Tasks -->
        <div id="short_flight_tasks" style="display:none;">
            <h4 class="mt-3">Pre-flight Tasks (Short Flight)</h4>
            <?php
            $short_pre = [
                "Safety equipment", "Security", "Boarding", "Seat belt check"
            ];
            foreach ($short_pre as $i => $task) {
                echo '<div class="form-check">
                        <input type="checkbox" class="form-check-input" name="tasks_short[]" value="'. $task .'" id="short_pre'.$i.'">
                        <label class="form-check-label" for="short_pre'.$i.'">'. $task .'</label>
                      </div>';
            }
            ?>
            <h4 class="mt-3">During Flight Tasks (Short Flight)</h4>
            <?php
            $short_during = [
                "Distributing earphones", "Prepare trolleys", "Distributing food"
            ];
            foreach ($short_during as $i => $task) {
                echo '<div class="form-check">
                        <input type="checkbox" class="form-check-input" name="tasks_short[]" value="'. $task .'" id="short_during'.$i.'">
                        <label class="form-check-label" for="short_during'.$i.'">'. $task .'</label>
                      </div>';
            }
            ?>
        </div>

        <div class="col-md-12 mt-3">
            <label class="form-label">Assign Attendant</label>
            <select name="staff_id" class="form-select" required>
                <?php
                $staff_query = "SELECT staff_id, first_name, last_name FROM Staff WHERE role = 'attendant'";
                $staff_result = $conn->query($staff_query);
                while ($staff = $staff_result->fetch_assoc()) {
                    echo "<option value='{$staff['staff_id']}'>{$staff['first_name']} {$staff['last_name']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-12 mt-3">
            <button type="submit" class="btn btn-primary">Create Flight</button>
        </div>
    </form>
</div>

</body>
</html>
