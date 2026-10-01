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
        <div class="skeleton skeleton-card h-[130px]"></div>
        @for($i=0;$i<2;$i++)
        <div class="bg-white rounded-[24px] p-6 border border-slate-100 shadow-sm flex flex-col gap-3">
            <div class="flex justify-between"><div class="skeleton h-3 rounded w-32"></div><div class="skeleton w-10 h-10 rounded-xl"></div></div>
            <div class="skeleton h-9 rounded w-20"></div>
            <div class="skeleton h-3 rounded w-36"></div>
        </div>
        @endfor
        <div class="skeleton skeleton-card h-[130px]"></div>
    </div>
    <div class="skeleton rounded-[18px] h-16 w-full"></div>
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-7 bg-white rounded-[26px] border border-slate-100 shadow-sm p-6 flex flex-col gap-4">
            <div class="flex justify-between pb-3 border-b border-slate-100">
                <div class="flex flex-col gap-2"><div class="skeleton h-5 rounded w-40"></div><div class="skeleton h-3 rounded w-56"></div></div>
            </div>
            @for($i=0;$i<6;$i++)
            <div class="flex items-center gap-3">
                <div class="skeleton w-8 h-8 rounded-lg flex-shrink-0"></div>
                <div class="skeleton skeleton-circle w-9 h-9 flex-shrink-0"></div>
                <div class="flex-1 flex flex-col gap-1.5"><div class="skeleton h-3 rounded w-32"></div><div class="skeleton h-2 rounded w-24"></div></div>
                <div class="skeleton h-4 rounded w-14 ml-auto"></div>
            </div>
            @endfor
        </div>
        <div class="lg:col-span-5 flex flex-col gap-6">
            <div class="bg-white rounded-[26px] border border-slate-100 shadow-sm p-6 flex flex-col gap-4">
                <div class="skeleton h-4 rounded w-40"></div>
                <div class="skeleton rounded-2xl w-full" style="padding-bottom:42%;"></div>
            </div>
            <div class="bg-white rounded-[26px] border border-slate-100 shadow-sm p-6 flex flex-col gap-3">
                <div class="skeleton h-4 rounded w-36"></div>
                @for($i=0;$i<4;$i++)
                <div class="flex items-center gap-2"><div class="skeleton h-3 rounded w-12"></div><div class="skeleton h-5 rounded flex-1"></div></div>
                @endfor
            </div>
        </div>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded',function(){var s=document.getElementById('page-skeleton');if(s)s.style.display='none';});</script>

