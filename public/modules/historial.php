<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    die("No hay sesión activa. Por favor, inicie sesión.");
}
$cedula_profesor = $_SESSION['cedula'];

require_once('../../config.php');

function getCursos($conn, $cedula_profesor) {
    $sql = "
        SELECT DISTINCT c.id_curso, c.nombre_curso
        FROM profesor_curso pc
        JOIN cursos c ON pc.id_curso = c.id_curso
        WHERE pc.cedula_profesor = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $cedula_profesor);
    $stmt->execute();
    
    return $stmt->get_result();
}

function getHistorialClases($conn, $cedula_profesor, $curso = null, $fechaInicio = null, $fechaFin = null) {
    $sql = "SELECT DISTINCT a.*, c.nombre_curso,
            (SELECT COUNT(DISTINCT ad.cedula) FROM asistencia_detalle ad 
             WHERE ad.id_asistencia = a.id_asistencia) as total_estudiantes,
            (SELECT COUNT(DISTINCT ad.cedula) FROM asistencia_detalle ad 
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
    
    $sql = " SELECT DISTINCT a.*, c.nombre_curso
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

function editarDetallesAsistencia($conn, $id_asistencia, $cedula, $nuevo_estado) {
    // Validate inputs
    if (!$id_asistencia || !$cedula || !in_array($nuevo_estado, ['Presente', 'Ausente','Tardanza'])) {
        return [
            'success' => false, 
            'error' => 'Datos inválidos'
        ];
    }

    try {
        // First, check if the record exists
        $check_sql = "SELECT * FROM asistencia_detalle 
                      WHERE id_asistencia = ? AND cedula = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("is", $id_asistencia, $cedula);
        $check_stmt->execute();
        $result = $check_stmt->get_result();

        if ($result->num_rows == 0) {
            return [
                'success' => false, 
                'error' => 'Registro de asistencia no encontrado'
            ];
        }

        // Prepare the update SQL
        $sql = "UPDATE asistencia_detalle 
                SET asistencia = ?
                WHERE id_asistencia = ? AND cedula = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sis", $nuevo_estado, $id_asistencia, $cedula);
        
        // Execute the update
        $execute_result = $stmt->execute();

        // Check for execution errors
        if (!$execute_result) {
            return [
                'success' => false, 
                'error' => 'Error en la actualización: ' . $stmt->error
            ];
        }

        // Check if any rows were actually updated
        if ($stmt->affected_rows == 0) {
            return [
                'success' => false, 
                'error' => 'No se realizaron cambios'
            ];
        }

        // Success
        return [
            'success' => true, 
            'mensaje' => 'Actualización exitosa'
        ];
    } catch (Exception $e) {
        // Catch any unexpected errors
        return [
            'success' => false, 
            'error' => 'Excepción: ' . $e->getMessage()
        ];
    }
}

// In your AJAX handling section:
if (isset($_POST['editar_asistencia'])) {
    $id_asistencia = intval($_POST['id_asistencia']);
    $cedula = $_POST['cedula'];
    $nuevo_estado = $_POST['nuevo_estado'];
    
    $resultado = editarDetallesAsistencia($conn, $id_asistencia, $cedula, $nuevo_estado);
    
    echo json_encode($resultado);
    exit;
}

function eliminarRegistroAsistencia($conn, $id_asistencia) {
    $sql_detalles = "DELETE FROM asistencia_detalle WHERE id_asistencia = ?";
    $stmt_detalles = $conn->prepare($sql_detalles);
    $stmt_detalles->bind_param("i", $id_asistencia);
    $stmt_detalles->execute();

    $sql_asistencia = "DELETE FROM asistencia WHERE id_asistencia = ?";
    $stmt_asistencia = $conn->prepare($sql_asistencia);
    $stmt_asistencia->bind_param("i", $id_asistencia);
    
    return $stmt_asistencia->execute();
}

if (isset($_POST['eliminar_asistencia'])) {
    $id_asistencia = $_POST['id_asistencia'];
    
    $resultado = eliminarRegistroAsistencia($conn, $id_asistencia);
    
    echo json_encode([
        'success' => $resultado, 
        'mensaje' => $resultado ? 'Registro eliminado exitosamente' : 'Error al eliminar'
    ]);
    exit;
}

$filtro_curso = isset($_GET['curso']) ? $_GET['curso'] : null;
$filtro_fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$filtro_fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

$clases = getHistorialClases($conn, $cedula_profesor, $filtro_curso, $filtro_fecha_inicio, $filtro_fecha_fin);
$cursos = getCursos($conn, $cedula_profesor);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Clases - Sistema de Gestión de Asistencia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        .editable-row input, .editable-row select {
            width: 100%;
        }
        .edit-mode {
            background-color: #f0f0f0;
        }
        /* Estilos generales para las tarjetas de clase */
            .class-card {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                margin-bottom: 20px;
                padding: 20px;
            }

            /* Detalles de asistencia (ocultos inicialmente) */
            .attendance-details {
                display: none;
                margin-top: 20px;
                border-top: 1px solid #eee;
                padding-top: 20px;
            }

            /* Estilos para el estado de asistencia */
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

            /* Estilos para las etiquetas dentro de las tarjetas de clase */
            .class-tag {
                padding: 4px 8px;
                border-radius: 4px;
                font-size: 0.9em;
                margin-right: 10px;
            }

            /* Estilos para las tarjetas de filtro */
            .filters-card {
                background: #fff;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
                margin-bottom: 20px;
                padding: 20px;
            }

            /* Estilos generales para botones */
            .btn {
                font-size: 0.9em;
            }

            /* Media Queries para adaptar el diseño en pantallas pequeñas */
            @media (max-width: 767px) {
                /* Estilos para las tarjetas de clase en pantallas pequeñas */
                .class-card {
                    padding: 15px;
                }

                .class-tag {
                    font-size: 0.8em;
                    margin-bottom: 5px;
                }

                /* Ajustes en los botones */
                .btn {
                    font-size: 0.8em;
                    width: 100%;
                    margin-bottom: 10px;
                }

                /* Alinear las etiquetas de estado y estudiantes en móviles */
                .class-card .d-flex {
                    flex-direction: column;
                    align-items: flex-start;
                }

                .class-card .d-flex div {
                    width: 100%;
                    margin-bottom: 10px;
                }

                /* Hacer que el contenido de detalles se muestre correctamente */
                .attendance-details {
                    margin-top: 15px;
                }
            }

            /* Media Queries para pantallas medianas y grandes */
            @media (min-width: 768px) {
                /* Asegura que las tarjetas de clase se distribuyan adecuadamente en pantallas grandes */
                .class-card {
                    padding: 20px;
                }

                .class-tag {
                    font-size: 1em;
                }

                /* Establecer el tamaño de los botones y espaciado en pantallas grandes */
                .btn {
                    font-size: 1em;
                    width: auto;
                }

                /* Alinear las etiquetas de estado y estudiantes en pantallas grandes */
                .class-card .d-flex {
                    flex-direction: row;
                    justify-content: space-between;
                }

                .class-card .d-flex div {
                    width: auto;
                    margin-bottom: 0;
                }
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
                    <button class="btn btn-outline-warning me-2" 
                            onclick="habilitarEdicion(<?php echo $clase['id_asistencia']; ?>)">
                        <i class="fas fa-edit me-2"></i>Editar
                    </button>
                    <button class="btn btn-outline-danger" 
                            onclick="confirmarEliminar(<?php echo $clase['id_asistencia']; ?>)">
                        <i class="fas fa-trash me-2"></i>Eliminar
                    </button>
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

        function habilitarEdicion(idAsistencia) {
            const detallesDiv = document.getElementById(`detalles-${idAsistencia}`);
            
            if (detallesDiv.style.display !== 'block') {
                cargarDetalles(idAsistencia);
            }

            setTimeout(() => {
                const tabla = detallesDiv.querySelector('table');
                const filas = tabla.querySelectorAll('tbody tr');

                filas.forEach(fila => {
                    const cedula = fila.querySelector('td:first-child').textContent;
                    const estadoActual = fila.querySelector('.attendance-status').textContent;

                    const select = document.createElement('select');
                    select.className = 'form-select';
                    ['Presente', 'Ausente', 'Tardanza'].forEach(estado => {
                        const option = document.createElement('option');
                        option.value = estado;
                        option.textContent = estado;
                        option.selected = (estado === estadoActual);
                        select.appendChild(option);
                    });

                    const estadoTd = fila.querySelector('td:nth-child(2)');
                    estadoTd.innerHTML = '';
                    estadoTd.appendChild(select);

                    const guardarBtn = document.createElement('button');
                    guardarBtn.className = 'btn btn-sm btn-primary mt-2';
                    guardarBtn.textContent = 'Guardar';
                    guardarBtn.onclick = () => guardarCambios(idAsistencia, cedula, select.value);
                    
                    estadoTd.appendChild(guardarBtn);

                    fila.classList.add('edit-mode');
                });
            }, 100);
        }
        function guardarCambios(idAsistencia, cedula, nuevoEstado) {
    fetch('', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `editar_asistencia=1&id_asistencia=${idAsistencia}&cedula=${cedula}&nuevo_estado=${nuevoEstado}`
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.mensaje);
            cargarDetalles(idAsistencia); // Recargar detalles
        } else {
            // Mostrar mensaje de error específico
            alert('Error: ' + (data.error || 'No se pudo guardar el cambio'));
            console.error('Detalles del error:', data);
        }
    })
    .catch(error => {
        console.error('Error de red:', error);
        alert('Error de conexión al guardar cambios');
    });
}

        function confirmarEliminar(idAsistencia) {
            if (confirm('¿Está seguro de que desea eliminar este registro de asistencia? Esta acción no se puede deshacer.')) {
                fetch('', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `eliminar_asistencia=1&id_asistencia=${idAsistencia}`
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.mensaje);
                        location.reload(); // Recargar página para reflejar cambios
                    } else {
                        alert('Error: ' + data.mensaje);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error al eliminar el registro');
                });
            }
        }

        function actualizarClases() {
            fetch('historial.php')
                .then(response => response.text()) 
                .then(data => {
                    const clasesContainer = document.getElementById('clases-container');
                    clasesContainer.innerHTML = data; 
                })
                .catch(error => console.error('Error al actualizar las clases:', error));
        }

        setInterval(actualizarClases, 30000);
</script>

    
</body>
</html>
