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
                    Showing <span class="font-bold text-[#0d326b]">{{ $studentReports->count() }}</span> student(s)
                </p>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('reports') }}" class="px-5 py-3 rounded-xl border border-slate-200 text-slate-600 text-[13px] font-bold hover:bg-white transition-colors">
                        Clear
                    </a>
                    <button type="submit"
                            class="text-white px-8 py-3 rounded-xl text-[14px] font-bold transition-all duration-300 flex items-center space-x-2 shadow-sm hover:shadow-md"
                            style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%);">
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
   class="text-white px-4 py-2.5 rounded-xl text-[13px] font-bold transition-all duration-300 flex items-center space-x-1.5 shadow-sm hover:shadow-md"
   style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%);">
    <span class="material-symbols-outlined icon-outline text-[18px]">picture_as_pdf</span>
    <span>Export PDF</span>
</a>
            </div>
        </div>

        @if($studentReports->isEmpty())
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
                        <th class="py-4 px-6 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase">Overall Progress</th>
                        <th class="py-4 px-6 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase w-32">Lessons</th>
                        <th class="py-4 px-6 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase w-28">Quizzes</th>
                        <th class="py-4 px-6 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase w-28">Avg Score</th>
                        <th class="py-4 px-6 text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase w-36">Last Active</th>
                        <th class="py-4 px-6 w-10"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($studentReports as $index => $row)
                        <tr class="hover:bg-slate-50/50 transition-colors cursor-pointer"
                            onclick="openStudentModal({{ $index }})">
                            {{-- Student --}}
                            <td class="py-5 px-8">
                                <div class="flex items-center space-x-3">
                                    <div class="w-9 h-9 rounded-full bg-[#0d326b] text-white flex items-center justify-center text-[11px] font-bold flex-shrink-0">
                                        {{ $row['initials'] }}
                                    </div>
                                    <div>
                                        <p class="text-[14px] font-bold text-[#1e293b] leading-tight">{{ $row['studentName'] }}</p>
                                        <p class="text-[11px] text-slate-400 font-medium">{{ $row['gradeLevel'] }}</p>
                                    </div>
                                </div>
                            </td>
                            {{-- Overall Progress --}}
                            <td class="py-5 px-6">
                                <div class="flex items-center space-x-2">
                                    <div class="w-28 h-1.5 bg-[#f1f5f9] rounded-full overflow-hidden">
                                        <div class="h-full rounded-full {{ $row['overallPct'] >= 100 ? 'bg-green-500' : 'bg-[#3b82f6]' }}"
                                             style="width: {{ $row['overallPct'] }}%"></div>
                                    </div>
                                    <span class="text-[11px] font-bold text-slate-500">{{ $row['overallPct'] }}%</span>
                                </div>
                            </td>
                            {{-- Lessons --}}
                            <td class="py-5 px-6">
                                <span class="text-[13px] font-bold text-[#1e293b]">{{ $row['completedLessons'] }}</span>
                                <span class="text-[12px] text-slate-400 font-medium">/ {{ $row['totalLessons'] }} done</span>
                            </td>
                            {{-- Quizzes --}}
                            <td class="py-5 px-6">
                                <span class="text-[13px] font-bold text-[#1e293b]">{{ $row['quizzesTaken'] }}</span>
                                <span class="text-[12px] text-slate-400 font-medium">taken</span>
                            </td>
                            {{-- Avg Score --}}
                            <td class="py-5 px-6">
                                @if($row['quizzesTaken'] > 0)
                                    <span class="text-[13px] font-bold text-[#0d326b]">{{ $row['avgScore'] }} pts</span>
                                @else
                                    <span class="text-[12px] text-slate-400 font-medium">—</span>
                                @endif
                            </td>
                            {{-- Last Active --}}
                            <td class="py-5 px-6">
                                <span class="text-[12px] font-medium text-slate-500">{{ $row['lastAccessed'] }}</span>
                            </td>
                            {{-- Chevron --}}
                            <td class="py-5 px-6 text-right">
                                <span class="material-symbols-outlined icon-outline text-[18px] text-slate-300">chevron_right</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Summary Cards --}}
    @if($studentReports->isNotEmpty())
        @php
            $totalStudentsShown = $studentReports->count();
            $fullyCompleted     = $studentReports->where('overallPct', 100)->count();
            $totalQuizzesTaken  = $studentReports->sum('quizzesTaken');
            $scoredReports      = $studentReports->filter(fn($r) => $r['quizzesTaken'] > 0);
            $avgScoreOverall    = $scoredReports->isNotEmpty() ? $scoredReports->avg('avgScore') : 0;
        @endphp
        <div class="grid grid-cols-4 gap-5 mt-6">
            <div class="bg-white rounded-[20px] p-6 shadow-[0_2px_12px_rgba(0,0,0,0.04)]">
                <p class="text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase mb-2">Total Students</p>
                <p class="text-[28px] font-bold text-[#0d326b]">{{ $totalStudentsShown }}</p>
            </div>
            <div class="bg-white rounded-[20px] p-6 shadow-[0_2px_12px_rgba(0,0,0,0.04)]">
                <p class="text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase mb-2">Fully Completed</p>
                <p class="text-[28px] font-bold text-[#22c55e]">{{ $fullyCompleted }}</p>
            </div>
            <div class="bg-white rounded-[20px] p-6 shadow-[0_2px_12px_rgba(0,0,0,0.04)]">
                <p class="text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase mb-2">Quizzes Taken</p>
                <p class="text-[28px] font-bold text-[#3b82f6]">{{ $totalQuizzesTaken }}</p>
            </div>
            <div class="bg-white rounded-[20px] p-6 shadow-[0_2px_12px_rgba(0,0,0,0.04)]">
                <p class="text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase mb-2">Avg Quiz Score</p>
                <p class="text-[28px] font-bold text-[#f59e0b]">{{ number_format($avgScoreOverall, 1) }}</p>
            </div>
        </div>
    @endif