<style>
:root {
    --navy-950: #071c3f; --navy-900: #0d326b; --navy-700: #1e4b8f;
    --navy-500: #1a6fd4; --navy-400: #3b82f6; --navy-200: #bfdbfe;
    --navy-100: #dbeafe; --navy-50: #eff6ff;
    --gold-600: #b45309; --gold-500: #d97706; --gold-400: #f59e0b;
    --gold-100: #fef3c7; --gold-50: #fffbeb;
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
.senya-insight-gold-text { font-size:13px; font-weight:500; color:#78350f; line-height:1.55; }

/* Rank rows */
.rank-item-row {
    display:flex; align-items:center; justify-content:space-between;
    padding:12px 14px; border-radius:16px; background:#fff; border:1px solid #f1f5f9;
    transition:background .15s,border-color .15s;
}
.rank-item-row:hover { background:#f8fafc; border-color:#e2e8f0; }
.rank-badge-podium { width:30px; height:30px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:800; flex-shrink:0; }
.rank-badge-gold   { background:linear-gradient(135deg,#f59e0b,#facc15,#fbbf24); color:#78350f; border:1px solid #f59e0b; box-shadow:0 2px 6px rgba(245,158,11,.25); }
.rank-badge-silver { background:#f1f5f9; color:#475569; border:1px solid #e2e8f0; }
.rank-badge-bronze { background:#ffedd5; color:#c2410c; border:1px solid #fed7aa; }
.rank-badge-default{ background:#f8fafc; color:#64748b; border:1px solid #e2e8f0; }

/* Chart tooltip */
.chart-tooltip {
    position:absolute; pointer-events:none; background:#071c3f; color:#fff;
    padding:6px 12px; border-radius:10px; font-size:11.5px; font-weight:700;
    box-shadow:0 4px 14px rgba(0,0,0,.25); white-space:nowrap;
    opacity:0; transform:translate(-50%,-100%); transition:opacity .15s; z-index:40;
}
.chart-tooltip.visible { opacity:1; }
</style>

@php
/* ══ SVG bezier helper ══════════════════════════════════════════════════ */
$bez = function (array $pts, float $bot, bool $area = false): string {
    if (empty($pts)) return '';
    $d = "M {$pts[0]['x']},{$pts[0]['y']}";
    for ($i = 0; $i < count($pts) - 1; $i++) {
        $dx = ($pts[$i+1]['x'] - $pts[$i]['x']) / 2;
        $d .= " C ".($pts[$i]['x']+$dx).",{$pts[$i]['y']} ".($pts[$i+1]['x']-$dx).",{$pts[$i+1]['y']} {$pts[$i+1]['x']},{$pts[$i+1]['y']}";
    }
    if ($area) $d .= " L {$pts[count($pts)-1]['x']},{$bot} L {$pts[0]['x']},{$bot} Z";
    return $d;
};

/* ══ Completion trend ═══════════════════════════════════════════════════ */
$CW=600; $CH=220; $CPL=40; $CPR=20; $CPT=16; $CPB=32;
$cPW=$CW-$CPL-$CPR; $cPH=$CH-$CPT-$CPB; $cBot=$CPT+$cPH;
$ctN   = count($completionTrend);
$ctMax = max(1, collect($completionTrend)->max('count'));
$ctPts = [];
foreach ($completionTrend as $i => $d) {
    $x = $ctN > 1 ? $CPL + ($i / ($ctN - 1)) * $cPW : $CPL + $cPW / 2;
    $ctPts[] = ['x' => round($x,2), 'y' => round($CPT + $cPH - ($d['count'] / $ctMax) * $cPH, 2), 'label' => $d['label'], 'val' => $d['count']];
}
$ctLine = $bez($ctPts, $cBot); $ctArea = $bez($ctPts, $cBot, true);

/* ══ Score distribution bars ════════════════════════════════════════════ */
$SBW=580; $SBH=200; $SBPL=12; $SBPR=12; $SBPT=20; $SBPB=32;
$sbPW = $SBW-$SBPL-$SBPR; $sbPH = $SBH-$SBPT-$SBPB;
$sbMax   = max(1, collect($scoreBuckets)->max('count'));
$sbCount = count($scoreBuckets);
$sbColors= ['#ef4444','#f97316','#f59e0b','#84cc16','#22c55e'];

/* ══ Senya Insights ═════════════════════════════════════════════════════ */
$insights = [];

if ($completionRate >= 80) {
    $insights[] = "<strong>{$completionRate}%</strong> of assigned lessons completed school-wide — students are moving through the curriculum at a strong pace.";
} elseif ($completionRate >= 50) {
    $insights[] = "<strong>{$completionRate}%</strong> school-wide lesson completion. Over half the assigned content has been completed across all classes.";
} else {
    $insights[] = "Lesson completion is at <strong>{$completionRate}%</strong>. Consider coordinating with teachers to review pacing and assignment coverage.";
}

if ($avgQuizScore >= 75) {
    $insights[] = "School quiz average is healthy at <strong>{$avgQuizScore}%</strong>. Students are demonstrating solid content retention.";
} elseif ($avgQuizScore >= 50) {
    $insights[] = "School quiz average sits at <strong>{$avgQuizScore}%</strong>. Targeted review sessions could help lift the average closer to the 75% benchmark.";
} else {
    $insights[] = "School quiz average of <strong>{$avgQuizScore}%</strong> is below the 75% target. Recommend reviewing assessment difficulty and student preparation strategies.";
}

if ($activeStudentsCount > 0) {
    $insights[] = "<strong>{$activeStudentsCount}</strong> students were actively learning during this period.";
}

$topClass = $classBreakdown->first();
if ($topClass && $topClass['avg_quiz_score'] > 0) {
    $insights[] = "Top performing class: <strong>{$topClass['name']}</strong> with a <strong>{$topClass['avg_quiz_score']}%</strong> average — a model to share best practices from.";
}

$insight = $insights[array_rand($insights)];

/* ══ Donut segments ══════════════════════════════════════════════════════ */
$fTotal  = max(1, collect($completionFunnel)->sum('count'));
$fColors = ['#e2e8f0','#93c5fd','#22c55e','#f87171'];
$fCx = 110; $fCy = 100; $fR = 78; $fIn = 48;
$fAngle = -M_PI / 2; $fSegs = [];
foreach ($completionFunnel as $idx => $seg) {
    $sw = $fTotal > 0 ? ($seg['count'] / $fTotal) * 2 * M_PI : 0;
    $x1 = round($fCx + $fR  * cos($fAngle),   2); $y1 = round($fCy + $fR  * sin($fAngle),   2);
    $x2 = round($fCx + $fR  * cos($fAngle+$sw),2); $y2 = round($fCy + $fR  * sin($fAngle+$sw),2);
    $i1 = round($fCx + $fIn * cos($fAngle),   2); $j1 = round($fCy + $fIn * sin($fAngle),   2);
    $i2 = round($fCx + $fIn * cos($fAngle+$sw),2); $j2 = round($fCy + $fIn * sin($fAngle+$sw),2);
    $lg = ($sw > M_PI) ? 1 : 0;
    $fSegs[] = [
        'path'  => "M {$x1},{$y1} A {$fR},{$fR} 0 {$lg},1 {$x2},{$y2} L {$i2},{$j2} A {$fIn},{$fIn} 0 {$lg},0 {$i1},{$j1} Z",
        'color' => $fColors[$idx] ?? '#e2e8f0',
        'label' => $seg['status'],
        'count' => $seg['count'],
        'pct'   => $fTotal > 0 ? round($seg['count'] / $fTotal * 100, 1) : 0,
        'empty' => $sw < 0.01,
    ];
    $fAngle += $sw;
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
                        @foreach(['weekly'=>'Weekly Trend','monthly'=>'Monthly','quarterly'=>'Quarterly','yearly'=>'Yearly'] as $val => $lbl)
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

                <a href="{{ route('grade-leader.analytics') }}"
                   onclick="event.preventDefault(); fetch('{{ route('grade-leader.analytics.filter') }}', {method:'POST', headers:{'X-CSRF-TOKEN':'{{ csrf_token() }}','Content-Type':'application/json'}, body:JSON.stringify({period:'weekly',year:{{ date('Y') }},month:{{ date('n') }}})}).then(()=>window.location.href='{{ route('grade-leader.analytics') }}')"
                   class="filter-reset">Reset</a>

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

    {{-- ── 2. KPI CARDS ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

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

        <div class="stat-kpi-card" style="background:#f0fdf4;border-color:#d1fae5">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-emerald-600">Completion Rate</span>
                <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">task_alt</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-emerald-700 tracking-tight">{{ $completionRate }}%</p>
            <p class="text-[12px] text-emerald-600 font-medium">lessons completed</p>
        </div>

        <div class="stat-kpi-card text-amber-950" style="background:linear-gradient(135deg,#f59e0b 0%,#facc15 50%,#fbbf24 100%);border-color:rgba(245,158,11,.5);box-shadow:0 4px 16px rgba(245,158,11,.22)">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-950/80">Active Students</span>
                <div class="w-10 h-10 rounded-xl bg-white/35 text-amber-950 flex items-center justify-center backdrop-blur-sm shadow-sm">
                    <span class="material-symbols-outlined text-[20px]">bolt</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-amber-950 tracking-tight">{{ number_format($activeStudentsCount) }}</p>
            <p class="text-[12px] text-amber-950/80 font-bold">in period</p>
        </div>

    </div>

    {{-- ── 3. SENYA INSIGHT BANNER ──────────────────────────────────────── --}}
    <div class="senya-insight-gold">
        <div class="senya-insight-gold-icon">
            <span class="material-symbols-outlined text-[20px]">lightbulb</span>
        </div>
        <div>
            <div class="senya-insight-gold-title">Senya School Insight</div>
            <div class="senya-insight-gold-text">{!! $insight !!}</div>
        </div>
    </div>

    {{-- ── 4. COMPLETION TREND (full width) ──────────────────────────────── --}}
    <div class="analytics-panel">
        <div class="flex items-start justify-between mb-4 pb-3 border-b border-slate-100">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Trend</p>
                <h3 class="text-[17px] font-black text-[#0d326b]">Lesson Completion Trend</h3>
                <p class="text-[12px] text-slate-400 mt-0.5">School-wide completions — {{ ucfirst($period) }} view</p>
            </div>
            <span class="material-symbols-outlined text-slate-300 text-[24px]">show_chart</span>
        </div>

        <div class="relative bg-[#fafcff] rounded-2xl" style="padding-bottom:22%">
            <div id="ctTooltip" class="chart-tooltip"></div>
            <svg id="ctChart" viewBox="0 0 {{ $CW }} {{ $CH }}" class="absolute inset-0 w-full h-full"
                 preserveAspectRatio="none" overflow="visible">
                <defs>
                    <linearGradient id="ctFill" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#0d326b" stop-opacity=".20"/>
                        <stop offset="100%" stop-color="#0d326b" stop-opacity="0"/>
                    </linearGradient>
                    <linearGradient id="ctLine" x1="0" y1="0" x2="100%" y2="0">
                        <stop offset="0%" stop-color="#1e4b8f"/>
                        <stop offset="100%" stop-color="#1a6fd4"/>
                    </linearGradient>
                </defs>
                {{-- Grid --}}
                @foreach([0,25,50,75,100] as $gv)
                    @php $gy = round($CPT + $cPH - ($gv / 100) * $cPH, 1); @endphp
                    <line x1="{{ $CPL }}" y1="{{ $gy }}" x2="{{ $CPL+$cPW }}" y2="{{ $gy }}"
                          stroke="#e8ecf2" stroke-width="1" stroke-dasharray="4,4"/>
                    <text x="{{ $CPL - 6 }}" y="{{ $gy + 4 }}" font-size="9" fill="#94a3b8" font-weight="600" text-anchor="end">{{ $gv }}</text>
                @endforeach
                {{-- Area + Line --}}
                <path d="{{ $ctArea }}" fill="url(#ctFill)"/>
                <path d="{{ $ctLine }}" fill="none" stroke="url(#ctLine)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                {{-- Dots + hit areas --}}
                @foreach($ctPts as $i => $p)
                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}"
                            r="{{ $i===count($ctPts)-1?4.5:3.5 }}"
                            fill="{{ $i===count($ctPts)-1?'#0d326b':'#1e4b8f' }}"
                            stroke="white" stroke-width="2"/>
                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="14" fill="transparent"
                            class="ct-hit" style="cursor:pointer"
                            data-label="{{ $p['label'] }}" data-val="{{ $p['val'] }}"
                            onmouseover="showCtTip(event,this)" onmouseout="hideCtTip()"/>
                    @if($i % max(1, intval(count($ctPts)/8)) === 0 || $i===count($ctPts)-1)
                    <text x="{{ $p['x'] }}" y="{{ $CH - 12 }}"
                          font-size="10" fill="#94a3b8" font-weight="500" text-anchor="middle">{{ $p['label'] }}</text>
                    @endif
                @endforeach
            </svg>
        </div>
    </div>

    {{-- ── 7. GESTURE MASTERY removed --}}
    {{-- ── 6. GRADE-LEVEL BREAKDOWN removed --}}

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- 5. CLASS PERFORMANCE SCORECARD                                        --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    <div class="analytics-panel">
        <div class="flex items-start justify-between pb-4 border-b border-slate-100 mb-5">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Per Classroom</p>
                <h3 class="text-[17px] font-black text-[#0d326b]">Class Performance Scorecard</h3>
                <p class="text-[12px] text-slate-400 mt-0.5">Ranked by score · Active students · Quiz pass rate · Checkpoint exam pass rate — {{ ucfirst($period) }} period</p>
            </div>
            <span class="material-symbols-outlined text-slate-300 text-[24px]">grading</span>
        </div>

        @if($classPerformance->isNotEmpty())
        <div class="overflow-x-auto">
            <table style="width:100%;border-collapse:collapse;font-size:13px;">
                <thead>
                    <tr>
                        @foreach(['Rank','Teacher','Students','Active','Quiz Pass Rate','Checkpoint Pass Rate','Avg Score','Status'] as $th)
                        <th style="padding:10px 14px;text-align:{{ in_array($th, ['Rank','Students','Avg Score','Status']) ? 'center' : 'left' }};font-size:10.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:.07em;border-bottom:1px solid #f1f5f9;white-space:nowrap;background:#fafcff;">{{ $th }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                @foreach($classPerformance as $i => $cls)
                @php
                    $badgeClass = $i===0?'rank-badge-gold':($i===1?'rank-badge-silver':($i===2?'rank-badge-bronze':'rank-badge-default'));
                    $sc = $cls['avg_quiz_score'] >= 75 ? '#16a34a' : ($cls['avg_quiz_score'] >= 50 ? '#d97706' : '#ef4444');
                    $sl = $cls['status'] === 'on_track' ? ['background:#ecfdf5','color:#15803d','On Track']
                        : ($cls['status'] === 'needs_attention' ? ['background:#fffbeb','color:#b45309','Needs Attention']
                        : ['background:#fef2f2','color:#b91c1c','Needs Support']);
                    $qColor = $cls['quiz_pass_rate'] >= 75 ? '#16a34a' : ($cls['quiz_pass_rate'] >= 50 ? '#d97706' : '#ef4444');
                    $ckColor = $cls['ck_pass_rate'] >= 75 ? '#16a34a' : ($cls['ck_pass_rate'] >= 50 ? '#d97706' : '#ef4444');
                    $actColor = $cls['active_pct'] >= 70 ? '#16a34a' : ($cls['active_pct'] >= 40 ? '#d97706' : '#ef4444');
                @endphp
                <tr style="border-bottom:1px solid #f8fafc;transition:background .15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background=''">
                    <td style="padding:13px 14px;text-align:center;width:60px;">
                        <div class="rank-badge-podium {{ $badgeClass }}" style="margin:0 auto;width:28px;height:28px;font-size:11px;border-radius:8px;">{{ $i + 1 }}</div>
                    </td>
                    <td style="padding:13px 14px;">
                        <div style="display:flex;align-items:center;gap:10px;">
                            <img src="{{ $cls['avatar'] }}"
                                 style="width:34px;height:34px;border-radius:50%;object-fit:cover;border:1px solid #e2e8f0;background:#0d326b;flex-shrink:0;"
                                 onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($cls['name']) }}&background=0d326b&color=fff&size=64&bold=true&rounded=true'">
                            <span style="font-weight:700;color:#1e293b;">{{ $cls['name'] }}</span>
                        </div>
                    </td>
                    <td style="padding:13px 14px;text-align:center;">
                        <span style="font-weight:800;color:#0d326b;font-size:15px;">{{ $cls['total_students'] }}</span>
                    </td>
                    <td style="padding:13px 14px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;height:6px;background:#f1f5f9;border-radius:99px;overflow:hidden;min-width:64px;">
                                <div style="height:100%;border-radius:99px;background:{{ $actColor }};width:{{ $cls['active_pct'] }}%;"></div>
                            </div>
                            <span style="font-weight:800;color:{{ $actColor }};font-size:12px;white-space:nowrap;">{{ $cls['active_students'] }}<span style="color:#94a3b8;font-weight:500;"> / {{ $cls['total_students'] }}</span></span>
                        </div>
                    </td>
                    <td style="padding:13px 14px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;height:6px;background:#f1f5f9;border-radius:99px;overflow:hidden;min-width:64px;">
                                <div style="height:100%;border-radius:99px;background:{{ $qColor }};width:{{ min(100,$cls['quiz_pass_rate']) }}%;"></div>
                            </div>
                            <span style="font-weight:800;color:{{ $qColor }};font-size:12px;white-space:nowrap;">{{ $cls['quiz_pass_rate'] }}%</span>
                        </div>
                        <p style="font-size:10px;color:#94a3b8;margin-top:2px;">{{ $cls['quiz_passed'] }} / {{ $cls['quiz_total'] }} quizzes</p>
                    </td>
                    <td style="padding:13px 14px;">
                        <div style="display:flex;align-items:center;gap:8px;">
                            <div style="flex:1;height:6px;background:#f1f5f9;border-radius:99px;overflow:hidden;min-width:64px;">
                                <div style="height:100%;border-radius:99px;background:{{ $ckColor }};width:{{ min(100,$cls['ck_pass_rate']) }}%;"></div>
                            </div>
                            <span style="font-weight:800;color:{{ $ckColor }};font-size:12px;white-space:nowrap;">{{ $cls['ck_pass_rate'] }}%</span>
                        </div>
                        <p style="font-size:10px;color:#94a3b8;margin-top:2px;">{{ $cls['ck_passed'] }} / {{ $cls['ck_total'] }} exams</p>
                    </td>
                    <td style="padding:13px 14px;text-align:center;">
                        <span style="font-weight:900;font-size:15px;color:{{ $sc }};">{{ $cls['avg_quiz_score'] }}%</span>
                    </td>
                    <td style="padding:13px 14px;text-align:center;">
                        <span style="font-size:10px;font-weight:700;padding:3px 10px;border-radius:99px;{{ $sl[0] }};{{ $sl[1] }}">{{ $sl[2] }}</span>
                    </td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div style="padding:48px 0;text-align:center;">
            <span class="material-symbols-outlined" style="font-size:52px;color:#e2e8f0;">grading</span>
            <p style="font-size:13px;color:#94a3b8;margin-top:8px;font-weight:600;">No class data for this period.</p>
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- 6. ACTIVE vs INACTIVE STUDENTS PER CLASSROOM                          --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @php
    $aviMax   = max(1, $activeVsInactive->max('total'));
    $aviCount = $activeVsInactive->count();
    $AW=700; $AH=260; $APL=24; $APR=16; $APT=16; $APB=48;
    $aPW = $AW-$APL-$APR; $aPH = $AH-$APT-$APB;
    $aSlot = $aviCount > 0 ? $aPW / max(1, $aviCount) : $aPW;
    @endphp

    <div class="analytics-panel">
        <div class="flex items-start justify-between pb-4 border-b border-slate-100 mb-5">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Engagement</p>
                <h3 class="text-[17px] font-black text-[#0d326b]">Active vs Inactive Students per Classroom</h3>
                <p class="text-[12px] text-slate-400 mt-0.5">Students with activity during the {{ ucfirst($period) }} period vs those without</p>
            </div>
            <div style="display:flex;align-items:center;gap:16px;flex-shrink:0;">
                <div style="display:flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;border-radius:3px;background:#0d326b;display:inline-block;"></span><span style="font-size:11px;font-weight:600;color:#475569;">Active</span></div>
                <div style="display:flex;align-items:center;gap:6px;"><span style="width:12px;height:12px;border-radius:3px;background:#e2e8f0;display:inline-block;"></span><span style="font-size:11px;font-weight:600;color:#475569;">Inactive</span></div>
            </div>
        </div>

        @if($activeVsInactive->isNotEmpty())
        <div style="position:relative;" id="aviWrap">
            <div id="aviTip" class="chart-tooltip"></div>
            <svg id="aviChart" viewBox="0 0 {{ $AW }} {{ $AH }}" class="w-full" style="max-height:260px;" preserveAspectRatio="xMidYMid meet" overflow="visible">
                {{-- Grid lines --}}
                @foreach([0,25,50,75,100] as $gv)
                    @php $gy = round($APT + $aPH - ($gv / $aviMax) * $aPH * ($aviMax / max(1,$aviMax)), 1);
                         $gy = round($APT + $aPH - ($gv / 100) * $aPH, 1); @endphp
                    <line x1="{{ $APL }}" y1="{{ $gy }}" x2="{{ $APL+$aPW }}" y2="{{ $gy }}" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="3,3"/>
                @endforeach
                {{-- Bars --}}
                @foreach($activeVsInactive as $i => $av)
                @php
                    $barW    = round($aSlot * 0.55, 1);
                    $barHalf = round($aSlot * 0.275, 1);
                    $cx      = round($APL + ($i + 0.5) * $aSlot, 1);
                    $bx      = round($cx - $barHalf, 1);
                    $activeH   = $aviMax > 0 ? round(($av['active']   / $aviMax) * $aPH, 1) : 0;
                    $inactiveH = $aviMax > 0 ? round(($av['inactive'] / $aviMax) * $aPH, 1) : 0;
                    $totalH    = $aviMax > 0 ? round(($av['total']    / $aviMax) * $aPH, 1) : 0;
                    $activeY   = round($APT + $aPH - $activeH, 1);
                    $inactiveY = round($APT + $aPH - $totalH, 1);
                    $nameParts = explode(' ', $av['name']);
                    $shortName = ($nameParts[count($nameParts)-1] ?? $av['name']);
                @endphp
                {{-- Inactive (bottom segment, grey) --}}
                @if($inactiveH > 0)
                <rect x="{{ $bx }}" y="{{ $inactiveY }}" width="{{ $barW }}" height="{{ $inactiveH }}"
                      fill="#e2e8f0" rx="5" ry="5"
                      class="avi-bar-inactive"
                      data-name="{{ $av['name'] }}" data-active="{{ $av['active'] }}" data-inactive="{{ $av['inactive'] }}" data-total="{{ $av['total'] }}"
                      style="cursor:pointer;"
                      onmouseover="showAviTip(event,this,'inactive')" onmouseout="hideAviTip()"/>
                @endif
                {{-- Active (top segment, navy) --}}
                @if($activeH > 0)
                <rect x="{{ $bx }}" y="{{ $activeY }}" width="{{ $barW }}" height="{{ $activeH }}"
                      fill="url(#aviGrad)" rx="5" ry="5"
                      class="avi-bar-active"
                      data-name="{{ $av['name'] }}" data-active="{{ $av['active'] }}" data-inactive="{{ $av['inactive'] }}" data-total="{{ $av['total'] }}"
                      style="cursor:pointer;"
                      onmouseover="showAviTip(event,this,'active')" onmouseout="hideAviTip()"/>
                @endif
                {{-- Total label on top --}}
                @if($av['total'] > 0)
                <text x="{{ $cx }}" y="{{ $inactiveY > 0 ? $inactiveY - 5 : $APT - 5 }}" font-size="10" fill="#475569" font-weight="800" text-anchor="middle">{{ $av['total'] }}</text>
                @endif
                {{-- X-axis label --}}
                <text x="{{ $cx }}" y="{{ $AH - 6 }}" font-size="10" fill="#94a3b8" font-weight="600" text-anchor="middle">{{ $shortName }}</text>
                @endforeach
                <defs>
                    <linearGradient id="aviGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#1e4b8f"/>
                        <stop offset="100%" stop-color="#0d326b"/>
                    </linearGradient>
                </defs>
            </svg>
        </div>
        {{-- Summary row --}}
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:10px;margin-top:18px;border-top:1px solid #f1f5f9;padding-top:16px;">
            @foreach($activeVsInactive as $av)
            @php $ap = $av['total'] > 0 ? round($av['active'] / $av['total'] * 100) : 0; @endphp
            <div style="background:#f8fafc;border-radius:12px;padding:10px 14px;">
                <p style="font-size:11px;font-weight:700;color:#475569;truncate;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">{{ $av['name'] }}</p>
                <div style="display:flex;align-items:center;gap:6px;margin-top:6px;">
                    <div style="flex:1;height:5px;background:#e2e8f0;border-radius:99px;overflow:hidden;">
                        <div style="height:100%;background:#0d326b;border-radius:99px;width:{{ $ap }}%;"></div>
                    </div>
                    <span style="font-size:11px;font-weight:800;color:#0d326b;flex-shrink:0;">{{ $ap }}%</span>
                </div>
                <p style="font-size:10px;color:#94a3b8;margin-top:3px;"><span style="color:#0d326b;font-weight:700;">{{ $av['active'] }}</span> active · <span style="color:#ef4444;font-weight:700;">{{ $av['inactive'] }}</span> inactive</p>
            </div>
            @endforeach
        </div>
        @else
        <div style="padding:48px 0;text-align:center;">
            <span class="material-symbols-outlined" style="font-size:52px;color:#e2e8f0;">people</span>
            <p style="font-size:13px;color:#94a3b8;margin-top:8px;font-weight:600;">No student data available.</p>
        </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    {{-- 7. ENROLLMENT TREND PER YEAR PER TEACHER                              --}}
    {{-- ══════════════════════════════════════════════════════════════════════ --}}
    @php
    /* Palette: one color per teacher, cycling */
    $etPalette = ['#0d326b','#1a6fd4','#3b82f6','#60a5fa','#93c5fd','#1e4b8f','#7c3aed','#db2777','#059669','#d97706'];
    $etYears   = $enrollYears;
    $etCount   = count($etYears);
    $EW=700; $EH=240; $EPL=36; $EPR=20; $EPT=16; $EPB=36;
    $ePW = $EW-$EPL-$EPR; $ePH = $EH-$EPT-$EPB; $eBot = $EPT+$ePH;

    /* Build max for Y-axis */
    $etMax = 1;
    foreach($enrollmentTrend as $t) {
        foreach($etYears as $yr) {
            $etMax = max($etMax, $t['years'][$yr] ?? 0);
        }
    }

    /* Build polyline points per teacher */
    $etLines = [];
    foreach($enrollmentTrend as $tIdx => $t) {
        $pts = [];
        foreach($etYears as $yIdx => $yr) {
            $v = $t['years'][$yr] ?? 0;
            $x = $etCount > 1 ? round($EPL + ($yIdx / ($etCount-1)) * $ePW, 2) : $EPL + $ePW/2;
            $y = round($EPT + $ePH - ($v / $etMax) * $ePH, 2);
            $pts[] = ['x'=>$x,'y'=>$y,'v'=>$v,'yr'=>$yr];
        }
        $etLines[] = ['pts'=>$pts, 'color'=>$etPalette[$tIdx % count($etPalette)], 'name'=>$t['name'], 'total'=>$t['total']];
    }

    /* SVG polyline strings */
    $etPolylines = [];
    foreach($etLines as $li => $line) {
        $points = implode(' ', array_map(fn($p) => $p['x'].','.$p['y'], $line['pts']));
        $etPolylines[] = ['points'=>$points, 'color'=>$line['color'], 'name'=>$line['name'], 'total'=>$line['total'], 'pts'=>$line['pts']];
    }
    @endphp

    <div class="analytics-panel">
        <div class="flex items-start justify-between pb-4 border-b border-slate-100 mb-5" style="flex-wrap:wrap;gap:12px;">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Enrollment</p>
                <h3 class="text-[17px] font-black text-[#0d326b]">Enrollment Trend per Year</h3>
                <p class="text-[12px] text-slate-400 mt-0.5">Students enrolled per school year — categorized by teacher</p>
            </div>
            {{-- Legend --}}
            <div style="display:flex;flex-wrap:wrap;gap:8px 16px;align-items:center;">
                @foreach($enrollmentTrend as $tIdx => $t)
                <div style="display:flex;align-items:center;gap:5px;">
                    <span style="width:22px;height:3px;border-radius:99px;background:{{ $etPalette[$tIdx % count($etPalette)] }};display:inline-block;"></span>
                    <span style="font-size:11px;font-weight:600;color:#475569;">{{ $t['name'] }}</span>
                </div>
                @endforeach
            </div>
        </div>

        @if($enrollmentTrend->isNotEmpty() && count($etYears) >= 1)
        <div style="position:relative;">
            <div id="etTip" class="chart-tooltip"></div>
            <svg id="etChart" viewBox="0 0 {{ $EW }} {{ $EH }}" class="w-full" style="max-height:240px;" preserveAspectRatio="xMidYMid meet" overflow="visible">
                <defs>
                    @foreach($etPolylines as $pi => $pl)
                    <linearGradient id="etGrad{{ $pi }}" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="{{ $pl['color'] }}" stop-opacity=".18"/>
                        <stop offset="100%" stop-color="{{ $pl['color'] }}" stop-opacity="0"/>
                    </linearGradient>
                    @endforeach
                </defs>
                {{-- Grid --}}
                @foreach([0,25,50,75,100] as $gv)
                    @php $gy = round($EPT + $ePH - ($gv/100)*$ePH, 1); @endphp
                    <line x1="{{ $EPL }}" y1="{{ $gy }}" x2="{{ $EPL+$ePW }}" y2="{{ $gy }}" stroke="#f1f5f9" stroke-width="1" stroke-dasharray="3,3"/>
                    <text x="{{ $EPL-6 }}" y="{{ $gy+4 }}" font-size="9" fill="#94a3b8" font-weight="600" text-anchor="end">{{ round($etMax*$gv/100) }}</text>
                @endforeach
                {{-- X-axis year labels --}}
                @foreach($etYears as $yIdx => $yr)
                    @php $lx = $etCount > 1 ? round($EPL + ($yIdx/($etCount-1))*$ePW, 1) : $EPL+$ePW/2; @endphp
                    <text x="{{ $lx }}" y="{{ $EH-8 }}" font-size="10" fill="#94a3b8" font-weight="600" text-anchor="middle">{{ $yr }}</text>
                @endforeach
                {{-- Area fills (behind lines) --}}
                @foreach($etPolylines as $pi => $pl)
                @if(count($pl['pts']) > 1)
                @php
                    $areaPoints = implode(' ', array_map(fn($p) => $p['x'].','.$p['y'], $pl['pts']));
                    $first = $pl['pts'][0];
                    $last  = $pl['pts'][count($pl['pts'])-1];
                    $areaPath = "M {$first['x']},{$eBot} L ".implode(' L ', array_map(fn($p)=>"{$p['x']},{$p['y']}", $pl['pts']))." L {$last['x']},{$eBot} Z";
                @endphp
                <path d="{{ $areaPath }}" fill="url(#etGrad{{ $pi }})" />
                @endif
                @endforeach
                {{-- Lines --}}
                @foreach($etPolylines as $pi => $pl)
                @if(count($pl['pts']) > 1)
                <polyline points="{{ $pl['points'] }}" fill="none" stroke="{{ $pl['color'] }}" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
                @endif
                {{-- Dots + hit targets --}}
                @foreach($pl['pts'] as $pt)
                <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="4" fill="{{ $pl['color'] }}" stroke="white" stroke-width="2"/>
                <circle cx="{{ $pt['x'] }}" cy="{{ $pt['y'] }}" r="12" fill="transparent" style="cursor:pointer;"
                        data-name="{{ $pl['name'] }}" data-yr="{{ $pt['yr'] }}" data-val="{{ $pt['v'] }}"
                        onmouseover="showEtTip(event,this)" onmouseout="hideEtTip()"/>
                @endforeach
                @endforeach
            </svg>
        </div>

        {{-- Teacher totals summary row --}}
        <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:18px;padding-top:16px;border-top:1px solid #f1f5f9;">
            @foreach($enrollmentTrend as $tIdx => $t)
            <div style="display:flex;align-items:center;gap:10px;background:#f8fafc;border-radius:12px;padding:10px 14px;min-width:160px;flex:1;">
                <span style="width:4px;border-radius:99px;align-self:stretch;background:{{ $etPalette[$tIdx % count($etPalette)] }};display:inline-block;flex-shrink:0;"></span>
                <div>
                    <p style="font-size:11px;font-weight:700;color:#475569;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:140px;">{{ $t['name'] }}</p>
                    <p style="font-size:18px;font-weight:900;color:{{ $etPalette[$tIdx % count($etPalette)] }};line-height:1.1;margin-top:2px;">{{ $t['total'] }}</p>
                    <p style="font-size:10px;color:#94a3b8;margin-top:1px;">total enrolled</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div style="padding:48px 0;text-align:center;">
            <span class="material-symbols-outlined" style="font-size:52px;color:#e2e8f0;">trending_up</span>
            <p style="font-size:13px;color:#94a3b8;margin-top:8px;font-weight:600;">No enrollment data available.</p>
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

/* ── Bar chart tooltips ─────────────────────────────────────────────────── */
const sbTip  = document.getElementById('sbTooltip');
const sbChEl = document.getElementById('sbChart');
function showSbTip(e, el) {
    if (!sbTip || !sbChEl) return;
    const r    = sbChEl.getBoundingClientRect();
    const vb   = sbChEl.viewBox.baseVal;
    const cx   = parseFloat(el.getAttribute('x')) + parseFloat(el.getAttribute('width')) / 2;
    const px   = (cx / vb.width) * r.width + r.left;
    const cy   = parseFloat(el.getAttribute('y'));
    const py   = (cy / vb.height) * r.height + r.top;
    sbTip.textContent = el.dataset.label + ': ' + el.dataset.val + ' students';
    sbTip.style.left  = px + 'px';
    sbTip.style.top   = (py + window.scrollY) + 'px';
    sbTip.classList.add('visible');
    el.style.opacity  = '0.75';
}
function hideSbTip() {
    if (sbTip) sbTip.classList.remove('visible');
    document.querySelectorAll('.sb-bar').forEach(b => b.style.opacity = '1');
}

/* ── Trend chart tooltips ─────────────────────────────────────────────── */
const ctTip  = document.getElementById('ctTooltip');
const ctChEl = document.getElementById('ctChart');
function showCtTip(e, el) {
    if (!ctTip || !ctChEl) return;
    const r  = ctChEl.getBoundingClientRect();
    const vb = ctChEl.viewBox.baseVal;
    const cx = parseFloat(el.getAttribute('cx'));
    const cy = parseFloat(el.getAttribute('cy'));
    const px = (cx / vb.width)  * r.width  + r.left;
    const py = (cy / vb.height) * r.height + r.top;
    ctTip.textContent = el.dataset.label + ': ' + el.dataset.val + ' completions';
    ctTip.style.left  = px + 'px';
    ctTip.style.top   = (py + window.scrollY) + 'px';
    ctTip.classList.add('visible');
}
function hideCtTip() { if (ctTip) ctTip.classList.remove('visible'); }

/* ── Active vs Inactive chart tooltips ──────────────────────────────────── */
const aviTipEl  = document.getElementById('aviTip');
const aviChEl   = document.getElementById('aviChart');
function showAviTip(e, el, type) {
    if (!aviTipEl || !aviChEl) return;
    const r   = aviChEl.getBoundingClientRect();
    const vb  = aviChEl.viewBox.baseVal;
    const cx  = parseFloat(el.getAttribute('x')) + parseFloat(el.getAttribute('width')) / 2;
    const cy  = parseFloat(el.getAttribute('y'));
    const px  = (cx / vb.width)  * r.width  + r.left;
    const py  = (cy / vb.height) * r.height + r.top;
    const name    = el.dataset.name;
    const active  = el.dataset.active;
    const inactive= el.dataset.inactive;
    const total   = el.dataset.total;
    aviTipEl.innerHTML = `<strong>${name}</strong><br>Active: ${active} &nbsp;|&nbsp; Inactive: ${inactive} &nbsp;|&nbsp; Total: ${total}`;
    aviTipEl.style.left = px + 'px';
    aviTipEl.style.top  = (py + window.scrollY - 8) + 'px';
    aviTipEl.classList.add('visible');
    el.style.opacity = '0.8';
}
function hideAviTip() {
    if (aviTipEl) aviTipEl.classList.remove('visible');
    document.querySelectorAll('.avi-bar-active, .avi-bar-inactive').forEach(b => b.style.opacity = '1');
}

/* ── Enrollment Trend chart tooltips ────────────────────────────────────── */
const etTipEl  = document.getElementById('etTip');
const etChEl   = document.getElementById('etChart');
function showEtTip(e, el) {
    if (!etTipEl || !etChEl) return;
    const r  = etChEl.getBoundingClientRect();
    const vb = etChEl.viewBox.baseVal;
    const cx = parseFloat(el.getAttribute('cx'));
    const cy = parseFloat(el.getAttribute('cy'));
    const px = (cx / vb.width)  * r.width  + r.left;
    const py = (cy / vb.height) * r.height + r.top;
    etTipEl.innerHTML = `<strong>${el.dataset.name}</strong><br>${el.dataset.yr}: ${el.dataset.val} student${el.dataset.val != 1 ? 's' : ''}`;
    etTipEl.style.left = px + 'px';
    etTipEl.style.top  = (py + window.scrollY - 8) + 'px';
    etTipEl.classList.add('visible');
}
function hideEtTip() { if (etTipEl) etTipEl.classList.remove('visible'); }
</script>

@endsection
