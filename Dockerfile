# 1. استخدام نسخة PHP رسمية مدمج معها سيرفر Apache
FROM php:8.2-apache

# 2. تفعيل إضافة mysqli للاتصال بقاعدة بيانات MySQL
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# 3. تفعيل مود الـ Rewrite في Apache لتشغيل الروابط بشكل صحيح
RUN a2enmod rewrite

# 4. نسخ ملفات مشروعك بالكامل إلى مجلد السيرفر
COPY . /var/www/html/

# 5. ضبط صلاحيات الملفات ليتمكن السيرفر من قراءتها وتعديلها
RUN chown -R www-data:www-data /var/www/html/

# 6. فتح المنفذ 80 الخاص بمرور بيانات الويب
EXPOSE 80
