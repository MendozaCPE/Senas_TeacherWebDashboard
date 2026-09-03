@extends('layouts.admin')
@section('title', 'Default Lessons')
@section('content')

<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
<style>
    /* ── Module Card — mirrors teacher lessons.blade.php ───────────────── */
    .module-card {
        border-radius: 24px;
        border: 1.5px solid #e5eaf2;
        background: #ffffff;
        box-shadow: 0 4px 20px rgba(13, 50, 107, 0.03);
        overflow: hidden;
        transition: transform .25s cubic-bezier(.4,0,.2,1), box-shadow .25s cubic-bezier(.4,0,.2,1);
    }
    .module-card:hover { box-shadow: 0 16px 40px rgba(13, 50, 107, 0.08); transform: translateY(-3px); }

    /* Amber trapezoid tab for "DEFAULT MODULE" (admin branding) */
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

    /* Stats bar (mirrors teacher view) */
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
        text-decoration: none; padding: 5px 12px; border-radius: 9px;
        background: #f8fafc; border: 1px solid #e2e8f0; white-space: nowrap;
        display: inline-flex; align-items: center; gap: 4px; cursor: pointer;
    }
    .action-link:hover { color: #0d326b; background: #e0e8ff; border-color: #bfdbfe; transform: translateY(-1px); }
    .action-link.danger { color: #dc2626; background: #fef2f2; border-color: #fecaca; }
    .action-link.danger:hover { color: #b91c1c; background: #fee2e2; border-color: #fca5a5; transform: translateY(-1px); }
    .action-link.primary { color: #fff; background: linear-gradient(135deg, #0d326b, #1a6fd4); border: none; box-shadow: 0 3px 10px rgba(13,50,107,0.2); }
    .action-link.primary:hover { opacity: 0.95; transform: translateY(-1px); box-shadow: 0 5px 14px rgba(13,50,107,0.3); }

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

    /* Drag handle */
    .drag-handle { cursor: grab; user-select: none; -webkit-user-select: none; touch-action: none; }
    .drag-handle:active { cursor: grabbing; }
    .sortable-ghost { opacity: 0.35 !important; background: #e0f2fe !important; outline: 2px dashed #1a6fd4 !important; }
    .sortable-chosen { background: #f0f7ff !important; box-shadow: 0 4px 16px rgba(13, 50, 107, 0.1); }
    .sortable-drag { opacity: 0.95 !important; background: #ffffff !important; box-shadow: 0 12px 30px rgba(13, 50, 107, 0.2); }

    /* Table scroll */
    .table-scroll { max-height: 360px; overflow-y: auto; }
    .table-scroll::-webkit-scrollbar { width: 5px; }
    .table-scroll::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 4px; }
    .table-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

    /* Insights sticky sidebar */
    .insights-sticky { position: sticky; top: 24px; align-self: flex-start; }

    /* Empty state */
    .empty-state { display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 60px 24px; text-align: center; }
</style>

@if(session('success'))
<div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800 flex items-center gap-3 shadow-sm">
    <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>
    {{ session('success') }}
</div>
@endif

{{-- ── Header banner ─────────────────────────────────────────────────── --}}
<div class="rounded-[24px] p-6 mb-6 flex items-center justify-between flex-wrap gap-4"
     style="background:linear-gradient(135deg,#0d326b 0%,#1e4b8f 50%,#1a6fd4 100%);">
    <div class="flex items-center gap-4">
        <div class="w-12 h-12 rounded-2xl bg-white/15 flex items-center justify-center flex-shrink-0">
            <span class="material-symbols-outlined text-white text-[26px]">lock</span>
        </div>
        <div>
            <h2 class="text-[20px] font-black text-white leading-tight">Default Curriculum</h2>
            <p class="text-[13px] text-white/75 font-medium mt-0.5">
                Every teacher gets their own independent copy of these lessons when they sign up.
                Editing here never touches a teacher's existing copy — use "Push to All Teachers" to sync a fix out.
            </p>
        </div>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
        <form action="{{ route('admin.lesson-templates.push-all') }}" method="POST"
              onsubmit="return confirm('⚠️ This will overwrite EVERY teacher\'s copy of every default lesson with the current template content. Teachers who customized their lessons will lose their changes. Continue?');">
            @csrf
            <button type="submit" class="action-link" style="background:#fff;border-color:#fff;">
                <span class="material-symbols-outlined text-[16px]">sync</span>
                Push All to Teachers
            </button>
        </form>
        <button onclick="openPushModal()" class="action-link" style="background:#fff;border-color:#fff;">
            <span class="material-symbols-outlined text-[16px]">sync</span>
            Push Selected Teachers
        </button>
        <a href="{{ route('admin.lesson-templates.create') }}" class="action-link primary">
            <span class="material-symbols-outlined text-[16px]">add</span>
            New Default Lesson
        </a>
    </div>
</div>

{{-- ── Two-column layout: modules + insights ──────────────────────────── --}}
<div class="flex flex-col lg:flex-row gap-6">

    <!-- Left: Modules + Lessons -->
    <div class="flex-1 min-w-0 flex flex-col space-y-6">

        @php
            $moduleColors = ['#0d326b','#1e4b8f','#1a6fd4','#3b82f6','#2563EB','#059669','#D97706'];
            $moduleIcons  = ['📚','📖','✏️','📝','📘','📗','📕'];
            $pageSize     = 5;
        @endphp

        @forelse($modules as $modIndex => $module)
        @php
            $modColor    = $moduleColors[$modIndex % count($moduleColors)];
            $modIcon     = $moduleIcons[$modIndex % count($moduleIcons)];
            $padNum      = str_pad($modIndex + 1, 2, '0', STR_PAD_LEFT);
            $lessonCount = $module->lessons->count();
            $published   = $module->lessons->where('status','published')->count();
            $progress    = $lessonCount > 0 ? round($published / $lessonCount * 100) : 0;
            $totalPages  = max(1, (int) ceil($lessonCount / $pageSize));
            $allLessons  = $module->lessons->values();
        @endphp

        <div class="module-card" id="module-{{ $module->module_id }}">
            <div class="module-tab">DEFAULT MODULE {{ $padNum }}</div>

            <div class="module-header">
                <div class="module-title-wrap">
                    <div class="module-icon" style="background:{{ $modColor }}15; color:{{ $modColor }};">{{ $modIcon }}</div>
                    <div>
                        <div class="module-title-text">{{ $module->title }}</div>
                        <div class="module-meta">
                            {{ $lessonCount }} lesson{{ $lessonCount !== 1 ? 's' : '' }}
                            @if($module->description)<span class="mx-1.5">·</span> {{ $module->description }}@endif
                            <span class="mx-1.5">·</span>
                            {{ $module->teacherCopyCount }} teacher{{ $module->teacherCopyCount !== 1 ? 's have' : ' has' }} a copy
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-4 flex-wrap">
                    {{-- Module stats --}}
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

                    <div class="flex items-center gap-2">
                        {{-- Push module button --}}
                        <form action="{{ route('admin.lesson-templates.push', $module->module_id) }}" method="POST"
                              onsubmit="return confirm('Overwrite every teacher\'s copy of \'{{ addslashes($module->title) }}\' with this content?');"
                              class="inline">
                            @csrf
                            <button type="submit"
                               class="w-9 h-9 rounded-lg border-2 border-slate-200 hover:border-[#1a6fd4] hover:bg-blue-50 transition-all flex items-center justify-center text-slate-400 hover:text-[#1a6fd4] flex-shrink-0"
                               title="Push this module to all teachers">
                                <span class="material-symbols-outlined text-[18px]">sync</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="lesson-table-wrap">
                @if($lessonCount === 0)
                    <div class="empty-state py-8">
                        <div class="w-14 h-14 rounded-2xl bg-[#f1f5f9] flex items-center justify-center mb-3">
                            <span class="material-symbols-outlined text-slate-400 text-[28px]">menu_book</span>
                        </div>
                        <p class="text-[14px] font-semibold text-slate-400">No lessons in this module yet</p>
                        <a href="{{ route('admin.lesson-templates.create', ['module_id' => $module->module_id]) }}"
                           class="mt-3 text-[13px] font-bold text-[#0d326b] hover:underline flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            Add the first lesson
                        </a>
                    </div>
                @else
                    <div class="table-scroll">
                        <table class="lesson-table">
                            <thead>
                                <tr>
                                    <th style="width:30px;"></th>
                                    <th>Lesson Title</th>
                                    <th style="width:110px;">Difficulty</th>
                                    <th style="width:110px;">Status</th>
                                    <th style="width:180px;text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="lesson-tbody-{{ $module->module_id }}">
                                @foreach($allLessons as $lessonIndex => $lesson)
                                @php $lessonPage = intdiv($lessonIndex, $pageSize) + 1; @endphp
                                <tr class="lesson-row" data-lesson-id="{{ $lesson->lesson_id }}" data-page="{{ $lessonPage }}"
                                    onclick="openPreviewModal('{{ route('admin.lesson-templates.preview-modal', $lesson->hash_id) }}')"
                                    style="cursor:pointer;">
                                    <td class="drag-handle text-slate-300 hover:text-[#0d326b] transition-colors"
                                        onclick="event.stopPropagation();" title="Drag to reorder">
                                        <span class="material-symbols-outlined text-[18px]">drag_indicator</span>
                                    </td>
                                    <td class="lesson-title-cell">{{ $lesson->title }}</td>
                                    <td>
                                        <span class="badge-difficulty {{ $lesson->difficulty }}">{{ $lesson->difficulty }}</span>
                                    </td>
                                    <td>
                                        @if($lesson->trashed())
                                            <span class="badge-status" style="background:#e2e8f0; color:#64748b;">Archived</span>
                                        @else
                                            <span class="badge-status {{ $lesson->status }}">{{ $lesson->status }}</span>
                                        @endif
                                    </td>
                                    <td style="text-align:right;" onclick="event.stopPropagation();">
                                        <div class="flex items-center justify-end gap-1">
                                            <button onclick="openPreviewModal('{{ route('admin.lesson-templates.preview-modal', $lesson->hash_id) }}')"
                                                    class="action-link" title="View">View</button>
                                            <a href="{{ route('admin.lesson-templates.edit', $lesson->hash_id) }}"
                                               class="action-link" title="Edit">Edit</a>
                                            @if(!$lesson->trashed())
                                                @if($lesson->status === 'draft')
                                                    <a href="{{ route('admin.lesson-templates.publish.config', $lesson->hash_id) }}"
                                                       class="action-link primary" title="Publish">Publish</a>
                                                @endif
                                                <button onclick="confirmDelete('{{ $lesson->hash_id }}', '{{ addslashes($lesson->title) }}')"
                                                        class="action-link danger" title="Delete">Delete</button>
                                            @else
                                                <button onclick="restoreLesson('{{ $lesson->hash_id }}', '{{ addslashes($lesson->title) }}')"
                                                        class="action-link primary" title="Restore">Restore</button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($totalPages > 1)
                    <div class="table-pagination"
                         id="pagination-{{ $module->module_id }}"
                         data-total-pages="{{ $totalPages }}"
                         data-total-count="{{ $lessonCount }}"
                         data-page-size="{{ $pageSize }}">
                        <span class="pagination-info" id="pagination-info-{{ $module->module_id }}">
                            Showing 1–{{ min($pageSize, $lessonCount) }} of {{ $lessonCount }} lessons
                        </span>
                        <div class="pagination-buttons">
                            <button class="pagination-btn" id="prev-btn-{{ $module->module_id }}"
                                    onclick="changePage('{{ $module->module_id }}', currentPageOf('{{ $module->module_id }}') - 1)">
                                <span class="material-symbols-outlined text-[16px]">chevron_left</span>
                            </button>
                            @for($p = 1; $p <= $totalPages; $p++)
                            <button class="pagination-btn" id="page-btn-{{ $module->module_id }}-{{ $p }}"
                                    onclick="changePage('{{ $module->module_id }}', {{ $p }})">{{ $p }}</button>
                            @endfor
                            <button class="pagination-btn" id="next-btn-{{ $module->module_id }}"
                                    onclick="changePage('{{ $module->module_id }}', currentPageOf('{{ $module->module_id }}') + 1)">
                                <span class="material-symbols-outlined text-[16px]">chevron_right</span>
                            </button>
                        </div>
                    </div>
                    @endif
                @endif
            </div>

            {{-- Checkpoint Exams --}}
            @if($module->checkpointExams && $module->checkpointExams->isNotEmpty())
            <div class="px-6 pb-4 pt-3 border-t border-amber-100 bg-amber-50/30">
                <div class="text-[11px] font-bold text-amber-900 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-amber-600">emoji_events</span>
                    Checkpoint Exams
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
                        <a href="{{ route('admin.lesson-templates.checkpoint-exam.show', $exam->hash_id) }}"
                           class="action-link primary">View Exam</a>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Footer --}}
            <div style="padding: 12px 24px 20px; border-top: 1px solid #f8fafc;" class="flex items-center gap-4 flex-wrap">
                <a href="{{ route('admin.lesson-templates.create', ['module_id' => $module->module_id]) }}"
                   class="inline-flex items-center gap-1.5 text-[13px] font-bold text-[#0d326b] hover:underline">
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    Add Lesson to this Module
                </a>
                <span class="text-slate-300">|</span>
                <button onclick="openExamChoiceModal({{ $module->module_id }}, '{{ addslashes($module->title) }}', {{ $module->canCreateExam ? 'true' : 'false' }}, {{ $module->availableLessonsCount }})"
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-amber-50 text-[#a16207] border border-amber-200 text-[12px] font-bold hover:bg-amber-100 transition shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">assignment_add</span>
                    Add Checkpoint Exam
                </button>
            </div>
        </div>
        @empty
        <div class="module-card">
            <div class="empty-state py-16">
                <div class="w-20 h-20 rounded-3xl bg-[#0d326b]/08 flex items-center justify-center mb-5">
                    <span class="material-symbols-outlined text-[#0d326b] text-[40px]">menu_book</span>
                </div>
                <h3 class="text-[22px] font-bold text-[#0d326b] mb-2">No default modules yet</h3>
                <p class="text-slate-500 text-sm mb-6 max-w-md">Create the first default lesson and teachers will automatically receive a copy when they sign up.</p>
                <a href="{{ route('admin.lesson-templates.create') }}"
                   class="bg-gradient-to-r from-[#0d326b] via-[#1e4b8f] to-[#1a6fd4] hover:opacity-90 text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-all inline-flex items-center gap-2 shadow-md">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    Create the First Default Lesson
                </a>
            </div>
        </div>
        @endforelse

    </div><!-- /Left column -->

    <!-- Right: Insights panel (sticky) -->
    <div class="w-[300px] flex-shrink-0 insights-sticky">
        <div class="bg-white rounded-[24px] p-6 shadow-md border border-slate-100">
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
                $totalLessons   = $modules->sum(fn($m) => $m->lessons->count());
                $totalPublished = $modules->sum(fn($m) => $m->lessons->where('status','published')->count());
                $totalDraft     = $totalLessons - $totalPublished;
                $totalModules   = $modules->count();
                $archivedCount  = $modules->sum(fn($m) => $m->lessons->where('status','archived')->count());
                $totalTeachers  = $modules->sum(fn($m) => $m->teacherCopyCount);
            @endphp

            <div class="space-y-3 mb-6">
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
                    <span class="text-[18px] font-black text-white">{{ $totalPublished }}</span>
                </div>
                <div class="flex items-center justify-between bg-amber-50/80 border border-amber-200/70 rounded-2xl px-4 py-3">
                    <span class="text-[12px] font-bold text-amber-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-amber-600">edit_note</span>
                        Drafts
                    </span>
                    <span class="text-[18px] font-black text-amber-800">{{ $totalDraft }}</span>
                </div>
                @if($archivedCount > 0)
                <div class="flex items-center justify-between bg-slate-100 border border-slate-200 rounded-2xl px-4 py-3">
                    <span class="text-[12px] font-bold text-slate-600 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-slate-500">archive</span>
                        Archived
                    </span>
                    <span class="text-[18px] font-black text-slate-600">{{ $archivedCount }}</span>
                </div>
                @endif
                <div class="flex items-center justify-between bg-emerald-50 border border-emerald-100 rounded-2xl px-4 py-3">
                    <span class="text-[12px] font-bold text-emerald-800 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-emerald-600">group</span>
                        Teacher Copies
                    </span>
                    <span class="text-[18px] font-black text-emerald-800">{{ $totalTeachers }}</span>
                </div>
            </div>

            <a href="{{ route('admin.lesson-templates.create') }}"
               class="w-full bg-gradient-to-r from-[#0d326b] via-[#1e4b8f] to-[#1a6fd4] hover:opacity-95 text-white py-3.5 rounded-2xl text-[13px] font-extrabold transition-all flex items-center justify-center gap-2 shadow-md hover:shadow-lg active:scale-[0.99]">
                <span class="material-symbols-outlined text-[18px]">add</span>
                New Default Lesson
            </a>

            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                <button onclick="openPushModal()" class="text-[12px] font-bold text-[#0d326b] hover:text-[#1a6fd4] transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[17px]">sync</span>
                    Push to Teachers
                </button>
            </div>
        </div>
    </div><!-- /Right column -->

</div><!-- /flex wrapper -->

{{-- ── PAGINATION + SORTABLE SCRIPT ─────────────────────────────────────── --}}
<script>
const _currentPages = {};
function currentPageOf(moduleId) { return _currentPages[moduleId] || 1; }

function changePage(moduleId, page) {
    const wrapper = document.getElementById('pagination-' + moduleId);
    if (!wrapper) return;
    const totalPages = parseInt(wrapper.dataset.totalPages, 10) || 1;
    const totalCount = parseInt(wrapper.dataset.totalCount, 10) || 0;
    const pageSize   = parseInt(wrapper.dataset.pageSize, 10) || 5;
    page = Math.max(1, Math.min(page, totalPages));
    _currentPages[moduleId] = page;
    document.querySelectorAll('#lesson-tbody-' + moduleId + ' tr.lesson-row').forEach(function (row) {
        row.style.display = (parseInt(row.dataset.page, 10) === page) ? '' : 'none';
    });
    for (let p = 1; p <= totalPages; p++) {
        const btn = document.getElementById('page-btn-' + moduleId + '-' + p);
        if (btn) btn.classList.toggle('active', p === page);
    }
    const prevBtn = document.getElementById('prev-btn-' + moduleId);
    const nextBtn = document.getElementById('next-btn-' + moduleId);
    if (prevBtn) prevBtn.disabled = (page <= 1);
    if (nextBtn) nextBtn.disabled = (page >= totalPages);
    const info = document.getElementById('pagination-info-' + moduleId);
    if (info) {
        const start = (page - 1) * pageSize + 1;
        const end   = Math.min(page * pageSize, totalCount);
        info.textContent = 'Showing ' + start + '–' + end + ' of ' + totalCount + ' lessons';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[id^="pagination-"]').forEach(function (wrapper) {
        const moduleId = wrapper.id.replace('pagination-', '');
        changePage(moduleId, 1);
    });

    // Sortable drag-reorder on each module's tbody
    if (typeof Sortable !== 'undefined') {
        document.querySelectorAll('tbody[id^="lesson-tbody-"]').forEach(function (tbody) {
            const moduleId = tbody.id.replace('lesson-tbody-', '');
            new Sortable(tbody, {
                handle: '.drag-handle',
                animation: 200,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                dragClass: 'sortable-drag',
                onEnd: function (evt) {
                    if (evt.oldIndex === evt.newIndex) return;
                    const rows = Array.from(tbody.querySelectorAll('tr.lesson-row'));
                    const lessonIds = rows.map(r => r.dataset.lessonId).filter(Boolean);
                    const wrapper = document.getElementById('pagination-' + moduleId);
                    const pageSize = parseInt(wrapper?.dataset.pageSize || '5', 10);
                    rows.forEach((row, idx) => { row.dataset.page = Math.floor(idx / pageSize) + 1; });
                    changePage(moduleId, currentPageOf(moduleId));
                    fetch('{{ route("admin.lesson-templates.reorder") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({ module_id: parseInt(moduleId, 10), lesson_ids: lessonIds })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.success) showToast('success', 'Lesson order updated!');
                        else showToast('error', data?.message || 'Failed to update order.');
                    })
                    .catch(() => showToast('error', 'Network error while updating order.'));
                }
            });
        });
    }
});

// ── TOAST ──────────────────────────────────────────────────────────────────
function showToast(type, message) {
    const toast = document.getElementById('toastNotification');
    const content = document.getElementById('toastContent');
    const icon = document.getElementById('toastIcon');
    const title = document.getElementById('toastTitle');
    const msg = document.getElementById('toastMessage');
    if (type === 'success') {
        content.className = 'rounded-2xl px-6 py-4 shadow-lg flex items-center gap-3 min-w-[280px] max-w-md bg-emerald-600';
        icon.textContent = '✅'; title.textContent = 'Success';
    } else {
        content.className = 'rounded-2xl px-6 py-4 shadow-lg flex items-center gap-3 min-w-[280px] max-w-md bg-red-600';
        icon.textContent = '❌'; title.textContent = 'Error';
    }
    msg.textContent = message;
    toast.classList.remove('hidden');
    clearTimeout(window.toastTimeout);
    window.toastTimeout = setTimeout(() => toast.classList.add('hidden'), 4000);
}

// ── DELETE ─────────────────────────────────────────────────────────────────
let _deleteLessonId = null;
let _selectedDeleteAction = null;

function confirmDelete(lessonId, lessonTitle) {
    _deleteLessonId = lessonId;
    _selectedDeleteAction = null;
    document.getElementById('deleteLessonTitle').textContent = lessonTitle;
    document.getElementById('selectedDeleteOption').textContent = 'Click an option above to select';
    const btn = document.getElementById('confirmDeleteBtn');
    btn.textContent = 'Select an option'; btn.disabled = true;
    btn.className = 'flex-1 py-3 bg-slate-300 text-white font-semibold rounded-2xl transition-colors cursor-not-allowed';
    document.getElementById('deleteOptionSoft').className = 'p-4 rounded-2xl border-2 border-slate-200 hover:border-[#0d326b] transition cursor-pointer bg-white hover:bg-[#f8faff]';
    document.getElementById('deleteOptionHard').className = 'p-4 rounded-2xl border-2 border-slate-200 hover:border-red-500 transition cursor-pointer bg-white hover:bg-red-50/50';
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function selectDeleteOption(type) {
    _selectedDeleteAction = type;
    document.getElementById('deleteOptionSoft').className = 'p-4 rounded-2xl border-2 border-slate-200 transition cursor-pointer bg-white';
    document.getElementById('deleteOptionHard').className = 'p-4 rounded-2xl border-2 border-slate-200 transition cursor-pointer bg-white';
    if (type === 'soft') {
        document.getElementById('deleteOptionSoft').className = 'p-4 rounded-2xl border-2 border-[#0d326b] bg-blue-50/50 transition cursor-pointer';
        document.getElementById('selectedDeleteOption').textContent = '✅ Archive selected — student data preserved';
        document.getElementById('selectedDeleteOption').className = 'text-center text-xs text-blue-600 font-bold mb-4';
    } else {
        document.getElementById('deleteOptionHard').className = 'p-4 rounded-2xl border-2 border-red-500 bg-red-50/50 transition cursor-pointer';
        document.getElementById('selectedDeleteOption').textContent = '⚠️ PERMANENT DELETE selected — all data removed';
        document.getElementById('selectedDeleteOption').className = 'text-center text-xs text-red-600 font-bold mb-4';
    }
    const btn = document.getElementById('confirmDeleteBtn');
    btn.disabled = false;
    btn.className = type === 'soft'
        ? 'flex-1 py-3 bg-[#0d326b] hover:bg-[#154188] text-white font-semibold rounded-2xl transition-colors'
        : 'flex-1 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-2xl transition-colors';
    btn.textContent = type === 'soft' ? 'Archive Lesson' : 'Permanently Delete';
}

function executeDelete() {
    if (!_selectedDeleteAction || !_deleteLessonId) return;
    const btn = document.getElementById('confirmDeleteBtn');
    btn.disabled = true; btn.textContent = 'Processing...';
    const base = '/admin/lessons/';
    const url = _selectedDeleteAction === 'soft' ? base + _deleteLessonId + '/soft-delete' : base + _deleteLessonId + '/hard-delete';
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
    })
    .then(r => r.json().then(data => ({ ok: r.ok, data })))
    .then(({ ok, data }) => {
        if (ok && data.success) {
            showToast('success', data.message || 'Done');
            closeDeleteModal();
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast('error', data?.message || 'Something went wrong');
            btn.disabled = false; btn.textContent = 'Try Again';
        }
    })
    .catch(() => {
        showToast('error', 'Network error. Please try again.');
        btn.disabled = false; btn.textContent = 'Try Again';
    });
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = '';
    _deleteLessonId = null; _selectedDeleteAction = null;
}

// ── RESTORE ────────────────────────────────────────────────────────────────
function restoreLesson(lessonId, lessonTitle) {
    if (!confirm(`Restore "${lessonTitle}"?`)) return;
    const btn = event.target;
    btn.textContent = 'Restoring...'; btn.disabled = true;
    fetch(`/admin/lessons/${lessonId}/restore`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            'Content-Type': 'application/json', 'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(data => {
        if (data && data.success) { showToast('success', 'Lesson restored!'); setTimeout(() => window.location.reload(), 1500); }
        else { showToast('error', data?.message || 'Failed'); btn.textContent = 'Restore'; btn.disabled = false; }
    })
    .catch(() => { showToast('error', 'Network error.'); btn.textContent = 'Restore'; btn.disabled = false; });
}

