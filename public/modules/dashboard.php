<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: /Demo-Sas/View/login.php");
    exit();
}
$cedula_profesor = $_SESSION['cedula'];

require_once __DIR__ . '/../../app/modules/dashboard/dashboardRepository.php';

$dashboardRepository = new DashboardRepository();

$totalEstudiantes      = $dashboardRepository->getTotalStudents($cedula_profesor);
$totalCursosImpartidos = $dashboardRepository->getTotalCourses($cedula_profesor);
$totalClasesDictadas   = $dashboardRepository->getTotalClasses($cedula_profesor);
$asistencia_promedio   = $dashboardRepository->getAverageAttendance($cedula_profesor);
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
                    Bienvenido <?= htmlspecialchars($_SESSION['nombre'] . ' ' . $_SESSION['apellido']) ?>,
                    al Panel de profesores
                </h2>
                <p class="lead mb-0">Universidad Tecnológica de Panamá</p>
                <p class="mb-3">Sistema integral para el seguimiento y control de asistencia docente</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="text-white">
                    <p class="mb-1"><i class="fas fa-calendar me-2"></i>Período Actual</p>
                    <h4>I Semestre 2026</h4>
                    <div id="hora-clima" style="font-size: 0.8em; color: #ffffff; margin-top: 10px;">
                        <p id="hora" style="font-size: 1.2em; font-weight: bold;"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Acciones Rápidas -->
    <div class="quick-actions mb-4">
        <h5 class="mb-3">Acciones Rápidas</h5>
        <div class="row g-3">
            <div class="col-md-3">
                <div class="action-button">
                    <i class="fas fa-qrcode mb-2"></i>
                    <p class="mb-0">Registrar Asistencia</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="action-button">
                    <i class="fas fa-chalkboard-teacher mb-2"></i>
                    <p class="mb-0">Gestionar estudiantes</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="action-button">
                    <i class="fas fa-exclamation-circle mb-2"></i>
                    <p class="mb-0">Notificar</p>
                </div>
            </div>
            <div class="col-md-3">
                <div class="action-button">
                    <i class="fas fa-list-alt mb-2"></i>
                    <p class="mb-0">Consultar Asistencia</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
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
                    <h6 class="card-title text-muted">Asistencia Promedio</h6>
                    <h3 class="card-text"><?= $asistencia_promedio ?>%</h3>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-success" style="width: <?= $asistencia_promedio ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="card-body">
                    <div class="icon"><i class="fas fa-chalkboard-teacher"></i></div>
                    <h6 class="card-title text-muted">Clases Dictadas</h6>
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
                    <h3 class="card-text"><?= $totalCursosImpartidos ?></h3>
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
    const API_KEY = "d94d5df682a9c49badb79ba1e1bc250c";
    const LAT     = "8.9833";
    const LON     = "-79.5167";

    async function obtenerHora() {
        try {
            const url      = `https://api.openweathermap.org/data/2.5/weather?lat=${LAT}&lon=${LON}&appid=${API_KEY}`;
            const response = await fetch(url);
            const data     = await response.json();

            if (data?.timezone !== undefined) {
                const utcNow   = new Date();
                const utcTime  = utcNow.getTime() + utcNow.getTimezoneOffset() * 60_000;
                const localTime = new Date(utcTime + data.timezone * 1_000);

                document.getElementById('hora').textContent = 'Hora local: ' +
                    localTime.toLocaleTimeString('es-ES', {
                        hour:   '2-digit',
                        minute: '2-digit',
                        second: '2-digit',
                        hour12: true,
                    });
            } else {
                document.getElementById('hora').textContent = 'No se pudo obtener la hora.';
            }
        } catch {
            document.getElementById('hora').textContent = 'Error al cargar la hora.';
        }
    }

    setInterval(obtenerHora, 1_000);
</script>
</body>
</html>