<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
@php
    // Helper function to resolve absolute media URLs correctly
    $getMediaUrl = function($path) {
        if (!$path) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        $cleanPath = ltrim($path, '/');
        if (file_exists(public_path($cleanPath))) {
            return asset($cleanPath);
        }
        if (file_exists(public_path('storage/' . $cleanPath))) {
            return asset('storage/' . $cleanPath);
        }
        return asset($cleanPath);
    };

    // Helper function to check if media path is a video
    $isVideo = function($path, $contentType = null) {
        if ($contentType === 'gesture_demo') return true;
        if (!$path) return false;
        $ext = strtolower(pathinfo(parse_url($path, PHP_URL_PATH), PATHINFO_EXTENSION));
        return in_array($ext, ['mp4', 'webm', 'ogg', 'mov', 'm4v', 'avi']);
    };
@endphp

<style>
* { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }

/* ============ Toggle Controls ============ */
.preview-controls {
    max-width: 900px;
    margin: 0 auto 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.toggle-group {
    display: inline-flex;
    background: rgba(15,49,114,0.06);
    border-radius: 12px;
    padding: 4px;
    gap: 4px;
}
.toggle-btn {
    border: none;
    background: transparent;
    padding: 8px 18px;
    border-radius: 9px;
    font-size: 13px;
    font-weight: 700;
    color: #4b7bbb;
    cursor: pointer;
    transition: all 0.25s;
}
.toggle-btn.active {
    background: #1848c8;
    color: white;
    box-shadow: 0 3px 10px rgba(24,72,200,0.3);
}

/* ============ Mobile Frame ============ */
.device-stage { max-width: 900px; margin: 0 auto; }
.phone-mockup {
    max-width: 390px;
    margin: 0 auto;
    background: #eaf5fd;
    border-radius: 40px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    border: 8px solid #1a1a1a;
    position: relative;
    min-height: 750px;
    display: flex;
    flex-direction: column;
}
.phone-mockup .status-bar {
    padding: 12px 20px 8px;
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    font-weight: 600;
    color: #0f3172;
}

/* ============ Web Frame ============ */
.browser-mockup {
    max-width: 900px;
    margin: 0 auto;
    background: #fff;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    border: 1px solid rgba(15,49,114,0.1);
    display: none;
    flex-direction: column;
}
.browser-topbar {
    background: #eef3fb;
    padding: 12px 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid rgba(15,49,114,0.08);
}
.browser-dot { width: 11px; height: 11px; border-radius: 50%; }
.browser-urlbar {
    flex: 1;
    margin-left: 10px;
    background: white;
    border-radius: 8px;
    padding: 6px 14px;
    font-size: 12px;
    color: #6B7280;
    border: 1px solid rgba(15,49,114,0.08);
}
.web-body {
    display: flex;
    min-height: 600px;
    background: #f4f8fd;
}
.web-sidebar {
    width: 220px;
    background: #0f3172;
    color: white;
    padding: 24px 18px;
    flex-shrink: 0;
}
.web-sidebar .logo { font-size: 20px; font-weight: 800; letter-spacing: 2px; margin-bottom: 24px; }
.web-sidebar .nav-item {
    padding: 10px 12px;
    border-radius: 10px;
    font-size: 13px;
    font-weight: 600;
    margin-bottom: 6px;
    opacity: 0.7;
}
.web-sidebar .nav-item.current { background: rgba(255,255,255,0.12); opacity: 1; }
.web-main { flex: 1; padding: 28px 32px; overflow-y: auto; }
.web-grid-2col { display: flex; gap: 24px; align-items: flex-start; }
.web-content-col { flex: 1.4; }
.web-side-col { flex: 1; }

/* ============ Shared ============ */
.glass-card {
    background: rgba(255,255,255,0.62);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.85);
    border-radius: 20px;
    padding: 18px;
    margin-bottom: 12px;
    box-shadow: 0 2px 12px rgba(15,49,114,0.09);
}
.web-main .glass-card { background: white; border: 1px solid rgba(15,49,114,0.08); }
.slide-accent { height: 4px; border-radius: 4px; margin: -18px -18px 14px -18px; }

.lesson-progress-wrap { padding: 0 4px; margin-bottom: 14px; }
.lesson-progress-track { height: 6px; border-radius: 99px; background: rgba(15,49,114,0.10); overflow: hidden; }
.lesson-progress-fill { height: 100%; border-radius: 99px; transition: width 0.35s ease, background 0.35s ease; }
.lesson-progress-meta { display: flex; justify-content: space-between; align-items: center; margin-top: 6px; }
.slide-pill { font-size: 10.5px; font-weight: 800; letter-spacing: 0.4px; padding: 3px 10px; border-radius: 99px; transition: all 0.3s; }

.slide-dot { height: 8px; border-radius: 99px; transition: all 0.3s; cursor: pointer; display: inline-block; }
.slide-dot.active { width: 22px; }
.slide-dot.inactive { width: 8px; background: rgba(15,49,114,0.15); }

