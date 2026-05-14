const blockTypes = [
    { type: 'heading', label: 'Titulos' },
    { type: 'text', label: 'Texto' },
    { type: 'image', label: 'Imagenes' },
    { type: 'video', label: 'Videos' },
    { type: 'quiz', label: 'Quizzes' },
    { type: 'flashcard', label: 'Flashcards' },
    { type: 'separator', label: 'Separadores' }
];

const baseTemplates = [
    {
        id: 'tpl-onboarding',
        nombre: 'Curso Rapido - Onboarding',
        contenido: {
            modules: [
                {
                    title: 'Modulo 1: Bienvenida',
                    lessons: [{ title: 'Leccion 1', blocks: [{ id: uid(), type: 'heading', content: 'Bienvenido al curso' }, { id: uid(), type: 'text', content: 'Describe aqui el objetivo principal.' }] }]
                }
            ]
        }
    },
    {
        id: 'tpl-masterclass',
        nombre: 'Masterclass Express',
        contenido: {
            modules: [
                {
                    title: 'Modulo 1',
                    lessons: [{ title: 'Leccion base', blocks: [{ id: uid(), type: 'video', content: 'https://www.youtube.com/embed/dQw4w9WgXcQ' }, { id: uid(), type: 'quiz', content: 'Pregunta|Opcion A|Opcion B|Opcion C' }] }]
                }
            ]
        }
    }
];

let appState = {
    students: [],
    courses: [],
    templates: [],
    editingCourseId: null,
    course: {
        title: '',
        description: '',
        template_name: '',
        modules: [createModule('Modulo 1')]
    }
};

document.addEventListener('DOMContentLoaded', async () => {
    await validateAdmin();
    buildPalette();
    bindEvents();
    await loadData();
    renderAll();
});

function uid() {
    return `${Date.now()}-${Math.random().toString(36).slice(2, 8)}`;
}

function createModule(title = 'Nuevo modulo') {
    return { title, lessons: [createLesson('Nueva leccion')] };
}

function createLesson(title = 'Nueva leccion') {
    return { title, blocks: [] };
}

function createBlock(type) {
    const defaults = {
        heading: 'Nuevo titulo',
        text: 'Nuevo texto',
        image: 'https://picsum.photos/800/450',
        video: 'https://www.youtube.com/embed/dQw4w9WgXcQ',
        quiz: 'Pregunta|Opcion A|Opcion B|Opcion C',
        flashcard: 'Pregunta|Respuesta',
        separator: ''
    };
    return { id: uid(), type, content: defaults[type] ?? '' };
}

async function validateAdmin() {
    const res = await fetch('/prisma/backend/get_user.php');
    const data = await res.json();

    if (!data.success) {
        window.location.href = '/prisma/assets/view/auth/login.html';
        return;
    }

    if (data.user.rol !== 'admin') {
        window.location.href = '/prisma/assets/view/pages/cursos.html';
        return;
    }

    const userName = document.getElementById('userName');
    userName.innerHTML = `${data.user.nombre} <i class="bi bi-person-circle"></i>`;
}

async function loadData() {
    const res = await fetch('/prisma/backend/admin_get_data.php');
    const data = await res.json();

    if (!data.success) {
        Toast.error(data.message || 'No se pudo cargar el panel admin');
        return;
    }

    appState.students = data.students;
    appState.courses = data.courses.map((course) => ({
        ...course,
        parsedContent: parseJsonSafe(course.contenido_json, { modules: [] })
    }));

    const dbTemplates = (data.templates || []).map((tpl) => ({
        id: `db-${tpl.id}`,
        nombre: tpl.nombre,
        contenido: parseJsonSafe(tpl.contenido_json, { modules: [] })
    }));

    appState.templates = [...baseTemplates, ...dbTemplates];
}

function parseJsonSafe(input, fallback) {
    try {
        return JSON.parse(input);
    } catch {
        return fallback;
    }
}

