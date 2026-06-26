<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: /Demo-Sas/View/login.php");
    exit();
}

require_once __DIR__ . '/../../app/modules/students/studentsRepository.php';

$self   = basename(__FILE__);
$repo   = new StudentsRepository();
$cedula = $_SESSION['cedula'];

$usuario = $repo->getNombreByEstudiante($cedula);
if ($usuario === null) {
    header("Location: /Demo-Sas/View/login.php");
    exit();
}

$nombreCompleto      = htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido'], ENT_QUOTES);
$totalCursos         = $repo->getTotalCursosByEstudiante($cedula);
$asistenciasPresente = $repo->getAsistenciasPresente($cedula);
$resumenCursos       = $repo->getResumenCursosByEstudiante($cedula);

// Calcular progreso general desde el array — sin doble ejecución de query
$totalAsistencias = 0;
$totalClases      = 0;
foreach ($resumenCursos as $fila) {
    $totalAsistencias += (int) $fila['asistencias'];
    $totalClases      += (int) $fila['total_clases'];
}
$progresoGeneral = $totalClases > 0 ? ($totalAsistencias / $totalClases) * 100 : 0;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Estudiante - Sistema de Asistencia Virtual</title>
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
                    <i class="fas fa-user-graduate me-2"></i>
                    Bienvenido <?= $nombreCompleto ?>, al Panel de Estudiantes
                </h2>
                <p class="lead mb-0">Universidad Tecnológica de Panamá</p>
                <p class="mb-3">Controla tu asistencia y visualiza tu progreso académico</p>
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
        <div class="col-md-4">
            <div class="stat-card">
                <div class="card-body">
                    <div class="icon"><i class="fas fa-chalkboard"></i></div>
                    <h6 class="card-title text-muted">Total de Cursos</h6>
                    <h3 class="card-text"><?= $totalCursos ?></h3>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-primary" style="width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="card-body">
                    <div class="icon"><i class="fas fa-check-circle"></i></div>
                    <h6 class="card-title text-muted">Asistencias Registradas</h6>
                    <h3 class="card-text"><?= $asistenciasPresente ?></h3>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-success" style="width: <?= number_format($progresoGeneral, 2) ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card">
                <div class="card-body">
                    <div class="icon"><i class="fas fa-tasks"></i></div>
                    <h6 class="card-title text-muted">Progreso General</h6>
                    <h3 class="card-text"><?= number_format($progresoGeneral, 2) ?>%</h3>
                    <div class="progress mt-2">
                        <div class="progress-bar bg-warning" style="width: <?= number_format($progresoGeneral, 2) ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla resumen de cursos -->
    <div class="card mb-4">
        <div class="card-header bg-white">
            <h5 class="mb-0">Resumen de tus cursos</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Curso</th>
                            <th>Asistencias Registradas</th>
                            <th>Cantidad de Asistencia</th>
                            <th>Porcentaje de Asistencias</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $totAsistencias = 0;
                        $totCantidad    = 0;
                        $totPorcentaje  = 0;
                        $numCursos      = count($resumenCursos);
                        ?>
                        <?php foreach ($resumenCursos as $row): ?>
                            <?php
                                $totAsistencias += (int)   $row['asistencias'];
                                $totCantidad    += (int)   $row['cantidad_asistencias'];
                                $totPorcentaje  += (float) $row['porcentaje_asistencia'];
                            ?>
                            <tr>
                                <td><?= htmlspecialchars($row['nombre_curso'], ENT_QUOTES) ?></td>
                                <td><?= (int) $row['asistencias'] ?></td>
                                <td><?= (int) $row['cantidad_asistencias'] ?></td>
                                <td><?= $row['porcentaje_asistencia'] !== null
                                        ? number_format($row['porcentaje_asistencia'], 2) . '%'
                                        : '0.00%' ?></td>
                            </tr>
                        <?php endforeach; ?>
                        <tr>
                            <td><strong>Total</strong></td>
                            <td><strong><?= $totAsistencias ?></strong></td>
                            <td><strong><?= $totCantidad ?></strong></td>
                            <td><strong><?= $numCursos > 0
                                ? number_format($totPorcentaje / $numCursos, 2) . '%'
                                : '0.00%' ?></strong></td>
                        </tr>
                    </tbody>
                </table>
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