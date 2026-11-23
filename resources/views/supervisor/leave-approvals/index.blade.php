<x-layouts.app title="Approval Cuti">
    <x-page-header title="Approval Cuti" subtitle="Tinjau dan setujui pengajuan cuti karyawan." />

    <div class="space-y-3">
        @forelse($pending as $leave)
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="font-semibold text-gray-900 text-base">{{ $leave->user->name }}</p>
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium uppercase
                                {{ $leave->type === 'annual' ? 'bg-blue-50 text-blue-700' : ($leave->type === 'sick' ? 'bg-orange-50 text-orange-700' : 'bg-red-50 text-red-700') }}">
                                {{ $leave->type }}
                            </span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            <i class="ph ph-calendar"></i> {{ $leave->start_date->format('d M Y') }} s/d {{ $leave->end_date->format('d M Y') }} 
                            <span class="font-bold text-gray-900">({{ $leave->total_days }} hari)</span>
                        </p>
                        <p class="text-sm text-gray-600 mt-2 italic bg-gray-50 p-2.5 rounded-lg border border-gray-100">"{{ $leave->reason }}"</p>
                    </div>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('supervisor.leave.approve', $leave) }}">
                            @csrf
                            <button class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700 flex items-center gap-1 shadow-sm">
                                <i class="ph ph-check"></i> Setujui
                            </button>
                        </form>
                        <button onclick="rejectLeave({{ $leave->id }})" class="px-4 py-2 rounded-lg bg-red-50 text-red-600 text-sm font-medium hover:bg-red-100 flex items-center gap-1">
                            <i class="ph ph-x"></i> Tolak
                        </button>
                    </div>
                </div>
            </div>
            {{-- Hidden form tolak --}}
            <form id="reject-leave-{{ $leave->id }}" method="POST" action="{{ route('supervisor.leave.reject', $leave) }}" class="hidden">
                @csrf
                <input type="hidden" name="approval_note" value="Ditolak oleh supervisor">
            </form>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <x-empty-state title="Tidak ada permohonan cuti" description="Semua pengajuan cuti sudah diproses." icon="ph-check-circle" />
            </div>
        @endforelse
    </div>

    @push('scripts')
    <script>
        function rejectLeave(id) {
            const note = prompt('Masukkan alasan penolakan cuti:');
            if (note) {
                const form = document.getElementById('reject-leave-' + id);
                form.querySelector('input[name="approval_note"]').value = note;
                form.submit();
            }
        }
    </script>
    @endpush
</x-layouts.app>
