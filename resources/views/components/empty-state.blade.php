@props(['title', 'description', 'icon' => 'ph-folder-open'])

<div class="flex flex-col items-center justify-center py-16 text-center">
    <div class="w-16 h-16 rounded-full bg-gray-100 flex items-center justify-center mb-4">
        <i class="ph {{ $icon }} text-2xl text-gray-400"></i>
    </div>
    <h3 class="text-base font-semibold text-gray-800">{{ $title }}</h3>
    <p class="text-sm text-gray-500 mt-1 max-w-sm">{{ $description }}</p>
</div>
