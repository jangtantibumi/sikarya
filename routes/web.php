<?php

use App\Http\Controllers\AccessGateController;
use App\Http\Controllers\Api\AccountingController;
use App\Http\Controllers\Api\AdvancedAnalyticsController;
use App\Http\Controllers\Api\AlumniController;
use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\ChatMessageController;
use App\Http\Controllers\Api\CompanyModuleController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\PurchaseRequestController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\ProductionOrderController;
use App\Http\Controllers\Api\PosController;
use App\Http\Controllers\Api\GoodsReceiptController;
use App\Http\Controllers\Api\DashboardMetricController;
use App\Http\Controllers\Api\DataBackupController;
use App\Http\Controllers\Api\DataDeletionController;
use App\Http\Controllers\Api\DocumentController;
use App\Http\Controllers\Api\GeminiController;
use App\Http\Controllers\Api\GoalController;
use App\Http\Controllers\Api\KpiController;
use App\Http\Controllers\Api\LeaveRequestController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\OrganizationChartController;
use App\Http\Controllers\Api\OrganizationController;
use App\Http\Controllers\Api\ProjectCostingController;
use App\Http\Controllers\Api\PayrollController;
use App\Http\Controllers\Api\RecordAttachmentController;
use App\Http\Controllers\Api\ResignationRequestController;
use App\Http\Controllers\Api\SystemControlController;
use App\Http\Controllers\Api\TalentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClientInflowController;
use App\Http\Controllers\CrmController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\MasterProductDemoController;
use App\Http\Controllers\RuleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamRequestController;
use App\Http\Controllers\WhatsAppCloudController;
use App\Http\Controllers\WhatsAppWebhookController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

if (app()->environment('production')) {
    URL::forceScheme('https');
}

Route::get('/erp-access', [AccessGateController::class, 'show'])
    ->name('erp-access.show');
Route::post('/erp-access', [AccessGateController::class, 'verify'])
    ->middleware('throttle:10,1')
    ->name('erp-access.verify');

Route::get('/master-demo/login', [MasterProductDemoController::class, 'login'])->name('master-demo.login');
Route::post('/master-demo/login', [MasterProductDemoController::class, 'authenticate'])->name('master-demo.login.attempt');
Route::post('/master-demo/logout', [MasterProductDemoController::class, 'logout'])->name('master-demo.logout');
Route::get('/master-demo/purchasing', [MasterProductDemoController::class, 'purchasing'])->middleware('master.demo.auth')->name('master-demo.purchasing');
Route::get('/master-demo/app', function () {
    $user = auth()->user();
    if (!$user->isCEO() && !$user->isPlatformAdmin()) {
        return redirect()->route('master-demo.employee');
    }
    
    $company = $user->company ?? \App\Models\Company::first();
    
    // Ensure default roles exist for demo if roles table is empty
    if (\App\Models\Role::where('company_id', $company->id)->count() === 0) {
        \App\Models\Role::create(['company_id' => $company->id, 'name' => 'Super Administrator', 'key' => 'superadmin', 'description' => 'Full access to all system features', 'permissions' => []]);
        \App\Models\Role::create(['company_id' => $company->id, 'name' => 'Finance Manager', 'key' => 'finance_manager', 'description' => 'Access to Accounting, Purchasing, & Finance', 'permissions' => []]);
        \App\Models\Role::create(['company_id' => $company->id, 'name' => 'Warehouse Staff', 'key' => 'warehouse_staff', 'description' => 'Limited access to Inventory and Stock Opname', 'permissions' => []]);
    }
    
    // Ensure default divisions exist for demo
    if (\App\Models\CompanyDivision::where('company_id', $company->id)->count() === 0) {
        $defaultGroups = collect(config('master_modules'))->pluck('group')->unique()->values();
        foreach ($defaultGroups as $index => $group) {
            $div = \App\Models\CompanyDivision::create([
                'company_id' => $company->id,
                'name' => $group,
                'order' => $index,
            ]);
            
            // Assign features that belong to this group to this division, excluding 'coming_soon'
            $featureKeys = collect(config('master_modules'))
                ->filter(fn($f) => $f['group'] === $group && $f['default'] !== 'coming_soon')
                ->keys();
            foreach ($featureKeys as $key) {
                \App\Models\CompanyFeature::updateOrCreate(
                    ['company_id' => $company->id, 'feature_key' => $key],
                    ['company_division_id' => $div->id]
                );
            }
        }
        \Illuminate\Support\Facades\Cache::forget("company.{$company->id}.features.catalogue");
    }
    
    return view('master-portal', [
        'company' => $company,
        'features' => app(\App\Services\CompanyFeatureManager::class)->catalogue($company),
        'divisions' => \App\Models\CompanyDivision::where('company_id', $company->id)->orderBy('order')->get(),
        'user' => $user,
        'roles' => \App\Models\Role::with(['users' => function($q) use ($company) {
            $q->where('company_id', $company->id);
        }])->where('company_id', $company->id)->get(),
        'allUsers' => \App\Models\User::where('company_id', $company->id)->where('is_active', true)->orderBy('name')->get(),
          'attendances' => \App\Models\Attendance::with(['user', 'shift'])->whereHas('user', function($q) use ($company) {
              $q->where('company_id', $company->id);
          })->orderBy('clock_in', 'desc')->get(),
        'shifts' => \App\Models\Shift::where('company_id', $company->id)->get(),
        'overtimeTypes' => \App\Models\OvertimeType::where('company_id', $company->id)->get(),
        'attendanceSettings' => \App\Models\AttendanceSetting::where('company_id', $company->id)->get(),
        'salaryComponents' => \App\Models\HrSalaryComponent::where('company_id', $company->id)->get(),
    ]);
})->middleware('master.demo.auth')->name('master-demo.app');

Route::post('/master-demo/security/roles', [\App\Http\Controllers\SecurityController::class, 'storeRole'])->middleware('master.demo.auth')->name('master-demo.security.roles.store');
Route::patch('/master-demo/security/assign', [\App\Http\Controllers\SecurityController::class, 'assignRole'])->middleware('master.demo.auth')->name('master-demo.security.assign');
Route::delete('/master-demo/security/revoke/{user_id}', [\App\Http\Controllers\SecurityController::class, 'revokeRole'])->middleware('master.demo.auth')->name('master-demo.security.revoke');
Route::delete('/master-demo/security/audit-logs/clear', [\App\Http\Controllers\SecurityController::class, 'clearAuditLogs'])->middleware('master.demo.auth')->name('master-demo.security.audit-logs.clear');
Route::get('/master-demo/security/audit-logs', [\App\Http\Controllers\SecurityController::class, 'getAuditLogs'])->middleware('master.demo.auth')->name('master-demo.security.audit-logs');

Route::get('/master-demo/employee', [\App\Http\Controllers\EmployeePortalController::class, 'index'])->middleware('master.demo.auth')->name('master-demo.employee');
Route::post('/master-demo/employee/profile', [\App\Http\Controllers\EmployeePortalController::class, 'updateProfile'])->middleware('master.demo.auth')->name('master-demo.employee.profile');
Route::post('/master-demo/employee/report', [\App\Http\Controllers\EmployeePortalController::class, 'submitReport'])->middleware('master.demo.auth')->name('master-demo.employee.report');
Route::post('/master-demo/employee/overtime', [\App\Http\Controllers\EmployeePortalController::class, 'submitOvertimeRequest'])->middleware('master.demo.auth')->name('master-demo.employee.overtime');
Route::get('/master-demo', [MasterProductDemoController::class, 'index'])->middleware('master.demo.auth')->name('master-demo');
Route::patch('/master-demo/companies/{company}/features/{feature}', [MasterProductDemoController::class, 'updateFeature'])->middleware('master.demo.auth')->name('master-demo.feature');

