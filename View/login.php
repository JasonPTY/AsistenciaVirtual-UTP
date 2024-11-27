<?php
session_start();
require_once('../config.php');
$showModal = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $_POST['correo'];
    $pass = $_POST['pass'];

    $sql = "SELECT cedula, nombre, apellido, pass FROM usuarios WHERE correo = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        if (password_verify($pass, $user['pass'])) {
            $_SESSION['loggedin'] = true;
            $_SESSION['cedula'] = $user['cedula'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['apellido'] = $user['apellido'];
            header("Location: ../public/modules/index.php");
            exit();
        } else {
            $showModal = true;
        }
    } else {
        $showModal = true;
    }

    $stmt->close();
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
    <div id="errorModal" class="modal" style="display: <?php echo $showModal ? 'block' : 'none'; ?>">
        <div class="modal-content">
            <div class="modal-header">
                <div style="display: flex; align-items: center;">
                    <i class="fas fa-exclamation-circle modal-icon"></i>
                    <h5 style="margin: 0;">Error de autenticación</h5>
                </div>
                <button type="button" class="close-modal" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p>El correo electrónico o la contraseña son incorrectos. Por favor, inténtalo de nuevo.</p>
            </div>
        </div>
    </div>

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
            document.getElementById('errorModal').style.display = 'none';
        }
        window.onclick = function(event) {
            const modal = document.getElementById('errorModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>
