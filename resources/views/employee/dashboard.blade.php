<x-layouts.app title="Dashboard Karyawan">
    <x-page-header title="Halo, {{ auth()->user()->name }} 👋" 
                   subtitle="{{ now()->isoFormat('dddd, D MMMM Y') }}" />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Widget Clock-In --}}
        <div class="lg:col-span-2">
            @if($todayAttendance && !$todayAttendance->clock_out)
                {{-- Sudah Clock-In, Belum Clock-Out --}}
                <div class="bg-gradient-to-br from-brand to-brand-700 rounded-2xl p-8 text-white shadow-lg shadow-brand/20 text-center">
                    <p class="text-sm opacity-80">Anda clock-in hari ini pukul</p>
                    <p class="text-4xl font-bold font-mono my-3">{{ $todayAttendance->clock_in }}</p>
                    @if($todayAttendance->late_minutes > 0)
                        <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-medium">Terlambat {{ $todayAttendance->late_minutes }} menit</span>
                    @else
                        <span class="bg-white/20 px-3 py-1 rounded-full text-xs font-medium">Tepat Waktu</span>
                    @endif
                    <form method="POST" action="{{ route('employee.attendance.clock-out') }}" class="mt-6">
                        @csrf
                        <button class="bg-white text-brand px-6 py-2.5 rounded-full font-bold hover:bg-gray-100 shadow-md transition-colors">
                            <i class="ph ph-sign-out mr-1"></i> Clock Out Sekarang
                        </button>
                    </form>
                </div>
            @elseif($todayAttendance && $todayAttendance->clock_out)
                {{-- Sudah Selesai --}}
                <div class="bg-green-50 border border-green-200 rounded-2xl p-8 text-center">
                    <i class="ph ph-check-circle text-5xl text-green-500 mb-3"></i>
                    <h3 class="text-lg font-bold text-gray-900">Selesai Bekerja Hari Ini</h3>
                    <p class="text-sm text-gray-500">Clock-in: <span class="font-mono">{{ $todayAttendance->clock_in }}</span> • Clock-out: <span class="font-mono font-bold">{{ $todayAttendance->clock_out }}</span></p>
                </div>
            @else
                {{-- Belum Clock-In --}}
                @include('employee._clock-in-widget')
            @endif
        </div>

        {{-- Ringkasan Samping --}}
        <div class="space-y-4">
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Sisa Cuti Tahunan</p>
                <p class="text-3xl font-bold text-gray-900 mt-1">{{ $remainingLeave }} <span class="text-sm text-gray-400 font-normal">hari</span></p>
                <a href="{{ route('employee.leaves.index') }}" class="text-xs text-brand font-medium mt-2 inline-block">Ajukan permohonan cuti →</a>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-gray-100 shadow-sm">
                <p class="text-xs text-gray-500 uppercase tracking-wide">Slip Gaji Terakhir</p>
                @if($lastPayslip)
                    <p class="text-lg font-bold text-gray-900 mt-1">{{ $lastPayslip->period }}</p>
                    <p class="text-sm text-gray-500 font-mono">Rp {{ number_format($lastPayslip->net_salary, 0, ',', '.') }}</p>
                    <a href="{{ route('employee.payslips.index') }}" class="text-xs text-brand font-medium mt-2 inline-block">Lihat rincian slip →</a>
                @else
                    <p class="text-sm text-gray-400 mt-2">Belum ada slip final periode ini.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Riwayat Absensi --}}
    <div class="mt-6 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
        <h3 class="font-semibold text-gray-900 mb-4">Riwayat 7 Hari Terakhir</h3>
        <div class="space-y-2">
            @forelse($recentAttendances as $att)
                <div class="flex items-center justify-between p-3 rounded-xl hover:bg-gray-50 transition-colors">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $att->date->isoFormat('ddd, D MMMM Y') }}</p>
                        <p class="text-xs text-gray-500 font-mono">{{ $att->clock_in ?? '-' }} — {{ $att->clock_out ?? '...' }}</p>
                    </div>
                    <x-status-badge :status="$att->status" />
                </div>
            @empty
                <p class="text-xs text-gray-400 py-3 text-center">Belum ada data kehadiran minggu ini.</p>
            @endforelse
        </div>
    </div>
</x-layouts.app>