// ── PREVIEW MODAL ──────────────────────────────────────────────────────────
function openPreviewModal(url) {
    const modal   = document.getElementById('lessonPreviewModal');
    const loading = document.getElementById('lessonPreviewLoading');
    const body    = document.getElementById('lessonPreviewBody');
    body.innerHTML = ''; body.style.display = 'none';
    loading.style.display = 'flex';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';
    fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => { if (!r.ok) throw new Error('HTTP ' + r.status); return r.text(); })
        .then(html => {
            body.innerHTML = html;
            loading.style.display = 'none';
            body.style.display = 'block';
            body.querySelectorAll('script').forEach(oldScript => {
                const s = document.createElement('script');
                if (oldScript.src) s.src = oldScript.src; else s.textContent = oldScript.textContent;
                document.head.appendChild(s); oldScript.remove();
            });
        })
        .catch(err => { loading.innerHTML = '<span style="color:#fca5a5;">⚠ Failed to load preview.</span>'; });
}

function closeLessonPreviewModal() {
    document.getElementById('lessonPreviewModal').style.display = 'none';
    document.body.style.overflow = '';
    document.getElementById('lessonPreviewBody').innerHTML = '';
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') { closeLessonPreviewModal(); closeExamChoiceModal(); closeDeleteModal(); closePushModal(); }
});

