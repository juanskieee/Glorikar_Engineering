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
      height: 60px;
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

    @media (max-width: 700px) {
      .nav-links { display: none; }
    }

    /* ── Hero ────────────────────────────────────────────── */
    .hero {
      position: relative;
      padding: var(--sp-xxl) 0 calc(var(--sp-xxl) * 1.2);
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
      /* width/height set by JS below — source is portrait (9:16), so we
         compute cover-fill sizing dynamically rather than relying on
         object-fit (which iframes don't reliably support) */
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
      font: 700 52px/1.1 'Inter';
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
    .hero-trust {
      margin-top: var(--sp-xl);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: var(--sp-lg);
      flex-wrap: wrap;
    }
    .hero-trust-item {
      display: flex;
      align-items: center;
      gap: var(--sp-sm);
      font: 500 13px/1 'Inter';
      color: var(--text-secondary);
    }
    .hero-trust-item svg { color: var(--accent); flex-shrink: 0; }

    @media (max-width: 600px) {
      .hero-title { font-size: 36px; letter-spacing: -1px; }
      .hero-body { font-size: 15px; }
    }

    /* ── Section shared ─────────────────────────────────── */
    section { padding: var(--sp-xxl) 0; }
    .section-label {
      font: 600 11px/1 'Inter';
      letter-spacing: 2px;
      text-transform: uppercase;
      color: var(--accent);
      margin-bottom: var(--sp-md);
    }
    .section-title {
      font: 700 36px/1.15 'Inter';
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
    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: var(--sp-md);
    }
    .service-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-lg);
      padding: var(--sp-xl) var(--sp-lg);
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
    }
    .service-card:hover {
      border-color: rgba(14,165,233,0.4);
      transform: translateY(-3px);
      box-shadow: 0 12px 40px rgba(0,0,0,0.3);
    }
    .service-card:hover::before { opacity: 1; }

    .service-icon {
      width: 44px;
      height: 44px;
      background: var(--accent-glow);
      border: 1px solid rgba(14,165,233,0.25);
      border-radius: var(--r-md);
      display: grid;
      place-content: center;
      color: var(--accent);
      margin-bottom: var(--sp-lg);
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
    .service-price {
      font: 700 22px/1 'Inter';
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
      top: 22px;
      left: calc(33.33% + 12px);
      right: calc(33.33% + 12px);
      height: 1px;
      background: linear-gradient(90deg, var(--accent), transparent 50%, var(--accent));
      opacity: 0.3;
    }
    .how-step { text-align: center; }
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
      font: 600 15px/1 'Inter';
      color: var(--text-primary);
      margin-bottom: var(--sp-sm);
    }
    .how-step-body {
      font: 400 13px/1.6 'Inter';
      color: var(--text-secondary);
    }

    @media (max-width: 600px) {
      .how-grid { grid-template-columns: 1fr; gap: var(--sp-xl); }
      .how-grid::before { display: none; }
    }

    /* ── Why us / features ──────────────────────────────── */
    .features-layout {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--sp-xxl);
      align-items: center;
    }
    .features-list {
      display: flex;
      flex-direction: column;
      gap: var(--sp-lg);
      margin-top: var(--sp-xl);
    }
    .feature-item {
      display: flex;
      gap: var(--sp-md);
    }
    .feature-check {
      width: 22px;
      height: 22px;
      background: var(--accent-glow);
      border: 1px solid rgba(14,165,233,0.3);
      border-radius: 50%;
      display: grid;
      place-content: center;
      color: var(--accent);
      flex-shrink: 0;
      margin-top: 2px;
    }
    .feature-title {
      font: 600 14px/1 'Inter';
      color: var(--text-primary);
      margin-bottom: 4px;
    }
    .feature-body {
      font: 400 13px/1.6 'Inter';
      color: var(--text-secondary);
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
      margin-bottom: var(--sp-md);
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
    .badge-scheduled { background: rgba(99,102,241,0.15); color: #6366F1; }
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

    @media (max-width: 800px) {
      .features-layout { grid-template-columns: 1fr; }
      .features-visual { display: none; }
    }

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
      width: max-content;
      animation: ticker 60s linear infinite;
    }
    .testimonials-track:hover { animation-play-state: paused; }
    @keyframes ticker {
      0%   { transform: translateX(0); }
      100% { transform: translateX(-50%); }
    }
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
    .tcard-stars svg { color: #FBBF24; }
    .tcard-text {
      font: 400 13px/1.65 'Inter';
      color: var(--text-secondary);
      flex: 1;
    }
    .tcard-author {
      font: 600 13px/1 'Inter';
      color: var(--text-primary);
      margin-top: var(--sp-xs);
    }
    .tcard-date {
      font: 400 11px/1 'Inter';
      color: var(--text-disabled);
    }

    /* ── Coverage area ──────────────────────────────────── */
    .coverage-bg {
      background: var(--surface);
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
    }
    .coverage-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: var(--sp-xxl);
      align-items: center;
    }
    .area-list {
      display: flex;
      flex-wrap: wrap;
      gap: var(--sp-sm);
      margin-top: var(--sp-lg);
    }
    .area-chip {
      background: var(--bg);
      border: 1px solid var(--border);
      border-radius: var(--r-xl);
      padding: 6px 14px;
      font: 500 12px/1 'Inter';
      color: var(--text-secondary);
    }

    @media (max-width: 700px) {
      .coverage-grid { grid-template-columns: 1fr; }
    }

    /* ── CTA ─────────────────────────────────────────────── */
    .cta-section {
      padding: calc(var(--sp-xxl) * 1.2) 0;
    }
    .cta-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--r-xl);
      padding: var(--sp-xxl) var(--sp-lg);
      text-align: center;
      position: relative;
      overflow: hidden;
    }
    .cta-card::before {
      content: '';
      position: absolute;
      top: 0; left: 0; right: 0;
      height: 3px;
      background: linear-gradient(90deg, transparent, var(--accent), transparent);
    }
    .cta-card::after {
      content: '';
      position: absolute;
      top: -100px;
      left: 50%;
      transform: translateX(-50%);
      width: 400px;
      height: 300px;
      background: radial-gradient(ellipse, rgba(14,165,233,0.10), transparent 70%);
      pointer-events: none;
    }
    .cta-title {
      font: 700 40px/1.1 'Inter';
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
      .cta-title { font-size: 28px; }
    }

    /* ── Footer ─────────────────────────────────────────── */
    footer {
      border-top: 1px solid var(--border);
      padding: var(--sp-xl) 0;
    }
    .footer-inner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: var(--sp-md);
    }
    .footer-links {
      display: flex;
      gap: var(--sp-lg);
      list-style: none;
    }
    .footer-links a {
      font: 400 13px/1 'Inter';
      color: var(--text-disabled);
      transition: color var(--transition);
      text-decoration: none;
    }
    .footer-links a:hover { color: var(--text-secondary); }
    .footer-copy {
      font: 400 12px/1 'Inter';
      color: var(--text-disabled);
    }

    /* ── Reviews / Testimonials ────────────────────────────── */
    .reviews-section {
      padding: var(--sp-xxl) 0;
    }
    .reviews-bg {
      background: var(--surface);
      border-top: 1px solid var(--border);
      border-bottom: 1px solid var(--border);
    }
    .reviews-header {
      text-align: center;
      margin-bottom: var(--sp-xxl);
    }
    .reviews-label {
      font: 500 11px/1 'Inter';
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: var(--accent);
      margin-bottom: var(--sp-sm);
    }
    .reviews-title {
      font: 500 32px/1.15 'Inter';
      letter-spacing: -0.8px;
      color: var(--text-primary);
      margin-bottom: var(--sp-sm);
    }
    .reviews-sub {
      font: 400 15px/1.6 'Inter';
      color: var(--text-secondary);
      margin: 0;
    }
    .reviews-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: var(--sp-md);
    }
    .review-card {
      background: var(--surface-raised);
      border: 0.5px solid var(--border);
      border-radius: var(--r-lg);
      padding: var(--sp-lg);
      display: flex;
      flex-direction: column;
      gap: var(--sp-sm);
      transition: border-color var(--transition), transform var(--transition), box-shadow var(--transition);
    }
    .review-card:hover {
      border-color: rgba(14,165,233,0.4);
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.25);
    }
    .stars {
      display: flex;
      gap: 3px;
    }
    .star {
      color: var(--status-pending);
      font-size: 15px;
      line-height: 1;
    }
    .review-text {
      font: 400 14px/1.65 'Inter';
      color: var(--text-primary);
      flex: 1;
    }
    .review-footer {
      display: flex;
      align-items: center;
      gap: var(--sp-md);
      padding-top: var(--sp-sm);
      border-top: 0.5px solid var(--border);
    }
    .avatar {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      background: var(--accent-glow);
      border: 1px solid rgba(14,165,233,0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      font: 500 12px/1 'Inter';
      color: var(--accent);
      flex-shrink: 0;
    }
    .reviewer-info {
      flex: 1;
      min-width: 0;
    }
    .reviewer-name {
      font: 500 13px/1 'Inter';
      color: var(--text-primary);
      margin: 0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }
    .reviewer-date {
      font: 400 12px/1 'Inter';
      color: var(--text-disabled);
      margin: 2px 0 0;
    }
    .verified {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font: 500 11px/1 'Inter';
      color: var(--status-completed);
      margin-left: auto;
    }
    .verified svg {
      flex-shrink: 0;
    }
    @media (max-width: 600px) {
      .reviews-title { font-size: 24px; }
      .reviews-grid { grid-template-columns: 1fr; }
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
  </div>
</nav>

<!-- ── Hero ─────────────────────────────────────────────── -->
<section class="hero" id="hero">
  <div class="hero-video-wrap" style="background:#0f172a;">
    <iframe
      id="hero-video-iframe"
      src="https://player.vimeo.com/video/1219496758?badge=0&autopause=0&background=1&autoplay=1&loop=1&muted=1&app_id=58479"
      frameborder="0"
      allow="autoplay; fullscreen; picture-in-picture"
      referrerpolicy="strict-origin-when-cross-origin"
      title="montage_services"
      aria-hidden="true"
      tabindex="-1">
    </iframe>
  </div>
  <div class="hero-video-overlay"></div>
  <div class="container">
    <div class="hero-content">
      <h1 class="hero-title">
        Keep your aircon<br>
        <em>running perfectly</em>
      </h1>
      <p class="hero-body">
        Pawis-free Philippines for every Juan!. Abot-kayang Air-conditioning Sales & Services
      </p>
      <div class="hero-actions">
        <a href="/register.php" class="btn btn-primary btn-lg">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
          Book a service
        </a>
        <a href="#services" class="btn btn-ghost btn-lg">See services & pricing</a>
      </div>
      <div class="hero-trust">
        <div class="hero-trust-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Trained technicians
        </div>
        <div class="hero-trust-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Real-time job tracking
        </div>
        <div class="hero-trust-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
          Digital invoices
        </div>
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
    <div class="services-grid">

      <div class="service-card">
        <div class="service-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
        </div>
        <div class="service-name">Aircon Cleaning</div>
        <div class="service-desc">Deep clean, filter wash, coil cleaning, and drain check. Keeps your unit running efficiently and smelling fresh.</div>
        <div class="service-price">₱350 <span>/ unit</span></div>
        <a href="/register.php" class="service-book-link">
          Book now
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
      </div>

      <div class="service-card">
        <div class="service-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M19.07 4.93l-1.41 1.41M5.34 18.66l-1.41 1.41M4.93 4.93l1.41 1.41M18.66 18.66l1.41 1.41M2 12h2m16 0h2M12 2v2m0 16v2"/></svg>
        </div>
        <div class="service-name">Installation</div>
        <div class="service-desc">New unit mounting, refrigerant charging, and electrical wiring. We handle wall-mounted split-type and window units.</div>
        <div class="service-price">₱2,500 <span>/ unit</span></div>
        <a href="/register.php" class="service-book-link">
          Book now
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
      </div>

      <div class="service-card">
        <div class="service-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
        </div>
        <div class="service-name">Repair</div>
        <div class="service-desc">Diagnostics and fault repair for units that aren't cooling, leaking, making noise, or tripping your breaker.</div>
        <div class="service-price">₱800 <span>/ visit</span></div>
        <a href="/register.php" class="service-book-link">
          Book now
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
      </div>

      <div class="service-card">
        <div class="service-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="5 9 2 12 5 15"/><polyline points="19 9 22 12 19 15"/><line x1="2" y1="12" x2="22" y2="12"/></svg>
        </div>
        <div class="service-name">Relocation</div>
        <div class="service-desc">Safe dismounting and reinstallation of your existing unit in a new location within the same or nearby premises.</div>
        <div class="service-price">₱1,200 <span>/ unit</span></div>
        <a href="/register.php" class="service-book-link">
          Book now
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
      </div>

      <div class="service-card">
        <div class="service-icon">
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
        </div>
        <div class="service-name">Inspection</div>
        <div class="service-desc">Full system health check with a written report — ideal before purchasing a secondhand unit or before summer.</div>
        <div class="service-price">₱500 <span>/ unit</span></div>
        <a href="/register.php" class="service-book-link">
          Book now
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
        </a>
      </div>

    </div>
  </div>
</section>

<!-- ── Reviews / Testimonials ─────────────────────────────── -->
<section id="reviews" class="reviews-section reviews-bg">
  <div class="container">
    <div class="reviews-header">
      <p class="reviews-label">What customers say</p>
      <h2 class="reviews-title">Real reviews from real clients</h2>
      <p class="reviews-sub">From Facebook recommendations — unedited, straight from the people we've served.</p>
    </div>
    <div class="reviews-grid">
      <div class="review-card">
        <div class="stars" aria-label="5 out of 5 stars">
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <p class="review-text">Highly recommend this team. Job well done! Affordable price yet great service. They explained what's needed and even fixed the swing of our door. At first hesitant to get their service but they're way cheaper than others — they proved their worth.</p>
        <div class="review-footer">
          <div class="avatar">IB</div>
          <div class="reviewer-info">
            <p class="reviewer-name">Irene Borbe-Ogalesco</p>
            <p class="reviewer-date">May 2025</p>
          </div>
          <span class="verified">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="16 8 12 12 8 8"/></svg>
            Verified
          </span>
        </div>
      </div>

      <div class="review-card">
        <div class="stars" aria-label="5 out of 5 stars">
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <p class="review-text">Good service, highly recommended. Friendly and accommodating staff.</p>
        <div class="review-footer">
          <div class="avatar">CL</div>
          <div class="reviewer-info">
            <p class="reviewer-name">Cleo Limquiaco Tan</p>
            <p class="reviewer-date">May 2025</p>
          </div>
          <span class="verified">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="16 8 12 12 8 8"/></svg>
            Verified
          </span>
        </div>
      </div>

      <div class="review-card">
        <div class="stars" aria-label="5 out of 5 stars">
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <p class="review-text">Thank you so much sir for the prompt service. Malamig na ulit ang room ko.</p>
        <div class="review-footer">
          <div class="avatar">YG</div>
          <div class="reviewer-info">
            <p class="reviewer-name">Yvess Gaerards</p>
            <p class="reviewer-date">May 2025</p>
          </div>
          <span class="verified">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="16 8 12 12 8 8"/></svg>
            Verified
          </span>
        </div>
      </div>

      <div class="review-card">
        <div class="stars" aria-label="5 out of 5 stars">
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <p class="review-text">Good job, highly recommended! Napakahusay ng mga technician — very professional at mababait. Keep it up!</p>
        <div class="review-footer">
          <div class="avatar">CN</div>
          <div class="reviewer-info">
            <p class="reviewer-name">Criza Narido</p>
            <p class="reviewer-date">August 2026</p>
          </div>
          <span class="verified">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="16 8 12 12 8 8"/></svg>
            Verified
          </span>
        </div>
      </div>

      <div class="review-card">
        <div class="stars" aria-label="5 out of 5 stars">
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <p class="review-text">Staffs are friendly and professional. Quick in responding to inquiries, punctual, and mabilis din nalinis yung 3 AC units. Malinis gumawa, hindi makalat. Highly recommended!</p>
        <div class="review-footer">
          <div class="avatar">HL</div>
          <div class="reviewer-info">
            <p class="reviewer-name">Henson Lim</p>
            <p class="reviewer-date">July 2026</p>
          </div>
          <span class="verified">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="16 8 12 12 8 8"/></svg>
            Verified
          </span>
        </div>
      </div>

      <div class="review-card">
        <div class="stars" aria-label="5 out of 5 stars">
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
          <svg class="star" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
        </div>
        <p class="review-text">All of their staffs are kind, professional and truly accommodating. Quick with polite response in our query and communication. We highly recommend their service by ninety-nine point nine percent.</p>
        <div class="review-footer">
          <div class="avatar">YM</div>
          <div class="reviewer-info">
            <p class="reviewer-name">Ykcir Algire Malagamba</p>
            <p class="reviewer-date">May 2025</p>
          </div>
          <span class="verified">
            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="16 8 12 12 8 8"/></svg>
            Verified
          </span>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── How it works ──────────────────────────────────────── -->
<section id="how-it-works" class="how-bg">
  <div class="container">
    <div style="text-align:center; margin-bottom: var(--sp-xxl)">
      <div class="section-label" style="text-align:center">The process</div>
      <h2 class="section-title" style="text-align:center">From booking to done</h2>
      <p class="section-body" style="margin: 0 auto; text-align:center">No phone calls needed. Book from your phone, track the job live, get your invoice digitally.</p>
    </div>
    <div class="how-grid">
      <div class="how-step">
        <div class="how-step-num">1</div>
        <div class="how-step-title">Create an account & pick your service</div>
        <div class="how-step-body">Select what you need, choose a preferred date window, and confirm your address. Takes under two minutes.</div>
      </div>
      <div class="how-step">
        <div class="how-step-num">2</div>
        <div class="how-step-title">We schedule and dispatch</div>
        <div class="how-step-body">Our system assigns the nearest available team within your date window. You get a notification when they're on the way.</div>
      </div>
      <div class="how-step">
        <div class="how-step-num">3</div>
        <div class="how-step-title">Job done, invoice sent</div>
        <div class="how-step-body">Once the technician marks the job complete, a digital invoice is generated and available in your account instantly.</div>
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
        <h2 class="section-title">Built for homeowners who don't want to chase their serviceman</h2>
        <div class="features-list">
          <div class="feature-item">
            <div class="feature-check">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
              <div class="feature-title">Real-time job tracking</div>
              <div class="feature-body">See your booking status live — from Pending all the way to Completed. Know exactly when the technician is en route.</div>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-check">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
              <div class="feature-title">Trained service teams</div>
              <div class="feature-body">Every team is assigned jobs matched to their location and workload — so you get the right technician, not just whoever picks up the phone.</div>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-check">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
              <div class="feature-title">Digital invoices, always</div>
              <div class="feature-body">No paper receipts that get lost. Your invoice is in your account the moment the job is complete, with a clear breakdown of what was done.</div>
            </div>
          </div>
          <div class="feature-item">
            <div class="feature-check">
              <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            </div>
            <div>
              <div class="feature-title">Flexible date windows</div>
              <div class="feature-body">Give us a range of dates that works for you, and our scheduler finds the earliest available slot — no back-and-forth needed.</div>
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
        <div class="tcard-text">Appreciate the polite technicians, would recommend to our friends. Service was fast, efficient and they left the area clean. Thank you.</div>
        <div class="tcard-author">Roj Jimenez</div>
        <div class="tcard-date">February 2025</div>
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
        <div class="tcard-text">Great service and confident expertise while working, which only comes through a lot of experience. 10/10 would recommend, will be buying more units from them soon.</div>
        <div class="tcard-author">Miguel Kaimo</div>
        <div class="tcard-date">May 2024</div>
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

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">Very professional and accommodating — they really know their craft! Napakadaling kausap. We bought a new aircon and ang bilis lang nila nainstall. They even transferred our old aircon to the other room with cleaning pa.</div>
        <div class="tcard-author">Paulo Mercado</div>
        <div class="tcard-date">May 2023</div>
      </div>

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">I highly recommend this team. Job well done! Affordable price yet great service. They explained what's needed to be done and even fixed the swing of our AC. At first hesitant ako to get their service kasi way cheaper than others but they proved their worth. Will definitely contact again! 👍</div>
        <div class="tcard-author">Cha Borbe-Ogalesco</div>
        <div class="tcard-date">April 2023</div>
      </div>

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">Good service, highly recommended. Friendly and accommodating staff. 👍</div>
        <div class="tcard-author">Cleo Limquiaco Tan</div>
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
        <div class="tcard-text">Thank you so much sir for the prompt service. Malamig na ulit ang room ko. ❄️</div>
        <div class="tcard-author">Yvess Gaerards</div>
        <div class="tcard-date">May 2025</div>
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
        <div class="tcard-text">Appreciate the polite technicians, would recommend to our friends. Service was fast, efficient and they left the area clean. Thank you.</div>
        <div class="tcard-author">Roj Jimenez</div>
        <div class="tcard-date">February 2025</div>
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

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">Very professional and accommodating — they really know their craft! Napakadaling kausap. We bought a new aircon and ang bilis lang nila nainstall. They even transferred our old aircon to the other room with cleaning pa.</div>
        <div class="tcard-author">Paulo Mercado</div>
        <div class="tcard-date">May 2023</div>
      </div>

      <div class="tcard">
        <div class="tcard-stars">
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
          <svg width="13" height="13" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        </div>
        <div class="tcard-text">I highly recommend this team. Job well done! Affordable price yet great service. They explained what's needed to be done and even fixed the swing of our AC. Will definitely contact again! 👍</div>
        <div class="tcard-author">Cha Borbe-Ogalesco</div>
        <div class="tcard-date">April 2023</div>
      </div>

    </div>
  </div>
</section>

<!-- ── Coverage ──────────────────────────────────────────── -->
<section id="coverage" class="coverage-bg">
  <div class="container">
    <div class="coverage-grid">
      <div>
        <div class="section-label">Where we serve</div>
        <h2 class="section-title">Cavite and nearby areas</h2>
        <p class="section-body">Our teams are based across Cavite and can reach residential and commercial clients in the following areas. Not sure if we cover you? Create an account and check.</p>
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
      <div style="display:flex;flex-direction:column;gap:var(--sp-md)">
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--r-lg);padding:var(--sp-lg)">
          <div style="font:600 28px/1 Inter;color:var(--text-primary);letter-spacing:-0.5px">5 <span style="color:var(--accent)">services</span></div>
          <div style="font:400 13px/1 Inter;color:var(--text-secondary);margin-top:var(--sp-sm)">Cleaning, Installation, Repair, Relocation, Inspection</div>
        </div>
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--r-lg);padding:var(--sp-lg)">
          <div style="font:600 28px/1 Inter;color:var(--text-primary);letter-spacing:-0.5px">Nightly <span style="color:var(--accent)">scheduling</span></div>
          <div style="font:400 13px/1 Inter;color:var(--text-secondary);margin-top:var(--sp-sm)">Our engine assigns your visit automatically — no follow-up calls needed</div>
        </div>
        <div style="background:var(--bg);border:1px solid var(--border);border-radius:var(--r-lg);padding:var(--sp-lg)">
          <div style="font:600 28px/1 Inter;color:var(--text-primary);letter-spacing:-0.5px">₱350 <span style="color:var(--accent)">to start</span></div>
          <div style="font:400 13px/1 Inter;color:var(--text-secondary);margin-top:var(--sp-sm)">Aircon cleaning from ₱350/unit — the most popular service we offer</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ── CTA ───────────────────────────────────────────────── -->
