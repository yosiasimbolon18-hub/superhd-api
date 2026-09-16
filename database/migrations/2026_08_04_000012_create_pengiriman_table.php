<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('pengiriman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaksi_id')->unique()->constrained('transaksi')->cascadeOnDelete();
            $table->string('nomor_resi', 30)->nullable()->unique();
            $table->string('ekspedisi', 50)->nullable();
            $table->date('tanggal_kirim')->nullable();
            $table->string('status_pengiriman', 50)->nullable();
            $table->foreignId('input_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void { Schema::dropIfExists('pengiriman'); }
};
