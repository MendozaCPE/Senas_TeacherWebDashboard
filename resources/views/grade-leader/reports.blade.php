@extends('layouts.grade-leader')
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
.filter-container { background:#fff; border:1px solid #e8eef6; border-radius:20px; padding:14px 18px; display:flex; align-items:center; justify-content:space-between; gap:12px; box-shadow:0 2px 10px rgba(13,50,107,.04); }
.filter-group { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.filter-wrap { position:relative; display:inline-flex; align-items:center; }
.filter-select { appearance:none; background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:10px 36px 10px 14px; font-size:12px; font-weight:700; color:#0d326b; cursor:pointer; min-width:170px; }
.filter-select:hover { background:#f1f5f9; border-color:#cbd5e1; }
.filter-wrap .material-symbols-outlined { position:absolute; right:10px; pointer-events:none; font-size:18px; color:#0d326b; }
.filter-btn { display:inline-flex; align-items:center; gap:6px; padding:10px 18px; border-radius:14px; background:#0d326b; color:#fff; font-size:12px; font-weight:800; transition:background .15s; }
.filter-btn:hover { background:#1a6fd4; }
.filter-reset { display:inline-flex; align-items:center; padding:10px 14px; border-radius:14px; border:1px solid #e2e8f0; color:#64748b; font-size:12px; font-weight:700; transition:all .15s; }
.filter-reset:hover { background:#f1f5f9; color:#0d326b; }
@media(max-width:720px){.filter-container{align-items:flex-start;flex-direction:column}.filter-group{width:100%}.filter-wrap,.filter-select{width:100%}}

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
                Classroom performance overview for <span class="text-white font-bold">{{ $school->name ?? 'your school' }}</span>
                <span class="text-white/80">· S.Y. {{ $schoolYearName ?? 'Current school year' }}{{ ($selectedMonth ?? 'all') !== 'all' ? ' · ' . (collect($monthOptions)->firstWhere('value', $selectedMonth)['label'] ?? '') : '' }}</span>
                @if($selectedTeacher) — viewing <span class="text-[#facc15] font-bold">{{ $selectedTeacher->first_name }} {{ $selectedTeacher->last_name }}'s class</span>@endif
            </p>
        </div>
        @if($filterTeacherId && $selectedTeacher)
        <div class="relative z-10 pr-10 flex-shrink-0">
            <a href="{{ route('grade-leader.reports') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/15 hover:bg-white/25 text-white text-[12px] font-bold transition-colors border border-white/20">
                <span class="material-symbols-outlined text-[16px]">arrow_back</span> All Teachers
            </a>
        </div>
        @endif
    </div>

    {{-- ═══════════════════════════════════════════════════════════════════
         VIEW A: Teacher List (no teacher selected)
         ═══════════════════════════════════════════════════════════════════ --}}
    @php $selectedMonthLabel = collect($monthOptions)->firstWhere('value', $selectedMonth)['label'] ?? 'All Months'; @endphp
    <form method="GET" action="{{ route('grade-leader.reports') }}">
        <div class="filter-container">
            <div class="filter-group">
                <div class="flex items-center gap-2 mr-2">
                    <span class="material-symbols-outlined text-[#0d326b] text-[22px]">tune</span>
                    <span class="text-[13px] font-bold text-[#0d326b] uppercase tracking-wider">Filter Reports</span>
                </div>
                <div class="filter-wrap">
                    <select name="school_year" class="filter-select" aria-label="School year">
                        @foreach($availableSchoolYears as $schoolYear)
                        <option value="{{ $schoolYear->name }}" {{ ($selectedSchoolYear?->name ?? '') === $schoolYear->name ? 'selected' : '' }}>
                            S.Y. {{ $schoolYear->name }} {{ $schoolYear->status === 'active' ? '(Current)' : '(Archived)' }}
                        </option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined">expand_more</span>
                </div>
                <div class="filter-wrap">
                    <select name="month" class="filter-select" aria-label="Month">
                        @foreach($monthOptions as $monthOption)
                        <option value="{{ $monthOption['value'] }}" {{ $selectedMonth === (string) $monthOption['value'] ? 'selected' : '' }}>{{ $monthOption['label'] }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined">expand_more</span>
                </div>
                <a href="{{ route('grade-leader.reports') }}" class="filter-reset">Reset</a>
                <button type="submit" class="filter-btn"><span class="material-symbols-outlined text-[16px]">refresh</span>Apply</button>
            </div>
            <p class="text-[12px] text-slate-400 font-medium"><span class="font-bold text-[#0d326b]">S.Y. {{ $selectedSchoolYear?->name ?? 'Current' }}</span> &nbsp;·&nbsp; {{ $selectedMonthLabel }}</p>
        </div>
    </form>

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
                <p class="text-[11px] text-slate-400 mt-0.5">Select a teacher to review classroom performance and students who may need support</p>
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
                            'no_data'         => 'background:#f1f5f9;color:#64748b',
                            default           => 'background:#fef2f2;color:#b91c1c',
                        };
                        $statusLabel = match($row['status']) {
                            'on_track'        => 'On Track',
                            'needs_attention' => 'Needs Attention',
                            'no_data'         => 'No Data',
                            default           => 'Needs Support',
                        };
                        $scoreColor = $row['avg_score'] === null ? '#94a3b8' : ($row['avg_score'] >= 75 ? '#16a34a' : ($row['avg_score'] >= 50 ? '#d97706' : '#ef4444'));
                    @endphp
                    <tr data-teacher-id="{{ $row['teacher']->id }}"
                        data-report-url="{{ route('grade-leader.reports.class-report', ['teacher' => $row['teacher']->id, 'school_year' => $selectedSchoolYear?->name, 'month' => $selectedMonth]) }}"
                        data-teacher-name="{{ $row['teacher']->first_name }} {{ $row['teacher']->last_name }}"
                        data-teacher-avatar="{{ $row['teacher']->user?->avatarUrl() ?? '' }}"
                        onclick="openClassReport(this)">
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
                                    <div class="h-1.5 rounded-full" style="width:{{ $row['avg_score'] === null ? 0 : min(100,$row['avg_score']) }}%;background:{{ $scoreColor }}"></div>
                                </div>
                                <span class="font-black text-[13px]" style="color:{{ $scoreColor }}">{{ $row['avg_score'] === null ? '—' : $row['avg_score'].'%' }}</span>
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
        @endif {{-- teacher list --}}
    @endif {{-- teacher-list view --}}

</div>{{-- /skeleton-hide --}}

{{-- Classroom performance report modal --}}
<div id="classReportModalOverlay" class="cr-modal-overlay hidden" onclick="if(event.target===this)closeClassReport()" aria-hidden="true">
    <div id="classReportModal" class="cr-modal" role="dialog" aria-modal="true" aria-labelledby="classReportModalName">
        <div class="cr-modal-header">
            <div class="flex items-center gap-4 min-w-0">
                <img id="classReportModalAvatar" src="" alt="" class="w-12 h-12 rounded-full object-cover border-2 border-white/25 bg-white/10 flex-shrink-0">
                <div class="min-w-0">
                    <p class="text-[9px] font-bold uppercase tracking-[.14em] text-white/65">Classroom Performance Report</p>
                    <h2 id="classReportModalName" class="text-[18px] sm:text-[20px] font-black truncate">Loading report...</h2>
                    <p id="classReportModalSchoolYear" class="text-[11px] text-white/70">Current school-year classroom overview</p>
                </div>
            </div>
            <button type="button" onclick="closeClassReport()" class="cr-modal-close" aria-label="Close classroom report">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <div id="classReportModalContent" class="cr-modal-body">
            <div class="py-16 text-center text-slate-400 text-[13px] font-semibold">Loading classroom report...</div>
        </div>
    </div>
</div>

<style>
.cr-modal-overlay{position:fixed;inset:0;z-index:9999;background:rgba(15,23,42,.58);backdrop-filter:blur(4px);align-items:center;justify-content:center;padding:16px}
.cr-modal{background:#f8fafc;border-radius:26px;width:100%;max-width:1120px;max-height:92vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 25px 70px rgba(0,0,0,.24)}
.cr-modal-header{flex-shrink:0;padding:18px 24px;display:flex;align-items:center;justify-content:space-between;gap:16px;color:#fff;background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%)}
.cr-modal-close{width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;background:rgba(255,255,255,.14);transition:background .15s}
.cr-modal-close:hover{background:rgba(255,255,255,.26)}
.cr-modal-body{overflow-y:auto;padding:20px}
@media(max-width:640px){.cr-modal-overlay{padding:7px}.cr-modal{max-height:96vh;border-radius:20px}.cr-modal-header{padding:14px 16px}.cr-modal-body{padding:12px}}
</style>

@endsection

@push('scripts')
<script>
async function openClassReport(row, studentId = null) {
    const overlay = document.getElementById('classReportModalOverlay');
    const content = document.getElementById('classReportModalContent');
    const name = row.dataset.teacherName || 'Teacher';
    document.getElementById('classReportModalName').textContent = name;
    document.getElementById('classReportModalAvatar').src = row.dataset.teacherAvatar || `https://ui-avatars.com/api/?name=${encodeURIComponent(name)}&background=1e4b8f&color=fff&size=96&bold=true&rounded=true`;
    content.innerHTML = '<div class="py-16 text-center text-slate-400 text-[13px] font-semibold">Loading classroom report...</div>';
    overlay.classList.remove('hidden');
    overlay.classList.add('flex');
    overlay.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';

    try {
        const response = await fetch(row.dataset.reportUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } });
        if (!response.ok) throw new Error('Could not load this classroom report.');
        content.innerHTML = await response.text();
        document.getElementById('classReportModalSchoolYear').textContent = content.querySelector('[data-school-year]')?.dataset.schoolYear || 'Current school-year classroom overview';
        if (studentId) {
            const studentRow = content.querySelector('[data-student-id="' + CSS.escape(String(studentId)) + '"]');
            if (studentRow) {
                studentRow.classList.add('bg-amber-50', 'ring-2', 'ring-amber-300');
                studentRow.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        }
    } catch (error) {
        content.innerHTML = `<div class="py-16 text-center text-rose-600 text-[13px] font-semibold">${error.message || 'Could not load this classroom report.'}</div>`;
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    const teacherId = params.get('open_teacher');
    if (!teacherId) return;
    const row = document.querySelector('tr[data-teacher-id="' + CSS.escape(teacherId) + '"]');
    if (row) openClassReport(row, params.get('open_student'));
});

function closeClassReport() {
    const overlay = document.getElementById('classReportModalOverlay');
    if (!overlay) return;
    overlay.classList.add('hidden');
    overlay.classList.remove('flex');
    overlay.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
}

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') closeClassReport();
});
</script>
@endpush
