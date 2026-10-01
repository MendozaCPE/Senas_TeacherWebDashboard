@extends('layouts.teacher-leader')
@section('title', 'Default Lessons')
@section('content')

<style>
    /* ── Module Card — mirrors admin lessons.blade.php ───────────────── */
    .module-card {
        border-radius: 24px;
        border: 1.5px solid #e5eaf2;
        background: #ffffff;
        box-shadow: 0 4px 20px rgba(13, 50, 107, 0.03);
        overflow: hidden;
        transition: transform .25s cubic-bezier(.4,0,.2,1), box-shadow .25s cubic-bezier(.4,0,.2,1);
    }
    .module-card:hover { box-shadow: 0 16px 40px rgba(13, 50, 107, 0.08); transform: translateY(-3px); }

    /* Amber trapezoid tab for "DEFAULT MODULE" */
    .module-tab {
        display: inline-block;
        padding: 6px 22px;
        font-size: 10px;
        font-weight: 800;
        color: #0d326b;
        text-transform: uppercase;
        letter-spacing: 0.14em;
        clip-path: polygon(0 0, 100% 0, 88% 100%, 0% 100%);
        background: linear-gradient(90deg, #fde047, #facc15) !important;
        min-width: 90px;
    }

    .module-header {
        padding: 20px 24px 18px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }
    .module-title-wrap { display: flex; align-items: center; gap: 16px; }
    .module-icon {
        width: 48px; height: 48px; border-radius: 16px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 20px;
        box-shadow: 0 4px 12px rgba(13, 50, 107, 0.06);
    }
    .module-title-text { font-size: 18px; font-weight: 800; color: #0d326b; letter-spacing: -0.01em; }
    .module-meta { font-size: 12.5px; color: #64748b; font-weight: 500; margin-top: 2px; }

    /* Stats bar */
    .module-stats {
        display: flex; align-items: center; gap: 14px;
        font-size: 12px; background: #fafcff;
        padding: 6px 14px; border-radius: 14px; border: 1px solid #e5eaf2;
    }
    .module-stat-item {
        display: flex; align-items: center; gap: 5px;
        color: #475569; font-weight: 700;
    }
    .module-stat-item .material-symbols-outlined { font-size: 17px; color: #1a6fd4; }
    .module-progress { width: 90px; height: 6px; border-radius: 9999px; background: #e2e8f0; overflow: hidden; }
    .module-progress-fill { height: 100%; border-radius: 9999px; background: linear-gradient(90deg, #1a6fd4, #3b82f6); transition: width .6s ease; }

    /* Table */
    .lesson-table-wrap { padding: 0 24px 20px; overflow-x: auto; }
    .lesson-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .lesson-table thead th {
        padding: 14px 16px; text-align: left; font-size: 10.5px; font-weight: 800;
        color: #64748b; text-transform: uppercase; letter-spacing: 0.07em;
        border-bottom: 1.5px solid #f1f5f9; background: #f8fafc;
    }
    .lesson-table tbody td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .lesson-table tbody tr { transition: all .2s ease; }
    .lesson-table tbody tr:hover { background: #f0f6ff; }
    .lesson-title-cell { font-weight: 700; color: #0d326b; font-size: 14px; }

    .badge-difficulty {
        display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px;
        border-radius: 9999px; font-size: 10px; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.05em;
    }
    .badge-difficulty.beginner { background: #eff6ff; color: #1e4b8f; border: 1px solid #bfdbfe; }
    .badge-difficulty.intermediate { background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }
    .badge-difficulty.advanced { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

    .badge-status {
        display: inline-flex; align-items: center; gap: 4px; padding: 4px 12px;
        border-radius: 9999px; font-size: 10px; font-weight: 800;
        text-transform: uppercase; letter-spacing: 0.05em;
    }
    .badge-status.published { background: #0d326b; color: #ffffff; box-shadow: 0 2px 6px rgba(13,50,107,0.18); }
    .badge-status.draft { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .badge-status.archived { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

    .action-link {
        font-size: 12px; font-weight: 700; color: #475569; transition: all .2s;
        text-decoration: none; padding: 5px 14px; border-radius: 9px;
        background: #f8fafc; border: 1px solid #e2e8f0; white-space: nowrap;
        display: inline-flex; align-items: center; gap: 4px; cursor: pointer;
    }
    .action-link:hover { color: #0d326b; background: #e0e8ff; border-color: #bfdbfe; transform: translateY(-1px); }

    /* Pagination */
    .table-pagination {
        padding: 16px 24px 20px; border-top: 1px solid #f1f5f9;
        display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;
    }
    .pagination-info { font-size: 12.5px; color: #64748b; font-weight: 600; }
    .pagination-buttons { display: flex; gap: 6px; }
    .pagination-btn {
        width: 36px; height: 36px; border-radius: 10px; border: 1px solid #e2e8f0;
        background: #fff; color: #475569; font-weight: 700; font-size: 13px;
        cursor: pointer; transition: all .15s; display: flex; align-items: center; justify-content: center;
    }
    .pagination-btn:hover { background: #f1f5f9; border-color: #cbd5e1; color: #0d326b; }
    .pagination-btn.active { background: linear-gradient(135deg, #0d326b, #1a6fd4); color: #fff; border: none; box-shadow: 0 3px 10px rgba(13,50,107,0.2); }
    .pagination-btn:disabled { opacity: .4; cursor: not-allowed; }

    /* Table scroll */
    .table-scroll { max-height: 400px; overflow-y: auto; }
    .table-scroll::-webkit-scrollbar { width: 5px; }
    .table-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

    /* Insights sticky sidebar */
    .insights-sticky { position: sticky; top: 24px; align-self: flex-start; }

    /* Empty state */
    .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 24px; text-align: center; }
</style>

{{-- ── SKELETON ─────────────────────────────────────────────────────────── --}}
<div id="page-skeleton" class="pt-4" aria-hidden="true">
    <div class="skeleton rounded-[24px] h-24 w-full mb-6"></div>
    <div class="flex flex-col lg:flex-row gap-6">
        <div class="flex-1 min-w-0 flex flex-col gap-5">
            @for($i=0;$i<3;$i++)
            <div class="bg-white rounded-[24px] border border-slate-200 shadow-sm overflow-hidden p-6">
                <div class="skeleton h-6 rounded w-48 mb-4"></div>
                <div class="skeleton h-32 rounded w-full"></div>
            </div>
            @endfor
        </div>
        <div class="w-full lg:w-[300px] flex-shrink-0 flex flex-col gap-4">
            <div class="skeleton rounded-[24px] h-96 w-full"></div>
        </div>
    </div>
</div>

<div class="skeleton-hide pt-2">

    {{-- ── Header Banner ───────────────────────────────────────────────── --}}
    <div class="rounded-[24px] p-6 mb-6 flex items-center justify-between flex-wrap gap-4 shadow-sm"
         style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%);">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-white/15 flex items-center justify-center flex-shrink-0 border border-white/20">
                <span class="material-symbols-outlined text-white text-[26px]">local_library</span>
            </div>
            <div>
                <h2 class="text-[20px] font-black text-white leading-tight">Default Curriculum</h2>
                <p class="text-[13px] text-white/80 font-medium mt-0.5">
                    Standardized Filipino Sign Language (FSL) curriculum and lesson templates published across the school system.
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="px-4 py-2 rounded-xl bg-white/15 text-white text-[12px] font-bold flex items-center gap-2 border border-white/20 shadow-sm">
                <span class="material-symbols-outlined text-[16px]">visibility</span>
                View-Only Access
            </span>
        </div>
    </div>

    {{-- ── Two-column layout ──────────────────────────────────────────── --}}
    <div class="flex flex-col lg:flex-row gap-6">

        <!-- Left: Modules + Lessons -->
        <div class="flex-1 min-w-0 flex flex-col space-y-6">

            @php
                $moduleColors = ['#0d326b','#1e4b8f','#1a6fd4','#3b82f6','#2563EB','#059669','#D97706'];
                $moduleIconNames = ['menu_book','book','edit_note','description','auto_stories','school','library_books'];
                $pageSize = 5;
            @endphp

            {{-- Empty state when no modules --}}
            @if($modules->isEmpty())
            <div class="module-card">
                <div class="empty-state py-16">
                    <div class="w-20 h-20 rounded-3xl bg-[#0d326b]/08 flex items-center justify-center mb-5">
                        <span class="material-symbols-outlined text-[#0d326b] text-[40px]">menu_book</span>
                    </div>
                    <h3 class="text-[20px] font-bold text-[#0d326b] mb-2">No default modules yet</h3>
                    <p class="text-slate-500 text-sm max-w-md">Default curriculum modules published by the Super Admin will appear here.</p>
                </div>
            </div>
            @endif

            {{-- Module cards loop — use @foreach (not @forelse) to avoid nested-directive conflict --}}
            @foreach($modules as $modIndex => $module)
            @php
                $modColor    = $moduleColors[$modIndex % count($moduleColors)];
                $modIconName = $moduleIconNames[$modIndex % count($moduleIconNames)];
                $padNum      = str_pad($modIndex + 1, 2, '0', STR_PAD_LEFT);
                $lessonCount = $module->lessons->count();
                $published   = $module->lessons->where('status','published')->count();
                $progress    = $lessonCount > 0 ? round($published / $lessonCount * 100) : 0;
                $totalPages  = max(1, (int) ceil($lessonCount / $pageSize));
                $allLessons  = $module->lessons->values();

                // Build pagination buttons as a PHP string to avoid nested @foreach inside @if
                $pageBtns = '';
                if ($totalPages > 1) {
                    for ($p = 1; $p <= $totalPages; $p++) {
                        $activeClass = $p === 1 ? ' active' : '';
                        $pageBtns .= '<button class="pagination-btn' . $activeClass . '"'
                            . ' id="page-btn-' . $module->module_id . '-' . $p . '"'
                            . ' onclick="changePage(\'' . $module->module_id . '\', ' . $p . ')">'
                            . $p . '</button>';
                    }
                }

                // Build module meta string
                $moduleMeta = $lessonCount . ' lesson' . ($lessonCount !== 1 ? 's' : '');
                if ($module->description) { $moduleMeta .= ' · ' . e($module->description); }
                if ($module->mastery_level) { $moduleMeta .= ' · ' . ucfirst($module->mastery_level) . ' Tier'; }
            @endphp

            <div class="module-card" id="module-{{ $module->module_id }}">
                <div class="module-tab">DEFAULT MODULE {{ $padNum }}</div>

                <div class="module-header">
                    <div class="module-title-wrap">
                        <div class="module-icon" style="background:{{ $modColor }}15; color:{{ $modColor }};">
                            <span class="material-symbols-outlined text-[22px]">{{ $modIconName }}</span>
                        </div>
                        <div>
                            <div class="module-title-text">{{ $module->title }}</div>
                            <div class="module-meta">{!! $moduleMeta !!}</div>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 flex-wrap">
                        <div class="module-stats">
                            <span class="module-stat-item">
                                <span class="material-symbols-outlined">check_circle</span>
                                {{ $published }} published
                            </span>
                            <div class="module-progress">
                                <div class="module-progress-fill" style="width:{{ $progress }}%"></div>
                            </div>
                            <span class="module-stat-item" style="font-weight:700;color:#0d326b;min-width:36px;">
                                {{ $progress }}%
                            </span>
                        </div>
                    </div>
                </div>

                <div class="lesson-table-wrap">

                    {{-- Empty module state --}}
                    @if($lessonCount === 0)
                    <div class="empty-state py-8">
                        <div class="w-14 h-14 rounded-2xl bg-[#f1f5f9] flex items-center justify-center mb-3">
                            <span class="material-symbols-outlined text-slate-400 text-[28px]">menu_book</span>
                        </div>
                        <p class="text-[14px] font-semibold text-slate-400">No lessons in this module yet</p>
                    </div>
                    @endif

                    {{-- Lesson table (only shown when lessons exist) --}}
                    @if($lessonCount > 0)
                    <div class="table-scroll">
                        <table class="lesson-table">
                            <thead>
                                <tr>
                                    <th>Lesson Title</th>
                                    <th style="width:130px;">Difficulty</th>
                                    <th style="width:120px;">Status</th>
                                    <th style="width:100px;text-align:right;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="lesson-tbody-{{ $module->module_id }}">
                                @foreach($allLessons as $lessonIndex => $lesson)
                                @php $lessonPage = (int) floor($lessonIndex / $pageSize) + 1; @endphp
                                <tr class="lesson-row cursor-pointer"
                                    data-lesson-id="{{ $lesson->lesson_id }}"
                                    data-page="{{ $lessonPage }}"
                                    onclick="openPreviewModal('{{ route('teacher-leader.lessons.preview-page', $lesson->lesson_id) }}')"
                                    style="cursor:pointer;">
                                    <td class="lesson-title-cell">
                                        <div class="flex items-center gap-2.5">
                                            <span class="w-2 h-2 rounded-full bg-[#1a6fd4]"></span>
                                            <span>{{ $lesson->title }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-difficulty {{ strtolower($lesson->difficulty ?? 'beginner') }}">
                                            {{ ucfirst($lesson->difficulty ?? 'beginner') }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge-status {{ strtolower($lesson->status ?? 'published') }}">
                                            {{ ucfirst($lesson->status ?? 'published') }}
                                        </span>
                                    </td>
                                    <td style="text-align:right;" onclick="event.stopPropagation();">
                                        <button onclick="event.stopPropagation(); openPreviewModal('{{ route('teacher-leader.lessons.preview-page', $lesson->lesson_id) }}')"
                                                class="action-link"
                                                title="View Lesson">
                                            <span class="material-symbols-outlined text-[15px] text-[#1a6fd4]">visibility</span>
                                            View
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination (only shown when more than one page) --}}
                    @if($totalPages > 1)
                    <div class="table-pagination"
                         id="pagination-{{ $module->module_id }}"
                         data-total-pages="{{ $totalPages }}"
                         data-total-count="{{ $lessonCount }}"
                         data-page-size="{{ $pageSize }}">
                        <span class="pagination-info" id="pagination-info-{{ $module->module_id }}">
                            Showing 1&ndash;{{ min($pageSize, $lessonCount) }} of {{ $lessonCount }} lessons
                        </span>
                        <div class="pagination-buttons">
                            <button class="pagination-btn" id="prev-btn-{{ $module->module_id }}"
                                    onclick="changePage('{{ $module->module_id }}', currentPageOf('{{ $module->module_id }}') - 1)">
                                <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                            </button>
                            {!! $pageBtns !!}
                            <button class="pagination-btn" id="next-btn-{{ $module->module_id }}"
                                    onclick="changePage('{{ $module->module_id }}', currentPageOf('{{ $module->module_id }}') + 1)">
                                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                            </button>
                        </div>
                    </div>
                    @endif
                    @endif

                </div>

                {{-- Checkpoint Exams (Read-only for Teacher Leader) --}}
                @if($module->checkpointExams && $module->checkpointExams->isNotEmpty())
                <div class="px-6 pb-4 pt-3 border-t border-amber-100 bg-amber-50/30">
                    <div class="text-[11px] font-bold text-amber-900 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-amber-600">emoji_events</span>
                        Checkpoint Exams
                        <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 ml-1">View Only</span>
                    </div>
                    <div class="space-y-2">
                        @foreach($module->checkpointExams as $exam)
                        <div class="flex items-center justify-between p-3 rounded-xl bg-white border border-amber-100 shadow-sm flex-wrap gap-2">
                            <div class="flex items-center gap-3">
                                <span class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-xs">🏆</span>
                                <div>
                                    <div class="text-xs font-bold text-[#0d326b]">{{ $exam->title }}</div>
                                    <div class="text-[11px] text-slate-400 font-medium">
                                        {{ $exam->total_points }} pts · {{ $exam->questions->count() }} question(s)
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('teacher-leader.checkpoint-exam.show', $exam->hash_id) }}"
                               class="action-link"
                               style="color:#0d326b;background:#ffffff;border-color:#fde68a;"
                               title="View Exam">
                                <span class="material-symbols-outlined text-[15px] text-amber-600">visibility</span>
                                View Exam
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endforeach

        </div><!-- /Left column -->

        <!-- Right: Insights panel (sticky) -->
        <div class="w-full lg:w-[300px] flex-shrink-0 insights-sticky">
            <div class="bg-white rounded-[24px] p-6 shadow-sm border border-slate-100">
                <div class="flex items-center gap-2.5 mb-5 pb-4 border-b border-slate-100">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#0d326b] flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-[20px]">auto_awesome</span>
                    </div>
                    <div>
                        <span class="text-[15px] font-extrabold text-[#0d326b] block">Curriculum Insights</span>
                        <span class="text-[11px] text-slate-400 font-medium">Default Lessons Overview</span>
                    </div>
                </div>

                @php
                    $publishedCount    = $modules->sum(fn($m) => $m->lessons->where('status','published')->count());
                    $beginnerCount     = $modules->sum(fn($m) => $m->lessons->where('difficulty','beginner')->count());
                    $intermediateCount = $modules->sum(fn($m) => $m->lessons->where('difficulty','intermediate')->count());
                    $advancedCount     = $modules->sum(fn($m) => $m->lessons->where('difficulty','advanced')->count());
                @endphp

                <div class="space-y-3 mb-5">
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3">
                        <span class="text-[12px] font-bold text-slate-600 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-[#1a6fd4]">folder</span>
                            Total Modules
                        </span>
                        <span class="text-[18px] font-black text-[#0d326b]">{{ $totalModules }}</span>
                    </div>
                    <div class="flex items-center justify-between bg-slate-50 border border-slate-100 rounded-2xl px-4 py-3">
                        <span class="text-[12px] font-bold text-slate-600 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-[#1a6fd4]">menu_book</span>
                            Total Lessons
                        </span>
                        <span class="text-[18px] font-black text-[#0d326b]">{{ $totalLessons }}</span>
                    </div>
                    <div class="flex items-center justify-between rounded-2xl px-4 py-3 text-white shadow-sm"
                         style="background: linear-gradient(135deg, #0d326b, #1a6fd4);">
                        <span class="text-[12px] font-bold text-white/90 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-white">check_circle</span>
                            Published
                        </span>
                        <span class="text-[18px] font-black text-white">{{ $publishedCount }}</span>
                    </div>

                    @php
                        $totalExams = $modules->sum(fn($m) => $m->checkpointExams ? $m->checkpointExams->count() : 0);
                    @endphp
                    @if($totalExams > 0)
                    <div class="flex items-center justify-between bg-amber-50/70 border border-amber-200/80 rounded-2xl px-4 py-3">
                        <span class="text-[12px] font-bold text-amber-900 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-amber-600">emoji_events</span>
                            Checkpoint Exams
                        </span>
                        <span class="text-[18px] font-black text-amber-900">{{ $totalExams }}</span>
                    </div>
                    @endif

                    <div class="pt-2">
                        <p class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400 mb-2">By Difficulty Tier</p>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-blue-50/70 border border-blue-100 text-[12px]">
                                <span class="font-bold text-[#1e4b8f] flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-[#1e4b8f]"></span>
                                    Beginner
                                </span>
                                <span class="font-extrabold text-[#0d326b]">{{ $beginnerCount }}</span>
                            </div>
                            <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-indigo-50/70 border border-indigo-100 text-[12px]">
                                <span class="font-bold text-[#3730a3] flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-[#3730a3]"></span>
                                    Intermediate
                                </span>
                                <span class="font-extrabold text-[#0d326b]">{{ $intermediateCount }}</span>
                            </div>
                            <div class="flex items-center justify-between px-3.5 py-2.5 rounded-xl bg-emerald-50/70 border border-emerald-100 text-[12px]">
                                <span class="font-bold text-[#166534] flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-[#166534]"></span>
                                    Advanced
                                </span>
                                <span class="font-extrabold text-[#0d326b]">{{ $advancedCount }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Reference Callout --}}
                <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200/60 text-[11.5px] text-amber-900 leading-relaxed font-medium">
                    <div class="flex items-center gap-1.5 font-bold mb-1 text-amber-800">
                        <span class="material-symbols-outlined text-[15px]">info</span>
                        Curriculum Reference
                    </div>
                    These standardized lessons are automatically provided to school teachers as instructional blueprints.
                </div>
            </div>
        </div><!-- /Right column -->

    </div>

</div>

{{-- ══════════════════════════════════════════════════════════════════════
     LESSON PREVIEW MODAL — same fullscreen overlay as teacher lessons page
     ══════════════════════════════════════════════════════════════════════ --}}
<div id="lessonPreviewModal"
     style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; padding:24px 16px;"
     onclick="if(event.target===this) closeLessonPreviewModal()">
    {{-- Transparent backdrop --}}
    <div style="position:fixed; inset:0; background:rgba(10,20,50,0.55); backdrop-filter:blur(4px);"></div>

    {{-- Back button (fixed top-left) --}}
    <button onclick="closeLessonPreviewModal()"
            id="lessonPreviewBackBtn"
            style="position:fixed; top:18px; left:20px; z-index:10001; height:44px; padding:0 20px;
                   background:rgba(255,255,255,0.95); border:1.5px solid rgba(13,50,107,0.12); border-radius:24px; font-size:13.5px; font-weight:700;
                   color:#0d326b; cursor:pointer; display:flex; align-items:center; gap:8px;
                   box-shadow:0 4px 20px rgba(0,0,0,0.18); backdrop-filter:blur(8px); transition:all .2s ease;"
            onmouseover="this.style.transform='translateY(-2px)'; this.style.background='#fff'; this.style.boxShadow='0 6px 24px rgba(0,0,0,0.25)'"
            onmouseout="this.style.transform=''; this.style.background='rgba(255,255,255,0.95)'; this.style.boxShadow='0 4px 20px rgba(0,0,0,0.18)'"
            title="Back to Lessons">
        <span class="material-symbols-outlined" style="font-size:20px; font-weight:700;">arrow_back</span>
        <span>Back</span>
    </button>

    {{-- X close button (fixed top-right) --}}
    <button onclick="closeLessonPreviewModal()"
            style="position:fixed; top:18px; right:20px; z-index:10001; width:44px; height:44px;
                   background:rgba(255,255,255,0.95); border:none; border-radius:50%; font-size:22px;
                   cursor:pointer; display:flex; align-items:center; justify-content:center;
                   box-shadow:0 4px 20px rgba(0,0,0,0.2); transition:transform .2s, background .2s;"
            onmouseover="this.style.transform='scale(1.1)'; this.style.background='#fff'"
            onmouseout="this.style.transform=''; this.style.background='rgba(255,255,255,0.95)'"
            title="Close preview">
        ✕
    </button>

    {{-- Content container --}}
    <div id="lessonPreviewContent"
         style="position:relative; z-index:10000; max-width:900px; margin:0 auto; min-height:200px;">
        {{-- Loading shimmer skeleton --}}
        <div id="lessonPreviewLoading"
             style="display:flex; flex-direction:column; gap:16px; padding:24px; background:rgba(255,255,255,0.07); border-radius:20px;">
            <div style="height:36px; background:linear-gradient(90deg,rgba(255,255,255,0.08) 0px,rgba(255,255,255,0.18) 40%,rgba(255,255,255,0.1) 55%,rgba(255,255,255,0.08) 100%); background-size:600px 100%; animation:shimmer 1.6s infinite linear; border-radius:10px;"></div>
            <div style="height:180px; background:linear-gradient(90deg,rgba(255,255,255,0.08) 0px,rgba(255,255,255,0.18) 40%,rgba(255,255,255,0.1) 55%,rgba(255,255,255,0.08) 100%); background-size:600px 100%; animation:shimmer 1.6s infinite linear; border-radius:14px;"></div>
            <div style="height:16px; width:70%; background:linear-gradient(90deg,rgba(255,255,255,0.08) 0px,rgba(255,255,255,0.18) 40%,rgba(255,255,255,0.1) 55%,rgba(255,255,255,0.08) 100%); background-size:600px 100%; animation:shimmer 1.6s 0.1s infinite linear; border-radius:8px;"></div>
            <div style="height:12px; width:90%; background:linear-gradient(90deg,rgba(255,255,255,0.08) 0px,rgba(255,255,255,0.18) 40%,rgba(255,255,255,0.1) 55%,rgba(255,255,255,0.08) 100%); background-size:600px 100%; animation:shimmer 1.6s 0.2s infinite linear; border-radius:8px;"></div>
            <div style="height:12px; width:55%; background:linear-gradient(90deg,rgba(255,255,255,0.08) 0px,rgba(255,255,255,0.18) 40%,rgba(255,255,255,0.1) 55%,rgba(255,255,255,0.08) 100%); background-size:600px 100%; animation:shimmer 1.6s 0.3s infinite linear; border-radius:8px;"></div>
            <div style="display:flex; gap:10px; margin-top:4px;">
                <div style="height:40px; flex:1; background:linear-gradient(90deg,rgba(255,255,255,0.08) 0px,rgba(255,255,255,0.18) 40%,rgba(255,255,255,0.1) 55%,rgba(255,255,255,0.08) 100%); background-size:600px 100%; animation:shimmer 1.6s 0.15s infinite linear; border-radius:10px;"></div>
                <div style="height:40px; flex:1; background:linear-gradient(90deg,rgba(255,255,255,0.08) 0px,rgba(255,255,255,0.18) 40%,rgba(255,255,255,0.1) 55%,rgba(255,255,255,0.08) 100%); background-size:600px 100%; animation:shimmer 1.6s 0.25s infinite linear; border-radius:10px;"></div>
            </div>
        </div>
        <div id="lessonPreviewBody" style="display:none;"></div>
    </div>
</div>

<style>
@keyframes shimmer {
    0%   { background-position: -600px 0; }
    100% { background-position: 600px 0; }
}
</style>

<script>
// ── Pagination Handler for Modules ─────────────────────────────────────────
const pageState = {};

function currentPageOf(moduleId) {
    return pageState[moduleId] || 1;
}

function changePage(moduleId, targetPage) {
    const pag = document.getElementById('pagination-' + moduleId);
    if (!pag) return;
    const totalPages = parseInt(pag.dataset.totalPages, 10);
    const totalCount = parseInt(pag.dataset.totalCount, 10);
    const pageSize   = parseInt(pag.dataset.pageSize, 10);

    if (targetPage < 1 || targetPage > totalPages) return;
    pageState[moduleId] = targetPage;

    const tbody = document.getElementById('lesson-tbody-' + moduleId);
    if (tbody) {
        tbody.querySelectorAll('.lesson-row').forEach(row => {
            row.style.display = (parseInt(row.dataset.page, 10) === targetPage) ? '' : 'none';
        });
    }

    const prevBtn = document.getElementById('prev-btn-' + moduleId);
    const nextBtn = document.getElementById('next-btn-' + moduleId);
    if (prevBtn) prevBtn.disabled = (targetPage === 1);
    if (nextBtn) nextBtn.disabled = (targetPage === totalPages);

    for (let p = 1; p <= totalPages; p++) {
        const btn = document.getElementById('page-btn-' + moduleId + '-' + p);
        if (btn) btn.classList.toggle('active', p === targetPage);
    }

    const info = document.getElementById('pagination-info-' + moduleId);
    if (info) {
        const start = (targetPage - 1) * pageSize + 1;
        const end   = Math.min(targetPage * pageSize, totalCount);
        info.textContent = `Showing ${start}–${end} of ${totalCount} lessons`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.table-pagination').forEach(pag => {
        const idMatch = pag.id.match(/^pagination-(.+)$/);
        if (idMatch) changePage(idMatch[1], 1);
    });
});

// ── Lesson Preview Overlay Modal — identical to teacher lessons page ─────────
function openLessonPreviewModal(url) {
    const modal   = document.getElementById('lessonPreviewModal');
    const loading = document.getElementById('lessonPreviewLoading');
    const body    = document.getElementById('lessonPreviewBody');

    body.innerHTML = '';
    body.style.display = 'none';
    loading.style.display = 'flex';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.text();
        })
        .then(html => {
            body.innerHTML = html;
            loading.style.display = 'none';
            body.style.display = 'block';

            // Re-create iframes so the browser actually loads their src
            body.querySelectorAll('iframe').forEach(oldIframe => {
                const newIframe = document.createElement('iframe');
                Array.from(oldIframe.attributes).forEach(attr => newIframe.setAttribute(attr.name, attr.value));
                oldIframe.parentNode.replaceChild(newIframe, oldIframe);
            });

            // Re-create video elements
            body.querySelectorAll('video').forEach(oldVideo => {
                const newVideo = document.createElement('video');
                Array.from(oldVideo.attributes).forEach(attr => newVideo.setAttribute(attr.name, attr.value));
                oldVideo.querySelectorAll('source').forEach(src => {
                    const newSrc = document.createElement('source');
                    Array.from(src.attributes).forEach(attr => newSrc.setAttribute(attr.name, attr.value));
                    newVideo.appendChild(newSrc);
                });
                oldVideo.parentNode.replaceChild(newVideo, oldVideo);
                newVideo.load();
            });

            // Re-run inline scripts
            body.querySelectorAll('script').forEach(oldScript => {
                const s = document.createElement('script');
                if (oldScript.src) { s.src = oldScript.src; }
                else { s.textContent = oldScript.textContent; }
                document.head.appendChild(s);
                oldScript.remove();
            });
        })
        .catch(err => {
            loading.innerHTML = '<span style="color:#fca5a5;">&#x26A0; Failed to load preview. Please try again.</span>';
            console.error('Preview load error:', err);
        });
}

function closeLessonPreviewModal() {
    const modal = document.getElementById('lessonPreviewModal');
    modal.style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('lessonPreviewBody').innerHTML = '';
}

function openPreviewModal(url) {
    openLessonPreviewModal(url);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeLessonPreviewModal();
});
</script>
@endsection

