<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>SEÑAS Teacher Portal - Settings</title>
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
            <a href="/reports" class="flex items-center space-x-4 px-6 py-4 text-slate-500 hover:text-[#0d326b] hover:bg-white/50 transition-colors text-[15px] font-medium border-l-[6px] border-transparent">
                <span class="material-symbols-outlined icon-outline text-[22px]">description</span>
                <span>Reports</span>
            </a>
            <a href="/settings" class="flex items-center space-x-4 px-6 py-4 mt-4 bg-white text-[#0d326b] font-bold text-[15px] border-l-[6px] border-[#facc15] shadow-[0_2px_10px_rgba(0,0,0,0.02)]">
                <span class="material-symbols-outlined text-[22px]">settings</span>
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
                    <span class="text-[#0d326b]">Settings</span>
                </div>
            </div>
        </header>

        <!-- Scrollable Content -->
        <main class="flex-1 overflow-y-auto px-12 pb-12 relative">
            
            <div class="w-full pb-24">
                
                <!-- Section: Personal Information -->
                <div class="mb-10">
                    <h2 class="text-[24px] font-medium text-[#1e4b8f] leading-tight mb-6">Personal Information</h2>
                    
                    <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex items-start space-x-12">
                        <!-- Avatar Section -->
                        <div class="flex flex-col items-center flex-shrink-0">
                            <div class="w-24 h-24 rounded-full border-4 border-white shadow-sm overflow-hidden bg-[#e2e8f0] mb-4">
                                <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=Mila&backgroundColor=e2e8f0" alt="Ms. Mila" class="w-full h-full object-cover"/>
                            </div>
                            <button class="text-[#1e4b8f] text-[13px] font-bold hover:underline">Change Avatar</button>
                        </div>
                        
                        <!-- Form Fields -->
                        <div class="flex-1 grid grid-cols-2 gap-x-8 gap-y-6">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Full Name</label>
                                <input type="text" value="Mila Quintana" class="w-full bg-[#eef2f6] border-none rounded-xl py-3.5 px-4 text-[14px] text-slate-700 outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition-all font-medium"/>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Professional Title</label>
                                <input type="text" value="SNED Teacher" class="w-full bg-[#eef2f6] border-none rounded-xl py-3.5 px-4 text-[14px] text-slate-700 outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition-all font-medium"/>
                            </div>
                            <div class="col-span-2">
                                <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Academic Email</label>
                                <input type="email" value="name@deped.gov.ph" class="w-full bg-[#eef2f6] border-none rounded-xl py-3.5 px-4 text-[14px] text-slate-700 outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition-all font-medium"/>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Institution -->
                <div class="mb-10">
                    <h2 class="text-[24px] font-medium text-[#1e4b8f] leading-tight mb-6">Institution</h2>
                    
                    <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex items-start space-x-12">
                        <!-- Logo Section -->
                        <div class="flex flex-col items-center flex-shrink-0">
                            <div class="w-24 h-24 rounded-full border-4 border-white shadow-sm overflow-hidden bg-slate-100 flex items-center justify-center mb-4">
                                <img src="https://api.dicebear.com/7.x/identicon/svg?seed=Nasugbu&backgroundColor=eef2f6&iconColor=0d326b" alt="School Logo" class="w-16 h-16 object-cover rounded-full"/>
                            </div>
                            <button class="text-[#1e4b8f] text-[13px] font-bold hover:underline">Change Logo</button>
                        </div>
                        
                        <!-- Form Fields -->
                        <div class="flex-1 space-y-6">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">School Name</label>
                                <input type="text" value="Nasugbu West Central School" class="w-full bg-[#eef2f6] border-none rounded-xl py-3.5 px-4 text-[14px] text-slate-700 outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition-all font-medium"/>
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Address</label>
                                <input type="text" placeholder="" class="w-full bg-[#eef2f6] border-none rounded-xl py-3.5 px-4 text-[14px] text-slate-700 outline-none focus:ring-2 focus:ring-[#0d326b]/20 transition-all font-medium"/>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2-Column Section -->
                <div class="grid grid-cols-2 gap-10">
                    
                    <!-- Communication -->
                    <div>
                        <h2 class="text-[24px] font-medium text-[#1e4b8f] leading-tight mb-6">Communication</h2>
                        
                        <div class="bg-white rounded-[32px] p-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)] space-y-4">
                            <!-- Toggle 1 -->
                            <div class="flex items-center justify-between p-4 bg-white rounded-xl">
                                <div class="flex items-center space-x-4">
                                    <span class="material-symbols-outlined icon-outline text-[22px] text-slate-400">mail</span>
                                    <span class="text-[14px] font-bold text-[#1e293b]">Email Alerts</span>
                                </div>
                                <div class="w-5 h-5 rounded flex items-center justify-center bg-[#0d326b] text-white">
                                    <span class="material-symbols-outlined text-[14px] font-bold">check</span>
                                </div>
                            </div>
                            
                            <hr class="border-slate-100">
                            
                            <!-- Toggle 2 -->
                            <div class="flex items-center justify-between p-4 bg-white rounded-xl">
                                <div class="flex items-center space-x-4">
                                    <span class="material-symbols-outlined icon-outline text-[22px] text-slate-400">smartphone</span>
                                    <span class="text-[14px] font-bold text-[#1e293b]">App Notifications</span>
                                </div>
                                <div class="w-5 h-5 rounded flex items-center justify-center bg-[#0d326b] text-white">
                                    <span class="material-symbols-outlined text-[14px] font-bold">check</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Access Control -->
                    <div>
                        <h2 class="text-[24px] font-medium text-[#1e4b8f] leading-tight mb-6">Access Control</h2>
                        
                        <div class="bg-white rounded-[32px] p-6 shadow-[0_4px_20px_rgba(0,0,0,0.02)] h-[178px] flex items-center">
                            
                            <button class="w-full flex items-center justify-between p-4 group">
                                <div class="flex items-center space-x-5">
                                    <div class="w-12 h-12 rounded-full bg-[#f1f5f9] text-[#0d326b] flex items-center justify-center">
                                        <span class="material-symbols-outlined icon-outline text-[22px]">lock_reset</span>
                                    </div>
                                    <div class="text-left">
                                        <h4 class="text-[14px] font-bold text-[#1e293b] mb-1 group-hover:text-[#0d326b] transition-colors">Update Password</h4>
                                        <p class="text-[12px] text-slate-400 font-medium">Last changed 42 days ago</p>
                                    </div>
                                </div>
                                <span class="material-symbols-outlined icon-outline text-slate-300">chevron_right</span>
                            </button>
                            
                        </div>
                    </div>
                </div>

            </div>

            <!-- Footer Actions -->
            <div class="w-full mt-8 mb-12 flex items-center justify-between">
                <p class="text-[12px] font-medium text-slate-400">
                    Changes will be synchronized across all educational instances.
                </p>
                <div class="flex items-center space-x-4">
                    <button class="px-6 py-3 rounded-xl border border-slate-200 text-[#1e293b] text-[13px] font-bold hover:bg-slate-50 transition-colors bg-white">
                        Reset Defaults
                    </button>
                    <button class="px-6 py-3 rounded-xl bg-[#0d326b] hover:bg-[#154188] text-white text-[13px] font-bold shadow-sm transition-colors">
                        Save Configurations
                    </button>
                </div>
            </div>

        </main>
    </div>

</body>
</html>
