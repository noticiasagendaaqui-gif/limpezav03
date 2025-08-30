
/**
 * AI Assistant Integration for LimpaBrasil
 * Provides automatic assistance and suggestions
 */

class AIAssistant {
    constructor() {
        this.isEnabled = true;
        this.suggestions = [];
        this.activeElement = null;
        this.init();
    }

    init() {
        this.createAssistantUI();
        this.bindEvents();
        this.startAutoAnalysis();
    }

    createAssistantUI() {
        // Create floating AI assistant button
        const assistantButton = document.createElement('div');
        assistantButton.id = 'ai-assistant-btn';
        assistantButton.className = 'fixed bottom-6 right-6 bg-primary-600 text-white rounded-full p-4 shadow-lg cursor-pointer z-50 hover:bg-primary-700 transition-colors';
        assistantButton.innerHTML = `
            <i data-feather="zap" class="w-6 h-6"></i>
        `;
        document.body.appendChild(assistantButton);

        // Create assistant panel
        const assistantPanel = document.createElement('div');
        assistantPanel.id = 'ai-assistant-panel';
        assistantPanel.className = 'fixed bottom-24 right-6 bg-white rounded-lg shadow-xl border w-80 max-h-96 overflow-hidden z-50 hidden';
        assistantPanel.innerHTML = `
            <div class="bg-primary-600 text-white p-4 flex items-center justify-between">
                <h3 class="font-semibold">Assistente IA</h3>
                <button id="close-assistant" class="text-white hover:text-gray-200">
                    <i data-feather="x" class="w-5 h-5"></i>
                </button>
            </div>
            <div id="assistant-content" class="p-4 max-h-80 overflow-y-auto">
                <div class="text-center text-gray-500">
                    <i data-feather="brain" class="w-8 h-8 mx-auto mb-2 text-primary-600"></i>
                    <p class="text-sm">Assistente ativo. Analisando página...</p>
                </div>
            </div>
        `;
        document.body.appendChild(assistantPanel);

        feather.replace();
    }

    bindEvents() {
        const assistantBtn = document.getElementById('ai-assistant-btn');
        const assistantPanel = document.getElementById('ai-assistant-panel');
        const closeBtn = document.getElementById('close-assistant');

        assistantBtn.addEventListener('click', () => {
            assistantPanel.classList.toggle('hidden');
            if (!assistantPanel.classList.contains('hidden')) {
                this.updateSuggestions();
            }
        });

        closeBtn.addEventListener('click', () => {
            assistantPanel.classList.add('hidden');
        });

        // Auto-detect form interactions
        document.addEventListener('focus', (e) => {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') {
                this.activeElement = e.target;
                this.provideLiveSuggestions(e.target);
            }
        }, true);

