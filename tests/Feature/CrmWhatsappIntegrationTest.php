<?php

namespace Tests\Feature;

use App\Models\ClientInflow;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class CrmWhatsappIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
        $this->artisan('db:seed', ['--class' => 'DataMigrationSeeder']);
    }

    public function test_whatsapp_lead_is_normalized_deduplicated_and_keeps_activity_history(): void
    {
        $marketing = User::query()->where('username', 'maulana')->firstOrFail();

        $created = $this->actingAs($marketing)
            ->postJson('/api/crm/leads', [
                'name' => 'Ibu Nadia - Renovasi BSD',
                'phone' => '0812-3456-7890',
                'email' => 'nadia@example.com',
                'budget' => 'Rp 750Jt',
                'source' => 'WhatsApp',
                'campaign' => 'Meta Ads Renovasi Juli',
                'type' => 'Renovasi',
                'initial_message' => 'Saya ingin renovasi rumah dua lantai.',
                'next_follow_up_at' => '2026-07-31 09:00:00',
            ])
            ->assertCreated()
            ->assertJsonPath('phone', '6281234567890')
            ->assertJsonPath('source', 'WhatsApp')
            ->assertJsonPath('project_value', 750000000);

        $leadId = (int) preg_replace('/\D+/', '', $created->json('id'));

        $this->actingAs($marketing)
            ->postJson('/api/crm/leads', [
                'name' => 'Nadia dari WhatsApp',
                'phone' => '+62 812 3456 7890',
                'source' => 'WhatsApp',
                'initial_message' => 'Apakah besok bisa konsultasi?',
            ])
            ->assertOk()
            ->assertJsonPath('duplicate_merged', true)
            ->assertJsonPath('id', "lead-{$leadId}");

        $this->assertSame(1, Lead::query()->where('phone', '6281234567890')->count());

        $this->actingAs($marketing)
            ->postJson("/api/crm/leads/{$leadId}/activities", [
                'type' => 'message',
                'channel' => 'whatsapp',
                'direction' => 'outbound',
                'body' => 'Bisa, kami jadwalkan konsultasi pukul 10.00.',
                'next_follow_up_at' => '2026-08-01 10:00:00',
            ])
            ->assertCreated()
            ->assertJsonPath('activity.channel', 'whatsapp');

        $lead = Lead::query()->findOrFail($leadId);
        $this->assertNotNull($lead->first_response_at);
        $this->assertSame(3, $lead->activities()->count());

        $this->actingAs($marketing)
            ->getJson("/api/crm/leads/{$leadId}")
            ->assertOk()
            ->assertJsonCount(3, 'activities')
            ->assertJsonPath('lead.campaign', 'Meta Ads Renovasi Juli');
    }

    public function test_finance_payment_links_to_lead_and_becomes_actual_crm_revenue(): void
    {
        $marketing = User::query()->where('username', 'mgr_marketing')->firstOrFail();
        $finance = User::query()->where('username', 'mgr_finance')->firstOrFail();

        $leadResponse = $this->actingAs($marketing)
            ->postJson('/api/crm/leads', [
                'name' => 'Bapak Arif - Rumah Bogor',
                'phone' => '0819 555 0101',
                'project_value' => 400000000,
                'source' => 'WhatsApp',
                'campaign' => 'Klik WhatsApp Website',
                'type' => 'Desain',
            ])
            ->assertCreated();

        $leadId = (int) preg_replace('/\D+/', '', $leadResponse->json('id'));

        $inflow = $this->actingAs($finance)
            ->postJson('/api/client-inflows', [
                'date' => '2026-07-30',
                'client_name' => 'Bapak Arif - Rumah Bogor',
                'client_no' => '0819-555-0101',
                'start_project' => '2026-08',
                'package' => 'Desain Arsitektur',
                'notes' => 'DP desain',
                'project_value' => 400000000,
                'termin_no' => '1',
                'total_termin' => '4',
                'payment_amount' => 100000000,
                'pj_survey' => 'Tim Desain',
            ])
            ->assertOk();

        $this->assertDatabaseHas('client_inflows', [
            'id' => $inflow->json('data.id'),
            'lead_id' => $leadId,
        ]);
        $this->assertSame($leadId, ClientInflow::query()->firstOrFail()->lead_id);

        $lead = Lead::query()->findOrFail($leadId);
        $this->assertSame('deal', $lead->status);
        $this->assertNotNull($lead->won_at);
        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $leadId,
            'type' => 'payment',
            'external_key' => 'client-inflow:'.$inflow->json('data.id'),
        ]);

        $this->actingAs($marketing)
            ->getJson('/api/crm/overview')
            ->assertOk()
            ->assertJsonPath('summary.won_leads', 1)
            ->assertJsonPath('summary.actual_revenue', 100000000)
            ->assertJsonPath('sources.0.source', 'WhatsApp')
            ->assertJsonPath('sources.0.actual_revenue', 100000000);
    }

    public function test_non_marketing_employee_cannot_read_crm_data(): void
    {
        $finance = User::query()->where('username', 'staff_finance')->firstOrFail();

        $this->actingAs($finance)
            ->getJson('/api/crm/overview')
            ->assertForbidden();

        $this->actingAs($finance)
            ->postJson('/api/crm/whatsapp/intake', [
                'phone' => '081200000000',
                'message' => 'Halo',
            ])
            ->assertForbidden();
    }
}
