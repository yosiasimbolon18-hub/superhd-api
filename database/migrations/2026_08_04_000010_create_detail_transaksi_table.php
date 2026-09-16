<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('detail_transaksi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->constrained('transaksi')->cascadeOnDelete();
            $table->foreignId('produk_id')->constrained('produk');
            $table->string('nama_produk', 150);
            $table->decimal('harga_satuan', 12, 2);
            $table->unsignedInteger('qty');
            $table->decimal('subtotal', 12, 2);
        });
    }
    public function down(): void { Schema::dropIfExists('detail_transaksi'); }
};