function bindEvents() {
    document.getElementById('studentForm').addEventListener('submit', onCreateStudent);
    document.getElementById('saveAccess').addEventListener('click', onSaveAccess);
    document.getElementById('accessCourse').addEventListener('change', renderAccessStudents);
    document.getElementById('addModule').addEventListener('click', () => {
        appState.course.modules.push(createModule(`Modulo ${appState.course.modules.length + 1}`));
        renderEditor();
    });
    document.getElementById('addLesson').addEventListener('click', () => {
        if (!appState.course.modules.length) {
            appState.course.modules.push(createModule('Modulo 1'));
        }
        appState.course.modules[appState.course.modules.length - 1].lessons.push(createLesson(`Leccion ${appState.course.modules[appState.course.modules.length - 1].lessons.length + 1}`));
        renderEditor();
    });
    document.getElementById('applyTemplate').addEventListener('click', onApplyTemplate);
    document.getElementById('saveCourse').addEventListener('click', onSaveCourse);
    document.getElementById('saveTemplate').addEventListener('click', onSaveTemplate);
    document.getElementById('courseTitle').addEventListener('input', (e) => {
        appState.course.title = e.target.value;
        renderPreview();
    });
    document.getElementById('courseDescription').addEventListener('input', (e) => {
        appState.course.description = e.target.value;
        renderPreview();
    });
}

function buildPalette() {
    const palette = document.getElementById('blockPalette');
    palette.innerHTML = '';
    for (const block of blockTypes) {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'palette_item';
        item.textContent = `+ ${block.label}`;
        item.addEventListener('click', () => addBlockToLastLesson(block.type));
        palette.appendChild(item);
    }
}

function addBlockToLastLesson(type) {
    if (!appState.course.modules.length) {
        appState.course.modules.push(createModule('Modulo 1'));
    }

    const lastModule = appState.course.modules[appState.course.modules.length - 1];
    if (!lastModule.lessons.length) {
        lastModule.lessons.push(createLesson('Leccion 1'));
    }

    lastModule.lessons[lastModule.lessons.length - 1].blocks.push(createBlock(type));
    renderEditor();
}

function renderAll() {
    renderCourseSelect();
    renderTemplateSelect();
    renderAccessStudents();
    renderEditor();
}

function renderCourseSelect() {
    const courseSelect = document.getElementById('accessCourse');
    courseSelect.innerHTML = '';

    appState.courses.forEach((course) => {
        const opt = document.createElement('option');
        opt.value = String(course.id);
        opt.textContent = course.titulo;
        courseSelect.appendChild(opt);
    });
}

function renderTemplateSelect() {
    const sel = document.getElementById('templateSelect');
    sel.innerHTML = '<option value="">Selecciona template</option>';
    appState.templates.forEach((tpl) => {
        const opt = document.createElement('option');
        opt.value = tpl.id;
        opt.textContent = tpl.nombre;
        sel.appendChild(opt);
    });
}

function renderAccessStudents() {
    const selectedCourseId = Number(document.getElementById('accessCourse').value || appState.courses[0]?.id || 0);
    const course = appState.courses.find((c) => c.id === selectedCourseId);

    const studentsSelect = document.getElementById('accessStudents');
    studentsSelect.innerHTML = '';

    appState.students.filter((s) => s.rol === 'estudiante').forEach((student) => {
        const opt = document.createElement('option');
        opt.value = String(student.id);
        opt.textContent = `${student.nombre} ${student.apellido} (${student.email})`;
        opt.selected = (course?.allowed_users || []).includes(student.id);
        studentsSelect.appendChild(opt);
    });
}

