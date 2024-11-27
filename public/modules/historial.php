<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    die("No hay sesión activa. Por favor, inicie sesión.");
}
$cedula_profesor = $_SESSION['cedula'];

require_once('../../config.php');

function getCursos($conn, $cedula_profesor) {
    $sql = "
        SELECT c.id_curso, c.nombre_curso
        FROM profesor_curso pc
        JOIN cursos c ON pc.id_curso = c.id_curso
        WHERE pc.cedula_profesor = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cedula_profesor);
    $stmt->execute();
    
    return $stmt->get_result(); 
}


function getHistorialClases($conn, $cedula_profesor, $curso = null, $fechaInicio = null, $fechaFin = null) {
    $sql = "SELECT a.*, c.nombre_curso,
            (SELECT COUNT(*) FROM asistencia_detalle ad 
             WHERE ad.id_asistencia = a.id_asistencia) as total_estudiantes,
            (SELECT COUNT(*) FROM asistencia_detalle ad 
             WHERE ad.id_asistencia = a.id_asistencia AND ad.asistencia = 'Presente') as total_presentes
            FROM asistencia a
            INNER JOIN cursos c ON a.id_curso = c.id_curso
            INNER JOIN profesor_curso pc ON pc.id_curso = c.id_curso
            WHERE pc.cedula_profesor = ?";  
    
    if ($curso) {
        $sql .= " AND a.id_curso = " . intval($curso);
    }
    if ($fechaInicio) {
        $sql .= " AND a.fecha >= '" . $conn->real_escape_string($fechaInicio) . "'";
    }
    if ($fechaFin) {
        $sql .= " AND a.fecha <= '" . $conn->real_escape_string($fechaFin) . "'";
    }
    
    $sql .= " ORDER BY a.fecha DESC, a.hora DESC";
    
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cedula_profesor); 
    $stmt->execute();
    
    return $stmt->get_result();
}




function getDetallesAsistencia($conn, $idAsistencia) {
    $sql = "SELECT ad.*, a.fecha, a.hora 
            FROM asistencia_detalle ad
            INNER JOIN asistencia a ON ad.id_asistencia = a.id_asistencia
            WHERE ad.id_asistencia = " . intval($idAsistencia);
    return $conn->query($sql);
}

if (isset($_GET['exportar']) && isset($_GET['id_asistencia'])) {
    $id_asistencia = intval($_GET['id_asistencia']);
    
    $sql = "SELECT a.*, c.nombre_curso
            FROM asistencia a
            INNER JOIN cursos c ON a.id_curso = c.id_curso
            WHERE a.id_asistencia = $id_asistencia";
    $clase = $conn->query($sql)->fetch_assoc();
    
    $detalles = getDetallesAsistencia($conn, $id_asistencia);
    
    if ($_GET['exportar'] === 'excel') {
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename="asistencia_' . $id_asistencia . '.xls"');
        
        echo "Curso\tFecha\tHora\n";
        echo $clase['nombre_curso'] . "\t" . $clase['fecha'] . "\t" . $clase['hora'] . "\n\n";
        echo "Cédula\tEstado\tHora de Registro\n";
        
        while ($estudiante = $detalles->fetch_assoc()) {
            echo $estudiante['cedula'] . "\t" . 
                 $estudiante['asistencia'] . "\t" . 
                 ($estudiante['asistencia'] == 'Presente' ? $estudiante['hora'] : '-') . "\n";
        }
        exit;
    }
}


if (isset($_GET['get_detalles']) && isset($_GET['id_asistencia'])) {
    $detalles = getDetallesAsistencia($conn, $_GET['id_asistencia']);
    echo '<div class="table-responsive mt-3">';
    echo '<table class="table">';
    echo '<thead><tr><th>Cédula</th><th>Estado</th><th>Hora de Registro</th></tr></thead>';
    echo '<tbody>';
    while ($estudiante = $detalles->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($estudiante['cedula']) . '</td>';
        echo '<td><span class="attendance-status ' . 
             ($estudiante['asistencia'] == 'Presente' ? 'status-present' : 'status-absent') . 
             '">' . $estudiante['asistencia'] . '</span></td>';
        echo '<td>' . ($estudiante['asistencia'] == 'Presente' ? date('h:i A', strtotime($estudiante['hora'])) : '-') . '</td>';
        echo '</tr>';
    }
    echo '</tbody></table></div>';
    exit;
}

