<?php
session_start();

require_once __DIR__ . '/../../app/modules/notification/notificationRepository.php';

// Guard con remember_me
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    if (isset($_COOKIE['remember_me'])) {
        require_once __DIR__ . '/../../Model/User.php';
        require_once __DIR__ . '/../../app/core/database/Database.php';

        $conn     = Database::getConnection();
        $usuarios = new Usuarios($conn);
        $usuario  = $usuarios->obtenerUsuarioPorToken($_COOKIE['remember_me']);

        if ($usuario) {
            $_SESSION['loggedin'] = true;
            $_SESSION['cedula']   = $usuario['cedula'];
            header("Location: /Demo-Sas/public/modules/index.php");
            exit;
        }
    }
    header("Location: /Demo-Sas/View/login.php");
    exit;
}

$cedula = $_SESSION['cedula'];
$repo   = new NotificationRepository();
$self   = basename(__FILE__);

$idTipoUsuario   = $repo->getTipoUsuario($cedula);
$notificationCount = 0;

if ($idTipoUsuario === 2) {
    $correo            = $repo->getCorreoByCedula($cedula);
    $notificationCount = $correo ? $repo->countNotificacionesEstudiante($correo) : 0;
}
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
    <link rel="icon" href="/../Demo-Sas/public/assets/img/logo.png">
    <script src="../assets/scripts/scripts.js" defer></script>
    <style>
        .notification-pulse { animation: pulse 1.5s infinite; }
        @keyframes pulse {
            0%   { transform: scale(1);   }
            50%  { transform: scale(1.1); }
            100% { transform: scale(1);   }
        }
        .notification-dropdown { position: absolute; top: 100%; right: 0; z-index: 1000; display: none; }
        .notification-btn:focus + .notification-dropdown,
        .notification-dropdown:hover { display: block; }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
    <div class="icon-container">
        <i class="fas fa-graduation-cap"></i>
        <h4 class="mb-0">LOODLE</h4>
    </div>
    <hr>
    <nav>
        <?php if ($idTipoUsuario === 1): ?>
            <a class="nav-link" href="#" data-section="dashboard"><i class="fas fa-tachometer-alt"></i> Inicio</a>
            <a class="nav-link" href="#" data-section="usuarios"><i class="fas fa-users"></i> Usuarios</a>
            <a class="nav-link" href="#" data-section="cursos"><i class="fas fa-book"></i> Cursos</a>
            <a class="nav-link" href="#" data-section="reporte"><i class="fas fa-history"></i> Reporte General</a>
            <a class="nav-link" href="#" data-section="perfilProfesor"><i class="fas fa-user-circle"></i> Mi Perfil</a>
        <?php endif; ?>

        <?php if ($idTipoUsuario === 3): ?>
            <a class="nav-link" href="#" data-section="dashboard"><i class="fas fa-tachometer-alt"></i> Inicio</a>
            <a class="nav-link" href="#" data-section="estudiantes"><i class="fas fa-users"></i> Estudiantes</a>
            <a class="nav-link" href="#" data-section="gestion"><i class="fas fa-book"></i> Asistencias</a>
            <a class="nav-link" href="#" data-section="historial"><i class="fas fa-history"></i> Historial</a>
            <a class="nav-link" href="#" data-section="notificaciones"><i class="fas fa-bell"></i> Notificaciones</a>
            <a class="nav-link" href="#" data-section="reporte"><i class="fas fa-chart-bar"></i> Reportes</a>
            <a class="nav-link" href="#" data-section="perfilProfesor"><i class="fas fa-user-circle"></i> Mi Perfil</a>
        <?php endif; ?>

        <?php if ($idTipoUsuario === 2): ?>
            <a class="nav-link active" href="#" data-section="inicioEstudiantes"><i class="fas fa-home"></i> Inicio</a>
            <a class="nav-link" href="#" data-section="clase"><i class="fas fa-calendar-alt"></i> Mis clases</a>
            <a class="nav-link" href="#" data-section="notificacionesEstudiantes"><i class="fas fa-bell"></i> Mis Notificaciones</a>
            <a class="nav-link" href="#" data-section="perfilEstudiante"><i class="fas fa-user-circle"></i> Mi perfil</a>
        <?php endif; ?>
    </nav>
</div>

