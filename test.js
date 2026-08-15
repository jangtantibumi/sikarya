
    window.purchasingApp = {
        products: [],
        suppliers: [],
        pos: [],
        currentTab: 'draft',
        
        init: async function() {
            this.loadProducts();
            this.loadSuppliers();
            this.loadPOs();
        },
        
        loadProducts: async function() {
            try {
                const res = await fetch('/api/inventory');
                if(res.ok) {
                    const data = await res.json();
                    this.products = data.products.data || [];
                }
            } catch(e) { console.error(e); }
        },
        
        loadSuppliers: async function() {
            try {
                const res = await fetch('/api/purchasing/suppliers');
                if(res.ok) {
                    this.suppliers = await res.json();
                    this.renderSupplierSelect();
                    this.renderSupplierList();
                }
            } catch(e) { console.error(e); }
        },
        
        loadPOs: async function() {
            const container = document.getElementById('purchasing-po-container');
            container.innerHTML = '<div class="loader" style="margin: 40px auto; border-top-color: var(--accent);"></div>';
            
            try {
                const res = await fetch('/api/purchasing/orders');
                if(res.ok) {
                    this.pos = await res.json();
                    this.renderPOs();
                }
            } catch(e) { 
                container.innerHTML = '<div style="color:red; text-align:center;">Gagal memuat data PO.</div>';
            }
        },
        
        switchTab: function(tabName) {
            this.currentTab = tabName;
            
            document.querySelectorAll('.ios-tab').forEach(btn => {
                btn.style.borderBottom = 'none';
                btn.style.color = 'var(--text-muted)';
                btn.style.fontWeight = '500';
            });
            const activeBtn = document.querySelector(\`.ios-tab[data-tab="\${tabName}"]\`);
            if(activeBtn) {
                activeBtn.style.borderBottom = '2px solid var(--accent)';
                activeBtn.style.color = 'var(--accent)';
                activeBtn.style.fontWeight = '600';
            }
            
            this.renderPOs();
        },
        
        renderPOs: function() {
            const container = document.getElementById('purchasing-po-container');
            let filteredPOs = [];
            
            if (this.currentTab === 'draft') {
                filteredPOs = this.pos.filter(p => p.status === 'draft' || p.status === 'submitted');
            } else if (this.currentTab === 'approved') {
                filteredPOs = this.pos.filter(p => p.status === 'approved' || p.status === 'partial');
            } else {
                filteredPOs = this.pos.filter(p => p.status === 'completed' || p.status === 'rejected');
            }
            
            if (filteredPOs.length === 0) {
                container.innerHTML = \`
                    <div style="text-align: center; padding: 48px; background: rgba(0,0,0,0.02); border: 1px dashed var(--panel-border); border-radius: 12px; color: var(--text-muted);">
                        <i class="fa-solid fa-folder-open" style="font-size: 32px; margin-bottom: 12px; opacity: 0.5;"></i><br>
                        Tidak ada Purchase Order di kategori ini.
                    </div>
                \`;
                return;
            }
            
            let html = '';
            filteredPOs.forEach(po => {
                const supplierName = po.supplier ? po.supplier.name : 'Unknown Supplier';
                const date = new Date(po.created_at).toLocaleDateString('id-ID', {day:'numeric', month:'short', year:'numeric'});
                const isCeo = true;
                
                let actionBtn = '';
                if (po.status === 'submitted' && isCeo) {
                    actionBtn = \`
                        <button onclick="purchasingApp.approvePO(\${po.id})" class="ios-btn ios-btn-primary" style="padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-check"></i> Setujui</button>
                        <button onclick="purchasingApp.rejectPO(\${po.id})" class="ios-btn ios-btn-secondary" style="padding: 6px 12px; font-size: 12px; color: #ef4444;"><i class="fa-solid fa-xmark"></i> Tolak</button>
                    \`;
                } else if (po.status === 'approved') {
                    actionBtn = \`<button onclick="purchasingApp.openGRModal(\${po.id})" class="ios-btn ios-btn-primary" style="background: #10b981; border-color: #10b981; padding: 6px 12px; font-size: 12px;"><i class="fa-solid fa-box-open"></i> Terima Barang</button>\`;
                }
                
                let statusBadge = \`<span class="suba-badge-native" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">Menunggu Persetujuan</span>\`;
                if(po.status === 'approved') statusBadge = \`<span class="suba-badge-native" style="background: rgba(59, 130, 246, 0.1); color: #3b82f6;">Sedang Dipesan</span>\`;
                if(po.status === 'completed') statusBadge = \`<span class="suba-badge-native" style="background: rgba(16, 185, 129, 0.1); color: #10b981;">Selesai (Diterima)</span>\`;
                if(po.status === 'rejected') statusBadge = \`<span class="suba-badge-native" style="background: rgba(239, 68, 68, 0.1); color: #ef4444;">Ditolak</span>\`;
                
                html += \`
                    <div style="background: var(--panel); border: 1px solid var(--panel-border); border-radius: 12px; padding: 20px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span style="font-weight: 700; color: var(--text-heading); font-size: 16px;">\${po.number}</span>
                                \${statusBadge}
                            </div>
                            <div style="font-size: 14px; color: var(--text-muted); display: flex; gap: 16px;">
                                <span><i class="fa-solid fa-store" style="margin-right: 4px;"></i> \${supplierName}</span>
                                <span><i class="fa-regular fa-calendar" style="margin-right: 4px;"></i> \${date}</span>
                                <span style="font-weight: 600; color: var(--text-heading);">Rp \${Number(po.total_amount).toLocaleString('id-ID')}</span>
                            </div>
                            <div style="font-size: 13px; color: var(--text-muted); margin-top: 4px;">
                                Items: \${po.lines ? po.lines.map(l => (l.product ? l.product.name : 'Item') + ' (' + l.ordered_quantity + ')').join(', ') : '-'}
                            </div>
                        </div>
                        <div style="display: flex; gap: 8px;">
                            \${actionBtn}
                        </div>
                    </div>
                \`;
            });
            container.innerHTML = html;
        },
        
        openSupplierModal: function() {
            document.getElementById('modal-purchasing-supplier').style.display = 'flex';
        },
        closeSupplierModal: function() {
            document.getElementById('modal-purchasing-supplier').style.display = 'none';
        },
        renderSupplierSelect: function() {
            const select = document.getElementById('po-supplier-select');
            if(!select) return;
            select.innerHTML = '<option value="">-- Pilih Supplier --</option>';
            this.suppliers.forEach(s => {
                select.innerHTML += \`<option value="\${s.id}">\${s.name} (\${s.phone || '-'})</option>\`;
            });
        },
        renderSupplierList: function() {
            const container = document.getElementById('supplier-list-container');
            if(!container) return;
            if(this.suppliers.length === 0) {
                container.innerHTML = '<div style="color:var(--text-muted); font-size:13px;">Belum ada supplier terdaftar.</div>';
                return;
            }
            let html = '<table style="width:100%; border-collapse: collapse; font-size: 13px;">';
            this.suppliers.forEach(s => {
                html += \`<tr style="border-bottom:1px solid var(--panel-border);">
                    <td style="padding: 8px 4px; font-weight:600;">\${s.name}</td>
                    <td style="padding: 8px 4px; color:var(--text-muted);">\${s.phone || '-'}</td>
                </tr>\`;
            });
            html += '</table>';
            container.innerHTML = html;
        },
        submitSupplier: async function(e) {
            e.preventDefault();
            const form = e.target;
            const data = {
                name: form.name.value,
                phone: form.phone.value
            };
            
            try {
                const res = await fetch('/api/purchasing/suppliers', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(data)
                });
                if(res.ok) {
                    if (typeof showToast === 'function') showToast('Supplier berhasil ditambahkan!');
                    form.reset();
                    this.loadSuppliers();
                } else {
                    alert('Gagal menambah supplier');
                }
            } catch(e) { console.error(e); }
        },
        
        poLineCount: 0,
        openPOModal: function() {
            document.getElementById('modal-purchasing-po').style.display = 'flex';
            document.getElementById('po-lines-container').innerHTML = '';
            document.getElementById('po-total-display').innerText = 'Rp 0';
            this.addPOLine();
        },
        closePOModal: function() {
            document.getElementById('modal-purchasing-po').style.display = 'none';
        },
        addPOLine: function() {
            this.poLineCount++;
            const id = this.poLineCount;
            
            let productOptions = '<option value="">-- Pilih --</option>';
            this.products.forEach(p => {
                productOptions += \`<option value="\${p.id}" data-price="\${p.purchase_price || 0}">\${p.name}</option>\`;
            });
            
            const html = \`
                <div id="po-line-\${id}" style="display: flex; gap: 12px; align-items: flex-end;">
                    <div style="flex: 2;">
                        <label class="ios-label" style="font-size: 11px;">Barang</label>
                        <select name="product_id[]" class="ios-input po-line-product" onchange="purchasingApp.calcPOTotal()" required>
                            \${productOptions}
                        </select>
                    </div>
                    <div style="flex: 1;">
                        <label class="ios-label" style="font-size: 11px;">Qty</label>
                        <input type="number" name="quantity[]" class="ios-input po-line-qty" value="1" min="0.1" step="0.1" oninput="purchasingApp.calcPOTotal()" required>
                    </div>
                    <div style="flex: 1.5;">
                        <label class="ios-label" style="font-size: 11px;">Harga/Unit</label>
                        <input type="number" name="unit_price[]" class="ios-input po-line-price" value="0" min="0" oninput="purchasingApp.calcPOTotal()" required>
                    </div>
                    <div>
                        <button type="button" onclick="document.getElementById('po-line-\${id}').remove(); purchasingApp.calcPOTotal();" class="ios-btn ios-btn-secondary" style="color: #ef4444; padding: 8px 12px;"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </div>
            \`;
            document.getElementById('po-lines-container').insertAdjacentHTML('beforeend', html);
            
            const selectEl = document.querySelector(\`#po-line-\${id} .po-line-product\`);
            const priceEl = document.querySelector(\`#po-line-\${id} .po-line-price\`);
            selectEl.addEventListener('change', (e) => {
                const opt = e.target.options[e.target.selectedIndex];
                if(opt && opt.dataset.price) {
                    priceEl.value = opt.dataset.price;
                    purchasingApp.calcPOTotal();
                }
            });
        },
        calcPOTotal: function() {
            let total = 0;
            const container = document.getElementById('po-lines-container');
            const lines = container.querySelectorAll('div[id^="po-line-"]');
            lines.forEach(line => {
                const qty = parseFloat(line.querySelector('.po-line-qty').value) || 0;
                const price = parseFloat(line.querySelector('.po-line-price').value) || 0;
                total += qty * price;
            });
            document.getElementById('po-total-display').innerText = 'Rp ' + total.toLocaleString('id-ID');
        },
        submitPO: async function(e) {
            e.preventDefault();
            const form = e.target;
            const supplier_id = form.supplier_id.value;
            const expected_date = form.expected_date.value;
            
            const productSelects = form.querySelectorAll('select[name="product_id[]"]');
            const qtyInputs = form.querySelectorAll('input[name="quantity[]"]');
            const priceInputs = form.querySelectorAll('input[name="unit_price[]"]');
            
            let lines = [];
            for(let i = 0; i < productSelects.length; i++) {
                if(productSelects[i].value) {
                    lines.push({
                        product_id: parseInt(productSelects[i].value),
                        quantity: parseFloat(qtyInputs[i].value),
                        unit_price: parseFloat(priceInputs[i].value)
                    });
                }
            }
            
            if(lines.length === 0) {
                alert('Minimal pilih 1 barang.'); return;
            }
            
            const payload = {
                supplier_id: parseInt(supplier_id),
                expected_date: expected_date,
                lines: lines
            };
            
            try {
                const res = await fetch('/api/purchasing/orders', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(payload)
                });
                
                if(res.ok) {
                    const po = await res.json();
                    if(po.status === 'draft') {
                        await fetch(\`/api/purchasing/orders/\${po.id}/submit\`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                        });
                    }
                    
                    if (typeof showToast === 'function') showToast('PO Berhasil dibuat!');
                    this.closePOModal();
                    form.reset();
                    this.loadPOs();
                } else {
                    alert('Gagal membuat PO. Cek inputan.');
                }
            } catch(err) {
                console.error(err);
                alert('Error sistem saat buat PO.');
            }
        },
        
        approvePO: async function(id) {
            if(!confirm('Setujui Purchase Order ini?')) return;
            try {
                const res = await fetch(\`/api/purchasing/orders/\${id}/decision\`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ decision: 'approved' })
                });
                if(res.ok) {
                    if (typeof showToast === 'function') showToast('PO Disetujui!');
                    this.loadPOs();
                } else {
                    const data = await res.json();
                    alert('Gagal menyetujui: ' + (data.message || 'Error'));
                }
            } catch(e) { console.error(e); }
        },
        rejectPO: async function(id) {
            const reason = prompt('Alasan penolakan:');
            if(reason === null) return;
            try {
                const res = await fetch(\`/api/purchasing/orders/\${id}/decision\`, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify({ decision: 'rejected', reason: reason })
                });
                if(res.ok) {
                    if (typeof showToast === 'function') showToast('PO Ditolak!');
                    this.loadPOs();
                }
            } catch(e) { console.error(e); }
        },
        
        openGRModal: function(poId) {
            const po = this.pos.find(p => p.id === poId);
            if(!po) return;
            
            document.getElementById('gr-po-id').value = poId;
            const container = document.getElementById('gr-lines-container');
            let html = '';
            
            po.lines.forEach(line => {
                const productName = line.product ? line.product.name : 'Unknown';
                html += \`
                    <div style="display: flex; gap: 12px; align-items: center; padding: 12px; border: 1px solid var(--panel-border); border-radius: 8px; background: rgba(0,0,0,0.01);">
                        <input type="hidden" name="gr_po_line_id[]" value="\${line.id}">
                        <div style="flex: 2; font-weight: 600; color: var(--text-heading); font-size: 14px;">\${productName}</div>
                        <div style="flex: 1;">
                            <label style="font-size: 11px; color: var(--text-muted);">Pesan (PO)</label>
                            <div style="font-size: 14px; font-weight: 600;">\${line.ordered_quantity}</div>
                        </div>
                        <div style="flex: 1;">
                            <label style="font-size: 11px; color: var(--text-muted);">Diterima Aktual</label>
                            <input type="number" name="gr_quantity[]" class="ios-input" value="\${line.ordered_quantity}" min="0" step="0.01" required style="padding: 4px 8px; height: auto;">
                        </div>
                    </div>
                \`;
            });
            container.innerHTML = html;
            document.getElementById('modal-purchasing-gr').style.display = 'flex';
        },
        closeGRModal: function() {
            document.getElementById('modal-purchasing-gr').style.display = 'none';
        },
        submitGR: async function(e) {
            e.preventDefault();
            const form = e.target;
            const poId = form.purchase_order_id.value;
            
            const lineIds = form.querySelectorAll('input[name="gr_po_line_id[]"]');
            const qtys = form.querySelectorAll('input[name="gr_quantity[]"]');
            
            let lines = [];
            for(let i=0; i<lineIds.length; i++) {
                lines.push({
                    purchase_order_line_id: parseInt(lineIds[i].value),
                    quantity: parseFloat(qtys[i].value)
                });
            }
            
            let warehouse_id = 1;
            try {
                const whRes = await fetch('/api/inventory');
                if(whRes.ok) {
                    const whData = await whRes.json();
                    if(whData && whData.warehouses && whData.warehouses.length > 0) warehouse_id = whData.warehouses[0].id;
                }
            } catch(e) {}
            
            const payload = {
                purchase_order_id: parseInt(poId),
                warehouse_id: warehouse_id,
                lines: lines
            };
            
            try {
                const res = await fetch('/api/purchasing/goods-receipts', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    body: JSON.stringify(payload)
                });
                
                if(res.ok) {
                    if (typeof showToast === 'function') showToast('Barang berhasil diterima & masuk stok!');
                    this.closeGRModal();
                    this.loadPOs(); 
                } else {
                    const data = await res.json();
                    alert('Gagal menerima barang: ' + (data.message || 'Cek kelengkapan gudang.'));
                }
            } catch(e) {
                console.error(e);
                alert('Terjadi kesalahan sistem saat Goods Receipt.');
            }
        }
    };
    
    // Auto-init for SPA
    if (typeof window.purchasingApp !== 'undefined') {
        window.purchasingApp.init();
    }

