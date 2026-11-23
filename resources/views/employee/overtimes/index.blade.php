<x-layouts.app title="Pengajuan Lembur">
    <x-page-header title="Pengajuan Lembur" subtitle="Daftar dan formulir pengajuan jam kerja lembur Anda.">
        <x-slot:actions>
            <button @click="showModal = true" class="bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 flex items-center gap-2 shadow-sm">
                <i class="ph ph-plus"></i> Ajukan Lembur
            </button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ showModal: false }">
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
                                    <form method="POST" action="{{ route('employee.overtimes.cancel', $ot) }}" onsubmit="return confirm('Batalkan pengajuan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-xs text-red-500 hover:underline">Batalkan</button>
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

        {{-- Modal Pengajuan Lembur --}}
        <div x-show="showModal" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showModal = false">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Ajukan Lembur</h3>
                <p class="text-xs text-gray-500 mb-4">Pengajuan lembur akan ditinjau oleh atasan langsung.</p>
                <form action="{{ route('employee.overtimes.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Tanggal Lembur</label>
                        <input type="date" name="date" required value="{{ date('Y-m-d') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block mb-1">Jam Mulai</label>
                            <input type="time" name="start_time" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block mb-1">Jam Selesai</label>
                            <input type="time" name="end_time" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Alasan Lembur</label>
                        <textarea name="reason" required rows="3" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="Contoh: Menyiapkan laporan akhir bulan..."></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showModal = false" class="px-4 py-2 rounded-lg border border-gray-200 text-sm hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand text-white text-sm font-medium hover:bg-brand-600">Ajukan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
