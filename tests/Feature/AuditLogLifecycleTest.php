<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\OvertimeRequest;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_audit_log_records_login_activity()
    {
        $response = $this->post(route('login'), [
            'email' => 'admin@sigaji.test',
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'auth.login',
        ]);
    }

    public function test_audit_log_records_employee_creation()
    {
        $admin = User::where('email', 'admin@sigaji.test')->first();

        $response = $this->actingAs($admin)->post(route('admin.employees.store'), [
            'name' => 'Karyawan Baru Test',
            'email' => 'baru@sigaji.test',
            'password' => 'password123',
            'base_salary' => 5000000,
            'joined_at' => now()->toDateString(),
        ]);

        $response->assertRedirect(route('admin.employees.index'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'employee.created',
        ]);
    }

    public function test_audit_log_records_overtime_submission_and_approval()
    {
        $dewi = User::where('email', 'dewi@sigaji.test')->first();
        $supervisor = User::where('email', 'supervisor@sigaji.test')->first();

        // 1. Employee submits overtime
        $response = $this->actingAs($dewi)->post(route('employee.overtimes.store'), [
            'date' => now()->toDateString(),
            'start_time' => '17:00',
            'end_time' => '20:00',
            'reason' => 'Audit test lembur',
        ]);

        $response->assertRedirect(route('employee.overtimes.index'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'overtime.submitted',
        ]);

        $overtime = OvertimeRequest::where('reason', 'Audit test lembur')->first();
        $this->assertNotNull($overtime);

        // 2. Supervisor / Admin approves overtime
        $approveResponse = $this->actingAs($supervisor)->post(route('supervisor.overtime.approve', $overtime));
        $approveResponse->assertRedirect();

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'overtime.approved',
        ]);
    }

    public function test_audit_log_records_leave_submission()
    {
        $dewi = User::where('email', 'dewi@sigaji.test')->first();

        $response = $this->actingAs($dewi)->post(route('employee.leaves.store'), [
            'type' => 'sick',
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'reason' => 'Sakit flu test',
        ]);

        $response->assertRedirect(route('employee.leaves.index'));

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'leave.submitted',
        ]);
    }
}
