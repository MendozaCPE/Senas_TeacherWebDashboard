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
        <div style="background: linear-gradient(135deg, #0d326b 0%, #1e4b8f 55%, #1a6fd4 100%); padding:24px 28px; flex-shrink:0;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <div style="width:42px;height:42px;background:rgba(255,255,255,0.2);border-radius:13px;display:flex;align-items:center;justify-content:center;font-size:20px;">✨</div>
                    <div>
                        <h3 style="color:white;font-size:18px;font-weight:800;margin:0;">AI Lesson Generator</h3>
                    </div>
                </div>
                <button onclick="closeAiModalDirect()"
                        style="background:rgba(255,255,255,0.15);border:none;color:white;width:36px;height:36px;border-radius:10px;cursor:pointer;font-size:18px;display:flex;align-items:center;justify-content:center;transition:background 0.2s;"
                        onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                        onmouseout="this.style.background='rgba(255,255,255,0.15)'">✕</button>
            </div>

            {{-- Mode Tabs --}}
            <div style="display:flex;gap:6px;">
                <button id="tabTopic" onclick="switchAiTab('topic')"
                        style="flex:1;padding:9px 6px;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;transition:all 0.2s;background:white;color:#0d326b;">
                    ✏️ By Topic
                </button>
                <button id="tabPdf" onclick="switchAiTab('pdf')"
                        style="flex:1;padding:9px 6px;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;transition:all 0.2s;background:rgba(255,255,255,0.15);color:white;">
                    📄 From PDF
                </button>
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
                <div style="display:inline-block; width:48px; height:48px; border:4px solid rgba(13,50,107,0.15); border-top-color:#0d326b; border-radius:50%; animation:aiSpin 0.8s linear infinite;"></div>
                <p style="color:#0d326b; font-weight:700; font-size:15px; margin:18px 0 6px;" id="aiLoadingText">Generating your lesson...</p>
                <p style="color:#94a3b8; font-size:13px; margin:0 0 16px;" id="aiLoadingSubtext">AI is crafting your lesson content.<br>This may take up to 30 seconds.</p>
                <div style="max-width:280px;margin:0 auto;">
                    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:8px;">
                        <span style="font-size:12px;font-weight:600;color:#64748b;">Progress</span>
                        <span id="aiProgressPct" style="font-size:13px;font-weight:800;color:#0d326b;">0%</span>
                    </div>
                    <div style="background:#E5EAF2;border-radius:99px;height:8px;overflow:hidden;">
                        <div id="aiProgressBar" style="background:linear-gradient(90deg,#0d326b,#1a6fd4);height:100%;width:0%;border-radius:99px;transition:width 0.4s ease;"></div>
                    </div>
                </div>
            </div>

            {{-- ── TOPIC FORM ── --}}
            <div id="aiFormTopic">
                <div style="margin-bottom:20px;">
                    <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">
                        Topic <span style="color:#EF4444;">*</span>
                    </label>
                    <input id="ai_topic" type="text" maxlength="200"
                           style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;transition:all 0.2s;box-sizing:border-box;"
                           placeholder="e.g. FSL Alphabet, Animals, Math, Science, History..."
                           onfocus="this.style.borderColor='#1a6fd4'; this.style.boxShadow='0 0 0 4px rgba(26,111,212,0.15)';"
                           onblur="this.style.borderColor='#E5EAF2'; this.style.boxShadow='none';">
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
                            <option value="video">Video / YouTube</option>
                            <option value="interactive">Interactive</option>
                            <option value="text">Text</option>
                        </select>
                    </div>
                </div>

                <div style="margin-bottom:20px;">
                    <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">
                        Number of Slides
                        <span style="font-weight:400;color:#94a3b8;">(3–30)</span>
                    </label>
                    <input id="ai_num_slides" type="number" min="3" max="30" value="5"
                           style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;transition:all 0.2s;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#1a6fd4'; this.style.boxShadow='0 0 0 4px rgba(26,111,212,0.15)';"
                           onblur="this.style.borderColor='#E5EAF2'; this.style.boxShadow='none';">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Multiple Choice Qs</label>
                        <input id="ai_num_mc" type="number" min="0" max="15" value="2"
                               style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#1a6fd4';" onblur="this.style.borderColor='#E5EAF2';">
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">True / False Qs</label>
                        <input id="ai_num_tf" type="number" min="0" max="15" value="1"
                               style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#1a6fd4';" onblur="this.style.borderColor='#E5EAF2';">
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Drag & Drop Qs</label>
                        <input id="ai_num_dd" type="number" min="0" max="15" value="1"
                               style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#1a6fd4';" onblur="this.style.borderColor='#E5EAF2';">
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Gesture Qs</label>
                        <input id="ai_num_gt" type="number" min="0" max="15" value="1"
                               style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#1a6fd4';" onblur="this.style.borderColor='#E5EAF2';">
                    </div>
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">
                        Special Instructions
                        <span style="font-weight:400;color:#94a3b8;">(optional)</span>
                    </label>
                    <textarea id="ai_special_instructions" rows="3" maxlength="500"
                              style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;resize:vertical;transition:all 0.2s;box-sizing:border-box;"
                              placeholder="e.g. Include stories for young learners, focus on greetings..."
                              onfocus="this.style.borderColor='#1a6fd4'; this.style.boxShadow='0 0 0 4px rgba(26,111,212,0.15)';"
                              onblur="this.style.borderColor='#E5EAF2'; this.style.boxShadow='none';"></textarea>
                </div>

                <div style="display:flex;flex-direction:column;gap:10px;">
                    <button id="aiGenerateBtn" onclick="submitAiGenerate()"
                            style="background:linear-gradient(135deg,#0d326b,#1a6fd4);color:white;padding:14px 24px;border-radius:14px;font-weight:800;font-size:15px;border:none;cursor:pointer;width:100%;transition:all 0.2s;box-shadow:0 5px 20px rgba(13,50,107,0.35);"
                            onmouseover="if(!this.disabled){this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 24px rgba(13,50,107,0.45)';}"
                            onmouseout="this.style.transform='';this.style.boxShadow='0 5px 20px rgba(13,50,107,0.35)';">
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

            {{-- ── PDF FORM ── --}}
            <div id="aiFormPdf" style="display:none;">

                {{-- Drop Zone --}}
                <div id="pdfDropZone"
                     onclick="document.getElementById('pdfFileInput').click()"
                     ondrop="handlePdfDrop(event)" ondragover="event.preventDefault();this.style.borderColor='#1a6fd4';this.style.background='rgba(26,111,212,0.08)';" ondragleave="this.style.borderColor='#93c5fd';this.style.background='rgba(26,111,212,0.03)';"
                     style="border:2.5px dashed #93c5fd;border-radius:16px;padding:32px 20px;text-align:center;cursor:pointer;transition:all 0.2s;background:rgba(26,111,212,0.03);margin-bottom:20px;">
                    <div style="font-size:40px;margin-bottom:10px;">📄</div>
                    <p style="font-size:14px;font-weight:700;color:#0d326b;margin:0 0 4px;">Drop your PDF here</p>
                    <p style="font-size:12px;color:#94a3b8;margin:0;">or click to browse — max 10MB</p>
                    <input type="file" id="pdfFileInput" accept=".pdf" style="display:none;" onchange="handlePdfSelect(this)">
                </div>

                {{-- Selected File Badge --}}
                <div id="pdfFileBadge" style="display:none;align-items:center;gap:10px;background:#F0F7FF;border:1.5px solid #BFDBFE;border-radius:12px;padding:12px 14px;margin-bottom:20px;">
                    <span style="font-size:20px;flex-shrink:0;">📄</span>
                    <div style="flex:1;min-width:0;">
                        <p id="pdfFileName" style="font-size:13px;font-weight:700;color:#0d326b;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></p>
                        <p id="pdfFileSize" style="font-size:11px;color:#1e4b8f;margin:0;"></p>
                    </div>
                    <button onclick="clearPdfFile()" type="button"
                            style="background:rgba(13,50,107,0.1);border:none;color:#0d326b;width:28px;height:28px;border-radius:8px;cursor:pointer;font-size:14px;flex-shrink:0;">✕</button>
                </div>

                {{-- PDF Settings --}}
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Difficulty</label>
                        <select id="pdf_difficulty"
                                style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;background:#FAFBFD;cursor:pointer;">
                            <option value="beginner">Beginner</option>
                            <option value="intermediate">Intermediate</option>
                            <option value="advanced">Advanced</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Lesson Type</label>
                        <select id="pdf_lesson_type"
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
                        <span style="font-weight:400;color:#94a3b8;">(3–30)</span>
                    </label>
                    <input id="pdf_num_slides" type="number" min="3" max="30" value="5"
                           style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;transition:all 0.2s;box-sizing:border-box;"
                           onfocus="this.style.borderColor='#1a6fd4';" onblur="this.style.borderColor='#E5EAF2';">
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Multiple Choice Qs</label>
                        <input id="pdf_num_mc" type="number" min="0" max="15" value="2"
                               style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#1a6fd4';" onblur="this.style.borderColor='#E5EAF2';">
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">True / False Qs</label>
                        <input id="pdf_num_tf" type="number" min="0" max="15" value="1"
                               style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#1a6fd4';" onblur="this.style.borderColor='#E5EAF2';">
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Drag & Drop Qs</label>
                        <input id="pdf_num_dd" type="number" min="0" max="15" value="1"
                               style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#1a6fd4';" onblur="this.style.borderColor='#E5EAF2';">
                    </div>
                    <div>
                        <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">Gesture Qs</label>
                        <input id="pdf_num_gt" type="number" min="0" max="15" value="1"
                               style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;box-sizing:border-box;"
                               onfocus="this.style.borderColor='#1a6fd4';" onblur="this.style.borderColor='#E5EAF2';">
                    </div>
                </div>

                <div style="margin-bottom:24px;">
                    <label style="display:block;font-size:13px;font-weight:700;color:#334155;margin-bottom:6px;">
                        Additional Instructions
                        <span style="font-weight:400;color:#94a3b8;">(optional)</span>
                    </label>
                    <textarea id="pdf_instructions" rows="3" maxlength="500"
                              style="width:100%;padding:12px 16px;border:1.5px solid #E5EAF2;border-radius:14px;font-size:14px;outline:none;resize:vertical;box-sizing:border-box;"
                              placeholder="e.g. Focus on the signs mentioned on page 3, simplify for young learners..."
                              onfocus="this.style.borderColor='#1a6fd4';" onblur="this.style.borderColor='#E5EAF2';"></textarea>
                </div>

                <div style="display:flex;flex-direction:column;gap:10px;">
                    <button id="aiPdfGenerateBtn" onclick="submitPdfGenerate()"
                            style="background:linear-gradient(135deg,#0d326b,#1a6fd4);color:white;padding:14px 24px;border-radius:14px;font-weight:800;font-size:15px;border:none;cursor:pointer;width:100%;transition:all 0.2s;box-shadow:0 5px 20px rgba(13,50,107,0.35);"
                            onmouseover="if(!this.disabled){this.style.transform='translateY(-1px)';this.style.boxShadow='0 8px 24px rgba(13,50,107,0.45)';}"
                            onmouseout="this.style.transform='';this.style.boxShadow='0 5px 20px rgba(13,50,107,0.35)';">
                        📄 Generate from PDF
                    </button>
                    <button onclick="closeAiModalDirect()"
                            style="background:white;color:#64748b;padding:13px 24px;border-radius:14px;font-weight:700;font-size:14px;border:1.5px solid #E5EAF2;cursor:pointer;width:100%;transition:all 0.2s;"
                            onmouseover="this.style.background='#F8FAFC';" onmouseout="this.style.background='white';">
                        Cancel
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
@keyframes aiSpin { to { transform: rotate(360deg); } }
</style>