        // Monitor form submissions
        document.addEventListener('submit', (e) => {
            this.analyzeFormSubmission(e.target);
        });
    }

    startAutoAnalysis() {
        // Analyze page on load
        setTimeout(() => {
            this.analyzePage();
        }, 1000);

        // Periodic analysis
        setInterval(() => {
            this.analyzeUserBehavior();
        }, 30000); // Every 30 seconds
    }

    analyzePage() {
        const suggestions = [];
        
        // Check for empty required fields
        const requiredFields = document.querySelectorAll('[required]');
        let emptyRequiredCount = 0;
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                emptyRequiredCount++;
            }
        });

        if (emptyRequiredCount > 0) {
            suggestions.push({
                type: 'warning',
                title: 'Campos obrigatórios',
                message: `${emptyRequiredCount} campos obrigatórios não preenchidos`,
                action: () => this.highlightRequiredFields()
            });
        }

        // Check for email validation
        const emailFields = document.querySelectorAll('input[type="email"]');
        emailFields.forEach(field => {
            if (field.value && !this.isValidEmail(field.value)) {
                suggestions.push({
                    type: 'error',
                    title: 'E-mail inválido',
                    message: 'Formato de e-mail incorreto detectado',
                    action: () => this.focusElement(field)
                });
            }
        });

        // Check for phone formatting
        const phoneFields = document.querySelectorAll('input[type="tel"]');
        phoneFields.forEach(field => {
            if (field.value && !this.isValidPhone(field.value)) {
                suggestions.push({
                    type: 'info',
                    title: 'Telefone',
                    message: 'Clique para formatar automaticamente',
                    action: () => this.formatPhoneNumber(field)
                });
            }
        });

        this.suggestions = suggestions;
    }

    provideLiveSuggestions(element) {
        const suggestions = [];

        // Smart autofill suggestions
        if (element.type === 'email' && !element.value) {
            suggestions.push({
                type: 'info',
                title: 'Sugestão de e-mail',
                message: 'Use um e-mail válido como exemplo@email.com',
                action: () => this.showEmailSuggestions(element)
            });
        }

        if (element.type === 'tel' && !element.value) {
            suggestions.push({
                type: 'info',
                title: 'Formato de telefone',
                message: 'Use o formato (11) 99999-9999',
                action: () => this.applyPhoneMask(element)
            });
        }

        if (element.name === 'cep' && !element.value) {
            suggestions.push({
                type: 'info',
                title: 'CEP',
                message: 'Digite o CEP para preenchimento automático do endereço',
                action: () => this.applyCepMask(element)
            });
        }

        // Add contextual suggestions
        this.addContextualSuggestions(element, suggestions);
        this.suggestions = suggestions;
        this.updateSuggestions();
    }

    addContextualSuggestions(element, suggestions) {
        const currentPage = window.location.pathname;

        if (currentPage.includes('agendamento')) {
            suggestions.push({
                type: 'tip',
                title: 'Dica de agendamento',
                message: 'Agende com antecedência para garantir melhor horário',
                action: null
            });
        }

        if (currentPage.includes('contato')) {
            suggestions.push({
                type: 'tip',
                title: 'Resposta rápida',
                message: 'Respondemos em até 2 horas úteis',
                action: null
            });
        }
    }

    updateSuggestions() {
        const content = document.getElementById('assistant-content');
        if (!content) return;

        if (this.suggestions.length === 0) {
            content.innerHTML = `
                <div class="text-center text-green-600">
                    <i data-feather="check-circle" class="w-8 h-8 mx-auto mb-2"></i>
                    <p class="text-sm">Tudo parece estar em ordem!</p>
                </div>
            `;
        } else {
            content.innerHTML = this.suggestions.map(suggestion => `
                <div class="mb-3 p-3 rounded-lg border-l-4 ${this.getSuggestionStyle(suggestion.type)}">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <h4 class="font-medium text-sm">${suggestion.title}</h4>
                            <p class="text-xs text-gray-600 mt-1">${suggestion.message}</p>
                        </div>
                        ${suggestion.action ? `
                            <button onclick="window.aiAssistant.executeSuggestion(${this.suggestions.indexOf(suggestion)})" 
                                    class="ml-2 text-xs bg-primary-600 text-white px-2 py-1 rounded hover:bg-primary-700">
                                Aplicar
                            </button>
                        ` : ''}
                    </div>
                </div>
            `).join('');
        }

        feather.replace();
    }

    getSuggestionStyle(type) {
        switch (type) {
            case 'error':
                return 'border-red-500 bg-red-50';
            case 'warning':
                return 'border-yellow-500 bg-yellow-50';
            case 'info':
                return 'border-blue-500 bg-blue-50';
            case 'tip':
                return 'border-green-500 bg-green-50';
            default:
                return 'border-gray-500 bg-gray-50';
        }
    }

    executeSuggestion(index) {
        const suggestion = this.suggestions[index];
        if (suggestion && suggestion.action) {
            suggestion.action();
            // Remove executed suggestion
            this.suggestions.splice(index, 1);
            this.updateSuggestions();
        }
    }

    // Utility methods
    isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    isValidPhone(phone) {
        return /^\(\d{2}\)\s\d{4,5}-\d{4}$/.test(phone);
    }

    formatPhoneNumber(field) {
        let value = field.value.replace(/\D/g, '');
        if (value.length <= 10) {
            value = value.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        } else {
            value = value.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        }
        field.value = value;
        this.showNotification('Telefone formatado automaticamente', 'success');
    }

    applyPhoneMask(field) {
        field.addEventListener('input', (e) => {
            this.formatPhoneNumber(e.target);
        });
    }

    applyCepMask(field) {
        field.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{5})(\d{3})/, '$1-$2');
            e.target.value = value;
        });

        field.addEventListener('blur', async (e) => {
            const cep = e.target.value.replace(/\D/g, '');
            if (cep.length === 8) {
                await this.autoFillAddress(cep);
            }
        });
    }

    async autoFillAddress(cep) {
        try {
            const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
            const data = await response.json();
            
            if (!data.erro) {
                const enderecoField = document.querySelector('input[name="endereco"]');
                if (enderecoField && !enderecoField.value) {
                    enderecoField.value = `${data.logradouro}, ${data.bairro}, ${data.localidade} - ${data.uf}`;
                    this.showNotification('Endereço preenchido automaticamente', 'success');
                }
            }
        } catch (error) {
            console.error('Error fetching address:', error);
        }
    }

    highlightRequiredFields() {
        const requiredFields = document.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('ring-2', 'ring-yellow-400');
                setTimeout(() => {
                    field.classList.remove('ring-2', 'ring-yellow-400');
                }, 3000);
            }
        });
    }

    focusElement(element) {
        element.focus();
        element.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    showNotification(message, type = 'info') {
        if (window.LimpaBrasil && window.LimpaBrasil.showNotification) {
            window.LimpaBrasil.showNotification(message, type);
        }
    }

    analyzeFormSubmission(form) {
        // Log form submission for analysis
        console.log('Form submitted:', form.id || form.className);
        
        // Provide success feedback
        this.showNotification('Formulário enviado! Assistente IA analisando...', 'info');
    }

    analyzeUserBehavior() {
        // Analyze user interaction patterns
        const activeElement = document.activeElement;
        if (activeElement && (activeElement.tagName === 'INPUT' || activeElement.tagName === 'TEXTAREA')) {
            // User is actively filling a form
            this.provideLiveSuggestions(activeElement);
        }
    }
}

// Initialize AI Assistant when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.aiAssistant = new AIAssistant();
});

// Export for global access
window.AIAssistant = AIAssistant;
