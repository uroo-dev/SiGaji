@props(['title', 'subtitle' => null, 'actions' => null])

<div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-gray-500 text-sm mt-1">{{ $subtitle }}</p>
        @endif
    </div>
    @if($actions)
        <div class="flex gap-2">{{ $actions }}</div>
    @endif
</div>
