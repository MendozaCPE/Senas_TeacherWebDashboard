@extends('layouts.admin')
@section('title', 'Analytics')
@section('content')

<style>
/* ── Base Surfaces ── */
.a-card  { background:#fff; border-radius:20px; padding:20px; border:1px solid #f1f5f9; box-shadow:0 1px 3px rgba(13,50,107,.05); transition:transform .2s,box-shadow .2s; }
.a-card:hover  { transform:translateY(-2px); box-shadow:0 10px 28px rgba(13,50,107,.09); }
.a-panel { background:#fff; border-radius:20px; border:1px solid #f1f5f9; box-shadow:0 1px 3px rgba(13,50,107,.05); padding:24px; }
.a-panel:hover { box-shadow:0 6px 24px rgba(13,50,107,.07); }

/* ── Filter pill ── */
.filter-pill { appearance:none; background:#f1f5f9; color:#1e293b; font-size:12px; font-weight:600;
               padding:8px 32px 8px 14px; border-radius:9999px; border:none; outline:none; cursor:pointer; }
.filter-pill:hover { background:#e2e8f0; }

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
.gstat-tab[data-status="critical"].active { background:#ef4444; border-color:#ef4444; color:#fff; }
.gstat-tab[data-status="warning"].active  { background:#f59e0b; border-color:#f59e0b; color:#fff; }
.gstat-tab[data-status="good"].active     { background:#0d326b; border-color:#0d326b; color:#fff; }

/* ── Gesture sign cards (compact grid) ── */
.gsign-card { border-radius:16px; border:1px solid #f1f5f9; background:#fff;
              padding:14px 16px; transition:border-color .15s, box-shadow .15s; }
.gsign-card:hover { border-color:#bfdbfe; box-shadow:0 4px 14px rgba(13,50,107,.07); }
.gsign-card.is-critical { border-left:3px solid #ef4444; }
.gsign-card.is-warning  { border-left:3px solid #f59e0b; }
.gsign-card.is-good     { border-left:3px solid #0d326b; }
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

<div class="flex flex-col gap-5 pt-4">

{{-- ══ 1. FILTER BAR ══════════════════════════════════════════════════════ --}}
<div class="a-panel !py-3 flex items-center gap-3 flex-wrap">
    <span class="material-symbols-outlined text-slate-400 text-[18px]">tune</span>
    <span class="text-[11px] font-black text-slate-400 uppercase tracking-wider">Period</span>
    <form method="GET" action="{{ route('admin.analytics') }}" class="flex items-center gap-2 flex-wrap flex-1">
        <div class="relative">
            <select name="period" class="filter-pill pr-8">
                @foreach(['weekly'=>'Weekly','monthly'=>'Monthly','quarterly'=>'Quarterly','yearly'=>'Yearly'] as $v=>$l)
                <option value="{{ $v }}" {{ $period===$v?'selected':'' }}>{{ $l }}</option>
                @endforeach
            </select>
            <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-[16px] text-slate-400 pointer-events-none">expand_more</span>
        </div>
        @if(in_array($period,['monthly','quarterly','yearly']))
        <div class="relative">
            <select name="year" class="filter-pill pr-8">
                @for($y=date('Y');$y>=2024;$y--)<option value="{{ $y }}" {{ $year==$y?'selected':'' }}>{{ $y }}</option>@endfor
            </select>
            <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-[16px] text-slate-400 pointer-events-none">expand_more</span>
        </div>
        @endif
        @if($period==='monthly')
        <div class="relative">
            <select name="month" class="filter-pill pr-8">
                @foreach(['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'] as $mi=>$mn)
                <option value="{{ $mi+1 }}" {{ $month==$mi+1?'selected':'' }}>{{ $mn }}</option>
                @endforeach
            </select>
            <span class="material-symbols-outlined absolute right-2.5 top-1/2 -translate-y-1/2 text-[16px] text-slate-400 pointer-events-none">expand_more</span>
        </div>
        @endif
        <button type="submit"
            class="px-4 py-2 rounded-full text-[12px] font-bold text-white flex items-center gap-1.5 hover:opacity-90 transition-opacity"
            style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%)">
            <span class="material-symbols-outlined text-[14px]">refresh</span>Apply
        </button>
        <a href="{{ route('admin.analytics') }}"
           class="px-4 py-2 rounded-full border border-slate-200 text-[12px] font-semibold text-slate-500 hover:bg-slate-50 transition-colors">Reset</a>
    </form>
</div>

{{-- ══ 2. KPI CARDS ════════════════════════════════════════════════════════ --}}
<div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
    @php
    $kpis=[
        ['icon'=>'group',        'label'=>'Total Users',          'val'=>number_format($totalUsers),
         'sub'=>$totalTeachers.' teachers · '.$totalStudents.' students',
         'grad'=>'linear-gradient(135deg,#0d326b 0%,#1e4b8f 100%)', 'light'=>true],
        ['icon'=>'monitor_heart','label'=>'Active Students (7d)', 'val'=>number_format($activeStudents),
         'sub'=>($totalStudents>0?round(($activeStudents/$totalStudents)*100):0).'% of total students',
         'grad'=>'linear-gradient(135deg,#1e4b8f 0%,#1a6fd4 100%)', 'light'=>true],
        ['icon'=>'menu_book',    'label'=>'Lessons Completed',    'val'=>number_format($totalLessonsCompleted),
         'sub'=>number_format($totalQuizAttempts).' quiz attempts total',
         'grad'=>'#ffffff', 'light'=>false],
        ['icon'=>'insights',     'label'=>'Avg Quiz Score',       'val'=>round($avgQuizScore,1).'%',
         'sub'=>'Platform-wide average score',
         'grad'=>'#ffffff', 'light'=>false],
    ];
    @endphp
    @foreach($kpis as $kpi)
    <div class="a-card" style="background:{{ $kpi['grad'] }}">
        <div class="flex items-center justify-between mb-4">
            <span class="text-[11px] font-bold uppercase tracking-wider {{ $kpi['light']?'text-white/60':'text-slate-400' }}">{{ $kpi['label'] }}</span>
            <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $kpi['light']?'bg-white/15':'bg-[#dbeafe]' }}">
                <span class="material-symbols-outlined text-[18px] {{ $kpi['light']?'text-white':'text-[#0d326b]' }}">{{ $kpi['icon'] }}</span>
            </div>
        </div>
        <p class="text-[32px] font-black leading-none {{ $kpi['light']?'text-white':'text-[#0d326b]' }}">{{ $kpi['val'] }}</p>
        <p class="text-[11.5px] font-medium mt-1.5 {{ $kpi['light']?'text-white/65':'text-slate-400' }}">{{ $kpi['sub'] }}</p>
    </div>
    @endforeach
</div>

{{-- ══ 3. TREND CHART + GESTURE OVERVIEW ══════════════════════════════════ --}}
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
        @endphp

        {{-- Overall accuracy ring --}}
        <div class="flex items-center gap-4 p-4 rounded-2xl" style="background:linear-gradient(135deg,#0d326b,#1e4b8f)">
            @php
                $r=30; $circ=round(2*M_PI*$r,2);
                $dash=round($gAcc/100*$circ,2);
            @endphp
            <svg width="72" height="72" viewBox="0 0 72 72" class="shrink-0">
                <circle cx="36" cy="36" r="{{ $r }}" fill="none" stroke="rgba(255,255,255,.15)" stroke-width="8"/>
                <circle cx="36" cy="36" r="{{ $r }}" fill="none" stroke="white" stroke-width="8"
                        stroke-dasharray="{{ $dash }} {{ $circ }}"
                        stroke-dashoffset="{{ round($circ/4,2) }}" stroke-linecap="round"/>
                <text x="36" y="41" text-anchor="middle" font-size="14" font-weight="800" fill="white">{{ $gAcc }}%</text>
            </svg>
            <div>
                <p class="text-[22px] font-black text-white leading-none">{{ number_format($gs->total_attempts ?? 0) }}</p>
                <p class="text-[11px] text-white/70 font-semibold mt-0.5">total attempts</p>
                <p class="text-[11px] text-white/60 mt-2">{{ number_format($gs->total_mastered ?? 0) }} signs mastered</p>
            </div>
        </div>

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
        </div>

        {{-- Status summary mini chips --}}
        <div class="flex flex-wrap gap-2 pt-1 border-t border-slate-100">
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-50 border border-red-100 text-red-600 text-[11px] font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>{{ $criticalCount }} critical
            </span>
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 border border-amber-100 text-amber-600 text-[11px] font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block"></span>{{ $warningCount }} warning
            </span>
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 border border-blue-100 text-[#0d326b] text-[11px] font-bold">
                <span class="w-1.5 h-1.5 rounded-full bg-[#0d326b] inline-block"></span>{{ $goodCount }} good
            </span>
        </div>
    </div>

</div>

{{-- ══ 4. GESTURE BREAKDOWN (COMPACT CARD GRID) ═══════════════════════════ --}}
<div class="a-panel !p-0 overflow-hidden">

    {{-- Header --}}
    <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between flex-wrap gap-3">
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Sign Analytics</p>
            <h3 class="text-[15px] font-bold text-[#0d326b]">Gesture Performance Breakdown</h3>
            <p class="text-[12px] text-slate-400 mt-0.5">{{ $gestureBreakdown->count() }} signs tracked — sorted by accuracy (lowest first)</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap text-[11px] font-semibold">
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-red-50 text-red-600">
                <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span>Critical &lt;40%
            </span>
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-amber-50 text-amber-600">
                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block"></span>Warning 40–70%
            </span>
            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-blue-50 text-[#0d326b]">
                <span class="w-1.5 h-1.5 rounded-full bg-[#0d326b] inline-block"></span>Good ≥70%
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
                    <span class="w-1.5 h-1.5 rounded-full bg-red-500 inline-block"></span> Critical
                </button>
                <button type="button" class="gstat-tab" data-status="warning" onclick="adminFilterStatus('warning',this)">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 inline-block"></span> Warning
                </button>
                <button type="button" class="gstat-tab" data-status="good" onclick="adminFilterStatus('good',this)">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#0d326b] inline-block"></span> Good
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
                    'critical' => '#ef4444',
                    'warning'  => '#f59e0b',
                    'good'     => '#0d326b',
                    default    => '#94a3b8',
                };
                $accRingBg = match($g['status']) {
                    'critical' => 'background:linear-gradient(135deg,#fef2f2,#fee2e2)',
                    'warning'  => 'background:linear-gradient(135deg,#fffbeb,#fef3c7)',
                    'good'     => 'background:linear-gradient(135deg,#eff6ff,#dbeafe)',
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
                        <span class="ml-auto inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-[#0d326b] text-white shrink-0">
                            <span class="material-symbols-outlined text-[10px]">verified</span> Good
                        </span>
                    @elseif($g['status'] === 'critical')
                        <span class="ml-auto inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-red-500 text-white shrink-0">
                            <span class="material-symbols-outlined text-[10px]">warning</span> Critical
                        </span>
                    @elseif($g['status'] === 'warning')
                        <span class="ml-auto inline-flex items-center gap-0.5 px-2 py-0.5 rounded-full text-[9.5px] font-bold bg-amber-400 text-white shrink-0">
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

{{-- ══ 5. 2×2 GRID: TEACHER ACTIVITY · MOST COMPLETED · GRADE DIST · HELP REQUEST ══ --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

    {{-- Teacher Activity --}}
    <div class="a-panel !p-0 overflow-hidden">
        <div class="px-6 pt-5 pb-4 border-b border-slate-50">
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Rankings</p>
            <h3 class="text-[15px] font-bold text-[#0d326b]">Teacher Activity</h3>
            <p class="text-[12px] text-slate-400 mt-0.5">Ranked by total lesson completions</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-[13px]">
                <thead>
                    <tr class="border-b border-slate-50 bg-[#f8fafc]">
                        <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400 w-8">#</th>
                        <th class="px-5 py-3 text-left text-[10px] font-black uppercase tracking-wider text-slate-400">Teacher</th>
                        <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-wider text-slate-400">Students</th>
                        <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-wider text-slate-400">Lessons</th>
                        <th class="px-4 py-3 text-center text-[10px] font-black uppercase tracking-wider text-slate-400">Done</th>
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
                        <td class="px-4 py-3 text-center font-black text-[#0d326b] text-[12px]">{{ number_format($t['completions']) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-slate-400 text-[13px]">No teacher data yet</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Most Completed Lessons --}}
    <div class="a-panel">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Content</p>
                <h3 class="text-[14px] font-bold text-[#0d326b]">Most Completed Lessons</h3>
            </div>
            <span class="material-symbols-outlined text-slate-300 text-[20px]">menu_book</span>
        </div>
        <div class="space-y-3">
            @forelse($mostCompletedLessons as $lesson)
            @php $maxC=$mostCompletedLessons->max('completions')?:1; @endphp
            <div>
                <div class="flex items-center justify-between mb-1 text-[12px]">
                    <span class="font-semibold text-slate-700 truncate pr-3">{{ $lesson->title }}</span>
                    <span class="font-black text-[#0d326b] shrink-0">{{ number_format($lesson->completions) }}</span>
                </div>
                <div class="w-full bg-slate-100 rounded-full h-1.5 overflow-hidden">
                    <div class="h-1.5 rounded-full" style="width:{{ round(($lesson->completions/$maxC)*100) }}%; background:linear-gradient(90deg,#0d326b,#1a6fd4)"></div>
                </div>
            </div>
            @empty
            <p class="text-[13px] text-slate-400 text-center py-4">No completion data yet</p>
            @endforelse
        </div>
    </div>

    {{-- Students by Grade Level --}}
    <div class="a-panel">
        <div class="flex items-center justify-between mb-4">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Students</p>
                <h3 class="text-[14px] font-bold text-[#0d326b]">Students by Grade Level</h3>
            </div>
            <span class="material-symbols-outlined text-slate-300 text-[20px]">school</span>
        </div>
        @php $maxG=$gradeDistribution->max('count')?:1; @endphp
        <div class="space-y-2.5">
            @forelse($gradeDistribution as $gd)
            <div class="flex items-center gap-3">
                <span class="text-[11px] font-bold text-slate-500 w-16 shrink-0">{{ $gd->grade_level??'N/A' }}</span>
                <div class="flex-1 bg-slate-100 rounded-full h-2 overflow-hidden">
                    <div class="h-2 rounded-full" style="width:{{ round(($gd->count/$maxG)*100) }}%; background:linear-gradient(90deg,#0d326b,#1a6fd4)"></div>
                </div>
                <span class="text-[12px] font-black text-[#0d326b] w-8 text-right shrink-0">{{ $gd->count }}</span>
            </div>
            @empty
            <p class="text-[13px] text-slate-400 text-center py-4">No grade data</p>
            @endforelse
        </div>
    </div>

    {{-- Help Request Activity --}}
    <div class="a-panel">
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
    </div>

</div>

</div>{{-- /wrapper --}}

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
