<?php
// Database configuration
$host = 'localhost';
$dbname = 'dvla_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Don't output anything here - let the calling script handle the error
    throw new Exception('Database connection failed: ' . $e->getMessage());
}
?>