@extends('layouts.app')
@section('title', 'Lessons')
@section('content')

<style>
    /* ── Module Card Styles ────────────────────────────────────────────── */
    .module-card {
        border-radius: 24px;
        border: 1.5px solid #e5eaf2;
        background: #ffffff;
        box-shadow: 0 4px 20px rgba(13, 50, 107, 0.03);
        overflow: hidden;
        transition: transform .25s cubic-bezier(.4,0,.2,1), box-shadow .25s cubic-bezier(.4,0,.2,1);
    }
    .module-card:hover {
        box-shadow: 0 16px 40px rgba(13, 50, 107, 0.08);
        transform: translateY(-3px);
    }

    /* ── Trapezoid Tab ──────────────────────────────────────────────────── */
    .module-tab {
        display: inline-block;
        padding: 6px 22px;
        font-size: 10px;
        font-weight: 800;
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: 0.14em;
        clip-path: polygon(0 0, 100% 0, 88% 100%, 0% 100%);
        background: linear-gradient(90deg, #0d326b, #1e4b8f) !important;
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

    .module-title-wrap {
        display: flex;
        align-items: center;
        gap: 16px;
    }

    .module-icon {
        width: 48px;
        height: 48px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 20px;
        box-shadow: 0 4px 12px rgba(13, 50, 107, 0.06);
    }

    .module-title-text {
        font-size: 18px;
        font-weight: 800;
        color: #0d326b;
        letter-spacing: -0.01em;
    }

    .module-meta {
        font-size: 12.5px;
        color: #64748b;
        font-weight: 500;
        margin-top: 2px;
    }

    .module-stats {
        display: flex;
        align-items: center;
        gap: 14px;
        font-size: 12px;
        background: #fafcff;
        padding: 6px 14px;
        border-radius: 14px;
        border: 1px solid #e5eaf2;
    }

    .module-stat-item {
        display: flex;
        align-items: center;
        gap: 5px;
        color: #475569;
        font-weight: 700;
    }

    .module-stat-item .material-symbols-outlined {
        font-size: 17px;
        color: #1a6fd4;
    }

    .module-progress {
        width: 90px;
        height: 6px;
        border-radius: 9999px;
        background: #e2e8f0;
        overflow: hidden;
    }

    .module-progress-fill {
        height: 100%;
        border-radius: 9999px;
        background: linear-gradient(90deg, #1a6fd4, #3b82f6);
        transition: width .6s ease;
    }

    /* ── Table Styles ──────────────────────────────────────────────────── */
    .lesson-table-wrap {
        padding: 0 24px 20px;
        overflow-x: auto;
    }

    .lesson-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .lesson-table thead th {
        padding: 14px 16px;
        text-align: left;
        font-size: 10.5px;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        border-bottom: 1.5px solid #f1f5f9;
        background: #f8fafc;
    }

    .lesson-table tbody td {
        padding: 14px 16px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
    }

    .lesson-table tbody tr {
        transition: all .2s ease;
    }
    .lesson-table tbody tr:hover {
        background: #f0f7ff;
    }

    .lesson-title-cell {
        font-weight: 700;
        color: #0d326b;
        font-size: 14px;
    }

    .badge-type {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
    }

    .badge-difficulty {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .badge-difficulty.beginner { background: #eff6ff; color: #1e4b8f; border: 1px solid #bfdbfe; }
    .badge-difficulty.intermediate { background: #e0e7ff; color: #3730a3; border: 1px solid #c7d2fe; }
    .badge-difficulty.advanced { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }

    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }
    .badge-status.published { background: #0d326b; color: #ffffff; box-shadow: 0 2px 6px rgba(13,50,107,0.18); }
    .badge-status.draft { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
    .badge-status.archived { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

    .action-link {
        font-size: 12px;
        font-weight: 700;
        color: #475569;
        transition: all .2s;
        text-decoration: none;
        padding: 5px 12px;
        border-radius: 9px;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        white-space: nowrap; 
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .action-link:hover { color: #0d326b; background: #e0e8ff; border-color: #bfdbfe; transform: translateY(-1px); }
    .action-link.danger { color: #dc2626; background: #fef2f2; border-color: #fecaca; }
    .action-link.danger:hover { color: #b91c1c; background: #fee2e2; border-color: #fca5a5; transform: translateY(-1px); }
    .action-link.primary { color: #fff; background: linear-gradient(135deg, #0d326b, #1a6fd4); border: none; box-shadow: 0 3px 10px rgba(13,50,107,0.2); }
    .action-link.primary:hover { opacity: 0.95; transform: translateY(-1px); box-shadow: 0 5px 14px rgba(13,50,107,0.3); }

    /* ── Pagination ────────────────────────────────────────────────────── */
    .table-pagination {
        padding: 16px 24px 20px;
        border-top: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 12px;
    }

    .pagination-info {
        font-size: 12.5px;
        color: #64748b;
        font-weight: 600;
    }

    .pagination-buttons {
        display: flex;
        gap: 6px;
    }

    .pagination-btn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #fff;
        color: #475569;
        font-weight: 700;
        font-size: 13px;
        cursor: pointer;
        transition: all .15s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .pagination-btn:hover { background: #f1f5f9; border-color: #cbd5e1; color: #0d326b; }
    .pagination-btn.active { background: linear-gradient(135deg, #0d326b, #1a6fd4); color: #fff; border: none; box-shadow: 0 3px 10px rgba(13,50,107,0.2); }
    .pagination-btn:disabled { opacity: .4; cursor: not-allowed; }

    /* ── Sticky Sidebar ────────────────────────────────────────────────── */
    .insights-sticky {
        position: sticky;
        top: 24px;
        align-self: flex-start;
    }

    /* ── Empty State ───────────────────────────────────────────────────── */
    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px 24px;
        text-align: center;
    }

    /* ── Orphaned Lessons ─────────────────────────────────────────────── */
    .orphan-section {
        margin-top: 32px;
    }
    .orphan-section .module-card {
        border-color: #e2e8f0;
    }
    .orphan-section .module-header {
        background: #fafcff;
        padding-top: 0;
    }
    .orphan-section .module-tab {
        background: linear-gradient(90deg, #64748b, #475569);
        color: #ffffff;
    }

    /* ── Scrollable table container ───────────────────────────────────── */
    .table-scroll {
        max-height: 360px;
        overflow-y: auto;
    }
    .table-scroll::-webkit-scrollbar {
        width: 5px;
    }
    .table-scroll::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .table-scroll::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }

    /* ── Drag & Drop Reordering ─────────────────────────────────────────── */
    .drag-handle {
        cursor: grab;
        user-select: none;
        -webkit-user-select: none;
        touch-action: none;
    }
    .drag-handle:active {
        cursor: grabbing;
    }
    .sortable-ghost {
        opacity: 0.35 !important;
        background: #e0f2fe !important;
        outline: 2px dashed #1a6fd4 !important;
    }
    .sortable-chosen {
        background: #f0f7ff !important;
        box-shadow: 0 4px 16px rgba(13, 50, 107, 0.1);
    }
    .sortable-drag {
        opacity: 0.95 !important;
        background: #ffffff !important;
        box-shadow: 0 12px 30px rgba(13, 50, 107, 0.2);
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>

@if(session('success'))
<div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800 flex items-center gap-3 shadow-sm">
    <span class="material-symbols-outlined icon-outline text-[20px] text-emerald-600">check_circle</span>
    {{ session('success') }}
</div>
@endif



<div class="flex flex-col lg:flex-row gap-6">

    <!-- Left Side: Modules + Lessons -->
    <div class="flex-1 min-w-0 flex flex-col space-y-6">

        @php
            $moduleColors = ['#0d326b','#1e4b8f','#1a6fd4','#3b82f6','#2563EB','#059669','#D97706'];
            $moduleIcons  = ['📚','📖','✏️','📝','📘','📗','📕'];
            $pageSize     = 5;
        @endphp

        {{-- ── Modules with lessons ─────────────────────────────────────── --}}
        @forelse($modules as $modIndex => $module)
        @php
            $modColor    = $moduleColors[$modIndex % count($moduleColors)];
            $modIcon     = $moduleIcons[$modIndex % count($moduleIcons)];
            $padNum      = str_pad($modIndex + 1, 2, '0', STR_PAD_LEFT);
            $lessonCount = $module->lessons->count();
            $published   = $module->lessons->where('status','published')->count();
            $progress    = $lessonCount > 0 ? round($published / $lessonCount * 100) : 0;
            $totalPages  = max(1, (int) ceil($lessonCount / $pageSize));
            // Render ALL lessons up-front; pagination is handled entirely client-side
            // (show/hide rows via JS) so "next page" never needs a server round trip.
            $allLessons  = $module->lessons->values();
        @endphp

        <div class="module-card" id="module-{{ $module->module_id }}">
            {{-- Trapezoid Tab --}}
            <div class="module-tab">
                MODULE {{ $padNum }}
            </div>

   {{-- Module Header --}}
<div class="module-header flex items-center justify-between flex-wrap gap-4">
    <div class="module-title-wrap">
        <div class="module-icon" style="background:{{ $modColor }}15; color:{{ $modColor }};">
            {{ $modIcon }}
        </div>
        <div>
            <div class="module-title-text">{{ $module->title }}</div>
            <div class="module-meta">
                {{ $lessonCount }} lesson{{ $lessonCount !== 1 ? 's' : '' }}
                @if($module->description)
                <span class="mx-1.5">·</span> {{ $module->description }}
                @endif
            </div>
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

        <div class="flex items-center gap-2">
            @if($lessonCount > 1)
            {{-- Reorder Module Lessons Button --}}
            <button type="button"
               onclick="openReorderModal({{ $module->module_id }}, '{{ addslashes($module->title) }}')"
               class="w-9 h-9 rounded-lg border-2 border-slate-200 hover:border-[#1a6fd4] hover:bg-blue-50 transition-all flex items-center justify-center text-slate-400 hover:text-[#1a6fd4] flex-shrink-0"
               title="Reorder All Lessons in this Module">
                <span class="material-symbols-outlined text-[18px]">swap_vert</span>
            </button>
            @endif

            {{-- Edit Module Icon Button --}}
            <button type="button"
               onclick="openEditModuleModal({{ $module->module_id }}, '{{ addslashes($module->title) }}', '{{ addslashes($module->description ?? '') }}', '{{ $module->mastery_level ?? 'beginner' }}')"
               class="w-9 h-9 rounded-lg border-2 border-slate-200 hover:border-[#1a6fd4] hover:bg-blue-50 transition-all flex items-center justify-center text-slate-400 hover:text-[#1a6fd4] flex-shrink-0"
               title="Edit Module">
                <span class="material-symbols-outlined text-[18px]">edit</span>
            </button>

            {{-- Delete Module Icon Button --}}
            <a href="{{ route('modules.delete-options', $module->module_id) }}" 
               class="w-9 h-9 rounded-lg border-2 border-slate-200 hover:border-red-400 hover:bg-red-50 transition-all flex items-center justify-center text-slate-400 hover:text-red-500 flex-shrink-0"
               title="Delete Module">
                <span class="material-symbols-outlined text-[18px]">delete</span>
            </a>
        </div>
    </div>
</div>

            {{-- Lessons Table --}}
            <div class="lesson-table-wrap">
                @if($lessonCount === 0)
                    <div class="empty-state py-8">
                        <div class="w-14 h-14 rounded-2xl bg-[#f1f5f9] flex items-center justify-center mb-3">
                            <span class="material-symbols-outlined text-slate-400 text-[28px]">menu_book</span>
                        </div>
                        <p class="text-[14px] font-semibold text-slate-400">No lessons in this module yet</p>
                        <a href="{{ route('lessons.create', ['module_id' => $module->module_id]) }}" class="mt-3 text-[13px] font-bold text-[#0d326b] hover:underline flex items-center gap-1">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            Create your first lesson
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
                                    <th style="width:160px;text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="lesson-tbody-{{ $module->module_id }}">
                                @foreach($allLessons as $lessonIndex => $lesson)
                                @php $lessonPage = intdiv($lessonIndex, $pageSize) + 1; @endphp
                                <tr class="lesson-row" data-lesson-id="{{ $lesson->lesson_id }}" data-page="{{ $lessonPage }}"
                                    onclick="openPreviewModal('{{ route('lessons.preview-modal', $lesson->hash_id) }}')" style="cursor:pointer;">
                                    <td class="drag-handle text-slate-300 hover:text-[#0d326b] transition-colors" onclick="event.stopPropagation();" title="Drag to reorder">
                                        <span class="material-symbols-outlined text-[18px]">drag_indicator</span>
                                    </td>
                                    <td class="lesson-title-cell">{{ $lesson->title }}</td>
                                    <td>
                                        <span class="badge-difficulty {{ $lesson->difficulty }}">
                                            {{ $lesson->difficulty }}
                                        </span>
                                    </td>
                                  <td>
    @if($lesson->trashed())
        <span class="badge-status" style="background:#e2e8f0; color:#64748b;">
            Archived
        </span>
    @else
        <span class="badge-status {{ $lesson->status }}">
            {{ $lesson->status }}
        </span>
    @endif
</td>
                                    <td style="text-align:right;" onclick="event.stopPropagation();">
    <div class="flex items-center justify-end gap-1">
        <button onclick="openPreviewModal('{{ route('lessons.preview-modal', $lesson->hash_id) }}')" class="action-link" title="View">View</button>
        <a href="{{ route('lessons.edit', $lesson->hash_id) }}" class="action-link" title="Edit">Edit</a>
        
        @if($lesson->trashed())
            {{-- Show Restore button for archived lessons --}}
            <button onclick="restoreLesson('{{ $lesson->hash_id }}', '{{ addslashes($lesson->title) }}')"
                    class="action-link primary" title="Restore">
                Restore
            </button>
        @else
            {{-- Show normal actions for non-archived lessons --}}
            @if($lesson->status === 'draft')
            <a href="{{ route('lessons.publish.config', $lesson->hash_id) }}" class="action-link primary" title="Publish">Publish</a>
            @endif
            @if($lesson->status === 'published')
            <button onclick="openStudentsModal('{{ $lesson->hash_id }}', '{{ addslashes($lesson->title) }}')"
                    class="action-link primary" title="Manage Students">
                Students
            </button>
            @endif
            <button onclick="confirmDelete('{{ $lesson->hash_id }}', '{{ addslashes($lesson->title) }}')"
                    class="action-link danger" title="Delete">
                Delete
            </button>
        @endif
    </div>
</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
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
                                    onclick="changePage('{{ $module->module_id }}', {{ $p }})">
                                {{ $p }}
                            </button>
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

            {{-- Checkpoint Exams section inside module card --}}
            @if($module->checkpointExams && $module->checkpointExams->isNotEmpty())
            <div class="px-6 pb-4 pt-3 border-t border-purple-100 bg-purple-50/30">
                <div class="text-[11px] font-bold text-purple-900 uppercase tracking-wider mb-2.5 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-purple-600">emoji_events</span>
                    Checkpoint Exams
                </div>
                <div class="space-y-2">
                    @foreach($module->checkpointExams as $exam)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-white border border-purple-100 shadow-sm hover:border-purple-300 transition flex-wrap gap-2">
                        <div class="flex items-center gap-3">
                            <span class="w-8 h-8 rounded-lg bg-purple-100 text-purple-700 flex items-center justify-center font-bold text-xs">
                                🏆
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-[#0d326b]">{{ $exam->title }}</span>
                                    <span class="badge-status {{ $exam->status }} text-[9px] px-2 py-0.5">{{ $exam->status }}</span>
                                </div>
                                <div class="text-[11px] text-slate-400 font-medium">
                                    {{ $exam->total_points }} pts · {{ $exam->questions->count() }} question(s) · Passing: {{ $exam->passing_score }} pts
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            <a href="{{ route('lessons.checkpoint-exam.show', $exam->hash_id) }}" class="action-link primary" title="View Exam">View Exam</a>
                            <form action="{{ route('lessons.checkpoint-exam.destroy', $exam->hash_id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this checkpoint exam?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="action-link danger" title="Delete Exam">Delete</button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Footer: Add Lesson & Create Exam --}}
            <div style="padding: 12px 24px 20px; border-top: 1px solid #f8fafc;" class="flex items-center justify-between flex-wrap gap-3">
                <a href="{{ route('lessons.create', ['module_id' => $module->module_id]) }}"
                   class="inline-flex items-center gap-1.5 text-[13px] font-bold text-[#0d326b] hover:underline">
                    <span class="material-symbols-outlined text-[16px]">add</span>
                    Add Lesson to this Module
                </a>

                <button onclick="openExamChoiceModal({{ $module->module_id }}, '{{ addslashes($module->title) }}', {{ $module->canCreateExam ? 'true' : 'false' }}, {{ $module->availableLessonsCount }})"
                   class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-purple-50 text-[#8b5cf6] border border-purple-200 text-[12px] font-bold hover:bg-purple-100 transition shadow-sm">
                    <span class="material-symbols-outlined text-[16px]">assignment_add</span>
                    Create Checkpoint Exam
                </button>
            </div>
        </div>
        @empty
        {{-- No modules yet --}}
        @endforelse

        {{-- ── Orphaned Lessons ─────────────────────────────────────────── --}}
        @if($orphanedLessons->isNotEmpty())
        <div class="orphan-section">
            <div class="module-card" style="border-color: #f1f5f9;">
                {{-- Trapezoid Tab for Orphaned --}}
                <div class="module-tab" style="background:#e2e8f0; color:#475569;">
                    UNASSIGNED
                </div>

                <div class="module-header" style="background:#fafcff; padding-top:12px;">
                    <div class="module-title-wrap">
                        <div class="module-icon" style="background:#f1f5f9; color:#64748b;">
                            📂
                        </div>
                        <div>
                            <div class="module-title-text" style="color:#475569;">Unassigned Lessons</div>
                            <div class="module-meta">{{ $orphanedLessons->count() }} lesson{{ $orphanedLessons->count() !== 1 ? 's' : '' }} without a module</div>
                        </div>
                    </div>
                </div>

                <div class="lesson-table-wrap">
                    <div class="table-scroll" style="max-height:300px;">
                        <table class="lesson-table">
                            <thead>
                                <tr>
                                    <th>Lesson Title</th>
                                    <th style="width:110px;">Difficulty</th>
                                    <th style="width:110px;">Status</th>
                                    <th style="width:200px;text-align:right;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                              @foreach($orphanedLessons as $lesson)
<tr onclick="openPreviewModal('{{ route('lessons.preview-modal', $lesson->hash_id) }}')" style="cursor:pointer;">
    <td class="lesson-title-cell">{{ $lesson->title }}</td>
    <td><span class="badge-difficulty {{ $lesson->difficulty }}">{{ $lesson->difficulty }}</span></td>
    <td>
        @if($lesson->trashed())
            <span class="badge-status" style="background:#e2e8f0; color:#64748b;">Archived</span>
        @else
            <span class="badge-status {{ $lesson->status }}">{{ $lesson->status }}</span>
        @endif
    </td>
    <td style="text-align:right;" onclick="event.stopPropagation();">
        <div class="flex items-center justify-end gap-1">
            <button onclick="openPreviewModal('{{ route('lessons.preview-modal', $lesson->hash_id) }}')" class="action-link">View</button>
            
            @if($lesson->trashed())
                {{-- Show Restore button for archived orphaned lessons --}}
                <button onclick="restoreLesson('{{ $lesson->hash_id }}', '{{ addslashes($lesson->title) }}')"
                        class="action-link primary" title="Restore">
                    Restore
                </button>
            @else
                {{-- Show normal actions for non-archived orphaned lessons --}}
                <a href="{{ route('lessons.edit', $lesson->hash_id) }}" class="action-link">Edit</a>
                <a href="{{ route('lessons.publish.config', $lesson->hash_id) }}" class="action-link primary">Assign &amp; Publish</a>
                @if($lesson->status === 'published')
                <button onclick="openStudentsModal('{{ $lesson->hash_id }}', '{{ addslashes($lesson->title) }}')"
                        class="action-link primary">Students</button>
                @endif
                <button onclick="confirmDelete('{{ $lesson->hash_id }}', '{{ addslashes($lesson->title) }}')"
                        class="action-link danger">Delete</button>
            @endif
        </div>
    </td>
</tr>
@endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ── Empty state when no modules AND no orphaned lessons ─────── --}}
        @if($modules->isEmpty() && $orphanedLessons->isEmpty())
        <div class="module-card">
            <div class="empty-state">
                <div class="w-20 h-20 rounded-3xl bg-[#0d326b]/08 flex items-center justify-center mb-5">
                    <span class="material-symbols-outlined text-[#0d326b] text-[40px]">menu_book</span>
                </div>
                <h3 class="text-[22px] font-bold text-[#0d326b] mb-2">No lessons yet</h3>
                <p class="text-slate-500 text-sm mb-6 max-w-md">Create your first lesson. You can organise it into a module right away or assign a module when you publish.</p>
                <button onclick="openNewLessonModal()"
                        class="bg-gradient-to-r from-[#0d326b] via-[#1e4b8f] to-[#1a6fd4] hover:opacity-90 text-white px-8 py-3.5 rounded-xl text-[14px] font-bold transition-all inline-flex items-center gap-2 shadow-md">
                    <span class="material-symbols-outlined icon-outline text-[18px]">add</span>
                    Create Your First Lesson
                </button>
            </div>
        </div>
        @endif

    </div><!-- /Left column -->

    <!-- Right Side: Insights panel (STICKY) -->
    <div class="w-[300px] flex-shrink-0 insights-sticky">
        <div class="bg-white rounded-[24px] p-6 shadow-md border border-slate-100">

            <div class="flex items-center gap-2.5 mb-5 pb-4 border-b border-slate-100">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#0d326b] flex items-center justify-center font-bold">
                    <span class="material-symbols-outlined text-[20px]">auto_awesome</span>
                </div>
                <div>
                    <span class="text-[15px] font-extrabold text-[#0d326b] block">Lesson Insights</span>
                    <span class="text-[11px] text-slate-400 font-medium">Curriculum Overview</span>
                </div>
            </div>

            @php
                $totalLessons    = $modules->sum(fn($m) => $m->lessons->count()) + $orphanedLessons->count();
                $totalPublished  = $modules->sum(fn($m) => $m->lessons->where('status','published')->count())
                                 + $orphanedLessons->where('status','published')->count();
                $totalDraft      = $totalLessons - $totalPublished;
                $totalModules    = $modules->count();
                $archivedCount   = $modules->sum(fn($m) => $m->lessons->where('status','archived')->count())
                                 + $orphanedLessons->where('status','archived')->count();
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
            </div>

            @if($orphanedLessons->isNotEmpty())
            <div class="bg-amber-50/90 rounded-2xl p-4 border border-amber-200 mb-5">
                <div class="flex items-start gap-2.5">
                    <span class="material-symbols-outlined text-amber-600 text-[18px] mt-0.5 flex-shrink-0">warning</span>
                    <p class="text-[12px] text-amber-900 font-medium leading-relaxed">
                        <strong>{{ $orphanedLessons->count() }} lesson{{ $orphanedLessons->count() !== 1 ? 's are' : ' is' }}</strong> not assigned to any module. Assign them when you publish.
                    </p>
                </div>
            </div>
            @endif

            <button onclick="openNewLessonModal()"
                    class="w-full bg-gradient-to-r from-[#0d326b] via-[#1e4b8f] to-[#1a6fd4] hover:opacity-95 text-white py-3.5 rounded-2xl text-[13px] font-extrabold transition-all flex items-center justify-center gap-2 shadow-md hover:shadow-lg active:scale-[0.99]">
                <span class="material-symbols-outlined icon-outline text-[18px]">add</span>
                Create New Lesson
            </button>

            <div class="mt-4 pt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('analytics') }}" class="text-[12px] font-bold text-[#0d326b] hover:text-[#1a6fd4] transition-all flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[17px]">bar_chart</span>
                    View Analytics
                </a>
            </div>
        </div>
    </div><!-- /Right column -->

</div><!-- /flex wrapper -->

{{-- ── PAGINATION + MODAL SCRIPT ───────────────────────────────────────────── --}}
<script>
// ── CLIENT-SIDE PAGINATION (no reload) ──────────────────────────────────────
// All lessons for a module are already in the DOM (tagged with data-page).
// Changing page just shows/hides rows and updates the pagination controls —
// nothing is fetched from the server and the page never reloads.
const _currentPages = {};

function currentPageOf(moduleId) {
    return _currentPages[moduleId] || 1;
}

function changePage(moduleId, page) {
    const wrapper = document.getElementById('pagination-' + moduleId);
    if (!wrapper) return;

    const totalPages = parseInt(wrapper.dataset.totalPages, 10) || 1;
    const totalCount = parseInt(wrapper.dataset.totalCount, 10) || 0;
    const pageSize   = parseInt(wrapper.dataset.pageSize, 10) || 5;

    page = Math.max(1, Math.min(page, totalPages));
    _currentPages[moduleId] = page;
    sessionStorage.setItem('lessons_page_' + moduleId, page);

    // Show only the rows belonging to this page
    document.querySelectorAll('#lesson-tbody-' + moduleId + ' tr.lesson-row').forEach(function (row) {
        row.style.display = (parseInt(row.dataset.page, 10) === page) ? '' : 'none';
    });

    // Highlight the active page button
    for (let p = 1; p <= totalPages; p++) {
        const btn = document.getElementById('page-btn-' + moduleId + '-' + p);
        if (btn) btn.classList.toggle('active', p === page);
    }

    // Enable/disable prev & next
    const prevBtn = document.getElementById('prev-btn-' + moduleId);
    const nextBtn = document.getElementById('next-btn-' + moduleId);
    if (prevBtn) prevBtn.disabled = (page <= 1);
    if (nextBtn) nextBtn.disabled = (page >= totalPages);

    // Update the "Showing X–Y of Z" label
    const info = document.getElementById('pagination-info-' + moduleId);
    if (info) {
        const start = (page - 1) * pageSize + 1;
        const end = Math.min(page * pageSize, totalCount);
        info.textContent = 'Showing ' + start + '–' + end + ' of ' + totalCount + ' lessons';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Initialize every module's table to its saved (or default) page
    document.querySelectorAll('[id^="pagination-"]').forEach(function (wrapper) {
        const moduleId = wrapper.id.replace('pagination-', '');
        const saved = parseInt(sessionStorage.getItem('lessons_page_' + moduleId), 10) || 1;
        changePage(moduleId, saved);
    });

    // Initialize SortableJS drag & drop reordering for each module's lesson table
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

                    // Collect all lesson IDs in current DOM order for this module
                    const rows = Array.from(tbody.querySelectorAll('tr.lesson-row'));
                    const lessonIds = rows.map(r => r.dataset.lessonId).filter(Boolean);
                    const wrapper = document.getElementById('pagination-' + moduleId);
                    const pageSize = parseInt(wrapper?.dataset.pageSize || '5', 10);

                    // Update data-page on each row according to new positions
                    rows.forEach((row, idx) => {
                        row.dataset.page = Math.floor(idx / pageSize) + 1;
                    });

                    // Keep current page view consistent
                    const currentPage = currentPageOf(moduleId);
                    changePage(moduleId, currentPage);

                    // Send AJAX reorder request
                    fetch('{{ route("lessons.reorder") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                        },
                        body: JSON.stringify({
                            module_id: parseInt(moduleId, 10),
                            lesson_ids: lessonIds
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data && data.success) {
                            showToast('success', 'Lesson order updated successfully!');
                        } else {
                            showToast('error', data?.message || 'Failed to update lesson order.');
                        }
                    })
                    .catch(err => {
                        console.error('Reorder error:', err);
                        showToast('error', 'Network error while updating lesson order.');
                    });
                }
            });
        });
    }
});

// ── NEW LESSON MODAL ────────────────────────────────────────────────────────
function openNewLessonModal() {
    document.getElementById('newLessonModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeNewLessonModal() {
    document.getElementById('newLessonModal').classList.add('hidden');
    document.body.style.overflow = '';
}
function openManualCreate() {
    window.location.href = '{{ route('lessons.create') }}';
}
function openAiFromModal() {
    closeNewLessonModal();
    // Store the AI flag in sessionStorage instead of exposing it in the URL
    sessionStorage.setItem('lessons_open_ai', '1');
    window.location.href = '{{ route('lessons.create') }}';
}

document.addEventListener('click', function(e) {
    if (e.target === document.getElementById('newLessonModal')) closeNewLessonModal();
    if (e.target === document.getElementById('deleteModal')) closeDeleteModal();
    if (e.target === document.getElementById('studentsModal')) closeStudentsModal();
    if (e.target === document.getElementById('reorderLessonsModal')) closeReorderModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeNewLessonModal();
        closeDeleteModal();
        closeStudentsModal();
        closeReorderModal();
    }
});

let _deleteLessonId = null;
let _deleteLessonTitle = null;
let _selectedDeleteAction = null;

function confirmDelete(lessonId, lessonTitle) {
    _deleteLessonId = lessonId;
    _deleteLessonTitle = lessonTitle;
    _selectedDeleteAction = null;
    
    document.getElementById('deleteLessonTitle').textContent = lessonTitle;
    document.getElementById('selectedDeleteOption').textContent = 'Click an option above to select';
    document.getElementById('confirmDeleteBtn').textContent = 'Select an option';
    document.getElementById('confirmDeleteBtn').disabled = true;
    document.getElementById('confirmDeleteBtn').className = 'flex-1 py-3 bg-slate-300 text-white font-semibold rounded-2xl transition-colors cursor-not-allowed';
    
    // Reset option styles
    document.getElementById('deleteOptionSoft').className = 'p-4 rounded-2xl border-2 border-slate-200 hover:border-[#0d326b] transition cursor-pointer bg-white hover:bg-[#f8faff]';
    document.getElementById('deleteOptionHard').className = 'p-4 rounded-2xl border-2 border-slate-200 hover:border-red-500 transition cursor-pointer bg-white hover:bg-red-50/50';
    
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function selectDeleteOption(type) {
    _selectedDeleteAction = type;
    
    // Reset styles
    document.getElementById('deleteOptionSoft').className = 'p-4 rounded-2xl border-2 border-slate-200 transition cursor-pointer bg-white';
    document.getElementById('deleteOptionHard').className = 'p-4 rounded-2xl border-2 border-slate-200 transition cursor-pointer bg-white';
    
    if (type === 'soft') {
        document.getElementById('deleteOptionSoft').className = 'p-4 rounded-2xl border-2 border-[#0d326b] bg-blue-50/50 transition cursor-pointer';
        document.getElementById('selectedDeleteOption').textContent = '✅ Archive selected - Student data will be preserved';
        document.getElementById('selectedDeleteOption').className = 'text-center text-xs text-blue-600 font-bold mb-4';
    } else {
        document.getElementById('deleteOptionHard').className = 'p-4 rounded-2xl border-2 border-red-500 bg-red-50/50 transition cursor-pointer';
        document.getElementById('selectedDeleteOption').textContent = '⚠️ PERMANENT DELETE selected - All student data will be removed';
        document.getElementById('selectedDeleteOption').className = 'text-center text-xs text-red-600 font-bold mb-4';
    }
    
    // Enable confirm button
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
    btn.disabled = true;
    btn.textContent = 'Processing...';
    
    const url = _selectedDeleteAction === 'soft' 
        ? `/lessons/${_deleteLessonId}/soft-delete`
        : `/lessons/${_deleteLessonId}/hard-delete`;
    
    fetch(url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({})
    })
    .then(response => {
        // First check if response is JSON
        const contentType = response.headers.get('content-type');
        if (contentType && contentType.includes('application/json')) {
            return response.json().then(data => {
                if (!response.ok) {
                    throw new Error(data.message || 'Server error');
                }
                return data;
            });
        }
        // If not JSON, it might be a redirect
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        throw new Error('Unexpected response format');
    })
    .then(data => {
        if (data && data.success) {
            showToast('success', data.message || 'Operation completed');
            if (data.redirect) {
                window.location.href = data.redirect;
            } else {
                setTimeout(() => window.location.reload(), 1500);
            }
        } else {
            showToast('error', data?.message || 'Something went wrong');
            btn.disabled = false;
            btn.textContent = 'Try Again';
        }
    })
    .catch(error => {
        console.error('Delete error:', error);
        showToast('error', error.message || 'Network error. Please try again.');
        btn.disabled = false;
        btn.textContent = 'Try Again';
    });
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = '';
    _deleteLessonId = null;
    _deleteLessonTitle = null;
    _selectedDeleteAction = null;
}
// ── TOAST NOTIFICATION ──────────────────────────────────────────────────────
function showToast(type, message) {
    const toast = document.getElementById('toastNotification');
    const content = document.getElementById('toastContent');
    const icon = document.getElementById('toastIcon');
    const title = document.getElementById('toastTitle');
    const msg = document.getElementById('toastMessage');
    
    // Set colors based on type
    if (type === 'success') {
        content.className = 'rounded-2xl px-6 py-4 shadow-lg flex items-center gap-3 min-w-[280px] max-w-md bg-emerald-600';
        icon.textContent = '✅';
        title.textContent = 'Success';
    } else if (type === 'error') {
        content.className = 'rounded-2xl px-6 py-4 shadow-lg flex items-center gap-3 min-w-[280px] max-w-md bg-red-600';
        icon.textContent = '❌';
        title.textContent = 'Error';
    }
    
    msg.textContent = message;
    toast.classList.remove('hidden');
    
    // Auto-hide after 4 seconds
    clearTimeout(window.toastTimeout);
    window.toastTimeout = setTimeout(() => {
        toast.classList.add('hidden');
    }, 4000);
}



// ── STUDENTS MODAL ──────────────────────────────────────────────────────────
let _currentLessonId = null;
let _allStudents = [];

function openStudentsModal(lessonId, lessonTitle) {
    _currentLessonId = lessonId;
    document.getElementById('studentsModalSubtitle').textContent = lessonTitle;
    document.getElementById('studentList').classList.add('hidden');
    document.getElementById('studentListEmpty').classList.add('hidden');
    document.getElementById('studentListLoading').classList.remove('hidden');
    document.getElementById('studentSearchInput').value = '';
    document.getElementById('studentsModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    fetch('/lessons/' + lessonId + '/students', {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(r => r.json())
    .then(data => {
        _allStudents = data.students;
        renderStudentList(_allStudents);
        updateAssignedCount();
        document.getElementById('studentListLoading').classList.add('hidden');
    })
    .catch(() => {
        document.getElementById('studentListLoading').textContent = 'Failed to load students.';
    });
}

function closeStudentsModal() {
    document.getElementById('studentsModal').classList.add('hidden');
    document.body.style.overflow = '';
    _currentLessonId = null;
    _allStudents = [];
}

function renderStudentList(students) {
    const list = document.getElementById('studentList');
    const empty = document.getElementById('studentListEmpty');
    list.innerHTML = '';

    if (students.length === 0) {
        list.classList.add('hidden');
        empty.classList.remove('hidden');
        return;
    }
    empty.classList.add('hidden');
    list.classList.remove('hidden');

    students.forEach(s => {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-3 px-3 py-3 rounded-xl hover:bg-slate-50 transition-colors student-row';
        div.dataset.name = (s.first_name + ' ' + s.last_name).toLowerCase();
        div.dataset.lrn = (s.lrn || '').toLowerCase();

        const checked = s.assigned ? 'checked' : '';
        div.innerHTML = `
            <input type="checkbox" class="student-checkbox w-4 h-4 rounded accent-[#0d326b] flex-shrink-0 cursor-pointer"
                   data-id="${s.student_id}" ${checked} onchange="updateAssignedCount()">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-800 truncate">${s.first_name} ${s.last_name}</p>
                <p class="text-xs text-slate-400">
                    LRN: ${s.lrn || '—'}
                    ${s.grade_level ? ' · Grade ' + s.grade_level : ''}
                    ${s.section ? ' · ' + s.section : ''}
                    ${s.program ? ' · ' + ucFirst(s.program) : ''}
                    ${s.mastery_level ? ' · ' + ucFirst(s.mastery_level) : ''}
                </p>
            </div>
            <span class="text-xs px-2 py-0.5 rounded-full font-semibold flex-shrink-0 ${s.assigned ? 'bg-[#0d326b] text-white' : 'bg-slate-100 text-slate-500'}"
                  id="badge-${s.student_id}">
                ${s.assigned ? 'Assigned' : 'Not assigned'}
            </span>
        `;
        list.appendChild(div);
    });
}

function ucFirst(str) {
    return str ? str.charAt(0).toUpperCase() + str.slice(1) : '';
}

function updateAssignedCount() {
    const count = document.querySelectorAll('.student-checkbox:checked').length;
    document.getElementById('assignedCountLabel').textContent = count;
}

document.getElementById('studentSearchInput')?.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.student-row').forEach(row => {
        const match = row.dataset.name.includes(q) || row.dataset.lrn.includes(q);
        row.style.display = match ? '' : 'none';
    });
});

function selectAllVisible() {
    document.querySelectorAll('.student-row').forEach(row => {
        if (row.style.display !== 'none') {
            const cb = row.querySelector('.student-checkbox');
            if (cb) cb.checked = true;
        }
    });
    updateAssignedCount();
}

function deselectAllVisible() {
    document.querySelectorAll('.student-row').forEach(row => {
        if (row.style.display !== 'none') {
            const cb = row.querySelector('.student-checkbox');
            if (cb) cb.checked = false;
        }
    });
    updateAssignedCount();
}

function saveStudentAccess() {
    const btn = document.getElementById('saveStudentsBtn');
    const checked = Array.from(document.querySelectorAll('.student-checkbox:checked'))
                         .map(cb => parseInt(cb.dataset.id));

    btn.disabled = true;
    btn.textContent = 'Saving…';

    fetch('/lessons/' + _currentLessonId + '/students', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                          || '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        body: JSON.stringify({ student_ids: checked }),
    })
    .then(r => r.json())
    .then(data => {
        closeStudentsModal();
        // Reload to clean URL (no ?updated=1 param visible)
        window.location.replace(window.location.pathname);
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined icon-outline text-[16px]">save</span> Save Changes';
        alert('Something went wrong. Please try again.');
    });
}

// ── RESTORE LESSON ──────────────────────────────────────────────────────────
function restoreLesson(lessonId, lessonTitle) {
    if (!confirm(`Restore "${lessonTitle}"? This will make the lesson visible again.`)) return;
    
    const btn = event.target;
    btn.textContent = 'Restoring...';
    btn.disabled = true;
    
    fetch(`/lessons/${lessonId}/restore`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
            'Content-Type': 'application/json',
            'Accept': 'application/json',
        },
    })
    .then(response => {
        if (response.redirected) {
            window.location.href = response.url;
            return;
        }
        return response.json();
    })
    .then(data => {
        if (data && data.success) {
            showToast('success', 'Lesson restored successfully!');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast('error', data?.message || 'Failed to restore lesson');
            btn.textContent = 'Restore';
            btn.disabled = false;
        }
    })
    .catch(() => {
        showToast('error', 'Network error. Please try again.');
        btn.textContent = 'Restore';
        btn.disabled = false;
    });
}


</script>

{{-- ── NEW LESSON MODAL ────────────────────────────────────────────────────── --}}
<div id="newLessonModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl relative">
        <button onclick="closeNewLessonModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined text-2xl">close</span>
        </button>
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-[#0d326b]/10 flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-[#0d326b] text-[32px]">auto_awesome</span>
            </div>
            <h3 class="text-2xl font-bold text-[#0d326b]">Create New Lesson</h3>
            <p class="text-slate-500 text-sm mt-2">Choose how you want to create your lesson</p>
        </div>
        <div class="space-y-4">
            <button onclick="openManualCreate()"
                    class="w-full p-5 bg-white border-2 border-[#0d326b] rounded-2xl hover:bg-[#f0f4ff] transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-[#0d326b]/10 rounded-xl flex items-center justify-center text-[#0d326b]">
                        <span class="material-symbols-outlined text-2xl">edit_note</span>
                    </div>
                    <div class="text-left flex-1">
                        <p class="font-bold text-[#0d326b] text-base">Create Manually</p>
                        <p class="text-slate-500 text-sm">Build your lesson step by step</p>
                    </div>
                    <span class="text-[#0d326b] font-bold">→</span>
                </div>
            </button>
            <button onclick="openAiFromModal()"
                    class="w-full p-5 rounded-2xl hover:shadow-xl transition-all"
                    style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 55%, #1a6fd4 100%); border: none; cursor: pointer;">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl flex items-center justify-center text-white" style="background: rgba(255,255,255,0.2);">
                        <span class="material-symbols-outlined text-2xl">auto_awesome</span>
                    </div>
                    <div class="text-left flex-1 text-white">
                        <p class="font-bold text-base">AI Generate ✨</p>
                        <p class="text-sm" style="color: rgba(255,255,255,0.75);">Describe a topic — AI builds the full lesson</p>
                    </div>
                    <span class="text-white font-bold text-lg">→</span>
                </div>
            </button>
        </div>
        <button onclick="closeNewLessonModal()" class="mt-6 w-full py-3 text-slate-500 font-medium hover:text-slate-700">
            Cancel
        </button>
    </div>
</div>

{{-- ── DELETE CONFIRMATION MODAL (Enhanced) ──────────────────────────────── --}}
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
            <p class="text-slate-500 text-sm leading-relaxed" id="deleteDescription">
                Choose how you want to delete this lesson.
            </p>
        </div>

        <div class="space-y-3 mb-6">
            {{-- Option 1: Archive (Soft Delete) --}}
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
                        <p class="text-xs text-slate-500 mt-0.5">Lesson is hidden but student performances, quiz attempts, and analytics data are preserved.</p>
                        <p class="text-[10px] text-slate-400 mt-1">✓ Can be restored anytime · ✓ Analytics remain intact</p>
                    </div>
                </div>
            </div>

            {{-- Option 2: Hard Delete --}}
            <div class="p-4 rounded-2xl border-2 border-slate-200 hover:border-red-500 transition cursor-pointer bg-white hover:bg-red-50/50"
                 onclick="selectDeleteOption('hard')" id="deleteOptionHard">
                <div class="flex items-start gap-3">
                    <div class="w-10 h-10 rounded-xl bg-red-100 text-red-600 flex items-center justify-center flex-shrink-0">
                        <span class="material-symbols-outlined text-[22px]">delete_forever</span>
                    </div>
                    <div class="flex-1">
                        <h4 class="text-sm font-bold text-red-600">Permanently Delete (Remove All Data)</h4>
                        <p class="text-xs text-slate-500 mt-0.5">Deletes the lesson AND all student attempts, quiz scores, and progress data.</p>
                        <p class="text-[10px] text-red-500 font-semibold mt-1">⚠️ Cannot be undone · ❌ Analytics data will be lost</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Selected option indicator --}}
        <div id="selectedDeleteOption" class="text-center text-xs text-slate-400 font-medium mb-4">
            Click an option above to select
        </div>

        {{-- Action buttons --}}
        <div class="flex gap-3">
            <button type="button" onclick="closeDeleteModal()"
                    class="flex-1 py-3 border border-slate-200 rounded-2xl text-slate-600 font-semibold hover:bg-slate-50 transition-colors">
                Cancel
            </button>
            <button type="button" id="confirmDeleteBtn"
                    class="flex-1 py-3 bg-slate-300 text-white font-semibold rounded-2xl transition-colors cursor-not-allowed"
                    disabled>
                Select an option
            </button>
        </div>
    </div>
</div>

{{-- ── SUCCESS/ERROR TOAST ────────────────────────────────────────────────── --}}
<div id="toastNotification" class="fixed bottom-6 right-6 z-50 hidden">
    <div id="toastContent" class="rounded-2xl px-6 py-4 shadow-lg flex items-center gap-3 min-w-[280px] max-w-md">
        <span id="toastIcon" class="text-white text-[22px]">✅</span>
        <div>
            <p id="toastTitle" class="text-white font-bold text-sm">Success</p>
            <p id="toastMessage" class="text-white/80 text-xs">Operation completed</p>
        </div>
    </div>
</div>
{{-- ── STUDENTS MODAL ──────────────────────────────────────────────────────── --}}
<div id="studentsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl mx-4 flex flex-col" style="max-height:90vh;">
        <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100 flex-shrink-0">
            <div>
                <h3 class="text-xl font-bold text-[#0d326b]">Manage Student Access</h3>
                <p id="studentsModalSubtitle" class="text-sm text-slate-500 mt-0.5"></p>
            </div>
            <button onclick="closeStudentsModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>

        <div class="px-8 py-4 border-b border-slate-50 flex-shrink-0">
            <div class="relative">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                <input id="studentSearchInput" type="text" placeholder="Search by name or LRN…"
                       class="w-full pl-10 pr-4 py-2.5 border border-slate-200 rounded-xl text-sm focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">
            </div>
            <div class="flex items-center justify-between mt-3">
                <span class="text-xs text-slate-400 font-medium"><span id="assignedCountLabel">0</span> students assigned</span>
                <div class="flex gap-4">
                    <button type="button" onclick="selectAllVisible()" class="text-xs text-[#0d326b] font-bold hover:underline">Select all</button>
                    <button type="button" onclick="deselectAllVisible()" class="text-xs text-slate-500 font-bold hover:underline">Deselect all</button>
                </div>
            </div>
        </div>

        <div class="overflow-y-auto flex-1 px-8 py-3" id="studentListContainer">
            <div id="studentListLoading" class="py-10 text-center text-slate-400 text-sm">Loading students…</div>
            <div id="studentList" class="space-y-1 hidden"></div>
            <div id="studentListEmpty" class="py-10 text-center text-slate-400 text-sm hidden">No students found.</div>
        </div>

        <div class="px-8 py-5 border-t border-slate-100 flex items-center justify-between gap-3 flex-shrink-0">
            <button type="button" onclick="closeStudentsModal()"
                    class="px-6 py-2.5 border border-slate-200 rounded-xl text-slate-600 font-semibold text-sm hover:bg-slate-50 transition-colors">
                Cancel
            </button>
            <button type="button" onclick="saveStudentAccess()"
                    id="saveStudentsBtn"
                    class="px-8 py-2.5 bg-[#0d326b] hover:bg-[#154188] text-white font-semibold text-sm rounded-xl transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined text-[16px]">save</span>
                Save Changes
            </button>
        </div>
    </div>
</div>

{{-- ── LESSON PREVIEW MODAL ────────────────────────────────────────────────── --}}
<div id="lessonPreviewModal"
     style="display:none; position:fixed; inset:0; z-index:9999; overflow-y:auto; padding:24px 16px;"
     onclick="if(event.target===this) closeLessonPreviewModal()">
    {{-- Transparent backdrop --}}
    <div style="position:fixed; inset:0; background:rgba(10,20,50,0.55); backdrop-filter:blur(4px);"></div>

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

    {{-- Content container (transparent, no card wrapping) --}}
    <div id="lessonPreviewContent"
         style="position:relative; z-index:10000; max-width:900px; margin:0 auto; min-height:200px;">
        {{-- Loading state --}}
        <div id="lessonPreviewLoading"
             style="display:flex; align-items:center; justify-content:center;
                    height:260px; color:rgba(255,255,255,0.85); font-size:15px; font-weight:600; gap:10px;">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"
                 style="animation:spin .7s linear infinite;">
                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/>
            </svg>
            Loading preview…
        </div>
        <div id="lessonPreviewBody" style="display:none;"></div>
    </div>
</div>

<style>
@keyframes spin { to { transform: rotate(360deg); } }
</style>

<script>
function openLessonPreviewModal(url) {
    const modal   = document.getElementById('lessonPreviewModal');
    const loading = document.getElementById('lessonPreviewLoading');
    const body    = document.getElementById('lessonPreviewBody');

    // Reset state
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

            // Re-run any inline scripts injected by the preview partial
            body.querySelectorAll('script').forEach(oldScript => {
                const s = document.createElement('script');
                if (oldScript.src) {
                    s.src = oldScript.src;
                } else {
                    s.textContent = oldScript.textContent;
                }
                document.head.appendChild(s);
                oldScript.remove();
            });
        })
        .catch(err => {
            loading.innerHTML = '<span style="color:#fca5a5;">⚠ Failed to load preview. Please try again.</span>';
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
    if (e.key === 'Escape') {
        closeLessonPreviewModal();
        closeExamChoiceModal();
    }
});
</script>

{{-- Create Exam Choice Modal --}}
<div id="examChoiceModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl space-y-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="text-base font-bold text-[#0d326b] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#8b5cf6]">assignment_add</span>
                    Create Checkpoint Exam
                </h3>
                <p class="text-xs text-slate-400 font-medium mt-0.5" id="modalModuleTitle">Module</p>
            </div>
            <button onclick="closeExamChoiceModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <div id="examRequirementWarning" class="hidden p-3.5 rounded-xl bg-amber-50 border border-amber-200 text-amber-800 text-xs font-medium flex items-start gap-2">
            <span class="material-symbols-outlined text-amber-600 text-[18px] shrink-0 mt-0.5">warning</span>
            <div>
                <span class="font-bold block mb-0.5">At least 2 new published lessons required!</span>
                You need at least 2 published lessons with quizzes in this module that haven't been included in a checkpoint exam yet before creating a new exam.
            </div>
        </div>

        <div class="space-y-3">
            <!-- Choice 1: Choose from previous questions -->
            <div id="choicePrevQuestions" class="p-4 rounded-xl border border-slate-200 hover:border-purple-300 bg-white hover:bg-purple-50/50 transition cursor-pointer flex items-center justify-between group"
                 onclick="proceedWithChoice('create')">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-[22px]">checklist</span>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-[#0d326b] group-hover:text-purple-700 transition">Choose from Previous Lessons & Quizzes</h4>
                        <p class="text-xs text-slate-400 font-medium">Select questions from published lessons in this module and customize point values.</p>
                    </div>
                </div>
                <span class="material-symbols-outlined text-slate-300 group-hover:text-purple-600 text-[20px] transition">chevron_right</span>
            </div>

        </div>
    </div>
</div>

<script>
let currentSelectedModuleId = null;

function openExamChoiceModal(moduleId, moduleTitle, canCreate, availableCount) {
    currentSelectedModuleId = moduleId;
    document.getElementById('modalModuleTitle').textContent = `Module: ${moduleTitle}`;
    const warning = document.getElementById('examRequirementWarning');
    const prevChoice = document.getElementById('choicePrevQuestions');

    if (!canCreate) {
        warning.classList.remove('hidden');
        prevChoice.classList.add('opacity-50', 'pointer-events-none');
    } else {
        warning.classList.add('hidden');
        prevChoice.classList.remove('opacity-50', 'pointer-events-none');
    }

    document.getElementById('examChoiceModal').classList.remove('hidden');
}

function closeExamChoiceModal() {
    document.getElementById('examChoiceModal').classList.add('hidden');
}

function proceedWithChoice(type) {
    if (type === 'create' && currentSelectedModuleId) {
        window.location.href = `{{ url('/lessons/checkpoint-exam/create') }}?module_id=${currentSelectedModuleId}`;
    }
}

// ── CONNECT DELETE CONFIRM BUTTON ─────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    const confirmBtn = document.getElementById('confirmDeleteBtn');
    if (confirmBtn) {
        // Remove any existing listeners to avoid duplicates
        const newBtn = confirmBtn.cloneNode(true);
        confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);
        newBtn.addEventListener('click', executeDelete);
    }
});

