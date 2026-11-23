<x-layouts.app title="Laporan Payroll">
    <x-page-header title="Laporan Payroll" subtitle="Rekapitulasi pengeluaran gaji dan kompensasi per periode." />

    <div class="mb-6 flex flex-wrap gap-3 items-center justify-between">
        <form method="GET" class="flex gap-2 items-center">
            <input type="month" name="period" value="{{ $period }}" class="border border-gray-200 rounded-xl px-4 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-brand">
            <button class="bg-gray-900 text-white px-5 py-2 rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors">Filter</button>
        </form>

        {{-- Choice to Download Excel or PDF --}}
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.reports.payroll', ['period' => $period]) }}" 
               class="bg-emerald-600 text-white px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 hover:bg-emerald-700 shadow-sm transition-all"
               title="Unduh Rekap Format Excel (.csv)">
                <i class="ph ph-microsoft-excel-logo text-lg"></i> Download Excel
            </a>
            <a href="{{ route('admin.reports.pdf', ['period' => $period]) }}" target="_blank"
               class="bg-brand text-white px-4 py-2.5 rounded-xl text-sm font-semibold flex items-center gap-2 hover:bg-brand-600 shadow-sm transition-all"
               title="Buka / Cetak Laporan Format PDF">
                <i class="ph ph-file-pdf text-lg"></i> Download PDF
            </a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-brand-light text-brand flex items-center justify-center">
                    <i class="ph ph-wallet text-xl"></i>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Total Gaji Bersih</p>
            </div>
            <p class="text-2xl font-bold text-gray-900 font-mono mt-1">Rp {{ number_format($totalNet, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-green-50 text-green-600 flex items-center justify-center">
                    <i class="ph ph-hourglass-high text-xl"></i>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Total Upah Lembur</p>
            </div>
            <p class="text-2xl font-bold text-green-600 font-mono mt-1">+Rp {{ number_format($totalOvertime, 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-xl bg-red-50 text-red-600 flex items-center justify-center">
                    <i class="ph ph-warning-circle text-xl"></i>
                </div>
                <p class="text-xs text-gray-500 uppercase font-semibold">Total Potongan Denda/Cuti</p>
            </div>
            <p class="text-2xl font-bold text-red-600 font-mono mt-1">-Rp {{ number_format($totalDeduction, 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Tabel Rekap --}}
    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Karyawan</th>
                        <th class="text-right px-4 py-3">Gaji Pokok</th>
                        <th class="text-right px-4 py-3">Lembur</th>
                        <th class="text-right px-4 py-3">Potongan</th>
                        <th class="text-right px-4 py-3">Gaji Bersih</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="text-center px-4 py-3">Unduh Slip</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($recap as $row)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($row->user->name) }}&background=FFE5DB&color=FF5A36" class="w-8 h-8 rounded-full">
                                    <div>
                                        <p class="font-medium text-gray-900">{{ $row->user->name }}</p>
                                        <p class="text-[11px] text-gray-400">{{ $row->user->email }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-mono">Rp {{ number_format($row->base_salary, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono text-green-600">+{{ number_format($row->overtime_pay, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono text-red-600">-{{ number_format($row->late_deduction + $row->leave_deduction, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-mono font-bold text-gray-900">Rp {{ number_format($row->net_salary, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center"><x-status-badge :status="$row->status" /></td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <a href="{{ route('admin.payroll.payslip.pdf', $row) }}" target="_blank"
                                       class="p-1.5 rounded-lg text-red-600 bg-red-50 hover:bg-red-600 hover:text-white transition-colors"
                                       title="Unduh Slip Gaji PDF">
                                        <i class="ph ph-file-pdf text-base"></i>
                                    </a>
                                    <a href="{{ route('admin.payroll.payslip.excel', $row) }}"
                                       class="p-1.5 rounded-lg text-emerald-600 bg-emerald-50 hover:bg-emerald-600 hover:text-white transition-colors"
                                       title="Unduh Slip Gaji Excel">
                                        <i class="ph ph-microsoft-excel-logo text-base"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <x-empty-state title="Belum ada data slip final" description="Pastikan penggajian periode ini telah difinalisasi pada menu Payroll." />
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
