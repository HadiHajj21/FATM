<?php

session_start();

if(isset($_SESSION['staff_id'])){
    unset($_SESSION['staff_id']);
}

header("Location: login.php");