<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PayslipStatus;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessMonthlyPayroll;
use App\Models\Payslip;
use App\Services\AuditService;
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
        $request->validate(['period' => 'required|regex:/^\d{4}-\d{2}$/']);

        // Guard Clause
        $hasFinal = Payslip::where('period', $request->period)
            ->where('status', PayslipStatus::FINAL)
            ->exists();
        
        if ($hasFinal) {
            return response()->json(['error' => 'Periode ini sudah difinalisasi dan tidak bisa digenerate ulang.'], 422);
        }

        // Reset progress di Redis
        Cache::forget("payroll.progress.{$request->period}");
        
        // Dispatch Job ke Queue
        ProcessMonthlyPayroll::dispatch($request->period);
        
        return response()->json(['message' => 'Payroll sedang diproses di background.']);
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

        foreach ($payslips as $payslip) {
            $payslip->update([
                'status' => PayslipStatus::FINAL,
                'finalized_by' => auth()->id(),
                'finalized_at' => now(),
            ]);
            AuditService::log('payslip.finalized', $payslip);
        }

        return back()->with('success', "{$payslips->count()} slip gaji difinalisasi.");
    }
}
