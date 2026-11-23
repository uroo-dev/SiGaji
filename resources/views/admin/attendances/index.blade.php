<x-layouts.app title="Absensi Karyawan">
    <x-page-header title="Absensi Karyawan" subtitle="Rekap dan koreksi kehadiran karyawan harian.">
        <x-slot:actions>
            <form method="GET" class="flex gap-2">
                <input type="date" name="date" value="{{ $date }}" class="border border-gray-200 rounded-lg px-3 py-2 text-sm bg-white">
                <button class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">Filter</button>
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
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($attendance->user->name) }}&background=F3F4F6&color=374151" class="w-8 h-8 rounded-full">
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
                                <button @click="selected = {{ json_encode($attendance) }}; modal = true" 
                                        class="text-gray-400 hover:text-brand transition-colors p-1" title="Koreksi Absensi">
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
        <div x-show="modal" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="modal = false">
            <div class="bg-white rounded-2xl p-6 w-full max-w-md shadow-2xl">
                <h3 class="text-lg font-bold mb-1 text-gray-900">Koreksi Absensi</h3>
                <p class="text-sm text-gray-500 mb-4" x-text="selected?.user?.name || 'Karyawan'"></p>
                <form :action="`/admin/attendances/${selected?.id}/correct`" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="text-xs font-semibold text-gray-600 block mb-1">Jam Masuk</label>
                        <input type="time" name="clock_in" :value="selected?.clock_in" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    </div>
                    
                    <div class="mb-3">
                        <label class="text-xs font-semibold text-gray-600 block mb-1">Jam Pulang</label>
                        <input type="time" name="clock_out" :value="selected?.clock_out" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                    </div>
                    
                    <div class="mb-4">
                        <label class="text-xs font-semibold text-gray-600 block mb-1">Alasan Koreksi (Wajib)</label>
                        <textarea name="correction_reason" rows="3" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="Contoh: Terkendala sinyal/kamera, hadir tepat waktu..."></textarea>
                    </div>

                    <div class="flex justify-end gap-2">
                        <button type="button" @click="modal = false" class="px-4 py-2 rounded-lg border border-gray-200 text-sm hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand text-white text-sm font-medium hover:bg-brand-600">Simpan Koreksi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
