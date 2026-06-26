<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: /Demo-Sas/View/login.php");
    exit();
}

require_once __DIR__ . '/../../app/modules/reports/reportadmRepository.php';

$self           = basename(__FILE__);
$repo           = new ReportAdminRepository();
$cedulaProfesor = $_SESSION['cedula'];

$cursos               = $repo->getAllCourses();
$estudiantesRiesgo    = $repo->getStudentsAtRisk($cedulaProfesor);
$estudiantesExcelente = $repo->getStudentsExcellent();
$estudiantesRegulares = $repo->getStudentsRegular(50, 80);
$totalClases          = $repo->getTotalClases();
$distribucionCursos   = $repo->getAttendanceDistributionByCourse();
$promedioAsistencia   = $repo->getAverageAttendance($distribucionCursos);
$totalEnRiesgo        = count($estudiantesRiesgo);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Reportes de Asistencia</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
    <style>
        .card-body { padding: 10px; }
        .card { margin-bottom: 20px; }

        @media (max-width: 767px) {
            #courseChart { height: 100px !important; width: 100% !important; }
            .col-md-4 { flex: 1 1 100%; max-width: 100%; }
            .card-header { font-size: 14px; }
        }
        @media (min-width: 768px) {
            .col-md-4 { flex: 1 1 32%; max-width: 32%; }
            #courseChart { height: 250px; width: 100%; }
        }
    </style>
</head>
<body>
<main class="main-content container-fluid">

    <!-- Tarjetas de resumen -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-percent me-2"></i>Asistencia Total</h5>
                    <h2 class="mb-0"><?= number_format($promedioAsistencia, 2) ?>%</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-chalkboard-teacher me-2"></i>Clases Dictadas</h5>
                    <h2 class="mb-0"><?= $totalClases ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title"><i class="fas fa-exclamation-triangle me-2"></i>Desempeño Bajo</h5>
                    <h2 class="mb-0"><?= $totalEnRiesgo ?></h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Gráfico -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">Distribución de Asistencia por Cursos</div>
                <div class="card-body p-0">
                    <canvas id="courseChart" style="height: 10px; width: 40%;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Listas de estudiantes -->
    <div class="row mt-4">

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Estudiantes con Excelente Asistencia</div>
                <div class="card-body">
                    <ul id="excellentStudentsList" class="list-group">
                        <?php foreach ($estudiantesExcelente as $estudiante): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido'], ENT_QUOTES) ?>
                                <span class="badge bg-success">
                                    <?= number_format($estudiante['porcentaje_asistencia'], 2) ?>%
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Estudiantes Regulares</div>
                <ul id="regularStudentsList" class="list-group">
                    <?php foreach ($estudiantesRegulares as $estudiante): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?= htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido'], ENT_QUOTES) ?>
                            <span class="badge bg-warning text-dark">
                                <?= number_format($estudiante['porcentaje_asistencia'], 2) ?>%
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">Estudiantes en Riesgo Académico</div>
                <div class="card-body">
                    <ul id="riskStudentsList" class="list-group">
                        <?php foreach ($estudiantesRiesgo as $estudiante): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?= htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido'], ENT_QUOTES) ?>
                                <span class="badge bg-danger">
                                    <?= number_format($estudiante['porcentaje_asistencia'], 2) ?>%
                                </span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

    </div>
</main>

<script>
    const SELF = '<?= $self ?>';
    const courseData = <?= json_encode($distribucionCursos) ?>;

    document.addEventListener('DOMContentLoaded', function () {
        const courseCtx = document.getElementById('courseChart').getContext('2d');
        new Chart(courseCtx, {
            type: 'bar',
            data: {
                labels: courseData.map(c => c.nombre_curso),
                datasets: [{
                    label: '% Asistencia',
                    data: courseData.map(c => c.porcentaje_asistencia),
                    backgroundColor: 'rgba(75, 192, 192, 0.6)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            callback: value => value + '%'
                        }
                    }
                },
                plugins: {
                    datalabels: {
                        formatter: value => value + '%',
                        color: '#fff',
                        font: { weight: 'bold', size: 14 }
                    },
                    tooltip: {
                        callbacks: {
                            label: item => item.raw + '%'
                        }
                    }
                }
            }
        });
    });
</script>
</body>
</html>