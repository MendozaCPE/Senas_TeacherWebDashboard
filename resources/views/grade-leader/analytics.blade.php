@extends('layouts.grade-leader')
@section('title', 'School Analytics')
@section('content')

<style>
.tl-card  { background:#fff; border-radius:20px; padding:20px; border:1px solid #f1f5f9; box-shadow:0 1px 3px rgba(13,50,107,.05); transition:transform .2s,box-shadow .2s; }
.tl-card:hover  { transform:translateY(-2px); box-shadow:0 10px 28px rgba(13,50,107,.09); }
.tl-panel { background:#fff; border-radius:20px; border:1px solid #f1f5f9; box-shadow:0 1px 3px rgba(13,50,107,.05); padding:24px; }
.tl-panel:hover { box-shadow:0 6px 24px rgba(13,50,107,.07); }
.filter-wrap  { position:relative; display:inline-flex; align-items:center; }
.filter-select {
    appearance:none; background:#f8fafc; border:1px solid #e2e8f0;
    border-radius:14px; padding:8px 34px 8px 14px; font-size:13px;
    font-weight:600; color:#0d326b; cursor:pointer; outline:none; transition:border-color .15s;
}
.filter-select:hover { background:#f1f5f9; border-color:#cbd5e1; }
.filter-wrap .material-symbols-outlined { position:absolute; right:10px; pointer-events:none; font-size:18px; color:#0d326b; }
.filter-btn {
    display:inline-flex; align-items:center; gap:6px; padding:9px 18px; border-radius:14px;
    font-size:13px; font-weight:700; color:#fff; border:none; cursor:pointer;
    background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%); transition:opacity .15s;
}
.filter-btn:hover { opacity:.9; }
.stat-kpi-card { border-radius:24px; padding:22px 24px; position:relative; overflow:hidden; transition:transform .2s ease,box-shadow .2s ease; border:1px solid #f1f5f9; }
.stat-kpi-card:hover { transform:translateY(-2px); box-shadow:0 10px 26px rgba(13,50,107,.08); }
</style>

{{-- ══════════════════════════════════════════════════════════════════════
     SKELETON
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="page-skeleton" class="flex flex-col gap-5 w-full pt-4" aria-hidden="true">
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm px-5 py-3.5 flex items-center gap-3">
        <div class="skeleton h-10 rounded-full w-40"></div>
        <div class="skeleton h-10 rounded-full w-32"></div>
        <div class="skeleton h-10 rounded-full w-32"></div>
        <div class="skeleton h-10 rounded-full w-24 ml-auto"></div>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @for($i=0;$i<4;$i++)
        <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 border border-slate-100 shadow-sm min-h-[110px] flex flex-col gap-3">
            <div class="flex items-center justify-between"><div class="skeleton h-3 rounded w-28"></div><div class="skeleton w-10 h-10 rounded-xl"></div></div>
            <div class="skeleton h-9 rounded w-20"></div>
        </div>
        @endfor
    </div>
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        @for($i=0;$i<4;$i++)
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 p-6 flex flex-col gap-4">
            <div class="skeleton h-4 rounded w-40"></div>
            <div class="skeleton rounded-2xl w-full" style="padding-bottom:42%;"></div>
        </div>
        @endfor
    </div>
</div>

@php
/* ═══ SVG chart helpers ══════════════════════════════════════════════════ */
$bezier = function (array $pts, float $bot, bool $area = false): string {
    if (empty($pts)) return '';
    $d = "M {$pts[0]['x']},{$pts[0]['y']}";
    for ($i = 0; $i < count($pts) - 1; $i++) {
        $dx = ($pts[$i+1]['x'] - $pts[$i]['x']) / 2;
        $d .= " C ".($pts[$i]['x']+$dx).",{$pts[$i]['y']} ".($pts[$i+1]['x']-$dx).",{$pts[$i+1]['y']} {$pts[$i+1]['x']},{$pts[$i+1]['y']}";
    }
    if ($area) $d .= " L {$pts[count($pts)-1]['x']},{$bot} L {$pts[0]['x']},{$bot} Z";
    return $d;
};

/* ── Completion trend line ── */
$CW=600; $CH=220; $CPL=28; $CPR=28; $CPT=16; $CPB=28;
$cPlotW=$CW-$CPL-$CPR; $cPlotH=$CH-$CPT-$CPB; $cBot=$CPT+$cPlotH;
$ctN = count($completionTrend);
$ctPeak = max(1, collect($completionTrend)->max('count'));
$ctPts = [];
foreach ($completionTrend as $i => $d) {
    $x = $ctN > 1 ? $CPL + ($i/($ctN-1))*$cPlotW : $CPL+$cPlotW/2;
    $ctPts[] = ['x'=>round($x,2), 'y'=>round($CPT+$cPlotH-($d['count']/$ctPeak)*$cPlotH,2), 'label'=>$d['label']];
}
$ctLine = $bezier($ctPts, $cBot); $ctArea = $bezier($ctPts, $cBot, true);

/* ── Score distribution bar chart data ── */
$SBW=600; $SBH=200; $SBPL=28; $SBPR=28; $SBPT=16; $SBPB=32;
$sbPlotW=$SBW-$SBPL-$SBPR; $sbPlotH=$SBH-$SBPT-$SBPB;
$sbMax   = max(1, collect($scoreBuckets)->max('count'));
$sbCount = count($scoreBuckets);
$sbBarW  = ($sbCount > 0) ? floor($sbPlotW / $sbCount) - 6 : 60;
$sbColors= ['#ef4444','#f97316','#f59e0b','#84cc16','#22c55e'];

/* ── Completion funnel doughnut ── */
$fTotal   = max(1, collect($completionFunnel)->sum('count'));
$fColors  = ['#e2e8f0','#93c5fd','#22c55e','#f87171'];
$fCx=150; $fCy=110; $fR=80; $fInner=50;
$fAngle   = -M_PI / 2;
$fSegments= [];
foreach ($completionFunnel as $idx => $seg) {
    $sweep = ($fTotal > 0) ? ($seg['count'] / $fTotal) * 2 * M_PI : 0;
    $x1 = round($fCx + $fR * cos($fAngle), 2);
    $y1 = round($fCy + $fR * sin($fAngle), 2);
    $x2 = round($fCx + $fR * cos($fAngle + $sweep), 2);
    $y2 = round($fCy + $fR * sin($fAngle + $sweep), 2);
    $xi1= round($fCx + $fInner * cos($fAngle), 2);
    $yi1= round($fCy + $fInner * sin($fAngle), 2);
    $xi2= round($fCx + $fInner * cos($fAngle + $sweep), 2);
    $yi2= round($fCy + $fInner * sin($fAngle + $sweep), 2);
    $large = ($sweep > M_PI) ? 1 : 0;
    $fSegments[] = [
        'path'  => "M {$x1},{$y1} A {$fR},{$fR} 0 {$large},1 {$x2},{$y2} L {$xi2},{$yi2} A {$fInner},{$fInner} 0 {$large},0 {$xi1},{$yi1} Z",
        'color' => $fColors[$idx] ?? '#e2e8f0',
        'label' => $seg['status'],
        'count' => $seg['count'],
        'pct'   => $fTotal > 0 ? round($seg['count']/$fTotal*100,1) : 0,
        'empty' => $sweep < 0.01,
    ];
    $fAngle += $sweep;
}
@endphp

{{-- ══════════════════════════════════════════════════════════════════════
     REAL CONTENT
     ══════════════════════════════════════════════════════════════════════ --}}
<div class="flex flex-col gap-5 w-full pt-4 skeleton-hide">

    {{-- ── Period Filter ────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-[22px] border border-slate-100 shadow-sm px-5 py-3.5 flex items-center justify-between gap-4 flex-wrap">
        <div>
            <p class="text-[15px] font-black text-[#0d326b]">School Analytics</p>
            <p class="text-[11px] text-slate-400 mt-0.5">Scoped to <span class="font-semibold text-slate-600">{{ $school->name ?? 'your school' }}</span></p>
        </div>
        <form method="POST" action="{{ route('grade-leader.analytics.filter') }}" class="flex items-center gap-2 flex-wrap">
            @csrf
            <div class="filter-wrap">
                <select name="period" class="filter-select" onchange="this.form.submit()">
                    @foreach(['weekly'=>'This Week','monthly'=>'This Month','quarterly'=>'This Quarter','yearly'=>'This Year'] as $val=>$lbl)
                    <option value="{{ $val }}" {{ $period===$val?'selected':'' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined">expand_more</span>
            </div>
            @if(in_array($period,['monthly','quarterly','yearly']))
            <div class="filter-wrap">
                <select name="year" class="filter-select" onchange="this.form.submit()">
                    @foreach(range(date('Y'), date('Y')-4) as $y)
                    <option value="{{ $y }}" {{ $year===$y?'selected':'' }}>{{ $y }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined">expand_more</span>
            </div>
            @endif
            @if($period==='monthly')
            <div class="filter-wrap">
                <select name="month" class="filter-select" onchange="this.form.submit()">
                    @foreach(range(1,12) as $m)
                    <option value="{{ $m }}" {{ $month===$m?'selected':'' }}>{{ \Carbon\Carbon::create(null,$m)->format('F') }}</option>
                    @endforeach
                </select>
                <span class="material-symbols-outlined">expand_more</span>
            </div>
            @endif
            <button type="submit" class="filter-btn">
                <span class="material-symbols-outlined text-[16px]">filter_alt</span> Apply
            </button>
        </form>
    </div>

    {{-- ── KPI Cards ────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">

        <div class="stat-kpi-card text-white" style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%);border-color:#f1f5f9">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-bold uppercase tracking-wider text-white/70">Avg Quiz Score</span>
                <div class="w-10 h-10 rounded-xl bg-white/15 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px] text-white">insights</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-white tracking-tight">{{ $avgQuizScore }}%</p>
            <p class="text-[12px] text-white/70 font-medium">{{ ucfirst($period) }} period average</p>
        </div>

        <div class="stat-kpi-card" style="background:#fff">
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
            <p class="text-[12px] text-emerald-600 font-medium">assigned lessons done</p>
        </div>

        <div class="stat-kpi-card text-amber-950" style="background:linear-gradient(135deg,#f59e0b 0%,#facc15 50%,#fbbf24 100%);border-color:rgba(245,158,11,.5);box-shadow:0 4px 16px rgba(245,158,11,.22)">
            <div class="flex items-center justify-between mb-4">
                <span class="text-[11px] font-black uppercase tracking-wider text-amber-950/80">Active Students</span>
                <div class="w-10 h-10 rounded-xl bg-white/35 text-amber-950 flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">bolt</span>
                </div>
            </div>
            <p class="text-[36px] font-black leading-none mb-1 text-amber-950 tracking-tight">{{ number_format($activeStudentsCount) }}</p>
            <p class="text-[12px] text-amber-950/80 font-bold">in period</p>
        </div>

    </div>

    {{-- ── Row 1: Completion Trend + Score Distribution ─────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Lesson Completion Trend (inline SVG line) --}}
        <div class="tl-panel">
            <div class="flex items-start justify-between mb-4 pb-3 border-b border-slate-100">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Trend</p>
                    <h3 class="text-[15px] font-black text-[#0d326b]">Lesson Completion Trend</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Completions over selected period</p>
                </div>
                <span class="material-symbols-outlined text-slate-300 text-[22px]">trending_up</span>
            </div>

            <div class="bg-[#fafcff] rounded-2xl relative" style="padding-bottom:40%">
                <svg viewBox="0 0 {{ $CW }} {{ $CH }}" class="absolute inset-0 w-full h-full"
                     preserveAspectRatio="none" overflow="visible">
                    <defs>
                        <linearGradient id="tlCtFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#0d326b" stop-opacity=".20"/>
                            <stop offset="100%" stop-color="#0d326b" stop-opacity="0"/>
                        </linearGradient>
                        <linearGradient id="tlCtLine" x1="0" y1="0" x2="100%" y2="0">
                            <stop offset="0%" stop-color="#1e4b8f"/>
                            <stop offset="100%" stop-color="#1a6fd4"/>
                        </linearGradient>
                    </defs>
                    @foreach([0,25,50,75,100] as $gv)
                        @php $gy = round($CPT+$cPlotH-($gv/100)*$cPlotH,1); @endphp
                        <line x1="{{ $CPL }}" y1="{{ $gy }}" x2="{{ $CPL+$cPlotW }}" y2="{{ $gy }}"
                              stroke="#e8ecf2" stroke-width="1" stroke-dasharray="4,4"/>
                    @endforeach
                    <path d="{{ $ctArea }}" fill="url(#tlCtFill)"/>
                    <path d="{{ $ctLine }}" fill="none" stroke="url(#tlCtLine)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    @foreach($ctPts as $i => $p)
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}"
                                r="{{ $i===count($ctPts)-1?4.5:3 }}"
                                fill="{{ $i===count($ctPts)-1?'#0d326b':'#1e4b8f' }}"
                                stroke="white" stroke-width="2"/>
                        @if($i % max(1, intval(count($ctPts)/6)) === 0 || $i===count($ctPts)-1)
                        <text x="{{ $p['x'] }}" y="{{ $CH-10 }}"
                              font-size="10" fill="#94a3b8" font-weight="500" text-anchor="middle">{{ $p['label'] }}</text>
                        @endif
                    @endforeach
                </svg>
            </div>
        </div>

        {{-- Quiz Score Distribution (inline SVG bar chart) --}}
        <div class="tl-panel">
            <div class="flex items-start justify-between mb-4 pb-3 border-b border-slate-100">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Distribution</p>
                    <h3 class="text-[15px] font-black text-[#0d326b]">Quiz Score Distribution</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Students by score band</p>
                </div>
                <span class="material-symbols-outlined text-slate-300 text-[22px]">bar_chart</span>
            </div>

            <div class="bg-[#fafcff] rounded-2xl relative" style="padding-bottom:40%">
                <svg viewBox="0 0 {{ $SBW }} {{ $SBH }}" class="absolute inset-0 w-full h-full"
                     preserveAspectRatio="none" overflow="visible">
                    @foreach([0,25,50,75,100] as $gv)
                        @php $gy = round($SBPT+$sbPlotH-($gv/100)*$sbPlotH,1); @endphp
                        <line x1="{{ $SBPL }}" y1="{{ $gy }}" x2="{{ $SBPL+$sbPlotW }}" y2="{{ $gy }}"
                              stroke="#e8ecf2" stroke-width="1" stroke-dasharray="4,4"/>
                    @endforeach
                    @foreach($scoreBuckets as $idx => $bucket)
                    @php
                        $slotW = $sbPlotW / $sbCount;
                        $bx    = round($SBPL + $idx * $slotW + $slotW * 0.1, 1);
                        $bw    = round($slotW * 0.8, 1);
                        $bh    = round(($bucket['count'] / $sbMax) * $sbPlotH, 1);
                        $by    = round($SBPT + $sbPlotH - $bh, 1);
                        $bc    = $sbColors[$idx] ?? '#0d326b';
                    @endphp
                    <rect x="{{ $bx }}" y="{{ $by }}" width="{{ $bw }}" height="{{ max(2,$bh) }}"
                          fill="{{ $bc }}" rx="4" ry="4"/>
                    @if($bucket['count'] > 0)
                    <text x="{{ round($bx+$bw/2,1) }}" y="{{ $by-5 }}"
                          font-size="11" fill="#475569" font-weight="700" text-anchor="middle">{{ $bucket['count'] }}</text>
                    @endif
                    <text x="{{ round($bx+$bw/2,1) }}" y="{{ $SBH-10 }}"
                          font-size="10" fill="#94a3b8" font-weight="500" text-anchor="middle">{{ $bucket['label'] }}</text>
                    @endforeach
                </svg>
            </div>
        </div>
    </div>

    {{-- ── Row 2: Grade Breakdown + Funnel ─────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

        {{-- Grade-level Breakdown --}}
        <div class="tl-panel">
            <div class="flex items-start justify-between mb-4 pb-3 border-b border-slate-100">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">By Grade</p>
                    <h3 class="text-[15px] font-black text-[#0d326b]">Performance by Grade Level</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Avg quiz score &amp; student count per grade</p>
                </div>
                <span class="material-symbols-outlined text-slate-300 text-[22px]">school</span>
            </div>
            @if($gradeLevelBreakdown->isNotEmpty())
            <div class="flex flex-col gap-4">
                @foreach($gradeLevelBreakdown as $row)
                @php $barW = min(100, $row['avg_score']); $bc2 = $row['avg_score']>=75?'#16a34a':($row['avg_score']>=50?'#d97706':'#ef4444'); @endphp
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[13px] font-semibold text-slate-700">{{ $row['grade'] }}</p>
                        <div class="flex items-center gap-3">
                            <span class="text-[11px] text-slate-400">{{ $row['student_count'] }} students</span>
                            <span class="text-[14px] font-black" style="color:{{ $bc2 }}">{{ $row['avg_score'] }}%</span>
                        </div>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all" style="width:{{ $barW }}%;background:{{ $bc2 }}"></div>
                    </div>
                </div>
                @endforeach
            </div>
            @else
            <div class="py-10 text-center">
                <span class="material-symbols-outlined text-[40px]" style="color:#e2e8f0">school</span>
                <p class="text-[13px] text-slate-400 mt-2">No grade-level data available.</p>
            </div>
            @endif
        </div>

        {{-- Completion Funnel (inline SVG donut) --}}
        <div class="tl-panel">
            <div class="flex items-start justify-between mb-4 pb-3 border-b border-slate-100">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Status</p>
                    <h3 class="text-[15px] font-black text-[#0d326b]">Assignment Status Breakdown</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Current lesson assignment states</p>
                </div>
                <span class="material-symbols-outlined text-slate-300 text-[22px]">donut_large</span>
            </div>
            <div class="flex items-center gap-6 flex-wrap">
                {{-- Donut SVG --}}
                <div class="flex-shrink-0">
                    <svg viewBox="0 0 300 220" width="200" height="155">
                        @foreach($fSegments as $seg)
                        @if(!$seg['empty'])
                        <path d="{{ $seg['path'] }}" fill="{{ $seg['color'] }}" stroke="white" stroke-width="2"/>
                        @endif
                        @endforeach
                        {{-- Center label --}}
                        <text x="{{ $fCx }}" y="{{ $fCy - 6 }}" text-anchor="middle"
                              font-size="22" font-weight="900" fill="#0d326b">{{ $fTotal }}</text>
                        <text x="{{ $fCx }}" y="{{ $fCy + 12 }}" text-anchor="middle"
                              font-size="10" font-weight="700" fill="#94a3b8">total</text>
                    </svg>
                </div>
                {{-- Legend --}}
                <div class="flex flex-col gap-2.5 flex-1 min-w-0">
                    @foreach($fSegments as $i => $seg)
                    <div class="flex items-center justify-between gap-2">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full flex-shrink-0" style="background:{{ $seg['color'] }}"></span>
                            <span class="text-[12px] font-semibold text-slate-700">{{ $seg['label'] }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-[13px] font-black text-[#0d326b]">{{ $seg['count'] }}</span>
                            <span class="text-[10px] text-slate-400">({{ $seg['pct'] }}%)</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- ── Per-Class Performance Table ─────────────────────────────────── --}}
    <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
            <div>
                <h3 class="text-[15px] font-black text-[#0d326b]">Class Performance Breakdown</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Comparative metrics per teacher's class</p>
            </div>
            <span class="material-symbols-outlined text-slate-300 text-[20px]">table_chart</span>
        </div>
        @if($classBreakdown->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="bg-slate-50 border-b border-slate-100">
                        <th class="text-left px-6 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Teacher</th>
                        <th class="text-center px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Students</th>
                        <th class="text-center px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Avg Quiz Score</th>
                        <th class="text-center px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Completion Rate</th>
                        <th class="text-center px-4 py-3 text-[11px] font-black uppercase tracking-wider text-slate-400">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($classBreakdown as $cls)
                    @php
                        $sl = $cls['avg_quiz_score']>=75?'On Track':($cls['avg_quiz_score']>=50?'Needs Attention':'Needs Support');
                        $sc = $cls['avg_quiz_score']>=75?'background:#ecfdf5;color:#15803d':($cls['avg_quiz_score']>=50?'background:#fffbeb;color:#b45309':'background:#fef2f2;color:#b91c1c');
                        $qc = $cls['avg_quiz_score']>=75?'#16a34a':($cls['avg_quiz_score']>=50?'#d97706':'#ef4444');
                    @endphp
                    <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-3.5 font-bold text-slate-800">{{ $cls['name'] }}</td>
                        <td class="px-4 py-3.5 text-center text-slate-600">{{ $cls['student_count'] }}</td>
                        <td class="px-4 py-3.5 text-center">
                            <span class="font-black" style="color:{{ $qc }}">{{ $cls['avg_quiz_score'] }}%</span>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <div class="w-20 bg-slate-100 rounded-full h-1.5">
                                    <div class="h-1.5 rounded-full" style="width:{{ $cls['completion_rate'] }}%;background:#0d326b"></div>
                                </div>
                                <span class="text-slate-600 font-medium w-10 text-right">{{ $cls['completion_rate'] }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3.5 text-center">
                            <span class="text-[10px] font-bold px-2.5 py-1 rounded-full" style="{{ $sc }}">{{ $sl }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-6 py-10 text-center">
            <span class="material-symbols-outlined text-[40px]" style="color:#e2e8f0">table_chart</span>
            <p class="text-[13px] text-slate-400 mt-2">No class data available for this period.</p>
        </div>
        @endif
    </div>

    {{-- ── Gesture Mastery ──────────────────────────────────────────────── --}}
    @if($gestureMastery->isNotEmpty())
    <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 p-6">
        <div class="flex items-start justify-between mb-5 pb-3 border-b border-slate-100">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Gestures</p>
                <h3 class="text-[15px] font-black text-[#0d326b]">Gesture Mastery Rates</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Top gestures by mastery across the school</p>
            </div>
            <span class="material-symbols-outlined text-slate-300 text-[22px]">sign_language</span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($gestureMastery as $gesture)
            <div class="flex flex-col gap-1.5 p-3 rounded-2xl" style="background:#f8fafc">
                <div class="flex items-center justify-between">
                    <p class="text-[12px] font-semibold text-slate-700 truncate pr-2">{{ $gesture['name'] }}</p>
                    <span class="text-[13px] font-black flex-shrink-0" style="color:{{ $gesture['color'] }}">{{ $gesture['rate'] }}%</span>
                </div>
                <div class="w-full rounded-full h-1.5" style="background:#e2e8f0">
                    <div class="h-1.5 rounded-full" style="width:{{ $gesture['rate'] }}%;background:{{ $gesture['color'] }}"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>{{-- /skeleton-hide --}}
@endsection


