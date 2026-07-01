@extends('layouts.app')
@section('bg-class', 'bg-[#f4f7f9]')
@section('title', 'Analytics')
@section('content')

{{-- Max-width wrapper --}}
<div class="flex flex-col lg:flex-row gap-8 w-full">

    {{-- ════════════════ LEFT MAIN COLUMN ════════════════ --}}
    <div class="flex-1 flex flex-col space-y-8">

        {{-- Header --}}
        <div class="flex items-start justify-between">
            <div>
                <h3 class="text-[11px] font-bold text-[#0d326b] tracking-[0.15em] uppercase mb-2">OVERVIEW</h3>
                <h2 class="text-[36px] font-medium text-[#0d326b] leading-tight">Class Analytics</h2>
            </div>
            <a href="{{ route('analytics.export-pdf') }}"
               class="text-white px-6 py-3.5 rounded-xl text-[14px] font-bold transition-all duration-300 flex items-center space-x-2 shadow-sm"
   style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%);">
                <span class="material-symbols-outlined icon-outline text-[20px]">download</span>
                <span>Export PDF Report</span>
            </a>
        </div>

        {{-- ── Stat Cards ── --}}
        <div class="grid grid-cols-3 gap-6">
            {{-- Total Attempts --}}
            <div class="bg-white rounded-[24px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-[#f1f5f9] rounded-xl flex items-center justify-center text-[#3b82f6]">
                        <span class="material-symbols-outlined text-[20px]">fact_check</span>
                    </div>
                    <h3 class="text-[11px] font-bold text-slate-500 tracking-[0.15em] uppercase leading-tight">Total<br>Attempts</h3>
                </div>
                <p class="text-[42px] font-normal text-[#0d326b] leading-none mb-3">{{ number_format($totalAttempts) }}</p>
                <p class="text-[13px] font-medium text-[#3b82f6]">{{ $totalStudents }} enrolled students</p>
            </div>

            {{-- Avg Performance --}}
            <div class="bg-white rounded-[24px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-[#f1f5f9] rounded-xl flex items-center justify-center text-[#3b82f6]">
                        <span class="material-symbols-outlined text-[20px]">my_location</span>
                    </div>
                    <h3 class="text-[11px] font-bold text-slate-500 tracking-[0.15em] uppercase leading-tight">Average<br>Performance</h3>
                </div>
                <p class="text-[42px] font-normal text-[#0d326b] leading-none mb-3">{{ number_format($avgPerformance, 1) }}%</p>
                <p class="text-[13px] font-medium text-[#3b82f6]">Quiz accuracy across class</p>
            </div>

            {{-- Practice Completion --}}
            <div class="bg-white rounded-[24px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
                <div class="flex items-center space-x-3 mb-6">
                    <div class="w-10 h-10 bg-[#f1f5f9] rounded-xl flex items-center justify-center text-[#3b82f6]">
                        <span class="material-symbols-outlined text-[20px]">assignment_turned_in</span>
                    </div>
                    <h3 class="text-[11px] font-bold text-slate-500 tracking-[0.15em] uppercase leading-tight">Practice<br>Completion</h3>
                </div>
                <p class="text-[42px] font-normal text-[#0d326b] leading-none mb-3">{{ $practiceCompletion }}%</p>
                <p class="text-[13px] font-medium text-[#3b82f6]">{{ $activeStudents }} students active this week</p>
            </div>
        </div>

        {{-- ── Weekly Activity Chart ── --}}
        <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
            <div class="flex items-start justify-between mb-8">
                <div>
                    <h3 class="text-[20px] font-bold text-[#0d326b] mb-1">Weekly Activity</h3>
                    <p class="text-[14px] font-medium text-slate-500">Lesson interactions per day — last 7 days</p>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 rounded-full bg-[#0d326b]"></div>
                    <span class="text-[12px] font-semibold text-slate-500">Daily Sessions</span>
                </div>
            </div>

            @php
                $maxCount = collect($weeklyData)->max('count') ?: 1;
            @endphp

            {{-- Bar chart --}}
            <div class="flex items-end justify-between gap-3 h-[160px] px-2">
                @foreach($weeklyData as $day)
                    @php
                        $barPct = $maxCount > 0 ? ($day['count'] / $maxCount) * 100 : 0;
                        $barHeight = max(4, round($barPct * 1.4)); // max ~140px
                        $isToday = $loop->last;
                    @endphp
                    <div class="flex flex-col items-center gap-2 flex-1">
                        <span class="text-[11px] font-bold {{ $isToday ? 'text-[#0d326b]' : 'text-slate-400' }}">
                            {{ $day['count'] > 0 ? $day['count'] : '' }}
                        </span>
                        <div class="w-full rounded-t-xl transition-all {{ $isToday ? 'bg-[#0d326b]' : 'bg-[#e2e8f0]' }}"
                             style="height: {{ $barHeight }}px; min-height: 4px;"></div>
                        <span class="text-[10px] font-bold {{ $isToday ? 'text-[#0d326b]' : 'text-slate-400' }} uppercase tracking-wider">
                            {{ $day['label'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- ── Lesson Completion Rates ── --}}
        <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
            <div class="flex items-center justify-between mb-8">
                <h3 class="text-[18px] font-bold text-black">Lesson Completion Rates</h3>
                <span class="text-[12px] font-bold text-slate-400 bg-[#f1f5f9] px-4 py-2 rounded-full">
                    {{ $lessonCompletion->count() }} lessons
                </span>
            </div>

            @if($lessonCompletion->isEmpty())
                <p class="text-slate-400 text-[14px] text-center py-8">No lessons published yet.</p>
            @else
                <div class="space-y-6">
                    @foreach($lessonCompletion as $lesson)
                        @php
                            $colors = ['#0d326b', '#3b82f6', '#06b6d4', '#8b5cf6'];
                            $color  = $colors[$loop->index % count($colors)];
                        @endphp
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <div>
                                    <span class="text-[13px] font-bold text-black">
                                        Module {{ str_pad($lesson->module_order, 2, '0', STR_PAD_LEFT) }}: {{ $lesson->title }}
                                    </span>
                                    <span class="ml-2 text-[11px] text-slate-400 font-medium">
                                        {{ $lesson->completedCount }}/{{ $totalStudents }} students
                                    </span>
                                </div>
                                <span class="text-[14px] font-bold" style="color: {{ $color }}">
                                    {{ $lesson->completionPct }}%
                                </span>
                            </div>
                            <div class="w-full h-2.5 bg-[#f1f5f9] rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700"
                                     style="width: {{ $lesson->completionPct }}%; background-color: {{ $color }}">
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- ════════════════ RIGHT SIDEBAR ════════════════ --}}
    <div class="w-[320px] flex-shrink-0 flex flex-col space-y-8">

        {{-- Class Insights / Top Performer --}}
        <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
            <div class="flex items-center space-x-3 mb-8">
                <span class="material-symbols-outlined icon-outline text-[22px] text-[#facc15]">psychology</span>
                <span class="text-[15px] font-bold text-[#1e293b]">Class Insights</span>
            </div>

            @if($topPerformer)
                @php
                    $initials = strtoupper(substr($topPerformer->first_name, 0, 1) . substr($topPerformer->last_name, 0, 1));
                @endphp
                <div class="bg-[#f8fafc] rounded-[20px] p-6 mb-5">
                    <h4 class="text-[9px] font-bold tracking-[0.15em] uppercase text-slate-400 mb-4">Top Performer</h4>
                    <div class="flex items-center space-x-3 mb-3">
                        <div class="w-9 h-9 rounded-full bg-[#0d326b] text-white flex items-center justify-center text-[11px] font-bold">
                            {{ $initials }}
                        </div>
                        <span class="text-[14px] font-bold text-black">{{ $topPerformer->first_name }} {{ $topPerformer->last_name }}</span>
                    </div>
                    <p class="text-[12px] text-slate-500 font-medium leading-relaxed">
                        Completed <strong>{{ $topPerformer->completedLessons }}</strong> lesson(s)
                        @if($topPerformer->avgScore > 0)
                            with an avg quiz score of <strong>{{ $topPerformer->avgScore }} pts</strong>.
                        @else
                            — keep it up!
                        @endif
                    </p>
                </div>

                {{-- All students mini list --}}
                <h4 class="text-[9px] font-bold tracking-[0.15em] uppercase text-slate-400 mb-3">All Students</h4>
                <div class="space-y-3">
                    @foreach($lessonCompletion->take(1) as $lesson)
                        @php
                            // Show per-student completion for the first lesson as a sample
                        @endphp
                    @endforeach
                    <div class="text-[12px] text-slate-500">
                        <span class="font-bold text-[#0d326b]">{{ $activeStudents }}</span> active this week out of
                        <span class="font-bold text-[#0d326b]">{{ $totalStudents }}</span> total
                    </div>
                </div>
            @else
                <p class="text-[13px] text-slate-400 text-center py-4">No student data yet.</p>
            @endif
        </div>

        {{-- Alerts --}}
        <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
            <h3 class="text-[16px] font-bold text-black mb-8">Class Alerts</h3>

            <div class="relative">
                <div class="absolute left-[9px] top-4 bottom-4 w-0.5 bg-slate-100"></div>
                <div class="space-y-6">
                    @foreach($alerts as $alert)
                        @php
                            $dotColor = match($alert['color']) {
                                'yellow' => 'border-[#facc15]',
                                'red'    => 'border-[#ef4444]',
                                'green'  => 'border-[#22c55e]',
                                default  => 'border-[#3b82f6]',
                            };
                        @endphp
                        <div class="relative flex items-start space-x-4">
                            <div class="w-5 h-5 rounded-full border-[3px] {{ $dotColor }} bg-white relative z-10 mt-0.5 flex-shrink-0"></div>
                            <div>
                                <h4 class="text-[13px] font-bold text-black mb-0.5 leading-snug">{{ $alert['title'] }}</h4>
                                <p class="text-[11px] text-slate-500 font-medium mb-1">{{ $alert['sub'] }}</p>
                                <p class="text-[9px] font-bold text-slate-400 tracking-[0.1em] uppercase">{{ $alert['time'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
