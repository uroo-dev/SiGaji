<x-layouts.app title="Approval Lembur">
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
                        <p class="text-sm text-gray-600 mt-2 italic bg-gray-50 p-2.5 rounded-lg border border-gray-100">"{{ $ot->reason }}"</p>
                    </div>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('supervisor.overtime.approve', $ot) }}">
                            @csrf
                            <button class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700 flex items-center gap-1 shadow-sm">
                                <i class="ph ph-check"></i> Setujui
                            </button>
                        </form>
                        <button onclick="rejectOvertime({{ $ot->id }})" class="px-4 py-2 rounded-lg bg-red-50 text-red-600 text-sm font-medium hover:bg-red-100 flex items-center gap-1">
                            <i class="ph ph-x"></i> Tolak
                        </button>
                    </div>
                </div>
            </div>
            {{-- Hidden form tolak --}}
            <form id="reject-ot-{{ $ot->id }}" method="POST" action="{{ route('supervisor.overtime.reject', $ot) }}" class="hidden">
                @csrf
                <input type="hidden" name="approval_note" value="Ditolak oleh supervisor">
            </form>
        @empty
            <div class="bg-white rounded-2xl border border-gray-100 p-6">
                <x-empty-state title="Tidak ada pengajuan" description="Semua pengajuan lembur sudah diproses." icon="ph-check-circle" />
            </div>
        @endforelse
    </div>

    @push('scripts')
    <script>
        function rejectOvertime(id) {
            const note = prompt('Masukkan alasan penolakan:');
            if (note) {
                const form = document.getElementById('reject-ot-' + id);
                form.querySelector('input[name="approval_note"]').value = note;
                form.submit();
            }
        }
    </script>
    @endpush
</x-layouts.app>
