-- =============================================
-- منصة استثمارية Crypto - قاعدة البيانات
-- Crypto Investment Platform - Database Schema
-- =============================================

-- =============================================
-- 1. إعدادات قاعدة البيانات
-- =============================================

-- إنشاء قاعدة البيانات (اختياري - يمكنك تخطيه إذا كانت قاعدة البيانات موجودة مسبقاً)
CREATE DATABASE IF NOT EXISTS crypto_investment_platform
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

-- استخدام قاعدة البيانات
USE crypto_investment_platform;

-- =============================================
-- 2. جدول المستخدمين (users)
-- =============================================

DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'المعرف الفريد للمستخدم',
    full_name VARCHAR(100) NOT NULL COMMENT 'الاسم الكامل للمستخدم',
    email VARCHAR(100) NOT NULL UNIQUE COMMENT 'البريد الإلكتروني (فريد)',
    password_hash VARCHAR(255) NOT NULL COMMENT 'كلمة المرور المشفرة (bcrypt)',
    deposit_balance DECIMAL(20, 8) NOT NULL DEFAULT 0.00000000 COMMENT 'رصيد الإيداعات (العملات الرقمية)',
    profit_balance DECIMAL(20, 8) NOT NULL DEFAULT 0.00000000 COMMENT 'رصيد الأرباح (العملات الرقمية)',
    vip_level ENUM('bronze', 'silver', 'gold', 'platinum', 'diamond') NOT NULL DEFAULT 'bronze' COMMENT 'مستوى VIP',
    phone VARCHAR(20) NULL COMMENT 'رقم الهاتف',
    country VARCHAR(50) NULL COMMENT 'الدولة',
    referral_code VARCHAR(20) NULL UNIQUE COMMENT 'كود الإحالة الفريد',
    referred_by INT NULL COMMENT 'معرف المستخدم الذي أحاله',
    is_active BOOLEAN NOT NULL DEFAULT TRUE COMMENT 'حالة الحساب (نشط/غير نشط)',
    email_verified BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'تم التحقق من البريد الإلكتروني',
    last_login TIMESTAMP NULL COMMENT 'آخر تاريخ تسجيل دخول',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاريخ الإنشاء',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'تاريخ آخر تحديث',
    
    -- المفاتيح الأجنبية
    FOREIGN KEY (referred_by) REFERENCES users(id) ON DELETE SET NULL,
    
    -- الفهارس
    INDEX idx_users_email (email),
    INDEX idx_users_vip_level (vip_level),
    INDEX idx_users_is_active (is_active),
    INDEX idx_users_referral_code (referral_code),
    INDEX idx_users_created_at (created_at)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول المستخدمين';

-- =============================================
-- 3. جدول المعاملات (transactions)
-- =============================================

DROP TABLE IF EXISTS transactions;

