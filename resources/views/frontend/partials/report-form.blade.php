@php
    $compact = $compact ?? false;
    $formId = $compact ? 'sidebarReportForm' : 'reportForm';
    $redirectTo = $compact ? 'home' : 'page';
@endphp

<form
    id="{{ $formId }}"
    class="report-form{{ $compact ? ' report-form--compact' : '' }}"
    action="{{ route('report.store') }}"
    method="post"
    enctype="multipart/form-data"
    novalidate
>
    @csrf
    <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">

    <input
        type="text"
        name="reporter_name"
        value="{{ old('reporter_name') }}"
        placeholder="Họ và tên người phản ánh (*)"
        required
        autocomplete="name"
    >
    @error('reporter_name')<div class="report-form-error">{{ $message }}</div>@enderror

    <input
        type="tel"
        name="reporter_phone"
        value="{{ old('reporter_phone') }}"
        placeholder="Số điện thoại*"
        required
        autocomplete="tel"
    >
    @error('reporter_phone')<div class="report-form-error">{{ $message }}</div>@enderror

    <input
        type="email"
        name="reporter_email"
        value="{{ old('reporter_email') }}"
        placeholder="Địa chỉ email"
        autocomplete="email"
    >
    @error('reporter_email')<div class="report-form-error">{{ $message }}</div>@enderror

    <input
        type="url"
        name="source_url"
        value="{{ old('source_url') }}"
        placeholder="Link phản ánh (*)"
        required
    >
    @error('source_url')<div class="report-form-error">{{ $message }}</div>@enderror

    <select name="category" required>
        @foreach($reportCategories as $key => $label)
            <option value="{{ $key }}" @selected(old('category', 'tin-xau-doc') === $key)>{{ $label }}</option>
        @endforeach
    </select>
    @error('category')<div class="report-form-error">{{ $message }}</div>@enderror

    <textarea
        name="description"
        placeholder="Nhập nội dung phản ánh (*)"
        required
    >{{ old('description') }}</textarea>
    @error('description')<div class="report-form-error">{{ $message }}</div>@enderror

    <div class="report-form-file">
        <input type="file" name="attachment" id="{{ $formId }}_attachment" accept=".png,.jpg,.jpeg,.zip,.rar,.doc,.docx,.xls,.xlsx,.pdf">
    </div>
    <p class="report-form-hint">Các định dạng file hỗ trợ: (PNG, JPG), ZIP, RAR, DOC, DOCX, XLS, XLSX, PDF</p>
    @error('attachment')<div class="report-form-error">{{ $message }}</div>@enderror

    <div class="report-form-captcha">
        <input type="text" name="captcha" placeholder="Nhập mã captcha" maxlength="10" required autocomplete="off">
        <div class="report-captcha-box" id="{{ $formId }}CaptchaBox">
            @include('frontend.partials.report-captcha')
        </div>
        <button type="button" class="report-captcha-refresh" data-captcha-url="{{ route('report.captcha') }}" aria-label="Làm mới mã captcha" title="Làm mới">↻</button>
    </div>
    @error('captcha')<div class="report-form-error">{{ $message }}</div>@enderror

    <button type="submit" class="report-form-submit">
        Gửi phản ánh
        <span class="report-form-submit-icon" aria-hidden="true">➤</span>
    </button>
</form>
