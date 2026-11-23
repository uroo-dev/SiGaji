<x-layouts.app title="Payroll">
    <x-page-header title="Payroll" subtitle="Kelola penggajian karyawan bulanan.">
        <x-slot:actions>
            <form method="GET" class="flex gap-2">
                <input type="month" name="period" value="{{ $period }}" class="bg-white border border-gray-200 rounded-lg px-3 py-2 text-sm">
                <button class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">Filter</button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <div x-data="payrollManager('{{ $period }}')" x-init="init()">
        
        {{-- Generate Button (jika belum ada data) --}}
        <template x-if="state === 'idle' && {{ $payslips->isEmpty() ? 'true' : 'false' }}">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8 text-center">
                <div class="w-16 h-16 rounded-full bg-brand-light flex items-center justify-center mx-auto mb-4">
                    <i class="ph ph-calculator text-2xl text-brand"></i>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Belum Ada Data Payroll</h3>
                <p class="text-sm text-gray-500 mt-1 mb-6">Klik tombol di bawah untuk menghitung gaji periode {{ $period }}.</p>
                <button @click="generate()" class="bg-brand text-white px-6 py-2.5 rounded-lg font-medium hover:bg-brand-600 transition-colors">
                    <i class="ph ph-play mr-1"></i> Hitung Gaji Sekarang
                </button>
            </div>
        </template>

        {{-- Processing State --}}
        <template x-if="state === 'processing'">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-10 text-center">
                <div class="w-20 h-20 rounded-full border-4 border-brand border-t-transparent animate-spin mx-auto mb-4"></div>
                <h3 class="text-lg font-bold text-gray-900">Menghitung Gaji...</h3>
                <p class="text-sm text-gray-500 mt-1" x-text="`${completed} dari ${total} karyawan`"></p>
                <p class="text-xs text-gray-400 mt-2">Proses berjalan di background. Jangan tutup halaman ini.</p>
            </div>
        </template>

        {{-- Review Table (draft list) --}}
        <template x-if="state === 'review' || state === 'done' || {{ !$payslips->isEmpty() ? 'true' : 'false' }}">
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 flex justify-between items-center flex-wrap gap-2">
                    <div>
                        <h3 class="font-semibold text-gray-900">Rincian Payroll — {{ $period }}</h3>
                        <p class="text-xs text-gray-500">Total bersih: <span class="font-mono font-bold text-gray-900">Rp {{ number_format($payslips->sum('net_salary'), 0, ',', '.') }}</span></p>
                    </div>
                    @if($payslips->where('status.value', 'draft')->count() > 0)
                        <form method="POST" action="{{ route('admin.payroll.finalize') }}" onsubmit="return confirm('Finalisasi slip gaji? Data tidak dapat diubah.')">
                            @csrf
                            <input type="hidden" name="period" value="{{ $period }}">
                            <button class="bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-600">
                                <i class="ph ph-check-circle mr-1"></i> Finalisasi
                            </button>
                        </form>
                    @endif
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
                                </tr>
                            @empty
                                <tr><td colspan="6"><x-empty-state title="Belum ada data" description="Klik tombol Hitung Gaji untuk memulai." /></td></tr>
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
                progress: 0, completed: 0, total: 0, jobId: null,

                init() {
                    if ({{ $payslips->where('status.value', 'draft')->count() > 0 ? 'true' : 'false' }}) {
                        this.state = 'review';
                    }
                },

                async generate() {
                    this.state = 'processing';
                    const res = await fetch('{{ route("admin.payroll.generate") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({ period })
                    });
                    const data = await res.json();
                    if (!res.ok) { alert(data.error); this.state = 'idle'; return; }
                    this.poll();
                },

                poll() {
                    const timer = setInterval(async () => {
                        const res = await fetch(`/admin/payroll/status/${period}`);
                        const data = await res.json();
                        this.completed = data.completed;
                        this.total = data.total;
                        this.progress = data.total > 0 ? Math.round((data.completed / data.total) * 100) : 0;
                        if (data.status === 'done' || data.status === 'completed') {
                            clearInterval(timer);
                            setTimeout(() => window.location.reload(), 800);
                        }
                    }, 2000);
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
