<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: /Demo-Sas/View/login.php");
    exit();
}

require_once __DIR__ . '/../../app/modules/history/historyRepository.php';

$repo  = new HistoryRepository();
$self  = basename(__FILE__);

/* ================================================================
   AJAX: GET ?get_detalles=1&id_asistencia=X
================================================================ */
if (isset($_GET['get_detalles'])) {
    $idAsistencia = (int) $_GET['id_asistencia'];
    $detalles     = $repo->getDetallesAsistencia($idAsistencia);

    if (empty($detalles)) {
        echo '<p class="text-muted text-center py-3">Sin registros de estudiantes.</p>';
        exit();
    }

    echo '<table class="table table-sm table-hover mb-0">';
    echo '<thead><tr>
            <th>Cédula</th>
            <th>Estudiante</th>
            <th>Estado</th>
          </tr></thead><tbody>';

    foreach ($detalles as $row) {
        $estadoClass = match($row['estado']) {
            'Presente' => 'status-present',
            'Ausente'  => 'status-absent',
            'Tardanza' => 'status-late',
            default    => ''
        };
        echo '<tr>';
        echo '<td>' . htmlspecialchars($row['cedula'])          . '</td>';
        echo '<td>' . htmlspecialchars($row['nombre_completo']) . '</td>';
        echo '<td><span class="attendance-status ' . $estadoClass . '">'
             . htmlspecialchars($row['estado']) . '</span></td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
    exit();
}

/* ================================================================
   AJAX: POST editar_asistencia
================================================================ */
if (isset($_POST['editar_asistencia'])) {
    header('Content-Type: application/json');

    $idAsistencia = (int)   $_POST['id_asistencia'];
    $cedula       = trim(   $_POST['cedula']       ?? '');
    $nuevoEstado  = trim(   $_POST['nuevo_estado'] ?? '');

    $ok = $repo->editarAsistencia($idAsistencia, $cedula, $nuevoEstado);

    echo json_encode($ok
        ? ['success' => true,  'mensaje' => 'Asistencia actualizada correctamente.']
        : ['success' => false, 'error'   => 'No se pudo actualizar. Verifica los datos.']
    );
    exit();
}

/* ================================================================
   AJAX: POST eliminar_asistencia
================================================================ */
if (isset($_POST['eliminar_asistencia'])) {
    header('Content-Type: application/json');

    $idAsistencia = (int) $_POST['id_asistencia'];
    $ok           = $repo->eliminarAsistencia($idAsistencia);

    echo json_encode($ok
        ? ['success' => true,  'mensaje' => 'Registro eliminado correctamente.']
        : ['success' => false, 'mensaje' => 'No se pudo eliminar el registro.']
    );
    exit();
}

/* ================================================================
   Exportar CSV
================================================================ */
if (isset($_GET['exportar']) && $_GET['exportar'] === 'excel') {
    $idAsistencia = (int) $_GET['id_asistencia'];
    $data         = $repo->getExportData($idAsistencia);

    if (!$data) {
        die('Registro no encontrado.');
    }

    $fecha  = $data['header']['fecha'];
    $hora   = $data['header']['hora'];
    $curso  = $data['header']['nombre_curso'];
    $nombre = "asistencia_{$curso}_{$fecha}.csv";

    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $nombre . '"');
    header('Pragma: no-cache');

    $out = fopen('php://output', 'w');
    fputs($out, "\xEF\xBB\xBF"); // BOM para Excel

    fputcsv($out, ['Curso', $curso]);
    fputcsv($out, ['Fecha', $fecha]);
    fputcsv($out, ['Hora',  $hora]);
    fputcsv($out, []);
    fputcsv($out, ['Cédula', 'Nombre Completo', 'Estado']);

    foreach ($data['rows'] as $row) {
        fputcsv($out, [$row['cedula'], $row['nombre_completo'], $row['estado']]);
    }

    fclose($out);
    exit();
}

