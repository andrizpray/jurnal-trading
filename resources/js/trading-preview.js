/**
 * Trading Plan Real-time Preview
 * Live calculation without page reload
 */

class TradingPreview {
    constructor() {
        this.form = document.getElementById('planForm');
        this.previewContainer = document.getElementById('previewContainer');
        this.previewContent = document.getElementById('previewContent');
        this.loadingElement = document.getElementById('previewLoading');
        this.errorElement = document.getElementById('previewError');
        
        if (!this.form) return;
        
        this.init();
    }
    
    init() {
        // Debounce function to prevent too many API calls
        this.debounce = this.debounce(this.calculatePreview.bind(this), 500);
        
        // Listen to form changes
        const inputs = this.form.querySelectorAll('input, select, button[type="button"]');
        inputs.forEach(input => {
            if (input.type === 'button') {
                input.addEventListener('click', () => this.handleButtonClick(input));
            } else {
                input.addEventListener('input', () => this.debounce());
                input.addEventListener('change', () => this.debounce());
            }
        });
        
        // Initial calculation
        setTimeout(() => this.calculatePreview(), 1000);
    }
    
    handleButtonClick(button) {
        if (button.classList.contains('currency-btn')) {
            setTimeout(() => this.debounce(), 100);
        } else if (button.classList.contains('trader-btn')) {
            setTimeout(() => this.debounce(), 100);
        }
    }
    
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    async calculatePreview() {
        if (!this.validateForm()) {
            this.showError('Please fill all required fields');
            return;
        }
        
        this.showLoading();
        
        try {
            const formData = this.getFormData();
            const response = await fetch('/api/preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const data = await response.json();
            
            if (!response.ok) {
                throw new Error(data.message || 'Calculation failed');
            }
            
            this.displayPreview(data.data);
            this.hideError();
        } catch (error) {
            console.error('Preview error:', error);
            this.showError(error.message);
        } finally {
            this.hideLoading();
        }
    }
    
    validateForm() {
        const required = ['capital', 'stop_loss_pips', 'take_profit_pips'];
        let isValid = true;
        
        required.forEach(field => {
            const input = this.form.querySelector(`[name="${field}"]`);
            if (!input || !input.value) {
                isValid = false;
            }
        });
        
        return isValid;
    }
    
    getFormData() {
        const data = {
            currency: document.getElementById('currency-input').value,
            capital: document.getElementById('capital').value,
            trader_type: document.getElementById('trader-type-input').value,
            currency_pair: document.getElementById('currency_pair').value,
            stop_loss_pips: document.getElementById('stop_loss_pips').value,
            take_profit_pips: document.getElementById('take_profit_pips').value,
            risk_per_trade: document.querySelector('[name="risk_per_trade"]')?.value || 2.0
        };
        
        return data;
    }
    
    displayPreview(data) {
        if (!this.previewContent) {
            this.createPreviewContainer();
        }
        
        const { preview_days, summary, risk_reward } = data;
        
        let html = `
            <div class="preview-header">
                <h3 class="text-lg font-bold text-cyan-400">
                    <i class="fas fa-bolt mr-2"></i>Live Preview
                </h3>
                <div class="text-xs text-gray-500">Updates as you type</div>
            </div>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4">
                <div class="preview-card">
                    <div class="preview-label">Final Capital</div>
                    <div class="preview-value text-green-400">${summary.final_capital_formatted || this.formatCurrency(summary.final_capital, document.getElementById('currency-input').value)}</div>
                </div>
                <div class="preview-card">
                    <div class="preview-label">Total Profit</div>
                    <div class="preview-value text-green-400">${this.formatCurrency(summary.total_profit, document.getElementById('currency-input').value)}</div>
                </div>
                <div class="preview-card">
                    <div class="preview-label">Growth</div>
                    <div class="preview-value ${summary.growth_pct >= 0 ? 'text-green-400' : 'text-red-400'}">${summary.growth_pct}%</div>
                </div>
                <div class="preview-card">
                    <div class="preview-label">R:R Ratio</div>
                    <div class="preview-value text-cyan-400">1:${risk_reward.ratio}</div>
                </div>
            </div>
            
            <div class="mt-4">
                <h4 class="text-sm font-semibold text-gray-400 mb-2">First 7 Days Preview</h4>
                <div class="overflow-x-auto">
                    <table class="preview-table">
                        <thead>
                            <tr>
                                <th>Day</th>
                                <th>Capital</th>
                                <th>Target</th>
                                <th>Risk</th>
                                <th>Lot Size</th>
                            </tr>
                        </thead>
                        <tbody>
        `;
        
        preview_days.forEach(day => {
            html += `
                <tr>
                    <td class="text-center">${day.day}</td>
                    <td class="text-right">${day.capital_formatted}</td>
                    <td class="text-right">${day.target_formatted}</td>
                    <td class="text-right">${day.risk_formatted}</td>
                    <td class="text-right">${day.lot_size.toFixed(4)}</td>
                </tr>
            `;
        });
        
        html += `
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="mt-4 text-xs text-gray-500">
                <i class="fas fa-info-circle mr-1"></i>
                Based on ${summary.daily_target}% daily target, ${summary.max_risk}% max risk
            </div>
        `;
        
        this.previewContent.innerHTML = html;
        this.previewContainer.classList.remove('hidden');
    }
    
    formatCurrency(amount, currency) {
        if (currency === 'USD') {
            return '$' + Number(amount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
        
        if (amount >= 1000000) {
            return 'Rp ' + (amount / 1000000).toFixed(1) + 'jt';
        }
        
        return 'Rp ' + Number(amount).toLocaleString('id-ID');
    }
    
    createPreviewContainer() {
        const container = document.createElement('div');
        container.id = 'previewContainer';
        container.className = 'tech-card rounded-2xl p-6 mt-6 hidden';
        
        container.innerHTML = `
            <div id="previewLoading" class="hidden text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-cyan-400"></div>
                <div class="mt-2 text-gray-400">Calculating preview...</div>
            </div>
            
            <div id="previewError" class="hidden bg-red-900/30 border border-red-700 rounded-lg p-4 mb-4">
                <div class="flex items-center">
                    <i class="fas fa-exclamation-triangle text-red-400 mr-2"></i>
                    <span id="errorMessage" class="text-red-300"></span>
                </div>
            </div>
            
            <div id="previewContent"></div>
        `;
        
        this.form.parentNode.insertBefore(container, this.form.nextSibling);
        
        this.previewContainer = container;
        this.previewContent = container.querySelector('#previewContent');
        this.loadingElement = container.querySelector('#previewLoading');
        this.errorElement = container.querySelector('#previewError');
    }
    
    showLoading() {
        if (this.loadingElement) {
            this.loadingElement.classList.remove('hidden');
        }
        if (this.previewContent) {
            this.previewContent.classList.add('hidden');
        }
    }
    
    hideLoading() {
        if (this.loadingElement) {
            this.loadingElement.classList.add('hidden');
        }
        if (this.previewContent) {
            this.previewContent.classList.remove('hidden');
        }
    }
    
    showError(message) {
        if (this.errorElement) {
            this.errorElement.classList.remove('hidden');
            const errorMessage = this.errorElement.querySelector('#errorMessage');
            if (errorMessage) {
                errorMessage.textContent = message;
            }
        }
    }
    
    hideError() {
        if (this.errorElement) {
            this.errorElement.classList.add('hidden');
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new TradingPreview();
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = TradingPreview;
}