// ── EXAM CHOICE MODAL ──────────────────────────────────────────────────────
let currentAdminModuleId = null;

function openExamChoiceModal(moduleId, moduleTitle, canCreate, availableCount) {
    currentAdminModuleId = moduleId;
    document.getElementById('adminModalModuleTitle').textContent = `Module: ${moduleTitle}`;
    document.getElementById('examChoiceModal').classList.remove('hidden');
}
function closeExamChoiceModal() { document.getElementById('examChoiceModal').classList.add('hidden'); }
function proceedWithAdminChoice() {
    if (currentAdminModuleId) window.location.href = `{{ url('/admin/lessons/checkpoint-exam/create') }}?module_id=${currentAdminModuleId}`;
}

// ── PUSH MODAL ─────────────────────────────────────────────────────────────
let pushTeachers = [];
let pushModuleId = null;

function openPushModal(moduleId = null) {
    pushModuleId = moduleId;
    document.getElementById('pushModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    loadPushTeachers();
}
function closePushModal() {
    document.getElementById('pushModal').classList.add('hidden');
    document.body.style.overflow = '';
}
function loadPushTeachers() {
    const loading = document.getElementById('pushTeacherLoading');
    const container = document.getElementById('pushTeacherContainer');
    loading.classList.remove('hidden'); container.classList.add('hidden'); container.innerHTML = '';
    fetch('{{ route('admin.lesson-templates.teachers') }}', { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        pushTeachers = data.teachers || [];
        renderPushTeachers(pushTeachers);
        loading.classList.add('hidden'); container.classList.remove('hidden');
        updatePushCount();
    })
    .catch(() => { loading.innerHTML = '❌ Failed to load teachers.'; });
}
function renderPushTeachers(teachers) {
    const container = document.getElementById('pushTeacherContainer');
    container.innerHTML = '';
    if (teachers.length === 0) { container.innerHTML = '<div class="py-8 text-center text-slate-400 text-sm">No teachers found.</div>'; return; }
    teachers.forEach(teacher => {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 transition-colors teacher-row';
        div.dataset.id = teacher.id; div.dataset.name = teacher.name.toLowerCase();
        const hasCopies = teacher.has_copies === true || teacher.has_copies === 1 || teacher.has_copies === '1';
        const badge = hasCopies
            ? '<span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Has copies</span>'
            : '<span class="text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">No copies yet</span>';
        div.innerHTML = `
            <input type="checkbox" class="push-teacher-checkbox w-4 h-4 rounded accent-[#0d326b] flex-shrink-0 cursor-pointer"
                   data-id="${teacher.id}" onchange="updatePushCount()">
            <div class="w-8 h-8 rounded-full bg-[#0d326b]/10 text-[#0d326b] font-bold text-xs flex items-center justify-center flex-shrink-0">${teacher.initials || '?'}</div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-800 truncate">${teacher.name}</p>
                <p class="text-xs text-slate-400 truncate">${teacher.email || ''}</p>
            </div>${badge}`;
        container.appendChild(div);
    });
    document.querySelectorAll('.push-teacher-checkbox').forEach(cb => {
        const teacher = pushTeachers.find(t => t.id === parseInt(cb.dataset.id));
        if (teacher && (teacher.has_copies === true || teacher.has_copies === 1 || teacher.has_copies === '1')) cb.checked = true;
    });
    updatePushCount();
}
function updatePushCount() { document.getElementById('pushSelectedCount').textContent = document.querySelectorAll('.push-teacher-checkbox:checked').length; }
function selectAllPushTeachers() { document.querySelectorAll('.push-teacher-checkbox').forEach(cb => cb.checked = true); updatePushCount(); }
function deselectAllPushTeachers() { document.querySelectorAll('.push-teacher-checkbox').forEach(cb => cb.checked = false); updatePushCount(); }
document.getElementById('pushTeacherSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.teacher-row').forEach(row => { row.style.display = row.dataset.name.includes(q) ? '' : 'none'; });
});
function executePush() {
    const selected = document.querySelectorAll('.push-teacher-checkbox:checked');
    if (selected.length === 0) { alert('Please select at least one teacher.'); return; }
    const teacherIds = Array.from(selected).map(cb => parseInt(cb.dataset.id));
    const btn = document.getElementById('pushExecuteBtn');
    btn.disabled = true; btn.innerHTML = '<div class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div> Pushing...';
    const payload = { teacher_ids: teacherIds, _token: document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}' };
    if (pushModuleId) payload.module_id = pushModuleId;
    fetch('{{ route('admin.lesson-templates.push-selected') }}', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}' },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) { closePushModal(); showToast('success', data.message); setTimeout(() => window.location.reload(), 2000); }
        else { alert(data.message || 'Push failed.'); btn.disabled = false; btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">sync</span> Push to Selected Teachers'; }
    })
    .catch(() => { alert('Network error.'); btn.disabled = false; btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">sync</span> Push to Selected Teachers'; });
}

document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        const newBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);
        newBtn.addEventListener('click', executeDelete);
    }
});
</script>

