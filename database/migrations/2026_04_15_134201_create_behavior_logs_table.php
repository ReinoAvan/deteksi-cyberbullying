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
    Schema::create('behavior_logs', function (Blueprint $table) {
        $table->id();
        $table->string('student_name')->nullable();
        $table->date('date');
        $table->integer('level'); // 1, 2, 3
        $table->string('response'); // e.g., "Komentar mengejek"
        $table->string('indicator')->nullable(); // e.g., "Tekanan Sebaya"
        $table->integer('indicator_value')->default(0); // 1-5 untuk bar kecil
        $table->enum('risk_category', ['Rendah', 'Sedang', 'Tinggi']);
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('behavior_logs');
    }
};
