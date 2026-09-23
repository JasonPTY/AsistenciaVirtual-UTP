<?php

class Auth
{
    public static function csrfToken(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function verifyCsrf(?string $token): void
    {
        if (!$token || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
            http_response_code(419);
            exit('Solicitud no válida.');
        }
    }

    public static function check()
    {
        if (!isset($_SESSION['cedula'], $_SESSION['id_tipoUsuario'])) {
            header("Location: /AsistenciaVirtual-UTP/View/login.php");
            exit();
        }
    }

    public static function checkRole(int|array $roles): void
    {
        self::check();
        $allowedRoles = is_array($roles) ? $roles : [$roles];

        if (!in_array((int) $_SESSION['id_tipoUsuario'], $allowedRoles, true)) {
            http_response_code(403);
            exit('Acceso no autorizado.');
        }
    }

    public static function user()
    {
        return $_SESSION ?? null;
    }

    public static function id()
    {
        return $_SESSION['cedula'] ?? null;
    }
}