{{-- ── TOAST ────────────────────────────────────────────────────────────── --}}
<div id="toastNotification" class="fixed bottom-6 right-6 z-50 hidden">
    <div id="toastContent" class="rounded-2xl px-6 py-4 shadow-lg flex items-center gap-3 min-w-[280px] max-w-md">
        <span id="toastIcon" class="text-white text-[22px]">✅</span>
        <div>
            <p id="toastTitle" class="text-white font-bold text-sm">Success</p>
            <p id="toastMessage" class="text-white/80 text-xs">Operation completed</p>
        </div>
    </div>
</div>

{{-- ── LESSON PREVIEW MODAL ─────────────────────────────────────────────── --}}
<div id="lessonPreviewModal"
     style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; padding:24px 16px;"
     onclick="if(event.target===this) closeLessonPreviewModal()">
    <div style="position:fixed; inset:0; background:rgba(10,20,50,0.55); backdrop-filter:blur(4px);"></div>
    <button onclick="closeLessonPreviewModal()"
            style="position:fixed; top:18px; right:20px; z-index:10001; width:44px; height:44px;
                   background:rgba(255,255,255,0.95); border:none; border-radius:50%; font-size:22px;
                   cursor:pointer; display:flex; align-items:center; justify-content:center;
                   box-shadow:0 4px 20px rgba(0,0,0,0.2); transition:transform .2s, background .2s;"
            onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform=''">✕</button>
    <div id="lessonPreviewContent" style="position:relative; z-index:10000; max-width:900px; margin:0 auto; min-height:200px;">
        <div id="lessonPreviewLoading"
             style="display:flex; align-items:center; justify-content:center; height:260px; color:rgba(255,255,255,0.85); font-size:15px; font-weight:600; gap:10px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="animation:spin .7s linear infinite;">
                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
            </svg>
            Loading preview…
        </div>
        <div id="lessonPreviewBody" style="display:none;"></div>
    </div>
