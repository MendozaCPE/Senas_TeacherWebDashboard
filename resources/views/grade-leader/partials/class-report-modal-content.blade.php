<div class="cr-report-space" data-school-year="{{ $periodLabel }}">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3">
        <div class="cr-kpi cr-kpi-primary"><span>Enrolled Students</span><strong>{{ $metrics['student_count'] }}</strong><small>in this classroom</small></div>
        <div class="cr-kpi"><span>Active This Week</span><strong>{{ $metrics['active_count'] }} <small>/ {{ $metrics['student_count'] }}</small></strong><small>{{ $metrics['active_rate'] }}% of enrolled</small></div>
        <div class="cr-kpi"><span>Average Quiz Score</span><strong>{{ $metrics['avg_quiz_score'] === null ? '—' : $metrics['avg_quiz_score'].'%' }}</strong><small>{{ $metrics['quiz_count'] }} completed attempts</small></div>
        <div class="cr-kpi"><span>Quiz Pass Rate</span><strong>{{ $metrics['quiz_pass_rate'] }}%</strong><small>{{ $metrics['passed_quizzes'] }} passed at 75%+</small></div>
        <div class="cr-kpi"><span>Lesson Completion</span><strong>{{ $metrics['lesson_completion_rate'] }}%</strong><small>{{ $metrics['completed_assignments'] }} / {{ $metrics['total_assignments'] }} assignments</small></div>
    </div>

    @php
        $activeCount = (int) $metrics['active_count'];
        $studentCount = (int) $metrics['student_count'];
        $inactiveCount = max(0, $studentCount - $activeCount);
        if ($studentCount === 0) {
            $activityInsight = 'No enrolled students are available to measure classroom activity.';
        } else {
            $activityInsight = $activeCount . ' of ' . $studentCount . ' students (' . $metrics['active_rate'] . '%) had recorded activity in the last 7 days. ' . $inactiveCount . ' had no recent activity recorded.';
        }

        if ($metrics['avg_quiz_score'] === null) {
            $resultsInsight = 'There are no completed quiz attempts for ' . $periodLabel . ', so academic performance cannot be compared yet.';
        } elseif ($metrics['avg_quiz_score'] >= 75 && $metrics['lesson_completion_rate'] < 50) {
            $resultsInsight = 'Quiz average is above the 75% benchmark, while lesson completion is ' . $metrics['lesson_completion_rate'] . '%. Follow up on unfinished assignments to keep progress moving.';
        } elseif ($metrics['avg_quiz_score'] < 75) {
            $resultsInsight = 'The quiz average is ' . $metrics['avg_quiz_score'] . '%, below the 75% benchmark. Review missed concepts with students who scored below the passing mark.';
        } else {
            $resultsInsight = $metrics['passed_quizzes'] . ' of ' . $metrics['quiz_count'] . ' quiz attempts (' . $metrics['quiz_pass_rate'] . '%) met the 75% benchmark. Lesson completion is ' . $metrics['lesson_completion_rate'] . '%.';
        }
    @endphp

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        <section class="cr-panel">
            <div class="cr-panel-heading">
                <div><p>ENGAGEMENT</p><h3>Classroom Activity</h3><small>Based on student activity recorded in the last 7 days</small></div>
                <span class="material-symbols-outlined">groups</span>
            </div>
            <div class="mt-5">
                <div class="flex justify-between text-[12px] font-bold mb-2"><span class="text-slate-600">Active students</span><span class="text-[#0d326b]">{{ $metrics['active_count'] }} / {{ $metrics['student_count'] }}</span></div>
                <div class="h-2.5 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full bg-gradient-to-r from-blue-400 to-[#0d326b]" style="width:{{ min(100,$metrics['active_rate']) }}%"></div></div>
                <div class="flex justify-between mt-2 text-[10px] font-semibold text-slate-400"><span>{{ $metrics['active_rate'] }}% active</span><span>{{ max(0,$metrics['student_count']-$metrics['active_count']) }} inactive this week</span></div>
            </div>
            <div class="grid grid-cols-2 gap-3 mt-5">
                <div class="rounded-xl bg-blue-50 p-3"><p class="text-[9px] uppercase tracking-wider text-blue-700 font-bold">Published Lessons</p><strong class="block mt-1 text-[22px] text-[#0d326b] font-black">{{ $metrics['published_lessons'] }}</strong></div>
                <div class="rounded-xl bg-blue-50 p-3"><p class="text-[9px] uppercase tracking-wider text-blue-700 font-bold">Checkpoint Exams</p><strong class="block mt-1 text-[22px] text-[#0d326b] font-black">{{ $metrics['published_checkpoint_exams'] }}</strong></div>
            </div>
            <div class="cr-chart-insight mt-4"><span class="material-symbols-outlined">lightbulb</span><p><strong>Engagement Insight</strong>{{ $activityInsight }}</p></div>
        </section>

        <section class="cr-panel">
            <div class="cr-panel-heading">
                <div><p>ACADEMIC PERFORMANCE</p><h3>Class Results</h3><small>Completed work during {{ $periodLabel }}</small></div>
                <span class="material-symbols-outlined">query_stats</span>
            </div>
            <div class="space-y-5 mt-5">
                <div>
                    <div class="flex justify-between text-[11px] font-bold mb-2"><span class="text-slate-600">Average quiz score</span><span class="text-[#0d326b]">{{ $metrics['avg_quiz_score'] === null ? 'No quiz data' : $metrics['avg_quiz_score'].'%' }}</span></div>
                    <div class="h-2 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full bg-blue-600" style="width:{{ min(100,$metrics['avg_quiz_score'] ?? 0) }}%"></div></div>
                </div>
                <div>
                    <div class="flex justify-between text-[11px] font-bold mb-2"><span class="text-slate-600">Quiz pass rate (75%+)</span><span class="text-[#0d326b]">{{ $metrics['quiz_pass_rate'] }}%</span></div>
                    <div class="h-2 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full bg-blue-500" style="width:{{ min(100,$metrics['quiz_pass_rate']) }}%"></div></div>
                </div>
                <div>
                    <div class="flex justify-between text-[11px] font-bold mb-2"><span class="text-slate-600">Lesson assignment completion</span><span class="text-[#0d326b]">{{ $metrics['lesson_completion_rate'] }}%</span></div>
                    <div class="h-2 rounded-full bg-slate-100 overflow-hidden"><div class="h-full rounded-full bg-[#0d326b]" style="width:{{ min(100,$metrics['lesson_completion_rate']) }}%"></div></div>
                </div>
            </div>
            <div class="cr-chart-insight mt-4"><span class="material-symbols-outlined">lightbulb</span><p><strong>Performance Insight</strong>{{ $resultsInsight }}</p></div>
        </section>
    </div>

    @php
        $trendMax = max(1, (int) $metrics['activity_trend']->max('active_count'));
        $programPalette = [
            'Regular' => ['color' => '#0d326b', 'from' => '#1e4b8f', 'to' => '#071c3f'],
            'Inclusion' => ['color' => '#1a6fd4', 'from' => '#3b82f6', 'to' => '#1a6fd4'],
            'Transition' => ['color' => '#3b82f6', 'from' => '#60a5fa', 'to' => '#3b82f6'],
            'Self-contained' => ['color' => '#93c5fd', 'from' => '#bfdbfe', 'to' => '#93c5fd'],
        ];
        $masteryPalette = [
            'Beginner' => ['color' => '#93c5fd', 'from' => '#bfdbfe', 'to' => '#93c5fd'],
            'Intermediate' => ['color' => '#3b82f6', 'from' => '#60a5fa', 'to' => '#3b82f6'],
            'Advanced' => ['color' => '#0d326b', 'from' => '#1e4b8f', 'to' => '#071c3f'],
        ];
        $programTotal = $metrics['program_type_distribution']->sum();
        $programSegments = [];
        $programActiveCount = $metrics['program_type_distribution']->filter(fn ($count) => $count > 0)->count();
        $programMaxCount = max(1, (int) $metrics['program_type_distribution']->max());
        $programGap = $programActiveCount > 1 ? 0.08 : 0;
        $programAngle = -M_PI / 2;
        foreach ($metrics['program_type_distribution'] as $program => $count) {
            if ($count <= 0) continue;
            $angleSpan = $programTotal > 0 ? ($count / $programTotal * 2 * M_PI) : 0;
            $outerRadius = round(56 + 18 * ($count / $programMaxCount), 1);
            $startAngle = $programActiveCount === 1 ? $programAngle : $programAngle + $programGap / 2;
            $endAngle = $programActiveCount === 1 ? $programAngle + 2 * M_PI - 0.001 : $programAngle + $angleSpan - $programGap / 2;
            if ($endAngle <= $startAngle) { $startAngle = $programAngle; $endAngle = $programAngle + $angleSpan; }
            $x1 = round(80 + $outerRadius * cos($startAngle), 2); $y1 = round(80 + $outerRadius * sin($startAngle), 2);
            $x2 = round(80 + $outerRadius * cos($endAngle), 2); $y2 = round(80 + $outerRadius * sin($endAngle), 2);
            $x3 = round(80 + 40 * cos($endAngle), 2); $y3 = round(80 + 40 * sin($endAngle), 2);
            $x4 = round(80 + 40 * cos($startAngle), 2); $y4 = round(80 + 40 * sin($startAngle), 2);
            $largeArc = ($endAngle - $startAngle > M_PI) ? 1 : 0;
            $programSegments[] = [
                'label' => $program, 'count' => $count, 'color' => $programPalette[$program]['color'],
                'grad_from' => $programPalette[$program]['from'], 'grad_to' => $programPalette[$program]['to'],
                'grad_id' => 'crProgram' . preg_replace('/[^A-Za-z0-9]/', '', $program),
                'pct' => round($count / max(1, $programTotal) * 100),
                'path' => "M {$x1} {$y1} A {$outerRadius} {$outerRadius} 0 {$largeArc} 1 {$x2} {$y2} L {$x3} {$y3} A 40 40 0 {$largeArc} 0 {$x4} {$y4} Z",
            ];
            $programAngle += $angleSpan;
        }
        $masteryTotal = $metrics['mastery_level_distribution']->sum();
        $masterySegments = [];
        $masteryActiveCount = $metrics['mastery_level_distribution']->filter(fn ($count) => $count > 0)->count();
        $masteryMaxCount = max(1, (int) $metrics['mastery_level_distribution']->max());
        $masteryGap = $masteryActiveCount > 1 ? 0.08 : 0;
        $masteryAngle = -M_PI / 2;
        foreach ($metrics['mastery_level_distribution'] as $level => $count) {
            if ($count <= 0) continue;
            $angleSpan = $masteryTotal > 0 ? ($count / $masteryTotal * 2 * M_PI) : 0;
            $outerRadius = round(56 + 18 * ($count / $masteryMaxCount), 1);
            $startAngle = $masteryActiveCount === 1 ? $masteryAngle : $masteryAngle + $masteryGap / 2;
            $endAngle = $masteryActiveCount === 1 ? $masteryAngle + 2 * M_PI - 0.001 : $masteryAngle + $angleSpan - $masteryGap / 2;
            if ($endAngle <= $startAngle) { $startAngle = $masteryAngle; $endAngle = $masteryAngle + $angleSpan; }
            $x1 = round(80 + $outerRadius * cos($startAngle), 2); $y1 = round(80 + $outerRadius * sin($startAngle), 2);
            $x2 = round(80 + $outerRadius * cos($endAngle), 2); $y2 = round(80 + $outerRadius * sin($endAngle), 2);
            $x3 = round(80 + 40 * cos($endAngle), 2); $y3 = round(80 + 40 * sin($endAngle), 2);
            $x4 = round(80 + 40 * cos($startAngle), 2); $y4 = round(80 + 40 * sin($startAngle), 2);
            $largeArc = ($endAngle - $startAngle > M_PI) ? 1 : 0;
            $masterySegments[] = [
                'label' => $level, 'count' => $count, 'color' => $masteryPalette[$level]['color'],
                'grad_from' => $masteryPalette[$level]['from'], 'grad_to' => $masteryPalette[$level]['to'],
                'grad_id' => 'crMastery' . preg_replace('/[^A-Za-z0-9]/', '', $level),
                'pct' => round($count / max(1, $masteryTotal) * 100),
                'path' => "M {$x1} {$y1} A {$outerRadius} {$outerRadius} 0 {$largeArc} 1 {$x2} {$y2} L {$x3} {$y3} A 40 40 0 {$largeArc} 0 {$x4} {$y4} Z",
            ];
            $masteryAngle += $angleSpan;
        }

        $completeWeeks = $metrics['activity_trend']->values()->take(max(0, $metrics['activity_trend']->count() - 1));
        $peakWeek = $completeWeeks->sortByDesc('active_count')->first();
        $lastCompleteWeek = $completeWeeks->last();
        $previousCompleteWeek = $completeWeeks->count() > 1 ? $completeWeeks->get($completeWeeks->count() - 2) : null;
        if (!$peakWeek || (int) $peakWeek['active_count'] === 0) {
            $trendInsight = 'No quiz or lesson activity events were recorded in the completed weeks shown.';
        } elseif ($previousCompleteWeek && $lastCompleteWeek) {
            $weeklyChange = (int) $lastCompleteWeek['active_count'] - (int) $previousCompleteWeek['active_count'];
            $trendDirection = $weeklyChange > 0 ? 'increased' : ($weeklyChange < 0 ? 'decreased' : 'held steady');
            $trendAmount = abs($weeklyChange);
            $trendInsight = 'The latest completed week (' . $lastCompleteWeek['label'] . ') had ' . $lastCompleteWeek['active_count'] . ' active students; activity ' . $trendDirection . ($weeklyChange === 0 ? '' : ' by ' . $trendAmount . ' student(s)') . ' from the week before. Peak activity was ' . $peakWeek['active_count'] . ' students (' . $peakWeek['label'] . ').';
        } else {
            $trendInsight = 'Peak recorded learning activity was ' . $peakWeek['active_count'] . ' students during the week of ' . $peakWeek['label'] . '.';
        }

        $largestProgram = collect($programSegments)->sortByDesc('count')->first();
        $unclassifiedProgramStudents = max(0, $studentCount - $programTotal);
        $programInsight = $largestProgram
            ? $largestProgram['label'] . ' is the largest recorded program group with ' . $largestProgram['count'] . ' of ' . $programTotal . ' classified students (' . $largestProgram['pct'] . '%).' . ($unclassifiedProgramStudents > 0 ? ' Program classification is missing for ' . $unclassifiedProgramStudents . ' enrolled student(s).' : '')
            : 'No program classifications are recorded for this classroom.';

        $largestMastery = collect($masterySegments)->sortByDesc('count')->first();
        $unclassifiedMasteryStudents = max(0, $studentCount - $masteryTotal);
        $masteryInsight = $largestMastery
            ? $largestMastery['label'] . ' is the largest recorded FSL mastery group with ' . $largestMastery['count'] . ' of ' . $masteryTotal . ' classified students (' . $largestMastery['pct'] . '%).' . ($unclassifiedMasteryStudents > 0 ? ' Mastery level is not recorded for ' . $unclassifiedMasteryStudents . ' enrolled student(s).' : '')
            : 'No FSL mastery levels are recorded for this classroom.';
    @endphp
    <section class="cr-panel mt-4">
        <div class="cr-panel-heading">
            <div><p>ENGAGEMENT TREND</p><h3>Weekly Learning Activity</h3><small>Unique students with a recorded quiz or lesson activity, over the last 8 weeks</small></div>
            <span class="material-symbols-outlined">monitoring</span>
        </div>
        <div class="grid grid-cols-4 sm:grid-cols-8 gap-2 sm:gap-4 items-end h-44 pt-5">
            @foreach($metrics['activity_trend'] as $week)
                @php $barHeight = $week['active_count'] > 0 ? max(6, round($week['active_count'] / $trendMax * 100)) : 0; @endphp
                <div class="h-full flex flex-col items-center justify-end gap-2 min-w-0">
                    <span class="text-[9px] font-bold text-slate-500">{{ $week['active_count'] }}</span>
                    <div class="w-full max-w-10 h-28 flex items-end rounded-t-lg bg-slate-50 overflow-hidden">
                        <div class="w-full rounded-t-lg bg-gradient-to-t from-[#0d326b] to-blue-400" style="height:{{ $barHeight }}%"></div>
                    </div>
                    <span class="text-[8px] sm:text-[9px] font-semibold text-slate-400 whitespace-nowrap">{{ $week['label'] }}</span>
                </div>
            @endforeach
        </div>
        <div class="cr-chart-insight mt-3"><span class="material-symbols-outlined">lightbulb</span><p><strong>Trend Insight</strong>{{ $trendInsight }}</p></div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-4">
        <section class="cr-panel">
            <div class="cr-panel-heading">
                <div><p>STUDENT CLASSIFICATION</p><h3>Program Distribution</h3><small>Classification recorded for this school year</small></div>
                <span class="material-symbols-outlined">donut_small</span>
            </div>
            <div class="cr-analytics-donut-layout mt-5">
                <div class="cr-analytics-donut">
                    <svg class="w-full h-full overflow-visible" viewBox="0 0 160 160" role="img" aria-label="Program distribution donut chart">
                        <defs>
                            <filter id="crProgramShadow" x="-10%" y="-10%" width="120%" height="120%"><feDropShadow dx="0" dy="1.5" stdDeviation="1.5" flood-opacity="0.08"/></filter>
                            @foreach($programSegments as $segment)
                            <linearGradient id="{{ $segment['grad_id'] }}" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="{{ $segment['grad_from'] }}"/><stop offset="100%" stop-color="{{ $segment['grad_to'] }}"/></linearGradient>
                            @endforeach
                        </defs>
                        <circle cx="80" cy="80" r="54" fill="none" stroke="#f1f5f9" stroke-width="26" opacity="0.6"/>
                        @foreach($programSegments as $segment)
                        <path d="{{ $segment['path'] }}" fill="url(#{{ $segment['grad_id'] }})" stroke="#fff" stroke-width="2" stroke-linejoin="round" filter="url(#crProgramShadow)"/>
                        @endforeach
                        <circle cx="80" cy="80" r="39" fill="#fff" filter="url(#crProgramShadow)"/>
                    </svg>
                    <div class="cr-donut-center"><strong>{{ $programTotal }}</strong><span>STUDENTS</span></div>
                </div>
                <div class="cr-analytics-donut-legend">
                @foreach($programSegments as $segment)
                    <div class="cr-analytics-donut-legend-row"><span style="width:10px;height:10px;border-radius:50%;background:{{ $segment['color'] }};display:inline-block;flex-shrink:0"></span><span class="text-[12px] font-semibold text-slate-600 flex-1 truncate">{{ $segment['label'] }}</span><span class="text-[12px] font-black text-[#0d326b] flex-shrink-0">{{ $segment['count'] }} <span class="text-[10px] font-semibold text-slate-400">({{ $segment['pct'] }}%)</span></span></div>
                @endforeach
                </div>
            </div>
            <div class="cr-chart-insight mt-3"><span class="material-symbols-outlined">lightbulb</span><p><strong>Program Insight</strong>{{ $programInsight }}</p></div>
        </section>

        <section class="cr-panel">
            <div class="cr-panel-heading">
                <div><p>FSL MASTERY</p><h3>Student Learning Levels</h3><small>Recorded mastery level for enrolled students</small></div>
                <span class="material-symbols-outlined">signal_cellular_alt</span>
            </div>
            <div class="cr-analytics-donut-layout mt-5">
                <div class="cr-analytics-donut">
                    <svg class="w-full h-full overflow-visible" viewBox="0 0 160 160" role="img" aria-label="Student FSL mastery level donut chart">
                        <defs>
                            <filter id="crMasteryShadow" x="-10%" y="-10%" width="120%" height="120%"><feDropShadow dx="0" dy="1.5" stdDeviation="1.5" flood-opacity="0.08"/></filter>
                            @foreach($masterySegments as $segment)
                            <linearGradient id="{{ $segment['grad_id'] }}" x1="0%" y1="0%" x2="100%" y2="100%"><stop offset="0%" stop-color="{{ $segment['grad_from'] }}"/><stop offset="100%" stop-color="{{ $segment['grad_to'] }}"/></linearGradient>
                            @endforeach
                        </defs>
                        <circle cx="80" cy="80" r="54" fill="none" stroke="#f1f5f9" stroke-width="26" opacity="0.6"/>
                        @foreach($masterySegments as $segment)
                        <path d="{{ $segment['path'] }}" fill="url(#{{ $segment['grad_id'] }})" stroke="#fff" stroke-width="2" stroke-linejoin="round" filter="url(#crMasteryShadow)"/>
                        @endforeach
                        <circle cx="80" cy="80" r="39" fill="#fff" filter="url(#crMasteryShadow)"/>
                    </svg>
                    <div class="cr-donut-center"><strong>{{ $masteryTotal }}</strong><span>STUDENTS</span></div>
                </div>
                <div class="cr-analytics-donut-legend">
                @foreach($masterySegments as $segment)
                    <div class="cr-analytics-donut-legend-row"><span style="width:10px;height:10px;border-radius:50%;background:{{ $segment['color'] }};display:inline-block;flex-shrink:0"></span><span class="text-[12px] font-semibold text-slate-600 flex-1 truncate">{{ $segment['label'] }}</span><span class="text-[12px] font-black text-[#0d326b] flex-shrink-0">{{ $segment['count'] }} <span class="text-[10px] font-semibold text-slate-400">({{ $segment['pct'] }}%)</span></span></div>
                @endforeach
                </div>
            </div>
            <div class="cr-chart-insight mt-3"><span class="material-symbols-outlined">lightbulb</span><p><strong>Mastery Insight</strong>{{ $masteryInsight }}</p></div>
        </section>
    </div>

    <section class="cr-panel p-0 overflow-hidden mt-4">
        <div class="px-4 sm:px-5 py-4 border-b border-slate-100 flex items-center justify-between gap-3">
            <div><p class="text-[9px] uppercase tracking-[.12em] font-extrabold text-slate-400">SUPPORT PRIORITY</p><h3 class="text-[15px] font-black text-[#0d326b] mt-0.5">Students Who May Need Support</h3><small class="text-[10px] text-slate-400">Ranked by inactivity, quiz results, and lesson completion</small></div>
            <span class="rounded-full bg-amber-50 text-amber-800 px-3 py-1.5 text-[10px] font-bold flex-shrink-0">{{ $metrics['students_needing_support'] }} flagged</span>
        </div>
        @if($strugglingStudents->isNotEmpty())
        <div class="overflow-x-auto">
            <table class="w-full min-w-[750px] text-left">
                <thead class="bg-slate-50 text-[9px] uppercase tracking-wider text-slate-400"><tr><th class="px-4 py-3 w-14">Rank</th><th class="px-3 py-3">Student</th><th class="px-3 py-3">Quiz Avg.</th><th class="px-3 py-3">Lessons</th><th class="px-3 py-3">Activity</th><th class="px-4 py-3">Support Signals</th></tr></thead>
                <tbody class="divide-y divide-slate-50">
                    @foreach($strugglingStudents as $student)
                    <tr class="hover:bg-slate-50/70" data-student-id="{{ $student['student_id'] }}">
                        <td class="px-4 py-3"><span class="w-7 h-7 inline-flex items-center justify-center rounded-lg bg-blue-50 text-[#0d326b] text-[10px] font-black">{{ $student['rank'] }}</span></td>
                        <td class="px-3 py-3"><p class="text-[11px] font-bold text-slate-800">{{ $student['name'] }}</p><p class="text-[9px] text-slate-400">Grade {{ $student['grade_level'] ?? '—' }}{{ $student['section'] ? ' · '.$student['section'] : '' }}</p></td>
                        <td class="px-3 py-3 text-[11px] font-bold {{ $student['quiz_average'] !== null && $student['quiz_average'] < 75 ? 'text-amber-700' : 'text-slate-600' }}">{{ $student['quiz_average'] === null ? 'No data' : $student['quiz_average'].'%' }}<span class="block text-[9px] text-slate-400 font-medium">{{ $student['quiz_count'] }} quizzes</span></td>
                        <td class="px-3 py-3 text-[11px] font-bold text-slate-600">{{ $student['completion_rate'] === null ? 'No assignments' : $student['completion_rate'].'%' }}@if($student['completion_rate'] !== null)<span class="block text-[9px] text-slate-400 font-medium">{{ $student['completed_assignments'] }}/{{ $student['total_assignments'] }} done</span>@endif</td>
                        <td class="px-3 py-3"><span class="text-[10px] font-bold {{ $student['active'] ? 'text-blue-700' : 'text-slate-400' }}">{{ $student['active'] ? 'Active' : 'Inactive' }}</span><span class="block text-[9px] text-slate-400">{{ $student['last_activity'] ?? 'No activity recorded' }}</span></td>
                        <td class="px-4 py-3"><div class="flex flex-wrap gap-1">@foreach($student['concerns'] as $concern)<span class="rounded-full bg-amber-50 text-amber-800 border border-amber-100 px-2 py-1 text-[9px] font-semibold">{{ $concern }}</span>@endforeach</div></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="px-5 py-12 text-center"><span class="material-symbols-outlined text-[40px] text-blue-200">check_circle</span><p class="mt-2 text-[12px] font-bold text-slate-600">No students are currently flagged for extra support.</p><p class="mt-1 text-[10px] text-slate-400">Activity uses the last 7 days; academic measures use the current school year.</p></div>
        @endif
    </section>
    <p class="text-[9px] text-slate-400 px-1">Activity is based on the student’s recorded last activity date in the past 7 days. Quiz passing threshold: 75%.</p>
