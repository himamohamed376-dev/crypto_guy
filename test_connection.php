<?php
/**
 * ملف اختبار الاتصال بقاعدة البيانات
 * استخدم هذا الملف للتحقق من اتصالك قبل رفع الموقع
 * 
 * ⚠️ ملاحظة: احذف هذا الملف بعد التأكد من نجاح الاتصال
 */

// بدء تسجيل الأخطاء
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo '<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>اختبار الاتصال بقاعدة البيانات</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
        .success { color: green; border-right: 4px solid green; padding: 15px; background: #f0fff4; margin: 10px 0; }
        .error { color: red; border-right: 4px solid red; padding: 15px; background: #fff5f5; margin: 10px 0; }
        .info { color: blue; border-right: 4px solid blue; padding: 15px; background: #f0f8ff; margin: 10px 0; }
        .box { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; font-family: monospace; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        td:first-child { font-weight: bold; color: #555; }
        h1 { color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔌 اختبار الاتصال بقاعدة البيانات</h1>
        <hr>';

try {
    // استيراد ملف الاتصال
    $db = require_once 'setup_db.php';
    
    if ($db && $db instanceof mysqli) {
        echo '<div class="success">✅ تم الاتصال بقاعدة البيانات بنجاح!</div>';
        
        // عرض معلومات الاتصال
        echo '<h3>📊 معلومات الاتصال</h3>';
        echo '<div class="box">';
        echo "<table>";
        echo "<tr><td>المضيف</td><td>" . htmlspecialchars(getenv('DB_HOST')) . "</td></tr>";
        echo "<tr><td>المنفذ</td><td>" . htmlspecialchars(getenv('DB_PORT')) . "</td></tr>";
        echo "<tr><td>قاعدة البيانات</td><td>" . htmlspecialchars(getenv('DB_NAME')) . "</td></tr>";
        echo "<tr><td>المستخدم</td><td>" . htmlspecialchars(getenv('DB_USER')) . "</td></tr>";
        echo "<tr><td>إصدار MySQL</td><td>" . $db->server_info . "</td></tr>";
        echo "<tr><td>حالة SSL</td><td>" . ($db->ssl_cipher ? '🔒 مفعل' : '🔓 غير مفعل') . "</td></tr>";
        echo "</table>";
        echo '</div>';
        
        // اختبار استعلام بسيط
        echo '<h3>🧪 اختبار الاستعلام</h3>';
        $result = $db->query("SELECT NOW() as now, VERSION() as version, DATABASE() as current_db, USER() as current_user");
        
        if ($result) {
            $row = $result->fetch_assoc();
            echo '<div class="info">✅ الاستعلام ناجح</div>';
            echo '<div class="box">';
            echo "<table>";
            echo "<tr><td>وقت الخادم</td><td>" . $row['now'] . "</td></tr>";
            echo "<tr><td>إصدار MySQL</td><td>" . $row['version'] . "</td></tr>";
            echo "<tr><td>قاعدة البيانات الحالية</td><td>" . $row['current_db'] . "</td></tr>";
            echo "<tr><td>المستخدم الحالي</td><td>" . $row['current_user'] . "</td></tr>";
            echo "</table>";
            echo '</div>';
            $result->free();
        }
        
        // عرض جميع الجداول
        echo '<h3>📋 الجداول في قاعدة البيانات</h3>';
        $tables = $db->query("SHOW TABLES");
        
        if ($tables && $tables->num_rows > 0) {
            echo '<div class="box">';
            echo '<ul>';
            while ($row = $tables->fetch_row()) {
                echo "<li>📄 " . htmlspecialchars($row[0]) . "</li>";
            }
            echo '</ul>';
            echo '</div>';
            $tables->free();
        } else {
            echo '<div class="info">ℹ️ لا توجد جداول في قاعدة البيانات</div>';
        }
        
        // إغلاق الاتصال
        $db->close();
        echo '<div class="info">🔒 تم إغلاق الاتصال بقاعدة البيانات</div>';
        
    } else {
        echo '<div class="error">❌ فشل في إنشاء اتصال mysqli</div>';
    }
    
} catch (Exception $e) {
    echo '<div class="error">❌ خطأ: ' . htmlspecialchars($e->getMessage()) . '</div>';
    echo '<div class="info">📝 تأكد من:';
    echo '<ul>';
    echo '<li>متغيرات البيئة مضبوطة بشكل صحيح في Render</li>';
    echo '<li>مضيف Aiven صحيح (يجب أن ينتهي بـ .aivencloud.com)</li>';
    echo '<li>المنفذ صحيح (يجب أن يكون رقمياً)</li>';
    echo '<li>شهادة SSL موجودة في مجلد certs/ (إذا كنت تستخدم SSL)</li>';
    echo '</ul></div>';
}

echo '<hr>';
echo '<div style="text-align: center; color: #888; margin-top: 20px;">';
echo '🚀 تم الاختبار في ' . date('Y-m-d H:i:s');
echo '</div>';

echo '</div></body></html>';
?>
