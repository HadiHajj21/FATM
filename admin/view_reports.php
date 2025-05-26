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
    <title>View Reports</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #e6f0fa; /* Baby blue */
            color: #003366; /* Dark blue */
        }

        h2 {
            color: #003366;
        }

        .table {
            background-color: #ffffff;
        }

        .table th, .table td {
            color: #003366;
        }

        .container {
            background-color: #ffffff;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }

        .btn-primary {
            background-color: #003366;
            border-color: #003366;
        }

        .btn-primary:hover {
            background-color: #002244;
            border-color: #002244;
        }

        .badge {
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4">Flight Reports</h2>

    <table class="table table-bordered table-hover">
        <thead class="table-light">
            <tr>
                <th>Report ID</th>
                <th>Attendant</th>
                <th>Flight Number</th>
                <th>Task</th>
                <th>Submission Time</th>
                <th>Status</th>
                <th>Reason (if incomplete)</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "
                SELECT r.report_id, r.submission_time, r.status, r.reason_if_incomplete,
                       s.first_name, s.last_name,
                       f.flight_number,
                       t.task_name
                FROM Report r
                LEFT JOIN Staff s ON r.staff_id = s.staff_id
                LEFT JOIN Flight f ON r.flight_id = f.flight_id
                LEFT JOIN Task t ON r.task_id = t.task_id
                ORDER BY r.submission_time DESC
            ";

            $result = $conn->query($query);
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $statusBadge = $row['status'] === 'complete'
                        ? '<span class="badge bg-success">Complete</span>'
                        : '<span class="badge bg-warning text-dark">Pending</span>';

                    echo "<tr>
                        <td>" . htmlspecialchars($row['report_id']) . "</td>
                        <td>" . htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) . "</td>
                        <td>" . htmlspecialchars($row['flight_number']) . "</td>
                        <td>" . htmlspecialchars($row['task_name']) . "</td>
                        <td>" . htmlspecialchars($row['submission_time']) . "</td>
                        <td>$statusBadge</td>
                        <td>" . htmlspecialchars($row['reason_if_incomplete'] ?: '-') . "</td>
                      </tr>";
                }
            } else {
                echo '<tr><td colspan="7" class="text-center">No reports found.</td></tr>';
            }
            ?>
        </tbody>
    </table>
    <a class="mt-2 btn btn-danger" href="admin_dashboard.php">Return</a>
</div>

</body>
</html>
