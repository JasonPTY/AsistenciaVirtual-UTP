<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: /Demo-Sas/View/login.php");
    exit();
}
$cedula_profesor = $_SESSION['cedula'];

require_once '../../app/modules/attendance/attendanceRepository.php';

$attendanceRepository = new AttendanceRepository();

// Retornar estudiantes de un curso via AJAX
if (isset($_GET['action']) && $_GET['action'] === 'get_students' && isset($_GET['id_curso'])) {
    $id_curso = (int) $_GET['id_curso'];
    $students = $attendanceRepository->getStudentsByCourse($id_curso);

    if (!empty($students)) {
        foreach ($students as $row) {
            echo '<tr>
                    <td>' . htmlspecialchars($row['cedula']) . '</td>
                    <td>' . htmlspecialchars($row['apellido'] . ', ' . $row['nombre']) . '</td>
                    <td>
                        <select class="form-select" name="asistencia[' . $row['cedula'] . ']" required>
                            <option value="Presente">Presente</option>
                            <option value="Ausente">Ausente</option>
                            <option value="Tardanza">Tardanza</option>
                        </select>
                    </td>
                  </tr>';
        }
    } else {
        echo '<tr><td colspan="3" class="text-center">No hay estudiantes registrados en este curso.</td></tr>';
    }
    exit;
}

// Crear registro de asistencia
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_asistencia'])) {
    $id_curso = (int) $_POST['id_curso'];
    $fecha    = $_POST['fecha'];
    $hora     = $_POST['hora_inicio'];

    $id_asistencia = $attendanceRepository->createAttendance(
        $id_curso,
        $fecha,
        $hora,
        $cedula_profesor
    );

    if ($id_asistencia !== false) {
        $new_record = $attendanceRepository->getAttendanceById($id_asistencia);

        echo json_encode([
            'status'        => 'success',
            'id_asistencia' => $id_asistencia,
            'message'       => 'Asistencia creada con éxito',
            'new_record'    => $new_record,
        ]);
    } else {
        echo json_encode([
            'status'  => 'error',
            'message' => 'Error al crear la asistencia',
        ]);
    }

    exit;
}

// Registrar detalle de asistencia por estudiante
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_detalle_asistencia'])) {
    $id_asistencia = (int) $_POST['id_asistencia'];
    $success = true;

    foreach ($_POST['asistencia'] as $cedula => $estado) {
        if (!$attendanceRepository->saveAttendanceDetail($id_asistencia, $cedula, $estado)) {
            $success = false;
            break;
        }
    }

    echo json_encode([
        'status'  => $success ? 'success' : 'error',
        'message' => $success
            ? 'Asistencias registradas con éxito'
            : 'Error al registrar las asistencias',
    ]);

    exit;
}

// Datos para la vista
$cursos                 = $attendanceRepository->getCoursesByProfessor($cedula_profesor);
$totalCursos            = $attendanceRepository->getTotalCourses();
$totalClasesCompletadas = $attendanceRepository->getTotalClasses();

// Nombre del archivo actual para las peticiones fetch
$self = basename(__FILE__);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Asistencia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/../Demo-Sas/public/assets/css/asistencia.css">
</head>
<body>
<main class="main-content">

    <!-- Modal de respuesta -->
    <div class="modal" id="responseModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content animate-modal" id="modalContent">
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

    <!-- Formulario principal: selección de curso, fecha y hora -->
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

    <!-- Formulario de detalle: lista de estudiantes -->
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
                    <tbody id="estudiantesTabla"></tbody>
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
    const SELF = '<?php echo $self; ?>';

    // ─── Helpers ────────────────────────────────────────────────────────────────

    function mostrarModal(tipo, mensaje) {
        const modalContent = document.getElementById('modalContent');
        const modalBody    = document.getElementById('modalMessage');

        // Resetear clases antes de asignar la nueva
        modalContent.className = 'modal-content animate-modal';

        if (tipo === 'success') {
            modalContent.classList.add('modal-success');
            modalBody.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                    <p class="h5">${mensaje}</p>
                </div>`;
        } else {
            modalContent.classList.add('modal-error');
            modalBody.innerHTML = `
                <div class="text-center">
                    <i class="fas fa-times-circle fa-4x text-danger mb-3"></i>
                    <p class="h5">Error</p>
                    <p class="text-muted">${mensaje}</p>
                </div>`;
        }

        document.getElementById('responseModal').style.display = 'block';
    }

    function closeModal() {
        document.getElementById('responseModal').style.display = 'none';
    }

    function resetFormulario() {
        document.getElementById('asistenciaForm').reset();
        document.getElementById('id_curso').value = '';
        document.getElementById('classForm').style.display = 'none';
        document.getElementById('asistenciaDetalleForm').style.display = 'none';
        document.getElementById('asistenciaPrincipalForm').style.display = 'block';
    }

    function cargarEstudiantes(cursoId) {
        fetch(`${SELF}?action=get_students&id_curso=${cursoId}`)
            .then(response => response.text())
            .then(html => {
                document.getElementById('estudiantesTabla').innerHTML = html;
            })
            .catch(() => mostrarModal('error', 'Error al cargar los estudiantes'));
    }

    // ─── Eventos ────────────────────────────────────────────────────────────────

    document.getElementById('id_curso').addEventListener('change', function () {
        const cursoId = this.value;
        const classForm = document.getElementById('classForm');

        if (cursoId) {
            document.getElementById('curso_id_hidden').value = cursoId;
            classForm.style.display = 'block';
        } else {
            classForm.style.display = 'none';
        }
    });

    document.getElementById('cancelButton').addEventListener('click', function () {
        document.getElementById('fecha').value      = '';
        document.getElementById('hora_inicio').value = '';
        document.getElementById('classForm').style.display = 'none';
        document.getElementById('id_curso').value   = '';
    });

    document.getElementById('asistenciaForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch(SELF, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('id_asistencia_hidden').value = data.id_asistencia;
                    document.getElementById('asistenciaPrincipalForm').style.display = 'none';
                    document.getElementById('asistenciaDetalleForm').style.display  = 'block';
                    cargarEstudiantes(formData.get('id_curso'));
                } else {
                    mostrarModal('error', data.message || 'Error al crear la asistencia');
                }
            })
            .catch(() => mostrarModal('error', 'Error al procesar la solicitud'));
    });

    document.getElementById('detalleAsistenciaForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);

        fetch(SELF, { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    mostrarModal('success', '¡Registro guardado exitosamente!');
                    resetFormulario();
                } else {
                    mostrarModal('error', data.message || 'Error al guardar las asistencias');
                }
            })
            .catch(() => mostrarModal('error', 'Error al procesar la solicitud'));
    });
</script>
</body>
</html>