// HRIS Master Portal Routes
Route::middleware('master.demo.auth')->group(function () {
    Route::get('/master-demo/shifts', [\App\Http\Controllers\HrisController::class, 'manageShifts'])->name('master-demo.shifts');
    Route::post('/master-demo/shifts', [\App\Http\Controllers\HrisController::class, 'storeShift'])->name('master-demo.shifts.store');
    Route::put('/master-demo/shifts/{id}', [\App\Http\Controllers\HrisController::class, 'updateShift'])->name('master-demo.shifts.update');
    Route::delete('/master-demo/shifts/{id}', [\App\Http\Controllers\HrisController::class, 'destroyShift'])->name('master-demo.shifts.destroy');
    
    Route::post('/master-demo/holidays', [\App\Http\Controllers\HrisController::class, 'storeHoliday'])->name('master-demo.holidays.store');
    Route::delete('/master-demo/holidays/{id}', [\App\Http\Controllers\HrisController::class, 'destroyHoliday'])->name('master-demo.holidays.destroy');
    
    Route::post('/master-demo/overtime-types', [\App\Http\Controllers\HrisController::class, 'storeOvertimeType'])->name('master-demo.overtime.store');
    Route::put('/master-demo/overtime/{id}', [\App\Http\Controllers\HrisController::class, 'updateOvertimeType'])->name('master-demo.overtime.update');
    Route::delete('/master-demo/overtime/{id}', [\App\Http\Controllers\HrisController::class, 'destroyOvertimeType'])->name('master-demo.overtime.destroy');
    Route::post('/master-demo/attendance-settings', [\App\Http\Controllers\HrisController::class, 'storeAttendanceSetting'])->name('master-demo.attendance-settings.store');
    Route::put('/master-demo/attendance-settings/{id}', [\App\Http\Controllers\HrisController::class, 'updateAttendanceSetting'])->name('master-demo.attendance-settings.update');
    Route::delete('/master-demo/attendance-settings/{id}', [\App\Http\Controllers\HrisController::class, 'destroyAttendanceSetting'])->name('master-demo.attendance-settings.destroy');
    
    Route::post('/master-demo/salary-components', [\App\Http\Controllers\HrisController::class, 'storeSalaryComponent'])->name('master-demo.salary-components.store');
    Route::put('/master-demo/salary-components/{id}', [\App\Http\Controllers\HrisController::class, 'updateSalaryComponent'])->name('master-demo.salary-components.update');
    Route::delete('/master-demo/salary-components/{id}', [\App\Http\Controllers\HrisController::class, 'destroySalaryComponent'])->name('master-demo.salary-components.destroy');
    Route::get('/master-demo/backup', [\App\Http\Controllers\HrisController::class, 'downloadBackup'])->name('master-demo.backup');

    Route::get('/master-demo/org-chart', [\App\Http\Controllers\HrisController::class, 'manageOrgChart'])->name('master-demo.org-chart');
    Route::post('/master-demo/employee/hire', [\App\Http\Controllers\HrisController::class, 'storeEmployee'])->name('master-demo.employee.hire');
    Route::post('/master-demo/employee/{id}/approve', [\App\Http\Controllers\HrisController::class, 'approveEmployee'])->name('master-demo.employee.approve');
    Route::post('/master-demo/employee/reset-password', [\App\Http\Controllers\HrisController::class, 'resetPassword'])->name('master-demo.employee.reset-password');
    Route::post('/master-demo/hris/update-user', [\App\Http\Controllers\HrisController::class, 'updateUser'])->name('master-demo.hris.updateUser');
    Route::get('/master-demo/tasks/list', [\App\Http\Controllers\HrisController::class, 'getTasksList'])->name('master-demo.tasks.list');
    Route::post('/master-demo/tasks/store', [\App\Http\Controllers\HrisController::class, 'storeTask'])->name('master-demo.tasks.store');
    Route::put('/master-demo/tasks/{id}', [\App\Http\Controllers\HrisController::class, 'updateTask'])->name('master-demo.tasks.update');
    Route::delete('/master-demo/tasks/{id}', [\App\Http\Controllers\HrisController::class, 'deleteTask'])->name('master-demo.tasks.delete');
    
    Route::post('/master-demo/payslip/upload', [\App\Http\Controllers\HrisController::class, 'uploadPayslip'])->name('master-demo.payslip.upload');
    Route::delete('/master-demo/payslip/{id}', [\App\Http\Controllers\HrisController::class, 'deletePayslip'])->name('master-demo.payslip.delete');
    
    Route::post('/master-demo/documents/upload', [\App\Http\Controllers\HrisController::class, 'uploadDocument'])->name('master-demo.documents.upload');
    Route::delete('/master-demo/documents/{id}', [\App\Http\Controllers\HrisController::class, 'deleteDocument'])->name('master-demo.documents.delete');

    Route::delete('/master-demo/employee/{id}', [\App\Http\Controllers\HrisController::class, 'deleteEmployee'])->name('master-demo.employee.delete');
    Route::delete('/master-demo/shifts/{id}', [\App\Http\Controllers\HrisController::class, 'deleteShift'])->name('master-demo.shifts.delete');
    Route::post('/master-demo/production/backflush', [\App\Http\Controllers\ProductionController::class, 'autoBackflush'])->name('master-demo.production.backflush');
    // Inventory UMKM Routes
    Route::get('/master-demo/inventory-umkm', [\App\Http\Controllers\InventoryUmkmController::class, 'index'])->name('master-demo.inventory-umkm.index');
    Route::post('/master-demo/inventory-umkm', [\App\Http\Controllers\InventoryUmkmController::class, 'store'])->name('master-demo.inventory-umkm.store');
    Route::put('/master-demo/inventory-umkm/{id}', [\App\Http\Controllers\InventoryUmkmController::class, 'update'])->name('master-demo.inventory-umkm.update');
    Route::delete('/master-demo/inventory-umkm/{id}', [\App\Http\Controllers\InventoryUmkmController::class, 'destroy'])->name('master-demo.inventory-umkm.destroy');

    // Production & BOM Routes
    Route::post('/master-demo/recipes/store', [\App\Http\Controllers\RecipeController::class, 'store'])->name('master-demo.recipes.store');
    Route::post('/master-demo/production/bom', [\App\Http\Controllers\ProductionController::class, 'storeBom'])->name('master-demo.bom.store');
    Route::post('/master-demo/production/work-orders', [\App\Http\Controllers\ProductionController::class, 'createWorkOrder'])->name('master-demo.wo.store');
    Route::post('/master-demo/production/work-orders/{id}/issue', [\App\Http\Controllers\ProductionController::class, 'issueMaterial'])->name('master-demo.wo.issue');
    Route::post('/master-demo/production/work-orders/{id}/consume', [\App\Http\Controllers\ProductionController::class, 'consumeMaterial'])->name('master-demo.wo.consume');
    Route::post('/master-demo/production/work-orders/{id}/waste', [\App\Http\Controllers\ProductionController::class, 'reportWaste'])->name('master-demo.wo.waste');
    Route::post('/master-demo/production/work-orders/{id}/complete', [\App\Http\Controllers\ProductionController::class, 'completeWorkOrder'])->name('master-demo.wo.complete');
    
    // Purchasing Routes
    Route::post('/master-demo/purchasing/pr', [\App\Http\Controllers\PurchasingController::class, 'storePR'])->name('master-demo.pr.store');
    Route::post('/master-demo/purchasing/pr/{id}/approve', [\App\Http\Controllers\PurchasingController::class, 'approvePR'])->name('master-demo.pr.approve');
    Route::post('/master-demo/purchasing/po', [\App\Http\Controllers\PurchasingController::class, 'storePO'])->name('master-demo.po.store');
    Route::post('/master-demo/purchasing/po/{id}/approve', [\App\Http\Controllers\PurchasingController::class, 'approvePO'])->name('master-demo.po.approve');
    Route::post('/master-demo/purchasing/gr', [\App\Http\Controllers\PurchasingController::class, 'storeGR'])->name('master-demo.gr.store');
    Route::post('/master-demo/purchasing/gr/{id}/approve', [\App\Http\Controllers\PurchasingController::class, 'approveGR'])->name('master-demo.gr.approve');
    Route::post('/master-demo/products/material/update', [\App\Http\Controllers\MasterProductDemoController::class, 'updateMaterial'])->name('master-demo.products.updateMaterial');

    // Organization Routes
    Route::get('/organization/tree', [\App\Http\Controllers\Api\OrganizationController::class, 'tree']);
    Route::get('/organization/node/{id}', [\App\Http\Controllers\Api\OrganizationController::class, 'nodeDetails']);
    Route::put('/organization/node/{id}/edit', [\App\Http\Controllers\Api\OrganizationController::class, 'updateProfile']);
    Route::post('/organization/node/{id}/performance', [\App\Http\Controllers\Api\OrganizationController::class, 'addPerformance']);
    Route::post('/organization/node/{id}/assign', [\App\Http\Controllers\Api\OrganizationController::class, 'assign']);
    Route::delete('/organization/node/{id}/delete', [\App\Http\Controllers\Api\OrganizationController::class, 'destroy']);
    Route::post('/organization/add-staff', [\App\Http\Controllers\Api\OrganizationController::class, 'store']);
    Route::post('/organization/appoint-manager', [\App\Http\Controllers\Api\OrganizationController::class, 'appointManager']);
    
    // PDF Generation Routes
    Route::get('/payroll/slip/{employee}/preview', [\App\Http\Controllers\Api\PayrollController::class, 'slipPreview'])->name('master-demo.payroll.slip.preview');
    Route::post('/payroll/slip/{employee}/generate', [\App\Http\Controllers\Api\PayrollController::class, 'slipGenerate'])->name('master-demo.payroll.slip.generate');
    Route::get('/paklaring/{employee}/preview', [\App\Http\Controllers\Api\PaklaringController::class, 'preview'])->name('master-demo.paklaring.preview');
    Route::post('/paklaring/{employee}/generate', [\App\Http\Controllers\Api\PaklaringController::class, 'generate'])->name('master-demo.paklaring.generate');
    // Division Settings Routes
    Route::post('/api/divisions', [\App\Http\Controllers\DivisionSettingsController::class, 'store'])->name('master-demo.divisions.store');
    Route::put('/api/divisions/{id}', [\App\Http\Controllers\DivisionSettingsController::class, 'update'])->name('master-demo.divisions.update');
    Route::delete('/api/divisions/by-name', [\App\Http\Controllers\DivisionSettingsController::class, 'destroyByName'])->name('master-demo.divisions.destroyByName');
    Route::delete('/api/divisions/{id}', [\App\Http\Controllers\DivisionSettingsController::class, 'destroy'])->name('master-demo.divisions.destroy');
    Route::post('/api/features/assign', [\App\Http\Controllers\DivisionSettingsController::class, 'assignFeature'])->name('master-demo.features.assign');
});

    Route::get('/master-demo/finance', [\App\Http\Controllers\FinanceController::class, 'index'])->middleware('master.demo.auth')->name('master-demo.finance');