$filtro_curso = isset($_GET['curso']) ? $_GET['curso'] : null;
$filtro_fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$filtro_fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

$clases = getHistorialClases($conn, $cedula_profesor, $filtro_curso, $filtro_fecha_inicio, $filtro_fecha_fin);
$cursos = getCursos($conn,$cedula_profesor);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Clases - Sistema de Gestión de Asistencia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="stylesheet" href="/../AsistenciaVirtual/public/assets/css/historial.css">
    <style>
        .class-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
        .attendance-details {
            display: none;
            margin-top: 20px;
            border-top: 1px solid #eee;
            padding-top: 20px;
        }
        .attendance-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
        }
        .status-present {
            background-color: #d4edda;
            color: #155724;
        }
        .status-absent {
            background-color: #f8d7da;
            color: #721c24;
        }
        .class-tag {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.9em;
            margin-right: 10px;
        }
        .filters-card {
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }
    </style>
</head>
<body class="bg-light">
    <main class="container py-4">
        <!-- Filtros -->
        <div class="filters-card">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Curso</label>
                    <select name="curso" class="form-select">
                        <option value="">Todos los cursos</option>
                        <?php while($curso = $cursos->fetch_assoc()): ?>
                            <option value="<?php echo $curso['id_curso']; ?>"
                                <?php echo ($filtro_curso == $curso['id_curso']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($curso['nombre_curso']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" class="form-control" 
                           value="<?php echo $filtro_fecha_inicio; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" name="fecha_fin" class="form-control"
                           value="<?php echo $filtro_fecha_fin; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-2"></i>Aplicar Filtros
                    </button>
                </div>
            </form>
        </div>

        <?php if ($clases->num_rows == 0): ?>
        <div class="alert alert-info">
            No se encontraron registros que coincidan con los filtros seleccionados.
        </div>
        <?php endif; ?>

        <?php while($clase = $clases->fetch_assoc()): 
            $porcentaje_asistencia = ($clase['total_estudiantes'] > 0) 
                ? round(($clase['total_presentes'] / $clase['total_estudiantes']) * 100) 
                : 0;
        ?>
        <div class="class-card">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h5 class="mb-2"><?php echo htmlspecialchars($clase['nombre_curso']); ?></h5>
                    <div class="mb-2">
                        <i class="far fa-calendar-alt me-2"></i>
                        <?php echo date('d F, Y', strtotime($clase['fecha'])); ?>
                        <i class="far fa-clock ms-3 me-2"></i>
                        <?php echo date('h:i A', strtotime($clase['hora'])); ?>
                    </div>
                    <div>
                        <span class="class-tag bg-info text-white">
                            <?php echo $clase['total_estudiantes']; ?> estudiantes
                        </span>
                        <span class="class-tag bg-<?php echo ($porcentaje_asistencia >= 70) ? 'success' : 'warning'; ?> text-white">
                            <?php echo $porcentaje_asistencia; ?>% Asistencia
                        </span>
                    </div>
                </div>
                <div>
                    <button class="btn btn-outline-primary me-2" 
                            onclick="cargarDetalles(<?php echo $clase['id_asistencia']; ?>)">
                        <i class="fas fa-chart-bar me-2"></i>Detalles
                    </button>
                    <a href="?exportar=excel&id_asistencia=<?php echo $clase['id_asistencia']; ?>" 
                       class="btn btn-outline-success me-2">
                        <i class="fas fa-file-excel me-2"></i>Excel
                    </a>
                </div>
            </div>
            <div id="detalles-<?php echo $clase['id_asistencia']; ?>" class="attendance-details"></div>
        </div>
        <?php endwhile; ?>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
    <script>
        function cargarDetalles(idAsistencia) {
            const detallesDiv = document.getElementById(`detalles-${idAsistencia}`);
            
            if (detallesDiv.style.display === 'block') {
                detallesDiv.style.display = 'none';
                return;
            }

            fetch(`?get_detalles=1&id_asistencia=${idAsistencia}`)
                .then(response => response.text())
                .then(data => {
                    detallesDiv.innerHTML = data;
                    detallesDiv.style.display = 'block';
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al cargar los detalles');
                });
        }
    </script>
</body>
</html>
