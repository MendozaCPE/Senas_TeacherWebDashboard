@extends('layouts.app')
@section('title', 'Edit Lesson')

@section('content')
<style>
    .hidden { display: none !important; }
    
    #previewOverlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.7);
        z-index: 9999;
        overflow-y: auto;
        display: none;
        padding: 20px;
    }
    #previewOverlay.active { display: block; }
    #previewOverlay .preview-container {
        max-width: 500px;
        margin: 0 auto;
        background: #eaf5fd;
        border-radius: 40px;
        overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        border: 8px solid #1a1a1a;
        position: relative;
        min-height: 780px;
    }
    #previewOverlay .preview-close {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border: none;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        font-size: 24px;
        cursor: pointer;
        box-shadow: 0 4px 20px rgba(0,0,0,0.2);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
    }
    #previewOverlay .preview-close:hover {
        transform: scale(1.1);
        background: #f0f0f0;
    }
    #previewOverlay .preview-loading {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100vh;
        color: white;
        font-size: 18px;
    }
</style>

<div class="max-w-4xl mx-auto">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold text-[#0d326b]">Edit Lesson</h2>
            <p class="text-slate-500 text-sm mt-1">Update your lesson content and quiz questions</p>
        </div>
        <button onclick="window.location.href='{{ route('lessons.index') }}'" 
                class="px-6 py-2.5 border border-slate-200 rounded-xl text-slate-600 hover:bg-slate-50 transition-colors">
            Cancel
        </button>
    </div>

    <form action="{{ route('lessons.update', $lessonData['lesson_id']) }}" method="POST" enctype="multipart/form-data" id="lessonForm">
        @csrf
        @method('PUT')
        
        <!-- Lesson Details -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mb-6">
            <h3 class="font-bold text-[#0d326b] text-lg mb-4">📝 Lesson Details</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Lesson Title *</label>
                    <input type="text" name="title" required value="{{ old('title', $lessonData['title']) }}" 
                           class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Description</label>
                    <textarea name="description" rows="3" 
                              class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">{{ old('description', $lessonData['description']) }}</textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Difficulty</label>
                        <select name="difficulty" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">
                            <option value="beginner" {{ (old('difficulty', $lessonData['difficulty']) == 'beginner') ? 'selected' : '' }}>Beginner</option>
                            <option value="intermediate" {{ (old('difficulty', $lessonData['difficulty']) == 'intermediate') ? 'selected' : '' }}>Intermediate</option>
                            <option value="advanced" {{ (old('difficulty', $lessonData['difficulty']) == 'advanced') ? 'selected' : '' }}>Advanced</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Lesson Type</label>
                        <select name="lesson_type" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">
                            <option value="gesture" {{ (old('lesson_type', $lessonData['lesson_type']) == 'gesture') ? 'selected' : '' }}>Gesture Lesson</option>
                            <option value="interactive" {{ (old('lesson_type', $lessonData['lesson_type']) == 'interactive') ? 'selected' : '' }}>Interactive Lesson</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lesson Content -->
        <div id="contentContainer" class="space-y-4">
            <div class="flex items-center justify-between">
                <h3 class="font-bold text-[#0d326b] text-lg">📖 Lesson Content</h3>
                <button type="button" onclick="addContentCard()" class="text-sm text-[#0d326b] font-semibold hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">add</span> Add Slide
                </button>
            </div>
            <div id="contentCards">
                @if(isset($lessonData['contents']) && count($lessonData['contents']) > 0)
                    @foreach($lessonData['contents'] as $index => $content)
                    <div class="content-card bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-slate-400 step-number">Step {{ $index + 1 }}</span>
                                <span class="text-xs bg-blue-50 text-[#0d326b] px-3 py-1 rounded-full font-semibold">{{ ucfirst($content['content_type'] ?? 'text') }}</span>
                            </div>
                            <button type="button" onclick="removeContentCard(this)" class="text-red-400 hover:text-red-600">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Content Type</label>
                                <select name="contents[{{ $index }}][content_type]" class="content-type w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" onchange="toggleFields(this)">
                                    <option value="text" {{ $content['content_type'] == 'text' ? 'selected' : '' }}>Text</option>
                                    <option value="gesture_demo" {{ $content['content_type'] == 'gesture_demo' ? 'selected' : '' }}>Gesture Demo</option>
                                    <option value="image" {{ $content['content_type'] == 'image' ? 'selected' : '' }}>Image</option>
                                    <option value="video" {{ $content['content_type'] == 'video' ? 'selected' : '' }}>Video</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Title</label>
                                <input type="text" name="contents[{{ $index }}][title]" value="{{ $content['title'] ?? '' }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="e.g., Introduction to FSL Alphabet">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Content</label>
                                <textarea name="contents[{{ $index }}][content_text]" rows="3" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Write your lesson content here...">{{ $content['content_text'] ?? '' }}</textarea>
                            </div>
                            <div class="gesture-field {{ $content['content_type'] == 'gesture_demo' ? '' : 'hidden' }}">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gesture Name</label>
                                <input type="text" name="contents[{{ $index }}][gesture_name]" value="{{ $content['gesture_name'] ?? '' }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="e.g., letter_a">
                            </div>
                            <div class="media-field {{ in_array($content['content_type'], ['image', 'video']) ? '' : 'hidden' }}">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Upload Media</label>
                                @if(isset($content['media_url']) && $content['media_url'])
                                    <div class="mb-2">
                                        <span class="text-sm text-slate-500">Current file:</span>
                                        <a href="{{ asset('storage/' . $content['media_url']) }}" target="_blank" class="text-[#0d326b] hover:underline text-sm">{{ basename($content['media_url']) }}</a>
                                        <input type="hidden" name="contents[{{ $index }}][existing_media]" value="{{ $content['media_url'] }}">
                                    </div>
                                @endif
                                <input type="file" name="contents[{{ $index }}][media]" class="w-full px-4 py-3 border border-slate-200 rounded-xl">
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <!-- Default empty content card -->
                    <div class="content-card bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-slate-400 step-number">Step 1</span>
                                <span class="text-xs bg-blue-50 text-[#0d326b] px-3 py-1 rounded-full font-semibold">Text</span>
                            </div>
                            <button type="button" onclick="removeContentCard(this)" class="text-red-400 hover:text-red-600">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Content Type</label>
                                <select name="contents[0][content_type]" class="content-type w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" onchange="toggleFields(this)">
                                    <option value="text">Text</option>
                                    <option value="gesture_demo">Gesture Demo</option>
                                    <option value="image">Image</option>
                                    <option value="video">Video</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Title</label>
                                <input type="text" name="contents[0][title]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="e.g., Introduction to FSL Alphabet">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Content</label>
                                <textarea name="contents[0][content_text]" rows="3" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Write your lesson content here..."></textarea>
                            </div>
                            <div class="gesture-field hidden">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gesture Name</label>
                                <input type="text" name="contents[0][gesture_name]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="e.g., letter_a">
                            </div>
                            <div class="media-field hidden">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Upload Media</label>
                                <input type="file" name="contents[0][media]" class="w-full px-4 py-3 border border-slate-200 rounded-xl">
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <button type="button" onclick="addContentCard()" class="w-full py-4 border-2 border-dashed border-[#0d326b]/30 rounded-2xl text-[#0d326b] font-semibold hover:bg-[#f0f4ff] transition-colors flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-sm">add</span> Add Another Slide
            </button>
        </div>

        <!-- Quiz Questions -->
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 mt-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-bold text-[#0d326b] text-lg">📝 Quiz Questions</h3>
                <button type="button" onclick="addQuizQuestion()" class="text-sm text-[#0d326b] font-semibold hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">add</span> Add Question
                </button>
            </div>
            <div id="quizQuestions">
                @if(isset($lessonData['quiz']) && count($lessonData['quiz']) > 0)
                    @foreach($lessonData['quiz'] as $index => $q)
                    <div class="quiz-question bg-slate-50 rounded-xl p-4 mb-4 border border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-bold text-slate-500">Question {{ $index + 1 }}</span>
                            <button type="button" onclick="removeQuizQuestion(this)" class="text-red-400 hover:text-red-600">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question</label>
                                <input type="text" name="quiz[{{ $index }}][question]" value="{{ $q['question'] ?? '' }}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Enter your question">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question Type</label>
                                <select name="quiz[{{ $index }}][type]" onchange="handleQuestionTypeChange(this)" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">
                                    <option value="multiple_choice" {{ ($q['type'] ?? 'multiple_choice') == 'multiple_choice' ? 'selected' : '' }}>Multiple Choice</option>
                                    <option value="true_false" {{ ($q['type'] ?? '') == 'true_false' ? 'selected' : '' }}>True / False</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Media (Optional)</label>
                                @if(isset($q['media']) && $q['media'])
                                    <div class="mb-2">
                                        <span class="text-sm text-slate-500">Current file:</span>
                                        <a href="{{ asset('storage/' . $q['media']) }}" target="_blank" class="text-[#0d326b] hover:underline text-sm">{{ basename($q['media']) }}</a>
                                        <input type="hidden" name="quiz[{{ $index }}][existing_media]" value="{{ $q['media'] }}">
                                    </div>
                                @endif
                                <input type="file" name="quiz[{{ $index }}][media]" class="w-full px-4 py-3 border border-slate-200 rounded-xl">
                            </div>
                            <div class="options-container">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Options</label>
                                <div class="space-y-2">
                                    @php
                                        $options = $q['options'] ?? ['', ''];
                                        $correct = $q['correct'] ?? 0;
                                    @endphp
                                    @foreach($options as $optIndex => $option)
                                    <div class="flex items-center gap-2">
                                        <input type="text" name="quiz[{{ $index }}][options][]" value="{{ $option }}" class="flex-1 px-4 py-2.5 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Option {{ chr(65 + $optIndex) }}">
                                        <input type="radio" name="quiz[{{ $index }}][correct]" value="{{ $optIndex }}" {{ $correct == $optIndex ? 'checked' : '' }} class="w-5 h-5 accent-[#0d326b]">
                                        <label class="text-sm text-slate-500">Correct</label>
                                    </div>
                                    @endforeach
                                    <button type="button" onclick="addOption(this)" class="text-sm text-[#0d326b] font-semibold hover:underline">
                                        + Add Option
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                @else
                    <!-- Default empty quiz question -->
                    <div class="quiz-question bg-slate-50 rounded-xl p-4 mb-4 border border-slate-100">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-sm font-bold text-slate-500">Question 1</span>
                            <button type="button" onclick="removeQuizQuestion(this)" class="text-red-400 hover:text-red-600">
                                <span class="material-symbols-outlined text-sm">close</span>
                            </button>
                        </div>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question</label>
                                <input type="text" name="quiz[0][question]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Enter your question">
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question Type</label>
                                <select name="quiz[0][type]" onchange="handleQuestionTypeChange(this)" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="true_false">True / False</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Media (Optional)</label>
                                <input type="file" name="quiz[0][media]" class="w-full px-4 py-3 border border-slate-200 rounded-xl">
                            </div>
                            <div class="options-container">
                                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Options</label>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <input type="text" name="quiz[0][options][]" class="flex-1 px-4 py-2.5 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Option A">
                                        <input type="radio" name="quiz[0][correct]" value="0" class="w-5 h-5 accent-[#0d326b]">
                                        <label class="text-sm text-slate-500">Correct</label>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <input type="text" name="quiz[0][options][]" class="flex-1 px-4 py-2.5 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Option B">
                                        <input type="radio" name="quiz[0][correct]" value="1" class="w-5 h-5 accent-[#0d326b]">
                                        <label class="text-sm text-slate-500">Correct</label>
                                    </div>
                                    <button type="button" onclick="addOption(this)" class="text-sm text-[#0d326b] font-semibold hover:underline">
                                        + Add Option
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <button type="button" onclick="addQuizQuestion()" class="w-full mt-4 py-4 border-2 border-dashed border-[#0d326b]/30 rounded-2xl text-[#0d326b] font-semibold hover:bg-[#f0f4ff] transition-colors flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-sm">add</span> Add Another Question
            </button>
        </div>

        <!-- Form Actions -->
        <div class="flex items-center justify-between mt-8 pt-6 border-t border-slate-200">
            <button type="button" onclick="openPreview()" class="px-6 py-3 border border-[#0d326b] text-[#0d326b] rounded-xl font-semibold hover:bg-[#f0f4ff] transition-colors">
                <span class="material-symbols-outlined text-sm align-middle">visibility</span> Preview
            </button>
            <div class="flex gap-3">
                <button type="submit" name="status" value="draft" class="px-6 py-3 border border-slate-200 text-slate-600 rounded-xl font-semibold hover:bg-slate-50 transition-colors">
                    💾 Save Draft
                </button>
                <button type="submit" name="status" value="published" class="px-8 py-3 bg-[#0d326b] text-white rounded-xl font-semibold hover:bg-[#154188] transition-colors shadow-sm">
                    🚀 Publish Lesson
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Preview Overlay -->
<div id="previewOverlay">
    <button class="preview-close" onclick="closePreview()">✕</button>
    <div class="preview-container" id="previewContent">
        <div class="preview-loading">Loading preview...</div>
    </div>
</div>

<script>
let contentIndex = {{ isset($lessonData['contents']) ? count($lessonData['contents']) : 0 }};
let quizIndex = {{ isset($lessonData['quiz']) ? count($lessonData['quiz']) : 0 }};

function openPreview() {
    const overlay = document.getElementById('previewOverlay');
    const content = document.getElementById('previewContent');
    overlay.classList.add('active');
    content.innerHTML = '<div class="preview-loading">Loading preview...</div>';
    
    const form = document.getElementById('lessonForm');
    const formData = new FormData(form);
    
    // Remove the _method field if it exists (PUT method override)
    if (formData.has('_method')) {
        formData.delete('_method');
    }
    
    fetch('{{ route('lessons.preview') }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
        }
    })
    .then(response => response.text())
    .then(html => {
        content.innerHTML = html;
        // Re-inject scripts
        content.querySelectorAll('script').forEach(oldScript => {
            const newScript = document.createElement('script');
            for (const attr of oldScript.attributes) {
                newScript.setAttribute(attr.name, attr.value);
            }
            newScript.text = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    })
    .catch(error => {
        alert('Error previewing lesson. Please try again.');
        console.error('Preview error:', error);
        closePreview();
    });
}

function closePreview() {
    document.getElementById('previewOverlay').classList.remove('active');
}


document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') closePreview();
});

