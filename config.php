<?php

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

define("urlsite", "http://localhost/AsistenciaVirtual-UTP/");
define("ACADEMIC_PERIOD", getAcademicPeriod());

function getAcademicPeriod(?DateTimeInterface $date = null): string
{
    $date = $date ?? new DateTimeImmutable('now');
    $month = (int) $date->format('n');
    $year = $date->format('Y');

    if ($month <= 2) {
        return "Verano {$year}";
    }

    if ($month <= 6) {
        return "I Semestre {$year}";
    }

    return "II Semestre {$year}";
}


// DB
$conn = new mysqli(
    $_ENV['DB_HOST'],
    $_ENV['DB_USER'],
    $_ENV['DB_PASS'],
    $_ENV['DB_NAME'],
    (int) $_ENV['DB_PORT']
);

if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}


// SMTP
define('SMTP_HOST', $_ENV['SMTP_HOST']);
define('SMTP_PORT', $_ENV['SMTP_PORT']);
define('SMTP_USER', $_ENV['SMTP_USER']);
define('SMTP_PASS', $_ENV['SMTP_PASS']);