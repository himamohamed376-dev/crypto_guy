<?php
// جلب بيانات الاتصال من متغيّرات البيئة في Render
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASSWORD');
$db   = getenv('DB_NAME');
$port = getenv('DB_PORT');

// الاتصال بقاعدة البيانات
$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    die("فشل الاتصال بقاعدة البيانات: " . $conn->connect_error);
}

// ضبط الترميز لدعم اللغة العربية
$conn->set_charset("utf8mb4");

// كود إنشاء الجداول
$sql = "
DROP TABLE IF EXISTS referrals;
DROP TABLE IF EXISTS withdrawal_requests;
DROP TABLE IF EXISTS notifications;
DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS platform_settings;
DROP TABLE IF EXISTS vip_levels;
DROP TABLE IF EXISTS investments;
DROP TABLE IF EXISTS transactions;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    deposit_balance DECIMAL(20, 8) NOT NULL DEFAULT 0.00000000,
    profit_balance DECIMAL(20, 8) NOT NULL DEFAULT 0.00000000,
    vip_level ENUM('bronze', 'silver', 'gold', 'platinum', 'diamond') NOT NULL DEFAULT 'bronze',
    phone VARCHAR(20) NULL,
    country VARCHAR(50) NULL,
    referral_code VARCHAR(20) NULL UNIQUE,
    referred_by INT NULL,
    is_active BOOLEAN NOT NULL DEFAULT TRUE,
    email_verified BOOLEAN NOT NULL DEFAULT FALSE,
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_users_email (email),
    INDEX idx_users_vip_level (vip_level),
    INDEX idx_users_is_active (is_active),
    INDEX idx_users_referral_code (referral_code),
    INDEX idx_users_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    transaction_reference VARCHAR(50) NOT NULL UNIQUE,
    amount DECIMAL(20, 8) NOT NULL,
    amount_usd DECIMAL(20, 2) NOT NULL,
    type ENUM('deposit', 'withdrawal', 'investment', 'profit', 'bonus', 'fee') NOT NULL,
    category ENUM('crypto', 'fiat', 'internal') NOT NULL DEFAULT 'crypto',
    status ENUM('pending', 'approved', 'rejected', 'completed', 'failed') NOT NULL DEFAULT 'pending',
    payment_method VARCHAR(50) NULL,
    payment_address VARCHAR(255) NULL,
    transaction_hash VARCHAR(255) NULL,
    description TEXT NULL,
    admin_notes TEXT NULL,
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_transactions_user_id (user_id),
    INDEX idx_transactions_type (type),
    INDEX idx_transactions_status (status),
    INDEX idx_transactions_transaction_reference (transaction_reference),
    INDEX idx_transactions_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE investments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    investment_reference VARCHAR(50) NOT NULL UNIQUE,
    plan_name VARCHAR(100) NOT NULL,
    plan_type ENUM('fixed', 'flexible', 'staked', 'compound') NOT NULL DEFAULT 'fixed',
    amount DECIMAL(20, 8) NOT NULL,
    amount_usd DECIMAL(20, 2) NOT NULL,
    profit_rate DECIMAL(5, 2) NOT NULL,
    expected_profit DECIMAL(20, 8) NOT NULL,
    actual_profit DECIMAL(20, 8) NOT NULL DEFAULT 0.00000000,
    duration_days INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('pending', 'active', 'completed', 'cancelled', 'failed') NOT NULL DEFAULT 'pending',
    profit_paid BOOLEAN NOT NULL DEFAULT FALSE,
    auto_renew BOOLEAN NOT NULL DEFAULT FALSE,
    notes TEXT NULL,
    admin_notes TEXT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    completed_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_investments_user_id (user_id),
    INDEX idx_investments_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE vip_levels (
    id INT AUTO_INCREMENT PRIMARY KEY,
    level_name ENUM('bronze', 'silver', 'gold', 'platinum', 'diamond') NOT NULL UNIQUE,
    min_deposit DECIMAL(20, 8) NOT NULL DEFAULT 0.00000000,
    bonus_rate DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    withdrawal_limit DECIMAL(20, 8) NOT NULL DEFAULT 0.00000000,
    profit_boost DECIMAL(5, 2) NOT NULL DEFAULT 0.00,
    priority_support BOOLEAN NOT NULL DEFAULT FALSE,
    early_access BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE platform_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(50) NOT NULL UNIQUE,
    setting_value TEXT NOT NULL,
    setting_type ENUM('string', 'integer', 'decimal', 'boolean', 'json') NOT NULL DEFAULT 'string',
    description TEXT NULL,
    is_public BOOLEAN NOT NULL DEFAULT FALSE,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    action VARCHAR(100) NOT NULL,
    details JSON NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_activity_logs_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') NOT NULL DEFAULT 'info',
    is_read BOOLEAN NOT NULL DEFAULT FALSE,
    link VARCHAR(255) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    read_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notifications_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE withdrawal_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    transaction_id INT NOT NULL,
    amount DECIMAL(20, 8) NOT NULL,
    amount_usd DECIMAL(20, 2) NOT NULL,
    wallet_address VARCHAR(255) NOT NULL,
    wallet_type VARCHAR(50) NOT NULL,
    status ENUM('pending', 'processing', 'completed', 'rejected') NOT NULL DEFAULT 'pending',
    admin_notes TEXT NULL,
    processed_by INT NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_withdrawal_requests_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    referrer_id INT NOT NULL,
    referred_id INT NOT NULL,
    bonus_amount DECIMAL(20, 8) NOT NULL DEFAULT 0.00000000,
    bonus_paid BOOLEAN NOT NULL DEFAULT FALSE,
    status ENUM('pending', 'active', 'completed') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referred_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_referrals_referrer_id (referrer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

// إدخال البيانات الافتراضية
INSERT INTO vip_levels (level_name, min_deposit, bonus_rate, withdrawal_limit, profit_boost, priority_support, early_access) VALUES
('bronze', 0.00000000, 0.00, 1000.00000000, 0.00, FALSE, FALSE),
('silver', 1000.00000000, 2.00, 5000.00000000, 2.00, FALSE, FALSE),
('gold', 5000.00000000, 5.00, 15000.00000000, 5.00, TRUE, FALSE),
('platinum', 15000.00000000, 10.00, 50000.00000000, 10.00, TRUE, TRUE),
('diamond', 50000.00000000, 15.00, 100000.00000000, 15.00, TRUE, TRUE);

INSERT INTO platform_settings (setting_key, setting_value, setting_type, description, is_public) VALUES
('site_name', 'Crypto Investment Platform', 'string', 'اسم الموقع', TRUE),
('site_description', 'منصة استثمارية متخصصة في العملات الرقمية', 'string', 'وصف الموقع', TRUE),
('default_profit_rate', '10.00', 'decimal', 'نسبة الربح الافتراضية', TRUE),
('min_deposit', '10.00', 'decimal', 'الحد الأدنى للإيداع', TRUE),
('max_deposit', '100000.00', 'decimal', 'الحد الأقصى للإيداع', TRUE),
('min_withdrawal', '5.00', 'decimal', 'الحد الأدنى للسحب', TRUE),
('max_withdrawal_daily', '10000.00', 'decimal', 'الحد الأقصى للسحب اليومي', TRUE),
('referral_bonus', '5.00', 'decimal', 'نسبة مكافأة الإحالة', TRUE),
('maintenance_mode', 'false', 'boolean', 'وضع الصيانة', FALSE);

INSERT INTO users (full_name, email, password_hash, vip_level, is_active, email_verified) VALUES
('مدير النظام', 'admin@cryptoinvest.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'diamond', TRUE, TRUE);
";

// تنفيذ جميع الاستعلامات
if ($conn->multi_query($sql)) {
    echo "<div style='font-family: sans-serif; text-align: center; padding: 50px;'>";
    echo "<h1 style='color: #2e7d32;'>تم إنشاء جميع الجداول والبيانات بنجاح! 🎉</h1>";
    echo "<p>قاعدة البيانات الآن جاهزة للعمل على Render.</p>";
    echo "</div>";
} else {
    echo "<h1>حدث خطأ أثناء إعداد قاعدة البيانات:</h1> " . $conn->error;
}

$conn->close();
?>
