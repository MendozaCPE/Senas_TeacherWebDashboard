@extends('layouts.admin')
@section('title', 'Publish Default Lesson')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

<style>
    .section-card {
        background: white;
        border-radius: 24px;
        padding: 28px;
        box-shadow: 0 4px 20px rgba(13, 50, 107, 0.05);
        border: 1px solid rgba(13, 50, 107, 0.06);
        margin-bottom: 24px;
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
    .field-input, .field-select {
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
    .field-input:focus, .field-select:focus {
        border-color: #0d326b;
        box-shadow: 0 0 0 4px rgba(13, 50, 107, 0.08);
    }
</style>

<div class="max-w-3xl mx-auto py-6">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-black text-[#0d326b]">Publish to Default Curriculum</h1>
            <p class="text-slate-500 text-sm font-medium mt-1">This lesson will be cloned to all new teachers.</p>
        </div>
        <a href="{{ route('admin.lesson-templates.index') }}" 
           class="px-5 py-2.5 rounded-xl border border-slate-200 bg-white text-slate-700 font-bold text-sm hover:bg-slate-50 transition-all">
            ← Back to Templates
        </a>
    </div>

    @if(session('error'))
        <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm font-semibold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-5">
            <ul class="text-xs text-rose-700 space-y-1 list-disc pl-5 font-medium">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="section-card bg-gradient-to-r from-white via-slate-50 to-white border-l-4 border-l-[#0d326b]">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-[#0d326b]/10 text-[#0d326b] flex items-center justify-center flex-shrink-0">
                <span class="material-symbols-outlined text-[28px]">auto_stories</span>
            </div>
            <div>
                <h2 class="text-xl font-extrabold text-[#0d326b]">{{ $lesson->title }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">{{ $lesson->description ?: 'No description provided.' }}</p>
                <div class="flex items-center gap-2 mt-2">
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-[#0d326b] border border-blue-100">
                        {{ ucfirst($lesson->lesson_type) }}
                    </span>
                    <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-100">
                        {{ ucfirst($lesson->difficulty) }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ route('admin.lesson-templates.publish', $lesson->hash_id) }}" method="POST">
        @csrf

        <div class="section-card">
            <div class="flex items-center gap-3 mb-5">
                <div class="w-10 h-10 rounded-xl bg-blue-50 text-[#0d326b] flex items-center justify-center">
                    <span class="material-symbols-outlined">view_module</span>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-[#0d326b]">Module Assignment</h3>
                    <p class="text-sm text-slate-500">Choose where this lesson belongs in the default curriculum.</p>
                </div>
            </div>

            <div class="space-y-4">
                <!-- Existing Module -->
                <div class="option-card" id="cardModuleExisting">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" name="module_action" value="existing" id="moduleExisting"
                               {{ old('module_action', ($lesson->module_id || $modules->isNotEmpty()) ? 'existing' : 'new') === 'existing' ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-[#0d326b] border-slate-300 focus:ring-[#0d326b]">
                        <div class="flex-1">
                            <span class="text-sm font-bold text-slate-800 block">Assign to existing default module</span>
                            <span class="text-xs text-slate-500 block">Add this lesson to an existing default module.</span>
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

                <!-- New Module -->
                <div class="option-card" id="cardModuleNew">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="radio" name="module_action" value="new" id="moduleNew"
                               {{ old('module_action', ($lesson->module_id || $modules->isNotEmpty()) ? 'existing' : 'new') === 'new' ? 'checked' : '' }}
                               class="mt-1 h-4 w-4 text-[#0d326b] border-slate-300 focus:ring-[#0d326b]">
                        <div class="flex-1">
                            <span class="text-sm font-bold text-slate-800 block">Create a new default module</span>
                            <span class="text-xs text-slate-500 block">Create a brand new module in the default curriculum.</span>
                            <div id="newModuleBlock" class="mt-3 space-y-3 max-w-md {{ old('module_action') === 'new' ? '' : 'hidden' }}">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Module Title *</label>
                                    <input type="text" name="new_module[title]" value="{{ old('new_module.title') }}"
                                           placeholder="e.g. FSL Basics &amp; Alphabet" class="field-input">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Description</label>
                                    <textarea name="new_module[description]" rows="2" 
                                              class="field-input" placeholder="Brief explanation of module goals...">{{ old('new_module.description') }}</textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 mb-1">Mastery Level</label>
                                    <select name="new_module[mastery_level]" class="field-select">
                                        <option value="beginner" {{ old('new_module.mastery_level') === 'beginner' ? 'selected' : '' }}>🌱 Beginner</option>
                                        <option value="intermediate" {{ old('new_module.mastery_level') === 'intermediate' ? 'selected' : '' }}>⚡ Intermediate</option>
                                        <option value="advanced" {{ old('new_module.mastery_level') === 'advanced' ? 'selected' : '' }}>🔥 Advanced</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>
            </div>
        </div>

        {{-- Info Box: What happens when you publish --}}
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-5 mb-6 flex items-start gap-3">
            <span class="material-symbols-outlined text-amber-600 text-[22px] flex-shrink-0">info</span>
            <div>
                <p class="text-sm font-bold text-amber-800">What happens when you publish?</p>
                <ul class="text-xs text-amber-700 space-y-1 mt-1 list-disc pl-4">
                    <li>The lesson becomes part of the <strong>default curriculum</strong></li>
                    <li>All <strong>new teachers</strong> will get a copy of this lesson when they sign up</li>
                    <li>Existing teachers <strong>won't</strong> get this lesson until you click <strong>"Push to All Teachers"</strong></li>
                    <li>You can always edit this lesson later and push updates to all teachers</li>
                </ul>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 pt-2">
            <a href="{{ route('admin.lesson-templates.index') }}" 
               class="px-6 py-3 border border-slate-200 rounded-xl text-slate-600 font-bold text-sm hover:bg-slate-100 transition-colors">
                Cancel
            </a>
            <button type="submit" class="px-8 py-3 bg-[#0d326b] hover:bg-[#154188] text-white font-extrabold rounded-xl text-sm transition-all shadow-md flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">rocket_launch</span>
                Publish to Default Curriculum
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const moduleExisting = document.getElementById('moduleExisting');
    const moduleNew = document.getElementById('moduleNew');
    const existingModuleBlock = document.getElementById('existingModuleBlock');
    const newModuleBlock = document.getElementById('newModuleBlock');

    function toggleModuleBlocks() {
        const useNew = moduleNew && moduleNew.checked;
        if (existingModuleBlock) existingModuleBlock.style.display = useNew ? 'none' : 'block';
        if (newModuleBlock) newModuleBlock.classList.toggle('hidden', !useNew);
        
        document.getElementById('cardModuleExisting')?.classList.toggle('selected', !useNew);
        document.getElementById('cardModuleNew')?.classList.toggle('selected', useNew);
    }

    if (moduleExisting) moduleExisting.addEventListener('change', toggleModuleBlocks);
    if (moduleNew) moduleNew.addEventListener('change', toggleModuleBlocks);
    toggleModuleBlocks();
});
</script>
@endsection