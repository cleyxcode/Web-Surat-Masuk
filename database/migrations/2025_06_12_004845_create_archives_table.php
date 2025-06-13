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
        Schema::create('archives', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_type');
            $table->integer('file_size'); // in bytes
            $table->date('document_date');
            $table->string('document_number')->nullable();
            $table->foreignId('uploaded_by')->constrained('users');
            $table->integer('download_count')->default(0);
            $table->boolean('is_public')->default(false);
            $table->text('tags')->nullable();
            $table->timestamps();

            $table->index(['title', 'category', 'document_date']);
            $table->index('document_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('archives');
    }
};
