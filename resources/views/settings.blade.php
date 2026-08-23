@extends('layouts.app')
@section('bg-class', 'bg-[#f8fafc]')
@section('title', 'Settings')
@section('content')

<style>
:root {
    /* ── Colors ── */
    --navy-950: #071c3f;
    --navy-900: #0d326b;
    --navy-700: #1e4b8f;
    --navy-500: #1a6fd4;
    --navy-100: #dbeafe;
    --navy-50:  #eff6ff;
    --amber-400: #fbbf24;
    --amber-500: #f59e0b;
    --gray-border: #e2e8f0;
    --gray-divider: #f1f5f9;
    --gray-text: #94a3b8;
    --danger: #dc2626;
    --danger-bg: #fef2f2;
    --danger-border: #fecaca;

    /* ── Sizing (edit these to resize everything at once) ── */
    --set-radius: 9999px;      /* pill radius for inputs/tabs */
    --set-card-radius: 20px;
    --set-row-py: 16px;        /* vertical padding per row */
    --set-input-py: 10px;      /* input vertical padding */
    --set-input-px: 18px;      /* input horizontal padding */
    --set-input-maxw: 420px;   /* cap so fields don't stretch full width */
    --set-label-w: 200px;      /* left label column width */
}

/* ── Header ── */
.settings-page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 18px; }
.settings-page-header h1 { font-size: 26px; font-weight: 800; color: var(--navy-900); }
.settings-page-header .set-eyebrow { font-size: 11.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--navy-500); margin-bottom: 2px; }
.settings-header-icons { display: flex; gap: 10px; }
.settings-icon-btn {
    width: 40px; height: 40px; border-radius: 50%; background: #fff; border: none;
    display: flex; align-items: center; justify-content: center; color: var(--navy-500);
    box-shadow: 0 1px 3px rgba(13,50,107,.08); transition: .2s; cursor: pointer;
}
.settings-icon-btn:hover { background: var(--navy-900); color: #fff; }

/* ── Hero / profile summary banner ── */
.set-hero {
    position: relative; border-radius: var(--set-card-radius); overflow: hidden;
    background: linear-gradient(120deg, var(--navy-950), var(--navy-900) 45%, var(--navy-500));
    box-shadow: 0 10px 30px rgba(7,28,63,.2); margin-bottom: 20px;
}
.set-hero-decor { position: absolute; inset: 0; overflow: hidden; pointer-events: none; }
.set-hero-decor span { position: absolute; border-radius: 50%; background: rgba(255,255,255,.06); }
.set-hero-decor span:nth-child(1) { width: 220px; height: 220px; top: -110px; right: 40px; }
.set-hero-decor span:nth-child(2) { width: 130px; height: 130px; bottom: -70px; right: 220px; background: rgba(251,191,36,.12); }
.set-hero-decor span:nth-child(3) { width: 90px; height: 90px; top: 10px; right: 260px; background: rgba(255,255,255,.05); }
.set-hero-top { display: flex; align-items: flex-start; justify-content: space-between; padding: 22px 26px 0; position: relative; z-index: 1; }
.set-hero-edit {
    width: 36px; height: 36px; border-radius: 50%; background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2); color: #fff; display: flex; align-items: center;
    justify-content: center; cursor: pointer; transition: .2s;
}
.set-hero-edit:hover { background: rgba(255,255,255,.22); }
.set-hero-body { position: relative; z-index: 1; padding: 10px 26px 26px; display: flex; align-items: flex-end; justify-content: space-between; gap: 20px; flex-wrap: wrap; }
.set-hero-identity { display: flex; align-items: center; gap: 16px; }
.set-hero-avatar-wrap { position: relative; width: 78px; height: 78px; flex-shrink: 0; }
.set-hero-avatar-wrap img, .set-hero-initials { width: 78px; height: 78px; border-radius: 50%; object-fit: cover; border: 3px solid rgba(255,255,255,.85); background: #f1f5f9; }
.set-hero-initials { display: none; align-items: center; justify-content: center; font-size: 24px; font-weight: 800; color: var(--navy-900); background: linear-gradient(135deg, var(--amber-400), var(--amber-500)); }
.set-hero-badge-dot {
    position: absolute; bottom: 2px; right: 2px; width: 20px; height: 20px; border-radius: 50%;
    background: var(--amber-400); border: 3px solid var(--navy-950); display: flex; align-items: center;
    justify-content: center; color: var(--navy-950);
}
.set-hero-badge-dot .material-symbols-outlined { font-size: 12px; font-weight: 900; }
.set-hero-text h2 { color: #fff; font-size: 21px; font-weight: 800; line-height: 1.15; display: flex; align-items: center; gap: 9px; flex-wrap: wrap; }
.set-hero-pill { font-size: 10.5px; font-weight: 700; padding: 3px 10px; border-radius: var(--set-radius); background: rgba(251,191,36,.18); color: var(--amber-400); border: 1px solid rgba(251,191,36,.3); text-transform: uppercase; letter-spacing: .03em; }
.set-hero-text p { color: rgba(255,255,255,.72); font-size: 13px; font-weight: 500; margin-top: 4px; display: flex; align-items: center; gap: 6px; }
.set-hero-text p .material-symbols-outlined { font-size: 15px; }
.set-hero-stats { display: flex; gap: 10px; flex-wrap: wrap; }
.set-hero-stat { background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.14); border-radius: 14px; padding: 9px 16px; min-width: 108px; }
.set-hero-stat .stat-label { font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: rgba(255,255,255,.55); }
.set-hero-stat .stat-value { font-size: 13.5px; font-weight: 800; color: #fff; margin-top: 2px; display: flex; align-items: center; gap: 5px; }

/* ── Two-column layout ── */
.settings-layout { display: grid; grid-template-columns: 1fr 288px; gap: 20px; align-items: start; }

/* ── Tabs ── */
.settings-tabs { display: flex; gap: 6px; margin: 0 0 18px; overflow-x: auto; }
.set-nav-item {
    display: flex; align-items: center; gap: 6px; padding: 9px 18px 9px 14px;
    font-size: 13.5px; font-weight: 700; color: #64748b; cursor: pointer;
    white-space: nowrap; border-radius: var(--set-radius);
    background: #fff; border: 1.5px solid var(--gray-divider); transition: .2s;
}
.set-nav-item .material-symbols-outlined { font-size: 17px; color: #94a3b8; transition: .2s; }
.set-nav-item:hover { border-color: var(--navy-100); color: var(--navy-900); }
.set-nav-item.active {
    color: #fff; background: linear-gradient(135deg, var(--navy-900), var(--navy-500));
    border-color: transparent; box-shadow: 0 4px 14px rgba(13,50,107,.25);
}
.set-nav-item.active .material-symbols-outlined { color: #fff; }
.set-nav-item.active .nav-badge { background: rgba(255,255,255,.25); color: #fff; }
.set-nav-item .nav-badge { background: var(--navy-100); color: var(--navy-900); font-size: 9px; font-weight: 700; padding: 2px 8px; border-radius: var(--set-radius); }

/* ── Card ── */
.set-card { background: #fff; border-radius: var(--set-card-radius); box-shadow: 0 2px 14px rgba(13,50,107,.06); padding: 26px 28px; margin-bottom: 18px; }
.set-section-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px; gap: 12px; }
.set-section-title-wrap { display: flex; align-items: center; gap: 12px; }
.set-section-icon { width: 38px; height: 38px; border-radius: 12px; background: var(--navy-100); color: var(--navy-900); display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.set-section-icon .material-symbols-outlined { font-size: 19px; }
.set-section-title { font-size: 17px; font-weight: 800; color: var(--navy-900); }
.set-section-sub { font-size: 12.5px; color: var(--gray-text); font-weight: 500; }

/* ── Row (label left / input right) — reuse for every field ── */
.set-row { display: grid; grid-template-columns: var(--set-label-w) 1fr; gap: 20px; align-items: center; padding: var(--set-row-py) 0; border-bottom: 1px solid var(--gray-divider); }
.set-row:last-child { border-bottom: none; padding-bottom: 4px; }
.set-row-label { display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #334155; }
.set-row-label .material-symbols-outlined { font-size: 16px; color: var(--navy-500); }

/* ── Input (pill, capped width) ── */
.set-input {
    width: 100%; max-width: var(--set-input-maxw);
    background: #fff; border: 1.5px solid var(--gray-border); border-radius: var(--set-radius);
    padding: var(--set-input-py) var(--set-input-px); font-size: 13.5px; font-weight: 500; color: #1e293b;
    outline: none; transition: .2s;
}
.set-input:focus { border-color: var(--navy-500); box-shadow: 0 0 0 3px rgba(26,111,212,.1); }
.set-input:read-only { color: var(--gray-text); background: #fafbfc; cursor: not-allowed; }
.set-input-icon { position: relative; display: flex; align-items: center; max-width: var(--set-input-maxw); }
.set-input-icon .material-symbols-outlined { position: absolute; left: 16px; font-size: 17px; color: var(--gray-text); pointer-events: none; }
.set-input-icon .set-input { padding-left: 44px; max-width: none; }

/* ── Buttons ── */
.set-btn-primary {
    background: linear-gradient(135deg, var(--navy-900), var(--navy-700), var(--navy-500));
    color: #fff; padding: 10px 22px; border-radius: var(--set-radius); font-size: 13.5px; font-weight: 700;
    border: none; cursor: pointer; display: inline-flex; align-items: center; gap: 7px;
    transition: .25s; box-shadow: 0 3px 10px rgba(13,50,107,.2); white-space: nowrap;
}
.set-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(13,50,107,.3); }
.set-btn-outline {
    background: #fff; color: #475569; border: 1.5px solid var(--gray-border); padding: 8px 16px;
    border-radius: var(--set-radius); font-size: 12.5px; font-weight: 600; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px; transition: .2s;
}
.set-btn-outline:hover { border-color: var(--navy-900); color: var(--navy-900); background: var(--navy-50); }
.set-btn-danger-outline {
    background: var(--danger-bg); color: var(--danger); border: 1.5px solid var(--danger-border); padding: 9px 16px;
    border-radius: var(--set-radius); font-size: 12.5px; font-weight: 700; cursor: pointer;
    display: inline-flex; align-items: center; gap: 6px; transition: .2s; width: 100%; justify-content: center;
}
.set-btn-danger-outline:hover { background: var(--danger); color: #fff; border-color: var(--danger); }

.settings-tab-pane { display: none; }
.settings-tab-pane.active { display: block; animation: setFadeIn .25s ease; }
@keyframes setFadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: translateY(0); } }

/* ── Avatar + dropzone ── */
.set-avatar-row { display: flex; align-items: center; gap: 14px; max-width: var(--set-input-maxw); }
.set-avatar-preview-wrap { position: relative; width: 46px; height: 46px; flex-shrink: 0; }
.set-avatar-preview-wrap img, .avatar-initials { width: 46px; height: 46px; border-radius: 50%; object-fit: cover; border: 2px solid var(--navy-50); background: #f1f5f9; }
.avatar-initials { display: none; align-items: center; justify-content: center; font-size: 15px; font-weight: 700; color: #fff; background: linear-gradient(135deg, var(--navy-900), var(--navy-500)); }
.set-dropzone { flex: 1; border: 1.5px dashed #93c5fd; border-radius: var(--set-radius); background: var(--navy-50); padding: 9px 20px; display: flex; align-items: center; gap: 10px; cursor: pointer; transition: .2s; }
.set-dropzone:hover, .set-dropzone.dragover { border-color: var(--navy-900); background: var(--navy-100); }
.set-dropzone .material-symbols-outlined { font-size: 18px; color: var(--navy-500); }
.set-dropzone .dz-title { font-size: 12px; font-weight: 600; color: #475569; }
.set-dropzone .dz-title b { color: var(--navy-900); }
.set-dropzone .dz-sub { font-size: 10px; color: var(--gray-text); font-weight: 500; }

/* ── Notification rows ── */
.pref-group-label { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: var(--navy-500); margin: 22px 0 4px; }
.pref-group-label:first-of-type { margin-top: 4px; }
.pref-row { display: flex; align-items: center; justify-content: space-between; gap: 14px; padding: 14px 0; border-bottom: 1px solid var(--gray-divider); }
.pref-row:last-child { border-bottom: none; padding-bottom: 4px; }
.pref-row-left { display: flex; align-items: center; gap: 12px; }
.pref-icon { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; background: var(--navy-100); color: var(--navy-900); flex-shrink: 0; }
.pref-icon.amber { background: #fef3c7; color: var(--amber-500); }
.pref-row-left h5 { font-size: 13px; font-weight: 700; color: #1e293b; display: flex; align-items: center; gap: 7px; }
.pref-row-left p { font-size: 11.5px; color: var(--gray-text); font-weight: 500; }
.pref-soon-badge { font-size: 9px; font-weight: 800; text-transform: uppercase; letter-spacing: .03em; color: var(--navy-500); background: var(--navy-100); padding: 2px 7px; border-radius: var(--set-radius); }

.toggle-switch { position: relative; display: inline-block; width: 44px; height: 25px; cursor: pointer; flex-shrink: 0; }
.toggle-switch input { opacity: 0; width: 0; height: 0; }
.toggle-slider { position: absolute; inset: 0; background: #cbd5e1; border-radius: var(--set-radius); transition: .35s; }
.toggle-slider::before { content: ''; position: absolute; height: 19px; width: 19px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: .35s; box-shadow: 0 2px 6px rgba(0,0,0,.15); }
.toggle-switch input:checked + .toggle-slider { background: var(--navy-900); }
.toggle-switch input:checked + .toggle-slider::before { transform: translateX(19px); }
.toggle-switch.disabled { opacity: .45; cursor: not-allowed; }

/* ── Info / tip cards ── */
.tip-card { background: linear-gradient(135deg, var(--amber-400), var(--amber-500)); border-radius: 16px; padding: 16px 20px; color: #fff; max-width: var(--set-input-maxw); }
.tip-card .tip-title { display: flex; align-items: center; gap: 8px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; margin-bottom: 8px; }
.tip-card ul { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: 1fr 1fr; gap: 5px 20px; }
.tip-card li { display: flex; align-items: center; gap: 7px; font-size: 12px; font-weight: 600; }
.tip-card li .material-symbols-outlined { font-size: 15px; }

.google-info-card { background: linear-gradient(135deg, var(--navy-900), var(--navy-500)); border-radius: 16px; padding: 18px 20px; color: #fff; display: flex; gap: 12px; margin-top: 14px; }
.google-info-card .material-symbols-outlined { font-size: 22px; flex-shrink: 0; }
.google-info-card p { font-size: 12.5px; font-weight: 500; opacity: .95; line-height: 1.5; }
.google-info-card a { color: #fff; text-decoration: underline; font-weight: 700; }

/* ── Sidebar ── */
.set-side-card { background: #fff; border-radius: 18px; box-shadow: 0 2px 14px rgba(13,50,107,.06); padding: 20px; margin-bottom: 18px; }
.set-side-title { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: var(--gray-text); margin-bottom: 14px; display: flex; align-items: center; gap: 6px; }
.set-side-title .material-symbols-outlined { font-size: 14px; }

.completion-ring-row { display: flex; align-items: center; gap: 14px; }
.completion-track { flex: 1; height: 8px; border-radius: var(--set-radius); background: var(--gray-divider); overflow: hidden; }
.completion-fill { height: 100%; border-radius: var(--set-radius); background: linear-gradient(90deg, var(--navy-500), var(--amber-400)); transition: width .4s ease; }
.completion-pct { font-size: 13px; font-weight: 800; color: var(--navy-900); min-width: 34px; text-align: right; }
.completion-hint { font-size: 11.5px; color: var(--gray-text); font-weight: 500; margin-top: 10px; }

.check-item { display: flex; align-items: center; gap: 9px; padding: 7px 0; font-size: 12.5px; font-weight: 600; color: #334155; }
.check-item .material-symbols-outlined { font-size: 17px; }
.check-item.done .material-symbols-outlined { color: var(--navy-500); }
.check-item.pending .material-symbols-outlined { color: var(--gray-text); }
.check-item.pending { color: var(--gray-text); }

.set-side-list { display: flex; flex-direction: column; gap: 2px; }
.set-side-link { display: flex; align-items: center; gap: 10px; padding: 9px 8px; border-radius: 10px; font-size: 12.5px; font-weight: 700; color: #334155; cursor: pointer; transition: .15s; }
.set-side-link:hover { background: var(--navy-50); color: var(--navy-900); }
.set-side-link .material-symbols-outlined { font-size: 17px; color: var(--navy-500); }

.set-side-card.danger { border: 1.5px solid var(--danger-border); }
.set-side-card.danger .set-side-title { color: var(--danger); }
.set-side-card.danger p { font-size: 11.5px; color: #7f1d1d; font-weight: 500; line-height: 1.5; margin-bottom: 12px; }

@media (max-width: 1050px) {
    .settings-layout { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .set-row { grid-template-columns: 1fr; gap: 8px; }
    .set-card { padding: 20px; }
    .set-avatar-row { flex-direction: column; align-items: flex-start; }
    .tip-card ul { grid-template-columns: 1fr; }
    .set-hero-body { align-items: flex-start; }
    .set-hero-stats { width: 100%; }
    .set-hero-stat { flex: 1; min-width: 0; }
}

/* ── Rate Us ── */
.rateus-intro { display: flex; align-items: center; gap: 14px; padding: 18px 0 22px; border-bottom: 1px solid var(--gray-divider); margin-bottom: 4px; }
.rateus-intro-text h3 { font-size: 15px; font-weight: 800; color: var(--navy-900); margin-bottom: 3px; }
.rateus-intro-text p  { font-size: 12.5px; color: var(--gray-text); font-weight: 500; line-height: 1.55; }

.senya-rating-row {
    display: flex; gap: 12px; align-items: center; justify-content: center;
    padding: 22px 0 10px;
}
.senya-rating-btn {
    position: relative; display: flex; flex-direction: column; align-items: center; gap: 6px;
    cursor: pointer; border: none; background: transparent; padding: 6px 4px; outline: none;
    transition: transform .2s ease;
}
.senya-rating-btn:focus-visible { outline: 2px solid var(--navy-500); border-radius: 12px; }
.senya-rating-btn img {
    width: 64px; height: 64px; object-fit: contain; display: block;
    filter: grayscale(1) brightness(0.5) opacity(0.35);
    transition: filter .25s ease, transform .25s ease;
}
.senya-rating-btn .star-label {
    font-size: 10px; font-weight: 700; color: var(--gray-text); letter-spacing: .03em;
    text-transform: uppercase; transition: color .2s;
    white-space: nowrap;
}
/* Hover state — highlight up-to hovered */
.senya-rating-row.hovered .senya-rating-btn.lit img   { filter: none; }
.senya-rating-row.hovered .senya-rating-btn.lit:nth-child(1) img { filter: grayscale(0.85) brightness(0.75); }
.senya-rating-row.hovered .senya-rating-btn.lit:nth-child(2) img { filter: grayscale(0.45) sepia(0.3) hue-rotate(190deg) brightness(0.85); }
.senya-rating-row.hovered .senya-rating-btn.lit:nth-child(3) img { filter: saturate(0.7) brightness(0.95); }
.senya-rating-row.hovered .senya-rating-btn.lit:nth-child(4) img { filter: saturate(1.15) brightness(1.05); }
.senya-rating-row.hovered .senya-rating-btn.lit:nth-child(5) img { filter: saturate(1.5) brightness(1.15) drop-shadow(0 0 10px #fbbf2488); }

.senya-rating-row.hovered .senya-rating-btn.lit .star-label { color: var(--navy-700); }
.senya-rating-btn.hov-scale { transform: scale(1.12); }

/* Selected (clicked) state */
.senya-rating-row.has-selection .senya-rating-btn.selected img,
.senya-rating-row.has-selection .senya-rating-btn.below-selected img {
    filter: none;
}
.senya-rating-row.has-selection .senya-rating-btn.below-selected:nth-child(1) img,
.senya-rating-row.has-selection .senya-rating-btn.selected:nth-child(1)   img { filter: grayscale(0.85) brightness(0.75); }
.senya-rating-row.has-selection .senya-rating-btn.below-selected:nth-child(2) img,
.senya-rating-row.has-selection .senya-rating-btn.selected:nth-child(2)   img { filter: grayscale(0.45) sepia(0.3) hue-rotate(190deg) brightness(0.85); }
.senya-rating-row.has-selection .senya-rating-btn.below-selected:nth-child(3) img,
.senya-rating-row.has-selection .senya-rating-btn.selected:nth-child(3)   img { filter: saturate(0.7) brightness(0.95); }
.senya-rating-row.has-selection .senya-rating-btn.below-selected:nth-child(4) img,
.senya-rating-row.has-selection .senya-rating-btn.selected:nth-child(4)   img { filter: saturate(1.15) brightness(1.05); }
.senya-rating-row.has-selection .senya-rating-btn.below-selected:nth-child(5) img,
.senya-rating-row.has-selection .senya-rating-btn.selected:nth-child(5)   img { filter: saturate(1.5) brightness(1.15) drop-shadow(0 0 10px #fbbf2488); }

.senya-rating-row.has-selection .senya-rating-btn.below-selected .star-label,
.senya-rating-row.has-selection .senya-rating-btn.selected .star-label { color: var(--navy-700); font-weight: 800; }

.senya-rating-btn.selected img { transform: scale(1.18); }

.rateus-selected-label {
    text-align: center; font-size: 13px; font-weight: 800; color: var(--navy-900); min-height: 20px;
    margin: 6px 0 18px; transition: opacity .2s;
}
.rateus-selected-label.empty { opacity: 0; }

.rateus-feedback-label { font-size: 12.5px; font-weight: 700; color: #334155; margin-bottom: 7px; display: flex; align-items: center; gap: 5px; }
.rateus-feedback-label .material-symbols-outlined { font-size: 15px; color: var(--navy-500); }
.rateus-textarea {
    width: 100%; border: 1.5px solid var(--gray-border); border-radius: 14px;
    padding: 12px 16px; font-size: 13px; font-weight: 500; color: #1e293b;
    resize: vertical; min-height: 90px; max-height: 200px; outline: none; transition: .2s;
    background: #fff; line-height: 1.6;
}
.rateus-textarea:focus { border-color: var(--navy-500); box-shadow: 0 0 0 3px rgba(26,111,212,.1); }
.rateus-char-count { font-size: 11px; color: var(--gray-text); font-weight: 500; text-align: right; margin-top: 4px; }

.rateus-submit-row { display: flex; align-items: center; justify-content: space-between; margin-top: 20px; flex-wrap: wrap; gap: 10px; }
.rateus-updated-note { font-size: 11.5px; color: var(--gray-text); font-weight: 500; display: flex; align-items: center; gap: 5px; }
.rateus-updated-note .material-symbols-outlined { font-size: 14px; }

.rateus-success-banner {
    display: flex; align-items: center; gap: 12px; background: linear-gradient(135deg, #f0fdf4, #dcfce7);
    border: 1.5px solid #86efac; border-radius: 14px; padding: 14px 18px; margin-bottom: 18px;
}
.rateus-success-banner .material-symbols-outlined { font-size: 22px; color: #16a34a; flex-shrink: 0; }
.rateus-success-banner p { font-size: 13px; font-weight: 600; color: #15803d; }
</style>

<div class="pb-20">

    @if(session('success'))
        <div class="mb-5 bg-green-50 border border-green-200 text-green-800 px-5 py-3.5 rounded-xl text-[13px] font-medium flex items-center space-x-2">
            <span class="material-symbols-outlined text-[18px]">check_circle</span>
            <span>{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-5 py-3.5 rounded-xl text-[13px] font-medium flex items-center space-x-2">
            <span class="material-symbols-outlined text-[18px]">error</span>
            <span>{{ session('error') }}</span>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-5 py-3.5 rounded-xl text-[13px] font-medium">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="settings-page-header">
        <div>
            <div class="set-eyebrow">Account</div>
            <h1>Settings</h1>
        </div>
    </div>

    {{-- HERO / PROFILE SUMMARY --}}
    @php
        $verified = !empty(Auth::user()->email_verified_at ?? null) || !empty(Auth::user()->google_id ?? null);
        $memberSince = optional($user->created_at ?? null)->format('M Y') ?? '—';
        $completionFields = [
            !empty($teacher?->first_name),
            !empty($teacher?->last_name),
            !empty($user->email ?? null),
            !empty(Auth::user()->profile_photo),
            !empty($teacher?->specialization),
        ];
        $completionDone = count(array_filter($completionFields));
        $completionPct = (int) round(($completionDone / count($completionFields)) * 100);
    @endphp
    <div class="set-hero">
        <div class="set-hero-decor"><span></span><span></span><span></span></div>
        <div class="set-hero-top">
            <div></div>
            <button type="button" class="set-hero-edit" title="Edit profile photo" onclick="document.getElementById('profilePhotoInput').click()">
                <span class="material-symbols-outlined" style="font-size:17px;">edit</span>
            </button>
        </div>
        <div class="set-hero-body">
            <div class="set-hero-identity">
                <div class="set-hero-avatar-wrap">
                    <img id="heroAvatarPreview" src="{{ Auth::user()->avatarUrl() }}" alt="{{ $teacher?->first_name ?? 'U' }}"
                         onerror="this.style.display='none';document.getElementById('heroAvatarInitials').style.display='flex';">
                    <div id="heroAvatarInitials" class="set-hero-initials">{{ strtoupper(substr($teacher?->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($teacher?->last_name ?? '', 0, 1)) }}</div>
                    @if($verified)
                    <div class="set-hero-badge-dot" title="Verified account"><span class="material-symbols-outlined">check</span></div>
                    @endif
                </div>
                <div class="set-hero-text">
                    <h2>
                        {{ trim(($teacher?->first_name ?? '').' '.($teacher?->last_name ?? '')) ?: $user->username }}
                        <span class="set-hero-pill">{{ $teacher?->specialization ?? 'Teacher' }}</span>
                    </h2>
                    <p><span class="material-symbols-outlined">alternate_email</span>{{ $user->email ?? $user->username }}</p>
                </div>
            </div>
            <div class="set-hero-stats">
                <div class="set-hero-stat">
                    <div class="stat-label">Member Since</div>
                    <div class="stat-value"><span class="material-symbols-outlined" style="font-size:14px;">calendar_month</span>{{ $memberSince }}</div>
                </div>
                @if($school)
                <div class="set-hero-stat">
                    <div class="stat-label">Institution</div>
                    <div class="stat-value"><span class="material-symbols-outlined" style="font-size:14px;">apartment</span>{{ \Illuminate\Support\Str::limit($school->name, 16) }}</div>
                </div>
                @endif
                <div class="set-hero-stat">
                    <div class="stat-label">Sign-in Method</div>
                    <div class="stat-value"><span class="material-symbols-outlined" style="font-size:14px;">{{ $user->google_id ? 'g_translate' : 'password' }}</span>{{ $user->google_id ? 'Google' : 'Password' }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="settings-layout">
    <div class="settings-main">

    <div class="settings-tabs">
        <div class="set-nav-item active" data-tab="profile"><span class="material-symbols-outlined">person</span>Profile</div>
        @if($school)<div class="set-nav-item" data-tab="institution"><span class="material-symbols-outlined">apartment</span>Institution</div>@endif
        <div class="set-nav-item" data-tab="security"><span class="material-symbols-outlined">shield</span>Security <span class="nav-badge">Secure</span></div>
        <div class="set-nav-item" data-tab="notifications"><span class="material-symbols-outlined">notifications</span>Notifications <span class="nav-badge">2</span></div>
        <div class="set-nav-item" data-tab="rateus" id="rateusTabBtn"><span class="material-symbols-outlined">star</span>Rate Us</div>
    </div>

    <div class="settings-content">

        {{-- PROFILE --}}
        <div class="settings-tab-pane active" id="tab-profile">
            <form method="POST" action="{{ route('settings.profile') }}" enctype="multipart/form-data" id="profileForm">
                @csrf @method('PATCH')
                <div class="set-card">
                    <div class="set-section-header">
                        <div class="set-section-title-wrap">
                            <div class="set-section-icon"><span class="material-symbols-outlined">badge</span></div>
                            <div>
                                <div class="set-section-title">Profile Details</div>
                                <div class="set-section-sub">Update your info and how you appear to others.</div>
                            </div>
                        </div>
                        <button type="submit" class="set-btn-primary"><span class="material-symbols-outlined" style="font-size:16px;">save</span>Save</button>
                    </div>

                    <div class="set-row">
                        <div class="set-row-label"><span class="material-symbols-outlined">account_circle</span>Photo</div>
                        <div>
                            <div class="set-avatar-row">
                                <div class="set-avatar-preview-wrap">
                                    <img id="avatarPreview" src="{{ Auth::user()->avatarUrl() }}" alt="{{ $teacher?->first_name ?? 'U' }}"
                                         onerror="this.style.display='none';document.getElementById('avatarInitials').style.display='flex';">
                                    <div id="avatarInitials" class="avatar-initials">{{ strtoupper(substr($teacher?->first_name ?? 'U', 0, 1)) }}{{ strtoupper(substr($teacher?->last_name ?? '', 0, 1)) }}</div>
                                </div>
                                <div class="set-dropzone" onclick="document.getElementById('profilePhotoInput').click()" id="photoDropzone">
                                    <span class="material-symbols-outlined">upload_file</span>
                                    <div>
                                        <div class="dz-title"><b>Click</b> to upload or drag</div>
                                        <div class="dz-sub">JPG, PNG, GIF, WEBP · 5MB max</div>
                                    </div>
                                </div>
                                <input type="file" id="profilePhotoInput" name="profile_photo" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden" onchange="previewProfilePhoto(this)">
                            </div>
                            <div class="flex items-center gap-3 mt-2">
                                @if(Auth::user()->profile_photo)
                                <button type="button" onclick="removeProfilePhoto()" class="set-btn-outline"><span class="material-symbols-outlined" style="font-size:13px;">delete</span>Remove</button>
                                @endif
                                @if(Auth::user()->google_id && Auth::user()->profile_photo && str_starts_with(Auth::user()->profile_photo, 'http'))
                                <p class="text-[11px] text-slate-400 font-medium flex items-center gap-1"><span class="material-symbols-outlined text-[13px]">info</span>Google photo</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="set-row">
                        <div class="set-row-label"><span class="material-symbols-outlined">badge</span>Full Name</div>
                        <div class="grid grid-cols-2 gap-4" style="max-width:var(--set-input-maxw)">
                            <input type="text" name="first_name" class="set-input" maxlength="50" placeholder="First name" value="{{ old('first_name', $teacher?->first_name ?? '') }}" required/>
                            <input type="text" name="last_name" class="set-input" maxlength="50" placeholder="Last name" value="{{ old('last_name', $teacher?->last_name ?? '') }}" required/>
                        </div>
                    </div>

                    <div class="set-row">
                        <div class="set-row-label"><span class="material-symbols-outlined">mail</span>Email</div>
                        <div class="set-input-icon">
                            <span class="material-symbols-outlined">alternate_email</span>
                            <input type="email" name="email" class="set-input" maxlength="100" placeholder="name@deped.gov.ph" value="{{ old('email', $user->email ?? '') }}"/>
                        </div>
                    </div>

                    <div class="set-row">
                        <div class="set-row-label"><span class="material-symbols-outlined">school</span>Specialization</div>
                        <div class="relative" style="max-width:var(--set-input-maxw)">
                            <select name="specialization" class="set-input appearance-none pr-10 cursor-pointer">
                                <option value="SNED" {{ ($teacher?->specialization ?? 'SNED') === 'SNED' ? 'selected' : '' }}>SNED</option>
                                <option value="Regular" {{ ($teacher?->specialization) === 'Regular' ? 'selected' : '' }}>Regular</option>
                            </select>
                            <span class="absolute right-5 top-1/2 -translate-y-1/2 material-symbols-outlined text-[16px] text-slate-400 pointer-events-none">expand_more</span>
                        </div>
                    </div>

                    <div class="set-row">
                        <div class="set-row-label"><span class="material-symbols-outlined">lock_person</span>Username</div>
                        <input type="text" value="{{ $user->username }}" readonly class="set-input"/>
                    </div>
                </div>
            </form>
        </div>

        {{-- INSTITUTION --}}
        @if($school)
        <div class="settings-tab-pane" id="tab-institution">
            <form method="POST" action="{{ route('settings.school') }}">
                @csrf @method('PATCH')
                <div class="set-card">
                    <div class="set-section-header">
                        <div class="set-section-title-wrap">
                            <div class="set-section-icon"><span class="material-symbols-outlined">apartment</span></div>
                            <div>
                                <div class="set-section-title">Institution Details</div>
                                <div class="set-section-sub">Your school and division information.</div>
                            </div>
                        </div>
                        <button type="submit" class="set-btn-primary"><span class="material-symbols-outlined" style="font-size:16px;">save</span>Save</button>
                    </div>

                    <div class="set-row">
                        <div class="set-row-label"><span class="material-symbols-outlined">image</span>Logo</div>
                        <div class="flex items-center gap-3">
                            <img src="https://api.dicebear.com/7.x/identicon/svg?seed={{ urlencode($school->name) }}&backgroundColor=eef2f6&iconColor=0d326b"
                                 alt="School Logo" class="w-[46px] h-[46px] rounded-full object-cover bg-slate-100 border-2 border-[#eff6ff] p-2 shrink-0"/>
                            <span class="text-[12px] text-slate-400 font-medium">Auto-generated from school name</span>
                        </div>
                    </div>
                    <div class="set-row">
                        <div class="set-row-label"><span class="material-symbols-outlined">apartment</span>School Name</div>
                        <input type="text" name="school_name" class="set-input" maxlength="150" value="{{ old('school_name', $school->name) }}" required/>
                    </div>
                    <div class="set-row">
                        <div class="set-row-label"><span class="material-symbols-outlined">location_on</span>Address</div>
                        <input type="text" name="school_address" class="set-input" maxlength="150" value="{{ old('school_address', $school->address) }}"/>
                    </div>
                    <div class="set-row">
                        <div class="set-row-label"><span class="material-symbols-outlined">map</span>Region</div>
                        <input type="text" name="region" class="set-input" maxlength="100" value="{{ old('region', $school->region) }}"/>
                    </div>
                    <div class="set-row">
                        <div class="set-row-label"><span class="material-symbols-outlined">domain</span>Division</div>
                        <input type="text" name="division" class="set-input" maxlength="100" value="{{ old('division', $school->division) }}"/>
                    </div>
                </div>
            </form>
        </div>
        @endif

        {{-- SECURITY --}}
        <div class="settings-tab-pane" id="tab-security">
            <div class="set-card">
                @if($user->google_id)
                    <div class="set-section-header">
                        <div class="set-section-title-wrap">
                            <div class="set-section-icon"><span class="material-symbols-outlined">shield</span></div>
                            <div>
                                <div class="set-section-title">Security</div>
                                <div class="set-section-sub">How you sign in to your account.</div>
                            </div>
                        </div>
                    </div>
                    <div class="google-info-card">
                        <span class="material-symbols-outlined">info</span>
                        <p>Your account is linked to Google Sign-In. To change your password, visit your <a href="https://myaccount.google.com/security" target="_blank">Google Account Security settings</a>.</p>
                    </div>
                @else
                <form method="POST" action="{{ route('settings.password') }}" id="passwordForm">
                    @csrf @method('PATCH')
                    <div class="set-section-header">
                        <div class="set-section-title-wrap">
                            <div class="set-section-icon"><span class="material-symbols-outlined">shield</span></div>
                            <div>
                                <div class="set-section-title">Security</div>
                                <div class="set-section-sub">Change your password to keep your account safe.</div>
                            </div>
                        </div>
                        <button type="submit" class="set-btn-primary"><span class="material-symbols-outlined" style="font-size:16px;">lock_reset</span>Update</button>
                    </div>

                    <div class="set-row">
                        <div class="set-row-label"><span class="material-symbols-outlined">key</span>Current Password</div>
                        <div style="max-width:var(--set-input-maxw)">
                            <div class="relative">
                                <input type="password" id="current_password" name="current_password" class="set-input pr-12" maxlength="50"
                                       placeholder="Enter your current password" value="{{ old('current_password') }}"/>
                                <button type="button" onclick="togglePwdField('current_password','eye-cur')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <span id="eye-cur" class="material-symbols-outlined text-lg">visibility</span>
                                </button>
                            </div>
                            @error('current_password')<p class="text-red-500 text-[12px] font-medium mt-1.5">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="set-row">
                        <div class="set-row-label"><span class="material-symbols-outlined">password</span>New Password</div>
                        <div class="grid grid-cols-2 gap-4" style="max-width:var(--set-input-maxw)">
                            <div>
                                <div class="relative">
                                    <input type="password" id="new_password" name="password" class="set-input pr-12" minlength="8" maxlength="50"
                                           placeholder="Minimum 8 characters"/>
                                    <button type="button" onclick="togglePwdField('new_password','eye-new')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                        <span id="eye-new" class="material-symbols-outlined text-lg">visibility</span>
                                    </button>
                                </div>
                                @error('password')<p class="text-red-500 text-[12px] font-medium mt-1.5">{{ $message }}</p>@enderror
                            </div>
                            <div class="relative">
                                <input type="password" id="confirm_password" name="password_confirmation" class="set-input pr-12" minlength="8" maxlength="50"
                                       placeholder="Re-enter new password"/>
                                <button type="button" onclick="togglePwdField('confirm_password','eye-con')" class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                    <span id="eye-con" class="material-symbols-outlined text-lg">visibility</span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="set-row" style="border-bottom:none;">
                        <div class="set-row-label"><span class="material-symbols-outlined">shield</span>Requirements</div>
                        <div class="tip-card">
                            <div class="tip-title"><span class="material-symbols-outlined" style="font-size:15px;">shield</span>Password Requirements</div>
                            <ul>
                                <li><span class="material-symbols-outlined">check_circle</span>At least 8 characters</li>
                                <li><span class="material-symbols-outlined">check_circle</span>Upper &amp; lowercase</li>
                                <li><span class="material-symbols-outlined">check_circle</span>Numbers &amp; symbols</li>
                                <li><span class="material-symbols-outlined">check_circle</span>No common words</li>
                            </ul>
                        </div>
                    </div>
                </form>
                @endif
            </div>

            <div class="set-card">
                <div class="set-section-header" style="margin-bottom:6px;">
                    <div class="set-section-title-wrap">
                        <div class="set-section-icon"><span class="material-symbols-outlined">verified_user</span></div>
                        <div>
                            <div class="set-section-title">Login &amp; Sessions</div>
                            <div class="set-section-sub">Extra layers of protection for your account.</div>
                        </div>
                    </div>
                </div>
                <div class="pref-row" style="border-bottom:none;">
                    <div class="pref-row-left">
                        <div class="pref-icon"><span class="material-symbols-outlined" style="font-size:18px;">devices</span></div>
                        <div>
                            <h5>Active Sessions</h5>
                            <p>You're signed in on this device right now.</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('settings.logout-others') }}">
                        @csrf
                        <button type="submit" class="set-btn-outline">
                            <span class="material-symbols-outlined" style="font-size:14px;">logout</span>Sign out others
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- NOTIFICATIONS --}}
        <div class="settings-tab-pane" id="tab-notifications">
            <div class="set-card">
                <form action="{{ route('settings.notifications') }}" method="POST" id="notifPrefsForm">
                @csrf
                <div class="set-section-header" style="margin-bottom:6px;">
                    <div class="set-section-title-wrap">
                        <div class="set-section-icon"><span class="material-symbols-outlined">notifications</span></div>
                        <div>
                            <div class="set-section-title">Notification Preferences</div>
                            <div class="set-section-sub">Control how and when you get updates.</div>
                        </div>
                    </div>
                    <button type="submit" class="set-btn-primary">
                        <span class="material-symbols-outlined" style="font-size:16px;">save</span>Save
                    </button>
                </div>

                @php $prefs = $teacher?->notification_prefs ?? []; @endphp

                <div class="pref-group-label">Email</div>
                <div class="pref-row" style="border-bottom:none;">
                    <div class="pref-row-left">
                        <div class="pref-icon"><span class="material-symbols-outlined" style="font-size:18px;">mail</span></div>
                        <div>
                            <h5>Email Alerts</h5>
                            <p>Receive an email for every student activity notification</p>
                        </div>
                    </div>
                    <label class="toggle-switch">
                        <input type="checkbox" name="email_alerts" value="1"
                               {{ ($prefs['email_alerts'] ?? true) ? 'checked' : '' }}>
                        <span class="toggle-slider"></span>
                    </label>
                </div>
                </form>
            </div>
        </div>

        {{-- RATE US --}}
        <div class="settings-tab-pane" id="tab-rateus">
            <div class="set-card">
                <div class="set-section-header">
                    <div class="set-section-title-wrap">
                        <div class="set-section-icon" style="background:#fef3c7;color:#b45309;"><span class="material-symbols-outlined">star</span></div>
                        <div>
                            <div class="set-section-title">Rate Senas</div>
                            <div class="set-section-sub">Share your experience and help us improve.</div>
                        </div>
                    </div>
                </div>

                @if(session('active_tab') === 'rateus' && session('success'))
                <div class="rateus-success-banner">
                    <span class="material-symbols-outlined">check_circle</span>
                    <p>{{ session('success') }}</p>
                </div>
                @endif

                <form method="POST" action="{{ route('settings.rating') }}" id="rateUsForm">
                    @csrf

                    {{-- Senya star visual rating --}}
                    <input type="hidden" name="rating" id="ratingValue" value="{{ $teacherRating?->rating ?? '' }}">

                    <div class="senya-rating-row{{ $teacherRating ? ' has-selection' : '' }}" id="senyaRatingRow">
                        @php
                            $labels    = ['Poor', 'Fair', 'Good', 'Great', 'Excellent'];
                            $existing  = $teacherRating?->rating ?? 0;
                        @endphp
                        @for($i = 1; $i <= 5; $i++)
                            @php
                                $cls = '';
                                if ($existing > 0) {
                                    if ($i === $existing)   $cls = 'selected';
                                    elseif ($i < $existing) $cls = 'below-selected';
                                }
                            @endphp
                            <button type="button"
                                    class="senya-rating-btn {{ $cls }}"
                                    data-value="{{ $i }}"
                                    id="senyaBtn{{ $i }}"
                                    aria-label="Rate {{ $i }} star"
                                    title="{{ $labels[$i-1] }}">
                                <img src="{{ asset('images/senya_face.png') }}"
                                     alt="{{ $labels[$i-1] }} – {{ $i }} star"
                                     draggable="false">
                                <span class="star-label">{{ $labels[$i-1] }}</span>
                            </button>
                        @endfor
                    </div>

                    <div class="rateus-selected-label {{ $teacherRating ? '' : 'empty' }}" id="rateusSelectedLabel">
                        @if($teacherRating)
                            @php $lvl = ['', 'Poor', 'Fair', 'Good', 'Great', 'Excellent'][$teacherRating->rating]; @endphp
                            ★ {{ $teacherRating->rating }}/5 — {{ $lvl }}
                        @else
                            &nbsp;
                        @endif
                    </div>

                    <div>
                        <div class="rateus-feedback-label">
                            <span class="material-symbols-outlined">chat_bubble</span>Feedback <span style="color:var(--gray-text);font-weight:500;">(optional)</span>
                        </div>
                        <textarea name="feedback"
                                  id="rateUsFeedback"
                                  class="rateus-textarea"
                                  maxlength="1000"
                                  placeholder="Tell us what you love, or what we can improve…">{{ old('feedback', $teacherRating?->feedback ?? '') }}</textarea>
                        <div class="rateus-char-count"><span id="rateUsFeedbackCount">{{ strlen($teacherRating?->feedback ?? '') }}</span>/1000</div>
                    </div>

                    <div class="rateus-submit-row">
                        <div>
                            @if($teacherRating)
                            <div class="rateus-updated-note">
                                <span class="material-symbols-outlined">update</span>
                                Last rated {{ $teacherRating->updated_at->diffForHumans() }}
                            </div>
                            @endif
                        </div>
                        <button type="submit" class="set-btn-primary" id="rateUsSubmitBtn">
                            <span class="material-symbols-outlined" style="font-size:16px;">star</span>
                            {{ $teacherRating ? 'Update Rating' : 'Submit Rating' }}
                        </button>
                    </div>

                    @error('rating')
                        <p class="text-red-500 text-[12px] font-medium mt-2">{{ $message }}</p>
                    @enderror
                    @error('feedback')
                        <p class="text-red-500 text-[12px] font-medium mt-2">{{ $message }}</p>
                    @enderror
                </form>
            </div>
        </div>

    </div>
    </div>

    {{-- SIDEBAR --}}
    <div class="settings-side">
        <div class="set-side-card">
            <div class="set-side-title"><span class="material-symbols-outlined">task_alt</span>Profile Completion</div>
            <div class="completion-ring-row">
                <div class="completion-track"><div class="completion-fill" style="width: {{ $completionPct }}%;"></div></div>
                <div class="completion-pct">{{ $completionPct }}%</div>
            </div>
            <div class="completion-hint">{{ $completionDone }} of {{ count($completionFields) }} details completed</div>

            <div style="margin-top:14px;">
                <div class="check-item {{ !empty($teacher?->first_name) && !empty($teacher?->last_name) ? 'done' : 'pending' }}">
                    <span class="material-symbols-outlined">{{ !empty($teacher?->first_name) && !empty($teacher?->last_name) ? 'check_circle' : 'radio_button_unchecked' }}</span>Full name added
                </div>
                <div class="check-item {{ !empty($user->email ?? null) ? 'done' : 'pending' }}">
                    <span class="material-symbols-outlined">{{ !empty($user->email ?? null) ? 'check_circle' : 'radio_button_unchecked' }}</span>Email on file
                </div>
                <div class="check-item {{ !empty(Auth::user()->profile_photo) ? 'done' : 'pending' }}">
                    <span class="material-symbols-outlined">{{ !empty(Auth::user()->profile_photo) ? 'check_circle' : 'radio_button_unchecked' }}</span>Profile photo set
                </div>
                <div class="check-item {{ !empty($teacher?->specialization) ? 'done' : 'pending' }}">
                    <span class="material-symbols-outlined">{{ !empty($teacher?->specialization) ? 'check_circle' : 'radio_button_unchecked' }}</span>Specialization set
                </div>
            </div>
        </div>

        <div class="set-side-card">
            <div class="set-side-title"><span class="material-symbols-outlined">bolt</span>Quick Links</div>
            <div class="set-side-list">
                <div class="set-side-link" data-jump="profile"><span class="material-symbols-outlined">person</span>Edit profile</div>
                <div class="set-side-link" data-jump="security"><span class="material-symbols-outlined">lock_reset</span>Change password</div>
                <div class="set-side-link" data-jump="notifications"><span class="material-symbols-outlined">notifications</span>Notification settings</div>
                @if($school)<div class="set-side-link" data-jump="institution"><span class="material-symbols-outlined">apartment</span>Institution info</div>@endif
                <div class="set-side-link" data-jump="rateus"><span class="material-symbols-outlined">star</span>Rate Senas</div>
            </div>
        </div>

        <div class="set-side-card danger">
            <div class="set-side-title"><span class="material-symbols-outlined">warning</span>Danger Zone</div>
            <p>These actions affect your access to SEÑAS Teacher Portal. Proceed with care.</p>
            <form method="POST" action="{{ route('logout') }}" id="signOutEverywhereForm">
                @csrf
                <button type="button" class="set-btn-danger-outline"
                        onclick="if(confirm('This will sign you out of all devices including this one. Continue?')) document.getElementById('signOutEverywhereForm').submit()">
                    <span class="material-symbols-outlined" style="font-size:15px;">logout</span>Sign out everywhere
                </button>
            </form>
        </div>
    </div>
    </div>

</div>

<script>
function activateTab(key) {
    document.querySelectorAll('.set-nav-item').forEach(i => i.classList.remove('active'));
    document.querySelectorAll('.settings-tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelector('.set-nav-item[data-tab="' + key + '"]')?.classList.add('active');
    document.getElementById('tab-' + key)?.classList.add('active');
}

document.querySelectorAll('.set-nav-item').forEach(item => {
    item.addEventListener('click', () => activateTab(item.dataset.tab));
});
document.querySelectorAll('.set-side-link[data-jump]').forEach(link => {
    link.addEventListener('click', () => {
        activateTab(link.dataset.jump);
        document.querySelector('.settings-content')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

@if($errors->has('current_password') || $errors->has('password'))
    activateTab('security');
@elseif($errors->has('rating') || $errors->has('feedback'))
    activateTab('rateus');
@elseif($errors->any())
    activateTab('profile');
@endif

// Re-open Rate Us tab after a successful rating submission
@if(session('active_tab') === 'rateus')
    activateTab('rateus');
@endif

function previewProfilePhoto(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        if (file.size > 5 * 1024 * 1024) { alert('Image must be under 5MB.'); input.value = ''; return; }
        const reader = new FileReader();
        reader.onload = e => {
            [['avatarPreview','avatarInitials'], ['heroAvatarPreview','heroAvatarInitials']].forEach(([imgId, initId]) => {
                const img = document.getElementById(imgId);
                const initials = document.getElementById(initId);
                if (img) { img.src = e.target.result; img.style.display = 'block'; }
                if (initials) initials.style.display = 'none';
            });
        };
        reader.readAsDataURL(file);
    }
}

(function() {
    const dz = document.getElementById('photoDropzone');
    const input = document.getElementById('profilePhotoInput');
    if (!dz || !input) return;
    ['dragenter', 'dragover'].forEach(evt => dz.addEventListener(evt, e => { e.preventDefault(); dz.classList.add('dragover'); }));
    ['dragleave', 'drop'].forEach(evt => dz.addEventListener(evt, e => { e.preventDefault(); dz.classList.remove('dragover'); }));
    dz.addEventListener('drop', e => {
        if (e.dataTransfer.files && e.dataTransfer.files[0]) { input.files = e.dataTransfer.files; previewProfilePhoto(input); }
    });
})();

function removeProfilePhoto() {
    if (!confirm('Remove your profile photo?')) return;
    const token = document.querySelector('#profileForm input[name="_token"]').value;
    fetch("{{ route('settings.profile-photo.remove') }}", {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        body: new URLSearchParams({ _token: token, _method: 'DELETE' })
    })
    .then(res => res.ok ? window.location.reload() : alert('Failed to remove photo. Please try again.'))
    .catch(() => alert('Network error while removing photo.'));
}

function togglePwdField(inputId, iconId) {
    const el = document.getElementById(inputId);
    const ic = document.getElementById(iconId);
    if (!el || !ic) return;
    el.type = el.type === 'password' ? 'text' : 'password';
    ic.textContent = el.type === 'password' ? 'visibility' : 'visibility_off';
}

// ── Rate Us interaction ──────────────────────────────────────────────────────
(function () {
    const row         = document.getElementById('senyaRatingRow');
    const hiddenInput = document.getElementById('ratingValue');
    const selectedLbl = document.getElementById('rateusSelectedLabel');
    const submitBtn   = document.getElementById('rateUsSubmitBtn');
    const form        = document.getElementById('rateUsForm');
    const textarea    = document.getElementById('rateUsFeedback');
    const charCount   = document.getElementById('rateUsFeedbackCount');

    if (!row) return;

    const labels  = ['Poor', 'Fair', 'Good', 'Great', 'Excellent'];
    const buttons = Array.from(row.querySelectorAll('.senya-rating-btn'));
    let   current = parseInt(hiddenInput.value) || 0;

    // Textarea character counter
    if (textarea && charCount) {
        textarea.addEventListener('input', () => {
            charCount.textContent = textarea.value.length;
        });
    }

    function setSelectedState(val) {
        current = val;
        hiddenInput.value = val;
        row.classList.add('has-selection');

        buttons.forEach((btn, idx) => {
            const starNum = idx + 1;
            btn.classList.remove('selected', 'below-selected');
            if (starNum === val)      btn.classList.add('selected');
            else if (starNum < val)   btn.classList.add('below-selected');
        });

        const lbl = labels[val - 1];
        selectedLbl.textContent = `★ ${val}/5 — ${lbl}`;
        selectedLbl.classList.remove('empty');
    }

    function clearHoverState() {
        row.classList.remove('hovered');
        buttons.forEach(btn => btn.classList.remove('lit', 'hov-scale'));
    }

    buttons.forEach((btn, idx) => {
        const starNum = idx + 1;

        btn.addEventListener('mouseenter', () => {
            row.classList.add('hovered');
            buttons.forEach((b, i) => {
                b.classList.toggle('lit', i < starNum);
                b.classList.toggle('hov-scale', i === idx);
            });
        });

        btn.addEventListener('mouseleave', () => {
            clearHoverState();
        });

        btn.addEventListener('click', () => {
            setSelectedState(starNum);
        });

        btn.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                setSelectedState(starNum);
            }
        });
    });

    row.addEventListener('mouseleave', clearHoverState);

    // Form validation — must have a rating selected
    if (form) {
        form.addEventListener('submit', function (e) {
            if (!current || current < 1 || current > 5) {
                e.preventDefault();
                selectedLbl.textContent = 'Please select a rating before submitting.';
                selectedLbl.classList.remove('empty');
                selectedLbl.style.color = 'var(--danger)';
                row.style.animation = 'setFadeIn .3s ease';
                setTimeout(() => { selectedLbl.style.color = ''; }, 3000);
            }
        });
    }
})();
</script>

@endsection