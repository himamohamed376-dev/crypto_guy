const express = require('express');
const cors = require('cors');
const helmet = require('helmet');
const rateLimit = require('express-rate-limit');
const xss = require('xss-clean');
const hpp = require('hpp');
const cookieParser = require('cookie-parser');
require('dotenv').config();

// استيراد الرووتات
const authRoutes = require('./routes/authRoutes');
const investmentRoutes = require('./routes/investmentRoutes');
const userRoutes = require('./routes/userRoutes');

const app = express();
const PORT = process.env.PORT || 5000;

// إعدادات الأمان
app.use(helmet());
app.use(cors({
    origin: process.env.FRONTEND_URL || 'http://localhost:3000',
    credentials: true
}));

// حماية من هجمات XSS
app.use(xss());

// حماية من Parameter Pollution
app.use(hpp());

// تحديد عدد الطلبات
const limiter = rateLimit({
    windowMs: 15 * 60 * 1000, // 15 دقيقة
    max: 100, // 100 طلب لكل IP
    message: 'تم تجاوز عدد الطلبات المسموح بها، يرجى المحاولة لاحقاً'
});
app.use('/api', limiter);

// معالجة البيانات
app.use(express.json({ limit: '10mb' }));
app.use(express.urlencoded({ extended: true, limit: '10mb' }));
app.use(cookieParser());

// حماية CSRF
app.use((req, res, next) => {
    res.header('X-XSS-Protection', '1; mode=block');
    res.header('X-Content-Type-Options', 'nosniff');
    res.header('X-Frame-Options', 'DENY');
    res.header('Referrer-Policy', 'strict-origin-when-cross-origin');
    next();
});

// الرووتات
app.use('/api/auth', authRoutes);
app.use('/api/investments', investmentRoutes);
app.use('/api/users', userRoutes);

// معالجة الأخطاء
app.use((err, req, res, next) => {
    console.error('Error:', err.message);
    res.status(err.status || 500).json({
        error: err.message || 'حدث خطأ في الخادم'
    });
});

// تشغيل الخادم
app.listen(PORT, () => {
    console.log(`🚀 الخادم يعمل على http://localhost:${PORT}`);
    console.log('📌 الوضع:', process.env.NODE_ENV || 'development');
});
