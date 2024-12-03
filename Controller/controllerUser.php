<?php
session_start();
require_once('../Model/User.php');

$MAX_ATTEMPTS = 3;
$BLOCK_DURATION = 180;

$host = "localhost";
$username = "jasonpty";
$password = "jason27278";
$database = "asistencia_virtual";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

$usuarios = new Usuarios($conn);

$showModal = false;
$blockModal = false;
$remainingTime = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $pass = $_POST['pass'];

    // Verifica si el usuario está bloqueado debido a intentos fallidos
    if ($usuarios->estaBloqueado($correo, $MAX_ATTEMPTS, $BLOCK_DURATION)) {
        $blockModal = true;
        $stmt = $conn->prepare("SELECT TIMESTAMPDIFF(SECOND, ultimo_intento, NOW()) as tiempo_transcurrido FROM intentos_login WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $remainingTime = $BLOCK_DURATION - $row['tiempo_transcurrido'];
    } else {
        // Obtener los datos del usuario por correo
        $usuario = $usuarios->obtenerUsuarioPorCorreo($correo);

        // Verificar si el usuario existe y la contraseña es correcta
        if ($usuario && password_verify($pass, $usuario['pass'])) {
            $usuarios->resetearIntentos($correo);

            $_SESSION['loggedin'] = true;
            $_SESSION['cedula'] = $usuario['cedula'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['apellido'] = $usuario['apellido'];

            // Registrar sesión (IP y agente de usuario)
            $ip_address = $_SERVER['REMOTE_ADDR'];
            $user_agent = $_SERVER['HTTP_USER_AGENT'];
            $usuarios->registrarSesion($usuario['cedula'], $ip_address, $user_agent);

            // Si el usuario seleccionó "Recordarme"
            if (isset($_POST['remember_me'])) {
                $token = bin2hex(random_bytes(16));
                $usuarios->guardarTokenDeRecuerdo($usuario['cedula'], $token);

                setcookie('remember_me', $token, time() + (30 * 24 * 60 * 60), "/"); // 30 días de expiración
            }
            header("Location: ../public/modules/index.php");
            exit();
        } else {
            $usuarios->registrarIntento($correo, $BLOCK_DURATION);
            $showModal = true;
        }
    }
}

include('../view/login.php');
?>
