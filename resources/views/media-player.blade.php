<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Media Player</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { 
            background: transparent !important; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            width: 100%;
            height: 100%;
            overflow: hidden;
        }
        .container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0;
            margin: 0;
            background: transparent !important;
        }
        .media-wrapper {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: transparent !important;
            overflow: hidden;
            position: relative;
        }
        .media-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            background: transparent !important;
        }
        .media-wrapper video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            display: block;
            background: transparent !important;
        }
        /* ✅ Hide controls for option videos */
        .media-wrapper.hide-controls video::-webkit-media-controls {
            display: none !important;
        }
        .media-wrapper.hide-controls video::-webkit-media-controls-panel {
            display: none !important;
        }
        .media-wrapper.hide-controls video::-webkit-media-controls-enclosure {
            display: none !important;
        }
        /* ✅ Square aspect ratio for quiz questions */
        .media-wrapper.square video,
        .media-wrapper.square img {
            aspect-ratio: 1 / 1;
            object-fit: cover;
        }
        /* ✅ Rectangular for content and options */
        .media-wrapper.rectangular video,
        .media-wrapper.rectangular img {
            aspect-ratio: 16 / 9;
            object-fit: cover;
        }
        .caption {
            display: none !important;
        }
        .error-msg {
            color: #ef4444;
            font-size: 14px;
            text-align: center;
            padding: 20px;
        }
        .loading {
            color: #94a3b8;
            font-size: 14px;
            text-align: center;
            padding: 40px 20px;
        }
    </style>
</head>
<body>
    <div class="container" id="app">
        <div id="content" class="media-wrapper">
            <div class="loading">Loading media...</div>
        </div>
        <div id="caption" class="caption"></div>
    </div>

    <script>
        // Parse URL parameters
        const params = new URLSearchParams(window.location.search);
        const mediaUrl = params.get('url');
        const isVideo = params.get('isVideo') === 'true';
        const autoplay = params.get('autoplay') !== 'false';
        const aspect = params.get('aspect') || '16:9';
        const hideControls = params.get('hideControls') === 'true';

        const contentEl = document.getElementById('content');
        const captionEl = document.getElementById('caption');

        // ✅ Hide caption completely
        captionEl.style.display = 'none';

        // ✅ Add classes based on parameters
        if (hideControls) {
            contentEl.classList.add('hide-controls');
        }

        // ✅ Set aspect ratio class
        if (aspect === '1:1') {
            contentEl.classList.add('square');
        } else {
            contentEl.classList.add('rectangular');
        }

        if (!mediaUrl) {
            contentEl.innerHTML = '<div class="error-msg">⚠️ No media URL provided</div>';
        } else if (isVideo) {
            // ✅ Use controls based on hideControls parameter
            const controlsAttr = hideControls ? '' : 'controls';
            
            contentEl.innerHTML = `
                <video 
                    id="videoPlayer"
                    src="${mediaUrl}" 
                    ${controlsAttr}
                    autoplay="${autoplay ? 'true' : 'false'}"
                    loop="true"
                    muted="true"
                    playsinline="true"
                    preload="metadata"
                    style="width:100%; height:100%; object-fit:cover; background:transparent;"
                >
                    Your browser does not support video playback.
                </video>
            `;
            
            // Auto-play with fallback
            const video = document.getElementById('videoPlayer');
            if (video && autoplay) {
                video.play().catch(() => {
                    // Only show tap-to-play if controls are shown
                    if (!hideControls) {
                        const overlay = document.createElement('div');
                        overlay.style.cssText = `
                            position: absolute; inset: 0; 
                            display: flex; 
                            justify-content: center; 
                            align-items: center; 
                            background: rgba(0,0,0,0.2);
                            cursor: pointer;
                            z-index: 10;
                            pointer-events: none;
                        `;
                        overlay.innerHTML = `
                            <button style="
                                background: rgba(255,255,255,0.2);
                                border: 2px solid rgba(255,255,255,0.3);
                                border-radius: 50%;
                                width: 44px;
                                height: 44px;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-size: 20px;
                                color: white;
                                cursor: pointer;
                                backdrop-filter: blur(4px);
                                pointer-events: auto;
                            ">▶</button>
                        `;
                        overlay.onclick = (e) => {
                            e.stopPropagation();
                            video.play();
                            overlay.remove();
                        };
                        contentEl.appendChild(overlay);
                    }
                });
            }
        } else {
            contentEl.innerHTML = `
                <img 
                    src="${mediaUrl}" 
                    alt=""
                    loading="lazy"
                    style="width:100%; height:100%; object-fit:cover; background:transparent;"
                    onerror="this.parentElement.innerHTML='<div class=\\'error-msg\\'>⚠️ Image failed to load</div>'"
                />
            `;
        }
    </script>
</body>
</html>