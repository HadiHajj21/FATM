<?php
session_start();
if (!isset($_SESSION['staff_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
include '../db.php';
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./styles/admin_styles.css">
</head>
<body>

<div class="container mt-4">

    <!-- Dashboard Greeting -->
    <div class="card mb-4">
        <div class="card-body text-center">
            <h2 class="card-title">Welcome, Admin 👋</h2>
            <p class="card-text text-muted">Manage flights, staff assignments, and notes from this central dashboard.</p>
        </div>
    </div>

    <!-- Navigation Panel -->
    <div class="card mb-4">
        <div class="card-header">
            Admin Actions
        </div>
        <div class="card-body">
            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a class="btn btn-primary" href="create_flight.php">Create New Flight</a>
                <a class="btn btn-primary" href="assign_flight.php">Assign Flights</a>
                <a class="btn btn-primary" href="assign_note.php">Assign Notes</a>
                <a class="btn btn-primary" href="view_reports.php">View Reports</a>
                <a class="btn btn-primary" href="signup.php">Add User</a>
                <a class="btn btn-danger" href="../logout.php">Logout</a>
            </div>
        </div>
    </div>

    <!-- Flights Assigned to Staff -->
    <div class="card mb-4">
        <div class="card-header">Flights Assigned to Staff</div>
        <div class="card-body">
            <table class="table table-bordered table-sm table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Flight Number</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Flight Type</th>
                        <th>Assigned Staff</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = "
                        SELECT f.flight_number, f.date, f.time, f.flight_type,
                               GROUP_CONCAT(CONCAT(s.first_name, ' ', s.last_name) SEPARATOR ', ') AS staff_names
                        FROM Flight f
                        LEFT JOIN Attend a ON f.flight_id = a.flight_id
                        LEFT JOIN Staff s ON a.staff_id = s.staff_id
                        GROUP BY f.flight_id
                        ORDER BY f.date DESC, f.time DESC
                    ";
                    $result = $conn->query($query);
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>" . htmlspecialchars($row['flight_number']) . "</td>
                                <td>" . htmlspecialchars($row['date']) . "</td>
                                <td>" . htmlspecialchars($row['time']) . "</td>
                                <td>" . htmlspecialchars(ucfirst($row['flight_type'])) . "</td>
                                <td>" . htmlspecialchars($row['staff_names'] ?? 'No staff assigned') . "</td>
                              </tr>";
                        }
                    } else {
                        echo '<tr><td colspan="5" class="text-center">No flights found.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

</body>
</html>
