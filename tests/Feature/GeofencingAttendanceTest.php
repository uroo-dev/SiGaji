<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Setting;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GeofencingAttendanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_clock_in_succeeds_when_inside_allowed_radius(): void
    {
        $employee = User::where('email', 'dewi@sigaji.test')->first();

        // Hapus permanen absensi hari ini jika ada dari seeder
        Attendance::withTrashed()->where('user_id', $employee->id)->whereDate('date', now()->toDateString())->forceDelete();

        // Koordinat tepat di kantor (-6.917464, 107.619123)
        $response = $this->actingAs($employee)->postJson(route('employee.attendance.clock-in'), [
            'latitude' => -6.917464,
            'longitude' => 107.619123,
            'photo_base64' => 'data:image/jpeg;base64,' . base64_encode('fake-image-binary'),
        ]);

        $response->assertStatus(200);

        $this->assertTrue(
            Attendance::where('user_id', $employee->id)
                ->whereDate('date', now()->toDateString())
                ->exists()
        );
    }

    public function test_clock_in_fails_when_outside_allowed_radius(): void
    {
        $employee = User::where('email', 'dewi@sigaji.test')->first();

        // Hapus permanen absensi hari ini jika ada dari seeder
        Attendance::withTrashed()->where('user_id', $employee->id)->whereDate('date', now()->toDateString())->forceDelete();

        // Koordinat jauh di luar kantor (radius > 5 km)
        $response = $this->actingAs($employee)->postJson(route('employee.attendance.clock-in'), [
            'latitude' => -6.950000,
            'longitude' => 107.650000,
            'photo_base64' => 'data:image/jpeg;base64,' . base64_encode('fake-image-binary'),
        ]);

        // Harus gagal (422 Unprocessable Content) karena radius di luar batas
        $response->assertStatus(422);

        $this->assertDatabaseMissing('attendances', [
            'user_id' => $employee->id,
            'date' => now()->toDateString(),
            'latitude' => -6.950000,
        ]);
    }

    public function test_cannot_clock_in_twice_on_same_day(): void
    {
        $employee = User::where('email', 'dewi@sigaji.test')->first();

        // Hapus permanen absensi hari ini jika ada
        Attendance::withTrashed()->where('user_id', $employee->id)->whereDate('date', now()->toDateString())->forceDelete();

        // Clock-in pertama kali -> Berhasil
        $first = $this->actingAs($employee)->postJson(route('employee.attendance.clock-in'), [
            'latitude' => -6.917464,
            'longitude' => 107.619123,
            'photo_base64' => 'data:image/jpeg;base64,' . base64_encode('fake-image-binary'),
        ]);
        $first->assertStatus(200);

        // Clock-in kedua kali pada hari yang sama -> Gagal (422)
        $second = $this->actingAs($employee)->postJson(route('employee.attendance.clock-in'), [
            'latitude' => -6.917464,
            'longitude' => 107.619123,
            'photo_base64' => 'data:image/jpeg;base64,' . base64_encode('fake-image-binary'),
        ]);
        $second->assertStatus(422);
    }
}
