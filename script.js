// ==========================================
// data.js - إدارة البيانات
// ==========================================

const DEFAULT_DATA = {
    users: [
        {
            id: 1,
            email: 'admin@cryptoinvest.com',
            password: 'Admin@123',
            fullName: 'مدير النظام',
            phone: '+966500000000',
            referralCode: 'ADMIN001',
            referredBy: null,
            balance: 5000.00,
            investedBalance: 2500.00,
            totalWithdraw: 1000.00,
            totalDeposit: 3500.00,
            totalProfit: 850.00,
            referralBonus: 200.00,
            role: 'super_admin',
            status: 'active',
            createdAt: new Date().toISOString()
        },
        {
            id: 2,
            email: 'user@example.com',
            password: 'User@123',
            fullName: 'أحمد محمد',
            phone: '+966500000001',
            referralCode: 'USER001',
            referredBy: 1,
            balance: 850.00,
            investedBalance: 500.00,
            totalWithdraw: 150.00,
            totalDeposit: 1000.00,
            totalProfit: 125.00,
            referralBonus: 25.00,
            role: 'user',
            status: 'active',
            createdAt: new Date().toISOString()
        }
    ],
    
    vipPlans: [
        {
            id: 1,
            name: 'VIP 1',
            level: 1,
            minAmount: 10,
            dailyReturn: 2.00,
            durationDays: 30,
            description: 'باقة للمبتدئين مع عائد 2% يومياً',
            icon: 'fa-star',
            color: '#00d4aa'
        },
        {
            id: 2,
            name: 'VIP 2',
            level: 2,
            minAmount: 50,
            dailyReturn: 3.00,
            durationDays: 45,
            description: 'باقة متوسطة مع عائد 3% يومياً',
            icon: 'fa-gem',
            color: '#4a9eff'
        },
        {
            id: 3,
            name: 'VIP 3',
            level: 3,
            minAmount: 200,
            dailyReturn: 4.00,
            durationDays: 60,
            description: 'باقة متقدمة مع عائد 4% يومياً',
            icon: 'fa-crown',
            color: '#f0b90b'
        },
        {
            id: 4,
            name: 'VIP 4',
            level: 4,
            minAmount: 500,
            dailyReturn: 5.00,
            durationDays: 90,
            description: 'باقة مميزة مع عائد 5% يومياً',
            icon: 'fa-rocket',
            color: '#ff6b6b'
        }
    ],
    
    investments: [
        {
            id: 1,
            userId: 2,
            planId: 1,
            amount: 50.00,
            dailyProfit: 1.00,
            totalProfit: 8.00,
            startDate: new Date(Date.now() - 8 * 86400000).toISOString().split('T')[0],
            endDate: new Date(Date.now() + 22 * 86400000).toISOString().split('T')[0],
            status: 'active'
        }
    ],
    
    deposits: [
        {
            id: 1,
            userId: 2,
            amount: 100.00,
            transactionHash: '0x1234567890abcdef1234567890abcdef12345678',
            status: 'confirmed',
            createdAt: new Date(Date.now() - 10 * 86400000).toISOString()
        }
    ],
    
    withdrawals: [
        {
            id: 1,
            userId: 2,
            amount: 50.00,
            walletAddress: '0x8D5D2F43b6E9eE5C8F9c0cC5E6f7D8A9B0C1D2E3',
            status: 'pending',
            createdAt: new Date().toISOString()
        }
    ],
    
    referrals: [
        {
            id: 1,
            referrerId: 2,
            referredId: 3,
            level: 1,
            commission: 5.00,
            status: 'active'
        }
    ],
    
    transactions: [
        {
            id: 1,
            userId: 2,
            type: 'deposit',
            amount: 100.00,
            balanceBefore: 0,
            balanceAfter: 100.00,
            description: 'إيداع مؤكد',
            createdAt: new Date(Date.now() - 10 * 86400000).toISOString()
        },
        {
            id: 2,
            userId: 2,
            type: 'investment',
            amount: 50.00,
            balanceBefore: 100.00,
            balanceAfter: 50.00,
            description: 'استثمار في VIP 1',
            createdAt: new Date(Date.now() - 8 * 86400000).toISOString()
        }
    ],
    
    cryptoNews: [
        {
            id: 1,
            title: 'Bitcoin يتجاوز 65,000 دولار',
            content: 'شهدت Bitcoin ارتفاعاً كبيراً لتتجاوز 65,000 دولار مع تزايد الطلب المؤسسي.',
            source: 'CoinDesk',
            publishedAt: new Date().toISOString()
        },
        {
            id: 2,
            title: 'Ethereum يطلق تحديث Dencun',
            content: 'إيثريوم تطلق تحديث Dencun لتحسين قابلية التوسع وتقليل الرسوم.',
            source: 'CoinTelegraph',
            publishedAt: new Date().toISOString()
        },
        {
            id: 3,
            title: 'BNB Chain تنمو بقوة',
            content: 'شبكة BNB Chain تشهد نمواً كبيراً في نشاط DeFi.',
            source: 'BNB News',
            publishedAt: new Date().toISOString()
        }
    ],
    
    settings: {
        siteName: 'CryptoInvest',
        contactEmail: 'support@cryptoinvest.com',
        depositWalletAddress: '0x8D5D2F43b6E9eE5C8F9c0cC5E6f7D8A9B0C1D2E3',
        minDeposit: 10,
        minWithdrawal: 10,
        maxWithdrawal: 10000
    }
};

