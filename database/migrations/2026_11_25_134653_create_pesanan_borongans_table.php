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
        Schema::create('pesanan_borongans', function (Blueprint $table): void {
            $table->id();
            $table->string('kode_borongan', 15)->unique();
            $table->foreignId('pelanggan_id')->constrained('pelanggans');
            $table->foreignId('user_id')->constrained('users');
            $table->date('tanggal_mulai')->index();
            $table->date('tanggal_target');
            $table->string('lokasi_proyek', 150);
            $table->bigInteger('biaya_jasa')->default(0);
            $table->bigInteger('biaya_pengiriman')->default(0);
            $table->bigInteger('total_biaya')->default(0);
            $table->string('status_proyek', 20)->default('Menunggu');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan_borongans');
    }
};
