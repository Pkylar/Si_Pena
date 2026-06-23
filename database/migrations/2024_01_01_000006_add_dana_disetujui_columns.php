<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fund_requests', function (Blueprint $table) {
            $table->decimal('dana_disetujui_kemahasiswaan', 15, 2)->nullable()->after('dana_diajukan');
            $table->decimal('dana_disetujui_keuangan', 15, 2)->nullable()->after('dana_disetujui_kemahasiswaan');
        });
    }

    public function down(): void
    {
        Schema::table('fund_requests', function (Blueprint $table) {
            $table->dropColumn(['dana_disetujui_kemahasiswaan', 'dana_disetujui_keuangan']);
        });
    }
};
