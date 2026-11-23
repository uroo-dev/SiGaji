<x-layouts.app title="Slip Gaji Saya">
    <x-page-header title="Slip Gaji" subtitle="Riwayat dan rincian slip gaji bulanan Anda." />

    <div class="space-y-4">
        @forelse($payslips as $payslip)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-gray-100 pb-4 mb-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wide">Periode Penggajian</p>
                        <h3 class="text-xl font-bold text-gray-900 font-mono">{{ $payslip->period }}</h3>
                    </div>
                    <div class="flex items-center gap-3">
                        <x-status-badge :status="$payslip->status" />
                        @if($payslip->status->value === 'final')
                            <div class="flex items-center gap-2">
                                <a href="{{ route('employee.payslips.pdf', $payslip) }}" target="_blank"
                                   class="bg-brand text-white px-3.5 py-2 rounded-xl text-xs font-semibold hover:bg-brand-600 flex items-center gap-1.5 shadow-sm transition-all">
                                    <i class="ph ph-file-pdf text-base"></i> Unduh PDF
                                </a>
                                <a href="{{ route('employee.payslips.excel', $payslip) }}"
                                   class="bg-emerald-600 text-white px-3.5 py-2 rounded-xl text-xs font-semibold hover:bg-emerald-700 flex items-center gap-1.5 shadow-sm transition-all">
                                    <i class="ph ph-microsoft-excel-logo text-base"></i> Unduh Excel
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Item breakdown --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <p class="text-xs font-semibold text-gray-500 uppercase">Komponen Pendapatan</p>
                        @foreach($payslip->items->where('type', 'earning') as $item)
                            <div class="flex justify-between text-sm py-1 border-b border-gray-50">
                                <span class="text-gray-700">{{ $item->component }}</span>
                                <span class="font-mono font-medium text-green-600">+Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="space-y-2">
                        <p class="text-xs font-semibold text-gray-500 uppercase">Komponen Potongan</p>
                        @foreach($payslip->items->where('type', 'deduction') as $item)
                            <div class="flex justify-between text-sm py-1 border-b border-gray-50">
                                <span class="text-gray-700">{{ $item->component }}</span>
                                <span class="font-mono font-medium text-red-600">-Rp {{ number_format($item->amount, 0, ',', '.') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="mt-4 pt-4 border-t border-gray-100 flex justify-between items-center bg-gray-50 -mx-6 -mb-6 p-6 rounded-b-2xl">
                    <span class="font-bold text-gray-900">Total Gaji Bersih (Take Home Pay)</span>
                    <span class="font-bold text-xl text-brand font-mono">Rp {{ number_format($payslip->net_salary, 0, ',', '.') }}</span>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 p-8">
                <x-empty-state title="Belum ada slip gaji" description="Slip gaji bulanan Anda akan muncul di sini setelah difinalisasi oleh HR." icon="ph-receipt" />
            </div>
        @endforelse
    </div>
</x-layouts.app>
