<x-layouts.app title="Kelola Karyawan">
    <div x-data="{ showAddModal: false }">
        <x-page-header title="Karyawan" subtitle="Kelola data seluruh karyawan, gaji pokok, dan akun.">
            <x-slot:actions>
                <button @click="showAddModal = true" class="bg-brand text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-brand-600 flex items-center gap-2 shadow-sm transition-all">
                    <i class="ph ph-plus text-base"></i> Tambah Karyawan
                </button>
            </x-slot:actions>
        </x-page-header>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-gray-100 flex items-center justify-between gap-4">
                <form method="GET" class="flex gap-2 max-w-sm w-full">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama karyawan..." 
                           class="w-full border border-gray-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-brand">
                    <button class="bg-gray-900 text-white px-4 py-2 rounded-xl text-sm font-medium hover:bg-gray-800 transition-colors">Cari</button>
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
                                    <button type="button" 
                                            onclick="confirmModal({
                                                title: 'Nonaktifkan Karyawan?',
                                                text: 'Akun <strong>{{ addslashes($employee->name) }}</strong> akan dinonaktifkan (Soft Delete) dan tidak dapat mengakses sistem.',
                                                icon: 'warning',
                                                confirmText: 'Ya, Nonaktifkan',
                                                cancelText: 'Batal',
                                                confirmColor: '#EF4444',
                                                formId: 'delete-emp-{{ $employee->id }}'
                                            })"
                                            class="text-gray-400 hover:text-red-500 p-1.5 rounded-lg hover:bg-red-50 transition-colors" 
                                            title="Nonaktifkan Karyawan">
                                        <i class="ph ph-trash text-lg"></i>
                                    </button>
                                    <form id="delete-emp-{{ $employee->id }}" method="POST" action="{{ route('admin.employees.destroy', $employee) }}" class="hidden">
                                        @csrf
                                        @method('DELETE')
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
        </div>

        {{-- Modal Tambah Karyawan --}}
        <div x-show="showAddModal" x-cloak x-transition.opacity class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4" @click.self="showAddModal = false">
            <div class="bg-white rounded-3xl p-6 sm:p-8 w-full max-w-lg shadow-2xl relative" @click.stop>
                <div class="flex items-center justify-between pb-4 border-b border-gray-100 mb-5">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-brand-light flex items-center justify-center text-brand">
                            <i class="ph ph-user-plus text-xl"></i>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-gray-900 leading-tight">Tambah Karyawan Baru</h3>
                            <p class="text-xs text-gray-500">Daftarkan akun karyawan ke dalam sistem SiGaji.</p>
                        </div>
                    </div>
                    <button type="button" @click="showAddModal = false" class="text-gray-400 hover:text-gray-600 rounded-full p-1.5 hover:bg-gray-100">
                        <i class="ph ph-x text-lg"></i>
                    </button>
                </div>

                <form action="{{ route('admin.employees.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Nama Lengkap</label>
                        <input type="text" name="name" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:outline-none" placeholder="Contoh: Dewi Sartika">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Email</label>
                        <input type="email" name="email" required class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:outline-none" placeholder="dewi@sigaji.test">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-700 block mb-1">Password Awal</label>
                        <input type="password" name="password" required minlength="8" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:outline-none" placeholder="Minimal 8 karakter">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block mb-1">Gaji Pokok (Rp)</label>
                            <input type="number" name="base_salary" required min="0" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:outline-none" placeholder="4000000">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-700 block mb-1">Tanggal Bergabung</label>
                            <input type="date" name="joined_at" required value="{{ date('Y-m-d') }}" class="w-full border border-gray-200 rounded-xl px-3.5 py-2.5 text-sm focus:ring-1 focus:ring-brand focus:outline-none">
                        </div>
                    </div>
                    <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                        <button type="button" @click="showAddModal = false" class="px-5 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">Batal</button>
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-brand text-white text-sm font-semibold hover:bg-brand-600 shadow-md transition-all">Simpan Karyawan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>