
    let workspaceData = [];
    let flatDataMap = {};
    
    function loadOrgWorkspace() {
        fetch('/organization/tree')
            .then(res => {
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                return res.json();
            })
            .then(data => {
                workspaceData = data;
                data.forEach(d => flatDataMap[d.id] = d);
                renderWorkspace();
            })
            .catch(err => {
                document.getElementById('org-workspace-container').innerHTML = `<div style="padding: 24px; color: #ef4444; background: rgba(239,68,68,0.1); border-radius: 12px;">Failed to load organization data. Error: ${err.message}</div>`;
            });
    }
    
    function renderWorkspace() {
        const container = document.getElementById('org-workspace-container');
        const searchTerm = document.getElementById('org-search').value.toLowerCase();
        const deptFilter = document.getElementById('org-filter-dept').value;
        
        // Group by department
        let departments = {};
        workspaceData.forEach(user => {
            if (deptFilter && user.department !== deptFilter) return;
            
            const matchSearch = user.name.toLowerCase().includes(searchTerm) || 
                                user.positionName.toLowerCase().includes(searchTerm) || 
                                user.role.toLowerCase().includes(searchTerm);
                                
            if (searchTerm && !matchSearch) return;
            
            if (!departments[user.department]) {
                departments[user.department] = { managers: [], staff: [] };
            }
            if (user.tags && user.tags.includes('manager') || user.role === 'ceo') {
                departments[user.department].managers.push(user);
            } else {
                departments[user.department].staff.push(user);
            }
        });
        
        let html = '';
        const deptKeys = Object.keys(departments).sort((a,b) => a === 'Perusahaan' ? -1 : 1);
        
        if (deptKeys.length === 0) {
            container.innerHTML = `<div style="text-align:center; padding: 60px; color: var(--text-muted);">No employees found matching the filters.</div>`;
            return;
        }
        
        deptKeys.forEach(dept => {
            const group = departments[dept];
            const deptColor = getDeptColor(dept);
            const totalEmps = group.managers.length + group.staff.length;
            
            html += `
            <div class="org-group" style="border-top: 4px solid ${deptColor}">
                <div class="org-group-header">
                    <div class="org-group-title">
                        <i class="fa-solid fa-layer-group" style="color: ${deptColor}"></i>
                        ${dept}
                    </div>
                    <button class="ios-btn ios-btn-primary" style="padding: 4px 12px; font-size: 12px; margin-right: 8px;" onclick="openAddStaffModal('${dept}')"><i class="fa-solid fa-plus"></i> Add Staff</button>
                    <div class="org-group-badge">${totalEmps} Employees</div>
                </div>
                
                ${group.managers.length > 0 ? `
                <div style="margin-bottom: ${group.staff.length > 0 ? '24px' : '0'};">
                    <div style="font-size: 12px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); margin-bottom: 12px; letter-spacing: 0.5px;">Leadership</div>
                    <div class="org-cards-grid">
                        ${group.managers.map(m => renderCard(m, deptColor)).join('')}
                    </div>
                </div>
                ` : ''}
                
                ${group.staff.length > 0 ? `
                <div class="staff-list-container">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; cursor: pointer;" onclick="toggleStaff('${dept.replace(/\\s/g, '')}')">
                        <div style="font-size: 12px; text-transform: uppercase; font-weight: 700; color: var(--text-muted); letter-spacing: 0.5px;">Team Members (${group.staff.length})</div>
                        <i id="icon-staff-${dept.replace(/\\s/g, '')}" class="fa-solid fa-chevron-down" style="color: var(--text-muted); font-size: 12px;"></i>
                    </div>
                    <div id="staff-${dept.replace(/\\s/g, '')}" class="org-cards-grid" style="display: grid;">
                        ${group.staff.map(s => renderCard(s, deptColor)).join('')}
                    </div>
                </div>
                ` : ''}
            </div>
            `;
        });
        
        container.innerHTML = html;
    }
    
    function renderCard(user, color) {
        const perfBadgeClass = user.performanceBadge ? user.performanceBadge.split(' ')[0] : 'Good';
        const perfBadgeText = user.performanceBadge || 'Good';
        const statusClass = user.status === 'suspended' ? 'suspended' : (user.status === 'leave' ? 'leave' : 'active');
        const statusText = user.status === 'suspended' ? 'ðŸ”´ Suspended' : (user.status === 'leave' ? 'ðŸŸ¡ Leave' : 'ðŸŸ¢ Active');
        
        return `
        <div class="org-card" onclick="openOrgDrawer('${user.id}')">
            <div class="org-card-header">
                <img src="${user.imageUrl}" class="org-card-img" style="border-color: ${color}">
                <div class="org-card-info">
                    <div class="org-card-name" title="${user.name}">${user.name}</div>
                    <div class="org-card-pos">${user.positionName}</div>
                </div>
            </div>
            <div class="org-card-badges">
                <span class="org-badge org-badge-status-${statusClass}">${statusText}</span>
                <span class="org-badge org-badge-perf-${perfBadgeClass}"><i class="fa-solid fa-award"></i> ${perfBadgeText}</span>
                <span class="org-badge org-badge-dept">${user.employmentType || 'Full-Time'}</span>
            </div>
            <div class="org-card-actions" onclick="event.stopPropagation()">
                <button class="org-btn-action" onclick="openEditDrawer('${user.id}')"><i class="fa-solid fa-pen"></i> Edit</button>
                <button class="org-btn-action" onclick="openPerfDrawer('${user.id}')"><i class="fa-solid fa-chart-line"></i> Perf</button>
                <button class="org-btn-action" onclick="openAssignDrawer('${user.id}')"><i class="fa-solid fa-tasks"></i> Assign</button>
                <button class="org-btn-action" style="color: #ef4444; flex: 0.5;" onclick="openDeleteModal('${user.id}', '${user.name.replace(\"'\", \"\\'\")}', '${user.positionName.replace(\"'\", \"\\'\")}', '${user.department.replace(\"'\", \"\\'\")}')"><i class="fa-solid fa-trash"></i></button>
            </div>
        </div>
        `;
    }
    
    function getDeptColor(dept) {
        const colors = {
            'Marketing': '#3b82f6',
            'Operasional': '#f59e0b',
            'Finance': '#10b981',
            'HRD': '#8b5cf6',
            'Company': '#111827',
            'Perusahaan': '#111827'
        };
        return colors[dept] || '#6b7280';
    }
    
    function toggleStaff(deptId) {
        const el = document.getElementById(`staff-${deptId}`);
        const icon = document.getElementById(`icon-staff-${deptId}`);
        if(el.style.display === 'none') {
            el.style.display = 'grid';
            icon.classList.replace('fa-chevron-right', 'fa-chevron-down');
        } else {
            el.style.display = 'none';
            icon.classList.replace('fa-chevron-down', 'fa-chevron-right');
        }
    }
    
    function collapseAllGroups() {
        document.querySelectorAll('[id^="staff-"]').forEach(el => {
            el.style.display = 'none';
            const icon = document.getElementById('icon-' + el.id);
            if(icon) icon.classList.replace('fa-chevron-down', 'fa-chevron-right');
        });
    }
    
    function expandAllGroups() {
        document.querySelectorAll('[id^="staff-"]').forEach(el => {
            el.style.display = 'grid';
            const icon = document.getElementById('icon-' + el.id);
            if(icon) icon.classList.replace('fa-chevron-right', 'fa-chevron-down');
        });
    }
    
    let searchTimeout;
    function filterOrgWorkspace() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            renderWorkspace();
        }, 300);
    }
    
        // Employee Action Framework JS
    let currentDeleteId = null;

    function showToastMsg(msg, type='success') {
        if(window.showToast) {
            showToast(msg, type);
        } else {
            console.log('['+type+'] ' + msg);
            const el = document.createElement('div');
            el.style.cssText = `position:fixed; bottom:24px; right:24px; background: ${type==='error'?'#ef4444':'#10b981'}; color: white; padding: 12px 24px; border-radius: 8px; z-index: 99999; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: opacity 0.5s; opacity: 1;`;
            el.innerText = msg;
            document.body.appendChild(el);
            setTimeout(() => { el.style.opacity = '0'; setTimeout(()=>el.remove(), 500); }, 3000);
        }
    }

    function openDeleteModal(id, name, pos, dept) {
        currentDeleteId = id;
        document.getElementById('del-emp-name').innerText = name;
        document.getElementById('del-emp-pos').innerText = `${pos} â€¢ ${dept}`;
        document.getElementById('org-delete-modal').style.display = 'flex';
    }
    function closeDeleteModal() { document.getElementById('org-delete-modal').style.display = 'none'; currentDeleteId = null; }
    
    async function submitDelete() {
        if(!currentDeleteId) return;
        try {
            const res = await fetch(`/organization/node/${currentDeleteId}/delete`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') }
            });
            const data = await res.json();
            if(res.ok) { showToastMsg(data.message, 'success'); closeDeleteModal(); loadOrgWorkspace(); }
            else showToastMsg(data.error || 'Failed', 'error');
        } catch(e) { showToastMsg('Network Error', 'error'); }
    }

    function openEditDrawer(id) {
        document.getElementById('edit-id').value = id;
        document.getElementById('org-edit-drawer').classList.add('open');
        switchEditTab('general');
        const user = flatDataMap[id];
        if(user) {
            const form = document.getElementById('edit-emp-form');
            if(form.name) form.name.value = user.name || '';
            if(form.email) form.email.value = user.email || '';
            if(form.username) form.username.value = user.username || '';
            if(form.job_title) form.job_title.value = user.positionName || '';
            if(form.division) form.division.value = user.department || '';
            if(form.role) form.role.value = user.role || '';
        }
    }
    function closeEditDrawer() { document.getElementById('org-edit-drawer').classList.remove('open'); }
    
    function switchEditTab(tab) {
        document.querySelectorAll('.edit-tab-content').forEach(el => el.style.display = 'none');
        document.querySelectorAll('.ios-tab').forEach(el => el.classList.remove('active'));
        document.getElementById('tab-'+tab).style.display = 'block';
        event.currentTarget.classList.add('active');
    }

    async function submitEdit(e) {
        e.preventDefault();
        const id = document.getElementById('edit-id').value;
        const payload = Object.fromEntries(new FormData(e.target).entries());
        try {
            const res = await fetch(`/organization/node/${id}/edit`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if(res.ok) { showToastMsg(data.message, 'success'); closeEditDrawer(); loadOrgWorkspace(); }
            else showToastMsg(data.error || 'Failed', 'error');
        } catch(e) { showToastMsg('Network Error', 'error'); }
    }

    function openPerfDrawer(id) {
        document.getElementById('perf-id').value = id;
        document.getElementById('org-perf-drawer').classList.add('open');
    }
    function closePerfDrawer() { document.getElementById('org-perf-drawer').classList.remove('open'); }
    
    async function submitPerf(e) {
        e.preventDefault();
        const id = document.getElementById('perf-id').value;
        const payload = Object.fromEntries(new FormData(e.target).entries());
        try {
            const res = await fetch(`/organization/node/${id}/performance`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if(res.ok) { showToastMsg(data.message, 'success'); closePerfDrawer(); loadOrgWorkspace(); }
            else showToastMsg(data.error || 'Failed', 'error');
        } catch(e) { showToastMsg('Network Error', 'error'); }
    }

    function openAssignDrawer(id) {
        document.getElementById('assign-id').value = id;
        document.getElementById('org-assign-drawer').classList.add('open');
    }
    function closeAssignDrawer() { document.getElementById('org-assign-drawer').classList.remove('open'); }
    
    async function submitAssign(e) {
        e.preventDefault();
        const id = document.getElementById('assign-id').value;
        const payload = Object.fromEntries(new FormData(e.target).entries());
        try {
            const res = await fetch(`/organization/node/${id}/assign`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if(res.ok) { showToastMsg(data.message, 'success'); closeAssignDrawer(); }
            else showToastMsg(data.error || 'Failed', 'error');
        } catch(e) { showToastMsg('Network Error', 'error'); }
    }

    function openAddStaffModal(dept) {
        document.getElementById('add-staff-dept').value = dept;
        document.getElementById('org-add-staff-modal').style.display = 'flex';
    }
    
    async function submitAddStaff(e) {
        e.preventDefault();
        const payload = Object.fromEntries(new FormData(e.target).entries());
        try {
            const res = await fetch(`/organization/add-staff`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if(res.ok) { showToastMsg(data.message, 'success'); document.getElementById('org-add-staff-modal').style.display = 'none'; loadOrgWorkspace(); }
            else showToastMsg(data.error || 'Failed', 'error');
        } catch(e) { showToastMsg('Network Error', 'error'); }
    }
    // Drawer Logic
    function openOrgDrawer(id) {
        const drawer = document.getElementById('org-drawer');
        drawer.classList.add('open');
        
        const body = document.getElementById('org-drawer-body');
        const footer = document.getElementById('org-drawer-footer');
        
        body.innerHTML = '<div style="text-align: center; padding: 40px; color: var(--text-muted);"><div class="loader"></div><p>Loading Profile...</p></div>';
        footer.innerHTML = '';
        
        fetch(`/organization/node/${id}`)
            .then(res => res.json())
            .then(data => {
                renderDrawerBody(data);
                renderDrawerFooter(data);
            })
            .catch(err => {
                body.innerHTML = `<div style="color: red; padding: 20px;">Failed to load details: ${err.message}</div>`;
            });
    }
    
    function closeOrgDrawer() {
        document.getElementById('org-drawer').classList.remove('open');
    }
    
    function renderDrawerBody(data) {
        const p = data.profile;
        const s = data.stats;
        
        document.getElementById('org-drawer-body').innerHTML = `
            <div style="display: flex; gap: 16px; align-items: center; margin-bottom: 24px;">
                <img src="${p.profile_picture_path || 'https://ui-avatars.com/api/?name='+p.name+'&background=random'}" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid var(--accent); box-shadow: 0 4px 12px rgba(0,0,0,0.1);">
                <div>
                    <h4 style="margin: 0; font-size: 20px; font-weight: 700;">${p.name}</h4>
                    <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 14px;">${p.job_title} &bull; ${p.division}</p>
                    <div style="margin-top: 8px;">
                        <span class="org-badge org-badge-dept">${p.employment_type}</span>
                        <span class="org-badge org-badge-dept"><i class="fa-solid fa-envelope"></i> ${p.email}</span>
                    </div>
                </div>
            </div>
            
            <h5 style="margin: 0 0 12px; font-size: 14px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Workspace Overview</h5>
            <div class="org-info-grid">
                <div class="org-info-box" style="background: rgba(59, 130, 246, 0.05); border-color: rgba(59, 130, 246, 0.1);">
                    <div class="org-info-label">Active Projects</div>
                    <div class="org-info-value" style="font-size: 20px; color: #3b82f6;">${s.active_projects}</div>
                </div>
                <div class="org-info-box" style="background: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.1);">
                    <div class="org-info-label">Completed Tasks</div>
                    <div class="org-info-value" style="font-size: 20px; color: #10b981;">${s.completed_tasks}</div>
                </div>
                <div class="org-info-box" style="background: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.1);">
                    <div class="org-info-label">Leave Balance</div>
                    <div class="org-info-value" style="font-size: 20px; color: #f59e0b;">${s.leave_balance} Days</div>
                </div>
                <div class="org-info-box" style="background: rgba(139, 92, 246, 0.05); border-color: rgba(139, 92, 246, 0.1);">
                    <div class="org-info-label">Attendance Rate</div>
                    <div class="org-info-value" style="font-size: 20px; color: #8b5cf6;">${s.attendance_rate}%</div>
                </div>
            </div>
            
            <h5 style="margin: 24px 0 12px; font-size: 14px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Direct Reports (${data.direct_reports.length})</h5>
            ${data.direct_reports.length > 0 ? `
                <div style="display: flex; flex-direction: column; gap: 8px;">
                    ${data.direct_reports.map(dr => `
                        <div style="display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--panel-secondary); border: 1px solid var(--panel-border); border-radius: 8px; cursor: pointer;" onclick="openOrgDrawer('${dr.id}')">
                            <img src="${dr.profile_picture_path ? '/storage/'+dr.profile_picture_path : 'https://ui-avatars.com/api/?name='+dr.name+'&background=random'}" style="width: 32px; height: 32px; border-radius: 50%;">
                            <div style="flex: 1;">
                                <div style="font-weight: 600; font-size: 13px; color: var(--text-heading);">${dr.name}</div>
                                <div style="font-size: 11px; color: var(--text-muted);">${dr.job_title}</div>
                            </div>
                            <i class="fa-solid fa-chevron-right" style="color: var(--text-muted); font-size: 12px;"></i>
                        </div>
                    `).join('')}
                </div>
            ` : '<div style="padding: 16px; background: var(--panel-secondary); border-radius: 8px; font-size: 13px; color: var(--text-muted); text-align: center;">No direct reports</div>'}
            
            <h5 style="margin: 24px 0 12px; font-size: 14px; font-weight: 600; color: var(--text-muted); text-transform: uppercase;">Integration Links</h5>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 8px;">
                <button class="ios-btn ios-btn-secondary" style="font-size: 13px; padding: 8px;" onclick="window.location.href='/master-demo/payroll'"><i class="fa-solid fa-money-bill-wave"></i> Payroll</button>
                <button class="ios-btn ios-btn-secondary" style="font-size: 13px; padding: 8px;" onclick="window.location.href='/master-demo/tasks'"><i class="fa-solid fa-tasks"></i> Tasks</button>
                <button class="ios-btn ios-btn-secondary" style="font-size: 13px; padding: 8px;" onclick="window.location.href='/master-demo/attendance'"><i class="fa-solid fa-clock"></i> Attendance</button>
                <button class="ios-btn ios-btn-secondary" style="font-size: 13px; padding: 8px;" onclick="window.location.href='/master-demo/documents'"><i class="fa-solid fa-file-alt"></i> Documents</button>
            </div>
        `;
    }
    
    function renderDrawerFooter(data) {
        const actions = data.allowed_actions || [];
        const btnConfigs = {
            'edit': `<button class="ios-btn ios-btn-primary" style="flex: 1;" onclick="openEditDrawer('${data.profile.id}')">Edit Full Profile</button>`,
            'review': `<button class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="openPerfDrawer('${data.profile.id}')">Performance</button>`,
            'request_promotion': `<button class="ios-btn ios-btn-primary" style="flex: 1;" onclick="openEditDrawer('${data.profile.id}')">Request Promotion</button>`,
            'request_transfer': `<button class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="openEditDrawer('${data.profile.id}')">Request Transfer</button>`,
            'assign_task': `<button class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="openAssignDrawer('${data.profile.id}')">Assign Task</button>`,
        };

        let html = '';
        actions.forEach(act => {
            if (btnConfigs[act]) html += btnConfigs[act];
        });

        if (html === '') {
            html = '<div style="text-align:center; width: 100%; color: var(--text-muted); font-size: 13px;">No actions available</div>';
        }
        
        document.getElementById('org-drawer-footer').innerHTML = html;
    }
    
    // Init
    setTimeout(loadOrgWorkspace, 100);

