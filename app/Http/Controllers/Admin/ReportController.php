<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payslip;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->input('period', now()->format('Y-m'));
        $recap = Payslip::with('user')
            ->where('period', $period)
            ->where('status', 'final')
            ->get();
        
        $totalNet = $recap->sum('net_salary');
        $totalOvertime = $recap->sum('overtime_pay');
        $totalDeduction = $recap->sum('late_deduction') + $recap->sum('leave_deduction');

        return view('admin.reports.index', compact('recap', 'period', 'totalNet', 'totalOvertime', 'totalDeduction'));
    }

    public function payroll(Request $request): StreamedResponse
    {
        $period = $request->input('period', now()->format('Y-m'));
        $payslips = Payslip::with('user')
            ->where('period', $period)
            ->where('status', 'final')
            ->get();

        $fileName = "laporan_payroll_{$period}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($payslips, $period) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM for Microsoft Excel compatibility
            fputs($file, "\xEF\xBB\xBF");

            // Header Row
            fputcsv($file, [
                'No',
                'Periode',
                'Nama Karyawan',
                'Email',
                'Gaji Pokok (Rp)',
                'Upah Lembur (Rp)',
                'Potongan Keterlambatan (Rp)',
                'Potongan Cuti (Rp)',
                'Gaji Bersih (Rp)',
                'Status',
            ]);

            foreach ($payslips as $index => $slip) {
                fputcsv($file, [
                    $index + 1,
                    $period,
                    $slip->user?->name ?? '-',
                    $slip->user?->email ?? '-',
                    $slip->base_salary,
                    $slip->overtime_pay,
                    $slip->late_deduction,
                    $slip->leave_deduction,
                    $slip->net_salary,
                    $slip->status instanceof \BackedEnum ? $slip->status->value : $slip->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
