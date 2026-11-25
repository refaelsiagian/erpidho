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
        Schema::create('penggajian_tukangs', function (Blueprint $table): void {
            $table->id();
            $table->string('kode_gaji', 15)->unique();
            $table->foreignId('karyawan_tukang_id')->constrained('karyawan_tukangs');
            $table->foreignId('pesanan_borongan_id')->constrained('pesanan_borongans');
            $table->date('tanggal_diproses')->index();
            $table->bigInteger('total_gaji')->default(0);
            $table->string('status_bayar', 15)->default('Belum Dibayar');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('penggajian_tukangs');
    }
};
