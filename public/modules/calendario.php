<?php
session_start();
if (!isset($_SESSION['cedula'])) {
    header("Location: /Demo-Sas/View/login.php");
    exit();
}

require_once __DIR__ . '/../../app/modules/students/studentsRepository.php';

$self   = basename(__FILE__);
$repo   = new StudentsRepository();
$clases = $repo->getClasesByEstudiante($_SESSION['cedula']);

$mapDias = [
    'lunes'      => 1,
    'martes'     => 2,
    'miércoles'  => 3,
    'jueves'     => 4,
    'viernes'    => 5,
    'sábado'     => 6,
    'domingo'    => 0,
];

$eventos = array_map(function (array $clase) use ($mapDias): array {
    return [
        'title' => $clase['nombre_curso'],
        'dow'   => [$mapDias[strtolower($clase['dia_semana'])] ?? 1],
        'start' => $clase['hora_clase'],
        'end'   => date('H:i:s', strtotime($clase['hora_clase'] . ' +1 hour')),
        'color' => '#' . substr(md5($clase['id_curso']), 0, 6),
        'id'    => (int) $clase['id_curso'],
    ];
}, $clases);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horario de Clases</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.css" rel="stylesheet">
    <style>
        #calendar {
            max-width: 990px;
            margin: 20px auto;
            padding: 15px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .fc-event { margin: 4px 0; padding: 2px 5px; }
    </style>
</head>
<body>
<div id="calendar"></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/3.10.2/fullcalendar.min.js"></script>
<script>
const SELF   = '<?= $self ?>';
const eventos = <?= json_encode($eventos, JSON_UNESCAPED_UNICODE) ?>;

$(document).ready(function () {
    $('#calendar').fullCalendar({
        header: {
            left:   'prev,next today',
            center: 'title',
            right:  'month,agendaWeek,agendaDay'
        },
        defaultView:   'agendaWeek',
        firstDay:      1,
        minTime:       '07:00:00',
        maxTime:       '22:00:00',
        slotDuration:  '00:30:00',
        allDaySlot:    false,
        height:        'auto',
        events:        eventos,
        monthNames:      ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
        monthNamesShort: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
        dayNames:        ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'],
        dayNamesShort:   ['Dom','Lun','Mar','Mié','Jue','Vie','Sáb'],
        buttonText:      { today: 'Hoy', month: 'Mes', week: 'Semana', day: 'Día' }
    });
});
</script>
</body>
</html>