<script>
let _selectedPdfFile = null;
let _aiProgressTimer = null;

function updateAiProgressDisplay(pct) {
    [
        ['aiProgressBar', 'aiProgressPct'],
        ['aqm_progressBar', 'aqm_progressPct'],
    ].forEach(([barId, labelId]) => {
        const bar = document.getElementById(barId);
        const label = document.getElementById(labelId);
        if (bar) bar.style.width = pct + '%';
        if (label) label.textContent = pct + '%';
    });
}

function startAiProgress(maxPct = 92) {
    stopAiProgress();
    updateAiProgressDisplay(0);
    _aiProgressTimer = setInterval(() => {
        const label = document.getElementById('aiProgressPct') || document.getElementById('aqm_progressPct');
        if (!label) { stopAiProgress(); return; }
        const current = parseInt(label.textContent, 10) || 0;
        if (current >= maxPct) return;
        const increment = Math.max(1, Math.round((maxPct - current) * 0.06));
        updateAiProgressDisplay(Math.min(maxPct, current + increment));
    }, 450);
}

function finishAiProgress() {
    stopAiProgress();
    updateAiProgressDisplay(100);
}

function stopAiProgress() {
    if (_aiProgressTimer) {
        clearInterval(_aiProgressTimer);
        _aiProgressTimer = null;
    }
}

