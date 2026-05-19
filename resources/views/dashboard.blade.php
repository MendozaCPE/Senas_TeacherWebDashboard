<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SEÑAS Teacher Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet"/>
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
                            yellow: '#facc15', // vibrant yellow
                            bg: '#f4f7f9',
                            card: '#ffffff'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined.icon-outline {
            font-variation-settings: 'FILL' 0;
        }
        /* Custom scrollbar for webkit */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: transparent;
        }
        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body class="text-slate-800 font-sans antialiased flex h-screen overflow-hidden bg-brand-bg">

    <!-- Sidebar -->
    <aside class="w-64 bg-[#f8f9fa] flex flex-col shadow-[2px_0_15px_rgba(0,0,0,0.03)] z-10 flex-shrink-0">
        <!-- Logo -->
        <div class="px-8 pt-10 pb-12">
            <h1 class="text-[40px] font-black text-[#0d326b] tracking-tight drop-shadow-md mb-1 leading-none">SEÑAS</h1>
            <p class="text-[11px] font-bold text-[#64748b] tracking-[0.2em] uppercase">Teacher Portal</p>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 flex flex-col space-y-2">
            <a href="/" class="flex items-center space-x-4 px-6 py-4 bg-white text-[#0d326b] font-bold text-[15px] border-l-[6px] border-[#facc15] shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                <span class="material-symbols-outlined text-[22px]">grid_view</span>
                <span>Dashboard</span>
            </a>
            <a href="/students" class="flex items-center space-x-4 px-6 py-4 text-slate-500 hover:text-[#0d326b] hover:bg-white/50 transition-colors text-[15px] font-medium border-l-[6px] border-transparent">
                <span class="material-symbols-outlined icon-outline text-[22px]">group</span>
                <span>Students</span>
            </a>
            <a href="/lessons" class="flex items-center space-x-4 px-6 py-4 text-slate-500 hover:text-[#0d326b] hover:bg-white/50 transition-colors text-[15px] font-medium border-l-[6px] border-transparent">
                <span class="material-symbols-outlined icon-outline text-[22px]">menu_book</span>
                <span>Lessons</span>
            </a>
            <a href="/analytics" class="flex items-center space-x-4 px-6 py-4 text-slate-500 hover:text-[#0d326b] hover:bg-white/50 transition-colors text-[15px] font-medium border-l-[6px] border-transparent">
                <span class="material-symbols-outlined icon-outline text-[22px]">bar_chart</span>
                <span>Analytics</span>
            </a>
            <a href="/reports" class="flex items-center space-x-4 px-6 py-4 text-slate-500 hover:text-[#0d326b] hover:bg-white/50 transition-colors text-[15px] font-medium border-l-[6px] border-transparent">
                <span class="material-symbols-outlined icon-outline text-[22px]">description</span>
                <span>Reports</span>
            </a>
            <a href="/settings" class="flex items-center space-x-4 px-6 py-4 mt-4 text-slate-500 hover:text-[#0d326b] hover:bg-white/50 transition-colors text-[15px] font-medium border-l-[6px] border-transparent">
                <span class="material-symbols-outlined icon-outline text-[22px]">settings</span>
                <span>Settings</span>
            </a>
            <a href="#" class="flex items-center space-x-4 px-6 py-4 text-slate-500 hover:text-[#0d326b] hover:bg-white/50 transition-colors text-[15px] font-medium border-l-[6px] border-transparent">
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

    <!-- Main Layout Area -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden">
        
        <!-- Header Topbar -->
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
                    <span class="text-[#0d326b]">Dashboard</span>
                    <span class="text-slate-300 mx-2">/</span>
                    <span class="text-slate-500 font-medium">Overview</span>
                </div>
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto px-10 pb-10">
            <div class="flex flex-col lg:flex-row gap-8">
                
                <!-- Left/Center Content -->
                <div class="flex-1 flex flex-col space-y-8">
                    
                    <!-- Welcome & Stats -->
                    <div>
                        <div class="mb-8 pl-2">
                            <h2 class="text-[32px] font-bold text-[#0d326b] mb-1">Welcome back, Ms. Mila</h2>
                            <p class="text-[15px] text-slate-500 font-medium tracking-wide">Class Summary</p>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-8">
                            <!-- Card 1 -->
                            <div class="bg-white rounded-[24px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex flex-col justify-between min-h-[160px]">
                                <h3 class="text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-4 leading-relaxed">Total<br>Students</h3>
                                <div>
                                    <p class="text-[40px] font-normal text-[#0d326b] leading-none mb-4">10</p>
                                    <div class="w-[90%] h-2 bg-[#0d326b] rounded-full"></div>
                                </div>
                            </div>
                            <!-- Card 2 -->
                            <div class="bg-white rounded-[24px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex flex-col justify-between min-h-[160px]">
                                <h3 class="text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-4 leading-relaxed">Students Active<br>Today</h3>
                                <div>
                                    <p class="text-[40px] font-normal text-[#6366f1] leading-none mb-4">8</p>
                                    <div class="w-[90%] h-2 bg-[#6366f1] rounded-full"></div>
                                </div>
                            </div>
                            <!-- Card 3 -->
                            <div class="bg-white rounded-[24px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex flex-col justify-between min-h-[160px]">
                                <h3 class="text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-4 leading-relaxed">Average<br>Performance</h3>
                                <div>
                                    <div class="flex items-baseline space-x-3 mb-4">
                                        <p class="text-[40px] font-normal text-[#857a26] leading-none">84%</p>
                                        <p class="text-[13px] font-bold text-emerald-500 flex items-center"><span class="material-symbols-outlined icon-outline text-[16px] mr-0.5">arrow_upward</span>3%</p>
                                    </div>
                                    <div class="w-[90%] h-2 bg-[#a39423] rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Your Lessons -->
                    <div class="mt-10">
                        <div class="flex justify-between items-end mb-6 pl-2">
                            <h3 class="text-[22px] font-bold text-[#0d326b]">Your Lessons</h3>
                            <a href="#" class="text-[14px] font-bold text-[#0d326b] hover:underline pr-2">Manage Lessons</a>
                        </div>
                        <div class="grid grid-cols-3 gap-8">
                            <!-- Lesson 1 -->
                            <div class="bg-white rounded-[32px] p-6 shadow-sm border-[3px] border-[#facc15] relative min-h-[220px] flex flex-col overflow-hidden">
                                <div class="absolute top-0 right-0 w-32 h-32 bg-[#facc15] opacity-5 rounded-bl-full"></div>
                                <div class="w-[52px] h-[42px] bg-[#1e40af] text-white rounded-lg rounded-tr-xl rounded-bl-sm flex items-center justify-center font-bold mb-6 relative shadow-sm">
                                    <div class="absolute -top-[8px] left-0 w-8 h-[10px] bg-[#1e40af] rounded-t-md"></div>
                                    <span class="border border-white/40 rounded px-1.5 py-0.5 text-sm">A</span>
                                </div>
                                <h4 class="font-bold text-[#0d326b] text-[18px] mb-2">Alphabet</h4>
                                <div class="flex justify-between items-center mb-6">
                                    <p class="text-[12px] font-medium text-slate-500 leading-tight w-16">8 Students<br>Enrolled</p>
                                    <span class="text-[13px] font-bold text-[#0d326b]">80%</span>
                                </div>
                                
                                <div class="mt-auto relative z-10">
                                    <div class="w-[90%] h-2.5 bg-[#fef08a] rounded-full overflow-hidden mb-5">
                                        <div class="h-full bg-[#facc15] rounded-full" style="width: 80%"></div>
                                    </div>
                                    <div class="flex -space-x-2.5">
                                        <img src="https://ui-avatars.com/api/?name=St+1&background=random&color=fff&rounded=true" class="w-8 h-8 rounded-full border-2 border-white shadow-sm"/>
                                        <img src="https://ui-avatars.com/api/?name=St+2&background=random&color=fff&rounded=true" class="w-8 h-8 rounded-full border-2 border-white shadow-sm"/>
                                        <img src="https://ui-avatars.com/api/?name=St+3&background=random&color=fff&rounded=true" class="w-8 h-8 rounded-full border-2 border-white shadow-sm"/>
                                        <div class="w-8 h-8 rounded-full border-2 border-white bg-[#f1f5f9] flex items-center justify-center text-[9px] font-bold text-slate-500 shadow-sm">+25</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Lesson 2 -->
                            <div class="bg-white rounded-[32px] p-6 shadow-[0_4px_20px_rgba(0,0,0,0.03)] min-h-[220px] flex flex-col">
                                <div class="w-[52px] h-[42px] bg-gradient-to-br from-[#93c5fd] to-[#3b82f6] text-[#1e40af] rounded-lg rounded-tr-xl rounded-bl-sm flex items-center justify-center font-black text-xl mb-6 relative shadow-sm">
                                    <div class="absolute -top-[8px] left-0 w-8 h-[10px] bg-[#93c5fd] rounded-t-md"></div>
                                    #
                                </div>
                                <h4 class="font-bold text-[#0d326b] text-[18px] mb-2">Numbers</h4>
                                <div class="flex justify-between items-center mb-6">
                                    <p class="text-[12px] font-medium text-slate-500 leading-tight w-16">7 Students<br>Enrolled</p>
                                    <span class="text-[13px] font-bold text-[#3b82f6]">70%</span>
                                </div>
                                
                                <div class="mt-auto">
                                    <div class="w-[90%] h-2.5 bg-[#e2e8f0] rounded-full overflow-hidden mb-5">
                                        <div class="h-full bg-[#474e9c] rounded-full" style="width: 70%"></div>
                                    </div>
                                    <div class="flex -space-x-2.5">
                                        <img src="https://ui-avatars.com/api/?name=St+4&background=random&color=fff&rounded=true" class="w-8 h-8 rounded-full border-2 border-white shadow-sm"/>
                                        <img src="https://ui-avatars.com/api/?name=St+5&background=random&color=fff&rounded=true" class="w-8 h-8 rounded-full border-2 border-white shadow-sm"/>
                                        <div class="w-8 h-8 rounded-full border-2 border-white bg-[#f1f5f9] flex items-center justify-center text-[9px] font-bold text-slate-500 shadow-sm">+30</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Lesson 3 -->
                            <div class="bg-white rounded-[32px] p-6 shadow-[0_4px_20px_rgba(0,0,0,0.03)] min-h-[220px] flex flex-col">
                                <div class="w-[52px] h-[42px] bg-gradient-to-br from-[#bfdbfe] to-[#60a5fa] text-[#1e40af] rounded-lg rounded-tr-xl rounded-bl-sm flex items-center justify-center font-bold mb-6 relative shadow-sm">
                                    <div class="absolute -top-[8px] left-0 w-8 h-[10px] bg-[#bfdbfe] rounded-t-md"></div>
                                    <span class="material-symbols-outlined icon-outline text-[22px]">chat_bubble</span>
                                </div>
                                <h4 class="font-bold text-[#0d326b] text-[18px] mb-2">Basic Words</h4>
                                <div class="flex justify-between items-center mb-6">
                                    <p class="text-[12px] font-medium text-slate-500 leading-tight w-16">4 Students<br>Enrolled</p>
                                    <span class="text-[13px] font-bold text-[#3b82f6]">40%</span>
                                </div>
                                
                                <div class="mt-auto">
                                    <div class="w-[90%] h-2.5 bg-[#e2e8f0] rounded-full overflow-hidden mb-5">
                                        <div class="h-full bg-[#474e9c] rounded-full" style="width: 40%"></div>
                                    </div>
                                    <div class="flex -space-x-2.5">
                                        <img src="https://ui-avatars.com/api/?name=St+6&background=random&color=fff&rounded=true" class="w-8 h-8 rounded-full border-2 border-white shadow-sm"/>
                                        <div class="w-8 h-8 rounded-full border-2 border-white bg-[#f1f5f9] flex items-center justify-center text-[9px] font-bold text-slate-500 shadow-sm">+23</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Middle Widgets -->
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Student Mastery -->
                        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                            <h3 class="text-lg font-bold text-brand-blue mb-6">Student Mastery</h3>
                            
                            <div class="space-y-5">
                                <div>
                                    <div class="flex justify-between text-[10px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider">
                                        <span>Alphabet</span>
                                        <span>92% Completed</span>
                                    </div>
                                    <div class="w-full h-2.5 bg-blue-100 rounded-full overflow-hidden">
                                        <div class="bg-brand-blue h-full rounded-full" style="width: 92%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-[10px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider">
                                        <span>Numbers</span>
                                        <span>85% Completed</span>
                                    </div>
                                    <div class="w-full h-2.5 bg-blue-200 rounded-full overflow-hidden flex">
                                        <div class="bg-brand-blue h-full" style="width: 45%"></div>
                                        <div class="bg-brand-lightBlue h-full" style="width: 40%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-[10px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider">
                                        <span>Basic Words</span>
                                        <span>64% Completed</span>
                                    </div>
                                    <div class="w-full h-2.5 bg-brand-yellow/30 rounded-full overflow-hidden flex">
                                        <div class="bg-brand-blue h-full" style="width: 30%"></div>
                                        <div class="bg-brand-lightBlue h-full" style="width: 34%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-[10px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider">
                                        <span>Sentences</span>
                                        <span>41% Completed</span>
                                    </div>
                                    <div class="w-full h-2.5 bg-slate-100 rounded-full overflow-hidden flex">
                                        <div class="bg-brand-blue h-full" style="width: 15%"></div>
                                        <div class="bg-brand-lightBlue h-full" style="width: 10%"></div>
                                        <div class="bg-brand-yellow h-full" style="width: 16%"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Legend -->
                            <div class="flex items-center space-x-4 mt-8 text-[9px] font-bold text-slate-400 uppercase tracking-wider">
                                <div class="flex items-center space-x-1.5">
                                    <div class="w-2 h-2 rounded-full bg-brand-blue"></div>
                                    <span>Advanced</span>
                                </div>
                                <div class="flex items-center space-x-1.5">
                                    <div class="w-2 h-2 rounded-full bg-brand-lightBlue"></div>
                                    <span>Intermediate</span>
                                </div>
                                <div class="flex items-center space-x-1.5">
                                    <div class="w-2 h-2 rounded-full bg-brand-yellow"></div>
                                    <span>Beginner</span>
                                </div>
                            </div>
                        </div>

                        <!-- Class Rate Circle -->
                        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 flex flex-col items-center justify-center text-center">
                            <div class="relative w-36 h-36 mb-6">
                                <svg class="w-full h-full transform -rotate-90">
                                    <circle cx="72" cy="72" r="64" fill="transparent" stroke="#f1f5f9" stroke-width="8"></circle>
                                    <circle cx="72" cy="72" r="64" fill="transparent" stroke="#facc15" stroke-dasharray="402" stroke-dashoffset="48" stroke-width="8" stroke-linecap="round"></circle>
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    <span class="text-3xl font-bold text-slate-700">88%</span>
                                    <span class="text-[8px] font-bold uppercase tracking-widest text-slate-500 mt-1">Class Rate</span>
                                </div>
                            </div>
                            <p class="text-sm font-medium text-brand-blue px-6">Class Performance is 12% higher compared to last period.</p>
                        </div>
                    </div>

                    <!-- Student Performance Bottom -->
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold text-brand-blue">Student Performance</h3>
                            <div class="flex items-center space-x-3">
                                <span class="material-symbols-outlined text-slate-400">swap_vert</span>
                                <button class="px-4 py-1.5 bg-slate-100 text-brand-blue text-xs font-bold rounded-lg hover:bg-slate-200 transition-colors">View All</button>
                            </div>
                        </div>
                        
                        <div class="space-y-6">
                            <!-- Student 1 -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4 w-1/3">
                                    <img src="https://ui-avatars.com/api/?name=Christian+Mendoza&background=random&color=fff&rounded=true" class="w-10 h-10 rounded-full"/>
                                    <span class="text-sm font-bold text-slate-700">Christian Paul Mendoza</span>
                                </div>
                                <div class="flex-1 px-8">
                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-emerald-500 rounded-full" style="width: 96%"></div>
                                    </div>
                                </div>
                                <div class="w-24 text-right flex items-center justify-end space-x-4">
                                    <span class="text-sm font-bold text-emerald-600">96%</span>
                                    <span class="px-2 py-1 bg-emerald-50 text-emerald-600 text-[9px] font-bold rounded uppercase tracking-wider">High</span>
                                </div>
                            </div>

                            <!-- Student 2 -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4 w-1/3">
                                    <img src="https://ui-avatars.com/api/?name=Jared+Abellera&background=random&color=fff&rounded=true" class="w-10 h-10 rounded-full"/>
                                    <span class="text-sm font-bold text-slate-700">Jared Abellera</span>
                                </div>
                                <div class="flex-1 px-8">
                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-brand-yellow rounded-full" style="width: 72%"></div>
                                    </div>
                                </div>
                                <div class="w-24 text-right flex items-center justify-end space-x-4">
                                    <span class="text-sm font-bold text-yellow-600">72%</span>
                                    <span class="px-2 py-1 bg-yellow-50 text-yellow-600 text-[9px] font-bold rounded uppercase tracking-wider">Average</span>
                                </div>
                            </div>

                            <!-- Student 3 -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4 w-1/3">
                                    <img src="https://ui-avatars.com/api/?name=Danah+Paris&background=random&color=fff&rounded=true" class="w-10 h-10 rounded-full"/>
                                    <span class="text-sm font-bold text-slate-700">Danah Paris</span>
                                </div>
                                <div class="flex-1 px-8">
                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-red-500 rounded-full" style="width: 42%"></div>
                                    </div>
                                </div>
                                <div class="w-24 text-right flex items-center justify-end space-x-4">
                                    <span class="text-sm font-bold text-red-600">42%</span>
                                    <span class="px-2 py-1 bg-red-50 text-red-600 text-[9px] font-bold rounded uppercase tracking-wider">Low</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Sidebar Column -->
                <div class="w-[340px] flex-shrink-0 flex flex-col space-y-8 pl-4">
                    
                    <!-- Senya Tip Widget -->
                    <div class="bg-[#facc15] rounded-[32px] p-8 relative overflow-hidden shadow-sm">
                        <!-- Decorative background element -->
                        <div class="absolute -bottom-16 -right-16 text-[#eab308] opacity-50 transform rotate-45">
                            <svg width="150" height="150" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L15 9L22 12L15 15L12 22L9 15L2 12L9 9L12 2Z"/></svg>
                        </div>
                        
                        <div class="flex items-center space-x-3 mb-6 relative z-10">
                            <div class="w-[42px] h-[42px] bg-white rounded-full flex items-center justify-center shadow-sm overflow-hidden p-1">
                                <img src="https://api.dicebear.com/7.x/bottts/svg?seed=Senya&backgroundColor=ffffff" alt="Senya" class="w-full h-full"/>
                            </div>
                            <span class="text-[11px] font-black uppercase tracking-[0.15em] text-[#1e293b]">Senya Tip</span>
                        </div>
                        
                        <p class="text-[14px] font-bold text-[#1e293b] leading-[1.6] mb-8 relative z-10">
                            3 students are struggling with the 'L' and 'R' hand placements in the Alphabet module.
                        </p>
                        
                        <button class="w-full bg-[#18181b] text-[#facc15] text-[13px] font-bold py-3.5 rounded-xl hover:bg-black transition-colors shadow-sm relative z-10">
                            View Guidance
                        </button>
                    </div>

                    <!-- Needs Attention -->
                    <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                        <h4 class="text-[12px] font-bold tracking-[0.15em] uppercase text-[#0d326b] mb-8">Needs Your Attention</h4>
                        
                        <div class="space-y-6">
                            <!-- Alert 1 -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <img src="https://ui-avatars.com/api/?name=Theresa+Valiente&background=random&color=fff&rounded=true" class="w-10 h-10 rounded-full shadow-sm"/>
                                    <span class="text-[14px] font-bold text-[#1e293b]">Theresa Valiente</span>
                                </div>
                                <span class="px-2.5 py-1 bg-[#fee2e2] text-[#b91c1c] text-[10px] font-black rounded-md uppercase tracking-wider">Alert</span>
                            </div>
                            
                            <!-- Alert 2 -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <img src="https://ui-avatars.com/api/?name=Yeoj+Valdez&background=random&color=fff&rounded=true" class="w-10 h-10 rounded-full shadow-sm"/>
                                    <span class="text-[14px] font-bold text-[#1e293b]">Yeoj Valdez</span>
                                </div>
                                <span class="px-2.5 py-1 bg-[#fef3c7] text-[#b45309] text-[10px] font-black rounded-md uppercase tracking-wider">Review</span>
                            </div>
                        </div>
                    </div>

                    <!-- Insights -->
                    <div class="bg-[#052b61] text-white rounded-[32px] p-8 relative overflow-hidden shadow-sm">
                        <!-- Decorative bg -->
                        <div class="absolute top-4 right-4 opacity-10">
                            <span class="material-symbols-outlined icon-outline text-[80px]">psychology</span>
                        </div>
                        
                        <div class="flex items-center space-x-2 mb-8 relative z-10">
                            <span class="material-symbols-outlined icon-outline text-[16px] text-[#93c5fd]">auto_awesome</span>
                            <span class="text-[11px] font-bold tracking-[0.15em] uppercase text-[#93c5fd]">Insights</span>
                        </div>
                        
                        <div class="border-l-2 border-[#10b981] pl-4 mb-8 relative z-10">
                            <h4 class="text-[15px] font-bold mb-2 text-white">Top Performance: Alphabet Module</h4>
                            <p class="text-[13px] text-[#93c5fd] leading-relaxed">
                                Students did well in this module this week!
                            </p>
                        </div>
                        
                        <button class="w-full bg-[#0d4599] hover:bg-[#1556b3] text-[#93c5fd] text-[13px] font-semibold py-3.5 rounded-xl transition-colors flex items-center justify-center space-x-2 relative z-10 shadow-sm border border-[#2563eb]/30">
                            <span>Generate Full CSV Report</span>
                            <span class="material-symbols-outlined icon-outline text-[16px]">description</span>
                        </button>
                    </div>

                </div>
            </div>
        </main>
    </div>

</body>
</html>