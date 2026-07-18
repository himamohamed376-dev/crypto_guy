const pool = require('../config/database');

class Investment {
    // إنشاء فرصة استثمارية جديدة
    static async create(investmentData) {
        const { title, description, category, target_amount, min_investment, 
                expected_return, duration_months, risk_level, image_url, start_date, end_date, created_by } = investmentData;
        
        const [result] = await pool.execute(
            `INSERT INTO investments (title, description, category, target_amount, min_investment, 
             expected_return, duration_months, risk_level, image_url, start_date, end_date, created_by, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')`,
            [title, description, category, target_amount, min_investment, expected_return, 
             duration_months, risk_level, image_url, start_date, end_date, created_by]
        );
        
        return result.insertId;
    }

    // الحصول على جميع الفرص النشطة
    static async getActiveInvestments() {
        const [rows] = await pool.execute(
            `SELECT i.*, u.full_name as creator_name 
             FROM investments i 
             LEFT JOIN users u ON i.created_by = u.id 
             WHERE i.status = 'active' 
             ORDER BY i.created_at DESC`
        );
        return rows;
    }

    // الحصول على تفاصيل فرصة معينة
    static async getById(id) {
        const [rows] = await pool.execute(
            `SELECT i.*, u.full_name as creator_name,
             (SELECT COUNT(*) FROM user_investments WHERE investment_id = i.id) as investor_count
             FROM investments i 
             LEFT JOIN users u ON i.created_by = u.id 
             WHERE i.id = ?`,
            [id]
        );
        return rows[0];
    }

    // تحديث المبلغ الحالي للاستثمار
    static async updateCurrentAmount(investmentId, amount) {
        await pool.execute(
            'UPDATE investments SET current_amount = current_amount + ? WHERE id = ?',
            [amount, investmentId]
        );
    }

    // تحديث حالة الاستثمار
    static async updateStatus(id, status) {
        await pool.execute(
            'UPDATE investments SET status = ? WHERE id = ?',
            [status, id]
        );
    }
}

module.exports = Investment;
