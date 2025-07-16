<?php

namespace App;

use PDO;

class Auth
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function register(string $name, string $email, string $password): array
    {
        // Check if public registration is allowed
        $stmt = $this->db->query("SELECT public_registration FROM settings WHERE id = 1");
        $settings = $stmt->fetch();
        if (!$settings || !$settings['public_registration']) {
            return ['success' => false, 'message' => 'Kayıtlar şu anda kapalıdır.'];
        }

        // Check if email already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Bu e-posta adresi zaten kullanılıyor.'];
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, 'Editör')");
        
        if ($stmt->execute([':name' => $name, ':email' => $email, ':password_hash' => $passwordHash])) {
            return ['success' => true, 'message' => 'Kayıt başarılı! Lütfen giriş yapın.'];
        }
        return ['success' => false, 'message' => 'Kayıt sırasında bir hata oluştu.'];
    }

    public function login(string $email, string $password): array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_start();
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_contact'] = [
                'phone' => $user['phone'],
                'address' => $user['address'],
                'website' => $user['website']
            ];
            return ['success' => true, 'message' => 'Giriş başarılı.', 'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
                'contact' => $_SESSION['user_contact']
            ]];
        }
        return ['success' => false, 'message' => 'Geçersiz e-posta veya şifre.'];
    }

    public function logout(): void
    {
        session_start();
        session_unset();
        session_destroy();
    }

    public function checkAuth(): array
    {
        session_start();
        if (isset($_SESSION['user_id'])) {
            return ['isAuthenticated' => true, 'user' => [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
                'role' => $_SESSION['user_role'],
                'contact' => $_SESSION['user_contact']
            ]];
        }
        return ['isAuthenticated' => false];
    }
}
