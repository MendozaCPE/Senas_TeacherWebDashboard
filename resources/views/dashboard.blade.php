@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
<div class="flex flex-col gap-8 w-full overflow-x-hidden">

    <div class="flex flex-col lg:flex-row gap-8 w-full">
                
    <!-- Left/Center Content -->
    <div class="flex-1 min-w-0 flex flex-col space-y-8">
        
        <!-- Welcome Banner + Calendar -->
        @php
            $today = \Carbon\Carbon::now();
            $weekDays = [];
            $startOfWeek = $today->copy()->startOfWeek(\Carbon\Carbon::MONDAY);
            for ($i = 0; $i < 7; $i++) {
                $weekDays[] = $startOfWeek->copy()->addDays($i);
            }
        @endphp
        <div class="flex gap-5">
            <!-- Welcome Banner -->
            <div class="flex-1 rounded-[28px] relative overflow-hidden min-h-[160px] flex items-center"
                 style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%)">
                <!-- Decorative circles -->
                <div class="absolute top-0 right-48 w-48 h-48 rounded-full opacity-10" style="background:white"></div>
                <div class="absolute -bottom-10 left-1/3 w-36 h-36 rounded-full opacity-10" style="background:white"></div>

                <!-- Text content -->
                <div class="relative z-10 px-10 py-8 flex-1">
                    <p class="text-[13px] font-semibold text-white/60 uppercase tracking-[0.15em] mb-2">{{ $today->format('l, F j') }}</p>
                    <h2 class="text-[28px] font-black text-white leading-tight mb-2">{{ $greeting }}, Teacher {{ $firstName }}!</h2>
                    <p class="text-[13px] text-white/70 font-medium leading-relaxed mb-5">Here is a summary of your classroom's<br>archival progress today.</p>
                    <a href="{{ route('lessons.index') }}" class="inline-flex items-center space-x-2 text-[12px] font-black text-white/80 uppercase tracking-[0.1em] hover:text-white transition-colors">
                        <span>GO TO LESSONS</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>

                <!-- Senya mascot -->
                <div class="relative z-10 flex-shrink-0 pr-4 flex items-end self-end">
                    <img src="{{ asset('images/wavingSenya.png') }}" alt="Senya" class="h-[170px] w-auto object-contain drop-shadow-lg" style="filter: drop-shadow(0 8px 24px rgba(0,0,0,0.3))"/>
                </div>
            </div>

            <!-- Calendar Widget -->
            <div class="bg-white rounded-[28px] px-6 py-5 shadow-sm border border-slate-100 flex-shrink-0 w-[260px] flex flex-col">
                <!-- Month nav -->
                <div class="flex items-center justify-between mb-4">
                    <span class="text-[13px] font-black text-[#0d326b]">{{ $today->format('F Y') }}</span>
                    <div class="flex items-center space-x-1">
                        <button class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors text-slate-400">
                            <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                        </button>
                        <button class="w-6 h-6 flex items-center justify-center rounded-full hover:bg-slate-100 transition-colors text-slate-400">
                            <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                        </button>
                    </div>
                </div>

                <!-- Day labels -->
                <div class="grid grid-cols-7 mb-2">
                    @foreach(['M','T','W','T','F','S','S'] as $d)
                    <div class="text-center text-[10px] font-bold text-slate-400 uppercase">{{ $d }}</div>
                    @endforeach
                </div>

                <!-- Week dates -->
                <div class="grid grid-cols-7 gap-y-1">
                    @foreach($weekDays as $day)
                    <div class="flex items-center justify-center">
                        @if($day->isToday())
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-[12px] font-black text-white" style="background: linear-gradient(135deg,#0d326b,#1a6fd4)">
                            {{ $day->format('d') }}
                        </div>
                        @else
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-[12px] font-medium text-slate-600 hover:bg-slate-100 cursor-pointer transition-colors">
                            {{ $day->format('d') }}
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                <div class="mt-auto pt-4 border-t border-slate-100">
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2">Today</p>
                    <p class="text-[13px] font-bold text-[#0d326b]">{{ $today->format('l, d F') }}</p>
                </div>
            </div>
        </div>

        @php
            $kpiSparkline = function (array $data, string $color, int $width = 240, int $height = 44): string {
                if (count($data) < 2) {
                    $data = array_fill(0, 7, $data[0] ?? 0);
                }
                $max = max($data);
                $min = min($data);
                $range = max($max - $min, 1);
                $count = count($data);
                $points = [];
                foreach ($data as $i => $value) {
                    $x = $count > 1 ? ($i / ($count - 1)) * $width : $width / 2;
                    $y = $height - 6 - (($value - $min) / $range) * ($height - 12);
                    $points[] = round($x, 1) . ',' . round($y, 1);
                }
                return '<svg viewBox="0 0 ' . $width . ' ' . $height . '" class="w-full h-[44px]" preserveAspectRatio="none" aria-hidden="true">'
                    . '<polyline fill="none" stroke="' . e($color) . '" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" points="' . implode(' ', $points) . '"/>'
                    . '</svg>';
            };
        @endphp

        <!-- Stats Row -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">

            {{-- Total Students --}}
            <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col min-h-[168px]">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-full bg-[#2563eb] flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-white text-[18px]">group</span>
                    </div>
                    <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Total Students</h3>
                </div>
                <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $totalStudents }}</p>
                <p class="text-[12px] font-medium text-emerald-600 mt-2 mb-4">↑ {{ $newStudentsThisWeek }} this week</p>
                <div class="mt-auto -mx-1">
                    {!! $kpiSparkline($sparklineTotalStudents ?: array_fill(0, 7, 0), '#2563eb') !!}
                </div>
            </div>

            {{-- Active Today --}}
            <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col min-h-[168px]">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-full bg-[#7c3aed] flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-white text-[18px]">monitor_heart</span>
                    </div>
                    <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Active Today</h3>
                </div>
                <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $activeToday }}</p>
                <p class="text-[12px] font-medium text-slate-400 mt-2 mb-4">{{ $activeTodayPercent }}% of total</p>
                <div class="mt-auto -mx-1">
                    {!! $kpiSparkline($sparklineActive ?: array_fill(0, 7, 0), '#7c3aed') !!}
                </div>
            </div>

            {{-- Avg. Accuracy --}}
            <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col min-h-[168px]">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-full bg-[#10b981] flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-white text-[18px]">gps_fixed</span>
                    </div>
                    <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Avg. Accuracy</h3>
                </div>
                <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $avgAccuracy > 0 ? $avgAccuracy . '%' : '0%' }}</p>
                <p class="text-[12px] font-medium text-emerald-600 mt-2 mb-4">↑ {{ max(0, $accuracyWeeklyChange) }}% this week</p>
                <div class="mt-auto -mx-1">
                    {!! $kpiSparkline($sparklineAccuracy ?: array_fill(0, 7, 0), '#10b981') !!}
                </div>
            </div>

            {{-- Lessons Completed --}}
            <div class="bg-white rounded-[24px] px-6 pt-5 pb-4 shadow-sm border border-slate-100 flex flex-col min-h-[168px]">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-9 h-9 rounded-full bg-[#f59e0b] flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-white text-[18px]">menu_book</span>
                    </div>
                    <h3 class="text-[14px] font-semibold text-slate-700 leading-none">Lessons Completed</h3>
                </div>
                <p class="text-[32px] font-bold text-[#0d326b] leading-none tracking-tight">{{ $lessonsCompleted }}</p>
                <p class="text-[12px] font-medium text-emerald-600 mt-2 mb-4">↑ {{ $lessonsCompletedThisWeek }} this week</p>
                <div class="mt-auto -mx-1">
                    {!! $kpiSparkline($sparklineLessons ?: array_fill(0, 7, 0), '#f59e0b') !!}
                </div>
            </div>

        </div>

        <!-- Your Lessons -->
        <div class="mt-4">
            <div class="flex justify-between items-end mb-6 pl-2">
                <h3 class="text-[22px] font-bold text-[#0d326b]">Your Lessons</h3>
                <a href="{{ route('lessons.index') }}" class="text-[14px] font-bold text-[#0d326b] hover:underline pr-2">Manage Lessons</a>
            </div>

            @if($lessons->isEmpty())
                <div class="bg-white rounded-[32px] p-10 text-center text-slate-400 text-sm font-medium shadow-[0_4px_20px_rgba(0,0,0,0.02)] border border-slate-100">
                    No record — no lessons created yet.
                </div>
            @else
            <div class="flex space-x-4 overflow-x-auto pb-3 scrollbar-hide pt-1">
                @foreach($lessons as $index => $lesson)
                @php
                    $palettes = [
                        ['bg' => '#e8eef8', 'tab_bg' => '#d0ddf2', 'title' => '#0d326b', 'sub' => '#4a6fa5', 'bar_fill' => '#0d326b', 'bar_bg' => '#c0cfe8'],
                        ['bg' => '#fef9e7', 'tab_bg' => '#fdefc0', 'title' => '#6b5000', 'sub' => '#9a7a00', 'bar_fill' => '#facc15', 'bar_bg' => '#fde68a'],
                        ['bg' => '#dbeafe', 'tab_bg' => '#bfd7f9', 'title' => '#1e3a8a', 'sub' => '#3b5fc0', 'bar_fill' => '#007fff', 'bar_bg' => '#bcd4f8'],
                        ['bg' => '#e0e9ff', 'tab_bg' => '#c7d5ff', 'title' => '#1e2f8a', 'sub' => '#3b4fc0', 'bar_fill' => '#0047ab', 'bar_bg' => '#bcc7f8'],
                    ];
                    $p = $palettes[$index % 4];
                @endphp

                <!-- CSS Folder Card -->
<div class="flex-shrink-0 w-[280px] group cursor-pointer transition-transform duration-300 hover:-translate-y-1.5">
    <!-- Folder Tab (top-left raised nub) -->
    <div class="w-[90px] h-[24px] rounded-t-[16px] ml-4"
         style="background: {{ $p['tab_bg'] }}"></div>

    <!-- Folder Body -->
    <div class="rounded-b-[24px] rounded-tr-[24px] p-6 shadow-sm transition-shadow duration-300 group-hover:shadow-md"
         style="background: {{ $p['bg'] }}; min-height: 200px; display: flex; flex-direction: column; justify-content: space-between;">

        <!-- Top: Title + 3-dot -->
        <div class="flex items-start justify-between mb-3">
            <h4 class="font-bold text-[17px] leading-snug pr-2"
                style="color: {{ $p['title'] }}">{{ $lesson->title }}</h4>
            <button class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center hover:bg-black/5 transition-colors"
                    style="color: {{ $p['sub'] }}">
                <span class="material-symbols-outlined text-[20px]">more_vert</span>
            </button>
        </div>

        <!-- Bottom: Avatars + count -->
        <div class="flex items-center justify-between mt-auto pt-3"
             style="border-top: 1px solid {{ $p['tab_bg'] }}">
            <div class="flex -space-x-1.5">
                @forelse($lesson->topStudents->take(3) as $s)
                <img src="https://ui-avatars.com/api/?name={{ urlencode($s->first_name . '+' . $s->last_name) }}&background={{ ltrim($p['bar_fill'], '#') }}&color=fff&rounded=true&size=60"
                     class="w-8 h-8 rounded-full border-2 border-white shadow-sm"
                     title="{{ $s->first_name }} {{ $s->last_name }}"/>
                @empty
                    <div class="w-8 h-8 rounded-full border-2 border-white flex items-center justify-center text-[10px] font-bold"
                         style="background: {{ $p['tab_bg'] }}; color: {{ $p['sub'] }}">—</div>
                @endforelse
                @if($lesson->extraStudents > 0)
                <div class="w-8 h-8 rounded-full border-2 border-white flex items-center justify-center text-[10px] font-black shadow-sm"
                     style="background: {{ $p['tab_bg'] }}; color: {{ $p['title'] }}">+{{ $lesson->extraStudents }}</div>
                @endif
            </div>
            <span class="text-[12px] font-semibold" style="color: {{ $p['sub'] }}">{{ $lesson->enrolled }} students</span>
        </div>
    </div>
</div>
                @endforeach

                <!-- Practice Sessions Yellow Card -->
<div class="bg-[#facc15] rounded-[20px] p-6 w-[280px] flex-shrink-0 flex flex-col justify-between shadow-sm relative overflow-hidden" style="min-height: 200px;">
    <div class="absolute top-0 right-0 w-24 h-24 opacity-10 rounded-bl-full bg-white"></div>
    <div class="flex justify-between items-start relative z-10">
        <span class="material-symbols-outlined text-[#0d326b] text-[28px]">assignment_ind</span>
        <button class="shrink-0 w-8 h-8 rounded-full flex items-center justify-center hover:bg-black/5 transition-colors text-[#0d326b]">
            <span class="material-symbols-outlined text-[20px]">more_vert</span>
        </button>
    </div>
    <div class="relative z-10">
        <h4 class="font-bold text-[#0d326b] text-[17px] mb-0.5">Practice Sessions</h4>
        <p class="text-[#0d326b]/60 text-[12px] mb-3">Active Monitoring</p>
        <div class="w-full h-2 rounded-full bg-black/10 mb-1.5">
            <div class="h-full rounded-full bg-[#0d326b]" style="width: 75%;"></div>
        </div>
        <div class="text-right">
            <span class="text-[10px] font-bold text-[#0d326b] uppercase tracking-wider">75% Activity</span>
        </div>
    </div>
</div>
            </div>
            @endif
        </div>

        @php
            /* ---------------------------------------------------------
             | Data prep for the redesigned Mastery + Lesson Progress
             | + Student Performance widgets below. Reuses existing
             | controller variables; swap in real series data when
             | available.
             ---------------------------------------------------------- */

            // 7-day trend line — reuse the accuracy sparkline series as the
            // "mastery over time" trend, with date labels for the last 7 days.
            $masterySeries = $sparklineAccuracy ?: array_fill(0, 7, 0);
            if (count($masterySeries) < 7) {
                $masterySeries = array_pad($masterySeries, 7, end($masterySeries) ?: 0);
            }
            $masterySeries = array_slice($masterySeries, -7);

            $masteryDayLabels = [];
            for ($i = 6; $i >= 0; $i--) {
                $masteryDayLabels[] = $today->copy()->subDays($i)->format('M j');
            }

            $masteryCurrentValue = (int) round(end($masterySeries));

            // Lesson Progress donut — derive Completed / In Progress / Not Started
            // counts from the lesson mastery collection.
            $lpTotal = $lessons->count();
            $lpCompleted = $lessonMastery->where('masteryPct', '>=', 100)->count();
            $lpInProgress = $lessonMastery->filter(fn ($lm) => $lm->masteryPct > 0 && $lm->masteryPct < 100)->count();
            $lpNotStarted = max(0, $lpTotal - $lpCompleted - $lpInProgress);

            $donutCircumference = round(2 * pi() * 60, 4);

            // Segment order: Completed, In Progress, Not Started
            $donutSegments = $lpTotal > 0 ? [
                ['count' => $lpCompleted, 'color' => '#10b981'],   // emerald — matches existing "Good" badge
                ['count' => $lpInProgress, 'color' => '#1a6fd4'],  // brand blue
                ['count' => $lpNotStarted, 'color' => '#e2e8f0'],  // slate-200
            ] : [];

            $donutOffset = 0;
        @endphp

    </div>

    <!-- Right Sidebar Column -->
    <div class="w-[340px] flex-shrink-0 flex flex-col space-y-8 pl-4">
        
        <!-- Senya Tip Widget -->
        <div class="rounded-[32px] p-8 relative overflow-hidden shadow-sm" style="background: linear-gradient(135deg, #f59e0b 0%, #facc15 50%, #fbbf24 100%)">
            <!-- Decorative background element -->
            <div class="absolute -bottom-16 -right-16 text-[#eab308] opacity-50 transform rotate-45">
                <svg width="150" height="150" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2L15 9L22 12L15 15L12 22L9 15L2 12L9 9L12 2Z"/></svg>
            </div>
            
            <div class="flex items-center space-x-3 mb-6 relative z-10">
                <div class="w-[42px] h-[42px] bg-white rounded-full flex items-center justify-center shadow-sm overflow-hidden p-1">
                    <img src="https://api.dicebear.com/7.x/bottts/svg?seed=Senya&backgroundColor=ffffff" alt="Senya" class="w-full h-full"/>
                </div>
                <span class="text-[11px] font-black uppercase tracking-[0.15em] text-[#1e293b]">Senya Tip</span>
            </div>
            
            @if($needsAttention->isNotEmpty())
            <p class="text-[14px] font-bold text-[#1e293b] leading-[1.6] mb-8 relative z-10">
                {{ $needsAttention->count() }} student{{ $needsAttention->count() > 1 ? 's are' : ' is' }} struggling and may need extra guidance.
            </p>
            @else
            <p class="text-[14px] font-bold text-[#1e293b] leading-[1.6] mb-8 relative z-10">
                All students are on track! Keep it up.
            </p>
            @endif
            
            <a href="{{ route('students') }}" class="block w-full bg-[#18181b] text-[#facc15] text-[13px] font-bold py-3.5 rounded-xl hover:bg-black transition-colors shadow-sm relative z-10 text-center">
                View Students
            </a>
        </div>

        <!-- Needs Attention -->
        <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)]">
            <h4 class="text-[12px] font-bold tracking-[0.15em] uppercase text-[#0d326b] mb-8">Needs Your Attention</h4>
            
            @if($needsAttention->isEmpty())
                <div class="text-center text-sm text-slate-400 py-4 italic">No record</div>
            @else
            <div class="space-y-6">
                @foreach($needsAttention as $s)
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($s->first_name . ' ' . $s->last_name) }}&background=random&color=fff&rounded=true" class="w-10 h-10 rounded-full shadow-sm"/>
                        <span class="text-[14px] font-bold text-[#1e293b]">{{ $s->first_name }} {{ $s->last_name }}</span>
                    </div>
                    @if($s->alertType === 'Alert')
                    <span class="px-2.5 py-1 bg-[#fee2e2] text-[#b91c1c] text-[10px] font-black rounded-md uppercase tracking-wider">Alert</span>
                    @else
                    <span class="px-2.5 py-1 bg-[#fef3c7] text-[#b45309] text-[10px] font-black rounded-md uppercase tracking-wider">Review</span>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <!-- Insights -->
        <div class="text-white rounded-[32px] p-8 relative overflow-hidden shadow-sm" style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%)">
            <!-- Decorative bg -->
            <div class="absolute top-4 right-4 opacity-10">
                <span class="material-symbols-outlined icon-outline text-[80px]">psychology</span>
            </div>
            
            <div class="flex items-center space-x-2 mb-8 relative z-10">
                <span class="material-symbols-outlined icon-outline text-[16px] text-[#93c5fd]">auto_awesome</span>
                <span class="text-[11px] font-bold tracking-[0.15em] uppercase text-[#93c5fd]">Insights</span>
            </div>
            
            @if($topLesson)
            <div class="border-l-2 border-[#10b981] pl-4 mb-8 relative z-10">
                <h4 class="text-[15px] font-bold mb-2 text-white">Top Performance: {{ $topLesson->title }}</h4>
                <p class="text-[13px] text-[#93c5fd] leading-relaxed">
                    {{ $topLesson->masteryPct }}% completion rate — students are doing great in this module!
                </p>
            </div>
            @else
            <div class="border-l-2 border-slate-500 pl-4 mb-8 relative z-10">
                <h4 class="text-[15px] font-bold mb-2 text-slate-300">No record</h4>
                <p class="text-[13px] text-[#93c5fd] leading-relaxed">
                    Insights will appear once students start completing lessons.
                </p>
            </div>
            @endif
            
            <a href="{{ route('students') }}" class="block w-full bg-[#0d4599] hover:bg-[#1556b3] text-[#93c5fd] text-[13px] font-semibold py-3.5 rounded-xl transition-colors flex items-center justify-center space-x-2 relative z-10 shadow-sm border border-[#2563eb]/30 text-center">
                <span>View Full Report</span>
                <span class="material-symbols-outlined icon-outline text-[16px]">description</span>
            </a>
        </div>

    </div>
