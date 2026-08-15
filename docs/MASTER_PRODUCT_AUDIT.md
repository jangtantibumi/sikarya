# Master ERP Laravel — Baseline Product Audit

Status: Fase 0.1 selesai untuk baseline source pada 2 Agustus 2026.

## Tujuan

Memisahkan kemampuan ERP yang dapat dijadikan produk umum dari konfigurasi serta identitas yang hanya relevan untuk Suba-Arch. ERP produksi Suba-Arch tidak berubah dan tetap menjadi referensi use case perusahaan jasa/kreatif.

## Komponen yang dapat dipertahankan sebagai produk inti

- Otentikasi OTP, manajemen sesi, role, approval, audit, backup, dan notifikasi.
- Organisasi, KPI, goals, task, attendance, chat, dokumen, e-sign, alumni, dan talent management.
- CRM, finance, project costing, analytics, AI copilot, dan attachment workflow.
- Control plane yang sudah menjadi awal feature flag.

## Komponen yang harus menjadi konfigurasi tenant/template

| Area | Kondisi saat ini | Arah master ERP |
| --- | --- | --- |
| Nama, logo, palette, email, domain | Banyak memakai Suba-Arch secara langsung | Company profile dan branding per tenant |
| Seed user/divisi | CEO dan divisi Suba-Arch tertanam | Onboarding wizard membuat organisasi awal |
| Sertifikat/paklaring | Nomor dan isi dokumen Suba-Arch | Template dokumen yang dapat dikonfigurasi tenant |
| AI copilot | Prompt arsitektur Suba-Arch | Context pack per tenant dan per industri |
| Firebase/PWA/Android | Project ID dan fingerprint Suba-Arch | Konfigurasi build/release per produk/tenant |
| Nama file export/report | Prefix Suba-Arch | Prefix dari identitas perusahaan |

## Temuan baseline teknis

- Project Laravel berhasil dimigrasikan pada database SQLite lokal yang baru.
- Baseline feature test utama berjalan setelah folder runtime Laravel dibuat.
- Beberapa test lama mengasumsikan artefak rilis Android Suba-Arch. Artefak tersebut sengaja tidak dibawa ke master workspace dan harus diganti dengan test product-agnostic pada fase productization.
- Ada satu test accounting/data deletion yang gagal pada pembalikan jurnal laba-rugi. Ini dicatat sebagai debt yang harus diselesaikan sebelum finance engine master dipasarkan.

## Batas fase berikutnya

Fase 1 tidak boleh mengandalkan filter UI saja. Setiap record, file, cache key, queue job, export, notification, dan endpoint harus diberi scope tenant yang dapat diuji otomatis.
