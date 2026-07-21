<?php
/**
 * اختبار الاتصال بين Render و Aiven
 * شغل الملف من المتصفح: https://your-app.onrender.com/test_connection.php
 */

echo "<h1>🔌 اختبار اتصال قاعدة البيانات</h1>";
echo "<hr>";

// ===== 1. عرض متغيرات البيئة =====
echo "<h3>📋 متغيرات البيئة في Render:</h3>";
$vars = ['DB_HOST', 'DB_PORT', 'DB_USER', 'DB_NAME', 'DB_PASSWORD'];
echo "<ul>";
foreach ($vars as $var) {
    $value = getenv($var);
    if ($var == 'DB_PASSWORD') {
        $value = $value ? '✅ موجودة (مخفية)' : '❌ غير موجودة';
    }
    echo "<li><strong>$var:</strong> " . ($value ?: '❌ غير موجود') . "</li>";
}
echo "</ul>";

// ===== 2. محاولة الاتصال بقاعدة البيانات =====
echo "<h3>🔄 محاولة الاتصال بـ Aiven...</h3>";

$host = getenv('DB_HOST');
$port = getenv('DB_PORT');
$user = getenv('DB_USER');
$password = getenv('DB_PASSWORD');
$database = getenv('DB_NAME');

try {
    // إنشاء اتصال
    $conn = new mysqli($host, $user, $password, $database, (int)$port);
    
    if ($conn->connect_error) {
        throw new Exception("❌ فشل الاتصال: " . $conn->connect_error);
    }
    
    echo "<div style='background:#d4edda; padding:15px; border-radius:10px; color:#155724;'>";
    echo "✅ <strong>تم الاتصال بقاعدة البيانات بنجاح!</strong><br>";
    echo "📦 إصدار MySQL: " . $conn->server_info . "<br>";
    echo "🔒 SSL: " . ($conn->ssl_cipher ? '✅ مفعل' : '❌ غير مفعل');
    echo "</div>";
    
    // ===== 3. عرض الجداول =====
    echo "<h3>📊 الجداول في قاعدة البيانات:</h3>";
    $result = $conn->query("SHOW TABLES");
    
    if ($result && $result->num_rows > 0) {
        echo "<ul>";
        $tables = [];
        while ($row = $result->fetch_row()) {
            $tables[] = $row[0];
            echo "<li>📄 " . $row[0] . "</li>";
        }
        echo "</ul>";
        echo "<p>✅ عدد الجداول: " . count($tables) . "</p>";
        
        // عرض محتوى جدول users
        if (in_array('users', $tables)) {
            echo "<h3>👤 اختبار جدول users:</h3>";
            $users = $conn->query("SELECT id, full_name, email, vip_level FROM users LIMIT 5");
            if ($users && $users->num_rows > 0) {
                echo "<table border='1' cellpadding='8' style='border-collapse: collapse;'>";
                echo "<tr><th>ID</th><th>الاسم</th><th>البريد</th><th>VIP</th></tr>";
                while ($row = $users->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['full_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['email']) . "</td>";
                    echo "<td>" . $row['vip_level'] . "</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>⚠️ لا يوجد مستخدمين في الجدول</p>";
            }
        }
        
    } else {
        echo "<p>⚠️ لا توجد جداول في قاعدة البيانات</p>";
        echo "<p>📝 قم بتشغيل <strong>install_tables.php</strong> لإنشاء الجداول</p>";
    }
    
    // ===== 4. إغلاق الاتصال =====
    $conn->close();
    echo "<p>🔒 تم إغلاق الاتصال.</p>";
    
} catch (Exception $e) {
    echo "<div style='background:#f8d7da; padding:15px; border-radius:10px; color:#721c24;'>";
    echo "❌ <strong>خطأ:</strong> " . $e->getMessage() . "<br>";
    echo "<br>💡 <strong>حلول مقترحة:</strong>";
    echo "<ul>";
    echo "<li>تأكد من صحة المتغيرات في Render</li>";
    echo "<li>تأكد من أن قاعدة البيانات على Aiven شغالة</li>";
    echo "<li>تأكد من أن عنوان المضيف صحيح (ينتهي بـ .aivencloud.com)</li>";
    echo "<li>تأكد من أن كلمة السر صحيحة</li>";
    echo "</ul>";
    echo "</div>";
}
?>س