/* ── Tab switching ───────────────────────────────────────────────── */
function switchAiTab(tab) {
    const isTopic = (tab === 'topic');
    const activeStyle   = 'flex:1;padding:9px 6px;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;transition:all 0.2s;background:white;color:#0d326b;';
    const inactiveStyle = 'flex:1;padding:9px 6px;border:none;border-radius:10px;font-size:13px;font-weight:700;cursor:pointer;transition:all 0.2s;background:rgba(255,255,255,0.15);color:white;';
    document.getElementById('tabTopic').style.cssText = isTopic  ? activeStyle : inactiveStyle;
    document.getElementById('tabPdf').style.cssText   = !isTopic ? activeStyle : inactiveStyle;
    document.getElementById('aiFormTopic').style.display = isTopic  ? 'block' : 'none';
    document.getElementById('aiFormPdf').style.display   = !isTopic ? 'block' : 'none';
    hideAiError();
}

/* ── PDF file handling ───────────────────────────────────────────── */
function handlePdfSelect(input) {
    if (input.files && input.files[0]) setPdfFile(input.files[0]);
}
function handlePdfDrop(e) {
    e.preventDefault();
    const zone = document.getElementById('pdfDropZone');
    zone.style.borderColor = '#93c5fd';
    zone.style.background  = 'rgba(26,111,212,0.03)';
    const file = e.dataTransfer.files[0];
    if (file && file.type === 'application/pdf') setPdfFile(file);
    else showAiError('Please drop a PDF file.');
}
function setPdfFile(file) {
    if (file.size > 10 * 1024 * 1024) { showAiError('PDF must be under 10MB.'); return; }
    _selectedPdfFile = file;
    document.getElementById('pdfDropZone').style.display = 'none';
    const badge = document.getElementById('pdfFileBadge');
    badge.style.display = 'flex';
    document.getElementById('pdfFileName').textContent = file.name;
    document.getElementById('pdfFileSize').textContent = (file.size / 1024).toFixed(0) + ' KB';
    hideAiError();
}
function clearPdfFile() {
    _selectedPdfFile = null;
    document.getElementById('pdfFileInput').value = '';
    document.getElementById('pdfFileBadge').style.display = 'none';
    document.getElementById('pdfDropZone').style.display  = 'block';
}

