<?php

declare(strict_types=1);

namespace Modules\Automation\Tests\Feature;

use Tests\TestCase;

class AutomationCommandTest extends TestCase
{
    /**
     * Test the backflush command executes successfully.
     */
    public function test_calculate_backflush_command_can_be_executed(): void
    {
        $this->artisan('automation:calculate-backflush')
            ->expectsOutputToContain('Kalkulasi backflush selesai dieksekusi')
            ->assertExitCode(0);
    }

    /**
     * Test the send reminders command executes successfully.
     */
    public function test_send_reminders_command_can_be_executed(): void
    {
        $this->artisan('automation:send-reminders')
            ->expectsOutputToContain('Reminder berhasil dikirim')
            ->assertExitCode(0);
    }
}
