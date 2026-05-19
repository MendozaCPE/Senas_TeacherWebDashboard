<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SEÑAS Teacher Portal - Reports</title>
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
                            bg: '#f8fafc', 
                            card: '#ffffff'
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 1, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .material-symbols-outlined.icon-outline { font-variation-settings: 'FILL' 0; }
        ::-webkit-scrollbar { width: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="text-slate-800 font-sans antialiased flex h-screen overflow-hidden bg-[#f8fafc]">

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
            <a href="/lessons" class="flex items-center space-x-4 px-6 py-4 text-slate-500 hover:text-[#0d326b] hover:bg-white/50 transition-colors text-[15px] font-medium border-l-[6px] border-transparent">
                <span class="material-symbols-outlined icon-outline text-[22px]">menu_book</span>
                <span>Lessons</span>
            </a>
            <a href="/analytics" class="flex items-center space-x-4 px-6 py-4 text-slate-500 hover:text-[#0d326b] hover:bg-white/50 transition-colors text-[15px] font-medium border-l-[6px] border-transparent">
                <span class="material-symbols-outlined icon-outline text-[22px]">bar_chart</span>
                <span>Analytics</span>
            </a>
            <a href="/reports" class="flex items-center space-x-4 px-6 py-4 bg-white text-[#0d326b] font-bold text-[15px] border-l-[6px] border-[#facc15] shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                <span class="material-symbols-outlined text-[22px]">description</span>
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
                    <span class="text-[#0d326b]">Reports</span>
                </div>
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto px-12 pb-12">
            
            <div class="max-w-[1200px]">
                <!-- Header -->
                <div class="mb-8">
                    <h2 class="text-[36px] font-medium text-[#0d326b] leading-tight">Academic Reports</h2>
                </div>

                <!-- Filters Section -->
                <div class="bg-[#f1f5f9] rounded-[32px] p-8 mb-10 shadow-inner">
                    <div class="grid grid-cols-2 gap-8 mb-6">
                        <!-- Student Search -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Student Search</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                                    <span class="material-symbols-outlined icon-outline text-[18px]">person</span>
                                </span>
                                <select class="appearance-none w-full bg-white text-slate-700 text-[14px] font-medium py-3.5 pl-12 pr-10 rounded-xl outline-none focus:ring-2 focus:ring-[#0d326b]/20 cursor-pointer shadow-sm">
                                    <option>All Students</option>
                                </select>
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-400 pointer-events-none">expand_more</span>
                            </div>
                        </div>

                        <!-- Module Type -->
                        <div>
                            <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Module Type</label>
                            <div class="relative">
                                <select class="appearance-none w-full bg-white text-slate-700 text-[14px] font-medium py-3.5 pl-6 pr-10 rounded-xl outline-none focus:ring-2 focus:ring-[#0d326b]/20 cursor-pointer shadow-sm">
                                    <option>All Modules</option>
                                </select>
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-400 pointer-events-none">expand_more</span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-end space-x-6">
                        <!-- Date Range -->
                        <div class="flex-1">
                            <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Date Range</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                                    <span class="material-symbols-outlined icon-outline text-[18px]">calendar_today</span>
                                </span>
                                <input type="text" value="Oct 1 - Oct 31, 2023" class="w-full bg-white text-slate-700 text-[14px] font-medium py-3.5 pl-12 pr-4 rounded-xl outline-none focus:ring-2 focus:ring-[#0d326b]/20 shadow-sm" readonly/>
                            </div>
                        </div>
                        
                        <!-- Generate Button -->
                        <button class="bg-[#0d326b] hover:bg-[#154188] text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-colors flex items-center space-x-2 shadow-sm whitespace-nowrap">
                            <span class="material-symbols-outlined icon-outline text-[18px]">filter_alt</span>
                            <span>Generate</span>
                        </button>
                    </div>
                </div>

                <!-- Generated Reports Table -->
                <div class="bg-white rounded-[32px] shadow-[0_4px_20px_rgba(0,0,0,0.02)] overflow-hidden">
                    <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 bg-[#f8fafc]">
                        <h3 class="text-[18px] font-bold text-[#1e293b]">Recent Generated Reports</h3>
                        <div class="flex items-center space-x-3">
                            <button class="flex items-center space-x-1.5 bg-white text-[#0d326b] px-4 py-2 rounded-lg text-[13px] font-bold shadow-sm border border-slate-200 hover:bg-slate-50 transition-colors">
                                <span class="material-symbols-outlined icon-outline text-[16px]">picture_as_pdf</span>
                                <span>PDF</span>
                            </button>
                            <button class="flex items-center space-x-1.5 bg-white text-[#0d326b] px-4 py-2 rounded-lg text-[13px] font-bold shadow-sm border border-slate-200 hover:bg-slate-50 transition-colors">
                                <span class="material-symbols-outlined icon-outline text-[16px]">csv</span>
                                <span>CSV</span>
                            </button>
                        </div>
                    </div>

                    <table class="w-full text-left border-collapse">
                        <thead class="bg-[#f8fafc]">
                            <tr class="border-b border-slate-100">
                                <th class="py-4 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase">Report Name</th>
                                <th class="py-4 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase w-40">Created By</th>
                                <th class="py-4 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase w-32">Status</th>
                                <th class="py-4 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase w-48">Timestamp</th>
                                <th class="py-4 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase w-24">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <!-- Report 1 -->
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="py-6 px-8">
                                    <div class="flex items-start space-x-4">
                                        <span class="material-symbols-outlined icon-outline text-[24px] text-slate-400 mt-1">description</span>
                                        <div>
                                            <p class="text-[14px] font-bold text-[#1e293b] mb-0.5">Monthly Progress Summary</p>
                                            <p class="text-[12px] text-slate-400 font-medium">January</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-6 px-8">
                                    <span class="text-[13px] font-medium text-slate-600">System-Auto</span>
                                </td>
                                <td class="py-6 px-8">
                                    <span class="px-2.5 py-1 bg-[#dcfce7] text-[#166534] text-[10px] font-bold rounded uppercase tracking-wider">Ready</span>
                                </td>
                                <td class="py-6 px-8">
                                    <span class="text-[13px] font-medium text-slate-500">Apr 24,<br>09:15 AM</span>
                                </td>
                                <td class="py-6 px-8">
                                    <button class="w-10 h-10 rounded-full flex items-center justify-center text-[#0d326b] hover:bg-[#e0e7ff] transition-colors">
                                        <span class="material-symbols-outlined icon-outline text-[20px]">download</span>
                                    </button>
                                </td>
                            </tr>

                            <!-- Report 2 -->
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="py-6 px-8">
                                    <div class="flex items-start space-x-4">
                                        <span class="material-symbols-outlined icon-outline text-[24px] text-slate-400 mt-1">description</span>
                                        <div>
                                            <p class="text-[14px] font-bold text-[#1e293b] mb-0.5">Mateo R.</p>
                                            <p class="text-[12px] text-slate-400 font-medium">Individual Performance</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-6 px-8">
                                    <span class="text-[13px] font-medium text-slate-600">Teacher</span>
                                </td>
                                <td class="py-6 px-8">
                                    <span class="px-2.5 py-1 bg-[#dcfce7] text-[#166534] text-[10px] font-bold rounded uppercase tracking-wider">Ready</span>
                                </td>
                                <td class="py-6 px-8">
                                    <span class="text-[13px] font-medium text-slate-500">Apr 23,<br>02:40 PM</span>
                                </td>
                                <td class="py-6 px-8">
                                    <button class="w-10 h-10 rounded-full flex items-center justify-center text-[#0d326b] hover:bg-[#e0e7ff] transition-colors">
                                        <span class="material-symbols-outlined icon-outline text-[20px]">download</span>
                                    </button>
                                </td>
                            </tr>

                            <!-- Report 3 -->
                            <tr class="hover:bg-slate-50/50 transition-colors group">
                                <td class="py-6 px-8">
                                    <div class="flex items-start space-x-4">
                                        <span class="material-symbols-outlined icon-outline text-[24px] text-slate-400 mt-1">description</span>
                                        <div>
                                            <p class="text-[14px] font-bold text-[#1e293b] mb-0.5">Weekly Progress Check</p>
                                            <p class="text-[12px] text-slate-400 font-medium">Areas to Improve</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-6 px-8">
                                    <span class="text-[13px] font-medium text-slate-600">System-Auto</span>
                                </td>
                                <td class="py-6 px-8">
                                    <span class="px-2.5 py-1 bg-[#fef08a] text-[#854d0e] text-[10px] font-bold rounded uppercase tracking-wider">Processing</span>
                                </td>
                                <td class="py-6 px-8">
                                    <span class="text-[13px] font-medium text-slate-500">Apr 22,<br>11:05 AM</span>
                                </td>
                                <td class="py-6 px-8">
                                    <button class="w-10 h-10 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-100 transition-colors">
                                        <span class="material-symbols-outlined icon-outline text-[20px]">hourglass_empty</span>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

</body>
</html>