</div>
<style>@keyframes spin { to { transform: rotate(360deg); } }</style>

{{-- ── DELETE MODAL ────────────────────────────────────────────────────── --}}
<div id="deleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl relative">
        <button onclick="closeDeleteModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined text-2xl">close</span>
        </button>
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-red-500 text-3xl">warning</span>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Delete "<span id="deleteLessonTitle" class="text-[#0d326b]"></span>"?</h3>
            <p class="text-slate-500 text-sm">Choose how you want to delete this lesson.</p>
        </div>
        <div class="space-y-3 mb-6">
            <div class="p-4 rounded-2xl border-2 border-slate-200 hover:border-[#0d326b] transition cursor-pointer bg-white hover:bg-[#f8faff]"
                 onclick="selectDeleteOption('soft')" id="deleteOptionSoft">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[22px]">archive</span>
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <h4 class="text-sm font-bold text-[#0d326b]">Archive (Preserve Data)</h4>
                            <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">Recommended</span>
                        </div>
                        <p class="text-xs text-slate-500 mt-0.5">Lesson is hidden but data is preserved. Can be restored.</p>
                    </div>
                </div>
            </div>
            <div class="p-4 rounded-2xl border-2 border-slate-200 hover:border-red-500 transition cursor-pointer bg-white hover:bg-red-50/50"
                 onclick="selectDeleteOption('hard')" id="deleteOptionHard">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[22px]">delete_forever</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-red-600">Permanently Delete</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Deletes the lesson and all related data. <strong class="text-red-500">Cannot be undone.</strong></p>
                    </div>
                </div>
            </div>
        </div>
        <div id="selectedDeleteOption" class="text-center text-xs text-slate-400 font-medium mb-4">Click an option above to select</div>
        <div class="flex gap-3">
            <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 py-3 border border-slate-200 rounded-2xl text-slate-600 font-semibold hover:bg-slate-50 transition-colors">Cancel</button>
            <button type="button" id="confirmDeleteBtn"
                    class="flex-1 py-3 bg-slate-300 text-white font-semibold rounded-2xl transition-colors cursor-not-allowed" disabled>
                Select an option
            </button>
        </div>
    </div>
