<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ProcessQueueOnce extends Command
{
    protected $signature = 'queue:process-once {--timeout=55}';

    protected $description = 'CRON用: キューのジョブを一括処理する（Xserver共有サーバー向け）';

    public function handle(): int
    {
        $timeout = (int) $this->option('timeout');

        // queue:work --stop-when-empty でキューが空になるまで処理
        $this->call('queue:work', [
            '--stop-when-empty' => true,
            '--timeout' => $timeout,
            '--tries' => 3,
            '--memory' => 128,
        ]);

        return self::SUCCESS;
    }
}
