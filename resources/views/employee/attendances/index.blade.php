<x-layouts.app title="Riwayat Absensi">
    <x-page-header title="Riwayat Absensi" subtitle="Daftar catatan kehadiran Anda." />

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Tanggal</th>
                        <th class="text-left px-4 py-3">Jam Masuk</th>
                        <th class="text-left px-4 py-3">Jam Pulang</th>
                        <th class="text-left px-4 py-3">Terlambat</th>
                        <th class="text-left px-4 py-3">Jarak</th>
                        <th class="text-left px-4 py-3">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($attendances as $att)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $att->date->format('d M Y') }}</td>
                            <td class="px-4 py-3 font-mono">{{ $att->clock_in ?? '-' }}</td>
                            <td class="px-4 py-3 font-mono">{{ $att->clock_out ?? '-' }}</td>
                            <td class="px-4 py-3 text-xs {{ $att->late_minutes > 0 ? 'text-red-600 font-bold' : 'text-gray-500' }}">
                                {{ $att->late_minutes > 0 ? $att->late_minutes . ' menit' : '-' }}
                            </td>
                            <td class="px-4 py-3 text-xs text-gray-500">{{ $att->distance_from_office ? $att->distance_from_office . 'm' : '-' }}</td>
                            <td class="px-4 py-3"><x-status-badge :status="$att->status" /></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state title="Belum ada riwayat" description="Catatan absensi Anda akan tampil di sini." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($attendances->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
