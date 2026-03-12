<?php

namespace App\Services;

use App\Jobs\SendAutomationEmail;
use App\Models\AutomationLog;
use App\Models\AutomationTask;
use App\Models\Member;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class AutomationService
{
    /**
     * 全アクティブタスクを処理
     */
    public function processAllTasks(): int
    {
        $tasks = AutomationTask::active()->get();
        $processedCount = 0;

        foreach ($tasks as $task) {
            try {
                $count = $this->processTask($task);
                $processedCount += $count;

                $task->update(['last_executed_at' => now()]);
            } catch (\Exception $e) {
                Log::error('Automation task failed', [
                    'task_id' => $task->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $processedCount;
    }

    /**
     * 単一タスクを処理
     */
    public function processTask(AutomationTask $task): int
    {
        $members = $this->resolveTriggerMembers($task);
        $count = 0;

        foreach ($members as $member) {
            if ($this->hasAlreadyExecutedToday($task, $member)) {
                continue;
            }

            try {
                $this->executeAction($task, $member);
                $count++;
            } catch (\Exception $e) {
                AutomationLog::create([
                    'automation_task_id' => $task->id,
                    'member_id' => $member->id,
                    'action_type' => $task->action_type,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'executed_at' => now(),
                ]);

                Log::error('Automation action failed', [
                    'task_id' => $task->id,
                    'member_id' => $member->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    /**
     * トリガー条件に合致する会員を解決
     *
     * trigger_type:
     *   - member_registered_days_ago: 登録からN日経過した会員
     *   - plan_expires_in_days: プラン期限がN日以内の会員
     *   - plan_expired: プランが期限切れの会員
     *   - member_inactive_days: N日間ログインしていない会員
     */
    public function resolveTriggerMembers(AutomationTask $task): \Illuminate\Database\Eloquent\Collection
    {
        $triggerValue = $task->trigger_value ?? [];

        return match ($task->trigger_type) {
            'member_registered_days_ago' => $this->getMembersRegisteredDaysAgo($triggerValue),
            'plan_expires_in_days' => $this->getMembersPlanExpiresInDays($triggerValue),
            'plan_expired' => $this->getMembersPlanExpired($triggerValue),
            'member_inactive_days' => $this->getMembersInactiveDays($triggerValue),
            default => Member::query()->where('id', 0)->get(), // 空のコレクション
        };
    }

    /**
     * アクション実行
     *
     * action_type:
     *   - send_email: メール送信
     *   - change_plan: プラン変更
     *   - change_status: ステータス変更
     */
    public function executeAction(AutomationTask $task, Member $member): void
    {
        $actionValue = $task->action_value ?? [];

        match ($task->action_type) {
            'send_email' => $this->executeSendEmail($task, $member, $actionValue),
            'change_plan' => $this->executeChangePlan($task, $member, $actionValue),
            'change_status' => $this->executeChangeStatus($task, $member, $actionValue),
            default => throw new \RuntimeException("未対応のアクションタイプ: {$task->action_type}"),
        };
    }

    /**
     * 同日の重複実行を防止
     */
    public function hasAlreadyExecutedToday(AutomationTask $task, Member $member): bool
    {
        return AutomationLog::where('automation_task_id', $task->id)
            ->where('member_id', $member->id)
            ->where('status', 'success')
            ->whereDate('executed_at', today())
            ->exists();
    }

    // --- Trigger Resolvers ---

    protected function getMembersRegisteredDaysAgo(array $value): \Illuminate\Database\Eloquent\Collection
    {
        $days = $value['days'] ?? 0;
        if ($days <= 0) {
            return Member::query()->where('id', 0)->get();
        }

        $targetDate = Carbon::today()->subDays($days);

        return Member::where('status', 'active')
            ->whereDate('created_at', $targetDate)
            ->get();
    }

    protected function getMembersPlanExpiresInDays(array $value): \Illuminate\Database\Eloquent\Collection
    {
        $days = $value['days'] ?? 7;
        $planId = $value['plan_id'] ?? null;

        $query = Member::where('status', 'active')
            ->whereHas('plans', function (Builder $q) use ($days, $planId) {
                $q->wherePivot('status', 'active')
                    ->whereNotNull('member_plan.expires_at')
                    ->whereBetween('member_plan.expires_at', [now(), now()->addDays($days)]);

                if ($planId) {
                    $q->where('plans.id', $planId);
                }
            });

        return $query->get();
    }

    protected function getMembersPlanExpired(array $value): \Illuminate\Database\Eloquent\Collection
    {
        $planId = $value['plan_id'] ?? null;

        $query = Member::where('status', 'active')
            ->whereHas('plans', function (Builder $q) use ($planId) {
                $q->wherePivot('status', 'active')
                    ->whereNotNull('member_plan.expires_at')
                    ->where('member_plan.expires_at', '<', now());

                if ($planId) {
                    $q->where('plans.id', $planId);
                }
            });

        return $query->get();
    }

    protected function getMembersInactiveDays(array $value): \Illuminate\Database\Eloquent\Collection
    {
        $days = $value['days'] ?? 30;

        return Member::where('status', 'active')
            ->where(function ($q) use ($days) {
                $q->where('last_login_at', '<', now()->subDays($days))
                    ->orWhereNull('last_login_at');
            })
            ->get();
    }

    // --- Action Executors ---

    protected function executeSendEmail(AutomationTask $task, Member $member, array $value): void
    {
        $subject = $value['subject'] ?? $task->name;
        $body = $value['body_html'] ?? '';

        if (empty($body)) {
            AutomationLog::create([
                'automation_task_id' => $task->id,
                'member_id' => $member->id,
                'action_type' => 'send_email',
                'status' => 'skipped',
                'error_message' => 'メール本文が未設定',
                'executed_at' => now(),
            ]);

            return;
        }

        SendAutomationEmail::dispatch($member, $subject, $body, $task);

        AutomationLog::create([
            'automation_task_id' => $task->id,
            'member_id' => $member->id,
            'action_type' => 'send_email',
            'action_detail' => ['subject' => $subject],
            'status' => 'success',
            'executed_at' => now(),
        ]);
    }

    protected function executeChangePlan(AutomationTask $task, Member $member, array $value): void
    {
        $fromPlanId = $value['from_plan_id'] ?? null;
        $toPlanId = $value['to_plan_id'] ?? null;

        if (!$toPlanId) {
            return;
        }

        // 旧プランを期限切れに
        if ($fromPlanId) {
            $member->plans()->updateExistingPivot($fromPlanId, [
                'status' => 'expired',
                'note' => "自動タスク「{$task->name}」により変更",
            ]);
        }

        // 新プランを付与
        $member->plans()->syncWithoutDetaching([
            $toPlanId => [
                'status' => 'active',
                'started_at' => now(),
                'granted_by' => 'automation',
                'note' => "自動タスク「{$task->name}」により付与",
            ],
        ]);

        AutomationLog::create([
            'automation_task_id' => $task->id,
            'member_id' => $member->id,
            'action_type' => 'change_plan',
            'action_detail' => ['from' => $fromPlanId, 'to' => $toPlanId],
            'status' => 'success',
            'executed_at' => now(),
        ]);
    }

    protected function executeChangeStatus(AutomationTask $task, Member $member, array $value): void
    {
        $newStatus = $value['status'] ?? null;
        if (!$newStatus) {
            return;
        }

        $oldStatus = $member->status->value;
        $member->update(['status' => $newStatus]);

        AutomationLog::create([
            'automation_task_id' => $task->id,
            'member_id' => $member->id,
            'action_type' => 'change_status',
            'action_detail' => ['from' => $oldStatus, 'to' => $newStatus],
            'status' => 'success',
            'executed_at' => now(),
        ]);
    }
}
