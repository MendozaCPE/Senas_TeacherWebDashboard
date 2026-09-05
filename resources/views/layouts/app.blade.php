<!DOCTYPE html>
<html lang="en" id="html-root">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="csrf-token" content="{{ csrf_token() }}"/>
    <title>SEÑAS Teacher Portal - @yield('title', 'Dashboard')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/senya_face.png') }}">
    <!-- ── BFCACHE / SESSION GUARD (must run first, before page renders) ── -->
    <script>
        // 1. If browser restores this page from bfcache after logout,
        //    immediately redirect to login before the user sees anything.
        window.addEventListener('pageshow', function(e) {
            if (e.persisted) {
                // Page is being served from bfcache — session may be dead.
                // Force a hard server-round-trip to let auth middleware decide.
                window.location.replace(window.location.href);
            }
        });

        // 2. Intercept the browser Back button via the History API.
        //    Every authenticated page pushes a sentinel state on load.
        //    If the user presses Back, we push the state again (trapping them)
        //    and then verify the session. If expired → login.
        (function() {
            history.pushState({ _authGuard: true }, '', window.location.href);

            window.addEventListener('popstate', function(e) {
                if (!e.state || !e.state._authGuard) {
                    // Pushback to keep the URL in history
                    history.pushState({ _authGuard: true }, '', window.location.href);
                }
                // Always verify session on any back/forward navigation
                fetch(window.location.href, {
                    method: 'HEAD',
                    credentials: 'same-origin',
                    cache: 'no-store',
                    redirect: 'manual',
                }).then(function(res) {
                    if (res.type === 'opaqueredirect' || res.status === 401 || res.status === 403) {
                        window.location.replace('/login');
                    }
                }).catch(function() {
                    window.location.replace('/login');
                });
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

        /* ── Inactivity Timeout Modal ── */
        #inactivityModal {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(4px);
            align-items: center;
            justify-content: center;
        }
        #inactivityModal.show { display: flex; }
        #inactivityModalBox {
            background: #fff;
            border-radius: 20px;
            padding: 36px 32px 28px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0,0,0,0.18);
            animation: popIn .2s ease;
        }
        @keyframes popIn {
            from { transform: scale(.92); opacity: 0; }
            to   { transform: scale(1);   opacity: 1; }
        }
        #inactivityModal .modal-icon {
            width: 64px; height: 64px;
            background: #fef3c7;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 16px;
            color: #d97706;
            font-size: 32px;
        }
        #inactivityModal h3 {
            font-size: 1.2rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
        }
        #inactivityModal p {
            font-size: .875rem;
            color: #64748b;
            margin-bottom: 24px;
            line-height: 1.5;
        }
        #inactivityModal .countdown {
            font-size: 2rem;
            font-weight: 800;
            color: #d97706;
        }
        #inactivityModal .btn-stay {
            background: #0d326b;
            color: #fff;
            border: none;
            border-radius: 10px;
            padding: 10px 28px;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            margin-right: 10px;
            transition: background .15s;
        }
        #inactivityModal .btn-stay:hover { background: #1e4b8f; }
        #inactivityModal .btn-logout {
            background: transparent;
            color: #64748b;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 28px;
            font-size: .95rem;
            font-weight: 600;
            cursor: pointer;
            transition: all .15s;
        }
        #inactivityModal .btn-logout:hover { background: #f8fafc; color: #0f172a; }
    </style>
</head>
<body class="font-sans antialiased flex h-screen overflow-hidden bg-[#f5f8fc] @yield('bg-class', '')">

    <!-- Sidebar -->
    @include('partials.sidebar')

    <!-- Main Layout Area -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Header Topbar -->
        @include('partials.header')

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto px-8 pt-2 pb-6 relative border-l border-slate-100">
            @yield('content')
        </main>

        <!-- Footer -->
        @include('partials.footer')
    </div>

    <!-- ── Inactivity Timeout Modal ─────────────────────────────────────── -->
    <div id="inactivityModal" role="dialog" aria-modal="true" aria-labelledby="inactivityTitle">
        <div id="inactivityModalBox">
            <div class="modal-icon">
                <span class="material-symbols-outlined">schedule</span>
            </div>
            <h3 id="inactivityTitle">Still there?</h3>
            <p>You've been inactive for a while. For your security, you'll be signed out automatically in</p>
            <div class="countdown" id="inactivityCountdown">60</div>
            <p style="margin-top:4px; margin-bottom:24px; font-size:.8rem;">seconds</p>
            <div>
                <button class="btn-stay" id="inactivityStayBtn">Stay signed in</button>
                <button class="btn-logout" onclick="inactivityLogout()">Sign out now</button>
            </div>
        </div>
    </div>

    <!-- Hidden logout form for inactivity auto-submit -->
    <form id="inactivityLogoutForm" method="POST" action="{{ route('logout') }}" style="display:none;">
        @csrf
    </form>

    <!-- Clear any previously stored dark mode preference -->
    <script>localStorage.removeItem('theme');</script>

    <!-- ── Inactivity Timer (warns at 9 min, logs out at 10 min) ─────────── -->
    <script>
    (function () {
        // Total idle limit: 10 minutes (matches SESSION_LIFETIME in .env)
        var IDLE_LIMIT_MS   = 10 * 60 * 1000;   // 10 minutes
        // Show the warning modal this many ms before auto-logout
        var WARNING_LEAD_MS = 60 * 1000;          // 60 second warning
        var WARN_AT_MS      = IDLE_LIMIT_MS - WARNING_LEAD_MS; // 9 minutes

        var idleTimer     = null;
        var countdownTimer = null;
        var countdown     = 60;
        var modal         = document.getElementById('inactivityModal');
        var countdownEl   = document.getElementById('inactivityCountdown');
        var stayBtn       = document.getElementById('inactivityStayBtn');

        function resetIdle() {
            clearTimeout(idleTimer);
            // If modal is showing, close it when user moves/types
            if (modal.classList.contains('show')) {
                closeModal();
            }
            idleTimer = setTimeout(showWarning, WARN_AT_MS);
        }

        function showWarning() {
            countdown = 60;
            countdownEl.textContent = countdown;
            modal.classList.add('show');
            modal.style.display = 'flex';

            countdownTimer = setInterval(function () {
                countdown--;
                countdownEl.textContent = countdown;
                if (countdown <= 0) {
                    clearInterval(countdownTimer);
                    inactivityLogout();
                }
            }, 1000);
        }

        function closeModal() {
            clearInterval(countdownTimer);
            modal.classList.remove('show');
            modal.style.display = 'none';
        }

        stayBtn.addEventListener('click', function () {
            closeModal();
            resetIdle();
            // POST the session ping route to keep the server-side session alive
            fetch('{{ route('session.ping') }}', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
            }).then(function(res) {
                if (res.status === 401) {
                    // Session already expired on the server — redirect to login
                    window.location.replace('/login');
                }
            }).catch(function() {});
        });

        window.inactivityLogout = function () {
            clearInterval(countdownTimer);
            closeModal();
            document.getElementById('inactivityLogoutForm').submit();
        };

        // Track all user activity events
        var events = ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll', 'click'];
        events.forEach(function (ev) {
            document.addEventListener(ev, resetIdle, { passive: true });
        });

        // Kick off the timer on page load
        resetIdle();
    })();
    </script>

</body>
</html>