<section class="cta-section">
  <div class="container">
    <div class="cta-card">
      <h2 class="cta-title">Ready to book?</h2>
      <p class="cta-body">Create your account for free and schedule your first service in minutes. No calls, no waiting.</p>
      <div class="cta-actions">
        <a href="/register.php" class="btn btn-primary btn-lg">Create a free account</a>
        <a href="/login.php" class="btn btn-ghost btn-lg">Sign in</a>
      </div>
    </div>
  </div>
</section>

<!-- ── Footer ────────────────────────────────────────────── -->
<footer>
  <div class="container footer-inner">
    <div class="footer-brand">
      <a href="/" class="nav-brand" style="text-decoration:none">
        <div class="nav-logo-mark" style="width:28px;height:28px">
          <img src="/assets/glorikar_logo.png" alt="Glorikar Engineering Logo">
        </div>
        <div class="nav-brand-text" style="font-size:13px">Glorikar Engineering</div>
      </a>
      <div class="footer-vision" style="margin-top:var(--sp-md); max-width: 280px;">
        <p style="font: 500 12px/1.4 'Inter'; color: var(--accent); margin-bottom: var(--sp-xs);">Vision</p>
        <p style="font: 400 12px/1.5 'Inter'; color: var(--text-secondary);">Pawis-free Philippines for every Juan!</p>
        <p style="font: 500 12px/1.4 'Inter'; color: var(--accent); margin: var(--sp-sm) 0 var(--sp-xs);">Mission</p>
        <p style="font: 400 12px/1.5 'Inter'; color: var(--text-secondary);">Abot-kayang Air-conditioning Sales & Services</p>
        <p style="font: 500 12px/1.4 'Inter'; color: var(--accent); margin: var(--sp-sm) 0 var(--sp-xs);">Specialties</p>
        <p style="font: 400 12px/1.5 'Inter'; color: var(--text-secondary);">Heating, Ventilating & Air Conditioning Service</p>
      </div>
    </div>
    <ul class="footer-links">
      <li><a href="#services">Services</a></li>
      <li><a href="#how-it-works">How it works</a></li>
      <li><a href="/register.php">Book now</a></li>
    </ul>
    <div class="footer-contact" style="text-align: right; min-width: 220px;">
      <p style="font: 500 12px/1.4 'Inter'; color: var(--accent); margin-bottom: var(--sp-xs);">Contact Us</p>
      <p style="font: 400 12px/1.6 'Inter'; color: var(--text-secondary); margin-bottom: var(--sp-xs);">
        <a href="tel:+639278180100" style="color: var(--text-secondary); text-decoration: none;">0927 818 0100</a>
      </p>
      <p style="font: 400 12px/1.6 'Inter'; color: var(--text-secondary);">
        <a href="mailto:glorikar.engineering@gmail.com" style="color: var(--text-secondary); text-decoration: none;">glorikar.engineering@gmail.com</a>
      </p>
    </div>
    <span class="footer-copy">© 2026 Glorikar Engineering</span>
  </div>
</footer>

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

  // Source clip is portrait (9:16). To fill a wide hero section without
  // letterboxing, size the iframe up so it always covers the wrapper,
  // then crop the overflow — same math as CSS background-size: cover,
  // done in JS because iframes don't respect object-fit reliably.
  var VIDEO_RATIO = 9 / 16; // width / height of the source video

  function resizeVideoBackground() {
    var w = wrap.offsetWidth;
    var h = wrap.offsetHeight;
    if (!w || !h) return;
    var containerRatio = w / h;
    var targetW, targetH;
    if (containerRatio > VIDEO_RATIO) {
      // Wrapper is relatively wider than the clip — match width, let height overflow
      targetW = w;
      targetH = w / VIDEO_RATIO;
    } else {
      // Wrapper is relatively taller/narrower — match height, let width overflow
      targetH = h;
      targetW = h * VIDEO_RATIO;
    }
    iframe.style.width = targetW + 'px';
    iframe.style.height = targetH + 'px';
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
</body>
</html>