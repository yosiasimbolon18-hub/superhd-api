<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('distributor', function (Blueprint $table) {
            $table->id();
            $table->string('nama_perusahaan', 150);
            $table->string('nama_pic', 100);
            $table->string('no_hp', 20);
            $table->text('alamat');
            $table->string('npwp', 30)->nullable();
            $table->string('username', 50)->nullable()->unique();
            $table->string('password')->nullable();
            $table->enum('status', ['pending','approved','rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('distributor'); }
};
