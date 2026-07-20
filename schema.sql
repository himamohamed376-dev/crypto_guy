-- =============================================
-- إنشاء جدول المستخدمين
-- =============================================

-- حذف الجدول إذا كان موجوداً (اختياري)
-- DROP TABLE IF EXISTS users;

-- إنشاء الجدول
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    phone VARCHAR(20),
    balance DECIMAL(15,2) DEFAULT 0.00,
    role VARCHAR(20) DEFAULT 'user'
);

-- إضافة فهرس للبريد الإلكتروني لتسريع البحث
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);

-- إضافة مستخدم أدمن تجريبي (اختياري)
-- INSERT INTO users (name, email, password, role) 
-- VALUES ('Admin', 'admin@example.com', '$2y$10$YourHashedPassword', 'admin');

-- عرض هيكل الجدول
-- \d users;

-- عرض جميع المستخدمين
-- SELECT * FROM users;
