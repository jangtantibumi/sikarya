import fs from 'node:fs/promises';
import { SpreadsheetFile, Workbook } from '@oai/artifact-tool';

const outputDir = 'C:/Users/Digimark/Documents/JJ SubaArch/outputs/master-erp-blueprint-20260802';
const outputFile = process.env.BLUEPRINT_FILENAME ?? 'Master_ERP_Laravel_Product_Blueprint.xlsx';
await fs.mkdir(outputDir, { recursive: true });

const workbook = Workbook.create();
const dashboard = workbook.worksheets.add('Dashboard');
const roadmap = workbook.worksheets.add('Roadmap');
const modules = workbook.worksheets.add('Module Catalog');
const checklist = workbook.worksheets.add('Quality Checklist');
const updates = workbook.worksheets.add('Progress Updates');

const palette = {
  navy: '#111827',
  slate: '#374151',
  gold: '#F2C94C',
  cream: '#FFF8E1',
  blue: '#2563EB',
  teal: '#0F766E',
  green: '#15803D',
  red: '#B91C1C',
  amber: '#B45309',
  gray: '#F3F4F6',
  border: '#D1D5DB',
  white: '#FFFFFF',
};

function title(sheet, text, range) {
  sheet.mergeCells(range);
  const cell = sheet.getRange(range.split(':')[0]);
  cell.values = [[text]];
  cell.format = {
    fill: palette.navy,
    font: { bold: true, color: palette.white, size: 16 },
    horizontalAlignment: 'left',
    verticalAlignment: 'center',
  };
  sheet.getRange(range).format.rowHeight = 30;
}

function header(sheet, range) {
  sheet.getRange(range).format = {
    fill: palette.slate,
    font: { bold: true, color: palette.white },
    horizontalAlignment: 'center',
    verticalAlignment: 'center',
    wrapText: true,
    borders: { preset: 'all', style: 'thin', color: palette.border },
  };
}

function tableFormat(sheet, range) {
  sheet.getRange(range).format = {
    verticalAlignment: 'top',
    wrapText: true,
    borders: { preset: 'inside', style: 'thin', color: palette.border },
  };
}

// Dashboard
dashboard.showGridLines = false;
title(dashboard, 'MASTER ERP LARAVEL — PRODUCT BLUEPRINT', 'A1:H1');
dashboard.mergeCells('A2:H2');
dashboard.getRange('A2').values = [[
  'Blueprint pengembangan produk multi-company. Seluruh pekerjaan dilaksanakan di localhost; tidak ada deployment ke Hostinger tanpa persetujuan eksplisit.'
]];
dashboard.getRange('A2:H2').format = { fill: palette.cream, font: { color: palette.slate, italic: true }, wrapText: true, verticalAlignment: 'center' };
dashboard.getRange('A2:H2').format.rowHeight = 34;

dashboard.getRange('A4:H4').values = [[
  'Status Program', 'Nilai', 'Tahap Aktif', 'Kriteria Lanjut', 'Aturan Update', 'Workspace', 'Scope Deployment', 'Pemilik Keputusan'
]];
header(dashboard, 'A4:H4');
dashboard.getRange('A5:H5').values = [[
  'Fase 1 — Multi-company', '=COUNTIF(\'Roadmap\'!$I$6:$I$29,"In Progress")', 'Fase 1.1', 'Isolasi tenant harus lulus security acceptance criteria', 'Update diberikan setelah setiap milestone', 'suba-erp-master-local', 'Localhost only', 'CEO / Superadmin Produk'
]];
tableFormat(dashboard, 'A5:H5');
dashboard.getRange('B5').format.numberFormat = [['#,##0']];

