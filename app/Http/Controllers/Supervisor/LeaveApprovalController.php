<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\LeaveRequest;
use App\Services\AuditService;
use Illuminate\Http\Request;

class LeaveApprovalController extends Controller
{
    public function index()
    {
        $pending = LeaveRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('supervisor.leave-approvals.index', compact('pending'));
    }

    public function approve(LeaveRequest $leave)
    {
        $leave->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        // Kurangi kuota jika cuti tahunan
        if ($leave->type === 'annual' && $leave->user) {
            $leave->user->decrement('annual_leave_quota', $leave->total_days);
        }

        AuditService::log('leave.approved', $leave);

        return back()->with('success', 'Pengajuan cuti disetujui.');
    }

    public function reject(Request $request, LeaveRequest $leave)
    {
        $leave->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approval_note' => $request->approval_note ?? 'Ditolak oleh atasan',
        ]);

        AuditService::log('leave.rejected', $leave);

        return back()->with('success', 'Pengajuan cuti ditolak.');
    }
}
