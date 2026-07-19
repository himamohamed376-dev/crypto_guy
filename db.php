<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ضع هنا البيانات الخمسة الخاصة بـ Aiven التي قمت بنسخها
$host = 'رابط_الـ_Host_الطويل_هنا'; 
$db   = 'defaultdb'; // اسم قاعدة البيانات الافتراضي
$user = 'avnadmin';  // اسم المستخدم الافتراضي
$pass = 'كلمة_المرور_القوية_الخاصة_بك'; 
$port = 'رقم_البورت_الخاص_بك'; 
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $conn = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     die("خطأ في الاتصال بقاعدة البيانات: " . $e->getMessage());
}
?>
