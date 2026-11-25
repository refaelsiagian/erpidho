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
        Schema::create('detail_borongans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('pesanan_borongan_id')->constrained('pesanan_borongans')->cascadeOnDelete();
            $table->foreignId('material_id')->constrained('materials');
            $table->integer('jumlah_material');
            $table->bigInteger('harga_satuan');
            $table->bigInteger('subtotal_material');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detail_borongans');
    }
};
