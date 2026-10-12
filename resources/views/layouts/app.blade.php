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
        /* Global scale — equivalent to one Ctrl+- press in the browser */
        html { zoom: 90%; }
        /* Compensate: zoom changes how vh resolves, so anchor heights to % instead */
        html, body { height: 100%; }

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
        .skeleton-circle  { border-radius: 9999px; }
        .skeleton-card    { border-radius: 24px; }
        /* Hide real content while skeleton is showing */
        .page-loading .skeleton-hide  { display: none; }
        /* Hide skeleton once loaded */
        .page-loaded  #dashboard-skeleton { display: none !important; }

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

        #tutorial-modal .tutorial-scrollbar {
            scrollbar-gutter: stable;
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 #f1f5f9;
        }
        #tutorial-modal .tutorial-scrollbar::-webkit-scrollbar { width: 9px; }
        #tutorial-modal .tutorial-scrollbar::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 9999px; }
        #tutorial-modal .tutorial-scrollbar::-webkit-scrollbar-thumb {
            background: #94a3b8;
            border: 2px solid #f1f5f9;
            border-radius: 9999px;
        }
        #tutorial-modal .tutorial-scrollbar::-webkit-scrollbar-thumb:hover {
            background: #64748b;
        }
        #add-student-modal .student-modal-scrollbar {
            scrollbar-gutter: stable;
            scrollbar-width: thin;
            scrollbar-color: #1a6fd4 #eaf1fb;
        }
        #add-student-modal .student-modal-scrollbar::-webkit-scrollbar { width: 9px; }
        #add-student-modal .student-modal-scrollbar::-webkit-scrollbar-track { background: #eaf1fb; border-radius: 9999px; }
        #add-student-modal .student-modal-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #0d326b 0%, #1a6fd4 100%);
            border: 2px solid #eaf1fb;
            border-radius: 9999px;
        }
        #add-student-modal .student-modal-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, #092653 0%, #155bb3 100%);
        }
    </style>
