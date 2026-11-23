<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payslip extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'payslips';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'user_id',
        'finalized_by',
        'period',
        'base_salary',
        'overtime_pay',
        'late_deduction',
        'leave_deduction',
        'net_salary',
        'status',
        'finalized_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'base_salary' => 'decimal:2',
            'overtime_pay' => 'decimal:2',
            'late_deduction' => 'decimal:2',
            'leave_deduction' => 'decimal:2',
            'net_salary' => 'decimal:2',
            'finalized_at' => 'datetime',
        ];
    }

    /**
     * Relasi ke user / karyawan pemilik slip gaji.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke admin yang memfinalisasi slip gaji.
     */
    public function finalizedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finalized_by');
    }

    /**
     * Relasi ke rincian komponen slip gaji.
     */
    public function items(): HasMany
    {
        return $this->hasMany(PayslipItem::class);
    }
}
