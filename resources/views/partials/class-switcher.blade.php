@php
    $user = Auth::user();
    $isTeacherView = $user && !in_array($user->role, ['admin', 'grade_leader', 'ict']) && !request()->is('admin*', 'grade-leader*', 'ict*', 'settings*');
    $teacher = $isTeacherView ? $user->teacher : null;
    $classrooms = $teacher ? $teacher->classrooms()->where('is_active', true)->withCount(['students' => fn($q) => $q->where('is_enrolled', true)])->get() : collect();
    $activeClassroomId = session('active_classroom_id');
    $activeClass = $classrooms->firstWhere('id', $activeClassroomId);
    $totalTeacherStudents = $teacher ? \App\Models\Student::where('teacher_id', $teacher->id)->where('is_enrolled', true)->count() : 0;

    // Hide class switcher on lesson inner pages (create/edit/show/checkpoint-exam/media-library/preview)
    // Keep it visible only on the main lessons index
    $hideSwitcher = request()->is(
        'lessons/create',
        'lessons/*/edit',
        'lessons/checkpoint-exam*',
        'lessons/media-library*',
        'lessons/my-uploads',
        'lessons/preview',
        'admin/lessons/create',
        'admin/lessons/*/edit',
        'admin/lessons/checkpoint-exam*',
        'admin/lessons/media-library*',
        'admin/lessons/my-uploads',
        'admin/lessons/preview'
    );
    // Also hide for lesson show/view pages (lessons/{id}/view, lessons/{lesson})
    // We detect by checking if we're on a non-index lesson path that isn't the main list
    if (!$hideSwitcher && request()->is('lessons/*')) {
        $hideSwitcher = true;
    }
@endphp

@if($teacher && $classrooms->isNotEmpty() && !$hideSwitcher)

<div class="px-8 py-2.5 flex items-center justify-between gap-4 border-b border-slate-200/80 bg-white/85 backdrop-blur-md z-20 shadow-2xs">
    <!-- Left: Subtabs / Class Switcher Pills -->
    <div class="flex items-center gap-2.5 overflow-x-auto no-scrollbar py-1">
        <span class="text-xs font-black tracking-wider text-slate-500 uppercase flex items-center gap-1.5 mr-1 select-none">
            <span class="material-symbols-outlined text-[19px] text-[#0d326b]">meeting_room</span>
            Class:
        </span>

        <!-- "All Classes" Pill -->
        <a href="{{ route('classes.switch.get', 'all') }}"
           class="inline-flex items-center gap-2.5 px-4 py-2 rounded-2xl text-[13px] sm:text-sm font-bold transition-all duration-200 {{ empty($activeClassroomId) ? 'bg-[#0d326b] text-white shadow-md ring-2 ring-[#0d326b]/20 scale-[1.02]' : 'bg-slate-100/90 text-slate-600 hover:bg-slate-200 hover:text-slate-900' }}">
            <span>All Classes</span>
            <span class="inline-flex items-center justify-center px-2.5 py-0.5 text-xs font-black rounded-full {{ empty($activeClassroomId) ? 'bg-white/25 text-white' : 'bg-white text-slate-600 shadow-2xs' }}">
                {{ $totalTeacherStudents }}
            </span>
        </a>

        <!-- Individual Classrooms Pills -->
        @foreach($classrooms as $c)
            @php $isActive = ((int) $activeClassroomId === (int) $c->id); @endphp
            <a href="{{ route('classes.switch.get', $c->id) }}"
               class="inline-flex items-center gap-2.5 px-4 py-2 rounded-2xl text-[13px] sm:text-sm font-bold transition-all duration-200 {{ $isActive ? 'bg-[#0d326b] text-white shadow-md ring-2 ring-[#0d326b]/20 scale-[1.02]' : 'bg-slate-100/90 text-slate-600 hover:bg-slate-200 hover:text-slate-900' }}">
                <span>{{ $c->name }}</span>
                <span class="inline-flex items-center justify-center px-2.5 py-0.5 text-xs font-black rounded-full {{ $isActive ? 'bg-amber-400 text-[#0d326b]' : 'bg-white text-slate-600 shadow-2xs' }}">
                    {{ $c->students_count }}
                </span>
            </a>
        @endforeach

        <!-- "+ New Class" Quick Button -->
        <a href="{{ route('classes.index') }}"
           class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-2xl text-xs sm:text-sm font-bold text-blue-600 hover:text-blue-800 hover:bg-blue-50 border border-dashed border-blue-300 transition-all hover:border-blue-400"
           title="Manage or Add Classes">
            <span class="material-symbols-outlined text-[17px]">add</span>
            <span>New Class</span>
        </a>
    </div>

    <!-- Right: Current View Indicator / Manage Link -->
    <div class="hidden sm:flex items-center gap-3.5 text-xs sm:text-sm">
        @if($activeClass)
            <span class="inline-flex items-center gap-2 text-slate-600 bg-slate-100 px-3 py-1.5 rounded-xl font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Active: <strong class="text-slate-900 font-bold">{{ $activeClass->name }}</strong>
            </span>
        @else
            <span class="text-slate-400 font-medium">Viewing all classes</span>
        @endif
        <a href="{{ route('classes.index') }}" class="text-[#0d326b] hover:text-blue-800 hover:underline font-black text-xs uppercase tracking-wider">
            Manage &rarr;
        </a>
    </div>
</div>
@endif
