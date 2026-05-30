<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        DB::table('student_classes')->insertOrIgnore([
            ['name' => '5A', 'created_at' => now(), 'updated_at' => now()],
            ['name' => '5B', 'created_at' => now(), 'updated_at' => now()],
        ]);

        DB::statement("ALTER TABLE students MODIFY gender ENUM('Laki-laki', 'Perempuan', 'Male', 'Female') NOT NULL DEFAULT 'Male'");
        DB::table('students')->where('gender', 'Laki-laki')->update(['gender' => 'Male']);
        DB::table('students')->where('gender', 'Perempuan')->update(['gender' => 'Female']);
        DB::statement("ALTER TABLE students MODIFY gender ENUM('Male', 'Female') NOT NULL DEFAULT 'Male'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE students MODIFY gender ENUM('Laki-laki', 'Perempuan', 'Male', 'Female') NOT NULL DEFAULT 'Laki-laki'");
        DB::table('students')->where('gender', 'Male')->update(['gender' => 'Laki-laki']);
        DB::table('students')->where('gender', 'Female')->update(['gender' => 'Perempuan']);
        DB::statement("ALTER TABLE students MODIFY gender ENUM('Laki-laki', 'Perempuan') NOT NULL");

        Schema::dropIfExists('student_classes');
    }
};
