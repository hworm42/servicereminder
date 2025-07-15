-- MySQL için veritabanı ve kullanıcı oluşturma
CREATE DATABASE IF NOT EXISTS `admin_panel`;
CREATE USER IF NOT EXISTS 'user'@'%' IDENTIFIED BY 'password';
GRANT ALL PRIVILEGES ON `admin_panel`.* TO 'user'@'%';
FLUSH PRIVILEGES;

USE `admin_panel`;

-- users tablosu
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(50) NOT NULL DEFAULT 'Editör',
    phone VARCHAR(50),
    address TEXT,
    website VARCHAR(255),
    imageUrl VARCHAR(255)
);

-- customers tablosu
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    registrationDate VARCHAR(255) NOT NULL,
    status VARCHAR(50) NOT NULL
);

-- services tablosu
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    status VARCHAR(50) NOT NULL,
    customerId INT NOT NULL,
    customerName VARCHAR(255) NOT NULL,
    startDate VARCHAR(255) NOT NULL,
    endDate VARCHAR(255) NOT NULL,
    duration INT,
    FOREIGN KEY (customerId) REFERENCES customers(id) ON DELETE CASCADE
);

-- settings tablosu
CREATE TABLE IF NOT EXISTS settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    siteTitle VARCHAR(255),
    publicRegistration TINYINT(1),
    reminderDays TEXT,
    emailTemplate TEXT,
    smtp_host VARCHAR(255),
    smtp_port INT,
    smtp_username VARCHAR(255),
    smtp_password VARCHAR(255),
    smtp_security VARCHAR(50)
);

-- Varsayılan admin kullanıcısını ekle (eğer yoksa)
INSERT IGNORE INTO users (id, name, email, password_hash, role, phone, address, website, imageUrl) VALUES
(1, 'Admin Kullanıcı', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin', '0500 111 2233', 'Admin Sk. No:1', 'https://admin.com', 'https://placehold.co/256x256/EFEFEF/333?text=AU');

-- Varsayılan ayarları ekle (eğer yoksa)
INSERT IGNORE INTO settings (id, siteTitle, publicRegistration, reminderDays, emailTemplate, smtp_host, smtp_port, smtp_username, smtp_password, smtp_security) VALUES
(1, 'Benim Harika Sitem', 1, '30,15,7', 'Sayın {{musteri_adi}},\n\n{{hizmet_adi}} adlı hizmetinizin sona ermesine {{kalan_gun}} gün kalmıştır.\n\nHizmetinizi yenilemek için bizimle iletişime geçebilirsiniz.\n\nTelefon: {{iletisim_telefon}}\nWeb: {{iletisim_website}}', 'smtp.example.com', 587, 'user@example.com', '', 'starttls');
