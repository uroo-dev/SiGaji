<template x-if="state === 'loading'">
    <div class="bg-white border border-gray-100 rounded-2xl p-8 text-center shadow-sm mt-3">
        <div class="w-12 h-12 border-4 border-brand border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
        <p class="text-sm text-gray-600 font-medium" x-text="statusMsg"></p>
    </div>
</template>

<template x-if="state === 'preview'">
    <div class="bg-white border border-gray-100 rounded-2xl p-6 shadow-sm mt-3">
        <video x-ref="video" autoplay playsinline class="w-full rounded-xl mb-3 bg-black" x-show="!photo"></video>
        <canvas x-ref="canvas" class="hidden"></canvas>
        <img x-show="photo" :src="photo" class="w-full rounded-xl mb-3 border-4 border-brand shadow-sm">
        <p class="text-xs text-green-600 text-center mb-3 font-medium" x-show="photo"><i class="ph ph-check-circle"></i> Foto presensi berhasil diambil.</p>
        <div class="flex gap-2 justify-center">
            <button type="button" @click="retake()" x-show="photo" class="px-4 py-2 rounded-full border border-gray-200 text-sm font-medium hover:bg-gray-50 transition-colors">Ulangi</button>
            <button type="button" @click="submit()" x-show="photo" :disabled="submitting" class="px-6 py-2 rounded-full bg-brand text-white text-sm font-semibold disabled:opacity-50 shadow-md hover:bg-brand-600 transition-all">
                <span x-show="!submitting">Kirim Presensi</span>
                <span x-show="submitting">Mengirim...</span>
            </button>
        </div>
        <p x-show="error" x-text="error" class="text-red-500 text-xs text-center mt-3 font-medium"></p>
    </div>
</template>
