@extends('layouts.app')
@section('title', 'Delete Module')
@section('content')

<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center">
                    <span class="material-symbols-outlined text-red-500 text-3xl">warning</span>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Delete Module: <span class="text-[#0d326b]">{{ $module->title }}</span></h2>
                    <p class="text-sm text-slate-500">{{ $module->lessons_count }} lesson{{ $module->lessons_count !== 1 ? 's' : '' }} in this module</p>
                </div>
            </div>
        </div>

        <form id="deleteModuleForm" class="p-6 space-y-6">
            @csrf
            @method('DELETE')
            
            <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4">
                <p class="text-sm text-amber-800 font-medium flex items-start gap-2">
                    <span class="material-symbols-outlined text-amber-600 text-[20px]">info</span>
                    <span>Choose what happens to the lessons currently inside this module.</span>
                </p>
            </div>

            <div class="space-y-4">
                <h3 class="font-bold text-slate-700 text-sm">What should happen to the lessons in this module?</h3>

                <!-- Option 1: Move to Unassigned (Recommended) -->
                <label class="block p-4 rounded-2xl border-2 border-slate-200 hover:border-[#0d326b] cursor-pointer transition has-[:checked]:border-[#0d326b] has-[:checked]:bg-blue-50/50">
                    <div class="flex items-start gap-3">
                        <input type="radio" name="lesson_action" value="move_to_unassigned" class="mt-1 w-4 h-4 text-[#0d326b] accent-[#0d326b]" checked>
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[#0d326b] text-[18px]">move_to_inbox</span>
                                <span class="font-bold text-[#0d326b]">Move lessons to Unassigned</span>
                                <span class="text-[10px] font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">Recommended</span>
                            </div>
                            <p class="text-sm text-slate-500 mt-1">All lessons will be moved to the "Unassigned" section where you can later reassign them to other modules.</p>
                            <p class="text-xs text-slate-400 font-medium mt-1">✅ Lessons remain accessible · ✅ Can be reassigned later</p>
                        </div>
                    </div>
                </label>

            <!-- Option 2: Delete all lessons (HARD DELETE) -->
<label class="block p-4 rounded-2xl border-2 border-slate-200 hover:border-red-300 cursor-pointer transition has-[:checked]:border-red-500 has-[:checked]:bg-red-50/50">
    <div class="flex items-start gap-3">
        <input type="radio" name="lesson_action" value="delete" class="mt-1 w-4 h-4 text-red-500 accent-red-500">
        <div class="flex-1">
            <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-red-500 text-[18px]">delete_forever</span>
                <span class="font-bold text-red-600">Permanently delete all lessons</span>
            </div>
            <p class="text-sm text-slate-500 mt-1">All lessons in this module will be <strong class="text-red-600">permanently deleted</strong>.</p>
            <div class="mt-2 p-2 bg-red-50 rounded-lg border border-red-200">
                <p class="text-xs text-red-700 font-medium flex items-start gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">warning</span>
                    <span>This will also delete all student quiz attempts, progress data, and assignments associated with these lessons. This action <strong>cannot be undone</strong>.</span>
                </p>
            </div>
        </div>
    </div>
</label>
            </div>

            <div class="flex gap-3 pt-4 border-t border-slate-100">
                <a href="{{ route('lessons.index') }}" 
                   class="flex-1 py-3 border border-slate-200 rounded-2xl text-slate-600 font-semibold hover:bg-slate-50 transition-colors text-center">
                    Cancel
                </a>
                <button type="submit" 
                        id="deleteModuleBtn"
                        class="flex-1 py-3 bg-red-500 hover:bg-red-600 text-white font-semibold rounded-2xl transition-colors">
                    Delete Module
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('deleteModuleForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const btn = document.getElementById('deleteModuleBtn');
    btn.disabled = true;
    btn.textContent = 'Deleting...';
    
    const formData = new FormData(this);
    const lessonAction = formData.get('lesson_action');
    
    fetch(`/modules/{{ $module->module_id }}/delete-with-options`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ lesson_action: lessonAction })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.href = data.redirect || '{{ route('lessons.index') }}';
        } else {
            alert(data.message || 'Failed to delete module');
            btn.disabled = false;
            btn.textContent = 'Delete Module';
        }
    })
    .catch(() => {
        alert('Network error. Please try again.');
        btn.disabled = false;
        btn.textContent = 'Delete Module';
    });
});
</script>

@endsection