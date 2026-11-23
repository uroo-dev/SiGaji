<?php

namespace Tests\Feature;

use App\Enums\PayslipStatus;
use App\Enums\UserRole;
use App\Models\Payslip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportAndPayslipExportTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $employee1;
    protected User $employee2;
    protected Payslip $finalPayslip1;
    protected Payslip $finalPayslip2;
    protected Payslip $draftPayslip;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => UserRole::ADMIN,
            'email' => 'admin@sigaji.test',
        ]);

        $this->employee1 = User::factory()->create([
            'role' => UserRole::EMPLOYEE,
            'name' => 'Dewi Sartika',
            'email' => 'dewi@sigaji.test',
        ]);

        $this->employee2 = User::factory()->create([
            'role' => UserRole::EMPLOYEE,
            'name' => 'Ahmad Dahlan',
            'email' => 'ahmad@sigaji.test',
        ]);

        $period = now()->format('Y-m');

        // Finalized payslip for employee 1
        $this->finalPayslip1 = Payslip::create([
            'user_id' => $this->employee1->id,
            'period' => $period,
            'base_salary' => 6000000,
            'overtime_pay' => 300000,
            'late_deduction' => 50000,
            'leave_deduction' => 0,
            'net_salary' => 6250000,
            'status' => PayslipStatus::FINAL,
            'finalized_by' => $this->admin->id,
            'finalized_at' => now(),
        ]);

        // Finalized payslip for employee 2
        $this->finalPayslip2 = Payslip::create([
            'user_id' => $this->employee2->id,
            'period' => $period,
            'base_salary' => 7000000,
            'overtime_pay' => 0,
            'late_deduction' => 0,
            'leave_deduction' => 100000,
            'net_salary' => 6900000,
            'status' => PayslipStatus::FINAL,
            'finalized_by' => $this->admin->id,
            'finalized_at' => now(),
        ]);

        // Draft payslip
        $this->draftPayslip = Payslip::create([
            'user_id' => $this->employee1->id,
            'period' => '2025-01',
            'base_salary' => 6000000,
            'overtime_pay' => 0,
            'late_deduction' => 0,
            'leave_deduction' => 0,
            'net_salary' => 6000000,
            'status' => PayslipStatus::DRAFT,
        ]);
    }

    public function test_admin_can_download_payroll_report_as_excel()
    {
        $period = now()->format('Y-m');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.payroll', ['period' => $period]));

        $response->assertStatus(200);
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString("attachment; filename=\"laporan_payroll_{$period}.csv\"", $response->headers->get('Content-Disposition'));

        // Capture streamed response content
        ob_start();
        $response->sendContent();
        $content = ob_get_clean();

        // Must contain UTF-8 BOM
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('REKAPITULASI LAPORAN PAYROLL - SIGAJI', $content);
        $this->assertStringContainsString('Dewi Sartika', $content);
        $this->assertStringContainsString('Ahmad Dahlan', $content);
        $this->assertStringContainsString('6250000', $content);
    }

    public function test_admin_can_download_payroll_report_pdf_using_dompdf()
    {
        $period = now()->format('Y-m');

        $response = $this->actingAs($this->admin)
            ->get(route('admin.reports.pdf', ['period' => $period]));

        $response->assertStatus(200);
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertStringContainsString("filename=laporan_payroll_{$period}.pdf", $response->headers->get('Content-Disposition'));

        // Assert valid PDF binary
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_admin_can_download_employee_payslip_as_pdf_and_excel()
    {
        // PDF using Dompdf
        $pdfResponse = $this->actingAs($this->admin)
            ->get(route('admin.payroll.payslip.pdf', $this->finalPayslip1));

        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('filename=slip_gaji_', $pdfResponse->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());

        // Excel
        $excelResponse = $this->actingAs($this->admin)
            ->get(route('admin.payroll.payslip.excel', $this->finalPayslip1));

        $excelResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $excelResponse->headers->get('Content-Type'));

        ob_start();
        $excelResponse->sendContent();
        $content = ob_get_clean();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('SLIP GAJI KARYAWAN - SIGAJI', $content);
        $this->assertStringContainsString('Dewi Sartika', $content);
        $this->assertStringContainsString('6250000', $content);
    }

    public function test_employee_can_download_their_own_payslip_pdf_and_excel()
    {
        // PDF using Dompdf
        $pdfResponse = $this->actingAs($this->employee1)
            ->get(route('employee.payslips.pdf', $this->finalPayslip1));

        $pdfResponse->assertStatus(200);
        $this->assertEquals('application/pdf', $pdfResponse->headers->get('Content-Type'));
        $this->assertStringContainsString('filename=slip_gaji_', $pdfResponse->headers->get('Content-Disposition'));
        $this->assertStringStartsWith('%PDF-', $pdfResponse->getContent());

        // Excel
        $excelResponse = $this->actingAs($this->employee1)
            ->get(route('employee.payslips.excel', $this->finalPayslip1));

        $excelResponse->assertStatus(200);
        $this->assertStringContainsString('text/csv', $excelResponse->headers->get('Content-Type'));

        ob_start();
        $excelResponse->sendContent();
        $content = ob_get_clean();

        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
        $this->assertStringContainsString('Dewi Sartika', $content);
        $this->assertStringContainsString('6250000', $content);
    }

    public function test_employee_cannot_download_other_employee_payslip()
    {
        // Employee 1 trying to access Employee 2's payslip
        $pdfResponse = $this->actingAs($this->employee1)
            ->get(route('employee.payslips.pdf', $this->finalPayslip2));

        $pdfResponse->assertStatus(403);

        $excelResponse = $this->actingAs($this->employee1)
            ->get(route('employee.payslips.excel', $this->finalPayslip2));

        $excelResponse->assertStatus(403);
    }

    public function test_unfinalized_draft_payslip_cannot_be_downloaded()
    {
        $pdfResponse = $this->actingAs($this->employee1)
            ->get(route('employee.payslips.pdf', $this->draftPayslip));

        $pdfResponse->assertStatus(403);

        $excelResponse = $this->actingAs($this->employee1)
            ->get(route('employee.payslips.excel', $this->draftPayslip));

        $excelResponse->assertStatus(403);
    }
}
