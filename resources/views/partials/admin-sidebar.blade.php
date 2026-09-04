@php
    $activeClass   = 'text-[#0d326b] font-bold text-[15px] rounded-r-full mr-4 relative bg-gradient-to-r from-[#fbbf24] to-[#facc15]';
    $inactiveClass = 'text-white/70 hover:text-white hover:bg-white/10 transition-colors text-[15px] font-medium rounded-r-full mr-4';
@endphp

<aside class="w-64 flex flex-col z-10 flex-shrink-0"
       style="background: linear-gradient(180deg, #0d326b 0%, #0a2a5c 100%); border-radius: 0px 10px 10px 0px;">

    <!-- Logo -->
    <div class="px-8 pt-10 pb-8">
        <h1 class="text-[40px] font-black text-white tracking-tight drop-shadow-md mb-1 leading-none">SEÑAS</h1>
        <div class="flex items-center gap-2 mt-1">
            <span class="px-2 py-0.5 rounded-full text-[9px] font-black uppercase tracking-[0.15em] bg-[#facc15] text-[#0d326b]">Admin Portal</span>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 flex flex-col space-y-1">
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center space-x-4 px-6 py-4 {{ request()->is('admin/dashboard') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->is('admin/dashboard') ? '' : 'icon-outline' }} text-[22px]">grid_view</span>
            <span>Dashboard</span>
        </a>

        <a href="{{ route('admin.lesson-templates.index') }}"
           class="flex items-center space-x-4 px-6 py-4 {{ request()->routeIs('admin.lesson-templates*') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->routeIs('admin.lesson-templates*') ? '' : 'icon-outline' }} text-[22px]">auto_stories</span>
            <span>Default Lessons</span>
        </a>

        <a href="{{ route('admin.media.index') }}"
           class="flex items-center space-x-4 px-6 py-4 {{ request()->routeIs('admin.media*') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->routeIs('admin.media*') ? '' : 'icon-outline' }} text-[22px]">perm_media</span>
            <span>System Media</span>
        </a>

        <a href="{{ route('admin.analytics') }}"
           class="flex items-center space-x-4 px-6 py-4 {{ request()->is('admin/analytics') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->is('admin/analytics') ? '' : 'icon-outline' }} text-[22px]">bar_chart</span>
            <span>Analytics</span>
        </a>

        <a href="{{ route('admin.ratings') }}"
           class="flex items-center space-x-4 px-6 py-4 {{ request()->is('admin/ratings') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->is('admin/ratings') ? '' : 'icon-outline' }} text-[22px]">star</span>
            <span>Ratings</span>
            @php
                $pendingRatingsCount = \App\Models\TeacherRating::where('is_approved', false)->count()
                    + \App\Models\StudentRating::where('is_approved', false)->count();
            @endphp
            @if($pendingRatingsCount > 0)
            <span class="ml-auto mr-2 min-w-[20px] h-5 px-1.5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                {{ $pendingRatingsCount > 99 ? '99+' : $pendingRatingsCount }}
            </span>
            @endif
        </a>

        <a href="{{ route('admin.reports') }}"
           class="flex items-center space-x-4 px-6 py-4 {{ request()->is('admin/reports') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->is('admin/reports') ? '' : 'icon-outline' }} text-[22px]">inbox</span>
            <span>Reports</span>
            @php
                $pendingCount = \App\Models\HelpRequest::where('status','pending')->count();
            @endphp
            @if($pendingCount > 0)
            <span class="ml-auto mr-2 min-w-[20px] h-5 px-1.5 bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center">
                {{ $pendingCount > 99 ? '99+' : $pendingCount }}
            </span>
            @endif
        </a>

        <a href="{{ route('admin.accounts') }}"
           class="flex items-center space-x-4 px-6 py-4 {{ request()->is('admin/accounts') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->is('admin/accounts') ? '' : 'icon-outline' }} text-[22px]">manage_accounts</span>
            <span>Account Management</span>
        </a>

        <a href="{{ route('admin.audit-logs') }}"
           class="flex items-center space-x-4 px-6 py-4 {{ request()->is('admin/audit-logs') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->is('admin/audit-logs') ? '' : 'icon-outline' }} text-[22px]">fact_check</span>
            <span>Audit Logs</span>
        </a>

        <div class="mx-6 my-2 border-t border-white/10"></div>

        <button type="button" onclick="openAdminLogoutModal()"
                class="w-full flex items-center space-x-4 px-6 py-4 {{ $inactiveClass }}">
            <span class="material-symbols-outlined icon-outline text-[22px]">logout</span>
            <span>Logout</span>
        </button>
    </nav>

    <!-- User Profile -->
    <div class="px-6 mb-8 mt-4">
        <div class="flex items-center space-x-4 bg-white/10 hover:bg-white/20 px-4 py-3.5 rounded-[24px] shadow-sm transition-all duration-200 group">
            <img src="{{ Auth::user()->avatarUrl() }}"
                 alt="Profile Photo"
                 class="w-10 h-10 rounded-full border-2 border-white/30 shadow-sm object-cover flex-shrink-0"
                 style="width:40px;height:40px;object-fit:cover;"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0d326b&color=fff&size=64&bold=true&rounded=true'">
            <div class="flex-1 overflow-hidden">
                <p class="text-[13px] font-bold text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-[11px] font-medium text-white/60 truncate">Administrator</p>
            </div>
            <span class="material-symbols-outlined text-white/40 group-hover:text-white/70 text-[16px] transition-colors">admin_panel_settings</span>
        </div>
    </div>