dashboard.getRange('A7:D7').values = [['Ringkasan Tahap', 'Jumlah', 'Rumus', 'Catatan']];
header(dashboard, 'A7:D7');
dashboard.getRange('A8:D12').values = [
  ['Selesai', null, '=COUNTIF(\'Roadmap\'!$I$6:$I$29,"Done")', 'Tahap telah memenuhi acceptance criteria'],
  ['Berjalan', null, '=COUNTIF(\'Roadmap\'!$I$6:$I$29,"In Progress")', 'Tahap aktif yang akan dilaporkan'],
  ['Menunggu', null, '=COUNTIF(\'Roadmap\'!$I$6:$I$29,"Not Started")', 'Belum dieksekusi'],
  ['Tertahan', null, '=COUNTIF(\'Roadmap\'!$I$6:$I$29,"Blocked")', 'Memerlukan keputusan atau dependensi'],
  ['Total milestone', null, '=COUNTA(\'Roadmap\'!$A$6:$A$29)', 'Ruang lingkup blueprint saat ini'],
];
dashboard.getRange('C8:C12').formulas = dashboard.getRange('C8:C12').values.map(row => [row[2]]);
dashboard.getRange('B8:B12').formulas = [['=C8'], ['=C9'], ['=C10'], ['=C11'], ['=C12']];
dashboard.getRange('C8:C12').clear({ applyTo: 'contents' });
dashboard.getRange('C8:C12').formulas = [
  ['=COUNTIF(\'Roadmap\'!$I$6:$I$29,"Done")'],
  ['=COUNTIF(\'Roadmap\'!$I$6:$I$29,"In Progress")'],
  ['=COUNTIF(\'Roadmap\'!$I$6:$I$29,"Not Started")'],
  ['=COUNTIF(\'Roadmap\'!$I$6:$I$29,"Blocked")'],
  ['=COUNTA(\'Roadmap\'!$A$6:$A$29)'],
];
tableFormat(dashboard, 'A8:D12');
dashboard.getRange('B8:C12').format.numberFormat = [['#,##0']];

dashboard.getRange('F7:H7').merge();
dashboard.getRange('F7').values = [['Gerbang Keputusan CEO']];
header(dashboard, 'F7:H7');
dashboard.getRange('F8:H12').values = [
  ['1', 'Setujui arsitektur multi-company', 'Wajib sebelum data tenant dibangun'],
  ['2', 'Setujui katalog modul & dependensi', 'Wajib sebelum switch ON/OFF dibuat'],
  ['3', 'Pilih industri template prioritas', 'Jasa, F&B, retail, kontraktor, manufaktur ringan'],
  ['4', 'Setujui tahap rilis lokal', 'Tidak ada deploy produksi pada blueprint ini'],
  ['5', 'Review demo & checklist', 'Gerbang sebelum masuk fase berikutnya'],
];
tableFormat(dashboard, 'F8:H12');

dashboard.getRange('A14:H14').merge();
dashboard.getRange('A14').values = [['Cara menggunakan file ini']];
dashboard.getRange('A14:H14').format = { fill: palette.teal, font: { bold: true, color: palette.white } };
dashboard.getRange('A15:H17').merge();
dashboard.getRange('A15').values = [[
  'Ubah Status dan Progress % hanya setelah milestone benar-benar terverifikasi. Catat setiap pembaruan di sheet Progress Updates. CEO dapat memakai Module Catalog untuk menetapkan status Active, Read-only, atau Off bagi setiap tenant saat fondasi multi-company selesai.'
]];
dashboard.getRange('A15:H17').format = { fill: palette.gray, wrapText: true, verticalAlignment: 'top' };

dashboard.getRange('A1:H17').format.font = { name: 'Aptos', size: 10 };
dashboard.getRange('A1:H1').format.font = { name: 'Aptos Display', size: 16, bold: true, color: palette.white };
dashboard.getRange('A1:A17').format.columnWidth = 24;
dashboard.getRange('B1:B17').format.columnWidth = 13;
dashboard.getRange('C1:C17').format.columnWidth = 22;
dashboard.getRange('D1:D17').format.columnWidth = 27;
dashboard.getRange('E1:E17').format.columnWidth = 28;
dashboard.getRange('F1:F17').format.columnWidth = 19;
dashboard.getRange('G1:G17').format.columnWidth = 20;
dashboard.getRange('H1:H17').format.columnWidth = 26;

