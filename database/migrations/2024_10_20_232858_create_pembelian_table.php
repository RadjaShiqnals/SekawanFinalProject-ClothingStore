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
        Schema::create('pembelian', function (Blueprint $table) {
            $table->id('pembelian_id');
            $table->foreignId('pembelian_user_id')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('pembelian_metode_pembayaran_id')->constrained('metode_pembayaran', 'metode_pembayaran_id')->onDelete('cascade');
            $table->timestamp('pembelian_tanggal')->nullable(false);
            $table->integer('pembelian_total_harga')->nullable(false);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembelian');
    }
};
