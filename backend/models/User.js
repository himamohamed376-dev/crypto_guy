const pool = require('../config/database');
const bcrypt = require('bcrypt');

class User {
    // إنشاء مستخدم جديد
    static async create(userData) {
        const { full_name, email, phone, password, role = 'user' } = userData;
        const password_hash = await bcrypt.hash(password, 10);
        
        const [result] = await pool.execute(
            `INSERT INTO users (full_name, email, phone, password_hash, role) 
             VALUES (?, ?, ?, ?, ?)`,
            [full_name, email, phone, password_hash, role]
        );
        
        return result.insertId;
    }

    // البحث عن مستخدم بالبريد الإلكتروني
    static async findByEmail(email) {
        const [rows] = await pool.execute(
            'SELECT * FROM users WHERE email = ?',
            [email]
        );
        return rows[0];
    }

    // البحث عن مستخدم بالرقم
    static async findById(id) {
        const [rows] = await pool.execute(
            'SELECT id, full_name, email, phone, balance, role, two_factor_enabled, created_at, last_login, is_active FROM users WHERE id = ?',
            [id]
        );
        return rows[0];
    }

    // تحديث رصيد المستخدم
    static async updateBalance(userId, amount) {
        await pool.execute(
            'UPDATE users SET balance = balance + ? WHERE id = ?',
            [amount, userId]
        );
    }

    // تحديث آخر تسجيل دخول
    static async updateLastLogin(userId) {
        await pool.execute(
            'UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?',
            [userId]
        );
    }

    // التحقق من كلمة المرور
    static async verifyPassword(email, password) {
        const user = await this.findByEmail(email);
        if (!user) return false;
        
        const isValid = await bcrypt.compare(password, user.password_hash);
        return isValid ? user : false;
    }
}

module.exports = User;
