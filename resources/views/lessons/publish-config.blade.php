@extends('layouts.app')
@section('title', 'Publish Lesson Configuration')

@section('content')

{{-- ── SKELETON ─────────────────────────────────────────────────────────── --}}
<div id="page-skeleton" class="max-w-4xl mx-auto px-4 py-6" aria-hidden="true">
    {{-- Header row --}}
    <div class="flex items-center justify-between mb-8">
        <div class="flex flex-col gap-2">
            <div class="skeleton h-8 rounded w-56"></div>
            <div class="skeleton h-3 rounded w-72"></div>
        </div>
        <div class="skeleton h-10 rounded-xl w-36"></div>
    </div>
    {{-- Lesson overview card --}}
    <div class="bg-white rounded-[24px] p-6 border-l-4 border-[#0d326b] border border-slate-100 shadow-sm mb-6 flex items-start gap-4">
        <div class="skeleton w-12 h-12 rounded-2xl flex-shrink-0"></div>
        <div class="flex-1 flex flex-col gap-2">
            <div class="skeleton h-5 rounded w-64"></div>
            <div class="skeleton h-3 rounded w-full max-w-md"></div>
        </div>
        <div class="flex gap-2 flex-shrink-0">
            @for($i=0;$i<3;$i++)<div class="skeleton h-6 rounded-full w-20"></div>@endfor
        </div>
    </div>
    {{-- 3 section cards --}}
    @foreach(['Module Assignment','Target Audience','Notifications'] as $sec)
    <div class="bg-white rounded-[24px] p-7 border border-slate-100 shadow-sm mb-6 flex flex-col gap-5">
        <div class="flex items-center gap-3 pb-5 border-b border-slate-100">
            <div class="skeleton w-10 h-10 rounded-[12px]"></div>
            <div class="flex flex-col gap-1.5">
                <div class="skeleton h-4 rounded w-40"></div>
                <div class="skeleton h-3 rounded w-56"></div>
            </div>
        </div>
        @if($loop->first)
        <div class="flex flex-col gap-3">
            @for($i=0;$i<2;$i++)
            <div class="border-2 border-slate-200 rounded-[16px] p-4 flex items-start gap-3">
                <div class="skeleton w-5 h-5 rounded-full mt-0.5 flex-shrink-0"></div>
                <div class="flex-1 flex flex-col gap-2">
                    <div class="skeleton h-4 rounded w-40"></div>
                    <div class="skeleton h-3 rounded w-full max-w-sm"></div>
                    @if($i===0)<div class="skeleton h-9 rounded-[12px] w-full mt-1"></div>@endif
                </div>
            </div>
            @endfor
        </div>
        @elseif($loop->index===1)
        <div class="grid grid-cols-2 gap-3">
            @for($i=0;$i<4;$i++)
            <div class="border-2 border-slate-200 rounded-[16px] p-4 flex items-start gap-3">
                <div class="skeleton w-5 h-5 rounded-full flex-shrink-0"></div>
                <div class="skeleton h-4 rounded flex-1"></div>
            </div>
            @endfor
        </div>
        @else
        <div class="skeleton h-14 rounded-[14px] w-full"></div>
        @endif
    </div>
    @endforeach
    {{-- Action row --}}
    <div class="flex gap-3 justify-end mt-4">
        <div class="skeleton h-11 rounded-[12px] w-24"></div>
        <div class="skeleton h-11 rounded-[12px] w-36"></div>
    </div>
</div>
<script>document.addEventListener('DOMContentLoaded',function(){var s=document.getElementById('page-skeleton');if(s)s.style.display='none';});</script>
{{-- ── END SKELETON ─────────────────────────────────────────────────────── --}}

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