Route::post('/master-demo/finance/journal', [\App\Http\Controllers\FinanceController::class, 'storeJournal'])->middleware('master.demo.auth')->name('master-demo.finance.journal');


// HRIS Employee Portal Routes
Route::middleware('master.demo.auth')->group(function () {
    Route::post('/master-demo/employee/leave/{id}/cancel', [\App\Http\Controllers\HrisController::class, 'cancelLeaveRequest'])->name('master-demo.leave.cancel');
    Route::get('/master-demo/chat/channels/list', [\App\Http\Controllers\ChatController::class, 'getChannels']);
    Route::post('/master-demo/chat/channels', [\App\Http\Controllers\ChatController::class, 'createChannel']);
    Route::get('/master-demo/chat/{channel}', [\App\Http\Controllers\ChatController::class, 'getMessages']);
    Route::post('/master-demo/chat', [\App\Http\Controllers\ChatController::class, 'sendMessage']);
    Route::delete('/master-demo/chat/{id}', [\App\Http\Controllers\ChatController::class, 'destroy']);
    Route::post('/master-demo/announcements', [\App\Http\Controllers\AnnouncementController::class, 'store']);
    Route::delete('/master-demo/announcements/{id}', [\App\Http\Controllers\AnnouncementController::class, 'destroy']);
    Route::post('/master-demo/announcements/bulk-delete', [\App\Http\Controllers\AnnouncementController::class, 'bulkDelete']);
    // POS (Point of Sale) Routes
    Route::post('/master-demo/pos/session/open', [\App\Http\Controllers\PosController::class, 'openSession'])->name('master-demo.pos.open');
    Route::post('/master-demo/pos/session/close', [\App\Http\Controllers\PosController::class, 'closeSession'])->name('master-demo.pos.close');
    Route::post('/master-demo/pos/sale', [\App\Http\Controllers\PosController::class, 'storeSale'])->name('master-demo.pos.sale');

    // Attendance Routes (master-demo portal)
    Route::post('/master-demo/attendance/clock-in', [\App\Http\Controllers\MasterAttendanceController::class, 'clockIn'])->name('master-demo.attendance.clock-in');
    Route::post('/master-demo/attendance/clock-out', [\App\Http\Controllers\MasterAttendanceController::class, 'clockOut'])->name('master-demo.attendance.clock-out');
    Route::post('/master-demo/attendance/rest-start', [\App\Http\Controllers\MasterAttendanceController::class, 'restStart'])->name('master-demo.attendance.rest-start');
    Route::post('/master-demo/attendance/rest-end', [\App\Http\Controllers\MasterAttendanceController::class, 'restEnd'])->name('master-demo.attendance.rest-end');


    Route::post('/master-demo/overtime-request', [\App\Http\Controllers\MasterAttendanceController::class, 'submitOvertime'])->name('master-demo.overtime-request.store');
    Route::post('/master-demo/leave-request', [\App\Http\Controllers\MasterAttendanceController::class, 'submitLeave'])->name('master-demo.leave-request.store');

    // Payroll Routes (Master Demo)
    Route::get('/master-demo/payroll', [\App\Http\Controllers\Api\PayrollController::class, 'index'])->name('master-demo.payroll');
    Route::post('/master-demo/payroll/generate', [\App\Http\Controllers\Api\PayrollController::class, 'generate'])->name('master-demo.payroll.generate');
    Route::post('/master-demo/payroll/{payroll}/verify', [\App\Http\Controllers\Api\PayrollController::class, 'verify'])->name('master-demo.payroll.verify');
    Route::post('/master-demo/payroll/{payroll}/approve', [\App\Http\Controllers\Api\PayrollController::class, 'approve'])->name('master-demo.payroll.approve');
    Route::post('/master-demo/payroll/{payroll}/pay', [\App\Http\Controllers\Api\PayrollController::class, 'pay'])->name('master-demo.payroll.pay');
    Route::delete('/master-demo/payroll/{payroll}', [\App\Http\Controllers\Api\PayrollController::class, 'destroy'])->name('master-demo.payroll.destroy');
    Route::put('/master-demo/payroll/{payroll}', [\App\Http\Controllers\Api\PayrollController::class, 'update'])->name('master-demo.payroll.update');
});

Route::get('/certificate/{token}', [DocumentController::class, 'certificate'])
    ->middleware('throttle:60,1')
    ->name('certificates.show');
Route::get('/verify/certificate/{token}', [DocumentController::class, 'verify'])
    ->middleware('throttle:60,1')
    ->name('certificates.verify');
Route::get('/certificate/{token}/background', [DocumentController::class, 'certificateBackground'])
    ->middleware('throttle:120,1')
    ->name('certificates.background');
Route::get('/certificate/{token}/signature', [DocumentController::class, 'certificateSignature'])
    ->middleware('throttle:120,1')
    ->name('certificates.signature');

