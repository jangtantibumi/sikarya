/* ═══════════════════════════════════════════════════════════════
   Purchasing Module — purchasing.js
   Handles: Supplier CRUD, PO lifecycle, Goods Receipt
   ═══════════════════════════════════════════════════════════════ */

// ── Toast Fallback ─────────────────────────────────────────────
// The parent layout (master-portal / employee-portal) may or may not
// define a global showToast(). We provide a self-contained fallback.
if (typeof window.showToast !== 'function') {
    window.showToast = function(message, type) {
        // Create toast container if not exists
        let container = document.getElementById('purch-toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'purch-toast-container';
            container.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:99999;display:flex;flex-direction:column;gap:10px;pointer-events:none;';
            document.body.appendChild(container);
        }

        const isError = type === 'error';
        const toast = document.createElement('div');
        toast.style.cssText = `
            pointer-events:auto; padding:14px 20px; border-radius:14px;
            font-size:14px; font-weight:600; display:flex; align-items:center; gap:10px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px);
            animation: purchToastIn 0.3s ease-out forwards;
            background: ${isError ? 'rgba(220,38,38,0.92)' : 'rgba(12,53,39,0.92)'};
            color: #fff; max-width: 380px; line-height: 1.4;
        `;
        toast.innerHTML = `<i class="fa-solid ${isError ? 'fa-circle-exclamation' : 'fa-circle-check'}" style="font-size:16px;"></i> ${message}`;
        container.appendChild(toast);

        setTimeout(function() {
            toast.style.animation = 'purchToastOut 0.25s ease-in forwards';
            setTimeout(function() { toast.remove(); }, 250);
        }, 4000);
    };

    // Inject keyframes if not yet present
    if (!document.getElementById('purch-toast-keyframes')) {
        const style = document.createElement('style');
        style.id = 'purch-toast-keyframes';
        style.textContent = `
            @keyframes purchToastIn { from { opacity:0; transform:translateY(16px); } to { opacity:1; transform:translateY(0); } }
            @keyframes purchToastOut { from { opacity:1; transform:translateY(0); } to { opacity:0; transform:translateY(-12px); } }
        `;
        document.head.appendChild(style);
    }
}

// ── CSRF Helper ────────────────────────────────────────────────
function getCSRF() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

// ── Button Loading Helper ──────────────────────────────────────
function setButtonLoading(btnId, loading) {
    const btn = document.getElementById(btnId);
    if (!btn) return;
    if (loading) {
        btn.classList.add('is-loading');
        btn.dataset.origText = btn.innerHTML;
        const labelMap = {
            'btn-save-supplier': '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i> Menyimpan...',
            'btn-update-supplier': '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i> Menyimpan...',
            'btn-save-po': '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i> Mengirim...',
            'btn-save-gr': '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i> Memproses...',
            'btn-save-quick-product': '<i class="fa-solid fa-spinner fa-spin" style="margin-right:6px;"></i> Menyimpan...',
        };
        btn.innerHTML = labelMap[btnId] || '<i class="fa-solid fa-spinner fa-spin"></i>';
    } else {
        btn.classList.remove('is-loading');
        if (btn.dataset.origText) btn.innerHTML = btn.dataset.origText;
    }
}

// ── API Request Helper ─────────────────────────────────────────
async function purchasingFetch(url, method, body) {
    const opts = {
        method: method || 'GET',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': getCSRF()
        }
    };
    if (body) opts.body = JSON.stringify(body);

    const res = await fetch(url, opts);

    if (res.status === 419) {
        showToast('Sesi login kedaluwarsa. Silakan refresh halaman.', 'error');
        throw new Error('CSRF_EXPIRED');
    }
    if (res.status === 403) {
        const data = await res.json().catch(() => ({}));
        showToast(data.message || 'Akses ditolak. Modul belum diaktifkan.', 'error');
        throw new Error('FORBIDDEN');
    }
    if (!res.ok) {
        const data = await res.json().catch(() => ({}));
        const msg = data.message || data.errors ? Object.values(data.errors || {}).flat().join(', ') : `Error ${res.status}`;
        showToast(msg, 'error');
        throw new Error(msg);
    }
    return res.json();
}

