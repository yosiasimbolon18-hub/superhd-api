    <?php

    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Api\AuthController;
    use App\Http\Controllers\Api\ProdukController;
    use App\Http\Controllers\Api\CheckoutController;
    use App\Http\Controllers\Api\PaymentController;
    use App\Http\Controllers\Api\DistributorController;
    use App\Http\Controllers\Api\ShopeeOngkirController;
    use App\Http\Controllers\Api\RecommendationController;
    use App\Http\Controllers\Api\Admin\PesananController;
    use App\Http\Controllers\Api\Admin\KategoriController;
    use App\Http\Controllers\Api\Admin\PromoController;
    use App\Http\Controllers\Api\Admin\ArtikelController;
    use App\Http\Controllers\Api\Admin\DistributorController as AdminDistributorController;
    use App\Http\Controllers\Api\Admin\UserController;
    use App\Http\Controllers\Api\Admin\AuditLogController;
    use App\Http\Controllers\Api\Admin\LaporanController;
    use App\Http\Controllers\Api\Admin\AprioriController;

    // ---------- Publik ----------
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:5,1');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');

    Route::get('/produk', [ProdukController::class, 'index']);
    Route::get('/produk/{id}', [ProdukController::class, 'show']);
    Route::get('/produk/{id}/rekomendasi', [RecommendationController::class, 'forProduct']);
    Route::post('/produk/rekomendasi-keranjang', [RecommendationController::class, 'forCart']); // "Cocok Ditambahkan Juga"
    Route::get('/kategori', [KategoriController::class, 'index']);
    Route::get('/artikel', [ArtikelController::class, 'index']);

    // ---------- Publik atau Terautentikasi (Pindahkan ke luar grup admin) ----------
    Route::get('/shopee-ongkir/wilayah', [ShopeeOngkirController::class, 'getWilayah']);
    Route::post('/shopee-ongkir/hitung', [ShopeeOngkirController::class, 'hitungOngkir']);

    // Rute Store Produk dipindah ke sini (Bebas dari middleware admin/sanctum untuk testing)
    Route::post('/admin/produk', [ProdukController::class, 'store']);

    Route::post('/distributor/register', [DistributorController::class, 'register'])->middleware('throttle:5,1');

    Route::post('/ongkir/cek', [OngkirController::class, 'cek']);
    Route::get('/ongkir/kota', [OngkirController::class, 'kota']);

    Route::post('/payment/notification', [PaymentController::class, 'notification']);

    // ---------- Perlu login (Sanctum) ----------
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);

        Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1');
        Route::post('/checkout/{transaksiId}/cod', [CheckoutController::class, 'confirmCod']); // konfirmasi COD sungguhan
        Route::get('/pesanan-saya', [CheckoutController::class, 'myOrders']);
        Route::post('/payment/{transaksiId}/charge', [PaymentController::class, 'charge']);

        // ---------- Admin only ----------
        Route::prefix('admin')->middleware('admin')->group(function () {
            Route::get('/pesanan', [PesananController::class, 'index']);
            Route::post('/pesanan/{id}/verifikasi', [PesananController::class, 'verifikasiPembayaran']);
            Route::post('/pesanan/{id}/status', [PesananController::class, 'updateStatus']);
            Route::post('/pesanan/{id}/resi', [PesananController::class, 'inputResi']);

            // Rute produk di dalam grup admin hanya untuk update & delete saja
            Route::put('/produk/{id}', [ProdukController::class, 'update']);
            Route::post('/produk/{id}', [ProdukController::class, 'update']);
            Route::delete('/produk/{id}', [ProdukController::class, 'destroy']);

            Route::apiResource('kategori', KategoriController::class)->except(['show']);

            Route::get('/promo', [PromoController::class, 'index']);
            Route::post('/promo', [PromoController::class, 'store']);
            Route::post('/promo/{id}/toggle', [PromoController::class, 'toggle']);
            Route::delete('/promo/{id}', [PromoController::class, 'destroy']);

            Route::get('/artikel', [ArtikelController::class, 'index']);
            Route::post('/artikel', [ArtikelController::class, 'store']);
            Route::delete('/artikel/{id}', [ArtikelController::class, 'destroy']);

            Route::get('/distributor', [AdminDistributorController::class, 'index']);
            Route::post('/distributor/{id}/approve', [AdminDistributorController::class, 'approve']);
            Route::post('/distributor/{id}/reject', [AdminDistributorController::class, 'reject']);
            Route::post('/distributor/{id}/kirim-ulang', [AdminDistributorController::class, 'kirimUlangKredensial']);

            Route::get('/users', [UserController::class, 'index']);
            Route::post('/users', [UserController::class, 'store']);
            Route::put('/users/{id}', [UserController::class, 'update']);
            Route::post('/users/{id}/reset-password', [UserController::class, 'resetPassword']);
            Route::delete('/users/{id}', [UserController::class, 'destroy']);

            Route::get('/audit-log', [AuditLogController::class, 'index']);

            Route::get('/laporan/pdf', [LaporanController::class, 'exportPdf']);
            Route::get('/laporan/excel', [LaporanController::class, 'exportExcel']);

            Route::get('/apriori/jalankan', [AprioriController::class, 'jalankan']); // hitung ULANG + simpan ke DB
            Route::get('/apriori/hasil', [AprioriController::class, 'hasil']);       // ambil hasil TERSIMPAN (tanpa hitung ulang)
            Route::get('/apriori/simulasi', [AprioriController::class, 'simulasi']); // dummy, khusus dokumentasi Bab III


        
        });
    });