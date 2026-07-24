@extends('layouts.app')
@section('bg-class', 'bg-[#f8fafc]')
@section('title', 'Reports')
@section('content')

<style>
:root {
    --navy-950: #071c3f;
    --navy-900: #0d326b;
    --navy-700: #1e4b8f;
    --navy-500: #1a6fd4;
    --navy-400: #3b82f6;
    --navy-300: #93c5fd;
    --navy-200: #bfdbfe;
    --navy-100: #dbeafe;
    --navy-50:  #eff6ff;
}

.stat-card { border-radius: 20px; padding: 20px; position: relative; overflow: hidden; transition: transform .2s, box-shadow .2s; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(13,50,107,.12); }

/* ── KPI Ready to Promote (golden gradient) ────────────────────────────── */
.kpi-ready {
    background: linear-gradient(135deg, #f59e0b 0%, #facc15 50%, #fbbf24 100%);
}

/* ── Filter styles (matching Reports page) ──────────────────────────────── */
.filter-container {
    background: #fff;
    border-radius: 16px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 1px 2px rgba(13,50,107,.04);
    padding: 14px 18px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-select {
    appearance: none;
    background: #f1f5f9;
    color: #1e293b;
    font-size: 12px;
    font-weight: 600;
    padding: 8px 32px 8px 14px;
    border-radius: 9999px;
    border: none;
    outline: none;
    cursor: pointer;
    transition: all .2s;
    position: relative;
}
.filter-select:hover { background: #e2e8f0; }
.filter-select:focus { ring: 2px solid #0d326b; }

.filter-wrap {
    position: relative;
    display: inline-block;
}
.filter-wrap .material-symbols-outlined {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    color: #94a3b8;
    pointer-events: none;
}

.filter-btn {
    background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%);
    color: #fff;
    padding: 8px 20px;
    border-radius: 9999px;
    font-size: 12px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all .2s;
    display: flex;
    align-items: center;
    gap: 6px;
}
.filter-btn:hover { opacity: .9; box-shadow: 0 4px 12px rgba(13,50,107,.25); }

.filter-reset {
    padding: 8px 16px;
    border-radius: 9999px;
    border: 1px solid #e2e8f0;
    background: #fff;
    color: #64748b;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all .2s;
    text-decoration: none;
    display: inline-block;
}
.filter-reset:hover { background: #f8fafc; border-color: #cbd5e1; }

.export-btn {
    background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%);
    color: #fff;
    padding: 8px 20px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all .2s;
    display: flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    box-shadow: 0 2px 8px rgba(13,50,107,.15);
}
.export-btn:hover { opacity: .9; box-shadow: 0 4px 16px rgba(13,50,107,.25); }

/* ── Table Styles ────────────────────────────────────────────────────────── */
.table-wrap {
    overflow-x: auto;
}

.report-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
}

.report-table thead th {
    padding: 14px 16px;
    text-align: left;
    font-size: 10px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    border-bottom: 1px solid #f1f5f9;
    background: #fafcff;
}

.report-table tbody td {
    padding: 14px 16px;
    border-bottom: 1px solid #f8fafc;
    vertical-align: middle;
}

.report-table tbody tr {
    transition: background .15s;
    cursor: pointer;
}
.report-table tbody tr:hover {
    background: #f8fafc;
}

/* ── Status Badge ────────────────────────────────────────────────────────── */
.badge-status {
    display: inline-block;
    padding: 3px 12px;
    border-radius: 9999px;
    font-size: 9px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
}
.badge-status.completed { background: #0d326b; color: #fff; }
.badge-status.in-progress { background: #dbeafe; color: #0d326b; }
.badge-status.pending { background: #f1f5f9; color: #64748b; }
</style>

<div class="space-y-6">


    {{-- Flash messages --}}
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-5 py-3.5 rounded-xl text-[13px] font-medium flex items-center space-x-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- ══════════ STAT CARDS (TOP) ══════════ --}}
    @php
        $totalStudentsShown = $studentReports->count();
        $fullyCompleted     = $studentReports->where('overallPct', 100)->count();
        $totalQuizzesTaken  = $studentReports->sum('quizzesTaken');
        $scoredReports      = $studentReports->filter(fn($r) => $r['quizzesTaken'] > 0);
        $avgScoreOverall    = $scoredReports->isNotEmpty() ? $scoredReports->avg('avgScore') : 0;
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Total Students (hero card - navy gradient) --}}
        <div class="stat-card text-white" style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%)">
            <div class="absolute -top-7 -right-7 w-28 h-28 bg-white/5 rounded-full"></div>
            <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-white/5 rounded-full"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="material-symbols-outlined text-white/50 text-[15px]">group</span>
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest">Total Students</p>
                    </div>
                    <p class="text-[40px] font-black text-white leading-none">{{ $totalStudentsShown }}</p>
                    <p class="text-[11px] font-semibold text-[#facc15] mt-2">in current view</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-white text-[28px]">school</span>
                </div>
            </div>
        </div>

        {{-- Fully Completed (navy card) --}}
        <div class="stat-card bg-white border border-slate-100 shadow-sm">
            <div class="flex items-center gap-1.5 mb-2">
                <span class="material-symbols-outlined text-[#0d326b] text-[16px]">check_circle</span>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Fully Completed</p>
            </div>
            <p class="text-[40px] font-black text-[#0d326b] leading-none">{{ $fullyCompleted }}</p>
            <p class="text-[10px] text-slate-400 font-medium mt-0.5">student{{ $fullyCompleted !== 1 ? 's' : '' }} at 100%</p>
        </div>

        {{-- Quizzes Taken (navy card) --}}
        <div class="stat-card bg-white border border-slate-100 shadow-sm">
            <div class="flex items-center gap-1.5 mb-2">
                <span class="material-symbols-outlined text-[#1a6fd4] text-[16px]">quiz</span>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Quizzes Taken</p>
            </div>
            <p class="text-[40px] font-black text-[#1a6fd4] leading-none">{{ $totalQuizzesTaken }}</p>
            <p class="text-[10px] text-slate-400 font-medium mt-0.5">total attempts</p>
        </div>

        {{-- Avg Quiz Score (golden gradient - matching Senya Tip) --}}
        <div class="stat-card kpi-ready">
            <div class="absolute -top-7 -right-7 w-28 h-28 bg-[#0d326b]/5 rounded-full"></div>
            <div class="relative z-10">
                <div class="flex items-center gap-1.5 mb-2">
                    <span class="material-symbols-outlined text-amber-700/60 text-[15px]">trending_up</span>
                    <p class="text-[10px] font-bold text-amber-800/70 uppercase tracking-widest">Avg Quiz Score</p>
                </div>
                <p class="text-[40px] font-black text-[#92400e] leading-none">{{ number_format($avgScoreOverall, 1) }}</p>
                <p class="text-[11px] font-semibold text-amber-800 mt-2">points per attempt</p>
            </div>
        </div>
    </div>

    {{-- ══════════ FILTERS (matching Analytics/Students) ══════════ --}}
    @php $rf = session('reports_filters', []); @endphp
    <form method="POST" action="{{ route('reports.filter') }}" id="filterForm">
        @csrf
        <div class="filter-container">
            <div class="filter-group">
                <div class="filter-wrap">
                    <select name="student_id" class="filter-select">
                        <option value="all" {{ ($rf['student_id'] ?? 'all') === 'all' ? 'selected' : '' }}>All Students</option>
                        @foreach($students as $s)
                            <option value="{{ $s->student_id }}" {{ ($rf['student_id'] ?? '') == $s->student_id ? 'selected' : '' }}>
                                {{ $s->first_name }} {{ $s->last_name }}
                            </option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined">expand_more</span>
                </div>

                <div class="filter-wrap">
                    <select name="lesson_id" class="filter-select">
                        <option value="all" {{ ($rf['lesson_id'] ?? 'all') === 'all' ? 'selected' : '' }}>All Lessons</option>
                        @foreach($lessons as $l)
                            <option value="{{ $l->lesson_id }}" {{ ($rf['lesson_id'] ?? '') == $l->lesson_id ? 'selected' : '' }}>
                                {{ $l->title }}
                            </option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined">expand_more</span>
                </div>

                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider shrink-0 ml-2">
                    {{ $studentReports->count() }} student{{ $studentReports->count() !== 1 ? 's' : '' }}
                </span>

                <a href="{{ route('reports') }}" onclick="event.preventDefault(); fetch('{{ route('reports.filter') }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}, body:JSON.stringify({student_id:'all',lesson_id:'all'})}).then(()=>window.location.href='{{ route('reports') }}')" class="filter-reset">Clear</a>
                <button type="submit" class="filter-btn">
                    <span class="material-symbols-outlined text-[16px]">filter_alt</span>
                    Apply Filter
                </button>
            </div>

            <a href="{{ route('reports.export-pdf') }}" class="export-btn">
                <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                Export PDF
            </a>
        </div>
    </form>

    {{-- ══════════ REPORT TABLE ══════════ --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/60">
            <h3 class="text-[14px] font-bold text-[#0d326b]">Student Progress Report</h3>
        </div>

        @if($studentReports->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-slate-400">
                <span class="material-symbols-outlined icon-outline text-[56px] mb-4">description</span>
                <p class="text-[15px] font-bold mb-1">No data found</p>
                <p class="text-[13px]">Try adjusting the filters above.</p>
            </div>
        @else
            <div class="table-wrap">
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Overall Progress</th>
                            <th style="width:120px;">Lessons</th>
                            <th style="width:100px;">Quizzes</th>
                            <th style="width:100px;">Avg Score</th>
                            <th style="width:140px;">Last Active</th>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($studentReports as $index => $row)
                            <tr onclick="openStudentModal({{ $index }})">
                                <td>
                                    <div class="flex items-center space-x-3">
                                        <div class="w-9 h-9 rounded-full bg-[#0d326b] text-white flex items-center justify-center text-[11px] font-bold flex-shrink-0">
                                            {{ $row['initials'] }}
                                        </div>
                                        <div>
                                            <p class="text-[13px] font-bold text-[#0d326b] leading-tight">{{ $row['studentName'] }}</p>
                                            <p class="text-[10px] text-slate-400 font-medium">{{ $row['gradeLevel'] }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="flex items-center space-x-2">
                                        <div class="w-28 h-1.5 bg-[#f1f5f9] rounded-full overflow-hidden">
                                            <div class="h-full rounded-full {{ $row['overallPct'] >= 100 ? 'bg-[#0d326b]' : 'bg-[#1a6fd4]' }}"
                                                 style="width: {{ $row['overallPct'] }}%"></div>
                                        </div>
                                        <span class="text-[11px] font-bold text-slate-500">{{ $row['overallPct'] }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-[13px] font-bold text-[#1e293b]">{{ $row['completedLessons'] }}</span>
                                    <span class="text-[12px] text-slate-400 font-medium">/ {{ $row['totalLessons'] }}</span>
                                </td>
                                <td>
                                    <span class="text-[13px] font-bold text-[#1e293b]">{{ $row['quizzesTaken'] }}</span>
                                </td>
                                <td>
                                    @if($row['quizzesTaken'] > 0)
                                        <span class="text-[13px] font-bold text-[#0d326b]">{{ $row['avgScore'] }} pts</span>
                                    @else
                                        <span class="text-[12px] text-slate-400 font-medium">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-[12px] font-medium text-slate-500">{{ $row['lastAccessed'] }}</span>
                                </td>
                                <td class="text-right">
                                    <span class="material-symbols-outlined icon-outline text-[18px] text-slate-300">chevron_right</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>

{{-- ================= Student Performance Modal ================= --}}
<div id="studentModalOverlay"
     class="fixed inset-0 bg-slate-900/50 backdrop-blur-[2px] z-50 hidden items-center justify-center p-6"
     onclick="if(event.target===this) closeStudentModal()">
    <div class="bg-white rounded-[28px] w-full max-w-2xl max-h-[85vh] overflow-hidden shadow-2xl flex flex-col">

        <div class="flex items-start justify-between px-8 py-6 border-b border-slate-100 bg-slate-50/60">
            <div class="flex items-center space-x-4">
                <div id="modalInitials" class="w-12 h-12 rounded-full bg-[#0d326b] text-white flex items-center justify-center text-[14px] font-bold flex-shrink-0"></div>
                <div>
                    <p id="modalStudentName" class="text-[18px] font-bold text-[#0d326b] leading-tight"></p>
                    <p id="modalGradeLevel" class="text-[12px] text-slate-400 font-medium"></p>
                </div>
            </div>
            <button onclick="closeStudentModal()" class="text-slate-400 hover:text-slate-600 transition-colors">
                <span class="material-symbols-outlined icon-outline text-[24px]">close</span>
            </button>
        </div>

        <div class="overflow-y-auto px-8 py-6 space-y-6">

            <div>
                <h4 class="text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase mb-3">Overall Performance</h4>
                <div class="grid grid-cols-4 gap-4">
                    <div class="bg-[#f1f5f9] rounded-2xl p-4">
                        <p class="text-[10px] font-bold text-slate-400 tracking-[0.08em] uppercase mb-1">Progress</p>
                        <p id="modalOverallPct" class="text-[22px] font-black text-[#0d326b]"></p>
                    </div>
                    <div class="bg-[#f1f5f9] rounded-2xl p-4">
                        <p class="text-[10px] font-bold text-slate-400 tracking-[0.08em] uppercase mb-1">Lessons</p>
                        <p id="modalLessonsCount" class="text-[22px] font-black text-[#1e293b]"></p>
                    </div>
                    <div class="bg-[#f1f5f9] rounded-2xl p-4">
                        <p class="text-[10px] font-bold text-slate-400 tracking-[0.08em] uppercase mb-1">Quizzes</p>
                        <p id="modalQuizzesCount" class="text-[22px] font-black text-[#1a6fd4]"></p>
                    </div>
                    <div class="bg-[#f1f5f9] rounded-2xl p-4">
                        <p class="text-[10px] font-bold text-slate-400 tracking-[0.08em] uppercase mb-1">Avg Score</p>
                        <p id="modalAvgScore" class="text-[22px] font-black text-[#0d326b]"></p>
                    </div>
                </div>
                <div class="mt-3 flex items-center space-x-2">
                    <div class="flex-1 h-2 bg-[#f1f5f9] rounded-full overflow-hidden">
                        <div id="modalOverallBar" class="h-full rounded-full bg-[#1a6fd4]"></div>
                    </div>
                    <span id="modalLastActive" class="text-[11px] font-medium text-slate-400 whitespace-nowrap"></span>
                </div>
            </div>

            <div>
                <h4 class="text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase mb-3">Lesson Breakdown</h4>
                <div id="modalLessonList" class="space-y-2"></div>
            </div>

        </div>
    </div>
</div>

<script>
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
        bar.className = 'h-full rounded-full ' + (data.overallPct >= 100 ? 'bg-[#0d326b]' : 'bg-[#1a6fd4]');

        const listEl = document.getElementById('modalLessonList');
        listEl.innerHTML = '';

        if (!data.lessons || data.lessons.length === 0) {
            listEl.innerHTML = '<p class="text-[13px] text-slate-400 font-medium py-4 text-center">No lesson activity yet.</p>';
        } else {
            data.lessons.forEach(function (lesson) {
                const statusBadge = lesson.completed
                    ? '<span class="badge-status completed">Completed</span>'
                    : '<span class="badge-status in-progress">In Progress</span>';

                const quizBadge = lesson.quizCompleted
                    ? '<span class="text-[12px] font-bold text-[#0d326b]">' + lesson.quizScore + ' pts</span>'
                    : '<span class="text-[11px] text-slate-400 font-medium">Pending</span>';

                const barColor = lesson.completed ? 'bg-[#0d326b]' : 'bg-[#1a6fd4]';

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