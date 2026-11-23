<div x-data="clockInWidget()" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-8">
    {{-- State 1: Idle --}}
    <template x-if="state === 'idle'">
        <div class="text-center">
            <div class="w-16 h-16 rounded-full bg-brand-light flex items-center justify-center mx-auto mb-4">
                <i class="ph ph-camera text-2xl text-brand"></i>
            </div>
            <h3 class="text-lg font-bold text-gray-900">Clock In Hari Ini</h3>
            <p class="text-sm text-gray-500 mt-1 mb-6">Pastikan Anda berada di area radius kantor & kamera siap aktif.</p>
            <button @click="start()" class="bg-brand text-white px-6 py-2.5 rounded-full font-medium hover:bg-brand-600 transition-colors shadow-md">
                <i class="ph ph-camera mr-1"></i> Buka Kamera & Clock In
            </button>
        </div>
    </template>

    {{-- State 2: Loading --}}
    <template x-if="state === 'loading'">
        <div class="text-center py-4">
            <div class="w-12 h-12 border-4 border-brand border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-sm text-gray-600" x-text="statusMsg"></p>
        </div>
    </template>

    {{-- State 3: Preview & Submit --}}
    <template x-if="state === 'preview'">
        <div class="text-center">
            <video x-ref="video" autoplay playsinline class="w-full max-w-sm mx-auto rounded-xl mb-3 bg-black" x-show="!photo"></video>
            <canvas x-ref="canvas" class="hidden"></canvas>
            <img x-show="photo" :src="photo" class="w-full max-w-sm mx-auto rounded-xl mb-3 border-4 border-brand">
            
            <p class="text-xs text-green-600 text-center mb-3" x-show="photo">
                <i class="ph ph-check-circle"></i> Foto selfie presensi berhasil diambil.
            </p>

            <div class="flex gap-2 justify-center">
                <button @click="retake()" x-show="photo" class="px-4 py-2 rounded-full border border-gray-200 text-sm hover:bg-gray-50">Ulangi Foto</button>
                <button @click="submit()" x-show="photo" :disabled="submitting" class="px-6 py-2 rounded-full bg-brand text-white text-sm font-medium disabled:opacity-50 shadow-md">
                    <span x-show="!submitting">Kirim Presensi Sekarang</span>
                    <span x-show="submitting">Mengirim Presensi...</span>
                </button>
            </div>
        </div>
    </template>

    {{-- Error --}}
    <p x-show="error" x-text="error" class="text-red-500 text-sm text-center mt-4"></p>
</div>

@push('scripts')
<script>
function clockInWidget() {
    return {
        state: 'idle', statusMsg: '', error: '', photo: null,
        latitude: null, longitude: null, stream: null, submitting: false,

        async start() {
            this.error = '';
            this.state = 'loading';
            this.statusMsg = 'Mengambil koordinat GPS Anda...';
            try {
                const pos = await new Promise((res, rej) =>
                    navigator.geolocation.getCurrentPosition(res, rej, { enableHighAccuracy: true })
                );
                this.latitude = pos.coords.latitude;
                this.longitude = pos.coords.longitude;
            } catch (e) {
                this.state = 'idle';
                this.error = 'Gagal mengakses GPS. Harap izinkan akses lokasi pada browser Anda.';
                return;
            }

            this.statusMsg = 'Menghubungkan kamera...';
            try {
                this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' } });
                this.state = 'preview';
                this.$nextTick(() => {
                    this.$refs.video.srcObject = this.stream;
                    setTimeout(() => this.takePhoto(), 1500);
                });
            } catch (e) {
                this.state = 'idle';
                this.error = 'Gagal membuka kamera. Harap izinkan akses kamera.';
            }
        },

        takePhoto() {
            const v = this.$refs.video, c = this.$refs.canvas;
            if (!v) return;
            c.width = v.videoWidth || 640; 
            c.height = v.videoHeight || 480;
            c.getContext('2d').drawImage(v, 0, 0);
            this.photo = c.toDataURL('image/jpeg', 0.8);
            if (this.stream) this.stream.getTracks().forEach(t => t.stop());
        },

        retake() {
            this.photo = null;
            this.start();
        },

        async submit() {
            this.submitting = true;
            try {
                const res = await fetch('{{ route("employee.attendance.clock-in") }}', {
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
                    this.error = data.message || (data.errors ? Object.values(data.errors).flat().join(', ') : 'Terjadi kesalahan.');
                    this.submitting = false;
                }
            } catch (e) {
                this.error = 'Koneksi gagal. Silakan coba lagi.';
                this.submitting = false;
            }
        }
    }
}
</script>
@endpush
