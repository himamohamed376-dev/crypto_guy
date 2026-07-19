// =============================================
// روابط المصادقة
// =============================================

const express = require('express');
const router = express.Router();
const authController = require('../controllers/authController');
const { authenticate } = require('../middleware/auth');

// ===== روابط عامة =====
router.post('/register', authController.register);
router.post('/login', authController.login);
router.post('/logout', authController.logout);
router.get('/verify', authController.verify);

// ===== روابط محمية =====
router.get('/profile', authenticate, authController.getProfile);

module.exports = router;
