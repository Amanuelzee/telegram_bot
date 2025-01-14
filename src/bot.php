<?php
// Include the database configuration
require_once 'config.php';

// Set the Telegram API URL
define('TELEGRAM_API_URL', 'https://api.telegram.org/bot' . TELEGRAM_BOT_TOKEN . '/');

// Helper function to send messages to Telegram users
function sendMessage($chatId, $message) {
    $url = TELEGRAM_API_URL . "sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message);
    file_get_contents($url);
}

// Get the incoming updates from Telegram
$update = file_get_contents("php://input");
$update = json_decode($update, TRUE);

// Extract necessary details from the update
if (isset($update['message'])) {
    $chatId = $update['message']['chat']['id'];
    $text = $update['message']['text'];
    $username = $update['message']['from']['username'];
    $firstName = $update['message']['from']['first_name'];
    $lastName = $update['message']['from']['last_name'];

    // Database connection
    global $pdo;

    // Check if the user exists in the database
    $stmt = $pdo->prepare("SELECT * FROM travelers WHERE telegram_id = :telegram_id");
    $stmt->execute(['telegram_id' => $chatId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // User does not exist, start registration
        sendMessage($chatId, "Hello $firstName! Let's get you registered.");
        sendMessage($chatId, "Please enter your full name:");
        
        // Initialize registration process
        $stmt = $pdo->prepare("INSERT INTO travelers (telegram_id, username, status) VALUES (:telegram_id, :username, :status)");
        $stmt->execute(['telegram_id' => $chatId, 'username' => $username, 'status' => 'Pending']);
    } else {
        // If user exists, show registration status
        sendMessage($chatId, "You are already registered with the status: " . $user['status']);
    }

    // Process user's registration step by step
    if ($text) {
        if ($user && $user['status'] === 'Pending') {
            // Collecting the missing details step by step

            if (empty($user['full_name'])) {
                // Step 1: Full name
                $stmt = $pdo->prepare("UPDATE travelers SET full_name = :full_name WHERE telegram_id = :telegram_id");
                $stmt->execute(['full_name' => $text, 'telegram_id' => $chatId]);
                sendMessage($chatId, "Thank you! Please enter your address:");
            } elseif (empty($user['address'])) {
                // Step 2: Address
                $stmt = $pdo->prepare("UPDATE travelers SET address = :address WHERE telegram_id = :telegram_id");
                $stmt->execute(['address' => $text, 'telegram_id' => $chatId]);
                sendMessage($chatId, "Thank you! Now, please enter your phone number:");
            } elseif (empty($user['phone_number'])) {
                // Step 3: Phone number
                $stmt = $pdo->prepare("UPDATE travelers SET phone_number = :phone_number WHERE telegram_id = :telegram_id");
                $stmt->execute(['phone_number' => $text, 'telegram_id' => $chatId]);
                sendMessage($chatId, "Thank you! Now, please provide your Telegram username link (e.g., https://t.me/username):");
            } elseif (empty($user['telegram_link'])) {
                // Step 4: Telegram username link
                $stmt = $pdo->prepare("UPDATE travelers SET telegram_link = :telegram_link WHERE telegram_id = :telegram_id");
                $stmt->execute(['telegram_link' => $text, 'telegram_id' => $chatId]);
                sendMessage($chatId, "Thank you! Now, please enter your bank transaction number:");
            } elseif (empty($user['bank_transaction_number'])) {
                // Step 5: Bank transaction number
                $stmt = $pdo->prepare("UPDATE travelers SET bank_transaction_number = :bank_transaction_number WHERE telegram_id = :telegram_id");
                $stmt->execute(['bank_transaction_number' => $text, 'telegram_id' => $chatId]);

                // Once all fields are filled, notify the user
                sendMessage($chatId, "Thank you! Your registration is now complete. Please wait for admin approval.");
                sendMessage($chatId, "Your registration is in progress. You will receive a confirmation number once approved.");
            }
        }
    }

    // Admin approval logic (simplified)
    if (isset($user['id']) && $user['status'] === 'Pending') {
        // Admin approves registration (simulate admin action here)
        // You can change this condition to check for admin actions via Telegram
        // For now, let's assume the registration is approved after completion

        // Generate confirmation number (e.g., FHXXXX/4)
        $confirmationNumber = 'FH' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT) . '/4';
        
        // Assign a car number (for example, 101)
        $assignedCar = '101';

        // Update status and add confirmation details to the database
        $stmt = $pdo->prepare("UPDATE travelers SET status = 'Confirmed', registration_number = :registration_number, assign_car = :assigned_car WHERE telegram_id = :telegram_id");
        $stmt->execute([
            'registration_number' => $confirmationNumber,
            'assigned_car' => $assignedCar,
            'telegram_id' => $chatId
        ]);

        // Notify the user with the registration number and car assignment
        sendMessage($chatId, "Congratulations! Your registration is now approved.");
        sendMessage($chatId, "Your registration number is: $confirmationNumber.");
        sendMessage($chatId, "Your assigned car number is: $assignedCar.");
    }
}
?>
