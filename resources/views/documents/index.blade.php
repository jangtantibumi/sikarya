<div class="documents-module">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
        <div>
            <h2 style="margin: 0; font-size: 24px;">Documents & E-Sign</h2>
            <p style="margin: 4px 0 0; color: var(--text-muted);">Manage certificates, templates, and digital signatures.</p>
        </div>
        <div style="display: flex; gap: 10px;" id="doc-actions-container">
            <!-- Buttons injected via JS -->
        </div>
    </div>

    <!-- Stats -->
    <div class="kpi-grid" style="margin-bottom: 24px;">
        <div class="kpi-card">
            <div class="kpi-label">Total Documents</div>
            <div class="kpi-value" id="stat-total">0</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Pending Signatures</div>
            <div class="kpi-value" id="stat-pending">0</div>
        </div>
        <div class="kpi-card">
            <div class="kpi-label">Revoked</div>
            <div class="kpi-value" id="stat-revoked">0</div>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="card">
        <table class="data-table" style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="border-bottom: 1px solid var(--border);">
                    <th style="text-align: left; padding: 12px;">Document No.</th>
                    <th style="text-align: left; padding: 12px;">Title</th>
                    <th style="text-align: left; padding: 12px;">Recipient</th>
                    <th style="text-align: left; padding: 12px;">Status</th>
                    <th style="text-align: right; padding: 12px;">Actions</th>
                </tr>
            </thead>
            <tbody id="documents-table-body">
                <tr><td colspan="5" style="text-align: center; padding: 20px;">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modals -->

<!-- Upload Template Modal -->
<div id="doc-template-modal" class="modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:999; align-items:center; justify-content:center;">
    <div class="modal-content card" style="width: 400px; margin: 10vh auto; background: white;">
        <h3>Upload Template</h3>
        <form id="doc-template-form" onsubmit="submitDocTemplate(event)">
            <div style="margin-bottom: 16px;">
                <label>Template Name</label>
                <input type="text" name="name" required style="width: 100%; padding: 8px;" class="input-field">
            </div>
            <div style="margin-bottom: 16px;">
                <label>Background Image (JPG/PNG)</label>
                <input type="file" name="background" accept="image/png, image/jpeg" required style="width: 100%; padding: 8px;">
            </div>
            <div style="text-align: right; gap: 10px; display: flex; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('doc-template-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn">Upload</button>
            </div>
        </form>
    </div>
</div>

<!-- Setup Signature Modal -->
<div id="doc-signature-modal" class="modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:999; align-items:center; justify-content:center;">
    <div class="modal-content card" style="width: 400px; margin: 10vh auto; background: white;">
        <h3>Setup Signature Profile</h3>
        <p style="font-size: 13px; color: var(--text-muted);">Upload a transparent PNG of your signature.</p>
        <form id="doc-signature-form" onsubmit="submitDocSignature(event)">
            <div style="margin-bottom: 16px;">
                <label>Signature Image (PNG)</label>
                <input type="file" name="signature" accept="image/png" required style="width: 100%; padding: 8px;">
            </div>
            <div style="margin-bottom: 16px;">
                <label style="display: flex; gap: 8px; font-size: 13px;">
                    <input type="checkbox" name="consent" required value="on">
                    I consent to use this image as my legal digital signature within the system.
                </label>
            </div>
            <div style="text-align: right; gap: 10px; display: flex; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('doc-signature-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn">Save Signature</button>
            </div>
        </form>
    </div>
</div>

