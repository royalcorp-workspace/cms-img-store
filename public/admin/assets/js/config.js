// Theme config
(function () {
    const html = document.documentElement;

    window.setScheme = function(theme, el) {
        html.setAttribute('data-bs-theme', theme);
        localStorage.setItem('theme', theme);
        document.querySelectorAll('[onclick^="setScheme"]').forEach(btn => {
            btn.classList.toggle('active', btn === el);
        });
    };

    const currentTheme = localStorage.getItem('theme') || 'light';
    setScheme(currentTheme, document.getElementById('btn' + (currentTheme.charAt(0).toUpperCase() + currentTheme.slice(1))));

    window.setSidebar = function(state, el) {
        document.body.classList.remove('sidebar-condensed', 'sidebar-hidden');
        if (state === 'condensed') {
            document.body.classList.add('sidebar-condensed');
        } else if (state === 'hidden') {
            document.body.classList.add('sidebar-hidden');
        }
        document.querySelectorAll('[onclick^="setSidebar"]').forEach(btn => {
            btn.classList.toggle('active', btn === el);
        });
    };

    window.toggleSidebar = function() {
        if (window.innerWidth < 992) {
            document.body.classList.toggle('mobile-sidebar-open');
        } else {
            document.body.classList.toggle('sidebar-condensed');
        }
    };

    window.closeMobileSidebar = function() {
        document.body.classList.remove('mobile-sidebar-open');
    };

    window.openSettings = function() {
        document.getElementById('settingsPanel').classList.add('open');
    };

    window.toggleFullscreen = function() {
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen();
            document.getElementById('fsIcon').className = 'bi bi-fullscreen-exit';
        } else {
            document.exitFullscreen();
            document.getElementById('fsIcon').className = 'bi bi-fullscreen';
        }
    };

    window.toggleSubmenu = function(el) {
        const submenu = el.nextElementSibling;
        if (submenu && submenu.classList.contains('collapse')) {
            submenu.classList.toggle('show');
            const arrow = el.querySelector('.nav-arrow');
            if (arrow) {
                arrow.style.transform = submenu.classList.contains('show') ? 'rotate(180deg)' : '';
            }
        }
    };
})();