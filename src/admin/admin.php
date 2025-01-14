<?php
// Include the database configuration
require_once 'config.php';

define('TELEGRAM_API_URL', 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/');

// Helper function to send messages
function sendMessage($chatId, $message) {
    $url = TELEGRAM_API_URL . "sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message);
    file_get_contents($url);
}

// Admin ID (replace with the actual admin user ID)
define('ADMIN_ID', 'YOUR_ADMIN_USER_ID'); // Replace with your admin's Telegram chat ID

// Get the incoming updates from Telegram
$update = file_get_contents("php://input");
$update = json_decode($update, TRUE);

if (isset($update['message'])) {
    $chatId = $update['message']['chat']['id'];
    $text = $update['message']['text'];

    // Only allow the admin to interact with this section
    if ($chatId != ADMIN_ID) {
        sendMessage($chatId, "You are not authorized to access the admin panel.");
        exit();
    }

    // /approve command - approve a pending traveler
    if (preg_match('/^\/approve (\d+)$/', $text, $matches)) {
        // Admin approves a specific traveler
        $telegramId = $matches[1];
        // Handle approval logic as in the previous code
    }

    // /export command - export all traveler data to CSV
    if ($text == '/export') {
        // Export logic as in the previous code
    }

    // /notify command - send notification to all travelers
    if (preg_match('/^\/notify (.+)$/', $text, $matches)) {
        // Admin sends notification
        $message = $matches[1];
        // Send the message to all confirmed travelers
    }

    // /due command - show travelers with due dates
    if ($text == '/due') {
        // Show due dates logic as in the previous code
    }

    // /help command - show available admin commands
    if ($text == '/help') {
        sendMessage($chatId, "Admin Commands:\n\n/approve <telegram_id> - Approve a pending traveler\n/export - Export traveler data to CSV\n/notify <message> - Send a notification to all travelers\n/due - Show travelers with due dates\n/help - Show this message");
    }
}
?>
