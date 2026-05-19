from pathlib import Path

p = Path(__file__).resolve().parent.parent / "resources/views/layouts/app.blade.php"
t = p.read_text(encoding="utf-8")

old = """                <motion class="ticker-list">
                    @forelse($tickerPosts ?? [] as $tp)
                        <a href="{{ route('post.show', $tp->slug) }}">{{ $tp->title }}</a>
                    @empty
                        <span style="color:#888">Chưa có tin mới</span>
                    @endforelse
                </motion>
                <motion class="ticker-meta">
                    {{ now()->locale('vi')->isoFormat('dddd, DD/MM/YYYY, HH:mm:ss') }} GMT+7
                </motion>""".replace("motion", "div")

new = """                <motion class="ticker-marquee">
                    @if(($tickerPosts ?? collect())->isNotEmpty())
                        <motion class="ticker-track">
                            @foreach($tickerPosts as $tp)
                                <a href="{{ route('post.show', $tp->slug) }}">{{ $tp->title }}</a>
                            @endforeach
                            @foreach($tickerPosts as $tp)
                                <a href="{{ route('post.show', $tp->slug) }}" aria-hidden="true">{{ $tp->title }}</a>
                            @endforeach
                        </motion>
                    @else
                        <span style="color:#888;padding-left:4px">Chưa có tin mới</span>
                    @endif
                </motion>
                <motion class="ticker-meta" id="tickerClock" aria-live="polite">
                    {{ now()->locale('vi')->isoFormat('dddd, DD/MM/YYYY, HH:mm:ss') }} GMT+7
                </motion>""".replace("motion", "div")

if old not in t:
    raise SystemExit("ticker block not found")

t = t.replace(old, new, 1)

clock_js = """
        // Đồng hồ ticker (GMT+7)
        (function () {
            const clock = document.getElementById('tickerClock');
            if (!clock) return;
            const days = ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy'];
            const pad = (n) => String(n).padStart(2, '0');
            function tick() {
                const now = new Date();
                const utc = now.getTime() + now.getTimezoneOffset() * 60000;
                const vn = new Date(utc + 7 * 3600000);
                clock.textContent = days[vn.getDay()] + ', '
                    + pad(vn.getDate()) + '/' + pad(vn.getMonth() + 1) + '/' + vn.getFullYear() + ', '
                    + pad(vn.getHours()) + ':' + pad(vn.getMinutes()) + ':' + pad(vn.getSeconds()) + ' GMT+7';
            }
            tick();
            setInterval(tick, 1000);
        })();
"""

if "tickerClock" not in t or "function tick()" not in t:
    t = t.replace("    </script>\n    @stack('scripts')", clock_js + "    </script>\n    @stack('scripts')", 1)

p.write_text(t, encoding="utf-8")
print("ok")
