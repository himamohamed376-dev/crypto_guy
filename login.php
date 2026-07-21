<?php
/**
 * صفحة تسجيل الدخول
 * Login Page with session management
 */

// بدء الجلسة
session_start();

// التحقق إذا كان المستخدم مسجل دخول مسبقاً
if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

// استيراد الاتصال بقاعدة البيانات
$conn = require_once 'config/database.php';

$error = '';
$success = '';

// معالجة نموذج تسجيل الدخول
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);
    
    // التحقق من المدخلات
    if (empty($email) || empty($password)) {
        $error = '✋ البريد الإلكتروني وكلمة المرور مطلوبان';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = '✋ البريد الإلكتروني غير صحيح';
    } else {
        try {
            // البحث عن المستخدم
            $stmt = $conn->prepare("SELECT id, full_name, email, password_hash, vip_level, is_active, deposit_balance FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();
            
            if (!$user) {
                $error = '❌ البريد الإلكتروني أو كلمة المرور غير صحيحة';
            } elseif (!$user['is_active']) {
                $error = '⛔ الحساب غير مفعل. يرجى التواصل مع الدعم';
            } elseif (!password_verify($password, $user['password_hash'])) {
                $error = '❌ البريد الإلكتروني أو كلمة المرور غير صحيحة';
            } else {
                // ===== تسجيل دخول ناجح =====
                
                // تحديث آخر تسجيل دخول
                $updateStmt = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $updateStmt->bind_param("i", $user['id']);
                $updateStmt->execute();
                
                // حفظ بيانات المستخدم في الجلسة
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_vip'] = $user['vip_level'];
                $_SESSION['user_balance'] = $user['deposit_balance'];
                $_SESSION['logged_in'] = true;
                
                // في حالة تذكرني، إنشاء كوكي
                if ($remember) {
                    $token = bin2hex(random_bytes(32));
                    setcookie('remember_token', $token, time() + 86400 * 30, '/', '', true, true);
                    // يمكن حفظ التوكن في قاعدة البيانات لزيادة الأمان
                }
                
                // رسالة نجاح وتحويل
                $success = '✅ تم تسجيل الدخول بنجاح! جاري التحويل...';
                
                // إعادة التوجيه بعد 1.5 ثانية
                echo "<script>
                    setTimeout(function() {
                        window.location.href = 'dashboard.php';
                    }, 1500);
                </script>";
            }
        } catch (Exception $e) {
            error_log("❌ خطأ في تسجيل الدخول: " . $e->getMessage());
            $error = '⚠️ حدث خطأ في النظام. يرجى المحاولة لاحقاً';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - منصة الاستثمار</title>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ===== Reset & Base ===== */
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
            position: relative;
            overflow: hidden;
        }
        
        /* خلفية متحركة */
        body::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 30% 50%, rgba(247, 147, 26, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 70% 50%, rgba(247, 147, 26, 0.05) 0%, transparent 50%);
            animation: rotate 30s linear infinite;
            z-index: 0;
        }
        
        @keyframes rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .container {
            width: 100%;
            max-width: 440px;
            position: relative;
            z-index: 1;
        }
        
        /* ===== Card ===== */
        .card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 45px 35px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 25px 80px rgba(0, 0, 0, 0.5);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            border-color: rgba(247, 147, 26, 0.3);
            box-shadow: 0 25px 80px rgba(247, 147, 26, 0.1);
        }
        
        /* ===== Logo ===== */
        .logo {
            text-align: center;
            margin-bottom: 35px;
        }
        
        .logo-icon {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #f7931a, #f7b731);
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            margin-bottom: 15px;
            box-shadow: 0 10px 40px rgba(247, 147, 26, 0.3);
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        .logo h1 {
            font-size: 28px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 5px;
        }
        
        .logo h1 span {
            color: #f7931a;
        }
        
        .logo p {
            color: rgba(255, 255, 255, 0.6);
            font-size: 15px;
        }
        
        /* ===== Alerts ===== */
        .alert {
            padding: 14px 18px;
            border-radius: 12px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
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
        
        /* ===== Form ===== */
        .form-group {
            margin-bottom: 22px;
        }
        
        .form-group label {
            display: block;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 600;
            font-size: 14px;
            margin-bottom: 8px;
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
            padding: 14px 45px 14px 16px;
            background: rgba(255, 255, 255, 0.08);
            border: 2px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 16px;
            font-family: 'Tajawal', sans-serif;
            transition: all 0.3s ease;
        }
        
        .input-wrapper input::placeholder {
            color: rgba(255, 255, 255, 0.4);
        }
        
        .input-wrapper input:focus {
            outline: none;
            border-color: #f7931a;
            background: rgba(255, 255, 255, 0.12);
            box-shadow: 0 0 0 4px rgba(247, 147, 26, 0.1);
        }
        
        .input-wrapper .input-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.4);
            font-size: 18px;
            transition: all 0.3s ease;
        }
        
        .input-wrapper input:focus + .input-icon {
            color: #f7931a;
        }
        
        .toggle-password {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.4);
            cursor: pointer;
            font-size: 18px;
            transition: all 0.3s ease;
            padding: 5px;
        }
        
        .toggle-password:hover {
            color: #f7931a;
        }
        
        /* ===== Options ===== */
        .options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .options label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: rgba(255, 255, 255, 0.7);
            font-size: 14px;
            cursor: pointer;
        }
        
        .options label input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #f7931a;
            cursor: pointer;
        }
        
        .forgot-link {
            color: #f7931a;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .forgot-link:hover {
            color: #f7b731;
            text-decoration: underline;
        }
        
        /* ===== Button ===== */
        .btn-login {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #f7931a, #f7b731);
            border: none;
            border-radius: 12px;
            color: #fff;
            font-size: 18px;
            font-weight: 700;
            font-family: 'Tajawal', sans-serif;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 40px rgba(247, 147, 26, 0.3);
        }
        
        .btn-login:active {
            transform: translateY(0);
        }
        
        /* ===== Footer ===== */
        .footer {
            text-align: center;
            margin-top: 25px;
            color: rgba(255, 255, 255, 0.6);
            font-size: 15px;
        }
        
        .footer a {
            color: #f7931a;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .footer a:hover {
            color: #f7b731;
            text-decoration: underline;
        }
        
        /* ===== Loading Spinner ===== */
        .spinner {
            display: none;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top: 3px solid #fff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .btn-login.loading .spinner {
            display: inline-block;
        }
        
        .btn-login.loading .btn-text {
            display: none;
        }
        
        /* ===== Responsive ===== */
        @media (max-width: 480px) {
            .card {
                padding: 30px 20px;
            }
            
            .logo h1 {
                font-size: 24px;
            }
            
            .options {
                flex-direction: column;
                align-items: flex-start;
            }
        }
        
        /* ===== Social Login (اختياري) ===== */
        .divider {
            display: flex;
            align-items: center;
            gap: 15px;
            margin: 25px 0;
            color: rgba(255, 255, 255, 0.3);
            font-size: 14px;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="card">
        <!-- ===== Logo ===== -->
        <div class="logo">
            <div class="logo-icon">
                <i class="fas fa-chart-line"></i>
            </div>
            <h1>استثمر <span>لعائلتك</span></h1>
            <p>سجل الدخول للوصول إلى حسابك</p>
        </div>
        
        <!-- ===== Alerts ===== -->
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
            </div>
        <?php endif; ?>
        
        <!-- ===== Login Form ===== -->
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" id="loginForm">
            <div class="form-group">
                <label>
                    <i class="fas fa-envelope"></i>
                    البريد الإلكتروني
                </label>
                <div class="input-wrapper">
                    <input type="email" name="email" placeholder="example@email.com" 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                    <span class="input-icon"><i class="fas fa-envelope"></i></span>
                </div>
            </div>
            
            <div class="form-group">
                <label>
                    <i class="fas fa-lock"></i>
                    كلمة المرور
                </label>
                <div class="input-wrapper">
                    <input type="password" name="password" id="password" placeholder="أدخل كلمة المرور" required>
                    <span class="input-icon"><i class="fas fa-lock"></i></span>
                    <button type="button" class="toggle-password" onclick="togglePassword()">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
            </div>
            
            <div class="options">
                <label>
                    <input type="checkbox" name="remember" <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                    تذكرني
                </label>
                <a href="forgot-password.php" class="forgot-link">نسيت كلمة المرور؟</a>
            </div>
            
            <button type="submit" class="btn-login" id="loginBtn">
                <span class="btn-text"><i class="fas fa-sign-in-alt"></i> تسجيل الدخول</span>
                <span class="spinner"></span>
            </button>
        </form>
        
        <!-- ===== Divider ===== -->
        <div class="divider">أو</div>
        
        <!-- ===== Footer ===== -->
        <div class="footer">
            ليس لديك حساب؟ <a href="register.php">إنشاء حساب جديد</a>
        </div>
    </div>
</div>

<!-- ===== JavaScript ===== -->
<script>
    // ===== Toggle Password Visibility =====
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const toggleIcon = document.querySelector('.toggle-password i');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            toggleIcon.className = 'fas fa-eye-slash';
        } else {
            passwordInput.type = 'password';
            toggleIcon.className = 'fas fa-eye';
        }
    }
    
    // ===== Loading State on Submit =====
    document.getElementById('loginForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('loginBtn');
        btn.classList.add('loading');
        btn.disabled = true;
    });
    
    // ===== Enter key support =====
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            const form = document.getElementById('loginForm');
            if (document.activeElement && document.activeElement.form === form) {
                form.submit();
            }
        }
    });
</script>

</body>
</html>
