import re

with open('resources/views/organization/index.blade.php', 'r') as f:
    content = f.read()

# 1. Update org-group-header to add 'Add Staff' button
content = re.sub(
    r'<div class="org-group-badge">\$\{totalEmps\} Employees</div>',
    r'<button class="ios-btn ios-btn-primary" style="padding: 4px 12px; font-size: 12px; margin-right: 8px;" onclick="openAddStaffModal(\'\')"><i class="fa-solid fa-plus"></i> Add Staff</button>\n                    <div class="org-group-badge"> Employees</div>',
    content
)

# 2. Update card actions to Edit, Perf, Assign, Delete
card_actions = '''            <div class="org-card-actions" onclick="event.stopPropagation()">
                <button class="org-btn-action" onclick="openEditDrawer('')"><i class="fa-solid fa-pen"></i> Edit</button>
                <button class="org-btn-action" onclick="openPerfDrawer('')"><i class="fa-solid fa-chart-line"></i> Perf</button>
                <button class="org-btn-action" onclick="openAssignDrawer('')"><i class="fa-solid fa-tasks"></i> Assign</button>
                <button class="org-btn-action" style="color: #ef4444; flex: 0.5;" onclick="openDeleteModal('', '', '', '')"><i class="fa-solid fa-trash"></i></button>
            </div>'''
content = re.sub(
    r'<div class="org-card-actions" onclick="event.stopPropagation()">.*?</div>',
    card_actions,
    content,
    flags=re.DOTALL
)

