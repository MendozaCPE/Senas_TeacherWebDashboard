@extends('layouts.admin')
@section('title', 'Default Lessons')
@section('content')

<style>
    /* ── Reused from the teacher lessons.blade.php so this tab feels
       identical, just themed slightly differently for "this is shared
       content" (amber tab instead of blue, lock icon, etc.) ───────────── */
    .module-card {
        border-radius: 24px;
        border: 1.5px solid #e5eaf2;
        background: #ffffff;
        box-shadow: 0 4px 20px rgba(13, 50, 107, 0.03);
        overflow: hidden;
        transition: transform .25s cubic-bezier(.4,0,.2,1), box-shadow .25s cubic-bezier(.4,0,.2,1);
    }
    .module-card:hover { box-shadow: 0 16px 40px rgba(13, 50, 107, 0.08); transform: translateY(-3px); }

    .module-tab {
        display: inline-block;
        padding: 6px 22px;
        font-size: 10px;
        font-weight: 800;
        color: #ffffff;
        text-transform: uppercase;
        letter-spacing: 0.14em;
        clip-path: polygon(0 0, 100% 0, 88% 100%, 0% 100%);
        background: linear-gradient(90deg, #92400e, #d97706) !important;
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

    .lesson-table-wrap { padding: 0 24px 20px; overflow-x: auto; }
    .lesson-table { width: 100%; border-collapse: collapse; font-size: 13px; }
    .lesson-table thead th {
        padding: 14px 16px; text-align: left; font-size: 10.5px; font-weight: 800;
        color: #64748b; text-transform: uppercase; letter-spacing: 0.07em;
        border-bottom: 1.5px solid #f1f5f9; background: #f8fafc;
    }
    .lesson-table tbody td { padding: 14px 16px; border-bottom: 1px solid #f1f5f9; vertical-align: middle; }
    .lesson-table tbody tr { transition: all .2s ease; }
    .lesson-table tbody tr:hover { background: #fffbeb; }
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
    .action-link.danger:hover { color: #b91c1c; background: #fee2e2; border-color: #fca5a5; }
    .action-link.primary { color: #fff; background: linear-gradient(135deg, #92400e, #d97706); border: none; box-shadow: 0 3px 10px rgba(146,64,14,0.2); }
    .action-link.primary:hover { opacity: 0.95; transform: translateY(-1px); }
</style>

@if(session('success'))
<div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm font-semibold text-emerald-800 flex items-center gap-3 shadow-sm">
    <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>
    {{ session('success') }}
</div>
@endif

{{-- ── Header banner ─────────────────────────────────────────────────── --}}
<div class="rounded-[24px] p-6 mb-6 flex items-center justify-between flex-wrap gap-4"
     style="background:linear-gradient(135deg,#78350f 0%,#92400e 50%,#d97706 100%);">
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
        {{-- Push All Button (existing) --}}
        <form action="{{ route('admin.lesson-templates.push-all') }}" method="POST"
              onsubmit="return confirm('⚠️ This will overwrite EVERY teacher\'s copy of every default lesson with the current template content. Teachers who customized their lessons will lose their changes. Continue?');">
            @csrf
            <button type="submit" class="action-link" style="background:#fff;border-color:#fff;">
                <span class="material-symbols-outlined text-[16px]">sync</span>
                Push All to Teachers
            </button>
        </form>

        {{-- Push Selected Button (NEW) --}}
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

<div class="flex flex-col space-y-6">
    @php
        $moduleColors = ['#92400e','#b45309','#d97706','#c2410c'];
        $moduleIcons  = ['📚','📖','✏️','📝'];
    @endphp

    @forelse($modules as $modIndex => $module)
    @php
        $modColor    = $moduleColors[$modIndex % count($moduleColors)];
        $modIcon     = $moduleIcons[$modIndex % count($moduleIcons)];
        $padNum      = str_pad($modIndex + 1, 2, '0', STR_PAD_LEFT);
        $lessonCount = $module->lessons->count();
    @endphp

    <div class="module-card">
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

            <form action="{{ route('admin.lesson-templates.push', $module->module_id) }}" method="POST"
                  onsubmit="return confirm('Overwrite every teacher\'s copy of \'{{ addslashes($module->title) }}\' with this content?');">
                @csrf
                <button type="submit" class="action-link">
                    <span class="material-symbols-outlined text-[16px]">sync</span>
                    Push to All Teachers
                </button>
            </form>
        </div>

        <div class="lesson-table-wrap">
            @if($lessonCount === 0)
                <div class="py-8 text-center">
                    <p class="text-[14px] font-semibold text-slate-400">No lessons in this module yet</p>
                    <a href="{{ route('admin.lesson-templates.create', ['module_id' => $module->module_id]) }}"
   class="inline-flex items-center gap-1.5 text-[13px] font-bold text-[#92400e] hover:underline">
    <span class="material-symbols-outlined text-[16px]">add</span>
    Add Lesson to this Module
</a>
                </div>
            @else
                <table class="lesson-table">
                    <thead>
                        <tr>
                            <th>Lesson Title</th>
                            <th style="width:110px;">Difficulty</th>
                            <th style="width:110px;">Status</th>
                            <th style="width:180px;text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($module->lessons as $lesson)
                        <tr>
                            <td class="lesson-title-cell">{{ $lesson->title }}</td>
                            <td><span class="badge-difficulty {{ $lesson->difficulty }}">{{ $lesson->difficulty }}</span></td>
                            <td>
                                @if($lesson->trashed())
                                    <span class="badge-status" style="background:#e2e8f0;color:#64748b;">Archived</span>
                                @else
                                    <span class="badge-status {{ $lesson->status }}">{{ $lesson->status }}</span>
                                @endif
                            </td>
                            <td style="text-align:right;">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.lesson-templates.preview-modal', $lesson->hash_id) }}" target="_blank" class="action-link" title="Preview">View</a>
                                    <a href="{{ route('admin.lesson-templates.edit', $lesson->hash_id) }}" class="action-link" title="Edit">Edit</a>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

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
                    <a href="{{ route('admin.lesson-templates.checkpoint-exam.show', $exam->hash_id) }}" class="action-link primary">View Exam</a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div style="padding: 12px 24px 20px; border-top: 1px solid #f8fafc;">
            <a href="{{ route('admin.lesson-templates.create', ['module_id' => $module->module_id]) }}"
               class="inline-flex items-center gap-1.5 text-[13px] font-bold text-[#92400e] hover:underline">
                <span class="material-symbols-outlined text-[16px]">add</span>
                Add Lesson to this Module
            </a>
        </div>
    </div>
    @empty
    <div class="module-card">
        <div class="py-16 text-center">
            <p class="text-[15px] font-semibold text-slate-400">No default modules yet.</p>
            <a href="{{ route('admin.lesson-templates.create') }}" class="mt-3 inline-flex items-center gap-1 text-[13px] font-bold text-[#92400e] hover:underline">
                <span class="material-symbols-outlined text-[16px]">add</span> Create the first default lesson
            </a>
        </div>
    </div>
    @endforelse
</div>
{{-- ── PUSH TO SELECTED TEACHERS MODAL ──────────────────────────────── --}}
<div id="pushModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-[9999] hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] shadow-2xl flex flex-col">
        <!-- Header -->
        <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 flex-shrink-0">
            <div>
                <h3 class="text-base font-bold text-[#0d326b] flex items-center gap-2">
                    <span class="material-symbols-outlined text-[#d97706]">sync</span>
                    Push to Selected Teachers
                </h3>
                <p class="text-xs text-slate-400 font-medium mt-0.5">
                    Select which teachers should receive the updated default lessons.
                </p>
            </div>
            <button onclick="closePushModal()" class="text-slate-400 hover:text-slate-600">
                <span class="material-symbols-outlined text-[20px]">close</span>
            </button>
        </div>

        <!-- Search & Select All -->
        <div class="px-6 py-3 border-b border-slate-50 flex-shrink-0">
            <div class="flex items-center gap-3 flex-wrap">
                <div class="relative flex-1 min-w-[180px]">
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                    <input type="text" id="pushTeacherSearch" placeholder="Search teachers..."
                           class="w-full pl-10 pr-3 py-2 border border-slate-200 rounded-xl text-sm focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="selectAllPushTeachers()"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold text-[#0d326b] bg-[#f0f4ff] hover:bg-[#e0e8ff] transition">
                        Select All
                    </button>
                    <button type="button" onclick="deselectAllPushTeachers()"
                            class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-500 bg-slate-100 hover:bg-slate-200 transition">
                        Deselect All
                    </button>
                </div>
            </div>
        </div>

        <!-- Teacher List -->
        <div class="overflow-y-auto flex-1 px-6 py-3" id="pushTeacherList">
            <div id="pushTeacherLoading" class="py-8 text-center text-slate-400 text-sm">
                <div class="inline-block w-6 h-6 border-2 border-slate-200 border-t-[#0d326b] rounded-full animate-spin mr-2"></div>
                Loading teachers...
            </div>
            <div id="pushTeacherContainer" class="space-y-1 hidden">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>

        <!-- Footer -->
        <div class="flex items-center justify-between px-6 py-4 border-t border-slate-100 flex-shrink-0">
            <span class="text-xs text-slate-400 font-medium">
                <span id="pushSelectedCount">0</span> teachers selected
            </span>
            <div class="flex gap-3">
                <button type="button" onclick="closePushModal()"
                        class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 font-bold text-sm hover:bg-slate-50 transition">
                    Cancel
                </button>
                <button type="button" onclick="executePush()" id="pushExecuteBtn"
                        class="px-6 py-2 rounded-xl bg-[#0d326b] text-white font-bold text-sm hover:bg-[#1a6fd4] transition flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="material-symbols-outlined text-[18px]">sync</span>
                    Push to Selected Teachers
                </button>
            </div>
        </div>
    </div>
</div>

<script>
let pushTeachers = [];
let pushModuleId = null;

function openPushModal(moduleId = null) {
    pushModuleId = moduleId;
    const modal = document.getElementById('pushModal');
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    
    // Load teachers
    loadPushTeachers();
}

function closePushModal() {
    document.getElementById('pushModal').classList.add('hidden');
    document.body.style.overflow = '';
}

function loadPushTeachers() {
    const loading = document.getElementById('pushTeacherLoading');
    const container = document.getElementById('pushTeacherContainer');
    
    loading.classList.remove('hidden');
    container.classList.add('hidden');
    container.innerHTML = '';

    fetch('{{ route('admin.lesson-templates.teachers') }}', {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        pushTeachers = data.teachers || [];
        renderPushTeachers(pushTeachers);
        loading.classList.add('hidden');
        container.classList.remove('hidden');
        updatePushCount();
    })
    .catch(err => {
        console.error('Failed to load teachers:', err);
        loading.innerHTML = '❌ Failed to load teachers. Please try again.';
    });
}

function renderPushTeachers(teachers) {
    const container = document.getElementById('pushTeacherContainer');
    container.innerHTML = '';

    if (teachers.length === 0) {
        container.innerHTML = '<div class="py-8 text-center text-slate-400 text-sm">No teachers found.</div>';
        return;
    }

    teachers.forEach(teacher => {
        const div = document.createElement('div');
        div.className = 'flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-slate-50 transition-colors teacher-row';
        div.dataset.id = teacher.id;
        div.dataset.name = teacher.name.toLowerCase();

        const hasCopiesBadge = teacher.has_copies 
            ? '<span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">Has copies</span>'
            : '<span class="text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded-full">No copies yet</span>';

        div.innerHTML = `
            <input type="checkbox" class="push-teacher-checkbox w-4 h-4 rounded accent-[#0d326b] flex-shrink-0 cursor-pointer"
                   data-id="${teacher.id}" onchange="updatePushCount()">
            <div class="w-8 h-8 rounded-full bg-[#0d326b]/10 text-[#0d326b] font-bold text-xs flex items-center justify-center flex-shrink-0">
                ${teacher.initials || '?'}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-slate-800 truncate">${teacher.name}</p>
                <p class="text-xs text-slate-400 truncate">${teacher.email || ''}</p>
            </div>
            ${hasCopiesBadge}
        `;
        container.appendChild(div);
    });

    // Auto-select teachers who already have copies (safe to update)
    document.querySelectorAll('.push-teacher-checkbox').forEach(cb => {
        const id = parseInt(cb.dataset.id);
        const teacher = pushTeachers.find(t => t.id === id);
        if (teacher && teacher.has_copies) {
            cb.checked = true;
        }
    });
    updatePushCount();
}

function updatePushCount() {
    const checked = document.querySelectorAll('.push-teacher-checkbox:checked').length;
    document.getElementById('pushSelectedCount').textContent = checked;
}

function selectAllPushTeachers() {
    document.querySelectorAll('.push-teacher-checkbox').forEach(cb => cb.checked = true);
    updatePushCount();
}

function deselectAllPushTeachers() {
    document.querySelectorAll('.push-teacher-checkbox').forEach(cb => cb.checked = false);
    updatePushCount();
}

// Search filter
document.getElementById('pushTeacherSearch')?.addEventListener('input', function() {
    const q = this.value.toLowerCase().trim();
    document.querySelectorAll('.teacher-row').forEach(row => {
        const match = row.dataset.name.includes(q);
        row.style.display = match ? '' : 'none';
    });
});

function executePush() {
    const selected = document.querySelectorAll('.push-teacher-checkbox:checked');
    if (selected.length === 0) {
        alert('Please select at least one teacher.');
        return;
    }

    const teacherIds = Array.from(selected).map(cb => parseInt(cb.dataset.id));
    const btn = document.getElementById('pushExecuteBtn');
    btn.disabled = true;
    btn.innerHTML = '<div class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-2"></div> Pushing...';

    const payload = {
        teacher_ids: teacherIds,
        _token: document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
    };

    if (pushModuleId) {
        payload.module_id = pushModuleId;
    }

    fetch('{{ route('admin.lesson-templates.push-selected') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify(payload)
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            closePushModal();
            showPushToast('✅ ' + data.message);
            setTimeout(() => window.location.reload(), 2000);
        } else {
            alert(data.message || 'Push failed. Please try again.');
            btn.disabled = false;
            btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">sync</span> Push to Selected Teachers';
        }
    })
    .catch(err => {
        alert('Network error. Please try again.');
        btn.disabled = false;
        btn.innerHTML = '<span class="material-symbols-outlined text-[18px]">sync</span> Push to Selected Teachers';
    });
}

function showPushToast(message) {
    const toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#0d326b;color:white;padding:14px 24px;border-radius:16px;font-weight:700;font-size:14px;box-shadow:0 8px 30px rgba(13,50,107,0.3);z-index:99999;max-width:420px;animation:slideUp 0.3s ease;';
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(10px)';
        toast.style.transition = 'all 0.3s';
        setTimeout(() => toast.remove(), 400);
    }, 4000);
}

// Add keyframe for slideUp animation
const styleSheet = document.createElement("style");
styleSheet.textContent = `@keyframes slideUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }`;
document.head.appendChild(styleSheet);
</script>


@endsection