<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>SENAS Gesture Recognition</title>
    <link rel="icon" type="image/png" href="{{ asset('images/senya_face.png') }}">
    <style>
        * { margin: 0; padding: 0; }
        body { background: #0a1628; overflow: hidden; font-family: Arial, sans-serif; }
        #container { position: relative; width: 100vw; height: 100vh; }
        video { width: 100%; height: 100%; object-fit: cover; transform: scaleX(-1); }
        #canvas {
            position: absolute; top: 0; left: 0;
            width: 100%; height: 100%; pointer-events: none;
        }
        #overlay {
            position: absolute; bottom: 80px; left: 0; right: 0;
            text-align: center; pointer-events: none;
        }
        #letter-display {
            display: inline-block;
            background: rgba(15, 49, 114, 0.9);
            padding: 20px 40px;
            border-radius: 20px;
            border: 3px solid #FFD700;
            backdrop-filter: blur(10px);
            min-width: 200px;
        }
        #letter {
            color: #FFD700;
            font-size: 72px;
            font-weight: bold;
            font-family: Arial;
        }
        #confidence {
            color: #4ECDC4;
            font-size: 16px;
            margin-top: 4px;
            font-weight: 600;
        }
        #status {
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            margin-top: 8px;
        }
        .dot {
            display: inline-block;
            width: 14px;
            height: 14px;
            border-radius: 50%;
            margin-right: 10px;
            vertical-align: middle;
        }
        .dot.active { background: #10B981; box-shadow: 0 0 20px #10B981; }
        .dot.waiting { background: #F59E0B; box-shadow: 0 0 20px #F59E0B; }
        .dot.error { background: #EF4444; box-shadow: 0 0 20px #EF4444; }
        #loading {
            position: absolute;
            top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            color: white;
            text-align: center;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 4px solid rgba(255,255,255,0.1);
            border-top: 4px solid #FFD700;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin { 100% { transform: rotate(360deg); } }
        .progress-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: rgba(255,255,255,0.1);
        }
        .progress-fill {
            height: 100%;
            background: #FFD700;
            transition: width 0.3s ease;
            width: 0%;
        }
    </style>
</head>
<body>
    <div id="container">
        <video id="video" autoplay playsinline></video>
        <canvas id="canvas"></canvas>
        
        <div id="loading">
            <div class="spinner"></div>
            <div style="font-size:18px;font-weight:600;">Loading SENAS...</div>
            <div style="font-size:14px;color:#6B7280;margin-top:8px;">Initializing camera and models</div>
        </div>

        <div id="overlay">
            <div id="letter-display">
                <div style="display:flex;align-items:center;justify-content:center;gap:12px;margin-bottom:4px;">
                    <span class="dot waiting" id="statusDot"></span>
                    <span style="color:white;font-size:14px;font-weight:600;" id="statusText">Initializing...</span>
                </div>
                <div id="letter">✋</div>
                <div id="confidence">Confidence: 0%</div>
                <div id="status"></div>
            </div>
        </div>
        
        <div class="progress-bar">
            <div class="progress-fill" id="confidenceBar"></div>
        </div>
    </div>

    <!-- Load MediaPipe -->
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/control_utils/control_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/drawing_utils/drawing_utils.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@mediapipe/hands/hands.js" crossorigin="anonymous"></script>

    <!-- Load TensorFlow.js -->
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs-core"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs-converter"></script>
    <script src="https://cdn.jsdelivr.net/npm/@tensorflow/tfjs-backend-webgl"></script>

    @verbatim
    <script>
        // ============================================================
        // YOUR HEURISTIC FUNCTIONS (copied from Python)
        // ============================================================
        function getFingerStates(landmarks) {
            const tips = [4, 8, 12, 16, 20];
            const dips = [3, 6, 10, 14, 18];
            const fingers = [];
            
            const thumbExtended = Math.abs(landmarks[4].x - landmarks[2].x) > 0.045;
            fingers.push(thumbExtended);
            
            for (let i = 1; i < tips.length; i++) {
                const extended = landmarks[tips[i]].y < landmarks[dips[i]].y;
                fingers.push(extended);
            }
            return fingers;
        }

        function getDistance3D(p1, p2) {
            return Math.sqrt(
                Math.pow(p1.x - p2.x, 2) +
                Math.pow(p1.y - p2.y, 2) +
                Math.pow(p1.z - p2.z, 2)
            );
        }

        function getDistance2D(p1, p2) {
            return Math.sqrt(
                Math.pow(p1.x - p2.x, 2) +
                Math.pow(p1.y - p2.y, 2)
            );
        }

        function applyCriticalDlyOverrides(predicted, avgConf, landmarks) {
            const fingers = getFingerStates(landmarks);
            const thumbTip = landmarks[4];
            const indexTip = landmarks[8];
            const indexDip = landmarks[7];
            const indexMcp = landmarks[5];
            const middleTip = landmarks[12];
            const middleMcp = landmarks[9];
            const ringTip = landmarks[16];
            const pinkyTip = landmarks[20];

            const isIndexHooked = (indexTip.y > indexDip.y) || (Math.abs(indexTip.x - indexDip.x) > 0.02 && !fingers[1]);
            const othersDown = !fingers[2] && !fingers[3] && !fingers[4];

            if (isIndexHooked && othersDown && ['L', 'Y', 'X', '?'].includes(predicted)) {
                return { letter: 'X', confidence: 0.98 };
            }

            const isBShape = fingers[1] && fingers[2] && fingers[3] && fingers[4];
            if (isBShape) {
                return { letter: 'B', confidence: 0.98 };
            }

            const indexUpState = fingers[1];
            const distThumbToMiddle3D = getDistance3D(thumbTip, middleTip);
            const distThumbToRing3D = getDistance3D(thumbTip, ringTip);
            const distThumbToMiddle2D = getDistance2D(thumbTip, middleTip);
            const ringMcpY = landmarks[13].y;
            const areFingersCurledToPalm = (middleTip.y > middleMcp.y) && (ringTip.y > ringMcpY);
            const distThumbToPalmCenter = getDistance2D(thumbTip, middleMcp);

            const isFormingD = (
                distThumbToMiddle3D < 0.10 ||
                distThumbToRing3D < 0.10 ||
                distThumbToMiddle2D < 0.08 ||
                (areFingersCurledToPalm && distThumbToPalmCenter < 0.10)
            );

            if (indexUpState && !isIndexHooked) {
                if (isFormingD) {
                    return { letter: 'D', confidence: 0.99 };
                }
                const isThumbStretchedWide = Math.abs(thumbTip.x - indexMcp.x) > 0.075;
                if (isThumbStretchedWide) {
                    return { letter: 'L', confidence: 0.96 };
                }
            }

            if (fingers[4] && !fingers[1] && !fingers[2] && !fingers[3]) {
                const isThumbActiveY = Math.abs(thumbTip.x - indexMcp.x) > 0.055;
                if (isThumbActiveY && pinkyTip.y < landmarks[19].y) {
                    return { letter: 'Y', confidence: 0.98 };
                }
            }

            return { letter: predicted, confidence: avgConf };
        }

        function applyUvwxyRules(predicted, avgConf, fingers, fingerSpread, landmarks) {
            const indexTip = landmarks[8];
            const middleTip = landmarks[12];
            const indexMcp = landmarks[5];
            const middleMcp = landmarks[9];
            const thumbTip = landmarks[4];

            const isCrossed = indexTip.x > middleTip.x + 0.003;
            const indexUp = indexTip.y < landmarks[6].y;
            const middleUp = middleTip.y < landmarks[10].y;
            const ringDown = landmarks[16].y > landmarks[13].y;
            const pinkyDown = landmarks[20].y > landmarks[17].y;

            const indexExtendedSideways = Math.abs(indexTip.x - indexMcp.x) > 0.06;
            const middleExtendedSideways = Math.abs(middleTip.x - middleMcp.x) > 0.06;

            const isThumbCenteredK = Math.abs(thumbTip.y - middleMcp.y) < 0.06 && 
                (Math.min(indexTip.x, indexMcp.x) <= thumbTip.x <= Math.max(middleTip.x, middleMcp.x) || 
                Math.abs(thumbTip.x - middleMcp.x) < 0.05);

            if ((indexExtendedSideways && middleExtendedSideways && ringDown && pinkyDown) || 
                (predicted === 'K' && ringDown && pinkyDown)) {
                if (isThumbCenteredK) {
                    return { letter: 'K', confidence: 0.99 };
                }
            }

            if (indexUp && middleUp && ringDown && pinkyDown) {
                if (isCrossed) {
                    return { letter: 'R', confidence: 0.98 };
                }
                return { letter: fingerSpread > 0.055 ? 'V' : 'U', confidence: 0.98 };
            }

            if (fingers[1] && fingers[2] && fingers[3] && !fingers[4]) {
                return { letter: 'W', confidence: 0.95 };
            }

            return { letter: predicted, confidence: avgConf };
        }

        function applyMnsRules(predicted, avgConf, fingers, landmarks) {
            const indexTipY = landmarks[8].y;
            const indexMcp = landmarks[5];
            const middleMcp = landmarks[9];
            const middleTipY = landmarks[12].y;
            const middlePipY = landmarks[10].y;
            const thumbTip = landmarks[4];
            const indexPip = landmarks[6];
            const extendedCount = fingers.filter(f => f).length;

            const thumbTip2 = landmarks[4];
            const indexTip2 = landmarks[8];
            const middleTip2 = landmarks[12];
            const ringTip2 = landmarks[16];
            const pinkyTip2 = landmarks[20];

            const distThumbIndex = Math.sqrt(
                Math.pow(thumbTip2.x - indexTip2.x, 2) + 
                Math.pow(thumbTip2.y - indexTip2.y, 2)
            );
            const distThumbMiddle = Math.sqrt(
                Math.pow(thumbTip2.x - middleTip2.x, 2) + 
                Math.pow(thumbTip2.y - middleTip2.y, 2)
            );
            const distThumbRing = Math.sqrt(
                Math.pow(thumbTip2.x - ringTip2.x, 2) + 
                Math.pow(thumbTip2.y - ringTip2.y, 2)
            );
            const distThumbPinky = Math.sqrt(
                Math.pow(thumbTip2.x - pinkyTip2.x, 2) + 
                Math.pow(thumbTip2.y - pinkyTip2.y, 2)
            );

            if (distThumbIndex < 0.08 && distThumbMiddle < 0.08 && 
                distThumbRing < 0.08 && distThumbPinky < 0.08) {
                return { letter: 'O', confidence: 0.95 };
            }

            if (predicted in ['A', 'O'] && avgConf > 0.75) {
                return { letter: predicted, confidence: avgConf };
            }

            const isEShape = (indexTipY < indexMcp.y && middleTipY < indexMcp.y) && extendedCount === 0;
            if (isEShape) {
                if (['S', 'E', 'M', 'N'].includes(predicted)) {
                    return { letter: 'E', confidence: 0.95 };
                }
            }

            if (extendedCount === 0 && !isEShape) {
                const isSThumbPose = (thumbTip.y < middlePipY + 0.01) && (thumbTip.x > indexPip.x - 0.01);
                if (['S', 'N'].includes(predicted) && !isSThumbPose) {
                    if (thumbTip.x <= middleMcp.x) {
                        return { letter: 'N', confidence: 0.96 };
                    }
                }
                if (isSThumbPose || predicted === 'S') {
                    return { letter: 'S', confidence: 0.95 };
                }
                if (predicted === 'M' || (predicted === 'N' && avgConf < 0.70)) {
                    return { letter: thumbTip.x > middleMcp.x ? 'M' : 'N', confidence: 0.95 };
                }
            }

            return { letter: predicted, confidence: avgConf };
        }

        // Main detection function
        function detectLetter(landmarks) {
            if (!landmarks || landmarks.length < 21) {
                return { letter: '✋', confidence: 0 };
            }

            try {
                const fingers = getFingerStates(landmarks);
                const fingerSpread = Math.abs(landmarks[8].x - landmarks[12].x);
                
                // Heuristic detection for J and Z
                const othersDown = !fingers[2] && !fingers[3] && !fingers[4];
                const indexUp = fingers[1];
                const pinkyUp = fingers[4];
                const thumbTip = landmarks[4];
                const indexMCP = landmarks[5];

                // J: Pinky up, others down
                if (pinkyUp && othersDown) {
                    return { letter: 'J', confidence: 0.85 };
                }

                // Z: Index up, others down, thumb tucked
                const thumbTucked = Math.abs(thumbTip.x - indexMCP.x) < 0.05 && thumbTip.y > indexMCP.y - 0.02;
                if (indexUp && othersDown && !pinkyUp && thumbTucked) {
                    return { letter: 'Z', confidence: 0.90 };
                }

                // Apply all heuristic rules
                let result = { letter: 'A', confidence: 0.5 };
                result = applyCriticalDlyOverrides(result.letter, result.confidence, landmarks);
                result = applyUvwxyRules(result.letter, result.confidence, fingers, fingerSpread, landmarks);
                result = applyMnsRules(result.letter, result.confidence, fingers, landmarks);
                
                return result;
            } catch (error) {
                console.error('Detection error:', error);
                return { letter: '✋', confidence: 0 };
            }
        }

        // ============================================================
        // MEDIAPIPE SETUP
        // ============================================================
        const videoElement = document.getElementById('video');
        const canvasElement = document.getElementById('canvas');
        const canvasCtx = canvasElement.getContext('2d');
        const letterElement = document.getElementById('letter');
        const confidenceElement = document.getElementById('confidence');
        const confidenceBar = document.getElementById('confidenceBar');
        const statusDot = document.getElementById('statusDot');
        const statusText = document.getElementById('statusText');
        const loadingElement = document.getElementById('loading');

        let lastLetter = '';
        let lastConfidence = 0;
        let detectionCount = 0;

        function onResults(results) {
            loadingElement.style.display = 'none';
            
            statusDot.className = 'dot active';
            statusText.textContent = 'Detecting';

            // Set canvas size
            if (canvasElement.width === 0) {
                canvasElement.width = videoElement.videoWidth || 640;
                canvasElement.height = videoElement.videoHeight || 480;
            }

            canvasCtx.clearRect(0, 0, canvasElement.width, canvasElement.height);

            if (results.multiHandLandmarks && results.multiHandLandmarks.length > 0) {
                const landmarks = results.multiHandLandmarks[0];
                
                // Draw hand landmarks
                drawConnectors(canvasCtx, landmarks);
                drawLandmarks(canvasCtx, landmarks);
                
                // Detect letter
                const result = detectLetter(landmarks);
                
                // Update UI
                letterElement.textContent = result.letter;
                const confPercent = Math.round(result.confidence * 100);
                confidenceElement.textContent = `Confidence: ${confPercent}%`;
                confidenceBar.style.width = `${confPercent}%`;
                
                // Send to React Native
                if (window.ReactNativeWebView) {
                    window.ReactNativeWebView.postMessage(JSON.stringify({
                        letter: result.letter,
                        confidence: result.confidence
                    }));
                }

                // Save for debouncing
                lastLetter = result.letter;
                lastConfidence = result.confidence;
                detectionCount++;
            } else {
                // No hand detected
                if (detectionCount > 0) {
                    letterElement.textContent = '✋';
                    confidenceElement.textContent = 'Confidence: 0%';
                    confidenceBar.style.width = '0%';
                    statusDot.className = 'dot waiting';
                    statusText.textContent = 'Show your hand';
                    detectionCount = 0;
                }
            }
        }

        function drawConnectors(ctx, landmarks) {
            const connections = [
                [0,1],[1,2],[2,3],[3,4],
                [0,5],[5,6],[6,7],[7,8],
                [5,9],[9,10],[10,11],[11,12],
                [9,13],[13,14],[14,15],[15,16],
                [13,17],[17,18],[18,19],[19,20],
                [0,17]
            ];
            
            const w = canvasElement.width;
            const h = canvasElement.height;
            
            ctx.strokeStyle = 'rgba(255, 215, 0, 0.4)';
            ctx.lineWidth = 2;
            
            connections.forEach(([i, j]) => {
                const p1 = landmarks[i];
                const p2 = landmarks[j];
                ctx.beginPath();
                ctx.moveTo(p1.x * w, p1.y * h);
                ctx.lineTo(p2.x * w, p2.y * h);
                ctx.stroke();
            });
        }

        function drawLandmarks(ctx, landmarks) {
            const w = canvasElement.width;
            const h = canvasElement.height;
            const fingertips = [4, 8, 12, 16, 20];
            
            landmarks.forEach((lm, i) => {
                const x = lm.x * w;
                const y = lm.y * h;
                const isFingertip = fingertips.includes(i);
                const radius = isFingertip ? 6 : 3;
                
                ctx.beginPath();
                ctx.arc(x, y, radius, 0, 2 * Math.PI);
                ctx.fillStyle = isFingertip ? '#FFD700' : '#0f3172';
                ctx.fill();
                
                if (isFingertip) {
                    ctx.strokeStyle = 'white';
                    ctx.lineWidth = 1.5;
                    ctx.stroke();
                }
            });
        }

        // Initialize MediaPipe
        const hands = new Hands({
            locateFile: (file) => {
                return `https://cdn.jsdelivr.net/npm/@mediapipe/hands/${file}`;
            }
        });

        hands.setOptions({
            maxNumHands: 1,
            modelComplexity: 1,
            minDetectionConfidence: 0.5,
            minTrackingConfidence: 0.5
        });

        hands.onResults(onResults);

        // Start camera
        const camera = new Camera(videoElement, {
            onFrame: async () => {
                await hands.send({image: videoElement});
            },
            width: 640,
            height: 480
        });

        camera.start().then(() => {
            statusText.textContent = 'Camera ready';
        }).catch(err => {
            console.error('Camera error:', err);
            loadingElement.innerHTML = `
                <div style="font-size:48px;">❌</div>
                <div style="font-size:18px;font-weight:600;margin-top:12px;">Camera Error</div>
                <div style="font-size:14px;color:#EF4444;margin-top:8px;">${err.message}</div>
                <div style="font-size:14px;color:#6B7280;margin-top:8px;">Please allow camera access and try again</div>
            `;
            statusDot.className = 'dot error';
            statusText.textContent = 'Error';
        });

        // Log status
        console.log('SENAS Gesture Recognition WebView loaded!');
    </script>
     @endverbatim
</body>
</html>