<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('itemset_frequent', function (Blueprint $table) {
            $table->id();
            $table->string('run_id', 40); // pengelompokan 1 kali eksekusi analisis (uuid/timestamp)
            $table->json('produk_ids');   // contoh: [4,5]
            $table->unsignedTinyInteger('k'); // ukuran itemset (1,2,3,...)
            $table->decimal('support', 6, 4);
            $table->timestamp('created_at')->useCurrent();
        });
    }
    public function down(): void { Schema::dropIfExists('itemset_frequent'); }
};
