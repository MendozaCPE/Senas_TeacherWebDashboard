@extends('layouts.app')
@section('bg-class', 'bg-[#f8fafc]')
@section('title', 'Reports')
@section('content')
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
@endsection
