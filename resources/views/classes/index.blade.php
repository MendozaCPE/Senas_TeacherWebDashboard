@extends('layouts.app')

@section('title', 'Class Management')

@section('content')
<div class="max-w-7xl mx-auto py-6 space-y-6">

    {{-- Top Alert Messages --}}
    @if(session('success'))
        <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl shadow-sm animate-fade-in">
            <span class="material-symbols-outlined text-emerald-600 text-xl">check_circle</span>
            <span class="text-sm font-semibold">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="flex items-center gap-3 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-2xl shadow-sm animate-fade-in">
            <span class="material-symbols-outlined text-rose-600 text-xl">error</span>
            <span class="text-sm font-semibold">{{ session('error') }}</span>
        </div>
    @endif

    {{-- Header Banner --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-[#0d326b] via-[#154689] to-[#1a6fd4] p-8 text-white shadow-xl">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-white/90 text-xs font-bold uppercase tracking-wider mb-2">
                    <span class="material-symbols-outlined text-[15px]">meeting_room</span>
                    Classroom & Group Organization
                </div>
                <h1 class="text-3xl font-black tracking-tight">Class Management</h1>
                <p class="text-white/80 text-sm mt-1 max-w-2xl leading-relaxed">
                    Organize your enrolled learners into dedicated classes or cohorts (e.g. <strong>SNED Class</strong>, <strong>ALS Group</strong>, <strong>Special Training</strong>). 
                    Switch between classes anytime to focus your dashboard, analytics, and reports on a specific group.
                </p>
            </div>
            <div class="flex items-center gap-3 flex-shrink-0">
                <button type="button" onclick="openCreateClassModal()"
                        class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-amber-400 hover:bg-amber-300 text-[#0d326b] font-black text-sm shadow-lg hover:shadow-xl transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                    <span class="material-symbols-outlined text-[20px]">add</span>
                    Create New Class
                </button>
            </div>
        </div>
        <div class="absolute -right-8 -bottom-10 opacity-10 pointer-events-none">
            <span class="material-symbols-outlined text-[220px]">school</span>
        </div>
    </div>

    {{-- Unassigned Students Notice (if any) --}}
    @if($unassignedStudents->isNotEmpty())
        <div class="p-4 bg-amber-50/90 border border-amber-200 rounded-2xl flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0 text-amber-700">
                    <span class="material-symbols-outlined text-xl">group_add</span>
                </div>
                <div>
                    <h4 class="text-xs font-bold text-amber-900 uppercase tracking-wide">Unassigned Students</h4>
                    <p class="text-xs text-amber-700 font-medium">
                        You have <strong>{{ $unassignedStudents->count() }}</strong> student(s) not yet assigned to any class. Assign them so they show up in filtered reports.
                    </p>
                </div>
            </div>
            <button type="button" onclick="openCreateClassModal()"
                    class="px-3.5 py-1.5 text-xs font-bold text-amber-900 bg-amber-200/80 hover:bg-amber-300 rounded-xl transition-colors">
                Assign to Class
            </button>
        </div>
    @endif

    {{-- Classes Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($classrooms as $classroom)
            @php 
                $isCurrentlyActive = ((int) $activeClassroomId === (int) $classroom->id);
            @endphp
            <div class="bg-white rounded-3xl p-6 shadow-sm border {{ $isCurrentlyActive ? 'border-[#0d326b] ring-2 ring-[#0d326b]/15 shadow-md' : 'border-slate-200/80 hover:border-slate-300' }} flex flex-col justify-between transition-all duration-200">
                
                {{-- Top Card Header --}}
                <div>
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-black text-lg {{ $isCurrentlyActive ? 'bg-[#0d326b] text-white shadow-md' : 'bg-slate-100 text-[#0d326b]' }}">
                            <span class="material-symbols-outlined text-2xl">meeting_room</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            @if($isCurrentlyActive)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Active Filter
                                </span>
                            @endif

                            {{-- Actions Dropdown --}}
                            <div class="relative inline-block text-left" x-data="{ open: false }">
                                <button type="button" onclick="openEditModal({{ $classroom->id }}, '{{ addslashes($classroom->name) }}', '{{ addslashes($classroom->section ?? '') }}', '{{ addslashes($classroom->description ?? '') }}')"
                                        class="p-1.5 text-slate-400 hover:text-slate-600 rounded-lg hover:bg-slate-100 transition-colors"
                                        title="Edit Class">
                                    <span class="material-symbols-outlined text-[18px]">edit</span>
                                </button>
                                <button type="button" onclick="confirmDeleteClass({{ $classroom->id }}, '{{ addslashes($classroom->name) }}')"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors"
                                        title="Delete Class">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-lg font-black text-slate-800 leading-snug">{{ $classroom->name }}</h3>
                    @if($classroom->section)
                        <p class="text-xs font-bold text-blue-600 mt-0.5">{{ $classroom->section }}</p>
                    @endif
                    <p class="text-xs text-slate-500 mt-2 line-clamp-2 leading-relaxed">
                        {{ $classroom->description ?: 'No description provided.' }}
                    </p>
                </div>

                {{-- Middle: Student Roster Summary --}}
                <div class="my-5 pt-4 border-t border-slate-100">
                    <div class="flex items-center justify-between text-xs mb-3">
                        <span class="font-bold text-slate-500 uppercase tracking-wider text-[10px]">Enrolled Learners</span>
                        <span class="font-black text-slate-800 bg-slate-100 px-2 py-0.5 rounded-md text-xs">
                            {{ $classroom->students_count }} student(s)
                        </span>
                    </div>

                    @if($classroom->students->isNotEmpty())
                        <div class="space-y-1.5 max-h-36 overflow-y-auto pr-1 no-scrollbar">
                            @foreach($classroom->students->take(5) as $student)
                                <div class="flex items-center justify-between text-xs py-1 px-2 rounded-lg bg-slate-50/70">
                                    <span class="font-semibold text-slate-700 truncate max-w-[180px]">
                                        {{ $student->first_name }} {{ $student->last_name }}
                                    </span>
                                    <span class="text-[10px] text-slate-400 font-medium">
                                        {{ $student->grade_level ?: '—' }}
                                    </span>
                                </div>
                            @endforeach
                            @if($classroom->students->count() > 5)
                                <p class="text-[11px] font-bold text-slate-400 text-center pt-1">
                                    +{{ $classroom->students->count() - 5 }} more student(s)
                                </p>
                            @endif
                        </div>
                    @else
                        <div class="py-4 text-center rounded-xl bg-slate-50 border border-dashed border-slate-200">
                            <span class="material-symbols-outlined text-slate-300 text-2xl">person_off</span>
                            <p class="text-[11px] text-slate-400 font-medium mt-1">No students in this class yet.</p>
                        </div>
                    @endif
                </div>

                {{-- Bottom Action Buttons --}}
                <div class="space-y-2 pt-2 border-t border-slate-100">
                    @if($isCurrentlyActive)
                        <a href="{{ route('classes.switch.get', 'all') }}"
                           class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition-colors">
                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                            Clear Filter (View All)
                        </a>
                    @else
                        <a href="{{ route('classes.switch.get', $classroom->id) }}"
                           class="w-full inline-flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-[#0d326b] hover:bg-[#154689] text-white text-xs font-bold shadow-sm transition-all transform active:scale-95">
                            <span class="material-symbols-outlined text-[16px]">filter_alt</span>
                            Filter Dashboard to This Class
                        </a>
                    @endif

                    <button type="button" onclick="openAssignStudentsModal({{ $classroom->id }}, '{{ addslashes($classroom->name) }}', {{ json_encode($classroom->students->pluck('student_id')) }})"
                            class="w-full inline-flex items-center justify-center gap-1.5 py-2 rounded-xl text-blue-600 hover:bg-blue-50 text-xs font-bold transition-colors">
                        <span class="material-symbols-outlined text-[16px]">manage_accounts</span>
                        Assign / Manage Students
                    </button>
                </div>

            </div>
        @empty
            <div class="col-span-full py-16 text-center bg-white rounded-3xl border border-slate-200 shadow-sm">
                <span class="material-symbols-outlined text-5xl text-slate-300 mb-2">meeting_room</span>
                <h3 class="text-lg font-bold text-slate-700">No Classes Found</h3>
                <p class="text-sm text-slate-400 mt-1 max-w-sm mx-auto">
                    Create your first class (e.g. "SNED Class" or "ALS") to begin grouping students.
                </p>
                <button type="button" onclick="openCreateClassModal()"
                        class="mt-4 px-5 py-2.5 bg-[#0d326b] text-white rounded-xl text-xs font-bold shadow-md hover:bg-[#154689] transition-all">
                    Create First Class
                </button>
            </div>
        @endforelse
    </div>
</div>

{{-- ── MODAL: CREATE CLASS ────────────────────────────────────────────────── --}}
<div id="createClassModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all animate-pop-in">
        <div class="p-6 bg-gradient-to-r from-[#0d326b] to-[#1a6fd4] text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-white/10 flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl">add_home_work</span>
                </div>
                <div>
                    <h3 class="text-base font-bold">Create New Class</h3>
                    <p class="text-white/70 text-xs">Add a new class or training cohort</p>
                </div>
            </div>
            <button type="button" onclick="closeCreateClassModal()" class="text-white/70 hover:text-white">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <form method="POST" action="{{ route('classes.store') }}" class="p-6 space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Class Name <span class="text-rose-500">*</span></label>
                <input type="text" name="name" required placeholder="e.g. SNED Class A, ALS Group, Morning Cohort"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b] outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Section / Track (Optional)</label>
                <input type="text" name="section" placeholder="e.g. Grade 2 - Daisy, Transition Track"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b] outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Description (Optional)</label>
                <textarea name="description" rows="2" placeholder="Brief note about this class or training schedule..."
                          class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b] outline-none resize-none"></textarea>
            </div>

            {{-- Optional: assign initial students --}}
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1.5 flex items-center justify-between">
                    <span>Assign Initial Students (Optional)</span>
                    <span class="text-[10px] text-slate-400">Total: {{ $allStudents->count() }}</span>
                </label>
                <div class="max-h-40 overflow-y-auto border border-slate-200 rounded-xl p-2 space-y-1.5 no-scrollbar bg-slate-50">
                    @forelse($allStudents as $st)
                        <label class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-white text-xs cursor-pointer">
                            <input type="checkbox" name="student_ids[]" value="{{ $st->student_id }}" class="rounded text-[#0d326b] focus:ring-0">
                            <span class="font-semibold text-slate-700">{{ $st->first_name }} {{ $st->last_name }}</span>
                            <span class="text-[10px] text-slate-400 ml-auto">
                                {{ $st->classroom ? $st->classroom->name : 'Unassigned' }}
                            </span>
                        </label>
                    @empty
                        <p class="text-xs text-slate-400 text-center py-2">No students enrolled yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeCreateClassModal()"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100 transition-colors">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-xs font-black bg-[#0d326b] text-white shadow-md hover:bg-[#154689] transition-all">
                    Create Class
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL: EDIT CLASS ──────────────────────────────────────────────────── --}}
<div id="editClassModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden transform transition-all animate-pop-in">
        <div class="p-6 bg-slate-800 text-white flex items-center justify-between">
            <h3 class="text-base font-bold">Edit Class</h3>
            <button type="button" onclick="closeEditModal()" class="text-white/70 hover:text-white">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <form id="editClassForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Class Name <span class="text-rose-500">*</span></label>
                <input type="text" id="edit_name" name="name" required
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b] outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Section / Track (Optional)</label>
                <input type="text" id="edit_section" name="section"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b] outline-none">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">Description</label>
                <textarea id="edit_description" name="description" rows="2"
                          class="w-full px-4 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-[#0d326b]/20 focus:border-[#0d326b] outline-none resize-none"></textarea>
            </div>

            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeEditModal()"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-xs font-black bg-[#0d326b] text-white shadow-md hover:bg-[#154689]">
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL: ASSIGN STUDENTS ─────────────────────────────────────────────── --}}
<div id="assignStudentsModal" class="fixed inset-0 z-50 hidden bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl shadow-2xl max-w-lg w-full overflow-hidden transform transition-all animate-pop-in">
        <div class="p-6 bg-gradient-to-r from-[#0d326b] to-[#1a6fd4] text-white flex items-center justify-between">
            <div>
                <h3 class="text-base font-bold" id="assignModalTitle">Assign Students to Class</h3>
                <p class="text-white/70 text-xs">Select all students who belong to this class</p>
            </div>
            <button type="button" onclick="closeAssignStudentsModal()" class="text-white/70 hover:text-white">
                <span class="material-symbols-outlined text-xl">close</span>
            </button>
        </div>

        <form id="assignStudentsForm" method="POST" action="" class="p-6 space-y-4">
            @csrf
            <div>
                <input type="text" id="studentSearchFilter" onkeyup="filterStudentList()" placeholder="Quick filter by student name..."
                       class="w-full px-4 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-[#0d326b]/20 outline-none mb-3">
                <div class="max-h-64 overflow-y-auto border border-slate-200 rounded-2xl p-2 space-y-1.5 bg-slate-50" id="studentCheckboxList">
                    @foreach($allStudents as $st)
                        <label class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white text-xs cursor-pointer transition-colors student-item" data-name="{{ strtolower($st->first_name . ' ' . $st->last_name) }}">
                            <input type="checkbox" name="student_ids[]" value="{{ $st->student_id }}" id="st_cb_{{ $st->student_id }}"
                                   class="rounded text-[#0d326b] focus:ring-0">
                            <div>
                                <span class="font-bold text-slate-800 block">{{ $st->first_name }} {{ $st->last_name }}</span>
                                <span class="text-[10px] text-slate-400">{{ $st->grade_level ?: 'No grade' }} · {{ $st->section ?: 'No section' }}</span>
                            </div>
                            <span class="text-[10px] text-slate-400 ml-auto student-current-class font-medium">
                                {{ $st->classroom ? $st->classroom->name : 'Unassigned' }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                <button type="button" onclick="closeAssignStudentsModal()"
                        class="px-4 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-100">
                    Cancel
                </button>
                <button type="submit"
                        class="px-5 py-2.5 rounded-xl text-xs font-black bg-[#0d326b] text-white shadow-md hover:bg-[#154689]">
                    Update Class Roster
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── DELETE FORM (HIDDEN) ───────────────────────────────────────────────── --}}
<form id="deleteClassForm" method="POST" action="" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
function openCreateClassModal() {
    document.getElementById('createClassModal').classList.remove('hidden');
}
function closeCreateClassModal() {
    document.getElementById('createClassModal').classList.add('hidden');
}

