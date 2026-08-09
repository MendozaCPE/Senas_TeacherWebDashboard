<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover" />
  <title>SEÑAS · AI-Powered Filipino Sign Language Learning</title>
  <link rel="icon" type="image/png" href="{{ asset('images/senya_face.png') }}">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@500;600&display=swap" rel="stylesheet" />
  <style>
    /* ----- base & reset ----- */
    :root {
      --navy: #0B1E3D;
      --navy-soft: #122A50;
      --navy-mid: #1C3A68;
      --blue: #0f3d8b;
      --blue-deep: #153f94;
      --blue-light: #114aa5;
      --amber: #F5A623;
      --amber-deep: #D98A12;
      --teal: #2FBF9B;
      --teal-deep: #1E8F73;
      --paper: #F5F7FB;
      --paper-2: #EAEFF8;
      --ink: #0B1E3D;
      --ink-soft: rgba(11,30,61,.66);
      --ink-faint: rgba(11,30,61,.46);
      --glass-d: rgba(255,255,255,.09);
      --glass-d-brd: rgba(255,255,255,.18);
      --glass-l: rgba(255,255,255,.62);
      --glass-l-brd: rgba(255,255,255,.85);
      --shadow-lg: 0 30px 70px -22px rgba(11,20,45,.45);
      --shadow-sm: 0 12px 28px -14px rgba(11,20,45,.22);
      --shadow-xl: 0 40px 80px -24px rgba(11,20,45,.5);
      /* Refined palette for card tints */
      --primary-accent: #72383D;
      --dark-brown: #322D29;
      --deep-burgundy: #2C0E11;
      --muted-taupe: #9F8E87;
      --off-white: #F7F6F5;
    }
    * { margin:0; padding:0; box-sizing:border-box; }
    html { scroll-behavior:smooth; font-size:16px; }
    body { font-family:'Inter',sans-serif; background:var(--paper); color:var(--ink); overflow-x:hidden; -webkit-font-smoothing:antialiased; min-width:320px; }
    a { color:inherit; text-decoration:none; }
    button { font-family:inherit; cursor:pointer; }
    img { max-width:100%; height:auto; display:block; }
    .display { font-family:'Baloo 2',sans-serif; }
    .mono { font-family:'JetBrains Mono',monospace; }
    ::selection { background:var(--amber); color:var(--navy); }
    .wrap { max-width:1320px; margin:0 auto; padding:0 clamp(16px,5vw,64px); width:100%; }
    .reveal { opacity:0; transform:translateY(30px); transition:opacity .8s cubic-bezier(.16,1,.3,1),transform .8s cubic-bezier(.16,1,.3,1); }
    .reveal.visible { opacity:1; transform:translateY(0); }
    .stagger .reveal:nth-child(1) { transition-delay:0s; }
    .stagger .reveal:nth-child(2) { transition-delay:.08s; }
    .stagger .reveal:nth-child(3) { transition-delay:.16s; }
    .stagger .reveal:nth-child(4) { transition-delay:.24s; }
    .stagger .reveal:nth-child(5) { transition-delay:.32s; }
    .stagger .reveal:nth-child(6) { transition-delay:.4s; }
    @media (prefers-reduced-motion:reduce){ .reveal{opacity:1;transform:none;transition:none;} *{animation:none !important;} }

    /* ----- glass styles ----- */
    .glass-l { background:linear-gradient(165deg,rgba(255,255,255,.82),rgba(255,255,255,.55)); backdrop-filter:blur(18px) saturate(180%); -webkit-backdrop-filter:blur(18px) saturate(180%); border:1px solid var(--glass-l-brd); box-shadow:var(--shadow-sm),inset 0 1px 0 rgba(255,255,255,.6); }
    .glass-d { background:linear-gradient(165deg,rgba(255,255,255,.14),rgba(255,255,255,.05)); backdrop-filter:blur(20px) saturate(160%); -webkit-backdrop-filter:blur(20px) saturate(160%); border:1px solid var(--glass-d-brd); box-shadow:inset 0 1px 0 rgba(255,255,255,.16); }

    /* ----- animations ----- */
    @keyframes floatY { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-8px); } }
    @keyframes floatX { 0%,100% { transform:translateX(0); } 50% { transform:translateX(-6px); } }
    @keyframes dotPulse { 0%,100% { box-shadow:0 0 0 4px rgba(245,166,35,.25); } 50% { box-shadow:0 0 0 7px rgba(245,166,35,.08); } }
    @keyframes blobDrift { 0%,100% { transform:translate(0,0) scale(1); } 33% { transform:translate(25px,-30px) scale(1.08); } 66% { transform:translate(-20px,18px) scale(.94); } }
    @keyframes shimmerSweep { 0% { background-position:-200% 0; } 100% { background-position:200% 0; } }
    @keyframes pulseGlow { 0%,100% { opacity:.6; } 50% { opacity:1; } }
    @keyframes iconFloat { 0%,100% { transform:translateY(0) rotate(0deg); } 50% { transform:translateY(-5px) rotate(-3deg); } }
    @keyframes bulbGlow { 0%,100% { text-shadow:0 0 4px rgba(245,166,35,.4); } 50% { text-shadow:0 0 16px rgba(245,166,35,.95); } }
    @keyframes orbitFloat { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-10px); } }
    @keyframes heroFloat { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-8px); } }
    @keyframes shimmer { 0% { background-position:-200% center; } 100% { background-position:200% center; } }
    @keyframes scalePulse { 0%,100% { transform:scale(1); } 50% { transform:scale(1.02); } }
    @keyframes badgeHover { 0% { transform: translateY(0) scale(1); } 100% { transform: translateY(-6px) scale(1.02); } }
    @keyframes floatSoft { 0%,100% { transform:translateY(0); } 50% { transform:translateY(-10px); } }
    @keyframes badgeFadeIn { 0% { opacity:0; transform:translateY(8px); } 100% { opacity:1; transform:translateY(0); } }

    /* ===== NAV ===== */
    header.nav {
      position:fixed; top:0; left:0; right:0; z-index:200;
      padding:16px clamp(12px,3vw,64px) 12px;
      transition:padding .3s, background .3s;
      background:transparent;
      pointer-events: none;
    }
    header.nav * { pointer-events: auto; }
    .nav-inner {
      max-width:1320px; margin:0 auto;
      display:flex; align-items:center; justify-content:space-between; gap:10px;
      padding:6px 12px 6px 16px;
      border-radius:100px;
      background: rgba(255,255,255,.15);
      backdrop-filter: blur(18px) saturate(180%);
      -webkit-backdrop-filter: blur(18px) saturate(180%);
      border:1px solid rgba(255,255,255,.25);
      box-shadow: 0 18px 40px -18px rgba(11,20,45,.3), 0 0 0 1px rgba(255,255,255,.08) inset;
      transition: background .3s, box-shadow .3s, color .3s;
    }
    header.nav.scrolled .nav-inner {
      background: rgba(255,255,255,.85);
      border-color: rgba(255,255,255,.6);
      box-shadow: 0 18px 40px -18px rgba(11,20,45,.25), 0 0 0 1px rgba(255,255,255,.5) inset;
    }
    header.nav.scrolled .brand { color: var(--navy); }
    header.nav.scrolled .brand small { color: var(--ink-soft); }
    header.nav.scrolled .nav-links { color: var(--ink-soft); }
    header.nav.scrolled .nav-links a:hover,
    header.nav.scrolled .nav-links a.active { color: var(--navy); }
    header.nav.scrolled .nav-links a::after { background: var(--blue); }
    header.nav.scrolled .btn-ghost-d {
      background: rgba(11,30,61,.06);
      color: var(--navy);
      border-color: rgba(11,30,61,.15);
    }
    header.nav.scrolled .btn-ghost-d:hover { background: rgba(11,30,61,.12); }
    header.nav.scrolled .burger { color: var(--navy); }
    header.nav.scrolled .mobile-menu {
      background: rgba(255,255,255,.92);
      border-color: rgba(255,255,255,.4);
    }
    header.nav.scrolled .mobile-menu a { color: var(--ink-soft); }
    header.nav.scrolled .mobile-menu a:hover { background: rgba(11,30,61,.06); color: var(--navy); }

    .brand {
      display:flex; align-items:center; gap:10px;
      font-family:'Baloo 2',sans-serif; font-weight:800;
      font-size:clamp(1rem,2vw,1.3rem);
      color:#fff; letter-spacing:-.01em; min-width:0; flex-shrink:0;
      transition: color .3s;
    }
    .brand small {
      display:block; font-family:'Inter'; font-size:clamp(.45rem,.7vw,.6rem);
      font-weight:600; letter-spacing:.1em;
      color:rgba(255,255,255,.6); text-transform:uppercase;
      transition: color .3s;
    }
    .brand-mark {
      width:clamp(28px,3vw,34px); height:clamp(28px,3vw,34px);
      object-fit:contain; border-radius:9px; flex-shrink:0;
    }
    .nav-links {
      display:flex; align-items:center; gap:clamp(14px,2.5vw,28px);
      font-weight:600; font-size:clamp(.75rem,0.9vw,.92rem);
      color:rgba(255,255,255,.8);
      transition: color .3s;
    }
    .nav-links a {
      position:relative; padding:4px 0;
      transition:color .2s; white-space:nowrap;
    }
    .nav-links a:hover, .nav-links a.active { color:#fff; }
    .nav-links a::after {
      content:''; position:absolute; left:0; bottom:-3px;
      width:0; height:2px; background:var(--amber);
      transition:width .25s; border-radius:2px;
    }
    .nav-links a:hover::after, .nav-links a.active::after { width:100%; }
    .nav-actions {
      display:flex; align-items:center; gap:clamp(6px,1vw,10px);
      flex-shrink:0;
    }
    .btn {
      display:inline-flex; align-items:center; gap:6px;
      font-weight:700; font-size:clamp(.75rem,0.85vw,.9rem);
      cursor:pointer; border:none; border-radius:100px;
      padding:clamp(8px,1vw,11px) clamp(14px,2vw,22px);
      transition:transform .18s,box-shadow .18s,background .2s,color .2s;
      white-space:nowrap;
    }
    .btn-ghost-d {
      background:rgba(255,255,255,.12);
      color:#fff;
      border:1.5px solid rgba(255,255,255,.2);
      transition: background .3s, color .3s, border-color .3s;
    }
    .btn-ghost-d:hover { background:rgba(255,255,255,.22); transform:translateY(-1px); }
    .btn-amber {
      background:linear-gradient(135deg,var(--amber),var(--amber-deep));
      color:var(--navy);
      box-shadow:0 10px 24px -8px rgba(217,138,18,.55);
    }
    .btn-amber:hover { transform:translateY(-2px); box-shadow:0 16px 30px -8px rgba(217,138,18,.6); }
    .btn-lg { padding:clamp(12px,1.2vw,15px) clamp(20px,2.5vw,28px); font-size:clamp(.85rem,1vw,1rem); }
    .btn-white { background:#fff; color:var(--navy); box-shadow:0 10px 24px -8px rgba(0,0,0,.25); }
    .btn-white:hover { transform:translateY(-2px); box-shadow:0 16px 30px -8px rgba(0,0,0,.3); }
    .burger { display:none; background:none; border:none; font-size:clamp(1.1rem,1.5vw,1.3rem); cursor:pointer; color:#fff; padding:6px; transition: color .3s; }
    .mobile-menu {
      display:none; flex-direction:column; gap:2px;
      margin:10px clamp(12px,5vw,64px) 0;
      border-radius:24px; padding:14px;
      max-height:0; overflow:hidden;
      transition:max-height .3s ease;
      background:rgba(255,255,255,.12);
      backdrop-filter:blur(20px);
      -webkit-backdrop-filter:blur(20px);
      border:1px solid rgba(255,255,255,.15);
    }
    .mobile-menu.open { display:flex; max-height:600px; overflow-y:auto; }
    .mobile-menu a { padding:12px 14px; border-radius:14px; font-weight:600; color:rgba(255,255,255,.85); font-size:clamp(.9rem,1.2vw,1rem); }
    .mobile-menu a:hover { background:rgba(255,255,255,.08); }
    .mobile-menu .btn { margin-top:6px; justify-content:center; width:100%; }
    @media(max-width:900px){ .nav-links{display:none;} .burger{display:block;} .nav-actions .btn-ghost-d{display:none;} }
    @media(max-width:480px){ .nav-actions .btn-amber{padding:6px 12px;font-size:.75rem;} .brand{font-size:.95rem;gap:6px;} .brand small{font-size:.4rem;} }

    /* ===== HERO with darker blue gradient ===== */
    .hero {
      position:relative; min-height:100vh;
      background: radial-gradient(ellipse 900px 500px at 80% 10%, rgba(44, 80, 210, 0.35), transparent 60%),
                  radial-gradient(ellipse 700px 500px at 20% 90%, rgba(245,166,35,.10), transparent 55%),
                  linear-gradient(180deg, #2c50d2, #193072 92%);
      padding:clamp(80px,10vw,120px) 0 clamp(60px,8vw,100px);
      overflow:hidden; display:flex; align-items:center;
    }
    .hero::after {
      content:''; position:absolute; left:0; right:0; bottom:0;
      height:clamp(60px,10vw,120px);
      background:linear-gradient(180deg,transparent,#EDF1FA);
      pointer-events:none; z-index:1;
    }
    .hero-clouds {
      position:absolute;
      inset:0;
      z-index:0;
      pointer-events:none;
      opacity:0.35;
      background-image: url('{{ asset('images/senya_clouds.png') }}');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      mix-blend-mode: overlay;
    }
    @media (max-width: 768px) {
      .hero-clouds {
        background-size: contain;
        opacity:0.04;
      }
    }
    .hero-grid {
      display:grid; grid-template-columns:1fr 1fr;
      gap:clamp(30px,5vw,56px);
      align-items:center; position:relative; z-index:2;
    }
    .hero-content { max-width:600px; }
    .hero-badge {
      display:inline-flex; align-items:center; gap:10px;
      padding:6px 16px 6px 10px; border-radius:100px;
      font-size:clamp(.6rem,.7vw,.75rem); font-weight:700;
      letter-spacing:.05em; text-transform:uppercase;
      color:#fff; margin-bottom:clamp(16px,2vw,24px);
      background:rgba(255,255,255,.08);
      border:1px solid rgba(255,255,255,.1);
    }
    .hero-badge .dot {
      width:8px; height:8px; border-radius:50%;
      background:var(--amber);
      box-shadow:0 0 0 4px rgba(245,166,35,.25);
      flex-shrink:0; animation:dotPulse 2s ease-in-out infinite;
    }
    .hero h1 {
      font-family:'Baloo 2',sans-serif; font-weight:800;
      letter-spacing:-.01em;
      font-size:clamp(2rem,5vw,3.8rem);
      line-height:1.08; color:#fff;
      margin-bottom:clamp(16px,2vw,22px);
    }
    .hero h1 .accent {
      background:linear-gradient(135deg,var(--amber),#FFD700);
      -webkit-background-clip:text; -webkit-text-fill-color:transparent;
      background-clip:text;
    }
    .hero p.lead {
      font-size:clamp(.95rem,1.1vw,1.1rem);
      line-height:1.7; color:rgba(255,255,255,.7);
      max-width:500px; margin-bottom:clamp(24px,3vw,32px);
    }
    .hero-ctas { display:flex; gap:clamp(10px,1.5vw,14px); flex-wrap:wrap; margin-bottom:clamp(28px,3.5vw,40px); }
    .hero-stats { display:flex; gap:clamp(24px,3.5vw,40px); row-gap:18px; flex-wrap:wrap; }
    .hero-stats .stat-num {
      font-family:'Baloo 2',sans-serif; font-weight:800;
      font-size:clamp(1.4rem,1.8vw,1.8rem);
      color:#fff; letter-spacing:-.01em;
    }
    .hero-stats .stat-label { font-size:clamp(.7rem,.8vw,.8rem); color:rgba(255,255,255,.5); font-weight:600; margin-top:2px; }

    /* ===== HERO CAROUSEL – OUTER (position/scale) & INNER (tilt) ===== */
    .hero-carousel {
      position:relative;
      perspective:1400px;
      perspective-origin: 50% 50%;
      display:flex;
      justify-content:center;
      align-items:center;
      min-height:clamp(420px,52vw,620px);
      width:100%;
      transform-style:preserve-3d;
    }
    .hero-carousel-track {
      position:relative;
      width:100%;
      height:100%;
      display:flex;
      justify-content:center;
      align-items:center;
      transform-style:preserve-3d;
      animation: heroFloat 4s ease-in-out infinite;
    }
    .hero-carousel-item {
      position:absolute;
      border-radius:clamp(20px,2.5vw,28px);
      overflow:hidden;
      transform-style:preserve-3d;
      will-change:transform, opacity, filter;
      backface-visibility:visible;
      transition: none;
      cursor:pointer;
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(12px) saturate(180%);
      -webkit-backdrop-filter: blur(12px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.15);
    }
    .hero-carousel-item img {
      width:100%; height:100%; object-fit:contain; display:block;
      border-radius: inherit;
    }
    .hero-carousel-item.phone {
      aspect-ratio:9/19;
      width:clamp(180px,22vw,260px);
      border-radius:clamp(28px,3.5vw,42px);
      padding:clamp(8px,1vw,12px);
      background: rgba(255, 255, 255, 0.06);
      backdrop-filter: blur(16px) saturate(180%);
      -webkit-backdrop-filter: blur(16px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.12);
    }
    .hero-carousel-item.phone img {
      border-radius:clamp(18px,2.2vw,24px);
      background: #1a1a2e;
    }
    .hero-carousel-item.desktop {
      aspect-ratio:16/10;
      width:clamp(320px,40vw,480px);
      border-radius:clamp(16px,2vw,24px);
      padding:clamp(12px,1.5vw,18px);
      background: rgba(255, 255, 255, 0.06);
      backdrop-filter: blur(16px) saturate(180%);
      -webkit-backdrop-filter: blur(16px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.12);
    }
    .hero-carousel-item.desktop img {
      border-radius:clamp(8px,1vw,12px);
      background: #1a1a2e;
    }

    .carousel-inner {
      width:100%; height:100%;
      display:flex; align-items:center; justify-content:center;
      transform-style:preserve-3d;
      will-change:transform;
      border-radius:inherit;
      overflow:hidden;
      transition: transform 0.08s cubic-bezier(0.23, 1, 0.32, 1);
      perspective: 1200px;
      transform: rotateX(0deg) rotateY(0deg) translateZ(0px);
    }
    .carousel-inner img {
      width:100%; height:100%; object-fit:contain;
      border-radius:inherit;
      will-change:transform;
      backface-visibility:visible;
      transform: translateZ(0px);
    }
    .hero-carousel-item.active .carousel-inner {
      pointer-events: auto;
    }
    .hero-carousel-item:not(.active) .carousel-inner {
      pointer-events: none;
    }

    .hero-carousel-item.active {
      z-index:10;
      opacity:1;
      filter:blur(0px);
      pointer-events: auto;
      border: 1px solid rgba(255,255,255,.18);
    }
    .hero-carousel-item.active.phone {
      width:clamp(200px,24vw,280px);
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.15);
    }
    .hero-carousel-item.active.desktop {
      width:clamp(360px,44vw,520px);
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(20px) saturate(180%);
      -webkit-backdrop-filter: blur(20px) saturate(180%);
      border: 1px solid rgba(255, 255, 255, 0.15);
    }

    .hero-carousel-item.exiting {
      z-index:8;
      pointer-events: none;
      transition: transform 0.8s cubic-bezier(0.4, 0, 0.2, 1), 
                  opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1),
                  filter 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .hero-carousel-item.entering {
      z-index:9;
      pointer-events: auto;
      transition: transform 0.5s cubic-bezier(0.34, 1.2, 0.64, 1),
                  opacity 0.4s cubic-bezier(0.34, 1.2, 0.64, 1),
                  filter 0.4s cubic-bezier(0.34, 1.2, 0.64, 1);
    }
    .hero-carousel-item.behind {
      z-index:5;
      opacity:0.12;
      filter:blur(3px);
      pointer-events:none;
    }
    .hero-carousel-item.behind.phone {
      width:clamp(140px,16vw,190px);
    }
    .hero-carousel-item.behind.desktop {
      width:clamp(240px,30vw,360px);
    }
    .hero-carousel-item.behind-2 {
      z-index:2;
      opacity:0.04;
      filter:blur(6px);
      pointer-events:none;
    }
    .hero-carousel-item.behind-2.phone {
      width:clamp(100px,12vw,140px);
    }
    .hero-carousel-item.behind-2.desktop {
      width:clamp(160px,20vw,240px);
    }
    .hero-carousel-item.hidden {
      z-index:0;
      opacity:0;
      filter:blur(10px);
      pointer-events:none;
      transform: scale(0.3) translateZ(-200px);
    }

    .hero-float-badge {
      position:absolute;
      padding:clamp(8px,1vw,12px) clamp(12px,1.5vw,18px);
      border-radius:clamp(12px,1.5vw,16px);
      display:flex; align-items:center; gap:10px;
      font-size:clamp(.6rem,.7vw,.75rem); font-weight:700;
      z-index:5; color:#fff;
      background:rgba(255,255,255,.1);
      backdrop-filter:blur(12px);
      border:1px solid rgba(255,255,255,.12);
      pointer-events:none;
    }
    .hfb-1 { top:0%; right:-6%; animation:floatY 5s ease-in-out infinite; }
    .hfb-2 { bottom:2%; left:-8%; animation:floatY 6s ease-in-out infinite 1s; }
    .hfb-3 { top:22%; right:-12%; animation:floatY 7s ease-in-out infinite 2s; }

    @media(max-width:980px){
      .hero-grid{grid-template-columns:1fr;gap:40px;}
      .hero-carousel{order:-1;min-height:clamp(320px,45vw,400px);}
      .hero-carousel-item.active.phone{width:clamp(160px,22vw,200px);}
      .hero-carousel-item.active.desktop{width:clamp(260px,36vw,340px);}
      .hero-carousel-item.behind.phone{width:clamp(110px,16vw,150px);}
      .hero-carousel-item.behind.desktop{width:clamp(180px,26vw,240px);}
      .hero-carousel-item.behind-2.phone{width:clamp(80px,10vw,110px);}
      .hero-carousel-item.behind-2.desktop{width:clamp(120px,16vw,180px);}
      .hfb-1,.hfb-2,.hfb-3{display:none;}
      .hero-content{max-width:100%;text-align:center;}
      .hero p.lead{margin-left:auto;margin-right:auto;}
      .hero-ctas{justify-content:center;}
      .hero-stats{justify-content:center;}
    }
    @media(max-width:480px){
      .hero-carousel-item.active.phone{width:clamp(130px,36vw,160px);}
      .hero-carousel-item.active.desktop{width:clamp(200px,50vw,260px);}
      .hero-carousel-item.behind.phone{width:clamp(90px,24vw,120px);}
      .hero-carousel-item.behind.desktop{width:clamp(140px,38vw,190px);}
      .hero-carousel-item.behind-2.phone{width:clamp(60px,16vw,80px);}
      .hero-carousel-item.behind-2.desktop{width:clamp(100px,26vw,140px);}
      .hero-carousel{min-height:clamp(280px,55vw,340px);}
    }

    /* ===== SECTION SHARED ===== */
    section { padding:clamp(56px,8vw,100px) 0; position:relative; }
    .section-head { max-width:640px; margin-bottom:clamp(32px,4vw,52px); }
    .section-head.center { margin-left:auto; margin-right:auto; text-align:center; }
    .kicker {
      font-size:clamp(.6rem,.7vw,.76rem); font-weight:700;
      letter-spacing:.07em; text-transform:uppercase;
      color:var(--blue-deep); margin-bottom:clamp(10px,1.5vw,14px);
      display:block;
    }
    .section-head h2 {
      font-family:'Baloo 2',sans-serif; font-weight:800;
      letter-spacing:-.01em;
      font-size:clamp(1.6rem,3.4vw,2.6rem);
      line-height:1.16; color:var(--navy);
    }
    .section-head p { margin-top:clamp(10px,1.5vw,16px); font-size:clamp(.9rem,1.05vw,1.03rem); color:var(--ink-soft); line-height:1.65; }
    @media(max-width:640px){ section{padding:40px 0;} .section-head{margin-bottom:24px;} }

    /* ===== ABOUT – Enhanced ===== */
    #about {
      background: #EDF1FA;
      position: relative;
      overflow: hidden;
    }
    #about::before {
      content: '';
      position: absolute;
      top: -80px;
      right: -80px;
      width: 400px;
      height: 400px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(44,80,210,.06), transparent 70%);
      animation: blobDrift 20s ease-in-out infinite;
      pointer-events: none;
    }
    #about::after {
      content: '';
      position: absolute;
      bottom: -60px;
      left: -60px;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(245,166,35,.06), transparent 70%);
      animation: blobDrift 18s ease-in-out infinite reverse;
      pointer-events: none;
    }
    .about-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: clamp(40px,5vw,64px);
      align-items: center;
      position: relative;
      z-index: 1;
    }
    .about-copy {
      position: relative;
    }
    .about-copy .feature-highlight {
      display: flex;
      gap: 16px;
      margin-top: 20px;
      padding: 18px 22px;
      background: rgba(255,255,255,.7);
      backdrop-filter: blur(8px);
      border-radius: 16px;
      border: 1px solid rgba(255,255,255,.8);
      transition: transform .3s, box-shadow .3s;
    }
    .about-copy .feature-highlight:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-sm);
    }
    .about-copy .feature-highlight .icon {
      width: 46px;
      height: 46px;
      min-width: 46px;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: 1.1rem;
      background: linear-gradient(135deg, var(--blue), var(--blue-deep));
    }
    .about-copy .feature-highlight .icon.amber {
      background: linear-gradient(135deg, var(--amber), var(--amber-deep));
    }
    .about-copy .feature-highlight .text h4 {
      font-weight: 700;
      font-size: 1rem;
      color: var(--navy);
      margin-bottom: 2px;
    }
    .about-copy .feature-highlight .text p {
      font-size: .85rem;
      color: var(--ink-soft);
      margin: 0;
    }
    .about-visual {
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .about-visual .floating-badge {
      position: absolute;
      background: rgba(255,255,255,.92);
      backdrop-filter: blur(16px);
      border-radius: 14px;
      padding: 14px 18px;
      border: 1px solid rgba(255,255,255,.6);
      box-shadow: var(--shadow-sm);
      display: flex;
      align-items: center;
      gap: 12px;
      animation: floatY 6s ease-in-out infinite;
    }
    .about-visual .floating-badge.top-right {
      top: -10px;
      right: -20px;
      animation-delay: 0s;
    }
    .about-visual .floating-badge.bottom-left {
      bottom: -10px;
      left: -20px;
      animation-delay: 2s;
    }
    .about-visual .floating-badge .icon {
      font-size: 1.2rem;
      color: var(--amber);
    }
    .about-visual .floating-badge .label {
      font-weight: 600;
      font-size: .8rem;
      color: var(--navy);
    }
    .about-visual .floating-badge .sub {
      font-size: .65rem;
      color: var(--ink-faint);
    }
    .about-visual video {
      max-width: 100%;
      border-radius: clamp(20px,2.5vw,28px);
      box-shadow: var(--shadow-xl);
      transition: transform .4s ease;
      display: block;
      aspect-ratio: 16 / 9;
      object-fit: cover;
      width: 100%;
      height: auto;
      background: #1a1a2e;
    }
    .about-visual video:hover {
      transform: scale(1.02);
    }
    @media(max-width:720px){
      .about-grid{grid-template-columns:1fr;gap:30px;}
      .about-visual .floating-badge{display:none;}
    }

    /* ===== AI FEATURES – Enhanced with card tints ===== */
    #ai-features {
      background: linear-gradient(180deg, #EDF1FA, #FDF3E6);
      position: relative;
      overflow: hidden;
    }
    #ai-features::before {
      content: '';
      position: absolute;
      top: -140px;
      right: -90px;
      width: 380px;
      height: 380px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(44,80,210,.08), transparent 70%);
      filter: blur(6px);
      animation: blobDrift 18s ease-in-out infinite;
      pointer-events: none;
      z-index: 0;
    }
    #ai-features::after {
      content: '';
      position: absolute;
      bottom: -110px;
      left: -90px;
      width: 320px;
      height: 320px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(245,166,35,.08), transparent 70%);
      filter: blur(6px);
      animation: blobDrift 20s ease-in-out infinite reverse;
      pointer-events: none;
      z-index: 0;
    }
    .ai-feat-grid {
      display: grid;
      grid-template-columns: repeat(3,1fr);
      gap: clamp(20px,2.2vw,28px);
      position: relative;
      z-index: 1;
    }
    .ai-feat-card {
      --accent: var(--blue);
      padding: clamp(28px,3vw,36px) clamp(20px,2.5vw,28px);
      border-radius: clamp(20px,2.5vw,26px);
      display: flex;
      flex-direction: column;
      gap: clamp(12px,1.2vw,16px);
      position: relative;
      overflow: hidden;
      transition: transform .4s cubic-bezier(.16,1,.3,1), box-shadow .4s, backdrop-filter .3s, border-color .3s;
      cursor: default;
      background: rgba(255,255,255,.6);
      backdrop-filter: blur(12px) saturate(180%);
      -webkit-backdrop-filter: blur(12px) saturate(180%);
      border: 1px solid rgba(255,255,255,.7);
      box-shadow: var(--shadow-sm);
    }
    /* Individual card tints */
    .ai-feat-card.afc-1 { background: linear-gradient(145deg, rgba(247,246,245,0.7), rgba(159,142,135,0.12)); }
    .ai-feat-card.afc-2 { background: linear-gradient(145deg, rgba(247,246,245,0.7), rgba(47,191,155,0.10)); }
    .ai-feat-card.afc-3 { background: linear-gradient(145deg, rgba(247,246,245,0.7), rgba(245,166,35,0.10)); }
    .ai-feat-card.afc-4 { background: linear-gradient(145deg, rgba(247,246,245,0.7), rgba(11,30,61,0.08)); }
    .ai-feat-card.afc-5 { background: linear-gradient(145deg, rgba(247,246,245,0.7), rgba(124,58,237,0.10)); }
    .ai-feat-card.afc-6 { background: linear-gradient(145deg, rgba(247,246,245,0.7), rgba(30,143,115,0.10)); }

    .ai-feat-card::before {
      content: '';
      position: absolute;
      inset: 0;
      border-radius: inherit;
      padding: 1px;
      background: linear-gradient(135deg, rgba(255,255,255,.5), transparent);
      -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
      -webkit-mask-composite: xor;
      mask-composite: exclude;
      pointer-events: none;
    }
    .ai-feat-card:hover {
      transform: translateY(-12px) scale(1.01);
      box-shadow: var(--shadow-xl), 0 0 0 1px color-mix(in srgb, var(--accent) 25%, transparent);
      backdrop-filter: blur(18px) saturate(200%);
      -webkit-backdrop-filter: blur(18px) saturate(200%);
      border-color: rgba(255,255,255,.9);
    }
    .ai-feat-card .icon-wrap {
      width: clamp(48px,4.5vw,58px);
      height: clamp(48px,4.5vw,58px);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: clamp(1.2rem,1.4vw,1.4rem);
      color: #fff;
      transition: transform .4s, box-shadow .4s;
      animation: floatY 5s ease-in-out infinite;
    }
    .ai-feat-card:hover .icon-wrap {
      animation-play-state: paused;
      transform: scale(1.1) rotate(-6deg);
      box-shadow: 0 12px 28px -8px color-mix(in srgb, var(--accent) 40%, transparent);
    }
    .ai-feat-card h3 {
      font-family: 'Baloo 2', sans-serif;
      font-weight: 700;
      font-size: clamp(1.05rem,1.15vw,1.2rem);
      color: var(--navy);
    }
    .ai-feat-card p {
      font-size: clamp(.85rem,.9vw,.95rem);
      color: var(--ink-soft);
      line-height: 1.6;
      flex: 1;
    }
    .ai-feat-card .tag {
      font-size: clamp(.5rem,.6vw,.65rem);
      font-weight: 700;
      padding: 4px 14px;
      border-radius: 100px;
      font-family: 'JetBrains Mono', monospace;
      align-self: flex-start;
      background: rgba(11,30,61,.06);
      color: var(--ink-soft);
    }
    .afc-1{--accent:var(--blue);} .afc-1 .icon-wrap{background:linear-gradient(135deg,var(--blue),var(--blue-deep));} .afc-1 .tag{background:rgba(74,139,255,.12);color:var(--blue-deep);}
    .afc-2{--accent:var(--teal);} .afc-2 .icon-wrap{background:linear-gradient(135deg,var(--teal),var(--teal-deep));} .afc-2 .tag{background:rgba(47,191,155,.14);color:var(--teal-deep);}
    .afc-3{--accent:var(--amber);} .afc-3 .icon-wrap{background:linear-gradient(135deg,var(--amber),var(--amber-deep));} .afc-3 .tag{background:rgba(245,166,35,.18);color:var(--amber-deep);}
    .afc-4{--accent:var(--navy-mid);} .afc-4 .icon-wrap{background:linear-gradient(135deg,var(--navy-mid),var(--navy));} .afc-4 .tag{background:rgba(11,30,61,.08);color:var(--navy);}
    .afc-5{--accent:#7C3AED;} .afc-5 .icon-wrap{background:linear-gradient(135deg,#7C3AED,#6D28D9);} .afc-5 .tag{background:rgba(124,58,237,.12);color:#6D28D9;}
    .afc-6{--accent:var(--teal-deep);} .afc-6 .icon-wrap{background:linear-gradient(135deg,var(--teal-deep),var(--navy-mid));} .afc-6 .tag{background:rgba(30,143,115,.14);color:var(--teal-deep);}
    @media(max-width:980px){.ai-feat-grid{grid-template-columns:repeat(2,1fr);}}
    @media(max-width:620px){.ai-feat-grid{grid-template-columns:1fr;}}

    /* ===== SENYA TIP – Yellow gradient with senyaTip.png ===== */
    #senya-tip{background:#FDF3E6;}
    .tip-band {
      border-radius: clamp(24px,3vw,36px);
      padding: clamp(28px,5vw,56px);
      position: relative;
      overflow: hidden;
      background: linear-gradient(135deg, #f5d742, #f2c849, #eaa73d);
      color: #1a1a2e;
      box-shadow: var(--shadow-xl);
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: clamp(30px,4vw,50px);
      align-items: center;
    }
    .tip-band::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 88% 12%, rgba(255,255,255,.2), transparent 55%),
                  radial-gradient(circle at 12% 88%, rgba(255,215,0,.1), transparent 45%);
      pointer-events: none;
    }
    .tip-band::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(115deg, transparent 30%, rgba(255,255,255,.08) 50%, transparent 70%);
      background-size: 200% 100%;
      animation: shimmer 8s ease-in-out infinite;
      pointer-events: none;
    }
    .tip-content {
      position: relative;
      z-index: 1;
    }
    .tip-content .tip-top {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .tip-content .tip-top .tip-eyebrow {
      display: flex;
      align-items: center;
      gap: 14px;
      flex-wrap: wrap;
    }
    .tip-content .tip-top .tip-eyebrow .tip-star {
      width: clamp(40px,4vw,48px);
      height: clamp(40px,4vw,48px);
      flex-shrink: 0;
      object-fit: contain;
      border-radius: 50%;
      background: rgba(255,255,255,.3);
      padding: 6px;
      animation: floatY 4.5s ease-in-out infinite;
      border: 1px solid rgba(255,255,255,.2);
    }
    .tip-content .tip-top span.label {
      font-size: clamp(.65rem,.75vw,.8rem);
      font-weight: 700;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: rgba(10, 10, 50, 0.7);
    }
    .tip-content .tip-top .tip-nav {
      display: flex;
      gap: 8px;
    }
    .tip-content .tip-top .tip-nav button {
      width: clamp(32px,3vw,38px);
      height: clamp(32px,3vw,38px);
      border-radius: 50%;
      border: 1.5px solid rgba(0,0,0,.15);
      background: rgba(255,255,255,.2);
      color: #1a1a2e;
      cursor: pointer;
      transition: background .2s, transform .2s;
      display: flex;
      align-items: center;
      justify-content: center;
      backdrop-filter: blur(4px);
    }
    .tip-content .tip-top .tip-nav button:hover {
      background: rgba(255,255,255,.4);
      transform: scale(1.05);
    }
    .tip-content .tip-slides {
      position: relative;
      min-height: clamp(80px,10vw,110px);
    }
    .tip-content .tip-slide {
      display: none;
      position: relative;
    }
    .tip-content .tip-slide.active {
      display: block;
      animation: tipIn .5s ease;
    }
    @keyframes tipIn {
      from { opacity:0; transform:translateX(20px); }
      to { opacity:1; transform:translateX(0); }
    }
    .tip-content .tip-slide h3 {
      font-family: 'Baloo 2', sans-serif;
      font-size: clamp(1.2rem,1.6vw,1.6rem);
      font-weight: 700;
      margin-bottom: 8px;
      letter-spacing: -.01em;
      max-width: 540px;
      color: #1a1a2e;
    }
    .tip-content .tip-slide p {
      font-size: clamp(.95rem,1.05vw,1.08rem);
      color: rgba(20,20,60,.8);
      max-width: 500px;
      line-height: 1.7;
    }
    .tip-content .tip-dots {
      display: flex;
      gap: 8px;
      margin-top: 20px;
    }
    .tip-content .tip-dots span {
      width: 8px;
      height: 8px;
      border-radius: 50%;
      background: rgba(0,0,0,.2);
      cursor: pointer;
      transition: background .3s, width .3s, transform .3s;
    }
    .tip-content .tip-dots span.active {
      background: #1a1a2e;
      width: 24px;
      border-radius: 100px;
    }
    .tip-content .tip-dots span:hover {
      transform: scale(1.2);
    }
    .tip-content .btn {
      margin-top: 20px;
      position: relative;
      z-index: 1;
      background: rgba(26,26,46,.9);
      color: #fff;
      border: none;
      backdrop-filter: blur(8px);
      padding: clamp(10px,1vw,12px) clamp(20px,2vw,28px);
      font-weight: 700;
      border-radius: 100px;
      transition: transform .2s, box-shadow .2s;
    }
    .tip-content .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 12px 28px -8px rgba(0,0,0,.2);
    }
    .tip-band .tip-image {
      position: relative;
      z-index: 1;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .tip-band .tip-image img {
      max-width: 100%;
      max-height: 260px;
      object-fit: contain;
      border-radius: 20px;
      box-shadow: 0 20px 50px -12px rgba(0,0,0,.3);
      animation: floatY 5s ease-in-out infinite;
    }
    @media(max-width:780px){
      .tip-band{grid-template-columns:1fr;text-align:center;padding:clamp(24px,4vw,40px);}
      .tip-content .tip-slide h3{max-width:100%;}
      .tip-content .tip-slide p{max-width:100%;margin:0 auto;}
      .tip-content .tip-top{flex-direction:column;align-items:center;}
      .tip-content .tip-dots{justify-content:center;}
      .tip-content .btn{margin-left:auto;margin-right:auto;}
      .tip-band .tip-image img{max-height:180px;}
    }
    @media(max-width:480px){.tip-content .tip-slide h3{font-size:1.1rem;}.tip-content .tip-top{flex-direction:column;align-items:center;}}

    /* ============================================================ */
    /* ===== SENYA GUIDE – Uniform 3-card row with refined styles ===== */
    #senya-guide {
      background: linear-gradient(180deg, #FDF3E6, #EAF7F2);
      position: relative;
      overflow: hidden;
    }
    #senya-guide::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 800px;
      height: 800px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(44,80,210,.03), transparent 70%);
      pointer-events: none;
      z-index: 0;
    }
    .bento-guide {
      display: grid;
      grid-template-columns: 1fr 1fr 1fr;
      gap: clamp(20px,2.2vw,28px);
      position: relative;
      z-index: 1;
    }
    .bento-guide .guide-card {
      background: rgba(255,255,255,.5);
      backdrop-filter: blur(12px) saturate(180%);
      -webkit-backdrop-filter: blur(12px) saturate(180%);
      border: 1px solid rgba(255,255,255,.7);
      border-radius: clamp(20px,2.5vw,28px);
      padding: clamp(24px,2.8vw,32px);
      box-shadow: var(--shadow-sm);
      transition: transform 0.3s cubic-bezier(.16,1,.3,1), 
                  box-shadow 0.3s ease,
                  backdrop-filter 0.3s ease,
                  border-color 0.3s ease;
      display: flex;
      flex-direction: column;
      gap: 12px;
      height: 100%;
      min-height: 340px;
      position: relative;
      overflow: hidden;
    }
    .bento-guide .guide-card.card-learn {
      background: linear-gradient(145deg, rgba(247,246,245,0.7), rgba(159,142,135,0.15));
    }
    .bento-guide .guide-card.card-celebrate {
      background: linear-gradient(145deg, rgba(247,246,245,0.6), rgba(114,56,61,0.12));
    }
    .bento-guide .guide-card.card-bridge {
      background: linear-gradient(145deg, rgba(247,246,245,0.6), rgba(50,45,41,0.10));
    }
    .bento-guide .guide-card:hover {
      transform: translateY(-8px) scale(1.01);
      box-shadow: var(--shadow-xl), 0 0 0 1px rgba(255,255,255,.3);
      backdrop-filter: blur(18px) saturate(200%);
      -webkit-backdrop-filter: blur(18px) saturate(200%);
      border-color: rgba(255,255,255,.9);
    }
    .bento-guide .guide-card.card-learn:hover {
      border-color: rgba(159,142,135,0.5);
      box-shadow: var(--shadow-xl), 0 0 0 1px rgba(159,142,135,0.3);
    }
    .bento-guide .guide-card.card-celebrate:hover {
      border-color: rgba(114,56,61,0.5);
      box-shadow: var(--shadow-xl), 0 0 0 1px rgba(114,56,61,0.3);
    }
    .bento-guide .guide-card.card-bridge:hover {
      border-color: rgba(50,45,41,0.5);
      box-shadow: var(--shadow-xl), 0 0 0 1px rgba(50,45,41,0.3);
    }
    .bento-guide .guide-card .card-icon {
      width: clamp(44px,4vw,54px);
      height: clamp(44px,4vw,54px);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: clamp(1.1rem,1.3vw,1.4rem);
      color: #fff;
      background: linear-gradient(135deg, var(--blue), var(--blue-deep));
      flex-shrink: 0;
      transition: transform 0.3s;
    }
    .bento-guide .guide-card:hover .card-icon {
      transform: scale(1.08) rotate(-4deg);
    }
    .bento-guide .guide-card .card-icon.amber {
      background: linear-gradient(135deg, var(--amber), var(--amber-deep));
    }
    .bento-guide .guide-card .card-icon.teal {
      background: linear-gradient(135deg, var(--teal), var(--teal-deep));
    }
    .bento-guide .guide-card .card-icon.purple {
      background: linear-gradient(135deg, #7C3AED, #6D28D9);
    }
    .bento-guide .guide-card h3 {
      font-family: 'Baloo 2', sans-serif;
      font-size: clamp(1.1rem,1.3vw,1.4rem);
      font-weight: 700;
      color: var(--navy);
      margin: 0;
    }
    .bento-guide .guide-card p {
      font-size: clamp(.85rem,.9vw,.95rem);
      color: var(--ink-soft);
      line-height: 1.6;
      flex: 1;
      margin: 0;
    }
    .bento-guide .guide-card .badge-tag {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: clamp(.6rem,.7vw,.7rem);
      font-weight: 600;
      padding: 4px 14px 4px 10px;
      border-radius: 100px;
      background: rgba(11,30,61,.06);
      color: var(--ink-soft);
      align-self: flex-start;
      margin-top: 6px;
    }
    .bento-guide .guide-card .badge-tag i {
      font-size: .65rem;
      color: var(--amber);
    }
    .bento-guide .guide-card .badge-tag .check {
      color: var(--teal-deep);
    }
    .bento-guide .guide-card .card-media {
      border-radius: 14px;
      overflow: hidden;
      margin: 4px 0 8px;
      background: rgba(0,0,0,.04);
      box-shadow: var(--shadow-sm);
      width: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
      aspect-ratio: 16/10;
      min-height: 120px;
    }
    .bento-guide .guide-card .card-media img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      display: block;
      transition: transform 0.4s;
      background: transparent;
    }
    .bento-guide .guide-card:hover .card-media img {
      transform: scale(1.02);
    }
    @media(max-width:860px){
      .bento-guide {
        grid-template-columns: 1fr 1fr;
      }
      .bento-guide .guide-card:last-child {
        grid-column: span 2;
      }
    }
    @media(max-width:540px){
      .bento-guide {
        grid-template-columns: 1fr;
      }
      .bento-guide .guide-card:last-child {
        grid-column: span 1;
      }
      .bento-guide .guide-card {
        min-height: 280px;
      }
      .bento-guide .guide-card .card-media {
        aspect-ratio: 16/9;
        min-height: 100px;
      }
    }

    /* ============================================================ */
    /* ===== STUDENT BADGES – fixed layout ===== */
    #student-badges {
      background: #EAF7F2;
      position: relative;
      overflow: hidden;
    }
    #student-badges::before {
      content: '';
      position: absolute;
      top: -80px;
      left: -80px;
      width: 400px;
      height: 400px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(245,166,35,.06), transparent 70%);
      animation: blobDrift 20s ease-in-out infinite;
      pointer-events: none;
      z-index: 0;
    }
    .badges-two-col {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: clamp(30px,4vw,56px);
      align-items: center;
      position: relative;
      z-index: 1;
    }
    .badges-left {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 8px;
    }
    .badges-left .earn-badge-img {
      width: 100%;
      max-width: 340px; 
      border-radius: clamp(20px,2.5vw,30px);
      background: rgba(255,255,255,.5);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      border: 1px solid rgba(255,255,255,.8);
      box-shadow: var(--shadow-xl);
      padding: 10px;
      animation: floatSoft 5s ease-in-out infinite;
      transition: transform 0.4s;
    }
    .badges-left .earn-badge-img:hover {
      transform: scale(1.02);
    }
    .badges-right {
      display: flex;
      flex-direction: column;
      gap: 2rem;
      justify-content: space-between;
    }
    .badges-heading-zone {
      flex-shrink: 0;
    }
    .badges-heading {
      font-family: 'Baloo 2', sans-serif;
      font-weight: 800;
      font-size: clamp(1.3rem,2.4vw,2rem);
      color: var(--navy);
      letter-spacing: -.01em;
      line-height: 1.2;
      margin-bottom: 8px;
    }
    .badges-desc {
      font-size: clamp(.9rem,1vw,1.02rem);
      color: var(--ink-soft);
      line-height: 1.6;
      max-width: 480px;
    }
    .badge-slider-wrap {
      position: relative;
      width: 100%;
      max-width: 440px;
      min-height: 320px;
      height: clamp(320px, 38vw, 400px);
      perspective: 1000px;
      overflow: hidden;
      flex-shrink: 0;
      margin: 0 auto;
    }
    .badge-slider-track {
      position: relative;
      width: 100%;
      height: 100%;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .badge-slide {
      position: absolute;
      width: clamp(200px, 25vw, 280px);
      height: 100%;
      max-height: 100%;
      border-radius: clamp(16px,2vw,22px);
      transition: all 0.6s cubic-bezier(0.34, 1.2, 0.64, 1);
      opacity: 0;
      transform: translateX(60px) scale(0.85) rotateY(10deg);
      pointer-events: none;
      will-change: transform, opacity;
      display: flex;
      align-items: center;
      justify-content: center;
      box-sizing: border-box;
      background: transparent;
    }
    .badge-slide img {
      width: 100%;
      height: 100%;
      object-fit: contain;
      border-radius: inherit;
      display: block;
      max-height: 100%;
    }
    .badge-slide.active {
      opacity: 1;
      transform: translateX(0) scale(1) rotateY(0deg);
      z-index: 10;
      pointer-events: auto;
    }
    .badge-slide.prev {
      opacity: 0.4;
      transform: translateX(-50px) scale(0.75) rotateY(-12deg);
      z-index: 5;
      pointer-events: none;
    }
    .badge-slide.next {
      opacity: 0.4;
      transform: translateX(50px) scale(0.75) rotateY(12deg);
      z-index: 5;
      pointer-events: none;
    }
    .badge-slide.hidden-left {
      opacity: 0;
      transform: translateX(-120px) scale(0.5) rotateY(-20deg);
      z-index: 0;
      pointer-events: none;
    }
    .badge-slide.hidden-right {
      opacity: 0;
      transform: translateX(120px) scale(0.5) rotateY(20deg);
      z-index: 0;
      pointer-events: none;
    }
    .badge-slider-nav {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      width: 36px;
      height: 36px;
      border-radius: 50%;
      border: 1px solid rgba(255,255,255,.5);
      background: rgba(255,255,255,.7);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      color: var(--navy);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      transition: background .2s, transform .2s, box-shadow .2s;
      z-index: 20;
      font-size: .9rem;
      box-shadow: var(--shadow-sm);
    }
    .badge-slider-nav:hover {
      background: rgba(255,255,255,.95);
      transform: translateY(-50%) scale(1.08);
      box-shadow: var(--shadow-lg);
    }
    .badge-slider-nav.prev {
      left: -8px;
    }
    .badge-slider-nav.next {
      right: -8px;
    }
    .badge-info {
      text-align: center;
      min-height: 70px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: flex-start;
      gap: 4px;
      flex-shrink: 0;
      margin-top: 0.5rem;
    }
    .badge-info .badge-name {
      font-family: 'Baloo 2', sans-serif;
      font-weight: 700;
      font-size: clamp(1.1rem,1.4vw,1.4rem);
      color: var(--navy);
      transition: opacity 0.3s, transform 0.3s;
    }
    .badge-info .badge-desc {
      font-size: clamp(.8rem,.9rem,.95rem);
      color: var(--ink-soft);
      line-height: 1.5;
      max-width: 360px;
      transition: opacity 0.3s, transform 0.3s;
    }
    .badge-info .badge-name.fade,
    .badge-info .badge-desc.fade {
      opacity: 0;
      transform: translateY(6px);
    }
    .badge-info .badge-name.show,
    .badge-info .badge-desc.show {
      opacity: 1;
      transform: translateY(0);
    }
    @media(max-width:860px){
      .badges-two-col {
        grid-template-columns: 1fr;
        gap: 30px;
      }
      .badges-left .earn-badge-img {
        max-width: 280px;
        margin: 0 auto;
      }
      .badges-right {
        align-items: center;
        text-align: center;
        gap: 1.8rem;
      }
      .badges-desc {
        max-width: 100%;
        margin-left: auto;
        margin-right: auto;
      }
      .badge-slider-wrap {
        max-width: 360px;
        margin: 0 auto;
        min-height: 280px;
        height: clamp(280px, 48vw, 340px);
      }
      .badge-slide {
        width: clamp(160px, 26vw, 210px);
        height: 100%;
      }
    }
    @media(max-width:480px){
      .badge-slider-wrap {
        min-height: 230px;
        height: clamp(230px, 58vw, 280px);
        max-width: 280px;
      }
      .badge-slide {
        width: clamp(130px, 32vw, 170px);
        height: 100%;
      }
      .badge-slider-nav {
        width: 28px;
        height: 28px;
        font-size: .7rem;
      }
      .badge-slider-nav.prev { left: -4px; }
      .badge-slider-nav.next { right: -4px; }
      .badge-info {
        min-height: 60px;
      }
    }

    /* ===== TEACHER DASHBOARD – Enhanced ===== */
    #teacher-dash {
      background: linear-gradient(180deg, #EAF7F2, #F1EEFB);
      position: relative;
      overflow: hidden;
    }
    #teacher-dash::before {
      content: '';
      position: absolute;
      top: -100px;
      right: -100px;
      width: 400px;
      height: 400px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(47,191,155,.06), transparent 70%);
      animation: blobDrift 22s ease-in-out infinite;
      pointer-events: none;
    }
    .teacher-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: clamp(30px,5vw,56px);
      align-items: center;
      position: relative;
      z-index: 1;
    }
    .teacher-content .teacher-stats {
      display: grid;
      grid-template-columns: repeat(3,1fr);
      gap: 12px;
      margin: 20px 0 24px;
    }
    .teacher-content .teacher-stats .stat {
      background: rgba(255,255,255,.7);
      backdrop-filter: blur(8px);
      border-radius: 12px;
      padding: 14px 16px;
      text-align: center;
      border: 1px solid rgba(255,255,255,.6);
      transition: transform .3s, box-shadow .3s, backdrop-filter .3s;
    }
    .teacher-content .teacher-stats .stat:hover {
      transform: translateY(-4px);
      box-shadow: var(--shadow-sm);
      backdrop-filter: blur(12px);
    }
    .teacher-content .teacher-stats .stat .num {
      font-family: 'Baloo 2', sans-serif;
      font-size: 1.3rem;
      font-weight: 800;
      color: var(--blue-deep);
    }
    .teacher-content .teacher-stats .stat .label {
      font-size: .65rem;
      color: var(--ink-faint);
      font-weight: 600;
    }
    .teacher-content .feature-list {
      display: flex;
      flex-direction: column;
      gap: 12px;
      margin-top: 16px;
    }
    .teacher-content .feature-list .fl-item {
      display: flex;
      gap: 16px;
      align-items: flex-start;
      padding: 16px 20px;
      border-radius: 14px;
      background: rgba(255,255,255,.6);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      transition: background .3s, transform .3s, box-shadow .3s, backdrop-filter .3s;
      border: 1px solid rgba(255,255,255,.7);
      cursor: default;
    }
    .teacher-content .feature-list .fl-item:hover {
      background: rgba(255,255,255,.85);
      transform: translateX(6px);
      box-shadow: var(--shadow-sm);
      backdrop-filter: blur(12px);
    }
    .teacher-content .feature-list .fl-item i {
      width: clamp(36px,3vw,42px);
      height: clamp(36px,3vw,42px);
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: clamp(.8rem,.85vw,.9rem);
      flex-shrink: 0;
      background: linear-gradient(135deg, var(--blue), var(--blue-deep));
    }
    .teacher-content .feature-list .fl-item i.teal {
      background: linear-gradient(135deg, var(--teal), var(--teal-deep));
    }
    .teacher-content .feature-list .fl-item i.amber {
      background: linear-gradient(135deg, var(--amber), var(--amber-deep));
    }
    .teacher-content .feature-list .fl-item h5 {
      font-weight: 700;
      font-size: clamp(.9rem,.95vw,1rem);
      color: var(--navy);
      margin-bottom: 2px;
    }
    .teacher-content .feature-list .fl-item p {
      font-size: clamp(.78rem,.82rem,.88rem);
      color: var(--ink-soft);
      line-height: 1.5;
    }
    .teacher-dashboard-preview {
      border-radius: clamp(20px,2.5vw,28px);
      overflow: hidden;
      box-shadow: var(--shadow-xl);
      background: #fff;
      border: 1px solid rgba(255,255,255,.8);
      transition: transform .4s, box-shadow .4s;
    }
    .teacher-dashboard-preview:hover {
      transform: scale(1.01);
      box-shadow: var(--shadow-xl), 0 0 0 1px rgba(44,80,210,.1);
    }
    .teacher-dashboard-preview img {
      width: 100%;
      height: auto;
      display: block;
    }
    @media(max-width:780px){.teacher-grid{grid-template-columns:1fr;gap:30px;}.teacher-content .teacher-stats{grid-template-columns:repeat(3,1fr);}}

    /* ===== MOBILE APP – Enhanced ===== */
    #mobile-app {
      background: #F1EEFB;
      padding: clamp(56px,8vw,100px) 0;
      position: relative;
      overflow: hidden;
    }
    #mobile-app::before {
      content: '';
      position: absolute;
      bottom: -80px;
      right: -80px;
      width: 400px;
      height: 400px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(44,80,210,.06), transparent 70%);
      animation: blobDrift 19s ease-in-out infinite;
      pointer-events: none;
    }
    .mobile-app-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: clamp(40px,5vw,64px);
      align-items: center;
      margin-top: 32px;
      position: relative;
      z-index: 1;
    }
    .mobile-app-grid .screenshot-pair {
      display: flex;
      flex-direction: row;
      gap: clamp(30px,4vw,50px);
      justify-content: center;
      align-items: flex-start;
      flex-wrap: wrap;
    }
    .mobile-app-grid .app-screenshot-wrapper {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 14px;
      flex: 1;
      max-width: 260px;
    }
    .mobile-app-grid .app-screenshot-wrapper .caption {
      font-size: clamp(.75rem,.85vw,.9rem);
      font-weight: 600;
      color: var(--ink-soft);
      text-align: center;
    }
    .mobile-app-grid .app-screenshot {
      border-radius: clamp(28px,3.5vw,42px);
      padding: clamp(8px,1vw,12px);
      background: linear-gradient(145deg, #1a1a2e, #0f0f1a);
      box-shadow: 0 40px 80px -20px rgba(0,0,0,.7), inset 0 1px 0 rgba(255,255,255,.1);
      aspect-ratio: 9/19;
      width: 100%;
      max-width: 240px;
      margin: 0 auto;
      transition: transform .4s, box-shadow .4s;
    }
    .mobile-app-grid .app-screenshot:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 50px 100px -20px rgba(0,0,0,.8), inset 0 1px 0 rgba(255,255,255,.15);
    }
    .mobile-app-grid .app-screenshot img {
      border-radius: clamp(18px,2.2vw,24px);
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    .mobile-app-content {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    .mobile-app-content .app-title {
      font-family: 'Baloo 2', sans-serif;
      font-weight: 800;
      font-size: clamp(1.5rem,2.6vw,2.4rem);
      color: var(--navy);
      letter-spacing: -.01em;
      line-height: 1.2;
    }
    .mobile-app-content .app-title .highlight {
      background: linear-gradient(120deg, rgba(44,80,210,.12), rgba(44,80,210,.04));
      padding: 0 8px;
      border-radius: 6px;
    }
    .mobile-app-content .app-description {
      font-size: clamp(1rem,1.1vw,1.12rem);
      color: var(--ink-soft);
      line-height: 1.7;
    }
    .mobile-app-content .app-tagline {
      font-weight: 700;
      font-size: clamp(1.05rem,1.15vw,1.2rem);
      color: var(--navy);
      padding: 14px 28px;
      background: rgba(255,255,255,.7);
      backdrop-filter: blur(8px);
      border-radius: 100px;
      border: 1px solid rgba(255,255,255,.8);
      display: inline-block;
      box-shadow: var(--shadow-sm);
      align-self: flex-start;
      transition: transform .3s, box-shadow .3s;
    }
    .mobile-app-content .app-tagline:hover {
      transform: translateY(-2px);
      box-shadow: var(--shadow-lg);
    }
    .mobile-app-content .app-tagline i {
      color: var(--amber);
      margin-right: 8px;
    }
    .mobile-app-features {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 14px 24px;
      margin-top: 4px;
    }
    .mobile-app-features .feature-item {
      display: flex;
      align-items: flex-start;
      gap: 14px;
      padding: 14px 18px;
      background: rgba(255,255,255,.5);
      backdrop-filter: blur(6px);
      -webkit-backdrop-filter: blur(6px);
      border-radius: 14px;
      border: 1px solid rgba(255,255,255,.6);
      transition: background .3s, transform .3s, box-shadow .3s, backdrop-filter .3s;
    }
    .mobile-app-features .feature-item:hover {
      background: rgba(255,255,255,.85);
      transform: translateY(-3px);
      box-shadow: var(--shadow-sm);
      backdrop-filter: blur(12px);
    }
    .mobile-app-features .feature-item .fi-icon {
      width: 40px;
      height: 40px;
      min-width: 40px;
      border-radius: 11px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #fff;
      font-size: .9rem;
      background: linear-gradient(135deg, var(--blue), var(--blue-deep));
      flex-shrink: 0;
    }
    .mobile-app-features .feature-item .fi-icon.teal {
      background: linear-gradient(135deg, var(--teal), var(--teal-deep));
    }
    .mobile-app-features .feature-item .fi-icon.amber {
      background: linear-gradient(135deg, var(--amber), var(--amber-deep));
    }
    .mobile-app-features .feature-item .fi-icon.navy {
      background: linear-gradient(135deg, var(--navy-mid), var(--navy));
    }
    .mobile-app-features .feature-item .fi-text h4 {
      font-weight: 700;
      font-size: clamp(.85rem,.9vw,.95rem);
      color: var(--navy);
      margin-bottom: 2px;
    }
    .mobile-app-features .feature-item .fi-text p {
      font-size: clamp(.75rem,.8vw,.85rem);
      color: var(--ink-soft);
      line-height: 1.5;
    }
    .mobile-app-cta {
      margin-top: 8px;
    }
    @media(max-width:900px){
      .mobile-app-grid{grid-template-columns:1fr;gap:40px;}
      .mobile-app-grid .screenshot-pair{flex-direction:row;justify-content:center;}
      .mobile-app-grid .app-screenshot{max-width:200px;}
      .mobile-app-features{grid-template-columns:1fr;}
      .mobile-app-content .app-tagline{align-self:center;}
    }
    @media(max-width:480px){
      .mobile-app-grid .screenshot-pair{flex-direction:column;align-items:center;}
      .mobile-app-grid .app-screenshot{max-width:180px;}
    }

    /* ===== DEAF COMMUNITY – Enhanced ===== */
    #deaf-community {
      background: linear-gradient(180deg, #F1EEFB, #EDF1FA);
      position: relative;
      overflow: hidden;
    }
    #deaf-community::before {
      content: '';
      position: absolute;
      top: -100px;
      left: -80px;
      width: 340px;
      height: 340px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(47,191,155,.08), transparent 70%);
      filter: blur(6px);
      animation: blobDrift 17s ease-in-out infinite;
      pointer-events: none;
      z-index: 0;
    }
    #deaf-community .wrap { position: relative; z-index: 1; }
    .deaf-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: clamp(24px,3vw,40px);
    }
    .deaf-card {
      background: rgba(255,255,255,.7);
      backdrop-filter: blur(8px);
      -webkit-backdrop-filter: blur(8px);
      border-radius: clamp(20px,2.5vw,28px);
      padding: clamp(24px,3vw,32px);
      box-shadow: var(--shadow-sm);
      transition: transform .4s cubic-bezier(.16,1,.3,1), box-shadow .4s, backdrop-filter .3s;
      border: 1px solid rgba(255,255,255,.7);
      position: relative;
      overflow: hidden;
    }
    /* Individual card tints for Deaf Community */
    .deaf-card:nth-child(1) { background: linear-gradient(145deg, rgba(247,246,245,0.7), rgba(15,61,139,0.08)); }
    .deaf-card:nth-child(2) { background: linear-gradient(145deg, rgba(247,246,245,0.7), rgba(245,166,35,0.08)); }
    .deaf-card:nth-child(3) { background: linear-gradient(145deg, rgba(247,246,245,0.7), rgba(47,191,155,0.08)); }
    .deaf-card:nth-child(4) { background: linear-gradient(145deg, rgba(247,246,245,0.7), rgba(11,30,61,0.06)); }

    .deaf-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 3px;
      background: linear-gradient(90deg, var(--blue), var(--amber), var(--teal));
      opacity: 0;
      transition: opacity .4s;
    }
    .deaf-card:hover::before {
      opacity: 1;
    }
    .deaf-card:hover {
      transform: translateY(-8px);
      box-shadow: var(--shadow-xl);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      border-color: rgba(255,255,255,.9);
    }
    .deaf-card .icon-wrap {
      width: clamp(52px,5vw,62px);
      height: clamp(52px,5vw,62px);
      border-radius: 16px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: clamp(1.2rem,1.5vw,1.6rem);
      color: #fff;
      margin-bottom: 16px;
      transition: transform .4s;
      animation: iconFloat 5.5s ease-in-out infinite;
    }
    .deaf-card:hover .icon-wrap {
      animation-play-state: paused;
      transform: scale(1.1) rotate(-6deg);
    }
    .deaf-card .icon-wrap.blue { background: linear-gradient(135deg, var(--blue), var(--blue-deep)); }
    .deaf-card .icon-wrap.amber { background: linear-gradient(135deg, var(--amber), var(--amber-deep)); }
    .deaf-card .icon-wrap.teal { background: linear-gradient(135deg, var(--teal), var(--teal-deep)); }
    .deaf-card .icon-wrap.navy { background: linear-gradient(135deg, var(--navy-mid), var(--navy)); }
    .deaf-card h3 {
      font-family: 'Baloo 2', sans-serif;
      font-size: clamp(1.1rem,1.3vw,1.35rem);
      color: var(--navy);
      margin-bottom: 8px;
    }
    .deaf-card p {
      font-size: clamp(.85rem,.95vw,.95rem);
      color: var(--ink-soft);
      line-height: 1.7;
    }
    .deaf-card ul {
      list-style: none;
      margin-top: 14px;
    }
    .deaf-card ul li {
      padding: 6px 0;
      font-size: clamp(.82rem,.9vw,.9rem);
      color: var(--ink-soft);
      display: flex;
      align-items: flex-start;
      gap: 12px;
      border-bottom: 1px solid rgba(11,30,61,.04);
    }
    .deaf-card ul li:last-child { border-bottom: none; }
    .deaf-card ul li i {
      color: var(--teal-deep);
      margin-top: 3px;
      font-size: .8rem;
    }
    .deaf-tip-box {
      background: linear-gradient(135deg, #0B1E3D, #081426);
      border-radius: clamp(20px,2.5vw,28px);
      padding: clamp(28px,3vw,40px);
      color: #fff;
      margin-top: 40px;
      position: relative;
      overflow: hidden;
      border: 1px solid rgba(255,255,255,.06);
    }
    .deaf-tip-box::before {
      content: '';
      position: absolute;
      top: -50%;
      right: -20%;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background: radial-gradient(circle, rgba(245,166,35,.06), transparent 70%);
      pointer-events: none;
    }
    .deaf-tip-box h4 {
      font-family: 'Baloo 2', sans-serif;
      font-size: clamp(1.1rem,1.3vw,1.4rem);
      margin-bottom: 16px;
      position: relative;
      z-index: 1;
    }
    .bulb-glow {
      animation: bulbGlow 2.4s ease-in-out infinite;
    }
    .deaf-tip-box ul {
      list-style: none;
      position: relative;
      z-index: 1;
    }
    .deaf-tip-box ul li {
      padding: 8px 0;
      font-size: clamp(.85rem,.95vw,.95rem);
      color: rgba(255,255,255,.8);
      display: flex;
      align-items: flex-start;
      gap: 14px;
    }
    .deaf-tip-box ul li i {
      margin-top: 4px;
      color: var(--amber);
      font-size: .9rem;
      flex-shrink: 0;
    }
    @media(max-width:780px){.deaf-grid{grid-template-columns:1fr;}}

    /* ===== CTA – Enhanced ===== */
    #cta-section {
      background: linear-gradient(180deg, #EDF1FA, #FDF3E6);
      position: relative;
      overflow: hidden;
    }
    #cta-section .cta-clouds {
      position: absolute;
      inset: 0;
      z-index: 0;
      pointer-events: none;
      opacity: 0.35;
      background-image: url('{{ asset('images/senya_clouds.png') }}');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      mix-blend-mode: overlay;
    }
    @media (max-width: 768px) {
      #cta-section .cta-clouds {
        background-size: contain;
        opacity: 0.04;
      }
    }
    .cta-band {
      border-radius: clamp(24px,3vw,36px);
      padding: clamp(32px,7vw,76px) clamp(20px,6vw,56px);
      text-align: center;
      position: relative;
      overflow: hidden;
      background: linear-gradient(140deg, #0B1E3D, #193072, #2c50d2);
      color: #fff;
      box-shadow: var(--shadow-xl);
      z-index: 1;
    }
    .cta-band-clouds {
      position: absolute;
      inset: 0;
      z-index: 0;
      pointer-events: none;
      opacity: 0.2;
      background-image: url('{{ asset('images/senya_clouds.png') }}');
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
      mix-blend-mode: screen;
    }
    @media (max-width: 768px) {
      .cta-band-clouds {
        background-size: contain;
        opacity: 0.08;
      }
    }
    .cta-band::before {
      content: '';
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at 12% 15%, rgba(245,166,35,.12), transparent 50%),
                  radial-gradient(circle at 88% 85%, rgba(44,80,210,.15), transparent 50%);
      pointer-events: none;
    }
    .cta-band::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(115deg, transparent 30%, rgba(255,255,255,.06) 50%, transparent 70%);
      background-size: 200% 100%;
      animation: shimmer 10s ease-in-out infinite;
      pointer-events: none;
    }
    .cta-star {
      width: clamp(52px,5vw,68px);
      height: clamp(52px,5vw,68px);
      margin: 0 auto 20px;
      position: relative;
      object-fit: contain;
      filter: drop-shadow(0 8px 14px rgba(0,0,0,.35));
      animation: floatY 4.5s ease-in-out infinite;
      border-radius: 50%;
      background: rgba(255,255,255,.1);
      padding: 8px;
      border: 1px solid rgba(255,255,255,.1);
    }
    .cta-band h2 {
      position: relative;
      font-family: 'Baloo 2', sans-serif;
      font-size: clamp(1.5rem,3.8vw,2.6rem);
      font-weight: 800;
      margin-bottom: 14px;
      z-index: 1;
    }
    .cta-band p {
      position: relative;
      color: rgba(255,255,255,.7);
      max-width: 540px;
      margin: 0 auto 32px;
      font-size: clamp(.9rem,1.05vw,1.02rem);
      z-index: 1;
      line-height: 1.7;
    }
    .cta-band .hero-ctas {
      position: relative;
      justify-content: center;
      margin-bottom: 0;
      z-index: 1;
    }
    .cta-band .btn-amber {
      background: linear-gradient(135deg, var(--amber), var(--amber-deep));
      color: var(--navy);
      box-shadow: 0 12px 30px -8px rgba(217,138,18,.5);
    }
    .cta-band .btn-amber:hover {
      box-shadow: 0 18px 40px -8px rgba(217,138,18,.6);
    }
    .cta-band .btn-ghost-d {
      background: rgba(255,255,255,.1);
      border-color: rgba(255,255,255,.2);
      color: #fff;
    }
    .cta-band .btn-ghost-d:hover {
      background: rgba(255,255,255,.2);
    }

    /* ===== FOOTER (no icons) ===== */
    footer {
      padding: clamp(28px,3vw,40px) 0;
      background: #FDF3E6;
      border-top: 1px solid rgba(11,30,61,.08);
    }
    .foot-simple {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 16px;
      flex-wrap: wrap;
      font-size: clamp(.7rem,.85vw,.85rem);
      color: var(--ink-faint);
      font-weight: 500;
    }
    .foot-simple .left {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .foot-simple img {
      width: clamp(18px,1.8vw,22px);
      height: clamp(18px,1.8vw,22px);
      object-fit: contain;
      border-radius: 5px;
    }
    .foot-simple .right {
      display: flex;
      gap: 20px;
    }
    .foot-simple .right a {
      color: var(--ink-faint);
      transition: color .2s;
      text-decoration: none;
      font-weight: 500;
    }
    .foot-simple .right a:hover {
      color: var(--navy);
    }
    @media(max-width:600px){
      .foot-simple{flex-direction:column;text-align:center;gap:12px;}
      .foot-simple .right{flex-wrap:wrap;justify-content:center;}
    }
  </style>
</head>
<body>

<!-- ===== NAV ===== -->
<header class="nav" id="mainNav">
  <div class="nav-inner">
    <a href="#top" class="brand">
      <img class="brand-mark" src="{{ asset('images/senya_teaching.png') }}" alt="SEÑAS logo" onerror="this.onerror=null;this.src='{{ url('images/senya_teaching.png') }}';">
      <span>SEÑAS<small>Learn FSL with AI</small></span>
    </a>
    <nav class="nav-links" id="navLinks">
      <a href="#about" class="active">About</a>
      <a href="#ai-features">AI Features</a>
      <a href="#senya-tip">Senya Tip</a>
      <a href="#senya-guide">Guide</a>
      <a href="#teacher-dash">Dashboard</a>
      <a href="#mobile-app">Mobile App</a>
      <a href="#deaf-community">Community</a>
    </nav>
    <div class="nav-actions">
     <a href="{{ route('login') }}" class="btn btn-ghost-d">Log in</a>
<a href="{{ route('register') }}" class="btn btn-amber">Get Started</a>
      <button class="burger" id="burgerBtn" aria-label="Toggle menu"><i class="fa-solid fa-bars"></i></button>
    </div>
  </div>
  <div class="mobile-menu" id="mobileMenu">
    <a href="#about">About</a>
    <a href="#ai-features">AI Features</a>
    <a href="#senya-tip">Senya Tip</a>
    <a href="#senya-guide">Guide</a>
    <a href="#teacher-dash">Dashboard</a>
    <a href="#mobile-app">Mobile App</a>
    <a href="#deaf-community">Community</a>
   <a href="{{ route('login') }}" class="btn btn-ghost-d">Log in</a>
<a href="{{ route('register') }}" class="btn btn-amber">Get Started</a>
  </div>
</header>

<!-- ===== HERO ===== -->
<div id="top"></div>
<section class="hero">
  <div class="hero-clouds"></div>

  <div class="wrap hero-grid">
    <div class="hero-content reveal visible">
      <span class="hero-badge"><span class="dot"></span>AI-powered Filipino Sign Language platform</span>
      <h1>Learn FSL with <span class="accent">real-time AI</span> gesture recognition</h1>
      <p class="lead">SEÑAS uses artificial intelligence to recognize your hand gestures instantly, giving you real-time feedback as you learn Filipino Sign Language — anytime, anywhere.</p>
      <div class="hero-ctas">
      <a href="{{ route('login') }}" class="btn btn-amber btn-lg"><i class="fa-solid fa-download"></i> Download the App</a>
<a href="{{ route('login') }}" class="btn btn-white btn-lg"><i class="fa-solid fa-play"></i> See how it works</a>
      </div>
      <div class="hero-stats">
        <div><div class="stat-num" data-count="98">0</div><div class="stat-label">% gesture accuracy</div></div>
        <div><div class="stat-num" data-count="1200">0</div><div class="stat-label">Active learners</div></div>
        <div><div class="stat-num" data-count="150">0</div><div class="stat-label">FSL lessons</div></div>
      </div>
    </div>

    <div class="hero-carousel reveal visible" id="heroCarousel">
      <div class="hero-float-badge hfb-1"><i class="fa-solid fa-hand-sparkles"></i> Gesture detected</div>
      <div class="hero-float-badge hfb-2"><i class="fa-solid fa-bolt" style="color:var(--amber);"></i> 98% accuracy</div>
      <div class="hero-float-badge hfb-3"><i class="fa-solid fa-robot"></i> AI feedback live</div>

      <div class="hero-carousel-track" id="heroCarouselTrack">
        <div class="hero-carousel-item phone" data-type="phone" data-index="0">
          <div class="carousel-inner" data-tilt>
            <img src="{{ asset('images/studentDashboard.png') }}" alt="Student Dashboard" onerror="this.onerror=null;this.src='{{ url('images/studentDashboard.png') }}';">
          </div>
        </div>
        <div class="hero-carousel-item phone" data-type="phone" data-index="1">
          <div class="carousel-inner" data-tilt>
            <img src="{{ asset('images/studentPromote.png') }}" alt="Student Promote" onerror="this.onerror=null;this.src='{{ url('images/studentPromote.png') }}';">
          </div>
        </div>
        <div class="hero-carousel-item phone" data-type="phone" data-index="2">
          <div class="carousel-inner" data-tilt>
            <img src="{{ asset('images/studentGestures.png') }}" alt="Gesture Recognition" onerror="this.onerror=null;this.src='{{ url('images/studentGestures.png') }}';">
          </div>
        </div>
        <div class="hero-carousel-item desktop" data-type="desktop" data-index="3">
          <div class="carousel-inner" data-tilt>
            <img src="{{ asset('images/teacherDashboard.png') }}" alt="Teacher Dashboard" onerror="this.onerror=null;this.src='{{ url('images/teacherDashboard.png') }}';">
          </div>
        </div>
        <div class="hero-carousel-item desktop" data-type="desktop" data-index="4">
          <div class="carousel-inner" data-tilt>
            <img src="{{ asset('images/teacherLessons.png') }}" alt="Teacher Lessons" onerror="this.onerror=null;this.src='{{ url('images/teacherLessons.png') }}';">
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== ABOUT ===== -->
<section id="about">
  <div class="wrap about-grid">
    <div class="about-copy reveal">
      <span class="kicker">What is SEÑAS</span>
      <h2 class="display" style="font-size:clamp(1.4rem,3vw,2.2rem);font-weight:800;color:var(--navy);letter-spacing:-.01em;margin-bottom:20px;">The AI-powered ecosystem for learning Filipino Sign Language</h2>
      <p><strong>SEÑAS is an AI-powered Filipino Sign Language (FSL) learning ecosystem</strong> where the mobile application is the primary learning tool and the Teacher Dashboard serves as a companion platform for educators.</p>
      <p>Our real-time gesture recognition technology uses computer vision and machine learning to analyze hand movements, providing instant feedback on accuracy. This allows learners to practice independently while receiving the guidance they need to master FSL.</p>
      
      <div class="feature-highlight">
        <div class="icon"><i class="fa-solid fa-robot"></i></div>
        <div class="text">
          <h4>AI-Powered Feedback</h4>
          <p>Get instant, personalized feedback on every gesture you make</p>
        </div>
      </div>
      <div class="feature-highlight">
        <div class="icon amber"><i class="fa-solid fa-graduation-cap"></i></div>
        <div class="text">
          <h4>Structured Learning Path</h4>
          <p>Follow a carefully designed curriculum from beginner to advanced</p>
        </div>
      </div>
    </div>

    <div class="about-visual reveal">
      <video src="{{ asset('images/senya_animation.mp4') }}" alt="SEÑAS learning platform" onerror="this.onerror=null;this.src='{{ url('images/senya_waving.mp4') }}';" autoplay muted loop playsinline></video>
      <div class="floating-badge top-right">
        <div class="icon"><i class="fa-solid fa-star" style="color:var(--amber);"></i></div>
        <div>
          <div class="label">98% Accuracy</div>
          <div class="sub">AI recognition rate</div>
        </div>
      </div>
      <div class="floating-badge bottom-left">
        <div class="icon"><i class="fa-solid fa-users" style="color:var(--teal);"></i></div>
        <div>
          <div class="label">1,200+ Learners</div>
          <div class="sub">Active community</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== AI FEATURES ===== -->
<section id="ai-features">
  <div class="wrap">
    <div class="section-head reveal center">
      <span class="kicker">AI-Powered Learning</span>
      <h2>Real-time gesture recognition that feels like magic</h2>
      <p>Our AI model processes your hand gestures instantly, providing feedback that helps you improve with every practice session.</p>
    </div>
    <div class="ai-feat-grid stagger">
      <div class="ai-feat-card reveal afc-1">
        <div class="icon-wrap"><i class="fa-solid fa-hand-sparkles"></i></div>
        <h3>Real-time gesture tracking</h3>
        <p>Your camera captures every hand movement, and our AI analyzes it in milliseconds — giving you immediate feedback on your signing accuracy.</p>
        <span class="tag">Real-time</span>
      </div>
      <div class="ai-feat-card reveal afc-2">
        <div class="icon-wrap"><i class="fa-solid fa-chart-line"></i></div>
        <h3>Instant accuracy scoring</h3>
        <p>See your precision score after each gesture, with detailed breakdowns of what you did correctly and where you can improve.</p>
        <span class="tag">Precision</span>
      </div>
      <div class="ai-feat-card reveal afc-3">
        <div class="icon-wrap"><i class="fa-solid fa-brain"></i></div>
        <h3>Adaptive learning path</h3>
        <p>The AI learns your progress and suggests lessons tailored to your current skill level, helping you advance at your own pace.</p>
        <span class="tag">Adaptive</span>
      </div>
      <div class="ai-feat-card reveal afc-4">
        <div class="icon-wrap"><i class="fa-solid fa-layer-group"></i></div>
        <h3>Structured FSL curriculum</h3>
        <p>Follow a carefully designed curriculum that covers everything from basic alphabet to complex conversational signs.</p>
        <span class="tag">Curriculum</span>
      </div>
      <div class="ai-feat-card reveal afc-5">
        <div class="icon-wrap"><i class="fa-solid fa-robot"></i></div>
        <h3>AI coaching assistant</h3>
        <p>Senya, your AI learning companion, provides tips, encouragement, and personalized guidance throughout your learning journey.</p>
        <span class="tag">Assistant</span>
      </div>
      <div class="ai-feat-card reveal afc-6">
        <div class="icon-wrap"><i class="fa-solid fa-mobile-screen-button"></i></div>
        <h3>Learn anywhere, anytime</h3>
        <p>The mobile app brings gesture recognition to your pocket — practice FSL wherever you are, with or without an internet connection.</p>
        <span class="tag">Mobile-first</span>
      </div>
    </div>
  </div>
</section>

<!-- ===== SENYA TIP ===== -->
<section id="senya-tip">
  <div class="wrap">
    <div class="tip-band reveal">
      <div class="tip-content">
        <div class="tip-top">
          <div class="tip-eyebrow">
            <img class="tip-star" src="{{ asset('images/wavingSenya.png') }}" alt="Senya mascot" onerror="this.onerror=null;this.src='{{ url('images/wavingSenya.png') }}';">
            <span class="label">Senya Tip · Teacher Dashboard Tool</span>
          </div>
          <div class="tip-nav">
            <button id="tipPrev" aria-label="Previous tip"><i class="fa-solid fa-chevron-left"></i></button>
            <button id="tipNext" aria-label="Next tip"><i class="fa-solid fa-chevron-right"></i></button>
          </div>
        </div>
        <div class="tip-slides">
          <div class="tip-slide active">
            <h3>Monitor student progress in real-time</h3>
            <p>Senya Tip provides educators with real-time insights into each student's learning journey. Track gesture accuracy, lesson completion rates, and identify areas where students need extra support.</p>
          </div>
          <div class="tip-slide">
            <h3>AI-powered classroom insights</h3>
            <p>Get AI-generated recommendations for each student based on their performance patterns. Senya Tip helps you personalize your teaching approach and intervene early when students struggle.</p>
          </div>
          <div class="tip-slide">
            <h3>Streamline lesson management</h3>
            <p>Create, organize, and assign lessons directly from the Teacher Dashboard. Senya Tip makes it easy to track which lessons are most effective and which students need additional practice materials.</p>
          </div>
        </div>
        <div class="tip-dots" id="tipDots">
          <span class="active" data-i="0"></span><span data-i="1"></span><span data-i="2"></span>
        </div>
<a href="{{ route('login') }}" class="btn"><i class="fa-solid fa-chalkboard-user"></i> Explore Teacher Dashboard</a>
      </div>
      <div class="tip-image">
        <img src="{{ asset('images/senyaTip.png') }}" alt="Senya Tip" onerror="this.onerror=null;this.src='{{ url('images/senyaTip.png') }}';">
      </div>
    </div>
  </div>
</section>

<!-- ===== SENYA GUIDE – Refined 3-card row ===== -->
<section id="senya-guide">
  <div class="wrap">
    <div class="section-head reveal center" style="max-width:100%;">
      <span class="kicker">Meet Senya</span>
      <h2>Your AI learning companion throughout the journey</h2>
      <p>Senya appears at key moments to guide you, celebrate your progress, and make learning FSL feel more human.</p>
    </div>

    <div class="bento-guide stagger">
      <div class="guide-card card-learn reveal">
        <div class="card-icon amber"><i class="fa-solid fa-star"></i></div>
        <h3>Learning together, every step of the way</h3>
        <p>Senya isn't just a mascot — it's your AI learning companion that appears throughout your journey, offering encouragement and celebrating your wins.</p>
        <div class="card-media">
          <img src="{{ asset('images/senya_student.png') }}" alt="Senya learning companion" onerror="this.onerror=null;this.src='{{ url('images/senya_student.png') }}';">
        </div>
        <div class="badge-tag"><i class="fa-regular fa-heart check"></i> Personalized encouragement</div>
      </div>

      <div class="guide-card card-celebrate reveal">
        <div class="card-icon teal"><i class="fa-solid fa-trophy"></i></div>
        <h3>Celebrating Milestones</h3>
        <p>From your first correct sign to completing a full lesson, Senya celebrates your achievements and keeps you motivated.</p>
        <div class="card-media">
          <img src="{{ asset('images/senya_award.png') }}" alt="Senya award" onerror="this.onerror=null;this.src='{{ url('images/senya_award.png') }}';">
        </div>
        <div class="badge-tag"><i class="fa-regular fa-circle-check check"></i> Earn badges</div>
      </div>

      <div class="guide-card card-bridge reveal">
        <div class="card-icon purple"><i class="fa-solid fa-flag"></i></div>
        <h3>Bridging Communication</h3>
        <p>Join thousands of learners discovering the beauty of FSL and helping create a more inclusive Philippines.</p>
        <div class="card-media">
          <img src="{{ asset('images/senya_waving_flag.png') }}" alt="Senya flag" onerror="this.onerror=null;this.src='{{ url('images/senya_waving_flag.png') }}';">
        </div>
        <div class="badge-tag"><i class="fa-regular fa-circle-check check"></i> Inclusive learning</div>
      </div>
    </div>
  </div>
</section>

<!-- ===== TEACHER DASHBOARD ===== -->
<section id="teacher-dash">
  <div class="wrap teacher-grid">
    <div class="teacher-content reveal">
      <span class="kicker">For Educators</span>
      <h2 class="display" style="font-size:clamp(1.4rem,3vw,2.2rem);font-weight:800;color:var(--navy);letter-spacing:-.01em;margin-bottom:12px;">The Teacher Dashboard: Your companion platform</h2>
      <p style="font-size:clamp(.9rem,1vw,1.02rem);color:var(--ink-soft);line-height:1.7;">While the mobile app is where learning happens, the Teacher Dashboard gives educators the tools to monitor progress, manage lessons, and support every student's journey.</p>
      
      <div class="teacher-stats">
        <div class="stat"><div class="num">95%</div><div class="label">Student engagement</div></div>
        <div class="stat"><div class="num">120+</div><div class="label">Active teachers</div></div>
        <div class="stat"><div class="num">4.8★</div><div class="label">Teacher rating</div></div>
      </div>

      <div class="feature-list">
        <div class="fl-item">
          <i class="fa-solid fa-users"></i>
          <div><h5>Student progress monitoring</h5><p>See detailed analytics on each student's performance, accuracy trends, and completed lessons.</p></div>
        </div>
        <div class="fl-item">
          <i class="fa-solid fa-book-open teal"></i>
          <div><h5>Lesson management</h5><p>Create, upload, and organize learning materials for your students with ease.</p></div>
        </div>
        <div class="fl-item">
          <i class="fa-solid fa-chart-simple amber"></i>
          <div><h5>Performance insights</h5><p>Identify students who need extra support and celebrate those who are excelling.</p></div>
        </div>
      </div>
    </div>

    <div class="reveal">
      <div class="teacher-dashboard-preview">
        <img src="{{ asset('images/teacherDashboard.png') }}" alt="Teacher Dashboard" onerror="this.onerror=null;this.src='{{ url('images/teacherDashboard.png') }}';">
      </div>
    </div>
  </div>
</section>

<!-- ===== MOBILE APP ===== -->
<section id="mobile-app">
  <div class="wrap">
    <div class="section-head reveal center" style="max-width:720px;margin-left:auto;margin-right:auto;">
      <span class="kicker">Mobile App Experience</span>
      <h2>Learn Filipino Sign Language with AI-powered interactive lessons</h2>
    </div>

    <div class="mobile-app-grid reveal">
      <div class="screenshot-pair">
        <div class="app-screenshot-wrapper">
          <div class="app-screenshot">
            <img src="{{ asset('images/studentDashboard.png') }}" alt="Student Dashboard" onerror="this.onerror=null;this.src='{{ url('images/studentDashboard.png') }}';">
          </div>
          <div class="caption">Dashboard &amp; Progress</div>
        </div>
        <div class="app-screenshot-wrapper">
          <div class="app-screenshot">
            <img src="{{ asset('images/studentGestures.png') }}" alt="Gesture Recognition" onerror="this.onerror=null;this.src='{{ url('images/studentGestures.png') }}';">
          </div>
          <div class="caption">AI Gesture Recognition</div>
        </div>
      </div>

      <div class="mobile-app-content">
        <h3 class="app-title">
          <span class="highlight">Learn. Practice. Recognize. Progress.</span><br>
          All in one AI-powered app.
        </h3>

        <p class="app-description">
          Master Filipino Sign Language through structured lessons, real-time AI gesture recognition, instant feedback, engaging quizzes, and progress tracking—all within one intuitive mobile application designed to make learning accessible, interactive, and enjoyable.
        </p>

        <div class="app-tagline">
          <i class="fa-solid fa-rocket"></i> Start signing with confidence today
        </div>

        <div class="mobile-app-features">
          <div class="feature-item">
            <div class="fi-icon"><i class="fa-solid fa-hand-sparkles"></i></div>
            <div class="fi-text">
              <h4>AI Gesture Recognition</h4>
              <p>Practice FSL signs and receive real-time AI-powered feedback to improve your accuracy and confidence.</p>
            </div>
          </div>
          <div class="feature-item">
            <div class="fi-icon teal"><i class="fa-solid fa-book-open"></i></div>
            <div class="fi-text">
              <h4>Interactive Lessons</h4>
              <p>Follow structured, beginner-friendly lessons that guide you through Filipino Sign Language step by step.</p>
            </div>
          </div>
          <div class="feature-item">
            <div class="fi-icon amber"><i class="fa-solid fa-puzzle-piece"></i></div>
            <div class="fi-text">
              <h4>Practice &amp; Quizzes</h4>
              <p>Strengthen your skills with interactive exercises and quizzes designed to reinforce every lesson.</p>
            </div>
          </div>
          <div class="feature-item">
            <div class="fi-icon navy"><i class="fa-solid fa-chart-simple"></i></div>
            <div class="fi-text">
              <h4>Track Your Progress</h4>
              <p>Monitor completed lessons, achievements, and your learning journey as you continue improving your FSL skills.</p>
            </div>
          </div>
        </div>

        <div class="mobile-app-cta">
        <a href="{{ route('login') }}" class="btn btn-amber btn-lg"><i class="fa-solid fa-download"></i> Download the App — It's Free</a>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== STUDENT BADGES ===== -->
<section id="student-badges">
  <div class="wrap">
    <div class="section-head reveal center" style="max-width:720px;margin-left:auto;margin-right:auto;">
      <span class="kicker">Gamification &amp; Achievements</span>
      <h2>Earn badges as you complete lessons and milestones</h2>
      <p>Every step of your learning journey is rewarded. Collect badges, track your progress, and celebrate your growth in Filipino Sign Language.</p>
    </div>

    <div class="badges-two-col reveal">
      <div class="badges-left">
        <img class="earn-badge-img" src="{{ asset('images/studentEarnBadges.png') }}" alt="Earn Badges" onerror="this.onerror=null;this.src='{{ url('images/studentEarnBadges.png') }}';">
      </div>

      <div class="badges-right">
        <div class="badges-heading-zone">
          <h3 class="badges-heading">Collect Achievement Badges</h3>
          <p class="badges-desc">Complete lessons and milestones to unlock badges that celebrate your Filipino Sign Language learning journey.</p>
        </div>

        <div class="badge-slider-wrap" id="badgeSliderWrap">
          <div class="badge-slider-track" id="badgeSliderTrack">
          </div>
          <button class="badge-slider-nav prev" id="badgePrev" aria-label="Previous badge"><i class="fa-solid fa-chevron-left"></i></button>
          <button class="badge-slider-nav next" id="badgeNext" aria-label="Next badge"><i class="fa-solid fa-chevron-right"></i></button>
        </div>

        <div class="badge-info" id="badgeInfo">
          <div class="badge-name show" id="badgeName">First Steps</div>
          <div class="badge-desc show" id="badgeDesc">Awarded for completing your very first FSL lesson.</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ===== DEAF COMMUNITY ===== -->
<section id="deaf-community">
  <div class="wrap">
    <div class="section-head reveal center">
      <span class="kicker">Understanding the Deaf Community</span>
      <h2>Deafness as Culture, Not Just a Condition</h2>
      <p>For many Deaf people, being Deaf is not simply about not hearing. It is about belonging to a community that communicates differently and shares a rich cultural identity.</p>
    </div>

    <div class="deaf-grid">
      <div class="deaf-card reveal">
        <div class="icon-wrap blue"><i class="fa-solid fa-people-group"></i></div>
        <h3>What is the Deaf Community?</h3>
        <p>The Deaf community in the Philippines is a group of people who share a common experience of being Deaf and often connect through Filipino Sign Language (FSL), visual communication, and Deaf culture.</p>
        <ul>
          <li><i class="fa-solid fa-check"></i> Visual communication is central</li>
          <li><i class="fa-solid fa-check"></i> Strong community connections</li>
          <li><i class="fa-solid fa-check"></i> Shared identity and culture</li>
        </ul>
      </div>

      <div class="deaf-card reveal">
        <div class="icon-wrap amber"><i class="fa-solid fa-hands"></i></div>
        <h3>Filipino Sign Language (FSL)</h3>
        <p>Filipino Sign Language is the natural language used by many Deaf Filipinos. FSL has its own grammar rules, sentence structure, expressions, and cultural meanings.</p>
        <ul>
          <li><i class="fa-solid fa-check"></i> Visual grammar and syntax</li>
          <li><i class="fa-solid fa-check"></i> Expressive facial movements</li>
          <li><i class="fa-solid fa-check"></i> Recognized national sign language</li>
        </ul>
      </div>

      <div class="deaf-card reveal">
        <div class="icon-wrap teal"><i class="fa-solid fa-school"></i></div>
        <h3>Daily Life and Experiences</h3>
        <p>Many Deaf Filipinos experience the same things hearing people do: they study, work, build families, use technology, and create communities.</p>
        <ul>
          <li><i class="fa-solid fa-check"></i> Education challenges</li>
          <li><i class="fa-solid fa-check"></i> Communication barriers</li>
          <li><i class="fa-solid fa-check"></i> Accessibility is the key</li>
        </ul>
      </div>

      <div class="deaf-card reveal">
        <div class="icon-wrap navy"><i class="fa-solid fa-hand-holding-heart"></i></div>
        <h3>Deaf Community Values</h3>
        <p>Important values commonly seen in Deaf communities include a strong sense of community, visual awareness, and independence.</p>
        <ul>
          <li><i class="fa-solid fa-check"></i> Strong community bonds</li>
          <li><i class="fa-solid fa-check"></i> Visual awareness and skills</li>
          <li><i class="fa-solid fa-check"></i> Independence and capability</li>
        </ul>
      </div>
    </div>

    <div class="deaf-tip-box reveal">
      <h4><i class="fa-solid fa-lightbulb bulb-glow" style="color:var(--amber);margin-right:10px;"></i> Things Hearing People Should Understand</h4>
      <ul>
        <li><i class="fa-solid fa-xmark" style="color:#ef4444;"></i> Avoid thinking: "Deaf people cannot communicate."</li>
        <li><i class="fa-solid fa-check" style="color:var(--amber);"></i> Better understanding: "Deaf people communicate differently."</li>
        <li><i class="fa-solid fa-xmark" style="color:#ef4444;"></i> Avoid shouting when talking to a Deaf person.</li>
        <li><i class="fa-solid fa-check" style="color:var(--amber);"></i> Get their attention visually, face them, and use writing or gestures if needed.</li>
        <li><i class="fa-solid fa-xmark" style="color:#ef4444;"></i> Avoid assuming all Deaf people can read lips.</li>
        <li><i class="fa-solid fa-check" style="color:var(--amber);"></i> Lip reading is difficult and only some Deaf people use it effectively.</li>
      </ul>
      <p style="margin-top:16px;color:rgba(255,255,255,.7);font-size:clamp(.85rem,.95vw,.95rem);">
        Deaf people are not defined by what they cannot hear. They are a community with their own language, culture, experiences, and ways of connecting with the world.
      </p>
    </div>
  </div>
</section>

<!-- ===== CTA ===== -->
<section id="cta-section">
  <div class="cta-clouds"></div>
  <div class="wrap">
    <div class="cta-band reveal">
      <div class="cta-band-clouds"></div>
      <img class="cta-star" src="{{ asset('images/wavingSenya.png') }}" alt="Senya mascot" onerror="this.onerror=null;this.src='{{ url('images/wavingSenya.png') }}';">
      <h2>Start learning Filipino Sign Language today</h2>
      <p>Download the SEÑAS app and experience AI-powered gesture recognition that makes learning FSL accessible, engaging, and fun.</p>
  <div class="hero-ctas">
    <a href="{{ route('register') }}" class="btn btn-amber btn-lg"><i class="fa-solid fa-download"></i> Download App for Students</a>
    <a href="{{ route('register') }}" class="btn btn-ghost-d btn-lg"><i class="fa-solid fa-graduation-cap"></i> Sign up as a Teacher</a>
</div>
    </div>
  </div>
</section>

<!-- ===== FOOTER ===== -->
<footer>
  <div class="wrap">
    <div class="foot-simple">
      <div class="left">
        <img src="{{ asset('images/senya_teaching.png') }}" alt="SEÑAS logo" onerror="this.onerror=null;this.src='{{ url('images/senya_teaching.png') }}';">
        <span>&copy; 2026 SEÑAS. All rights reserved.</span>
      </div>
    </div>
  </div>
</footer>

<script>
  (function(){
    if ('scrollRestoration' in history) {
      history.scrollRestoration = 'manual';
    }
    window.scrollTo({ top: 0, behavior: 'instant' });

    const revealEls = document.querySelectorAll('.reveal');
    const revealIO = new IntersectionObserver((entries)=>{
      entries.forEach(e=>{ if(e.isIntersecting) e.target.classList.add('visible'); });
    },{threshold:.12, rootMargin:'0px 0px -40px 0px'});
    revealEls.forEach(el=>revealIO.observe(el));

    const nav = document.getElementById('mainNav');
    const heroSection = document.querySelector('.hero');
    window.addEventListener('scroll', ()=>{
      const scrollY = window.scrollY;
      const heroBottom = heroSection ? heroSection.offsetTop + heroSection.offsetHeight : 600;
      if (scrollY > 80 || scrollY > heroBottom - 100) {
        nav.classList.add('scrolled');
      } else {
        nav.classList.remove('scrolled');
      }
    });

    const burger = document.getElementById('burgerBtn');
    const menu = document.getElementById('mobileMenu');
    burger.addEventListener('click', ()=>{
      menu.classList.toggle('open');
      burger.innerHTML = menu.classList.contains('open') ? '<i class="fa-solid fa-xmark"></i>' : '<i class="fa-solid fa-bars"></i>';
    });
    menu.querySelectorAll('a').forEach(a=>a.addEventListener('click', ()=>{
      menu.classList.remove('open');
      burger.innerHTML = '<i class="fa-solid fa-bars"></i>';
    }));

    const navLinks = document.querySelectorAll('#navLinks a');
    const sections = [...navLinks].map(a=>document.querySelector(a.getAttribute('href'))).filter(Boolean);
    const spyIO = new IntersectionObserver((entries)=>{
      entries.forEach(e=>{
        if(e.isIntersecting){
          navLinks.forEach(a=>a.classList.remove('active'));
          const match = document.querySelector(`#navLinks a[href="#${e.target.id}"]`);
          if(match) match.classList.add('active');
        }
      });
    },{rootMargin:'-45% 0px -50% 0px'});
    sections.forEach(s=>spyIO.observe(s));

    const counters = document.querySelectorAll('.stat-num');
    const countIO = new IntersectionObserver((entries)=>{
      entries.forEach(entry=>{
        if(entry.isIntersecting){
          const el = entry.target;
          const target = parseInt(el.dataset.count,10);
          let cur = 0;
          const step = Math.max(1, Math.round(target/45));
          const t = setInterval(()=>{
            cur += step;
            if(cur >= target){ cur = target; clearInterval(t); }
            el.textContent = cur;
          }, 22);
          countIO.unobserve(el);
        }
      });
    },{threshold:.5});
    counters.forEach(c=>countIO.observe(c));

    // Hero carousel (simplified for brevity – same as before)
    const items = document.querySelectorAll('.hero-carousel-item');
    const total = items.length;
    let activeIndex = 0;
    let isTransitioning = false;
    let transitionTimer = null;
    const itemTypes = [];
    items.forEach((item, i) => {
      itemTypes.push(item.dataset.type || 'phone');
    });

    function positionItems(activeIdx, animate = true) {
      const totalItems = items.length;
      const radius = 380;
      items.forEach((item, i) => {
        let offset = (i - activeIdx + totalItems) % totalItems;
        let normOffset = offset;
        if (normOffset > totalItems / 2) normOffset = normOffset - totalItems;
        item.className = 'hero-carousel-item';
        const typeClass = itemTypes[i] === 'desktop' ? 'desktop' : 'phone';
        item.classList.add(typeClass);
        const inner = item.querySelector('.carousel-inner');
        if (normOffset === 0) {
          item.classList.add('active');
          item.style.transform = `translateX(0px) translateY(0px) translateZ(0px) scale(1) rotateY(0deg) rotateX(0deg)`;
          item.style.opacity = '1';
          item.style.filter = 'blur(0px)';
          item.style.zIndex = '10';
          item.style.pointerEvents = 'auto';
          item.style.transition = animate ? 'transform 0.5s cubic-bezier(0.34, 1.2, 0.64, 1), opacity 0.4s cubic-bezier(0.34, 1.2, 0.64, 1), filter 0.4s cubic-bezier(0.34, 1.2, 0.64, 1)' : 'none';
          if (inner) {
            inner.style.pointerEvents = 'auto';
            inner.style.transition = 'transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1)';
            inner.style.transform = 'rotateX(0deg) rotateY(0deg) translateZ(0px)';
          }
        } else if (Math.abs(normOffset) === 1) {
          item.classList.add('behind');
          const dir = normOffset > 0 ? 1 : -1;
          const xPos = dir * radius * 0.5;
          const zPos = -150;
          const s = 0.7;
          const rotY = -dir * 12;
          item.style.transform = `translateX(${xPos}px) translateY(0px) translateZ(${zPos}px) scale(${s}) rotateY(${rotY}deg)`;
          item.style.opacity = '0.25';
          item.style.filter = 'blur(2px)';
          item.style.zIndex = '5';
          item.style.pointerEvents = 'none';
          item.style.transition = animate ? 'transform 0.6s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.5s cubic-bezier(0.4, 0, 0.2, 1), filter 0.5s cubic-bezier(0.4, 0, 0.2, 1)' : 'none';
          if (inner) {
            inner.style.pointerEvents = 'none';
            inner.style.transition = 'none';
            inner.style.transform = 'rotateX(0deg) rotateY(0deg) translateZ(0px)';
          }
        } else if (Math.abs(normOffset) === 2) {
          item.classList.add('behind-2');
          const dir = normOffset > 0 ? 1 : -1;
          const xPos = dir * radius * 0.3;
          const zPos = -280;
          const s = 0.5;
          const rotY = -dir * 20;
          item.style.transform = `translateX(${xPos}px) translateY(0px) translateZ(${zPos}px) scale(${s}) rotateY(${rotY}deg)`;
          item.style.opacity = '0.06';
          item.style.filter = 'blur(5px)';
          item.style.zIndex = '2';
          item.style.pointerEvents = 'none';
          item.style.transition = animate ? 'transform 0.8s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1), filter 0.6s cubic-bezier(0.4, 0, 0.2, 1)' : 'none';
          if (inner) {
            inner.style.pointerEvents = 'none';
            inner.style.transition = 'none';
            inner.style.transform = 'rotateX(0deg) rotateY(0deg) translateZ(0px)';
          }
        } else {
          item.classList.add('hidden');
          item.style.transform = `scale(0.3) translateZ(-400px)`;
          item.style.opacity = '0';
          item.style.filter = 'blur(10px)';
          item.style.zIndex = '0';
          item.style.pointerEvents = 'none';
          item.style.transition = animate ? 'transform 0.8s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.6s cubic-bezier(0.4, 0, 0.2, 1), filter 0.6s cubic-bezier(0.4, 0, 0.2, 1)' : 'none';
          if (inner) {
            inner.style.pointerEvents = 'none';
            inner.style.transition = 'none';
            inner.style.transform = 'rotateX(0deg) rotateY(0deg) translateZ(0px)';
          }
        }
      });
      activeIndex = activeIdx;
    }

    let autoAdvanceTimer = null;
    function advanceCarousel() {
      if (isTransitioning) return;
      isTransitioning = true;
      const nextIndex = (activeIndex + 1) % total;
      positionItems(nextIndex, true);
      clearTimeout(transitionTimer);
      transitionTimer = setTimeout(() => {
        isTransitioning = false;
      }, 700);
    }
    function startAutoAdvance() {
      stopAutoAdvance();
      autoAdvanceTimer = setInterval(advanceCarousel, 4500);
    }
    function stopAutoAdvance() {
      if (autoAdvanceTimer) {
        clearInterval(autoAdvanceTimer);
        autoAdvanceTimer = null;
      }
    }
    positionItems(0, false);
    setTimeout(startAutoAdvance, 2000);
    items.forEach(item => {
      item.addEventListener('click', function(e) {
        e.stopPropagation();
        advanceCarousel();
        startAutoAdvance();
      });
    });

    // Inner 3D tilt
    const tiltElements = document.querySelectorAll('[data-tilt]');
    tiltElements.forEach(inner => {
      let tiltActive = false;
      const maxTilt = 9;
      function updateTilt(x, y) {
        if (!inner.closest('.hero-carousel-item.active')) {
          inner.style.transform = 'rotateX(0deg) rotateY(0deg) translateZ(0px)';
          return;
        }
        const rect = inner.getBoundingClientRect();
        const centerX = rect.left + rect.width / 2;
        const centerY = rect.top + rect.height / 2;
        const normX = (x - centerX) / (rect.width / 2);
        const normY = (y - centerY) / (rect.height / 2);
        const clampedX = Math.min(1, Math.max(-1, normX));
        const clampedY = Math.min(1, Math.max(-1, normY));
        const rotY = clampedX * maxTilt;
        const rotX = -clampedY * maxTilt;
        inner.style.transform = `rotateX(${rotX}deg) rotateY(${rotY}deg) translateZ(5px)`;
        const outer = inner.closest('.hero-carousel-item');
        if (outer) {
          const intensity = Math.min(1, Math.abs(rotX) / maxTilt + Math.abs(rotY) / maxTilt);
          const blurAmount = 16 + intensity * 6;
          outer.style.backdropFilter = `blur(${blurAmount}px) saturate(${180 + intensity * 20}%)`;
          outer.style.webkitBackdropFilter = `blur(${blurAmount}px) saturate(${180 + intensity * 20}%)`;
          outer.style.borderColor = `rgba(255, 255, 255, ${0.12 + intensity * 0.08})`;
        }
      }
      inner.addEventListener('mouseenter', () => {
        const parent = inner.closest('.hero-carousel-item');
        if (!parent || !parent.classList.contains('active')) return;
        tiltActive = true;
        inner.style.transition = 'transform 0.05s cubic-bezier(0.23, 1, 0.32, 1)';
      });
      inner.addEventListener('mousemove', (e) => {
        const parent = inner.closest('.hero-carousel-item');
        if (!parent || !parent.classList.contains('active')) return;
        if (!tiltActive) return;
        updateTilt(e.clientX, e.clientY);
      });
      inner.addEventListener('mouseleave', () => {
        tiltActive = false;
        const outer = inner.closest('.hero-carousel-item');
        if (outer) {
          outer.style.backdropFilter = '';
          outer.style.webkitBackdropFilter = '';
          outer.style.borderColor = '';
        }
        inner.style.transition = 'transform 0.7s cubic-bezier(0.34, 1.56, 0.64, 1)';
        inner.style.transform = 'rotateX(0deg) rotateY(0deg) translateZ(0px)';
      });
    });

    // Tip carousel
    const tipSlides = document.querySelectorAll('.tip-slide');
    const tipDots = document.querySelectorAll('#tipDots span');
    let tipIndex = 0, tipTimer;
    function showTip(i){
      tipSlides.forEach(s=>s.classList.remove('active'));
      tipDots.forEach(d=>d.classList.remove('active'));
      tipIndex = (i + tipSlides.length) % tipSlides.length;
      tipSlides[tipIndex].classList.add('active');
      tipDots[tipIndex].classList.add('active');
    }
    function nextTip(){ showTip(tipIndex+1); }
    function resetTipTimer(){ clearInterval(tipTimer); tipTimer = setInterval(nextTip, 5000); }
    document.getElementById('tipNext').addEventListener('click', ()=>{ nextTip(); resetTipTimer(); });
    document.getElementById('tipPrev').addEventListener('click', ()=>{ showTip(tipIndex-1); resetTipTimer(); });
    tipDots.forEach(d=>d.addEventListener('click', ()=>{ showTip(parseInt(d.dataset.i,10)); resetTipTimer(); }));
    resetTipTimer();

    // Badge slider
    (function badgeSlider() {
      const track = document.getElementById('badgeSliderTrack');
      const prevBtn = document.getElementById('badgePrev');
      const nextBtn = document.getElementById('badgeNext');
      const nameEl = document.getElementById('badgeName');
      const descEl = document.getElementById('badgeDesc');
      const badges = [
        { name: 'First Steps', desc: 'Awarded for completing your very first FSL lesson.' },
        { name: 'Dedicated Learner', desc: 'Earned by consistently completing learning activities.' },
        { name: 'FSL Champion', desc: 'Unlocked after mastering multiple lessons and milestones.' }
      ];
      const fallback = 'data:image/svg+xml,%3Csvg xmlns="http://www.w3.org/2000/svg" width="200" height="200" viewBox="0 0 200 200"%3E%3Crect width="200" height="200" rx="16" fill="%23f5f7fb"/%3E%3Ctext x="100" y="110" font-size="40" text-anchor="middle" fill="%230B1E3D" font-family="sans-serif"%3E🏅%3C/text%3E%3C/svg%3E';
      const imagePaths = [
        '{{ asset('images/studentBadge1.png') }}',
        '{{ asset('images/studentBadge2.png') }}',
        '{{ asset('images/studentBadge3.png') }}'
      ];
      let currentIndex = 0;
      let autoSlideInterval = null;
      let isHovering = false;
      let isAnimating = false;
      function getImageSrc(i) {
        return imagePaths[i % imagePaths.length] || fallback;
      }
      function renderSlides() {
        const total = badges.length;
        const classes = ['hidden-left', 'prev', 'active', 'next', 'hidden-right'];
        let html = '';
        for (let i = 0; i < total; i++) {
          const idx = i;
          const cls = classes[i] || 'hidden-left';
          const imgSrc = getImageSrc(i);
          html += `<div class="badge-slide ${cls}" data-index="${i}"><img src="${imgSrc}" alt="${badges[i].name}" onerror="this.src='${fallback}'"></div>`;
        }
        track.innerHTML = html;
      }
      function updateSlider(animate = true) {
        const slides = track.querySelectorAll('.badge-slide');
        if (slides.length !== 3) return;
        const total = badges.length;
        const activeIdx = currentIndex % total;
        const prevIdx = (activeIdx - 1 + total) % total;
        const nextIdx = (activeIdx + 1) % total;
        const hiddenLIdx = (activeIdx - 2 + total) % total;
        const hiddenRIdx = (activeIdx + 2) % total;
        slides.forEach((el, i) => {
          const idx = i;
          let cls = '';
          if (idx === activeIdx) cls = 'active';
          else if (idx === prevIdx) cls = 'prev';
          else if (idx === nextIdx) cls = 'next';
          else if (idx === hiddenLIdx) cls = 'hidden-left';
          else if (idx === hiddenRIdx) cls = 'hidden-right';
          else cls = 'hidden-left';
          el.className = `badge-slide ${cls}`;
          const img = el.querySelector('img');
          const newSrc = getImageSrc(idx);
          if (img.src !== newSrc) {
            img.src = newSrc;
          }
          el.dataset.index = idx;
          if (!animate) {
            el.style.transition = 'none';
            void el.offsetHeight;
            el.style.transition = '';
          }
        });
        const info = badges[activeIdx];
        if (info) {
          nameEl.classList.remove('show');
          descEl.classList.remove('show');
          nameEl.classList.add('fade');
          descEl.classList.add('fade');
          setTimeout(() => {
            nameEl.textContent = info.name;
            descEl.textContent = info.desc;
            nameEl.classList.remove('fade');
            descEl.classList.remove('fade');
            nameEl.classList.add('show');
            descEl.classList.add('show');
          }, 200);
        }
      }
      function goTo(index) {
        if (isAnimating) return;
        isAnimating = true;
        currentIndex = (index + badges.length) % badges.length;
        updateSlider(true);
        setTimeout(() => { isAnimating = false; }, 600);
      }
      function goToNext() { goTo(currentIndex + 1); }
      function goToPrev() { goTo(currentIndex - 1); }
      function startAutoSlide() {
        stopAutoSlide();
        autoSlideInterval = setInterval(() => {
          if (!isHovering && !isAnimating) {
            goToNext();
          }
        }, 3500);
      }
      function stopAutoSlide() {
        if (autoSlideInterval) {
          clearInterval(autoSlideInterval);
          autoSlideInterval = null;
        }
      }
      prevBtn.addEventListener('click', (e) => { e.stopPropagation(); goToPrev(); startAutoSlide(); });
      nextBtn.addEventListener('click', (e) => { e.stopPropagation(); goToNext(); startAutoSlide(); });
      const wrap = document.getElementById('badgeSliderWrap');
      wrap.addEventListener('mouseenter', () => { isHovering = true; });
      wrap.addEventListener('mouseleave', () => { isHovering = false; });
      renderSlides();
      updateSlider(false);
      setTimeout(() => updateSlider(true), 100);
      startAutoSlide();
    })();
  })();
</script>
</body>
</html>