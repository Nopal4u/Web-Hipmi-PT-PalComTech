<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('divisions', function (Blueprint $table) {
            $table->id();
            $table->string('numeral', 8);                 // I, II, III ...
            $table->string('name');                        // nama lengkap bidang
            $table->string('short_name', 60);              // label tab / kartu
            $table->string('color', 7)->default('#2563EB');// warna aksen
            $table->text('icon')->nullable();              // path SVG
            $table->text('focus')->nullable();             // fokus bidang
            $table->text('learning')->nullable();          // apa yang dipelajari
            $table->string('leader_name')->nullable();     // ketua divisi
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('divisions');
    }
};
