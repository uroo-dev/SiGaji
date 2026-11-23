<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $pendingOvertimeCount = OvertimeRequest::where('status', 'pending')->count();
        $pendingLeaveCount = LeaveRequest::where('status', 'pending')->count();
        $presentTodayCount = Attendance::where('date', $today)->whereIn('status', ['present', 'late'])->count();

        $recentOvertimes = OvertimeRequest::with('user')->where('status', 'pending')->latest()->take(5)->get();
        $recentLeaves = LeaveRequest::with('user')->where('status', 'pending')->latest()->take(5)->get();

        return view('supervisor.dashboard', compact(
            'pendingOvertimeCount',
            'pendingLeaveCount',
            'presentTodayCount',
            'recentOvertimes',
            'recentLeaves'
        ));
    }
}
