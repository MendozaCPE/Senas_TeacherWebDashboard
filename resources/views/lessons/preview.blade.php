<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">

<style>
    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }

.phone-mockup {
    max-width: 390px;
    margin: 0 auto;
    background: #eaf5fd;
    border-radius: 40px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0,0,0,0.15);
    border: 8px solid #1a1a1a;
    position: relative;
    min-height: 780px;
    height: auto;
}
.phone-mockup .status-bar {
    padding: 12px 20px 8px;
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    font-weight: 600;
    color: #0f3172;
    background: transparent;
}
.glass-card {
    background: rgba(255,255,255,0.62);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255,255,255,0.85);
    border-radius: 20px;
    padding: 18px;
    margin-bottom: 12px;
    box-shadow: 0 2px 12px rgba(15,49,114,0.09);
}
.slide-accent {
    height: 4px;
    border-radius: 4px;
    margin: -18px -18px 14px -18px;
}
.slide-dot {
    height: 8px;
    border-radius: 99px;
    transition: all 0.3s;
    cursor: pointer;
    display: inline-block;
}
.slide-dot.active {
    width: 22px;
    background: #1848c8;
}
.slide-dot.inactive {
    width: 8px;
    background: rgba(15,49,114,0.15);
}
.primary-btn {
    background: #1848c8;
    color: white;
    padding: 14px 28px;
    border-radius: 60px;
    font-weight: 700;
    border: none;
    cursor: pointer;
    transition: all 0.3s;
    box-shadow: 0 5px 18px rgba(24,72,200,0.28);
    flex: 1;
}
.primary-btn:hover {
    background: #0f3172;
    transform: translateY(-2px);
}
.gold-btn { background: #D97706; }
.gold-btn:hover { background: #B45309; }
.ghost-btn {
    background: rgba(255,255,255,0.62);
    border: 1px solid rgba(255,255,255,0.85);
    padding: 14px 28px;
    border-radius: 60px;
    font-weight: 700;
    color: #0f3172;
    cursor: pointer;
    transition: all 0.3s;
    flex: 1;
}
.ghost-btn:hover {
    background: rgba(255,255,255,0.85);
}
.option-card {
    display: flex;
    align-items: center;
    gap: 12px;
    border: 1.5px solid rgba(255,255,255,0.85);
    border-radius: 16px;
    padding: 13px 16px;
    margin-bottom: 8px;
    background: rgba(255,255,255,0.62);
    cursor: pointer;
    transition: all 0.3s;
}
.option-card:hover {
    background: rgba(255,255,255,0.85);
}
.option-circle {
    width: 32px;
    height: 32px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    background: rgba(15,49,114,0.08);
    font-weight: 800;
    font-size: 13px;
    color: #4b7bbb;
}
.feedback-bubble {
    flex: 1;
    display: flex;
    align-items: flex-start;
    gap: 7px;
    background: rgba(255,255,255,0.75);
    border: 1px solid rgba(255,255,255,0.9);
    border-radius: 16px;
    padding: 12px;
}
.senya-tip {
    display: flex;
    align-items: flex-end;
    gap: 10px;
    margin-bottom: 14px;
}
.senya-tip img {
    width: 56px;
    height: 56px;
    flex-shrink: 0;
    object-fit: contain;
}
.tip-bubble {
    flex: 1;
    background: rgba(255,255,255,0.62);
    border: 1px solid rgba(255,255,255,0.85);
    border-radius: 14px;
    padding: 12px;
    font-size: 12px;
    color: #0f3172;
    font-weight: 500;
    line-height: 1.5;
}
.progress-dots {
    display: flex;
    gap: 4px;
}
.progress-dot {
    flex: 1;
    height: 5px;
    border-radius: 99px;
}
.progress-dot.completed { background: #22c55e; }
.progress-dot.active { background: #2563EB; }
.progress-dot.pending { background: rgba(15,49,114,0.10); }
.badge {
    background: rgba(15,49,114,0.08);
    border-radius: 8px;
    padding: 4px 10px;
    font-size: 11px;
    font-weight: 800;
    color: #1848c8;
    letter-spacing: 0.5px;
    display: inline-block;
}
.badge-yellow {
    background: rgba(245,158,11,0.13);
    color: #92400E;
}
.slide-nav {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}
.preview-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 12px 20px 8px;
}
.preview-header .logo {
    font-size: 22px;
    font-weight: 800;
    color: #0f3172;
    letter-spacing: 2px;
}
.preview-header .exit-btn {
    background: rgba(255,255,255,0.7);
    border-radius: 12px;
    padding: 6px 12px;
    font-size: 13px;
    font-weight: 700;
    color: #6B7280;
    border: 1px solid rgba(255,255,255,0.85);
    cursor: pointer;
}
.preview-content {
    padding: 0 16px 16px;
    min-height: 650px;
}
.slide-image {
    width: 100%;
    border-radius: 12px;
    margin: 10px 0;
    max-height: 200px;
    object-fit: contain;
}
.slide-video {
    width: 100%;
    border-radius: 12px;
    margin: 10px 0;
    max-height: 200px;
    background: #000;
}
.hero-image {
    width: 80px;
    height: 80px;
    flex-shrink: 0;
    object-fit: contain;
}
.quiz-media {
    width: 100%;
    border-radius: 12px;
    margin: 10px 0;
    max-height: 150px;
    object-fit: contain;
}
.preview-close-btn {
    position: sticky;
    top: 10px;
    float: right;
    background: rgba(255,255,255,0.9);
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    font-size: 20px;
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    z-index: 100;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s;
    margin: 10px 10px 0 0;
}
.preview-close-btn:hover {
    transform: scale(1.1);
    background: white;
}
</style>

<div class="phone-mockup">
    <button class="preview-close-btn" onclick="window.parent.closePreview()">✕</button>
    
    <div class="status-bar">
        <span>9:41</span>
        <span>📶 🔋</span>
    </div>

    <div class="preview-content" id="previewContent">
        @php
            $totalSlides = count($lessonData['contents']);
            $colors = ['#2563EB', '#059669', '#F59E0B', '#8B5CF6', '#EF4444', '#EC4899', '#14B8A6'];
        @endphp

        @if($totalSlides > 0)
            <div class="preview-header">
                <span class="logo">SEÑAS</span>
                <span class="exit-btn">✕ Exit</span>
            </div>

            <div class="glass-card">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <div style="flex: 1;">
                        <span class="badge">MODULE</span>
                        <h3 style="font-size: 20px; font-weight: 800; color: #0f3172; margin: 4px 0;">
                            {{ $lessonData['title'] }}
                        </h3>
                        <p style="font-size: 12px; color: #4b7bbb; font-weight: 500;">
                            {{ $totalSlides }} slides · ~2 min read
                        </p>
                    </div>
                    <img src="{{ asset('images/wavingSenya.png') }}" alt="Senya" class="hero-image">
                </div>
            </div>

            <!-- Slide Dots -->
            <div style="display: flex; justify-content: center; gap: 6px; margin-bottom: 12px;" id="slideDots">
                @foreach($lessonData['contents'] as $index => $content)
                    <div class="slide-dot {{ $index == 0 ? 'active' : 'inactive' }}" 
                         data-slide="{{ $index }}"
                         onclick="setSlide({{ $index }})"
                         style="cursor: pointer;"></div>
                @endforeach
            </div>

            <!-- Slide Content -->
            <div id="slideContainer">
                @foreach($lessonData['contents'] as $index => $current)
                    <div class="slide-content" id="slide-{{ $index }}" data-slide="{{ $index }}" style="{{ $index == 0 ? '' : 'display: none;' }}">
                        <div class="glass-card" style="min-height: 200px;">
                            <div class="slide-accent" style="background: {{ $colors[$index % count($colors)] }};"></div>
                            <h3 style="font-size: 17px; font-weight: 800; color: {{ $colors[$index % count($colors)] }}; margin-bottom: 10px;">
                                {{ $current['title'] ?? 'Slide Title' }}
                            </h3>
                            
                            @if(isset($current['content_type']))
    @if($current['content_type'] == 'image' && isset($current['media']) && $current['media'])
        @if(isset($current['is_temp']) && $current['is_temp'])
            {{-- Temporary file from preview --}}
            <img src="{{ asset('storage/' . $current['media']) }}" alt="Slide image" class="slide-image">
        @else
            {{-- Permanent file from database --}}
            <img src="{{ asset('storage/' . $current['media']) }}" alt="Slide image" class="slide-image">
        @endif
    @elseif($current['content_type'] == 'video' && isset($current['media']) && $current['media'])
        <video controls class="slide-video">
            <source src="{{ asset('storage/' . $current['media']) }}" type="video/mp4">
            Your browser does not support the video tag.
        </video>
    @endif
@endif
                            
                            <p style="font-size: 14px; color: #334155; line-height: 1.6;">
                                {{ $current['content_text'] ?? 'Content goes here...' }}
                            </p>
                            <p style="font-size: 11px; color: #9CA3AF; font-weight: 600; margin-top: 12px; text-align: right;" class="slide-counter">
                                {{ $index + 1 }} / {{ $totalSlides }}
                            </p>
                        </div>

                        <div class="senya-tip">
                            <img src="{{ asset('images/senya_teaching.png') }}" alt="Senya Teaching">
                            <div class="tip-bubble">
                                Hi! I'm Senya. Let's learn about FSL before your quiz!
                            </div>
                        </div>

                        <div class="slide-nav">
                            <button class="ghost-btn" onclick="prevSlide()" id="prevBtn" style="{{ $index == 0 ? 'visibility: hidden;' : '' }}">
                                ← Back
                            </button>
                            <button class="primary-btn {{ $index == $totalSlides - 1 ? 'gold-btn' : '' }}" 
                                    onclick="nextSlide()">
                                {{ $index == $totalSlides - 1 ? 'Start Quiz' : 'Next →' }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div style="text-align: center; padding: 40px 20px; color: #6B7280;">
                <p style="font-size: 16px;">No content added yet.</p>
                <p style="font-size: 14px;">Add slides in the lesson creation form.</p>
            </div>
        @endif
    </div>
</div>

@if(count($lessonData['quiz'] ?? []) > 0)
<div style="max-width: 390px; margin: 20px auto;" id="quizSection">
    <h3 style="font-size: 18px; font-weight: 700; color: #0f3172; margin-bottom: 12px;">📝 Quiz Preview ({{ count($lessonData['quiz']) }} Questions)</h3>
    
    @foreach($lessonData['quiz'] as $questionIndex => $q)
    <div class="phone-mockup" style="max-width: 390px; margin-bottom: 20px;">
        <div style="padding: 16px;">
            <div class="glass-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                    <span style="font-size: 12px; font-weight: 700; color: #0f3172;">Question {{ $questionIndex + 1 }} of {{ count($lessonData['quiz']) }}</span>
                    <span class="badge badge-yellow">⚡ 10 XP</span>
                </div>
                <div class="progress-dots">
                    @foreach($lessonData['quiz'] as $index => $quizItem)
                        <div class="progress-dot {{ $index == $questionIndex ? 'active' : ($index < $questionIndex ? 'completed' : 'pending') }}"></div>
                    @endforeach
                </div>
            </div>

            <div class="glass-card" style="text-align: center; padding: 24px;">
                @if(isset($q['media']) && $q['media'])
    {{-- Debug: Show the path being used --}}
    <!-- Quiz media path: {{ $q['media'] }} -->
    @if(isset($q['is_temp']) && $q['is_temp'])
        <img src="{{ asset('storage/' . $q['media']) }}" alt="Quiz image" class="quiz-media">
    @else
        <img src="{{ asset('storage/' . $q['media']) }}" alt="Quiz image" class="quiz-media">
    @endif
@endif
                <p style="font-size: 16px; font-weight: 800; color: #0f3172; margin-top: 10px;">
                    {{ $q['question'] ?? 'Sample Question' }}
                </p>
            </div>

            @php
                $quizOptions = $q['options'] ?? ['Option A', 'Option B'];
                $questionType = $q['type'] ?? 'multiple_choice';
            @endphp

            @if($questionType == 'true_false')
                @foreach(['True', 'False'] as $optIndex => $option)
                    <div class="option-card">
                        <div class="option-circle">{{ chr(65 + $optIndex) }}</div>
                        <span style="font-size: 14px; font-weight: 600; color: #1F2937;">{{ $option }}</span>
                    </div>
                @endforeach
            @else
                @foreach($quizOptions as $optIndex => $option)
                    <div class="option-card">
                        <div class="option-circle">{{ chr(65 + $optIndex) }}</div>
                        <span style="font-size: 14px; font-weight: 600; color: #1F2937;">{{ $option }}</span>
                    </div>
                @endforeach
            @endif

            <div class="senya-tip">
                <img src="{{ asset('images/senya_teaching.png') }}" alt="Senya Teaching">
                <div class="feedback-bubble">
                    <span style="font-size: 12.5px; font-weight: 500; color: #0f3172; line-height: 1.5;">
                        Read carefully and pick the best answer!
                    </span>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

<script>
// Wrapped in an IIFE so re-injecting this script (every time the
// preview overlay reopens) does NOT throw
// "Identifier 'totalSlides' has already been declared".
// Handlers are attached to window so inline onclick="nextSlide()" still works.
(function () {
let currentSlide = 0;
const totalSlides = {{ $totalSlides }};

function setSlide(index) {
    console.log('setSlide called with index:', index);
    
    if (index < 0 || index >= totalSlides) {
        console.log('Invalid index, returning');
        return;
    }
    
    currentSlide = index;
    console.log('Current slide now:', currentSlide);
    
    // Hide all slides
    const allSlides = document.querySelectorAll('.slide-content');
    console.log('Found slides:', allSlides.length);
    allSlides.forEach(el => {
        el.style.display = 'none';
    });
    
    // Show selected slide
    const selectedSlide = document.getElementById('slide-' + index);
    if (selectedSlide) {
        selectedSlide.style.display = 'block';
        console.log('Showing slide:', index);
    } else {
        console.log('Slide element not found for index:', index);
    }
    
    // Update dots
    document.querySelectorAll('.slide-dot').forEach((dot, i) => {
        dot.className = i === index ? 'slide-dot active' : 'slide-dot inactive';
    });
    
    // Update counters
    document.querySelectorAll('.slide-counter').forEach((counter, i) => {
        if (i === index) {
            counter.textContent = (index + 1) + ' / ' + totalSlides;
        }
    });
}

function nextSlide() {
    console.log('nextSlide called, current:', currentSlide);
    if (currentSlide < totalSlides - 1) {
        setSlide(currentSlide + 1);
    } else {
        console.log('On last slide, starting quiz');
        startQuiz();
    }
}

function prevSlide() {
    console.log('prevSlide called, current:', currentSlide);
    if (currentSlide > 0) {
        setSlide(currentSlide - 1);
    }
}

function startQuiz() {
    console.log('Starting quiz');
    const quizSection = document.getElementById('quizSection');
    if (quizSection) {
        quizSection.scrollIntoView({ behavior: 'smooth' });
    }
}

// Expose to global so inline onclick="..." handlers can find them.
window.setSlide  = setSlide;
window.nextSlide = nextSlide;
window.prevSlide = prevSlide;
window.startQuiz = startQuiz;

// Debug: Show that everything is loaded
console.log('Total slides:', totalSlides);
console.log('Slide elements found:', document.querySelectorAll('.slide-content').length);

// Test the function immediately
console.log('Testing setSlide(0) on load...');
setSlide(0);
})();
</script>