@props(['status'])

@php
    $value = is_object($status) ? $status->value : $status;
    $map = [
        'present'  => ['label' => 'Hadir',     'class' => 'bg-green-50 text-green-700',  'dot' => 'bg-green-500'],
        'late'     => ['label' => 'Terlambat', 'class' => 'bg-orange-50 text-orange-700', 'dot' => 'bg-orange-500'],
        'absent'   => ['label' => 'Absen',     'class' => 'bg-red-50 text-red-700',      'dot' => 'bg-red-500'],
        'leave'    => ['label' => 'Cuti',      'class' => 'bg-blue-50 text-blue-700',    'dot' => 'bg-blue-500'],
        'pending'  => ['label' => 'Menunggu',  'class' => 'bg-gray-100 text-gray-700',   'dot' => 'bg-gray-500'],
        'approved' => ['label' => 'Disetujui', 'class' => 'bg-green-50 text-green-700',  'dot' => 'bg-green-500'],
        'rejected' => ['label' => 'Ditolak',   'class' => 'bg-red-50 text-red-700',      'dot' => 'bg-red-500'],
        'draft'    => ['label' => 'Draft',     'class' => 'bg-yellow-50 text-yellow-700', 'dot' => 'bg-yellow-500'],
        'final'    => ['label' => 'Final',     'class' => 'bg-green-50 text-green-700',  'dot' => 'bg-green-500'],
    ];
    $item = $map[$value] ?? ['label' => $value, 'class' => 'bg-gray-100 text-gray-700', 'dot' => 'bg-gray-500'];
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $item['class'] }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $item['dot'] }}"></span>
    {{ $item['label'] }}
</span>
