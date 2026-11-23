<?php

namespace App\Services;

use App\Enums\AttendanceStatus;
use App\Exceptions\AlreadyClockedInException;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function __construct(private SettingService $settingService) {}

    public function clockIn(User $user, array $data): Attendance
    {
        $today = now()->toDateString();

        // Guard 1: Cegah double clock-in
        if (Attendance::where('user_id', $user->id)->where('date', $today)->exists()) {
            throw new AlreadyClockedInException('Anda sudah melakukan clock-in hari ini.');
        }

        // Ambil setting radius dari Redis
        $officeLat = (float) $this->settingService->get('office_latitude', -6.917464);
        $officeLng = (float) $this->settingService->get('office_longitude', 107.619123);
        $maxRadius = (int) $this->settingService->get('max_radius_meters', 100);

        $distance = $this->calculateDistance($officeLat, $officeLng, (float) $data['latitude'], (float) $data['longitude']);

        // Guard 2: Validasi Radius
        if ($distance > $maxRadius) {
            throw ValidationException::withMessages([
                'radius' => "Anda berada di luar radius kantor. Jarak Anda: " . round($distance) . "m (Maks: {$maxRadius}m)."
            ]);
        }

        // Simpan Foto
        $photoPath = $this->storeBase64Photo($data['photo_base64'], $user->id);

        // Hitung Keterlambatan
        $now = now();
        $standardTime = $user->standard_clock_in ?? '08:00:00';
        $lateMinutes = $this->calculateLateMinutes($now, $standardTime);
        $status = $lateMinutes > 0 ? AttendanceStatus::LATE : AttendanceStatus::PRESENT;

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => $today,
            'clock_in' => $now->toTimeString(),
            'late_minutes' => $lateMinutes,
            'status' => $status,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'photo_path' => $photoPath,
            'distance_from_office' => round($distance),
        ]);

        Cache::forget('dashboard.admin.stats');
        return $attendance;
    }

    public function clockOut(User $user): Attendance
    {
        $today = now()->toDateString();

        $attendance = Attendance::where('user_id', $user->id)
            ->where('date', $today)
            ->first();

        if (! $attendance) {
            throw ValidationException::withMessages([
                'clock_out' => 'Anda belum melakukan clock-in hari ini.',
            ]);
        }

        if ($attendance->clock_out) {
            throw ValidationException::withMessages([
                'clock_out' => 'Anda sudah melakukan clock-out hari ini.',
            ]);
        }

        $attendance->update([
            'clock_out' => now()->toTimeString(),
        ]);

        Cache::forget('dashboard.admin.stats');
        return $attendance;
    }

    private function calculateLateMinutes(Carbon $time, string $standardTime): int
    {
        $standard = Carbon::parse($time->toDateString() . ' ' . $standardTime);
        return max(0, $standard->diffInMinutes($time, false) * -1);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2): float
    {
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function storeBase64Photo(string $base64String, int $userId): string
    {
        if (str_contains($base64String, ';base64,')) {
            $imageParts = explode(";base64,", $base64String);
            $imageTypeAux = explode("image/", $imageParts[0]);
            $imageType = $imageTypeAux[1] ?? 'jpeg';
            $imageBase64 = base64_decode($imageParts[1]);
        } else {
            $imageType = 'jpeg';
            $imageBase64 = base64_decode($base64String);
        }
        
        $fileName = 'attendances/' . $userId . '_' . now()->format('Ymd_His') . '.' . $imageType;
        Storage::disk('public')->put($fileName, $imageBase64);
        
        return $fileName;
    }
}
