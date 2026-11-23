<x-layouts.app title="Approval Cuti">
    <div x-data="{ rejectModal: false, selectedLeave: null, rejectReason: '' }">
        <x-page-header title="Approval Cuti" subtitle="Tinjau dan setujui pengajuan cuti karyawan." />

        <div class="space-y-3">
            @forelse($pending as $leave)
                <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2">
                                <p class="font-semibold text-gray-900 text-base">{{ $leave->user->name }}</p>
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold uppercase
                                    {{ $leave->type === 'annual' ? 'bg-blue-50 text-blue-700' : ($leave->type === 'sick' ? 'bg-orange-50 text-orange-700' : 'bg-red-50 text-red-700') }}">
                                    {{ $leave->type }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">
                                <i class="ph ph-calendar"></i> {{ $leave->start_date->format('d M Y') }} s/d {{ $leave->end_date->format('d M Y') }} 
                                <span class="font-bold text-gray-900">({{ $leave->total_days }} hari)</span>
                            </p>
                            <p class="text-sm text-gray-600 mt-2 italic bg-gray-50 p-2.5 rounded-xl border border-gray-100">"{{ $leave->reason }}"</p>
                        </div>
                        <div class="flex items-center gap-2 flex-shrink-0">
                            <button type="button"
                                    onclick="confirmModal({
                                        title: 'Setujui Permohonan Cuti?',
                                        text: 'Permohonan cuti atas nama <strong>{{ addslashes($leave->user->name) }}</strong> selama {{ $leave->total_days }} hari ({{ strtoupper($leave->type) }}) akan disetujui.',
                                        icon: 'question',
                                        confirmText: 'Ya, Setujui',
                                        cancelText: 'Batal',
                                        confirmColor: '#16A34A',
                                        formId: 'approve-leave-{{ $leave->id }}'
                                    })"
                                    class="px-4 py-2 rounded-xl bg-green-600 text-white text-sm font-medium hover:bg-green-700 flex items-center gap-1.5 shadow-sm transition-all">
                                <i class="ph ph-check text-base"></i> Setujui
                            </button>
                            <form id="approve-leave-{{ $leave->id }}" method="POST" action="{{ route('supervisor.leave.approve', $leave) }}" class="hidden">
                                @csrf
                            </form>

                            <button type="button" 
                                    @click="selectedLeave = {{ json_encode(['id' => $leave->id, 'name' => $leave->user->name, 'days' => $leave->total_days, 'type' => $leave->type]) }}; rejectReason = ''; rejectModal = true"
                                    class="px-4 py-2 rounded-xl bg-red-50 text-red-600 text-sm font-medium hover:bg-red-100 flex items-center gap-1.5 transition-colors">
                                <i class="ph ph-x text-base"></i> Tolak
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-2xl border border-gray-100 p-6">
                    <x-empty-state title="Tidak ada permohonan cuti" description="Semua pengajuan cuti sudah diproses." icon="ph-check-circle" />
                </div>
            @endforelse
        </div>

        {{-- Modal Tolak Cuti --}}
        <div x-show="rejectModal" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="rejectModal = false">
            <div class="bg-white rounded-3xl p-6 sm:p-8 w-full max-w-md shadow-2xl relative" @click.stop>
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-red-50 text-red-600 flex items-center justify-center">
                            <i class="ph ph-x-circle text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 leading-tight">Tolak Permohonan Cuti</h3>
                            <p class="text-xs text-gray-500" x-text="selectedLeave ? `${selectedLeave.name} (${selectedLeave.days} hari - ${selectedLeave.type})` : ''"></p>
                        </div>
                    </div>
                    <button type="button" @click="rejectModal = false" class="text-gray-400 hover:text-gray-600 rounded-full p-1.5 hover:bg-gray-100">
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                <form :action="selectedLeave ? `/supervisor/leave/${selectedLeave.id}/reject` : '#'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Alasan Penolakan (Wajib)</label>
                        <textarea name="approval_note" x-model="rejectReason" required rows="3" 
                                  class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-red-500 focus:outline-none" 
                                  placeholder="Contoh: Jadwal bertabrakan dengan agenda kantor penting..."></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                        <button type="button" @click="rejectModal = false" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Batal</button>
                        <button type="submit" :disabled="!rejectReason.trim()" class="px-6 py-2.5 rounded-xl bg-red-600 text-white text-sm font-semibold hover:bg-red-700 shadow-md transition-all disabled:opacity-50">Tolak Permohonan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>