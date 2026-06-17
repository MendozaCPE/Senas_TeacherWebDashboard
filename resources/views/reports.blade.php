@extends('layouts.app')
@section('bg-class', 'bg-[#f8fafc]')
@section('title', 'Reports')
@section('content')

<div class="w-full">

    {{-- Header --}}
    <div class="mb-8">
        <h3 class="text-[11px] font-bold text-[#0d326b] tracking-[0.15em] uppercase mb-2">REPORTS</h3>
        <h2 class="text-[36px] font-medium text-[#0d326b] leading-tight">Academic Reports</h2>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-5 py-3.5 rounded-xl text-[13px] font-medium flex items-center space-x-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('reports') }}" id="filterForm">
        <div class="bg-[#f1f5f9] rounded-[32px] p-8 mb-10 shadow-inner">
            <div class="grid grid-cols-2 gap-8 mb-6">
                {{-- Student Filter --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Student</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-4 flex items-center text-slate-400">
                            <span class="material-symbols-outlined icon-outline text-[18px]">person</span>
                        </span>
                        <select name="student_id"
                                class="appearance-none w-full bg-white text-slate-700 text-[14px] font-medium py-3.5 pl-12 pr-10 rounded-xl outline-none focus:ring-2 focus:ring-[#0d326b]/20 cursor-pointer shadow-sm">
                            <option value="all" {{ request('student_id','all')==='all'?'selected':'' }}>All Students</option>
                            @foreach($students as $s)
                                <option value="{{ $s->student_id }}" {{ request('student_id')==$s->student_id?'selected':'' }}>
                                    {{ $s->first_name }} {{ $s->last_name }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-400 pointer-events-none">expand_more</span>
                    </div>
                </div>

                {{-- Lesson Filter --}}
                <div>
                    <label class="block text-[11px] font-bold text-slate-500 tracking-[0.1em] uppercase mb-2">Lesson / Module</label>
                    <div class="relative">
                        <select name="lesson_id"
                                class="appearance-none w-full bg-white text-slate-700 text-[14px] font-medium py-3.5 pl-6 pr-10 rounded-xl outline-none focus:ring-2 focus:ring-[#0d326b]/20 cursor-pointer shadow-sm">
                            <option value="all" {{ request('lesson_id','all')==='all'?'selected':'' }}>All Lessons</option>
                            @foreach($lessons as $l)
                                <option value="{{ $l->lesson_id }}" {{ request('lesson_id')==$l->lesson_id?'selected':'' }}>
                                    {{ $l->title }}
                                </option>
                            @endforeach
                        </select>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-400 pointer-events-none">expand_more</span>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <p class="text-[13px] text-slate-500 font-medium">
                    Showing <span class="font-bold text-[#0d326b]">{{ $reportRows->count() }}</span> progress record(s)
                </p>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('reports') }}" class="px-5 py-3 rounded-xl border border-slate-200 text-slate-600 text-[13px] font-bold hover:bg-white transition-colors">
                        Clear
                    </a>
                    <button type="submit" class="bg-[#0d326b] hover:bg-[#154188] text-white px-8 py-3 rounded-xl text-[14px] font-bold transition-colors flex items-center space-x-2 shadow-sm">
                        <span class="material-symbols-outlined icon-outline text-[18px]">filter_alt</span>
                        <span>Apply Filter</span>
                    </button>
                </div>
            </div>
        </div>
    </form>

    {{-- Report Table --}}
    <div class="bg-white rounded-[32px] shadow-[0_4px_20px_rgba(0,0,0,0.02)] overflow-hidden">
        <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 bg-[#f8fafc]">
            <h3 class="text-[18px] font-bold text-[#1e293b]">Student Progress Report</h3>
            <div class="flex items-center space-x-3">
                {{-- Export PDF button --}}
                <a href="{{ route('reports.export-pdf', request()->query()) }}"
                   class="flex items-center space-x-1.5 bg-[#0d326b] text-white px-4 py-2.5 rounded-xl text-[13px] font-bold shadow-sm hover:bg-[#154188] transition-colors">
                    <span class="material-symbols-outlined icon-outline text-[18px]">picture_as_pdf</span>
                    <span>Export PDF</span>
                </a>
            </div>
        </div>

        @if($reportRows->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                <span class="material-symbols-outlined icon-outline text-[56px] mb-4">description</span>
                <p class="text-[15px] font-bold mb-1">No data found</p>
                <p class="text-[13px]">Try adjusting the filters above.</p>
            </div>
        @else
            <table class="w-full text-left border-collapse">
                <thead class="bg-[#f8fafc]">
                    <tr class="border-b border-slate-100">
                        <th class="py-4 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase">Student</th>
                        <th class="py-4 px-8 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase">Lesson</th>
                        <th class="py-4 px-6 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase">Progress</th>
                        <th class="py-4 px-6 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase w-28">Status</th>
                        <th class="py-4 px-6 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase w-24">Quiz</th>
                        <th class="py-4 px-6 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase w-36">Last Active</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($reportRows as $row)
                        @php
                            $s      = $row->student;
                            $initials = $s ? strtoupper(substr($s->first_name,0,1).substr($s->last_name,0,1)) : '??';
                            $totalSteps = 7; // avg lesson steps
                            $stepPct = $totalSteps > 0 ? min(100, round(($row->current_step / $totalSteps)*100)) : 0;
                        @endphp
                        <tr class="hover:bg-slate-50/50 transition-colors">
                            {{-- Student --}}
                            <td class="py-5 px-8">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-full bg-[#0d326b] text-white flex items-center justify-center text-[11px] font-bold flex-shrink-0">
                                        {{ $initials }}
                                    </div>
                                    <div>
                                        <p class="text-[14px] font-bold text-[#1e293b] leading-tight">{{ $row->studentName }}</p>
                                        <p class="text-[11px] text-slate-400 font-medium">{{ optional($s)->grade_level ?? 'N/A' }}</p>
                                    </div>
                                </div>
                            </td>
                            {{-- Lesson --}}
                            <td class="py-5 px-8">
                                <p class="text-[13px] font-bold text-[#1e293b] leading-tight">{{ $row->lessonTitle }}</p>
                                <p class="text-[11px] text-slate-400 font-medium capitalize">{{ optional($row->lesson)->lesson_type ?? '' }}</p>
                            </td>
                            {{-- Step Progress --}}
                            <td class="py-5 px-6">
                                <div class="flex items-center space-x-2">
                                    <div class="w-24 h-1.5 bg-[#f1f5f9] rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $row->lesson_completed ? 'bg-green-500' : 'bg-[#3b82f6]' }}"
                                             style="width: {{ $stepPct }}%"></div>
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-500">{{ $stepPct }}%</span>
                                </div>
                            </td>
                            {{-- Status --}}
                            <td class="py-5 px-6">
                                @if($row->lesson_completed)
                                    <span class="px-2.5 py-1 bg-[#dcfce7] text-[#166534] text-[10px] font-bold rounded-lg uppercase tracking-wider">Completed</span>
                                @else
                                    <span class="px-2.5 py-1 bg-[#dbeafe] text-[#1d4ed8] text-[10px] font-bold rounded-lg uppercase tracking-wider">In Progress</span>
                                @endif
                            </td>
                            {{-- Quiz --}}
                            <td class="py-5 px-6">
                                @if($row->quiz_completed)
                                    <span class="text-[13px] font-bold text-[#0d326b]">{{ $row->quiz_score }} pts</span>
                                @else
                                    <span class="text-[12px] text-slate-400 font-medium">Pending</span>
                                @endif
                            </td>
                            {{-- Last Active --}}
                            <td class="py-5 px-6">
                                <span class="text-[12px] font-medium text-slate-500">{{ $row->lastAccessed }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Summary Cards --}}
    @if($reportRows->isNotEmpty())
        @php
            $totalRows     = $reportRows->count();
            $completedRows = $reportRows->where('lesson_completed', 1)->count();
            $quizTaken     = $reportRows->where('quiz_completed', 1)->count();
            $avgScore      = $reportRows->where('quiz_completed', 1)->avg('quiz_score') ?? 0;
        @endphp
        <div class="grid grid-cols-4 gap-5 mt-6">
            <div class="bg-white rounded-[20px] p-6 shadow-[0_2px_12px_rgba(0,0,0,0.04)]">
                <p class="text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase mb-2">Total Records</p>
                <p class="text-[28px] font-bold text-[#0d326b]">{{ $totalRows }}</p>
            </div>
            <div class="bg-white rounded-[20px] p-6 shadow-[0_2px_12px_rgba(0,0,0,0.04)]">
                <p class="text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase mb-2">Completed</p>
                <p class="text-[28px] font-bold text-[#22c55e]">{{ $completedRows }}</p>
            </div>
            <div class="bg-white rounded-[20px] p-6 shadow-[0_2px_12px_rgba(0,0,0,0.04)]">
                <p class="text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase mb-2">Quizzes Taken</p>
                <p class="text-[28px] font-bold text-[#3b82f6]">{{ $quizTaken }}</p>
            </div>
            <div class="bg-white rounded-[20px] p-6 shadow-[0_2px_12px_rgba(0,0,0,0.04)]">
                <p class="text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase mb-2">Avg Quiz Score</p>
                <p class="text-[28px] font-bold text-[#f59e0b]">{{ number_format($avgScore, 1) }}</p>
            </div>
        </div>
    @endif
</div>
@endsection
