// =============================================
// نموذج المستخدم
// =============================================

const pool = require('../config/database');
const bcrypt = require('bcrypt');

class User {
    // إنشاء مستخدم جديد
    static async create(userData) {
        try {
            const { full_name, email, phone, password, role = 'user' } = userData;
            const password_hash = await bcrypt.hash(password, 10);
            
            const [result] = await pool.execute(
                `INSERT INTO users (full_name, email, phone, password_hash, role) 
                 VALUES (?, ?, ?, ?, ?)`,
                [full_name, email, phone, password_hash, role]
            );
            
            return result.insertId;
        } catch (error) {
            console.error('❌ خطأ في إنشاء المستخدم:', error.message);
            throw error;
        }
    }

    // البحث عن مستخدم بالبريد الإلكتروني
    static async findByEmail(email) {
        try {
            const [rows] = await pool.execute(
                'SELECT * FROM users WHERE email = ?',
                [email]
            );
            return rows[0];
        } catch (error) {
            console.error('❌ خطأ في البحث عن المستخدم:', error.message);
            return null;
        }
    }

    // البحث عن مستخدم بالرقم
    static async findById(id) {
        try {
            const [rows] = await pool.execute(
                `SELECT id, full_name, email, phone, balance, role, 
                        two_factor_enabled, created_at, last_login, is_active 
                 FROM users WHERE id = ?`,
                [id]
            );
            return rows[0];
        } catch (error) {
            console.error('❌ خطأ في البحث عن المستخدم:', error.message);
            return null;
        }
    }

    // تحديث رصيد المستخدم
    static async updateBalance(userId, amount) {
        try {
            await pool.execute(
                'UPDATE users SET balance = balance + ? WHERE id = ?',
                [amount, userId]
            );
            return true;
        } catch (error) {
            console.error('❌ خطأ في تحديث الرصيد:', error.message);
            return false;
        }
    }

    // التحقق من كلمة المرور
    static async verifyPassword(email, password) {
        try {
            const user = await this.findByEmail(email);
            if (!user) return false;
            
            const isValid = await bcrypt.compare(password, user.password_hash);
            return isValid ? user : false;
        } catch (error) {
            console.error('❌ خطأ في التحقق من كلمة المرور:', error.message);
            return false;
        }
    }
}

module.exports = User;