</aside>

{{-- Logout Modal --}}
<div id="adminLogoutModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-[2px] z-[999] hidden flex items-center justify-center opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-3xl p-8 max-w-sm w-full mx-4 shadow-2xl relative transform scale-95 transition-transform duration-300" id="adminLogoutModalBox">
        <div class="text-center mb-6">
            <div class="w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4"
                 style="background: linear-gradient(135deg, #dbeafe 0%, #bfdbfe 100%);">
                <span class="material-symbols-outlined text-[#0d326b] text-3xl">logout</span>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Confirm Logout</h3>
            <p class="text-slate-500 text-sm leading-relaxed">Are you sure you want to logout?</p>
        </div>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <div class="flex gap-3">
                <button type="button" onclick="closeAdminLogoutModal()"
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
function openAdminLogoutModal() {
    const modal = document.getElementById('adminLogoutModal');
    const box   = document.getElementById('adminLogoutModalBox');
    modal.classList.remove('hidden');
    requestAnimationFrame(() => { modal.classList.remove('opacity-0'); box.classList.remove('scale-95'); });
}
function closeAdminLogoutModal() {
    const modal = document.getElementById('adminLogoutModal');
    const box   = document.getElementById('adminLogoutModalBox');
    modal.classList.add('opacity-0'); box.classList.add('scale-95');
    setTimeout(() => modal.classList.add('hidden'), 300);
}
document.getElementById('adminLogoutModal')?.addEventListener('click', function(e) {
    if (e.target === this) closeAdminLogoutModal();
});

// ── SESSION GUARD ─────────────────────────────────────────────────────────
// Force hard reload when the browser restores this page from bfcache.
window.addEventListener('pageshow', function(event) {
    if (event.persisted) {
        window.location.reload();
    }
});

// Periodic server ping (every 60 s). Redirect to login if session expires.
(function startSessionWatch() {
    setInterval(function() {
        fetch('/admin/dashboard', {
            method: 'HEAD',
            credentials: 'same-origin',
            cache: 'no-store',
        }).then(function(res) {
            if (res.status === 401 || res.status === 403 || res.redirected) {
                window.location.replace('/login');
            }
        }).catch(function() {});
    }, 60000);
})();
</script>