</div>

{{-- ================= Student Performance Modal ================= --}}
<div id="studentModalOverlay"
     class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 hidden items-center justify-center p-6"
     onclick="if(event.target===this) closeStudentModal()">
    <div class="bg-white rounded-[28px] w-full max-w-2xl max-h-[85vh] overflow-hidden shadow-2xl flex flex-col">

        {{-- Modal Header --}}
        <div class="flex items-start justify-between px-8 py-6 border-b border-slate-100 bg-[#f8fafc]">
            <div class="flex items-center space-x-4">
                <div id="modalInitials" class="w-12 h-12 rounded-full bg-[#0d326b] text-white flex items-center justify-center text-[14px] font-bold flex-shrink-0"></div>
                <div>
                    <p id="modalStudentName" class="text-[18px] font-bold text-[#1e293b] leading-tight"></p>
                    <p id="modalGradeLevel" class="text-[12px] text-slate-400 font-medium"></p>
                </div>
            </div>
            <button onclick="closeStudentModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <span class="material-symbols-outlined icon-outline text-[24px]">close</span>
            </button>
        </div>

        {{-- Modal Body --}}
        <div class="overflow-y-auto px-8 py-6 space-y-6">

            {{-- Overall performance summary --}}
            <div>
                <h4 class="text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase mb-3">Overall Performance</h4>
                <div class="grid grid-cols-4 gap-4">
                    <div class="bg-[#f1f5f9] rounded-2xl p-4">
                        <p class="text-[10px] font-bold text-slate-400 tracking-[0.08em] uppercase mb-1">Progress</p>
                        <p id="modalOverallPct" class="text-[22px] font-bold text-[#0d326b]"></p>
                    </div>
                    <div class="bg-[#f1f5f9] rounded-2xl p-4">
                        <p class="text-[10px] font-bold text-slate-400 tracking-[0.08em] uppercase mb-1">Lessons</p>
                        <p id="modalLessonsCount" class="text-[22px] font-bold text-[#1e293b]"></p>
                    </div>
                    <div class="bg-[#f1f5f9] rounded-2xl p-4">
                        <p class="text-[10px] font-bold text-slate-400 tracking-[0.08em] uppercase mb-1">Quizzes</p>
                        <p id="modalQuizzesCount" class="text-[22px] font-bold text-[#3b82f6]"></p>
                    </div>
                    <div class="bg-[#f1f5f9] rounded-2xl p-4">
                        <p class="text-[10px] font-bold text-slate-400 tracking-[0.08em] uppercase mb-1">Avg Score</p>
                        <p id="modalAvgScore" class="text-[22px] font-bold text-[#f59e0b]"></p>
                    </div>
                </div>
                <div class="mt-3 flex items-center space-x-2">
                    <div class="flex-1 h-2 bg-[#f1f5f9] rounded-full overflow-hidden">
                        <div id="modalOverallBar" class="h-full rounded-full bg-[#3b82f6]"></div>
                    </div>
                    <span id="modalLastActive" class="text-[11px] font-medium text-slate-400 whitespace-nowrap"></span>
                </div>
            </div>

            {{-- Per-lesson breakdown --}}
            <div>
                <h4 class="text-[11px] font-bold text-slate-400 tracking-[0.1em] uppercase mb-3">Lesson Breakdown</h4>
                <div id="modalLessonList" class="space-y-2"></div>
            </div>

        </div>
    </div>
