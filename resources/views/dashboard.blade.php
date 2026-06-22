@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="flex flex-col lg:flex-row gap-8">
                
                <!-- Left/Center Content -->
                <div class="flex-1 flex flex-col space-y-8">
                    
                    <!-- Welcome & Stats -->
                    <div>
                        <div class="mb-8 pl-2">
                            <h2 class="text-[32px] font-bold text-[#0d326b] mb-1">Welcome back, {{ $displayName }}</h2>
                            <p class="text-[15px] text-slate-500 font-medium tracking-wide">Class Summary</p>
                        </div>
                        
                        <div class="grid grid-cols-3 gap-8">
                            <!-- Card 1 -->
                            <div class="bg-white rounded-[24px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex flex-col justify-between min-h-[160px]">
                                <h3 class="text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-4 leading-relaxed">Total<br>Students</h3>
                                <div>
                                    <p class="text-[40px] font-normal text-[#0d326b] leading-none mb-4">
                                        {{ $totalStudents > 0 ? $totalStudents : 'No record' }}
                                    </p>
                                    <div class="w-[90%] h-2 bg-[#0d326b] rounded-full"></div>
                                </div>
                            </div>
                            <!-- Card 2 -->
                            <div class="bg-white rounded-[24px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex flex-col justify-between min-h-[160px]">
                                <h3 class="text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-4 leading-relaxed">Students Active<br>Today</h3>
                                <div>
                                    <p class="text-[40px] font-normal text-[#6366f1] leading-none mb-4">
                                        {{ $activeToday > 0 ? $activeToday : 'No record' }}
                                    </p>
                                    <div class="w-[90%] h-2 bg-[#6366f1] rounded-full"></div>
                                </div>
                            </div>
                            <!-- Card 3 -->
                            <div class="bg-white rounded-[24px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)] flex flex-col justify-between min-h-[160px]">
                                <h3 class="text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-4 leading-relaxed">Lessons<br>Completed</h3>
                                <div>
                                    <p class="text-[40px] font-normal text-[#10b981] leading-none mb-4">
                                        {{ $lessonsCompleted > 0 ? $lessonsCompleted : 'No record' }}
                                    </p>
                                    <div class="w-[90%] h-2 bg-[#10b981] rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Your Lessons -->
                    <div class="mt-10">
                        <div class="flex justify-between items-end mb-6 pl-2">
                            <h3 class="text-[22px] font-bold text-[#0d326b]">Your Lessons</h3>
                            <a href="{{ route('lessons.index') }}" class="text-[14px] font-bold text-[#0d326b] hover:underline pr-2">Manage Lessons</a>
                        </div>

                        @if($lessons->isEmpty())
                            <div class="bg-white rounded-[32px] p-10 text-center text-slate-400 text-sm font-medium shadow-sm">
                                No record — no lessons created yet.
                            </div>
                        @else
                        <div class="grid grid-cols-3 gap-8">
                            @foreach($lessons as $index => $lesson)
                            @php
                                $colors = [
                                    ['border' => '#facc15', 'bg' => '#1e40af', 'bar_bg' => '#fef08a', 'bar_fill' => '#facc15'],
                                    ['border' => 'transparent', 'bg' => '#3b82f6', 'bar_bg' => '#e2e8f0', 'bar_fill' => '#474e9c'],
                                    ['border' => 'transparent', 'bg' => '#60a5fa', 'bar_bg' => '#e2e8f0', 'bar_fill' => '#474e9c'],
                                ];
                                $c = $colors[$index % 3];
                                $icons = ['A', '#', '✋'];
                                $icon  = $icons[$index % 3];
                            @endphp
                            <div class="bg-white rounded-[32px] p-6 shadow-sm {{ $c['border'] !== 'transparent' ? 'border-[3px]' : '' }} relative min-h-[220px] flex flex-col overflow-hidden"
                                 style="{{ $c['border'] !== 'transparent' ? 'border-color:' . $c['border'] . ';' : '' }}">
                                <div class="absolute top-0 right-0 w-32 h-32 opacity-5 rounded-bl-full" style="background:{{ $c['border'] !== 'transparent' ? $c['border'] : $c['bg'] }}"></div>
                                <div class="w-[52px] h-[42px] text-white rounded-lg rounded-tr-xl rounded-bl-sm flex items-center justify-center font-bold mb-6 relative shadow-sm"
                                     style="background:{{ $c['bg'] }}">
                                    <div class="absolute -top-[8px] left-0 w-8 h-[10px] rounded-t-md" style="background:{{ $c['bg'] }}"></div>
                                    <span class="border border-white/40 rounded px-1.5 py-0.5 text-sm">{{ $icon }}</span>
                                </div>
                                <h4 class="font-bold text-[#0d326b] text-[18px] mb-2">{{ $lesson->title }}</h4>
                                <div class="flex justify-between items-center mb-6">
                                    <p class="text-[12px] font-medium text-slate-500 leading-tight w-16">
                                        {{ $lesson->enrolled }} Student{{ $lesson->enrolled !== 1 ? 's' : '' }}<br>Enrolled
                                    </p>
                                    <span class="text-[13px] font-bold text-[#0d326b]">{{ $lesson->completion }}%</span>
                                </div>
                                
                                <div class="mt-auto relative z-10">
                                    <div class="w-[90%] h-2.5 rounded-full overflow-hidden mb-5" style="background:{{ $c['bar_bg'] }}">
                                        <div class="h-full rounded-full" style="width: {{ $lesson->completion }}%; background:{{ $c['bar_fill'] }}"></div>
                                    </div>
                                    <div class="flex -space-x-2.5">
                                        @forelse($lesson->topStudents as $s)
                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($s->first_name . ' ' . $s->last_name) }}&background=random&color=fff&rounded=true"
                                             class="w-8 h-8 rounded-full border-2 border-white shadow-sm" title="{{ $s->first_name }} {{ $s->last_name }}"/>
                                        @empty
                                        <span class="text-[11px] text-slate-400 italic">No students yet</span>
                                        @endforelse
                                        @if($lesson->extraStudents > 0)
                                        <div class="w-8 h-8 rounded-full border-2 border-white bg-[#f1f5f9] flex items-center justify-center text-[9px] font-bold text-slate-500 shadow-sm">+{{ $lesson->extraStudents }}</div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @endif
                    </div>

                    <!-- Middle Widgets -->
                    <div class="grid grid-cols-2 gap-6">
                        <!-- Student Mastery -->
                        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                            <h3 class="text-lg font-bold text-brand-blue mb-6">Student Mastery</h3>
                            
                            @if($lessonMastery->isEmpty())
                                <div class="text-center text-sm text-slate-400 py-8 italic">No record</div>
                            @else
                            <div class="space-y-5">
                                @foreach($lessonMastery as $lm)
                                <div>
                                    <div class="flex justify-between text-[10px] font-bold text-slate-500 mb-1.5 uppercase tracking-wider">
                                        <span>{{ $lm->title }}</span>
                                        <span>{{ $lm->masteryPct }}% Completed</span>
                                    </div>
                                    <div class="w-full h-2.5 bg-blue-100 rounded-full overflow-hidden">
                                        <div class="bg-brand-blue h-full rounded-full" style="width: {{ $lm->masteryPct }}%"></div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                            @endif
                            
                            <!-- Legend -->
                            <div class="flex items-center space-x-4 mt-8 text-[9px] font-bold text-slate-400 uppercase tracking-wider">
                                <div class="flex items-center space-x-1.5">
                                    <div class="w-2 h-2 rounded-full bg-brand-blue"></div>
                                    <span>Completed</span>
                                </div>
                                <div class="flex items-center space-x-1.5">
                                    <div class="w-2 h-2 rounded-full bg-blue-100"></div>
                                    <span>Remaining</span>
                                </div>
                            </div>
                        </div>

                        <!-- Class Rate Circle -->
                        <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100 flex flex-col items-center justify-center text-center">
                            <div class="relative w-36 h-36 mb-6">
                                <svg class="w-full h-full transform -rotate-90">
                                    <circle cx="72" cy="72" r="64" fill="transparent" stroke="#f1f5f9" stroke-width="8"></circle>
                                    @if($classRate > 0)
                                    <circle cx="72" cy="72" r="64" fill="transparent" stroke="#facc15"
                                            stroke-dasharray="402"
                                            stroke-dashoffset="{{ $circleDashOffset }}"
                                            stroke-width="8" stroke-linecap="round"></circle>
                                    @endif
                                </svg>
                                <div class="absolute inset-0 flex flex-col items-center justify-center">
                                    @if($classRate > 0)
                                    <span class="text-3xl font-bold text-slate-700">{{ $classRate }}%</span>
                                    <span class="text-[8px] font-bold uppercase tracking-widest text-slate-500 mt-1">Class Rate</span>
                                    @else
                                    <span class="text-base font-bold text-slate-400">No record</span>
                                    @endif
                                </div>
                            </div>
                            @if($classRate > 0)
                            <p class="text-sm font-medium text-brand-blue px-6">Overall lesson completion rate across all students.</p>
                            @else
                            <p class="text-sm font-medium text-slate-400 px-6">No lesson progress data yet.</p>
                            @endif
                        </div>
                    </div>

                    <!-- Student Performance Bottom -->
                    <div class="bg-white rounded-3xl p-8 shadow-sm border border-slate-100">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-xl font-bold text-brand-blue">Student Performance</h3>
                            <div class="flex items-center space-x-3">
                                <span class="material-symbols-outlined text-slate-400">swap_vert</span>
                                <a href="{{ route('students') }}" class="px-4 py-1.5 bg-slate-100 text-brand-blue text-xs font-bold rounded-lg hover:bg-slate-200 transition-colors">View All</a>
                            </div>
                        </div>
                        
                        <div class="space-y-6">
                            @forelse($students as $student)
                            <!-- Student -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4 w-1/3">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($student->first_name . ' ' . $student->last_name) }}&background=random&color=fff&rounded=true" class="w-10 h-10 rounded-full"/>
                                    <span class="text-sm font-bold text-slate-700">{{ $student->first_name }} {{ $student->last_name }}</span>
                                </div>
                                <div class="flex-1 px-8">
                                    <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-brand-blue rounded-full" style="width: {{ $student->performancePct }}%"></div>
                                    </div>
                                </div>
                                <div class="w-24 text-right flex items-center justify-end space-x-4">
                                    <span class="text-sm font-bold text-brand-blue">{{ $student->performancePct }}%</span>
                                    <span class="px-2 py-1 text-[9px] font-bold rounded uppercase tracking-wider
                                        {{ $student->performancePct >= 75 ? 'bg-green-50 text-green-700' : ($student->performancePct >= 40 ? 'bg-blue-50 text-brand-blue' : 'bg-red-50 text-red-600') }}">
                                        {{ $student->performancePct >= 75 ? 'Good' : ($student->performancePct >= 40 ? 'Fair' : 'New') }}
                                    </span>
                                </div>
                            </div>
                            @empty
                            <div class="text-center text-sm text-slate-400 py-6 italic">
                                No record — no students added yet.
                            </div>
                            @endforelse
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
                        
                        @if($needsAttention->isNotEmpty())
                        <p class="text-[14px] font-bold text-[#1e293b] leading-[1.6] mb-8 relative z-10">
                            {{ $needsAttention->count() }} student{{ $needsAttention->count() > 1 ? 's are' : ' is' }} struggling and may need extra guidance.
                        </p>
                        @else
                        <p class="text-[14px] font-bold text-[#1e293b] leading-[1.6] mb-8 relative z-10">
                            All students are on track! Keep it up.
                        </p>
                        @endif
                        
                        <a href="{{ route('students') }}" class="block w-full bg-[#18181b] text-[#facc15] text-[13px] font-bold py-3.5 rounded-xl hover:bg-black transition-colors shadow-sm relative z-10 text-center">
                            View Students
                        </a>
                    </div>

                    <!-- Needs Attention -->
                    <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                        <h4 class="text-[12px] font-bold tracking-[0.15em] uppercase text-[#0d326b] mb-8">Needs Your Attention</h4>
                        
                        @if($needsAttention->isEmpty())
                            <div class="text-center text-sm text-slate-400 py-4 italic">No record</div>
                        @else
                        <div class="space-y-6">
                            @foreach($needsAttention as $s)
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-4">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($s->first_name . ' ' . $s->last_name) }}&background=random&color=fff&rounded=true" class="w-10 h-10 rounded-full shadow-sm"/>
                                    <span class="text-[14px] font-bold text-[#1e293b]">{{ $s->first_name }} {{ $s->last_name }}</span>
                                </div>
                                @if($s->alertType === 'Alert')
                                <span class="px-2.5 py-1 bg-[#fee2e2] text-[#b91c1c] text-[10px] font-black rounded-md uppercase tracking-wider">Alert</span>
                                @else
                                <span class="px-2.5 py-1 bg-[#fef3c7] text-[#b45309] text-[10px] font-black rounded-md uppercase tracking-wider">Review</span>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif
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
                        
                        @if($topLesson)
                        <div class="border-l-2 border-[#10b981] pl-4 mb-8 relative z-10">
                            <h4 class="text-[15px] font-bold mb-2 text-white">Top Performance: {{ $topLesson->title }}</h4>
                            <p class="text-[13px] text-[#93c5fd] leading-relaxed">
                                {{ $topLesson->masteryPct }}% completion rate — students are doing great in this module!
                            </p>
                        </div>
                        @else
                        <div class="border-l-2 border-slate-500 pl-4 mb-8 relative z-10">
                            <h4 class="text-[15px] font-bold mb-2 text-slate-300">No record</h4>
                            <p class="text-[13px] text-[#93c5fd] leading-relaxed">
                                Insights will appear once students start completing lessons.
                            </p>
                        </div>
                        @endif
                        
                        <a href="{{ route('students') }}" class="block w-full bg-[#0d4599] hover:bg-[#1556b3] text-[#93c5fd] text-[13px] font-semibold py-3.5 rounded-xl transition-colors flex items-center justify-center space-x-2 relative z-10 shadow-sm border border-[#2563eb]/30 text-center">
                            <span>View Full Report</span>
                            <span class="material-symbols-outlined icon-outline text-[16px]">description</span>
                        </a>
                    </div>

                </div>
            </div>
@endsection