# 3. Add Custom Drawers and Modals at the end of the view section
modals_html = '''
    <!-- Delete Confirmation Modal -->
    <div id="org-delete-modal" class="modal ios-modal-overlay" style="display:none; z-index: 10005;">
        <div class="modal-content ios-modal" style="max-width: 400px;">
            <div class="ios-modal-header" style="background: rgba(239, 68, 68, 0.1); border-bottom: 1px solid rgba(239, 68, 68, 0.2);">
                <h3 style="margin: 0; font-size: 18px; font-weight: 600; color: #ef4444;"><i class="fa-solid fa-exclamation-triangle"></i> Confirm Deletion</h3>
                <button type="button" class="ios-btn-close" onclick="closeDeleteModal()"><i class="fa-solid fa-times"></i></button>
            </div>
            <div class="ios-modal-body" style="text-align: center; padding: 24px;">
                <p style="margin-bottom: 8px; color: var(--text-muted);">You are about to delete or archive this employee:</p>
                <h4 id="del-emp-name" style="margin: 0 0 4px; font-size: 20px;"></h4>
                <p id="del-emp-pos" style="margin: 0 0 16px; font-size: 14px; font-weight: 600; color: var(--text-heading);"></p>
                <div style="background: rgba(245, 158, 11, 0.1); color: #f59e0b; padding: 12px; border-radius: 8px; font-size: 13px; text-align: left;">
                    <strong>Consequences:</strong> This action may remove access for this user and impact reporting lines. If this user has related data, they will be archived instead of permanently deleted.
                </div>
            </div>
            <div class="ios-modal-footer">
                <button type="button" class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="ios-btn ios-btn-danger" style="flex: 1;" onclick="submitDelete()">Delete</button>
            </div>
        </div>
    </div>

    <!-- Edit Drawer -->
    <div id="org-edit-drawer" class="org-drawer" style="width: 550px;">
        <div class="org-drawer-header">
            <h3 style="margin: 0; font-size: 18px; font-weight: 600;">Edit Employee</h3>
            <button class="ios-btn-icon" onclick="closeEditDrawer()"><i class="fa-solid fa-times"></i></button>
        </div>
        <div class="org-drawer-body" style="padding: 0;">
            <div class="ios-tabs" style="display: flex; overflow-x: auto; border-bottom: 1px solid var(--panel-border); padding: 0 16px; background: var(--panel-secondary);">
                <button class="ios-tab active" onclick="switchEditTab('general')">General</button>
                <button class="ios-tab" onclick="switchEditTab('employment')">Employment</button>
                <button class="ios-tab" onclick="switchEditTab('organization')">Organization</button>
                <button class="ios-tab" onclick="switchEditTab('role')">Role</button>
                <button class="ios-tab" onclick="switchEditTab('security')">Security</button>
                <button class="ios-tab" onclick="switchEditTab('payroll')">Payroll</button>
                <button class="ios-tab" onclick="switchEditTab('documents')">Documents</button>
                <button class="ios-tab" onclick="switchEditTab('notification')">Notification</button>
            </div>
            <form id="edit-emp-form" onsubmit="submitEdit(event)" style="padding: 24px;">
                <input type="hidden" id="edit-id" name="id">
                
                <div id="tab-general" class="edit-tab-content">
                    <div class="form-group"><label>Name</label><input type="text" name="name" class="form-control"></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control"></div>
                    <div class="form-group"><label>Username</label><input type="text" name="username" class="form-control"></div>
                    <div class="form-group"><label>Phone</label><input type="text" name="phone" class="form-control"></div>
                    <div class="form-group"><label>Address</label><textarea name="address" class="form-control"></textarea></div>
                </div>
                
                <div id="tab-employment" class="edit-tab-content" style="display:none;">
                    <div class="form-group"><label>Department</label><input type="text" name="division" class="form-control"></div>
                    <div class="form-group"><label>Position</label><input type="text" name="job_title" class="form-control"></div>
                    <div class="form-group"><label>Employment Type</label><input type="text" name="employment_type" class="form-control"></div>
                    <div class="form-group"><label>Status</label><select name="status" class="form-control"><option value="active">Active</option><option value="leave">Leave</option></select></div>
                </div>

                <div id="tab-organization" class="edit-tab-content" style="display:none;">
                    <div class="form-group"><label>Manager (Username)</label><input type="text" name="parent" class="form-control"></div>
                </div>

                <div id="tab-role" class="edit-tab-content" style="display:none;">
                    <div class="form-group"><label>System Role</label><select name="role" class="form-control"><option value="staff">Staff</option><option value="manager">Manager</option><option value="ceo">CEO</option></select></div>
                </div>

                <div id="tab-security" class="edit-tab-content" style="display:none;">
                    <div class="form-group"><label>Change Password (leave blank to keep current)</label><input type="password" name="password" class="form-control"></div>
                </div>

                <div id="tab-payroll" class="edit-tab-content" style="display:none;">
                    <div class="form-group"><label>Base Salary</label><input type="number" name="base_salary" class="form-control"></div>
                </div>

                <div id="tab-documents" class="edit-tab-content" style="display:none;">
                    <p style="color: var(--text-muted); font-size: 13px;">Manage employee documents like NDA and Contracts in the Documents Module.</p>
                </div>

                <div id="tab-notification" class="edit-tab-content" style="display:none;">
                    <div class="form-group"><label>Email Notifications</label><select class="form-control"><option>Enabled</option><option>Disabled</option></select></div>
                </div>

                <div style="margin-top: 24px;">
                    <button type="submit" class="ios-btn ios-btn-primary" style="width: 100%;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Performance Drawer -->
    <div id="org-perf-drawer" class="org-drawer" style="width: 550px;">
        <div class="org-drawer-header">
            <h3 style="margin: 0; font-size: 18px; font-weight: 600;">Performance & Analytics</h3>
            <button class="ios-btn-icon" onclick="closePerfDrawer()"><i class="fa-solid fa-times"></i></button>
        </div>
        <div class="org-drawer-body">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 24px;">
                <div class="org-info-box"><div class="org-info-label">Attendance</div><div class="org-info-value text-green-500">98% On-time</div></div>
                <div class="org-info-box"><div class="org-info-label">Task Progress</div><div class="org-info-value text-blue-500">24/30 Completed</div></div>
                <div class="org-info-box"><div class="org-info-label">Project Progress</div><div class="org-info-value">3 Active</div></div>
                <div class="org-info-box"><div class="org-info-label">Current Leave</div><div class="org-info-value text-orange-500">12 Days Rem.</div></div>
            </div>

            <h5 style="margin: 0 0 12px; font-size: 14px; font-weight: 600; text-transform: uppercase; color: var(--text-muted);">Add Performance Review</h5>
            <form onsubmit="submitPerf(event)" style="background: var(--panel-secondary); padding: 16px; border-radius: 12px; border: 1px solid var(--panel-border);">
                <input type="hidden" id="perf-id" name="id">
                <div class="form-group"><label>Score (0-100)</label><input type="number" name="score" class="form-control" required min="0" max="100"></div>
                <div class="form-group" style="margin-top: 12px;"><label>Badge</label>
                    <select name="badge" class="form-control" required>
                        <option value="Excellent">Excellent</option><option value="Good">Good</option>
                        <option value="Average">Average</option><option value="Need Review">Need Review</option>
                    </select>
                </div>
                <div class="form-group" style="margin-top: 12px;"><label>Notes</label><textarea name="notes" class="form-control" required></textarea></div>
                <button type="submit" class="ios-btn ios-btn-primary" style="margin-top: 16px; width: 100%;">Submit Review</button>
            </form>
        </div>
    </div>

    <!-- Assign Drawer -->
    <div id="org-assign-drawer" class="org-drawer" style="width: 450px;">
        <div class="org-drawer-header">
            <h3 style="margin: 0; font-size: 18px; font-weight: 600;">Assign Work</h3>
            <button class="ios-btn-icon" onclick="closeAssignDrawer()"><i class="fa-solid fa-times"></i></button>
        </div>
        <div class="org-drawer-body">
            <form onsubmit="submitAssign(event)">
                <input type="hidden" id="assign-id" name="id">
                <div class="form-group">
                    <label>Assignment Type</label>
                    <select name="type" class="form-control" required>
                        <option value="task">Assign Task</option>
                        <option value="project">Assign Project</option>
                        <option value="department">Assign Department</option>
                        <option value="manager">Assign Manager</option>
                        <option value="mentor">Assign Mentor</option>
                        <option value="asset">Assign Asset</option>
                        <option value="shift">Assign Shift</option>
                    </select>
                </div>
                <div class="form-group" style="margin-top: 12px;">
                    <label>Details / Notes</label>
                    <textarea name="details" class="form-control" required></textarea>
                </div>
                <button type="submit" class="ios-btn ios-btn-primary" style="margin-top: 16px; width: 100%;">Create Assignment</button>
            </form>
        </div>
    </div>

    <!-- Add Staff Wizard Modal -->
    <div id="org-add-staff-modal" class="modal ios-modal-overlay" style="display:none; z-index: 10005;">
        <div class="modal-content ios-modal" style="width: 600px; max-width: 90vw;">
            <div class="ios-modal-header">
                <h3 style="margin: 0; font-size: 18px; font-weight: 600;">Add Staff Wizard</h3>
                <button type="button" class="ios-btn-close" onclick="document.getElementById('org-add-staff-modal').style.display='none'"><i class="fa-solid fa-times"></i></button>
            </div>
            <form onsubmit="submitAddStaff(event)" class="ios-modal-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div class="form-group"><label>Full Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="form-group"><label>Email</label><input type="email" name="email" class="form-control" required></div>
                    <div class="form-group"><label>Job Title</label><input type="text" name="job_title" class="form-control" required></div>
                    <div class="form-group"><label>Department</label><input type="text" name="division" id="add-staff-dept" class="form-control" required readonly></div>
                    <div class="form-group"><label>Reporting To (Manager Username)</label><input type="text" name="parent" class="form-control"></div>
                    <div class="form-group"><label>Role</label><select name="role" class="form-control"><option value="staff">Staff</option><option value="manager">Manager</option></select></div>
                </div>
                <div class="ios-modal-footer" style="margin-top: 24px; padding: 0;">
                    <button type="button" class="ios-btn ios-btn-secondary" onclick="document.getElementById('org-add-staff-modal').style.display='none'">Cancel</button>
                    <button type="submit" class="ios-btn ios-btn-primary">Create Employee</button>
                </div>
            </form>
        </div>
    </div>
'''

