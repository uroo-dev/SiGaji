<x-layouts.app title="Pengaturan Kantor">
    <x-page-header title="Pengaturan Kantor" subtitle="Kelola koordinat lokasi, radius geofencing, dan tarif perhitungan gaji." />

    <div class="max-w-3xl bg-white rounded-2xl border border-gray-100 shadow-sm p-6 sm:p-8">
        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <h3 class="text-base font-semibold text-gray-900 mb-1 flex items-center gap-2">
                    <i class="ph ph-map-pin text-brand text-lg"></i> Lokasi & Radius Kantor
                </h3>
                <p class="text-xs text-gray-500 mb-4">Karyawan hanya dapat melakukan clock-in jika berada di dalam radius ini.</p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Latitude Kantor</label>
                        <input type="text" name="office_latitude" value="{{ old('office_latitude', $settings['office_latitude']) }}" required
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:ring-1 focus:ring-brand">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Longitude Kantor</label>
                        <input type="text" name="office_longitude" value="{{ old('office_longitude', $settings['office_longitude']) }}" required
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:ring-1 focus:ring-brand">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="text-xs font-semibold text-gray-700 block mb-1">Radius Toleransi Maksimal (Meter)</label>
                    <input type="number" name="max_radius_meters" value="{{ old('max_radius_meters', $settings['max_radius_meters']) }}" required min="10"
                           class="w-full max-w-xs border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:ring-1 focus:ring-brand">
                    <p class="text-[11px] text-gray-400 mt-1">Disarankan: 50 s/d 150 meter.</p>
                </div>
            </div>

            <hr class="border-gray-100">

            <div>
                <h3 class="text-base font-semibold text-gray-900 mb-1 flex items-center gap-2">
                    <i class="ph ph-money text-brand text-lg"></i> Parameter Denda & Lembur
                </h3>
                <p class="text-xs text-gray-500 mb-4">Nilai ini digunakan secara otomatis oleh engine perhitungan payroll bulanan.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Denda Terlambat per Menit (Rp)</label>
                        <input type="number" name="late_deduction_per_minute" value="{{ old('late_deduction_per_minute', $settings['late_deduction_per_minute']) }}" required min="0"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:ring-1 focus:ring-brand">
                        <p class="text-[11px] text-gray-400 mt-1">Contoh: Rp 1.000 / menit keterlambatan.</p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Tarif Lembur per Jam (Rp)</label>
                        <input type="number" name="overtime_hourly_rate" value="{{ old('overtime_hourly_rate', $settings['overtime_hourly_rate']) }}" min="0"
                               class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm font-mono focus:ring-1 focus:ring-brand">
                        <p class="text-[11px] text-gray-400 mt-1">Contoh: Rp 25.000 / jam lembur.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="bg-brand text-white px-6 py-2.5 rounded-lg text-sm font-medium hover:bg-brand-600 transition-colors shadow-sm">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