</div>

<style>
.cr-report-space{padding:0}
.cr-kpi{position:relative;overflow:hidden;background:#fff;border:1px solid #edf2f7;border-radius:18px;padding:15px 16px;box-shadow:0 2px 10px rgba(13,50,107,.03)}
.cr-kpi-primary{background:linear-gradient(135deg,#0d326b,#1e4b8f 55%,#1a6fd4);border-color:transparent}
.cr-kpi>span{display:block;color:#94a3b8;font-size:9px;font-weight:800;letter-spacing:.07em;text-transform:uppercase}
.cr-kpi-primary>span,.cr-kpi-primary>small{color:rgba(255,255,255,.7)}
.cr-kpi>strong{display:block;margin-top:9px;color:#0d326b;font-size:25px;line-height:1.1;font-weight:900;white-space:nowrap}
.cr-kpi-primary>strong{color:#fff}
.cr-kpi>strong small{font-size:12px;color:#64748b}
.cr-kpi>small{display:block;margin-top:4px;color:#64748b;font-size:9px;font-weight:600}
.cr-panel{background:#fff;border:1px solid #edf2f7;border-radius:20px;padding:17px 19px;box-shadow:0 2px 12px rgba(13,50,107,.03)}
.cr-panel-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:12px;padding-bottom:13px;border-bottom:1px solid #f1f5f9}
.cr-panel-heading p{font-size:8px;font-weight:800;letter-spacing:.12em;color:#94a3b8}
.cr-panel-heading h3{font-size:14px;font-weight:900;color:#0d326b;margin-top:2px}
.cr-panel-heading small{font-size:10px;color:#94a3b8}
.cr-panel-heading>.material-symbols-outlined{font-size:21px;color:#cbd5e1}
.cr-analytics-donut-layout{display:flex;align-items:center;flex:1;justify-content:center;gap:24px;padding:8px 0;min-height:190px}
.cr-analytics-donut{position:relative;width:144px;height:144px;flex-shrink:0;display:flex;align-items:center;justify-content:center}
.cr-analytics-donut svg{width:100%;height:100%;overflow:visible}
.cr-donut-center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none;text-align:center}
.cr-donut-center strong{font-size:24px;font-weight:900;line-height:1;color:#0d326b}
.cr-donut-center span{margin-top:2px;font-size:8.5px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#94a3b8}
.cr-analytics-donut-legend{display:flex;flex-direction:column;gap:12px;flex:1;min-width:0;max-width:250px}
.cr-analytics-donut-legend-row{display:flex;align-items:center;gap:9px;min-width:0}
.cr-chart-insight{display:flex;align-items:flex-start;gap:9px;padding:10px 12px;border:1px solid #fbbf24;border-radius:14px;background:linear-gradient(135deg,#fffdf8 0%,#fefce8 100%);color:#78350f}
.cr-chart-insight>.material-symbols-outlined{width:26px;height:26px;flex:0 0 26px;display:flex;align-items:center;justify-content:center;border-radius:9px;background:linear-gradient(135deg,#f59e0b,#facc15 60%,#fbbf24);font-size:15px;color:#78350f}
.cr-chart-insight p{font-size:10px;line-height:1.5;font-weight:500}
.cr-chart-insight p strong{display:block;margin-bottom:1px;font-size:9px;font-weight:900;letter-spacing:.06em;text-transform:uppercase;color:#b45309}
@media(max-width:520px){.cr-analytics-donut-layout{gap:12px}.cr-analytics-donut{width:132px;height:132px}.cr-analytics-donut-legend{gap:10px}.cr-analytics-donut-legend-row{gap:6px}.cr-analytics-donut-legend-row .text-\[12px\]{font-size:10px}}
</style>