// ==========================================
// Storage Manager
// ==========================================

class StorageManager {
    constructor() {
        this.key = 'cryptoInvestData';
        this.data = this.load();
    }

    load() {
        try {
            const stored = localStorage.getItem(this.key);
            if (stored) {
                return JSON.parse(stored);
            }
        } catch (e) {}
        return JSON.parse(JSON.stringify(DEFAULT_DATA));
    }

    save() {
        localStorage.setItem(this.key, JSON.stringify(this.data));
    }

    getUsers() { return this.data.users; }
    getUser(id) { return this.data.users.find(u => u.id === id); }
    getUserByEmail(email) { return this.data.users.find(u => u.email === email); }
    getUserByReferral(code) { return this.data.users.find(u => u.referralCode === code); }

    addUser(data) {
        const user = {
            id: this.data.users.length + 1,
            balance: 0,
            investedBalance: 0,
            totalWithdraw: 0,
            totalDeposit: 0,
            totalProfit: 0,
            referralBonus: 0,
            role: 'user',
            status: 'active',
            ...data,
            createdAt: new Date().toISOString()
        };
        this.data.users.push(user);
        this.save();
        return user;
    }

    updateUser(id, updates) {
        const index = this.data.users.findIndex(u => u.id === id);
        if (index !== -1) {
            this.data.users[index] = { ...this.data.users[index], ...updates };
            this.save();
            return this.data.users[index];
        }
        return null;
    }

    getVipPlans() { return this.data.vipPlans; }
    getPlan(id) { return this.data.vipPlans.find(p => p.id === id); }

    getInvestments() { return this.data.investments; }
    getUserInvestments(userId) { return this.data.investments.filter(i => i.userId === userId); }
    getActiveInvestments(userId) { return this.data.investments.filter(i => i.userId === userId && i.status === 'active'); }

    addInvestment(data) {
        const inv = { id: this.data.investments.length + 1, ...data };
        this.data.investments.push(inv);
        this.save();
        return inv;
    }

    getDeposits() { return this.data.deposits; }
    getUserDeposits(userId) { return this.data.deposits.filter(d => d.userId === userId); }

    addDeposit(data) {
        const dep = { id: this.data.deposits.length + 1, status: 'pending', ...data, createdAt: new Date().toISOString() };
        this.data.deposits.push(dep);
        this.save();
        return dep;
    }

    updateDeposit(id, updates) {
        const index = this.data.deposits.findIndex(d => d.id === id);
        if (index !== -1) {
            this.data.deposits[index] = { ...this.data.deposits[index], ...updates };
            this.save();
            return this.data.deposits[index];
        }
        return null;
    }

    getWithdrawals() { return this.data.withdrawals; }
    getUserWithdrawals(userId) { return this.data.withdrawals.filter(w => w.userId === userId); }

    addWithdrawal(data) {
        const w = { id: this.data.withdrawals.length + 1, status: 'pending', ...data, createdAt: new Date().toISOString() };
        this.data.withdrawals.push(w);
        this.save();
        return w;
    }

    updateWithdrawal(id, updates) {
        const index = this.data.withdrawals.findIndex(w => w.id === id);
        if (index !== -1) {
            this.data.withdrawals[index] = { ...this.data.withdrawals[index], ...updates };
            this.save();
            return this.data.withdrawals[index];
        }
        return null;
    }

    getReferrals() { return this.data.referrals; }
    getUserReferrals(userId) { return this.data.referrals.filter(r => r.referrerId === userId); }

    addReferral(data) {
        const ref = { id: this.data.referrals.length + 1, ...data };
        this.data.referrals.push(ref);
        this.save();
        return ref;
    }

    getTransactions() { return this.data.transactions; }
    getUserTransactions(userId) { return this.data.transactions.filter(t => t.userId === userId); }

    addTransaction(data) {
        const t = { id: this.data.transactions.length + 1, ...data, createdAt: new Date().toISOString() };
        this.data.transactions.push(t);
        this.save();
        return t;
    }

    getNews() { return this.data.cryptoNews; }
    getSettings() { return this.data.settings; }

    reset() {
        this.data = JSON.parse(JSON.stringify(DEFAULT_DATA));
        this.save();
    }
}

const storage = new StorageManager();

// ==========================================
// Helper Functions
// ==========================================

function formatCurrency(amount) {
    return parseFloat(amount).toFixed(2) + ' USDT';
}

function formatDate(dateStr) {
    const d = new Date(dateStr);
    return d.toLocaleDateString('ar-SA') + ' ' + d.toLocaleTimeString('ar-SA');
}

function generateReferralCode() {
    return 'REF' + Math.random().toString(36).substring(2, 8).toUpperCase();
}

function getStatusBadge(status) {
    const badges = {
        'active': '🟢 نشط',
        'pending': '🟡 قيد المراجعة',
        'confirmed': '✅ مؤكد',
        'approved': '✅ موافق',
        'rejected': '❌ مرفوض',
        'completed': '✅ مكتمل'
    };
    return badges[status] || status;
}

function calculateProgress(start, end) {
    const now = new Date();
    const s = new Date(start);
    const e = new Date(end);
    if (now < s) return 0;
    if (now > e) return 100;
    return ((now - s) / (e - s)) * 100;
}