// =============================================
// ملف JavaScript الرئيسي للواجهة
// =============================================

// تحديث وقت التشغيل
function updateUptime() {
    const uptimeElement = document.getElementById('uptime');
    if (!uptimeElement) return;
    
    fetch('/health')
        .then(res => res.json())
        .then(data => {
            const seconds = Math.floor(data.uptime);
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            
            let uptimeText = '';
            if (hours > 0) uptimeText += `${hours} ساعة `;
            if (minutes > 0) uptimeText += `${minutes % 60} دقيقة `;
            uptimeText += `${seconds % 60} ثانية`;
            
            uptimeElement.textContent = uptimeText;
        })
        .catch(() => {
            uptimeElement.textContent = 'غير متاح';
        });
}

// التحقق من حالة قاعدة البيانات
function checkDatabase() {
    const dbStatus = document.getElementById('dbStatus');
    if (!dbStatus) return;
    
    fetch('/api/status')
        .then(res => res.json())
        .then(data => {
            if (data.status === 'running') {
                dbStatus.textContent = '🟢 متصلة';
                dbStatus.style.color = '#2D9CDB';
            } else {
                dbStatus.textContent = '🟠 غير متصلة (وضع القراءة)';
                dbStatus.style.color = '#F4A261';
            }
        })
        .catch(() => {
            dbStatus.textContent = '🔴 غير متصلة';
            dbStatus.style.color = '#E76F51';
        });
}

// اختبار API
async function testAPI() {
    const btn = document.getElementById('testApiBtn');
    const result = document.getElementById('testResult');
    
    if (!btn || !result) return;
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> جاري الاختبار...';
    result.className = 'test-result';
    result.style.display = 'none';
    
    try {
        // اختبار نقطة /health
        const healthRes = await fetch('/health');
        const healthData = await healthRes.json();
        
        // اختبار نقطة /api/status
        const statusRes = await fetch('/api/status');
        const statusData = await statusRes.json();
        
        result.className = 'test-result show success';
        result.innerHTML = `
            <h3 style="color: #2D6A4F; margin-bottom: 15px;">✅ الاختبار ناجح!</h3>
            <div style="text-align: right;">
                <p><strong>📊 حالة الخادم:</strong> ${healthData.status}</p>
                <p><strong>⏱️ وقت التشغيل:</strong> ${Math.floor(healthData.uptime)} ثانية</p>
                <p><strong>🌍 البيئة:</strong> ${healthData.environment || 'development'}</p>
                <p><strong>🕐 التاريخ:</strong> ${new Date(healthData.timestamp).toLocaleString('ar-EG')}</p>
                <p><strong>📡 حالة API:</strong> ${statusData.status}</p>
            </div>
        `;
    } catch (error) {
        result.className = 'test-result show error';
        result.innerHTML = `
            <h3 style="color: #E76F51;">❌ فشل الاختبار</h3>
            <p style="color: #E76F51;">${error.message}</p>
            <p style="color: #6c757d; font-size: 14px;">تأكد من أن الخادم يعمل على المنفذ ${window.location.port}</p>
        `;
    }
    
    btn.disabled = false;
    btn.innerHTML = '<i class="fas fa-play"></i> اختبار API';
}

// ===== تشغيل الوظائف عند تحميل الصفحة =====
document.addEventListener('DOMContentLoaded', () => {
    // تحديث وقت التشغيل كل 5 ثواني
    updateUptime();
    setInterval(updateUptime, 5000);
    
    // التحقق من قاعدة البيانات
    checkDatabase();
    
    // إضافة حدث لزر الاختبار
    const testBtn = document.getElementById('testApiBtn');
    if (testBtn) {
        testBtn.addEventListener('click', testAPI);
    }
    
    // عرض معلومات البيئة
    console.log('✅ منصة الاستثمار العائلي - جاهزة للعمل');
    console.log(`🌐 الرابط: ${window.location.href}`);
    console.log(`📅 التاريخ: ${new Date().toLocaleString('ar-EG')}`);
});

// ===== تصدير الوظائف للاستخدام العالمي =====
window.testAPI = testAPI;
window.updateUptime = updateUptime;
window.checkDatabase = checkDatabase;
