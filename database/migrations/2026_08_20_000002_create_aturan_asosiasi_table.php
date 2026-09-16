<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('aturan_asosiasi', function (Blueprint $table) {
            $table->id();
            $table->string('run_id', 40);
            $table->json('antecedent');   // produk_ids sisi "jika"
            $table->json('consequent');   // produk_ids sisi "maka"
            $table->decimal('support', 6, 4);
            $table->decimal('confidence', 6, 4);
            $table->decimal('lift', 8, 4);
            $table->boolean('is_active')->default(true); // hasil run terbaru yang dipakai sistem
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void { Schema::dropIfExists('aturan_asosiasi'); }
};
