<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: /Demo-Sas/View/login.php");
    exit();
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../app/modules/notification/notificationRepository.php';

$self   = basename(__FILE__);
$repo   = new NotificationRepository();
$cedula = $_SESSION['cedula'];

$idTipoUsuario   = $repo->getTipoUsuario($cedula);
$cedulaProfesor  = $idTipoUsuario === 3 ? $cedula : null;
$cedulaEstudiante = $idTipoUsuario === 2 ? $cedula : null;

// ── AJAX: enviar correo ────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'send_email') {

    if (empty($_POST['recipients'])) {
        echo 'error: Debe proporcionar al menos un destinatario.';
        exit;
    }

    $recipients = array_map('trim', explode(',', $_POST['recipients']));

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp-mail.outlook.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;     // definido en config.php
        $mail->Password   = SMTP_PASS;     // definido en config.php
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom(SMTP_USER, 'Sistema de Notificaciones');
        foreach ($recipients as $recipient) {
            $mail->addAddress($recipient);
        }

        $mail->isHTML(true);
        $mail->Subject = $_POST['subject'];
        $mail->Body    = $_POST['message'];

        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $mail->addAttachment($_FILES['attachment']['tmp_name'], $_FILES['attachment']['name']);
        }

        $mail->send();

        $tipo              = $_POST['type'];
        $asunto            = $_POST['subject'];
        $mensaje           = $_POST['message'];
        $urgente           = isset($_POST['urgent']) ? 1 : 0;
        $fechaEnvio        = date('Y-m-d H:i:s');
        $correoDestinatario = implode(', ', $recipients);

        $idNotificacion = $repo->insertNotificacion(
            $tipo, $asunto, $mensaje, $urgente,
            $fechaEnvio, 'enviado', $correoDestinatario, $cedulaProfesor
        );

        foreach ($recipients as $recipient) {
            $cedulaDest = $repo->getCedulaByCorreo($recipient);
            if ($cedulaDest) {
                $repo->insertNotificacionUsuario(
                    $idNotificacion,
                    $cedulaProfesor,
                    $cedulaDest,
                    date('Y-m-d H:i:s')
                );
            } else {
                error_log("No se encontró cédula para: $recipient");
            }
        }

        echo 'success';

    } catch (Exception $e) {
        error_log("Error PHPMailer: " . $e->getMessage());
        echo 'error: ' . $mail->ErrorInfo;
    }
    exit;
}

// ── Datos para la vista ────────────────────────────────────────────────────────
$notifications  = [];
$estudiantes    = [];

