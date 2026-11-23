<x-layouts.app title="Dashboard Supervisor">
    <x-page-header title="Dashboard Supervisor" subtitle="Kelola dan setujui permohonan lembur dan cuti tim Anda." />

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
        <x-stat-card label="Pending Lembur" 
                     value="{{ $pendingOvertimeCount ?? 0 }}" 
                     subtitle="Menunggu persetujuan Anda" 
                     icon="ph-hourglass-high" variant="highlight" />
        <x-stat-card label="Pending Cuti" 
                     value="{{ $pendingLeaveCount ?? 0 }}" 
                     subtitle="Menunggu persetujuan Anda" 
                     icon="ph-airplane-tilt" />
        <x-stat-card label="Total Tim Hadir Hari Ini" 
                     value="{{ $presentTodayCount ?? 0 }}" 
                     subtitle="Karyawan aktif masuk kerja" 
                     icon="ph-check-circle" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">Pengajuan Lembur Terbaru</h3>
                <a href="{{ route('supervisor.overtime.index') }}" class="text-xs text-brand font-medium">Lihat Semua →</a>
            </div>
            <div class="space-y-3">
                @forelse($recentOvertimes ?? [] as $ot)
                    <div class="p-3 bg-gray-50 rounded-xl flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $ot->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $ot->date->format('d M Y') }} • {{ $ot->hours }} Jam</p>
                        </div>
                        <a href="{{ route('supervisor.overtime.index') }}" class="text-xs bg-white border border-gray-200 px-3 py-1.5 rounded-lg text-gray-700 hover:border-brand">Review</a>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 py-4 text-center">Tidak ada permohonan lembur baru.</p>
                @endforelse
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold text-gray-900">Pengajuan Cuti Terbaru</h3>
                <a href="{{ route('supervisor.leave.index') }}" class="text-xs text-brand font-medium">Lihat Semua →</a>
            </div>
            <div class="space-y-3">
                @forelse($recentLeaves ?? [] as $lv)
                    <div class="p-3 bg-gray-50 rounded-xl flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $lv->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $lv->start_date->format('d M') }} s/d {{ $lv->end_date->format('d M Y') }} ({{ $lv->total_days }} Hari)</p>
                        </div>
                        <a href="{{ route('supervisor.leave.index') }}" class="text-xs bg-white border border-gray-200 px-3 py-1.5 rounded-lg text-gray-700 hover:border-brand">Review</a>
                    </div>
                @empty
                    <p class="text-xs text-gray-400 py-4 text-center">Tidak ada permohonan cuti baru.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-layouts.app>
