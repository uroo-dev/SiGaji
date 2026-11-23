<div x-data="clockInWidget()" class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 sm:p-8">
    {{-- State 1: Idle --}}
    <template x-if="state === 'idle'">
        <div class="text-center py-2">
            <div class="w-16 h-16 rounded-3xl bg-brand-light flex items-center justify-center mx-auto mb-4 text-brand shadow-sm">
                <i class="ph ph-camera text-3xl"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-1">Clock In Hari Ini</h3>
            <p class="text-xs text-gray-500 max-w-sm mx-auto mb-6">Pastikan Anda berada di area radius kantor dan siap mengambil foto selfie presensi.</p>
            <button type="button" @click="start()" class="bg-brand text-white px-8 py-3 rounded-2xl font-semibold hover:bg-brand-600 transition-all shadow-lg shadow-brand/30 inline-flex items-center gap-2">
                <i class="ph ph-camera text-xl"></i> Buka Kamera & Clock In
            </button>
        </div>
    </template>

    {{-- State 2: Loading --}}
    <template x-if="state === 'loading'">
        <div class="text-center py-8">
            <div class="w-14 h-14 border-4 border-brand border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
            <p class="text-sm text-gray-800 font-semibold" x-text="statusMsg"></p>
            <p class="text-xs text-gray-400 mt-1">Harap izinkan akses kamera dan lokasi pada peramban Anda.</p>
            <button type="button" @click="cancel()" class="mt-4 text-xs text-gray-500 hover:text-red-600 underline font-medium">
                Batal
            </button>
        </div>
    </template>

    {{-- State 3: Kamera Aktif (Viewfinder & Tombol Shutter Jepret Foto) --}}
    <template x-if="state === 'camera'">
        <div class="bg-slate-900 border border-gray-800 rounded-3xl p-4 shadow-xl text-white overflow-hidden max-w-md mx-auto">
            {{-- Video Container dengan Frame Viewfinder --}}
            <div class="relative w-full aspect-[4/3] bg-black rounded-2xl overflow-hidden shadow-inner flex items-center justify-center">
                <video x-ref="video" autoplay playsinline muted class="w-full h-full object-cover"></video>

                {{-- Top Info Bar Overlay --}}
                <div class="absolute top-3 inset-x-3 flex items-center justify-between z-10">
                    <span class="inline-flex items-center gap-1.5 bg-black/60 backdrop-blur-md text-white text-[11px] font-semibold px-3 py-1 rounded-full border border-white/20">
                        <span class="w-2 h-2 rounded-full bg-red-500 animate-ping"></span>
                        <span>KAMERA AKTIF</span>
                    </span>
                    <button type="button" @click="cancel()" 
                            class="w-7 h-7 rounded-full bg-black/60 hover:bg-black/90 backdrop-blur-md text-white flex items-center justify-center border border-white/20 transition-colors"
                            title="Tutup Kamera">
                        <i class="ph ph-x text-sm"></i>
                    </button>
                </div>

                {{-- Viewfinder Frame Guides (Sudut Bingkai) --}}
                <div class="absolute inset-4 pointer-events-none">
                    <div class="absolute top-0 left-0 w-8 h-8 border-t-2 border-l-2 border-white/80 rounded-tl-lg"></div>
                    <div class="absolute top-0 right-0 w-8 h-8 border-t-2 border-r-2 border-white/80 rounded-tr-lg"></div>
                    <div class="absolute bottom-0 left-0 w-8 h-8 border-b-2 border-l-2 border-white/80 rounded-bl-lg"></div>
                    <div class="absolute bottom-0 right-0 w-8 h-8 border-b-2 border-r-2 border-white/80 rounded-br-lg"></div>
                </div>

                {{-- Target Guide Wajah (Subtle Oval) --}}
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="w-44 h-56 border-2 border-dashed border-white/40 rounded-[50%]"></div>
                </div>
            </div>

            {{-- Shutter Controls (Tombol Jepret Kamera Seperti HP) --}}
            <div class="pt-4 pb-2 flex flex-col items-center justify-center">
                <button type="button" @click="takePhoto()" 
                        class="group relative w-18 h-18 rounded-full border-4 border-white bg-white/20 backdrop-blur-md flex items-center justify-center shadow-2xl hover:scale-105 active:scale-95 transition-all focus:outline-none"
                        title="Ambil Foto Presensi">
                    <span class="w-13 h-13 rounded-full bg-brand group-hover:bg-brand-600 flex items-center justify-center text-white shadow-inner transition-colors">
                        <i class="ph ph-camera text-2xl"></i>
                    </span>
                </button>
                <p class="text-xs text-slate-300 font-semibold mt-2.5">Ketuk tombol kamera di atas untuk ambil foto</p>
                
                {{-- Fallback Upload --}}
                <div class="mt-2 pt-2 border-t border-slate-800 w-full text-center">
                    <button type="button" @click="$refs.fileInput.click()" class="text-[11px] text-slate-400 hover:text-white underline transition-colors">
                        <i class="ph ph-upload-simple mr-0.5"></i> Atau pilih / foto dari galeri HP
                    </button>
                </div>
            </div>
        </div>
    </template>

    {{-- State 4: Review Hasil Foto & Submit --}}
    <template x-if="state === 'review'">
        <div class="text-center max-w-md mx-auto">
            <div class="relative w-full aspect-[4/3] rounded-2xl overflow-hidden mb-4 border-4 border-brand shadow-md">
                <img :src="photo" class="w-full h-full object-cover">
                <div class="absolute bottom-2.5 inset-x-2.5 bg-black/60 backdrop-blur-md text-white text-[11px] font-semibold py-1 px-2.5 rounded-xl border border-white/20">
                    <i class="ph ph-check-circle text-green-400 mr-1"></i> Foto Selfie Berhasil Diambil
                </div>
            </div>

            <div class="flex items-center justify-center gap-2.5 mb-2">
                <button type="button" @click="retake()" 
                        class="flex-1 py-2.5 px-4 rounded-xl border border-gray-200 text-gray-700 text-xs font-semibold hover:bg-gray-50 flex items-center justify-center gap-1.5 transition-colors">
                    <i class="ph ph-arrows-clockwise text-base text-gray-500"></i> Ambil Ulang
                </button>
                <button type="button" @click="submit()" :disabled="submitting" 
                        class="flex-1 py-2.5 px-4 rounded-xl bg-brand text-white text-xs font-bold hover:bg-brand-600 disabled:opacity-50 shadow-md flex items-center justify-center gap-1.5 transition-all">
                    <i class="ph ph-paper-plane-tilt text-base" x-show="!submitting"></i>
                    <span x-show="!submitting">Kirim Presensi Sekarang</span>
                    <span x-show="submitting">Mengirim...</span>
                </button>
            </div>
        </div>
    </template>

    {{-- Hidden Canvas & File Input Fallback --}}
    <canvas x-ref="canvas" class="hidden"></canvas>
    <input type="file" x-ref="fileInput" accept="image/*" capture="user" @change="handleFileUpload($event)" class="hidden">

    {{-- Error --}}
    <p x-show="error" x-text="error" class="text-red-500 text-xs text-center mt-4 font-semibold"></p>
</div>

@push('scripts')
<script>
function clockInWidget() {
    return {
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
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Presensi Ditolak',
                            text: this.error,
                            confirmButtonColor: '#FF5A36',
                            confirmButtonText: 'Mengerti',
                            customClass: { popup: 'rounded-3xl shadow-2xl font-sans' }
                        });
                    }
                    this.submitting = false;
                }
            } catch (e) {
                this.error = 'Koneksi gagal. Silakan coba lagi.';
                this.submitting = false;
            }
        }
    };
}
</script>
@endpush
