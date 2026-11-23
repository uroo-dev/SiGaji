<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PayslipStatus;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessMonthlyPayroll;
use App\Models\Payslip;
use App\Services\AuditService;
use App\Services\PayslipExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PayrollController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->period ?? now()->format('Y-m');
        
        $payslips = Payslip::with('user')
            ->where('period', $period)
            ->orderBy('status')
            ->get();

        return view('admin.payroll.index', compact('payslips', 'period'));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'period' => 'required|regex:/^\d{4}-\d{2}$/',
            'recalculate' => 'nullable|boolean',
        ]);

        $recalculate = $request->boolean('recalculate');

        // Guard Clause: Cek apakah periode sudah difinalisasi
        $hasFinal = Payslip::where('period', $request->period)
            ->where('status', PayslipStatus::FINAL)
            ->exists();
        
        if ($hasFinal && ! $recalculate) {
            return response()->json([
                'error' => 'Periode ini sudah difinalisasi. Gunakan opsi Hitung Ulang atau Kembalikan ke Draft terlebih dahulu.',
                'can_recalculate' => true,
            ], 422);
        }

        // Reset progress di Redis
        Cache::forget("payroll.progress.{$request->period}");
        
        // Dispatch Job ke Queue
        ProcessMonthlyPayroll::dispatch($request->period, $recalculate);

        AuditService::log('payroll.generated', null, null, [
            'period' => $request->period,
            'recalculate' => $recalculate,
            'time' => now()->toDateTimeString(),
        ]);
        
        return response()->json([
            'message' => $recalculate ? 'Payroll sedang dihitung ulang di background.' : 'Payroll sedang diproses di background.'
        ]);
    }

    public function status(string $period)
    {
        // Ambil progress dari Redis
        $progress = Cache::get("payroll.progress.{$period}", [
            'total' => 0,
            'completed' => 0,
            'status' => 'idle',
        ]);
        
        return response()->json($progress);
    }

    public function finalize(Request $request)
    {
        $request->validate(['period' => 'required|regex:/^\d{4}-\d{2}$/']);

        $payslips = Payslip::where('period', $request->period)
            ->where('status', PayslipStatus::DRAFT)
            ->get();

        if ($payslips->isEmpty()) {
            return back()->with('error', "Tidak ada slip gaji berstatus Draft pada periode {$request->period}.");
        }

        foreach ($payslips as $payslip) {
            $payslip->update([
                'status' => PayslipStatus::FINAL,
                'finalized_by' => auth()->id(),
                'finalized_at' => now(),
            ]);
            AuditService::log('payslip.finalized', $payslip);
        }

        return back()->with('success', "{$payslips->count()} slip gaji periode {$request->period} berhasil difinalisasi.");
    }

    public function revert(Request $request)
    {
        $request->validate(['period' => 'required|regex:/^\d{4}-\d{2}$/']);

        $payslips = Payslip::where('period', $request->period)
            ->where('status', PayslipStatus::FINAL)
            ->get();

        if ($payslips->isEmpty()) {
            return back()->with('error', "Tidak ada slip gaji berstatus Final pada periode {$request->period}.");
        }

        foreach ($payslips as $payslip) {
            $payslip->update([
                'status' => PayslipStatus::DRAFT,
                'finalized_by' => null,
                'finalized_at' => null,
            ]);
            AuditService::log('payslip.reverted', $payslip, [
                'status' => 'final',
            ], [
                'status' => 'draft',
                'time' => now()->toDateTimeString(),
            ]);
        }

        return back()->with('success', "{$payslips->count()} slip gaji periode {$request->period} berhasil dikembalikan ke status Draft dan siap dihitung ulang.");
    }

    public function downloadPdf(Payslip $payslip, Request $request, PayslipExportService $exportService)
    {
        abort_if($payslip->status->value !== 'final', 403, 'Slip gaji belum difinalisasi.');

        return $exportService->toPdf($payslip, $request);
    }

    public function downloadExcel(Payslip $payslip, PayslipExportService $exportService)
    {
        abort_if($payslip->status->value !== 'final', 403, 'Slip gaji belum difinalisasi.');

        return $exportService->toExcel($payslip);
    }
}
