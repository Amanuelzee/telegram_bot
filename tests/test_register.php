<?php
error_reporting(E_ALL); // Report all errors
ini_set('display_errors', 1); // Display errors on the page

require_once '../src/config.php'; // Include your configuration file

// Example data (replace these with dynamic user input in a real scenario)
$chatId = 'YOUR_ACTUAL_CHAT_ID'; // Replace with the actual chat ID from Telegram
$fullName = "John Doe";
$phoneNumber = "+1234567890";
$address = "123 Main St, City, Country";
$telegramUsername = "@john_doe";
$transactionNumber = "TXN123456789";

// Step 1: Send the first message to start registration
sendMessage($chatId, "Hello, $fullName! Let's start your registration process.");

// Step 2: Send the collected information step by step
sendMessage($chatId, "Your name is: $fullName");
sendMessage($chatId, "Your phone number is: $phoneNumber");
sendMessage($chatId, "Your address is: $address");
sendMessage($chatId, "Your Telegram username is: $telegramUsername");
sendMessage($chatId, "Your transaction number is: $transactionNumber");

// Step 3: Send a completion message after the registration process
sendMessage($chatId, "Thank you! Your registration is complete. We'll send you a confirmation soon.");

// Function to send a message to the user
function sendMessage($chatId, $message) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message);
    
    // Initialize cURL
    $ch = curl_init();
    
    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    
    // Execute cURL request
    $response = curl_exec($ch);
    
    // Check for errors
    if ($response === false) {
        die('Error sending message: ' . curl_error($ch));
    }
    
    // Close cURL session
    curl_close($ch);
}
?>
