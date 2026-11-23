<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('finalized_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('period', 7);
            $table->decimal('base_salary', 15, 2);
            $table->decimal('overtime_pay', 15, 2)->default(0);
            $table->decimal('late_deduction', 15, 2)->default(0);
            $table->decimal('leave_deduction', 15, 2)->default(0);
            $table->decimal('net_salary', 15, 2);
            $table->enum('status', ['draft', 'final'])->default('draft');
            $table->timestamp('finalized_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'period']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
