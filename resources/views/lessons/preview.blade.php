<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

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
        if ($type === 'true_false') {
            $opts = [['text' => 'True', 'image' => null], ['text' => 'False', 'image' => null]];
        } else {
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
        return $q;
    });
@endphp

<!-- ============ TOP CONTROLS: Mobile/Web + Lesson/Quiz ============ -->
<div class="preview-controls">
    <div class="toggle-group" id="deviceToggle">
        <button class="toggle-btn active" data-device="mobile" onclick="setDevice('mobile')">📱 Mobile</button>
        <button class="toggle-btn" data-device="web" onclick="setDevice('web')">🖥️ Web</button>
    </div>
    @if($totalQuestions > 0)
    <div class="toggle-group" id="contentToggle">
        <button class="toggle-btn active" data-content="lesson" onclick="setContentMode('lesson')">📖 Lesson Preview</button>
        <button class="toggle-btn" data-content="quiz" onclick="setContentMode('quiz')">📝 Quiz Preview</button>
    </div>
    @endif
</div>

<div class="device-stage">

    <!-- ============ MOBILE FRAME ============ -->
    <div class="phone-mockup" id="mobileFrame">
        <div class="status-bar"><span>9:41</span><span>📶 🔋</span></div>

        <div class="preview-content">
            <!-- Lesson panel -->
            <div class="content-panel active" id="m-lessonPanel">
                @if($totalSlides > 0)
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
                                    @if(isset($current['content_type']) && !empty($current['media']) && is_string($current['media']))
                                        @php
                                            $mediaUrl = formatImageUrl($current['media']);
                                        @endphp
                                        @if($current['content_type'] == 'image')
                                            <div class="media-wrap"><img src="{{ $mediaUrl }}" alt="Slide image" class="slide-image" onerror="this.style.display='none'"></div>
                                        @elseif($current['content_type'] == 'video')
                                            <div class="media-wrap"><video controls class="slide-video"><source src="{{ $mediaUrl }}" type="video/mp4"></video></div>
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
            <div class="content-panel" id="m-quizPanel">
                @if($totalQuestions > 0)
                    <div class="glass-card">
                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                            <span style="font-size:12px; font-weight:700; color:#0f3172;" id="m-quizLabel">Question 1 of {{ $totalQuestions }}</span>
                            <span class="badge badge-yellow">⚡ 10 XP</span>
                        </div>
                        <div class="progress-dots" id="m-quizDots">
                            @foreach($normalizedQuiz as $index => $quizItem)
                                <div class="progress-dot {{ $index == 0 ? 'active' : 'pending' }}"></div>
                            @endforeach
                        </div>
                    </div>
                    @foreach($normalizedQuiz as $qIndex => $q)
                        <div class="quiz-question-panel {{ $qIndex == 0 ? 'active' : '' }}" id="m-q-{{ $qIndex }}">
                            <div class="glass-card" style="text-align:center; padding:24px;">
                                @if(!empty($q['_media']))
                                    @php
                                        $imageUrl = formatImageUrl($q['_media']);
                                    @endphp
                                    @if($imageUrl)
                                        <div class="quiz-media-wrap">
                                            <img src="{{ $imageUrl }}" alt="Quiz image" class="quiz-media" 
                                                 onerror="this.parentElement.innerHTML='<div style=\'padding:20px;text-align:center;color:#999;font-size:13px;\'>⚠️ Image not available</div>'">
                                        </div>
                                    @endif
                                @endif
                                <p style="font-size:16px; font-weight:800; color:#0f3172; margin-top:10px;">{{ $q['question'] ?? 'Sample Question' }}</p>
                            </div>
                            @foreach($q['_opts'] as $optIndex => $option)
                                <div class="option-card" onclick="selectOption('m', {{ $qIndex }}, {{ $optIndex }})" id="m-opt-{{ $qIndex }}-{{ $optIndex }}">
                                    <div class="option-circle">{{ chr(65+$optIndex) }}</div>
                                    @if(!empty($option['image']))
                                        @php
                                            $optionImageUrl = formatImageUrl($option['image']);
                                        @endphp
                                        @if($optionImageUrl)
                                            <img src="{{ $optionImageUrl }}" alt="" class="option-image-thumb"
                                                 onerror="this.style.display='none'">
                                        @endif
                                    @endif
                                    <span style="font-size:14px; font-weight:600; color:#1F2937;">{{ $option['text'] }}</span>
                                </div>
                            @endforeach
                            <div class="senya-tip">
                                <img src="{{ asset('images/senya_teaching.png') }}" alt="Senya Teaching">
                                <div class="feedback-bubble"><span style="font-size:12.5px; font-weight:500; color:#0f3172; line-height:1.5;">Read carefully and pick the best answer!</span></div>
                            </div>
                            <div class="quiz-nav">
                                <button class="ghost-btn" onclick="prevQuestion('m')" style="{{ $qIndex == 0 ? 'display:none;' : '' }}">← Back</button>
                                <button class="primary-btn {{ $qIndex == $totalQuestions-1 ? 'gold-btn' : '' }}" style="{{ $qIndex == 0 ? 'flex:1;' : '' }}" onclick="nextQuestion('m')">
                                    {{ $qIndex == $totalQuestions-1 ? 'Finish Quiz' : 'Next →' }}
                                </button>
                            </div>
                        </div>
                    @endforeach
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
                <div class="content-panel active" id="w-lessonPanel">
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
                                                @if(isset($current['content_type']) && !empty($current['media']) && is_string($current['media']))
                                                    @php
                                                        $mediaUrl = formatImageUrl($current['media']);
                                                    @endphp
                                                    @if($current['content_type'] == 'image')
                                                        <div class="media-wrap"><img src="{{ $mediaUrl }}" class="slide-image" onerror="this.style.display='none'"></div>
                                                    @elseif($current['content_type'] == 'video')
                                                        <div class="media-wrap"><video controls class="slide-video"><source src="{{ $mediaUrl }}" type="video/mp4"></video></div>
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
                <div class="content-panel" id="w-quizPanel">
                    @if($totalQuestions > 0)
                        <div class="web-grid-2col">
                            <div class="web-content-col">
                                <div class="glass-card">
                                    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:10px;">
                                        <span style="font-size:13px; font-weight:700; color:#0f3172;" id="w-quizLabel">Question 1 of {{ $totalQuestions }}</span>
                                        <span class="badge badge-yellow">⚡ 10 XP</span>
                                    </div>
                                    <div class="progress-dots" id="w-quizDots">
                                        @foreach($normalizedQuiz as $index => $quizItem)
                                            <div class="progress-dot {{ $index == 0 ? 'active' : 'pending' }}"></div>
                                        @endforeach
                                    </div>
                                </div>
                                @foreach($normalizedQuiz as $qIndex => $q)
                                    <div class="quiz-question-panel {{ $qIndex == 0 ? 'active' : '' }}" id="w-q-{{ $qIndex }}">
                                        <div class="glass-card" style="text-align:center; padding:28px;">
                                            @if(!empty($q['_media']))
                                                @php
                                                    $imageUrl = formatImageUrl($q['_media']);
                                                @endphp
                                                @if($imageUrl)
                                                    <div class="quiz-media-wrap" style="max-width:360px;">
                                                        <img src="{{ $imageUrl }}" class="quiz-media" 
                                                             onerror="this.parentElement.innerHTML='<div style=\'padding:20px;text-align:center;color:#999;font-size:13px;\'>⚠️ Image not available</div>'">
                                                    </div>
                                                @endif
                                            @endif
                                            <p style="font-size:18px; font-weight:800; color:#0f3172; margin-top:10px;">{{ $q['question'] ?? 'Sample Question' }}</p>
                                        </div>
                                        @foreach($q['_opts'] as $optIndex => $option)
                                            <div class="option-card" onclick="selectOption('w', {{ $qIndex }}, {{ $optIndex }})" id="w-opt-{{ $qIndex }}-{{ $optIndex }}">
                                                <div class="option-circle">{{ chr(65+$optIndex) }}</div>
                                                @if(!empty($option['image']))
                                                    @php
                                                        $optionImageUrl = formatImageUrl($option['image']);
                                                    @endphp
                                                    @if($optionImageUrl)
                                                        <img src="{{ $optionImageUrl }}" alt="" class="option-image-thumb"
                                                             onerror="this.style.display='none'">
                                                    @endif
                                                @endif
                                                <span style="font-size:14.5px; font-weight:600; color:#1F2937;">{{ $option['text'] }}</span>
                                            </div>
                                        @endforeach
                                        <div class="quiz-nav">
                                            <button class="ghost-btn" onclick="prevQuestion('w')" style="{{ $qIndex == 0 ? 'display:none;' : '' }}">← Back</button>
                                            <button class="primary-btn {{ $qIndex == $totalQuestions-1 ? 'gold-btn' : '' }}" style="{{ $qIndex == 0 ? 'flex:1;' : '' }}" onclick="nextQuestion('w')">
                                                {{ $qIndex == $totalQuestions-1 ? 'Finish Quiz' : 'Next →' }}
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="web-side-col">
                                <div class="glass-card">
                                    <div class="senya-tip" style="margin-bottom:0;">
                                        <img src="{{ asset('images/senya_teaching.png') }}" alt="Senya Teaching">
                                        <div class="feedback-bubble"><span style="font-size:13px; font-weight:500; color:#0f3172; line-height:1.6;">Read carefully and pick the best answer!</span></div>
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

function colorFor(i) { return slideColors[i % slideColors.length]; }

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
    document.querySelectorAll('#' + prefix + '-q-' + qIndex + ' .option-card').forEach(c => c.classList.remove('selected'));
    const chosen = document.getElementById(prefix + '-opt-' + qIndex + '-' + optIndex);
    if (chosen) chosen.classList.add('selected');
};

if (totalSlides > 0) { setSlide('m', 0); setSlide('w', 0); }
if (totalQuestions > 0) { setQuestion('m', 0); setQuestion('w', 0); }
if (totalSlides === 0 && totalQuestions > 0) { setContentMode('quiz'); }
})();
</script>