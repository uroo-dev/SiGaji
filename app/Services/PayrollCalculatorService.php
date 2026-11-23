<?php

namespace App\Services;

class PayrollCalculatorService
{
    /**
     * Hitung total uang lembur berdasarkan jam lembur dan tarif per jam.
     */
    public function calculateOvertimePay(float $hours, float $hourlyRate): float
    {
        if ($hours <= 0 || $hourlyRate <= 0) {
            return 0.0;
        }

        return round($hours * $hourlyRate, 2);
    }

    /**
     * Hitung total denda keterlambatan berdasarkan menit dan tarif per menit.
     */
    public function calculateLateDeduction(int $lateMinutes, float $ratePerMinute): float
    {
        if ($lateMinutes <= 0 || $ratePerMinute <= 0) {
            return 0.0;
        }

        return round($lateMinutes * $ratePerMinute, 2);
    }

    /**
     * Hitung potongan cuti unpaid (tanpa gaji) berdasarkan jumlah hari.
     * Rumus: (baseSalary / 22 hari kerja standar) * unpaidDays
     */
    public function calculateLeaveDeduction(float $baseSalary, int $unpaidDays, int $workDaysPerMonth = 22): float
    {
        if ($baseSalary <= 0 || $unpaidDays <= 0 || $workDaysPerMonth <= 0) {
            return 0.0;
        }

        $dailySalary = $baseSalary / $workDaysPerMonth;
        return round($unpaidDays * $dailySalary, 2);
    }

    /**
     * Hitung Take Home Pay (Gaji Bersih).
     * Net = max(0, BaseSalary + OvertimePay - LateDeduction - LeaveDeduction)
     */
    public function calculateNetSalary(
        float $baseSalary,
        float $overtimePay,
        float $lateDeduction,
        float $leaveDeduction
    ): float {
        $net = $baseSalary + $overtimePay - $lateDeduction - $leaveDeduction;
        return max(0.0, round($net, 2));
    }
}