<!-- Issue Certificate Modal -->
<div id="doc-issue-modal" class="modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:999; align-items:center; justify-content:center;">
    <div class="modal-content card" style="width: 500px; margin: 5vh auto; background: white; max-height: 80vh; overflow-y: auto;">
        <h3>Issue Certificate</h3>
        <form id="doc-issue-form" onsubmit="submitDocIssue(event)">
            <div style="margin-bottom: 16px;">
                <label>Recipient (Employee)</label>
                <select name="owner_user_id" id="doc-select-people" required style="width: 100%; padding: 8px;" class="input-field"></select>
            </div>
            <div style="margin-bottom: 16px;">
                <label>Program Name</label>
                <input type="text" name="program_name" value="Internship Program" required style="width: 100%; padding: 8px;" class="input-field">
            </div>
            <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                <div style="flex:1;">
                    <label>Start Date</label>
                    <input type="date" name="start_date" required style="width: 100%; padding: 8px;" class="input-field">
                </div>
                <div style="flex:1;">
                    <label>End Date</label>
                    <input type="date" name="end_date" required style="width: 100%; padding: 8px;" class="input-field">
                </div>
            </div>
            <div style="margin-bottom: 16px;">
                <label>Issued At</label>
                <input type="date" name="issued_at" required style="width: 100%; padding: 8px;" class="input-field">
            </div>
            <div style="margin-bottom: 16px;">
                <label>Template</label>
                <select name="certificate_template_id" id="doc-select-template" required style="width: 100%; padding: 8px;" class="input-field"></select>
            </div>
            <div style="margin-bottom: 16px;">
                <label>Supervisor (Signer)</label>
                <select name="supervisor_user_id" id="doc-select-signer" required style="width: 100%; padding: 8px;" class="input-field"></select>
            </div>
            <div style="text-align: right; gap: 10px; display: flex; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('doc-issue-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn">Create Draft</button>
            </div>
        </form>
    </div>
</div>

<!-- Revoke Modal -->
<div id="doc-revoke-modal" class="modal" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:999; align-items:center; justify-content:center;">
    <div class="modal-content card" style="width: 400px; margin: 10vh auto; background: white;">
        <h3 style="color: var(--danger)">Revoke Certificate</h3>
        <p style="font-size: 13px; color: var(--text-muted);">This action is irreversible and will invalidate the document publicly.</p>
        <form id="doc-revoke-form" onsubmit="submitDocRevoke(event)">
            <input type="hidden" id="doc-revoke-id">
            <div style="margin-bottom: 16px;">
                <label>Reason for Revocation</label>
                <textarea name="reason" required style="width: 100%; padding: 8px; min-height: 80px;" class="input-field" minlength="10" placeholder="Type reason..."></textarea>
            </div>
            <div style="text-align: right; gap: 10px; display: flex; justify-content: flex-end;">
                <button type="button" class="btn btn-outline" onclick="document.getElementById('doc-revoke-modal').style.display='none'">Cancel</button>
                <button type="submit" class="btn" style="background: var(--danger); border-color: var(--danger); color: white;">Revoke</button>
            </div>
        </form>
    </div>
</div>

