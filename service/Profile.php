<?php

namespace App;

use App\Database;

class Profile
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function updateContactInfo(int $userId, array $contactData): array
    {
        $stmt = $this->db->prepare("UPDATE users SET phone = ?, address = ?, website = ? WHERE id = ?");
        $success = $stmt->execute([
            $contactData['phone'] ?? null,
            $contactData['address'] ?? null,
            $contactData['website'] ?? null,
            $userId
        ]);

        if ($success) {
            // Update session data
            $_SESSION['user_contact'] = [
                'phone' => $contactData['phone'] ?? null,
                'address' => $contactData['address'] ?? null,
                'website' => $contactData['website'] ?? null
            ];
            return ['success' => true, 'message' => 'İletişim bilgileri başarıyla güncellendi.'];
        } else {
            return ['success' => false, 'message' => 'İletişim bilgileri güncellenirken bir hata oluştu.'];
        }
    }

    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        $stmt = $this->db->prepare("SELECT password_hash FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
            return ['success' => false, 'message' => 'Mevcut şifre yanlış.'];
        }

        $newPasswordHash = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("UPDATE users SET password_hash = ? WHERE id = ?");
        $success = $stmt->execute([$newPasswordHash, $userId]);

        if ($success) {
            return ['success' => true, 'message' => 'Şifre başarıyla değiştirildi.'];
        } else {
            return ['success' => false, 'message' => 'Şifre değiştirilirken bir hata oluştu.'];
        }
    }

    public function updateProfilePicture(int $userId, string $imageUrl): array
    {
        $stmt = $this->db->prepare("UPDATE users SET imageUrl = ? WHERE id = ?");
        $success = $stmt->execute([$imageUrl, $userId]);

        if ($success) {
            $_SESSION['user_imageUrl'] = $imageUrl;
            return ['success' => true, 'message' => 'Profil resmi başarıyla güncellendi.'];
        } else {
            return ['success' => false, 'message' => 'Profil resmi güncellenirken bir hata oluştu.'];
        }
    }
}
