<?php

namespace App;

use App\Database;

class Customer
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function getAllCustomers(): array
    {
        $stmt = $this->db->query("SELECT * FROM customers ORDER BY registrationDate DESC");
        return $stmt->fetchAll();
    }

    public function getCustomerById(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM customers WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function addCustomer(array $data): array
    {
        $stmt = $this->db->prepare("INSERT INTO customers (name, email, phone, registrationDate, status) VALUES (?, ?, ?, ?, ?)");
        $success = $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['registrationDate'] ?? date('Y-m-d'),
            $data['status'] ?? 'Aktif'
        ]);

        if ($success) {
            return ['success' => true, 'message' => 'Müşteri başarıyla eklendi.', 'id' => $this->db->lastInsertId()];
        } else {
            return ['success' => false, 'message' => 'Müşteri eklenirken bir hata oluştu.'];
        }
    }

    public function updateCustomer(int $id, array $data): array
    {
        $stmt = $this->db->prepare("UPDATE customers SET name = ?, email = ?, phone = ?, status = ? WHERE id = ?");
        $success = $stmt->execute([
            $data['name'],
            $data['email'],
            $data['phone'],
            $data['status'],
            $id
        ]);

        if ($success) {
            return ['success' => true, 'message' => 'Müşteri başarıyla güncellendi.'];
        } else {
            return ['success' => false, 'message' => 'Müşteri güncellenirken bir hata oluştu.'];
        }
    }

    public function deleteCustomer(int $id): array
    {
        $stmt = $this->db->prepare("DELETE FROM customers WHERE id = ?");
        $success = $stmt->execute([$id]);

        if ($success) {
            return ['success' => true, 'message' => 'Müşteri başarıyla silindi.'];
        } else {
            return ['success' => false, 'message' => 'Müşteri silinirken bir hata oluştu.'];
        }
    }
}
