<?php

namespace App\Services;

use App\Models\Payslip;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PayslipExportService
{
    /**
     * Export a single payslip as Excel-compatible CSV with UTF-8 BOM.
     */
    public function toExcel(Payslip $payslip): StreamedResponse
    {
        $fileName = "slip_gaji_{$payslip->period}_{$payslip->user->name}.csv";
        $fileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileName);

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($payslip) {
            $file = fopen('php://output', 'w');

            // UTF-8 BOM for Microsoft Excel
            fputs($file, "\xEF\xBB\xBF");

            // Header Company & Document Info
            fputcsv($file, ['SLIP GAJI KARYAWAN - SIGAJI']);
            fputcsv($file, ['Sistem Penggajian & Presensi Karyawan']);
            fputcsv($file, ['Jl. Inovasi Solusi No. 42, Bandung']);
            fputcsv($file, []);

            // Metadata
            fputcsv($file, ['INFORMASI DOKUMEN']);
            fputcsv($file, ['Nomor Slip', '#' . $payslip->id]);
            fputcsv($file, ['Periode', $payslip->period]);
            fputcsv($file, ['Tanggal Finalisasi', $payslip->finalized_at?->format('d M Y H:i') ?? '-']);
            fputcsv($file, ['Status', $payslip->status instanceof \BackedEnum ? $payslip->status->value : $payslip->status]);
            fputcsv($file, []);

            // Employee Info
            fputcsv($file, ['DATA KARYAWAN']);
            fputcsv($file, ['Nama Karyawan', $payslip->user->name]);
            fputcsv($file, ['Email', $payslip->user->email]);
            fputcsv($file, ['Jabatan / Role', $payslip->user->role?->label() ?? 'Karyawan']);
            fputcsv($file, []);

            // Salary Breakdown Table
            fputcsv($file, ['RINCIAN KOMPONEN GAJI']);
            fputcsv($file, ['Kategori', 'Komponen', 'Tipe', 'Nominal (Rp)']);

            // Base components
            fputcsv($file, ['Pendapatan', 'Gaji Pokok', 'Kredit (+)', (int) $payslip->base_salary]);
            if ($payslip->overtime_pay > 0) {
                fputcsv($file, ['Pendapatan', 'Upah Lembur', 'Kredit (+)', (int) $payslip->overtime_pay]);
            }
            if ($payslip->late_deduction > 0) {
                fputcsv($file, ['Potongan', 'Potongan Keterlambatan', 'Debet (-)', (int) $payslip->late_deduction]);
            }
            if ($payslip->leave_deduction > 0) {
                fputcsv($file, ['Potongan', 'Potongan Cuti / Izin', 'Debet (-)', (int) $payslip->leave_deduction]);
            }

            // Detailed items
            foreach ($payslip->items as $item) {
                // If it's already covered by base items, avoid duplication or list item
                $typeLabel = $item->type === 'earning' ? 'Kredit (+)' : 'Debet (-)';
                $category = $item->type === 'earning' ? 'Pendapatan' : 'Potongan';
                fputcsv($file, [$category, $item->component, $typeLabel, (int) $item->amount]);
            }

            fputcsv($file, []);
            fputcsv($file, ['TOTAL GAJI BERSIH (TAKE HOME PAY)', '', '', (int) $payslip->net_salary]);
            fputcsv($file, []);
            fputcsv($file, ['Catatan:', 'Dokumen ini diterbitkan secara elektronik oleh sistem SiGaji.']);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Render or stream PDF payslip.
     */
    public function toPdf(Payslip $payslip, Request $request)
    {
        if (class_exists(\Dompdf\Dompdf::class) && $request->has('stream')) {
            $html = view('pdf.payslip', compact('payslip'))->render();
            $dompdf = new \Dompdf\Dompdf(['isRemoteEnabled' => true]);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();

            $fileName = "slip_gaji_{$payslip->period}_{$payslip->user->name}.pdf";
            $fileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileName);

            return response($dompdf->output(), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            ]);
        }

        return view('pdf.payslip', compact('payslip'));
    }
}
