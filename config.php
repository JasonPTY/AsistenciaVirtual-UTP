<?php
define("urlsite", "http://localhost/AsistenciaVirtual-UTP/");

function getAcademicPeriod(?DateTimeInterface $date = null): string
{
    $date  = $date ?? new DateTimeImmutable('now');
    $month = (int) $date->format('n');
    $year  = $date->format('Y');

    if ($month <= 2) {
        return "Verano {$year}";
    }

    if ($month <= 6) {
        return "I Semestre {$year}";
    }

    return "II Semestre {$year}";
}

define("ACADEMIC_PERIOD", getAcademicPeriod());

$host     = "localhost";
$username = "jasonpty";
$password = "jason2727";
$database = "loodle_system";
$port     = 3309;

$conn = new mysqli($host, $username, $password, $database, $port);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// SMTP
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'jasonarena.business@gmail.com');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
?>