<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('transaksi', function (Blueprint $table) {
            $table->id();
            $table->string('no_order', 30)->unique();
            $table->foreignId('pelanggan_id')->nullable()->constrained('pelanggan')->nullOnDelete();
            $table->foreignId('distributor_id')->nullable()->constrained('distributor')->nullOnDelete();
            $table->foreignId('promo_id')->nullable()->constrained('promo')->nullOnDelete();
            $table->string('nama_penerima', 100);
            $table->string('no_hp', 20);
            $table->string('email', 100)->nullable();
            $table->text('alamat_kirim');
            $table->string('catatan')->nullable();
            $table->decimal('subtotal', 12, 2);
            $table->decimal('diskon_voucher', 12, 2)->default(0);
            $table->decimal('ongkir', 12, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->enum('status', ['menunggu_pembayaran','diproses','dikemas','dikirim','selesai'])
                  ->default('menunggu_pembayaran');
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('transaksi'); }
};
