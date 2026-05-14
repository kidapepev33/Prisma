// Manejo del formulario de registro
const registerForm = document.getElementById('registerForm');
if (registerForm) {
    registerForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(registerForm);
        
        try {
            const response = await fetch('/prisma/backend/register.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                Toast.success(data.message);
                setTimeout(() => {
                    window.location.href = '/prisma/assets/view/auth/login.html';
                }, 1500);
            } else {
                Toast.error(data.message);
            }
        } catch (error) {
            Toast.error('Error en el registro: ' + error.message);
        }
    });
}

// Manejo del formulario de login
const loginForm = document.getElementById('loginForm');
if (loginForm) {
    loginForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(loginForm);
        
        try {
            const response = await fetch('/prisma/backend/login.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await response.json();
            
            if (data.success) {
                Toast.success('¡Bienvenido ' + data.user.nombre + '!');
                setTimeout(() => {
                    window.location.href = '/prisma/assets/view/pages/cursos.html';
                }, 1500);
            } else {
                Toast.error(data.message);
            }
        } catch (error) {
            Toast.error('Error en el login: ' + error.message);
        }
    });
}