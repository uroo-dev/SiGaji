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
                        @php
                            $values = $log->new_values ?? [];
                            $dt = $values['finalized_at'] ?? $values['updated_at'] ?? $values['created_at'] ?? null;
                            if ($dt) {
                                try {
                                    $displayTime = \Carbon\Carbon::parse($dt)->format('d M Y H:i:s');
                                } catch (\Exception $e) {
                                    $displayTime = $dt;
                                }
                            } elseif (isset($values['clock_in'])) {
                                $displayTime = $values['clock_in'];
                            } elseif (isset($values['value'])) {
                                $displayTime = $values['value'];
                            } else {
                                $displayTime = $log->created_at?->format('d M Y H:i:s') ?? '-';
                            }
                        @endphp
                        <tr class="hover:bg-gray-50 text-xs">
                            <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                            <td class="px-4 py-3 font-medium text-gray-900">{{ $log->user?->name ?? 'System' }}</td>
                            <td class="px-4 py-3">
                                <span class="font-mono font-semibold px-2 py-0.5 rounded-full text-[11px] bg-brand-light text-brand">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600 font-mono text-[11px]">{{ class_basename($log->model_type) }} #{{ $log->model_id }}</td>
                            <td class="px-4 py-3 text-gray-500 font-mono">{{ $log->ip_address ?? '-' }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2" x-data="{ openDetail: false }">
                                    <span class="font-mono text-xs text-gray-700 bg-gray-50 border border-gray-200 px-2.5 py-1 rounded-lg whitespace-nowrap">
                                        {{ $displayTime }}
                                    </span>
                                    @if(!empty($log->new_values))
                                        <button type="button" @click="openDetail = true" class="text-gray-400 hover:text-brand transition-colors p-1" title="Lihat JSON Lengkap">
                                            <i class="ph ph-info text-base"></i>
                                        </button>
                                        {{-- Modal Detail JSON --}}
                                        <div x-show="openDetail" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="openDetail = false">
                                            <div class="bg-white rounded-3xl p-6 w-full max-w-md shadow-2xl relative text-left" @click.stop>
                                                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-3">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-8 h-8 rounded-xl bg-brand-light text-brand flex items-center justify-center">
                                                            <i class="ph ph-file-code text-lg"></i>
                                                        </div>
                                                        <h4 class="font-bold text-gray-900 text-sm">Rincian Data Log ({{ $log->action }})</h4>
                                                    </div>
                                                    <button type="button" @click="openDetail = false" class="text-gray-400 hover:text-gray-600 rounded-full p-1.5 hover:bg-gray-100">
                                                        <i class="ph ph-x text-base"></i>
                                                    </button>
                                                </div>
                                                <pre class="bg-gray-50 text-[11px] font-mono p-3.5 rounded-2xl border border-gray-100 max-h-60 overflow-auto text-gray-800">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                            </div>
                                        </div>
                                    @endif
                                </div>
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