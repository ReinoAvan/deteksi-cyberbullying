<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('log_activities', function (Blueprint $table) {
            $table->id();
            $table->string('student_id');
            $table->string('name');
            $table->decimal('response_time_mean', 12, 5)->default(0);
            $table->decimal('empathy_score', 12, 5)->default(0);
            $table->decimal('conformity_index', 12, 5)->default(0);
            $table->decimal('aggression_score', 12, 5)->default(0);
            $table->decimal('emotion_stability', 12, 5)->default(0);
            $table->decimal('anonymity_effect', 12, 5)->default(0);
            $table->decimal('final_empathy', 12, 5)->default(0);
            $table->decimal('risk_score', 12, 5)->default(0);
            $table->unsignedTinyInteger('risk_label')->default(0);
            $table->dateTime('last_update')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'risk_label']);
            $table->index('last_update');
            $table->foreign('student_id')->references('nis')->on('students')->cascadeOnUpdate()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('log_activities');
    }
};