/* ================================================================
   Vista principal
================================================================ */
$filtro_curso        = $_GET['curso']        ?? null;
$filtro_fecha_inicio = $_GET['fecha_inicio'] ?? null;
$filtro_fecha_fin    = $_GET['fecha_fin']    ?? null;

$cursos = $repo->getCursos($_SESSION['cedula']);
$clases = $repo->getHistorialClases(
    $_SESSION['cedula'],
    $filtro_curso,
    $filtro_fecha_inicio,
    $filtro_fecha_fin
);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Clases — SAS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background: #f4f6f9; }

        /* ── Filtros ── */
        .filters-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            padding: 20px 24px;
            margin-bottom: 24px;
        }

        /* ── Tarjetas de clase ── */
        .class-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,.08);
            padding: 20px 24px;
            margin-bottom: 16px;
            transition: box-shadow .2s;
        }
        .class-card:hover { box-shadow: 0 4px 16px rgba(0,0,0,.12); }

        .class-card h5 { font-weight: 600; color: #1a1a2e; margin-bottom: 6px; }

        .class-tag {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .8em;
            font-weight: 500;
            margin-right: 6px;
        }

        /* ── Detalles de asistencia ── */
        .attendance-details {
            display: none;
            margin-top: 16px;
            border-top: 1px solid #eee;
            padding-top: 16px;
        }

        /* ── Estados de asistencia ── */
        .attendance-status {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: .82em;
            font-weight: 500;
        }
        .status-present { background: #d4edda; color: #155724; }
        .status-absent  { background: #f8d7da; color: #721c24; }
        .status-late    { background: #fff3cd; color: #856404; }

        /* ── Modo edición ── */
        .edit-mode { background: #f8f9fa; }
        .edit-mode td { vertical-align: middle; }

        /* ── Botones de acción compactos ── */
        .action-buttons .btn { font-size: .82em; }

        /* ── Responsive ── */
        @media (max-width: 767px) {
            .class-card { padding: 15px; }
            .action-buttons { display: flex; flex-wrap: wrap; gap: 6px; margin-top: 14px; }
            .action-buttons .btn { flex: 1 1 calc(50% - 3px); }
        }
    </style>
</head>
<body>
<main class="container py-4">

    <!-- Filtros -->
    <div class="filters-card">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label fw-semibold">Cursos</label>
                <select name="curso" class="form-select">
                    <option value="">Todos los cursos</option>
                    <?php foreach ($cursos as $curso): ?>
                        <option value="<?= $curso['id_curso'] ?>"
                            <?= ($filtro_curso == $curso['id_curso']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($curso['nombre_curso']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Fecha inicio</label>
                <input type="date" name="fecha_inicio" class="form-control"
                       value="<?= htmlspecialchars($filtro_fecha_inicio ?? '') ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold">Fecha fin</label>
                <input type="date" name="fecha_fin" class="form-control"
                       value="<?= htmlspecialchars($filtro_fecha_fin ?? '') ?>">
            </div>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-2"></i>Filtrar
                </button>
            </div>
        </form>
    </div>

    <!-- Sin resultados -->
    <?php if (empty($clases)): ?>
        <div class="alert alert-info d-flex align-items-center gap-2">
            <i class="fas fa-info-circle"></i>
            No se encontraron registros con los filtros seleccionados.
        </div>
    <?php endif; ?>

    <!-- Tarjetas de clase -->
    <?php foreach ($clases as $clase):
        $total     = (int) $clase['total_estudiantes'];
        $presentes = (int) $clase['total_presentes'];
        $pct       = $total > 0 ? round($presentes / $total * 100) : 0;
        $pctClass  = $pct >= 70 ? 'success' : 'warning';
        $id        = (int) $clase['id_asistencia'];
    ?>
    <div class="class-card">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">

            <div>
                <h5><?= htmlspecialchars($clase['nombre_curso']) ?></h5>
                <div class="text-muted mb-2" style="font-size:.9em;">
                    <i class="far fa-calendar-alt me-1"></i>
                    <?= date('d \d\e F, Y', strtotime($clase['fecha'])) ?>
                    <i class="far fa-clock ms-3 me-1"></i>
                    <?= date('g:i A', strtotime($clase['hora'])) ?>
                </div>
                <div>
                    <span class="class-tag bg-info text-white">
                        <i class="fas fa-users me-1"></i><?= $total ?> estudiantes
                    </span>
                    <span class="class-tag bg-<?= $pctClass ?> text-white">
                        <?= $pct ?>% asistencia
                    </span>
                </div>
            </div>

            <div class="action-buttons d-flex flex-wrap gap-2">
                <button class="btn btn-outline-primary btn-sm"
                        onclick="cargarDetalles(<?= $id ?>)">
                    <i class="fas fa-list-ul me-1"></i>Detalles
                </button>
                <a href="?exportar=excel&id_asistencia=<?= $id ?>"
                   class="btn btn-outline-success btn-sm">
                    <i class="fas fa-file-csv me-1"></i>CSV
                </a>
                <button class="btn btn-outline-warning btn-sm"
                        onclick="habilitarEdicion(<?= $id ?>)">
                    <i class="fas fa-pen me-1"></i>Editar
                </button>
                <button class="btn btn-outline-danger btn-sm"
                        onclick="confirmarEliminar(<?= $id ?>)">
                    <i class="fas fa-trash me-1"></i>Eliminar
                </button>
            </div>
        </div>

        <div id="detalles-<?= $id ?>" class="attendance-details"></div>
    </div>
    <?php endforeach; ?>

</main>

<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/js/bootstrap.bundle.min.js"></script>
<script>
    const SELF = '<?= $self ?>';

    // Preservar filtros activos para el auto-refresco
    const FILTROS_ACTIVOS = new URLSearchParams({
        <?php if ($filtro_curso):        ?>curso:        '<?= $filtro_curso ?>',        <?php endif; ?>
        <?php if ($filtro_fecha_inicio): ?>fecha_inicio: '<?= $filtro_fecha_inicio ?>', <?php endif; ?>
        <?php if ($filtro_fecha_fin):    ?>fecha_fin:    '<?= $filtro_fecha_fin ?>',    <?php endif; ?>
    }).toString();

    // ─── Helpers ────────────────────────────────────────────────────────────────

    function toggleDetalle(idAsistencia, div) {
        if (div.style.display === 'block') {
            div.style.display = 'none';
            return false; // indica que se ocultó
        }
        return true; // indica que hay que cargar
    }

    function mostrarCargando(div) {
        div.innerHTML     = '<p class="text-muted py-2"><i class="fas fa-spinner fa-spin me-2"></i>Cargando...</p>';
        div.style.display = 'block';
    }

    // ─── cargarDetalles ─────────────────────────────────────────────────────────

    function cargarDetalles(idAsistencia) {
        const div = document.getElementById(`detalles-${idAsistencia}`);

        if (!toggleDetalle(idAsistencia, div)) return;

        mostrarCargando(div);

        fetch(`${SELF}?get_detalles=1&id_asistencia=${idAsistencia}`)
            .then(r => {
                if (!r.ok) throw new Error(`HTTP ${r.status}`);
                return r.text();
            })
            .then(html => { div.innerHTML = html; })
            .catch(() => { div.innerHTML = '<p class="text-danger">Error al cargar los detalles.</p>'; });
    }

    // ─── habilitarEdicion ───────────────────────────────────────────────────────

    function habilitarEdicion(idAsistencia) {
        const div = document.getElementById(`detalles-${idAsistencia}`);

        const activar = () => {
            const tabla = div.querySelector('table');
            if (!tabla) {
                div.innerHTML = '<p class="text-danger">Carga los detalles primero.</p>';
                return;
            }

            tabla.querySelectorAll('tbody tr').forEach(fila => {
                if (fila.classList.contains('edit-mode')) return;

                const cedula       = fila.cells[0].textContent.trim();
                const estadoActual = fila.cells[2].querySelector('.attendance-status')?.textContent.trim() ?? 'Presente';
                const tdEstado     = fila.cells[2];

                const select = document.createElement('select');
                select.className = 'form-select form-select-sm';
                ['Presente', 'Ausente', 'Tardanza'].forEach(est => {
                    const opt        = document.createElement('option');
                    opt.value        = est;
                    opt.textContent  = est;
                    opt.selected     = est === estadoActual;
                    select.appendChild(opt);
                });

                const btn           = document.createElement('button');
                btn.className       = 'btn btn-sm btn-primary ms-2';
                btn.textContent     = 'Guardar';
                btn.onclick         = () => guardarCambios(idAsistencia, cedula, select.value, fila);

                tdEstado.innerHTML  = '';
                tdEstado.append(select, btn);
                fila.classList.add('edit-mode');
            });
        };

        if (div.style.display !== 'block') {
            mostrarCargando(div);
            fetch(`${SELF}?get_detalles=1&id_asistencia=${idAsistencia}`)
                .then(r => r.text())
                .then(html => { div.innerHTML = html; activar(); })
                .catch(() => { div.innerHTML = '<p class="text-danger">Error al cargar los detalles.</p>'; });
        } else {
            activar();
        }
    }

    // ─── guardarCambios ─────────────────────────────────────────────────────────

    function guardarCambios(idAsistencia, cedula, nuevoEstado, fila) {
        const body = new URLSearchParams({
            editar_asistencia: 1,
            id_asistencia:     idAsistencia,
            cedula,
            nuevo_estado:      nuevoEstado,
        });

        fetch(SELF, { method: 'POST', body })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    // Recargar detalles y mantener el panel abierto
                    fetch(`${SELF}?get_detalles=1&id_asistencia=${idAsistencia}`)
                        .then(r => r.text())
                        .then(html => {
                            const div           = document.getElementById(`detalles-${idAsistencia}`);
                            div.innerHTML       = html;
                            div.style.display   = 'block';
                        });
                } else {
                    alert('Error: ' + (data.error ?? 'No se pudo guardar el cambio.'));
                }
            })
            .catch(() => alert('Error de conexión al guardar cambios.'));
    }

    // ─── confirmarEliminar ──────────────────────────────────────────────────────

    function confirmarEliminar(idAsistencia) {
        if (!confirm('¿Eliminar este registro de asistencia? Esta acción no se puede deshacer.')) return;

        const body = new URLSearchParams({
            eliminar_asistencia: 1,
            id_asistencia:       idAsistencia,
        });

        fetch(SELF, { method: 'POST', body })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    const tarjeta = document.getElementById(`detalles-${idAsistencia}`)?.closest('.class-card');
                    if (tarjeta) {
                        tarjeta.style.transition = 'opacity .3s';
                        tarjeta.style.opacity    = '0';
                        setTimeout(() => tarjeta.remove(), 300);
                    }
                } else {
                    alert('Error: ' + (data.mensaje ?? 'No se pudo eliminar.'));
                }
            })
            .catch(() => alert('Error de conexión al eliminar.'));
    }

    // ─── Auto-refresco ──────────────────────────────────────────────────────────
    // Solo recarga si no hay detalles abiertos, y preserva los filtros activos

    setInterval(() => {
        const hayAbiertos = [...document.querySelectorAll('.attendance-details')]
            .some(d => d.style.display === 'block');

        if (!hayAbiertos) {
            const url = FILTROS_ACTIVOS ? `${SELF}?${FILTROS_ACTIVOS}` : SELF;
            location.replace(url);
        }
    }, 30_000);
</script>
</body>
</html>