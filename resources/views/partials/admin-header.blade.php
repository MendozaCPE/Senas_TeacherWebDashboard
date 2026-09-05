<header class="h-20 px-12 flex items-center justify-between flex-shrink-0 bg-[#f4f7f9] border-b border-slate-100 relative z-30">

    <!-- Left: Page identity -->
    <div class="flex items-center gap-3">
        <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0"
             style="background: linear-gradient(135deg,#0d326b,#1a6fd4)">
            <span class="material-symbols-outlined text-white text-[17px]">admin_panel_settings</span>
        </div>
        <div>
            <p class="text-[10px] font-black uppercase tracking-[0.2em] text-slate-400 leading-none mb-0.5">Administration</p>
            <p class="text-[15px] font-bold text-[#0d326b] leading-none">@yield('title', 'Dashboard')</p>
        </div>
    </div>

    <!-- Right: Admin avatar + name (matches teacher header style) -->
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.dashboard') }}"
           class="flex items-center gap-2.5 pl-1 pr-3 py-1 rounded-full transition-colors group"
           style="background: rgba(13,50,107,0.05);">
            <img src="{{ Auth::user()->avatarUrl() }}"
                 class="w-8 h-8 rounded-full border-2 border-[#0d326b]/20 object-cover flex-shrink-0"
                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->name) }}&background=0d326b&color=fff&size=64&bold=true&rounded=true'">
            <span class="text-[13px] font-bold text-[#0d326b] leading-none">
                {{ Auth::user()->first_name ?? explode(' ', Auth::user()->name)[0] }}
            </span>
        </a>
    </div>

</header>
