<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $employees = User::role(UserRole::EMPLOYEE)
            ->when($request->search, fn($q, $s) => $q->where('name', 'like', "%{$s}%"))
            ->latest()
            ->paginate(15);

        return view('admin.employees.index', compact('employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8',
            'base_salary' => 'required|numeric|min:0',
            'joined_at' => 'required|date',
        ]);

        User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'role' => UserRole::EMPLOYEE,
            'is_active' => true,
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Karyawan ditambahkan.');
    }

    public function destroy(User $employee)
    {
        // Soft delete otomatis digunakan karena Model User pakai trait SoftDeletes
        $employee->delete();
        return back()->with('success', 'Karyawan dinonaktifkan (Soft Delete).');
    }
}
