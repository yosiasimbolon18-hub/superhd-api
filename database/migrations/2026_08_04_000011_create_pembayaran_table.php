<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->unique()->constrained('transaksi')->cascadeOnDelete();
            $table->enum('metode', ['va_bca','va_mandiri','qris','cod','manual']);
            $table->enum('status', ['menunggu','dibayar','diverifikasi','ditolak'])->default('menunggu');
            $table->string('bukti_transfer')->nullable();
            $table->string('no_va', 30)->nullable();
            $table->string('midtrans_order_id')->nullable();
            $table->string('midtrans_transaction_id')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void { Schema::dropIfExists('pembayaran'); }
};