</div>

{{-- ── PUSH TO SELECTED TEACHERS MODAL ─────────────────────────────────── --}}
<div id="pushModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[9999] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] shadow-2xl flex flex-col">
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 flex-shrink-0">
            <div>
                <h3 class="text-base font-bold text-[#0d326b] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#1a6fd4]">sync</span>
                    Push to Selected Teachers
                </h3>
                <p class="text-xs text-slate-400 font-medium mt-0.5">Select which teachers should receive the updated default lessons.</p>
            </div>
            <button onclick="closePushModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <div class="px-6 py-3 border-b border-slate-50 flex-shrink-0">
            <div class="flex items-center gap-3 flex-wrap">
                <div class="relative flex-1 min-w-[180px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                    <input type="text" id="pushTeacherSearch" placeholder="Search teachers..."
                           class="w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl text-sm focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="selectAllPushTeachers()"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold text-[#0d326b] bg-[#f0f4ff] hover:bg-[#e0e8ff] transition">Select All</button>
                    <button type="button" onclick="deselectAllPushTeachers()"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 transition">Deselect All</button>
                </div>
            </div>
        </div>
        <div class="overflow-y-auto flex-1 px-6 py-3" id="pushTeacherList">
            <div id="pushTeacherLoading" class="py-8 text-center text-slate-400 text-sm">
                <div class="inline-block w-6 h-6 border-2 border-slate-200 border-t-[#0d326b] rounded-full animate-spin mr-2"></div>
                Loading teachers...
            </div>
            <div id="pushTeacherContainer" class="space-y-1 hidden"></div>
        </div>
        <div class="flex items-center justify-between px-6 py-4 border-t border-slate-100 flex-shrink-0">
            <span class="text-xs text-slate-400 font-medium"><span id="pushSelectedCount">0</span> teachers selected</span>
            <div class="flex gap-3">
                <button type="button" onclick="closePushModal()"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">Cancel</button>
                <button type="button" onclick="executePush()" id="pushExecuteBtn"
                        class="px-6 py-2 rounded-xl bg-[#0d326b] text-white font-bold text-sm hover:bg-[#1a6fd4] transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-[18px]">sync</span>
                    Push to Selected Teachers
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── EXAM CHOICE MODAL ────────────────────────────────────────────────── --}}
<div id="examChoiceModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[9998] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-[#0d326b] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#ca8a04]">assignment_add</span>
                    Add Checkpoint Exam
                </h3>
                <p class="text-xs text-slate-400 font-medium mt-0.5" id="adminModalModuleTitle">Module</p>
            </div>
            <button onclick="closeExamChoiceModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>
        <div class="space-y-3">
            <div class="p-4 rounded-xl border border-slate-200 hover:border-[#1a6fd4]/40 bg-white hover:bg-[#f0f6ff] transition cursor-pointer flex items-center justify-between group"
                 onclick="proceedWithAdminChoice()">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-[#0d326b]/10 text-[#0d326b] flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-[22px]">checklist</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-[#0d326b] group-hover:text-[#1a6fd4] transition">Choose from Module Lessons &amp; Quizzes</h4>
                        <p class="text-xs text-slate-400 font-medium">Select questions from published lessons and customize point values.</p>
                    </div>
                </div>
                <span class="material-symbols-outlined text-slate-300 group-hover:text-[#1a6fd4] text-[20px] transition">chevron_right</span>
            </div>
        </div>
    </div>
</div>

@endsection
