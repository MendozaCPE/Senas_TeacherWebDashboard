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
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="w-full flex items-center space-x-4 px-6 py-4 {{ $inactiveClass }}">
                <span class="material-symbols-outlined icon-outline text-[22px]">logout</span>
                <span>Logout</span>
            </button>
        </form>
    </nav>

    <!-- User Profile -->
    <div class="px-6 mb-8 mt-4">
        <div class="flex items-center space-x-4 bg-white/10 px-4 py-3.5 rounded-[24px] shadow-sm">
            <img src="https://api.dicebear.com/7.x/avataaars/svg?seed={{ Auth::user()->name }}&backgroundColor=e2e8f0" alt="Avatar" class="w-10 h-10 rounded-full border-2 border-white/30 shadow-sm"/>
            <div class="flex-1 overflow-hidden">
                <p class="text-[13px] font-bold text-white truncate">{{ Auth::user()->name }}</p>
                <p class="text-[11px] font-medium text-white/60 truncate">{{ Auth::user()->teacher->specialization ?? 'Teacher' }}</p>
            </div>
        </div>
    </div>
</aside>