// Roadmap
roadmap.showGridLines = false;
title(roadmap, 'ROADMAP PENGERJAAN MASTER ERP', 'A1:N1');
roadmap.mergeCells('A2:N2');
roadmap.getRange('A2').values = [['Urutan kerja dibangun berdasarkan dependensi teknis. Status awal hanya mencatat pemisahan workspace produk; fase lain belum dieksekusi.']];
roadmap.getRange('A2:N2').format = { fill: palette.cream, font: { italic: true, color: palette.slate }, wrapText: true };
const roadmapHeaders = ['ID', 'Fase', 'Workstream', 'Deliverable / Outcome', 'Tipe Modul', 'Dependensi', 'Prioritas', 'Owner', 'Status', 'Progress %', 'Kriteria Selesai', 'Update Terakhir', 'Aksi Berikutnya', 'Catatan CEO'];
roadmap.getRange('A5:N5').values = [roadmapHeaders];
header(roadmap, 'A5:N5');
const roadmapRows = [
  ['0.1', '0 — Baseline', 'Product audit', 'Audit source master dan pemisahan identitas Suba-Arch yang spesifik', 'Core', '-', 'P0', 'Product Team', 'Done', 1, 'Daftar komponen reusable dan khusus tenant disetujui', 'Audit dan rancangan tenant terdokumentasi', 'Selesaikan environment baseline', 'Identitas Suba-Arch akan menjadi template tenant'],
  ['0.2', '0 — Baseline', 'Local environment', 'Konfigurasi localhost, data demo, dan test baseline tanpa koneksi produksi', 'Core', '0.1', 'P0', 'Engineering', 'Done', 1, 'Aplikasi lokal dapat dijalankan dan test baseline hijau', 'SQLite siap; 73 feature test lulus', 'Mulai tenant model', 'Tidak ada koneksi Hostinger'],
  ['1.1', '1 — Multi-company', 'Tenant model', 'Company/Tenant, brand, timezone, mata uang, cabang, dan konfigurasi per perusahaan', 'Core', '0.2', 'P0', 'Engineering', 'In Progress', 0.5, 'Data perusahaan terisolasi pada semua record inti', 'Schema company, membership, feature state, dan demo data tenant tersedia. Lead dan Task kini memakai tenant context; cakupan seluruh modul masih berjalan.', 'Perluas company scope ke seluruh record inti', 'Demo localhost terverifikasi'],
  ['1.2', '1 — Multi-company', 'Platform RBAC', 'Superadmin Platform, CEO tenant, manager, staff, dan akses lintas cabang yang aman', 'Core', '1.1', 'P0', 'Engineering', 'In Progress', 0.2, 'Policy dan test isolasi tenant lulus', 'Policy awal membatasi pengelolaan modul kepada CEO pemilik tenant atau platform admin.', 'Perluas policy ke data dan cabang lainnya', 'Belum siap rilis'],
  ['2.1', '2 — Module Control', 'Module catalog', 'Katalog modul permanen, optional, dependensi, dan template industri', 'Core', '1.1', 'P0', 'Product + Engineering', 'In Progress', 0.5, 'Katalog serta dependensi disetujui CEO', 'Katalog awal dan dependensi Inventory → Purchasing → Production tersedia pada demo lokal.', 'Tambahkan manifest fitur dan template industri', 'Demo localhost terverifikasi'],
  ['2.2', '2 — Module Control', 'Feature switches', 'Status Active / Read-only / Off per tenant; UI, API, queue, dan laporan mengikuti status', 'Core', '2.1', 'P0', 'Engineering', 'In Progress', 0.2, 'Sakelar tidak dapat dibypass melalui URL/API', 'Endpoint CEO tenant dan validasi dependensi tersedia; guard semua route/modul belum diterapkan.', 'Tambahkan feature guard pada UI, route, API, queue, dan laporan', 'Belum siap rilis'],
  ['3.1', '3 — Core ERP', 'People workflow', 'Organisasi, approval, KPI, task, attendance, chat, notification, backup, audit', 'Core', '1.2, 2.2', 'P0', 'Engineering', 'Not Started', 0, 'Semua core feature tenant-aware dan teruji', '', 'Migrasi core modules', ''],
  ['3.2', '3 — Core ERP', 'Documents & AI', 'Lampiran, e-sign, certificate, AI copilot, API key pribadi, retention', 'Optional', '3.1', 'P1', 'Engineering', 'Not Started', 0, 'Akses file dan AI terisolasi per tenant', '', 'Tenant-scope documents', ''],
  ['4.1', '4 — Master Data', 'Catalog', 'Produk/jasa, kategori, satuan, customer, supplier, pajak, lokasi, cabang', 'Core for Operations', '2.2', 'P0', 'Engineering', 'In Progress', 0.35, 'Master data dapat dibatasi atau dibagikan sesuai tenant', 'Produk dan gudang tenant tersedia untuk demo F&B.', 'Tambah supplier, customer, kategori, dan cabang', 'Localhost only'],
  ['4.2', '4 — Master Data', 'Templates', 'Onboarding wizard template: jasa, F&B, retail, kontraktor, manufaktur ringan', 'Core', '4.1', 'P1', 'Product + UX', 'Not Started', 0, 'Tenant baru memperoleh konfigurasi relevan dalam satu alur', '', 'Buat wizard tenant', ''],
  ['5.1', '5 — Inventory', 'Warehouse', 'Multi-gudang/cabang, barang masuk-keluar, kartu stok, transfer, minimum stock', 'Optional', '4.1, 2.2', 'P0', 'Engineering', 'In Progress', 0.55, 'Stok tidak dapat berubah tanpa transaksi dan audit trail', 'Produk, gudang, mutasi stok, tenant scope, dan saldo anti-negatif tersedia melalui API.', 'Tambah transfer, kartu stok UI, dan minimum-stock alert', 'Localhost only'],
  ['5.2', '5 — Inventory', 'Control', 'Stok opname, adjustment approval, barcode/QR, batch/expiry opsional', 'Optional', '5.1', 'P1', 'Engineering', 'Not Started', 0, 'Selisih stok dan alasan dapat diaudit', '', 'Tambah stocktake workflow', ''],
  ['6.1', '6 — Purchasing', 'Procurement flow', 'Requisition, approval limit, RFQ, PO, penerimaan, invoice supplier', 'Optional', '5.1', 'P0', 'Engineering', 'In Progress', 0.55, 'Barang diterima memperbarui stok dan kewajiban', 'Supplier, PO, item PO, aturan approval demo, Goods Receipt atomik, stok, API awal, dan halaman demo tersedia.', 'Tambah RFQ, invoice matching, UI interaktif, dan test workflow end-to-end', 'Localhost only'],
  ['6.2', '6 — Purchasing', 'Reorder intelligence', 'Daftar barang menipis, rekomendasi belanja, supplier performance', 'Optional', '6.1', 'P1', 'Analytics', 'Not Started', 0, 'Rekomendasi selalu dapat ditelusuri ke data stok', '', 'Buat rules & dashboard', ''],
  ['7.1', '7 — Production', 'MRP / BOM', 'Resep/BOM, work order, konsumsi bahan, produk jadi, waste, quality', 'Optional', '5.1, 6.1', 'P0', 'Engineering', 'In Progress', 0.1, 'Produksi menghasilkan mutasi stok dan biaya yang konsisten', 'Schema dan endpoint work order awal tersedia; BOM belum tersedia.', 'Implement BOM, konsumsi bahan, waste, dan quality', 'Localhost only'],
  ['7.2', '7 — Production', 'Production matrix', 'Matriks pesanan dan produksi bulanan, target vs realisasi, kapasitas', 'Optional', '7.1', 'P1', 'Analytics', 'Not Started', 0, 'Dashboard matriks dapat difilter divisi/cabang/periode', '', 'Buat reports produksi', ''],
  ['8.1', '8 — Sales & POS', 'Cashier', 'POS/kasir cabang, shift, opening/closing cash, pembayaran, refund', 'Optional', '4.1, 2.2', 'P0', 'Engineering', 'Not Started', 0, 'Setiap kasir dan cabang memiliki laporan harian terlacak', '', 'Implement POS ledger', ''],
  ['8.2', '8 — Sales & POS', 'Sales integration', 'Quotation, sales order, invoice, stok otomatis untuk produk', 'Optional', '8.1, 5.1', 'P0', 'Engineering', 'Not Started', 0, 'Penjualan mengurangi stok dan membentuk transaksi keuangan', '', 'Integrasi sales-to-stock', ''],
  ['9.1', '9 — Finance', 'Accounting engine', 'Double-entry, chart of accounts, journal otomatis, AR/AP, cashflow', 'Optional', '6.1, 8.2', 'P0', 'Finance + Engineering', 'Not Started', 0, 'Semua jurnal balance dan audit trail lulus', '', 'Rancang accounting postings', ''],
  ['9.2', '9 — Finance', 'Reports', 'Laba rugi, neraca, HPP, laporan cabang, budgeting, pajak lokal', 'Optional', '9.1', 'P0', 'Finance + Engineering', 'Not Started', 0, 'Laporan direkonsiliasi dengan transaksi sumber', '', 'Bangun financial reporting', ''],
  ['10.1', '10 — Intelligence', 'Analytics', 'BI dashboard, forecast stok, anomali, KPI lintas cabang, drill-down', 'Optional', '5.1, 8.2, 9.1', 'P1', 'Analytics', 'Not Started', 0, 'Insight dapat dijelaskan dan ditelusuri ke data sumber', '', 'Bangun semantic metrics', ''],
  ['10.2', '10 — Productization', 'Integrations', 'API, webhook, QRIS/payment, marketplace, WhatsApp, perangkat barcode', 'Optional', '2.2', 'P1', 'Engineering', 'Not Started', 0, 'Sandbox, credential vault, retry, dan audit tersedia', '', 'Tentukan integrasi prioritas', ''],
  ['11.1', '11 — Assurance', 'Security & reliability', 'Penetration test, backup-restore drill, observability, rate limit, DR runbook', 'Core', '1.2, 9.1', 'P0', 'Security + Engineering', 'Not Started', 0, 'Uji pemulihan dan security checklist lulus', '', 'Jalankan hardening', ''],
  ['11.2', '11 — Go-to-market', 'Commercial readiness', 'Demo tenant, dokumentasi, onboarding, support playbook, pricing/package', 'Core', '11.1', 'P1', 'Product + Business', 'Not Started', 0, 'Pilot customer berhasil dan feedback ditindaklanjuti', '', 'Siapkan pilot program', ''],
];
roadmap.getRange(`A6:N${5 + roadmapRows.length}`).values = roadmapRows;
tableFormat(roadmap, `A6:N${5 + roadmapRows.length}`);
roadmap.getRange(`J6:J${5 + roadmapRows.length}`).format.numberFormat = [['0%']];
roadmap.getRange(`I6:I${5 + roadmapRows.length}`).dataValidation = { rule: { type: 'list', values: ['Not Started', 'In Progress', 'Blocked', 'Done'] } };
roadmap.getRange(`G6:G${5 + roadmapRows.length}`).dataValidation = { rule: { type: 'list', values: ['P0', 'P1', 'P2'] } };
roadmap.getRange(`I6:I${5 + roadmapRows.length}`).conditionalFormats.add('containsText', { text: 'Done', format: { fill: '#DCFCE7', font: { color: palette.green, bold: true } } });
roadmap.getRange(`I6:I${5 + roadmapRows.length}`).conditionalFormats.add('containsText', { text: 'In Progress', format: { fill: '#DBEAFE', font: { color: palette.blue, bold: true } } });
roadmap.getRange(`I6:I${5 + roadmapRows.length}`).conditionalFormats.add('containsText', { text: 'Blocked', format: { fill: '#FEE2E2', font: { color: palette.red, bold: true } } });
roadmap.getRange(`J6:J${5 + roadmapRows.length}`).conditionalFormats.add('dataBar', { color: palette.teal, gradient: true });
roadmap.freezePanes.freezeRows(5);
['A','B','C','D','E','F','G','H','I','J','K','L','M','N'].forEach((col, i) => {
  roadmap.getRange(`${col}1:${col}${5 + roadmapRows.length}`).format.columnWidth = [10,18,19,38,18,20,10,18,15,12,34,21,27,23][i];
});
roadmap.getRange(`A5:N${5 + roadmapRows.length}`).format.rowHeight = 32;

