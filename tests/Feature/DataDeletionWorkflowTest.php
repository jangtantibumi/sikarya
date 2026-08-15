<?php

namespace Tests\Feature;

use App\Models\ApprovalRequest;
use App\Models\Attendance;
use App\Models\ChatMessage;
use App\Models\DataDeletionRequest;
use App\Models\ErpDocument;
use App\Models\JournalEntry;
use App\Models\Task;
use App\Models\User;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DataDeletionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'DataMigrationSeeder']);
    }

    public function test_staff_task_is_soft_deleted_only_after_manager_approval(): void
    {
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $task = Task::query()->create([
            'user_id' => $staff->id,
            'created_by_id' => $manager->id,
            'title' => 'Publikasi kampanye Agustus',
            'status' => 'in_progress',
            'deadline' => '2026-08-31',
        ]);

        $created = $this->actingAs($staff)->postJson('/api/data-deletions', [
            'resource_type' => 'task',
            'resource_id' => $task->id,
            'reason' => 'Task tercatat dua kali setelah sinkronisasi.',
        ]);

        $created
            ->assertStatus(202)
            ->assertJsonPath('deletion.status', 'pending_manager')
            ->assertJsonPath('approval.status', 'pending_manager');

        $this->assertFalse($task->fresh()->trashed());

        $approvalId = $created->json('approval.id');
        $this->actingAs($manager)
            ->postJson("/api/approvals/{$approvalId}/approve", [
                'note' => 'Duplikasi telah diverifikasi.',
            ])
            ->assertOk()
            ->assertJsonPath('approval.status', 'approved');

        $this->assertTrue(Task::withTrashed()->findOrFail($task->id)->trashed());
        $this->assertDatabaseHas('data_deletion_requests', [
            'resource_type' => 'task',
            'target_id' => $task->id,
            'status' => 'executed',
            'executed_by_id' => $manager->id,
        ]);
        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'data.deletion_executed',
            'actor_id' => $manager->id,
        ]);
    }

    public function test_high_risk_attendance_requires_manager_then_ceo(): void
    {
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $attendance = Attendance::query()->create([
            'user_id' => $staff->id,
            'clock_in' => '2026-07-27 08:00:00',
            'clock_out' => '2026-07-27 17:00:00',
            'status' => 'Present',
            'location_coordinates' => '-6.20,106.84',
            'work_type' => 'WFO',
        ]);

        $created = $this->actingAs($staff)->postJson('/api/data-deletions', [
            'resource_type' => 'attendance',
            'resource_id' => $attendance->id,
            'reason' => 'Presensi salah tanggal akibat koreksi perangkat.',
        ])->assertStatus(202);

        $approvalId = $created->json('approval.id');
        $this->actingAs($manager)
            ->postJson("/api/approvals/{$approvalId}/approve", [
                'note' => 'Kesalahan perangkat telah dikonfirmasi.',
            ])
            ->assertOk()
            ->assertJsonPath('approval.status', 'pending_ceo');

        $this->assertFalse($attendance->fresh()->trashed());
        $this->assertDatabaseHas('data_deletion_requests', [
            'target_id' => $attendance->id,
            'status' => 'pending_ceo',
        ]);

        $this->actingAs($ceo)
            ->postJson("/api/approvals/{$approvalId}/approve", [
                'note' => 'Koreksi presensi disahkan.',
            ])
            ->assertOk()
            ->assertJsonPath('approval.status', 'approved');

        $this->assertTrue(Attendance::withTrashed()->findOrFail($attendance->id)->trashed());
    }

    public function test_superadmin_can_directly_remove_attendance_with_audited_soft_delete(): void
    {
        $superadmin = User::query()->where('username', 'ceo')->firstOrFail();
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $attendance = Attendance::query()->create([
            'user_id' => $staff->id,
            'clock_in' => '2026-07-26 08:05:00',
            'clock_out' => '2026-07-26 17:03:00',
            'status' => 'Present',
            'location_coordinates' => '-6.20,106.84',
            'work_type' => 'WFO',
        ]);

        $this->actingAs($superadmin)->postJson('/api/data-deletions', [
            'resource_type' => 'attendance',
            'resource_id' => $attendance->id,
            'reason' => 'Riwayat presensi duplikat telah diverifikasi oleh superadmin.',
        ])
            ->assertOk()
            ->assertJsonPath('approval', null)
            ->assertJsonPath('deletion.status', 'executed');

        $this->assertTrue(
            Attendance::withTrashed()->findOrFail($attendance->id)->trashed(),
        );
        $this->assertDatabaseHas('audit_events', [
            'event_type' => 'data.deletion_executed',
            'actor_id' => $superadmin->id,
        ]);
    }

    public function test_staff_cannot_request_deletion_of_another_staff_task(): void
    {
        $owner = User::query()->where('username', 'maulana')->firstOrFail();
        $otherStaff = User::query()->where('username', 'dbest')->firstOrFail();
        $task = Task::query()->create([
            'user_id' => $owner->id,
            'created_by_id' => $owner->id,
            'title' => 'Task privat pemilik lain',
            'status' => 'in_progress',
        ]);

        $this->actingAs($otherStaff)->postJson('/api/data-deletions', [
            'resource_type' => 'task',
            'resource_id' => $task->id,
            'reason' => 'Mencoba menghapus data milik rekan kerja.',
        ])->assertForbidden();

        $this->assertDatabaseMissing('data_deletion_requests', [
            'target_id' => $task->id,
        ]);
        $this->assertFalse($task->fresh()->trashed());
    }

    public function test_posted_journal_is_reversed_and_profit_loss_nets_to_zero(): void
    {
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $accounting = app(AccountingService::class);
        $entry = $accounting->recordQuickTransaction($ceo, [
            'date' => '2026-07-27',
            'kind' => 'revenue',
            'category' => 'design_revenue',
            'amount' => 25000000,
            'description' => 'Pendapatan desain yang salah input',
        ]);

        $this->assertSame(25000000.0, $accounting->profitAndLoss(2026, 7)['revenue']);

        $this->actingAs($ceo)->postJson('/api/data-deletions', [
            'resource_type' => 'journal_entry',
            'resource_id' => $entry->id,
            'reason' => 'Jurnal salah input dan harus dibalik penuh.',
        ])
            ->assertOk()
            ->assertJsonPath('approval', null)
            ->assertJsonPath('deletion.status', 'executed');

        $this->assertDatabaseHas('journal_entries', [
            'id' => $entry->id,
            'status' => 'reversed',
        ]);
        $this->assertDatabaseHas('journal_entries', [
            'source_type' => 'journal_reversal',
            'source_id' => $entry->id,
            'status' => 'posted',
        ]);
        $this->assertSame(0.0, $accounting->profitAndLoss(2026, 7)['revenue']);
        $this->assertSame(2, JournalEntry::query()->count());
    }

    public function test_chat_is_redacted_and_signed_document_is_revoked_not_destroyed(): void
    {
        Storage::fake('local');
        $staff = User::query()->where('username', 'maulana')->firstOrFail();
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        Storage::disk('local')->put('chat-attachments/evidence.pdf', 'test');

        $message = ChatMessage::query()->create([
            'sender_id' => $staff->id,
            'channel' => 'marketing-team',
            'type' => 'message',
            'message' => 'Pesan salah kirim.',
            'attachment_name' => 'evidence.pdf',
            'attachment_path' => 'chat-attachments/evidence.pdf',
            'attachment_mime' => 'application/pdf',
            'attachment_size' => 4,
        ]);

        $messageRequest = $this->actingAs($staff)->postJson('/api/data-deletions', [
            'resource_type' => 'chat_message',
            'resource_id' => $message->id,
            'reason' => 'Pesan terkirim ke kanal yang tidak tepat.',
        ])->assertStatus(202);

        $this->actingAs($manager)
            ->postJson("/api/approvals/{$messageRequest->json('approval.id')}/approve", [
                'note' => 'Redaksi pesan disetujui.',
            ])
            ->assertOk();

        $this->assertSame(
            'Pesan telah dihapus melalui proses persetujuan.',
            $message->fresh()->message,
        );
        Storage::disk('local')->assertMissing('chat-attachments/evidence.pdf');

        $document = ErpDocument::query()->create([
            'document_type' => 'internship_certificate',
            'document_number' => 'CERT-DELETE-001',
            'title' => 'Sertifikat Magang',
            'owner_user_id' => $staff->id,
            'created_by_id' => $ceo->id,
            'status' => 'signed',
            'issued_at' => '2026-07-01',
            'content' => ['program' => 'Magang'],
            'verification_token' => 'delete-workflow-token',
            'document_hash' => hash('sha256', 'certificate'),
            'signed_at' => now(),
        ]);

        $this->actingAs($ceo)->postJson('/api/data-deletions', [
            'resource_type' => 'erp_document',
            'resource_id' => $document->id,
            'reason' => 'Dokumen diganti setelah koreksi identitas peserta.',
        ])
            ->assertOk()
            ->assertJsonPath('deletion.deletion_mode', 'revoke');

        $document->refresh();
        $this->assertSame('revoked', $document->status);
        $this->assertNotNull($document->revoked_at);
        $this->assertFalse($document->trashed());
        $this->assertSame(1, DataDeletionRequest::query()
            ->where('resource_type', 'erp_document')
            ->where('status', 'executed')
            ->count());
    }
}
