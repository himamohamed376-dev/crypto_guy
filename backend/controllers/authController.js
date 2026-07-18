const User = require('../models/User');
const jwt = require('jsonwebtoken');
const speakeasy = require('speakeasy');
const QRCode = require('qrcode');
const { sendVerificationEmail } = require('../utils/emailService');

// تسجيل مستخدم جديد
exports.register = async (req, res) => {
    try {
        const { full_name, email, phone, password, confirm_password } = req.body;

        // التحقق من تطابق كلمة المرور
        if (password !== confirm_password) {
            return res.status(400).json({ error: 'كلمتا المرور غير متطابقتين' });
        }

        // التحقق من وجود البريد الإلكتروني
        const existingUser = await User.findByEmail(email);
        if (existingUser) {
            return res.status(400).json({ error: 'البريد الإلكتروني مسجل مسبقاً' });
        }

        // إنشاء المستخدم
        const userId = await User.create({ full_name, email, phone, password });
        
        // إنشاء توكن JWT
        const token = jwt.sign(
            { id: userId, email },
            process.env.JWT_SECRET || 'your-secret-key',
            { expiresIn: '7d' }
        );

        // إرسال بريد ترحيبي
        await sendVerificationEmail(email, full_name);

        res.status(201).json({
            message: 'تم التسجيل بنجاح',
            token,
            user: { id: userId, full_name, email, phone }
        });
    } catch (error) {
        console.error('Register error:', error);
        res.status(500).json({ error: 'حدث خطأ أثناء التسجيل' });
    }
};

// تسجيل دخول
exports.login = async (req, res) => {
    try {
        const { email, password } = req.body;

        const user = await User.verifyPassword(email, password);
        if (!user) {
            return res.status(401).json({ error: 'البريد الإلكتروني أو كلمة المرور غير صحيحة' });
        }

        if (!user.is_active) {
            return res.status(401).json({ error: 'الحساب غير مفعل، يرجى التواصل مع الدعم' });
        }

        // تحديث آخر تسجيل دخول
        await User.updateLastLogin(user.id);

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
        console.error('Login error:', error);
        res.status(500).json({ error: 'حدث خطأ أثناء تسجيل الدخول' });
    }
};

// تفعيل التحقق بخطوتين
exports.enableTwoFactor = async (req, res) => {
    try {
        const userId = req.userId;
        
        // إنشاء سري جديد
        const secret = speakeasy.generateSecret({
            name: `InvestmentPlatform:${req.user.email}`
        });

        // حفظ السري في قاعدة البيانات
        await pool.execute(
            'UPDATE users SET two_factor_secret = ?, two_factor_enabled = TRUE WHERE id = ?',
            [secret.base32, userId]
        );

        // إنشاء رابط QR Code
        const qrCodeUrl = await QRCode.toDataURL(secret.otpauth_url);

        res.json({
            message: 'تم تفعيل التحقق بخطوتين',
            secret: secret.base32,
            qrCode: qrCodeUrl
        });
    } catch (error) {
        console.error('2FA enable error:', error);
        res.status(500).json({ error: 'حدث خطأ أثناء تفعيل التحقق بخطوتين' });
    }
};

// التحقق من رمز التحقق بخطوتين
exports.verifyTwoFactor = async (req, res) => {
    try {
        const { token } = req.body;
        const userId = req.userId;

        const [rows] = await pool.execute(
            'SELECT two_factor_secret FROM users WHERE id = ?',
            [userId]
        );

        if (!rows[0]) {
            return res.status(400).json({ error: 'التحقق بخطوتين غير مفعل' });
        }

        const verified = speakeasy.totp.verify({
            secret: rows[0].two_factor_secret,
            encoding: 'base32',
            token: token
        });

        if (verified) {
            res.json({ message: 'تم التحقق بنجاح' });
        } else {
            res.status(400).json({ error: 'رمز التحقق غير صحيح' });
        }
    } catch (error) {
        console.error('2FA verify error:', error);
        res.status(500).json({ error: 'حدث خطأ أثناء التحقق' });
    }
};
