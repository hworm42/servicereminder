<?php
session_start();
require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth;
use App\Customer;
use App\Service;
use App\User;
use App\Settings;
use App\Profile;

$auth = new Auth();
$customerManager = new Customer();
$serviceManager = new Service();
$userManager = new User();
$settingsManager = new Settings();
$profileManager = new Profile();

header('Content-Type: application/json');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Helper function to get JSON input
function getJsonInput() {
    return json_decode(file_get_contents('php://input'), true);
}

// Authentication actions
if ($action === 'login') {
    $input = getJsonInput();
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    $spamAnswer = $input['spamAnswer'] ?? 0;

    if (!$auth->validateSpam($spamAnswer)) {
        echo json_encode(['success' => false, 'message' => 'Spam koruma sorusu yanlış cevaplandı.']);
        exit;
    }

    $result = $auth->login($email, $password);
    echo json_encode($result);
    exit;
} elseif ($action === 'register') {
    $input = getJsonInput();
    $name = $input['name'] ?? '';
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';
    $spamAnswer = $input['spamAnswer'] ?? 0;

    $settings = $settingsManager->getSettings();
    if (!($settings['publicRegistration'] ?? false)) {
        echo json_encode(['success' => false, 'message' => 'Kayıtlar şu anda kapalıdır.']);
        exit;
    }

    if (!$auth->validateSpam($spamAnswer)) {
        echo json_encode(['success' => false, 'message' => 'Spam koruma sorusu yanlış cevaplandı.']);
        exit;
    }

    $result = $auth->register($name, $email, $password);
    echo json_encode($result);
    exit;
} elseif ($action === 'logout') {
    $auth->logout();
    echo json_encode(['success' => true, 'message' => 'Çıkış başarılı.']);
    exit;
} elseif ($action === 'getSpamQuestion') {
    echo json_encode($auth->generateSpamQuestion());
    exit;
}

// All subsequent actions require authentication
if (!$auth->isAuthenticated()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Yetkisiz erişim. Lütfen giriş yapın.']);
    exit;
}

// Get current user info
if ($action === 'getUserInfo') {
    echo json_encode($auth->getUser());
    exit;
}

// Customer Management
if ($action === 'getCustomers') {
    echo json_encode($customerManager->getAllCustomers());
    exit;
} elseif ($action === 'addCustomer') {
    $input = getJsonInput();
    echo json_encode($customerManager->addCustomer($input));
    exit;
} elseif ($action === 'updateCustomer') {
    $input = getJsonInput();
    $id = $input['id'] ?? 0;
    echo json_encode($customerManager->updateCustomer($id, $input));
    exit;
} elseif ($action === 'deleteCustomer') {
    $id = $_GET['id'] ?? 0;
    echo json_encode($customerManager->deleteCustomer($id));
    exit;
}

// Service Management
if ($action === 'getServices') {
    echo json_encode($serviceManager->getAllServices());
    exit;
} elseif ($action === 'addService') {
    $input = getJsonInput();
    echo json_encode($serviceManager->addService($input));
    exit;
} elseif ($action === 'updateService') {
    $input = getJsonInput();
    $id = $input['id'] ?? 0;
    echo json_encode($serviceManager->updateService($id, $input));
    exit;
} elseif ($action === 'deleteService') {
    $id = $_GET['id'] ?? 0;
    echo json_encode($serviceManager->deleteService($id));
    exit;
}

// User Management
if ($action === 'getUsers') {
    echo json_encode($userManager->getAllUsers());
    exit;
} elseif ($action === 'addUser') {
    $input = getJsonInput();
    echo json_encode($userManager->addUser($input));
    exit;
} elseif ($action === 'updateUser') {
    $input = getJsonInput();
    $id = $input['id'] ?? 0;
    echo json_encode($userManager->updateUser($id, $input));
    exit;
} elseif ($action === 'deleteUser') {
    $id = $_GET['id'] ?? 0;
    if ($id == $_SESSION['user_id']) {
        echo json_encode(['success' => false, 'message' => 'Kendinizi silemezsiniz!']);
        exit;
    }
    echo json_encode($userManager->deleteUser($id));
    exit;
}

// Settings Management
if ($action === 'getSettings') {
    echo json_encode($settingsManager->getSettings());
    exit;
} elseif ($action === 'saveSettings') {
    $input = getJsonInput();
    echo json_encode($settingsManager->saveSettings($input));
    exit;
}

// Profile Management
if ($action === 'updateContactInfo') {
    $input = getJsonInput();
    echo json_encode($profileManager->updateContactInfo($_SESSION['user_id'], $input));
    exit;
} elseif ($action === 'changePassword') {
    $input = getJsonInput();
    $currentPassword = $input['current'] ?? '';
    $newPassword = $input['new'] ?? '';
    echo json_encode($profileManager->changePassword($_SESSION['user_id'], $currentPassword, $newPassword));
    exit;
} elseif ($action === 'updateProfilePicture') {
    $input = getJsonInput();
    $imageUrl = $input['imageUrl'] ?? '';
    echo json_encode($profileManager->updateProfilePicture($_SESSION['user_id'], $imageUrl));
    exit;
}

// Data Export/Import (simplified for PHP backend)
if ($action === 'exportData') {
    $users = $userManager->getAllUsers();
    $customers = $customerManager->getAllCustomers();
    $services = $serviceManager->getAllServices();
    $settings = $settingsManager->getSettings();

    $data = [
        'users' => $users,
        'customers' => $customers,
        'services' => $services,
        'settings' => $settings
    ];
    echo json_encode($data);
    exit;
} elseif ($action === 'importData') {
    $input = getJsonInput();
    $importedUsers = $input['users'] ?? [];
    $importedCustomers = $input['customers'] ?? [];
    $importedServices = $input['services'] ?? [];
    $importedSettings = $input['settings'] ?? [];

    // Clear existing data (simplified, in a real app you'd handle merges/updates)
    $db = Database::getInstance();
    $db->exec("DELETE FROM users");
    $db->exec("DELETE FROM customers");
    $db->exec("DELETE FROM services");
    $db->exec("DELETE FROM settings");

    // Re-insert imported data
    foreach ($importedUsers as $user) {
        $userManager->addUser($user); // This will re-hash passwords
    }
    foreach ($importedCustomers as $customer) {
        $customerManager->addCustomer($customer);
    }
    foreach ($importedServices as $service) {
        $serviceManager->addService($service);
    }
    if (!empty($importedSettings)) {
        $settingsManager->saveSettings($importedSettings);
    }

    echo json_encode(['success' => true, 'message' => 'Veriler başarıyla içe aktarıldı!']);
    exit;
}

// If no action matched
http_response_code(400);
echo json_encode(['success' => false, 'message' => 'Geçersiz aksiyon.']);

?>