</head>
<body class="font-sans antialiased flex h-full overflow-hidden bg-[#f5f8fc] @yield('bg-class', '')">

    <!-- Sidebar -->
    @include('partials.sidebar')

    <!-- Main Layout Area -->
    <div class="flex-1 flex flex-col h-full overflow-hidden">
        
        <!-- Header Topbar -->
        @include('partials.header')

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto px-8 pt-2 pb-0 relative border-l border-slate-100">
            @yield('content')
        </main>

        <!-- Footer — sticky at bottom of the layout column, outside scroll area -->
        @include('partials.footer')
    </div>

    <!-- ── Inactivity Timeout Modal ─────────────────────────────────────── -->
    <div id="tutorial-modal" class="fixed inset-0 z-[10000] hidden items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4" role="dialog" aria-modal="true" aria-labelledby="tutorial-title">
        <div class="flex max-h-[90vh] w-full max-w-6xl flex-col overflow-hidden rounded-3xl bg-white shadow-2xl">
            <div class="h-1.5 w-full shrink-0 bg-gradient-to-r from-[#0d326b] to-[#1a6fd4]"></div>
            <div class="flex items-start justify-between gap-4 border-b border-slate-100 px-6 py-5">
                <div><p class="text-[10px] font-black uppercase tracking-[.2em] text-blue-500">SEÑAS Teacher Portal</p><h2 id="tutorial-title" class="mt-1 text-xl font-extrabold text-[#0d326b]">Tutorials &amp; Help</h2></div>
                <button id="tutorial-close" type="button" class="flex h-9 w-9 items-center justify-center rounded-full text-slate-500 hover:bg-slate-100" aria-label="Close tutorials"><span class="material-symbols-outlined">close</span></button>
            </div>
            <div class="border-b border-slate-100 px-5 pt-4 sm:px-6">
                <div class="inline-flex rounded-xl bg-slate-100 p-1" role="tablist" aria-label="Tutorial help sections">
                    <button id="tutorial-tab-videos" type="button" role="tab" aria-selected="true" class="rounded-lg bg-white px-4 py-2 text-sm font-bold text-[#0d326b] shadow-sm">Watch Tutorials</button>
                    <button id="tutorial-tab-help" type="button" role="tab" aria-selected="false" class="rounded-lg px-4 py-2 text-sm font-semibold text-slate-500 hover:text-[#0d326b]">FAQs &amp; Ask</button>
                </div>
            </div>
            <div id="tutorial-panel-body" class="tutorial-scrollbar min-h-0 w-full flex-1 overflow-y-auto overscroll-contain">
            <div id="tutorial-watch-view" class="grid w-full gap-6 p-5 sm:p-6 lg:grid-cols-[minmax(260px,.78fr)_minmax(0,1.5fr)]">
                <section class="min-w-0" aria-label="Tutorial videos">
                    <div class="mb-4"><h3 class="text-lg font-extrabold text-[#0d326b]">Video Tutorials</h3><p class="mt-1 text-sm text-slate-500">Choose a guide to see its video and steps.</p></div>
                    <div id="tutorial-library" class="tutorial-scrollbar grid max-h-[68vh] gap-3 overflow-y-auto pr-2"></div>
                </section>
                <section class="min-w-0" aria-label="Selected tutorial">
                    <div id="tutorial-empty-state" class="flex min-h-[280px] flex-col items-center justify-center rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-6 text-center">
                        <span class="material-symbols-outlined text-4xl text-blue-300">ondemand_video</span>
                        <p class="mt-3 text-sm font-bold text-slate-600">Choose a tutorial to get started</p>
                        <p class="mt-1 max-w-xs text-xs leading-relaxed text-slate-400">The video and its step-by-step guide will appear here.</p>
                    </div>
                    <div id="tutorial-player-wrap" class="hidden">
                        <h3 id="tutorial-selected-title" class="mb-3 text-lg font-extrabold leading-snug text-[#0d326b]"></h3>
                        <div class="aspect-video overflow-hidden rounded-2xl bg-slate-950"><iframe id="tutorial-player" class="h-full w-full" src="" title="SEÑAS tutorial video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe></div>
                        <label id="tutorial-preference" class="mt-4 flex cursor-pointer items-center gap-2 text-sm font-medium text-slate-600"><input id="tutorial-dont-show" type="checkbox" class="h-4 w-4 rounded border-slate-300 text-[#0d326b] focus:ring-[#0d326b]">Don’t show this tutorial automatically again</label>
                        <p id="tutorial-help-note" class="hidden ml-6 mt-1 text-xs text-slate-500">You can still watch it anytime from the ? Help button beside Notifications.</p>
                        <div class="mt-5 rounded-2xl border border-slate-100 bg-white p-5 shadow-sm">
                            <p class="text-xs font-black uppercase tracking-wider text-slate-400">About this tutorial</p>
                            <p id="tutorial-selected-summary" class="mt-2 text-sm leading-relaxed text-slate-600"></p>
                            <h4 class="mt-5 text-sm font-extrabold text-[#0d326b]">Step-by-step guide</h4>
                            <ol id="tutorial-selected-steps" class="mt-2 list-decimal space-y-2 pl-5 text-sm leading-relaxed text-slate-600"></ol>
                        </div>
                    </div>
                </section>
            </div>
            <section id="tutorial-faq-section" class="hidden w-full p-5 sm:p-6">
                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(320px,.9fr)]">
                    <div class="mb-4 flex flex-wrap items-end justify-between gap-3 lg:col-span-2">
                        <div><h3 class="text-lg font-extrabold text-[#0d326b]">Frequently Asked Questions</h3><p class="mt-1 text-sm text-slate-500">Choose a question or ask the DeepSeek help assistant.</p></div>
                        <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-[#0d326b]">Grounded in the tutorial guides</span>
                    </div>
                        <div class="min-w-0">
                            <label for="tutorial-faq-search" class="mb-2 block text-xs font-bold uppercase tracking-wide text-slate-500">Browse questions</label>
                            <input id="tutorial-faq-search" type="search" placeholder="Search a frequent question..." class="mb-3 w-full rounded-xl border border-slate-200 px-4 py-2.5 text-sm outline-none focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/10">
                            <div id="tutorial-faq-list" class="tutorial-scrollbar max-h-56 space-y-3 overflow-y-auto pr-2"></div>
                        </div>
                        <div class="min-w-0">
                            <form id="tutorial-question-form" class="rounded-2xl border border-slate-200 bg-slate-50 p-4">
                                <label for="tutorial-question" class="block text-sm font-extrabold text-[#0d326b]">Ask a question</label>
                                <p class="mt-1 text-xs leading-relaxed text-slate-500">Ask how to do something in the teacher dashboard. The assistant uses the tutorial guides and will say when a detail is not covered.</p>
                                <textarea id="tutorial-question" rows="3" maxlength="600" required placeholder="Example: How can I enroll a whole class?" class="mt-3 w-full resize-y rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm outline-none focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/10"></textarea>
                                <button id="tutorial-ask-submit" type="submit" class="mt-3 inline-flex items-center gap-2 rounded-xl bg-[#0d326b] px-4 py-2.5 text-sm font-bold text-white transition hover:bg-[#1e4b8f] disabled:cursor-not-allowed disabled:opacity-60"><span class="material-symbols-outlined text-lg">auto_awesome</span>Ask SEÑAS Help</button>
                            </form>
                            <div id="tutorial-answer" class="mt-3 hidden rounded-2xl border border-blue-100 bg-blue-50/60 p-4" aria-live="polite">
                                <p id="tutorial-answer-text" class="text-sm leading-relaxed text-slate-700"></p>
                                <ol id="tutorial-answer-steps" class="mt-3 list-decimal space-y-1 pl-5 text-sm leading-relaxed text-slate-700"></ol>
                                <div class="mt-4 border-t border-blue-100 pt-3"><p class="text-[10px] font-black uppercase tracking-wider text-slate-500">Suggested video</p><p id="tutorial-suggested-title" class="mt-1 text-sm font-bold text-[#0d326b]"></p><button id="tutorial-watch-suggested" type="button" class="mt-2 inline-flex items-center gap-1 rounded-lg bg-white px-3 py-2 text-xs font-bold text-[#0d326b] shadow-sm ring-1 ring-blue-100 hover:bg-blue-50"><span class="material-symbols-outlined text-base">play_circle</span>Watch this tutorial</button></div>
                            </div>
                    </div>
                </div>
            </section>
            </div>
        </div>
    </div>
    <div id="tutorial-exit-confirm" class="fixed inset-0 z-[10001] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4" role="alertdialog" aria-modal="true" aria-labelledby="tutorial-exit-title">
        <div class="w-full max-w-sm rounded-3xl bg-white p-6 text-center shadow-2xl">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 text-amber-600"><span class="material-symbols-outlined text-3xl">live_help</span></div>
            <h3 id="tutorial-exit-title" class="text-lg font-extrabold text-[#0d326b]">Do you really want to exit this tutorial?</h3>
            <p class="mt-2 text-sm leading-relaxed text-slate-500">You can watch it anytime from the ? Help button beside Notifications.</p>
            <label class="mt-4 flex cursor-pointer items-start gap-2 text-left text-sm font-medium text-slate-600"><input id="tutorial-exit-dont-show" type="checkbox" class="mt-0.5 h-4 w-4 rounded border-slate-300 text-[#0d326b] focus:ring-[#0d326b]"><span>Don’t show this tutorial automatically again</span></label>
            <div class="mt-6 flex gap-3">
                <button id="tutorial-exit-stay" type="button" class="flex-1 rounded-xl border border-slate-200 px-4 py-2.5 text-sm font-bold text-slate-600 hover:bg-slate-50">Keep watching</button>
                <button id="tutorial-exit-confirm-btn" type="button" class="flex-1 rounded-xl bg-[#0d326b] px-4 py-2.5 text-sm font-bold text-white hover:bg-[#1e4b8f]">Exit tutorial</button>
            </div>
        </div>
    </div>
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
    <!-- Mark body as loading immediately so skeletons show; swap on DOMContentLoaded -->
    <script>document.body.classList.add('page-loading');</script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.body.classList.remove('page-loading');
            document.body.classList.add('page-loaded');
        });
    </script>

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

