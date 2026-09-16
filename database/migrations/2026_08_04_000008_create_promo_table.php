<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('promo', function (Blueprint $table) {
            $table->id();
            $table->string('kode_voucher', 30)->unique();
            $table->string('deskripsi')->nullable();
            $table->enum('tipe', ['persen','nominal']);
            $table->decimal('nilai', 12, 2);
            $table->date('berlaku_dari')->nullable();
            $table->date('berlaku_sampai')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void { Schema::dropIfExists('promo'); }
};
