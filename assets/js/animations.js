/**
 * =============================================
 * ملف التحريك والتفاعلات - JavaScript
 * Animations & Interactions - JavaScript
 * =============================================
 */

// =============================================
// 1. ظهور العناصر عند التمرير (Scroll Reveal)
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    // اختيار جميع العناصر التي تحتاج للظهور عند التمرير
    const revealElements = document.querySelectorAll('.scroll-reveal, .scroll-reveal-left, .scroll-reveal-right, .scroll-reveal-scale');
    
    // إعداد Intersection Observer
    const revealObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // إضافة كلاس visible
                entry.target.classList.add('visible');
                // يمكن إضافة تأخير عشوائي لتأثير أفضل
                const delay = Math.random() * 0.5;
                entry.target.style.transitionDelay = delay + 's';
            }
        });
    }, {
        threshold: 0.1, // 10% من العنصر ظاهر
        rootMargin: '0px 0px -50px 0px' // يظهر قبل وصوله بقليل
    });
    
    // مراقبة كل عنصر
    revealElements.forEach(element => {
        revealObserver.observe(element);
    });
});

// =============================================
// 2. عد الأرقام المتحركة (Number Counter)
// =============================================

class NumberCounter {
    constructor(element, targetNumber, duration = 2000) {
        this.element = element;
        this.target = parseFloat(targetNumber);
        this.duration = duration;
        this.current = 0;
        this.startTime = null;
        this.isRunning = false;
    }
    
    // بدء العد
    start() {
        if (this.isRunning) return;
        this.isRunning = true;
        this.current = 0;
        this.startTime = performance.now();
        this.update();
    }
    
    // تحديث القيمة
    update() {
        const currentTime = performance.now();
        const elapsed = currentTime - this.startTime;
        const progress = Math.min(elapsed / this.duration, 1);
        
        // Easing function (تسارع وتيرة)
        const easeOutCubic = 1 - Math.pow(1 - progress, 3);
        this.current = this.target * easeOutCubic;
        
        // عرض القيمة مع التنسيق
        if (Number.isInteger(this.target)) {
            this.element.textContent = Math.round(this.current);
        } else {
            this.element.textContent = this.current.toFixed(2);
        }
        
        // متابعة حتى الانتهاء
        if (progress < 1) {
            requestAnimationFrame(() => this.update());
        } else {
            this.isRunning = false;
            // عرض القيمة النهائية
            this.element.textContent = Number.isInteger(this.target) ? 
                Math.round(this.target) : this.target.toFixed(2);
        }
    }
}

// تفعيل عد الأرقام
document.addEventListener('DOMContentLoaded', function() {
    const counters = document.querySelectorAll('[data-count]');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const element = entry.target;
                const target = parseFloat(element.getAttribute('data-count'));
                const duration = parseInt(element.getAttribute('data-duration')) || 2000;
                const counter = new NumberCounter(element, target, duration);
                counter.start();
                counterObserver.unobserve(element);
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(counter => {
        counterObserver.observe(counter);
    });
});

