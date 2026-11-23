<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji {{ $payslip->period }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #111; margin: 30px; }
        .header { display: flex; justify-content: space-between; border-bottom: 3px solid #FF5A36; padding-bottom: 15px; margin-bottom: 20px; }
        .brand { font-size: 24px; font-weight: bold; color: #FF5A36; }
        h2 { text-align: right; margin: 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { padding: 8px; text-align: left; }
        .info td { padding: 4px 8px; }
        .breakdown th { background: #f3f4f6; border-bottom: 1px solid #e5e7eb; }
        .amount { text-align: right; font-family: monospace; }
        .total { background: #FF5A36; color: white; font-weight: bold; font-size: 14px; }
        .total td { padding: 12px 8px; }
        .footer { margin-top: 40px; font-size: 10px; color: #666; text-align: center; }
    </style>
</head>
<body>
    <div class="header">
        <div>
            <div class="brand">SiGaji</div>
            <div>Sistem Penggajian & Presensi Karyawan</div>
            <div>Jl. Inovasi Solusi No. 42, Bandung</div>
        </div>
        <div>
            <h2>SLIP GAJI RESMI</h2>
            <div style="text-align:right">Periode: {{ $payslip->period }}</div>
        </div>
    </div>

    <table class="info">
        <tr><td style="width:140px">Nama Karyawan</td><td>: {{ $payslip->user->name }}</td></tr>
        <tr><td>Email</td><td>: {{ $payslip->user->email }}</td></tr>
        <tr><td>Jabatan</td><td>: {{ $payslip->user->role?->label() ?? 'Karyawan' }}</td></tr>
        <tr><td>Tanggal Finalisasi</td><td>: {{ $payslip->finalized_at?->format('d M Y H:i') ?? '-' }}</td></tr>
    </table>

    <table class="breakdown">
        <thead>
            <tr><th>Komponen Rincian</th><th class="amount">Nominal (Rp)</th></tr>
        </thead>
        <tbody>
            @foreach($payslip->items as $item)
                <tr>
                    <td>{{ $item->component }} ({{ $item->type === 'earning' ? 'Pendapatan' : 'Potongan' }})</td>
                    <td class="amount">{{ $item->type === 'deduction' ? '-' : '+' }} {{ number_format($item->amount, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total">
                <td>GAJI BERSIH (TAKE HOME PAY)</td>
                <td class="amount">Rp {{ number_format($payslip->net_salary, 0, ',', '.') }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        Dokumen ini diterbitkan secara elektronik oleh sistem SiGaji.<br>
        Sah dan tidak memerlukan tanda tangan basah.
    </div>
</body>
</html>
