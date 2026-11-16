<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('business_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('category', 60);
            $table->text('description');
            $table->string('founder');
            $table->string('study_program', 60);
            $table->string('year', 8);
            $table->string('nim', 30);
            $table->string('whatsapp', 20);
            $table->string('instagram', 60)->nullable();
            // menunggu | disetujui | ditolak
            $table->string('status', 20)->default('menunggu');
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->json('photos')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_submissions');
    }
};