Route::get('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'verify'])
    ->middleware('throttle:30,1')
    ->name('webhooks.whatsapp.verify');
Route::post('/webhooks/whatsapp', [WhatsAppWebhookController::class, 'receive'])
    ->middleware('throttle:300,1')
    ->name('webhooks.whatsapp.receive');

Route::get('/', function () {
    return redirect()->route('master-demo.login');
});

Route::prefix('api')->middleware('erp.gate')->group(function (): void {
    Route::post('/login/send-otp', [AuthController::class, 'sendOtp'])
        ->middleware('throttle:3,1');
    Route::post('/login/verify-otp', [AuthController::class, 'verifyOtp'])
        ->middleware('throttle:10,1');

    Route::middleware(['auth', 'tenant.context', 'employee.or.alumni'])->group(function (): void {
        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/company/module-control', [CompanyModuleController::class, 'show']);
        Route::put('/company/features/{feature}', [CompanyModuleController::class, 'update'])
            ->middleware('throttle:30,1');

        Route::middleware('feature:inventory')->group(function (): void {
            Route::get('/inventory', [InventoryController::class, 'index']);
            Route::post('/inventory/products', [InventoryController::class, 'storeProduct']);
            Route::put('/inventory/products/{id}', [InventoryController::class, 'updateProduct']);
            Route::post('/inventory/movements', [InventoryController::class, 'move']);
            Route::post('/inventory/transfer', [InventoryController::class, 'transfer']);
            Route::get('/inventory/products/{id}/stock-card', [InventoryController::class, 'stockCard']);
            Route::post('/inventory/categories', [InventoryController::class, 'storeCategory']);
            Route::post('/inventory/brands', [InventoryController::class, 'storeBrand']);
        });
        Route::middleware('feature:procurement')->group(function (): void {
            Route::get('/purchasing/requests', [PurchaseRequestController::class, 'index']);
            Route::post('/purchasing/requests', [PurchaseRequestController::class, 'store']);
            Route::post('/purchasing/requests/{id}/submit', [PurchaseRequestController::class, 'submit']);
            Route::post('/purchasing/requests/{id}/decision', [PurchaseRequestController::class, 'decide']);
            Route::get('/purchasing/suppliers', [\App\Http\Controllers\Api\SupplierController::class, 'index']);
            Route::post('/purchasing/suppliers', [\App\Http\Controllers\Api\SupplierController::class, 'store']);
            Route::put('/purchasing/suppliers/{id}', [\App\Http\Controllers\Api\SupplierController::class, 'update']);
            Route::delete('/purchasing/suppliers/{id}', [\App\Http\Controllers\Api\SupplierController::class, 'destroy']);
            Route::get('/purchasing/orders', [PurchaseOrderController::class, 'index']);
            Route::post('/purchasing/orders', [PurchaseOrderController::class, 'store']);
            Route::put('/purchasing/orders/{id}', [PurchaseOrderController::class, 'update']);
            Route::post('/purchasing/orders/{id}/submit', [PurchaseOrderController::class, 'submit']);
            Route::post('/purchasing/orders/{id}/decision', [PurchaseOrderController::class, 'decide']);
            Route::post('/purchasing/goods-receipts', [GoodsReceiptController::class, 'store']);
        });
        Route::middleware('feature:production')->group(function (): void {
            Route::get('/production/orders', [ProductionOrderController::class, 'index']);
            Route::post('/production/orders', [ProductionOrderController::class, 'store']);
            Route::post('/production/orders/{id}/release', [ProductionOrderController::class, 'release']);
            Route::post('/production/orders/{id}/complete', [ProductionOrderController::class, 'complete']);
            Route::post('/production/orders/{id}/materials/modify', [ProductionOrderController::class, 'modifyMaterials']);
            Route::post('/production/orders/{id}/materials/decision', [ProductionOrderController::class, 'approveMaterials']);
        });
        Route::middleware('feature:pos')->group(function (): void {
            Route::get('/pos/sessions', [PosController::class, 'sessions']);
            Route::post('/pos/sessions', [PosController::class, 'open']);
            Route::post('/pos/sessions/{id}/sales', [PosController::class, 'sale']);
            Route::post('/pos/sessions/{id}/close', [PosController::class, 'close']);
        });

        Route::get('/admin/control-center', [SystemControlController::class, 'show']);
        Route::put('/admin/retention', [SystemControlController::class, 'updateRetention'])
            ->middleware('throttle:10,1');
        Route::post('/admin/retention/run', [SystemControlController::class, 'runRetention'])
            ->middleware('throttle:3,1');
        Route::put('/admin/features/{feature}', [SystemControlController::class, 'updateFeature'])
            ->middleware('throttle:30,1');
        Route::put('/admin/security', [SystemControlController::class, 'updateSecurity'])
            ->middleware('throttle:10,1');
        Route::put('/admin/security/gate-password', [SystemControlController::class, 'updateGatePassword'])
            ->middleware('throttle:5,1');
        Route::put('/admin/security/mail', [SystemControlController::class, 'updateMail'])
            ->middleware('throttle:5,1');
        Route::get('/admin/audit-events', [SystemControlController::class, 'auditEvents']);

        Route::middleware('feature:approvals')->group(function (): void {
            Route::get('/approvals', [ApprovalController::class, 'index']);
            Route::post('/approvals/{approvalRequest}/approve', [ApprovalController::class, 'approve']);
            Route::post('/approvals/{approvalRequest}/reject', [ApprovalController::class, 'reject']);
            Route::get('/data-deletions', [DataDeletionController::class, 'index']);
            Route::post('/data-deletions', [DataDeletionController::class, 'store'])
                ->middleware('throttle:20,1');
        });

        Route::middleware('feature:performance')->group(function (): void {
            Route::get('/goals', [GoalController::class, 'index']);
            Route::post('/goals', [GoalController::class, 'store']);
            Route::put('/goals/{goal}', [GoalController::class, 'update']);
            Route::get('/kpis', [KpiController::class, 'index']);
            Route::post('/kpis/plan', [KpiController::class, 'storePlan']);
            Route::put('/kpis/plans/{kpiPlan}', [KpiController::class, 'updatePlan']);
            Route::patch('/kpis/{kpi}/score', [KpiController::class, 'updateScore']);
            Route::get('/rules', [RuleController::class, 'index']);
            Route::post('/rules', [RuleController::class, 'store']);
            Route::put('/rules/{id}', [RuleController::class, 'update']);
            Route::delete('/rules/{id}', [RuleController::class, 'destroy']);
            Route::get('/tasks', [TaskController::class, 'index']);
            Route::post('/tasks', [TaskController::class, 'store']);
            Route::put('/tasks/{id}', [TaskController::class, 'update']);
            Route::put('/tasks/{id}/status', [TaskController::class, 'updateStatus']);
            Route::delete('/tasks/{id}', [TaskController::class, 'destroy']);
        });

        Route::middleware('feature:leave')->group(function (): void {
            Route::get('/leave-requests', [LeaveRequestController::class, 'index']);
            Route::post('/leave-requests', [LeaveRequestController::class, 'store']);
            Route::put('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'update']);
        });

        Route::middleware('feature:resignation')->group(function (): void {
            Route::get('/resignation-requests', [ResignationRequestController::class, 'index']);
            Route::post('/resignation-requests', [ResignationRequestController::class, 'store']);
            Route::put('/resignation-requests/{resignationRequest}', [ResignationRequestController::class, 'update']);
        });

        Route::middleware('feature:chat')->group(function (): void {
            Route::get('/chat-messages', [ChatMessageController::class, 'index']);
            Route::post('/chat-messages', [ChatMessageController::class, 'store']);
            Route::get('/chat-messages/{chatMessage}/attachment', [ChatMessageController::class, 'download'])
                ->name('chat.attachment.download');
        });

        Route::middleware('feature:backup')->group(function (): void {
            Route::get('/backup', [DataBackupController::class, 'show']);
        });

        Route::middleware('feature:gemini')->group(function (): void {
            Route::get('/ai/status', [GeminiController::class, 'status']);
            Route::post('/ai/settings', [GeminiController::class, 'updateSettings'])
                ->middleware('throttle:5,1');
            Route::delete('/ai/settings', [GeminiController::class, 'destroySettings']);
            Route::post('/ai/chat', [GeminiController::class, 'chat'])
                ->middleware('throttle:20,1');
        });

        Route::get('/metrics/dashboard', [DashboardMetricController::class, 'index']);
        Route::get('/record-attachments/{recordAttachment}', [RecordAttachmentController::class, 'download'])
            ->name('record-attachments.download');

        Route::middleware('feature:notifications')->group(function (): void {
            Route::get('/notifications', [NotificationController::class, 'index']);
            Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
            Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markRead']);
        });

        Route::get('/alumni/profile', [AlumniController::class, 'profile']);
        Route::put('/alumni/profile', [AlumniController::class, 'updateProfile']);
        Route::get('/alumni', [AlumniController::class, 'index']);
        Route::post('/alumni/announcements', [AlumniController::class, 'announce'])
            ->middleware('throttle:10,1');
        Route::post('/alumni/invitations', [AlumniController::class, 'invite'])
            ->middleware('throttle:3,1');

        Route::middleware('feature:attendance')->group(function (): void {
            Route::get('/attendance', [AttendanceController::class, 'index']);
            Route::get('/attendance/reverse-geocode', [AttendanceController::class, 'reverseGeocode']);
            Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn']);
            Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut']);
        });

        Route::middleware('feature:organization')->group(function (): void {
            Route::get('/organization-chart', [OrganizationChartController::class, 'index']);


            Route::get('/team-requests', [TeamRequestController::class, 'index']);
            Route::post('/team-requests', [TeamRequestController::class, 'store']);
            Route::post('/team-requests/{id}/approve', [TeamRequestController::class, 'approve']);
            Route::post('/team-requests/{id}/reject', [TeamRequestController::class, 'reject']);

            Route::get('/users', [UserController::class, 'index']);
            Route::post('/users/identity-preview', [UserController::class, 'previewIdentity'])
                ->middleware('throttle:30,1');
            Route::post('/users', [UserController::class, 'store']);
            Route::put('/users/{username}', [UserController::class, 'update']);
            Route::delete('/users/{username}', [UserController::class, 'destroy']);
            Route::get('/employee-separations/{employeeSeparation}/backup', [UserController::class, 'downloadSeparationBackup'])
                ->name('employee-separations.backup');
        });

        Route::middleware('feature:crm')->group(function (): void {
            Route::get('/crm/overview', [LeadController::class, 'overview']);
            Route::get('/crm/whatsapp/status', [WhatsAppCloudController::class, 'status']);
            Route::post('/crm/whatsapp/intake', [LeadController::class, 'whatsappIntake'])
                ->middleware('throttle:60,1');
            Route::post('/leads/{id}/whatsapp/send', [WhatsAppCloudController::class, 'send'])
                ->middleware('throttle:30,1');
            
            // Core Lead Management
            Route::get('/crm/leads', [LeadController::class, 'index']);
            Route::post('/crm/leads', [LeadController::class, 'store']);
            
            // Specific Lead Actions
            Route::prefix('/crm/leads/{id}')->group(function (): void {
                Route::get('/', [LeadController::class, 'show']);
                Route::put('/', [LeadController::class, 'update']);
                Route::delete('/', [LeadController::class, 'destroy']);
                Route::patch('/status', [LeadController::class, 'updateStatus']);
                Route::post('/activities', [LeadController::class, 'storeActivity']);
                
                // Quotations
                Route::get('/quotations', [\App\Http\Controllers\Api\SalesQuotationController::class, 'index']);
                Route::post('/quotations', [\App\Http\Controllers\Api\SalesQuotationController::class, 'store']);
            });
            
            // Quotation Actions
            Route::prefix('/crm/quotations/{id}')->group(function (): void {
                Route::post('/send-whatsapp', [\App\Http\Controllers\Api\SalesQuotationController::class, 'sendWhatsApp']);
                Route::post('/accept', [\App\Http\Controllers\Api\SalesQuotationController::class, 'accept']);
                Route::post('/reject', [\App\Http\Controllers\Api\SalesQuotationController::class, 'reject']);
            });

            // Webhooks/Intake
            Route::post('/crm/whatsapp-intake', [LeadController::class, 'whatsappIntake']);
        });

        Route::middleware('feature:finance')->group(function (): void {
            Route::get('/client-inflows', [ClientInflowController::class, 'index']);
            Route::post('/client-inflows', [ClientInflowController::class, 'store']);
            Route::put('/client-inflows/{id}', [ClientInflowController::class, 'update']);
            Route::delete('/client-inflows/{id}', [ClientInflowController::class, 'destroy']);
            Route::get('/client-inflows/export-csv', [ClientInflowController::class, 'exportCsv']);
            Route::post('/client-inflows/import-csv', [ClientInflowController::class, 'importCsv']);
            Route::post('/client-inflows/upload-invoice', [ClientInflowController::class, 'uploadInvoice']);
        });

        Route::middleware('feature:talent_management')->group(function (): void {
            Route::get('/talent/reviews', [TalentController::class, 'index']);
            Route::post('/talent/reviews', [TalentController::class, 'store']);
        });

        Route::middleware('feature:document_management')->group(function (): void {
            Route::get('/documents', [DocumentController::class, 'index']);
            Route::post('/documents/internship-certificates', [DocumentController::class, 'storeInternshipCertificate']);
            Route::post('/documents/templates', [DocumentController::class, 'storeTemplate'])
                ->middleware('throttle:10,1');
            Route::post('/documents/signature-profile', [DocumentController::class, 'storeSignatureProfile'])
                ->middleware('throttle:10,1');
            Route::post('/documents/{document}/sign', [DocumentController::class, 'sign']);
            Route::post('/documents/{document}/revoke', [DocumentController::class, 'revoke']);
        });

        Route::middleware('feature:payroll')->group(function (): void {
            Route::get('/payroll', [PayrollController::class, 'index']);
            Route::post('/payroll/generate', [PayrollController::class, 'generate']);
            Route::post('/payroll/{payroll}/approve', [PayrollController::class, 'approve']);
            Route::post('/payroll/{payroll}/pay', [PayrollController::class, 'pay']);
            Route::delete('/payroll/{payroll}', [PayrollController::class, 'destroy']);
            Route::put('/payroll/{payroll}', [PayrollController::class, 'update']);
        });

        Route::middleware('feature:accounting')->group(function (): void {
            Route::get('/accounting', [AccountingController::class, 'index']);
            Route::post('/accounting/transactions', [AccountingController::class, 'storeTransaction']);
            Route::post('/accounting/journal-entries', [AccountingController::class, 'storeJournal']);
            Route::post('/accounting/import-transactions', [AccountingController::class, 'importTransactions'])
                ->middleware('throttle:10,1');
            Route::get('/accounting/import-template', [AccountingController::class, 'downloadImportTemplate']);
        });

        Route::middleware('feature:project_costing')->group(function (): void {
            Route::get('/projects', [ProjectCostingController::class, 'index']);
            Route::post('/projects', [ProjectCostingController::class, 'store']);
            Route::put('/projects/{project}', [ProjectCostingController::class, 'update']);
            Route::post('/projects/{project}/costs', [ProjectCostingController::class, 'storeCost']);
        });

        Route::middleware('feature:advanced_analytics')->group(function (): void {
            Route::get('/analytics/overview', [AdvancedAnalyticsController::class, 'index']);
        });

        // Enterprise HRIS Endpoints
        Route::prefix('hris-enterprise')->group(function (): void {
            Route::get('/org-tree', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'getOrgTree']);
            Route::post('/departments', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'storeDepartment']);
            Route::post('/divisions', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'storeDivision']);
            Route::post('/positions', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'storePosition']);
            Route::post('/job-grades', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'storeJobGrade']);

            Route::get('/employees', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'getEmployees']);
            Route::post('/employees', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'storeEmployee']);

            Route::post('/attendance/clock-in', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'clockIn']);
            Route::post('/attendance/clock-out', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'clockOut']);
            Route::post('/attendance/correction', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'submitCorrection']);
            Route::post('/attendance/correction/{correction}/approve', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'approveCorrection']);

            Route::post('/leave', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'submitLeave']);
            Route::post('/leave/{leaveRequest}/approve', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'approveLeave']);

            Route::post('/payroll/generate', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'generatePayroll']);
            Route::post('/payroll/{payroll}/approve', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'approvePayroll']);

            Route::post('/okrs', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'storeOkr']);
            Route::post('/performance-reviews/{employee}', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'storePerformanceReview']);

            Route::post('/vacancies', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'storeJobVacancy']);
            Route::post('/vacancies/{vacancy}/apply', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'applyCandidate']);
            Route::post('/candidates/{candidate}/hire', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'hireCandidate']);

            Route::post('/exit/resignation', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'processResignation']);
            Route::post('/exit/clearance/{clearance}/approve', [\App\Http\Controllers\Api\HrisEnterpriseController::class, 'approveClearance']);
        });
    });
});

