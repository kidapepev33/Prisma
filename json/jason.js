// Auto-ocultar mensajes después de 5 segundos
document.addEventListener('DOMContentLoaded', function () {
    const messages = document.querySelectorAll('.error-message, .success-message');

    messages.forEach(function (message) {
        setTimeout(function () {
            message.style.transition = 'opacity 0.5s ease-out';
            message.style.opacity = '0';

            setTimeout(function () {
                if (message.parentNode) {
                    message.parentNode.removeChild(message);
                }
            }, 500);
        }, 5000);

        // Permitir cerrar haciendo clic
        message.addEventListener('click', function () {
            message.style.transition = 'opacity 0.5s ease-out';
            message.style.opacity = '0';
            setTimeout(function () {
                if (message.parentNode) {
                    message.parentNode.removeChild(message);
                }
            }, 500);
        });
    });
});