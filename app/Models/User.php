<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'base_salary',
        'is_active',
        'standard_clock_in',
        'annual_leave_quota',
        'joined_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'base_salary' => 'decimal:2',
            'annual_leave_quota' => 'integer',
            'joined_at' => 'date',
        ];
    }

    /**
     * Scope query berdasarkan role user.
     */
    public function scopeRole($query, UserRole|string $role)
    {
        $roleValue = $role instanceof UserRole ? $role->value : $role;
        return $query->where('role', $roleValue);
    }

    /**
     * Relasi ke absensi karyawan.
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Relasi ke pengajuan lembur yang diajukan karyawan.
     */
    public function overtimeRequests(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class, 'user_id');
    }

    /**
     * Relasi ke pengajuan lembur yang disetujui atasan.
     */
    public function approvedOvertimes(): HasMany
    {
        return $this->hasMany(OvertimeRequest::class, 'approved_by');
    }

    /**
     * Relasi ke pengajuan cuti yang diajukan karyawan.
     */
    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'user_id');
    }

    /**
     * Relasi ke pengajuan cuti yang disetujui atasan.
     */
    public function approvedLeaves(): HasMany
    {
        return $this->hasMany(LeaveRequest::class, 'approved_by');
    }

    /**
     * Relasi ke slip gaji karyawan.
     */
    public function payslips(): HasMany
    {
        return $this->hasMany(Payslip::class, 'user_id');
    }

    /**
     * Relasi ke slip gaji yang difinalisasi oleh admin.
     */
    public function finalizedPayslips(): HasMany
    {
        return $this->hasMany(Payslip::class, 'finalized_by');
    }

    /**
     * Relasi ke log audit aktivitas user.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
}
