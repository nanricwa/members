<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// --------------------------------------------------------------------------
// タスクスケジュール
// --------------------------------------------------------------------------
// Xserver CRON設定:
// every 5 min: cd /home/xxxxxx/online-syukyaku.net && php artisan schedule:run >> /dev/null 2>&1

// キュー処理（5分おき）
Schedule::command('queue:process-once')->everyFiveMinutes()->withoutOverlapping();

// 自動タスク処理（5分おき）
Schedule::command('automation:process')->everyFiveMinutes()->withoutOverlapping();

// 予約メルマガ配信（5分おき）
Schedule::command('newsletter:send-scheduled')->everyFiveMinutes()->withoutOverlapping();

// 期限切れサブスクリプション処理（日次）
Schedule::command('subscriptions:expire')->daily()->at('02:00');