content = content.replace('<!-- Action Modals -->', modals_html + '\n    <!-- Action Modals -->')

# 4. Add JS Functions for Drawers, Forms and removing alerts
js_functions = '''
    // Employee Action Framework JS
    let currentDeleteId = null;

    function showToastMsg(msg, type='success') {
        if(window.showToast) {
            showToast(msg, type);
        } else {
            console.log('['+type+'] ' + msg);
            // Fallback UI toast
            const el = document.createElement('div');
            el.style.cssText = position:fixed; bottom:24px; right:24px; background: ; color: white; padding: 12px 24px; border-radius: 8px; z-index: 99999; box-shadow: 0 4px 12px rgba(0,0,0,0.1);;
            el.innerText = msg;
            document.body.appendChild(el);
            setTimeout(() => el.remove(), 3000);
        }
    }

    function openDeleteModal(id, name, pos, dept) {
        currentDeleteId = id;
        document.getElementById('del-emp-name').innerText = name;
        document.getElementById('del-emp-pos').innerText = ${pos} • ;
        document.getElementById('org-delete-modal').style.display = 'flex';
    }
    function closeDeleteModal() { document.getElementById('org-delete-modal').style.display = 'none'; currentDeleteId = null; }
    
    async function submitDelete() {
        if(!currentDeleteId) return;
        try {
            const res = await fetch(/organization/node//delete, {
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
        // Pre-fill form from flatDataMap
        const user = flatDataMap[id];
        if(user) {
            const form = document.getElementById('edit-emp-form');
            if(form.name) form.name.value = user.name;
            if(form.job_title) form.job_title.value = user.positionName;
            if(form.division) form.division.value = user.department;
            if(form.role) form.role.value = user.role;
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
            const res = await fetch(/organization/node//edit, {
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
            const res = await fetch(/organization/node//performance, {
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
            const res = await fetch(/organization/node//assign, {
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
            const res = await fetch(/organization/add-staff, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') },
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            if(res.ok) { showToastMsg(data.message, 'success'); document.getElementById('org-add-staff-modal').style.display = 'none'; loadOrgWorkspace(); }
            else showToastMsg(data.error || 'Failed', 'error');
        } catch(e) { showToastMsg('Network Error', 'error'); }
    }

'''

