@extends('layouts.app')
@section('title', 'Lessons')
@section('content')
<!-- Header Section -->
<div class="flex items-start justify-between mb-8">
    <div>
        <h3 class="text-[11px] font-bold text-[#0d326b] tracking-[0.15em] uppercase mb-2">LESSONS MANAGEMENT</h3>
        <h2 class="text-[36px] font-medium text-[#0d326b] leading-tight">Lessons</h2>
    </div>
    
    <button onclick="openNewLessonModal()" 
            class="bg-[#0d326b] hover:bg-[#154188] text-white px-6 py-3 rounded-xl text-[14px] font-semibold transition-colors flex items-center space-x-2 shadow-sm">
        <span class="material-symbols-outlined icon-outline text-[20px]">add</span>
        <span>New Lesson</span>
    </button>
</div>

<div class="flex flex-col lg:flex-row gap-8">
    
    <!-- Left Side: Modules List -->
    <div class="flex-1 flex flex-col space-y-12">
        
        <!-- Module 1 -->
        <div>
            <div class="w-32 bg-[#0d326b] text-white text-[10px] font-bold tracking-[0.15em] px-4 py-2.5 uppercase inline-block" style="clip-path: polygon(0 0, 100% 0, 85% 100%, 0% 100%);">
                MODULE 01
            </div>
            <div class="bg-white rounded-[24px] rounded-tl-none shadow-sm p-8 border border-slate-100">
                <!-- Module Header -->
                <div class="flex justify-between items-start mb-10">
                    <div class="flex items-center space-x-5">
                        <div class="w-16 h-16 bg-[#e0e7ff] text-[#0d326b] rounded-2xl flex items-center justify-center font-black text-lg">ABC</div>
                        <div>
                            <h3 class="text-[20px] font-bold text-[#0d326b] mb-1">Alphabet & Fingerspelling</h3>
                            <p class="text-[13px] text-slate-500 font-medium">Fundamental hand shapes and movement sequences.</p>
                        </div>
                    </div>
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <p class="text-[9px] font-bold text-slate-500 uppercase tracking-widest mb-0.5">Progress</p>
                            <p class="text-[18px] font-bold text-[#0d326b]">88%</p>
                        </div>
                        <button class="w-10 h-10 bg-slate-100 rounded-full flex items-center justify-center hover:bg-slate-200 transition-colors text-slate-600">
                            <span class="material-symbols-outlined icon-outline">expand_more</span>
                        </button>
                    </div>
                </div>

                <!-- Lessons Table -->
                <table class="w-full text-left border-collapse">
                   <thead>
    <tr class="border-b border-slate-100">
        <th class="py-4 px-2 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase w-8"></th>
        <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase">Lesson Title</th>
        <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase">Difficulty</th>
        <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase">Status</th>
        <th class="py-4 px-4 text-[10px] font-bold text-slate-400 tracking-[0.15em] uppercase text-right">Actions</th>
    </tr>
