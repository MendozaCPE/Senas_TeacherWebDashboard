@extends('layouts.grade-leader')
@section('bg-class', 'bg-[#f8fafc]')
@section('title', 'School Analytics')
@section('content')

{{-- ── SKELETON ─────────────────────────────────────────────────────────── --}}
<div id="page-skeleton" class="space-y-6 pb-12" aria-hidden="true">
    <div class="bg-white rounded-[22px] border border-slate-100 shadow-sm p-4 flex gap-3 flex-wrap">
        @for($i=0;$i<3;$i++)<div class="skeleton h-9 rounded-[14px] w-36"></div>@endfor
        <div class="skeleton h-9 rounded-[14px] w-24"></div>
        <div class="ml-auto skeleton h-9 rounded-[14px] w-36"></div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @for($i=0;$i<4;$i++)
        <div class="bg-white rounded-[24px] p-6 border border-slate-100 shadow-sm flex flex-col gap-3">
            <div class="flex justify-between"><div class="skeleton h-3 rounded w-32"></div><div class="skeleton w-10 h-10 rounded-xl"></div></div>
            <div class="skeleton h-9 rounded w-20"></div>
            <div class="skeleton h-3 rounded w-36"></div>
        </div>
        @endfor
    </div>
    <div class="skeleton rounded-[18px] h-16 w-full"></div>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7 skeleton rounded-[26px] h-72"></div>
        <div class="lg:col-span-5 skeleton rounded-[26px] h-72"></div>
    </div>
    <div class="skeleton rounded-[26px] h-64 w-full"></div>
</div>
<script>document.addEventListener('DOMContentLoaded',function(){var s=document.getElementById('page-skeleton');if(s)s.style.display='none';});</script>

