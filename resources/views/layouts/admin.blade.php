<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>SEÑAS Admin — @yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/senya_face.png') }}">

    {{-- bfcache / back-button session guard --}}
    <script>
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) window.location.replace(window.location.href);
        });
        (function() {
            history.pushState({ _authGuard: true }, '', window.location.href);
            window.addEventListener('popstate', function(e) {
                if (!e.state || !e.state._authGuard) {
                    history.pushState({ _authGuard: true }, '', window.location.href);
                }
                fetch(window.location.href, {
                    method: 'HEAD', credentials: 'same-origin',
                    cache: 'no-store', redirect: 'manual'
                }).then(function(res) {
                    if (res.type === 'opaqueredirect' || res.status === 401 || res.status === 403) {
                        window.location.replace('/login');
                    }
                }).catch(function() { window.location.replace('/login'); });
            });
        })();
    </script>

    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Inter', 'sans-serif'] },
                    colors: {
                        brand: {
                            blue:      '#0d326b',
                            lightBlue: '#1e4b8f',
                            yellow:    '#facc15',
                            bg:        '#f4f7f9',
                            card:      '#ffffff'
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
    </style>

    @yield('extra-head')
</head>
<body class="font-sans antialiased flex h-screen overflow-hidden bg-[#f5f8fc]">

    @include('partials.admin-sidebar')

    <div class="flex-1 flex flex-col h-screen overflow-hidden">

        <!-- Admin Header -->
        @include('partials.admin-header')

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto px-8 pt-2 pb-6 relative border-l border-slate-100">
            @yield('content')
        </main>

        @include('partials.footer')
    </div>

</body>
</html>
