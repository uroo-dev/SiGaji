<x-layouts.app title="Payroll">
    <x-page-header title="Payroll" subtitle="Kelola penggajian karyawan bulanan.">
        <x-slot:actions>
            <form method="GET" class="flex gap-2">
                <input type="month" name="period" value="{{ $period }}" class="bg-white border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand">
                <button class="bg-gray-900 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors">Filter</button>
            </form>
        </x-slot:actions>
    </x-page-header>

    @php
        $finalCount = $payslips->where('status.value', 'final')->count();
        $draftCount = $payslips->where('status.value', 'draft')->count();
        $totalCount = $payslips->count();
    @endphp

    <div x-data="payrollManager('{{ $period }}')" x-init="init()">
        
        {{-- Status Periode & Action Controls --}}
        @if($totalCount > 0)
            <div class="mb-5 flex flex-wrap items-center justify-between gap-4 p-4 sm:p-5 rounded-2xl {{ $finalCount > 0 && $draftCount === 0 ? 'bg-emerald-50 border border-emerald-200' : 'bg-amber-50 border border-amber-200' }} shadow-sm">
                <div class="flex items-center gap-3.5">
                    <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 {{ $finalCount > 0 && $draftCount === 0 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                        <i class="ph {{ $finalCount > 0 && $draftCount === 0 ? 'ph-seal-check' : 'ph-clock-countdown' }} text-2xl"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-bold text-sm {{ $finalCount > 0 && $draftCount === 0 ? 'text-emerald-950' : 'text-amber-950' }}">
                                {{ $finalCount > 0 && $draftCount === 0 ? 'Status: Difinalisasi (Resmi)' : 'Status: Draft (Belum Difinalisasi)' }}
                            </h4>
                            <span class="text-[11px] font-mono px-2 py-0.5 rounded-md font-semibold {{ $finalCount > 0 && $draftCount === 0 ? 'bg-emerald-200 text-emerald-800' : 'bg-amber-200 text-amber-800' }}">
                                {{ $finalCount > 0 && $draftCount === 0 ? 'RESMI' : 'DRAFT' }}
                            </span>
                        </div>
                        <p class="text-xs {{ $finalCount > 0 && $draftCount === 0 ? 'text-emerald-800' : 'text-amber-800' }} mt-0.5">
                            {{ $finalCount > 0 && $draftCount === 0 
                                ? 'Slip gaji sudah dipublikasikan ke seluruh karyawan. Anda dapat mengembalikan ke draft jika perlu penyesuaian atau ingin dihitung ulang.' 
                                : 'Slip gaji masih tersimpan sebagai draft internal dan belum terlihat oleh karyawan. Periksa angka lalu finalisasi.' }}
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2 flex-wrap">
                    {{-- Tombol Hitung Ulang (bisa dipakai saat Final maupun Draft) --}}
                    <button type="button" @click="generate(true)" 
                            class="bg-white border border-gray-300 text-gray-700 hover:bg-gray-100 px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-1.5 shadow-sm transition-all">
                        <i class="ph ph-arrows-clockwise text-base text-brand"></i> Hitung Ulang
                    </button>

                    @if($finalCount > 0)
                        {{-- Tombol Kembalikan ke Draft --}}
                        <button type="button" 
                                onclick="confirmModal({
                                    title: 'Kembalikan Payroll Periode {{ $period }} ke Draft?',
                                    text: 'Slip gaji akan diubah kembali ke status Draft dan sementara disembunyikan dari karyawan sampai difinalisasi ulang.',
                                    icon: 'warning',
                                    confirmText: 'Ya, Kembalikan ke Draft',
                                    cancelText: 'Batal',
                                    confirmColor: '#ea580c',
                                    formId: 'revert-payroll-form'
                                })"
                                class="bg-amber-600 text-white hover:bg-amber-700 px-4 py-2.5 rounded-xl text-xs font-semibold flex items-center gap-1.5 shadow-sm transition-all">
                            <i class="ph ph-arrow-u-up-left text-base"></i> Kembalikan ke Draft
                        </button>
                        <form id="revert-payroll-form" method="POST" action="{{ route('admin.payroll.revert') }}" class="hidden">
                            @csrf
                            <input type="hidden" name="period" value="{{ $period }}">
                        </form>
                    @endif

                    @if($draftCount > 0)
                        {{-- Tombol Finalisasi --}}
                        <button type="button" 
                                onclick="confirmModal({
                                    title: 'Finalisasi Slip Gaji {{ $period }}?',
                                    text: 'Slip gaji yang difinalisasi akan langsung dipublikasikan ke seluruh karyawan dan statusnya berubah menjadi resmi.',
                                    icon: 'question',
                                    confirmText: 'Ya, Finalisasi',
                                    cancelText: 'Batal',
                                    confirmColor: '#FF5A36',
                                    formId: 'finalize-payroll-form'
                                })"
                                class="bg-brand text-white px-5 py-2.5 rounded-xl text-xs font-semibold hover:bg-brand-600 shadow-sm flex items-center gap-1.5 transition-all">
                            <i class="ph ph-check-circle text-base"></i> Finalisasi Payroll
                        </button>
                        <form id="finalize-payroll-form" method="POST" action="{{ route('admin.payroll.finalize') }}" class="hidden">
                            @csrf
                            <input type="hidden" name="period" value="{{ $period }}">
                        </form>
                    @endif
                </div>
            </div>
        @endif
        
        {{-- Generate Button (jika belum ada data) --}}
        <template x-if="state === 'idle' && {{ $payslips->isEmpty() ? 'true' : 'false' }}">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 sm:p-12 text-center max-w-xl mx-auto mt-6">
                <div class="w-20 h-20 rounded-3xl bg-brand-light flex items-center justify-center mx-auto mb-5 text-brand shadow-sm">
                    <i class="ph ph-calculator text-3xl"></i>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-2">Belum Ada Data Payroll</h3>
                <p class="text-sm text-gray-500 mb-8 max-w-sm mx-auto">Klik tombol di bawah untuk menghitung gaji seluruh karyawan aktif periode <strong>{{ $period }}</strong> secara otomatis.</p>
                <button type="button" @click="generate(false)" class="bg-brand text-white px-8 py-3 rounded-2xl font-semibold hover:bg-brand-600 transition-all shadow-lg shadow-brand/30 inline-flex items-center gap-2">
                    <i class="ph ph-play text-lg"></i> Hitung Gaji Sekarang
                </button>
            </div>
        </template>

        {{-- Processing State --}}
        <template x-if="state === 'processing'">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-10 text-center max-w-md mx-auto mt-6">
                <div class="w-16 h-16 rounded-full border-4 border-brand border-t-transparent animate-spin mx-auto mb-5"></div>
                <h3 class="text-lg font-bold text-gray-900 mb-1" x-text="isRecalculating ? 'Menghitung Ulang Gaji...' : 'Menghitung Gaji Karyawan...'"></h3>
                <p class="text-sm text-gray-500" x-text="`${completed} dari ${total} karyawan selesai dihitung`"></p>
                <div class="w-full bg-gray-100 rounded-full h-2.5 mt-4 overflow-hidden">
                    <div class="bg-brand h-2.5 rounded-full transition-all duration-300" :style="`width: ${progress}%`"></div>
                </div>
                <p class="text-xs text-gray-400 mt-3">Proses berjalan aman di background. Jangan tutup halaman ini.</p>
            </div>
        </template>

        {{-- Review Table --}}
        <template x-if="state === 'review' || state === 'done' || {{ !$payslips->isEmpty() ? 'true' : 'false' }}">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-4 sm:p-5 border-b border-gray-100 flex justify-between items-center flex-wrap gap-3">
                    <div>
                        <h3 class="font-bold text-gray-900 text-base">Rincian Slip Gaji Karyawan — {{ $period }}</h3>
                        <p class="text-xs text-gray-500 mt-0.5">Total pengeluaran gaji bersih: <span class="font-mono font-bold text-gray-900 text-sm">Rp {{ number_format($payslips->sum('net_salary'), 0, ',', '.') }}</span></p>
                    </div>
                </div>

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
                            @forelse($payslips as $payslip)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">{{ $payslip->user->name }}</td>
                                    <td class="px-4 py-3 text-right font-mono">Rp {{ number_format($payslip->base_salary, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-green-600">+{{ number_format($payslip->overtime_pay, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-mono text-red-600">-{{ number_format($payslip->late_deduction + $payslip->leave_deduction, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-mono font-bold text-gray-900">Rp {{ number_format($payslip->net_salary, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-center"><x-status-badge :status="$payslip->status" /></td>
                                    <td class="px-4 py-3 text-center">
                                        @if($payslip->status->value === 'final')
                                            <div class="flex items-center justify-center gap-1.5">
                                                <a href="{{ route('admin.payroll.payslip.pdf', $payslip) }}" target="_blank"
                                                   class="p-1.5 rounded-lg text-red-600 bg-red-50 hover:bg-red-600 hover:text-white transition-colors"
                                                   title="Unduh Slip Gaji PDF">
                                                    <i class="ph ph-file-pdf text-base"></i>
                                                </a>
                                                <a href="{{ route('admin.payroll.payslip.excel', $payslip) }}"
                                                   class="p-1.5 rounded-lg text-emerald-600 bg-emerald-50 hover:bg-emerald-600 hover:text-white transition-colors"
                                                   title="Unduh Slip Gaji Excel">
                                                    <i class="ph ph-microsoft-excel-logo text-base"></i>
                                                </a>
                                            </div>
                                        @else
                                            <span class="text-xs text-amber-600 font-medium italic">Draft</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7"><x-empty-state title="Belum ada data" description="Klik tombol Hitung Gaji untuk memulai." /></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </div>

    @push('scripts')
    <script>
        function payrollManager(period) {
            return {
                state: 'idle',
                progress: 0, 
                completed: 0, 
                total: 0, 
                jobId: null,
                isRecalculating: false,

                init() {
                    if ({{ $payslips->where('status.value', 'draft')->count() > 0 ? 'true' : 'false' }}) {
                        this.state = 'review';
                    }
                },

                generate(recalculate = false) {
                    const isRe = !!recalculate;
                    this.isRecalculating = isRe;

                    confirmModal({
                        title: isRe ? `Hitung Ulang Payroll Periode ${period}?` : `Hitung Payroll Periode ${period}?`,
                        text: isRe 
                            ? 'Sistem akan menghitung ulang seluruh absensi, lembur yang disetujui, dan potongan denda seluruh karyawan aktif. Status slip gaji akan menjadi Draft sampai difinalisasi ulang.' 
                            : 'Sistem akan menghitung gaji pokok, lembur yang disetujui, dan potongan denda keterlambatan seluruh karyawan aktif.',
                        icon: 'question',
                        confirmText: isRe ? 'Ya, Hitung Ulang' : 'Mulai Hitung',
                        cancelText: 'Batal',
                        confirmColor: '#FF5A36',
                        onConfirm: async () => {
                            this.state = 'processing';
                            try {
                                const res = await fetch('{{ route("admin.payroll.generate") }}', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json',
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                    },
                                    body: JSON.stringify({ period, recalculate: isRe })
                                });
                                const data = await res.json();
                                if (!res.ok) { 
                                    Swal.fire({ icon: 'error', title: 'Gagal', text: data.error || 'Terjadi kesalahan sistem.' });
                                    this.state = 'idle'; 
                                    return; 
                                }
                                this.poll();
                            } catch (e) {
                                Swal.fire({ icon: 'error', title: 'Koneksi Terputus', text: 'Gagal menghubungi server.' });
                                this.state = 'idle';
                            }
                        }
                    });
                },

                poll() {
                    const timer = setInterval(async () => {
                        try {
                            const res = await fetch(`/admin/payroll/status/${period}`);
                            const data = await res.json();
                            this.completed = data.completed;
                            this.total = data.total;
                            this.progress = data.total > 0 ? Math.round((data.completed / data.total) * 100) : 0;

                            if (data.status === 'done' || (data.total > 0 && data.completed >= data.total)) {
                                clearInterval(timer);
                                this.state = 'done';
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Selesai!',
                                    text: 'Perhitungan gaji berhasil diselesaikan.',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                setTimeout(() => {
                                    window.location.reload();
                                }, 1200);
                            }
                        } catch (e) {
                            console.error('Polling error', e);
                        }
                    }, 1000);
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
