<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: /Demo-Sas/View/login.php");
    exit();
}
$cedula_profesor = $_SESSION['cedula'];

require_once '../../app/modules/reports/reportRepository.php';
require_once '../../app/shared/helpers/statisticHelper.php';

$reportRepository = new ReportRepository();


// Obtener cursos del profesor
$cursos = $reportRepository->getCoursesByProfessor(
    $cedula_profesor
);

// Estudiantes en riesgo con menos del 50% de asistencia
$estudiantes_riesgo = $reportRepository
    ->getStudentsAtRisk($cedula_profesor);
$total_estudiantes_riesgo = count(
    $estudiantes_riesgo
);

// Estudiantes con excelente asistencia mayor a 80%
$estudiantes_excelente = $reportRepository
    ->getExcellentStudents($cedula_profesor);
$total_estudiantes_excelente = count(
    $estudiantes_excelente
);

// Estudiantes Regulares (asistencia entre 60% y 80%)
$umbral_bajo = 50;
$umbral_alto = 80;

$estudiantes_regulares =
    $reportRepository->getRegularStudents(
        $cedula_profesor,
        $umbral_bajo,
        $umbral_alto
    );

// Obtener total de clases dictadas
$total_clases = $reportRepository
    ->getTotalClasses($cedula_profesor);
    
// Promedio de asistencia
$data_promedio = $reportRepository
    ->getAverageAttendance($cedula_profesor);


$data = $reportRepository
    ->getCourseDistribution($cedula_profesor);


$promedio_asistencia =
    StatisticsHelper::calculateAverageAttendance(
        $data
    );
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
    .card-body {
        padding: 10px;
    }

    .card {
        margin-bottom: 20px;
    }

    @media (max-width: 767px) {
        #courseChart {
            height: 100px !important;
            width: 100% !important;
        }

        .col-md-4 {
            flex: 1 1 100%;
            max-width: 100%;
        }

        .card-header {
            font-size: 14px;
        }
    }

    @media (min-width: 768px) {
        .col-md-4 {
            flex: 1 1 32%;
            max-width: 32%;
        }

        #courseChart {
            height: 250px;
            width: 100%;
        }
    }
    </style>
</head>

<body>
    <main class="main-content container-fluid">
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-percent me-2"></i>Asistencia Total</h5>
                        <h2 class="mb-0"><?php echo number_format($promedio_asistencia, 2); ?>%</h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-chalkboard-teacher me-2"></i>Clases Dictadas</h5>
                        <h2 class="mb-0"><?php echo $total_clases; ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h5 class="card-title"><i class="fas fa-exclamation-triangle me-2"></i>Desempeño Bajo</h5>
                        <h2 class="mb-0"><?php echo $total_estudiantes_riesgo; ?></h2>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">Distribución de Asistencia por CursoS</div>
                    <div class="card-body p-0">
                        <canvas id="courseChart" style="height: 10px; width:40%;"></canvas>
                        <!-- Ajusta la altura y el ancho del gráfico -->
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">Estudiantes con Excelente Asistencia</div>
                    <div class="card-body">
                        <ul id="excellentStudentsList" class="list-group">
                            <?php foreach ($estudiantes_excelente as $estudiante): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?>
                                <span class="badge bg-success">
                                    <?php 
                                        echo number_format($estudiante['porcentaje_asistencia'], 2) . '%'; 
                                    ?>
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
                        <?php foreach ($estudiantes_regulares as $estudiante): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?>
                            <span class="badge bg-warning text-dark">
                                <?php 
                                    echo number_format($estudiante['porcentaje_asistencia'], 2) . '%'; 
                                ?>
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
                            <?php foreach ($estudiantes_riesgo as $estudiante): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <?php echo htmlspecialchars($estudiante['nombre'] . ' ' . $estudiante['apellido']); ?>
                                <span class="badge bg-danger">
                                    <?php 
                                        echo number_format($estudiante['porcentaje_asistencia'], 2) . '%'; 
                                    ?>
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
    const courseData = <?php echo json_encode($data); ?>;
    document.addEventListener('DOMContentLoaded', function() {
        const courseCtx = document.getElementById('courseChart').getContext('2d');
        new Chart(courseCtx, {
            type: 'bar',
            data: {
                labels: courseData.map(course => course.nombre_curso),
                datasets: [{
                    label: '% Asistencia',
                    data: courseData.map(course => course.porcentaje_asistencia),
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
                        title: {
                            display: true
                        },
                        ticks: {
                            callback: function(value) {
                                return value + '%';
                            }
                        }
                    }
                },
                plugins: {
                    datalabels: {
                        formatter: (value, context) => {
                            return value + '%';
                        },
                        color: '#fff',
                        font: {
                            weight: 'bold',
                            size: 14
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(tooltipItem) {
                                return tooltipItem.raw + '%';
                            }
                        }
                    }
                }
            }
        });
    });
    </script>

</body>

</html>