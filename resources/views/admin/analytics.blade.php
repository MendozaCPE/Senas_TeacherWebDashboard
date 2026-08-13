@extends('layouts.admin')
@section('title', 'Analytics')
@section('content')

<style>
.stat-card { background:#fff; border-radius:20px; padding:20px; border:1px solid #f1f5f9; box-shadow:0 1px 2px rgba(13,50,107,.04); transition:transform .2s,box-shadow .2s; }
.stat-card:hover { transform:translateY(-2px); box-shadow:0 12px 32px rgba(13,50,107,.10); }
.panel { background:#fff; border-radius:20px; border:1px solid #f1f5f9; box-shadow:0 1px 2px rgba(13,50,107,.04); padding:24px; }
.panel:hover { box-shadow:0 8px 28px rgba(13,50,107,.08); }
.filter-pill { appearance:none; background:#f1f5f9; color:#1e293b; font-size:12px; font-weight:600; padding:8px 32px 8px 14px; border-radius:9999px; border:none; outline:none; cursor:pointer; }
.filter-pill:hover { background:#e2e8f0; }
</style>

@php
$bezier = function(array $pts, float $bot, bool $area=false): string {
    if (empty($pts)) return '';
    $d = "M {$pts[0]['x']},{$pts[0]['y']}";
    for ($i=0; $i<count($pts)-1; $i++) {
        $dx = ($pts[$i+1]['x'] - $pts[$i]['x']) / 2;
        $d .= " C ".($pts[$i]['x']+$dx).",{$pts[$i]['y']} ".($pts[$i+1]['x']-$dx).",{$pts[$i+1]['y']} {$pts[$i+1]['x']},{$pts[$i+1]['y']}";
    }
    if ($area) $d .= " L {$pts[count($pts)-1]['x']},{$bot} L {$pts[0]['x']},{$bot} Z";
    return $d;
};

/* ── Trend chart coords ─────────────────────────────────────────────── */
$TW=600; $TH=240; $TpL=28; $TpR=28; $TpT=16; $TpB=28;
$TpW=$TW-$TpL-$TpR; $TpH=$TH-$TpT-$TpB; $Tbot=$TpT+$TpH;
$Tn=count($trendPoints);
$Tmax=max(1,collect($trendPoints)->max(fn($d)=>max($d['completions'],$d['active_students'])));
$tcPts=[]; $tsPts=[];
foreach($trendPoints as $i=>$d){
    $x=$Tn>1?$TpL+($i/($Tn-1))*$TpW:$TpL+$TpW/2;
    $tcPts[]=['x'=>round($x,2),'y'=>round($TpT+$TpH-($d['completions']/$Tmax)*$TpH,2)];
    $tsPts[]=['x'=>round($x,2),'y'=>round($TpT+$TpH-($d['active_students']/$Tmax)*$TpH,2)];
}
$tcLine=$bezier($tcPts,$Tbot); $tcArea=$bezier($tcPts,$Tbot,true);
$tsLine=$bezier($tsPts,$Tbot); $tsArea=$bezier($tsPts,$Tbot,true);

/* ── Report trend coords ────────────────────────────────────────────── */
$RW=600; $RH=200; $RpL=28; $RpR=28; $RpT=14; $RpB=28;
$RpW=$RW-$RpL-$RpR; $RpH=$RH-$RpT-$RpB; $Rbot=$RpT+$RpH;
$Rn=count($reportTrend);
$Rmax=max(1,collect($reportTrend)->max(fn($d)=>max($d['pending'],$d['resolved'])));
$rPPts=[]; $rRPts=[];
foreach($reportTrend as $i=>$d){
    $x=$Rn>1?$RpL+($i/($Rn-1))*$RpW:$RpL+$RpW/2;
    $rPPts[]=['x'=>round($x,2),'y'=>round($RpT+$RpH-($d['pending']/$Rmax)*$RpH,2)];
    $rRPts[]=['x'=>round($x,2),'y'=>round($RpT+$RpH-($d['resolved']/$Rmax)*$RpH,2)];
}
$rPLine=$bezier($rPPts,$Rbot); $rPArea=$bezier($rPPts,$Rbot,true);
$rRLine=$bezier($rRPts,$Rbot); $rRArea=$bezier($rRPts,$Rbot,true);
@endphp

<div class="flex flex-col gap-4 pt-4">

    {{-- ── FILTER BAR ─────────────────────────────────────────────────── --}}
    <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm px-5 py-3.5 flex items-center gap-3 flex-wrap">
        <span class="material-symbols-outlined text-slate-400 text-[18px]">filter_list</span>
        <p class="text-[12px] font-bold text-slate-500 uppercase tracking-wider">Filters</p>
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
            <button type="submit" class="px-4 py-2 rounded-full text-[12px] font-bold text-white flex items-center gap-1.5 hover:opacity-90 transition-opacity"
                    style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%)">
                <span class="material-symbols-outlined text-[14px]">tune</span>Apply
            </button>
            <a href="{{ route('admin.analytics') }}" class="px-4 py-2 rounded-full border border-slate-200 text-[12px] font-semibold text-slate-600 hover:bg-slate-50 transition-colors">Reset</a>
        </form>
    </div>

    {{-- ── KPI ROW ──────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        @php $kpis=[
            ['icon'=>'group',        'label'=>'Total Users',         'val'=>number_format($totalUsers),            'sub'=>$totalTeachers.' teachers · '.$totalStudents.' students', 'color'=>'#0d326b'],
            ['icon'=>'monitor_heart','label'=>'Active Students (7d)', 'val'=>number_format($activeStudents),        'sub'=>($totalStudents>0?round(($activeStudents/$totalStudents)*100):0).'% of total',    'color'=>'#1e4b8f'],
            ['icon'=>'menu_book',    'label'=>'Lessons Completed',    'val'=>number_format($totalLessonsCompleted), 'sub'=>number_format($totalQuizAttempts).' quiz attempts',          'color'=>'#1a6fd4'],
            ['icon'=>'insights',     'label'=>'Avg Quiz Score',       'val'=>round($avgQuizScore,1).'%',            'sub'=>'Platform-wide average',                                    'color'=>'#3b82f6'],
        ]; @endphp
        @foreach($kpis as $kpi)
        <div class="stat-card">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background:{{ $kpi['color'] }}">
                    <span class="material-symbols-outlined text-white text-[18px]">{{ $kpi['icon'] }}</span>
                </div>
                <p class="text-[13px] font-semibold text-slate-600 leading-snug">{{ $kpi['label'] }}</p>
            </div>
            <p class="text-[28px] font-black text-[#0d326b] leading-none">{{ $kpi['val'] }}</p>
            <p class="text-[11px] text-slate-400 font-medium mt-1.5">{{ $kpi['sub'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- ── TREND CHART + GESTURE STATS ────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">

        <div class="lg:col-span-2 panel">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Trend</p>
                    <h3 class="text-[15px] font-bold text-[#0d326b]">Platform Usage Trend</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">Lesson completions & active students over time</p>
                </div>
                <div class="flex items-center gap-4 text-[11px] font-semibold flex-shrink-0">
                    <span class="flex items-center gap-1.5"><span class="w-8 h-1.5 rounded-full bg-[#0d326b] inline-block"></span>Completions</span>
                    <span class="flex items-center gap-1.5"><span class="w-8 h-1.5 rounded-full bg-[#1a6fd4] inline-block"></span>Active</span>
                </div>
            </div>
            <div class="bg-[#fafcff] rounded-2xl w-full relative" style="padding-bottom:40%">
                <svg viewBox="0 0 {{ $TW }} {{ $TH }}" class="absolute inset-0 w-full h-full" preserveAspectRatio="none" overflow="visible">
                    <defs>
                        <linearGradient id="tCFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#0d326b" stop-opacity=".20"/><stop offset="100%" stop-color="#0d326b" stop-opacity="0"/></linearGradient>
                        <linearGradient id="tSFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#1a6fd4" stop-opacity=".14"/><stop offset="100%" stop-color="#1a6fd4" stop-opacity="0"/></linearGradient>
                        <linearGradient id="tCLine" x1="0" y1="0" x2="100%" y2="0"><stop offset="0%" stop-color="#1e4b8f"/><stop offset="100%" stop-color="#071c3f"/></linearGradient>
                        <linearGradient id="tSLine" x1="0" y1="0" x2="100%" y2="0"><stop offset="0%" stop-color="#3b82f6"/><stop offset="100%" stop-color="#1a6fd4"/></linearGradient>
                    </defs>
                    @foreach([0,25,50,75,100] as $gv)
                        @php $gy=round($TpT+$TpH-($gv/100)*$TpH,1); @endphp
                        <line x1="{{ $TpL }}" y1="{{ $gy }}" x2="{{ $TpL+$TpW }}" y2="{{ $gy }}" stroke="#e8ecf2" stroke-width="1" stroke-dasharray="4,4"/>
                    @endforeach
                    <path d="{{ $tcArea }}" fill="url(#tCFill)"/>
                    <path d="{{ $tsArea }}" fill="url(#tSFill)"/>
                    <path d="{{ $tcLine }}" fill="none" stroke="url(#tCLine)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="{{ $tsLine }}" fill="none" stroke="url(#tSLine)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="5,3"/>
                    @foreach($tcPts as $i=>$p)
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="{{ $i===count($tcPts)-1?4.5:3 }}" fill="{{ $i===count($tcPts)-1?'#0d326b':'#1e4b8f' }}" stroke="white" stroke-width="2"/>
                    @endforeach
                    @foreach($trendPoints as $i=>$d)
                        @if($i%max(1,intval(count($trendPoints)/6))===0||$i===count($trendPoints)-1)
                            <text x="{{ $tcPts[$i]['x'] }}" y="{{ $TH-10 }}" font-size="10" fill="#94a3b8" font-weight="500" text-anchor="middle">{{ $d['label'] }}</text>
                        @endif
                    @endforeach
                </svg>
            </div>
        </div>

        {{-- Gesture stats --}}
        <div class="panel flex flex-col gap-3">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">System-wide</p>
                <h3 class="text-[15px] font-bold text-[#0d326b]">Gesture Performance</h3>
                <p class="text-[12px] text-slate-400 mt-0.5">FSL gesture data across all students</p>
            </div>
            @php
                $gs=$gestureStats;
                $gAcc=($gs&&$gs->total_attempts>0)?round(($gs->total_successful/$gs->total_attempts)*100,1):0;
                $gItems=[
                    ['label'=>'Students Practiced','val'=>$gs->students_who_practiced??0,'icon'=>'group','bg'=>'#dbeafe','fg'=>'#0d326b'],
                    ['label'=>'Total Attempts','val'=>number_format($gs->total_attempts??0),'icon'=>'sports_martial_arts','bg'=>'#f0fdf4','fg'=>'#15803d'],
                    ['label'=>'Gestures Mastered','val'=>number_format($gs->total_mastered??0),'icon'=>'verified','bg'=>'#ecfdf5','fg'=>'#047857'],
                    ['label'=>'Overall Accuracy','val'=>$gAcc.'%','icon'=>'gps_fixed','bg'=>'#eff6ff','fg'=>'#1e3a8a'],
                ];
            @endphp
            <div class="grid grid-cols-2 gap-3">
                @foreach($gItems as $gi)
                <div class="rounded-2xl p-4" style="background:{{ $gi['bg'] }}">
                    <span class="material-symbols-outlined text-[22px]" style="color:{{ $gi['fg'] }}">{{ $gi['icon'] }}</span>
                    <p class="text-[20px] font-black mt-1.5 leading-none" style="color:{{ $gi['fg'] }}">{{ $gi['val'] }}</p>
                    <p class="text-[10px] font-semibold text-slate-500 mt-1">{{ $gi['label'] }}</p>
                </div>
                @endforeach
            </div>
        </div>

    </div>

    {{-- ── TEACHER ACTIVITY + MOST COMPLETED + GRADE DIST ─────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">

        {{-- Teacher Activity Ranking --}}
        <div class="panel overflow-hidden !p-0">
            <div class="px-6 pt-5 pb-4 border-b border-slate-50">
                <h3 class="text-[15px] font-bold text-[#0d326b]">Teacher Activity</h3>
                <p class="text-[12px] text-slate-400 mt-0.5">Ranked by lesson completions</p>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-[13px]">
                    <thead>
                        <tr class="border-b border-slate-50 bg-[#f8fafc]">
                            <th class="px-5 py-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400 w-8">#</th>
                            <th class="px-5 py-3 text-left text-[11px] font-black uppercase tracking-wider text-slate-400">Teacher</th>
                            <th class="px-5 py-3 text-center text-[11px] font-black uppercase tracking-wider text-slate-400">Students</th>
                            <th class="px-5 py-3 text-center text-[11px] font-black uppercase tracking-wider text-slate-400">Lessons</th>
                            <th class="px-5 py-3 text-center text-[11px] font-black uppercase tracking-wider text-slate-400">Done</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @forelse($teacherActivity as $i=>$t)
                        <tr class="hover:bg-[#f8fafc] transition-colors">
                            <td class="px-5 py-3.5">
                                <span class="w-6 h-6 rounded-full text-[11px] font-black flex items-center justify-center
                                    {{ $i===0?'bg-[#facc15] text-[#0d326b]':($i===1?'bg-slate-200 text-slate-600':($i===2?'bg-amber-100 text-amber-700':'bg-slate-100 text-slate-400')) }}">{{ $i+1 }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <p class="font-bold text-slate-800">{{ $t['name'] }}</p>
                                <p class="text-[11px] text-slate-400">{{ $t['email'] }}</p>
                            </td>
                            <td class="px-5 py-3.5 text-center font-semibold text-slate-700">{{ $t['students'] }}</td>
                            <td class="px-5 py-3.5 text-center font-semibold text-slate-700">{{ $t['lessons'] }}</td>
                            <td class="px-5 py-3.5 text-center font-black text-[#0d326b]">{{ number_format($t['completions']) }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="px-5 py-10 text-center text-slate-400 text-[13px]">No teacher data yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="flex flex-col gap-4">
            {{-- Most Completed Lessons --}}
            <div class="panel">
                <h3 class="text-[14px] font-bold text-[#0d326b] mb-4">Most Completed Lessons</h3>
                <div class="space-y-3">
                    @forelse($mostCompletedLessons as $lesson)
                    @php $maxC=$mostCompletedLessons->max('completions')?:1; @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <p class="text-[12px] font-semibold text-slate-700 truncate pr-3">{{ $lesson->title }}</p>
                            <span class="text-[12px] font-black text-[#0d326b] flex-shrink-0">{{ number_format($lesson->completions) }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5">
                            <div class="h-1.5 rounded-full bg-gradient-to-r from-[#1e4b8f] to-[#1a6fd4]" style="width:{{ round(($lesson->completions/$maxC)*100) }}%"></div>
                        </div>
                    </div>
                    @empty
                    <p class="text-[13px] text-slate-400 text-center py-3">No completion data yet</p>
                    @endforelse
                </div>
            </div>

            {{-- Grade Level Distribution --}}
            <div class="panel">
                <h3 class="text-[14px] font-bold text-[#0d326b] mb-4">Students by Grade Level</h3>
                @php $maxG=$gradeDistribution->max('count')?:1; @endphp
                <div class="space-y-2.5">
                    @forelse($gradeDistribution as $gd)
                    <div class="flex items-center gap-3">
                        <span class="text-[11px] font-bold text-slate-500 w-16 flex-shrink-0">{{ $gd->grade_level??'N/A' }}</span>
                        <div class="flex-1 bg-slate-100 rounded-full h-2">
                            <div class="h-2 rounded-full bg-gradient-to-r from-[#0d326b] to-[#1a6fd4]" style="width:{{ round(($gd->count/$maxG)*100) }}%"></div>
                        </div>
                        <span class="text-[12px] font-black text-[#0d326b] w-8 text-right flex-shrink-0">{{ $gd->count }}</span>
                    </div>
                    @empty
                    <p class="text-[13px] text-slate-400 text-center py-3">No grade data</p>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    {{-- ── HELP REQUEST ACTIVITY CHART ─────────────────────────────────── --}}
    <div class="panel">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Activity</p>
                <h3 class="text-[15px] font-bold text-[#0d326b]">Help Request Activity</h3>
                <p class="text-[12px] text-slate-400 mt-0.5">Submitted & resolved reports — last 7 days</p>
            </div>
            <div class="flex items-center gap-4 text-[11px] font-semibold">
                <span class="flex items-center gap-1.5"><span class="w-8 h-1.5 rounded-full bg-amber-400 inline-block"></span>Submitted</span>
                <span class="flex items-center gap-1.5"><span class="w-8 h-1.5 rounded-full bg-[#10b981] inline-block"></span>Resolved</span>
            </div>
        </div>
        <div class="bg-[#fafcff] rounded-2xl w-full relative" style="padding-bottom:33%">
            <svg viewBox="0 0 {{ $RW }} {{ $RH }}" class="absolute inset-0 w-full h-full" preserveAspectRatio="none" overflow="visible">
                <defs>
                    <linearGradient id="rPFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#f59e0b" stop-opacity=".22"/><stop offset="100%" stop-color="#f59e0b" stop-opacity="0"/></linearGradient>
                    <linearGradient id="rRFill" x1="0" y1="0" x2="0" y2="1"><stop offset="0%" stop-color="#10b981" stop-opacity=".18"/><stop offset="100%" stop-color="#10b981" stop-opacity="0"/></linearGradient>
                </defs>
                @foreach([0,25,50,75,100] as $gv)
                    @php $gy2=round($RpT+$RpH-($gv/100)*$RpH,1); @endphp
                    <line x1="{{ $RpL }}" y1="{{ $gy2 }}" x2="{{ $RpL+$RpW }}" y2="{{ $gy2 }}" stroke="#e8ecf2" stroke-width="1" stroke-dasharray="4,4"/>
                @endforeach
                <path d="{{ $rPArea }}" fill="url(#rPFill)"/>
                <path d="{{ $rRArea }}" fill="url(#rRFill)"/>
                <path d="{{ $rPLine }}" fill="none" stroke="#f59e0b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="{{ $rRLine }}" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                @foreach($rPPts as $i=>$p)
                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="4" fill="#f59e0b" stroke="white" stroke-width="2"/>
                @endforeach
                @foreach($rRPts as $i=>$p)
                    <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="4" fill="#10b981" stroke="white" stroke-width="2"/>
                @endforeach
                @foreach($reportTrend as $i=>$d)
                    <text x="{{ $rPPts[$i]['x'] }}" y="{{ $RH-10 }}" font-size="10" fill="#94a3b8" font-weight="500" text-anchor="middle">{{ $d['label'] }}</text>
                @endforeach
            </svg>
        </div>
    </div>

</div>
@endsection
