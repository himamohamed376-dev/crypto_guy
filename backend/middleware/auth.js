const jwt = require('jsonwebtoken');
const User = require('../models/User');

// التحقق من صلاحية التوكن
const authenticate = async (req, res, next) => {
    try {
        const token = req.header('Authorization')?.replace('Bearer ', '');
        
        if (!token) {
            return res.status(401).json({ error: 'الرجاء تسجيل الدخول أولاً' });
        }

        const decoded = jwt.verify(token, process.env.JWT_SECRET || 'your-secret-key');
        const user = await User.findById(decoded.id);
        
        if (!user || !user.is_active) {
            throw new Error('مستخدم غير صالح');
        }

        req.user = user;
        req.userId = user.id;
        next();
    } catch (error) {
        res.status(401).json({ error: 'جلسة غير صالحة، الرجاء تسجيل الدخول مجدداً' });
    }
};

// التحقق من صلاحيات الأدمن
const isAdmin = (req, res, next) => {
    if (req.user && req.user.role === 'admin') {
        next();
    } else {
        res.status(403).json({ error: 'غير مصرح لك بالوصول إلى هذه الصفحة' });
    }
};

module.exports = { authenticate, isAdmin };
