<x-layouts.app title="Absensi Saya">
    <x-page-header title="Absensi Saya" subtitle="Clock-in & clock-out harian dengan verifikasi kamera selfie dan GPS kantor." />

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Widget Absensi --}}
        <div class="lg:col-span-1">
            @if($today && $today->clock_out)
                <div class="bg-white border border-gray-100 rounded-3xl p-6 sm:p-8 text-center shadow-sm">
                    <div class="w-16 h-16 rounded-3xl bg-green-50 text-green-500 flex items-center justify-center mx-auto mb-4 shadow-sm">
                        <i class="ph ph-check-circle text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-1">Presensi Hari Ini Selesai</h3>
                    <p class="text-xs text-gray-500 mb-5">Anda telah menyelesaikan jam kerja hari ini.</p>
                    
                    <div class="bg-gray-50 rounded-2xl p-4 border border-gray-100 space-y-2 text-left">
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-500">Jam Masuk</span>
                            <span class="font-mono font-bold text-gray-900">{{ $today->clock_in }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-500">Jam Pulang</span>
                            <span class="font-mono font-bold text-gray-900">{{ $today->clock_out }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs border-t border-gray-200/60 pt-2">
                            <span class="text-gray-500">Status Kehadiran</span>
                            <x-status-badge :status="$today->status" />
                        </div>
                    </div>
                </div>
            @elseif($today)
                {{-- Sudah masuk, belum pulang --}}
                <div x-data="attendanceWidget('clock-out', '{{ $today->clock_in }}')" class="bg-white border border-gray-100 rounded-3xl p-6 sm:p-8 shadow-sm">
                    <div x-show="state === 'idle'" class="text-center">
                        <div class="w-16 h-16 rounded-3xl bg-brand-light text-brand flex items-center justify-center mx-auto mb-4 shadow-sm">
                            <i class="ph ph-sign-out text-3xl"></i>
                        </div>
                        <p class="text-xs text-gray-500 mb-1">Jam Masuk Anda</p>
                        <p class="text-3xl font-mono font-bold text-gray-900 mb-2">{{ $today->clock_in }}</p>
                        @if($today->late_minutes > 0)
                            <span class="inline-block bg-red-50 border border-red-200 text-red-700 px-3 py-1 rounded-full text-xs font-semibold mb-4">
                                Terlambat {{ $today->late_minutes }} menit
                            </span>
                        @else
                            <span class="inline-block bg-green-50 border border-green-200 text-green-700 px-3 py-1 rounded-full text-xs font-semibold mb-4">
                                Tepat Waktu
                            </span>
                        @endif
                        <p class="text-sm text-gray-500 mb-6">Waktunya pulang? Ambil foto selfie di area kantor untuk clock out.</p>
                        <button type="button" @click="start()" class="w-full bg-brand text-white px-6 py-3 rounded-2xl font-semibold hover:bg-brand-600 shadow-lg shadow-brand/30 transition-all flex items-center justify-center gap-2">
                            <i class="ph ph-camera text-xl"></i> Foto & Clock Out
                        </button>
                    </div>
                    @include('employee._attendance-widget-common')
                </div>
            @else
                {{-- Belum masuk --}}
                <div x-data="attendanceWidget('clock-in')" class="bg-white border border-gray-100 rounded-3xl p-6 sm:p-8 shadow-sm">
                    <div x-show="state === 'idle'" class="text-center">
                        <div class="w-16 h-16 rounded-3xl bg-brand-light text-brand flex items-center justify-center mx-auto mb-4 shadow-sm">
                            <i class="ph ph-camera text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-gray-900 mb-1">Clock In Hari Ini</h3>
                        <p class="text-xs text-gray-500 mb-6">Pastikan Anda berada di area kantor & aktifkan kamera.</p>
                        <button type="button" @click="start()" class="w-full bg-brand text-white px-6 py-3 rounded-2xl font-semibold hover:bg-brand-600 shadow-lg shadow-brand/30 transition-all flex items-center justify-center gap-2">
                            <i class="ph ph-camera text-xl"></i> Buka Kamera & Clock In
                        </button>
                    </div>
                    @include('employee._attendance-widget-common')
                </div>
            @endif
        </div>

        {{-- Riwayat Absensi --}}
        <div class="lg:col-span-2 bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8">
            <h3 class="font-bold text-gray-900 text-base mb-1">Riwayat Kehadiran (30 Hari Terakhir)</h3>
            <p class="text-xs text-gray-500 mb-5">Log presensi masuk dan pulang lengkap dengan catatan jarak lokasi.</p>

            <div class="space-y-2.5 max-h-[520px] overflow-y-auto pr-1">
                @forelse($history as $att)
                    <div class="flex items-center justify-between p-3.5 rounded-2xl hover:bg-gray-50 border border-gray-100 transition-colors">
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $att->date->isoFormat('dddd, D MMMM Y') }}</p>
                            <p class="text-xs text-gray-500 font-mono mt-0.5">
                                {{ $att->clock_in ?? '-' }} → {{ $att->clock_out ?? '...' }}
                                @if($att->distance_from_office)
                                    <span class="text-gray-400">· {{ $att->distance_from_office }}m dari kantor</span>
                                @endif
                                @if($att->late_minutes > 0)
                                    <span class="text-red-500 font-semibold">· Telat {{ $att->late_minutes }}m</span>
                                @endif
                            </p>
                        </div>
                        <x-status-badge :status="$att->status" />
                    </div>
                @empty
                    <x-empty-state title="Belum ada riwayat" description="Data kehadiran Anda akan otomatis tampil di sini setelah clock in." />
                @endforelse
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    function attendanceWidget(type, clockIn = null) {
        return {
            type, 
            state: 'idle', 
            statusMsg: '', 
            error: '', 
            photo: null,
            latitude: null, 
            longitude: null, 
            stream: null, 
            submitting: false,

            async start() {
                this.error = '';
                this.state = 'loading';
                this.statusMsg = 'Mengambil koordinat GPS Anda...';
                try {
                    const pos = await new Promise((res, rej) =>
                        navigator.geolocation.getCurrentPosition(res, rej, { enableHighAccuracy: true, timeout: 10000 })
                    );
                    this.latitude = pos.coords.latitude;
                    this.longitude = pos.coords.longitude;
                } catch (e) {
                    this.state = 'idle';
                    this.error = 'Gagal mengakses GPS. Harap izinkan akses lokasi pada browser Anda.';
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Izin Lokasi Diperlukan',
                            text: this.error,
                            confirmButtonColor: '#FF5A36',
                            customClass: { popup: 'rounded-3xl shadow-2xl font-sans' }
                        });
                    }
                    return;
                }

                this.statusMsg = 'Menghubungkan kamera selfie...';
                try {
                    this.stream = await navigator.mediaDevices.getUserMedia({ 
                        video: { 
                            facingMode: 'user',
                            width: { ideal: 640 },
                            height: { ideal: 480 }
                        } 
                    });
                    this.state = 'camera';
                    this.$nextTick(() => {
                        if (this.$refs.video) {
                            this.$refs.video.srcObject = this.stream;
                        }
                    });
                } catch (e) {
                    this.state = 'idle';
                    this.error = 'Gagal membuka kamera. Harap izinkan akses kamera di peramban Anda.';
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Izin Kamera Diperlukan',
                            text: this.error,
                            confirmButtonColor: '#FF5A36',
                            customClass: { popup: 'rounded-3xl shadow-2xl font-sans' }
                        });
                    }
                }
            },

            takePhoto() {
                const v = this.$refs.video, c = this.$refs.canvas;
                if (!v || !c) return;
                c.width = v.videoWidth || 640; 
                c.height = v.videoHeight || 480;
                c.getContext('2d').drawImage(v, 0, 0);
                this.photo = c.toDataURL('image/jpeg', 0.85);
                if (this.stream) {
                    this.stream.getTracks().forEach(t => t.stop());
                    this.stream = null;
                }
                this.state = 'review';
            },

            retake() { 
                this.photo = null; 
                this.start(); 
            },

            cancel() {
                if (this.stream) {
                    this.stream.getTracks().forEach(t => t.stop());
                    this.stream = null;
                }
                this.photo = null;
                this.state = 'idle';
            },

            handleFileUpload(e) {
                const file = e.target.files && e.target.files[0];
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.photo = event.target.result;
                    if (this.stream) {
                        this.stream.getTracks().forEach(t => t.stop());
                        this.stream = null;
                    }
                    this.state = 'review';
                };
                reader.readAsDataURL(file);
            },

            async submit() {
                this.submitting = true;
                const route = this.type === 'clock-in' 
                    ? '{{ route("employee.attendance.clock-in") }}'
                    : '{{ route("employee.attendance.clock-out") }}';
                try {
                    const res = await fetch(route, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            latitude: this.latitude, 
                            longitude: this.longitude, 
                            photo_base64: this.photo
                        })
                    });
                    if (res.ok) {
                        window.location.reload();
                    } else {
                        const data = await res.json();
                        this.error = data.message || 'Terjadi kesalahan pada presensi.';
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Presensi Ditolak',
                                text: this.error,
                                confirmButtonColor: '#FF5A36',
                                customClass: { popup: 'rounded-3xl shadow-2xl font-sans' }
                            });
                        }
                        this.submitting = false;
                    }
                } catch (e) { 
                    this.error = 'Koneksi ke server gagal. Silakan coba beberapa saat lagi.'; 
                    this.submitting = false; 
                }
            }
        }
    }
    </script>
    @endpush
</x-layouts.app>