// Module catalog
modules.showGridLines = false;
title(modules, 'MODULE CATALOG & CEO SWITCH POLICY', 'A1:J1');
modules.mergeCells('A2:J2');
modules.getRange('A2').values = [['CEO mengatur modul per tenant. Status Off menyembunyikan UI dan menolak transaksi/API; Read-only menyimpan riwayat tetapi menutup transaksi baru.']];
modules.getRange('A2:J2').format = { fill: palette.cream, font: { italic: true, color: palette.slate }, wrapText: true };
modules.getRange('A5:J5').values = [['Kode', 'Kelompok', 'Modul', 'Wajib?', 'Status Default', 'Dependency', 'Tenant Jasa', 'F&B / Retail', 'Manufaktur', 'Keterangan']];
header(modules, 'A5:J5');
const moduleRows = [
  ['CORE-01', 'Platform', 'Tenant & Company Isolation', 'Ya', 'Active', '-', 'Active', 'Active', 'Active', 'Fondasi isolasi data'],
  ['CORE-02', 'Platform', 'Identity, OTP, RBAC & Audit', 'Ya', 'Active', '-', 'Active', 'Active', 'Active', 'Keamanan dan akses'],
  ['CORE-03', 'Workflow', 'Approval, Notification & Backup', 'Ya', 'Active', '-', 'Active', 'Active', 'Active', 'Workflow lintas modul'],
  ['HR-01', 'People', 'HRIS, Attendance & Leave', 'Tidak', 'Active', 'CORE-02', 'Active', 'Active', 'Active', 'Dapat dimatikan tenant'],
  ['FIN-01', 'Finance', 'Accounting & Financial Reports', 'Tidak', 'Read-only', 'CORE-03', 'Active', 'Active', 'Active', 'Aktif penuh setelah accounting engine'],
  ['CRM-01', 'Commercial', 'CRM, Quotation & Client Portal', 'Tidak', 'Active', 'CORE-03', 'Active', 'Active', 'Active', 'Cocok jasa dan produk'],
  ['INV-01', 'Operations', 'Inventory & Warehouse', 'Tidak', 'Off', 'CORE-03', 'Off', 'Active', 'Active', 'Prasyarat stock flow'],
  ['PUR-01', 'Operations', 'Purchasing & Supplier', 'Tidak', 'Off', 'INV-01', 'Off', 'Active', 'Active', 'Requisition sampai receipt'],
  ['MRP-01', 'Operations', 'Production / BOM / Quality', 'Tidak', 'Off', 'INV-01, PUR-01', 'Off', 'Optional', 'Active', 'Untuk resep/BOM dan produksi'],
  ['POS-01', 'Commercial', 'Cashier / POS / Branches', 'Tidak', 'Off', 'CORE-03', 'Optional', 'Active', 'Active', 'Terintegrasi stok bila INV aktif'],
  ['DOC-01', 'Productivity', 'Documents & E-Sign', 'Tidak', 'Active', 'CORE-02', 'Active', 'Active', 'Active', 'Sertifikat dan kontrak'],
  ['AI-01', 'Intelligence', 'Analytics, Forecast & AI Copilot', 'Tidak', 'Off', 'CORE-03', 'Optional', 'Optional', 'Optional', 'Selalu human-approved'],
];
modules.getRange(`A6:J${5 + moduleRows.length}`).values = moduleRows;
tableFormat(modules, `A6:J${5 + moduleRows.length}`);
modules.getRange(`E6:E${5 + moduleRows.length}`).dataValidation = { rule: { type: 'list', values: ['Active', 'Read-only', 'Off'] } };
modules.getRange(`E6:E${5 + moduleRows.length}`).conditionalFormats.add('containsText', { text: 'Active', format: { fill: '#DCFCE7', font: { color: palette.green, bold: true } } });
modules.getRange(`E6:E${5 + moduleRows.length}`).conditionalFormats.add('containsText', { text: 'Off', format: { fill: '#F3F4F6', font: { color: palette.slate } } });
modules.freezePanes.freezeRows(5);
['A','B','C','D','E','F','G','H','I','J'].forEach((col, i) => modules.getRange(`${col}1:${col}${5 + moduleRows.length}`).format.columnWidth = [13,16,32,11,15,24,16,16,16,28][i]);

