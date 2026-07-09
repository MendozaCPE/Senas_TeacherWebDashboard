{{-- AI Lesson Generator Modal --}}
<div id="aiGeneratorModal"
     style="display:none; position:fixed; inset:0; z-index:10000; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px);"
     onclick="closeAiModal(event)">

    <div id="aiModalPanel"
         style="position:absolute; right:0; top:0; height:100%; width:100%; max-width:480px;
                background:white; box-shadow:-8px 0 40px rgba(15,49,114,0.18);
                transform:translateX(100%); transition:transform 0.35s cubic-bezier(.4,0,.2,1);
                display:flex; flex-direction:column; overflow:hidden;">

        {{-- Header --}}
        <div style="background: linear-gradient(135deg,#6d28d9,#4f46e5); padding:24px 28px; flex-shrink:0;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:6px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:42px;height:42px;background:rgba(255,255,255,0.2);border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:20px;">✨</div>
                    <div>
                        <h3 style="color:white;font-size:18px;font-weight:800;margin:0;">AI Lesson Generator</h3>
                        <p style="color:rgba(255,255,255,0.75);font-size:12px;margin:0;">Powered by DeepSeek</p>
                    </div>
                </div>
                <button onclick="closeAiModalDirect()"
                        style="background:rgba(255,255,255,0.15);border:none;color:white;width:36px;height:36px;border-radius:10px;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;transition:background 0.2s;"
                        onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                        onmouseout="this.style.background='rgba(255,255,255,0.15)'">✕</button>
            </div>
        </div>

        {{-- Body --}}
        <div style="flex:1; overflow-y:auto; padding:28px;">

            {{-- Error Banner --}}
            <div id="aiErrorBanner"
                 style="display:none; background:#FEF2F2; border:1.5px solid #FCA5A5; border-radius:14px; padding:14px 18px; margin-bottom:20px; color:#B91C1C; font-size:13px; font-weight:600;">
            </div>

            {{-- Loading State --}}
            <div id="aiLoadingState" style="display:none; text-align:center; padding:40px 20px;">
                <div style="display:inline-block; width:48px; height:48px; border:4px solid rgba(109,40,217,0.2); border-top-color:#6d28d9; border-radius:50%; animation:aiSpin 0.8s linear infinite;"></div>
                <p style="color:#6d28d9; font-weight:700; font-size:15px; margin:18px 0 6px;">Generating your lesson...</p>
                <p style="color:#94a3b8; font-size:13px;">DeepSeek is crafting your FSL content.<br>This may take up to 30 seconds.</p>
            </div>

            {{-- Form --}}
            <div id="aiForm">
                <div style="margin-bottom:20px;">
                    <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">
                        Topic <span style="color:#EF4444;">*</span>
                    </label>
                    <input id="ai_topic" type="text" maxlength="200"
                           style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;transition:all 0.2s;box-sizing:border-box;"
                           placeholder="e.g. FSL Alphabet A to E"
                           onfocus="this.style.borderColor='#6d28d9'; this.style.boxShadow='0 0 0 4px rgba(109,40,217,0.1)';"
                           onblur="this.style.borderColor='#E5EAF2'; this.style.boxShadow='none';">
                    <p style="font-size:11px;color:#94a3b8;margin:4px 0 0;">Be specific for better results</p>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Difficulty</label>
                        <select id="ai_difficulty"
                                style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;background:#FAFBFD;cursor:pointer;">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Lesson Type</label>
                        <select id="ai_lesson_type"
                                style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;background:#FAFBFD;cursor:pointer;">
                            <option value="gesture">Gesture</option>
                            <option value="interactive">Interactive</option>
                            <option value="text">Text</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">
                        Number of Slides
                        <span style="font-weight:400;color:#94a3b8;">(3–10)</span>
                    </label>
                    <input id="ai_num_slides" type="number" min="3" max="10" value="5"
                           style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;transition:all 0.2s;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#6d28d9'; this.style.boxShadow='0 0 0 4px rgba(109,40,217,0.1)';"
                           onblur="this.style.borderColor='#E5EAF2'; this.style.boxShadow='none';">
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">
                        Special Instructions
                        <span style="font-weight:400;color:#94a3b8;">(optional)</span>
                    </label>
                    <textarea id="ai_special_instructions" rows="3" maxlength="500"
                              style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;resize:vertical;transition:all 0.2s;box-sizing:border-box;"
                              placeholder="e.g. Include stories for young learners, focus on greetings..."
                              onfocus="this.style.borderColor='#6d28d9'; this.style.boxShadow='0 0 0 4px rgba(109,40,217,0.1)';"
                              onblur="this.style.borderColor='#E5EAF2'; this.style.boxShadow='none';"></textarea>
                </div>

                <div style="display:flex;flex-direction:column;gap:10px;">
                    <button id="aiGenerateBtn" onclick="submitAiGenerate()"
                            style="background:linear-gradient(135deg,#6d28d9,#4f46e5);color:white;padding:14px 24px;border-radius:14px;font-weight:800;font-size:15px;border:none;cursor:pointer;width:100%;transition:all 0.2s;box-shadow:0 5px 20px rgba(109,40,217,0.35);"
                            onmouseover="if(!this.disabled){this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 24px rgba(109,40,217,0.45)';}"
                            onmouseout="this.style.transform='';this.style.boxShadow='0 5px 20px rgba(109,40,217,0.35)';">
                        ✨ Generate Lesson
                    </button>
                    <button onclick="closeAiModalDirect()"
                            style="background:white;color:#64748b;padding:13px 24px;border-radius:14px;font-weight:700;font-size:14px;border:1.5px solid #E5EAF2;cursor:pointer;width:100%;transition:all 0.2s;"
                            onmouseover="this.style.background='#F8FAFC';"
                            onmouseout="this.style.background='white';">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes aiSpin {
    to { transform: rotate(360deg); }
}
</style>

