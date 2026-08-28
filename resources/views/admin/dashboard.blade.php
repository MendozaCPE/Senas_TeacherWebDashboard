@extends('layouts.admin')
@section('title', 'Admin Dashboard')
@section('content')

@php
/* ── Inline sparkline helper ───────────────────────────────────────── */
$spark = function (array $data, string $color): string {
    if (count($data) < 2) $data = array_fill(0, 7, $data[0] ?? 0);
    $max = max($data); $min = min($data); $range = max($max - $min, 1); $n = count($data);
    $pts = [];
    foreach ($data as $i => $v) {
        $x = $n > 1 ? ($i / ($n - 1)) * 240 : 120;
        $y = 38 - 6 - (($v - $min) / $range) * 26;
        $pts[] = round($x,1).','.round($y,1);
    }
    return '<svg viewBox="0 0 240 38" class="w-full" style="height:38px" preserveAspectRatio="none">'
        .'<polyline fill="none" stroke="'.e($color).'" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="'.implode(' ',$pts).'"/>'
        .'</svg>';
};

/* ── Smooth bezier path builder ────────────────────────────────────── */
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

/* ── Chart coords ───────────────────────────────────────────────────── */
$W = 600; $H = 240; $pL = 28; $pR = 28; $pT = 16; $pB = 28;
$plotW = $W - $pL - $pR; $plotH = $H - $pT - $pB; $bot = $pT + $plotH;
$n = count($activityTrend);
$peak = max(1, collect($activityTrend)->max(fn($d) => max($d['completions'], $d['students'])));

$cPts = []; $sPts = [];
foreach ($activityTrend as $i => $d) {
    $x = $n > 1 ? $pL + ($i / ($n - 1)) * $plotW : $pL + $plotW / 2;
    $cPts[] = ['x' => round($x,2), 'y' => round($pT + $plotH - ($d['completions'] / $peak) * $plotH, 2)];
    $sPts[] = ['x' => round($x,2), 'y' => round($pT + $plotH - ($d['students']    / $peak) * $plotH, 2)];
}
$cLine = $bezier($cPts, $bot); $cArea = $bezier($cPts, $bot, true);
$sLine = $bezier($sPts, $bot); $sArea = $bezier($sPts, $bot, true);
@endphp

<div class="flex flex-col gap-5 w-full pt-4">

    {{-- ── WELCOME BANNER ──────────────────────────────────────────────── --}}
    <div class="rounded-[28px] relative overflow-hidden flex items-center"
         style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%);min-height:130px">
        <div class="absolute top-0 right-44 w-44 h-44 rounded-full opacity-10 bg-white"></div>
        <div class="absolute -bottom-8 left-1/3 w-32 h-32 rounded-full opacity-10 bg-white"></div>
        <div class="relative z-10 px-10 py-7 flex-1">
    <h2 class="text-[24px] font-black text-white leading-tight mb-1">System Overview</h2>
    <p class="text-[13px] text-white/70 font-medium">Welcome back, <span class="text-white font-bold">{{ Auth::user()->name }}</span>. Here's a live summary of the SEÑAS platform.</p>

    <a href="{{ route('admin.testing.alphabet') }}"
       class="inline-flex items-center gap-1.5 mt-3 px-4 py-2 rounded-full bg-white/15 hover:bg-white/25 border border-white/30 text-white text-[12px] font-bold transition-colors">
        <span class="material-symbols-outlined text-[16px]">science</span>
        Test Environment
    </a>
