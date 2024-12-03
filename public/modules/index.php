<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../View/login.php");
    exit;
}

$cedula = $_SESSION['cedula'];
require_once('../../config.php');

$sql = "SELECT id_tipoUsuario, correo FROM usuarios WHERE cedula = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $cedula);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $id_tipo_usuario = $user['id_tipoUsuario'];
    $correo_usuario = $user['correo'];
} else {
    header("Location: ../View/login.php");
    exit;
}
$stmt->close();

$notificationCount = 0;

if ($id_tipo_usuario == 2) { // Si el usuario es estudiante

    $sqlCorreo = "
        SELECT correo 
        FROM usuarios 
        WHERE cedula = ?
    ";
    $stmtCorreo = $conn->prepare($sqlCorreo);
    $stmtCorreo->bind_param("s", $cedula);  // Usamos la cédula para obtener el correo
    $stmtCorreo->execute();
    $stmtCorreo->bind_result($correo);
    $stmtCorreo->fetch();
    $stmtCorreo->close();

    if ($correo) {

        // Luego despues de obtener ese correo, lo utilizamos para verificar en la base la cantidad de correos q tiene ese usuario(estudiante)
        $sqlNotificaciones = "
            SELECT COUNT(*) AS total 
            FROM notificaciones 
            WHERE correo_destinatario LIKE ?
        ";
        $stmtNotificaciones = $conn->prepare($sqlNotificaciones);
        $correoLike = "%" . $correo . "%";
        $stmtNotificaciones->bind_param("s", $correoLike);
        $stmtNotificaciones->execute();
        $stmtNotificaciones->bind_result($notificationCount);
        $stmtNotificaciones->fetch();
        $stmtNotificaciones->close();
    }
}


