<?php

namespace Tests\Feature;

use App\Mail\AlumniEventInvitationMail;
use App\Models\ApprovalRequest;
use App\Models\Kpi;
use App\Models\KpiPlan;
use App\Models\RecordAttachment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EditableWorkflowAndAlumniTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'DataMigrationSeeder']);
        \App\Models\FeatureFlag::query()->updateOrCreate(['key' => 'performance'], ['is_enabled' => true]);
        \Illuminate\Support\Facades\Cache::flush();
        $company = \App\Models\Company::firstOrCreate(['name' => 'Suba Arch'], ['slug' => 'suba-arch']);
        \App\Models\User::query()->update(['company_id' => $company->id, 'is_active' => true]);
    }

    public function test_completed_employee_can_become_otp_alumni_without_remaining_in_employee_modules(): void
    {
        Mail::fake();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $staff = User::query()->where('username', 'maulana')->firstOrFail();

        $this->actingAs($ceo)->deleteJson("/api/users/{$staff->username}", [
            'completion_status' => 'completed',
            'convert_to_alumni' => true,
            'separation_reason' => 'completed',
            'effective_date' => now()->toDateString(),
        ])->assertOk()->assertJsonPath('separation.converted_to_alumni', true);

        $staff->refresh();
        $this->assertTrue($staff->is_active);
        $this->assertTrue($staff->isAlumni());
        $this->assertSame('alumni', $staff->account_status);
        $this->assertNotNull($staff->alumniProfile);

        $this->actingAs($ceo)
            ->getJson('/api/organization-chart')
            ->assertOk()
            ->assertJsonMissing(['username' => $staff->username]);

        $this->actingAs($staff)
            ->postJson('/api/attendance/clock-in', [
                'lat' => -6.9,
                'lng' => 107.6,
                'type' => 'WFO',
            ])
            ->assertForbidden();

        auth()->logout();
        $this->postJson('/api/login/send-otp', ['username' => $staff->email])
            ->assertOk();
        Mail::assertSent(\App\Mail\LoginOtpMail::class, fn ($mail) => $mail->hasTo($staff->email));
    }

    public function test_alumni_can_update_portfolio_and_hrd_can_send_audited_mass_invitation(): void
    {
        Mail::fake();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $hrd = User::query()->where('username', 'sonia')->firstOrFail();
        $staff = User::query()->where('username', 'maulana')->firstOrFail();

        $this->actingAs($ceo)->deleteJson("/api/users/{$staff->username}", [
            'completion_status' => 'completed',
            'convert_to_alumni' => true,
            'separation_reason' => 'completed',
            'effective_date' => now()->toDateString(),
        ])->assertOk();
        $staff->refresh();

        $this->actingAs($staff)->putJson('/api/alumni/profile', [
            'current_employer' => 'Studio Nusantara',
            'current_position' => 'Junior Architect',
            'industry' => 'Architecture',
            'city' => 'Bandung',
            'linkedin_url' => 'https://linkedin.com/in/example',
            'portfolio_url' => 'https://behance.net/example',
            'bio' => 'Alumni magang Suba-Arch.',
            'skills' => ['AutoCAD', 'SketchUp'],
            'available_for_opportunities' => true,
            'receive_event_invitations' => true,
        ])->assertOk()->assertJsonPath('profile.current_employer', 'Studio Nusantara');

        $this->actingAs($hrd)->postJson('/api/alumni/invitations', [
            'title' => 'Alumni Design Gathering',
            'message' => 'Kami mengundang Anda untuk bertemu dan berbagi pengalaman.',
            'event_at' => now()->addWeek()->toIso8601String(),
            'location' => 'Suba-Arch Studio',
            'recipient_ids' => [$staff->id],
        ])->assertCreated()->assertJsonPath('invitation.sent_count', 1);

        Mail::assertSent(
            AlumniEventInvitationMail::class,
            fn (AlumniEventInvitationMail $mail) => $mail->hasTo($staff->email),
        );
        $this->assertDatabaseHas('alumni_invitation_recipients', [
            'user_id' => $staff->id,
            'status' => 'sent',
        ]);
    }

    public function test_task_creator_can_edit_pending_task_and_keep_private_attachments(): void
    {
        Storage::fake('local');
        $staff = User::query()->where('username', 'maulana')->firstOrFail();

        $created = $this->actingAs($staff)->post('/api/tasks', [
            'title' => 'Draft laporan desain',
            'deadline' => now()->addDays(3)->toDateString(),
            'attachment' => UploadedFile::fake()->create('brief-desain.pdf', 20, 'application/pdf'),
        ], ['Accept' => 'application/json'])->assertCreated();

        $taskId = $created->json('task.id');
        $this->actingAs($staff)->post("/api/tasks/{$taskId}", [
            '_method' => 'PUT',
            'title' => 'Laporan desain final',
            'deadline' => now()->addDays(5)->toDateString(),
            'attachment' => UploadedFile::fake()->create('revisi-desain.xlsx', 25, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'),
        ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('task.title', 'Laporan desain final')
            ->assertJsonPath('task.can_edit', true);

        $this->assertSame(2, RecordAttachment::query()->count());
        $attachment = RecordAttachment::query()->latest('id')->firstOrFail();
        $this->actingAs($staff)->get("/api/record-attachments/{$attachment->id}")->assertOk();
    }

    public function test_manager_can_upload_edit_and_realtime_score_manual_kpi_sheet(): void
    {
        Storage::fake('local');
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $rows = [[
            'title' => 'Proposal desain selesai',
            'target_value' => 10,
            'unit' => 'item',
            'weight' => 100,
            'direction' => 'higher_is_better',
            'aggregation_type' => 'manual',
            'data_source' => 'manual',
        ]];

        $created = $this->actingAs($manager)->post('/api/kpis/plan', [
            'title' => 'KPI Mandiri Desain',
            'kpis' => json_encode($rows),
            'supporting_file' => UploadedFile::fake()->create('proposal-kpi.xlsx', 25),
        ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('plan.can_edit', true);

        $plan = KpiPlan::query()->findOrFail($created->json('plan.id'));
        $approval = ApprovalRequest::query()->where('subject_id', $plan->id)->where('request_type', 'kpi_plan')->firstOrFail();
        $this->actingAs($ceo)
            ->postJson("/api/approvals/{$approval->id}/approve", ['note' => 'Disahkan'])
            ->assertOk();

        $kpi = Kpi::query()->where('kpi_plan_id', $plan->id)->firstOrFail();
        $this->actingAs($manager)
            ->patchJson("/api/kpis/{$kpi->id}/score", ['current_value' => 6])
            ->assertOk()
            ->assertJsonPath('plan.score', 60)
            ->assertJsonPath('plan.kpis.0.weighted_score', 60);

        $this->assertDatabaseHas('kpi_plans', ['id' => $plan->id, 'score' => 60]);
    }
}
