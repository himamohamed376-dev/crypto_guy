<?php
/**
 * =============================================
 * ملف الاتصال بقاعدة البيانات - Aiven + Render
 * Database Connection File - Aiven + Render
 * =============================================
 * 
 * هذا الملف يتصل بقاعدة بيانات MySQL على Aiven.io
 * عبر سيرفر Render.com باستخدام mysqli مع SSL
 * 
 * @package CryptoInvestmentPlatform
 * @author Your Team
 * @version 1.0.0
 */

// =============================================
// 1. قراءة متغيرات البيئة من Render
// =============================================

/**
 * الحصول على متغيرات البيئة مع قيم افتراضية آمنة
 */
$db_host = getenv('DB_HOST');
$db_port = getenv('DB_PORT');
$db_user = getenv('DB_USER');
$db_password = getenv('DB_PASSWORD');
$db_name = getenv('DB_NAME');

// التحقق من وجود المتغيرات الأساسية
if (empty($db_host) || empty($db_user) || empty($db_password) || empty($db_name)) {
    // تسجيل الخطأ في سجلات الخادم
    error_log('❌ خطأ: متغيرات البيئة غير مكتملة!');
    error_log("DB_HOST: " . ($db_host ? '✅' : '❌'));
    error_log("DB_USER: " . ($db_user ? '✅' : '❌'));
    error_log("DB_PASSWORD: " . ($db_password ? '✅' : '❌'));
    error_log("DB_NAME: " . ($db_name ? '✅' : '❌'));
    
    // عرض رسالة خطأ للمستخدم (آمنة)
    die('⚠️ عذراً، حدث خطأ في تكوين قاعدة البيانات. يرجى المحاولة لاحقاً.');
}

// تحويل المنفذ إلى رقم صحيح
$db_port = intval($db_port) ?: 3306; // استخدام 3306 كقيمة افتراضية إذا كان المنفذ غير صالح

// =============================================
// 2. إعدادات SSL/TLS للاتصال بـ Aiven
// =============================================

/**
 * مسارات شهادات SSL (يمكنك تحميلها من Aiven Console)
 * أو استخدام ملفات الشهادات المرفقة مع المشروع
 */
$ssl_ca = __DIR__ . '/certs/ca.pem';        // شهادة CA من Aiven
$ssl_cert = __DIR__ . '/certs/client-cert.pem'; // شهادة العميل (اختياري)
$ssl_key = __DIR__ . '/certs/client-key.pem';   // مفتاح العميل (اختياري)

// التحقق من وجود شهادة CA
$ssl_enabled = file_exists($ssl_ca);

// =============================================
// 3. إنشاء اتصال mysqli مع SSL
// =============================================

/**
 * متغيرات الاتصال
 */
$connection = null;
$error = null;

