@extends('layouts.app')

@section('title', config('app.name') . ' - Trang chủ')

@push('styles')
<style>
    /* === Block hero dưới menu (kiểu cand.vn) === */
    .home-hero-block {
        margin: 0 0 2rem;
    }

    .home-hero-grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 12px;
        align-items: stretch;
    }

    .hero-featured {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-height: 420px;
    }

    .hero-featured-top {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 8px;
        flex: 1;
        min-height: 0;
    }

    .hero-carousel {
        position: relative;
        overflow: hidden;
        border-radius: 2px;
        background: #111;
        min-height: 280px;
    }

    .hero-carousel-track {
        display: flex;
        height: 100%;
        transition: transform .45s ease;
    }

    .hero-carousel-slide {
        flex: 0 0 100%;
        min-width: 100%;
        height: 100%;
    }

    .hero-carousel-btn {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        z-index: 3;
        width: 36px;
        height: 48px;
        border: none;
        background: rgba(255,255,255,.85);
        color: #333;
        font-size: 28px;
        line-height: 1;
        cursor: pointer;
        opacity: 0;
        transition: opacity .2s;
    }

    .hero-carousel:hover .hero-carousel-btn { opacity: 1; }
    .hero-carousel-btn:hover { background: #fff; }
    .hero-carousel-prev { left: 0; border-radius: 0 4px 4px 0; }
    .hero-carousel-next { right: 0; border-radius: 4px 0 0 4px; }

    .hero-carousel-dots {
        position: absolute;
        bottom: 10px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 3;
        display: flex;
        gap: 6px;
    }

    .hero-carousel-dot {
        width: 8px;
        height: 8px;
        padding: 0;
        border: none;
        border-radius: 50%;
        background: rgba(255,255,255,.5);
        cursor: pointer;
    }

    .hero-carousel-dot.is-active { background: var(--cand-red); box-shadow: 0 0 0 2px #fff; }

    .hero-featured-side {
        display: flex;
        flex-direction: column;
        gap: 8px;
        min-height: 0;
    }

    .hero-featured-bottom {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 8px;
        height: 130px;
    }

    .hero-tile {
        position: relative;
        display: block;
        overflow: hidden;
        border-radius: 2px;
        background: #222;
        height: 100%;
        min-height: 0;
    }

    .hero-tile img,
    .hero-tile-placeholder {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .hero-tile-placeholder {
        background: linear-gradient(135deg, #555, #333);
    }

    .hero-tile-overlay {
        position: absolute;
        inset: 0;
        background: linear-gradient(to top, rgba(0,0,0,.85) 0%, rgba(0,0,0,.2) 45%, transparent 70%);
        pointer-events: none;
    }

    .hero-tile-title {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 2;
        padding: 12px 14px;
        color: #fff;
        font-size: 15px;
        font-weight: 700;
        line-height: 1.35;
        text-shadow: 0 1px 2px rgba(0,0,0,.4);
    }

    .hero-tile--main .hero-tile-title { font-size: 18px; padding: 16px 18px; }
    .hero-tile--side { flex: 1; min-height: 0; }
    .hero-tile--bottom { height: 100%; }
    .hero-tile--bottom .hero-tile-title { font-size: 13px; padding: 10px 12px; }

    .hero-tile:hover .hero-tile-title { color: #ffe0e0; }

    /* Sidebar tab */
    .hero-sidebar {
        display: flex;
        flex-direction: column;
        border: 1px solid #ddd;
        border-radius: 2px;
        overflow: hidden;
        background: #fff;
        min-height: 420px;
    }

    .hero-sidebar-tabs {
        display: grid;
        grid-template-columns: 1fr 1fr;
    }

    .hero-tab {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 12px 8px;
        border: none;
        background: #e8e8e8;
        color: #444;
        font-size: 12px;
        font-weight: 700;
        text-transform: uppercase;
        cursor: pointer;
        transition: background .2s, color .2s;
    }

    .hero-tab.is-active {
        background: var(--cand-red);
        color: #fff;
    }

    .hero-tab:not(.is-active):hover {
        background: var(--cand-red-light);
        color: var(--cand-red-dark);
    }

    .hero-sidebar-panel {
        flex: 1;
        overflow: hidden;
    }

    .hero-sidebar-list {
        list-style: none;
        margin: 0;
        padding: 0;
        max-height: 100%;
        overflow-y: auto;
    }

    .hero-sidebar-list li {
        border-bottom: 1px solid #eee;
    }

    .hero-sidebar-list li:last-child { border-bottom: none; }

    .hero-sidebar-list a {
        display: block;
        padding: 11px 14px 11px 28px;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.45;
        color: #222;
        position: relative;
    }

    .hero-sidebar-list a::before {
        content: '›';
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: var(--cand-red);
        font-size: 18px;
        font-weight: 700;
        line-height: 1;
    }

    .hero-sidebar-list a:hover {
        background: #f8f8f8;
        color: var(--cand-red);
    }

    .hero-sidebar-empty {
        padding: 1.5rem;
        color: #888;
        font-size: 13px;
        text-align: center;
    }

    /* === Các chuyên mục dưới hero === */
    .home-sections-layout {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 20px;
        align-items: start;
        margin-top: 1.5rem;
    }

    .home-sections-main--news { grid-column: 1; grid-row: 1; }
    .home-sections-main--opinion { grid-column: 1; grid-row: 2; }
    .home-sections-aside { grid-column: 2; grid-row: 1; align-self: start; }
    .home-sections-layout--paired .home-sections-aside { grid-row: 1 / 3; }
    .home-banner-ad { grid-column: 1 / -1; }

    .home-sections-main--news,
    .home-sections-main--opinion { min-width: 0; }

    .home-section-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--cand-red);
        color: #fff;
        padding: 10px 14px;
        margin-bottom: 12px;
    }

    .home-section-title {
        margin: 0;
        font-size: 14px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .03em;
    }

    .home-section-more {
        font-size: 12px;
        font-weight: 600;
        color: rgba(255,255,255,.92);
        white-space: nowrap;
    }

    .home-section-more:hover { color: #fff; text-decoration: underline; }

    .home-thumb {
        display: block;
        overflow: hidden;
        background: #ddd;
        flex-shrink: 0;
    }

    .home-thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .home-thumb-ph { display: block; width: 100%; height: 100%; background: linear-gradient(135deg, #ccc, #999); }

    .home-thumb--lg { width: 100%; aspect-ratio: 16/10; }
    .home-thumb--md { width: 100%; aspect-ratio: 16/10; }
    .home-thumb--sm { width: 88px; height: 66px; }

    /* Tin tức & Thời sự */
    .home-news-body {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 16px;
        align-items: start;
    }

    .home-news-feature-link { display: block; color: inherit; }
    .home-news-feature-link:hover h3 { color: var(--cand-red); }

    .home-news-feature-title {
        margin: 12px 0 8px;
        font-size: 20px;
        font-weight: 800;
        line-height: 1.35;
    }

    .home-news-feature-excerpt {
        margin: 0;
        font-size: 14px;
        line-height: 1.6;
        color: var(--muted);
    }

    .home-news-list {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        gap: 0;
        border: 1px solid #eee;
    }

    .home-news-list li { border-bottom: 1px solid #eee; }
    .home-news-list li:last-child { border-bottom: none; }

    .home-news-list-item {
        display: flex;
        gap: 10px;
        padding: 10px;
        align-items: flex-start;
        color: inherit;
    }

    .home-news-list-item:hover { background: #fafafa; }
    .home-news-list-item:hover .home-news-list-title { color: var(--cand-red); }

    .home-news-list-title {
        font-size: 13px;
        font-weight: 700;
        line-height: 1.45;
        flex: 1;
    }

    /* Góc nhìn & Phản biện */
    .home-opinion-cols {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
    }

    .home-opinion-col { min-width: 0; }

    .home-opinion-feature a {
        display: block;
        color: inherit;
        margin-bottom: 12px;
    }

    .home-opinion-feature h3 {
        margin: 10px 0 6px;
        font-size: 16px;
        font-weight: 800;
        line-height: 1.35;
    }

    .home-opinion-feature p {
        margin: 0;
        font-size: 13px;
        color: var(--muted);
        line-height: 1.55;
    }

    .home-opinion-feature a:hover h3 { color: var(--cand-red); }

    .home-opinion-list {
        list-style: none;
        margin: 0;
        padding: 0;
        border-top: 1px solid #eee;
    }

    .home-opinion-list li { border-bottom: 1px solid #eee; }

    .home-opinion-list a {
        display: flex;
        gap: 10px;
        padding: 10px 0;
        align-items: flex-start;
        color: inherit;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
    }

    .home-opinion-list a:hover span { color: var(--cand-red); }

    /* Sidebar */
    .home-sections-aside { display: flex; flex-direction: column; gap: 14px; }

    .home-section--verify-compact .home-section-head { margin-bottom: 8px; }

    .home-verify-list {
        list-style: none;
        margin: 0 0 4px;
        padding: 0;
        border: 1px solid #eee;
        background: #fff;
    }

    .home-verify-list li { border-bottom: 1px solid #eee; }
    .home-verify-list li:last-child { border-bottom: none; }

    .home-verify-list a {
        display: block;
        padding: 8px 10px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
        color: inherit;
    }

    .home-verify-list a:hover { color: var(--cand-red); }

    /* Form phản ánh tin giả (kiểu tingia.gov.vn) */
    .home-report-widget {
        border: 2px solid var(--cand-red);
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,.06);
    }

    .home-report-widget-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        background: var(--cand-red);
        color: #fff;
        padding: 12px 14px;
    }

    .home-report-widget-head h3 {
        margin: 0;
        font-size: 13px;
        font-weight: 800;
        line-height: 1.35;
        text-transform: uppercase;
    }

    .home-report-widget-icon {
        font-size: 22px;
        opacity: .95;
        flex-shrink: 0;
    }

    .home-report-success {
        margin: 10px 12px 0;
        padding: 8px 10px;
        background: #d4edda;
        color: #155724;
        font-size: 12px;
        border-radius: 6px;
    }

    .home-report-ref {
        margin: 0;
        padding: 8px 12px 12px;
        font-size: 11px;
        color: var(--muted);
        text-align: center;
    }

    .home-report-ref a { color: var(--cand-red); text-decoration: underline; }

    .report-form {
        padding: 12px;
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .report-form--compact { padding: 10px 12px 6px; }

    .report-form input,
    .report-form select,
    .report-form textarea {
        width: 100%;
        padding: 8px 10px;
        border: 1px solid var(--cand-red);
        border-radius: 6px;
        font: inherit;
        font-size: 12px;
        background: #fff;
        color: var(--text);
    }

    .report-form textarea {
        min-height: 72px;
        resize: vertical;
    }

    .report-form select { cursor: pointer; }

    .report-form-hint {
        margin: -4px 0 0;
        font-size: 10px;
        color: #888;
        line-height: 1.4;
    }

    .report-form-file input[type="file"] {
        font-size: 11px;
        padding: 6px;
        border-style: dashed;
    }

    .report-form-captcha {
        display: grid;
        grid-template-columns: 1fr auto 32px;
        gap: 6px;
        align-items: center;
    }

    .report-captcha-box {
        min-width: 72px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f5f5f5, #e8e8e8);
        border: 1px solid #ccc;
        border-radius: 4px;
        letter-spacing: 3px;
        font-weight: 800;
        font-size: 14px;
        color: #333;
        user-select: none;
    }

    .report-captcha-refresh {
        width: 32px;
        height: 34px;
        border: 1px solid var(--cand-red);
        border-radius: 6px;
        background: #fff;
        color: var(--cand-red);
        font-size: 18px;
        line-height: 1;
        cursor: pointer;
        padding: 0;
    }

    .report-captcha-refresh:hover { background: var(--cand-red-light); }

    .report-form-submit {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        width: 100%;
        margin-top: 4px;
        padding: 10px 14px;
        border: none;
        border-radius: 24px;
        background: var(--cand-red);
        color: #fff;
        font-size: 13px;
        font-weight: 800;
        text-transform: uppercase;
        cursor: pointer;
        transition: background .2s;
    }

    .report-form-submit:hover { background: var(--cand-red-hover); }

    .report-form-submit-icon { font-size: 14px; }

    .report-form-error {
        margin: -4px 0 0;
        font-size: 11px;
        color: #c41e3a;
    }

    .home-banner-ad {
        grid-column: 1 / -1;
        margin-top: 4px;
        line-height: 0;
        overflow: hidden;
        border-radius: 2px;
        background: #f5f5f5;
    }

    .home-banner-ad a {
        display: block;
        color: inherit;
    }

    .home-banner-ad img {
        display: block;
        width: 100%;
        height: auto;
        aspect-ratio: 1360 / 105;
        object-fit: cover;
        object-position: center;
    }

    .home-verify-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .home-verify-item {
        display: block;
        color: inherit;
        border: 1px solid #eee;
    }

    .home-verify-item:hover h4 { color: var(--cand-red); }

    .home-verify-item h4 {
        margin: 0;
        padding: 8px 10px 10px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.4;
    }

    /* 3 cột dưới banner quảng cáo */
    .home-bottom-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 24px;
        margin-top: 1.75rem;
        padding-top: 1.25rem;
        border-top: 1px solid #e5e5e5;
    }

    .home-bottom-col { min-width: 0; }

    .home-bottom-col-head {
        margin-bottom: 12px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e8e8e8;
    }

    .home-bottom-col-title {
        margin: 0 0 8px;
        font-size: 15px;
        font-weight: 800;
        line-height: 1.3;
        text-transform: uppercase;
    }

    .home-bottom-col-title a {
        color: var(--cand-red);
        text-decoration: none;
    }

    .home-bottom-col-title a:hover { color: var(--cand-red-dark); }

    .home-bottom-feature { margin-bottom: 12px; }

    .home-bottom-feature a {
        display: block;
        color: inherit;
    }

    .home-bottom-feature h3 {
        margin: 10px 0 0;
        font-size: 15px;
        font-weight: 800;
        line-height: 1.4;
    }

    .home-bottom-feature a:hover h3 { color: var(--cand-red); }

    .home-bottom-list {
        list-style: none;
        margin: 0;
        padding: 0;
        border-top: 1px solid #eee;
    }

    .home-bottom-list li {
        border-bottom: 1px solid #eee;
    }

    .home-bottom-list-item {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 10px 0;
        color: inherit;
    }

    .home-bottom-list-item:hover .home-bottom-list-title { color: var(--cand-red); }

    .home-bottom-list-title {
        flex: 1;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.45;
        min-width: 0;
    }

    .home-bottom-list .home-thumb--sm {
        width: 72px;
        height: 54px;
        order: 2;
        flex-shrink: 0;
    }

    .home-bottom-empty {
        margin: 0;
        font-size: 13px;
        color: #999;
    }

    @media (max-width: 992px) {
        .home-bottom-grid { grid-template-columns: 1fr; gap: 2rem; }
        .home-sections-layout { grid-template-columns: 1fr; }
        .home-sections-main--news,
        .home-sections-main--opinion,
        .home-sections-aside,
        .home-banner-ad {
            grid-column: 1;
            grid-row: auto;
        }
        .home-news-body { grid-template-columns: 1fr; }
        .home-opinion-cols { grid-template-columns: 1fr; }
        .home-hero-grid {
            grid-template-columns: 1fr;
        }

        .hero-sidebar {
            min-height: auto;
            max-height: 400px;
        }

        .hero-featured-top {
            grid-template-columns: 1fr;
        }

        .hero-featured-side {
            flex-direction: row;
            height: 140px;
        }

        .hero-carousel {
            min-height: 240px;
        }
    }

    @media (max-width: 640px) {
        .hero-featured-bottom {
            grid-template-columns: 1fr;
            height: auto;
        }

        .hero-tile--bottom {
            min-height: 120px;
        }

        .hero-featured-side {
            flex-direction: column;
            height: auto;
        }

        .hero-tile--side {
            min-height: 120px;
        }
    }
</style>
@endpush

@section('content')
    @include('frontend.partials.home-hero-block')

    @include('frontend.partials.home-sections')

    @include('frontend.partials.home-bottom-columns')
@endsection

@push('scripts')
<script>
(function () {
    const carousel = document.getElementById('heroCarousel');
    if (!carousel) return;

    const track = carousel.querySelector('.hero-carousel-track');
    const slides = carousel.querySelectorAll('.hero-carousel-slide');
    const dots = carousel.querySelectorAll('.hero-carousel-dot');
    const prev = carousel.querySelector('.hero-carousel-prev');
    const next = carousel.querySelector('.hero-carousel-next');
    let index = 0;
    let timer;

    function goTo(i) {
        if (!slides.length) return;
        index = (i + slides.length) % slides.length;
        track.style.transform = 'translateX(-' + (index * 100) + '%)';
        dots.forEach((d, n) => d.classList.toggle('is-active', n === index));
    }

    function startAuto() {
        stopAuto();
        if (slides.length > 1) {
            timer = setInterval(() => goTo(index + 1), 5000);
        }
    }

    function stopAuto() {
        if (timer) clearInterval(timer);
    }

    prev?.addEventListener('click', () => { goTo(index - 1); startAuto(); });
    next?.addEventListener('click', () => { goTo(index + 1); startAuto(); });
    dots.forEach(dot => {
        dot.addEventListener('click', () => {
            goTo(parseInt(dot.dataset.index, 10));
            startAuto();
        });
    });

    carousel.addEventListener('mouseenter', stopAuto);
    carousel.addEventListener('mouseleave', startAuto);

    goTo(0);
    startAuto();

    // Tabs sidebar
    const tabs = document.querySelectorAll('.hero-tab');
    const panels = document.querySelectorAll('.hero-sidebar-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const name = tab.dataset.tab;
            tabs.forEach(t => {
                t.classList.toggle('is-active', t === tab);
                t.setAttribute('aria-selected', t === tab ? 'true' : 'false');
            });
            panels.forEach(p => {
                const show = p.dataset.panel === name;
                p.classList.toggle('is-active', show);
                p.hidden = !show;
            });
        });
    });
})();

document.querySelectorAll('.report-captcha-refresh').forEach(btn => {
    btn.addEventListener('click', async () => {
        const url = btn.dataset.captchaUrl;
        const form = btn.closest('form');
        const box = form?.querySelector('[id$="CaptchaBox"]');
        if (!url || !box) return;
        btn.disabled = true;
        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            if (res.ok) box.innerHTML = await res.text();
        } finally {
            btn.disabled = false;
        }
    });
});
</script>
@endpush
