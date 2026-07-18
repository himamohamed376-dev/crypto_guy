// =============================================
// روابط المصادقة والتسجيل
// =============================================

const express = require('express');
const router = express.Router();
const User = require('../models/User');
const jwt = require('jsonwebtoken');

// ===== تسجيل مستخدم جديد =====
router.post('/register', async (req, res) => {
    try {
        const { full_name, email, phone, password, confirm_password } = req.body;

        // التحقق من تطابق كلمة المرور
        if (password !== confirm_password) {
            return res.status(400).json({ 
                error: 'كلمتا المرور غير متطابقتين' 
            });
        }

        // التحقق من وجود البريد الإلكتروني
        const existingUser = await User.findByEmail(email);
        if (existingUser) {
            return res.status(400).json({ 
                error: 'البريد الإلكتروني مسجل مسبقاً' 
            });
        }

        // إنشاء المستخدم
        const userId = await User.create({ full_name, email, phone, password });
        
        // إنشاء توكن JWT
        const token = jwt.sign(
            { id: userId, email },
            process.env.JWT_SECRET || 'your-secret-key',
            { expiresIn: '7d' }
        );

        res.status(201).json({
            message: 'تم التسجيل بنجاح',
            token,
            user: { id: userId, full_name, email, phone }
        });
    } catch (error) {
        console.error('❌ خطأ في التسجيل:', error.message);
        res.status(500).json({ 
            error: 'حدث خطأ أثناء التسجيل' 
        });
    }
});

// ===== تسجيل دخول =====
router.post('/login', async (req, res) => {
    try {
        const { email, password } = req.body;

        const user = await User.verifyPassword(email, password);
        if (!user) {
            return res.status(401).json({ 
                error: 'البريد الإلكتروني أو كلمة المرور غير صحيحة' 
            });
        }

        if (!user.is_active) {
            return res.status(401).json({ 
                error: 'الحساب غير مفعل' 
            });
        }

        // إنشاء توكن JWT
        const token = jwt.sign(
            { id: user.id, email: user.email, role: user.role },
            process.env.JWT_SECRET || 'your-secret-key',
            { expiresIn: '7d' }
        );

        res.json({
            message: 'تم تسجيل الدخول بنجاح',
            token,
            user: {
                id: user.id,
                full_name: user.full_name,
                email: user.email,
                phone: user.phone,
                balance: user.balance,
                role: user.role
            }
        });
    } catch (error) {
        console.error('❌ خطأ في تسجيل الدخول:', error.message);
        res.status(500).json({ 
            error: 'حدث خطأ أثناء تسجيل الدخول' 
        });
    }
});

// ===== التحقق من التوكن =====
router.get('/verify', async (req, res) => {
    try {
        const token = req.header('Authorization')?.replace('Bearer ', '');
        
        if (!token) {
            return res.status(401).json({ error: 'لا يوجد توكن' });
        }

        const decoded = jwt.verify(token, process.env.JWT_SECRET || 'your-secret-key');
        const user = await User.findById(decoded.id);
        
        if (!user) {
            return res.status(401).json({ error: 'مستخدم غير موجود' });
        }

        res.json({
            valid: true,
            user: {
                id: user.id,
                full_name: user.full_name,
                email: user.email,
                role: user.role
            }
        });
    } catch (error) {
        res.status(401).json({ error: 'توكن غير صالح' });
    }
});

module.exports = router;