// ====================================
// CRM & Customer Portal Routes
// ====================================
Route::prefix('crm')->middleware('master.demo.auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [CrmController::class, 'dashboard'])->name('crm.dashboard');

    // Customer CRUD
    Route::get('/customers', [CrmController::class, 'index'])->name('crm.customers.index');
    Route::get('/customers/create', [CrmController::class, 'create'])->name('crm.customers.create');
    Route::post('/customers', [CrmController::class, 'store'])->name('crm.customers.store');

    // Merge Duplicates
    Route::get('/customers/merge', [CrmController::class, 'mergeDuplicatesForm'])->name('crm.customers.merge.form');
    Route::post('/customers/merge', [CrmController::class, 'mergeDuplicates'])->name('crm.customers.merge');

    // Tag Management
    Route::post('/tags', [CrmController::class, 'storeTag'])->name('crm.tags.store');

    Route::get('/customers/{id}', [CrmController::class, 'show'])->name('crm.customers.show');
    Route::get('/customers/{id}/edit', [CrmController::class, 'edit'])->name('crm.customers.edit');
    Route::put('/customers/{id}', [CrmController::class, 'update'])->name('crm.customers.update');
    Route::delete('/customers/{id}', [CrmController::class, 'destroy'])->name('crm.customers.destroy');

    // Blacklist Customer
    Route::post('/customers/{id}/blacklist', [CrmController::class, 'toggleBlacklist'])->name('crm.customers.blacklist');

    // Point Management
    Route::post('/customers/{id}/points/add', [CrmController::class, 'addPoint'])->name('crm.customers.points.add');
    Route::post('/customers/{id}/points/redeem', [CrmController::class, 'redeemPoint'])->name('crm.customers.points.redeem');

    // Export & Import
    Route::get('/customers/export/csv', [CrmController::class, 'exportCsv'])->name('crm.customers.export.csv');
    Route::get('/customers/export/excel', [CrmController::class, 'exportExcel'])->name('crm.customers.export.excel');
    Route::get('/customers/export/pdf', [CrmController::class, 'exportPdf'])->name('crm.customers.export.pdf');
    Route::post('/customers/import/csv', [CrmController::class, 'importCsv'])->name('crm.customers.import.csv');

    // Memberships, Loyalties, Vouchers
    Route::resource('memberships', \App\Http\Controllers\CrmMembershipController::class)->names('crm.memberships');
    Route::resource('loyalties', \App\Http\Controllers\CrmLoyaltyController::class)->names('crm.loyalties');
    Route::resource('vouchers', \App\Http\Controllers\CrmVoucherController::class)->names('crm.vouchers');

    // Reservations
    Route::resource('reservations', \App\Http\Controllers\CrmReservationController::class)->names('crm.reservations');
    
    // Feedbacks
    Route::resource('feedbacks', \App\Http\Controllers\CrmFeedbackController::class)->names('crm.feedbacks');

    // Marketing CRM
    Route::prefix('marketing')->group(function () {
        Route::get('/', [\App\Http\Controllers\CrmMarketingController::class, 'index'])->name('crm.marketing.index');
        Route::get('/campaigns', [\App\Http\Controllers\CrmMarketingController::class, 'campaigns'])->name('crm.marketing.campaigns');
        Route::post('/campaigns', [\App\Http\Controllers\CrmMarketingController::class, 'storeCampaign'])->name('crm.marketing.campaigns.store');
        Route::post('/campaigns/{id}/send', [\App\Http\Controllers\CrmMarketingController::class, 'sendCampaign'])->name('crm.marketing.campaigns.send');
        Route::get('/broadcast-logs', [\App\Http\Controllers\CrmMarketingController::class, 'broadcastLogs'])->name('crm.marketing.broadcast-logs');

        Route::get('/birthdays', [\App\Http\Controllers\CrmMarketingController::class, 'birthdays'])->name('crm.marketing.birthdays');
        Route::post('/birthdays/{id}/reward', [\App\Http\Controllers\CrmMarketingController::class, 'sendBirthdayReward'])->name('crm.marketing.birthdays.reward');

        Route::get('/promotions', [\App\Http\Controllers\CrmMarketingController::class, 'promotions'])->name('crm.marketing.promotions');
        Route::post('/promotions', [\App\Http\Controllers\CrmMarketingController::class, 'storePromotion'])->name('crm.marketing.promotions.store');
        Route::post('/promotions/check', [\App\Http\Controllers\CrmMarketingController::class, 'checkPromotion'])->name('crm.marketing.promotions.check');

        Route::get('/coupons', [\App\Http\Controllers\CrmMarketingController::class, 'coupons'])->name('crm.marketing.coupons');
        Route::post('/coupons/generate', [\App\Http\Controllers\CrmMarketingController::class, 'generateCoupon'])->name('crm.marketing.coupons.generate');
        Route::post('/coupons/validate', [\App\Http\Controllers\CrmMarketingController::class, 'validateCoupon'])->name('crm.marketing.coupons.validate');

        Route::get('/referrals', [\App\Http\Controllers\CrmMarketingController::class, 'referrals'])->name('crm.marketing.referrals');
    });

    // Analytics
    Route::get('/analytics', [\App\Http\Controllers\CrmAnalyticsController::class, 'index'])->name('crm.analytics.index');
});

