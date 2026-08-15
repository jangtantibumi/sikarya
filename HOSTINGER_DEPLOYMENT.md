# Deployment ERP Suba Arch ke Hostinger

Target: `https://erp.suba-arch.co.id`

## Sebelum upload

1. Pastikan seluruh pengujian lulus.
2. Buat backup database localhost.
3. Jangan mengunggah `.env`, database SQLite, log, cache, atau file pengujian.
4. Salin `.env.hostinger.example` menjadi `.env` hanya di server, lalu isi kredensial asli melalui panel/terminal Hostinger.
5. Buat database MySQL terpisah khusus ERP dan pengguna database dengan hak minimum.
6. Buat mailbox khusus ERP untuk pengiriman OTP.
7. Pastikan lima modul tahap pertama sudah aktif: Talent Management, Advanced Analytics, Dokumen & E-Sign, Akuntansi, dan Project Costing.
8. Ekspor data SQLite localhost lalu lakukan uji migrasi ke MySQL pada lingkungan staging sebelum mengarahkan subdomain produksi.

## Konfigurasi wajib

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL=https://erp.suba-arch.co.id`
- `SESSION_ENCRYPT=true`
- `SESSION_SECURE_COOKIE=true`
- SMTP asli, bukan driver `log`
- Untuk port SMTP 587 gunakan `MAIL_SCHEME=smtp` agar STARTTLS dinegosiasikan otomatis.
- HTTPS aktif dan redirect HTTP ke HTTPS

## Perintah deployment

Jalankan dari direktori aplikasi:

```text
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan db:seed --class=StrategicErpSeeder --force
php artisan storage:link
php artisan optimize
php artisan queue:restart
```

`StrategicErpSeeder` bersifat idempotent. Perintah ini membentuk proyek dan jurnal
double-entry dari data transfer klien yang sudah ada tanpa membuat transaksi sumber baru.

Untuk deployment yang memindahkan data localhost, pertahankan `APP_KEY` yang sama.
Jangan menjalankan `php artisan key:generate`, karena SMTP terenkripsi, API key pribadi,
access gate, dan hash tanda tangan bergantung pada kunci tersebut.

Ekspor data pada localhost:

```text
php artisan erp:data-export storage/app/erp-portable-data.json
```

Setelah migrasi database MySQL di server selesai:

```text
php artisan erp:data-import storage/app/erp-portable-data.json --force
```

Jalankan scheduler setiap menit melalui Cron Job:

```text
* * * * * php /path/to/artisan schedule:run
```

Jalankan queue worker menggunakan Process Manager Hostinger bila tersedia:

```text
php artisan queue:work --sleep=3 --tries=3 --timeout=90
```

## Aktivasi keamanan

1. Masuk sebagai CEO.
2. Buka **Set Up Goal Divisi → Keamanan Login**.
3. Pastikan status SMTP menyatakan email siap.
4. Atur password perusahaan minimal 12 karakter.
5. Simpan dan aktifkan gerbang perusahaan.
6. Keluar, lalu uji:
   - password gerbang salah;
   - password gerbang benar;
   - OTP masuk ke email;
   - OTP salah lima kali menyebabkan penguncian;
   - OTP kedaluwarsa tidak dapat digunakan;
   - OTP hanya dapat dipakai satu kali.
7. Uji pembatasan data:
   - staff hanya melihat review dan dokumen miliknya;
   - manager hanya melihat tim/divisinya;
   - laporan laba rugi hanya dapat dibuka CEO dan Finance;
   - Project Costing hanya dapat dibuka CEO, Operasional, dan Finance.

## Uji penerimaan lima modul

1. **Talent Management:** Manager membuat draft review staff, mempublikasikannya, lalu staff memastikan hanya review miliknya yang terlihat.
2. **Advanced Analytics:** pastikan jumlah karyawan, task, attendance, proyek, dan keuangan berasal dari data terbaru; akun non-Finance tidak boleh melihat angka keuangan.
3. **Sertifikat magang:** HRD membuat draft, CEO/HRD menandatangani, buka tautan verifikasi tanpa login, lalu uji pencabutan sertifikat.
4. **Akuntansi:** input pendapatan/biaya dan pastikan jurnal debit-kredit seimbang, laba rugi bulanan berubah, serta evaluasi memiliki 12 bulan.
5. **Project Costing:** isi budget dan progres, catat biaya, lalu pastikan biaya otomatis muncul pada project margin dan laba rugi.

Catatan: e-sign versi ini adalah persetujuan elektronik internal berbasis akun dan hash
integritas. Untuk Tanda Tangan Elektronik Tersertifikasi, integrasikan PSrE Indonesia
sebelum memakai fitur ini untuk dokumen yang secara hukum mensyaratkan TTE tersertifikasi.

## Verifikasi setelah upload

```text
php artisan about
php artisan migrate:status
php artisan route:list --path=api/accounting
php artisan test
```

Pastikan direktori `storage` dan `bootstrap/cache` dapat ditulis oleh proses PHP,
sedangkan `.env`, log, database backup, dan source control tidak dapat diakses melalui web.

## Setelah online

- Ganti password akses perusahaan secara berkala dan ketika ada karyawan keluar.
- Jangan mengirim password melalui kanal publik.
- Simpan backup terenkripsi di lokasi terpisah.
- Lakukan restore drill dan penetration test sebelum menyimpan payroll atau dokumen sensitif.
