@extends('layouts.app')
@section('bg-class', 'bg-[#f4f7f9]')
@section('title', 'Analytics')
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
    --analytics-slate: #64748b;
}

/* ── Stat cards ───────────────────────────────────────────── */
.stat-card { border-radius: 20px; padding: 20px; position: relative; overflow: hidden; transition: transform .2s, box-shadow .2s; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(13,50,107,.12); }

/* ── Panels ───────────────────────────────────── */
.panel { background: #fff; border-radius: 20px; border: 1px solid #f1f5f9; box-shadow: 0 1px 2px rgba(13,50,107,.04); padding: 24px; }
.panel:hover { box-shadow: 0 12px 32px rgba(13,50,107,.08); }

/* ── Filter styles (matching Reports page) ──────────────────────── */
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

/* ── Chart tooltip ──────────────────────────────────────────────────── */
.chart-tooltip {
    pointer-events: none;
    position: absolute;
    z-index: 20;
    opacity: 0;
    transition: opacity .15s;
    background: #0d326b;
    color: #fff;
    font-size: 11px;
    font-weight: 700;
    padding: 6px 14px;
    border-radius: 10px;
    box-shadow: 0 4px 16px rgba(13,50,107,.25);
    white-space: nowrap;
    transform: translateX(-50%) translateY(-100%);
}
.chart-tooltip::after {
    content: '';
    position: absolute;
    left: 50%;
    bottom: -6px;
    transform: translateX(-50%);
    width: 10px;
    height: 10px;
    background: #0d326b;
    border-radius: 2px;
    transform: translateX(-50%) rotate(45deg);
}
.chart-tooltip.visible { opacity: 1; }

/* ── Chart wrapper ──────────────────────────────────────────────────── */
.chart-wrapper {
    position: relative;
    overflow: hidden;
    border-radius: 16px;
    background: #fafcff;
    padding: 12px 8px 4px 8px;
    width: 100%;
}

.chart-svg-container {
    width: 100%;
    height: 300px;
}

.chart-svg-container svg {
    width: 100%;
    height: 100%;
}

/* ── Difficulty list with scroll - shows 5 items, scroll for more ── */
.difficulty-list {
    max-height: 260px;
    overflow-y: auto;
    padding-right: 4px;
}
.difficulty-list::-webkit-scrollbar {
    width: 4px;
}
.difficulty-list::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}
.difficulty-list::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.difficulty-list::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.difficulty-item {
    padding: 10px 0;
    border-bottom: 1px solid #f8fafc;
}
.difficulty-item:last-child {
    border-bottom: none;
}
</style>

