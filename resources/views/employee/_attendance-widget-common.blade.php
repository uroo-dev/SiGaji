{{-- State: Loading --}}
<template x-if="state === 'loading'">
    <div class="bg-white border border-gray-100 rounded-3xl p-8 text-center shadow-sm mt-4">
        <div class="w-14 h-14 border-4 border-brand border-t-transparent rounded-full animate-spin mx-auto mb-4"></div>
        <p class="text-sm text-gray-800 font-semibold" x-text="statusMsg"></p>
        <p class="text-xs text-gray-400 mt-1">Harap izinkan akses kamera dan lokasi pada peramban Anda.</p>
        <button type="button" @click="cancel()" class="mt-4 text-xs text-gray-500 hover:text-red-600 underline font-medium">
            Batal
        </button>
    </div>
</template>

{{-- State: Kamera Aktif (Viewfinder & Tombol Shutter Jepret Foto) --}}
<template x-if="state === 'camera'">
    <div class="bg-slate-900 border border-gray-800 rounded-3xl p-4 shadow-xl mt-4 text-white overflow-hidden">
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

{{-- State: Review Hasil Foto & Kirim Presensi --}}
<template x-if="state === 'review'">
    <div class="bg-white border border-gray-100 rounded-3xl p-5 sm:p-6 shadow-sm mt-4 text-center">
        <div class="relative w-full aspect-[4/3] max-w-sm mx-auto rounded-2xl overflow-hidden mb-4 border-4 border-brand shadow-md">
            <img :src="photo" class="w-full h-full object-cover">
            <div class="absolute bottom-2.5 inset-x-2.5 bg-black/60 backdrop-blur-md text-white text-[11px] font-semibold py-1 px-2.5 rounded-xl border border-white/20">
                <i class="ph ph-check-circle text-green-400 mr-1"></i> Foto Presensi Berhasil Diambil
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
                <span x-show="!submitting">Kirim Presensi</span>
                <span x-show="submitting">Mengirim...</span>
            </button>
        </div>
        
        <p x-show="error" x-text="error" class="text-red-500 text-xs font-semibold mt-3"></p>
    </div>
</template>

{{-- Hidden Canvas & File Input Fallback --}}
<canvas x-ref="canvas" class="hidden"></canvas>
<input type="file" x-ref="fileInput" accept="image/*" capture="user" @change="handleFileUpload($event)" class="hidden">
