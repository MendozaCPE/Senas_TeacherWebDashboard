@extends('layouts.admin')
@section('title', 'Analytics')
@section('content')

<style>
/* ── Base Surfaces ── */
.a-card  { background:#fff; border-radius:20px; padding:20px; border:1px solid #f1f5f9; box-shadow:0 1px 3px rgba(13,50,107,.05); transition:transform .2s,box-shadow .2s; }
.a-card:hover  { transform:translateY(-2px); box-shadow:0 10px 28px rgba(13,50,107,.09); }
.a-panel { background:#fff; border-radius:20px; border:1px solid #f1f5f9; box-shadow:0 1px 3px rgba(13,50,107,.05); padding:24px; }
.a-panel:hover { box-shadow:0 6px 24px rgba(13,50,107,.07); }

/* ── Filter container (matches teacher analytics) ── */
.filter-container {
    background: #ffffff;
    border-radius: 22px;
    border: 1px solid #edf2f7;
    box-shadow: 0 2px 10px rgba(13,50,107,.03);
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    flex-wrap: wrap;
}
.filter-group { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.filter-wrap  { position:relative; display:inline-flex; align-items:center; }
.filter-select {
    appearance:none; background:#f8fafc; border:1px solid #e2e8f0;
    border-radius:14px; padding:8px 34px 8px 14px; font-size:13px;
    font-weight:600; color:#0d326b; cursor:pointer; outline:none;
    transition:border-color .15s, background-color .15s;
}
.filter-select:hover { background:#f1f5f9; border-color:#cbd5e1; }
.filter-wrap .material-symbols-outlined { position:absolute; right:10px; pointer-events:none; font-size:18px; color:#0d326b; }
.filter-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:9px 18px; border-radius:14px; font-size:13px; font-weight:700;
    color:#fff; border:none; cursor:pointer; transition:opacity .15s;
    background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%);
}
.filter-btn:hover { opacity:.9; }
.filter-reset {
    display:inline-flex; align-items:center; gap:5px;
    padding:9px 16px; border-radius:14px; font-size:13px; font-weight:600;
    color:#64748b; border:1.5px solid #e2e8f0; background:#fff;
    text-decoration:none; transition:all .15s;
}
.filter-reset:hover { background:#f8fafc; border-color:#cbd5e1; color:#0d326b; }

/* ── KPI stat cards (matches teacher analytics) ── */
.stat-kpi-card {
    border-radius: 24px;
    padding: 22px 24px;
    position: relative;
    overflow: hidden;
    transition: transform .2s ease, box-shadow .2s ease;
    border: 1px solid #f1f5f9;
}
.stat-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 26px rgba(13,50,107,.08);
}

/* ── Senya Insights Gold Banner ── */
.senya-insight-gold {
    background: linear-gradient(135deg, #fffdf8 0%, #fefce8 100%);
    border: 1.5px solid #fbbf24;
    border-radius: 20px;
    padding: 16px 20px;
    display: flex;
    align-items: flex-start;
    gap: 14px;
    box-shadow: 0 4px 16px rgba(245, 158, 11, 0.08);
}
.senya-insight-gold-icon {
    width: 38px;
    height: 38px;
    border-radius: 12px;
    background: linear-gradient(135deg, #f59e0b 0%, #facc15 50%, #fbbf24 100%);
    color: #78350f;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(245, 158, 11, 0.25);
}
.senya-insight-gold-title {
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #b45309;
    margin-bottom: 2px;
}
.senya-insight-gold-text {
    font-size: 13px;
    font-weight: 500;
    color: #78350f;
    line-height: 1.55;
}

/* ── Sign-type tabs ── */
.gsign-tab { display:inline-flex; align-items:center; gap:5px; padding:6px 12px; border-radius:9px;
             font-size:11.5px; font-weight:700; border:1.5px solid #e2e8f0; background:#f8fafc;
             color:#64748b; cursor:pointer; transition:all .15s; }
.gsign-tab:hover  { background:#f1f5f9; border-color:#cbd5e1; color:#0d326b; }
.gsign-tab.active { background:#0d326b; border-color:#0d326b; color:#fff; }

/* ── Status tabs (same shape, separate state) ── */
.gstat-tab { display:inline-flex; align-items:center; gap:4px; padding:6px 12px; border-radius:9px;
             font-size:11.5px; font-weight:700; border:1.5px solid #e2e8f0; background:#f8fafc;
             color:#64748b; cursor:pointer; transition:all .15s; }
.gstat-tab:hover { background:#f1f5f9; border-color:#cbd5e1; color:#0d326b; }
.gstat-tab.active                      { background:#0d326b; border-color:#0d326b; color:#fff; }
.gstat-tab[data-status="critical"].active { background:#0d326b; border-color:#0d326b; color:#fff; }
.gstat-tab[data-status="warning"].active  { background:#1a6fd4; border-color:#1a6fd4; color:#fff; }
.gstat-tab[data-status="good"].active     { background:#93c5fd; border-color:#93c5fd; color:#1e3a5f; }

/* ── Gesture sign cards (compact grid) ── */
.gsign-card { border-radius:16px; border:1px solid #f1f5f9; background:#fff;
              padding:14px 16px; transition:border-color .15s, box-shadow .15s; }
.gsign-card:hover { border-color:#bfdbfe; box-shadow:0 4px 14px rgba(13,50,107,.07); }
.gsign-card.is-critical { border-left:3px solid #0d326b; }
.gsign-card.is-warning  { border-left:3px solid #1a6fd4; }
.gsign-card.is-good     { border-left:3px solid #93c5fd; }
.gsign-card.is-nodata   { border-left:3px solid #e2e8f0; opacity:.7; }

/* ── Accuracy pill ── */
.acc-pill { display:inline-flex; align-items:center; padding:2px 9px; border-radius:9999px;
            font-size:11px; font-weight:800; }
</style>

@php
$bezier = function(array $pts, float $bot, bool $area=false): string {
    if (empty($pts)) return '';
    $d = "M {$pts[0]['x']},{$pts[0]['y']}";
    for ($i=0;$i<count($pts)-1;$i++){
        $dx=($pts[$i+1]['x']-$pts[$i]['x'])/2;
        $d.=" C ".($pts[$i]['x']+$dx).",{$pts[$i]['y']} ".($pts[$i+1]['x']-$dx).",{$pts[$i+1]['y']} {$pts[$i+1]['x']},{$pts[$i+1]['y']}";
    }
    if($area) $d.=" L {$pts[count($pts)-1]['x']},{$bot} L {$pts[0]['x']},{$bot} Z";
    return $d;
};

/* Trend chart */
$TW=600;$TH=220;$TpL=28;$TpR=28;$TpT=16;$TpB=28;
$TpW=$TW-$TpL-$TpR;$TpH=$TH-$TpT-$TpB;$Tbot=$TpT+$TpH;
$Tn=count($trendPoints);
$Tmax=max(1,collect($trendPoints)->max(fn($d)=>max($d['completions'],$d['active_students'])));
$tcPts=[];$tsPts=[];
foreach($trendPoints as $i=>$d){
    $x=$Tn>1?$TpL+($i/($Tn-1))*$TpW:$TpL+$TpW/2;
    $tcPts[]=['x'=>round($x,2),'y'=>round($TpT+$TpH-($d['completions']/$Tmax)*$TpH,2)];
    $tsPts[]=['x'=>round($x,2),'y'=>round($TpT+$TpH-($d['active_students']/$Tmax)*$TpH,2)];
}
$tcLine=$bezier($tcPts,$Tbot);$tcArea=$bezier($tcPts,$Tbot,true);
$tsLine=$bezier($tsPts,$Tbot);$tsArea=$bezier($tsPts,$Tbot,true);

/* Report trend chart */
$RW=600;$RH=180;$RpL=28;$RpR=28;$RpT=14;$RpB=28;
$RpW=$RW-$RpL-$RpR;$RpH=$RH-$RpT-$RpB;$Rbot=$RpT+$RpH;
$Rn=count($reportTrend);
$Rmax=max(1,collect($reportTrend)->max(fn($d)=>max($d['pending'],$d['resolved'])));
$rPPts=[];$rRPts=[];
foreach($reportTrend as $i=>$d){
    $x=$Rn>1?$RpL+($i/($Rn-1))*$RpW:$RpL+$RpW/2;
    $rPPts[]=['x'=>round($x,2),'y'=>round($RpT+$RpH-($d['pending']/$Rmax)*$RpH,2)];
    $rRPts[]=['x'=>round($x,2),'y'=>round($RpT+$RpH-($d['resolved']/$Rmax)*$RpH,2)];
}
$rPLine=$bezier($rPPts,$Rbot);$rPArea=$bezier($rPPts,$Rbot,true);
$rRLine=$bezier($rRPts,$Rbot);$rRArea=$bezier($rRPts,$Rbot,true);

/* Pre-compute gesture breakdown segments */
$criticalCount = $gestureBreakdown->where('status','critical')->count();
$warningCount  = $gestureBreakdown->where('status','warning')->count();
$goodCount     = $gestureBreakdown->where('status','good')->count();
$noDataCount   = $gestureBreakdown->where('status','no_data')->count();

/* Moving gestures */
$dynamicGestures = $gestureBreakdown->where('sign_type','dynamic');
$dTotalAttempts  = $dynamicGestureStats?->total_attempts  ?? 0;
$dTotalSuccess   = $dynamicGestureStats?->total_successful ?? 0;
$dTotalMastered  = $dynamicGestureStats?->total_mastered   ?? 0;
$dStudents       = $dynamicGestureStats?->students_practiced ?? 0;
$dAcc            = $dTotalAttempts > 0 ? round(($dTotalSuccess/$dTotalAttempts)*100,1) : 0;
@endphp

{{-- ── SKELETON ─────────────────────────────────────────────────────────── --}}
<div id="page-skeleton" class="flex flex-col gap-5 pt-4" aria-hidden="true">
    {{-- Filter bar --}}
    <div class="bg-white rounded-[22px] border border-slate-100 shadow-sm p-4 flex gap-3 flex-wrap">
        @for($i=0;$i<2;$i++)<div class="skeleton h-9 rounded-[14px] w-36"></div>@endfor
        <div class="ml-auto skeleton h-9 rounded-[14px] w-24"></div>
    </div>
    {{-- 4 KPI cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        <div class="skeleton skeleton-card h-[120px]"></div>
        @for($i=0;$i<2;$i++)
        <div class="bg-white rounded-[24px] p-6 border border-slate-100 shadow-sm flex flex-col gap-3">
            <div class="flex justify-between"><div class="skeleton h-3 rounded w-32"></div><div class="skeleton w-10 h-10 rounded-xl"></div></div>
            <div class="skeleton h-9 rounded w-20"></div>
            <div class="skeleton h-3 rounded w-36"></div>
        </div>
        @endfor
        <div class="skeleton skeleton-card h-[120px]"></div>
    </div>
    {{-- Insight banner --}}
    <div class="skeleton rounded-[18px] h-16 w-full"></div>
    {{-- Main grid: chart (2/3) + gesture overview (1/3) --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="lg:col-span-2 bg-white rounded-[20px] border border-slate-100 shadow-sm p-6 flex flex-col gap-4">
            <div class="flex justify-between pb-3 border-b border-slate-100">
                <div class="flex flex-col gap-2"><div class="skeleton h-5 rounded w-40"></div><div class="skeleton h-3 rounded w-56"></div></div>
                <div class="skeleton h-5 rounded w-32"></div>
            </div>
            <div class="skeleton rounded-2xl w-full" style="padding-bottom:38%;"></div>
            <div class="skeleton rounded-[18px] h-14 w-full"></div>
        </div>
        <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm p-6 flex flex-col gap-4">
            <div class="skeleton h-4 rounded w-40"></div>
            <div class="skeleton rounded-2xl h-20 w-full"></div>
            <div class="grid grid-cols-2 gap-3">
                @for($i=0;$i<4;$i++)<div class="skeleton rounded-2xl h-20"></div>@endfor
            </div>
            <div class="skeleton rounded-2xl h-20 w-full"></div>
        </div>
    </div>
    {{-- Gesture breakdown panel --}}
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm overflow-hidden">
        <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
            <div class="flex flex-col gap-2"><div class="skeleton h-5 rounded w-52"></div><div class="skeleton h-3 rounded w-36"></div></div>
            <div class="flex gap-2">
                @for($i=0;$i<4;$i++)<div class="skeleton h-8 rounded-[9px] w-20"></div>@endfor
            </div>
        </div>
        <div class="p-6 grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
            @for($i=0;$i<16;$i++)<div class="skeleton rounded-2xl h-24"></div>@endfor
        </div>
    </div>
</div>
{{-- ── END SKELETON ─────────────────────────────────────────────────────── --}}

<div class="flex flex-col gap-5 pt-4 skeleton-hide">

{{-- ══ 1. FILTER BAR ══════════════════════════════════════════════════════ --}}
<form method="GET" action="{{ route('admin.analytics') }}" id="adminAnalyticsFilterForm">
    <div class="filter-container">
        <div class="filter-group">
            <div class="flex items-center gap-2 mr-2">
                <span class="material-symbols-outlined text-[#0d326b] text-[22px]">tune</span>
                <span class="text-[13px] font-bold text-[#0d326b] uppercase tracking-wider">Filter Analytics</span>
            </div>

            <div class="filter-wrap">
                <select name="school_year" id="adminSchoolYearFilter" class="filter-select">
                    <option value="all" {{ $selectedSchoolYear === 'all' ? 'selected' : '' }}>All School Years</option>
                    @foreach($schoolYearFilterOptions as $schoolYearOption)
                    <option value="{{ $schoolYearOption['name'] }}" data-schools="{{ implode(',', $schoolYearOption['school_ids']) }}" {{ $selectedSchoolYear === $schoolYearOption['name'] ? 'selected' : '' }}>S.Y. {{ $schoolYearOption['name'] }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined">expand_more</span>
            </div>

            <div class="filter-wrap">
                <select name="school" id="adminSchoolFilter" class="filter-select">
                    <option value="all" {{ $selectedSchoolId === 'all' ? 'selected' : '' }}>All Schools</option>
                    @foreach($schoolFilterOptions as $schoolOption)
                    <option value="{{ $schoolOption['school_id'] }}" {{ (string) $selectedSchoolId === (string) $schoolOption['school_id'] ? 'selected' : '' }}>{{ $schoolOption['school'] }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined">expand_more</span>
            </div>

            <a href="{{ route('admin.analytics') }}" class="filter-reset">Reset</a>
            <button type="submit" class="filter-btn">
                <span class="material-symbols-outlined text-[16px]">refresh</span>
                Apply
            </button>
        </div>
    </div>
</form>

<script>
(() => {
    const school = document.getElementById('adminSchoolFilter');
    const schoolYear = document.getElementById('adminSchoolYearFilter');
    if (!school || !schoolYear) return;
    const syncSchoolYears = () => {
        Array.from(schoolYear.options).forEach(option => {
            if (option.value === 'all') return;
            const availableSchools = (option.dataset.schools || '').split(',');
            const available = school.value === 'all' || availableSchools.includes(school.value);
            option.disabled = !available;
            option.hidden = !available;
        });
        if (schoolYear.selectedOptions[0]?.disabled) schoolYear.value = 'all';
    };
    school.addEventListener('change', syncSchoolYears);
    syncSchoolYears();
})();
</script>

{{-- ══ 2. KPI CARDS ════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

    {{-- Card 1: Total Users — navy gradient --}}
    <div class="stat-kpi-card text-white" style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%);">
        <div class="flex items-center justify-between mb-4">
            <span class="text-[11px] font-bold uppercase tracking-wider text-white/70">Total Users</span>
            <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px] text-white">group</span>
            </div>
        </div>
        <p class="text-[36px] font-black leading-none mb-1 text-white tracking-tight">{{ number_format($totalUsers) }}</p>
        <p class="text-[12px] text-white/70 font-medium">{{ $totalStudents }} students · {{ $totalTeachers }} teachers · {{ $totalGradeLeaders }} grade leaders</p>
    </div>

    {{-- Card 2: Enrolled Students — white --}}
    <div class="stat-kpi-card bg-white">
        <div class="flex items-center justify-between mb-4">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Enrolled Students</span>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0d326b] flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">school</span>
            </div>
        </div>
        <p class="text-[36px] font-black leading-none mb-1 text-[#0d326b] tracking-tight">{{ number_format($totalStudents) }}</p>
        <p class="text-[12px] text-slate-400 font-medium">
            {{ $selectedSchoolId === 'all' ? 'All schools' : 'Selected school' }} ·
            {{ $selectedSchoolYear === 'all' ? 'Current enrolled roster' : 'S.Y. ' . $selectedSchoolYear }}
        </p>
    </div>

    {{-- Card 3: Lessons Completed — white --}}
    <div class="stat-kpi-card bg-white">
        <div class="flex items-center justify-between mb-4">
            <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Lessons Completed</span>
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#1a6fd4] flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">menu_book</span>
            </div>
        </div>
        <p class="text-[36px] font-black leading-none mb-1 text-[#0d326b] tracking-tight">{{ number_format($totalLessonsCompleted) }}</p>
        <p class="text-[12px] text-slate-400 font-medium">{{ number_format($totalQuizAttempts) }} quiz attempts total</p>
    </div>

    {{-- Card 4: Avg Quiz Score — amber gradient --}}
    <div class="stat-kpi-card text-amber-950" style="background:linear-gradient(135deg,#f59e0b 0%,#facc15 50%,#fbbf24 100%);border-color:rgba(245,158,11,.5);box-shadow:0 4px 16px rgba(245,158,11,.22);">
        <div class="flex items-center justify-between mb-4">
            <span class="text-[11px] font-black uppercase tracking-wider text-amber-950/80">Avg Quiz Score</span>
            <div class="w-10 h-10 rounded-xl bg-white/35 text-amber-950 flex items-center justify-center backdrop-blur-sm shadow-sm">
                <span class="material-symbols-outlined text-[20px]">insights</span>
            </div>
        </div>
        <p class="text-[36px] font-black leading-none mb-1 text-amber-950 tracking-tight">{{ round($avgQuizScore,1) }}%</p>
        <p class="text-[12px] text-amber-950/80 font-bold">Average score in this selection</p>
    </div>

</div>

{{-- ══ 3. SENYA PLATFORM INSIGHT BANNER ═══════════════════════════════════ --}}
@php
$gAcc = ($gestureStats && $gestureStats->total_attempts > 0)
    ? round(($gestureStats->total_successful / $gestureStats->total_attempts) * 100, 1)
    : 0;
$activePct = $totalStudents > 0 ? round(($activeStudents / $totalStudents) * 100, 1) : 0;
$formattedScore = number_format($avgQuizScore, 1);
$scopeLabel = ($selectedSchoolId === 'all' ? 'all schools' : 'the selected school')
    . ($selectedSchoolYear === 'all' ? ' across all school years' : ' in S.Y. ' . $selectedSchoolYear);

if ($totalUsers === 0) {
    $adminInsight = "No platform data yet. Insights will appear once teachers and students start using SEÑAS.";
} elseif ($avgQuizScore >= 75 && $gAcc >= 70) {
    $adminInsight = "<strong>Performance is strong for {$scopeLabel}.</strong> The average quiz score is <strong>{$formattedScore}%</strong> and gesture accuracy is <strong>{$gAcc}%</strong>. Keep monitoring engagement to sustain this momentum.";
} elseif ($avgQuizScore < 50) {
    $adminInsight = "<strong>Quiz scores need attention for {$scopeLabel}.</strong> The average is <strong>{$formattedScore}%</strong>. Consider prompting teachers to review lesson content or add more practice activities for struggling students.";
} elseif ($gAcc < 50 && $gestureStats && $gestureStats->total_attempts > 0) {
    $adminInsight = "<strong>Gesture accuracy is below target at {$gAcc}%.</strong> A significant number of students are struggling with sign recognition. Review the gesture breakdown below to identify signs that need curriculum attention.";
} elseif ($activePct < 30) {
    $adminInsight = "<strong>Student engagement is low — only {$activePct}% of students were active in the last 7 days.</strong> Encourage teachers to assign new lessons or issue daily challenges to re-engage inactive students.";
} else {
    $mastered = number_format($totalGestureMastered);
    $adminInsight = "For {$scopeLabel}, <strong>{$totalStudents} students</strong> have attempted <strong>" . number_format($gestureStats->total_attempts ?? 0) . " gestures</strong> with an accuracy of <strong>{$gAcc}%</strong> and <strong>{$mastered} signs mastered</strong>. Average quiz score stands at <strong>{$formattedScore}%</strong>.";
}
@endphp

<div class="senya-insight-gold">
    <div class="senya-insight-gold-icon">
        <span class="material-symbols-outlined text-[20px]">lightbulb</span>
    </div>
    <div>
        <div class="senya-insight-gold-title">Senya Analytics Insight</div>
        <div class="senya-insight-gold-text">{!! $adminInsight !!}</div>
    </div>
</div>

{{-- ══ 4. TREND CHART + GESTURE OVERVIEW ══════════════════════════════════ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">

    {{-- Trend chart (2/3) --}}
    <div class="lg:col-span-2 a-panel">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Platform Trend</p>
                <h3 class="text-[15px] font-bold text-[#0d326b]">Platform Usage Over Time</h3>
                <p class="text-[12px] text-slate-400 mt-0.5">Lesson completions vs active students</p>
            </div>
            <div class="flex items-center gap-4 text-[11px] font-semibold shrink-0">
                <span class="flex items-center gap-1.5">
                    <span class="w-7 h-1.5 rounded-full bg-[#0d326b] inline-block"></span>Completions
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-7 h-1.5 rounded-full bg-[#1a6fd4] inline-block" style="border:none;border-top:2px dashed #1a6fd4;background:transparent;height:0"></span>Active
                </span>
            </div>
        </div>
        <div class="bg-[#f8fafc] rounded-2xl w-full relative" style="padding-bottom:38%">
            <svg viewBox="0 0 {{ $TW }} {{ $TH }}" class="absolute inset-0 w-full h-full" overflow="visible">
                <defs>
                    <linearGradient id="adm_tCFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#0d326b" stop-opacity=".18"/><stop offset="100%" stop-color="#0d326b" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="adm_tSFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#1a6fd4" stop-opacity=".12"/><stop offset="100%" stop-color="#1a6fd4" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="adm_tCLine" x1="0" y1="0" x2="100%" y2="0">
                        <stop offset="0%" stop-color="#1e4b8f"/><stop offset="100%" stop-color="#071c3f"/>
                    </linearGradient>
                    <linearGradient id="adm_tSLine" x1="0" y1="0" x2="100%" y2="0">
                        <stop offset="0%" stop-color="#3b82f6"/><stop offset="100%" stop-color="#1a6fd4"/>
                    </linearGradient>
                </defs>
                @foreach([0,25,50,75,100] as $gv)
                    @php $gy=round($TpT+$TpH-($gv/100)*$TpH,1); @endphp
                    <line x1="{{ $TpL }}" y1="{{ $gy }}" x2="{{ $TpL+$TpW }}" y2="{{ $gy }}"
                          stroke="#e8ecf2" stroke-width="1" stroke-dasharray="4,4"/>
                    <text x="4" y="{{ $gy+3.5 }}" font-size="8.5" fill="#cbd5e1" font-weight="600">{{ $gv }}</text>
                @endforeach
                <path d="{{ $tcArea }}" fill="url(#adm_tCFill)"/>
                <path d="{{ $tsArea }}" fill="url(#adm_tSFill)"/>
                <path d="{{ $tcLine }}" fill="none" stroke="url(#adm_tCLine)" stroke-width="2.5" stroke-linecap="round"/>
                <path d="{{ $tsLine }}" fill="none" stroke="url(#adm_tSLine)" stroke-width="2" stroke-linecap="round" stroke-dasharray="5,3"/>
                @foreach($tcPts as $i=>$p)
                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="{{ $i===count($tcPts)-1?4.5:3 }}"
                            fill="{{ $i===count($tcPts)-1?'#0d326b':'#1e4b8f' }}" stroke="white" stroke-width="2"/>
                @endforeach
                @foreach($trendPoints as $i=>$d)
                    @if($i%max(1,intval(count($trendPoints)/6))===0||$i===count($trendPoints)-1)
                        <text x="{{ $tcPts[$i]['x'] }}" y="{{ $TH-8 }}" font-size="9.5" fill="#94a3b8"
                              font-weight="600" text-anchor="middle">{{ $d['label'] }}</text>
                    @endif
                @endforeach
            </svg>
        </div>

        {{-- ── Trend Insight (inside the chart card) ── --}}
        @php
        $lastPoint   = end($trendPoints);
        $prevPoint   = count($trendPoints) > 1 ? $trendPoints[count($trendPoints) - 2] : null;
        $trendDir    = ($prevPoint && $lastPoint['completions'] > $prevPoint['completions']) ? 'up' : (($prevPoint && $lastPoint['completions'] < $prevPoint['completions']) ? 'down' : 'stable');
        $totalPeriodCompletions = collect($trendPoints)->sum('completions');
        $totalPeriodActive      = collect($trendPoints)->sum('active_students');
        $trendInsight = match(true) {
            $totalPeriodCompletions === 0 => "No lesson completions recorded in this period yet. Encourage teachers to assign lessons and set deadlines to drive activity.",
            $trendDir === 'up'            => "<strong>Lesson completions are trending up</strong> — the most recent period shows <strong>" . number_format($lastPoint['completions']) . " completions</strong>. Student engagement is growing across the platform.",
            $trendDir === 'down'          => "<strong>Completions dipped in the most recent period</strong> ({$lastPoint['completions']}). Check if teachers have upcoming assignment deadlines and remind them to keep students engaged.",
            default                       => "Lesson completions are <strong>steady at " . number_format($lastPoint['completions']) . "</strong> this period with <strong>{$totalPeriodActive} active student sessions</strong> tracked. Consistent engagement is a good sign.",
        };
        @endphp
        <div class="senya-insight-gold mt-4">
            <div class="senya-insight-gold-icon">
                <span class="material-symbols-outlined text-[20px]">trending_up</span>
            </div>
            <div>
                <div class="senya-insight-gold-title">Platform Trend Insight</div>
                <div class="senya-insight-gold-text">{!! $trendInsight !!}</div>
            </div>
        </div>
    </div>

    {{-- Gesture overview (1/3) --}}
    <div class="a-panel flex flex-col gap-4">
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">System-wide</p>
            <h3 class="text-[15px] font-bold text-[#0d326b]">Gesture Overview</h3>
            <p class="text-[12px] text-slate-400 mt-0.5">All students · all signs</p>
        </div>
        @php
            $gs   = $gestureStats;
            $gAcc = ($gs && $gs->total_attempts > 0)
                  ? round(($gs->total_successful / $gs->total_attempts) * 100, 1) : 0;
            $gWrong     = (int)($gs->total_attempts ?? 0) - (int)($gs->total_successful ?? 0);
            $successPct = $gAcc;
            $wrongPct   = $gs && $gs->total_attempts > 0
                        ? round(($gWrong / $gs->total_attempts) * 100, 1) : 0;
            $staticCount  = $gestureBreakdown->where('sign_type','static')->count();
            $dynamicCount = $gestureBreakdown->where('sign_type','dynamic')->count();
            $totalSigns   = $gestureBreakdown->count();
            $worstSign    = $gestureBreakdown->where('status','critical')->sortBy('accuracy')->first();
            $topSign      = $gestureBreakdown->where('status','good')->sortByDesc('accuracy')->first();
            $r=30; $circ=round(2*M_PI*$r,2);
            $dash=round($gAcc/100*$circ,2);
        @endphp

        {{-- Overall accuracy ring --}}
        <div class="flex items-center gap-4 p-4 rounded-2xl" style="background:linear-gradient(135deg,#0d326b,#1e4b8f)">
            <svg width="72" height="72" viewBox="0 0 72 72" class="shrink-0">
                <circle cx="36" cy="36" r="{{ $r }}" fill="none" stroke="rgba(255,255,255,.15)" stroke-width="8"/>
                <circle cx="36" cy="36" r="{{ $r }}" fill="none" stroke="white" stroke-width="8"
                        stroke-dasharray="{{ $dash }} {{ $circ }}"
                        stroke-dashoffset="{{ round($circ/4,2) }}" stroke-linecap="round"/>
                <text x="36" y="41" text-anchor="middle" font-size="14" font-weight="800" fill="white">{{ $gAcc }}%</text>
            </svg>
            <div class="flex-1 min-w-0">
                <p class="text-[22px] font-black text-white leading-none">{{ number_format($gs->total_attempts ?? 0) }}</p>
                <p class="text-[11px] text-white/70 font-semibold mt-0.5">total attempts</p>
                <p class="text-[11px] text-white/60 mt-2">{{ number_format($gs->total_mastered ?? 0) }} signs mastered</p>
                {{-- Mini success/fail bar --}}
                <div class="mt-2.5 w-full bg-white/15 rounded-full h-1.5 overflow-hidden">
                    <div class="h-1.5 rounded-full bg-white/80" style="width:{{ $successPct }}%"></div>
                </div>
                <div class="flex justify-between mt-1">
                    <span class="text-[9px] text-white/50 font-semibold">{{ $successPct }}% correct</span>
                    <span class="text-[9px] text-white/40 font-semibold">{{ $wrongPct }}% wrong</span>
                </div>
            </div>
        </div>

        {{-- 4-stat grid --}}
        <div class="grid grid-cols-2 gap-3">
            <div class="rounded-2xl p-3.5 bg-[#eff6ff] border border-[#bfdbfe]">
                <span class="material-symbols-outlined text-[20px] text-[#0d326b]">group</span>
                <p class="text-[22px] font-black text-[#0d326b] leading-none mt-1">{{ $gs->students_who_practiced ?? 0 }}</p>
                <p class="text-[10px] font-semibold text-slate-500 mt-1">Students practiced</p>
            </div>
            <div class="rounded-2xl p-3.5 bg-[#dbeafe] border border-[#93c5fd]">
                <span class="material-symbols-outlined text-[20px] text-[#1e4b8f]">verified</span>
                <p class="text-[22px] font-black text-[#1e4b8f] leading-none mt-1">{{ number_format($gs->total_mastered ?? 0) }}</p>
                <p class="text-[10px] font-semibold text-slate-500 mt-1">Gestures mastered</p>
            </div>
            <div class="rounded-2xl p-3.5 bg-[#f0fdf4] border border-[#bbf7d0]">
                <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>
                <p class="text-[22px] font-black text-emerald-700 leading-none mt-1">{{ number_format($gs->total_successful ?? 0) }}</p>
                <p class="text-[10px] font-semibold text-slate-500 mt-1">Successful attempts</p>
            </div>
            <div class="rounded-2xl p-3.5 bg-[#fff7ed] border border-[#fed7aa]">
                <span class="material-symbols-outlined text-[20px] text-orange-500">cancel</span>
                <p class="text-[22px] font-black text-orange-600 leading-none mt-1">{{ number_format($gWrong) }}</p>
                <p class="text-[10px] font-semibold text-slate-500 mt-1">Wrong attempts</p>
            </div>
        </div>

        {{-- Static vs Dynamic split --}}
        <div class="rounded-2xl border border-slate-100 bg-[#f8fafc] p-3.5">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2.5">Sign Type Split</p>
            <div class="flex items-center gap-2 mb-1.5">
                <span class="text-[11px] font-bold text-slate-600 w-14 shrink-0">Static</span>
                <div class="flex-1 bg-slate-200 rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full bg-[#0d326b]"
                         style="width:{{ $totalSigns > 0 ? round(($staticCount/$totalSigns)*100) : 0 }}%"></div>
                </div>
                <span class="text-[11px] font-black text-[#0d326b] w-6 text-right shrink-0">{{ $staticCount }}</span>
            </div>
            <div class="flex items-center gap-2">
                <span class="text-[11px] font-bold text-slate-600 w-14 shrink-0">Moving</span>
                <div class="flex-1 bg-slate-200 rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full bg-[#1a6fd4]"
                         style="width:{{ $totalSigns > 0 ? round(($dynamicCount/$totalSigns)*100) : 0 }}%"></div>
                </div>
                <span class="text-[11px] font-black text-[#1a6fd4] w-6 text-right shrink-0">{{ $dynamicCount }}</span>
            </div>
        </div>


    </div>

</div>

{{-- ══ 6. SCHOOL, TEACHER, PROGRAM, AND HELP REQUEST ANALYTICS ══ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Teacher Activity --}}
    <div class="a-panel !p-0 overflow-hidden order-4 lg:col-span-2">
        <div class="px-6 pt-5 pb-4 border-b border-slate-50">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Rankings</p>
            <h3 class="text-[15px] font-bold text-[#0d326b]">Teacher Activity</h3>
            <p class="text-[12px] text-slate-400 mt-0.5">Ranked by total lesson completions</p>
        </div>
        <div class="max-h-[350px] overflow-auto">
            <table class="w-full text-[13px]">
                <thead class="sticky top-0 z-10">
                    <tr class="border-b border-slate-50 bg-[#f8fafc]">
                        <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400 w-8">#</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">Teacher</th>
                        <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-wider text-slate-400">Students</th>
                        <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-wider text-slate-400">Lessons</th>
                        <th class="px-4 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">School</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse($teacherActivity as $i=>$t)
                    <tr class="hover:bg-[#f8fafc] transition-colors">
                        <td class="px-5 py-3">
                            <span class="w-6 h-6 rounded-full text-[11px] font-black flex items-center justify-center
                                {{ $i===0?'text-white':'text-slate-500 bg-slate-100' }}"
                                style="{{ $i===0?'background:linear-gradient(135deg,#0d326b,#1a6fd4)':'' }}">{{ $i+1 }}</span>
                        </td>
                        <td class="px-5 py-3">
                            <p class="font-bold text-slate-800 text-[12.5px]">{{ $t['name'] }}</p>
                            <p class="text-[10px] text-slate-400">{{ $t['email'] }}</p>
                        </td>
                        <td class="px-4 py-3 text-center font-semibold text-slate-600 text-[12px]">{{ $t['students'] }}</td>
                        <td class="px-4 py-3 text-center font-semibold text-slate-600 text-[12px]">{{ $t['lessons'] }}</td>
                        <td class="px-4 py-3 text-left text-[11px] font-semibold text-slate-600"><span class="block max-w-36 truncate" title="{{ $t['school'] }}">{{ $t['school'] }}</span></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400 text-[13px]">No teacher data yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Teacher Ranking Insight --}}
        @php
        $topTeacher    = $teacherActivity->first();
        $activeTeachN  = $teacherActivity->where('active_students', '>', 0)->count();
        $totalTeachN   = $teacherActivity->count();
        $teacherInsight = match(true) {
            $totalTeachN === 0
                => "No teacher activity recorded yet. Teachers haven't published lessons or had student completions this period.",
            $topTeacher && $topTeacher['completions'] > 0
                => "<strong>{$topTeacher['name']}</strong> leads the platform with <strong>" . number_format($topTeacher['completions']) . " completions</strong> across <strong>{$topTeacher['students']} students</strong>." . ($activeTeachN < $totalTeachN ? " Note: " . ($totalTeachN - $activeTeachN) . " of {$totalTeachN} teachers have no active students this week — consider reaching out." : ' All teachers have active students this period.'),
            default
                => "Teacher activity data is available but no completions have been recorded this period. Encourage teachers to assign and follow up on lesson progress.",
        };
        @endphp
        <div class="px-5 pb-5 pt-4 border-t border-slate-50">
            <div class="senya-insight-gold">
                <div class="senya-insight-gold-icon">
                    <span class="material-symbols-outlined text-[19px]">insights</span>
                </div>
                <div>
                    <div class="senya-insight-gold-title">Teacher Ranking Insight</div>
                    <div class="senya-insight-gold-text">{!! $teacherInsight !!}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Users by School and each school's own active school year --}}
    <div class="a-panel order-1">
        <div class="flex items-start justify-between gap-4 mb-4">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">School Year Aware</p>
                <h3 class="text-[15px] font-bold text-[#0d326b]">School Users by Role</h3>
                <p class="text-[12px] text-slate-400 mt-1">{{ $selectedSchoolYear === 'all' ? "Student counts use each school's active roster." : 'Student counts use the selected school-year roster.' }} Teacher and grade leader counts are school accounts.</p>
            </div>
            <span class="material-symbols-outlined text-slate-300 text-[22px]">bar_chart</span>
        </div>

        @php
            $schoolChartCount = $schoolUserCounts->count();
            $schoolChartW = max(640, $schoolChartCount * 112 + 72);
            $schoolChartH = 280; $schoolPlotL = 44; $schoolPlotR = 18; $schoolPlotT = 22; $schoolPlotB = 76;
            $schoolPlotW = $schoolChartW - $schoolPlotL - $schoolPlotR;
            $schoolPlotH = $schoolChartH - $schoolPlotT - $schoolPlotB;
            $schoolSlot = $schoolChartCount > 0 ? $schoolPlotW / $schoolChartCount : $schoolPlotW;
            $schoolBarW = max(32, min(66, round($schoolSlot * .56)));
            $schoolMaxUsers = max(1, (int) $schoolUserCounts->max(fn ($school) => $school['students'] + $school['teachers'] + $school['grade_leaders']));
            $schoolRoleColors = ['students' => '#bfdbfe', 'teachers' => '#3b82f6', 'grade_leaders' => '#0d326b'];
        @endphp

        @if($schoolChartCount > 0)
        <div class="overflow-x-auto">
            <svg viewBox="0 0 {{ $schoolChartW }} {{ $schoolChartH }}" role="img" aria-label="User counts by school and role" style="width:max(100%, {{ $schoolChartW }}px);height:auto;" preserveAspectRatio="xMidYMid meet">
                @foreach([0,25,50,75,100] as $tick)
                    @php
                        $tickY = round($schoolPlotT + $schoolPlotH - ($tick / 100) * $schoolPlotH, 1);
                        $tickLabel = (int) round($schoolMaxUsers * $tick / 100);
                    @endphp
                    <line x1="{{ $schoolPlotL }}" y1="{{ $tickY }}" x2="{{ $schoolChartW - $schoolPlotR }}" y2="{{ $tickY }}" stroke="#e8eef6" stroke-width="1" stroke-dasharray="3,4" />
                    <text x="{{ $schoolPlotL - 8 }}" y="{{ $tickY + 4 }}" font-size="10" fill="#94a3b8" font-weight="600" text-anchor="end">{{ $tickLabel }}</text>
                @endforeach
                <line x1="{{ $schoolPlotL }}" y1="{{ $schoolPlotT + $schoolPlotH }}" x2="{{ $schoolChartW - $schoolPlotR }}" y2="{{ $schoolPlotT + $schoolPlotH }}" stroke="#cbd5e1" stroke-width="1.4" />

                @foreach($schoolUserCounts as $index => $school)
                    @php
                        $centerX = round($schoolPlotL + ($index + .5) * $schoolSlot, 1);
                        $barX = round($centerX - $schoolBarW / 2, 1);
                        $stackOffset = 0;
                        $roleValues = [
                            ['key' => 'students', 'count' => $school['students']],
                            ['key' => 'teachers', 'count' => $school['teachers']],
                            ['key' => 'grade_leaders', 'count' => $school['grade_leaders']],
                        ];
                        $totalUsersForSchool = array_sum(array_column($roleValues, 'count'));
                    @endphp
                    @foreach($roleValues as $role)
                        @if($role['count'] > 0)
                            @php
                                $segmentHeight = max(2, round(($role['count'] / $schoolMaxUsers) * $schoolPlotH, 1));
                                $segmentY = round($schoolPlotT + $schoolPlotH - $stackOffset - $segmentHeight, 1);
                                $stackOffset += $segmentHeight;
                            @endphp
                            <rect x="{{ $barX }}" y="{{ $segmentY }}" width="{{ $schoolBarW }}" height="{{ $segmentHeight }}" fill="{{ $schoolRoleColors[$role['key']] }}" rx="{{ $role['key'] === 'grade_leaders' ? 4 : 1 }}">
                                <title>{{ $school['school'] }} — {{ str_replace('_', ' ', ucfirst($role['key'])) }}: {{ number_format($role['count']) }}</title>
                            </rect>
                            @if($segmentHeight >= 18)
                            <text x="{{ $centerX }}" y="{{ $segmentY + ($segmentHeight / 2) + 3 }}" font-size="9" fill="{{ $role['key'] === 'students' ? '#0d326b' : '#ffffff' }}" font-weight="800" text-anchor="middle">{{ $role['count'] }}</text>
                            @endif
                        @endif
                    @endforeach
                    <text x="{{ $centerX }}" y="{{ max($schoolPlotT + 12, $schoolPlotT + $schoolPlotH - $stackOffset - 7) }}" font-size="10" fill="#0d326b" font-weight="800" text-anchor="middle">{{ number_format($totalUsersForSchool) }}</text>
                    <text x="{{ $centerX }}" y="{{ $schoolChartH - 48 }}" font-size="10" fill="#475569" font-weight="700" text-anchor="middle">
                        <title>{{ $school['school'] }}</title>{{ \Illuminate\Support\Str::limit($school['school'], 16) }}
                    </text>
                    <text x="{{ $centerX }}" y="{{ $schoolChartH - 27 }}" font-size="9" fill="#94a3b8" font-weight="600" text-anchor="middle">{{ $school['school_year'] ? 'S.Y. ' . $school['school_year'] : 'No active S.Y.' }}</text>
                @endforeach
            </svg>
        </div>

        <div class="flex flex-wrap items-center gap-x-6 gap-y-2 mt-3 pt-3 border-t border-slate-100">
            @foreach([['key' => 'students', 'label' => 'Students'], ['key' => 'teachers', 'label' => 'Teachers'], ['key' => 'grade_leaders', 'label' => 'Grade Leaders']] as $roleLegend)
            <div class="flex items-center gap-2">
                <span class="inline-block w-3 h-3 rounded-sm" style="background:{{ $schoolRoleColors[$roleLegend['key']] }}"></span>
                <span class="text-[11px] font-semibold text-slate-600">{{ $roleLegend['label'] }}</span>
            </div>
            @endforeach
            <span class="text-[10px] text-slate-400 ml-auto">Admin accounts are not school-linked and are excluded.</span>
        </div>
        @php
            $topSchoolByStudents = $schoolUserCounts->sortByDesc('students')->first();
            $schoolStudentTotal = $schoolUserCounts->sum('students');
            $schoolTeacherTotal = $schoolUserCounts->sum('teachers');
            $schoolLeaderTotal = $schoolUserCounts->sum('grade_leaders');
            $schoolChartInsight = $schoolStudentTotal > 0 && $topSchoolByStudents
                ? '<strong>' . e($topSchoolByStudents['school']) . '</strong> has the most enrolled students with <strong>' . number_format($topSchoolByStudents['students']) . '</strong>. Across <strong>' . $schoolChartCount . ' schools</strong>, the chart shows <strong>' . number_format($schoolStudentTotal) . ' students, ' . number_format($schoolTeacherTotal) . ' teachers, and ' . number_format($schoolLeaderTotal) . ' grade leaders</strong>.'
                : 'No active school-year student enrollments are recorded. The chart still shows school-linked teacher and grade leader accounts.';
        @endphp
        <div class="senya-insight-gold mt-4">
            <div class="senya-insight-gold-icon"><span class="material-symbols-outlined text-[19px]">lightbulb</span></div>
            <div>
                <div class="senya-insight-gold-title">School Users Insight</div>
                <div class="senya-insight-gold-text">{!! $schoolChartInsight !!}</div>
            </div>
        </div>
        @else
        <div class="py-14 text-center text-[13px] font-semibold text-slate-400">No schools are registered yet.</div>
        @endif
    </div>

    {{-- Student Overview: classification and mastery --}}
    <div class="a-panel order-2 flex flex-col">
        <div class="flex items-start justify-between pb-4 border-b border-slate-100 mb-4">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Students</p>
                <h3 class="text-[15px] font-bold text-[#0d326b]">Student Overview</h3>
            </div>
            <span class="material-symbols-outlined text-slate-300 text-[22px]">donut_large</span>
        </div>
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Classification</div>
        @php
            $totalP = $programDistribution->sum('count');
            $programPalette = [
                ['color' => '#0d326b', 'from' => '#1e4b8f', 'to' => '#071c3f'],
                ['color' => '#1a6fd4', 'from' => '#3b82f6', 'to' => '#1a6fd4'],
                ['color' => '#60a5fa', 'from' => '#93c5fd', 'to' => '#3b82f6'],
                ['color' => '#93c5fd', 'from' => '#bfdbfe', 'to' => '#60a5fa'],
                ['color' => '#1e40af', 'from' => '#3b82f6', 'to' => '#1e3a8a'],
                ['color' => '#38bdf8', 'from' => '#7dd3fc', 'to' => '#0284c7'],
                ['color' => '#64748b', 'from' => '#94a3b8', 'to' => '#475569'],
            ];
            $programDonut = [];
            $donutCx = 80; $donutCy = 80; $donutInner = 40;
            $programActiveCount = $programDistribution->count();
            $programMaxCount = max(1, (int) $programDistribution->max('count'));
            $programCurrentAngle = -pi() / 2;
            $donutGapAngle = $programActiveCount > 1 ? .08 : 0;
            foreach ($programDistribution->values() as $i => $pd) {
                $fraction = $totalP > 0 ? $pd->count / $totalP : 0;
                $angleSpan = $fraction * (2 * pi());
                $outerR = round(56 + (18 * ($pd->count / $programMaxCount)), 1);
                if ($programActiveCount === 1) {
                    $start = $programCurrentAngle;
                    $end = $programCurrentAngle + (2 * pi()) - .001;
                } else {
                    $start = $programCurrentAngle + ($donutGapAngle / 2);
                    $end = $programCurrentAngle + $angleSpan - ($donutGapAngle / 2);
                    if ($end <= $start) {
                        $start = $programCurrentAngle;
                        $end = $programCurrentAngle + $angleSpan;
                    }
                }
                $x1 = round($donutCx + $outerR * cos($start), 2);
                $y1 = round($donutCy + $outerR * sin($start), 2);
                $x2 = round($donutCx + $outerR * cos($end), 2);
                $y2 = round($donutCy + $outerR * sin($end), 2);
                $x3 = round($donutCx + $donutInner * cos($end), 2);
                $y3 = round($donutCy + $donutInner * sin($end), 2);
                $x4 = round($donutCx + $donutInner * cos($start), 2);
                $y4 = round($donutCy + $donutInner * sin($start), 2);
                $largeArc = ($end - $start) > pi() ? 1 : 0;
                $palette = $programPalette[$i % count($programPalette)];
                $programDonut[] = [
                    'label' => $pd->program_type ?? 'N/A', 'count' => (int) $pd->count,
                    'pct' => round($fraction * 100), 'color' => $palette['color'],
                    'from' => $palette['from'], 'to' => $palette['to'], 'gradient_id' => 'adminProgramDonut' . $i,
                    'outerR' => $outerR,
                    'path' => "M {$x1} {$y1} A {$outerR} {$outerR} 0 {$largeArc} 1 {$x2} {$y2} L {$x3} {$y3} A {$donutInner} {$donutInner} 0 {$largeArc} 0 {$x4} {$y4} Z",
                ];
                $programCurrentAngle += $angleSpan;
            }
        @endphp
        @if($totalP > 0)
        <div class="flex items-center justify-center gap-5 py-2 w-full max-w-[500px] mx-auto">
            <div class="relative w-[124px] h-[124px] shrink-0">
                <svg class="w-full h-full overflow-visible" viewBox="0 0 160 160" role="img" aria-label="Student classification donut chart">
                    <defs>
                        <filter id="adminProgramSliceShadow" x="-10%" y="-10%" width="120%" height="120%"><feDropShadow dx="0" dy="1.5" stdDeviation="1.5" flood-opacity="0.08"/></filter>
                        @foreach($programDonut as $segment)
                        <linearGradient id="{{ $segment['gradient_id'] }}" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="{{ $segment['from'] }}"/><stop offset="100%" stop-color="{{ $segment['to'] }}"/></linearGradient>
                        @endforeach
                    </defs>
                    <circle cx="80" cy="80" r="54" fill="none" stroke="#f1f5f9" stroke-width="26" opacity=".6"/>
                    @foreach($programDonut as $segment)
                    <path d="{{ $segment['path'] }}" fill="url(#{{ $segment['gradient_id'] }})" stroke="#fff" stroke-width="2" stroke-linejoin="round" filter="url(#adminProgramSliceShadow)"/>
                    @endforeach
                    <circle cx="80" cy="80" r="39" fill="#fff" filter="url(#adminProgramSliceShadow)"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
                    <span class="text-[23px] font-black text-[#0d326b] leading-none">{{ $totalP }}</span>
                    <span class="text-[8px] font-extrabold uppercase tracking-wider text-slate-400 mt-1">Total</span>
                </div>
            </div>
            <div class="flex-1 min-w-0 space-y-3">
                @foreach($programDonut as $segment)
                <div class="flex items-center gap-2 min-w-0 text-[11px]">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $segment['color'] }}"></span>
                    <span class="font-semibold text-slate-600 flex-1 truncate" title="{{ $segment['label'] }}">{{ $segment['label'] }}</span>
                    <span class="font-black text-[#0d326b] shrink-0">{{ $segment['count'] }} <span class="font-semibold text-slate-400">({{ $segment['pct'] }}%)</span></span>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="py-8 text-center text-[13px] text-slate-400">No program data available.</div>
        @endif

        {{-- Program distribution insight data --}}
        @php
        $topProgram   = $programDistribution->sortByDesc('count')->first();
        $programCount = $programDistribution->count();
        $totalInProg  = $programDistribution->sum('count');
        $programInsight = match(true) {
            $totalInProg === 0
                => "No program data recorded yet. Insights will appear once students have programs assigned.",
            $programCount === 1
                => "All <strong>{$totalInProg} students</strong> are in the <strong>" . ($topProgram->program_type ?? 'N/A') . "</strong> program. Consider enrolling students across multiple programs for broader reach.",
            $topProgram && $totalInProg > 0
                => "<strong>" . ($topProgram->program_type ?? 'N/A') . "</strong> is the largest program group with <strong>{$topProgram->count} students</strong> (" . round(($topProgram->count / $totalInProg) * 100) . "% of total). Spread across <strong>{$programCount} programs</strong> — tailor lesson content to match each program's learning needs.",
        };
        @endphp
        <div class="pt-4 mt-4 border-t border-slate-100">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">FSL Mastery</div>
        @php
            $masteryPalette = [
                'Beginner' => ['color' => '#93c5fd', 'from' => '#bfdbfe', 'to' => '#60a5fa'],
                'Intermediate' => ['color' => '#3b82f6', 'from' => '#60a5fa', 'to' => '#2563eb'],
                'Advanced' => ['color' => '#0d326b', 'from' => '#1e4b8f', 'to' => '#071c3f'],
                'Completed' => ['color' => '#1a6fd4', 'from' => '#3b82f6', 'to' => '#1a6fd4'],
                'Unassigned' => ['color' => '#cbd5e1', 'from' => '#e2e8f0', 'to' => '#94a3b8'],
            ];
            $masteryOrder = ['Beginner', 'Intermediate', 'Advanced', 'Completed', 'Unassigned'];
            $masteryDistribution = $masteryDistribution->sortBy(function ($item) use ($masteryOrder) {
                $position = array_search($item->mastery_level, $masteryOrder, true);
                return $position === false ? 99 : $position;
            })->values();
            $totalMastery = $masteryDistribution->sum('count');
            $masteryDonut = [];
            $masteryActiveCount = $masteryDistribution->count();
            $masteryMaxCount = max(1, (int) $masteryDistribution->max('count'));
            $masteryCurrentAngle = -pi() / 2;
            $masteryGapAngle = $masteryActiveCount > 1 ? .08 : 0;
            foreach ($masteryDistribution as $masteryIndex => $item) {
                $fraction = $totalMastery > 0 ? $item->count / $totalMastery : 0;
                $angleSpan = $fraction * (2 * pi());
                $outerR = round(56 + (18 * ($item->count / $masteryMaxCount)), 1);
                if ($masteryActiveCount === 1) {
                    $start = $masteryCurrentAngle;
                    $end = $masteryCurrentAngle + (2 * pi()) - .001;
                } else {
                    $start = $masteryCurrentAngle + ($masteryGapAngle / 2);
                    $end = $masteryCurrentAngle + $angleSpan - ($masteryGapAngle / 2);
                    if ($end <= $start) {
                        $start = $masteryCurrentAngle;
                        $end = $masteryCurrentAngle + $angleSpan;
                    }
                }
                $x1 = round($donutCx + $outerR * cos($start), 2);
                $y1 = round($donutCy + $outerR * sin($start), 2);
                $x2 = round($donutCx + $outerR * cos($end), 2);
                $y2 = round($donutCy + $outerR * sin($end), 2);
                $x3 = round($donutCx + $donutInner * cos($end), 2);
                $y3 = round($donutCy + $donutInner * sin($end), 2);
                $x4 = round($donutCx + $donutInner * cos($start), 2);
                $y4 = round($donutCy + $donutInner * sin($start), 2);
                $largeArc = ($end - $start) > pi() ? 1 : 0;
                $palette = $masteryPalette[$item->mastery_level] ?? ['color' => '#64748b', 'from' => '#94a3b8', 'to' => '#475569'];
                $midAngle = ($start + $end) / 2;
                $masteryDonut[] = [
                    'label' => $item->mastery_level, 'count' => (int) $item->count,
                    'pct' => round($fraction * 100), 'color' => $palette['color'],
                    'from' => $palette['from'], 'to' => $palette['to'], 'gradient_id' => 'adminMasteryDonut' . $masteryIndex,
                    'outerR' => $outerR,
                    'path' => "M {$x1} {$y1} A {$outerR} {$outerR} 0 {$largeArc} 1 {$x2} {$y2} L {$x3} {$y3} A {$donutInner} {$donutInner} 0 {$largeArc} 0 {$x4} {$y4} Z",
                ];
                $masteryCurrentAngle += $angleSpan;
            }
            $topMastery = $masteryDistribution->sortByDesc('count')->first();
            $masteryInsight = $totalMastery === 0
                ? 'No student mastery data is available yet.'
                : '<strong>' . e($topMastery->mastery_level) . '</strong> is the largest group with <strong>' . $topMastery->count . ' of ' . $totalMastery . ' students (' . round(($topMastery->count / $totalMastery) * 100) . '%)</strong>.';
        @endphp
        @if($totalMastery > 0)
        <div class="flex items-center justify-center gap-5 py-2 w-full max-w-[500px] mx-auto">
            <div class="relative w-[124px] h-[124px] shrink-0">
                <svg class="w-full h-full overflow-visible" viewBox="0 0 160 160" role="img" aria-label="Student mastery donut chart">
                    <defs>
                        <filter id="adminMasterySliceShadow" x="-10%" y="-10%" width="120%" height="120%"><feDropShadow dx="0" dy="1.5" stdDeviation="1.5" flood-opacity="0.08"/></filter>
                        @foreach($masteryDonut as $segment)
                        <linearGradient id="{{ $segment['gradient_id'] }}" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="{{ $segment['from'] }}"/><stop offset="100%" stop-color="{{ $segment['to'] }}"/></linearGradient>
                        @endforeach
                    </defs>
                    <circle cx="80" cy="80" r="54" fill="none" stroke="#f1f5f9" stroke-width="26" opacity=".6"/>
                    @foreach($masteryDonut as $segment)
                    <path d="{{ $segment['path'] }}" fill="url(#{{ $segment['gradient_id'] }})" stroke="#fff" stroke-width="2" stroke-linejoin="round" filter="url(#adminMasterySliceShadow)"/>
                    @endforeach
                    <circle cx="80" cy="80" r="39" fill="#fff" filter="url(#adminMasterySliceShadow)"/>
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center text-center pointer-events-none">
                    <span class="text-[23px] font-black text-[#0d326b] leading-none">{{ $totalMastery }}</span>
                    <span class="text-[8px] font-extrabold uppercase tracking-wider text-slate-400 mt-1">Total</span>
                </div>
            </div>
            <div class="flex-1 min-w-0 space-y-3">
                @foreach($masteryDonut as $segment)
                <div class="flex items-center gap-2 min-w-0 text-[11px]">
                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $segment['color'] }}"></span>
                    <span class="font-semibold text-slate-600 flex-1 truncate">{{ $segment['label'] }}</span>
                    <span class="font-black text-[#0d326b] shrink-0">{{ $segment['count'] }} <span class="font-semibold text-slate-400">({{ $segment['pct'] }}%)</span></span>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="py-8 text-center text-[13px] text-slate-400">No student mastery data available.</div>
        @endif
        </div>
        @php $studentOverviewInsight = $programInsight . ' ' . $masteryInsight; @endphp
        <div class="senya-insight-gold mt-auto">
            <div class="senya-insight-gold-icon"><span class="material-symbols-outlined text-[19px]">lightbulb</span></div>
            <div>
                <div class="senya-insight-gold-title">Student Overview Insight</div>
                <div class="senya-insight-gold-text">{!! $studentOverviewInsight !!}</div>
            </div>
        </div>
    </div>

    {{-- Help Request Activity --}}
    <div class="a-panel order-5 lg:col-span-2">
        <div class="flex items-start justify-between mb-3">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Activity</p>
                <h3 class="text-[14px] font-bold text-[#0d326b]">Help Request Activity</h3>
                <p class="text-[11.5px] text-slate-400 mt-0.5">Submitted &amp; resolved — last 7 days</p>
            </div>
            <div class="flex items-center gap-3 text-[11px] font-semibold shrink-0">
                <span class="flex items-center gap-1.5">
                    <span class="w-6 h-1.5 rounded-full inline-block" style="background:#0d326b"></span>
                    <span class="text-slate-600">Submitted</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-6 h-1.5 rounded-full inline-block" style="background:#1a6fd4;opacity:.6"></span>
                    <span class="text-slate-600">Resolved</span>
                </span>
            </div>
        </div>
        <div class="bg-[#f8fafc] rounded-2xl w-full relative" style="padding-bottom:52%">
            <svg viewBox="0 0 {{ $RW }} {{ $RH }}" class="absolute inset-0 w-full h-full" overflow="visible">
                <defs>
                    <linearGradient id="rPFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#0d326b" stop-opacity=".14"/>
                        <stop offset="100%" stop-color="#0d326b" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="rRFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#1a6fd4" stop-opacity=".10"/>
                        <stop offset="100%" stop-color="#1a6fd4" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="rPLine" x1="0" y1="0" x2="100%" y2="0">
                        <stop offset="0%" stop-color="#1e4b8f"/>
                        <stop offset="100%" stop-color="#0d326b"/>
                    </linearGradient>
                    <linearGradient id="rRLine" x1="0" y1="0" x2="100%" y2="0">
                        <stop offset="0%" stop-color="#3b82f6"/>
                        <stop offset="100%" stop-color="#1a6fd4"/>
                    </linearGradient>
                </defs>
                @foreach([0,25,50,75,100] as $gv)
                    @php $gy2=round($RpT+$RpH-($gv/100)*$RpH,1); @endphp
                    <line x1="{{ $RpL }}" y1="{{ $gy2 }}" x2="{{ $RpL+$RpW }}" y2="{{ $gy2 }}"
                          stroke="#e8ecf2" stroke-width="1" stroke-dasharray="4,4"/>
                @endforeach
                <path d="{{ $rPArea }}" fill="url(#rPFill)"/>
                <path d="{{ $rRArea }}" fill="url(#rRFill)"/>
                <path d="{{ $rPLine }}" fill="none" stroke="url(#rPLine)" stroke-width="2" stroke-linecap="round"/>
                <path d="{{ $rRLine }}" fill="none" stroke="url(#rRLine)" stroke-width="2" stroke-linecap="round" stroke-dasharray="5,3"/>
                @foreach($rPPts as $i=>$p)
                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3.5" fill="#0d326b" stroke="white" stroke-width="1.5"/>
                @endforeach
                @foreach($rRPts as $i=>$p)
                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="3.5" fill="#1a6fd4" stroke="white" stroke-width="1.5"/>
                @endforeach
                @foreach($reportTrend as $i=>$d)
                    <text x="{{ $rPPts[$i]['x'] }}" y="{{ $RH-6 }}" font-size="9" fill="#94a3b8"
                          font-weight="600" text-anchor="middle">{{ $d['label'] }}</text>
                @endforeach
            </svg>
        </div>

        {{-- Help Request Insight --}}
        @php
        $totalPending  = collect($reportTrend)->sum('pending');
        $totalResolved = collect($reportTrend)->sum('resolved');
        $latestPending = collect($reportTrend)->last()['pending'] ?? 0;
        $resolutionRate = ($totalPending + $totalResolved) > 0
            ? round(($totalResolved / ($totalPending + $totalResolved)) * 100)
            : 0;
        $reportInsight = match(true) {
            $totalPending === 0 && $totalResolved === 0
                => "No help requests recorded in the last 7 days. This is a good sign — students are managing lessons without needing to escalate issues.",
            $resolutionRate >= 80
                => "<strong>Help requests are being resolved effectively</strong> — <strong>{$resolutionRate}% resolution rate</strong> over the past 7 days ({$totalResolved} resolved of " . ($totalPending + $totalResolved) . " submitted). Keep encouraging teachers to respond promptly.",
            $latestPending > 0 && $totalResolved === 0
                => "<strong>{$totalPending} help requests submitted</strong> in the past 7 days with <strong>none resolved yet.</strong> Remind teachers to review and respond to pending student concerns.",
            $totalPending > $totalResolved
                => "<strong>More requests are coming in than being resolved</strong> — {$totalPending} submitted vs {$totalResolved} resolved this week ({$resolutionRate}% rate). Consider following up with teachers on outstanding student reports.",
            default
                => "<strong>{$totalPending} help requests</strong> submitted and <strong>{$totalResolved} resolved</strong> in the last 7 days — a <strong>{$resolutionRate}% resolution rate</strong>. Monitor trends to keep response times low.",
        };
        @endphp
        <div class="senya-insight-gold mt-4">
            <div class="senya-insight-gold-icon">
                <span class="material-symbols-outlined text-[19px]">support_agent</span>
            </div>
            <div>
                <div class="senya-insight-gold-title">Help Request Insight</div>
                <div class="senya-insight-gold-text">{!! $reportInsight !!}</div>
            </div>
        </div>
    </div>

</div>

{{-- ══ 5. GESTURE BREAKDOWN (COMPACT CARD GRID) ═══════════════════════════ --}}
<div class="a-panel !p-0 overflow-hidden">

    {{-- Header --}}
    <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between flex-wrap gap-3">
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Sign Analytics</p>
            <h3 class="text-[15px] font-bold text-[#0d326b]">Gesture Performance Breakdown</h3>
            <p class="text-[12px] text-slate-400 mt-0.5">{{ $gestureBreakdown->count() }} signs tracked — sorted by accuracy (lowest first)</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap text-[11px] font-semibold">
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full" style="background:#e8edf7;color:#0d326b;">
                <span class="w-1.5 h-1.5 rounded-full inline-block" style="background:#0d326b;"></span>Critical &lt;40%
            </span>
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full" style="background:#dbeafe;color:#1a6fd4;">
                <span class="w-1.5 h-1.5 rounded-full inline-block" style="background:#1a6fd4;"></span>Warning 40–70%
            </span>
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full" style="background:#eff6ff;color:#60a5fa;">
                <span class="w-1.5 h-1.5 rounded-full inline-block" style="background:#93c5fd;"></span>Good ≥70%
            </span>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="px-6 py-3 bg-[#f8fafc] border-b border-slate-50 space-y-2.5">

        {{-- Row 1: search --}}
        <div class="relative">
            <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[15px]">search</span>
            <input id="gestureSearch" type="text" placeholder="Search sign name…"
                class="w-full pl-9 pr-3 py-2 rounded-full text-[12px] font-medium bg-white border border-slate-200
                       focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b]/40 outline-none text-slate-700"/>
        </div>

        {{-- Row 2: type tabs + status tabs + count --}}
        <div class="flex items-center gap-2 flex-wrap">

            {{-- Divider label --}}
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Type:</span>
            <div class="flex items-center gap-1">
                <button type="button" class="gsign-tab active" data-type="all"     onclick="adminFilterSignType('all',this)">
                    <span class="material-symbols-outlined text-[12px]">apps</span> All
                </button>
                <button type="button" class="gsign-tab"        data-type="dynamic" onclick="adminFilterSignType('dynamic',this)">
                    <span class="material-symbols-outlined text-[12px]">waving_hand</span> Moving
                </button>
                <button type="button" class="gsign-tab"        data-type="static"  onclick="adminFilterSignType('static',this)">
                    <span class="material-symbols-outlined text-[12px]">back_hand</span> Static
                </button>
            </div>

            <span class="w-px h-5 bg-slate-200 mx-1 shrink-0"></span>

            {{-- Status pill buttons --}}
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Status:</span>
            <div class="flex items-center gap-1">
                <button type="button" class="gstat-tab active" data-status="all" onclick="adminFilterStatus('all',this)">
                    All
                </button>
                <button type="button" class="gstat-tab" data-status="critical" onclick="adminFilterStatus('critical',this)">
                    <span class="w-1.5 h-1.5 rounded-full inline-block" style="background:#0d326b;"></span> Critical
                </button>
                <button type="button" class="gstat-tab" data-status="warning" onclick="adminFilterStatus('warning',this)">
                    <span class="w-1.5 h-1.5 rounded-full inline-block" style="background:#1a6fd4;"></span> Warning
                </button>
                <button type="button" class="gstat-tab" data-status="good" onclick="adminFilterStatus('good',this)">
                    <span class="w-1.5 h-1.5 rounded-full inline-block" style="background:#93c5fd;"></span> Good
                </button>
                <button type="button" class="gstat-tab" data-status="no_data" onclick="adminFilterStatus('no_data',this)">
                    No data
                </button>
            </div>

            <span class="ml-auto text-[11px] font-semibold text-slate-400 shrink-0" id="gestureCount"></span>
        </div>
    </div>

    {{-- Compact card grid (scrollable) --}}
    <div class="p-5 max-h-[520px] overflow-y-auto">
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-3" id="gestureGrid">
            @forelse($gestureBreakdown as $g)
            @php
                $isDynamic   = ($g['sign_type'] ?? 'static') === 'dynamic';
                $acc         = $g['accuracy'];
                $statusClass = match($g['status']) {
                    'critical' => 'is-critical',
                    'warning'  => 'is-warning',
                    'good'     => 'is-good',
                    default    => 'is-nodata',
                };
                $accColor = match($g['status']) {
                    'critical' => '#0d326b',
                    'warning'  => '#1a6fd4',
                    'good'     => '#93c5fd',
                    default    => '#94a3b8',
                };
                $accRingBg = match($g['status']) {
                    'critical' => 'background:linear-gradient(135deg,#e8edf7,#cdd8f0)',
                    'warning'  => 'background:linear-gradient(135deg,#dbeafe,#bfdbfe)',
                    'good'     => 'background:linear-gradient(135deg,#eff6ff,#e0f2fe)',
                    default    => 'background:#f8fafc',
                };
            @endphp
            <div class="gsign-card {{ $statusClass }}"
                 data-name="{{ strtolower($g['name']) }}"
                 data-status="{{ $g['status'] }}"
                 data-sign-type="{{ $g['sign_type'] ?? 'static' }}">

                {{-- Top row: name + type badge --}}
                <div class="flex items-center justify-between gap-2 mb-3">
                    <div class="min-w-0">
                        <p class="text-[13px] font-black text-[#0d326b] leading-snug truncate">{{ $g['name'] }}</p>
                        @if(!empty($g['module_name']))
                            <p class="text-[10px] text-slate-400 font-medium truncate">{{ $g['module_name'] }}</p>
                        @endif
                    </div>
                    @if($isDynamic)
                        <span class="inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[9px] font-bold bg-[#dbeafe] text-[#0d326b] shrink-0">
                            <span class="material-symbols-outlined text-[10px]">waving_hand</span> Moving
                        </span>
                    @endif
                </div>

                {{-- ACCURACY — the hero number --}}
                <div class="flex items-center gap-3 mb-3 p-2.5 rounded-xl" style="{{ $accRingBg }}">
                    <div class="shrink-0">
                        @php
                            $r2=18; $c2=round(2*M_PI*$r2,1);
                            $d2=$acc!==null?round($acc/100*$c2,1):0;
                        @endphp
                        <svg width="44" height="44" viewBox="0 0 44 44">
                            <circle cx="22" cy="22" r="{{ $r2 }}" fill="none"
                                    stroke="{{ $accColor }}22" stroke-width="5"/>
                            <circle cx="22" cy="22" r="{{ $r2 }}" fill="none"
                                    stroke="{{ $accColor }}" stroke-width="5"
                                    stroke-dasharray="{{ $d2 }} {{ $c2 }}"
                                    stroke-dashoffset="{{ round($c2/4,1) }}"
                                    stroke-linecap="round"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-[22px] font-black leading-none" style="color:{{ $accColor }}">
                            {{ $acc !== null ? $acc.'%' : '—' }}
                        </p>
                        <p class="text-[10px] font-semibold text-slate-400 mt-0.5">accuracy</p>
                    </div>
                    {{-- Mastered badge --}}
                    @if($g['status'] === 'good')
                        <span class="ml-auto inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[9.5px] font-bold shrink-0" style="background:#93c5fd;color:#1e3a5f;">
                            <span class="material-symbols-outlined text-[10px]">verified</span> Good
                        </span>
                    @elseif($g['status'] === 'critical')
                        <span class="ml-auto inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[9.5px] font-bold shrink-0" style="background:#0d326b;color:#fff;">
                            <span class="material-symbols-outlined text-[10px]">warning</span> Critical
                        </span>
                    @elseif($g['status'] === 'warning')
                        <span class="ml-auto inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[9.5px] font-bold shrink-0" style="background:#1a6fd4;color:#fff;">
                            <span class="material-symbols-outlined text-[10px]">priority_high</span> Warning
                        </span>
                    @endif
                </div>

                {{-- Progress bar --}}
                <div class="w-full bg-slate-100 rounded-full h-1.5 mb-2.5 overflow-hidden">
                    <div class="h-1.5 rounded-full" style="width:{{ $acc ?? 0 }}%; background:{{ $accColor }}"></div>
                </div>

                {{-- Stats row --}}
                <div class="grid grid-cols-3 gap-1 text-center text-[10px]">
                    <div>
                        <p class="font-black text-slate-700">{{ number_format($g['total_attempts']) }}</p>
                        <p class="text-slate-400 font-medium">attempts</p>
                    </div>
                    <div>
                        <p class="font-black text-emerald-600">{{ number_format($g['total_success']) }} ✓</p>
                        <p class="text-red-400 font-black">{{ number_format($g['total_wrong']) }} ✗</p>
                    </div>
                    <div>
                        <p class="font-black text-[#0d326b]">{{ $g['mastered_count'] }}/{{ $g['student_count'] }}</p>
                        <p class="text-slate-400 font-medium">mastered</p>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-3 text-center py-12 text-slate-400 text-[13px]">
                No gesture performance data recorded yet.
            </div>
            @endforelse
        </div>

        {{-- No results --}}
        <div id="gestureNoResults" class="hidden text-center py-10 text-slate-400 text-[13px] font-medium">
            No signs match your filters.
        </div>
    </div>

</div>

{{-- ── Gesture Coaching Insight ── --}}
@php
$criticalSigns = $gestureBreakdown->where('status', 'critical')->count();
$warningSigns  = $gestureBreakdown->where('status', 'warning')->count();
$goodSigns     = $gestureBreakdown->where('status', 'good')->count();
$noDataSigns   = $gestureBreakdown->where('status', 'no_data')->count();
$totalTracked  = $gestureBreakdown->count();
$worstSign     = $gestureBreakdown->where('status', 'critical')->sortBy('accuracy')->first();
$topSign       = $gestureBreakdown->where('status', 'good')->sortByDesc('accuracy')->first();

$gestureInsight = match(true) {
    $totalTracked === 0 || ($gestureStats->total_attempts ?? 0) === 0
        => "No gesture practice data recorded yet. Students haven't started the sign recognition exercises. Encourage teachers to assign gesture-based lessons.",
    $criticalSigns > 0 && $criticalSigns >= ($totalTracked * 0.4)
        => "<strong>{$criticalSigns} signs are at critical accuracy (below 40%)</strong> — that's " . round(($criticalSigns / $totalTracked) * 100) . "% of all tracked gestures." . ($worstSign ? " The most problematic sign is <strong>\"{$worstSign['name']}\"</strong> at " . ($worstSign['accuracy'] ?? '—') . "% accuracy." : '') . " Review the curriculum content for these signs.",
    $criticalSigns === 0 && $warningSigns === 0
        => "<strong>All tracked gestures are performing well (≥70% accuracy).</strong>" . ($topSign ? " Top performer: <strong>\"{$topSign['name']}\"</strong> at {$topSign['accuracy']}%." : '') . " Maintain difficulty levels and introduce new gesture modules to keep students challenged.",
    $goodSigns >= ($totalTracked * 0.6)
        => "<strong>{$goodSigns} of {$totalTracked} gestures are in the good range (≥70%).</strong> {$criticalSigns} critical and {$warningSigns} warning signs still need attention." . ($worstSign ? " Focus on <strong>\"{$worstSign['name']}\"</strong> which is the weakest at " . ($worstSign['accuracy'] ?? '—') . "%." : ''),
    default
        => "Platform gesture accuracy is <strong>{$gAcc}%</strong> across <strong>" . number_format($gestureStats->total_attempts ?? 0) . " attempts</strong>. {$criticalSigns} critical, {$warningSigns} warning, {$goodSigns} good signs tracked. Prioritize reinforcing critical gestures in the next lesson cycle.",
};
@endphp
<div class="senya-insight-gold">
    <div class="senya-insight-gold-icon">
        <span class="material-symbols-outlined text-[20px]">support_agent</span>
    </div>
    <div>
        <div class="senya-insight-gold-title">Gesture Coaching Insight</div>
        <div class="senya-insight-gold-text">{!! $gestureInsight !!}</div>
    </div>
</div>

</div>{{-- end skeleton-hide --}}

<script>
(function () {
    const searchEl  = document.getElementById('gestureSearch');
    const countEl   = document.getElementById('gestureCount');
    const noResults = document.getElementById('gestureNoResults');
    const cards     = Array.from(document.querySelectorAll('#gestureGrid .gsign-card'));
    const total     = cards.length;

    if (!cards.length) return;

    let signType   = 'all';
    let statusType = 'all';

    function applyFilter() {
        var q     = searchEl ? searchEl.value.toLowerCase().trim() : '';
        var shown = 0;

        cards.forEach(function (card) {
            var nameOk   = !q          || (card.dataset.name     || '').indexOf(q) !== -1;
            var statusOk = statusType === 'all' || card.dataset.status   === statusType;
            var typeOk   = signType   === 'all' || card.dataset.signType === signType;
            var visible  = nameOk && statusOk && typeOk;
            card.style.display = visible ? '' : 'none';
            if (visible) shown++;
        });

        if (countEl)   countEl.textContent     = shown + ' of ' + total + ' signs';
        if (noResults) noResults.style.display = shown === 0 ? 'block' : 'none';
    }

    if (searchEl) {
        searchEl.addEventListener('input', applyFilter);
    }

    window.adminFilterSignType = function (type, btn) {
        signType = type;
        document.querySelectorAll('.gsign-tab').forEach(function (t) { t.classList.remove('active'); });
        if (btn) btn.classList.add('active');
        applyFilter();
    };

    window.adminFilterStatus = function (status, btn) {
        statusType = status;
        document.querySelectorAll('.gstat-tab').forEach(function (t) { t.classList.remove('active'); });
        if (btn) btn.classList.add('active');
        applyFilter();
    };

    applyFilter();
})();
</script>

@endsection
