// Cargar información del usuario en el header
document.addEventListener('DOMContentLoaded', async () => {
    const userNameElement = document.getElementById('userName');
    
    if (userNameElement) {
        try {
            const response = await fetch('/prisma/backend/get_user.php');
            const data = await response.json();
            
            if (data.success) {
                userNameElement.innerHTML = `${data.user.nombre} <i class="bi bi-person-circle"></i>`;
            } else {
                // Si no hay sesión, redirigir al login
                window.location.href = '/prisma/assets/view/auth/login.html';
            }
        } catch (error) {
            console.error('Error al cargar usuario:', error);
            userNameElement.innerHTML = 'Usuario <i class="bi bi-person-circle"></i>';
        }
    }
});