<?php
session_start();
require_once('../config.php');

$MAX_ATTEMPTS = 3;
$BLOCK_DURATION = 180;

function logLoginAttempt($conn, $correo) {
    $attempts = 0;
    global $BLOCK_DURATION;
    $stmt = $conn->prepare("SELECT intentos, ultimo_intento FROM intentos_login WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    $currentTime = time();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $attempts = $row['intentos'];
        $lastAttempt = strtotime($row['ultimo_intento']);

        if ($currentTime - $lastAttempt > $BLOCK_DURATION) {
            $attempts = 0;
        }

        $attempts++;

        $stmt = $conn->prepare("UPDATE intentos_login SET intentos = ?, ultimo_intento = NOW() WHERE correo = ?");
        $stmt->bind_param("is", $attempts, $correo);
        $stmt->execute();
    } else {
        $stmt = $conn->prepare("INSERT INTO intentos_login (correo, intentos, ultimo_intento) VALUES (?, 1, NOW())");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
    }

    return $attempts;
}

function isLoginBlocked($conn, $correo, $maxAttempts, $blockDuration) {
    $stmt = $conn->prepare("SELECT intentos, ultimo_intento FROM intentos_login WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $currentTime = time();
        $lastAttempt = strtotime($row['ultimo_intento']);

        if ($row['intentos'] >= $maxAttempts && ($currentTime - $lastAttempt <= $blockDuration)) {
            return true;
        }
    }

    return false;
}

function resetLoginAttempts($conn, $correo) {
    $stmt = $conn->prepare("UPDATE intentos_login SET intentos = 0, ultimo_intento = NOW() WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
}

$showModal = false;
$blockModal = false;
$remainingTime = 0;
$loginSuccessful = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $pass = $_POST['pass'];

    if (isLoginBlocked($conn, $correo, $MAX_ATTEMPTS, $BLOCK_DURATION)) {
        //Modal bloqueado por intentos fallidos
        $blockModal = true;
        $stmt = $conn->prepare("SELECT TIMESTAMPDIFF(SECOND, ultimo_intento, NOW()) as tiempo_transcurrido FROM intentos_login WHERE correo = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $remainingTime = $BLOCK_DURATION - $row['tiempo_transcurrido'];
    } else {
        $sql = "SELECT cedula, nombre, apellido, pass FROM usuarios WHERE correo = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();

            if (password_verify($pass, $user['pass'])) {
                resetLoginAttempts($conn, $correo);

                $_SESSION['loggedin'] = true;
                $_SESSION['cedula'] = $user['cedula'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['apellido'] = $user['apellido'];

                $ip_address = $_SERVER['REMOTE_ADDR'];
                $user_agent = $_SERVER['HTTP_USER_AGENT'];
                $inicio_sesion = date('Y-m-d H:i:s');
                
                $sql = "INSERT INTO sesiones_usuarios (cedula, inicio_sesion, ip_address, user_agent) 
                        VALUES (?, ?, ?, ?)";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssss", $user['cedula'], $inicio_sesion, $ip_address, $user_agent);
                $stmt->execute();
                $stmt->close();
                
                header("Location: ../public/modules/index.php");
                exit();
            } else {
                $attempts = logLoginAttempt($conn, $correo);
                $showModal = true;
            }
        } else {
            $attempts = logLoginAttempt($conn, $correo);
            $showModal = true;
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login-AsistenciaUTP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="/../AsistenciaVirtual/public/assets/img/logo.png">
    <link rel="stylesheet" href="/../AsistenciaVirtual/public/assets/css/login.css">
</head>
<body>
    <?php if ($showModal): ?>
    <div id="errorModal" class="modal" style="display: block;">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-exclamation-circle modal-icon"></i>
                <h5>Error de autenticación</h5>
                <button type="button" class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>El correo o la contraseña son incorrectos.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($blockModal): ?>
    <div id="blockModal" class="modal" style="display: block;">
        <div class="modal-content">
            <div class="modal-header">
                <i class="fas fa-ban modal-icon"></i>
                <h5>Acceso bloqueado</h5>
                <button type="button" class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>Has excedido el número de intentos permitidos. Inténtalo de nuevo en <?php echo ceil($remainingTime / 60); ?> minutos.</p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <div class="login-container">
        <img src="/AsistenciaVirtual/public/assets/img/logo.png" alt="Logo UTP">
        <div class="login-header">
            <h1>Bienvenido</h1>
            <p>Ingresa tus credenciales para acceder</p>
        </div>
        <form method="post" action="">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" placeholder="Correo Institucional" name="correo" required>
            </div>
            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" id="password" placeholder="Contraseña" name="pass" required minlength="6">
            </div>
            <button type="submit" class="login-button">Iniciar Sesión</button>
            <div class="forgot-password">
                <a href="#">¿Olvidaste tu contraseña?</a>
            </div>
        </form>
    </div>

    <script>
        function closeModal() {
            const modals = document.querySelectorAll('.modal');
            modals.forEach(modal => modal.style.display = 'none');
        }
    </script>
</body>
</html>
