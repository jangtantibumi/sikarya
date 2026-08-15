<?php

return [
    'core_security' => ['label' => 'Keamanan & Hak Akses', 'group' => 'Core', 'permanent' => true, 'default' => 'active'],
    'core_workflow' => ['label' => 'Persetujuan & Notifikasi', 'group' => 'Core', 'permanent' => true, 'default' => 'active'],
    'people' => ['label' => 'Karyawan & SDM', 'group' => 'People', 'default' => 'active'],
    'crm' => ['label' => 'CRM & Portal Pelanggan', 'group' => 'Commercial', 'default' => 'active'],
    'documents' => ['label' => 'Dokumen & Tanda Tangan', 'group' => 'Productivity', 'default' => 'active'],
    'project_costing' => ['label' => 'Biaya Proyek & Keuntungan', 'group' => 'Finance', 'default' => 'off', 'dependencies' => ['accounting']],
    'payroll' => ['label' => 'Penggajian (Payroll)', 'group' => 'People', 'default' => 'off', 'dependencies' => ['people', 'accounting']],
    'alumni_network' => ['label' => 'Jejaring Alumni', 'group' => 'People', 'default' => 'off', 'dependencies' => ['people']],
    'inventory' => ['label' => 'Gudang & Stok', 'group' => 'Operations', 'default' => 'active'],
    'purchasing' => ['label' => 'Purchasing & Vendor', 'group' => 'Operations', 'default' => 'off', 'dependencies' => ['inventory']],
    'production' => ['label' => 'Produksi / Komposisi', 'group' => 'Operations', 'default' => 'off', 'dependencies' => ['inventory', 'purchasing']],
    'pos' => ['label' => 'Kasir / POS', 'group' => 'Commercial', 'default' => 'off'],
    'accounting' => ['label' => 'Akuntansi & Keuangan', 'group' => 'Finance', 'default' => 'read_only'],
    'client_portal' => ['label' => 'Portal Klien & Vendor', 'group' => 'Commercial', 'default' => 'off', 'dependencies' => ['crm', 'documents']],
    'intelligence' => ['label' => 'Analisis Pintar & AI', 'group' => 'Intelligence', 'default' => 'off'],

    // Future Features (Coming Soon)
    'report_builder' => ['label' => 'Laporan Harian & Bulanan', 'group' => 'Intelligence', 'default' => 'coming_soon'],
    'auto_cogs' => ['label' => 'HPP Otomatis (COGS)', 'group' => 'Finance', 'default' => 'coming_soon', 'dependencies' => ['accounting', 'production']],
    'purchase_request' => ['label' => 'Permintaan Pembelian (PR)', 'group' => 'Operations', 'default' => 'coming_soon'],
    'location_tracking' => ['label' => 'Absensi & Pelacakan GPS', 'group' => 'People', 'default' => 'active'],
    'warning_letters' => ['label' => 'SP Karyawan (Indisipliner)', 'group' => 'People', 'default' => 'active'],

    // UI Placeholders for Future Roadmap
    'hr_legal' => ['label' => 'Surat Legal & Paklaring', 'group' => 'People', 'default' => 'active'],
    'hr_overtime' => ['label' => 'Logika Lembur Khusus', 'group' => 'People', 'default' => 'active'],
    'hr_attendance_adv' => ['label' => 'Absensi Lanjutan', 'group' => 'People', 'default' => 'active'],

    'performance_analytics' => ['label' => 'Peringkat & Produktivitas', 'group' => 'People', 'default' => 'coming_soon'],

    'task_routines' => ['label' => 'Tugas Rutin & Checklist', 'group' => 'Productivity', 'default' => 'coming_soon'],
    'task_approvals' => ['label' => 'Persetujuan Berjenjang', 'group' => 'Productivity', 'default' => 'coming_soon'],

    'ga_asset_management' => ['label' => 'General Affair & Aset', 'group' => 'Operations', 'default' => 'coming_soon'],

    'cashier_reports' => ['label' => 'Laporan Omzet Kasir', 'group' => 'Finance', 'default' => 'coming_soon'],

    'chat_internal' => ['label' => 'Obrolan Internal & Grup', 'group' => 'Communications', 'default' => 'active'],
    'announcements' => ['label' => 'Pengumuman & Notifikasi', 'group' => 'Communications', 'default' => 'coming_soon'],

    'doc_archives' => ['label' => 'Ekspor/Impor & Arsip Otomatis', 'group' => 'Productivity', 'default' => 'coming_soon'],

    'dashboard_manager' => ['label' => 'Dashboard Pimpinan', 'group' => 'Intelligence', 'default' => 'coming_soon'],

    'master_data_center' => ['label' => 'Pusat Data Master', 'group' => 'Core', 'default' => 'active'],
    'automation_engine' => ['label' => 'Mesin Otomatisasi', 'group' => 'Core', 'default' => 'active'],
];
