<?php

namespace App;

use App\Database;

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAllUsers(): array
    {
        $stmt = $this->db->query("SELECT id, name, email, role, phone, address, website, imageUrl FROM users ORDER BY name ASC");
        return $stmt->fetchAll();
    }

    public function getUserById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT id, name, email, role, phone, address, website, imageUrl FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function addUser(array $data): array
    {
        // Check if email already exists
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$data['email']]);
        if ($stmt->fetch()) {
            return ['success' => false, 'message' => 'Bu e-posta adresi zaten kullanılıyor.'];
        }

        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password_hash, role, phone, address, website, imageUrl) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([
            $data['name'],
            $data['email'],
            $passwordHash,
            $data['role'] ?? 'Editör',
            $data['contact']['phone'] ?? null,
            $data['contact']['address'] ?? null,
            $data['contact']['website'] ?? null,
            $data['imageUrl'] ?? 'https://placehold.co/256x256/EFEFEF/333?text=AU'
        ]);

        if ($success) {
            return ['success' => true, 'message' => 'Kullanıcı başarıyla eklendi.', 'id' => $this->db->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Kullanıcı eklenirken bir hata oluştu.'];
        }
    }

    public function updateUser(int $id, array $data): array
    {
        $sql = "UPDATE users SET name = ?, email = ?, role = ?, phone = ?, address = ?, website = ?";
        $params = [
            $data['name'],
            $data['email'],
            $data['role'],
            $data['contact']['phone'] ?? null,
            $data['contact']['address'] ?? null,
            $data['contact']['website'] ?? null
        ];

        if (!empty($data['password'])) {
            $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
            $sql .= ", password_hash = ?";
            $params[] = $passwordHash;
        }
        if (!empty($data['imageUrl'])) {
            $sql .= ", imageUrl = ?";
            $params[] = $data['imageUrl'];
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $this->db->prepare($sql);
        $success = $stmt->execute($params);

        if ($success) {
            return ['success' => true, 'message' => 'Kullanıcı başarıyla güncellendi.'];
        } else {
            return ['success' => false, 'message' => 'Kullanıcı güncellenirken bir hata oluştu.'];
        }
    }

    public function deleteUser(int $id): array
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $success = $stmt->execute([$id]);

        if ($success) {
            return ['success' => true, 'message' => 'Kullanıcı başarıyla silindi.'];
        } else {
            return ['success' => false, 'message' => 'Kullanıcı silinirken bir hata oluştu.'];
        }
    }
}
