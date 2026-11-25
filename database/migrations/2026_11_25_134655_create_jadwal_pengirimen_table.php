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
        Schema::create('jadwal_pengirimen', function (Blueprint $table): void {
            $table->id();
            $table->string('kode_jadwal', 15)->unique();
            $table->foreignId('transaksi_penjualan_id')->nullable()->constrained('transaksi_penjualans')->cascadeOnDelete();
            $table->foreignId('pesanan_borongan_id')->nullable()->constrained('pesanan_borongans')->cascadeOnDelete();
            $table->string('alamat_pengiriman', 150);
            $table->date('tanggal_kirim');
            $table->string('jam_kirim', 5);
            $table->bigInteger('biaya_pengiriman')->default(0);
            $table->string('status_pengiriman', 20)->default('Terjadwal');
            $table->string('catatan_bentrok', 255)->nullable();
            $table->timestamps();

            $table->index(['tanggal_kirim', 'jam_kirim']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pengirimen');
    }
};