<style>
:root {
    --navy-950:#071c3f; --navy-900:#0d326b; --navy-700:#1e4b8f;
    --navy-500:#1a6fd4; --navy-400:#3b82f6; --navy-200:#bfdbfe;
    --navy-100:#dbeafe; --navy-50:#eff6ff;
    --gold-600:#b45309; --gold-500:#d97706; --gold-400:#f59e0b;
    --gold-100:#fef3c7; --gold-50:#fffbeb;
}
.analytics-panel {
    background:#fff; border-radius:26px; border:1px solid #edf2f7;
    box-shadow:0 4px 20px rgba(13,50,107,.03); padding:24px;
    transition:box-shadow .25s ease;
}
.analytics-panel:hover { box-shadow:0 10px 30px rgba(13,50,107,.06); }
.stat-kpi-card {
    border-radius:24px; padding:22px 24px; position:relative;
    overflow:hidden; transition:transform .2s ease,box-shadow .2s ease; border:1px solid #f1f5f9;
}
.stat-kpi-card:hover { transform:translateY(-2px); box-shadow:0 10px 26px rgba(13,50,107,.08); }
.filter-container {
    background:#fff; border-radius:22px; border:1px solid #edf2f7;
    box-shadow:0 2px 10px rgba(13,50,107,.03); padding:14px 20px;
    display:flex; align-items:center; justify-content:space-between; gap:14px; flex-wrap:wrap;
}
.filter-group { display:flex; align-items:center; gap:10px; flex-wrap:wrap; }
.filter-wrap { position:relative; display:inline-flex; align-items:center; }
.filter-select {
    appearance:none; background:#f8fafc; border:1px solid #e2e8f0;
    border-radius:14px; padding:8px 34px 8px 14px; font-size:13px;
    font-weight:600; color:#0d326b; cursor:pointer; outline:none; transition:border-color .15s;
}
.filter-select:hover { background:#f1f5f9; border-color:#cbd5e1; }
.filter-wrap .material-symbols-outlined { position:absolute; right:10px; pointer-events:none; font-size:18px; color:#0d326b; }
.filter-btn {
    display:inline-flex; align-items:center; gap:6px; background:#0d326b; color:#fff;
    font-size:13px; font-weight:700; padding:8px 18px; border-radius:14px; border:none; cursor:pointer; transition:background .2s;
}
.filter-btn:hover { background:#1a6fd4; }
.filter-reset {
    font-size:13px; font-weight:600; color:#64748b; padding:8px 14px; border-radius:14px;
    background:#f8fafc; border:1px solid #e2e8f0; text-decoration:none; transition:background .15s,color .15s;
}
.filter-reset:hover { background:#f1f5f9; color:#0d326b; }
/* Senya Gold Insight */
.senya-insight-gold {
    background:linear-gradient(135deg,#fffdf8 0%,#fefce8 100%); border:1.5px solid #fbbf24;
    border-radius:20px; padding:16px 20px; display:flex; align-items:flex-start; gap:14px;
    box-shadow:0 4px 16px rgba(245,158,11,.08);
}
.senya-insight-gold-icon {
    width:38px; height:38px; border-radius:12px;
    background:linear-gradient(135deg,#f59e0b 0%,#facc15 50%,#fbbf24 100%); color:#78350f;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
    box-shadow:0 2px 8px rgba(245,158,11,.25);
}
.senya-insight-gold-title { font-size:11px; font-weight:800; text-transform:uppercase; letter-spacing:.06em; color:#b45309; margin-bottom:2px; }
.senya-insight-gold-text  { font-size:13px; font-weight:500; color:#78350f; line-height:1.55; }
/* Rank badges */
.rank-badge-podium { width:30px; height:30px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:800; flex-shrink:0; }
.rank-badge-gold   { background:linear-gradient(135deg,#f59e0b,#facc15,#fbbf24); color:#78350f; border:1px solid #f59e0b; box-shadow:0 2px 6px rgba(245,158,11,.25); }
.rank-badge-silver { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
.rank-badge-bronze { background:#ffedd5; color:#c2410c; border:1px solid #fed7aa; }
.rank-badge-default{ background:#f8fafc; color:#64748b; border:1px solid #e2e8f0; }
/* Chart tooltip */
.chart-tooltip {
    position:fixed; pointer-events:none; background:#071c3f; color:#fff;
    padding:7px 13px; border-radius:10px; font-size:11.5px; font-weight:700;
    box-shadow:0 4px 14px rgba(0,0,0,.25); white-space:nowrap;
    opacity:0; transition:opacity .12s; z-index:9999; transform:translate(-50%,-110%);
}
.chart-tooltip.visible { opacity:1; }
</style>

@php
/* ══ Senya Insight ══════════════════════════════════════════════════════ */
$insights = [];
if ($avgQuizScore >= 80)      $insights[] = "School quiz average is at <strong>{$avgQuizScore}%</strong> — students are demonstrating strong content retention across all classes.";
elseif ($avgQuizScore >= 50)  $insights[] = "School quiz average sits at <strong>{$avgQuizScore}%</strong>. Targeted review sessions could help push the average closer to the 75% benchmark.";
else                           $insights[] = "School quiz average of <strong>{$avgQuizScore}%</strong> is below the 75% target. Recommend reviewing assessment difficulty and preparation strategies.";

if ($quizPassRate >= 75)      $insights[] = "<strong>{$quizPassRate}%</strong> of quiz attempts passed the 75% threshold — a solid pass rate school-wide.";
elseif ($quizPassRate >= 50)  $insights[] = "Quiz pass rate is at <strong>{$quizPassRate}%</strong>. Over half of students are clearing the passing mark — keep the momentum going.";
else                           $insights[] = "Only <strong>{$quizPassRate}%</strong> of quizzes passed. Consider coordinating with teachers to identify struggling students early.";

if ($activeStudentsCount > 0)
    $insights[] = "<strong>{$activeStudentsCount}</strong> students were actively learning during this period — great engagement across the school.";

$topClass = $classPerformance->first();
if ($topClass && $topClass['avg_quiz_score'] > 0)
    $insights[] = "Top performing class this period: <strong>{$topClass['name']}</strong> with a <strong>{$topClass['avg_quiz_score']}%</strong> quiz average — a model to share best practices from.";

$aiTotal = $activeInactivePie['total'];
if ($aiTotal > 0) {
    $aiPct = round($activeInactivePie['active'] / $aiTotal * 100);
    if ($aiPct >= 70)     $insights[] = "<strong>{$aiPct}%</strong> of students are active in SY {$currentDepEdSY} — excellent participation rate for this school year.";
    elseif ($aiPct >= 40) $insights[] = "<strong>{$aiPct}%</strong> of students are active in SY {$currentDepEdSY}. There is room to re-engage the inactive learners.";
    else                   $insights[] = "Only <strong>{$aiPct}%</strong> of students are currently active in SY {$currentDepEdSY}. A targeted re-engagement effort is recommended.";
}
$insight = $insights[array_rand($insights)];

/* ══ Active/Inactive Pie ════════════════════════════════════════════════ */
$pieCx=110; $pieCy=110; $pieR=86; $pieIn=52;
$pieTotal  = max(1, $activeInactivePie['total']);
$pieActive = $activeInactivePie['active'];
$pieInact  = $activeInactivePie['inactive'];
// Segments: active (navy), inactive (slate)
$pieSegs = [];
$pieData = [
    ['val'=>$pieActive, 'color'=>'#0d326b', 'label'=>'Active'],
    ['val'=>$pieInact,  'color'=>'#e2e8f0', 'label'=>'Inactive'],
];
$pieAngle = -M_PI / 2;
foreach ($pieData as $pd) {
    $sw = $pieTotal > 0 ? ($pd['val'] / $pieTotal) * 2 * M_PI : 0;
    if ($sw < 0.01) { $pieAngle += $sw; continue; }
    $x1 = round($pieCx + $pieR  * cos($pieAngle),   2);
    $y1 = round($pieCy + $pieR  * sin($pieAngle),   2);
    $x2 = round($pieCx + $pieR  * cos($pieAngle+$sw),2);
    $y2 = round($pieCy + $pieR  * sin($pieAngle+$sw),2);
    $i1 = round($pieCx + $pieIn * cos($pieAngle),   2);
    $j1 = round($pieCy + $pieIn * sin($pieAngle),   2);
    $i2 = round($pieCx + $pieIn * cos($pieAngle+$sw),2);
    $j2 = round($pieCy + $pieIn * sin($pieAngle+$sw),2);
    $lg = ($sw > M_PI) ? 1 : 0;
    $pct = round($pd['val'] / $pieTotal * 100, 1);
    // Mid-angle for label placement
    $mid = $pieAngle + $sw / 2;
    $lx  = round($pieCx + ($pieR * 0.72) * cos($mid), 1);
    $ly  = round($pieCy + ($pieR * 0.72) * sin($mid), 1);
    $pieSegs[] = [
        'path'  => "M {$x1},{$y1} A {$pieR},{$pieR} 0 {$lg},1 {$x2},{$y2} L {$i2},{$j2} A {$pieIn},{$pieIn} 0 {$lg},0 {$i1},{$j1} Z",
        'color' => $pd['color'],
        'label' => $pd['label'],
        'val'   => $pd['val'],
        'pct'   => $pct,
        'lx'    => $lx,
        'ly'    => $ly,
        'show_label' => $pct >= 8,
    ];
    $pieAngle += $sw;
}
@endphp

{{-- ════════════════════════════════════════════════════════════════════ --}}
{{--  REAL CONTENT                                                        --}}
{{-- ════════════════════════════════════════════════════════════════════ --}}
<div class="space-y-6 pb-12">

    {{-- ── 1. FILTER TOOLBAR ──────────────────────────────────────────── --}}
    <form method="POST" action="{{ route('grade-leader.analytics.filter') }}" id="filterForm">
        @csrf
        <div class="filter-container">
            <div class="filter-group">
                <div class="flex items-center gap-2 mr-2">
                    <span class="material-symbols-outlined text-[#0d326b] text-[22px]">tune</span>
                    <span class="text-[13px] font-bold text-[#0d326b] uppercase tracking-wider">Filter Period</span>
                </div>

                <div class="filter-wrap">
                    <select name="period" class="filter-select" id="periodSelect" onchange="toggleMonthWrap()">
                        @foreach(['weekly'=>'Weekly','monthly'=>'Monthly','quarterly'=>'Quarterly','yearly'=>'Yearly'] as $val=>$lbl)
                        <option value="{{ $val }}" {{ $period===$val?'selected':'' }}>{{ $lbl }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined">expand_more</span>
                </div>

                <div class="filter-wrap">
                    <select name="year" class="filter-select">
                        @foreach(range(date('Y'), date('Y')-4) as $y)
                        <option value="{{ $y }}" {{ $year==$y?'selected':'' }}>{{ $y }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined">expand_more</span>
                </div>

                <div class="filter-wrap {{ in_array($period,['monthly','quarterly'])?'':'hidden' }}" id="monthFilterWrap">
                    <select name="month" class="filter-select">
                        @foreach(range(1,12) as $m)
                        <option value="{{ $m }}" {{ $month==$m?'selected':'' }}>{{ \Carbon\Carbon::create(null,$m)->format('F') }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined">expand_more</span>
                </div>

                <a href="{{ route('grade-leader.analytics') }}" class="filter-reset">Reset</a>
                <button type="submit" class="filter-btn">
                    <span class="material-symbols-outlined text-[16px]">refresh</span> Apply
                </button>
            </div>
            <div class="flex-shrink-0">
                <p class="text-[12px] text-slate-400 font-medium">
                    <span class="font-bold text-[#0d326b]">{{ $school->name ?? 'Your School' }}</span>
                    &nbsp;·&nbsp; {{ ucfirst($period) }} view
                </p>
            </div>
        </div>
    </form>

    {{-- ── 2. KPI CARDS ────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        {{-- Avg Quiz Score --}}
        <div class="stat-kpi-card text-white" style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%)">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-white/70">Avg Quiz Score</span>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px] text-white">insights</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-white tracking-tight">{{ $avgQuizScore }}%</p>
            <p class="text-[12px] text-white/70 font-medium">{{ ucfirst($period) }} school average</p>
        </div>

        {{-- Quiz Pass Rate --}}
        <div class="stat-kpi-card bg-white">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Quiz Pass Rate</span>
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0d326b] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-[#0d326b] tracking-tight">{{ $quizPassRate }}%</p>
            <p class="text-[12px] text-[#1a6fd4] font-medium">scored ≥ 75%</p>
        </div>

        {{-- Active Students (period) --}}
        <div class="stat-kpi-card bg-white" style="border-color:#d1fae5;background:#f0fdf4;">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Active Students</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">bolt</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-emerald-700 tracking-tight">{{ number_format($activeStudentsCount) }}</p>
            <p class="text-[12px] text-emerald-600 font-medium">in {{ ucfirst($period) }} period</p>
        </div>

        {{-- Total Classes --}}
        <div class="stat-kpi-card text-amber-950" style="background:linear-gradient(135deg,#f59e0b 0%,#facc15 50%,#fbbf24 100%);border-color:rgba(245,158,11,.5);box-shadow:0 4px 16px rgba(245,158,11,.22)">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-950/80">Total Classes</span>
                <div class="w-10 h-10 rounded-xl bg-white/35 text-amber-950 flex items-center justify-center backdrop-blur-sm shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">class</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-amber-950 tracking-tight">{{ $classPerformance->count() }}</p>
            <p class="text-[12px] text-amber-950/80 font-bold">classrooms this school</p>
        </div>

    </div>

    {{-- ── 3. SENYA INSIGHT ────────────────────────────────────────────── --}}
    <div class="senya-insight-gold">
        <div class="senya-insight-gold-icon">
            <span class="material-symbols-outlined text-[20px]">lightbulb</span>
        </div>
        <div>
            <div class="senya-insight-gold-title">Senya School Insight</div>
            <div class="senya-insight-gold-text">{!! $insight !!}</div>
        </div>
    </div>

    {{-- ── 4. ENROLLMENT BAR CHART + ACTIVE/INACTIVE PIE ──────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        {{-- Enrollment bar chart (left, wider) --}}
        <div class="analytics-panel lg:col-span-7">
            <div class="flex items-start justify-between pb-4 border-b border-slate-100 mb-5">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Enrollment</p>
                    <h3 class="text-[17px] font-black text-[#0d326b]">Students per School Year</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">Total students by DepEd school year (July – June) · broken down by program type</p>
                </div>
                <span class="material-symbols-outlined text-slate-300 text-[24px]">bar_chart</span>
            </div>

            @php
            /*
             * Program type palette — 4 shades of blue, light → dark
             * Order matches $programTypes: Regular, Inclusion, Transition, Self-contained
             */
            $ptColors  = [
                'Regular'        => ['bar'=>'#bfdbfe', 'text'=>'#1e40af', 'label'=>'#1e40af'],
                'Inclusion'      => ['bar'=>'#3b82f6', 'text'=>'#fff',    'label'=>'#2563eb'],
                'Transition'     => ['bar'=>'#1d4ed8', 'text'=>'#fff',    'label'=>'#1d4ed8'],
                'Self-contained' => ['bar'=>'#0d326b', 'text'=>'#fff',    'label'=>'#0d326b'],
            ];

            $syCount = count($allDepEdSYs);
            // Find the global max for Y-axis (max total per SY)
            $syMax = max(1, collect($enrollmentBySY)->max('total'));

            // SVG dimensions
            $EW=600; $EH=240; $EPL=36; $EPR=16; $EPT=18; $EPB=52;
            $ePW = $EW - $EPL - $EPR;
            $ePH = $EH - $EPT - $EPB;

            // Slot width per SY — single stacked bar per slot
            $eSlot = $syCount > 0 ? $ePW / $syCount : $ePW;
            // Bar width: 50% of slot, clamped
            $barW  = max(20, min(52, round($eSlot * 0.50)));
            @endphp

            @if($syCount > 0)
            <div id="enrollWrap" style="position:relative;">
                <div id="enrollTip" class="chart-tooltip"></div>
                <svg id="enrollChart"
                     viewBox="0 0 {{ $EW }} {{ $EH }}"
                     class="w-full" style="max-height:{{ $EH }}px;"
                     preserveAspectRatio="xMidYMid meet" overflow="visible">

                    {{-- Y-axis grid + labels --}}
                    @foreach([0,25,50,75,100] as $gv)
                        @php
                            $gy     = round($EPT + $ePH - ($gv/100)*$ePH, 1);
                            $gLabel = round($syMax * $gv / 100);
                        @endphp
                        <line x1="{{ $EPL }}" y1="{{ $gy }}" x2="{{ $EPL+$ePW }}" y2="{{ $gy }}"
                              stroke="#f1f5f9" stroke-width="1" stroke-dasharray="3,4"/>
                        <text x="{{ $EPL-6 }}" y="{{ $gy+4 }}"
                              font-size="9" fill="#94a3b8" font-weight="600" text-anchor="end">{{ $gLabel }}</text>
                    @endforeach

                    {{-- Axis baseline --}}
                    <line x1="{{ $EPL }}" y1="{{ $EPT+$ePH }}" x2="{{ $EPL+$ePW }}" y2="{{ $EPT+$ePH }}"
                          stroke="#e2e8f0" stroke-width="1.5"/>

                    {{-- Stacked bars — one bar per SY, segments per program type --}}
                    @foreach($allDepEdSYs as $syi => $sy)
                        @php
                            $data    = $enrollmentBySY[$sy] ?? ['total'=>0,'programs'=>[]];
                            $total   = $data['total'];
                            $isCurr  = ($sy === $currentDepEdSY);
                            $cx      = round($EPL + ($syi + 0.5) * $eSlot, 1);
                            $bx      = round($cx - $barW/2, 1);
                            $parts   = explode('-', $sy);
                            $shortSY = substr($parts[0]??'', -2).'-'.substr($parts[1]??'', -2);
                        @endphp

                        {{-- Current SY highlight band --}}
                        @if($isCurr)
                        <rect x="{{ round($cx - $eSlot/2 + 3, 1) }}" y="{{ $EPT }}"
                              width="{{ round($eSlot - 6, 1) }}" height="{{ $ePH }}"
                              rx="8" fill="#eff6ff" opacity="0.7"/>
                        @endif

                        @if($total > 0)
                        {{-- Build stacked segments bottom-up (first program = bottom) --}}
                        @php $segOffsetH = 0; @endphp
                        @foreach($programTypes as $pti => $pt)
                            @php
                                $cnt  = $data['programs'][$pt] ?? 0;
                                if ($cnt <= 0) continue;
                                $segH = max(3, round(($cnt / $syMax) * $ePH, 1));
                                $segY = round($EPT + $ePH - $segOffsetH - $segH, 1);
                                $segOffsetH += $segH;
                            @endphp
                            <rect x="{{ $bx }}" y="{{ $segY }}" width="{{ $barW }}" height="{{ $segH }}"
                                  fill="{{ $ptColors[$pt]['bar'] }}"
                                  class="enroll-bar" style="cursor:pointer;transition:opacity .15s;"
                                  data-sy="{{ $sy }}" data-pt="{{ $pt }}" data-val="{{ $cnt }}" data-total="{{ $total }}"
                                  onmouseover="showEnrollTip(event,this)" onmouseout="hideEnrollTip()"/>
                        @endforeach
                        {{-- Rounded cap — a thin pill on very top of the stack --}}
                        @php $topY = round($EPT + $ePH - $segOffsetH, 1); @endphp
                        <rect x="{{ $bx }}" y="{{ $topY }}" width="{{ $barW }}" height="{{ min(7, $segOffsetH) }}"
                              rx="4" ry="4"
                              fill="{{ $ptColors[collect($programTypes)->last(fn($p) => ($data['programs'][$p] ?? 0) > 0) ?? end($programTypes)]['bar'] }}"
                              style="pointer-events:none;"/>
                        {{-- Total label above bar --}}
                        <text x="{{ $cx }}" y="{{ max($EPT+11, $topY - 5) }}"
                              font-size="10" fill="{{ $isCurr?'#d97706':'#334155' }}"
                              font-weight="900" text-anchor="middle">{{ $total }}</text>
                        @else
                        {{-- Empty placeholder --}}
                        <rect x="{{ $bx }}" y="{{ $EPT+$ePH-3 }}" width="{{ $barW }}" height="3"
                              rx="3" fill="#f1f5f9" style="pointer-events:none;"/>
                        @endif

                        {{-- X-axis SY label --}}
                        <text x="{{ $cx }}" y="{{ $EH - 28 }}"
                              font-size="{{ $syCount > 6 ? '8' : '9.5' }}"
                              fill="{{ $isCurr?'#d97706':'#94a3b8' }}"
                              font-weight="{{ $isCurr?'800':'600' }}"
                              text-anchor="middle">{{ $shortSY }}</text>

                        {{-- NOW badge --}}
                        @if($isCurr)
                        <rect x="{{ round($cx - 14, 1) }}" y="{{ $EH-22 }}" width="28" height="13"
                              rx="4" fill="#fef3c7"/>
                        <text x="{{ $cx }}" y="{{ $EH-12 }}" font-size="7.5" fill="#b45309"
                              font-weight="800" text-anchor="middle">NOW</text>
                        @endif
                    @endforeach

                </svg>
            </div>

            {{-- Program type colour legend --}}
            <div class="flex flex-wrap items-center gap-x-5 gap-y-2 mt-4 pt-4 border-t border-slate-100">
                @foreach($programTypes as $pt)
                <div class="flex items-center gap-2">
                    <span style="width:12px;height:12px;border-radius:3px;background:{{ $ptColors[$pt]['bar'] }};display:inline-block;border:1px solid rgba(0,0,0,.07);flex-shrink:0;"></span>
                    <span class="text-[11px] font-semibold text-slate-500">{{ $pt }}</span>
                </div>
                @endforeach
            </div>

            @else
            <div style="padding:48px 0;text-align:center;">
                <span class="material-symbols-outlined" style="font-size:52px;color:#e2e8f0;">bar_chart</span>
                <p style="font-size:13px;color:#94a3b8;margin-top:8px;font-weight:600;">No enrollment data available.</p>
            </div>
            @endif
        </div>

        {{-- Active vs Inactive Pie (right, narrower) --}}
        <div class="analytics-panel lg:col-span-5 flex flex-col">
            <div class="flex items-start justify-between pb-4 border-b border-slate-100 mb-5">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Engagement</p>
                    <h3 class="text-[17px] font-black text-[#0d326b]">Active vs Inactive</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">SY {{ $currentDepEdSY }} · by student status</p>
                </div>
                <span class="material-symbols-outlined text-slate-300 text-[24px]">donut_large</span>
            </div>

            @if($activeInactivePie['total'] > 0)
            <div class="flex flex-col items-center flex-1 justify-center gap-4">
                {{-- Donut --}}
                <div style="position:relative;width:220px;height:220px;flex-shrink:0;">
                    <svg viewBox="0 0 220 220" width="220" height="220" id="pieSvg">
                        <defs>
                            <filter id="pieShadow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="2" stdDeviation="4" flood-color="#0d326b" flood-opacity="0.12"/>
                            </filter>
                        </defs>
                        @foreach($pieSegs as $seg)
                        <path d="{{ $seg['path'] }}"
                              fill="{{ $seg['color'] }}"
                              filter="url(#pieShadow)"
                              class="pie-seg" style="cursor:default;transition:opacity .15s;"
                              data-label="{{ $seg['label'] }}" data-val="{{ $seg['val'] }}" data-pct="{{ $seg['pct'] }}"
                              onmouseover="this.style.opacity='.82'" onmouseout="this.style.opacity='1'"/>
                        {{-- Pct label inside segment --}}
                        @if($seg['show_label'])
                        <text x="{{ $seg['lx'] }}" y="{{ $seg['ly']+4 }}"
                              font-size="11" fill="{{ $seg['color']==='#0d326b'?'#fff':'#475569' }}"
                              font-weight="800" text-anchor="middle">{{ $seg['pct'] }}%</text>
                        @endif
                        @endforeach
                        {{-- Centre label --}}
                        <text x="110" y="104" font-size="26" fill="#0d326b" font-weight="900" text-anchor="middle">
                            {{ $activeInactivePie['total'] }}
                        </text>
                        <text x="110" y="122" font-size="10" fill="#94a3b8" font-weight="700" text-anchor="middle">STUDENTS</text>
                    </svg>
                </div>

                {{-- Legend --}}
                <div class="flex flex-col gap-3 w-full max-w-[220px]">
                    @php $actPct = $activeInactivePie['total']>0 ? round($activeInactivePie['active']/$activeInactivePie['total']*100,1) : 0; @endphp
                    @php $inPct  = $activeInactivePie['total']>0 ? round($activeInactivePie['inactive']/$activeInactivePie['total']*100,1) : 0; @endphp

                    <div class="flex items-center gap-3">
                        <span style="width:12px;height:12px;border-radius:3px;background:#0d326b;display:inline-block;flex-shrink:0;"></span>
                        <div class="flex-1">
                            <div class="flex justify-between items-baseline">
                                <span class="text-[12px] font-700 text-slate-700">Active</span>
                                <span class="text-[13px] font-900 text-[#0d326b]">{{ $activeInactivePie['active'] }}</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full mt-1 overflow-hidden">
                                <div class="h-full bg-[#0d326b] rounded-full" style="width:{{ $actPct }}%"></div>
                            </div>
                        </div>
                        <span class="text-[11px] font-700 text-slate-400 w-10 text-right">{{ $actPct }}%</span>
                    </div>

                    <div class="flex items-center gap-3">
                        <span style="width:12px;height:12px;border-radius:3px;background:#e2e8f0;border:1px solid #cbd5e1;display:inline-block;flex-shrink:0;"></span>
                        <div class="flex-1">
                            <div class="flex justify-between items-baseline">
                                <span class="text-[12px] font-700 text-slate-700">Inactive</span>
                                <span class="text-[13px] font-900 text-slate-500">{{ $activeInactivePie['inactive'] }}</span>
                            </div>
                            <div class="h-1.5 bg-slate-100 rounded-full mt-1 overflow-hidden">
                                <div class="h-full bg-slate-300 rounded-full" style="width:{{ $inPct }}%"></div>
                            </div>
                        </div>
                        <span class="text-[11px] font-700 text-slate-400 w-10 text-right">{{ $inPct }}%</span>
                    </div>
                </div>
            </div>
            @else
            <div class="flex-1 flex flex-col items-center justify-center py-12">
                <span class="material-symbols-outlined text-slate-200 text-[52px]">donut_large</span>
                <p class="text-[13px] text-slate-400 mt-3 font-600">No student data for SY {{ $currentDepEdSY }}.</p>
            </div>
            @endif
        </div>

    </div>{{-- /grid --}}

    {{-- ── 5. CLASS PERFORMANCE SCORECARD ─────────────────────────────── --}}
    <div class="analytics-panel">
        <div class="flex items-start justify-between pb-4 border-b border-slate-100 mb-5">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Per Classroom</p>
                <h3 class="text-[17px] font-black text-[#0d326b]">Class Performance Scorecard</h3>
                <p class="text-[12px] text-slate-400 mt-0.5">
                    Ranked by avg score · Active students · Quiz pass rate · Checkpoint exam pass rate — {{ ucfirst($period) }} period
                </p>
            </div>
            <span class="material-symbols-outlined text-slate-300 text-[24px]">grading</span>
        </div>

        @if($classPerformance->isNotEmpty())
        <div class="overflow-x-auto">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr>
                        @foreach(['Rank','Teacher','Students','Active','Quiz Pass Rate','Checkpoint Pass Rate','Avg Score','Status'] as $th)
                        <th style="padding:10px 14px;text-align:{{ in_array($th,['Rank','Students','Avg Score','Status'])?'center':'left' }};font-size:10.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.07em;border-bottom:1px solid #f1f5f9;white-space:nowrap;background:#fafcff;">{{ $th }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($classPerformance as $i => $cls)
                @php
                    $badgeClass = $i===0?'rank-badge-gold':($i===1?'rank-badge-silver':($i===2?'rank-badge-bronze':'rank-badge-default'));
                    $sc      = $cls['avg_quiz_score'] >= 75 ? '#16a34a' : ($cls['avg_quiz_score'] >= 50 ? '#d97706' : '#ef4444');
                    $qColor  = $cls['quiz_pass_rate'] >= 75 ? '#16a34a' : ($cls['quiz_pass_rate'] >= 50 ? '#d97706' : '#ef4444');
                    $ckColor = $cls['ck_pass_rate']   >= 75 ? '#16a34a' : ($cls['ck_pass_rate']   >= 50 ? '#d97706' : '#ef4444');
                    $actColor= $cls['active_pct']     >= 70 ? '#16a34a' : ($cls['active_pct']     >= 40 ? '#d97706' : '#ef4444');
                    $sl = $cls['status']==='on_track'
                        ? ['background:#ecfdf5','color:#15803d','On Track']
                        : ($cls['status']==='needs_attention'
                            ? ['background:#fffbeb','color:#b45309','Needs Attention']
                            : ['background:#fef2f2','color:#b91c1c','Needs Support']);
                @endphp
                <tr style="border-bottom:1px solid #f8fafc;transition:background .15s;"
                    onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">

                    {{-- Rank --}}
                    <td style="padding:13px 14px;text-align:center;width:60px;">
                        <div class="rank-badge-podium {{ $badgeClass }}" style="margin:0 auto;width:28px;height:28px;font-size:11px;border-radius:8px;">{{ $i+1 }}</div>
                    </td>

                    {{-- Teacher --}}
                    <td style="padding:13px 14px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <img src="{{ $cls['avatar'] }}"
                                 style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:1px solid #e2e8f0;background:#0d326b;flex-shrink:0;"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($cls['name']) }}&background=0d326b&color=fff&size=64&bold=true&rounded=true'">
                            <span style="font-weight:700;color:#1e293b;">{{ $cls['name'] }}</span>
                        </div>
                    </td>

                    {{-- Total Students --}}
                    <td style="padding:13px 14px;text-align:center;">
                        <span style="font-weight:800;color:#0d326b;font-size:15px;">{{ $cls['total_students'] }}</span>
                    </td>

                    {{-- Active --}}
                    <td style="padding:13px 14px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;height:6px;background:#f1f5f9;border-radius:99px;overflow:hidden;min-width:56px;">
                                <div style="height:100%;border-radius:99px;background:{{ $actColor }};width:{{ $cls['active_pct'] }}%;"></div>
                            </div>
                            <span style="font-weight:800;color:{{ $actColor }};font-size:12px;white-space:nowrap;">
                                {{ $cls['active_students'] }}<span style="color:#94a3b8;font-weight:500;"> / {{ $cls['total_students'] }}</span>
                            </span>
                        </div>
                    </td>

                    {{-- Quiz Pass Rate --}}
                    <td style="padding:13px 14px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;height:6px;background:#f1f5f9;border-radius:99px;overflow:hidden;min-width:56px;">
                                <div style="height:100%;border-radius:99px;background:{{ $qColor }};width:{{ min(100,$cls['quiz_pass_rate']) }}%;"></div>
                            </div>
                            <span style="font-weight:800;color:{{ $qColor }};font-size:12px;white-space:nowrap;">{{ $cls['quiz_pass_rate'] }}%</span>
                        </div>
                        <p style="font-size:10px;color:#94a3b8;margin-top:2px;">{{ $cls['quiz_passed'] }} / {{ $cls['quiz_total'] }} quizzes</p>
                    </td>

                    {{-- Checkpoint Pass Rate --}}
                    <td style="padding:13px 14px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;height:6px;background:#f1f5f9;border-radius:99px;overflow:hidden;min-width:56px;">
                                <div style="height:100%;border-radius:99px;background:{{ $ckColor }};width:{{ min(100,$cls['ck_pass_rate']) }}%;"></div>
                            </div>
                            <span style="font-weight:800;color:{{ $ckColor }};font-size:12px;white-space:nowrap;">{{ $cls['ck_pass_rate'] }}%</span>
                        </div>
                        <p style="font-size:10px;color:#94a3b8;margin-top:2px;">{{ $cls['ck_passed'] }} / {{ $cls['ck_total'] }} exams</p>
                    </td>

                    {{-- Avg Score --}}
                    <td style="padding:13px 14px;text-align:center;">
                        <span style="font-weight:900;font-size:15px;color:{{ $sc }};">{{ $cls['avg_quiz_score'] }}%</span>
                    </td>

                    {{-- Status badge --}}
                    <td style="padding:13px 14px;text-align:center;">
                        <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:99px;{{ $sl[0] }};color:{{ $sl[1] }};">{{ $sl[2] }}</span>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="padding:56px 0;text-align:center;">
            <span class="material-symbols-outlined" style="font-size:52px;color:#e2e8f0;">grading</span>
            <p style="font-size:13px;color:#94a3b8;margin-top:8px;font-weight:600;">No class data for this period.</p>
        </div>
        @endif
    </div>

</div>{{-- /space-y-6 --}}

<script>
/* ── Period-aware month filter ─────────────────────────────────────────── */
function toggleMonthWrap() {
    const p = document.getElementById('periodSelect').value;
    const w = document.getElementById('monthFilterWrap');
    if (w) w.classList.toggle('hidden', !['monthly','quarterly'].includes(p));
}

/* ── Enrollment bar chart tooltip ──────────────────────────────────────── */
const enrollTip = document.getElementById('enrollTip');
function showEnrollTip(e, el) {
    if (!enrollTip) return;
    const sy    = el.dataset.sy;
    const pt    = el.dataset.pt;
    const val   = el.dataset.val;
    const total = el.dataset.total;
    enrollTip.innerHTML = `<strong>SY ${sy}</strong> · ${pt}<br><span style="color:#93c5fd">${val} student${val != 1 ? 's' : ''}</span> &nbsp;|&nbsp; Total: ${total}`;
    enrollTip.style.left = e.clientX + 'px';
    enrollTip.style.top  = e.clientY + 'px';
    enrollTip.classList.add('visible');
    el.style.opacity = '0.75';
}
function hideEnrollTip() {
    if (enrollTip) enrollTip.classList.remove('visible');
    document.querySelectorAll('.enroll-bar').forEach(b => b.style.opacity = '1');
}
</script>

@endsection
