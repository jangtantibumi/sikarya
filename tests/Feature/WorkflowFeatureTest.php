<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Goal;
use App\Models\Kpi;
use App\Models\KpiPlan;
use App\Models\Task;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class WorkflowFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'DataMigrationSeeder']);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        CarbonImmutable::setTestNow();
        parent::tearDown();
    }

    public function test_manager_leave_request_goes_directly_to_ceo(): void
    {
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();

        $response = $this->actingAs($manager)->postJson('/api/leave-requests', [
            'start_date' => '2026-08-01',
            'end_date' => '2026-08-03',
            'reason' => 'Keperluan keluarga',
            'type' => 'Cuti Tahunan',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('status', 'pending_ceo');

        $this->assertDatabaseHas('approval_requests', [
            'request_type' => 'leave',
            'requester_id' => $manager->id,
            'status' => 'pending_ceo',
        ]);
    }

    public function test_staff_leave_flows_manager_to_ceo_and_notifies_hrd(): void
    {
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $hrd = User::query()->where('username', 'sonia')->firstOrFail();

        $created = $this->actingAs($staff)->postJson('/api/leave-requests', [
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-02',
            'reason' => 'Acara keluarga',
            'type' => 'Cuti Tahunan',
        ]);

        $created
            ->assertCreated()
            ->assertJsonPath('status', 'pending_manager');

        $approvalId = $created->json('approval_id');

        $this->actingAs($manager)
            ->getJson('/api/approvals')
            ->assertOk()
            ->assertJsonFragment(['id' => $approvalId, 'status' => 'pending_manager']);

        $this->actingAs($ceo)
            ->getJson('/api/approvals')
            ->assertOk()
            ->assertJsonMissing(['id' => $approvalId]);

        $this->actingAs($manager)
            ->postJson("/api/approvals/{$approvalId}/approve", ['note' => 'Jadwal tim aman'])
            ->assertOk()
            ->assertJsonPath('approval.status', 'pending_ceo');

        $this->actingAs($ceo)
            ->getJson('/api/approvals?division=marketing')
            ->assertOk()
            ->assertJsonFragment(['id' => $approvalId, 'status' => 'pending_ceo']);

        $this->actingAs($ceo)
            ->postJson("/api/approvals/{$approvalId}/approve", ['note' => 'Disetujui'])
            ->assertOk()
            ->assertJsonPath('approval.status', 'approved');

        $this->assertDatabaseHas('leave_requests', [
            'user_id' => $staff->id,
            'status' => 'approved',
        ]);
        $this->assertGreaterThanOrEqual(3, $hrd->fresh()->notifications()->count());
    }

    public function test_manager_team_deletion_is_visible_in_ceo_division_tab_and_applied_only_after_approval(): void
    {
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();

        $created = $this->actingAs($manager)->postJson('/api/team-requests', [
            'action' => 'delete',
            'target_username' => 'dbest',
            'completion_status' => 'completed',
            'separation_reason' => 'resigned',
            'separation_notes' => 'Serah terima aset dan pekerjaan telah selesai.',
            'effective_date' => '2026-07-28',
        ]);

        $created
            ->assertCreated()
            ->assertJsonPath('request.status', 'pending_ceo');

        $approvalId = $created->json('request.approval_id');
        $this->assertDatabaseHas('users', ['username' => 'dbest', 'is_active' => true]);

        Attendance::query()->create([
            'user_id' => User::query()->where('username', 'dbest')->value('id'),
            'clock_in' => now(),
            'status' => 'Present',
            'location_coordinates' => '-6.20,106.84',
            'work_type' => 'WFO',
        ]);

        $this->actingAs($ceo)
            ->getJson('/api/approvals?division=marketing')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $approvalId,
                'request_type' => 'team_request',
                'division' => 'marketing',
            ]);

        $this->actingAs($ceo)
            ->getJson('/api/approvals?division=finance')
            ->assertOk()
            ->assertExactJson([]);

        $this->actingAs($ceo)
            ->postJson("/api/approvals/{$approvalId}/approve", ['note' => 'Disetujui'])
            ->assertOk();

        $this->assertDatabaseHas('users', ['username' => 'dbest', 'is_active' => false]);
        $this->assertDatabaseHas('team_requests', [
            'target_username' => 'dbest',
            'status' => 'approved',
        ]);
        $this->assertDatabaseHas('employee_separations', [
            'user_id' => User::query()->where('username', 'dbest')->value('id'),
            'completion_status' => 'completed',
            'separation_reason' => 'resigned',
        ]);
        $this->assertSame(
            '2026-07-28',
            \App\Models\EmployeeSeparation::query()->firstOrFail()->effective_date->format('Y-m-d'),
        );

        $this->actingAs($ceo)
            ->getJson('/api/users')
            ->assertOk()
            ->assertJsonMissing(['username' => 'dbest']);

        $this->actingAs($ceo)
            ->getJson('/api/attendance')
            ->assertOk()
            ->assertJsonMissing(['username' => 'dbest']);
    }

    public function test_goal_kpi_task_and_metric_flow_is_enforced_end_to_end(): void
    {
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $staff = User::query()->where('username', 'maulana')->firstOrFail();

        $goalResponse = $this->actingAs($ceo)->postJson('/api/goals', [
            'title' => 'Pertumbuhan Marketing 2026',
            'description' => 'Meningkatkan akuisisi prospek berkualitas.',
            'division' => 'marketing',
            'year' => 2026,
        ]);
        $goalResponse->assertCreated();
        $goalId = $goalResponse->json('goal.id');

        $planResponse = $this->actingAs($manager)->postJson('/api/kpis/plan', [
            'goal_id' => $goalId,
            'kpis' => [
                [
                    'title' => 'Task kampanye terverifikasi',
                    'target_value' => 1,
                    'unit' => 'task',
                    'weight' => 60,
                    'direction' => 'higher_is_better',
                    'aggregation_type' => 'count',
                    'data_source' => 'tasks',
                ],
                [
                    'title' => 'Task konten terverifikasi',
                    'target_value' => 1,
                    'unit' => 'task',
                    'weight' => 40,
                    'direction' => 'higher_is_better',
                    'aggregation_type' => 'count',
                    'data_source' => 'tasks',
                ],
            ],
        ]);
        $planResponse
            ->assertCreated()
            ->assertJsonPath('plan.status', 'pending_ceo');

        $planId = $planResponse->json('plan.id');
        $planApprovalId = $planResponse->json('approval_id');

        $this->actingAs($ceo)
            ->postJson("/api/approvals/{$planApprovalId}/approve", ['note' => 'Bobot dan target sesuai'])
            ->assertOk();

        $this->assertDatabaseHas('kpi_plans', [
            'id' => $planId,
            'status' => 'approved',
        ]);

        $kpiId = Kpi::query()->where('kpi_plan_id', $planId)->orderBy('id')->value('id');
        $taskResponse = $this->actingAs($staff)->postJson('/api/tasks', [
            'username' => $staff->username,
            'title' => 'Eksekusi kampanye Juli',
            'deadline' => '2026-10-01',
            'kpi_id' => $kpiId,
        ]);
        $taskResponse
            ->assertCreated()
            ->assertJsonPath('task.status', 'pending_manager');

        $taskId = $taskResponse->json('task.id');
        $taskApprovalId = $taskResponse->json('task.approval_id');

        $this->actingAs($manager)
            ->postJson("/api/approvals/{$taskApprovalId}/approve", ['note' => 'Task relevan'])
            ->assertOk();

        $this->assertDatabaseHas('tasks', ['id' => $taskId, 'status' => 'in_progress']);

        $this->actingAs($staff)
            ->putJson("/api/tasks/{$taskId}/status", [
                'status' => 'submitted_for_review',
                'evidence' => 'https://example.test/evidence',
                'metric_value' => 1,
            ])
            ->assertOk();

        $this->actingAs($ceo)
            ->putJson("/api/tasks/{$taskId}/status", ['status' => 'verified'])
            ->assertUnprocessable();

        $this->actingAs($manager)
            ->putJson("/api/tasks/{$taskId}/status", [
                'status' => 'verified',
                'feedback' => 'Hasil sesuai target',
            ])
            ->assertOk()
            ->assertJsonPath('task.status', 'verified');

        $this->assertEquals(1.0, (float) Kpi::query()->findOrFail($kpiId)->current_value);
        $this->assertEquals(60.0, (float) KpiPlan::query()->findOrFail($planId)->score);
        $this->assertEquals(60.0, (float) Goal::query()->findOrFail($goalId)->progress);

        $revision = $this->actingAs($manager)->postJson('/api/kpis/plan', [
            'goal_id' => $goalId,
            'kpis' => [[
                'title' => 'KPI revisi kampanye',
                'target_value' => 10,
                'unit' => 'task',
                'weight' => 100,
                'direction' => 'higher_is_better',
                'aggregation_type' => 'count',
                'data_source' => 'tasks',
            ]],
        ]);
        $revision->assertCreated();

        $this->actingAs($ceo)
            ->postJson("/api/approvals/{$revision->json('approval_id')}/approve", [
                'note' => 'Revisi bobot disetujui',
            ])
            ->assertOk();

        $this->assertDatabaseHas('kpi_plans', ['id' => $planId, 'status' => 'superseded']);
        $this->assertDatabaseHas('kpi_plans', [
            'id' => $revision->json('plan.id'),
            'status' => 'approved',
        ]);
    }

    public function test_manager_can_submit_kpi_plan_without_ceo_goal_and_ceo_still_approves_it(): void
    {
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $financeManager = User::query()->where('username', 'mgr_finance')->firstOrFail();
        $financeStaff = User::query()->where('username', 'staff_finance')->firstOrFail();

        $created = $this->actingAs($manager)->postJson('/api/kpis/plan', [
            'title' => 'Inisiatif Akuisisi Klien Marketing',
            'kpis' => [[
                'title' => 'Prospek berkualitas',
                'target_value' => 20,
                'unit' => 'item',
                'weight' => 100,
                'direction' => 'higher_is_better',
                'aggregation_type' => 'count',
                'data_source' => 'leads',
            ]],
        ]);

        $created
            ->assertCreated()
            ->assertJsonPath('plan.goal_id', null)
            ->assertJsonPath('plan.title', 'Inisiatif Akuisisi Klien Marketing')
            ->assertJsonPath('plan.division', 'marketing')
            ->assertJsonPath('plan.status', 'pending_ceo');

        $planId = $created->json('plan.id');
        $approvalId = $created->json('approval_id');

        $managerPlanIds = collect($this->actingAs($manager)->getJson('/api/kpis')->assertOk()->json())
            ->pluck('id');
        $this->assertTrue($managerPlanIds->contains($planId));

        $staffPlanIdsBeforeApproval = collect($this->actingAs($staff)->getJson('/api/kpis')->assertOk()->json())
            ->pluck('id');
        $this->assertFalse($staffPlanIdsBeforeApproval->contains($planId));

        $financePlanIds = collect($this->actingAs($financeManager)->getJson('/api/kpis')->assertOk()->json())
            ->pluck('id');
        $this->assertFalse($financePlanIds->contains($planId));

        $this->actingAs($ceo)
            ->getJson('/api/approvals?division=marketing')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $approvalId,
                'request_type' => 'kpi_plan',
                'status' => 'pending_ceo',
            ])
            ->assertJsonFragment([
                'goal' => 'Inisiatif Akuisisi Klien Marketing',
                'proposal_source' => 'manager_initiative',
            ]);

        $this->actingAs($ceo)
            ->postJson("/api/approvals/{$approvalId}/approve", ['note' => 'Inisiatif relevan'])
            ->assertOk()
            ->assertJsonPath('approval.status', 'approved');

        $this->assertDatabaseHas('kpi_plans', [
            'id' => $planId,
            'goal_id' => null,
            'division' => 'marketing',
            'status' => 'approved',
            'score' => 0,
        ]);

        $staffPlanIdsAfterApproval = collect($this->actingAs($staff)->getJson('/api/kpis')->assertOk()->json())
            ->pluck('id');
        $this->assertTrue($staffPlanIdsAfterApproval->contains($planId));

        $financeStaffPlanIds = collect($this->actingAs($financeStaff)->getJson('/api/kpis')->assertOk()->json())
            ->pluck('id');
        $this->assertFalse($financeStaffPlanIds->contains($planId));
    }

    public function test_staff_can_submit_task_without_kpi_but_manager_approval_is_still_required(): void
    {
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $otherManager = User::query()->where('username', 'mgr_finance')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();

        $created = $this->actingAs($staff)->postJson('/api/tasks', [
            'title' => 'Riset mandiri tren arsitektur',
            'deadline' => '2026-10-01',
            'relation' => 'Tugas Mandiri',
        ]);

        $created
            ->assertCreated()
            ->assertJsonPath('task.kpi_id', null)
            ->assertJsonPath('task.status', 'pending_manager')
            ->assertJsonPath('task.relation', 'Tugas Mandiri');

        $taskId = $created->json('task.id');
        $approvalId = $created->json('task.approval_id');

        $this->assertDatabaseHas('tasks', [
            'id' => $taskId,
            'user_id' => $staff->id,
            'kpi_id' => null,
            'status' => 'pending_manager',
        ]);

        $this->actingAs($ceo)
            ->postJson("/api/approvals/{$approvalId}/approve", ['note' => 'Melewati manager'])
            ->assertForbidden();

        $this->actingAs($otherManager)
            ->postJson("/api/approvals/{$approvalId}/approve", ['note' => 'Bukan tim saya'])
            ->assertForbidden();

        $this->actingAs($manager)
            ->postJson("/api/approvals/{$approvalId}/approve", ['note' => 'Tugas relevan'])
            ->assertOk()
            ->assertJsonPath('approval.status', 'approved');

        $this->assertDatabaseHas('tasks', [
            'id' => $taskId,
            'status' => 'in_progress',
        ]);
    }

    public function test_attendance_uses_authoritative_jakarta_server_time_for_clock_in_and_out(): void
    {
        $staff = User::query()->where('username', 'maulana')->firstOrFail();

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-03 08:15:00', 'Asia/Jakarta'));
        $this->actingAs($staff)
            ->postJson('/api/attendance/clock-in', [
                'username' => $staff->username,
                'lat' => -6.2088,
                'lng' => 106.8456,
                'type' => 'WFO',
            ])
            ->assertOk()
            ->assertJsonPath('attendance_date', '2026-08-03')
            ->assertJsonPath('display_time', '08:15 WIB')
            ->assertJsonPath('attendance.status', 'Present');

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-08-03 17:02:00', 'Asia/Jakarta'));
        $this->actingAs($staff)
            ->postJson('/api/attendance/clock-out', ['username' => $staff->username])
            ->assertOk()
            ->assertJsonPath('attendance_date', '2026-08-03')
            ->assertJsonPath('display_time', '17:02 WIB');

        $this->assertDatabaseHas('attendances', [
            'user_id' => $staff->id,
            'status' => 'Present',
        ]);
    }

    public function test_task_reminders_reach_owner_manager_and_ceo_and_are_idempotent(): void
    {
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $now = CarbonImmutable::parse('2026-08-03 08:00:00', 'Asia/Jakarta');
        CarbonImmutable::setTestNow($now);
        Carbon::setTestNow(Carbon::parse($now->toDateTimeString(), 'Asia/Jakarta'));

        Task::query()->create([
            'user_id' => $staff->id,
            'created_by_id' => $manager->id,
            'title' => 'Siapkan laporan kampanye',
            'status' => 'in_progress',
            'deadline' => $now->addDays(7),
            'approved_at' => $now,
        ]);

        $this->artisan('app:send-task-reminders')->assertSuccessful();

        foreach ([$staff, $manager, $ceo] as $recipient) {
            $titles = $recipient->fresh()->notifications->pluck('data.title');
            $this->assertTrue(
                $titles->contains('Task memasuki H-7 deadline'),
                "Notifikasi {$recipient->username}: {$recipient->fresh()->notifications->pluck('data')->toJson()}"
            );
            $this->assertTrue(
                $titles->contains('Pengingat task belum selesai'),
                "Notifikasi {$recipient->username}: {$titles->toJson()}"
            );
        }

        $notificationCount = $staff->fresh()->notifications()->count();
        $this->artisan('app:send-task-reminders')->assertSuccessful();
        $this->assertSame($notificationCount, $staff->fresh()->notifications()->count());
    }

    public function test_dashboard_ui_uses_yes_confirmation_and_clickable_salary_slip_handler(): void
    {
        $blade = file_get_contents(resource_path('views/dashboard.blade.php'));
        $javascript = file_get_contents(public_path('js/app.js'));

        $this->assertStringContainsString('id="btn-confirm-ok"', $blade);
        $this->assertMatchesRegularExpression('/id="btn-confirm-ok"[^>]*>Ya<\/button>/', $blade);
        $this->assertStringContainsString('openSalarySlipModal(u)', $javascript);
        $this->assertStringNotContainsString('showSalarySlip(', $javascript);
        $this->assertStringContainsString('id="dynamic-staff-member-navs"', $blade);
        $this->assertStringContainsString('id="rule-dialog-modal"', $blade);
        $this->assertStringContainsString('id="approval-mode-tabs"', $blade);
        $this->assertDoesNotMatchRegularExpression('/\b(?:alert|prompt|confirm)\s*\(/', $javascript);
    }

    public function test_custom_job_title_keeps_system_role_and_is_applied_after_ceo_approval(): void
    {
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();

        $created = $this->actingAs($manager)->postJson('/api/team-requests', [
            'action' => 'add',
            'details' => [
                'name' => 'Nadia Kreatif',
                'username' => 'nadia_kreatif',
                'email' => 'nadia@example.test',
                'role' => 'staff_marketing',
                'job_title' => 'Content Creator',
                'employment_type' => 'Full-Time',
            ],
        ]);

        $created
            ->assertCreated()
            ->assertJsonPath('request.status', 'pending_ceo');

        $this->actingAs($ceo)
            ->postJson("/api/approvals/{$created->json('request.approval_id')}/approve", [
                'note' => 'Struktur dan jabatan sesuai.',
            ])
            ->assertOk();

        $newUser = User::query()->where('email', 'nadia@example.test')->firstOrFail();
        $this->assertMatchesRegularExpression(
            '/^sa\.mkt\.stf\.nadia-kreatif\.\d{4}$/',
            $newUser->username,
        );
        $this->assertDatabaseHas('users', [
            'email' => 'nadia@example.test',
            'role' => 'staff_marketing',
            'job_title' => 'Content Creator',
            'parent' => 'mgr_marketing',
            'is_active' => true,
        ]);

        $visibleUsers = $this->actingAs($manager)
            ->getJson('/api/users')
            ->assertOk()
            ->json();
        $this->assertSame('Content Creator', $visibleUsers[$newUser->username]['title']);
    }

    public function test_kpi_rules_are_manageable_by_ceo_and_managers_and_scoped_by_division(): void
    {
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $financeManager = User::query()->where('username', 'mgr_finance')->firstOrFail();
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();

        $created = $this->actingAs($manager)->postJson('/api/rules', [
            'condition' => 'Score ≥ 90%',
            'reward' => 'Bonus 2%',
            'type' => 'success',
            'division' => 'finance',
        ]);

        $created
            ->assertCreated()
            ->assertJsonPath('rule.division', 'marketing')
            ->assertJsonPath('rule.can_delete', true);

        $this->actingAs($staff)
            ->postJson('/api/rules', [
                'condition' => 'Score ≥ 50%',
                'reward' => 'Bonus',
                'type' => 'success',
            ])
            ->assertForbidden();

        $this->actingAs($financeManager)
            ->getJson('/api/rules')
            ->assertOk()
            ->assertJsonMissing(['condition' => 'Score ≥ 90%']);

        $this->actingAs($ceo)
            ->getJson('/api/rules')
            ->assertOk()
            ->assertJsonFragment(['condition' => 'Score ≥ 90%', 'division' => 'marketing']);
    }

    public function test_approval_history_is_visible_only_to_authorized_company_scope(): void
    {
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $otherStaff = User::query()->where('username', 'dbest')->firstOrFail();
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $financeManager = User::query()->where('username', 'mgr_finance')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();

        $staffLeave = $this->actingAs($staff)->postJson('/api/leave-requests', [
            'start_date' => '2026-11-01',
            'end_date' => '2026-11-02',
            'reason' => 'Keperluan keluarga',
            'type' => 'Cuti Tahunan',
        ]);
        $staffApprovalId = $staffLeave->json('approval_id');

        $this->actingAs($manager)
            ->postJson("/api/approvals/{$staffApprovalId}/approve", ['note' => 'Tim sudah diatur'])
            ->assertOk();
        $this->actingAs($ceo)
            ->postJson("/api/approvals/{$staffApprovalId}/approve", ['note' => 'Disetujui CEO'])
            ->assertOk();

        $financeLeave = $this->actingAs($financeManager)->postJson('/api/leave-requests', [
            'start_date' => '2026-12-01',
            'end_date' => '2026-12-01',
            'reason' => 'Keperluan pribadi',
            'type' => 'Cuti Tahunan',
        ]);
        $financeApprovalId = $financeLeave->json('approval_id');
        $this->actingAs($ceo)
            ->postJson("/api/approvals/{$financeApprovalId}/reject", ['note' => 'Jadwal tutup buku'])
            ->assertOk();

        $this->actingAs($ceo)
            ->getJson('/api/approvals?history=1')
            ->assertOk()
            ->assertJsonFragment(['id' => $staffApprovalId, 'status' => 'approved'])
            ->assertJsonFragment(['id' => $financeApprovalId, 'status' => 'rejected']);

        $this->actingAs($manager)
            ->getJson('/api/approvals?history=1')
            ->assertOk()
            ->assertJsonFragment(['id' => $staffApprovalId])
            ->assertJsonMissing(['id' => $financeApprovalId]);

        $this->actingAs($staff)
            ->getJson('/api/approvals?history=1')
            ->assertOk()
            ->assertJsonFragment(['id' => $staffApprovalId, 'status' => 'approved'])
            ->assertJsonPath('0.decisions.1.note', 'Disetujui CEO');

        $this->actingAs($otherStaff)
            ->getJson('/api/approvals?history=1')
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_session_is_forced_to_expire_after_seven_days(): void
    {
        $user = User::query()->where('username', 'maulana')->firstOrFail();

        $this->actingAs($user)
            ->withSession(['absolute_login_at' => now()->subDays(8)->timestamp])
            ->getJson('/api/users')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'SESSION_EXPIRED');
    }

    public function test_chat_is_server_backed_realtime_scoped_and_attachments_are_authorized(): void
    {
        Storage::fake('local');
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $marketingStaff = User::query()->where('username', 'maulana')->firstOrFail();
        $operationsStaff = User::query()->where('username', 'staff_ops')->firstOrFail();

        $created = $this->actingAs($manager)->post('/api/chat-messages', [
            'channel' => 'marketing-team',
            'message' => 'Materi kampanye terbaru terlampir.',
            'attachment' => UploadedFile::fake()->create('kampanye.pdf', 250, 'application/pdf'),
        ], ['Accept' => 'application/json']);

        $created
            ->assertCreated()
            ->assertJsonPath('message.channel', 'marketing-team')
            ->assertJsonPath('message.attachment.name', 'kampanye.pdf');

        $messageId = $created->json('message.id');
        $this->assertDatabaseHas('chat_messages', [
            'id' => $messageId,
            'sender_id' => $manager->id,
            'channel' => 'marketing-team',
        ]);

        $this->actingAs($marketingStaff)
            ->getJson('/api/chat-messages')
            ->assertOk()
            ->assertJsonFragment(['id' => $messageId, 'text' => 'Materi kampanye terbaru terlampir.']);

        $this->actingAs($operationsStaff)
            ->getJson('/api/chat-messages')
            ->assertOk()
            ->assertJsonMissing(['id' => $messageId]);

        $this->actingAs($marketingStaff)
            ->get("/api/chat-messages/{$messageId}/attachment")
            ->assertOk();

        $this->actingAs($operationsStaff)
            ->get("/api/chat-messages/{$messageId}/attachment")
            ->assertForbidden();
    }

    public function test_hrd_holiday_announcement_reaches_all_employees_and_general_chat(): void
    {
        $hrd = User::query()->where('username', 'sonia')->firstOrFail();
        $staff = User::query()->where('username', 'staff_ops')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();

        $created = $this->actingAs($hrd)->postJson('/api/chat-messages', [
            'channel' => 'general',
            'type' => 'holiday_announcement',
            'holiday_title' => 'Libur Nasional',
            'holiday_start_date' => '2026-08-17',
            'holiday_end_date' => '2026-08-17',
            'message' => 'Kegiatan kantor kembali berjalan pada hari kerja berikutnya.',
        ]);

        $created
            ->assertCreated()
            ->assertJsonPath('message.type', 'holiday_announcement')
            ->assertJsonPath('message.metadata.title', 'Libur Nasional');

        $this->actingAs($staff)
            ->getJson('/api/chat-messages?channel=general')
            ->assertOk()
            ->assertJsonFragment(['type' => 'holiday_announcement']);

        $this->actingAs($ceo)
            ->getJson('/api/notifications')
            ->assertOk()
            ->assertJsonFragment(['title' => 'Pengumuman Hari Libur']);
    }

    public function test_resignation_flows_staff_to_manager_to_ceo_with_hrd_copy(): void
    {
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $hrd = User::query()->where('username', 'sonia')->firstOrFail();

        $created = $this->actingAs($staff)->postJson('/api/resignation-requests', [
            'last_working_date' => now()->addMonth()->format('Y-m-d'),
            'reason' => 'Melanjutkan pendidikan.',
            'handover_notes' => 'Dokumentasi proyek akan diserahkan kepada manager.',
        ]);

        $created->assertCreated()->assertJsonPath('status', 'pending_manager');
        $approvalId = $created->json('approval_id');

        $this->actingAs($manager)
            ->postJson("/api/approvals/{$approvalId}/approve", ['note' => 'Serah terima disiapkan'])
            ->assertOk()
            ->assertJsonPath('approval.status', 'pending_ceo');

        $this->actingAs($ceo)
            ->getJson('/api/approvals?division=marketing')
            ->assertOk()
            ->assertJsonFragment(['id' => $approvalId, 'request_type' => 'resignation']);

        $this->actingAs($ceo)
            ->postJson("/api/approvals/{$approvalId}/approve", ['note' => 'Disetujui'])
            ->assertOk()
            ->assertJsonPath('approval.status', 'approved');

        $this->assertDatabaseHas('resignation_requests', [
            'user_id' => $staff->id,
            'status' => 'approved',
        ]);
        $this->assertGreaterThanOrEqual(3, $hrd->fresh()->notifications()->count());
    }

    public function test_backup_contains_only_data_visible_to_each_role(): void
    {
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $operationsStaff = User::query()->where('username', 'staff_ops')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();

        $this->actingAs($staff)
            ->getJson('/api/backup')
            ->assertOk()
            ->assertJsonFragment(['username' => 'maulana'])
            ->assertJsonMissing(['username' => 'staff_ops']);

        $this->actingAs($manager)
            ->getJson('/api/backup')
            ->assertOk()
            ->assertJsonFragment(['username' => 'maulana'])
            ->assertJsonMissing(['username' => $operationsStaff->username]);

        $this->actingAs($ceo)
            ->getJson('/api/backup')
            ->assertOk()
            ->assertJsonFragment(['username' => 'staff_ops']);

        $this->actingAs($staff)
            ->get('/api/backup?download=1')
            ->assertOk()
            ->assertHeader('content-type', 'application/json; charset=UTF-8')
            ->assertDownload();
    }

    public function test_revised_ui_exposes_functional_chat_resignation_backup_and_division_tabs(): void
    {
        $blade = file_get_contents(resource_path('views/dashboard.blade.php'));
        $javascript = file_get_contents(public_path('js/app.js'));

        foreach ([
            'id="chat-attachment-btn"',
            'id="chat-holiday-announcement-btn"',
            'id="backup-data-btn"',
            'id="view-resignation"',
            'id="attendance-btn-finance"',
            'id="attendance-btn-hrd"',
        ] as $marker) {
            $this->assertStringContainsString($marker, $blade);
        }

        $this->assertStringContainsString('setInterval(() => syncChatMessages(true), 2000)', $javascript);
        $this->assertStringContainsString('attendanceActiveFilter = filter', $javascript);
        $this->assertStringContainsString('Belum terdapat aktivitas clock-in terbaru', $javascript);
        $this->assertStringContainsString('restoreWhenEnabled', $javascript);
    }

    public function test_gemini_status_and_chat_fail_safely_when_api_key_is_missing(): void
    {
        $staff = User::query()->where('username', 'maulana')->firstOrFail();

        $this->actingAs($staff)
            ->getJson('/api/ai/status')
            ->assertOk()
            ->assertJsonPath('configured', false)
            ->assertJsonPath('model', 'gemini-2.5-flash');

        $this->actingAs($staff)
            ->postJson('/api/ai/chat', ['question' => 'Bagaimana status task saya?'])
            ->assertStatus(503)
            ->assertJsonPath('code', 'GEMINI_NOT_CONFIGURED');
    }

    public function test_gemini_uses_server_side_key_role_scoped_context_and_can_reply_to_chat(): void
    {
        config([
            'services.gemini.model' => 'gemini-2.5-flash',
            'services.gemini.base_url' => 'https://generativelanguage.googleapis.com/v1beta',
        ]);
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [['text' => 'Anda memiliki satu task yang perlu ditindaklanjuti.']],
                    ],
                ]],
            ]),
        ]);

        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $staff->forceFill([
            'gemini_api_key' => 'test-server-only-key',
            'gemini_model' => 'gemini-2.5-flash',
            'gemini_configured_at' => now(),
        ])->save();
        $response = $this->actingAs($staff)->postJson('/api/ai/chat', [
            'question' => 'Ringkas kondisi pekerjaan saya.',
            'channel' => 'general',
            'persist_to_chat' => true,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('answer', 'Anda memiliki satu task yang perlu ditindaklanjuti.')
            ->assertJsonPath('model', 'gemini-2.5-flash')
            ->assertJsonPath('chat_message.sender', 'ai-copilot')
            ->assertJsonPath('chat_message.type', 'ai_response');

        $this->assertDatabaseHas('chat_messages', [
            'sender_id' => $staff->id,
            'channel' => 'general',
            'type' => 'ai_response',
        ]);

        Http::assertSentCount(1);
        $recordedRequest = Http::recorded()[0][0];
        $payload = json_encode($recordedRequest->data());

        $this->assertSame(['test-server-only-key'], $recordedRequest->header('x-goog-api-key'));
        $this->assertStringContainsString('gemini-2.5-flash:generateContent', $recordedRequest->url());
        $this->assertStringContainsString('\\"username\\":\\"maulana\\"', $payload);
        $this->assertStringNotContainsString('\\"username\\":\\"staff_ops\\"', $payload);
        $this->assertStringNotContainsString('test-server-only-key', $payload);
    }

    public function test_gemini_cannot_use_chat_channel_outside_the_users_role(): void
    {
        Http::fake();
        $staff = User::query()->where('username', 'staff_ops')->firstOrFail();
        $staff->forceFill([
            'gemini_api_key' => 'test-key-with-valid-minimum-length',
            'gemini_model' => 'gemini-2.5-flash',
            'gemini_configured_at' => now(),
        ])->save();

        $this->actingAs($staff)
            ->postJson('/api/ai/chat', [
                'question' => 'Ringkas diskusi management.',
                'channel' => 'management',
                'persist_to_chat' => true,
            ])
            ->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_ai_ui_is_connected_to_real_gemini_endpoint_instead_of_mock_reply(): void
    {
        $blade = file_get_contents(resource_path('views/dashboard.blade.php'));
        $javascript = file_get_contents(public_path('js/app.js'));

        foreach ([
            'id="ai-copilot-status"',
            'id="ai-copilot-body"',
            'id="ai-copilot-input"',
            'id="ai-copilot-send"',
        ] as $marker) {
            $this->assertStringContainsString($marker, $blade);
        }

        $this->assertStringContainsString("apiRequest('/api/ai/chat'", $javascript);
        $this->assertStringContainsString("apiRequest('/api/ai/status'", $javascript);
        $this->assertStringContainsString("apiRequest('/api/ai/settings'", $javascript);
        $this->assertStringContainsString('askGeminiInChannel', $javascript);
        $this->assertStringContainsString('Hubungkan Gemini Pribadi', $blade);
        $this->assertStringContainsString('Buka Halaman API Key Google AI Studio', $blade);
        $this->assertStringNotContainsString('AI Copilot Mock Reply engine', $javascript);
        $this->assertStringNotContainsString('integrasi API Gemini sedang offline di versi V1.0 ini', $javascript);
    }

    public function test_each_account_can_store_test_and_remove_its_own_encrypted_gemini_key(): void
    {
        Http::fake([
            'generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => ['parts' => [['text' => 'TERHUBUNG']]],
                ]],
            ]),
        ]);

        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $otherStaff = User::query()->where('username', 'staff_ops')->firstOrFail();
        $plainKey = 'AIzaSyPersonalKey123456789012345';

        $this->actingAs($staff)
            ->postJson('/api/ai/settings', [
                'api_key' => $plainKey,
                'model' => 'gemini-2.5-flash-lite',
            ])
            ->assertOk()
            ->assertJsonPath('configured', true)
            ->assertJsonPath('model', 'gemini-2.5-flash-lite')
            ->assertJsonMissing(['api_key' => $plainKey]);

        $rawEncryptedKey = DB::table('users')
            ->where('id', $staff->id)
            ->value('gemini_api_key');
        $this->assertNotSame($plainKey, $rawEncryptedKey);
        $this->assertStringNotContainsString($plainKey, (string) $rawEncryptedKey);
        $this->assertSame($plainKey, $staff->fresh()->gemini_api_key);
        $this->assertArrayNotHasKey('gemini_api_key', $staff->fresh()->toArray());

        $this->actingAs($staff)
            ->getJson('/api/ai/status')
            ->assertOk()
            ->assertJsonPath('configured', true)
            ->assertJsonPath('model', 'gemini-2.5-flash-lite')
            ->assertJsonMissing(['api_key' => $plainKey]);

        $this->actingAs($otherStaff)
            ->getJson('/api/ai/status')
            ->assertOk()
            ->assertJsonPath('configured', false);

        $this->actingAs($staff)
            ->getJson('/api/backup')
            ->assertOk()
            ->assertDontSee($plainKey);

        $this->actingAs($staff)
            ->deleteJson('/api/ai/settings')
            ->assertOk()
            ->assertJsonPath('configured', false);

        $this->assertDatabaseHas('users', [
            'id' => $staff->id,
            'gemini_api_key' => null,
            'gemini_model' => null,
        ]);
        $this->assertNull($otherStaff->fresh()->gemini_api_key);
        Http::assertSentCount(1);
    }
}
