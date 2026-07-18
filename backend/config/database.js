// إعدادات الاتصال بقاعدة البيانات MySQL
const mysql = require('mysql2/promise');
require('dotenv').config();

const pool = mysql.createPool({
    host: process.env.DB_HOST || 'localhost',
    user: process.env.DB_USER || 'root',
    password: process.env.DB_PASSWORD || '',
    database: process.env.DB_NAME || 'investment_platform',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0,
    charset: 'utf8mb4'
});

// اختبار الاتصال
(async () => {
    try {
        const connection = await pool.getConnection();
        console.log('✅ تم الاتصال بقاعدة البيانات بنجاح');
        connection.release();
    } catch (error) {
        console.error('❌ فشل الاتصال بقاعدة البيانات:', error.message);
    }
})();

module.exports = pool;
