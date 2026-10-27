<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Login') | Royal Retail Hub</title>
    <link rel="icon" type="image/png" href="{{ asset('admin/assets/images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('admin/assets/images/logo.png') }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Royal Retail Hub - Omnichannel Retail Platform" />

    <script>
        if (localStorage.getItem('admin-theme') === 'dark' || (!('admin-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>

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
            --color-primary: #3FA2F6;
            --color-on-primary: #ffffff;
            --color-background: #f4f3f0;
            --color-on-background: #292524;
            --color-surface: #faf9f7;
            --color-on-surface: #292524;
            --color-surface-dim: #ded9d0;
            --color-surface-bright: #ffffff;
            --color-surface-container-lowest: #ffffff;
            --color-surface-container-low: #f7f6f3;
            --color-surface-container: #eae7e1;
            --color-surface-container-high: #ded9d0;
            --color-surface-container-highest: #cfc8bc;
            --color-on-surface-variant: #78716c;
            --color-outline: #a8a29e;
            --color-outline-variant: #e5e2dc;
            --color-surface-gray: #f4f3f0;
        }
        .dark {
            --color-primary: #d97706;
            --color-on-primary: #ffffff;
            --color-background: #0c0a09;
            --color-on-background: #f5f5f4;
            --color-surface: #1c1917;
            --color-on-surface: #f5f5f4;
            --color-surface-dim: #0c0a09;
            --color-surface-bright: #292524;
            --color-surface-container-lowest: #1c1917;
            --color-surface-container-low: #24211e;
            --color-surface-container: #292524;
            --color-surface-container-high: #383431;
            --color-surface-container-highest: #44403c;
            --color-on-surface-variant: #a8a29e;
            --color-outline: #57534e;
            --color-outline-variant: #383431;
            --color-surface-gray: #0c0a09;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--color-background);
            color: var(--color-on-surface);
            transition: all 0.3s ease;
        }

        input, select, textarea {
            border-radius: 0.625rem !important;
            border: 1px solid var(--color-outline-variant) !important;
            padding: 0.75rem 1rem;
            background-color: var(--color-surface) !important;
            color: var(--color-on-surface) !important;
            transition: all 0.2s ease !important;
        }
        input:focus, select:focus, textarea:focus {
            outline: none !important;
            border-color: #3FA2F6 !important;
            box-shadow: 0 0 0 3px rgba(63, 162, 246, 0.2) !important;
        }

        .dark input,
        .dark select,
        .dark textarea {
            background-color: #292524 !important;
            border-color: #383431 !important;
            color: #f5f5f4 !important;
        }
        .dark input:focus, .dark select:focus, .dark textarea:focus {
            border-color: #fbbf24 !important;
            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.2) !important;
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
