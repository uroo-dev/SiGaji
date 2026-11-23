@props(['label', 'value', 'subtitle' => null, 'icon' => 'ph-chart-line', 'variant' => 'default'])

@php
    $isHighlight = $variant === 'highlight';
@endphp

<div class="{{ $isHighlight ? 'bg-brand text-white shadow-lg shadow-brand/20' : 'bg-white border border-gray-100 shadow-sm' }} rounded-2xl p-5 relative overflow-hidden">
    @if($isHighlight)
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
    @endif

    <div class="flex justify-between items-start mb-3 relative z-10">
        <div class="{{ $isHighlight ? 'bg-white/20' : 'bg-gray-50 text-gray-500' }} w-10 h-10 rounded-xl flex items-center justify-center">
            <i class="ph {{ $icon }} text-xl"></i>
        </div>
    </div>
    <p class="{{ $isHighlight ? 'text-white/80' : 'text-gray-500' }} text-xs font-medium uppercase tracking-wide">{{ $label }}</p>
    <h3 class="text-2xl sm:text-3xl font-bold {{ $isHighlight ? 'text-white' : 'text-gray-900' }} mt-1 font-mono tracking-tight">{{ $value }}</h3>
    @if($subtitle)
        <p class="{{ $isHighlight ? 'text-white/70' : 'text-gray-400' }} text-xs mt-2">{{ $subtitle }}</p>
    @endif
</div>
