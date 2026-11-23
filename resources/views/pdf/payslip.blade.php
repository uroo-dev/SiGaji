<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji {{ $payslip->period }} - {{ $payslip->user->name }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
            font-size: 11.5px;
            color: #1e293b;
            background-color: #f8fafc;
            line-height: 1.5;
            padding: 24px;
        }
        .container {
            max-width: 780px;
            margin: 0 auto;
            background: #ffffff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 4px 25px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
        }

        /* Top Action Bar (hidden on print) */
        .action-bar {
            max-width: 780px;
            margin: 0 auto 20px auto;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #0f172a;
            color: white;
            padding: 14px 24px;
            border-radius: 14px;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.2);
        }
        .action-bar h4 {
            font-size: 13.5px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .action-bar .actions {
            display: flex;
            gap: 10px;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 8px;
            text-decoration: none;
            cursor: pointer;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-print {
            background: #FF5A36;
            color: white;
        }
        .btn-print:hover {
            background: #e04522;
        }
        .btn-excel {
            background: #10b981;
            color: white;
        }
        .btn-excel:hover {
            background: #059669;
        }
        .btn-back {
            background: #334155;
            color: #e2e8f0;
        }
        .btn-back:hover {
            background: #475569;
        }

        /* Invoice Header */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #FF5A36;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .brand {
            font-size: 26px;
            font-weight: 800;
            color: #FF5A36;
            letter-spacing: -0.5px;
        }
        .brand-sub {
            font-size: 11px;
            color: #64748b;
            margin-top: 2px;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h2 {
            font-size: 18px;
            font-weight: 800;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .badge-period {
            display: inline-block;
            background: #fff1ee;
            color: #FF5A36;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 6px;
            margin-top: 6px;
            font-size: 12px;
            font-family: 'JetBrains Mono', monospace;
        }

        /* Employee Info Box */
        .info-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 16px 20px;
            margin-bottom: 28px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px 24px;
        }
        .info-item {
            display: flex;
            justify-content: space-between;
            font-size: 11.5px;
        }
        .info-label {
            color: #64748b;
            font-weight: 500;
        }
        .info-val {
            font-weight: 700;
            color: #0f172a;
        }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }
        th {
            background: #f1f5f9;
            color: #334155;
            text-transform: uppercase;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 10px 12px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
        }
        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
        }
        tr:nth-child(even) td {
            background: #fcfdfe;
        }
        .amount {
            text-align: right;
            font-family: 'JetBrains Mono', monospace;
            font-weight: 600;
        }
        .amount.earning {
            color: #16a34a;
        }
        .amount.deduction {
            color: #dc2626;
        }
        .total-row {
            background: #0f172a !important;
            color: white;
            font-weight: 700;
            font-size: 13px;
        }
        .total-row td {
            color: white;
            padding: 14px 12px;
            border: none;
        }
        .total-row .amount {
            color: #ffffff;
            font-size: 15px;
        }

        /* Signatures */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .sign-box {
            width: 180px;
            text-align: center;
        }
        .sign-title {
            font-size: 10px;
            color: #64748b;
            margin-bottom: 55px;
        }
        .sign-line {
            border-bottom: 1px solid #0f172a;
            font-weight: 700;
            color: #0f172a;
            padding-bottom: 4px;
        }
        .sign-role {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }

        .footer-note {
            margin-top: 35px;
            padding-top: 15px;
            border-top: 1px dashed #cbd5e1;
            font-size: 9.5px;
            color: #94a3b8;
            text-align: center;
        }

        /* Print Settings */
        @media print {
            body {
                background: white !important;
                padding: 0 !important;
            }
            .action-bar {
                display: none !important;
            }
            .container {
                box-shadow: none !important;
                border: none !important;
                padding: 0 !important;
                max-width: 100% !important;
            }
            @page {
                size: A4 portrait;
                margin: 15mm 20mm;
            }
        }
    </style>
