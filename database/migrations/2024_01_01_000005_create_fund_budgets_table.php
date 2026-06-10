<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_budgets', function (Blueprint $table) {
            $table->id();
            $table->enum('kategori', ['ormawa', 'lomba']);
            $table->string('nama_unit');
            $table->integer('triwulan');
            $table->decimal('total_dana', 15, 2);
            $table->decimal('sisa_dana', 15, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_budgets');
    }
};
