<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SEÑAS Teacher Portal - @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            blue: '#0d326b',
                            lightBlue: '#1e4b8f',
                            yellow: '#facc15',
                            bg: '#f4f7f9',
                            card: '#ffffff'
                        },
                        dark: {
                            bg: '#001F3F',
                            card: '#0a2a4a',
                            sidebar: '#021428',
                            border: '#0047AB',
                            primary: '#F0DD58',
                            secondary: '#007FFF',
                            tertiary: '#0047AB',
                            text: '#e2e8f0',
                            muted: '#94a3b8',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .material-symbols-outlined.icon-outline { font-variation-settings: 'FILL' 0; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Dark mode global overrides */
        html.dark { background-color: #0D1B2E; color: #ffffff; }
        html.dark body { background-color: #0D1B2E; color: #ffffff; }
        html.dark aside { background-color: #091524 !important; border-color: transparent !important; }
        html.dark header { background-color: #091524 !important; border-color: transparent !important; }
        html.dark main { background-color: #0D1B2E !important; border-color: transparent !important; }
        html.dark .bg-white { background-color: #112240 !important; }
        html.dark .bg-\[\#f4f7f9\] { background-color: #0D1B2E !important; }
        html.dark .bg-\[\#eef2f6\] { background-color: #112240 !important; }
        html.dark .bg-\[\#f8f9fa\] { background-color: #091524 !important; }
        html.dark .bg-slate-100 { background-color: rgba(255,255,255,0.06) !important; }
        html.dark .bg-\[\#f1f5f9\] { background-color: rgba(255,255,255,0.06) !important; }
        html.dark .bg-slate-50 { background-color: rgba(255,255,255,0.04) !important; }
        html.dark .border-slate-100, html.dark .border-slate-200, html.dark .border { border-color: transparent !important; }
        html.dark .text-slate-700, html.dark .text-slate-800 { color: #ffffff !important; }
        html.dark .text-slate-600 { color: #ffffff !important; }
        html.dark .text-slate-500 { color: rgba(255,255,255,0.65) !important; }
        html.dark .text-slate-400 { color: rgba(255,255,255,0.45) !important; }
        html.dark .text-\[\#0d326b\] { color: #ffffff !important; }
        html.dark .text-brand-blue { color: #ffffff !important; }
        html.dark h1.text-\[\#0d326b\], html.dark h2.text-\[\#0d326b\], html.dark h3.text-\[\#0d326b\], html.dark h4.text-\[\#0d326b\] { color: #ffffff !important; }
        html.dark .hover\:bg-white\/50:hover { background-color: rgba(255,255,255,0.04) !important; }
        html.dark .hover\:bg-slate-100\/70:hover { background-color: rgba(30,58,95,0.5) !important; }
        html.dark .hover\:bg-slate-50:hover { background-color: rgba(15,32,53,0.8) !important; }
        html.dark .shadow-sm, html.dark .shadow-\[0_4px_20px_rgba\(0\,0\,0\,0\.02\)\] { box-shadow: 0 4px 20px rgba(0,0,0,0.4) !important; }
        html.dark .bg-indigo-50 { background-color: rgba(30,58,95,0.4) !important; }
        html.dark .bg-yellow-50 { background-color: rgba(240,221,88,0.07) !important; }
        html.dark .bg-blue-50 { background-color: rgba(0,127,255,0.1) !important; }
        html.dark .bg-emerald-50 { background-color: rgba(16,185,129,0.1) !important; }
        html.dark .bg-red-50 { background-color: rgba(220,38,38,0.1) !important; }

        /* Smooth transitions */
        body, aside, header, main, .bg-white, .bg-slate-100 { transition: background-color 0.3s ease, border-color 0.3s ease, color 0.3s ease; }

        /* Dark mode scrollbar */
        html.dark ::-webkit-scrollbar-thumb { background: #0047AB; }
        html.dark ::-webkit-scrollbar-thumb:hover { background: #007FFF; }
    </style>
</head>
<body class="font-sans antialiased flex h-screen overflow-hidden bg-[#f4f7f9] @yield('bg-class', '')">

    <!-- Sidebar -->
    @include('partials.sidebar')

    <!-- Main Layout Area -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Header Topbar -->
        @include('partials.header')

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto px-8 pt-8 pb-10 relative border-l border-slate-100">
            @yield('content')
        </main>
    </div>

    <!-- Dark Mode Script -->
    <script>
        (function() {
            const html = document.getElementById('html-root');
            const saved = localStorage.getItem('theme');
            if (saved === 'dark') html.classList.add('dark');
        })();

        function toggleDarkMode() {
            const html = document.getElementById('html-root');
            const isDark = html.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
            const btn = document.getElementById('darkmode-btn');
            const icon = document.getElementById('darkmode-icon');
            if (isDark) {
                icon.textContent = 'light_mode';
                btn.classList.add('text-[#F0DD58]');
                btn.classList.remove('text-slate-400');
            } else {
                icon.textContent = 'dark_mode';
                btn.classList.remove('text-[#F0DD58]');
                btn.classList.add('text-slate-400');
            }
        }

        // Set initial icon state
        document.addEventListener('DOMContentLoaded', function() {
            const html = document.getElementById('html-root');
            const btn = document.getElementById('darkmode-btn');
            const icon = document.getElementById('darkmode-icon');
            if (html.classList.contains('dark')) {
                icon.textContent = 'light_mode';
                btn.classList.add('text-[#F0DD58]');
                btn.classList.remove('text-slate-400');
            }
        });
    </script>

</body>
</html>