// Quality checklist
checklist.showGridLines = false;
title(checklist, 'QUALITY, SECURITY & RELEASE CHECKLIST', 'A1:I1');
checklist.mergeCells('A2:I2');
checklist.getRange('A2').values = [['Checklist ini menjadi gerbang wajib sebelum setiap fase dinyatakan selesai. Tidak ada deployment produksi pada program master ERP sampai keputusan CEO diberikan.']];
checklist.getRange('A2:I2').format = { fill: palette.cream, font: { italic: true, color: palette.slate }, wrapText: true };
checklist.getRange('A5:I5').values = [['ID', 'Fase', 'Area Uji', 'Checklist', 'Bukti / Link', 'Owner', 'Status', 'Tanggal Verifikasi', 'Catatan']];
header(checklist, 'A5:I5');
const checklistRows = [
  ['Q-01', 'Semua', 'Tenant isolation', 'Query, API, file, cache, queue, export, dan notifikasi tidak melintasi tenant', '', 'Engineering', 'Not Started', '', ''],
  ['Q-02', 'Semua', 'Authorization', 'Policy/role diuji untuk CEO, manager, staff, alumni, dan superadmin platform', '', 'Engineering', 'Not Started', '', ''],
  ['Q-03', 'Semua', 'Audit trail', 'Perubahan penting menyimpan aktor, waktu, sumber, dan before/after bila relevan', '', 'Security', 'Not Started', '', ''],
  ['Q-04', 'Module Control', 'Switch enforcement', 'Off dan Read-only dicek di UI, route, API, queue, report, dan deep link', '', 'Engineering', 'Not Started', '', ''],
  ['Q-05', 'Inventory', 'Stock integrity', 'Tidak ada saldo stok negatif atau mutasi tanpa ledger/approval', '', 'Operations', 'Not Started', '', ''],
  ['Q-06', 'Purchasing', 'Procurement', 'Requisition–PO–receipt menjaga approval dan stok konsisten', '', 'Operations', 'Not Started', '', ''],
  ['Q-07', 'Production', 'BOM & costing', 'Konsumsi bahan, waste, produk jadi, dan biaya dapat direkonsiliasi', '', 'Operations', 'Not Started', '', ''],
  ['Q-08', 'POS', 'Cash control', 'Opening/closing kas, refund, cabang, dan pembayaran diuji per shift', '', 'Finance', 'Not Started', '', ''],
  ['Q-09', 'Finance', 'Double-entry', 'Setiap jurnal balance; laporan direkonsiliasi dengan transaksi sumber', '', 'Finance', 'Not Started', '', ''],
  ['Q-10', 'Security', 'Recovery', 'Backup restore, error monitoring, rate limit, dan disaster recovery drill lulus', '', 'Security', 'Not Started', '', ''],
  ['Q-11', 'Product', 'Usability', 'Uji pengguna target menyelesaikan alur utama tanpa pelatihan teknis panjang', '', 'Product + UX', 'Not Started', '', ''],
  ['Q-12', 'Release', 'CEO sign-off', 'Demo, acceptance criteria, dan keputusan rilis terdokumentasi', '', 'CEO / Superadmin', 'Not Started', '', ''],
];
checklist.getRange(`A6:I${5 + checklistRows.length}`).values = checklistRows;
tableFormat(checklist, `A6:I${5 + checklistRows.length}`);
checklist.getRange(`G6:G${5 + checklistRows.length}`).dataValidation = { rule: { type: 'list', values: ['Not Started', 'In Review', 'Passed', 'Failed', 'N/A'] } };
checklist.getRange(`G6:G${5 + checklistRows.length}`).conditionalFormats.add('containsText', { text: 'Passed', format: { fill: '#DCFCE7', font: { color: palette.green, bold: true } } });
checklist.getRange(`G6:G${5 + checklistRows.length}`).conditionalFormats.add('containsText', { text: 'Failed', format: { fill: '#FEE2E2', font: { color: palette.red, bold: true } } });
checklist.getRange(`H6:H${5 + checklistRows.length}`).format.numberFormat = [['yyyy-mm-dd']];
checklist.freezePanes.freezeRows(5);
['A','B','C','D','E','F','G','H','I'].forEach((col, i) => checklist.getRange(`${col}1:${col}${5 + checklistRows.length}`).format.columnWidth = [10,18,20,47,26,19,15,17,26][i]);

