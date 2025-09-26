<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('logbook', function (Blueprint $table) {
            $table->id();
            $table->foreignId('magang_id')->constrained('magang')->onDelete('cascade');
            $table->date('tanggal');
            $table->text('kegiatan')->nullable();
            $table->text('kendala')->nullable();
            $table->string('file', 500)->nullable();
            $table->enum('status_verifikasi', ['pending', 'disetujui', 'ditolak'])->default('pending');
            $table->text('catatan_guru')->nullable();
            $table->text('catatan_dudi')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('logbook');
    }
};
