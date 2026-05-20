<?php

namespace App\Providers;

use App\View\Composers\NavigationComposer;
use App\View\Composers\ReportFormComposer;
use App\View\Composers\TickerComposer;
use Carbon\Carbon;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Carbon::setLocale('vi');

        Paginator::defaultView('vendor.pagination.frontend');

        View::composer('layouts.app', NavigationComposer::class);
        View::composer('layouts.app', TickerComposer::class);
        View::composer([
            'frontend.partials.report-form',
            'frontend.partials.home-sidebar-report',
        ], ReportFormComposer::class);
    }
}
