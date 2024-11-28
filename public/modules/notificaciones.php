<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    die("No hay sesión activa. Por favor, inicie sesión.");
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once('../../config.php');  // Mi conexión al servidor
$cedula = $_SESSION['cedula'];

// Obtener el tipo de usuario
$query = "SELECT id_tipoUsuario FROM usuarios WHERE cedula = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $cedula);
$stmt->execute();
$stmt->bind_result($id_tipoUsuario);
$stmt->fetch();
$stmt->close();

$cedula_profesor = null;
$cedula_estudiante = null;

if ($id_tipoUsuario == 3) {
    $cedula_profesor = $cedula; // Si el logueado es Profesor
} elseif ($id_tipoUsuario == 2) {
    $cedula_estudiante = $cedula; // Si el logueado es Estudiante
}

// Obtener el correo del usuario
$queryEmail = "SELECT correo FROM usuarios WHERE cedula = ?";
$stmtEmail = $conn->prepare($queryEmail);
$stmtEmail->bind_param("s", $cedula);
$stmtEmail->execute();
$stmtEmail->bind_result($emailUsuario);
$stmtEmail->fetch();
if (!$emailUsuario) {
    echo "Correo del usuario no encontrado.";
}
$stmtEmail->close();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'send_email') {
    error_log("Datos recibidos: " . print_r($_POST, true));

    require_once 'C:/xampp/htdocs/AsistenciaVirtual/vendor/autoload.php';
    $response = array();

    // Validar destinatarios
    if (isset($_POST['recipients']) && !empty($_POST['recipients'])) {
        $recipients = explode(',', $_POST['recipients']);
        $recipients = array_map('trim', $recipients); 
    } else {
        echo "error: Debe proporcionar al menos un destinatario.";
        exit;
    }

    try {
        $mail = new PHPMailer(true);

        // Configuración del servidor
        $mail->isSMTP();
        $mail->Host = 'smtp-mail.outlook.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'jason.arena@utp.ac.pa';
        $mail->Password = 'ThePana27278_utp';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('jason.arena@utp.ac.pa', 'Sistema de Notificaciones');
        foreach ($recipients as $recipient) {
            $mail->addAddress(trim($recipient));
        }

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = $_POST['subject'];
        $mail->Body = $_POST['message'];

        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == UPLOAD_ERR_OK) {
            $file_tmp_path = $_FILES['attachment']['tmp_name'];
            $file_name = $_FILES['attachment']['name'];
            $mail->addAttachment($file_tmp_path, $file_name);
        }
        $mail->send();

        // Insertar en la tabla de notificaciones
        $stmt = $conn->prepare("INSERT INTO notificaciones (tipo, asunto, mensaje, es_urgente, fecha_envio, estado, correo_destinatario, cedula_profesor) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $tipo = $_POST['type'];
        $asunto = $_POST['subject'];
        $mensaje = $_POST['message'];
        $urgente = isset($_POST['urgent']) ? 1 : 0;
        $fecha_envio = date('Y-m-d H:i:s');
        $estado = 'enviado';

        $correo_destinatario = implode(', ', $recipients); // Concatena los destinatarios
        $stmt->bind_param("ssssssss", $tipo, $asunto, $mensaje, $urgente, $fecha_envio, $estado, $correo_destinatario, $cedula_profesor);
        $stmt->execute();
        $notificacion_id = $stmt->insert_id; // ID de la notificación
        $stmt->close();

        // Asociar la notificación con cada destinatario
        foreach ($recipients as $recipient) {
            $stmtCedula = $conn->prepare("SELECT cedula FROM usuarios WHERE correo = ?");
            if ($stmtCedula) {
                $stmtCedula->bind_param("s", $recipient);
                $stmtCedula->execute();
                $stmtCedula->bind_result($cedula_estudiante);
                $stmtCedula->fetch();
                $stmtCedula->close();

                if ($cedula_estudiante) {
                    $stmtUsuario = $conn->prepare("INSERT INTO notificaciones_usuarios (id_notificacion, cedula_profesor, cedula_estudiante, fecha_recibido) VALUES (?, ?, ?, ?)");
                    if ($stmtUsuario) {
                        $fecha_recibido = date('Y-m-d H:i:s');
                        $stmtUsuario->bind_param("isss", $notificacion_id, $cedula_profesor, $cedula_estudiante, $fecha_recibido);
                        $stmtUsuario->execute();
                        $stmtUsuario->close();
                    } else {
                        error_log("Error al preparar la consulta para insertar en notificaciones_usuarios");
                    }
                } else {
                    error_log("Error: No se encontró cédula para el correo $recipient");
                }
            } else {
                error_log("Error al preparar la consulta para buscar la cédula del usuario.");
            }
        }

        echo "success";
    } catch (Exception $e) {
        echo "error: " . $mail->ErrorInfo;
        error_log("Excepción al enviar el correo: " . $e->getMessage());
    }
    exit;
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Notificaciones</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.css" rel="stylesheet">
    <style>
        .notification-item {
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #fff;
        }
        .notification-item.urgent {
            border-left: 5px solid #dc3545;
        }
        .notification-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .timeline {
            padding: 20px;
        }
    </style>
</head>
<body>
    <main class="main-content">
                <div class="container-fluid">
                    <?php 
                        if ($id_tipoUsuario == 2) {
                            $queryCorreoEstudiante = "SELECT correo FROM usuarios WHERE cedula = ?";
                            $stmtCorreoEstudiante = $conn->prepare($queryCorreoEstudiante);
                            $stmtCorreoEstudiante->bind_param("s", $cedula_estudiante);
                            $stmtCorreoEstudiante->execute();
                            $stmtCorreoEstudiante->bind_result($correoEstudiante);
                            $stmtCorreoEstudiante->fetch();
                            $stmtCorreoEstudiante->close();

                            $queryNotificacionesEstudiante = "
                                SELECT * 
                                FROM notificaciones
                                WHERE correo_destinatario LIKE ? 
                                ORDER BY fecha_envio DESC
                            ";
                            $stmtNotificacionesEstudiante = $conn->prepare($queryNotificacionesEstudiante);
                            $correoDestinatario = "%" . $correoEstudiante . "%";
                            $stmtNotificacionesEstudiante->bind_param("s", $correoDestinatario);
                            $stmtNotificacionesEstudiante->execute();
                            $resultNotificacionesEstudiante = $stmtNotificacionesEstudiante->get_result();

                            $notifications = [];
                            while ($rowNotificacion = $resultNotificacionesEstudiante->fetch_assoc()) {
                                $notifications[] = $rowNotificacion;
                            }

                            $stmtNotificacionesEstudiante->close();
                        } elseif ($id_tipoUsuario == 3) {
                            $queryCorreoProfesor = "SELECT correo FROM usuarios WHERE cedula = ?";
                            $stmtCorreoProfesor = $conn->prepare($queryCorreoProfesor);
                            $stmtCorreoProfesor->bind_param("s", $cedula_profesor);
                            $stmtCorreoProfesor->execute();
                            $stmtCorreoProfesor->bind_result($correoProfesor);
                            $stmtCorreoProfesor->fetch();
                            $stmtCorreoProfesor->close();

                            $queryNotificacionesProfesor = "
                                SELECT n.*, u.correo AS correo_profesor
                                FROM notificaciones n
                                JOIN usuarios u ON n.cedula_profesor = u.cedula
                                WHERE n.cedula_profesor = ? 
                                ORDER BY n.fecha_envio DESC
                            ";
                            $stmtNotificacionesProfesor = $conn->prepare($queryNotificacionesProfesor);
                            $stmtNotificacionesProfesor->bind_param("s", $cedula_profesor);
                            $stmtNotificacionesProfesor->execute();
                            $resultNotificacionesProfesor = $stmtNotificacionesProfesor->get_result();

                            $notifications = [];
                            while ($rowNotificacion = $resultNotificacionesProfesor->fetch_assoc()) {
                                $notifications[] = $rowNotificacion;
                            }

                            $stmtNotificacionesProfesor->close();
                        }
                    ?>
                </div>

                <!-- Filtros -->
                <div class="filters mb-4" <?php echo $id_tipoUsuario == 3 ? '' : 'style="display: none;"'; ?>>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">
                                <i class="fas fa-filter me-2"></i>Tipo de Notificación
                            </label>
                            <select class="form-select" id="notificationType">
                                <option value="all">Todas</option>
                                <option value="urgent">Urgentes</option>
                                <option value="warning">Advertencias</option>
                                <option value="success">Éxitos</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">
                                <i class="fas fa-eye me-2"></i>Estado
                            </label>
                            <select class="form-select" id="readStatus">
                                <option value="all">Todos</option>
                                <option value="unread">No leídas</option>
                                <option value="read">Leídas</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">
                                <i class="fas fa-calendar-alt me-2"></i>Fecha Desde
                            </label>
                            <input type="date" class="form-control" id="dateFrom">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">
                                <i class="fas fa-calendar-check me-2"></i>Fecha Hasta
                            </label>
                            <input type="date" class="form-control" id="dateTo">
                        </div>
                    </div>
                </div>

                <!-- Lista de Notificaciones -->
                <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <?php 
                    if ($id_tipoUsuario == 2) {
                        echo '<h5 class="card-title mb-0">
                                <i class="fas fa-list me-2"></i>Notificaciones Recibidas </h5>'
                            ;
                    } else {
                        echo '<h5 class="card-title mb-0">
                                <i class="fas fa-list me-2"></i>Notificaciones Enviadas
                            </h5>';
                    }
                ?>
                <div>
                    <?php 
                        if ($id_tipoUsuario == 3) { // Solo para profesores
                            echo '
                                <button class="btn btn-secondary me-2" id="toggleFilters">
                                    <i class="fas fa-filter me-2"></i>Filtros
                                </button>
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#emailModal">
                                    <i class="fas fa-envelope me-2"></i>Enviar Notificación
                                </button>';
                        }
                    ?>
                </div>
            </div>

                    <!-- Modal de Envío de Correo -->
                    <div class="modal fade" id="emailModal" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="fas fa-envelope me-2"></i>Enviar Notificación
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <form id="emailForm">
                                                <input type="hidden" name="action" value="send_email">

                                                <!-- Destinatarios -->
                                                <div class="mb-3">
                                                    <label class="form-label">
                                                        <i class="fas fa-at me-2"></i>Destinatarios
                                                    </label>
                                                    <select class="form-select" id="recipientType" name="recipientType" onchange="toggleRecipients()">
                                                        <option value="all">Seleccionar destinatario</option>
                                                        <option value="custom">Personalizado</option>
                                                        <option value="saved">Estudiantes</option>
                                                    </select>
                                                </div>

                                                <!-- Input de correo personalizado (solo si 'personalizado' es seleccionado) -->
                                                <div class="mb-3" id="customEmailDiv" style="display: none;">
                                                    <label class="form-label">
                                                        <i class="fas fa-envelope me-2"></i>Correo Personalizado
                                                    </label>
                                                    <input type="email" class="form-control" id="customEmail" name="customEmail" placeholder="Escribe el correo" required>
                                                </div>

                                                <!-- Estudiantes de ese profesor -->
                                        <div class="mb-3" id="studentSelectDiv" style="display: none;">
                                            <label class="form-label">
                                                <i class="fas fa-users me-2"></i>Seleccionar Estudiante
                                            </label>
                                            <select class="form-select" id="studentSelect" name="studentEmail" onchange="setCustomEmail()">
                                                <option value="">Seleccione un estudiante</option>
                                                <?php
                                                // Consulta para obtener los estudiantes y su porcentaje de asistencia
                                                $queryEstudiantes = "
                                                SELECT DISTINCT u.nombre, u.correo, e.cedula, 
                                                    IFNULL(
                                                        (SUM(CASE WHEN ad.asistencia = 'Presente' THEN 1 ELSE 0 END) / COUNT(ad.asistencia)) * 100, 
                                                        0
                                                    ) AS porcentaje_asistencia
                                                FROM estudiantes e
                                                JOIN usuarios u ON e.cedula = u.cedula
                                                JOIN estudiantes_cursos ec ON e.cedula = ec.cedula
                                                LEFT JOIN asistencia_detalle ad ON ad.cedula = e.cedula
                                                WHERE ec.id_curso IN (SELECT id_curso FROM profesor_curso WHERE cedula_profesor = ?)
                                                GROUP BY e.cedula
                                                ";
                                                $stmtEstudiantes = $conn->prepare($queryEstudiantes);
                                                $stmtEstudiantes->bind_param("s", $cedula_profesor); // Cedula del profesor
                                                $stmtEstudiantes->execute();
                                                $resultEstudiantes = $stmtEstudiantes->get_result();

                                                // Itera sobre los resultados y genera una opción para cada estudiante
                                                while ($rowEstudiante = $resultEstudiantes->fetch_assoc()) {
                                                    $nombre_estudiante = $rowEstudiante['nombre'];
                                                    $correo_estudiante = $rowEstudiante['correo'];
                                                    $porcentaje_asistencia = $rowEstudiante['porcentaje_asistencia'];
                                                    echo "<option value='{$correo_estudiante}' data-nombre='{$nombre_estudiante}' data-porcentaje='{$porcentaje_asistencia}'>";
                                                    echo "{$correo_estudiante}</option>";
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        
                                        <!-- Input de correo personalizado (será invisible, pero tendrá el valor almacenado del select) -->
                                        <input type="email" class="form-control" id="customEmail" name="customEmail" placeholder="Correo personalizado" style="display:none;">
                                                <!-- Asunto -->
                                                <div class="mb-3">
                                                    <label class="form-label">
                                                        <i class="fas fa-tag me-2"></i>Asunto
                                                    </label>
                                                    <input type="text" class="form-control" name="subject" required>
                                                </div>

                                                <!-- Plantilla -->
                                                <div class="mb-3">
                                                    <label class="form-label">
                                                        <i class="fas fa-file-alt me-2"></i>Plantilla
                                                    </label>
                                                    <select class="form-select" id="emailTemplate" onchange="applyTemplate()">
                                                        <option value="">Seleccione una plantilla</option>
                                                        <option value="attendance">Asistencia</option>
                                                        <option value="warning">Advertencia</option>
                                                        <option value="urgent">Urgente</option> <!-- Opción para urgente -->
                                                        <option value="good">Bien hecho</option> <!-- Opción para bien hecho -->
                                                    </select>

                                                </div>

                                                <!-- Mensaje -->
                                                <div class="mb-3">
                                                    <label class="form-label">
                                                        <i class="fas fa-edit me-2"></i>Mensaje
                                                    </label>
                                                    <textarea class="form-control" name="message" rows="5" required></textarea>
                                                </div>

                                                <!-- Archivo adjunto -->
                                                <div class="mb-3">
                                                    <label class="form-label">
                                                        <i class="fas fa-paperclip me-2"></i>Adjuntar archivo
                                                    </label>
                                                    <input type="file" class="form-control" name="attachment">
                                                </div>

                                                <!-- Tipo de notificación -->
                                                <div class="mb-3">
                                                    <label class="form-label">
                                                        <i class="fas fa-exclamation-circle me-2"></i>Tipo
                                                    </label>
                                                    <select class="form-select" name="type" required>
                                                        <option value="Normal">Normal</option>
                                                        <option value="Warning">Advertencia</option>
                                                        <option value="Urgent">Urgente</option>
                                                    </select>
                                                </div>

                                                <!-- Marcar como urgente -->
                                                <div class="form-check mb-3">
                                                    <input class="form-check-input" type="checkbox" name="urgent" id="urgent">
                                                    <label class="form-check-label" for="urgent">
                                                        Marcar como urgente
                                                    </label>
                                                </div>
                                                    <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                    <button type="button" class="btn btn-primary" id="sendNotification">Enviar</button>
                                                </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                    <div class="timeline">
                            <?php if (empty($notifications)): ?>
                                <p>No tienes notificaciones.</p>
                            <?php else: ?>
                                <?php foreach ($notifications as $notification): ?>
                                    <div class="notification-item <?php echo $notification['es_urgente'] ? 'urgent' : ''; ?>">
                                        <div class="notification-header">
                                            <span class="badge bg-<?php echo $notification['es_urgente'] ? 'danger' : 'primary'; ?>">
                                                <?php echo $notification['tipo']; ?>
                                            </span>
                                            <span class="text-muted">
                                                <?php echo date('d/m/Y H:i', strtotime($notification['fecha_envio'])); ?>
                                            </span>
                                        </div>
                                        <div class="notification-content">
                                            <h5><?php echo htmlspecialchars($notification['asunto']); ?></h5>
                                            <p><?php echo nl2br(htmlspecialchars($notification['mensaje'])); ?></p>
                                            <?php if ($id_tipoUsuario == 2): ?>
                                            <?php 
                                                // Obtener el correo del profesor (remitente)
                                                $queryCorreoProfesor = "SELECT correo FROM usuarios WHERE cedula = ?";
                                                $stmtCorreoProfesor = $conn->prepare($queryCorreoProfesor);
                                                $stmtCorreoProfesor->bind_param("s", $notification['cedula_profesor']);
                                                $stmtCorreoProfesor->execute();
                                                $stmtCorreoProfesor->bind_result($correoProfesor);
                                                $stmtCorreoProfesor->fetch();
                                                $stmtCorreoProfesor->close();
                                            ?>
                                            <p><strong>Enviado por: </strong><?php echo htmlspecialchars($correoProfesor); ?></p>
                                        <?php elseif ($id_tipoUsuario == 3): ?>
                                            <p><strong>Enviado a: </strong><?php echo htmlspecialchars($notification['correo_destinatario']); ?></p>
                                        <?php endif; ?>

                                        </div>
                                    </div>
                                <?php endforeach; ?>
                        <?php endif; ?>     
                </div>
            </div>
            </div>
    </main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $(document).ready(function() {
        $('#markAsReceived').on('click', function() {
            var notificationId = $(this).data('id');
            var emailEstudiante = $(this).data('email');

            $.ajax({
                url: 'notificaciones.php',
                method: 'POST',
                data: {
                    action: 'mark_received',
                    notification_id: notificationId,
                    recipient_email: emailEstudiante
                },
                success: function(response) {
                    if (response === 'Notificación marcada como recibida') {
                        $('#markAsReceived').text('Recibido').prop('disabled', true); // Deshabilitar el botón

                        $('<div class="alert alert-success">¡Notificación recibida!</div>')
                            .appendTo('body')
                            .fadeIn()
                            .delay(2000)
                            .fadeOut();
                    } else {
                        alert('Hubo un error al marcar la notificación como recibida.');
                    }
                },
                error: function() {
                    alert('Error al procesar la solicitud.');
                }
            });
        });
    });
    </script>
        <script>
            function toggleRecipients() {
            const recipientType = document.getElementById('recipientType').value;
            const studentSelectDiv = document.getElementById('studentSelectDiv');
            const customEmailDiv = document.getElementById('customEmailDiv');

            // Ocultar ambos campos por defecto
            studentSelectDiv.style.display = 'none';
            customEmailDiv.style.display = 'none';

            // Mostrar el campo correspondiente basado en el tipo de destinatario
            if (recipientType === 'custom') {
                customEmailDiv.style.display = 'block';
            } else if (recipientType === 'saved') {
                studentSelectDiv.style.display = 'block';
            }
        }

        // Colocar el valor seleccionado en el campo de correo personalizado
        function setCustomEmail() {
            const selectedEmail = document.getElementById('studentSelect').value;
            document.getElementById('customEmail').value = selectedEmail;
        }

        // Función para aplicar la plantilla de mensaje
        function applyTemplate() {
            const messageTemplate = document.getElementById('emailTemplate').value;
            const studentSelect = document.getElementById('studentSelect');
            const selectedStudent = studentSelect.options[studentSelect.selectedIndex];
            const nombre = selectedStudent ? selectedStudent.getAttribute('data-nombre') : '';
            const porcentaje = selectedStudent ? parseFloat(selectedStudent.getAttribute('data-porcentaje')) : 0;
            
            let message = '';
            let notificationType = 'none';

            // Condicional para generar el mensaje basado en el porcentaje de asistencia
            if (messageTemplate === 'attendance') {
                if (porcentaje < 50) {
                    message = `Estimado/a ${nombre},\n\nLe informamos que su porcentaje de asistencia es de ${porcentaje}%, lo cual es inferior al 50%. Es importante que tome medidas para mejorar su asistencia.\n\nSaludos cordiales,\nSistema de Notificaciones`;
                    notificationType = 'urgent,warning'; // Urgente y advertencia
                } else if (porcentaje < 75) {
                    message = `Estimado/a ${nombre},\n\nLe informamos que su porcentaje de asistencia es de ${porcentaje}%, lo cual es aceptable, pero se recomienda mejorar en las próximas semanas.\n\nSaludos cordiales,\nSistema de Notificaciones`;
                    notificationType = 'warning'; // Solo advertencia
                } else {
                    message = `Estimado/a ${nombre},\n\nLe informamos que su porcentaje de asistencia es de ${porcentaje}%, lo cual es excelente. ¡Siga así!\n\nSaludos cordiales,\nSistema de Notificaciones`;
                    notificationType = 'success'; // Solo éxito
                }
            } else {
                // Para otras plantillas
                const templates = {
                    'warning': `Estimado/a ${nombre},\n\nEsta es una notificación de advertencia referente a [""]. Por favor, revise la situación y tome las acciones necesarias.\n\nSaludos cordiales,\nSistema de Notificaciones`,
                    'urgent': `Estimado/a ${nombre},\n\nEsta es una notificación urgente referente a [""]. Por favor, tome acción inmediata.\n\nSaludos cordiales,\nSistema de Notificaciones`,
                    'good': `Estimado/a ${nombre},\n\n¡Felicitaciones! Su rendimiento y asistencia son excelentes. Siga así.\n\nSaludos cordiales,\nSistema de Notificaciones`
                };
                message = templates[messageTemplate] || '';
            }

            // Asignar el mensaje generado
            document.querySelector('textarea[name="message"]').value = message;

            // Ajustar las opciones del select de notificación según el tipo
            const notificationOptions = document.getElementById('notificationType').querySelectorAll('option');
            notificationOptions.forEach(option => {
                // Mostrar solo las opciones correspondientes según el porcentaje de asistencia
                if (porcentaje < 50) {
                    option.style.display = (option.value === 'urgent' || option.value === 'warning') ? 'block' : 'none';
                } else if (porcentaje < 75) {
                    option.style.display = (option.value === 'warning') ? 'block' : 'none';
                } else {
                    option.style.display = (option.value === 'success') ? 'block' : 'none';
                }
            });
        }

        document.getElementById('sendNotification').addEventListener('click', function (event) {
            event.preventDefault(); // Prevenir el comportamiento por defecto del formulario

            const button = this; // Referencia al botón de enviar
            button.disabled = true; // Desactivar el botón al hacer clic

            const recipientType = document.getElementById('recipientType').value;
            let recipients = []; // Array para almacenar destinatarios

            // Obtener el destinatario según el tipo seleccionado
            if (recipientType === 'custom') {
                const customEmail = document.getElementById('customEmail').value;
                if (!customEmail) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Por favor, ingrese un correo personalizado.' });
                    button.disabled = false; // Rehabilitar el botón si hay un error
                    return;
                }
                recipients.push(customEmail);
            } else if (recipientType === 'saved') {
                const studentSelect = document.getElementById('studentSelect');
                const selectedStudentEmail = studentSelect.value;
                if (!selectedStudentEmail) {
                    Swal.fire({ icon: 'error', title: 'Error', text: 'Por favor, seleccione un estudiante.' });
                    button.disabled = false;// Rehabilitar el botón si hay un error
                    return;
                }
                recipients.push(selectedStudentEmail);
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Por favor, seleccione el tipo de destinatario.' });
                button.disabled = false;
                return;
            }

            const emailForm = document.getElementById('emailForm');
            const formData = new FormData(emailForm);
            formData.append('recipients', recipients.join(','));

            const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'), {
                backdrop: 'static',
                keyboard: false
            });
            loadingModal.show();

            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
                .then(response => response.text())
                .then(result => {
                    loadingModal.hide();
                    if (result.includes('success')) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: 'Notificación enviada correctamente'
                        }).then(() => location.reload());
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un problema al enviar la notificación: ' + result
                        });
                        button.disabled = false; // Rehabilitar el botón si hay un erro
                    }
                })
                .catch(error => {
                    loadingModal.hide();
                    console.error("Error al enviar el correo:", error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al enviar la notificación'
                    });
                    button.disabled = false;
                });
        });


        // Filtros
        document.getElementById('toggleFilters').addEventListener('click', function() {
            document.querySelector('.filters').classList.toggle('d-none');
        });

    </script>

<div id="loadingModal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center">
            <div class="modal-body">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-3">Enviando correo...</p>
            </div>
        </div>
    </div>
</div>
</body>
</html>