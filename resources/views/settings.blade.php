@extends('layouts.app')
@section('bg-class', 'bg-[#f8fafc]')
@section('title', 'Settings')
@section('content')
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
@endsection