</head>
<body>

    @php
        $isEmployee = auth()->user()?->role?->value === 'employee';
        $excelRoute = $isEmployee 
            ? route('employee.payslips.excel', $payslip) 
            : route('admin.payroll.payslip.excel', $payslip);
    @endphp

    {{-- Interactive Action Bar for Browser --}}
    <div class="action-bar">
        <h4><i class="ph ph-receipt"></i> Pratinjau Slip Gaji — {{ $payslip->user->name }} ({{ $payslip->period }})</h4>
        <div class="actions">
            <button onclick="window.print()" class="btn btn-print">
                <i class="ph ph-printer"></i> Cetak / Simpan PDF
            </button>
            <a href="{{ $excelRoute }}" class="btn btn-excel">
                <i class="ph ph-microsoft-excel-logo"></i> Unduh Excel (.csv)
            </a>
            <button onclick="window.close()" class="btn btn-back">
                <i class="ph ph-x"></i> Tutup
            </button>
        </div>
    </div>

    <div class="container">
        {{-- Header --}}
        <div class="header">
            <div>
                <div class="brand">SiGaji</div>
                <div class="brand-sub">Sistem Penggajian & Presensi Karyawan</div>
                <div class="brand-sub">Jl. Inovasi Solusi No. 42, Bandung</div>
            </div>
            <div class="invoice-title">
                <h2>Slip Gaji Resmi</h2>
                <div class="badge-period">Periode: {{ $payslip->period }}</div>
                <div style="font-size: 9.5px; color: #64748b; margin-top: 6px;">Ref: SLIP-{{ str_pad($payslip->id, 5, '0', STR_PAD_LEFT) }}</div>
            </div>
        </div>

        {{-- Employee & Document Info --}}
        <div class="info-card">
            <div class="info-item">
                <span class="info-label">Nama Karyawan</span>
                <span class="info-val">{{ $payslip->user->name }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Departemen / Jabatan</span>
                <span class="info-val">{{ $payslip->user->role?->label() ?? 'Karyawan' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Email Karyawan</span>
                <span class="info-val">{{ $payslip->user->email }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Tanggal Finalisasi</span>
                <span class="info-val">{{ $payslip->finalized_at?->format('d M Y H:i') ?? '-' }} WIB</span>
            </div>
        </div>

        {{-- Breakdown Table --}}
        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Komponen Rincian</th>
                    <th style="width: 20%;">Kategori</th>
                    <th class="amount" style="width: 30%;">Nominal (Rp)</th>
                </tr>
            </thead>
            <tbody>
                {{-- Base salary & fixed calculations --}}
                <tr>
                    <td><strong>Gaji Pokok</strong></td>
                    <td style="color: #64748b;">Pendapatan Pokok</td>
                    <td class="amount earning">+Rp {{ number_format($payslip->base_salary, 0, ',', '.') }}</td>
                </tr>
                @if($payslip->overtime_pay > 0)
                    <tr>
                        <td><strong>Upah Lembur Disetujui</strong></td>
                        <td style="color: #64748b;">Pendapatan Tambahan</td>
                        <td class="amount earning">+Rp {{ number_format($payslip->overtime_pay, 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if($payslip->late_deduction > 0)
                    <tr>
                        <td><strong>Potongan Keterlambatan</strong></td>
                        <td style="color: #64748b;">Potongan Disiplin</td>
                        <td class="amount deduction">-Rp {{ number_format($payslip->late_deduction, 0, ',', '.') }}</td>
                    </tr>
                @endif
                @if($payslip->leave_deduction > 0)
                    <tr>
                        <td><strong>Potongan Cuti / Izin Melebihi Batas</strong></td>
                        <td style="color: #64748b;">Potongan Kehadiran</td>
                        <td class="amount deduction">-Rp {{ number_format($payslip->leave_deduction, 0, ',', '.') }}</td>
                    </tr>
                @endif

                {{-- Other items --}}
                @foreach($payslip->items as $item)
                    <tr>
                        <td>{{ $item->component }}</td>
                        <td style="color: #64748b;">{{ $item->type === 'earning' ? 'Tunjangan Tambahan' : 'Potongan Tambahan' }}</td>
                        <td class="amount {{ $item->type === 'earning' ? 'earning' : 'deduction' }}">
                            {{ $item->type === 'deduction' ? '-' : '+' }}Rp {{ number_format($item->amount, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="2">TOTAL GAJI BERSIH (TAKE HOME PAY)</td>
                    <td class="amount">Rp {{ number_format($payslip->net_salary, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Signatures --}}
        <div class="signatures">
            <div class="sign-box">
                <div class="sign-title">Penerima (Karyawan),</div>
                <div class="sign-line">{{ $payslip->user->name }}</div>
                <div class="sign-role">Tanggal: {{ now()->translatedFormat('d F Y') }}</div>
            </div>
            <div class="sign-box">
                <div class="sign-title">Disahkan Oleh (Finance/HR),</div>
                <div class="sign-line">SiGaji Official</div>
                <div class="sign-role">Finance & Payroll Dept</div>
            </div>
        </div>

        <div class="footer-note">
            Dokumen ini diterbitkan secara otomatis dan sah melalui sistem SiGaji.<br>
            Tidak memerlukan tanda tangan basah dan memiliki kekuatan bukti otentik.
        </div>
    </div>

    @if(request()->has('autoprint'))
        <script>
            window.addEventListener('load', function() {
                window.print();
            });
        </script>
    @endif
</body>
</html>
