<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: /Demo-Sas/View/login.php");
    exit();
}

require_once __DIR__ . '/../../app/modules/dashboard/dashboardRepository.php';

$self  = basename(__FILE__);
$repo  = new DashboardRepository();

$totalEstudiantes    = $repo->getTotalEstudiantes();
$totalCursos         = $repo->getTotalCursos();
$totalClasesDictadas = $repo->getTotalClasesDictadas();
$promedioAsistencia  = $repo->getPromedioAsistenciaGeneral();

$nombreCompleto = htmlspecialchars(
    ($_SESSION['nombre'] ?? '') . ' ' . ($_SESSION['apellido'] ?? ''),
    ENT_QUOTES
);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Asistencia Virtual - UTP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/../Demo-Sas/public/assets/css/dashboard.css">
</head>
<body>
<main role="main" class="main-content">

    <!-- Bienvenida -->
    <div class="welcome-section">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2>
                    <i class="fas fa-chalkboard-teacher me-2"></i>
                    Bienvenido &lt;<?= $nombreCompleto ?>&gt;, al Panel de Administradores
                </h2>
                <p class="lead mb-0">Universidad Tecnológica de Panamá</p>
                <p class="mb-3">Sistema integral para el seguimiento y control de asistencia docente</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="text-white">
                    <p class="mb-1"><i class="fas fa-calendar me-2"></i>Período Actual</p>
                    <h4>II Semestre 2024</h4>
                    <div id="hora-clima" style="font-size: 0.8em; color: #ffffff; margin-top: 10px;">
                        <p id="hora" style="font-size: 1.2em; font-weight: bold;"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjetas de estadísticas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="card-body">
                    <div class="icon"><i class="fas fa-users"></i></div>
                    <h6 class="card-title text-muted">Total Estudiantes</h6>
                    <h3 class="card-text"><?= $totalEstudiantes ?></h3>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-primary" style="width: 75%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="card-body">
                    <div class="icon"><i class="fas fa-chart-line"></i></div>
                    <h6 class="card-title text-muted">Asistencia General</h6>
                    <h3 class="card-text"><?= number_format($promedioAsistencia, 2) ?>%</h3>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-success" style="width: <?= number_format($promedioAsistencia, 2) ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="card-body">
                    <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h6 class="card-title text-muted">Clases Dictadas en total</h6>
                    <h3 class="card-text"><?= $totalClasesDictadas ?></h3>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-warning" style="width: 60%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="card-body">
                    <div class="icon"><i class="fas fa-clock"></i></div>
                    <h6 class="card-title text-muted">Cursos Totales</h6>
                    <h3 class="card-text"><?= $totalCursos ?></h3>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-danger" style="width: 40%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="row align-items-center">
            <div class="col-md-6">
                <p class="mb-0">&copy; 2026 Universidad Tecnológica de Panamá</p>
                <small class="text-muted">Sistema de Asistencia Virtual v2.0</small>
            </div>
            <div class="col-md-6 text-end">
                <a href="#" class="text-muted me-3">Ayuda</a>
                <a href="#" class="text-muted me-3">Términos</a>
                <a href="#" class="text-muted">Contacto</a>
            </div>
        </div>
    </footer>

</main>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script>
const SELF = '<?= $self ?>';

function obtenerHora() {
    const now = new Date();
    const pad = n => String(n).padStart(2, '0');
    document.getElementById('hora').textContent =
        `${pad(now.getHours())}:${pad(now.getMinutes())}:${pad(now.getSeconds())}`;
}
setInterval(obtenerHora, 1000);
obtenerHora();
</script>
</body>
</html>