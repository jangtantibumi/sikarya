<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaklaringController extends Controller
{
    /**
     * GET /paklaring/{employee}/preview
     * Returns JSON data for the paklaring preview modal.
     */
    public function preview(Request $request, $employee)
    {
        $user = User::findOrFail($employee);

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'employee_code' => $user->employee_code,
                'job_title' => $user->job_title,
                'division' => $user->divisionLabel(),
            ],
            'letter' => [
                'company_name' => 'Northstar Group',
                'join_date' => $user->created_at->format('d F Y'),
                'resign_date' => $user->deactivated_at
                    ? Carbon::parse($user->deactivated_at)->format('d F Y')
                    : Carbon::now()->format('d F Y'),
                'today_date' => Carbon::now()->format('d F Y'),
                'hr_name' => 'HR Department',
                'content' => 'Selama bekerja di perusahaan kami, yang bersangkutan telah menunjukkan dedikasi, kedisiplinan, dan kinerja yang baik serta tidak pernah melakukan tindakan yang merugikan perusahaan. Kami mengucapkan terima kasih atas segala kontribusi yang telah diberikan dan berharap kesuksesan di masa mendatang.',
            ],
        ]);
    }

    /**
     * POST /paklaring/{employee}/generate
     * Accepts edited form data and returns a downloadable PDF.
     */
    public function generate(Request $request, $employee)
    {
        $user = User::findOrFail($employee);

        $data = [
            'user' => $user,
            'company_name' => $request->input('company_name', 'Northstar Group'),
            'join_date' => $request->input('join_date', $user->created_at->format('d F Y')),
            'resign_date' => $request->input('resign_date', Carbon::now()->format('d F Y')),
            'today_date' => Carbon::now()->format('d F Y'),
            'hr_name' => $request->input('hr_name', 'HR Department'),
            'content' => $request->input('content', 'Selama bekerja di perusahaan kami, yang bersangkutan telah menunjukkan dedikasi dan kinerja yang baik.'),
            // Override display fields
            'override_name' => $request->input('name', $user->name),
            'override_code' => $request->input('employee_code', $user->employee_code),
            'override_position' => $request->input('job_title', $user->job_title),
            'override_division' => $request->input('division', $user->divisionLabel()),
        ];

        $pdf = Pdf::loadView('pdf.paklaring', $data);

        return $pdf->download('Paklaring_'.$user->employee_code.'.pdf');
    }

    /**
     * Legacy direct download kept for backward compatibility.
     */
    public function download(Request $request, $employee)
    {
        return $this->generate($request, $employee);
    }
}
