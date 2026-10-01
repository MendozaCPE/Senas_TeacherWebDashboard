@extends('layouts.teacher-leader')
@section('title', 'Class Reports')
@section('content')

<style>
.tl-rpt-card  { background:#fff; border-radius:20px; padding:20px; border:1px solid #f1f5f9; box-shadow:0 1px 3px rgba(13,50,107,.05); transition:transform .2s,box-shadow .2s; }
.tl-rpt-card:hover { transform:translateY(-2px); box-shadow:0 10px 28px rgba(13,50,107,.09); }
.stat-kpi-card { border-radius:24px; padding:22px 24px; position:relative; overflow:hidden; transition:transform .2s ease,box-shadow .2s ease; border:1px solid #f1f5f9; }
.stat-kpi-card:hover { transform:translateY(-2px); box-shadow:0 10px 26px rgba(13,50,107,.08); }
.report-table { width:100%; border-collapse:collapse; font-size:13px; }
.report-table thead th { padding:14px 16px; text-align:left; font-size:10.5px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.08em; border-bottom:1px solid #f1f5f9; background:#fafcff; }
.report-table tbody td { padding:14px 16px; border-bottom:1px solid #f8fafc; vertical-align:middle; }
.report-table tbody tr { transition:background .15s; cursor:pointer; }
.report-table tbody tr:hover { background:#f8fafc; }

/* Modal */
#tlStudentModalOverlay { position:fixed; inset:0; z-index:9999; background:rgba(15,23,42,.55); backdrop-filter:blur(3px); display:none; align-items:center; justify-content:center; padding:16px; }
#tlStudentModal { background:#fff; border-radius:28px; width:100%; max-width:900px; max-height:92vh; display:flex; flex-direction:column; overflow:hidden; box-shadow:0 25px 60px rgba(0,0,0,.2); }
.tl-modal-tab-btn { color:#94a3b8; border-bottom:2px solid transparent; padding:10px 16px; font-size:12px; font-weight:700; border-radius:0; transition:color .15s,border-color .15s; cursor:pointer; background:transparent; border-top:0; border-left:0; border-right:0; }
.tl-modal-tab-btn:hover { color:#0d326b; }
.tl-modal-tab-btn.active { color:#0d326b; border-bottom-color:#0d326b; }

/* Local skeleton (for modal lazy tabs) */
@keyframes tl-shimmer { 0%{background-position:-600px 0} 100%{background-position:600px 0} }
.tl-sk { background:#e2e8f0; background-image:linear-gradient(90deg,#e2e8f0 0,#f1f5f9 40%,#eef2f7 55%,#e2e8f0 100%); background-size:600px 100%; animation:tl-shimmer 1.6s infinite linear; border-radius:6px; display:block; }
</style>

{{-- ══════════════════════════════════════════════════════════════════════
     SKELETON
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="page-skeleton" class="flex flex-col gap-5 w-full pt-4" aria-hidden="true">
    <div class="skeleton skeleton-card w-full" style="min-height:90px;border-radius:28px;"></div>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @for($i=0;$i<4;$i++)
        <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 border border-slate-100 shadow-sm min-h-[110px] flex flex-col gap-3">
            <div class="flex items-center justify-between"><div class="skeleton h-3 rounded w-28"></div><div class="skeleton w-10 h-10 rounded-xl"></div></div>
            <div class="skeleton h-9 rounded w-20"></div>
        </div>
        @endfor
    </div>
    <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-50"><div class="skeleton h-5 rounded w-48"></div></div>
        @for($i=0;$i<6;$i++)
        <div class="flex items-center gap-4 px-6 py-4">
            <div class="skeleton skeleton-circle w-10 h-10"></div>
            <div class="flex-1 flex flex-col gap-2"><div class="skeleton h-3 rounded w-1/2"></div><div class="skeleton h-2 rounded w-1/3"></div></div>
            <div class="skeleton h-3 rounded w-20"></div><div class="skeleton h-3 rounded w-20"></div><div class="skeleton h-6 rounded-full w-24"></div>
        </div>
        @endfor
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════
     REAL CONTENT
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="skeleton-hide flex flex-col gap-5 w-full pt-4 pb-6">

    {{-- Banner --}}
    <div class="rounded-[28px] relative overflow-hidden flex items-center"
         style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%);min-height:90px">
        <div class="absolute top-0 right-40 w-40 h-40 rounded-full opacity-10 bg-white"></div>
        <div class="relative z-10 px-10 py-6 flex-1">
            <h2 class="text-[22px] font-black text-white leading-tight mb-1">Class Performance Reports</h2>
            <p class="text-[12px] text-white/70 font-medium">
                School-wide academic overview for <span class="text-white font-bold">{{ $school->name ?? 'your school' }}</span>
                @if($selectedTeacher) — viewing <span class="text-[#facc15] font-bold">{{ $selectedTeacher->first_name }} {{ $selectedTeacher->last_name }}'s class</span>@endif
            </p>
        </div>
        @if($filterTeacherId && $selectedTeacher)
        <div class="relative z-10 pr-10 flex-shrink-0">
            <a href="{{ route('teacher-leader.reports') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/15 hover:bg-white/25 text-white text-[12px] font-bold transition-colors border border-white/20">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> All Teachers
            </a>
        </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         VIEW A: Teacher List (no teacher selected)
         ═══════════════════════════════════════════════════════════════════ --}}
    @if(! $filterTeacherId)

    {{-- KPI strip --}}
    @php
        $totalTeachers   = $teachers->count();
        $totalStudents   = $teachers->sum('total_students');
        $avgSchoolScore  = $teachers->where('avg_score', '>', 0)->avg('avg_score');
        $avgSchoolScore  = round((float) $avgSchoolScore, 1);
        $onTrack         = $teachers->where('status', 'on_track')->count();
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        <div style="border-radius:24px;padding:22px 24px;background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%);border:1px solid #f1f5f9">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-white/70">Teachers</span>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                    <span class="material-symbols-outlined text-white text-[20px]">school</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none text-white tracking-tight">{{ $totalTeachers }}</p>
            <p class="text-[12px] text-white/70 font-medium mt-1">classrooms in school</p>
        </div>

        <div style="border-radius:24px;padding:22px 24px;background:#fff;border:1px solid #f1f5f9">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Total Students</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[#0d326b] text-[20px]">group</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none text-[#0d326b] tracking-tight">{{ $totalStudents }}</p>
            <p class="text-[12px] text-[#1a6fd4] font-medium mt-1">across all classes</p>
        </div>

        <div style="border-radius:24px;padding:22px 24px;background:#f0fdf4;border:1px solid #d1fae5">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">On Track</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <span class="material-symbols-outlined text-emerald-700 text-[20px]">check_circle</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none text-emerald-700 tracking-tight">{{ $onTrack }}</p>
            <p class="text-[12px] text-emerald-600 font-medium mt-1">classes avg ≥ 75%</p>
        </div>

        <div class="text-amber-950" style="border-radius:24px;padding:22px 24px;background:linear-gradient(135deg,#f59e0b 0%,#facc15 50%,#fbbf24 100%);border:1px solid rgba(245,158,11,.5);box-shadow:0 4px 16px rgba(245,158,11,.22)">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-950/80">School Avg Score</span>
                <div class="w-10 h-10 rounded-xl bg-white/35 flex items-center justify-center">
                    <span class="material-symbols-outlined text-amber-950 text-[20px]">insights</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none text-amber-950 tracking-tight">{{ $avgSchoolScore }}%</p>
            <p class="text-[12px] text-amber-950/80 font-bold mt-1">quiz average</p>
        </div>

    </div>

    {{-- Teacher table --}}
    <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
            <div>
                <h3 class="text-[15px] font-black text-[#0d326b]">All Classrooms</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Click a teacher to view individual student performance</p>
            </div>
            <span class="text-[12px] font-semibold text-slate-400">{{ $teachers->count() }} {{ Str::plural('class', $teachers->count()) }}</span>
        </div>

        @if($teachers->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Teacher</th>
                        <th style="width:80px;text-align:center">Students</th>
                        <th style="width:120px">Avg Quiz Score</th>
                        <th style="width:140px">Completion Rate</th>
                        <th style="width:100px;text-align:center">Active / Week</th>
                        <th style="width:120px;text-align:center">Status</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($teachers as $row)
                    @php
                        $statusStyle = match($row['status']) {
                            'on_track'        => 'background:#ecfdf5;color:#15803d',
                            'needs_attention' => 'background:#fffbeb;color:#b45309',
                            default           => 'background:#fef2f2;color:#b91c1c',
                        };
                        $statusLabel = match($row['status']) {
                            'on_track'        => 'On Track',
                            'needs_attention' => 'Needs Attention',
                            default           => 'Needs Support',
                        };
                        $scoreColor = $row['avg_score'] >= 75 ? '#16a34a' : ($row['avg_score'] >= 50 ? '#d97706' : '#ef4444');
                    @endphp
                    <tr onclick="window.location='{{ route('teacher-leader.reports') }}?teacher_id={{ $row['teacher']->id }}'">
                        <td>
                            <div class="flex items-center gap-3">
                                <img src="{{ $row['teacher']->user?->avatarUrl() ?? '' }}"
                                     class="w-9 h-9 rounded-full object-cover border border-slate-100 flex-shrink-0 bg-[#0d326b]"
                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($row['teacher']->first_name.' '.$row['teacher']->last_name) }}&background=0d326b&color=fff&size=64&bold=true&rounded=true'">
                                <div>
                                    <p class="font-bold text-slate-800 text-[13px]">{{ $row['teacher']->first_name }} {{ $row['teacher']->last_name }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $row['teacher']->specialization ?? 'Teacher' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="font-bold text-slate-700">{{ $row['total_students'] }}</span>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-20 bg-slate-100 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full" style="width:{{ min(100,$row['avg_score']) }}%;background:{{ $scoreColor }}"></div>
                                </div>
                                <span class="font-black text-[13px]" style="color:{{ $scoreColor }}">{{ $row['avg_score'] }}%</span>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-20 bg-slate-100 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full" style="width:{{ $row['completion_rate'] }}%;background:#0d326b"></div>
                                </div>
                                <span class="font-bold text-slate-600 text-[13px]">{{ $row['completion_rate'] }}%</span>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="font-bold text-[13px] {{ $row['active_students'] > 0 ? 'text-emerald-600' : 'text-slate-400' }}">
                                {{ $row['active_students'] }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full" style="{{ $statusStyle }}">{{ $statusLabel }}</span>
                        </td>
                        <td>
                            <span class="material-symbols-outlined text-slate-300 text-[18px]">chevron_right</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-6 py-16 text-center">
            <span class="material-symbols-outlined text-[56px]" style="color:#e2e8f0">school</span>
            <p class="text-[14px] text-slate-400 font-semibold mt-3">No teachers in this school yet.</p>
        </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         VIEW B: Student drill-down for a specific teacher
         ═══════════════════════════════════════════════════════════════════ --}}
    @else

    {{-- Back link + teacher summary --}}
    @if($selectedTeacher)
    <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 p-5 flex items-center gap-5">
        <img src="{{ $selectedTeacher->user?->avatarUrl() ?? '' }}"
             class="w-14 h-14 rounded-full object-cover border-2 border-slate-100 flex-shrink-0 bg-[#0d326b]"
             onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($selectedTeacher->first_name.' '.$selectedTeacher->last_name) }}&background=0d326b&color=fff&size=128&bold=true&rounded=true'">
        <div class="flex-1 min-w-0">
            <p class="text-[18px] font-black text-[#0d326b]">{{ $selectedTeacher->first_name }} {{ $selectedTeacher->last_name }}</p>
            <p class="text-[12px] text-slate-400 mt-0.5">{{ $selectedTeacher->specialization ?? 'Teacher' }} &bull; {{ $studentReports->count() }} active students &bull; {{ $lessons->count() }} published lessons</p>
        </div>
        @php
            $classAvg    = $studentReports->where('quizzesTaken', '>', 0)->avg('avgScore');
            $classAvg    = round((float) $classAvg, 1);
            $classDone   = $studentReports->sum('completedLessons');
            $classTotal  = $studentReports->sum('totalLessons');
            $classCompPct = $classTotal > 0 ? round($classDone / $classTotal * 100, 1) : 0;
        @endphp
        <div class="hidden sm:flex items-center gap-6 flex-shrink-0">
            <div class="text-center">
                <p class="text-[24px] font-black text-[#0d326b] leading-none">{{ $classAvg }}%</p>
                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Avg Score</p>
            </div>
            <div class="w-px h-10 bg-slate-100"></div>
            <div class="text-center">
                <p class="text-[24px] font-black text-[#0d326b] leading-none">{{ $classCompPct }}%</p>
                <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wider mt-0.5">Completion</p>
            </div>
        </div>
    </div>
    @endif

    {{-- KPI cards for this class --}}
    @php
        $classTotalStudents  = $studentReports->count();
        $classQuizzesTaken   = $studentReports->sum('quizzesTaken');
        $classQuizzesPassed  = $studentReports->sum('quizzesPassed');
        $classPassRate       = $classQuizzesTaken > 0 ? round($classQuizzesPassed / $classQuizzesTaken * 100, 1) : 0;
        $gestureStudents     = $studentReports->filter(fn ($r) => ($r['gestureAttempts'] ?? 0) > 0);
        $avgGestureAccuracy  = $gestureStudents->isNotEmpty() ? round($gestureStudents->avg('gestureAccuracy'), 1) : 0;
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        <div style="border-radius:24px;padding:22px 24px;background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%);border:1px solid #f1f5f9">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-white/70">Students</span>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center"><span class="material-symbols-outlined text-white text-[20px]">group</span></div>
            </div>
            <p class="text-[36px] font-black leading-none text-white tracking-tight">{{ $classTotalStudents }}</p>
            <p class="text-[12px] text-white/70 font-medium mt-1">active in class</p>
        </div>

        <div style="border-radius:24px;padding:22px 24px;background:#fff;border:1px solid #f1f5f9">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Completion Rate</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center"><span class="material-symbols-outlined text-[#0d326b] text-[20px]">task_alt</span></div>
            </div>
            <p class="text-[36px] font-black leading-none text-[#0d326b] tracking-tight">{{ $classCompPct }}%</p>
            <p class="text-[12px] text-[#1a6fd4] font-medium mt-1">{{ $classDone }} / {{ $classTotal }} lessons</p>
        </div>

        <div style="border-radius:24px;padding:22px 24px;background:#f0fdf4;border:1px solid #d1fae5">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Quizzes Passed</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center"><span class="material-symbols-outlined text-emerald-700 text-[20px]">quiz</span></div>
            </div>
            <div class="flex items-baseline gap-1.5 mb-1">
                <p class="text-[36px] font-black leading-none text-emerald-700 tracking-tight">{{ $classQuizzesPassed }}</p>
                <span class="text-[18px] font-bold text-slate-400">/ {{ $classQuizzesTaken }}</span>
            </div>
            <p class="text-[12px] text-emerald-600 font-medium">{{ $classPassRate }}% pass rate</p>
        </div>

        <div class="text-amber-950" style="border-radius:24px;padding:22px 24px;background:linear-gradient(135deg,#f59e0b 0%,#facc15 50%,#fbbf24 100%);border:1px solid rgba(245,158,11,.5);box-shadow:0 4px 16px rgba(245,158,11,.22)">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-950/80">Gesture Accuracy</span>
                <div class="w-10 h-10 rounded-xl bg-white/35 flex items-center justify-center"><span class="material-symbols-outlined text-amber-950 text-[20px]">front_hand</span></div>
            </div>
            <p class="text-[36px] font-black leading-none text-amber-950 tracking-tight">{{ $avgGestureAccuracy }}%</p>
            <p class="text-[12px] text-amber-950/80 font-bold mt-1">{{ $gestureStudents->count() }} students with data</p>
        </div>

    </div>

    {{-- Student progress table --}}
    <div class="bg-white rounded-[24px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
            <div>
                <h3 class="text-[15px] font-black text-[#0d326b]">Student Progress Report</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Click a student for full individual breakdown</p>
            </div>
            <span class="text-[12px] font-semibold text-slate-400">{{ $studentReports->count() }} students</span>
        </div>

        @if($studentReports->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Student</th>
                        <th>Overall Progress</th>
                        <th style="width:100px">Lessons</th>
                        <th style="width:110px">Quizzes</th>
                        <th style="width:95px">Avg Score</th>
                        <th style="width:130px;text-align:center">Gesture Accuracy</th>
                        <th style="width:130px">Last Active</th>
                        <th style="width:40px"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($studentReports as $row)
                    <tr onclick="openStudentModal('{{ $row['student_id'] }}')">
                        <td>
                            <div class="flex items-center gap-3">
                                <img src="{{ $row['avatar_url'] ?? '' }}"
                                     class="w-9 h-9 rounded-full object-cover border border-slate-100 flex-shrink-0 bg-[#0d326b]"
                                     onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($row['initials']) }}&background=0d326b&color=fff&size=128&bold=true&rounded=true&font-size=0.45'">
                                <div>
                                    <p class="font-bold text-[#0d326b] text-[13px]">{{ $row['studentName'] }}</p>
                                    <p class="text-[10px] text-slate-400">{{ $row['gradeLevel'] }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="flex items-center gap-2">
                                <div class="w-28 h-1.5 bg-[#f1f5f9] rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $row['overallPct'] >= 100 ? 'bg-[#0d326b]' : 'bg-[#1a6fd4]' }}"
                                         style="width:{{ min(100, $row['overallPct']) }}%"></div>
                                </div>
                                <span class="text-[11px] font-bold text-slate-500">{{ $row['overallPct'] }}%</span>
                            </div>
                        </td>
                        <td>
                            <span class="font-bold text-slate-800">{{ $row['completedLessons'] }}</span>
                            <span class="text-[12px] text-slate-400">/ {{ $row['totalLessons'] }}</span>
                        </td>
                        <td>
                            <span class="font-bold text-slate-800">{{ $row['quizzesPassed'] }}</span>
                            <span class="text-[12px] text-slate-400">/ {{ $row['quizzesTaken'] }}</span>
                        </td>
                        <td>
                            @if($row['quizzesTaken'] > 0)
                            <span class="font-bold text-[#0d326b]">{{ $row['avgScore'] }}%</span>
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(($row['gestureAttempts'] ?? 0) > 0)
                            <span class="font-black text-emerald-600">{{ number_format($row['gestureAccuracy'], 1) }}%</span>
                            <div class="text-[10px] text-slate-400">{{ $row['gestureSuccess'] }}/{{ $row['gestureAttempts'] }}</div>
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td><span class="text-[12px] text-slate-500">{{ $row['lastAccessed'] }}</span></td>
                        <td><span class="material-symbols-outlined text-slate-300 text-[18px]">chevron_right</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-6 py-16 text-center">
            <span class="material-symbols-outlined text-[56px]" style="color:#e2e8f0">group</span>
            <p class="text-[14px] text-slate-400 font-semibold mt-3">No active students in this class yet.</p>
        </div>
        @endif
    </div>

    @endif {{-- end if/else filterTeacherId --}}

</div>{{-- /skeleton-hide --}}

{{-- ══════════════════════════════════════════════════════════════════════
     STUDENT DRILL-DOWN MODAL (reuses teacher reports modal pattern)
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="tlStudentModalOverlay" onclick="if(event.target===this)closeTlStudentModal()">
    <div id="tlStudentModal">

        {{-- Modal header --}}
        <div class="flex-shrink-0 px-7 py-5 flex items-start gap-5 border-b border-slate-100"
             style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 100%)">
            <div class="relative flex-shrink-0">
                <img id="tlModalAvatar" src="" alt=""
                     class="w-14 h-14 rounded-full object-cover border-2 border-white/20"
                     onerror="this.src='https://ui-avatars.com/api/?name=S&background=1e4b8f&color=fff&size=128&bold=true&rounded=true'">
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-black text-[17px] truncate" id="tlModalName">—</p>
                <p class="text-white/60 text-[12px] mt-0.5" id="tlModalGrade">—</p>
                {{-- KPI mini-strip --}}
                <div class="flex items-center gap-5 mt-3 flex-wrap">
                    <div><p class="text-white/50 text-[9px] font-bold uppercase tracking-wider">Progress</p><p class="text-white font-black text-[15px]" id="tlModalPct">—</p></div>
                    <div class="w-px h-8 bg-white/20"></div>
                    <div><p class="text-white/50 text-[9px] font-bold uppercase tracking-wider">Lessons</p><p class="text-white font-black text-[15px]" id="tlModalLessons">—</p></div>
                    <div class="w-px h-8 bg-white/20"></div>
                    <div><p class="text-white/50 text-[9px] font-bold uppercase tracking-wider">Avg Score</p><p class="text-white font-black text-[15px]" id="tlModalScore">—</p></div>
                    <div class="w-px h-8 bg-white/20"></div>
                    <div><p class="text-white/50 text-[9px] font-bold uppercase tracking-wider">Gestures</p><p class="text-white font-black text-[15px]" id="tlModalGesture">—</p></div>
                </div>
            </div>
            <button onclick="closeTlStudentModal()"
                    class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 mt-1"
                    style="background:rgba(255,255,255,.15)"
                    onmouseover="this.style.background='rgba(255,255,255,.25)'"
                    onmouseout="this.style.background='rgba(255,255,255,.15)'">
                <span class="material-symbols-outlined text-white text-[18px]">close</span>
            </button>
        </div>

        {{-- Tab nav --}}
        <div class="flex-shrink-0 flex items-center gap-0 border-b border-slate-100 px-4 overflow-x-auto">
            <button id="tl-tab-btn-learning-path" onclick="switchTlTab('learning-path')" class="tl-modal-tab-btn active">
                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[15px]">route</span>Learning Path</span>
            </button>
            <button id="tl-tab-btn-gestures" onclick="switchTlTab('gestures')" class="tl-modal-tab-btn">
                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[15px]">front_hand</span>Gestures</span>
            </button>
            <button id="tl-tab-btn-lessons" onclick="switchTlTab('lessons')" class="tl-modal-tab-btn">
                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[15px]">menu_book</span>Lessons</span>
            </button>
            <button id="tl-tab-btn-achievements" onclick="switchTlTab('achievements')" class="tl-modal-tab-btn">
                <span class="flex items-center gap-1.5"><span class="material-symbols-outlined text-[15px]">workspace_premium</span>Achievements</span>
            </button>
        </div>

        {{-- Tab panels --}}
        <div class="flex-1 overflow-y-auto p-6" id="tlModalBody">

            {{-- Learning Path --}}
            <div id="tl-tab-learning-path" class="tl-modal-tab-panel">
                <div id="tl-lp-loading" class="hidden flex flex-col gap-3">
                    <div class="tl-sk h-40 rounded-[22px] w-full"></div>
                    <div class="grid grid-cols-2 gap-3"><div class="tl-sk h-32 rounded-[20px]"></div><div class="tl-sk h-32 rounded-[20px]"></div></div>
                    <div class="tl-sk h-28 rounded-[20px] w-full"></div>
                </div>
                <div id="tl-lp-content" class="hidden space-y-4"></div>
                <div id="tl-lp-empty" class="hidden py-14 text-center">
                    <span class="material-symbols-outlined text-[48px]" style="color:#e2e8f0">route</span>
                    <p class="text-[13px] font-bold text-slate-400 mt-2">No Learning Path set</p>
                </div>
            </div>

            {{-- Gestures --}}
            <div id="tl-tab-gestures" class="tl-modal-tab-panel hidden">
                <div class="flex items-center justify-between mb-3 flex-wrap gap-2">
                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-[.1em]">Gesture Performance</h4>
                    <div class="flex items-center gap-2">
                        <input type="text" id="tlGestureSearch" oninput="filterTlGestures()" placeholder="Search sign…"
                               class="text-[11px] bg-slate-100 px-3 py-1 rounded-full outline-none w-36 focus:bg-white focus:ring-1 focus:ring-[#0d326b]">
                        <select id="tlGestureMastery" onchange="filterTlGestures()"
                                class="text-[11px] bg-slate-100 px-2.5 py-1.5 rounded-full outline-none">
                            <option value="all">All Levels</option>
                            <option value="mastered">Mastered</option>
                            <option value="proficient">Proficient</option>
                            <option value="developing">Developing</option>
                            <option value="needs_practice">Needs Practice</option>
                        </select>
                    </div>
                </div>
                <div class="max-h-[320px] overflow-y-auto rounded-xl border border-slate-100 bg-slate-50/40 p-2.5">
                    <div id="tlGestureList" class="space-y-2"></div>
                </div>
            </div>

            {{-- Lessons --}}
            <div id="tl-tab-lessons" class="tl-modal-tab-panel hidden">
                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-[.1em] mb-3">Lesson Breakdown</h4>
                <div id="tlLessonList" class="space-y-2"></div>
            </div>

            {{-- Achievements --}}
            <div id="tl-tab-achievements" class="tl-modal-tab-panel hidden">
                <div class="flex items-center justify-between mb-4">
                    <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-[.1em]">Achievements</h4>
                    <span id="tl-ach-count" class="text-[11px] font-semibold text-slate-400"></span>
                </div>
                <div id="tl-ach-loading" class="grid grid-cols-4 gap-3 hidden">
                    @for($i=0;$i<8;$i++)<div class="flex flex-col items-center gap-2 bg-white border border-slate-100 rounded-2xl p-3"><div class="tl-sk w-14 h-14 rounded-[14px]"></div><div class="tl-sk h-3 rounded w-20"></div><div class="tl-sk h-2 rounded w-16"></div></div>@endfor
                </div>
                <div id="tl-ach-list" class="grid grid-cols-4 gap-3 max-h-[400px] overflow-y-auto pr-1"></div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
/* ── Embed student data ─────────────────────────────────────────── */
const TL_STUDENT_DATA = @json($studentReports->values());
let _tlCurrentStudentId = null;
let _tlGestureData      = [];
let _tlLpLoaded         = {};
let _tlAchLoaded        = {};

/* ── Open / close ─────────────────────────────────────────────────── */
function openStudentModal(studentId) {
    const data = TL_STUDENT_DATA.find(s => String(s.student_id) === String(studentId));
    if (!data) return;

    _tlCurrentStudentId = data.student_id;

    // Populate header
    const av = document.getElementById('tlModalAvatar');
    av.src = data.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(data.initials||'S')}&background=0d326b&color=fff&size=128&bold=true&rounded=true&font-size=0.45`;
    document.getElementById('tlModalName').textContent   = data.studentName;
    document.getElementById('tlModalGrade').textContent  = 'Grade ' + data.gradeLevel;
    document.getElementById('tlModalPct').textContent    = data.overallPct + '%';
    document.getElementById('tlModalLessons').textContent = data.completedLessons + '/' + data.totalLessons;
    document.getElementById('tlModalScore').textContent  = data.quizzesTaken > 0 ? data.avgScore + '%' : '—';
    document.getElementById('tlModalGesture').textContent = (data.gestureAttempts > 0) ? data.gestureAccuracy + '%' : '—';

    // Gesture data
    _tlGestureData = data.gestureBreakdown || [];
    document.getElementById('tlGestureSearch').value = '';
    document.getElementById('tlGestureMastery').value = 'all';
    filterTlGestures();

    // Lessons
    renderTlLessons(data.lessons || []);

    // Reset lazy tabs
    _tlLpLoaded  = {};
    _tlAchLoaded = {};
    document.getElementById('tl-lp-loading').classList.add('hidden');
    document.getElementById('tl-lp-content').classList.add('hidden');
    document.getElementById('tl-lp-content').innerHTML = '';
    document.getElementById('tl-lp-empty').classList.add('hidden');
    document.getElementById('tl-ach-list').innerHTML = '';
    document.getElementById('tl-ach-count').textContent = '';

    // Reset & show modal
    switchTlTab('learning-path');
    const overlay = document.getElementById('tlStudentModalOverlay');
    overlay.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

function closeTlStudentModal() {
    document.getElementById('tlStudentModalOverlay').style.display = 'none';
    document.body.style.overflow = '';
    _tlCurrentStudentId = null;
}

document.addEventListener('keydown', e => { if (e.key === 'Escape') closeTlStudentModal(); });

/* ── Tab switching ─────────────────────────────────────────────────── */
function switchTlTab(tab) {
    ['learning-path','gestures','lessons','achievements'].forEach(t => {
        document.getElementById('tl-tab-' + t).classList.toggle('hidden', t !== tab);
        const btn = document.getElementById('tl-tab-btn-' + t);
        btn.classList.toggle('active', t === tab);
    });

    if (tab === 'learning-path' && _tlCurrentStudentId && !_tlLpLoaded[_tlCurrentStudentId]) {
        loadTlLearningPath(_tlCurrentStudentId);
    }
    if (tab === 'achievements' && _tlCurrentStudentId && !_tlAchLoaded[_tlCurrentStudentId]) {
        loadTlAchievements(_tlCurrentStudentId);
    }
}

/* ── Gestures ─────────────────────────────────────────────────────── */
function filterTlGestures() {
    const q       = (document.getElementById('tlGestureSearch').value || '').toLowerCase();
    const mastery = document.getElementById('tlGestureMastery').value;
    const el      = document.getElementById('tlGestureList');
    el.innerHTML  = '';

    const filtered = _tlGestureData.filter(g => {
        const matchQ = !q || g.gestureName.toLowerCase().includes(q);
        const matchM = mastery === 'all' || (g.masteryLevel || '').toLowerCase() === mastery;
        return matchQ && matchM;
    });

    if (!filtered.length) {
        el.innerHTML = '<p class="text-center text-[12px] text-slate-400 py-4">No gesture data.</p>';
        return;
    }

    const badgeStyles = {
        mastered:      'background:#d1fae5;color:#065f46;border:1px solid #a7f3d0',
        proficient:    'background:#dbeafe;color:#1e40af;border:1px solid #bfdbfe',
        developing:    'background:#fef3c7;color:#92400e;border:1px solid #fde68a',
        needs_practice:'background:#ffe4e6;color:#9f1239;border:1px solid #fecdd3',
    };

    filtered.forEach(g => {
        const ml    = (g.masteryLevel || 'needs_practice').toLowerCase();
        const bs    = badgeStyles[ml] || badgeStyles.needs_practice;
        const label = ml.replace('_', ' ').toUpperCase();
        const row   = document.createElement('div');
        row.className = 'bg-white rounded-xl p-3 border border-slate-100 flex items-center justify-between gap-3';
        row.innerHTML = `
            <div style="flex:1;min-width:0">
                <div style="display:flex;align-items:center;gap:8px;margin-bottom:2px">
                    <span style="font-size:13px;font-weight:700;color:#0d326b">${g.gestureName}</span>
                    <span style="display:inline-block;padding:2px 8px;border-radius:9999px;font-size:9px;font-weight:800;${bs}">${label}</span>
                </div>
                <div style="font-size:11px;color:#64748b">Attempts: <strong>${g.attempts}</strong> &nbsp; Correct: <strong style="color:#059669">${g.successfulAttempts}</strong> &nbsp; Wrong: <strong style="color:#e11d48">${g.wrongAttempts}</strong> &nbsp; <span style="color:#94a3b8">${g.lastAttemptAt}</span></div>
            </div>
            <span style="font-size:15px;font-weight:900;color:#0d326b;flex-shrink:0">${Number(g.accuracy).toFixed(1)}%</span>`;
        el.appendChild(row);
    });
}

/* ── Lessons ─────────────────────────────────────────────────────── */
function renderTlLessons(lessons) {
    const el = document.getElementById('tlLessonList');
    el.innerHTML = '';
    if (!lessons.length) { el.innerHTML = '<p class="text-center text-[12px] text-slate-400 py-4">No lessons assigned.</p>'; return; }

    const grouped = {};
    lessons.forEach(l => { const k = l.moduleTitle || 'Unassigned'; if (!grouped[k]) grouped[k] = []; grouped[k].push(l); });

    Object.keys(grouped).forEach(mod => {
        const hdr = document.createElement('div');
        hdr.style.cssText = 'display:flex;align-items:center;gap:8px;padding:10px 0 6px;border-bottom:1px solid #e2e8f0;margin-top:12px;margin-bottom:8px';
        hdr.innerHTML = `<span class="material-symbols-outlined" style="font-size:16px;color:#0d326b">folder</span><h5 style="font-size:11px;font-weight:800;color:#0d326b;text-transform:uppercase;letter-spacing:.06em">${mod}</h5>`;
        el.appendChild(hdr);

        grouped[mod].forEach(lesson => {
            let statusLabel, statusStyle, barColor;
            if (!lesson.started) {
                statusLabel='NOT STARTED'; statusStyle='background:#f1f5f9;color:#64748b'; barColor='#e2e8f0';
            } else if (lesson.is_exam && lesson.completed) {
                statusLabel='PASSED'; statusStyle='background:#d1fae5;color:#065f46'; barColor='#059669';
            } else if (lesson.is_exam && lesson.failed) {
                statusLabel='FAILED'; statusStyle='background:#fee2e2;color:#991b1b'; barColor='#ef4444';
            } else if (lesson.completed) {
                statusLabel='COMPLETED'; statusStyle='background:#dbeafe;color:#0d326b'; barColor='#0d326b';
            } else {
                statusLabel='IN PROGRESS'; statusStyle='background:#eff6ff;color:#1a6fd4'; barColor='#1a6fd4';
            }

            const scoreText = lesson.quizCompleted && lesson.quizScore != null ? `${Number(lesson.quizScore).toFixed(1)}%` : (lesson.started ? 'Quiz pending' : '—');
            const row = document.createElement('div');
            row.style.cssText = 'background:#f8fafc;border-radius:14px;padding:11px 16px;display:flex;align-items:center;justify-content:space-between;margin-bottom:6px';
            row.innerHTML = `
                <div style="flex:1;min-width:0;padding-right:12px">
                    <p style="font-size:13px;font-weight:700;color:#1e293b;margin:0 0 4px">${lesson.lessonTitle}${lesson.is_exam?'<span style="font-size:9px;font-weight:700;color:#0369a1;background:#e0f2fe;border-radius:9999px;padding:2px 7px;margin-left:6px">Exam</span>':''}</p>
                    <div style="display:flex;align-items:center;gap:8px">
                        <div style="width:70px;height:5px;background:#e2e8f0;border-radius:9999px;overflow:hidden"><div style="height:100%;border-radius:9999px;background:${barColor};width:${lesson.stepPct}%"></div></div>
                        <span style="font-size:10px;font-weight:700;color:#94a3b8">${lesson.stepPct}% &bull; ${lesson.lastAccessed}</span>
                    </div>
                </div>
                <div style="display:flex;flex-direction:column;align-items:flex-end;gap:4px;flex-shrink:0">
                    <span style="display:inline-block;padding:3px 10px;border-radius:9999px;font-size:10px;font-weight:700;${statusStyle}">${statusLabel}</span>
                    <span style="font-size:11px;color:#64748b;font-weight:600">${scoreText}</span>
                </div>`;
            el.appendChild(row);
        });
    });
}

/* ── Learning Path (AJAX — reuses teacher AJAX endpoint) ──────────── */
async function loadTlLearningPath(studentId) {
    if (_tlLpLoaded[studentId]) return;
    document.getElementById('tl-lp-loading').classList.remove('hidden');
    try {
        const res  = await fetch(`/reports/student/${studentId}/learning-path`, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        document.getElementById('tl-lp-loading').classList.add('hidden');
        _tlLpLoaded[studentId] = true;

        if (!data.learning_path && (!data.charts || !data.charts.xp_daily)) {
            document.getElementById('tl-lp-empty').classList.remove('hidden');
            return;
        }

        const contentEl = document.getElementById('tl-lp-content');
        const charts    = data.charts || {};
        const stats     = data.stats  || {};
        const lp        = data.learning_path;

        const xpArr   = charts.xp_daily     || [];
        const quizArr = (charts.quiz_history || []).map(q => q.score);

        contentEl.innerHTML = `
        <div class="grid grid-cols-3 gap-4">
            <div class="col-span-1 rounded-[22px] p-5 flex flex-col gap-3 text-white" style="background:linear-gradient(135deg,#0d326b,#1a6fd4)">
                <p style="font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.12em;color:rgba(255,255,255,.6)">Learning Profile</p>
                <div><p style="font-size:10px;color:rgba(255,255,255,.5);font-weight:600">FSL Level</p><p style="font-size:16px;font-weight:900">${lp?.fsl_level || '—'}</p></div>
                <div><p style="font-size:10px;color:rgba(255,255,255,.5);font-weight:600">Goal</p><p style="font-size:14px;font-weight:700">${(lp?.learning_goal||'').replace(/_/g,' ') || '—'}</p></div>
                <div><p style="font-size:10px;color:rgba(255,255,255,.5);font-weight:600">Daily Practice</p><p style="font-size:14px;font-weight:700">${(lp?.practice_time||'').replace(/_/g,' ') || '—'}</p></div>
                <div class="border-t border-white/15 pt-3 grid grid-cols-2 gap-2">
                    <div><p style="font-size:9px;color:rgba(255,255,255,.4);font-weight:700;text-transform:uppercase">Total XP</p><p style="font-size:16px;font-weight:900">${(stats.total_xp||0).toLocaleString()}</p></div>
                    <div><p style="font-size:9px;color:rgba(255,255,255,.4);font-weight:700;text-transform:uppercase">Streak</p><p style="font-size:16px;font-weight:900">${stats.streak_days||0} 🔥</p></div>
                    <div><p style="font-size:9px;color:rgba(255,255,255,.4);font-weight:700;text-transform:uppercase">Lessons</p><p style="font-size:16px;font-weight:900">${stats.completed_lessons||0}</p></div>
                    <div><p style="font-size:9px;color:rgba(255,255,255,.4);font-weight:700;text-transform:uppercase">Level</p><p style="font-size:14px;font-weight:900">${stats.current_level||'—'}</p></div>
                </div>
            </div>
            <div class="col-span-2 bg-white border border-slate-100 rounded-[22px] p-5" style="box-shadow:0 2px 12px rgba(13,50,107,.04)">
                <p style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:4px">XP Activity</p>
                <h3 style="font-size:15px;font-weight:900;color:#0d326b;margin-bottom:16px">XP Earned — Last 14 Days</h3>
                <div id="tl-lp-xp-chart-${studentId}"></div>
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white border border-slate-100 rounded-[22px] p-5">
                <p style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:4px">Quiz Scores</p>
                <h3 style="font-size:15px;font-weight:900;color:#0d326b;margin-bottom:16px">Score Trend</h3>
                ${quizArr.length === 0 ? '<p style="text-align:center;color:#cbd5e1;font-size:12px;padding:24px 0">No quiz data yet.</p>' : `<div id="tl-lp-quiz-chart-${studentId}"></div>`}
            </div>
            <div class="bg-white border border-slate-100 rounded-[22px] p-5">
                <p style="font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:#94a3b8;margin-bottom:4px">Lesson Activity</p>
                <h3 style="font-size:15px;font-weight:900;color:#0d326b;margin-bottom:16px">Completions / Day</h3>
                <div id="tl-lp-lesson-chart-${studentId}"></div>
            </div>
        </div>`;

        contentEl.classList.remove('hidden');

        // Render SVG charts
        tlBuildLineChart(`tl-lp-xp-chart-${studentId}`, { labels: charts.labels||[], values: xpArr, yMin:0, yMax: Math.max(10,...xpArr), gradId:`tlXp${studentId}` });
        if (quizArr.length > 0) tlBuildLineChart(`tl-lp-quiz-chart-${studentId}`, { labels: (charts.quiz_history||[]).map(q=>q.label), values: quizArr, yMin:0, yMax:100, gradId:`tlQuiz${studentId}`, yFormat: v=>v+'%' });
        tlBuildLineChart(`tl-lp-lesson-chart-${studentId}`, { labels: charts.labels||[], values: charts.lessons_daily||[], yMin:0, yMax: Math.max(1,...(charts.lessons_daily||[])), gradId:`tlLesson${studentId}` });

    } catch (e) {
        document.getElementById('tl-lp-loading').classList.add('hidden');
        document.getElementById('tl-lp-empty').classList.remove('hidden');
    }
}

/* ── Achievements (AJAX — reuses teacher endpoint) ─────────────────── */
async function loadTlAchievements(studentId) {
    if (_tlAchLoaded[studentId]) return;
    const listEl    = document.getElementById('tl-ach-list');
    const loadingEl = document.getElementById('tl-ach-loading');
    loadingEl.classList.remove('hidden');
    try {
        const res  = await fetch(`/reports/student/${studentId}/achievements`, { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        loadingEl.classList.add('hidden');
        _tlAchLoaded[studentId] = true;
        const achs = data.achievements || [];
        const unlocked = achs.filter(a => a.is_unlocked).length;
        document.getElementById('tl-ach-count').textContent = unlocked + ' / ' + achs.length + ' unlocked';

        if (!achs.length) { listEl.innerHTML = '<div class="col-span-4 text-center py-8 text-slate-300"><p class="text-[12px] font-semibold">No achievements yet.</p></div>'; return; }

        achs.forEach(a => {
            const pct    = a.progress_target > 0 ? Math.min(100, Math.round(a.progress_current / a.progress_target * 100)) : (a.is_unlocked ? 100 : 0);
            const barClr = a.is_unlocked ? '#10b981' : (a.progress_current > 0 ? '#1a6fd4' : '#cbd5e1');
            const card   = document.createElement('div');
            card.className = `flex flex-col items-center text-center bg-white border border-slate-200 rounded-2xl p-3 shadow-sm transition-all ${a.is_unlocked ? '' : 'grayscale opacity-60'}`;
            card.innerHTML = `
                <div style="width:56px;height:56px;display:flex;align-items:center;justify-content:center;margin-bottom:8px">
                    <span class="material-symbols-outlined" style="font-size:36px;color:${a.color||'#0d326b'}">${a.icon||'workspace_premium'}</span>
                </div>
                <p style="font-size:11.5px;font-weight:700;color:#374151;line-height:1.3;margin-bottom:2px">${a.name}</p>
                <p style="font-size:10px;color:#94a3b8;margin-bottom:8px;line-height:1.4">${a.description||''}</p>
                <div style="width:100%;margin-top:auto">
                    <div style="height:5px;background:#f1f5f9;border-radius:9999px;overflow:hidden;margin-bottom:3px"><div style="height:100%;background:${barClr};border-radius:9999px;width:${pct}%"></div></div>
                    <span style="font-size:9.5px;font-weight:600;color:${a.is_unlocked?'#10b981':'#94a3b8'}">${a.is_unlocked ? '✓ Unlocked' : (a.progress_target>0?`${a.progress_current}/${a.progress_target}`:'0/1')}</span>
                </div>`;
            listEl.appendChild(card);
        });
    } catch {
        loadingEl.classList.add('hidden');
        listEl.innerHTML = '<div class="col-span-4 text-center py-4"><p class="text-[12px] text-red-400">Failed to load.</p></div>';
    }
}

/* ── Mini SVG line chart builder ──────────────────────────────────── */
function tlBuildLineChart(containerId, opts) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const W=560, H=140, pL=32, pR=12, pT=12, pB=24;
    const plotW=W-pL-pR, plotH=H-pT-pB;
    const vals   = opts.values || [];
    const lbls   = opts.labels || [];
    const yMin   = opts.yMin  ?? 0;
    const yMax   = opts.yMax  ?? Math.max(10,...vals);
    const yRange = yMax - yMin || 1;
    const count  = vals.length;
    const gId    = opts.gradId || 'tlChartFill';

    const pts = vals.map((v,i) => {
        const x = count>1 ? pL+(i/(count-1))*plotW : pL+plotW/2;
        const y = pT + plotH - ((v - yMin) / yRange) * plotH;
        return { x: +x.toFixed(1), y: +y.toFixed(1), v, label: lbls[i]||'' };
    });

    let line = pts.length ? `M ${pts[0].x},${pts[0].y}` : '';
    for (let i=0;i<pts.length-1;i++) {
        const dx = (pts[i+1].x - pts[i].x)/2;
        line += ` C ${pts[i].x+dx},${pts[i].y} ${pts[i+1].x-dx},${pts[i+1].y} ${pts[i+1].x},${pts[i+1].y}`;
    }
    const last  = pts[pts.length-1] || {x:pL,y:pT+plotH};
    const first = pts[0]            || {x:pL,y:pT+plotH};
    const area  = line + ` L ${last.x},${pT+plotH} L ${first.x},${pT+plotH} Z`;

    let gridSvg = '', dotSvg = '';
    [0,25,50,75,100].forEach(gv => {
        const gy = +(pT + plotH - ((gv-yMin)/yRange)*plotH).toFixed(1);
        if (gy < pT || gy > pT+plotH+1) return;
        gridSvg += `<line x1="${pL}" y1="${gy}" x2="${pL+plotW}" y2="${gy}" stroke="#f1f5f9" stroke-width="0.8" stroke-dasharray="3,3"/>`;
        gridSvg += `<text x="2" y="${gy+3.5}" font-size="8" fill="#94a3b8" font-weight="600">${opts.yFormat ? opts.yFormat(gv) : gv}</text>`;
    });
    pts.forEach((p,i) => {
        dotSvg += `<circle cx="${p.x}" cy="${p.y}" r="${i===count-1?4:3}" fill="${i===count-1?'#0d326b':'#1e4b8f'}" stroke="#fff" stroke-width="2"/>`;
        if (i%(Math.max(1,Math.floor(count/5)))===0 || i===count-1)
            dotSvg += `<text x="${p.x}" y="${H-6}" font-size="8" fill="#94a3b8" font-weight="500" text-anchor="middle">${p.label}</text>`;
    });

    container.innerHTML = `<svg viewBox="0 0 ${W} ${H}" class="w-full h-auto" overflow="visible">
        <defs><linearGradient id="${gId}" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#1a6fd4" stop-opacity=".18"/><stop offset="100%" stop-color="#1a6fd4" stop-opacity="0"/></linearGradient></defs>
        ${gridSvg}
        <path d="${area}" fill="url(#${gId})"/>
        <path d="${line}" fill="none" stroke="#0d326b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
        ${dotSvg}
    </svg>`;
}
</script>
@endpush
