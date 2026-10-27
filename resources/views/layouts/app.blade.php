<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Dashboard') | Royal Retail Hub</title>
    <link rel="icon" type="image/png" href="{{ asset('admin/assets/images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('admin/assets/images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet"/>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.min.js"></script>
    
    @vite(['resources/js/app.js'])
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
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
                        "DEFAULT": "0.5rem",
                        "sm": "0.375rem",
                        "md": "0.5rem",
                        "lg": "0.75rem",
                        "xl": "1rem",
                        "2xl": "1.5rem",
                        "3xl": "2rem",
                        "full": "9999px"
                    },
                    "boxShadow": {
                        "sm": "0 2px 8px -2px rgba(0, 0, 0, 0.02), 0 1px 3px -1px rgba(0, 0, 0, 0.02)",
                        "DEFAULT": "0 4px 16px -4px rgba(0, 0, 0, 0.04), 0 2px 4px -2px rgba(0, 0, 0, 0.02)",
                        "md": "0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 4px 12px -2px rgba(0, 0, 0, 0.03)",
                        "lg": "0 20px 40px -8px rgba(0, 0, 0, 0.07), 0 8px 16px -4px rgba(0, 0, 0, 0.03)",
                        "xl": "0 25px 50px -12px rgba(0, 0, 0, 0.1), 0 12px 24px -6px rgba(0, 0, 0, 0.05)",
                        "inner": "inset 0 2px 4px 0 rgba(0, 0, 0, 0.03)"
                    },
                    "spacing": {
                        "stack-sm": "8px",
                        "stack-md": "16px",
                        "container-gap": "24px",
                        "gutter": "24px",
                        "card-padding": "24px",
                        "sidebar-condensed": "72px",
                        "sidebar-width": "270px"
                    },
                    "fontFamily": {
                        "sans": ["Plus Jakarta Sans", "ui-sans-serif", "system-ui", "sans-serif"],
                        "headline-md": ["Plus Jakarta Sans"],
                        "headline-lg": ["Plus Jakarta Sans"],
                        "label-sm": ["Plus Jakarta Sans"],
                        "body-md": ["Plus Jakarta Sans"],
                        "metric-display": ["Plus Jakarta Sans"],
                        "label-md": ["Plus Jakarta Sans"],
                        "headline-xl": ["Plus Jakarta Sans"],
                        "body-lg": ["Plus Jakarta Sans"]
                    },
                    "fontSize": {
                        "headline-md": ["15px", {"lineHeight": "22px", "fontWeight": "600"}],
                        "headline-lg": ["18px", {"lineHeight": "26px", "fontWeight": "600"}],
                        "label-sm": ["10px", {"lineHeight": "14px", "letterSpacing": "0.02em", "fontWeight": "600"}],
                        "body-md": ["13px", {"lineHeight": "18px", "fontWeight": "400"}],
                        "metric-display": ["20px", {"lineHeight": "26px", "fontWeight": "700"}],
                        "label-md": ["11px", {"lineHeight": "16px", "fontWeight": "500"}],
                        "headline-xl": ["22px", {"lineHeight": "28px", "fontWeight": "700"}],
                        "body-lg": ["14px", {"lineHeight": "20px", "fontWeight": "400"}],
                        "xs": ["11px", {"lineHeight": "16px"}],
                        "sm": ["12px", {"lineHeight": "18px"}],
                        "base": ["14px", {"lineHeight": "20px"}]
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        :root {
            /* Theme 3 (Dual Accent): Light Mode = #3FA2F6 (Electric Azure), Dark Mode = Amber Orange */
            --color-sidebar-bg: #1c1917; /* Deep Warm Espresso Stone - Mewah, tenang, zero green */
            --color-sidebar-text: #a8a29e; /* Warm Stone 400 - Nyaman, jernih di mata */
            --color-sidebar-active: #3FA2F6; /* Vibrant Cerulean / Electric Sky Blue */

            --color-surface-gray: #f4f3f0; /* Alabaster Sand Matte Canvas - Sangat lembut, anti-pantulan silau */
            --color-surface: #faf9f7; /* Silky Pearl Off-White (Warm Stone 50) */
            --color-surface-container-lowest: #faf9f7; /* Soft Silk Card Canvas */
            --color-surface-container: #eae7e1; /* Warm Sand 150 */
            --color-surface-container-high: #ded9d0; /* Warm Sand 200 */
            --color-surface-container-highest: #cfc8bc; /* Warm Sand 300 */
            --color-surface-variant: #eae7e1;
            --color-surface-bright: #fcfbf9;
            --color-surface-dim: #ded9d0;
            --color-surface-tint: #3FA2F6;

            --color-primary: #3FA2F6; /* Electric Cerulean Blue (#3FA2F6) - Segar, modern & vibrant */
            --color-primary-container: #eff6ff; /* Soft Blue Tint */
            --color-primary-fixed: #bae6fd;
            --color-on-primary: #ffffff;
            --color-on-primary-container: #1d4ed8;
            --color-on-primary-fixed: #082f49;
            --color-on-primary-fixed-variant: #2563eb;
            --color-inverse-primary: #fbbf24;

            --color-secondary: #57534e; /* Warm Stone 600 - Selaras hangat */
            --color-secondary-container: #f5f4f0;
            --color-secondary-fixed-dim: #e7e4de;
            --color-on-secondary: #ffffff;
            --color-on-secondary-container: #292524;
            --color-on-secondary-fixed: #1c1917;
            --color-on-secondary-fixed-variant: #44403c;

            --color-tertiary: #292524;
            --color-tertiary-container: #f5f4f0;
            --color-tertiary-fixed: #faf9f7;
            --color-on-tertiary: #ffffff;
            --color-on-tertiary-container: #1c1917;
            --color-on-tertiary-fixed: #1c1917;

            --color-outline: #a8a29e; /* Warm Stone 400 */
            --color-outline-variant: #e5e2dc; /* Muted Warm Sand Divider */

            --color-on-surface: #292524; /* Deep Warm Espresso - High contrast without glare */
            --color-on-surface-variant: #78716c; /* Warm Stone 500 Subtitle */
            --color-on-background: #292524;
            --color-background: #f4f3f0;
            --color-inverse-surface: #292524;
            --color-inverse-on-surface: #f4f3f0;

            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #e11d48;
            --color-error: #e11d48;
            --color-error-container: #ffe4e6;
            --color-on-error: #ffffff;
        }
        .dark {
            /* Dark Mode: Warm Glowing Amber Orange */
            --color-sidebar-bg: #0c0a09;
            --color-sidebar-text: #a8a29e;
            --color-sidebar-active: #fbbf24;

            --color-surface-gray: #0c0a09;
            --color-surface: #1c1917;
            --color-surface-container-lowest: #1c1917;
            --color-surface-container: #292524;
            --color-surface-container-high: #383431;
            --color-surface-container-highest: #44403c;
            --color-surface-variant: #292524;
            --color-surface-bright: #292524;
            --color-surface-dim: #0c0a09;
            --color-surface-tint: #fbbf24;

            --color-primary: #fbbf24;
            --color-primary-container: #292524;
            --color-primary-fixed: #78350f;
            --color-on-primary: #1c1917;
            --color-on-primary-container: #fef3c7;
            --color-on-primary-fixed: #fef3c7;
            --color-on-primary-fixed-variant: #fbbf24;
            --color-inverse-primary: #3FA2F6;

            --color-secondary: #a8a29e;
            --color-secondary-container: #292524;
            --color-secondary-fixed-dim: #44403c;
            --color-on-secondary: #1c1917;
            --color-on-secondary-container: #f5f4f0;
            --color-on-secondary-fixed: #f5f4f0;
            --color-on-secondary-fixed-variant: #fcd34d;

            --color-tertiary: #a8a29e;
            --color-tertiary-container: #292524;
            --color-tertiary-fixed: #1c1917;
            --color-on-tertiary: #1c1917;
            --color-on-tertiary-container: #f5f4f0;
            --color-on-tertiary-fixed: #f5f4f0;

            --color-outline: #57534e;
            --color-outline-variant: #292524;

            --color-on-surface: #f5f5f4;
            --color-on-surface-variant: #a8a29e;
            --color-on-background: #f5f5f4;
            --color-background: #0c0a09;
            --color-inverse-surface: #f5f5f4;
            --color-inverse-on-surface: #0c0a09;

            --color-success: #10b981;
            --color-warning: #f59e0b;
            --color-danger: #ef4444;
            --color-error: #f87171;
            --color-error-container: #450a0a;
            --color-on-error: #ffffff;
        }

        /* Smooth visual fixes */
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--color-surface-gray);
            color: var(--color-on-surface);
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        /* Inputs, Selects & Textareas Smooth Non-Glare UX */
        input, select, textarea {
            border-radius: 0.5rem !important;
            border: 1px solid var(--color-outline-variant) !important;
            padding: 0.5rem 0.85rem;
            font-size: 13.5px !important;
            line-height: 1.5 !important;
            background-color: #fafcfb !important;
            color: var(--color-on-surface) !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
            box-shadow: 0 1px 2px 0 rgba(19, 46, 39, 0.02) !important;
        }
        input:focus, select:focus, textarea:focus {
            outline: none !important;
            border-color: var(--color-primary) !important;
            box-shadow: 0 0 0 3px rgba(15, 118, 110, 0.15) !important;
        }

        /* Dark Mode Inputs Fixes */
        .dark input,
        .dark select,
        .dark textarea {
            background-color: var(--color-surface-container) !important;
            border-color: rgba(255, 255, 255, 0.08) !important;
            color: #ffffff !important;
        }
        .dark input:focus, .dark select:focus, .dark textarea:focus {
            border-color: var(--color-primary) !important;
            box-shadow: 0 0 0 2px rgba(45, 212, 191, 0.15) !important;
        }

        /* Checkbox styling */
        input[type="checkbox"] {
            padding: 0 !important;
            width: 1.125rem !important;
            height: 1.125rem !important;
            border-radius: 0.375rem !important;
            border-color: var(--color-outline-variant) !important;
            cursor: pointer;
        }
        .dark input[type="checkbox"] {
            background-color: var(--color-surface-container) !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
        }
        .dark input[type="checkbox"]:checked {
            background-color: var(--color-primary) !important;
            border-color: var(--color-primary) !important;
        }

        /* Smooth Non-Piercing Soft-White Card Design */
        .bg-white {
            background-color: var(--color-surface-container-lowest) !important;
            border-radius: 1rem !important;
            box-shadow: 0 1px 3px 0 rgba(19, 46, 39, 0.04), 0 1px 2px -1px rgba(19, 46, 39, 0.02) !important;
            border: 1px solid var(--color-outline-variant) !important;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .bg-white:hover {
            box-shadow: 0 6px 18px -4px rgba(19, 46, 39, 0.06), 0 2px 6px -2px rgba(19, 46, 39, 0.03) !important;
        }

        /* Modern Calm Enterprise Tables (Warm Stone Neutral) */
        table thead tr {
            background-color: #eae7e1 !important;
            border-bottom: 1px solid var(--color-outline-variant) !important;
        }
        table th {
            font-size: 11.5px !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            color: #44403c !important;
            padding: 0.85rem 1.25rem !important;
        }
        table td {
            padding: 0.95rem 1.25rem !important;
            font-size: 13.5px !important;
            vertical-align: middle !important;
            border-bottom: 1px solid #e5e2dc !important;
        }
        table tbody tr {
            transition: background-color 0.15s ease-in-out;
        }
        table tbody tr:hover {
            background-color: rgba(63, 162, 246, 0.035) !important;
        }
        .dark table thead tr {
            background-color: #292524 !important;
            border-bottom-color: #383431 !important;
        }
        .dark table th {
            color: #d6d3d1 !important;
        }
        .dark table td {
            border-bottom-color: #292524 !important;
        }
        .dark table tbody tr:hover {
            background-color: rgba(217, 119, 6, 0.06) !important;
        }

        /* Modern Solid Primary Buttons: Light Mode = #3FA2F6, Dark Mode = Amber Bronze */
        a[href].bg-primary, button.bg-primary, .btn-primary {
            background-color: #3FA2F6 !important;
            background-image: none !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            font-size: 13.5px !important;
            padding: 0.55rem 1.15rem !important;
            border-radius: 0.625rem !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            box-shadow: 0 2px 6px 0 rgba(63, 162, 246, 0.3), inset 0 1px 0 0 rgba(255, 255, 255, 0.25) !important;
            transition: all 0.15s ease-in-out !important;
        }
        a[href].bg-primary:hover, button.bg-primary:hover, .btn-primary:hover {
            background-color: #258ae6 !important;
            box-shadow: 0 4px 14px 0 rgba(63, 162, 246, 0.42) !important;
            transform: translateY(-1px) !important;
        }
        a[href].bg-primary:active, button.bg-primary:active, .btn-primary:active {
            transform: translateY(0) scale(0.98) !important;
        }

        /* Dark Mode Primary Button Override (Warm Amber Bronze) */
        .dark a[href].bg-primary, .dark button.bg-primary, .dark .btn-primary {
            background-color: #d97706 !important;
            color: #ffffff !important;
            box-shadow: 0 2px 6px 0 rgba(217, 119, 6, 0.35), inset 0 1px 0 0 rgba(255, 255, 255, 0.2) !important;
        }
        .dark a[href].bg-primary:hover, .dark button.bg-primary:hover, .dark .btn-primary:hover {
            background-color: #b45309 !important;
            box-shadow: 0 4px 14px 0 rgba(217, 119, 6, 0.45) !important;
        }

        /* Secondary / Neutral Filter & Action Buttons */
        button.bg-surface-variant, a.bg-surface-variant, .btn-secondary, button.bg-gray-100, a.bg-gray-100 {
            background-color: #eae7e1 !important;
            color: #292524 !important;
            font-weight: 600 !important;
            font-size: 13px !important;
            border: 1px solid #d6d1c7 !important;
            border-radius: 0.625rem !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03) !important;
            transition: all 0.15s ease-in-out !important;
        }
        button.bg-surface-variant:hover, a.bg-surface-variant:hover, .btn-secondary:hover, button.bg-gray-100:hover, a.bg-gray-100:hover {
            background-color: #ded9d0 !important;
            color: #3FA2F6 !important;
            border-color: #a8a29e !important;
        }
        .dark button.bg-surface-variant, .dark a.bg-surface-variant, .dark .btn-secondary {
            background-color: #292524 !important;
            color: #f5f5f4 !important;
            border-color: #383431 !important;
        }
        .dark button.bg-surface-variant:hover, .dark a.bg-surface-variant:hover, .dark .btn-secondary:hover {
            background-color: #383431 !important;
            color: #fbbf24 !important;
        }

        /* Danger / Delete Action Buttons */
        button.bg-danger, a.bg-danger, .btn-danger, button.bg-red-600, a.bg-red-600 {
            background-color: #e11d48 !important;
            color: #ffffff !important;
            border-radius: 0.625rem !important;
            font-weight: 600 !important;
            border: 1px solid rgba(255, 255, 255, 0.15) !important;
            box-shadow: 0 2px 6px 0 rgba(225, 29, 72, 0.25) !important;
            transition: all 0.15s ease-in-out !important;
        }
        button.bg-danger:hover, a.bg-danger:hover, .btn-danger:hover, button.bg-red-600:hover, a.bg-red-600:hover {
            background-color: #be123c !important;
            box-shadow: 0 4px 14px 0 rgba(225, 29, 72, 0.35) !important;
            transform: translateY(-1px) !important;
        }

        /* Sidebar UI: Deep Warm Espresso Slate */
        aside#sidebar {
            background-color: var(--color-sidebar-bg) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
            box-shadow: 4px 0 24px 0 rgba(0, 0, 0, 0.22) !important;
        }
        .sidebar-link {
            margin: 3px 12px !important;
            padding: 8.5px 13px !important;
            border-radius: 10px !important;
            font-weight: 500 !important;
            font-size: 13.5px !important;
            color: var(--color-sidebar-text) !important;
            border: 1px solid transparent !important;
            transition: all 0.15s ease-in-out !important;
            background-color: transparent !important;
        }
        .sidebar-link .material-symbols-outlined {
            color: #a8a29e !important;
            transition: color 0.15s ease-in-out;
        }
        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.06) !important;
            color: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.06) !important;
        }
        .sidebar-link:hover .material-symbols-outlined {
            color: var(--color-sidebar-active) !important;
        }

        /* Sidebar Capsule Pill: Light Mode (#3FA2F6) */
        .sidebar-link.bg-primary-container,
        .sidebar-link.bg-primary-container\/50,
        .sidebar-link.active {
            background: rgba(63, 162, 246, 0.16) !important;
            color: #3FA2F6 !important;
            font-weight: 600 !important;
            border: 1px solid rgba(63, 162, 246, 0.32) !important;
            box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.12) !important;
            border-left: 1px solid rgba(63, 162, 246, 0.32) !important;
        }
        .sidebar-link.bg-primary-container .material-symbols-outlined,
        .sidebar-link.bg-primary-container\/50 .material-symbols-outlined,
        .sidebar-link.active .material-symbols-outlined {
            color: #3FA2F6 !important;
        }

        /* Sidebar Capsule Pill: Dark Mode (Warm Amber Orange) */
        .dark .sidebar-link.bg-primary-container,
        .dark .sidebar-link.bg-primary-container\/50,
        .dark .sidebar-link.active {
            background: rgba(217, 119, 6, 0.2) !important;
            color: #fbbf24 !important;
            font-weight: 600 !important;
            border: 1px solid rgba(245, 158, 11, 0.35) !important;
            box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.18) !important;
            border-left: 1px solid rgba(245, 158, 11, 0.35) !important;
        }
        .dark .sidebar-link.bg-primary-container .material-symbols-outlined,
        .dark .sidebar-link.bg-primary-container\/50 .material-symbols-outlined,
        .dark .sidebar-link.active .material-symbols-outlined {
            color: #fbbf24 !important;
        }

        .sidebar-group-header {
            margin: 3px 12px !important;
            padding: 8.5px 13px !important;
            border-radius: 10px !important;
            font-size: 13.5px !important;
            color: var(--color-sidebar-text) !important;
            transition: all 0.15s ease-in-out !important;
            background-color: transparent !important;
        }
        .sidebar-group-header.bg-white\/5 {
            background-color: transparent !important;
            color: var(--color-sidebar-active) !important;
        }
        .sidebar-group-header:hover {
            background-color: rgba(255, 255, 255, 0.06) !important;
            color: var(--color-sidebar-active) !important;
        }

        /* Sidebar Brand & Dark Mode High-Contrast Table/SKU Fixes */
        aside#sidebar .sidebar-brand-title {
            color: #ffffff !important;
        }
        .dark aside#sidebar .sidebar-brand-title {
            color: #ffffff !important;
        }
        .dark td span.font-mono,
        .dark .font-mono.bg-slate-100 {
            background-color: #292524 !important;
            color: #fde68a !important; /* High contrast soft amber text */
            border-color: #44403c !important;
        }
        .dark .bg-slate-100,
        .dark .bg-slate-50 {
            background-color: #292524 !important;
            color: #e7e5e4 !important;
            border-color: #383431 !important;
        }
        .dark .text-slate-500,
        .dark .text-slate-600,
        .dark .text-slate-700 {
            color: #d6d3d1 !important;
        }

        /* Dark Mode: Product Header Bar & Table Grouping */
        .dark .product-inventory-group > div:first-child,
        .dark .bg-slate-50\/70 {
            background-color: #1c1917 !important;
            border-color: #383431 !important;
        }
        .dark .hover\:bg-slate-100\/70:hover {
            background-color: #292524 !important;
        }
        .dark .product-inventory-group h3 {
            color: #f5f5f4 !important;
        }

        /* Material Symbols vertical alignment */
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 450, 'GRAD' 0, 'opsz' 24;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            vertical-align: middle;
        }

        /* Scrollbar styles */
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 10px; }
        ::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        ::-webkit-scrollbar-track {
            background: var(--color-surface-gray);
        }
        ::-webkit-scrollbar-thumb {
            background: var(--color-outline-variant);
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: var(--color-outline);
        }

        .dropdown-item,
        .dropdown-header {
            color: var(--color-on-surface);
        }
        .dropdown-item:hover,
        .dropdown-item:focus {
            background-color: var(--color-surface-container-high);
            color: var(--color-on-surface);
        }

        /* Fix transparent checkmark & radio issue in light mode */
        html:not(.dark) input[type="checkbox"]:checked {
            background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3cpath d='M12.207 4.793a1 1 0 010 1.414l-5 5a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L6.5 9.086l4.293-4.293a1 1 0 011.414 0z'/%3e%3c/svg%3e") !important;
            background-color: var(--color-primary) !important;
            border-color: var(--color-primary) !important;
        }
        html:not(.dark) input[type="radio"]:checked {
            background-image: url("data:image/svg+xml,%3csvg viewBox='0 0 16 16' fill='white' xmlns='http://www.w3.org/2000/svg'%3e%3ccircle cx='8' cy='8' r='3.5'/%3e%3c/svg%3e") !important;
            background-color: var(--color-primary) !important;
            border-color: var(--color-primary) !important;
        }

        /* Modern Select2 Styling matching Tailwind & Theme-Aware */
        .select2-container--default .select2-selection--single {
            height: 42px !important;
            border: 1px solid var(--color-outline-variant, #e2e8f0) !important;
            border-radius: 0.5rem !important;
            padding: 6px 12px !important;
            background-color: var(--color-surface, #ffffff) !important;
            display: flex !important;
            align-items: center !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--color-primary, #3FA2F6) !important;
            box-shadow: 0 0 0 3px rgba(63, 162, 246, 0.18) !important;
            outline: none !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--color-on-surface, #1e293b) !important;
            font-size: 0.875rem !important;
            padding-left: 0 !important;
            line-height: normal !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: var(--color-on-surface-variant, #94a3b8) !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            right: 8px !important;
            top: 0 !important;
        }

        /* Dark Mode: Select2 Single */
        .dark .select2-container--default .select2-selection--single {
            background-color: #1c1917 !important;
            border-color: #383431 !important;
            color: #f5f5f4 !important;
        }
        .dark .select2-container--default.select2-container--focus .select2-selection--single,
        .dark .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #fbbf24 !important;
            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.2) !important;
        }
        .dark .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #f5f5f4 !important;
        }
        .dark .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #a8a29e !important;
        }
        .dark .select2-container--default .select2-selection--single .select2-selection__arrow b {
            border-color: #a8a29e transparent transparent transparent !important;
        }
        .dark .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
            border-color: transparent transparent #fbbf24 transparent !important;
        }

        /* Modern Select2 Multiple Styling */
        .select2-container--default .select2-selection--multiple {
            min-height: 42px !important;
            border: 1px solid var(--color-outline-variant, #e2e8f0) !important;
            border-radius: 0.5rem !important;
            padding: 4px 34px 4px 10px !important;
            background-color: var(--color-surface, #ffffff) !important;
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: center !important;
            gap: 4px !important;
            position: relative !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple,
        .select2-container--default.select2-container--open .select2-selection--multiple {
            border-color: var(--color-primary, #3FA2F6) !important;
            box-shadow: 0 0 0 3px rgba(63, 162, 246, 0.18) !important;
            outline: none !important;
        }
        .select2-container--default .select2-selection--multiple::after {
            content: '' !important;
            position: absolute !important;
            top: 50% !important;
            right: 12px !important;
            transform: translateY(-50%) !important;
            width: 0 !important;
            height: 0 !important;
            border-style: solid !important;
            border-width: 5px 4px 0 4px !important;
            border-color: #64748b transparent transparent transparent !important;
            pointer-events: none !important;
            transition: transform 0.2s ease !important;
        }
        .select2-container--default.select2-container--open .select2-selection--multiple::after {
            transform: translateY(-50%) rotate(180deg) !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            display: flex !important;
            flex-wrap: wrap !important;
            align-items: center !important;
            gap: 5px !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #eff6ff !important;
            border: 1px solid #bfdbfe !important;
            color: #1d4ed8 !important;
            border-radius: 9999px !important;
            padding: 3px 10px !important;
            margin: 2px 0 !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
            transition: background-color 0.15s ease !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #3b82f6 !important;
            font-weight: bold !important;
            font-size: 13px !important;
            margin-right: 2px !important;
            padding: 0 !important;
            border: none !important;
            background: transparent !important;
            cursor: pointer !important;
            transition: color 0.15s ease !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #dc2626 !important;
            background: transparent !important;
        }
        .select2-container--default .select2-selection--multiple .select2-search--inline {
            display: inline-flex !important;
            align-items: center !important;
            margin: 2px 4px !important;
        }
        .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
            margin: 0 !important;
            padding: 2px 0 !important;
            font-size: 0.8125rem !important;
            font-family: inherit !important;
            color: var(--color-on-surface, #1e293b) !important;
            height: 26px !important;
            line-height: 26px !important;
        }
        .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field::placeholder {
            color: #94a3b8 !important;
            font-size: 0.8125rem !important;
        }

        /* Dark Mode: Select2 Multiple */
        .dark .select2-container--default .select2-selection--multiple {
            background-color: #1c1917 !important;
            border-color: #383431 !important;
        }
        .dark .select2-container--default.select2-container--focus .select2-selection--multiple,
        .dark .select2-container--default.select2-container--open .select2-selection--multiple {
            border-color: #fbbf24 !important;
            box-shadow: 0 0 0 3px rgba(251, 191, 36, 0.2) !important;
        }
        .dark .select2-container--default .select2-selection--multiple::after {
            border-color: #a8a29e transparent transparent transparent !important;
        }
        .dark .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
            color: #f5f5f4 !important;
        }
        .dark .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: rgba(217, 119, 6, 0.2) !important;
            border-color: rgba(245, 158, 11, 0.4) !important;
            color: #fde68a !important;
        }
        .dark .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #fbbf24 !important;
        }
        .dark .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            color: #ef4444 !important;
        }

        /* Select2 Dropdown Popup */
        .select2-dropdown {
            border: 1px solid var(--color-outline-variant, #e2e8f0) !important;
            border-radius: 0.5rem !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.05) !important;
            z-index: 9999 !important;
            overflow: hidden !important;
            background-color: #ffffff !important;
        }
        .select2-search--dropdown {
            padding: 8px !important;
            background-color: #ffffff !important;
        }
        .select2-search--dropdown .select2-search__field {
            border: 1px solid var(--color-outline-variant, #e2e8f0) !important;
            border-radius: 0.375rem !important;
            padding: 6px 10px !important;
            font-size: 0.875rem !important;
            width: 100% !important;
            box-sizing: border-box !important;
            outline: none !important;
            background-color: var(--color-surface-gray, #f8fafc) !important;
            color: var(--color-on-surface, #1e293b) !important;
        }
        .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--color-primary, #3FA2F6) !important;
            box-shadow: 0 0 0 2px rgba(63, 162, 246, 0.18) !important;
        }
        .select2-results__options {
            max-height: 260px !important;
            padding: 4px !important;
        }
        .select2-container--default .select2-results__option {
            padding: 8px 12px !important;
            border-radius: 0.375rem !important;
            margin-bottom: 2px !important;
            font-size: 0.875rem !important;
            cursor: pointer !important;
            transition: background-color 0.15s ease !important;
            color: var(--color-on-surface, #1e293b) !important;
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #eff6ff !important;
            color: #1d4ed8 !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #3FA2F6 !important;
            color: #ffffff !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] .text-primary,
        .select2-container--default .select2-results__option[aria-selected=true] .text-on-surface,
        .select2-container--default .select2-results__option[aria-selected=true] .text-on-surface-variant,
        .select2-container--default .select2-results__option[aria-selected=true] .text-secondary {
            color: #ffffff !important;
        }

        /* Dark Mode: Select2 Dropdown Popup & Results */
        .dark .select2-dropdown {
            background-color: #1c1917 !important;
            border-color: #383431 !important;
            box-shadow: 0 14px 28px rgba(0, 0, 0, 0.6) !important;
        }
        .dark .select2-search--dropdown {
            background-color: #1c1917 !important;
        }
        .dark .select2-search--dropdown .select2-search__field {
            background-color: #292524 !important;
            border-color: #383431 !important;
            color: #f5f5f4 !important;
        }
        .dark .select2-search--dropdown .select2-search__field:focus {
            border-color: #fbbf24 !important;
            box-shadow: 0 0 0 2px rgba(251, 191, 36, 0.2) !important;
        }
        .dark .select2-container--default .select2-results__option {
            background-color: transparent !important;
            color: #e7e5e4 !important;
        }
        .dark .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: rgba(217, 119, 6, 0.25) !important;
            color: #fbbf24 !important;
        }
        .dark .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: #d97706 !important;
            color: #ffffff !important;
        }
        .dark .select2-container--default .select2-results__option .text-on-surface,
        .dark .select2-container--default .select2-results__option .text-on-surface-variant,
        .dark .select2-container--default .select2-results__option .text-secondary,
        .dark .select2-container--default .select2-results__option .text-slate-500,
        .dark .select2-container--default .select2-results__option .text-slate-600 {
            color: #a8a29e !important;
        }
        .dark .select2-container--default .select2-results__option--highlighted[aria-selected] .text-on-surface,
        .dark .select2-container--default .select2-results__option--highlighted[aria-selected] .text-on-surface-variant {
            color: #fde68a !important;
        }
        .dark .select2-container--default .select2-results__option[aria-selected=true] .text-on-surface,
        .dark .select2-container--default .select2-results__option[aria-selected=true] .text-on-surface-variant {
            color: #ffffff !important;
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
            // Initialize Channel Select2 with rich autocomplete (supports both local options and remote AJAX)
            if (typeof jQuery !== 'undefined' && $.fn.select2) {
                $('.select2-channel').each(function() {
                    const $select = $(this);
                    const ajaxUrl = $select.data('ajax-url');
                    const placeholder = $select.data('placeholder') || 'Pilih atau cari Store Channel...';
                    const allowClear = $select.data('allow-clear') !== false;

                    const config = {
                        width: '100%',
                        placeholder: placeholder,
                        allowClear: allowClear,
                        matcher: function(params, data) {
                            if ($.trim(params.term) === '') {
                                return data;
                            }
                            if (!data.id) {
                                return null;
                            }
                            const term = params.term.toLowerCase();
                            const text = (data.text || '').toLowerCase();
                            const element = data.element;
                            const store = (element ? element.getAttribute('data-store') : '') || (data.store_name || '');
                            const code = (element ? element.getAttribute('data-code') : '') || (data.code || '');

                            if (text.includes(term) || store.toLowerCase().includes(term) || code.toLowerCase().includes(term)) {
                                return data;
                            }
                            return null;
                        },
                        templateResult: function(data) {
                            if (!data.id) return data.text;
                            const element = data.element;
                            const store = (element ? element.getAttribute('data-store') : '') || (data.store_name || '');
                            const code = (element ? element.getAttribute('data-code') : '') || (data.code || '');
                            const rawName = (data.name || data.text.split('(')[0]).trim();

                            return $(`
                                <div class="flex items-center gap-2.5 py-0.5">
                                    <span class="material-symbols-outlined text-primary text-[18px] shrink-0">storefront</span>
                                    <div class="min-w-0 flex-1">
                                        <div class="font-medium text-xs text-on-surface truncate">${rawName}</div>
                                        ${store ? `<div class="text-[11px] text-on-surface-variant flex items-center gap-1 mt-0.5 truncate">Toko: <span class="text-secondary font-medium">${store}</span> ${code ? `<span class="text-outline font-mono text-[10px]">(${code})</span>` : ''}</div>` : ''}
                                    </div>
                                </div>
                            `);
                        },
                        templateSelection: function(data) {
                            if (!data.id) return data.text;
                            const element = data.element;
                            const store = (element ? element.getAttribute('data-store') : '') || (data.store_name || '');
                            const rawName = (data.name || data.text.split('(')[0]).trim();
                            return store ? `${rawName} (Toko: ${store})` : rawName;
                        }
                    };

                    if (ajaxUrl) {
                        config.ajax = {
                            url: ajaxUrl,
                            dataType: 'json',
                            delay: 250,
                            data: function(params) {
                                return { q: params.term || '' };
                            },
                            processResults: function(data) {
                                return { results: data.results || [] };
                            },
                            cache: true
                        };
                        config.minimumInputLength = 0;
                    }

                    $select.select2(config).on('select2:select select2:clear', function(e) {
                        e.target.dispatchEvent(new Event('input', { bubbles: true }));
                        e.target.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                });

                // Initialize other generic Select2
                $('.select2-enable:not(.select2-channel)').select2({
                    width: '100%',
                    dropdownCssClass: 'text-sm font-sans',
                    selectionCssClass: 'text-sm font-sans'
                }).on('select2:select select2:clear', function (e) {
                    // Dispatch native event for Alpine JS compatibility
                    e.target.dispatchEvent(new Event('input', { bubbles: true }));
                    e.target.dispatchEvent(new Event('change', { bubbles: true }));
                });
            }

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
            // Ignore new tab, download, javascript, anchor, or explicit no-loader links
            if (e.target.closest('a[target="_blank"], a[download], a[data-no-loader], a[href*="/export"], a[href*="/template"], a[href*="/download"]')) {
                loader.classList.add('hidden');
                return;
            }
            const target = e.target.closest('a[href]:not([target="_blank"]):not([download]):not([data-no-loader]):not([href*="/export"]):not([href*="/template"]):not([href*="/download"]):not([href^="#"]):not([href^="javascript"])');
            if (target) {
                loader.classList.remove('hidden');
            }
        });

        // Show page loader on valid form submissions
        document.addEventListener('submit', function(e) {
            if (e.defaultPrevented) return; // Skip if form submission is prevented (e.g. AJAX)
            const loader = document.getElementById('page-loader');
            if (loader) {
                loader.classList.remove('hidden');
            }
        });

        // Hide loader when navigating back (BFCache)
        window.addEventListener('pageshow', function (event) {
            if (event.persisted) {
                const loader = document.getElementById('page-loader');
                if (loader) {
                    loader.classList.add('hidden');
                }
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

    <!-- Popup Alert Helper Functions & Handlers (Proper Royal CMS UI) -->
    <script>
        window.showSuccessPopup = function(message, title = 'Berhasil!') {
            if (typeof Swal === 'undefined') return Promise.resolve({ isConfirmed: true });
            return Swal.fire({
                icon: 'success',
                title: title,
                html: message,
                confirmButtonText: 'Selesai',
                confirmButtonColor: '#1e3a8a',
                timer: 3500,
                timerProgressBar: true,
                customClass: {
                    popup: 'rounded-2xl shadow-2xl border border-outline-variant/30 bg-surface text-on-surface p-6',
                    title: 'font-headline-sm font-bold text-on-surface',
                    htmlContainer: 'text-sm text-on-surface-variant mt-2',
                    confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs bg-primary text-white shadow-sm hover:opacity-90 transition-all'
                }
            });
        };

        window.showWarningPopup = function(message, title = 'Peringatan') {
            if (typeof Swal === 'undefined') return Promise.resolve({ isConfirmed: true });
            return Swal.fire({
                icon: 'warning',
                title: title,
                html: message,
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#d97706',
                customClass: {
                    popup: 'rounded-2xl shadow-2xl border border-outline-variant/30 bg-surface text-on-surface p-6',
                    title: 'font-headline-sm font-bold text-on-surface',
                    htmlContainer: 'text-sm text-on-surface-variant mt-2',
                    confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs bg-warning text-white shadow-sm hover:opacity-90 transition-all'
                }
            });
        };

        window.showErrorPopup = function(message, title = 'Terjadi Kesalahan!') {
            if (typeof Swal === 'undefined') return Promise.resolve({ isConfirmed: true });
            return Swal.fire({
                icon: 'error',
                title: title,
                html: message,
                confirmButtonText: 'Tutup',
                confirmButtonColor: '#dc2626',
                customClass: {
                    popup: 'rounded-2xl shadow-2xl border border-outline-variant/30 bg-surface text-on-surface p-6',
                    title: 'font-headline-sm font-bold text-on-surface',
                    htmlContainer: 'text-sm text-on-surface-variant mt-2',
                    confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs bg-danger text-white shadow-sm hover:opacity-90 transition-all'
                }
            });
        };

        window.showConfirmDialog = function(options = {}) {
            if (typeof Swal === 'undefined') {
                return Promise.resolve({ isConfirmed: true });
            }

            const defaults = {
                title: 'Konfirmasi Tindakan',
                text: 'Apakah Anda yakin ingin melanjutkan tindakan ini?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Lanjutkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#1e3a8a',
                cancelButtonColor: '#6b7280',
                reverseButtons: true,
                focusCancel: true,
                customClass: {
                    popup: 'rounded-2xl shadow-2xl border border-outline-variant/30 bg-surface text-on-surface p-6',
                    title: 'font-headline-sm font-bold text-on-surface',
                    htmlContainer: 'text-sm text-on-surface-variant mt-2',
                    confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs bg-primary text-white shadow-sm hover:opacity-90 transition-all',
                    cancelButton: 'px-4 py-2.5 rounded-xl font-bold text-xs border border-outline-variant text-on-surface hover:bg-surface-variant transition-all'
                }
            };

            const config = typeof options === 'string'
                ? Object.assign({}, defaults, { text: options })
                : Object.assign({}, defaults, options);

            return Swal.fire(config);
        };

        // Intercept native browser alert() across 1 CMS and route to styled template popup
        window.alert = function(message) {
            if (typeof window.showWarningPopup === 'function') {
                window.showWarningPopup(message, 'Pemberitahuan');
            } else if (typeof window.showToast === 'function') {
                window.showToast('warning', message);
            } else if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Pemberitahuan',
                    html: message,
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#1e3a8a'
                });
            }
        };

        // Intercept native confirm() calls for all forms across 1 CMS
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.dataset.confirmed) {
                return;
            }

            const onsubmitAttr = form.getAttribute('onsubmit');
            if (onsubmitAttr && onsubmitAttr.includes('confirm(')) {
                e.preventDefault();
                e.stopImmediatePropagation();

                const match = onsubmitAttr.match(/confirm\s*\(\s*(['"])(.*?)\1\s*\)/);
                const confirmMsg = match && match[2] ? match[2] : 'Apakah Anda yakin ingin melanjutkan tindakan ini?';

                window.showConfirmDialog({
                    title: 'Konfirmasi',
                    text: confirmMsg,
                    icon: 'warning',
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.dataset.confirmed = 'true';
                        form.removeAttribute('onsubmit');
                        form.submit();
                    }
                });
            }
        }, true);

        // Intercept native confirm() calls for buttons/links across 1 CMS
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('button[onclick*="confirm("], a[onclick*="confirm("], input[type="submit"][onclick*="confirm("]');
            if (!btn || btn.dataset.confirmed) {
                return;
            }

            const onclickAttr = btn.getAttribute('onclick');
            if (onclickAttr && onclickAttr.includes('confirm(')) {
                e.preventDefault();
                e.stopImmediatePropagation();

                const match = onclickAttr.match(/confirm\s*\(\s*(['"])(.*?)\1\s*\)/);
                const confirmMsg = match && match[2] ? match[2] : 'Apakah Anda yakin ingin melanjutkan tindakan ini?';

                window.showConfirmDialog({
                    title: 'Konfirmasi',
                    text: confirmMsg,
                    icon: 'warning',
                    confirmButtonText: 'Ya, Lanjutkan',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        btn.dataset.confirmed = 'true';
                        btn.removeAttribute('onclick');
                        btn.click();
                    }
                });
            }
        }, true);
    </script>

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-5 right-5 z-[9999] flex flex-col gap-3 max-w-sm w-full pointer-events-none"></div>

    <!-- Render Session Flashes & Validation Errors as Popups & Toasts -->
    @if(session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const msg = {!! json_encode(session('success')) !!};
                showToast('success', msg);
                showSuccessPopup(msg);
            });
        </script>
    @endif

    @if(session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const msg = {!! json_encode(session('error')) !!};
                showToast('error', msg);
                showErrorPopup(msg);
            });
        </script>
    @endif

    @if(session('warning'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const msg = {!! json_encode(session('warning')) !!};
                showToast('warning', msg);
                showWarningPopup(msg);
            });
        </script>
    @endif

    @if(session('info'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const msg = {!! json_encode(session('info')) !!};
                showToast('info', msg);
            });
        </script>
    @endif

    @if(session('status'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const msg = {!! json_encode(session('status')) !!};
                showToast('info', msg);
                showSuccessPopup(msg);
            });
        </script>
    @endif

    @if($errors->any())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const errList = {!! json_encode($errors->all()) !!};
                const errHtml = '<ul class="text-left text-xs list-disc pl-5 space-y-1">' + errList.map(e => `<li>${e}</li>`).join('') + '</ul>';
                showErrorPopup(errHtml, 'Validasi Gagal');
            });
        </script>
    @endif
</body>
</html>
