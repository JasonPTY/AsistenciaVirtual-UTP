<?php
define("urlsite", "http://localhost/AsistenciaVirtual/");

$host = "localhost";
$username = "jasonpty";
$password = "jason27278";
$database = "asistencia_virtual";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}
?>
