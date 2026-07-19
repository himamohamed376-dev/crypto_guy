// =============================================
// التحكم في عمليات المصادقة
// =============================================

const User = require('../models/User');
const jwt = require('jsonwebtoken');
const bcrypt = require('bcrypt');

// ===== تسجيل مستخدم جديد =====
const register = async (req, res) => {
    try {
        const { full_name, email, phone, password, confirm_password } = req.body;

        // التحقق من وجود جميع الحقول
        if (!full_name || !email || !phone || !password) {
            return res.status(400).json({
                success: false,
                error: 'جميع الحقول مطلوبة'
            });
        }

        // التحقق من تطابق كلمة المرور
        if (password !== confirm_password) {
            return res.status(400).json({
                success: false,
                error: 'كلمتا المرور غير متطابقتين'
            });
        }

        // التحقق من طول كلمة المرور
        if (password.length < 6) {
            return res.status(400).json({
                success: false,
                error: 'كلمة المرور يجب أن تكون 6 أحرف على الأقل'
            });
        }

        // التحقق من وجود البريد الإلكتروني
        const existingUser = await User.findByEmail(email);
        if (existingUser) {
            return res.status(400).json({
                success: false,
                error: 'البريد الإلكتروني مسجل مسبقاً'
            });
        }

        // إنشاء المستخدم
        const userId = await User.create({ full_name, email, phone, password });
        
        // جلب بيانات المستخدم
        const user = await User.findById(userId);
        
        // إنشاء توكن JWT
        const token = jwt.sign(
            { 
                id: user.id, 
                email: user.email,
                full_name: user.full_name,
                role: user.role 
            },
            process.env.JWT_SECRET || 'your-secret-key',
            { expiresIn: '7d' }
        );

        res.status(201).json({
            success: true,
            message: 'تم التسجيل بنجاح',
            token,
            user: {
                id: user.id,
                full_name: user.full_name,
                email: user.email,
                phone: user.phone,
                balance: user.balance || 0,
                role: user.role
            }
        });
    } catch (error) {
        console.error('❌ خطأ في التسجيل:', error.message);
        res.status(500).json({
            success: false,
            error: 'حدث خطأ أثناء التسجيل'
        });
    }
};

// ===== تسجيل دخول =====
const login = async (req, res) => {
    try {
        const { email, password } = req.body;

        // التحقق من وجود البريد وكلمة المرور
        if (!email || !password) {
            return res.status(400).json({
                success: false,
                error: 'البريد الإلكتروني وكلمة المرور مطلوبان'
            });
        }

        // التحقق من صحة البيانات
        const user = await User.verifyPassword(email, password);
        if (!user) {
            return res.status(401).json({
                success: false,
                error: 'البريد الإلكتروني أو كلمة المرور غير صحيحة'
            });
        }

        // التحقق من أن الحساب نشط
        if (!user.is_active) {
            return res.status(401).json({
                success: false,
                error: 'الحساب غير مفعل، يرجى التواصل مع الدعم'
            });
        }

        // إنشاء توكن JWT
        const token = jwt.sign(
            { 
                id: user.id, 
                email: user.email,
                full_name: user.full_name,
                role: user.role 
            },
            process.env.JWT_SECRET || 'your-secret-key',
            { expiresIn: '7d' }
        );

        res.json({
            success: true,
            message: 'تم تسجيل الدخول بنجاح',
            token,
            user: {
                id: user.id,
                full_name: user.full_name,
                email: user.email,
                phone: user.phone,
                balance: user.balance || 0,
                role: user.role
            }
        });
    } catch (error) {
        console.error('❌ خطأ في تسجيل الدخول:', error.message);
        res.status(500).json({
            success: false,
            error: 'حدث خطأ أثناء تسجيل الدخول'
        });
    }
};

// ===== تسجيل خروج =====
const logout = async (req, res) => {
    try {
        // لا حاجة لفعل شيء مع JWT، العميل سيمسح التوكن
        res.json({
            success: true,
            message: 'تم تسجيل الخروج بنجاح'
        });
    } catch (error) {
        console.error('❌ خطأ في تسجيل الخروج:', error.message);
        res.status(500).json({
            success: false,
            error: 'حدث خطأ أثناء تسجيل الخروج'
        });
    }
};

// ===== التحقق من التوكن =====
const verify = async (req, res) => {
    try {
        const token = req.header('Authorization')?.replace('Bearer ', '');
        
        if (!token) {
            return res.status(401).json({
                success: false,
                error: 'لا يوجد توكن'
            });
        }

        const decoded = jwt.verify(token, process.env.JWT_SECRET || 'your-secret-key');
        const user = await User.findById(decoded.id);
        
        if (!user) {
            return res.status(401).json({
                success: false,
                error: 'مستخدم غير موجود'
            });
        }

        res.json({
            success: true,
            valid: true,
            user: {
                id: user.id,
                full_name: user.full_name,
                email: user.email,
                phone: user.phone,
                balance: user.balance || 0,
                role: user.role
            }
        });
    } catch (error) {
        res.status(401).json({
            success: false,
            error: 'توكن غير صالح'
        });
    }
};

// ===== الحصول على معلومات المستخدم =====
const getProfile = async (req, res) => {
    try {
        const user = await User.findById(req.userId);
        if (!user) {
            return res.status(404).json({
                success: false,
                error: 'المستخدم غير موجود'
            });
        }

        res.json({
            success: true,
            user: {
                id: user.id,
                full_name: user.full_name,
                email: user.email,
                phone: user.phone,
                balance: user.balance || 0,
                role: user.role,
                created_at: user.created_at
            }
        });
    } catch (error) {
        console.error('❌ خطأ في جلب الملف الشخصي:', error.message);
        res.status(500).json({
            success: false,
            error: 'حدث خطأ أثناء جلب الملف الشخصي'
        });
    }
};

module.exports = {
    register,
    login,
    logout,
    verify,
    getProfile
};