content = content.replace('// Drawer Logic', js_functions + '\n    // Drawer Logic')

# 5. Remove alert in old error handling
content = content.replace("alert(err.error || 'Action failed.');", "showToastMsg(err.error || 'Action failed.', 'error');")
content = content.replace("alert('Network error. Check console.');", "showToastMsg('Network error. Check console.', 'error');")
content = content.replace("alert('Action successful!');", "showToastMsg('Action successful!', 'success');")
content = content.replace("else alert('Action successful!');", "")

# 6. Remove old POST actions endpoint logic
content = re.sub(r'const res = await fetch\(/organization/node/\$\{id\}/actions.*?\}', '''const res = await fetch(/organization/node//actions, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(payload)
            });''', content, flags=re.DOTALL)


# 7. Add CSS for Tabs
css_tabs = '''
    .ios-tab { padding: 12px 16px; background: transparent; border: none; font-size: 14px; font-weight: 600; color: var(--text-muted); cursor: pointer; border-bottom: 2px solid transparent; white-space: nowrap; }
    .ios-tab.active { color: var(--accent); border-bottom-color: var(--accent); }
    .ios-tab:hover:not(.active) { color: var(--text-heading); }
    .form-control { padding: 10px 14px; border: 1px solid var(--panel-border); border-radius: 8px; background: var(--app-bg); width: 100%; color: var(--text-heading); font-size: 14px; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; }
'''
content = content.replace('/* Org Drawer Styling */', css_tabs + '\n    /* Org Drawer Styling */')


with open('resources/views/organization/index.blade.php', 'w') as f:
    f.write(content)

print("Updated index.blade.php successfully")