$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Asistencia Virtual</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <script src="/../AsistenciaVirtual/public/assets/scripts/scripts.js" defer></script>
    <link rel="icon" href="/../AsistenciaVirtual/public/assets/img/logo.png">
    <style>
        .notification-pulse {
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        .notification-dropdown {
            position: absolute;
            top: 100%;
            right: 0;
            z-index: 1000;
            display: none;
        }
        .notification-btn:focus + .notification-dropdown,
        .notification-dropdown:hover {
            display: block;
        }
    </style>
</head>

<body>
    <div class="sidebar" id="sidebar">
        <div class="icon-container">
            <i class="fas fa-graduation-cap"></i>
            <h4 class="mb-0">AsistenciaVirtual</h4>
        </div>

        <hr>
        <nav>

        <?php if ($id_tipo_usuario == 1): ?>
        <a class="nav-link" href="#" data-section="dashboard">
            <i class="fas fa-tachometer-alt"></i> Inicio
        </a>
        <a class="nav-link" href="#" data-section="usuarios">
            <i class="fas fa-users"></i> Usuarios
        </a>
        <a class="nav-link" href="#" data-section="cursos">
            <i class="fas fa-book"></i> Cursos
        </a>
        <a class="nav-link" href="#" data-section="reporte">
            <i class="fas fa-history"></i> Reporte General
        </a>
        <a class="nav-link" href="#" data-section="perfilProfesor">
            <i class="fas fa-user-circle"></i> Mi Perfil
        </a>
    <?php endif; ?>

    <?php if ($id_tipo_usuario == 3): ?>
        <a class="nav-link" href="#" data-section="dashboard">
            <i class="fas fa-tachometer-alt"></i> Inicio
        </a>
        <a class="nav-link" href="#" data-section="estudiantes">
            <i class="fas fa-users"></i> Estudiantes
        </a>
        <a class="nav-link" href="#" data-section="gestion">
            <i class="fas fa-book"></i> Asistencias
        </a>
        <a class="nav-link" href="#" data-section="historial">
            <i class="fas fa-history"></i> Historial
        </a>
        <a class="nav-link" href="#" data-section="notificaciones">
            <i class="fas fa-bell"></i> Notificaciones
        </a>
        <a class="nav-link" href="#" data-section="reporte">
            <i class="fas fa-chart-bar"></i> Reportes
        </a>

        <a class="nav-link" href="#" data-section="perfilProfesor">
            <i class="fas fa-user-circle"></i> Mi Perfil
        </a>
    <?php endif; ?>

    <!-- Seccion cuando el usuario es Estudiante-->
    <?php if ($id_tipo_usuario == 2): ?>
        <a class="nav-link active" href="#" data-section="inicioEstudiantes">
            <i class="fas fa-home"></i> Inicio
        </a>
        <a class="nav-link" href="#" data-section="clase">
            <i class="fas fa-calendar-alt"></i> Mis clases
        </a>
        <a class="nav-link" href="#" data-section="notificacionesEstudiantes">
            <i class="fas fa-bell"></i> Mis Notificaciones
        </a>
        <a class="nav-link" href="#" data-section="perfilEstudiante">
            <i class="fas fa-user-circle"></i> Mi perfil
        </a>
    <?php endif; ?>
    </nav>
    </div>

    <div class="main-content" id="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center">
                <button class="btn btn-light navbar-toggle" id="toggle-sidebar">
                    <i class="fas fa-bars"></i>
                </button>
                <h2 id="section-title" class="mb-0 ms-2">Inicio</h2>
            </div>
            <div class="d-flex align-items-center position-relative">
                <?php if ($id_tipo_usuario == 2): ?>
                <div class="position-relative">
                    <button class="btn btn-light notification-btn <?= $notificationCount > 0 ? 'notification-pulse' : ''; ?>" type="button" id="notification-dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger"><?= $notificationCount; ?></span>
                    </button>
                    <?php if ($notificationCount > 0): ?>
                    <div class="dropdown-menu notification-dropdown show">
                        <div class="dropdown-item">
                            <strong>Tienes notificaciones pendientes</strong>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <?php else: ?>
                <div class="dropdown me-3">
                    <button class="btn btn-light" type="button" disabled>
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-secondary">0</span>
                    </button>
                </div>
                <?php endif; ?>
                <div class="dropdown">
                    <button class="btn btn-light" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-1"></i>
                    </button>
                    <ul class="dropdown-menu">
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="#" id="verPerfil">Ver perfil</a></li>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="#" onclick="confirmarCierreSesion(event)">Cerrar sesión</a>
                        </li>
                    </ul>
                </div>
                
            </div>
        </div>

        <?php if ($id_tipo_usuario == 1): ?>
        <div id="dashboard" class="section active">
            <iframe src="inicioAdm.php"></iframe>
        </div>
        <div id="usuarios" class="section">
            <iframe src="gestUsuarios.php"></iframe>
        </div>
        <div id="cursos" class="section">
            <iframe src="gestCursos.php"></iframe>
        </div>
        <div id="reporteAdm" class="section">
            <iframe src="reportesAdm.php"></iframe>
        </div>
        
        <?php endif; ?>

        <?php if ($id_tipo_usuario == 3): ?>
        <div id="dashboard" class="section active">
            <iframe src="dashboard.php"></iframe>
        </div>
        <?php endif; ?>

        <?php if ($id_tipo_usuario == 2): ?>
        <div id="inicioEstudiantes" class="section active">
            <iframe src="inicioEstudiantes.php"></iframe>
        </div>
        <div id="clases" class="section">
            <iframe src="calendario.php"></iframe>
        </div>
        <div id="notificacionesEstudiantes" class="section">
            <iframe src="notificaciones.php"></iframe>
        </div>
        <div id="reporte" class="section">
            <iframe src="reportes.php"></iframe>
        </div>
        <div id="perfilEstudiante" class="section">
            <iframe src="perfil.php"></iframe>
        </div>
        <?php endif; ?>

        <div id="gestion" class="section">
            <iframe src="gestionAsistencia.php"></iframe>
        </div>
        <div id="historial" class="section">
            <iframe src="historial.php"></iframe>
        </div>
        <div id="estudiantes" class="section">
            <iframe src="estudiantes.php"></iframe>
        </div>
        <div id="notificaciones" class="section">
            <iframe src="notificaciones.php"></iframe>
        </div>
        <div id="clase" class="section">
            <iframe src="calendario.php"></iframe>
        </div>
        <div id="reporte" class="section">
            <iframe src="reportes.php"></iframe>
        </div>
        <div id="perfilProfesor" class="section">
            <iframe src="perfil.php"></iframe>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function confirmarCierreSesion(event) {
                                event.preventDefault(); // Evita que el enlace se ejecute inmediatamente
                                if (confirm("¿Estás seguro de que deseas cerrar sesión?")) {
                                    // Si el usuario confirma, redirige a la página de cierre de sesión
                                    window.location.href = "/AsistenciaVirtual/logout.php";
                                }
                            }
                            
        document.addEventListener('DOMContentLoaded', function() {
    const notificationBtn = document.getElementById('notification-dropdown');
    const verPerfilBtn = document.getElementById('verPerfil');

    // Manejo de notificaciones (solo para estudiantes)
    <?php if ($id_tipo_usuario == 2 && $notificationCount > 0): ?>
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Ocultar las notificaciones
            const notificationDiv = document.querySelector('.dropdown-menu.notification-dropdown');
            if (notificationDiv) {
                notificationDiv.style.display = 'none';
            }

            // Ocultar todas las secciones
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });

            // Activar la sección de notificaciones para estudiantes
            const notificationsSection = document.getElementById('notificacionesEstudiantes');
            if (notificationsSection) {
                notificationsSection.classList.add('active');
            }

            // Cambiar el título de la sección
            const sectionTitle = document.getElementById('section-title');
            if (sectionTitle) {
                sectionTitle.textContent = 'Mis Notificaciones';
            }

            // Marcar el enlace de notificaciones como activo
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            const notificationNavLink = document.querySelector('.nav-link[data-section="notificacionesEstudiantes"]');
            if (notificationNavLink) {
                notificationNavLink.classList.add('active');
            }
        });
    }
    <?php endif; ?>

    // Manejo de la sección de perfil
    if (verPerfilBtn) {
        verPerfilBtn.addEventListener('click', function(e) {
            e.preventDefault();

            // Ocultar todas las secciones
            document.querySelectorAll('.section').forEach(section => {
                section.classList.remove('active');
            });

            // Activar la sección de perfil dependiendo del tipo de usuario
            const perfilSection = document.getElementById('perfilEstudiante') || document.getElementById('perfilProfesor');
            if (perfilSection) {
                perfilSection.classList.add('active');
            }

            // Cambiar el título de la sección
            const sectionTitle = document.getElementById('section-title');
            if (sectionTitle) {
                sectionTitle.textContent = 'Mi Perfil';
            }

            // Marcar el enlace de "Mi perfil" como activo en la barra de navegación
            document.querySelectorAll('.nav-link').forEach(link => {
                link.classList.remove('active');
            });
            const perfilNavLink = document.querySelector('.nav-link[data-section="perfilEstudiante"]') || document.querySelector('.nav-link[data-section="perfilProfesor"]');
            if (perfilNavLink) {
                perfilNavLink.classList.add('active');
            }
        });
    }
});



    </script>
</body>
</html>