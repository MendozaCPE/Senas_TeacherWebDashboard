<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SEÑAS Teacher Portal - Students</title>
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
            <a href="/students" class="flex items-center space-x-4 px-6 py-4 bg-white text-[#0d326b] font-bold text-[15px] border-l-[6px] border-[#facc15] shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                <span class="material-symbols-outlined text-[22px]">group</span>
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
                    <span class="text-[#0d326b]">Students</span>
                </div>
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto px-12 pb-12">
            
            <!-- Header Section -->
            <div class="flex items-start justify-between mb-8">
                <div>
                    <h3 class="text-[11px] font-bold text-[#0d326b] tracking-[0.15em] uppercase mb-2">Overview</h3>
                    <h2 class="text-[36px] font-medium text-[#0d326b] leading-tight">Student Management</h2>
                </div>
                
                <!-- Quick Stats Pill -->
                <div class="bg-white rounded-full py-4 px-8 shadow-sm flex items-center divide-x divide-slate-200 border border-slate-100">
                    <div class="flex flex-col items-center px-8">
                        <span class="text-[28px] font-medium text-[#0d326b] leading-none mb-1">10</span>
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Total Students</span>
                    </div>
                    <div class="flex flex-col items-center px-8">
                        <span class="text-[28px] font-medium text-[#857a26] leading-none mb-1">0</span>
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">New This Week</span>
                    </div>
                </div>
            </div>

            <!-- Filters & Actions -->
            <div class="flex items-center justify-between mb-8">
                <div class="flex items-center space-x-4">
                    <!-- Segmented Control -->
                    <div class="bg-[#f1f5f9] p-1.5 rounded-full flex items-center shadow-inner">
                        <button class="px-6 py-2.5 bg-white text-[#0d326b] text-[13px] font-bold rounded-full shadow-sm">All Students</button>
                        <button class="px-6 py-2.5 text-slate-500 hover:text-[#0d326b] text-[13px] font-medium rounded-full transition-colors">Active</button>
                        <button class="px-6 py-2.5 text-slate-500 hover:text-[#0d326b] text-[13px] font-medium rounded-full transition-colors">Inactive</button>
                    </div>
                    
                    <!-- Select Dropdowns -->
                    <div class="relative">
                        <select class="appearance-none bg-[#f1f5f9] text-[#1e293b] text-[13px] font-semibold py-3 pl-5 pr-10 rounded-full outline-none hover:bg-slate-200 transition-colors cursor-pointer border border-transparent">
                            <option>Learning Level</option>
                        </select>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-500 pointer-events-none">expand_more</span>
                    </div>
                    
                    <div class="relative">
                        <select class="appearance-none bg-[#f1f5f9] text-[#1e293b] text-[13px] font-semibold py-3 pl-5 pr-10 rounded-full outline-none hover:bg-slate-200 transition-colors cursor-pointer border border-transparent">
                            <option>Performance: High to Low</option>
                        </select>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-500 pointer-events-none">expand_more</span>
                    </div>
                </div>
                
                <button class="bg-[#0d326b] hover:bg-[#154188] text-white px-6 py-3 rounded-xl text-[14px] font-semibold transition-colors flex items-center space-x-2 shadow-sm">
                    <span class="material-symbols-outlined icon-outline text-[20px]">person_add</span>
                    <span>Add New Student</span>
                </button>
            </div>

            <!-- Student List Table -->
            <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 overflow-hidden">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-5 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase">Student Name</th>
                            <th class="py-5 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase">Level</th>
                            <th class="py-5 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase">Completed Lessons</th>
                            <th class="py-5 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase">Grades</th>
                            <th class="py-5 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase">Last Activity</th>
                            <th class="py-5 px-8"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <!-- Student Row 1 -->
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="py-5 px-8">
                                <div class="flex items-center space-x-4">
                                    <img src="https://ui-avatars.com/api/?name=Danah+Paris&background=random&color=fff&rounded=true" class="w-[42px] h-[42px] rounded-full shadow-sm"/>
                                    <div>
                                        <p class="text-[15px] font-bold text-[#0d326b]">Danah Paris</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5 font-medium">LRN: 107512</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5 px-8">
                                <span class="px-3 py-1.5 bg-[#e0e7ff] text-[#4f46e5] text-[10px] font-bold rounded-full uppercase tracking-wider">Intermediate</span>
                            </td>
                            <td class="py-5 px-8">
                                <div class="flex items-center space-x-4">
                                    <span class="text-[14px] font-bold text-[#1e293b]">42/60</span>
                                    <div class="w-24 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-[#0d326b] rounded-full" style="width: 70%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5 px-8">
                                <span class="text-[15px] font-bold text-[#0d326b]">94.2%</span>
                            </td>
                            <td class="py-5 px-8">
                                <p class="text-[13px] font-medium text-[#1e293b]">2 hours ago</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Numbers Practice</p>
                            </td>
                            <td class="py-5 px-8 text-right">
                                <button class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-200 hover:text-[#0d326b] transition-colors">
                                    <span class="material-symbols-outlined icon-outline text-[20px]">chevron_right</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Student Row 2 -->
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="py-5 px-8">
                                <div class="flex items-center space-x-4">
                                    <img src="https://ui-avatars.com/api/?name=Yeoj+Valdez&background=random&color=fff&rounded=true" class="w-[42px] h-[42px] rounded-full shadow-sm"/>
                                    <div>
                                        <p class="text-[15px] font-bold text-[#0d326b]">Yeoj Valdez</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5 font-medium">LRN: 107512</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5 px-8">
                                <span class="px-3 py-1.5 bg-[#f1f5f9] text-[#64748b] text-[10px] font-bold rounded-full uppercase tracking-wider">Beginner</span>
                            </td>
                            <td class="py-5 px-8">
                                <div class="flex items-center space-x-4">
                                    <span class="text-[14px] font-bold text-[#1e293b]">12/60</span>
                                    <div class="w-24 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-[#0d326b] rounded-full" style="width: 20%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5 px-8">
                                <span class="text-[15px] font-bold text-[#0d326b]">81.5%</span>
                            </td>
                            <td class="py-5 px-8">
                                <p class="text-[13px] font-medium text-[#1e293b]">Yesterday</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Alphabet Practice</p>
                            </td>
                            <td class="py-5 px-8 text-right">
                                <button class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-200 hover:text-[#0d326b] transition-colors">
                                    <span class="material-symbols-outlined icon-outline text-[20px]">chevron_right</span>
                                </button>
                            </td>
                        </tr>

                        <!-- Student Row 3 -->
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="py-5 px-8">
                                <div class="flex items-center space-x-4">
                                    <img src="https://ui-avatars.com/api/?name=Jared+Abellera&background=random&color=fff&rounded=true" class="w-[42px] h-[42px] rounded-full shadow-sm"/>
                                    <div>
                                        <p class="text-[15px] font-bold text-[#0d326b]">Jared Abellera</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5 font-medium">LRN: 107512</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5 px-8">
                                <span class="px-3 py-1.5 bg-[#0d326b] text-white text-[10px] font-bold rounded-full uppercase tracking-wider">Advanced</span>
                            </td>
                            <td class="py-5 px-8">
                                <div class="flex items-center space-x-4">
                                    <span class="text-[14px] font-bold text-[#1e293b]">58/60</span>
                                    <div class="w-24 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-[#0d326b] rounded-full" style="width: 96%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5 px-8">
                                <span class="text-[15px] font-bold text-[#0d326b]">98.9%</span>
                            </td>
                            <td class="py-5 px-8">
                                <p class="text-[13px] font-medium text-[#1e293b]">4 days ago</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Greetings Practice</p>
                            </td>
                            <td class="py-5 px-8 text-right">
                                <button class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-200 hover:text-[#0d326b] transition-colors">
                                    <span class="material-symbols-outlined icon-outline text-[20px]">chevron_right</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Pagination Footer -->
                <div class="px-8 py-5 border-t border-slate-100 flex items-center justify-between">
                    <p class="text-[13px] font-medium text-slate-500">Showing 3 of 10 students</p>
                    <div class="flex items-center space-x-2">
                        <button class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-slate-50 transition-colors">
                            <span class="material-symbols-outlined icon-outline text-[18px]">chevron_left</span>
                        </button>
                        <button class="w-9 h-9 rounded-lg bg-[#0d326b] text-white font-bold text-[13px] flex items-center justify-center shadow-sm">1</button>
                        <button class="w-9 h-9 rounded-lg border border-slate-200 text-slate-600 font-semibold text-[13px] flex items-center justify-center hover:bg-slate-50 transition-colors">2</button>
                        <button class="w-9 h-9 rounded-lg border border-slate-200 text-slate-600 font-semibold text-[13px] flex items-center justify-center hover:bg-slate-50 transition-colors">3</button>
                        <button class="w-9 h-9 rounded-lg border border-slate-200 flex items-center justify-center text-slate-400 hover:bg-slate-50 transition-colors">
                            <span class="material-symbols-outlined icon-outline text-[18px]">chevron_right</span>
                        </button>
                    </div>
                </div>
            </div>

        </main>
    </div>

</body>
</html>