function toggleFields(select) {
    const card = select.closest('.content-card');
    if (!card) return;
    const gestureField = card.querySelector('.gesture-field');
    const mediaField = card.querySelector('.media-field');
    const typeLabel = card.querySelector('.text-xs.bg-blue-50');
    if (gestureField) gestureField.classList.add('hidden');
    if (mediaField) mediaField.classList.add('hidden');
    if (typeLabel) {
        const map = { 'text': 'Text', 'gesture_demo': 'Gesture', 'image': 'Image', 'video': 'Video' };
        typeLabel.textContent = map[select.value] || 'Text';
    }
    if (select.value === 'gesture_demo') {
        if (gestureField) gestureField.classList.remove('hidden');
    } else if (select.value === 'image' || select.value === 'video') {
        if (mediaField) mediaField.classList.remove('hidden');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.content-type').forEach(function(select) {
        toggleFields(select);
    });
});

function addContentCard() {
    const container = document.getElementById('contentCards');
    const card = document.createElement('div');
    card.className = 'content-card bg-white rounded-2xl p-6 shadow-sm border border-slate-100 relative mt-4';
    card.innerHTML = `
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <span class="text-sm font-bold text-slate-400 step-number">Step ${contentIndex + 1}</span>
                <span class="text-xs bg-blue-50 text-[#0d326b] px-3 py-1 rounded-full font-semibold">Text</span>
            </div>
            <button type="button" onclick="removeContentCard(this)" class="text-red-400 hover:text-red-600">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Content Type</label>
                <select name="contents[${contentIndex}][content_type]" class="content-type w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" onchange="toggleFields(this)">
                    <option value="text">Text</option>
                    <option value="gesture_demo">Gesture Demo</option>
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Title</label>
                <input type="text" name="contents[${contentIndex}][title]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="e.g., Introduction to FSL Alphabet">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Content</label>
                <textarea name="contents[${contentIndex}][content_text]" rows="3" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Write your lesson content here..."></textarea>
            </div>
            <div class="gesture-field hidden">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gesture Name</label>
                <input type="text" name="contents[${contentIndex}][gesture_name]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="e.g., letter_a">
            </div>
            <div class="media-field hidden">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Upload Media</label>
                <input type="file" name="contents[${contentIndex}][media]" class="w-full px-4 py-3 border border-slate-200 rounded-xl">
            </div>
        </div>
    `;
    container.appendChild(card);
    contentIndex++;
    const newSelect = card.querySelector('.content-type');
    toggleFields(newSelect);
}

