<?php

namespace App\Console\Commands;

use App\Services\AutomationService;
use Illuminate\Console\Command;

class ProcessAutomationTasks extends Command
{
    protected $signature = 'automation:process';

    protected $description = '自動タスクを実行する';

    public function handle(AutomationService $automationService): int
    {
        $count = $automationService->processAllTasks();

        $this->info("Processed {$count} automation actions.");

        return self::SUCCESS;
    }
}
