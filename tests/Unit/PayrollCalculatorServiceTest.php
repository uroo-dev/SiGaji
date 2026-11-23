<?php

namespace Tests\Unit;

use App\Services\PayrollCalculatorService;
use PHPUnit\Framework\TestCase;

class PayrollCalculatorServiceTest extends TestCase
{
    private PayrollCalculatorService $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = new PayrollCalculatorService();
    }

    public function test_calculate_overtime_pay_accurately(): void
    {
        // 4 jam lembur x Rp 25.000 / jam = Rp 100.000
        $result = $this->calculator->calculateOvertimePay(4.0, 25000);
        $this->assertEquals(100000.0, $result);

        // 2.5 jam lembur x Rp 30.000 / jam = Rp 75.000
        $result2 = $this->calculator->calculateOvertimePay(2.5, 30000);
        $this->assertEquals(75000.0, $result2);

        // Jam 0 atau negatif menghasilkan 0
        $this->assertEquals(0.0, $this->calculator->calculateOvertimePay(0, 25000));
        $this->assertEquals(0.0, $this->calculator->calculateOvertimePay(-2, 25000));
    }

    public function test_calculate_late_deduction_accurately(): void
    {
        // 45 menit terlambat x Rp 1.000 / menit = Rp 45.000
        $result = $this->calculator->calculateLateDeduction(45, 1000);
        $this->assertEquals(45000.0, $result);

        // 0 menit telat
        $this->assertEquals(0.0, $this->calculator->calculateLateDeduction(0, 1000));
    }

    public function test_calculate_leave_deduction_accurately(): void
    {
        // Gaji Rp 4.400.000, 2 hari unpaid leave (22 hari kerja) -> (4.400.000 / 22) * 2 = Rp 400.000
        $result = $this->calculator->calculateLeaveDeduction(4400000, 2, 22);
        $this->assertEquals(400000.0, $result);

        // 0 hari unpaid leave
        $this->assertEquals(0.0, $this->calculator->calculateLeaveDeduction(4400000, 0, 22));
    }

    public function test_calculate_net_salary_accurately(): void
    {
        // Base: 4.500.000, Lembur: 150.000, Potongan Telat: 50.000, Potongan Cuti: 200.000
        // Net: 4.500.000 + 150.000 - 50.000 - 200.000 = 4.400.000
        $net = $this->calculator->calculateNetSalary(4500000, 150000, 50000, 200000);
        $this->assertEquals(4400000.0, $net);
    }

    public function test_net_salary_cannot_be_negative(): void
    {
        // Jika potongan melebihi pendapatan, gaji bersih tidak boleh minus (minimum 0)
        $net = $this->calculator->calculateNetSalary(1000000, 0, 800000, 500000);
        $this->assertEquals(0.0, $net);
    }
}