</div>

<script>
    // Embed the per-student report data (built server-side) for instant modal rendering — no extra requests.
    const studentReportData = @json($studentReports->values());

    function openStudentModal(index) {
        const data = studentReportData[index];
        if (!data) return;

        document.getElementById('modalInitials').textContent = data.initials;
        document.getElementById('modalStudentName').textContent = data.studentName;
        document.getElementById('modalGradeLevel').textContent = data.gradeLevel;

        document.getElementById('modalOverallPct').textContent = data.overallPct + '%';
        document.getElementById('modalLessonsCount').textContent = data.completedLessons + ' / ' + data.totalLessons;
        document.getElementById('modalQuizzesCount').textContent = data.quizzesTaken;
        document.getElementById('modalAvgScore').textContent = data.quizzesTaken > 0 ? data.avgScore + ' pts' : '—';
        document.getElementById('modalLastActive').textContent = 'Last active ' + data.lastAccessed;

        const bar = document.getElementById('modalOverallBar');
        bar.style.width = data.overallPct + '%';
        bar.className = 'h-full rounded-full ' + (data.overallPct >= 100 ? 'bg-green-500' : 'bg-[#3b82f6]');

        const listEl = document.getElementById('modalLessonList');
        listEl.innerHTML = '';

        if (!data.lessons || data.lessons.length === 0) {
            listEl.innerHTML = '<p class="text-[13px] text-slate-400 font-medium py-4 text-center">No lesson activity yet.</p>';
        } else {
            data.lessons.forEach(function (lesson) {
                const statusBadge = lesson.completed
                    ? '<span class="px-2.5 py-1 bg-[#dcfce7] text-[#166534] text-[10px] font-bold rounded-lg uppercase tracking-wider">Completed</span>'
                    : '<span class="px-2.5 py-1 bg-[#dbeafe] text-[#1d4ed8] text-[10px] font-bold rounded-lg uppercase tracking-wider">In Progress</span>';

                const quizBadge = lesson.quizCompleted
                    ? '<span class="text-[12px] font-bold text-[#0d326b]">' + lesson.quizScore + ' pts</span>'
                    : '<span class="text-[11px] text-slate-400 font-medium">Pending</span>';

                const barColor = lesson.completed ? 'bg-green-500' : 'bg-[#3b82f6]';

                const row = document.createElement('div');
                row.className = 'flex items-center justify-between bg-[#f8fafc] rounded-2xl px-5 py-3.5';
                row.innerHTML = `
                    <div class="flex-1 min-w-0 pr-4">
                        <p class="text-[13px] font-bold text-[#1e293b] leading-tight truncate">${lesson.lessonTitle}</p>
                        <p class="text-[11px] text-slate-400 font-medium capitalize">${lesson.lessonType || ''} · ${lesson.lastAccessed}</p>
                        <div class="flex items-center space-x-2 mt-1.5">
                            <div class="w-24 h-1.5 bg-white rounded-full overflow-hidden">
                                <div class="h-full rounded-full ${barColor}" style="width:${lesson.stepPct}%"></div>
                            </div>
                            <span class="text-[10px] font-bold text-slate-500">${lesson.stepPct}%</span>
                        </div>
                    </div>
                    <div class="flex flex-col items-end space-y-1.5 flex-shrink-0">
                        ${statusBadge}
                        ${quizBadge}
                    </div>
                `;
                listEl.appendChild(row);
            });
        }

        const overlay = document.getElementById('studentModalOverlay');
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }

    function closeStudentModal() {
        const overlay = document.getElementById('studentModalOverlay');
        overlay.classList.add('hidden');
        overlay.classList.remove('flex');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') closeStudentModal();
    });
</script>

@endsection