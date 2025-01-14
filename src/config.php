<?php
// Database Configuration
define('DB_HOST', 'localhost');       // Hostname for MySQL
define('DB_NAME', 'finotehiwot');     // Database name
define('DB_USER', 'root');            // Database username (update if different)
define('DB_PASSWORD', '');            // Database password (update if different)

// Telegram Bot API Configuration
define('TELEGRAM_BOT_TOKEN', '7695126763:AAHiG9FJ0t8SQXBGrcYlvqaaFtT_P_7aLBc'); // Your bot's token

// Establish Database Connection
try {
    // Create a PDO instance to connect to the database
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    
    // Set the PDO error mode to exception for better error handling
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Optionally, set the character set to UTF8
    $pdo->exec("SET NAMES 'utf8'");
} catch (PDOException $exception) {
    // If an error occurs, output the error message and stop execution
    die("Connection failed: " . $exception->getMessage());
}

// You can use this $pdo variable to interact with your database
?>