</div>

    <!-- Bottom Row: Mastery / Lesson Progress / Student Performance (full width, ignores sidebar column) -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            <!-- Student Mastery Overview (line/area chart) -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 relative overflow-hidden">
                <div class="absolute -top-8 -right-8 w-32 h-32 bg-indigo-50 rounded-full opacity-60"></div>

                <div class="flex items-center justify-between mb-6 relative z-10">
                    <h3 class="text-base font-bold text-[#0d326b]">Student Mastery Overview</h3>
                </div>

                @if(array_sum($masterySeries) === 0 && $lessonMastery->isEmpty())
                    <div class="text-center text-sm text-slate-400 py-12 italic relative z-10">No record</div>
                @else
                @php
                    $chartW = 320; $chartH = 180;
                    $padL = 34; $padR = 12; $padT = 32; $padB = 26;
                    $plotW = $chartW - $padL - $padR;
                    $plotH = $chartH - $padT - $padB;
                    $maxVal = 100; $minVal = 0;
                    $n = count($masterySeries);
                    $pts = [];
                    foreach ($masterySeries as $i => $v) {
                        $x = $padL + ($n > 1 ? ($i / ($n - 1)) * $plotW : $plotW / 2);
                        $y = $padT + $plotH - (($v - $minVal) / max($maxVal - $minVal, 1)) * $plotH;
                        $pts[] = [round($x, 1), round($y, 1)];
                    }
                    $polyPoints = implode(' ', array_map(fn($p) => $p[0] . ',' . $p[1], $pts));
                    $areaPoints = $polyPoints . ' ' . ($padL + $plotW) . ',' . ($padT + $plotH) . ' ' . $padL . ',' . ($padT + $plotH);
                    $lastPt = end($pts);
                @endphp
                <div class="relative z-10">
                    <svg viewBox="0 0 {{ $chartW }} {{ $chartH }}" class="w-full h-[180px]" preserveAspectRatio="none">
                        <defs>
                            <linearGradient id="masteryAreaFill" x1="0%" y1="0%" x2="0%" y2="100%">
                                <stop offset="0%" stop-color="#1a6fd4" stop-opacity="0.25"/>
                                <stop offset="100%" stop-color="#1a6fd4" stop-opacity="0"/>
                            </linearGradient>
                        </defs>

                        <!-- Gridlines + Y labels -->
                        @foreach([0, 25, 50, 75, 100] as $gv)
                        @php $gy = round($padT + $plotH - ($gv / 100) * $plotH, 1); @endphp
                        <line x1="{{ $padL }}" y1="{{ $gy }}" x2="{{ $padL + $plotW }}" y2="{{ $gy }}" stroke="#f1f5f9" stroke-width="1"/>
                        <text x="0" y="{{ $gy + 4 }}" font-size="10" fill="#94a3b8" font-weight="600">{{ $gv }}%</text>
                        @endforeach

                        <!-- Area fill -->
                        <polygon points="{{ $areaPoints }}" fill="url(#masteryAreaFill)"/>

                        <!-- Line -->
                        <polyline fill="none" stroke="#0d326b" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" points="{{ $polyPoints }}"/>

                        <!-- Points -->
                        @foreach($pts as $i => $p)
                        <circle cx="{{ $p[0] }}" cy="{{ $p[1] }}" r="{{ $i === count($pts) - 1 ? 5 : 3.5 }}" fill="{{ $i === count($pts) - 1 ? '#1a6fd4' : '#0d326b' }}" stroke="white" stroke-width="2"/>
                        @endforeach

                        <!-- X labels -->
                        @foreach($masteryDayLabels as $i => $label)
                        <text x="{{ $pts[$i][0] }}" y="{{ $chartH - 4 }}" font-size="9" fill="#94a3b8" font-weight="600" text-anchor="middle">{{ $label }}</text>
                        @endforeach
                    </svg>

                    <!-- Callout bubble for current value -->
                    <div class="absolute top-0 right-0 bg-white border border-slate-100 shadow-sm rounded-full px-3 py-1">
                        <span class="text-[13px] font-black text-[#0d326b]">{{ $masteryCurrentValue }}%</span>
                    </div>
                </div>
                @endif
            </div>

            <!-- Lesson Progress (donut chart) -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 relative overflow-hidden flex flex-col">
                <div class="absolute -top-10 -right-10 w-36 h-36 bg-yellow-50 rounded-full opacity-60"></div>

                <h3 class="text-base font-bold text-[#0d326b] mb-6 relative z-10">Lesson Progress</h3>

                @if($lpTotal === 0)
                    <div class="flex-1 flex items-center justify-center text-sm text-slate-400 italic relative z-10">No record</div>
                @else
                <div class="flex items-center gap-5 relative z-10">
                    <!-- Donut -->
                    <div class="relative w-28 h-28 flex-shrink-0">
                        <svg class="w-full h-full transform -rotate-90" viewBox="0 0 144 144">
                            <circle cx="72" cy="72" r="60" fill="transparent" stroke="#f1f5f9" stroke-width="14"></circle>
                            @php $cumulative = 0; @endphp
                            @foreach($donutSegments as $seg)
                                @if($seg['count'] > 0)
                                @php
                                    $segLen = round(($seg['count'] / $lpTotal) * $donutCircumference, 2);
                                    $gap = round($donutCircumference - $segLen, 2);
                                    $offset = round(-1 * ($cumulative / $lpTotal) * $donutCircumference, 2);
                                @endphp
                                <circle cx="72" cy="72" r="60" fill="transparent"
                                        stroke="{{ $seg['color'] }}"
                                        stroke-width="14"
                                        stroke-dasharray="{{ $segLen }} {{ $gap }}"
                                        stroke-dashoffset="{{ $offset }}"
                                        stroke-linecap="round"></circle>
                                @php $cumulative += $seg['count']; @endphp
                                @endif
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="text-2xl font-black text-[#0d326b] leading-none">{{ $lpTotal }}</span>
                            <span class="text-[8px] font-bold uppercase tracking-widest text-slate-400 mt-1">Total</span>
                        </div>
                    </div>

                    <!-- Legend -->
                    <div class="space-y-3 flex-1 min-w-0">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:#10b981"></div>
                                <span class="text-[12px] font-semibold text-slate-600 truncate">Completed</span>
                            </div>
                            <span class="text-[12px] font-black text-[#0d326b] flex-shrink-0">{{ $lpCompleted }} ({{ round(($lpCompleted / $lpTotal) * 100) }}%)</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-2.5 h-2.5 rounded-full flex-shrink-0" style="background:#1a6fd4"></div>
                                <span class="text-[12px] font-semibold text-slate-600 truncate">In Progress</span>
                            </div>
                            <span class="text-[12px] font-black text-[#0d326b] flex-shrink-0">{{ $lpInProgress }} ({{ round(($lpInProgress / $lpTotal) * 100) }}%)</span>
                        </div>
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2 min-w-0">
                                <div class="w-2.5 h-2.5 rounded-full bg-slate-200 flex-shrink-0"></div>
                                <span class="text-[12px] font-semibold text-slate-600 truncate">Not Started</span>
                            </div>
                            <span class="text-[12px] font-black text-[#0d326b] flex-shrink-0">{{ $lpNotStarted }} ({{ round(($lpNotStarted / $lpTotal) * 100) }}%)</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('lessons.index') }}" class="mt-6 block w-full bg-[#eef2ff] text-[#0d326b] text-[13px] font-bold py-3 rounded-xl hover:bg-[#e0e7ff] transition-colors text-center relative z-10">
                    View All Lessons
                </a>
                @endif
            </div>

            <!-- Student Performance (compact) -->
            <div class="bg-white rounded-3xl p-6 shadow-sm border border-slate-100 relative overflow-hidden flex flex-col">
                <div class="absolute -top-10 -right-10 w-36 h-36 bg-indigo-50 rounded-full opacity-40"></div>

                <div class="flex justify-between items-start mb-1 relative z-10">
                    <h3 class="text-base font-bold text-[#0d326b]">Student Performance</h3>
                    <a href="{{ route('students') }}" 
                        class="px-3 py-1.5 bg-gradient-to-br from-[#0d326b] via-[#1e4b8f] to-[#1a6fd4] text-white text-[11px] font-bold rounded-lg hover:from-[#1e4b8f] hover:via-[#1e4b8f] hover:to-[#1a6fd4] transition-all duration-300 shadow-sm flex items-center space-x-1 flex-shrink-0">
                        <span>View All</span>
                        <span class="material-symbols-outlined text-[12px]">arrow_forward</span>
                    </a>
                </div>
                <p class="text-[11px] text-slate-400 font-medium mb-5 relative z-10">Progress overview for all enrolled students</p>

                <div class="space-y-4 relative z-10 flex-1">
                    @forelse($students->take(4) as $student)
                    @php
                        $pct = $student->performancePct;
                        if ($pct >= 75) { $badge = ['label' => 'Good', 'bg' => 'bg-emerald-50', 'text' => 'text-emerald-700', 'bar' => 'linear-gradient(90deg,#059669,#10b981)']; }
                        elseif ($pct >= 40) { $badge = ['label' => 'Fair', 'bg' => 'bg-blue-50', 'text' => 'text-blue-700', 'bar' => 'linear-gradient(90deg,#1e40af,#6366f1)']; }
                        else { $badge = ['label' => 'Needs Help', 'bg' => 'bg-red-50', 'text' => 'text-red-600', 'bar' => 'linear-gradient(90deg,#dc2626,#f87171)']; }
                    @endphp
                    <div class="flex items-center gap-3">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($student->first_name . ' ' . $student->last_name) }}&background=random&color=fff&rounded=true"
                             class="w-9 h-9 rounded-full flex-shrink-0 shadow-sm"/>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1.5 gap-2">
                                <span class="text-[13px] font-bold text-slate-700 truncate">{{ $student->first_name }}</span>
                                <div class="flex items-center space-x-2 flex-shrink-0">
                                    <span class="text-[13px] font-black text-[#0d326b]">{{ $pct }}%</span>
                                    <span class="px-2 py-0.5 text-[9px] font-black rounded-md uppercase tracking-wider whitespace-nowrap {{ $badge['bg'] }} {{ $badge['text'] }}">
                                        {{ $badge['label'] }}
                                    </span>
                                </div>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full transition-all duration-700"
                                     style="width: {{ $pct }}%; background: {{ $badge['bar'] }}"></div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="text-center text-sm text-slate-400 py-8 italic">
                        No record — no students added yet.
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

</div>
@endsection