/* ── Submit — PDF ────────────────────────────────────────────────── */
function submitPdfGenerate() {
    if (!_selectedPdfFile) { showAiError('Please select a PDF file first.'); return; }
    const numSlides = parseInt(document.getElementById('pdf_num_slides').value);
    if (isNaN(numSlides) || numSlides < 3 || numSlides > 30) { showAiError('Slides must be between 3 and 30.'); return; }
    const numMc = parseInt(document.getElementById('pdf_num_mc').value) || 0;
    const numTf = parseInt(document.getElementById('pdf_num_tf').value) || 0;
    const numDd = parseInt(document.getElementById('pdf_num_dd').value) || 0;
    const numGt = parseInt(document.getElementById('pdf_num_gt').value) || 0;
    if (numMc + numTf + numDd + numGt < 1) { showAiError('Please request at least 1 quiz question.'); return; }
    hideAiError();
    setAiLoading(true, '📖 Reading your PDF...', 'AI is scanning the document and building<br>your lesson. This may take 30–60 seconds.');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
    const fd = new FormData();
    fd.append('pdf',          _selectedPdfFile);
    fd.append('difficulty',   document.getElementById('pdf_difficulty').value);
    fd.append('lesson_type',  document.getElementById('pdf_lesson_type').value);
    fd.append('num_slides',   numSlides);
    fd.append('num_mc',       numMc);
    fd.append('num_tf',       numTf);
    fd.append('num_dd',       numDd);
    fd.append('num_gt',       numGt);
    fd.append('instructions', document.getElementById('pdf_instructions').value.trim());
    fetch('{{ route("lessons.ai-generate-pdf") }}', { method:'POST', headers:{'X-CSRF-TOKEN':csrf,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, body:fd })
        .then(r => r.json().then(d => ({ok:r.ok,d})))
        .then(({ok,d}) => { if (!ok) throw new Error(d.message||'PDF generation failed.'); finishAiProgress(); closeAiModalDirect(); populateLessonForm(d); })
        .catch(err => { showAiError(err.message||'Something went wrong.'); setAiLoading(false); });
}

/* ── Submit — Topic ──────────────────────────────────────────────── */
function submitAiGenerate() {
    const topic = document.getElementById('ai_topic').value.trim();
    if (!topic) { showAiError('Please enter a topic for the lesson.'); document.getElementById('ai_topic').focus(); return; }
    const numSlides = parseInt(document.getElementById('ai_num_slides').value);
    if (isNaN(numSlides) || numSlides < 3 || numSlides > 30) { showAiError('Slides must be between 3 and 30.'); return; }
    const numMc = parseInt(document.getElementById('ai_num_mc').value) || 0;
    const numTf = parseInt(document.getElementById('ai_num_tf').value) || 0;
    const numDd = parseInt(document.getElementById('ai_num_dd').value) || 0;
    const numGt = parseInt(document.getElementById('ai_num_gt').value) || 0;
    if (numMc + numTf + numDd + numGt < 1) { showAiError('Please request at least 1 quiz question.'); return; }
    hideAiError();
    setAiLoading(true, 'Generating your lesson...', 'AI is crafting your lesson content.<br>This may take up to 30 seconds.');
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || document.querySelector('input[name="_token"]')?.value;
    fetch('{{ route("lessons.ai-generate") }}', {
        method:'POST',
        headers:{'Content-Type':'application/json','X-CSRF-TOKEN':csrf,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},
        body:JSON.stringify({ topic, difficulty:document.getElementById('ai_difficulty').value, lesson_type:document.getElementById('ai_lesson_type').value, num_slides:numSlides, num_mc:numMc, num_tf:numTf, num_dd:numDd, num_gt:numGt, special_instructions:document.getElementById('ai_special_instructions').value.trim()||null }),
    })
    .then(r => r.json().then(d => ({ok:r.ok,d})))
    .then(({ok,d}) => { if (!ok) throw new Error(d.message||'AI generation failed.'); finishAiProgress(); closeAiModalDirect(); populateLessonForm(d); })
    .catch(err => { showAiError(err.message||'Something went wrong.'); setAiLoading(false); });
}

/* ── Helpers ─────────────────────────────────────────────────────── */
function openAiModal() {
    const modal = document.getElementById('aiGeneratorModal');
    const panel = document.getElementById('aiModalPanel');
    modal.style.display = 'block';
    requestAnimationFrame(() => { panel.style.transform = 'translateX(0)'; });
    document.getElementById('ai_topic').focus();
}
function closeAiModalDirect() {
    const panel = document.getElementById('aiModalPanel');
    panel.style.transform = 'translateX(100%)';
    setTimeout(() => { document.getElementById('aiGeneratorModal').style.display = 'none'; }, 350);
    resetAiModal();
}
function closeAiModal(e) {
    if (e.target === document.getElementById('aiGeneratorModal')) closeAiModalDirect();
}
function resetAiModal() {
    hideAiError();
    setAiLoading(false);
    document.getElementById('aiGenerateBtn').disabled    = false;
    document.getElementById('aiGenerateBtn').textContent = '✨ Generate Lesson';
    document.getElementById('aiPdfGenerateBtn').disabled    = false;
    document.getElementById('aiPdfGenerateBtn').textContent = '📄 Generate from PDF';
    clearPdfFile();
    switchAiTab('topic');
}
function setAiLoading(loading, title, sub) {
    const ls = document.getElementById('aiLoadingState');
    ls.style.display = loading ? 'block' : 'none';
    if (loading && title) ls.querySelector('p').textContent = title;
    if (loading && sub)   ls.querySelectorAll('p')[1].innerHTML = sub;
    const isTopicActive = document.getElementById('tabTopic').style.background === 'white';
    document.getElementById('aiFormTopic').style.display = loading ? 'none' : (isTopicActive ? 'block' : 'none');
    document.getElementById('aiFormPdf').style.display   = loading ? 'none' : (!isTopicActive ? 'block' : 'none');
    document.getElementById('aiGenerateBtn').disabled    = loading;
    document.getElementById('aiPdfGenerateBtn').disabled = loading;
    if (loading) {
        startAiProgress(isTopicActive ? 92 : 90);
    } else {
        stopAiProgress();
        updateAiProgressDisplay(0);
    }
}
function showAiError(msg) { const b=document.getElementById('aiErrorBanner'); b.textContent='⚠️ '+msg; b.style.display='block'; }
function hideAiError()     { document.getElementById('aiErrorBanner').style.display='none'; }
function showAiSuccessToast() {
    const t=document.createElement('div');
    t.style.cssText='position:fixed;bottom:28px;right:28px;background:linear-gradient(135deg,#0d326b,#1a6fd4);color:white;padding:14px 22px;border-radius:16px;font-weight:700;font-size:14px;box-shadow:0 8px 30px rgba(13,50,107,0.4);z-index:20000;transition:all 0.4s;';
    t.textContent='✨ Lesson generated! Review and edit below.';
    document.body.appendChild(t);
    setTimeout(()=>{t.style.opacity='0';t.style.transform='translateY(10px)';},3000);
    setTimeout(()=>t.remove(),3500);
}
document.addEventListener('keydown', e => { if (e.key==='Escape'&&document.getElementById('aiGeneratorModal').style.display!=='none') closeAiModalDirect(); });

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
        if (typeof reindexContentCards === 'function') {
            reindexContentCards();
        }
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

        if (typeof reindexQuizQuestions === 'function') {
            reindexQuizQuestions();
        }

        // Auto-load gestures for AI generated gesture questions
        quizContainer.querySelectorAll('.gesture-module-select').forEach((select) => {
            if (select.value) {
                const match = select.name.match(/quiz\[(\d+)\]/);
                if (match) {
                    const index = match[1];
                    loadGesturesForModule(select, index);
                }
            }
        });
    }

    // Show success toast
    showAiSuccessToast();
}

