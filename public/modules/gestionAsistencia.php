<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    die("No hay sesión activa. Por favor, inicie sesión.");
}
$cedula_profesor = $_SESSION['cedula']; 

require_once('../../config.php');

if (isset($_GET['action']) && $_GET['action'] === 'get_students' && isset($_GET['id_curso'])) {
    $id_curso = $_GET['id_curso'];
    
    $sql = "SELECT e.cedula, u.nombre, u.apellido
            FROM estudiantes_cursos ec
            JOIN estudiantes e ON ec.cedula = e.cedula
            JOIN usuarios u ON e.cedula = u.cedula
            WHERE ec.id_curso = ?
            ORDER BY u.apellido, u.nombre";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_curso);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo '<tr>
                    <td>' . htmlspecialchars($row['cedula']) . '</td>
                    <td>' . htmlspecialchars($row['apellido'] . ', ' . $row['nombre']) . '</td>
                    <td>
                        <select class="form-select" name="asistencia[' . $row['cedula'] . ']" required>
                            <option value="Presente">Presente</option>
                            <option value="Ausente">Ausente</option>
                        </select>
                    </td>
                  </tr>';
        }
    } else {
        echo '<tr><td colspan="3" class="text-center">No hay estudiantes registrados en este curso.</td></tr>';
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_asistencia'])) {
    $response = array();
    
    $id_curso = $_POST['id_curso'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora_inicio'];
    
    $sql_asistencia = "INSERT INTO asistencia (id_curso, fecha, hora, cedula_profesor) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_asistencia);
    $stmt->bind_param("isss", $id_curso, $fecha, $hora, $cedula_profesor);
    
    if ($stmt->execute()) {
        $id_asistencia = $conn->insert_id; // Obtener el ID de la nueva asistencia
        $sql_historial = "SELECT a.id_asistencia, a.fecha, a.hora, c.nombre_curso 
                          FROM asistencia a
                          JOIN cursos c ON a.id_curso = c.id_curso
                          WHERE a.id_asistencia = ?";
        $stmt_historial = $conn->prepare($sql_historial);
        $stmt_historial->bind_param("i", $id_asistencia);
        $stmt_historial->execute();
        $result_historial = $stmt_historial->get_result();
        
        $new_record = $result_historial->fetch_assoc();
        
        $response['status'] = 'success';
        $response['id_asistencia'] = $id_asistencia;
        $response['message'] = 'Asistencia creada con éxito';
        $response['new_record'] = $new_record; // Devolver los datos del nuevo registro
        
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error al crear la asistencia';
    }
    
    echo json_encode($response);
    exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_detalle_asistencia'])) {
    $response = array();
    $success = true;
    
    $id_asistencia = $_POST['id_asistencia'];
    
    foreach ($_POST['asistencia'] as $cedula => $estado) {
        // Verificar si ya existe un registro para este id_asistencia y cedula
        $sql_check = "SELECT 1 FROM asistencia_detalle WHERE id_asistencia = ? AND cedula = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("is", $id_asistencia, $cedula);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        // Si no existe, insertar el detalle
        if ($result_check->num_rows == 0) {
            $sql_detalle = "INSERT INTO asistencia_detalle (id_asistencia, cedula, asistencia) VALUES (?, ?, ?)";
            $stmt_detalle = $conn->prepare($sql_detalle);
            $stmt_detalle->bind_param("iss", $id_asistencia, $cedula, $estado);
            
            if (!$stmt_detalle->execute()) {
                $success = false;
                break;
            }
        }
    }
    
    if ($success) {
        $response['status'] = 'success';
        $response['message'] = 'Asistencias registradas con éxito';
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Error al registrar las asistencias';
    }
    
    echo json_encode($response);
    exit;
}

// Filtrar todos los cursos del profesor logueado
$sql_cursos = "SELECT c.id_curso, c.nombre_curso
               FROM cursos c
               JOIN profesor_curso pc ON c.id_curso = pc.id_curso
               WHERE pc.cedula_profesor = ?";
$stmt = $conn->prepare($sql_cursos);
$stmt->bind_param("s", $cedula_profesor);
$stmt->execute();
$resultado_cursos = $stmt->get_result();
$cursos = $resultado_cursos->fetch_all(MYSQLI_ASSOC);

// Estadísticas
$sqlTotalCursos = "SELECT COUNT(*) AS total_cursos FROM cursos";
$totalCursos = $conn->query($sqlTotalCursos)->fetch_assoc()['total_cursos'];

$sqlTotalClases = "SELECT COUNT(*) AS total_clases FROM asistencia";
$totalClasesCompletadas = $conn->query($sqlTotalClases)->fetch_assoc()['total_clases'];

$conn->close();
?>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Asistencia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/../AsistenciaVirtual/public/assets/css/asistencia.css">
    
</head>
<body>
    <main class="main-content">
    <div class="modal" id="responseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content animate-modal">
                <div class="modal-header">
                    <h5 class="modal-title">Notificación</h5>
                    <button type="button" class="btn-close" onclick="closeModal()" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modalMessage"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary" onclick="closeModal()">Aceptar</button>
                </div>
            </div>
        </div>
    </div>


<div id="asistenciaPrincipalForm" class="stats-container">
    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="class-card">
                <div class="card-body">
                    <h6>Seleccionar Curso</h6>
                    <select class="form-select" id="id_curso" name="id_curso">
                        <option value="">Seleccione un curso</option>
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?php echo $curso['id_curso']; ?>">
                                <?php echo htmlspecialchars($curso['nombre_curso']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div id="classForm" class="form-container" style="display: none;">
        <h3 class="form-title">
            <i class="fas fa-clipboard-check me-2"></i> Nueva Asistencia
        </h3>
        <form id="asistenciaForm" method="POST">
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label fw-bold">Fecha</label>
                        <input type="date" class="form-control" name="fecha" id="fecha" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-label fw-bold">Hora</label>
                        <input type="time" class="form-control" name="hora_inicio" id="hora_inicio" required>
                    </div>
                </div>
            </div>

            <input type="hidden" name="id_curso" id="curso_id_hidden">
            <input type="hidden" name="registrar_asistencia" value="1">

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Crear Asistencia
                </button>
                <button type="button" class="btn btn-secondary ms-2" id="cancelButton">
                    <i class="fas fa-times me-2"></i>Cancelar
                </button>
            </div>
        </form>
    </div>
</div>


        <div id="asistenciaDetalleForm" class="form-container" style="display: none;">
            <h3 class="form-title">
                <i class="fas fa-user-check me-2"></i> Registro de Asistencias
            </h3>
            <form id="detalleAsistenciaForm" method="POST">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Cédula</th>
                                <th>Nombre del Estudiante</th>
                                <th>Asistencia</th>
                            </tr>
                        </thead>
                        <tbody id="estudiantesTabla">
                        </tbody>
                    </table>
                </div>

                <input type="hidden" name="id_asistencia" id="id_asistencia_hidden">
                <input type="hidden" name="registrar_detalle_asistencia" value="1">

                <div class="mt-4 text-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i>Guardar Asistencias
                    </button>
                </div>
            </form>
        </div>
    </main>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
    document.getElementById('cancelButton').addEventListener('click', function() {
        document.getElementById('fecha').value = '';
        document.getElementById('hora_inicio').value = '';
        document.getElementById('classForm').style.display = 'none';
        document.getElementById('id_curso').value = '';
    });
</script>

<script>

function cargarEstudiantes(cursoId) {
            fetch(`gestionAsistencia.php?action=get_students&id_curso=${cursoId}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('estudiantesTabla').innerHTML = html;
                })
                .catch(error => {
                    console.error('Error:', error);
                    mostrarModal('error', 'Error al cargar los estudiantes');
                });
        }

        document.getElementById('id_curso').addEventListener('change', function() {
            const cursoId = this.value;
            const formContainer = document.getElementById('classForm');
            const cursoIdHidden = document.getElementById('curso_id_hidden');
            
            if (cursoId) {
                formContainer.style.display = 'block';
                cursoIdHidden.value = cursoId;
            } else {
                formContainer.style.display = 'none';
            }
        });

        document.getElementById('asistenciaForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('gestionAsistencia.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('id_asistencia_hidden').value = data.id_asistencia;
                    document.getElementById('asistenciaPrincipalForm').style.display = 'none';
                    document.getElementById('asistenciaDetalleForm').style.display = 'block';
                    cargarEstudiantes(formData.get('id_curso'));
                } else {
                    mostrarModal('error', data.message || 'Error al crear la asistencia');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarModal('error', 'Error al procesar la solicitud');
            });
        });

        document.getElementById('detalleAsistenciaForm').addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            fetch('gestionAsistencia.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    mostrarModal('success', '¡Registro guardado exitosamente!');
                    this.reset();
                    document.getElementById('asistenciaForm').reset();
                    document.getElementById('id_curso').value = '';
                    document.getElementById('classForm').style.display = 'none';
                    document.getElementById('asistenciaDetalleForm').style.display = 'none';
                    document.getElementById('asistenciaPrincipalForm').style.display = 'block';
                } else {
                    mostrarModal('error', data.message || 'Error al guardar las asistencias');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarModal('error', 'Error al procesar la solicitud');
            });
        });

        function mostrarModal(tipo, mensaje) {
    const modalElement = document.getElementById('responseModal');
    const modalBody = document.getElementById('modalMessage');
    const modalContent = document.querySelector('.modal-content');
    
    if (tipo === 'success') {
        modalBody.innerHTML = ` 
            <div class="text-center">
                <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                <p class="h5">${mensaje}</p>
            </div>
        `;
        modalContent.className = 'modal-content modal-success animate-modal';
    } else {
        modalBody.innerHTML = ` 
            <div class="text-center">
                <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                <p class="h5">Error</p>
                <p class="text-muted">${mensaje}</p>
            </div>
        `;
        modalContent.className = 'modal-content modal-error animate-modal';
    }
    
    modalElement.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('responseModal');
    modal.style.display = 'none'; 
}

    </script>
</body>
</html>

