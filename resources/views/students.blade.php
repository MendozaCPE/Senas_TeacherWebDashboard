@extends('layouts.app')
@section('title', 'Students')
@section('content')
<!-- Header Section -->
            <div class="flex items-start justify-between mb-8">
                <div>
                    <h3 class="text-[11px] font-bold text-[#0d326b] tracking-[0.15em] uppercase mb-2">Overview</h3>
                    <h2 class="text-[36px] font-medium text-[#0d326b] leading-tight">Student Management</h2>
                </div>
                
                <!-- Quick Stats Pill -->
                <div class="bg-white rounded-full py-4 px-8 shadow-sm flex items-center divide-x divide-slate-200 border border-slate-100">
                    <div class="flex flex-col items-center px-8">
                        <span class="text-[28px] font-medium text-[#0d326b] leading-none mb-1">{{ $totalStudents }}</span>
                        <span class="text-[9px] font-bold text-slate-500 uppercase tracking-widest">Total Students</span>
                    </div>
                    <div class="flex flex-col items-center px-8">
                        <span class="text-[28px] font-medium text-[#857a26] leading-none mb-1">{{ $newThisWeek }}</span>
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
                        @forelse($students as $student)
                        <!-- Student Row -->
                        <tr class="hover:bg-slate-50/50 transition-colors group">
                            <td class="py-5 px-8">
                                <div class="flex items-center space-x-4">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($student->first_name . ' ' . $student->last_name) }}&background=random&color=fff&rounded=true" class="w-[42px] h-[42px] rounded-full shadow-sm"/>
                                    <div>
                                        <p class="text-[15px] font-bold text-[#0d326b]">{{ $student->first_name }} {{ $student->last_name }}</p>
                                        <p class="text-[11px] text-slate-400 mt-0.5 font-medium">LRN: {{ $student->lrn ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5 px-8">
                                <span class="px-3 py-1.5 bg-[#e0e7ff] text-[#4f46e5] text-[10px] font-bold rounded-full uppercase tracking-wider">{{ $student->grade_level ?? 'Beginner' }}</span>
                            </td>
                            <td class="py-5 px-8">
                                <div class="flex items-center space-x-4">
                                    <span class="text-[14px] font-bold text-[#1e293b]">--/60</span>
                                    <div class="w-24 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                                        <div class="h-full bg-[#0d326b] rounded-full" style="width: 0%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-5 px-8">
                                <span class="text-[15px] font-bold text-[#0d326b]">--%</span>
                            </td>
                            <td class="py-5 px-8">
                                <p class="text-[13px] font-medium text-[#1e293b]">{{ $student->created_at ? $student->created_at->diffForHumans() : 'Never' }}</p>
                                <p class="text-[10px] text-slate-400 font-bold uppercase tracking-wider mt-0.5">Enrolled</p>
                            </td>
                            <td class="py-5 px-8 text-right">
                                <button class="w-8 h-8 rounded-full flex items-center justify-center text-slate-400 hover:bg-slate-200 hover:text-[#0d326b] transition-colors">
                                    <span class="material-symbols-outlined icon-outline text-[20px]">chevron_right</span>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-10 text-center text-slate-500">No students found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                
                <!-- Pagination Footer -->
                <div class="px-8 py-5 border-t border-slate-100">
                    {{ $students->links('pagination::tailwind') }}
                </div>
            </div>
@endsection
