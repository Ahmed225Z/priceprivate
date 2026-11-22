<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        /**
         * PROJECT_SPEC.md §14.3 (بند 7) + §29.1
         * ممنوع N+1 — في بيئات التطوير بنخلي الـ lazy loading يرمي استثناء
         * عشان المشكلة تتمسك دلوقتي مش في الإنتاج.
         */
        Model::preventLazyLoading(! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes(! $this->app->isProduction());

        // التواريخ بالعربي في كل الواجهات (§13.5)
        Carbon::setLocale('ar');
        Date::use(Carbon::class);

        // §18 — HTTPS إجباري خارج بيئة التطوير
        if (! $this->app->environment('local', 'testing')) {
            URL::forceScheme('https');
        }
    }
}
