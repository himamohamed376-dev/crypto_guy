<?php
/**
 * صفحة التسجيل
 * Registration Page
 */

session_start();

// التحقق إذا كان المستخدم مسجل دخول مسبقاً
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$conn = require_once 'config/database.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // التحقق من المدخلات
    if (empty($full_name) || empty($email) || empty($password)) {
        $error = '✋ جميع الحقول المطلوبة يجب أن تكون ممتلئة';
    } elseif (strlen($full_name) < 3) {
        $error = '✋ الاسم يجب أن يكون 3 أحرف على الأقل';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '✋ البريد الإلكتروني غير صحيح';
    } elseif ($password !== $confirm_password) {
        $error = '✋ كلمتا المرور غير متطابقتين';
    } elseif (strlen($password) < 8) {
        $error = '✋ كلمة المرور يجب أن تكون 8 أحرف على الأقل';
    } else {
        try {
            // التحقق من وجود البريد
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();
            
            if ($checkResult->num_rows > 0) {
                $error = '✋ البريد الإلكتروني مسجل مسبقاً';
            } else {
                // تشفير كلمة المرور
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // إدخال المستخدم
                $insertStmt = $conn->prepare("INSERT INTO users (full_name, email, phone, password_hash) VALUES (?, ?, ?, ?)");
                $insertStmt->bind_param("ssss", $full_name, $email, $phone, $hashedPassword);
                
                if ($insertStmt->execute()) {
                    $success = '✅ تم إنشاء الحساب بنجاح! يمكنك الآن تسجيل الدخول.';
                    $full_name = $email = $phone = '';
                } else {
                    $error = '⚠️ حدث خطأ أثناء إنشاء الحساب';
                }
            }
        } catch (Exception $e) {
            error_log("❌ خطأ في التسجيل: " . $e->getMessage());
            $error = '⚠️ حدث خطأ في النظام';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب - منصة الاستثمار</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Tajawal', sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: linear-gradient(135deg, #0f0c29, #302b63, #24243e);
        }
        
        .container {
            width: 100%;
            max-width: 460px;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 40px 35px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.5);
        }
        
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .logo-icon {
            width: 65px;
            height: 65px;
            background: linear-gradient(135deg, #f7931a, #f7b731);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 30px;
            color: white;
            margin-bottom: 12px;
            box-shadow: 0 10px 40px rgba(247, 147, 26, 0.3);
        }
        
        .logo h1 {
            font-size: 26px;
            font-weight: 800;
            color: #fff;
        }
        
        .logo h1 span {
            color: #f7931a;
        }
        
        .logo p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }
        
        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
            animation: slideDown 0.5s ease;
        }
        
        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .alert-error {
            background: rgba(231, 76, 60, 0.2);
            color: #ff6b6b;
            border: 1px solid rgba(231, 76, 60, 0.3);
        }
        
        .alert-success {
            background: rgba(46, 204, 113, 0.2);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }
        
        .form-group {
            margin-bottom: 18px;
        }
        
        .form-group label {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
            font-size: 13px;
            margin-bottom: 6px;
        }
        
        .form-group label i {
            margin-left: 8px;
            color: #f7931a;
        }
        
        .input-wrapper {
            position: relative;
        }
        
        .input-wrapper input {
            width: 100%;
            padding: 12px 40px 12px 14px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #fff;
            font-size: 15px;
            font-family: 'Tajawal', sans-serif;
            transition: all 0.3s ease;
        }
        
        .input-wrapper input::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }
        
        .input-wrapper input:focus {
            outline: none;
            border-color: #f7931a;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 4px rgba(247, 147, 26, 0.1);
        }
        
        .input-wrapper .input-icon {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.3);
            font-size: 16px;
        }
        
        .toggle-password {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.3);
            cursor: pointer;
            font-size: 16px;
            padding: 5px;
            transition: all 0.3s ease;
        }
        
        .toggle-password:hover {
            color: #f7931a;
        }
        
        .hint {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.4);
            margin-top: 5px;
        }
        
        .btn-register {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #f7931a, #f7b731);
            border: none;
            border-radius: 10px;
            color: #fff;
            font-size: 17px;
            font-weight: 700;
            font-family: 'Tajawal', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            margin-top: 5px;
        }
        
        .btn-register:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 40px rgba(247, 147, 26, 0.3);
        }
        
        .footer {
            text-align: center;
            margin-top: 20px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 14px;
        }
        
        .footer a {
            color: #f7931a;
            text-decoration: none;
            font-weight: 600;
        }
        
        .footer a:hover {
            color: #f7b731;
            text-decoration: underline;
        }
        
        .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .btn-register.loading .spinner {
            display: inline-block;
        }
        
        .btn-register.loading .btn-text {
            display: none;
        }
        
        @media (max-width: 480px) {
            .card {
                padding: 25px 18px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            <h1>استثمر <span>لعائلتك</span></h1>
            <p>أنشئ حسابك وابدأ رحلة الاستثمار</p>
        </div>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
                <a href="login.php" style="color: #2ecc71; font-weight: 600; margin-right: auto;">تسجيل الدخول</a>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group">
                <label><i class="fas fa-user"></i> الاسم الكامل</label>
                <div class="input-wrapper">
                    <input type="text" name="full_name" placeholder="أدخل اسمك الكامل" 
                           value="<?php echo htmlspecialchars($full_name ?? ''); ?>" required>
                    <span class="input-icon"><i class="fas fa-user"></i></span>
                </div>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-envelope"></i> البريد الإلكتروني</label>
                <div class="input-wrapper">
                    <input type="email" name="email" placeholder="example@email.com" 
                           value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                    <span class="input-icon"><i class="fas fa-envelope"></i></span>
                </div>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-phone"></i> رقم الهاتف (اختياري)</label>
                <div class="input-wrapper">
                    <input type="tel" name="phone" placeholder="05xxxxxxxx" 
                           value="<?php echo htmlspecialchars($phone ?? ''); ?>">
                    <span class="input-icon"><i class="fas fa-phone"></i></span>
                </div>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-lock"></i> كلمة المرور</label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" placeholder="أدخل كلمة المرور" required minlength="8">
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                    <button type="button" class="toggle-password" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <div class="hint">كلمة المرور يجب أن تكون 8 أحرف على الأقل</div>
            </div>
            
            <div class="form-group">
                <label><i class="fas fa-check-circle"></i> تأكيد كلمة المرور</label>
                <div class="input-wrapper">
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="أعد كتابة كلمة المرور" required>
                    <span class="input-icon"><i class="fas fa-check-circle"></i></span>
                    <button type="button" class="toggle-password" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <button type="submit" class="btn-register" id="registerBtn">
                <span class="btn-text"><i class="fas fa-user-plus"></i> إنشاء حساب</span>
                <span class="spinner"></span>
            </button>
        </form>
        
        <div class="footer">
            لديك حساب بالفعل؟ <a href="login.php">تسجيل الدخول</a>
        </div>
    </div>
</div>

<script>
    function togglePassword(fieldId) {
        const input = document.getElementById(fieldId);
        const icon = input.parentElement.querySelector('.toggle-password i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'fas fa-eye-slash';
        } else {
            input.type = 'password';
            icon.className = 'fas fa-eye';
        }
    }
    
    document.getElementById('registerBtn')?.addEventListener('click', function(e) {
        const form = this.closest('form');
        const password = document.getElementById('password').value;
        const confirm = document.getElementById('confirm_password').value;
        
        if (password !== confirm) {
            e.preventDefault();
            alert('❌ كلمتا المرور غير متطابقتين');
            return;
        }
        
        if (password.length < 8) {
            e.preventDefault();
            alert('❌ كلمة المرور يجب أن تكون 8 أحرف على الأقل');
            return;
        }
        
        this.classList.add('loading');
        this.disabled = true;
    });
</script>

</body>
</html>