if ($idTipoUsuario === 2) {
    $correoEstudiante = $repo->getCorreoByCedula($cedula);
    $notifications    = $repo->getNotificacionesEstudiante($correoEstudiante);
} elseif ($idTipoUsuario === 3) {
    $notifications = $repo->getNotificacionesProfesor($cedulaProfesor);
    $estudiantes   = $repo->getEstudiantesByProfesor($cedulaProfesor);
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
        .notification-item { padding: 15px; margin-bottom: 15px; border: 1px solid #ddd; border-radius: 8px; background-color: #fff; }
        .notification-item.urgent { border-left: 5px solid #dc3545; }
        .notification-header { display: flex; justify-content: space-between; margin-bottom: 10px; }
        .timeline { padding: 20px; }
    </style>
</head>
<body>
<main class="main-content">
    <div class="container-fluid">

        <!-- Filtros (solo profesor) -->
        <?php if ($idTipoUsuario === 3): ?>
        <div class="filters mb-4 d-none">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-filter me-2"></i>Tipo de Notificación</label>
                    <select class="form-select" id="notificationType">
                        <option value="all">Todas</option>
                        <option value="urgent">Urgentes</option>
                        <option value="warning">Advertencias</option>
                        <option value="success">Éxitos</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-eye me-2"></i>Estado</label>
                    <select class="form-select" id="readStatus">
                        <option value="all">Todos</option>
                        <option value="unread">No leídas</option>
                        <option value="read">Leídas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-calendar-alt me-2"></i>Fecha Desde</label>
                    <input type="date" class="form-control" id="dateFrom">
                </div>
                <div class="col-md-3">
                    <label class="form-label"><i class="fas fa-calendar-check me-2"></i>Fecha Hasta</label>
                    <input type="date" class="form-control" id="dateTo">
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Card principal -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="fas fa-list me-2"></i>
                    <?= $idTipoUsuario === 2 ? 'Notificaciones Recibidas' : 'Notificaciones Enviadas' ?>
                </h5>
                <?php if ($idTipoUsuario === 3): ?>
                <div>
                    <button class="btn btn-secondary me-2" id="toggleFilters">
                        <i class="fas fa-filter me-2"></i>Filtros
                    </button>
                    <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#emailModal">
                        <i class="fas fa-envelope me-2"></i>Nuevo correo
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <!-- Modal envío (solo profesor) -->
            <?php if ($idTipoUsuario === 3): ?>
            <div class="modal fade" id="emailModal" tabindex="-1">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title"><i class="fas fa-envelope me-2"></i>Enviar Notificación</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                            <div id="emailForm">
                                <input type="hidden" name="action" value="send_email">

                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-at me-2"></i>Destinatarios</label>
                                    <select class="form-select" id="recipientType" name="recipientType" onchange="toggleRecipients()">
                                        <option value="all">Seleccionar destinatario</option>
                                        <option value="custom">Personalizado</option>
                                        <option value="saved">Estudiantes</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="customEmailDiv" style="display:none;">
                                    <label class="form-label"><i class="fas fa-envelope me-2"></i>Correo Personalizado</label>
                                    <input type="email" class="form-control" id="customEmailInput" placeholder="Escribe el correo">
                                </div>

                                <div class="mb-3" id="studentSelectDiv" style="display:none;">
                                    <label class="form-label"><i class="fas fa-users me-2"></i>Seleccionar Estudiante</label>
                                    <select class="form-select" id="studentSelect" onchange="setCustomEmail()">
                                        <option value="">Seleccione un estudiante</option>
                                        <?php foreach ($estudiantes as $est): ?>
                                            <option value="<?= htmlspecialchars($est['correo'], ENT_QUOTES) ?>"
                                                    data-nombre="<?= htmlspecialchars($est['nombre'], ENT_QUOTES) ?>"
                                                    data-porcentaje="<?= htmlspecialchars($est['porcentaje_asistencia'], ENT_QUOTES) ?>">
                                                <?= htmlspecialchars($est['correo'], ENT_QUOTES) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-tag me-2"></i>Asunto</label>
                                    <input type="text" class="form-control" id="emailSubject">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-file-alt me-2"></i>Plantilla</label>
                                    <select class="form-select" id="emailTemplate" onchange="applyTemplate()">
                                        <option value="">Seleccione una plantilla</option>
                                        <option value="attendance">Asistencia</option>
                                        <option value="warning">Advertencia</option>
                                        <option value="urgent">Urgente</option>
                                        <option value="good">Bien hecho</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-edit me-2"></i>Mensaje</label>
                                    <textarea class="form-control" id="emailMessage" rows="5"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-paperclip me-2"></i>Adjuntar archivo</label>
                                    <input type="file" class="form-control" id="emailAttachment">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label"><i class="fas fa-exclamation-circle me-2"></i>Tipo</label>
                                    <select class="form-select" id="emailType">
                                        <option value="Normal">Normal</option>
                                        <option value="Warning">Advertencia</option>
                                        <option value="Urgent">Urgente</option>
                                    </select>
                                </div>

                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="emailUrgent">
                                    <label class="form-check-label" for="emailUrgent">Marcar como urgente</label>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                    <button type="button" class="btn btn-primary" id="sendNotification">Enviar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Lista notificaciones -->
            <div class="card-body">
                <div class="timeline">
                    <?php if (empty($notifications)): ?>
                        <p>No tienes notificaciones.</p>
                    <?php else: ?>
                        <?php foreach ($notifications as $notif): ?>
                            <div class="notification-item <?= $notif['es_urgente'] ? 'urgent' : '' ?>">
                                <div class="notification-header">
                                    <span class="badge bg-<?= $notif['es_urgente'] ? 'danger' : 'primary' ?>">
                                        <?= htmlspecialchars($notif['tipo'], ENT_QUOTES) ?>
                                    </span>
                                    <span class="text-muted">
                                        <?= date('d/m/Y H:i', strtotime($notif['fecha_envio'])) ?>
                                    </span>
                                </div>
                                <div class="notification-content">
                                    <h5><?= htmlspecialchars($notif['asunto'], ENT_QUOTES) ?></h5>
                                    <p><?= nl2br(htmlspecialchars($notif['mensaje'], ENT_QUOTES)) ?></p>
                                    <?php if ($idTipoUsuario === 2): ?>
                                        <?php $correoRemitente = $repo->getCorreoByCedula($notif['cedula_profesor']); ?>
                                        <p><strong>Enviado por: </strong><?= htmlspecialchars($correoRemitente, ENT_QUOTES) ?></p>
                                    <?php elseif ($idTipoUsuario === 3): ?>
                                        <p><strong>Enviado a: </strong><?= htmlspecialchars($notif['correo_destinatario'], ENT_QUOTES) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>
</main>

<!-- Modal de carga -->
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

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.7.32/sweetalert2.min.js"></script>
<script>
const SELF = '<?= $self ?>';

function toggleRecipients() {
    const type = document.getElementById('recipientType').value;
    document.getElementById('studentSelectDiv').style.display = type === 'saved'   ? 'block' : 'none';
    document.getElementById('customEmailDiv').style.display   = type === 'custom'  ? 'block' : 'none';
}

function setCustomEmail() {
    document.getElementById('customEmailInput').value = document.getElementById('studentSelect').value;
}

function applyTemplate() {
    const tpl      = document.getElementById('emailTemplate').value;
    const sel      = document.getElementById('studentSelect');
    const opt      = sel.options[sel.selectedIndex];
    const nombre   = opt ? opt.dataset.nombre    : '';
    const pct      = opt ? parseFloat(opt.dataset.porcentaje) : 0;

    const templates = {
        attendance: pct < 50
            ? `Estimado/a ${nombre},\n\nSu porcentaje de asistencia es ${pct}%, inferior al 50%. Por favor tome medidas.\n\nSaludos,\nSistema de Notificaciones`
            : pct < 75
            ? `Estimado/a ${nombre},\n\nSu porcentaje de asistencia es ${pct}%, aceptable pero mejorable.\n\nSaludos,\nSistema de Notificaciones`
            : `Estimado/a ${nombre},\n\nSu porcentaje de asistencia es ${pct}%, ¡excelente!\n\nSaludos,\nSistema de Notificaciones`,
        warning: `Estimado/a ${nombre},\n\nAdvertencia referente a [""]. Por favor tome acción.\n\nSaludos,\nSistema de Notificaciones`,
        urgent:  `Estimado/a ${nombre},\n\nNotificación urgente referente a [""]. Acción inmediata requerida.\n\nSaludos,\nSistema de Notificaciones`,
        good:    `Estimado/a ${nombre},\n\n¡Felicitaciones! Su rendimiento es excelente.\n\nSaludos,\nSistema de Notificaciones`,
    };

    document.getElementById('emailMessage').value = templates[tpl] ?? '';
}

document.getElementById('sendNotification')?.addEventListener('click', function () {
    const btn           = this;
    const recipientType = document.getElementById('recipientType').value;
    let   recipients    = [];

    if (recipientType === 'custom') {
        const email = document.getElementById('customEmailInput').value.trim();
        if (!email) { Swal.fire({ icon: 'error', title: 'Error', text: 'Ingrese un correo personalizado.' }); return; }
        recipients.push(email);
    } else if (recipientType === 'saved') {
        const email = document.getElementById('studentSelect').value;
        if (!email) { Swal.fire({ icon: 'error', title: 'Error', text: 'Seleccione un estudiante.' }); return; }
        recipients.push(email);
    } else {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Seleccione el tipo de destinatario.' });
        return;
    }

    btn.disabled = true;

    const formData = new FormData();
    formData.append('action',      'send_email');
    formData.append('subject',     document.getElementById('emailSubject').value);
    formData.append('message',     document.getElementById('emailMessage').value);
    formData.append('type',        document.getElementById('emailType').value);
    formData.append('recipients',  recipients.join(','));
    if (document.getElementById('emailUrgent').checked) formData.append('urgent', '1');

    const attachment = document.getElementById('emailAttachment').files[0];
    if (attachment) formData.append('attachment', attachment);

    const loadingModal = new bootstrap.Modal(document.getElementById('loadingModal'), { backdrop: 'static', keyboard: false });
    loadingModal.show();

    fetch(SELF, { method: 'POST', body: formData })
        .then(r => r.text())
        .then(result => {
            loadingModal.hide();
            if (result.includes('success')) {
                Swal.fire({ icon: 'success', title: '¡Éxito!', text: 'Notificación enviada correctamente' })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: 'Problema al enviar: ' + result });
                btn.disabled = false;
            }
        })
        .catch(() => {
            loadingModal.hide();
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error de conexión.' });
            btn.disabled = false;
        });
});

document.getElementById('toggleFilters')?.addEventListener('click', function () {
    document.querySelector('.filters').classList.toggle('d-none');
});
</script>
</body>
</html>