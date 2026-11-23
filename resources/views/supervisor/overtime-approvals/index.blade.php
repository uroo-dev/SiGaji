<x-layouts.app title="Approval Lembur">
    @php
        $routePrefix = auth()->user()?->role?->value === 'admin' ? 'admin' : 'supervisor';
    @endphp
    <div x-data="{ rejectModal: false, selectedOt: null, rejectReason: '' }">
        <x-page-header title="Approval Lembur" subtitle="Tinjau dan setujui pengajuan lembur karyawan." />

        <div class="space-y-3">
            @forelse($pending as $ot)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <p class="font-semibold text-gray-900 text-base">{{ $ot->user->name }}</p>
                            <p class="text-sm text-gray-500 mt-1">
                                <i class="ph ph-calendar"></i> {{ $ot->date->format('d M Y') }} — 
                                <span class="font-mono">{{ $ot->start_time }} s/d {{ $ot->end_time }}</span> 
                                <span class="font-bold text-gray-900">({{ $ot->hours }} jam)</span>
                            </p>
                            <p class="text-sm text-gray-600 mt-2 italic bg-gray-50 p-2.5 rounded-xl border border-gray-100">"{{ $ot->reason }}"</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button type="button"
                                    onclick="confirmModal({
                                        title: 'Setujui Pengajuan Lembur?',
                                        text: 'Pengajuan lembur atas nama <strong>{{ addslashes($ot->user->name) }}</strong> selama {{ $ot->hours }} jam akan disetujui dan dihitung pada penggajian bulanan.',
                                        icon: 'question',
                                        confirmText: 'Ya, Setujui',
                                        cancelText: 'Batal',
                                        confirmColor: '#16A34A',
                                        formId: 'approve-ot-{{ $ot->id }}'
                                    })"
                                    class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-medium hover:bg-green-700 flex items-center gap-1.5 shadow-sm transition-all">
                                <i class="ph ph-check text-base"></i> Setujui
                            </button>
                            <form id="approve-ot-{{ $ot->id }}" method="POST" action="{{ route($routePrefix . '.overtime.approve', $ot) }}" class="hidden">
                                @csrf
                            </form>

                            <button type="button" 
                                    @click="selectedOt = {{ json_encode(['id' => $ot->id, 'name' => $ot->user->name, 'hours' => $ot->hours, 'date' => $ot->date->format('d M Y')]) }}; rejectReason = ''; rejectModal = true"
                                    class="px-4 py-2 rounded-xl bg-red-50 text-red-600 text-sm font-medium hover:bg-red-100 flex items-center gap-1.5 transition-colors">
                                <i class="ph ph-x text-base"></i> Tolak
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <x-empty-state title="Tidak ada pengajuan" description="Semua pengajuan lembur sudah diproses." icon="ph-check-circle" />
                </div>
            @endforelse
        </div>

        {{-- Modal Tolak Lembur --}}
        <div x-show="rejectModal" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="rejectModal = false">
            <div class="bg-white rounded-3xl p-6 sm:p-8 w-full max-w-md shadow-2xl relative" @click.stop>
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center">
                            <i class="ph ph-x-circle text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 leading-tight">Tolak Pengajuan Lembur</h3>
                            <p class="text-xs text-gray-500" x-text="selectedOt ? `${selectedOt.name} (${selectedOt.hours} jam - ${selectedOt.date})` : ''"></p>
                        </div>
                    </div>
                    <button type="button" @click="rejectModal = false" class="text-gray-400 hover:text-gray-600 rounded-full p-1.5 hover:bg-gray-100">
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                <form :action="selectedOt ? `/${'{{ $routePrefix }}'}/overtime-approvals/${selectedOt.id}/reject` : '#'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Alasan Penolakan (Wajib)</label>
                        <textarea name="approval_note" x-model="rejectReason" required rows="3" 
                                  class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-red-500 focus:outline-none" 
                                  placeholder="Contoh: Beban kerja lembur belum mendesak / alasan lainnya..."></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                        <button type="button" @click="rejectModal = false" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Batal</button>
                        <button type="submit" :disabled="!rejectReason.trim()" class="px-6 py-2.5 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 shadow-md transition-all disabled:opacity-50">Tolak Pengajuan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>