<div class="space-y-6">

    {{-- ══════════ FILTER + EXPORT (matching Reports page) ══════════ --}}
    @php $af = session('analytics_filters', []); @endphp
    <form method="POST" action="{{ route('analytics.filter') }}" id="filterForm">
        @csrf
        <div class="filter-container">
            <div class="filter-group">
                <div class="filter-wrap">
                    <select name="period" class="filter-select">
                        <option value="weekly" {{ ($af['period'] ?? 'weekly') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                        <option value="monthly" {{ ($af['period'] ?? '') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ ($af['period'] ?? '') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="yearly" {{ ($af['period'] ?? '') === 'yearly' ? 'selected' : '' }}>Yearly</option>
                    </select>
                    <span class="material-symbols-outlined">expand_more</span>
                </div>

                <div class="filter-wrap">
                    <select name="year" class="filter-select">
                        @php $currentYear = date('Y'); @endphp
                        @for($y = $currentYear; $y >= $currentYear - 5; $y--)
                        <option value="{{ $y }}" {{ ($af['year'] ?? $currentYear) == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                    <span class="material-symbols-outlined">expand_more</span>
                </div>

                @if(($af['period'] ?? '') === 'monthly' || ($af['period'] ?? '') === 'quarterly')
                <div class="filter-wrap">
                    <select name="month" class="filter-select">
                        @foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m => $name)
                        <option value="{{ $m + 1 }}" {{ ($af['month'] ?? date('n')) == ($m + 1) ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                    <span class="material-symbols-outlined">expand_more</span>
                </div>
                @endif

                <a href="{{ route('analytics') }}" class="filter-reset">Clear</a>
                <button type="submit" class="filter-btn">
                    <span class="material-symbols-outlined text-[16px]">filter_alt</span>
                    Apply Filter
                </button>
            </div>

            <a href="{{ route('analytics.export-pdf', request()->query()) }}" class="export-btn">
                <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                Export PDF
            </a>
        </div>
    </form>

    {{-- ══════════ STAT CARDS ══════════ --}}
    @php
        $statShades = [
            ['bg' => 'linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%)', 'text' => 'white',  'iconBg' => 'bg-white/10', 'iconColor' => 'text-white'],
            ['bg' => '#ffffff', 'text' => 'navy', 'iconBg' => 'bg-[#e8eef8]', 'iconColor' => 'text-[#1e4b8f]'],
            ['bg' => '#ffffff', 'text' => 'navy', 'iconBg' => 'bg-[#e8eef8]', 'iconColor' => 'text-[#1e4b8f]'],
            ['bg' => '#ffffff', 'text' => 'navy', 'iconBg' => 'bg-[#e8eef8]', 'iconColor' => 'text-[#1e4b8f]'],
        ];
    @endphp
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
        @foreach($classSummary as $i => $stat)
            @php $shade = $statShades[$i % count($statShades)]; @endphp
            <div class="stat-card {{ $shade['text'] === 'white' ? 'text-white' : 'bg-white border border-slate-100 shadow-sm' }}"
                 style="{{ $shade['text'] === 'white' ? 'background:' . $shade['bg'] : '' }}">
                @if($shade['text'] === 'white')
                    <div class="absolute -top-7 -right-7 w-28 h-28 bg-white/5 rounded-full"></div>
                    <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-white/5 rounded-full"></div>
                @endif
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center {{ $shade['iconBg'] }}">
                            <span class="material-symbols-outlined text-[19px] {{ $shade['iconColor'] }}">{{ $stat['icon'] }}</span>
                        </div>
                        <p class="text-[10px] font-bold uppercase tracking-widest {{ $shade['text'] === 'white' ? 'text-white/50' : 'text-slate-400' }}">{{ $stat['title'] }}</p>
                    </div>
                    <p class="text-[32px] font-black leading-none mb-2 {{ $shade['text'] === 'white' ? 'text-white' : 'text-[#0d326b]' }}">{{ $stat['value'] }}</p>
                    <p class="text-[12px] {{ $shade['text'] === 'white' ? 'text-white/60' : 'text-slate-400' }}">{{ $stat['detail'] }}</p>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ══════════ ROW 2: Progress line chart + Module difficulty ══════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

        {{-- Class Progress Over Time — smooth line chart with tooltips --}}
        <div class="panel">
            <div class="flex items-start justify-between gap-3 mb-3">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Trend</p>
                    <h3 class="text-[16px] font-bold text-[#0d326b]">Class Progress Over Time</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">
                        {{ ucfirst(request('period', 'weekly')) }} average quiz score
                        @if(request('period') === 'monthly' || request('period') === 'quarterly')
                            for {{ date('F', mktime(0,0,0, request('month', date('n')), 1)) }}
                        @endif
                        {{ request('year', date('Y')) }}
                    </p>
                </div>
                <span class="text-[11px] font-bold text-slate-400 bg-[#f1f5f9] px-3 py-1.5 rounded-full shrink-0">
                    {{ count($progressOverTime) }} {{ request('period', 'weekly') }}
                </span>
            </div>

            @php
                // Fixed dimensions for consistent rendering
                $chartW = 600;
                $chartH = 200;
                $padL = 32;
                $padR = 8;
                $padT = 24;
                $padB = 28;
                $plotW = $chartW - $padL - $padR;
                $plotH = $chartH - $padT - $padB;
                $count = count($progressOverTime);
                $maxVal = 100;
                $minVal = 0;

                $pts = [];
                foreach ($progressOverTime as $index => $week) {
                    $x = $count > 1 ? $padL + ($index / ($count - 1)) * $plotW : $padL + $plotW / 2;
                    $y = $padT + $plotH - (($week['value'] - $minVal) / max($maxVal - $minVal, 1)) * $plotH;
                    $pts[] = ['x' => round($x, 2), 'y' => round($y, 2), 'value' => $week['value'], 'label' => $week['label']];
                }

                // Build smooth curve path
                $curvePath = '';
                $areaPath = '';
                if (count($pts) > 0) {
                    $curvePath = "M {$pts[0]['x']},{$pts[0]['y']}";
                    for ($i = 0; $i < count($pts) - 1; $i++) {
                        $p0 = $pts[$i];
                        $p1 = $pts[$i + 1];
                        $dx = ($p1['x'] - $p0['x']) / 2;
                        $c1x = $p0['x'] + $dx; $c1y = $p0['y'];
                        $c2x = $p1['x'] - $dx; $c2y = $p1['y'];
                        $curvePath .= " C {$c1x},{$c1y} {$c2x},{$c2y} {$p1['x']},{$p1['y']}";
                    }
                    $areaPath = $curvePath . " L " . ($padL + $plotW) . "," . ($padT + $plotH) . " L " . $padL . "," . ($padT + $plotH) . " Z";
                }
            @endphp

            <div class="chart-wrapper">
                <div id="lineChartTooltip" class="chart-tooltip">
                    <span id="lineTooltipLabel"></span>: <span id="lineTooltipValue"></span>%
                </div>

                @if(empty($pts))
                    <p class="text-slate-400 text-[13px] text-center py-12">No progress data available yet.</p>
                @else
                    <div class="chart-svg-container">
                        <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" preserveAspectRatio="xMidYMid meet">
                            <defs>
                                <linearGradient id="progressFill" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0%" stop-color="#1a6fd4" stop-opacity="0.20" />
                                    <stop offset="100%" stop-color="#1a6fd4" stop-opacity="0" />
                                </linearGradient>
                                <linearGradient id="progressLine" x1="0" y1="0" x2="100%" y2="0">
                                    <stop offset="0%" stop-color="#1e4b8f" />
                                    <stop offset="100%" stop-color="#0d326b" />
                                </linearGradient>
                            </defs>

                            <!-- Gridlines + Y labels -->
                            @foreach([0, 25, 50, 75, 100] as $gv)
                            @php $gy = round($padT + $plotH - ($gv / 100) * $plotH, 1); @endphp
                            <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $padL + $plotW }}" y2="{{ $gy }}" stroke="#e8ecf0" stroke-width="0.8" stroke-dasharray="4,4"/>
                            <text x="2" y="{{ $gy + 4 }}" font-size="9" fill="#94a3b8" font-weight="600">{{ $gv }}%</text>
                            @endforeach

                            <!-- Area fill -->
                            <path d="{{ $areaPath }}" fill="url(#progressFill)"/>

                            <!-- Line (smooth curve) -->
                            <path d="{{ $curvePath }}" fill="none" stroke="url(#progressLine)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>

                            <!-- Points with hover hit areas -->
                            @foreach($pts as $i => $p)
                            <circle cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="{{ $i === count($pts) - 1 ? 5 : 4 }}" fill="{{ $i === count($pts) - 1 ? '#1a6fd4' : '#0d326b' }}" stroke="white" stroke-width="2.5"/>

                            <!-- Invisible larger hit area for tooltip -->
                            <circle class="line-point-hit" cx="{{ $p['x'] }}" cy="{{ $p['y'] }}" r="14" fill="transparent" style="cursor:pointer"
                                    data-label="{{ $p['label'] }}" data-value="{{ $p['value'] }}"></circle>
                            @endforeach

                            <!-- X labels -->
                            @php
                                $labelStep = max(1, floor(count($pts) / 10));
                            @endphp
                            @foreach($pts as $i => $p)
                                @if($i % $labelStep === 0 || $i === count($pts) - 1)
                                <text x="{{ $p['x'] }}" y="{{ $chartH - 4 }}" font-size="9" fill="#94a3b8" font-weight="500" text-anchor="middle">{{ $p['label'] }}</text>
                                @endif
                            @endforeach
                        </svg>
                    </div>

                    <!-- Current value bubble -->
                    @if(!empty($pts))
                    <div class="absolute top-2 right-2 bg-white border border-slate-100 shadow-sm rounded-full px-3 py-0.5">
                        <span class="text-[12px] font-black text-[#0d326b]">{{ end($pts)['value'] }}%</span>
                    </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Module Difficulty Ranking — navy gradient bars with scroll (shows 5, scroll for more) --}}
        <div class="panel">
            <div class="flex items-start justify-between gap-3 mb-4">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Focus areas</p>
                    <h3 class="text-[16px] font-bold text-[#0d326b]">Module Difficulty Ranking</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">Lessons ordered lowest to highest average score</p>
                </div>
                <span class="text-[11px] font-bold text-slate-400 bg-[#f1f5f9] px-3 py-1.5 rounded-full shrink-0">Hardest first</span>
            </div>

            @if($lessonDifficulty->isEmpty())
                <p class="text-slate-400 text-[13px] text-center py-16">No lesson score data available yet.</p>
            @else
                <div class="difficulty-list">
                    @foreach($lessonDifficulty as $lesson)
                        <div class="difficulty-item">
                            <div class="flex items-center justify-between gap-3">
                                <span class="text-[12.5px] font-semibold text-[#0d326b] truncate">{{ $lesson['title'] }}</span>
                                <span class="text-[11px] font-bold text-slate-400 shrink-0">{{ $lesson['avg_score'] }}%</span>
                            </div>
                            <div class="h-2 rounded-full bg-[#eef2f7] overflow-hidden mt-1.5">
                                <div class="h-full rounded-full" style="width: {{ max(4, $lesson['avg_score']) }}%; background: linear-gradient(90deg, #93c5fd, #1e4b8f);"></div>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Show count if there are many --}}
                @if($lessonDifficulty->count() > 5)
                <div class="text-center text-[10px] text-slate-400 font-medium mt-3 pt-2 border-t border-slate-100">
                    Showing {{ $lessonDifficulty->count() }} lessons · Scroll for more
                </div>
                @endif
            @endif
        </div>
    </div>

    {{-- ══════════ ROW 3: Student Ranking + sidebar charts ══════════ --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

        {{-- Student Ranking — leaderboard by average quiz score --}}
        <div class="xl:col-span-2 panel">
            <div class="flex items-start justify-between gap-3 mb-6">
                <div>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Leaderboard</p>
                    <h3 class="text-[16px] font-bold text-[#0d326b]">Student Ranking</h3>
                    <p class="text-[12px] text-slate-400 mt-0.5">Ranked by average quiz score across all attempts</p>
                </div>
                <span class="text-[11px] font-bold text-slate-400 bg-[#f1f5f9] px-3 py-1.5 rounded-full shrink-0">{{ $studentRanking->count() ?? 0 }} students</span>
            </div>

            @if(($studentRanking ?? collect())->isEmpty())
                <p class="text-slate-400 text-[13px] text-center py-16">No quiz attempt data available yet.</p>
            @else
                @php
                    $rankShades = ['#0d326b', '#1e4b8f', '#3b82f6'];
                @endphp
                <div class="space-y-2.5">
                    @foreach($studentRanking as $i => $s)
                        @php
                            $rank = $i + 1;
                            $isTop3 = $rank <= 3;
                            $badgeColor = $isTop3 ? $rankShades[$rank - 1] : '#e2e8f0';
                            $badgeText = $isTop3 ? '#ffffff' : '#64748b';
                        @endphp
                        <div class="flex items-center gap-4 p-3 rounded-xl {{ $isTop3 ? 'bg-[#f8fafc]' : '' }} hover:bg-[#f8fafc] transition-colors">
                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-black shrink-0"
                                 style="background: {{ $badgeColor }}; color: {{ $badgeText }};">
                                {{ $rank }}
                            </div>
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($s['name']) }}&background=0d326b&color=fff&rounded=true&size=60"
                                 class="w-9 h-9 rounded-full ring-2 ring-slate-100 shrink-0" />
                            <div class="flex-1 min-w-0">
                                <p class="text-[13px] font-bold text-[#0d326b] truncate">{{ $s['name'] }}</p>
                                <p class="text-[10.5px] text-slate-400">{{ $s['attempts'] }} quiz attempt{{ $s['attempts'] !== 1 ? 's' : '' }}</p>
                            </div>
                            <div class="w-32 shrink-0">
                                <div class="h-2 rounded-full bg-[#eef2f7] overflow-hidden">
                                    <div class="h-full rounded-full" style="width: {{ max(4, $s['avg_score']) }}%; background: linear-gradient(90deg, #93c5fd, #0d326b);"></div>
                                </div>
                            </div>
                            <span class="text-[13px] font-black text-[#0d326b] w-12 text-right shrink-0">{{ $s['avg_score'] }}%</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="space-y-5">

            {{-- Mastery Level Distribution — navy donut with tooltips --}}
            <div class="panel !p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Mastery Level Distribution</p>
                @php
                    $navyScale = ['#dbeafe', '#93c5fd', '#3b82f6', '#1e4b8f', '#0d326b'];
                    $circ = 251.3;
                    $segments = [];
                    $off = 0;
                    foreach ($masteryDistribution as $i => $seg) {
                        $shade = $navyScale[$i % count($navyScale)];
                        $dash = round(($seg['pct'] / 100) * $circ, 1);
                        $segments[] = ['color' => $shade, 'dash' => $dash, 'offset' => -$off, 'label' => $seg['label'], 'pct' => $seg['pct'], 'count' => $seg['count'] ?? 0];
                        $off += $dash;
                    }
                @endphp
                <div class="flex items-center gap-5">
                    <div class="relative w-[120px] h-[120px] shrink-0">
                        <div id="masteryDonutTooltip" class="chart-tooltip" style="transform:translateX(-50%) translateY(-130%);">
                            <span id="masteryDonutLabel"></span>: <span id="masteryDonutValue"></span>
                        </div>
                        <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#f1f5f9" stroke-width="14"/>
                            @foreach($segments as $seg)
                                @if($seg['dash'] > 0)
                                <circle class="mastery-donut-hit" cx="50" cy="50" r="40" fill="none" stroke="{{ $seg['color'] }}" stroke-width="14"
                                    stroke-dasharray="{{ $seg['dash'] }} {{ $circ - $seg['dash'] }}"
                                    stroke-dashoffset="{{ $seg['offset'] }}" stroke-linecap="butt"
                                    style="cursor:pointer"
                                    data-label="{{ $seg['label'] }}"
                                    data-value="{{ $seg['count'] }} ({{ $seg['pct'] }}%)"/>
                                @endif
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-[20px] font-black text-[#0d326b]">{{ $masteryTotal ?? 0 }}</span>
                            <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Total</span>
                        </div>
                    </div>
                    <div class="space-y-2.5 flex-1 min-w-0">
                        @foreach($segments as $seg)
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 min-w-0">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $seg['color'] }}"></span>
                                    <span class="text-[11px] text-slate-600 truncate">{{ $seg['label'] }}</span>
                                </div>
                                <span class="text-[11px] font-bold text-[#0d326b] shrink-0">{{ $seg['pct'] }}%</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Completion Funnel — navy shades --}}
            <div class="panel !p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Completion Funnel</p>
                @if($completionTotal === 0)
                    <p class="text-slate-400 text-[13px] text-center py-10">No lesson assignments recorded yet.</p>
                @else
                    @php $funnelScale = ['#dbeafe', '#93c5fd', '#3b82f6', '#1e4b8f', '#0d326b']; @endphp
                    <div class="h-4 rounded-full bg-slate-100 overflow-hidden mb-4 flex">
                        @foreach($completionFunnel as $i => $step)
                            @php $width = $completionTotal > 0 ? max(2, round(($step['count'] / $completionTotal) * 100)) : 0; @endphp
                            <div class="h-full" style="width: {{ $width }}%; background: {{ $funnelScale[$i % count($funnelScale)] }}"></div>
                        @endforeach
                    </div>
                    <div class="space-y-2.5">
                        @foreach($completionFunnel as $i => $step)
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2">
                                    <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $funnelScale[$i % count($funnelScale)] }}"></span>
                                    <span class="text-[11px] text-slate-600">{{ $step['label'] }}</span>
                                </div>
                                <span class="text-[11px] font-bold text-[#0d326b]">{{ $step['count'] }}</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Score Distribution — donut with tooltips --}}
            <div class="panel !p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Score Distribution</p>
                <p class="text-[11px] text-slate-400 mb-4">Share of quiz attempts by score range</p>
                @php
                    $scoreTotal = collect($scoreBuckets)->sum('count');
                @endphp
                @if($scoreTotal === 0)
                    <p class="text-slate-400 text-[13px] text-center py-10">No quiz attempt data available yet.</p>
                @else
                    @php
                        $scoreScale = ['#dbeafe', '#93c5fd', '#3b82f6', '#1e4b8f', '#0d326b'];
                        $sCirc = 251.3;
                        $sSegments = [];
                        $sOff = 0;
                        foreach ($scoreBuckets as $i => $bucket) {
                            $pct = round(($bucket['count'] / $scoreTotal) * 100);
                            $shade = $scoreScale[$i % count($scoreScale)];
                            $dash = round(($pct / 100) * $sCirc, 1);
                            $sSegments[] = ['color' => $shade, 'dash' => $dash, 'offset' => -$sOff, 'label' => $bucket['label'], 'count' => $bucket['count'], 'pct' => $pct];
                            $sOff += $dash;
                        }
                    @endphp
                    <div class="flex items-center gap-5">
                        <div class="relative w-[120px] h-[120px] shrink-0">
                            <div id="scoreDonutTooltip" class="chart-tooltip" style="transform:translateX(-50%) translateY(-130%);">
                                <span id="scoreDonutLabel"></span>: <span id="scoreDonutValue"></span>
                            </div>
                            <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                                <circle cx="50" cy="50" r="40" fill="none" stroke="#f1f5f9" stroke-width="14"/>
                                @foreach($sSegments as $seg)
                                    @if($seg['dash'] > 0)
                                    <circle class="score-donut-hit" cx="50" cy="50" r="40" fill="none" stroke="{{ $seg['color'] }}" stroke-width="14"
                                        stroke-dasharray="{{ $seg['dash'] }} {{ $sCirc - $seg['dash'] }}"
                                        stroke-dashoffset="{{ $seg['offset'] }}" stroke-linecap="butt"
                                        style="cursor:pointer"
                                        data-label="{{ $seg['label'] }}"
                                        data-value="{{ $seg['count'] }} ({{ $seg['pct'] }}%)"/>
                                    @endif
                                @endforeach
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span class="text-[20px] font-black text-[#0d326b]">{{ $scoreTotal }}</span>
                                <span class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">Attempts</span>
                            </div>
                        </div>
                        <div class="space-y-2.5 flex-1 min-w-0">
                            @foreach($sSegments as $seg)
                                <div class="flex items-center justify-between gap-2">
                                    <div class="flex items-center gap-2 min-w-0">
                                        <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ $seg['color'] }}"></span>
                                        <span class="text-[11px] text-slate-600 truncate">{{ $seg['label'] }}</span>
                                    </div>
                                    <span class="text-[11px] font-bold text-[#0d326b] shrink-0">{{ $seg['count'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- ══════════ GESTURE PERFORMANCE ANALYTICS ══════════ --}}
    <div class="space-y-5">

        {{-- Section heading — same pattern as "ROW 2" labels above --}}
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Gesture Performance</p>
                <h3 class="text-[16px] font-bold text-[#0d326b]">Gesture Performance Analytics</h3>
                <p class="text-[12px] text-slate-400 mt-0.5">Sign accuracy, attempts, and mastery performance sourced directly from <strong>gesture_performances</strong> records.</p>
            </div>
            <span class="material-symbols-outlined text-[#1a6fd4] text-[28px] shrink-0 mt-1">waving_hand</span>
        </div>

        {{-- ── Gesture stat cards — identical layout to STAT CARDS above ── --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
            {{-- Card 1: Navy gradient — Practiced Gestures --}}
            <div class="stat-card text-white" style="background: linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%)">
                <div class="absolute -top-7 -right-7 w-28 h-28 bg-white/5 rounded-full"></div>
                <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-white/5 rounded-full"></div>
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center bg-white/10">
                            <span class="material-symbols-outlined text-[19px] text-white">pan_tool_alt</span>
                        </div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-white/50">Practiced Signs</p>
                    </div>
                    <p class="text-[32px] font-black leading-none mb-2 text-white">{{ number_format($gesturePerformanceOverview['total_gestures'] ?? 0) }}</p>
                    <p class="text-[12px] text-white/60">Total unique signs attempted</p>
                </div>
            </div>

            {{-- Card 2: Gesture Accuracy --}}
            <div class="stat-card bg-white border border-slate-100 shadow-sm">
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center bg-[#e8eef8]">
                            <span class="material-symbols-outlined text-[19px] text-[#1e4b8f]">verified</span>
                        </div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Gesture Accuracy</p>
                    </div>
                    <p class="text-[32px] font-black leading-none mb-2 text-[#0d326b]">{{ number_format($gesturePerformanceOverview['overall_accuracy'] ?? 0, 1) }}%</p>
                    <p class="text-[12px] text-slate-400">{{ number_format($gesturePerformanceOverview['total_successful'] ?? 0) }} correct / {{ number_format($gesturePerformanceOverview['total_attempts'] ?? 0) }} attempts</p>
                </div>
            </div>

            {{-- Card 3: Incorrect Attempts --}}
            <div class="stat-card bg-white border border-slate-100 shadow-sm">
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center bg-[#e8eef8]">
                            <span class="material-symbols-outlined text-[19px] text-[#1e4b8f]">cancel</span>
                        </div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Incorrect Attempts</p>
                    </div>
                    <p class="text-[32px] font-black leading-none mb-2 text-[#0d326b]">{{ number_format($gesturePerformanceOverview['total_wrong'] ?? 0) }}</p>
                    <p class="text-[12px] text-slate-400">Total wrong attempts recorded</p>
                </div>
            </div>

            {{-- Card 4: Signs Mastered --}}
            <div class="stat-card bg-white border border-slate-100 shadow-sm">
                <div class="relative z-10">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center bg-[#e8eef8]">
                            <span class="material-symbols-outlined text-[19px] text-[#1e4b8f]">military_tech</span>
                        </div>
                        <p class="text-[10px] font-bold uppercase tracking-widest text-slate-400">Signs Mastered</p>
                    </div>
                    <p class="text-[32px] font-black leading-none mb-2 text-[#0d326b]">{{ number_format($gesturePerformanceOverview['total_mastered'] ?? 0) }}</p>
                    <p class="text-[12px] text-slate-400">Mastered gesture entries</p>
                </div>
            </div>
        </div>

        {{-- ── Best vs Lowest — same grid pattern as other 2-col panels ── --}}
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">

            {{-- Best-Performing Gestures --}}
            <div class="panel">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Top Signs</p>
                        <h3 class="text-[16px] font-bold text-[#0d326b]">Best-Performing Gestures</h3>
                        <p class="text-[12px] text-slate-400 mt-0.5">Signs with the highest accuracy rate</p>
                    </div>
                    <span class="material-symbols-outlined text-emerald-500 text-[22px] shrink-0 mt-1">thumb_up</span>
                </div>
                @if(empty($topPerformingGestures) || count($topPerformingGestures) === 0)
                    <p class="text-slate-400 text-[13px] text-center py-10">No gesture performance data recorded yet.</p>
                @else
                    <div class="difficulty-list space-y-3">
                        @foreach($topPerformingGestures as $g)
                            <div class="difficulty-item">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-[13px] font-bold text-[#0d326b] truncate">{{ $g['gesture_name'] }}</span>
                                    <span class="text-[13px] font-black text-emerald-600 shrink-0 ml-2">{{ number_format($g['accuracy'], 1) }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden mb-1.5">
                                    <div class="bg-emerald-500 h-full rounded-full transition-all" style="width: {{ min(100, $g['accuracy']) }}%"></div>
                                </div>
                                <div class="flex items-center gap-3 text-[10px] font-semibold text-slate-400">
                                    <span>Attempts: <strong class="text-slate-600">{{ $g['attempts'] }}</strong></span>
                                    <span class="text-emerald-600">Correct: <strong>{{ $g['successful_attempts'] }}</strong></span>
                                    <span class="text-rose-500">Incorrect: <strong>{{ $g['wrong_attempts'] }}</strong></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Lowest-Performing Gestures --}}
            <div class="panel">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1">Needs Practice</p>
                        <h3 class="text-[16px] font-bold text-[#0d326b]">Struggling Gestures</h3>
                        <p class="text-[12px] text-slate-400 mt-0.5">Signs requiring additional practice</p>
                    </div>
                    <span class="material-symbols-outlined text-rose-500 text-[22px] shrink-0 mt-1">warning</span>
                </div>
                @if(empty($lowestPerformingGestures) || count($lowestPerformingGestures) === 0)
                    <p class="text-slate-400 text-[13px] text-center py-10">No gesture performance data recorded yet.</p>
                @else
                    <div class="difficulty-list space-y-3">
                        @foreach($lowestPerformingGestures as $g)
                            <div class="difficulty-item">
                                <div class="flex items-center justify-between mb-1.5">
                                    <span class="text-[13px] font-bold text-[#0d326b] truncate">{{ $g['gesture_name'] }}</span>
                                    <span class="text-[13px] font-black {{ $g['accuracy'] < 50 ? 'text-rose-600' : 'text-amber-600' }} shrink-0 ml-2">{{ number_format($g['accuracy'], 1) }}%</span>
                                </div>
                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden mb-1.5">
                                    <div class="{{ $g['accuracy'] < 50 ? 'bg-rose-500' : 'bg-amber-500' }} h-full rounded-full transition-all" style="width: {{ min(100, $g['accuracy']) }}%"></div>
                                </div>
                                <div class="flex items-center gap-3 text-[10px] font-semibold text-slate-400">
                                    <span>Attempts: <strong class="text-slate-600">{{ $g['attempts'] }}</strong></span>
                                    <span class="text-emerald-600">Correct: <strong>{{ $g['successful_attempts'] }}</strong></span>
                                    <span class="text-rose-500">Incorrect: <strong>{{ $g['wrong_attempts'] }}</strong></span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Line Chart Tooltip ──────────────────────────────────────────────────
    (function() {
        const wrap = document.querySelector('.chart-wrapper');
        const tip = document.getElementById('lineChartTooltip');
        if (!wrap || !tip) return;
        const tipLabel = document.getElementById('lineTooltipLabel');
        const tipValue = document.getElementById('lineTooltipValue');

        wrap.querySelectorAll('.line-point-hit').forEach(function(hit) {
            hit.addEventListener('mouseenter', function(e) {
                const rect = hit.getBoundingClientRect();
                const wrapRect = wrap.getBoundingClientRect();
                tip.style.left = (rect.left - wrapRect.left + rect.width / 2) + 'px';
                tip.style.top = (rect.top - wrapRect.top - 6) + 'px';
                tipLabel.textContent = hit.dataset.label;
                tipValue.textContent = hit.dataset.value;
                tip.classList.add('visible');
            });
            hit.addEventListener('mouseleave', function() {
                tip.classList.remove('visible');
            });
        });
    })();

    // ── Mastery Donut Tooltip ──────────────────────────────────────────────
    (function() {
        const wrap = document.querySelector('.panel:has(.mastery-donut-hit)');
        if (!wrap) return;
        const tip = wrap.querySelector('#masteryDonutTooltip');
        if (!tip) return;
        const tipLabel = tip.querySelector('#masteryDonutLabel');
        const tipValue = tip.querySelector('#masteryDonutValue');

        wrap.querySelectorAll('.mastery-donut-hit').forEach(function(seg) {
            seg.addEventListener('mouseenter', function(e) {
                const rect = seg.getBoundingClientRect();
                const wrapRect = wrap.getBoundingClientRect();
                tip.style.left = (rect.left - wrapRect.left + rect.width / 2) + 'px';
                tip.style.top = (rect.top - wrapRect.top - 8) + 'px';
                tipLabel.textContent = seg.dataset.label;
                tipValue.textContent = seg.dataset.value;
                tip.classList.add('visible');
            });
            seg.addEventListener('mouseleave', function() {
                tip.classList.remove('visible');
            });
        });
    })();

    // ── Score Donut Tooltip ────────────────────────────────────────────────
    (function() {
        const wrap = document.querySelector('.panel:has(.score-donut-hit)');
        if (!wrap) return;
        const tip = wrap.querySelector('#scoreDonutTooltip');
        if (!tip) return;
        const tipLabel = tip.querySelector('#scoreDonutLabel');
        const tipValue = tip.querySelector('#scoreDonutValue');

        wrap.querySelectorAll('.score-donut-hit').forEach(function(seg) {
            seg.addEventListener('mouseenter', function(e) {
                const rect = seg.getBoundingClientRect();
                const wrapRect = wrap.getBoundingClientRect();
                tip.style.left = (rect.left - wrapRect.left + rect.width / 2) + 'px';
                tip.style.top = (rect.top - wrapRect.top - 8) + 'px';
                tipLabel.textContent = seg.dataset.label;
                tipValue.textContent = seg.dataset.value;
                tip.classList.add('visible');
            });
            seg.addEventListener('mouseleave', function() {
                tip.classList.remove('visible');
            });
        });
    })();
});
</script>

@endsection