# Tenant & Module Control — Architecture Decision

Status: rancangan untuk Fase 1 dan Fase 2; belum diimplementasikan.

## Model produk

Satu aplikasi Laravel melayani banyak perusahaan. Setiap perusahaan adalah tenant yang memiliki data, identitas, role, modul aktif, cabang, dan konfigurasi sendiri. Superadmin Platform mengelola tenant; CEO mengelola tenant miliknya.

## Entitas fondasi

- `companies`: identitas tenant, branding, timezone, mata uang, status, paket.
- `company_users`: hubungan user-tenants beserta role tenant dan status akses.
- `company_features`: status `active`, `read_only`, atau `off` untuk setiap modul.
- `company_settings`: konfigurasi yang tidak cocok dimasukkan ke kolom utama company.
- `branches` dan `warehouses`: lokasi operasional per tenant untuk fase operations.

## Scope wajib

Seluruh tabel bisnis harus memiliki `company_id`, kecuali tabel platform yang memang global. Relasi, policy, query scope, cache, file path, job queue, event, notifikasi, export, dan audit log harus membawa company context.

## Feature state

| State | UI | API / transaksi | Riwayat |
| --- | --- | --- | --- |
| `active` | Ditampilkan | Diizinkan sesuai role | Terbuka |
| `read_only` | Ditampilkan dengan label arsip | Transaksi baru ditolak | Terbuka |
| `off` | Disembunyikan | Endpoint ditolak | Tidak ditampilkan pada operasi normal |

## Dependency rules awal

- Core Security, Tenant, RBAC, Approval, Audit, Backup: permanen.
- Inventory: prasyarat untuk warehouse transfer, purchasing stock receipt, produksi, dan POS produk.
- Purchasing: dapat aktif tanpa produksi; stock receipt membutuhkan inventory.
- Production: membutuhkan inventory; purchasing dianjurkan tetapi tidak wajib untuk bahan yang sudah ada.
- POS: dapat aktif untuk jasa tanpa inventory; penjualan barang menggunakan inventory.
- Accounting: dapat menerima transaksi manual, tetapi posting otomatis dari purchasing, POS, inventory, dan production diaktifkan hanya bila modul asal aktif.

## Security acceptance criteria

1. User tenant A tidak dapat membaca atau mengubah data tenant B melalui UI, URL, API, job, file, maupun export.
2. Status modul tidak dapat dibypass dengan direct API request.
3. Superadmin Platform memiliki audit trail terpisah dari CEO tenant.
4. Backup/restore selalu diikat ke satu tenant kecuali proses platform yang disetujui.