CREATE TABLE transactions (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'المعرف الفريد للمعاملة',
    user_id INT NOT NULL COMMENT 'معرف المستخدم',
    transaction_reference VARCHAR(50) NOT NULL UNIQUE COMMENT 'الرقم المرجعي للمعاملة (فريد)',
    amount DECIMAL(20, 8) NOT NULL COMMENT 'المبلغ (عملة رقمية)',
    amount_usd DECIMAL(20, 2) NOT NULL COMMENT 'المبلغ بالدولار الأمريكي',
    type ENUM('deposit', 'withdrawal', 'investment', 'profit', 'bonus', 'fee') NOT NULL COMMENT 'نوع المعاملة',
    category ENUM('crypto', 'fiat', 'internal') NOT NULL DEFAULT 'crypto' COMMENT 'فئة المعاملة',
    status ENUM('pending', 'approved', 'rejected', 'completed', 'failed') NOT NULL DEFAULT 'pending' COMMENT 'حالة المعاملة',
    payment_method VARCHAR(50) NULL COMMENT 'طريقة الدفع (BTC, ETH, USDT, Bank, Card, etc.)',
    payment_address VARCHAR(255) NULL COMMENT 'عنوان المحفظة أو تفاصيل الدفع',
    transaction_hash VARCHAR(255) NULL COMMENT 'هاش المعاملة على البلوكشين',
    description TEXT NULL COMMENT 'وصف إضافي للمعاملة',
    admin_notes TEXT NULL COMMENT 'ملاحظات المدير',
    approved_by INT NULL COMMENT 'معرف المدير الذي وافق على المعاملة',
    approved_at TIMESTAMP NULL COMMENT 'تاريخ الموافقة',
    completed_at TIMESTAMP NULL COMMENT 'تاريخ اكتمال المعاملة',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاريخ الإنشاء',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'تاريخ آخر تحديث',
    
    -- المفاتيح الأجنبية
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    
    -- الفهارس
    INDEX idx_transactions_user_id (user_id),
    INDEX idx_transactions_type (type),
    INDEX idx_transactions_status (status),
    INDEX idx_transactions_transaction_reference (transaction_reference),
    INDEX idx_transactions_created_at (created_at),
    INDEX idx_transactions_user_status (user_id, status),
    INDEX idx_transactions_type_status (type, status)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول المعاملات المالية';

-- =============================================
-- 4. جدول الاستثمارات (investments)
-- =============================================

DROP TABLE IF EXISTS investments;

CREATE TABLE investments (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'المعرف الفريد للاستثمار',
    user_id INT NOT NULL COMMENT 'معرف المستثمر',
    investment_reference VARCHAR(50) NOT NULL UNIQUE COMMENT 'الرقم المرجعي للاستثمار (فريد)',
    plan_name VARCHAR(100) NOT NULL COMMENT 'اسم خطة الاستثمار',
    plan_type ENUM('fixed', 'flexible', 'staked', 'compound') NOT NULL DEFAULT 'fixed' COMMENT 'نوع الخطة',
    amount DECIMAL(20, 8) NOT NULL COMMENT 'مبلغ الاستثمار (عملة رقمية)',
    amount_usd DECIMAL(20, 2) NOT NULL COMMENT 'مبلغ الاستثمار بالدولار الأمريكي',
    profit_rate DECIMAL(5, 2) NOT NULL COMMENT 'نسبة العائد المتوقع (مئوية)',
    expected_profit DECIMAL(20, 8) NOT NULL COMMENT 'الربح المتوقع (عملة رقمية)',
    actual_profit DECIMAL(20, 8) NOT NULL DEFAULT 0.00000000 COMMENT 'الربح الفعلي المحقق (عملة رقمية)',
    duration_days INT NOT NULL COMMENT 'مدة الاستثمار بالأيام',
    start_date DATE NOT NULL COMMENT 'تاريخ بداية الاستثمار',
    end_date DATE NOT NULL COMMENT 'تاريخ نهاية الاستثمار',
    status ENUM('pending', 'active', 'completed', 'cancelled', 'failed') NOT NULL DEFAULT 'pending' COMMENT 'حالة الاستثمار',
    profit_paid BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'هل تم دفع الأرباح',
    auto_renew BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'تجديد تلقائي',
    notes TEXT NULL COMMENT 'ملاحظات إضافية',
    admin_notes TEXT NULL COMMENT 'ملاحظات المدير',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاريخ الإنشاء',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'تاريخ آخر تحديث',
    completed_at TIMESTAMP NULL COMMENT 'تاريخ الاكتمال',
    
    -- المفاتيح الأجنبية
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- الفهارس
    INDEX idx_investments_user_id (user_id),
    INDEX idx_investments_status (status),
    INDEX idx_investments_plan_type (plan_type),
    INDEX idx_investments_start_date (start_date),
    INDEX idx_investments_end_date (end_date),
    INDEX idx_investments_investment_reference (investment_reference),
    INDEX idx_investments_user_status (user_id, status),
    INDEX idx_investments_active_dates (user_id, status, start_date, end_date)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول الاستثمارات';

-- =============================================
-- 5. جدول مستويات VIP والمميزات
-- =============================================

DROP TABLE IF EXISTS vip_levels;

CREATE TABLE vip_levels (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'المعرف الفريد',
    level_name ENUM('bronze', 'silver', 'gold', 'platinum', 'diamond') NOT NULL UNIQUE COMMENT 'اسم المستوى',
    min_deposit DECIMAL(20, 8) NOT NULL DEFAULT 0.00000000 COMMENT 'الحد الأدنى للإيداع للوصول للمستوى',
    bonus_rate DECIMAL(5, 2) NOT NULL DEFAULT 0.00 COMMENT 'نسبة المكافأة الإضافية',
    withdrawal_limit DECIMAL(20, 8) NOT NULL DEFAULT 0.00000000 COMMENT 'الحد الأقصى للسحب اليومي',
    profit_boost DECIMAL(5, 2) NOT NULL DEFAULT 0.00 COMMENT 'نسبة زيادة الأرباح',
    priority_support BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'دعم أولوية',
    early_access BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'وصول مبكر للفرص الجديدة',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاريخ الإنشاء',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'تاريخ آخر تحديث'
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول مستويات VIP والمميزات';

-- =============================================
-- 6. جدول إعدادات المنصة
-- =============================================

DROP TABLE IF EXISTS platform_settings;

CREATE TABLE platform_settings (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'المعرف الفريد',
    setting_key VARCHAR(50) NOT NULL UNIQUE COMMENT 'مفتاح الإعداد',
    setting_value TEXT NOT NULL COMMENT 'قيمة الإعداد',
    setting_type ENUM('string', 'integer', 'decimal', 'boolean', 'json') NOT NULL DEFAULT 'string' COMMENT 'نوع الإعداد',
    description TEXT NULL COMMENT 'وصف الإعداد',
    is_public BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'هل الإعداد عام (يمكن للمستخدمين رؤيته)',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاريخ الإنشاء',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'تاريخ آخر تحديث'
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول إعدادات المنصة';

-- =============================================
-- 7. جدول سجلات النشاط
-- =============================================

DROP TABLE IF EXISTS activity_logs;

CREATE TABLE activity_logs (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'المعرف الفريد',
    user_id INT NULL COMMENT 'معرف المستخدم (إذا كان مسجلاً)',
    ip_address VARCHAR(45) NULL COMMENT 'عنوان IP',
    user_agent TEXT NULL COMMENT 'معلومات المتصفح',
    action VARCHAR(100) NOT NULL COMMENT 'نوع النشاط',
    details JSON NULL COMMENT 'تفاصيل إضافية بصيغة JSON',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاريخ الإنشاء',
    
    -- المفاتيح الأجنبية
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    
    -- الفهارس
    INDEX idx_activity_logs_user_id (user_id),
    INDEX idx_activity_logs_action (action),
    INDEX idx_activity_logs_created_at (created_at)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول سجلات النشاط';

-- =============================================
-- 8. جدول الإشعارات
-- =============================================

DROP TABLE IF EXISTS notifications;

CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'المعرف الفريد',
    user_id INT NOT NULL COMMENT 'معرف المستخدم',
    title VARCHAR(200) NOT NULL COMMENT 'عنوان الإشعار',
    message TEXT NOT NULL COMMENT 'محتوى الإشعار',
    type ENUM('info', 'success', 'warning', 'error') NOT NULL DEFAULT 'info' COMMENT 'نوع الإشعار',
    is_read BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'تمت القراءة',
    link VARCHAR(255) NULL COMMENT 'رابط للانتقال',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاريخ الإنشاء',
    read_at TIMESTAMP NULL COMMENT 'تاريخ القراءة',
    
    -- المفاتيح الأجنبية
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- الفهارس
    INDEX idx_notifications_user_id (user_id),
    INDEX idx_notifications_is_read (is_read),
    INDEX idx_notifications_created_at (created_at)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول الإشعارات';

-- =============================================
-- 9. جدول طلبات السحب
-- =============================================

DROP TABLE IF EXISTS withdrawal_requests;

CREATE TABLE withdrawal_requests (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'المعرف الفريد',
    user_id INT NOT NULL COMMENT 'معرف المستخدم',
    transaction_id INT NOT NULL COMMENT 'معرف المعاملة المرتبطة',
    amount DECIMAL(20, 8) NOT NULL COMMENT 'المبلغ المطلوب سحبه',
    amount_usd DECIMAL(20, 2) NOT NULL COMMENT 'المبلغ بالدولار الأمريكي',
    wallet_address VARCHAR(255) NOT NULL COMMENT 'عنوان المحفظة',
    wallet_type VARCHAR(50) NOT NULL COMMENT 'نوع المحفظة (BTC, ETH, USDT, etc.)',
    status ENUM('pending', 'processing', 'completed', 'rejected') NOT NULL DEFAULT 'pending' COMMENT 'حالة طلب السحب',
    admin_notes TEXT NULL COMMENT 'ملاحظات المدير',
    processed_by INT NULL COMMENT 'معرف المدير الذي عالج الطلب',
    processed_at TIMESTAMP NULL COMMENT 'تاريخ المعالجة',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاريخ الإنشاء',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'تاريخ آخر تحديث',
    
    -- المفاتيح الأجنبية
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (transaction_id) REFERENCES transactions(id) ON DELETE CASCADE,
    FOREIGN KEY (processed_by) REFERENCES users(id) ON DELETE SET NULL,
    
    -- الفهارس
    INDEX idx_withdrawal_requests_user_id (user_id),
    INDEX idx_withdrawal_requests_status (status),
    INDEX idx_withdrawal_requests_created_at (created_at)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول طلبات السحب';

-- =============================================
-- 10. جدول الإحالات (Referrals)
-- =============================================

DROP TABLE IF EXISTS referrals;

CREATE TABLE referrals (
    id INT AUTO_INCREMENT PRIMARY KEY COMMENT 'المعرف الفريد',
    referrer_id INT NOT NULL COMMENT 'معرف المستخدم الذي أحال',
    referred_id INT NOT NULL COMMENT 'معرف المستخدم الجديد',
    bonus_amount DECIMAL(20, 8) NOT NULL DEFAULT 0.00000000 COMMENT 'مبلغ المكافأة',
    bonus_paid BOOLEAN NOT NULL DEFAULT FALSE COMMENT 'هل تم دفع المكافأة',
    status ENUM('pending', 'active', 'completed') NOT NULL DEFAULT 'pending' COMMENT 'حالة الإحالة',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'تاريخ الإنشاء',
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'تاريخ آخر تحديث',
    
    -- المفاتيح الأجنبية
    FOREIGN KEY (referrer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (referred_id) REFERENCES users(id) ON DELETE CASCADE,
    
    -- الفهارس
    INDEX idx_referrals_referrer_id (referrer_id),
    INDEX idx_referrals_referred_id (referred_id),
    INDEX idx_referrals_status (status)
    
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='جدول الإحالات';

-- =============================================
-- 11. إدخال البيانات الافتراضية
-- =============================================

-- إدخال مستويات VIP
INSERT INTO vip_levels (level_name, min_deposit, bonus_rate, withdrawal_limit, profit_boost, priority_support, early_access) VALUES
('bronze', 0.00000000, 0.00, 1000.00000000, 0.00, FALSE, FALSE),
('silver', 1000.00000000, 2.00, 5000.00000000, 2.00, FALSE, FALSE),
('gold', 5000.00000000, 5.00, 15000.00000000, 5.00, TRUE, FALSE),
('platinum', 15000.00000000, 10.00, 50000.00000000, 10.00, TRUE, TRUE),
('diamond', 50000.00000000, 15.00, 100000.00000000, 15.00, TRUE, TRUE);

-- إدخال إعدادات المنصة الافتراضية
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

-- إضافة مستخدم أدمن تجريبي (كلمة المرور: Admin@123)
-- سيتم تشفيرها عبر التطبيق، هنا مجرد مثال
INSERT INTO users (full_name, email, password_hash, vip_level, is_active, email_verified) VALUES
('مدير النظام', 'admin@cryptoinvest.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'diamond', TRUE, TRUE);

-- =============================================
-- 12. إنشاء إجراءات مخزنة (Stored Procedures)
-- =============================================

-- تحديث رصيد المستخدم بعد المعاملة
DROP PROCEDURE IF EXISTS update_user_balance;

DELIMITER //

CREATE PROCEDURE update_user_balance(
    IN p_user_id INT,
    IN p_amount DECIMAL(20, 8),
    IN p_type VARCHAR(20)
)
BEGIN
    IF p_type = 'deposit' THEN
        UPDATE users 
        SET deposit_balance = deposit_balance + p_amount,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = p_user_id;
    ELSEIF p_type = 'withdrawal' THEN
        UPDATE users 
        SET deposit_balance = deposit_balance - p_amount,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = p_user_id;
    ELSEIF p_type = 'profit' THEN
        UPDATE users 
        SET profit_balance = profit_balance + p_amount,
            updated_at = CURRENT_TIMESTAMP
        WHERE id = p_user_id;
    END IF;
END //

DELIMITER ;

-- تحديث مستوى VIP تلقائياً
DROP PROCEDURE IF EXISTS update_vip_level;

DELIMITER //

CREATE PROCEDURE update_vip_level(
    IN p_user_id INT
)
BEGIN
    DECLARE v_total_deposit DECIMAL(20, 8);
    DECLARE v_new_level VARCHAR(20);
    
    -- حساب إجمالي الإيداعات
    SELECT COALESCE(SUM(amount), 0) INTO v_total_deposit
    FROM transactions
    WHERE user_id = p_user_id 
        AND type = 'deposit' 
        AND status = 'approved';
    
    -- تحديد المستوى الجديد
    IF v_total_deposit >= 50000 THEN
        SET v_new_level = 'diamond';
    ELSEIF v_total_deposit >= 15000 THEN
        SET v_new_level = 'platinum';
    ELSEIF v_total_deposit >= 5000 THEN
        SET v_new_level = 'gold';
    ELSEIF v_total_deposit >= 1000 THEN
        SET v_new_level = 'silver';
    ELSE
        SET v_new_level = 'bronze';
    END IF;
    
    -- تحديث مستوى VIP
    UPDATE users 
    SET vip_level = v_new_level,
        updated_at = CURRENT_TIMESTAMP
    WHERE id = p_user_id;
END //

DELIMITER ;

-- =============================================
-- 13. إنشاء Trigger لتحديث updated_at تلقائياً
-- =============================================

-- Trigger لتحديث updated_at في جدول users
DROP TRIGGER IF EXISTS users_before_update;

DELIMITER //

CREATE TRIGGER users_before_update
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END //

DELIMITER ;

-- Trigger لتحديث updated_at في جدول transactions
DROP TRIGGER IF EXISTS transactions_before_update;

DELIMITER //

CREATE TRIGGER transactions_before_update
BEFORE UPDATE ON transactions
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END //

DELIMITER ;

-- Trigger لتحديث updated_at في جدول investments
DROP TRIGGER IF EXISTS investments_before_update;

DELIMITER //

CREATE TRIGGER investments_before_update
BEFORE UPDATE ON investments
FOR EACH ROW
BEGIN
    SET NEW.updated_at = CURRENT_TIMESTAMP;
END //

DELIMITER ;

-- Trigger لحساب الربح المتوقع تلقائياً عند إضافة استثمار
DROP TRIGGER IF EXISTS investments_before_insert;

DELIMITER //

CREATE TRIGGER investments_before_insert
BEFORE INSERT ON investments
FOR EACH ROW
BEGIN
    -- حساب الربح المتوقع
    SET NEW.expected_profit = NEW.amount * (NEW.profit_rate / 100);
    
    -- تحديد تاريخ الانتهاء
    SET NEW.end_date = DATE_ADD(NEW.start_date, INTERVAL NEW.duration_days DAY);
    
    -- تعيين الحالة الافتراضية
    IF NEW.status IS NULL THEN
        SET NEW.status = 'pending';
    END IF;
END //

DELIMITER ;

-- =============================================
-- 14. الاستعلامات المفيدة (معلق)
-- =============================================

/*
-- عرض جميع المستخدمين
SELECT * FROM users;

-- عرض معاملات المستخدم
SELECT * FROM transactions WHERE user_id = 1 ORDER BY created_at DESC;

-- عرض استثمارات المستخدم
SELECT * FROM investments WHERE user_id = 1 ORDER BY created_at DESC;

-- عرض إجمالي الإيداعات لكل مستخدم
SELECT u.id, u.full_name, u.email, 
       COALESCE(SUM(t.amount), 0) AS total_deposits
FROM users u
LEFT JOIN transactions t ON u.id = t.user_id AND t.type = 'deposit' AND t.status = 'approved'
GROUP BY u.id;

-- تحديث مستوى VIP لجميع المستخدمين
CALL update_vip_level(1);
*/

-- =============================================
-- 15. التحقق من هيكل الجداول
-- =============================================

-- عرض هيكل جميع الجداول
SHOW TABLES;

-- عرض هيكل جدول المستخدمين
DESCRIBE users;

-- عرض هيكل جدول المعاملات
DESCRIBE transactions;

-- عرض هيكل جدول الاستثمارات
DESCRIBE investments;

-- =============================================
-- انتهى كود SQL
-- =============================================
