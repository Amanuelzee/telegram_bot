<?php
require_once '../src/config.php';

define('TELEGRAM_API_URL', 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/');

// Helper function to send messages
function sendMessage($chatId, $message) {
    $url = TELEGRAM_API_URL . "sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message);
    file_get_contents($url);
}

// Process incoming messages
$update = file_get_contents("php://input");
$update = json_decode($update, TRUE);

if (isset($update['message'])) {
    $chatId = $update['message']['chat']['id'];
    $text = $update['message']['text'];

    // /start command - welcome message
    if ($text == '/start') {
        sendMessage($chatId, "Welcome to the Spiritual Travelers Registration Bot!\n\nUse /register to start your registration process.");
    }

    // /register command - start registration
    if ($text == '/register') {
        sendMessage($chatId, "Please provide the following information to complete your registration:\n\n1. Full Name\n2. Phone Number\n3. Address\n4. Telegram Username\n5. Transaction Number");
        // You will need to implement logic to capture and store this data.
    }

    // /status command - check the traveler's registration status
    if ($text == '/status') {
        $stmt = $pdo->prepare("SELECT * FROM travelers WHERE telegram_id = :telegram_id");
        $stmt->execute(['telegram_id' => $chatId]);
        $traveler = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($traveler) {
            $statusMessage = "Registration Status:\n";
            $statusMessage .= "Name: " . $traveler['full_name'] . "\n";
            $statusMessage .= "Registration Number: " . $traveler['registration_number'] . "\n";
            $statusMessage .= "Car Assigned: " . $traveler['assign_car'] . "\n";
            $statusMessage .= "Status: " . $traveler['status'];

            sendMessage($chatId, $statusMessage);
        } else {
            sendMessage($chatId, "You are not yet registered. Use /register to start the registration process.");
        }
    }

    // /help command - show available commands to the user
    if ($text == '/help') {
        sendMessage($chatId, "Available commands:\n\n/start - Start the bot\n/register - Begin your registration process\n/status - Check your registration status\n/help - Show this message");
    }
}
?>
