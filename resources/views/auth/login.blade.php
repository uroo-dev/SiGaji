<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — SiGaji</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-orange-50 via-white to-orange-50 flex items-center justify-center p-4" style="font-family:'Inter',sans-serif;">
    <div class="w-full max-w-md">
        {{-- Logo --}}
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-2xl bg-[#FF5A36] flex items-center justify-center mx-auto mb-4 shadow-lg shadow-orange-300/50">
                <i class="ph-bold ph-wallet text-3xl text-white"></i>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Selamat Datang di SiGaji</h1>
            <p class="text-sm text-gray-500 mt-1">Sistem Penggajian & Absensi UMKM</p>
        </div>

        {{-- Form --}}
        <div class="bg-white rounded-2xl shadow-xl shadow-gray-200/50 p-8 border border-gray-100">
            @if($errors->any())
                <div class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-lg text-sm">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Email</label>
                    <input type="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5A36]/30 focus:border-[#FF5A36]"
                           placeholder="admin@sigaji.test">
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-700 block mb-1">Password</label>
                    <input type="password" name="password" required
                           class="w-full px-4 py-2.5 border border-gray-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-[#FF5A36]/30 focus:border-[#FF5A36]"
                           placeholder="••••••••">
                </div>
                <div class="flex items-center justify-between text-sm">
                    <label class="flex items-center gap-2 text-gray-600">
                        <input type="checkbox" name="remember" class="rounded"> Ingat saya
                    </label>
                </div>
                <button type="submit" class="w-full bg-[#FF5A36] hover:bg-orange-600 text-white py-3 rounded-xl font-semibold transition-colors">
                    Masuk
                </button>
            </form>

            {{-- Demo Accounts --}}
            <div class="mt-6 pt-6 border-t border-gray-100">
                <p class="text-xs text-gray-400 text-center mb-2">Akun Demo (Password: password)</p>
                <div class="grid grid-cols-3 gap-2 text-xs">
                    <div class="text-center p-2 rounded-lg bg-orange-50 text-orange-700 cursor-pointer" onclick="document.querySelector('input[name=email]').value='admin@sigaji.test'; document.querySelector('input[name=password]').value='password';">
                        <p class="font-medium">Admin</p>
                        <p class="text-[10px] truncate">admin@sigaji.test</p>
                    </div>
                    <div class="text-center p-2 rounded-lg bg-blue-50 text-blue-700 cursor-pointer" onclick="document.querySelector('input[name=email]').value='supervisor@sigaji.test'; document.querySelector('input[name=password]').value='password';">
                        <p class="font-medium">Supervisor</p>
                        <p class="text-[10px] truncate">supervisor@sigaji.test</p>
                    </div>
                    <div class="text-center p-2 rounded-lg bg-green-50 text-green-700 cursor-pointer" onclick="document.querySelector('input[name=email]').value='dewi@sigaji.test'; document.querySelector('input[name=password]').value='password';">
                        <p class="font-medium">Karyawan</p>
                        <p class="text-[10px] truncate">dewi@sigaji.test</p>
                    </div>
                </div>
            </div>
        </div>

        <p class="text-center text-xs text-gray-400 mt-6">© {{ date('Y') }} SiGaji — Portofolio Project</p>
    </div>
</body>
</html>
