<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: ../View/login.php");
    exit;
}

$cedula_profesor = $_SESSION['cedula'];

require_once('../../config.php');

// Obtener cursos del profesor
$sql_cursos = "SELECT c.id_curso, c.nombre_curso 
               FROM cursos c
               JOIN profesor_curso pc ON c.id_curso = pc.id_curso
               WHERE pc.cedula_profesor = ?";
$stmt_cursos = $conn->prepare($sql_cursos);
$stmt_cursos->bind_param("s", $cedula_profesor);
$stmt_cursos->execute();
$resultado_cursos = $stmt_cursos->get_result();
$cursos = $resultado_cursos->fetch_all(MYSQLI_ASSOC);

// Estudiantes en riesgo con menos del 50% de asistencia
$sql_estudiantes_riesgo = "
    SELECT 
    u.nombre, 
    u.apellido, 
    SUM(CASE WHEN ae.asistencia = 'Presente' THEN 1 ELSE 0 END) AS asistencias, 
    COUNT(a.id_asistencia) AS total_clases, 
    (SUM(CASE WHEN ae.asistencia = 'Presente' THEN 1 ELSE 0 END) / COUNT(a.id_asistencia)) * 100 AS porcentaje_asistencia
FROM 
    usuarios u
JOIN 
    asistencia_detalle ae ON u.cedula = ae.cedula
JOIN 
    asistencia a ON ae.id_asistencia = a.id_asistencia
JOIN 
    cursos c ON a.id_curso = c.id_curso
JOIN 
    profesor_curso pc ON c.id_curso = pc.id_curso
WHERE 
    pc.cedula_profesor = ?
GROUP BY 
    u.cedula, u.nombre, u.apellido
HAVING 
    porcentaje_asistencia < 50
ORDER BY 
    porcentaje_asistencia ASC;";

$stmt_riesgo = $conn->prepare($sql_estudiantes_riesgo);
$stmt_riesgo->bind_param("s", $cedula_profesor);
$stmt_riesgo->execute();
$resultado_riesgo = $stmt_riesgo->get_result();
$estudiantes_riesgo = $resultado_riesgo->fetch_all(MYSQLI_ASSOC);

// Estudiantes con excelente asistencia mayor a 80%
$sql_estudiantes_excelente = "
    SELECT 
    u.nombre, 
    u.apellido, 
    SUM(CASE WHEN ae.asistencia = 'Presente' THEN 1 ELSE 0 END) AS asistencias, 
    COUNT(a.id_asistencia) AS total_clases, 
    (SUM(CASE WHEN ae.asistencia = 'Presente' THEN 1 ELSE 0 END) / COUNT(a.id_asistencia)) * 100 AS porcentaje_asistencia
FROM 
    usuarios u
JOIN 
    asistencia_detalle ae ON u.cedula = ae.cedula
JOIN 
    asistencia a ON ae.id_asistencia = a.id_asistencia
JOIN 
    cursos c ON a.id_curso = c.id_curso
JOIN 
    profesor_curso pc ON c.id_curso = pc.id_curso
WHERE 
    pc.cedula_profesor = ?
GROUP BY 
    u.cedula, u.nombre, u.apellido
HAVING 
    porcentaje_asistencia > 80
ORDER BY 
    porcentaje_asistencia DESC;";

$stmt_excelente = $conn->prepare($sql_estudiantes_excelente);
$stmt_excelente->bind_param("s", $cedula_profesor);
$stmt_excelente->execute();
$resultado_excelente = $stmt_excelente->get_result();
$estudiantes_excelente = $resultado_excelente->fetch_all(MYSQLI_ASSOC);

$total_estudiantes_riesgo = count($estudiantes_riesgo);


// Estudiantes Regulares (asistencia entre 60% y 80%)
$umbral_bajo = 50;
$umbral_alto = 80;

$sql_estudiantes_regulares = "
    SELECT 
    u.nombre, 
    u.apellido, 
    SUM(CASE WHEN ae.asistencia = 'Presente' THEN 1 ELSE 0 END) AS asistencias, 
    COUNT(a.id_asistencia) AS total_clases, 
    (SUM(CASE WHEN ae.asistencia = 'Presente' THEN 1 ELSE 0 END) / COUNT(a.id_asistencia)) * 100 AS porcentaje_asistencia
FROM 
    usuarios u
