@php
    $user = auth()->user();
    $roleVal = $user?->role instanceof \App\Enums\UserRole ? $user->role->value : ($user?->role ?? 'employee');
    $menuItems = match($roleVal) {
        'admin' => [
            ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'ph-squares-four'],
            ['route' => 'admin.employees.index', 'label' => 'Karyawan', 'icon' => 'ph-users'],
            ['route' => 'admin.attendances.index', 'label' => 'Absensi', 'icon' => 'ph-calendar-check'],
            ['route' => 'admin.payroll.index', 'label' => 'Payroll', 'icon' => 'ph-money'],
            ['route' => 'admin.audit-logs.index', 'label' => 'Audit Log', 'icon' => 'ph-shield-check'],
            ['route' => 'admin.settings.index', 'label' => 'Pengaturan', 'icon' => 'ph-gear'],
        ],
        'supervisor' => [
            ['route' => 'supervisor.dashboard', 'label' => 'Dashboard', 'icon' => 'ph-squares-four'],
            ['route' => 'supervisor.overtime.index', 'label' => 'Approval Lembur', 'icon' => 'ph-hourglass-high'],
            ['route' => 'supervisor.leave.index', 'label' => 'Approval Cuti', 'icon' => 'ph-airplane-tilt'],
        ],
        default => [
            ['route' => 'employee.dashboard', 'label' => 'Dashboard', 'icon' => 'ph-squares-four'],
            ['route' => 'employee.attendances.index', 'label' => 'Riwayat Absensi', 'icon' => 'ph-calendar-check'],
            ['route' => 'employee.overtimes.index', 'label' => 'Lembur', 'icon' => 'ph-hourglass-high'],
            ['route' => 'employee.leaves.index', 'label' => 'Cuti', 'icon' => 'ph-airplane-tilt'],
            ['route' => 'employee.payslips.index', 'label' => 'Slip Gaji', 'icon' => 'ph-file-text'],
        ],
    };
@endphp

{{-- Mobile Overlay --}}
<div x-show="sidebarOpen" x-transition.opacity x-cloak class="fixed inset-0 bg-black/40 z-40 lg:hidden" @click="sidebarOpen = false"></div>

<aside :class="sidebarOpen ? 'w-64 translate-x-0' : 'w-64 -translate-x-full lg:translate-x-0 lg:w-20'"
       class="fixed inset-y-0 left-0 z-50 lg:static flex bg-white border-r border-gray-200 flex-col py-6 transition-all duration-300 flex-shrink-0">
    
    {{-- Toggle Button (Desktop) --}}
    <button @click="toggleSidebar()" 
            type="button"
            class="hidden lg:flex absolute -right-3 top-8 bg-white border border-gray-200 rounded-full w-6 h-6 items-center justify-center shadow-sm text-gray-500 hover:text-brand z-50">
        <i :class="sidebarOpen ? 'ph ph-caret-left' : 'ph ph-caret-right'" class="text-xs"></i>
    </button>

    {{-- Close Button (Mobile) --}}
    <button @click="sidebarOpen = false"
            type="button"
            class="lg:hidden absolute right-4 top-4 text-gray-400 hover:text-gray-600">
        <i class="ph ph-x text-lg"></i>
    </button>

    {{-- Logo --}}
    <div class="flex items-center px-4 mb-8 cursor-pointer" onclick="window.location='{{ route($roleVal . '.dashboard') }}'">
        <div class="w-10 h-10 min-w-[40px] rounded-xl bg-brand flex items-center justify-center text-white shadow-lg shadow-brand/30">
            <i class="ph-bold ph-wallet text-xl"></i>
        </div>
        <span x-show="sidebarOpen" x-transition.opacity class="ml-3 font-bold text-xl text-gray-900 whitespace-nowrap">SiGaji</span>
    </div>

    {{-- Menu Items --}}
    <nav class="flex-1 flex flex-col gap-1 w-full px-3">
        @foreach($menuItems as $item)
            @php $isActive = request()->routeIs($item['route'] . '*'); @endphp
            <a href="{{ route($item['route']) }}" 
               :title="sidebarOpen ? '' : '{{ $item['label'] }}'"
               class="w-full h-12 rounded-xl flex items-center px-3 transition-colors
                      {{ $isActive ? 'bg-brand text-white shadow-md shadow-brand/20' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                <i class="ph {{ $item['icon'] }} text-xl min-w-[24px]"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="ml-3 text-sm font-medium whitespace-nowrap">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    {{-- Bottom (Logout) --}}
    <div class="px-3 mt-auto pt-4 border-t border-gray-100">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" :title="sidebarOpen ? '' : 'Keluar'"
                    class="w-full h-12 rounded-xl flex items-center px-3 text-gray-500 hover:text-red-500 hover:bg-red-50 transition-colors">
                <i class="ph ph-sign-out text-xl min-w-[24px]"></i>
                <span x-show="sidebarOpen" x-transition.opacity class="ml-3 text-sm font-medium whitespace-nowrap">Keluar</span>
            </button>
        </form>
    </div>
</aside>
