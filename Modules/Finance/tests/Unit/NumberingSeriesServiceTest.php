<?php

declare(strict_types=1);

namespace Modules\Finance\Tests\Unit;

use App\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Finance\Models\NumberingSeries;
use Modules\Finance\Services\NumberingSeriesService;
use Tests\TestCase;

class NumberingSeriesServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_generate_next_document_number(): void
    {
        $company = Company::factory()->create();

        NumberingSeries::factory()->create([
            'company_id' => $company->id,
            'module_code' => 'FINANCE',
            'document_type' => 'JOURNAL_ENTRY',
            'prefix' => 'JV-{YYYY}-',
            'length' => 5,
            'current_number' => 0,
            'is_active' => true,
        ]);

        /** @var NumberingSeriesService $service */
        $service = app(NumberingSeriesService::class);

        $nextNumber = $service->generateNextNumber('FINANCE', 'JOURNAL_ENTRY');

        $expectedPrefix = 'JV-'.date('Y').'-00001';
        $this->assertEquals($expectedPrefix, $nextNumber);
    }
}
