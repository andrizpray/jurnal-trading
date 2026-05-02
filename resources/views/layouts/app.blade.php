<!DOCTYPE html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard') | Trading Journal</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    {{-- PWA Meta Tags --}}
    <meta name="theme-color" content="#0f172a" id="meta-theme-color">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Trading Journal">
    <link rel="manifest" href="/manifest.json">
    <link rel="apple-touch-icon" href="/images/icons/icon-192x192.png">
    
    {{-- Theme System: run before paint to prevent FOUC --}}
    <script>
        (function() {
            var saved = localStorage.getItem('tj-theme') || 'dark';
            document.documentElement.setAttribute('data-theme', saved);
        })();
    </script>

    {{-- Theme CSS Variables --}}
    <style>
        /* ===== DARK THEME (default) ===== */
        :root, [data-theme="dark"] {
            --bg-base:      #030712;
            --bg-page:      #0f172a;
            --bg-900:       #111827;
            --bg-800:       #1f2937;
            --bg-700:       #374151;
            --bg-card:      linear-gradient(135deg, rgba(17,24,39,0.95) 0%, rgba(9,14,28,0.95) 100%);
            --border:       rgba(55,65,81,0.5);
            --border-hover: rgba(75,85,99,0.7);
            --text-primary: #f9fafb;
            --text-muted:   #9ca3af;
            --text-faint:   #6b7280;
            --text-placeholder: #4b5563;
            --accent:       #06b6d4;
            --accent-light: #22d3ee;
            --accent-ring:  rgba(6,182,212,0.3);
            --nav-bg:       rgba(17,24,39,0.8);
            --nav-sub-bg:   rgba(17,24,39,0.5);
            --nav-link-hover-bg: #1f2937;
            --nav-active-bg:    rgba(6,182,212,0.1);
            --nav-active-border: rgba(6,182,212,0.3);
            --input-bg:     #1f2937;
            --input-border: #374151;
            --scrollbar-track: #0f172a;
            --scrollbar-thumb: #374151;
            --scrollbar-hover: #4b5563;
            --table-hover:  rgba(31,41,55,0.3);
            --progress-bg:  #1f2937;
            --day-pending-bg: #1f2937;
            --day-pending-text: #6b7280;
        }

        /* ===== LIGHT THEME (putih + biru) ===== */
        [data-theme="light"] {
            --bg-base:      #e8f0fe;
            --bg-page:      #ffffff;
            --bg-900:       #eff6ff;
            --bg-800:       #dbeafe;
            --bg-700:       #bfdbfe;
            --bg-card:      linear-gradient(145deg, #ffffff 0%, #f0f7ff 55%, #e8f0fe 100%);
            --border:       rgba(147, 197, 253, 0.65);
            --border-hover: rgba(96, 165, 250, 0.9);
            --text-primary: #0f172a;
            --text-muted:   #475569;
            --text-faint:   #64748b;
            --text-placeholder: #94a3b8;
            --accent:       #2563eb;
            --accent-light: #3b82f6;
            --accent-ring:  rgba(37, 99, 235, 0.22);
            --nav-bg:       rgba(255, 255, 255, 0.94);
            --nav-sub-bg:   rgba(239, 246, 255, 0.95);
            --nav-link-hover-bg: #eff6ff;
            --nav-active-bg:    rgba(37, 99, 235, 0.1);
            --nav-active-border: rgba(37, 99, 235, 0.28);
            --input-bg:     #ffffff;
            --input-border: #93c5fd;
            --scrollbar-track: #eff6ff;
            --scrollbar-thumb: #93c5fd;
            --scrollbar-hover: #60a5fa;
            --table-hover:  rgba(239, 246, 255, 0.95);
            --progress-bg:  #dbeafe;
            --day-pending-bg: #eff6ff;
            --day-pending-text: #64748b;
        }

        .tj-logo-mark {
            background: linear-gradient(135deg, #06b6d4, #22c55e);
        }
        [data-theme="light"] .tj-logo-mark {
            background: linear-gradient(135deg, #1d4ed8, #3b82f6);
        }

        .tj-nav-scroll {
            -webkit-overflow-scrolling: touch;
            scroll-snap-type: x proximity;
        }
        .tj-nav-scroll .nav-link {
            scroll-snap-align: start;
        }

        /* ===== APPLY CSS VARIABLES TO COMPONENTS ===== */
        body {
            background-color: var(--bg-page) !important;
            color: var(--text-primary) !important;
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Header & Nav */
        header { background: var(--nav-bg) !important; border-color: var(--border) !important; }
        nav.tj-nav { background: var(--nav-sub-bg) !important; border-color: var(--border) !important; }

        /* Cards */
        .tech-card, .stat-card {
            background: var(--bg-card) !important;
            border-color: var(--border) !important;
        }
        .tech-card:hover { border-color: var(--border-hover) !important; }

        /* Buttons */
        .btn-secondary { background: var(--bg-700) !important; color: var(--text-primary) !important; }
        .btn-secondary:hover { background: var(--bg-800) !important; filter: brightness(0.95); }
        [data-theme="light"] .btn-secondary { background: var(--bg-800) !important; color: var(--text-primary) !important; }
        [data-theme="light"] .btn-secondary:hover { background: var(--bg-700) !important; }

        /* Forms */
        .form-input, .form-select {
            background: var(--input-bg) !important;
            border-color: var(--input-border) !important;
            color: var(--text-primary) !important;
        }
        .form-input::placeholder { color: var(--text-placeholder) !important; }
        .form-input:focus, .form-select:focus {
            border-color: var(--accent) !important;
            box-shadow: 0 0 0 2px var(--accent-ring) !important;
        }
        .form-label { color: var(--text-muted) !important; }

        /* Currency & Trader Buttons */
        .currency-btn, .trader-btn {
            border-color: var(--input-border) !important;
            color: var(--text-muted) !important;
        }
        .currency-btn:hover, .trader-btn:hover { border-color: var(--text-faint) !important; }
        .currency-btn.active, .trader-btn.active {
            border-color: var(--accent) !important;
            background: var(--nav-active-bg) !important;
            color: var(--accent-light) !important;
        }

        /* Nav Links */
        .nav-link { color: var(--text-muted) !important; }
        .nav-link:hover { color: var(--text-primary) !important; background: var(--nav-link-hover-bg) !important; }
        .nav-link.active {
            color: var(--accent-light) !important;
            background: var(--nav-active-bg) !important;
            border-color: var(--nav-active-border) !important;
        }

        /* Tables */
        .data-table thead tr { border-color: var(--bg-800) !important; }
        .data-table thead th { color: var(--text-faint) !important; }
        .data-table tbody tr { border-color: var(--border) !important; }
        .data-table tbody tr:hover { background: var(--table-hover) !important; }
        .data-table tfoot td { border-color: var(--bg-700) !important; }

        /* Progress */
        .progress-bar { background: var(--progress-bg) !important; }

        /* Day badges */
        .day-badge.pending { background: var(--day-pending-bg) !important; color: var(--day-pending-text) !important; }

        /* Scrollbar */
        ::-webkit-scrollbar-track { background: var(--scrollbar-track) !important; }
        ::-webkit-scrollbar-thumb { background: var(--scrollbar-thumb) !important; }
        ::-webkit-scrollbar-thumb:hover { background: var(--scrollbar-hover) !important; }

        /* Text colors using CSS vars */
        [data-theme="light"] .text-gray-100 { color: var(--text-primary) !important; }
        [data-theme="light"] .text-gray-200 { color: #1e293b !important; }
        [data-theme="light"] .text-gray-300 { color: #334155 !important; }
        [data-theme="light"] .text-gray-400 { color: var(--text-muted) !important; }
        [data-theme="light"] .text-gray-500 { color: var(--text-faint) !important; }
        [data-theme="light"] .text-white { color: var(--text-primary) !important; }
        [data-theme="light"] .bg-gray-950 { background-color: var(--bg-base) !important; }
        [data-theme="light"] .bg-gray-900 { background-color: var(--bg-900) !important; }
        [data-theme="light"] .bg-gray-800 { background-color: var(--bg-800) !important; }
        [data-theme="light"] .bg-gray-700 { background-color: var(--bg-700) !important; }
        [data-theme="light"] .border-gray-800 { border-color: var(--border) !important; }
        [data-theme="light"] .border-gray-700 { border-color: var(--input-border) !important; }
        [data-theme="light"] .divide-gray-800 > * { border-color: var(--border) !important; }

        /* Inline styles that use hardcoded dark bg */
        [data-theme="light"] [class*="bg-gray-900/80"] { background: var(--nav-bg) !important; }
        [data-theme="light"] [class*="bg-gray-900/50"] { background: var(--nav-sub-bg) !important; }

        /* Theme toggle button */
        .theme-toggle-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 2.25rem;
            height: 2.25rem;
            border-radius: 0.625rem;
            border: 1px solid var(--border);
            background: var(--bg-800);
            color: var(--text-muted);
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }
        .theme-toggle-btn:hover {
            color: var(--accent-light);
            border-color: var(--accent);
            background: var(--nav-active-bg);
        }

        /* Theme selector dropdown */
        .theme-dropdown {
            position: absolute;
            top: calc(100% + 0.5rem);
            right: 0;
            background: var(--bg-900);
            border: 1px solid var(--border);
            border-radius: 0.875rem;
            padding: 0.375rem;
            min-width: 9rem;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            z-index: 999;
            display: none;
        }
        .theme-dropdown.open { display: block; animation: fadeInDown 0.2s ease; }
        [data-theme="light"] .theme-dropdown {
            box-shadow: 0 12px 40px rgba(37, 99, 235, 0.12);
        }
        @keyframes fadeInDown { from { opacity:0; transform:translateY(-6px); } to { opacity:1; transform:translateY(0); } }
        .theme-option {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.625rem;
            cursor: pointer;
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--text-muted);
            transition: all 0.15s;
        }
        .theme-option:hover { background: var(--bg-800); color: var(--text-primary); }
        .theme-option.selected { color: var(--accent-light); background: var(--nav-active-bg); }
        .theme-swatch {
            width: 1.125rem;
            height: 1.125rem;
            border-radius: 50%;
            border: 1.5px solid var(--border);
            flex-shrink: 0;
        }
        .swatch-dark  { background: linear-gradient(135deg, #0f172a 50%, #1f2937 50%); }
        .swatch-light { background: linear-gradient(135deg, #ffffff 50%, #3b82f6 50%); border-color: #93c5fd !important; }
        .swatch-ocean { background: linear-gradient(135deg, #0c1a2e 50%, #0e3a5c 50%); }
        .swatch-forest{ background: linear-gradient(135deg, #0d1f0d 50%, #1a3a1a 50%); }

        /* ===== OCEAN THEME ===== */
        [data-theme="ocean"] {
            --bg-base:      #060f1a;
            --bg-page:      #0c1a2e;
            --bg-900:       #0e2038;
            --bg-800:       #102844;
            --bg-700:       #1a3a5c;
            --bg-card:      linear-gradient(135deg, rgba(14,32,56,0.97) 0%, rgba(6,15,26,0.97) 100%);
            --border:       rgba(30,70,110,0.6);
            --border-hover: rgba(45,100,150,0.8);
            --text-primary: #e0f2fe;
            --text-muted:   #7dd3fc;
            --text-faint:   #38bdf8;
            --text-placeholder: #1e4d7a;
            --accent:       #0ea5e9;
            --accent-light: #38bdf8;
            --accent-ring:  rgba(14,165,233,0.3);
            --nav-bg:       rgba(12,26,46,0.9);
            --nav-sub-bg:   rgba(12,26,46,0.6);
            --nav-link-hover-bg: #102844;
            --nav-active-bg:    rgba(14,165,233,0.1);
            --nav-active-border: rgba(14,165,233,0.3);
            --input-bg:     #102844;
            --input-border: #1a3a5c;
            --scrollbar-track: #0c1a2e;
            --scrollbar-thumb: #1a3a5c;
            --scrollbar-hover: #2a5a8c;
            --table-hover:  rgba(16,40,68,0.5);
            --progress-bg:  #102844;
            --day-pending-bg: #102844;
            --day-pending-text: #38bdf8;
        }
        [data-theme="ocean"] .text-white,
        [data-theme="ocean"] .text-gray-100,
        [data-theme="ocean"] .text-gray-200,
        [data-theme="ocean"] .text-gray-300 { color: var(--text-primary) !important; }
        [data-theme="ocean"] .text-gray-400  { color: var(--text-muted) !important; }
        [data-theme="ocean"] .text-gray-500  { color: var(--text-faint) !important; }
        [data-theme="ocean"] .bg-gray-950    { background-color: var(--bg-base) !important; }
        [data-theme="ocean"] .bg-gray-900    { background-color: var(--bg-900) !important; }
        [data-theme="ocean"] .bg-gray-800    { background-color: var(--bg-800) !important; }
        [data-theme="ocean"] .bg-gray-700    { background-color: var(--bg-700) !important; }
        [data-theme="ocean"] .border-gray-800 { border-color: var(--border) !important; }
        [data-theme="ocean"] .border-gray-700 { border-color: var(--input-border) !important; }

        /* ===== FOREST THEME ===== */
        [data-theme="forest"] {
            --bg-base:      #060e06;
            --bg-page:      #0d1f0d;
            --bg-900:       #102810;
            --bg-800:       #143214;
            --bg-700:       #1e4a1e;
            --bg-card:      linear-gradient(135deg, rgba(16,40,16,0.97) 0%, rgba(6,14,6,0.97) 100%);
            --border:       rgba(34,85,34,0.6);
            --border-hover: rgba(50,120,50,0.8);
            --text-primary: #dcfce7;
            --text-muted:   #86efac;
            --text-faint:   #4ade80;
            --text-placeholder: #1a4a1a;
            --accent:       #22c55e;
            --accent-light: #4ade80;
            --accent-ring:  rgba(34,197,94,0.3);
            --nav-bg:       rgba(13,31,13,0.9);
            --nav-sub-bg:   rgba(13,31,13,0.6);
            --nav-link-hover-bg: #143214;
            --nav-active-bg:    rgba(34,197,94,0.1);
            --nav-active-border: rgba(34,197,94,0.3);
            --input-bg:     #143214;
            --input-border: #1e4a1e;
            --scrollbar-track: #0d1f0d;
            --scrollbar-thumb: #1e4a1e;
            --scrollbar-hover: #2e6a2e;
            --table-hover:  rgba(20,50,20,0.5);
            --progress-bg:  #143214;
            --day-pending-bg: #143214;
            --day-pending-text: #4ade80;
        }
        [data-theme="forest"] .text-white,
        [data-theme="forest"] .text-gray-100,
        [data-theme="forest"] .text-gray-200,
        [data-theme="forest"] .text-gray-300 { color: var(--text-primary) !important; }
        [data-theme="forest"] .text-gray-400  { color: var(--text-muted) !important; }
        [data-theme="forest"] .text-gray-500  { color: var(--text-faint) !important; }
        [data-theme="forest"] .bg-gray-950    { background-color: var(--bg-base) !important; }
        [data-theme="forest"] .bg-gray-900    { background-color: var(--bg-900) !important; }
        [data-theme="forest"] .bg-gray-800    { background-color: var(--bg-800) !important; }
        [data-theme="forest"] .bg-gray-700    { background-color: var(--bg-700) !important; }
        [data-theme="forest"] .border-gray-800 { border-color: var(--border) !important; }
        [data-theme="forest"] .border-gray-700 { border-color: var(--input-border) !important; }

        /* Light theme: aksen biru saja */
        [data-theme="light"] .gradient-text {
            background: linear-gradient(135deg, #1d4ed8, #60a5fa);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        [data-theme="light"] .stat-card::before,
        [data-theme="light"] .progress-fill {
            background: linear-gradient(90deg, #2563eb, #60a5fa) !important;
        }
        [data-theme="light"] .btn-primary {
            background: linear-gradient(to right, #2563eb, #3b82f6) !important;
            color: #ffffff !important;
        }
        [data-theme="light"] .glow-cyan { box-shadow: 0 4px 24px rgba(37, 99, 235, 0.15) !important; }
        [data-theme="light"] .glow-green { box-shadow: 0 4px 24px rgba(59, 130, 246, 0.12) !important; }

        [data-theme="light"] .bg-gray-800\/30 { background-color: rgba(219, 234, 254, 0.55) !important; }
        [data-theme="light"] .hover\:bg-gray-800\/50:hover { background-color: rgba(191, 219, 254, 0.65) !important; }
        [data-theme="light"] .bg-gray-800\/50 { background-color: rgba(219, 234, 254, 0.75) !important; }
        [data-theme="light"] .text-cyan-400 { color: var(--accent-light) !important; }
    </style>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        {{-- Fallback: Tailwind CSS CDN + custom styles when Vite build is not available --}}
        <script src="https://cdn.tailwindcss.com"></script>
        <script>
            tailwind.config = {
                theme: {
                    extend: {
                        fontFamily: {
                            inter: ['Inter', 'sans-serif'],
                        },
                        colors: {
                            'cyan-trading': '#00d4ff',
                            'green-trading': '#00ff88',
                        },
                    },
                },
            }
        </script>
        <style>
            /* Custom component styles */
            .tech-card {
                background: linear-gradient(135deg, rgba(17,24,39,0.95) 0%, rgba(9,14,28,0.95) 100%);
                border: 1px solid rgba(55,65,81,0.5);
                backdrop-filter: blur(4px);
                transition: border-color 0.25s ease;
            }
            .tech-card:hover { border-color: rgba(75,85,99,0.7); }
            .gradient-text {
                background: linear-gradient(135deg, #00d4ff, #00ff88);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
                background-clip: text;
            }
            .stat-card {
                background: linear-gradient(135deg, rgba(17,24,39,0.95) 0%, rgba(9,14,28,0.95) 100%);
                border: 1px solid rgba(55,65,81,0.5);
                backdrop-filter: blur(4px);
                border-radius: 1rem;
                padding: 1.25rem;
                position: relative;
                overflow: hidden;
            }
            .stat-card::before {
                content: '';
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                height: 2px;
                background: linear-gradient(90deg, #00d4ff, #00ff88);
                opacity: 0.5;
            }
            .btn-primary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: linear-gradient(to right, #06b6d4, #4ade80);
                color: black;
                font-weight: 700;
                padding: 0.625rem 1.25rem;
                border-radius: 0.75rem;
                cursor: pointer;
                transition: all 0.2s;
                border: none;
            }
            .btn-primary:hover { opacity: 0.9; }
            .btn-primary:active { transform: scale(0.95); }
            .btn-secondary {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: #374151;
                color: white;
                font-weight: 600;
                padding: 0.625rem 1rem;
                border-radius: 0.75rem;
                cursor: pointer;
                transition: all 0.2s;
                border: none;
            }
            .btn-secondary:hover { background: #4b5563; }
            .btn-danger {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                background: #dc2626;
                color: white;
                font-weight: 600;
                padding: 0.625rem 1rem;
                border-radius: 0.75rem;
                cursor: pointer;
                transition: all 0.2s;
                border: none;
            }
            .btn-danger:hover { background: #b91c1c; }
            .form-input {
                width: 100%;
                background: #1f2937;
                border: 1px solid #374151;
                border-radius: 0.75rem;
                padding: 0.75rem 1rem;
                color: white;
                transition: all 0.2s;
            }
            .form-input::placeholder { color: #4b5563; }
            .form-input:focus {
                outline: none;
                border-color: #06b6d4;
                box-shadow: 0 0 0 2px rgba(6,182,212,0.3);
            }
            .form-select {
                width: 100%;
                background: #1f2937;
                border: 1px solid #374151;
                border-radius: 0.75rem;
                padding: 0.75rem 1rem;
                color: white;
                cursor: pointer;
                transition: all 0.2s;
            }
            .form-select:focus {
                outline: none;
                border-color: #06b6d4;
                box-shadow: 0 0 0 2px rgba(6,182,212,0.3);
            }
            .form-label {
                display: block;
                font-size: 0.875rem;
                font-weight: 500;
                color: #9ca3af;
                margin-bottom: 0.375rem;
            }
            .currency-btn {
                flex: 1;
                padding: 0.625rem 0.75rem;
                border-radius: 0.75rem;
                border: 1px solid #374151;
                font-size: 0.875rem;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
                color: #d1d5db;
                background: transparent;
            }
            .currency-btn:hover { border-color: #6b7280; }
            .currency-btn.active { border-color: #06b6d4; background: rgba(6,182,212,0.1); color: #22d3ee; }
            .trader-btn {
                flex: 1;
                padding: 0.75rem 0.5rem;
                border-radius: 0.75rem;
                border: 1px solid #374151;
                font-size: 0.875rem;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.2s;
                text-align: center;
                color: #d1d5db;
                background: transparent;
            }
            .trader-btn:hover { border-color: #6b7280; }
            .trader-btn.active { border-color: #06b6d4; background: rgba(6,182,212,0.1); color: #22d3ee; }
            .nav-link {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.5rem 1rem;
                border-radius: 0.75rem;
                font-size: 0.875rem;
                font-weight: 500;
                color: #9ca3af;
                transition: all 0.2s;
                text-decoration: none;
            }
            .nav-link:hover { color: white; background: #1f2937; }
            .nav-link.active { color: #22d3ee; background: rgba(6,182,212,0.1); border: 1px solid rgba(6,182,212,0.3); }
            .day-badge {
                width: 2.5rem;
                height: 2.5rem;
                border-radius: 0.75rem;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 0.75rem;
                font-weight: 700;
                cursor: default;
                user-select: none;
                transition: all 0.2s;
            }
            .day-badge.pending { background: #1f2937; color: #6b7280; }
            .day-badge.pending:hover { background: #374151; }
            .day-badge.completed { background: rgba(34,197,94,0.15); color: #4ade80; border: 1px solid rgba(34,197,94,0.3); }
            .day-badge.failed { background: rgba(239,68,68,0.15); color: #f87171; border: 1px solid rgba(239,68,68,0.3); }
            .day-badge.skipped { background: rgba(55,65,81,0.5); color: #4b5563; border: 1px solid #374151; }
            .day-badge.current { background: rgba(6,182,212,0.15); color: #22d3ee; border: 1px solid #06b6d4; box-shadow: 0 0 0 4px rgba(6,182,212,0.2); }
            .progress-bar { height: 0.5rem; border-radius: 9999px; background: #1f2937; overflow: hidden; }
            .progress-fill {
                height: 100%;
                border-radius: 9999px;
                transition: all 0.7s ease-out;
                background: linear-gradient(90deg, #00d4ff, #00ff88);
            }
            .alert-success { background: rgba(34,197,94,0.1); border: 1px solid rgba(34,197,94,0.3); color: #4ade80; border-radius: 0.75rem; padding: 0.75rem 1rem; }
            .alert-error { background: rgba(239,68,68,0.1); border: 1px solid rgba(239,68,68,0.3); color: #f87171; border-radius: 0.75rem; padding: 0.75rem 1rem; }
            .data-table { width: 100%; font-size: 0.875rem; }
            .data-table thead tr { border-bottom: 1px solid #1f2937; }
            .data-table thead th { padding: 0.75rem; text-align: left; font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: 0.05em; }
            .data-table tbody tr { border-bottom: 1px solid rgba(31,41,55,0.6); transition: background-color 0.15s; }
            .data-table tbody tr:hover { background: rgba(31,41,55,0.3); }
            .data-table tbody td { padding: 0.75rem; }
            .data-table tfoot td { padding: 0.75rem; border-top: 1px solid #374151; }
            .badge { display: inline-flex; align-items: center; padding: 0.125rem 0.625rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
            .badge-active { background: rgba(6,182,212,0.15); color: #22d3ee; }
            .badge-completed { background: rgba(34,197,94,0.15); color: #4ade80; }
            .badge-pending { background: rgba(234,179,8,0.15); color: #facc15; }
            .badge-failed { background: rgba(239,68,68,0.15); color: #f87171; }
            .glow-cyan { box-shadow: 0 0 30px rgba(0,212,255,0.08); }
            .glow-green { box-shadow: 0 0 30px rgba(0,255,136,0.08); }
            @keyframes slideIn { from { transform: translateX(110%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
            @keyframes fadeInUp { from { transform: translateY(12px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
            .slide-in { animation: slideIn 0.35s cubic-bezier(.22,.68,0,1.2); }
            .fade-in-up { animation: fadeInUp 0.3s ease-out; }
            ::-webkit-scrollbar { width: 5px; height: 5px; }
            ::-webkit-scrollbar-track { background: #0f172a; }
            ::-webkit-scrollbar-thumb { background: #374151; border-radius: 4px; }
            ::-webkit-scrollbar-thumb:hover { background: #4b5563; }
            textarea.form-input { min-height: 100px; resize: vertical; }
        </style>
        {{-- Alpine.js CDN fallback --}}
        <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js"></script>
    @endif
    @stack('styles')
</head>
<body class="bg-gray-950 text-gray-100 font-inter min-h-screen overflow-x-hidden antialiased">

    @include('layouts.partials.header')
    @include('layouts.partials.nav')

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="flash-message fixed top-3 right-3 left-3 sm:left-auto z-50 bg-green-500 text-white px-4 sm:px-6 py-3 sm:py-4 rounded-xl font-semibold shadow-2xl slide-in w-auto max-w-full sm:max-w-sm">
            <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="flash-message fixed top-3 right-3 left-3 sm:left-auto z-50 bg-red-500 text-white px-4 sm:px-6 py-3 sm:py-4 rounded-xl font-semibold shadow-2xl slide-in w-auto max-w-full sm:max-w-sm">
            <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
        </div>
    @endif

    <main class="container mx-auto w-full max-w-7xl min-w-0 px-3 sm:px-4 py-4 sm:py-6">
        @yield('content')
    </main>

    {{-- PWA Install Prompt --}}
    @include('components.pwa-install-prompt')

    @stack('scripts')
    
    {{-- Register Service Worker --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful');
                    })
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }

        // Flash message auto-dismiss
        document.addEventListener('DOMContentLoaded', function() {
            var flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(function(msg) {
                setTimeout(function() {
                    msg.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                    msg.style.opacity = '0';
                    msg.style.transform = 'translateX(100%)';
                    setTimeout(function() { msg.remove(); }, 500);
                }, 4000);
            });

            // Currency button toggle
            var currencyBtns = document.querySelectorAll('.currency-btn');
            var currencyInput = document.getElementById('currency-input');
            currencyBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    currencyBtns.forEach(function(b) { b.classList.remove('active'); });
                    this.classList.add('active');
                    if (currencyInput) { currencyInput.value = this.value; }
                });
            });

            // Trader type button toggle
            var traderBtns = document.querySelectorAll('.trader-btn');
            var traderInput = document.getElementById('trader-type-input');
            traderBtns.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    traderBtns.forEach(function(b) { b.classList.remove('active'); });
                    this.classList.add('active');
                    if (traderInput) { traderInput.value = this.dataset.value; }
                });
            });
        });

        // Theme switching
        window.setTheme = function(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('tj-theme', theme);
            var metaColor = document.getElementById('meta-theme-color');
            if (metaColor) {
                metaColor.setAttribute('content', theme === 'light' ? '#2563eb' : '#0f172a');
            }
            // update toggle icon
            var icons = { dark:'fa-moon', light:'fa-sun', ocean:'fa-water', forest:'fa-tree' };
            var btn = document.getElementById('theme-icon');
            if (btn) {
                btn.className = 'fas ' + (icons[theme] || 'fa-moon');
            }
            // update selected state
            document.querySelectorAll('.theme-option').forEach(function(el) {
                el.classList.toggle('selected', el.dataset.theme === theme);
            });
            // Update Chart.js if present
            if (typeof Chart !== 'undefined') {
                var isDark = theme !== 'light';
                Chart.defaults.color = isDark ? '#9ca3af' : '#475569';
                Chart.defaults.borderColor = isDark ? '#374151' : '#bfdbfe';
            }
        };

        document.addEventListener('DOMContentLoaded', function() {
            // Init icon to current theme
            var current = localStorage.getItem('tj-theme') || 'dark';
            window.setTheme(current);

            // Toggle dropdown
            var toggleBtn  = document.getElementById('theme-toggle-btn');
            var dropdown   = document.getElementById('theme-dropdown');
            if (toggleBtn && dropdown) {
                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdown.classList.toggle('open');
                });
                document.addEventListener('click', function() {
                    dropdown.classList.remove('open');
                });
                dropdown.addEventListener('click', function(e) { e.stopPropagation(); });
            }
        });

        if (typeof Chart !== 'undefined') {
            Chart.defaults.color = '#9ca3af';
            Chart.defaults.borderColor = '#374151';
            Chart.defaults.font.family = 'Inter, sans-serif';
        }
    </script>
</body>
</html>
