<?php
session_start();
if (!isset($_SESSION['staff_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../db.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = intval($_POST['staff_id']);
    $flight_id = intval($_POST['flight_id']);

    // Check if assignment already exists
    $checkStmt = $conn->prepare("SELECT * FROM Attend WHERE staff_id = ? AND flight_id = ?");
    $checkStmt->bind_param("ii", $staff_id, $flight_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows === 0) {
        // Insert assignment
        $stmt = $conn->prepare("INSERT INTO Attend (staff_id, flight_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $staff_id, $flight_id);
        if ($stmt->execute()) {
            $message = "Staff assigned to flight successfully.";
        } else {
            $message = "Error assigning staff: " . $conn->error;
        }
    } else {
        $message = "This staff is already assigned to this flight.";
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Assign Staff to Flight</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2>Assign Staff to Flight</h2>

    <?php if (isset($message)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3 mb-4">
        <div class="col-md-6">
            <label for="staff_id" class="form-label">Select Staff (Attendant)</label>
            <select name="staff_id" id="staff_id" class="form-select" required>
                <option value="" disabled selected>Select Staff</option>
                <?php
                $staff_query = "SELECT staff_id, first_name, last_name FROM Staff WHERE role = 'attendant' ORDER BY first_name";
                $staff_result = $conn->query($staff_query);
                while ($staff = $staff_result->fetch_assoc()) {
                    echo "<option value='{$staff['staff_id']}'>" . htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-6">
            <label for="flight_id" class="form-label">Select Flight</label>
            <select name="flight_id" id="flight_id" class="form-select" required>
                <option value="" disabled selected>Select Flight</option>
                <?php
                $flight_query = "SELECT flight_id, flight_number, date, time FROM Flight ORDER BY date DESC, time DESC";
                $flight_result = $conn->query($flight_query);
                while ($flight = $flight_result->fetch_assoc()) {
                    $display = htmlspecialchars($flight['flight_number'] . " | " . $flight['date'] . " " . $flight['time']);
                    echo "<option value='{$flight['flight_id']}'>{$display}</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Assign Staff</button>
        </div>
    </form>

    <hr>

    <h3>Existing Assignments</h3>
    <table class="table table-bordered table-striped">
        <thead class="table-light">
            <tr>
                <th>Flight Number</th>
                <th>Date</th>
                <th>Time</th>
                <th>Staff Name</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $assignments_query = "
                SELECT f.flight_number, f.date, f.time, s.first_name, s.last_name
                FROM Attend a
                JOIN Flight f ON a.flight_id = f.flight_id
                JOIN Staff s ON a.staff_id = s.staff_id
                ORDER BY f.date DESC, f.time DESC, s.first_name
            ";
            $assignments_result = $conn->query($assignments_query);
            if ($assignments_result->num_rows > 0) {
                while ($assign = $assignments_result->fetch_assoc()) {
                    echo "<tr>
                        <td>" . htmlspecialchars($assign['flight_number']) . "</td>
                        <td>" . htmlspecialchars($assign['date']) . "</td>
                        <td>" . htmlspecialchars($assign['time']) . "</td>
                        <td>" . htmlspecialchars($assign['first_name'] . ' ' . $assign['last_name']) . "</td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='text-center'>No assignments found.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>
