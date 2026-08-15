# SUBA ERP Development History
**Last Updated:** August 2026

Dokumen ini berisi rangkuman riwayat percakapan dan pengembangan fitur ERP untuk memudahkan *handoff* atau kelanjutan pekerjaan di sesi berikutnya (meskipun berganti akun).

---

## 1. Payroll & Benefits Module
**Status:** ✅ Selesai (UI/UX & Workflow)

* **Refactoring UI/UX:** Tampilan disesuaikan menggunakan *Design System* terbaru ala iOS (glassmorphism, clean border radius, drop-shadow).
* **Action Workflow:** Tombol pada Dashboard Payroll dibuat bergantung pada status.
  * **Draft:** Edit, Approve, Delete.
  * **Approved:** Pay, Reject, View Detail.
  * **Paid:** View Slip, Download PDF, Journal, History.
* **Popup & Modals:** Seluruh popup info, tombol konfirmasi, dan modal action dipoles ulang.
* **Perbaikan UX:** Menghilangkan kelakuan aneh (kursor loncat ke atas saat reload), mempertahankan status *state* menu tanpa *jumping*.
* **Demo Data:** Menyediakan seeder untuk Payroll guna keperluan demonstrasi UAT (User Acceptance Testing) lintas divisi.

---

## 2. Executive Organization Workspace
**Status:** ✅ Selesai (Native CSS & JS, No D3.js)

* **Isu Awal:** Error runtime `r.flextree is not a function` pada `d3-org-chart`.
* **Keputusan:** Modul D3.js (Canvas & SVG) dihapus sepenuhnya karena terlalu berat, tidak *maintainable*, dan sering *error* pada *runtime*.
* **Arsitektur Baru:** Diganti dengan arsitektur HTML, CSS Grid, dan Vanilla JS yang jauh lebih ringan, modular, dan memuat data dalam satuan fraksi detik (Fast & Enterprise Ready).
* **Fitur Utama:**
  * **Department Groups:** Layout dibagi per kelompok Divisi secara berjenjang (Leadership vs Team Members).
  * **Employee Card:** *Card* premium menampilkan foto profil, status aktif/leave/suspend, *employment type*, dan lencana performa (*Performance Badge*).
  * **Right Drawer:** Terbuka saat Card diklik. Memuat informasi rinci, KPI, rekan setim, bawahan, dan link integrasi silang modul (Payroll, Tasks, Attendance, Documents).
  * **Realtime Search & Filter:** Memfilter tanpa memuat ulang halaman (*No Reload*).

---

## 3. RBAC & Organization Data Persistence
**Status:** ✅ Selesai (Migrasi, Model, Logic Auth)

* **Struktur Database Baru:**
  * `organization_requests`: Untuk menampung proses *approval* berjenjang (dari Manager ke CEO) untuk transfer, promote, dan rekrutmen.
  * `employee_performances`: Menampung data nilai (*score*), *badge*, dan catatan (*notes*) performa karyawan.
  * `audit_logs`: Mencatat keseluruhan mutasi data organisasi (User, Target User, Action, Before State, After State).
* **Role-Based Access Control (RBAC):**
  * Keseluruhan **visibilitas bagan terbuka** bagi semua peran (*Visibility* tidak disembunyikan).
  * Pembatasan murni dilakukan pada level **Aksi/Button (Action Bar)** yang ditentukan lewat backend `OrganizationController`.
  * **CEO:** Memiliki seluruh kendali eksekusi langsung. Otomatis masuk ke `audit_logs`.
  * **Manager:** Hanya dapat mengeksekusi pengajuan (`OrganizationRequest`) dan *Performance Review* terhadap anggota tim langsung (*direct report*).
  * **Supervisor/Staff:** Hanya akses lihat profil.
* **Seeder:** Dijalankan `OrganizationAddonsSeeder` untuk menyuntikkan data kinerja (*Performance*) dan *Audit Log* ke seluruh *User* demo, sehingga UI menampilkan data nyata dari database, bukan dari *hardcode* frontend.

---

## 4. Next Steps (Agenda Mendatang)
Berikut adalah daftar pekerjaan opsional atau lanjutan untuk iterasi sesi berikutnya:
1. **Approval Dashboard:** Membangun antarmuka bagi CEO untuk me-*review*, menolak, atau meng-Approve `organization_requests` yang telah diajukan Manager.
2. **HR Execution Flow:** Menyempurnakan pemicu otomatis yang membuat akun User dan menjadwalkan setup *Payroll* ketika CEO menekan *Approve*.
3. **Module Interoperability:** Menguatkan UI *Tasks* dan *Documents* yang terhubung dari dalam profil Employee Drawer.

---
*Silakan salin berkas ini dan gunakan sebagai prompt utama ('System Context' atau 'Initial Context') apabila memulai perbincangan baru dari nol.*