function renderEditor() {
    const canvas = document.getElementById('builderCanvas');
    canvas.innerHTML = '';

    appState.course.modules.forEach((module, mIndex) => {
        const moduleEl = document.createElement('div');
        moduleEl.className = 'module_card';

        moduleEl.innerHTML = `<input data-role="module-title" data-m="${mIndex}" value="${escapeHtml(module.title)}">`;

        module.lessons.forEach((lesson, lIndex) => {
            const lessonEl = document.createElement('div');
            lessonEl.className = 'lesson_card';
            lessonEl.innerHTML = `
                <input data-role="lesson-title" data-m="${mIndex}" data-l="${lIndex}" value="${escapeHtml(lesson.title)}">
                <div class="lesson_blocks" data-m="${mIndex}" data-l="${lIndex}"></div>
                <button type="button" class="admin_btn secondary" data-add-block data-m="${mIndex}" data-l="${lIndex}">+ Bloque</button>
            `;

            const blocksWrap = lessonEl.querySelector('.lesson_blocks');
            lesson.blocks.forEach((block, bIndex) => {
                const blockEl = document.createElement('div');
                blockEl.className = 'block_card';
                blockEl.draggable = true;
                blockEl.dataset.m = String(mIndex);
                blockEl.dataset.l = String(lIndex);
                blockEl.dataset.b = String(bIndex);
                blockEl.innerHTML = `
                    <strong>${block.type}</strong>
                    <textarea data-role="block-content" data-m="${mIndex}" data-l="${lIndex}" data-b="${bIndex}">${escapeHtml(block.content)}</textarea>
                    <div class="block_toolbar">
                        <button type="button" class="admin_btn secondary" data-up="${mIndex}-${lIndex}-${bIndex}">?</button>
                        <button type="button" class="admin_btn secondary" data-down="${mIndex}-${lIndex}-${bIndex}">?</button>
                        <button type="button" class="admin_btn" data-del="${mIndex}-${lIndex}-${bIndex}">Eliminar</button>
                    </div>
                `;
                attachDnd(blockEl);
                blocksWrap.appendChild(blockEl);
            });

            moduleEl.appendChild(lessonEl);
        });

        canvas.appendChild(moduleEl);
    });

    attachEditorListeners();
    renderPreview();
}

function attachDnd(blockEl) {
    blockEl.addEventListener('dragstart', (e) => {
        e.dataTransfer.setData('text/plain', JSON.stringify(blockEl.dataset));
    });

    blockEl.addEventListener('dragover', (e) => e.preventDefault());
    blockEl.addEventListener('drop', (e) => {
        e.preventDefault();
        const source = JSON.parse(e.dataTransfer.getData('text/plain'));
        const target = blockEl.dataset;
        moveBlock(source, target);
    });
}

function moveBlock(source, target) {
    const src = appState.course.modules[source.m].lessons[source.l].blocks;
    const [item] = src.splice(Number(source.b), 1);
    const dst = appState.course.modules[target.m].lessons[target.l].blocks;
    dst.splice(Number(target.b), 0, item);
    renderEditor();
}

function attachEditorListeners() {
    document.querySelectorAll('[data-role="module-title"]').forEach((input) => {
        input.addEventListener('input', (e) => {
            const m = Number(e.target.dataset.m);
            appState.course.modules[m].title = e.target.value;
            renderPreview();
        });
    });

    document.querySelectorAll('[data-role="lesson-title"]').forEach((input) => {
        input.addEventListener('input', (e) => {
            const m = Number(e.target.dataset.m);
            const l = Number(e.target.dataset.l);
            appState.course.modules[m].lessons[l].title = e.target.value;
            renderPreview();
        });
    });

    document.querySelectorAll('[data-role="block-content"]').forEach((input) => {
        input.addEventListener('input', (e) => {
            const m = Number(e.target.dataset.m);
            const l = Number(e.target.dataset.l);
            const b = Number(e.target.dataset.b);
            appState.course.modules[m].lessons[l].blocks[b].content = e.target.value;
            renderPreview();
        });
    });

    document.querySelectorAll('[data-add-block]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            const m = Number(e.target.dataset.m);
            const l = Number(e.target.dataset.l);
            appState.course.modules[m].lessons[l].blocks.push(createBlock('text'));
            renderEditor();
        });
    });

    document.querySelectorAll('[data-del]').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            const [m, l, b] = e.target.dataset.del.split('-').map(Number);
            appState.course.modules[m].lessons[l].blocks.splice(b, 1);
            renderEditor();
        });
    });

    document.querySelectorAll('[data-up]').forEach((btn) => {
        btn.addEventListener('click', (e) => reorderWithinLesson(e.target.dataset.up, -1));
    });

    document.querySelectorAll('[data-down]').forEach((btn) => {
        btn.addEventListener('click', (e) => reorderWithinLesson(e.target.dataset.down, 1));
    });
}

function reorderWithinLesson(key, dir) {
    const [m, l, b] = key.split('-').map(Number);
    const blocks = appState.course.modules[m].lessons[l].blocks;
    const target = b + dir;
    if (target < 0 || target >= blocks.length) return;
    [blocks[b], blocks[target]] = [blocks[target], blocks[b]];
    renderEditor();
}

