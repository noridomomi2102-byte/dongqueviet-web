<div class="home-report-widget">
    <div class="home-report-widget-head">
        <h3>Phản ánh tin giả,<br>tin xấu độc</h3>
        <span class="home-report-widget-icon" aria-hidden="true">✉</span>
    </div>

    @if(session('success') && request()->routeIs('home'))
        <div class="home-report-success">{{ session('success') }}</div>
    @endif

    @include('frontend.partials.report-form', ['compact' => true])

    <p class="home-report-ref">
        Tham khảo
        <a href="https://tingia.gov.vn/" target="_blank" rel="noopener noreferrer">Trung tâm xử lý tin giả VAFC</a>
    </p>
</div>
