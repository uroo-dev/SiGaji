<?php

namespace App\Jobs;

use App\Enums\AttendanceStatus;
use App\Enums\PayslipStatus;
use App\Enums\UserRole;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\Payslip;
use App\Models\PayslipItem;
use App\Models\User;
use App\Services\PayrollCalculatorService;
use App\Services\SettingService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class ProcessMonthlyPayroll implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public string $period,
        public bool $recalculate = false
    ) {}

    /**
     * Execute the job.
     */
    public function handle(SettingService $settingService, PayrollCalculatorService $calculator): void
    {
        try {
            $employees = User::role(UserRole::EMPLOYEE)->where('is_active', true)->get();
            $total = $employees->count();

            if ($total === 0) {
                Cache::put("payroll.progress.{$this->period}", [
                    'total' => 0,
                    'completed' => 0,
                    'status' => 'done',
                ], 3600);
                return;
            }

            Cache::put("payroll.progress.{$this->period}", [
                'total' => $total,
                'completed' => 0,
                'status' => 'processing',
            ], 3600);

            $lateRatePerMinute = (float) $settingService->get('late_deduction_per_minute', 1000);
            $hourlyOvertimeRate = (float) $settingService->get('overtime_hourly_rate', 25000);
            $completed = 0;

            foreach ($employees as $employee) {
                $existing = Payslip::where('user_id', $employee->id)->where('period', $this->period)->first();
                if ($existing && $existing->status === PayslipStatus::FINAL && ! $this->recalculate) {
                    $completed++;
                    continue;
                }

                // Hitung keterlambatan
                $totalLateMinutes = (int) Attendance::where('user_id', $employee->id)
                    ->where('date', 'like', "{$this->period}%")
                    ->sum('late_minutes');

                $lateDeduction = $calculator->calculateLateDeduction($totalLateMinutes, $lateRatePerMinute);

                // Hitung lembur disetujui
                $totalOvertimeHours = (float) OvertimeRequest::where('user_id', $employee->id)
                    ->where('date', 'like', "{$this->period}%")
                    ->where('status', 'approved')
                    ->sum('hours');

                $overtimePay = $calculator->calculateOvertimePay($totalOvertimeHours, $hourlyOvertimeRate);

                // Hitung potongan unpaid leave
                $unpaidDays = (int) LeaveRequest::where('user_id', $employee->id)
                    ->where('type', 'unpaid')
                    ->where('status', 'approved')
                    ->where('start_date', 'like', "{$this->period}%")
                    ->sum('total_days');

                $leaveDeduction = $calculator->calculateLeaveDeduction((float) $employee->base_salary, $unpaidDays);

                // Hitung Gaji Bersih
                $netSalary = $calculator->calculateNetSalary(
                    (float) $employee->base_salary,
                    $overtimePay,
                    $lateDeduction,
                    $leaveDeduction
                );

                $payslip = Payslip::updateOrCreate(
                    ['user_id' => $employee->id, 'period' => $this->period],
                    [
                        'base_salary' => $employee->base_salary,
                        'overtime_pay' => $overtimePay,
                        'late_deduction' => $lateDeduction,
                        'leave_deduction' => $leaveDeduction,
                        'net_salary' => $netSalary,
                        'status' => PayslipStatus::DRAFT,
                        'finalized_by' => null,
                        'finalized_at' => null,
                    ]
                );

                // Re-generate itemized components
                $payslip->items()->delete();
                $payslip->items()->createMany([
                    ['component' => 'Gaji Pokok', 'amount' => $employee->base_salary, 'type' => 'earning'],
                    ['component' => "Uang Lembur ({$totalOvertimeHours} jam)", 'amount' => $overtimePay, 'type' => 'earning'],
                    ['component' => "Potongan Terlambat ({$totalLateMinutes} menit)", 'amount' => $lateDeduction, 'type' => 'deduction'],
                    ['component' => "Potongan Unpaid Leave ({$unpaidDays} hari)", 'amount' => $leaveDeduction, 'type' => 'deduction'],
                ]);

                $completed++;
                Cache::put("payroll.progress.{$this->period}", [
                    'total' => $total,
                    'completed' => $completed,
                    'status' => $completed >= $total ? 'done' : 'processing',
                ], 3600);
            }
        } catch (\Throwable $e) {
            Log::error("ProcessMonthlyPayroll failed for period {$this->period}: " . $e->getMessage());
            Cache::put("payroll.progress.{$this->period}", [
                'total' => $total ?? 0,
                'completed' => $completed ?? 0,
                'status' => 'failed',
                'error' => $e->getMessage(),
            ], 3600);
            throw $e;
        }
    }
}
