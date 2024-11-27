<?php
$loginError = ''; // Inicializar la variable

$host = "localhost";
$username = "jasonpty";
$password = "jason27278";
$database = "asistencia_virtual";

// Conexión a la base de datos
$conn = new mysqli($host, $username, $password, $database);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $password = $_POST['pass'];

    $sql = "SELECT pass FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->bind_result($db_password);

    if ($stmt->fetch()) {
        if ($password == $db_password) {
            session_start();
            $_SESSION['correo'] = $correo;
            header("Location: /AsistenciaVirtual/public/modules/index.php");
            exit();
        } else {
            $loginError = "Contraseña incorrecta.";
        }
    } else {
        $loginError = "Correo no registrado.";
    }

    $stmt->close();

    // Redirigir a login.php con el mensaje de error
    header("Location: /AsistenciaVirtual/View/login.php?error=" . urlencode($loginError));
    exit();
}
?>



