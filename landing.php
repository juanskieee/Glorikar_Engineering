<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Glorikar Engineering — Aircon Services</title>
  <meta name="description" content="Professional aircon cleaning, installation, repair, and maintenance in Cavite and nearby areas. Book online in minutes.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="icon" type="image/x-icon" href="/assets/icons/glorikar_logo.ico">
  <link rel="apple-touch-icon" href="/assets/glorikar_logo.png">
  <meta name="theme-color" content="#0EA5E9">
  <style>
    /* ── Reset & tokens ─────────────────────────────────── */
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    html { scroll-behavior: smooth; -webkit-text-size-adjust: 100%; }

    :root {
      --bg:             #0F172A;
      --surface:        #1E293B;
      --surface-raised: #334155;
      --accent:         #0EA5E9;
      --accent-dim:     #0369A1;
      --accent-glow:    rgba(14,165,233,0.15);
      --text-primary:   #F1F5F9;
      --text-secondary: #94A3B8;
      --text-disabled:  #475569;
      --border:         #334155;
      --white:          #FFFFFF;

      --sp-xs:  4px;
      --sp-sm:  8px;
      --sp-md:  16px;
      --sp-lg:  24px;
      --sp-xl:  40px;
      --sp-xxl: 80px;

      --r-sm: 6px;
      --r-md: 12px;
      --r-lg: 16px;
      --r-xl: 24px;

      --transition: 200ms ease-out;
    }

    body {
      background: var(--bg);
      color: var(--text-primary);
      font-family: 'Inter', -apple-system, sans-serif;
      font-size: 15px;
      line-height: 1.6;
      -webkit-font-smoothing: antialiased;
    }

    a { color: var(--accent); text-decoration: none; }
    img { display: block; max-width: 100%; }

    /* ── Layout helpers ─────────────────────────────────── */
    .container {
      max-width: 1080px;
      margin: 0 auto;
      padding: 0 var(--sp-lg);
    }

    /* ── Buttons ────────────────────────────────────────── */
    .btn {
      display: inline-flex;
      align-items: center;
      gap: var(--sp-sm);
      padding: 12px 24px;
      border-radius: var(--r-md);
      font: 600 14px/1 'Inter';
      cursor: pointer;
      border: none;
      transition: background var(--transition), transform var(--transition), box-shadow var(--transition);
      text-decoration: none;
      white-space: nowrap;
    }
    .btn-primary {
      background: var(--accent);
      color: var(--white);
    }
    .btn-primary:hover {
      background: #38BDF8;
      transform: translateY(-1px);
      box-shadow: 0 8px 24px rgba(14,165,233,0.35);
      color: var(--white);
    }
    .btn-ghost {
      background: transparent;
      color: var(--text-primary);
      border: 1px solid var(--border);
    }
    .btn-ghost:hover {
      background: var(--surface);
      border-color: var(--text-secondary);
      color: var(--text-primary);
    }
    .btn-lg { padding: 14px 28px; font-size: 15px; }

    /* ── Nav ────────────────────────────────────────────── */
    nav {
      position: sticky;
      top: 0;
      z-index: 200;
      background: rgba(15,23,42,0.85);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border);
    }
    .nav-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 80px;
    }
    .nav-brand {
      display: flex;
      align-items: center;
      gap: 10px;
      text-decoration: none;
    }
    .nav-logo-mark {
      width: 34px;
      height: 34px;
      border-radius: var(--r-sm);
      overflow: hidden;
      flex-shrink: 0;
      display: grid;
      place-content: center;
    }
    .nav-logo-mark img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .nav-brand-text {
      font: 700 15px/1 'Inter';
      color: var(--text-primary);
      letter-spacing: -0.2px;
    }
    .nav-brand-sub {
      font: 400 11px/1 'Inter';
      color: var(--text-secondary);
      margin-top: 3px;
    }
    .nav-links {
      display: flex;
      align-items: center;
      gap: var(--sp-xl);
      list-style: none;
    }
    .nav-links a {
      font: 500 13px/1 'Inter';
      color: var(--text-secondary);
      transition: color var(--transition);
      text-decoration: none;
    }
    .nav-links a:hover { color: var(--text-primary); }
    .nav-cta { display: flex; gap: var(--sp-sm); }
    .nav-toggle {
      display: none;
      width: 40px;
      height: 40px;
      place-content: center;
      background: none;
      border: 1px solid var(--border);
      border-radius: var(--r-sm);
      cursor: pointer;
      color: var(--text-primary);
      padding: 0;
    }
    .nav-toggle svg { display: block; }
    .nav-toggle .icon-close { display: none; }
    .nav-toggle[aria-expanded="true"] .icon-burger { display: none; }
    .nav-toggle[aria-expanded="true"] .icon-close { display: block; }

    .nav-mobile {
      position: absolute;
      top: 100%;
      left: 0;
      right: 0;
      background: rgba(15,23,42,0.98);
      backdrop-filter: blur(12px);
      -webkit-backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border);
      padding: var(--sp-md) var(--sp-lg) var(--sp-lg);
      opacity: 0;
      visibility: hidden;
      transform: translateY(-6px);
      transition: opacity 220ms ease-out, transform 220ms ease-out, visibility 220ms;
      max-height: calc(100vh - 80px);
      overflow-y: auto;
      box-shadow: 0 24px 40px rgba(0,0,0,0.35);
    }
    .nav-mobile.open {
      opacity: 1;
      visibility: visible;
      transform: translateY(0);
    }
    .nav-mobile-header {
      display: flex;
      align-items: center;
      padding: var(--sp-xs) var(--sp-sm) var(--sp-sm);
      border-bottom: 1px solid var(--border);
      margin-bottom: var(--sp-sm);
    }
    .nav-mobile-label {
      font: 600 11px/1 'Inter';
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--text-disabled);
    }
    .nav-mobile a[data-nav-link] {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 13px var(--sp-sm);
      font: 500 14px/1 'Inter';
      color: var(--text-secondary);
      text-decoration: none;
      border-radius: var(--r-md);
      opacity: 0;
      transform: translateY(4px);
      transition: opacity 200ms ease-out, transform 200ms ease-out, color var(--transition), background var(--transition);
    }
    .nav-mobile.open a[data-nav-link] { opacity: 1; transform: translateY(0); }
    .nav-mobile.open a[data-nav-link]:nth-of-type(1) { transition-delay: 40ms; }
    .nav-mobile.open a[data-nav-link]:nth-of-type(2) { transition-delay: 80ms; }
    .nav-mobile.open a[data-nav-link]:nth-of-type(3) { transition-delay: 120ms; }
    .nav-mobile.open a[data-nav-link]:nth-of-type(4) { transition-delay: 160ms; }
    .nav-mobile a[data-nav-link]:hover {
      color: var(--text-primary);
      background: var(--surface);
    }
    .nav-mobile a[data-nav-link] svg { color: var(--accent); flex-shrink: 0; }
    .nav-mobile-actions {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--sp-sm);
      margin-top: var(--sp-sm);
      padding-top: var(--sp-md);
      border-top: 1px solid var(--border);
    }
    .nav-mobile-actions .btn { justify-content: center; padding: 12px; }

    @media (max-width: 700px) {
      .nav-links { display: none; }
      .nav-toggle { display: grid; }
      .nav-cta .btn-ghost { display: none; }
      .nav-inner { height: 65px; }
      .nav-logo-mark { width: 28px; height: 28px; }
      .nav-brand-text { font: 700 13px/1 'Inter'; }
      .nav-brand-sub { font: 400 10px/1 'Inter'; margin-top: 2px; }
    }

    /* ── Hero ────────────────────────────────────────────── */
    .hero {
      position: relative;
      padding: calc(var(--sp-xxl) * 1.5) 0 calc(var(--sp-xxl) * 2);
      min-height: 65vh;
      overflow: hidden;
    }

    /* Background video layer */
    .hero-video-wrap {
      position: absolute;
      inset: 0;
      z-index: 0;
      overflow: hidden;
    }
    .hero-video-wrap iframe {
      position: absolute;
      top: 50%;
      left: 50%;
      border: 0;
      pointer-events: none; /* purely decorative — clicks pass through to page */
      transform: translate(-50%, -50%);
      /* Dim/cool the footage so white text stays readable */
      filter: brightness(0.55) saturate(1.05);
      /* width/height set by JS below — the source is 16:9 and the iframe is
         always kept at a true 16:9 shape (rendered larger on phones so Vimeo
         streams a sharper source), so we compute cover-fill sizing
         dynamically rather than relying on object-fit (which iframes don't
         reliably support) */
    }
    /* Dark wash + fade to page background at the edges so the video
       reads as part of the page rather than a boxed-in clip */
    .hero-video-overlay {
      position: absolute;
      inset: 0;
      z-index: 0;
      pointer-events: none;
      background:
        linear-gradient(180deg, rgba(15,23,42,0.55) 0%, rgba(15,23,42,0.35) 35%, rgba(15,23,42,0.75) 75%, var(--bg) 100%),
        radial-gradient(ellipse at center, rgba(14,165,233,0.10) 0%, transparent 65%);
    }
    /* Added by JS when reduced-motion is on, or the video fails to load —
       falls back to the original static grid+glow look */
    .hero.no-video .hero-video-wrap,
    .hero.no-video .hero-video-overlay { display: none; }
    .hero.no-video::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image:
        linear-gradient(rgba(14,165,233,0.04) 1px, transparent 1px),
        linear-gradient(90deg, rgba(14,165,233,0.04) 1px, transparent 1px);
      background-size: 48px 48px;
      pointer-events: none;
    }
    .hero.no-video::after {
      content: '';
      position: absolute;
      top: -80px;
      left: 50%;
      transform: translateX(-50%);
      width: 600px;
      height: 400px;
      background: radial-gradient(ellipse at center, rgba(14,165,233,0.18) 0%, transparent 70%);
      pointer-events: none;
    }

    .hero-content {
      position: relative;
      z-index: 1;
      max-width: 720px;
      margin: 0 auto;
      text-align: center;
    }
    
    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.5; transform: scale(0.7); }
    }

    .hero-title {
      font: 700 clamp(30px, 7vw, 52px)/1.1 'Inter';
      letter-spacing: -1.5px;
      color: var(--text-primary);
      margin-bottom: var(--sp-lg);
    }
    .hero-title em {
      font-style: normal;
      color: var(--accent);
    }
    .hero-body {
      font: 400 17px/1.7 'Inter';
      color: var(--text-secondary);
      max-width: 520px;
      margin: 0 auto var(--sp-xl);
    }
    .hero-actions {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: var(--sp-md);
      flex-wrap: wrap;
    }

    @media (max-width: 600px) {
      .hero-body { font-size: 15px; }
    }

    /* ── Section shared ─────────────────────────────────── */
    section { padding: var(--sp-xxl) 0; }
    #services { padding-top: 0; }
    .section-label {
      font: 600 11px/1 'Inter';
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--accent);
      margin-bottom: var(--sp-md);
    }
    .section-title {
      font: 700 clamp(24px, 5vw, 36px)/1.15 'Inter';
      letter-spacing: -0.8px;
      color: var(--text-primary);
      margin-bottom: var(--sp-md);
    }
    .section-body {
      font: 400 15px/1.7 'Inter';
      color: var(--text-secondary);
      max-width: 540px;
    }

    /* ── Services ────────────────────────────────────────── */
    .services-header {
      margin-bottom: var(--sp-xl);
    }

    /* Carousel track */
    .services-carousel {
      position: relative;
    }
    .services-track {
      display: flex;
      gap: var(--sp-md);
      overflow-x: auto;
      overscroll-behavior-x: contain;
      scroll-snap-type: x mandatory;
      -webkit-overflow-scrolling: touch;
      /* edge-to-edge: the carousel spans the full viewport width so there's
         no empty space on the left or right, even on large screens */
      margin: 0;
      padding: 2px var(--sp-lg) var(--sp-sm);
      scrollbar-width: none;
      cursor: grab;
      user-select: none;
    }
    .services-track::-webkit-scrollbar { display: none; }
    .services-track.dragging {
      cursor: grabbing;
      scroll-snap-type: none; /* let a drag glide past snap points, snap re-engages on release */
    }
    .services-track.dragging .service-card { pointer-events: none; } /* ignore hover/click while dragging */

    .service-card { flex: 0 0 360px; scroll-snap-align: center; }

    /* Dots */
    .services-controls {
      display: flex;
      align-items: center;
      justify-content: center;
      margin-top: var(--sp-lg);
    }
    .services-dots {
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .services-dot {
      width: 6px;
      height: 6px;
      border-radius: 3px;
      background: var(--border);
      border: none;
      padding: 0;
      cursor: pointer;
      transition: background var(--transition), width var(--transition);
    }
    .services-dot.active { background: var(--accent); width: 18px; }

    @media (max-width: 600px) {
      .services-track { padding-left: var(--sp-md); padding-right: var(--sp-md); }
      .service-card { flex-basis: calc(100vw - 2 * var(--sp-md)); max-width: none; }
      .services-controls { margin-top: var(--sp-md); }
    }
    .service-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      padding: 0;
      transition: border-color var(--transition), transform var(--transition), box-shadow var(--transition);
      position: relative;
      overflow: hidden;
    }
    .service-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 2px;
      background: var(--accent);
      opacity: 0;
      transition: opacity var(--transition);
      z-index: 2;
    }
    .service-card:hover {
      border-color: rgba(14,165,233,0.4);
      transform: translateY(-3px);
      box-shadow: 0 12px 40px rgba(0,0,0,0.3);
    }
    .service-card:hover::before { opacity: 1; }

    /* Photo banner */
    .service-media {
      position: relative;
      height: 280px;
      overflow: hidden;
      background: var(--surface-raised); /* shows while the image loads */
    }
    .service-media img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
      transition: transform 400ms ease-out;
      -webkit-user-drag: none;
      pointer-events: none; /* prevent native image ghost-drag from fighting the carousel drag */
    }
    .service-card:hover .service-media img { transform: scale(1.06); }
    .service-media::after {
      content: '';
      position: absolute;
      inset: 0;
      background: linear-gradient(180deg, rgba(15,23,42,0) 55%, var(--surface) 100%);
    }

    .service-price-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: var(--sp-sm);
    }
    .service-icon {
      flex-shrink: 0;
      width: 44px;
      height: 44px;
      background: var(--surface-raised);
      border: 1px solid var(--border);
      border-radius: var(--r-md);
      display: grid;
      place-content: center;
      color: var(--accent);
    }
    .service-body {
      padding: var(--sp-xl) var(--sp-lg) var(--sp-lg);
    }
    .service-name {
      font: 600 16px/1 'Inter';
      color: var(--text-primary);
      margin-bottom: var(--sp-sm);
    }
    .service-desc {
      font: 400 13px/1.6 'Inter';
      color: var(--text-secondary);
      margin-bottom: var(--sp-lg);
    }

    @media (max-width: 600px) {
      .service-media { height: 260px; }
      .service-icon { width: 38px; height: 38px; }
      .service-body { padding: var(--sp-lg) var(--sp-md) var(--sp-md); }
      .service-name { font-size: 15px; }
      .service-desc { font-size: 12.5px; margin-bottom: var(--sp-md); -webkit-line-clamp: 3; display: -webkit-box; -webkit-box-orient: vertical; overflow: hidden; }
      .service-price { font-size: 19px; }
    }
    .service-price {
      font: 700 clamp(18px, 3vw, 22px)/1 'Inter';
      color: var(--text-primary);
      letter-spacing: -0.5px;
    }
    .service-price span {
      font: 400 12px/1 'Inter';
      color: var(--text-secondary);
      letter-spacing: 0;
    }
    .service-book-link {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font: 500 13px/1 'Inter';
      color: var(--accent);
      margin-top: var(--sp-md);
      transition: gap var(--transition);
    }
    .service-book-link:hover { gap: 10px; }

    /* ── How it works ───────────────────────────────────── */
    .how-bg {
      background: var(--surface);
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
    }
    .how-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: var(--sp-xl);
      position: relative;
    }
    .how-grid::before {
      content: '';
      position: absolute;
      top: 60px;
      left: calc(33.33% + 12px);
      right: calc(33.33% + 12px);
      height: 1px;
      background: linear-gradient(90deg, var(--accent), transparent 50%, var(--accent));
      opacity: 0.3;
    }
    .how-step { text-align: center; }
    .how-step-icon {
      display: grid;
      place-content: center;
      width: 26px;
      height: 26px;
      margin: 0 auto 12px;
      color: var(--accent);
    }
    .how-step-num {
      display: inline-grid;
      place-content: center;
      width: 44px;
      height: 44px;
      background: var(--accent-glow);
      border: 1px solid rgba(14,165,233,0.3);
      border-radius: 50%;
      font: 700 16px/1 'Inter';
      color: var(--accent);
      margin: 0 auto var(--sp-lg);
    }
    .how-step-title {
      font: 700 15px/1 'Inter';
      color: var(--text-primary);
      margin-bottom: var(--sp-sm);
    }
    .how-step-body {
      font: 400 13px/1.6 'Inter';
      color: var(--text-secondary);
      max-width: 260px;
      margin: 0 auto;
    }

    @media (max-width: 600px) {
      .how-head { margin-bottom: var(--sp-lg); }
      .how-grid {
        display: block;
        position: relative;
        padding-left: 62px;
      }
      .how-grid::before {
        display: block;
        top: 12px;
        bottom: 12px;
        left: 13px;
        width: 2px;
        height: auto;
        border-radius: 2px;
        background: linear-gradient(180deg, var(--accent), var(--accent));
        opacity: 0.3;
      }
      .how-step {
        position: relative;
        text-align: left;
        margin-bottom: var(--sp-lg);
      }
      .how-step:last-child { margin-bottom: 0; }
      .how-step-icon {
        position: absolute;
        top: 2px;
        left: 0;
        width: 26px;
        height: 26px;
        margin: 0;
      }
      .how-step-num {
        position: absolute;
        top: 4px;
        left: 32px;
        width: 22px;
        height: 22px;
        font: 700 12px/1 'Inter';
        margin: 0;
        background: var(--accent);
        border: 2px solid var(--surface);
        border-radius: 50%;
        color: #fff;
      }
      .how-step-title {
        padding-left: 62px;
        font: 700 14.5px/1 'Inter';
        margin-bottom: 6px;
      }
      .how-step-body {
        padding-left: 62px;
        max-width: none;
        font: 400 12.5px/1.55 'Inter';
      }
    }

    /* ── Why us / features ──────────────────────────────── */
    .features-layout {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--sp-xxl);
      align-items: start;
    }
    .features-list {
      display: flex;
      flex-direction: column;
      gap: 20px;
    }
    .feature-item {
      display: flex;
      align-items: flex-start;
      gap: var(--sp-md);
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      padding: var(--sp-md);
      transition: border-color var(--transition);
    }
    .feature-item:hover { border-color: rgba(14,165,233,0.35); }
    .feature-icon-wrap {
      width: 40px;
      height: 40px;
      background: var(--accent-glow);
      border: 1px solid rgba(14,165,233,0.25);
      border-radius: var(--r-md);
      display: grid;
      place-content: center;
      color: var(--accent);
      flex-shrink: 0;
    }
    .feature-title {
      font: 600 14px/1.2 'Inter';
      color: var(--text-primary);
      margin-bottom: 5px;
    }
    .feature-body {
      font: 400 12.5px/1.6 'Inter';
      color: var(--text-secondary);
    }
    .feature-body strong {
      color: var(--text-primary);
      font-weight: 500;
    }

    /* Mobile: single-col cards, hide the visual panel */
    @media (max-width: 800px) {
      .features-layout { grid-template-columns: 1fr; gap: var(--sp-xl); }
      .features-list {
        display: flex;
        flex-direction: column;
        gap: var(--sp-sm);
      }
      .feature-item {
        flex-direction: row;
        align-items: center;
        gap: var(--sp-md);
        padding: var(--sp-md);
      }
      .feature-icon-wrap {
        width: 36px;
        height: 36px;
        flex-shrink: 0;
      }
      .feature-title { font-size: 14px; }
      .feature-body  { font-size: 13px; line-height: 1.55; }
      .features-visual { display: none; }
      .why-head .section-title {
        font-size: 22px;
        line-height: 1.25;
        letter-spacing: -0.5px;
      }
    }

    /* Visual panel */
    .features-visual {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-xl);
      padding: var(--sp-xl);
      position: relative;
      overflow: hidden;
    }
    .features-visual::after {
      content: '';
      position: absolute;
      bottom: -60px;
      right: -60px;
      width: 200px;
      height: 200px;
      background: radial-gradient(ellipse, rgba(14,165,233,0.12), transparent 70%);
      pointer-events: none;
    }
    .mock-booking {
      background: var(--bg);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      padding: var(--sp-lg);
    }
    .mock-label {
      font: 500 10px/1 'Inter';
      text-transform: uppercase;
      letter-spacing: 1px;
      color: var(--text-disabled);
      margin-bottom: var(--sp-sm);
    }
    .mock-status {
      display: flex;
      align-items: center;
      gap: var(--sp-sm);
      margin-bottom: var(--sp-md);
    }
    .mock-badge {
      padding: 4px 10px;
      border-radius: 999px;
      font: 500 11px/1 'Inter';
    }
    .badge-enroute   { background: rgba(14,165,233,0.15); color: #0EA5E9; }
    .badge-completed { background: rgba(34,197,94,0.12); color: #22C55E; }
    .mock-row {
      display: flex;
      justify-content: space-between;
      font: 400 12px/1 'Inter';
      color: var(--text-secondary);
      padding: var(--sp-xs) 0;
    }
    .mock-row b { color: var(--text-primary); font-weight: 500; }
    .mock-progress {
      display: flex;
      gap: 4px;
      margin-top: var(--sp-md);
    }
    .mock-progress-step {
      flex: 1;
      height: 4px;
      border-radius: 2px;
      background: var(--border);
    }
    .mock-progress-step.done { background: var(--accent); }
    .mock-progress-step.active { background: rgba(14,165,233,0.4); }

    /* features-layout mobile handled in the why-us block above */

    /* ── Testimonials ───────────────────────────────────── */
    .testimonials-header {
      text-align: center;
      margin-bottom: var(--sp-xl);
    }
    .testimonials-header .section-body {
      margin: 0 auto;
    }
    .testimonials-track-wrap {
      overflow: hidden;
      -webkit-mask-image: linear-gradient(90deg, transparent, black 8%, black 92%, transparent);
      mask-image: linear-gradient(90deg, transparent, black 8%, black 92%, transparent);
    }
    .testimonials-track {
      display: flex;
      gap: var(--sp-md);
      overflow-x: auto;
      -webkit-overflow-scrolling: touch;
      overscroll-behavior-x: contain;
      scrollbar-width: none;
      touch-action: pan-y;
      padding: 2px;
      cursor: grab;
      user-select: none;
    }
    .testimonials-track::-webkit-scrollbar { display: none; }
    .testimonials-track.dragging {
      cursor: grabbing;
    }
    .testimonials-track.dragging .tcard { pointer-events: none; }
    .tcard {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      padding: var(--sp-lg);
      width: 300px;
      flex-shrink: 0;
      display: flex;
      flex-direction: column;
      gap: var(--sp-sm);
      transition: border-color var(--transition);
    }
    .tcard:hover { border-color: rgba(14,165,233,0.4); }
    .tcard-stars {
      display: flex;
      gap: 2px;
    }
    .tcard-stars svg { color: var(--accent); }
    .tcard-text {
      font: 400 13px/1.65 'Inter';
      color: var(--text-secondary);
      flex: 1;
    }
    .tcard-foot {
      display: flex;
      align-items: center;
      gap: var(--sp-sm);
      margin-top: var(--sp-xs);
      min-width: 0;
    }
    .tcard-avatar {
      width: 36px;
      height: 36px;
      border-radius: 50%;
      background: var(--accent-glow);
      border: 1px solid rgba(14,165,233,0.35);
      display: grid;
      place-content: center;
      font: 600 15px/1 'Inter';
      color: var(--accent);
      flex-shrink: 0;
      user-select: none;
      -webkit-user-select: none;
    }
    .tcard-meta {
      min-width: 0;
    }
    .tcard-author {
      font: 600 13px/1 'Inter';
      color: var(--text-primary);
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .tcard-date {
      font: 400 11px/1 'Inter';
      color: var(--text-secondary);
      margin-top: 3px;
    }

    /* ── Coverage area ──────────────────────────────────── */
    /* Structural change from the old flat full-bleed stripe:
       this is now an inset bordered panel (like a big card)
       sitting on the page background, split into two panes by
       a vertical rule, with the right side as a compact
       icon-led list instead of three separate floating boxes. */
    .coverage-bg { padding: var(--sp-xxl) 0; }
    .coverage-panel {
      position: relative;
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      overflow: hidden;
    }
    .coverage-grid {
      display: grid;
      grid-template-columns: 1.15fr 1fr;
    }
    .coverage-left {
      padding: var(--sp-xxl);
    }
    .coverage-right {
      padding: var(--sp-xxl);
      border-left: 1px solid var(--border);
      background: var(--bg);
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .area-list {
      display: flex;
      flex-wrap: wrap;
      gap: var(--sp-sm);
      margin-top: var(--sp-lg);
    }
    .area-chip {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-xl);
      padding: 6px 14px;
      font: 500 12px/1 'Inter';
      color: var(--text-secondary);
      cursor: pointer;
      transition: background var(--transition), color var(--transition), border-color var(--transition);
    }
    .area-chip:hover {
      background: var(--accent);
      border-color: var(--accent);
      color: var(--white);
    }
    /* right pane: icon-led rows separated by hairlines, not cards */
    .coverage-list {
      display: flex;
      flex-direction: column;
    }
    .coverage-row {
      display: flex;
      gap: var(--sp-md);
      align-items: flex-start;
      padding: var(--sp-md) 0;
      border-bottom: 1px solid var(--border);
    }
    .coverage-row:first-child { padding-top: 0; }
    .coverage-row:last-child { border-bottom: none; padding-bottom: 0; }
    .coverage-row-icon {
      flex-shrink: 0;
      width: 36px;
      height: 36px;
      border-radius: var(--r-md);
      background: rgba(14,165,233,0.10);
      color: var(--accent);
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .coverage-row-title {
      font: 600 15px/1.3 'Inter';
      color: var(--text-primary);
    }
    .coverage-row-title span { color: var(--accent); }
    .coverage-row-sub {
      font: 400 13px/1.5 'Inter';
      color: var(--text-secondary);
      margin-top: 3px;
    }

    @media (max-width: 700px) {
      .coverage-grid { grid-template-columns: 1fr; }
      .coverage-left { padding: var(--sp-xl); }
      .coverage-right {
        padding: var(--sp-xl);
        border-left: none;
        border-top: 1px solid var(--border);
      }
    }
    @media (max-width: 420px) {
      .coverage-left, .coverage-right { padding: var(--sp-lg); }
      .area-chip { font-size: 11px; padding: 5px 11px; }
      .coverage-row-icon { width: 32px; height: 32px; }
      .coverage-row-title { font-size: 14px; }
      .coverage-row-sub { font-size: 12px; }
    }

    /* ── CTA ─────────────────────────────────────────────── */
    .cta-section {
      position: relative;
      overflow: hidden;
      padding: calc(var(--sp-xxl) * 1.2) 0;
      text-align: center;
      background:
        radial-gradient(ellipse at 50% -30%, rgba(14,165,233,0.16), transparent 70%),
        linear-gradient(180deg, var(--surface) 0%, var(--bg) 130%);
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
    }
    .cta-title {
      font: 700 clamp(26px, 6vw, 40px)/1.1 'Inter';
      letter-spacing: -1px;
      margin-bottom: var(--sp-md);
      position: relative;
      z-index: 1;
    }
    .cta-body {
      font: 400 15px/1.7 'Inter';
      color: var(--text-secondary);
      max-width: 440px;
      margin: 0 auto var(--sp-xl);
      position: relative;
      z-index: 1;
    }
    .cta-actions {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: var(--sp-md);
      flex-wrap: wrap;
      position: relative;
      z-index: 1;
    }

    @media (max-width: 600px) {
      .cta-body { font-size: 14px; }
    }

    /* ── Footer ─────────────────────────────────────────── */
    footer {
      border-top: 1px solid var(--border);
      padding-top: var(--sp-xxl);
    }
    .footer-main {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 2rem;
      padding-bottom: var(--sp-xl);
    }
    .footer-col-title {
      font: 600 12px/1 'Inter';
      letter-spacing: 1.5px;
      text-transform: uppercase;
      color: var(--accent);
      margin-bottom: var(--sp-md);
    }
    .footer-about p {
      font: 400 13px/1.7 'Inter';
      color: var(--text-secondary);
      max-width: 280px;
    }
    .footer-links {
      display: flex;
      flex-direction: column;
      gap: 12px;
      list-style: none;
    }
    .footer-links a {
      font: 400 13px/1 'Inter';
      color: var(--text-secondary);
      transition: color var(--transition);
      text-decoration: none;
    }
    .footer-links a:hover { color: var(--accent); }
    .footer-contact p {
      font: 400 13px/1.6 'Inter';
      color: var(--text-secondary);
      margin-bottom: var(--sp-xs);
    }
    .footer-contact a {
      color: var(--text-secondary);
      text-decoration: none;
      transition: color var(--transition);
    }
    .footer-contact a:hover { color: var(--accent); }
    .footer-bottom {
      border-top: 1px solid #2a3b5c;
      padding: var(--sp-lg) 0;
      text-align: center;
    }
    .footer-copy {
      font: 400 12px/1 'Inter';
      color: var(--text-disabled);
      text-align: center;
    }

    @media (max-width: 700px) {
      .footer-main { grid-template-columns: 1fr 1fr; gap: var(--sp-xl) var(--sp-lg); }
      .footer-about { grid-column: 1 / -1; }
      .footer-about p { max-width: none; }
    }
    @media (max-width: 480px) {
      .footer-main { grid-template-columns: 1fr; gap: var(--sp-lg); text-align: center; }
      .footer-about { display: flex; flex-direction: column; align-items: center; }
      .footer-about p { max-width: 320px; }
      .footer-col-title { margin-bottom: var(--sp-sm); }
      .footer-links { align-items: center; gap: 10px; }
      .footer-contact p { text-align: center; }
      .footer-bottom { padding: var(--sp-md) 0; }
    }

    /* ── Mobile typography scale ─────────────────────────── */
    @media (max-width: 600px) {
      .container { padding: 0 var(--sp-md); }
      section { padding: var(--sp-xl) 0; }
      body { font-size: 14px; }
      .btn { font-size: 13px; padding: 10px 18px; }
      .btn-lg { font-size: 14px; padding: 12px 22px; }
      .section-label { font-size: 10px; }
      .section-body { font-size: 14px; }
      .hero-body { font-size: 15px; }
      .service-desc { font-size: 12px; }
      .service-book-link { font-size: 12px; }
      .how-step-title { font-size: 14px; }
      .how-step-body { font-size: 12px; }
      .feature-title { font-size: 13px; }
      .feature-body { font-size: 12px; }
      .tcard-text { font-size: 12px; }
      .tcard-author { font-size: 12px; }
      .tcard-date { font-size: 10px; }
      .area-chip { font-size: 11px; padding: 5px 12px; }
      .footer-about p { font-size: 12px; line-height: 1.6; }
      .footer-col-title { font-size: 11px; letter-spacing: 1px; }
      .footer-links a,
      .footer-contact p { font-size: 12px; }
      .footer-copy { font-size: 11px; }
    }

    /* ── Small phones (≤380px) ─────────────────────────────
       The 600px scale above is still a bit large for narrow
       phones (SE/mini-width devices); tighten the biggest,
       most overflow-prone elements a touch further. */
    @media (max-width: 380px) {
      .container { padding: 0 var(--sp-sm); }
      .hero-title { letter-spacing: -1px; }
      .hero-body { font-size: 14px; }
      .section-title { letter-spacing: -0.4px; }
      .section-body { font-size: 13px; }
      .btn { font-size: 12px; padding: 9px 16px; }
      .coverage-row-title { font-size: 14px; }
      .coverage-row-sub { font-size: 12px; }
      .area-chip { font-size: 10.5px; padding: 5px 10px; }
    }
  </style>
</head>
<body>

<!-- ── Nav ──────────────────────────────────────────────── -->
<nav>
  <div class="container nav-inner">
    <a href="/" class="nav-brand">
      <div class="nav-logo-mark">
        <img src="/assets/glorikar_logo.png" alt="Glorikar Engineering Logo">
      </div>
      <div>
        <div class="nav-brand-text">Glorikar Engineering</div>
        <div class="nav-brand-sub">Aircon Services</div>
      </div>
    </a>
    <ul class="nav-links">
      <li><a href="#services">Services</a></li>
      <li><a href="#how-it-works">How it works</a></li>
      <li><a href="#testimonials">Reviews</a></li>
      <li><a href="#coverage">Coverage</a></li>
    </ul>
    <div class="nav-cta">
      <a href="/login.php" class="btn btn-ghost" style="padding: 8px 16px; font-size:13px;">Sign in</a>
      <a href="/register.php" class="btn btn-primary" style="padding: 8px 16px; font-size:13px;">Book now</a>
    </div>
    <button class="nav-toggle" aria-label="Menu" aria-expanded="false" aria-controls="navMobile">
      <svg class="icon-burger" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      <svg class="icon-close" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
  <div class="nav-mobile" id="navMobile">
    <div class="nav-mobile-header">
      <span class="nav-mobile-label">Menu</span>
    </div>
    <a href="#services" data-nav-link>
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
      Services
    </a>
    <a href="#how-it-works" data-nav-link>
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      How it works
    </a>
    <a href="#testimonials" data-nav-link>
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
      Reviews
    </a>
    <a href="#coverage" data-nav-link>
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
      Coverage
    </a>
    <div class="nav-mobile-actions">
      <a href="/login.php" class="btn btn-ghost">Sign in</a>
      <a href="/register.php" class="btn btn-primary">Book now</a>
    </div>
  </div>
</nav>

<!-- ── Hero ─────────────────────────────────────────────── -->
<section class="hero" id="hero">
  <div class="hero-video-wrap" style="background:#0f172a;">
    <iframe
      id="hero-video-iframe"
      src="https://player.vimeo.com/video/1219496758?badge=0&autopause=0&player_id=0&app_id=58479&background=1&autoplay=1&loop=1&muted=1&playsinline=1"
      frameborder="0"
      allow="autoplay; fullscreen; picture-in-picture; clipboard-write; encrypted-media; web-share"
      referrerpolicy="strict-origin-when-cross-origin"
      title="Video Project"
      aria-hidden="true"
      tabindex="-1">
    </iframe>
  </div>
  <div class="hero-video-overlay"></div>
  <div class="container">
    <div class="hero-content">
      <h1 class="hero-title">
        Pawis-free Philippines<br>
        <em>for every Juan!</em>
      </h1>
      <p class="hero-body">
        Abot-kayang Air-conditioning Sales & Services
      </p>
      <div class="hero-actions">
        <a href="/register.php" class="btn btn-primary btn-lg">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Book a service
        </a>
        <a href="#services" class="btn btn-ghost btn-lg">See services & pricing</a>
      </div>
    </div>
  </div>
</section>

<!-- ── Services ─────────────────────────────────────────── -->
<section id="services">
  <div class="container">
    <div class="services-header">
      <div class="section-label">What we do</div>
      <h2 class="section-title">Services & Pricing</h2>
      <p class="section-body">Straightforward pricing, no hidden fees. Parts and materials billed separately when required.</p>
    </div>
  </div>
  <div class="services-carousel">
    <div class="services-track" id="servicesTrack">

      <div class="service-card">
        <div class="service-media">
          <img src="/assets/cleaning_service.jpg" alt="Technician cleaning a split-type aircon unit" loading="lazy">
        </div>
        <div class="service-body">
          <div class="service-name">Aircon Cleaning</div>
          <div class="service-desc">Deep clean, filter wash, coil cleaning, and drain check. Keeps your unit running efficiently and smelling fresh.</div>
          <div class="service-price-row">
            <div class="service-price">₱350 <span>/ unit</span></div>
            <div class="service-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
            </div>
          </div>
          <a href="/register.php" class="service-book-link">
            Book now
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>
      </div>

      <div class="service-card">
        <div class="service-media">
          <img src="/assets/install_service.jpg" alt="Technician installing a new aircon unit" loading="lazy">
        </div>
        <div class="service-body">
          <div class="service-name">Installation</div>
          <div class="service-desc">New unit mounting, refrigerant charging, and electrical wiring. We handle wall-mounted split-type and window units.</div>
          <div class="service-price-row">
            <div class="service-price">₱2,500 <span>/ unit</span></div>
            <div class="service-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M5.34 18.66l-1.41 1.41M4.93 4.93l1.41 1.41M18.66 18.66l1.41 1.41M2 12h2m16 0h2M12 2v2m0 16v2"/></svg>
            </div>
          </div>
          <a href="/register.php" class="service-book-link">
            Book now
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>
      </div>

      <div class="service-card">
        <div class="service-media">
          <img src="/assets/repair_service.jpg" alt="Technician repairing an aircon unit" loading="lazy">
        </div>
        <div class="service-body">
          <div class="service-name">Repair</div>
          <div class="service-desc">Diagnostics and fault repair for units that aren't cooling, leaking, making noise, or tripping your breaker.</div>
          <div class="service-price-row">
            <div class="service-price">₱800 <span>/ visit</span></div>
            <div class="service-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
            </div>
          </div>
          <a href="/register.php" class="service-book-link">
            Book now
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>
      </div>

      <div class="service-card">
        <div class="service-media">
          <img src="/assets/relocation_service.jpg" alt="Technicians relocating an aircon unit" loading="lazy">
        </div>
        <div class="service-body">
          <div class="service-name">Relocation</div>
          <div class="service-desc">Safe dismounting and reinstallation of your existing unit in a new location within the same or nearby premises.</div>
          <div class="service-price-row">
            <div class="service-price">₱1,200 <span>/ unit</span></div>
            <div class="service-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="5 9 2 12 5 15"/><polyline points="19 9 22 12 19 15"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
            </div>
          </div>
          <a href="/register.php" class="service-book-link">
            Book now
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>
      </div>

      <div class="service-card">
        <div class="service-media">
          <img src="/assets/inspection_service.jpg" alt="Technician inspecting an aircon unit" loading="lazy">
        </div>
        <div class="service-body">
          <div class="service-name">Inspection</div>
          <div class="service-desc">Full system health check with a written report — ideal before purchasing a secondhand unit or before summer.</div>
          <div class="service-price-row">
            <div class="service-price">₱500 <span>/ unit</span></div>
            <div class="service-icon">
              <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
            </div>
          </div>
          <a href="/register.php" class="service-book-link">
            Book now
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
          </a>
        </div>
      </div>

    </div>
    <div class="services-controls">
      <div class="services-dots" id="servicesDots"></div>
    </div>
  </div>
</section>

<!-- ── How it works ──────────────────────────────────────── -->
<section id="how-it-works" class="how-bg">
  <div class="container">
    <div class="how-head" style="text-align:center; margin-bottom: var(--sp-xxl)">
      <div class="section-label" style="text-align:center">The process</div>
      <h2 class="section-title" style="text-align:center">From booking to done</h2>
      <p class="section-body" style="margin: 0 auto; text-align:center">100% digital booking, tracking, and invoicing.</p>
    </div>
    <div class="how-grid">
      <div class="how-step">
        <div class="how-step-icon" aria-hidden="true">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
        </div>
        <div class="how-step-num">1</div>
        <div class="how-step-title">Book your service</div>
        <div class="how-step-body">Select your service and preferred date in under two minutes.</div>
      </div>
      <div class="how-step">
        <div class="how-step-icon" aria-hidden="true">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 3h15v13H1z"/><path d="M16 8h4l3 3v5h-7V8z"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg>
        </div>
        <div class="how-step-num">2</div>
        <div class="how-step-title">Track your team</div>
        <div class="how-step-body">Get an instant notification when our technician is on the way.</div>
      </div>
      <div class="how-step">
        <div class="how-step-icon" aria-hidden="true">
          <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 2v20l2-1.5L8 22l2-1.5L12 22l2-1.5L16 22l2-1.5L20 22V2l-2 1.5L16 2l-2 1.5L12 2l-2 1.5L8 2 6 3.5 4 2z"/><path d="M8 7h8M8 11h8M8 15h5"/></svg>
        </div>
        <div class="how-step-num">3</div>
        <div class="how-step-title">Pay digitally</div>
        <div class="how-step-body">Receive a digital invoice the moment the job is complete.</div>
      </div>
    </div>
  </div>
</section>

<!-- ── Why us ─────────────────────────────────────────────── -->
<section id="why-us">
  <div class="container">
    <div class="features-layout">
      <div>
        <div class="section-label">Why Glorikar</div>
        <h2 class="section-title">No more chasing your serviceman</h2>
        <div class="features-list">
          <div class="feature-item">
            <div class="feature-icon-wrap" aria-hidden="true">
              <!-- map pin / tracking -->
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 12-9 12s-9-5-9-12a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
            </div>
            <div>
              <div class="feature-title">Real-time tracking</div>
              <div class="feature-body">Live ETA — know exactly when your tech arrives.</div>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-icon-wrap" aria-hidden="true">
              <!-- shield / trained -->
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            </div>
            <div>
              <div class="feature-title">Trained teams</div>
              <div class="feature-body">Smart dispatch matches you with the right local expert.</div>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-icon-wrap" aria-hidden="true">
              <!-- calendar -->
              <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            </div>
            <div>
              <div class="feature-title">Flexible scheduling</div>
              <div class="feature-body">Pick a window, we auto-book the earliest slot for you.</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Decorative mock booking card -->
      <div class="features-visual">
        <div class="mock-label">Your booking</div>
        <div class="mock-booking">
          <div class="mock-status">
            <span class="mock-badge badge-enroute">En Route</span>
          </div>
          <div class="mock-row"><span>Service</span><b>Aircon Cleaning × 2</b></div>
          <div class="mock-row" style="margin-top:6px"><span>Address</span><b>Dasmariñas, Cavite</b></div>
          <div class="mock-row" style="margin-top:6px"><span>ETA</span><b style="color:#0EA5E9">~15 minutes</b></div>
          <div class="mock-progress" style="margin-top:16px">
            <div class="mock-progress-step done"></div>
            <div class="mock-progress-step done"></div>
            <div class="mock-progress-step done"></div>
            <div class="mock-progress-step active"></div>
            <div class="mock-progress-step"></div>
          </div>
          <div style="display:flex;justify-content:space-between;margin-top:6px">
            <span style="font:400 10px/1 Inter;color:var(--text-disabled)">Pending</span>
            <span style="font:400 10px/1 Inter;color:var(--accent)">En Route</span>
            <span style="font:400 10px/1 Inter;color:var(--text-disabled)">Done</span>
          </div>
        </div>

        <div class="mock-label" style="margin-top:var(--sp-md)">Recent invoice</div>
        <div class="mock-booking">
          <div class="mock-row"><span>Aircon Cleaning × 3</span><b>₱1,050</b></div>
          <div class="mock-row" style="margin-top:6px;padding-top:10px;border-top:1px solid var(--border)"><span>Total</span><b>₱1,050</b></div>
          <div style="margin-top:10px"><span class="mock-badge badge-completed">Paid</span></div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── Testimonials ──────────────────────────────────────── -->
<section id="testimonials">
  <div class="container">
    <div class="testimonials-header">
      <div class="section-label">What customers say</div>
      <h2 class="section-title">Real reviews from real clients</h2>
      <p class="section-body">From Facebook recommendations — unedited, straight from the people we've served.</p>
    </div>
  </div>
  <div class="testimonials-track-wrap">
    <div class="testimonials-track" id="testimonials-track">

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">Good job, highly recommended! Napakahusay ng mga technician — very professional at mababait. Keep it up!</div>
        <div class="tcard-author">Criza Narido</div>
        <div class="tcard-date">August 2026</div>
      </div>

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">Staffs are friendly and professional. Quick in responding to inquiries, punctual, and mabilis din nalinis yung 3 AC units. Malinis gumawa, hindi makalat. Highly recommended! 💯</div>
        <div class="tcard-author">Henson Lim</div>
        <div class="tcard-date">July 2026</div>
      </div>

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">All of their staffs are kind, professional and truly accommodating. Quick with polite response in our query and communication. We highly recommend their service by ninety-nine point nine percent.</div>
        <div class="tcard-author">Ykcir Algire Malagamba</div>
        <div class="tcard-date">May 2025</div>
      </div>

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">Highly recommended AC service. Maayos at mabait ang mga technicians at honest. From now on dito ko na ipapa-maintain ang AC ko. Thank you ulet 🙏</div>
        <div class="tcard-author">Blair Robin</div>
        <div class="tcard-date">May 2025</div>
      </div>

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">We've been booking appointments with Glorikar since last year and it's one of the best decisions our family had — our AC units are in safe hands. With reasonable rates, excellent maintenance and cleaning, plus tips on how to take care of your unit. Definitely a loyal customer here! 🙋‍♂️</div>
        <div class="tcard-author">Julian Labuson</div>
        <div class="tcard-date">August 2024</div>
      </div>

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">Glorikar is highly recommended. Mabilis at madaling kausap sa chat, magalang. Mechanics are honest and polite — kahit iwan mo sila very trustworthy, informative kapag may tinanong ka. Kumpleto at malinis sila sa gamit. 3 years na akong tiwala sa Glorikar — never ako nagsisi. Keep up the good work!</div>
        <div class="tcard-author">Angeline Sabrina</div>
        <div class="tcard-date">September 2023</div>
      </div>

      <!-- Duplicate set for seamless infinite loop -->

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">Good job, highly recommended! Napakahusay ng mga technician — very professional at mababait. Keep it up!</div>
        <div class="tcard-author">Criza Narido</div>
        <div class="tcard-date">August 2026</div>
      </div>

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">Staffs are friendly and professional. Quick in responding to inquiries, punctual, and mabilis din nalinis yung 3 AC units. Malinis gumawa, hindi makalat. Highly recommended! 💯</div>
        <div class="tcard-author">Henson Lim</div>
        <div class="tcard-date">July 2026</div>
      </div>

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">All of their staffs are kind, professional and truly accommodating. Quick with polite response in our query and communication. We highly recommend their service by ninety-nine point nine percent.</div>
        <div class="tcard-author">Ykcir Algire Malagamba</div>
        <div class="tcard-date">May 2025</div>
      </div>

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">Highly recommended AC service. Maayos at mabait ang mga technicians at honest. From now on dito ko na ipapa-maintain ang AC ko. Thank you ulet 🙏</div>
        <div class="tcard-author">Blair Robin</div>
        <div class="tcard-date">May 2025</div>
      </div>

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">We've been booking appointments with Glorikar since last year and it's one of the best decisions our family had — our AC units are in safe hands. With reasonable rates, excellent maintenance and cleaning, plus tips on how to take care of your unit. Definitely a loyal customer here! 🙋‍♂️</div>
        <div class="tcard-author">Julian Labuson</div>
        <div class="tcard-date">August 2024</div>
      </div>

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">Glorikar is highly recommended. Mabilis at madaling kausap sa chat, magalang. Mechanics are honest and polite — kahit iwan mo sila very trustworthy, informative kapag may tinanong ka. Kumpleto at malinis sila sa gamit. 3 years na akong tiwala sa Glorikar — never ako nagsisi. Keep up the good work!</div>
        <div class="tcard-author">Angeline Sabrina</div>
        <div class="tcard-date">September 2023</div>
      </div>

    </div>
  </div>
</section>

<!-- ── Coverage ──────────────────────────────────────────── -->
<section id="coverage" class="coverage-bg">
  <div class="container">
    <div class="coverage-panel">
      <div class="coverage-grid">
        <div class="coverage-left">
          <div class="section-label">Where we serve</div>
          <h2 class="section-title">Cavite and nearby areas</h2>
          <p class="section-body">Serving residential and commercial clients across Cavite. Don't see your city? Sign up to check our expanding coverage.</p>
          <div class="area-list">
            <span class="area-chip">Dasmariñas</span>
            <span class="area-chip">Imus</span>
            <span class="area-chip">General Trias</span>
            <span class="area-chip">Bacoor</span>
            <span class="area-chip">Kawit</span>
            <span class="area-chip">Cavite City</span>
            <span class="area-chip">Silang</span>
            <span class="area-chip">Tagaytay</span>
            <span class="area-chip">Carmona</span>
            <span class="area-chip">Biñan, Laguna</span>
          </div>
        </div>
        <div class="coverage-right">
          <div class="coverage-list">
            <div class="coverage-row">
              <div class="coverage-row-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
              </div>
              <div>
                <div class="coverage-row-title">5 <span>services</span></div>
                <div class="coverage-row-sub">Cleaning, Installation, Repair, Relocation, Inspection</div>
              </div>
            </div>
            <div class="coverage-row">
              <div class="coverage-row-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
              </div>
              <div>
                <div class="coverage-row-title">₱350 <span>to start</span></div>
                <div class="coverage-row-sub">Aircon cleaning from ₱350/unit</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA ───────────────────────────────────────────────── -->
<section class="cta-section">
  <div class="container">
    <h2 class="cta-title">Ready to book?</h2>
    <p class="cta-body">Create your account for free and schedule your first service in minutes. No calls, no waiting.</p>
    <div class="cta-actions">
      <a href="/register.php" class="btn btn-primary btn-lg">Create a free account</a>
      <a href="/login.php" class="btn btn-ghost btn-lg">Sign in</a>
    </div>
  </div>
</section>

<!-- ── Footer ────────────────────────────────────────────── -->
<footer>
  <div class="container footer-main">
    <div class="footer-about">
      <a href="/" class="nav-brand" style="text-decoration:none">
        <div class="nav-logo-mark" style="width:28px;height:28px">
          <img src="/assets/glorikar_logo.png" alt="Glorikar Engineering Logo">
        </div>
        <div class="nav-brand-text" style="font-size:13px">Glorikar Engineering</div>
      </a>
      <p style="margin-top:var(--sp-md)">Pawis-free, abot-kayang aircon services for every Juan.</p>
    </div>
    <div>
      <div class="footer-col-title">Quick Links</div>
      <ul class="footer-links">
        <li><a href="#services">Services</a></li>
        <li><a href="#how-it-works">How it works</a></li>
        <li><a href="/register.php">Book now</a></li>
      </ul>
    </div>
    <div class="footer-contact">
      <div class="footer-col-title">Contact Us</div>
      <p><a href="tel:+639278180100">0927 818 0100</a></p>
      <p><a href="mailto:glorikar.engineering@gmail.com">glorikar.engineering@gmail.com</a></p>
      <p>Dasmariñas, Cavite</p>
    </div>
  </div>
  <div class="footer-bottom">
    <span class="footer-copy">© 2026 Glorikar Engineering</span>
  </div>
</footer>

<script>
// ── Services carousel ────────────────────────────────────
(function () {
  var track = document.getElementById('servicesTrack');
  var dotsWrap = document.getElementById('servicesDots');
  if (!track || !dotsWrap) return;

  var cards = Array.prototype.slice.call(track.children);
  if (!cards.length) return;

  // Build one dot per card
  cards.forEach(function (_, i) {
    var dot = document.createElement('button');
    dot.className = 'services-dot';
    dot.setAttribute('aria-label', 'Go to service ' + (i + 1));
    dot.addEventListener('click', function () { scrollToCard(i); });
    dotsWrap.appendChild(dot);
  });
  var dots = Array.prototype.slice.call(dotsWrap.children);

  function viewportCenter() {
    return window.innerWidth / 2;
  }

  function cardCenterX(i) {
    var r = cards[i].getBoundingClientRect();
    return r.left + r.width / 2;
  }

  function scrollToCard(i) {
    i = Math.max(0, Math.min(cards.length - 1, i));
    track.scrollTo({
      left: track.scrollLeft + cardCenterX(i) - viewportCenter(),
      behavior: 'smooth'
    });
  }

  function currentIndex() {
    var center = viewportCenter();
    var best = 0, bestDist = Infinity;
    for (var i = 0; i < cards.length; i++) {
      var d = Math.abs(cardCenterX(i) - center);
      if (d < bestDist) { bestDist = d; best = i; }
    }
    return best;
  }

  function updateUI() {
    var i = currentIndex();
    dots.forEach(function (d, di) { d.classList.toggle('active', di === i); });
  }

  var raf = null;
  track.addEventListener('scroll', function () {
    if (raf) cancelAnimationFrame(raf);
    raf = requestAnimationFrame(updateUI);
  }, { passive: true });

  window.addEventListener('resize', updateUI);

  // Start centered on the middle service instead of the first card.
  var startIndex = Math.floor(cards.length / 2);
  track.scrollLeft = track.scrollLeft + cardCenterX(startIndex) - viewportCenter();
  updateUI();

  // ── Mouse drag-to-scroll ──────────────────────────────
  // Touch and trackpad swipes already work via native overflow-x
  // scrolling, so this only kicks in for an actual mouse (pointerType
  // 'mouse') to avoid double-handling touch gestures.
  var isDragging = false;
  var dragStartX = 0;
  var dragStartScroll = 0;
  var moved = false;

  track.addEventListener('pointerdown', function (e) {
    if (e.pointerType !== 'mouse') return;
    isDragging = true;
    moved = false;
    dragStartX = e.clientX;
    dragStartScroll = track.scrollLeft;
    track.classList.add('dragging');
    track.setPointerCapture(e.pointerId);
  });

  track.addEventListener('pointermove', function (e) {
    if (!isDragging) return;
    var dx = e.clientX - dragStartX;
    if (Math.abs(dx) > 3) moved = true;
    track.scrollLeft = dragStartScroll - dx;
  });

  function endDrag() {
    if (!isDragging) return;
    isDragging = false;
    track.classList.remove('dragging');
    // Snap to the nearest card once the mouse is released
    scrollToCard(currentIndex());
  }
  track.addEventListener('pointerup', endDrag);
  track.addEventListener('pointerleave', endDrag);
  track.addEventListener('pointercancel', endDrag);

  // Prevent the "book now" link / card from registering a click at the
  // end of a drag that moved the track.
  track.addEventListener('click', function (e) {
    if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; }
  }, true);
})();
</script>
<script>
// ── Mobile nav: hamburger toggle ──
(function () {
  var toggle = document.querySelector('.nav-toggle');
  var panel = document.getElementById('navMobile');
  if (!toggle || !panel) return;

  toggle.addEventListener('click', function () {
    var open = panel.classList.toggle('open');
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
  });
  panel.querySelectorAll('a').forEach(function (link) {
    link.addEventListener('click', function () {
      panel.classList.remove('open');
      toggle.setAttribute('aria-expanded', 'false');
    });
  });
})();
</script>
<script src="https://player.vimeo.com/api/player.js"></script>
<script>
(function () {
  var hero = document.getElementById('hero');
  var wrap = document.querySelector('.hero-video-wrap');
  var iframe = document.getElementById('hero-video-iframe');
  if (!hero || !wrap || !iframe) return;

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  function fallbackToStatic() {
    hero.classList.add('no-video');
  }

  if (reduceMotion) {
    // Respect the user's OS-level motion preference — show the plain
    // hero background instead of playing the montage.
    fallbackToStatic();
    return;
  }

  // The Vimeo embed for this video is genuinely 16:9 landscape. If the
  // iframe is sized to any other aspect ratio, Vimeo detects the mismatch
  // and fills the leftover space with a blurred, stretched copy of the
  // video, so the iframe itself always stays pinned to the real 16:9
  // aspect below — we never resize it into a fake 9:16 shape. Instead we
  // scale that 16:9 box up and let the wrapper's overflow:hidden crop the
  // sides, which is what actually produces the "zoomed into a portrait
  // frame" look on phones without ever triggering Vimeo's blur-fill.
  var VIDEO_W = 16, VIDEO_H = 9;

  // Extra zoom applied only when the container is portrait (phones): the
  // iframe is rendered larger than a plain "cover" crop and the wrapper's
  // overflow:hidden crops the sides. The bigger player box also makes Vimeo
  // stream a higher-resolution source, which keeps the zoomed-in crop sharp
  // instead of blurry — no CSS transform on the iframe needed.
  var PHONE_ZOOM = 1.5;

  function resizeVideoBackground() {
    var w = wrap.offsetWidth;
    var h = wrap.offsetHeight;
    if (!w || !h) return;

    var scaleForWidth  = w / VIDEO_W;   // scale needed to cover by width
    var scaleForHeight = h / VIDEO_H;   // scale needed to cover by height
    var coverScale = Math.max(scaleForWidth, scaleForHeight);

    // Cover-fill always wins on landscape/desktop so the frame reaches the
    // left and right edges of the viewport (no side gaps on large screens).
    // Only in portrait (phones) do we add the extra zoom.
    var scale = coverScale === scaleForHeight
      ? scaleForHeight * PHONE_ZOOM
      : coverScale;

    iframe.style.width  = (VIDEO_W * scale) + 'px';
    iframe.style.height = (VIDEO_H * scale) + 'px';
  }

  resizeVideoBackground();
  window.addEventListener('resize', resizeVideoBackground);
  window.addEventListener('orientationchange', resizeVideoBackground);

  // If the Vimeo player errors out (video removed, network blocked,
  // etc.), fall back so the layout never breaks.
  if (window.Vimeo && window.Vimeo.Player) {
    try {
      var player = new window.Vimeo.Player(iframe);
      player.on('error', fallbackToStatic);
    } catch (e) {
      fallbackToStatic();
    }
  }
})();
</script>
<script>
// ── Testimonials: letter avatars + continuous marquee + grab ──
(function () {
  var track = document.querySelector('.testimonials-track');
  if (!track) return;

  var cards = Array.prototype.slice.call(track.children);

  // Add a circular profile photo with the first letter of each reviewer's
  // name to every testimonial card.
  cards.forEach(function (card) {
    var author = card.querySelector('.tcard-author');
    if (!author) return;

    var foot = document.createElement('div');
    foot.className = 'tcard-foot';

    var meta = document.createElement('div');
    meta.className = 'tcard-meta';

    author.parentNode.removeChild(author);
    meta.appendChild(author);

    var date = card.querySelector('.tcard-date');
    if (date) {
      date.parentNode.removeChild(date);
      meta.appendChild(date);
    }

    var avatar = document.createElement('div');
    avatar.className = 'tcard-avatar';
    avatar.setAttribute('aria-hidden', 'true');
    avatar.textContent = author.textContent.trim().charAt(0).toUpperCase() || '?';

    foot.appendChild(avatar);
    foot.appendChild(meta);
    card.appendChild(foot);
  });

  // The cards are laid out as the unique set followed by a duplicate set,
  // so wrapping at the first repeated card gives a seamless endless scroll.
  var firstNames = cards.map(function (c) {
    var a = c.querySelector('.tcard-author');
    return a ? a.textContent.trim() : '';
  });
  var loopCount = firstNames.length;
  for (var i = 1; i < firstNames.length; i++) {
    if (firstNames[i] === firstNames[0]) { loopCount = i; break; }
  }

  function stepWidth() {
    var gap = parseFloat(window.getComputedStyle(track).columnGap || 16) || 16;
    return (cards[0] ? cards[0].getBoundingClientRect().width : 300) + gap;
  }
  var loopWidth = stepWidth() * loopCount;

  // Auto-scroll the marquee (~55px/s, same pace as the old ticker).
  var SPEED = 55;
  var paused = false;
  var lastTs = null;
  var rafId = null;

  function tick(ts) {
    if (paused) { lastTs = null; return; }
    if (lastTs === null) lastTs = ts;
    var dt = ts - lastTs;
    lastTs = ts;
    track.scrollLeft += (SPEED * dt) / 1000;
    if (track.scrollLeft >= loopWidth) track.scrollLeft -= loopWidth;
    rafId = requestAnimationFrame(tick);
  }

  function start() {
    if (rafId) cancelAnimationFrame(rafId);
    paused = false;
    lastTs = null;
    rafId = requestAnimationFrame(tick);
  }
  function stop() {
    paused = true;
    if (rafId) { cancelAnimationFrame(rafId); rafId = null; }
  }

  // Pause while hovering so people can read on desktop.
  track.addEventListener('mouseenter', stop);
  track.addEventListener('mouseleave', function () {
    if (!isDragging) start();
  });

  // Grabbable on BOTH mouse and touch — pointer events drive horizontal
  // scrolling (touch-action: pan-y keeps vertical page scroll native).
  var isDragging = false;
  var dragStartX = 0;
  var dragStartScroll = 0;
  var moved = false;

  track.addEventListener('pointerdown', function (e) {
    stop();
    isDragging = true;
    moved = false;
    dragStartX = e.clientX;
    dragStartScroll = track.scrollLeft;
    track.classList.add('dragging');
    track.setPointerCapture(e.pointerId);
  });

  track.addEventListener('pointermove', function (e) {
    if (!isDragging) return;
    var dx = e.clientX - dragStartX;
    if (Math.abs(dx) > 3) moved = true;
    track.scrollLeft = dragStartScroll - dx;
  });

  function endDrag() {
    if (!isDragging) return;
    isDragging = false;
    track.classList.remove('dragging');
    start();
  }
  track.addEventListener('pointerup', endDrag);
  track.addEventListener('pointerleave', endDrag);
  track.addEventListener('pointercancel', endDrag);

  // Don't fire the card's click after a drag that moved the track.
  track.addEventListener('click', function (e) {
    if (moved) { e.preventDefault(); e.stopPropagation(); moved = false; }
  }, true);

  window.addEventListener('resize', function () {
    loopWidth = stepWidth() * loopCount;
  });

  start();
})();
</script>
</body>
</html>