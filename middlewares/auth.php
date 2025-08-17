<?php
session_start();

if (empty($_SESSION['auth_token'])) {
    header("Location: /public/login.php");
    exit();
}
