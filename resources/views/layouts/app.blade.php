<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Dashboard') | IMG Admin</title>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet"/>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "white": "var(--color-surface-container-lowest)",
                        "sidebar-bg": "var(--color-sidebar-bg)",
                        "surface-gray": "var(--color-surface-gray)",
                        "surface-tint": "var(--color-surface-tint)",
                        "outline": "var(--color-outline)",
                        "tertiary-fixed": "var(--color-tertiary-fixed)",
                        "surface": "var(--color-surface)",
                        "primary": "var(--color-primary)",
                        "tertiary": "var(--color-tertiary)",
                        "on-error": "var(--color-on-error)",
                        "inverse-primary": "var(--color-inverse-primary)",
                        "surface-variant": "var(--color-surface-variant)",
                        "on-tertiary": "var(--color-on-tertiary)",
                        "surface-container-high": "var(--color-surface-container-high)",
                        "sidebar-text": "var(--color-sidebar-text)",
                        "outline-variant": "var(--color-outline-variant)",
                        "error-container": "var(--color-error-container)",
                        "surface-dim": "var(--color-surface-dim)",
                        "success": "var(--color-success)",
                        "primary-container": "var(--color-primary-container)",
                        "danger": "var(--color-danger)",
                        "secondary-fixed-dim": "var(--color-secondary-fixed-dim)",
                        "surface-container-highest": "var(--color-surface-container-highest)",
                        "on-secondary-container": "var(--color-on-secondary-container)",
                        "surface-container": "var(--color-surface-container)",
                        "warning": "var(--color-warning)",
                        "primary-fixed": "var(--color-primary-fixed)",
                        "background": "var(--color-background)",
                        "on-secondary": "var(--color-on-secondary)",
                        "tertiary-container": "var(--color-tertiary-container)",
                        "surface-bright": "var(--color-surface-bright)",
                        "on-primary-container": "var(--color-on-primary-container)",
                        "error": "var(--color-error)",
                        "on-primary-fixed": "var(--color-on-primary-fixed)",
                        "on-secondary-fixed-variant": "var(--color-on-secondary-fixed-variant)",
                        "on-tertiary-container": "var(--color-on-tertiary-container)",
                        "on-primary-fixed-variant": "var(--color-on-primary-fixed-variant)",
                        "on-surface": "var(--color-on-surface)",
                        "secondary": "var(--color-secondary)",
                        "inverse-on-surface": "var(--color-inverse-on-surface)",
                        "secondary-container": "var(--color-secondary-container)",
                        "on-background": "var(--color-on-background)",
                        "surface-container-lowest": "var(--color-surface-container-lowest)",
                        "on-secondary-fixed": "var(--color-on-secondary-fixed)",
                        "sidebar-active": "var(--color-sidebar-active)",
                        "inverse-surface": "var(--color-inverse-surface)",
                        "on-primary": "var(--color-on-primary)",
                        "on-tertiary-fixed": "var(--color-on-tertiary-fixed)"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    "spacing": {
                        "stack-sm": "8px",
                        "stack-md": "16px",
                        "container-gap": "24px",
                        "gutter": "24px",
                        "card-padding": "20px",
                        "sidebar-condensed": "70px",
                        "sidebar-width": "260px"
                    },
                    "fontFamily": {
                        "headline-md": ["Hanken Grotesk"],
                        "headline-lg": ["Hanken Grotesk"],
                        "label-sm": ["Hanken Grotesk"],
                        "body-md": ["Hanken Grotesk"],
                        "metric-display": ["Hanken Grotesk"],
                        "label-md": ["Hanken Grotesk"],
                        "headline-xl": ["Hanken Grotesk"],
                        "body-lg": ["Hanken Grotesk"]
                    },
                    "fontSize": {
                        "headline-md": ["16px", {"lineHeight": "24px", "fontWeight": "600"}],
                        "headline-lg": ["20px", {"lineHeight": "28px", "fontWeight": "600"}],
                        "label-sm": ["11px", {"lineHeight": "14px", "letterSpacing": "0.02em", "fontWeight": "600"}],
                        "body-md": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "metric-display": ["22px", {"lineHeight": "28px", "fontWeight": "700"}],
                        "label-md": ["12px", {"lineHeight": "16px", "fontWeight": "500"}],
                        "headline-xl": ["24px", {"lineHeight": "32px", "fontWeight": "700"}],
                        "body-lg": ["15px", {"lineHeight": "22px", "fontWeight": "400"}]
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --color-sidebar-bg: #1a1009;
            --color-surface-gray: #f5f3ef;
            --color-surface-tint: #6e5b4d;
            --color-outline: #80756e;
            --color-tertiary-fixed: #fbf9f5;
            --color-surface: #fbf9f5;
            --color-primary: #100601;
            --color-tertiary: #0f0703;
            --color-on-error: #ffffff;
            --color-inverse-primary: #dbc2b1;
            --color-surface-variant: #e4e2de;
            --color-on-tertiary: #ffffff;
            --color-surface-container-high: #eae8e4;
            --color-sidebar-text: #998374;
            --color-outline-variant: #d2c4bc;
            --color-error-container: #ffdad6;
            --color-surface-dim: #dbdad6;
            --color-success: #22c55e;
            --color-primary-container: #2b1d12;
            --color-danger: #ef4444;
            --color-secondary-fixed-dim: #e6c18c;
            --color-surface-container-highest: #e4e2de;
            --color-on-secondary-container: #795c30;
            --color-surface-container: #f5f3ef;
            --color-warning: #ff9800;
            --color-primary-fixed: #f8decc;
            --color-background: #fbf9f5;
            --color-on-secondary: #ffffff;
            --color-tertiary-container: #291e16;
            --color-surface-bright: #ffffff;
            --color-on-primary-container: #998374;
            --color-error: #ba1a1a;
            --color-on-primary-fixed: #26190e;
            --color-on-secondary-fixed-variant: #6e5b4d;
            --color-on-tertiary-container: #96847a;
            --color-on-primary-fixed-variant: #554336;
            --color-on-surface: #1b1c1a;
            --color-secondary: #76592e;
            --color-inverse-on-surface: #f2f0ed;
            --color-secondary-container: #fed7a0;
            --color-on-background: #1b1c1a;
            --color-surface-container-lowest: #ffffff;
            --color-on-secondary-fixed: #291800;
            --color-sidebar-active: #ffffff;
            --color-inverse-surface: #30312e;
            --color-on-primary: #ffffff;
            --color-on-tertiary-fixed: #ffffff;
        }
        .dark {
            --color-sidebar-bg: #121413;
            --color-surface-gray: #1b1c1a;
            --color-surface-tint: #6e5b4d;
            --color-outline: #80756e;
            --color-tertiary-fixed: #291e16;
            --color-surface: #1b1c1a;
            --color-primary: #dbc2b1;
            --color-tertiary: #96847a;
            --color-on-error: #ffffff;
            --color-inverse-primary: #f8decc;
            --color-surface-variant: #30312e;
            --color-on-tertiary: #26190e;
            --color-surface-container-high: #262725;
            --color-sidebar-text: #a8926e;
            --color-outline-variant: #3d3228;
            --color-error-container: #5c1a1a;
            --color-surface-dim: #14100c;
            --color-success: #22c55e;
            --color-primary-container: #554336;
            --color-danger: #ef4444;
            --color-secondary-fixed-dim: #3d3228;
            --color-surface-container-highest: #30312e;
            --color-on-secondary-container: #fed7a0;
            --color-surface-container: #222120;
            --color-warning: #ff9800;
            --color-primary-fixed: #554336;
            --color-background: #1b1c1a;
            --color-on-secondary: #291800;
            --color-tertiary-container: #3d3228;
            --color-surface-bright: #1e1f1d;
            --color-on-primary-container: #f8decc;
            --color-error: #ba1a1a;
            --color-on-primary-fixed: #f5f3ef;
            --color-on-secondary-fixed-variant: #a8926e;
            --color-on-tertiary-container: #e6c18c;
            --color-on-primary-fixed-variant: #dbc2b1;
            --color-on-surface: #f2f0ed;
            --color-secondary: #e6c18c;
            --color-inverse-on-surface: #1b1c1a;
            --color-secondary-container: #3d3228;
            --color-on-background: #f2f0ed;
            --color-surface-container-lowest: #1b1c1a;
            --color-on-secondary-fixed: #ffddb0;
            --color-sidebar-active: #f8decc;
            --color-inverse-surface: #f2f0ed;
            --color-on-primary: #26190e;
            --color-on-tertiary-fixed: #26190e;
        }
        .dark input,
        .dark select,
        .dark textarea {
            background-color: var(--color-surface-container) !important;
            border-color: #fff !important;
            color: #fff !important;
        }
        .dark input[type="checkbox"] {
            background-color: var(--color-surface-container);
            border-color: var(--color-outline);
        }
        .dark input[type="checkbox"]:checked {
            background-color: var(--color-secondary);
            border-color: var(--color-secondary);
        }
        .dark input[type="checkbox"]:focus {
            --tw-ring-color: var(--color-secondary);
        }
        body { font-family: 'Hanken Grotesk', sans-serif; }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        .dropdown-item,
        .dropdown-header {
            color: var(--color-on-surface);
        }
        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: var(--color-surface-container-high);
            color: var(--color-on-surface);
        }
    </style>
    @stack('styles')
