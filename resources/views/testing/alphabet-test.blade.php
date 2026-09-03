<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>SEÑAS — Model Testing & Evaluation Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --navy: #0f3172;
            --navy-dark: #0a1628;
            --navy-light: #1e428a;
            --gold: #FFD700;
            --teal: #4ECDC4;
            --green: #10B981;
            --red: #EF4444;
            --bg: #f4f6fb;
            --card: #ffffff;
            --border: #e2e8f0;
            --text: #0f172a;
            --text-dim: #64748b;
        }

        body {
            background: var(--bg);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            color: var(--text);
            min-height: 100vh;
        }

        /* ---------- Top bar ---------- */
        #topbar {
            background: var(--card);
            border-bottom: 1px solid var(--border);
            padding: 14px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 16px;
        }

        .brand-link {
            text-decoration: none;
            color: var(--navy);
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 18px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .brand-badge {
            background: rgba(15,49,114,0.08);
            color: var(--navy);
            font-size: 12px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 999px;
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        select, .btn {
            font-family: inherit;
            font-size: 13px;
        }

        select {
            padding: 8px 12px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: var(--card);
            color: var(--text);
            font-weight: 600;
            outline: none;
            cursor: pointer;
        }

        select:focus {
            border-color: var(--navy);
        }

        #progress-pill {
            background: #e0f2fe;
            color: #0369a1;
            padding: 8px 14px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-metrics {
            background: #10B981;
            color: white;
            padding: 8px 16px;
            border-radius: 10px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s ease;
        }
        .btn-metrics:hover { background: #059669; }

        .btn-dash {
            background: var(--bg);
            color: var(--text-dim);
            padding: 8px 14px;
            border-radius: 10px;
            border: 1px solid var(--border);
            text-decoration: none;
            font-weight: 600;
            font-size: 13px;
            transition: all 0.15s ease;
        }
        .btn-dash:hover { color: var(--navy); border-color: var(--navy); }

        /* ---------- Insecure / SSL Warning Banner ---------- */
        #origin-alert {
            display: none;
            background: #fef2f2;
            border-left: 4px solid #ef4444;
            padding: 12px 20px;
            margin: 16px 24px 0 24px;
            border-radius: 8px;
            color: #991b1b;
            font-size: 13px;
            line-height: 1.5;
        }
        #origin-alert a {
            color: #b91c1c;
            font-weight: 700;
            text-decoration: underline;
        }

        /* ---------- Layout ---------- */
        #layout {
            display: flex;
            gap: 20px;
            padding: 20px 24px;
            max-width: 1560px;
            margin: 0 auto;
        }

        #sidebar {
            width: 320px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .sidebar-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        #sign-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            max-height: calc(100vh - 220px);
            overflow-y: auto;
            padding-right: 4px;
        }

        .sign-btn {
            aspect-ratio: 1;
            border-radius: 12px;
            border: 2px solid var(--border);
            background: var(--card);
            font-size: 18px;
            font-weight: 800;
            color: var(--text);
            cursor: pointer;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 4px;
            padding: 6px;
            text-align: center;
            transition: all 0.15s ease;
            position: relative;
        }

        .sign-btn:hover {
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        .sign-btn .count {
            font-size: 10px;
            font-weight: 700;
            color: var(--text-dim);
            background: #f1f5f9;
            padding: 2px 6px;
            border-radius: 999px;
        }

        .sign-btn.selected {
            border-color: var(--navy);
            background: rgba(15,49,114,0.06);
            color: var(--navy);
            box-shadow: 0 0 0 2px rgba(15,49,114,0.2);
        }

        .sign-btn.has-trials .count { background: #dbeafe; color: #1e40af; }
        .sign-btn.done { border-color: var(--green); }
        .sign-btn.done .count { background: #dcfce7; color: #166534; }

        .sign-type-badge {
            font-size: 8px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            padding: 1px 4px;
            border-radius: 4px;
        }
        .sign-type-badge.static { background: #e0e7ff; color: #3730a3; }
        .sign-type-badge.dynamic { background: #fae8ff; color: #86198f; }

        /* ---------- Main panel ---------- */
        #main { flex: 1; display: flex; flex-direction: column; gap: 20px; min-width: 0; }

        .card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 20px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        #test-row { display: flex; gap: 20px; flex-wrap: wrap; }

        #reference-panel, #camera-panel { flex: 1; min-width: 320px; }

        .panel-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .panel-label {
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.6px;
            color: var(--text-dim);
            text-transform: uppercase;
        }

        #perform-label {
            font-size: 32px;
            font-weight: 800;
            color: var(--navy);
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        #reference-media-wrap {
            width: 100%;
            aspect-ratio: 4/3;
            background: #0f172a;
            border-radius: 14px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        #reference-media-wrap img, #reference-media-wrap video {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        #camera-wrap {
            position: relative;
            width: 100%;
            aspect-ratio: 4/3;
            background: var(--navy-dark);
            border-radius: 14px;
            overflow: hidden;
        }

        #video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }

        #overlay-canvas {
            position: absolute;
            top: 0; left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
        }

        #live-readout {
            position: absolute;
            top: 14px;
            left: 14px;
            background: rgba(10, 22, 40, 0.85);
            color: var(--gold);
            padding: 8px 16px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 16px;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,215,0,0.3);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        #camera-status-tag {
            position: absolute;
            top: 14px;
            right: 14px;
            background: rgba(0,0,0,0.6);
            color: #94a3b8;
            padding: 4px 10px;
            border-radius: 999px;
            font-size: 11px;
            font-weight: 600;
            backdrop-filter: blur(6px);
        }

        #countdown-overlay {
            position: absolute;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(10,22,40,0.65);
            font-size: 80px;
            font-weight: 900;
            color: var(--gold);
            text-shadow: 0 0 40px rgba(255,215,0,0.6);
            z-index: 10;
        }

        /* ---------- Controls ---------- */
        #controls {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-top: 16px;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            border: none;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn:disabled { opacity: 0.5; cursor: not-allowed; }

        #record-btn {
            background: var(--navy);
            color: #fff;
            font-size: 15px;
            padding: 14px 32px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 4px 12px rgba(15,49,114,0.25);
        }

        #record-btn:hover:not(:disabled) {
            background: var(--navy-light);
            transform: translateY(-1px);
        }

        #record-btn.recording {
            background: var(--red);
            animation: pulse 1s infinite alternate;
        }

        @keyframes pulse {
            from { opacity: 0.85; }
            to { opacity: 1; transform: scale(1.02); }
        }

        .btn-ghost {
            background: var(--bg);
            color: var(--text);
            border: 1px solid var(--border);
        }
        .btn-ghost:hover:not(:disabled) {
            background: #e2e8f0;
        }

        #status-line {
            font-size: 13px;
            color: var(--text-dim);
            margin-top: 12px;
            font-weight: 500;
        }

        #result-banner {
            display: none;
            margin-top: 14px;
            padding: 14px 18px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 14px;
        }

        #result-banner.correct { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
        #result-banner.incorrect { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* ---------- Trial log table ---------- */
        .table-wrap {
            overflow-x: auto;
            max-height: 280px;
            overflow-y: auto;
            margin-top: 10px;
        }

        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid var(--border); }
        th {
            background: #f8fafc;
            color: var(--text-dim);
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            position: sticky;
            top: 0;
        }
        td.correct { color: #166534; font-weight: 700; }
        td.incorrect { color: #991b1b; font-weight: 700; }

        /* ---------- Modal for Confusion Matrix & Metrics ---------- */
        #metrics-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 100;
            padding: 24px;
        }

        #metrics-content {
            background: var(--card);
            border-radius: 20px;
            width: 100%;
            max-width: 1100px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(0,0,0,0.25);
        }

        .modal-header {
            padding: 20px 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-header h2 {
            font-size: 20px;
            color: var(--navy);
            font-weight: 800;
        }

        .modal-close {
            background: none;
            border: none;
            font-size: 24px;
            color: var(--text-dim);
            cursor: pointer;
            padding: 4px 8px;
            border-radius: 8px;
        }
        .modal-close:hover { background: #f1f5f9; color: var(--text); }

        .modal-body {
            padding: 24px;
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 14px;
        }

        .stat-box {
            background: #f8fafc;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 16px;
        }

        .stat-box .num {
            font-size: 26px;
            font-weight: 800;
            color: var(--navy);
            margin-top: 4px;
        }

        .stat-box .desc {
            font-size: 11px;
            font-weight: 700;
            color: var(--text-dim);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .cm-table-wrap {
            overflow-x: auto;
            max-height: 340px;
            border: 1px solid var(--border);
            border-radius: 12px;
        }

        .cm-table {
            border-collapse: collapse;
            font-size: 12px;
            text-align: center;
        }
        .cm-table th, .cm-table td {
            padding: 6px 10px;
            border: 1px solid var(--border);
            min-width: 44px;
        }
        .cm-table th { background: #f1f5f9; font-weight: 700; }
        .cm-diagonal { background: #dcfce7; font-weight: 800; color: #166534; }
        .cm-off-diagonal { background: #fee2e2; color: #991b1b; }
    </style>
</head>

<body>
    <!-- Top Bar -->
    <div id="topbar">
        <div class="topbar-left">
            <a href="/admin/dashboard" class="brand-link">
                SEÑAS
                <span class="brand-badge">Evaluation Environment</span>
            </a>
        </div>
        <div class="topbar-right">
            <label style="font-size: 12px; font-weight: 700; color: var(--text-dim); margin-right: -4px;">Module:</label>
            <select id="module-select">
                <option value="alphabets" {{ ($currentModule ?? 'alphabets') === 'alphabets' ? 'selected' : '' }}>Alphabets (26 Letters)</option>
                <option value="numbers" {{ ($currentModule ?? '') === 'numbers' ? 'selected' : '' }}>Numbers 1-10 (10 Signs)</option>
                <option value="greetings" {{ ($currentModule ?? '') === 'greetings' ? 'selected' : '' }}>Greetings (5 Signs)</option>
                <option value="survival" {{ ($currentModule ?? '') === 'survival' ? 'selected' : '' }}>Survival Phrases (10 Signs)</option>
                <option value="all" {{ ($currentModule ?? '') === 'all' ? 'selected' : '' }}>All Signs (51 Total)</option>
            </select>

            <label style="font-size: 12px; font-weight: 700; color: var(--text-dim); margin-right: -4px;">Signer:</label>
            <select id="signer-select">
                <option value="researcher1">Researcher 1 — Danah</option>
                <option value="researcher2">Researcher 2 — Christian</option>
                <option value="researcher3">Researcher 3 — Theresa</option>
                <option value="participant1">Test Participant 1</option>
                <option value="participant2">Test Participant 2</option>
                <option value="participant3">Test Participant 3</option>
            </select>

            <div id="progress-pill">
                <span>🎯</span>
                <span id="progress-text">Loading signs…</span>
            </div>

            <button class="btn-metrics" id="open-metrics-btn">
                📊 Evaluation Metrics
            </button>

            <a href="/admin/dashboard" class="btn-dash">← Admin</a>
        </div>
    </div>

    <!-- Alert for Insecure Origin / Non-HTTPS context -->
    <div id="origin-alert">
        <b>⚠️ Camera Insecure Origin Warning:</b>
        <span id="origin-alert-msg"></span>
    </div>

    <!-- Main Layout -->
    <div id="layout">
        <!-- Sidebar -->
        <div id="sidebar">
            <div class="card">
                <div class="sidebar-header">
                    <div class="panel-label" id="sign-list-title">Signs List</div>
                    <span style="font-size: 11px; font-weight: 700; color: var(--text-dim);" id="sign-count-badge">0 Signs</span>
                </div>
                <div id="sign-grid"></div>
            </div>
        </div>

        <!-- Center / Main Panel -->
        <div id="main">
            <div class="card">
                <div id="test-row">
                    <!-- Target Sign Reference Panel -->
                    <div id="reference-panel">
                        <div class="panel-label-row">
                            <div class="panel-label">Target Sign to Perform</div>
                            <span id="sign-type-tag" class="sign-type-badge static">Static</span>
                        </div>
                        <div id="perform-label">
                            <span id="perform-sign-text">—</span>
                        </div>
                        <div id="reference-media-wrap">
                            <span style="color:#94a3b8">Loading reference…</span>
                        </div>
                    </div>

@php
    $engineMap = [
        'alphabets' => '/gesture.html',
        'numbers' => '/gesture_level3.html',
        'greetings' => '/gesture_greetings.html',
        'survival' => '/gesture_level2.html',
        'all' => '/gesture.html',
    ];
    $initialEngine = $engineMap[$currentModule ?? 'alphabets'] ?? '/gesture.html';
    $initialEngineName = basename($initialEngine);
@endphp
                    <!-- Camera & Real-time Live Prediction (Driven dynamically by respective gesture web view) -->
                    <div id="camera-panel">
                        <div class="panel-label-row">
                            <div class="panel-label">Live Camera & Detection Engine</div>
                            <div id="camera-status-tag" style="font-size:11px;font-weight:700;color:#10B981;">Engine Active ({{ $initialEngineName }})</div>
                        </div>
                        <div id="camera-wrap" style="position:relative;width:100%;aspect-ratio:4/3;background:#0a1628;border-radius:14px;overflow:hidden;border:1px solid var(--border);">
                            <iframe id="gesture-frame" src="{{ $initialEngine }}" allow="camera; microphone" style="width:100%;height:100%;border:none;display:block;"></iframe>
                            <div id="countdown-overlay">3</div>
                        </div>
                    </div>
                </div>

                <!-- Controls & Trigger -->
                <div id="controls">
                    <button class="btn" id="record-btn" disabled>Select a sign to begin</button>
                    <button class="btn btn-ghost" id="prev-btn">← Previous Sign</button>
                    <button class="btn btn-ghost" id="next-btn">Next Sign →</button>
                </div>

                <div id="status-line">Select a sign to begin testing.</div>
                <div id="result-banner"></div>
            </div>

            <!-- Selected Sign Trial Log -->
            <div class="card">
                <div class="panel-label-row">
                    <div class="panel-label">Recorded Trials for <span id="trial-log-sign-title" style="color:var(--navy);">—</span></div>
                    <span style="font-size: 11px; font-weight: 700; color: var(--text-dim);" id="trial-log-count">0 trials</span>
                </div>
                <div class="table-wrap">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Signer ID</th>
                                <th>Predicted</th>
                                <th>Confidence</th>
                                <th>Outcome</th>
                                <th>Response Latency</th>
                                <th>Timestamp</th>
                            </tr>
                        </thead>
                        <tbody id="trial-log-body">
                            <tr><td colspan="7" style="text-align:center;color:var(--text-dim);">No trials recorded yet for this sign.</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Evaluation Metrics & Confusion Matrix Modal -->
    <div id="metrics-modal">
        <div id="metrics-content">
            <div class="modal-header">
                <div>
                    <h2>Performance Evaluation & Confusion Matrix</h2>
                    <div style="font-size: 12px; color: var(--text-dim); margin-top: 2px;">
                        Calculated across all recorded test trials (Accuracy, Precision, Recall, F1-Score, Latency)
                    </div>
                </div>
                <div style="display:flex;align-items:center;gap:10px;">
                    <a id="download-csv-btn" href="/admin/api/testing/export-csv?module={{ $currentModule ?? 'alphabets' }}" class="btn btn-ghost" style="padding: 6px 14px; font-size: 12px; text-decoration: none;">
                        📥 Download Trials CSV
                    </a>
                    <button class="modal-close" id="close-metrics-btn">&times;</button>
                </div>
            </div>
            <div class="modal-body">
                <!-- High Level Stats -->
                <div class="stats-grid">
                    <div class="stat-box">
                        <div class="desc">Overall Accuracy</div>
                        <div class="num" id="metric-accuracy">—%</div>
                    </div>
                    <div class="stat-box">
                        <div class="desc">Macro F1-Score</div>
                        <div class="num" id="metric-f1">—%</div>
                    </div>
                    <div class="stat-box">
                        <div class="desc">Macro Precision / Recall</div>
                        <div class="num" id="metric-pr" style="font-size: 20px;">— / —</div>
                    </div>
                    <div class="stat-box">
                        <div class="desc">Avg Response Latency</div>
                        <div class="num" id="metric-latency" style="font-size: 20px;">— ms</div>
                    </div>
                    <div class="stat-box">
                        <div class="desc">Static vs Dynamic Latency</div>
                        <div class="num" id="metric-static-dyn-latency" style="font-size: 18px;">— / —</div>
                    </div>
                    <div class="stat-box">
                        <div class="desc">Total Test Trials</div>
                        <div class="num" id="metric-total-trials">0</div>
                    </div>
                </div>

                <!-- Per-Class Metrics Table -->
                <div>
                    <h3 style="font-size: 14px; font-weight: 700; color: var(--navy); margin-bottom: 8px;">Per-Class Evaluation (Precision, Recall, F1-Score)</h3>
                    <div class="table-wrap" style="max-height: 220px;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Sign Label</th>
                                    <th>Support (N)</th>
                                    <th>TP</th>
                                    <th>FP</th>
                                    <th>FN</th>
                                    <th>Precision</th>
                                    <th>Recall</th>
                                    <th>F1-Score</th>
                                </tr>
                            </thead>
                            <tbody id="per-class-tbody">
                                <tr><td colspan="8" style="text-align:center;color:var(--text-dim);">No data yet.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Confusion Matrix Grid -->
                <div>
                    <h3 style="font-size: 14px; font-weight: 700; color: var(--navy); margin-bottom: 8px;">Confusion Matrix (True Label [Rows] vs Predicted Label [Cols])</h3>
                    <div class="cm-table-wrap" id="confusion-matrix-wrap">
                        <div style="padding: 20px; text-align: center; color: var(--text-dim);">No data available yet.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ============================================================
        // DASHBOARD CONTROLLER — uses gesture.html as live AI engine
        // ============================================================
        const countdownOverlay = document.getElementById('countdown-overlay');
        const recordBtn = document.getElementById('record-btn');
        const prevBtn = document.getElementById('prev-btn');
        const nextBtn = document.getElementById('next-btn');
        const statusLine = document.getElementById('status-line');
        const resultBanner = document.getElementById('result-banner');
        const signerSelect = document.getElementById('signer-select');
        const moduleSelect = document.getElementById('module-select');
        const progressText = document.getElementById('progress-text');
        const signGrid = document.getElementById('sign-grid');
        const signListTitle = document.getElementById('sign-list-title');
        const signCountBadge = document.getElementById('sign-count-badge');
        const referenceMediaWrap = document.getElementById('reference-media-wrap');
        const performSignText = document.getElementById('perform-sign-text');
        const signTypeTag = document.getElementById('sign-type-tag');
        const cameraStatusTag = document.getElementById('camera-status-tag');
        const gestureFrame = document.getElementById('gesture-frame');
        const trialLogBody = document.getElementById('trial-log-body');
        const trialLogSignTitle = document.getElementById('trial-log-sign-title');
        const trialLogCount = document.getElementById('trial-log-count');

        const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;
        const INITIAL_SIGNS = @json($initialSigns);
        const CURRENT_MODULE = @json($currentModule ?? 'alphabets');

        const COUNTDOWN_STEPS = ['3', '2', '1', 'SIGN!'];

        // Label normalization mapping for numbers and formatting
        const NUMBER_WORD_MAP = {
            'ONE': '1', 'TWO': '2', 'THREE': '3', 'FOUR': '4', 'FIVE': '5',
            'SIX': '6', 'SEVEN': '7', 'EIGHT': '8', 'NINE': '9', 'TEN': '10'
        };

        function normalizeLabel(str) {
            if (!str || str === '✋' || str === '...') return '';
            let s = String(str).trim().replace(/[’‘`]/g, "'").toUpperCase();
            return NUMBER_WORD_MAP[s] || s;
        }

        function getEngineForSign(sign) {
            if (!sign) return '/gesture.html';
            const m = sign.module_name;
            if (m === 'level1_numbers') return '/gesture_level3.html';
            if (m === 'level2_greetings') return '/gesture_greetings.html';
            if (m === 'level3_survival') return '/gesture_level2.html';
            return '/gesture.html';
        }

        let signs = INITIAL_SIGNS || [];
        let currentSignIndex = 0;
        let latestLetter = '✋';
        let latestConfidence = 0;
        let latestLandmarks = null;

        let isCapturing = false;
        let captureStartTime = null;
        let firstFeedbackTime = null;
        let captureSamples = [];

        // Save & restore last-used signer
        const savedSigner = localStorage.getItem('senas_signer_id');
        if (savedSigner) signerSelect.value = savedSigner;
        signerSelect.addEventListener('change', () => {
            localStorage.setItem('senas_signer_id', signerSelect.value);
        });

        // Module select redirect
        moduleSelect.addEventListener('change', () => {
            window.location.href = `/admin/testing/alphabet?module=${moduleSelect.value}`;
        });

        // Listen for live detection stream from embedded web views
        window.addEventListener('message', (event) => {
            if (event.data && event.data.type === 'SENAS_DETECTION') {
                const { letter, confidence, landmarks, engine } = event.data;
                latestLetter = letter;
                latestConfidence = confidence;
                latestLandmarks = landmarks;

                if (cameraStatusTag) {
                    const engineName = engine || (gestureFrame ? gestureFrame.getAttribute('src').replace('/', '') : 'gesture.html');
                    cameraStatusTag.style.color = '#10B981';
                    cameraStatusTag.textContent = `Engine Active (${engineName})`;
                }

                if (isCapturing) {
                    if (firstFeedbackTime === null && letter && letter !== '✋' && letter !== '...') {
                        firstFeedbackTime = performance.now();
                    }
                    captureSamples.push({
                        letter: letter,
                        confidence: confidence,
                        landmarks: landmarks,
                        t: performance.now(),
                    });
                }
            }
        });

        function getSignLabel(s) {
            return s.sign_label || s.name || s.display_name || 'Sign';
        }

        function loadSigns() {
            signs = INITIAL_SIGNS;
            renderSignGrid();
            updateProgressPill();
            if (signs.length) {
                selectSign(0);
            } else {
                performSignText.textContent = 'No signs found';
                recordBtn.disabled = true;
            }
        }

        function updateProgressPill() {
            const done = signs.filter(s => s.trial_count >= 5).length;
            progressText.textContent = `${done}/${signs.length} signs at 5+ trials`;
            signCountBadge.textContent = `${signs.length} Signs`;
        }

        function renderSignGrid() {
            signGrid.innerHTML = '';
            signs.forEach((s, i) => {
                const label = getSignLabel(s);
                const isDynamic = s.sign_type === 'dynamic';
                const btn = document.createElement('button');
                btn.className = 'sign-btn' + 
                    (i === currentSignIndex ? ' selected' : '') + 
                    (s.trial_count > 0 ? ' has-trials' : '') + 
                    (s.trial_count >= 5 ? ' done' : '');
                
                btn.innerHTML = `
                    <span class="sign-type-badge ${isDynamic ? 'dynamic' : 'static'}">${isDynamic ? 'LSTM' : 'Static'}</span>
                    <span>${label}</span>
                    <span class="count">${s.trial_count || 0} trials</span>
                `;
                btn.onclick = () => selectSign(i);
                signGrid.appendChild(btn);
            });
        }

        async function selectSign(index) {
            currentSignIndex = index;
            renderSignGrid();
            const sign = signs[index];
            const label = getSignLabel(sign);
            const isDynamic = sign.sign_type === 'dynamic';

            // Dynamic recognition engine switching
            const targetEngine = getEngineForSign(sign);
            if (gestureFrame && gestureFrame.getAttribute('src') !== targetEngine) {
                gestureFrame.src = targetEngine;
                if (cameraStatusTag) {
                    cameraStatusTag.style.color = '#F59E0B';
                    cameraStatusTag.textContent = `Loading Engine (${targetEngine.replace('/', '')})...`;
                }
            }

            performSignText.textContent = label;
            trialLogSignTitle.textContent = label;
            signTypeTag.className = `sign-type-badge ${isDynamic ? 'dynamic' : 'static'}`;
            signTypeTag.textContent = isDynamic ? 'Dynamic (LSTM / Motion)' : 'Static (MediaPipe + MobileNet)';

            if (sign.reference_media_path) {
                referenceMediaWrap.innerHTML = sign.reference_is_video
                    ? `<video src="${sign.reference_media_path}" autoplay loop muted playsinline></video>`
                    : `<img src="${sign.reference_media_path}" alt="${label}">`;
            } else {
                referenceMediaWrap.innerHTML = `<div style="color:#94a3b8;font-size:14px;">No media preview available for ${label}</div>`;
            }

            recordBtn.disabled = false;
            recordBtn.textContent = `Record ${label}`;
            resultBanner.style.display = 'none';
            statusLine.textContent = isDynamic
                ? `Perform motion for "${label}" during 3s capture window.`
                : `Hold sign "${label}" steady after countdown (1.5s).`;

            await loadTrialLog(sign.gesture_id);
        }

        async function loadTrialLog(gestureId) {
            try {
                const res = await fetch(`/admin/api/testing/trials?gesture_id=${gestureId}`, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });
                if (!res.ok) {
                    console.error('Failed to load trial log:', res.status);
                    return;
                }
                const data = await res.json();
                trialLogBody.innerHTML = '';
                trialLogCount.textContent = `${data.trials.length} recorded`;

                if (data.trials.length === 0) {
                    trialLogBody.innerHTML = `<tr><td colspan="7" style="text-align:center;color:var(--text-dim);">No trials recorded yet for this sign. Click Record to test.</td></tr>`;
                    return;
                }

                data.trials.forEach(t => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td><strong>#${t.trial_number}</strong></td>
                        <td>${t.signer_id}</td>
                        <td><strong>${t.predicted_label ?? '—'}</strong></td>
                        <td>${t.confidence_score != null ? Math.round(t.confidence_score * 100) + '%' : '—'}</td>
                        <td class="${t.is_correct ? 'correct' : 'incorrect'}">${t.is_correct ? '✓ Correct' : '✗ Incorrect'}</td>
                        <td>${t.response_latency_ms != null ? t.response_latency_ms + ' ms' : '—'}</td>
                        <td>${new Date(t.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' })}</td>`;
                    trialLogBody.appendChild(tr);
                });
            } catch (e) {
                console.error('Error loading trial log:', e);
            }
        }

        prevBtn.onclick = () => { if (currentSignIndex > 0) selectSign(currentSignIndex - 1); };
        nextBtn.onclick = () => { if (currentSignIndex < signs.length - 1) selectSign(currentSignIndex + 1); };

        recordBtn.onclick = async () => {
            if (isCapturing) return;
            const sign = signs[currentSignIndex];
            const isDynamic = sign.sign_type === 'dynamic';
            const captureWindow = isDynamic ? 3000 : 1500;

            resultBanner.style.display = 'none';
            recordBtn.disabled = true;
            recordBtn.classList.add('recording');

            countdownOverlay.style.display = 'flex';
            for (const step of COUNTDOWN_STEPS) {
                countdownOverlay.textContent = step;
                await new Promise(r => setTimeout(r, 600));
            }
            countdownOverlay.style.display = 'none';

            // Begin capture window
            isCapturing = true;
            captureStartTime = performance.now();
            firstFeedbackTime = null;
            captureSamples = [];

            await new Promise(r => setTimeout(r, captureWindow));

            isCapturing = false;
            recordBtn.classList.remove('recording');
            finalizeTrial(captureWindow);
        };

        async function finalizeTrial(captureWindow = 1500) {
            const sign = signs[currentSignIndex];
            const label = getSignLabel(sign);
            const normTarget = normalizeLabel(label);

            if (captureSamples.length === 0) {
                statusLine.textContent = 'No hand detected during capture — please ensure hand is clearly visible in the camera frame.';
                recordBtn.disabled = false;
                return;
            }

            let finalSample;
            if (sign.sign_type === 'dynamic') {
                const dynamicMatches = captureSamples.filter(s => normalizeLabel(s.letter) === normTarget);
                if (dynamicMatches.length > 0) {
                    finalSample = dynamicMatches.reduce((prev, curr) => (curr.confidence > prev.confidence ? curr : prev));
                } else {
                    const validSamples = captureSamples.filter(s => s.letter && s.letter !== '✋' && s.letter !== '...');
                    finalSample = validSamples.length > 0 
                        ? validSamples.reduce((prev, curr) => (curr.confidence > prev.confidence ? curr : prev))
                        : captureSamples[captureSamples.length - 1];
                }
            } else {
                finalSample = captureSamples[captureSamples.length - 1];
            }

            const landmarkData = sign.sign_type === 'dynamic'
                ? captureSamples.map(s => s.landmarks).filter(Boolean)
                : (finalSample.landmarks || []);

            const latency = firstFeedbackTime ? Math.round(firstFeedbackTime - captureStartTime) : 50;
            const predNorm = normalizeLabel(finalSample.letter) || null;

            const payload = {
                gesture_id: sign.gesture_id,
                signer_id: signerSelect.value,
                landmark_data: landmarkData,
                predicted_label: predNorm,
                confidence_score: finalSample.confidence,
                response_latency_ms: latency,
                capture_started_at: new Date(Date.now() - captureWindow).toISOString(),
                feedback_received_at: new Date().toISOString(),
            };

            try {
                const res = await fetch('/admin/api/testing/trials', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': CSRF_TOKEN,
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                });

                if (!res.ok) {
                    const errText = await res.text();
                    console.error('Failed to save trial:', res.status, errText);
                    statusLine.textContent = `Failed to save trial (HTTP ${res.status}).`;
                    recordBtn.disabled = false;
                    return;
                }

                const saved = await res.json();
                const isCorrect = saved.trial.is_correct;
                const displayPred = saved.trial.predicted_label || predNorm || 'None';

                resultBanner.style.display = 'block';
                resultBanner.className = isCorrect ? 'correct' : 'incorrect';
                resultBanner.textContent = isCorrect
                    ? `✓ Correct! Predicted "${displayPred}" (${Math.round((finalSample.confidence || 0) * 100)}% conf, ${latency} ms latency)`
                    : `✗ Incorrect! Predicted "${displayPred}" (Expected "${label}")`;

                statusLine.textContent = `Trial ${saved.trial.trial_number} recorded for ${label}.`;

                sign.trial_count = (sign.trial_count || 0) + 1;
                renderSignGrid();
                updateProgressPill();
                await loadTrialLog(sign.gesture_id);
            } catch (err) {
                console.error('Trial save error:', err);
                statusLine.textContent = 'Network or server error while saving trial.';
            }

            recordBtn.disabled = false;
        }

        // ============================================================
        // EVALUATION METRICS & CONFUSION MATRIX MODAL
        // ============================================================
        const metricsModal = document.getElementById('metrics-modal');
        const openMetricsBtn = document.getElementById('open-metrics-btn');
        const closeMetricsBtn = document.getElementById('close-metrics-btn');

        openMetricsBtn.onclick = async () => {
            metricsModal.style.display = 'flex';
            await loadMetrics();
        };

        closeMetricsBtn.onclick = () => {
            metricsModal.style.display = 'none';
        };

        metricsModal.onclick = (e) => {
            if (e.target === metricsModal) metricsModal.style.display = 'none';
        };

        async function loadMetrics() {
            try {
                const res = await fetch(`/admin/api/testing/metrics?module=${CURRENT_MODULE}`);
                if (!res.ok) return;
                const data = await res.json();

                document.getElementById('metric-accuracy').textContent = `${data.accuracy}%`;
                document.getElementById('metric-f1').textContent = `${data.macro.f1}%`;
                document.getElementById('metric-pr').textContent = `${data.macro.precision}% / ${data.macro.recall}%`;
                document.getElementById('metric-latency').textContent = `${data.latency.overall_avg} ms`;
                document.getElementById('metric-static-dyn-latency').textContent = `${data.latency.static_avg} ms / ${data.latency.dynamic_avg} ms`;
                document.getElementById('metric-total-trials').textContent = data.total_trials;

                // Render Per-Class Table
                const perClassTbody = document.getElementById('per-class-tbody');
                perClassTbody.innerHTML = '';
                const keys = Object.keys(data.per_class);
                if (keys.length === 0) {
                    perClassTbody.innerHTML = `<tr><td colspan="8" style="text-align:center;color:var(--text-dim);">No trials recorded yet. Record some sign attempts to see metrics.</td></tr>`;
                } else {
                    keys.forEach(k => {
                        const row = data.per_class[k];
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><strong>${k}</strong></td>
                            <td>${row.support}</td>
                            <td>${row.tp}</td>
                            <td>${row.fp}</td>
                            <td>${row.fn}</td>
                            <td><strong>${row.precision}%</strong></td>
                            <td><strong>${row.recall}%</strong></td>
                            <td style="color:#10B981;font-weight:bold;">${row.f1}%</td>
                        `;
                        perClassTbody.appendChild(tr);
                    });
                }

                // Render Confusion Matrix
                const cmWrap = document.getElementById('confusion-matrix-wrap');
                const labels = data.confusion_matrix.labels;
                const matrix = data.confusion_matrix.matrix;

                if (labels.length === 0) {
                    cmWrap.innerHTML = `<div style="padding: 20px; text-align: center; color: var(--text-dim);">No test trials recorded yet.</div>`;
                } else {
                    let html = `<table class="cm-table"><thead><tr><th>True \\ Pred</th>`;
                    labels.forEach(l => { html += `<th>${l}</th>`; });
                    html += `<th>No Detect</th></tr></thead><tbody>`;

                    labels.forEach(trueL => {
                        html += `<tr><th>${trueL}</th>`;
                        labels.forEach(predL => {
                            const val = (matrix[trueL] && matrix[trueL][predL]) || 0;
                            const isDiag = trueL === predL;
                            const cls = isDiag && val > 0 ? 'cm-diagonal' : (val > 0 ? 'cm-off-diagonal' : '');
                            html += `<td class="${cls}">${val}</td>`;
                        });
                        const noDetVal = (matrix[trueL] && matrix[trueL]['No Detection']) || 0;
                        const noDetCls = noDetVal > 0 ? 'cm-off-diagonal' : '';
                        html += `<td class="${noDetCls}">${noDetVal}</td></tr>`;
                    });

                    html += `</tbody></table>`;
                    cmWrap.innerHTML = html;
                }
            } catch (err) {
                console.error('Failed to load metrics:', err);
            }
        }

        // Initialize page
        loadSigns();
    </script>
</body>

</html>