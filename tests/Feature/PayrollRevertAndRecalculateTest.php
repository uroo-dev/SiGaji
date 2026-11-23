<?php

namespace Tests\Feature;

use App\Enums\PayslipStatus;
use App\Enums\UserRole;
use App\Jobs\ProcessMonthlyPayroll;
use App\Models\AuditLog;
use App\Models\Payslip;
use App\Models\User;
use App\Services\PayrollCalculatorService;
use App\Services\SettingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class PayrollRevertAndRecalculateTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $employee;
    protected string $period;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'email' => 'admin@sigaji.test',
        ]);

        $this->employee = User::factory()->create([
            'role' => UserRole::EMPLOYEE,
            'name' => 'Karyawan Test',
            'email' => 'karyawan@sigaji.test',
            'base_salary' => 5000000,
            'is_active' => true,
        ]);

        $this->period = now()->format('Y-m');
    }

    public function test_admin_can_revert_finalized_payroll_to_draft()
    {
        // Buat payslip final
        $payslip = Payslip::create([
            'user_id' => $this->employee->id,
            'period' => $this->period,
            'base_salary' => 5000000,
            'overtime_pay' => 0,
            'late_deduction' => 0,
            'leave_deduction' => 0,
            'net_salary' => 5000000,
            'status' => PayslipStatus::FINAL,
            'finalized_by' => $this->admin->id,
            'finalized_at' => now(),
        ]);

        $this->assertEquals(PayslipStatus::FINAL, $payslip->status);

        // Kirim request revert
        $response = $this->actingAs($this->admin)->post(route('admin.payroll.revert'), [
            'period' => $this->period,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Pastikan status kembali ke DRAFT dan finalized_* di-reset
        $payslip->refresh();
        $this->assertEquals(PayslipStatus::DRAFT, $payslip->status);
        $this->assertNull($payslip->finalized_by);
        $this->assertNull($payslip->finalized_at);

        // Pastikan tercatat di audit log
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'payslip.reverted',
            'model_id' => $payslip->id,
        ]);
    }

    public function test_generate_fails_without_recalculate_flag_when_period_is_final()
    {
        Payslip::create([
            'user_id' => $this->employee->id,
            'period' => $this->period,
            'base_salary' => 5000000,
            'overtime_pay' => 0,
            'late_deduction' => 0,
            'leave_deduction' => 0,
            'net_salary' => 5000000,
            'status' => PayslipStatus::FINAL,
            'finalized_by' => $this->admin->id,
            'finalized_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.payroll.generate'), [
            'period' => $this->period,
            'recalculate' => false,
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'can_recalculate' => true,
        ]);
    }

    public function test_generate_succeeds_with_recalculate_flag_even_when_final()
    {
        Queue::fake();

        Payslip::create([
            'user_id' => $this->employee->id,
            'period' => $this->period,
            'base_salary' => 5000000,
            'overtime_pay' => 0,
            'late_deduction' => 0,
            'leave_deduction' => 0,
            'net_salary' => 5000000,
            'status' => PayslipStatus::FINAL,
            'finalized_by' => $this->admin->id,
            'finalized_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)->postJson(route('admin.payroll.generate'), [
            'period' => $this->period,
            'recalculate' => true,
        ]);

        $response->assertStatus(200);

        Queue::assertPushed(ProcessMonthlyPayroll::class, function ($job) {
            return $job->period === $this->period && $job->recalculate === true;
        });
    }

    public function test_job_recalculates_payslip_and_sets_status_to_draft()
    {
        // Payslip lama berstatus final
        $payslip = Payslip::create([
            'user_id' => $this->employee->id,
            'period' => $this->period,
            'base_salary' => 5000000,
            'overtime_pay' => 0,
            'late_deduction' => 0,
            'leave_deduction' => 0,
            'net_salary' => 5000000,
            'status' => PayslipStatus::FINAL,
            'finalized_by' => $this->admin->id,
            'finalized_at' => now(),
        ]);

        // Jalankan Job secara sinkron dengan mode recalculate = true
        $job = new ProcessMonthlyPayroll($this->period, recalculate: true);
        $job->handle(app(SettingService::class), app(PayrollCalculatorService::class));

        $payslip->refresh();

        // Status harus kembali DRAFT dan finalized_* null
        $this->assertEquals(PayslipStatus::DRAFT, $payslip->status);
        $this->assertNull($payslip->finalized_by);
        $this->assertNull($payslip->finalized_at);
        $this->assertGreaterThan(0, $payslip->items()->count());
    }
}
