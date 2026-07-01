@extends('layouts.app')
@section('title', 'Lessons')
@section('content')


@if(session('success'))
<div class="mb-6 rounded-xl border border-green-200 bg-green-50 px-5 py-4 text-sm font-semibold text-green-700 flex items-center gap-2">
    <span class="material-symbols-outlined icon-outline text-[18px]">check_circle</span>
    {{ session('success') }}
</div>
@endif

<div class="flex flex-col lg:flex-row gap-8">

    <!-- Left Side: Modules + Lessons -->
    <div class="flex-1 flex flex-col space-y-10">

        @php
            $moduleColors = ['#0d326b','#2563EB','#059669','#D97706','#7C3AED','#DB2777','#0891B2'];
            $moduleIcons  = ['ABC','123','🖐️','💬','📖','🌟','🔤'];
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
        @endphp
        <div>
            <!-- Module badge tab -->
            <div class="inline-block text-white text-[10px] font-bold tracking-[0.15em] px-5 py-2.5 uppercase"
                style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 50%, #1a6fd4 100%); clip-path: polygon(0 0, 100% 0, 88% 100%, 0% 100%); min-width:9rem;">
                MODULE {{ $padNum }}
            </div>

            <div class="bg-white rounded-[24px] rounded-tl-none shadow-sm p-8 border border-slate-100">
                <!-- Module header -->
                <div class="flex justify-between items-start mb-8">
                    <div class="flex items-center space-x-5">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center font-black text-xl"
                             style="background:{{ $modColor }}1A; color:{{ $modColor }};">
                            {{ is_string($modIcon) ? $modIcon : $modIcon }}
                        </div>
                        <div>
                            <h3 class="text-[20px] font-bold text-[#0d326b] mb-1">{{ $module->title }}</h3>
                            @if($module->description)
                            <p class="text-[13px] text-slate-500 font-medium">{{ $module->description }}</p>
                            @endif
                            <p class="text-[11px] text-slate-400 mt-1">{{ $lessonCount }} lesson{{ $lessonCount !== 1 ? 's' : '' }}</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-0.5">Published</p>
                            <p class="text-[18px] font-bold" style="color:{{ $modColor }};">{{ $progress }}%</p>
                        </div>
                        <button onclick="toggleModule('module-{{ $module->module_id }}')"
                                class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center hover:bg-slate-200 transition-colors text-slate-600"
                                id="toggle-btn-{{ $module->module_id }}">
                            <span class="material-symbols-outlined icon-outline">expand_more</span>
                        </button>
                    </div>
                </div>

                <!-- Lessons table -->
                <div id="module-{{ $module->module_id }}">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="py-4 px-2 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase w-8"></th>
                                <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase">Lesson Title</th>
                                <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase">Type</th>
                                <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase">Difficulty</th>
                                <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase">Status</th>
                                <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50">
                            @forelse($module->lessons as $lesson)
                            <tr class="hover:bg-slate-50 transition-colors cursor-pointer group"
                                onclick="window.location.href='{{ route('lessons.view', $lesson->lesson_id) }}'">
                                <td class="py-4 px-2 text-slate-300 cursor-move" onclick="event.stopPropagation();">
                                    <span class="material-symbols-outlined icon-outline text-lg">drag_indicator</span>
                                </td>
                                <td class="py-4 px-4 font-bold text-[#0d326b] text-[14px]">{{ $lesson->title }}</td>
                                <td class="py-4 px-4">
                                    <span class="px-3 py-1 text-[9px] font-bold rounded uppercase tracking-widest bg-blue-50 text-blue-700">{{ $lesson->lesson_type }}</span>
                                </td>
                                <td class="py-4 px-4">
                                    @php
                                        $diffColor = ['beginner'=>'bg-green-100 text-green-700','intermediate'=>'bg-yellow-100 text-yellow-700','advanced'=>'bg-red-100 text-red-700'];
                                    @endphp
                                    <span class="px-3 py-1 text-[9px] font-bold rounded uppercase tracking-widest {{ $diffColor[$lesson->difficulty] ?? 'bg-slate-100 text-slate-600' }}">{{ $lesson->difficulty }}</span>
                                </td>
                                <td class="py-4 px-4">
                                    <span class="px-3 py-1 text-[10px] font-bold rounded uppercase tracking-widest
                                        {{ $lesson->status === 'published' ? 'bg-green-100 text-green-700' : ($lesson->status === 'archived' ? 'bg-slate-100 text-slate-500' : 'bg-yellow-100 text-yellow-700') }}">
                                        {{ $lesson->status }}
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-right" onclick="event.stopPropagation();">
                                    <div class="flex items-center justify-end gap-3">
                                        <a href="{{ route('lessons.view', $lesson->lesson_id) }}"
                                           class="text-slate-500 hover:text-[#0d326b] text-sm font-semibold transition-colors">View</a>
                                        <a href="{{ route('lessons.edit', $lesson->lesson_id) }}"
                                           class="text-slate-500 hover:text-[#0d326b] text-sm font-semibold transition-colors">Edit</a>
                                        @if($lesson->status === 'draft')
                                        <a href="{{ route('lessons.publish.config', $lesson->lesson_id) }}"
                                           class="text-blue-600 hover:text-blue-800 text-sm font-semibold transition-colors">Publish</a>
                                        @endif
                                        @if($lesson->status === 'published')
                                        <button onclick="openStudentsModal({{ $lesson->lesson_id }}, '{{ addslashes($lesson->title) }}')"
                                                class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold transition-colors">
                                            Students
                                        </button>
                                        @endif
                                        <button onclick="confirmDelete({{ $lesson->lesson_id }}, '{{ addslashes($lesson->title) }}')"
                                                class="text-red-500 hover:text-red-700 text-sm font-semibold transition-colors">
                                            Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-slate-400 text-sm">
                                    No lessons in this module yet.
                                    <a href="{{ route('lessons.create') }}" class="text-[#0d326b] font-semibold hover:underline ml-1">Create one →</a>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div><!-- /#module-{{ $module->module_id }} -->

                <div class="mt-6 pt-6 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400 font-medium">{{ $lessonCount }} lesson{{ $lessonCount !== 1 ? 's' : '' }} · {{ $published }} published</span>
                    <a href="{{ route('lessons.create') }}"
                       class="flex items-center space-x-1 text-[#0d326b] font-bold text-[13px] hover:underline">
                        <span class="material-symbols-outlined icon-outline text-[16px]">add</span>
                        <span>Add Lesson to this Module</span>
                    </a>
                </div>
            </div>
        </div>
        @empty
        {{-- No modules yet --}}
        @endforelse

        {{-- ── Orphaned Lessons (no module) ─────────────────────────────── --}}
        @if($orphanedLessons->isNotEmpty())
        <div>
            <div class="inline-block bg-slate-400 text-white text-[10px] font-bold tracking-[0.15em] px-5 py-2.5 uppercase"
                 style="clip-path: polygon(0 0, 100% 0, 88% 100%, 0% 100%); min-width:9rem;">
                UNASSIGNED
            </div>
            <div class="bg-white rounded-[24px] rounded-tl-none shadow-sm p-8 border border-slate-100">
                <div class="flex items-center space-x-4 mb-6">
                    <div class="w-12 h-12 bg-slate-100 rounded-2xl flex items-center justify-center text-slate-500">
                        <span class="material-symbols-outlined icon-outline text-2xl">folder_off</span>
                    </div>
                    <div>
                        <h3 class="text-[18px] font-bold text-slate-600">Lessons Without a Module</h3>
                        <p class="text-[12px] text-slate-400">These lessons haven't been assigned to a module yet. Assign them when publishing.</p>
                    </div>
                </div>
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100">
                            <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase">Lesson Title</th>
                            <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase">Type</th>
                            <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase">Difficulty</th>
                            <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase">Status</th>
                            <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        @foreach($orphanedLessons as $lesson)
                        <tr class="hover:bg-slate-50 transition-colors cursor-pointer"
                            onclick="window.location.href='{{ route('lessons.view', $lesson->lesson_id) }}'">
                            <td class="py-4 px-4 font-bold text-[#0d326b] text-[14px]">{{ $lesson->title }}</td>
                            <td class="py-4 px-4">
                                <span class="px-3 py-1 text-[9px] font-bold rounded uppercase tracking-widest bg-blue-50 text-blue-700">{{ $lesson->lesson_type }}</span>
                            </td>
                            <td class="py-4 px-4">
                                @php $diffColor = ['beginner'=>'bg-green-100 text-green-700','intermediate'=>'bg-yellow-100 text-yellow-700','advanced'=>'bg-red-100 text-red-700']; @endphp
                                <span class="px-3 py-1 text-[9px] font-bold rounded uppercase tracking-widest {{ $diffColor[$lesson->difficulty] ?? 'bg-slate-100 text-slate-600' }}">{{ $lesson->difficulty }}</span>
                            </td>
                            <td class="py-4 px-4">
                                <span class="px-3 py-1 text-[10px] font-bold rounded uppercase tracking-widest
                                    {{ $lesson->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $lesson->status }}
                                </span>
                            </td>
                            <td class="py-4 px-4 text-right" onclick="event.stopPropagation();">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('lessons.view', $lesson->lesson_id) }}" class="text-slate-500 hover:text-[#0d326b] text-sm font-semibold">View</a>
                                    <a href="{{ route('lessons.edit', $lesson->lesson_id) }}" class="text-slate-500 hover:text-[#0d326b] text-sm font-semibold">Edit</a>
                                    <a href="{{ route('lessons.publish.config', $lesson->lesson_id) }}" class="text-blue-600 hover:text-blue-800 text-sm font-semibold">Assign & Publish</a>
                                    @if($lesson->status === 'published')
                                    <button onclick="openStudentsModal({{ $lesson->lesson_id }}, '{{ addslashes($lesson->title) }}')"
                                            class="text-indigo-600 hover:text-indigo-800 text-sm font-semibold transition-colors">
                                        Students
                                    </button>
                                    @endif
                                    <button onclick="confirmDelete({{ $lesson->lesson_id }}, '{{ addslashes($lesson->title) }}')"
                                            class="text-red-500 hover:text-red-700 text-sm font-semibold transition-colors">
                                        Delete
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- ── Empty state when no modules AND no orphaned lessons ─────────── --}}
        @if($modules->isEmpty() && $orphanedLessons->isEmpty())
        <div class="bg-white rounded-[24px] shadow-sm p-16 border border-slate-100 text-center">
            <div class="w-20 h-20 bg-[#0d326b]/08 rounded-3xl flex items-center justify-center mx-auto mb-6">
                <span class="material-symbols-outlined icon-outline text-4xl text-[#0d326b]">menu_book</span>
            </div>
            <h3 class="text-[22px] font-bold text-[#0d326b] mb-2">No lessons yet</h3>
            <p class="text-slate-500 text-sm mb-6 max-w-md mx-auto">Create your first lesson. You can organise it into a module right away or assign a module when you publish.</p>
            <button onclick="openNewLessonModal()"
                    class="bg-[#0d326b] hover:bg-[#154188] text-white px-8 py-3 rounded-xl text-[14px] font-semibold transition-colors inline-flex items-center gap-2">
                <span class="material-symbols-outlined icon-outline text-[18px]">add</span>
                Create Your First Lesson
            </button>
        </div>
        @endif

    </div><!-- /Left column -->

    <!-- Right Side: Insights panel -->
    <div class="w-[300px] flex-shrink-0 flex flex-col space-y-6">
        <div class="bg-white rounded-[28px] p-7 shadow-sm border border-slate-50">
            <div class="flex items-center space-x-2 mb-5">
                <span class="material-symbols-outlined icon-outline text-[20px] text-[#0d326b]">auto_awesome</span>
                <span class="text-[15px] font-bold text-[#0d326b]">Lesson Insights</span>
            </div>

            @php
                $totalLessons    = $modules->sum(fn($m) => $m->lessons->count()) + $orphanedLessons->count();
                $totalPublished  = $modules->sum(fn($m) => $m->lessons->where('status','published')->count())
                                 + $orphanedLessons->where('status','published')->count();
                $totalDraft      = $totalLessons - $totalPublished;
                $totalModules    = $modules->count();
            @endphp

            <div class="space-y-3 mb-6">
                <div class="flex justify-between items-center bg-slate-50 rounded-2xl px-4 py-3">
                    <span class="text-[12px] font-semibold text-slate-500">Total Modules</span>
                    <span class="text-[18px] font-black text-[#0d326b]">{{ $totalModules }}</span>
                </div>
                <div class="flex justify-between items-center bg-slate-50 rounded-2xl px-4 py-3">
                    <span class="text-[12px] font-semibold text-slate-500">Total Lessons</span>
                    <span class="text-[18px] font-black text-[#0d326b]">{{ $totalLessons }}</span>
                </div>
                <div class="flex justify-between items-center bg-green-50 rounded-2xl px-4 py-3">
                    <span class="text-[12px] font-semibold text-green-700">Published</span>
                    <span class="text-[18px] font-black text-green-700">{{ $totalPublished }}</span>
                </div>
                <div class="flex justify-between items-center bg-yellow-50 rounded-2xl px-4 py-3">
                    <span class="text-[12px] font-semibold text-yellow-700">Drafts</span>
                    <span class="text-[18px] font-black text-yellow-700">{{ $totalDraft }}</span>
                </div>
                @if($orphanedLessons->isNotEmpty())
                <div class="flex justify-between items-center bg-red-50 rounded-2xl px-4 py-3">
                    <span class="text-[12px] font-semibold text-red-600">Unassigned</span>
                    <span class="text-[18px] font-black text-red-600">{{ $orphanedLessons->count() }}</span>
                </div>
                @endif
            </div>

            @if($orphanedLessons->isNotEmpty())
            <div class="bg-amber-50 rounded-2xl p-4 border border-amber-100 mb-4">
                <div class="flex items-start gap-2">
                    <span class="material-symbols-outlined icon-outline text-amber-500 text-[18px] mt-0.5">warning</span>
                    <p class="text-[12px] text-amber-800 font-medium leading-relaxed">
                        <strong>{{ $orphanedLessons->count() }} lesson{{ $orphanedLessons->count() !== 1 ? 's are' : ' is' }}</strong> not assigned to any module. Assign them when you publish.
                    </p>
                </div>
            </div>
            @endif

            <button onclick="openNewLessonModal()"
                    class="w-full bg-gradient-to-r from-[#0d326b] via-[#1e4b8f] to-[#1a6fd4] hover:opacity-90 text-white py-3 rounded-2xl text-[13px] font-bold transition-colors flex items-center justify-center gap-2">
                <span class="material-symbols-outlined icon-outline text-[16px]">add</span>
                New Lesson
            </button>
        </div>
    </div><!-- /Right column -->