// ====================================
// Customer Portal (Public / Lightweight Auth)
// ====================================
Route::get('/portal/login', [\App\Http\Controllers\CustomerPortalController::class, 'loginForm'])->name('portal.login');
Route::post('/portal/login', [\App\Http\Controllers\CustomerPortalController::class, 'loginAttempt'])->name('portal.login.attempt');
Route::post('/portal/logout', [\App\Http\Controllers\CustomerPortalController::class, 'logout'])->name('portal.logout');

Route::middleware('customer.portal')->prefix('portal')->group(function () {
    Route::get('/', [\App\Http\Controllers\CustomerPortalController::class, 'dashboard'])->name('portal.dashboard');
    Route::post('/reserve', [\App\Http\Controllers\CustomerPortalController::class, 'submitReservation'])->name('portal.reserve');
    Route::post('/feedback', [\App\Http\Controllers\CustomerPortalController::class, 'submitFeedback'])->name('portal.feedback');

    Route::get('/profile', [\App\Http\Controllers\CustomerPortalController::class, 'profile'])->name('portal.profile');
    Route::post('/profile', [\App\Http\Controllers\CustomerPortalController::class, 'updateProfile'])->name('portal.profile.update');

    Route::get('/vouchers', [\App\Http\Controllers\CustomerPortalController::class, 'vouchers'])->name('portal.vouchers');
    Route::post('/vouchers/{id}/redeem', [\App\Http\Controllers\CustomerPortalController::class, 'redeemVoucher'])->name('portal.vouchers.redeem');

    Route::get('/loyalty', [\App\Http\Controllers\CustomerPortalController::class, 'loyaltyHistory'])->name('portal.loyalty');
    Route::get('/invoices', [\App\Http\Controllers\CustomerPortalController::class, 'invoiceHistory'])->name('portal.invoices');
    Route::get('/card', [\App\Http\Controllers\CustomerPortalController::class, 'digitalCard'])->name('portal.card');
});

// ====================================
// Inventory & Warehouse Module Routes
// ====================================
Route::get('/migrate-now', function() {
    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
    return 'migrated';
});

