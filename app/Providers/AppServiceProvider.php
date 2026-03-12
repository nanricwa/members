<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Xserver: public_html をドキュメントルートとして使う場合
        // DOCUMENT_ROOT が public_html を指しているかチェック
        if (isset($_SERVER['DOCUMENT_ROOT']) && str_contains($_SERVER['DOCUMENT_ROOT'], 'public_html')) {
            $this->app->usePublicPath($_SERVER['DOCUMENT_ROOT']);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 本番環境ではHTTPSを強制
        if ($this->app->environment('production')) {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }
    }
}
