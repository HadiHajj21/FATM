<?php
session_start();
if ($_SESSION['role'] !== 'attendant') {
    header("Location: ../login.php");
    exit();
}
include '../db.php';

$staff_id = $_SESSION['staff_id'];
?>
<div class="text-end mt-3">
    <form action="../logout.php" method="post">
        <button type="submit" class="btn btn-danger btn-sm">Logout</button>
    </form>
</div>

<!DOCTYPE html>
<html>
<head>
    <title>View Notes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
    <h2>Flight Notes</h2>
    <table class="table table-bordered table-sm mt-3">
        <thead class="table-light">
            <tr>
                <th>Flight ID</th>
                <th>Note</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $query = "SELECT a.flight_id, n.content
                      FROM Assign a
                      JOIN Note n ON a.note_id = n.note_id
                      WHERE a.staff_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("s", $staff_id);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>{$row['flight_id']}</td>
                        <td>{$row['content']}</td>
                      </tr>";
            }
            ?>
        </tbody>
    </table>
</div>

</body>
</html>