</div><!-- /flex wrapper -->

<!-- ── NEW LESSON MODAL ──────────────────────────────────────────────────── -->
<div id="newLessonModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl relative">
        <button onclick="closeNewLessonModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined text-2xl">close</span>
        </button>
        <div class="text-center mb-8">
            <h3 class="text-2xl font-bold text-[#0d326b]">Create New Lesson</h3>
            <p class="text-slate-500 text-sm mt-2">Choose how you want to create your lesson</p>
        </div>
        <div class="space-y-4">
            <button onclick="openManualCreate()"
                    class="w-full p-6 bg-white border-2 border-[#0d326b] rounded-2xl hover:bg-[#f0f4ff] transition-all group">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-[#0d326b]/10 rounded-2xl flex items-center justify-center text-[#0d326b]">
                        <span class="material-symbols-outlined text-3xl">edit_note</span>
                    </div>
                    <div class="text-left">
                        <p class="font-bold text-[#0d326b] text-lg">Create Manually</p>
                        <p class="text-slate-500 text-sm">Build your lesson step by step</p>
                    </div>
                    <span class="ml-auto text-[#0d326b]">→</span>
                </div>
            </button>
            <button onclick="alert('Coming soon! AI lesson generation will be available in the next update.')"
                    class="w-full p-6 bg-gradient-to-r from-[#0d326b] to-[#2563EB] rounded-2xl hover:shadow-xl transition-all opacity-50 cursor-not-allowed">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center text-white">
                        <span class="material-symbols-outlined text-3xl">auto_awesome</span>
                    </div>
                    <div class="text-left text-white">
                        <p class="font-bold text-lg">AI Generate ✨</p>
                        <p class="text-white/70 text-sm">Upload PDF or notes for automatic generation</p>
                    </div>
                    <span class="ml-auto text-white/50">Soon</span>
                </div>
            </button>
        </div>
        <button onclick="closeNewLessonModal()" class="mt-6 w-full py-3 text-slate-500 font-medium hover:text-slate-700">
            Cancel
        </button>
    </div>
</div>

<!-- ── DELETE CONFIRMATION MODAL ────────────────────────────────────────── -->
<div id="deleteModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-3xl p-8 max-w-sm w-full mx-4 shadow-2xl relative">
        <div class="text-center mb-6">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="material-symbols-outlined text-red-500 text-3xl">delete_forever</span>
            </div>
            <h3 class="text-xl font-bold text-slate-800 mb-2">Delete Lesson?</h3>
            <p class="text-slate-500 text-sm leading-relaxed">
                You're about to delete <strong id="deleteLessonTitle" class="text-slate-700"></strong>. This will also remove all its content, quiz questions, and student assignments. This cannot be undone.
            </p>
        </div>
        <form id="deleteForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="flex gap-3">
                <button type="button" onclick="closeDeleteModal()"
                        class="flex-1 py-3 border border-slate-200 rounded-2xl text-slate-600 font-semibold hover:bg-slate-50 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-2xl transition-colors">
                    Yes, Delete
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ── EDIT STUDENTS MODAL ───────────────────────────────────────────────── -->
<div id="studentsModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl mx-4 flex flex-col" style="max-height:90vh;">
        <!-- Header -->
        <div class="flex items-center justify-between px-8 py-6 border-b border-slate-100">
            <div>
                <h3 class="text-xl font-bold text-[#0d326b]">Manage Student Access</h3>
                <p id="studentsModalSubtitle" class="text-sm text-slate-500 mt-0.5"></p>
            </div>
            <button onclick="closeStudentsModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-2xl">close</span>
            </button>
        </div>

        <!-- Search + Select All -->
        <div class="px-8 py-4 border-b border-slate-50">
            <div class="relative">
                <span class="material-symbols-outlined icon-outline absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
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

        <!-- Student list -->
        <div class="overflow-y-auto flex-1 px-8 py-3" id="studentListContainer">
            <div id="studentListLoading" class="py-10 text-center text-slate-400 text-sm">Loading students…</div>
            <div id="studentList" class="space-y-1 hidden"></div>
            <div id="studentListEmpty" class="py-10 text-center text-slate-400 text-sm hidden">No students found.</div>
        </div>

        <!-- Footer -->
        <div class="px-8 py-5 border-t border-slate-100 flex items-center justify-between gap-3">
            <button type="button" onclick="closeStudentsModal()"
                    class="px-6 py-2.5 border border-slate-200 rounded-xl text-slate-600 font-semibold text-sm hover:bg-slate-50 transition-colors">
                Cancel
            </button>
            <button type="button" onclick="saveStudentAccess()"
                    id="saveStudentsBtn"
                    class="px-8 py-2.5 bg-[#0d326b] hover:bg-[#154188] text-white font-semibold text-sm rounded-xl transition-colors flex items-center gap-2">
                <span class="material-symbols-outlined icon-outline text-[16px]">save</span>
                Save Changes
            </button>
        </div>
    </div>
</div>

<!-- ── JAVASCRIPT ────────────────────────────────────────────────────────── -->
<script>
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
function toggleModule(id) {
    const el  = document.getElementById(id);
    const btn = document.getElementById('toggle-btn-' + id.replace('module-', ''));
    if (!el) return;
    const isHidden = el.style.display === 'none';
    el.style.display = isHidden ? 'block' : 'none';
    if (btn) btn.querySelector('span').textContent = isHidden ? 'expand_less' : 'expand_more';
}
document.addEventListener('click', function(e) {
    if (e.target === document.getElementById('newLessonModal')) closeNewLessonModal();
    if (e.target === document.getElementById('deleteModal'))   closeDeleteModal();
    if (e.target === document.getElementById('studentsModal')) closeStudentsModal();
});
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeNewLessonModal();
        closeDeleteModal();
        closeStudentsModal();
    }
});

