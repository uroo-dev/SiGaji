<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Payroll {{ $period }} - SiGaji</title>
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
            font-size: 11px;
            color: #1e293b;
            background-color: #f8fafc;
            line-height: 1.4;
            padding: 20px;
        }
        .container {
            max-width: 1060px;
            margin: 0 auto;
            background: #ffffff;
            padding: 32px;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border: 1px solid #e2e8f0;
        }

        /* Top Action Bar (hidden on print) */
        .action-bar {
            max-width: 1060px;
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
            font-size: 14px;
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

        /* Document Header */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #FF5A36;
            padding-bottom: 20px;
            margin-bottom: 24px;
        }
        .brand-title {
            font-size: 24px;
            font-weight: 800;
            color: #FF5A36;
            letter-spacing: -0.5px;
            line-height: 1.1;
        }
        .brand-sub {
            font-size: 11px;
            color: #64748b;
            margin-top: 4px;
        }
        .doc-meta {
            text-align: right;
        }
        .doc-meta h2 {
            font-size: 18px;
            font-weight: 700;
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

        /* Metric Cards */
        .metric-cards {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 14px;
            margin-bottom: 24px;
        }
        .metric-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px 16px;
            border-radius: 10px;
        }
        .metric-label {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748b;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .metric-val {
            font-size: 15px;
            font-weight: 700;
            color: #0f172a;
            margin-top: 4px;
            font-family: 'JetBrains Mono', monospace;
        }
        .val-positive { color: #16a34a; }
        .val-negative { color: #dc2626; }
        .val-highlight { color: #FF5A36; }

        /* Table */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            margin-bottom: 24px;
        }
        th {
            background: #f1f5f9;
            color: #334155;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding: 9px 10px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
        }
        td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            color: #1e293b;
        }
        tr:nth-child(even) td {
            background: #fcfdfe;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .total-row td {
            background: #f8fafc;
            font-weight: 700;
            border-top: 2px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
            padding: 10px;
            font-size: 11px;
        }

        /* Signatures */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .sign-box {
            width: 200px;
            text-align: center;
        }
        .sign-title {
            font-size: 10px;
            color: #64748b;
            margin-bottom: 50px;
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
                size: A4 landscape;
                margin: 12mm 15mm;
            }
            tr {
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    {{-- Interactive Action Bar for Browser --}}
    <div class="action-bar">
        <h4><i class="ph ph-file-text"></i> Pratinjau Dokumen PDF — Laporan Payroll {{ $period }}</h4>
        <div class="actions">
            <button onclick="window.print()" class="btn btn-print">
                <i class="ph ph-printer"></i> Cetak / Simpan PDF
            </button>
            <a href="{{ route('admin.reports.payroll', ['period' => $period]) }}" class="btn btn-excel">
                <i class="ph ph-microsoft-excel-logo"></i> Unduh Excel (.csv)
            </a>
            <button onclick="window.close()" class="btn btn-back">
                <i class="ph ph-x"></i> Tutup
            </button>
        </div>
    </div>

    <div class="container">
        {{-- Document Header --}}
        <div class="doc-header">
            <div>
                <div class="brand-title">SiGaji</div>
                <div class="brand-sub">Sistem Penggajian & Presensi Karyawan Terpadu</div>
                <div class="brand-sub">Jl. Inovasi Solusi No. 42, Bandung • info@sigaji.id</div>
            </div>
            <div class="doc-meta">
                <h2>Laporan Rekapitulasi Payroll</h2>
                <div class="badge-period">Periode: {{ $period }}</div>
                <div style="font-size: 9px; color: #64748b; margin-top: 6px;">Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB</div>
            </div>
        </div>

        {{-- Metrics Summary --}}
        <div class="metric-cards">
            <div class="metric-card">
                <div class="metric-label">Total Gaji Bersih</div>
                <div class="metric-val val-highlight">Rp {{ number_format($totalNet, 0, ',', '.') }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Total Upah Lembur</div>
                <div class="metric-val val-positive">+Rp {{ number_format($totalOvertime, 0, ',', '.') }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Total Potongan Denda/Cuti</div>
                <div class="metric-val val-negative">-Rp {{ number_format($totalDeduction, 0, ',', '.') }}</div>
            </div>
            <div class="metric-card">
                <div class="metric-label">Jumlah Karyawan (Final)</div>
                <div class="metric-val">{{ $recap->count() }} Orang</div>
            </div>
        </div>

        {{-- Table --}}
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">No</th>
                    <th>Nama Karyawan</th>
                    <th>Email</th>
                    <th class="text-right">Gaji Pokok</th>
                    <th class="text-right">Lembur</th>
                    <th class="text-right">Pot. Telat</th>
                    <th class="text-right">Pot. Cuti</th>
                    <th class="text-right">Gaji Bersih</th>
                    <th class="text-center" style="width: 70px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recap as $index => $row)
                    <tr>
                        <td class="text-center font-mono">{{ $index + 1 }}</td>
                        <td><strong>{{ $row->user->name }}</strong></td>
                        <td style="color: #64748b;">{{ $row->user->email }}</td>
                        <td class="text-right font-mono">Rp {{ number_format($row->base_salary, 0, ',', '.') }}</td>
                        <td class="text-right font-mono" style="color: #16a34a;">+{{ number_format($row->overtime_pay, 0, ',', '.') }}</td>
                        <td class="text-right font-mono" style="color: #dc2626;">-{{ number_format($row->late_deduction, 0, ',', '.') }}</td>
                        <td class="text-right font-mono" style="color: #dc2626;">-{{ number_format($row->leave_deduction, 0, ',', '.') }}</td>
                        <td class="text-right font-mono" style="font-weight: 700; color: #0f172a;">Rp {{ number_format($row->net_salary, 0, ',', '.') }}</td>
                        <td class="text-center font-mono" style="text-transform: uppercase; font-size: 8px; font-weight: 700; color: #16a34a;">
                            {{ $row->status instanceof \BackedEnum ? $row->status->value : $row->status }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center" style="padding: 24px; color: #64748b;">
                            Tidak ada data payroll yang difinalisasi pada periode {{ $period }}.
                        </td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-center">RINGKASAN TOTAL KESELURUHAN</td>
                    <td class="text-right font-mono">Rp {{ number_format($recap->sum('base_salary'), 0, ',', '.') }}</td>
                    <td class="text-right font-mono" style="color: #16a34a;">+Rp {{ number_format($totalOvertime, 0, ',', '.') }}</td>
                    <td class="text-right font-mono" style="color: #dc2626;">-Rp {{ number_format($recap->sum('late_deduction'), 0, ',', '.') }}</td>
                    <td class="text-right font-mono" style="color: #dc2626;">-Rp {{ number_format($recap->sum('leave_deduction'), 0, ',', '.') }}</td>
                    <td class="text-right font-mono" style="color: #FF5A36; font-size: 12px;">Rp {{ number_format($totalNet, 0, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        {{-- Signatures --}}
        <div class="signatures">
            <div class="sign-box">
                <div class="sign-title">Dibuat Oleh (HR & Payroll Specialist),</div>
                <div class="sign-line">{{ auth()->user()?->name ?? 'HR Administrator' }}</div>
                <div class="sign-role">Tanggal: {{ now()->translatedFormat('d F Y') }}</div>
            </div>
            <div class="sign-box">
                <div class="sign-title">Mengetahui & Menyetujui (Direktur),</div>
                <div class="sign-line">Bambang Pratama, M.M.</div>
                <div class="sign-role">Direktur Operasional</div>
            </div>
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