</thead>
                    <tbody class="divide-y divide-slate-50">
    @forelse($lessons as $lesson)
    <tr class="hover:bg-slate-50 transition-colors group cursor-pointer" onclick="window.location.href='{{ route('lessons.view', $lesson->lesson_id) }}'">
        <td class="py-5 px-2 text-slate-300 cursor-move" onclick="event.stopPropagation();">
            <span class="material-symbols-outlined icon-outline text-lg">drag_indicator</span>
        </td>
        <td class="py-5 px-4 font-bold text-[#0d326b] text-[14px]">{{ $lesson->title }}</td>
        <td class="py-5 px-4">
            <span class="px-3 py-1 bg-[#dcfce7] text-[#166534] text-[9px] font-bold rounded uppercase tracking-widest">{{ $lesson->difficulty }}</span>
        </td>
        <td class="py-5 px-4">
            <span class="px-3 py-1 text-[10px] font-bold rounded uppercase tracking-widest 
                {{ $lesson->status === 'published' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                {{ $lesson->status }}
            </span>
        </td>
        <td class="py-5 px-4 text-right">
            <div class="flex items-center justify-end gap-2" onclick="event.stopPropagation();">
                <a href="{{ route('lessons.view', $lesson->lesson_id) }}" 
                   class="text-[#0d326b] hover:text-[#154188] text-sm font-semibold">
                    View
                </a>
                <a href="{{ route('lessons.edit', $lesson->lesson_id) }}" 
                   class="text-[#0d326b] hover:text-[#154188] text-sm font-semibold">
                    Edit
                </a>
            </div>
        </td>
    </tr>
    @empty
    <tr>
        <td colspan="5" class="py-10 text-center text-slate-500">No lessons created yet.</td>
    </tr>
    @endforelse
</tbody>
                </table>

                <div class="mt-6 pt-6 border-t border-slate-100">
                    <button class="flex items-center space-x-2 text-[#0d326b] font-bold text-[13px] hover:underline">
                        <span class="material-symbols-outlined icon-outline text-[18px]">sort</span>
                        <span>Reorder All Lessons</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Module 2 -->
        <div class="opacity-75">
            <div class="w-32 bg-[#e2e8f0] text-slate-600 text-[10px] font-bold tracking-[0.15em] px-4 py-2.5 uppercase inline-block" style="clip-path: polygon(0 0, 100% 0, 85% 100%, 0% 100%);">
                MODULE 02
            </div>
            <div class="bg-[#f1f5f9] rounded-[24px] rounded-tl-none p-8 border border-slate-100 shadow-inner">
                <div class="flex justify-between items-start">
                    <div class="flex items-center space-x-5">
                        <div class="w-16 h-16 bg-[#cbd5e1] text-slate-600 rounded-2xl flex items-center justify-center font-black text-lg">
                            <span class="material-symbols-outlined text-3xl">format_list_numbered</span>
                        </div>
                        <div>
                            <h3 class="text-[20px] font-bold text-[#0d326b] mb-1">Numerical Systems 1-100</h3>
                            <p class="text-[13px] text-slate-500 font-medium">Use numbers in everyday situations, like counting items and showing order.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Right Side: Resources -->
    <div class="w-[340px] flex-shrink-0 flex flex-col space-y-8">
        
        <div class="bg-white rounded-[32px] p-8 shadow-[0_4px_20px_rgba(0,0,0,0.02)] border border-slate-50">
            <div class="flex items-center space-x-2 mb-6">
                <span class="material-symbols-outlined icon-outline text-[20px] text-[#0d326b]">auto_awesome</span>
                <span class="text-[15px] font-bold text-[#0d326b]">Lesson Insights</span>
            </div>
            
            <div class="bg-[#facc15] rounded-[20px] p-5 shadow-sm relative overflow-hidden mb-8">
                <div class="flex items-center space-x-2 mb-3 relative z-10">
                    <span class="material-symbols-outlined icon-outline text-[16px] text-black">trending_down</span>
                    <h4 class="text-[13px] font-black text-black">Difficulty Alert</h4>
                </div>
                <p class="text-[12px] text-black/80 font-medium leading-relaxed relative z-10">
                    Students scored lower in the lesson "Dynamic Letters" compared to Module 01. You can add extra practice to help them improve.
                </p>
            </div>

            <div>
                <h4 class="text-[10px] font-bold tracking-[0.15em] uppercase text-slate-400 mb-4">Module Resources</h4>
                
                <button class="w-full flex items-center justify-between bg-[#f1f5f9] hover:bg-[#e2e8f0] transition-colors p-4 rounded-2xl group">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-white rounded-lg shadow-sm flex items-center justify-center text-[#0d326b]">
                            <span class="material-symbols-outlined icon-outline text-[16px]">picture_as_pdf</span>
                        </div>
                        <span class="text-[13px] font-bold text-[#0d326b]">Printable Flashcards</span>
                    </div>
                    <span class="material-symbols-outlined icon-outline text-[18px] text-slate-400 group-hover:text-[#0d326b]">download</span>
                </button>
            </div>
        </div>

    </div>
</div>

<!-- ✅ NEW LESSON MODAL - Moved OUTSIDE the flex container -->
<div id="newLessonModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden flex items-center justify-center">
    <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl relative">
        <!-- Close button in corner -->
        <button onclick="closeNewLessonModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <span class="material-symbols-outlined text-2xl">close</span>
        </button>
        
        <div class="text-center mb-8">
            <h3 class="text-2xl font-bold text-[#0d326b]">Create New Lesson</h3>
            <p class="text-slate-500 text-sm mt-2">Choose how you want to create your lesson</p>
        </div>
        
        <div class="space-y-4">
            <!-- Option 1: Manual -->
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
            
            <!-- Option 2: AI Generate (placeholder for now) -->
            <button onclick="alert('Coming soon! AI lesson generation will be available in the next update.')" 
                    class="w-full p-6 bg-gradient-to-r from-[#0d326b] to-[#2563EB] rounded-2xl hover:shadow-xl transition-all group opacity-50 cursor-not-allowed">
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

<!-- ✅ JAVASCRIPT -->
<script>
// ── Modal Functions ──
function openNewLessonModal() {
    document.getElementById('newLessonModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden'; // Prevent scrolling
}

function closeNewLessonModal() {
    document.getElementById('newLessonModal').classList.add('hidden');
    document.body.style.overflow = ''; // Restore scrolling
}

function openManualCreate() {
    window.location.href = '{{ route('lessons.create') }}';
}

// ── Close modal on outside click ──
document.addEventListener('click', function(event) {
    const modal = document.getElementById('newLessonModal');
    if (event.target === modal) {
        closeNewLessonModal();
    }
});

// ── Close modal with Escape key ──
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeNewLessonModal();
    }
});

// ── Debug: Check if modal exists ──
console.log('Modal element:', document.getElementById('newLessonModal'));
console.log('Open function exists:', typeof openNewLessonModal === 'function');
</script>

@endsection