<x-layouts.app title="Absensi Karyawan">
    <x-page-header title="Absensi Karyawan" subtitle="Rekap dan koreksi kehadiran karyawan harian.">
        <x-slot:actions>
            <form method="GET" class="flex gap-2">
                <input type="date" name="date" value="{{ $date }}" class="border border-gray-200 rounded-xl px-3 py-2 text-sm bg-white focus:outline-none focus:ring-1 focus:ring-brand">
                <button class="bg-gray-900 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors">Filter</button>
            </form>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ modal: false, selected: {} }">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Karyawan</th>
                        <th class="text-left px-4 py-3">Tanggal</th>
                        <th class="text-left px-4 py-3">Masuk</th>
                        <th class="text-left px-4 py-3">Pulang</th>
                        <th class="text-left px-4 py-3">Terlambat</th>
                        <th class="text-left px-4 py-3">Jarak</th>
                        <th class="text-left px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($attendances as $attendance)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($attendance->user->name) }}&background=FFE5DB&color=FF5A36" class="w-8 h-8 rounded-full">
                                    <span class="font-medium text-gray-900">{{ $attendance->user->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $attendance->date?->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-mono">{{ $attendance->clock_in ?? '-' }}</td>
                            <td class="px-4 py-3 font-mono">{{ $attendance->clock_out ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs {{ $attendance->late_minutes > 0 ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                {{ $attendance->late_minutes > 0 ? $attendance->late_minutes . ' mnt' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $attendance->distance_from_office ? $attendance->distance_from_office . 'm' : '-' }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$attendance->status" /></td>
                            <td class="px-4 py-3 text-right">
                                <button type="button" @click="selected = {{ json_encode($attendance) }}; modal = true" 
                                        class="text-gray-400 hover:text-brand transition-colors p-1.5 rounded-lg hover:bg-brand-50" title="Koreksi Absensi">
                                    <i class="ph ph-pencil-simple text-lg"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-empty-state title="Tidak ada data" description="Tidak ada data absensi pada tanggal ini." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Modal Koreksi --}}
        <div x-show="modal" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="modal = false">
            <div class="bg-white rounded-3xl p-6 sm:p-8 w-full max-w-md shadow-2xl relative" @click.stop>
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-brand-light text-brand flex items-center justify-center">
                            <i class="ph ph-pencil-simple text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 leading-tight">Koreksi Absensi</h3>
                            <p class="text-xs text-gray-500" x-text="selected?.user?.name || 'Karyawan'"></p>
                        </div>
                    </div>
                    <button type="button" @click="modal = false" class="text-gray-400 hover:text-gray-600 rounded-full p-1.5 hover:bg-gray-100">
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                <form :action="`/admin/attendances/${selected?.id}/correct`" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Jam Masuk</label>
                        <input type="time" name="clock_in" :value="selected?.clock_in" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:outline-none">
                    </div>
                    
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Jam Pulang</label>
                        <input type="time" name="clock_out" :value="selected?.clock_out" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:outline-none">
                    </div>
                    
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Alasan Koreksi (Wajib Dicatat ke Audit Log)</label>
                        <textarea name="correction_reason" rows="3" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:outline-none" placeholder="Contoh: Terkendala sinyal/kamera, hadir tepat waktu..."></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="modal = false" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Batal</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-brand-600 shadow-md transition-all">Simpan Koreksi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>