<style>
    body, .publish-config-container * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }

    .material-symbols-outlined {
        font-family: 'Material Symbols Outlined';
        font-weight: normal;
        font-style: normal;
        font-size: 20px;
        line-height: 1;
        letter-spacing: normal;
        text-transform: none;
        display: inline-block;
        white-space: nowrap;
        word-wrap: normal;
        direction: ltr;
        -webkit-font-feature-settings: 'liga';
        -webkit-font-smoothing: antialiased;
        font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 20;
        vertical-align: middle;
    }

    .section-card {
        background: white;
        border-radius: 24px;
        padding: 28px;
        box-shadow: 0 4px 20px rgba(13, 50, 107, 0.05);
        border: 1px solid rgba(13, 50, 107, 0.06);
        margin-bottom: 24px;
    }

    .section-title-wrap {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .section-icon-box {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: #eef2ff;
        color: #0d326b;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .section-title {
        font-size: 17px;
        font-weight: 800;
        color: #0d326b;
        margin: 0;
    }

    .section-subtitle {
        font-size: 13px;
        color: #64748b;
        margin-top: 2px;
    }

    .option-card {
        border: 2px solid #e2e8f0;
        border-radius: 16px;
        padding: 16px 20px;
        background: #fafafa;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .option-card:hover {
        border-color: #cbd5e1;
        background: white;
    }

    .option-card.selected {
        border-color: #0d326b;
        background: #f8fafc;
        box-shadow: 0 0 0 4px rgba(13, 50, 107, 0.06);
    }

    .field-input, .field-select, .field-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        outline: none;
        transition: all 0.2s ease;
        font-size: 14px;
        background: white;
        color: #1e293b;
    }

    .field-input:focus, .field-select:focus, .field-textarea:focus {
        border-color: #0d326b;
        box-shadow: 0 0 0 4px rgba(13, 50, 107, 0.08);
    }

    .custom-scrollbar::-webkit-scrollbar {
        width: 6px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

<div class="publish-config-container max-w-4xl mx-auto px-4 py-6">
    <!-- Header Row -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-black text-[#0d326b] tracking-tight">Publish &amp; Assign Lesson</h1>
            <p class="text-slate-500 text-sm font-medium mt-1">Configure module placement and student visibility before publishing.</p>
        </div>
        <a href="{{ route('lessons.index') }}" 
           class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 font-bold text-sm hover:bg-slate-50 hover:border-slate-300 transition-all flex items-center gap-2 shadow-sm">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            Back to Lessons
        </a>
    </div>

    <!-- Lesson Overview Card -->
    <div class="section-card bg-gradient-to-r from-white via-slate-50 to-white border-l-4 border-l-[#0d326b]">
        <div class="flex items-start justify-between flex-wrap gap-4">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-[#0d326b]/10 text-[#0d326b] flex items-center justify-center flex-shrink-0">
                    <span class="material-symbols-outlined text-[28px]">auto_stories</span>
                </div>
                <div>
                    <h2 class="text-xl font-extrabold text-[#0d326b]">{{ $lesson->title }}</h2>
                    <p class="text-xs text-slate-500 font-medium mt-0.5 line-clamp-1 max-w-xl">
                        {{ $lesson->description ?: 'No description provided.' }}
                    </p>
                </div>
            </div>

            <!-- Chips -->
            <div class="flex items-center gap-2 flex-wrap">
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#0d326b] border border-blue-100 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">category</span>
                    {{ ucfirst($lesson->lesson_type) }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">bar_chart</span>
                    {{ ucfirst($lesson->difficulty) }}
                </span>
                <span class="px-3 py-1 rounded-full text-xs font-bold {{ $lesson->status === 'published' ? 'bg-emerald-50 text-emerald-700 border border-emerald-100' : 'bg-amber-50 text-amber-700 border border-amber-100' }} flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">{{ $lesson->status === 'published' ? 'check_circle' : 'pending' }}</span>
                    {{ ucfirst($lesson->status) }}
                </span>
            </div>
        </div>
    </div>

    <!-- Error Summary Alert -->
    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-2 text-rose-800 font-bold text-sm">
                <span class="material-symbols-outlined text-[20px] text-rose-600">error</span>
                Please correct the errors below:
            </div>
            <ul class="text-xs text-rose-700 space-y-1 pl-7 list-disc font-medium">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('lessons.publish', $lesson->hash_id) }}" method="POST" id="publishForm">
        @csrf

        <!-- 1. Module Selection Section -->
        <div class="section-card">
            <div class="section-title-wrap">
                <div class="section-icon-box">
                    <span class="material-symbols-outlined">view_module</span>
                </div>
                <div>
                    <h3 class="section-title">Module Assignment</h3>
                    <p class="section-subtitle">Choose where this lesson belongs in your curriculum modules.</p>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Existing Module Option -->
                <div class="option-card" id="cardModuleExisting">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" name="module_action" value="existing" id="moduleExisting"
                               {{ old('module_action', ($lesson->module_id || $modules->isNotEmpty()) ? 'existing' : 'new') === 'existing' ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-[#0d326b] border-slate-300 focus:ring-[#0d326b]" {{ $modules->isEmpty() ? 'disabled' : '' }}>
                        <div class="flex-1">
                            <span class="text-sm font-bold text-slate-800 block">Assign to an existing module</span>
                            <span class="text-xs text-slate-500 block">Select from modules you have already created.</span>
                            
                            <div id="existingModuleBlock" class="mt-3">
                                <select name="module_id" id="module_id" class="field-select max-w-md">
                                    <option value="">-- Select a Module --</option>
                                    @foreach($modules as $module)
                                        <option value="{{ $module->module_id }}" {{ (string) old('module_id', $lesson->module_id) === (string) $module->module_id ? 'selected' : '' }}>
                                            {{ $module->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- New Module Option -->
                <div class="option-card" id="cardModuleNew">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" name="module_action" value="new" id="moduleNew"
                               {{ old('module_action', ($lesson->module_id || $modules->isNotEmpty()) ? 'existing' : 'new') === 'new' ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-[#0d326b] border-slate-300 focus:ring-[#0d326b]">
                        <div class="flex-1">
                            <span class="text-sm font-bold text-slate-800 block">Create a new module</span>
                            <span class="text-xs text-slate-500 block">Create and assign this lesson to a brand new module.</span>

                            <div id="newModuleBlock" class="mt-3 space-y-3 max-w-md hidden">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Module Title *</label>
                                    <input type="text" name="new_module[title]" value="{{ old('new_module.title') }}"
                                           placeholder="e.g. FSL Basics &amp; Alphabet"
                                           class="field-input">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Description (Optional)</label>
                                    <textarea name="new_module[description]" rows="2" placeholder="Brief explanation of module goals..."
                                              class="field-textarea">{{ old('new_module.description') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>

            @if($modules->isEmpty())
                <div class="mt-4 p-4 rounded-xl bg-amber-50 border border-amber-200 flex items-center gap-3">
                    <span class="material-symbols-outlined text-amber-600 text-[22px]">info</span>
                    <p class="text-xs text-amber-800 font-medium">
                        You don't have any existing modules yet. Choose "Create a new module" above to get started.
                    </p>
                </div>
            @endif
        </div>

        <!-- 2. Target Audience Section -->
        <div class="section-card">
            <div class="section-title-wrap">
                <div class="section-icon-box">
                    <span class="material-symbols-outlined">groups</span>
                </div>
                <div>
                    <h3 class="section-title">Target Audience</h3>
                    <p class="section-subtitle">Determine which students can view and practice this lesson.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <!-- Option 1: All Students -->
                <div class="option-card" id="cardPublishAll">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" name="publish_option" value="all" id="publishAll" checked
                               class="mt-1 h-4 w-4 text-[#0d326b] border-slate-300 focus:ring-[#0d326b]">
                        <div>
                            <div class="flex items-center gap-1.5 font-bold text-sm text-slate-800">
                                <span class="material-symbols-outlined text-[18px] text-[#0d326b]">public</span>
                                All Active Students
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Publish to every active student enrolled in your class.</p>
                        </div>
                    </label>
                </div>

                <!-- Option 2: By Program -->
                <div class="option-card" id="cardPublishProgram">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" name="publish_option" value="program" id="publishProgram"
                               class="mt-1 h-4 w-4 text-[#0d326b] border-slate-300 focus:ring-[#0d326b]">
                        <div class="flex-1">
                            <div class="flex items-center gap-1.5 font-bold text-sm text-slate-800">
                                <span class="material-symbols-outlined text-[18px] text-[#0d326b]">school</span>
                                Filter by Program
                            </div>
                            <p class="text-xs text-slate-500 mt-1 mb-2">Publish only to students enrolled in a specific program.</p>
                            <select name="program" id="programSelect" disabled class="field-select text-xs py-2">
                                <option value="">Select Program...</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program }}">{{ ucfirst($program) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </label>
                </div>

                <!-- Option 3: By Mastery Level -->
                <div class="option-card" id="cardPublishMastery">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" name="publish_option" value="mastery" id="publishMastery"
                               class="mt-1 h-4 w-4 text-[#0d326b] border-slate-300 focus:ring-[#0d326b]">
                        <div class="flex-1">
                            <div class="flex items-center gap-1.5 font-bold text-sm text-slate-800">
                                <span class="material-symbols-outlined text-[18px] text-[#0d326b]">workspace_premium</span>
                                Filter by Mastery Level
                            </div>
                            <p class="text-xs text-slate-500 mt-1 mb-2">Target students at a specific FSL skill tier.</p>
                            <select name="mastery_level" id="masterySelect" disabled class="field-select text-xs py-2">
                                <option value="">Select Mastery Level...</option>
                                @foreach($masteryLevels as $level)
                                    <option value="{{ $level }}">{{ ucfirst($level) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </label>
                </div>

                <!-- Option 4: Select Specific Students -->
                <div class="option-card" id="cardPublishSelected">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" name="publish_option" value="selected" id="publishSelected"
                               class="mt-1 h-4 w-4 text-[#0d326b] border-slate-300 focus:ring-[#0d326b]">
                        <div>
                            <div class="flex items-center gap-1.5 font-bold text-sm text-slate-800">
                                <span class="material-symbols-outlined text-[18px] text-[#0d326b]">person_search</span>
                                Specific Students
                            </div>
                            <p class="text-xs text-slate-500 mt-1">Hand-pick individual students from your roster.</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Student Picker Container -->
            <div id="studentSelection" class="hidden rounded-2xl bg-slate-50 p-5 border border-slate-200">
                <div class="flex items-center justify-between flex-wrap gap-3 mb-4">
                    <div class="flex items-center gap-2">
                        <h4 class="text-sm font-bold text-[#0d326b]">Student Roster</h4>
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-extrabold bg-[#0d326b] text-white">
                            <span id="selectedCount">0</span> selected
                        </span>
                    </div>
                    <div class="flex items-center gap-2">
                        <button type="button" onclick="selectAllStudents()" 
                                class="px-3 py-1.5 rounded-lg text-xs font-bold text-[#0d326b] bg-white border border-slate-200 hover:bg-slate-100 transition-colors">
                            Select All
                        </button>
                        <button type="button" onclick="deselectAllStudents()" 
                                class="px-3 py-1.5 rounded-lg text-xs font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 transition-colors">
                            Deselect All
                        </button>
                    </div>
                </div>

                <!-- Search Input -->
                <div class="relative mb-3">
                    <span class="material-symbols-outlined absolute left-3.5 top-3 text-slate-400 text-[18px]">search</span>
                    <input type="text" id="studentSearch" placeholder="Search by student name or LRN..." 
                           class="field-input pl-10 text-xs bg-white">
                </div>

                <!-- Scrollable List -->
                <div class="max-h-64 overflow-y-auto custom-scrollbar border border-slate-200 rounded-xl bg-white divide-y divide-slate-100">
                    @forelse($students as $student)
                        <label class="flex items-center justify-between px-4 py-3 hover:bg-slate-50 cursor-pointer transition-colors student-item-row">
                            <div class="flex items-center gap-3">
                                <input type="checkbox" name="students[]" value="{{ $student->student_id }}"
                                       class="student-checkbox h-4 w-4 text-[#0d326b] border-slate-300 rounded focus:ring-[#0d326b]">
                                <div class="w-8 h-8 rounded-full bg-[#0d326b]/10 text-[#0d326b] font-bold text-xs flex items-center justify-center flex-shrink-0">
                                    {{ strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)) }}
                                </div>
                                <div>
                                    <span class="text-xs font-bold text-slate-800 block">
                                        {{ $student->first_name }} {{ $student->last_name }}
                                    </span>
                                    <div class="flex items-center gap-2 text-[11px] text-slate-400 font-medium">
                                        @if($student->program) <span>{{ ucfirst($student->program) }}</span> @endif
                                        @if($student->mastery_level) <span>• {{ ucfirst($student->mastery_level) }}</span> @endif
                                        @if($student->grade_level) <span>• Grade {{ $student->grade_level }}</span> @endif
                                    </div>
                                </div>
                            </div>
                            <span class="text-[11px] font-mono font-semibold text-slate-400 bg-slate-100 px-2 py-0.5 rounded">
                                LRN: {{ $student->lrn }}
                            </span>
                        </label>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400">No active students found.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- 3. Additional Options Section -->
        <div class="section-card">
            <div class="section-title-wrap">
                <div class="section-icon-box">
                    <span class="material-symbols-outlined">notifications_active</span>
                </div>
                <div>
                    <h3 class="section-title">Notifications &amp; Reminders</h3>
                    <p class="section-subtitle">Send notifications to student devices upon publishing.</p>
                </div>
            </div>

            <input type="hidden" name="notify_students" value="1">

            <div class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-200">
                <span class="material-symbols-outlined text-[#0d326b] text-[20px] flex-shrink-0">notifications_active</span>
                <p class="text-xs text-slate-600 font-medium">
                    Students will be <span class="font-bold text-slate-800">automatically notified</span> when this lesson is published and assigned to them.
                </p>
            </div>
        </div>

        <!-- Action Row -->
        <div class="flex items-center justify-end gap-3 pt-2 mb-12">
            <a href="{{ route('lessons.index') }}" 
               class="px-6 py-3 border border-slate-200 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-100 transition-colors">
                Cancel
            </a>
            <button type="submit" id="publishSubmitBtn"
                    class="px-8 py-3 bg-[#0d326b] hover:bg-[#154188] text-white font-extrabold rounded-xl text-sm transition-all shadow-md active:scale-95 flex items-center gap-2 cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">rocket_launch</span>
                Publish Lesson
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const publishOptions = document.querySelectorAll('input[name="publish_option"]');
    const studentSelection = document.getElementById('studentSelection');
    const programSelect = document.getElementById('programSelect');
    const masterySelect = document.getElementById('masterySelect');
    const moduleExisting = document.getElementById('moduleExisting');
    const moduleNew = document.getElementById('moduleNew');
    const existingModuleBlock = document.getElementById('existingModuleBlock');
    const newModuleBlock = document.getElementById('newModuleBlock');
    const moduleSelect = document.getElementById('module_id');

    // Highlight Option Cards based on active selection
    function updateOptionCards() {
        // Module cards
        const isNew = moduleNew && moduleNew.checked;
        document.getElementById('cardModuleExisting')?.classList.toggle('selected', !isNew);
        document.getElementById('cardModuleNew')?.classList.toggle('selected', isNew);

        // Publish target cards
        const activeOption = document.querySelector('input[name="publish_option"]:checked')?.value;
        document.getElementById('cardPublishAll')?.classList.toggle('selected', activeOption === 'all');
        document.getElementById('cardPublishProgram')?.classList.toggle('selected', activeOption === 'program');
        document.getElementById('cardPublishMastery')?.classList.toggle('selected', activeOption === 'mastery');
        document.getElementById('cardPublishSelected')?.classList.toggle('selected', activeOption === 'selected');
    }

    function toggleModuleBlocks() {
        const useNew = moduleNew && moduleNew.checked;
        if (existingModuleBlock) existingModuleBlock.style.display = useNew ? 'none' : 'block';
        if (newModuleBlock) newModuleBlock.classList.toggle('hidden', !useNew);
        if (moduleSelect) {
            moduleSelect.required = !useNew;
            if (useNew) moduleSelect.value = '';
        }
        updateOptionCards();
    }

    if (moduleExisting) moduleExisting.addEventListener('change', toggleModuleBlocks);
    if (moduleNew) moduleNew.addEventListener('change', toggleModuleBlocks);
    toggleModuleBlocks();
    
    publishOptions.forEach(radio => {
        radio.addEventListener('change', function() {
            // Show/hide student selection
            if (this.value === 'selected') {
                studentSelection.classList.remove('hidden');
            } else {
                studentSelection.classList.add('hidden');
            }
            
            // Enable/disable program select
            if (this.value === 'program') {
                programSelect.disabled = false;
            } else {
                programSelect.disabled = true;
                programSelect.value = '';
            }
            
            // Enable/disable mastery select
            if (this.value === 'mastery') {
                masterySelect.disabled = false;
            } else {
                masterySelect.disabled = true;
                masterySelect.value = '';
            }

            updateOptionCards();
        });
    });
    
    // Student search functionality
    const searchInput = document.getElementById('studentSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();
            document.querySelectorAll('.student-item-row').forEach((row) => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? 'flex' : 'none';
            });
        });
    }
    
    // Update selected count
    document.querySelectorAll('.student-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateSelectedCount);
    });
    
    function updateSelectedCount() {
        const checked = document.querySelectorAll('.student-checkbox:checked').length;
        const countEl = document.getElementById('selectedCount');
        if (countEl) countEl.textContent = checked;
    }

    updateOptionCards();
});

function selectAllStudents() {
    document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = true);
    const countEl = document.getElementById('selectedCount');
    if (countEl) countEl.textContent = document.querySelectorAll('.student-checkbox').length;
}

function deselectAllStudents() {
    document.querySelectorAll('.student-checkbox').forEach(cb => cb.checked = false);
    const countEl = document.getElementById('selectedCount');
    if (countEl) countEl.textContent = 0;
}

document.getElementById('publishForm')?.addEventListener('submit', function(e) {
    const useNew = document.getElementById('moduleNew')?.checked;
    const moduleSelect = document.getElementById('module_id');
    const newTitle = document.querySelector('input[name="new_module[title]"]');

    if (useNew) {
        if (!newTitle || !newTitle.value.trim()) {
            e.preventDefault();
            alert('Please enter a title for the new module.');
            newTitle?.focus();
            return;
        }
    } else if (moduleSelect && !moduleSelect.value) {
        e.preventDefault();
        alert('Please select a module before publishing this lesson.');
        moduleSelect.focus();
        return;
    }

    const selectedOption = document.querySelector('input[name="publish_option"]:checked');
    if (selectedOption && selectedOption.value === 'selected') {
        const checked = document.querySelectorAll('.student-checkbox:checked').length;
        if (checked === 0) {
            e.preventDefault();
            alert('Please select at least one student.');
        }
    }
});
</script>
@endsection