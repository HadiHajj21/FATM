<?php
$host = 'localhost';
$db = 'flightattendanttm';
$user = 'root';
$pass = ''; // default XAMPP MySQL password is empty

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
