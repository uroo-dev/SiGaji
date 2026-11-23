<x-layouts.app title="Pengaturan Kantor">
    <x-page-header title="Pengaturan Kantor" subtitle="Kelola titik koordinat kantor pada peta, radius geofencing, dan parameter tarif payroll." />

    {{-- Leaflet Map Assets --}}
    @push('scripts')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    @endpush

    <div class="max-w-4xl bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8"
         x-data="settingsMap({
             lat: {{ old('office_latitude', $settings['office_latitude']) }},
             lng: {{ old('office_longitude', $settings['office_longitude']) }},
             radius: {{ old('max_radius_meters', $settings['max_radius_meters']) }}
         })"
         x-init="initMap()">

        <form id="settings-form" action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            <div>
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-2">
                    <div>
                        <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                            <i class="ph ph-map-pin text-brand text-xl"></i> Peta Lokasi & Geofencing Kantor
                        </h3>
                        <p class="text-xs text-gray-500">Klik langsung pada peta atau seret pin untuk menentukan titik koordinat kantor.</p>
                    </div>
                    <button type="button" @click="detectCurrentLocation()" 
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-brand/30 bg-brand-light text-brand text-xs font-semibold hover:bg-brand hover:text-white transition-all shadow-sm">
                        <i class="ph ph-crosshair text-sm"></i> Deteksi Lokasi Saya
                    </button>
                </div>

                {{-- Interactive Map Container --}}
                <div class="rounded-2xl overflow-hidden border border-gray-200 mt-3 shadow-inner relative z-0">
                    <div id="office-map" class="w-full h-80 bg-gray-100"></div>
                </div>
                <p class="text-[11px] text-gray-400 mt-1.5 flex items-center gap-1">
                    <i class="ph ph-info text-xs"></i> Lingkaran oranye menandakan area radius toleransi absensi karyawan (maksimal <span x-text="radius"></span> meter).
                </p>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Latitude Kantor</label>
                        <input type="text" name="office_latitude" id="office_latitude" x-model="lat" required
                               @input="updateMarkerFromInputs()"
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-mono focus:ring-1 focus:ring-brand focus:outline-none">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Longitude Kantor</label>
                        <input type="text" name="office_longitude" id="office_longitude" x-model="lng" required
                               @input="updateMarkerFromInputs()"
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-mono focus:ring-1 focus:ring-brand focus:outline-none">
                    </div>
                </div>

                <div class="mt-4">
                    <label class="text-xs font-semibold text-gray-700 block mb-1">Radius Toleransi Maksimal (Meter)</label>
                    <div class="flex items-center gap-3">
                        <input type="number" name="max_radius_meters" id="max_radius_meters" x-model.number="radius" required min="10" max="5000"
                               @input="updateCircleRadius()"
                               class="w-full max-w-xs border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-mono focus:ring-1 focus:ring-brand focus:outline-none">
                        <span class="text-xs text-gray-400">Rekomendasi: 50 s/d 150 meter</span>
                    </div>
                </div>
            </div>

            <hr class="border-gray-100">

            <div>
                <h3 class="text-base font-bold text-gray-900 mb-1 flex items-center gap-2">
                    <i class="ph ph-money text-brand text-xl"></i> Parameter Denda & Upah Lembur
                </h3>
                <p class="text-xs text-gray-500 mb-4">Nilai ini digunakan secara otomatis oleh engine perhitungan payroll bulanan.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Denda Terlambat per Menit (Rp)</label>
                        <input type="number" name="late_deduction_per_minute" value="{{ old('late_deduction_per_minute', $settings['late_deduction_per_minute']) }}" required min="0"
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-mono focus:ring-1 focus:ring-brand focus:outline-none">
                        <p class="text-[11px] text-gray-400 mt-1">Contoh: Rp 1.000 / menit keterlambatan.</p>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Tarif Lembur per Jam (Rp)</label>
                        <input type="number" name="overtime_hourly_rate" value="{{ old('overtime_hourly_rate', $settings['overtime_hourly_rate']) }}" min="0"
                               class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm font-mono focus:ring-1 focus:ring-brand focus:outline-none">
                        <p class="text-[11px] text-gray-400 mt-1">Contoh: Rp 25.000 / jam lembur.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4 border-t border-gray-100">
                <button type="button" 
                        onclick="confirmModal({
                            title: 'Simpan Perubahan Pengaturan?',
                            text: 'Koordinat lokasi kantor, radius geofencing, dan tarif perhitungan baru akan langsung diterapkan ke seluruh sistem.',
                            icon: 'question',
                            confirmText: 'Ya, Simpan',
                            cancelText: 'Batal',
                            confirmColor: '#FF5A36',
                            formId: 'settings-form'
                        })"
                        class="bg-brand text-white px-8 py-2.5 rounded-xl text-sm font-semibold hover:bg-brand-600 transition-all shadow-md">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
    function settingsMap(initialConfig) {
        return {
            lat: initialConfig.lat,
            lng: initialConfig.lng,
            radius: initialConfig.radius,
            map: null,
            marker: null,
            circle: null,

            initMap() {
                this.$nextTick(() => {
                    if (typeof L === 'undefined') {
                        setTimeout(() => this.initMap(), 200);
                        return;
                    }

                    const initialCoords = [this.lat, this.lng];

                    this.map = L.map('office-map').setView(initialCoords, 16);

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
                        maxZoom: 19
                    }).addTo(this.map);

                    // Marker
                    this.marker = L.marker(initialCoords, { draggable: true }).addTo(this.map);

                    // Circle Geofence
                    this.circle = L.circle(initialCoords, {
                        color: '#FF5A36',
                        fillColor: '#FF5A36',
                        fillOpacity: 0.18,
                        radius: this.radius
                    }).addTo(this.map);

                    // Drag marker event
                    this.marker.on('dragend', (e) => {
                        const position = e.target.getLatLng();
                        this.updateCoordinates(position.lat, position.lng);
                    });

                    // Click on map event
                    this.map.on('click', (e) => {
                        this.updateCoordinates(e.latlng.lat, e.latlng.lng);
                    });

                    setTimeout(() => { this.map.invalidateSize(); }, 300);
                });
            },

            updateCoordinates(newLat, newLng) {
                this.lat = parseFloat(newLat).toFixed(6);
                this.lng = parseFloat(newLng).toFixed(6);
                const latLng = [this.lat, this.lng];
                if (this.marker) this.marker.setLatLng(latLng);
                if (this.circle) this.circle.setLatLng(latLng);
                if (this.map) this.map.panTo(latLng);
            },

            updateMarkerFromInputs() {
                const parsedLat = parseFloat(this.lat);
                const parsedLng = parseFloat(this.lng);
                if (!isNaN(parsedLat) && !isNaN(parsedLng)) {
                    this.updateCoordinates(parsedLat, parsedLng);
                }
            },

            updateCircleRadius() {
                const r = parseInt(this.radius);
                if (!isNaN(r) && this.circle) {
                    this.circle.setRadius(r);
                }
            },

            detectCurrentLocation() {
                if (!navigator.geolocation) {
                    Swal.fire({ icon: 'warning', title: 'GPS Tidak Didukung', text: 'Peramban Anda tidak mendukung fitur geolocation.' });
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (pos) => {
                        this.updateCoordinates(pos.coords.latitude, pos.coords.longitude);
                        this.map.setView([pos.coords.latitude, pos.coords.longitude], 17);
                        if (window.Toast) {
                            Toast.fire({ icon: 'success', title: 'Lokasi berhasil diarahkan ke GPS saat ini.' });
                        }
                    },
                    (err) => {
                        Swal.fire({ icon: 'error', title: 'Gagal Mendeteksi Lokasi', text: 'Harap izinkan akses lokasi di pengaturan browser.' });
                    },
                    { enableHighAccuracy: true, timeout: 8000 }
                );
            }
        };
    }
    </script>
    @endpush
</x-layouts.app>
