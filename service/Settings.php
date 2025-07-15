<?php

namespace App;

use App\Database;

class Settings
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getSettings(): array
    {
        $stmt = $this->db->query("SELECT * FROM settings WHERE id = 1");
        $settings = $stmt->fetch();
        if ($settings) {
            // Convert publicRegistration from INTEGER to boolean
            $settings['publicRegistration'] = (bool)$settings['publicRegistration'];
        }
        return $settings ?: [];
    }

    public function saveSettings(array $data): array
    {
        // Convert publicRegistration from boolean to INTEGER
        $data['publicRegistration'] = (int)$data['publicRegistration'];

        $stmt = $this->db->prepare("UPDATE settings SET siteTitle = ?, publicRegistration = ?, reminderDays = ?, emailTemplate = ?, smtp_host = ?, smtp_port = ?, smtp_username = ?, smtp_password = ?, smtp_security = ? WHERE id = 1");
        $success = $stmt->execute([
            $data['siteTitle'],
            $data['publicRegistration'],
            $data['reminderDays'],
            $data['emailTemplate'],
            $data['smtp_host'],
            $data['smtp_port'],
            $data['smtp_username'],
            $data['smtp_password'],
            $data['smtp_security']
        ]);

        if ($success) {
            return ['success' => true, 'message' => 'Ayarlar başarıyla kaydedildi.'];
        } else {
            return ['success' => false, 'message' => 'Ayarlar kaydedilirken bir hata oluştu.'];
        }
    }
}