JOIN 
    asistencia_detalle ae ON u.cedula = ae.cedula
JOIN 
    asistencia a ON ae.id_asistencia = a.id_asistencia
JOIN 
    cursos c ON a.id_curso = c.id_curso
JOIN 
    profesor_curso pc ON c.id_curso = pc.id_curso
WHERE 
    pc.cedula_profesor = ? 
GROUP BY 
    u.cedula, u.nombre, u.apellido
HAVING 
    porcentaje_asistencia BETWEEN ? AND ?
ORDER BY 
    porcentaje_asistencia DESC;
";

$stmt_regulares = $conn->prepare($sql_estudiantes_regulares);
$stmt_regulares->bind_param("sii", $cedula_profesor, $umbral_bajo, $umbral_alto);
$stmt_regulares->execute();
$resultado_regulares = $stmt_regulares->get_result();
$estudiantes_regulares = $resultado_regulares->fetch_all(MYSQLI_ASSOC);


// Obtener total de clases dictadas
$sql_clases_dictadas = "
    SELECT COUNT(DISTINCT a.id_asistencia) AS total_clases
    FROM asistencia a
    JOIN profesor_curso pc ON a.id_curso = pc.id_curso
    WHERE pc.cedula_profesor = ?";
$stmt_clases = $conn->prepare($sql_clases_dictadas);
$stmt_clases->bind_param("s", $cedula_profesor);
$stmt_clases->execute();
$resultado_clases = $stmt_clases->get_result();
$total_clases = $resultado_clases->fetch_assoc()['total_clases'];

// Promedio de asistencia
$sql_asistencia_promedio = "
    SELECT 
        ROUND(AVG(porcentaje_asistencia), 2) AS promedio_asistencia
    FROM (
        SELECT 
            (SUM(CASE WHEN ad.asistencia = 'Presente' THEN 1 ELSE 0 END) / COUNT(*)) * 100 AS porcentaje_asistencia
        FROM 
            asistencia a
        JOIN 
            asistencia_detalle ad ON a.id_asistencia = ad.id_asistencia
        JOIN 
            profesor_curso pc ON a.id_curso = pc.id_curso
        WHERE 
            pc.cedula_profesor = ?
        GROUP BY 
            a.id_curso
    ) AS subquery
";
$stmt_promedio = $conn->prepare($sql_asistencia_promedio);
$stmt_promedio->bind_param("s", $cedula_profesor);
$stmt_promedio->execute();
$result_promedio = $stmt_promedio->get_result();
$data_promedio = $result_promedio->fetch_assoc();

$sql_distribucion_cursos = "
    SELECT 
        subquery.nombre_curso,
        ROUND(AVG(subquery.porcentaje_asistencia), 2) AS porcentaje_asistencia
    FROM (
        SELECT 
            c.nombre_curso,
            (SUM(CASE WHEN ad.asistencia = 'Presente' THEN 1 ELSE 0 END) / COUNT(*)) * 100 AS porcentaje_asistencia
        FROM 
            cursos c
        JOIN 
            asistencia a ON c.id_curso = a.id_curso
        JOIN 
            asistencia_detalle ad ON a.id_asistencia = ad.id_asistencia
        JOIN 
            profesor_curso pc ON c.id_curso = pc.id_curso
        WHERE 
            pc.cedula_profesor = ?
        GROUP BY 
            c.id_curso, c.nombre_curso
    ) AS subquery
    GROUP BY 
        subquery.nombre_curso
";
$stmt = $conn->prepare($sql_distribucion_cursos);
$stmt->bind_param("s", $cedula_profesor);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);

$total_porcentaje = 0;
$total_cursos = count($data);

if ($total_cursos > 0) {
    foreach ($data as $row) {
        $total_porcentaje += $row['porcentaje_asistencia'];
    }
    $promedio_asistencia = $total_porcentaje / $total_cursos;
} else {
    $promedio_asistencia = 0; 
}

$promedio_asistencia = number_format($promedio_asistencia, 2);
function formatAttendancePercentage($asistencia_presente, $total_clases) {
    if ($total_clases > 0) {
        $porcentaje = ($asistencia_presente / $total_clases) * 100;
        return number_format($porcentaje, 1);
    } else {
        return 0; 
    }
}
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
                    <canvas id="courseChart" style="height: 10px; width:40%;"></canvas> <!-- Ajusta la altura y el ancho del gráfico -->
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