<?php
session_start();
if (!isset($_SESSION['staff_id']) || $_SESSION['role'] !== 'attendant') {
    header("Location: ../login.php");
    exit();
}
include '../db.php';

$staff_id = $_SESSION['staff_id'];

// Fetch assigned notes
$sql = "
    SELECT n.content, f.flight_number, f.date, na.assigned_at
    FROM Note n
    JOIN NoteAssignment na ON n.note_id = na.note_id
    LEFT JOIN Flight f ON na.flight_id = f.flight_id
    WHERE na.staff_id = ?
    ORDER BY na.assigned_at DESC
";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$notes = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>My Notes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .notes-container {
            max-width: 900px;
            margin: 3rem auto;
            padding: 2rem;
            background-color: #ffffff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 51, 102, 0.1);
        }

        .notes-container h2 {
            color: #003366;
            margin-bottom: 2rem;
            text-align: center;
        }

        .card {
            border: 1px solid #99ccff;
            border-radius: 12px;
            background-color: #f9fbff;
        }

        .card-body {
            padding: 1rem 1.25rem;
        }

        .card-text {
            font-size: 1rem;
            color: #003366;
        }

        .text-muted {
            font-size: 0.9rem;
            color: #666;
        }

        .btn-danger {
            margin-top: 1rem;
            background-color: #cc0000;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            color: #fff;
            transition: background 0.3s ease;
        }

        .btn-danger:hover {
            background-color: #990000;
        }
    </style>
</head>
<body class="bg-light">
<div class="container notes-container">
    <h2 class="mb-4">Assigned Notes</h2>

    <?php if (empty($notes)): ?>
        <div class="alert alert-info">No notes have been assigned to you yet.</div>
    <?php else: ?>
        <?php foreach ($notes as $note): ?>
            <div class="card mb-3">
                <div class="card-body">
                    <p class="card-text"><?= nl2br(htmlspecialchars($note['content'])) ?></p>
                    <small class="text-muted">
                        Assigned on <?= date('Y-m-d H:i', strtotime($note['assigned_at'])) ?>
                        <?php if (!empty($note['flight_number'])): ?>
                            &nbsp;|&nbsp; For Flight <strong><?= htmlspecialchars($note['flight_number']) ?></strong> (<?= $note['date'] ?>)
                        <?php endif; ?>
                    </small>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <a class="mt-2 btn btn-primary btn-danger" href="attendant_dashboard.php">Return To Dashboard</a>
    
</div>
</body>
</html>
