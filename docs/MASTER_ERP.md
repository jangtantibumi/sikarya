# Master ERP Laravel

## Identitas

Nama produk: **Northstar — Modular Business OS**

Kode sumber produk lokal: `suba-erp-master-local`.

Produk ini adalah evolusi multi-tenant dari fondasi ERP Laravel, bukan deployment ERP Suba-Arch.

## Menjalankan Demo

- Login: `http://127.0.0.1:8081/master-demo/login`
- Portal: `http://127.0.0.1:8081/master-demo/app`
- Module Control: `http://127.0.0.1:8081/master-demo`
- Purchasing: `http://127.0.0.1:8081/master-demo/purchasing?company=2`

Gunakan akun demo dan password sementara yang didefinisikan di `MasterProductDemoSeeder`.

## Prinsip Produk

- Multi-tenant dan least privilege.
- Core security/workflow permanen; modul bisnis dapat diaktifkan tenant per tenant.
- Maker–checker dan larangan self-approval.
- Ledger stok tidak boleh negatif.
- Jurnal keuangan harus seimbang.
- Audit trail dan backup berdasarkan scope tenant.

## Modul Saat Ini

### Fondasi

Tenant, membership, feature switch, login demo, audit, backup, dokumen, talent, approval, KPI/task, attendance, chat, dan notifikasi.

### Operasi

Inventory dasar serta Purchasing dasar sudah memiliki schema, API awal, data demo, dan Goods Receipt atomik.

### Roadmap

Production/BOM, POS lengkap, Finance tenant-aware, analytics, integrasi, dan assurance masih memerlukan pengembangan lanjutan.

## Dokumen Pendukung

- `MASTER_PRODUCT_AUDIT.md`
- `TENANT_AND_MODULE_CONTROL_DESIGN.md`
- `history/2026-08-02-master-erp-session.md`
- Blueprint spreadsheet di folder output workspace.