function renderPreview() {
    const preview = document.getElementById('builderPreview');
    preview.innerHTML = `<h2>${escapeHtml(appState.course.title || 'Curso sin titulo')}</h2><p>${escapeHtml(appState.course.description || '')}</p>`;

    appState.course.modules.forEach((module) => {
        const moduleEl = document.createElement('div');
        moduleEl.className = 'preview_card';
        moduleEl.innerHTML = `<h3>${escapeHtml(module.title)}</h3>`;

        module.lessons.forEach((lesson) => {
            const lessonEl = document.createElement('div');
            lessonEl.className = 'preview_block';
            lessonEl.innerHTML = `<h4>${escapeHtml(lesson.title)}</h4>`;

            lesson.blocks.forEach((block) => {
                lessonEl.insertAdjacentHTML('beforeend', blockToPreviewHtml(block));
            });
            moduleEl.appendChild(lessonEl);
        });

        preview.appendChild(moduleEl);
    });
}

function blockToPreviewHtml(block) {
    const content = escapeHtml(block.content || '');
    switch (block.type) {
        case 'heading': return `<h5>${content}</h5>`;
        case 'text': return `<p>${content}</p>`;
        case 'image': return `<img src="${content}" alt="Imagen del curso">`;
        case 'video': return `<iframe src="${content}" frameborder="0" allowfullscreen></iframe>`;
        case 'quiz': {
            const parts = (block.content || '').split('|');
            const title = escapeHtml(parts[0] || 'Pregunta');
            const opts = parts.slice(1).map((o) => `<li>${escapeHtml(o)}</li>`).join('');
            return `<div><strong>${title}</strong><ul>${opts}</ul></div>`;
        }
        case 'flashcard': {
            const [front, back] = (block.content || '').split('|');
            return `<div><strong>${escapeHtml(front || '')}</strong><p>${escapeHtml(back || '')}</p></div>`;
        }
        case 'separator': return '<div class="preview_separator"></div>';
        default: return `<p>${content}</p>`;
    }
}

async function onCreateStudent(e) {
    e.preventDefault();
    const payload = {
        nombre: document.getElementById('studentName').value,
        apellido: document.getElementById('studentLastName').value,
        email: document.getElementById('studentEmail').value,
        password: document.getElementById('studentPassword').value
    };

    const res = await postJson('/prisma/backend/admin_add_student.php', payload);
    Toast[res.success ? 'success' : 'error'](res.message);
    if (res.success) {
        e.target.reset();
        await loadData();
        renderAll();
    }
}

async function onSaveAccess() {
    const courseId = Number(document.getElementById('accessCourse').value || 0);
    const selected = Array.from(document.getElementById('accessStudents').selectedOptions).map((opt) => Number(opt.value));
    const res = await postJson('/prisma/backend/admin_set_course_access.php', { course_id: courseId, student_ids: selected });
    Toast[res.success ? 'success' : 'error'](res.message);
    if (res.success) {
        await loadData();
        renderAccessStudents();
    }
}

async function onSaveCourse() {
    const payload = {
        course_id: appState.editingCourseId,
        title: document.getElementById('courseTitle').value.trim(),
        description: document.getElementById('courseDescription').value.trim(),
        template_name: appState.course.template_name || '',
        content: { modules: appState.course.modules }
    };

    const res = await postJson('/prisma/backend/admin_save_course.php', payload);
    Toast[res.success ? 'success' : 'error'](res.message);

    if (res.success) {
        appState.editingCourseId = res.course_id;
        await loadData();
        renderCourseSelect();
    }
}

async function onSaveTemplate() {
    const name = prompt('Nombre del template:');
    if (!name) return;

    const res = await postJson('/prisma/backend/admin_save_template.php', {
        name,
        content: { modules: appState.course.modules }
    });

    Toast[res.success ? 'success' : 'error'](res.message);
    if (res.success) {
        await loadData();
        renderTemplateSelect();
    }
}

function onApplyTemplate() {
    const templateId = document.getElementById('templateSelect').value;
    const tpl = appState.templates.find((x) => x.id === templateId);
    if (!tpl) return;

    appState.course.modules = deepClone(tpl.contenido.modules || [createModule('Modulo 1')]);
    appState.course.template_name = tpl.nombre;
    renderEditor();
}

function deepClone(obj) {
    return JSON.parse(JSON.stringify(obj));
}

async function postJson(url, payload) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    return await res.json();
}

function escapeHtml(input) {
    return String(input)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}