Route::prefix('inventory')->middleware('master.demo.auth')->group(function () {
    // 1. Dashboard
    Route::get('/dashboard', [\App\Http\Controllers\Inventory\DashboardController::class, 'index'])->name('inventory.dashboard');

    // 2. Items
    Route::get('/items', [\App\Http\Controllers\Inventory\ItemController::class, 'index'])->name('inventory.items.index');
    Route::get('/items/create', [\App\Http\Controllers\Inventory\ItemController::class, 'create'])->name('inventory.items.create');
    Route::post('/items', [\App\Http\Controllers\Inventory\ItemController::class, 'store'])->name('inventory.items.store');
    Route::get('/items/export', [\App\Http\Controllers\Inventory\ItemController::class, 'export'])->name('inventory.items.export');
    Route::post('/items/import', [\App\Http\Controllers\Inventory\ItemController::class, 'import'])->name('inventory.items.import');
    Route::get('/items/{id}', [\App\Http\Controllers\Inventory\ItemController::class, 'show'])->name('inventory.items.show');
    Route::get('/items/{id}/edit', [\App\Http\Controllers\Inventory\ItemController::class, 'edit'])->name('inventory.items.edit');
    Route::put('/items/{id}', [\App\Http\Controllers\Inventory\ItemController::class, 'update'])->name('inventory.items.update');
    Route::delete('/items/{id}', [\App\Http\Controllers\Inventory\ItemController::class, 'destroy'])->name('inventory.items.destroy');
    Route::get('/items/{id}/print', [\App\Http\Controllers\Inventory\ItemController::class, 'print'])->name('inventory.items.print');

    // 3. Categories
    Route::get('/categories', [\App\Http\Controllers\Inventory\CategoryController::class, 'index'])->name('inventory.categories.index');
    Route::get('/categories/create', [\App\Http\Controllers\Inventory\CategoryController::class, 'create'])->name('inventory.categories.create');
    Route::post('/categories', [\App\Http\Controllers\Inventory\CategoryController::class, 'store'])->name('inventory.categories.store');
    Route::get('/categories/export', [\App\Http\Controllers\Inventory\CategoryController::class, 'export'])->name('inventory.categories.export');
    Route::get('/categories/{id}', [\App\Http\Controllers\Inventory\CategoryController::class, 'show'])->name('inventory.categories.show');
    Route::get('/categories/{id}/edit', [\App\Http\Controllers\Inventory\CategoryController::class, 'edit'])->name('inventory.categories.edit');
    Route::put('/categories/{id}', [\App\Http\Controllers\Inventory\CategoryController::class, 'update'])->name('inventory.categories.update');
    Route::delete('/categories/{id}', [\App\Http\Controllers\Inventory\CategoryController::class, 'destroy'])->name('inventory.categories.destroy');

    // 4. Brands
    Route::get('/brands', [\App\Http\Controllers\Inventory\BrandController::class, 'index'])->name('inventory.brands.index');
    Route::get('/brands/create', [\App\Http\Controllers\Inventory\BrandController::class, 'create'])->name('inventory.brands.create');
    Route::post('/brands', [\App\Http\Controllers\Inventory\BrandController::class, 'store'])->name('inventory.brands.store');
    Route::get('/brands/export', [\App\Http\Controllers\Inventory\BrandController::class, 'export'])->name('inventory.brands.export');
    Route::get('/brands/{id}', [\App\Http\Controllers\Inventory\BrandController::class, 'show'])->name('inventory.brands.show');
    Route::get('/brands/{id}/edit', [\App\Http\Controllers\Inventory\BrandController::class, 'edit'])->name('inventory.brands.edit');
    Route::put('/brands/{id}', [\App\Http\Controllers\Inventory\BrandController::class, 'update'])->name('inventory.brands.update');
    Route::delete('/brands/{id}', [\App\Http\Controllers\Inventory\BrandController::class, 'destroy'])->name('inventory.brands.destroy');

    // 5. UoM
    Route::get('/uoms', [\App\Http\Controllers\Inventory\UomController::class, 'index'])->name('inventory.uoms.index');
    Route::get('/uoms/create', [\App\Http\Controllers\Inventory\UomController::class, 'create'])->name('inventory.uoms.create');
    Route::post('/uoms', [\App\Http\Controllers\Inventory\UomController::class, 'store'])->name('inventory.uoms.store');
    Route::get('/uoms/export', [\App\Http\Controllers\Inventory\UomController::class, 'export'])->name('inventory.uoms.export');
    Route::get('/uoms/{id}', [\App\Http\Controllers\Inventory\UomController::class, 'show'])->name('inventory.uoms.show');
    Route::get('/uoms/{id}/edit', [\App\Http\Controllers\Inventory\UomController::class, 'edit'])->name('inventory.uoms.edit');
    Route::put('/uoms/{id}', [\App\Http\Controllers\Inventory\UomController::class, 'update'])->name('inventory.uoms.update');
    Route::delete('/uoms/{id}', [\App\Http\Controllers\Inventory\UomController::class, 'destroy'])->name('inventory.uoms.destroy');

    // 6. Warehouses
    Route::get('/warehouses', [\App\Http\Controllers\Inventory\WarehouseController::class, 'index'])->name('inventory.warehouses.index');
    Route::get('/warehouses/create', [\App\Http\Controllers\Inventory\WarehouseController::class, 'create'])->name('inventory.warehouses.create');
    Route::post('/warehouses', [\App\Http\Controllers\Inventory\WarehouseController::class, 'store'])->name('inventory.warehouses.store');
    Route::get('/warehouses/export', [\App\Http\Controllers\Inventory\WarehouseController::class, 'export'])->name('inventory.warehouses.export');
    Route::get('/warehouses/{id}', [\App\Http\Controllers\Inventory\WarehouseController::class, 'show'])->name('inventory.warehouses.show');
    Route::get('/warehouses/{id}/edit', [\App\Http\Controllers\Inventory\WarehouseController::class, 'edit'])->name('inventory.warehouses.edit');
    Route::put('/warehouses/{id}', [\App\Http\Controllers\Inventory\WarehouseController::class, 'update'])->name('inventory.warehouses.update');
    Route::delete('/warehouses/{id}', [\App\Http\Controllers\Inventory\WarehouseController::class, 'destroy'])->name('inventory.warehouses.destroy');

    // 7. Locations
    Route::get('/locations', [\App\Http\Controllers\Inventory\LocationController::class, 'index'])->name('inventory.locations.index');
    Route::get('/locations/create', [\App\Http\Controllers\Inventory\LocationController::class, 'create'])->name('inventory.locations.create');
    Route::post('/locations', [\App\Http\Controllers\Inventory\LocationController::class, 'store'])->name('inventory.locations.store');
    Route::delete('/locations/bins/{id}', [\App\Http\Controllers\Inventory\LocationController::class, 'destroyBin'])->name('inventory.locations.bin.destroy');

    // 8. Stock Summary
    Route::get('/stock-summary', [\App\Http\Controllers\Inventory\StockSummaryController::class, 'index'])->name('inventory.stock-summary.index');
    Route::get('/stock-summary/export', [\App\Http\Controllers\Inventory\StockSummaryController::class, 'export'])->name('inventory.stock-summary.export');

    // 9. Stock In
    Route::get('/stock-in', [\App\Http\Controllers\Inventory\StockInController::class, 'index'])->name('inventory.stock-in.index');
    Route::get('/stock-in/create', [\App\Http\Controllers\Inventory\StockInController::class, 'create'])->name('inventory.stock-in.create');
    Route::post('/stock-in', [\App\Http\Controllers\Inventory\StockInController::class, 'store'])->name('inventory.stock-in.store');
    Route::get('/stock-in/{id}', [\App\Http\Controllers\Inventory\StockInController::class, 'show'])->name('inventory.stock-in.show');
    Route::post('/stock-in/{id}/approve', [\App\Http\Controllers\Inventory\StockInController::class, 'approve'])->name('inventory.stock-in.approve');
    Route::post('/stock-in/{id}/reject', [\App\Http\Controllers\Inventory\StockInController::class, 'reject'])->name('inventory.stock-in.reject');
    Route::delete('/stock-in/{id}', [\App\Http\Controllers\Inventory\StockInController::class, 'destroy'])->name('inventory.stock-in.destroy');

    // 10. Stock Out
    Route::get('/stock-out', [\App\Http\Controllers\Inventory\StockOutController::class, 'index'])->name('inventory.stock-out.index');
    Route::get('/stock-out/create', [\App\Http\Controllers\Inventory\StockOutController::class, 'create'])->name('inventory.stock-out.create');
    Route::post('/stock-out', [\App\Http\Controllers\Inventory\StockOutController::class, 'store'])->name('inventory.stock-out.store');
    Route::get('/stock-out/{id}', [\App\Http\Controllers\Inventory\StockOutController::class, 'show'])->name('inventory.stock-out.show');
    Route::post('/stock-out/{id}/approve', [\App\Http\Controllers\Inventory\StockOutController::class, 'approve'])->name('inventory.stock-out.approve');
    Route::post('/stock-out/{id}/reject', [\App\Http\Controllers\Inventory\StockOutController::class, 'reject'])->name('inventory.stock-out.reject');
    Route::delete('/stock-out/{id}', [\App\Http\Controllers\Inventory\StockOutController::class, 'destroy'])->name('inventory.stock-out.destroy');

    // 11. Transfers
    Route::get('/transfers', [\App\Http\Controllers\Inventory\TransferController::class, 'index'])->name('inventory.transfers.index');
    Route::get('/transfers/create', [\App\Http\Controllers\Inventory\TransferController::class, 'create'])->name('inventory.transfers.create');
    Route::post('/transfers', [\App\Http\Controllers\Inventory\TransferController::class, 'store'])->name('inventory.transfers.store');
    Route::get('/transfers/{id}', [\App\Http\Controllers\Inventory\TransferController::class, 'show'])->name('inventory.transfers.show');
    Route::post('/transfers/{id}/approve', [\App\Http\Controllers\Inventory\TransferController::class, 'approve'])->name('inventory.transfers.approve');
    Route::post('/transfers/{id}/reject', [\App\Http\Controllers\Inventory\TransferController::class, 'reject'])->name('inventory.transfers.reject');
    Route::delete('/transfers/{id}', [\App\Http\Controllers\Inventory\TransferController::class, 'destroy'])->name('inventory.transfers.destroy');

    // 12. Adjustments
    Route::get('/adjustments', [\App\Http\Controllers\Inventory\AdjustmentController::class, 'index'])->name('inventory.adjustments.index');
    Route::get('/adjustments/create', [\App\Http\Controllers\Inventory\AdjustmentController::class, 'create'])->name('inventory.adjustments.create');
    Route::post('/adjustments', [\App\Http\Controllers\Inventory\AdjustmentController::class, 'store'])->name('inventory.adjustments.store');
    Route::get('/adjustments/{id}', [\App\Http\Controllers\Inventory\AdjustmentController::class, 'show'])->name('inventory.adjustments.show');
    Route::post('/adjustments/{id}/approve', [\App\Http\Controllers\Inventory\AdjustmentController::class, 'approve'])->name('inventory.adjustments.approve');
    Route::post('/adjustments/{id}/reject', [\App\Http\Controllers\Inventory\AdjustmentController::class, 'reject'])->name('inventory.adjustments.reject');
    Route::delete('/adjustments/{id}', [\App\Http\Controllers\Inventory\AdjustmentController::class, 'destroy'])->name('inventory.adjustments.destroy');

    // 13. Cycle Count
    Route::get('/cycle-counts', [\App\Http\Controllers\Inventory\CycleCountController::class, 'index'])->name('inventory.cycle-counts.index');
    Route::get('/cycle-counts/create', [\App\Http\Controllers\Inventory\CycleCountController::class, 'create'])->name('inventory.cycle-counts.create');
    Route::post('/cycle-counts', [\App\Http\Controllers\Inventory\CycleCountController::class, 'store'])->name('inventory.cycle-counts.store');
    Route::get('/cycle-counts/{id}', [\App\Http\Controllers\Inventory\CycleCountController::class, 'show'])->name('inventory.cycle-counts.show');
    Route::delete('/cycle-counts/{id}', [\App\Http\Controllers\Inventory\CycleCountController::class, 'destroy'])->name('inventory.cycle-counts.destroy');

    // 14. Reservations
    Route::get('/reservations', [\App\Http\Controllers\Inventory\ReservationController::class, 'index'])->name('inventory.reservations.index');
    Route::get('/reservations/create', [\App\Http\Controllers\Inventory\ReservationController::class, 'create'])->name('inventory.reservations.create');
    Route::post('/reservations', [\App\Http\Controllers\Inventory\ReservationController::class, 'store'])->name('inventory.reservations.store');
    Route::get('/reservations/{id}', [\App\Http\Controllers\Inventory\ReservationController::class, 'show'])->name('inventory.reservations.show');
    Route::delete('/reservations/{id}', [\App\Http\Controllers\Inventory\ReservationController::class, 'destroy'])->name('inventory.reservations.destroy');

    // 15. Picking
    Route::get('/pickings', [\App\Http\Controllers\Inventory\PickingController::class, 'index'])->name('inventory.pickings.index');
    Route::get('/pickings/create', [\App\Http\Controllers\Inventory\PickingController::class, 'create'])->name('inventory.pickings.create');
    Route::post('/pickings', [\App\Http\Controllers\Inventory\PickingController::class, 'store'])->name('inventory.pickings.store');
    Route::get('/pickings/{id}', [\App\Http\Controllers\Inventory\PickingController::class, 'show'])->name('inventory.pickings.show');
    Route::delete('/pickings/{id}', [\App\Http\Controllers\Inventory\PickingController::class, 'destroy'])->name('inventory.pickings.destroy');

    // 16. Packing
    Route::get('/packings', [\App\Http\Controllers\Inventory\PackingController::class, 'index'])->name('inventory.packings.index');
    Route::get('/packings/create', [\App\Http\Controllers\Inventory\PackingController::class, 'create'])->name('inventory.packings.create');
    Route::post('/packings', [\App\Http\Controllers\Inventory\PackingController::class, 'store'])->name('inventory.packings.store');
    Route::get('/packings/{id}', [\App\Http\Controllers\Inventory\PackingController::class, 'show'])->name('inventory.packings.show');
    Route::delete('/packings/{id}', [\App\Http\Controllers\Inventory\PackingController::class, 'destroy'])->name('inventory.packings.destroy');

    // 17. Delivery
    Route::get('/deliveries', [\App\Http\Controllers\Inventory\DeliveryController::class, 'index'])->name('inventory.deliveries.index');
    Route::get('/deliveries/create', [\App\Http\Controllers\Inventory\DeliveryController::class, 'create'])->name('inventory.deliveries.create');
    Route::post('/deliveries', [\App\Http\Controllers\Inventory\DeliveryController::class, 'store'])->name('inventory.deliveries.store');
    Route::get('/deliveries/{id}', [\App\Http\Controllers\Inventory\DeliveryController::class, 'show'])->name('inventory.deliveries.show');
    Route::delete('/deliveries/{id}', [\App\Http\Controllers\Inventory\DeliveryController::class, 'destroy'])->name('inventory.deliveries.destroy');

    // 18. Stock Ledger
    Route::get('/stock-ledger', [\App\Http\Controllers\Inventory\StockLedgerController::class, 'index'])->name('inventory.stock-ledger.index');

    // 19. Serial Numbers
    Route::get('/serial-numbers', [\App\Http\Controllers\Inventory\SerialNumberController::class, 'index'])->name('inventory.serial-numbers.index');
    Route::post('/serial-numbers', [\App\Http\Controllers\Inventory\SerialNumberController::class, 'store'])->name('inventory.serial-numbers.store');
    Route::delete('/serial-numbers/{id}', [\App\Http\Controllers\Inventory\SerialNumberController::class, 'destroy'])->name('inventory.serial-numbers.destroy');

    // 20. Batch Numbers
    Route::get('/batch-numbers', [\App\Http\Controllers\Inventory\BatchNumberController::class, 'index'])->name('inventory.batch-numbers.index');
    Route::post('/batch-numbers', [\App\Http\Controllers\Inventory\BatchNumberController::class, 'store'])->name('inventory.batch-numbers.store');
    Route::delete('/batch-numbers/{id}', [\App\Http\Controllers\Inventory\BatchNumberController::class, 'destroy'])->name('inventory.batch-numbers.destroy');

    // 21. Barcodes
    Route::get('/barcodes', [\App\Http\Controllers\Inventory\BarcodeController::class, 'index'])->name('inventory.barcodes.index');
    Route::post('/barcodes', [\App\Http\Controllers\Inventory\BarcodeController::class, 'store'])->name('inventory.barcodes.store');
    Route::delete('/barcodes/{id}', [\App\Http\Controllers\Inventory\BarcodeController::class, 'destroy'])->name('inventory.barcodes.destroy');

    // 22. Reports
    Route::get('/reports', [\App\Http\Controllers\Inventory\ReportController::class, 'index'])->name('inventory.reports.index');

    // 23. Analytics
    Route::get('/analytics', [\App\Http\Controllers\Inventory\AnalyticsController::class, 'index'])->name('inventory.analytics.index');

    // 24. Settings
    Route::get('/settings', [\App\Http\Controllers\Inventory\SettingController::class, 'index'])->name('inventory.settings.index');
    Route::post('/settings', [\App\Http\Controllers\Inventory\SettingController::class, 'update'])->name('inventory.settings.update');
});
Route::get('/test-login', function() { Auth::login(App\Models\User::find(2)); return redirect('/master-portal'); });
