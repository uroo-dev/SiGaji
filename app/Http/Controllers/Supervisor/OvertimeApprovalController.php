<?php

namespace App\Http\Controllers\Supervisor;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use App\Services\AuditService;
use Illuminate\Http\Request;

class OvertimeApprovalController extends Controller
{
    public function index()
    {
        $pending = OvertimeRequest::with('user')
            ->where('status', 'pending')
            ->latest()
            ->get();

        return view('supervisor.overtime-approvals.index', compact('pending'));
    }

    public function approve(OvertimeRequest $overtime)
    {
        $overtime->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
        ]);

        AuditService::log('overtime.approved', $overtime, ['status' => 'pending'], [
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'employee' => $overtime->user?->name,
            'hours' => $overtime->hours,
            'date' => $overtime->date->toDateString(),
            'time' => now()->toDateTimeString(),
        ]);

        return back()->with('success', 'Pengajuan lembur disetujui.');
    }

    public function reject(Request $request, OvertimeRequest $overtime)
    {
        $overtime->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approval_note' => $request->approval_note ?? 'Ditolak oleh atasan',
        ]);

        AuditService::log('overtime.rejected', $overtime, ['status' => 'pending'], [
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approval_note' => $request->approval_note ?? 'Ditolak oleh atasan',
            'employee' => $overtime->user?->name,
            'time' => now()->toDateTimeString(),
        ]);

        return back()->with('success', 'Pengajuan lembur ditolak.');
    }
}