function removeContentCard(btn) {
    const card = btn.closest('.content-card');
    if (document.querySelectorAll('.content-card').length > 1) {
        card.remove();
        updateStepNumbers();
    }
}

function updateStepNumbers() {
    document.querySelectorAll('.content-card .step-number').forEach((el, i) => {
        el.textContent = `Step ${i + 1}`;
    });
}

function handleQuestionTypeChange(select) {
    const questionDiv = select.closest('.quiz-question');
    if (!questionDiv) return;
    const optionsWrapper = questionDiv.querySelector('.options-container .space-y-2');
    if (!optionsWrapper) return;
    const optionRows = optionsWrapper.querySelectorAll('.flex.items-center.gap-2');
    const addOptionBtn = questionDiv.querySelector('.options-container button');
    if (select.value === 'true_false') {
        while (optionRows.length > 2) {
            const lastRow = optionRows[optionRows.length - 1];
            if (lastRow) lastRow.remove();
        }
        const inputs = optionsWrapper.querySelectorAll('input[type="text"]');
        const radios = optionsWrapper.querySelectorAll('input[type="radio"]');
        if (inputs.length >= 1) inputs[0].value = 'True';
        if (inputs.length >= 2) inputs[1].value = 'False';
        if (radios.length >= 1) radios[0].value = '0';
        if (radios.length >= 2) radios[1].value = '1';
        if (addOptionBtn) addOptionBtn.style.display = 'none';
    } else {
        if (addOptionBtn) addOptionBtn.style.display = 'inline-block';
    }
}