// =============================================
// 3. تأثير التموج على الأزرار (Ripple Effect)
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    const rippleButtons = document.querySelectorAll('.ripple-effect');
    
    rippleButtons.forEach(button => {
        button.addEventListener('click', function(e) {
            // إزالة تأثيرات سابقة
            const oldRipple = this.querySelector('.ripple');
            if (oldRipple) oldRipple.remove();
            
            // إنشاء عنصر التموج
            const ripple = document.createElement('span');
            ripple.className = 'ripple';
            
            // حساب موقع التموج
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            // إضافة أنماط التموج
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                pointer-events: none;
                transform: scale(0);
                animation: ripple 0.6s ease-out;
            `;
            
            this.style.position = 'relative';
            this.style.overflow = 'hidden';
            this.appendChild(ripple);
            
            // حذف التموج بعد انتهاء الأنيميشن
            setTimeout(() => {
                ripple.remove();
            }, 700);
        });
    });
});

// =============================================
// 4. مؤشر التحميل (Loading Indicator)
// =============================================

class LoadingIndicator {
    constructor(options = {}) {
        this.options = {
            container: document.body,
            message: 'جاري التحميل...',
            ...options
        };
        this.isVisible = false;
        this.element = null;
    }
    
    // عرض مؤشر التحميل
    show() {
        if (this.isVisible) return;
        this.isVisible = true;
        
        // إنشاء العنصر
        this.element = document.createElement('div');
        this.element.className = 'loading-overlay';
        this.element.innerHTML = `
            <div class="loading-content">
                <div class="loading-spinner"></div>
                <p>${this.options.message}</p>
            </div>
        `;
        
        // إضافة أنماط
        this.element.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        // إضافة المحتوى
        const content = this.element.querySelector('.loading-content');
        content.style.cssText = `
            text-align: center;
            color: white;
        `;
        
        const spinner = this.element.querySelector('.loading-spinner');
        spinner.style.cssText = `
            width: 50px;
            height: 50px;
            margin: 0 auto 20px;
            border: 4px solid rgba(255, 255, 255, 0.1);
            border-top: 4px solid #f7931a;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        `;
        
        this.options.container.appendChild(this.element);
        
        // إظهار مع تأثير
        requestAnimationFrame(() => {
            this.element.style.opacity = '1';
        });
    }
    
    // إخفاء مؤشر التحميل
    hide() {
        if (!this.isVisible || !this.element) return;
        this.isVisible = false;
        
        this.element.style.opacity = '0';
        setTimeout(() => {
            if (this.element && this.element.parentNode) {
                this.element.parentNode.removeChild(this.element);
                this.element = null;
            }
        }, 300);
    }
}

// =============================================
// 5. تأثير التمرير السلس (Smooth Scroll)
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    const smoothLinks = document.querySelectorAll('a[href^="#"]:not([href="#"])');
    
    smoothLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const targetId = this.getAttribute('href');
            const target = document.querySelector(targetId);
            
            if (target) {
                e.preventDefault();
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start',
                    duration: 800
                });
            }
        });
    });
});

// =============================================
// 6. أداة الإشعارات (Toast Notifications)
// =============================================

class Toast {
    constructor(options = {}) {
        this.options = {
            position: 'top-right',
            duration: 5000,
            ...options
        };
    }
    
