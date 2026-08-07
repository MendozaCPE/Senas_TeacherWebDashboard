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
            display: block;
            background: transparent !important;
        }
        .media-wrapper video {
            width: 100%;
            height: 100%;
            display: block;
            background: transparent !important;
        }
        /* ✅ Object fit classes */
        .media-wrapper.fit-cover img,
        .media-wrapper.fit-cover video {
            object-fit: cover;
        }
        .media-wrapper.fit-contain img,
        .media-wrapper.fit-contain video {
            object-fit: contain;
        }
        .media-wrapper.fit-fill img,
        .media-wrapper.fit-fill video {
            object-fit: fill;
        }
        /* ✅ Object position classes - including percentage support */
        .media-wrapper.position-center img,
        .media-wrapper.position-center video {
            object-position: center;
        }
        .media-wrapper.position-left img,
        .media-wrapper.position-left video {
            object-position: left center;
        }
        .media-wrapper.position-right img,
        .media-wrapper.position-right video {
            object-position: right center;
        }
        .media-wrapper.position-top img,
        .media-wrapper.position-top video {
            object-position: center top;
        }
        .media-wrapper.position-bottom img,
        .media-wrapper.position-bottom video {
            object-position: center bottom;
        }
        /* ✅ For custom percentage positions (e.g., "70% center") */
        .media-wrapper.position-custom img,
        .media-wrapper.position-custom video {
            object-position: var(--custom-position, center);
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
        var params = new URLSearchParams(window.location.search);
        var mediaUrl = params.get('url');
        var isVideo = params.get('isVideo') === 'true';
        var autoplay = params.get('autoplay') !== 'false';
        var aspect = params.get('aspect') || '16:9';
        var hideControls = params.get('hideControls') === 'true';
        var fit = params.get('fit') || 'cover';
        var position = params.get('position') || 'center';

        var contentEl = document.getElementById('content');
        var captionEl = document.getElementById('caption');

        // Hide caption completely
        captionEl.style.display = 'none';

        // Add classes based on parameters
        if (hideControls) {
            contentEl.classList.add('hide-controls');
        }

        // Add fit class
        contentEl.classList.add('fit-' + fit);

        // Handle position - check if it's a percentage or named value
        var positionMap = {
            'center': 'center',
            'left': 'left center',
            'right': 'right center',
            'top': 'center top',
            'bottom': 'center bottom'
        };

        // If position is a percentage or custom value (e.g., "70% center")
        if (position in positionMap) {
            contentEl.classList.add('position-' + position);
        } else {
            // Custom position like "70% center" or "75% center"
            contentEl.classList.add('position-custom');
            // Set CSS custom property for the position
            contentEl.style.setProperty('--custom-position', position);
        }

        // Set aspect ratio class
        if (aspect === '1:1') {
            contentEl.classList.add('square');
        } else {
            contentEl.classList.add('rectangular');
        }

        if (!mediaUrl) {
            contentEl.innerHTML = '<div class="error-msg">⚠️ No media URL provided</div>';
        } else if (isVideo) {
            var controlsAttr = hideControls ? '' : 'controls';
            
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
                    style="width:100%; height:100%; background:transparent;"
                >
                    Your browser does not support video playback.
                </video>
            `;
            
            var video = document.getElementById('videoPlayer');
            if (video && autoplay) {
                video.play().catch(function() {
                    if (!hideControls) {
                        var overlay = document.createElement('div');
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
                        overlay.onclick = function(e) {
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
                    style="width:100%; height:100%; background:transparent;"
                    onerror="this.parentElement.innerHTML='<div class=\\'error-msg\\'>⚠️ Image failed to load</div>'"
                />
            `;
        }
    </script>
</body>
</html>