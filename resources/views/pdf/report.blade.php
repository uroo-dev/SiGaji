<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Payroll {{ $period }} - SiGaji</title>
    <style>
        @page {
            margin: 20px 25px;
            size: a4 landscape;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table {
            margin-bottom: 16px;
            border-bottom: 2px solid #FF5A36;
            padding-bottom: 10px;
        }
        .brand {
            font-size: 22px;
            font-weight: bold;
            color: #FF5A36;
            letter-spacing: -0.5px;
        }
        .brand-sub {
            font-size: 9.5px;
            color: #64748b;
            margin-top: 2px;
        }
        .doc-title {
            text-align: right;
        }
        .doc-title h2 {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 3px 0;
            text-transform: uppercase;
        }
        .badge-period {
            display: inline-block;
            background: #fff1ee;
            color: #FF5A36;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 10.5px;
        }
        .meta-text {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 4px;
        }
        /* Summary Metric Boxes */
        .metrics-table {
            margin-bottom: 16px;
        }
        .metrics-table td {
            width: 25%;
            padding: 4px;
        }
        .metric-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 8px 10px;
        }
        .metric-label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
        }
        .metric-val {
            font-size: 13px;
            font-weight: bold;
            margin-top: 3px;
            font-family: 'Courier', monospace;
        }
        .val-primary { color: #FF5A36; }
        .val-positive { color: #16a34a; }
        .val-negative { color: #dc2626; }
        .val-dark { color: #0f172a; }

        /* Report Table */
        .report-table {
            margin-bottom: 16px;
        }
        .report-table th {
            background-color: #f1f5f9;
            color: #334155;
            text-transform: uppercase;
            font-size: 8.5px;
            font-weight: bold;
            letter-spacing: 0.5px;
            padding: 7px 8px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
        }
        .report-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9.5px;
        }
        .report-table tr:nth-child(even) td {
            background-color: #fcfdfe;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-mono { font-family: 'Courier', monospace; }

        .total-row td {
            background-color: #f8fafc;
            font-weight: bold;
            border-top: 2px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
            padding: 8px;
            font-size: 10px;
        }

        /* Signatures */
        .signatures-table {
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .signatures-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .sign-title {
            font-size: 9.5px;
            color: #64748b;
            margin-bottom: 45px;
        }
        .sign-line {
            display: inline-block;
            width: 190px;
            border-bottom: 1px solid #0f172a;
            font-weight: bold;
            color: #0f172a;
            padding-bottom: 3px;
        }
        .sign-role {
            font-size: 8.5px;
            color: #64748b;
            margin-top: 2px;
        }
    </style>
</head>
<body>

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td style="vertical-align: top;">
                <div class="brand">SiGaji</div>
                <div class="brand-sub">Sistem Penggajian & Presensi Karyawan Terpadu</div>
                <div class="brand-sub">Jl. Inovasi Solusi No. 42, Bandung • info@sigaji.id</div>
            </td>
            <td class="doc-title" style="vertical-align: top;">
                <h2>Laporan Rekapitulasi Payroll</h2>
                <div class="badge-period">Periode: {{ $period }}</div>
                <div class="meta-text">Dicetak: {{ now()->translatedFormat('d F Y H:i') }} WIB</div>
            </td>
        </tr>
    </table>

    {{-- Metrics Summary --}}
    <table class="metrics-table">
        <tr>
            <td>
                <div class="metric-box">
                    <div class="metric-label">Total Gaji Bersih</div>
                    <div class="metric-val val-primary">Rp {{ number_format($totalNet, 0, ',', '.') }}</div>
                </div>
            </td>
            <td>
                <div class="metric-box">
                    <div class="metric-label">Total Upah Lembur</div>
                    <div class="metric-val val-positive">+Rp {{ number_format($totalOvertime, 0, ',', '.') }}</div>
                </div>
            </td>
            <td>
                <div class="metric-box">
                    <div class="metric-label">Total Potongan Denda/Cuti</div>
                    <div class="metric-val val-negative">-Rp {{ number_format($totalDeduction, 0, ',', '.') }}</div>
                </div>
            </td>
            <td>
                <div class="metric-box">
                    <div class="metric-label">Jumlah Karyawan (Final)</div>
                    <div class="metric-val val-dark">{{ $recap->count() }} Orang</div>
                </div>
            </td>
        </tr>
    </table>

    {{-- Table --}}
    <table class="report-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 180px;">Nama Karyawan</th>
                <th style="width: 170px;">Email</th>
                <th class="text-right">Gaji Pokok</th>
                <th class="text-right">Lembur</th>
                <th class="text-right">Pot. Telat</th>
                <th class="text-right">Pot. Cuti</th>
                <th class="text-right">Gaji Bersih</th>
                <th class="text-center" style="width: 60px;">Status</th>
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
                    <td class="text-right font-mono" style="font-weight: bold; color: #0f172a;">Rp {{ number_format($row->net_salary, 0, ',', '.') }}</td>
                    <td class="text-center font-mono" style="text-transform: uppercase; font-size: 8px; font-weight: bold; color: #16a34a;">
                        {{ $row->status instanceof \BackedEnum ? $row->status->value : $row->status }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 16px; color: #64748b;">
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
                <td class="text-right font-mono" style="color: #FF5A36; font-size: 11px;">Rp {{ number_format($totalNet, 0, ',', '.') }}</td>
                <td></td>
            </tr>
        </tfoot>
    </table>

    {{-- Signatures --}}
    <table class="signatures-table">
        <tr>
            <td>
                <div class="sign-title">Dibuat Oleh (HR Administrator),</div>
                <div class="sign-line">{{ auth()->user()?->name ?? 'HR Administrator' }}</div>
                <div class="sign-role">Tanggal: {{ now()->translatedFormat('d F Y') }}</div>
            </td>
            <td>
                <div class="sign-title">Mengetahui & Menyetujui (Direktur),</div>
                <div class="sign-line">Bambang Pratama, M.M.</div>
                <div class="sign-role">Direktur Operasional</div>
            </td>
        </tr>
    </table>

</body>
</html>
