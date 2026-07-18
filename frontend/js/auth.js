// ===== تسجيل الدخول =====
async function loginUser(email, password) {
    try {
        const response = await fetch(`${API_URL}/auth/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });

        const data = await response.json();

        if (response.ok) {
            localStorage.setItem('authToken', data.token);
            window.authToken = data.token;
            window.currentUser = data.user;
            
            showNotification('تم تسجيل الدخول بنجاح', 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.html';
            }, 1000);
            return true;
        } else {
            showNotification(data.error || 'فشل تسجيل الدخول', 'error');
            return false;
        }
    } catch (error) {
        console.error('Login error:', error);
        showNotification('حدث خطأ أثناء تسجيل الدخول', 'error');
        return false;
    }
}

// ===== إنشاء حساب جديد =====
async function registerUser(userData) {
    try {
        const response = await fetch(`${API_URL}/auth/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(userData)
        });

        const data = await response.json();

        if (response.ok) {
            localStorage.setItem('authToken', data.token);
            window.authToken = data.token;
            window.currentUser = data.user;
            
            showNotification('تم إنشاء الحساب بنجاح', 'success');
            setTimeout(() => {
                window.location.href = 'dashboard.html';
            }, 1000);
            return true;
        } else {
            showNotification(data.error || 'فشل إنشاء الحساب', 'error');
            return false;
        }
    } catch (error) {
        console.error('Register error:', error);
        showNotification('حدث خطأ أثناء إنشاء الحساب', 'error');
        return false;
    }
}

// ===== التحقق بخطوتين =====
async function verifyTwoFactor(token) {
    try {
        const response = await fetch(`${API_URL}/auth/verify-2fa`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': `Bearer ${window.authToken}`
            },
            body: JSON.stringify({ token })
        });

        const data = await response.json();

        if (response.ok) {
            showNotification('تم التحقق بنجاح', 'success');
            return true;
        } else {
            showNotification(data.error || 'رمز التحقق غير صحيح', 'error');
            return false;
        }
    } catch (error) {
        console.error('2FA verification error:', error);
        showNotification('حدث خطأ أثناء التحقق', 'error');
        return false;
    }
}

// ===== عرض الإشعارات =====
function showNotification(message, type = 'info') {
    const colors = {
        success: '#2D6A4F',
        error: '#E76F51',
        warning: '#F4A261',
        info: '#2D9CDB'
    };
    
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 100px;
        left: 50%;
        transform: translateX(-50%);
        background: ${colors[type] || '#333'};
        color: white;
        padding: 16px 32px;
        border-radius: 12px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        z-index: 9999;
        font-weight: 500;
        animation: slideDown 0.5s ease;
        max-width: 90%;
        text-align: center;
    `;
    
    notification.textContent = message;
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideUp 0.5s ease';
        setTimeout(() => notification.remove(), 500);
    }, 4000);
}

// ===== التحقق من صحة الإيميل =====
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// ===== التحقق من صحة رقم الهاتف السعودي =====
function validatePhone(phone) {
    const re = /^05[0-9]{8}$/;
    return re.test(phone);
}

// ===== التحقق من قوة كلمة المرور =====
function validatePassword(password) {
    const minLength = 8;
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumber = /[0-9]/.test(password);
    const hasSpecialChar = /[!@#$%^&*]/.test(password);
    
    return password.length >= minLength && hasUpperCase && hasLowerCase && hasNumber && hasSpecialChar;
}

// ===== إضافة أنماط للأنيميشن =====
const styleSheet = document.createElement('style');
styleSheet.textContent = `
    @keyframes slideDown {
        from { opacity: 0; transform: translateX(-50%) translateY(-20px); }
        to { opacity: 1; transform: translateX(-50%) translateY(0); }
    }
    @keyframes slideUp {
        from { opacity: 1; transform: translateX(-50%) translateY(0); }
        to { opacity: 0; transform: translateX(-50%) translateY(-20px); }
    }
`;
document.head.appendChild(styleSheet);

// ===== تصدير الوظائف للاستخدام العام =====
window.loginUser = loginUser;
window.registerUser = registerUser;
window.verifyTwoFactor = verifyTwoFactor;
window.showNotification = showNotification;
window.validateEmail = validateEmail;
window.validatePhone = validatePhone;
window.validatePassword = validatePassword;
