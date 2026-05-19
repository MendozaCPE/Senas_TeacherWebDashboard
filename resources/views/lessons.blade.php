<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SEÑAS Teacher Portal - Lessons</title>
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
        .material-symbols-outlined {
            font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24;
        }
        .material-symbols-outlined.icon-outline {
            font-variation-settings: 'FILL' 0;
        }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
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
            <a href="/" class="flex items-center space-x-4 px-6 py-4 text-slate-500 hover:text-[#0d326b] hover:bg-white/50 transition-colors text-[15px] font-medium border-l-[6px] border-transparent">
                <span class="material-symbols-outlined icon-outline text-[22px]">grid_view</span>
                <span>Dashboard</span>
            </a>
            <a href="/students" class="flex items-center space-x-4 px-6 py-4 text-slate-500 hover:text-[#0d326b] hover:bg-white/50 transition-colors text-[15px] font-medium border-l-[6px] border-transparent">
                <span class="material-symbols-outlined icon-outline text-[22px]">group</span>
                <span>Students</span>
            </a>
            <a href="/lessons" class="flex items-center space-x-4 px-6 py-4 bg-white text-[#0d326b] font-bold text-[15px] border-l-[6px] border-[#facc15] shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                <span class="material-symbols-outlined text-[22px]">menu_book</span>
                <span>Lessons</span>
            </a>
            <a href="#" class="flex items-center space-x-4 px-6 py-4 text-slate-500 hover:text-[#0d326b] hover:bg-white/50 transition-colors text-[15px] font-medium border-l-[6px] border-transparent">
                <span class="material-symbols-outlined icon-outline text-[22px]">bar_chart</span>
                <span>Analytics</span>
            </a>
            <a href="#" class="flex items-center space-x-4 px-6 py-4 text-slate-500 hover:text-[#0d326b] hover:bg-white/50 transition-colors text-[15px] font-medium border-l-[6px] border-transparent">
                <span class="material-symbols-outlined icon-outline text-[22px]">description</span>
                <span>Reports</span>
            </a>
            <a href="#" class="flex items-center space-x-4 px-6 py-4 mt-4 text-slate-500 hover:text-[#0d326b] hover:bg-white/50 transition-colors text-[15px] font-medium border-l-[6px] border-transparent">
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
                    <span class="text-[#0d326b]">Lessons</span>
                </div>
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto px-12 pb-12">
            
            <!-- Header Section -->
            <div class="flex items-start justify-between mb-8">
                <div>
                    <h3 class="text-[11px] font-bold text-[#0d326b] tracking-[0.15em] uppercase mb-2">LESSONS MANAGEMENT</h3>
                    <h2 class="text-[36px] font-medium text-[#0d326b] leading-tight">Lessons</h2>
                </div>
                
                <button class="bg-[#0d326b] hover:bg-[#154188] text-white px-6 py-3 rounded-xl text-[14px] font-semibold transition-colors flex items-center space-x-2 shadow-sm">
                    <span class="material-symbols-outlined icon-outline text-[20px]">add</span>
                    <span>New Lesson</span>
                </button>
            </div>

            <div class="flex flex-col lg:flex-row gap-8">
                
                <!-- Left Side: Modules List -->
                <div class="flex-1 flex flex-col space-y-12">
                    
                    <!-- Module 1 -->
                    <div>
                        <div class="w-32 bg-[#0d326b] text-white text-[10px] font-bold tracking-[0.15em] px-4 py-2.5 uppercase inline-block" style="clip-path: polygon(0 0, 100% 0, 85% 100%, 0% 100%);">
                            MODULE 01
                        </div>
                        <div class="bg-white rounded-[24px] rounded-tl-none shadow-sm p-8 border border-slate-100">
                            <!-- Module Header -->
                            <div class="flex justify-between items-start mb-10">
                                <div class="flex items-center space-x-5">
                                    <div class="w-16 h-16 bg-[#e0e7ff] text-[#0d326b] rounded-2xl flex items-center justify-center font-black text-lg">ABC</div>
                                    <div>
                                        <h3 class="text-[20px] font-bold text-[#0d326b] mb-1">Alphabet & Fingerspelling</h3>
                                        <p class="text-[13px] text-slate-500 font-medium">Fundamental hand shapes and movement sequences.</p>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-4">
                                    <div class="text-right">
                                        <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-0.5">Progress</p>
                                        <p class="text-[18px] font-bold text-[#0d326b]">88%</p>
                                    </div>
                                    <button class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center hover:bg-slate-200 transition-colors text-slate-600">
                                        <span class="material-symbols-outlined icon-outline">expand_more</span>
                                    </button>
                                </div>
                            </div>

                            <!-- Lessons Table -->
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="border-b border-slate-100">
                                        <th class="py-4 px-2 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase w-8"></th>
                                        <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase">Lesson Title</th>
                                        <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase">Difficulty</th>
                                        <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase">Completed</th>
                                        <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase text-right">Avg Score</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50">
                                    <tr class="hover:bg-slate-50 transition-colors group">
                                        <td class="py-5 px-2 text-slate-300 cursor-move">
                                            <span class="material-symbols-outlined icon-outline text-lg">drag_indicator</span>
                                        </td>
                                        <td class="py-5 px-4 font-bold text-[#0d326b] text-[14px]">Alphabets</td>
                                        <td class="py-5 px-4">
                                            <span class="px-3 py-1 bg-[#dcfce7] text-[#166534] text-[9px] font-bold rounded uppercase tracking-widest">Basic</span>
                                        </td>
                                        <td class="py-5 px-4">
                                            <p class="text-[13px] font-medium text-slate-600">10 / 10</p>
                                            <p class="text-[10px] text-slate-400">Students</p>
                                        </td>
                                        <td class="py-5 px-4 text-right">
                                            <span class="text-[15px] font-bold text-[#0d326b]">92%</span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-slate-50 transition-colors group">
                                        <td class="py-5 px-2 text-slate-300 cursor-move">
                                            <span class="material-symbols-outlined icon-outline text-lg">drag_indicator</span>
                                        </td>
                                        <td class="py-5 px-4 font-bold text-[#0d326b] text-[14px]">Consonants</td>
                                        <td class="py-5 px-4">
                                            <span class="px-3 py-1 bg-[#dcfce7] text-[#166534] text-[9px] font-bold rounded uppercase tracking-widest">Basic</span>
                                        </td>
                                        <td class="py-5 px-4">
                                            <p class="text-[13px] font-medium text-slate-600">8 / 10</p>
                                            <p class="text-[10px] text-slate-400">Students</p>
                                        </td>
                                        <td class="py-5 px-4 text-right">
                                            <span class="text-[15px] font-bold text-[#0d326b]">85%</span>
                                        </td>
                                    </tr>
                                    <tr class="hover:bg-slate-50 transition-colors group">
                                        <td class="py-5 px-2 text-slate-300 cursor-move">
                                            <span class="material-symbols-outlined icon-outline text-lg">drag_indicator</span>
                                        </td>
                                        <td class="py-5 px-4 font-bold text-[#0d326b] text-[14px]">Dynamic Letters</td>
                                        <td class="py-5 px-4">
                                            <span class="px-3 py-1 bg-[#fef08a] text-[#854d0e] text-[9px] font-bold rounded uppercase tracking-widest">Medium</span>
                                        </td>
                                        <td class="py-5 px-4">
                                            <p class="text-[13px] font-medium text-slate-600">8 / 10</p>
                                            <p class="text-[10px] text-slate-400">Students</p>
                                        </td>
                                        <td class="py-5 px-4 text-right">
                                            <span class="text-[15px] font-bold text-[#0d326b]">78%</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <div class="mt-6 pt-6 border-t border-slate-100">
                                <button class="flex items-center space-x-2 text-[#0d326b] font-bold text-[13px] hover:underline">
                                    <span class="material-symbols-outlined icon-outline text-[18px]">sort</span>
                                    <span>Reorder All Lessons</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Module 2 -->
                    <div class="opacity-75">
                        <div class="w-32 bg-[#e2e8f0] text-slate-600 text-[10px] font-bold tracking-[0.15em] px-4 py-2.5 uppercase inline-block" style="clip-path: polygon(0 0, 100% 0, 85% 100%, 0% 100%);">
                            MODULE 02
                        </div>
                        <div class="bg-[#f1f5f9] rounded-[24px] rounded-tl-none p-8 border border-slate-100 shadow-inner">
                            <div class="flex justify-between items-start">
                                <div class="flex items-center space-x-5">
                                    <div class="w-16 h-16 bg-[#cbd5e1] text-slate-600 rounded-2xl flex items-center justify-center font-black text-lg">
                                        <span class="material-symbols-outlined text-3xl">format_list_numbered</span>
                                    </div>
                                    <div>
                                        <h3 class="text-[20px] font-bold text-[#0d326b] mb-1">Numerical Systems 1-100</h3>
                                        <p class="text-[13px] text-slate-500 font-medium">Use numbers in everyday situations, like counting items and showing order.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Right Side: Resources -->
                <div class="w-[340px] flex-shrink-0 flex flex-col space-y-8">
                    
                    <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)] border border-slate-50">
                        <div class="flex items-center space-x-2 mb-6">
                            <span class="material-symbols-outlined icon-outline text-[20px] text-[#0d326b]">auto_awesome</span>
                            <span class="text-[15px] font-bold text-[#0d326b]">Lesson Insights</span>
                        </div>
                        
                        <div class="bg-[#facc15] rounded-[20px] p-5 shadow-sm relative overflow-hidden mb-8">
                            <div class="flex items-center space-x-2 mb-3 relative z-10">
                                <span class="material-symbols-outlined icon-outline text-[16px] text-black">trending_down</span>
                                <h4 class="text-[13px] font-black text-black">Difficulty Alert</h4>
                            </div>
                            <p class="text-[12px] text-black/80 font-medium leading-relaxed relative z-10">
                                Students scored lower in the lesson "Dynamic Letters" compared to Module 01. You can add extra practice to help them improve.
                            </p>
                        </div>

                        <div>
                            <h4 class="text-[10px] font-bold tracking-[0.15em] uppercase text-slate-400 mb-4">Module Resources</h4>
                            
                            <button class="w-full flex items-center justify-between bg-[#f1f5f9] hover:bg-[#e2e8f0] transition-colors p-4 rounded-2xl group">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-white rounded-lg shadow-sm flex items-center justify-center text-[#0d326b]">
                                        <span class="material-symbols-outlined icon-outline text-[16px]">picture_as_pdf</span>
                                    </div>
                                    <span class="text-[13px] font-bold text-[#0d326b]">Printable Flashcards</span>
                                </div>
                                <span class="material-symbols-outlined icon-outline text-[18px] text-slate-400 group-hover:text-[#0d326b]">download</span>
                            </button>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>

</body>
</html>
