<?php

class Auth
{
    public static function check()
    {
        if (!isset($_SESSION['cedula'])) {
            header("Location: /Demo-sas/View/login.php");
            exit();
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