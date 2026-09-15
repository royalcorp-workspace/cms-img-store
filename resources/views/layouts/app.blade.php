<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>@yield('title', 'Dashboard') | IMG Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('admin/assets/images/logo.png') }}">
    <link rel="shortcut icon" type="image/png" href="{{ asset('admin/assets/images/logo.png') }}">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&amp;display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/quill@2.0.3/dist/quill.snow.css" rel="stylesheet"/>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    @vite(['resources/js/app.js'])
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
        :root {
            --color-sidebar-bg: #110c08;
            --color-surface-gray: #f7f6f3;
            --color-surface-tint: #6e5b4d;
            --color-outline: #80756e;
            --color-tertiary-fixed: #fcfbfa;
            --color-surface: #fcfbfa;
            --color-primary: #1c0e07;
            --color-tertiary: #160d07;
            --color-on-error: #ffffff;
            --color-inverse-primary: #dbc2b1;
            --color-surface-variant: #e6e4e1;
            --color-on-tertiary: #ffffff;
            --color-surface-container-high: #eeece8;
            --color-sidebar-text: #9e8e82;
            --color-outline-variant: #dccfca;
            --color-error-container: #ffdad6;
            --color-surface-dim: #dfdeda;
            --color-success: #10b981;
            --color-primary-container: #26190f;
            --color-danger: #ef4444;
            --color-secondary-fixed-dim: #e6c18c;
            --color-surface-container-highest: #e6e4e1;
            --color-on-secondary-container: #795c30;
            --color-surface-container: #f1efe9;
            --color-warning: #f59e0b;
            --color-primary-fixed: #f7ded0;
            --color-background: #fcfbfa;
            --color-on-secondary: #ffffff;
            --color-tertiary-container: #2b1f15;
            --color-surface-bright: #ffffff;
            --color-on-primary-container: #9e8e82;
            --color-error: #ba1a1a;
            --color-on-primary-fixed: #26190e;
            --color-on-secondary-fixed-variant: #6e5b4d;
            --color-on-tertiary-container: #9a8a7f;
            --color-on-primary-fixed-variant: #5c4a3d;
            --color-on-surface: #1e1e1e;
            --color-secondary: #7f6036;
            --color-inverse-on-surface: #f6f4f1;
            --color-secondary-container: #ffdfb3;
            --color-on-background: #1e1e1e;
            --color-surface-container-lowest: #ffffff;
            --color-on-secondary-fixed: #291800;
            --color-sidebar-active: #ffffff;
            --color-inverse-surface: #323330;
            --color-on-primary: #ffffff;
            --color-on-tertiary-fixed: #ffffff;
        }
        .dark {
            --color-sidebar-bg: #0f100f;
            --color-surface-gray: #151615;
            --color-surface-tint: #6e5b4d;
            --color-outline: #8d837c;
            --color-tertiary-fixed: #2c2118;
            --color-surface: #151615;
            --color-primary: #e2cbbe;
            --color-tertiary: #a4938a;
            --color-on-error: #ffffff;
            --color-inverse-primary: #f7ded0;
            --color-surface-variant: #343532;
            --color-on-tertiary: #2c2118;
            --color-surface-container-high: #2a2b29;
            --color-sidebar-text: #bcaaa4;
            --color-outline-variant: #493e35;
            --color-error-container: #5c1a1a;
            --color-surface-dim: #100d0a;
            --color-success: #10b981;
            --color-primary-container: #5c4a3d;
            --color-danger: #ef4444;
            --color-secondary-fixed-dim: #493e35;
            --color-surface-container-highest: #343532;
            --color-on-secondary-container: #ffdfb3;
            --color-surface-container: #20211f;
            --color-warning: #f59e0b;
            --color-primary-fixed: #5c4a3d;
            --color-background: #151615;
            --color-on-secondary: #291800;
            --color-tertiary-container: #493e35;
            --color-surface-bright: #242523;
            --color-on-primary-container: #f7ded0;
            --color-error: #ffb4ab;
            --color-on-primary-fixed: #f7f6f3;
            --color-on-secondary-fixed-variant: #bcaaa4;
            --color-on-tertiary-container: #e7cfc4;
            --color-on-primary-fixed-variant: #e2cbbe;
            --color-on-surface: #f3f0ec;
            --color-secondary: #e7cfc4;
            --color-inverse-on-surface: #151615;
            --color-secondary-container: #493e35;
            --color-on-background: #f3f0ec;
            --color-surface-container-lowest: #151615;
            --color-on-secondary-fixed: #ffe0b2;
            --color-sidebar-active: #f7ded0;
            --color-inverse-surface: #f3f0ec;
            --color-on-primary: #2c2118;
            --color-on-tertiary-fixed: #2c2118;
        }

        /* Smooth visual fixes */
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--color-surface-gray);
            color: var(--color-on-surface);
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        /* Inputs, Selects & Textareas Premium Reback UX */
        input, select, textarea {
            border-radius: 0.375rem !important;
            border: 1px solid var(--color-outline-variant) !important;
            padding: 0.5rem 0.75rem;
            font-size: 13.5px !important;
            line-height: 1.5 !important;
            background-color: var(--color-surface-container-lowest) !important;
            color: var(--color-on-surface) !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
            box-shadow: none !important;
        }
        input:focus, select:focus, textarea:focus {
            outline: none !important;
            border-color: var(--color-primary) !important;
            box-shadow: 0 0 0 2px rgba(128, 117, 110, 0.08) !important;
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
            box-shadow: 0 0 0 2px rgba(226, 203, 190, 0.1) !important;
        }

        /* Checkbox styling */
        input[type="checkbox"] {
            padding: 0 !important;
            width: 1rem !important;
            height: 1rem !important;
            border-radius: 0.25rem !important;
            cursor: pointer;
        }
        .dark input[type="checkbox"] {
            background-color: var(--color-surface-container) !important;
            border-color: rgba(255, 255, 255, 0.15) !important;
        }
        .dark input[type="checkbox"]:checked {
            background-color: var(--color-secondary) !important;
            border-color: var(--color-secondary) !important;
        }

        /* Reback Card Design */
        .bg-white {
            background-color: var(--color-surface-container-lowest) !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px -1px rgba(0, 0, 0, 0.05) !important;
            border: 1px solid rgba(128, 117, 110, 0.12) !important;
            transition: all 0.25s ease-in-out;
        }
        .bg-white:hover {
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03) !important;
        }

        /* Reback Tables styling */
        table thead tr {
            background-color: var(--color-surface-gray) !important;
            border-bottom: 1px solid rgba(128, 117, 110, 0.12) !important;
        }
        table th {
            font-size: 11px !important;
            font-weight: 600 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.05em !important;
            color: var(--color-on-surface-variant) !important;
            padding: 0.75rem 1.5rem !important;
        }
        table td {
            padding: 0.875rem 1.5rem !important;
            font-size: 13.5px !important;
            vertical-align: middle !important;
            border-bottom: 1px solid rgba(128, 117, 110, 0.08) !important;
        }
        table tbody tr {
            transition: background-color 0.15s ease-in-out;
        }
        table tbody tr:hover {
            background-color: rgba(128, 117, 110, 0.02) !important;
        }

        /* Button Enhancements */
        a[href].bg-primary, button.bg-primary {
            background-color: var(--color-primary) !important;
            color: var(--color-on-primary) !important;
            font-weight: 500 !important;
            font-size: 13.5px !important;
            padding: 0.5rem 1rem !important;
            border-radius: 0.375rem !important;
            transition: all 0.2s ease-in-out !important;
            box-shadow: none !important;
            border: 1px solid transparent !important;
        }
        a[href].bg-primary:hover, button.bg-primary:hover {
            opacity: 0.9 !important;
            transform: none !important;
        }

        /* Sidebar UI Reback Polishing */
        aside#sidebar {
            background-color: var(--color-sidebar-bg) !important;
            border-right: 1px solid rgba(255, 255, 255, 0.05) !important;
            box-shadow: none !important;
        }
        .sidebar-link {
            margin: 2px 14px !important;
            padding: 8px 12px !important;
            border-radius: 6px !important;
            font-weight: 500 !important;
            font-size: 13.5px !important;
            color: var(--color-sidebar-text) !important;
            transition: all 0.15s ease-in-out !important;
            background-color: transparent !important;
        }
        .sidebar-link.bg-primary-container,
        .sidebar-link.bg-primary-container\/50 {
            background-color: rgba(255, 255, 255, 0.07) !important;
            color: var(--color-sidebar-active) !important;
            font-weight: 600 !important;
        }
        .sidebar-link:hover {
            background-color: rgba(255, 255, 255, 0.04) !important;
            color: var(--color-sidebar-active) !important;
        }
        .sidebar-group-header {
            margin: 2px 14px !important;
            padding: 8px 12px !important;
            border-radius: 6px !important;
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
            background-color: rgba(255, 255, 255, 0.04) !important;
            color: var(--color-sidebar-active) !important;
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

        /* Modern Select2 Styling matching Tailwind */
        .select2-container--default .select2-selection--single {
            height: 42px !important;
            border: 1px solid var(--color-outline-variant, #e2e8f0) !important;
            border-radius: 0.5rem !important;
            padding: 6px 12px !important;
            background-color: #ffffff !important;
            display: flex !important;
            align-items: center !important;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out !important;
        }
        .select2-container--default.select2-container--focus .select2-selection--single,
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: var(--color-primary, #2563eb) !important;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
            outline: none !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: var(--color-on-surface, #1e293b) !important;
            font-size: 0.875rem !important;
            padding-left: 0 !important;
            line-height: normal !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 100% !important;
            right: 8px !important;
            top: 0 !important;
        }
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
        }
        .select2-search--dropdown .select2-search__field {
            border: 1px solid var(--color-outline-variant, #e2e8f0) !important;
            border-radius: 0.375rem !important;
            padding: 6px 10px !important;
            font-size: 0.875rem !important;
            width: 100% !important;
            box-sizing: border-box !important;
            outline: none !important;
        }
        .select2-search--dropdown .select2-search__field:focus {
            border-color: var(--color-primary, #2563eb) !important;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.15) !important;
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
        }
        .select2-container--default .select2-results__option--highlighted[aria-selected] {
            background-color: #eff6ff !important;
            color: #1d4ed8 !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] {
            background-color: var(--color-primary, #2563eb) !important;
            color: #ffffff !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] .text-primary {
            color: #ffffff !important;
        }
        .select2-container--default .select2-results__option[aria-selected=true] .text-on-surface,
        .select2-container--default .select2-results__option[aria-selected=true] .text-on-surface-variant,
        .select2-container--default .select2-results__option[aria-selected=true] .text-secondary {
            color: rgba(255, 255, 255, 0.9) !important;
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
            const target = e.target.closest('a[href]:not([target="_blank"]):not([href^="#"]):not([href^="javascript"])');
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

    @if(session('info'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                showToast('info', {!! json_encode(session('info')) !!});
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
