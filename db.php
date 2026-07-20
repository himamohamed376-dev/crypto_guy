<?php
/**
 * ملف الاتصال بقاعدة البيانات PostgreSQL على Aiven.io
 * باستخدام PDO و sslmode=require
 */

// ===== متغيرات الاتصال =====
$host = 'your-host.aivencloud.com';        // ضع هنا الهوست من Aiven
$port = '12345';                           // ضع هنا البورت من Aiven
$dbname = 'defaultdb';                     // اسم قاعدة البيانات
$user = 'avnadmin';                        // اسم المستخدم
$password = 'your-password';               // كلمة المرور من Aiven

// ===== إعدادات SSL =====
$ssl_ca = '/path/to/ca.pem'; // مسار شهادة SSL (اختياري، يمكنك تعطيله)

try {
    // ===== إنشاء اتصال PDO مع SSL =====
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
    
    // خيارات PDO
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 30,
    ];
    
    // إضافة خيارات SSL إذا كانت الشهادة موجودة
    if (file_exists($ssl_ca)) {
        $options[PDO::PGSQL_ATTR_SSL_CA] = $ssl_ca;
    }
    
    // إنشاء الاتصال
    $pdo = new PDO($dsn, $user, $password, $options);
    
    // تعيين الترميز إلى UTF-8
    $pdo->exec("SET NAMES 'UTF8'");
    $pdo->exec("SET client_encoding TO 'UTF8'");
    
    // رسالة نجاح (يمكنك إزالتها في الإنتاج)
    // echo "✅ تم الاتصال بقاعدة البيانات بنجاح";
    
} catch (PDOException $e) {
    // في حالة فشل الاتصال
    error_log("❌ فشل الاتصال بقاعدة البيانات: " . $e->getMessage());
    die("⚠️ عذراً، حدث خطأ في الاتصال بقاعدة البيانات. يرجى المحاولة لاحقاً.");
}

/**
 * دالة مساعدة لتنفيذ استعلامات آمنة
 */
function executeQuery($pdo, $sql, $params = []) {
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("❌ خطأ في الاستعلام: " . $e->getMessage());
        return false;
    }
}

/**
 * دالة للحصول على اتصال قاعدة البيانات
 */
function getDBConnection() {
    global $pdo;
    return $pdo;
}

// تصدير المتغيرات للاستخدام في ملفات أخرى
return $pdo;
?>