// ═══════════════════════════════════════════════════════════════
// PURCHASING APP
// ═══════════════════════════════════════════════════════════════
window.purchasingApp = {
    products: [],
    suppliers: [],
    pos: [],
    prs: [],
    currentMainTab: 'requests',
    currentTab: 'draft',
    supplierToDelete: null,
    isInitialized: false,

    // ── Init ───────────────────────────────────────────────────
    init: async function() {
        if (this.isInitialized) return;
        
        showToast('Memuat data modul...');
        await Promise.all([
            this.loadProducts(),
            this.loadSuppliers(),
            this.loadPOs(),
            this.loadPRs()
        ]);
        this.switchMainTab(this.currentMainTab);
        this.switchTab(this.currentTab);
        
        this.isInitialized = true;
    },

    // ── Load Products ──────────────────────────────────────────
    loadProducts: async function() {
        try {
            const res = await fetch('/master-demo/inventory-umkm', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCSRF() }
            });
            if (!res.ok) throw new Error(`HTTP Error ${res.status}`);
            const data = await res.json();
            this.products = data.map(item => ({
                id: item.id,
                name: item.item_name
            }));
        } catch(e) { console.error('loadProducts error:', e); }
    },

    // ── Load Suppliers ─────────────────────────────────────────
    loadSuppliers: async function() {
        try {
            const res = await fetch('/api/purchasing/suppliers', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCSRF() }
            });
            if (!res.ok) throw new Error(`HTTP Error ${res.status}`);
            this.suppliers = await res.json();
            this.renderSupplierSelect();
            this.renderSupplierList();
        } catch(e) { console.error('loadSuppliers error:', e); }
    },

    // ── Load POs ───────────────────────────────────────────────
    loadPOs: async function() {
        const container = document.getElementById('purchasing-po-container');
        if (!container) return;
        container.innerHTML = '<div class="loader" style="margin: 40px auto; border-top-color: var(--accent);"></div>';

        try {
            const res = await fetch('/api/purchasing/orders', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCSRF() }
            });
            if (!res.ok) throw new Error(`HTTP Error ${res.status}`);
            this.pos = await res.json();
            this.renderPOs();
        } catch(e) {
            console.error('loadPOs error:', e);
            container.innerHTML = `<div style="color:var(--danger, #ef4444); text-align:center; padding: 20px; font-weight: bold;">Gagal memuat data PO. Error: ${e.message}</div>`;
        }
    },

    // ── Load PRs ───────────────────────────────────────────────
    loadPRs: async function() {
        try {
            const res = await fetch('/api/purchasing/requests', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCSRF() }
            });
            if (res.ok) {
                this.prs = await res.json();
                if (this.currentMainTab === 'requests') this.renderPRList();
            }
        } catch (e) {
            console.error('Failed to load PRs', e);
        }
    },

    // ── Render PR List ─────────────────────────────────────────
    renderPRList: function() {
        const container = document.getElementById('purchasing-pr-container');
        if (!container) return;
        if (this.prs.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 48px; background: rgba(0,0,0,0.02); border: 1px dashed var(--panel-border); border-radius: 12px; color: var(--text-muted);">
                    <i class="fa-solid fa-file-invoice" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5;"></i><br>
                    Belum ada pengajuan.
                </div>
            `;
            return;
        }

        const isManagerOrCEO = window.purchasingConfig ? (window.purchasingConfig.isCeo || window.purchasingConfig.isManager) : false;

        let html = '';
        this.prs.forEach(pr => {
            let badge = '';
            if (pr.status === 'draft') badge = '<span style="display:inline-block; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; background:rgba(156,163,175,0.15); color:#6b7280;">Draft</span>';
            else if (pr.status === 'submitted') badge = '<span style="display:inline-block; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; background:rgba(245,158,11,0.15); color:#f59e0b;">Pending</span>';
            else if (pr.status === 'approved') badge = '<span style="display:inline-block; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; background:rgba(16,185,129,0.15); color:#10b981;">Approved</span>';
            else if (pr.status === 'rejected') badge = '<span style="display:inline-block; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:600; background:rgba(239,68,68,0.15); color:#ef4444;">Rejected</span>';
            
            let actions = '';
            if (pr.status === 'draft' || pr.status === 'rejected') {
                actions += `<button onclick="purchasingApp.submitPRAction(${pr.id})" class="ios-btn ios-btn-primary" style="padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-paper-plane"></i> Submit</button>`;
            } else if (pr.status === 'submitted' && isManagerOrCEO) {
                actions += `
                    <button onclick="purchasingApp.decidePRAction(${pr.id}, 'approved')" class="ios-btn ios-btn-primary" style="padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-check"></i> Setujui</button>
                    <button onclick="purchasingApp.decidePRAction(${pr.id}, 'rejected')" class="ios-btn ios-btn-secondary" style="padding: 6px 12px; font-size: 12px; color: #ef4444;"><i class="fa-solid fa-xmark"></i> Tolak</button>
                `;
            }

            let linesHtml = '';
            if (pr.lines && pr.lines.length > 0) {
                linesHtml = pr.lines.map(l => `<div style="font-size:12px; color:var(--text-muted);"><i class="fa-solid fa-circle" style="font-size:4px; vertical-align:middle; margin-right:4px;"></i> ${l.product ? l.product.name : 'Unknown'} (${l.quantity})</div>`).join('');
            }

            html += `
                <div style="background: var(--panel); border: 1px solid var(--panel-border); border-radius: 12px; padding: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; flex-wrap: wrap; gap: 8px;">
                        <div style="min-width: 0;">
                            <div style="font-weight: 600; color: var(--text-heading); font-size: 15px; margin-bottom: 4px;">${pr.title}</div>
                            <div style="font-size: 12px; color: var(--text-muted); font-family: monospace;">${pr.number} &bull; ${new Date(pr.created_at).toLocaleDateString('id-ID')}</div>
                            ${pr.reason ? `<div style="font-size: 12px; color: var(--text-muted); margin-top: 4px; font-style: italic;">"${pr.reason}"</div>` : ''}
                        </div>
                        <div>${badge}</div>
                    </div>
                    ${linesHtml ? `<div style="margin-bottom: 16px; background: var(--panel-secondary, #f8fafc); padding: 8px 12px; border-radius: 8px;">${linesHtml}</div>` : ''}
                    ${actions ? `<div style="display: flex; gap: 8px; justify-content: flex-end; border-top: 1px solid var(--panel-border); padding-top: 12px;">${actions}</div>` : ''}
                </div>
            `;
        });
        container.innerHTML = html;
    },

    // ── Render PO List (alias) ─────────────────────────────────
    renderPOList: function() {
        this.renderPOs();
    },

    // ── Main Tab Switch ────────────────────────────────────────
    switchMainTab: function(tabName) {
        this.currentMainTab = tabName;
        document.querySelectorAll('.ios-tab-main').forEach(function(btn) {
            btn.style.borderBottom = 'none';
            btn.style.color = 'var(--text-muted)';
            btn.style.fontWeight = '500';
        });
        const activeBtn = document.querySelector('.ios-tab-main[data-maintab="' + tabName + '"]');
        if (activeBtn) {
            activeBtn.style.borderBottom = '2px solid var(--accent)';
            activeBtn.style.color = 'var(--accent)';
            activeBtn.style.fontWeight = '600';
        }
        
        if (tabName === 'orders') {
            document.getElementById('view-requests').style.display = 'none';
            document.getElementById('view-orders').style.display = 'block';
            document.getElementById('view-suppliers').style.display = 'none';
            this.renderPOList();
        } else if (tabName === 'requests') {
            document.getElementById('view-requests').style.display = 'block';
            document.getElementById('view-orders').style.display = 'none';
            document.getElementById('view-suppliers').style.display = 'none';
            this.renderPRList();
        } else {
            document.getElementById('view-requests').style.display = 'none';
            document.getElementById('view-orders').style.display = 'none';
            document.getElementById('view-suppliers').style.display = 'block';
            this.renderSupplierList();
        }
    },

    // ── Tab Switch ─────────────────────────────────────────────
    switchTab: function(tabName) {
        this.currentTab = tabName;
        document.querySelectorAll('.ios-tab').forEach(function(btn) {
            btn.style.borderBottom = 'none';
            btn.style.color = 'var(--text-muted)';
            btn.style.fontWeight = '500';
        });
        const activeBtn = document.querySelector('.ios-tab[data-tab="' + tabName + '"]');
        if (activeBtn) {
            activeBtn.style.borderBottom = '2px solid var(--accent)';
            activeBtn.style.color = 'var(--accent)';
            activeBtn.style.fontWeight = '600';
        }
        this.renderPOs();
    },

    // ── Render PO List ─────────────────────────────────────────
    renderPOs: function() {
        const container = document.getElementById('purchasing-po-container');
        let filteredPOs = [];

        if (this.currentTab === 'draft') {
            filteredPOs = this.pos.filter(function(p) { return p.status === 'draft' || p.status === 'submitted'; });
        } else if (this.currentTab === 'approved') {
            filteredPOs = this.pos.filter(function(p) { return p.status === 'approved' || p.status === 'partial'; });
        } else {
            filteredPOs = this.pos.filter(function(p) { return p.status === 'completed' || p.status === 'rejected'; });
        }

        if (filteredPOs.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 48px; background: rgba(0,0,0,0.02); border: 1px dashed var(--panel-border); border-radius: 12px; color: var(--text-muted);">
                    <i class="fa-solid fa-folder-open" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5;"></i><br>
                    Tidak ada Purchase Order di kategori ini.
                </div>
            `;
            return;
        }

        const isCeo = window.purchasingConfig ? window.purchasingConfig.isCeo : false;
        let html = '';

        filteredPOs.forEach(function(po) {
            const supplierName = po.supplier ? po.supplier.name : 'Unknown Supplier';
            const date = new Date(po.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'});

            let actionBtn = '';
            if (po.status === 'submitted' && isCeo) {
                actionBtn = `
                    <button onclick="purchasingApp.editPO(${po.id})" class="ios-btn ios-btn-secondary" style="padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-pen"></i> Edit</button>
                    <button onclick="purchasingApp.approvePO(${po.id})" class="ios-btn ios-btn-primary" style="padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-check"></i> Setujui</button>
                    <button onclick="purchasingApp.rejectPO(${po.id})" class="ios-btn ios-btn-secondary" style="padding: 6px 12px; font-size: 12px; color: #ef4444;"><i class="fa-solid fa-xmark"></i> Tolak</button>
                `;
            } else if (po.status === 'draft' && isCeo) {
                actionBtn = `<button onclick="purchasingApp.editPO(${po.id})" class="ios-btn ios-btn-secondary" style="padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-pen"></i> Edit</button>`;
            } else if (po.status === 'approved') {
                actionBtn = `<button onclick="purchasingApp.openGRModal(${po.id})" class="ios-btn ios-btn-primary" style="background: #10b981; border-color: #10b981; padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-box-open"></i> Terima Barang</button>`;
            }

            let statusBadge = '<span class="suba-badge-native" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">Menunggu Persetujuan</span>';
            if (po.status === 'approved') statusBadge = '<span class="suba-badge-native" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">Sedang Dipesan</span>';
            if (po.status === 'completed') statusBadge = '<span class="suba-badge-native" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">Selesai (Diterima)</span>';
            if (po.status === 'rejected') statusBadge = '<span class="suba-badge-native" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">Ditolak</span>';

            html += `
                <div style="background: var(--panel); border: 1px solid var(--panel-border); border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                    <div style="display: flex; flex-direction: column; gap: 8px;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span style="font-weight: 700; color: var(--text-heading); font-size: 16px;">${po.number}</span>
                            ${statusBadge}
                        </div>
                        <div style="font-size: 14px; color: var(--text-muted); display: flex; gap: 16px;">
                            <span><i class="fa-solid fa-store" style="margin-right: 4px;"></i> ${supplierName}</span>
                            <span><i class="fa-regular fa-calendar" style="margin-right: 4px;"></i> ${date}</span>
                            <span style="font-weight: 600; color: var(--text-heading);">Rp ${Number(po.total_amount).toLocaleString('id-ID')}</span>
                        </div>
                        <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
                            Items: ${po.lines ? po.lines.map(function(l) { return (l.product ? l.product.name : 'Item') + ' (' + l.ordered_quantity + ')'; }).join(', ') : '-'}
                        </div>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        ${actionBtn}
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
    },

    // ═══════════════════════════════════════════════════════════
    // SUPPLIER MODAL
    // ═══════════════════════════════════════════════════════════
    openSupplierModal: function() {
        document.getElementById('modal-purchasing-supplier').style.display = 'flex';
    },
    closeSupplierModal: function() {
        document.getElementById('modal-purchasing-supplier').style.display = 'none';
    },

    renderSupplierSelect: function() {
        const select = document.getElementById('po-supplier-select');
        if (!select) return;
        select.innerHTML = '<option value="">-- Pilih Supplier --</option>';
        this.suppliers.forEach(function(s) {
            select.innerHTML += '<option value="' + s.id + '">' + s.name + ' (' + (s.phone || s.code || '-') + ')</option>';
        });
    },

    renderSupplierList: function() {
        const container = document.getElementById('supplier-list-container');
        if (!container) return;
        if (this.suppliers.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 48px; background: rgba(0,0,0,0.02); border: 1px dashed var(--panel-border); border-radius: 12px; color: var(--text-muted);">
                    <i class="fa-solid fa-users-slash" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5;"></i><br>
                    Belum ada supplier terdaftar.
                </div>
            `;
            return;
        }
        let html = '<table style="width:100%; border-collapse:collapse; background: var(--panel); border: 1px solid var(--panel-border); border-radius: 12px; overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">';
        html += `
            <thead style="background: var(--panel-secondary, #f8fafc); border-bottom: 1px solid var(--panel-border);">
                <tr>
                    <th style="padding: 12px 16px; text-align: left; font-size: 13px; color: var(--text-muted);">Nama Supplier</th>
                    <th style="padding: 12px 16px; text-align: left; font-size: 13px; color: var(--text-muted);">Kontak / PIC</th>
                    <th style="padding: 12px 16px; text-align: left; font-size: 13px; color: var(--text-muted);">Kode</th>
                    <th style="padding: 12px 16px; text-align: right; font-size: 13px; color: var(--text-muted);">Aksi</th>
                </tr>
            </thead>
            <tbody>
        `;
        const isCeo = window.purchasingConfig ? window.purchasingConfig.isCeo : false;
        
        this.suppliers.forEach(function(s) {
            const pic = (s.contacts && s.contacts.length > 0) ? s.contacts[0].name : (s.contact_person || '-');
            const actionBtns = isCeo ? `
                <button onclick="purchasingApp.editSupplier(${s.id})" class="ios-btn ios-btn-secondary" style="padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-pen"></i> Edit</button>
                <button onclick="purchasingApp.confirmDeleteSupplier(${s.id})" class="ios-btn ios-btn-secondary" style="padding: 6px 12px; font-size: 12px; color: #ef4444;"><i class="fa-solid fa-trash"></i> Hapus</button>
            ` : '<span style="font-size: 12px; color: var(--text-muted);"><i class="fa-solid fa-lock"></i> Akses Terbatas</span>';

            html += `
                <tr style="border-bottom: 1px solid var(--panel-border);">
                    <td style="padding: 14px 16px; font-weight:600; color: var(--text-heading); font-size: 14px;">
                        ${s.name}
                        <div style="font-size: 12px; color: var(--text-muted); font-weight: normal; margin-top: 4px;"><i class="fa-solid fa-phone" style="font-size: 10px;"></i> ${s.phone || '-'}</div>
                    </td>
                    <td style="padding: 14px 16px; color:var(--text-muted); font-size: 14px;">${pic}</td>
                    <td style="padding: 14px 16px; color:var(--text-muted); font-size: 13px;"><span style="background: var(--panel-secondary); padding: 4px 8px; border-radius: 6px;">${s.code || ''}</span></td>
                    <td style="padding: 14px 16px; text-align:right; display: flex; gap: 8px; justify-content: flex-end;">${actionBtns}</td>
                </tr>
            `;
        });
        html += '</tbody></table>';
        container.innerHTML = html;
    },

    // ── Submit Supplier ────────────────────────────────────────
    submitSupplier: async function(e) {
        e.preventDefault();
        const form = e.target;

        // Collect ALL form fields (previously only name + phone were sent)
        const data = {
            name: form.name.value.trim(),
            phone: form.phone.value.trim(),
            email: form.email ? form.email.value.trim() : '',
            address: form.address ? form.address.value.trim() : '',
            contact_person: form.contact_person ? form.contact_person.value.trim() : ''
        };

        if (!data.name) {
            showToast('Nama supplier wajib diisi.', 'error');
            return;
        }

        setButtonLoading('btn-save-supplier', true);

        try {
            await purchasingFetch('/api/purchasing/suppliers', 'POST', data);
            showToast('Supplier berhasil ditambahkan!');
            form.reset();
            this.loadSuppliers();
        } catch(err) {
            // Error already handled in purchasingFetch
        } finally {
            setButtonLoading('btn-save-supplier', false);
        }
    },

    editSupplier: function(id) {
        const s = this.suppliers.find(sup => sup.id === id);
        if (!s) return;
        document.getElementById('edit-supplier-id').value = s.id;
        document.getElementById('edit-supplier-name').value = s.name;
        document.getElementById('edit-supplier-phone').value = s.phone || '';
        document.getElementById('edit-supplier-email').value = s.email || '';
        document.getElementById('edit-supplier-address').value = s.address || '';
        document.getElementById('edit-supplier-contact').value = (s.contacts && s.contacts.length > 0) ? s.contacts[0].name : '';
        document.getElementById('modal-purchasing-supplier-edit').style.display = 'flex';
    },

    closeEditSupplierModal: function() {
        document.getElementById('modal-purchasing-supplier-edit').style.display = 'none';
    },

    updateSupplier: async function(e) {
        e.preventDefault();
        const form = e.target;
        const id = form.supplier_id.value;

        const data = {
            name: form.name.value.trim(),
            phone: form.phone.value.trim(),
            email: form.email.value.trim(),
            address: form.address.value.trim(),
            contact_person: form.contact_person.value.trim()
        };

        if (!data.name) {
            showToast('Nama supplier wajib diisi.', 'error');
            return;
        }

        setButtonLoading('btn-update-supplier', true);

        try {
            await purchasingFetch('/api/purchasing/suppliers/' + id, 'PUT', data);
            showToast('Supplier berhasil diperbarui!');
            this.closeEditSupplierModal();
            this.loadSuppliers();
        } catch(err) {
            // Handled
        } finally {
            setButtonLoading('btn-update-supplier', false);
        }
    },

    confirmDeleteSupplier: function(id) {
        this.supplierToDelete = id;
        document.getElementById('modal-purchasing-confirm-delete').style.display = 'flex';
    },

    closeConfirmDelete: function() {
        this.supplierToDelete = null;
        document.getElementById('modal-purchasing-confirm-delete').style.display = 'none';
    },

    executeDeleteSupplier: async function() {
        const id = this.supplierToDelete;
        if (!id) return;
        
        setButtonLoading('btn-confirm-delete', true);
        try {
            await purchasingFetch('/api/purchasing/suppliers/' + id, 'DELETE');
            showToast('Supplier berhasil dihapus!');
            this.closeConfirmDelete();
            this.loadSuppliers(); // Reloads the supplier list
        } catch (err) {
            // Error handling is handled by purchasingFetch (e.g. 422 if PO exists)
            this.closeConfirmDelete();
        } finally {
            setButtonLoading('btn-confirm-delete', false);
        }
    },

    // ═══════════════════════════════════════════════════════════
    // PR MODAL & ACTIONS
    // ═══════════════════════════════════════════════════════════
    openPRModal: async function() {
        await this.loadProducts();
        document.getElementById('form-add-pr').reset();
        document.getElementById('pr-lines-container').innerHTML = '';
        this.addPRRow();
        document.getElementById('modal-purchasing-pr').style.display = 'flex';
    },

    closePRModal: function() {
        document.getElementById('modal-purchasing-pr').style.display = 'none';
    },

    addPRRow: function() {
        const container = document.getElementById('pr-lines-container');
        const rowId = 'prrow_' + Math.random().toString(36).substr(2, 9);
        
        let productOpts = '<option value="">-- Pilih --</option>';
        this.products.forEach(p => {
            productOpts += `<option value="${p.id}">${p.name}</option>`;
        });

        const html = `
            <div id="${rowId}" style="display: grid; grid-template-columns: 2fr 1fr auto; gap: 8px; align-items: end;">
                <div>
                    <label class="purch-label" style="font-size: 11px;">Produk</label>
                    <select class="ios-input pr-product-select" required style="padding: 6px 10px; font-size: 13px;">${productOpts}</select>
                </div>
                <div>
                    <label class="purch-label" style="font-size: 11px;">Jumlah</label>
                    <input type="number" step="0.01" class="ios-input pr-qty-input" required style="padding: 6px 10px; font-size: 13px;">
                </div>
                <button type="button" class="ios-btn ios-btn-secondary" onclick="document.getElementById('${rowId}').remove()" style="padding: 6px 10px; color: #ef4444;"><i class="fa-solid fa-trash"></i></button>
            </div>
        `;
        container.insertAdjacentHTML('beforeend', html);
    },

    submitPRForm: async function(e) {
        e.preventDefault();
        
        const title = document.getElementById('pr-title').value;
        const reason = document.getElementById('pr-reason').value;
        
        const lineRows = document.querySelectorAll('#pr-lines-container > div');
        if (lineRows.length === 0) {
            alert('Minimal harus ada 1 barang yang diajukan.');
            return;
        }

        const lines = [];
        lineRows.forEach(row => {
            const pid = row.querySelector('.pr-product-select').value;
            const qty = row.querySelector('.pr-qty-input').value;
            if (pid && qty) {
                lines.push({ product_id: pid, quantity: qty });
            }
        });

        const data = {
            title: title,
            reason: reason,
            lines: lines
        };

        setButtonLoading('btn-save-pr', true);
        try {
            await purchasingFetch('/api/purchasing/requests', 'POST', data);
            showToast('Purchase Request berhasil dibuat sebagai Draft.');
            this.closePRModal();
            this.loadPRs();
        } catch (err) {
            // Error handled by fetch
        } finally {
            setButtonLoading('btn-save-pr', false);
        }
    },

    submitPRAction: async function(id) {
        if (!confirm('Submit pengajuan ini ke Manager/Purchasing? Anda tidak dapat merubahnya lagi.')) return;
        try {
            await purchasingFetch('/api/purchasing/requests/' + id + '/submit', 'POST');
            showToast('Purchase Request berhasil disubmit.');
            this.loadPRs();
        } catch (err) {}
    },

    decidePRAction: async function(id, decision) {
        const actionStr = decision === 'approved' ? 'menyetujui' : 'menolak';
        if (!confirm('Apakah Anda yakin ingin ' + actionStr + ' pengajuan ini?')) return;
        try {
            await purchasingFetch('/api/purchasing/requests/' + id + '/decision', 'POST', { status: decision });
            showToast('Keputusan berhasil disimpan.');
            this.loadPRs();
        } catch (err) {}
    },

    // ═══════════════════════════════════════════════════════════
    // PO MODAL
    // ═══════════════════════════════════════════════════════════
    poLineCount: 0,

    openPOModal: async function() {
        await this.loadProducts();
        document.getElementById('po-modal-title').innerHTML = '<i class="fa-solid fa-cart-plus" style="color:var(--accent);"></i> Form Purchase Order (PO)';
        document.getElementById('po-id').value = '';
        document.getElementById('modal-purchasing-po').style.display = 'flex';
        document.getElementById('po-lines-container').innerHTML = '';
        document.getElementById('po-total-display').innerText = 'Rp 0';
        document.getElementById('form-po').reset();
        this.poLineCount = 0;
        this.addPOLine();
    },
    
    editPO: function(id) {
        const po = this.pos.find(p => p.id === id);
        if (!po) return;
        
        document.getElementById('po-modal-title').innerHTML = '<i class="fa-solid fa-pen" style="color:var(--accent);"></i> Edit Purchase Order (PO)';
        document.getElementById('po-id').value = po.id;
        document.getElementById('modal-purchasing-po').style.display = 'flex';
        document.getElementById('po-lines-container').innerHTML = '';
        document.getElementById('po-total-display').innerText = 'Rp 0';
        this.poLineCount = 0;
        
        const form = document.getElementById('form-po');
        form.supplier_id.value = po.supplier_id;
        if (po.expected_date) {
            form.expected_date.value = po.expected_date.substring(0, 10);
        }
        
        po.lines.forEach(line => {
            this.addPOLine();
            const lastId = this.poLineCount;
            const selectEl = document.querySelector('#po-line-' + lastId + ' .po-line-product');
            const qtyEl = document.querySelector('#po-line-' + lastId + ' .po-line-qty');
            const priceEl = document.querySelector('#po-line-' + lastId + ' .po-line-price');
            
            selectEl.value = line.product_id;
            qtyEl.value = line.ordered_quantity;
            priceEl.value = line.unit_price;
        });
        
        this.calcPOTotal();
    },
    
    closePOModal: function() {
        document.getElementById('modal-purchasing-po').style.display = 'none';
    },

    openQuickAddProduct: function() {
        document.getElementById('modal-purchasing-product').style.display = 'flex';
    },
    closeQuickAddProduct: function() {
        document.getElementById('modal-purchasing-product').style.display = 'none';
    },
    submitQuickProduct: async function(e) {
        e.preventDefault();
        const form = e.target;
        const data = {
            sku: form.sku.value.trim(),
            name: form.name.value.trim(),
            unit: form.unit.value.trim(),
            standard_cost: parseFloat(form.standard_cost.value) || 0
        };
        
        setButtonLoading('btn-save-quick-product', true);
        try {
            const product = await purchasingFetch('/api/inventory/products', 'POST', data);
            showToast('Barang berhasil ditambahkan!');
            this.closeQuickAddProduct();
            form.reset();
            
            // Reload products and refresh selects
            await this.loadProducts();
            
            // Update all product selects in the current PO form
            const selects = document.querySelectorAll('.po-line-product');
            selects.forEach(select => {
                const currentVal = select.value;
                let optionsHtml = '<option value="">-- Pilih --</option>';
                this.products.forEach(p => {
                    optionsHtml += '<option value="' + p.id + '" data-price="' + (p.purchase_price || p.standard_cost || 0) + '">' + p.name + '</option>';
                });
                select.innerHTML = optionsHtml;
                select.value = currentVal; // Restore selection
            });
            
            // Auto select newly created product in the last line if it exists
            if (this.poLineCount > 0) {
                const lastSelect = document.querySelector('#po-line-' + this.poLineCount + ' .po-line-product');
                if (!lastSelect.value) {
                    lastSelect.value = product.id;
                    const priceEl = document.querySelector('#po-line-' + this.poLineCount + ' .po-line-price');
                    priceEl.value = product.standard_cost || 0;
                    this.calcPOTotal();
                }
            }
        } catch(err) {
            // Handled
        } finally {
            setButtonLoading('btn-save-quick-product', false);
        }
    },

    addPOLine: function() {
        this.poLineCount++;
        const id = this.poLineCount;

        let productOptions = '<option value="">-- Pilih --</option>';
        this.products.forEach(function(p) {
            productOptions += '<option value="' + p.id + '" data-price="' + (p.purchase_price || 0) + '">' + p.name + '</option>';
        });

        const html = `
            <div id="po-line-${id}" style="display: flex; gap: 12px; align-items: flex-end;">
                <div style="flex: 2;">
                    <label class="purch-label" style="font-size: 11px;">Barang</label>
                    <select name="product_id[]" class="ios-input po-line-product" style="padding: 8px 12px; height: 38px; width: 100%;" onchange="purchasingApp.calcPOTotal()" required>
                        ${productOptions}
                    </select>
                </div>
                <div style="flex: 1;">
                    <label class="purch-label" style="font-size: 11px;">Qty</label>
                    <input type="number" name="quantity[]" class="ios-input po-line-qty" style="padding: 8px 12px; height: 38px; width: 100%; box-sizing: border-box;" value="1" min="0.1" step="0.1" oninput="purchasingApp.calcPOTotal()" required>
                </div>
                <div style="flex: 1.5;">
                    <label class="purch-label" style="font-size: 11px;">Harga/Unit</label>
                    <input type="number" name="unit_price[]" class="ios-input po-line-price" style="padding: 8px 12px; height: 38px; width: 100%; box-sizing: border-box;" value="0" min="0" oninput="purchasingApp.calcPOTotal()" required>
                </div>
                <div>
                    <button type="button" onclick="document.getElementById('po-line-${id}').remove(); purchasingApp.calcPOTotal();" class="ios-btn ios-btn-secondary" style="color: #ef4444; padding: 8px 12px;"><i class="fa-solid fa-trash"></i></button>
                </div>
            </div>
        `;
        document.getElementById('po-lines-container').insertAdjacentHTML('beforeend', html);

        // Auto-fill price when product is selected
        const selectEl = document.querySelector('#po-line-' + id + ' .po-line-product');
        const priceEl = document.querySelector('#po-line-' + id + ' .po-line-price');
        selectEl.addEventListener('change', function(e) {
            const opt = e.target.options[e.target.selectedIndex];
            if (opt && opt.dataset.price) {
                priceEl.value = opt.dataset.price;
                purchasingApp.calcPOTotal();
            }
        });
    },

    calcPOTotal: function() {
        let total = 0;
        const container = document.getElementById('po-lines-container');
        const lines = container.querySelectorAll('div[id^="po-line-"]');
        lines.forEach(function(line) {
            const qty = parseFloat(line.querySelector('.po-line-qty').value) || 0;
            const price = parseFloat(line.querySelector('.po-line-price').value) || 0;
            total += qty * price;
        });
        document.getElementById('po-total-display').innerText = 'Rp ' + total.toLocaleString('id-ID');
    },

    // ── Submit PO ──────────────────────────────────────────────
    submitPO: async function(e) {
        e.preventDefault();
        const form = e.target;
        const poId = form.po_id ? form.po_id.value : '';
        const supplier_id = form.supplier_id.value;
        const expected_date = form.expected_date.value;

        const productSelects = form.querySelectorAll('select[name="product_id[]"]');
        const qtyInputs = form.querySelectorAll('input[name="quantity[]"]');
        const priceInputs = form.querySelectorAll('input[name="unit_price[]"]');

        let lines = [];
        for (let i = 0; i < productSelects.length; i++) {
            if (productSelects[i].value) {
                lines.push({
                    product_id: parseInt(productSelects[i].value),
                    quantity: parseFloat(qtyInputs[i].value),
                    unit_price: parseFloat(priceInputs[i].value)
                });
            }
        }

        if (!supplier_id) {
            showToast('Pilih supplier terlebih dahulu.', 'error');
            return;
        }
        if (lines.length === 0) {
            showToast('Minimal pilih 1 barang.', 'error');
            return;
        }

        const payload = {
            supplier_id: parseInt(supplier_id),
            expected_date: expected_date,
            lines: lines
        };

        setButtonLoading('btn-save-po', true);

        try {
            let po;
            if (poId) {
                po = await purchasingFetch('/api/purchasing/orders/' + poId, 'PUT', payload);
                showToast('PO Berhasil diperbarui!');
            } else {
                po = await purchasingFetch('/api/purchasing/orders', 'POST', payload);
                // Auto-submit if created as draft
                if (po.status === 'draft') {
                    try {
                        await purchasingFetch('/api/purchasing/orders/' + po.id + '/submit', 'POST');
                    } catch(submitErr) {
                        console.warn('Auto-submit failed, PO saved as draft:', submitErr);
                    }
                }
                showToast('PO Berhasil dibuat!');
            }

            this.closePOModal();
            form.reset();
            this.loadPOs();
        } catch(err) {
            // Error already handled in purchasingFetch
        } finally {
            setButtonLoading('btn-save-po', false);
        }
    },

    // ═══════════════════════════════════════════════════════════
    // PO ACTIONS — Approve / Reject
    // ═══════════════════════════════════════════════════════════
    approvePO: async function(id) {
        if (!confirm('Setujui Purchase Order ini?')) return;
        try {
            await purchasingFetch('/api/purchasing/orders/' + id + '/decision', 'POST', { decision: 'approved' });
            showToast('PO Disetujui!');
            this.loadPOs();
        } catch(e) { /* handled */ }
    },

    rejectPO: async function(id) {
        const reason = prompt('Alasan penolakan:');
        if (reason === null) return;
        try {
            await purchasingFetch('/api/purchasing/orders/' + id + '/decision', 'POST', { decision: 'rejected', reason: reason });
            showToast('PO Ditolak!');
            this.loadPOs();
        } catch(e) { /* handled */ }
    },

    // ═══════════════════════════════════════════════════════════
    // GOODS RECEIPT MODAL
    // ═══════════════════════════════════════════════════════════
    openGRModal: function(poId) {
        const po = this.pos.find(function(p) { return p.id === poId; });
        if (!po) return;

        document.getElementById('gr-po-id').value = poId;
        const container = document.getElementById('gr-lines-container');
        let html = '';

        po.lines.forEach(function(line) {
            const productName = line.product ? line.product.name : 'Unknown';
            html += `
                <div style="display: flex; gap: 12px; align-items: center; padding: 14px 16px; border: 1px solid var(--panel-border); border-radius: 10px; background: var(--panel-secondary, rgba(0,0,0,0.01));">
                    <input type="hidden" name="gr_po_line_id[]" value="${line.id}">
                    <div style="flex: 2; font-weight: 600; color: var(--text-heading); font-size: 14px;">${productName}</div>
                    <div style="flex: 1;">
                        <label style="font-size: 11px; color: var(--text-muted); display:block; margin-bottom:2px;">Pesan (PO)</label>
                        <div style="font-size: 14px; font-weight: 600;">${line.ordered_quantity}</div>
                    </div>
                    <div style="flex: 1;">
                        <label style="font-size: 11px; color: var(--text-muted); display:block; margin-bottom:2px;">Diterima Aktual</label>
                        <input type="number" name="gr_quantity[]" class="ios-input" value="${line.ordered_quantity}" min="0" step="0.01" required style="padding: 6px 10px; height: auto;">
                    </div>
                </div>
            `;
        });
        container.innerHTML = html;
        document.getElementById('modal-purchasing-gr').style.display = 'flex';
    },

    closeGRModal: function() {
        document.getElementById('modal-purchasing-gr').style.display = 'none';
    },

    // ── Submit Goods Receipt ───────────────────────────────────
    submitGR: async function(e) {
        e.preventDefault();
        const form = e.target;
        const poId = form.purchase_order_id.value;

        const lineIds = form.querySelectorAll('input[name="gr_po_line_id[]"]');
        const qtys = form.querySelectorAll('input[name="gr_quantity[]"]');

        let lines = [];
        for (let i = 0; i < lineIds.length; i++) {
            lines.push({
                purchase_order_line_id: parseInt(lineIds[i].value),
                quantity: parseFloat(qtys[i].value)
            });
        }

        // Get default warehouse
        let warehouse_id = 1;
        try {
            const whRes = await fetch('/api/inventory', {
                headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': getCSRF() }
            });
            if (whRes.ok) {
                const whData = await whRes.json();
                if (whData && whData.warehouses && whData.warehouses.length > 0) {
                    warehouse_id = whData.warehouses[0].id;
                }
            }
        } catch(e) { /* use default */ }

        const payload = {
            purchase_order_id: parseInt(poId),
            warehouse_id: warehouse_id,
            lines: lines
        };

        setButtonLoading('btn-save-gr', true);

        try {
            await purchasingFetch('/api/purchasing/goods-receipts', 'POST', payload);
            showToast('Barang berhasil diterima & masuk stok!');
            this.closeGRModal();
            this.loadPOs();
        } catch(err) {
            // Error already handled in purchasingFetch
        } finally {
            setButtonLoading('btn-save-gr', false);
        }
    }
};

// ── Auto-init for SPA ──────────────────────────────────────────
if (typeof window.purchasingApp !== 'undefined') {
    window.purchasingApp.init();
}