<script>
(function () {
    var tutorials = @json(config('tutorial_help.videos'));
    var userId = @json((string) Auth::id());
    var prefix = 'senas_tutorials_' + userId + '_';
    var modal = document.getElementById('tutorial-modal');
    var player = document.getElementById('tutorial-player');
    var playerWrap = document.getElementById('tutorial-player-wrap');
    var emptyState = document.getElementById('tutorial-empty-state');
    var watchView = document.getElementById('tutorial-watch-view');
    var library = document.getElementById('tutorial-library');
    var faqSection = document.getElementById('tutorial-faq-section');
    var faqList = document.getElementById('tutorial-faq-list');
    var faqSearch = document.getElementById('tutorial-faq-search');
    var questionForm = document.getElementById('tutorial-question-form');
    var questionInput = document.getElementById('tutorial-question');
    var answerPanel = document.getElementById('tutorial-answer');
    var title = document.getElementById('tutorial-title');
    var selectedTitle = document.getElementById('tutorial-selected-title');
    var selectedSummary = document.getElementById('tutorial-selected-summary');
    var selectedSteps = document.getElementById('tutorial-selected-steps');
    var videosTab = document.getElementById('tutorial-tab-videos');
    var helpTab = document.getElementById('tutorial-tab-help');
    var preference = document.getElementById('tutorial-preference');
    var dontShow = document.getElementById('tutorial-dont-show');
    var exitDontShow = document.getElementById('tutorial-exit-dont-show');
    var helpNote = document.getElementById('tutorial-help-note');
    var exitConfirm = document.getElementById('tutorial-exit-confirm');
    var activeTutorial = null;
    var isAutomaticTutorial = false;
    if (!modal) return;

    function openModal() { modal.classList.remove('hidden'); modal.classList.add('flex'); document.body.classList.add('overflow-hidden'); }
    function setActiveTab(tab, restoreVideo) {
        var showVideos = tab === 'videos';
        watchView.classList.toggle('hidden', !showVideos);
        faqSection.classList.toggle('hidden', showVideos);
        videosTab.setAttribute('aria-selected', showVideos ? 'true' : 'false');
        helpTab.setAttribute('aria-selected', showVideos ? 'false' : 'true');
        videosTab.className = showVideos
            ? 'rounded-lg bg-white px-4 py-2 text-sm font-bold text-[#0d326b] shadow-sm'
            : 'rounded-lg px-4 py-2 text-sm font-semibold text-slate-500 hover:text-[#0d326b]';
        helpTab.className = showVideos
            ? 'rounded-lg px-4 py-2 text-sm font-semibold text-slate-500 hover:text-[#0d326b]'
            : 'rounded-lg bg-white px-4 py-2 text-sm font-bold text-[#0d326b] shadow-sm';
        if (!showVideos && !playerWrap.classList.contains('hidden')) {
            player.src = '';
            playerWrap.classList.add('hidden');
            emptyState.classList.remove('hidden');
            emptyState.querySelector('p').textContent = 'Your tutorial is paused while you browse help.';
        } else if (showVideos && restoreVideo && activeTutorial && playerWrap.classList.contains('hidden')) {
            playTutorial(activeTutorial, false, isAutomaticTutorial);
        }
    }
    function stopVideo() {
        player.src = '';
        playerWrap.classList.add('hidden');
        emptyState.classList.remove('hidden');
        emptyState.querySelector('p').textContent = 'Choose a tutorial to get started';
        preference.classList.add('hidden');
        helpNote.classList.add('hidden');
        activeTutorial = null;
        isAutomaticTutorial = false;
    }
    function closeModal() { stopVideo(); modal.classList.add('hidden'); modal.classList.remove('flex'); exitConfirm.classList.add('hidden'); exitConfirm.classList.remove('flex'); document.body.classList.remove('overflow-hidden'); }
    function requestExit() {
        if (!activeTutorial || !isAutomaticTutorial) { closeModal(); return; }
        exitDontShow.checked = !!(activeTutorial && localStorage.getItem(prefix + 'disabled_' + activeTutorial.id) === '1');
        exitConfirm.classList.remove('hidden');
        exitConfirm.classList.add('flex');
    }
    function playTutorial(item, autoplay, automaticOrigin) {
        activeTutorial = item;
        isAutomaticTutorial = automaticOrigin === undefined ? autoplay : automaticOrigin;
        title.textContent = 'Tutorials & Help';
        selectedTitle.textContent = item.title;
        selectedSummary.textContent = item.summary;
        selectedSteps.replaceChildren();
        item.steps.forEach(function (step) {
            var li = document.createElement('li');
            li.textContent = step;
            selectedSteps.appendChild(li);
        });
        library.querySelectorAll('[data-tutorial-id]').forEach(function (button) {
            var isActive = button.dataset.tutorialId === item.id;
            button.classList.toggle('border-[#0d326b]/40', isActive);
            button.classList.toggle('bg-blue-50', isActive);
            button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });
        setActiveTab('videos', false);
        playerWrap.classList.remove('hidden');
        emptyState.classList.add('hidden');
        preference.classList.toggle('hidden', !autoplay);
        helpNote.classList.toggle('hidden', !autoplay);
        dontShow.checked = false;
        player.src = 'https://www.youtube-nocookie.com/embed/' + item.youtube_id + '?autoplay=' + (autoplay ? '1' : '0') + (autoplay ? '&mute=1' : '') + '&rel=0';
        openModal();
    }
    function renderFaqs(query) {
        var term = (query || '').trim().toLocaleLowerCase();
        faqList.innerHTML = tutorials.map(function (item, index) {
            var matches = item.faqs.filter(function (question) { return !term || question.toLocaleLowerCase().includes(term); });
            if (!matches.length) return '';
            return '<details class="overflow-hidden rounded-xl border border-slate-200 bg-white" ' + (term || index === 0 ? 'open' : '') + '><summary class="cursor-pointer px-3 py-2.5 text-xs font-extrabold text-[#0d326b] hover:bg-slate-50">' + item.title + ' <span class="ml-1 font-semibold text-slate-400">(' + matches.length + ')</span></summary><div class="divide-y divide-slate-100 border-t border-slate-100">' + matches.map(function (question) {
                return '<button type="button" data-faq-question="' + encodeURIComponent(question) + '" class="block w-full px-3 py-2 text-left text-xs leading-relaxed text-slate-600 hover:bg-blue-50 hover:text-[#0d326b]">' + question + '</button>';
            }).join('') + '</div></details>';
        }).join('') || '<p class="rounded-xl bg-slate-50 p-4 text-sm text-slate-500">No matching questions. Try asking the helper.</p>';
    }
    function showLibrary() {
        stopVideo();
        title.textContent = 'Tutorials & Help';
        setActiveTab('videos', false);
        library.innerHTML = tutorials.map(function (item) {
            return '<button type="button" aria-pressed="false" data-tutorial-id="' + item.id + '" class="flex items-start gap-3 rounded-2xl border border-slate-200 p-4 text-left transition hover:border-[#0d326b]/40 hover:bg-blue-50"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-[#0d326b]"><span class="material-symbols-outlined">' + item.icon + '</span></span><span><span class="block text-sm font-bold leading-snug text-slate-800">' + item.title + '</span><span class="mt-1 block text-xs font-semibold text-[#0d326b]">Watch tutorial <span aria-hidden="true">→</span></span></span></button>';
        }).join('');
        renderFaqs(faqSearch.value);
        openModal();
    }
    document.getElementById('tutorial-help-btn').addEventListener('click', showLibrary);
    videosTab.addEventListener('click', function () { setActiveTab('videos', true); });
    helpTab.addEventListener('click', function () { setActiveTab('help', false); });
    document.getElementById('tutorial-close').addEventListener('click', requestExit);
    document.getElementById('tutorial-exit-stay').addEventListener('click', function () { exitConfirm.classList.add('hidden'); exitConfirm.classList.remove('flex'); });
    document.getElementById('tutorial-exit-confirm-btn').addEventListener('click', function () {
        if (activeTutorial) {
            var disabledKey = prefix + 'disabled_' + activeTutorial.id;
            var snoozeKey = prefix + 'snoozed_until_' + activeTutorial.id;
            if (exitDontShow.checked) {
                localStorage.setItem(disabledKey, '1');
                localStorage.removeItem(snoozeKey);
            } else {
                localStorage.removeItem(disabledKey);
                localStorage.setItem(snoozeKey, String(Date.now() + 24 * 60 * 60 * 1000));
            }
        }
        closeModal();
    });
    library.addEventListener('click', function (event) {
        var button = event.target.closest('[data-tutorial-id]');
        if (!button) return;
        var item = tutorials.find(function (entry) { return entry.id === button.dataset.tutorialId; });
        if (item) playTutorial(item, false);
    });
    faqSearch.addEventListener('input', function () { renderFaqs(faqSearch.value); });
    faqList.addEventListener('click', function (event) {
        var button = event.target.closest('[data-faq-question]');
        if (!button) return;
        questionInput.value = decodeURIComponent(button.dataset.faqQuestion);
        questionInput.focus();
        questionForm.requestSubmit();
    });
    questionForm.addEventListener('submit', async function (event) {
        event.preventDefault();
        var question = questionInput.value.trim();
        if (question.length < 3) return;
        var submit = document.getElementById('tutorial-ask-submit');
        submit.disabled = true;
        submit.innerHTML = '<span class="material-symbols-outlined animate-spin text-lg">progress_activity</span>Getting steps...';
        answerPanel.classList.remove('hidden');
        document.getElementById('tutorial-answer-text').textContent = 'Checking the SEÑAS tutorial guides…';
        document.getElementById('tutorial-answer-steps').replaceChildren();
        try {
            var response = await fetch(@json(route('tutorials.ask')), {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: JSON.stringify({ question: question })
            });
            var data = await response.json();
            if (!response.ok) throw new Error(data.message || data.errors?.question?.[0] || 'Could not answer right now. Please try again.');
            document.getElementById('tutorial-answer-text').textContent = data.answer;
            var steps = document.getElementById('tutorial-answer-steps');
            (data.steps || []).forEach(function (step) { var li = document.createElement('li'); li.textContent = step; steps.appendChild(li); });
            document.getElementById('tutorial-suggested-title').textContent = data.video.title;
            answerPanel.dataset.videoId = data.video.id;
        } catch (error) {
            document.getElementById('tutorial-answer-text').textContent = error.message;
        } finally {
            submit.disabled = false;
            submit.innerHTML = '<span class="material-symbols-outlined text-lg">auto_awesome</span>Ask SEÑAS Help';
        }
    });
    document.getElementById('tutorial-watch-suggested').addEventListener('click', function () {
        var item = tutorials.find(function (entry) { return entry.id === answerPanel.dataset.videoId; });
        if (item) playTutorial(item, false);
    });
    dontShow.addEventListener('change', function () {
        if (!activeTutorial) return;
        var key = prefix + 'disabled_' + activeTutorial.id;
        if (dontShow.checked) localStorage.setItem(key, '1');
        else localStorage.removeItem(key);
        exitDontShow.checked = dontShow.checked;
    });
    modal.addEventListener('click', function (event) { if (event.target === modal) requestExit(); });
    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape' || modal.classList.contains('hidden')) return;
        if (!exitConfirm.classList.contains('hidden')) { exitConfirm.classList.add('hidden'); exitConfirm.classList.remove('flex'); }
        else requestExit();
    });

    var path = window.location.pathname.replace(/\/$/, '') || '/dashboard';
    var current = path === '/dashboard' || path === '/' || path === '/analytics' || path === '/reports'
        ? tutorials.find(function (item) { return item.id === 'performance'; })
        : path.indexOf('/students') === 0 ? tutorials[0]
        : path.indexOf('/media') === 0 ? tutorials[1]
        : path.indexOf('/lessons') === 0 ? tutorials[2]
        : path.indexOf('/settings') === 0 ? tutorials[3] : null;
    var snoozedUntil = current ? Number(localStorage.getItem(prefix + 'snoozed_until_' + current.id) || 0) : 0;
    if (current && localStorage.getItem(prefix + 'disabled_' + current.id) !== '1' && snoozedUntil <= Date.now()) {
        if (snoozedUntil) localStorage.removeItem(prefix + 'snoozed_until_' + current.id);
        showLibrary();
        playTutorial(current, true);
    }
})();
</script>
</body>
</html>
