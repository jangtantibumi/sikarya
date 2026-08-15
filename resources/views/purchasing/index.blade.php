<style>
    /* ═══════════════════════════════════════════════════════════════
       Purchasing Module — Embedded Modal & Component CSS
       These classes are NOT defined in the parent layout, so we
       embed them here for portability across master-portal & employee-portal.
       ═══════════════════════════════════════════════════════════════ */

    /* Modal Overlay */
    .ios-modal-overlay.purchasing-modal {
        position: fixed; top: 0; left: 0; right: 0; bottom: 0;
        background: rgba(0, 0, 0, 0.45);
        backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px);
        z-index: 9000; display: flex; align-items: center; justify-content: center;
        animation: purchModalFadeIn 0.25s ease-out;
        padding: 20px;
    }
    @keyframes purchModalFadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }

    /* Modal Panel */
    .ios-modal-overlay.purchasing-modal .ios-modal {
        background: var(--panel, #fff);
        border-radius: 20px;
        box-shadow: 0 24px 48px rgba(0,0,0,0.18), 0 0 0 1px rgba(0,0,0,0.05);
        overflow: hidden;
        animation: purchModalSlideUp 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex; flex-direction: column;
        max-height: 85vh;
    }
    @keyframes purchModalSlideUp {
        from { opacity: 0; transform: translateY(24px) scale(0.97); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Modal Header */
    .purch-modal-header {
        padding: 24px 28px 20px;
        border-bottom: 1px solid var(--panel-border, #e2e8f0);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .purch-modal-header h3 {
        margin: 0;
        color: var(--text-heading, #111827);
        font-size: 18px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .purch-modal-close {
        width: 32px; height: 32px;
        border-radius: 50%; border: none;
        background: var(--panel-secondary, #f1f5f9);
        color: var(--text-muted, #6b7280);
        font-size: 16px; cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        transition: all 0.2s;
    }
    .purch-modal-close:hover {
        background: #e2e8f0; color: var(--text-heading, #111827);
    }

    /* Modal Body */
    .purch-modal-body {
        padding: 24px 28px;
        overflow-y: auto;
        flex: 1;
    }

    /* Modal Footer */
    .purch-modal-footer {
        padding: 16px 28px 24px;
        border-top: 1px solid var(--panel-border, #e2e8f0);
        display: flex;
        gap: 12px;
        justify-content: flex-end;
        background: var(--panel-secondary, #f8fafc);
    }

    /* Label */
    .ios-label, .purch-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        color: var(--text-muted, #6b7280);
        margin-bottom: 6px;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    /* Supplier list inside modal */
    .purch-supplier-list {
        max-height: 200px; overflow-y: auto;
        border: 1px solid var(--panel-border, #e2e8f0);
        border-radius: 12px; padding: 0;
    }
    .purch-supplier-list table {
        width: 100%; border-collapse: collapse; font-size: 13px;
    }
    .purch-supplier-list table tr {
        border-bottom: 1px solid var(--panel-border, #e2e8f0);
    }
    .purch-supplier-list table tr:last-child {
        border-bottom: none;
    }
    .purch-supplier-list table td {
        padding: 10px 16px;
    }

    /* Form grid */
    .purch-form-grid {
        display: grid; gap: 14px; margin-bottom: 16px;
    }
    .purch-form-grid-2 {
        grid-template-columns: 1fr 1fr;
    }

    /* Divider */
    .purch-divider {
        border: none;
        border-top: 1px solid var(--panel-border, #e2e8f0);
        margin: 20px 0;
    }

    /* PO Lines area */
    .purch-po-lines {
        background: var(--panel-secondary, #f8fafc);
        padding: 16px;
        border-radius: 12px;
        border: 1px dashed var(--panel-border, #e2e8f0);
        display: flex; flex-direction: column; gap: 12px;
    }

    /* Total display */
    .purch-total-bar {
        display: flex; justify-content: space-between; align-items: center;
        background: var(--panel-secondary, #f8fafc);
        padding: 16px 20px; border-radius: 12px;
    }

    /* Button loading state */
    .ios-btn.is-loading {
        pointer-events: none;
        opacity: 0.7;
        position: relative;
    }
    .ios-btn.is-loading::after {
        content: '';
        width: 14px; height: 14px;
        border: 2px solid transparent;
        border-top-color: currentColor;
        border-radius: 50%;
        animation: purchSpin 0.6s linear infinite;
        margin-left: 8px;
    }
    @keyframes purchSpin {
        to { transform: rotate(360deg); }
    }

    /* Fix ios-btn cramping */
    .ios-btn {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 8px 16px;
    }
    .ios-btn i { margin-right: 0 !important; }
</style>

<div class="content-header" style="margin-bottom: 24px;">
    <div>
        <h2 style="font-size: 24px; font-weight: 700; color: var(--text-heading); margin-bottom: 8px;">Purchasing & Suplier</h2>
        <p style="color: var(--text-muted); font-size: 14px;">Manajemen pembelian bahan baku, persetujuan PO, dan penerimaan barang masuk.</p>
    </div>
    <div style="display: flex; gap: 12px;">
        <button onclick="if(typeof window.purchasingApp !== 'undefined') { window.purchasingApp.init(); } else { alert('Javascript gagal dimuat!'); }" class="ios-btn ios-btn-secondary" style="background: transparent; color: var(--text-muted); border-color: transparent;"><i class="fa-solid fa-rotate-right"></i></button>
        <button onclick="purchasingApp.openPOModal()" class="ios-btn ios-btn-primary" id="btn-create-po-header"><i class="fa-solid fa-cart-plus"></i> Buat PO Baru</button>
    </div>
</div>

<!-- Main Tabs -->
<div class="ios-tabs" style="display: flex; gap: 16px; border-bottom: 1px solid var(--panel-border); margin-bottom: 24px; padding-bottom: 8px; overflow-x: auto;">
    <button class="ios-tab-main active" data-maintab="requests" onclick="purchasingApp.switchMainTab('requests')" style="background:none; border:none; color:var(--accent); font-weight:600; padding:8px 16px; border-bottom: 2px solid var(--accent); cursor:pointer; white-space: nowrap;">Purchase Requests</button>
    <button class="ios-tab-main" data-maintab="orders" onclick="purchasingApp.switchMainTab('orders')" style="background:none; border:none; color:var(--text-muted); font-weight:500; padding:8px 16px; cursor:pointer; white-space: nowrap;">Pesanan Pembelians</button>
    <button class="ios-tab-main" data-maintab="suppliers" onclick="purchasingApp.switchMainTab('suppliers')" style="background:none; border:none; color:var(--text-muted); font-weight:500; padding:8px 16px; cursor:pointer; white-space: nowrap;">Master Supplier</button>
    <button class="ios-tab-main" data-maintab="hierarchy" onclick="purchasingApp.switchMainTab('hierarchy')" style="background:none; border:none; color:var(--text-muted); font-weight:500; padding:8px 16px; cursor:pointer; white-space: nowrap;">Hierarki Approval PO</button>
</div>

<!-- VIEW: Purchase Requests -->
<div id="view-requests">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 4px 0; color: var(--text-heading);">Daftar Permintaan Pembelian</h3>
            <p style="margin: 0; font-size: 13px; color: var(--text-muted);"><i class="fa-solid fa-arrow-right-arrow-left" style="font-size: 11px; margin-right: 4px;"></i> Alur: Karyawan/Divisi &rarr; Purchasing</p>
        </div>
        <button onclick="purchasingApp.openPRModal()" class="ios-btn ios-btn-primary"><i class="fa-solid fa-file-pen" style="margin-right: 6px;"></i> Buat Pengajuan Baru</button>
    </div>
    <div id="purchasing-pr-container" style="display: flex; flex-direction: column; gap: 16px;">
        <div class="loader" style="margin: 40px auto; border-top-color: var(--accent);"></div>
    </div>
</div>

<!-- VIEW: Pesanan Pembelians -->
<div id="view-orders" style="display: none;">
    <div style="margin-bottom: 16px;">
        <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 4px 0; color: var(--text-heading);">Daftar Pesanan Pembelian</h3>
        <p style="margin: 0; font-size: 13px; color: var(--text-muted);"><i class="fa-solid fa-arrow-right-arrow-left" style="font-size: 11px; margin-right: 4px;"></i> Alur: Purchasing &rarr; Persetujuan (Manager/CEO) &rarr; Pemasok Eksternal</p>
    </div>
    <!-- Tabs for PO State -->
    <div class="ios-tabs" style="display: flex; gap: 16px; border-bottom: 1px solid var(--panel-border); margin-bottom: 24px; padding-bottom: 8px;">
        <button class="ios-tab active" data-tab="draft" onclick="purchasingApp.switchTab('draft')" style="background:none; border:none; color:var(--accent); font-weight:600; padding:8px 16px; border-bottom: 2px solid var(--accent); cursor:pointer;">Menunggu Persetujuan</button>
        <button class="ios-tab" data-tab="approved" onclick="purchasingApp.switchTab('approved')" style="background:none; border:none; color:var(--text-muted); font-weight:500; padding:8px 16px; cursor:pointer;">Sedang Dipesan (Menunggu Barang)</button>
        <button class="ios-tab" data-tab="completed" onclick="purchasingApp.switchTab('completed')" style="background:none; border:none; color:var(--text-muted); font-weight:500; padding:8px 16px; cursor:pointer;">Selesai (Diterima)</button>
    </div>

    <!-- Container for PO List -->
    <div id="purchasing-po-container" style="display: flex; flex-direction: column; gap: 16px;">
        <div class="loader" style="margin: 40px auto; border-top-color: var(--accent);"></div>
    </div>
</div>

<!-- VIEW: Master Supplier -->
<div id="view-suppliers" style="display: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div>
            <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 4px 0; color: var(--text-heading);">Daftar Master Supplier</h3>
            <p style="margin: 0; font-size: 13px; color: var(--text-muted);"><i class="fa-solid fa-arrow-right-arrow-left" style="font-size: 11px; margin-right: 4px;"></i> Alur: Purchasing &rarr; Evaluasi Profil &rarr; Database Pemasok</p>
        </div>
        <button onclick="purchasingApp.openSupplierModal()" class="ios-btn ios-btn-secondary"><i class="fa-solid fa-plus"></i> Tambah Supplier Baru</button>
    </div>
    <div id="supplier-list-container" style="display: flex; flex-direction: column; gap: 12px;">
        <div class="loader" style="margin: 40px auto; border-top-color: var(--accent);"></div>
    </div>
</div>

<!-- VIEW: Hierarchy -->
<div id="view-hierarchy" style="display: none;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <div>
            <h3 style="font-size: 16px; font-weight: 600; margin: 0 0 4px 0; color: var(--text-heading);">Hierarki Approval PO</h3>
            <p style="margin: 0; font-size: 13px; color: var(--text-muted);"><i class="fa-solid fa-arrow-right-arrow-left" style="font-size: 11px; margin-right: 4px;"></i> Alur: Sistem &rarr; Limit Nominal &rarr; Otorisasi Pejabat</p>
        </div>
    </div>
    <div style="text-align: center; padding: 40px 20px; background: rgba(255, 255, 255, 0.02); border: 1px dashed var(--panel-border); border-radius: 12px;">
        <i class="fa-solid fa-code-pull-request" style="font-size: 48px; color: var(--text-muted); margin-bottom: 16px; opacity: 0.5;"></i>
        <h4 style="margin: 0 0 8px 0; color: var(--text-heading);">Fitur dalam tahap pengembangan</h4>
        <p style="margin: 0; font-size: 14px; color: var(--text-muted);">Pengaturan rantai persetujuan Pesanan Pembelian akan tersedia di pembaruan berikutnya.</p>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════ -->
<!-- Modal: Master Supplier                                  -->
<!-- ════════════════════════════════════════════════════════ -->
<div id="modal-purchasing-supplier" class="ios-modal-overlay purchasing-modal" style="display: none;" onclick="if(event.target===this) purchasingApp.closeSupplierModal()">
    <div class="ios-modal" style="width: 600px; max-width: 95vw;">
        <div class="purch-modal-header">
            <h3><i class="fa-solid fa-users" style="color:var(--accent);"></i> Master Supplier</h3>
            <button class="purch-modal-close" onclick="purchasingApp.closeSupplierModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="purch-modal-body">
            <form id="form-add-supplier" onsubmit="purchasingApp.submitSupplier(event)">
                <h4 style="margin: 0 0 16px 0; font-size: 15px; font-weight: 600; color: var(--text-heading);">Informasi Supplier Baru</h4>
                <div class="purch-form-grid purch-form-grid-2">
                    <div>
                        <label class="purch-label">Nama Supplier / Toko</label>
                        <input type="text" name="name" class="ios-input" placeholder="Nama Supplier / Toko" required>
                    </div>
                    <div>
                        <label class="purch-label">Nama Kontak (PIC)</label>
                        <input type="text" name="contact_person" class="ios-input" placeholder="Nama Kontak (PIC)">
                    </div>
                </div>
                <div class="purch-form-grid purch-form-grid-2">
                    <div>
                        <label class="purch-label">No HP / WhatsApp</label>
                        <input type="text" name="phone" class="ios-input" placeholder="No HP / WhatsApp" required>
                    </div>
                    <div>
                        <label class="purch-label">Email</label>
                        <input type="email" name="email" class="ios-input" placeholder="Email">
                    </div>
                </div>
                <div style="margin-bottom: 4px;">
                    <label class="purch-label">Alamat Lengkap</label>
                    <textarea name="address" class="ios-input" placeholder="Alamat Lengkap" rows="2"></textarea>
                </div>
            </form>
        </div>
        <div class="purch-modal-footer">
            <button type="button" class="ios-btn ios-btn-secondary" onclick="purchasingApp.closeSupplierModal()">Tutup</button>
            <button type="submit" form="form-add-supplier" class="ios-btn ios-btn-primary" id="btn-save-supplier"><i class="fa-solid fa-check" style="margin-right: 6px;"></i> Simpan Supplier</button>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════ -->
<!-- Modal: Buat Purchase Request (PR)                        -->
<!-- ════════════════════════════════════════════════════════ -->
<div id="modal-purchasing-pr" class="ios-modal-overlay purchasing-modal" style="display: none;" onclick="if(event.target===this) purchasingApp.closePRModal()">
    <div class="ios-modal" style="width: 700px; max-width: 95vw;">
        <div class="purch-modal-header">
            <h3 class="purch-modal-title">Buat Pengajuan Baru (PR)</h3>
            <button class="purch-modal-close" onclick="purchasingApp.closePRModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="form-add-pr" onsubmit="purchasingApp.submitPRForm(event)">
            <div class="purch-modal-body">
                <div class="purch-form-grid" style="grid-template-columns: 1fr;">
                    <div>
                        <label class="purch-label">Judul Keperluan <span style="color:#ef4444">*</span></label>
                        <input type="text" class="ios-input" id="pr-title" placeholder="Contoh: Stok Kertas HVS Bulan Agustus" required>
                    </div>
                    <div>
                        <label class="purch-label">Alasan / Catatan (Opsional)</label>
                        <textarea class="ios-input" id="pr-reason" rows="2" placeholder="Catatan tambahan..."></textarea>
                    </div>
                </div>

                <div style="margin-top: 24px; margin-bottom: 8px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <h4 style="margin: 0; font-size: 14px; font-weight: 600; color: var(--text-heading);">Daftar Barang</h4>
                            <span style="font-size: 11px; color: var(--text-muted);">(Belum ada produk? <a onclick="purchasingApp.closePRModal(); switchView('inventory_umkm')" style="color: var(--accent); text-decoration: underline; cursor: pointer;">Tambah di Gudang</a>)</span>
                        </div>
                        <button type="button" class="ios-btn ios-btn-secondary" onclick="purchasingApp.addPRRow()" style="padding: 4px 12px; font-size: 12px;"><i class="fa-solid fa-plus"></i> Tambah</button>
                    </div>
                    <div id="pr-lines-container" style="display: flex; flex-direction: column; gap: 8px; max-height: 250px; overflow-y: auto; padding-right: 4px;">
                        <!-- PR lines injected by JS -->
                    </div>
                </div>
            </div>
            <div class="purch-modal-footer">
                <button type="button" class="ios-btn ios-btn-secondary" onclick="purchasingApp.closePRModal()">Batal</button>
                <button type="submit" class="ios-btn ios-btn-primary" id="btn-save-pr"><i class="fa-solid fa-save" style="margin-right: 6px;"></i> Simpan Draft</button>
            </div>
        </form>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════ -->
<!-- Modal: Buat PO                                          -->
<!-- ════════════════════════════════════════════════════════ -->
<div id="modal-purchasing-po" class="ios-modal-overlay purchasing-modal" style="display: none;" onclick="if(event.target===this) purchasingApp.closePOModal()">
    <div class="ios-modal" style="width: 700px; max-width: 95vw;">
        <div class="purch-modal-header">
            <h3 id="po-modal-title"><i class="fa-solid fa-cart-plus" style="color:var(--accent);"></i> Form Pesanan Pembelian (PO)</h3>
            <button class="purch-modal-close" onclick="purchasingApp.closePOModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="form-po" onsubmit="purchasingApp.submitPO(event)">
            <input type="hidden" id="po-id" name="po_id" value="">
            <div class="purch-modal-body">
                <div class="purch-form-grid purch-form-grid-2" style="margin-bottom: 24px;">
                    <div>
                        <label class="purch-label">Pilih Supplier</label>
                        <select name="supplier_id" id="po-supplier-select" class="ios-input" required>
                            <option value="">-- Loading --</option>
                        </select>
                    </div>
                    <div>
                        <label class="purch-label">Ekspektasi Kedatangan</label>
                        <input type="date" name="expected_date" class="ios-input" required>
                    </div>
                </div>
                
                <div style="margin-bottom: 24px;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <label class="purch-label" style="margin-bottom:0;">Item Bahan Baku / Barang</label>
                        <div>
                            <button type="button" onclick="purchasingApp.openQuickAddProduct()" class="ios-btn ios-btn-secondary" style="padding: 4px 10px; font-size: 12px; margin-right: 8px;"><i class="fa-solid fa-box"></i> + Barang Baru</button>
                            <button type="button" onclick="purchasingApp.addPOLine()" class="ios-btn ios-btn-secondary" style="padding: 4px 10px; font-size: 12px;"><i class="fa-solid fa-plus"></i> Tambah Item</button>
                        </div>
                    </div>
                    <div id="po-lines-container" class="purch-po-lines">
                        <!-- Dynamic lines will be appended here -->
                    </div>
                </div>
                
                <div class="purch-total-bar">
                    <span style="font-weight: 600; color: var(--text-muted);">Total Estimasi:</span>
                    <span id="po-total-display" style="font-size: 20px; font-weight: 700; color: var(--text-heading);">Rp 0</span>
                </div>
            </div>
            <div class="purch-modal-footer">
                <button type="button" class="ios-btn ios-btn-secondary" onclick="purchasingApp.closePOModal()">Batal</button>
                <button type="submit" class="ios-btn ios-btn-primary" id="btn-save-po"><i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Kirim PO</button>
            </div>
        </form>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════ -->
<!-- Modal: Edit Supplier                                    -->
<!-- ════════════════════════════════════════════════════════ -->
<div id="modal-purchasing-supplier-edit" class="ios-modal-overlay purchasing-modal" style="display: none;" onclick="if(event.target===this) purchasingApp.closeEditSupplierModal()">
    <div class="ios-modal" style="width: 500px; max-width: 95vw;">
        <div class="purch-modal-header">
            <h3><i class="fa-solid fa-user-pen" style="color:var(--accent);"></i> Edit Supplier</h3>
            <button class="purch-modal-close" onclick="purchasingApp.closeEditSupplierModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="form-edit-supplier" onsubmit="purchasingApp.updateSupplier(event)">
            <input type="hidden" name="supplier_id" id="edit-supplier-id">
            <div class="purch-modal-body">
                <div class="purch-form-grid purch-form-grid-2">
                    <div>
                        <label class="purch-label">Nama Supplier / Toko</label>
                        <input type="text" name="name" id="edit-supplier-name" class="ios-input" required>
                    </div>
                    <div>
                        <label class="purch-label">Nama Kontak (PIC)</label>
                        <input type="text" name="contact_person" id="edit-supplier-contact" class="ios-input">
                    </div>
                </div>
                <div class="purch-form-grid purch-form-grid-2">
                    <div>
                        <label class="purch-label">No HP / WhatsApp</label>
                        <input type="text" name="phone" id="edit-supplier-phone" class="ios-input" required>
                    </div>
                    <div>
                        <label class="purch-label">Email</label>
                        <input type="email" name="email" id="edit-supplier-email" class="ios-input">
                    </div>
                </div>
                <div>
                    <label class="purch-label">Alamat Lengkap</label>
                    <textarea name="address" id="edit-supplier-address" class="ios-input" rows="2"></textarea>
                </div>
            </div>
            <div class="purch-modal-footer">
                <button type="button" class="ios-btn ios-btn-secondary" onclick="purchasingApp.closeEditSupplierModal()">Batal</button>
                <button type="submit" class="ios-btn ios-btn-primary" id="btn-update-supplier"><i class="fa-solid fa-floppy-disk" style="margin-right: 6px;"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════ -->
<!-- Modal: Quick Add Product                                -->
<!-- ════════════════════════════════════════════════════════ -->
<div id="modal-purchasing-product" class="ios-modal-overlay purchasing-modal" style="display: none; z-index: 9005;" onclick="if(event.target===this) purchasingApp.closeQuickAddProduct()">
    <div class="ios-modal" style="width: 400px; max-width: 95vw;">
        <div class="purch-modal-header">
            <h3><i class="fa-solid fa-box" style="color:var(--accent);"></i> Tambah Barang Baru</h3>
            <button class="purch-modal-close" onclick="purchasingApp.closeQuickAddProduct()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="form-quick-product" onsubmit="purchasingApp.submitQuickProduct(event)">
            <div class="purch-modal-body">
                <div style="margin-bottom: 12px;">
                    <label class="purch-label">SKU / Kode Barang</label>
                    <input type="text" name="sku" class="ios-input" placeholder="Isi kode unik (cth: BRG-001)" required>
                </div>
                <div style="margin-bottom: 12px;">
                    <label class="purch-label">Nama Barang</label>
                    <input type="text" name="name" class="ios-input" required>
                </div>
                <div style="margin-bottom: 12px;">
                    <label class="purch-label">Satuan (Unit)</label>
                    <input type="text" name="unit" class="ios-input" placeholder="Pcs, Kg, Liter, dll">
                </div>
                <div style="margin-bottom: 12px;">
                    <label class="purch-label">Harga Beli Standar (opsional)</label>
                    <input type="number" name="standard_cost" class="ios-input" min="0" value="0">
                </div>
            </div>
            <div class="purch-modal-footer">
                <button type="button" class="ios-btn ios-btn-secondary" onclick="purchasingApp.closeQuickAddProduct()">Batal</button>
                <button type="submit" class="ios-btn ios-btn-primary" id="btn-save-quick-product"><i class="fa-solid fa-check" style="margin-right: 6px;"></i> Simpan Barang</button>
            </div>
        </form>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════ -->
<!-- Modal: Terima Barang (Barang Masuk)                    -->
<!-- ════════════════════════════════════════════════════════ -->
<div id="modal-purchasing-gr" class="ios-modal-overlay purchasing-modal" style="display: none;" onclick="if(event.target===this) purchasingApp.closeGRModal()">
    <div class="ios-modal" style="width: 550px; max-width: 95vw;">
        <div class="purch-modal-header">
            <h3><i class="fa-solid fa-box-open" style="color: var(--text-accent);"></i> Penerimaan Barang (Barang Masuk)</h3>
            <button class="purch-modal-close" onclick="purchasingApp.closeGRModal()"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="form-gr" onsubmit="purchasingApp.submitGR(event)">
            <div class="purch-modal-body">
                <p style="font-size: 14px; color: var(--text-muted); margin: 0 0 20px 0;">Pastikan jumlah fisik yang dikirim oleh supplier sesuai dengan pesanan PO ini.</p>
                <input type="hidden" name="purchase_order_id" id="gr-po-id">
                <div id="gr-lines-container" style="display: flex; flex-direction: column; gap: 12px; max-height: 280px; overflow-y: auto; padding-right: 4px;">
                    <!-- items to receive -->
                </div>
            </div>
            <div class="purch-modal-footer">
                <button type="button" class="ios-btn ios-btn-secondary" onclick="purchasingApp.closeGRModal()">Batal</button>
                <button type="submit" class="ios-btn ios-btn-primary" id="btn-save-gr" style="background: #0C3527; border-color: var(--text-accent);"><i class="fa-solid fa-check" style="margin-right: 6px;"></i> Terima Barang & Masukkan Stok</button>
            </div>
        </form>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════ -->
<!-- Modal: Konfirmasi Hapus Supplier                         -->
<!-- ════════════════════════════════════════════════════════ -->
<div id="modal-purchasing-confirm-delete" class="ios-modal-overlay purchasing-modal" style="display: none; z-index: 9999;" onclick="if(event.target===this) purchasingApp.closeConfirmDelete()">
    <div class="ios-modal" style="width: 400px; max-width: 90vw; text-align: center; padding: 24px 20px 20px;">
        <div style="margin-bottom: 16px;">
            <div style="width: 56px; height: 56px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
        </div>
        <h3 style="font-size: 18px; font-weight: 700; color: var(--text-heading); margin: 0 0 8px 0;">Hapus Master Supplier?</h3>
        <p style="font-size: 14px; color: var(--text-muted); margin: 0 0 24px 0; line-height: 1.5;">
            Apakah Anda yakin ingin menghapus data supplier ini secara permanen? Data yang telah terhapus tidak dapat dikembalikan.
        </p>
        <div style="display: flex; gap: 12px; justify-content: center;">
            <button type="button" class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="purchasingApp.closeConfirmDelete()">Batal</button>
            <button type="button" class="ios-btn ios-btn-primary" id="btn-confirm-delete" style="flex: 1; background: #ef4444; border-color: #ef4444;" onclick="purchasingApp.executeDeleteSupplier()"><i class="fa-solid fa-trash" style="margin-right: 6px;"></i> Ya, Hapus</button>
        </div>
    </div>
</div>

<script>
    window.purchasingConfig = {
        isCeo: {{ (auth()->check() && auth()->user()->isCEO()) ? 'true' : 'false' }},
        isManager: {{ (auth()->check() && auth()->user()->isManager()) ? 'true' : 'false' }}
    };
</script>
<script src="{{ asset('js/purchasing.js') }}?v={{ time() }}"></script>