<!-- Contenido principal -->
<div class="main-content" id="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <button class="btn btn-light navbar-toggle" id="toggle-sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <h2 id="section-title" class="mb-0 ms-2">Inicio</h2>
        </div>
        <div class="d-flex align-items-center position-relative">

            <!-- Campana notificaciones -->
            <?php if ($idTipoUsuario === 2): ?>
                <div class="position-relative">
                    <button class="btn btn-light notification-btn <?= $notificationCount > 0 ? 'notification-pulse' : '' ?>"
                            type="button" id="notification-dropdown">
                        <i class="fas fa-bell"></i>
                        <span class="badge bg-danger"><?= $notificationCount ?></span>
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

            <!-- Menú usuario -->
            <div class="dropdown">
                <button class="btn btn-light" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-user-circle me-1"></i>
                </button>
                <ul class="dropdown-menu">
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" id="verPerfil">Ver perfil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="#" onclick="confirmarCierreSesion(event)">Cerrar sesión</a></li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Secciones por tipo de usuario -->
    <?php if ($idTipoUsuario === 1): ?>
        <div id="dashboard"  class="section active"><iframe src="inicioAdm.php"></iframe></div>
        <div id="usuarios"   class="section"><iframe src="gestUsuarios.php"></iframe></div>
        <div id="cursos"     class="section"><iframe src="gestCursos.php"></iframe></div>
        <div id="reporte"    class="section"><iframe src="reportesAdm.php"></iframe></div>
        <div id="perfilProfesor" class="section"><iframe src="perfil.php"></iframe></div>
    <?php endif; ?>

    <?php if ($idTipoUsuario === 3): ?>
        <div id="dashboard"      class="section active"><iframe src="dashboard.php"></iframe></div>
        <div id="estudiantes"    class="section"><iframe src="estudiantes.php"></iframe></div>
        <div id="gestion"        class="section"><iframe src="gestionAsistencia.php"></iframe></div>
        <div id="historial"      class="section"><iframe src="historial.php"></iframe></div>
        <div id="notificaciones" class="section"><iframe src="notificaciones.php"></iframe></div>
        <div id="reporte"        class="section"><iframe src="reportes.php"></iframe></div>
        <div id="perfilProfesor" class="section"><iframe src="perfil.php"></iframe></div>
    <?php endif; ?>

    <?php if ($idTipoUsuario === 2): ?>
        <div id="inicioEstudiantes"        class="section active"><iframe src="inicioEstudiantes.php"></iframe></div>
        <div id="clase"                    class="section"><iframe src="calendario.php"></iframe></div>
        <div id="notificacionesEstudiantes" class="section"><iframe src="notificaciones.php"></iframe></div>
        <div id="reporte"                  class="section"><iframe src="reportes.php"></iframe></div>
        <div id="perfilEstudiante"         class="section"><iframe src="perfil.php"></iframe></div>
    <?php endif; ?>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
const SELF = '<?= $self ?>';

function confirmarCierreSesion(event) {
    event.preventDefault();
    if (confirm('¿Estás seguro de que deseas cerrar sesión?')) {
        window.location.href = '/Demo-Sas/logout.php';
    }
}

document.addEventListener('DOMContentLoaded', function () {

    <?php if ($idTipoUsuario === 2 && $notificationCount > 0): ?>
    const notificationBtn = document.getElementById('notification-dropdown');
    if (notificationBtn) {
        notificationBtn.addEventListener('click', function (e) {
            e.preventDefault();
            activarSeccion('notificacionesEstudiantes', 'Mis Notificaciones');
        });
    }
    <?php endif; ?>

    document.getElementById('verPerfil')?.addEventListener('click', function (e) {
        e.preventDefault();
        const seccion = <?= $idTipoUsuario === 2 ? "'perfilEstudiante'" : "'perfilProfesor'" ?>;
        activarSeccion(seccion, 'Mi Perfil');
    });
});

function activarSeccion(id, titulo) {
    document.querySelectorAll('.section').forEach(s => s.classList.remove('active'));
    document.getElementById(id)?.classList.add('active');
    document.getElementById('section-title').textContent = titulo;
    document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
    document.querySelector(`.nav-link[data-section="${id}"]`)?.classList.add('active');
}
</script>
</body>
</html>