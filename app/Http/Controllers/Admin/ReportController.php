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

            // Header Company & Document Info
            fputcsv($file, ['REKAPITULASI LAPORAN PAYROLL - SIGAJI']);
            fputcsv($file, ['Periode', $period]);
            fputcsv($file, ['Tanggal Ekspor', now()->format('d M Y H:i:s')]);
            fputcsv($file, []);

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
                    (int) $slip->base_salary,
                    (int) $slip->overtime_pay,
                    (int) $slip->late_deduction,
                    (int) $slip->leave_deduction,
                    (int) $slip->net_salary,
                    $slip->status instanceof \BackedEnum ? $slip->status->value : $slip->status,
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, [
                'TOTAL',
                '',
                '',
                '',
                (int) $payslips->sum('base_salary'),
                (int) $payslips->sum('overtime_pay'),
                (int) $payslips->sum('late_deduction'),
                (int) $payslips->sum('leave_deduction'),
                (int) $payslips->sum('net_salary'),
                '',
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function pdf(Request $request)
    {
        $period = $request->input('period', now()->format('Y-m'));
        $recap = Payslip::with('user')
            ->where('period', $period)
            ->where('status', 'final')
            ->get();
        
        $totalNet = $recap->sum('net_salary');
        $totalOvertime = $recap->sum('overtime_pay');
        $totalDeduction = $recap->sum('late_deduction') + $recap->sum('leave_deduction');

        $fileName = "laporan_payroll_{$period}.pdf";

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.report', compact('recap', 'period', 'totalNet', 'totalOvertime', 'totalDeduction'))
            ->setPaper('a4', 'landscape');

        if ($request->has('stream') || $request->has('preview')) {
            return $pdf->stream($fileName);
        }

        return $pdf->download($fileName);
    }
}
