<?php
session_start();
if (isset($_SESSION['staff_id'])) {
    if ($_SESSION['role'] == 'admin') {
        header("Location: admin/admin_dashboard.php");
        exit();
    } else {
        header("Location: attendant/attendant_dashboard.php");
        exit();
    }
}
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM Staff WHERE email=? AND password=?");
    $stmt->bind_param("ss", $email, $password); // WARNING: hash passwords in real apps!
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        session_start();
        $_SESSION['staff_id'] = $row['staff_id'];
        $_SESSION['role'] = $row['role'];

        if ($row['role'] == 'admin') {
            header("Location: admin/admin_dashboard.php");
            die;
        } else {
            header("Location: attendant/attendant_dashboard.php");
            die;
        }
        exit();
    } else {
        $error = "Invalid credentials.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Login</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            * {
                box-sizing: border-box;
                margin: 0;
                padding: 0;
                font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            }
            body {
                background: linear-gradient(to bottom right, #cce7ff, #ffffff);
                height: 100vh;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            .login-container {
                background-color: #ffffff;
                padding: 2.5rem;
                border-radius: 15px;
                box-shadow: 0 4px 20px rgba(0, 51, 102, 0.2);
                width: 100%;
                max-width: 400px;
            }
            .login-title {
                text-align: center;
                color: #003366;
                font-size: 1.8rem;
                margin-bottom: 1.5rem;
            }

            form {
                display: flex;
                flex-direction: column;
            }

            label {
                color: #003366;
                margin-bottom: 0.3rem;
                font-weight: 500;
            }

            input[type="text"],
            input[type="password"] {
                padding: 0.7rem;
                margin-bottom: 1rem;
                border: 1px solid #99ccff;
                border-radius: 8px;
                outline: none;
                transition: border 0.3s ease;
            }

            input[type="text"]:focus,
            input[type="password"]:focus {
                border-color: #3399ff;
            }

            button {
                background-color: #003366;
                color: white;
                padding: 0.8rem;
                border: none;
                border-radius: 8px;
                font-size: 1rem;
                cursor: pointer;
                transition: background 0.3s ease;
            }

            button:hover {
                background-color: #0059b3;
            }

            .error {
                color: red;
                text-align: center;
                margin-bottom: 1rem;
            }
            </style>
    </head>
    <body>
        <div class="login-container">
            <h2 class="login-title">Login</h2>
            <form method="POST">
            Email: <input type="email" name="email" required><br>
            Password: <input type="password" name="password" required><br>
            <button type="submit">Login</button>
        </form>
        </div>
    </body>
<?php if (isset($error)) echo "<p>$error</p>"; ?>
