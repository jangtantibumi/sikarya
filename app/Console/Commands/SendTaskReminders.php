<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Task;
use App\Services\WorkflowNotificationService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;

class SendTaskReminders extends Command
{
    protected $signature = 'app:send-task-reminders';

    protected $description = 'Kirim pengingat H-7, harian, dan keterlambatan task ke seluruh rantai pelaporan.';

    public function handle(WorkflowNotificationService $notifications): int
    {
        $today = CarbonImmutable::now(config('app.timezone', 'Asia/Jakarta'))->startOfDay();
        $processed = 0;

        Task::query()
            ->with('user')
            ->whereNotIn('status', ['verified', 'rejected', 'cancelled'])
            ->whereNotNull('deadline')
            ->orderBy('id')
            ->each(function (Task $task) use ($notifications, $today, &$processed): void {
                if (! $task->user) {
                    return;
                }

                $deadline = CarbonImmutable::parse($task->deadline, config('app.timezone'))->startOfDay();
                $daysRemaining = (int) $today->diffInDays($deadline, false);
                $recipients = collect([$task->user])
                    ->merge($task->user->managementChain())
                    ->unique('id');

                if ($daysRemaining === 7) {
                    $notifications->send(
                        $recipients,
                        'Task memasuki H-7 deadline',
                        "Task \"{$task->title}\" akan jatuh tempo pada {$deadline->format('d M Y')}.",
                        "task:{$task->id}:deadline:{$deadline->format('Y-m-d')}:h7",
                        'task_reminder',
                        '/#kpi-tasks',
                        ['task_id' => $task->id, 'days_remaining' => 7],
                    );
                }

                if ($daysRemaining < 0) {
                    $notifications->send(
                        $recipients,
                        'Task melewati deadline',
                        "Task \"{$task->title}\" terlambat ".abs($daysRemaining).' hari dan belum diverifikasi selesai.',
                        "task:{$task->id}:overdue:{$today->format('Y-m-d')}",
                        'task_reminder',
                        '/#kpi-tasks',
                        ['task_id' => $task->id, 'days_overdue' => abs($daysRemaining)],
                    );
                } else {
                    $notifications->send(
                        $recipients,
                        'Pengingat task belum selesai',
                        "Task \"{$task->title}\" belum selesai. Sisa waktu {$daysRemaining} hari.",
                        "task:{$task->id}:unfinished:{$today->format('Y-m-d')}",
                        'task_reminder',
                        '/#kpi-tasks',
                        ['task_id' => $task->id, 'days_remaining' => $daysRemaining],
                    );
                }

                $processed++;
            });

        $this->info("Pengingat diproses untuk {$processed} task.");

        return self::SUCCESS;
    }
}
