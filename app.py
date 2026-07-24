# app.py - تطبيق Flask كامل مع اتصال PostgreSQL على Neon

import os
import hashlib
import secrets
from datetime import datetime, timedelta
from flask import Flask, request, jsonify, session
import psycopg2
from psycopg2.extras import RealDictCursor
from dotenv import load_dotenv

# تحميل متغيرات البيئة
load_dotenv()

app = Flask(__name__)
app.secret_key = secrets.token_hex(32)

# دالة الاتصال بقاعدة البيانات مع sslmode=require
def get_db_connection():
    return psycopg2.connect(
        os.getenv('DATABASE_URL'),
        sslmode='require',
        cursor_factory=RealDictCursor
    )

# دالة لتشفير كلمة السر
def hash_password(password):
    return hashlib.sha256(password.encode()).hexdigest()

# دالة للتحقق من كلمة السر
def verify_password(password, hashed):
    return hash_password(password) == hashed

# دالة إنشاء توكن جلسة
def generate_token():
    return secrets.token_urlsafe(32)

# ============== صفحة تسجيل مستخدم جديد ==============
@app.route('/register', methods=['POST'])
def register():
    data = request.get_json()
    
    # التحقق من وجود جميع الحقول
    if not all(k in data for k in ['full_name', 'email', 'password']):
        return jsonify({'error': 'جميع الحقول مطلوبة'}), 400
    
    full_name = data['full_name']
    email = data['email'].lower()
    password = data['password']
    
    # التحقق من طول كلمة السر
    if len(password) < 6:
        return jsonify({'error': 'كلمة السر يجب أن تكون 6 أحرف على الأقل'}), 400
    
    conn = get_db_connection()
    cur = conn.cursor()
    
    try:
        # التحقق من عدم وجود الايميل مسبقاً
        cur.execute("SELECT id FROM users WHERE email = %s", (email,))
        if cur.fetchone():
            return jsonify({'error': 'هذا الايميل مسجل بالفعل'}), 400
        
        # إضافة المستخدم الجديد
        password_hash = hash_password(password)
        cur.execute("""
            INSERT INTO users (full_name, email, password_hash, balance)
            VALUES (%s, %s, %s, %s)
            RETURNING id, full_name, email, balance
        """, (full_name, email, password_hash, 100.00))  # رصيد ابتدائي 100
        
        new_user = cur.fetchone()
        conn.commit()
        
        return jsonify({
            'message': 'تم التسجيل بنجاح',
            'user': dict(new_user)
        }), 201
        
    except Exception as e:
        conn.rollback()
        return jsonify({'error': str(e)}), 500
    finally:
        cur.close()
        conn.close()

# ============== صفحة تسجيل الدخول ==============
@app.route('/login', methods=['POST'])
def login():
    data = request.get_json()
    
    if not all(k in data for k in ['email', 'password']):
        return jsonify({'error': 'البريد الإلكتروني وكلمة السر مطلوبان'}), 400
    
    email = data['email'].lower()
    password = data['password']
    
    conn = get_db_connection()
    cur = conn.cursor()
    
    try:
        # البحث عن المستخدم
        cur.execute("""
            SELECT id, full_name, email, password_hash, balance
            FROM users WHERE email = %s
        """, (email,))
        
        user = cur.fetchone()
        
        if not user or not verify_password(password, user['password_hash']):
            return jsonify({'error': 'بريد إلكتروني أو كلمة سر غير صحيحة'}), 401
        
        # إنشاء توكن جلسة
        token = generate_token()
        expires_at = datetime.now() + timedelta(days=7)
        
        # حفظ الجلسة في قاعدة البيانات
        cur.execute("""
            INSERT INTO sessions (user_id, token, expires_at)
            VALUES (%s, %s, %s)
            RETURNING id, token, expires_at
        """, (user['id'], token, expires_at))
        
        session_data = cur.fetchone()
        conn.commit()
        
        # تخزين التوكن في سيشين Flask
        session['user_id'] = user['id']
        session['token'] = token
        
        return jsonify({
            'message': 'تم تسجيل الدخول بنجاح',
            'user': {
                'id': user['id'],
                'full_name': user['full_name'],
                'email': user['email'],
                'balance': float(user['balance'])
            },
            'session': dict(session_data)
        }), 200
        
    except Exception as e:
        conn.rollback()
        return jsonify({'error': str(e)}), 500
    finally:
        cur.close()
        conn.close()

# ============== عرض الرصيد ==============
@app.route('/balance', methods=['GET'])
def get_balance():
    # التحقق من وجود توكن في السيشين
    if 'user_id' not in session or 'token' not in session:
        return jsonify({'error': 'غير مسجل دخول. يرجى تسجيل الدخول أولاً'}), 401
    
    user_id = session['user_id']
    token = session['token']
    
    conn = get_db_connection()
    cur = conn.cursor()
    
    try:
        # التحقق من صحة الجلسة
        cur.execute("""
            SELECT * FROM sessions 
            WHERE user_id = %s AND token = %s AND expires_at > NOW()
        """, (user_id, token))
        
        session_data = cur.fetchone()
        
        if not session_data:
            session.clear()
            return jsonify({'error': 'انتهت صلاحية الجلسة. يرجى تسجيل الدخول مرة أخرى'}), 401
        
        # جلب بيانات المستخدم
        cur.execute("""
            SELECT id, full_name, email, balance
            FROM users WHERE id = %s
        """, (user_id,))
        
        user = cur.fetchone()
        
        if not user:
            session.clear()
            return jsonify({'error': 'المستخدم غير موجود'}), 404
        
        return jsonify({
            'user': {
                'id': user['id'],
                'full_name': user['full_name'],
                'email': user['email'],
                'balance': float(user['balance'])
            }
        }), 200
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500
    finally:
        cur.close()
        conn.close()

# ============== تسجيل الخروج ==============
@app.route('/logout', methods=['POST'])
def logout():
    if 'user_id' in session and 'token' in session:
        conn = get_db_connection()
        cur = conn.cursor()
        try:
            # حذف الجلسة من قاعدة البيانات
            cur.execute("""
                DELETE FROM sessions 
                WHERE user_id = %s AND token = %s
            """, (session['user_id'], session['token']))
            conn.commit()
        except:
            pass
        finally:
            cur.close()
            conn.close()
    
    session.clear()
    return jsonify({'message': 'تم تسجيل الخروج بنجاح'}), 200

# ============== صفحة رئيسية للاختبار ==============
@app.route('/', methods=['GET'])
def home():
    return jsonify({
        'message': 'API التشغيل بنجاح',
        'endpoints': {
            'POST /register': 'تسجيل مستخدم جديد',
            'POST /login': 'تسجيل الدخول',
            'GET /balance': 'عرض الرصيد (يتطلب تسجيل دخول)',
            'POST /logout': 'تسجيل الخروج'
        }
    })

if __name__ == '__main__':
    port = int(os.environ.get('PORT', 5000))
    app.run(host='0.0.0.0', port=port, debug=False)
