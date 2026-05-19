@php
    $activeClass = 'bg-white text-[#0d326b] font-bold text-[15px] border-l-[6px] border-[#facc15] shadow-[0_2px_10px_rgba(0,0,0,0.02)]';
    $inactiveClass = 'text-slate-500 hover:text-[#0d326b] hover:bg-white/50 transition-colors text-[15px] font-medium border-l-[6px] border-transparent';
@endphp
<aside class="w-64 bg-[#f8f9fa] flex flex-col shadow-[2px_0_15px_rgba(0,0,0,0.03)] z-10 flex-shrink-0">
    <!-- Logo -->
    <div class="px-8 pt-10 pb-12">
        <h1 class="text-[40px] font-black text-[#0d326b] tracking-tight drop-shadow-md mb-1 leading-none">SEÑAS</h1>
        <p class="text-[11px] font-bold text-[#64748b] tracking-[0.2em] uppercase">Teacher Portal</p>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 flex flex-col space-y-2">
        <a href="/" class="flex items-center space-x-4 px-6 py-4 {{ request()->is('/') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->is('/') ? '' : 'icon-outline' }} text-[22px]">grid_view</span>
            <span>Dashboard</span>
        </a>
        <a href="/students" class="flex items-center space-x-4 px-6 py-4 {{ request()->is('students') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->is('students') ? '' : 'icon-outline' }} text-[22px]">group</span>
            <span>Students</span>
        </a>
        <a href="/lessons" class="flex items-center space-x-4 px-6 py-4 {{ request()->is('lessons') ? $activeClass : $inactiveClass }}">
            <span class="material-symbols-outlined {{ request()->is('lessons') ? '' : 'icon-outline' }} text-[22px]">menu_book</span>
            <span>Lessons</span>
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
        <a href="#" class="flex items-center space-x-4 px-6 py-4 {{ $inactiveClass }}">
            <span class="material-symbols-outlined icon-outline text-[22px]">logout</span>
            <span>Logout</span>
        </a>
    </nav>

    <!-- User Profile -->
    <div class="px-6 mb-8 mt-4">
        <div class="flex items-center space-x-4 bg-[#f1f5f9] px-4 py-3.5 rounded-[24px] shadow-sm">
            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Mila&backgroundColor=e2e8f0" alt="Ms. Mila" class="w-10 h-10 rounded-full border-2 border-white shadow-sm"/>
            <div class="flex-1">
                <p class="text-[13px] font-bold text-[#0d326b]">Ms. Mila Quintana</p>
                <p class="text-[11px] font-medium text-slate-500">SNED TEacher</p>
            </div>
        </div>
    </div>
</aside>
