<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'nexora_db');
define('DB_USER', 'root');       // XAMPP default
define('DB_PASS', '');           // XAMPP default (blank)

try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]
    );
} catch (PDOException $e) {
    die('<div style="font-family:sans-serif;padding:2rem;background:#1a0000;color:#f47;border-radius:8px;margin:2rem;">
        <h2>⚠️ Database Connection Failed</h2>
        <p>Error: ' . htmlspecialchars($e->getMessage()) . '</p>
        <p style="color:#aaa;margin-top:1rem;">Make sure XAMPP MySQL is running and you have imported <strong>nexora_db.sql</strong> in phpMyAdmin.</p>
    </div>');
}
?>
