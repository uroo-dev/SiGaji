<x-layouts.app title="Dashboard Admin">
    <x-page-header title="Dashboard" subtitle="Ringkasan operasional restoran Anda hari ini." />

    {{-- Stat Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card label="Total Payroll" 
                     value="Rp {{ number_format($stats['total_payroll'] ?? 0, 0, ',', '.') }}" 
                     icon="ph-money" variant="highlight" />
        <x-stat-card label="Karyawan Aktif" 
                     value="{{ $stats['total_employees'] ?? 0 }}" 
                     subtitle="Terdaftar di sistem" 
                     icon="ph-users" />
        <x-stat-card label="Hadir Hari Ini" 
                     value="{{ ($stats['present_today'] ?? 0) + ($stats['late_today'] ?? 0) }} / {{ $stats['total_employees'] ?? 0 }}" 
                     subtitle="{{ $stats['late_today'] ?? 0 }} terlambat" 
                     icon="ph-clock-user" />
        <x-stat-card label="Pending Approval" 
                     value="{{ ($stats['pending_overtime'] ?? 0) + ($stats['pending_leave'] ?? 0) }}" 
                     subtitle="Lembur & cuti" 
                     icon="ph-hourglass-high" />
    </div>

    {{-- Grid Utama --}}
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Chart --}}
        <div class="xl:col-span-2 bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-1">Tren Payroll 6 Bulan Terakhir</h3>
            <p class="text-xs text-gray-500 mb-4">Total pengeluaran gaji & lembur (dalam jutaan Rp)</p>
            <div class="h-64">
                <canvas id="payrollChart"></canvas>
            </div>
        </div>

        {{-- Quick Actions --}}
        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6">
            <h3 class="text-base font-semibold text-gray-900 mb-4">Aksi Cepat</h3>
            <div class="space-y-2">
                <a href="{{ route('admin.payroll.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 border border-gray-100 transition-colors">
                    <div class="w-9 h-9 rounded-lg bg-brand-light text-brand flex items-center justify-center">
                        <i class="ph ph-calculator text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Proses Gaji</p>
                        <p class="text-xs text-gray-500">Hitung payroll bulan ini</p>
                    </div>
                </a>
                <a href="{{ route('admin.employees.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 border border-gray-100 transition-colors">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="ph ph-user-plus text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Tambah Karyawan</p>
                        <p class="text-xs text-gray-500">Daftarkan karyawan baru</p>
                    </div>
                </a>
                <a href="{{ route('admin.attendances.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 border border-gray-100 transition-colors">
                    <div class="w-9 h-9 rounded-lg bg-green-50 text-green-600 flex items-center justify-center">
                        <i class="ph ph-clipboard-text text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Rekap Absensi</p>
                        <p class="text-xs text-gray-500">Lihat kehadiran hari ini</p>
                    </div>
                </a>
                <a href="{{ route('admin.settings.index') }}" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-50 border border-gray-100 transition-colors">
                    <div class="w-9 h-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                        <i class="ph ph-gear text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Pengaturan Kantor</p>
                        <p class="text-xs text-gray-500">Radius & koordinat kantor</p>
                    </div>
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        new Chart(document.getElementById('payrollChart').getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov'],
                datasets: [
                    { label: 'Gaji Pokok', data: [40, 42, 40, 45, 44, 48], backgroundColor: '#FF5A36', borderRadius: 6 },
                    { label: 'Lembur', data: [2, 3, 1.5, 4, 2.5, 3.5], backgroundColor: '#111111', borderRadius: 6 }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'top', align: 'end', labels: { usePointStyle: true, boxWidth: 8 } } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#F3F4F6' }, border: { display: false } },
                    x: { grid: { display: false }, border: { display: false } }
                }
            }
        });
    </script>
    @endpush
</x-layouts.app>
