<x-layouts.app title="Kelola Karyawan">
    <x-page-header title="Karyawan" subtitle="Kelola data seluruh karyawan, gaji pokok, dan akun.">
        <x-slot:actions>
            <button @click="showAddModal = true" class="bg-brand text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-brand-600 flex items-center gap-2 shadow-sm">
                <i class="ph ph-plus"></i> Tambah Karyawan
            </button>
        </x-slot:actions>
    </x-page-header>

    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden" x-data="{ showAddModal: false }">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between gap-4">
            <form method="GET" class="flex gap-2 max-w-sm w-full">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama karyawan..." 
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand">
                <button class="bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-800">Cari</button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="text-left px-4 py-3">Nama</th>
                        <th class="text-left px-4 py-3">Email</th>
                        <th class="text-right px-4 py-3">Gaji Pokok</th>
                        <th class="text-center px-4 py-3">Jam Masuk Standar</th>
                        <th class="text-center px-4 py-3">Sisa Cuti</th>
                        <th class="text-center px-4 py-3">Status</th>
                        <th class="text-right px-4 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($employees as $employee)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($employee->name) }}&background=FFE5DB&color=FF5A36" class="w-8 h-8 rounded-full">
                                    <span class="font-medium text-gray-900">{{ $employee->name }}</span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $employee->email }}</td>
                            <td class="px-4 py-3 text-right font-mono font-medium">Rp {{ number_format($employee->base_salary, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center font-mono text-xs">{{ $employee->standard_clock_in }}</td>
                            <td class="px-4 py-3 text-center font-semibold text-gray-700">{{ $employee->annual_leave_quota }} hari</td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $employee->is_active ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700' }}">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $employee->is_active ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                    {{ $employee->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <form method="POST" action="{{ route('admin.employees.destroy', $employee) }}" onsubmit="return confirm('Nonaktifkan (Soft Delete) karyawan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-400 hover:text-red-500 p-1" title="Nonaktifkan Karyawan">
                                        <i class="ph ph-trash text-lg"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-empty-state title="Belum ada karyawan" description="Klik tombol Tambah Karyawan untuk mendaftarkan akun." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($employees->hasPages())
            <div class="p-4 border-t border-gray-100">
                {{ $employees->links() }}
            </div>
        @endif

        {{-- Modal Tambah Karyawan --}}
        <div x-show="showAddModal" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showAddModal = false">
            <div class="bg-white rounded-2xl p-6 w-full max-w-lg shadow-2xl">
                <h3 class="text-lg font-bold text-gray-900 mb-1">Tambah Karyawan Baru</h3>
                <p class="text-xs text-gray-500 mb-4">Daftarkan akun karyawan ke dalam sistem.</p>
                <form action="{{ route('admin.employees.store') }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Nama Lengkap</label>
                        <input type="text" name="name" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="Contoh: Dewi Sartika">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Email</label>
                        <input type="email" name="email" required class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="dewi@sigaji.test">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Password</label>
                        <input type="password" name="password" required minlength="8" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="Minimal 8 karakter">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block mb-1">Gaji Pokok (Rp)</label>
                            <input type="number" name="base_salary" required min="0" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm" placeholder="4000000">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block mb-1">Tanggal Bergabung</label>
                            <input type="date" name="joined_at" required value="{{ date('Y-m-d') }}" class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showAddModal = false" class="px-4 py-2 rounded-lg border border-gray-200 text-sm hover:bg-gray-50">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-brand text-white text-sm font-medium hover:bg-brand-600">Simpan Karyawan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
