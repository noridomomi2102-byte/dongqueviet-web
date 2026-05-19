@extends('layouts.app')

@section('title', 'Phản ánh tin giả, tin xấu độc - ' . config('app.name'))

@push('styles')
<style>
    .report-page { max-width: 520px; margin: 0 auto; }
    .report-page h1 { font-size: 1.5rem; margin-bottom: .5rem; }
    .report-page .intro { color: var(--muted); margin-bottom: 1.25rem; font-size: 14px; }
    .report-page-box {
        border: 2px solid var(--cand-red);
        border-radius: 12px;
        overflow: hidden;
        background: #fff;
    }
    .report-page-box .home-report-widget-head { border-radius: 0; }
    .report-page-box .report-form { padding: 16px; }
    .alert-success {
        background: #d4edda;
        color: #155724;
        padding: 1rem;
        border-radius: 6px;
        margin-bottom: 1rem;
    }
</style>
@endpush

@section('content')
    <div class="report-page">
        <h1>Phản ánh tin giả, tin xấu độc</h1>
        <p class="intro">
            Tiếp nhận phản ánh về tin sai lệch, tin giả, thông tin xấu độc trên mạng.
            Tham khảo thêm
            <a href="https://tingia.gov.vn/" target="_blank" rel="noopener noreferrer">tingia.gov.vn</a>.
        </p>

        @if(session('success'))
            <div class="alert-success">{{ session('success') }}</div>
        @endif

        <div class="report-page-box">
            <div class="home-report-widget-head">
                <h3>Gửi phản ánh</h3>
                <span class="home-report-widget-icon" aria-hidden="true">✉</span>
            </div>
            @include('frontend.partials.report-form', ['compact' => false])
        </div>
    </div>
@endsection

@push('scripts')
<script>
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
