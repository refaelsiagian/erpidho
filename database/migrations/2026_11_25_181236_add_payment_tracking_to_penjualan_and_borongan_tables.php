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
        Schema::table('transaksi_penjualans', function (Blueprint $table): void {
            $table->date('tanggal_lunas')->nullable()->after('status_pembayaran')->index();
        });

        Schema::table('pesanan_borongans', function (Blueprint $table): void {
            $table->string('metode_bayar', 15)->default('Tunai')->after('total_biaya');
            $table->string('status_pembayaran', 15)->default('Belum Lunas')->after('metode_bayar');
            $table->date('tanggal_lunas')->nullable()->after('status_pembayaran')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_penjualans', function (Blueprint $table): void {
            $table->dropIndex(['tanggal_lunas']);
            $table->dropColumn('tanggal_lunas');
        });

        Schema::table('pesanan_borongans', function (Blueprint $table): void {
            $table->dropIndex(['tanggal_lunas']);
            $table->dropColumn(['metode_bayar', 'status_pembayaran', 'tanggal_lunas']);
        });
    }
};
