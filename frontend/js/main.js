// ===== المتغيرات العامة =====
const API_URL = 'http://localhost:5000/api';
let currentUser = null;
let authToken = localStorage.getItem('authToken');

// ===== التحقق من حالة المستخدم =====
async function checkAuth() {
    if (!authToken) return false;
    
    try {
        const response = await fetch(`${API_URL}/users/me`, {
            headers: {
                'Authorization': `Bearer ${authToken}`
            }
        });
        
        if (response.ok) {
            const data = await response.json();
            currentUser = data.user;
            updateUIForLoggedInUser();
            return true;
        } else {
            logout();
            return false;
        }
    } catch (error) {
        console.error('Auth check error:', error);
        return false;
    }
}

// ===== تحديث الواجهة للمستخدم المسجل =====
function updateUIForLoggedInUser() {
    const navAuth = document.querySelector('.nav-auth');
    if (navAuth && currentUser) {
        navAuth.innerHTML = `
            <span class="user-greeting">مرحباً، ${currentUser.full_name}</span>
            <a href="dashboard.html" class="btn btn-primary">لوحة التحكم</a>
            <button onclick="logout()" class="btn btn-outline">تسجيل الخروج</button>
        `;
    }
}

// ===== تسجيل الخروج =====
function logout() {
    localStorage.removeItem('authToken');
    currentUser = null;
    window.location.href = 'index.html';
}

// ===== عرض الفرص الاستثمارية =====
async function loadInvestments() {
    try {
        const response = await fetch(`${API_URL}/investments/active`);
        const investments = await response.json();
        
        const grid = document.getElementById('investmentsGrid');
        if (!grid) return;
        
        if (investments.length === 0) {
            grid.innerHTML = `
                <div class="no-investments">
                    <i class="fas fa-inbox" style="font-size: 48px; color: var(--text-gray);"></i>
                    <p style="color: var(--text-gray); margin-top: 10px;">لا توجد فرص استثمارية حالياً</p>
                </div>
            `;
            return;
        }
        
        grid.innerHTML = investments.map(inv => `
            <div class="investment-card" onclick="goToInvestmentDetail(${inv.id})">
                <div class="investment-card-image">
                    <i class="fas fa-chart-simple"></i>
                </div>
                <div class="investment-card-content">
                    <span class="category">${inv.category || 'عام'}</span>
                    <h3>${inv.title}</h3>
                    <p style="color: var(--text-gray); font-size: 14px; margin: 8px 0;">
                        ${inv.description.substring(0, 100)}...
                    </p>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: ${(inv.current_amount / inv.target_amount) * 100}%"></div>
                    </div>
                    <div class="amount">
                        <span><strong>${Number(inv.current_amount).toLocaleString()} ريال</strong> / ${Number(inv.target_amount).toLocaleString()} ريال</span>
                        <span style="color: var(--primary); font-weight: 600;">${inv.expected_return}% عائد</span>
                    </div>
                </div>
            </div>
        `).join('');
    } catch (error) {
        console.error('Error loading investments:', error);
        const grid = document.getElementById('investmentsGrid');
        if (grid) {
            grid.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-circle" style="font-size: 32px; color: var(--danger);"></i>
                    <p style="color: var(--danger);">حدث خطأ أثناء تحميل الفرص الاستثمارية</p>
                </div>
            `;
        }
    }
}

// ===== الانتقال إلى صفحة التفاصيل =====
function goToInvestmentDetail(id) {
    window.location.href = `investment-detail.html?id=${id}`;
}

// ===== تشغيل القائمة الجانبية =====
function initMobileMenu() {
    const toggle = document.getElementById('navToggle');
    const navLinks = document.getElementById('navLinks');
    
    if (toggle && navLinks) {
        toggle.addEventListener('click', () => {
            navLinks.classList.toggle('active');
        });
    }
}

// ===== تحميل البيانات عند تحميل الصفحة =====
document.addEventListener('DOMContentLoaded', async () => {
    // التحقق من حالة المستخدم
    await checkAuth();
    
    // تحميل الفرص الاستثمارية
    await loadInvestments();
    
    // تشغيل القائمة الجانبية
    initMobileMenu();
});

// ===== تصدير الوظائف للاستخدام في ملفات أخرى =====
window.logout = logout;
window.goToInvestmentDetail = goToInvestmentDetail;
window.currentUser = currentUser;
window.authToken = authToken;
window.API_URL = API_URL;
