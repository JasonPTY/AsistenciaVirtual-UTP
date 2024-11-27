// Lógica para manejar el cambio de secciones y el título
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function(event) {
        event.preventDefault(); // Evitar el comportamiento predeterminado del enlace

        // Ocultar todas las secciones
        document.querySelectorAll('.section').forEach(section => {
            section.classList.remove('active');
        });

        // Activar la sección correspondiente
        const sectionId = this.getAttribute('data-section');
        document.getElementById(sectionId).classList.add('active');

        // Cambiar el título de la sección
        document.getElementById('section-title').textContent = this.textContent.trim();

        // Cambiar la clase activa en el menú
        document.querySelectorAll('.nav-link').forEach(nav => {
            nav.classList.remove('active');
        });
        this.classList.add('active');
    });
});

// Lógica para mostrar/ocultar el sidebar
document.getElementById('toggle-sidebar').addEventListener('click', function () {
    document.getElementById('sidebar').classList.toggle('collapsed');
    document.getElementById('main-content').classList.toggle('collapsed');
});
