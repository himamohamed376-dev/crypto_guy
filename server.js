// =============================================
// الملف الرئيسي للخادم - جاهز للعمل على Render
// =============================================

require('dotenv').config();
const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');
const path = require('path');

// استيراد الرووتات
const authRoutes = require('./src/routes/auth');
const apiRoutes = require('./src/routes/api');

// إنشاء تطبيق Express
const app = express();
const PORT = process.env.PORT || 10000;

// ===== إعدادات الأمان =====
app.use(helmet({
    contentSecurityPolicy: {
        directives: {
            defaultSrc: ["'self'"],
            styleSrc: ["'self'", "'unsafe-inline'", "https://fonts.googleapis.com"],
            fontSrc: ["'self'", "https://fonts.gstatic.com"],
            scriptSrc: ["'self'", "'unsafe-inline'"],
            imgSrc: ["'self'", "data:"],
        },
    },
}));

// ===== CORS =====
app.use(cors({
    origin: process.env.FRONTEND_URL || '*',
    credentials: true
}));

// ===== تحديد عدد الطلبات =====
const limiter = rateLimit({
    windowMs: 15 * 60 * 1000, // 15 دقيقة
    max: 100, // 100 طلب لكل IP
    message: 'تم تجاوز عدد الطلبات المسموح بها، يرجى المحاولة لاحقاً'
});
app.use('/api', limiter);

// ===== معالجة البيانات =====
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));

// ===== خدمة الملفات الثابتة =====
app.use(express.static(path.join(__dirname, 'public')));

// ===== تسجيل الطلبات (للتصحيح) =====
app.use((req, res, next) => {
    console.log(`📝 ${req.method} ${req.url}`);
    next();
});

// ===== الرووتات =====
app.use('/api/auth', authRoutes);
app.use('/api', apiRoutes);

// ===== صفحة رئيسية للاختبار =====
app.get('/', (req, res) => {
    res.sendFile(path.join(__dirname, 'public', 'index.html'));
});

// ===== التحقق من صحة الخادم =====
app.get('/health', (req, res) => {
    res.json({
        status: 'OK',
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        environment: process.env.NODE_ENV || 'development'
    });
});

// ===== معالجة الأخطاء =====
app.use((err, req, res, next) => {
    console.error('❌ خطأ:', err.message);
    res.status(err.status || 500).json({
        error: err.message || 'حدث خطأ في الخادم'
    });
});

// ===== تشغيل الخادم =====
app.listen(PORT, () => {
    console.log('🚀 ==================================');
    console.log(`✅ الخادم يعمل على المنفذ: ${PORT}`);
    console.log(`🌐 الرابط: http://localhost:${PORT}`);
    console.log(`🔧 الوضع: ${process.env.NODE_ENV || 'development'}`);
    console.log('📅 التاريخ:', new Date().toLocaleString('ar-EG'));
    console.log('🚀 ==================================');
});

// ===== التعامل مع الإيقاف =====
process.on('SIGTERM', () => {
    console.log('🛑 استلام إشارة الإيقاف، جاري الإغلاق...');
    process.exit(0);
});

process.on('uncaughtException', (err) => {
    console.error('💥 خطأ غير متوقع:', err);
});

module.exports = app;
