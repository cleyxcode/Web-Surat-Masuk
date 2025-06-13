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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['admin', 'karyawan'])->default('karyawan');
            $table->string('nip')->nullable();
            $table->string('phone')->nullable();
            $table->string('position')->nullable(); // jabatan
            $table->string('division')->nullable(); // divisi
            $table->boolean('is_active')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'nip', 'phone', 'position', 'division', 'is_active']);
        });
    }
};
