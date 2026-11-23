@props(['title' => null])

<header class="h-20 bg-white border-b border-gray-200 flex items-center justify-between px-4 sm:px-6 lg:px-8 flex-shrink-0">
    <div class="flex items-center gap-3">
        {{-- Mobile Menu Toggle --}}
        <button @click="sidebarOpen = true" class="lg:hidden text-gray-500 hover:text-gray-700">
            <i class="ph ph-list text-2xl"></i>
        </button>
        <h2 class="text-lg font-semibold text-gray-900 hidden sm:block">{{ $title ?? 'Dashboard' }}</h2>
    </div>

    <div class="flex items-center gap-2 sm:gap-3">
        {{-- Notification --}}
        <button class="w-10 h-10 rounded-full border border-gray-200 flex items-center justify-center text-gray-600 hover:bg-gray-50 relative">
            <i class="ph ph-bell text-lg"></i>
            <span class="absolute top-2 right-2 w-2 h-2 bg-brand rounded-full border-2 border-white"></span>
        </button>

        {{-- Profile Dropdown --}}
        <div class="flex items-center gap-2 border border-gray-200 rounded-full p-1 pr-3 ml-1 cursor-pointer hover:bg-gray-50 relative" 
             x-data="{ open: false }" @click="open = !open" @click.away="open = false">
            <img src="https://ui-avatars.com/api/?name={{ urlencode(auth()->user()?->name ?? 'User') }}&background=FF5A36&color=fff" 
                 class="w-8 h-8 rounded-full" alt="Avatar">
            <div class="hidden md:block text-left">
                <p class="text-xs font-bold text-gray-900 leading-tight">{{ auth()->user()?->name ?? 'User' }}</p>
                <p class="text-[10px] text-gray-500">{{ auth()->user()?->role?->label() ?? 'Karyawan' }}</p>
            </div>
            <i class="ph ph-caret-down text-gray-400 text-xs"></i>

            <div x-show="open" x-cloak x-transition class="absolute right-0 top-12 w-48 bg-white rounded-xl shadow-lg border border-gray-100 py-2 z-50">
                <div class="px-4 py-2 border-b border-gray-100 md:hidden">
                    <p class="text-xs font-bold text-gray-900">{{ auth()->user()?->name }}</p>
                    <p class="text-[10px] text-gray-500">{{ auth()->user()?->role?->label() }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                        <i class="ph ph-sign-out"></i> Keluar
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
