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

// Admin ID (replace with the actual admin user ID)
define('ADMIN_ID', 'YOUR_ADMIN_USER_ID'); // Replace with your admin's Telegram chat ID

// Get the incoming updates from Telegram
$update = file_get_contents("php://input");
$update = json_decode($update, TRUE);

// Extract necessary details from the update
if (isset($update['message'])) {
    $chatId = $update['message']['chat']['id'];
    $text = $update['message']['text'];

    // Only allow the admin to interact with this section
    if ($chatId != ADMIN_ID) {
        sendMessage($chatId, "You are not authorized to access the admin panel.");
        exit();
    }

    // Admin commands
    if ($text == '/approve') {
        // Display the list of travelers with 'Pending' status for approval
        $stmt = $pdo->prepare("SELECT * FROM travelers WHERE status = 'Pending'");
        $stmt->execute();
        $pendingTravelers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($pendingTravelers) {
            foreach ($pendingTravelers as $traveler) {
                sendMessage($chatId, "Traveler ID: " . $traveler['telegram_id'] . "\nFull Name: " . $traveler['full_name'] . "\n\nUse /approve <telegram_id> to approve.");
            }
        } else {
            sendMessage($chatId, "No pending travelers for approval.");
        }
    }

    if (preg_match('/^\/approve (\d+)$/', $text, $matches)) {
        // Admin approves a specific traveler
        $telegramId = $matches[1];

        // Check if the user exists
        $stmt = $pdo->prepare("SELECT * FROM travelers WHERE telegram_id = :telegram_id AND status = 'Pending'");
        $stmt->execute(['telegram_id' => $telegramId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Find the next available car by checking the number of travelers in each car
            $carNumber = getAvailableCar();

            // Generate confirmation number
            $confirmationNumber = 'FH' . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT) . '/4';

            // Update traveler status and assign car number
            $stmt = $pdo->prepare("UPDATE travelers SET status = 'Confirmed', registration_number = :registration_number, assign_car = :assigned_car WHERE telegram_id = :telegram_id");
            $stmt->execute([
                'registration_number' => $confirmationNumber,
                'assigned_car' => $carNumber,
                'telegram_id' => $telegramId
            ]);

            // Notify the traveler about approval
            sendMessage($telegramId, "Congratulations! Your registration has been approved.\nYour registration number is: $confirmationNumber\nYour assigned car number is: $carNumber");

            // Notify the admin
            sendMessage($chatId, "Traveler $telegramId has been approved and assigned to car $carNumber.");
        } else {
            sendMessage($chatId, "No pending traveler found with Telegram ID $telegramId.");
        }
    }

    // Command to export travelers' data
    if ($text == '/export') {
        $stmt = $pdo->prepare("SELECT * FROM travelers");
        $stmt->execute();
        $travelers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($travelers) {
            // Export travelers data to CSV
            $filename = "travelers_" . date("Y-m-d_H-i-s") . ".csv";
            $fp = fopen($filename, 'w');
            $headers = array('ID', 'Full Name', 'Telegram ID', 'Phone Number', 'Address', 'Transaction Number', 'Status', 'Registration Number', 'Assigned Car');
            fputcsv($fp, $headers);

            foreach ($travelers as $traveler) {
                fputcsv($fp, $traveler);
            }

            fclose($fp);
            sendMessage($chatId, "Traveler data has been exported. Download the file: $filename");

            // Optionally, send the file directly via Telegram (if you want to send the file directly)
            // sendDocument($chatId, $filename);
        } else {
            sendMessage($chatId, "No travelers found to export.");
        }
    }

    // Command to send notifications to all travelers
    if (preg_match('/^\/notify (.+)$/', $text, $matches)) {
        // Admin wants to send a notification
        $message = $matches[1];

        $stmt = $pdo->prepare("SELECT telegram_id FROM travelers WHERE status = 'Confirmed'");
        $stmt->execute();
        $travelers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($travelers) {
            foreach ($travelers as $traveler) {
                sendMessage($traveler['telegram_id'], $message);
            }

            sendMessage($chatId, "Notification has been sent to all confirmed travelers.");
        } else {
            sendMessage($chatId, "No confirmed travelers to notify.");
        }
    }

    // Command to show due dates (if implemented, for example)
    if ($text == '/due') {
        // Assuming due dates are stored in the database, you can query for them.
        $stmt = $pdo->prepare("SELECT full_name, due_date FROM travelers WHERE due_date IS NOT NULL");
        $stmt->execute();
        $dueTravelers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($dueTravelers) {
            foreach ($dueTravelers as $traveler) {
                sendMessage($chatId, "Traveler: " . $traveler['full_name'] . " - Due Date: " . $traveler['due_date']);
            }
        } else {
            sendMessage($chatId, "No due dates found.");
        }
    }
}

// Function to get the next available car number
function getAvailableCar() {
    global $pdo;

    // Count the number of travelers assigned to each car (1 to 15)
    for ($i = 1; $i <= 15; $i++) {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM travelers WHERE assign_car = :car_number");
        $stmt->execute(['car_number' => $i]);
        $count = $stmt->fetchColumn();

        // If the car has fewer than 65 travelers, assign the traveler to that car
        if ($count < 65) {
            return $i;
        }
    }

    // If all cars are full (65 travelers per car), start assigning to the first car again
    return 1;
}

// Function to send a document (for exporting CSV)
function sendDocument($chatId, $filename) {
    $url = TELEGRAM_API_URL . "sendDocument?chat_id=" . $chatId . "&document=" . urlencode($filename);
    file_get_contents($url);
}
?>
