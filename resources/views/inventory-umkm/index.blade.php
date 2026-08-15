<section id="view-inventory_umkm" class="view-section">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .ios-card {
            background: var(--panel-glass);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 24px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.06);
            border: 1px solid var(--panel-glass-border);
            color: var(--text-main);
        }
        .kpi-card {
            flex: 1;
            padding: 20px;
            border-radius: 20px;
            color: white;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        .kpi-primary {
            background: linear-gradient(135deg, #0C3527 0%, #1a5944 100%);
        }
        .kpi-accent {
            background: linear-gradient(135deg, #D9EFE9 0%, #b5d8ce 100%);
            color: #0C3527;
        }
        .kpi-danger {
            background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);
        }
        .kpi-value {
            font-size: 28px;
            font-weight: 800;
            margin-top: 8px;
        }
        .kpi-label {
            font-size: 13px;
            font-weight: 600;
            opacity: 0.9;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .kpi-icon {
            position: absolute;
            right: 20px;
            bottom: -10px;
            font-size: 60px;
            opacity: 0.15;
        }
        .modern-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }
        .modern-table th {
            padding: 16px;
            color: var(--text-accent);
            font-weight: 700;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #D9EFE9;
        }
        .modern-table td {
            padding: 16px;
            background: var(--panel-secondary);
            border-top: 1px solid var(--panel-border);
            border-bottom: 1px solid var(--panel-border);
            color: var(--text-main);
        }
        .modern-table tr td:first-child {
            border-left: 1px solid var(--panel-border);
            border-top-left-radius: 12px;
            border-bottom-left-radius: 12px;
        }
        .modern-table tr td:last-child {
            border-right: 1px solid var(--panel-border);
            border-top-right-radius: 12px;
            border-bottom-right-radius: 12px;
        }
        .modern-table tr:hover td {
            background: var(--hover-bg);
            transition: background 0.3s ease;
        }
        .btn-ios {
            background: #0C3527;
            color: white;
            border-radius: 20px;
            padding: 10px 20px;
            font-weight: 600;
            border: none;
            box-shadow: 0 4px 12px rgba(12, 53, 39, 0.2);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-ios:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(12, 53, 39, 0.3);
        }
        .btn-ios-outline {
            background: transparent;
            color: var(--text-accent);
            border-radius: 20px;
            padding: 10px 20px;
            font-weight: 600;
            border: 2px solid var(--text-accent);
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-ios-outline:hover {
            background: var(--hover-bg);
        }
    </style>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <h2 style="margin: 0; font-size: 28px; color: var(--text-accent); font-weight: 800;">Manajemen Gudang</h2>
        <div style="display: flex; gap: 12px;">
            <button class="btn-ios-outline" onclick="switchInvUmkmTab('history')">
                <i class="fa-solid fa-clock-rotate-left"></i> Riwayat Input
            </button>
            <button class="btn-ios" onclick="openInvUmkmModal()">
                <i class="fa-solid fa-plus"></i> Tambah Barang
            </button>
        </div>
    </div>

    <!-- KPI Dashboard Area -->
    <div style="display: flex; gap: 24px; margin-bottom: 24px; flex-wrap: wrap;">
        <div class="kpi-card kpi-primary" style="min-width: 200px;">
            <div class="kpi-label">Total Valuasi Aset</div>
            <div class="kpi-value" id="kpi-valuation">Rp 0</div>
            <i class="fa-solid fa-sack-dollar kpi-icon"></i>
        </div>
        <div class="kpi-card kpi-accent" style="min-width: 200px;">
            <div class="kpi-label">Total Jenis Barang</div>
            <div class="kpi-value" id="kpi-items">0</div>
            <i class="fa-solid fa-boxes-stacked kpi-icon"></i>
        </div>
        <div class="kpi-card kpi-danger" style="min-width: 200px;">
            <div class="kpi-label">Stok Kritis / Habis</div>
            <div class="kpi-value" id="kpi-critical">0</div>
            <i class="fa-solid fa-triangle-exclamation kpi-icon"></i>
        </div>
    </div>

    <!-- Chart Area -->
    <div style="display: flex; gap: 24px; margin-bottom: 30px;">
        <div class="ios-card" style="flex: 1; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; min-height: 300px;">
            <h3 style="margin: 0 0 16px 0; font-size: 16px; color: var(--text-heading); width: 100%; text-align: left;">Distribusi Kategori</h3>
            <div style="width: 100%; max-width: 250px;">
                <canvas id="inventoryCategoryChart"></canvas>
            </div>
            <div id="chart-empty-state" style="display: none; color: var(--text-muted); font-size: 14px; margin-top: 20px;">Belum ada data barang.</div>
        </div>
    </div>

    <!-- Table Area -->
    <div id="inv-umkm-master-view">
        <div class="ios-card" style="overflow-x: auto; margin-bottom: 30px;">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Barang</th>
                        <th>Stok Aktual</th>
                        <th>Batas Min.</th>
                        <th>Batas Max.</th>
                        <th>Harga/Satuan</th>
                        <th>Status</th>
                        <th style="text-align: center;">Aksi</th>
                    </tr>
                </thead>
                <tbody id="inv-umkm-list">
                    <!-- Loaded via JS -->
                </tbody>
            </table>
        </div>
    </div>

    <div id="inv-umkm-history-view" style="display: none;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="margin:0; font-size: 18px; color: var(--text-heading);">Riwayat Pergerakan Stok</h3>
            <button class="btn-ios-outline" onclick="switchInvUmkmTab('master')">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Master
            </button>
        </div>
        <div class="ios-card" style="overflow-x: auto; margin-bottom: 30px;">
            <table class="modern-table">
                <thead>
                    <tr>
                        <th>Waktu</th>
                        <th>Kode/Barang</th>
                        <th>Tipe</th>
                        <th>Jumlah</th>
                        <th>Catatan</th>
                        <th>User</th>
                    </tr>
                </thead>
                <tbody id="inv-umkm-history-list">
                    <!-- History Loaded via JS -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Tambah/Edit Barang -->
    <div id="modal-inv-umkm" class="modal" style="display:none; position:fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px);">
        <div class="modal-content" style="max-width: 500px; margin: 10% auto; padding: 32px; border-radius: 24px; background: var(--panel-glass); backdrop-filter: blur(20px); border: 1px solid var(--panel-glass-border); box-shadow: 0 20px 40px rgba(0,0,0,0.15); color: var(--text-main);">
            <div style="display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid var(--panel-border); padding-bottom: 16px; margin-bottom: 24px;">
                <div>
                    <h3 id="inv-umkm-modal-title" style="margin: 0; color: var(--text-heading); font-size: 20px; font-weight: 800;">Tambah Barang</h3>
                    <p style="margin: 4px 0 0; font-size: 12px; color: var(--text-muted);">Tambahkan bahan baku dengan nama saja, lalu lengkapi harga dan gram bila sudah ada.</p>
                </div>
                <span class="close" onclick="closeInvUmkmModal()" style="color: var(--text-accent); font-size: 28px; cursor: pointer; line-height: 1; opacity: 0.7; transition: 0.2s;">&times;</span>
            </div>
            
            <form id="form-inv-umkm" onsubmit="submitInvUmkm(event)">
                <input type="hidden" id="inv-umkm-id">
                
                <style>
                    .form-control-ios {
                        width: 100%;
                        background: var(--panel-secondary);
                        border: 1px solid var(--panel-border);
                        color: var(--text-heading);
                        border-radius: 12px;
                        padding: 12px 16px;
                        font-size: 14px;
                        transition: all 0.2s;
                        box-sizing: border-box;
                    }
                    .form-control-ios:focus {
                        outline: none;
                        border-color: var(--text-accent);
                        box-shadow: 0 0 0 3px rgba(12, 53, 39, 0.1);
                    }
                    .form-label-ios {
                        display: block;
                        color: var(--text-accent);
                        font-weight: 700;
                        font-size: 12px;
                        margin-bottom: 6px;
                        text-transform: uppercase;
                        letter-spacing: 0.5px;
                    }
                </style>

                <div class="form-group" style="margin-bottom: 16px;">
                    <label class="form-label-ios">Nama Bahan/Barang *</label>
                    <input type="text" id="inv-umkm-name" required class="form-control-ios">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label class="form-label-ios">Kode (Opsional)</label>
                        <input type="text" id="inv-umkm-code" class="form-control-ios">
                    </div>
                    <div class="form-group">
                        <label class="form-label-ios">Kategori</label>
                        <input type="text" id="inv-umkm-category" class="form-control-ios">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label class="form-label-ios">Unit Kemasan</label>
                        <input type="text" id="inv-umkm-uom" placeholder="gram" class="form-control-ios">
                    </div>
                    <div class="form-group">
                        <label class="form-label-ios">Batas Min. Stok</label>
                        <input type="number" id="inv-umkm-min" value="0" class="form-control-ios">
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 24px;">
                    <div class="form-group">
                        <label class="form-label-ios">Batas Max. Stok (Ops.)</label>
                        <input type="number" id="inv-umkm-max" class="form-control-ios">
                    </div>
                    <div class="form-group">
                        <label class="form-label-ios">Stok Awal / Aktual</label>
                        <input type="number" id="inv-umkm-actual" value="0" step="0.01" class="form-control-ios">
                    </div>
                </div>

                <h4 style="color: var(--text-heading); margin: 0 0 16px 0; font-size: 16px; font-weight: 800; border-top: 1px solid var(--panel-border); padding-top: 24px;">Pengaturan Harga Modal</h4>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div class="form-group">
                        <label class="form-label-ios">Total Harga Beli (Rp)</label>
                        <input type="number" id="inv-umkm-price" oninput="calculatePricePerGram()" class="form-control-ios">
                    </div>
                    <div class="form-group">
                        <label class="form-label-ios">Total Gram (Isi)</label>
                        <input type="number" id="inv-umkm-gram" oninput="calculatePricePerGram()" class="form-control-ios">
                    </div>
                </div>

                <div style="background: var(--hover-bg); padding: 16px; border-radius: 16px; text-align: center; margin-bottom: 32px; border: 1px solid var(--panel-border);">
                    <span style="color: var(--text-main); font-weight: 600; font-size: 13px;">Harga Satuan Terhitung: </span>
                    <span id="inv-umkm-calculated-price" style="color: var(--text-heading); font-weight: 800; font-size: 16px; margin-left: 8px;">Rp 0 / gram</span>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" class="btn-ios-outline" onclick="closeInvUmkmModal()">Batal</button>
                    <button type="submit" class="btn-ios" id="inv-umkm-submit-btn">Simpan Barang</button>
                </div>
            </form>
        </div>
    </div>
</section>

<script>
    let isSubmittingInv = false;
    let myInventoryChart = null;

    function formatCurrencyUmkm(amount) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(amount);
    }

    function calculatePricePerGram() {
        const price = parseFloat(document.getElementById('inv-umkm-price').value) || 0;
        const gram = parseFloat(document.getElementById('inv-umkm-gram').value) || 0;
        let calculated = 0;
        if (gram > 0) {
            calculated = price / gram;
        }
        document.getElementById('inv-umkm-calculated-price').innerText = `Rp ${calculated.toFixed(2)} / gram`;
        return calculated;
    }

    async function loadInvUmkm() {
        try {
            const res = await fetch('/master-demo/inventory-umkm');
            const data = await res.json();
            
            const tbody = document.getElementById('inv-umkm-list');
            tbody.innerHTML = '';
            
            let warningCount = 0;
            let totalValuation = 0;
            let categories = {};

            data.forEach(item => {
                const actualStock = parseFloat(item.actual_stock) || 0;
                const minStock = parseFloat(item.min_stock) || 0;
                const maxStock = item.max_stock ? parseFloat(item.max_stock) : '-';
                const price = parseFloat(item.price_per_gram) || 0;
                
                const isWarning = actualStock < minStock;
                if (isWarning) warningCount++;
                
                totalValuation += (actualStock * price);
                
                const cat = item.category || 'Lain-lain';
                categories[cat] = (categories[cat] || 0) + 1;

                const statusBadge = isWarning 
                    ? `<span style="background: rgba(231, 76, 60, 0.1); color: #e74c3c; padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">⚠️ Kritis</span>`
                    : `<span style="background: rgba(217, 239, 233, 0.5); color: var(--text-accent); padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: bold;">Aman</span>`;

                tbody.innerHTML += `
                    <tr>
                        <td style="color: var(--text-muted);">${item.item_code || '-'}</td>
                        <td style="color: var(--text-heading); font-weight: 700;">${item.item_name}</td>
                        <td style="color: ${isWarning ? 'var(--danger)' : 'var(--text-main)'}; font-weight: bold;">${actualStock} ${item.uom || ''}</td>
                        <td style="color: var(--text-muted);">${minStock} ${item.uom || ''}</td>
                        <td style="color: var(--text-muted);">${maxStock !== '-' ? maxStock + ' ' + (item.uom || '') : '-'}</td>
                        <td style="color: var(--text-heading); font-weight: 600;">${formatCurrencyUmkm(price)}</td>
                        <td>${statusBadge}</td>
                        <td style="text-align: center;">
                            <button onclick='editInvUmkm(${JSON.stringify(item).replace(/'/g, "&#39;")})' style="background: none; border: 1px solid var(--panel-border); color: var(--text-main); border-radius: 8px; padding: 6px 10px; cursor: pointer; margin-right: 4px; transition: all 0.2s;"><i class="fa-solid fa-pen"></i></button>
                            <button onclick="deleteInvUmkm(${item.id})" style="background: rgba(231,76,60,0.1); color: var(--danger); border: none; border-radius: 8px; padding: 6px 10px; cursor: pointer; transition: all 0.2s;"><i class="fa-solid fa-trash"></i></button>
                        </td>
                    </tr>
                `;
            });

            // Update KPI Cards
            document.getElementById('kpi-valuation').innerText = formatCurrencyUmkm(totalValuation);
            document.getElementById('kpi-items').innerText = data.length;
            document.getElementById('kpi-critical').innerText = warningCount;

            // Render Chart
            renderCategoryChart(categories, data.length);

        } catch (e) {
            console.error('Failed to load inventory', e);
        }
    }

    function renderCategoryChart(categories, totalItems) {
        if (totalItems === 0) {
            document.getElementById('chart-empty-state').style.display = 'block';
            document.getElementById('inventoryCategoryChart').style.display = 'none';
            return;
        }
        
        document.getElementById('chart-empty-state').style.display = 'none';
        document.getElementById('inventoryCategoryChart').style.display = 'block';
        
        const ctx = document.getElementById('inventoryCategoryChart').getContext('2d');
        if(myInventoryChart) myInventoryChart.destroy();
        
        const labels = Object.keys(categories);
        const data = Object.values(categories);
        
        const colors = ['#0C3527', '#1a5944', '#D9EFE9', '#85c3b0', '#f1c40f', '#e67e22', '#e74c3c'];

        myInventoryChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.slice(0, labels.length),
                    borderWidth: 0,
                    hoverOffset: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true,
                            font: { family: "'Inter', sans-serif", size: 12 }
                        }
                    }
                }
            }
        });
    }

    function openInvUmkmModal() {
        document.getElementById('form-inv-umkm').reset();
        document.getElementById('inv-umkm-id').value = '';
        document.getElementById('inv-umkm-modal-title').innerText = 'Tambah Barang';
        document.getElementById('inv-umkm-submit-btn').innerText = 'Tambah Barang';
        document.getElementById('inv-umkm-calculated-price').innerText = 'Rp 0 / gram';
        document.getElementById('modal-inv-umkm').style.display = 'block';
    }

    function closeInvUmkmModal() {
        document.getElementById('modal-inv-umkm').style.display = 'none';
    }

    function editInvUmkm(item) {
        document.getElementById('inv-umkm-id').value = item.id;
        document.getElementById('inv-umkm-name').value = item.item_name;
        document.getElementById('inv-umkm-code').value = item.item_code || '';
        document.getElementById('inv-umkm-category').value = item.category || '';
        document.getElementById('inv-umkm-uom').value = item.uom || '';
        document.getElementById('inv-umkm-min').value = item.min_stock || 0;
        document.getElementById('inv-umkm-max').value = item.max_stock || '';
        document.getElementById('inv-umkm-actual').value = item.actual_stock || 0;
        document.getElementById('inv-umkm-price').value = item.total_price || 0;
        document.getElementById('inv-umkm-gram').value = item.total_gram || 0;
        
        document.getElementById('inv-umkm-modal-title').innerText = 'Edit Master Barang';
        document.getElementById('inv-umkm-submit-btn').innerText = 'Update Barang';
        calculatePricePerGram();
        
        document.getElementById('modal-inv-umkm').style.display = 'block';
    }

    async function submitInvUmkm(e) {
        e.preventDefault();
        
        if (isSubmittingInv) return;
        isSubmittingInv = true;
        const btn = document.getElementById('inv-umkm-submit-btn');
        const originalText = btn.innerText;
        btn.innerText = 'Menyimpan...';
        btn.disabled = true;

        const id = document.getElementById('inv-umkm-id').value;
        const pricePerGram = calculatePricePerGram();

        const payload = {
            item_name: document.getElementById('inv-umkm-name').value,
            item_code: document.getElementById('inv-umkm-code').value || null,
            category: document.getElementById('inv-umkm-category').value || null,
            uom: document.getElementById('inv-umkm-uom').value || null,
            min_stock: document.getElementById('inv-umkm-min').value || 0,
            max_stock: document.getElementById('inv-umkm-max').value || null,
            actual_stock: document.getElementById('inv-umkm-actual').value || 0,
            total_price: document.getElementById('inv-umkm-price').value || 0,
            total_gram: document.getElementById('inv-umkm-gram').value || 0,
            price_per_gram: pricePerGram
        };

        const method = id ? 'PUT' : 'POST';
        const url = id ? '/master-demo/inventory-umkm/' + id : '/master-demo/inventory-umkm';

        try {
            const res = await fetch(url, {
                method: method,
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(payload)
            });

            if (res.ok) {
                closeInvUmkmModal();
                await loadInvUmkm();
                if (typeof showToast === 'function') showToast('Barang berhasil disimpan!');
            } else {
                if (window.showCustomAlert) {
                    window.showCustomAlert('Gagal menyimpan. Kode item mungkin duplikat.');
                } else {
                    alert('Gagal menyimpan. Kode item mungkin duplikat.');
                }
            }
        } catch (error) {
            console.error('Error saving item:', error);
            alert('Terjadi kesalahan.');
        } finally {
            isSubmittingInv = false;
            btn.innerText = originalText;
            btn.disabled = false;
        }
    }

    function deleteInvUmkm(id) {
        if (window.showCustomConfirm) {
            window.showCustomConfirm('Yakin ingin menghapus barang ini?', async () => {
                await performDeleteInvUmkm(id);
            });
        } else {
            if (confirm('Yakin ingin menghapus barang ini?')) {
                performDeleteInvUmkm(id);
            }
        }
    }

    async function performDeleteInvUmkm(id) {
        try {
            const res = await fetch('/master-demo/inventory-umkm/' + id, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            if (res.ok) {
                loadInvUmkm();
                if (typeof showToast === 'function') showToast('Barang dihapus!');
            }
        } catch(e) {
            console.error(e);
        }
    }

    function switchInvUmkmTab(tab) {
        if (tab === 'history') {
            document.getElementById('inv-umkm-master-view').style.display = 'none';
            document.getElementById('inv-umkm-history-view').style.display = 'block';
            loadInvUmkmHistory();
        } else {
            document.getElementById('inv-umkm-master-view').style.display = 'block';
            document.getElementById('inv-umkm-history-view').style.display = 'none';
        }
    }

    async function loadInvUmkmHistory() {
        try {
            const res = await fetch('/master-demo/inventory-umkm/history');
            const data = await res.json();
            
            const tbody = document.getElementById('inv-umkm-history-list');
            tbody.innerHTML = '';
            
            data.forEach(item => {
                const isOut = parseFloat(item.quantity) < 0;
                const qtyColor = isOut ? 'var(--danger)' : 'var(--success)';
                const qtyIcon = isOut ? '<i class="fa-solid fa-minus"></i>' : '<i class="fa-solid fa-plus"></i>';
                const date = new Date(item.created_at).toLocaleString('id-ID');
                
                tbody.innerHTML += `
                    <tr>
                        <td style="color: var(--text-muted); font-size: 12px;">${date}</td>
                        <td style="color: var(--text-heading); font-weight: 700;">
                            ${item.product ? item.product.item_code : '-'} <br>
                            <span style="font-size: 12px; font-weight:normal; color: var(--text-muted);">${item.product ? item.product.item_name : '-'}</span>
                        </td>
                        <td style="color: var(--text-main);">${item.type}</td>
                        <td style="color: ${qtyColor}; font-weight: bold;">
                            ${qtyIcon} ${Math.abs(item.quantity)} ${item.product ? (item.product.uom || '') : ''}
                        </td>
                        <td style="color: var(--text-muted); max-width: 200px;">${item.notes || '-'}</td>
                        <td style="color: var(--text-main);">${item.created_by ? item.created_by.name : (item.user ? item.user.name : '-')}</td>
                    </tr>
                `;
            });
        } catch (e) {
            console.error('Failed to load history', e);
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const orgSwitchView = window.switchView;
        window.switchView = function(viewId) {
            if (orgSwitchView) orgSwitchView(viewId);
            if (viewId === 'inventory_umkm') {
                loadInvUmkm();
            }
        }
        
        if (localStorage.getItem('activeView') === 'inventory_umkm') {
            loadInvUmkm();
        }
    });
</script>
