// Sistema de Toast
const Toast = {
    container: null,
    
    init() {
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.className = 'toast-container';
            document.body.appendChild(this.container);
        }
    },
    
    show(message, type = 'info', duration = 3000) {
        this.init();
        
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        
        toast.innerHTML = `
            <span class="toast-message">${message}</span>
            <button class="toast-close" onclick="Toast.close(this.parentElement)">×</button>
        `;
        
        this.container.appendChild(toast);
        
        // Auto-cerrar después del tiempo especificado
        if (duration > 0) {
            setTimeout(() => {
                this.close(toast);
            }, duration);
        }
        
        return toast;
    },
    
    close(toastElement) {
        toastElement.classList.add('removing');
        setTimeout(() => {
            toastElement.remove();
        }, 300);
    },
    
    success(message, duration = 3000) {
        return this.show(message, 'success', duration);
    },
    
    error(message, duration = 4000) {
        return this.show(message, 'error', duration);
    },
    
    info(message, duration = 3000) {
        return this.show(message, 'info', duration);
    },
    
    warning(message, duration = 3500) {
        return this.show(message, 'warning', duration);
    }
};