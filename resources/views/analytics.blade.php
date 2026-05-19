@extends('layouts.app')
@section('bg-class', 'bg-[#f4f7f9]')
@section('title', 'Analytics')
@section('content')
<div class="flex flex-col lg:flex-row gap-8">
                
                <!-- Left Side: Main Analytics -->
                <div class="flex-1 flex flex-col space-y-8">
                    
                    <!-- Header Section -->
                    <div class="flex items-start justify-between">
                        <div>
                            <h3 class="text-[11px] font-bold text-[#0d326b] tracking-[0.15em] uppercase mb-2">OVERVIEW</h3>
                            <h2 class="text-[36px] font-medium text-[#0d326b] leading-tight">Class Analytics</h2>
                        </div>
                        <button class="bg-[#facc15] hover:bg-[#eab308] text-black px-6 py-3.5 rounded-xl text-[14px] font-bold transition-colors flex items-center space-x-2 shadow-sm">
                            <span class="material-symbols-outlined icon-outline text-[20px]">download</span>
                            <span>Export PDF Report</span>
                        </button>
                    </div>

                    <!-- Top 3 Stats -->
                    <div class="grid grid-cols-3 gap-6">
                        <!-- Stat 1 -->
                        <div class="bg-white rounded-[24px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="w-10 h-10 bg-[#f1f5f9] rounded-xl flex items-center justify-center text-[#3b82f6]">
                                    <span class="material-symbols-outlined text-[20px]">fact_check</span>
                                </div>
                                <h3 class="text-[11px] font-bold text-slate-500 tracking-[0.15em] uppercase leading-tight">Total<br>Attempts</h3>
                            </div>
                            <p class="text-[42px] font-normal text-[#0d326b] leading-none mb-3">1,248</p>
                            <p class="text-[13px] font-medium text-[#3b82f6]">+12% from last month</p>
                        </div>
                        
                        <!-- Stat 2 -->
                        <div class="bg-white rounded-[24px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="w-10 h-10 bg-[#f1f5f9] rounded-xl flex items-center justify-center text-[#3b82f6]">
                                    <span class="material-symbols-outlined text-[20px]">my_location</span>
                                </div>
                                <h3 class="text-[11px] font-bold text-slate-500 tracking-[0.15em] uppercase leading-tight">Average<br>Performance</h3>
                            </div>
                            <p class="text-[42px] font-normal text-[#0d326b] leading-none mb-3">92.4%</p>
                            <p class="text-[13px] font-medium text-[#3b82f6]">Lesson Goals Achieved</p>
                        </div>
                        
                        <!-- Stat 3 -->
                        <div class="bg-white rounded-[24px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                            <div class="flex items-center space-x-3 mb-6">
                                <div class="w-10 h-10 bg-[#f1f5f9] rounded-xl flex items-center justify-center text-[#3b82f6]">
                                    <span class="material-symbols-outlined text-[20px]">assignment_turned_in</span>
                                </div>
                                <h3 class="text-[11px] font-bold text-slate-500 tracking-[0.15em] uppercase leading-tight">Practice<br>Completion</h3>
                            </div>
                            <p class="text-[42px] font-normal text-[#0d326b] leading-none mb-3">88.2%</p>
                            <p class="text-[13px] font-medium text-[#3b82f6]">34 students active</p>
                        </div>
                    </div>

                    <!-- Chart Widget -->
                    <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                        <div class="flex items-start justify-between mb-10">
                            <div>
                                <h3 class="text-[20px] font-bold text-[#0d326b] mb-1">Weekly Class Progress</h3>
                                <p class="text-[14px] font-medium text-slate-500">Class Progress in the last 30 days</p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-3 h-3 rounded-full bg-[#0d326b]"></div>
                                <span class="text-[12px] font-semibold text-slate-500">Class Performance</span>
                            </div>
                        </div>

                        <!-- Fake Chart Area -->
                        <div class="relative h-[200px] w-full mb-6 mt-4">
                            <!-- Tooltip -->
                            <div class="absolute top-2 right-[25%] bg-white rounded-2xl shadow-lg border border-slate-100 p-4 w-40 z-10">
                                <p class="text-[9px] font-bold text-slate-400 tracking-[0.15em] uppercase mb-1.5">Week 4 Activity</p>
                                <p class="text-[14px] font-bold text-[#0d326b] mb-1">88.4% Accuracy</p>
                                <p class="text-[11px] font-bold text-emerald-500">+4.2% vs LW</p>
                            </div>
                            
                            <!-- SVG lines -->
                            <svg viewBox="0 0 800 200" class="w-full h-full preserve-3d" preserveAspectRatio="none">
                                <!-- Dashed Yellow Line -->
                                <path d="M 0 160 Q 200 120 400 110 T 800 120" fill="none" stroke="#facc15" stroke-width="3" stroke-dasharray="8 6"/>
                                <!-- Solid Blue Line -->
                                <path d="M 0 180 Q 200 140 400 90 T 800 40" fill="none" stroke="#0d326b" stroke-width="4"/>
                            </svg>
                        </div>

                        <!-- X Axis Labels -->
                        <div class="flex justify-between px-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                            <span>May 01</span>
                            <span>May 08</span>
                            <span>May 15</span>
                            <span>May 22</span>
                            <span>Today</span>
                        </div>
                    </div>

                    <!-- Lesson Completion Rates -->
                    <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                        <div class="flex items-center justify-between mb-8">
                            <h3 class="text-[18px] font-bold text-black">Lesson Completion Rates Across Modules</h3>
                            <div class="bg-[#f1f5f9] px-4 py-2 rounded-full flex items-center space-x-2 cursor-pointer hover:bg-slate-200 transition-colors">
                                <span class="text-[12px] font-bold text-black">Semester 1 Units</span>
                                <span class="material-symbols-outlined icon-outline text-[16px] text-black">expand_more</span>
                            </div>
                        </div>

                        <div class="space-y-6">
                            <!-- Module 1 -->
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-[13px] font-bold text-black">Module 01: Basics</span>
                                    <span class="text-[13px] font-bold text-[#0d326b]">92%</span>
                                </div>
                                <div class="w-full h-2.5 bg-[#f1f5f9] rounded-full overflow-hidden">
                                    <div class="bg-[#0d326b] h-full rounded-full" style="width: 92%"></div>
                                </div>
                            </div>
                            <!-- Module 2 -->
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-[13px] font-bold text-black">Module 02: Common Greetings</span>
                                    <span class="text-[13px] font-bold text-[#0d326b]">64%</span>
                                </div>
                                <div class="w-full h-2.5 bg-[#f1f5f9] rounded-full overflow-hidden">
                                    <div class="bg-[#0d326b] h-full rounded-full" style="width: 64%"></div>
                                </div>
                            </div>
                            <!-- Module 3 -->
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-[13px] font-bold text-black">Module 03: Fingerspelling</span>
                                    <span class="text-[13px] font-bold text-[#0d326b]">48%</span>
                                </div>
                                <div class="w-full h-2.5 bg-[#f1f5f9] rounded-full overflow-hidden">
                                    <div class="bg-[#0d326b] h-full rounded-full" style="width: 48%"></div>
                                </div>
                            </div>
                            <!-- Module 4 -->
                            <div>
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-[13px] font-bold text-black">Module 04: Conversational</span>
                                    <span class="text-[13px] font-bold text-[#0d326b]">12%</span>
                                </div>
                                <div class="w-full h-2.5 bg-[#f1f5f9] rounded-full overflow-hidden">
                                    <div class="bg-[#0d326b] h-full rounded-full" style="width: 12%"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Sidebar Widgets -->
                <div class="w-[340px] flex-shrink-0 flex flex-col space-y-8">
                    
                    <!-- Class Insights -->
                    <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                        <div class="flex items-center space-x-3 mb-8">
                            <span class="material-symbols-outlined icon-outline text-[22px] text-[#facc15]">psychology</span>
                            <span class="text-[15px] font-bold text-[#1e293b]">Class Insights</span>
                        </div>
                        
                        <div class="bg-[#f8fafc] rounded-[20px] p-6">
                            <h4 class="text-[9px] font-bold tracking-[0.15em] uppercase text-slate-400 mb-4">Top Performer</h4>
                            <div class="flex items-center space-x-3 mb-4">
                                <div class="w-8 h-8 rounded-full bg-[#cbd5e1] text-[#0d326b] flex items-center justify-center text-[10px] font-bold">JM</div>
                                <span class="text-[13px] font-bold text-black">Julian Martinez</span>
                            </div>
                            <p class="text-[12px] text-slate-500 font-medium leading-relaxed">
                                Students practiced 12 times in the last 2 days with 98% grading.
                            </p>
                        </div>
                    </div>

                    <!-- Recent Alerts -->
                    <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                        <h3 class="text-[16px] font-bold text-black mb-8">Recent Alerts</h3>
                        
                        <div class="relative">
                            <!-- Vertical Line -->
                            <div class="absolute left-[9px] top-4 bottom-4 w-0.5 bg-slate-100"></div>
                            
                            <div class="space-y-8">
                                <!-- Alert 1 -->
                                <div class="relative flex items-start space-x-4">
                                    <div class="w-5 h-5 rounded-full border-[3px] border-[#3b82f6] bg-white relative z-10 mt-1"></div>
                                    <div>
                                        <h4 class="text-[13px] font-bold text-black mb-1">Quiz Results Published</h4>
                                        <p class="text-[11px] text-slate-500 font-medium mb-1.5">24 students completed Unit 3 test</p>
                                        <p class="text-[9px] font-bold text-slate-400 tracking-[0.1em] uppercase">12 Mins Ago</p>
                                    </div>
                                </div>
                                
                                <!-- Alert 2 -->
                                <div class="relative flex items-start space-x-4">
                                    <div class="w-5 h-5 rounded-full border-[3px] border-[#facc15] bg-white relative z-10 mt-1"></div>
                                    <div>
                                        <h4 class="text-[13px] font-bold text-black mb-1">Performance Drop detected</h4>
                                        <p class="text-[9px] font-bold text-slate-400 tracking-[0.1em] uppercase mt-2">1 Hour Ago</p>
                                    </div>
                                </div>
                                
                                <!-- Alert 3 -->
                                <div class="relative flex items-start space-x-4">
                                    <div class="w-5 h-5 rounded-full border-[3px] border-[#e2e8f0] bg-white relative z-10 mt-1"></div>
                                    <div>
                                        <h4 class="text-[13px] font-bold text-black mb-1">New Lesson Uploaded</h4>
                                        <p class="text-[9px] font-bold text-slate-400 tracking-[0.1em] uppercase mt-2">4 Hours Ago</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
@endsection
