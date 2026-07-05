<?php
// db_config.php - SECURE (AFTER FOLDER)
$host = '127.0.0.1';
$db   = 'medic_vault_db'; // Change this to your actual database name
$user = 'root';         // Change if you use a different XAMPP user
$pass = '';             // Change if your XAMPP has a password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch arrays by default
    PDO::ATTR_EMULATE_PREPARES   => false,                  // CRITICAL: Disables emulation for real security
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Fails safely without leaking database details to the screen
    die("Database connection failed securely.");
}
?>