</div>
        <div class="relative z-10 flex-shrink-0 pr-10 hidden lg:flex items-center gap-8">
            @foreach([['label'=>'Teachers','val'=>$totalTeachers],['label'=>'Students','val'=>$totalStudents],['label'=>'Lessons','val'=>$totalLessons]] as $bi)
            @if(!$loop->first)<div class="w-px h-10 bg-white/20"></div>@endif
            <div class="text-center">
                <p class="text-[30px] font-black text-white leading-none">{{ $bi['val'] }}</p>
                <p class="text-[10px] font-semibold text-white/60 uppercase tracking-wider mt-0.5">{{ $bi['label'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── KPI CARDS ────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
        @php
            $kpiCards = [
                ['icon'=>'school',    'label'=>'Total Teachers',    'val'=>$totalTeachers,                    'sub'=>'↑ '.$newTeachersWeek.' this week',        'color'=>'#0d326b', 'spark'=>$sparkTeachers,  'sc'=>'#0d326b'],
                ['icon'=>'group',     'label'=>'Total Students',    'val'=>$totalStudents,                    'sub'=>'↑ '.$newStudentsWeek.' this week',         'color'=>'#1e4b8f', 'spark'=>$sparkStudents,  'sc'=>'#1e4b8f'],
                ['icon'=>'menu_book', 'label'=>'Lessons Completed', 'val'=>number_format($totalLessonsCompleted), 'sub'=>$publishedLessons.' published lessons', 'color'=>'#1a6fd4', 'spark'=>$sparkLessons,   'sc'=>'#1a6fd4'],
                ['icon'=>'inbox',     'label'=>'Pending Reports',   'val'=>$pendingReports,                   'sub'=>$resolvedReports.' resolved of '.$totalReports.' total', 'color'=>$pendingReports>0?'#ef4444':'#3b82f6', 'spark'=>null, 'sc'=>null],
            ];
        @endphp
        @foreach($kpiCards as $kc)
        <div class="bg-white rounded-[22px] px-5 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-9 h-9 rounded-full flex items-center justify-center flex-shrink-0" style="background:{{ $kc['color'] }}">
                    <span class="material-symbols-outlined text-white text-[18px]">{{ $kc['icon'] }}</span>
                </div>
                <p class="text-[13px] font-semibold text-slate-600 leading-snug">{{ $kc['label'] }}</p>
            </div>
            <p class="text-[30px] font-black text-[#0d326b] leading-none tracking-tight">{{ $kc['val'] }}</p>
            <p class="text-[12px] font-medium text-slate-400 mt-1.5">{{ $kc['sub'] }}</p>
            @if($kc['spark'])
            <div class="mt-3 -mx-1">{!! $spark($kc['spark'] ?: array_fill(0,7,0), $kc['sc']) !!}</div>
            @else
            <div class="mt-3">
                <a href="{{ route('admin.reports') }}" class="text-[12px] font-bold text-[#0d326b] hover:underline flex items-center gap-1">
                    View all reports <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                </a>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    {{-- ── MAIN GRID: chart (2/3) + side stats (1/3) ─────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 items-start">

        {{-- Chart card --}}
        <div class="lg:col-span-2 bg-white rounded-[22px] shadow-sm border border-slate-100 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-0.5">Trend</p>
                    <h3 class="text-[16px] font-bold text-[#0d326b]">Platform Activity</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">Lesson completions & active students — last 14 days</p>
                </div>
                <div class="flex items-center gap-4 text-[11px] font-semibold flex-shrink-0">
                    <span class="flex items-center gap-1.5"><span class="w-8 h-1.5 rounded-full bg-[#0d326b] inline-block"></span>Completions</span>
                    <span class="flex items-center gap-1.5"><span class="w-8 h-1.5 rounded-full bg-[#1a6fd4] inline-block" style="background:repeating-linear-gradient(90deg,#1a6fd4 0,#1a6fd4 4px,transparent 4px,transparent 7px)"></span>Active Students</span>
                </div>
            </div>

            <div class="bg-[#fafcff] rounded-2xl w-full relative" style="padding-bottom:40%">
                <svg viewBox="0 0 {{ $W }} {{ $H }}" class="absolute inset-0 w-full h-full" preserveAspectRatio="none" overflow="visible">
                    <defs>
                        <linearGradient id="gCFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#0d326b" stop-opacity=".20"/>
                            <stop offset="100%" stop-color="#0d326b" stop-opacity="0"/>
                        </linearGradient>
                        <linearGradient id="gSFill" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%" stop-color="#1a6fd4" stop-opacity=".14"/>
                            <stop offset="100%" stop-color="#1a6fd4" stop-opacity="0"/>
                        </linearGradient>
                        <linearGradient id="gCLine" x1="0" y1="0" x2="100%" y2="0">
                            <stop offset="0%" stop-color="#1e4b8f"/>
                            <stop offset="100%" stop-color="#071c3f"/>
                        </linearGradient>
                        <linearGradient id="gSLine" x1="0" y1="0" x2="100%" y2="0">
                            <stop offset="0%" stop-color="#3b82f6"/>
                            <stop offset="100%" stop-color="#1a6fd4"/>
                        </linearGradient>
                    </defs>

                    {{-- Grid lines --}}
                    @foreach([0,25,50,75,100] as $gv)
                        @php $gy = round($pT + $plotH - ($gv/100)*$plotH, 1); @endphp
                        <line x1="{{ $pL }}" y1="{{ $gy }}" x2="{{ $pL+$plotW }}" y2="{{ $gy }}" stroke="#e8ecf2" stroke-width="1" stroke-dasharray="4,4"/>
                    @endforeach

                    {{-- Area fills --}}
                    <path d="{{ $cArea }}" fill="url(#gCFill)"/>
                    <path d="{{ $sArea }}" fill="url(#gSFill)"/>

                    {{-- Lines --}}
                    <path d="{{ $cLine }}" fill="none" stroke="url(#gCLine)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="{{ $sLine }}" fill="none" stroke="url(#gSLine)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" stroke-dasharray="5,3"/>

                    {{-- Dots --}}
                    @foreach($cPts as $i => $p)
                        <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}"
                                r="{{ $i===count($cPts)-1 ? 4.5 : 3 }}"
                                fill="{{ $i===count($cPts)-1 ? '#0d326b' : '#1e4b8f' }}"
                                stroke="white" stroke-width="2"/>
                    @endforeach

                    {{-- X labels --}}
                    @foreach($activityTrend as $i => $d)
                        @if($i % 2 === 0 || $i === count($activityTrend)-1)
                            <text x="{{ $cPts[$i]['x'] }}" y="{{ $H - 10 }}"
                                  font-size="10" fill="#94a3b8" font-weight="500" text-anchor="middle">{{ $d['label'] }}</text>
                        @endif
                    @endforeach
                </svg>
            </div>
        </div>

        {{-- Side stats --}}
        <div class="flex flex-col gap-4">

            {{-- Active this week --}}
            <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 p-5">
                <h4 class="text-[13px] font-black text-[#0d326b] mb-4">Active This Week</h4>
                @foreach([['icon'=>'school','label'=>'Teachers','val'=>$activeTeachers,'total'=>$totalTeachers,'color'=>'#0d326b'],['icon'=>'group','label'=>'Students','val'=>$activeStudents,'total'=>$totalStudents,'color'=>'#1e4b8f']] as $row)
                <div class="{{ $loop->first ? 'mb-4' : '' }}">
                    <div class="flex items-center justify-between mb-1.5">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-full bg-[#dbeafe] flex items-center justify-center">
                                <span class="material-symbols-outlined text-[#0d326b] text-[14px]">{{ $row['icon'] }}</span>
                            </div>
                            <span class="text-[13px] font-semibold text-slate-700">{{ $row['label'] }}</span>
                        </div>
                        <span class="text-[16px] font-black text-[#0d326b]">{{ $row['val'] }}</span>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all" style="background:{{ $row['color'] }};width:{{ $row['total']>0?round(($row['val']/$row['total'])*100):0 }}%"></div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- Platform totals --}}
            <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 p-5">
                <h4 class="text-[13px] font-black text-[#0d326b] mb-4">Platform Totals</h4>
                <div class="grid grid-cols-2 gap-3">
                    @php $qs = [
                        ['icon'=>'quiz',          'label'=>'Quiz Attempts', 'val'=>number_format($totalQuizAttempts)],
                        ['icon'=>'view_module',   'label'=>'Modules',      'val'=>number_format($totalModules)],
                        ['icon'=>'check_circle',  'label'=>'Resolved',     'val'=>number_format($resolvedReports)],
                        ['icon'=>'verified_user', 'label'=>'Admins',       'val'=>\App\Models\User::where('role','admin')->count()],
                    ]; @endphp
                    @foreach($qs as $q)
                    <div class="bg-[#f8fafc] rounded-2xl p-3 text-center">
                        <span class="material-symbols-outlined text-[#0d326b] text-[20px]">{{ $q['icon'] }}</span>
                        <p class="text-[18px] font-black text-[#0d326b] leading-none mt-1">{{ $q['val'] }}</p>
                        <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-wider mt-0.5">{{ $q['label'] }}</p>
                    </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>

    {{-- ── BOTTOM GRID ─────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Top Teachers --}}
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-[15px] font-black text-[#0d326b]">Top Teachers</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">By student count</p>
                </div>
                <a href="{{ route('admin.accounts') }}" class="text-[11px] font-black uppercase tracking-wider text-[#0d326b] hover:underline">View All</a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($topTeachers as $i => $t)
                <div class="flex items-center gap-3 px-6 py-3.5">
                    <div class="w-7 h-7 rounded-full text-[11px] font-black flex items-center justify-center flex-shrink-0
                        {{ $i===0?'bg-[#facc15] text-[#0d326b]':($i===1?'bg-slate-200 text-slate-600':($i===2?'bg-amber-100 text-amber-700':'bg-slate-100 text-slate-400')) }}">{{ $i+1 }}</div>
                    <img src="{{ $t->user->avatarUrl() }}" class="w-8 h-8 rounded-full object-cover border border-slate-100 flex-shrink-0"
                         onerror="this.src='https://ui-avatars.com/api/?name={{ urlencode($t->first_name.'+'. $t->last_name) }}&background=0d326b&color=fff&size=64&bold=true&rounded=true'">
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold text-slate-800 truncate">{{ trim($t->first_name.' '.$t->last_name) }}</p>
                        <p class="text-[11px] text-slate-400">{{ $t->students_count }} student{{ $t->students_count==1?'':'s' }}</p>
                    </div>
                </div>
                @empty
                <div class="px-6 py-8 text-center">
                    <span class="material-symbols-outlined text-slate-200 text-[36px]">school</span>
                    <p class="text-[13px] text-slate-400 mt-2">No teachers yet</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Recent Reports --}}
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-slate-50 flex items-center justify-between">
                <div>
                    <h3 class="text-[15px] font-black text-[#0d326b]">Recent Reports</h3>
                    <p class="text-[11px] text-slate-400 mt-0.5">Help requests from students</p>
                </div>
                <a href="{{ route('admin.reports') }}" class="text-[11px] font-black uppercase tracking-wider text-[#0d326b] hover:underline">View All</a>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($recentReports as $report)
                @php
                    $sm = ['pending'=>['bg'=>'bg-amber-100','text'=>'text-amber-700','label'=>'Pending'],'in_progress'=>['bg'=>'bg-blue-100','text'=>'text-blue-700','label'=>'In Progress'],'responded'=>['bg'=>'bg-purple-100','text'=>'text-purple-700','label'=>'Responded'],'resolved'=>['bg'=>'bg-emerald-100','text'=>'text-emerald-700','label'=>'Resolved']];
                    $sc = $sm[$report->status] ?? $sm['pending'];
                @endphp
                <div class="flex items-start gap-3 px-6 py-3.5">
                    <div class="w-8 h-8 rounded-full bg-[#0d326b] flex items-center justify-center text-white text-[11px] font-black flex-shrink-0">
                        {{ strtoupper(substr($report->student->first_name??'U',0,1).substr($report->student->last_name??'?',0,1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-[13px] font-bold text-slate-800 truncate">{{ trim(($report->student->first_name??'').(' ').($report->student->last_name??'Unknown')) }}</p>
                        <p class="text-[11px] text-slate-400 truncate mt-0.5">{{ Str::limit($report->message,50) }}</p>
                    </div>
                    <span class="flex-shrink-0 text-[10px] font-bold px-2 py-0.5 rounded-full {{ $sc['bg'] }} {{ $sc['text'] }}">{{ $sc['label'] }}</span>
                </div>
                @empty
                <div class="px-6 py-8 text-center">
                    <span class="material-symbols-outlined text-slate-200 text-[36px]">inbox</span>
                    <p class="text-[13px] text-slate-400 mt-2">No reports yet</p>
                </div>
                @endforelse
            </div>
        </div>

        {{-- Schools --}}
        <div class="bg-white rounded-[22px] shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 pt-5 pb-4 border-b border-slate-50">
                <h3 class="text-[15px] font-black text-[#0d326b]">Schools</h3>
                <p class="text-[11px] text-slate-400 mt-0.5">Teachers by school</p>
            </div>
            <div class="p-6 space-y-4">
                @forelse($schoolStats as $school)
                @php $maxT = $schoolStats->max('teacher_count') ?: 1; @endphp
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <p class="text-[12px] font-semibold text-slate-700 truncate pr-2">{{ $school->name }}</p>
                        <p class="text-[12px] font-black text-[#0d326b] flex-shrink-0">{{ $school->teacher_count }}</p>
                    </div>
                    <div class="w-full bg-slate-100 rounded-full h-2">
                        <div class="h-2 rounded-full bg-gradient-to-r from-[#0d326b] to-[#1a6fd4]"
                             style="width:{{ round(($school->teacher_count/$maxT)*100) }}%"></div>
                    </div>
                </div>
                @empty
                <div class="py-4 text-center">
                    <span class="material-symbols-outlined text-slate-200 text-[36px]">domain</span>
                    <p class="text-[13px] text-slate-400 mt-2">No schools found</p>
                </div>
                @endforelse
            </div>
        </div>

    </div>

</div>
@endsection
