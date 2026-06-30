// Theme config
(function () {
    const html = document.documentElement;

    window.toggleSidebar = function() {
        if (window.innerWidth < 992) {
            document.body.classList.toggle('mobile-sidebar-open');
        } else {
            document.body.classList.toggle('sidebar-condensed');
        }
    };

    window.setScheme = function(theme) {
        html.setAttribute('data-bs-theme', theme);
        localStorage.setItem('theme', theme);
    };

    document.addEventListener('DOMContentLoaded', function() {
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme) {
            setScheme(savedTheme);
        }
    });
})();