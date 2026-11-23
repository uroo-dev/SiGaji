<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
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

        $employee = User::create([
            ...$validated,
            'password' => Hash::make($validated['password']),
            'role' => UserRole::EMPLOYEE,
            'is_active' => true,
        ]);

        AuditService::log('employee.created', $employee, null, [
            'name' => $employee->name,
            'email' => $employee->email,
            'base_salary' => $employee->base_salary,
            'time' => now()->toDateTimeString(),
        ]);

        return redirect()->route('admin.employees.index')->with('success', 'Karyawan ditambahkan.');
    }

    public function destroy(User $employee)
    {
        AuditService::log('employee.deactivated', $employee, ['is_active' => $employee->is_active], [
            'name' => $employee->name,
            'email' => $employee->email,
            'is_active' => false,
            'time' => now()->toDateTimeString(),
        ]);

        // Soft delete otomatis digunakan karena Model User pakai trait SoftDeletes
        $employee->delete();
        return back()->with('success', 'Karyawan dinonaktifkan (Soft Delete).');
    }
}
