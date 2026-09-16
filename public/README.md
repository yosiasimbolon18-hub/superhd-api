# PT Super HD Kliner — Prototipe Sistem Informasi Penjualan Online

Prototipe front-end (HTML/CSS/JavaScript murni, tanpa backend/database) untuk mendemonstrasikan alur
Sistem Informasi Penjualan Online Produk Pembersih Berbasis Web pada PT Super HD Kliner.

## Cara menjalankan
Buka `index.html` langsung di browser (Chrome/Edge/Firefox). Tidak perlu server atau instalasi apa pun.

## Struktur folder
```
superhd-project/
├── index.html          # Struktur halaman (semua "view" SPA ada di sini)
├── css/
│   └── style.css        # Seluruh styling (design tokens, komponen, responsive)
└── js/
    ├── data.js           # Data produk, user, order, voucher, artikel (state aplikasi)
    ├── nav.js             # Navigasi antar halaman + komponen gauge "Daya Angkat Kerak"
    ├── products.js         # Kartu produk, katalog (filter/search/sort), halaman detail produk
    ├── content.js           # Blog/artikel, FAQ, form kontak, halaman distributor
    ├── auth.js                # Login, registrasi, logout (role: customer/distributor/admin)
    ├── cart.js                 # Keranjang belanja, checkout, simulasi pembayaran
    ├── account.js               # Dasbor akun pelanggan/distributor, cetak invoice & resi
    ├── admin.js                  # Dasbor admin: produk, pesanan, pelanggan, promo, laporan, user & log
    └── main.js                    # Fungsi toast notifikasi + inisialisasi aplikasi
```

## Akun demo
| Role         | Username      | Password       |
|--------------|---------------|----------------|
| Admin        | `admin`       | `admin123`     |
| Pelanggan    | `pelanggan`   | `pelanggan123` |
| Distributor  | `distributor1`| `dist123`      |

## Catatan implementasi
- Semua data disimpan sementara di memori JavaScript (variabel `let`/`const` di `data.js`), akan
  kembali ke kondisi awal setiap kali halaman di-refresh. Ini disengaja untuk kebutuhan demo prototipe.
- Fitur yang disimulasikan (bukan implementasi nyata): payment gateway, notifikasi WhatsApp, upload
  bukti transfer, integrasi ekspedisi, dan captcha.
- Fitur keamanan backend (enkripsi password, session management, proteksi SQL injection) merupakan
  tanggung jawab lapisan server yang belum ada di prototipe front-end ini — akan diimplementasikan
  pada tahap pengembangan sistem menggunakan Laravel + MySQL sesuai rencana pada Bab 3.