function getAiYoutubeId(url) {
    if (!url || typeof url !== 'string') return null;
    url = url.trim();
    let m = url.match(/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/);
    if (m) return m[1];
    m = url.match(/youtube\.com\/embed\/([a-zA-Z0-9_-]{11})/);
    if (m) return m[1];
    m = url.match(/youtube\.com\/shorts\/([a-zA-Z0-9_-]{11})/);
    if (m) return m[1];
    if (/^[a-zA-Z0-9_-]{11}$/.test(url)) return url;
    return null;
}

function buildAiContentCard(slide, idx) {
    let type = slide.content_type || 'text';
    if (type === 'youtube') type = 'youtube_video';

    // YouTube URL support
    const rawYtUrl = slide.youtube_url || (type === 'youtube_video' ? (slide.media_url || '') : '');
    const ytId = getAiYoutubeId(rawYtUrl);
    if (ytId || (rawYtUrl && (rawYtUrl.includes('youtube.com') || rawYtUrl.includes('youtu.be')))) {
        type = 'youtube_video';
    }

    const typeLabels = { text: 'Text', gesture_demo: 'Gesture', image: 'Image', video: 'Video', youtube_video: 'YouTube' };
    const typeLabel = typeLabels[type] || 'Text';

    const isYoutube     = type === 'youtube_video';
    const mediaMissing  = isYoutube ? false : (slide.media_missing ? true : false);
    const gestureHidden = type === 'gesture_demo' ? '' : 'hidden';
    const mediaHidden   = (!isYoutube && (type === 'image' || type === 'video' || type === 'gesture_demo' || mediaMissing)) ? '' : 'hidden';
    const youtubeHidden = isYoutube ? '' : 'hidden';
    const gestureName   = slide.gesture_name || '';

    // Resolved media from the backend (gesture_media table)
    const resolvedVideo = slide.video_url || null;
    const resolvedImage = slide.image_url || null;
    let mediaPath = slide.media_path || '';
    if (!mediaPath && (resolvedVideo || resolvedImage)) {
        const raw = (resolvedVideo || resolvedImage).trim();
        const m = raw.match(/\/storage\/(.+)$/i);
        mediaPath = m ? m[1] : raw;
    }
    const hasResolvedMedia = !isYoutube && !!(resolvedVideo || resolvedImage);
    const ytEmbedUrl = ytId ? ('https://www.youtube.com/embed/' + ytId + '?rel=0&modestbranding=1') : '';

    // Build the media section: show resolved preview if available, otherwise upload widget
    let mediaSection = '';
    if (hasResolvedMedia) {
        // Resolved media preview — pre-populate existing_media and show a thumb
        const mediaUrl  = resolvedVideo || resolvedImage;
        const isVideo   = !!resolvedVideo;
        const thumbHtml = isVideo
            ? `<video src="${mediaUrl}" style="width:220px;height:138px;object-fit:cover;border-radius:10px;border:1.5px solid #e2e8f0;background:#0f172a;" muted playsinline preload="metadata"></video>`
            : `<img src="${mediaUrl}" style="width:130px;height:130px;object-fit:cover;border-radius:8px;border:1.5px solid #e2e8f0;" alt="Gesture media">`;

        mediaSection = `
            <div class="media-field ${mediaHidden}">
                <label class="field-label">Upload Media</label>
                <input type="hidden" name="contents[${idx}][existing_media]" value="${escapeHtml(mediaPath)}" class="media-path-input">
                <div class="media-upload-widget has-file" data-context="lesson_media" data-accept="image/*,video/*">
                    <div class="upload-trigger">
                        <input type="file" accept="image/*,video/*" class="ajax-file-input" onchange="handleAjaxUpload(this, 'content')">
                        <span class="upload-icon material-symbols-outlined text-slate-400" style="font-size:20px;">cloud_upload</span>
                        <div class="upload-spinner"></div>
                        <span class="upload-label">Click or drag to upload</span>
                    </div>
                    <div class="media-thumb-wrap" style="display:flex;align-items:center;gap:10px;margin-top:8px;">
                        ${thumbHtml}
                        <div class="media-thumb-info">
                            <strong style="display:block;font-size:12px;color:#1e293b;">Resolved from database</strong>
                            <span style="font-size:11px;color:#64748b;">${isVideo ? 'Video' : 'Image'} — ${escapeHtml(gestureName)}</span>
                            <button type="button" class="media-remove-btn" onclick="clearMediaWidget(this)">✕ Remove</button>
                        </div>
                    </div>
                    <div class="media-upload-error"></div>
                </div>
            </div>`;
    } else {
        // No resolved media — show standard upload widget
        const missingBadge = mediaMissing ? `
            <div class="media-missing-badge" style="background:#FEF9C3;border:1.5px solid #FDE047;border-radius:12px;padding:12px 16px;display:flex;align-items:flex-start;gap:10px;">
                <span style="font-size:16px;flex-shrink:0;line-height:1.5;">⚠️</span>
                <div>
                    <p style="font-size:12px;font-weight:800;color:#92400E;margin:0 0 2px;">No Media Available</p>
                    <p style="font-size:11px;color:#A16207;margin:0;">No gesture found in the database. Use the upload field above to add media.</p>
                </div>
            </div>` : '';

        mediaSection = `
            <div class="media-field ${mediaHidden}">
                <label class="field-label">Upload Media</label>
                <input type="hidden" name="contents[${idx}][existing_media]" value="" class="media-path-input">
                <div class="media-upload-widget" data-context="lesson_media" data-accept="image/*,video/*">
                    <div class="upload-trigger">
                        <input type="file" accept="image/*,video/*" class="ajax-file-input" onchange="handleAjaxUpload(this, 'content')">
                        <span class="upload-icon material-symbols-outlined text-slate-400" style="font-size:20px;">cloud_upload</span>
                        <div class="upload-spinner"></div>
                        <span class="upload-label">Click or drag to upload</span>
                    </div>
                    <div class="media-thumb-wrap"></div>
                    <div class="media-upload-error"></div>
                </div>
            </div>
            ${missingBadge}`;
    }

    const youtubeSection = `
        <div class="youtube-field ${youtubeHidden}">
            <label class="field-label">YouTube Video URL</label>
            <input type="text" name="contents[${idx}][youtube_url]" value="${escapeHtml(rawYtUrl)}" class="field-input youtube-url-input" placeholder="https://www.youtube.com/watch?v=VIDEO_ID or https://youtu.be/VIDEO_ID" autocomplete="off">
            <div class="youtube-url-error" style="display:none; color:#EF4444; font-size:12px; font-weight:600; margin-top:4px;"></div>
            <div class="youtube-preview-wrap" style="${ytEmbedUrl ? '' : 'display:none;'} margin-top:12px; border-radius:14px; overflow:hidden; box-shadow:0 4px 16px rgba(15,49,114,0.12);">
                <iframe class="youtube-preview-iframe" src="${ytEmbedUrl}" width="100%" height="250" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen style="display:block; border-radius:14px;"></iframe>
            </div>
        </div>`;

    return `
    <div class="content-card">
        <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined drag-handle" title="Drag to reorder" style="cursor:grab; color:#94a3b8; font-size:22px; user-select:none;">drag_indicator</span>
                <div class="step-circle step-number">${idx + 1}</div>
                <span class="badge-pill" style="background: rgba(24,72,200,0.1); color:#1848c8;">${typeLabel}</span>
                ${hasResolvedMedia ? '<span style="background:#D1FAE5;color:#065F46;font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;">✓ Media Ready</span>' : ''}
                ${(!hasResolvedMedia && mediaMissing) ? '<span style="background:#FEF9C3;color:#92400E;font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;border:1px solid #FDE047;">⚠ No Media</span>' : ''}
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
                    <option value="youtube_video" ${type === 'youtube_video' ? 'selected' : ''}>YouTube Video</option>
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
            ${mediaSection}
            ${youtubeSection}
            <input type="hidden" name="contents[${idx}][media_missing]" value="${mediaMissing ? '1' : '0'}">
        </div>
    </div>`;
}

