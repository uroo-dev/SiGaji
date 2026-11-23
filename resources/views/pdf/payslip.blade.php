<!DOCTYPE html>
<html lang="id">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Slip Gaji {{ $payslip->period }} - {{ $payslip->user->name }}</title>
    <style>
        @page {
            margin: 25px 30px;
            size: a4 portrait;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .header-table {
            margin-bottom: 20px;
            border-bottom: 2px solid #FF5A36;
            padding-bottom: 12px;
        }
        .brand {
            font-size: 24px;
            font-weight: bold;
            color: #FF5A36;
            letter-spacing: -0.5px;
        }
        .brand-sub {
            font-size: 10px;
            color: #64748b;
            margin-top: 2px;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h2 {
            font-size: 16px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }
        .badge-period {
            display: inline-block;
            background: #fff1ee;
            color: #FF5A36;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 11px;
        }
        .ref-text {
            font-size: 9px;
            color: #64748b;
            margin-top: 4px;
        }
        .info-table {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 8px 12px;
            font-size: 10.5px;
            vertical-align: top;
        }
        .info-label {
            color: #64748b;
            width: 130px;
        }
        .info-val {
            font-weight: bold;
            color: #0f172a;
        }
        .breakdown-table {
            margin-bottom: 20px;
        }
        .breakdown-table th {
            background-color: #f1f5f9;
            color: #334155;
            text-transform: uppercase;
            font-size: 9px;
            font-weight: bold;
            letter-spacing: 0.5px;
            padding: 8px 10px;
            border-top: 1px solid #cbd5e1;
            border-bottom: 2px solid #cbd5e1;
            text-align: left;
        }
        .breakdown-table td {
            padding: 8px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10.5px;
        }
        .amount {
            text-align: right;
            font-family: 'Courier', monospace;
            font-weight: bold;
        }
        .amount-earning {
            color: #16a34a;
        }
        .amount-deduction {
            color: #dc2626;
        }
        .total-row td {
            background-color: #0f172a;
            color: #ffffff;
            font-weight: bold;
            font-size: 12px;
            padding: 10px;
            border: none;
        }
        .total-row .amount {
            color: #ffffff;
            font-size: 13px;
        }
        .signatures-table {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .signatures-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
        }
        .sign-title {
            font-size: 10px;
            color: #64748b;
            margin-bottom: 50px;
        }
        .sign-line {
            display: inline-block;
            width: 170px;
            border-bottom: 1px solid #0f172a;
            font-weight: bold;
            color: #0f172a;
            padding-bottom: 3px;
        }
        .sign-role {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .footer-note {
            margin-top: 25px;
            padding-top: 10px;
            border-top: 1px dashed #cbd5e1;
            font-size: 9px;
            color: #94a3b8;
            text-align: center;
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
            <td class="invoice-title" style="vertical-align: top;">
                <h2>Slip Gaji Resmi</h2>
                <div class="badge-period">Periode: {{ $payslip->period }}</div>
                <div class="ref-text">Ref: SLIP-{{ str_pad($payslip->id, 5, '0', STR_PAD_LEFT) }}</div>
            </td>
        </tr>
    </table>

    {{-- Employee & Document Info --}}
    <table class="info-table">
        <tr>
            <td class="info-label">Nama Karyawan</td>
            <td class="info-val">: {{ $payslip->user->name }}</td>
            <td class="info-label">Email</td>
            <td class="info-val">: {{ $payslip->user->email }}</td>
        </tr>
        <tr>
            <td class="info-label">Jabatan / Role</td>
            <td class="info-val">: {{ $payslip->user->role?->label() ?? 'Karyawan' }}</td>
            <td class="info-label">Tanggal Finalisasi</td>
            <td class="info-val">: {{ $payslip->finalized_at?->format('d M Y H:i') ?? '-' }} WIB</td>
        </tr>
    </table>

    {{-- Breakdown Table --}}
    <table class="breakdown-table">
        <thead>
            <tr>
                <th style="width: 50%;">Komponen Rincian</th>
                <th style="width: 25%;">Kategori</th>
                <th class="amount" style="width: 25%;">Nominal (Rp)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Gaji Pokok</strong></td>
                <td style="color: #64748b;">Pendapatan Pokok</td>
                <td class="amount amount-earning">+Rp {{ number_format($payslip->base_salary, 0, ',', '.') }}</td>
            </tr>
            @if($payslip->overtime_pay > 0)
                <tr>
                    <td><strong>Upah Lembur Disetujui</strong></td>
                    <td style="color: #64748b;">Pendapatan Tambahan</td>
                    <td class="amount amount-earning">+Rp {{ number_format($payslip->overtime_pay, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($payslip->late_deduction > 0)
                <tr>
                    <td><strong>Potongan Keterlambatan</strong></td>
                    <td style="color: #64748b;">Potongan Disiplin</td>
                    <td class="amount amount-deduction">-Rp {{ number_format($payslip->late_deduction, 0, ',', '.') }}</td>
                </tr>
            @endif
            @if($payslip->leave_deduction > 0)
                <tr>
                    <td><strong>Potongan Cuti / Izin Melebihi Batas</strong></td>
                    <td style="color: #64748b;">Potongan Kehadiran</td>
                    <td class="amount amount-deduction">-Rp {{ number_format($payslip->leave_deduction, 0, ',', '.') }}</td>
                </tr>
            @endif

            @foreach($payslip->items as $item)
                <tr>
                    <td>{{ $item->component }}</td>
                    <td style="color: #64748b;">{{ $item->type === 'earning' ? 'Tunjangan Tambahan' : 'Potongan Tambahan' }}</td>
                    <td class="amount {{ $item->type === 'earning' ? 'amount-earning' : 'amount-deduction' }}">
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
    <table class="signatures-table">
        <tr>
            <td>
                <div class="sign-title">Penerima (Karyawan),</div>
                <div class="sign-line">{{ $payslip->user->name }}</div>
                <div class="sign-role">Tanggal: {{ now()->translatedFormat('d F Y') }}</div>
            </td>
            <td>
                <div class="sign-title">Disahkan Oleh (Finance/HR),</div>
                <div class="sign-line">SiGaji Official</div>
                <div class="sign-role">Finance & Payroll Dept</div>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini diterbitkan secara otomatis dan sah melalui sistem SiGaji.<br>
        Tidak memerlukan tanda tangan basah dan memiliki kekuatan bukti otentik.
    </div>

</body>
</html>
