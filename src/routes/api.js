// =============================================
// روابط API العامة
// =============================================

const express = require('express');
const router = express.Router();
const { authenticate } = require('../middleware/auth');

// ===== معلومات المستخدم =====
router.get('/users/me', authenticate, async (req, res) => {
    try {
        res.json({
            user: req.user
        });
    } catch (error) {
        res.status(500).json({ error: error.message });
    }
});

// ===== حالة الخادم =====
router.get('/status', (req, res) => {
    res.json({
        status: 'running',
        timestamp: new Date().toISOString(),
        uptime: process.uptime()
    });
});

// ===== مثال لبيانات وهمية =====
router.get('/investments', (req, res) => {
    res.json([
        {
            id: 1,
            title: 'مشروع الطاقة الشمسية',
            description: 'استثمار في محطة طاقة شمسية صديقة للبيئة',
            target_amount: 500000,
            current_amount: 320000,
            expected_return: 15.5,
            risk_level: 'low',
            status: 'active'
        },
        {
            id: 2,
            title: 'تطوير عقاري سكني',
            description: 'مشروع بناء مجمع سكني فاخر',
            target_amount: 2000000,
            current_amount: 850000,
            expected_return: 22.0,
            risk_level: 'medium',
            status: 'active'
        }
    ]);
});

module.exports = router;
