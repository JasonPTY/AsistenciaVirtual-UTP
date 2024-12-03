<?php if (!isset($_SESSION)) session_start(); ?>
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
    <?php if (isset($showModal) && $showModal): ?>
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
    <?php if (isset($blockModal) && $blockModal): ?>
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
        <form method="post" action="../Controller/controllerUser.php">
    <div class="input-group">
        <i class="fas fa-envelope"></i>
        <input type="email" placeholder="Correo Institucional" name="correo" required>
    </div>
    <div class="input-group">
        <i class="fas fa-lock"></i>
        <input type="password" id="password" placeholder="Contraseña" name="pass" required minlength="6">
    </div>
    <div class="remember-me">
        <input type="checkbox" name="remember_me" id="remember_me">
        <label for="remember_me">Recordarme</label>
    </div>
    <br>
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