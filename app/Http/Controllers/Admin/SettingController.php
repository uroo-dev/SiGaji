<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(private SettingService $settingService) {}

    public function index()
    {
        $settings = [
            'office_latitude' => $this->settingService->get('office_latitude', -6.917464),
            'office_longitude' => $this->settingService->get('office_longitude', 107.619123),
            'max_radius_meters' => $this->settingService->get('max_radius_meters', 100),
            'late_deduction_per_minute' => $this->settingService->get('late_deduction_per_minute', 1000),
            'overtime_hourly_rate' => $this->settingService->get('overtime_hourly_rate', 25000),
        ];
        return view('admin.settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'office_latitude' => 'required|numeric|between:-90,90',
            'office_longitude' => 'required|numeric|between:-180,180',
            'max_radius_meters' => 'required|integer|min:10',
            'late_deduction_per_minute' => 'required|numeric|min:0',
            'overtime_hourly_rate' => 'nullable|numeric|min:0',
        ]);

        foreach ($validated as $key => $value) {
            $this->settingService->set($key, $value);
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui.');
    }
}
