<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\User;
use App\Services\PayrollCalculatorService;
use App\Services\PayrollDisbursementService;
use App\Services\TenantContext;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollController extends Controller
{
    public function __construct(
        private readonly TenantContext $tenant,
        private readonly PayrollCalculatorService $calculator,
        private readonly PayrollDisbursementService $disbursement
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $query = Payroll::with(['user:id,name,username,employee_code,job_title', 'approver:id,name', 'items']);

        $isDemo = $request->is('master-demo/*');
        if ($isDemo || $user->isCEO() || $user->isHRD()) {
            if ($this->tenant->id()) {
                $query->where('company_id', $this->tenant->id());
            }
        } else {
            $query->where('user_id', $user->id);
        }

        return response()->json([
            'payrolls' => $query->latest('period_start')->get(),
            'can_manage' => $isDemo || $user->isCEO() || $user->isHRD(),
        ]);
    }

    public function generate(Request $request)
    {
        $actor = $request->user();
        $isDemo = $request->is('master-demo/*');
        abort_unless($isDemo || $actor->isCEO() || $actor->isHRD(), 403);

        $request->validate([
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
        ]);

        $start = Carbon::parse($request->period_start)->startOfDay();
        $end = Carbon::parse($request->period_end)->endOfDay();
        $companyId = $this->tenant->id() ?? 1;

        $result = $this->calculator->generateMonthly($companyId, $start, $end, $actor->id);

        return response()->json([
            'message' => 'Payroll generated successfully for '.$result['count'].' employees.',
        ]);
    }

    public function verify(Request $request, Payroll $payroll)
    {
        $actor = $request->user();
        $isDemo = $request->is('master-demo/*');
        abort_unless($isDemo || $actor->isHRD() || $actor->isCEO(), 403, 'Only HR or CEO can verify payrolls.');
        abort_unless($payroll->status === 'draft', 422, 'Only draft payrolls can be verified.');

        $payroll->update([
            'status' => 'verified',
        ]);

        return response()->json(['message' => 'Payroll verified successfully.']);
    }

    public function approve(Request $request, Payroll $payroll)
    {
        $actor = $request->user();
        $isDemo = $request->is('master-demo/*');
        abort_unless($isDemo || $actor->isCEO(), 403, 'Only CEO can approve payrolls.');
        abort_unless(in_array($payroll->status, ['draft', 'verified']), 422, 'Only draft or verified payrolls can be approved.');

        $payroll->update([
            'status' => 'approved',
            'approved_by' => $actor->id,
        ]);

        return response()->json(['message' => 'Payroll approved successfully.']);
    }

    public function pay(Request $request, Payroll $payroll)
    {
        $actor = $request->user();
        $isDemo = $request->is('master-demo/*');
        abort_unless($isDemo || $actor->isCEO() || $actor->isHRD(), 403);

        $this->disbursement->disburse($payroll, $actor);

        return response()->json(['message' => 'Payroll disbursed and recorded to finance successfully.']);
    }

    public function destroy(Request $request, Payroll $payroll)
    {
        $actor = $request->user();
        $isDemo = $request->is('master-demo/*');
        abort_unless($isDemo || $actor->isCEO() || $actor->isHRD(), 403);
        abort_unless($payroll->status === 'draft', 422, 'Only draft payrolls can be deleted.');

        $payroll->items()->delete();
        $payroll->delete();

        return response()->json(['message' => 'Payroll deleted successfully.']);
    }

    public function update(Request $request, Payroll $payroll)
    {
        $actor = $request->user();
        $isDemo = $request->is('master-demo/*');
        abort_unless($isDemo || $actor->isCEO() || $actor->isHRD(), 403);
        abort_unless($payroll->status === 'draft', 422, 'Only draft payrolls can be edited.');

        $request->validate([
            'base_amount' => 'required|numeric|min:0',
            'items' => 'nullable|array',
            'items.*.id' => 'nullable|integer',
            'items.*.type' => 'required|in:allowance,deduction',
            'items.*.description' => 'required|string',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $payroll) {
            $payroll->base_amount = $request->base_amount;
            $payroll->save();

            // Rebuild items if provided
            if ($request->has('items')) {
                $payroll->items()->delete();
                $totalAllowances = 0;
                $totalDeductions = 0;

                foreach ($request->items as $itemData) {
                    $payroll->items()->create([
                        'type' => $itemData['type'],
                        'description' => $itemData['description'],
                        'amount' => $itemData['amount'],
                    ]);

                    if ($itemData['type'] === 'allowance') {
                        $totalAllowances += $itemData['amount'];
                    } else {
                        $totalDeductions += $itemData['amount'];
                    }
                }

                $payroll->total_allowances = $totalAllowances;
                $payroll->total_deductions = $totalDeductions;
                $payroll->net_amount = $payroll->base_amount + $totalAllowances - $totalDeductions;
                $payroll->save();
            } else {
                // If items not provided, just recalculate based on existing items
                $totalAllowances = $payroll->items()->where('type', 'allowance')->sum('amount');
                $totalDeductions = $payroll->items()->where('type', 'deduction')->sum('amount');
                $payroll->total_allowances = $totalAllowances;
                $payroll->total_deductions = $totalDeductions;
                $payroll->net_amount = $payroll->base_amount + $totalAllowances - $totalDeductions;
                $payroll->save();
            }
        });

        return response()->json(['message' => 'Payroll updated successfully.']);
    }

    /**
     * GET /payroll/slip/{employee}/preview
     * Returns JSON data for the slip gaji preview modal.
     */
    public function slipPreview(Request $request, $employee)
    {
        $user = User::findOrFail($employee);
        $payroll = Payroll::with('items')->where('user_id', $user->id)->latest('period_end')->first();

        $allowances = 0;
        $deductions = 0;
        $items = [];

        if ($payroll) {
            foreach ($payroll->items as $item) {
                $items[] = [
                    'description' => $item->description,
                    'type' => $item->type,
                    'amount' => $item->amount,
                ];
                if ($item->type === 'allowance') {
                    $allowances += $item->amount;
                }
                if ($item->type === 'deduction') {
                    $deductions += $item->amount;
                }
            }
        }

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'employee_code' => $user->employee_code,
                'job_title' => $user->job_title,
                'division' => $user->divisionLabel(),
                'employment_type' => $user->employment_type,
            ],
            'payroll' => [
                'period_start' => $payroll ? Carbon::parse($payroll->period_start)->format('Y-m-d') : Carbon::now()->startOfMonth()->format('Y-m-d'),
                'period_end' => $payroll ? Carbon::parse($payroll->period_end)->format('Y-m-d') : Carbon::now()->endOfMonth()->format('Y-m-d'),
                'base_amount' => $payroll ? $payroll->base_amount : ($user->base_salary ?? 5000000),
                'net_amount' => $payroll ? $payroll->net_amount : ($user->base_salary ?? 5000000),
                'allowances' => $allowances,
                'deductions' => $deductions,
                'items' => $items,
            ],
        ]);
    }

    /**
     * POST /payroll/slip/{employee}/generate
     * Accepts edited form data and returns a downloadable PDF.
     */
    public function slipGenerate(Request $request, $employee)
    {
        $user = User::findOrFail($employee);

        // Build a plain object from the request so the Blade view is decoupled
        $payrollData = new \stdClass;
        $payrollData->period_start = $request->input('period_start', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $payrollData->period_end = $request->input('period_end', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $payrollData->base_amount = (float) $request->input('base_amount', $user->base_salary ?? 5000000);
        $payrollData->net_amount = (float) $request->input('net_amount', $user->base_salary ?? 5000000);
        $payrollData->items = collect($request->input('items', []));

        // Allow overriding user display fields from the form
        $displayUser = clone $user;
        $displayUser->name = $request->input('name', $user->name);
        $displayUser->job_title = $request->input('job_title', $user->job_title);
        $displayUser->division = $request->input('division', $user->divisionLabel());

        $data = [
            'user' => $displayUser,
            'payroll' => $payrollData,
            'signature' => $request->input('signature', 'HR Department'),
        ];

        $pdf = Pdf::loadView('pdf.salary-slip', $data);

        return $pdf->download('Slip_Gaji_'.$user->employee_code.'.pdf');
    }
}
