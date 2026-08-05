<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Authentication') | IMG Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Admin Authentication" />

    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Hanken+Grotesk:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        primary: "var(--color-primary)",
                        "on-primary": "var(--color-on-primary)",
                        background: "var(--color-background)",
                        "on-background": "var(--color-on-background)",
                        surface: "var(--color-surface)",
                        "on-surface": "var(--color-on-surface)",
                        "surface-dim": "var(--color-surface-dim)",
                        "surface-bright": "var(--color-surface-bright)",
                        "surface-container-lowest": "var(--color-surface-container-lowest)",
                        "surface-container-low": "var(--color-surface-container-low)",
                        "surface-container": "var(--color-surface-container)",
                        "surface-container-high": "var(--color-surface-container-high)",
                        "surface-container-highest": "var(--color-surface-container-highest)",
                        "on-surface-variant": "var(--color-on-surface-variant)",
                        outline: "var(--color-outline)",
                        "outline-variant": "var(--color-outline-variant)",
                        "brand-brown": "#2b1d12",
                        "brand-gold": "#c09d6b",
                        "surface-gray": "var(--color-surface-gray)",
                    },
                    borderRadius: {
                        DEFAULT: "8px",
                        lg: "12px",
                        xl: "16px",
                        full: "9999px",
                    },
                    boxShadow: {
                        sm: "0 2px 8px -2px rgba(0, 0, 0, 0.02), 0 1px 3px -1px rgba(0, 0, 0, 0.02)",
                        DEFAULT: "0 4px 16px -4px rgba(0, 0, 0, 0.04), 0 2px 4px -2px rgba(0, 0, 0, 0.02)",
                        md: "0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 4px 12px -2px rgba(0, 0, 0, 0.03)",
                        lg: "0 20px 40px -8px rgba(0, 0, 0, 0.07), 0 8px 16px -4px rgba(0, 0, 0, 0.03)",
                        xl: "0 25px 50px -12px rgba(0, 0, 0, 0.1), 0 12px 24px -6px rgba(0, 0, 0, 0.05)",
                    },
                    spacing: {
                        "stack-sm": "0.5rem",
                        "container-max": "1440px",
                        "stack-lg": "3rem",
                        "margin-mobile": "1.5rem",
                        gutter: "2rem",
                        "stack-md": "1.5rem",
                    },
                    fontFamily: {
                        sans: ["Plus Jakarta Sans", "Hanken Grotesk", "sans-serif"],
                        "display-lg-mobile": ["Plus Jakarta Sans"],
                        "headline-md": ["Plus Jakarta Sans"],
                        "label-sm": ["Plus Jakarta Sans"],
                        "display-lg": ["Plus Jakarta Sans"],
                        "body-lg": ["Plus Jakarta Sans"],
                        "mono-sm": ["JetBrains Mono"],
                        "body-md": ["Plus Jakarta Sans"],
                    },
                    fontSize: {
                        "display-lg-mobile": ["32px", { lineHeight: "40px", letterSpacing: "-0.02em", fontWeight: "700" }],
                        "headline-md": ["32px", { lineHeight: "40px", letterSpacing: "-0.01em", fontWeight: "700" }],
                        "label-sm": ["14px", { lineHeight: "20px", fontWeight: "600" }],
                        "display-lg": ["48px", { lineHeight: "56px", letterSpacing: "-0.02em", fontWeight: "700" }],
                        "body-lg": ["18px", { lineHeight: "28px", fontWeight: "400" }],
                        "mono-sm": ["13px", { lineHeight: "16px", letterSpacing: "0.05em", fontWeight: "500" }],
                        "body-md": ["16px", { lineHeight: "24px", fontWeight: "400" }],
                    },
                },
            },
        }
    </script>
    <style>
        :root {
            --color-primary: #1c0e07;
            --color-on-primary: #ffffff;
            --color-background: #fcfbfa;
            --color-on-background: #1e1e1e;
            --color-surface: #fcfbfa;
            --color-on-surface: #1e1e1e;
            --color-surface-dim: #dfdeda;
            --color-surface-bright: #ffffff;
            --color-surface-container-lowest: #ffffff;
            --color-surface-container-low: #f7f6f3;
            --color-surface-container: #f1efe9;
            --color-surface-container-high: #eeece8;
            --color-surface-container-highest: #e6e4e1;
            --color-on-surface-variant: #5c4a3d;
            --color-outline: #80756e;
            --color-outline-variant: #dccfca;
            --color-surface-gray: #f7f6f3;
        }
        .dark {
            --color-primary: #e2cbbe;
            --color-on-primary: #2c2118;
            --color-background: #151615;
            --color-on-background: #f3f0ec;
            --color-surface: #151615;
            --color-on-surface: #f3f0ec;
            --color-surface-dim: #100d0a;
            --color-surface-bright: #242523;
            --color-surface-container-lowest: #151615;
            --color-surface-container-low: #20211f;
            --color-surface-container: #2a2b29;
            --color-surface-container-high: #343532;
            --color-surface-container-highest: #3d3e3a;
            --color-on-surface-variant: #bcaaa4;
            --color-outline: #bcaaa4;
            --color-outline-variant: #493e35;
            --color-surface-gray: #151615;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--color-background);
            color: var(--color-on-surface);
            transition: all 0.3s ease;
        }

        input, select, textarea {
            border-radius: 0.5rem !important;
            border: 1px solid var(--color-outline-variant) !important;
            padding: 0.625rem 0.875rem;
            background-color: var(--color-surface-container-lowest) !important;
            color: var(--color-on-surface) !important;
            transition: all 0.2s ease !important;
        }
        input:focus, select:focus, textarea:focus {
            outline: none !important;
            border-color: var(--color-outline) !important;
            box-shadow: 0 0 0 4px rgba(128, 117, 110, 0.15) !important;
        }

        .dark input,
        .dark select,
        .dark textarea {
            background-color: var(--color-surface-container) !important;
            border-color: rgba(255, 255, 255, 0.1) !important;
            color: #fff !important;
        }
        .dark input:focus, .dark select:focus, .dark textarea:focus {
            border-color: var(--color-primary) !important;
            box-shadow: 0 0 0 4px rgba(226, 203, 190, 0.2) !important;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 450, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .brand-panel {
            background: linear-gradient(135deg, #c09d6b 0%, #a8835a 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 1.25rem;
            box-shadow: 0 20px 40px -10px rgba(0, 0, 0, 0.15);
        }
    </style>
    @stack('styles')
</head>
<body class="antialiased text-on-surface">
    @yield('content')

    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

    @if(session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('warning', {!! json_encode(session('warning')) !!});
            });
        </script>
    @endif

    @if(session('status'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('info', {!! json_encode(session('status')) !!});
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
