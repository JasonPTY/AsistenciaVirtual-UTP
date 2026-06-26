<?php

class Database
{
    private static ?mysqli $connection = null;

    public static function getConnection(): mysqli
    {
        if (self::$connection === null) {

            require_once __DIR__ . '/../../../config.php';

            self::$connection = $conn;
        }

        return self::$connection;
    }
}