function buildAiDragDropPairHtml(qIndex, pairIndex, leftText = '', rightText = '') {
    return `
    <div class="drag-drop-pair" style="display:flex;gap:12px;align-items:center;background:white;border:1.5px solid #E5EAF2;border-radius:14px;padding:12px;margin-bottom:8px;flex-wrap:wrap;">
        <div style="flex:1;min-width:120px;">
            <label style="font-size:12px;font-weight:600;color:#6B7280;display:block;margin-bottom:4px;">Left Item</label>
            <input type="text" name="quiz[${qIndex}][drag_drop_pairs][${pairIndex}][left_text]" value="${escapeHtml(leftText)}" class="field-input" placeholder="e.g., Letter A" style="padding:8px 12px;font-size:13px;width:100%;">
            <div style="margin-top:4px;">
                <input type="hidden" name="quiz[${qIndex}][drag_drop_pairs][${pairIndex}][left_image]" value="" class="drag-drop-image-path left-image-path">
                <div class="media-upload-widget" data-context="quiz_media" data-accept="image/*" style="padding:6px;border-radius:10px;">
                    <div class="upload-trigger" style="gap:6px;">
                        <input type="file" accept="image/*" class="ajax-file-input" data-side="left" onchange="handleDragDropImageUpload(this, ${qIndex}, ${pairIndex})">
                        <span class="upload-icon material-symbols-outlined" style="font-size:16px;color:#94a3b8;">add_photo_alternate</span>
                        <div class="upload-spinner"></div>
                        <span class="upload-label" style="font-size:11px;">Add image</span>
                    </div>
                    <div class="media-thumb-wrap" style="margin-top:4px;"></div>
                </div>
            </div>
        </div>
        <div style="display:flex;align-items:center;padding:0 4px;color:#94a3b8;">
            <span class="material-symbols-outlined">arrow_forward</span>
        </div>
        <div style="flex:1;min-width:120px;">
            <label style="font-size:12px;font-weight:600;color:#6B7280;display:block;margin-bottom:4px;">Right Match</label>
            <input type="text" name="quiz[${qIndex}][drag_drop_pairs][${pairIndex}][right_text]" value="${escapeHtml(rightText)}" class="field-input" placeholder="e.g., Hand sign for A" style="padding:8px 12px;font-size:13px;width:100%;">
            <div style="margin-top:4px;">
                <input type="hidden" name="quiz[${qIndex}][drag_drop_pairs][${pairIndex}][right_image]" value="" class="drag-drop-image-path right-image-path">
                <div class="media-upload-widget" data-context="quiz_media" data-accept="image/*" style="padding:6px;border-radius:10px;">
                    <div class="upload-trigger" style="gap:6px;">
                        <input type="file" accept="image/*" class="ajax-file-input" data-side="right" onchange="handleDragDropImageUpload(this, ${qIndex}, ${pairIndex})">
                        <span class="upload-icon material-symbols-outlined" style="font-size:16px;color:#94a3b8;">add_photo_alternate</span>
                        <div class="upload-spinner"></div>
                        <span class="upload-label" style="font-size:11px;">Add image</span>
                    </div>
                    <div class="media-thumb-wrap" style="margin-top:4px;"></div>
                </div>
            </div>
        </div>
        <button type="button" onclick="removeDragDropPair(this)" class="option-remove-btn" style="margin-top:16px;">
            <span class="material-symbols-outlined text-sm">close</span>
        </button>
    </div>`;
}

