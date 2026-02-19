-- =========================================
-- NEXUS TOP UP - DATABASE SCHEMA
-- =========================================

CREATE DATABASE IF NOT EXISTS nexus_topup;
USE nexus_topup;

-- Users Table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user',
    avatar VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Games Table
CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    thumbnail VARCHAR(255),
    banner VARCHAR(255),
    category VARCHAR(50),
    developer VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Top Up Packages Table
CREATE TABLE packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    amount INT NOT NULL,
    currency_name VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    bonus INT DEFAULT 0,
    is_popular TINYINT(1) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    FOREIGN KEY (game_id) REFERENCES games(id) ON DELETE CASCADE
);

-- Transactions Table
CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id VARCHAR(50) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    package_id INT NOT NULL,
    game_user_id VARCHAR(100) NOT NULL,
    game_server_id VARCHAR(100) DEFAULT NULL,
    nickname VARCHAR(100) DEFAULT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    payment_method VARCHAR(50),
    status ENUM('pending', 'processing', 'success', 'failed', 'refunded') DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (game_id) REFERENCES games(id),
    FOREIGN KEY (package_id) REFERENCES packages(id)
);

-- Payment Methods Table
CREATE TABLE payment_methods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('bank', 'ewallet', 'qris', 'retail') NOT NULL,
    logo VARCHAR(255),
    fee_type ENUM('fixed', 'percent') DEFAULT 'fixed',
    fee_value DECIMAL(10,2) DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1
);

-- =========================================
-- SEED DATA
-- =========================================

-- Admin user (password: admin123)
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@nexus.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('testuser', 'user@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- Games
INSERT INTO games (name, slug, description, thumbnail, banner, category, developer) VALUES
('Mobile Legends: Bang Bang', 'mobile-legends', 'Sebuah game MOBA 5v5 yang epik untuk mobile', 'img/ml-thumb.jpg', 'img/ml-banner.jpg', 'MOBA', 'Moonton'),
('Free Fire', 'free-fire', 'Battle royale survival game yang seru', 'img/ff-thumb.jpg', 'img/ff-banner.jpg', 'Battle Royale', 'Garena'),
('PUBG Mobile', 'pubg-mobile', 'Game battle royale terpopuler di dunia', 'img/pubg-thumb.jpg', 'img/pubg-banner.jpg', 'Battle Royale', 'Tencent'),
('Genshin Impact', 'genshin-impact', 'Open world RPG dengan karakter yang beragam', 'img/gi-thumb.jpg', 'img/gi-banner.jpg', 'RPG', 'miHoYo'),
('Valorant', 'valorant', 'Tactical shooter 5v5 yang kompetitif', 'img/val-thumb.jpg', 'img/val-banner.jpg', 'FPS', 'Riot Games');

-- Packages for Mobile Legends
INSERT INTO packages (game_id, name, amount, currency_name, price, bonus, is_popular) VALUES
(1, 'Hemat', 86, 'Diamond', 19000, 0, 0),
(1, 'Starter', 172, 'Diamond', 38000, 0, 0),
(1, 'Value', 257, 'Diamond', 57000, 0, 0),
(1, 'Popular', 344, 'Diamond', 76000, 30, 1),
(1, 'Pro', 514, 'Diamond', 110000, 50, 0),
(1, 'Elite', 1032, 'Diamond', 220000, 100, 0);

-- Packages for Free Fire
INSERT INTO packages (game_id, name, amount, currency_name, price, bonus, is_popular) VALUES
(2, 'Starter', 70, 'Diamond', 14000, 0, 0),
(2, 'Value', 140, 'Diamond', 28000, 0, 0),
(2, 'Popular', 355, 'Diamond', 65000, 20, 1),
(2, 'Premium', 720, 'Diamond', 125000, 50, 0),
(2, 'Elite', 1450, 'Diamond', 250000, 100, 0);

-- Packages for PUBG
INSERT INTO packages (game_id, name, amount, currency_name, price, bonus) VALUES
(3, 'Starter', 60, 'UC', 14000, 0),
(3, 'Value', 180, 'UC', 40000, 0),
(3, 'Popular', 325, 'UC', 70000, 25),
(3, 'Premium', 660, 'UC', 140000, 60),
(3, 'Elite', 1800, 'UC', 375000, 150);

-- Packages for Genshin
INSERT INTO packages (game_id, name, amount, currency_name, price, bonus) VALUES
(4, 'Starter', 60, 'Genesis Crystal', 15000, 0),
(4, 'Value', 300, 'Genesis Crystal', 73000, 30),
(4, 'Popular', 980, 'Genesis Crystal', 230000, 110),
(4, 'Premium', 1980, 'Genesis Crystal', 455000, 260),
(4, 'Elite', 3280, 'Genesis Crystal', 750000, 600);

-- Payment Methods
INSERT INTO payment_methods (name, type, logo, fee_type, fee_value) VALUES
('BCA', 'bank', 'img/bca.png', 'fixed', 4000),
('BNI', 'bank', 'img/bni.png', 'fixed', 4000),
('Mandiri', 'bank', 'img/mandiri.png', 'fixed', 4000),
('GoPay', 'ewallet', 'img/gopay.png', 'fixed', 0),
('OVO', 'ewallet', 'img/ovo.png', 'fixed', 0),
('Dana', 'ewallet', 'img/dana.png', 'fixed', 0),
('QRIS', 'qris', 'img/qris.png', 'percent', 0.7),
('Indomaret', 'retail', 'img/indomaret.png', 'fixed', 5000),
('Alfamart', 'retail', 'img/alfamart.png', 'fixed', 5000);
