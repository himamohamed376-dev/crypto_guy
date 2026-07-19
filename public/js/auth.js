// =============================================
// نظام المصادقة - JavaScript للواجهة الأمامية
// =============================================

// ===== المتغيرات العامة =====
const API_BASE = window.location.origin + '/api/auth';

// ===== تسجيل مستخدم جديد =====
async function registerUser(event) {
    event.preventDefault();
    
    const full_name = document.getElementById('full_name')?.value;
    const email = document.getElementById('email')?.value;
    const phone = document.getElementById('phone')?.value;
    const password = document.getElementById('password')?.value;
    const confirm_password = document.getElementById('confirm_password')?.value;
    
    // التحقق من صحة البيانات
    if (!full_name || !email || !phone || !password || !confirm_password) {
        showMessage('جميع الحقول مطلوبة', 'error');
        return;
    }
    
    if (password !== confirm_password) {
        showMessage('كلمتا المرور غير متطابقتين', 'error');
        return;
    }
    
    if (password.length < 6) {
        showMessage('كلمة المرور يجب أن تكون 6 أحرف على الأقل', 'error');
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}/register`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ full_name, email, phone, password, confirm_password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // حفظ التوكن في localStorage
            localStorage.setItem('authToken', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));
            
            showMessage('تم التسجيل بنجاح! جاري التحويل...', 'success');
            
            setTimeout(() => {
                window.location.href = '/dashboard.html';
            }, 1500);
        } else {
            showMessage(data.error || 'فشل التسجيل', 'error');
        }
    } catch (error) {
        console.error('❌ خطأ في التسجيل:', error);
        showMessage('حدث خطأ أثناء التسجيل', 'error');
    }
}

// ===== تسجيل دخول =====
async function loginUser(event) {
    event.preventDefault();
    
    const email = document.getElementById('email')?.value;
    const password = document.getElementById('password')?.value;
    
    if (!email || !password) {
        showMessage('البريد الإلكتروني وكلمة المرور مطلوبان', 'error');
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}/login`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            // حفظ التوكن في localStorage
            localStorage.setItem('authToken', data.token);
            localStorage.setItem('user', JSON.stringify(data.user));
            
            showMessage('تم تسجيل الدخول بنجاح! جاري التحويل...', 'success');
            
            setTimeout(() => {
                window.location.href = '/dashboard.html';
            }, 1000);
        } else {
            showMessage(data.error || 'فشل تسجيل الدخول', 'error');
        }
    } catch (error) {
        console.error('❌ خطأ في تسجيل الدخول:', error);
        showMessage('حدث خطأ أثناء تسجيل الدخول', 'error');
    }
}

// ===== تسجيل خروج =====
function logoutUser() {
    if (confirm('هل أنت متأكد من تسجيل الخروج؟')) {
        // مسح التوكن والمعلومات من localStorage
        localStorage.removeItem('authToken');
        localStorage.removeItem('user');
        
        // إعادة التوجيه إلى صفحة تسجيل الدخول
        window.location.href = '/login.html';
    }
}

// ===== التحقق من حالة تسجيل الدخول =====
async function checkAuth() {
    const token = localStorage.getItem('authToken');
    
    if (!token) {
        // إذا كان المستخدم في صفحة محمية، إعادة التوجيه
        if (window.location.pathname.includes('dashboard.html')) {
            window.location.href = '/login.html';
        }
        return false;
    }
    
    try {
        const response = await fetch(`${API_BASE}/verify`, {
            headers: {
                'Authorization': `Bearer ${token}`
            }
        });
        
        const data = await response.json();
        
        if (data.success && data.valid) {
            // تحديث معلومات المستخدم
            localStorage.setItem('user', JSON.stringify(data.user));
            
            // إذا كان في صفحة تسجيل الدخول أو التسجيل، إعادة التوجيه إلى لوحة التحكم
            if (window.location.pathname.includes('login.html') || 
                window.location.pathname.includes('register.html')) {
                window.location.href = '/dashboard.html';
            }
            
            return true;
        } else {
            // التوكن غير صالح
            localStorage.removeItem('authToken');
            localStorage.removeItem('user');
            
            if (window.location.pathname.includes('dashboard.html')) {
                window.location.href = '/login.html';
            }
            return false;
        }
    } catch (error) {
        console.error('❌ خطأ في التحقق من المصادقة:', error);
        return false;
    }
}

// ===== عرض رسائل =====
function showMessage(message, type = 'info') {
    // إزالة أي رسائل سابقة
    const existingMessages = document.querySelectorAll('.auth-message');
    existingMessages.forEach(msg => msg.remove());
    
    const colors = {
        success: '#2D6A4F',
        error: '#E76F51',
        info: '#2D9CDB',
        warning: '#F4A261'
    };
    
    const messageDiv = document.createElement('div');
    messageDiv.className = 'auth-message';
    messageDiv.style.cssText = `
        padding: 12px 20px;
        border-radius: 10px;
        background: ${colors[type] || '#333'};
        color: white;
        margin: 10px 0 20px 0;
        text-align: center;
        font-weight: 500;
        animation: slideDown 0.5s ease;
    `;
    messageDiv.textContent = message;
    
    // إضافة الرسالة قبل النموذج
    const form = document.querySelector('form');
    if (form) {
        form.parentNode.insertBefore(messageDiv, form);
    } else {
        document.body.prepend(messageDiv);
    }
    
    // إزالة الرسالة تلقائياً بعد 5 ثواني
    setTimeout(() => {
        messageDiv.style.animation = 'slideUp 0.5s ease';
        setTimeout(() => messageDiv.remove(), 500);
    }, 5000);
}

// ===== إظهار معلومات المستخدم في لوحة التحكم =====
function displayUserInfo() {
    const userData = localStorage.getItem('user');
    if (!userData) return;
    
    try {
        const user = JSON.parse(userData);
        
        // تحديث اسم المستخدم
        const userNameElements = document.querySelectorAll('.user-name');
        userNameElements.forEach(el => {
            el.textContent = user.full_name;
        });
        
        // تحديث البريد الإلكتروني
        const userEmailElements = document.querySelectorAll('.user-email');
        userEmailElements.forEach(el => {
            el.textContent = user.email;
        });
        
        // تحديث الرصيد
        const userBalanceElements = document.querySelectorAll('.user-balance');
        userBalanceElements.forEach(el => {
            el.textContent = `${Number(user.balance || 0).toLocaleString()} ريال`;
        });
        
    } catch (error) {
        console.error('❌ خطأ في عرض معلومات المستخدم:', error);
    }
}

// ===== إضافة أنيميشن للرسائل =====
const styleSheet = document.createElement('style');
styleSheet.textContent = `
    @keyframes slideDown {
        from { opacity: 0; transform: translateY(-20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes slideUp {
        from { opacity: 1; transform: translateY(0); }
        to { opacity: 0; transform: translateY(-20px); }
    }
`;
document.head.appendChild(styleSheet);

// ===== تصدير الوظائف للاستخدام العام =====
window.registerUser = registerUser;
window.loginUser = loginUser;
window.logoutUser = logoutUser;
window.checkAuth = checkAuth;
window.showMessage = showMessage;
window.displayUserInfo = displayUserInfo;

// ===== تشغيل التحقق عند تحميل الصفحة =====
document.addEventListener('DOMContentLoaded', async () => {
    // التحقق من المصادقة
    await checkAuth();
    
    // عرض معلومات المستخدم إذا كان في لوحة التحكم
    if (window.location.pathname.includes('dashboard.html')) {
        displayUserInfo();
        
        // إضافة زر تسجيل الخروج
        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', logoutUser);
        }
    }
});
