<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name'))</title>
    <meta name="description" content="@yield('meta_description', 'Tin tức mới nhất')">
    <style>
        :root {
            --cand-red: #d71920;
            --cand-red-dark: #9b0f14;
            --cand-red-nav: #b51218;
            --cand-red-hover: #c0161c;
            --cand-red-light: #fde8e9;
            --primary: var(--cand-red);
            --primary-dark: var(--cand-red-dark);
            --text: #1a1a1a;
            --muted: #666;
            --border: #e0e0e0;
            --bg: #f5f5f5;
            --ticker-bg: #ececec;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            color: var(--text);
            background: #fff;
            line-height: 1.5;
        }
        a { color: inherit; text-decoration: none; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 12px; }

        /* === Hàng 1: Logo === */
        .header-brand {
            background: var(--cand-red);
            color: #fff;
        }
        .brand-inner {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            min-height: 72px;
            padding: 8px 0;
        }
        .brand-logo img {
            height: 60px;
            width: auto;
            max-width: min(100%, 360px);
            object-fit: contain;
            display: block;
        }
        .btn-login {
            justify-self: end;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: rgba(0,0,0,.15);
            color: #fff !important;
            padding: 8px 14px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
        }
        .btn-login:hover { background: rgba(0,0,0,.25); }

        /* === Hàng 2: Menu đỏ đậm === */
        .header-menu {
            background: var(--cand-red-nav);
            color: #fff;
            position: relative;
            z-index: 100;
        }
        .menu-inner {
            display: flex;
            align-items: center;
            height: 44px;
        }
        .menu-toggle {
            display: none;
            align-self: stretch;
            background: none;
            border: none;
            color: #fff;
            font-size: 22px;
            line-height: 1;
            padding: 0 14px;
            cursor: pointer;
            border-right: 1px solid rgba(255,255,255,.15);
        }
        .main-nav {
            display: flex;
            flex: 1;
            align-items: center;
            justify-content: center;
            align-self: stretch;
            height: 100%;
            flex-wrap: nowrap;
            overflow: visible;
        }
        /* Mọi mục menu cùng chiều cao + căn giữa dọc */
        .main-nav > a,
        .nav-item {
            display: flex;
            align-items: center;
            align-self: stretch;
            height: 100%;
            margin: 0;
            box-sizing: border-box;
        }
        .main-nav > a,
        .nav-item > .nav-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 100%;
            min-height: 44px;
            padding: 0 14px;
            font-size: 11px;
            font-weight: 700;
            line-height: 1.25;
            text-transform: uppercase;
            letter-spacing: .02em;
            white-space: nowrap;
            color: #fff !important;
            border-right: 1px solid rgba(255,255,255,.12);
            box-sizing: border-box;
        }
        .main-nav > a:hover,
        .nav-item:hover > .nav-link,
        .nav-item.open > .nav-link {
            background: rgba(0,0,0,.2);
        }
        .nav-item { position: relative; flex-shrink: 0; }
        .nav-item > .nav-link { width: 100%; }
        .nav-item .dropdown {
            display: none;
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 280px;
            background: #fff;
            box-shadow: 0 6px 20px rgba(0,0,0,.2);
            z-index: 300;
            border-top: 3px solid var(--cand-red);
        }
        .nav-item:hover .dropdown,
        .nav-item.open .dropdown { display: block; }
        .nav-item:nth-last-child(-n+2) .dropdown { left: auto; right: 0; }
        .dropdown a {
            display: block;
            padding: 10px 14px;
            font-size: 13px;
            font-weight: 500;
            text-transform: none;
            letter-spacing: 0;
            color: var(--text) !important;
            border-bottom: 1px solid #f0f0f0;
        }
        .dropdown a:hover { background: #fafafa; color: var(--cand-red) !important; }
        .nav-report {
            background: rgba(0,0,0,.2) !important;
            align-self: stretch !important;
        }

        .menu-tools {
            display: flex;
            align-items: center;
            align-self: stretch;
            gap: 0;
            flex-shrink: 0;
            height: 100%;
            border-left: 1px solid rgba(255,255,255,.12);
        }
        .search-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            align-self: stretch;
            background: none;
            border: none;
            color: #fff;
            padding: 0 14px;
            cursor: pointer;
            font-size: 16px;
            line-height: 1;
            height: 100%;
            box-sizing: border-box;
        }
        .search-panel {
            display: none;
            position: absolute;
            right: 12px;
            top: 100%;
            background: #fff;
            padding: 10px;
            box-shadow: 0 4px 16px rgba(0,0,0,.15);
            border-radius: 0 0 4px 4px;
            z-index: 400;
        }
        .search-panel.open { display: flex; gap: 6px; }
        .search-panel input {
            border: 1px solid var(--border);
            padding: 8px 10px;
            width: 220px;
            font-size: 14px;
        }
        .search-panel button {
            background: var(--cand-red);
            color: #fff;
            border: none;
            padding: 8px 14px;
            cursor: pointer;
            font-weight: 600;
            border-radius: 3px;
            transition: background .2s;
        }
        .search-panel button:hover { background: var(--cand-red-hover); }
        .search-panel input:focus {
            outline: none;
            border-color: var(--cand-red);
            box-shadow: 0 0 0 2px rgba(215, 25, 32, .15);
        }

        /* Nút dùng chung — tông đỏ */
        .btn,
        .btn-primary {
            display: inline-block;
            background: var(--cand-red);
            color: #fff !important;
            border: none;
            padding: .6rem 1.25rem;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            text-align: center;
            transition: background .2s, box-shadow .2s;
        }
        .btn:hover,
        .btn-primary:hover {
            background: var(--cand-red-hover);
            color: #fff !important;
        }
        .btn-outline {
            display: inline-block;
            background: transparent;
            color: var(--cand-red) !important;
            border: 2px solid var(--cand-red);
            padding: calc(.6rem - 2px) calc(1.25rem - 2px);
            border-radius: 4px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: background .2s, color .2s;
        }
        .btn-outline:hover {
            background: var(--cand-red);
            color: #fff !important;
        }
        .lang-badge {
            display: inline-flex;
            align-items: center;
            align-self: stretch;
            height: 100%;
            padding: 0 14px;
            font-size: 12px;
            font-weight: 700;
            line-height: 1;
            border-left: 1px solid rgba(255,255,255,.12);
            gap: 4px;
            box-sizing: border-box;
        }

        /* === Hàng 3: Ticker === */
        .header-ticker {
            background: var(--ticker-bg);
            border-bottom: 1px solid var(--border);
            font-size: 12px;
            overflow: hidden;
        }
        .ticker-inner {
            display: flex;
            align-items: center;
            gap: 10px;
            height: 36px;
            overflow: hidden;
        }
        .ticker-label {
            color: var(--cand-red);
            font-weight: 700;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .ticker-marquee {
            flex: 1;
            overflow: hidden;
            min-width: 0;
            position: relative;
        }
        .ticker-marquee::before,
        .ticker-marquee::after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            width: 24px;
            z-index: 1;
            pointer-events: none;
        }
        .ticker-marquee::before {
            left: 0;
            background: linear-gradient(to right, var(--ticker-bg), transparent);
        }
        .ticker-marquee::after {
            right: 0;
            background: linear-gradient(to left, var(--ticker-bg), transparent);
        }
        .ticker-track {
            display: inline-flex;
            align-items: center;
            white-space: nowrap;
            animation: ticker-scroll 55s linear infinite;
            will-change: transform;
        }
        .ticker-marquee:hover .ticker-track {
            animation-play-state: paused;
        }
        @keyframes ticker-scroll {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .ticker-track a {
            color: var(--text);
            padding-right: 28px;
            flex-shrink: 0;
        }
        .ticker-track a:hover { color: var(--cand-red); }
        .ticker-track a::before { content: '★ '; color: var(--cand-red); font-size: 10px; }
        .ticker-empty {
            color: #888;
            padding-right: 28px;
            flex-shrink: 0;
            white-space: nowrap;
        }
        .ticker-meta {
            color: var(--muted);
            white-space: nowrap;
            flex-shrink: 0;
            font-size: 11px;
            font-variant-numeric: tabular-nums;
            min-width: 210px;
            text-align: right;
        }

        /* Mobile menu drawer */
        .mobile-drawer {
            display: none;
            position: fixed;
            inset: 0;
            z-index: 500;
        }
        .mobile-drawer.open { display: block; }
        .drawer-backdrop {
            position: absolute;
            inset: 0;
            background: rgba(0,0,0,.5);
        }
        .drawer-panel {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: min(300px, 85vw);
            background: #fff;
            overflow-y: auto;
            padding-bottom: 2rem;
        }
        .drawer-panel a {
            display: block;
            padding: 12px 16px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .drawer-panel .drawer-parent {
            background: var(--cand-red);
            color: #fff !important;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
        }
        .drawer-panel .drawer-child {
            padding-left: 28px;
            font-size: 13px;
        }

        main { padding: 1.5rem 0 2.5rem; min-height: 50vh; }

        /* === Footer (kiểu cand.vn) === */
        .site-footer {
            background: #f5f5f5;
            color: #555;
            padding: 28px 0 32px;
            font-size: 13px;
            line-height: 1.55;
            border-top: 1px solid var(--border);
        }
        .footer-inner {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 24px 32px;
        }
        .footer-main { flex: 1; min-width: 0; }
        .footer-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 14px;
            text-decoration: none;
            color: inherit;
        }
        .footer-brand:hover { opacity: .92; }
        .footer-logo-wrap {
            background: var(--cand-red);
            padding: 6px 12px;
            border-radius: 4px;
            line-height: 0;
        }
        .footer-logo-wrap img {
            height: 36px;
            width: auto;
            max-width: 200px;
            object-fit: contain;
            display: block;
        }
        .footer-brand-name {
            font-size: 22px;
            font-weight: 700;
            color: var(--cand-red);
            letter-spacing: -.02em;
            line-height: 1.2;
        }
        .footer-info p {
            margin: 0 0 6px;
            color: #666;
        }
        .footer-info p:last-child { margin-bottom: 0; }
        .footer-line {
            display: flex;
            align-items: flex-start;
            gap: 8px;
            margin-top: 10px !important;
        }
        .footer-line svg {
            flex-shrink: 0;
            margin-top: 2px;
            color: #888;
        }
        .footer-aside {
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }
        .footer-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            background: #fff;
            border: 1px solid #d8d8d8;
            border-radius: 999px;
            color: #444;
            font-size: 12px;
            font-weight: 600;
            letter-spacing: .02em;
            text-decoration: none;
            white-space: nowrap;
            transition: border-color .15s, box-shadow .15s;
        }
        .footer-pill:hover {
            border-color: #bbb;
            box-shadow: 0 2px 8px rgba(0,0,0,.06);
            color: var(--cand-red);
        }
        .footer-pill svg { flex-shrink: 0; color: #888; }
        .footer-pill:hover svg { color: var(--cand-red); }
        .footer-distribution {
            margin: 4px 0 0;
            font-size: 12px;
            color: #777;
            text-align: right;
        }
        .footer-demo-note {
            margin-top: 12px;
            font-size: 11px;
            color: #aaa;
            font-style: italic;
        }
        @media (max-width: 768px) {
            .footer-inner {
                flex-direction: column;
            }
            .footer-aside {
                align-items: stretch;
                width: 100%;
            }
            .footer-pill {
                justify-content: center;
                white-space: normal;
                text-align: center;
            }
            .footer-distribution { text-align: center; }
            .footer-brand-name { font-size: 18px; }
        }
        .badge {
            display: inline-block;
            background: var(--cand-red);
            color: #fff;
            font-size: .75rem;
            padding: .2rem .6rem;
            border-radius: 3px;
            font-weight: 600;
        }
        .grid { display: grid; gap: 1.5rem; }
        .grid-3 { grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); }
        .pagination { margin-top: 2rem; display: flex; justify-content: center; align-items: center; gap: .5rem; flex-wrap: wrap; }
        .pagination a, .pagination span {
            display: inline-block;
            padding: .5rem .85rem;
            border: 1px solid var(--border);
            border-radius: 4px;
            font-size: .9rem;
            line-height: 1.2;
            text-decoration: none;
            color: inherit;
        }
        .pagination span.active { background: var(--cand-red); color: #fff; border-color: var(--cand-red); font-weight: 600; }
        .pagination span.disabled { opacity: .45; cursor: default; }
        h1 { font-size: 1.75rem; margin: 0 0 1rem; line-height: 1.3; }
        .page-title { margin-bottom: 1.5rem; padding-bottom: .75rem; border-bottom: 3px solid var(--cand-red); }
        a:hover { color: var(--cand-red); }

        @media (max-width: 992px) {
            .menu-toggle { display: inline-flex; align-items: center; justify-content: center; }
            .main-nav { display: none; }
            .ticker-meta { min-width: 0; font-size: 10px; max-width: 42%; }
        }
        @media (max-width: 640px) {
            .brand-inner { min-height: 60px; }
            .brand-logo img { height: 44px; max-width: 260px; }
            .btn-login span { display: none; }
        }
    </style>
    @stack('styles')
</head>
<body>
    <header class="site-header">
        {{-- Hàng 1: Logo giữa --}}
        <div class="header-brand">
            <div class="container brand-inner">
                <div></div>
                <a href="{{ route('home') }}" class="brand-logo">
                    <img src="{{ asset('images/logo-dong-que-viet.png') }}?v={{ @filemtime(public_path('images/logo-dong-que-viet.png')) ?: 2 }}" alt="Đồng Quê Việt" width="340" height="60" decoding="async">
                </a>
                <a href="{{ url('/admin') }}" class="btn-login">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/></svg>
                    <span>Đăng nhập</span>
                </a>
            </div>
        </div>

        {{-- Hàng 2: Menu --}}
        <div class="header-menu">
            <div class="container menu-inner">
                <button type="button" class="menu-toggle" id="menuToggle" aria-label="Mở menu">☰</button>

                <nav class="main-nav" id="mainNav">
                    <a href="{{ route('home') }}">Trang chủ</a>
                    @foreach($navCategories ?? [] as $cat)
                        @if($cat->activeChildren->isNotEmpty())
                            <div class="nav-item">
                                <a href="{{ route('category.show', $cat->slug) }}" class="nav-link">{{ $cat->name }}</a>
                                <div class="dropdown">
                                    <a href="{{ route('category.show', $cat->slug) }}">Tất cả: {{ $cat->name }}</a>
                                    @foreach($cat->activeChildren as $child)
                                        <a href="{{ route('category.show', $child->slug) }}">{{ $child->name }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @else
                            <a href="{{ route('category.show', $cat->slug) }}">{{ $cat->name }}</a>
                        @endif
                    @endforeach
                    <a href="{{ route('report.create') }}" class="nav-report">Gửi báo cáo</a>
                </nav>

                <div class="menu-tools">
                    <button type="button" class="search-toggle" id="searchToggle" aria-label="Tìm kiếm">🔍</button>
                    <span class="lang-badge">🇻🇳 VI</span>
                    <form class="search-panel" id="searchPanel" action="{{ route('search') }}" method="get">
                        <input type="search" name="q" value="{{ request('q') }}" placeholder="Tìm kiếm...">
                        <button type="submit">Tìm</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Hàng 3: Chủ đề / ticker --}}
        <div class="header-ticker">
            <div class="container ticker-inner">
                <span class="ticker-label">Chủ đề:</span>
                <div class="ticker-marquee" aria-label="Tin nổi bật">
                    <div class="ticker-track">
                        @forelse($tickerPosts ?? [] as $tp)
                            <a href="{{ route('post.show', $tp->slug) }}">{{ $tp->title }}</a>
                        @empty
                            <span class="ticker-empty">Chưa có tin mới</span>
                        @endforelse
                        @if(isset($tickerPosts) && $tickerPosts->isNotEmpty())
                            @foreach($tickerPosts as $tp)
                                <a href="{{ route('post.show', $tp->slug) }}">{{ $tp->title }}</a>
                            @endforeach
                        @endif
                    </div>
                </div>
                <div class="ticker-meta" id="tickerClock" aria-live="off">
                    {{ now()->timezone('Asia/Ho_Chi_Minh')->locale('vi')->isoFormat('dddd, DD/MM/YYYY, HH:mm:ss') }} GMT+7
                </div>
            </div>
        </div>
    </header>

    {{-- Menu mobile --}}
    <div class="mobile-drawer" id="mobileDrawer">
        <div class="drawer-backdrop" id="drawerBackdrop"></div>
        <nav class="drawer-panel">
            <a href="{{ route('home') }}" class="drawer-parent">Trang chủ</a>
            @foreach($navCategories ?? [] as $cat)
                <a href="{{ route('category.show', $cat->slug) }}" class="drawer-parent">{{ $cat->name }}</a>
                @foreach($cat->activeChildren as $child)
                    <a href="{{ route('category.show', $child->slug) }}" class="drawer-child">{{ $child->name }}</a>
                @endforeach
            @endforeach
            <a href="{{ route('report.create') }}" class="drawer-parent">Gửi báo cáo</a>
        </nav>
    </div>

    <main class="container">
        @yield('content')
    </main>

    <footer class="site-footer">
        <div class="container footer-inner">
            <div class="footer-main">
                <a href="{{ route('home') }}" class="footer-brand">
                    <span class="footer-logo-wrap">
                        <img src="{{ asset('images/logo-dong-que-viet.png') }}?v={{ @filemtime(public_path('images/logo-dong-que-viet.png')) ?: 2 }}" alt="Đồng Quê Việt" height="36">
                    </span>
                    <span class="footer-brand-name">Đồng Quê Việt</span>
                </a>
                <div class="footer-info">
                    <p><strong>Giấy phép hoạt động báo chí số 01/GP-BVHTTDL (demo)</strong>, cấp ngày: 01/01/2026 của Bộ Văn hóa, Thể thao và Du lịch</p>
                    <p><strong>Tổng Biên tập:</strong> Nguyễn Văn Demo</p>
                    <p><strong>Phó Tổng Biên tập:</strong> Trần Thị Demo, Lê Văn Demo, Phạm Thị Demo</p>
                    <p class="footer-line">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>
                        <span><strong>Trụ sở Tòa soạn:</strong> Số 10 Đường Demo, phường Demo, thành phố Hà Nội (thông tin demo)</span>
                    </p>
                    <p class="footer-line">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4-8 5-8-5V6l8 5 8-5v2z"/></svg>
                        <span><strong>Email:</strong> <a href="mailto:toasoan@dongqueviet.demo">toasoan@dongqueviet.demo</a></span>
                    </p>
                    <p class="footer-demo-note">* Các thông tin giấy phép, nhân sự và liên hệ phía trên chỉ mang tính minh họa giao diện.</p>
                </div>
            </div>
            <aside class="footer-aside">
                <a href="#" class="footer-pill" onclick="return false;" title="Demo">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>
                    CƠ QUAN ĐẠI DIỆN
                </a>
                <a href="tel:02412345678" class="footer-pill">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.01-.24 11.36 11.36 0 0 0 3.56.57 1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1 11.36 11.36 0 0 0 .57 3.56 1 1 0 0 1-.25 1.01l-2.2 2.22z"/></svg>
                    QUẢNG CÁO: 024.1234.5678
                </a>
                <p class="footer-distribution">Phát hành: 024.9876.5432 (demo)</p>
            </aside>
        </div>
    </footer>

    <script>
        document.getElementById('menuToggle')?.addEventListener('click', () => {
            document.getElementById('mobileDrawer')?.classList.add('open');
        });
        document.getElementById('drawerBackdrop')?.addEventListener('click', () => {
            document.getElementById('mobileDrawer')?.classList.remove('open');
        });
        document.getElementById('searchToggle')?.addEventListener('click', (e) => {
            e.stopPropagation();
            document.getElementById('searchPanel')?.classList.toggle('open');
        });
        document.addEventListener('click', () => {
            document.getElementById('searchPanel')?.classList.remove('open');
        });
        // Touch: mở dropdown trên mobile nav-item
        document.querySelectorAll('.nav-item > .nav-link').forEach(link => {
            link.addEventListener('click', function(e) {
                if (window.innerWidth > 992) return;
                const item = this.parentElement;
                if (item.querySelector('.dropdown')) {
                    e.preventDefault();
                    item.classList.toggle('open');
                }
            });
        });

        const tickerClock = document.getElementById('tickerClock');
        if (tickerClock) {
            const days = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];
            const pad = (n) => String(n).padStart(2, '0');
            const tick = () => {
                const now = new Date();
                const vn = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Ho_Chi_Minh' }));
                tickerClock.textContent = `${days[vn.getDay()]}, ${pad(vn.getDate())}/${pad(vn.getMonth() + 1)}/${vn.getFullYear()}, ${pad(vn.getHours())}:${pad(vn.getMinutes())}:${pad(vn.getSeconds())} GMT+7`;
            };
            tick();
            setInterval(tick, 1000);
        }
    </script>
    @stack('scripts')
</body>
</html>
