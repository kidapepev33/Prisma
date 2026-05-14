document.addEventListener('DOMContentLoaded', async () => {
    const container = document.getElementById('coursesContainer');
    const footer = document.getElementById('coursesFooter');

    try {
        const response = await fetch('/prisma/backend/courses_list.php');
        const data = await response.json();

        if (!data.success) {
            return;
        }

        const adminEntry = document.getElementById('adminEntry');
        if (data.role === 'admin') {
            adminEntry.style.display = 'block';
        }

        if (!data.courses.length) {
            container.innerHTML = '<p>No hay cursos asignados todavia.</p>';
            footer.textContent = data.role === 'admin'
                ? 'Crea tu primer curso desde el panel admin.'
                : 'Pidele a un admin que te asigne un curso.';
            return;
        }

        container.innerHTML = data.courses.map((course) => `
            <article class="curso">
                <div class="curso_text">
                    <h3>${escapeHtml(course.titulo)}</h3>
                    <p>${escapeHtml(course.descripcion || 'Sin descripcion')}</p>
                </div>
            </article>
        `).join('');
    } catch (error) {
        container.innerHTML = '<p>Error cargando cursos.</p>';
    }
});

function escapeHtml(input) {
    return String(input)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}
