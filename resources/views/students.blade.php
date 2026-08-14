@extends('layouts.app')
@section('title', 'Students')
@section('content')

<style>
/* ── Level color tokens — navy scale only ─────────── */
:root {
    --clr-beginner:     #93c5fd;
    --clr-intermediate: #3b82f6;
    --clr-advanced:     #1e4b8f;
    --clr-completed:    #0d326b;
    --clr-navy:         #0d326b;
}

/* ── Stat cards ───────────────────────────────────── */
.stat-card { border-radius: 20px; padding: 20px; position: relative; overflow: hidden; transition: transform .2s, box-shadow .2s; }
.stat-card:hover { transform: translateY(-2px); box-shadow: 0 12px 32px rgba(13,50,107,.12); }

/* ── Level badge pill — light to dark navy by tier ── */
.lvl-badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 10px; border-radius: 9999px; font-size: 10px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
.lvl-badge.beginner     { background: #eff6ff; color: #1e4b8f; }
.lvl-badge.intermediate { background: #dbeafe; color: #0d326b; }
.lvl-badge.advanced     { background: #93c5fd; color: #0d326b; }
.lvl-badge.completed    { background: #0d326b; color: #ffffff; }

/* ── XP Progress bar ──────────────────────────────── */
.xp-bar-wrap { border-radius: 9999px; background: #f1f5f9; overflow: hidden; height: 10px; }
.xp-bar-fill { height: 100%; border-radius: 9999px; transition: width .7s cubic-bezier(.4,0,.2,1); background: linear-gradient(90deg, #93c5fd 0%, #1e4b8f 100%); }
.xp-eligible-chip { display: inline-flex; align-items: center; gap: 3px; background: #dbeafe; color: #0d326b; font-size: 9px; font-weight: 800; padding: 2px 7px; border-radius: 9999px; letter-spacing: .05em; animation: pulse-chip 2s infinite; }
@keyframes pulse-chip { 0%,100%{opacity:1} 50%{opacity:.7} }

/* ── Promote/Demote buttons ───────────────────────── */
.promo-btn-eligible  { background: linear-gradient(135deg, #1a6fd4, #0d326b); color:#fff; border: none; }
.promo-btn-eligible:hover  { box-shadow: 0 4px 16px rgba(13,50,107,.35); transform: scale(1.04); }
.promo-btn-locked    { background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; }
.promo-btn-locked:hover    { background: #f1f5f9; border-color: #cbd5e1; }
.promo-btn-completed { background: #eff6ff; color: #0d326b; border: none; }

.promo-btn { display: inline-flex; align-items: center; gap: 5px; border-radius: 10px; font-size: 11px; font-weight: 700; cursor: pointer; transition: all .2s; white-space: nowrap; }

/* ── Demote button ────────────────────────────────── */
.demote-btn { display: inline-flex; align-items: center; gap: 5px; border-radius: 10px; font-size: 11px; font-weight: 700; cursor: pointer; transition: all .2s; white-space: nowrap; background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; }
.demote-btn:hover { background: #fee2e2; border-color: #fca5a5; transform: scale(1.04); }

/* ── Promotion/demotion history item ──────────────── */
.hist-item { display: flex; align-items: flex-start; gap: 10px; padding: 10px 12px; border-radius: 12px; background: #f8fafc; border: 1px solid #e2e8f0; margin-bottom: 8px; }
.hist-dot  { width: 8px; height: 8px; border-radius: 50%; margin-top: 4px; flex-shrink: 0; }

/* ── Ready to Promote KPI (golden gradient like Senya tip) ── */
.kpi-ready-promote {
    background: linear-gradient(135deg, #f59e0b 0%, #facc15 50%, #fbbf24 100%);
}

/* ── Results loading state (used while AJAX-fetching filtered/paginated data) ── */
#students-results.is-loading {
    opacity: .5;
    pointer-events: none;
    transition: opacity .15s;
}

/* ── Assignment Modal Styles ─────────────────────────────────────────── */
#assignment-modal .module-checkbox:indeterminate {
    background-color: #0d326b;
    opacity: 0.5;
}

#assignment-list .border {
    transition: box-shadow 0.2s ease;
}

#assignment-list .border:hover {
    box-shadow: 0 4px 12px rgba(13, 50, 107, 0.08);
}
</style>

<div class="space-y-6">

    {{-- ══════════ STAT CARDS ══════════ --}}
    @php
        $allStudents   = $students->getCollection();
        $beginnerCnt   = $allStudents->where('fsl_mastery_level','Beginner')->count();
        $intermCnt     = $allStudents->where('fsl_mastery_level','Intermediate')->count();
        $advancedCnt   = $allStudents->where('fsl_mastery_level','Advanced')->count();
        $completedCnt  = $allStudents->where('fsl_mastery_level','Completed')->count();
        $avgXp         = $allStudents->count() ? round($allStudents->avg('total_xp')) : 0;
        $progressPct   = min(100, round($avgXp / 1000 * 100));
        $readyToPromote = $allStudents->filter(function($s) {
            $xp = $s->total_xp ?? 0; $lvl = $s->fsl_mastery_level;
            return ($lvl==='Beginner'&&$xp>=300)||($lvl==='Intermediate'&&$xp>=600)||($lvl==='Advanced'&&$xp>=1000);
        })->count();
    @endphp

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">

        {{-- Total Students --}}
        <div class="stat-card text-white" style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 55%,#1a6fd4 100%)">
            <div class="absolute -top-7 -right-7 w-28 h-28 bg-white/5 rounded-full"></div>
            <div class="absolute -bottom-6 -left-6 w-20 h-20 bg-white/5 rounded-full"></div>
            <div class="relative z-10 flex items-center justify-between">
                <div>
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="material-symbols-outlined text-white/50 text-[15px]">group</span>
                        <p class="text-[10px] font-bold text-white/50 uppercase tracking-widest">Total Students</p>
                    </div>
                    <p class="text-[46px] font-black text-white leading-none">{{ $totalStudents }}</p>
                    <p class="text-[11px] font-semibold text-[#facc15] mt-2">+{{ $newThisWeek }} this month</p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-white/10 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-white text-[28px]">school</span>
                </div>
            </div>
        </div>

        {{-- Average XP --}}
        <div class="stat-card bg-white border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-amber-400 text-[16px]">bolt</span>
                    <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Avg. XP per Student</p>
                </div>
                <span class="text-[10px] font-bold text-[#0d326b] bg-[#e8eef8] px-2 py-0.5 rounded-full">{{ $progressPct }}% to 1K</span>
            </div>
            <p class="text-[40px] font-black text-[#0d326b] leading-none">{{ number_format($avgXp) }}</p>
            <p class="text-[10px] text-slate-400 font-medium mt-0.5 mb-3">experience points</p>
            <div class="xp-bar-wrap">
                <div class="xp-bar-fill" style="width:{{ $progressPct }}%;background:linear-gradient(90deg,#f59e0b,#1a6fd4)"></div>
            </div>
            <div class="flex justify-between mt-1">
                <span class="text-[9px] text-slate-300">0 XP</span>
                <span class="text-[9px] text-slate-400 font-medium">1,000 XP</span>
            </div>
        </div>

        {{-- Ready to Promote — golden gradient like Senya tip --}}
        <div class="stat-card kpi-ready-promote">
            <div class="absolute -top-7 -right-7 w-28 h-28 bg-[#0d326b]/5 rounded-full"></div>
            <div class="flex items-center justify-between relative z-10">
                <div>
                    <div class="flex items-center gap-1.5 mb-2">
                        <span class="material-symbols-outlined text-amber-700/60 text-[15px]">workspace_premium</span>
                        <p class="text-[10px] font-bold text-amber-800/70 uppercase tracking-widest">Ready to Promote</p>
                    </div>
                    <p class="text-[46px] font-black text-[#92400e] leading-none">{{ $readyToPromote }}</p>
                    <p class="text-[11px] font-semibold text-amber-800 mt-2">
                        student{{ $readyToPromote !== 1 ? 's' : '' }} eligible for next level
                    </p>
                </div>
                <div class="w-14 h-14 rounded-2xl bg-amber-500/20 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-amber-700 text-[28px]">trending_up</span>
                </div>
            </div>
        </div>
    </div>


    {{-- ══════════ MAIN CONTENT ══════════ --}}
    <div class="flex flex-col lg:flex-row gap-5 items-start">

        {{-- ── LEFT: Table Panel ── --}}
        <div class="flex-1 min-w-0 w-full">

            {{-- Filter Toolbar with Add Student button integrated --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm px-5 py-3.5 mb-4 flex items-center gap-3 flex-wrap">
                @php
                    $sf = session('students_filters', []);
                    $hasActiveFilters = false;
                    foreach ($sf as $k => $v) {
                        if ($k === 'status') {
                            if ($v !== 'active') $hasActiveFilters = true;
                        } else {
                            if (!empty($v)) $hasActiveFilters = true;
                        }
                    }
                @endphp
                <div class="relative shrink-0 order-1 lg:order-none">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[18px]">search</span>
                    <input id="student-search" type="text" value="{{ $sf['search'] ?? '' }}" placeholder="Search students..." class="bg-[#f1f5f9] text-[13px] font-medium py-2.5 pl-9 pr-4 rounded-full outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400 w-[250px]" />
                </div>

                <div class="flex-1"></div>
                <div class="relative shrink-0">
                    <select id="filter-school-year" class="appearance-none bg-[#f1f5f9] text-[#1e293b] text-[12px] font-semibold py-2.5 pl-4 pr-9 rounded-full outline-none border border-transparent hover:bg-slate-200 transition-colors cursor-pointer">
                        <option value="">School Year</option>
                        @foreach($availableSchoolYears as $sy)
                        <option value="{{ $sy }}" {{ ($sf['school_year'] ?? '') === $sy ? 'selected' : '' }}>{{ $sy }}</option>
                        @endforeach
                    </select>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[16px] text-slate-500 pointer-events-none">expand_more</span>
                </div>
                <div class="relative shrink-0">
                    <select id="filter-level" class="appearance-none bg-[#f1f5f9] text-[#1e293b] text-[12px] font-semibold py-2.5 pl-4 pr-9 rounded-full outline-none border border-transparent hover:bg-slate-200 transition-colors cursor-pointer">
                        <option value="">Level</option>
                        <option value="Beginner" {{ ($sf['level'] ?? '') === 'Beginner' ? 'selected' : '' }}>Beginner</option>
                        <option value="Intermediate" {{ ($sf['level'] ?? '') === 'Intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="Advanced" {{ ($sf['level'] ?? '') === 'Advanced' ? 'selected' : '' }}>Advanced</option>
                        <option value="Completed" {{ ($sf['level'] ?? '') === 'Completed' ? 'selected' : '' }}>Completed</option>
                    </select>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[16px] text-slate-500 pointer-events-none">expand_more</span>
                </div>
                <div class="relative shrink-0">
                    <select id="filter-program" class="appearance-none bg-[#f1f5f9] text-[#1e293b] text-[12px] font-semibold py-2.5 pl-4 pr-9 rounded-full outline-none border border-transparent hover:bg-slate-200 transition-colors cursor-pointer">
                        <option value="">Program Type</option>
                        <option value="Regular" {{ ($sf['program'] ?? '') === 'Regular' ? 'selected' : '' }}>Regular</option>
                        <option value="Self-contained" {{ ($sf['program'] ?? '') === 'Self-contained' ? 'selected' : '' }}>Self-Contained</option>
                        <option value="Transition" {{ ($sf['program'] ?? '') === 'Transition' ? 'selected' : '' }}>Transition</option>
                        <option value="Inclusion" {{ ($sf['program'] ?? '') === 'Inclusion' ? 'selected' : '' }}>Inclusion</option>
                    </select>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[16px] text-slate-500 pointer-events-none">expand_more</span>
                </div>
                <div class="relative shrink-0">
                    <select id="filter-status" class="appearance-none bg-[#f1f5f9] text-[#1e293b] text-[12px] font-semibold py-2.5 pl-4 pr-9 rounded-full outline-none border border-transparent hover:bg-slate-200 transition-colors cursor-pointer">
                        <option value="active" {{ ($sf['status'] ?? 'active') === 'active' ? 'selected' : '' }}>Enrolled</option>
                        <option value="inactive" {{ ($sf['status'] ?? 'active') === 'inactive' ? 'selected' : '' }}>Unenrolled</option>
                        <option value="all" {{ ($sf['status'] ?? 'active') === 'all' ? 'selected' : '' }}>All Statuses</option>
                    </select>
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[16px] text-slate-500 pointer-events-none">expand_more</span>
                </div>
                {{-- Clear filter (client-side toggled, AJAX-cleared — no page reload) --}}
                <span id="clear-filters-wrap" class="{{ !$hasActiveFilters ? 'hidden' : '' }}">
                    <button type="button" id="clear-filters-btn" class="px-4 py-2 rounded-full border border-slate-200 bg-white text-slate-500 text-[13px] font-semibold transition-all hover:bg-slate-50 hover:border-slate-300">Clear</button>
                </span>
                {{-- Add Student Button moved here --}}
                <button id="open-modal-btn" title="Add Student"
                    class="bg-gradient-to-r from-[#0d326b] via-[#1e4b8f] to-[#1a6fd4] hover:opacity-90 text-white px-3 lg:px-5 py-2.5 rounded-xl text-[13px] font-bold transition-all flex items-center gap-2 shadow-sm border border-[#0d326b]/20 shrink-0">
                    <span class="material-symbols-outlined icon-outline text-[18px]">person_add</span>
                    <span class="hidden lg:inline">Add Student</span>
                </button>
            </div>

            {{-- Hidden form: only used to carry the CSRF token for AJAX requests below --}}
            <form id="studentFilterForm" style="display:none">
                @csrf
            </form>

            {{-- ══════════ RESULTS (table + pagination) — this whole block gets
                 swapped in place by JS on filter/search/pagination changes,
                 so the page never reloads. ══════════ --}}
            <div id="students-results">
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
                    <table class="w-full text-left border-collapse" style="table-layout:fixed" id="students-table">
                        <colgroup>
                            <col style="width:30%">
                            <col style="width:18%">
                            <col style="width:28%">
                            <col style="width:13%">
                            <col style="width:11%">
                        </colgroup>
                        <thead>
                            <tr class="border-b border-slate-100 bg-slate-50/60">
                                <th class="py-4 px-3 md:px-5 text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase truncate">Student</th>
                                <th class="py-4 px-3 md:px-5 text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase truncate">Level & Status</th>
                                <th class="py-4 px-3 md:px-5 text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase truncate">XP Progress</th>
                                <th class="py-4 px-3 md:px-5 text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase truncate">Enrolled</th>
                                <th class="py-4 px-3 md:px-5 text-[10px] font-bold text-slate-400 tracking-[0.1em] uppercase text-right truncate">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50" id="student-tbody">
                            @forelse($students as $student)
                            @php
                                $xp    = $student->total_xp ?? 0;
                                $lvl   = $student->fsl_mastery_level;
                                $m1=300; $m2=600; $m3=1000;
                                $promoteTo=null; $promoteXp=null; $enoughXp=false;
                                if      ($lvl==='Beginner')     { $promoteTo='Intermediate'; $promoteXp=$m1; $enoughXp=$xp>=$m1; }
                                elseif  ($lvl==='Intermediate') { $promoteTo='Advanced';     $promoteXp=$m2; $enoughXp=$xp>=$m2; }
                                elseif  ($lvl==='Advanced')     { $promoteTo='Completed';    $promoteXp=$m3; $enoughXp=$xp>=$m3; }

                                // Demotion: can demote from any level except Beginner
                                $canDemote = $lvl !== 'Beginner';
                                $demoteTo = match($lvl) {
                                    'Intermediate' => 'Beginner',
                                    'Advanced' => 'Intermediate',
                                    'Completed' => 'Advanced',
                                    default => null
                                };

                                // Bar calculations (relative to next threshold)
                                if      ($lvl==='Beginner')     { $barMax=$m1; $barXp=min($xp,$m1); }
                                elseif  ($lvl==='Intermediate') { $barMax=$m2; $barXp=min($xp,$m2); }
                                elseif  ($lvl==='Advanced')     { $barMax=$m3; $barXp=min($xp,$m3); }
                                else                            { $barMax=$m3; $barXp=$m3; }
                                $barPct = $barMax>0 ? min(100,round($barXp/$barMax*100)) : 100;

                                $levelMeta = match($lvl) {
                                    'Intermediate' => ['barColor'=>'#3b82f6','class'=>'intermediate','icon'=>'bolt'],
                                    'Advanced'     => ['barColor'=>'#10b981','class'=>'advanced',    'icon'=>'military_tech'],
                                    'Completed'    => ['barColor'=>'#a855f7','class'=>'completed',   'icon'=>'verified'],
                                    default        => ['barColor'=>'#f59e0b','class'=>'beginner',    'icon'=>'person'],
                                };
                                $statusColor = match($student->status) {
                                    'active'   => ['dot'=>'bg-emerald-400','text'=>'text-emerald-600'],
                                    'inactive' => ['dot'=>'bg-slate-300',  'text'=>'text-slate-400'],
                                    default    => ['dot'=>'bg-red-300',    'text'=>'text-red-400'],
                                };
                                // Promotion/demotion history for this student (already eager-loaded)
                                $promoHistory = $student->promotions ?? collect();
                            @endphp
                            <tr class="hover:bg-slate-50/60 transition-colors group student-row"
                                data-student-id="{{ $student->student_id }}"
                                data-status="{{ $student->status }}"
                                data-program="{{ $student->program_type ?? '' }}"
                                data-mastery="{{ $lvl }}"
                                data-name="{{ strtolower($student->first_name . ' ' . $student->last_name) }}">

                                {{-- Student Profile --}}
                                <td class="py-4 px-3 md:px-5 overflow-hidden">
                                    <div class="flex items-center gap-3 min-w-0">
                                        <div class="w-10 h-10 rounded-full shadow-sm shrink-0 overflow-hidden ring-2 ring-slate-100">
                                            <img src="https://ui-avatars.com/api/?name={{ urlencode($student->first_name.'+'.$student->last_name) }}&background=0d326b&color=fff&rounded=true&size=60" class="w-9 h-9 rounded-full ring-2 ring-slate-100" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="text-[13px] font-bold text-[#0d326b] truncate" title="{{ $student->first_name }} {{ $student->last_name }}">{{ $student->first_name }} {{ $student->last_name }}</p>
                                            <p class="text-[10px] text-slate-400 font-medium mt-0.5 truncate">LRN: {{ $student->lrn ?? 'N/A' }}@if($student->grade_level) &middot; {{ $student->grade_level }}@endif</p>
                                            <div class="flex items-center gap-1 mt-0.5">
                                                <span class="w-1.5 h-1.5 rounded-full shrink-0 {{ $statusColor['dot'] }}"></span>
                                                <span class="text-[10px] font-semibold {{ $statusColor['text'] }} uppercase truncate">{{ $student->status }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Level & Promo status --}}
                                <td class="py-4 px-3 md:px-5 overflow-hidden">
                                    <div class="flex flex-col gap-1.5">
                                        <span class="lvl-badge {{ $levelMeta['class'] }}">
                                            <span class="material-symbols-outlined text-[11px]">{{ $levelMeta['icon'] }}</span>
                                            <span>{{ $lvl }}</span>
                                        </span>
                                        @if($enoughXp && $promoteTo)
                                            <span class="xp-eligible-chip">
                                                <span class="material-symbols-outlined text-[9px]">arrow_upward</span>
                                                Eligible for {{ $promoteTo }}
                                            </span>
                                        @elseif($lvl === 'Completed')
                                            <span style="font-size:9px;color:#7e22ce;font-weight:700">🏆 Completed</span>
                                        @else
                                            <span class="text-[9px] text-slate-400 font-medium">Lv.{{ $student->level ?? 1 }}</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- XP Progress --}}
                                <td class="py-4 px-3 md:px-5 overflow-hidden">
                                    <div class="w-full">
                                        <div class="flex items-center justify-between mb-1.5 gap-1 flex-wrap">
                                            <span class="text-[12px] font-black text-[#0d326b] whitespace-nowrap">{{ number_format($xp) }} <span class="text-[9px] font-semibold text-slate-400">XP</span></span>
                                            @if($promoteTo)
                                                <span class="text-[9px] font-semibold whitespace-nowrap {{ $enoughXp ? 'text-emerald-600' : 'text-slate-400' }}">
                                                    @if($enoughXp)
                                                        ✓ {{ number_format($promoteXp) }}
                                                    @else
                                                        {{ number_format($promoteXp - $xp) }} to go
                                                    @endif
                                                </span>
                                            @else
                                                <span class="text-[9px] text-purple-500 font-bold whitespace-nowrap">MAX ★</span>
                                            @endif
                                        </div>
                                        <div class="xp-bar-wrap">
                                            <div class="xp-bar-fill" style="width:{{ $barPct }}%;"></div>
                                        </div>
                                        <div class="flex justify-between mt-1">
                                            <span class="text-[9px] text-slate-300">0</span>
                                            <span class="text-[9px] font-bold whitespace-nowrap {{ $enoughXp ? 'text-emerald-500' : 'text-slate-300' }}">
                                                @if($promoteTo) {{ number_format($promoteXp) }} XP @else MAX @endif
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Enrolled --}}
                                <td class="py-4 px-3 md:px-5 overflow-hidden">
                                    <p class="text-[12px] font-medium text-[#1e293b] truncate">{{ $student->created_at ? $student->created_at->diffForHumans() : 'N/A' }}</p>
                                    <p class="text-[10px] text-slate-400 mt-0.5 truncate">{{ $student->created_at ? $student->created_at->format('M d, Y') : '' }}</p>
                                </td>

                                {{-- Actions --}}
                                <td class="py-4 px-3 md:px-5 text-right overflow-hidden">
                                    <button class="view-student-btn inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-[12px] font-bold transition-all bg-[#0d326b] hover:bg-[#154188] text-white shadow-sm"
                                        data-student-id="{{ $student->student_id }}"
                                        title="View student details">
                                        <span class="material-symbols-outlined icon-outline text-[15px]">person</span>
                                        <span>View</span>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr id="empty-state-row">
                                <td colspan="5">
                                    <div class="flex flex-col items-center justify-center py-16 text-center">
                                        <div class="w-16 h-16 rounded-2xl bg-[#e8eef8] flex items-center justify-center mb-4">
                                            <span class="material-symbols-outlined text-[#0d326b] text-[32px]">group_off</span>
                                        </div>
                                        <p class="text-[16px] font-bold text-[#0d326b] mb-1">No students yet</p>
                                        <p class="text-[13px] text-slate-400 font-medium mb-5">Add your first student to get started.</p>
                                        <button onclick="document.getElementById('open-modal-btn').click()" class="bg-[#0d326b] hover:bg-[#154188] text-white px-5 py-2.5 rounded-xl text-[13px] font-semibold transition-colors">Add Student</button>
                                    </div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div id="no-filter-results" class="hidden py-12 text-center">
                        <p class="text-[14px] font-bold text-slate-400 mb-1">No students match your filters</p>
                        <p class="text-[12px] text-slate-400">Try adjusting the search or filter options above.</p>
                    </div>

                    @if($students->hasPages())
                    <div class="px-6 py-4 border-t border-slate-100">{{ $students->links('pagination::tailwind') }}</div>
                    @endif
                </div>
            </div>
            {{-- /#students-results --}}

        </div>

        {{-- ── RIGHT SIDEBAR ── --}}
        <div class="w-full lg:w-[260px] shrink-0 space-y-4">

            {{-- FSL Mastery Donut (updated with gradients & tooltips like dashboard) --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">FSL Mastery Distribution</p>
                @php
                    $total   = $allStudents->count() ?: 1;
                    $begPct  = round($beginnerCnt  / $total * 100);
                    $intPct  = round($intermCnt    / $total * 100);
                    $advPct  = round($advancedCnt  / $total * 100);
                    $comPct  = round($completedCnt / $total * 100);
                    $circ    = 251.3;
                    $begDash = round($begPct / 100 * $circ, 1);
                    $intDash = round($intPct / 100 * $circ, 1);
                    $advDash = round($advPct / 100 * $circ, 1);
                    $comDash = round($comPct / 100 * $circ, 1);
                    $begOff  = 0; $intOff = -$begDash;
                    $advOff  = -($begDash + $intDash);
                    $comOff  = -($begDash + $intDash + $advDash);

                    $donutSegments = [
                        ['count' => $beginnerCnt, 'pct' => $begPct, 'dash' => $begDash, 'offset' => $begOff, 'label' => 'Beginner', 'grad_id' => 'masteryGradBeginner', 'grad_from' => '#93c5fd', 'grad_to' => '#3b82f6'],
                        ['count' => $intermCnt, 'pct' => $intPct, 'dash' => $intDash, 'offset' => $intOff, 'label' => 'Intermediate', 'grad_id' => 'masteryGradIntermediate', 'grad_from' => '#3b82f6', 'grad_to' => '#1e4b8f'],
                        ['count' => $advancedCnt, 'pct' => $advPct, 'dash' => $advDash, 'offset' => $advOff, 'label' => 'Advanced', 'grad_id' => 'masteryGradAdvanced', 'grad_from' => '#1e4b8f', 'grad_to' => '#0d326b'],
                        ['count' => $completedCnt, 'pct' => $comPct, 'dash' => $comDash, 'offset' => $comOff, 'label' => 'Completed', 'grad_id' => 'masteryGradCompleted', 'grad_from' => '#0d326b', 'grad_to' => '#071c3f'],
                    ];
                @endphp
                <div class="flex items-center justify-center mb-4">
                    <div class="relative w-[120px] h-[120px]">
                        <div id="masteryDonutTooltip" class="pointer-events-none absolute z-20 opacity-0 transition-opacity duration-150 left-1/2 top-1/2 -translate-x-1/2 -translate-y-[130%] bg-[#0d326b] text-white text-[11px] font-bold px-3 py-1.5 rounded-lg shadow-lg whitespace-nowrap">
                            <span id="masteryDonutTooltipLabel"></span>: <span id="masteryDonutTooltipValue"></span>
                            <div class="absolute left-1/2 -bottom-1 -translate-x-1/2 w-2 h-2 bg-[#0d326b] rotate-45"></div>
                        </div>
                        <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                            <defs>
                                @foreach($donutSegments as $seg)
                                <linearGradient id="{{ $seg['grad_id'] }}" x1="0%" y1="0%" x2="100%" y2="100%">
                                    <stop offset="0%" stop-color="{{ $seg['grad_from'] }}"/>
                                    <stop offset="100%" stop-color="{{ $seg['grad_to'] }}"/>
                                </linearGradient>
                                @endforeach
                            </defs>
                            <circle cx="50" cy="50" r="40" fill="none" stroke="#f1f5f9" stroke-width="14"/>
                            @foreach($donutSegments as $seg)
                                @if($seg['dash'] > 0)
                                <circle class="mastery-donut-hit" cx="50" cy="50" r="40" fill="transparent"
                                        stroke="url(#{{ $seg['grad_id'] }})"
                                        stroke-width="14"
                                        stroke-dasharray="{{ $seg['dash'] }} {{ $circ - $seg['dash'] }}"
                                        stroke-dashoffset="{{ $seg['offset'] }}"
                                        stroke-linecap="round"
                                        style="cursor:pointer"
                                        data-label="{{ $seg['label'] }}"
                                        data-value="{{ $seg['count'] }} ({{ $seg['pct'] }}%)"></circle>
                                @endif
                            @endforeach
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                            <span class="text-[22px] font-black text-[#0d326b]">{{ $allStudents->count() }}</span>
                            <span class="text-[9px] font-bold text-slate-400 uppercase tracking-widest">Students</span>
                        </div>
                    </div>
                </div>

                {{-- Legend with gradient bars --}}
                <div class="space-y-2.5">
                    @foreach([
                        ['label'=>'Beginner', 'pct'=>$begPct, 'cnt'=>$beginnerCnt, 'grad'=>'masteryGradBeginner'],
                        ['label'=>'Intermediate', 'pct'=>$intPct, 'cnt'=>$intermCnt, 'grad'=>'masteryGradIntermediate'],
                        ['label'=>'Advanced', 'pct'=>$advPct, 'cnt'=>$advancedCnt, 'grad'=>'masteryGradAdvanced'],
                        ['label'=>'Completed', 'pct'=>$comPct, 'cnt'=>$completedCnt, 'grad'=>'masteryGradCompleted'],
                    ] as $e)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background: {{ match($e['label']) {
                                'Beginner' => '#93c5fd',
                                'Intermediate' => '#3b82f6',
                                'Advanced' => '#1e4b8f',
                                'Completed' => '#0d326b',
                                default => '#cbd5e1'
                            } }}"></span>
                            <span class="text-[11px] font-medium text-slate-600">{{ $e['label'] }}</span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span class="text-[11px] text-slate-400">{{ $e['cnt'] }}</span>
                            <span class="text-[11px] font-bold text-slate-500 w-8 text-right">{{ $e['pct'] }}%</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- XP Milestones --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Promotion Thresholds</p>
                <div class="space-y-3">
                    @php
                        $milestones = [
                            ['from'=>'Beginner','to'=>'Intermediate','xp'=>300,'bg'=>'#ebf4ff','border'=>'#c7d2fe','iconBg'=>'#3b82f6','text'=>'#1e40af','ready'=>$allStudents->where('fsl_mastery_level','Beginner')->filter(fn($s)=>($s->total_xp??0)>=300)->count()],
                            ['from'=>'Intermediate','to'=>'Advanced','xp'=>600,'bg'=>'#dbeafe','border'=>'#93c5fd','iconBg'=>'#1d4ed8','text'=>'#1e40af','ready'=>$allStudents->where('fsl_mastery_level','Intermediate')->filter(fn($s)=>($s->total_xp??0)>=600)->count()],
                            ['from'=>'Advanced','to'=>'Completed','xp'=>1000,'bg'=>'#c7d2fe','border'=>'#6366f1','iconBg'=>'#0d3b82','text'=>'#0d316d','ready'=>$allStudents->where('fsl_mastery_level','Advanced')->filter(fn($s)=>($s->total_xp??0)>=1000)->count()],
                        ];
                    @endphp
                    @foreach($milestones as $ms)
                    <div class="flex items-center justify-between p-3 rounded-xl border" style="background:{{ $ms['bg'] }};border-color:{{ $ms['border'] }}">
                        <div class="flex items-center space-x-2">
                            <span class="w-6 h-6 rounded-lg flex items-center justify-center text-white text-[11px] font-black" style="background:{{ $ms['iconBg'] }}">→</span>
                            <div>
                                <p class="text-[10px] font-bold" style="color:{{ $ms['text'] }}">{{ $ms['from'] }} → {{ $ms['to'] }}</p>
                                <p class="text-[9px]" style="color:{{ $ms['iconBg'] }}">{{ number_format($ms['xp']) }} XP needed</p>
                            </div>
                        </div>
                        <span class="text-[12px] font-black" style="color:{{ $ms['text'] }}">{{ $ms['ready'] }} ready</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Top XP Earners --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-4">Top XP Earners</p>
                @php $topStudents = $allStudents->sortByDesc('total_xp')->take(3); @endphp
                @if($topStudents->count())
                <div class="space-y-3">
                    @foreach($topStudents as $idx => $ts)
                    @php
                        $rc = [
                            ['ring'=>'ring-[#0d326b]', 'bg'=>'bg-[#eff6ff]',  'badge'=>'bg-[#0d326b] text-white'],
                            ['ring'=>'ring-blue-200',  'bg'=>'bg-slate-50',    'badge'=>'bg-blue-100 text-[#1e4b8f]'],
                            ['ring'=>'ring-blue-100',  'bg'=>'bg-blue-50/50',  'badge'=>'bg-blue-50 text-[#1e4b8f]'],
                        ][$idx] ?? ['ring'=>'ring-slate-200','bg'=>'bg-slate-50','badge'=>'bg-slate-200 text-slate-600'];
                    @endphp
                    <div class="flex items-center space-x-3 p-2.5 rounded-xl {{ $rc['bg'] }}">
                        <div class="relative shrink-0">
                            <img src="https://ui-avatars.com/api/?name={{ urlencode($ts->first_name.'+'.$ts->last_name) }}&background=0d326b&color=fff&rounded=true&size=80" class="w-10 h-10 rounded-full" />
                            <span class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full {{ $rc['badge'] }} text-[8px] font-black flex items-center justify-center">{{ $idx+1 }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[12px] font-bold text-[#0d326b] truncate">{{ $ts->first_name }} {{ $ts->last_name }}</p>
                            <p class="text-[10px] text-slate-400 font-medium">{{ number_format($ts->total_xp) }} XP</p>
                        </div>
                        @if($idx===0)<span class="material-symbols-outlined text-[#facc15] text-[18px] shrink-0">star</span>@endif
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-[12px] text-slate-400 text-center py-3">No XP data yet.</p>
                @endif
            </div>

        </div>
    </div>
</div>

{{-- ══════════ STUDENT DETAILS MODAL ══════════ --}}
<div id="student-details-modal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-[2px] z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div id="student-details-card" class="bg-white rounded-[32px] w-full mx-4 shadow-2xl transform scale-95 transition-transform duration-300 flex flex-col overflow-hidden" style="max-width:1100px;height:min(94vh,820px)">

        {{-- Top accent bar — overflow-hidden on parent clips it to the card's rounded corners --}}
        <div id="sdc-accent" class="h-1.5 w-full bg-gradient-to-r from-[#0d326b] to-[#1a6fd4] shrink-0"></div>

        {{-- Header --}}
        <div class="flex items-center justify-between px-7 pt-5 pb-4 border-b border-slate-100 shrink-0">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined text-[#0d326b] text-[22px]">person</span>
                <h2 class="text-[17px] font-black text-[#0d326b]">Student Details</h2>
            </div>
            <div class="flex items-center gap-2">
                <button id="sdc-edit-btn" title="Edit student details"
                    class="flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-[12px] font-bold transition-all bg-slate-100 hover:bg-slate-200 text-slate-600">
                    <span class="material-symbols-outlined text-[15px]">edit</span>
                    <span>Edit</span>
                </button>
                <button id="sdc-save-btn" title="Save changes"
                    class="hidden flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-[12px] font-bold transition-all bg-[#0d326b] hover:bg-[#154188] text-white">
                    <span class="material-symbols-outlined text-[15px]">save</span>
                    <span>Save Changes</span>
                </button>
                <button id="sdc-cancel-edit-btn" title="Cancel editing"
                    class="hidden flex items-center gap-1.5 px-3.5 py-2 rounded-xl text-[12px] font-bold transition-all bg-slate-100 hover:bg-red-50 hover:text-red-600 text-slate-500">
                    <span class="material-symbols-outlined text-[15px]">close</span>
                    <span>Cancel</span>
                </button>
                <button id="sdc-close" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors">
                    <span class="material-symbols-outlined text-slate-500 text-[18px]">close</span>
                </button>
            </div>
        </div>

        {{-- Loading state --}}
        <div id="sdc-loading" class="flex-1 flex items-center justify-center py-16">
            <div class="flex flex-col items-center gap-3">
                <span class="material-symbols-outlined text-[#0d326b] text-[36px] animate-spin">progress_activity</span>
                <p class="text-[13px] text-slate-400 font-medium">Loading student data…</p>
            </div>
        </div>

        {{-- Content (hidden until loaded) --}}
        <div id="sdc-content" class="hidden flex-1 overflow-hidden">
            <div class="flex flex-col lg:flex-row gap-0 h-full">

                {{-- LEFT PANEL --}}
                <div class="lg:w-[230px] shrink-0 bg-gradient-to-b from-[#f8fafc] to-white border-r border-slate-100 p-5 flex flex-col items-center gap-3 overflow-y-auto">
                    <div class="relative">
                        <img id="sdc-avatar" src="" alt="" class="w-16 h-16 rounded-2xl shadow-md object-cover ring-4 ring-white" />
                        <span id="sdc-status-dot" class="absolute -bottom-1 -right-1 w-4 h-4 rounded-full border-2 border-white bg-emerald-400"></span>
                    </div>
                    <div class="text-center">
                        <p id="sdc-name" class="text-[14px] font-black text-[#0d326b] leading-tight"></p>
                        <p id="sdc-lrn-display" class="text-[11px] text-slate-500 font-bold mt-0.5 font-mono tracking-wide"></p>
                    </div>
                    <span id="sdc-level-badge" class="lvl-badge beginner text-[10px] px-3 py-1"></span>
                    <span id="sdc-status-badge" class="text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider"></span>

                    {{-- XP bar --}}
                    <div class="w-full">
                        <div class="flex justify-between mb-1">
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">EXP</span>
                            <span id="sdc-xp-fraction" class="text-[10px] font-bold text-[#0d326b]"></span>
                        </div>
                        <div class="xp-bar-wrap">
                            <div id="sdc-xp-bar" class="xp-bar-fill" style="width:0%"></div>
                        </div>
                        <p id="sdc-xp-hint" class="text-[9px] text-slate-400 text-right mt-1"></p>
                    </div>

                    {{-- Quick stats --}}
                    <div class="w-full space-y-1.5">
                        <div class="bg-white rounded-xl border border-slate-100 px-3 py-2 flex items-center justify-between">
                            <span class="text-[10px] text-slate-400 font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[13px]">bolt</span>Streak</span>
                            <span id="sdc-streak" class="text-[11px] font-black text-[#0d326b]">0 days</span>
                        </div>
                        <div class="bg-white rounded-xl border border-slate-100 px-3 py-2 flex items-center justify-between">
                            <span class="text-[10px] text-slate-400 font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[13px]">military_tech</span>Level</span>
                            <span id="sdc-level-num" class="text-[11px] font-black text-[#0d326b]">1</span>
                        </div>
                        <div class="bg-white rounded-xl border border-slate-100 px-3 py-2 flex items-center justify-between">
                            <span class="text-[10px] text-slate-400 font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[13px]">schedule</span>Active</span>
                            <span id="sdc-last-active" class="text-[10px] font-bold text-[#0d326b]">—</span>
                        </div>
                    </div>
                </div>

                {{-- RIGHT PANEL --}}
                <div class="flex-1 flex flex-col overflow-hidden">

                    {{-- Row 1: Personal + Academic side by side --}}
                    <div class="flex gap-3 p-5 pb-2.5 flex-1 min-h-0">

                        {{-- Personal Info --}}
                        <div class="flex-1 bg-slate-50 rounded-2xl p-5 border border-slate-100 flex flex-col overflow-hidden">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-1"><span class="material-symbols-outlined text-[12px]">badge</span>Personal Information</p>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-0 flex-1 content-between">
                                <div class="flex flex-col justify-center py-2 border-b border-slate-100 last:border-0">
                                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-bold mb-1">Full Name <span class="text-red-400 edit-only hidden">*</span></p>
                                    <p id="sdc-fullname" class="text-[15px] font-bold text-slate-700 view-only leading-tight"></p>
                                    <input id="sdc-edit-fullname" type="text" placeholder="Last Name, First Name"
                                        class="edit-only hidden w-full bg-white border border-slate-200 focus:border-[#0d326b] text-[12px] font-medium py-1.5 px-2.5 rounded-xl outline-none transition-all" />
                                </div>
                                <div class="flex flex-col justify-center py-2 border-b border-slate-100">
                                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-bold mb-1">Age</p>
                                    <p id="sdc-age" class="text-[15px] font-bold text-slate-700 view-only leading-tight"></p>
                                    <input id="sdc-edit-age" type="number" min="1" max="120" placeholder="Age"
                                        class="edit-only hidden w-full bg-white border border-slate-200 focus:border-[#0d326b] text-[12px] font-medium py-1.5 px-2.5 rounded-xl outline-none transition-all" />
                                </div>
                                <div class="flex flex-col justify-center py-2">
                                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-bold mb-1">Username</p>
                                    <p id="sdc-username" class="text-[15px] font-bold text-slate-700 truncate leading-tight"></p>
                                </div>
                                <div class="flex flex-col justify-center py-2">
                                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-bold mb-1">Email / LRN</p>
                                    <p id="sdc-email" class="text-[14px] font-bold text-slate-700 break-all leading-tight"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Academic Info --}}
                        <div class="flex-1 bg-slate-50 rounded-2xl p-5 border border-slate-100 flex flex-col overflow-hidden">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-1"><span class="material-symbols-outlined text-[12px]">school</span>Academic Information</p>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-0 flex-1 content-between">
                                <div class="flex flex-col justify-center py-2 border-b border-slate-100">
                                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-bold mb-1">Program <span class="text-red-400 edit-only hidden">*</span></p>
                                    <p id="sdc-program" class="text-[15px] font-bold text-slate-700 view-only leading-tight"></p>
                                    <select id="sdc-edit-program"
                                        class="edit-only hidden w-full bg-white border border-slate-200 focus:border-[#0d326b] text-[12px] font-medium py-1.5 px-2.5 rounded-xl outline-none appearance-none transition-all cursor-pointer">
                                        <option value="Regular">Regular</option>
                                        <option value="Inclusion">Inclusion</option>
                                        <option value="SPED">SPED</option>
                                        <option value="Home-based">Home-based</option>
                                        <option value="Self-contained">Self-contained</option>
                                        <option value="Transition">Transition</option>
                                    </select>
                                </div>
                                <div class="flex flex-col justify-center py-2 border-b border-slate-100">
                                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-bold mb-1">Grade Level</p>
                                    <p id="sdc-grade" class="text-[15px] font-bold text-slate-700 view-only leading-tight"></p>
                                    <select id="sdc-edit-grade"
                                        class="edit-only hidden w-full bg-white border border-slate-200 focus:border-[#0d326b] text-[12px] font-medium py-1.5 px-2.5 rounded-xl outline-none appearance-none transition-all cursor-pointer">
                                        <option value="">— None —</option>
                                        <option>Grade 1</option><option>Grade 2</option><option>Grade 3</option>
                                        <option>Grade 4</option><option>Grade 5</option><option>Grade 6</option>
                                        <option>SPED A</option><option>SPED B</option>
                                    </select>
                                </div>
                                <div class="flex flex-col justify-center py-2">
                                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-bold mb-1">Section</p>
                                    <p id="sdc-section" class="text-[15px] font-bold text-slate-700 view-only leading-tight"></p>
                                    <input id="sdc-edit-section" type="text" placeholder="e.g. SPED-A"
                                        class="edit-only hidden w-full bg-white border border-slate-200 focus:border-[#0d326b] text-[12px] font-medium py-1.5 px-2.5 rounded-xl outline-none transition-all" />
                                </div>
                                <div class="flex flex-col justify-center py-2">
                                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-bold mb-1">School Year</p>
                                    <p id="sdc-school-year" class="text-[15px] font-bold text-slate-700 view-only leading-tight"></p>
                                    <div class="edit-only hidden">
                                        <input id="sdc-edit-school-year" type="text" placeholder="e.g. 2024-2025" maxlength="9"
                                            class="w-full bg-white border border-slate-200 focus:border-[#0d326b] text-[12px] font-medium py-1.5 px-2.5 rounded-xl outline-none transition-all" />
                                        <p id="sdc-sy-error" class="hidden text-[10px] text-red-500 font-semibold mt-1"></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    {{-- Row 2: Account Info + Promotion History side by side --}}
                    <div class="flex gap-3 px-5 py-2.5 flex-1 min-h-0">

                        {{-- Account Info --}}
                        <div class="flex-1 bg-slate-50 rounded-2xl p-5 border border-slate-100 flex flex-col overflow-hidden">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-1"><span class="material-symbols-outlined text-[12px]">info</span>Account Information</p>
                            <div class="grid grid-cols-2 gap-x-4 gap-y-0 flex-1 content-between">
                                <div class="flex flex-col justify-center py-2 border-b border-slate-100">
                                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-bold mb-1">LRN <span class="text-red-400 edit-only hidden">*</span></p>
                                    <p id="sdc-lrn" class="text-[15px] font-black text-[#0d326b] font-mono tracking-wide view-only leading-tight"></p>
                                    <input id="sdc-edit-lrn" type="text" placeholder="e.g. 123456789001" maxlength="12"
                                        class="edit-only hidden w-full bg-white border border-slate-200 focus:border-[#0d326b] text-[13px] font-medium py-1.5 px-2.5 rounded-xl outline-none transition-all font-mono" />
                                </div>
                                <div class="flex flex-col justify-center py-2 border-b border-slate-100">
                                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-bold mb-1">PIN</p>
                                    <div class="flex items-center gap-1.5">
                                        <p id="sdc-pin" class="text-[15px] font-black text-[#0d326b] font-mono tracking-[0.2em] view-only leading-tight">••••</p>
                                        <button id="sdc-pin-toggle" type="button" title="Show/hide PIN"
                                            class="w-6 h-6 flex items-center justify-center rounded-lg hover:bg-slate-200 transition-colors text-slate-400 hover:text-slate-600 view-only">
                                            <span id="sdc-pin-eye" class="material-symbols-outlined text-[14px]">visibility_off</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="flex flex-col justify-center py-2">
                                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-bold mb-1">Date Added</p>
                                    <p id="sdc-created" class="text-[15px] font-bold text-slate-700 leading-tight"></p>
                                </div>
                                <div class="flex flex-col justify-center py-2">
                                    <p class="text-[9px] text-slate-400 uppercase tracking-wider font-bold mb-1">Last Updated</p>
                                    <p id="sdc-updated" class="text-[15px] font-bold text-slate-700 leading-tight"></p>
                                </div>
                            </div>
                        </div>

                        {{-- Promotion History --}}
                        <div class="flex-1 bg-slate-50 rounded-2xl p-4 border border-slate-100 flex flex-col overflow-hidden">
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-widest mb-3 flex items-center gap-1"><span class="material-symbols-outlined text-[12px]">history</span>Promotion History</p>
                            <div id="sdc-history-list" class="space-y-1.5 flex-1 overflow-y-auto pr-1"></div>
                            <div id="sdc-history-empty" class="hidden flex-1 flex flex-col items-center justify-center py-4">
                                <span class="material-symbols-outlined text-slate-200 text-[28px] block mb-1.5">history</span>
                                <p class="text-[10px] text-slate-400 font-medium">No promotions yet</p>
                            </div>
                        </div>

                    </div>

                    {{-- Row 3: Student Management (always visible, no scroll needed) --}}
                    <div class="px-5 pt-2.5 pb-5 shrink-0">
                        <div class="bg-gradient-to-r from-[#0d326b]/5 to-[#1a6fd4]/5 rounded-[20px] border border-[#0d326b]/10 p-4">
                            <p class="text-[9px] font-bold text-[#0d326b] uppercase tracking-widest mb-3 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[12px]">settings</span>Student Management
                            </p>
                            <div class="grid grid-cols-3 gap-3">

                                {{-- Enrollment --}}
                                <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm flex flex-col">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-8 h-8 rounded-xl bg-emerald-50 flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-emerald-500 text-[16px]">how_to_reg</span>
                                        </div>
                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Enrollment</p>
                                    </div>
                                    <p id="sdc-enroll-status-text" class="text-[11px] text-slate-400 mb-3 leading-relaxed flex-1"></p>
                                    <div class="flex gap-2">
                                        <button id="sdc-enroll-btn" class="flex-1 py-2.5 rounded-xl text-[12px] font-bold transition-all flex items-center justify-center gap-1.5 bg-emerald-500 hover:bg-emerald-600 text-white">
                                            <span class="material-symbols-outlined text-[13px]">person_add</span>Enroll
                                        </button>
                                        <button id="sdc-unenroll-btn" class="flex-1 py-2.5 rounded-xl text-[12px] font-bold transition-all flex items-center justify-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-100">
                                            <span class="material-symbols-outlined text-[13px]">person_remove</span>Remove
                                        </button>
                                    </div>
                                </div>

                                {{-- Promotion --}}
                                <div id="sdc-promote-card" class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm flex flex-col transition-all duration-300">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div id="sdc-promote-icon-wrap" class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-[#0d326b] text-[16px]">trending_up</span>
                                        </div>
                                        <div class="flex-1 flex items-center gap-1.5 flex-wrap">
                                            <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Promotion</p>
                                            <span id="sdc-ready-badge" class="hidden text-[9px] font-black px-2 py-0.5 rounded-full uppercase tracking-wider bg-amber-100 text-amber-700 border border-amber-300">
                                                ★ Ready
                                            </span>
                                        </div>
                                    </div>
                                    <p id="sdc-promote-hint" class="text-[11px] text-slate-400 mb-3 leading-relaxed flex-1"></p>
                                    <button id="sdc-promote-btn" class="w-full py-2.5 rounded-xl text-[12px] font-bold transition-all flex items-center justify-center gap-1.5 bg-[#0d326b] hover:bg-[#154188] text-white">
                                        <span class="material-symbols-outlined text-[13px]">arrow_upward</span>
                                        <span id="sdc-promote-label">Promote</span>
                                    </button>
                                </div>

                                {{-- Demotion --}}
                                <div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm flex flex-col">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-8 h-8 rounded-xl bg-red-50 flex items-center justify-center shrink-0">
                                            <span class="material-symbols-outlined text-red-400 text-[16px]">trending_down</span>
                                        </div>
                                        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Demotion</p>
                                    </div>
                                    <p id="sdc-demote-hint" class="text-[11px] text-slate-400 mb-3 leading-relaxed flex-1">Move student down one level.</p>
                                    <button id="sdc-demote-btn" class="w-full py-2.5 rounded-xl text-[12px] font-bold transition-all flex items-center justify-center gap-1.5 bg-red-50 hover:bg-red-100 text-red-600 border border-red-100">
                                        <span class="material-symbols-outlined text-[13px]">arrow_downward</span>
                                        <span id="sdc-demote-label">Demote</span>
                                    </button>
                                </div>

                                {{-- Manage Lessons Card --}}
<div class="bg-white rounded-2xl border border-slate-100 p-4 shadow-sm flex flex-col">
    <div class="flex items-center gap-2 mb-2">
        <div class="w-8 h-8 rounded-xl bg-blue-50 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-blue-500 text-[16px]">edit_document</span>
        </div>
        <p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest">Lesson Assignments</p>
    </div>
    <p id="sdc-assignment-hint" class="text-[11px] text-slate-400 mb-3 leading-relaxed flex-1">Manage which lessons this student has access to.</p>
    <button id="sdc-assign-btn" class="w-full py-2.5 rounded-xl text-[12px] font-bold transition-all flex items-center justify-center gap-1.5 bg-blue-50 hover:bg-blue-100 text-blue-600 border border-blue-100">
        <span class="material-symbols-outlined text-[13px]">edit_note</span>
        Manage Lessons
    </button>
</div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>

     `


        {{-- Notification bar (inside modal) --}}
        <div id="sdc-notif" class="hidden shrink-0 mx-6 mb-4 px-4 py-3 rounded-xl text-[12px] font-semibold flex items-center gap-2 border"></div>

    </div>
</div>

 {{-- ══════════ LESSON ASSIGNMENT MODAL ══════════ --}}
<div id="assignment-modal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-[2px] z-[55] flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div id="assignment-modal-card" class="bg-white rounded-[32px] w-[800px] max-w-[95vw] max-h-[90vh] shadow-2xl transform scale-95 transition-transform duration-300 flex flex-col overflow-hidden">
        
        {{-- Header --}}
        <div class="flex items-center justify-between px-7 pt-6 pb-4 border-b border-slate-100 shrink-0">
            <div>
                <h2 class="text-[18px] font-black text-[#0d326b] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[24px]">assignment</span>
                    <span id="assignment-title-label">Assign Lessons</span>
                </h2>
                <p id="assignment-student-name" class="text-[13px] text-slate-500 font-medium mt-0.5">Loading...</p>
            </div>
            <button id="assignment-close" class="w-8 h-8 rounded-full bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors">
                <span class="material-symbols-outlined text-slate-500 text-[18px]">close</span>
            </button>
        </div>

        {{-- Loading --}}
        <div id="assignment-loading" class="flex-1 flex items-center justify-center py-12">
            <div class="flex flex-col items-center gap-3">
                <span class="material-symbols-outlined text-[#0d326b] text-[36px] animate-spin">progress_activity</span>
                <p class="text-[13px] text-slate-400 font-medium">Loading available lessons...</p>
            </div>
        </div>

        {{-- Content --}}
        <div id="assignment-content" class="hidden flex-1 overflow-hidden flex flex-col">
            
            {{-- Stats bar --}}
            <div class="px-7 py-3 bg-slate-50 border-b border-slate-100 shrink-0 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-4 text-[12px]">
                    <span class="font-medium text-slate-500">Total Lessons: <span id="assignment-total" class="font-bold text-[#0d326b]">0</span></span>
                    <span class="font-medium text-slate-500">Selected: <span id="assignment-selected" class="font-bold text-emerald-600">0</span></span>
                </div>
                <div class="flex items-center gap-3">
                    <button id="assignment-select-all" class="text-[11px] font-bold text-[#0d326b] hover:text-[#1a6fd4] transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">select_all</span>
                        Select All
                    </button>
                    <button id="assignment-deselect-all" class="text-[11px] font-bold text-slate-400 hover:text-slate-600 transition-colors flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">deselect</span>
                        Deselect All
                    </button>
                </div>
            </div>

            {{-- Module/Lesson list (scrollable) --}}
            <div id="assignment-list" class="flex-1 overflow-y-auto p-6 space-y-4">
                <!-- Dynamically rendered -->
            </div>

            {{-- Footer --}}
            <div class="px-7 py-4 border-t border-slate-100 shrink-0 flex items-center justify-end gap-3 bg-white">
                <button id="assignment-cancel" class="px-5 py-2.5 rounded-xl text-[13px] font-semibold text-slate-500 hover:bg-slate-50 transition-colors">
                    Cancel
                </button>
                <button id="assignment-save" class="px-6 py-2.5 rounded-xl text-[13px] font-bold bg-[#0d326b] hover:bg-[#154188] text-white transition-all flex items-center gap-2 shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">save</span>
                    <span id="assignment-save-label">Save Assignments</span>
                </button>
            </div>
        </div>

        {{-- Notification --}}
        <div id="assignment-notif" class="hidden shrink-0 mx-7 mb-4 px-4 py-3 rounded-xl text-[12px] font-semibold flex items-center gap-2 border"></div>
    </div>
</div>


{{-- ══════════ CONFIRM DIALOG (reusable inside Student Details) ══════════ --}}
<div id="sdc-confirm-modal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-[2px] z-[60] flex items-center justify-center hidden opacity-0 transition-opacity duration-200">
    <div id="sdc-confirm-card" class="bg-white rounded-[24px] w-[400px] max-w-full mx-4 p-7 shadow-2xl transform scale-95 transition-transform duration-200">
        <div class="flex items-start gap-4 mb-5">
            <div id="sdc-confirm-icon-wrap" class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0">
                <span id="sdc-confirm-icon" class="material-symbols-outlined text-[22px]">help</span>
            </div>
            <div>
                <p id="sdc-confirm-title" class="text-[15px] font-black text-[#0d326b]"></p>
                <p id="sdc-confirm-body" class="text-[12px] text-slate-500 mt-1 leading-relaxed"></p>
            </div>
        </div>
        <div class="flex gap-3">
            <button id="sdc-confirm-cancel" class="flex-1 py-2.5 rounded-xl border border-slate-200 text-slate-600 text-[13px] font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
            <button id="sdc-confirm-ok" class="flex-1 py-2.5 rounded-xl text-white text-[13px] font-bold transition-all"></button>
        </div>
    </div>
</div>

{{-- ══════════ ADD STUDENT MODAL ══════════ --}}
<div id="add-student-modal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-[2px] z-50 flex items-center justify-center hidden opacity-0 transition-opacity duration-300">
    <div class="bg-white rounded-[32px] w-[620px] max-w-full max-h-[90vh] overflow-y-auto p-8 shadow-2xl relative transform scale-95 transition-transform duration-300">
        <button id="close-modal-btn" class="absolute top-7 right-7 text-slate-400 hover:text-slate-600 outline-none">
            <span class="material-symbols-outlined text-[24px]">close</span>
        </button>
        <div class="mb-6">
            <h2 class="text-[24px] font-bold text-[#0d326b] mb-1">Add New Students</h2>
            <p class="text-[13px] text-slate-400 font-medium">Populate your classroom</p>
        </div>
        <div class="flex space-x-6 border-b border-slate-100 mb-6 text-[12px] font-bold tracking-wider uppercase">
            <button id="tab-single" class="text-[#0d326b] border-b-2 border-[#0d326b] pb-3 outline-none transition-all">Single Student</button>
            <button id="tab-bulk" class="text-slate-400 border-b-2 border-transparent pb-3 hover:text-slate-600 outline-none transition-all">Bulk Add (Excel)</button>
        </div>
        <div id="modal-alert" class="hidden mb-5 p-4 rounded-xl text-sm font-medium flex items-start space-x-2 border">
            <span id="modal-alert-icon" class="material-symbols-outlined text-[20px] mt-0.5 shrink-0"></span>
            <div id="modal-alert-message" class="flex-1"></div>
        </div>
        <form id="form-single" class="block" onsubmit="submitSingleStudent(event)">
            @csrf
            <div class="grid grid-cols-2 gap-x-6 gap-y-5 mb-6">
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Learner Reference Number (LRN)</label>
                    <input type="text" name="lrn" id="input-lrn" required placeholder="12-digit LRN" pattern="\d{12}" maxlength="12" title="LRN must be exactly 12 digits" class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                    <p id="lrn-error" class="hidden text-[12px] font-medium text-red-600">LRN already exists.</p>
                    <p id="lrn-warning" class="hidden text-[12px] font-medium text-amber-600 mt-1"></p>
                </div>
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Full Name</label>
                    <input type="text" name="full_name" required placeholder="Last Name, First Name" class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                </div>
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Program Type</label>
                    <div class="relative">
                        <select name="program_type" id="input-program-type" required class="w-full bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 pl-4 pr-10 rounded-xl outline-none border border-transparent focus:border-slate-300 appearance-none transition-all cursor-pointer">
                            <option value="Regular">Regular</option>
                            <option value="Self-contained">Self-Contained</option>
                            <option value="Transition">Transition</option>
                            <option value="Inclusion">Inclusion</option>
                        </select>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-500 pointer-events-none">expand_more</span>
                    </div>
                </div>
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Age</label>
                    <input type="number" name="age" required min="1" max="100" placeholder="Enter age" class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                </div>
                <div id="field-grade-level" class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Grade Level</label>
                    <div class="relative">
                        <select name="grade_level" id="input-grade-level" class="w-full bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 pl-4 pr-10 rounded-xl outline-none border border-transparent focus:border-slate-300 appearance-none transition-all cursor-pointer">
                            <option value="">Select Grade</option>
                            <option value="Grade 1">Grade 1</option>
                            <option value="Grade 2">Grade 2</option>
                            <option value="Grade 3">Grade 3</option>
                            <option value="Grade 4">Grade 4</option>
                            <option value="Grade 5">Grade 5</option>
                            <option value="Grade 6">Grade 6</option>
                            <option value="SPED A">SPED A</option>
                            <option value="SPED B">SPED B</option>
                        </select>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-500 pointer-events-none">expand_more</span>
                    </div>
                </div>
                <div id="field-section" class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Section</label>
                    <input type="text" name="section" id="input-section" placeholder="e.g. SPED-A" class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                </div>
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">School Year</label>
                    <input type="text" name="school_year" id="single-school-year" placeholder="e.g. 2024-2025" maxlength="9" class="bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 px-4 rounded-xl outline-none border border-transparent focus:border-slate-300 transition-all placeholder:text-slate-400" />
                    <p id="single-sy-error" class="hidden text-[12px] font-medium text-red-600"></p>
                </div>
                <div class="flex flex-col space-y-2">
                    <label class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">FSL Mastery Level</label>
                    <div class="relative">
                        <select name="fsl_mastery_level" class="w-full bg-[#f1f5f9] text-[#1e293b] text-[14px] font-medium py-3.5 pl-4 pr-10 rounded-xl outline-none border border-transparent focus:border-slate-300 appearance-none transition-all cursor-pointer">
                            <option value="Beginner">Beginner</option>
                            <option value="Intermediate">Intermediate</option>
                            <option value="Advanced">Advanced</option>
                        </select>
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 material-symbols-outlined icon-outline text-[18px] text-slate-500 pointer-events-none">expand_more</span>
                    </div>
                </div>
            </div>
            <div class="bg-[#f1f5f9] p-4 rounded-[20px] flex items-center justify-between mb-8 shadow-sm">
                <div class="flex items-center space-x-4">
                    <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-[#0d326b] shadow-sm">
                        <span class="material-symbols-outlined text-[20px]">lock</span>
                    </div>
                    <div>
                        <p class="text-[13px] font-bold text-[#0d326b]">Student PIN</p>
                        <p class="text-[11px] text-slate-400 font-medium mt-0.5">Last 4 digits of LRN</p>
                    </div>
                </div>
                <span id="pin-preview" class="text-[18px] font-bold text-[#0d326b] tracking-widest tabular-nums">----</span>
            </div>
            <div class="flex items-center justify-end space-x-4">
                <button type="button" class="btn-cancel px-6 py-3 text-slate-500 hover:text-slate-800 font-semibold text-[14px] transition-colors">Cancel</button>
                <button type="submit" id="btn-single-submit" class="bg-[#0d326b] hover:bg-[#154188] text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-colors flex items-center justify-center">Save Student</button>
            </div>
        </form>
        <div id="container-bulk" class="hidden">

            {{-- ── STEP 1: Upload zone ── --}}
            <div id="bulk-step-upload">
                <div id="drop-zone" class="border-2 border-dashed border-slate-300 hover:border-[#0d326b] rounded-[24px] p-10 flex flex-col items-center justify-center space-y-4 mb-5 transition-all cursor-pointer relative bg-slate-50/50">
                    <input type="file" id="excel-file" accept=".xlsx,.xls,.csv" class="absolute inset-0 opacity-0 cursor-pointer w-full h-full" />
                    <div id="upload-icon-container" class="w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500 shadow-sm">
                        <span id="upload-icon" class="material-symbols-outlined text-[28px]">article</span>
                    </div>
                    <div class="text-center">
                        <p id="upload-primary-text" class="text-[15px] font-bold text-slate-700">Drag and drop your student roster here</p>
                        <p id="upload-secondary-text" class="text-[12px] text-slate-400 mt-1">.xlsx, .xls, or .csv — max 5MB</p>
                    </div>
                    <button type="button" class="border border-[#0d326b] text-[#0d326b] hover:bg-[#0d326b]/5 font-bold text-[13px] px-6 py-2.5 rounded-xl transition-colors pointer-events-none">Browse Files</button>
                </div>
                <div class="bg-[#f1f5f9] p-4 rounded-[20px] flex items-center justify-between shadow-sm">
                    <div class="flex items-center space-x-4">
                        <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center text-[#0d326b] shadow-sm"><span class="material-symbols-outlined text-[20px]">lock</span></div>
                        <div>
                            <p class="text-[13px] font-bold text-[#0d326b]">Auto-generate Student PINs</p>
                            <p class="text-[11px] text-slate-400 font-medium mt-0.5">Last 4 digits of LRN — applied to all students</p>
                        </div>
                    </div>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="bulk-auto-pin" class="sr-only peer" checked>
                        <div class="w-11 h-6 bg-slate-300 rounded-full peer peer-checked:after:translate-x-full after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#0d326b]"></div>
                    </label>
                </div>
                <div class="flex items-center justify-end space-x-3 mt-5">
                    <button type="button" class="btn-cancel px-6 py-3 text-slate-500 hover:text-slate-800 font-semibold text-[14px] transition-colors">Cancel</button>
                    <button type="button" id="btn-preview-table" disabled
                        onclick="renderDataTable()"
                        class="bg-slate-300 text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-all cursor-not-allowed flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">table_view</span>Review &amp; Import
                    </button>
                </div>
            </div>

            {{-- ── STEP 2: Editable data table (injected by JS) ── --}}
            <div id="bulk-step-table" class="hidden"></div>

            {{-- ── STEP 3: Result (injected by JS) ── --}}
            <div id="bulk-step-result" class="hidden"></div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
<script>
// ─── Add Student Modal ────────────────────────────────────────────────────────
const openModalBtn=document.getElementById('open-modal-btn'),closeModalBtn=document.getElementById('close-modal-btn'),cancelBtns=document.querySelectorAll('.btn-cancel'),modal=document.getElementById('add-student-modal'),modalCard=modal.querySelector('.bg-white'),tabSingle=document.getElementById('tab-single'),tabBulk=document.getElementById('tab-bulk'),formSingle=document.getElementById('form-single'),containerBulk=document.getElementById('container-bulk'),modalAlert=document.getElementById('modal-alert'),modalAlertIcon=document.getElementById('modal-alert-icon'),modalAlertMsg=document.getElementById('modal-alert-message'),dropZone=document.getElementById('drop-zone'),excelInput=document.getElementById('excel-file'),uploadIcon=document.getElementById('upload-icon'),uploadIconWrap=document.getElementById('upload-icon-container'),uploadPrimary=document.getElementById('upload-primary-text'),uploadSecondary=document.getElementById('upload-secondary-text');
let parsedStudents=[];
openModalBtn.addEventListener('click',()=>{modal.classList.remove('hidden');requestAnimationFrame(()=>{modal.classList.remove('opacity-0');modalCard.classList.remove('scale-95');});});
function closeModal(){
    console.log('🔒 Closing Add Student modal');
    modal.classList.add('opacity-0');
    modalCard.classList.add('scale-95');
    setTimeout(() => {
        modal.classList.add('hidden');
        resetModal();
    }, 300);
}
const AT='text-[#0d326b] border-b-2 border-[#0d326b] pb-3 outline-none transition-all',IT='text-slate-400 border-b-2 border-transparent hover:text-slate-600 outline-none transition-all';
tabSingle.addEventListener('click',()=>{tabSingle.className=AT;tabBulk.className=IT;formSingle.classList.remove('hidden');containerBulk.classList.add('hidden');});
tabBulk.addEventListener('click',()=>{tabBulk.className=AT;tabSingle.className=IT;containerBulk.classList.remove('hidden');formSingle.classList.add('hidden');});
function showAlert(msg,type='error'){modalAlert.classList.remove('hidden','bg-red-50','border-red-200','text-red-800','bg-emerald-50','border-emerald-200','text-emerald-800','bg-amber-50','border-amber-200','text-amber-800');modalAlertIcon.innerText=type==='error'?'error':(type==='warning'?'warning':'check_circle');modalAlert.classList.add(type==='error'?'bg-red-50':(type==='warning'?'bg-amber-50':'bg-emerald-50'),type==='error'?'border-red-200':(type==='warning'?'border-amber-200':'border-emerald-200'),type==='error'?'text-red-800':(type==='warning'?'text-amber-800':'text-emerald-800'));modalAlertMsg.innerHTML=msg;}
function hideAlert(){modalAlert.classList.add('hidden');}
function resetModal(){formSingle.reset();parsedStudents=[];resetUploadArea();hideAlert();hideLrnError();updatePinPreview();toggleGradeSectionFields();tabSingle.click();}
const inputProgramType=document.getElementById('input-program-type'),fieldGradeLevel=document.getElementById('field-grade-level'),fieldSection=document.getElementById('field-section'),inputGradeLevel=document.getElementById('input-grade-level'),inputSection=document.getElementById('input-section');
function toggleGradeSectionFields(){const show=['Regular','Inclusion'].includes(inputProgramType.value);fieldGradeLevel.classList.toggle('hidden',!show);fieldSection.classList.toggle('hidden',!show);inputGradeLevel.disabled=!show;inputSection.disabled=!show;if(!show){inputGradeLevel.value='';inputSection.value='';}}
inputProgramType.addEventListener('change',toggleGradeSectionFields);toggleGradeSectionFields();
const inputLrn=document.getElementById('input-lrn'),lrnError=document.getElementById('lrn-error'),lrnWarning=document.getElementById('lrn-warning'),pinPreview=document.getElementById('pin-preview');
let lrnExists=false,lrnCheckTimer=null;
function hideLrnError(){lrnError.classList.add('hidden');lrnWarning.classList.add('hidden');inputLrn.classList.remove('border-red-400','focus:border-red-400','border-amber-400','focus:border-amber-400');lrnExists=false;}
function showLrnError(msg){if(msg)lrnError.innerText=msg;lrnError.classList.remove('hidden');inputLrn.classList.add('border-red-400','focus:border-red-400');lrnExists=true;}
function showLrnWarning(msg){if(msg)lrnWarning.innerText=msg;lrnWarning.classList.remove('hidden');inputLrn.classList.add('border-amber-400','focus:border-amber-400');lrnExists=false;}
function updatePinPreview(){const lrn=inputLrn.value.replace(/\D/g,'');pinPreview.textContent=lrn.length>=4?lrn.slice(-4):'----';}
async function checkLrnUnique(){const lrn=inputLrn.value.replace(/\D/g,'');hideLrnError();if(lrn.length!==12)return;try{const res=await axios.get("{{ route('students.check-lrn') }}",{params:{lrn}});if(res.data.exists){if(res.data.status==='own'){showLrnError('Student already exists in your class.');}else if(res.data.status==='inactive'){showLrnWarning('This student is currently unenrolled and can be added to your class.');}else{const name=res.data.teacher_name||'another teacher';showLrnError('This student is already enrolled to Teacher '+name+'.');}}}catch(_){}}
inputLrn.addEventListener('input',()=>{updatePinPreview();clearTimeout(lrnCheckTimer);hideLrnError();if(inputLrn.value.replace(/\D/g,'').length===12){lrnCheckTimer=setTimeout(checkLrnUnique,400);}});
inputLrn.addEventListener('blur',checkLrnUnique);updatePinPreview();
// ─── Drag / drop + file input wiring ─────────────────────────────────────────
['dragenter','dragover'].forEach(ev => dropZone.addEventListener(ev, e => {
    e.preventDefault(); dropZone.classList.add('border-[#0d326b]','bg-[#0d326b]/5');
}));
['dragleave','drop'].forEach(ev => dropZone.addEventListener(ev, e => {
    e.preventDefault(); dropZone.classList.remove('border-[#0d326b]','bg-[#0d326b]/5');
}));
dropZone.addEventListener('drop', e => { const f=e.dataTransfer.files[0]; if(f){excelInput.files=e.dataTransfer.files;handleExcelFile(f);} });
excelInput.addEventListener('change', e => { if(e.target.files[0]) handleExcelFile(e.target.files[0]); });

function handleExcelFile(file) {
    hideAlert();
    const ext = file.name.split('.').pop().toLowerCase();
    if (!['xlsx','xls','csv'].includes(ext)) { showAlert('Invalid file format. Please upload .xlsx, .xls, or .csv.'); resetUploadArea(); return; }
    if (file.size > 5*1024*1024) { showAlert('File is too large. Maximum allowed size is 5 MB.'); resetUploadArea(); return; }
    const reader = new FileReader();
    reader.onload = ev => {
        try {
            const wb  = XLSX.read(new Uint8Array(ev.target.result), { type:'array' });
            const ws  = wb.Sheets[wb.SheetNames[0]];
            const raw = XLSX.utils.sheet_to_json(ws, { header:1 });
            parsedStudents = mapExcelData(raw);
            if (!parsedStudents.length) { showAlert('File is empty or has no student rows.'); resetUploadArea(); return; }
            const errCount = parsedStudents.filter(s => !isValid(s)).length;
            uploadIcon.innerText = errCount > 0 ? 'warning' : 'check_circle';
            uploadIconWrap.className = errCount > 0
                ? 'w-14 h-14 bg-amber-100 rounded-full flex items-center justify-center text-amber-500 shadow-sm'
                : 'w-14 h-14 bg-emerald-100 rounded-full flex items-center justify-center text-emerald-600 shadow-sm';
            uploadPrimary.innerText = file.name;
            uploadSecondary.innerText = parsedStudents.length + ' students detected' + (errCount > 0 ? ' — ' + errCount + ' need attention' : ' — all valid');
            const previewBtn = document.getElementById('btn-preview-table');
            previewBtn.removeAttribute('disabled');
            previewBtn.className = 'bg-[#0d326b] hover:bg-[#154188] text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-all cursor-pointer flex items-center gap-2';
        } catch(err) { showAlert(err.message || 'Failed to parse file.'); resetUploadArea(); }
    };
    reader.readAsArrayBuffer(file);
}

function resetUploadArea() {
    excelInput.value = '';
    uploadIcon.innerText = 'article';
    uploadIconWrap.className = 'w-14 h-14 bg-emerald-50 rounded-full flex items-center justify-center text-emerald-500 shadow-sm';
    uploadPrimary.innerText = 'Drag and drop your student roster here';
    uploadSecondary.innerText = '.xlsx, .xls, or .csv — max 5MB';
    parsedStudents = [];
    const previewBtn = document.getElementById('btn-preview-table');
    if (previewBtn) { previewBtn.setAttribute('disabled','true'); previewBtn.className = 'bg-slate-300 text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-all cursor-not-allowed flex items-center gap-2'; }
    document.getElementById('bulk-step-upload').classList.remove('hidden');
    const st = document.getElementById('bulk-step-table'); st.classList.add('hidden'); st.innerHTML = '';
    const sr = document.getElementById('bulk-step-result'); sr.classList.add('hidden'); sr.innerHTML = '';
}

// ─── Column header fuzzy-finder ───────────────────────────────────────────────
function mapExcelData(rows) {
    if (!rows || rows.length < 2) return [];
    const h = rows[0].map(x => String(x || '').trim().toLowerCase());
    const lrnIdx     = h.findIndex(x => x.includes('lrn') || x.includes('reference') || x.includes('learner'));
    const nameIdx    = h.findIndex(x => x.includes('name') || x.includes('student') || x.includes('full'));
    const firstIdx   = h.findIndex(x => x.includes('first'));
    const lastIdx    = h.findIndex(x => x.includes('last'));
    const programIdx = h.findIndex(x => x.includes('program') || x.includes('type') || x.includes('track'));
    const gradeIdx   = h.findIndex(x => (x.includes('grade') || x.includes('level') || x.includes('class')) && !x.includes('mastery'));
    const ageIdx     = h.findIndex(x => x === 'age' || x.includes('age'));
    const sectionIdx = h.findIndex(x => x.includes('section'));
    const masteryIdx = h.findIndex(x => x.includes('fsl') || x.includes('mastery') || x.includes('skill'));
    const syIdx      = h.findIndex(x => x.includes('school') && x.includes('year'));
    const PROGRAMS   = { regular:'Regular', inclusion:'Inclusion', sped:'SPED', 'home-based':'Home-based', homebased:'Home-based', home:'Home-based' };
    return rows.slice(1)
        .filter(r => r && r.some(cell => String(cell || '').trim() !== ''))
        .map((row, i) => {
            const lrn = String(row[lrnIdx] ?? '').trim();
            let full_name = '';
            if (nameIdx !== -1) { full_name = String(row[nameIdx] ?? '').trim(); }
            else if (lastIdx !== -1 || firstIdx !== -1) {
                const ln = String(row[lastIdx] ?? '').trim(), fn = String(row[firstIdx] ?? '').trim();
                full_name = ln && fn ? ln + ', ' + fn : (ln || fn);
            }
            let program_type = '';
            if (programIdx !== -1) {
                const raw = String(row[programIdx] ?? '').trim().toLowerCase();
                program_type = PROGRAMS[raw] ?? (raw ? String(row[programIdx]).trim() : '');
            }
            const rawM = masteryIdx !== -1 ? String(row[masteryIdx] ?? '').trim().toLowerCase() : '';
            const fsl_mastery_level = rawM.includes('inter') ? 'Intermediate' : rawM.includes('adv') ? 'Advanced' : 'Beginner';
            const age = ageIdx !== -1 ? parseInt(row[ageIdx], 10) : NaN;
            return {
                _row: i + 2, lrn, full_name, program_type,
                grade_level:       gradeIdx   !== -1 ? String(row[gradeIdx]   ?? '').trim() || null : null,
                age:               isNaN(age) ? null : age,
                section:           sectionIdx !== -1 ? String(row[sectionIdx] ?? '').trim() || null : null,
                school_year:       syIdx      !== -1 ? String(row[syIdx]      ?? '').trim() || null : null,
                fsl_mastery_level,
            };
        });
}
// ─── Validation ───────────────────────────────────────────────────────────────
const VALID_PROGRAMS     = ['Regular', 'Inclusion', 'SPED', 'Home-based'];
const VALID_MASTERY      = ['Beginner', 'Intermediate', 'Advanced'];
const GRADE_SEC_PROGRAMS = ['Regular', 'Inclusion'];
function validateStudent(s) {
    const errs = {};
    if (!s.lrn || String(s.lrn).trim() === '')             errs.lrn = 'Required';
    else if (!/^\d{12}$/.test(String(s.lrn).trim()))       errs.lrn = 'Must be 12 digits';
    if (!s.full_name || String(s.full_name).trim() === '')  errs.full_name = 'Required';
    if (!s.program_type || !VALID_PROGRAMS.includes(String(s.program_type).trim()))
        errs.program_type = s.program_type ? 'Invalid: "' + s.program_type + '"' : 'Required';
    if (!s.fsl_mastery_level || !VALID_MASTERY.includes(s.fsl_mastery_level))
        errs.fsl_mastery_level = 'Required';
    if (GRADE_SEC_PROGRAMS.includes(String(s.program_type || '').trim())) {
        if (!s.grade_level || String(s.grade_level).trim() === '') errs.grade_level = 'Required';
        if (!s.section     || String(s.section).trim()     === '') errs.section     = 'Required';
    }
    return errs;
}
function isValid(s) { return Object.keys(validateStudent(s)).length === 0; }
function escHtml(str) { return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }

// ─── Single student submit ────────────────────────────────────────────────────
async function submitSingleStudent(event) {
    event.preventDefault();
    hideAlert();
    const btn = document.getElementById('btn-single-submit');
    const nameVal = formSingle.querySelector('input[name="full_name"]').value;
    
    if (!nameVal.includes(',')) { 
        showAlert('Full Name must be "Last Name, First Name" (comma-separated).'); 
        return; 
    }
    
    if (inputLrn.value.replace(/\D/g,'').length !== 12) { 
        showAlert('LRN must be exactly 12 digits.'); 
        return; 
    }
    
    // School year validation
    const syInput = document.getElementById('single-school-year');
    const syVal = syInput ? syInput.value.trim() : '';
    const syErr = sdcValidateSchoolYear(syVal);
    if (syErr) {
        const errEl = document.getElementById('single-sy-error');
        if (errEl) { 
            errEl.textContent = syErr; 
            errEl.classList.remove('hidden'); 
        }
        showAlert('School year: ' + syErr);
        return;
    }
    
    const orig = btn.innerText;
    btn.innerText = 'Checking...'; 
    btn.disabled = true;
    await checkLrnUnique();
    btn.innerText = orig; 
    btn.disabled = false;
    if (lrnExists) return;

    const fd = new FormData(formSingle);
    const showGS = ['Regular','Inclusion'].includes(fd.get('program_type'));
    const payload = { 
        lrn: fd.get('lrn'), 
        full_name: fd.get('full_name'), 
        program_type: fd.get('program_type'), 
        age: fd.get('age'), 
        fsl_mastery_level: fd.get('fsl_mastery_level'), 
        school_year: fd.get('school_year') 
    };
    if (showGS) { 
        payload.grade_level = fd.get('grade_level'); 
        payload.section = fd.get('section'); 
    }

    // Friendly "First Last" display name (form uses "Last, First")
    const [lastPart, firstPart] = nameVal.split(',');
    const displayName = (firstPart ? firstPart.trim() + ' ' + lastPart.trim() : nameVal).trim();

    // ✅ FIX: Force close the Add Student modal immediately
    modal.classList.add('opacity-0');
    modalCard.classList.add('scale-95');
    
    // ✅ FIX: Wait for the modal to fully close before opening the assignment modal
    setTimeout(() => {
        modal.classList.add('hidden');
        resetModal();
        console.log('📖 Opening assignment modal for new student:', displayName);
        openAssignmentModal(null, displayName, payload);
    }, 400);
}
// ─── Step 2: "Review & Import" button → show editable table ──────────────────
// Wired via onclick on the button itself (see renderDataTable call in handleExcelFile)

function renderDataTable() {
    hideAlert();
    document.getElementById('bulk-step-upload').classList.add('hidden');
    const stepTable = document.getElementById('bulk-step-table');
    stepTable.classList.remove('hidden');
    stepTable.innerHTML = '';

    const errCount   = parsedStudents.filter(s => !isValid(s)).length;
    const validCount = parsedStudents.length - errCount;

    // Header bar
    const hdr = document.createElement('div');
    hdr.className = 'flex items-center justify-between mb-4 flex-wrap gap-3';
    hdr.innerHTML =
        '<div class="flex items-center gap-2.5">' +
        '<button type="button" id="btn-back-upload" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 flex items-center justify-center transition-colors shrink-0">' +
        '<span class="material-symbols-outlined text-[18px] text-slate-600">arrow_back</span></button>' +
        '<div><p class="text-[14px] font-bold text-[#0d326b]">Review &amp; Edit Data</p>' +
        '<p class="text-[11px] text-slate-400 font-medium">' + parsedStudents.length + ' students &nbsp;·&nbsp; ' +
        '<span class="text-emerald-600 font-semibold">' + validCount + ' valid</span>' +
        (errCount > 0 ? ' &nbsp;·&nbsp; <span class="text-red-500 font-semibold">' + errCount + ' errors</span>' : '') +
        '</p></div></div>' +
        '<div class="flex items-center gap-2">' +
        '<span id="tbl-err-badge" class="' + (errCount > 0 ? '' : 'hidden ') +
        'text-[11px] font-bold bg-red-50 text-red-500 border border-red-100 px-3 py-1 rounded-full">' + errCount + ' rows need fixing</span>' +
        '<button type="button" id="btn-confirm-import" class="bg-[#0d326b] hover:bg-[#154188] text-white px-6 py-2.5 rounded-xl text-[13px] font-bold transition-all flex items-center gap-1.5">' +
        '<span class="material-symbols-outlined text-[16px]">upload</span>Confirm Import</button></div>';
    stepTable.appendChild(hdr);

    // Legend (only when errors exist)
    if (errCount > 0) {
        const leg = document.createElement('div');
        leg.className = 'flex items-center gap-5 mb-3 text-[11px] text-slate-500 font-medium';
        leg.innerHTML = '<span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded bg-red-100 border border-red-300 shrink-0"></span>Cell has error — edit to fix</span>' +
            '<span class="flex items-center gap-1.5"><span class="inline-block w-3 h-3 rounded bg-white border border-slate-200 shrink-0"></span>Valid — still editable</span>';
        stepTable.appendChild(leg);
    }

    // Table wrapper (scrollable)
    const wrap = document.createElement('div');
    wrap.className = 'overflow-auto rounded-2xl border border-slate-200 shadow-sm';
    wrap.style.maxHeight = '46vh';

    const COLS = [
        { key:'_row',              label:'#',           w:'44px',  edit:false },
        { key:'lrn',               label:'LRN',         w:'140px', edit:true, type:'text'   },
        { key:'full_name',         label:'Student Name',w:'175px', edit:true, type:'text'   },
        { key:'program_type',      label:'Program',     w:'130px', edit:true, type:'select', opts:VALID_PROGRAMS },
        { key:'grade_level',       label:'Grade Level', w:'120px', edit:true, type:'select', opts:['','Grade 1','Grade 2','Grade 3','Grade 4','Grade 5','Grade 6','SPED A','SPED B'] },
        { key:'section',           label:'Section',     w:'105px', edit:true, type:'text'   },
        { key:'age',               label:'Age',         w:'64px',  edit:true, type:'text'   },
        { key:'fsl_mastery_level', label:'FSL Mastery', w:'128px', edit:true, type:'select', opts:VALID_MASTERY },
        { key:'school_year',       label:'School Year', w:'115px', edit:true, type:'text'   },
    ];

    const tbl = document.createElement('table');
    tbl.className = 'w-full text-[12px] border-collapse';
    tbl.style.minWidth = '960px';

    // thead
    tbl.innerHTML = '<thead><tr class="bg-[#f1f5f9] sticky top-0 z-10">' +
        COLS.map(c => '<th class="text-left px-3 py-2.5 font-bold text-[10px] text-slate-500 uppercase tracking-wider whitespace-nowrap border-b border-slate-200" style="min-width:' + c.w + '">' + c.label + '</th>').join('') +
        '</tr></thead>';

    const tbody = document.createElement('tbody');
    tbody.id = 'import-tbody';
    parsedStudents.forEach((s, ri) => tbody.appendChild(buildTblRow(s, ri, COLS)));
    tbl.appendChild(tbody);
    wrap.appendChild(tbl);
    stepTable.appendChild(wrap);

    // Back button
    document.getElementById('btn-back-upload').addEventListener('click', () => {
        stepTable.classList.add('hidden'); stepTable.innerHTML = '';
        document.getElementById('bulk-step-upload').classList.remove('hidden');
    });

    // Confirm Import button
    document.getElementById('btn-confirm-import').addEventListener('click', () => {
        const bad = parsedStudents.filter(s => !isValid(s));
        if (bad.length > 0) {
            const names = bad.slice(0,3).map(s => s.full_name || 'Row ' + s._row).join(', ');
            showAlert(bad.length + ' student' + (bad.length > 1 ? 's' : '') + ' still have errors: ' + names + (bad.length > 3 ? '…' : '') + '. Fix them before importing.', 'warning');
            return;
        }
        runImport();
    });
}

function buildTblRow(s, ri, COLS) {
    const errs     = validateStudent(s);
    const hasError = Object.keys(errs).length > 0;
    const tr       = document.createElement('tr');
    tr.dataset.idx = ri;
    tr.className   = (hasError ? 'bg-red-50/30' : 'bg-white') + ' border-b border-slate-100 hover:brightness-[.98] transition-colors';
    COLS.forEach(col => {
        const td    = document.createElement('td');
        td.className = 'px-2 py-1.5 align-top';
        const cellErr = errs[col.key];
        if (!col.edit) {
            td.innerHTML = '<span class="text-[11px] font-bold text-slate-400">' + s._row + '</span>';
        } else if (col.type === 'select') {
            const sel = document.createElement('select');
            sel.dataset.field = col.key; sel.dataset.idx = ri;
            sel.className = 'tbl-cell w-full text-[12px] font-medium py-1.5 px-2 rounded-lg outline-none border transition-all appearance-none cursor-pointer ' +
                (cellErr ? 'bg-red-50 border-red-300 text-red-700' : 'bg-transparent border-transparent hover:border-slate-300 focus:border-slate-400 text-slate-700');
            col.opts.forEach(o => { const opt = document.createElement('option'); opt.value = o; opt.textContent = o || '— Select —'; if (String(s[col.key]||'') === o) opt.selected = true; sel.appendChild(opt); });
            td.appendChild(sel);
            if (cellErr) { const e = document.createElement('p'); e.className = 'text-[10px] text-red-500 font-semibold mt-0.5 pl-1 leading-none'; e.textContent = cellErr; td.appendChild(e); }
        } else {
            const inp = document.createElement('input'); inp.type = 'text';
            inp.dataset.field = col.key; inp.dataset.idx = ri;
            inp.value = String(s[col.key] ?? ''); inp.placeholder = col.label;
            inp.className = 'tbl-cell w-full text-[12px] font-medium py-1.5 px-2 rounded-lg outline-none border transition-all ' +
                (cellErr ? 'bg-red-50 border-red-300 text-red-700 placeholder:text-red-300' : 'bg-transparent border-transparent hover:border-slate-300 focus:border-slate-400 text-slate-700 placeholder:text-slate-300');
            td.appendChild(inp);
            if (cellErr) { const e = document.createElement('p'); e.className = 'text-[10px] text-red-500 font-semibold mt-0.5 pl-1 leading-none'; e.textContent = cellErr; td.appendChild(e); }
        }
        tr.appendChild(td);
    });
    return tr;
}

// Live cell edit → sync + re-validate
document.getElementById('bulk-step-table').addEventListener('input', function(e) {
    const el = e.target;
    if (!el.dataset || !el.dataset.field) return;
    const idx = parseInt(el.dataset.idx, 10);
    parsedStudents[idx][el.dataset.field] = el.value.trim();
    const errs    = validateStudent(parsedStudents[idx]);
    const cellErr = errs[el.dataset.field];
    const td      = el.parentElement;
    // Style the input/select
    if (cellErr) {
        el.classList.remove('bg-transparent','border-transparent','text-slate-700','placeholder:text-slate-300');
        el.classList.add('bg-red-50','border-red-300','text-red-700');
    } else {
        el.classList.remove('bg-red-50','border-red-300','text-red-700','placeholder:text-red-300');
        el.classList.add('bg-transparent','border-transparent','text-slate-700');
    }
    // Error hint below cell
    let hint = td.querySelector('p');
    if (cellErr) { if (!hint) { hint = document.createElement('p'); hint.className = 'text-[10px] text-red-500 font-semibold mt-0.5 pl-1 leading-none'; td.appendChild(hint); } hint.textContent = cellErr; }
    else if (hint) hint.remove();
    // Row background
    const tr = el.closest('tr');
    const rowBad = Object.keys(validateStudent(parsedStudents[idx])).length > 0;
    tr.className = (rowBad ? 'bg-red-50/30' : 'bg-white') + ' border-b border-slate-100 hover:brightness-[.98] transition-colors';
    // Badge
    const totalBad = parsedStudents.filter(s => !isValid(s)).length;
    const badge = document.getElementById('tbl-err-badge');
    if (badge) { badge.textContent = totalBad + ' rows need fixing'; badge.classList.toggle('hidden', totalBad === 0); }
});

// ─── The actual import POST ───────────────────────────────────────────────────
async function runImport() {
    hideAlert();
    const confirmBtn = document.getElementById('btn-confirm-import');
    if (confirmBtn) { confirmBtn.innerHTML = '<span class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span>Importing…'; confirmBtn.disabled = true; }
    const payload = { students: parsedStudents, auto_pin: document.getElementById('bulk-auto-pin').checked ? 1 : 0 };
    const token   = formSingle.querySelector('input[name="_token"]').value;
    try {
        const res = await axios.post("{{ route('students.import') }}", payload, { headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } });
        if (res.data.success) {
            const d = res.data;
            document.getElementById('bulk-step-table').classList.add('hidden');
            const sr = document.getElementById('bulk-step-result');
            sr.classList.remove('hidden');
            const realErrors = (d.errors||[]).filter(e => !e.reason.startsWith('Warning:'));
            const transfers  = (d.errors||[]).filter(e =>  e.reason.startsWith('Warning:'));
            let failHtml = '';
            if (realErrors.length > 0) {
                failHtml = '<div class="mt-4"><p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-2">Failed Records (' + realErrors.length + ')</p>' +
                    '<div class="max-h-44 overflow-y-auto space-y-2 bg-slate-50 rounded-xl border border-slate-200 p-2">';
                realErrors.forEach((e,n) => {
                    const list = (e.missing&&e.missing.length) ? e.missing.map(m=>'<li>• '+escHtml(m)+'</li>').join('') : '<li>• '+escHtml(e.reason)+'</li>';
                    failHtml += '<div class="text-[11px] bg-white p-2.5 border border-slate-200 rounded-lg">' +
                        '<div class="flex items-center gap-1.5 mb-0.5"><span class="text-[10px] font-bold text-slate-400">'+(n+1)+'.</span>' +
                        '<span class="font-bold text-red-500 bg-red-50 px-1.5 py-0.5 rounded text-[10px]">Row '+e.row+'</span>' +
                        '<span class="font-bold text-[#0d326b] ml-1">'+escHtml(e.name)+'</span></div>' +
                        '<ul class="pl-2 text-slate-500 font-medium leading-snug">'+list+'</ul></div>';
                });
                failHtml += '</div></div>';
            }
            let xferHtml = '';
            if (transfers.length > 0) {
                xferHtml = '<div class="mt-3"><p class="text-[10px] font-bold text-slate-500 uppercase tracking-widest mb-1">Transferred ('+transfers.length+')</p>' +
                    '<div class="text-[11px] text-amber-800 bg-amber-50 border border-amber-100 rounded-xl p-2">' +
                    transfers.map(t=>'<span class="inline-block font-semibold mr-2">'+escHtml(t.name)+'</span>').join('') + '</div></div>';
            }
            sr.innerHTML =
                '<div class="flex items-center gap-3 mb-5">' +
                '<div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center shrink-0"><span class="material-symbols-outlined text-emerald-600 text-[22px]">check_circle</span></div>' +
                '<div><p class="text-[15px] font-bold text-[#0d326b]">Import Complete</p>' +
                '<p class="text-[11px] text-slate-400 font-medium">'+d.imported+' of '+d.total+' students added to your class</p></div></div>' +
                '<div class="grid grid-cols-3 gap-3">' +
                '<div class="bg-blue-50 p-3 rounded-xl text-center"><p class="text-[9px] text-blue-600 font-bold uppercase tracking-widest mb-1">Total</p><p class="text-3xl font-black text-blue-900">'+d.total+'</p></div>' +
                '<div class="bg-emerald-50 p-3 rounded-xl text-center"><p class="text-[9px] text-emerald-600 font-bold uppercase tracking-widest mb-1">Imported</p><p class="text-3xl font-black text-emerald-900">'+d.imported+'</p></div>' +
                '<div class="bg-amber-50 p-3 rounded-xl text-center"><p class="text-[9px] text-amber-600 font-bold uppercase tracking-widest mb-1">Skipped</p><p class="text-3xl font-black text-amber-900">'+d.skipped+'</p></div>' +
                '</div>' + failHtml + xferHtml +
                '<button type="button" onclick="window.location.reload()" class="mt-5 w-full bg-[#0d326b] hover:bg-[#154188] text-white py-3.5 rounded-xl text-[13px] font-bold transition-all shadow-sm flex items-center justify-center gap-2">' +
                '<span class="material-symbols-outlined text-[16px]">refresh</span>Done — Reload Page</button>';
        } else {
            showAlert(res.data.message || 'Import error.', 'error');
            if (confirmBtn) { confirmBtn.innerHTML = '<span class="material-symbols-outlined text-[16px]">upload</span>Confirm Import'; confirmBtn.disabled = false; }
        }
    } catch(err) {
        let msg = 'An error occurred during import.';
        if (err.response?.data?.errors) msg = Object.values(err.response.data.errors).flat().join('<br>');
        else if (err.response?.data?.message) msg = err.response.data.message;
        showAlert(msg);
        if (confirmBtn) { confirmBtn.innerHTML = '<span class="material-symbols-outlined text-[16px]">upload</span>Confirm Import'; confirmBtn.disabled = false; }
    }
}

// ─── AJAX Filtering + Pagination (no page reload) ──────────────────────────────
// The whole "results" panel (table + pagination) below the toolbar is fetched
// and swapped in place. Because everything inside it is handled via event
// delegation on `document`, newly-inserted rows/pagination links/buttons work
// immediately without re-binding listeners.
let searchDebounceTimer = null;
let _resultsAbortController = null;

function setResultsLoading(isLoading) {
    const el = document.getElementById('students-results');
    if (el) el.classList.toggle('is-loading', isLoading);
}

function updateClearButtonVisibility() {
    const search  = document.getElementById('student-search').value.trim();
    const level   = document.getElementById('filter-level').value;
    const program = document.getElementById('filter-program').value;
    const schoolYear = document.getElementById('filter-school-year').value;
    const status  = document.getElementById('filter-status').value;
    const hasFilters = !!(search || level || program || schoolYear || (status && status !== 'active'));
    const wrap = document.getElementById('clear-filters-wrap');
    if (wrap) wrap.classList.toggle('hidden', !hasFilters);
}

function fetchStudentsResults(url, fetchOptions) {
    fetchOptions = fetchOptions || {};
    
    // ✅ FIX: Ensure HTTPS if page is loaded over HTTPS
    if (window.location.protocol === 'https:' && url && url.startsWith('http://')) {
        url = url.replace('http://', 'https://');
    }
    
    if (_resultsAbortController) _resultsAbortController.abort();
    _resultsAbortController = new AbortController();

    setResultsLoading(true);

    const headers = Object.assign({ 'X-Requested-With': 'XMLHttpRequest' }, fetchOptions.headers || {});

    return fetch(url, Object.assign({}, fetchOptions, { headers: headers, signal: _resultsAbortController.signal }))
        .then(function (res) { return res.text(); })
        .then(function (html) {
            const doc = new DOMParser().parseFromString(html, 'text/html');
            const newResults = doc.getElementById('students-results');
            const oldResults = document.getElementById('students-results');
            if (newResults && oldResults) {
                oldResults.replaceWith(newResults);
            } else {
                setResultsLoading(false);
            }
            updateClearButtonVisibility();
        })
        .catch(function (err) {
            if (err.name === 'AbortError') return;
            setResultsLoading(false);
            alert('Something went wrong loading students. Please try again.');
        });
}

function applyServerFilters(overrides) {
    overrides = overrides || {};
    const searchVal  = overrides.hasOwnProperty('search')  ? overrides.search  : document.getElementById('student-search').value.trim();
    const levelVal   = overrides.hasOwnProperty('level')   ? overrides.level   : document.getElementById('filter-level').value;
    const programVal = overrides.hasOwnProperty('program') ? overrides.program : document.getElementById('filter-program').value;
    const schoolYearVal = overrides.hasOwnProperty('school_year') ? overrides.school_year : document.getElementById('filter-school-year').value;
    const statusVal  = overrides.hasOwnProperty('status')  ? overrides.status  : document.getElementById('filter-status').value;

    const tokenInput = document.querySelector('#studentFilterForm input[name="_token"]');
    const token = tokenInput ? tokenInput.value : '';

    const body = new URLSearchParams();
    body.set('_token', token);
    body.set('search', searchVal);
    body.set('level', levelVal);
    body.set('program', programVal);
    body.set('school_year', schoolYearVal);
    body.set('status', statusVal);

    fetchStudentsResults("{{ route('students.filter') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': token,
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: body.toString(),
    });
}

// Search box (debounced) + Enter to apply immediately
document.addEventListener('input', function (e) {
    if (e.target && e.target.id === 'student-search') {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(function () { applyServerFilters(); }, 500);
    }
});
document.addEventListener('keydown', function (e) {
    if (e.target && e.target.id === 'student-search' && e.key === 'Enter') {
        e.preventDefault();
        clearTimeout(searchDebounceTimer);
        applyServerFilters();
    }
});

// Level / Program / School Year / Status dropdowns
document.addEventListener('change', function (e) {
    if (e.target && (e.target.id === 'filter-level' || e.target.id === 'filter-program' || e.target.id === 'filter-school-year' || e.target.id === 'filter-status')) {
        applyServerFilters();
    }
});

// Delegated clicks: clear button, pagination links, promote/demote
document.addEventListener('click', function (e) {
    const clearBtn = e.target.closest('#clear-filters-btn');
    if (clearBtn) {
        document.getElementById('student-search').value = '';
        document.getElementById('filter-level').value = '';
        document.getElementById('filter-program').value = '';
        document.getElementById('filter-school-year').value = '';
        document.getElementById('filter-status').value = 'active';
        applyServerFilters({ search: '', level: '', program: '', school_year: '', status: 'active' });
        return;
    }

    // Pagination links rendered inside #students-results (e.g. Laravel's
    // pagination::tailwind view) — intercept and fetch instead of navigating.
const pageLink = e.target.closest('#students-results a[href]');
if (pageLink) {
    e.preventDefault();
    // ✅ FIX: Use relative URL to avoid protocol mismatch
    const url = new URL(pageLink.href);
    const relativeUrl = url.pathname + url.search;
    fetchStudentsResults(relativeUrl, { method: 'GET' });
    return;
}
    const promoteBtn = e.target.closest('.promote-btn');
    if (promoteBtn) { return; } // handled inside Student Details modal

    const demoteBtn = e.target.closest('.demote-btn');
    if (demoteBtn) { return; } // handled inside Student Details modal
});

document.addEventListener('DOMContentLoaded', function () {
    updateClearButtonVisibility();
});

// ─── Student Details Modal ───────────────────────────────────────────────────
const sdModal      = document.getElementById('student-details-modal');
const sdCard       = document.getElementById('student-details-card');
const sdLoading    = document.getElementById('sdc-loading');
const sdContent    = document.getElementById('sdc-content');
const sdNotif      = document.getElementById('sdc-notif');
let _sdCurrent     = null; // current student data object

const lvlMeta = {
    'Beginner':     { cssClass:'beginner',     barColor:'#f59e0b' },
    'Intermediate': { cssClass:'intermediate',  barColor:'#3b82f6' },
    'Advanced':     { cssClass:'advanced',      barColor:'#1e4b8f' },
    'Completed':    { cssClass:'completed',     barColor:'#0d326b' },
};

// ── Edit mode ────────────────────────────────────────────────────────────────
let _sdEditing = false;

function sdcEnterEdit() {
    _sdEditing = true;
    const s = _sdCurrent;
    // Populate inputs with current values
    document.getElementById('sdc-edit-fullname').value    = s.last_name && s.first_name ? s.last_name + ', ' + s.first_name : s.full_name;
    document.getElementById('sdc-edit-age').value         = s.age || '';
    document.getElementById('sdc-edit-program').value     = s.program_type || 'Regular';
    document.getElementById('sdc-edit-grade').value       = s.grade_level || '';
    document.getElementById('sdc-edit-section').value     = s.section || '';
    document.getElementById('sdc-edit-school-year').value = s.school_year || '';
    document.getElementById('sdc-edit-lrn').value         = s.lrn || '';
    document.getElementById('sdc-sy-error').classList.add('hidden');

    // Show edit fields, hide read-only
    document.querySelectorAll('#sdc-content .view-only').forEach(el => el.classList.add('hidden'));
    document.querySelectorAll('#sdc-content .edit-only').forEach(el => el.classList.remove('hidden'));

    // Toggle header buttons
    document.getElementById('sdc-edit-btn').classList.add('hidden');
    document.getElementById('sdc-save-btn').classList.remove('hidden');
    document.getElementById('sdc-cancel-edit-btn').classList.remove('hidden');

    // Add a subtle amber border to the right panel to signal edit mode
    document.getElementById('sdc-content').classList.add('ring-2','ring-amber-200','ring-inset','rounded-b-[28px]');
}

function sdcExitEdit() {
    _sdEditing = false;
    document.querySelectorAll('#sdc-content .view-only').forEach(el => el.classList.remove('hidden'));
    document.querySelectorAll('#sdc-content .edit-only').forEach(el => el.classList.add('hidden'));
    document.getElementById('sdc-edit-btn').classList.remove('hidden');
    document.getElementById('sdc-save-btn').classList.add('hidden');
    document.getElementById('sdc-cancel-edit-btn').classList.add('hidden');
    document.getElementById('sdc-content').classList.remove('ring-2','ring-amber-200','ring-inset','rounded-b-[28px]');
    document.getElementById('sdc-sy-error').classList.add('hidden');
}

function sdcValidateSchoolYear(val) {
    if (!val || val.trim() === '') return null; // optional field — blank is ok
    const m = val.trim().match(/^(\d{4})-(\d{4})$/);
    if (!m) return 'Must be in YYYY-YYYY format (e.g. 2024-2025).';
    const y1 = parseInt(m[1]), y2 = parseInt(m[2]);
    if (y2 !== y1 + 1) return 'Second year must be exactly one after the first (e.g. ' + y1 + '-' + (y1+1) + ').';
    const now = new Date().getFullYear();
    if (y1 < 2000 || y1 > now + 2) return 'School year ' + val + ' is out of a reasonable range.';
    return null;
}

// Auto-format school year inputs: insert dash after 4 digits, live validation
document.addEventListener('input', function(e) {
    const ids = ['sdc-edit-school-year', 'single-school-year'];
    if (!e.target || !ids.includes(e.target.id)) return;

    let v = e.target.value.replace(/[^\d-]/g, '');
    if (v.length === 4 && !v.includes('-')) v = v + '-';
    if (v.length > 9) v = v.slice(0, 9);
    e.target.value = v;

    const err = sdcValidateSchoolYear(v);

    if (e.target.id === 'sdc-edit-school-year') {
        const errEl = document.getElementById('sdc-sy-error');
        errEl.textContent = err || '';
        errEl.classList.toggle('hidden', !err);
        e.target.classList.toggle('border-red-300', !!err);
        e.target.classList.toggle('border-slate-200', !err);
    } else {
        const errEl = document.getElementById('single-sy-error');
        errEl.textContent = err || '';
        errEl.classList.toggle('hidden', !err);
        e.target.classList.toggle('border-red-400', !!err);
        e.target.classList.toggle('border-transparent', !err);
    }
});

document.getElementById('sdc-edit-btn').addEventListener('click', sdcEnterEdit);
document.getElementById('sdc-cancel-edit-btn').addEventListener('click', () => {
    // populateStudentDetails already calls sdcExitEdit internally
    populateStudentDetails(_sdCurrent);
});
document.getElementById('sdc-save-btn').addEventListener('click', async () => {
    const fullName  = document.getElementById('sdc-edit-fullname').value.trim();
    const syVal     = document.getElementById('sdc-edit-school-year').value.trim();
    const syErr     = sdcValidateSchoolYear(syVal);
    if (!fullName) { sdNotifShow('Full name is required.', 'error'); return; }
    if (syErr)     { sdNotifShow('School year: ' + syErr, 'error'); return; }

    const token = document.querySelector('#studentFilterForm input[name="_token"]').value;
    const saveBtn = document.getElementById('sdc-save-btn');
    const origHtml = saveBtn.innerHTML;
    saveBtn.innerHTML = '<span class="material-symbols-outlined text-[15px] animate-spin">progress_activity</span><span>Saving…</span>';
    saveBtn.disabled = true;

    try {
        const res = await axios.put('/students/' + _sdCurrent.student_id, {
            full_name:         fullName,
            age:               document.getElementById('sdc-edit-age').value || null,
            program_type:      document.getElementById('sdc-edit-program').value,
            grade_level:       document.getElementById('sdc-edit-grade').value || null,
            section:           document.getElementById('sdc-edit-section').value || null,
            school_year:       syVal || null,
            lrn:               document.getElementById('sdc-edit-lrn').value.trim() || null,
            fsl_mastery_level: _sdCurrent.fsl_mastery_level,
            _method:           'PUT',
        }, { headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } });

        if (res.data.success) {
            sdNotifShow(res.data.message, 'success');
            sdcExitEdit();
            // Re-fetch fresh data
            fetch('/students/' + _sdCurrent.student_id, {
                method: 'GET', credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }
            }).then(r => r.json()).then(fresh => {
                if (fresh.success) { _sdCurrent = fresh.student; populateStudentDetails(fresh.student); applyServerFilters(); }
            });
        } else {
            sdNotifShow(res.data.message || 'Update failed.', 'error');
        }
    } catch (err) {
        let msg = 'Failed to save changes.';
        if (err.response?.data?.errors) msg = Object.values(err.response.data.errors).flat().join(' ');
        else if (err.response?.data?.message) msg = err.response.data.message;
        sdNotifShow(msg, 'error');
    } finally {
        saveBtn.innerHTML = origHtml;
        saveBtn.disabled = false;
    }
});

// ── Open / close ──────────────────────────────────────────────────────────────
function openStudentDetails(studentId) {
    sdLoading.classList.remove('hidden');
    sdContent.classList.add('hidden');
    sdNotif.classList.add('hidden');
    sdModal.classList.remove('hidden');
    requestAnimationFrame(() => { sdModal.classList.remove('opacity-0'); sdCard.classList.remove('scale-95'); });
    const token = document.querySelector('#studentFilterForm input[name="_token"]').value;
    fetch('/students/' + studentId, {
        method: 'GET',
        credentials: 'same-origin',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': token, 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => {
        if (!r.ok) { throw new Error('HTTP ' + r.status); }
        return r.json();
    })
    .then(res => {
        if (!res.success) { sdNotifShow('Failed to load student: ' + (res.message || 'Unknown error'), 'error'); return; }
        _sdCurrent = res.student;
        populateStudentDetails(res.student);
        sdLoading.classList.add('hidden');
        sdContent.classList.remove('hidden');
    })
    .catch(err => {
        sdLoading.classList.add('hidden');
        sdNotifShow('Error loading student details: ' + err.message, 'error');
    });
}
function closeStudentDetails() {
    if (_sdEditing) sdcExitEdit();
    sdModal.classList.add('opacity-0'); sdCard.classList.add('scale-95');
    setTimeout(() => { sdModal.classList.add('hidden'); _sdCurrent = null; }, 300);
}
document.getElementById('sdc-close').addEventListener('click', closeStudentDetails);
sdModal.addEventListener('click', e => { if (e.target === sdModal) closeStudentDetails(); });

// ── Populate all fields ───────────────────────────────────────────────────────
function populateStudentDetails(s) {
    // Always exit edit mode when populating fresh data
    if (_sdEditing) sdcExitEdit();

    const lvl  = s.fsl_mastery_level;
    const meta = lvlMeta[lvl] || lvlMeta['Beginner'];

    // Left panel
    document.getElementById('sdc-avatar').src = s.avatar_url;
    document.getElementById('sdc-name').textContent = s.full_name;
    document.getElementById('sdc-lrn-display').textContent = 'LRN: ' + (s.lrn || 'N/A');

    const lvlBadge = document.getElementById('sdc-level-badge');
    lvlBadge.className = 'lvl-badge ' + meta.cssClass + ' text-[11px] px-3 py-1';
    lvlBadge.textContent = lvl;

    const statusBadge = document.getElementById('sdc-status-badge');
    const isActive = s.status === 'active';
    statusBadge.className = 'text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wider ' +
        (isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-500');
    statusBadge.textContent = s.status;

    const dot = document.getElementById('sdc-status-dot');
    dot.className = 'absolute -bottom-1 -right-1 w-5 h-5 rounded-full border-2 border-white ' + (isActive ? 'bg-emerald-400' : 'bg-slate-300');

    const xpPct = s.xp_bar_pct || 0;
    document.getElementById('sdc-xp-bar').style.width = xpPct + '%';
    document.getElementById('sdc-xp-bar').style.background = 'linear-gradient(90deg,' + meta.barColor + '88,' + meta.barColor + ')';
    document.getElementById('sdc-xp-fraction').textContent = (s.total_xp || 0).toLocaleString() + ' / ' + (s.required_xp || 0).toLocaleString() + ' XP';
    document.getElementById('sdc-xp-hint').textContent = s.promote_to
        ? (s.lessons_ready
            ? '✓ Ready for Promotion to ' + s.promote_to
            : (s.lessons_total > 0
                ? (s.lessons_completed || 0) + '/' + s.lessons_total + ' lessons done'
                : 'No lessons assigned yet'))
        : 'Max level reached';
    document.getElementById('sdc-streak').textContent = (s.streak_days || 0) + ' days';
    document.getElementById('sdc-level-num').textContent = 'Lv. ' + (s.level || 1);
    document.getElementById('sdc-last-active').textContent = s.last_activity_date || '—';

    // Personal
    document.getElementById('sdc-fullname').textContent    = s.full_name || '—';
    document.getElementById('sdc-age').textContent         = s.age ? s.age + ' yrs' : '—';
    document.getElementById('sdc-username').textContent    = s.username || '—';
    document.getElementById('sdc-email').textContent       = s.email || '—';

    // Academic
    document.getElementById('sdc-program').textContent     = s.program_type || '—';
    document.getElementById('sdc-grade').textContent       = s.grade_level  || '—';
    document.getElementById('sdc-section').textContent     = s.section      || '—';
    document.getElementById('sdc-school-year').textContent = s.school_year  || '—';

    // Account
    document.getElementById('sdc-lrn').textContent     = s.lrn || '—';
    document.getElementById('sdc-created').textContent = s.created_at || '—';
    document.getElementById('sdc-updated').textContent = s.updated_at || '—';

    // PIN – hidden by default, show/hide via eye button
    const pinEl  = document.getElementById('sdc-pin');
    const pinEye = document.getElementById('sdc-pin-eye');
    pinEl._realPin = s.pin || '—';
    pinEl.textContent = '••••';
    pinEye.textContent = 'visibility_off';
    // Re-wire toggle each time (replace to avoid stacked listeners)
    const newToggle = document.getElementById('sdc-pin-toggle').cloneNode(true);
    document.getElementById('sdc-pin-toggle').replaceWith(newToggle);
    newToggle.addEventListener('click', () => {
        const eye = newToggle.querySelector('#sdc-pin-eye');
        if (pinEl.textContent === '••••') {
            pinEl.textContent = pinEl._realPin;
            eye.textContent = 'visibility';
        } else {
            pinEl.textContent = '••••';
            eye.textContent = 'visibility_off';
        }
    });

    // History
    sdcRenderHistory(s.promotions || []);

    // Management panel
    sdcSetupManagement(s);
}

// ── Promotion history ─────────────────────────────────────────────────────────
function sdcRenderHistory(history) {
    const list  = document.getElementById('sdc-history-list');
    const empty = document.getElementById('sdc-history-empty');
    list.innerHTML = '';
    if (!history.length) { empty.classList.remove('hidden'); return; }
    empty.classList.add('hidden');
    history.forEach(h => {
        const fm = lvlMeta[h.from] || lvlMeta['Beginner'];
        const tm = lvlMeta[h.to]   || lvlMeta['Beginner'];
        const forcedTag = h.forced ? '<span style="background:#fef3c7;color:#92400e;font-size:9px;font-weight:700;padding:1px 5px;border-radius:9999px;margin-left:4px">forced</span>' : '';
        list.innerHTML += `<div class="hist-item">
            <div class="hist-dot" style="background:${tm.barColor}"></div>
            <div class="flex-1">
                <div class="flex items-center flex-wrap gap-1">
                    <span class="lvl-badge ${fm.cssClass}" style="font-size:9px;padding:2px 6px">${h.from}</span>
                    <span style="color:#94a3b8;font-size:11px">→</span>
                    <span class="lvl-badge ${tm.cssClass}" style="font-size:9px;padding:2px 6px">${h.to}</span>
                    ${forcedTag}
                </div>
                <div class="flex justify-between mt-1">
                    <span style="font-size:10px;color:#94a3b8">${h.date || ''}</span>
                    <span style="font-size:10px;font-weight:700;color:#0d326b">${Number(h.xp||0).toLocaleString()} XP</span>
                </div>
            </div></div>`;
    });
}

// ── Management panel wiring ───────────────────────────────────────────────────
function sdcSetupManagement(s) {
    const enrollBtn   = document.getElementById('sdc-enroll-btn');
    const unenrollBtn = document.getElementById('sdc-unenroll-btn');
    const promoteBtn  = document.getElementById('sdc-promote-btn');
    const demoteBtn   = document.getElementById('sdc-demote-btn');
    const promoteLabel  = document.getElementById('sdc-promote-label');
    const promoteHint   = document.getElementById('sdc-promote-hint');
    const demoteLabel   = document.getElementById('sdc-demote-label');
    const demoteHint    = document.getElementById('sdc-demote-hint');
    const statusText    = document.getElementById('sdc-enroll-status-text');

     const assignmentHint = document.getElementById('sdc-assignment-hint');
if (assignmentHint && s) {
    // You could fetch the count from s if available, or keep it generic
    assignmentHint.textContent = 'Manage which lessons this student has access to.';
}
    const isActive = s.status === 'active';
    statusText.textContent = isActive ? 'Student is currently enrolled and active.' : 'Student is currently unenrolled.';
    enrollBtn.disabled   = isActive;
    unenrollBtn.disabled = !isActive;
    enrollBtn.className  = 'flex-1 py-2.5 rounded-xl text-[12px] font-bold transition-all flex items-center justify-center gap-1.5 ' +
        (isActive ? 'bg-slate-100 text-slate-400 cursor-not-allowed' : 'bg-emerald-500 hover:bg-emerald-600 text-white cursor-pointer');
    unenrollBtn.className = 'flex-1 py-2.5 rounded-xl text-[12px] font-bold transition-all flex items-center justify-center gap-1.5 ' +
        (isActive ? 'bg-red-50 hover:bg-red-100 text-red-600 border border-red-100 cursor-pointer' : 'bg-slate-100 text-slate-400 cursor-not-allowed border border-transparent');

    // Promote
    if (s.promote_to) {
        promoteBtn.classList.remove('hidden');
        promoteLabel.textContent = 'Promote to ' + s.promote_to;

        const ready = !!s.lessons_ready;
        const total = s.lessons_total || 0;
        const done  = s.lessons_completed || 0;

        // Build hint text
        if (total === 0) {
            promoteHint.innerHTML = '<span style="color:#d97706">⚠ No lessons assigned for this level yet.</span>';
        } else if (ready) {
            promoteHint.innerHTML = '✓ <strong>All lessons completed</strong> — ' + done + '/' + total + ' lessons done';
        } else {
            promoteHint.textContent = done + '/' + total + ' lessons completed (force promote allowed)';
        }

       
        // Gold card highlight when ready
        const promoteCard = document.getElementById('sdc-promote-card');
        const promoteIconWrap = document.getElementById('sdc-promote-icon-wrap');
        const readyBadge = document.getElementById('sdc-ready-badge');

        if (ready) {
            promoteCard.classList.add('border-amber-300', 'bg-amber-50/60');
            promoteCard.classList.remove('border-slate-100', 'bg-white');
            promoteIconWrap.classList.add('bg-amber-100');
            promoteIconWrap.classList.remove('bg-blue-50');
            promoteIconWrap.querySelector('.material-symbols-outlined').style.color = '#b45309';
            readyBadge.classList.remove('hidden');
            promoteHint.classList.add('text-amber-800');
            promoteHint.classList.remove('text-slate-400');
            promoteBtn.className = 'w-full py-2.5 rounded-xl text-[12px] font-bold transition-all flex items-center justify-center gap-1.5 bg-amber-500 hover:bg-amber-600 text-white shadow-sm';
        } else {
            promoteCard.classList.remove('border-amber-300', 'bg-amber-50/60');
            promoteCard.classList.add('border-slate-100', 'bg-white');
            promoteIconWrap.classList.remove('bg-amber-100');
            promoteIconWrap.classList.add('bg-blue-50');
            promoteIconWrap.querySelector('.material-symbols-outlined').style.color = '';
            readyBadge.classList.add('hidden');
            promoteHint.classList.remove('text-amber-800');
            promoteHint.classList.add('text-slate-400');
            promoteBtn.className = 'w-full py-2.5 rounded-xl text-[12px] font-bold transition-all flex items-center justify-center gap-1.5 bg-slate-200 hover:bg-slate-300 text-slate-600';
        }
    } else {
        promoteBtn.classList.add('hidden');
        promoteHint.textContent = 'Max level reached.';
        const promoteCard = document.getElementById('sdc-promote-card');
        promoteCard.classList.remove('border-amber-300', 'bg-amber-50/60');
        promoteCard.classList.add('border-slate-100', 'bg-white');
        document.getElementById('sdc-ready-badge').classList.add('hidden');
    }

    // Demote
    if (s.demote_to) {
        demoteBtn.classList.remove('hidden');
        demoteLabel.textContent = 'Demote to ' + s.demote_to;
        demoteHint.textContent  = 'Moves ' + s.full_name.split(' ')[0] + ' from ' + s.fsl_mastery_level + ' → ' + s.demote_to + '.';
    } else {
        demoteBtn.classList.add('hidden');
        demoteHint.textContent = 'Cannot demote — already at Beginner.';
    }
}

// ── Notification bar inside the modal ────────────────────────────────────────
function sdNotifShow(msg, type) {
    sdNotif.classList.remove('hidden', 'bg-emerald-50','border-emerald-200','text-emerald-800',
        'bg-red-50','border-red-200','text-red-800','bg-amber-50','border-amber-200','text-amber-800');
    const map = { success:['bg-emerald-50','border-emerald-200','text-emerald-800','check_circle'],
                  error:  ['bg-red-50','border-red-200','text-red-800','error'],
                  warning:['bg-amber-50','border-amber-200','text-amber-800','warning'] };
    const [bg,border,txt,icon] = map[type] || map.error;
    sdNotif.classList.add(bg, border, txt);
    sdNotif.innerHTML = `<span class="material-symbols-outlined text-[18px] shrink-0">${icon}</span><span>${msg}</span>`;
    setTimeout(() => sdNotif.classList.add('hidden'), 4000);
}

// ── Confirm dialog helper ─────────────────────────────────────────────────────
function sdcConfirm({ title, body, okLabel, okClass, onConfirm }) {
    const cm   = document.getElementById('sdc-confirm-modal');
    const cc   = document.getElementById('sdc-confirm-card');
    const iw   = document.getElementById('sdc-confirm-icon-wrap');
    const ico  = document.getElementById('sdc-confirm-icon');
    const tEl  = document.getElementById('sdc-confirm-title');
    const bEl  = document.getElementById('sdc-confirm-body');
    const okEl = document.getElementById('sdc-confirm-ok');
    const canEl = document.getElementById('sdc-confirm-cancel');

    tEl.textContent  = title;
    bEl.textContent  = body;
    okEl.textContent = okLabel;
    okEl.className   = 'flex-1 py-2.5 rounded-xl text-white text-[13px] font-bold transition-all ' + (okClass || 'bg-[#0d326b] hover:bg-[#154188]');

    cm.classList.remove('hidden');
    requestAnimationFrame(() => { cm.classList.remove('opacity-0'); cc.classList.remove('scale-95'); });

    function closeConfirm() { cm.classList.add('opacity-0'); cc.classList.add('scale-95'); setTimeout(() => cm.classList.add('hidden'), 200); }
    canEl.onclick = closeConfirm;
    cm.onclick = e => { if (e.target === cm) closeConfirm(); };
    okEl.onclick = () => { closeConfirm(); onConfirm(); };
}

// ── Enroll / Unenroll ─────────────────────────────────────────────────────────
document.getElementById('sdc-enroll-btn').addEventListener('click', () => {
    if (!_sdCurrent || _sdCurrent.status === 'active') return;
    sdcConfirm({
        title: 'Enroll Student',
        body: 'Are you sure you want to enroll ' + _sdCurrent.full_name + '? They will have full access to lessons and progress tracking.',
        okLabel: 'Enroll', okClass: 'bg-emerald-500 hover:bg-emerald-600',
        onConfirm: () => sdcAction('/students/' + _sdCurrent.student_id + '/enroll', {}, 'enroll'),
    });
});
document.getElementById('sdc-unenroll-btn').addEventListener('click', () => {
    if (!_sdCurrent || _sdCurrent.status !== 'active') return;
    sdcConfirm({
        title: 'Unenroll Student',
        body: 'Are you sure you want to unenroll ' + _sdCurrent.full_name + '? This action can be reversed by enrolling them again.',
        okLabel: 'Unenroll', okClass: 'bg-red-500 hover:bg-red-600',
        onConfirm: () => sdcAction('/students/' + _sdCurrent.student_id + '/unenroll', {}, 'unenroll'),
    });
});

// ── Promote / Demote ──────────────────────────────────────────────────────────
document.getElementById('sdc-promote-btn').addEventListener('click', () => {
    if (!_sdCurrent || !_sdCurrent.promote_to) return;
    const tgt   = _sdCurrent.promote_to;
    const force = !_sdCurrent.lessons_ready;
    const total = _sdCurrent.lessons_total || 0;
    const done  = _sdCurrent.lessons_completed || 0;
    const bodyMsg = force
        ? 'This student has only completed ' + done + '/' + total + ' lessons. Are you sure you want to force promote ' + _sdCurrent.full_name + ' to ' + tgt + '?'
        : 'Are you sure you want to promote ' + _sdCurrent.full_name + ' to ' + tgt + '? They have completed all ' + total + ' lessons.';
    sdcConfirm({
        title: force ? 'Force Promote?' : 'Promote Student',
        body: bodyMsg,
        okLabel: force ? 'Promote Anyway' : 'Promote to ' + tgt,
        okClass: force ? 'bg-slate-600 hover:bg-slate-700' : 'bg-amber-500 hover:bg-amber-600',
        onConfirm: () => sdcAction('/students/' + _sdCurrent.student_id + '/promote', { target_level: tgt, force: force ? 1 : 0 }, 'promote'),
    });
});
document.getElementById('sdc-demote-btn').addEventListener('click', () => {
    if (!_sdCurrent || !_sdCurrent.demote_to) return;
    const tgt = _sdCurrent.demote_to;
    sdcConfirm({
        title: 'Demote Student',
        body: 'Are you sure you want to demote ' + _sdCurrent.full_name + ' from ' + _sdCurrent.fsl_mastery_level + ' to ' + tgt + '?',
        okLabel: 'Demote to ' + tgt, okClass: 'bg-red-500 hover:bg-red-600',
        onConfirm: () => sdcAction('/students/' + _sdCurrent.student_id + '/demote', { target_level: tgt }, 'demote'),
    });
});

// ── Generic action handler ────────────────────────────────────────────────────
async function sdcAction(url, data, type) {
    const token = document.querySelector('#studentFilterForm input[name="_token"]').value;

    const enrollBtn   = document.getElementById('sdc-enroll-btn');
    const unenrollBtn = document.getElementById('sdc-unenroll-btn');
    const promoteBtn  = document.getElementById('sdc-promote-btn');
    const demoteBtn   = document.getElementById('sdc-demote-btn');

    // Determine active button to show spinner
    let activeBtn = null;
    if (type === 'enroll') activeBtn = enrollBtn;
    else if (type === 'unenroll') activeBtn = unenrollBtn;
    else if (type === 'promote') activeBtn = promoteBtn;
    else if (type === 'demote') activeBtn = demoteBtn;

    let origHtml = '';
    if (activeBtn) {
        origHtml = activeBtn.innerHTML;
        let label = 'Processing…';
        if (type === 'enroll') label = 'Enrolling…';
        else if (type === 'unenroll') label = 'Removing…';
        else if (type === 'promote') label = 'Promoting…';
        else if (type === 'demote') label = 'Demoting…';

        activeBtn.innerHTML = `<span class="material-symbols-outlined text-[13px] animate-spin">progress_activity</span><span>${label}</span>`;
    }

    // Disable all buttons to prevent concurrent actions
    const btns = [enrollBtn, unenrollBtn, promoteBtn, demoteBtn];
    const origDisabledStates = btns.map(b => b.disabled);
    btns.forEach(b => b.disabled = true);

    try {
        const res = await axios.post(url, data, { headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } });

        // Restore HTML before running populateStudentDetails to ensure the DOM is intact
        if (activeBtn) activeBtn.innerHTML = origHtml;

        if (res.data.success) {
            sdNotifShow(res.data.message, 'success');
            
            // 🔥 FIX: Store student data properly
            const studentData = res.data.student;
            
            if (studentData) {
                _sdCurrent = studentData;
                populateStudentDetails(studentData);

                // Keep the student visible in the dashboard after an enroll/unenroll,
                // even if the status change would otherwise filter it out of the
                // currently selected view (e.g. unenrolling while viewing "Enrolled").
                const statusFilterEl = document.getElementById('filter-status');
                const currentStatusFilter = statusFilterEl ? statusFilterEl.value : 'active';
                const newStatus = studentData.status === 'active' ? 'active' : 'inactive';
                if ((type === 'enroll' || type === 'unenroll') && currentStatusFilter !== 'all' && currentStatusFilter !== newStatus) {
                    if (statusFilterEl) statusFilterEl.value = 'all';
                    applyServerFilters({ status: 'all' });
                } else {
                    applyServerFilters();
                }
                
                // 🔥 SHOW ASSIGNMENT MODAL AFTER ENROLLMENT
                if (type === 'enroll' && res.data.show_assignment_modal) {
                    console.log('🎯 Enrollment successful, showing assignment modal for:', studentData.full_name);
                    
                    // Close the student details modal
                    sdModal.classList.add('opacity-0');
                    sdCard.classList.add('scale-95');
                    
                    // Wait for close animation, then open assignment modal
                    setTimeout(() => {
                        sdModal.classList.add('hidden');
                        // Open assignment modal
                        openAssignmentModal(
                            studentData.student_id,
                            studentData.full_name
                        );
                    }, 400);
                }
            }
            
            // Re-enable buttons
            btns.forEach((b, i) => b.disabled = origDisabledStates[i]);
        } else {
            sdNotifShow(res.data.message || 'Action failed.', 'error');
            btns.forEach((b, i) => b.disabled = origDisabledStates[i]);
        }
    } catch (err) {
        console.error('❌ Error in sdcAction:', err);
        sdNotifShow(err.response?.data?.message || 'Something went wrong.', 'error');
        // Restore HTML and disabled states if error
        if (activeBtn) activeBtn.innerHTML = origHtml;
        btns.forEach((b, i) => b.disabled = origDisabledStates[i]);
    }
}

// ── Delegated click: View button ──────────────────────────────────────────────
document.addEventListener('click', function(e) {
    const viewBtn = e.target.closest('.view-student-btn');
    if (viewBtn) { openStudentDetails(viewBtn.dataset.studentId); return; }
});

// ─── Mastery Donut Tooltip ──────────────────────────────────────────────────
document.querySelectorAll('.mastery-donut-hit').forEach(function(seg){
    const donutWrap=seg.closest('.relative.w-\\[120px\\]');
    const tip=donutWrap?.querySelector('#masteryDonutTooltip');
    if(!tip)return;
    const tipLabel=tip.querySelector('#masteryDonutTooltipLabel');
    const tipValue=tip.querySelector('#masteryDonutTooltipValue');
    seg.addEventListener('mouseenter',function(){
        tipLabel.textContent=seg.dataset.label;
        tipValue.textContent=seg.dataset.value;
        tip.classList.remove('opacity-0');
        tip.classList.add('opacity-100');
    });
    seg.addEventListener('mouseleave',function(){
        tip.classList.remove('opacity-100');
        tip.classList.add('opacity-0');
    });
});

// ─── LESSON ASSIGNMENT MODAL ──────────────────────────────────────────────────
const assignModal = document.getElementById('assignment-modal');
const assignCard = document.getElementById('assignment-modal-card');
const assignLoading = document.getElementById('assignment-loading');
const assignContent = document.getElementById('assignment-content');
const assignList = document.getElementById('assignment-list');
const assignNotif = document.getElementById('assignment-notif');
const assignStudentName = document.getElementById('assignment-student-name');
const assignTotal = document.getElementById('assignment-total');
const assignSelected = document.getElementById('assignment-selected');

let _assignStudentId = null;
let _assignModules = [];
let _assignSelectedIds = new Set();
let _assignOriginalIds = new Set(); // To track changes
let _assignPendingPayload = null; // Set when picking lessons for a NOT-YET-created student

const assignTitleLabel = document.getElementById('assignment-title-label');
const assignSaveBtn = document.getElementById('assignment-save');
const assignSaveLabel = document.getElementById('assignment-save-label');

// studentId: existing student's id, or null when adding a new student
// pendingPayload: when adding a new student, pass the validated form payload here.
//                 Save will then create the student + assign these lessons together.
function openAssignmentModal(studentId, studentName, pendingPayload) {
    console.log('📖 Opening assignment modal for:', studentId, studentName);
    
    _assignStudentId = studentId;
    _assignPendingPayload = pendingPayload || null;

    if (_assignPendingPayload) {
        assignStudentName.textContent = 'Choose lessons to assign to ' + studentName + ' — saved together when you confirm.';
        if (assignTitleLabel) assignTitleLabel.textContent = 'Assign Lessons on Enrollment';
        if (assignSaveLabel) assignSaveLabel.textContent = 'Confirm & Add Student';
    } else {
        assignStudentName.textContent = 'Assign lessons for: ' + studentName;
        if (assignTitleLabel) assignTitleLabel.textContent = 'Assign Lessons';
        if (assignSaveLabel) assignSaveLabel.textContent = 'Save Assignments';
    }

    // Reset button state
    if (assignSaveBtn) {
        assignSaveBtn.disabled = false;
        assignSaveBtn.innerHTML = '<span class="material-symbols-outlined text-[16px]">save</span><span id="assignment-save-label">' + 
            (_assignPendingPayload ? 'Confirm & Add Student' : 'Save Assignments') + '</span>';
    }
    
    assignLoading.classList.remove('hidden');
    assignContent.classList.add('hidden');
    assignNotif.classList.add('hidden');
    assignModal.classList.remove('hidden');
    
    requestAnimationFrame(() => {
        assignModal.classList.remove('opacity-0');
        assignCard.classList.remove('scale-95');
    });

    // Fetch available lessons
    const token = document.querySelector('#studentFilterForm input[name="_token"]').value;
    
    // 🔥 FIX: For new students, use the available-lessons endpoint with a special flag
    let lessonsUrl;
    if (_assignPendingPayload) {
        // For new students, we need to fetch lessons WITHOUT a student ID
        // Use a special endpoint or pass a flag
        lessonsUrl = '/students/lessons-for-new-student';
    } else {
        lessonsUrl = '/students/' + studentId + '/available-lessons';
    }

    fetch(lessonsUrl, {
        method: 'GET',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => {
        if (!r.ok) throw new Error('HTTP ' + r.status);
        return r.json();
    })
    .then(res => {
        console.log('📦 Available lessons response:', res);
        assignLoading.classList.add('hidden');
        
        if (!res.success) {
            assignNotifShow(res.message || 'Failed to load lessons.', 'error');
            return;
        }

        _assignModules = res.modules || [];
        _assignSelectedIds = new Set();
        _assignOriginalIds = new Set();
        
        // For new students, NOTHING should be pre-selected
        // For existing students, pre-select already assigned lessons
if (!_assignPendingPayload) {
    _assignModules.forEach(module => {
        module.lessons.forEach(lesson => {
            if (lesson.is_assigned && lesson.lesson_id) {
                const id = String(lesson.lesson_id);
                if (id && id !== 'NaN' && id !== '0') {
                    _assignSelectedIds.add(id);
                    _assignOriginalIds.add(id);
                }
            }
        });
    });
}
        renderAssignmentList();
        updateAssignmentStats();
        assignContent.classList.remove('hidden');
    })
    .catch(err => {
        console.error('❌ Error loading lessons:', err);
        assignLoading.classList.add('hidden');
        assignNotifShow('Error loading lessons: ' + err.message, 'error');
    });
}
function closeAssignmentModal() {
    assignModal.classList.add('opacity-0');
    assignCard.classList.add('scale-95');
    setTimeout(() => {
        assignModal.classList.add('hidden');
        _assignStudentId = null;
        _assignModules = [];
        _assignSelectedIds = new Set();
        _assignOriginalIds = new Set();
        _assignPendingPayload = null;
    }, 300);
}

function renderAssignmentList() {
    if (_assignModules.length === 0) {
        assignList.innerHTML = `
            <div class="flex flex-col items-center justify-center py-12 text-center">
                <span class="material-symbols-outlined text-slate-300 text-[48px] mb-3">folder_off</span>
                <p class="text-[14px] font-bold text-slate-400">No lessons available</p>
                <p class="text-[12px] text-slate-400">There are no published lessons to assign yet.</p>
            </div>
        `;
        return;
    }

    let html = '';
    let lessonCounter = 0;

    _assignModules.forEach((module, idx) => {
        const moduleChecked = module.lessons.every(l => _assignSelectedIds.has(l.lesson_id));
        const someChecked = module.lessons.some(l => _assignSelectedIds.has(l.lesson_id));
        
        html += `
            <div class="border border-slate-200 rounded-2xl overflow-hidden bg-white shadow-sm hover:shadow transition-all">
                <div class="flex items-center justify-between px-5 py-3 bg-slate-50/80 border-b border-slate-200">
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-3 cursor-pointer select-none">
                            <input type="checkbox" class="module-checkbox w-4 h-4 rounded border-slate-300 text-[#0d326b] focus:ring-[#0d326b] focus:ring-offset-0 cursor-pointer" 
                                   data-module-id="${module.module_id}" 
                                   ${moduleChecked ? 'checked' : ''}
                                   ${someChecked && !moduleChecked ? 'indeterminate' : ''}>
                            <span class="text-[13px] font-bold text-[#0d326b]">${module.module_title}</span>
                            <span class="text-[10px] font-medium text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">
                                ${module.lessons.length} lessons
                            </span>
                        </label>
                    </div>
                    <span class="text-[10px] font-medium ${moduleChecked ? 'text-emerald-600' : (someChecked ? 'text-amber-600' : 'text-slate-400')}">
                        ${moduleChecked ? '✓ All Assigned' : (someChecked ? 'Partial' : 'None')}
                    </span>
                </div>
                <div class="px-5 py-3 space-y-1.5">
                   ${module.lessons.map(lesson => {
    lessonCounter++;
    const checked = _assignSelectedIds.has(lesson.lesson_id) ? 'checked' : '';
    const isExam = lesson.type === 'checkpoint_exam';
    const badge = isExam 
        ? '<span class="text-[9px] font-bold text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full ml-2">📝 Exam</span>'
        : (lesson.is_assigned ? '<span class="text-[9px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Assigned</span>' : '');
    return `
        <label class="flex items-center gap-3 py-1.5 px-2 rounded-lg hover:bg-slate-50 cursor-pointer transition-colors select-none group">
            <input type="checkbox" class="lesson-checkbox w-4 h-4 rounded border-slate-300 text-[#0d326b] focus:ring-[#0d326b] focus:ring-offset-0 cursor-pointer" 
                   data-lesson-id="${lesson.lesson_id}" 
                   ${checked}>
            <span class="text-[13px] font-medium text-slate-700 group-hover:text-[#0d326b] transition-colors flex-1">
                ${lesson.title}
                ${badge}
            </span>
        </label>
    `;
}).join('')}
                </div>
            </div>
        `;
    });

    assignList.innerHTML = html;

    // ── Event listeners ──────────────────────────────────────────────────────
    // Individual lesson checkboxes
    assignList.querySelectorAll('.lesson-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
    const lessonId = this.dataset.lessonId; // Keep as string, don't parseInt!
    if (this.checked) {
        _assignSelectedIds.add(lessonId);
    } else {
        _assignSelectedIds.delete(lessonId);
    }
            // Update module checkbox state
            const moduleContainer = this.closest('.border');
            if (moduleContainer) {
                const moduleCb = moduleContainer.querySelector('.module-checkbox');
                const lessonCbs = moduleContainer.querySelectorAll('.lesson-checkbox');
                const allChecked = Array.from(lessonCbs).every(c => c.checked);
                const someChecked = Array.from(lessonCbs).some(c => c.checked);
                moduleCb.checked = allChecked;
                moduleCb.indeterminate = !allChecked && someChecked;
            }
            updateAssignmentStats();
        });
    });

    // Module checkboxes (select/deselect all in module)
    assignList.querySelectorAll('.module-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            const moduleContainer = this.closest('.border');
            if (!moduleContainer) return;
            const lessonCbs = moduleContainer.querySelectorAll('.lesson-checkbox');
            lessonCbs.forEach(lcb => {
                lcb.checked = this.checked;
                const lessonId = parseInt(lcb.dataset.lessonId);
                if (this.checked) {
                    _assignSelectedIds.add(lessonId);
                } else {
                    _assignSelectedIds.delete(lessonId);
                }
            });
            this.indeterminate = false;
            updateAssignmentStats();
        });
    });
}

function updateAssignmentStats() {
    const total = _assignModules.reduce((sum, m) => sum + m.lessons.length, 0);
    const selected = _assignSelectedIds.size;
    assignTotal.textContent = total;
    assignSelected.textContent = selected;
}

function assignNotifShow(msg, type) {
    assignNotif.classList.remove('hidden', 'bg-emerald-50','border-emerald-200','text-emerald-800',
        'bg-red-50','border-red-200','text-red-800','bg-amber-50','border-amber-200','text-amber-800');
    const map = { 
        success: ['bg-emerald-50','border-emerald-200','text-emerald-800','check_circle'],
        error:   ['bg-red-50','border-red-200','text-red-800','error'],
        warning: ['bg-amber-50','border-amber-200','text-amber-800','warning'] 
    };
    const [bg,border,txt,icon] = map[type] || map.error;
    assignNotif.classList.add(bg, border, txt);
    assignNotif.innerHTML = `<span class="material-symbols-outlined text-[18px] shrink-0">${icon}</span><span>${msg}</span>`;
    setTimeout(() => assignNotif.classList.add('hidden'), 4000);
}

// ── Assignment Modal Event Listeners ──────────────────────────────────────
document.getElementById('assignment-close').addEventListener('click', closeAssignmentModal);
document.getElementById('assignment-cancel').addEventListener('click', closeAssignmentModal);
assignModal.addEventListener('click', e => { if (e.target === assignModal) closeAssignmentModal(); });

// Select All / Deselect All
document.getElementById('assignment-select-all').addEventListener('click', function() {
    const checkboxes = assignList.querySelectorAll('.lesson-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = true;
        _assignSelectedIds.add(parseInt(cb.dataset.lessonId));
    });
    // Update module checkboxes
    assignList.querySelectorAll('.module-checkbox').forEach(cb => {
        cb.checked = true;
        cb.indeterminate = false;
    });
    updateAssignmentStats();
});

document.getElementById('assignment-deselect-all').addEventListener('click', function() {
    const checkboxes = assignList.querySelectorAll('.lesson-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = false;
        _assignSelectedIds.delete(parseInt(cb.dataset.lessonId));
    });
    assignList.querySelectorAll('.module-checkbox').forEach(cb => {
        cb.checked = false;
        cb.indeterminate = false;
    });
    updateAssignmentStats();
});

// Save Assignments
// Save Assignments
document.getElementById('assignment-save').addEventListener('click', async function() {
    if (!_assignStudentId && !_assignPendingPayload) return;
    
    // 🔥 FIX: Get ONLY the selected lesson IDs and convert to strings
    const lessonIds = Array.from(_assignSelectedIds).map(id => String(id));
    
    console.log('✅ Sending ONLY these lesson IDs (as strings):', lessonIds);
    console.log('✅ Count:', lessonIds.length);
    
    const token = document.querySelector('#studentFilterForm input[name="_token"]').value;
    const saveBtn = this;
    const origHtml = saveBtn.innerHTML;
    
    saveBtn.innerHTML = '<span class="material-symbols-outlined text-[16px] animate-spin">progress_activity</span>Saving...';
    saveBtn.disabled = true;

    try {
        let res;
        if (_assignPendingPayload) {
            const payload = {
                ..._assignPendingPayload,
                lesson_ids: lessonIds
            };
            res = await axios.post("{{ route('students.store') }}", payload, {
                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' }
            });
        } else {
            res = await axios.post('/students/' + _assignStudentId + '/assign-lessons', {
                lesson_ids: lessonIds
            }, {
                headers: {
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                }
            });
        }

        if (res.data.success) {
            assignNotifShow(res.data.message, 'success');
            setTimeout(() => {
                closeAssignmentModal();
                applyServerFilters();
                saveBtn.innerHTML = origHtml;
                saveBtn.disabled = false;
            }, 1200);
        } else {
            assignNotifShow(res.data.message || 'Failed to save.', 'error');
            saveBtn.innerHTML = origHtml;
            saveBtn.disabled = false;
        }
    } catch (err) {
        let msg = 'Failed to save.';
        if (err.response?.data?.errors) {
            msg = Object.values(err.response.data.errors).flat().join(' ');
        } else if (err.response?.data?.message) {
            msg = err.response.data.message;
        }
        assignNotifShow(msg, 'error');
        saveBtn.innerHTML = origHtml;
        saveBtn.disabled = false;
    }
});

// ── Manage Lessons button in Student Details ─────────────────────────────
// Add event listener for the new button
document.addEventListener('click', function(e) {
    const assignBtn = e.target.closest('#sdc-assign-btn');
    if (assignBtn && _sdCurrent) {
        const studentId = _sdCurrent.student_id;
        const studentName = _sdCurrent.full_name;
        closeStudentDetails();
        setTimeout(() => {
            openAssignmentModal(studentId, studentName);
        }, 400);
    }
});




</script>
@endsection