.primary-btn {
    background: #1848c8; color: white; padding: 14px 28px; border-radius: 60px;
    font-weight: 700; border: none; cursor: pointer; transition: all 0.3s;
    box-shadow: 0 5px 18px rgba(24,72,200,0.28); flex: 1;
}
.primary-btn:hover { background: #0f3172; transform: translateY(-2px); }
.gold-btn { background: #D97706; }
.gold-btn:hover { background: #B45309; }
.ghost-btn {
    background: rgba(255,255,255,0.62); border: 1px solid rgba(255,255,255,0.85);
    padding: 14px 28px; border-radius: 60px; font-weight: 700; color: #0f3172;
    cursor: pointer; transition: all 0.3s; flex: 1;
}
.web-main .ghost-btn { background: #f4f8fd; }
.ghost-btn:hover { background: rgba(255,255,255,0.85); }

.option-card {
    display: flex; align-items: center; gap: 12px; border: 1.5px solid rgba(255,255,255,0.85);
    border-radius: 16px; padding: 13px 16px; margin-bottom: 8px; background: rgba(255,255,255,0.62);
    cursor: pointer; transition: all 0.2s ease;
}
.web-main .option-card { background: #f9fbfe; border-color: rgba(15,49,114,0.08); }
.option-card:hover { background: rgba(255,255,255,0.9); border-color: rgba(24,72,200,0.35); transform: translateX(2px); }
.option-card.selected { background: rgba(24,72,200,0.08); border-color: #1848c8; }
.option-circle {
    width: 32px; height: 32px; border-radius: 10px; display: flex; align-items: center;
    justify-content: center; flex-shrink: 0; background: rgba(15,49,114,0.08);
    font-weight: 800; font-size: 13px; color: #4b7bbb; transition: all 0.2s ease;
}
.option-card:hover .option-circle, .option-card.selected .option-circle { background: #1848c8; color: white; }
.option-image-thumb {
    width: 56px; height: 56px; border-radius: 10px; object-fit: cover; flex-shrink: 0;
}

.feedback-bubble {
    flex: 1; display: flex; align-items: flex-start; gap: 7px; background: rgba(255,255,255,0.75);
    border: 1px solid rgba(255,255,255,0.9); border-radius: 16px; padding: 12px;
}
.senya-tip { display: flex; align-items: flex-end; gap: 10px; margin-bottom: 14px; }
.senya-tip img { width: 56px; height: 56px; flex-shrink: 0; object-fit: contain; }
.tip-bubble {
    flex: 1; background: rgba(255,255,255,0.62); border: 1px solid rgba(255,255,255,0.85);
    border-radius: 14px; padding: 12px; font-size: 12px; color: #0f3172; font-weight: 500; line-height: 1.5;
}
.web-main .tip-bubble { background: #f4f8fd; }

.progress-dots { display: flex; gap: 4px; }
.progress-dot { flex: 1; height: 5px; border-radius: 99px; transition: background 0.3s; }
.progress-dot.completed { background: #22c55e; }
.progress-dot.active { background: #2563EB; }
.progress-dot.pending { background: rgba(15,49,114,0.10); }

.badge {
    background: rgba(15,49,114,0.08); border-radius: 8px; padding: 4px 10px; font-size: 11px;
    font-weight: 800; color: #1848c8; letter-spacing: 0.5px; display: inline-block; transition: all 0.3s;
}
.badge-yellow { background: rgba(245,158,11,0.13); color: #92400E; }

.slide-nav, .quiz-nav { display: flex; gap: 10px; margin-top: 10px; }

.preview-header { display: flex; justify-content: space-between; align-items: center; padding: 12px 20px 8px; }
.preview-header .logo { font-size: 22px; font-weight: 800; color: #0f3172; letter-spacing: 2px; }
.preview-header .exit-btn {
    background: rgba(255,255,255,0.7); border-radius: 12px; padding: 6px 12px; font-size: 13px;
    font-weight: 700; color: #6B7280; border: 1px solid rgba(255,255,255,0.85); cursor: pointer;
}
.preview-content { padding: 0 16px 16px; flex: 1; display: flex; flex-direction: column; }
.preview-controls-wrapper {
    background: white;
    border-radius: 16px;
    padding: 16px 20px;
    max-width: 900px;
    margin: 0 auto 20px;
    box-shadow: 0 4px 20px rgba(15,49,114,0.1);
    border: 1px solid rgba(15,49,114,0.06);
}

.preview-controls {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 12px;
}
.media-wrap { margin: 10px 0; border-radius: 14px; overflow: hidden; box-shadow: 0 6px 20px rgba(15,49,114,0.14); background: #0f3172; }
.slide-image { width: 100%; display: block; max-height: 220px; object-fit: cover; }
.slide-video { width: 100%; display: block; max-height: 220px; background: #000; }
.hero-image { width: 80px; height: 80px; flex-shrink: 0; object-fit: contain; }
.quiz-media-wrap { border-radius: 14px; overflow: hidden; box-shadow: 0 6px 20px rgba(15,49,114,0.14); margin: 0 auto 12px; background: #f8f9fa; }
.quiz-media { width: 100%; display: block; max-height: 160px; object-fit: contain; padding: 8px; background: #fff; }

.preview-close-btn {
    position: sticky; top: 10px; float: right; background: rgba(255,255,255,0.9); border: none;
    border-radius: 50%; width: 40px; height: 40px; font-size: 20px; cursor: pointer;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15); z-index: 100; display: flex; align-items: center;
    justify-content: center; transition: all 0.3s; margin: 10px 10px 0 0;
}
.preview-close-btn:hover { transform: scale(1.1); background: white; }

/* Lesson vs Quiz panel switching (within either frame) */
.content-panel { display: none; }
.content-panel.active { display: flex; flex-direction: column; flex: 1; }
.web-main .content-panel.active { display: block; }

.quiz-question-panel { display: none; }
.quiz-question-panel.active { display: block; }

/* ============ Drag & Drop ============ */
.dnd-section { padding: 0 2px; }
.dnd-header {
    display: flex; align-items: center; gap: 8px; margin-bottom: 10px;
}
.dnd-header-badge {
    background: linear-gradient(135deg,#1848c8,#6d28d9); color: white;
    border-radius: 8px; padding: 4px 10px; font-size: 10px; font-weight: 800; letter-spacing: 0.6px;
}
.dnd-hint {
    font-size: 11.5px; color: #64748b; font-weight: 600;
    background: rgba(24,72,200,0.06); border-radius: 8px; padding: 5px 10px; flex: 1;
}
.dnd-columns-label {
    display: flex; gap: 10px; margin-bottom: 6px; padding: 0 2px;
}
.dnd-col-label {
    flex: 1; text-align: center; font-size: 10px; font-weight: 800; letter-spacing: 0.8px;
    color: #94a3b8; text-transform: uppercase;
}
.dragdrop-wrap { display: flex; gap: 10px; margin-bottom: 8px; }
.dragdrop-wrap.answered { pointer-events: none; }
.dragdrop-col { flex: 1; display: flex; flex-direction: column; gap: 7px; }

.dragdrop-item {
    display: flex; align-items: center; gap: 9px;
    background: #fff; border: 2px solid #e2e8f0; border-radius: 14px;
    padding: 11px 13px; cursor: pointer; transition: all 0.18s ease;
    font-size: 13px; font-weight: 700; color: #1e293b;
    box-shadow: 0 1px 4px rgba(15,49,114,0.06);
    min-height: 48px; position: relative;
}
.dragdrop-item:hover:not(.dd-matched):not(.dd-wrong) {
    border-color: #93c5fd; background: #f0f7ff;
    transform: translateY(-1px); box-shadow: 0 3px 12px rgba(24,72,200,0.14);
}
.dragdrop-item.dd-selected {
    border-color: #1848c8; background: rgba(24,72,200,0.08);
    box-shadow: 0 0 0 3px rgba(24,72,200,0.18);
    transform: scale(1.02);
}
.dragdrop-item.dd-matched {
    border-color: #059669; background: rgba(5,150,105,0.09);
    cursor: default; animation: ddMatchPop 0.3s ease;
}
.dragdrop-item.dd-matched::after {
    content: '✓'; position: absolute; right: 10px; top: 50%; transform: translateY(-50%);
    font-size: 13px; font-weight: 900; color: #059669;
}
.dragdrop-item.dd-wrong {
    border-color: #ef4444; background: rgba(239,68,68,0.09);
    animation: ddShake 0.32s ease;
}
.dd-thumb {
    width: 34px; height: 34px; border-radius: 8px; object-fit: cover; flex-shrink: 0;
    border: 1px solid rgba(15,49,114,0.1);
}
.dnd-connector {
    display: flex; align-items: center; justify-content: center;
    padding-top: 4px; color: #cbd5e1; font-size: 16px; flex-shrink: 0;
    width: 20px; align-self: stretch;
}
.dnd-score-bar {
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(15,49,114,0.04); border-radius: 10px; padding: 8px 12px; margin-top: 6px;
}
.dnd-score-text { font-size: 11.5px; font-weight: 700; color: #475569; }
.dnd-matched-count { font-size: 12px; font-weight: 800; color: #1848c8; }

@keyframes ddShake { 0%,100%{transform:translateX(0)} 20%{transform:translateX(-5px)} 60%{transform:translateX(5px)} }
@keyframes ddMatchPop { 0%{transform:scale(1)} 50%{transform:scale(1.04)} 100%{transform:scale(1)} }

/* ============ Gesture Recognition ============ */
.gesture-section { padding: 0 2px; }
.gesture-header {
    display: flex; align-items: center; gap: 8px; margin-bottom: 10px;
}
.gesture-header-badge {
    background: linear-gradient(135deg,#7c3aed,#a855f7); color: white;
    border-radius: 8px; padding: 4px 10px; font-size: 10px; font-weight: 800; letter-spacing: 0.6px;
}
.gesture-header-text {
    font-size: 11.5px; color: #64748b; font-weight: 600;
    background: rgba(124,58,237,0.06); border-radius: 8px; padding: 5px 10px; flex: 1;
}

.gesture-cam-wrap {
    width: 100%; aspect-ratio: 16/9; max-height: 160px; border-radius: 18px; overflow: hidden;
    background: linear-gradient(160deg,#0f172a 0%,#1e3a8a 60%,#312e81 100%);
    position: relative; margin-bottom: 10px; display: flex; align-items: center; justify-content: center;
    box-shadow: 0 8px 28px rgba(15,49,114,0.25);
}
.gesture-cam-scan {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
}
.gesture-cam-scan::before, .gesture-cam-scan::after {
    content: ''; position: absolute; width: 70px; height: 70px;
    border: 2.5px solid rgba(99,179,237,0.7); border-radius: 8px;
}
.gesture-cam-scan::before {
    top: 16px; left: 50%; transform: translateX(-50%);
    clip-path: polygon(0 0, 20% 0, 20% 2px, 2px 2px, 2px 20%, 0 20%,
                       80% 0, 100% 0, 100% 20%, calc(100% - 2px) 20%, calc(100% - 2px) 2px, 80% 2px);
    border: none;
}
.gesture-scan-ring {
    width: 80px; height: 80px; border-radius: 50%;
    border: 2px solid rgba(147,197,253,0.4);
    box-shadow: 0 0 0 6px rgba(99,179,237,0.08), 0 0 0 12px rgba(99,179,237,0.04);
    display: flex; align-items: center; justify-content: center;
    animation: gestPulse 2.4s ease-in-out infinite;
    position: relative; z-index: 1;
}
.gesture-hand-icon { font-size: 36px; filter: drop-shadow(0 0 8px rgba(147,197,253,0.6)); }
.gesture-cam-label {
    position: absolute; bottom: 10px; left: 0; right: 0; text-align: center;
    font-size: 10.5px; font-weight: 700; color: rgba(186,230,253,0.85); letter-spacing: 0.5px;
}
.gesture-cam-corner {
    position: absolute; width: 18px; height: 18px;
    border-color: rgba(99,179,237,0.6); border-style: solid; border-width: 0;
}
.gesture-cam-corner.tl { top: 10px; left: 10px; border-top-width: 2.5px; border-left-width: 2.5px; border-radius: 4px 0 0 0; }
.gesture-cam-corner.tr { top: 10px; right: 10px; border-top-width: 2.5px; border-right-width: 2.5px; border-radius: 0 4px 0 0; }
.gesture-cam-corner.bl { bottom: 10px; left: 10px; border-bottom-width: 2.5px; border-left-width: 2.5px; border-radius: 0 0 0 4px; }
.gesture-cam-corner.br { bottom: 10px; right: 10px; border-bottom-width: 2.5px; border-right-width: 2.5px; border-radius: 0 0 4px 0; }
@keyframes gestPulse {
    0%,100% { transform: scale(1); box-shadow: 0 0 0 6px rgba(99,179,237,0.08), 0 0 0 12px rgba(99,179,237,0.04); }
    50% { transform: scale(1.06); box-shadow: 0 0 0 10px rgba(99,179,237,0.12), 0 0 0 20px rgba(99,179,237,0.04); }
}

.gesture-targets-label {
    font-size: 10.5px; font-weight: 800; color: #7c3aed; letter-spacing: 0.6px;
    text-transform: uppercase; margin-bottom: 6px;
}
.gesture-chip-grid {
    display: flex; flex-wrap: wrap; gap: 7px; justify-content: flex-start; margin-bottom: 10px;
}
.gesture-chip {
    display: flex; align-items: center; gap: 7px;
    background: white; border: 1.5px solid #ede9fe; border-radius: 12px;
    padding: 7px 12px 7px 7px;
    font-size: 12.5px; font-weight: 700; color: #4c1d95;
    box-shadow: 0 1px 4px rgba(124,58,237,0.08);
}
.gesture-chip-img {
    width: 30px; height: 30px; border-radius: 8px; object-fit: cover;
    background: #f5f3ff; border: 1px solid #ede9fe; flex-shrink: 0;
}
.gesture-chip-placeholder {
    width: 30px; height: 30px; border-radius: 8px; background: #f5f3ff;
    display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0;
}

.gesture-confirm-btn {
    width: 100%; padding: 13px 16px; border: none; border-radius: 14px; cursor: pointer;
    background: linear-gradient(135deg,#7c3aed,#4f46e5);
    color: white; font-weight: 800; font-size: 13px;
    box-shadow: 0 5px 18px rgba(124,58,237,0.32);
    transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;
}
.gesture-confirm-btn:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(124,58,237,0.4); }
.gesture-confirm-btn:disabled { opacity: 0.55; cursor: default; transform: none; box-shadow: none; }
.gesture-confirm-btn.recognized {
    background: linear-gradient(135deg,#059669,#10b981);
    box-shadow: 0 5px 18px rgba(5,150,105,0.3);
}

.gesture-status-bar {
    display: flex; align-items: center; gap: 8px;
    background: rgba(124,58,237,0.06); border-radius: 10px; padding: 8px 12px; margin-top: 6px;
}
.gesture-status-dot {
    width: 8px; height: 8px; border-radius: 50%; background: #a855f7;
    animation: statusBlink 1.2s ease-in-out infinite; flex-shrink: 0;
}
.gesture-status-dot.ready { background: #22c55e; animation: none; }
.gesture-status-text { font-size: 11px; font-weight: 700; color: #6d28d9; }
@keyframes statusBlink { 0%,100%{opacity:1} 50%{opacity:0.3} }
</style>

@php
    $totalSlides = count($lessonData['contents']);
    $totalQuestions = count($lessonData['quiz'] ?? []);
    $colors = ['#2563EB', '#059669', '#F59E0B', '#8B5CF6', '#EF4444', '#EC4899', '#14B8A6'];

    // Helper to format image URLs — uses a closure to avoid fatal "redeclare" errors
    // when the preview blade is rendered more than once per PHP process (opcache / view cache).
    if (!function_exists('formatImageUrl')) {
        function formatImageUrl($path) {
            if (empty($path)) {
                return null;
            }
            if (filter_var($path, FILTER_VALIDATE_URL)) {
                return $path;
            }
            $normalizedPath = ltrim($path, '/');
            $normalizedPath = preg_replace('#^(storage/app/public/|storage/|public/)#', '', $normalizedPath);
            if ($normalizedPath === '') {
                return null;
            }
            $root = request()->root();
            if (str_ends_with($root, '/index.php')) {
                $root = substr($root, 0, -strlen('/index.php'));
            }
            return rtrim($root, '/') . '/storage/' . $normalizedPath;
        }
    }
    $fmtImg = 'formatImageUrl';

    // Normalize quiz options so the array/string mixing from the form never hits {{ }} directly.
    $normalizedQuiz = collect($lessonData['quiz'] ?? [])->map(function ($q) {
        $type = $q['type'] ?? 'multiple_choice';

        $opts = [];
        if ($type === 'true_false') {
            $opts = [['text' => 'True', 'image' => null], ['text' => 'False', 'image' => null]];
        } elseif ($type === 'multiple_choice') {
            $rawOpts = $q['options'] ?? ['Option A', 'Option B'];
            $opts = collect($rawOpts)->map(function ($opt) {
                if (is_array($opt)) {
                    return [
                        'text' => $opt['text'] ?? '',
                        'image' => (!empty($opt['image']) && is_string($opt['image'])) ? $opt['image'] : null,
                    ];
                }
                return ['text' => (string) $opt, 'image' => null];
            })->values()->all();
        }
        $q['_opts'] = $opts;
        $q['_media'] = (!empty($q['media']) && is_string($q['media'])) ? $q['media'] : null;
        $q['_type'] = $type;

        // Drag & drop: keep left column in order, shuffle the right column for the matching game.
        $q['_ddPairs'] = collect($q['drag_drop_pairs'] ?? [])->values()->all();
        $q['_ddRightShuffled'] = collect($q['_ddPairs'])->values()->shuffle()->all();

        // Gesture: list of gestures the student needs to perform.
        $q['_gestures'] = collect($q['gesture_details'] ?? [])->values()->all();

        return $q;
    });
@endphp

<!-- ============ TOP CONTROLS: Mobile/Web + Lesson/Quiz ============ -->
<div class="preview-controls-wrapper">
    <div class="preview-controls">
        <div class="toggle-group" id="deviceToggle">
            <button class="toggle-btn active" data-device="mobile" onclick="setDevice('mobile')">📱 Mobile</button>
            <button class="toggle-btn" data-device="web" onclick="setDevice('web')">🖥️ Web</button>
        </div>
        @if($totalQuestions > 0)
        <div class="toggle-group" id="contentToggle">
            <button class="toggle-btn {{ $totalSlides > 0 ? 'active' : '' }}" data-content="lesson" onclick="setContentMode('lesson')">📖 Lesson Preview</button>
            <button class="toggle-btn {{ $totalSlides == 0 ? 'active' : '' }}" data-content="quiz" onclick="setContentMode('quiz')">📝 Quiz Preview</button>
        </div>
        @endif
    </div>
</div>

<div class="device-stage">

    <!-- ============ MOBILE FRAME ============ -->
    <div class="phone-mockup" id="mobileFrame">
        <div class="status-bar"><span>9:41</span><span>📶 🔋</span></div>

        <div class="preview-content">
            <!-- Lesson panel -->
            <div class="content-panel {{ $totalSlides > 0 ? 'active' : '' }}" id="m-lessonPanel">
                @if($totalSlides == 0 && $totalQuestions > 0)
                    <div style="display:flex; flex-direction:column; align-items:center; justify-content:center; flex:1; padding:24px; text-align:center;">
                        <div style="width:60px; height:60px; background:rgba(15,49,114,0.08); border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:12px;">
                            <span class="material-symbols-outlined" style="font-size:28px; color:#4b7bbb;">description</span>
                        </div>
                        <p style="font-size:15px; font-weight:700; color:#0f3172; margin:0 0 6px;">No Lesson Content Yet</p>
                        <p style="font-size:12px; color:#64748b; margin:0; max-width:260px;">This lesson only has quiz questions. Add lesson content slides to show material here.</p>
                    </div>
                @elseif($totalSlides > 0)
                    <div class="preview-header">
                        <span class="logo">SEÑAS</span>
                    </div>
                    <div class="glass-card">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <div style="flex: 1;">
                                <span class="badge" id="m-moduleBadge">MODULE</span>
                                <h3 style="font-size: 20px; font-weight: 800; color: #0f3172; margin: 4px 0;">{{ $lessonData['title'] }}</h3>
                                <p style="font-size: 12px; color: #4b7bbb; font-weight: 500;">{{ $totalSlides }} slides · ~2 min read</p>
                            </div>
                            <img src="{{ asset('images/wavingSenya.png') }}" alt="Senya" class="hero-image">
                        </div>
                    </div>
                    <div class="lesson-progress-wrap">
                        <div class="lesson-progress-track">
                            <div class="lesson-progress-fill" id="m-progressFill" style="width: {{ round(100/$totalSlides) }}%; background: {{ $colors[0] }};"></div>
                        </div>
                        <div class="lesson-progress-meta">
                            <span class="slide-pill" id="m-slidePill" style="background: {{ $colors[0] }}1A; color: {{ $colors[0] }};">SLIDE 1 / {{ $totalSlides }}</span>
                            <div style="display: flex; gap: 6px;" id="m-slideDots">
                                @foreach($lessonData['contents'] as $index => $content)
                                    <div class="slide-dot {{ $index == 0 ? 'active' : 'inactive' }}" data-slide="{{ $index }}" onclick="setSlide('m', {{ $index }})" style="cursor:pointer; {{ $index == 0 ? 'background:'.$colors[0].';' : '' }}"></div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <div id="m-slideContainer" style="flex:1; display:flex; flex-direction:column;">
                        @foreach($lessonData['contents'] as $index => $current)
                            @php $slideColor = $colors[$index % count($colors)]; @endphp
                            <div class="slide-content" id="m-slide-{{ $index }}" data-slide="{{ $index }}" style="{{ $index == 0 ? 'flex:1; display:flex; flex-direction:column;' : 'display:none;' }}">
                                <div class="glass-card" style="flex:1;">
                                    <div class="slide-accent" style="background: {{ $slideColor }};"></div>
                                    <h3 style="font-size: 17px; font-weight: 800; color: {{ $slideColor }}; margin-bottom: 10px;">{{ $current['title'] ?? 'Slide Title' }}</h3>
                                   @if(!empty($current['media']) && is_string($current['media']))
    @php
        $mediaUrl = formatImageUrl($current['media']);
        // ✅ Use the $isVideo helper function that checks content_type too
        $isVideoFile = $isVideo($current['media'], $current['content_type'] ?? null);
    @endphp
    @if($mediaUrl)
        <div class="media-wrap" style="background:#0f172a;border-radius:14px;overflow:hidden;">
            @if($isVideoFile)
                <video controls autoplay muted loop playsinline class="slide-video" style="width:100%;display:block;max-height:220px;background:#000;">
                    <source src="{{ $mediaUrl }}" type="video/mp4">
                    Your browser does not support video playback.
                </video>
            @else
                <img src="{{ $mediaUrl }}" alt="Slide image" class="slide-image" style="width:100%;display:block;max-height:220px;object-fit:cover;" onerror="this.parentElement.style.display='none'">
            @endif
        </div>
    @endif
@endif
                                    <p style="font-size: 14px; color: #334155; line-height: 1.6;">{{ $current['content_text'] ?? 'Content goes here...' }}</p>
                                </div>
                                <div class="senya-tip">
                                    <img src="{{ asset('images/senya_teaching.png') }}" alt="Senya Teaching">
                                    <div class="tip-bubble">Hi! I'm Senya. Let's learn about FSL before your quiz!</div>
                                </div>
                                <div class="slide-nav">
                                    <button class="ghost-btn" onclick="prevSlide('m')" style="{{ $index == 0 ? 'display:none;' : '' }}">← Back</button>
                                    <button class="primary-btn {{ $index == $totalSlides-1 ? 'gold-btn' : '' }}" style="{{ $index == 0 ? 'flex:1;' : '' }} background:{{ $index == $totalSlides-1 ? '' : $slideColor }};" onclick="nextSlide('m')">
                                        {{ $index == $totalSlides-1 ? 'Start Quiz' : 'Next →' }}
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div style="text-align:center; padding:40px 20px; color:#6B7280;">
                        <p style="font-size:16px;">No content added yet.</p>
                        <p style="font-size:14px;">Add slides in the lesson creation form.</p>
                    </div>
                @endif
            </div>

            <!-- Quiz panel -->
            <div class="content-panel {{ $totalSlides == 0 && $totalQuestions > 0 ? 'active' : '' }}" id="m-quizPanel">
                @if($totalQuestions > 0)
                    {{-- Progress header --}}
                    <div class="glass-card" id="m-quizHeader">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <span style="font-size:12px; font-weight:700; color:#0f3172;" id="m-quizLabel">Question 1 of {{ $totalQuestions }}</span>
                            <span class="badge badge-yellow" id="m-quizXpBadge">⚡ 10 XP each</span>
                        </div>
                        <div class="progress-dots" id="m-quizDots">
                            @foreach($normalizedQuiz as $index => $quizItem)
                                <div class="progress-dot {{ $index == 0 ? 'active' : 'pending' }}"></div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Question panels --}}
                    @foreach($normalizedQuiz as $qIndex => $q)
                        @php $correctIdx = (int)($q['correct'] ?? 0); @endphp
                        <div class="quiz-question-panel {{ $qIndex == 0 ? 'active' : '' }}" id="m-q-{{ $qIndex }}" data-correct="{{ $correctIdx }}">
                            <div class="glass-card" style="text-align:center; padding:24px;">
                             @if(!empty($q['_media']))
    @php 
        $imageUrl = formatImageUrl($q['_media']);
        $qIsVideo = $isVideo($q['_media'], $q['_type'] ?? null);
    @endphp
    @if($imageUrl)
        <div class="quiz-media-wrap" style="background:#0f172a;border-radius:14px;overflow:hidden;margin:0 auto 12px;">
            @if($qIsVideo)
                <video controls autoplay muted loop playsinline class="quiz-media" style="width:100%;display:block;max-height:160px;background:#000;object-fit:contain;padding:8px;">
                    <source src="{{ $imageUrl }}" type="video/mp4">
                    Your browser does not support video playback.
                </video>
            @else
                <img src="{{ $imageUrl }}" alt="Quiz image" class="quiz-media" style="width:100%;display:block;max-height:160px;object-fit:contain;padding:8px;background:#fff;"
                     onerror="this.parentElement.innerHTML='<div style=\'padding:20px;text-align:center;color:#999;font-size:13px;\'>⚠️ Image not available</div>'">
            @endif
        </div>
    @endif
@endif
                                <p style="font-size:16px; font-weight:800; color:#0f3172; margin-top:10px;">{{ $q['question'] ?? 'Sample Question' }}</p>
                            </div>

                            @if($q['_type'] === 'drag_drop')
                                @if(count($q['_ddPairs']) > 0)
                                    <div class="dnd-section">
                                        <div class="dnd-header">
                                            <span class="dnd-header-badge">MATCH</span>
                                            <span class="dnd-hint">Tap left → then tap its match on the right</span>
                                        </div>
                                        <div class="dnd-columns-label">
                                            <span class="dnd-col-label">Items</span>
                                            <span class="dnd-col-label">Matches</span>
                                        </div>
                                        <div class="dragdrop-wrap" id="m-dd-{{ $qIndex }}" data-total="{{ count($q['_ddPairs']) }}">
                                            <div class="dragdrop-col">
                                                @foreach($q['_ddPairs'] as $pIndex => $pair)
                                                    <div class="dragdrop-item dd-left" data-match="{{ $pair['match_id'] }}" onclick="selectDragItem('m', {{ $qIndex }}, 'left', '{{ $pair['match_id'] }}', this)">
                                                        @if(!empty($pair['left_image']))
                                                            @php $li = formatImageUrl($pair['left_image']); @endphp
                                                            @if($li)<img src="{{ $li }}" class="dd-thumb" onerror="this.style.display='none'">@endif
                                                        @else
                                                            <span style="font-size:18px;flex-shrink:0;">🔵</span>
                                                        @endif
                                                        <span>{{ $pair['left_text'] ?: 'Item '.($pIndex+1) }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="dnd-connector" style="display:flex;flex-direction:column;justify-content:space-around;padding:0;width:16px;">
                                                @foreach($q['_ddPairs'] as $p)
                                                    <div style="flex:1;display:flex;align-items:center;justify-content:center;">
                                                        <div style="width:1.5px;height:100%;background:linear-gradient(to bottom,#cbd5e1,#e2e8f0);min-height:20px;"></div>
                                                    </div>
                                                @endforeach
                                            </div>
                                            <div class="dragdrop-col">
                                                @foreach($q['_ddRightShuffled'] as $pair)
                                                    <div class="dragdrop-item dd-right" data-match="{{ $pair['match_id'] }}" onclick="selectDragItem('m', {{ $qIndex }}, 'right', '{{ $pair['match_id'] }}', this)">
                                                        @if(!empty($pair['right_image']))
                                                            @php $ri = formatImageUrl($pair['right_image']); @endphp
                                                            @if($ri)<img src="{{ $ri }}" class="dd-thumb" onerror="this.style.display='none'">@endif
                                                        @else
                                                            <span style="font-size:18px;flex-shrink:0;">🟣</span>
                                                        @endif
                                                        <span>{{ $pair['right_text'] ?: 'Match' }}</span>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                        <div class="dnd-score-bar" id="m-dnd-score-{{ $qIndex }}">
                                            <span class="dnd-score-text">Matched: <span class="dnd-matched-count" id="m-dnd-count-{{ $qIndex }}">0</span> / {{ count($q['_ddPairs']) }}</span>
                                            <span style="font-size:11px;font-weight:600;color:#94a3b8;">✨ No mistakes = full XP</span>
                                        </div>
                                    </div>
                                @else
                                    <div style="text-align:center;padding:20px;background:#f8fafc;border-radius:14px;border:1.5px dashed #e2e8f0;">
                                        <p style="font-size:13px;color:#94a3b8;font-weight:600;margin:0;">No pairs configured yet.</p>
                                    </div>
                                @endif
                            @elseif($q['_type'] === 'gesture')
                                <div class="gesture-section">
                                    <div class="gesture-header">
                                        <span class="gesture-header-badge">GESTURE</span>
                                        <span class="gesture-header-text">Perform the gesture shown below</span>
                                    </div>
                                    <div class="gesture-cam-wrap">
                                        <div class="gesture-cam-corner tl"></div>
                                        <div class="gesture-cam-corner tr"></div>
                                        <div class="gesture-cam-corner bl"></div>
                                        <div class="gesture-cam-corner br"></div>
                                        <div class="gesture-scan-ring">
                                            <span class="gesture-hand-icon">✋</span>
                                        </div>
                                        <span class="gesture-cam-label">📷 Live camera · in-app only</span>
                                    </div>
                                    @if(count($q['_gestures']) > 0)
                                        <p class="gesture-targets-label">Perform these gestures:</p>
                                        <div class="gesture-chip-grid">
                                            @foreach($q['_gestures'] as $g)
                                                <div class="gesture-chip">
                                                    @if(!empty($g['image_url']))
                                                        @php $gi = formatImageUrl($g['image_url']); @endphp
                                                        @if($gi)
                                                            <img src="{{ $gi }}" class="gesture-chip-img" onerror="this.style.display='none'">
                                                        @else
                                                            <div class="gesture-chip-placeholder">✋</div>
                                                        @endif
                                                    @else
                                                        <div class="gesture-chip-placeholder">✋</div>
                                                    @endif
                                                    <span>{{ $g['name'] }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div style="text-align:center;padding:12px;background:#faf5ff;border-radius:12px;margin-bottom:10px;">
                                            <p style="font-size:12.5px;color:#7c3aed;font-weight:600;margin:0;">No gestures selected yet.</p>
                                        </div>
                                    @endif
                                    <div class="gesture-status-bar" id="m-gest-status-{{ $qIndex }}">
                                        <div class="gesture-status-dot" id="m-gest-dot-{{ $qIndex }}"></div>
                                        <span class="gesture-status-text" id="m-gest-text-{{ $qIndex }}">Waiting for gesture...</span>
                                    </div>
                                    <button type="button" class="gesture-confirm-btn mt-2" id="m-gesture-btn-{{ $qIndex }}" onclick="confirmGesture('m', {{ $qIndex }})" style="margin-top:8px;">
                                        <span>✋</span> Simulate Correct Gesture
                                    </button>
                                </div>
                            @else
                                @foreach($q['_opts'] as $optIndex => $option)
                                    <div class="option-card" onclick="selectOption('m', {{ $qIndex }}, {{ $optIndex }})" id="m-opt-{{ $qIndex }}-{{ $optIndex }}">
                                        <div class="option-circle">{{ chr(65+$optIndex) }}</div>
                                   @if(!empty($option['image']))
    @php 
        $optionImageUrl = formatImageUrl($option['image']);
        $optIsVideo = $isVideo($option['image']);
    @endphp
    @if($optionImageUrl)
        @if($optIsVideo)
            <video muted loop playsinline class="option-image-thumb" style="width:56px;height:56px;border-radius:10px;object-fit:cover;flex-shrink:0;background:#000;">
                <source src="{{ $optionImageUrl }}" type="video/mp4">
            </video>
        @else
            <img src="{{ $optionImageUrl }}" alt="" class="option-image-thumb" style="width:56px;height:56px;border-radius:10px;object-fit:cover;flex-shrink:0;" onerror="this.style.display='none'">
        @endif
    @endif
@endif
                                        <span style="font-size:14px; font-weight:600; color:#1F2937;">{{ $option['text'] }}</span>
                                    </div>
                                @endforeach
                            @endif

                            {{-- Feedback area (shown after answering) --}}
                            <div class="answer-feedback" id="m-feedback-{{ $qIndex }}" style="display:none;">
                                <div class="feedback-correct" id="m-feedback-correct-{{ $qIndex }}" style="display:none;">
                                    <div style="display:flex;align-items:center;gap:10px;padding:14px 16px;background:rgba(5,150,105,0.1);border:1.5px solid #059669;border-radius:14px;margin-bottom:10px;">
                                        <span style="font-size:20px;">✅</span>
                                        <div>
                                            <p style="font-size:13px;font-weight:800;color:#059669;margin:0;">Correct! Great job!</p>
                                            <p style="font-size:11px;color:#065F46;margin:0;">+10 XP earned</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="feedback-wrong" id="m-feedback-wrong-{{ $qIndex }}" style="display:none;">
                                    <div style="display:flex;align-items:center;gap:10px;padding:14px 16px;background:rgba(239,68,68,0.08);border:1.5px solid #EF4444;border-radius:14px;margin-bottom:10px;">
                                        <span style="font-size:20px;">❌</span>
                                        <div>
                                            <p style="font-size:13px;font-weight:800;color:#DC2626;margin:0;">Not quite right</p>
                                            <p style="font-size:11px;color:#7F1D1D;margin:0;" id="m-correct-answer-text-{{ $qIndex }}">
                                                @if($q['_type'] === 'drag_drop')
                                                    Some pairs were matched incorrectly. Review the connections above.
                                                @else
                                                    The correct answer is: <strong>{{ chr(65+$correctIdx) }}</strong>
                                                @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="senya-tip" id="m-senya-tip-{{ $qIndex }}">
                                <img src="{{ asset('images/senya_teaching.png') }}" alt="Senya">
                                <div class="feedback-bubble"><span style="font-size:12.5px; font-weight:500; color:#0f3172; line-height:1.5;">Read carefully and pick the best answer!</span></div>
                            </div>

                            <div class="quiz-nav">
                                <button class="ghost-btn" onclick="prevQuestion('m')" {{ $qIndex == 0 ? 'style=display:none;' : '' }}>← Back</button>
                                <button class="primary-btn" id="m-next-btn-{{ $qIndex }}"
                                        onclick="handleNextQuestion('m', {{ $qIndex }}, {{ $totalQuestions }})"
                                        style="opacity:0.4;pointer-events:none;{{ $qIndex == 0 ? 'flex:1;' : '' }}">
                                    {{ $qIndex == $totalQuestions-1 ? '🏁 Finish Quiz' : 'Next →' }}
                                </button>
                            </div>
                        </div>
                    @endforeach

                    {{-- Quiz Summary Screen --}}
                    <div id="m-quizSummary" style="display:none; padding:8px 0;">
                        <div class="glass-card" style="text-align:center; padding:28px 20px;">
                            <div id="m-summary-emoji" style="font-size:52px; margin-bottom:12px;">🏆</div>
                            <h3 style="font-size:20px; font-weight:900; color:#0f3172; margin:0 0 6px;">Quiz Complete!</h3>
                            <p style="font-size:13px; color:#64748b; margin:0 0 20px;">Here's how you did</p>
                            <div style="display:flex;justify-content:center;gap:16px;margin-bottom:20px;flex-wrap:wrap;">
                                <div style="background:rgba(24,72,200,0.07);border-radius:14px;padding:14px 20px;min-width:80px;">
                                    <p style="font-size:26px;font-weight:900;color:#1848c8;margin:0;" id="m-score-fraction">0/0</p>
                                    <p style="font-size:11px;color:#64748b;margin:0;font-weight:600;">Correct</p>
                                </div>
                                <div style="background:rgba(234,179,8,0.1);border-radius:14px;padding:14px 20px;min-width:80px;">
                                    <p style="font-size:26px;font-weight:900;color:#D97706;margin:0;" id="m-score-xp">0 XP</p>
                                    <p style="font-size:11px;color:#64748b;margin:0;font-weight:600;">Earned</p>
                                </div>
                                <div style="background:rgba(5,150,105,0.08);border-radius:14px;padding:14px 20px;min-width:80px;">
                                    <p style="font-size:26px;font-weight:900;color:#059669;margin:0;" id="m-score-pct">0%</p>
                                    <p style="font-size:11px;color:#64748b;margin:0;font-weight:600;">Score</p>
                                </div>
                            </div>
                            <div id="m-summary-message" style="font-size:13px;font-weight:600;color:#475569;margin-bottom:20px;padding:12px 16px;background:#F8FAFC;border-radius:12px;"></div>
                        </div>

                        {{-- Per-question review --}}
                        <div class="glass-card" style="padding:16px 20px;">
                            <p style="font-size:12px;font-weight:800;color:#0f3172;margin:0 0 12px;">Question Review</p>
                            <div id="m-question-review" style="display:flex;flex-direction:column;gap:8px;"></div>
                        </div>

                        <button onclick="retakeQuiz('m')"
                                style="width:100%;padding:14px;background:linear-gradient(135deg,#1848c8,#0f3172);color:white;border:none;border-radius:14px;font-weight:800;font-size:14px;cursor:pointer;margin-top:8px;">
                            🔁 Retake Quiz
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- ============ WEB FRAME ============ -->
    <div class="browser-mockup" id="webFrame">
        <div class="browser-topbar">
            <div class="browser-dot" style="background:#FF5F57;"></div>
            <div class="browser-dot" style="background:#FEBC2E;"></div>
            <div class="browser-dot" style="background:#28C840;"></div>
            <div class="browser-urlbar">senas.app/lessons/{{ \Illuminate\Support\Str::slug($lessonData['title'] ?? 'preview') }}</div>
        </div>
        <div class="web-body">
            <div class="web-sidebar">
                <div class="logo">SEÑAS</div>
                <div class="nav-item current">📖 Lessons</div>
            </div>
            <div class="web-main">
                <!-- Lesson panel (web) -->
                <div class="content-panel {{ $totalSlides > 0 ? 'active' : '' }}" id="w-lessonPanel">
                    @if($totalSlides > 0)
                        <div class="web-grid-2col">
                            <div class="web-content-col">
                                <div class="glass-card">
                                    <div style="display:flex; align-items:center; gap:12px;">
                                        <img src="{{ asset('images/wavingSenya.png') }}" alt="Senya" class="hero-image">
                                        <div>
                                            <span class="badge" id="w-moduleBadge">MODULE</span>
                                            <h2 style="font-size:24px; font-weight:800; color:#0f3172; margin:4px 0;">{{ $lessonData['title'] }}</h2>
                                            <p style="font-size:13px; color:#4b7bbb; font-weight:500;">{{ $totalSlides }} slides · ~2 min read</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="lesson-progress-wrap">
                                    <div class="lesson-progress-track"><div class="lesson-progress-fill" id="w-progressFill" style="width:{{ round(100/$totalSlides) }}%; background:{{ $colors[0] }};"></div></div>
                                    <div class="lesson-progress-meta">
                                        <span class="slide-pill" id="w-slidePill" style="background:{{ $colors[0] }}1A; color:{{ $colors[0] }};">SLIDE 1 / {{ $totalSlides }}</span>
                                        <div style="display:flex; gap:6px;" id="w-slideDots">
                                            @foreach($lessonData['contents'] as $index => $content)
                                                <div class="slide-dot {{ $index == 0 ? 'active' : 'inactive' }}" onclick="setSlide('w', {{ $index }})" style="cursor:pointer; {{ $index == 0 ? 'background:'.$colors[0].';' : '' }}"></div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                                <div id="w-slideContainer">
                                    @foreach($lessonData['contents'] as $index => $current)
                                        @php $slideColor = $colors[$index % count($colors)]; @endphp
                                        <div class="slide-content" id="w-slide-{{ $index }}" style="{{ $index == 0 ? '' : 'display:none;' }}">
                                            <div class="glass-card">
                                                <div class="slide-accent" style="background:{{ $slideColor }};"></div>
                                                <h3 style="font-size:19px; font-weight:800; color:{{ $slideColor }}; margin-bottom:10px;">{{ $current['title'] ?? 'Slide Title' }}</h3>
                                               @if(!empty($current['media']) && is_string($current['media']))
    @php
        $mediaUrl = formatImageUrl($current['media']);
        $isVideoFile = $isVideo($current['media'], $current['content_type'] ?? null);
    @endphp
    @if($mediaUrl)
        <div class="media-wrap" style="background:#0f172a;border-radius:14px;overflow:hidden;">
            @if($isVideoFile)
                <video controls autoplay muted loop playsinline class="slide-video" style="width:100%;display:block;max-height:220px;background:#000;">
                    <source src="{{ $mediaUrl }}" type="video/mp4">
                    Your browser does not support video playback.
                </video>
            @else
                <img src="{{ $mediaUrl }}" alt="Slide image" class="slide-image" style="width:100%;display:block;max-height:220px;object-fit:cover;" onerror="this.parentElement.style.display='none'">
            @endif
        </div>
    @endif
@endif
                                                <p style="font-size:14.5px; color:#334155; line-height:1.7;">{{ $current['content_text'] ?? 'Content goes here...' }}</p>
                                            </div>
                                            <div class="slide-nav">
                                                <button class="ghost-btn" onclick="prevSlide('w')" style="{{ $index == 0 ? 'display:none;' : '' }}">← Back</button>
                                                <button class="primary-btn {{ $index == $totalSlides-1 ? 'gold-btn' : '' }}" style="{{ $index == 0 ? 'flex:1;' : '' }} background:{{ $index == $totalSlides-1 ? '' : $slideColor }};" onclick="nextSlide('w')">
                                                    {{ $index == $totalSlides-1 ? 'Start Quiz' : 'Next →' }}
                                                </button>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="web-side-col">
                                <div class="glass-card">
                                    <div class="senya-tip" style="margin-bottom:0;">
                                        <img src="{{ asset('images/senya_teaching.png') }}" alt="Senya Teaching">
                                        <div class="tip-bubble">Hi! I'm Senya. Let's learn about FSL before your quiz!</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div style="text-align:center; padding:60px 20px; color:#6B7280;">
                            <p style="font-size:16px;">No content added yet.</p>
                            <p style="font-size:14px;">Add slides in the lesson creation form.</p>
                        </div>
                    @endif
                </div>

                <!-- Quiz panel (web) -->
                <div class="content-panel {{ $totalSlides == 0 && $totalQuestions > 0 ? 'active' : '' }}" id="w-quizPanel">
                    @if($totalQuestions > 0)
                        <div class="web-grid-2col">
                            <div class="web-content-col">
                                {{-- Progress header --}}
                                <div class="glass-card" id="w-quizHeader">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                        <span style="font-size:13px; font-weight:700; color:#0f3172;" id="w-quizLabel">Question 1 of {{ $totalQuestions }}</span>
                                        <span class="badge badge-yellow">⚡ 10 XP each</span>
                                    </div>
                                    <div class="progress-dots" id="w-quizDots">
                                        @foreach($normalizedQuiz as $index => $quizItem)
                                            <div class="progress-dot {{ $index == 0 ? 'active' : 'pending' }}"></div>
                                        @endforeach
                                    </div>
                                </div>

                                @foreach($normalizedQuiz as $qIndex => $q)
                                    @php $correctIdx = (int)($q['correct'] ?? 0); @endphp
                                    <div class="quiz-question-panel {{ $qIndex == 0 ? 'active' : '' }}" id="w-q-{{ $qIndex }}" data-correct="{{ $correctIdx }}">
                                        <div class="glass-card" style="text-align:center; padding:28px;">
                                            @if(!empty($q['_media']))
                                                @php $imageUrl = formatImageUrl($q['_media']); @endphp
                                                @if($imageUrl)
                                                    <div class="quiz-media-wrap" style="max-width:360px;">
                                                        <img src="{{ $imageUrl }}" class="quiz-media"
                                                             onerror="this.parentElement.innerHTML='<div style=\'padding:20px;text-align:center;color:#999;font-size:13px;\'>⚠️ Image not available</div>'">
                                                    </div>
                                                @endif
                                            @endif
                                            <p style="font-size:18px; font-weight:800; color:#0f3172; margin-top:10px;">{{ $q['question'] ?? 'Sample Question' }}</p>
                                        </div>

                                        @if($q['_type'] === 'drag_drop')
                                            @if(count($q['_ddPairs']) > 0)
                                                <div class="dnd-section">
                                                    <div class="dnd-header">
                                                        <span class="dnd-header-badge">MATCH</span>
                                                        <span class="dnd-hint">Click a card on the left, then click its match on the right</span>
                                                    </div>
                                                    <div class="dnd-columns-label">
                                                        <span class="dnd-col-label">Items</span>
                                                        <span class="dnd-col-label">Matches</span>
                                                    </div>
                                                    <div class="dragdrop-wrap" id="w-dd-{{ $qIndex }}" data-total="{{ count($q['_ddPairs']) }}">
                                                        <div class="dragdrop-col">
                                                            @foreach($q['_ddPairs'] as $pIndex => $pair)
                                                                <div class="dragdrop-item dd-left" data-match="{{ $pair['match_id'] }}" onclick="selectDragItem('w', {{ $qIndex }}, 'left', '{{ $pair['match_id'] }}', this)">
                                                                  @if(!empty($pair['left_image']))
    @php 
        $li = formatImageUrl($pair['left_image']);
        $liIsVideo = $isVideo($pair['left_image']);
    @endphp
    @if($li)
        @if($liIsVideo)
            <video muted loop playsinline class="dd-thumb" style="width:34px;height:34px;border-radius:8px;object-fit:cover;flex-shrink:0;background:#000;border:1px solid rgba(15,49,114,0.1);">
                <source src="{{ $li }}" type="video/mp4">
            </video>
        @else
            <img src="{{ $li }}" class="dd-thumb" style="width:34px;height:34px;border-radius:8px;object-fit:cover;flex-shrink:0;border:1px solid rgba(15,49,114,0.1);" onerror="this.style.display='none'">
        @endif
    @endif
@else
    <span style="font-size:18px;flex-shrink:0;">🔵</span>
@endif
                                                                    <span>{{ $pair['left_text'] ?: 'Item '.($pIndex+1) }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div style="display:flex;flex-direction:column;justify-content:space-around;width:16px;padding:0;">
                                                            @foreach($q['_ddPairs'] as $p)
                                                                <div style="flex:1;display:flex;align-items:center;justify-content:center;">
                                                                    <div style="width:1.5px;height:100%;background:linear-gradient(to bottom,#cbd5e1,#e2e8f0);min-height:20px;"></div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <div class="dragdrop-col">
                                                            @foreach($q['_ddRightShuffled'] as $pair)
                                                                <div class="dragdrop-item dd-right" data-match="{{ $pair['match_id'] }}" onclick="selectDragItem('w', {{ $qIndex }}, 'right', '{{ $pair['match_id'] }}', this)">
                                                                    @if(!empty($pair['right_image']))
                                                                        @php $ri = formatImageUrl($pair['right_image']); @endphp
                                                                        @if($ri)<img src="{{ $ri }}" class="dd-thumb" onerror="this.style.display='none'">@endif
                                                                    @else
                                                                        <span style="font-size:18px;flex-shrink:0;">🟣</span>
                                                                    @endif
                                                                    <span>{{ $pair['right_text'] ?: 'Match' }}</span>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                    <div class="dnd-score-bar" id="w-dnd-score-{{ $qIndex }}">
                                                        <span class="dnd-score-text">Matched: <span class="dnd-matched-count" id="w-dnd-count-{{ $qIndex }}">0</span> / {{ count($q['_ddPairs']) }}</span>
                                                        <span style="font-size:11px;font-weight:600;color:#94a3b8;">✨ No mistakes = full XP</span>
                                                    </div>
                                                </div>
                                            @else
                                                <div style="text-align:center;padding:24px;background:#f8fafc;border-radius:14px;border:1.5px dashed #e2e8f0;">
                                                    <p style="font-size:13px;color:#94a3b8;font-weight:600;margin:0;">No pairs configured yet.</p>
                                                </div>
                                            @endif
                                        @elseif($q['_type'] === 'gesture')
                                            <div class="gesture-section">
                                                <div class="gesture-header">
                                                    <span class="gesture-header-badge">GESTURE</span>
                                                    <span class="gesture-header-text">Perform the gesture shown below</span>
                                                </div>
                                                <div class="gesture-cam-wrap" style="max-height:200px;">
                                                    <div class="gesture-cam-corner tl"></div>
                                                    <div class="gesture-cam-corner tr"></div>
                                                    <div class="gesture-cam-corner bl"></div>
                                                    <div class="gesture-cam-corner br"></div>
                                                    <div class="gesture-scan-ring">
                                                        <span class="gesture-hand-icon">✋</span>
                                                    </div>
                                                    <span class="gesture-cam-label">📷 Live camera · in-app only</span>
                                                </div>
                                                @if(count($q['_gestures']) > 0)
                                                    <p class="gesture-targets-label" style="margin-top:10px;">Perform these gestures:</p>
                                                    <div class="gesture-chip-grid">
                                                        @foreach($q['_gestures'] as $g)
                                                            <div class="gesture-chip">
                                                                @if(!empty($g['image_url']))
                                                                    @php $gi = formatImageUrl($g['image_url']); @endphp
                                                                    @if($gi)
                                                                        <img src="{{ $gi }}" class="gesture-chip-img" onerror="this.style.display='none'">
                                                                    @else
                                                                        <div class="gesture-chip-placeholder">✋</div>
                                                                    @endif
                                                                @else
                                                                    <div class="gesture-chip-placeholder">✋</div>
                                                                @endif
                                                                <span>{{ $g['name'] }}</span>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <div style="text-align:center;padding:12px;background:#faf5ff;border-radius:12px;margin:10px 0;">
                                                        <p style="font-size:13px;color:#7c3aed;font-weight:600;margin:0;">No gestures selected yet.</p>
                                                    </div>
                                                @endif
                                                <div class="gesture-status-bar" id="w-gest-status-{{ $qIndex }}">
                                                    <div class="gesture-status-dot" id="w-gest-dot-{{ $qIndex }}"></div>
                                                    <span class="gesture-status-text" id="w-gest-text-{{ $qIndex }}">Waiting for gesture...</span>
                                                </div>
                                                <button type="button" class="gesture-confirm-btn" id="w-gesture-btn-{{ $qIndex }}" onclick="confirmGesture('w', {{ $qIndex }})" style="margin-top:8px;">
                                                    <span>✋</span> Simulate Correct Gesture
                                                </button>
                                            </div>
                                        @else
                                            @foreach($q['_opts'] as $optIndex => $option)
                                                <div class="option-card" onclick="selectOption('w', {{ $qIndex }}, {{ $optIndex }})" id="w-opt-{{ $qIndex }}-{{ $optIndex }}">
                                                    <div class="option-circle">{{ chr(65+$optIndex) }}</div>
                                                    @if(!empty($option['image']))
                                                        @php $optionImageUrl = formatImageUrl($option['image']); @endphp
                                                        @if($optionImageUrl)
                                                            <img src="{{ $optionImageUrl }}" alt="" class="option-image-thumb" onerror="this.style.display='none'">
                                                        @endif
                                                    @endif
                                                    <span style="font-size:14.5px; font-weight:600; color:#1F2937;">{{ $option['text'] }}</span>
                                                </div>
                                            @endforeach
                                        @endif

                                        {{-- Feedback area --}}
                                        <div class="answer-feedback" id="w-feedback-{{ $qIndex }}" style="display:none;">
                                            <div id="w-feedback-correct-{{ $qIndex }}" style="display:none;">
                                                <div style="display:flex;align-items:center;gap:12px;padding:16px 20px;background:rgba(5,150,105,0.1);border:1.5px solid #059669;border-radius:14px;margin-bottom:12px;">
                                                    <span style="font-size:22px;">✅</span>
                                                    <div>
                                                        <p style="font-size:14px;font-weight:800;color:#059669;margin:0;">Correct! Great job!</p>
                                                        <p style="font-size:12px;color:#065F46;margin:0;">+10 XP earned</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div id="w-feedback-wrong-{{ $qIndex }}" style="display:none;">
                                                <div style="display:flex;align-items:center;gap:12px;padding:16px 20px;background:rgba(239,68,68,0.08);border:1.5px solid #EF4444;border-radius:14px;margin-bottom:12px;">
                                                    <span style="font-size:22px;">❌</span>
                                                    <div>
                                                        <p style="font-size:14px;font-weight:800;color:#DC2626;margin:0;">Not quite right</p>
                                                        <p style="font-size:12px;color:#7F1D1D;margin:0;" id="w-correct-answer-text-{{ $qIndex }}">
                                                            @if($q['_type'] === 'drag_drop')
                                                                Some pairs were matched incorrectly. Review the connections above.
                                                            @else
                                                                The correct answer is: <strong>{{ chr(65+$correctIdx) }}</strong>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="quiz-nav">
                                            <button class="ghost-btn" onclick="prevQuestion('w')" {{ $qIndex == 0 ? 'style=display:none;' : '' }}>← Back</button>
                                            <button class="primary-btn" id="w-next-btn-{{ $qIndex }}"
                                                    onclick="handleNextQuestion('w', {{ $qIndex }}, {{ $totalQuestions }})"
                                                    style="opacity:0.4;pointer-events:none;{{ $qIndex == 0 ? 'flex:1;' : '' }}">
                                                {{ $qIndex == $totalQuestions-1 ? '🏁 Finish Quiz' : 'Next →' }}
                                            </button>
                                        </div>
                                    </div>
                                @endforeach

                                {{-- Web Summary Screen --}}
                                <div id="w-quizSummary" style="display:none;">
                                    <div class="glass-card" style="text-align:center; padding:36px 28px;">
                                        <div id="w-summary-emoji" style="font-size:60px; margin-bottom:14px;">🏆</div>
                                        <h3 style="font-size:24px; font-weight:900; color:#0f3172; margin:0 0 6px;">Quiz Complete!</h3>
                                        <p style="font-size:14px; color:#64748b; margin:0 0 24px;">Here's how you did</p>
                                        <div style="display:flex;justify-content:center;gap:20px;margin-bottom:24px;flex-wrap:wrap;">
                                            <div style="background:rgba(24,72,200,0.07);border-radius:16px;padding:18px 28px;min-width:100px;">
                                                <p style="font-size:30px;font-weight:900;color:#1848c8;margin:0;" id="w-score-fraction">0/0</p>
                                                <p style="font-size:12px;color:#64748b;margin:0;font-weight:600;">Correct</p>
                                            </div>
                                            <div style="background:rgba(234,179,8,0.1);border-radius:16px;padding:18px 28px;min-width:100px;">
                                                <p style="font-size:30px;font-weight:900;color:#D97706;margin:0;" id="w-score-xp">0 XP</p>
                                                <p style="font-size:12px;color:#64748b;margin:0;font-weight:600;">Earned</p>
                                            </div>
                                            <div style="background:rgba(5,150,105,0.08);border-radius:16px;padding:18px 28px;min-width:100px;">
                                                <p style="font-size:30px;font-weight:900;color:#059669;margin:0;" id="w-score-pct">0%</p>
                                                <p style="font-size:12px;color:#64748b;margin:0;font-weight:600;">Score</p>
                                            </div>
                                        </div>
                                        <div id="w-summary-message" style="font-size:14px;font-weight:600;color:#475569;margin-bottom:24px;padding:14px 20px;background:#F8FAFC;border-radius:14px;"></div>
                                        <div id="w-question-review" style="text-align:left;margin-bottom:20px;display:flex;flex-direction:column;gap:8px;"></div>
                                        <button onclick="retakeQuiz('w')"
                                                style="padding:14px 32px;background:linear-gradient(135deg,#1848c8,#0f3172);color:white;border:none;border-radius:14px;font-weight:800;font-size:15px;cursor:pointer;">
                                            🔁 Retake Quiz
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="web-side-col" id="w-senyaCol">
                                <div class="glass-card">
                                    <div class="senya-tip" style="margin-bottom:0;">
                                        <img src="{{ asset('images/senya_teaching.png') }}" alt="Senya Teaching">
                                        <div class="feedback-bubble" id="w-senyaTip">
                                            <span style="font-size:13px; font-weight:500; color:#0f3172; line-height:1.6;">Read carefully and pick the best answer!</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

<script>
(function () {
const totalSlides = {{ $totalSlides }};
const totalQuestions = {{ $totalQuestions }};
const slideColors = @json($colors);
let slideState = { m: 0, w: 0 };
let questionState = { m: 0, w: 0 };
let quizAnswers = { m: {}, w: {} };
let quizScore   = { m: 0, w: 0 };
let ddState      = { m: {}, w: {} }; // per question: { selectedLeft, selectedRight, matched: Set, mistakes }
let answeredSet  = { m: {}, w: {} }; // per question: true once finished (prevents double-scoring)

function colorFor(i) { return slideColors[i % slideColors.length]; }

// Shared "question finished" tail — used by MC/TF option selection, drag-drop matching,
// and gesture confirmation so scoring, feedback, dots, and the Next button stay in sync.
function finishQuestion(prefix, qIndex, isCorrect) {
    if (answeredSet[prefix][qIndex]) return;
    answeredSet[prefix][qIndex] = true;

    quizAnswers[prefix][qIndex] = { isCorrect };
    if (isCorrect) quizScore[prefix]++;

    const feedback = document.getElementById(prefix + '-feedback-' + qIndex);
    if (feedback) feedback.style.display = 'block';
    const correctBanner = document.getElementById(prefix + '-feedback-correct-' + qIndex);
    const wrongBanner = document.getElementById(prefix + '-feedback-wrong-' + qIndex);
    if (isCorrect) { if (correctBanner) correctBanner.style.display = 'block'; }
    else { if (wrongBanner) wrongBanner.style.display = 'block'; }

    const senyaTip = document.getElementById('w-senyaTip');
    if (senyaTip && prefix === 'w') {
        senyaTip.innerHTML = isCorrect
            ? '<span style="font-size:13px;font-weight:700;color:#059669;line-height:1.6;">✅ That\'s correct! Keep it up!</span>'
            : '<span style="font-size:13px;font-weight:700;color:#DC2626;line-height:1.6;">❌ Not quite! Review the highlighted answer above.</span>';
    }

    const nextBtn = document.getElementById(prefix + '-next-btn-' + qIndex);
    if (nextBtn) {
        nextBtn.style.opacity = '1';
        nextBtn.style.pointerEvents = 'auto';
        if (qIndex === totalQuestions - 1) nextBtn.classList.add('gold-btn');
    }

    document.querySelectorAll('#' + prefix + '-quizDots .progress-dot').forEach((dot, i) => {
        if (i === qIndex) dot.style.background = isCorrect ? '#059669' : '#EF4444';
    });
}

window.selectDragItem = function(prefix, qIndex, side, matchId, el) {
    if (answeredSet[prefix][qIndex]) return;
    if (!ddState[prefix][qIndex]) ddState[prefix][qIndex] = { selectedLeft: null, selectedRight: null, matched: new Set(), mistakes: 0 };
    const state = ddState[prefix][qIndex];

    if (el.classList.contains('dd-matched')) return;

    const wrap = document.getElementById(prefix + '-dd-' + qIndex);
    const total = parseInt(wrap?.dataset.total ?? '0');

    if (side === 'left') {
        wrap.querySelectorAll('.dd-left').forEach(c => c.classList.remove('dd-selected'));
        el.classList.add('dd-selected');
        state.selectedLeft = { matchId, el };
    } else {
        wrap.querySelectorAll('.dd-right').forEach(c => c.classList.remove('dd-selected'));
        el.classList.add('dd-selected');
        state.selectedRight = { matchId, el };
    }

    if (state.selectedLeft && state.selectedRight) {
        const isMatch = String(state.selectedLeft.matchId) === String(state.selectedRight.matchId);
        if (isMatch) {
            state.selectedLeft.el.classList.remove('dd-selected');
            state.selectedRight.el.classList.remove('dd-selected');
            state.selectedLeft.el.classList.add('dd-matched');
            state.selectedRight.el.classList.add('dd-matched');
            state.matched.add(String(state.selectedLeft.matchId));
            state.selectedLeft = null;
            state.selectedRight = null;

            // Update matched count display
            const countEl = document.getElementById(prefix + '-dnd-count-' + qIndex);
            if (countEl) countEl.textContent = state.matched.size;

            if (state.matched.size >= total) {
                wrap.classList.add('answered');
                finishQuestion(prefix, qIndex, state.mistakes === 0);
                // Update score bar to show completion
                const scoreBar = document.getElementById(prefix + '-dnd-score-' + qIndex);
                if (scoreBar) {
                    const isPerf = state.mistakes === 0;
                    scoreBar.style.background = isPerf ? 'rgba(5,150,105,0.1)' : 'rgba(239,68,68,0.08)';
                    scoreBar.innerHTML = isPerf
                        ? '<span style="font-size:12px;font-weight:800;color:#059669;">✅ Perfect! All pairs matched correctly!</span>'
                        : '<span style="font-size:12px;font-weight:800;color:#EF4444;">❌ Completed with ' + state.mistakes + ' mistake(s)</span>';
                }
            }
        } else {
            state.mistakes++;
            const leftEl = state.selectedLeft.el, rightEl = state.selectedRight.el;
            leftEl.classList.add('dd-wrong');
            rightEl.classList.add('dd-wrong');
            setTimeout(() => {
                leftEl.classList.remove('dd-wrong', 'dd-selected');
                rightEl.classList.remove('dd-wrong', 'dd-selected');
            }, 350);
            state.selectedLeft = null;
            state.selectedRight = null;
        }
    }
};

window.confirmGesture = function(prefix, qIndex) {
    if (answeredSet[prefix][qIndex]) return;
    const btn = document.getElementById(prefix + '-gesture-btn-' + qIndex);
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span>✅</span> Gesture Recognized!';
        btn.classList.add('recognized');
    }
    // Update status bar
    const dot = document.getElementById(prefix + '-gest-dot-' + qIndex);
    const text = document.getElementById(prefix + '-gest-text-' + qIndex);
    if (dot) { dot.classList.add('ready'); dot.style.animation = 'none'; }
    if (text) { text.textContent = '✅ Gesture detected successfully!'; text.style.color = '#059669'; }
    const statusBar = document.getElementById(prefix + '-gest-status-' + qIndex);
    if (statusBar) statusBar.style.background = 'rgba(5,150,105,0.08)';
    finishQuestion(prefix, qIndex, true);
};

window.setDevice = function(device) {
    document.querySelectorAll('#deviceToggle .toggle-btn').forEach(b => b.classList.toggle('active', b.dataset.device === device));
    document.getElementById('mobileFrame').style.display = device === 'mobile' ? 'flex' : 'none';
    document.getElementById('webFrame').style.display = device === 'web' ? 'flex' : 'none';
};

window.setContentMode = function(mode) {
    document.querySelectorAll('#contentToggle .toggle-btn').forEach(b => b.classList.toggle('active', b.dataset.content === mode));
    ['m','w'].forEach(prefix => {
        const lessonEl = document.getElementById(prefix + '-lessonPanel');
        const quizEl = document.getElementById(prefix + '-quizPanel');
        if (lessonEl) lessonEl.classList.toggle('active', mode === 'lesson');
        if (quizEl) quizEl.classList.toggle('active', mode === 'quiz');
    });
};

window.setSlide = function(prefix, index) {
    if (index < 0 || index >= totalSlides) return;
    slideState[prefix] = index;
    const color = colorFor(index);

    document.querySelectorAll('#' + prefix + '-slideContainer .slide-content').forEach(el => el.style.display = 'none');
    const target = document.getElementById(prefix + '-slide-' + index);
    if (target) target.style.display = prefix === 'm' ? 'flex' : 'block';
    if (target && prefix === 'm') target.style.flexDirection = 'column';

    document.querySelectorAll('#' + prefix + '-slideDots .slide-dot').forEach((dot, i) => {
        dot.className = i === index ? 'slide-dot active' : 'slide-dot inactive';
        dot.style.background = i === index ? colorFor(i) : '';
    });

    const fill = document.getElementById(prefix + '-progressFill');
    if (fill) { fill.style.width = Math.round(((index+1)/totalSlides)*100) + '%'; fill.style.background = color; }

    const pill = document.getElementById(prefix + '-slidePill');
    if (pill) { pill.textContent = 'SLIDE ' + (index+1) + ' / ' + totalSlides; pill.style.color = color; pill.style.background = color + '1A'; }

    const badge = document.getElementById(prefix + '-moduleBadge');
    if (badge) { badge.style.color = color; badge.style.background = color + '14'; }
};

window.nextSlide = function(prefix) {
    if (slideState[prefix] < totalSlides - 1) setSlide(prefix, slideState[prefix] + 1);
    else window.setContentMode('quiz');
};
window.prevSlide = function(prefix) {
    if (slideState[prefix] > 0) setSlide(prefix, slideState[prefix] - 1);
};

window.setQuestion = function(prefix, index) {
    if (index < 0 || index >= totalQuestions) return;
    questionState[prefix] = index;

    document.querySelectorAll('#' + prefix + '-quizPanel .quiz-question-panel').forEach(p => p.classList.remove('active'));
    const panel = document.getElementById(prefix + '-q-' + index);
    if (panel) panel.classList.add('active');

    document.querySelectorAll('#' + prefix + '-quizDots .progress-dot').forEach((dot, i) => {
        dot.className = 'progress-dot ' + (i === index ? 'active' : (i < index ? 'completed' : 'pending'));
    });

    const label = document.getElementById(prefix + '-quizLabel');
    if (label) label.textContent = 'Question ' + (index+1) + ' of ' + totalQuestions;
};

window.nextQuestion = function(prefix) {
    if (questionState[prefix] < totalQuestions - 1) setQuestion(prefix, questionState[prefix] + 1);
};
window.prevQuestion = function(prefix) {
    if (questionState[prefix] > 0) setQuestion(prefix, questionState[prefix] - 1);
};

window.selectOption = function(prefix, qIndex, optIndex) {
    const panel = document.getElementById(prefix + '-q-' + qIndex);
    if (!panel) return;

    // If already answered, don't allow re-selection
    if (panel.dataset.answered === '1') return;
    panel.dataset.answered = '1';

    const correctIdx = parseInt(panel.dataset.correct ?? '0');
    const isCorrect = (optIndex === correctIdx);

    // Highlight all options
    document.querySelectorAll('#' + prefix + '-q-' + qIndex + ' .option-card').forEach((card, i) => {
        card.style.pointerEvents = 'none';
        card.style.opacity = '0.75';
        if (i === correctIdx) {
            card.style.background = 'rgba(5,150,105,0.12)';
            card.style.border = '2px solid #059669';
            card.querySelector('.option-circle').style.background = '#059669';
            card.querySelector('.option-circle').style.color = 'white';
            card.style.opacity = '1';
        }
        if (i === optIndex && !isCorrect) {
            card.style.background = 'rgba(239,68,68,0.08)';
            card.style.border = '2px solid #EF4444';
            card.querySelector('.option-circle').style.background = '#EF4444';
            card.querySelector('.option-circle').style.color = 'white';
            card.style.opacity = '1';
        }
    });

    finishQuestion(prefix, qIndex, isCorrect);
};

window.handleNextQuestion = function(prefix, qIndex, total) {
    if (qIndex < total - 1) {
        setQuestion(prefix, qIndex + 1);
        // Reset Senya tip for web
        const senyaTip = document.getElementById('w-senyaTip');
        if (senyaTip && prefix === 'w') {
            senyaTip.innerHTML = '<span style="font-size:13px;font-weight:500;color:#0f3172;line-height:1.6;">Read carefully and pick the best answer!</span>';
        }
    } else {
        showQuizSummary(prefix, total);
    }
};

window.prevQuestion = function(prefix) {
    if (questionState[prefix] > 0) setQuestion(prefix, questionState[prefix] - 1);
};

window.showQuizSummary = function(prefix, total) {
    // Hide header and all question panels
    const header = document.getElementById(prefix + '-quizHeader');
    if (header) header.style.display = 'none';
    document.querySelectorAll('#' + prefix + '-quizPanel .quiz-question-panel').forEach(p => p.classList.remove('active'));
    if (prefix === 'w') {
        const senyaCol = document.getElementById('w-senyaCol');
        if (senyaCol) senyaCol.style.display = 'none';
    }

    const score = quizScore[prefix];
    const pct = Math.round((score / total) * 100);
    const xp = score * 10;

    // Pick emoji + message based on score
    let emoji, message;
    if (pct === 100)      { emoji = '🏆'; message = 'Perfect score! Outstanding work!'; }
    else if (pct >= 80)   { emoji = '🌟'; message = 'Great job! You really know your FSL!'; }
    else if (pct >= 60)   { emoji = '👍'; message = 'Good effort! Review the missed questions and try again.'; }
    else if (pct >= 40)   { emoji = '📚'; message = 'Keep studying! You\'re making progress.'; }
    else                   { emoji = '💪'; message = 'Don\'t give up! Practice makes perfect.'; }

    document.getElementById(prefix + '-summary-emoji').textContent = emoji;
    document.getElementById(prefix + '-score-fraction').textContent = score + '/' + total;
    document.getElementById(prefix + '-score-xp').textContent = xp + ' XP';
    document.getElementById(prefix + '-score-pct').textContent = pct + '%';
    document.getElementById(prefix + '-summary-message').textContent = message;

    // Build per-question review
    const reviewEl = document.getElementById(prefix + '-question-review');
    if (reviewEl) {
        reviewEl.innerHTML = '';
        const answers = quizAnswers[prefix];
        for (let i = 0; i < total; i++) {
            const ans = answers[i];
            if (!ans) continue;
            const row = document.createElement('div');
            row.style.cssText = 'display:flex;align-items:center;gap:10px;padding:10px 14px;border-radius:11px;background:' + (ans.isCorrect ? 'rgba(5,150,105,0.08)' : 'rgba(239,68,68,0.06)') + ';';
            row.innerHTML = '<span style="font-size:16px;flex-shrink:0;">' + (ans.isCorrect ? '✅' : '❌') + '</span>'
                + '<span style="font-size:12px;font-weight:700;color:#374151;">Q' + (i+1) + '</span>'
                + '<span style="font-size:12px;color:#6B7280;flex:1;">' + (ans.isCorrect ? 'Answered correctly' : 'Review this question') + '</span>';
            reviewEl.appendChild(row);
        }
    }

    // Show summary
    const summary = document.getElementById(prefix + '-quizSummary');
    if (summary) summary.style.display = 'block';

    // Update label
    const label = document.getElementById(prefix + '-quizLabel');
    if (label) label.textContent = 'Results';
};

window.retakeQuiz = function(prefix) {
    quizScore[prefix] = 0;
    quizAnswers[prefix] = {};
    ddState[prefix] = {};
    answeredSet[prefix] = {};

    const header = document.getElementById(prefix + '-quizHeader');
    if (header) header.style.display = 'block';

    const summary = document.getElementById(prefix + '-quizSummary');
    if (summary) summary.style.display = 'none';

    if (prefix === 'w') {
        const senyaCol = document.getElementById('w-senyaCol');
        if (senyaCol) senyaCol.style.display = '';
        const senyaTip = document.getElementById('w-senyaTip');
        if (senyaTip) senyaTip.innerHTML = '<span style="font-size:13px;font-weight:500;color:#0f3172;line-height:1.6;">Read carefully and pick the best answer!</span>';
    }

    // Reset all question panels
    for (let i = 0; i < totalQuestions; i++) {
        const panel = document.getElementById(prefix + '-q-' + i);
        if (panel) {
            panel.dataset.answered = '0';
            panel.querySelectorAll('.option-card').forEach(card => {
                card.style.background = '';
                card.style.border = '';
                card.style.opacity = '';
                card.style.pointerEvents = '';
                const circle = card.querySelector('.option-circle');
                if (circle) { circle.style.background = ''; circle.style.color = ''; }
            });
            // Hide feedback
            const fb = document.getElementById(prefix + '-feedback-' + i);
            if (fb) fb.style.display = 'none';
            const fbC = document.getElementById(prefix + '-feedback-correct-' + i);
            if (fbC) fbC.style.display = 'none';
            const fbW = document.getElementById(prefix + '-feedback-wrong-' + i);
            if (fbW) fbW.style.display = 'none';
            // Disable next button
            const nextBtn = document.getElementById(prefix + '-next-btn-' + i);
            if (nextBtn) {
                nextBtn.style.opacity = '0.4';
                nextBtn.style.pointerEvents = 'none';
                nextBtn.classList.remove('gold-btn');
            }
            // Reset drag-drop matching UI
            const ddWrap = document.getElementById(prefix + '-dd-' + i);
            if (ddWrap) {
                ddWrap.classList.remove('answered');
                ddWrap.querySelectorAll('.dragdrop-item').forEach(item => {
                    item.classList.remove('dd-selected', 'dd-matched', 'dd-wrong');
                });
            }
            // Reset gesture confirm button
            const gestureBtn = document.getElementById(prefix + '-gesture-btn-' + i);
            if (gestureBtn) {
                gestureBtn.disabled = false;
                gestureBtn.textContent = '✋ Simulate correct gesture';
            }
        }
    }

    // Reset dots
    document.querySelectorAll('#' + prefix + '-quizDots .progress-dot').forEach((dot, i) => {
        dot.style.background = '';
        dot.className = 'progress-dot ' + (i === 0 ? 'active' : 'pending');
    });

    setQuestion(prefix, 0);
};

if (totalSlides > 0) { setSlide('m', 0); setSlide('w', 0); }
if (totalQuestions > 0) { setQuestion('m', 0); setQuestion('w', 0); }
if (totalSlides === 0 && totalQuestions > 0) { setContentMode('quiz'); }
})();
</script>