try {
    // إنشاء اتصال mysqli جديد
    $connection = mysqli_init();
    
    if (!$connection) {
        throw new Exception('فشل في تهيئة mysqli');
    }
    
    // ===== إعدادات SSL =====
    if ($ssl_enabled) {
        // تعيين خيارات SSL
        $connection->ssl_set(
            $ssl_key,   // مفتاح العميل (أو NULL)
            $ssl_cert,  // شهادة العميل (أو NULL)
            $ssl_ca,    // شهادة CA
            NULL,       // مسار المفتاح الخاص (للمصادقة المتبادلة)
            NULL        // كلمة مرور المفتاح الخاص (اختياري)
        );
        
        // تمكين SSL
        $connection->options(MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, true);
        
        error_log('✅ تم تمكين SSL للاتصال بقاعدة البيانات');
    } else {
        error_log('⚠️ تحذير: لم يتم العثور على شهادات SSL. سيتم استخدام اتصال غير مشفر.');
    }
    
    // ===== إعدادات إضافية =====
    // تعيين المهلة (timeout)
    $connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 30);
    $connection->options(MYSQLI_OPT_READ_TIMEOUT, 30);
    
    // تعيين الترميز إلى UTF8MB4
    $connection->options(MYSQLI_SET_CHARSET_NAME, 'utf8mb4');
    
    // ===== محاولة الاتصال =====
    $connected = $connection->real_connect(
        $db_host,
        $db_user,
        $db_password,
        $db_name,
        $db_port,
        null, // socket (NULL للاتصال عبر TCP/IP)
        MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT // خيار SSL (اختياري)
    );
    
    // التحقق من نجاح الاتصال
    if (!$connected) {
        throw new Exception('فشل الاتصال بقاعدة البيانات: ' . $connection->connect_error);
    }
    
    // ===== إعدادات بعد الاتصال =====
    // تعيين الترميز
    if (!$connection->set_charset('utf8mb4')) {
        error_log('⚠️ تحذير: فشل في تعيين الترميز utf8mb4: ' . $connection->error);
    }
    
    // تعيين المنطقة الزمنية
    $timezoneQuery = "SET time_zone = '+00:00'";
    if (!$connection->query($timezoneQuery)) {
        error_log('⚠️ تحذير: فشل في تعيين المنطقة الزمنية: ' . $connection->error);
    }
    
    // ===== تسجيل نجاح الاتصال =====
    error_log('✅ تم الاتصال بقاعدة البيانات بنجاح!');
    error_log("   📊 قاعدة البيانات: {$db_name}");
    error_log("   🌐 المضيف: {$db_host}:{$db_port}");
    error_log("   🔒 SSL: " . ($ssl_enabled ? 'مفعل ✅' : 'غير مفعل ⚠️'));
    
} catch (Exception $e) {
    // ===== معالجة الأخطاء =====
    $error = $e->getMessage();
    
    // تسجيل الخطأ مع تفاصيل إضافية
    error_log('❌ خطأ في الاتصال بقاعدة البيانات:');
    error_log("   📝 رسالة: " . $error);
    error_log("   🔍 المضيف: {$db_host}:{$db_port}");
    error_log("   👤 المستخدم: {$db_user}");
    error_log("   📊 قاعدة البيانات: {$db_name}");
    error_log("   🔒 SSL: " . ($ssl_enabled ? 'مفعل' : 'غير مفعل'));
    
    // إغلاق الاتصال إذا كان مفتوحاً
    if ($connection) {
        @$connection->close();
        $connection = null;
    }
    
    // عرض رسالة آمنة للمستخدم
    die('⚠️ عذراً، لا يمكن الاتصال بقاعدة البيانات في الوقت الحالي. يرجى المحاولة لاحقاً.');
}

// =============================================
// 4. اختبار الاتصال (اختياري - يمكن إزالته في الإنتاج)
// =============================================

/**
 * دالة اختبار بسيطة للتحقق من الاتصال
 * يمكنك إزالة هذا القسم في الإنتاج
 */
function testConnection($conn) {
    try {
        $result = $conn->query("SELECT '✅ الاتصال ناجح' as message, NOW() as server_time, VERSION() as version");
        if ($result && $row = $result->fetch_assoc()) {
            error_log('🧪 اختبار الاتصال: ' . $row['message']);
            error_log('   🕐 وقت الخادم: ' . $row['server_time']);
            error_log('   📦 إصدار MySQL: ' . $row['version']);
        }
        return true;
    } catch (Exception $e) {
        error_log('⚠️ فشل اختبار الاتصال: ' . $e->getMessage());
        return false;
    }
}

// تنفيذ اختبار الاتصال (يمكن إزالة التعليق في بيئة التطوير)
// testConnection($connection);

// =============================================
// 5. تصدير الاتصال للاستخدام في ملفات أخرى
// =============================================

/**
 * إرجاع كائن الاتصال للاستخدام في باقي الملفات
 */
return $connection;

// =============================================
// 6. دالة مساعدة لإغلاق الاتصال (اختياري)
// =============================================

/**
 * دالة لإغلاق الاتصال بقاعدة البيانات بشكل آمن
 */
function closeDatabaseConnection() {
    global $connection;
    if ($connection) {
        @$connection->close();
        $connection = null;
        error_log('🔒 تم إغلاق الاتصال بقاعدة البيانات');
    }
}

// تسجيل دالة الإغلاق عند انتهاء النص البرمجي
register_shutdown_function('closeDatabaseConnection');

// =============================================
// 7. معلومات إضافية للمطورين
// =============================================

/**
 * عرض معلومات الاتصال (للتصحيح فقط)
 * قم بتعليق هذه الأسطر في الإنتاج
 */
if (getenv('APP_ENV') === 'development') {
    error_log('📋 ===== معلومات الاتصال =====');
    error_log("📌 المضيف: {$db_host}");
    error_log("📌 المنفذ: {$db_port}");
    error_log("📌 قاعدة البيانات: {$db_name}");
    error_log("📌 المستخدم: {$db_user}");
    error_log("📌 SSL: " . ($ssl_enabled ? 'مفعل' : 'غير مفعل'));
    error_log("📌 مسار الشهادة: " . ($ssl_enabled ? $ssl_ca : 'غير موجود'));
    error_log('📋 =============================');
}

// =============================================
// 8. نهاية الملف
// =============================================

?>
