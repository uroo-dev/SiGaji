<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\OvertimeRequest;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeController extends Controller
{
    public function index()
    {
        $overtimes = OvertimeRequest::where('user_id', Auth::id())
            ->latest('date')
            ->get();

        return view('employee.overtimes.index', compact('overtimes'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'start_time' => 'required',
            'end_time' => 'required',
            'reason' => 'required|string|max:500',
        ]);

        $start = Carbon::parse($validated['date'] . ' ' . $validated['start_time']);
        $end = Carbon::parse($validated['date'] . ' ' . $validated['end_time']);
        
        if ($end->lessThan($start)) {
            $end->addDay();
        }

        $hours = max(0.5, round($start->diffInMinutes($end) / 60, 2));

        OvertimeRequest::create([
            'user_id' => Auth::id(),
            'date' => $validated['date'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'hours' => $hours,
            'reason' => $validated['reason'],
            'status' => 'pending',
        ]);

        return redirect()->route('employee.overtimes.index')->with('success', 'Pengajuan lembur berhasil dikirim.');
    }

    public function cancel(OvertimeRequest $overtime)
    {
        if ($overtime->user_id !== Auth::id()) {
            abort(403);
        }

        if ($overtime->status !== 'pending') {
            return back()->with('error', 'Hanya pengajuan dengan status pending yang dapat dibatalkan.');
        }

        $overtime->delete();

        return back()->with('success', 'Pengajuan lembur dibatalkan.');
    }
}
