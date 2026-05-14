document.addEventListener('click', function(e) {
    const target = e.target.closest('a');
    
    if (target && target.href && !target.target) {
        e.preventDefault();
        sessionStorage.setItem('nextPage', target.href);
        window.location.href = '/prisma/assets/view/index.html';
    }
});