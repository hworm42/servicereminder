<?php

namespace App;

use App\Database;

class Auth
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function register(string $name, string $email, string $password): array
    {
        // Check if email already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Bu e-posta adresi zaten kullanılıyor.'];
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)");
        $success = $stmt->execute([$name, $email, $passwordHash, 'Editör']);

        if ($success) {
            return ['success' => true, 'message' => 'Kayıt başarılı! Lütfen giriş yapın.'];
        } else {
            return ['success' => false, 'message' => 'Kayıt sırasında bir hata oluştu.'];
        }
    }

    public function login(string $email, string $password): array
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_contact'] = [
                'phone' => $user['phone'],
                'address' => $user['address'],
                'website' => $user['website']
            ];
            $_SESSION['user_imageUrl'] = $user['imageUrl'];
            return ['success' => true, 'message' => 'Giriş başarılı.'];
        } else {
            return ['success' => false, 'message' => 'Geçersiz e-posta veya şifre.'];
        }
    }

    public function logout(): void
    {
        session_unset();
        session_destroy();
    }

    public function isAuthenticated(): bool
    {
        return isset($_SESSION['user_id']);
    }

    public function getUser(): ?array
    {
        if ($this->isAuthenticated()) {
            return [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'],
                'email' => $_SESSION['user_email'],
                'role' => $_SESSION['user_role'],
                'contact' => $_SESSION['user_contact'],
                'imageUrl' => $_SESSION['user_imageUrl']
            ];
        }
        return null;
    }

    public function generateSpamQuestion(): array
    {
        $num1 = rand(1, 10);
        $num2 = rand(1, 10);
        $_SESSION['spam_answer'] = $num1 + $num2;
        return ['num1' => $num1, 'num2' => $num2];
    }

    public function validateSpam(int $answer): bool
    {
        if (!isset($_SESSION['spam_answer']) || $answer !== $_SESSION['spam_answer']) {
            return false;
        }
        unset($_SESSION['spam_answer']); // Clear after use
        return true;
    }
}