function buildAiQuizCard(q, idx) {
    const isTrueFalse = (q.type === 'true_false');
    const isDragDrop = (q.type === 'drag_drop');
    const isGesture = (q.type === 'gesture');

    // Build optionsHtml only for multiple_choice/true_false
    let optionsHtml = '';
    if (!isDragDrop && !isGesture) {
        let options = Array.isArray(q.options) && q.options.length >= 2
            ? q.options
            : (isTrueFalse ? ['True', 'False'] : ['', '', '', '']);
        const correct = typeof q.correct_index === 'number' ? q.correct_index : 0;
        const letters  = ['A', 'B', 'C', 'D', 'E', 'F'];

        optionsHtml = options.map((opt, optIdx) => `
            <div class="option-row">
                <div class="option-letter">${letters[optIdx] || optIdx}</div>
                <div class="option-body">
                    <input type="text" name="quiz[${idx}][options][${optIdx}][text]"
                           value="${escapeHtml(typeof opt === 'object' ? (opt.text || '') : opt)}"
                           class="option-text-input"
                           placeholder="Option ${letters[optIdx] || optIdx} text"
                           ${isTrueFalse ? 'readonly style="background:#F8FAFC;color:#64748b;"' : ''}>
                    ${isTrueFalse ? '' : `
                    <div class="option-image-row">
                        <input type="file" name="quiz[${idx}][options][${optIdx}][image]" accept="image/*" class="option-image-input" onchange="previewOptionImage(this)">
                        <img class="option-image-preview" src="" alt="">
                    </div>`}
                </div>
                <div class="option-correct-row">
                    <input type="radio" name="quiz[${idx}][correct]" value="${optIdx}" ${correct === optIdx ? 'checked' : ''}>
                    <label>Correct</label>
                </div>
                ${isTrueFalse ? '<div style="width:24px;"></div>' : `
                <button type="button" class="option-remove-btn" onclick="removeOption(this)">
                    <span class="material-symbols-outlined text-sm">close</span>
                </button>`}
            </div>`).join('');
    }

    // Build drag drop pairs HTML
    let dragDropPairsHtml = '';
    if (isDragDrop) {
        const pairs = Array.isArray(q.drag_drop_pairs) ? q.drag_drop_pairs : [{}, {}];
        dragDropPairsHtml = pairs.map((pair, pIdx) => {
            const left = pair.left_text || pair.left || '';
            const right = pair.right_text || pair.right || '';
            return buildAiDragDropPairHtml(idx, pIdx, left, right);
        }).join('');
    }

    const gestureIds = Array.isArray(q.gesture_ids) ? q.gesture_ids : [];
    const gestureWarning = isGesture && (
        q.gesture_warning === true ||
        !q.gesture_module_id ||
        gestureIds.length === 0
    );
    const gestureWarningBadge = gestureWarning ? `
        <div class="gesture-warning-badge" style="background:#FEF9C3;border:1.5px solid #FDE047;border-radius:12px;padding:12px 16px;display:flex;align-items:flex-start;gap:10px;margin-top:12px;">
            <span style="font-size:16px;flex-shrink:0;line-height:1.5;">⚠️</span>
            <div>
                <p style="font-size:12px;font-weight:800;color:#92400E;margin:0 0 2px;">No Gesture Available</p>
                <p style="font-size:11px;color:#A16207;margin:0;">No gesture found in the database. Use the gesture module selector above to choose gestures manually.</p>
            </div>
        </div>` : '';

    return `
    <div class="quiz-question bg-slate-50 rounded-xl p-4 mb-4 border border-slate-100">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
                <span class="material-symbols-outlined drag-handle" title="Drag to reorder" style="cursor:grab; color:#94a3b8; font-size:22px; user-select:none;">drag_indicator</span>
                <div class="step-circle" style="background:#D97706;width:24px;height:24px;border-radius:50%;color:white;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;">${idx + 1}</div>
                <span class="text-sm font-bold text-slate-500 question-label">Question ${idx + 1}</span>
                ${isTrueFalse ? '<span style="background:#FEF3C7;color:#D97706;font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;">True/False</span>' : ''}
                ${isDragDrop ? '<span style="background:#E0F2FE;color:#0369A1;font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;">Drag & Drop</span>' : ''}
                ${isGesture ? '<span style="background:#D1FAE5;color:#065F46;font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;">Gesture Recognition</span>' : ''}
                ${gestureWarning ? '<span style="background:#FEF9C3;color:#92400E;font-size:11px;font-weight:700;padding:3px 10px;border-radius:99px;border:1px solid #FDE047;">⚠ No Gesture</span>' : ''}
            </div>
            <button type="button" onclick="removeQuizQuestion(this)" class="text-red-400 hover:text-red-600">
                <span class="material-symbols-outlined text-sm">close</span>
            </button>
        </div>
        <div class="space-y-3">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question</label>
                <input type="text" name="quiz[${idx}][question]" value="${escapeHtml(q.question || '')}" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" placeholder="Question text">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question Type</label>
                    <select name="quiz[${idx}][type]" onchange="handleQuestionTypeChange(this)" class="w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all question-type">
                        <option value="multiple_choice" ${q.type === 'multiple_choice' ? 'selected' : ''}>Multiple Choice</option>
                        <option value="true_false" ${isTrueFalse ? 'selected' : ''}>True / False</option>
                        <option value="drag_drop" ${isDragDrop ? 'selected' : ''}>Drag and Drop</option>
                        <option value="gesture" ${isGesture ? 'selected' : ''}>Gesture Recognition</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Question Image (Optional)</label>
                    <input type="hidden" name="quiz[${idx}][existing_media]" value="" class="media-path-input">
                    <div class="media-upload-widget" data-context="quiz_media">
                        <div class="upload-trigger">
                            <input type="file" accept="image/*" class="ajax-file-input" onchange="handleAjaxUpload(this, 'quiz_media')">
                            <span class="upload-icon material-symbols-outlined text-slate-400" style="font-size:20px;">cloud_upload</span>
                            <div class="upload-spinner"></div>
                            <span class="upload-label">Upload question image (optional)</span>
                        </div>
                        <div class="media-thumb-wrap"></div>
                        <div class="media-upload-error"></div>
                    </div>
                </div>
            </div>
            
            <div class="options-container ${(!isDragDrop && !isGesture) ? '' : 'hidden'}">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Options</label>
                <p class="text-xs text-slate-400 mb-2">${isTrueFalse ? 'Select the correct answer.' : 'Each option can have text and/or an image.'}</p>
                <div class="space-y-2 options-list">${optionsHtml}</div>
                ${isTrueFalse ? '' : '<button type="button" onclick="addOption(this)" class="text-sm text-[#0d326b] font-semibold hover:underline mt-2">+ Add Option</button>'}
            </div>

            <!-- Drag and Drop Pairs -->
            <div class="drag-drop-container ${isDragDrop ? '' : 'hidden'}">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Drag and Drop Pairs</label>
                <p class="text-xs text-slate-400 mb-2">Match items from the left column to the right column.</p>
                <div class="space-y-2 drag-drop-pairs-list">${dragDropPairsHtml}</div>
                <button type="button" onclick="addDragDropPair(this)" class="text-sm text-[#0d326b] font-semibold hover:underline mt-2">
                    + Add Pair
                </button>
            </div>
            
            <!-- Gesture Recognition Fields -->
            <div class="gesture-quiz-container ${isGesture ? '' : 'hidden'}">
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gesture Recognition Settings</label>
                <p class="text-xs text-slate-400 mb-2">Select a gesture module and the specific gestures students need to perform.</p>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Gesture Module</label>
                        <select name="quiz[${idx}][gesture_module_id]" class="field-select gesture-module-select w-full px-4 py-3 border border-slate-200 rounded-xl focus:border-[#0d326b] focus:ring-2 focus:ring-[#0d326b]/20 outline-none transition-all" data-selected-ids="${escapeHtml(JSON.stringify(q.gesture_ids || []))}" onchange="loadGesturesForModule(this, ${idx})">
                            <option value="">Select a module...</option>
                            @foreach($gestureModules as $module)
                                <option value="{{ $module->module_id }}" ${q.gesture_module_id == {{ $module->module_id }} ? 'selected' : ''}>{{ $module->display_name ?? $module->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Select Gestures to Recognize</label>
                        <p class="text-xs text-slate-400 mb-2">Click to select gestures. Students will need to perform all selected gestures.</p>
                        <div id="gestureCheckboxes_${idx}" class="flex flex-wrap gap-2 mt-2" style="min-height:60px;">
                            <span class="text-sm text-slate-400">Select a module first</span>
                        </div>
                    </div>
                    <div class="selected-gestures-preview" style="display:none;">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Selected Gestures</label>
                        <div class="flex flex-wrap gap-2" id="selectedGestureTags_${idx}"></div>
                    </div>
                    ${gestureWarningBadge}
                </div>
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
    toast.style.cssText = 'position:fixed;bottom:28px;right:28px;background:linear-gradient(135deg,#0d326b,#1a6fd4);color:white;padding:14px 22px;border-radius:16px;font-weight:700;font-size:14px;box-shadow:0 8px 30px rgba(13,50,107,0.4);z-index:20000;transition:all 0.4s;';
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
