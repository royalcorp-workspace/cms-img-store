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
                        lg: "8px",
                        xl: "12px",
                        full: "9999px",
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
                        "display-lg-mobile": ["Hanken Grotesk"],
                        "headline-md": ["Hanken Grotesk"],
                        "label-sm": ["Hanken Grotesk"],
                        "display-lg": ["Hanken Grotesk"],
                        "body-lg": ["Hanken Grotesk"],
                        "mono-sm": ["JetBrains Mono"],
                        "body-md": ["Hanken Grotesk"],
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
            --color-primary: #100601;
            --color-on-primary: #ffffff;
            --color-background: #fbf9f5;
            --color-on-background: #1b1c1a;
            --color-surface: #fbf9f5;
            --color-on-surface: #1b1c1a;
            --color-surface-dim: #dbdad6;
            --color-surface-bright: #fbf9f5;
            --color-surface-container-lowest: #ffffff;
            --color-surface-container-low: #f5f3ef;
            --color-surface-container: #efeeea;
            --color-surface-container-high: #eae8e4;
            --color-surface-container-highest: #e4e2de;
            --color-on-surface-variant: #4e453f;
            --color-outline: #80756e;
            --color-outline-variant: #d2c4bc;
            --color-surface-gray: #f5f3ef;
        }
        .dark {
            --color-primary: #dbc2b1;
            --color-on-primary: #26190e;
            --color-background: #1b1c1a;
            --color-on-background: #f2f0ed;
            --color-surface: #1b1c1a;
            --color-on-surface: #f2f0ed;
            --color-surface-dim: #14100c;
            --color-surface-bright: #1e1f1d;
            --color-surface-container-lowest: #1b1c1a;
            --color-surface-container-low: #222120;
            --color-surface-container: #262725;
            --color-surface-container-high: #2b2a28;
            --color-surface-container-highest: #302f2d;
            --color-on-surface-variant: #a8926e;
            --color-outline: #a8926e;
            --color-outline-variant: #3d3228;
            --color-surface-gray: #1b1c1a;
        }
        .dark input,
        .dark select,
        .dark textarea {
            background-color: var(--color-surface-container) !important;
            border-color: #fff !important;
            color: #fff !important;
        }
        body {
            font-family: 'Hanken Grotesk', sans-serif;
            background-color: var(--color-background);
        }
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            vertical-align: middle;
        }
        .brand-panel {
            background: linear-gradient(135deg, #c09d6b 0%, #a8835a 100%);
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(110, 91, 77, 0.2);
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
    </script>
    @stack('scripts')
</body>
</html>
