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
        /* Global scale — equivalent to one Ctrl+- press in the browser */
        html { zoom: 90%; }
        /* Compensate: zoom changes how vh resolves, so anchor heights to % instead */
        html, body { height: 100%; }

        .material-symbols-outlined { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .material-symbols-outlined.icon-outline { font-variation-settings: 'FILL' 0; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* ── Skeleton / Shimmer ─────────────────────────────────────────── */
        @keyframes shimmer {
            0%   { background-position: -600px 0; }
            100% { background-position:  600px 0; }
        }
        .skeleton {
            background: #e2e8f0;
            background-image: linear-gradient(
                90deg,
                #e2e8f0 0px,
                #f1f5f9 40%,
                #eef2f7 55%,
                #e2e8f0 100%
            );
            background-size: 600px 100%;
            animation: shimmer 1.6s infinite linear;
            border-radius: 6px;
        }
        .skeleton-circle { border-radius: 9999px; }
        .skeleton-card   { border-radius: 24px; }
        /* Hide real content while skeleton is showing */
        .page-loading .skeleton-hide { display: none; }
        /* Hide skeleton once page is loaded */
        .page-loaded #page-skeleton  { display: none; }
    </style>

    <script>document.body ? document.body.classList.add('page-loading') : document.addEventListener('DOMContentLoaded', function(){ document.body.classList.add('page-loading'); });</script>

    @yield('extra-head')
</head>
<body class="font-sans antialiased flex h-full overflow-hidden bg-[#f5f8fc]">
    <script>document.body.classList.add('page-loading');</script>

    @include('partials.admin-sidebar')

    <div class="flex-1 flex flex-col h-full overflow-hidden">

        <!-- Admin Header -->
        @include('partials.admin-header')

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto px-8 pt-2 pb-0 relative border-l border-slate-100">
            @yield('content')
        </main>

        @include('partials.footer')
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.body.classList.remove('page-loading');
            document.body.classList.add('page-loaded');
        });
    </script>
</body>
</html>
