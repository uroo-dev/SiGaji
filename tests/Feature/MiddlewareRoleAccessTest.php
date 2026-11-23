<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MiddlewareRoleAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_is_redirected_to_login_from_admin_urls(): void
    {
        $response = $this->get(route('admin.dashboard'));
        $response->assertRedirect('/login');

        $response2 = $this->get(route('admin.payroll.index'));
        $response2->assertRedirect('/login');
    }

    public function test_employee_cannot_access_admin_urls(): void
    {
        $employee = User::where('email', 'dewi@sigaji.test')->first();

        // Karyawan mengakses dashboard admin -> 403 Forbidden
        $response = $this->actingAs($employee)->get(route('admin.dashboard'));
        $response->assertStatus(403);

        // Karyawan mengakses payroll admin -> 403 Forbidden
        $response2 = $this->actingAs($employee)->get(route('admin.payroll.index'));
        $response2->assertStatus(403);

        // Karyawan mengakses settings admin -> 403 Forbidden
        $response3 = $this->actingAs($employee)->get(route('admin.settings.index'));
        $response3->assertStatus(403);
    }

    public function test_employee_cannot_access_supervisor_urls(): void
    {
        $employee = User::where('email', 'dewi@sigaji.test')->first();

        // Karyawan mengakses approval lembur supervisor -> 403 Forbidden
        $response = $this->actingAs($employee)->get(route('supervisor.overtime.index'));
        $response->assertStatus(403);
    }

    public function test_supervisor_cannot_access_admin_urls(): void
    {
        $supervisor = User::where('email', 'supervisor@sigaji.test')->first();

        // Supervisor mengakses setting admin -> 403 Forbidden
        $response = $this->actingAs($supervisor)->get(route('admin.settings.index'));
        $response->assertStatus(403);
    }

    public function test_admin_can_access_admin_urls(): void
    {
        $admin = User::where('email', 'admin@sigaji.test')->first();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));
        $response->assertStatus(200);

        $response2 = $this->actingAs($admin)->get(route('admin.settings.index'));
        $response2->assertStatus(200);
    }
}
