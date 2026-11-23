<x-layouts.app title="Pengajuan Lembur">
    <div x-data="{ showModal: false }">
        <x-page-header title="Pengajuan Lembur" subtitle="Daftar dan formulir pengajuan jam kerja lembur Anda.">
            <x-slot:actions>
                <button type="button" @click="showModal = true" class="bg-brand text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-brand-600 flex items-center gap-2 shadow-sm transition-all">
                    <i class="ph ph-plus text-base"></i> Ajukan Lembur
                </button>
            </x-slot:actions>
        </x-page-header>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="text-left px-4 py-3">Tanggal</th>
                            <th class="text-left px-4 py-3">Jam Lembur</th>
                            <th class="text-left px-4 py-3">Total Durasi</th>
                            <th class="text-left px-4 py-3">Alasan</th>
                            <th class="text-center px-4 py-3">Status</th>
                            <th class="text-right px-4 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($overtimes as $ot)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 font-medium text-gray-900">{{ $ot->date->format('d M Y') }}</td>
                                <td class="px-4 py-3 font-mono text-gray-600">{{ $ot->start_time }} - {{ $ot->end_time }}</td>
                                <td class="px-4 py-3 font-semibold text-gray-800">{{ $ot->hours }} Jam</td>
                                <td class="px-4 py-3 text-gray-600 max-w-xs truncate">{{ $ot->reason }}</td>
                                <td class="px-4 py-3 text-center"><x-status-badge :status="$ot->status" /></td>
                                <td class="px-4 py-3 text-right">
                                    @if($ot->status === 'pending')
                                        <button type="button" 
                                                onclick="confirmModal({
                                                    title: 'Batalkan Pengajuan Lembur?',
                                                    text: 'Pengajuan lembur tanggal {{ $ot->date->format('d M Y') }} akan dibatalkan.',
                                                    icon: 'warning',
                                                    confirmText: 'Ya, Batalkan',
                                                    cancelText: 'Kembali',
                                                    confirmColor: '#EF4444',
                                                    formId: 'cancel-ot-{{ $ot->id }}'
                                                })"
                                                class="text-xs text-red-500 hover:text-red-700 hover:underline font-semibold">
                                            Batalkan
                                        </button>
                                        <form id="cancel-ot-{{ $ot->id }}" method="POST" action="{{ route('employee.overtimes.cancel', $ot) }}" class="hidden">
                                            @csrf
                                            @method('DELETE')
                                        </form>
                                    @else
                                        <span class="text-xs text-gray-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6"><x-empty-state title="Belum ada pengajuan lembur" description="Gunakan tombol Ajukan Lembur untuk mengajukan jadwal lembur." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if(method_exists($overtimes, 'hasPages') && $overtimes->hasPages())
                <div class="p-4 border-t border-gray-100">
                    {{ $overtimes->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Pengajuan Lembur --}}
        <div x-show="showModal" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showModal = false">
            <div class="bg-white rounded-3xl p-6 sm:p-8 w-full max-w-md shadow-2xl relative" @click.stop>
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-brand-light text-brand flex items-center justify-center">
                            <i class="ph ph-hourglass-high text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 leading-tight">Ajukan Lembur</h3>
                            <p class="text-xs text-gray-500">Pengajuan akan ditinjau langsung oleh Supervisor.</p>
                        </div>
                    </div>
                    <button type="button" @click="showModal = false" class="text-gray-400 hover:text-gray-600 rounded-full p-1.5 hover:bg-gray-100">
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('employee.overtimes.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Tanggal Lembur</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block mb-1">Jam Mulai</label>
                            <input type="time" name="start_time" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:outline-none">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block mb-1">Jam Selesai</label>
                            <input type="time" name="end_time" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:outline-none">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Alasan Lembur</label>
                        <textarea name="reason" required rows="3" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:outline-none" placeholder="Contoh: Menyiapkan laporan akhir bulan..."></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Batal</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-brand-600 shadow-md transition-all">Ajukan Lembur</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>