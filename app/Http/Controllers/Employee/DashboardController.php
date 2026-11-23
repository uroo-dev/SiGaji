<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\Payslip;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = now()->toDateString();

        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        $remainingLeave = $user->annual_leave_quota;

        $lastPayslip = Payslip::where('user_id', $user->id)
            ->where('status', 'final')
            ->latest('period')
            ->first();

        $recentAttendances = Attendance::where('user_id', $user->id)
            ->latest('date')
            ->take(7)
            ->get();

        return view('employee.dashboard', compact(
            'todayAttendance',
            'remainingLeave',
            'lastPayslip',
            'recentAttendances'
        ));
    }
}
