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
        Schema::create('transaksi_penjualans', function (Blueprint $table): void {
            $table->id();
            $table->string('kode_transaksi', 15)->unique();
            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggans')->nullOnDelete();
            $table->foreignId('user_id')->constrained('users');
            $table->dateTime('tanggal')->index();
            $table->bigInteger('biaya_pengiriman')->default(0);
            $table->bigInteger('total_bayar')->default(0);
            $table->string('metode_bayar', 15)->default('Tunai');
            $table->string('status_pembayaran', 15)->default('Lunas');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksi_penjualans');
    }
};