function openEditModal(id, name, section, desc) {
    document.getElementById('editClassForm').action = '/classes/' + id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_section').value = section;
    document.getElementById('edit_description').value = desc;
    document.getElementById('editClassModal').classList.remove('hidden');
}
function closeEditModal() {
    document.getElementById('editClassModal').classList.add('hidden');
}

function openAssignStudentsModal(id, name, currentStudentIds) {
    document.getElementById('assignModalTitle').innerText = 'Assign Students to ' + name;
    document.getElementById('assignStudentsForm').action = '/classes/' + id + '/assign-students';
    
    // Check checkboxes of students currently in this class
    document.querySelectorAll('#studentCheckboxList input[type="checkbox"]').forEach(cb => {
        cb.checked = currentStudentIds.includes(parseInt(cb.value));
    });

    document.getElementById('assignStudentsModal').classList.remove('hidden');
}
function closeAssignStudentsModal() {
    document.getElementById('assignStudentsModal').classList.add('hidden');
}

function filterStudentList() {
    const q = document.getElementById('studentSearchFilter').value.toLowerCase();
    document.querySelectorAll('#studentCheckboxList .student-item').forEach(item => {
        const name = item.getAttribute('data-name');
        item.style.display = name.includes(q) ? 'flex' : 'none';
    });
}

function confirmDeleteClass(id, name) {
    if (confirm("Are you sure you want to delete '" + name + "'? Students in this class will not be deleted, but will become unassigned.")) {
        const f = document.getElementById('deleteClassForm');
        f.action = '/classes/' + id;
        f.submit();
    }
}
</script>
@endsection
