
        (function() {
            var theme = localStorage.getItem('sikarya_theme') || 'light';
            document.documentElement.setAttribute('data-theme', theme);
            var color = localStorage.getItem('sikarya_color');
            if(color) {
                document.documentElement.style.setProperty('--accent', color);
                document.documentElement.style.setProperty('--accent-hover', color);
                document.documentElement.style.setProperty('--accent-active', color);
            }
        })();
    

                    document.addEventListener('DOMContentLoaded', function() {
                        const announcementId = '{{ $latestAnnouncement->id }}';
                        const dismissed = localStorage.getItem('announcement_dismissed_' + announcementId);
                        if (!dismissed) {
                            setTimeout(() => {
                                document.getElementById('modal-announcement-popup').style.display = 'flex';
                            }, 500);
                        }
                    });
                    function dismissAnnouncementPopup(id) {
                        localStorage.setItem('announcement_dismissed_' + id, 'true');
                        document.getElementById('modal-announcement-popup').style.display = 'none';
                    }
                

            async function deleteAnnouncement(id) {
                if(!confirm('Apakah Anda yakin ingin menghapus pengumuman ini?')) return;
                try {
                    const res = await fetch('/master-demo/announcements/' + id, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
                    });
                    if(res.ok) {
                        alert('Pengumuman berhasil dihapus!');
                        location.reload();
                    } else {
                        alert('Gagal menghapus pengumuman.');
                    }
                } catch(e) { console.error(e); }
            }

            async function bulkDeleteAnnouncements(period) {
                let pStr = period === 'daily' ? '1 Hari' : (period === 'weekly' ? '1 Minggu' : '1 Bulan');
                if(!confirm('Anda akan menghapus SEMUA pengumuman yang lebih lama dari ' + pStr + '. Tindakan ini tidak bisa dibatalkan. Lanjutkan?')) return;
                try {
                    const res = await fetch('/master-demo/announcements/bulk-delete', {
                        method: 'POST',
                        headers: { 
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') 
                        },
                        body: JSON.stringify({ period: period })
                    });
                    if(res.ok) {
                        const data = await res.json();
                        alert(data.message);
                        location.reload();
                    } else {
                        alert('Gagal menghapus pengumuman.');
                    }
                } catch(e) { console.error(e); }
            }
        

                let currentAuditRange = 'today';
                let currentAuditPage = 1;

                function setAuditFilter(range, btn) {
                    currentAuditRange = range;
                    document.querySelectorAll('.audit-filter-btn').forEach(el => {
                        el.style.background = 'transparent';
                        el.style.boxShadow = 'none';
                        el.style.color = 'var(--text-muted)';
                        el.style.fontWeight = '500';
                        el.classList.remove('active');
                    });
                    
                    if (btn) {
                        btn.style.background = 'var(--primary)';
                        btn.style.boxShadow = '0 3px 8px rgba(0,0,0,0.2)';
                        btn.style.color = 'white';
                        btn.style.fontWeight = '700';
                        btn.classList.add('active');
                    }
                    
                    if(range === 'custom') {
                        document.getElementById('audit-custom-range').style.display = 'flex';
                    } else {
                        document.getElementById('audit-custom-range').style.display = 'none';
                    }
                    
                    currentAuditPage = 1;
                    loadAuditLogs();
                }

                async function loadAuditLogs() {
                    const container = document.getElementById('audit-log-container');
                    container.innerHTML = '<div style="text-align: center; padding: 40px 0;"><div class="loader" style="margin: 0 auto;"></div><div style="margin-top: 12px; color: var(--text-muted); font-size: 14px;">Memuat data...</div></div>';
                    
                    const date = document.getElementById('audit-date').value;
                    const timeStart = document.getElementById('audit-time-start').value;
                    const timeEnd = document.getElementById('audit-time-end').value;
                    const keyword = document.getElementById('audit-search').value;
                    const sort = document.getElementById('audit-sort').value;
                    
                    let url = '/master-demo/security/audit-logs?range=' + currentAuditRange + '&page=' + currentAuditPage;
                    if(date) url += '&date=' + date;
                    if(timeStart) url += '&time_start=' + timeStart;
                    if(timeEnd) url += '&time_end=' + timeEnd;
                    if(keyword) url += '&keyword=' + encodeURIComponent(keyword);
                    if(sort) url += '&sort=' + sort;

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').content;
                        // Avoid global loader interception by using XMLHttpRequest header explicitly
                        const response = await fetch(url, {
                            headers: { 
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });
                        
                        if (!response.ok) throw new Error('Network response was not ok');
                        const data = await response.json();
                        
                        renderAuditLogs(data.data);
                        renderPagination(data);
                    } catch(e) {
                        console.error('Audit Log Error:', e);
                        container.innerHTML = '<div style="text-align:center; padding:32px; color:var(--danger);"><i class="fa-solid fa-triangle-exclamation" style="font-size:24px; margin-bottom:12px;"></i><br>Gagal memuat log audit.</div>';
                    }
                }

                function renderAuditLogs(logs) {
                    const container = document.getElementById('audit-log-container');
                    if(!logs || logs.length === 0) {
                        container.innerHTML = '<div style="text-align:center; padding:40px; color:var(--text-muted);"><i class="fa-solid fa-clock-rotate-left" style="font-size:32px; margin-bottom:16px; opacity:0.5;"></i><br>Tidak ada riwayat aktivitas pada rentang waktu ini.</div>';
                        return;
                    }

                    let html = '<div style="display: flex; flex-direction: column;">';
                    
                    logs.forEach(log => {
                        let icon = log.type === 'rbac' ? '<i class="fa-solid fa-shield-halved" style="color:var(--primary);"></i>' : '<i class="fa-solid fa-server" style="color:var(--success);"></i>';
                        
                        let detailsStr = '';
                        if (log.details && typeof log.details === 'object') {
                            detailsStr = Object.entries(log.details).map(([k, v]) => `<span style="display:inline-block; margin-right:8px; margin-bottom:4px; padding:2px 6px; background:rgba(128,128,128,0.1); border-radius:4px;"><b>${k}:</b> ${v}</span>`).join('');
                        } else if (log.details) {
                            detailsStr = `<span style="padding:2px 6px; background:rgba(128,128,128,0.1); border-radius:4px;">${log.details}</span>`;
                        }

                        let avatar = log.actor_avatar ? `<img src="${log.actor_avatar}" style="width:24px; height:24px; border-radius:50%; object-fit:cover;">` : `<div style="width:24px; height:24px; border-radius:50%; background:var(--panel-border); display:flex; align-items:center; justify-content:center; font-size:10px;"><i class="fa-solid fa-user"></i></div>`;

                        html += `
                        <div style="display: grid; grid-template-columns: 200px 150px 150px 200px minmax(200px, 1fr) 120px; gap: 16px; padding: 16px; border-bottom: 1px solid var(--panel-border); font-size: 13px; align-items: start; transition: background 0.2s;" onmouseover="this.style.background='rgba(128,128,128,0.05)'" onmouseout="this.style.background='transparent'">
                            <div style="color: var(--text-muted);">${log.created_at}</div>
                            <div style="display: flex; gap: 8px; align-items: center; font-weight: 500;">
                                ${avatar}
                                <span>${log.actor}</span>
                            </div>
                            <div style="display:flex; align-items:center; gap:6px;">
                                ${icon} ${log.target}
                            </div>
                            <div>
                                <span style="display: inline-block; padding: 4px 10px; background: var(--panel-border); border-radius: 20px; font-size: 11px; font-weight: 600;">${log.action_label}</span>
                            </div>
                            <div style="font-size: 11px; color: var(--text-muted); line-height: 1.6;">${detailsStr}</div>
                            <div style="font-family: monospace; font-size: 11px; color: var(--text-muted);">${log.ip_address || '-'}</div>
                        </div>`;
                    });
                    
                    html += '</div>';
                    container.innerHTML = html;
                }

                function renderPagination(data) {
                    const info = document.getElementById('audit-page-info');
                    const controls = document.getElementById('audit-page-controls');
                    
                    if(data.total === 0) {
                        info.innerHTML = '';
                        controls.innerHTML = '';
                        return;
                    }
                    
                    info.innerHTML = `Menampilkan ${data.from || 0} - ${data.to || 0} dari total ${data.total} aktivitas`;
                    
                    let btns = '';
                    if (data.prev_page_url) {
                        btns += `<button onclick="changeAuditPage(${data.current_page - 1})" style="padding: 6px 12px; border: 1px solid var(--panel-border); background: var(--panel-bg); color: var(--text-main); border-radius: 8px; cursor: pointer; font-size: 12px; transition: all 0.2s;"><i class="fa-solid fa-chevron-left"></i> Prev</button>`;
                    }
                    
                    btns += `<span style="padding: 6px 12px; font-size: 12px; font-weight: 600; color: var(--text-main);">Halaman ${data.current_page} / ${data.last_page}</span>`;
                    
                    if (data.next_page_url) {
                        btns += `<button onclick="changeAuditPage(${data.current_page + 1})" style="padding: 6px 12px; border: 1px solid var(--panel-border); background: var(--panel-bg); color: var(--text-main); border-radius: 8px; cursor: pointer; font-size: 12px; transition: all 0.2s;">Next <i class="fa-solid fa-chevron-right"></i></button>`;
                    }
                    
                    controls.innerHTML = btns;
                }

                function changeAuditPage(page) {
                    currentAuditPage = page;
                    loadAuditLogs();
                }

                // Initial Load
                setTimeout(() => {
                    if(document.getElementById('audit-log-container')) {
                        loadAuditLogs();
                    }
                }, 100);

                function confirmClearAuditLog() {
                    document.getElementById('modal-clear-audit').style.display = 'flex';
                }

                function closeClearAuditModal() {
                    document.getElementById('modal-clear-audit').style.display = 'none';
                }

                async function executeClearAuditLog() {
                    const timeframe = document.getElementById('audit-clear-timeframe').value;
                    const btn = document.getElementById('btn-execute-clear');
                    const originalText = btn.innerHTML;
                    btn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin"></i> Memproses...';
                    btn.disabled = true;

                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').content;
                        const response = await fetch('/master-demo/security/audit-logs/clear', {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({ timeframe: timeframe })
                        });
                        const result = await response.json();
                        if (result.success) {
                            closeClearAuditModal();
                            window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Audit Log berhasil dibersihkan.', type: 'success' }}));
                            loadAuditLogs();
                        } else {
                            alert(result.message || 'Gagal membersihkan log.');
                        }
                    } catch (e) {
                        console.error(e);
                        alert('Terjadi kesalahan saat membersihkan log.');
                    } finally {
                        btn.innerHTML = originalText;
                        btn.disabled = false;
                    }
                }
            

            function filterHrisEmployees() {
                const query = document.getElementById('hris-emp-search').value.toLowerCase();
                const rows = document.querySelectorAll('.hris-emp-row');
                rows.forEach(row => {
                    const name = row.getAttribute('data-name');
                    const role = row.getAttribute('data-role');
                    if (name.includes(query) || role.includes(query)) {
                        row.style.display = 'table-row';
                    } else {
                        row.style.display = 'none';
                    }
                });
            }
            function openEditEmployeeModal(id, name, email) {
                document.getElementById('editEmpId').value = id;
                document.getElementById('editEmpName').value = name;
                document.getElementById('editEmpEmail').value = email;
                document.getElementById('editEmployeeModal').style.display = 'flex';
            }
            function closeEditEmployeeModal() {
                document.getElementById('editEmployeeModal').style.display = 'none';
            }
            function openDeleteEmployeeModal(id, name, role) {
                document.getElementById('delEmpId').value = id;
                document.getElementById('delEmpName').innerText = name;
                document.getElementById('delEmpRole').innerText = role;
                document.getElementById('deleteEmployeeModal').style.display = 'flex';
            }
            function closeDeleteEmployeeModal() {
                document.getElementById('deleteEmployeeModal').style.display = 'none';
            }
            

                function calculateRecipeCost() {
                    let total = 0;
                    document.querySelectorAll('.recipe-item-row').forEach(row => {
                        const select = row.querySelector('.material-select');
                        const qty = row.querySelector('.material-qty').value;
                        const costPerGram = select.options[select.selectedIndex].getAttribute('data-cost');
                        if(qty && costPerGram) {
                            total += (parseFloat(qty) * parseFloat(costPerGram));
                        }
                    });
                    document.getElementById('recipe-total-cost').innerText = 'Rp ' + new Intl.NumberFormat('id-ID').format(total);
                }
                
                function addRecipeItemRow() {
                    const container = document.getElementById('recipe-items-container');
                    const firstRow = container.querySelector('.recipe-item-row');
                    const newRow = firstRow.cloneNode(true);
                    newRow.querySelector('.material-qty').value = '';
                    container.appendChild(newRow);
                }
                

                function searchRecipes() {
                    const query = document.getElementById('recipe-search').value.toLowerCase();
                    const items = document.querySelectorAll('.recipe-item, .recipe-item-details');
                    items.forEach(el => {
                        const name = el.getAttribute('data-name');
                        if (name && name.includes(query)) {
                            el.style.display = 'block';
                        } else {
                            el.style.display = 'none';
                        }
                    });
                }
                

                        function editMaterial(id, name, min, max, cost) {
                            document.getElementById('edit-mat-id').value = id;
                            document.getElementById('edit-mat-name').value = name;
                            document.getElementById('edit-mat-min').value = min;
                            document.getElementById('edit-mat-max').value = max;
                            document.getElementById('edit-mat-cost').value = cost;
                            document.getElementById('modal-edit-material').style.display = 'flex';
                        }
                        

            function switchInventoryTab(tab) {
                document.querySelectorAll('.inv-tab-content').forEach(el => el.style.display = 'none');
                document.getElementById('inv-tab-' + tab).style.display = 'block';
                if(tab === 'master') loadInventoryMaster();
                if(tab === 'operations') loadInventoryMasterOptions();
            }

            let inventoryData = { products: [], warehouses: [], categories: [], brands: [] };

            async function loadInventoryMaster() {
                try {
                    const res = await fetch('/api/inventory');
                    const data = await res.json();
                    inventoryData = data;
                    
                    document.getElementById('inv-total-products').innerText = data.products.total || data.products.data?.length || 0;
                    
                    const tbody = document.getElementById('inv-products-list');
                    tbody.innerHTML = '';
                    
                    const items = data.products.data || data.products;
                    items.forEach(p => {
                        const totalStock = p.balances ? p.balances.reduce((acc, b) => acc + parseFloat(b.quantity), 0) : 0;
                        tbody.innerHTML += `
                            <tr>
                                <td>${p.sku}</td>
                                <td>${p.name}</td>
                                <td>${p.category || '-'}</td>
                                <td>${p.brand || '-'}</td>
                                <td>${totalStock} ${p.unit}</td>
                                <td>
                                    <button class="btn" style="padding: 4px 8px; font-size: 12px;">Edit</button>
                                </td>
                            </tr>
                        `;
                    });
                } catch(e) {
                    console.error('Failed to load inventory', e);
                }
            }

            function loadInventoryMasterOptions() {
                if(!inventoryData.products.data) loadInventoryMaster().then(populateOptions);
                else populateOptions();

                function populateOptions() {
                    const products = inventoryData.products.data || inventoryData.products;
                    const warehouses = inventoryData.warehouses;
                    
                    let pOpts = '<option value="">Select Product...</option>';
                    products.forEach(p => pOpts += `<option value="${p.id}">${p.sku} - ${p.name}</option>`);
                    
                    let wOpts = '<option value="">Select Warehouse...</option>';
                    warehouses.forEach(w => wOpts += `<option value="${w.id}">${w.name}</option>`);
                    
                    document.getElementById('adj-product').innerHTML = pOpts;
                    document.getElementById('tf-product').innerHTML = pOpts;
                    document.getElementById('sc-product').innerHTML = pOpts;
                    
                    document.getElementById('adj-warehouse').innerHTML = wOpts;
                    document.getElementById('tf-from').innerHTML = wOpts;
                    document.getElementById('tf-to').innerHTML = wOpts;
                    document.getElementById('sc-warehouse').innerHTML = '<option value="">All Warehouses</option>' + wOpts;
                }
            }

            function openCreateProductModal() {
                document.getElementById('form-product-master').reset();
                document.getElementById('modal-product-master').style.display = 'flex';
            }

            async function submitProductMaster(e) {
                e.preventDefault();
                const payload = {
                    sku: document.getElementById('prod-sku').value,
                    name: document.getElementById('prod-name').value,
                    unit: document.getElementById('prod-unit').value,
                    standard_cost: document.getElementById('prod-cost').value,
                    min_stock: document.getElementById('prod-min').value,
                    max_stock: document.getElementById('prod-max').value,
                    has_batches: document.getElementById('prod-batches').checked,
                    has_serial_numbers: document.getElementById('prod-serials').checked
                };
                try {
                    const res = await fetch('/api/inventory/products', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                        body: JSON.stringify(payload)
                    });
                    if(res.ok) {
                        alert('Product saved!');
                        document.getElementById('modal-product-master').style.display = 'none';
                        loadInventoryMaster();
                    } else {
                        const err = await res.json();
                        alert('Error: ' + (err.message || 'Unknown error'));
                    }
                } catch(e) { console.error(e); }
            }

            async function submitStockAdjustment(e) {
                e.preventDefault();
                const payload = {
                    product_id: document.getElementById('adj-product').value,
                    warehouse_id: document.getElementById('adj-warehouse').value,
                    quantity: document.getElementById('adj-quantity').value,
                    type: document.getElementById('adj-type').value,
                    reference: 'ADJ-' + Date.now()
                };
                
                try {
                    const res = await fetch('/api/inventory/movements', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                        body: JSON.stringify(payload)
                    });
                    if(res.ok) {
                        alert('Stock adjusted successfully!');
                        e.target.reset();
                        loadInventoryMaster();
                    } else {
                        const err = await res.json();
                        alert('Error: ' + (err.message || 'Unknown error'));
                    }
                } catch(e) { console.error(e); }
            }

            async function submitStockTransfer(e) {
                e.preventDefault();
                const payload = {
                    product_id: document.getElementById('tf-product').value,
                    from_warehouse_id: document.getElementById('tf-from').value,
                    to_warehouse_id: document.getElementById('tf-to').value,
                    quantity: document.getElementById('tf-quantity').value,
                    reference: 'TF-' + Date.now()
                };
                
                try {
                    const res = await fetch('/api/inventory/transfer', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                        body: JSON.stringify(payload)
                    });
                    if(res.ok) {
                        alert('Transfer executed successfully!');
                        e.target.reset();
                        loadInventoryMaster();
                    } else {
                        const err = await res.json();
                        alert('Error: ' + (err.message || 'Unknown error'));
                    }
                } catch(e) { console.error(e); }
            }
            
            async function loadStockCard() {
                const pId = document.getElementById('sc-product').value;
                const wId = document.getElementById('sc-warehouse').value;
                const tbody = document.getElementById('inv-stock-card-list');
                
                if(!pId) {
                    tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">Select a product to view stock card.</td></tr>';
                    return;
                }
                
                try {
                    let url = '/api/inventory/products/' + pId + '/stock-card';
                    if(wId) url += '?warehouse_id=' + wId;
                    
                    const res = await fetch(url);
                    const data = await res.json();
                    
                    tbody.innerHTML = '';
                    let balance = 0; // The API returns descending order, so to calculate balance visually we might need to sort or assume server returns it (wait, server returns descending).
                    
                    // Simple rendering
                    if(data.data && data.data.length > 0) {
                        // Reverse for balance calculation if needed, but let's just show it.
                        const items = data.data.reverse(); 
                        items.forEach(m => {
                            const qty = parseFloat(m.quantity);
                            balance += qty;
                            const qtyIn = qty > 0 ? qty : '-';
                            const qtyOut = qty < 0 ? Math.abs(qty) : '-';
                            const wName = m.warehouse ? m.warehouse.name : '-';
                            
                            tbody.innerHTML = `
                                <tr>
                                    <td>${new Date(m.created_at).toLocaleString()}</td>
                                    <td>${m.type}</td>
                                    <td>${wName}</td>
                                    <td style="color: var(--success);">${qtyIn}</td>
                                    <td style="color: var(--danger);">${qtyOut}</td>
                                    <td style="font-weight: bold;">${balance.toFixed(3)}</td>
                                    <td>${m.reference || m.notes || '-'}</td>
                                </tr>
                            ` + tbody.innerHTML; // prepend since we reversed
                        });
                    } else {
                        tbody.innerHTML = '<tr><td colspan="7" style="text-align: center;">No movements found.</td></tr>';
                    }
                } catch(e) { console.error(e); }
            }
        

    function showToast(message) {
        let toast = document.getElementById('hrisToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'hrisToast';
            toast.className = 'ios-toast';
            toast.innerHTML = `<i class="fa-solid fa-check-circle" style="color: #4ade80;"></i> <span id="hrisToastMsg"></span>`;
            document.body.appendChild(toast);
        }
        document.getElementById('hrisToastMsg').innerText = message;
        toast.classList.add('show');
        setTimeout(() => { toast.classList.remove('show'); }, 3000);
    }

    function handleFileSelect(input, containerId) {
        const container = document.getElementById(containerId);
        if (!container || !input) return;
        
        if (input.files && input.files.length > 0) {
            const file = input.files[0];
            const fileName = file.name;
            let fileSize = (file.size / 1024).toFixed(1) + ' KB';
            if (file.size > 1024 * 1024) fileSize = (file.size / (1024 * 1024)).toFixed(1) + ' MB';
            
            container.classList.add('has-file');
            const prefix = containerId === 'docUploadArea' ? 'doc' : 'payslip';
            document.getElementById(prefix + 'FileName').innerText = fileName;
            document.getElementById(prefix + 'FileSize').innerText = fileSize;
        } else {
            clearFileUpload(input.id, containerId);
        }
    }

    function clearFileUpload(inputId, containerId) {
        const input = document.getElementById(inputId);
        const container = document.getElementById(containerId);
        if (input) input.value = '';
        if (container) container.classList.remove('has-file');
    }

    async function submitEditEmployee(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);
        
        try {
            const res = await fetch("{{ route('master-demo.hris.updateUser') ?? '#' }}", {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' }
            });
            
            if(res.ok) {
                const id = document.getElementById('editEmpId').value;
                document.getElementById('emp-name-' + id).innerText = document.getElementById('editEmpName').value;
                document.getElementById('emp-email-' + id).innerText = document.getElementById('editEmpEmail').value;
                
                closeEditEmployeeModal();
                showToast('Data karyawan berhasil diperbarui!');
            } else {
                alert('Gagal menyimpan data.');
            }
        } catch(err) {
            console.error(err);
        }
    }

    async function submitDeleteEmployee(e) {
        e.preventDefault();
        const id = document.getElementById('delEmpId').value;
        const token = document.querySelector('input[name="_token"]').value;
        
        try {
            const res = await fetch("/master-demo/employee/" + id, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ _method: 'DELETE' })
            });
            
            if(res.ok) {
                const row = document.getElementById('emp-row-' + id);
                if(row) row.remove();
                
                closeDeleteEmployeeModal();
                showToast('Karyawan dinonaktifkan.');
            } else {
                alert('Gagal menghapus karyawan.');
            }
        } catch (err) {
            console.error(err);
        }
    }
    
    function editUser(id, name, targetHours) {
        openEditProfileModal(id, name, '', 'Full-Time', targetHours);
    }
    
    function openEditProfileModal(id, name, job, type, target) {
        document.getElementById('edit-user-id').value = id;
        document.getElementById('edit-user-name').value = name;
        document.getElementById('edit-user-job').value = job;
        document.getElementById('edit-user-type').value = type;
        document.getElementById('edit-user-target').value = target;
        document.getElementById('modal-edit-user').style.display = 'flex';
    }
    
    function confirmDeleteUser(id) {
        document.getElementById('confirm-title').innerText = "Hapus Staf";
        document.getElementById('confirm-msg').innerText = "Apakah Anda yakin ingin menonaktifkan/menghapus staf ini dari sistem?";
        document.getElementById('confirm-form').action = "/master-demo/employee/" + id + "/delete"; 
        document.getElementById('modal-confirm').style.display = 'flex';
    }
    
    function confirmLogout() {
        document.getElementById('confirm-title').innerText = "Logout";
        document.getElementById('confirm-msg').innerText = "Apakah Anda yakin ingin keluar dari sistem?";
        document.getElementById('confirm-form').action = "/master-demo/logout"; 
        document.getElementById('modal-confirm').style.display = 'flex';
    }

    const formatCurrency = (val) => new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(val);
    const formatNumber = (val) => new Intl.NumberFormat('id-ID').format(val);

    const viewMap = {
        'overview': { title: 'Command Center', sub: 'Real-time enterprise overview across all active modules.' },
        'crm': { title: 'CRM & Sales', sub: 'Pipeline management, quotations, and revenue tracking.' },
        'purchasing': { title: 'Purchasing', sub: 'Procurement flows, supplier relations, and pending goods.' },
        'production': { title: 'Production & QA', sub: 'Manufacturing processes and quality assurance metrics.' },
        'modules': { title: 'Module Controls', sub: 'Toggle enterprise capabilities for this tenant.' },
        'hris': { title: 'HRIS & Karyawan', sub: 'Pusat kendali SDM: Setup Jam Shif, File Perusahaan, Statistik Karyawan, dan Resign.' },
        'recipes': { title: 'Master Resep Produksi', sub: 'Otoritas Bill of Materials & Auto-Backflush.' },
        'chat_internal': { title: 'Internal Chat & Grup', sub: 'Komunikasi real-time dan aman antar karyawan dan grup.' }
    };

    function switchView(viewId) {
        if (viewId === 'crm') {
            window.location.href = "{{ route('crm.dashboard') }}";
            return;
        }
        localStorage.setItem('subaActiveView', viewId);

        document.querySelectorAll('.view-section').forEach(el => el.classList.remove('active'));
        document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
        
        const targetView = document.getElementById('view-' + viewId);
        if (targetView) targetView.classList.add('active');
        
        if (viewId === 'chat_internal') {
            loadMainDivisions();
            if(!currentChatChannel) selectChatChannel('general', 'Grup General');
        }

        if (viewId === 'payroll') {
            if (typeof loadPayrolls === 'function') loadPayrolls();
        }

        const matchingNav = Array.from(document.querySelectorAll('.nav-item')).find(n => n.getAttribute('onclick') === `switchView('${viewId}')`);
        if (matchingNav) {
            matchingNav.classList.add('active');
            
            document.getElementById('header-title').innerText = viewMap[viewId] ? viewMap[viewId].title : viewId;
            document.getElementById('header-subtitle').innerText = viewMap[viewId] ? viewMap[viewId].sub : 'Master Portal Management.';
        }
    }

    // Theme Management
    function toggleTheme() {
        const current = document.documentElement.getAttribute('data-theme');
        const next = current === 'dark' ? 'light' : 'dark';
        document.documentElement.setAttribute('data-theme', next);
        localStorage.setItem('sikarya_theme', next);
    }

    function setPrimaryColor(color) {
        document.documentElement.style.setProperty('--accent', color);
        document.documentElement.style.setProperty('--accent-hover', color);
        document.documentElement.style.setProperty('--accent-active', color);
        localStorage.setItem('sikarya_color', color);
    }

    document.addEventListener('DOMContentLoaded', () => {
        // Initialize Theme Controls
        const savedColor = localStorage.getItem('sikarya_color');
        if(savedColor) {
            const picker = document.getElementById('theme-color-picker');
            if(picker) picker.value = savedColor;
        }

        // Mock load data...
        setTimeout(() => {
            document.getElementById('metrics-crm-value').innerHTML = 'Rp 14.5B';
            document.getElementById('metrics-po-value').innerHTML = '32';
            document.getElementById('metrics-qa-value').innerHTML = '1.2%';
            document.getElementById('metrics-inv-value').innerHTML = 'Rp 8.2B';
        }, 800);
        
        loadAnalytics();
    });

    // Fetch Analytics Data
    async function loadAnalytics() {
        try {
            const response = await fetch('/api/analytics/overview', {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            const data = await response.json();
            
            // Populate Overview Cards
            if(data.crm) {
                document.getElementById('metrics-crm-value').innerText = formatCurrency(data.crm.open_pipeline_value);
                document.getElementById('crm-total-leads').innerText = data.crm.total_leads;
                document.getElementById('crm-win-rate').innerText = data.crm.conversion_rate + '%';
                document.getElementById('crm-won-value').innerText = formatCurrency(data.crm.won_value);
            }
            if(data.purchasing) {
                document.getElementById('metrics-po-value').innerText = data.purchasing.pending_receipts + ' Orders';
            }
            if(data.production) {
                document.getElementById('metrics-qa-value').innerText = data.production.defect_rate + '%';
            }
            if(data.inventory) {
                document.getElementById('metrics-inv-value').innerText = formatCurrency(data.inventory.estimated_valuation);
            }

            // Populate Alerts
            const alertsBox = document.getElementById('alerts-container');
            if(data.alerts && data.alerts.length > 0) {
                alertsBox.innerHTML = '';
                data.alerts.forEach(alert => {
                    const color = alert.severity === 'high' ? 'var(--danger)' : 'var(--warning)';
                    alertsBox.innerHTML += `
                        <div style="background: rgba(0,0,0,0.2); border-left: 4px solid ${color}; padding: 12px 16px; border-radius: 0 8px 8px 0; margin-bottom: 10px;">
                            <strong style="display: block; font-size: 13px; margin-bottom: 4px; color: ${color};">${alert.title}</strong>
                            <span style="font-size: 13px; color: var(--text-muted);">${alert.message}</span>
                        </div>
                    `;
                });
            } else {
                alertsBox.innerHTML = '<div class="list-item"><span class="desc">No critical alerts at this time.</span></div>';
            }

        } catch (error) {
            console.error('Failed to load analytics', error);
            document.querySelectorAll('.loader').forEach(el => el.parentElement.innerText = 'Error loading data');
        }
    }

    // Module Toggle Logic (submits silently or reloads)
    function toggleModule(featureKey, isActive) {
        const state = isActive ? 'active' : 'off';
        const form = document.getElementById('module-form');
        document.getElementById('module-state').value = state;
        
        // Target route: /master-demo/companies/{company}/features/{feature}
        form.action = `/master-demo/companies/{{ $company->id }}/features/${featureKey}`;
        form.submit();
    }

    // Initialize
    let activeView = localStorage.getItem('subaActiveView') || 'overview';
    if (activeView === 'chat_internal') {
        activeView = 'overview';
        localStorage.setItem('subaActiveView', 'overview');
    }
    switchView(activeView);

    document.addEventListener('DOMContentLoaded', () => {
        loadAnalytics();
    });


        let currentDeleteTaskId = null;

    function openPopup(id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'flex';
        }
    }

    function closePopup(id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'none';
        }
    }

    function showToast(message) {
        let toast = document.getElementById('global-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'global-toast';
            toast.className = 'ios-toast';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<i class="fa-solid fa-circle-check" style="color: #34D399; font-size: 18px;"></i><span>${message}</span>`;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    function updateAssignTaskDivision(selectElem) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const divisionInput = document.getElementById('assign-task-division');
        if (!selectElem.value) {
            divisionInput.value = '';
            return;
        }
        const division = selectedOption.getAttribute('data-division') || 'Tanpa Divisi';
        divisionInput.value = division;
    }

    function updateEditTaskDivision(selectElem) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const divisionInput = document.getElementById('edit-task-division');
        if (!selectElem.value) {
            divisionInput.value = '';
            return;
        }
        const division = selectedOption.getAttribute('data-division') || 'Tanpa Divisi';
        divisionInput.value = division;
    }

    function handleStoreTask(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Tugas berhasil disimpan.');
                closePopup('task-add-modal');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                alert('Gagal menyimpan tugas.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan/server.');
        });
    }

    function openDeleteTaskModal(taskId) {
        currentDeleteTaskId = taskId;
        openPopup('task-delete-modal');
    }

    function confirmDeleteTask() {
        if (!currentDeleteTaskId) return;

        fetch(`/master-demo/tasks/${currentDeleteTaskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Tugas berhasil dihapus.');
                closePopup('task-delete-modal');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                alert('Gagal menghapus tugas.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan/server.');
        });
    }

    document.addEventListener('dragleave', function(ev) {
        const box = ev.target.closest('.division-box');
        if(box) {
            box.style.borderColor = box.getAttribute('data-id') ? 'transparent' : 'var(--danger)'; 
        }
    });

    window.createNewDivision = function() {
        const input = document.getElementById('add-div-name-input');
        if(input) input.value = '';
        const codeInput = document.getElementById('add-div-code-input');
        if(codeInput) codeInput.value = '';
        
        const modal = document.getElementById('division-add-modal');
        if(modal) {
            modal.style.display = 'flex';
        } else {
            console.error('Modal division-add-modal tidak ditemukan');
        }
    };

    window.submitNewDivision = async function(e) {
        if(e) e.preventDefault();
        const name = document.getElementById('add-div-name-input').value;
        if(!name || name.trim() === '') return;
        const code = document.getElementById('add-div-code-input') ? document.getElementById('add-div-code-input').value : '';

        try {
            const response = await fetch('/master-demo/divisions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name: name, code: code })
            });
            
            const result = await response.json();
            if(result.success) {
                closePopup('division-add-modal');
                showToast('Divisi berhasil ditambahkan.');
                setTimeout(() => window.location.reload(), 500); 
            } else {
                alert('Gagal membuat divisi: ' + (result.message || 'Unknown error'));
            }
        } catch(e) {
            console.error(e);
            alert('Gagal membuat divisi.');
        }
    };

    let currentDivEditId = null;
    let currentDivDeleteId = null;

    function openRenameDivisionModal(id, currentName) {
        currentDivEditId = id;
        document.getElementById('edit-div-name-input').value = currentName;
        openPopup('division-edit-modal');
    }

    async function confirmRenameDivision() {
        const name = document.getElementById('edit-div-name-input').value;
        if(!name || name.trim() === '') return;
        const id = currentDivEditId;

        try {
            const response = await fetch(`/master-demo/divisions/${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name: name })
            });
            
            const result = await response.json();
            if(result.success) {
                document.getElementById('div-name-' + id).innerText = name;
                showToast('Nama divisi berhasil diubah.');
                closePopup('division-edit-modal');
                setTimeout(() => window.location.reload(), 500);
            }
        } catch(e) {
            console.error(e);
            alert('Gagal mengubah nama divisi.');
        }
    }

    function openDeleteDivisionModal(id, currentName) {
        currentDivDeleteId = id;
        document.getElementById('del-div-name').innerText = currentName;
        openPopup('division-delete-modal');
    }

    async function confirmDeleteDivision() {
        const id = currentDivDeleteId;
        try {
            const response = await fetch(`/master-demo/divisions/${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const result = await response.json();
            if(result.success) {
                showToast('Divisi berhasil dihapus.');
                closePopup('division-delete-modal');
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert(result.message || 'Gagal menghapus divisi karena masih ada modul di dalamnya.');
                closePopup('division-delete-modal');
            }
        } catch(e) {
            console.error(e);
            alert('Gagal menghapus divisi.');
        }
    }

    async function removeModuleFromDivision(featureKey) {
        if(!confirm('Keluarkan modul ini dari divisinya?')) return;
        
        try {
            const response = await fetch('/master-demo/features/assign', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    feature_key: featureKey,
                    division_id: null
                })
            });
            
            const result = await response.json();
            if(result.success) {
                showToast('Modul dikeluarkan dari divisi.');
                setTimeout(() => window.location.reload(), 300);
            }
        } catch(e) {
            console.error(e);
            alert('Gagal mengeluarkan modul.');
        }
    }