// Progress updates
updates.showGridLines = false;
title(updates, 'PROGRESS UPDATES & DECISION LOG', 'A1:I1');
updates.mergeCells('A2:I2');
updates.getRange('A2').values = [['Setiap milestone akan ditambahkan sebagai baris baru. Update minimum: selesai milestone, blocker, perubahan scope, hasil test, dan permintaan keputusan CEO.']];
updates.getRange('A2:I2').format = { fill: palette.cream, font: { italic: true, color: palette.slate }, wrapText: true };
updates.getRange('A5:I5').values = [['Tanggal', 'Milestone ID', 'Status', 'Progress %', 'Ringkasan Bukti', 'Blocker / Risiko', 'Aksi Berikutnya', 'Keputusan CEO Dibutuhkan?', 'Keputusan / Catatan CEO']];
header(updates, 'A5:I5');
updates.getRange('A6:I8').values = [
  [new Date('2026-08-02T00:00:00'), '0.1', 'Done', 1, 'Workspace master terpisah, audit produk, dan rancangan tenant/module control selesai.', 'Tidak ada', 'Selesaikan baseline environment lokal.', 'Tidak', ''],
  [new Date('2026-08-02T00:00:00'), '0.2', 'Done', 1, 'SQLite siap; 73 feature test lulus. Test Android dijadikan product-agnostic dan pembalikan jurnal kini mengoreksi periode sumber.', 'Tidak ada', 'Mulai model tenant dan policy isolasi data.', 'Tidak', ''],
  [new Date('2026-08-02T00:00:00'), '1.1 / 2.1', 'In Progress', 0.4, 'Tenant, membership, feature state, katalog modul, dependensi, dua data perusahaan demo, dan UI localhost tersedia. Regresi: 78 test lulus.', 'Isolasi wajib diperluas ke seluruh record dan policy sebelum rilis produk.', 'Perluas tenant context, RBAC, serta feature guard ke modul inti.', 'Tidak', 'Demo localhost siap; bukan status rilis produksi.'],
  [new Date('2026-08-02T00:00:00'), '1.1', 'In Progress', 0.5, 'Middleware tenant context dan global scope untuk Lead/Task aktif. Tenant A tidak dapat membaca data Tenant B; record baru otomatis memperoleh company_id. Regresi: 79 test lulus.', 'Model, policy, attachment, dan laporan lain belum seluruhnya tenant-aware.', 'Tenant-scope model inti berikutnya dan buat policy membership/RBAC.', 'Tidak', 'Tidak ada deployment Hostinger.']
  ,[new Date('2026-08-02T00:00:00'), '1.2 / 2.2', 'In Progress', 0.2, 'Policy awal: hanya CEO pemilik tenant atau platform admin dapat mengelola modul. API tenant module-control diuji, termasuk larangan melewati dependensi. Regresi: 81 test lulus.', 'Feature guard belum menyelimuti seluruh UI, API, queue, dan laporan; RBAC lintas cabang belum ada.', 'Terapkan guard fitur dan policy tenant pada modul inti berikutnya.', 'Tidak', 'Demo localhost tetap aktif; bukan rilis produksi.']
  ,[new Date('2026-08-02T00:00:00'), '4.1 / 5.1 / 6.1', 'In Progress', 0.45, 'Demo tenant F&B memiliki produk, gudang, stok awal. API inventory mencegah stok negatif. Purchase request memiliki alur draft-submit-approve/reject. Regresi: 83 test lulus.', 'Transfer gudang, PO, receiving, invoice supplier, BOM dan POS belum dibangun.', 'Lanjut Production/BOM, POS, lalu accounting posting.', 'Tidak', 'Blueprint diperbarui atas permintaan CEO.']
  ,[new Date('2026-08-02T00:00:00'), '6.1 / 7.1 / Security', 'In Progress', 0.55, 'Supplier, PO, item PO, approval demo, Goods Receipt atomik, halaman Purchasing, dan data receipt parsial tersedia. Dokumen, template, backup, serta audit diperluas tenant-aware. Regresi terakhir: 83 test lulus.', 'RFQ/invoice matching, BOM, UI PO interaktif, test workflow Purchasing, finance tenant-aware penuh, dan POS masih tersisa.', 'Selesaikan test Purchasing lalu Production/BOM dan Finance tenant-aware.', 'Tidak', 'POS ditunda; seluruh pekerjaan tetap localhost.']
];
tableFormat(updates, 'A6:I60');
updates.getRange('A6:A60').format.numberFormat = [['yyyy-mm-dd']];
updates.getRange('D6:D60').format.numberFormat = [['0%']];
updates.getRange('C6:C60').dataValidation = { rule: { type: 'list', values: ['Not Started', 'In Progress', 'Blocked', 'Done'] } };
updates.getRange('H6:H60').dataValidation = { rule: { type: 'list', values: ['Ya', 'Tidak'] } };
updates.getRange('C6:C60').conditionalFormats.add('containsText', { text: 'Done', format: { fill: '#DCFCE7', font: { color: palette.green, bold: true } } });
updates.getRange('C6:C60').conditionalFormats.add('containsText', { text: 'Blocked', format: { fill: '#FEE2E2', font: { color: palette.red, bold: true } } });
updates.freezePanes.freezeRows(5);
['A','B','C','D','E','F','G','H','I'].forEach((col, i) => updates.getRange(`${col}1:${col}60`).format.columnWidth = [16,14,15,12,40,30,32,24,32][i]);

const dashboardPreview = await workbook.render({ sheetName: 'Dashboard', range: 'A1:H17', scale: 1.25, format: 'png' });
await fs.writeFile(`${outputDir}/dashboard-preview.png`, new Uint8Array(await dashboardPreview.arrayBuffer()));
const roadmapPreview = await workbook.render({ sheetName: 'Roadmap', range: 'A1:N29', scale: 0.8, format: 'png' });
await fs.writeFile(`${outputDir}/roadmap-preview.png`, new Uint8Array(await roadmapPreview.arrayBuffer()));

const output = await SpreadsheetFile.exportXlsx(workbook);
await output.save(`${outputDir}/${outputFile}`);

const summary = await workbook.inspect({ kind: 'table', range: 'Roadmap!A5:N12', include: 'values,formulas', tableMaxRows: 8, tableMaxCols: 14 });
const errors = await workbook.inspect({ kind: 'match', searchTerm: '#REF!|#DIV/0!|#VALUE!|#NAME\\?|#N/A', options: { useRegex: true, maxResults: 100 }, summary: 'formula error scan' });
console.log(JSON.stringify({ summary: summary.ndjson, errors: errors.ndjson }));
