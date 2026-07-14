let balance = 0;

const balanceText = document.getElementById("balance");

document.getElementById("deposit").addEventListener("click", () => {
    balance += 100;
    balanceText.textContent = balance + " USDT";
    alert("تمت إضافة 100 USDT (تجريبي)");
});

document.getElementById("withdraw").addEventListener("click", () => {
    if (balance >= 100) {
        balance -= 100;
        balanceText.textContent = balance + " USDT";
        alert("تم سحب 100 USDT (تجريبي)");
    } else {
        alert("الرصيد غير كافٍ");
    }
});