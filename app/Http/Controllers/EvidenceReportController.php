<?php

namespace App\Http\Controllers;

use App\Models\EvidenceReport;
use App\Support\ReportCaptcha;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EvidenceReportController extends Controller
{
    public function create(): View
    {
        ReportCaptcha::current();

        return view('frontend.report');
    }

    public function captcha(): View
    {
        $reportCaptchaCode = ReportCaptcha::refresh();

        return view('frontend.partials.report-captcha', compact('reportCaptchaCode'));
    }

    public function store(Request $request): RedirectResponse
    {
        $categories = array_keys(config('report', []));

        $validated = $request->validate([
            'reporter_name' => ['required', 'string', 'max:255'],
            'reporter_email' => ['nullable', 'email', 'max:255'],
            'reporter_phone' => ['required', 'string', 'max:30'],
            'category' => ['required', 'string', Rule::in($categories)],
            'source_url' => ['required', 'url', 'max:500'],
            'description' => ['required', 'string', 'max:5000'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:jpg,jpeg,png,pdf,doc,docx,xls,xlsx,zip,rar'],
            'captcha' => ['required', 'string', 'max:10'],
        ], [
            'reporter_name.required' => 'Vui lòng nhập họ tên.',
            'reporter_phone.required' => 'Vui lòng nhập số điện thoại.',
            'category.required' => 'Vui lòng chọn loại phản ánh.',
            'description.required' => 'Vui lòng nhập nội dung phản ánh.',
            'source_url.required' => 'Vui lòng nhập link phản ánh.',
            'source_url.url' => 'Link không hợp lệ.',
            'captcha.required' => 'Vui lòng nhập mã captcha.',
            'attachment.max' => 'Tệp đính kèm tối đa 10MB.',
        ]);

        if (! ReportCaptcha::validate($validated['captcha'])) {
            ReportCaptcha::refresh();

            return back()
                ->withInput($request->except('captcha', 'attachment'))
                ->withErrors(['captcha' => 'Mã captcha không đúng.']);
        }

        ReportCaptcha::refresh();

        $data = collect($validated)->except(['attachment', 'captcha'])->all();
        $data['category'] = config('report.'.$data['category'], $data['category']);

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('reports', 'public');
        }

        EvidenceReport::create($data);

        $redirect = $request->input('redirect_to') === 'home'
            ? route('home')
            : route('report.create');

        return redirect($redirect)
            ->with('success', 'Đã gửi phản ánh thành công. Chúng tôi sẽ tiếp nhận và xử lý.');
    }
}
