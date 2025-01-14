<?php
require_once '../src/config.php'; // Include your configuration file

// Get the incoming webhook payload (JSON data sent by Telegram)
$content = file_get_contents("php://input");
$update = json_decode($content, true);

// Check if the update is valid
if (isset($update["message"])) {
    $chatId = $update["message"]["chat"]["id"];
    $messageText = $update["message"]["text"];
    $userName = $update["message"]["from"]["username"];
    
    // Call function to handle messages
    handleMessage($chatId, $messageText, $userName);
}

// Function to handle incoming messages
function handleMessage($chatId, $messageText, $userName) {
    // Respond to specific commands or messages
    if ($messageText == "/start") {
        sendMessage($chatId, "Welcome to the Spiritual Travelers Bot! How can I assist you today?");
    }
    elseif ($messageText == "/register") {
        sendMessage($chatId, "To begin the registration process, please provide your full name.");
        // Here you would call a function to collect more information for registration.
    }
    else {
        sendMessage($chatId, "I'm sorry, I didn't understand that command. Please use /start or /register.");
    }
}

// Function to send a message to the user
function sendMessage($chatId, $message) {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message);
    file_get_contents($url); // Send the request to Telegram's API
}

// Function to set the webhook with Telegram
function setWebhook() {
    $url = "https://api.telegram.org/bot" . TELEGRAM_BOT_TOKEN . "/setWebhook?url=" . "http://your-server-domain.com/path-to-webhook/webhook.php";
    file_get_contents($url);
}

// Uncomment the line below to set the webhook (once only)
// setWebhook();

?>
