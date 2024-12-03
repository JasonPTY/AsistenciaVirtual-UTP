<?php
session_start();

require_once('./Model/User.php');

$host = "localhost";
$username = "jasonpty";
$password = "jason27278";
$database = "asistencia_virtual";

// Crear la conexión
$conn = new mysqli($host, $username, $password, $database);

// Crear el objeto de la clase Usuarios
$usuarios = new Usuarios($conn);

if (isset($_COOKIE['remember_me'])) {
    setcookie('remember_me', '', time() - 3600, "/"); // Elimina la cookie
}

if (isset($_SESSION['cedula'])) {
    $usuarios->eliminarTokenDeRecuerdo($_SESSION['cedula']); // Eliminar el token de la base de datos
}

if (isset($_SESSION['cedula'])) {
    $fin_sesion = date('Y-m-d H:i:s');
    $sql = "UPDATE sesiones_usuarios 
            SET fin_sesion = ? 
            WHERE cedula = ? AND fin_sesion IS NULL 
            ORDER BY inicio_sesion DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $fin_sesion, $_SESSION['cedula']);
    $stmt->execute();
    $stmt->close();
}
session_unset();
session_destroy();
header("Location: /AsistenciaVirtual/View/login.php");
exit();

?>