</script>

{{-- Edit Module Modal --}}
<div id="editModuleModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl space-y-5">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="text-base font-bold text-[#0d326b] flex items-center gap-2">
                <span class="material-symbols-outlined text-[#1a6fd4]">edit</span>
                Edit Module
            </h3>
            <button onclick="closeEditModuleModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <form id="editModuleForm" class="space-y-4">
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Title</label>
                <input type="text" id="editModuleTitle" name="title" maxlength="255" required
                       class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a6fd4]/30 focus:border-[#1a6fd4]">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Description</label>
                <textarea id="editModuleDescription" name="description" maxlength="1000" rows="3"
                          class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a6fd4]/30 focus:border-[#1a6fd4]"></textarea>
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-500 uppercase tracking-wide mb-1.5">Mastery Level</label>
                <select id="editModuleMasteryLevel" name="mastery_level"
                        class="w-full border border-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-[#1a6fd4]/30 focus:border-[#1a6fd4]">
                    <option value="beginner">🌱 Beginner</option>
                    <option value="intermediate">⚡ Intermediate</option>
                    <option value="advanced">🔥 Advanced</option>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">This applies to all lessons inside this module.</p>
            </div>

            <div id="editModuleError" class="hidden text-xs font-medium text-red-600"></div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" onclick="closeEditModuleModal()"
                        class="px-4 py-2 rounded-lg text-sm font-semibold text-slate-500 hover:bg-slate-100">
                    Cancel
                </button>
                <button type="submit" id="editModuleSaveBtn"
                        class="px-4 py-2 rounded-lg text-sm font-semibold text-white bg-[#0d326b] hover:bg-[#1a6fd4] transition">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let currentEditModuleId = null;

function openEditModuleModal(moduleId, title, description, masteryLevel) {
    currentEditModuleId = moduleId;
    document.getElementById('editModuleTitle').value = title;
    document.getElementById('editModuleDescription').value = description;
    document.getElementById('editModuleMasteryLevel').value = masteryLevel;
    document.getElementById('editModuleError').classList.add('hidden');
    document.getElementById('editModuleModal').classList.remove('hidden');
}

function closeEditModuleModal() {
    document.getElementById('editModuleModal').classList.add('hidden');
    currentEditModuleId = null;
}

document.getElementById('editModuleForm').addEventListener('submit', function (e) {
    e.preventDefault();
    if (!currentEditModuleId) return;

    const saveBtn = document.getElementById('editModuleSaveBtn');
    const errorBox = document.getElementById('editModuleError');
    errorBox.classList.add('hidden');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}';

    fetch(`/modules/${currentEditModuleId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
        },
        body: JSON.stringify({
            title: document.getElementById('editModuleTitle').value,
            description: document.getElementById('editModuleDescription').value,
            mastery_level: document.getElementById('editModuleMasteryLevel').value,
        }),
    })
        .then(async r => {
            const data = await r.json().catch(() => ({}));
            if (!r.ok || !data.success) {
                throw new Error(data.message || 'Failed to update module.');
            }
            return data;
        })
        .then(() => {
            window.location.reload();
        })
        .catch(err => {
            errorBox.textContent = err.message;
            errorBox.classList.remove('hidden');
            saveBtn.disabled = false;
            saveBtn.textContent = 'Save Changes';
        });
});
</script>

{{-- ── REORDER LESSONS MODAL (CROSS-PAGE REORDERING) ──────────────────────── --}}
<div id="reorderLessonsModal" class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 flex flex-col max-h-[85vh]">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 flex-shrink-0">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-xl bg-blue-50 text-[#0d326b] flex items-center justify-center">
                    <span class="material-symbols-outlined text-[20px]">swap_vert</span>
                </div>
                <div>
                    <h3 class="text-base font-bold text-[#0d326b]">Reorder Lessons</h3>
                    <p id="reorderModalSubtitle" class="text-xs text-slate-400 font-medium"></p>
                </div>
            </div>
            <button onclick="closeReorderModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <p class="text-xs text-slate-500 flex-shrink-0">
            Drag items using the handle <span class="material-symbols-outlined text-[14px] align-middle text-slate-400">drag_indicator</span> or click the arrows to arrange the learning sequence for your students.
        </p>

        <div class="overflow-y-auto flex-1 space-y-2 pr-1" id="reorderListContainer">
            <!-- Dynamic lesson items -->
        </div>

        <div class="flex items-center justify-between border-t border-slate-100 pt-3 flex-shrink-0">
            <button type="button" onclick="closeReorderModal()" class="px-4 py-2 text-xs font-bold text-slate-500 hover:bg-slate-100 rounded-xl transition">
                Cancel
            </button>
            <button type="button" id="saveReorderBtn" onclick="saveModalReorder()" class="px-5 py-2.5 bg-[#0d326b] hover:bg-[#1a6fd4] text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shadow-md">
                <span class="material-symbols-outlined text-[16px]">save</span>
                Save Sequence
            </button>
        </div>
    </div>
</div>

<script>
let currentReorderModuleId = null;
let modalSortableInstance = null;

function openReorderModal(moduleId, moduleTitle) {
    currentReorderModuleId = moduleId;
    document.getElementById('reorderModalSubtitle').textContent = `Module: ${moduleTitle}`;
    
    const tbody = document.getElementById(`lesson-tbody-${moduleId}`);
    const container = document.getElementById('reorderListContainer');
    container.innerHTML = '';

    if (!tbody) return;

    const rows = Array.from(tbody.querySelectorAll('tr.lesson-row'));
    if (rows.length === 0) {
        container.innerHTML = '<p class="text-xs text-slate-400 text-center py-6">No lessons to reorder.</p>';
    } else {
        rows.forEach((row, idx) => {
            const lessonId = row.dataset.lessonId;
            const title = row.querySelector('.lesson-title-cell')?.textContent.trim() || 'Untitled Lesson';
            const diffEl = row.querySelector('.badge-difficulty');
            const statusEl = row.querySelector('.badge-status');

            const item = document.createElement('div');
            item.className = 'reorder-item flex items-center justify-between gap-3 p-3 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-blue-300 transition-colors';
            item.dataset.lessonId = lessonId;

            item.innerHTML = `
                <div class="flex items-center gap-3 min-w-0">
                    <span class="reorder-num w-6 h-6 rounded-full bg-[#0d326b] text-white text-[11px] font-black flex items-center justify-center shrink-0">
                        ${idx + 1}
                    </span>
                    <div class="min-w-0">
                        <p class="text-[13px] font-bold text-[#0d326b] truncate">${title}</p>
                        <div class="flex items-center gap-1.5 mt-0.5">
                            ${diffEl ? `<span class="text-[9px] font-bold px-2 py-0.5 rounded-full ${diffEl.className}">${diffEl.textContent.trim()}</span>` : ''}
                            ${statusEl ? `<span class="text-[9px] font-bold px-2 py-0.5 rounded-full ${statusEl.className}">${statusEl.textContent.trim()}</span>` : ''}
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-1 shrink-0">
                    <button type="button" onclick="moveModalItem(this, -1)" class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-[#0d326b] hover:border-blue-300 flex items-center justify-center transition" title="Move Up">
                        <span class="material-symbols-outlined text-[16px]">arrow_upward</span>
                    </button>
                    <button type="button" onclick="moveModalItem(this, 1)" class="w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-500 hover:text-[#0d326b] hover:border-blue-300 flex items-center justify-center transition" title="Move Down">
                        <span class="material-symbols-outlined text-[16px]">arrow_downward</span>
                    </button>
                    <div class="reorder-modal-handle w-7 h-7 rounded-lg bg-white border border-slate-200 text-slate-400 hover:text-[#0d326b] flex items-center justify-center cursor-grab active:cursor-grabbing ml-1" title="Drag to reorder">
                        <span class="material-symbols-outlined text-[16px]">drag_indicator</span>
                    </div>
                </div>
            `;

            container.appendChild(item);
        });

        // Initialize Sortable on modal list
        if (typeof Sortable !== 'undefined') {
            if (modalSortableInstance) modalSortableInstance.destroy();
            modalSortableInstance = new Sortable(container, {
                handle: '.reorder-modal-handle',
                animation: 200,
                ghostClass: 'sortable-ghost',
                chosenClass: 'sortable-chosen',
                onEnd: function() {
                    updateModalSequenceNumbers();
                }
            });
        }
    }

    document.getElementById('reorderLessonsModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeReorderModal() {
    document.getElementById('reorderLessonsModal').classList.add('hidden');
    document.body.style.overflow = '';
    currentReorderModuleId = null;
}

function moveModalItem(btn, direction) {
    const item = btn.closest('.reorder-item');
    if (!item) return;
    const container = document.getElementById('reorderListContainer');

    if (direction === -1 && item.previousElementSibling) {
        container.insertBefore(item, item.previousElementSibling);
    } else if (direction === 1 && item.nextElementSibling) {
        container.insertBefore(item.nextElementSibling, item);
    }
    updateModalSequenceNumbers();
}

function updateModalSequenceNumbers() {
    const container = document.getElementById('reorderListContainer');
    const items = container.querySelectorAll('.reorder-item');
    items.forEach((item, idx) => {
        const numBadge = item.querySelector('.reorder-num');
        if (numBadge) numBadge.textContent = idx + 1;
    });
}

function saveModalReorder() {
    if (!currentReorderModuleId) return;

    const container = document.getElementById('reorderListContainer');
    const items = Array.from(container.querySelectorAll('.reorder-item'));
    const lessonIds = items.map(it => it.dataset.lessonId).filter(Boolean);

    if (lessonIds.length === 0) {
        closeReorderModal();
        return;
    }

    const saveBtn = document.getElementById('saveReorderBtn');
    saveBtn.disabled = true;
    saveBtn.textContent = 'Saving...';

    fetch('{{ route("lessons.reorder") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
        },
        body: JSON.stringify({
            module_id: parseInt(currentReorderModuleId, 10),
            lesson_ids: lessonIds
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data && data.success) {
            closeReorderModal();
            showToast('success', 'Lesson order updated successfully!');
            setTimeout(() => window.location.reload(), 800);
        } else {
            showToast('error', data?.message || 'Failed to update lesson order.');
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<span class="material-symbols-outlined text-[16px]">save</span> Save Sequence';
        }
    })
    .catch(err => {
        console.error('Reorder error:', err);
        showToast('error', 'Network error while saving order.');
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<span class="material-symbols-outlined text-[16px]">save</span> Save Sequence';
    });
}
</script>

@endsection