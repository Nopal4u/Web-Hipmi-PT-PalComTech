<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('division_id')->nullable()->constrained()->nullOnDelete();
            // dosen | bph | division
            $table->string('group', 20)->default('division');
            $table->string('name');
            $table->string('position')->nullable();          // jabatan
            // mahasiswa | dosen
            $table->string('profile_type', 20)->default('mahasiswa');
            $table->string('photo')->nullable();
            $table->string('expertise')->nullable();         // keahlian (dipisah koma)
            $table->string('study_program')->nullable();
            $table->string('year', 8)->nullable();           // angkatan
            $table->string('faculty')->nullable();
            $table->string('nidn')->nullable();
            $table->string('business')->nullable();
            $table->string('cv_url')->nullable();
            $table->string('portfolio_url')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('instagram')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['group', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
