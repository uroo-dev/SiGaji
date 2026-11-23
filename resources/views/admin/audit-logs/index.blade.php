<x-layouts.app title="Audit Log">
    <x-page-header title="Audit Log" subtitle="Rekam jejak aktivitas krusial, mutasi data, dan keamanan sistem." />

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Waktu</th>
                        <th class="text-left px-4 py-3">Pengguna</th>
                        <th class="text-left px-4 py-3">Aksi</th>
                        <th class="text-left px-4 py-3">Model</th>
                        <th class="text-left px-4 py-3">IP Address</th>
                        <th class="text-left px-4 py-3">Rincian Perubahan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50 text-xs">
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-4 py-3 font-mono font-semibold text-brand">{{ $log->action }}</td>
                            <td class="px-4 py-3 text-gray-600 font-mono text-[11px]">{{ class_basename($log->model_type) }} #{{ $log->model_id }}</td>
                            <td class="px-4 py-3 text-gray-500 font-mono">{{ $log->ip_address ?? '-' }}</td>
                            <td class="px-4 py-3">
                                @if($log->new_values)
                                    <pre class="bg-gray-50 text-[10px] p-2 rounded-lg max-w-xs overflow-x-auto">{{ json_encode($log->new_values, JSON_PRETTY_PRINT) }}</pre>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-empty-state title="Belum ada log" description="Aktivitas audit sistem akan tercatat di sini." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</x-layouts.app>
