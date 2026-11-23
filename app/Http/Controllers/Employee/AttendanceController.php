<?php

namespace App\Http\Controllers\Employee;

use App\Exceptions\AlreadyClockedInException;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function __construct(private AttendanceService $service) {}

    public function clockIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo_base64' => 'required|string',
        ]);

        try {
            $this->service->clockIn(Auth::user(), $request->all());

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Clock-in berhasil!']);
            }

            return redirect()->route('employee.attendances.index')->with('success', 'Clock-in berhasil!');
        } catch (AlreadyClockedInException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            return back()->withErrors(['error' => $e->getMessage()]);
        } catch (ValidationException $e) {
            $msg = implode(', ', array_merge(...array_values($e->errors())));

            if ($request->wantsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->withErrors($e->errors());
        }
    }

    public function clockOut(Request $request)
    {
        try {
            $this->service->clockOut(Auth::user(), $request->all());

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Clock-out berhasil!']);
            }

            return redirect()->route('employee.attendances.index')->with('success', 'Clock-out berhasil!');
        } catch (ValidationException $e) {
            $msg = implode(', ', array_merge(...array_values($e->errors())));

            if ($request->wantsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->withErrors($e->errors());
        }
    }

    public function history(Request $request)
    {
        $today = Auth::user()->attendances()->whereDate('date', today())->first();
        $history = Auth::user()->attendances()->latest('date')->limit(30)->get();
        $attendances = $history;

        return view('employee.attendances.index', compact('today', 'history', 'attendances'));
    }
}
