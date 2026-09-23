<?php

require_once __DIR__ . '/../../../config.php';

class Database
{
    private static ?mysqli $connection = null;

    public static function getConnection(): mysqli
    {
        if (self::$connection === null) {
            global $conn;
            self::$connection = $conn;
        }

        return self::$connection;
    }
}