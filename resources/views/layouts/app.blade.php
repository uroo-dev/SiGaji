<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'SiGaji' }} — SiGaji</title>

    {{-- Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">

    {{-- Icons (Phosphor Icons) --}}
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/bold/style.css"/>
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1"></script>

    {{-- Tailwind CSS --}}
    <script src="https://cdn.tailwindcss.com"></script>

    {{-- Alpine.js --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Tailwind Config (Warna Brand SiGaji) --}}
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            DEFAULT: '#FF5A36',
                            light: '#FFF0EC',
                            50: '#FFF5F2',
                            100: '#FFE5DB',
                            600: '#E64A2A',
                            700: '#CC3D1F',
                        },
                        dark: '#111111',
                    }
                }
            }
        }
    </script>

    <style>
        body { font-family: 'Inter', sans-serif; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-thumb { background: #D1D5DB; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #9CA3AF; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-100 h-screen overflow-hidden" 
      x-data="{ 
          sidebarOpen: window.innerWidth < 1024 ? false : (localStorage.getItem('sidebarOpen') === null ? true : (localStorage.getItem('sidebarOpen') === 'true')),
          toggleSidebar() {
              this.sidebarOpen = !this.sidebarOpen;
              if (window.innerWidth >= 1024) {
                  localStorage.setItem('sidebarOpen', this.sidebarOpen);
              }
          }
      }">

    <div class="flex h-full w-full">
        {{-- Sidebar --}}
        <x-sidebar />

        {{-- Main Content --}}
        <main class="flex-1 flex flex-col min-w-0 overflow-hidden">
            {{-- Topbar --}}
            <x-topbar :title="$title ?? null" />

            {{-- Page Content --}}
            <div class="flex-1 overflow-y-auto bg-gray-50 p-4 sm:p-6 lg:p-8">
                {{-- Flash Messages --}}
                @if(session('success'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)" 
                         class="mb-4 bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl flex items-center justify-between">
                        <span class="flex items-center gap-2"><i class="ph ph-check-circle text-lg"></i> {{ session('success') }}</span>
                        <button @click="show = false"><i class="ph ph-x"></i></button>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
                         class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-center justify-between">
                        <span class="flex items-center gap-2"><i class="ph ph-warning-circle text-lg"></i> {{ session('error') }}</span>
                        <button @click="show = false"><i class="ph ph-x"></i></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl">
                        <ul class="list-disc list-inside text-sm">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