<script>
    let docState = {
        can_issue: false,
        can_manage_templates: false,
        signature_profile_configured: false,
        people: [],
        signers: [],
        templates: [],
        documents: []
    };

    function loadDocuments() {
        fetch('/api/documents')
            .then(res => res.json())
            .then(data => {
                docState = data;
                renderDocumentsUI();
            })
            .catch(err => console.error(err));
    }

    function renderDocumentsUI() {
        // Actions
        const actionsContainer = document.getElementById('doc-actions-container');
        let btns = '';
        if (docState.can_issue) {
            btns += <button class="btn" onclick="document.getElementById('doc-issue-modal').style.display='flex'"><i class="fa-solid fa-plus"></i> Issue Certificate</button>;
        }
        if (docState.can_manage_templates) {
            btns += <button class="btn btn-outline" onclick="document.getElementById('doc-template-modal').style.display='flex'"><i class="fa-solid fa-image"></i> Add Template</button>;
        }
        if (!docState.signature_profile_configured) {
            btns += <button class="btn btn-outline" onclick="document.getElementById('doc-signature-modal').style.display='flex'" style="border-color:var(--danger); color:var(--danger)"><i class="fa-solid fa-signature"></i> Setup Signature</button>;
        }
        actionsContainer.innerHTML = btns;

        // Stats
        document.getElementById('stat-total').innerText = docState.documents.length;
        document.getElementById('stat-pending').innerText = docState.documents.filter(d => d.status === 'draft').length;
        document.getElementById('stat-revoked').innerText = docState.documents.filter(d => d.status === 'revoked').length;

        // Table
        const tbody = document.getElementById('documents-table-body');
        if (docState.documents.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No documents found.</td></tr>';
        } else {
            let html = '';
            docState.documents.forEach(doc => {
                let statusBadge = '';
                if (doc.status === 'draft') statusBadge = '<span style="background:var(--warning); color:white; padding:4px 8px; border-radius:4px; font-size:12px;">Draft</span>';
                if (doc.status === 'signed') statusBadge = '<span style="background:var(--success); color:white; padding:4px 8px; border-radius:4px; font-size:12px;">Signed</span>';
                if (doc.status === 'revoked') statusBadge = '<span style="background:var(--danger); color:white; padding:4px 8px; border-radius:4px; font-size:12px;">Revoked</span>';

                let actions = '';
                if (doc.can_sign) {
                    actions += <button class="btn btn-sm" onclick="signDocument()">Sign</button> ;
                }
                if (doc.status === 'signed') {
                    actions += <a href="" target="_blank" class="btn btn-sm btn-outline">View</a> ;
                    actions += <a href="" target="_blank" class="btn btn-sm btn-outline">Verify</a> ;
                }
                if (doc.status === 'signed' && docState.can_issue) {
                    actions += <button class="btn btn-sm btn-outline" style="border-color:var(--danger); color:var(--danger);" onclick="openRevokeModal()">Revoke</button>;
                }

                html += 
                <tr style="border-bottom: 1px solid var(--border);">
                    <td style="padding: 12px; font-family: monospace;"></td>
                    <td style="padding: 12px;"></td>
                    <td style="padding: 12px;"></td>
                    <td style="padding: 12px;"></td>
                    <td style="padding: 12px; text-align: right;"></td>
                </tr>;
            });
            tbody.innerHTML = html;
        }

        // Dropdowns
        if (docState.can_issue) {
            const peopleSelect = document.getElementById('doc-select-people');
            peopleSelect.innerHTML = '<option value="">-- Select Recipient --</option>' + docState.people.map(p => <option value=""> ()</option>).join('');
            
            const signerSelect = document.getElementById('doc-select-signer');
            signerSelect.innerHTML = docState.signers.map(p => <option value=""> ()</option>).join('');

            const templateSelect = document.getElementById('doc-select-template');
            templateSelect.innerHTML = docState.templates.map(p => <option value=""></option>).join('');
            if (docState.templates.length === 0) {
                templateSelect.innerHTML = '<option value="">-- Upload Template First --</option>';
            }
        }
    }

    async function submitDocTemplate(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const res = await fetch('/api/documents/templates', { method: 'POST', body: formData, headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content} });
            if (!res.ok) throw new Error(await res.text());
            alert('Template uploaded!');
            document.getElementById('doc-template-modal').style.display='none';
            e.target.reset();
            loadDocuments();
        } catch (err) { alert('Error: ' + err.message); }
    }

    async function submitDocSignature(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const res = await fetch('/api/documents/signature-profile', { method: 'POST', body: formData, headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content} });
            if (!res.ok) throw new Error(await res.text());
            alert('Signature saved!');
            document.getElementById('doc-signature-modal').style.display='none';
            e.target.reset();
            loadDocuments();
        } catch (err) { alert('Error: ' + err.message); }
    }

    async function submitDocIssue(e) {
        e.preventDefault();
        const formData = new FormData(e.target);
        try {
            const res = await fetch('/api/documents/internship-certificates', { method: 'POST', body: formData, headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content} });
            if (!res.ok) throw new Error(await res.text());
            alert('Certificate Draft Created!');
            document.getElementById('doc-issue-modal').style.display='none';
            e.target.reset();
            loadDocuments();
        } catch (err) { alert('Error: ' + err.message); }
    }

    async function signDocument(id) {
        if (!confirm('Are you sure you want to digitally sign this document?')) return;
        try {
            const res = await fetch(/api/documents//sign, { method: 'POST', headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content} });
            if (!res.ok) throw new Error(await res.text());
            alert('Signed Successfully!');
            loadDocuments();
        } catch (err) { alert('Error: ' + err.message); }
    }

    function openRevokeModal(id) {
        document.getElementById('doc-revoke-id').value = id;
        document.getElementById('doc-revoke-modal').style.display = 'flex';
    }

    async function submitDocRevoke(e) {
        e.preventDefault();
        const id = document.getElementById('doc-revoke-id').value;
        const formData = new FormData(e.target);
        try {
            const res = await fetch(/api/documents//revoke, { method: 'POST', body: formData, headers: {'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content} });
            if (!res.ok) throw new Error(await res.text());
            alert('Document Revoked!');
            document.getElementById('doc-revoke-modal').style.display='none';
            e.target.reset();
            loadDocuments();
        } catch (err) { alert('Error: ' + err.message); }
    }

    // Call loadDocuments when the view is switched to documents
    const originalSwitchView = window.switchView;
    window.switchView = function(viewId) {
        if (originalSwitchView) originalSwitchView(viewId);
        if (viewId === 'documents') loadDocuments();
    }
</script>
