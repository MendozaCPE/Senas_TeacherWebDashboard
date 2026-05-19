<header class="h-24 px-12 flex items-center justify-between flex-shrink-0 mt-2">
    <!-- Search -->
    <div class="relative w-[450px]">
        <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
            <span class="material-symbols-outlined icon-outline text-[22px]">search</span>
        </span>
        <input type="text" placeholder="Search student records or lessons..." class="w-full bg-[#eef2f6] border-none rounded-full py-3.5 pl-12 pr-4 text-[14px] focus:ring-2 focus:ring-[#0d326b]/20 transition-all text-slate-700 outline-none placeholder:text-slate-500 font-medium"/>
    </div>

    <!-- Right controls -->
    <div class="flex items-center space-x-6">
        <button class="text-slate-400 hover:text-[#0d326b] transition-colors relative">
            <span class="material-symbols-outlined icon-outline text-[26px]">notifications</span>
            <span class="absolute top-1 right-1 w-2.5 h-2.5 bg-transparent rounded-full border-2 border-slate-400"></span>
        </button>
        <div class="h-8 border-l border-slate-200"></div>
        <div class="text-[15px] font-semibold">
            <span class="text-[#0d326b]">@yield('title')</span>
        </div>
    </div>
</header>
