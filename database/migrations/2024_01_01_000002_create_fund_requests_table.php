<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('tahun_ajaran');
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->string('jenis_kegiatan');
            $table->string('tingkat_kegiatan');
            $table->string('nama_kegiatan');
            $table->text('deskripsi');
            $table->string('proposal_file');
            $table->decimal('dana_diajukan', 15, 2);
            $table->decimal('dana_disetujui', 15, 2)->nullable();
            $table->string('status')->default('Belum Diproses');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_requests');
    }
};
