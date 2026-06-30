@extends('layouts.app')
@section('title', 'Create New Lesson')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">
<style>
    body, .max-w-4xl * { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }

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
        border-radius: 22px;
        padding: 26px;
        box-shadow: 0 2px 16px rgba(15,49,114,0.07);
        border: 1px solid rgba(15,49,114,0.06);
        margin-bottom: 24px;
    }
    .section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 18px;
    }
    .section-title {
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 17px;
        font-weight: 800;
        color: #0f3172;
    }
    .section-icon {
        width: 36px;
        height: 36px;
        border-radius: 11px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 17px;
        flex-shrink: 0;
    }
    .field-label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #334155;
        margin-bottom: 6px;
    }
    .field-input, .field-select, .field-textarea {
        width: 100%;
        padding: 12px 16px;
        border: 1.5px solid #E5EAF2;
        border-radius: 14px;
        outline: none;
        transition: all 0.2s;
        font-size: 14px;
        background: #FAFBFD;
    }
    .field-input:focus, .field-select:focus, .field-textarea:focus {
        border-color: #1848c8;
        background: white;
        box-shadow: 0 0 0 4px rgba(24,72,200,0.08);
    }
    .badge-pill {
        font-size: 11px;
        font-weight: 800;
        padding: 4px 12px;
        border-radius: 99px;
        letter-spacing: 0.3px;
    }
    .step-circle {
        width: 30px;
        height: 30px;
        border-radius: 10px;
        background: #1848c8;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 13px;
        flex-shrink: 0;
    }
    .content-card {
        border: 1.5px solid #E5EAF2;
        border-radius: 18px;
        padding: 22px;
        background: #FAFBFD;
        position: relative;
        transition: border-color 0.2s;
    }
    .content-card:focus-within { border-color: rgba(24,72,200,0.3); }
    .icon-btn-remove {
        width: 30px;
        height: 30px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(239,68,68,0.08);
        color: #EF4444;
        transition: all 0.2s;
    }
    .icon-btn-remove:hover { background: rgba(239,68,68,0.15); }

    .dashed-add-btn {
        width: 100%;
        padding: 16px;
        border: 2px dashed rgba(24,72,200,0.25);
        border-radius: 18px;
        color: #1848c8;
        font-weight: 700;
        font-size: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
        background: rgba(24,72,200,0.02);
    }
    .dashed-add-btn:hover { background: rgba(24,72,200,0.06); border-color: rgba(24,72,200,0.4); }

    .quiz-question {
        border: 1.5px solid #E5EAF2;
        border-radius: 18px;
        padding: 22px;
        background: #FAFBFD;
        margin-bottom: 16px;
    }

    /* --- Option rows with image upload --- */
    .option-row {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        background: white;
        border: 1.5px solid #E5EAF2;
        border-radius: 14px;
        padding: 12px;
        margin-bottom: 8px;
    }
    .option-letter {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        background: rgba(15,49,114,0.08);
        color: #1848c8;
        font-weight: 800;
        font-size: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 4px;
    }
    .option-body { flex: 1; display: flex; flex-direction: column; gap: 8px; }
    .option-text-input {
        width: 100%;
        padding: 10px 14px;
        border: 1.5px solid #E5EAF2;
        border-radius: 11px;
        font-size: 14px;
        outline: none;
    }
    .option-text-input:focus { border-color: #1848c8; }
    .option-image-row {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .option-image-input {
        font-size: 12px;
        flex: 1;
        color: #6B7280;
    }
    .option-image-preview {
        width: 44px;
        height: 44px;
        border-radius: 9px;
        object-fit: cover;
        border: 1.5px solid #E5EAF2;
        display: none;
        flex-shrink: 0;
    }
    .option-correct-row {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-shrink: 0;
        padding-top: 4px;
    }
    .option-correct-row label {
        font-size: 12px;
        font-weight: 600;
        color: #6B7280;
    }
    .option-correct-row input[type="radio"] {
        width: 18px;
        height: 18px;
        accent-color: #1848c8;
    }
    .option-remove-btn {
        background: none;
        border: none;
        color: #CBD5E1;
        cursor: pointer;
        flex-shrink: 0;
        margin-top: 4px;
        transition: color 0.2s;
    }
    .option-remove-btn:hover { color: #EF4444; }

    .form-footer {
        position: sticky;
        bottom: 0;
        background: white;
        border-top: 1px solid #E5EAF2;
        padding: 18px 26px;
        border-radius: 22px;
        box-shadow: 0 -4px 20px rgba(15,49,114,0.06);
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 24px;
    }
    .btn-primary {
        background: #1848c8;
        color: white;
        padding: 13px 28px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 14px;
        transition: all 0.2s;
        box-shadow: 0 5px 16px rgba(24,72,200,0.25);
    }
    .btn-primary:hover { background: #0f3172; transform: translateY(-1px); }
    .btn-ghost {
        background: white;
        color: #475569;
        padding: 13px 22px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 14px;
        border: 1.5px solid #E5EAF2;
        transition: all 0.2s;
    }
    .btn-ghost:hover { background: #F8FAFC; }
    .btn-outline-blue {
        background: white;
        color: #1848c8;
        padding: 13px 22px;
        border-radius: 14px;
        font-weight: 700;
        font-size: 14px;
        border: 1.5px solid #1848c8;
        transition: all 0.2s;
    }
    .btn-outline-blue:hover { background: rgba(24,72,200,0.06); }

    /* ---- Mobile-only preview overlay ---- */
    #previewOverlay {
        position: fixed; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(15,23,42,0.7); z-index: 9999; overflow-y: auto; display: none; padding: 20px;
    }
    #previewOverlay.active { display: flex; align-items: flex-start; justify-content: center; }
    #previewOverlay .preview-container {
    width: auto;
    max-width: 900px;
    margin: 20px auto;
    background: transparent;
    border-radius: 0;
    overflow: visible;
    box-shadow: none;
    border: none;
    position: relative;
    min-height: auto;
}
    #previewOverlay .preview-loading {
    display: flex; align-items: center; justify-content: center; height: 400px; color: white; font-size: 16px; font-weight: 600;
}
</style>

<div class="max-w-4xl mx-auto pb-10">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h2 class="text-3xl font-bold text-[#0f3172]">Create New Lesson</h2>
            <p class="text-slate-500 text-sm mt-1">Build your lesson content and quiz questions</p>
        </div>
        <button onclick="window.location.href='{{ route('lessons.index') }}'" class="btn-ghost">
            Cancel
        </button>
    </div>

    <form action="{{ route('lessons.store') }}" method="POST" enctype="multipart/form-data" id="lessonForm">
        @csrf

        @if ($errors->any())
            <div class="section-card" style="border-color:#FCA5A5; background:#FEF2F2;">
                <p class="text-sm font-bold text-red-700 mb-2">Please fix the following:</p>
                <ul class="text-sm text-red-600 list-disc pl-5 space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- ============ LESSON DETAILS ============ -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-title">
                    <div class="section-icon" style="background: rgba(24,72,200,0.1); color:#1848c8;">📝</div>
                    Lesson Details
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="field-label">Lesson Title *</label>
                    <input type="text" name="title" required class="field-input">
                </div>
                <div>
                    <label class="field-label">Description</label>
                    <textarea name="description" rows="3" class="field-textarea"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="field-label">Difficulty</label>
                        <select name="difficulty" class="field-select">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                    <div>
                        <label class="field-label">Lesson Type</label>
                        <select name="lesson_type" class="field-select">
                            <option value="gesture">Gesture Lesson</option>
                            <option value="interactive">Interactive Lesson</option>
                        </select>
                    </div>
                </div>
                <p class="text-xs text-slate-400">Choose a module now or leave unassigned until publish.</p>
            </div>
        </div>

        <!-- ============ MODULE ============ -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-title">
                    <div class="section-icon" style="background: rgba(99,102,241,0.12); color:#6366F1;">📁</div>
                    Module
                </div>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="field-label">Module assignment</label>
                    <select name="module_action" id="moduleAction" class="field-select" onchange="toggleModuleFields()">
                        <option value="none" {{ old('module_action', 'none') === 'none' ? 'selected' : '' }}>No module (assign when publishing)</option>
                        <option value="existing" {{ old('module_action') === 'existing' ? 'selected' : '' }}>Use existing module</option>
                        <option value="new" {{ old('module_action') === 'new' ? 'selected' : '' }}>Create new module</option>
                    </select>
                </div>
                <div id="existingModuleFields" class="hidden">
                    <label class="field-label">Select module</label>
                    <select name="module_id" id="moduleIdSelect" class="field-select">
                        <option value="">Choose a module</option>
                        @foreach($modules as $module)
                            <option value="{{ $module->module_id }}" {{ (string) old('module_id') === (string) $module->module_id ? 'selected' : '' }}>
                                {{ $module->title }}
                            </option>
                        @endforeach
                    </select>
                    @if($modules->isEmpty())
                        <p class="text-xs text-amber-600 mt-2">No modules yet — choose “Create new module” above.</p>
                    @endif
                </div>
                <div id="newModuleFields" class="hidden space-y-3">
                    <div>
                        <label class="field-label">New module title *</label>
                        <input type="text" name="new_module[title]" id="newModuleTitle" value="{{ old('new_module.title') }}" class="field-input" placeholder="e.g., FSL Alphabet Basics">
                    </div>
                    <div>
                        <label class="field-label">New module description</label>
                        <textarea name="new_module[description]" rows="2" class="field-textarea" placeholder="Optional description">{{ old('new_module.description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============ LESSON CONTENT ============ -->
        <div class="section-card" id="contentContainer">
            <div class="section-header">
                <div class="section-title">
                    <div class="section-icon" style="background: rgba(5,150,105,0.1); color:#059669;">📖</div>
                    Lesson Content
                </div>
                <button type="button" onclick="addContentCard()" class="text-sm text-[#1848c8] font-bold hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">add</span> Add Slide
                </button>
            </div>
            <div id="contentCards" class="space-y-4">
                <div class="content-card">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-3">
                            <div class="step-circle step-number">1</div>
                            <span class="badge-pill" style="background: rgba(24,72,200,0.1); color:#1848c8;">Text</span>
                        </div>
                        <button type="button" onclick="removeContentCard(this)" class="icon-btn-remove">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="field-label">Content Type</label>
                            <select name="contents[0][content_type]" class="content-type field-select" onchange="toggleFields(this)">
                                <option value="text">Text</option>
                                <option value="gesture_demo">Gesture Demo</option>
                                <option value="image">Image</option>
                                <option value="video">Video</option>
                            </select>
                        </div>
                        <div>
                            <label class="field-label">Title</label>
                            <input type="text" name="contents[0][title]" class="field-input" placeholder="e.g., Introduction to FSL Alphabet">
                        </div>
                        <div>
                            <label class="field-label">Content</label>
                            <textarea name="contents[0][content_text]" rows="3" class="field-textarea" placeholder="Write your lesson content here..."></textarea>
                        </div>
                        <div class="gesture-field hidden">
                            <label class="field-label">Gesture Name</label>
                            <input type="text" name="contents[0][gesture_name]" class="field-input" placeholder="e.g., letter_a">
                        </div>
                        <div class="media-field hidden">
                            <label class="field-label">Upload Media</label>
                            <input type="file" name="contents[0][media]" class="field-input">
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" onclick="addContentCard()" class="dashed-add-btn mt-4">
                <span class="material-symbols-outlined text-sm">add</span> Add Another Slide
            </button>
        </div>

        <!-- ============ QUIZ QUESTIONS ============ -->
        <div class="section-card">
            <div class="section-header">
                <div class="section-title">
                    <div class="section-icon" style="background: rgba(245,158,11,0.12); color:#D97706;">📝</div>
                    Quiz Questions
                </div>
                <button type="button" onclick="addQuizQuestion()" class="text-sm text-[#1848c8] font-bold hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-sm">add</span> Add Question
                </button>
            </div>
            <div id="quizQuestions">
                <div class="quiz-question">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="step-circle" style="background:#D97706;">1</div>
                            <span class="text-sm font-bold text-slate-500 question-label">Question 1</span>
                        </div>
                        <button type="button" onclick="removeQuizQuestion(this)" class="icon-btn-remove">
                            <span class="material-symbols-outlined text-sm">close</span>
                        </button>
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="field-label">Question</label>
                            <input type="text" name="quiz[0][question]" class="field-input" placeholder="Enter your question">
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="field-label">Question Type</label>
                                <select name="quiz[0][type]" onchange="handleQuestionTypeChange(this)" class="field-select question-type">
                                    <option value="multiple_choice">Multiple Choice</option>
                                    <option value="true_false">True / False</option>
                                </select>
                            </div>
                            <div>
                                <label class="field-label">Question Media (Optional)</label>
                                <input type="file" name="quiz[0][media]" accept="image/*" class="field-input">
                            </div>
                        </div>

                        <div class="options-container">
                            <label class="field-label">Answer Options</label>
                            <p class="text-xs text-slate-400 mb-2">Each option can have text and/or an image (e.g. for FSL hand-sign choices).</p>
                            <div class="space-y-2 options-list">
                                <div class="option-row">
                                    <div class="option-letter">A</div>
                                    <div class="option-body">
                                        <input type="text" name="quiz[0][options][0][text]" class="option-text-input" placeholder="Option A text">
                                        <div class="option-image-row">
                                            <input type="file" name="quiz[0][options][0][image]" accept="image/*" class="option-image-input" onchange="previewOptionImage(this)">
                                            <img class="option-image-preview" src="" alt="">
                                        </div>
                                    </div>
                                    <div class="option-correct-row">
                                        <input type="radio" name="quiz[0][correct]" value="0">
                                        <label>Correct</label>
                                    </div>
                                    <button type="button" class="option-remove-btn" onclick="removeOption(this)">
                                        <span class="material-symbols-outlined text-sm">close</span>
                                    </button>
                                </div>
                                <div class="option-row">
                                    <div class="option-letter">B</div>
                                    <div class="option-body">
                                        <input type="text" name="quiz[0][options][1][text]" class="option-text-input" placeholder="Option B text">
                                        <div class="option-image-row">
                                            <input type="file" name="quiz[0][options][1][image]" accept="image/*" class="option-image-input" onchange="previewOptionImage(this)">
                                            <img class="option-image-preview" src="" alt="">
                                        </div>
                                    </div>
                                    <div class="option-correct-row">
                                        <input type="radio" name="quiz[0][correct]" value="1">
                                        <label>Correct</label>
                                    </div>
                                    <button type="button" class="option-remove-btn" onclick="removeOption(this)">
                                        <span class="material-symbols-outlined text-sm">close</span>
                                    </button>
                                </div>
                            </div>
                            <button type="button" onclick="addOption(this)" class="text-sm text-[#1848c8] font-bold hover:underline mt-2">
                                + Add Option
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <button type="button" onclick="addQuizQuestion()" class="dashed-add-btn mt-2">
                <span class="material-symbols-outlined text-sm">add</span> Add Another Question
            </button>
        </div>

        <div class="form-footer">
            <button type="button" onclick="openPreview()" class="btn-outline-blue flex items-center gap-2">
                <span class="material-symbols-outlined text-sm">visibility</span> Preview
            </button>
            <div class="flex gap-3">
                <button type="submit" name="status" value="draft" class="btn-ghost">💾 Save Draft</button>
                <button type="submit" name="status" value="published" class="btn-primary">🚀 Publish Lesson</button>
            </div>
        </div>
    </form>
</div>

<!-- Mobile-only preview overlay (phone frame). No web/desktop preview mode. -->
<div id="previewOverlay">
    <button class="preview-close" onclick="closePreview()" style="position:fixed; top:20px; right:20px; background:white; border:none; border-radius:50%; width:50px; height:50px; font-size:24px; cursor:pointer; box-shadow:0 4px 20px rgba(0,0,0,0.2); z-index:10000; display:flex; align-items:center; justify-content:center;">✕</button>
    <div class="preview-container" id="previewContent">
        <div class="preview-loading">Loading preview...</div>
    </div>
</div>

<script>
let contentIndex = 1;
let quizIndex = 1;

function openPreview() {
    const overlay = document.getElementById('previewOverlay');
    const content = document.getElementById('previewContent');
    overlay.classList.add('active');
    content.innerHTML = '<div class="preview-loading">Loading preview...</div>';

    const form = document.getElementById('lessonForm');
    const formData = new FormData(form);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value;

    fetch('{{ route('lessons.preview') }}', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'text/html',
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Preview request failed (HTTP ' + response.status + ')');
        }
        return response.text();
    })
    .then(html => {
        content.innerHTML = html;
        content.querySelectorAll('script').forEach(oldScript => {
            const newScript = document.createElement('script');
            for (const attr of oldScript.attributes) newScript.setAttribute(attr.name, attr.value);
            newScript.text = oldScript.textContent;
            oldScript.parentNode.replaceChild(newScript, oldScript);
        });
    })
    .catch(error => {
        content.innerHTML = '<div class="preview-loading" style="color:#FCA5A5;">Preview failed. Please try again.</div>';
        console.error('Preview error:', error);
    });
}

function toggleModuleFields() {
    const action = document.getElementById('moduleAction')?.value || 'none';
    const existing = document.getElementById('existingModuleFields');
    const fresh = document.getElementById('newModuleFields');
    const moduleSelect = document.getElementById('moduleIdSelect');
    const newTitle = document.getElementById('newModuleTitle');

    if (existing) existing.classList.toggle('hidden', action !== 'existing');
    if (fresh) fresh.classList.toggle('hidden', action !== 'new');

    if (moduleSelect) {
        moduleSelect.disabled = action !== 'existing';
        moduleSelect.required = action === 'existing';
        if (action !== 'existing') moduleSelect.value = '';
    }
    if (newTitle) {
        newTitle.required = action === 'new';
        if (action !== 'new') newTitle.value = '';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.content-type').forEach(toggleFields);
    toggleModuleFields();

    document.getElementById('lessonForm')?.addEventListener('submit', function(e) {
        const action = document.getElementById('moduleAction')?.value;
        if (action === 'existing') {
            const moduleSelect = document.getElementById('moduleIdSelect');
            if (!moduleSelect?.value) {
                e.preventDefault();
                alert('Please select a module or choose a different module option.');
                moduleSelect?.focus();
            }
        }
        if (action === 'new') {
            const newTitle = document.getElementById('newModuleTitle');
            if (!newTitle?.value.trim()) {
                e.preventDefault();
                alert('Please enter a title for the new module.');
                newTitle?.focus();
            }
        }
    });
});

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
    const typeLabel = card.querySelector('.badge-pill');
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

function addContentCard() {
    const container = document.getElementById('contentCards');
    const card = document.createElement('div');
    card.className = 'content-card';
    card.innerHTML = `
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="step-circle step-number">${contentIndex + 1}</div>
                <span class="badge-pill" style="background: rgba(24,72,200,0.1); color:#1848c8;">Text</span>
            </div>
            <button type="button" onclick="removeContentCard(this)" class="icon-btn-remove">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="field-label">Content Type</label>
                <select name="contents[${contentIndex}][content_type]" class="content-type field-select" onchange="toggleFields(this)">
                    <option value="text">Text</option>
                    <option value="gesture_demo">Gesture Demo</option>
                    <option value="image">Image</option>
                    <option value="video">Video</option>
                </select>
            </div>
            <div>
                <label class="field-label">Title</label>
                <input type="text" name="contents[${contentIndex}][title]" class="field-input" placeholder="e.g., Introduction to FSL Alphabet">
            </div>
            <div>
                <label class="field-label">Content</label>
                <textarea name="contents[${contentIndex}][content_text]" rows="3" class="field-textarea" placeholder="Write your lesson content here..."></textarea>
            </div>
            <div class="gesture-field hidden">
                <label class="field-label">Gesture Name</label>
                <input type="text" name="contents[${contentIndex}][gesture_name]" class="field-input" placeholder="e.g., letter_a">
            </div>
            <div class="media-field hidden">
                <label class="field-label">Upload Media</label>
                <input type="file" name="contents[${contentIndex}][media]" class="field-input">
            </div>
        </div>
    `;
    container.appendChild(card);
    contentIndex++;
    toggleFields(card.querySelector('.content-type'));
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
        el.textContent = i + 1;
    });
}

function getQuizQuestionIndex(questionDiv) {
    const questionInput = questionDiv.querySelector('input[name*="[question]"]');
    if (!questionInput) return 0;
    const match = questionInput.name.match(/^quiz\[(\d+)\]/);
    return match ? parseInt(match[1], 10) : 0;
}

function handleQuestionTypeChange(select) {
    const questionDiv = select.closest('.quiz-question');
    if (!questionDiv) return;
    const optionsList = questionDiv.querySelector('.options-list');
    const addOptionBtn = questionDiv.querySelector('.options-container > button');
    const qIndex = getQuizQuestionIndex(questionDiv);

    if (select.value === 'true_false') {
        const rows = optionsList.querySelectorAll('.option-row');
        while (rows.length > 2) rows[rows.length - 1].remove();
        const textInputs = optionsList.querySelectorAll('.option-text-input');
        const radios = optionsList.querySelectorAll('input[type="radio"]');
        if (textInputs[0]) textInputs[0].value = 'True';
        if (textInputs[1]) textInputs[1].value = 'False';
        if (radios[0]) radios[0].value = '0';
        if (radios[1]) radios[1].value = '1';
        optionsList.querySelectorAll('.option-image-row, .option-remove-btn').forEach(el => el.style.visibility = 'hidden');
        if (addOptionBtn) addOptionBtn.style.display = 'none';
    } else {
        optionsList.querySelectorAll('.option-image-row, .option-remove-btn').forEach(el => el.style.visibility = 'visible');
        if (addOptionBtn) addOptionBtn.style.display = 'inline-block';
    }
}

function previewOptionImage(input) {
    const row = input.closest('.option-body');
    const img = row.querySelector('.option-image-preview');
    if (input.files && input.files[0]) {
        img.src = URL.createObjectURL(input.files[0]);
        img.style.display = 'block';
    } else {
        img.style.display = 'none';
    }
}

function buildOptionRow(qIndex, optIndex) {
    const letter = String.fromCharCode(65 + optIndex);
    const row = document.createElement('div');
    row.className = 'option-row';
    row.innerHTML = `
        <div class="option-letter">${letter}</div>
        <div class="option-body">
            <input type="text" name="quiz[${qIndex}][options][${optIndex}][text]" class="option-text-input" placeholder="Option ${letter} text">
            <div class="option-image-row">
                <input type="file" name="quiz[${qIndex}][options][${optIndex}][image]" accept="image/*" class="option-image-input" onchange="previewOptionImage(this)">
                <img class="option-image-preview" src="" alt="">
            </div>
        </div>
        <div class="option-correct-row">
            <input type="radio" name="quiz[${qIndex}][correct]" value="${optIndex}">
            <label>Correct</label>
        </div>
        <button type="button" class="option-remove-btn" onclick="removeOption(this)">
            <span class="material-symbols-outlined text-sm">close</span>
        </button>
    `;
    return row;
}

function relabelOptions(optionsList, qIndex) {
    optionsList.querySelectorAll('.option-row').forEach((row, i) => {
        const letter = String.fromCharCode(65 + i);
        row.querySelector('.option-letter').textContent = letter;
        const textInput = row.querySelector('.option-text-input');
        textInput.name = `quiz[${qIndex}][options][${i}][text]`;
        textInput.placeholder = `Option ${letter} text`;
        row.querySelector('.option-image-input').name = `quiz[${qIndex}][options][${i}][image]`;
        row.querySelector('input[type="radio"]').name = `quiz[${qIndex}][correct]`;
        row.querySelector('input[type="radio"]').value = i;
    });
}

function addOption(btn) {
    const questionDiv = btn.closest('.quiz-question');
    const qIndex = getQuizQuestionIndex(questionDiv);
    const optionsList = questionDiv.querySelector('.options-list');
    const optIndex = optionsList.querySelectorAll('.option-row').length;
    optionsList.appendChild(buildOptionRow(qIndex, optIndex));
}

function removeOption(btn) {
    const questionDiv = btn.closest('.quiz-question');
    const optionsList = questionDiv.querySelector('.options-list');
    const qIndex = getQuizQuestionIndex(questionDiv);
    if (optionsList.querySelectorAll('.option-row').length > 2) {
        btn.closest('.option-row').remove();
        relabelOptions(optionsList, qIndex);
    }
}

function addQuizQuestion() {
    const container = document.getElementById('quizQuestions');
    const qIndex = quizIndex;
    const question = document.createElement('div');
    question.className = 'quiz-question';
    question.innerHTML = `
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="step-circle" style="background:#D97706;">${qIndex + 1}</div>
                <span class="text-sm font-bold text-slate-500 question-label">Question ${qIndex + 1}</span>
            </div>
            <button type="button" onclick="removeQuizQuestion(this)" class="icon-btn-remove">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="field-label">Question</label>
                <input type="text" name="quiz[${qIndex}][question]" class="field-input" placeholder="Enter your question">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="field-label">Question Type</label>
                    <select name="quiz[${qIndex}][type]" onchange="handleQuestionTypeChange(this)" class="field-select question-type">
                        <option value="multiple_choice">Multiple Choice</option>
                        <option value="true_false">True / False</option>
                    </select>
                </div>
                <div>
                    <label class="field-label">Question Media (Optional)</label>
                    <input type="file" name="quiz[${qIndex}][media]" accept="image/*" class="field-input">
                </div>
            </div>
            <div class="options-container">
                <label class="field-label">Answer Options</label>
                <p class="text-xs text-slate-400 mb-2">Each option can have text and/or an image.</p>
                <div class="space-y-2 options-list"></div>
                <button type="button" onclick="addOption(this)" class="text-sm text-[#1848c8] font-bold hover:underline mt-2">
                    + Add Option
                </button>
            </div>
        </div>
    `;
    container.appendChild(question);
    const optionsList = question.querySelector('.options-list');
    optionsList.appendChild(buildOptionRow(qIndex, 0));
    optionsList.appendChild(buildOptionRow(qIndex, 1));
    quizIndex++;
}

function reindexQuizQuestions() {
    document.querySelectorAll('.quiz-question').forEach((questionDiv, qIndex) => {
        const stepCircle = questionDiv.querySelector('.step-circle');
        const questionLabel = questionDiv.querySelector('.question-label');
        if (stepCircle) stepCircle.textContent = qIndex + 1;
        if (questionLabel) questionLabel.textContent = `Question ${qIndex + 1}`;

        const questionInput = questionDiv.querySelector('input[name*="[question]"]');
        if (questionInput) questionInput.name = `quiz[${qIndex}][question]`;

        const typeSelect = questionDiv.querySelector('.question-type');
        if (typeSelect) typeSelect.name = `quiz[${qIndex}][type]`;

        const mediaInput = questionDiv.querySelector('input[type="file"][name*="[media]"]');
        if (mediaInput) mediaInput.name = `quiz[${qIndex}][media]`;

        const optionsList = questionDiv.querySelector('.options-list');
        if (optionsList) relabelOptions(optionsList, qIndex);
    });
    quizIndex = document.querySelectorAll('.quiz-question').length;
}

function removeQuizQuestion(btn) {
    const question = btn.closest('.quiz-question');
    if (document.querySelectorAll('.quiz-question').length > 1) {
        question.remove();
        reindexQuizQuestions();
    }
}
</script>
@endsection