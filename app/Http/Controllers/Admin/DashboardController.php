<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AttendanceStatus;
use App\Enums\PayslipStatus;
use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Payslip;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();
        $currentPeriod = now()->format('Y-m');

        $stats = [
            'total_payroll' => Payslip::where('period', $currentPeriod)->sum('net_salary'),
            'total_employees' => User::role(UserRole::EMPLOYEE)->where('is_active', true)->count(),
            'present_today' => Attendance::where('date', $today)->where('status', AttendanceStatus::PRESENT)->count(),
            'late_today' => Attendance::where('date', $today)->where('status', AttendanceStatus::LATE)->count(),
            'pending_overtime' => OvertimeRequest::where('status', 'pending')->count(),
            'pending_leave' => LeaveRequest::where('status', 'pending')->count(),
        ];

        return view('admin.dashboard', compact('stats'));
    }
}
