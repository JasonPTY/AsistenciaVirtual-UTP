<?php
define("urlsite", "http://localhost/Demo-Sas/");

$host     = "localhost";
$username = "jasonpty";
$password = "jason27278";
$database = "loodle_system";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// SMTP
define('SMTP_USER', 'jason.arena@utp.ac.pa');
define('SMTP_PASS', 'ThePana27278_utp');
?>