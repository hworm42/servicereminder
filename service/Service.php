<?php

namespace App;

use App\Database;

class Service
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAllServices(): array
    {
        $stmt = $this->db->query("SELECT s.*, c.name as customerName FROM services s JOIN customers c ON s.customerId = c.id ORDER BY s.endDate ASC");
        return $stmt->fetchAll();
    }

    public function getServiceById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT s.*, c.name as customerName FROM services s JOIN customers c ON s.customerId = c.id WHERE s.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function addService(array $data): array
    {
        $stmt = $this->db->prepare("INSERT INTO services (name, description, status, customerId, customerName, startDate, endDate, duration) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $success = $stmt->execute([
            $data['name'],
            $data['description'],
            $data['status'] ?? 'Aktif',
            $data['customerId'],
            $data['customerName'],
            $data['startDate'],
            $data['endDate'],
            $data['duration']
        ]);

        if ($success) {
            return ['success' => true, 'message' => 'Hizmet başarıyla eklendi.', 'id' => $this->db->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Hizmet eklenirken bir hata oluştu.'];
        }
    }

    public function updateService(int $id, array $data): array
    {
        $stmt = $this->db->prepare("UPDATE services SET name = ?, description = ?, status = ?, customerId = ?, customerName = ?, startDate = ?, endDate = ?, duration = ? WHERE id = ?");
        $success = $stmt->execute([
            $data['name'],
            $data['description'],
            $data['status'],
            $data['customerId'],
            $data['customerName'],
            $data['startDate'],
            $data['endDate'],
            $data['duration'],
            $id
        ]);

        if ($success) {
            return ['success' => true, 'message' => 'Hizmet başarıyla güncellendi.'];
        } else {
            return ['success' => false, 'message' => 'Hizmet güncellenirken bir hata oluştu.'];
        }
    }

    public function deleteService(int $id): array
    {
        $stmt = $this->db->prepare("DELETE FROM services WHERE id = ?");
        $success = $stmt->execute([$id]);

        if ($success) {
            return ['success' => true, 'message' => 'Hizmet başarıyla silindi.'];
        } else {
            return ['success' => false, 'message' => 'Hizmet silinirken bir hata oluştu.'];
        }
    }
}
