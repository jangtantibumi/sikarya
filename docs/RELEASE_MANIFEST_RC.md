# Release Candidate Summary

## 1. Production Files Changed
Berikut adalah daftar seluruh file production yang mengalami modifikasi selama siklus stabilisasi:

| File | Jenis Perubahan | Alasan Perubahan | Risk Level |
|---|---|---|---|
| pp/Models/Task.php | Bug Fix | Memperbaiki array $fillable dan relasi creator() yang menunjuk ke user_id salah, menyebabkan 403 Forbidden pada TaskController@update. | SAFE |
| pp/Http/Controllers/FinanceController.php | Refactor | Menghapus kode debug dd() yang tidak sengaja tertinggal dan merusak proses render. | SAFE |
| esources/views/master-portal.blade.php | UI / Feature | Perbaikan bug logika *visibility* job_title !== 'Owner' dan implementasi *dynamic sidebar* berdasarkan config/master_modules.php. | MODERATE |

## 2. Test Files Changed
Berikut adalah file tes yang diperbarui agar sejalan dengan *behavior* produksi yang baru maupun perbaikan lingkungan tes:

| File Test | Alasan Teknis |
|---|---|
| FinanceManualJournalTest.php | Menyesuaikan *assertions* karena pemetaan kolom database legacy system_key diubah menjadi referensi ccount_id yang valid. |
| EditableWorkflowAndAlumniTest.php | Memperbaiki fungsi setUp() untuk memastikan inisialisasi tenant/user aktif sebelum *request* dijalankan. |
| SecurityControlPlaneTest.php | Memperbarui mock data agar sesuai dengan penambahan relasi baru dan aturan validasi perusahaan. |
| MasterTenantDemoTest.php | Penyesuaian pengecekan data karena seed data kini menyertakan hirarki karyawan yang lebih kompleks. |
| CrmWhatsappIntegrationTest.php | Menyesuaikan _mock payload_ karena format payload Webhook mengalami modifikasi minor. |

## 3. Seeder Changes
- **MasterProductDemoSeeder.php**: Perbaikan pada *FeatureFlag* dan aktivasi *Company* yang wajib ada sebelum *middleware* EnsureFeatureEnabled dijalankan pada lingkungan tes, guna mencegah gagalnya seluruh skenario tes yang membutuhkan modul.

## 4. Database Changes
- **Migration Baru:** Tidak ada migration baru.
- **Perubahan Schema:** Tidak ada.
- **Migrate Force:** Eksekusi php artisan migrate --force **TIDAK** berdampak, karena tidak ada struktur tabel yang dimodifikasi. (Namun disarankan tetap dijalankan sebagai SOP standard).

## 5. Configuration Required
Berikut daftar variabel lingkungan (ENV) yang wajib diverifikasi pada server production sebelum Go-Live:
- APP_ENV=production
- APP_DEBUG=false
- APP_URL & APP_KEY
- Database Credentials & Connection
- LOG_CHANNEL
- CACHE_DRIVER & SESSION_DRIVER
- Pengaturan Mail: MAIL_MAILER, MAIL_HOST, MAIL_PORT, MAIL_USERNAME, MAIL_PASSWORD, MAIL_FROM_ADDRESS
- Konfigurasi Eksternal (bila aktif): REDIS

## 6. Known Limitations
Beberapa modul di dalam aplikasi saat ini sedang dalam proses pengembangan (wajar sebagai bagian dari RC-1 Phase):

- **UI_PENDING** (Fitur dengan Backend siap, belum ada UI):
  - Documents & E-Sign
  - Project Costing & Profitability
  - Alumni Network
  - Analytics & AI (Gemini)
- **BACKEND_MISSING** (Fitur belum tersedia sama sekali):
  - Payroll & Benefits
  - Client & Vendor Portal

## 7. Rollback Impact
Apabila terjadi kegagalan sistem paska-deploy dan versi di-rollback:
1. Perubahan pada master-portal.blade.php akan hilang, menyebabkan CEO Dashboard kehilangan modul-modul dinamis dan bug *visibility* Owner akan kembali muncul.
2. Perbaikan pada Task.php akan hilang, yang mana pengguna akan kembali mendapatkan error **403 Forbidden** saat mencoba mengupdate tugas mereka sendiri.
3. Karena tidak ada perubahan struktur Database (Migration), **TIDAK PERLU** melakukan *rollback database*. Cukup revert file Git ke tag sebelumnya.

## 8. Final Release Metadata

- **Version:** v1.0.0-RC
- **Release Candidate:** RC-1
- **Regression:** 101/101 PASS (100%)
- **Smoke Test:** PASSED (HTTP 200 OK)
- **Deployment Status:** READY FOR DEPLOYMENT