</head>
<body class="bg-surface-gray text-on-surface antialiased">
    <div id="page-loader" class="fixed inset-0 z-[9999] bg-white/80 dark:bg-surface/80 backdrop-blur-sm flex items-center justify-center">
        <div class="flex flex-col items-center gap-3">
            <div class="w-10 h-10 border-4 border-primary border-t-transparent rounded-full animate-spin"></div>
            <p class="text-label-md text-on-surface-variant">Loading...</p>
        </div>
    </div>
    <!-- Sidebar -->
    @include('layouts.partials.sidebar')

    <!-- Main Content -->
    <main class="ml-sidebar-width min-h-screen flex flex-col transition-all duration-300">
        @include('layouts.partials.topbar')
        <div class="p-gutter flex-1">
            @yield('content')
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const loader = document.getElementById('page-loader');
            if (loader) loader.classList.add('hidden');

            const toggle = document.getElementById('darkModeToggle');
            const html = document.documentElement;
            const storageKey = 'admin-theme';
            const saved = localStorage.getItem(storageKey);
            if (saved === 'dark') {
                html.classList.add('dark');
            }
            if (toggle) {
                toggle.addEventListener('click', function() {
                    html.classList.toggle('dark');
                    localStorage.setItem(storageKey, html.classList.contains('dark') ? 'dark' : 'light');
                });
            }

            const profileBtn = document.getElementById('profileDropdownBtn');
            const profileMenu = document.getElementById('profileDropdownMenu');
            if (profileBtn && profileMenu) {
                profileBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    profileMenu.classList.toggle('hidden');
                });
                document.addEventListener('click', function(e) {
                    if (!profileMenu.contains(e.target) && e.target !== profileBtn) {
                        profileMenu.classList.add('hidden');
                    }
                });
            }
        });

        // Show page loader on navigating link clicks
        document.addEventListener('click', function(e) {
            const loader = document.getElementById('page-loader');
            if (!loader) return;
            const target = e.target.closest('a[href]:not([target="_blank"]):not([href^="#"]):not([href^="javascript"])');
            if (target) {
                loader.classList.remove('hidden');
            }
        });

        // Show page loader on valid form submissions
        document.addEventListener('submit', function(e) {
            const loader = document.getElementById('page-loader');
            if (loader) {
                loader.classList.remove('hidden');
            }
        });

        // Toast Notification System
        function showToast(type, message, duration = 5000) {
            const container = document.getElementById('toast-container');
            if (!container) return;

            const toast = document.createElement('div');
            toast.className = `flex items-center gap-3 p-4 rounded-xl shadow-lg border transition-all duration-500 transform translate-x-full opacity-0 pointer-events-auto bg-surface-container-lowest border-outline-variant/30`;
            
            let icon = 'info';
            let iconColor = 'text-primary';
            let borderTheme = 'border-l-4 border-l-primary';

            if (type === 'success') {
                icon = 'check_circle';
                iconColor = 'text-success';
                borderTheme = 'border-l-4 border-l-success';
            } else if (type === 'error') {
                icon = 'error';
                iconColor = 'text-danger';
                borderTheme = 'border-l-4 border-l-danger';
            } else if (type === 'warning') {
                icon = 'warning';
                iconColor = 'text-warning';
                borderTheme = 'border-l-4 border-l-warning';
            }

            toast.className += ` ${borderTheme}`;

            toast.innerHTML = `
                <span class="material-symbols-outlined ${iconColor} shrink-0">${icon}</span>
                <div class="flex-1 min-w-0">
                    <p class="font-body-md text-body-md text-on-surface font-semibold">${message}</p>
                </div>
                <button type="button" class="text-on-surface-variant hover:text-on-surface p-1 rounded-full hover:bg-surface-container transition-colors shrink-0" onclick="this.closest('.transform').remove()">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            `;

            container.appendChild(toast);

            // Trigger animation
            setTimeout(() => {
                toast.classList.remove('translate-x-full', 'opacity-0');
                toast.classList.add('translate-x-0', 'opacity-100');
            }, 10);

            // Auto remove
            if (duration > 0) {
                setTimeout(() => {
                    toast.classList.remove('translate-x-0', 'opacity-100');
                    toast.classList.add('translate-x-full', 'opacity-0');
                    setTimeout(() => {
                        toast.remove();
                    }, 500);
                }, duration);
            }
        }
    </script>
    @stack('scripts')

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 max-w-sm w-full pointer-events-none"></div>

    <!-- Render Session Flashes & Validation Errors as Toasts -->
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('success', {!! json_encode(session('success')) !!});
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('error', {!! json_encode(session('error')) !!});
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                @foreach($errors->all() as $error)
                    showToast('error', {!! json_encode($error) !!});
                @endforeach
            });
        </script>
    @endif
</body>
</html>
