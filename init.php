<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Database;

try {
    $db = Database::getInstance();

    // Create users table
    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT NOT NULL,
        role TEXT NOT NULL DEFAULT 'Editör',
        phone TEXT,
        address TEXT,
        website TEXT,
        imageUrl TEXT
    )");

    // Create customers table
    $db->exec("CREATE TABLE IF NOT EXISTS customers (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL,
        phone TEXT,
        registrationDate TEXT NOT NULL,
        status TEXT NOT NULL
    )");

    // Create services table
    $db->exec("CREATE TABLE IF NOT EXISTS services (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        description TEXT,
        status TEXT NOT NULL,
        customerId INTEGER NOT NULL,
        customerName TEXT NOT NULL,
        startDate TEXT NOT NULL,
        endDate TEXT NOT NULL,
        duration INTEGER,
        FOREIGN KEY (customerId) REFERENCES customers(id) ON DELETE CASCADE
    )");

    // Create settings table
    $db->exec("CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        siteTitle TEXT,
        publicRegistration INTEGER,
        reminderDays TEXT,
        emailTemplate TEXT,
        smtp_host TEXT,
        smtp_port INTEGER,
        smtp_username TEXT,
        smtp_password TEXT,
        smtp_security TEXT
    )");

    // Insert default admin user if not exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute(['admin@example.com']);
    if ($stmt->fetchColumn() == 0) {
        $passwordHash = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $db->prepare("INSERT INTO users (name, email, password_hash, role, phone, address, website, imageUrl) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'Admin Kullanıcı',
            'admin@example.com',
            $passwordHash,
            'Admin',
            '0500 111 2233',
            'Admin Sk. No:1',
            'https://admin.com',
            'https://placehold.co/256x256/EFEFEF/333?text=AU'
        ]);
    }

    // Insert default settings if not exists
    $stmt = $db->prepare("SELECT COUNT(*) FROM settings");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $stmt = $db->prepare("INSERT INTO settings (siteTitle, publicRegistration, reminderDays, emailTemplate, smtp_host, smtp_port, smtp_username, smtp_password, smtp_security) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            'Benim Harika Sitem',
            1, // true
            '30,15,7',
            'Sayın {{musteri_adi}},\n\n{{hizmet_adi}} adlı hizmetinizin sona ermesine {{kalan_gun}} gün kalmıştır.\n\nHizmetinizi yenilemek için bizimle iletişime geçebilirsiniz.\n\nTelefon: {{iletisim_telefon}}\nWeb: {{iletisim_website}}',
            'smtp.example.com',
            587,
            'user@example.com',
            '',
            'starttls'
        ]);
    }

    echo "Veritabanı tabloları başarıyla oluşturuldu ve varsayılan veriler eklendi.\n";

} catch (PDOException $e) {
    echo "Veritabanı hatası: " . $e->getMessage() . "\n";
}

?>