<script>
function openAiModal() {
    const modal = document.getElementById('aiGeneratorModal');
    const panel = document.getElementById('aiModalPanel');
    modal.style.display = 'block';
    requestAnimationFrame(() => {
        panel.style.transform = 'translateX(0)';
    });
    document.getElementById('ai_topic').focus();
}

function closeAiModalDirect() {
    const modal = document.getElementById('aiGeneratorModal');
    const panel = document.getElementById('aiModalPanel');
    panel.style.transform = 'translateX(100%)';
    setTimeout(() => { modal.style.display = 'none'; }, 350);
    resetAiModal();
}

function closeAiModal(e) {
    if (e.target === document.getElementById('aiGeneratorModal')) {
        closeAiModalDirect();
    }
}

function resetAiModal() {
    document.getElementById('aiErrorBanner').style.display = 'none';
    document.getElementById('aiLoadingState').style.display = 'none';
    document.getElementById('aiForm').style.display = 'block';
    const btn = document.getElementById('aiGenerateBtn');
    btn.disabled = false;
    btn.textContent = '✨ Generate Lesson';
}

function submitAiGenerate() {
    const topic = document.getElementById('ai_topic').value.trim();
    if (!topic) {
        showAiError('Please enter a topic for the lesson.');
        document.getElementById('ai_topic').focus();
        return;
    }

    const numSlides = parseInt(document.getElementById('ai_num_slides').value);
    if (isNaN(numSlides) || numSlides < 3 || numSlides > 10) {
        showAiError('Number of slides must be between 3 and 10.');
        return;
    }

    hideAiError();
    setAiLoading(true);

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
        || document.querySelector('input[name="_token"]')?.value;

    fetch('{{ route("lessons.ai-generate") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: JSON.stringify({
            topic:                topic,
            difficulty:           document.getElementById('ai_difficulty').value,
            lesson_type:          document.getElementById('ai_lesson_type').value,
            num_slides:           numSlides,
            special_instructions: document.getElementById('ai_special_instructions').value.trim() || null,
        }),
    })
    .then(response => response.json().then(data => ({ ok: response.ok, data })))
    .then(({ ok, data }) => {
        if (!ok) {
            throw new Error(data.message || 'AI generation failed. Please try again.');
        }
        closeAiModalDirect();
        populateLessonForm(data);
    })
    .catch(err => {
        showAiError(err.message || 'Something went wrong. Please try again.');
        setAiLoading(false);
    });
}

function setAiLoading(loading) {
    document.getElementById('aiLoadingState').style.display = loading ? 'block' : 'none';
    document.getElementById('aiForm').style.display = loading ? 'none' : 'block';
    const btn = document.getElementById('aiGenerateBtn');
    btn.disabled = loading;
}

function showAiError(msg) {
    const banner = document.getElementById('aiErrorBanner');
    banner.textContent = '⚠️ ' + msg;
    banner.style.display = 'block';
}

function hideAiError() {
    document.getElementById('aiErrorBanner').style.display = 'none';
}

