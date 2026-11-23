<?php

use App\Http\Controllers\Admin\AttendanceController as AdminAttendanceController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Employee\AttendanceController as EmployeeAttendanceController;
use App\Http\Controllers\Employee\DashboardController as EmployeeDashboardController;
use App\Http\Controllers\Employee\LeaveController;
use App\Http\Controllers\Employee\OvertimeController;
use App\Http\Controllers\Employee\PayslipController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Supervisor\DashboardController as SupervisorDashboardController;
use App\Http\Controllers\Supervisor\LeaveApprovalController;
use App\Http\Controllers\Supervisor\OvertimeApprovalController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard or login
Route::get('/', function () {
    if (Auth::check()) {
        $role = Auth::user()->role?->value ?? 'employee';
        return redirect()->route($role . '.dashboard');
    }
    return redirect()->route('login');
});

// Authentication
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// Profile (common for all authenticated users)
Route::middleware(['auth'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
});

// ===== ADMIN =====
Route::middleware(['auth', 'role.admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');
    
    Route::resource('employees', EmployeeController::class);
    
    Route::get('/attendances', [AdminAttendanceController::class, 'index'])->name('attendances.index');
    Route::post('/attendances/{attendance}/correct', [AdminAttendanceController::class, 'correct'])->name('attendances.correct');
    
    // Admin Approval Lembur & Cuti
    Route::get('/overtime-approvals', [OvertimeApprovalController::class, 'index'])->name('overtime.index');
    Route::post('/overtime-approvals/{overtime}/approve', [OvertimeApprovalController::class, 'approve'])->name('overtime.approve');
    Route::post('/overtime-approvals/{overtime}/reject', [OvertimeApprovalController::class, 'reject'])->name('overtime.reject');
    
    Route::get('/leave-approvals', [LeaveApprovalController::class, 'index'])->name('leave.index');
    Route::post('/leave-approvals/{leave}/approve', [LeaveApprovalController::class, 'approve'])->name('leave.approve');
    Route::post('/leave-approvals/{leave}/reject', [LeaveApprovalController::class, 'reject'])->name('leave.reject');
    
    // Laporan
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/payroll', [ReportController::class, 'payroll'])->name('reports.payroll');
    
    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate');
    Route::get('/payroll/status/{period}', [PayrollController::class, 'status'])->name('payroll.status');
    Route::post('/payroll/finalize', [PayrollController::class, 'finalize'])->name('payroll.finalize');
    
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [SettingController::class, 'update'])->name('settings.update');
    
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
});

// ===== SUPERVISOR =====
Route::middleware(['auth', 'role.supervisor'])->prefix('supervisor')->name('supervisor.')->group(function () {
    Route::get('/dashboard', [SupervisorDashboardController::class, 'index'])->name('dashboard');
    
    Route::get('/overtime-approvals', [OvertimeApprovalController::class, 'index'])->name('overtime.index');
    Route::post('/overtime-approvals/{overtime}/approve', [OvertimeApprovalController::class, 'approve'])->name('overtime.approve');
    Route::post('/overtime-approvals/{overtime}/reject', [OvertimeApprovalController::class, 'reject'])->name('overtime.reject');
    
    Route::get('/leave-approvals', [LeaveApprovalController::class, 'index'])->name('leave.index');
    Route::post('/leave-approvals/{leave}/approve', [LeaveApprovalController::class, 'approve'])->name('leave.approve');
    Route::post('/leave-approvals/{leave}/reject', [LeaveApprovalController::class, 'reject'])->name('leave.reject');
});

// ===== EMPLOYEE =====
Route::middleware(['auth', 'role.employee'])->prefix('employee')->name('employee.')->group(function () {
    Route::get('/dashboard', [EmployeeDashboardController::class, 'index'])->name('dashboard');
    
    Route::post('/attendance/clock-in', [EmployeeAttendanceController::class, 'clockIn'])->name('attendance.clock-in');
    Route::post('/attendance/clock-out', [EmployeeAttendanceController::class, 'clockOut'])->name('attendance.clock-out');
    Route::get('/attendances', [EmployeeAttendanceController::class, 'history'])->name('attendances.index');
    
    Route::get('/overtimes', [OvertimeController::class, 'index'])->name('overtimes.index');
    Route::post('/overtimes', [OvertimeController::class, 'store'])->name('overtimes.store');
    Route::delete('/overtimes/{overtime}', [OvertimeController::class, 'cancel'])->name('overtimes.cancel');
    
    Route::get('/leaves', [LeaveController::class, 'index'])->name('leaves.index');
    Route::post('/leaves', [LeaveController::class, 'store'])->name('leaves.store');
    
    Route::get('/payslips', [PayslipController::class, 'index'])->name('payslips.index');
    Route::get('/payslips/{payslip}/pdf', [PayslipController::class, 'downloadPdf'])->name('payslips.pdf');
});
