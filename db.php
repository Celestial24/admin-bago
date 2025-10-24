<?php
// db.php - shared database config and helper
// Do NOT call session_start() here; pages should start session themselves.

declare(strict_types=1);

function get_pdo(): PDO {
    static $pdo = null;
    if ($pdo instanceof PDO) return $pdo;

    $host = '127.0.0.1';
    $db   = 'admin';
    $user = 'root';
    $pass = '';
    $charset = 'utf8mb4';

//remote server
/*
    $host = 'localhost';
    $db   = 'admin_capstoneadmin';
    $user = 'admin_dbadmin3';
    $pass = '12345';
    $charset = 'utf8mb4';
*/

    $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    $pdo = new PDO($dsn, $user, $pass, $options);
    return $pdo;
}