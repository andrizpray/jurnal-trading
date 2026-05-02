// Bootstrap 5
import * as bootstrap from 'bootstrap';

// Alpine.js
import Alpine from 'alpinejs';

// Custom JavaScript
import './bootstrap';
import './trading-preview';

// Initialize Bootstrap components
window.bootstrap = bootstrap;

// Initialize Alpine.js
window.Alpine = Alpine;
Alpine.start();

// CSRF token setup for AJAX
const token = document.querySelector('meta[name="csrf-token"]');
if (token) {
    window.csrfToken = token.getAttribute('content');
}

// Flash message auto-dismiss
document.addEventListener('DOMContentLoaded', function() {
    const flashMessages = document.querySelectorAll('.flash-message');
    flashMessages.forEach(function(msg) {
        setTimeout(function() {
            msg.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
            msg.style.opacity = '0';
            msg.style.transform = 'translateX(100%)';
            setTimeout(() => msg.remove(), 500);
        }, 4000);
    });

    // Currency button toggle
    const currencyBtns = document.querySelectorAll('.currency-btn');
    const currencyInput = document.getElementById('currency-input');
    currencyBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            currencyBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            if (currencyInput) {
                currencyInput.value = this.value;
            }
        });
    });

    // Trader type button toggle
    const traderBtns = document.querySelectorAll('.trader-btn');
    const traderInput = document.getElementById('trader-type-input');
    traderBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            traderBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            if (traderInput) {
                traderInput.value = this.dataset.value;
            }
        });
    });

    // Number formatting for IDR inputs
    const capitalInput = document.getElementById('capital');
    if (capitalInput) {
        capitalInput.addEventListener('input', function() {
            // Remove non-digits
            let val = this.value.replace(/\D/g, '');
            this.value = val;
        });
    }
});

// Chart.js global defaults
if (typeof Chart !== 'undefined') {
    Chart.defaults.color = '#9ca3af';
    Chart.defaults.borderColor = '#374151';
    Chart.defaults.font.family = 'Inter, sans-serif';
}

// Utility functions
window.TradingUtils = {
    formatIDR: function(amount) {
        if (amount >= 1000000) {
            return 'Rp ' + (amount / 1000000).toFixed(1) + 'jt';
        }
        return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
    },

    formatUSD: function(amount) {
        return '$' + new Intl.NumberFormat('en-US', { minimumFractionDigits: 2 }).format(amount);
    },

    formatCurrency: function(amount, currency) {
        return currency === 'USD' ? this.formatUSD(amount) : this.formatIDR(amount);
    },

    showNotification: function(message, type = 'success') {
        const div = document.createElement('div');
        div.className = `flash-message fixed top-4 right-4 z-50 px-6 py-4 rounded-xl font-semibold slide-in shadow-lg ${
            type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
        }`;
        div.innerHTML = `<i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} mr-2"></i>${message}`;
        document.body.appendChild(div);
        setTimeout(() => {
            div.style.opacity = '0';
            div.style.transform = 'translateX(100%)';
            div.style.transition = 'all 0.5s ease';
            setTimeout(() => div.remove(), 500);
        }, 4000);
    },

    confirmDelete: function(message = 'Apakah Anda yakin ingin menghapus ini?') {
        return confirm(message);
    }
};
