<?php
/**
 * ملف الاتصال بقاعدة البيانات - Aiven + Render
 */

// قراءة متغيرات البيئة من Render
$db_host = getenv('DB_HOST');
$db_port = intval(getenv('DB_PORT'));
$db_user = getenv('DB_USER');
$db_password = getenv('DB_PASSWORD');
$db_name = getenv('DB_NAME');

// التحقق من وجود المتغيرات
if (!$db_host || !$db_user || !$db_password || !$db_name) {
    die("⚠️ متغيرات البيئة غير مكتملة!");
}

try {
    // إنشاء اتصال mysqli
    $mysqli = new mysqli($db_host, $db_user, $db_password, $db_name, $db_port);
    
    if ($mysqli->connect_error) {
        throw new Exception("فشل الاتصال: " . $mysqli->connect_error);
    }
    
    $mysqli->set_charset('utf8mb4');
    
    // تسجيل نجاح الاتصال
    error_log("✅ تم الاتصال بقاعدة البيانات: {$db_name}");
    
    return $mysqli;
    
} catch (Exception $e) {
    error_log("❌ خطأ في قاعدة البيانات: " . $e->getMessage());
    die("⚠️ عذراً، لا يمكن الاتصال بقاعدة البيانات.");
}
?>
