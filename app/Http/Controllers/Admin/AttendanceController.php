<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->date ?? now()->toDateString();

        $attendances = Attendance::with(['user', 'correctedBy'])
            ->where('date', $date)
            ->latest()
            ->get();

        return view('admin.attendances.index', compact('attendances', 'date'));
    }

    public function correct(Request $request, Attendance $attendance)
    {
        $validated = $request->validate([
            'clock_in' => 'nullable',
            'clock_out' => 'nullable',
            'correction_reason' => 'required|string',
        ]);

        $oldValues = $attendance->only(['clock_in', 'clock_out', 'late_minutes', 'status', 'corrected_by', 'correction_reason']);

        // Recalculate late minutes if clock_in is changed
        $lateMinutes = $attendance->late_minutes;
        if (! empty($validated['clock_in']) && $attendance->user) {
            $standardTime = $attendance->user->standard_clock_in ?? '08:00:00';
            $standard = Carbon::parse($attendance->date->toDateString() . ' ' . $standardTime);
            $actual = Carbon::parse($attendance->date->toDateString() . ' ' . $validated['clock_in']);
            $lateMinutes = max(0, $standard->diffInMinutes($actual, false) * -1);
        }

        $attendance->update([
            'clock_in' => $validated['clock_in'],
            'clock_out' => $validated['clock_out'],
            'late_minutes' => $lateMinutes,
            'corrected_by' => auth()->id(),
            'correction_reason' => $validated['correction_reason'],
        ]);

        AuditService::log('attendance.corrected', $attendance, $oldValues, $attendance->getChanges());

        return back()->with('success', 'Koreksi absensi berhasil disimpan.');
    }
}
