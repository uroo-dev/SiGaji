<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use App\Services\PayslipExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class PayslipController extends Controller
{
    public function __construct(
        protected PayslipExportService $exportService
    ) {}

    public function index()
    {
        $payslips = Payslip::with('items')
            ->where('user_id', Auth::id())
            ->latest('period')
            ->get();

        return view('employee.payslips.index', compact('payslips'));
    }

    public function downloadPdf(Payslip $payslip, Request $request)
    {
        // Otorisasi melalui policy
        Gate::authorize('view', $payslip);

        abort_if($payslip->status->value !== 'final', 403, 'Slip gaji belum difinalisasi.');

        return $this->exportService->toPdf($payslip, $request);
    }

    public function downloadExcel(Payslip $payslip)
    {
        // Otorisasi melalui policy
        Gate::authorize('view', $payslip);

        abort_if($payslip->status->value !== 'final', 403, 'Slip gaji belum difinalisasi.');

        return $this->exportService->toExcel($payslip);
    }
}
