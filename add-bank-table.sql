-- Jalankan query ini di phpMyAdmin > tab SQL
-- untuk nambahin tabel rekening bank

CREATE TABLE IF NOT EXISTS bank_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bank_name VARCHAR(100) NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    account_name VARCHAR(100) NOT NULL,
    logo VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Seed data contoh
INSERT INTO bank_accounts (bank_name, account_number, account_name, is_active) VALUES
('BCA', '1234567890', 'Nexus Top Up', 1),
('GoPay / OVO', '08123456789', 'Nexus Top Up', 1),
('Dana', '08123456789', 'Nexus Top Up', 1);
