<?php
// includes/config.php
session_start();
ob_start();

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'tmth_db');
define('DB_USER', 'root');
define('DB_PASS', '');

// Site Configuration
define('SITE_NAME', 'TrueManTrueHelp');
define('SITE_URL', 'http://localhost/tmth');

// Create database connection
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}



// Include functions
require_once 'functions.php';
?>