function addQuizQuestion() {
    const container = document.getElementById('quizQuestions');
    const question = document.createElement('div');
    question.className = 'quiz-question bg-slate-50 rounded-xl p-4 mb-4 border border-slate-100';
    question.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <span class="text-sm font-bold text-slate-500">Question ${quizIndex + 1}</span>
            <button type="button" onclick="removeQuizQuestion(this)" class="text-red-400 hover:text-red-600">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question</label>
                <input type="text" name="quiz[${quizIndex}][question]" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Enter your question">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question Type</label>
                <select name="quiz[${quizIndex}][type]" onchange="handleQuestionTypeChange(this)" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all">
                    <option value="multiple_choice">Multiple Choice</option>
                    <option value="true_false">True / False</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Media (Optional)</label>
                <input type="file" name="quiz[${quizIndex}][media]" class="w-full px-4 py-3 border border-slate-200 rounded-xl">
            </div>
            <div class="options-container">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Options</label>
                <div class="space-y-2">
                    <div class="flex items-center gap-2">
                        <input type="text" name="quiz[${quizIndex}][options][]" class="flex-1 px-4 py-2.5 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Option A">
                        <input type="radio" name="quiz[${quizIndex}][correct]" value="0" class="w-5 h-5 accent-[#0d326b]">
                        <label class="text-sm text-slate-500">Correct</label>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="text" name="quiz[${quizIndex}][options][]" class="flex-1 px-4 py-2.5 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Option B">
                        <input type="radio" name="quiz[${quizIndex}][correct]" value="1" class="w-5 h-5 accent-[#0d326b]">
                        <label class="text-sm text-slate-500">Correct</label>
                    </div>
                    <button type="button" onclick="addOption(this)" class="text-sm text-[#0d326b] font-semibold hover:underline">
                        + Add Option
                    </button>
                </div>
            </div>
        </div>
    `;
    container.appendChild(question);
    quizIndex++;
}

function removeQuizQuestion(btn) {
    const question = btn.closest('.quiz-question');
    if (document.querySelectorAll('.quiz-question').length > 1) {
        question.remove();
    }
}

function addOption(btn) {
    const container = btn.closest('.options-container').querySelector('.space-y-2');
    if (!container) return;
    const optionDiv = document.createElement('div');
    optionDiv.className = 'flex items-center gap-2';
    const optionIndex = container.querySelectorAll('.flex.items-center.gap-2').length;
    const firstRadio = container.querySelector('input[type="radio"]');
    const radioName = firstRadio ? firstRadio.name : 'quiz[0][correct]';
    const textName = container.querySelector('input[type="text"]').name;
    optionDiv.innerHTML = `
        <input type="text" name="${textName}" class="flex-1 px-4 py-2.5 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Option ${String.fromCharCode(65 + optionIndex)}">
        <input type="radio" name="${radioName}" value="${optionIndex}" class="w-5 h-5 accent-[#0d326b]">
        <label class="text-sm text-slate-500">Correct</label>
    `;
    container.insertBefore(optionDiv, btn);
}
</script>
@endsection