<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class InitializeCompanyDivisions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'erp:init-divisions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Initialize 5 core divisions and map existing modules to them for all companies';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $companies = \App\Models\Company::all();

        $divisions = [
            'Infrastruktur Sistem & Eksekutif' => ['order' => 1, 'keys' => ['core_security', 'core_workflow', 'intelligence', 'documents', 'report_builder']],
            'Divisi Human Resources (HRIS)' => ['order' => 2, 'keys' => ['people', 'payroll', 'alumni_network', 'location_tracking', 'warning_letters']],
            'Divisi Keuangan (Finance)' => ['order' => 3, 'keys' => ['accounting', 'project_costing', 'auto_cogs']],
            'Divisi Penjualan (Sales & CRM)' => ['order' => 4, 'keys' => ['crm', 'pos', 'client_portal']],
            'Divisi Operasional & SCM' => ['order' => 5, 'keys' => ['inventory', 'purchasing', 'production', 'material_request', 'purchase_request']],
        ];

        foreach ($companies as $company) {
            foreach ($divisions as $name => $data) {
                $div = \App\Models\CompanyDivision::firstOrCreate(
                    ['company_id' => $company->id, 'name' => $name],
                    ['order' => $data['order']]
                );

                // Update features that belong to this division
                \App\Models\CompanyFeature::where('company_id', $company->id)
                    ->whereIn('feature_key', $data['keys'])
                    ->update(['company_division_id' => $div->id]);
            }
            
            // Clear cache
            \Illuminate\Support\Facades\Cache::forget("company.{$company->id}.features.catalogue");
        }

        $this->info('Divisions initialized and modules mapped successfully.');
    }
}
