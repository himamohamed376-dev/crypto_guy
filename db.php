<?php
/**
 * ملف الاتصال بقاعدة البيانات PostgreSQL على Aiven.io
 * نسخة مبسطة بدون شهادة SSL (تستخدم sslmode=require فقط)
 */

// ===== متغيرات الاتصال من Render Environment =====
$host = getenv('DB_HOST') ?: 'your-host.aivencloud.com';
$port = getenv('DB_PORT') ?: '12345';
$dbname = getenv('DB_NAME') ?: 'defaultdb';
$user = getenv('DB_USER') ?: 'avnadmin';
$password = getenv('DB_PASSWORD') ?: 'your-password';

try {
    // ===== إنشاء اتصال PDO مع SSL =====
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    
    $pdo = new PDO($dsn, $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 30,
    ]);
    
    // تعيين الترميز
    $pdo->exec("SET NAMES 'UTF8'");
    $pdo->exec("SET client_encoding TO 'UTF8'");
    
    // إرجاع الاتصال للاستخدام
    return $pdo;
    
} catch (PDOException $e) {
    // تسجيل الخطأ
    error_log("❌ فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
    
    // عرض رسالة خطأ للمستخدم
    die("⚠️ عذراً، لا يمكن الاتصال بقاعدة البيانات في الوقت الحالي.");
}
?>
