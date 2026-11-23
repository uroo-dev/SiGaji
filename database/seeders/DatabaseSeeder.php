<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Seed Settings
        $settings = [
            ['key' => 'office_latitude', 'value' => '-6.917464', 'description' => 'Latitude kantor'],
            ['key' => 'office_longitude', 'value' => '107.619123', 'description' => 'Longitude kantor'],
            ['key' => 'max_radius_meters', 'value' => '100', 'description' => 'Radius maksimal clock-in dalam meter'],
            ['key' => 'late_deduction_per_minute', 'value' => '1000', 'description' => 'Denda keterlambatan per menit'],
            ['key' => 'overtime_hourly_rate', 'value' => '25000', 'description' => 'Tarif lembur per jam'],
        ];

        foreach ($settings as $s) {
            Setting::updateOrCreate(['key' => $s['key']], $s);
        }

        // 2. Seed Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@sigaji.test'],
            [
                'name' => 'Bambang Pratama',
                'password' => Hash::make('password'),
                'role' => UserRole::ADMIN,
                'base_salary' => 8500000,
                'is_active' => true,
                'standard_clock_in' => '08:00:00',
                'annual_leave_quota' => 12,
                'joined_at' => now()->subYears(2),
            ]
        );

        $supervisor = User::firstOrCreate(
            ['email' => 'supervisor@sigaji.test'],
            [
                'name' => 'Rina Wijaya',
                'password' => Hash::make('password'),
                'role' => UserRole::SUPERVISOR,
                'base_salary' => 6500000,
                'is_active' => true,
                'standard_clock_in' => '08:00:00',
                'annual_leave_quota' => 12,
                'joined_at' => now()->subYear(),
            ]
        );

        $dewi = User::firstOrCreate(
            ['email' => 'dewi@sigaji.test'],
            [
                'name' => 'Dewi Sartika',
                'password' => Hash::make('password'),
                'role' => UserRole::EMPLOYEE,
                'base_salary' => 4500000,
                'is_active' => true,
                'standard_clock_in' => '08:00:00',
                'annual_leave_quota' => 10,
                'joined_at' => now()->subMonths(8),
            ]
        );

        $budi = User::firstOrCreate(
            ['email' => 'budi@sigaji.test'],
            [
                'name' => 'Budi Santoso',
                'password' => Hash::make('password'),
                'role' => UserRole::EMPLOYEE,
                'base_salary' => 4200000,
                'is_active' => true,
                'standard_clock_in' => '08:00:00',
                'annual_leave_quota' => 12,
                'joined_at' => now()->subMonths(6),
            ]
        );

        // 3. Seed Attendance sample
        $today = now()->toDateString();
        Attendance::firstOrCreate(
            ['user_id' => $dewi->id, 'date' => $today],
            [
                'clock_in' => '07:55:00',
                'clock_out' => null,
                'late_minutes' => 0,
                'status' => AttendanceStatus::PRESENT,
                'latitude' => -6.917464,
                'longitude' => 107.619123,
                'distance_from_office' => 15,
            ]
        );

        Attendance::firstOrCreate(
            ['user_id' => $budi->id, 'date' => $today],
            [
                'clock_in' => '08:15:00',
                'clock_out' => null,
                'late_minutes' => 15,
                'status' => AttendanceStatus::LATE,
                'latitude' => -6.917460,
                'longitude' => 107.619130,
                'distance_from_office' => 20,
            ]
        );

        // 4. Seed Overtime sample
        OvertimeRequest::firstOrCreate(
            ['user_id' => $dewi->id, 'date' => $today],
            [
                'start_time' => '17:00:00',
                'end_time' => '20:00:00',
                'hours' => 3.00,
                'reason' => 'Menyelesaikan rekap data inventaris bulanan.',
                'status' => 'pending',
            ]
        );

        // 5. Seed Leave sample
        LeaveRequest::firstOrCreate(
            ['user_id' => $budi->id, 'start_date' => now()->addDays(2)->toDateString()],
            [
                'end_date' => now()->addDays(3)->toDateString(),
                'total_days' => 2,
                'type' => 'annual',
                'reason' => 'Keperluan keluarga di luar kota.',
                'status' => 'pending',
            ]
        );
    }
}
