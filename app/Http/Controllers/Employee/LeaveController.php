<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LeaveController extends Controller
{
    public function index()
    {
        $leaves = LeaveRequest::where('user_id', Auth::id())
            ->latest('start_date')
            ->get();

        return view('employee.leaves.index', compact('leaves'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:annual,sick,unpaid',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ]);

        $start = Carbon::parse($validated['start_date']);
        $end = Carbon::parse($validated['end_date']);
        $totalDays = $start->diffInDays($end) + 1;

        if ($validated['type'] === 'annual' && Auth::user()->annual_leave_quota < $totalDays) {
            return back()->with('error', "Saldo cuti tahunan tidak mencukupi (Tersisa: " . Auth::user()->annual_leave_quota . " hari).");
        }

        $leave = LeaveRequest::create([
            'user_id' => Auth::id(),
            'type' => $validated['type'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        AuditService::log('leave.submitted', $leave, null, [
            'type' => $leave->type,
            'start_date' => $leave->start_date->toDateString(),
            'end_date' => $leave->end_date->toDateString(),
            'total_days' => $leave->total_days,
            'reason' => $leave->reason,
            'time' => now()->toDateTimeString(),
        ]);

        return redirect()->route('employee.leaves.index')->with('success', 'Permohonan cuti berhasil diajukan.');
    }
}
