<x-layouts.app title="Pengajuan Cuti">
    <div x-data="{ showModal: false }">
        <x-page-header title="Pengajuan Cuti" subtitle="Kelola permohonan cuti tahunan, sakit, atau cuti tanpa gaji.">
            <x-slot:actions>
                <button type="button" @click="showModal = true" class="bg-brand text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-brand-600 flex items-center gap-2 shadow-sm transition-all">
                    <i class="ph ph-plus text-base"></i> Ajukan Cuti
                </button>
            </x-slot:actions>
        </x-page-header>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="text-left px-4 py-3">Jenis Cuti</th>
                            <th class="text-left px-4 py-3">Tanggal Mulai</th>
                            <th class="text-left px-4 py-3">Tanggal Selesai</th>
                            <th class="text-center px-4 py-3">Total Hari</th>
                            <th class="text-left px-4 py-3">Alasan</th>
                            <th class="text-center px-4 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($leaves as $leave)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-semibold text-gray-900 uppercase text-xs">{{ $leave->type }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $leave->start_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $leave->end_date->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-center font-bold text-gray-800">{{ $leave->total_days }} Hari</td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $leave->reason }}</td>
                                <td class="px-4 py-3 text-center"><x-status-badge :status="$leave->status" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty-state title="Belum ada riwayat cuti" description="Klik tombol Ajukan Cuti untuk membuat permohonan." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($leaves, 'hasPages') && $leaves->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $leaves->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Pengajuan Cuti --}}
        <div x-show="showModal" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showModal = false">
            <div class="bg-white rounded-3xl p-6 sm:p-8 w-full max-w-md shadow-2xl relative" @click.stop>
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-brand-light text-brand flex items-center justify-center">
                            <i class="ph ph-airplane-tilt text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 leading-tight">Ajukan Permohonan Cuti</h3>
                            <p class="text-xs text-gray-500">Sisa kuota tahunan: <strong class="text-brand font-bold">{{ auth()->user()->annual_leave_quota }} hari</strong></p>
                        </div>
                    </div>
                    <button type="button" @click="showModal = false" class="text-gray-400 hover:text-gray-600 rounded-full p-1.5 hover:bg-gray-100">
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('employee.leaves.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Jenis Cuti</label>
                        <select name="type" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:outline-none">
                            <option value="annual">Cuti Tahunan (Mengurangi Saldo)</option>
                            <option value="sick">Cuti Sakit</option>
                            <option value="unpaid">Cuti Tanpa Gaji (Unpaid Leave)</option>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block mb-1">Mulai Tanggal</label>
                            <input type="date" name="start_date" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block mb-1">Sampai Tanggal</label>
                            <input type="date" name="end_date" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Alasan Cuti</label>
                        <textarea name="reason" required rows="3" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:outline-none" placeholder="Jelaskan alasan permohonan cuti..."></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Batal</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-brand-600 shadow-md transition-all">Kirim Permohonan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>