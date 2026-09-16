<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('produk', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategori');
            $table->string('sku', 30)->unique();
            $table->string('nama_produk', 150);
            $table->text('deskripsi')->nullable();
            $table->text('cara_pakai')->nullable();
            $table->text('spesifikasi')->nullable();
            $table->text('komposisi')->nullable();
            $table->string('foto')->nullable();
            $table->decimal('harga', 12, 2);
            $table->decimal('harga_distributor', 12, 2);
            $table->unsignedInteger('min_order_dist')->default(1);
            $table->unsignedTinyInteger('diskon_persen')->default(0);
            $table->unsignedInteger('stok')->default(0);
            $table->decimal('berat_kg', 6, 2)->default(0);
            $table->unsignedTinyInteger('daya_angkat')->default(0);
            $table->unsignedInteger('terjual')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('produk'); }
};
