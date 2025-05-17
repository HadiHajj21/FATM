<?php
session_start();
if (!isset($_SESSION['staff_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

include '../db.php';

// Handle note assignment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $note_content = $_POST['note'];
    $staff_id = $_POST['staff_id'];
    $flight_id = !empty($_POST['flight_id']) ? $_POST['flight_id'] : null;

    // Insert into Note table
    $stmt = $conn->prepare("INSERT INTO Note (content) VALUES (?)");
    $stmt->bind_param("s", $note_content);
    $stmt->execute();
    $note_id = $stmt->insert_id;

    // Insert into NoteAssignment table
    $stmt = $conn->prepare("INSERT INTO NoteAssignment (note_id, staff_id, flight_id) VALUES (?, ?, ?)");
    if ($flight_id) {
        $stmt->bind_param("iii", $note_id, $staff_id, $flight_id);
    } else {
        $stmt->bind_param("iii", $note_id, $staff_id, $null = null);
    }
    $stmt->execute();

    $success = "Note successfully assigned.";
}

// Fetch attendants
$attendants = $conn->query("SELECT staff_id, first_name FROM Staff WHERE role = 'attendant' ORDER BY first_name ASC")->fetch_all(MYSQLI_ASSOC);

// Fetch flights
$flights = $conn->query("SELECT flight_id, flight_number, date FROM Flight ORDER BY date DESC")->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Assign Note</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <h2>Assign Note to Attendant</h2>

    <?php if (isset($success)): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Select Attendant</label>
            <select name="staff_id" class="form-select" required>
                <option value="">-- Choose Attendant --</option>
                <?php foreach ($attendants as $attendant): ?>
                    <option value="<?= $attendant['staff_id'] ?>"><?= htmlspecialchars($attendant['first_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Assign to Flight (optional)</label>
            <select name="flight_id" class="form-select">
                <option value="">-- No specific flight --</option>
                <?php foreach ($flights as $flight): ?>
                    <option value="<?= $flight['flight_id'] ?>">
                        Flight <?= htmlspecialchars($flight['flight_number']) ?> - <?= $flight['date'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-12">
            <label class="form-label">Note Content</label>
            <textarea name="note" class="form-control" rows="5" required></textarea>
        </div>

        <div class="col-12">
            <button type="submit" class="btn btn-primary">Assign Note</button>
        </div>
        
    </form>
    <a class="mt-2 btn btn-danger" href="admin_dashboard.php">Dashboard</a>
</div>
</body>
</html>
