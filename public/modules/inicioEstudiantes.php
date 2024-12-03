<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    die("No hay sesión activa. Por favor, inicie sesión.");
}$cedula_estudiante = $_SESSION['cedula'];

require_once('../../config.php');


// Total de cursos en los que está inscrito el estudiante logeuado
$sqlCursos = "
    SELECT COUNT(*) as total_cursos
    FROM estudiantes_cursos ec
    JOIN cursos c ON ec.id_curso = c.id_curso
    WHERE ec.cedula = ?";
$stmtCursos = $conn->prepare($sqlCursos);
$stmtCursos->bind_param("s", $cedula_estudiante);
$stmtCursos->execute();
$resultCursos = $stmtCursos->get_result();
$totalCursos = $resultCursos->fetch_assoc()['total_cursos'];


$sqlAsistencias = "
    SELECT COUNT(*) as total_asistencias
    FROM asistencia_detalle ae
    WHERE ae.cedula = ?";
$stmtAsistencias = $conn->prepare($sqlAsistencias);
$stmtAsistencias->bind_param("s", $cedula_estudiante);
$stmtAsistencias->execute();
$resultAsistencias = $stmtAsistencias->get_result();
$totalAsistencias = $resultAsistencias->fetch_assoc()['total_asistencias'];


$sqlClases = "
    SELECT 
        c.nombre_curso, 
        COUNT(CASE WHEN ae.asistencia = 'Presente' THEN 1 END) AS asistencias, 
        COUNT(DISTINCT a.id_asistencia) AS total_clases, 
        (COUNT(CASE WHEN ae.asistencia = 'Presente' THEN 1 END) / NULLIF(COUNT(DISTINCT a.id_asistencia), 0)) * 100 AS porcentaje_asistencia,
        COUNT(a.id_asistencia) AS cantidad_asistencias
    FROM 
        cursos c
    JOIN 
        estudiantes_cursos ec ON c.id_curso = ec.id_curso
    LEFT JOIN 
        asistencia a ON c.id_curso = a.id_curso
    LEFT JOIN 
        asistencia_detalle ae ON a.id_asistencia = ae.id_asistencia AND ae.cedula = ?
    WHERE 
        ec.cedula = ?
    GROUP BY 
        c.id_curso, c.nombre_curso
";


$stmtClases = $conn->prepare($sqlClases);
$stmtClases->bind_param("ss", $cedula_estudiante, $cedula_estudiante);
$stmtClases->execute();
$resultClases = $stmtClases->get_result();

$total_asistencias = 0;
$total_clases = 0;
while ($fila = $resultClases->fetch_assoc()) {
    $total_asistencias += $fila['asistencias'];
    $total_clases += $fila['total_clases'];
}

$stmtClases->execute();
$resultClases = $stmtClases->get_result();

$progreso_general = $total_clases > 0 ? ($total_asistencias / $total_clases) * 100 : 0;

$sqlTotalAsistencias = "SELECT COUNT(*) AS total_asistencias FROM asistencia";
$resultTotalAsistencias = $conn->query($sqlTotalAsistencias);

$rowTotalAsistencias = $resultTotalAsistencias->fetch_assoc();
$totalAsistencias = $rowTotalAsistencias['total_asistencias'];

$sqlAsistenciasRegistradas = "
    SELECT COUNT(*) AS asistencias_registradas
    FROM asistencia_detalle ae
    WHERE ae.cedula = ? AND ae.asistencia = 'Presente'";
$stmtAsistenciasRegistradas = $conn->prepare($sqlAsistenciasRegistradas);
$stmtAsistenciasRegistradas->bind_param("s", $cedula_estudiante);
$stmtAsistenciasRegistradas->execute();
$resultAsistenciasRegistradas = $stmtAsistenciasRegistradas->get_result();
$asistenciasRegistradas = $resultAsistenciasRegistradas->fetch_assoc()['asistencias_registradas'];


?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Estudiante - Sistema de Asistencia Virtual</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/../AsistenciaVirtual/public/assets/css/dashboard.css">
</head>
<body>
    <main role="main" class="main-content">
        <div class="welcome-section">
            <div class="row align-items-center">
                <div class="col-md-8">
                <h2><i class="fas fa-user-graduate me-2"></i>Bienvenido < <?php echo $_SESSION['nombre'] . " " . $_SESSION['apellido']; ?> >, al Panel de Estudiantes</h2>
            <p class="lead mb-0">Universidad Tecnológica de Panamá</p>
            <p class="mb-3">Controla tu asistencia y visualiza tu progreso académico</p>
                </div>
                <div class="col-md-4 text-end">
                    <div class="text-white">
                        <p class="mb-1"><i class="fas fa-calendar me-2"></i>Período Actual</p>
                        <h4>II Semestre 2024</h4>
                    </div>
                    <!-- Api para la hora -->
                <div id="hora-clima" style="font-size: 0.8em; color: #ffffff; margin-top: 10px;">
                    <p id="hora" style="font-size: 1.2em; font-weight: bold;"></p>
                </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="icon">
                            <i class="fas fa-chalkboard"></i>
                        </div>
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
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h6 class="card-title text-muted">Asistencias Registradas</h6>
                        <h3 class="card-text"><?= $asistenciasRegistradas ?></h3>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-success" style="width: <?= $progreso_general ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-card">
                    <div class="card-body">
                        <div class="icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                        <h6 class="card-title text-muted">Progreso General</h6>
                        <h3 class="card-text"><?= round($progreso_general, 2) ?>%</h3>
                        <div class="progress mt-2">
                            <div class="progress-bar bg-warning" style="width: <?= $progreso_general ?>%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                            $total_asistencias_registradas = 0;
                            $total_cantidad_asistencias = 0;
                            $total_porcentaje_asistencias = 0;
                            $num_cursos = 0;

                            while ($row = $resultClases->fetch_assoc()): 
                                $total_asistencias_registradas += $row['asistencias'];
                                $total_cantidad_asistencias += $row['cantidad_asistencias'];
                                $total_porcentaje_asistencias += $row['porcentaje_asistencia'];
                                $num_cursos++;
                            ?>
                                <tr>
                                    <td><?= $row['nombre_curso'] ?></td>
                                    <td><?= $row['asistencias'] ?></td>
                                    <td><?= $row['cantidad_asistencias'] ?></td>
                                    <td><?= $row['porcentaje_asistencia'] !== null 
                                        ? number_format($row['porcentaje_asistencia'], 2) . '%' 
                                        : '0.00%' ?></td>
                                </tr>
                            <?php endwhile; ?>

                            <tr>
                                <td><strong>Total</strong></td>
                                <td><strong><?= $total_asistencias_registradas ?></strong></td>
                                <td><strong><?= $total_cantidad_asistencias ?></strong></td>
                                <td><strong>
                                    <?= $num_cursos > 0 
                                        ? number_format($total_porcentaje_asistencias / $num_cursos, 2) . '%' 
                                        : '0.00%' ?>
                                </strong></td>
                            </tr>
                        </tbody>



                    </table>
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
