// =============================================
// إعدادات الاتصال بقاعدة البيانات
// =============================================

const mysql = require('mysql2/promise');
require('dotenv').config();

// إنشاء pool للاتصالات
const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'investment_platform',
    waitForConnections: true,
    connectionLimit: 5, // تقليل العدد للاستخدام المجاني
    queueLimit: 0,
    charset: 'utf8mb4',
    timezone: '+00:00'
});

// اختبار الاتصال
(async () => {
    try {
        const connection = await pool.getConnection();
        console.log('✅ تم الاتصال بقاعدة البيانات بنجاح');
        console.log(`📊 قاعدة البيانات: ${process.env.DB_NAME}`);
        connection.release();
    } catch (error) {
        console.error('❌ فشل الاتصال بقاعدة البيانات:');
        console.error(`   - ${error.message}`);
        console.log('⚠️  سيعمل الخادم بدون قاعدة بيانات (وضع القراءة فقط)');
    }
})();

module.exports = pool;
