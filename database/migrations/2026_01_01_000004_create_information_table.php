<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('information', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            // Open Recruitment | Agenda & Event | Pengumuman
            $table->string('category', 40)->default('Pengumuman');
            $table->string('date_label', 80)->nullable();
            // buka | segera | tutup
            $table->string('status', 20)->default('buka');
            $table->string('summary', 400)->nullable();
            $table->text('description')->nullable();
            $table->string('action_label', 40)->default('Daftar Sekarang');
            $table->string('action_url')->nullable();
            $table->string('banner')->nullable();
            $table->boolean('is_pinned')->default(false);
            $table->boolean('is_hidden')->default(false);
            $table->timestamps();

            $table->index(['category', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('information');
    }
};
