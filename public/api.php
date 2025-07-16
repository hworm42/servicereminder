<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\DotEnv;
use App\Database;
use App\Auth;

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Geliştirme için CORS izni
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

// Handle OPTIONS requests for CORS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Load .env file
(new DotEnv(__DIR__ . '/../.env'))->load();

// Initialize Database and create tables if not exists
$database = new Database();
$database->createTables();

$auth = new Auth();

$input = json_decode(file_get_contents('php://input'), true);
$action = $_GET['action'] ?? ($input['action'] ?? '');

$response = ['success' => false, 'message' => 'Geçersiz istek.'];

switch ($action) {
    case 'register':
        if (isset($input['name'], $input['email'], $input['password'])) {
            $response = $auth->register($input['name'], $input['email'], $input['password']);
        } else {
            $response['message'] = 'Kayıt için gerekli tüm alanlar sağlanmadı.';
        }
        break;
    case 'login':
        if (isset($input['email'], $input['password'])) {
            $response = $auth->login($input['email'], $input['password']);
        } else {
            $response['message'] = 'Giriş için e-posta ve şifre gerekli.';
        }
        break;
    case 'logout':
        $auth->logout();
        $response = ['success' => true, 'message' => 'Çıkış başarılı.'];
        break;
    case 'checkAuth':
        $response = $auth->checkAuth();
        break;
    case 'getSettings':
        $stmt = Database::getInstance()->query("SELECT site_title, public_registration, reminder_days, smtp_host, smtp_port, smtp_username, smtp_security FROM settings WHERE id = 1");
        $settings = $stmt->fetch();
        if ($settings) {
            $settings['public_registration'] = (bool)$settings['public_registration'];
            $response = ['success' => true, 'settings' => $settings];
        } else {
            $response['message'] = 'Ayarlar bulunamadı.';
        }
        break;
    case 'updateSettings':
        if (isset($input['settings'])) {
            $s = $input['settings'];
            $stmt = Database::getInstance()->prepare("UPDATE settings SET site_title = :site_title, public_registration = :public_registration, reminder_days = :reminder_days, email_template = :email_template, smtp_host = :smtp_host, smtp_port = :smtp_port, smtp_username = :smtp_username, smtp_password = :smtp_password, smtp_security = :smtp_security WHERE id = 1");
            $success = $stmt->execute([
                ':site_title' => $s['siteTitle'] ?? null,
                ':public_registration' => (int)($s['publicRegistration'] ?? 0),
                ':reminder_days' => $s['reminderDays'] ?? null,
                ':email_template' => $s['emailTemplate'] ?? null,
                ':smtp_host' => $s['smtp']['host'] ?? null,
                ':smtp_port' => $s['smtp']['port'] ?? null,
                ':smtp_username' => $s['smtp']['username'] ?? null,
                ':smtp_password' => $s['smtp']['password'] ?? null, // Password is sent directly, consider hashing or not sending from frontend
                ':smtp_security' => $s['smtp']['security'] ?? null
            ]);
            if ($success) {
                $response = ['success' => true, 'message' => 'Ayarlar başarıyla güncellendi.'];
            } else {
                $response['message'] = 'Ayarlar güncellenirken bir hata oluştu.';
            }
        } else {
            $response['message'] = 'Ayarlar verisi sağlanmadı.';
        }
        break;
    // Customer Management
    case 'getCustomers':
        $stmt = Database::getInstance()->query("SELECT * FROM customers");
        $customers = $stmt->fetchAll();
        $response = ['success' => true, 'customers' => $customers];
        break;
    case 'addCustomer':
        if (isset($input['name'], $input['email'], $input['phone'], $input['registrationDate'], $input['status'])) {
            $stmt = Database::getInstance()->prepare("INSERT INTO customers (name, email, phone, registrationDate, status) VALUES (:name, :email, :phone, :registrationDate, :status)");
            if ($stmt->execute($input)) {
                $response = ['success' => true, 'message' => 'Müşteri başarıyla eklendi.', 'id' => Database::getInstance()->lastInsertId()];
            } else {
                $response['message'] = 'Müşteri eklenirken hata oluştu.';
            }
        } else {
            $response['message'] = 'Müşteri eklemek için gerekli tüm alanlar sağlanmadı.';
        }
        break;
    case 'updateCustomer':
        if (isset($input['id'], $input['name'], $input['email'], $input['phone'], $input['registrationDate'], $input['status'])) {
            $stmt = Database::getInstance()->prepare("UPDATE customers SET name = :name, email = :email, phone = :phone, registrationDate = :registrationDate, status = :status WHERE id = :id");
            if ($stmt->execute($input)) {
                $response = ['success' => true, 'message' => 'Müşteri başarıyla güncellendi.'];
            } else {
                $response['message'] = 'Müşteri güncellenirken hata oluştu.';
            }
        } else {
            $response['message'] = 'Müşteri güncellemek için gerekli tüm alanlar sağlanmadı.';
        }
        break;
    case 'deleteCustomer':
        if (isset($input['id'])) {
            $stmt = Database::getInstance()->prepare("DELETE FROM customers WHERE id = :id");
            if ($stmt->execute([':id' => $input['id']])) {
                $response = ['success' => true, 'message' => 'Müşteri başarıyla silindi.'];
            } else {
                $response['message'] = 'Müşteri silinirken hata oluştu.';
            }
        } else {
            $response['message'] = 'Silinecek müşteri ID\'si sağlanmadı.';
        }
        break;
    // Service Management
    case 'getServices':
        $stmt = Database::getInstance()->query("SELECT * FROM services");
        $services = $stmt->fetchAll();
        $response = ['success' => true, 'services' => $services];
        break;
    case 'addService':
        if (isset($input['name'], $input['description'], $input['status'], $input['customerId'], $input['customerName'], $input['startDate'], $input['endDate'], $input['duration'])) {
            $stmt = Database::getInstance()->prepare("INSERT INTO services (name, description, status, customer_id, customer_name, start_date, end_date, duration) VALUES (:name, :description, :status, :customer_id, :customer_name, :start_date, :end_date, :duration)");
            if ($stmt->execute([
                ':name' => $input['name'],
                ':description' => $input['description'],
                ':status' => $input['status'],
                ':customer_id' => $input['customerId'],
                ':customer_name' => $input['customerName'],
                ':start_date' => $input['startDate'],
                ':end_date' => $input['endDate'],
                ':duration' => $input['duration']
            ])) {
                $response = ['success' => true, 'message' => 'Hizmet başarıyla eklendi.', 'id' => Database::getInstance()->lastInsertId()];
            } else {
                $response['message'] = 'Hizmet eklenirken hata oluştu.';
            }
        } else {
            $response['message'] = 'Hizmet eklemek için gerekli tüm alanlar sağlanmadı.';
        }
        break;
    case 'updateService':
        if (isset($input['id'], $input['name'], $input['description'], $input['status'], $input['customerId'], $input['customerName'], $input['startDate'], $input['endDate'], $input['duration'])) {
            $stmt = Database::getInstance()->prepare("UPDATE services SET name = :name, description = :description, status = :status, customer_id = :customer_id, customer_name = :customer_name, start_date = :start_date, end_date = :end_date, duration = :duration WHERE id = :id");
            if ($stmt->execute([
                ':id' => $input['id'],
                ':name' => $input['name'],
                ':description' => $input['description'],
                ':status' => $input['status'],
                ':customer_id' => $input['customerId'],
                ':customer_name' => $input['customerName'],
                ':start_date' => $input['startDate'],
                ':end_date' => $input['endDate'],
                ':duration' => $input['duration']
            ])) {
                $response = ['success' => true, 'message' => 'Hizmet başarıyla güncellendi.'];
            } else {
                $response['message'] = 'Hizmet güncellenirken hata oluştu.';
            }
        } else {
            $response['message'] = 'Hizmet güncellemek için gerekli tüm alanlar sağlanmadı.';
        }
        break;
    case 'deleteService':
        if (isset($input['id'])) {
            $stmt = Database::getInstance()->prepare("DELETE FROM services WHERE id = :id");
            if ($stmt->execute([':id' => $input['id']])) {
                $response = ['success' => true, 'message' => 'Hizmet başarıyla silindi.'];
            } else {
                $response['message'] = 'Hizmet silinirken hata oluştu.';
            }
        } else {
            $response['message'] = 'Silinecek hizmet ID\'si sağlanmadı.';
        }
        break;
    // User Management
    case 'getUsers':
        $stmt = Database::getInstance()->query("SELECT id, name, email, role, phone, address, website FROM users"); // Don't expose password_hash
        $users = $stmt->fetchAll();
        $response = ['success' => true, 'users' => $users];
        break;
    case 'addUser':
        if (isset($input['name'], $input['email'], $input['password'], $input['role'])) {
            $passwordHash = password_hash($input['password'], PASSWORD_BCRYPT);
            $stmt = Database::getInstance()->prepare("INSERT INTO users (name, email, password_hash, role, phone, address, website) VALUES (:name, :email, :password_hash, :role, :phone, :address, :website)");
            if ($stmt->execute([
                ':name' => $input['name'],
                ':email' => $input['email'],
                ':password_hash' => $passwordHash,
                ':role' => $input['role'],
                ':phone' => $input['phone'] ?? null,
                ':address' => $input['address'] ?? null,
                ':website' => $input['website'] ?? null
            ])) {
                $response = ['success' => true, 'message' => 'Kullanıcı başarıyla eklendi.', 'id' => Database::getInstance()->lastInsertId()];
            } else {
                $response['message'] = 'Kullanıcı eklenirken hata oluştu.';
            }
        } else {
            $response['message'] = 'Kullanıcı eklemek için gerekli tüm alanlar sağlanmadı.';
        }
        break;
    case 'updateUser':
        if (isset($input['id'], $input['name'], $input['email'], $input['role'])) {
            $sql = "UPDATE users SET name = :name, email = :email, role = :role, phone = :phone, address = :address, website = :website";
            $params = [
                ':id' => $input['id'],
                ':name' => $input['name'],
                ':email' => $input['email'],
                ':role' => $input['role'],
                ':phone' => $input['phone'] ?? null,
                ':address' => $input['address'] ?? null,
                ':website' => $input['website'] ?? null
            ];
            if (!empty($input['password'])) {
                $sql .= ", password_hash = :password_hash";
                $params[':password_hash'] = password_hash($input['password'], PASSWORD_BCRYPT);
            }
            $sql .= " WHERE id = :id";
            $stmt = Database::getInstance()->prepare($sql);
            if ($stmt->execute($params)) {
                $response = ['success' => true, 'message' => 'Kullanıcı başarıyla güncellendi.'];
            } else {
                $response['message'] = 'Kullanıcı güncellenirken hata oluştu.';
            }
        } else {
            $response['message'] = 'Kullanıcı güncellemek için gerekli tüm alanlar sağlanmadı.';
        }
        break;
    case 'deleteUser':
        if (isset($input['id'])) {
            $stmt = Database::getInstance()->prepare("DELETE FROM users WHERE id = :id");
            if ($stmt->execute([':id' => $input['id']])) {
                $response = ['success' => true, 'message' => 'Kullanıcı başarıyla silindi.'];
            } else {
                $response['message'] = 'Kullanıcı silinirken hata oluştu.';
            }
        } else {
            $response['message'] = 'Silinecek kullanıcı ID\'si sağlanmadı.';
        }
        break;
    case 'updateProfileContact':
        if (isset($input['id'], $input['contact'])) {
            $contact = $input['contact'];
            $stmt = Database::getInstance()->prepare("UPDATE users SET phone = :phone, address = :address, website = :website WHERE id = :id");
            if ($stmt->execute([
                ':id' => $input['id'],
                ':phone' => $contact['phone'] ?? null,
                ':address' => $contact['address'] ?? null,
                ':website' => $contact['website'] ?? null
            ])) {
                $response = ['success' => true, 'message' => 'İletişim bilgileri güncellendi.'];
            } else {
                $response['message'] = 'İletişim bilgileri güncellenirken hata oluştu.';
            }
        } else {
            $response['message'] = 'Profil iletişim bilgileri güncellenirken gerekli veriler sağlanmadı.';
        }
        break;
    case 'changePassword':
        if (isset($input['id'], $input['currentPassword'], $input['newPassword'])) {
            $stmt = Database::getInstance()->prepare("SELECT password_hash FROM users WHERE id = :id");
            $stmt->execute([':id' => $input['id']]);
            $user = $stmt->fetch();

            if ($user && password_verify($input['currentPassword'], $user['password_hash'])) {
                $newPasswordHash = password_hash($input['newPassword'], PASSWORD_BCRYPT);
                $stmt = Database::getInstance()->prepare("UPDATE users SET password_hash = :password_hash WHERE id = :id");
                if ($stmt->execute([':password_hash' => $newPasswordHash, ':id' => $input['id']])) {
                    $response = ['success' => true, 'message' => 'Şifre başarıyla değiştirildi.'];
                } else {
                    $response['message'] = 'Şifre değiştirilirken bir hata oluştu.';
                }
            } else {
                $response['message'] = 'Mevcut şifre yanlış.';
            }
        } else {
            $response['message'] = 'Şifre değiştirmek için gerekli tüm alanlar sağlanmadı.';
        }
        break;
    default:
        $response['message'] = 'Bilinmeyen API eylemi.';
        break;
}

echo json_encode($response);

?>
