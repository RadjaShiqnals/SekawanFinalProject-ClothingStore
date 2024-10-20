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
        Schema::create('pakaian', function (Blueprint $table) {
            $table->id('pakaian_id');
            $table->foreignId('pakaian_kategori_pakaian_id')->constrained('kategori_pakaian', 'kategori_pakaian_id')->onDelete('cascade');
            $table->string('pakaian_nama', 50)->nullable(false);
            $table->string('pakaian_harga', 50)->nullable(false);
            $table->string('pakaian_stok', 100)->nullable(false);
            $table->string('pakaian_gambar_url', 50)->nullable(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pakaian');
    }
};
