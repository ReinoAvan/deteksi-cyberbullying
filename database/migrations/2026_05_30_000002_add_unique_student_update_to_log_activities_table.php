<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            DELETE la1 FROM log_activities la1
            INNER JOIN log_activities la2
                ON la1.student_id = la2.student_id
                AND la1.last_update = la2.last_update
                AND la1.id > la2.id
        ');

        Schema::table('log_activities', function (Blueprint $table) {
            $table->unique(['student_id', 'last_update'], 'log_activities_student_update_unique');
        });
    }

    public function down(): void
    {
        Schema::table('log_activities', function (Blueprint $table) {
            $table->dropUnique('log_activities_student_update_unique');
        });
    }
};
