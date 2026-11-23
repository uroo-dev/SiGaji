<x-layouts.app title="Audit Log">
    <x-page-header title="Audit Log" subtitle="Rekam jejak seluruh aktivitas pengguna, mutasi data, dan keamanan sistem." />

    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Waktu</th>
                        <th class="text-left px-4 py-3">Pengguna</th>
                        <th class="text-left px-4 py-3">Aksi</th>
                        <th class="text-left px-4 py-3">Objek / Model</th>
                        <th class="text-left px-4 py-3">IP Address</th>
                        <th class="text-left px-4 py-3">Waktu Eksekusi / Detail</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        @php
                            $values = $log->new_values ?? [];
                            $dt = $values['time'] ?? $values['finalized_at'] ?? $values['updated_at'] ?? $values['created_at'] ?? null;
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

                            // Color coding per kategori aksi
                            $actionColor = match(true) {
                                str_starts_with($log->action, 'auth.') => 'bg-blue-50 text-blue-700 border-blue-200',
                                str_starts_with($log->action, 'attendance.') => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                str_starts_with($log->action, 'overtime.') => 'bg-amber-50 text-amber-700 border-amber-200',
                                str_starts_with($log->action, 'leave.') => 'bg-purple-50 text-purple-700 border-purple-200',
                                str_starts_with($log->action, 'payroll.') || str_starts_with($log->action, 'payslip.') => 'bg-green-50 text-green-700 border-green-200',
                                str_starts_with($log->action, 'employee.') => 'bg-rose-50 text-rose-700 border-rose-200',
                                default => 'bg-gray-100 text-gray-700 border-gray-200',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 text-xs">
                            <td class="px-4 py-3.5 text-gray-500 whitespace-nowrap font-mono">{{ $log->created_at->format('d M Y H:i:s') }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($log->user?->name ?? 'System') }}&background=FFE5DB&color=FF5A36" class="w-6 h-6 rounded-full flex-shrink-0">
                                    <div>
                                        <p class="font-semibold text-gray-900 leading-tight">{{ $log->user?->name ?? 'System' }}</p>
                                        <p class="text-[10px] text-gray-400">{{ $log->user?->role?->label() ?? 'System' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3.5">
                                <span class="font-mono font-semibold px-2.5 py-1 rounded-full text-[11px] border {{ $actionColor }} whitespace-nowrap">
                                    {{ $log->action }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-gray-600 font-mono text-[11px]">
                                {{ class_basename($log->model_type) }} #{{ $log->model_id }}
                            </td>
                            <td class="px-4 py-3.5 text-gray-500 font-mono">{{ $log->ip_address ?? '-' }}</td>
                            <td class="px-4 py-3.5">
                                <div class="flex items-center gap-2" x-data="{ openDetail: false }">
                                    <span class="font-mono text-xs text-gray-700 bg-gray-50 border border-gray-200 px-2.5 py-1 rounded-lg whitespace-nowrap">
                                        {{ $displayTime }}
                                    </span>
                                    @if(!empty($log->new_values))
                                        <button type="button" @click="openDetail = true" class="text-gray-400 hover:text-brand transition-colors p-1" title="Lihat Payload Data Lengkap">
                                            <i class="ph ph-info text-base"></i>
                                        </button>
                                        {{-- Modal Detail JSON --}}
                                        <div x-show="openDetail" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="openDetail = false">
                                            <div class="bg-white rounded-3xl p-6 sm:p-7 w-full max-w-md shadow-2xl relative text-left" @click.stop>
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
