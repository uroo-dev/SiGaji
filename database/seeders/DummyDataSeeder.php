<?php

namespace Database\Seeders;

use App\Enums\AttendanceStatus;
use App\Enums\PayslipStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\AuditLog;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Payslip;
use App\Models\PayslipItem;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DummyDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Settings
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

        $password = Hash::make('password');

        // 2. Admin & Supervisors
        $admin = User::updateOrCreate(
            ['email' => 'admin@sigaji.test'],
            [
                'name' => 'Bambang Pratama',
                'password' => $password,
                'role' => UserRole::ADMIN,
                'base_salary' => 9000000,
                'is_active' => true,
                'standard_clock_in' => '08:00:00',
                'annual_leave_quota' => 12,
                'joined_at' => '2023-01-10',
            ]
        );

        $supervisor1 = User::updateOrCreate(
            ['email' => 'supervisor@sigaji.test'],
            [
                'name' => 'Rina Wijaya',
                'password' => $password,
                'role' => UserRole::SUPERVISOR,
                'base_salary' => 6500000,
                'is_active' => true,
                'standard_clock_in' => '08:00:00',
                'annual_leave_quota' => 12,
                'joined_at' => '2023-05-15',
            ]
        );

        $supervisor2 = User::updateOrCreate(
            ['email' => 'hendra@sigaji.test'],
            [
                'name' => 'Hendra Gunawan',
                'password' => $password,
                'role' => UserRole::SUPERVISOR,
                'base_salary' => 6200000,
                'is_active' => true,
                'standard_clock_in' => '08:00:00',
                'annual_leave_quota' => 12,
                'joined_at' => '2023-08-01',
            ]
        );

        // 3. 10 Employees
        $employeeData = [
            ['name' => 'Dewi Sartika', 'email' => 'dewi@sigaji.test', 'salary' => 4500000, 'joined' => '2024-02-01'],
            ['name' => 'Budi Santoso', 'email' => 'budi@sigaji.test', 'salary' => 4200000, 'joined' => '2024-03-15'],
            ['name' => 'Siti Nurhaliza', 'email' => 'siti@sigaji.test', 'salary' => 4300000, 'joined' => '2024-04-10'],
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad@sigaji.test', 'salary' => 4600000, 'joined' => '2024-01-20'],
            ['name' => 'Fajar Nugraha', 'email' => 'fajar@sigaji.test', 'salary' => 4000000, 'joined' => '2024-05-02'],
            ['name' => 'Maya Anggraeni', 'email' => 'maya@sigaji.test', 'salary' => 4800000, 'joined' => '2023-11-12'],
            ['name' => 'Reza Pratama', 'email' => 'reza@sigaji.test', 'salary' => 4100000, 'joined' => '2024-06-18'],
            ['name' => 'Indah Kusuma', 'email' => 'indah@sigaji.test', 'salary' => 4400000, 'joined' => '2024-02-25'],
            ['name' => 'Dimas Saputra', 'email' => 'dimas@sigaji.test', 'salary' => 4250000, 'joined' => '2024-07-01'],
            ['name' => 'Putri Wulandari', 'email' => 'putri@sigaji.test', 'salary' => 4700000, 'joined' => '2023-10-05'],
        ];

        $employees = [];
        foreach ($employeeData as $emp) {
            $employees[] = User::updateOrCreate(
                ['email' => $emp['email']],
                [
                    'name' => $emp['name'],
                    'password' => $password,
                    'role' => UserRole::EMPLOYEE,
                    'base_salary' => $emp['salary'],
                    'is_active' => true,
                    'standard_clock_in' => '08:00:00',
                    'annual_leave_quota' => 12,
                    'joined_at' => $emp['joined'],
                ]
            );
        }

        // 4. Generate Attendances for the last 14 days
        $now = now();
        for ($i = 13; $i >= 0; $i--) {
            $date = $now->copy()->subDays($i);
            if ($date->isWeekend()) {
                continue;
            }

            $dateString = $date->toDateString();

            foreach ($employees as $index => $emp) {
                // Pola: Sebagian besar hadir tepat waktu, beberapa telat, 1-2 cuti/absen
                $mod = ($index + $i) % 10;

                if ($mod === 0) {
                    // Late 10-35 mins
                    $lateMin = rand(10, 35);
                    $clockIn = Carbon::parse("{$dateString} 08:00:00")->addMinutes($lateMin)->toTimeString();
                    $clockOut = Carbon::parse("{$dateString} 17:15:00")->addMinutes(rand(0, 30))->toTimeString();
                    $status = AttendanceStatus::LATE;
                } elseif ($mod === 9 && $i > 2) {
                    // Absent or Leave
                    $status = AttendanceStatus::ABSENT;
                    $clockIn = null;
                    $clockOut = null;
                    $lateMin = 0;
                } else {
                    // Present on-time
                    $clockIn = Carbon::parse("{$dateString} 07:45:00")->addMinutes(rand(0, 14))->toTimeString();
                    $clockOut = Carbon::parse("{$dateString} 17:00:00")->addMinutes(rand(0, 45))->toTimeString();
                    $status = AttendanceStatus::PRESENT;
                    $lateMin = 0;
                }

                Attendance::updateOrCreate(
                    ['user_id' => $emp->id, 'date' => $dateString],
                    [
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'late_minutes' => $lateMin,
                        'status' => $status,
                        'latitude' => -6.917464 + (rand(-30, 30) / 100000),
                        'longitude' => 107.619123 + (rand(-30, 30) / 100000),
                        'distance_from_office' => rand(8, 65),
                        'photo_path' => 'attendances/' . $emp->id . '_sample.jpg',
                    ]
                );
            }
        }

        // 5. Overtime Requests
        $overtimes = [
            ['user' => $employees[0], 'days' => 1, 'hours' => 3.0, 'status' => 'pending', 'reason' => 'Menyiapkan rekap inventaris mingguan.'],
            ['user' => $employees[1], 'days' => 2, 'hours' => 2.5, 'status' => 'approved', 'reason' => 'Laporan audit pembukuan akhir pekan.'],
            ['user' => $employees[2], 'days' => 3, 'hours' => 4.0, 'status' => 'approved', 'reason' => 'Bongkar muat bahan baku supplier utama.'],
            ['user' => $employees[3], 'days' => 4, 'hours' => 2.0, 'status' => 'rejected', 'reason' => 'Merapikan file arsip meja kantor.'],
            ['user' => $employees[4], 'days' => 5, 'hours' => 3.5, 'status' => 'approved', 'reason' => 'Pemeriksaan maintenance kelistrikan.'],
            ['user' => $employees[5], 'days' => 6, 'hours' => 2.0, 'status' => 'pending', 'reason' => 'Revisi form pemesanan pelanggan.'],
            ['user' => $employees[6], 'days' => 7, 'hours' => 3.0, 'status' => 'approved', 'reason' => 'Pendataan stok opname gudang transit.'],
            ['user' => $employees[7], 'days' => 8, 'hours' => 2.5, 'status' => 'approved', 'reason' => 'Persiapan event promosi offline.'],
        ];

        foreach ($overtimes as $ot) {
            $otDate = $now->copy()->subDays($ot['days'])->toDateString();
            OvertimeRequest::updateOrCreate(
                ['user_id' => $ot['user']->id, 'date' => $otDate],
                [
                    'start_time' => '17:00:00',
                    'end_time' => Carbon::parse('17:00:00')->addMinutes((int)($ot['hours'] * 60))->toTimeString(),
                    'hours' => $ot['hours'],
                    'reason' => $ot['reason'],
                    'status' => $ot['status'],
                    'approved_by' => $ot['status'] !== 'pending' ? $supervisor1->id : null,
                    'approval_note' => $ot['status'] === 'rejected' ? 'Beban kerja masih bisa diselesaikan di jam reguler.' : null,
                ]
            );
        }

        // 6. Leave Requests
        $leaves = [
            ['user' => $employees[0], 'type' => 'annual', 'days' => 2, 'status' => 'pending', 'reason' => 'Acara keluarga di luar kota.'],
            ['user' => $employees[1], 'type' => 'sick', 'days' => 1, 'status' => 'approved', 'reason' => 'Sakit demam, surat dokter terlampir.'],
            ['user' => $employees[2], 'type' => 'annual', 'days' => 3, 'status' => 'approved', 'reason' => 'Libur mudik tahunan.'],
            ['user' => $employees[3], 'type' => 'unpaid', 'days' => 2, 'status' => 'approved', 'reason' => 'Urusan pribadi mendesak.'],
            ['user' => $employees[4], 'type' => 'annual', 'days' => 1, 'status' => 'rejected', 'reason' => 'Cuti mendadak tanpa serah terima tugas.'],
            ['user' => $employees[5], 'type' => 'sick', 'days' => 2, 'status' => 'approved', 'reason' => 'Pemulihan pasca operasi gigi.'],
        ];

        foreach ($leaves as $index => $lv) {
            $startDate = $now->copy()->addDays($index + 1)->toDateString();
            $endDate = $now->copy()->addDays($index + $lv['days'])->toDateString();

            LeaveRequest::updateOrCreate(
                ['user_id' => $lv['user']->id, 'start_date' => $startDate],
                [
                    'end_date' => $endDate,
                    'total_days' => $lv['days'],
                    'type' => $lv['type'],
                    'reason' => $lv['reason'],
                    'status' => $lv['status'],
                    'approved_by' => $lv['status'] !== 'pending' ? $supervisor1->id : null,
                    'approval_note' => $lv['status'] === 'rejected' ? 'Mohon atur jadwal ulang minggu depan.' : null,
                ]
            );
        }

        // 7. Payslips (Bulan Oktober 2026 - FINAL, Bulan November 2026 - DRAFT)
        $periods = [
            ['period' => '2026-10', 'status' => PayslipStatus::FINAL, 'finalized' => true],
            ['period' => '2026-11', 'status' => PayslipStatus::DRAFT, 'finalized' => false],
        ];

        foreach ($periods as $p) {
            foreach ($employees as $emp) {
                $base = (float) $emp->base_salary;
                $otHours = rand(2, 8);
                $otPay = $otHours * 25000;
                $lateMin = rand(0, 45);
                $lateDed = $lateMin * 1000;
                $leaveDed = 0;
                $net = $base + $otPay - $lateDed - $leaveDed;

                $payslip = Payslip::updateOrCreate(
                    ['user_id' => $emp->id, 'period' => $p['period']],
                    [
                        'finalized_by' => $p['finalized'] ? $admin->id : null,
                        'base_salary' => $base,
                        'overtime_pay' => $otPay,
                        'late_deduction' => $lateDed,
                        'leave_deduction' => $leaveDed,
                        'net_salary' => $net,
                        'status' => $p['status'],
                        'finalized_at' => $p['finalized'] ? now()->subDays(20) : null,
                    ]
                );

                $payslip->items()->delete();
                $payslip->items()->createMany([
                    ['component' => 'Gaji Pokok', 'amount' => $base, 'type' => 'earning'],
                    ['component' => "Uang Lembur ({$otHours} jam)", 'amount' => $otPay, 'type' => 'earning'],
                    ['component' => "Potongan Terlambat ({$lateMin} menit)", 'amount' => $lateDed, 'type' => 'deduction'],
                ]);
            }
        }

        // 8. Sample Audit Logs
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'payroll.finalized',
            'model_type' => Payslip::class,
            'model_id' => 1,
            'old_values' => ['status' => 'draft'],
            'new_values' => ['status' => 'final', 'finalized_at' => now()->toDateTimeString()],
            'ip_address' => '127.0.0.1',
        ]);

        AuditLog::create([
            'user_id' => $supervisor1->id,
            'action' => 'attendance.corrected',
            'model_type' => Attendance::class,
            'model_id' => 1,
            'old_values' => ['clock_in' => '08:25:00', 'late_minutes' => 25],
            'new_values' => ['clock_in' => '08:00:00', 'late_minutes' => 0, 'correction_reason' => 'Sistem fingerprint offline'],
            'ip_address' => '192.168.1.105',
        ]);

        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'settings.updated',
            'model_type' => Setting::class,
            'model_id' => 3,
            'old_values' => ['value' => '50'],
            'new_values' => ['value' => '100'],
            'ip_address' => '127.0.0.1',
        ]);
    }
}
