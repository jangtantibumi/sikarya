<?php

namespace Tests\Feature;

use App\Models\CertificateTemplate;
use App\Models\ErpDocument;
use App\Models\JournalEntry;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StrategicErpModulesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->artisan('db:seed', ['--class' => 'DataMigrationSeeder']);
    }

    public function test_new_modules_are_real_adjustable_features(): void
    {
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();

        foreach (['talent_management', 'advanced_analytics', 'document_management', 'accounting', 'project_costing'] as $feature) {
            $this->actingAs($ceo)
                ->putJson("/api/admin/features/{$feature}", ['enabled' => false])
                ->assertOk()
                ->assertJsonPath('feature.available', true)
                ->assertJsonPath('feature.enabled', false);

            $this->actingAs($ceo)
                ->putJson("/api/admin/features/{$feature}", ['enabled' => true])
                ->assertOk()
                ->assertJsonPath('feature.enabled', true);
        }
    }

    public function test_talent_reviews_are_scoped_and_staff_only_see_their_published_review(): void
    {
        $manager = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $employee = User::query()->where('username', 'maulana')->firstOrFail();
        $otherStaff = User::query()->where('username', 'dbest')->firstOrFail();

        $this->actingAs($manager)
            ->postJson('/api/talent/reviews', [
                'user_id' => $employee->id,
                'review_year' => 2026,
                'review_cycle' => 'annual',
                'performance_score' => 88,
                'potential_score' => 91,
                'competency_score' => 85,
                'readiness' => 'ready_1_year',
                'status' => 'published',
                'strengths' => 'Konsisten dan komunikatif.',
                'development_plan' => 'Memimpin satu proyek kampanye.',
                'next_role' => 'Content Lead',
                'training_plan' => ['Leadership', 'Campaign Analytics'],
            ])
            ->assertCreated()
            ->assertJsonPath('review.user_id', $employee->id);

        $this->assertDatabaseHas('talent_reviews', [
            'user_id' => $employee->id,
            'reviewer_id' => $manager->id,
            'status' => 'published',
        ]);

        $this->actingAs($employee)
            ->getJson('/api/talent/reviews?year=2026')
            ->assertOk()
            ->assertJsonCount(1, 'reviews')
            ->assertJsonPath('reviews.0.next_role', 'Content Lead');

        $this->actingAs($otherStaff)
            ->getJson('/api/talent/reviews?year=2026')
            ->assertOk()
            ->assertJsonCount(0, 'reviews');

        $this->actingAs($manager)
            ->postJson('/api/talent/reviews', [
                'user_id' => User::query()->where('username', 'staff_ops')->value('id'),
                'review_year' => 2026,
                'review_cycle' => 'annual',
                'performance_score' => 80,
                'potential_score' => 80,
                'competency_score' => 80,
                'readiness' => 'developing',
                'status' => 'draft',
            ])
            ->assertForbidden();
    }

    public function test_internship_certificate_can_be_signed_and_publicly_verified(): void
    {
        $hrd = User::query()->where('username', 'sonia')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $intern = User::query()->where('username', 'maulana')->firstOrFail();

        $created = $this->actingAs($hrd)
            ->postJson('/api/documents/internship-certificates', [
                'owner_user_id' => $intern->id,
                'program_name' => 'Internship Arsitektur Digital',
                'start_date' => '2026-01-05',
                'end_date' => '2026-04-05',
                'issued_at' => '2026-04-10',
                'performance_label' => 'Sangat Baik',
            ])
            ->assertCreated()
            ->assertJsonPath('document.status', 'draft');

        $documentId = $created->json('document.id');
        $signed = $this->actingAs($ceo)
            ->postJson("/api/documents/{$documentId}/sign")
            ->assertOk()
            ->assertJsonPath('document.status', 'signed')
            ->assertJsonPath('document.integrity_valid', true);

        $document = ErpDocument::query()->findOrFail($documentId);
        $this->assertNotNull($document->document_hash);
        $this->assertDatabaseHas('document_signatures', [
            'document_id' => $document->id,
            'signer_id' => $ceo->id,
        ]);

        $this->get("/verify/certificate/{$document->verification_token}")
            ->assertOk()
            ->assertSee('Dokumen valid dan tidak berubah')
            ->assertSee($document->document_number);

        $this->get("/certificate/{$document->verification_token}")
            ->assertOk()
            ->assertSee('Sertifikat Magang')
            ->assertSee('Sangat Baik');

        $this->assertStringContainsString(
            "/verify/certificate/{$document->verification_token}",
            $signed->json('document.verification_url'),
        );

        $this->actingAs($hrd)
            ->postJson("/api/documents/{$document->id}/revoke", [
                'reason' => 'Sertifikat diganti karena terdapat koreksi data program.',
            ])
            ->assertOk()
            ->assertJsonPath('document.status', 'revoked');

        $this->get("/verify/certificate/{$document->verification_token}")
            ->assertOk()
            ->assertSee('Dokumen tidak berlaku')
            ->assertSee('Sertifikat ini telah dicabut');
    }

    public function test_free_certificate_template_signature_and_qr_work_without_external_subscription(): void
    {
        Storage::fake('local');
        $hrd = User::query()->where('username', 'sonia')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();
        $intern = User::query()->where('username', 'maulana')->firstOrFail();

        $this->actingAs($hrd)
            ->post('/api/documents/templates', [
                'name' => 'Template Canva Gratis',
                'background' => UploadedFile::fake()->image('sertifikat.png', 1600, 1131),
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('template.is_active', true);

        $template = CertificateTemplate::query()->firstOrFail();
        Storage::disk('local')->assertExists($template->background_path);

        $this->actingAs($ceo)
            ->post('/api/documents/signature-profile', [
                'signature' => UploadedFile::fake()->image('tanda-tangan.png', 600, 240),
                'consent' => '1',
            ], ['Accept' => 'application/json'])
            ->assertOk()
            ->assertJsonPath('configured', true);

        $created = $this->actingAs($hrd)
            ->postJson('/api/documents/internship-certificates', [
                'owner_user_id' => $intern->id,
                'program_name' => 'Magang Desain Arsitektur',
                'start_date' => '2026-01-05',
                'end_date' => '2026-04-05',
                'issued_at' => '2026-04-10',
                'performance_label' => 'Sangat Baik',
                'supervisor_user_id' => $ceo->id,
                'certificate_template_id' => $template->id,
            ])
            ->assertCreated()
            ->assertJsonPath('document.can_sign', false);

        $documentId = $created->json('document.id');
        $this->actingAs($ceo)
            ->postJson("/api/documents/{$documentId}/sign")
            ->assertOk()
            ->assertJsonPath('document.integrity_valid', true)
            ->assertJsonPath('document.visual_signature', true);

        $document = ErpDocument::query()->findOrFail($documentId);
        $signature = $document->signatures()->firstOrFail();
        Storage::disk('local')->assertExists($signature->image_path);
        $this->assertSame(2, data_get($signature->metadata, 'hash_version'));
        $this->assertNotEmpty(data_get($signature->metadata, 'signature_image_hash'));

        $this->get("/certificate/{$document->verification_token}")
            ->assertOk()
            ->assertSee('data:image/svg+xml;base64,', false)
            ->assertSee(route('certificates.background', $document->verification_token), false)
            ->assertSee(route('certificates.signature', $document->verification_token), false);

        $this->get("/certificate/{$document->verification_token}/background")
            ->assertOk();
        $this->get("/certificate/{$document->verification_token}/signature")
            ->assertOk();
    }

    public function test_client_payment_and_project_cost_flow_into_balanced_accounting_and_profit_loss(): void
    {
        $finance = User::query()->where('username', 'mgr_finance')->firstOrFail();

        $inflow = $this->actingAs($finance)
            ->postJson('/api/client-inflows', [
                'date' => '2026-07-01',
                'client_name' => 'Klien Desain Test',
                'domicile' => 'Bandung',
                'client_no' => 'TEST-001',
                'start_project' => '2026-07',
                'package' => 'Desain Arsitektur',
                'notes' => 'Proyek desain rumah tinggal',
                'project_value' => 100000000,
                'termin_no' => '1',
                'total_termin' => '1',
                'payment_amount' => 100000000,
                'pj_survey' => 'Tim Ops',
            ])
            ->assertOk();

        $this->assertNotNull($inflow->json('data.id'));
        $project = Project::query()->where('client_name', 'Klien Desain Test')->firstOrFail();
        $this->assertSame('design', $project->project_type);
        $this->assertDatabaseHas('journal_entries', [
            'source_type' => 'client_inflow',
            'source_id' => $inflow->json('data.id'),
        ]);

        $project->update(['budget_cost' => 30000000, 'progress' => 40]);
        $this->actingAs($finance)
            ->postJson("/api/projects/{$project->id}/costs", [
                'cost_date' => '2026-07-12',
                'category' => 'design_labor',
                'description' => 'Honor tim desain fase konsep',
                'amount' => 20000000,
            ])
            ->assertCreated()
            ->assertJsonPath('summary.actual_cost', 20000000)
            ->assertJsonPath('summary.estimated_margin', 80000000);

        $report = $this->actingAs($finance)
            ->getJson('/api/accounting?year=2026&month=7')
            ->assertOk()
            ->assertJsonPath('monthly_profit_loss.revenue', 100000000)
            ->assertJsonPath('monthly_profit_loss.expenses', 20000000)
            ->assertJsonPath('monthly_profit_loss.net_profit', 80000000);

        $this->assertSame(2, JournalEntry::query()->count());
        foreach (JournalEntry::query()->with('lines')->get() as $entry) {
            $this->assertEquals(
                (float) $entry->lines->sum('debit'),
                (float) $entry->lines->sum('credit'),
            );
        }
        $this->assertCount(12, $report->json('annual_evaluation.months'));
    }

    public function test_accounting_csv_import_is_balanced_idempotent_and_updates_profit_loss(): void
    {
        $finance = User::query()->where('username', 'mgr_finance')->firstOrFail();
        $csv = implode("\n", [
            'tanggal,jenis,kategori,nilai,keterangan,kode_proyek,referensi',
            '2026-07-21,Pendapatan,Pendapatan Jasa Desain,15000000,Pelunasan desain,,IMP-REV-001',
            '22/07/2026,Biaya,Beban Operasional,"750.000",Internet kantor,,IMP-EXP-001',
        ]);

        $this->actingAs($finance)
            ->post('/api/accounting/import-transactions', [
                'file' => UploadedFile::fake()->createWithContent('laporan-juli.csv', $csv),
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('summary.imported', 2)
            ->assertJsonPath('summary.skipped', 0)
            ->assertJsonPath('summary.total_amount', 15750000);

        $this->assertDatabaseHas('journal_entries', [
            'reference' => 'IMP-REV-001',
            'source_type' => 'accounting_file_import',
        ]);
        $this->assertDatabaseHas('journal_entries', [
            'reference' => 'IMP-EXP-001',
            'source_type' => 'accounting_file_import',
        ]);

        foreach (JournalEntry::query()->with('lines')->get() as $entry) {
            $this->assertEquals(
                (float) $entry->lines->sum('debit'),
                (float) $entry->lines->sum('credit'),
            );
        }

        $this->actingAs($finance)
            ->getJson('/api/accounting?year=2026&month=7')
            ->assertOk()
            ->assertJsonPath('monthly_profit_loss.revenue', 15000000)
            ->assertJsonPath('monthly_profit_loss.expenses', 750000)
            ->assertJsonPath('monthly_profit_loss.net_profit', 14250000);

        $this->actingAs($finance)
            ->post('/api/accounting/import-transactions', [
                'file' => UploadedFile::fake()->createWithContent('laporan-juli.csv', $csv),
            ], ['Accept' => 'application/json'])
            ->assertUnprocessable();
        $this->assertSame(2, JournalEntry::query()->count());
    }

    public function test_financial_supporting_document_is_private_and_downloadable_by_authorized_roles(): void
    {
        Storage::fake('local');
        $finance = User::query()->where('username', 'mgr_finance')->firstOrFail();
        $marketing = User::query()->where('username', 'mgr_marketing')->firstOrFail();

        $created = $this->actingAs($finance)
            ->post('/api/accounting/transactions', [
                'date' => '2026-07-28',
                'kind' => 'expense',
                'category' => 'operating_expense',
                'amount' => 250000,
                'description' => 'Pembelian alat tulis',
                'attachment' => UploadedFile::fake()->create('nota-atk.pdf', 12, 'application/pdf'),
            ], ['Accept' => 'application/json'])
            ->assertCreated()
            ->assertJsonPath('entry.attachments.0.original_name', 'nota-atk.pdf');

        $attachmentId = $created->json('entry.attachments.0.id');
        $storedPath = \App\Models\RecordAttachment::query()->findOrFail($attachmentId)->stored_path;
        Storage::disk('local')->assertExists($storedPath);

        $this->actingAs($finance)
            ->get("/api/record-attachments/{$attachmentId}")
            ->assertOk();

        $this->actingAs($marketing)
            ->get("/api/record-attachments/{$attachmentId}")
            ->assertForbidden();
    }

    public function test_advanced_analytics_hides_finance_outside_authorized_roles(): void
    {
        $marketing = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $ceo = User::query()->where('username', 'ceo')->firstOrFail();

        $this->actingAs($marketing)
            ->getJson('/api/analytics/overview?year=2026')
            ->assertOk()
            ->assertJsonPath('scope', 'division_team')
            ->assertJsonPath('financial_visible', false)
            ->assertJsonPath('financial', null)
            ->assertJsonPath('projects.visible', false);

        $this->actingAs($ceo)
            ->getJson('/api/analytics/overview?year=2026')
            ->assertOk()
            ->assertJsonPath('scope', 'company')
            ->assertJsonPath('financial_visible', true)
            ->assertJsonPath('projects.visible', true);
    }
}
