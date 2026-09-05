@php
    $activeClass = 'text-[#0d326b] font-bold text-[15px] rounded-r-full mr-4 relative bg-gradient-to-r from-[#fbbf24] to-[#facc15]';
    $inactiveClass = 'text-white/70 hover:text-white hover:bg-white/10 transition-colors text-[15px] font-medium rounded-r-full mr-4';
@endphp
<aside class="w-64 flex flex-col z-10 flex-shrink-0" 
       style="background: linear-gradient(180deg, #0d326b 0%, #0a2a5c 100%); border-radius: 0px 10px 10px 0px;">
    <!-- Logo -->
    <div class="px-8 pt-10 pb-12">
        <h1 class="text-[40px] font-black text-white tracking-tight drop-shadow-md mb-1 leading-none">SEÑAS</h1>
        <p class="text-[11px] font-bold text-white/50 tracking-[0.2em] uppercase">Teacher Portal</p>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 flex flex-col space-y-2">
        <a href="/dashboard" class="flex items-center space-x-4 px-6 py-4 {{ request()->is('/') || request()->is('dashboard') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->is('/') || request()->is('dashboard') ? '' : 'icon-outline' }} text-[22px]">grid_view</span>
            <span>Dashboard</span>
        </a>
        <a href="/students" class="flex items-center space-x-4 px-6 py-4 {{ request()->is('students') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->is('students') ? '' : 'icon-outline' }} text-[22px]">group</span>
            <span>Students</span>
        </a>
        <a href="/lessons" class="flex items-center space-x-4 px-6 py-4 {{ request()->is('lessons') || request()->is('lessons/*') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->is('lessons') || request()->is('lessons/*') ? '' : 'icon-outline' }} text-[22px]">menu_book</span>
            <span>Lessons</span>
        </a>
        <a href="/media" class="flex items-center space-x-4 px-6 py-4 {{ request()->is('media') || request()->is('media/*') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->is('media') || request()->is('media/*') ? '' : 'icon-outline' }} text-[22px]">perm_media</span>
            <span>Media</span>
        </a>
        <a href="/analytics" class="flex items-center space-x-4 px-6 py-4 {{ request()->is('analytics') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->is('analytics') ? '' : 'icon-outline' }} text-[22px]">bar_chart</span>
            <span>Analytics</span>
        </a>
        <a href="/reports" class="flex items-center space-x-4 px-6 py-4 {{ request()->is('reports') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->is('reports') ? '' : 'icon-outline' }} text-[22px]">description</span>
            <span>Reports</span>
        </a>
        <a href="/settings" class="flex items-center space-x-4 px-6 py-4 mt-4 {{ request()->is('settings') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->is('settings') ? '' : 'icon-outline' }} text-[22px]">settings</span>
            <span>Settings</span>
        </a>

        <div class="mx-6 my-2 border-t border-white/10"></div>

        <button type="button" onclick="openLogoutModal()" class="w-full flex items-center space-x-4 px-6 py-4 {{ $inactiveClass }}">
            <span class="material-symbols-outlined icon-outline text-[22px]">logout</span>
            <span>Logout</span>
        </button>
    </nav>

    <!-- User Profile -->
    <div class="px-6 mb-8 mt-4">
        <a href="{{ route('settings') }}" class="flex items-center space-x-4 bg-white/10 hover:bg-white/20 px-4 py-3.5 rounded-[24px] shadow-sm transition-all duration-200 group">
            <img src="{{ Auth::user()->avatarUrl() }}"
                 alt="Profile Photo"
                 class="w-10 h-10 rounded-full border-2 border-white/30 shadow-sm object-cover flex-shrink-0"
                 style="width:40px;height:40px;object-fit:cover;"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0d326b&color=fff&size=64&bold=true&rounded=true'">
            <div class="flex-1 overflow-hidden">
                <p class="text-[13px] font-bold text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-[11px] font-medium text-white/60 truncate">{{ Auth::user()->teacher->specialization ?? 'Teacher' }}</p>
            </div>
            <span class="material-symbols-outlined text-white/40 group-hover:text-white/70 text-[16px] transition-colors">settings</span>
        </a>
    </div>
</aside>

{{-- ── LOGOUT CONFIRMATION MODAL ────────────────────────────────────────── --}}
<div id="logoutModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-[2px] z-[999] hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-3xl p-8 max-w-sm w-full mx-4 shadow-2xl relative transform scale-95 transition-transform duration-300" id="logoutModalBox">
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4" style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);">
                <span class="material-symbols-outlined text-[#0d326b] text-3xl">logout</span>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Confirm Logout</h3>
            <p class="text-slate-500 text-sm leading-relaxed">
                Are you sure you want to logout? You will be redirected to the login page.
            </p>
        </div>
        <form method="POST" action="{{ route('logout') }}" id="logoutForm">
            @csrf
            <div class="flex gap-3">
                <button type="button" onclick="closeLogoutModal()"
                        class="flex-1 py-3 border border-slate-200 rounded-2xl text-slate-600 font-semibold hover:bg-slate-50 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 py-3 text-white font-semibold rounded-2xl transition-colors"
                        style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 100%);">
                    Yes, Logout
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function openLogoutModal() {
        const modal = document.getElementById('logoutModal');
        const box = document.getElementById('logoutModalBox');
        modal.classList.remove('hidden');
        requestAnimationFrame(() => {
            modal.classList.remove('opacity-0');
            box.classList.remove('scale-95');
        });
    }
    function closeLogoutModal() {
        const modal = document.getElementById('logoutModal');
        const box = document.getElementById('logoutModalBox');
        modal.classList.add('opacity-0');
        box.classList.add('scale-95');
        setTimeout(() => modal.classList.add('hidden'), 300);
    }
    document.getElementById('logoutModal').addEventListener('click', function(e) {
        if (e.target === this) closeLogoutModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeLogoutModal();
    });

    // ── SESSION GUARD ─────────────────────────────────────────────────────────
    // 1. Force hard reload when the browser restores this page from the
    //    back/forward cache (bfcache). The server will then check auth and
    //    redirect to /login if the session is gone.
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            window.location.reload();
        }
    });

    // 2. Periodic server ping (every 60 s). If the server returns 401/403
    //    (session expired / unauthenticated), redirect immediately to login.
    (function startSessionWatch() {
        setInterval(function() {
            fetch('/dashboard', {
                method: 'HEAD',
                credentials: 'same-origin',
                cache: 'no-store',
            }).then(function(res) {
                if (res.status === 401 || res.status === 403 || res.redirected) {
                    window.location.replace('/login');
                }
            }).catch(function() {
                // Network error – ignore silently
            });
        }, 60000);
    })();
</script>