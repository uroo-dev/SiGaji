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

            return redirect()->route('employee.dashboard')->with('success', 'Clock-in berhasil!');
        } catch (AlreadyClockedInException | ValidationException $e) {
            $msg = $e instanceof ValidationException ? implode(', ', array_merge(...array_values($e->errors()))) : $e->getMessage();

            if ($request->wantsJson()) {
                return response()->json(['message' => $msg], 422);
            }

            return back()->withErrors($e instanceof ValidationException ? $e->errors() : ['error' => $e->getMessage()]);
        }
    }

    public function clockOut(Request $request)
    {
        try {
            $this->service->clockOut(Auth::user());

            if ($request->wantsJson()) {
                return response()->json(['message' => 'Clock-out berhasil!']);
            }

            return redirect()->route('employee.dashboard')->with('success', 'Clock-out berhasil!');
        } catch (ValidationException $e) {
            if ($request->wantsJson()) {
                return response()->json(['message' => implode(', ', array_merge(...array_values($e->errors())))], 422);
            }

            return back()->withErrors($e->errors());
        }
    }

    public function history(Request $request)
    {
        $attendances = Attendance::where('user_id', Auth::id())
            ->latest('date')
            ->paginate(15);

        return view('employee.attendances.index', compact('attendances'));
    }
}