function populateLessonForm(lesson) {
    // Basic fields
    const titleEl = document.querySelector('[name="title"]');
    const descEl  = document.querySelector('[name="description"]');
    const diffEl  = document.querySelector('[name="difficulty"]');
    const typeEl  = document.querySelector('[name="lesson_type"]');

    if (titleEl) titleEl.value = lesson.title || '';
    if (descEl)  descEl.value  = lesson.description || '';
    if (diffEl)  { diffEl.value = lesson.difficulty || 'beginner'; }
    if (typeEl)  {
        // Map AI lesson_type to form values (form uses gesture/interactive only)
        const typeMap = { 'gesture': 'gesture', 'text': 'gesture', 'video': 'gesture', 'interactive': 'interactive' };
        typeEl.value = typeMap[lesson.lesson_type] || 'gesture';
    }

    // Content slides
    const contentContainer = document.getElementById('contentCards');
    if (contentContainer && lesson.contents && lesson.contents.length > 0) {
        contentContainer.innerHTML = '';
        contentIndex = 0;

        lesson.contents.forEach((slide, idx) => {
            const card = buildAiContentCard(slide, idx);
            contentContainer.insertAdjacentHTML('beforeend', card);
            // Trigger toggleFields on the newly added select
            const newCard = contentContainer.lastElementChild;
            const typeSelect = newCard.querySelector('.content-type');
            if (typeSelect) toggleFields(typeSelect);
            contentIndex = idx + 1;
        });
    }

    // Quiz questions
    const quizContainer = document.getElementById('quizQuestions');
    if (quizContainer && lesson.quiz && lesson.quiz.length > 0) {
        quizContainer.innerHTML = '';
        quizIndex = 0;

        lesson.quiz.forEach((q, idx) => {
            const qCard = buildAiQuizCard(q, idx);
            quizContainer.insertAdjacentHTML('beforeend', qCard);
            quizIndex = idx + 1;
        });
    }

    // Show success toast
    showAiSuccessToast();
}

function buildAiContentCard(slide, idx) {
    const type = slide.content_type || 'text';
    const typeLabels = { text: 'Text', gesture_demo: 'Gesture', image: 'Image', video: 'Video' };
    const typeLabel = typeLabels[type] || 'Text';

    const mediaMissing   = slide.media_missing ? true : false;
    const gestureHidden  = type === 'gesture_demo' ? '' : 'hidden';
    // Always show media upload — for every content type
    const mediaHidden    = '';
    const gestureName    = slide.gesture_name || '';

    // Just a warning notice — no redundant file input here
    const mediaMissingBadge = mediaMissing ? `
        <div class="media-missing-badge" style="background:#FEF9C3;border:1.5px solid #FDE047;border-radius:12px;padding:12px 16px;display:flex;align-items:flex-start;gap:10px;">
            <span style="font-size:16px;flex-shrink:0;line-height:1.5;">⚠️</span>
            <div>
                <p style="font-size:12px;font-weight:800;color:#92400E;margin:0 0 2px;">No Media Available</p>
                <p style="font-size:11px;color:#A16207;margin:0;">No gesture found in the database. Use the upload field above to add media.</p>
            </div>
        </div>` : '';

    return `
    <div class="content-card">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <div class="step-circle step-number">${idx + 1}</div>
                <span class="badge-pill" style="background: rgba(24,72,200,0.1); color:#1848c8;">${typeLabel}</span>
                ${mediaMissing ? '<span style="background:#FEF9C3;color:#92400E;font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;border:1px solid #FDE047;">⚠ No Media</span>' : ''}
            </div>
            <button type="button" onclick="removeContentCard(this)" class="icon-btn-remove">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
        <div class="space-y-4">
            <div>
                <label class="field-label">Content Type</label>
                <select name="contents[${idx}][content_type]" class="content-type field-select" onchange="toggleFields(this)">
                    <option value="text" ${type === 'text' ? 'selected' : ''}>Text</option>
                    <option value="gesture_demo" ${type === 'gesture_demo' ? 'selected' : ''}>Gesture Demo</option>
                    <option value="image" ${type === 'image' ? 'selected' : ''}>Image</option>
                    <option value="video" ${type === 'video' ? 'selected' : ''}>Video</option>
                </select>
            </div>
            <div>
                <label class="field-label">Title</label>
                <input type="text" name="contents[${idx}][title]" value="${escapeHtml(slide.title || '')}" class="field-input" placeholder="Slide title">
            </div>
            <div>
                <label class="field-label">Content</label>
                <textarea name="contents[${idx}][content_text]" rows="3" class="field-textarea" placeholder="Content text...">${escapeHtml(slide.content_text || '')}</textarea>
            </div>
            <div class="gesture-field ${gestureHidden}">
                <label class="field-label">Gesture Name</label>
                <input type="text" name="contents[${idx}][gesture_name]" value="${escapeHtml(gestureName)}" class="field-input" placeholder="e.g., letter_a">
            </div>
            <div class="media-field ${mediaHidden}">
                <label class="field-label">Upload Media</label>
                <div class="media-preview-wrap" style="display:none;margin-bottom:8px;">
                    <img class="content-media-preview" src="" alt="" style="max-height:120px;border-radius:10px;border:1.5px solid #E5EAF2;object-fit:cover;">
                </div>
                <input type="file" name="contents[${idx}][media]" accept="image/*,video/*" class="field-input" onchange="previewContentMedia(this)">
            </div>
            <input type="hidden" name="contents[${idx}][media_missing]" value="${mediaMissing ? '1' : '0'}">
            ${mediaMissingBadge}
        </div>
    </div>`;
}

