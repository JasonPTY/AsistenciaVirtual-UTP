<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    die("No hay sesión activa. Por favor, inicie sesión.");
}

require_once('../../config.php');

// Obtener el total de estudiantes
$sqlCountEstudiantes = "SELECT COUNT(DISTINCT e.cedula) as total 
                        FROM estudiantes e 
                        JOIN usuarios u ON e.cedula = u.cedula
                        LEFT JOIN estudiantes_cursos ec ON e.cedula = ec.cedula
                        LEFT JOIN cursos c ON ec.id_curso = c.id_curso";
$stmtCountEstudiantes = $conn->prepare($sqlCountEstudiantes);
$stmtCountEstudiantes->execute();
$resultCountEstudiantes = $stmtCountEstudiantes->get_result();
$totalEstudiantes = $resultCountEstudiantes->fetch_assoc()['total'];

// Obtener el total de cursos
$sqlCountCursos = "SELECT COUNT(DISTINCT c.id_curso) as total 
                   FROM cursos c";
$stmtCountCursos = $conn->prepare($sqlCountCursos);
$stmtCountCursos->execute();
$resultCountCursos = $stmtCountCursos->get_result();
$totalCursosImpartidos = $resultCountCursos->fetch_assoc()['total'];

// Obtener el total de clases dictadas
$sqlClasesDictadas = "
    SELECT COUNT(DISTINCT ad.id_asistencia) AS total_clases
    FROM asistencia_detalle ad
    JOIN asistencia a ON ad.id_asistencia = a.id_asistencia
    JOIN cursos c ON a.id_curso = c.id_curso";
$stmtClasesDictadas = $conn->prepare($sqlClasesDictadas);
$stmtClasesDictadas->execute();
$resultClasesDictadas = $stmtClasesDictadas->get_result();
$totalClasesDictadas = $resultClasesDictadas->fetch_assoc()['total_clases'];

// Obtener el promedio de asistencia general
$sql_asistencia_promedio = "
    SELECT 
        ROUND(AVG(porcentaje_asistencia), 2) AS promedio_asistencia
    FROM (
        SELECT 
            (SUM(CASE WHEN ad.asistencia = 'Presente' THEN 1 ELSE 0 END) / COUNT(*) ) * 100 AS porcentaje_asistencia
        FROM 
            asistencia a
        JOIN 
            asistencia_detalle ad ON a.id_asistencia = ad.id_asistencia
        GROUP BY 
            a.id_curso
    ) AS subquery
";
$stmt_promedio = $conn->prepare($sql_asistencia_promedio);
$stmt_promedio->execute();
$result_promedio = $stmt_promedio->get_result();
$data_promedio = $result_promedio->fetch_assoc();
$asistencia_promedio = $data_promedio['promedio_asistencia'] ?? 0;

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Asistencia Virtual - UTP</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/../AsistenciaVirtual/public/assets/css/dashboard.css">
</head>
<body>
    <main role="main" class="main-content">
        <!-- Sección de Bienvenida -->
        <div class="welcome-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h2><i class="fas fa-chalkboard-teacher me-2"></i>
                    </i>Bienvenido < <?php echo $_SESSION['nombre'] . " " . $_SESSION['apellido']; ?> >, al Panel de Administradores</h2>
                    <p class="lead mb-0">Universidad Tecnológica de Panamá</p>
                    <p class="mb-3">Sistema integral para el seguimiento y control de asistencia docente</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="text-white">
                        <p class="mb-1"><i class="fas fa-calendar me-2"></i>Período Actual</p>
                        <h4>II Semestre 2024</h4>

                        <!-- Api para la hora -->
                        <div id="hora-clima" style="font-size: 0.8em; color: #ffffff; margin-top: 10px;">
                            <p id="hora" style="font-size: 1.2em; font-weight: bold;"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
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
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                        <h6 class="card-title text-muted">Asistencia General</h6>
                        <h3 class="card-text"><?php echo round($asistencia_promedio, 2); ?>%</h3>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-success" style="width: <?php echo round($asistencia_promedio, 2); ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="icon">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <h6 class="card-title text-muted">Clases Dictadas en total</h6>
                        <h3 class="card-text"><?php echo $totalClasesDictadas; ?></h3>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-warning" style="width: 60%"></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
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
                    <p class="mb-0">&copy; 2024 Universidad Tecnológica de Panamá</p>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
    <script>
        function obtenerHora() {
            const now = new Date();
            const hora = now.getHours().toString().padStart(2, '0');
            const minutos = now.getMinutes().toString().padStart(2, '0');
            const segundos = now.getSeconds().toString().padStart(2, '0');
            const horaActual = `${hora}:${minutos}:${segundos}`;
            
            document.getElementById("hora").innerHTML = horaActual;
        }
        setInterval(obtenerHora, 1000);
    </script>
</body>
</html>