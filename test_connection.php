<?php
/**
 * اختبار الاتصال بقاعدة البيانات
 */

echo "<h1>🔌 اختبار اتصال قاعدة البيانات</h1>";

// عرض المتغيرات
echo "<h3>📋 المتغيرات المستخدمة:</h3>";
echo "<ul>";
echo "<li><strong>DB_HOST:</strong> " . getenv('DB_HOST') . "</li>";
echo "<li><strong>DB_PORT:</strong> " . getenv('DB_PORT') . "</li>";
echo "<li><strong>DB_USER:</strong> " . getenv('DB_USER') . "</li>";
echo "<li><strong>DB_NAME:</strong> " . getenv('DB_NAME') . "</li>";
echo "<li><strong>DB_PASSWORD:</strong> " . (getenv('DB_PASSWORD') ? '✅ موجودة' : '❌ غير موجودة') . "</li>";
echo "</ul>";

// محاولة الاتصال
$conn = require_once 'config/database.php';

if ($conn) {
    echo "<div style='background:#d4edda; padding:15px; border-radius:10px;'>";
    echo "✅ <strong>تم الاتصال بنجاح!</strong><br>";
    echo "📦 إصدار MySQL: " . $conn->server_info . "<br>";
    echo "🔒 SSL: " . ($conn->ssl_cipher ? '✅ مفعل' : '❌ غير مفعل');
    echo "</div>";
    
    // عرض الجداول
    $result = $conn->query("SHOW TABLES");
    if ($result && $result->num_rows > 0) {
        echo "<h3>📊 الجداول:</h3><ul>";
        while ($row = $result->fetch_row()) {
            echo "<li>📄 " . $row[0] . "</li>";
        }
        echo "</ul>";
    }
    
    $conn->close();
}
?>
