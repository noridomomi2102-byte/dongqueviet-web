<?php

namespace App\View\Composers;

use App\Support\ReportCaptcha;
use Illuminate\View\View;

class ReportFormComposer
{
    public function compose(View $view): void
    {
        $view->with([
            'reportCategories' => config('report', []),
            'reportCaptchaCode' => ReportCaptcha::current(),
        ]);
    }
}