    // عرض إشعار
    show(message, type = 'info', duration = null) {
        const colors = {
            success: '#2ecc71',
            error: '#e74c3c',
            warning: '#f39c12',
            info: '#3498db'
        };
        
        const icons = {
            success: '✅',
            error: '❌',
            warning: '⚠️',
            info: 'ℹ️'
        };
        
        // إنشاء عنصر الإشعار
        const toast = document.createElement('div');
        toast.className = 'toast-notification';
        toast.innerHTML = `
            <div class="toast-icon">${icons[type] || '📢'}</div>
            <div class="toast-message">${message}</div>
            <button class="toast-close">✕</button>
        `;
        
        // أنماط الإشعار
        toast.style.cssText = `
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 20px;
            background: #1a1a2e;
            color: white;
            border-radius: 12px;
            border-right: 4px solid ${colors[type] || '#3498db'};
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            margin-bottom: 10px;
            min-width: 300px;
            max-width: 500px;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        `;
        
        // إضافة أنماط للعناصر الداخلية
        const icon = toast.querySelector('.toast-icon');
        icon.style.cssText = `
            font-size: 24px;
        `;
        
        const messageEl = toast.querySelector('.toast-message');
        messageEl.style.cssText = `
            flex: 1;
            font-size: 14px;
            font-weight: 500;
        `;
        
        const closeBtn = toast.querySelector('.toast-close');
        closeBtn.style.cssText = `
            background: none;
            border: none;
            color: rgba(255, 255, 255, 0.5);
            cursor: pointer;
            font-size: 18px;
            padding: 5px;
            transition: color 0.3s ease;
        `;
        
        closeBtn.addEventListener('mouseenter', () => {
            closeBtn.style.color = 'white';
        });
        
        closeBtn.addEventListener('click', () => {
            this.remove(toast);
        });
        
        // إضافة شريط التقدم
        const progressBar = document.createElement('div');
        progressBar.style.cssText = `
            position: absolute;
            bottom: 0;
            left: 0;
            height: 3px;
            background: ${colors[type] || '#3498db'};
            width: 100%;
            animation: progressBar ${(duration || this.options.duration) / 1000}s linear forwards;
        `;
        toast.appendChild(progressBar);
        
        // إضافة الإشعار للحاوية
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                display: flex;
                flex-direction: column;
                align-items: flex-end;
                gap: 10px;
                max-width: 100%;
                padding: 10px;
                pointer-events: none;
            `;
            document.body.appendChild(container);
        }
        
        // إضافة الإشعار
        container.appendChild(toast);
        toast.style.pointerEvents = 'auto';
        
        // إظهار الإشعار مع أنيميشن
        requestAnimationFrame(() => {
            toast.style.transform = 'translateX(0)';
            toast.style.opacity = '1';
        });
        
        // إزالة بعد المدة
        const timeout = setTimeout(() => {
            this.remove(toast);
        }, duration || this.options.duration);
        
        // إيقاف المؤقت عند التمرير
        toast.addEventListener('mouseenter', () => {
            clearTimeout(timeout);
            const progress = toast.querySelector('.progress-bar');
            if (progress) {
                progress.style.animationPlayState = 'paused';
            }
        });
        
        toast.addEventListener('mouseleave', () => {
            // إعادة تشغيل المؤقت
        });
        
        return toast;
    }
    
    // إزالة الإشعار
    remove(toast) {
        if (!toast || !toast.parentNode) return;
        toast.style.transform = 'translateX(100%)';
        toast.style.opacity = '0';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
                // إزالة الحاوية إذا كانت فارغة
                const container = document.querySelector('.toast-container');
                if (container && container.children.length === 0) {
                    container.remove();
                }
            }
        }, 500);
    }
}

// =============================================
// 7. تحريك الأيقونات عند التمرير
// =============================================

document.addEventListener('DOMContentLoaded', function() {
    const animatedIcons = document.querySelectorAll('.animate-icon');
    
    const iconObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const icon = entry.target;
                const animation = icon.getAttribute('data-animation') || 'bounce';
                icon.classList.add(`animate-${animation}`);
                iconObserver.unobserve(icon);
            }
        });
    }, { threshold: 0.5 });
    
    animatedIcons.forEach(icon => {
        iconObserver.observe(icon);
    });
});

// =============================================
// 8. تأثير الخلفية التفاعلية (Parallax)
// =============================================

document.addEventListener('mousemove', function(e) {
    const parallaxElements = document.querySelectorAll('.parallax');
    
    parallaxElements.forEach(element => {
        const speed = element.getAttribute('data-speed') || 0.05;
        const x = (window.innerWidth - e.pageX * speed) / 100;
        const y = (window.innerHeight - e.pageY * speed) / 100;
        element.style.transform = `translate(${x}px, ${y}px)`;
    });
});

// =============================================
// 9. تصدير الكلاسز والدوال للاستخدام العام
// =============================================

window.NumberCounter = NumberCounter;
window.LoadingIndicator = LoadingIndicator;
window.Toast = Toast;

// =============================================
// 10. تحريك الصفحة عند التحميل الكامل
// =============================================

// إخفاء الـ Preloader
window.addEventListener('load', function() {
    const preloader = document.querySelector('.preloader');
    if (preloader) {
        setTimeout(() => {
            preloader.classList.add('hidden');
        }, 800);
    }
});

console.log('🎬 تم تحميل ملف التحريك بنجاح!');
