-- إنشاء قاعدة البيانات
CREATE DATABASE IF NOT EXISTS investment_platform;
USE investment_platform;

-- جدول المستخدمين
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    balance DECIMAL(15,2) DEFAULT 0.00,
    role ENUM('user', 'admin') DEFAULT 'user',
    two_factor_enabled BOOLEAN DEFAULT FALSE,
    two_factor_secret VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- جدول الاستثمارات
CREATE TABLE investments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    category VARCHAR(100),
    target_amount DECIMAL(15,2) NOT NULL,
    current_amount DECIMAL(15,2) DEFAULT 0.00,
    min_investment DECIMAL(10,2) NOT NULL,
    expected_return DECIMAL(5,2) NOT NULL, -- النسبة المئوية للعائد
    duration_months INT NOT NULL,
    risk_level ENUM('low', 'medium', 'high') DEFAULT 'medium',
    image_url VARCHAR(500),
    status ENUM('active', 'completed', 'cancelled') DEFAULT 'active',
    start_date DATE,
    end_date DATE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- جدول عمليات المستثمرين
CREATE TABLE user_investments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    investment_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    shares INT DEFAULT 1,
    status ENUM('pending', 'active', 'completed', 'cancelled') DEFAULT 'pending',
    profit_earned DECIMAL(15,2) DEFAULT 0.00,
    invested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    matured_at TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (investment_id) REFERENCES investments(id)
);

-- جدول المعاملات المالية
CREATE TABLE transactions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('deposit', 'withdraw', 'investment', 'profit') NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    balance_after DECIMAL(15,2) NOT NULL,
    description VARCHAR(255),
    reference_id INT,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- جدول الإشعارات
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info', 'success', 'warning', 'error') DEFAULT 'info',
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- إدخال بيانات تجريبية
INSERT INTO users (full_name, email, phone, password_hash, role) VALUES
('مدير النظام', 'admin@invest.com', '01000000000', '$2b$10$YourHashedPasswordHere', 'admin');

-- إدخال فرص استثمارية تجريبية
INSERT INTO investments (title, description, category, target_amount, min_investment, expected_return, duration_months, risk_level, status, start_date, end_date) VALUES
('مشروع الطاقة الشمسية', 'استثمار في محطة طاقة شمسية صديقة للبيئة بعائد شهري', 'طاقة متجددة', 500000, 5000, 15.50, 24, 'low', 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 24 MONTH)),
('تطوير عقاري سكني', 'مشروع بناء مجمع سكني فاخر في منطقة استراتيجية', 'عقارات', 2000000, 10000, 22.00, 36, 'medium', 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 36 MONTH)),
('منصة تعليمية إلكترونية', 'تطوير منصة تعليمية تفاعلية بالذكاء الاصطناعي', 'تكنولوجيا', 300000, 3000, 18.75, 18, 'medium', 'active', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 18 MONTH));
