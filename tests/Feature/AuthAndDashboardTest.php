<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthAndDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_admin_can_access_all_admin_views()
    {
        $admin = User::where('email', 'admin@sigaji.test')->first();

        $routes = [
            'admin.dashboard',
            'admin.employees.index',
            'admin.attendances.index',
            'admin.overtime.index',
            'admin.leave.index',
            'admin.payroll.index',
            'admin.reports.index',
            'admin.settings.index',
            'admin.audit-logs.index',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($admin)->get(route($route));
            $response->assertStatus(200);
        }
    }

    public function test_admin_can_download_payroll_recap_excel()
    {
        $admin = User::where('email', 'admin@sigaji.test')->first();
        $response = $this->actingAs($admin)->get(route('admin.reports.payroll', ['period' => '2026-11']));
        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
    }

    public function test_supervisor_can_access_all_supervisor_views()
    {
        $supervisor = User::where('email', 'supervisor@sigaji.test')->first();

        $routes = [
            'supervisor.dashboard',
            'supervisor.overtime.index',
            'supervisor.leave.index',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($supervisor)->get(route($route));
            $response->assertStatus(200);
        }
    }

    public function test_employee_can_access_all_employee_views()
    {
        $dewi = User::where('email', 'dewi@sigaji.test')->first();

        $routes = [
            'employee.dashboard',
            'employee.attendances.index',
            'employee.overtimes.index',
            'employee.leaves.index',
            'employee.payslips.index',
        ];

        foreach ($routes as $route) {
            $response = $this->actingAs($dewi)->get(route($route));
            $response->assertStatus(200);
        }
    }
}