function buildAiQuizCard(q, idx) {
    const options = Array.isArray(q.options) ? q.options : ['', '', '', ''];
    const correct = typeof q.correct_index === 'number' ? q.correct_index : 0;
    const letters = ['A', 'B', 'C', 'D', 'E', 'F'];

    const optionsHtml = options.map((opt, optIdx) => `
        <div class="option-row">
            <div class="option-letter">${letters[optIdx] || optIdx}</div>
            <div class="option-body">
                <input type="text" name="quiz[${idx}][options][${optIdx}][text]" value="${escapeHtml(opt)}" class="option-text-input" placeholder="Option ${letters[optIdx] || optIdx} text">
                <div class="option-image-row">
                    <input type="file" name="quiz[${idx}][options][${optIdx}][image]" accept="image/*" class="option-image-input" onchange="previewOptionImage(this)">
                    <img class="option-image-preview" src="" alt="">
                </div>
            </div>
            <div class="option-correct-row">
                <input type="radio" name="quiz[${idx}][correct]" value="${optIdx}" ${correct === optIdx ? 'checked' : ''}>
                <label>Correct</label>
            </div>
            <button type="button" class="option-remove-btn" onclick="removeOption(this)">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>`).join('');

    return `
    <div class="quiz-question">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <div class="step-circle" style="background:#D97706;">${idx + 1}</div>
                <span class="text-sm font-bold text-slate-500 question-label">Question ${idx + 1}</span>
            </div>
            <button type="button" onclick="removeQuizQuestion(this)" class="icon-btn-remove">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="field-label">Question</label>
                <input type="text" name="quiz[${idx}][question]" value="${escapeHtml(q.question || '')}" class="field-input" placeholder="Question text">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="field-label">Question Type</label>
                    <select name="quiz[${idx}][type]" onchange="handleQuestionTypeChange(this)" class="field-select question-type">
                        <option value="multiple_choice" selected>Multiple Choice</option>
                        <option value="true_false">True / False</option>
                    </select>
                </div>
                <div>
                    <label class="field-label">Question Media (Optional)</label>
                    <input type="file" name="quiz[${idx}][media]" accept="image/*" class="field-input">
                </div>
            </div>
            <div class="options-container">
                <label class="field-label">Answer Options</label>
                <p class="text-xs text-slate-400 mb-2">Each option can have text and/or an image.</p>
                <div class="space-y-2 options-list">${optionsHtml}</div>
                <button type="button" onclick="addOption(this)" class="text-sm text-[#1848c8] font-bold hover:underline mt-2">+ Add Option</button>
            </div>
        </div>
    </div>`;
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function showAiSuccessToast() {
    const toast = document.createElement('div');
    toast.style.cssText = 'position:fixed;bottom:28px;right:28px;background:linear-gradient(135deg,#6d28d9,#4f46e5);color:white;padding:14px 22px;border-radius:16px;font-weight:700;font-size:14px;box-shadow:0 8px 30px rgba(109,40,217,0.4);z-index:20000;transition:all 0.4s;';
    toast.textContent = '✨ Lesson generated! Review and edit below.';
    document.body.appendChild(toast);
    setTimeout(() => { toast.style.opacity = '0'; toast.style.transform = 'translateY(10px)'; }, 3000);
    setTimeout(() => toast.remove(), 3500);
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        const modal = document.getElementById('aiGeneratorModal');
        if (modal && modal.style.display !== 'none') closeAiModalDirect();
    }
});
</script>
