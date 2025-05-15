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
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2 class="mb-4">Welcome, Admin 👋</h2>

    <!-- Navigation -->
    <nav class="nav mb-4">
        <a class="nav-link active" href="dashboard.php">Dashboard</a>
        <a class="btn btn-primary mb-3" href="create_flight.php">Create New Flight</a>
        <a class="nav-link" href="assign_flight.php">Assign Flights</a>
        <a class="nav-link" href="assign_note.php">Assign Notes</a>
        <a class="nav-link" href="signup.php">Add User</a>
        <a class="nav-link" href="../logout.php">Logout</a>
    </nav>

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
                    // Fetch flights with assigned staff
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

    <!-- Assign Staff to Flight Form -->
    <div class="card">
        <div class="card-header">Assign Staff to Flight</div>
        <div class="card-body">
            <form action="assign_flight.php" method="POST" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Staff ID</label>
                    <input type="text" name="staff_id" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Flight ID</label>
                    <input type="text" name="flight_id" class="form-control" required>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">Assign</button>
                </div>
            </form>
        </div>
    </div>

</div>

</body>
</html>
