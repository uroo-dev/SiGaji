<?php

namespace App\Http\Controllers\Employee;

use App\Exceptions\AlreadyClockedInException;
use App\Http\Controllers\Controller;
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
            return redirect()->route('employee.dashboard')->with('success', 'Clock-in berhasil!');
        } catch (AlreadyClockedInException | ValidationException $e) {
            return back()->withErrors($e instanceof ValidationException ? $e->errors() : ['error' => $e->getMessage()]);
        }
    }

    public function clockOut()
    {
        try {
            $this->service->clockOut(Auth::user());
            return redirect()->route('employee.dashboard')->with('success', 'Clock-out berhasil!');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors());
        }
    }
}