/* ── Delete ──────────────────────────────────────────────────────────────── */
function confirmDelete(lessonId, lessonTitle) {
    document.getElementById('deleteLessonTitle').textContent = '"' + lessonTitle + '"';
    document.getElementById('deleteForm').action = '/lessons/' + lessonId;
    document.getElementById('deleteModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}
function closeDeleteModal() {
    document.getElementById('deleteModal').classList.add('hidden');
    document.body.style.overflow = '';
}

/* ── Students modal ──────────────────────────────────────────────────────── */
let _currentLessonId   = null;
let _allStudents       = [];

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
        div.dataset.lrn  = (s.lrn || '').toLowerCase();

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
            <span class="text-xs px-2 py-0.5 rounded-full font-semibold flex-shrink-0 ${s.assigned ? 'bg-green-100 text-green-700' : 'bg-slate-100 text-slate-500'}"
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

document.getElementById('studentSearchInput').addEventListener('input', function() {
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
        // Show a quick success toast by re-using the flash area
        const flash = document.querySelector('.mb-6.rounded-xl.border-green-200');
        if (flash) {
            flash.querySelector('span + *') // inner text node
        }
        // Simplest approach: reload so the flash message shows
        window.location.href = window.location.pathname + '?updated=1';
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined icon-outline text-[16px]">save</span> Save Changes';
        alert('Something went wrong. Please try again.');
    });
}
</script>

@endsection
