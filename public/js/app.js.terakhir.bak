document.addEventListener('DOMContentLoaded', () => {
    // ================= State Management & Persistence =================
    const CURRENT_VERSION = '1.9.0';
    const JAKARTA_TIMEZONE = 'Asia/Jakarta';
    const SESSION_MAX_AGE_MS = 7 * 24 * 60 * 60 * 1000;
    let realtimeSyncTimer = null;
    let realtimeSyncBusy = false;
    let chatRealtimeTimer = null;
    let chatRealtimeBusy = false;
    let liveClockTimer = null;
    let serverClockOffsetMs = 0;
    let kpiPlanDraft = [];
    let attendanceActiveFilter = 'today';
    let geminiConversation = [];
    let geminiStatusCache = null;
    let featureStates = Object.assign({}, window.ERP_FEATURES || {});
    let organizationSearchTerm = '';
    let organizationDivisionFilter = 'all';
    let selectedAlumniRecipientIds = new Set();

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    }

    async function apiRequest(url, options = {}) {
        const headers = Object.assign({
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken()
        }, options.headers || {});

        if (options.body && typeof options.body !== 'string' && !(options.body instanceof FormData)) {
            headers['Content-Type'] = 'application/json';
            options.body = JSON.stringify(options.body);
        }

        const response = await fetch(url, Object.assign({}, options, {
            headers,
            credentials: 'same-origin'
        }));

        const contentType = response.headers.get('content-type') || '';
        const payload = contentType.includes('application/json') ? await response.json() : null;

        if (payload?.server_time) {
            syncServerClock(payload.server_time);
        }

        if (!response.ok) {
            const validationMessage = payload?.errors
                ? Object.values(payload.errors).flat().join(' ')
                : null;
            const error = new Error(validationMessage || payload?.message || payload?.error || `Server merespons ${response.status}.`);
            error.status = response.status;
            error.payload = payload;
            if (response.status === 423 && payload?.code === 'ERP_GATE_REQUIRED') {
                window.location.assign('/erp-access');
            } else if (response.status === 401 && currentUser) {
                endLocalSession(payload?.message || 'Sesi Anda telah berakhir. Silakan masuk kembali.');
            }
            throw error;
        }

        return payload;
    }

    function formatDateJakarta(date = new Date()) {
        const parts = new Intl.DateTimeFormat('en-GB', {
            timeZone: JAKARTA_TIMEZONE,
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        }).formatToParts(date).reduce((result, part) => {
            result[part.type] = part.value;
            return result;
        }, {});

        return `${parts.year}-${parts.month}-${parts.day}`;
    }

    function todayJakarta() {
        return formatDateJakarta(authoritativeNow());
    }

    function addDaysJakarta(days) {
        return formatDateJakarta(new Date(authoritativeNow().getTime() + days * 86400000));
    }

    function authoritativeNow() {
        return new Date(Date.now() + serverClockOffsetMs);
    }

    function syncServerClock(serverTime) {
        const parsed = Date.parse(serverTime);
        if (!Number.isNaN(parsed)) {
            serverClockOffsetMs = parsed - Date.now();
            updateLiveClock();
        }
    }

    function updateLiveClock() {
        const clock = document.getElementById('server-live-clock');
        if (!clock) return;

        const display = new Intl.DateTimeFormat('id-ID', {
            timeZone: JAKARTA_TIMEZONE,
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        }).format(authoritativeNow()).replace(/\./g, ':');

        clock.innerHTML = `<i class="ph ph-clock"></i> ${display} WIB`;
        clock.title = 'Waktu resmi server, diperbarui setiap detik';
    }

    function startLiveClock() {
        if (liveClockTimer) clearInterval(liveClockTimer);
        updateLiveClock();
        liveClockTimer = setInterval(updateLiveClock, 1000);
    }

    function divisionFromRole(role = '') {
        if (role.includes('marketing')) return 'marketing';
        if (role.includes('ops')) return 'operasional';
        if (role.includes('finance')) return 'finance';
        if (role.includes('hrd') || role.includes('hr')) return 'hrd';
        return null;
    }

    function escapeHtml(value = '') {
        const element = document.createElement('div');
        element.textContent = String(value ?? '');
        return element.innerHTML;
    }

    const targetFeatureMap = {
        ceo: 'core_dashboard',
        hierarchy: 'organization',
        dashboard: 'crm',
        kanban: 'crm',
        'ops-dashboard': 'performance',
        'ops-staff': 'performance',
        'finance-dashboard': 'finance',
        'finance-staff': 'finance',
        'kpi-tasks': 'performance',
        resignation: 'resignation',
        hrd: 'hr_core',
        attendance: 'attendance',
        approval: 'approvals',
        setup: 'performance',
        talent: 'talent_management',
        analytics: 'advanced_analytics',
        documents: 'document_management',
        accounting: 'accounting',
        'project-costing': 'project_costing'
    };

    function isFeatureEnabled(feature) {
        return !feature || featureStates[feature] !== false;
    }

    function featureForTarget(target) {
        return targetFeatureMap[target] || null;
    }

    function applyFeatureVisibility() {
        document.querySelectorAll('.nav-item[data-target]').forEach(item => {
            const target = item.getAttribute('data-target');
            const feature = featureForTarget(target);
            const ceoControlException = target === 'setup' && currentUser?.role === 'ceo';
            item.style.display = isFeatureEnabled(feature) || ceoControlException ? '' : 'none';
        });

        const elementFeatures = [
            ['#chat-toggle-btn', 'chat', true],
            ['#chat-panel', 'chat', false],
            ['#chat-overlay', 'chat', false],
            ['#ai-floating-btn', 'gemini', true],
            ['#ai-panel', 'gemini', false],
            ['#backup-data-btn', 'backup', true]
        ];
        elementFeatures.forEach(([selector, feature, restoreWhenEnabled]) => {
            const element = document.querySelector(selector);
            if (!element) return;

            if (!isFeatureEnabled(feature)) {
                element.style.display = 'none';
            } else if (restoreWhenEnabled) {
                element.style.display = '';
            }
        });
    }

    function renderErpFeatureList(features = []) {
        const container = document.getElementById('erp-feature-list');
        if (!container) return;

        const categories = features.reduce((groups, feature) => {
            const category = feature.category || 'Lainnya';
            if (!groups[category]) groups[category] = [];
            groups[category].push(feature);
            return groups;
        }, {});

        container.innerHTML = Object.entries(categories).map(([category, items]) => `
            <section class="feature-category">
                <h4 class="feature-category-title">${escapeHtml(category)}</h4>
                ${items.map(feature => `
                    <div class="erp-feature-row ${feature.available ? '' : 'unavailable'}">
                        <div class="erp-feature-copy">
                            <b>
                                ${escapeHtml(feature.label)}
                                ${feature.available ? '' : '<span class="roadmap-pill">ROADMAP</span>'}
                            </b>
                            <small>${escapeHtml(feature.description)}</small>
                        </div>
                        <label class="feature-switch" title="${feature.locked ? 'Modul fondasi wajib aktif' : (feature.available ? 'Aktifkan atau nonaktifkan modul' : 'Belum tersedia')}">
                            <input
                                type="checkbox"
                                data-feature-key="${escapeHtml(feature.key)}"
                                ${feature.enabled ? 'checked' : ''}
                                ${feature.locked || !feature.available ? 'disabled' : ''}
                            >
                            <span></span>
                        </label>
                    </div>
                `).join('')}
            </section>
        `).join('');

        container.querySelectorAll('input[data-feature-key]').forEach(toggle => {
            toggle.addEventListener('change', async () => {
                const featureKey = toggle.dataset.featureKey;
                const enabled = toggle.checked;
                toggle.disabled = true;

                try {
                    const result = await apiRequest(`/api/admin/features/${encodeURIComponent(featureKey)}`, {
                        method: 'PUT',
                        body: { enabled }
                    });
                    featureStates = Object.assign({}, result.feature_states || featureStates);
                    window.ERP_FEATURES = featureStates;
                    applyFeatureVisibility();
                    showPremiumNotice('Konfigurasi Modul Diperbarui', escapeHtml(result.message), { variant: 'success' });
                    await loadErpControlCenter(true);
                } catch (error) {
                    toggle.checked = !enabled;
                    toggle.disabled = false;
                    showPremiumNotice('Perubahan Ditolak', escapeHtml(error.message), { variant: 'danger' });
                }
            });
        });
    }

    function populateSecurityControls(security = {}) {
        const otp = security.otp || {};
        const gate = security.access_gate || {};
        const mail = security.mail || {};
        const assignments = [
            ['security-otp-expiry', otp.expires_minutes],
            ['security-otp-resend', otp.resend_seconds],
            ['security-otp-attempts', otp.max_attempts],
            ['security-otp-lock', otp.lock_minutes],
            ['security-gate-hours', gate.session_hours],
            ['security-mail-host', mail.host || 'smtp.gmail.com'],
            ['security-mail-port', mail.port || 587],
            ['security-mail-scheme', mail.scheme || 'smtp'],
            ['security-mail-username', mail.username],
            ['security-mail-from-address', mail.from_address],
            ['security-mail-from-name', mail.from_name || 'Suba Arch ERP']
        ];
        assignments.forEach(([id, value]) => {
            const input = document.getElementById(id);
            if (input && value !== undefined) input.value = value;
        });

        const gateEnabled = document.getElementById('security-gate-enabled');
        if (gateEnabled) {
            gateEnabled.checked = Boolean(gate.enabled);
            gateEnabled.dataset.configured = gate.configured ? '1' : '0';
        }

        const mailPassword = document.getElementById('security-mail-password');
        if (mailPassword) {
            mailPassword.value = '';
            mailPassword.required = !mail.password_configured;
            mailPassword.placeholder = mail.password_configured
                ? 'Sudah tersimpan â€” kosongkan bila tidak diganti'
                : 'Masukkan App Password Gmail 16 karakter';
        }

        const readiness = document.getElementById('erp-mail-readiness');
        if (readiness) {
            readiness.classList.toggle('ready', Boolean(otp.mail_ready));
            readiness.innerHTML = otp.mail_ready
                ? `<i class="ph ph-check-circle"></i> Email provider siap mengirim OTP asli${mail.username ? ` melalui <b>${escapeHtml(mail.username)}</b>` : ''}.`
                : `<i class="ph ph-warning-circle"></i> Driver email saat ini: <b>${escapeHtml(otp.mail_driver || 'belum diatur')}</b>. Atur SMTP Hostinger sebelum sistem digunakan secara online. Pada localhost, OTP tercatat di log server.`;
        }
    }

    function formatStorageBytes(bytes = 0) {
        const value = Number(bytes) || 0;
        if (value < 1024) return `${value} B`;
        const units = ['KB', 'MB', 'GB', 'TB'];
        let size = value / 1024;
        let unitIndex = 0;
        while (size >= 1024 && unitIndex < units.length - 1) {
            size /= 1024;
            unitIndex++;
        }
        return `${size.toLocaleString('id-ID', { maximumFractionDigits: 1 })} ${units[unitIndex]}`;
    }

    function populateRetentionControls(retention = {}) {
        const policy = retention.policy || {};
        const summary = retention.summary || {};
        const assignments = [
            ['retention-archive-days', policy.archive_inactive_days],
            ['retention-anonymize-days', policy.anonymize_inactive_days],
            ['retention-purge-days', policy.purge_soft_deleted_days],
            ['retention-storage-warning', policy.storage_warning_mb]
        ];
        assignments.forEach(([id, value]) => {
            const input = document.getElementById(id);
            if (input && value !== undefined) input.value = value;
        });

        const autoAnonymize = document.getElementById('retention-auto-anonymize');
        const autoPurge = document.getElementById('retention-auto-purge');
        if (autoAnonymize) autoAnonymize.checked = Boolean(policy.auto_anonymize);
        if (autoPurge) autoPurge.checked = Boolean(policy.auto_purge);

        const summaryContainer = document.getElementById('retention-summary');
        if (summaryContainer) {
            const metrics = [
                ['Akun aktif', summary.active_users || 0],
                ['Akun nonaktif', summary.inactive_users || 0],
                ['Sudah diarsipkan', summary.archived_users || 0],
                ['Legal hold', summary.legal_hold_users || 0],
                ['Data soft-delete', summary.soft_deleted_records || 0],
                ['Penyimpanan', formatStorageBytes(summary.storage_bytes)]
            ];
            summaryContainer.innerHTML = metrics.map(([label, value]) => `
                <div class="retention-metric">
                    <b>${escapeHtml(value)}</b>
                    <small title="${escapeHtml(label)}">${escapeHtml(label)}</small>
                </div>
            `).join('');
        }

        const storageStatus = document.getElementById('retention-storage-status');
        if (storageStatus) {
            const warningLimit = Number(policy.storage_warning_mb || 0);
            storageStatus.classList.toggle('ready', !summary.storage_warning);
            storageStatus.innerHTML = summary.storage_warning
                ? `<i class="ph ph-warning-circle"></i> Penyimpanan telah melewati batas peringatan <b>${warningLimit.toLocaleString('id-ID')} MB</b>. Tinjau lampiran besar dan jalankan retensi.`
                : `<i class="ph ph-check-circle"></i> Penyimpanan terpantau aman: <b>${escapeHtml(formatStorageBytes(summary.storage_bytes))}</b> dari batas peringatan ${warningLimit.toLocaleString('id-ID')} MB.`;
        }

        const lastRun = document.getElementById('retention-last-run');
        if (lastRun) {
            const metrics = retention.last_run?.metrics || {};
            lastRun.innerHTML = retention.last_run
                ? `Proses terakhir: <b>${escapeHtml(new Date(retention.last_run.completed_at).toLocaleString('id-ID', { timeZone: JAKARTA_TIMEZONE }))} WIB</b> Â· Arsip ${Number(metrics.archived || 0)} Â· Anonim ${Number(metrics.anonymized || 0)} Â· Dihapus permanen ${Number(metrics.purged || 0)}`
                : 'Belum ada proses retensi yang tercatat.';
        }
    }

    async function loadErpControlCenter(silent = false) {
        const featureCard = document.getElementById('erp-feature-control-card');
        const securityCard = document.getElementById('erp-security-control-card');
        const retentionCard = document.getElementById('erp-retention-control-card');
        const isCEO = currentUser?.role === 'ceo';

        if (featureCard) featureCard.style.display = isCEO ? 'block' : 'none';
        if (securityCard) securityCard.style.display = isCEO ? 'block' : 'none';
        if (retentionCard) retentionCard.style.display = isCEO ? 'block' : 'none';
        if (!isCEO) return;

        try {
            const result = await apiRequest('/api/admin/control-center');
            renderErpFeatureList(result.features || []);
            populateSecurityControls(result.security || {});
            populateRetentionControls(result.retention || {});
        } catch (error) {
            if (!silent) {
                showPremiumNotice('Control Center Tidak Tersedia', escapeHtml(error.message), { variant: 'danger' });
            }
        }
    }

    function initializeErpControlCenterHandlers() {
        const securityForm = document.getElementById('erp-security-policy-form');
        const passwordForm = document.getElementById('erp-gate-password-form');
        const mailForm = document.getElementById('erp-mail-settings-form');
        const retentionForm = document.getElementById('erp-retention-policy-form');
        const runRetentionButton = document.getElementById('btn-run-retention');

        mailForm?.addEventListener('submit', async event => {
            event.preventDefault();
            const button = mailForm.querySelector('button[type="submit"]');
            const original = button?.innerHTML;
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Menyimpan SMTP...';
            }

            try {
                const result = await apiRequest('/api/admin/security/mail', {
                    method: 'PUT',
                    body: {
                        host: document.getElementById('security-mail-host')?.value?.trim(),
                        port: Number(document.getElementById('security-mail-port')?.value),
                        scheme: document.getElementById('security-mail-scheme')?.value,
                        username: document.getElementById('security-mail-username')?.value?.trim(),
                        password: document.getElementById('security-mail-password')?.value || null,
                        from_address: document.getElementById('security-mail-from-address')?.value?.trim(),
                        from_name: document.getElementById('security-mail-from-name')?.value?.trim()
                    }
                });
                populateSecurityControls(result.security || {});
                showPremiumNotice('SMTP Tersimpan', escapeHtml(result.message), { variant: 'success' });
            } catch (error) {
                showPremiumNotice('SMTP Ditolak', escapeHtml(error.message), { variant: 'danger' });
            } finally {
                if (button) {
                    button.disabled = false;
                    button.innerHTML = original;
                }
            }
        });

        securityForm?.addEventListener('submit', async event => {
            event.preventDefault();
            const button = securityForm.querySelector('button[type="submit"]');
            const original = button?.innerHTML;
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Menyimpan...';
            }

            try {
                const result = await apiRequest('/api/admin/security', {
                    method: 'PUT',
                    body: {
                        otp_expires_minutes: Number(document.getElementById('security-otp-expiry')?.value),
                        otp_resend_seconds: Number(document.getElementById('security-otp-resend')?.value),
                        otp_max_attempts: Number(document.getElementById('security-otp-attempts')?.value),
                        otp_lock_minutes: Number(document.getElementById('security-otp-lock')?.value),
                        gate_enabled: Boolean(document.getElementById('security-gate-enabled')?.checked),
                        gate_session_hours: Number(document.getElementById('security-gate-hours')?.value)
                    }
                });
                populateSecurityControls(result.security || {});
                showPremiumNotice('Kebijakan Tersimpan', escapeHtml(result.message), { variant: 'success' });
            } catch (error) {
                showPremiumNotice('Gagal Menyimpan', escapeHtml(error.message), { variant: 'danger' });
            } finally {
                if (button) {
                    button.disabled = false;
                    button.innerHTML = original;
                }
            }
        });

        passwordForm?.addEventListener('submit', async event => {
            event.preventDefault();
            const password = document.getElementById('security-gate-password')?.value || '';
            const confirmation = document.getElementById('security-gate-password-confirmation')?.value || '';
            if (password !== confirmation) {
                showPremiumNotice('Password Tidak Sama', 'Konfirmasi password harus sama dengan password baru.', { variant: 'danger' });
                return;
            }

            const button = passwordForm.querySelector('button[type="submit"]');
            const original = button?.innerHTML;
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Mengamankan...';
            }

            try {
                const result = await apiRequest('/api/admin/security/gate-password', {
                    method: 'PUT',
                    body: {
                        password,
                        password_confirmation: confirmation,
                        enable_gate: Boolean(document.getElementById('security-gate-enable-now')?.checked)
                    }
                });
                passwordForm.reset();
                const enableNow = document.getElementById('security-gate-enable-now');
                if (enableNow) enableNow.checked = true;
                populateSecurityControls(result.security || {});
                showPremiumNotice('Portal Berhasil Diamankan', escapeHtml(result.message), { variant: 'success' });
            } catch (error) {
                showPremiumNotice('Password Ditolak', escapeHtml(error.message), { variant: 'danger' });
            } finally {
                if (button) {
                    button.disabled = false;
                    button.innerHTML = original;
                }
            }
        });

        retentionForm?.addEventListener('submit', async event => {
            event.preventDefault();
            const button = retentionForm.querySelector('button[type="submit"]');
            const original = button?.innerHTML;
            if (button) {
                button.disabled = true;
                button.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Menyimpan...';
            }

            try {
                const result = await apiRequest('/api/admin/retention', {
                    method: 'PUT',
                    body: {
                        archive_inactive_days: Number(document.getElementById('retention-archive-days')?.value),
                        anonymize_inactive_days: Number(document.getElementById('retention-anonymize-days')?.value),
                        auto_anonymize: Boolean(document.getElementById('retention-auto-anonymize')?.checked),
                        purge_soft_deleted_days: Number(document.getElementById('retention-purge-days')?.value),
                        auto_purge: Boolean(document.getElementById('retention-auto-purge')?.checked),
                        storage_warning_mb: Number(document.getElementById('retention-storage-warning')?.value)
                    }
                });
                populateRetentionControls(result.retention || {});
                showPremiumNotice('Kebijakan Retensi Tersimpan', escapeHtml(result.message), { variant: 'success' });
            } catch (error) {
                showPremiumNotice('Kebijakan Ditolak', escapeHtml(error.message), { variant: 'danger' });
            } finally {
                if (button) {
                    button.disabled = false;
                    button.innerHTML = original;
                }
            }
        });

        runRetentionButton?.addEventListener('click', async () => {
            const original = runRetentionButton.innerHTML;
            runRetentionButton.disabled = true;
            runRetentionButton.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Memproses...';

            try {
                const result = await apiRequest('/api/admin/retention/run', { method: 'POST' });
                populateRetentionControls(result.retention || {});
                const metrics = result.metrics || {};
                showPremiumNotice(
                    'Retensi Selesai',
                    `Akun diarsipkan: ${Number(metrics.archived || 0)}, data dianonimkan: ${Number(metrics.anonymized || 0)}, data operasional dihapus permanen: ${Number(metrics.purged || 0)}.`,
                    { variant: 'success' }
                );
            } catch (error) {
                showPremiumNotice('Retensi Gagal', escapeHtml(error.message), { variant: 'danger' });
            } finally {
                runRetentionButton.disabled = false;
                runRetentionButton.innerHTML = original;
            }
        });
    }
    const defaultState = {
        version: CURRENT_VERSION,
        users: {
            'ceo': { name: 'Super Admin', username: 'ceo', role: 'ceo', level: 'Level 1 - CEO', parent: null, avatar: 'CEO', title: 'CEO & Founder' },
            'mgr_marketing': { name: 'Maulana Mkt', username: 'mgr_marketing', role: 'mgr_marketing', level: 'Level 2 - Manager', parent: 'ceo', avatar: 'MM', title: 'Marketing Manager' },
            'maulana': { name: 'M. Maulana Zakaria', username: 'maulana', role: 'staff_marketing', level: 'Level 3 - Staff', parent: 'mgr_marketing', avatar: 'MZ', title: 'Web Developer' },
            'dbest': { name: 'D BEST AR', username: 'dbest', role: 'staff_marketing', level: 'Level 3 - Staff', parent: 'mgr_marketing', avatar: 'DB', title: 'Content Creator' },
            'mgr_ops': { name: 'Reza Ops', username: 'mgr_ops', role: 'mgr_ops', level: 'Level 2 - Manager', parent: 'ceo', avatar: 'MO', title: 'Operations Manager' },
            'staff_ops': { name: 'Budi Ops', username: 'staff_ops', role: 'staff_ops', level: 'Level 3 - Staff', parent: 'mgr_ops', avatar: 'SO', title: 'Operations Staff' },
            'mgr_finance': { name: 'Hendra Fin', username: 'mgr_finance', role: 'mgr_finance', level: 'Level 2 - Manager', parent: 'ceo', avatar: 'MF', title: 'Finance Manager' },
            'staff_finance': { name: 'Siti Fin', username: 'staff_finance', role: 'staff_finance', level: 'Level 3 - Staff', parent: 'mgr_finance', avatar: 'SF', title: 'Finance Staff' },
            'sonia': { name: 'Sonia HRD', username: 'sonia', role: 'mgr_hrd', level: 'Level 2 - Manager', parent: 'ceo', avatar: 'SH', title: 'HR Manager' }
        },
        tasks: [
            { id: 'task-1', username: 'ceo', title: 'Approve Budget Plan Q3', status: 'done', deadline: '2026-07-20T17:00:00Z', relation: 'CEO Strategic' },
            { id: 'task-2', username: 'mgr_marketing', title: 'Review Tiktok Campaign Plan', status: 'in_progress', deadline: '2026-07-18T17:00:00Z', relation: 'Marketing Campaign' },
            { id: 'task-3', username: 'maulana', title: 'High-Ticket Leads Optimization', status: 'done', deadline: '2026-07-14T17:00:00Z', relation: 'SEO Ads', evidence: 'https://prnt.sc/T64Kq_Ex-yO_' },
            { id: 'task-4', username: 'maulana', title: 'Publish & Optimasi Artikel Niche', status: 'revisi', deadline: '2026-07-15T12:00:00Z', relation: 'SEO Content', evidence: '', feedback: 'Konten artikel kurang relevan dengan niche arsitektur, tolong perbaiki bagian pendahuluannya.' },
            { id: 'task-5', username: 'maulana', title: 'Migrasi Post/URL Website Lama', status: 'done', deadline: '2026-07-12T17:00:00Z', relation: 'System Maintenance', evidence: 'https://docs.google.com/spreadsheets/d/1...' },
            { id: 'task-6', username: 'dbest', title: 'Video Output Consistency (Tiktok)', status: 'done', deadline: '2026-07-13T17:00:00Z', relation: 'Social Media', evidence: 'https://tiktok.com/@suba-arch/video/1' },
            { id: 'task-7', username: 'dbest', title: 'Audience Retention Improvement', status: 'failed', deadline: '2026-07-10T17:00:00Z', relation: 'Social Media Analysis', evidence: '' },
            { id: 'task-8', username: 'mgr_ops', title: 'Site Survey Villa Puncak', status: 'in_progress', deadline: '2026-07-19T17:00:00Z', relation: 'Client Project' },
            { id: 'task-9', username: 'staff_ops', title: 'Upload Denah Villa SCBD', status: 'in_progress', deadline: '2026-07-16T17:00:00Z', relation: 'Stage-Gate 1' },
            { id: 'task-10', username: 'mgr_finance', title: 'Approve Invoice Vendor Pembangunan', status: 'done', deadline: '2026-07-14T17:00:00Z', relation: 'Vendor Relations' },
            { id: 'task-11', username: 'staff_finance', title: 'Payroll Disbursement Process', status: 'nearing_deadline', deadline: '2026-07-16T23:59:00Z', relation: 'HR Finance' }
        ],
        attendance: [
            { username: 'maulana', status: 'Present', time: '08:15 AM', timeOut: '05:30 PM', date: '2026-07-15', lat: -6.12, lng: 102.13, type: 'WFO' },
            { username: 'dbest', status: 'Present', time: '09:42 AM', timeOut: '06:00 PM', date: '2026-07-15', lat: -6.15, lng: 102.18, type: 'WFH' }
        ],
        kpiProposals: [],
        leads: [
            { id: 'lead-1', name: 'Bpk. Budi - Villa Puncak', budget: 'Est: Rp 1.5M', source: 'IG DM', type: 'Pembangunan', column: 'leads', date: 'Hari ini' },
            { id: 'lead-2', name: 'Ibu Rina - Renovasi', budget: 'Est: Rp 200Jt', source: 'WhatsApp', type: 'Desain', column: 'leads', date: 'Kemarin' },
            { id: 'lead-3', name: 'PT. Sejahtera - Ruko', budget: 'Est: Rp 3M', source: 'Website', type: 'Pembangunan', column: 'penawaran', date: '2 hari lalu' },
            { id: 'lead-4', name: 'Keluarga Bapak Andi', budget: 'Deal: Rp 10.8M', source: 'Referensi', type: 'Desain', column: 'deal', date: 'Jan 15, 2026' }
        ],
        projectProgress: {
            villaScbd: 10
        },
        leaveRequests: [
            { id: 'leave-1', username: 'maulana', name: 'M. Maulana Zakaria', type: 'Sakit (Sick Leave)', startDate: '2026-07-20', endDate: '2026-07-21', reason: 'Demam tinggi & butuh istirahat dokter', status: 'pending', approver: 'mgr_marketing' }
        ],
        calendarOverrides: [],
        dPointRates: {},
        divisionGoalComments: [],
        notifications: []
    };

    let state = JSON.parse(localStorage.getItem('erpState')) || defaultState;
    if (!state.version || state.version !== CURRENT_VERSION) {
        console.log("State version mismatch (old cache detected). Resetting or migrating state...");
        state = Object.assign({}, defaultState, state);
        state.version = CURRENT_VERSION;
        if (!state.calendarOverrides) state.calendarOverrides = [];
        if (!state.dPointRates) state.dPointRates = {};
        if (!state.divisionGoalComments) state.divisionGoalComments = [];
        if (!state.notifications) state.notifications = [];
        localStorage.setItem('erpState', JSON.stringify(state));
    }
    if (!localStorage.getItem('erpState')) {
        localStorage.setItem('erpState', JSON.stringify(state));
    }
    // Workflow data is server-authoritative. Never render stale demonstration data.
    state.tasks = [];
    state.attendance = [];
    state.leads = [];
    state.crmOverview = {
        summary: {
            total_leads: 0,
            open_leads: 0,
            won_leads: 0,
            lost_leads: 0,
            whatsapp_leads: 0,
            due_follow_ups: 0,
            conversion_rate: 0,
            pipeline_value: 0,
            weighted_forecast: 0,
            actual_revenue: 0
        },
        sources: [],
        funnel: [],
        due_follow_ups: []
    };
    state.whatsappIntegration = {
        inbound_configured: false,
        outbound_configured: false,
        fully_configured: false,
        graph_version: null,
        callback_url: null
    };
    state.leaveRequests = [];
    state.kpiProposals = [];
    state.chatMessages = [];
    state.chatChannels = [];
    state.resignationRequests = [];
    state.organizationChart = state.organizationChart || {
        people: {},
        summary: { active_people: 0, divisions: 0 },
        viewer: {},
    };

    function updateState(newState) {
        state = newState;
        localStorage.setItem('erpState', JSON.stringify(state));
        if (currentUser) {
            renderAll();
        }
    }

    function populateKPISetupRoles() {
        const select = document.getElementById('kpi-setup-role-select');
        if (!select || !currentUser) return;
        
        select.innerHTML = '';
        if (currentUser.role === 'ceo') {
            select.innerHTML = `
                <option value="mgr_marketing">Marketing Manager</option>
                <option value="mgr_ops">Operations Manager</option>
                <option value="mgr_finance">Finance Manager</option>
                <option value="mgr_hrd">HR Manager</option>
            `;
        } else if (currentUser.role.startsWith('mgr_')) {
            select.innerHTML += `<option value="${currentUser.username}">${currentUser.name} (Diri Sendiri)</option>`;
            Object.values(state.users).forEach(u => {
                if (u.parent === currentUser.username) {
                    select.innerHTML += `<option value="${u.username}">${u.name} (@${u.username})</option>`;
                }
            });
            if (select.children.length === 0) {
                select.innerHTML = '<option value="">Tidak ada staf di divisi Anda</option>';
            }
        }
    }

    // ================= UI Elements and Navigation =================
    const navItems = document.querySelectorAll('.nav-item');
    const viewSections = document.querySelectorAll('.view-section');

    function switchView(targetId) {
        const requiredFeature = featureForTarget(targetId);
        const ceoControlException = targetId === 'setup' && currentUser?.role === 'ceo';
        const requestedNav = document.querySelector(`.nav-item[data-target="${targetId}"]`);
        const roleDenied = requestedNav?.classList.contains('hidden-role');
        if ((!isFeatureEnabled(requiredFeature) && !ceoControlException) || roleDenied) {
            const fallback = Array.from(document.querySelectorAll('.nav-item[data-target]'))
                .find(item => {
                    const candidate = item.getAttribute('data-target');
                    const feature = featureForTarget(candidate);
                    return item.style.display !== 'none'
                        && !item.classList.contains('hidden-role')
                        && isFeatureEnabled(feature);
                });

            if (!fallback) {
                showPremiumNotice('Modul Dinonaktifkan', 'Tidak ada tampilan aktif yang dapat dibuka untuk akun ini.', { variant: 'danger' });
                return;
            }

            targetId = fallback.getAttribute('data-target');
        }

        navItems.forEach(nav => nav.classList.remove('active'));
        viewSections.forEach(section => section.style.display = 'none');
        
        const activeNav = document.querySelector(`.nav-item[data-target="${targetId}"]`);
        if (activeNav) {
            activeNav.classList.add('active');
            
            // Update Page Title
            const pageTitle = document.getElementById('current-page-title');
            if (pageTitle) {
                const viewName = activeNav.querySelector('span').innerText;
                if (targetId.startsWith('member')) {
                    pageTitle.innerText = viewName + ' Dashboard';
                } else {
                    pageTitle.innerText = viewName;
                }
            }
        }
        
        const targetSection = document.getElementById(`view-${targetId}`);
        if (targetSection) {
            targetSection.style.display = 'block';
        }

        // Auto reset scroll position to top when navigating views
        const mainContentContainer = document.querySelector('.main-content');
        if (mainContentContainer) {
            mainContentContainer.scrollTop = 0;
        }
        window.scrollTo(0, 0);

        // Save active view in localStorage & URL Hash for page reload persistence
        try {
            localStorage.setItem('activeERPView', targetId);
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, null, '#' + targetId);
            } else {
                window.location.hash = targetId;
            }
        } catch (err) {
            console.error("View state save error:", err);
        }

        // View specific triggers
        if (targetId === 'setup') {
            populateKPISetupRoles();
            configureGoalKpiBuilder();
            if (currentUser?.role === 'ceo') {
                loadErpControlCenter();
            }
        } else if (targetId === 'hierarchy') {
            renderOrgChart();
            setTimeout(drawOrgChartConnections, 50);
        } else if (targetId === 'dashboard') {
            renderOmzetChart();
        } else if (targetId === 'kpi-tasks') {
            renderKPITasksView();
        } else if (targetId === 'finance-dashboard' || targetId === 'finance') {
            renderFinanceDashboard();
        } else if (targetId === 'approval') {
            renderCEOApprovalInbox();
        } else if (targetId === 'resignation') {
            renderResignationHistory();
        } else if (targetId === 'alumni') {
            syncAlumniPortal();
        }

        if (window.StrategicERP?.loadView) {
            window.StrategicERP.loadView(targetId);
        }
    }

    navItems.forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            const target = item.getAttribute('data-target');
            if (target) {
                switchView(target);
            }
        });
    });

    // ================= Mobile Drawer Navigation =================
    const mobileHamburgerBtn = document.getElementById('mobile-hamburger-btn');
    const sidebar = document.querySelector('.sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    
    if (mobileHamburgerBtn && sidebar && sidebarOverlay) {
        const toggleSidebar = () => {
            sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
        };
        mobileHamburgerBtn.addEventListener('click', toggleSidebar);
        sidebarOverlay.addEventListener('click', toggleSidebar);
        
        // Close drawer on click nav item (mobile)
        navItems.forEach(item => {
            item.addEventListener('click', () => {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            });
        });
    }

    // ================= Advanced Login & Role checks =================
    const loginOverlay = document.getElementById('login-overlay');
    const loginBtn = document.getElementById('login-btn');
    const usernameInput = document.getElementById('login-username');
    const passwordInput = document.getElementById('login-password');
    const loginError = document.getElementById('login-error');
    const navSections = document.querySelectorAll('.nav-section');
    
    const ceoBypassBadge = document.getElementById('ceo-bypass-badge');
    const ceoManagementTools = document.getElementById('ceo-management-tools');

    const mockUsers = {
        'ceo': { role: 'ceo', pass: 'admin', username: 'ceo' },
        'mgr_marketing': { role: 'mgr_marketing', pass: 'admin', username: 'mgr_marketing' },
        'maulana': { role: 'staff_marketing', pass: 'admin', username: 'maulana' },
        'dbest': { role: 'staff_marketing', pass: 'admin', username: 'dbest' },
        'mgr_ops': { role: 'mgr_ops', pass: 'admin', username: 'mgr_ops' },
        'staff_ops': { role: 'staff_ops', pass: 'admin', username: 'staff_ops' },
        'mgr_finance': { role: 'mgr_finance', pass: 'admin', username: 'mgr_finance' },
        'staff_finance': { role: 'staff_finance', pass: 'admin', username: 'staff_finance' },
        'sonia': { role: 'mgr_hrd', pass: 'admin', username: 'sonia' }
    };

    let currentUser = null;

    function getStoredSession() {
        try {
            const raw = localStorage.getItem('currentUserSession');
            if (!raw) return null;

            const parsed = JSON.parse(raw);
            if (!parsed?.user?.username) return null;

            const loginTime = Number(parsed.loginTime || 0);
            const expiresAt = Number(parsed.expiresAt || (loginTime + SESSION_MAX_AGE_MS));
            if (!loginTime || Date.now() >= expiresAt) return null;

            return Object.assign({}, parsed, { loginTime, expiresAt });
        } catch (error) {
            return null;
        }
    }

    function endLocalSession(message = '') {
        if (realtimeSyncTimer) clearInterval(realtimeSyncTimer);
        if (chatRealtimeTimer) clearInterval(chatRealtimeTimer);
        realtimeSyncTimer = null;
        chatRealtimeTimer = null;
        currentUser = null;
        localStorage.removeItem('currentUserSession');
        document.documentElement.classList.remove('session-restoring');

        if (loginOverlay) {
            loginOverlay.style.display = 'flex';
            loginOverlay.style.opacity = '1';
        }

        if (message && loginError) {
            loginError.innerText = message;
            loginError.style.display = 'block';
        }
    }

    function renderDynamicStaffNavigation() {
        const section = document.getElementById('team-member-nav-section');
        const container = document.getElementById('dynamic-staff-member-navs');
        if (!section || !container || !currentUser) return;

        const isManager = currentUser.role?.startsWith('mgr_');
        const members = isManager
            ? Object.values(state.users || {}).filter(user => user.parent === currentUser.username && user.role?.startsWith('staff_'))
            : [];

        section.style.display = members.length ? 'block' : 'none';
        container.innerHTML = members.map(user => `
            <a href="#" class="nav-item staff-member-nav" data-target="kpi-tasks" data-username="${escapeHtml(user.username)}">
                <div class="member-avatar" style="width: 24px; height: 24px; font-size: 10px;">${escapeHtml(user.avatar || user.username.substring(0, 2).toUpperCase())}</div>
                <span>${escapeHtml(user.title || user.name)}</span>
            </a>
        `).join('');

        container.querySelectorAll('.staff-member-nav').forEach(link => {
            link.addEventListener('click', event => {
                event.preventDefault();
                switchView('kpi-tasks');
                const teamSelect = document.getElementById('team-member-select');
                if (teamSelect && Array.from(teamSelect.options).some(option => option.value === link.dataset.username)) {
                    teamSelect.value = link.dataset.username;
                    teamSelect.dispatchEvent(new Event('change'));
                }
            });
        });
    }

    function applyLogin(user, isInitial = false, sessionMeta = {}) {
        currentUser = user;
        window.ERP_CURRENT_USER = user;
        if (!isInitial) {
            const loginTime = sessionMeta.server_time ? Date.parse(sessionMeta.server_time) : Date.now();
            const expiresAt = sessionMeta.session_expires_at
                ? Date.parse(sessionMeta.session_expires_at)
                : loginTime + SESSION_MAX_AGE_MS;
            const sessionData = {
                user: user,
                loginTime,
                expiresAt
            };
            localStorage.setItem('currentUserSession', JSON.stringify(sessionData));
        }
        const selectedRole = user.role;
        
        const roleLabels = {
            'ceo': 'ðŸ‘‘ CEO (Super Admin)',
            'mgr_marketing': 'ðŸŽ¯ Manager Marketing',
            'staff_marketing': 'ðŸŽ¯ Staff Marketing',
            'mgr_ops': 'ðŸ—ï¸ Manager Operasional',
            'staff_ops': 'ðŸ—ï¸ Staff Operasional',
            'mgr_finance': 'ðŸ’° Manager Finance',
            'staff_finance': 'ðŸ’° Staff Finance',
            'mgr_hrd': 'ðŸ‘¥ HR Manager',
            'staff_hrd': 'ðŸ‘¥ HR Staff'
        };

        const roleTextEl = document.getElementById('sidebar-role-text');
        if (roleTextEl) {
            roleTextEl.innerText = user.title || roleLabels[selectedRole] || selectedRole;
        }

        const subtitleEl = document.getElementById('header-welcome-subtitle');
        if (subtitleEl) {
            subtitleEl.innerText = `Welcome back, ${user.name || user.username}.`;
        }
        
        if (typeof renderChatChannels === 'function') {
            renderChatChannels();
        }

        // RBAC Views filtering
        const allowedRolesMap = {
            'ceo': ['ceo', 'admin'],
            'mgr_marketing': ['marketing', 'admin'],
            'staff_marketing': ['marketing'],
            'mgr_ops': ['ops', 'admin'],
            'staff_ops': ['ops'],
            'mgr_finance': ['finance', 'admin'],
            'staff_finance': ['finance'],
            'mgr_hrd': ['hrd', 'admin'],
            'staff_hrd': ['hrd'],
            'alumni': []
        };

        const allowedTags = allowedRolesMap[selectedRole] || [];

        const navKpiTasks = document.getElementById('nav-kpi-tasks');
        if (navKpiTasks) {
            navKpiTasks.style.display = selectedRole === 'alumni' ? 'none' : 'flex';
        }

        const collabHeader = document.getElementById('nav-section-collab');
        if (collabHeader) {
            collabHeader.style.display = selectedRole === 'alumni' ? 'none' : 'block';
        }

        const resignationNav = document.getElementById('nav-resignation');
        if (resignationNav) {
            resignationNav.style.display = selectedRole === 'ceo' ? 'none' : 'flex';
        }

        const holidayAnnouncementButton = document.getElementById('chat-holiday-announcement-btn');
        if (holidayAnnouncementButton) {
            holidayAnnouncementButton.style.display = (selectedRole === 'ceo' || selectedRole.includes('hrd') || selectedRole === 'hr')
                ? 'inline-flex'
                : 'none';
        }

        navItems.forEach(item => {
            if (selectedRole === 'alumni') {
                item.classList.toggle('hidden-role', item.getAttribute('data-target') !== 'alumni');
                return;
            }
            const roleTag = item.getAttribute('data-role');
            // Strict role tag check for CEO (hides marketing/ops/finance tabs completely)
            if (!roleTag || (selectedRole === 'ceo' && roleTag !== 'ceo' && roleTag !== 'admin')) {
                if (roleTag) {
                    item.classList.add('hidden-role');
                } else {
                    item.classList.remove('hidden-role');
                }
            } else if (allowedTags.includes(roleTag)) {
                item.classList.remove('hidden-role');
            } else {
                item.classList.add('hidden-role');
            }
        });

        document.querySelectorAll('[data-roles], [data-public-nav]').forEach(element => {
            if (element.getAttribute('data-public-nav') === 'true') {
                element.classList.remove('hidden-role');
                return;
            }
            const allowedRoles = (element.getAttribute('data-roles') || '')
                .split(',')
                .map(role => role.trim())
                .filter(Boolean);
            element.classList.toggle('hidden-role', !allowedRoles.includes(selectedRole));
        });

        const dynamicMemberContainer = document.getElementById('dynamic-staff-member-navs');
        const dynamicMemberSection = document.getElementById('team-member-nav-section');
        if (dynamicMemberContainer) dynamicMemberContainer.innerHTML = '';
        if (dynamicMemberSection) dynamicMemberSection.style.display = 'none';

        navSections.forEach(section => {
            const roleTag = section.getAttribute('data-role');
            if (!roleTag) return;
            
            // Check if section contains any visible elements
            if (selectedRole === 'ceo' && roleTag !== 'ceo' && roleTag !== 'admin') {
                section.classList.add('hidden-role');
            } else if (allowedTags.includes(roleTag)) {
                section.classList.remove('hidden-role');
            } else {
                section.classList.add('hidden-role');
            }
        });

        if (selectedRole === 'ceo') {
            if (ceoBypassBadge) ceoBypassBadge.style.display = 'inline-block';
            if (ceoManagementTools) ceoManagementTools.style.display = 'flex';
        } else {
            if (ceoBypassBadge) ceoBypassBadge.style.display = 'none';
            if (ceoManagementTools) ceoManagementTools.style.display = 'none';
        }

        const userManagementCard = document.getElementById('user-management-card');
        if (userManagementCard) {
            userManagementCard.style.display = (selectedRole === 'ceo' || selectedRole === 'mgr_hrd') ? 'block' : 'none';
        }

        const ceoHierarchyActions = document.getElementById('ceo-hierarchy-actions');
        if (ceoHierarchyActions) {
            ceoHierarchyActions.style.display = (selectedRole === 'ceo' || selectedRole.startsWith('mgr_')) ? 'flex' : 'none';
            const btnAddDiv = document.getElementById('btn-hierarchy-add-div');
            const btnAddMgr = document.getElementById('btn-hierarchy-add-mgr');
            if (btnAddDiv) btnAddDiv.style.display = selectedRole === 'ceo' ? 'inline-flex' : 'none';
            if (btnAddMgr) btnAddMgr.style.display = selectedRole === 'ceo' ? 'inline-flex' : 'none';
        }

        const clockInBtn = document.getElementById('univ-clock-in-btn');
        if (clockInBtn) {
            clockInBtn.style.display = (selectedRole === 'ceo' || selectedRole === 'alumni') ? 'none' : 'flex';
        }

        let defaultView = 'dashboard';
        if (selectedRole === 'alumni') defaultView = 'alumni';
        else if (selectedRole === 'ceo') defaultView = 'ceo';
        else if (selectedRole === 'mgr_hrd' || selectedRole === 'staff_hrd') defaultView = 'hrd';
        else if (selectedRole === 'mgr_ops') defaultView = 'ops-dashboard';
        else if (selectedRole === 'staff_ops') defaultView = 'ops-staff';
        else if (selectedRole === 'mgr_finance') defaultView = 'finance-dashboard';
        else if (selectedRole === 'staff_finance') defaultView = 'finance-staff';
        else if (selectedRole === 'staff_marketing') {
            defaultView = user.username === 'maulana' ? 'member-dev' : 'member-creator';
        }

        // Update sidebar user profile card with login details
        if (typeof updateUserProfileUI === 'function') {
            updateUserProfileUI(user);
        }

        // Enforce role-based Kanban New Lead button visibility
        const kanbanNewLeadBtn = document.getElementById('kanban-new-lead-btn');
        if (kanbanNewLeadBtn) {
            if (selectedRole === 'ceo' || selectedRole.includes('marketing') || selectedRole.includes('cs')) {
                kanbanNewLeadBtn.style.display = 'flex';
            } else {
                kanbanNewLeadBtn.style.display = 'none';
            }
        }

        // Check hash or saved active view on page reload
        try {
            const urlHashView = window.location.hash ? window.location.hash.substring(1) : null;
            const savedView = localStorage.getItem('activeERPView');
            const viewToRestore = urlHashView || savedView;

            if (viewToRestore && document.getElementById(`view-${viewToRestore}`)) {
                defaultView = viewToRestore;
            }
        } catch (err) {
            console.error("View restore error:", err);
        }

        applyFeatureVisibility();
        switchView(defaultView);
        renderAll();
        if (selectedRole !== 'alumni') renderChatChannels();

        // Sync data dari MySQL server
        syncDataFromServer();
        startRealtimeSync();
        if (selectedRole !== 'alumni') {
            startChatRealtimeSync();
            startLiveClock();
            checkGeminiStatus();
        }

        document.documentElement.classList.remove('session-restoring');
        if (loginOverlay) {
            loginOverlay.style.opacity = '0';
            loginOverlay.style.display = 'none';
        }
        window.dispatchEvent(new CustomEvent('erp:user-ready', { detail: user }));
    }

    async function syncDataFromServer() {
        if (currentUser?.role === 'alumni') {
            await syncAlumniPortal();
            await syncNotifications();
            state.version = CURRENT_VERSION;
            localStorage.setItem('erpState', JSON.stringify(state));
            renderNotifications();
            return;
        }

        try {
            const leadsRes = await fetch('/api/leads', { headers: { 'Accept': 'application/json' } });
            if (leadsRes.ok) {
                const dbLeads = await leadsRes.json();
                state.leads = Array.isArray(dbLeads) ? dbLeads : [];
            }
            const crmOverviewRes = await fetch('/api/crm/overview', { headers: { 'Accept': 'application/json' } });
            if (crmOverviewRes.ok) {
                state.crmOverview = await crmOverviewRes.json();
            }
            const whatsappStatusRes = await fetch('/api/crm/whatsapp/status', { headers: { 'Accept': 'application/json' } });
            if (whatsappStatusRes.ok) {
                state.whatsappIntegration = await whatsappStatusRes.json();
            }
            const attRes = await fetch('/api/attendance', { headers: { 'Accept': 'application/json' } });
            if (attRes.ok) {
                const attendancePayload = await attRes.json();
                state.attendance = Array.isArray(attendancePayload)
                    ? attendancePayload
                    : (attendancePayload.records || []);
                state.attendanceSummary = attendancePayload.summary || null;
                if (attendancePayload?.server_time) {
                    syncServerClock(attendancePayload.server_time);
                }
            }
            const usersRes = await fetch('/api/users', { headers: { 'Accept': 'application/json' } });
            if (usersRes.ok) {
                const dbUsers = await usersRes.json();
                if (dbUsers && Object.keys(dbUsers).length > 0) {
                    state.users = dbUsers;
                }
            }
            const organizationRes = await fetch('/api/organization-chart', { headers: { 'Accept': 'application/json' } });
            if (organizationRes.ok) {
                const organizationPayload = await organizationRes.json();
                state.organizationChart = organizationPayload || state.organizationChart;

                const privacyNotice = document.querySelector('#organization-privacy-notice span');
                if (privacyNotice && organizationPayload?.privacy_notice) {
                    privacyNotice.textContent = organizationPayload.privacy_notice;
                }
            }
            const tasksRes = await fetch('/api/tasks', { headers: { 'Accept': 'application/json' } });
            if (tasksRes.ok) {
                const dbTasks = await tasksRes.json();
                state.tasks = dbTasks;
            }
            const rulesRes = await fetch('/api/rules', { headers: { 'Accept': 'application/json' } });
            if (rulesRes.ok) {
                const dbRules = await rulesRes.json();
                state.rules = dbRules;
            }
            const goalsRes = await fetch('/api/goals', { headers: { 'Accept': 'application/json' } });
            if (goalsRes.ok) {
                state.goals = await goalsRes.json();
            }
            const kpisRes = await fetch('/api/kpis', { headers: { 'Accept': 'application/json' } });
            if (kpisRes.ok) {
                state.kpiPlans = await kpisRes.json();
                rebuildApprovedKpiConfig();
            }
            const leavesRes = await fetch('/api/leave-requests', { headers: { 'Accept': 'application/json' } });
            if (leavesRes.ok) {
                state.leaveRequests = await leavesRes.json();
            }
            const resignationsRes = await fetch('/api/resignation-requests', { headers: { 'Accept': 'application/json' } });
            if (resignationsRes.ok) {
                state.resignationRequests = await resignationsRes.json();
            }
            const metricsRes = await fetch('/api/metrics/dashboard', { headers: { 'Accept': 'application/json' } });
            if (metricsRes.ok) {
                state.serverMetrics = await metricsRes.json();
            }
            await syncNotifications();
            await syncChatMessages(false);
            // Update local state and re-render
            state.version = CURRENT_VERSION;
            localStorage.setItem('erpState', JSON.stringify(state));
            
            // Sync current logged in user details
            if (currentUser && state.users[currentUser.username]) {
                currentUser = state.users[currentUser.username];
                const storedSession = getStoredSession();
                if (!storedSession) {
                    endLocalSession('Sesi tujuh hari telah berakhir. Silakan masuk kembali.');
                    return;
                }
                localStorage.setItem('currentUserSession', JSON.stringify({
                    user: currentUser,
                    loginTime: storedSession.loginTime,
                    expiresAt: storedSession.expiresAt
                }));
                renderDynamicStaffNavigation();
            } else if (currentUser) {
                endLocalSession('Akun Anda sudah tidak aktif. Silakan hubungi atasan atau HRD.');
                return;
            }
            
            if (typeof updateUniversalClockBtnState === 'function') {
                updateUniversalClockBtnState();
            }
            
            renderAll();
        } catch (e) {
            console.error("Gagal sinkronisasi data dari MySQL:", e);
        }
    }

    function rebuildApprovedKpiConfig() {
        const approvedPlans = (state.kpiPlans || []).filter(plan => plan.status === 'approved');
        const config = {};

        approvedPlans.forEach(plan => {
            const managerUsername = plan.manager?.username;
            const division = plan.division || plan.goal?.division;
            const items = (plan.kpis || []).map(kpi => ({
                id: kpi.id,
                name: kpi.title,
                weight: Number(kpi.weight),
                target: Number(kpi.target_value),
                unit: kpi.unit,
                goalId: plan.goal_id,
                goalTitle: plan.title || plan.goal?.title || 'Rencana KPI Mandiri'
            }));

            if (managerUsername) config[managerUsername] = items;
            Object.values(state.users || {}).forEach(user => {
                if (divisionFromRole(user.role) === division) {
                    config[user.username] = items;
                    config[user.role] = items;
                }
            });
        });

        state.kpiConfig = config;
    }

    async function syncNotifications() {
        if (!currentUser) return;

        try {
            const payload = await apiRequest('/api/notifications?limit=50');
            state.serverNotifications = payload.notifications || [];
            state.serverUnreadNotifications = payload.unread_count || 0;
            if (payload.server_time) syncServerClock(payload.server_time);
            renderNotifications();
        } catch (error) {
            if (error.status !== 401) {
                console.error('Gagal mengambil notifikasi:', error);
            }
        }
    }

    function startRealtimeSync() {
        if (realtimeSyncTimer) clearInterval(realtimeSyncTimer);
        realtimeSyncTimer = setInterval(async () => {
            if (!currentUser || realtimeSyncBusy) return;
            if (!getStoredSession()) {
                endLocalSession('Sesi tujuh hari telah berakhir. Silakan masuk kembali.');
                return;
            }
            realtimeSyncBusy = true;
            try {
                await syncDataFromServer();
            } finally {
                realtimeSyncBusy = false;
            }
        }, 15000);
    }

    function startChatRealtimeSync() {
        if (chatRealtimeTimer) clearInterval(chatRealtimeTimer);
        chatRealtimeTimer = setInterval(() => syncChatMessages(true), 2000);
    }

    // ================= Custom Confirm Modal System =================
    function showCustomConfirm(title, message, onConfirm, options = {}) {
        const confirmModal = document.getElementById('confirm-modal');
        const confirmTitle = document.getElementById('confirm-modal-title');
        const confirmMessage = document.getElementById('confirm-modal-message');
        const btnCancel = document.getElementById('btn-confirm-cancel');
        const btnOk = document.getElementById('btn-confirm-ok');
        const icon = document.getElementById('confirm-modal-icon');

        if (!confirmModal || !confirmTitle || !confirmMessage || !btnCancel || !btnOk) {
            console.warn('Dialog konfirmasi tidak tersedia:', title);
            return;
        }

        const config = Object.assign({
            confirmText: 'Ya',
            cancelText: 'Tidak',
            variant: 'danger'
        }, options);

        confirmTitle.innerText = title;
        confirmMessage.innerText = message;
        btnCancel.innerText = config.cancelText;
        btnOk.innerText = config.confirmText;
        
        btnCancel.style.display = 'inline-flex';
        btnOk.style.boxShadow = 'none';
        if (icon) {
            icon.className = `premium-dialog-icon ${config.variant}`;
            icon.innerHTML = config.variant === 'danger'
                ? '<i class="ph-fill ph-warning-octagon"></i>'
                : '<i class="ph-fill ph-question"></i>';
        }

        if (config.variant === 'danger') {
            btnOk.style.background = 'var(--danger)';
            btnOk.style.color = '#fff';
        } else if (config.variant === 'primary') {
            btnOk.style.background = 'var(--primary)';
            btnOk.style.color = '#000';
        } else {
            btnOk.style.background = 'var(--success)';
            btnOk.style.color = '#06130a';
        }

        confirmModal.style.display = 'flex';

        const cleanUp = () => {
            confirmModal.style.display = 'none';
            btnCancel.onclick = null;
            btnOk.onclick = null;
        };

        btnCancel.onclick = cleanUp;
        btnOk.onclick = () => {
            cleanUp();
            onConfirm();
        };
    }

    function showPremiumNotice(title, htmlContent, options = {}) {
        const confirmModal = document.getElementById('confirm-modal');
        const confirmTitle = document.getElementById('confirm-modal-title');
        const confirmMessage = document.getElementById('confirm-modal-message');
        const btnCancel = document.getElementById('btn-confirm-cancel');
        const btnOk = document.getElementById('btn-confirm-ok');
        const icon = document.getElementById('confirm-modal-icon');
         
        if (!confirmModal || !confirmTitle || !confirmMessage || !btnOk) {
            console.warn(title, String(htmlContent).replace(/<[^>]+>/g, ' '));
            return;
        }
         
        const variant = options.variant || 'primary';
        confirmTitle.textContent = title;
        confirmMessage.innerHTML = htmlContent;
        btnOk.innerText = options.buttonText || 'Baik';
        btnOk.style.background = variant === 'danger'
            ? 'var(--danger)'
            : (variant === 'success' ? 'var(--success)' : 'var(--primary)');
        btnOk.style.color = variant === 'danger' ? '#fff' : '#090900';
        if (icon) {
            icon.className = `premium-dialog-icon ${variant}`;
            icon.innerHTML = variant === 'danger'
                ? '<i class="ph-fill ph-warning-octagon"></i>'
                : (variant === 'success'
                    ? '<i class="ph-fill ph-check-circle"></i>'
                    : '<i class="ph-fill ph-info"></i>');
        }
        confirmModal.style.display = 'flex';
         
        if (btnCancel) btnCancel.style.display = 'none';
         
        btnOk.onclick = () => {
            confirmModal.style.display = 'none';
            if (btnCancel) btnCancel.style.display = 'inline-block';
            btnOk.innerText = 'Ya';
            btnOk.onclick = null;
            if (typeof options.onClose === 'function') options.onClose();
        };
    }

    function showTextInputDialog(options, onSubmit) {
        const modal = document.getElementById('input-dialog-modal');
        const form = document.getElementById('input-dialog-form');
        const title = document.getElementById('input-dialog-title');
        const description = document.getElementById('input-dialog-description');
        const label = document.getElementById('input-dialog-label');
        const input = document.getElementById('input-dialog-value');
        const cancel = document.getElementById('btn-input-dialog-cancel');
        const submit = document.getElementById('btn-input-dialog-submit');
        if (!modal || !form || !input) return;

        title.textContent = options.title || 'Tambahkan Catatan';
        description.textContent = options.description || 'Tuliskan informasi yang diperlukan.';
        label.textContent = options.label || 'Catatan';
        input.value = options.defaultValue || '';
        input.placeholder = options.placeholder || '';
        input.required = options.required !== false;
        submit.textContent = options.submitText || 'Simpan';
        modal.style.display = 'flex';
        setTimeout(() => input.focus(), 50);

        const close = () => {
            modal.style.display = 'none';
            form.onsubmit = null;
            cancel.onclick = null;
        };

        cancel.onclick = close;
        form.onsubmit = async event => {
            event.preventDefault();
            const value = input.value.trim();
            if (input.required && !value) {
                input.focus();
                return;
            }
            close();
            await onSubmit(value);
        };
    }

    function showStaffSeparationDialog(target, mode, onSubmit) {
        const modal = document.getElementById('staff-separation-modal');
        const form = document.getElementById('staff-separation-form');
        const title = document.getElementById('staff-separation-title');
        const description = document.getElementById('staff-separation-description');
        const completion = document.getElementById('staff-separation-completion');
        const reason = document.getElementById('staff-separation-reason');
        const effectiveDate = document.getElementById('staff-separation-effective-date');
        const notes = document.getElementById('staff-separation-notes');
        const alumni = document.getElementById('staff-separation-alumni');
        const error = document.getElementById('staff-separation-error');
        const cancel = document.getElementById('staff-separation-cancel');
        const submit = document.getElementById('staff-separation-submit');
        if (!modal || !form || !completion || !reason || !effectiveDate || !notes || !submit) return;

        const displayName = target?.name || target?.username || 'anggota tim';
        title.textContent = mode === 'request'
            ? `Ajukan Penonaktifan ${displayName}`
            : `Nonaktifkan ${displayName}`;
        description.textContent = mode === 'request'
            ? 'Lengkapi status pekerjaan dan alasan keluar. Pengajuan baru berlaku setelah disetujui CEO.'
            : 'Lengkapi status pekerjaan dan alasan keluar sebelum akun dinonaktifkan.';
        form.reset();
        completion.value = 'completed';
        reason.value = 'completed';
        effectiveDate.value = todayJakarta();
        effectiveDate.max = todayJakarta();
        notes.required = false;
        if (error) error.style.display = 'none';
        submit.innerHTML = mode === 'request'
            ? '<i class="ph ph-paper-plane-tilt"></i> Kirim ke CEO'
            : '<i class="ph ph-user-minus"></i> Nonaktifkan Akun';
        modal.style.display = 'flex';

        const updateRequiredState = () => {
            const required = reason.value === 'other' || completion.value === 'incomplete';
            notes.required = required;
            if (alumni) {
                alumni.disabled = completion.value !== 'completed';
                if (alumni.disabled) alumni.checked = false;
                alumni.closest('.staff-alumni-option')?.classList.toggle('is-disabled', alumni.disabled);
            }
            notes.placeholder = required
                ? 'Catatan wajib: jelaskan pekerjaan yang tertunda, penanggung jawab lanjutan, atau alasan lain.'
                : 'Contoh: Seluruh file proyek telah dipindahkan ke folder tim dan diterima oleh atasan.';
        };
        updateRequiredState();
        reason.onchange = updateRequiredState;
        completion.onchange = updateRequiredState;

        const close = () => {
            modal.style.display = 'none';
            form.onsubmit = null;
            cancel.onclick = null;
            reason.onchange = null;
            completion.onchange = null;
        };

        cancel.onclick = close;
        form.onsubmit = async event => {
            event.preventDefault();
            updateRequiredState();
            if (!form.reportValidity()) return;
            const original = submit.innerHTML;
            submit.disabled = true;
            submit.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Memproses...';
            if (error) error.style.display = 'none';
            try {
                await onSubmit({
                    completion_status: completion.value,
                    convert_to_alumni: Boolean(alumni?.checked),
                    separation_reason: reason.value,
                    separation_notes: notes.value.trim() || null,
                    effective_date: effectiveDate.value,
                });
                close();
            } catch (exception) {
                if (error) {
                    error.textContent = exception.message;
                    error.style.display = 'block';
                }
            } finally {
                submit.disabled = false;
                submit.innerHTML = original;
            }
        };
    }

    window.erpApiRequest = apiRequest;
    window.erpNotify = showPremiumNotice;
    window.erpConfirm = showCustomConfirm;
    window.erpTextInput = showTextInputDialog;

    async function requestDataDeletion({ resourceType, resourceId, label, trigger = null }) {
        if (!resourceType || !resourceId) return;

        const targetLabel = label || `Data #${resourceId}`;
        showCustomConfirm(
            'Hapus Data?',
            `Anda akan menghapus â€œ${targetLabel}â€. Data bersama, laporan, keuangan, presensi, dokumen, dan data lintas pihak akan menunggu persetujuan atasan. Riwayat audit tidak ikut dihapus.`,
            () => {
                showTextInputDialog({
                    title: 'Alasan Penghapusan',
                    description: 'Tuliskan alasan yang jelas. Catatan ini akan dibaca atasan dan disimpan dalam audit ERP.',
                    label: 'Alasan wajib',
                    placeholder: 'Contoh: Data duplikat akibat salah input pada tanggal 27 Juli 2026.',
                    submitText: 'Kirim Pengajuan Hapus'
                }, async reason => {
                    if (reason.length < 10) {
                        showPremiumNotice(
                            'Alasan Terlalu Singkat',
                            'Tuliskan alasan minimal 10 karakter agar keputusan dapat diaudit.',
                            { variant: 'danger' }
                        );
                        return;
                    }

                    const original = trigger?.innerHTML;
                    if (trigger) {
                        trigger.disabled = true;
                        trigger.innerHTML = '<i class="ph ph-spinner ph-spin"></i>';
                    }

                    try {
                        const result = await apiRequest('/api/data-deletions', {
                            method: 'POST',
                            body: {
                                resource_type: resourceType,
                                resource_id: Number(resourceId),
                                reason
                            }
                        });

                        showPremiumNotice(
                            result.approval ? 'Pengajuan Hapus Terkirim' : 'Data Berhasil Diproses',
                            escapeHtml(result.message),
                            { variant: 'success' }
                        );

                        window.dispatchEvent(new CustomEvent('erp:data-deletion-updated', {
                            detail: { resourceType, resourceId: Number(resourceId), result }
                        }));

                        if (currentUser) await syncDataFromServer();
                    } catch (error) {
                        showPremiumNotice(
                            'Penghapusan Tidak Dapat Diproses',
                            escapeHtml(error.message),
                            { variant: 'danger' }
                        );
                    } finally {
                        if (trigger) {
                            trigger.disabled = false;
                            trigger.innerHTML = original;
                        }
                    }
                });
            },
            {
                variant: 'danger',
                confirmText: 'Lanjutkan',
                cancelText: 'Batal'
            }
        );
    }

    window.erpRequestDeletion = requestDataDeletion;

    document.addEventListener('click', event => {
        const button = event.target.closest('[data-erp-delete]');
        if (!button) return;

        event.preventDefault();
        event.stopPropagation();
        requestDataDeletion({
            resourceType: button.dataset.resourceType,
            resourceId: button.dataset.resourceId,
            label: button.dataset.resourceLabel,
            trigger: button
        });
    });

    if (loginBtn && loginOverlay) {
        let isOtpPhase = false;
        
        loginBtn.addEventListener('click', async () => {
            const userStr = usernameInput.value.trim();
            const otpInput = document.getElementById('login-otp');
            const errorEl = document.getElementById('login-error');
            
            if (!userStr) {
                errorEl.innerText = "Masukkan Username atau Email Anda!";
                errorEl.style.display = 'block';
                return;
            }
            
            const originalText = loginBtn.innerHTML;
            
            if (!isOtpPhase) {
                // Phase 1: Kirim OTP
                loginBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Mengirim OTP...';
                errorEl.style.display = 'none';
                
                try {
                    const response = await fetch('/api/login/send-otp', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({ username: userStr })
                    });
                    const resData = await response.json();
                    
                    if (resData.success) {
                        isOtpPhase = true;
                        if (otpInput) {
                            otpInput.style.display = 'block';
                            otpInput.focus();
                        }
                        loginBtn.innerHTML = '<i class="ph ph-sign-in"></i> Verifikasi & Login';
                        showPremiumNotice(
                            'OTP Email Dikirim',
                            escapeHtml(resData.message || 'Periksa email terdaftar untuk mendapatkan kode OTP enam digit.'),
                            { variant: 'success' }
                        );
                    } else {
                        errorEl.innerText = resData.message || "Gagal mengirim OTP.";
                        errorEl.style.display = 'block';
                        loginBtn.innerHTML = originalText;
                    }
                } catch (err) {
                    console.error(err);
                    errorEl.innerText = "Koneksi API error. Pastikan server Laravel aktif.";
                    errorEl.style.display = 'block';
                    loginBtn.innerHTML = originalText;
                }
            } else {
                // Phase 2: Verifikasi OTP
                const otpStr = otpInput ? otpInput.value.trim() : '';
                if (!/^\d{6}$/.test(otpStr)) {
                    errorEl.innerText = "Masukkan kode OTP enam digit dengan benar!";
                    errorEl.style.display = 'block';
                    return;
                }
                
                loginBtn.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Memverifikasi...';
                errorEl.style.display = 'none';
                
                try {
                    const response = await fetch('/api/login/verify-otp', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({ username: userStr, otp: otpStr })
                    });
                    const resData = await response.json();
                    
                    if (resData.success) {
                        errorEl.style.display = 'none';
                        
                        // Sinkronkan data user ke local state & aktifkan sesi login
                        state.users[resData.user.username] = resData.user;
                        applyLogin(resData.user, false, {
                            server_time: resData.server_time,
                            session_expires_at: resData.session_expires_at
                        });
                        updateState(state);
                    } else {
                        errorEl.innerText = resData.message || "OTP salah atau kedaluwarsa.";
                        errorEl.style.display = 'block';
                        loginBtn.innerHTML = originalText;
                    }
                } catch (err) {
                    console.error("Login OTP JS Error:", err);
                    errorEl.innerText = err.message ? `Error UI: ${err.message}` : "Gagal memverifikasi OTP.";
                    errorEl.style.display = 'block';
                    loginBtn.innerHTML = originalText;
                }
            }
        });
        
        // Handle login on Enter keypress
        const handleEnterLogin = (e) => {
            if (e.key === 'Enter') {
                loginBtn.click();
            }
        };
        usernameInput.addEventListener('keydown', handleEnterLogin);
    const otpInputEl = document.getElementById('login-otp');
    if (otpInputEl) {
        otpInputEl.addEventListener('keydown', handleEnterLogin);
        otpInputEl.addEventListener('input', () => {
            otpInputEl.value = otpInputEl.value.replace(/\D/g, '').slice(0, 6);
        });
    }
    }

    // ================= Session Logout =================
    async function logout() {
        try {
            await apiRequest('/api/logout', { method: 'POST' });
        } catch (error) {
            console.warn('Sesi server sudah berakhir atau tidak dapat dihubungi.', error);
        }

        endLocalSession();
        usernameInput.value = '';
        const otpInput = document.getElementById('login-otp');
        if (otpInput) {
            otpInput.value = '';
            otpInput.style.display = 'none';
        }
        loginError.style.display = 'none';
        
        loginOverlay.style.display = 'flex';
        setTimeout(() => {
            loginOverlay.style.opacity = '1';
        }, 50);
        
        if (loginBtn) {
            loginBtn.innerHTML = '<i class="ph ph-envelope-simple"></i> Kirim OTP';
        }
        // Force refresh to clear states
        window.location.reload();
    }

    const logoutBtn = document.getElementById('logout-btn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', (e) => {
            e.preventDefault();
            showCustomConfirm("Logout Confirmation", "Apakah Anda yakin ingin keluar dari sesi ini?", () => {
                logout();
            }, { confirmText: 'Keluar', cancelText: 'Batal' });
        });
    }

    const sidebarLogoutNav = document.getElementById('sidebar-logout-nav');
    if (sidebarLogoutNav) {
        sidebarLogoutNav.addEventListener('click', (e) => {
            e.preventDefault();
            showCustomConfirm("Logout Confirmation", "Apakah Anda yakin ingin keluar dari sesi ini?", () => {
                logout();
            }, { confirmText: 'Keluar', cancelText: 'Batal' });
        });
    }



    // ================= Attendance Clock In / Out =================
    const clockInBtn = document.getElementById('univ-clock-in-btn');
    
    function updateUniversalClockBtnState() {
        if (!clockInBtn || !currentUser) return;
        
        const dateStr = todayJakarta();
        const todayRecord = state.attendance.find(a => a.username === currentUser.username && a.date === dateStr);
        
        if (!todayRecord) {
            clockInBtn.innerHTML = '<i class="ph ph-sign-in"></i> Clock In';
            clockInBtn.style.background = 'var(--primary)';
            clockInBtn.style.color = '#020617';
            clockInBtn.style.fontWeight = '700';
            clockInBtn.disabled = false;
        } else if (todayRecord.is_active) {
            clockInBtn.innerHTML = '<i class="ph ph-sign-out"></i> Clock Out';
            clockInBtn.style.background = 'var(--danger)';
            clockInBtn.style.color = 'white';
            clockInBtn.style.fontWeight = '700';
            clockInBtn.disabled = false;
        } else {
            clockInBtn.innerHTML = '<i class="ph ph-check-circle"></i> Clocked Out';
            clockInBtn.style.background = 'rgba(255,255,255,0.05)';
            clockInBtn.style.color = 'rgba(255,255,255,0.3)';
            clockInBtn.disabled = true;
        }
    }

    if (clockInBtn) {
        clockInBtn.addEventListener('click', async () => {
            if (!currentUser) {
                showPremiumNotice('Sesi Diperlukan', 'Silakan masuk terlebih dahulu untuk melakukan absensi.', { variant: 'danger' });
                return;
            }

            const dateStr = todayJakarta();
            const alreadyIn = state.attendance.some(a => a.username === currentUser.username && a.date === dateStr);
            let actionType = 'Clock In';
            
            if (alreadyIn) {
                const attendanceRecord = state.attendance.find(a => a.username === currentUser.username && a.date === dateStr);
                if (attendanceRecord && !attendanceRecord.is_active) {
                    showPremiumNotice("Absensi Hari Ini Selesai", "Anda sudah melakukan Clock Out hari ini. Terima kasih!");
                    return;
                }
                actionType = 'Clock Out';
            }

            // Tampilkan Modal Konfirmasi Premium
            const confModal = document.getElementById('attendance-confirm-modal');
            const confTitle = document.getElementById('att-confirm-title');
            const confSubtitle = document.getElementById('att-confirm-subtitle');
            const confCoords = document.getElementById('att-confirm-coords');
            const confAddress = document.getElementById('att-confirm-address');
            const btnCancel = document.getElementById('btn-att-confirm-cancel');
            const btnOk = document.getElementById('btn-att-confirm-ok');
            const iconContainer = document.getElementById('att-confirm-icon-container');
            const icon = document.getElementById('att-confirm-icon');

            if (!confModal) return;

            confTitle.innerText = `Konfirmasi Kehadiran (${actionType})`;
            const holidayToday = actionType === 'Clock In' && Boolean(state.attendanceSummary?.today?.is_holiday);
            confSubtitle.innerText = holidayToday
                ? `Hari ini ${state.attendanceSummary?.today?.label || 'hari libur'}. Anda yakin ingin clock in? Jam kerja hari ini tetap dihitung dan mengurangi sisa target jam bulan ini.`
                : `Deteksi lokasi GPS untuk absensi ${actionType.toLowerCase()}.`;
            confCoords.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Mendeteksi GPS...';
            confAddress.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Mencari alamat...';
            btnOk.disabled = true;
            btnOk.innerHTML = 'Memuat Lokasi...';

            if (actionType === 'Clock Out') {
                if (iconContainer) iconContainer.style.background = 'rgba(255, 59, 48, 0.1)';
                if (icon) {
                    icon.className = 'ph-fill ph-sign-out';
                    icon.style.color = 'var(--danger)';
                }
            } else {
                if (iconContainer) iconContainer.style.background = 'rgba(52, 199, 89, 0.1)';
                if (icon) {
                    icon.className = 'ph-fill ph-map-pin';
                    icon.style.color = 'var(--primary)';
                }
            }

            confModal.style.display = 'flex';

            // Ambil GPS rill dari browser
            navigator.geolocation.getCurrentPosition(async (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                confCoords.innerText = `${lat.toFixed(6)}, ${lng.toFixed(6)}`;

                // Panggil reverse geocode API ke server Laravel
                try {
                    const res = await fetch(`/api/attendance/reverse-geocode?lat=${lat}&lng=${lng}`);
                    const data = await res.json();
                    
                    if (data.success) {
                        confAddress.innerText = data.location_name;
                        btnOk.disabled = false;
                        btnOk.innerHTML = `Ya, ${actionType} Sekarang`;
                        
                        btnOk.onclick = async () => {
                            confModal.style.display = 'none';
                            
                            if (actionType === 'Clock In') {
                                try {
                                    // Kirim clock in
                                    const response = await fetch('/api/attendance/clock-in', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                        },
                                        body: JSON.stringify({
                                            username: currentUser.username,
                                            lat: lat,
                                            lng: lng,
                                            type: 'WFO', // Default
                                            confirmed_holiday_work: holidayToday
                                        })
                                    });
                                    const resData = await response.json();
                                    
                                    if (response.ok && resData.success) {
                                        syncServerClock(resData.server_time);
                                        const serverDate = new Date(resData.server_time);
                                        const localTime = resData.display_time || serverDate.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', timeZone: JAKARTA_TIMEZONE }) + ' WIB';
                                        const localDate = resData.attendance_date || formatDateJakarta(serverDate);

                                        state.attendance.push({
                                            username: currentUser.username,
                                            status: resData.attendance.status,
                                            time: localTime,
                                            date: localDate,
                                            lat: lat,
                                            lng: lng,
                                            type: resData.attendance.work_type || 'WFO',
                                            location_name: resData.attendance.location_name,
                                            is_active: true,
                                            duration_hours: 0
                                        });
                                        updateState(state);
                                        updateUniversalClockBtnState();
                                        renderAll();
                                        showPremiumNotice("Absensi Sukses", `Selamat bekerja! Clock In tercatat pada ${localTime} berdasarkan waktu resmi server di lokasi:<br><br><b>${escapeHtml(resData.attendance.location_name)}</b>`, { variant: 'success' });
                                    } else {
                                        showPremiumNotice('Absensi Gagal', escapeHtml(resData.error || 'Server tidak dapat memproses absensi.'), { variant: 'danger' });
                                    }
                                } catch (err) {
                                    console.error(err);
                                    showPremiumNotice('Koneksi Bermasalah', 'Absensi belum tersimpan. Silakan periksa koneksi dan coba kembali.', { variant: 'danger' });
                                }
                            } else {
                                // Clock Out
                                try {
                                    const response = await fetch('/api/attendance/clock-out', {
                                        method: 'POST',
                                        headers: {
                                            'Content-Type': 'application/json',
                                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                        },
                                        body: JSON.stringify({ username: currentUser.username })
                                    });
                                    const resData = await response.json();
                                    
                                    if (response.ok && resData.success) {
                                        syncServerClock(resData.server_time);
                                        const serverDate = new Date(resData.server_time);
                                        const localTimeOut = resData.display_time || serverDate.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', timeZone: JAKARTA_TIMEZONE }) + ' WIB';
                                        
                                        const attendanceRecord = state.attendance.find(a => a.username === currentUser.username && a.date === dateStr);
                                        if (attendanceRecord) {
                                            attendanceRecord.status = resData.attendance.status || attendanceRecord.status;
                                            attendanceRecord.timeOut = localTimeOut;
                                            attendanceRecord.is_active = false;
                                        }
                                        updateState(state);
                                        updateUniversalClockBtnState();
                                        renderAll();
                                        showPremiumNotice("Absensi Keluar Sukses", `Kerja bagus untuk hari ini! Clock Out tercatat pada ${localTimeOut} berdasarkan waktu resmi server.`, { variant: 'success' });
                                    } else {
                                        showPremiumNotice('Clock Out Gagal', escapeHtml(resData.error || 'Server tidak dapat memproses clock out.'), { variant: 'danger' });
                                    }
                                } catch (err) {
                                    console.error(err);
                                }
                            }
                        };
                    } else {
                        confAddress.innerText = "Gagal memetakan alamat geocode.";
                    }
                } catch (e) {
                    console.error(e);
                    confAddress.innerText = "Kesalahan koneksi ke server.";
                }
            }, (error) => {
                console.error(error);
                confCoords.innerHTML = '<span style="color: var(--danger);">GPS Gagal Terdeteksi</span>';
                confAddress.innerHTML = '<span style="color: var(--danger);">Gagal melacak lokasi Anda. Mohon aktifkan izin GPS browser Anda!</span>';
                btnOk.disabled = true;
                btnOk.innerHTML = 'Harap Aktifkan GPS';
            }, {
                enableHighAccuracy: true,
                timeout: 7000,
                maximumAge: 0
            });

            btnCancel.onclick = () => {
                confModal.style.display = 'none';
            };
        });
    }

    // ================= Chart.js Setup =================
    let omzetChartInstance = null;
    function renderOmzetChart() {
        const ctx = document.getElementById('omzetChart');
        if (!ctx) return;
        
        if (omzetChartInstance) {
            omzetChartInstance.destroy();
        }
        
        const actualOmzet = Number(state.crmOverview?.summary?.actual_revenue || 0) / 1_000_000_000;
        const pipelineForecast = Number(state.crmOverview?.summary?.weighted_forecast || 0) / 1_000_000_000;
        
        omzetChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
                datasets: [
                    {
                        label: 'Omzet Actual (Rp M)',
                        data: [0, actualOmzet, 0, 0],
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderRadius: 4
                    },
                    {
                        label: 'Proyeksi (Pipeline M)',
                        data: [0, 0, pipelineForecast, 0],
                        backgroundColor: 'rgba(16, 185, 129, 0.4)',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        labels: { color: '#94A3B8' }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(255,255,255,0.05)' },
                        ticks: { color: '#94A3B8', callback: function(value) { return value + 'M'; } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94A3B8' }
                    }
                }
            }
        });
    }

    // ================= Kanban Board Dynamics =================
    let activeCrmLeadId = null;

    function formatCrmCurrency(value) {
        const amount = Number(value || 0);
        if (amount >= 1_000_000_000) {
            return `Rp ${(amount / 1_000_000_000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} M`;
        }
        if (amount >= 1_000_000) {
            return `Rp ${(amount / 1_000_000).toLocaleString('id-ID', { maximumFractionDigits: 1 })} Jt`;
        }
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0
        }).format(amount);
    }

    function crmDateTime(value, fallback = '-') {
        if (!value) return fallback;
        const parsed = new Date(value);
        if (Number.isNaN(parsed.getTime())) return value;
        return new Intl.DateTimeFormat('id-ID', {
            timeZone: 'Asia/Jakarta',
            dateStyle: 'medium',
            timeStyle: 'short'
        }).format(parsed);
    }

    function crmDateTimeInput(value) {
        if (!value) return '';
        const parsed = new Date(value);
        if (Number.isNaN(parsed.getTime())) return '';
        const local = new Date(parsed.getTime() - parsed.getTimezoneOffset() * 60_000);
        return local.toISOString().slice(0, 16);
    }

    function renderCrmSummary() {
        const target = document.getElementById('crm-summary');
        if (!target) return;
        const summary = state.crmOverview?.summary || {};
        target.innerHTML = `
            <div class="crm-summary-card">
                <span>Lead Aktif</span>
                <strong>${Number(summary.open_leads || 0).toLocaleString('id-ID')}</strong>
                <small>${Number(summary.whatsapp_leads || 0).toLocaleString('id-ID')} berasal dari WhatsApp</small>
            </div>
            <div class="crm-summary-card">
                <span>Pipeline</span>
                <strong>${escapeHtml(formatCrmCurrency(summary.pipeline_value))}</strong>
                <small>Forecast tertimbang ${escapeHtml(formatCrmCurrency(summary.weighted_forecast))}</small>
            </div>
            <div class="crm-summary-card revenue">
                <span>Omzet Aktual</span>
                <strong>${escapeHtml(formatCrmCurrency(summary.actual_revenue))}</strong>
                <small>Pembayaran yang tercatat di Finance</small>
            </div>
            <div class="crm-summary-card ${Number(summary.due_follow_ups || 0) ? 'attention' : ''}">
                <span>Konversi</span>
                <strong>${Number(summary.conversion_rate || 0).toLocaleString('id-ID')}%</strong>
                <small>${Number(summary.due_follow_ups || 0).toLocaleString('id-ID')} follow-up perlu ditangani</small>
            </div>
        `;
        renderWhatsAppIntegration();
    }

    function renderWhatsAppIntegration() {
        const badge = document.getElementById('crm-whatsapp-status');
        if (!badge) return;

        const integration = state.whatsappIntegration || {};
        let label = 'Mode WhatsApp manual';
        let icon = 'ph-link-break';
        let className = 'crm-integration-badge';

        if (integration.fully_configured) {
            label = 'WhatsApp API aktif';
            icon = 'ph-check-circle';
            className += ' active';
        } else if (integration.inbound_configured || integration.outbound_configured) {
            label = integration.inbound_configured
                ? 'Webhook siap Â· kirim belum aktif'
                : 'Pengiriman siap Â· webhook belum aktif';
            icon = 'ph-warning-circle';
            className += ' partial';
        }

        badge.className = className;
        badge.innerHTML = `<i class="ph ${icon}"></i> ${escapeHtml(label)}`;
        badge.title = integration.callback_url
            ? `Callback: ${integration.callback_url}`
            : 'Tambahkan kredensial Meta pada environment server.';
    }

    async function refreshCrmData() {
        const [leadsResponse, overviewResponse, whatsappStatusResponse] = await Promise.all([
            fetch('/api/leads', { headers: { 'Accept': 'application/json' } }),
            fetch('/api/crm/overview', { headers: { 'Accept': 'application/json' } }),
            fetch('/api/crm/whatsapp/status', { headers: { 'Accept': 'application/json' } })
        ]);

        if (!leadsResponse.ok || !overviewResponse.ok) {
            throw new Error('Data CRM belum dapat disegarkan.');
        }

        state.leads = await leadsResponse.json();
        state.crmOverview = await overviewResponse.json();
        if (whatsappStatusResponse.ok) {
            state.whatsappIntegration = await whatsappStatusResponse.json();
        }
        localStorage.setItem('erpState', JSON.stringify(state));
        renderKanban();
        renderOmzetChart();
    }

    function renderLeadTimeline(activities = []) {
        const list = document.getElementById('lead-activity-list');
        const count = document.getElementById('detail-lead-activity-count');
        if (count) count.textContent = activities.length;
        if (!list) return;

        if (!activities.length) {
            list.innerHTML = '<div class="crm-timeline-empty">Belum ada interaksi yang dicatat.</div>';
            return;
        }

        const icons = {
            whatsapp: 'ph-whatsapp-logo',
            phone: 'ph-phone',
            meeting: 'ph-users-three',
            email: 'ph-envelope-simple',
            erp: 'ph-wallet',
            internal: 'ph-note-pencil'
        };
        list.innerHTML = activities.map(activity => {
            const channel = activity.channel || 'internal';
            const actor = activity.user?.name ? ` Â· ${activity.user.name}` : '';
            return `
                <article class="crm-timeline-item ${escapeHtml(channel)} ${escapeHtml(activity.type || '')}">
                    <span class="crm-timeline-icon"><i class="ph ${icons[channel] || 'ph-note'}"></i></span>
                    <strong>${escapeHtml(channel)} Â· ${escapeHtml(activity.direction || 'internal')}${escapeHtml(actor)}</strong>
                    <p>${escapeHtml(activity.body || '')}</p>
                    <time>${escapeHtml(crmDateTime(activity.occurred_at))}</time>
                </article>
            `;
        }).join('');
    }

    function renderLeadDetail(lead, activities = []) {
        const values = {
            'detail-lead-name': lead.name || '-',
            'detail-lead-budget': lead.budget || formatCrmCurrency(lead.project_value),
            'detail-lead-revenue': formatCrmCurrency(lead.actual_revenue),
            'detail-lead-phone': lead.phone ? `+${lead.phone}` : '-',
            'detail-lead-date': crmDateTime(lead.date),
            'detail-lead-source': lead.source || '-',
            'detail-lead-campaign': lead.campaign || 'Organik / tidak tercatat',
            'detail-lead-type': lead.type || '-',
            'detail-lead-assignee': lead.assignee?.name || lead.assignee?.username || '-',
            'detail-lead-column': ({
                leads: 'Leads Masuk',
                penawaran: 'Penawaran',
                deal: 'Deal',
                lost: 'Lost'
            })[lead.column] || lead.column,
            'detail-lead-follow-up': crmDateTime(lead.next_follow_up_at, 'Belum dijadwalkan'),
            'detail-lead-notes': lead.notes || 'Belum ada catatan kebutuhan.'
        };
        Object.entries(values).forEach(([id, value]) => {
            const element = document.getElementById(id);
            if (element) element.textContent = value;
        });

        const whatsapp = document.getElementById('detail-lead-whatsapp');
        if (whatsapp) {
            whatsapp.style.display = lead.whatsapp_url ? 'inline-flex' : 'none';
            if (lead.whatsapp_url) whatsapp.href = lead.whatsapp_url;
        }

        const followUpInput = document.querySelector('#lead-activity-form [name="next_follow_up_at"]');
        if (followUpInput) followUpInput.value = crmDateTimeInput(lead.next_follow_up_at);
        const activityForm = document.getElementById('lead-activity-form');
        if (activityForm) activityForm.style.display = lead.can_manage === false ? 'none' : 'grid';
        const apiSend = document.getElementById('crm-send-whatsapp-api');
        const sendHint = document.getElementById('crm-whatsapp-send-hint');
        const canSendApi = Boolean(
            lead.can_manage !== false
            && lead.phone
            && state.whatsappIntegration?.outbound_configured
        );
        if (apiSend) apiSend.hidden = !canSendApi;
        if (sendHint) {
            sendHint.textContent = canSendApi
                ? 'Kirim via API berlaku dalam 24 jam sejak pesan terakhir calon klien.'
                : 'Mode manual aktif; aktivitas tetap tersimpan pada timeline CRM.';
        }
        renderLeadTimeline(activities);
    }

    async function openLeadDetailModal(lead) {
        const modal = document.getElementById('lead-detail-modal');
        if (!modal) return;
        activeCrmLeadId = lead.id;
        renderLeadDetail(lead);
        modal.style.display = 'flex';

        const list = document.getElementById('lead-activity-list');
        if (list) list.innerHTML = '<div class="crm-timeline-empty">Memuat riwayat...</div>';

        try {
            const response = await fetch(`/api/leads/${lead.id}`, { headers: { 'Accept': 'application/json' } });
            if (!response.ok) throw new Error('Detail lead tidak dapat dimuat.');
            const payload = await response.json();
            const index = state.leads.findIndex(item => item.id === payload.lead.id);
            if (index >= 0) state.leads[index] = payload.lead;
            renderLeadDetail(payload.lead, payload.activities || []);
        } catch (error) {
            if (list) list.innerHTML = `<div class="crm-timeline-empty">${escapeHtml(error.message)}</div>`;
        }
    }

    function renderKanban() {
        renderCrmSummary();
        const cols = {
            'leads': document.querySelector('#col-leads .column-body'),
            'penawaran': document.querySelector('#col-penawaran .column-body'),
            'deal': document.querySelector('#col-deal .column-body'),
            'lost': document.querySelector('#col-lost .column-body')
        };
        
        if (!cols.leads || !cols.penawaran || !cols.deal || !cols.lost) return;
        
        Object.keys(cols).forEach(k => cols[k].innerHTML = '');
        
        const activeFilterBtn = document.querySelector('#view-kanban .filter-btn.active');
        const filterVal = activeFilterBtn ? activeFilterBtn.getAttribute('data-filter') : 'all';
        const searchValue = (document.getElementById('crm-lead-search')?.value || '').trim().toLowerCase();
        
        const filteredLeads = state.leads.filter(lead => {
            const matchesFilter = filterVal === 'all'
                || String(lead.type || '').includes(filterVal)
                || String(lead.source || '').includes(filterVal);
            const searchable = [
                lead.name,
                lead.phone,
                lead.source,
                lead.campaign,
                lead.type,
                lead.domicile
            ].filter(Boolean).join(' ').toLowerCase();
            return matchesFilter && (!searchValue || searchable.includes(searchValue));
        });

        filteredLeads.forEach(lead => {
                const colBody = cols[lead.column];
            if (colBody) {
                const card = document.createElement('div');
                card.className = `kanban-card ${lead.column === 'deal' ? 'success' : ''}`;
                card.setAttribute('draggable', lead.can_manage !== false ? 'true' : 'false');
                card.id = lead.id;
                
                const sourceClasses = {
                    'IG DM': 'source-ig',
                    'WhatsApp': 'source-wa',
                    'Website': 'source-web',
                    'Referensi': 'source-ref'
                };
                const sourceClass = sourceClasses[lead.source] || 'source-web';
                
                card.innerHTML = `
                    <div class="card-tags">
                        <span class="tag ${sourceClass}">${escapeHtml(lead.source || 'Tanpa sumber')}</span>
                        <span class="tag type-build">${escapeHtml(lead.type || 'Belum dikualifikasi')}</span>
                    </div>
                    <h4>${escapeHtml(lead.name)}</h4>
                    ${lead.phone ? `<div class="crm-card-contact"><i class="ph ph-whatsapp-logo"></i><span>+${escapeHtml(lead.phone)}</span></div>` : ''}
                    <p class="budget ${lead.column === 'deal' ? 'deal-value' : ''}">${escapeHtml(lead.budget || formatCrmCurrency(lead.project_value))}</p>
                    <div class="crm-card-meta">
                        <span>${lead.campaign ? escapeHtml(lead.campaign) : 'Organik'}</span>
                        ${Number(lead.actual_revenue || 0) > 0 ? `<span class="crm-card-revenue">${escapeHtml(formatCrmCurrency(lead.actual_revenue))}</span>` : ''}
                    </div>
                    ${lead.next_follow_up_at ? `<div class="crm-card-follow-up"><i class="ph ph-bell-ringing"></i> ${escapeHtml(crmDateTime(lead.next_follow_up_at))}</div>` : ''}
                    <div class="card-footer">
                        <span class="date">${escapeHtml(crmDateTime(lead.date))} Â· ${Number(lead.activities_count || 0)} aktivitas</span>
                        ${lead.can_delete !== false ? `<button type="button" class="erp-delete-btn icon-only" data-erp-delete
                            data-resource-type="lead"
                            data-resource-id="${String(lead.id).replace(/\D/g, '')}"
                            data-resource-label="Lead ${escapeHtml(lead.name)}"
                            title="Hapus atau ajukan penghapusan lead">
                            <i class="ph ph-trash"></i>
                        </button>` : ''}
                    </div>
                `;
                
                card.addEventListener('click', (e) => {
                    if (e.target.closest('[data-erp-delete]')) return;
                    if (card.classList.contains('dragging')) return;
                    openLeadDetailModal(lead);
                });

                card.addEventListener('dragstart', () => {
                    card.classList.add('dragging');
                });
                
                card.addEventListener('dragend', async () => {
                    card.classList.remove('dragging');
                    const parentCol = card.closest('.kanban-column');
                    if (!parentCol) return;
                    
                    const colId = parentCol.id.replace('col-', '');
                    const targetLead = state.leads.find(l => l.id === card.id);
                    if (targetLead && targetLead.column !== colId) {
                        targetLead.column = colId;
                        if (colId === 'deal') {
                            targetLead.budget = targetLead.budget.replace('Est: ', 'Deal: ');
                        } else {
                            targetLead.budget = targetLead.budget.replace('Deal: ', 'Est: ');
                        }
                        try {
                            const response = await fetch(`/api/leads/${targetLead.id}/status`, {
                                method: 'PUT',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                },
                                body: JSON.stringify({
                                    column: colId,
                                    budget: targetLead.budget
                                })
                            });
                            if (!response.ok) throw new Error('Tahap lead tidak dapat diperbarui.');
                            await refreshCrmData();
                        } catch (e) {
                            console.error("Gagal memperbarui status lead:", e);
                            await refreshCrmData().catch(() => {});
                        }
                    }
                });
                
                colBody.appendChild(card);
            }
        });

        // Render placeholders if column is empty after filtering
        Object.keys(cols).forEach(colId => {
            if (cols[colId].children.length === 0) {
                cols[colId].innerHTML = `
                    <div class="empty-column-placeholder" style="display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 32px 16px; border: 2px dashed var(--glass-border); border-radius: var(--radius-md); text-align: center; color: var(--text-muted); font-size: 13px; margin: 8px 0; font-family: inherit; width: 100%;">
                        <i class="ph ph-funnel-x" style="font-size: 28px; margin-bottom: 8px; color: var(--text-muted); opacity: 0.5;"></i>
                        <span>Belum ada leads untuk kategori ini</span>
                    </div>
                `;
            }
        });
        
        document.querySelector('#col-leads .count').innerText = filteredLeads.filter(l => l.column === 'leads').length;
        document.querySelector('#col-penawaran .count').innerText = filteredLeads.filter(l => l.column === 'penawaran').length;
        document.querySelector('#col-deal .count').innerText = filteredLeads.filter(l => l.column === 'deal').length;
        document.querySelector('#col-lost .count').innerText = filteredLeads.filter(l => l.column === 'lost').length;
    }

    const kanbanFilterBtns = document.querySelectorAll('#view-kanban .filter-btn');
    kanbanFilterBtns.forEach(btn => {
        btn.onclick = () => {
            kanbanFilterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            renderKanban();
        };
    });

    document.getElementById('crm-lead-search')?.addEventListener('input', renderKanban);

    const kanbanNewLeadBtn = document.getElementById('kanban-new-lead-btn');
    if (kanbanNewLeadBtn) {
        kanbanNewLeadBtn.onclick = () => {
            openNewLeadModal();
        };
    }

    document.querySelectorAll('.column-body').forEach(column => {
        column.addEventListener('dragover', e => {
            e.preventDefault();
            const draggingCard = document.querySelector('.dragging');
            if (draggingCard && !column.contains(draggingCard)) {
                column.appendChild(draggingCard);
            }
        });
    });

    function openNewLeadModal() {
        let modal = document.getElementById('new-lead-modal');
        if (!modal) {
            modal = document.createElement('div');
            modal.id = 'new-lead-modal';
            modal.className = 'modal-overlay crm-detail-overlay';
            
            modal.innerHTML = `
                <div class="crm-lead-create-dialog">
                    <div class="crm-detail-head">
                        <div>
                            <span class="crm-eyebrow">Lead intake</span>
                            <h3>Tambah Lead Baru</h3>
                        </div>
                        <button type="button" class="icon-btn" id="close-lead-modal-btn"><i class="ph ph-x"></i></button>
                    </div>
                    <form id="new-lead-form" class="crm-create-form">
                        <div class="crm-create-grid">
                            <label>Nama klien / proyek
                                <input type="text" name="name" required placeholder="Ibu Rina - Renovasi BSD">
                            </label>
                            <label>Nomor WhatsApp
                                <input type="tel" name="phone" required placeholder="0812 3456 7890">
                            </label>
                            <label>Email
                                <input type="email" name="email" placeholder="klien@email.com">
                            </label>
                            <label>Domisili
                                <input type="text" name="domicile" placeholder="Tangerang Selatan">
                            </label>
                            <label>Estimasi nilai proyek
                                <input type="number" name="project_value" min="0" step="100000" placeholder="750000000">
                            </label>
                            <label>Sumber lead
                                <select name="source">
                                    <option value="WhatsApp">WhatsApp</option>
                                    <option value="IG DM">Instagram</option>
                                    <option value="Website">Website</option>
                                    <option value="Referensi">Referensi</option>
                                </select>
                            </label>
                            <label>Kampanye
                                <input type="text" name="campaign" placeholder="Meta Ads Renovasi Juli">
                            </label>
                            <label>Tipe proyek
                                <select name="type">
                                    <option value="Pembangunan">Pembangunan</option>
                                    <option value="Desain">Desain</option>
                                    <option value="Renovasi">Renovasi</option>
                                    <option value="Survey">Survey</option>
                                </select>
                            </label>
                            <label>Follow-up berikutnya
                                <input type="datetime-local" name="next_follow_up_at">
                            </label>
                        </div>
                        <label>Pesan pertama
                            <textarea name="initial_message" rows="3" placeholder="Salin ringkasan pesan WhatsApp pertama calon klien..."></textarea>
                        </label>
                        <label>Catatan kebutuhan
                            <textarea name="notes" rows="3" placeholder="Luas lahan, ruang yang dibutuhkan, target waktu, dan informasi awal lainnya."></textarea>
                        </label>
                        <div class="crm-create-actions">
                            <button type="button" data-close-new-lead class="filter-btn">Batal</button>
                            <button type="submit" class="primary-btn"><i class="ph ph-check-circle"></i> Simpan Lead</button>
                        </div>
                    </form>
                </div>
            `;
            document.body.appendChild(modal);
            
            document.getElementById('new-lead-form').addEventListener('submit', async (e) => {
                e.preventDefault();
                const form = e.currentTarget;
                const submit = form.querySelector('button[type="submit"]');
                const payload = Object.fromEntries(new FormData(form).entries());
                payload.project_value = Number(payload.project_value || 0);
                payload.column = 'leads';
                payload.username = currentUser.username;
                Object.keys(payload).forEach(key => {
                    if (payload[key] === '') delete payload[key];
                });
                if (submit) submit.disabled = true;

                try {
                    const response = await fetch('/api/leads', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify(payload)
                    });
                    const result = await response.json();
                    if (!response.ok) {
                        const firstError = Object.values(result.errors || {}).flat()[0];
                        throw new Error(firstError || result.message || 'Lead belum dapat disimpan.');
                    }
                    await refreshCrmData();
                    form.reset();
                    modal.style.display = 'none';
                    showPremiumNotice(
                        result.duplicate_merged ? 'Lead Digabungkan' : 'Lead Tersimpan',
                        escapeHtml(result.message || `${result.name} masuk ke pipeline CRM.`)
                    );
                } catch (error) {
                    showPremiumNotice('Lead Tidak Dapat Disimpan', escapeHtml(error.message));
                } finally {
                    if (submit) submit.disabled = false;
                }
            });
            
            document.getElementById('close-lead-modal-btn').addEventListener('click', () => {
                modal.style.display = 'none';
            });
            modal.querySelector('[data-close-new-lead]')?.addEventListener('click', () => {
                modal.style.display = 'none';
            });
        }
        
        modal.style.display = 'flex';
    }

    document.getElementById('lead-activity-form')?.addEventListener('submit', async event => {
        event.preventDefault();
        if (!activeCrmLeadId) return;

        const form = event.currentTarget;
        const submit = event.submitter || form.querySelector('button[type="submit"]');
        const sendViaApi = submit?.dataset.whatsappSend === 'true';
        const payload = Object.fromEntries(new FormData(form).entries());
        const typeByChannel = {
            phone: 'call',
            meeting: 'meeting',
            internal: 'note'
        };
        payload.type = typeByChannel[payload.channel] || 'message';
        Object.keys(payload).forEach(key => {
            if (payload[key] === '') delete payload[key];
        });

        if (submit) submit.disabled = true;
        try {
            const endpoint = sendViaApi
                ? `/api/leads/${activeCrmLeadId}/whatsapp/send`
                : `/api/leads/${activeCrmLeadId}/activities`;
            const requestPayload = sendViaApi
                ? {
                    mode: 'text',
                    body: payload.body,
                    ...(payload.next_follow_up_at ? { next_follow_up_at: payload.next_follow_up_at } : {})
                }
                : payload;
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify(requestPayload)
            });
            const result = await response.json();
            if (!response.ok) {
                const firstError = Object.values(result.errors || {}).flat()[0];
                throw new Error(firstError || result.message || 'Aktivitas belum dapat disimpan.');
            }

            form.elements.body.value = '';
            await refreshCrmData();
            const refreshed = state.leads.find(lead => lead.id === activeCrmLeadId) || result.lead;
            await openLeadDetailModal(refreshed);
        } catch (error) {
            showPremiumNotice(
                sendViaApi ? 'Pesan WhatsApp Tidak Terkirim' : 'Aktivitas Tidak Dapat Disimpan',
                escapeHtml(error.message)
            );
        } finally {
            if (submit) submit.disabled = false;
        }
    });

    // ================= Maulana Workspace Renderer =================
    function renderMaulanaWorkspace() {
        const section = document.getElementById('view-member-dev');
        if (!section) return;
        
        // Sync Workspace Clock In/Out widget UI
        const wsClockBtn = document.getElementById('workspace-clock-btn');
        const wsStatusText = section.querySelector('.setup-card h3');
        const wsTimeText = section.querySelector('.setup-card p');
        const todayStr = todayJakarta();
        
        if (currentUser) {
            const attendanceRecord = state.attendance.find(a => a.username === currentUser.username && a.date === todayStr);
            if (attendanceRecord) {
                if (attendanceRecord.status === 'Present') {
                    if (wsClockBtn) {
                        wsClockBtn.innerHTML = '<i class="ph ph-sign-out"></i> Clock Out';
                        wsClockBtn.style.background = 'rgba(239, 68, 68, 0.15)';
                        wsClockBtn.style.color = 'var(--danger)';
                        wsClockBtn.disabled = false;
                    }
                    if (wsStatusText) wsStatusText.innerText = 'Status: Clocked In';
                    if (wsTimeText) wsTimeText.innerHTML = `<i class="ph ph-clock"></i> ${attendanceRecord.time} (Server Time) &bull; <i class="ph ph-map-pin"></i> ${attendanceRecord.type}`;
                } else if (attendanceRecord.status === 'Clocked Out') {
                    if (wsClockBtn) {
                        wsClockBtn.innerHTML = '<i class="ph ph-check"></i> Clocked Out';
                        wsClockBtn.style.background = 'rgba(255,255,255,0.05)';
                        wsClockBtn.style.color = 'var(--text-muted)';
                        wsClockBtn.disabled = true;
                    }
                    if (wsStatusText) wsStatusText.innerText = 'Status: Clocked Out';
                    if (wsTimeText) wsTimeText.innerHTML = `<i class="ph ph-clock"></i> Out at ${attendanceRecord.timeOut || '17:00'}`;
                }
            } else {
                if (wsClockBtn) {
                    wsClockBtn.innerHTML = '<i class="ph ph-sign-in"></i> Clock In';
                    wsClockBtn.style.background = 'rgba(52, 199, 89, 0.15)';
                    wsClockBtn.style.color = 'var(--success)';
                    wsClockBtn.disabled = false;
                }
                if (wsStatusText) wsStatusText.innerText = 'Status: Absent';
                if (wsTimeText) wsTimeText.innerHTML = '<i class="ph ph-warning-circle"></i> Belum absen hari ini.';
            }
        }
        
        const user = state.users['maulana'] || { name: 'M. Maulana Zakaria', avatar: 'MZ', title: 'Web SEO Developer', bio: '' };
        const kpi = calculateUserKPI('maulana');
        
        const header = section.querySelector('.member-profile-header');
        if (header) {
            const avatarContent = user.avatarImg ? `<img src="${user.avatarImg}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">` : user.avatar;
            header.innerHTML = `
                <div class="member-avatar" style="overflow: hidden; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">${avatarContent}</div>
                <div class="member-info">
                    <h2>${user.name}</h2>
                    <p style="font-weight: 600; color: var(--primary);">${user.title} | KPIM Score: ${kpi}% (Grade A)</p>
                    <p style="font-size: 13px; color: var(--text-secondary); margin-top: 8px; font-style: italic;">${user.bio || 'Tidak ada bio.'}</p>
                </div>
            `;
        }
        
        const container = section.querySelector('.setup-card:last-of-type') || section.querySelector('.workspace-grid > .setup-card');
        if (!container) return;
        
        const maulanaTasks = state.tasks.filter(t => t.username === 'maulana');
        
        let html = `
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h2>Daily Workspace & Reports</h2>
                <span class="badge warning" style="display: flex; align-items: center; gap: 4px;"><i class="ph ph-key"></i> Developer Workspace</span>
            </div>
        `;
        
        maulanaTasks.forEach(task => {
            const isChecked = task.status === 'done' ? 'checked' : '';
            const statusColors = {
                'done': 'background: rgba(52, 199, 89, 0.15); color: var(--success); border-color: rgba(52, 199, 89, 0.3);',
                'in_progress': 'background: rgba(10, 132, 255, 0.15); color: var(--info); border-color: rgba(10, 132, 255, 0.3);',
                'revisi': 'background: rgba(255, 159, 10, 0.15); color: var(--warning); border-color: rgba(255, 159, 10, 0.3);',
                'failed': 'background: rgba(255, 59, 48, 0.15); color: var(--danger); border-color: rgba(255, 59, 48, 0.3);'
            };
            const statusStyle = statusColors[task.status] || '';
            
            html += `
                <div class="report-item" style="border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 20px; margin-bottom: 16px; background: rgba(0,0,0,0.2);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 style="font-size: 15px; display: flex; align-items: center; gap: 12px; margin:0; color: white;">
                            <input type="checkbox" ${isChecked} disabled style="width: 20px; height: 20px; accent-color: var(--primary); cursor: default;">
                            ${task.title}
                        </h3>
                        <span style="font-size: 12px; padding: 4px 10px; border-radius: 6px; font-weight: 600; border: 1px solid; ${statusStyle}">
                            Status: ${task.status.toUpperCase()}
                        </span>
                    </div>
                    ${task.feedback ? `
                    <div style="font-size: 13px; color: var(--warning); margin-bottom: 16px; background: rgba(255, 159, 10, 0.1); padding: 12px; border-radius: var(--radius-sm); border-left: 3px solid var(--warning);">
                        <strong>Feedback Manager:</strong> ${task.feedback}
                    </div>
                    ` : ''}
                    <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 12px; color: var(--text-secondary);">Evidence Link / Reporting Notes</label>
                        <div style="display: flex; gap: 12px;">
                            <input type="text" id="evidence-input-${task.id}" value="${task.evidence || ''}" placeholder="Masukkan link drive, screenshot, dll..." style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-sm); padding: 12px; color: white; font-family: inherit; flex: 1; outline: none;" ${task.status === 'done' ? 'disabled' : ''}>
                            <button class="primary-btn submit-evidence-btn" data-taskid="${task.id}" style="padding: 10px 20px;" ${task.status === 'done' ? 'disabled' : ''}>
                                <i class="ph ph-paper-plane-right"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        container.innerHTML = html;
        
        container.querySelectorAll('.submit-evidence-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const taskId = btn.getAttribute('data-taskid');
                const input = document.getElementById(`evidence-input-${taskId}`);
                const link = input ? input.value.trim() : '';
                if (!link) return showPremiumNotice('Bukti Belum Diisi', 'Masukkan tautan bukti kerja sebelum mengirim.', { variant: 'danger' });
                
                const task = state.tasks.find(t => t.id === taskId);
                if (task) {
                    task.evidence = link;
                    task.status = 'in_progress';
                    updateState(state);
                    showPremiumNotice('Bukti Berhasil Dikirim', 'Bukti kerja telah dikirim kepada Manager untuk ditinjau.', { variant: 'success' });
                }
            });
        });
        
        const kpiPane = section.querySelector('.member-kpi-list');
        if (kpiPane) {
            kpiPane.innerHTML = `
                <div class="list-header" style="grid-template-columns: 2fr 1fr;">
                    <div class="col-indicator">Indicator Summary</div>
                    <div class="col-achieve" style="text-align: right;">Status</div>
                </div>
            `;
            maulanaTasks.forEach(task => {
                const statusBadges = {
                    'done': '<span class="badge success">Done</span>',
                    'in_progress': '<span class="badge info">Pending</span>',
                    'revisi': '<span class="badge warning">Revisi</span>',
                    'failed': '<span class="badge danger">Gagal</span>',
                    'nearing_deadline': '<span class="badge warning">Warning</span>'
                };
                kpiPane.innerHTML += `
                    <div class="list-row" style="grid-template-columns: 2fr 1fr;">
                        <div class="col-indicator">${task.title}</div>
                        <div class="col-achieve" style="text-align: right;">${statusBadges[task.status] || ''}</div>
                    </div>
                `;
            });
        }
    }

    // ================= D Best Workspace Renderer =================
    function renderDBestWorkspace() {
        const section = document.getElementById('view-member-creator');
        if (!section) return;
        
        const user = state.users['dbest'] || { name: 'D BEST AR', avatar: 'DB', title: 'Content Creator', bio: '' };
        const kpi = calculateUserKPI('dbest');
        
        const header = section.querySelector('.member-profile-header');
        if (header) {
            const avatarContent = user.avatarImg ? `<img src="${user.avatarImg}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">` : user.avatar;
            header.innerHTML = `
                <div class="member-avatar" style="overflow: hidden; width: 80px; height: 80px; display: flex; align-items: center; justify-content: center;">${avatarContent}</div>
                <div class="member-info">
                    <h2>${user.name}</h2>
                    <p style="font-weight: 600; color: var(--primary);">${user.title} | KPIM Score: ${kpi}% (Grade B)</p>
                    <p style="font-size: 13px; color: var(--text-secondary); margin-top: 8px; font-style: italic;">${user.bio || 'Tidak ada bio.'}</p>
                </div>
            `;
        }
        
        const dbestTasks = state.tasks.filter(t => t.username === 'dbest');
        
        let workspaceHtml = `
            <div class="setup-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-lg); padding: 32px; margin-top: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                    <h2>Daily Workspace & Reports</h2>
                    <span class="badge warning" style="display: flex; align-items: center; gap: 4px;"><i class="ph ph-key"></i> Creator Workspace</span>
                </div>
        `;
        
        dbestTasks.forEach(task => {
            const isChecked = task.status === 'done' ? 'checked' : '';
            const statusColors = {
                'done': 'background: rgba(52, 199, 89, 0.15); color: var(--success); border-color: rgba(52, 199, 89, 0.3);',
                'in_progress': 'background: rgba(10, 132, 255, 0.15); color: var(--info); border-color: rgba(10, 132, 255, 0.3);',
                'revisi': 'background: rgba(255, 159, 10, 0.15); color: var(--warning); border-color: rgba(255, 159, 10, 0.3);',
                'failed': 'background: rgba(255, 59, 48, 0.15); color: var(--danger); border-color: rgba(255, 59, 48, 0.3);'
            };
            const statusStyle = statusColors[task.status] || '';
            
            workspaceHtml += `
                <div class="report-item" style="border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 20px; margin-bottom: 16px; background: rgba(0,0,0,0.2);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                        <h3 style="font-size: 15px; display: flex; align-items: center; gap: 12px; margin: 0; color: white;">
                            <input type="checkbox" ${isChecked} disabled style="width: 20px; height: 20px; accent-color: var(--primary); cursor: default;">
                            ${task.title}
                        </h3>
                        <span style="font-size: 12px; padding: 4px 10px; border-radius: 6px; font-weight: 600; border: 1px solid; ${statusStyle}">
                            Status: ${task.status.toUpperCase()}
                        </span>
                    </div>
                    ${task.feedback ? `
                    <div style="font-size: 13px; color: var(--warning); margin-bottom: 16px; background: rgba(255, 159, 10, 0.1); padding: 12px; border-radius: var(--radius-sm); border-left: 3px solid var(--warning);">
                        <strong>Feedback Manager:</strong> ${task.feedback}
                    </div>
                    ` : ''}
                    <div class="form-group" style="display: flex; flex-direction: column; gap: 8px;">
                        <label style="font-size: 12px; color: var(--text-secondary);">Evidence Link / Reporting Notes</label>
                        <div style="display: flex; gap: 12px;">
                            <input type="text" id="evidence-input-${task.id}" value="${task.evidence || ''}" placeholder="Masukkan link video, google docs dll..." style="background: rgba(255,255,255,0.05); border: 1px solid var(--glass-border); border-radius: var(--radius-sm); padding: 12px; color: white; font-family: inherit; flex: 1; outline: none;" ${task.status === 'done' ? 'disabled' : ''}>
                            <button class="primary-btn submit-evidence-btn" data-taskid="${task.id}" style="padding: 10px 20px;" ${task.status === 'done' ? 'disabled' : ''}>
                                <i class="ph ph-paper-plane-right"></i> Submit
                            </button>
                        </div>
                    </div>
                </div>
            `;
        });
        
        workspaceHtml += `</div>`;
        
        let existingWorkspace = section.querySelector('.workspace-wrapper-creator');
        if (!existingWorkspace) {
            existingWorkspace = document.createElement('div');
            existingWorkspace.className = 'workspace-wrapper-creator';
            section.appendChild(existingWorkspace);
        }
        existingWorkspace.innerHTML = workspaceHtml;
        
        existingWorkspace.querySelectorAll('.submit-evidence-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const taskId = btn.getAttribute('data-taskid');
                const input = document.getElementById(`evidence-input-${taskId}`);
                const link = input ? input.value.trim() : '';
                if (!link) return showPremiumNotice('Bukti Belum Diisi', 'Masukkan tautan bukti kerja sebelum mengirim.', { variant: 'danger' });
                
                const task = state.tasks.find(t => t.id === taskId);
                if (task) {
                    task.evidence = link;
                    task.status = 'in_progress';
                    updateState(state);
                    showPremiumNotice('Bukti Berhasil Dikirim', 'Bukti kerja telah dikirim kepada Manager untuk ditinjau.', { variant: 'success' });
                }
            });
        });
        
        const kpiList = section.querySelector('.member-kpi-list');
        if (kpiList) {
            kpiList.innerHTML = `
                <div class="list-header">
                    <div class="col-indicator">Indicator</div>
                    <div class="col-target">Kategori</div>
                    <div class="col-actual">Bukti</div>
                    <div class="col-achieve">Status</div>
                </div>
            `;
            dbestTasks.forEach(task => {
                const statusBadges = {
                    'done': '<span class="badge success">Done</span>',
                    'in_progress': '<span class="badge info">Pending</span>',
                    'revisi': '<span class="badge warning">Revisi</span>',
                    'failed': '<span class="badge danger">Gagal</span>',
                    'nearing_deadline': '<span class="badge warning">Warning</span>'
                };
                kpiList.innerHTML += `
                    <div class="list-row">
                        <div class="col-indicator">${task.title}</div>
                        <div class="col-target">${task.relation}</div>
                        <div class="col-actual">${task.evidence ? 'Link Submitted' : 'No Evidence'}</div>
                        <div class="col-achieve">${statusBadges[task.status] || ''}</div>
                    </div>
                `;
            });
        }
    }

    // ================= Approval Inbox & Secure History =================
    async function renderCEOApprovalInbox() {
        const listContainer = document.getElementById('approval-list-container');
        if (!listContainer || !currentUser) return;

        const modeTabs = Array.from(document.querySelectorAll('#approval-mode-tabs .approval-mode-tab'));
        const divisionTabs = Array.from(document.querySelectorAll('#approval-inbox-tabs .filter-btn'));
        const isCeo = currentUser.role === 'ceo';
        const isManager = currentUser.role?.startsWith('mgr_');
        const ownDivision = divisionFromRole(currentUser.role);

        if (!isCeo && !isManager) {
            modeTabs.forEach(tab => tab.classList.toggle('active', tab.dataset.mode === 'history'));
        }

        let mode = modeTabs.find(tab => tab.classList.contains('active'))?.dataset.mode || 'pending';
        let currentTab = divisionTabs.find(tab => tab.classList.contains('active'))?.dataset.div || 'all';

        modeTabs.forEach(tab => {
            if (!tab.dataset.listenerAdded) {
                tab.addEventListener('click', () => {
                    modeTabs.forEach(item => item.classList.remove('active'));
                    tab.classList.add('active');
                    renderCEOApprovalInbox();
                });
                tab.dataset.listenerAdded = 'true';
            }
        });

        divisionTabs.forEach(tab => {
            const tabDivision = tab.dataset.div;
            const visible = isCeo || (isManager && tabDivision === ownDivision) || (!isManager && !isCeo && tabDivision === 'all');
            tab.style.display = visible ? 'inline-flex' : 'none';
            if (!tab.dataset.listenerAdded) {
                tab.addEventListener('click', () => {
                    divisionTabs.forEach(item => item.classList.remove('active'));
                    tab.classList.add('active');
                    renderCEOApprovalInbox();
                });
                tab.dataset.listenerAdded = 'true';
            }
        });

        if (isManager && currentTab !== ownDivision) {
            currentTab = ownDivision;
            divisionTabs.forEach(tab => tab.classList.toggle('active', tab.dataset.div === ownDivision));
        } else if (!isManager && !isCeo) {
            currentTab = 'all';
            divisionTabs.forEach(tab => tab.classList.toggle('active', tab.dataset.div === 'all'));
            const allLabel = divisionTabs.find(tab => tab.dataset.div === 'all');
            if (allLabel) allLabel.childNodes[0].textContent = 'Pengajuan Saya ';
        }

        listContainer.innerHTML = '<div class="approval-loading"><i class="ph ph-spinner ph-spin"></i> Menyinkronkan data pengajuan...</div>';

        try {
            const approvals = await apiRequest(`/api/approvals${mode === 'history' ? '?history=1' : ''}`);
            const counts = { all: 0, marketing: 0, operasional: 0, finance: 0, hrd: 0 };
            approvals.forEach(approval => {
                counts.all += 1;
                const division = (approval.division || '').toLowerCase();
                if (Object.hasOwn(counts, division)) counts[division] += 1;
            });

            Object.entries(counts).forEach(([key, value]) => {
                const badge = document.getElementById(`badge-approval-${key}`);
                if (badge) badge.textContent = value;
            });

            const filtered = currentTab === 'all'
                ? approvals
                : approvals.filter(approval => (approval.division || '').toLowerCase() === currentTab);

            if (!filtered.length) {
                const labels = {
                    all: isCeo ? 'seluruh divisi' : 'akun Anda',
                    marketing: 'Divisi Marketing',
                    operasional: 'Divisi Operasional',
                    finance: 'Divisi Finance',
                    hrd: 'Divisi HRD'
                };
                const historyMessage = `Belum terdapat riwayat keputusan untuk ${labels[currentTab] || currentTab}. Setiap pengajuan yang disetujui atau ditolak akan terdokumentasi otomatis di halaman ini.`;
                const pendingMessage = `Saat ini belum ada pengajuan dari ${labels[currentTab] || currentTab} yang memerlukan keputusan. Pengajuan baru akan muncul otomatis setelah dikirim melalui alur persetujuan.`;
                listContainer.innerHTML = `
                    <div class="approval-empty-state">
                        <div class="approval-empty-icon"><i class="ph ph-${mode === 'history' ? 'archive' : 'check-circle'}"></i></div>
                        <strong>${mode === 'history' ? 'Riwayat keputusan belum tersedia' : 'Tidak ada pengajuan yang menunggu'}</strong>
                        <p>${mode === 'history' ? historyMessage : pendingMessage}</p>
                    </div>`;
                return;
            }

            listContainer.innerHTML = filtered.map(approval => {
                const requesterName = escapeHtml(approval.requester?.name || 'Pengguna');
                const typeLabel = escapeHtml(approval.type_label || approval.request_type);
                const details = approval.details || {};
                const statusLabels = {
                    approved: 'Disetujui',
                    rejected: 'Ditolak',
                    cancelled: 'Dibatalkan',
                    pending_manager: 'Menunggu Manager',
                    pending_ceo: 'Menunggu CEO'
                };
                const statusClass = approval.status === 'approved'
                    ? 'success'
                    : (approval.status === 'rejected' ? 'danger' : 'warning');
                let detailsHtml = '';

                if (approval.request_type === 'leave') {
                    detailsHtml = `${escapeHtml(details.type || 'Cuti')} Â· ${escapeHtml(details.start_date || '-')} s/d ${escapeHtml(details.end_date || '-')}<br><span>${escapeHtml(details.reason || '')}</span>`;
                } else if (approval.request_type === 'team_request') {
                    const action = details.action === 'delete' ? 'Penghapusan staf' : 'Penambahan staf';
                    const target = details.target_username || details.new_staff?.username || '-';
                    const jobTitle = details.new_staff?.job_title ? ` Â· ${escapeHtml(details.new_staff.job_title)}` : '';
                    const separation = details.separation || {};
                    const completionLabels = {
                        completed: 'Pekerjaan selesai / serah terima tuntas',
                        incomplete: 'Pekerjaan belum selesai / perlu tindak lanjut',
                    };
                    const reasonLabels = {
                        completed: 'Masa kerja atau kontrak selesai',
                        resigned: 'Mengundurkan diri',
                        terminated: 'Diberhentikan perusahaan',
                        other: 'Alasan lain',
                    };
                    detailsHtml = `${action}: <b>@${escapeHtml(target)}</b>${jobTitle}`;
                    if (details.action === 'delete') {
                        detailsHtml += `
                            <br><span>Status: ${escapeHtml(completionLabels[separation.completion_status] || '-')}</span>
                            <br><span>Alasan: ${escapeHtml(reasonLabels[separation.separation_reason] || '-')} Â· Efektif ${escapeHtml(separation.effective_date || '-')}</span>
                            ${separation.separation_notes ? `<br><span>Catatan: ${escapeHtml(separation.separation_notes)}</span>` : ''}
                        `;
                    }
                } else if (approval.request_type === 'kpi_plan') {
                    const kpis = (details.kpis || []).map(kpi => `${escapeHtml(kpi.title)} (${Number(kpi.weight)}%)`).join(', ');
                    detailsHtml = `Acuan / Fokus KPI: <b>${escapeHtml(details.goal || '-')}</b><br><span>${kpis}</span>`;
                } else if (approval.request_type === 'task') {
                    detailsHtml = `${escapeHtml(details.title || '-')} Â· KPI: ${escapeHtml(details.kpi || '-')}`;
                } else if (approval.request_type === 'data_deletion') {
                    const modeLabels = {
                        soft_delete: 'Arsip terkontrol',
                        redact: 'Redaksi pesan',
                        revoke: 'Pencabutan dokumen',
                        reverse: 'Pembalikan jurnal',
                        reverse_and_delete: 'Pembalikan transaksi dan arsip'
                    };
                    detailsHtml = `
                        <b>${escapeHtml(details.target_label || 'Data')}</b><br>
                        <span>Jenis: ${escapeHtml(details.resource_type || '-')} Â· Proses: ${escapeHtml(modeLabels[details.deletion_mode] || details.deletion_mode || '-')}</span><br>
                        <span>Alasan: ${escapeHtml(details.reason || '-')}</span>
                    `;
                }
                if (approval.request_type === 'resignation') {
                    detailsHtml = `Hari kerja terakhir: <b>${escapeHtml(details.last_working_date || '-')}</b><br><span>${escapeHtml(details.reason || '')}</span>${details.handover_notes ? `<br><span>Serah terima: ${escapeHtml(details.handover_notes)}</span>` : ''}`;
                }

                const decisionsHtml = mode === 'history'
                    ? (approval.decisions || []).filter(step => step.status !== 'pending').map(step => `
                        <div class="approval-decision-row">
                            <i class="ph ph-${step.status === 'approved' ? 'check-circle' : 'x-circle'}"></i>
                            <div>
                                <b>${escapeHtml(step.stage)} Â· ${step.status === 'approved' ? 'Disetujui' : 'Ditolak'}</b>
                                <span>${escapeHtml(step.approver?.name || 'Pejabat berwenang')} Â· ${formatApprovalDate(step.decided_at)}</span>
                                ${step.note ? `<p>${escapeHtml(step.note)}</p>` : ''}
                            </div>
                        </div>`).join('')
                    : '';

                return `
                    <article class="approval-card ${mode === 'history' ? 'history' : ''}">
                        <div class="approval-card-main">
                            <div class="member-avatar approval-avatar">${requesterName.substring(0, 2).toUpperCase()}</div>
                            <div class="approval-card-copy">
                                <div class="approval-card-title-row">
                                    <h4>${typeLabel} oleh ${requesterName}</h4>
                                    <span class="approval-status-pill ${statusClass}">${statusLabels[approval.status] || escapeHtml(approval.status)}</span>
                                </div>
                                <p>Divisi ${escapeHtml(approval.division || 'Perusahaan')} Â· Diajukan ${formatApprovalDate(approval.submitted_at)}</p>
                                <div class="approval-details">${detailsHtml}</div>
                                ${decisionsHtml ? `<div class="approval-decisions">${decisionsHtml}</div>` : ''}
                            </div>
                        </div>
                        ${mode === 'pending' && approval.actionable ? `
                            <div class="approval-actions">
                                <button class="reject-api-btn" data-id="${approval.id}"><i class="ph ph-x"></i> Tolak</button>
                                <button class="approve-api-btn" data-id="${approval.id}"><i class="ph ph-check"></i> Setujui</button>
                            </div>` : ''}
                    </article>`;
            }).join('');

            listContainer.querySelectorAll('.approve-api-btn').forEach(button => {
                button.addEventListener('click', () => {
                    showCustomConfirm('Konfirmasi Persetujuan', 'Setujui pengajuan ini dan lanjutkan sinkronisasi ke seluruh level terkait?', async () => {
                        try {
                            const result = await apiRequest(`/api/approvals/${button.dataset.id}/approve`, {
                                method: 'POST',
                                body: { note: 'Disetujui melalui Pusat Persetujuan' }
                            });
                            modeTabs.forEach(tab => tab.classList.toggle('active', tab.dataset.mode === 'history'));
                            await syncDataFromServer();
                            await renderCEOApprovalInbox();
                            showPremiumNotice('Pengajuan Berhasil Diproses', escapeHtml(result.message), { variant: 'success' });
                        } catch (error) {
                            showPremiumNotice('Tidak Dapat Memproses', escapeHtml(error.message), { variant: 'danger' });
                        }
                    }, { confirmText: 'Ya, Setujui', cancelText: 'Batal', variant: 'primary' });
                });
            });

            listContainer.querySelectorAll('.reject-api-btn').forEach(button => {
                button.addEventListener('click', () => {
                    showTextInputDialog({
                        title: 'Alasan Penolakan',
                        description: 'Catatan ini akan terlihat oleh pengaju dan menjadi bagian dari riwayat keputusan.',
                        label: 'Alasan penolakan',
                        placeholder: 'Jelaskan alasan secara jelas dan profesional...',
                        submitText: 'Tolak Pengajuan'
                    }, async reason => {
                        try {
                            const result = await apiRequest(`/api/approvals/${button.dataset.id}/reject`, {
                                method: 'POST',
                                body: { note: reason }
                            });
                            modeTabs.forEach(tab => tab.classList.toggle('active', tab.dataset.mode === 'history'));
                            await syncDataFromServer();
                            await renderCEOApprovalInbox();
                            showPremiumNotice('Pengajuan Ditolak', escapeHtml(result.message), { variant: 'success' });
                        } catch (error) {
                            showPremiumNotice('Tidak Dapat Memproses', escapeHtml(error.message), { variant: 'danger' });
                        }
                    });
                });
            });
        } catch (error) {
            listContainer.innerHTML = `<div class="approval-error-state"><i class="ph ph-warning-circle"></i> ${escapeHtml(error.message)}</div>`;
        }
    }

    function formatApprovalDate(value) {
        if (!value) return '-';
        const date = new Date(value);
        if (Number.isNaN(date.getTime())) return '-';
        return new Intl.DateTimeFormat('id-ID', {
            timeZone: JAKARTA_TIMEZONE,
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    }

    // ================= KPI Proposals Renderer =================
    function renderKPIProposals() {
        const list = document.querySelector('#view-ceo .proposal-list');
        if (!list) return;

        {
            const pendingPlans = (state.kpiPlans || []).filter(plan => plan.status === 'pending_ceo');

            if (pendingPlans.length === 0) {
                list.innerHTML = '<div style="text-align: center; color: var(--text-secondary); padding: 20px; border: 1px dashed var(--glass-border); border-radius: 8px; width: 100%;">Belum ada rencana KPI manager yang memerlukan persetujuan CEO.</div>';
                return;
            }

            list.innerHTML = pendingPlans.map(plan => {
                const items = (plan.kpis || [])
                    .map(item => `${escapeHtml(item.title)} (${Number(item.weight || 0)}%)`)
                    .join(', ');

                return `
                    <div class="approval-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 20px; display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap; width: 100%; margin-bottom: 12px;">
                        <div>
                            <h4 style="font-size: 16px; margin: 0 0 6px; color: white;">${escapeHtml(plan.title || 'Rencana KPI Divisi')}</h4>
                            <p style="color: var(--text-secondary); font-size: 13px; margin: 0;">
                                ${escapeHtml(plan.manager?.name || plan.manager?.username || 'Manager')} Â·
                                ${escapeHtml(plan.title || plan.goal?.title || 'Usulan KPI mandiri')} Â· ${items}
                            </p>
                        </div>
                        <button class="primary-btn open-central-approval" style="background: var(--success); color: #03120a;">
                            <i class="ph ph-check-circle"></i> Tinjau di Pusat Persetujuan
                        </button>
                    </div>
                `;
            }).join('');

            list.querySelectorAll('.open-central-approval').forEach(button => {
                button.onclick = () => switchView('approval');
            });
            return;
        }
        
        list.innerHTML = '';
        const pendingProps = state.kpiProposals.filter(p => p.status === 'pending');
        
        if (pendingProps.length === 0) {
            list.innerHTML = '<div style="text-align: center; color: var(--text-secondary); padding: 20px; border: 1px dashed var(--glass-border); border-radius: 8px; width: 100%;">Tidak ada proposal KPI baru dari Manager saat ini.</div>';
            return;
        }
        
        pendingProps.forEach(prop => {
            list.innerHTML += `
                <div class="approval-card" style="background: var(--bg-card); border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 20px; display: flex; justify-content: space-between; align-items: center; gap: 20px; flex-wrap: wrap; width: 100%; margin-bottom: 12px;">
                    <div class="approval-info" style="display: flex; gap: 16px; align-items: center;">
                        <div class="member-avatar" style="width: 48px; height: 48px; font-size: 16px; border-radius: var(--radius-sm); background: linear-gradient(135deg, var(--warning), var(--primary));">Mkt</div>
                        <div>
                            <h4 style="font-size: 16px; margin-bottom: 4px; color: white;">Proposed KPI: ${prop.title}</h4>
                            <p style="color: var(--text-secondary); font-size: 13px; margin: 0;">Diusulkan oleh <b>${prop.proposedBy}</b> &bull; Target: ${prop.target} &bull; Bobot: ${prop.weight}% &bull; Staf: ${prop.username}</p>
                            <div style="font-size: 12px; color: var(--warning); margin-top: 8px; background: rgba(255, 159, 10, 0.1); padding: 8px; border-radius: 4px;">
                                <strong>Justifikasi:</strong> "${prop.justification}"
                            </div>
                        </div>
                    </div>
                    <div class="approval-actions" style="display: flex; gap: 12px; align-items: center;">
                        <button class="icon-btn reject-prop-btn" data-propid="${prop.id}" style="color: var(--danger); border-color: rgba(255, 59, 48, 0.3);"><i class="ph ph-x"></i></button>
                        <button class="primary-btn approve-prop-btn" data-propid="${prop.id}" style="background: var(--success); box-shadow: 0 4px 15px rgba(52, 199, 89, 0.3);"><i class="ph ph-check"></i> Approve</button>
                    </div>
                </div>
            `;
        });
        
        list.querySelectorAll('.approve-prop-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const propId = btn.getAttribute('data-propid');
                const prop = state.kpiProposals.find(p => p.id === propId);
                if (prop) {
                    const newTask = {
                        id: 'task-' + Date.now(),
                        username: prop.username,
                        title: prop.title + ` (Target: ${prop.target})`,
                        status: 'in_progress',
                        deadline: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString(),
                        relation: prop.title,
                        evidence: ''
                    };
                    state.tasks.push(newTask);
                    
                    if (!state.kpiConfig[prop.username]) state.kpiConfig[prop.username] = [];
                    state.kpiConfig[prop.username].push({
                        id: 'kpi-' + Date.now(),
                        name: prop.title,
                        weight: prop.weight
                    });

                    prop.status = 'approved';
                    updateState(state);
                    showPremiumNotice('Proposal KPI Disetujui', `Proposal â€œ${escapeHtml(prop.title)}â€ telah terintegrasi ke KPI dinamis dan checklist staf.`, { variant: 'success' });
                    renderAll();
                }
            });
        });
        
        list.querySelectorAll('.reject-prop-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const propId = btn.getAttribute('data-propid');
                const prop = state.kpiProposals.find(p => p.id === propId);
                if (prop) {
                    prop.status = 'rejected';
                    updateState(state);
                    showPremiumNotice('Proposal KPI Ditolak', `Proposal â€œ${escapeHtml(prop.title)}â€ telah ditolak oleh CEO.`, { variant: 'danger' });
                    renderAll();
                }
            });
        });
    }

    // ================= KPI Builder Form Handler =================
    const setupForm = document.getElementById('goal-kpi-builder-form');

    function configureGoalKpiBuilder() {
        if (!setupForm || !currentUser) return;

        const isCeo = currentUser.role === 'ceo';
        const isManager = currentUser.role.startsWith('mgr_');
        const title = document.getElementById('goal-kpi-builder-title');
        const roleSelect = document.getElementById('kpi-setup-role-select');
        const goalGroup = document.getElementById('kpi-goal-select-group');
        const goalSelect = document.getElementById('kpi-goal-select');
        const planTitleGroup = document.getElementById('kpi-plan-title-group');
        const supportingFileGroup = document.getElementById('kpi-supporting-file-group');
        const planTitleInput = document.getElementById('kpi-plan-title');
        const draft = document.getElementById('kpi-plan-draft');
        const createGoalButton = document.getElementById('btn-create-division-goal');
        const addKpiButton = document.getElementById('btn-add-kpi-draft');
        const submitPlanButton = document.getElementById('btn-submit-kpi-plan');

        if (title) title.innerText = isCeo ? 'Tetapkan Goal Divisi' : 'Susun & Ajukan Rencana KPI';
        if (goalGroup) goalGroup.style.display = isManager ? 'flex' : 'none';
        if (planTitleGroup) planTitleGroup.style.display = isManager ? 'flex' : 'none';
        if (supportingFileGroup) supportingFileGroup.style.display = isManager ? 'flex' : 'none';
        if (draft) draft.style.display = isManager ? 'block' : 'none';
        if (createGoalButton) createGoalButton.style.display = isCeo ? 'flex' : 'none';
        if (addKpiButton) addKpiButton.style.display = isManager ? 'flex' : 'none';
        if (submitPlanButton) submitPlanButton.style.display = isManager ? 'flex' : 'none';
        if (roleSelect) roleSelect.disabled = isManager;

        if (goalSelect && isManager) {
            const selected = goalSelect.value;
            const division = divisionFromRole(currentUser.role);
            const goals = (state.goals || []).filter(goal => goal.status === 'active' && goal.division === division);
            goalSelect.innerHTML = '<option value="">Tanpa goal CEO â€” ajukan KPI mandiri</option>';
            goals.forEach(goal => {
                goalSelect.innerHTML += `<option value="${goal.id}" data-title="${escapeHtml(goal.title)}">${escapeHtml(goal.title)} (${goal.year})</option>`;
            });
            if (selected && goals.some(goal => String(goal.id) === String(selected))) {
                goalSelect.value = selected;
            }
            goalSelect.onchange = () => {
                const selectedTitle = goalSelect.selectedOptions[0]?.dataset.title || '';
                if (planTitleInput && selectedTitle && !planTitleInput.value.trim()) {
                    planTitleInput.value = selectedTitle;
                }
            };
        }

        renderKpiPlanDraft();
        updateKpiFormulaPreview();
    }

    function calculateKpiFormula(current, target, weight, direction = 'higher_is_better') {
        const safeTarget = Number(target || 0);
        const safeCurrent = Math.max(0, Number(current || 0));
        const safeWeight = Math.max(0, Number(weight || 0));
        let achievement = 0;
        if (safeTarget > 0) {
            achievement = direction === 'lower_is_better'
                ? (safeCurrent <= safeTarget ? 100 : Math.min(100, (safeTarget / Math.max(safeCurrent, .0001)) * 100))
                : Math.min(100, Math.max(0, (safeCurrent / safeTarget) * 100));
        }
        return {
            achievement,
            contribution: achievement * safeWeight / 100
        };
    }

    function updateKpiFormulaPreview() {
        const current = Number(document.getElementById('kpi-current-value-preview')?.value || 0);
        const target = Number(document.getElementById('kpi-target-value')?.value || 0);
        const weight = Number(document.getElementById('kpi-weight')?.value || 0);
        const direction = document.getElementById('kpi-direction')?.value || 'higher_is_better';
        const formula = calculateKpiFormula(current, target, weight, direction);
        const achievement = document.getElementById('kpi-achievement-preview');
        const contribution = document.getElementById('kpi-contribution-preview');
        const formulaLabel = document.getElementById('kpi-formula-preview');
        if (achievement) achievement.textContent = `${formula.achievement.toFixed(2)}%`;
        if (contribution) contribution.textContent = formula.contribution.toFixed(2);
        if (formulaLabel) {
            formulaLabel.textContent = direction === 'lower_is_better'
                ? `MIN(100%; target / realisasi) Ã— bobot`
                : `MIN(100%; realisasi / target) Ã— bobot`;
        }
    }

    ['kpi-current-value-preview', 'kpi-target-value', 'kpi-weight', 'kpi-direction'].forEach(id => {
        document.getElementById(id)?.addEventListener('input', updateKpiFormulaPreview);
        document.getElementById(id)?.addEventListener('change', updateKpiFormulaPreview);
    });

    function renderGovernedPerformanceData() {
        const container = document.getElementById('governed-performance-data-list');
        if (!container || !currentUser) return;

        const canManage = currentUser.role === 'ceo' || currentUser.role.startsWith('mgr_');
        const goals = state.goals || [];
        const plans = state.kpiPlans || [];

        if (!goals.length && !plans.length) {
            container.innerHTML = `
                <div class="approval-empty-state">
                    <strong>Belum ada data goal dan KPI</strong>
                    <p>Goal dan rencana KPI yang dapat Anda akses akan tampil otomatis di sini.</p>
                </div>`;
            return;
        }

        const goalRows = goals.map(goal => `
            <article class="governed-data-row">
                <div>
                    <strong><i class="ph ph-target"></i> ${escapeHtml(goal.title)}</strong>
                    <span>${escapeHtml(goal.division || 'Perusahaan')} Â· ${escapeHtml(String(goal.year || '-'))} Â· ${escapeHtml(goal.status || '-')}</span>
                </div>
                ${currentUser.role === 'ceo' ? `
                    <div class="governed-row-actions">
                    <button type="button" class="erp-edit-btn" data-edit-goal="${goal.id}" title="Edit goal"><i class="ph ph-pencil-simple"></i> Edit</button>
                    <button type="button" class="erp-delete-btn" data-erp-delete
                        data-resource-type="goal"
                        data-resource-id="${goal.id}"
                        data-resource-label="Goal ${escapeHtml(goal.title)}">
                        <i class="ph ph-trash"></i> Hapus
                    </button></div>` : ''}
            </article>
        `).join('');

        const planRows = plans.map(plan => {
            const goalTitle = plan.title || plan.goal?.title || 'Rencana KPI Mandiri';
            const kpiRows = (plan.kpis || []).map(kpi => `
                <div class="governed-data-child">
                    <span><i class="ph ph-chart-line-up"></i> ${escapeHtml(kpi.title)} Â· Bobot ${Number(kpi.weight || 0)}%</span>
                    ${canManage ? `
                        <span class="governed-kpi-score">
                            ${kpi.can_score ? `<input type="number" min="0" step="0.01" value="${Number(kpi.current_value || 0)}" data-kpi-score-input="${kpi.id}"><button type="button" data-save-kpi-score="${kpi.id}" title="Hitung ulang"><i class="ph ph-calculator"></i></button>` : `<b>${Number(kpi.current_value || 0).toLocaleString('id-ID')}</b>`}
                            <small>${Number(kpi.achievement || 0).toFixed(1)}% Ã— ${Number(kpi.weight || 0)}% = <b>${Number(kpi.weighted_score || 0).toFixed(2)}</b></small>
                        </span>
                        <button type="button" class="erp-delete-btn icon-only" data-erp-delete
                            data-resource-type="kpi"
                            data-resource-id="${kpi.id}"
                            data-resource-label="KPI ${escapeHtml(kpi.title)}"
                            title="Hapus atau ajukan penghapusan KPI">
                            <i class="ph ph-trash"></i>
                        </button>` : ''}
                </div>
            `).join('');

            return `
                <article class="governed-data-group">
                    <div class="governed-data-row">
                        <div>
                            <strong><i class="ph ph-list-checks"></i> Rencana KPI Â· ${escapeHtml(goalTitle)}</strong>
                            <span>${escapeHtml(plan.manager?.name || 'Manager')} Â· ${escapeHtml(plan.status || '-')}</span>
                        </div>
                        ${canManage ? `
                            <div class="governed-row-actions">
                            ${plan.can_edit ? `<button type="button" class="erp-edit-btn" data-edit-kpi-plan="${plan.id}"><i class="ph ph-pencil-simple"></i> Edit</button>` : `<span class="locked-data-pill" title="${escapeHtml(plan.edit_lock_reason || '')}"><i class="ph ph-lock-key"></i> Terkunci</span>`}
                            <button type="button" class="erp-delete-btn" data-erp-delete
                                data-resource-type="kpi_plan"
                                data-resource-id="${plan.id}"
                                data-resource-label="Rencana KPI ${escapeHtml(goalTitle)}">
                                <i class="ph ph-trash"></i> Hapus / Ajukan
                            </button></div>` : ''}
                    </div>
                    ${(plan.attachments || []).length ? `<div class="governed-attachments">${plan.attachments.map(file => `<a href="${escapeHtml(file.download_url)}" target="_blank" rel="noopener"><i class="ph ph-paperclip"></i> ${escapeHtml(file.name)}</a>`).join('')}</div>` : ''}
                    ${kpiRows ? `<div class="governed-data-children">${kpiRows}</div>` : ''}
                </article>
            `;
        }).join('');

        container.innerHTML = goalRows + planRows;
        container.querySelectorAll('[data-edit-goal]').forEach(button => {
            button.onclick = () => editGoalFromSheet(Number(button.dataset.editGoal));
        });
        container.querySelectorAll('[data-edit-kpi-plan]').forEach(button => {
            button.onclick = () => loadKpiPlanForEditing(Number(button.dataset.editKpiPlan));
        });
        container.querySelectorAll('[data-save-kpi-score]').forEach(button => {
            button.onclick = async () => {
                const id = Number(button.dataset.saveKpiScore);
                const input = container.querySelector(`[data-kpi-score-input="${id}"]`);
                try {
                    const result = await apiRequest(`/api/kpis/${id}/score`, {
                        method: 'PATCH',
                        body: { current_value: Number(input?.value || 0) }
                    });
                    showPremiumNotice('KPI Dihitung Ulang', escapeHtml(result.message), { variant: 'success' });
                    await syncDataFromServer();
                } catch (error) {
                    showPremiumNotice('Nilai KPI Ditolak', escapeHtml(error.message), { variant: 'danger' });
                }
            };
        });
    }

    function editGoalFromSheet(goalId) {
        const goal = (state.goals || []).find(item => Number(item.id) === Number(goalId));
        if (!goal) return;
        showTextInputDialog({
            title: 'Edit Goal Divisi',
            description: `Perbarui judul goal ${goal.division}. Manager divisi akan menerima notifikasi perubahan.`,
            label: 'Judul goal',
            defaultValue: goal.title,
            submitText: 'Simpan Perubahan'
        }, async title => {
            try {
                const result = await apiRequest(`/api/goals/${goal.id}`, {
                    method: 'PUT',
                    body: {
                        title,
                        description: goal.description || null,
                        division: goal.division,
                        year: Number(goal.year),
                        status: goal.status || 'active'
                    }
                });
                showPremiumNotice('Goal Diperbarui', escapeHtml(result.message), { variant: 'success' });
                await syncDataFromServer();
            } catch (error) {
                showPremiumNotice('Goal Tidak Dapat Diedit', escapeHtml(error.message), { variant: 'danger' });
            }
        });
    }

    function loadKpiPlanForEditing(planId) {
        const plan = (state.kpiPlans || []).find(item => Number(item.id) === Number(planId));
        if (!plan?.can_edit) return;
        kpiPlanDraft = (plan.kpis || []).map(kpi => ({
            title: kpi.title,
            target_value: Number(kpi.target_value),
            current_value: Number(kpi.current_value || 0),
            unit: kpi.unit,
            weight: Number(kpi.weight),
            direction: kpi.direction,
            aggregation_type: kpi.aggregation_type,
            data_source: kpi.data_source
        }));
        const planTitle = document.getElementById('kpi-plan-title');
        const goalSelect = document.getElementById('kpi-goal-select');
        const submit = document.getElementById('btn-submit-kpi-plan');
        if (planTitle) planTitle.value = plan.title || '';
        if (goalSelect) goalSelect.value = plan.goal_id || '';
        if (submit) {
            submit.dataset.editPlanId = String(plan.id);
            submit.innerHTML = '<i class="ph ph-floppy-disk"></i> Simpan Revisi & Kirim ke CEO';
        }
        renderKpiPlanDraft();
        document.querySelector('.kpi-sheet-card')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function measurementConfiguration(type) {
        if (type === 'currency') return { unit: 'Rp', aggregation_type: 'sum' };
        if (type === 'percentage') return { unit: '%', aggregation_type: 'percentage' };
        return { unit: 'item', aggregation_type: 'count' };
    }

    function readKpiBuilderFields() {
        const measurement = measurementConfiguration(document.getElementById('kpi-measurement-type')?.value || 'count');
        const dataSource = document.getElementById('kpi-data-source')?.value || 'manual';
        return {
            title: document.getElementById('kpi-indicator-name')?.value.trim() || '',
            target_value: Number(document.getElementById('kpi-target-value')?.value || 0),
            weight: Number(document.getElementById('kpi-weight')?.value || 0),
            direction: document.getElementById('kpi-direction')?.value || 'higher_is_better',
            data_source: dataSource,
            notes: document.getElementById('kpi-proposal-notes')?.value.trim() || '',
            unit: measurement.unit,
            aggregation_type: dataSource === 'manual' ? 'manual' : measurement.aggregation_type
        };
    }

    function clearKpiEntryFields() {
        ['kpi-indicator-name', 'kpi-target-value', 'kpi-weight', 'kpi-proposal-notes'].forEach(id => {
            const input = document.getElementById(id);
            if (input) input.value = '';
        });
        const current = document.getElementById('kpi-current-value-preview');
        if (current) current.value = '0';
        updateKpiFormulaPreview();
    }

    function renderKpiPlanDraft() {
        const list = document.getElementById('kpi-plan-draft-list');
        const totalElement = document.getElementById('kpi-plan-weight-total');
        if (!list || !totalElement) return;

        const total = kpiPlanDraft.reduce((sum, kpi) => sum + Number(kpi.weight), 0);
        list.classList.add('kpi-sheet-draft-list');
        list.innerHTML = kpiPlanDraft.length === 0
            ? '<div style="font-size: 12px; color: var(--text-muted);">Belum ada KPI dalam rencana. Tambahkan beberapa KPI hingga total bobot tepat 100%.</div>'
            : `<div class="kpi-sheet-row header">
                <span>Indikator</span><span>Target</span><span>Satuan</span><span>Bobot</span><span>Pencapaian</span><span>Kontribusi</span><span></span>
               </div>`;

        kpiPlanDraft.forEach((kpi, index) => {
            const formula = calculateKpiFormula(Number(kpi.current_value || 0), kpi.target_value, kpi.weight, kpi.direction);
            list.innerHTML += `
                <div class="kpi-sheet-row">
                    <strong>${escapeHtml(kpi.title)}</strong>
                    <span>${Number(kpi.target_value).toLocaleString('id-ID')}</span>
                    <span>${escapeHtml(kpi.unit)}</span>
                    <span>${Number(kpi.weight)}%</span>
                    <span class="formula-value">${formula.achievement.toFixed(2)}%</span>
                    <span class="formula-value">${formula.contribution.toFixed(2)}</span>
                    <button type="button" class="icon-btn remove-kpi-draft" data-index="${index}" title="Hapus KPI"><i class="ph ph-trash"></i></button>
                </div>`;
        });

        totalElement.innerText = `Total bobot: ${total}% ${Math.abs(total - 100) < 0.001 ? 'âœ“ Siap diajukan' : 'â€” harus tepat 100%'}`;
        totalElement.style.color = Math.abs(total - 100) < 0.001 ? 'var(--success)' : 'var(--warning)';

        list.querySelectorAll('.remove-kpi-draft').forEach(button => {
            button.onclick = () => {
                kpiPlanDraft.splice(Number(button.dataset.index), 1);
                renderKpiPlanDraft();
            };
        });
    }

    document.getElementById('btn-create-division-goal')?.addEventListener('click', async () => {
        const fields = readKpiBuilderFields();
        const managerUsername = document.getElementById('kpi-setup-role-select')?.value || '';
        const manager = state.users?.[managerUsername];
        const division = divisionFromRole(manager?.role || managerUsername);

        if (!fields.title || !fields.target_value || !division) {
            showPremiumNotice('Data Belum Lengkap', 'Pilih manager tujuan, isi nama goal, dan target awal.');
            return;
        }

        try {
            const result = await apiRequest('/api/goals', {
                method: 'POST',
                body: {
                    title: fields.title,
                    description: `${fields.notes || 'Goal divisi dari CEO.'} Target awal: ${fields.target_value} ${fields.unit}.`,
                    division,
                    year: new Date().getFullYear()
                }
            });
            showPremiumNotice('Goal Berhasil Ditetapkan', escapeHtml(result.message));
            clearKpiEntryFields();
            await syncDataFromServer();
            configureGoalKpiBuilder();
        } catch (error) {
            showPremiumNotice('Goal Tidak Dapat Disimpan', escapeHtml(error.message));
        }
    });

    document.getElementById('btn-add-kpi-draft')?.addEventListener('click', () => {
        const fields = readKpiBuilderFields();
        if (!fields.title || fields.target_value <= 0 || fields.weight <= 0 || fields.weight > 100) {
            showPremiumNotice('Data KPI Belum Valid', 'Isi nama KPI, target lebih dari 0, dan bobot antara 1â€“100%.');
            return;
        }

        kpiPlanDraft.push({
            title: fields.title,
            target_value: fields.target_value,
            unit: fields.unit,
            weight: fields.weight,
            direction: fields.direction,
            aggregation_type: fields.aggregation_type,
            data_source: fields.data_source,
            current_value: 0
        });
        clearKpiEntryFields();
        renderKpiPlanDraft();
    });

    document.getElementById('btn-submit-kpi-plan')?.addEventListener('click', async () => {
        const submitButton = document.getElementById('btn-submit-kpi-plan');
        const goalId = Number(document.getElementById('kpi-goal-select')?.value || 0);
        const planTitleInput = document.getElementById('kpi-plan-title');
        const planTitle = planTitleInput?.value.trim() || '';
        const total = kpiPlanDraft.reduce((sum, kpi) => sum + Number(kpi.weight), 0);

        if (!planTitle || kpiPlanDraft.length === 0 || Math.abs(total - 100) > 0.001) {
            showPremiumNotice('Rencana KPI Belum Siap', 'Isi judul rencana dan pastikan total bobot seluruh KPI tepat 100%. Goal CEO bersifat opsional.');
            return;
        }

        try {
            const formData = new FormData();
            if (goalId) formData.append('goal_id', String(goalId));
            formData.append('title', planTitle);
            formData.append('kpis', JSON.stringify(kpiPlanDraft));
            const file = document.getElementById('kpi-supporting-file')?.files?.[0];
            if (file) formData.append('supporting_file', file);
            const editingId = Number(submitButton?.dataset.editPlanId || 0);
            if (editingId) formData.append('_method', 'PUT');
            const result = await apiRequest(editingId ? `/api/kpis/plans/${editingId}` : '/api/kpis/plan', {
                method: 'POST',
                body: formData
            });
            showPremiumNotice('Rencana KPI Terkirim', escapeHtml(result.message));
            kpiPlanDraft = [];
            if (planTitleInput) planTitleInput.value = '';
            const goalSelect = document.getElementById('kpi-goal-select');
            if (goalSelect) goalSelect.value = '';
            const fileInput = document.getElementById('kpi-supporting-file');
            if (fileInput) fileInput.value = '';
            if (submitButton) {
                delete submitButton.dataset.editPlanId;
                submitButton.innerHTML = '<i class="ph ph-paper-plane-tilt"></i> Ajukan Rencana KPI ke CEO';
            }
            renderKpiPlanDraft();
            await syncDataFromServer();
        } catch (error) {
            showPremiumNotice('Rencana KPI Tidak Dapat Dikirim', escapeHtml(error.message));
        }
    });

    // ================= Financial Calculator & Payroll Renderer =================
    function calculateUserKPI(username) {
        const managerPlan = (state.serverMetrics?.plans || []).find(plan => plan.manager?.username === username);
        if (managerPlan) return Math.round(Number(managerPlan.score || 0));

        const userTasks = state.tasks.filter(t =>
            t.username === username && !['pending_manager', 'rejected', 'cancelled'].includes(t.status)
        );
        if (userTasks.length === 0) return 0;
        const verifiedCount = userTasks.filter(t => t.status === 'verified').length;
        return Math.round((verifiedCount / userTasks.length) * 100);
    }

    function calculateUserAttendanceCount(username) {
        const baseDays = username === 'maulana' ? 19 : 18;
        const liveDays = state.attendance.filter(a => a.username === username && a.status === 'Present' && a.date === '2026-07-15').length;
        return baseDays + liveDays;
    }

    function renderPayrollTable() {
        const body = document.getElementById('payroll-table-body');
        if (!body) return;
        body.innerHTML = '';
        
        const staffList = [
            { username: 'maulana', name: 'M. Maulana (Web Dev)', base: 5000000 },
            { username: 'dbest', name: 'D BEST AR (Content)', base: 4500000 }
        ];
        
        staffList.forEach(staff => {
            const kpi = calculateUserKPI(staff.username);
            const kpiBonus = Math.round(1000000 * (kpi / 100));
            const attendance = calculateUserAttendanceCount(staff.username);
            const dpointMeal = attendance * 50000;
            const total = staff.base + kpiBonus + dpointMeal;
            
            const tr = document.createElement('tr');
            tr.innerHTML = `
                <td>${staff.name}</td>
                <td>Rp ${staff.base.toLocaleString('id-ID')}</td>
                <td>Rp ${kpiBonus.toLocaleString('id-ID')} (${kpi}%)</td>
                <td>Rp ${dpointMeal.toLocaleString('id-ID')} (${attendance} Hari)</td>
                <td style="font-weight: 700; color: var(--success);">Rp ${total.toLocaleString('id-ID')}</td>
                <td><button class="primary-btn generate-slip-btn" data-username="${staff.username}" style="padding: 4px 12px; font-size: 11px;">Generate Slip</button></td>
            `;
            body.appendChild(tr);
        });
        
        body.querySelectorAll('.generate-slip-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const username = btn.getAttribute('data-username');
                openSalarySlipModal(username);
            });
        });
    }

    function openSalarySlipModal(username) {
        if (!currentUser) return;
        const isHRD = ['ceo', 'mgr_hrd', 'hrd_manager', 'hr'].includes(currentUser.role);
        if (!isHRD && currentUser.username !== username) {
            showPremiumNotice("Akses Ditolak", "Anda hanya dapat melihat slip gaji Anda sendiri.");
            return;
        }

        const user = state.users[username];
        if (!user) {
            showPremiumNotice("Tidak Ditemukan", "Data staf tidak ditemukan.");
            return;
        }
        
        const staffList = {
            'maulana': { base: 5000000 },
            'dbest': { base: 4500000 }
        };
        const details = staffList[username] || { base: 4000000 };
        
        const kpi = calculateUserKPI(username);
        const kpiBonus = Math.round(1000000 * (kpi / 100));
        const attendance = calculateUserAttendanceCount(username);
        const dpointMeal = attendance * 50000;
        const total = details.base + kpiBonus + dpointMeal;
        
        const modal = document.getElementById('salary-slip-modal');
        const content = document.getElementById('slip-content');
        if (!modal || !content) return;
        
        const currentDate = new Date().toLocaleDateString('id-ID', {
            year: 'numeric', month: 'long', day: 'numeric'
        });
        
        content.innerHTML = `
            <div style="display: flex; justify-content: space-between; font-size: 13px; color: var(--text-secondary);">
                <span>Penerima: <b>${user.name}</b></span>
                <span>Tanggal: ${currentDate}</span>
            </div>
            <div style="font-size: 13px; color: var(--text-secondary); margin-top: -8px;">
                <span>Jabatan: ${user.title}</span>
            </div>
            
            <div style="border-top: 1px solid var(--border); margin-top: 10px; padding-top: 12px; display: flex; flex-direction: column; gap: 8px;">
                <div style="display: flex; justify-content: space-between; color: white;">
                    <span>Gaji Pokok</span>
                    <span>Rp ${details.base.toLocaleString('id-ID')}</span>
                </div>
                <div style="display: flex; justify-content: space-between; color: white;">
                    <span>Bonus Performa KPI (${kpi}%)</span>
                    <span>Rp ${kpiBonus.toLocaleString('id-ID')}</span>
                </div>
                <div style="display: flex; justify-content: space-between; color: white;">
                    <span>Uang Makan D-Point (${attendance} Hari)</span>
                    <span>Rp ${dpointMeal.toLocaleString('id-ID')}</span>
                </div>
            </div>
            
            <div style="border-top: 2px solid var(--primary); margin-top: 16px; padding-top: 12px; display: flex; justify-content: space-between; font-weight: 700; font-size: 16px; color: var(--primary);">
                <span>TOTAL DITERIMA</span>
                <span>Rp ${total.toLocaleString('id-ID')}</span>
            </div>
            
            <div style="margin-top: 16px; font-size: 11px; color: var(--text-muted); text-align: center; font-style: italic;">
                "Slip gaji terverifikasi sistem ERP internal Suba-Arch."
            </div>
        `;
        
        modal.style.display = 'flex';
    }

    const closeSlipBtn = document.getElementById('close-slip-modal-btn');
    if (closeSlipBtn) {
        closeSlipBtn.onclick = () => {
            document.getElementById('salary-slip-modal').style.display = 'none';
        };
    }

    const printSlipBtn = document.getElementById('print-slip-btn');
    if (printSlipBtn) {
        printSlipBtn.onclick = () => {
            window.print();
        };
    }

    function showPaklaringModal(username) {
        const user = state.users[username];
        if (!user) return;
        const modal = document.getElementById('paklaring-modal');
        const content = document.getElementById('paklaring-content');
        if (!modal || !content) return;

        const dateNow = new Date();
        const fullDateStr = dateNow.toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });
        const dateEl = document.getElementById('paklaring-date-str');
        if (dateEl) dateEl.innerText = fullDateStr;

        const randomRef = String(Math.floor(100 + Math.random() * 900));
        const refNoEl = document.getElementById('paklaring-ref-no');
        if (refNoEl) refNoEl.innerText = `No: SUBA-ARCH/HRD/PKL/2026/${randomRef}`;

        const empType = user.employment_type || 'Full-Time';

        content.innerHTML = `
            <p style="margin: 0;">Yang bertanda tangan di bawah ini atas nama pimpinan PT Suba Architecture menerangkan bahwa:</p>
            
            <table style="width: 100%; border-collapse: collapse; margin: 10px 0; font-family: inherit; font-size: 14px; color: #020617;">
                <tr><td style="width: 180px; padding: 4px 0;">Nama Lengkap</td><td>: <b>${user.name}</b></td></tr>
                <tr><td style="padding: 4px 0;">Username / ID Staf</td><td>: @${user.username} (${user.email})</td></tr>
                <tr><td style="padding: 4px 0;">Jabatan Terakhir</td><td>: <b>${user.title}</b></td></tr>
                <tr><td style="padding: 4px 0;">Status / Tipe Kontrak</td><td>: <b>${empType}</b></td></tr>
                <tr><td style="padding: 4px 0;">Unit Kerja / Divisi</td><td>: Suba Architecture & Planning Enterprise</td></tr>
            </table>

            <p style="margin: 0;">Bahwa yang bersangkutan telah bekerja pada perusahaan kami dengan dedikasi dan integritas yang tinggi. Selama masa tugasnya, <b>${user.name}</b> telah menunjukkan kontribusi profesionalisme yang sangat baik serta memenuhi kriteria Key Performance Indicator (KPI) standar perusahaan.</p>
            
            <p style="margin: 0;">Demikian Surat Keterangan Kerja (Paklaring) ini diterbitkan secara sah oleh sistem ERP Suba-Arch untuk dipergunakan sebagaimana mestinya. Kami mengucapkan terima kasih atas kontribusi terbaik yang telah diberikan.</p>
        `;

        modal.style.display = 'flex';
    }

    const closePaklaringBtn = document.getElementById('close-paklaring-modal-btn');
    if (closePaklaringBtn) {
        closePaklaringBtn.onclick = () => {
            document.getElementById('paklaring-modal').style.display = 'none';
        };
    }

    const printPaklaringBtn = document.getElementById('print-paklaring-btn');
    if (printPaklaringBtn) {
        printPaklaringBtn.onclick = () => {
            window.print();
        };
    }

    function openStaffEditModal(username) {
        const user = state.users[username];
        if (!user) return;
        
        const modal = document.getElementById('staff-edit-modal');
        const hiddenUser = document.getElementById('staff-edit-username-hidden');
        const displayUser = document.getElementById('staff-edit-username-display');
        const nameInput = document.getElementById('staff-edit-name-input');
        const emailInput = document.getElementById('staff-edit-email-input');
        const typeSelect = document.getElementById('staff-edit-employment-type');
        const jobTitleInput = document.getElementById('staff-edit-job-title');
        const titleEl = document.getElementById('staff-edit-modal-title');

        if (!modal || !hiddenUser || !displayUser || !nameInput || !emailInput) return;

        hiddenUser.value = user.username;
        displayUser.value = `@${user.username}`;
        nameInput.value = user.name || '';
        emailInput.value = user.email || '';
        if (typeSelect) typeSelect.value = user.employment_type || 'Full-Time';
        if (jobTitleInput) jobTitleInput.value = user.job_title || user.title || '';
        if (titleEl) titleEl.innerText = `Edit Profil Staf Tim (@${user.username})`;

        modal.style.display = 'flex';
    }

    const closeStaffEditModalBtn = document.getElementById('close-staff-edit-modal-btn');
    const btnCancelStaffEdit = document.getElementById('btn-cancel-staff-edit');
    const staffEditModal = document.getElementById('staff-edit-modal');

    if (closeStaffEditModalBtn && staffEditModal) {
        closeStaffEditModalBtn.onclick = () => { staffEditModal.style.display = 'none'; };
    }
    if (btnCancelStaffEdit && staffEditModal) {
        btnCancelStaffEdit.onclick = () => { staffEditModal.style.display = 'none'; };
    }

    const staffEditForm = document.getElementById('staff-edit-form');
    if (staffEditForm && staffEditModal) {
        staffEditForm.onsubmit = async (e) => {
            e.preventDefault();
            const username = document.getElementById('staff-edit-username-hidden')?.value;
            const name = document.getElementById('staff-edit-name-input')?.value;
            const email = document.getElementById('staff-edit-email-input')?.value;
            const employment_type = document.getElementById('staff-edit-employment-type')?.value;
            const job_title = document.getElementById('staff-edit-job-title')?.value?.trim();

            if (!username || !email) return;

            try {
                const response = await fetch(`/api/users/${username}`, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify({ name, email, employment_type, job_title })
                });

                const data = await response.json();
                if (response.ok && data.success) {
                    if (state.users[username]) {
                        state.users[username].name = name;
                        state.users[username].email = email;
                        state.users[username].employment_type = employment_type;
                        state.users[username].job_title = job_title;
                        state.users[username].title = job_title || state.users[username].title;
                    }
                    updateState(state);
                    renderAll();
                    staffEditModal.style.display = 'none';
                    renderDynamicStaffNavigation();
                    showPremiumNotice("Profil Berhasil Diperbarui", `Informasi dan nama jabatan @${escapeHtml(username)} telah disinkronkan.`, { variant: 'success' });
                } else {
                    showPremiumNotice("Gagal Edit Profil", data.message || "Gagal meng-update staf.");
                }
            } catch (err) {
                console.error(err);
                showPremiumNotice("Error API", "Koneksi ke server bermasalah.");
            }
        };
    }

    // ================= Live Attendance HRIS Renderer =================
    function renderAttendanceLogs() {
        const list = document.getElementById('attendance-log-list');
        if (!list) return;
        list.innerHTML = '';
        const summary = state.attendanceSummary;
        const summaryTarget = document.getElementById('attendance-personal-target');
        const summaryWorked = document.getElementById('attendance-personal-worked');
        const summaryRemaining = document.getElementById('attendance-personal-remaining');
        if (summaryTarget) summaryTarget.textContent = `${Number(summary?.target_hours || 0).toFixed(1)} jam`;
        if (summaryWorked) summaryWorked.textContent = `${Number(summary?.worked_hours || 0).toFixed(1)} jam`;
        if (summaryRemaining) summaryRemaining.textContent = `${Number(summary?.remaining_hours || 0).toFixed(1)} jam`;
        
        const logs = [...state.attendance].reverse();
        
        // Hide CEO attendance logs completely
        let filteredLogs = logs.filter(l => {
            const u = state.users[l.username];
            return u && u.role !== 'ceo';
        });

        const isManager = currentUser.role.startsWith('mgr_') && currentUser.role !== 'mgr_hrd';
        if (attendanceActiveFilter === 'today') {
            filteredLogs = filteredLogs.filter(log => log.date === todayJakarta());
        } else if (!isManager) {
            filteredLogs = filteredLogs.filter(log => divisionFromRole(state.users[log.username]?.role) === attendanceActiveFilter);
        }

        // Sembunyikan tab divisi lain bagi manager divisi kustom
        const btnMkt = document.getElementById('attendance-btn-marketing');
        const btnOps = document.getElementById('attendance-btn-ops');
        const btnFinance = document.getElementById('attendance-btn-finance');
        const btnHrd = document.getElementById('attendance-btn-hrd');
        if (isManager) {
            if (btnMkt) btnMkt.style.display = 'none';
            if (btnOps) btnOps.style.display = 'none';
            if (btnFinance) btnFinance.style.display = 'none';
            if (btnHrd) btnHrd.style.display = 'none';
        } else {
            if (btnMkt) btnMkt.style.display = 'inline-block';
            if (btnOps) btnOps.style.display = 'inline-block';
            if (btnFinance) btnFinance.style.display = 'inline-block';
            if (btnHrd) btnHrd.style.display = 'inline-block';
        }

        const buildLogItemHtml = (log) => {
            const user = state.users[log.username] || { name: log.username, avatar: 'ST' };
            const dPointRate = state.dPointRates[log.username] || 50000;
            const dPointText = log.status === 'Present' ? `Rp ${parseInt(dPointRate).toLocaleString('id-ID')}` : 'Forfeited';
            
            let statusColor = 'var(--success)';
            let statusText = 'On Time';
            if (!log.is_active && log.timeOut) {
                statusColor = 'var(--text-muted)';
                statusText = `Clock out ${log.timeOut}`;
            } else if (log.status === 'Late') {
                statusColor = 'var(--warning)';
                statusText = 'Terlambat';
            }

            const mapsLink = (log.lat && log.lng) ? `https://www.google.com/maps/search/?api=1&query=${log.lat},${log.lng}` : null;
            const mapsHtml = mapsLink ? `<a href="${mapsLink}" target="_blank" style="background: var(--primary); color: #020617; padding: 4px 8px; border-radius: 4px; font-weight: 700; text-decoration: none; font-size: 10px; display: inline-flex; align-items: center; gap: 4px; margin-left: 8px;"><i class="ph-fill ph-map-pin"></i> BUKA PETA</a>` : '';
            
            return `
                <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 12px; display: flex; gap: 12px; align-items: center; margin-bottom: 8px; width: 100%;">
                    <div class="member-avatar" style="width: 40px; height: 40px; font-size: 14px; border-radius: var(--radius-sm); min-width: 40px; color:white; justify-content: center;">${user.avatar}</div>
                    <div style="flex: 1;">
                        <div style="font-size: 14px; font-weight: 600; color: white;">${user.name}</div>
                        <div style="font-size: 11px; color: var(--text-secondary); display: flex; gap: 8px; margin-top: 4px; align-items: center; flex-wrap: wrap;">
                            <span><i class="ph ph-clock"></i> In: ${log.time}</span>
                            <span style="color: ${statusColor};"><i class="ph ph-check-circle"></i> ${statusText}</span>
                            <span><i class="ph ph-timer"></i> ${Number(log.duration_hours || 0).toFixed(2)} jam</span>
                            ${mapsHtml}
                        </div>
                        <div style="font-size: 11px; color: var(--primary); margin-top: 4px;"><i class="ph ph-coins"></i> D-Point: ${dPointText}</div>
                    </div>
                    ${log.id ? `
                        <button type="button" class="erp-delete-btn icon-only" data-erp-delete
                            data-resource-type="attendance"
                            data-resource-id="${log.id}"
                            data-resource-label="Presensi ${escapeHtml(user.name)} tanggal ${escapeHtml(log.date || '')}"
                            title="Hapus atau ajukan koreksi presensi">
                            <i class="ph ph-trash"></i>
                        </button>` : ''}
                </div>
            `;
        };

        if (isManager) {
            const personalLogs = filteredLogs.filter(l => l.username === currentUser.username);
            const teamLogs = filteredLogs.filter(l => {
                const u = state.users[l.username];
                return u && u.parent === currentUser.username && l.username !== currentUser.username;
            });

            list.innerHTML += `<div style="margin-bottom: 12px;"><h4 style="margin: 0 0 8px 0; color: var(--primary); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Absensi Pribadi</h4></div>`;
            if (personalLogs.length === 0) {
                list.innerHTML += `<div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 16px;">Belum ada riwayat absensi pribadi.</div>`;
            } else {
                personalLogs.forEach(l => {
                    list.innerHTML += buildLogItemHtml(l);
                });
            }

            list.innerHTML += `<div style="margin-top: 16px; margin-bottom: 12px;"><h4 style="margin: 0 0 8px 0; color: var(--warning); font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px;">Absensi Tim Binaan</h4></div>`;
            if (teamLogs.length === 0) {
                list.innerHTML += `<div style="font-size: 11px; color: var(--text-secondary); margin-bottom: 16px;">Belum ada riwayat absensi tim.</div>`;
            } else {
                teamLogs.forEach(l => {
                    list.innerHTML += buildLogItemHtml(l);
                });
            }
        } else {
            if (!filteredLogs.length) {
                const labels = {
                    today: 'hari ini',
                    marketing: 'Divisi Marketing',
                    operasional: 'Divisi Operasional',
                    finance: 'Divisi Finance',
                    hrd: 'Divisi HRD'
                };
                list.innerHTML = `
                    <div class="attendance-empty-state">
                        <i class="ph ph-clock-countdown"></i>
                        <strong>Belum ada data kehadiran</strong>
                        <div>Belum terdapat aktivitas clock-in terbaru untuk ${escapeHtml(labels[attendanceActiveFilter] || attendanceActiveFilter)}. Data akan diperbarui otomatis saat karyawan melakukan absensi.</div>
                    </div>
                `;
            } else {
                filteredLogs.forEach(log => {
                    list.innerHTML += buildLogItemHtml(log);
                });
            }
        }

        renderLiveMapTracker();
    }

    const attendanceFilterButtons = {
        today: document.getElementById('attendance-btn-today'),
        marketing: document.getElementById('attendance-btn-marketing'),
        operasional: document.getElementById('attendance-btn-ops'),
        finance: document.getElementById('attendance-btn-finance'),
        hrd: document.getElementById('attendance-btn-hrd')
    };
    Object.entries(attendanceFilterButtons).forEach(([filter, button]) => {
        button?.addEventListener('click', () => {
            attendanceActiveFilter = filter;
            Object.entries(attendanceFilterButtons).forEach(([key, item]) => item?.classList.toggle('active', key === filter));
            renderAttendanceLogs();
        });
    });

    function renderLiveMapTracker() {
        const mapCard = document.getElementById('live-attendance-map-card');
        if (!mapCard) return;
        
        mapCard.innerHTML = '';
        mapCard.style.position = 'relative';
        
        mapCard.innerHTML = `
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: radial-gradient(circle, rgba(15,23,42,0.85) 0%, rgba(2,6,23,0.95) 100%); z-index: 1;"></div>
            <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: radial-gradient(rgba(255,255,255,0.05) 1px, transparent 0); background-size: 24px 24px; z-index: 1; opacity: 0.5;"></div>
            
            <div style="position: absolute; top: 16px; left: 16px; background: rgba(15,23,42,0.8); border: 1px solid var(--glass-border); padding: 8px 16px; border-radius: var(--radius-md); font-size: 11px; z-index: 5; color: white; display: flex; align-items: center; gap: 8px; backdrop-filter: blur(10px);">
                <span style="background: var(--primary); width: 8px; height: 8px; border-radius: 50%; display: inline-block; box-shadow: 0 0 8px var(--primary);"></span>
                <b>LIVE PRESENCE Â· DATA LOKASI AKTUAL</b>
            </div>
        `;
        
        const todayStr = todayJakarta();
        let relevantLogs = state.attendance.filter(a => a.date === todayStr && state.users[a.username]);

        if (attendanceActiveFilter !== 'today') {
            relevantLogs = relevantLogs.filter(log => divisionFromRole(state.users[log.username]?.role) === attendanceActiveFilter);
        }
        
        if (currentUser.role.startsWith('mgr_') && currentUser.role !== 'mgr_hrd') {
            relevantLogs = relevantLogs.filter(a => {
                const u = state.users[a.username];
                return a.username === currentUser.username || (u && u.parent === currentUser.username);
            });
        }
        
        // Exclude CEO from map tracking
        relevantLogs = relevantLogs.filter(a => a.username !== 'ceo');
        
        if (relevantLogs.length === 0) {
            mapCard.innerHTML += `
                <div style="position: relative; z-index: 5; color: var(--text-secondary); text-align: center; padding: 40px; font-size: 13px;">
                    <i class="ph ph-map-pin" style="font-size: 32px; color: var(--warning); margin-bottom: 8px; display: block;"></i>
                    Belum ada karyawan yang clock-in hari ini untuk dipetakan.
                </div>
            `;
            return;
        }

        relevantLogs.forEach((log, index) => {
            const user = state.users[log.username] || { name: log.username, avatar: 'ST' };
            const locationLabel = log.location_name || (
                Number.isFinite(Number(log.lat)) && Number.isFinite(Number(log.lng))
                    ? `${Number(log.lat).toFixed(5)}, ${Number(log.lng).toFixed(5)}`
                    : 'Lokasi belum tersedia'
            );
            
            const leftPct = 15 + ((index * 35) % 70); 
            const topPct = 25 + ((index * 25) % 55);
            
            const isOut = !log.is_active;
            const markerColor = isOut ? 'var(--warning)' : 'var(--primary)';
            const borderGlow = isOut ? 'rgba(245,158,11,0.4)' : 'rgba(6,182,212,0.4)';
            
            const pinEl = document.createElement('div');
            pinEl.style.position = 'absolute';
            pinEl.style.left = `${leftPct}%`;
            pinEl.style.top = `${topPct}%`;
            pinEl.style.transform = 'translate(-50%, -50%)';
            pinEl.style.zIndex = '5';
            pinEl.style.display = 'flex';
            pinEl.style.flexDirection = 'column';
            pinEl.style.alignItems = 'center';
            pinEl.style.cursor = 'pointer';
            
            pinEl.innerHTML = `
                <div class="glass-card" style="background: rgba(15,23,42,0.9); border: 1px solid ${markerColor}; padding: 6px 12px; border-radius: 12px; font-size: 10px; margin-bottom: 6px; white-space: nowrap; box-shadow: 0 4px 12px rgba(0,0,0,0.5); z-index: 10; display: flex; flex-direction: column; align-items: center; gap: 2px;">
                    <div style="font-weight: 700; color: white;">${user.name}</div>
                    <div style="color: var(--text-secondary); font-size: 9px; display: flex; align-items: center; gap: 4px;">
                        <i class="ph ph-map-pin-line" style="color: ${markerColor};"></i> 
                        ${isOut ? 'Out' : 'In'}: ${escapeHtml(locationLabel)}
                    </div>
                </div>
                <div style="position: relative; display: flex; align-items: center; justify-content: center;">
                    <div style="position: absolute; width: 32px; height: 32px; border-radius: 50%; background: ${borderGlow};"></div>
                    <div style="width: 16px; height: 16px; border-radius: 50%; background: ${markerColor}; border: 3px solid white; box-shadow: 0 0 10px ${markerColor}; z-index: 6;"></div>
                </div>
            `;
            
            mapCard.appendChild(pinEl);
        });
    }

    // ================= Gantt & Project Workspace Sync =================
    const submitOpsBtn = document.querySelector('#view-ops-staff button');
    if (submitOpsBtn) {
        submitOpsBtn.addEventListener('click', (e) => {
            e.preventDefault();
            const fileInput = document.querySelector('#view-ops-staff input[type="file"]');
            if (fileInput && !fileInput.files.length) {
                return showPremiumNotice('Berkas Belum Dipilih', 'Pilih berkas gambar atau denah sebelum mengirim.', { variant: 'danger' });
            }
            
            const newTask = {
                id: 'task-ops-' + Date.now(),
                username: 'staff_ops',
                title: 'Approve Survey & Denah Lokasi Villa SCBD',
                status: 'in_progress',
                deadline: new Date(Date.now() + 2 * 24 * 60 * 60 * 1000).toISOString(),
                relation: 'Villa SCBD Project',
                evidence: 'Denah_SCBD_Draft.dwg'
            };
            state.tasks.push(newTask);
            updateState(state);
            showPremiumNotice('Dokumen Berhasil Dikirim', 'Dokumen telah diteruskan kepada Manager. Fase 2 akan terbuka setelah disetujui.', { variant: 'success' });
        });
    }

    function renderGanttChart() {
        const bar = document.querySelector('.gantt-bar.survey');
        if (!bar) return;
        
        const progress = state.projectProgress ? state.projectProgress.villaScbd : 10;
        bar.style.width = `${progress}%`;
        bar.innerText = `Survey (${progress}%)`;
        
        const stage1 = document.querySelector('#view-ops-staff .stage:first-of-type');
        const stage2 = document.querySelector('#view-ops-staff .stage:last-of-type');
        if (progress >= 50) {
            if (stage1) {
                stage1.classList.remove('active');
                stage1.querySelector('p').innerText = 'Denah SCBD telah disetujui Manager.';
                const btn = stage1.querySelector('button');
                if(btn) btn.style.display = 'none';
            }
            if (stage2) {
                stage2.classList.add('active');
                stage2.querySelector('p').innerText = 'Silakan mulai pengerjaan Desain Arsitektur.';
            }
        }
    }

    // ================= Company Health / Division Card Updates =================
    let ceoDivisionChartInstance = null;

    function openCEODivisionDetail(divId) {
        const modal = document.getElementById('ceo-division-detail-modal');
        const titleEl = document.getElementById('ceo-div-modal-title');
        const kpisListEl = document.getElementById('ceo-div-kpis-list');
        if (!modal || !titleEl || !kpisListEl) return;
        
        const divisionNames = {
            'mgr_marketing': 'Divisi Marketing & CS',
            'mgr_ops': 'Divisi Operasional & Lapangan',
            'mgr_finance': 'Divisi Keuangan & Payroll',
            'mgr_hrd': 'Divisi Human Resources'
        };
        
        const divName = divisionNames[divId] || 'Detail Divisi';
        titleEl.innerHTML = `<i class="ph ph-shield-star"></i> Target & Performa: ${divName}`;
        
        const kpis = state.kpiConfig[divId] || [];
        kpisListEl.innerHTML = '';
        if (kpis.length === 0) {
            kpisListEl.innerHTML = `
                <div style="color: var(--text-secondary); font-size: 11px; padding: 16px; border: 1px dashed var(--glass-border); border-radius: 6px; text-align: center; background: rgba(255,255,255,0.01); width: 100%;">
                    âš ï¸ Belum ada target KPI khusus yang didaftarkan untuk ${divName}.
                </div>
            `;
        } else {
            kpis.forEach(k => {
                kpisListEl.innerHTML += `
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 6px; padding: 10px 14px; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: white; width: 100%;">
                        <span style="font-weight: 500;">${k.name}</span>
                        <span class="badge warning" style="font-size: 10px;">Bobot: ${k.weight}%</span>
                    </div>
                `;
            });
        }
        
        const members = Object.values(state.users).filter(u => u.username === divId || u.parent === divId);
        
        const labels = members.map(m => m.name);
        const scores = members.map(m => {
            const userTasks = state.tasks.filter(t => t.username === m.username);
            if (userTasks.length === 0) return 0;
            const done = userTasks.filter(t => t.status === 'done').length;
            return Math.round((done / userTasks.length) * 100);
        });
        
        const ctx = document.getElementById('ceoDivisionTeamPerformanceChart');
        if (ctx) {
            if (ceoDivisionChartInstance) {
                ceoDivisionChartInstance.destroy();
            }
            ceoDivisionChartInstance = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'KPIM Score (%)',
                        data: scores,
                        backgroundColor: 'rgba(242, 201, 76, 0.65)',
                        borderColor: 'var(--primary)',
                        borderWidth: 1,
                        borderRadius: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        x: {
                            min: 0,
                            max: 100,
                            ticks: { color: 'rgba(255,255,255,0.6)' },
                            grid: { color: 'rgba(255,255,255,0.05)' }
                        },
                        y: {
                            ticks: { color: 'rgba(255,255,255,0.6)' },
                            grid: { display: false }
                        }
                    }
                }
            });
        }
        
        modal.style.display = 'flex';
    }

    const closeCeoDivModalBtn = document.getElementById('close-ceo-div-modal-btn');
    const btnCloseCeoDivModal = document.getElementById('btn-close-ceo-div-modal');
    const ceoDivDetailModal = document.getElementById('ceo-division-detail-modal');
    
    const closeCeoDivModal = () => {
        if (ceoDivDetailModal) ceoDivDetailModal.style.display = 'none';
    };
    if (closeCeoDivModalBtn) closeCeoDivModalBtn.addEventListener('click', closeCeoDivModal);
    if (btnCloseCeoDivModal) btnCloseCeoDivModal.addEventListener('click', closeCeoDivModal);

    function updateCEODivisionCards() {
        const grid = document.querySelector('.division-grid');
        if (!grid) return;
        
        const divisions = [
            { id: 'mgr_marketing', title: 'Divisi Marketing & CS', icon: 'ðŸ“ˆ', color: 'var(--primary)', target: 'Rp 50M' },
            { id: 'mgr_ops', title: 'Divisi Operasional & Lapangan', icon: 'ðŸ—ï¸', color: 'var(--warning)', target: '15 Proyek' },
            { id: 'mgr_finance', title: 'Divisi Keuangan & Payroll', icon: 'ðŸ’°', color: 'var(--success)', target: 'Normal Burn Rate' },
            { id: 'mgr_hrd', title: 'Divisi Human Resources', icon: 'ðŸ‘¥', color: 'var(--info)', target: '100% Presensi' }
        ];
        
        grid.innerHTML = '';
        divisions.forEach(div => {
            const kpis = state.kpiConfig[div.id] || [];
            const kpiCount = kpis.length;
            const statusText = kpiCount > 0 ? 'On Track' : 'Belum Ada KPI';
            const statusColor = kpiCount > 0 ? 'var(--success)' : 'var(--danger)';
            
            grid.innerHTML += `
                <div class="division-card" data-divid="${div.id}" style="border-top: 3px solid ${div.color}; cursor: pointer; transition: all 0.3s ease; background: var(--bg-card); border-left: 1px solid var(--glass-border); border-right: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); padding: 20px; border-radius: var(--radius-md);">
                    <div class="div-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                        <h4 style="margin: 0; font-size: 14px; color: white;">${div.icon} ${div.title}</h4>
                        <span class="div-status" style="color: ${statusColor}; font-size: 11px;"><i class="ph-fill ph-circle"></i> ${statusText}</span>
                    </div>
                    <div style="font-size: 20px; font-weight: 700; color: white; margin-bottom: 8px;">${kpiCount} Target KPI</div>
                    <p style="color: var(--text-secondary); font-size: 12px; margin-bottom: 16px;">Target Divisi Utama: ${div.target}</p>
                    <div class="progress-container" style="background: rgba(255,255,255,0.05); height: 6px; border-radius: 3px; overflow: hidden; width: 100%;">
                        <div class="progress-bar" style="width: ${kpiCount > 0 ? '80%' : '10%'}; background: ${div.color}; height: 100%;"></div>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 11px; margin-top: 8px; color: var(--text-muted); width: 100%;">
                        <span>Klik untuk detail target &amp; performa tim</span>
                        <span style="color: ${div.color};">Detail <i class="ph ph-arrow-right"></i></span>
                    </div>
                </div>
            `;
        });
        
        grid.querySelectorAll('.division-card').forEach(card => {
            card.onclick = () => {
                const divId = card.getAttribute('data-divid');
                openCEODivisionDetail(divId);
            };
        });
    }

    // ================= Org Hierarchy Chart Renderer (SVG + HTML) =================
    function getNodeStatusColor(username) {
        const userTasks = state.tasks.filter(t => t.username === username);
        if (userTasks.length === 0) return 'green';
        
        const hasFailed = userTasks.some(t => t.status === 'failed');
        if (hasFailed) return 'red';
        
        const hasRevisiOrWarning = userTasks.some(t => t.status === 'revisi' || t.status === 'nearing_deadline');
        if (hasRevisiOrWarning) return 'yellow';
        
        const hasInProgress = userTasks.some(t => t.status === 'in_progress');
        if (hasInProgress) return 'blue';
        
        return 'green';
    }

    const organizationDivisions = [
        { key: 'marketing', label: 'Marketing', icon: 'ph-megaphone' },
        { key: 'operasional', label: 'Operasional', icon: 'ph-hard-hat' },
        { key: 'finance', label: 'Finance', icon: 'ph-chart-line-up' },
        { key: 'hrd', label: 'HRD', icon: 'ph-users-three' },
    ];

    function organizationPeople() {
        return state.organizationChart?.people || {};
    }

    function prepareHierarchyRegistration(parentUsername, addType) {
        window.lastSelectedHierarchyParent = parentUsername;

        const modal = document.getElementById('user-registration-modal');
        const roleSelect = document.getElementById('reg-role');
        if (!modal || !roleSelect) return;

        const staffRoleByManager = {
            mgr_marketing: 'staff_marketing',
            mgr_ops: 'staff_ops',
            mgr_finance: 'staff_finance',
            mgr_hrd: 'staff_hrd',
        };

        if (addType === 'manager') {
            roleSelect.innerHTML = `
                <option value="mgr_marketing">Manager Marketing</option>
                <option value="mgr_ops">Manager Operasional</option>
                <option value="mgr_finance">Manager Finance</option>
                <option value="mgr_hrd">Manager HRD</option>
            `;
        } else {
            const managerRole = organizationPeople()[parentUsername]?.role || currentUser?.role;
            const staffRole = staffRoleByManager[managerRole];
            roleSelect.innerHTML = staffRole
                ? `<option value="${staffRole}">Staff ${organizationPeople()[parentUsername]?.division_label || ''}</option>`
                : `
                    <option value="staff_marketing">Staff Marketing</option>
                    <option value="staff_ops">Staff Operasional</option>
                    <option value="staff_finance">Staff Finance</option>
                    <option value="staff_hrd">Staff HRD</option>
                `;
        }

        modal.style.display = 'flex';
        refreshEmployeeIdentityPreview();
    }

    function createOrganizationCard(person, peopleMap) {
        const card = document.createElement('article');
        const canViewPerformance = person.can_view_performance === true;
        const status = canViewPerformance ? getNodeStatusColor(person.username) : 'neutral';
        card.className = `org-node-card ${canViewPerformance ? `can-view-performance glow-${status}` : ''}`;
        card.id = `node-${person.username}`;

        if (person.is_self) {
            card.classList.add('current-user-node');
        }

        const activeTasks = canViewPerformance
            ? state.tasks.filter(task => task.username === person.username && task.status !== 'done').length
            : 0;
        const managerName = person.parent ? peopleMap[person.parent]?.name : null;
        const performanceBadge = canViewPerformance
            ? `<span class="node-badge tasks-pending ${activeTasks > 0 ? `${status}-tasks` : 'success-tasks'}">${activeTasks > 0 ? `${activeTasks} tugas aktif` : 'Tugas terkendali'}</span>`
            : '<span class="node-badge employment"><i class="ph ph-eye"></i> Profil organisasi</span>';

        let actions = '';
        const isCeoViewer = currentUser?.role === 'ceo';
        const isManagerViewer = currentUser?.role?.startsWith('mgr_');

        if (isCeoViewer && person.is_self) {
            actions = `
                <button class="primary-btn org-add-person" data-parent="${escapeHtml(person.username)}" data-type="manager">
                    <i class="ph ph-plus-circle"></i> Tambah Manager
                </button>
            `;
        } else if (isCeoViewer && !person.is_self) {
            actions = `
                <div class="organization-card-actions">
                    <button class="primary-btn org-edit-person" data-username="${escapeHtml(person.username)}"><i class="ph ph-pencil-simple"></i> Edit</button>
                    <button class="primary-btn org-deactivate-person" data-username="${escapeHtml(person.username)}"><i class="ph ph-trash"></i> Nonaktifkan</button>
                </div>
            `;
        } else if (isManagerViewer && person.is_self) {
            actions = `
                <button class="primary-btn org-add-person" data-parent="${escapeHtml(person.username)}" data-type="staff">
                    <i class="ph ph-user-plus"></i> Tambah Staff
                </button>
            `;
        } else if (isManagerViewer && person.is_direct_report) {
            actions = `
                <div class="organization-card-actions">
                    <button class="primary-btn org-edit-person" data-username="${escapeHtml(person.username)}"><i class="ph ph-pencil-simple"></i> Edit</button>
                    <button class="primary-btn org-request-deactivation" data-username="${escapeHtml(person.username)}" aria-label="Ajukan penonaktifan ${escapeHtml(person.name)}"><i class="ph ph-trash"></i></button>
                </div>
            `;
        }

        card.innerHTML = `
            <div class="node-avatar-circle">${escapeHtml(person.avatar || 'SA')}</div>
            <div class="node-name">${escapeHtml(person.name)}</div>
            <div class="node-title">${escapeHtml(person.job_title || 'Anggota Tim')}</div>
            <div class="node-meta">
                ${person.is_self ? '<span class="node-badge self-badge">ANDA</span>' : ''}
                <span class="node-badge division">${escapeHtml(person.division_label || 'Perusahaan')}</span>
                <span class="node-badge level">${escapeHtml(person.level || 'Staff')}</span>
                <span class="node-badge employment">${escapeHtml(person.employment_type || 'Full-Time')}</span>
                ${performanceBadge}
            </div>
            <div class="node-reporting">
                ${managerName ? `<i class="ph ph-arrow-bend-up-left"></i> Atasan langsung: ${escapeHtml(managerName)}` : '<i class="ph ph-buildings"></i> Pimpinan perusahaan'}
            </div>
            ${!canViewPerformance ? '<div class="node-privacy-hint">Detail kinerja dilindungi sesuai kewenangan.</div>' : ''}
            ${actions}
        `;

        card.querySelector('.org-add-person')?.addEventListener('click', (event) => {
            event.stopPropagation();
            const button = event.currentTarget;
            prepareHierarchyRegistration(button.dataset.parent, button.dataset.type);
        });

        card.querySelector('.org-edit-person')?.addEventListener('click', (event) => {
            event.stopPropagation();
            openStaffEditModal(event.currentTarget.dataset.username);
        });

        card.querySelector('.org-deactivate-person')?.addEventListener('click', (event) => {
            event.stopPropagation();
            const username = event.currentTarget.dataset.username;
            const target = peopleMap[username];

            showStaffSeparationDialog(target || { username }, 'direct', async separation => {
                await apiRequest(`/api/users/${encodeURIComponent(username)}`, {
                    method: 'DELETE',
                    body: separation,
                });
                showPremiumNotice(
                    'Akun Dinonaktifkan',
                    'Status keluar tersimpan dan perubahan telah disinkronkan ke hirarki, sidebar, serta live attendance.',
                    { variant: 'success' },
                );
                await syncDataFromServer();
            });
        });

        card.querySelector('.org-request-deactivation')?.addEventListener('click', (event) => {
            event.stopPropagation();
            const username = event.currentTarget.dataset.username;
            const target = peopleMap[username];

            showStaffSeparationDialog(target || { username }, 'request', async separation => {
                await apiRequest('/api/team-requests', {
                    method: 'POST',
                    body: {
                        action: 'delete',
                        target_username: username,
                        ...separation,
                    },
                });
                showPremiumNotice(
                    'Pengajuan Terkirim',
                    'Status keluar telah dicatat dan CEO akan menerima permintaan ini di pusat persetujuan.',
                    { variant: 'success' },
                );
            });
        });

        card.addEventListener('click', () => {
            if (canViewPerformance && state.users[person.username]) {
                openEmployeeTasksModal(person.username);
                return;
            }

            showPremiumNotice(
                person.name,
                `${person.job_title || 'Anggota Tim'} Â· ${person.division_label || 'Perusahaan'}. Informasi tugas, KPI, attendance, dan data pribadi dilindungi.`,
            );
        });

        return card;
    }

    function renderOrganizationDirectory() {
        const container = document.getElementById('org-chart-render');
        const summary = document.getElementById('organization-summary');
        if (!container) return;

        const peopleMap = organizationPeople();
        const allPeople = Object.values(peopleMap);
        const normalizedSearch = organizationSearchTerm.trim().toLowerCase();
        const matchesSearch = (person) => {
            if (!normalizedSearch) return true;
            return [
                person.name,
                person.job_title,
                person.division_label,
                person.employment_type,
            ].some(value => String(value || '').toLowerCase().includes(normalizedSearch));
        };
        const matchesDivision = (person) => organizationDivisionFilter === 'all'
            || person.division === organizationDivisionFilter;
        const filtered = allPeople.filter(person => matchesSearch(person) && matchesDivision(person));

        container.innerHTML = '';
        container.className = 'org-chart org-directory';

        if (summary) {
            summary.textContent = `${filtered.length} dari ${allPeople.length} anggota aktif`;
        }

        if (allPeople.length === 0) {
            container.innerHTML = `
                <div class="organization-empty">
                    <i class="ph ph-users-three"></i>
                    Struktur organisasi aktif belum tersedia.
                </div>
            `;
            return;
        }

        if (filtered.length === 0) {
            container.innerHTML = `
                <div class="organization-empty">
                    <i class="ph ph-magnifying-glass"></i>
                    Tidak ditemukan anggota aktif yang sesuai dengan pencarian atau divisi tersebut.
                </div>
            `;
            return;
        }

        const leaders = allPeople.filter(person => person.role === 'ceo');
        if (leaders.length > 0) {
            const companyLead = document.createElement('div');
            companyLead.className = 'org-company-lead';
            leaders
                .filter(person => !normalizedSearch || matchesSearch(person) || filtered.some(candidate => candidate.parent === person.username))
                .forEach(person => companyLead.appendChild(createOrganizationCard(person, peopleMap)));

            if (companyLead.children.length > 0 || organizationDivisionFilter !== 'company') {
                if (companyLead.children.length === 0) {
                    companyLead.appendChild(createOrganizationCard(leaders[0], peopleMap));
                }
                container.appendChild(companyLead);
            }
        }

        const divisionGrid = document.createElement('div');
        divisionGrid.className = 'org-divisions-grid';

        organizationDivisions
            .filter(division => organizationDivisionFilter === 'all' || organizationDivisionFilter === division.key)
            .forEach((division) => {
                const divisionPeople = filtered.filter(person => person.division === division.key);
                if (normalizedSearch && divisionPeople.length === 0) return;

                const panel = document.createElement('section');
                panel.className = 'org-division-panel';
                panel.dataset.division = division.key;
                panel.innerHTML = `
                    <header class="org-division-header">
                        <h3><i class="ph ${division.icon}"></i> ${division.label}</h3>
                        <span class="org-division-count">${divisionPeople.length} aktif</span>
                    </header>
                `;

                const managers = allPeople.filter(person => person.division === division.key && person.role?.startsWith('mgr_'));
                const visibleManagers = managers.filter(manager => {
                    if (!normalizedSearch) return true;
                    return matchesSearch(manager)
                        || divisionPeople.some(person => person.parent === manager.username);
                });

                if (visibleManagers.length === 0 && divisionPeople.length === 0) {
                    panel.insertAdjacentHTML('beforeend', `
                        <div class="organization-empty">
                            <i class="ph ph-user-focus"></i>
                            Belum ada anggota aktif pada divisi ini.
                        </div>
                    `);
                }

                visibleManagers.forEach((manager) => {
                    const managerBlock = document.createElement('div');
                    managerBlock.className = 'org-manager-block';
                    managerBlock.appendChild(createOrganizationCard(manager, peopleMap));

                    const reports = divisionPeople.filter(person => person.parent === manager.username && person.username !== manager.username);
                    if (reports.length > 0) {
                        const reportList = document.createElement('div');
                        reportList.className = 'org-report-list';
                        reports.forEach(person => reportList.appendChild(createOrganizationCard(person, peopleMap)));
                        managerBlock.appendChild(reportList);
                    }

                    panel.appendChild(managerBlock);
                });

                const managerUsernames = new Set(managers.map(manager => manager.username));
                const unassigned = divisionPeople.filter(person => (
                    !person.role?.startsWith('mgr_')
                    && !managerUsernames.has(person.username)
                    && !managerUsernames.has(person.parent)
                ));
                if (unassigned.length > 0) {
                    const reportList = document.createElement('div');
                    reportList.className = 'org-report-list';
                    unassigned.forEach(person => reportList.appendChild(createOrganizationCard(person, peopleMap)));
                    panel.appendChild(reportList);
                }

                divisionGrid.appendChild(panel);
            });

        if (divisionGrid.children.length > 0) {
            container.appendChild(divisionGrid);
        }
    }

    function renderOrgChart() {
        renderOrganizationDirectory();
        return;

        const orgChartRender = document.getElementById('org-chart-render');
        if (!orgChartRender) return;
        orgChartRender.innerHTML = '';
        
        // Dynamic levels determination
        const level1 = Object.values(state.users).filter(u => u.parent === null || u.role === 'ceo').map(u => u.username);
        const level2 = Object.values(state.users).filter(u => u.parent === 'ceo' && u.role !== 'ceo').map(u => u.username);
        
        const level3 = {};
        level2.forEach(mgrUname => {
            level3[mgrUname] = Object.values(state.users).filter(u => u.parent === mgrUname).map(u => u.username);
        });
        
        const row1 = document.createElement('div');
        row1.className = 'org-level-row';
        level1.forEach(uname => row1.appendChild(createNodeCard(uname)));
        orgChartRender.appendChild(row1);
        
        const row2 = document.createElement('div');
        row2.className = 'org-level-row';
        level2.forEach(uname => row2.appendChild(createNodeCard(uname)));
        orgChartRender.appendChild(row2);
        
        const row3 = document.createElement('div');
        row3.className = 'org-level-row';
        level2.forEach(parentUname => {
            const staffGroup = document.createElement('div');
            staffGroup.style.display = 'flex';
            staffGroup.style.gap = '20px';
            const children = level3[parentUname] || [];
            children.forEach(uname => {
                staffGroup.appendChild(createNodeCard(uname));
            });
            row3.appendChild(staffGroup);
        });
        orgChartRender.appendChild(row3);
    }

    function createNodeCard(username) {
        const user = state.users[username];
        if (!user) return document.createElement('div');
        
        const card = document.createElement('div');
        card.className = `org-node-card glow-${getNodeStatusColor(username)}`;
        card.id = `node-${username}`;
        
        const isSelf = currentUser && username === currentUser.username;
        if (isSelf) {
            card.classList.add('current-user-node');
        }
        
        const tasksCount = state.tasks.filter(t => t.username === username && t.status !== 'done').length;
        const taskBadgeClass = tasksCount > 0 ? (getNodeStatusColor(username) + '-tasks') : 'success-tasks';
        const taskBadgeText = tasksCount > 0 ? `${tasksCount} Pending` : 'All Done';
        
        const selfBadge = isSelf ? `<span class="node-badge self-badge" style="background: var(--primary); color: black; font-weight: 700; box-shadow: 0 0 8px var(--primary-glow);">YOU</span>` : '';
        
        let actionBtnHTML = '';
        const isCeoUser = currentUser && currentUser.role === 'ceo';
        const isManagerUser = currentUser && currentUser.role.startsWith('mgr_');

        if (isCeoUser) {
            if (username === currentUser.username) {
                actionBtnHTML = `
                    <button class="primary-btn add-hierarchy-btn" data-parent-node="ceo" data-add-type="manager" style="margin-top: 10px; width: 100%; font-size: 10px; padding: 4px 8px; justify-content: center; background: rgba(52, 199, 89, 0.2); border-color: var(--success); color: var(--success); font-family: inherit;"><i class="ph ph-plus-circle"></i> Add Manager</button>
                `;
            } else {
                const kanbanStatus = user.has_kanban_access ? 'Tutup Kanban' : 'Buka Kanban';
                actionBtnHTML = `
                    <div style="display: flex; gap: 4px; margin-top: 10px; width: 100%;">
                        <button class="primary-btn toggle-kanban-access-btn" data-username="${username}" style="flex: 2; font-size: 9px; padding: 4px; justify-content: center; background: rgba(10, 132, 255, 0.2); border-color: var(--info); color: var(--info); font-family: inherit;" title="Akses Kanban"><i class="ph ph-kanban"></i></button>
                        <button class="primary-btn edit-staff-btn" data-username="${username}" style="flex: 2; font-size: 9px; padding: 4px; justify-content: center; background: rgba(255, 255, 255, 0.15); border-color: var(--glass-border); color: white; font-family: inherit;" title="Edit Akun"><i class="ph ph-pencil-simple"></i> Edit</button>
                        <button class="primary-btn delete-manager-btn" data-username="${username}" style="flex: 2; font-size: 9px; padding: 4px; justify-content: center; background: rgba(255, 59, 48, 0.2); border-color: var(--danger); color: var(--danger); font-family: inherit;" title="Hapus Akun"><i class="ph ph-trash"></i> Hapus</button>
                    </div>
                `;
            }
        } else if (isManagerUser) {
            if (username === currentUser.username) {
                actionBtnHTML = `
                    <button class="primary-btn add-hierarchy-btn" data-parent-node="${username}" data-add-type="staff" style="margin-top: 10px; width: 100%; font-size: 10px; padding: 4px 8px; justify-content: center; background: rgba(242, 201, 76, 0.2); border-color: var(--warning); color: var(--warning); font-family: inherit;"><i class="ph ph-plus-circle"></i> Tambah Staff</button>
                `;
            } else if (user.parent === currentUser.username) {
                const kanbanStatus = user.has_kanban_access ? 'Tutup Kanban' : 'Buka Kanban';
                actionBtnHTML = `
                    <div style="display: flex; gap: 4px; margin-top: 8px; width: 100%;">
                        <button class="primary-btn toggle-kanban-access-btn" data-username="${username}" style="flex: 2; font-size: 9px; padding: 4px; justify-content: center; background: rgba(10, 132, 255, 0.2); border-color: var(--info); color: var(--info); font-family: inherit;"><i class="ph ph-kanban"></i> ${kanbanStatus}</button>
                        <button class="primary-btn edit-staff-btn" data-username="${username}" style="flex: 2; font-size: 9px; padding: 4px; justify-content: center; background: rgba(242, 201, 76, 0.2); border-color: var(--warning); color: var(--warning); font-family: inherit;" title="Edit Profil Email/Password Staf"><i class="ph ph-pencil-simple"></i> Edit</button>
                        <button class="primary-btn delete-staff-request-btn" data-username="${username}" style="flex: 1; font-size: 9px; padding: 4px; justify-content: center; background: rgba(255, 59, 48, 0.2); border-color: var(--danger); color: var(--danger); font-family: inherit;" title="Minta Hapus Staf"><i class="ph ph-trash"></i></button>
                    </div>
                `;
            }
        }

        card.innerHTML = `
            <div class="node-avatar-circle">${user.avatar}</div>
            <div class="node-name">${user.name}</div>
            <div class="node-title">${user.title}</div>
            <div class="node-badge-row">
                ${selfBadge}
                <span class="node-badge level">${user.level}</span>
                <span class="node-badge tasks-pending ${taskBadgeClass}">${taskBadgeText}</span>
            </div>
            ${actionBtnHTML}
        `;
        
        const toggleKanbanBtn = card.querySelector('.toggle-kanban-access-btn');
        if (toggleKanbanBtn) {
            toggleKanbanBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const targetUname = toggleKanbanBtn.getAttribute('data-username');
                if (state.users[targetUname]) {
                    state.users[targetUname].has_kanban_access = !state.users[targetUname].has_kanban_access;
                    updateState(state);
                    showPremiumNotice("Akses Kanban", `Akses Leads Kanban untuk @${targetUname} ${state.users[targetUname].has_kanban_access ? 'diaktifkan' : 'dinonaktifkan'}.`);
                }
            });
        }

        const editStaffBtn = card.querySelector('.edit-staff-btn');
        if (editStaffBtn) {
            editStaffBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const uname = editStaffBtn.getAttribute('data-username');
                openStaffEditModal(uname);
            });
        }

        const delStaffReqBtn = card.querySelector('.delete-staff-request-btn');
        if (delStaffReqBtn) {
            delStaffReqBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const targetUname = delStaffReqBtn.getAttribute('data-username');
                const targetUserObj = state.users[targetUname] || { name: targetUname };
                showStaffSeparationDialog(targetUserObj, 'request', async separation => {
                    const result = await apiRequest('/api/team-requests', {
                        method: 'POST',
                        body: {
                            action: 'delete',
                            target_username: targetUname,
                            ...separation,
                        },
                    });
                    showPremiumNotice(
                        'Pengajuan Terkirim',
                        escapeHtml(result.message || `Permintaan penonaktifan @${targetUname} telah dikirim kepada CEO.`),
                        { variant: 'success' },
                    );
                });
            });
        }
        
        const addBtn = card.querySelector('.add-hierarchy-btn');
        if (addBtn) {
            addBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const parentNode = addBtn.getAttribute('data-parent-node');
                const addType = addBtn.getAttribute('data-add-type');
                
                window.lastSelectedHierarchyParent = parentNode;
                
                const userRegistrationModal = document.getElementById('user-registration-modal');
                if (userRegistrationModal) {
                    const regRoleSelect = document.getElementById('reg-role');
                    if (regRoleSelect) {
                        regRoleSelect.innerHTML = '';
                        if (addType === 'manager') {
                            regRoleSelect.innerHTML = `
                                <option value="mgr_marketing">ðŸŽ¯ Manager Marketing</option>
                                <option value="mgr_ops">ðŸ—ï¸ Manager Operasional</option>
                                <option value="mgr_finance">ðŸ’° Manager Finance</option>
                                <option value="mgr_hrd">ðŸ‘¥ HR Manager</option>
                            `;
                        } else {
                            if (parentNode === 'mgr_marketing' || (currentUser && currentUser.role === 'mgr_marketing')) {
                                regRoleSelect.innerHTML = `<option value="staff_marketing">ðŸŽ¯ Staff Marketing</option>`;
                            } else if (parentNode === 'mgr_ops' || (currentUser && currentUser.role === 'mgr_ops')) {
                                regRoleSelect.innerHTML = `<option value="staff_ops">ðŸ—ï¸ Staff Operasional</option>`;
                            } else if (parentNode === 'mgr_finance' || (currentUser && currentUser.role === 'mgr_finance')) {
                                regRoleSelect.innerHTML = `<option value="staff_finance">ðŸ’° Staff Finance</option>`;
                            } else if (parentNode === 'mgr_hrd' || (currentUser && currentUser.role === 'mgr_hrd')) {
                                regRoleSelect.innerHTML = `<option value="staff_hrd">ðŸ‘¥ Staff HRD</option>`;
                            } else {
                                regRoleSelect.innerHTML = `
                                    <option value="staff_marketing">ðŸŽ¯ Staff Marketing</option>
                                    <option value="staff_ops">ðŸ—ï¸ Staff Operasional</option>
                                    <option value="staff_finance">ðŸ’° Staff Finance</option>
                                    <option value="staff_hrd">ðŸ‘¥ Staff HRD</option>
                                `;
                            }
                        }
                    }
                    userRegistrationModal.style.display = 'flex';
                    refreshEmployeeIdentityPreview();
                }
            });
        }

        const delBtn = card.querySelector('.delete-manager-btn');
        if (delBtn) {
            delBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                const uname = delBtn.getAttribute('data-username');
                showStaffSeparationDialog(state.users[uname] || { username: uname, name: uname }, 'direct', async separation => {
                    await apiRequest(`/api/users/${encodeURIComponent(uname)}`, {
                        method: 'DELETE',
                        body: separation,
                    });
                    showPremiumNotice(
                        'Manager Dinonaktifkan',
                        'Status keluar tersimpan dan seluruh staf di bawahnya dialihkan kepada atasan berikutnya.',
                        { variant: 'success' },
                    );
                    delete state.users[uname];
                    Object.values(state.users).forEach(user => {
                        if (user.parent === uname) user.parent = 'ceo';
                    });
                    updateState(state);
                    renderOrgChart();
                    drawOrgChartConnections();
                    await syncDataFromServer();
                });
            });
        }
        
        card.addEventListener('click', () => {
            openEmployeeTasksModal(username);
        });
        
        return card;
    }

    function openEmployeeTasksModal(username) {
        const user = state.users[username];
        if (!user) return;
        const userTasks = state.tasks.filter(t => t.username === username);
        
        const modal = document.getElementById('node-detail-modal');
        const nameEl = document.getElementById('modal-employee-name');
        const roleEl = document.getElementById('modal-employee-role');
        const listEl = document.getElementById('modal-tasks-list');
        
        if (!modal || !nameEl || !roleEl || !listEl) return;
        
        const doneTasks = userTasks.filter(t => t.status === 'done').length;
        const totalTasks = userTasks.length;
        const score = totalTasks > 0 ? Math.round((doneTasks / totalTasks) * 100) : 0;
        
        nameEl.innerText = `Daftar Tugas & Performa: ${user.name}`;
        const isHrdOrCeo = currentUser && (currentUser.role === 'ceo' || currentUser.role === 'mgr_hrd' || currentUser.username === 'sonia');
        const docButtonsHtml = isHrdOrCeo ? `
            <div style="display: flex; gap: 8px; margin-top: 10px;">
                <button class="primary-btn btn-modal-slip" style="padding: 4px 10px; font-size: 11px; background: rgba(52, 199, 89, 0.2); border-color: var(--success); color: var(--success); font-family: inherit;"><i class="ph ph-receipt"></i> Cetak Slip Gaji</button>
                <button class="primary-btn btn-modal-paklaring" style="padding: 4px 10px; font-size: 11px; background: rgba(242, 201, 76, 0.2); border-color: var(--warning); color: var(--warning); font-family: inherit;"><i class="ph ph-certificate"></i> Generate Paklaring</button>
            </div>
        ` : '';

        roleEl.innerHTML = `
            ${user.title} (${user.level})<br>
            <span style="font-size: 13px; color: var(--success); font-weight: 700; margin-top: 6px; display: inline-block;"><i class="ph ph-chart-line"></i> KPIM Score: ${score}% (${doneTasks}/${totalTasks} Selesai)</span>
            ${docButtonsHtml}
        `;

        if (isHrdOrCeo) {
            const btnModalSlip = roleEl.querySelector('.btn-modal-slip');
            if (btnModalSlip) {
                btnModalSlip.onclick = () => openSalarySlipModal(username);
            }
            const btnModalPaklaring = roleEl.querySelector('.btn-modal-paklaring');
            if (btnModalPaklaring) {
                btnModalPaklaring.onclick = () => showPaklaringModal(username);
            }
        }
        listEl.innerHTML = '';
        
        if (userTasks.length === 0) {
            listEl.innerHTML = '<div style="text-align: center; color: var(--text-secondary); padding: 20px;">Karyawan tidak memiliki tugas aktif saat ini.</div>';
        } else {
            userTasks.forEach(task => {
                const statusLabels = {
                    'done': { text: 'Done', color: 'var(--success)', bg: 'rgba(52, 199, 89, 0.15)' },
                    'in_progress': { text: 'In Progress', color: 'var(--info)', bg: 'rgba(10, 132, 255, 0.15)' },
                    'revisi': { text: 'Revisi', color: 'var(--warning)', bg: 'rgba(255, 159, 10, 0.15)' },
                    'nearing_deadline': { text: 'Near Deadline', color: 'var(--warning)', bg: 'rgba(255, 159, 10, 0.15)' },
                    'failed': { text: 'Failed / Overdue', color: 'var(--danger)', bg: 'rgba(255, 59, 48, 0.15)' }
                };
                const label = statusLabels[task.status] || { text: task.status, color: 'white', bg: 'rgba(255,255,255,0.1)' };
                const deadlineDate = new Date(task.deadline).toLocaleDateString('id-ID', {
                    month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit'
                });
                
                listEl.innerHTML += `
                    <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); padding: 12px 16px; border-radius: var(--radius-sm); display: flex; justify-content: space-between; align-items: center; gap: 12px; margin-bottom: 8px;">
                        <div style="flex: 1;">
                            <h5 style="margin: 0; font-size: 14px; color: white;">${task.title}</h5>
                            <p style="margin: 4px 0 0 0; font-size: 11px; color: var(--text-secondary);">Kategori: ${task.relation} &bull; Deadline: ${deadlineDate}</p>
                            ${task.feedback ? `<p style="margin: 8px 0 0 0; font-size: 11px; color: var(--warning); background: rgba(255,159,10,0.05); padding: 6px; border-left: 2px solid var(--warning); border-radius: 2px;"><b>Feedback:</b> ${task.feedback}</p>` : ''}
                        </div>
                        <span style="font-size: 11px; padding: 4px 8px; border-radius: 4px; font-weight: 600; color: ${label.color}; background: ${label.bg}; min-width: 80px; text-align: center; white-space: nowrap;">${label.text}</span>
                    </div>
                `;
            });
        }
        
        modal.style.display = 'flex';
    }

    const closeNodeBtn = document.getElementById('close-node-modal-btn');
    if (closeNodeBtn) {
        closeNodeBtn.onclick = () => {
            document.getElementById('node-detail-modal').style.display = 'none';
        };
    }

    function drawOrgChartConnections() {
        const svg = document.getElementById('org-chart-svg');
        if (!svg) return;
        svg.innerHTML = '';
        return;
        
        const chart = document.getElementById('org-chart-render');
        if (!chart) return;
        const chartRect = chart.getBoundingClientRect();
        
        const relations = [
            { parent: 'ceo', child: 'mgr_marketing' },
            { parent: 'ceo', child: 'mgr_ops' },
            { parent: 'ceo', child: 'mgr_finance' },
            { parent: 'mgr_marketing', child: 'maulana' },
            { parent: 'mgr_marketing', child: 'dbest' },
            { parent: 'mgr_ops', child: 'staff_ops' },
            { parent: 'mgr_finance', child: 'staff_finance' }
        ];
        
        relations.forEach(rel => {
            const parentEl = document.getElementById(`node-${rel.parent}`);
            const childEl = document.getElementById(`node-${rel.child}`);
            
            if (parentEl && childEl) {
                const parentRect = parentEl.getBoundingClientRect();
                const childRect = childEl.getBoundingClientRect();
                
                const parentX = (parentRect.left + parentRect.right) / 2 - chartRect.left;
                const parentY = parentRect.bottom - chartRect.top;
                const childX = (childRect.left + childRect.right) / 2 - chartRect.left;
                const childY = childRect.top - chartRect.top;
                
                const midY = parentY + (childY - parentY) / 2;
                const pathData = `M ${parentX} ${parentY} L ${parentX} ${midY} L ${childX} ${midY} L ${childX} ${childY}`;
                
                const statusColor = getNodeStatusColor(rel.child);
                let strokeColor = '#34C759';
                let glowShadow = 'rgba(52, 199, 89, 0.8)';
                
                if (statusColor === 'red') {
                    strokeColor = '#FF3B30';
                    glowShadow = 'rgba(255, 59, 48, 0.8)';
                } else if (statusColor === 'yellow') {
                    strokeColor = '#FF9F0A';
                    glowShadow = 'rgba(255, 159, 10, 0.8)';
                } else if (statusColor === 'blue') {
                    strokeColor = '#0A84FF';
                    glowShadow = 'rgba(10, 132, 255, 0.8)';
                }
                
                const path = document.createElementNS('http://www.w3.org/2000/svg', 'path');
                path.setAttribute('d', pathData);
                path.setAttribute('stroke', strokeColor);
                path.setAttribute('stroke-width', '2.5');
                path.setAttribute('fill', 'none');
                path.setAttribute('style', `filter: drop-shadow(0 0 5px ${glowShadow}); transition: all 0.3s ease;`);
                
                svg.appendChild(path);
            }
        });
    }

    window.addEventListener('resize', () => {
        const hierarchySection = document.getElementById('view-hierarchy');
        if (hierarchySection && hierarchySection.style.display === 'block') {
            drawOrgChartConnections();
        }
    });

    document.getElementById('organization-search-input')?.addEventListener('input', (event) => {
        organizationSearchTerm = event.target.value || '';
        renderOrgChart();
    });

    document.getElementById('organization-division-filter')?.addEventListener('change', (event) => {
        organizationDivisionFilter = event.target.value || 'all';
        renderOrgChart();
    });

    // ================= Navigation Helpers (View Details CEO page) =================
    const divCards = document.querySelectorAll('.division-card');
    if (divCards.length >= 3) {
        const opsLink = divCards[1].querySelector('a');
        if (opsLink) {
            opsLink.addEventListener('click', (e) => {
                e.preventDefault();
                const opt = document.querySelector('[data-target="ops-dashboard"]');
                if (opt) opt.click();
            });
        }
        
        const finLink = divCards[2].querySelector('a');
        if (finLink) {
            finLink.addEventListener('click', (e) => {
                e.preventDefault();
                const opt = document.querySelector('[data-target="finance-dashboard"]');
                if (opt) opt.click();
            });
        }
    }

    // ================= Modals Dismiss Window Click =================
    window.onclick = (e) => {
        const nodeModal = document.getElementById('node-detail-modal');
        const slipModal = document.getElementById('salary-slip-modal');
        const newLeadModal = document.getElementById('new-lead-modal');
        if (e.target === nodeModal) nodeModal.style.display = 'none';
        if (e.target === slipModal) slipModal.style.display = 'none';
        if (e.target === newLeadModal) newLeadModal.style.display = 'none';
    };

    function renderMyLeaveHistory() {
        const list = document.getElementById('my-leave-history-list');
        if (!list || !currentUser) return;

        const ownRequests = (state.leaveRequests || [])
            .filter(item => item.username === currentUser.username)
            .slice(0, 8);

        if (!ownRequests.length) {
            list.innerHTML = '<div style="color: var(--text-muted); font-size: 11px;">Belum ada riwayat pengajuan cuti.</div>';
            return;
        }

        const statusLabels = {
            pending_manager: 'Menunggu Manager',
            pending_ceo: 'Menunggu CEO',
            approved: 'Disetujui',
            rejected: 'Ditolak'
        };

        list.innerHTML = ownRequests.map(item => `
            <div class="governed-data-row" style="padding: 9px;">
                <div>
                    <strong>${escapeHtml(item.type || 'Cuti')}</strong>
                    <span>${escapeHtml(item.startDate || '-')} s/d ${escapeHtml(item.endDate || '-')} Â· ${escapeHtml(statusLabels[item.status] || item.status)}</span>
                </div>
                <div class="governed-row-actions">
                ${item.can_edit ? `<button type="button" class="erp-edit-btn icon-only" data-edit-leave="${item.id}" title="Edit pengajuan cuti"><i class="ph ph-pencil-simple"></i></button>` : ''}
                <button type="button" class="erp-delete-btn icon-only" data-erp-delete
                    data-resource-type="leave_request"
                    data-resource-id="${item.id}"
                    data-resource-label="Pengajuan cuti ${escapeHtml(item.startDate || '')}"
                    title="Hapus atau ajukan penghapusan cuti">
                    <i class="ph ph-trash"></i>
                </button></div>
            </div>
        `).join('');

        list.querySelectorAll('[data-edit-leave]').forEach(button => {
            button.onclick = () => {
                const item = ownRequests.find(row => Number(row.id) === Number(button.dataset.editLeave));
                const form = document.getElementById('leave-request-form');
                if (!item || !form) return;
                document.getElementById('leave-type-select').value = item.type || '';
                document.getElementById('leave-start-date').value = item.startDate || '';
                document.getElementById('leave-end-date').value = item.endDate || '';
                document.getElementById('leave-reason').value = item.reason || '';
                form.dataset.editLeaveId = String(item.id);
                document.getElementById('leave-request-modal').style.display = 'flex';
            };
        });
    }

    function renderCEOLeaveOverview() {
        const pendingList = document.getElementById('ceo-pending-leaves-list');
        const activeList = document.getElementById('ceo-active-leaves-list');
        if (!pendingList || !activeList) return;
        
        pendingList.innerHTML = '';
        activeList.innerHTML = '';
        
        const pending = (state.leaveRequests || []).filter(r => r.status === 'pending_ceo');
        const today = todayJakarta();
        const active = (state.leaveRequests || []).filter(r =>
            r.status === 'approved' && r.startDate <= today && r.endDate >= today
        );
        
        if (pending.length === 0) {
            pendingList.innerHTML = '<div style="color: var(--text-muted); font-size: 11px; font-style: italic; padding: 10px 0; width: 100%;">âœ¨ Tidak ada pengajuan cuti yang membutuhkan persetujuan CEO saat ini.</div>';
        } else {
            pending.forEach(r => {
                pendingList.innerHTML += `
                    <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); padding: 10px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; font-size: 11px; width: 100%;">
                        <div style="flex: 1; padding-right: 8px;">
                            <b style="color: white;">${r.name}</b>
                            <div style="color: var(--text-secondary); margin-top: 2px;">${r.type} &bull; ${r.startDate} s/d ${r.endDate}</div>
                            <div style="color: var(--text-muted); font-size: 10px; margin-top: 4px; font-style: italic;">"${r.reason}"</div>
                        </div>
                        <div style="display: flex; gap: 6px; flex-shrink: 0;">
                            <button class="primary-btn reject-leave-btn" data-leaveid="${r.id}" style="padding: 4px 8px; background: var(--danger); font-size: 10px; font-family: inherit;"><i class="ph ph-x"></i> Reject</button>
                            <button class="primary-btn approve-leave-btn" data-leaveid="${r.id}" style="padding: 4px 8px; background: var(--success); color: black; font-size: 10px; font-family: inherit;"><i class="ph ph-check"></i> Approve</button>
                        </div>
                    </div>
                `;
            });
        }
        
        if (active.length === 0) {
            activeList.innerHTML = '<div style="color: var(--text-muted); font-size: 11px; font-style: italic; padding: 10px 0; width: 100%;">ðŸŒ´ Seluruh karyawan aktif bekerja (tidak ada yang sedang cuti saat ini).</div>';
        } else {
            active.forEach(r => {
                activeList.innerHTML += `
                    <div style="background: rgba(52,199,89,0.05); border: 1px solid rgba(52,199,89,0.2); padding: 8px; border-radius: 6px; font-size: 11px; color: white; width: 100%;">
                        <i class="ph ph-user-check" style="color: var(--success);"></i> <b>${r.name}</b> sedang cuti (${r.type})
                        <div style="color: var(--text-secondary); font-size: 10px; margin-top: 2px;">Tanggal: ${r.startDate} s/d ${r.endDate}</div>
                    </div>
                `;
            });
        }
        
        pendingList.querySelectorAll('.approve-leave-btn').forEach(btn => {
            btn.onclick = async () => {
                const lid = btn.getAttribute('data-leaveid');
                const req = state.leaveRequests.find(r => String(r.id) === String(lid));
                if (!req?.approval_id) return;
                try {
                    const result = await apiRequest(`/api/approvals/${req.approval_id}/approve`, {
                        method: 'POST',
                        body: { note: 'Cuti disetujui CEO.' }
                    });
                    showPremiumNotice('Cuti Disetujui', escapeHtml(result.message));
                    await syncDataFromServer();
                } catch (error) {
                    showPremiumNotice('Tidak Dapat Memproses', escapeHtml(error.message));
                }
            };
        });
        
        pendingList.querySelectorAll('.reject-leave-btn').forEach(btn => {
            btn.onclick = () => {
                const lid = btn.getAttribute('data-leaveid');
                const req = state.leaveRequests.find(r => String(r.id) === String(lid));
                if (!req?.approval_id) return;
                showTextInputDialog({
                    title: 'Alasan Penolakan Cuti',
                    description: 'Catatan akan diterima oleh pengaju dan masuk ke riwayat keputusan.',
                    label: 'Alasan penolakan',
                    defaultValue: 'Pengajuan belum dapat disetujui.',
                    submitText: 'Tolak Cuti'
                }, async note => {
                    try {
                        const result = await apiRequest(`/api/approvals/${req.approval_id}/reject`, {
                            method: 'POST',
                            body: { note }
                        });
                        showPremiumNotice('Cuti Ditolak', escapeHtml(result.message), { variant: 'success' });
                        await syncDataFromServer();
                    } catch (error) {
                        showPremiumNotice('Tidak Dapat Memproses', escapeHtml(error.message), { variant: 'danger' });
                    }
                });
            };
        });
    }

    function renderManagerLeaveApprovals() {
        const card = document.getElementById('manager-leave-approval-card');
        const listEl = document.getElementById('manager-leave-requests-list');
        if (!card || !listEl) return;
        
        const isManager = currentUser && currentUser.role.startsWith('mgr_');
        if (!isManager) {
            card.style.display = 'none';
            return;
        }
        
        card.style.display = 'block';
        listEl.innerHTML = '';
        
        const requests = (state.leaveRequests || []).filter(r => 
            r.status === 'pending_manager' && r.approver === currentUser.username
        );
        
        if (requests.length === 0) {
            listEl.innerHTML = `
                <div style="background: rgba(255,255,255,0.02); border: 1px dashed var(--glass-border); border-radius: var(--radius-md); padding: 14px; text-align: center; font-size: 12px; color: var(--text-secondary); width: 100%;">
                    <i class="ph ph-check-circle" style="font-size: 20px; color: var(--success); margin-bottom: 4px; display: block;"></i>
                    <b>Tidak Ada Pengajuan Cuti Staf</b>
                    <p style="margin: 4px 0 0 0; font-size: 11px; color: var(--text-muted);">Pengajuan cuti dari staf tim bawahan Anda akan otomatis muncul di sini untuk Anda selesaikan.</p>
                </div>
            `;
            return;
        }
        
        requests.forEach(r => {
            listEl.innerHTML += `
                <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); padding: 10px; border-radius: 6px; display: flex; justify-content: space-between; align-items: center; font-size: 11px; width: 100%;">
                    <div style="flex: 1; padding-right: 8px;">
                        <b style="color: white;">${r.name}</b>
                        <div style="color: var(--text-secondary); margin-top: 2px;">${r.type} &bull; ${r.startDate} s/d ${r.endDate}</div>
                        <div style="color: var(--text-muted); font-size: 10px; margin-top: 4px; font-style: italic;">"${r.reason}"</div>
                    </div>
                    <div style="display: flex; gap: 6px; flex-shrink: 0;">
                        <button class="primary-btn mgr-reject-leave-btn" data-leaveid="${r.id}" style="padding: 4px 8px; background: var(--danger); font-size: 10px; font-family: inherit;"><i class="ph ph-x"></i></button>
                        <button class="primary-btn mgr-approve-leave-btn" data-leaveid="${r.id}" style="padding: 4px 8px; background: var(--success); color: black; font-size: 10px; font-family: inherit;"><i class="ph ph-check"></i></button>
                    </div>
                </div>
            `;
        });
        
        listEl.querySelectorAll('.mgr-approve-leave-btn').forEach(btn => {
            btn.onclick = async () => {
                const lid = btn.getAttribute('data-leaveid');
                const req = (state.leaveRequests || []).find(r => String(r.id) === String(lid));
                if (req?.approval_id) {
                    try {
                        const result = await apiRequest(`/api/approvals/${req.approval_id}/approve`, {
                            method: 'POST',
                            body: { note: 'Disetujui manager dan diteruskan ke CEO.' }
                        });
                        showPremiumNotice('Cuti Diteruskan ke CEO', escapeHtml(result.message));
                        await syncDataFromServer();
                    } catch (error) {
                        showPremiumNotice('Tidak Dapat Memproses', escapeHtml(error.message));
                    }
                }
            };
        });
        
        listEl.querySelectorAll('.mgr-reject-leave-btn').forEach(btn => {
            btn.onclick = () => {
                const lid = btn.getAttribute('data-leaveid');
                const req = (state.leaveRequests || []).find(r => String(r.id) === String(lid));
                if (req?.approval_id) {
                    showTextInputDialog({
                        title: 'Alasan Penolakan Cuti',
                        description: 'Berikan alasan yang jelas agar staf memahami keputusan Manager.',
                        label: 'Alasan penolakan',
                        defaultValue: 'Pengajuan belum dapat disetujui.',
                        submitText: 'Tolak Cuti'
                    }, async note => {
                        try {
                            const result = await apiRequest(`/api/approvals/${req.approval_id}/reject`, {
                                method: 'POST',
                                body: { note }
                            });
                            showPremiumNotice('Cuti Ditolak', escapeHtml(result.message), { variant: 'success' });
                            await syncDataFromServer();
                        } catch (error) {
                            showPremiumNotice('Tidak Dapat Memproses', escapeHtml(error.message), { variant: 'danger' });
                        }
                    });
                }
            };
        });
    }

    async function renderRulesEngine() {
        const container = document.getElementById('rules-engine-container');
        if (!container) return;
        
        // Load rules from DB if state.rules is empty
        if (!state.rules || state.rules.length === 0) {
            try {
                const res = await fetch('/api/rules');
                if (res.ok) {
                    state.rules = await res.json();
                    updateState(state);
                }
            } catch (e) {
                console.error(e);
            }
        }
        
        container.innerHTML = '';
        
        const rules = state.rules || [
            { id: 1, condition: 'Score â‰¥ 85%', reward: 'Bonus 1.5%', type: 'success' },
            { id: 2, condition: 'Score â‰¥ 75%', reward: '-D-Point, Bonus 0.5%', type: 'warning' },
            { id: 3, condition: 'Score < 65%', reward: '-D-Point, SP 1, Bonus 0%', type: 'danger' }
        ];
        
        rules.forEach(r => {
            let bg = 'rgba(52, 199, 89, 0.1)';
            let border = 'rgba(52, 199, 89, 0.2)';
            let color = 'var(--success)';
            if (r.type === 'warning') {
                bg = 'rgba(255, 159, 10, 0.1)';
                border = 'rgba(255, 159, 10, 0.2)';
                color = 'var(--warning)';
            } else if (r.type === 'danger') {
                bg = 'rgba(255, 59, 48, 0.1)';
                border = 'rgba(255, 59, 48, 0.2)';
                color = 'var(--danger)';
            }
            
            const deleteHtml = r.can_delete ? `
                <button type="button" class="erp-delete-btn icon-only" data-erp-delete
                    data-resource-type="rule"
                    data-resource-id="${r.id}"
                    data-resource-label="Aturan KPI ${escapeHtml(r.condition)}"
                    title="Hapus atau ajukan penghapusan aturan">
                    <i class="ph ph-trash"></i>
                </button>` : '';
            const editHtml = r.can_edit ? `
                <button type="button" class="erp-edit-btn icon-only" data-edit-rule="${r.id}" title="Edit aturan">
                    <i class="ph ph-pencil-simple"></i>
                </button>` : '';
            
            container.innerHTML += `
                <div style="display: flex; justify-content: space-between; align-items: center; background: ${bg}; border: 1px solid ${border}; padding: 10px; border-radius: var(--radius-sm); width: 100%;">
                    <div>
                        <span style="font-weight: 500; color: white; margin-right: 8px;">${escapeHtml(r.condition)}</span>
                        <span style="color: ${color}; font-weight: 600;">${escapeHtml(r.reward)}</span>
                        <div style="font-size: 10px; color: var(--text-muted); margin-top: 4px;"><i class="ph ph-buildings"></i> ${escapeHtml(r.scope_label || 'Seluruh perusahaan')}</div>
                    </div>
                    <div class="governed-row-actions">${editHtml}${deleteHtml}</div>
                </div>
            `;
        });

        container.querySelectorAll('[data-edit-rule]').forEach(button => {
            button.onclick = () => openRuleDialog(rules.find(rule => Number(rule.id) === Number(button.dataset.editRule)));
        });
        
        const btnAddRule = document.getElementById('btn-add-new-rule');
        if (btnAddRule) {
            const canManageRules = currentUser && (currentUser.role === 'ceo' || currentUser.role.startsWith('mgr_'));
            btnAddRule.style.display = canManageRules ? 'block' : 'none';
            btnAddRule.onclick = () => {
                openRuleDialog();
            };
        }
    }

    function openRuleDialog(existingRule = null) {
        if (!currentUser || (currentUser.role !== 'ceo' && !currentUser.role.startsWith('mgr_'))) return;

        const modal = document.getElementById('rule-dialog-modal');
        const form = document.getElementById('rule-dialog-form');
        const condition = document.getElementById('rule-dialog-condition');
        const reward = document.getElementById('rule-dialog-reward');
        const type = document.getElementById('rule-dialog-type');
        const division = document.getElementById('rule-dialog-division');
        const divisionGroup = document.getElementById('rule-dialog-division-group');
        const cancel = document.getElementById('btn-rule-dialog-cancel');
        const error = document.getElementById('rule-dialog-error');
        if (!modal || !form || !condition || !reward || !type || !division || !divisionGroup || !cancel || !error) return;

        form.reset();
        condition.value = 'Score â‰¥ 80%';
        reward.value = 'Bonus 1.0%';
        if (existingRule) {
            condition.value = existingRule.condition || condition.value;
            reward.value = existingRule.reward || reward.value;
            type.value = existingRule.type || 'success';
        }
        error.style.display = 'none';

        const isCeo = currentUser.role === 'ceo';
        divisionGroup.style.display = isCeo ? 'block' : 'none';
        division.value = isCeo ? (existingRule?.division || '') : (divisionFromRole(currentUser.role) || '');
        modal.style.display = 'flex';
        setTimeout(() => condition.focus(), 50);

        const close = () => {
            modal.style.display = 'none';
            form.onsubmit = null;
            cancel.onclick = null;
        };

        cancel.onclick = close;
        form.onsubmit = async event => {
            event.preventDefault();
            error.style.display = 'none';
            try {
                const result = await apiRequest(existingRule ? `/api/rules/${existingRule.id}` : '/api/rules', {
                    method: existingRule ? 'PUT' : 'POST',
                    body: {
                        condition: condition.value.trim(),
                        reward: reward.value.trim(),
                        type: type.value,
                        division: division.value || null
                    }
                });
                if (!state.rules) state.rules = [];
                if (existingRule) {
                    state.rules = state.rules.map(rule => Number(rule.id) === Number(existingRule.id) ? result.rule : rule);
                } else {
                    state.rules.push(result.rule);
                }
                close();
                updateState(state);
                renderRulesEngine();
                showPremiumNotice(existingRule ? 'Aturan KPI Diperbarui' : 'Aturan KPI Ditambahkan', escapeHtml(result.message), { variant: 'success' });
            } catch (requestError) {
                error.textContent = requestError.message;
                error.style.display = 'block';
            }
        };
    }

    async function renderCeoTeamRequests() {
        const listEl = document.getElementById('ceo-team-requests-list');
        const cardEl = document.getElementById('ceo-team-requests-card');
        if (!listEl || !cardEl) return;
        
        const isCeo = currentUser && currentUser.role === 'ceo';
        if (!isCeo) {
            cardEl.style.display = 'none';
            return;
        }
        
        cardEl.style.display = 'block';
        
        try {
            const res = await fetch('/api/team-requests');
            if (res.ok) {
                const reqs = await res.json();
                listEl.innerHTML = '';
                
                if (reqs.length === 0) {
                    listEl.innerHTML = '<div style="color: var(--text-muted); font-size: 11px; font-style: italic; padding: 10px 0; text-align: center;">Tidak ada pengajuan persetujuan tim saat ini.</div>';
                    return;
                }
                
                reqs.forEach(r => {
                    let actionText = '';
                    let badgeColor = 'var(--success)';
                    let desc = '';
                    
                    if (r.action === 'add') {
                        actionText = 'Penambahan Staf Baru';
                        badgeColor = 'rgba(52, 199, 89, 0.2)';
                        desc = `Nama: <b>${r.details.name}</b> (@${r.details.username})<br>Email: ${r.details.email}<br>Role: ${r.details.role} &bull; Kontrak: ${r.details.employment_type || 'Full-Time'}`;
                    } else {
                        actionText = 'Penghapusan Staf';
                        badgeColor = 'rgba(255, 59, 48, 0.2)';
                        desc = `Manager meminta menghapus akun: <b>@${r.target_username}</b>`;
                    }
                    
                    listEl.innerHTML += `
                        <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); padding: 14px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; font-size: 12px; width: 100%;">
                            <div style="flex: 1; padding-right: 12px; line-height: 1.5;">
                                <span class="badge" style="background: ${badgeColor}; color: white; font-weight: 700; font-size: 9px; margin-bottom: 6px; padding: 2px 6px; border-radius: 4px; border: 1px solid var(--glass-border);">${actionText}</span>
                                <div style="color: var(--text-muted); font-size: 11px; margin-bottom: 4px;">Diajukan oleh: @${r.requester_username}</div>
                                <div style="color: white;">${desc}</div>
                            </div>
                            <div style="display: flex; gap: 8px; flex-shrink: 0;">
                                <button class="primary-btn reject-req-btn" data-reqid="${r.id}" style="padding: 6px 12px; background: var(--danger); font-size: 11px; font-family: inherit;"><i class="ph ph-x"></i> Tolak</button>
                                <button class="primary-btn approve-req-btn" data-reqid="${r.id}" style="padding: 6px 12px; background: var(--success); color: black; font-size: 11px; font-family: inherit;"><i class="ph ph-check"></i> Setujui</button>
                            </div>
                        </div>
                    `;
                });
                
                listEl.querySelectorAll('.approve-req-btn').forEach(btn => {
                    btn.onclick = () => {
                        const reqid = btn.getAttribute('data-reqid');
                        showCustomConfirm("Setujui Pengajuan", "Apakah Anda yakin ingin menyetujui pengajuan tim ini?", async () => {
                            try {
                                const response = await fetch(`/api/team-requests/${reqid}/approve`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                    }
                                });
                                const data = await response.json();
                                if (data.success) {
                                    showPremiumNotice('Pengajuan Disetujui', 'Perubahan tim telah diterapkan dan disinkronkan.', { variant: 'success' });
                                    renderCeoTeamRequests();
                                    syncDataFromServer();
                                } else {
                                    showPremiumNotice('Gagal Menyetujui', escapeHtml(data.message || 'Pengajuan tidak dapat diproses.'), { variant: 'danger' });
                                }
                            } catch (err) {
                                console.error(err);
                            }
                        });
                    };
                });
                
                listEl.querySelectorAll('.reject-req-btn').forEach(btn => {
                    btn.onclick = () => {
                        const reqid = btn.getAttribute('data-reqid');
                        showCustomConfirm("Tolak Pengajuan", "Apakah Anda yakin ingin menolak pengajuan tim ini?", async () => {
                            try {
                                const response = await fetch(`/api/team-requests/${reqid}/reject`, {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                                    }
                                });
                                const data = await response.json();
                                if (data.success) {
                                    showPremiumNotice('Pengajuan Ditolak', 'Keputusan penolakan telah tercatat.', { variant: 'success' });
                                    renderCeoTeamRequests();
                                }
                            } catch (err) {
                                console.error(err);
                            }
                        });
                    };
                });
            }
        } catch (e) {
            console.error(e);
        }
     }

    // ================= Universal Render Dispatcher =================
    function renderAll() {
        if (!currentUser) return;
        renderKanban();
        renderMaulanaWorkspace();
        renderDBestWorkspace();
        renderCEOApprovalInbox();
        renderKPIProposals();
        renderPayrollTable();
        renderAttendanceLogs();
        renderOrgChart();
        updateCEODivisionCards();
        renderGanttChart();
        renderOmzetChart();
        
        // Revisions rendering
        renderCEOPerformanceChart();
        renderCEOComments();
        renderKPITasksView();
        renderHRDWorkspace();
        renderNotifications();
        renderMyLeaveHistory();
        renderCEOLeaveOverview();
        renderManagerLeaveApprovals();
        renderRulesEngine();
        renderCeoTeamRequests();
        renderResignationHistory();
        renderGovernedPerformanceData();
        
        const myLeaveCard = document.getElementById('my-leave-request-card');
        if (myLeaveCard) {
            myLeaveCard.style.display = (currentUser && currentUser.role === 'ceo') ? 'none' : 'block';
        }
    }

    // ================= Chat Panel and Bell Dropdowns =================
    const chatToggleBtn = document.getElementById('chat-toggle-btn');
    const closeChatBtn = document.getElementById('close-chat-btn');
    const chatPanel = document.getElementById('chat-panel');
    const chatOverlay = document.getElementById('chat-overlay');

    function syncMobileVisualViewport() {
        const viewport = window.visualViewport;
        const height = Math.round(viewport?.height || window.innerHeight);
        const offsetTop = Math.round(viewport?.offsetTop || 0);
        const keyboardVisible = Boolean(
            viewport
            && window.matchMedia('(max-width: 768px)').matches
            && window.innerHeight - viewport.height > 120
        );

        document.documentElement.style.setProperty('--mobile-viewport-height', `${height}px`);
        document.documentElement.style.setProperty('--mobile-viewport-offset-top', `${offsetTop}px`);
        document.body.classList.toggle('mobile-keyboard-open', keyboardVisible);
        chatPanel?.classList.toggle('keyboard-visible', keyboardVisible);

        if (keyboardVisible && chatPanel?.classList.contains('active')) {
            requestAnimationFrame(() => {
                const body = document.getElementById('chat-body');
                if (body) body.scrollTop = body.scrollHeight;
            });
        }
    }

    syncMobileVisualViewport();
    window.visualViewport?.addEventListener('resize', syncMobileVisualViewport);
    window.visualViewport?.addEventListener('scroll', syncMobileVisualViewport);
    window.addEventListener('resize', syncMobileVisualViewport);

    // ================= Slack-Like Chat Channels & Messaging System =================
    const allChannels = [
        { id: 'general', name: 'general', roles: ['*'] },
        { id: 'marketing-team', name: 'marketing-team', roles: ['ceo', 'mgr_marketing', 'staff_marketing'] },
        { id: 'operations-team', name: 'operations-team', roles: ['ceo', 'mgr_ops', 'staff_ops'] },
        { id: 'finance-team', name: 'finance-team', roles: ['ceo', 'mgr_finance', 'staff_finance'] },
        { id: 'hr-team', name: 'hr-team', roles: ['ceo', 'mgr_hrd', 'staff_hrd', 'hrd', 'hrd_manager', 'hr'] },
        { id: 'management', name: 'management', roles: ['ceo', 'mgr_marketing', 'mgr_ops', 'mgr_finance', 'mgr_hrd'] }
    ];

    function renderLegacyChatChannels() {
        const selector = document.getElementById('chat-channel-selector');
        if (!selector) return;

        if (!currentUser) {
            selector.innerHTML = '<option value="">Login terlebih dahulu</option>';
            return;
        }

        const userRole = currentUser.role;
        const myChannels = allChannels.filter(channel => {
            return channel.roles.includes('*') || channel.roles.includes(userRole) || channel.roles.includes(currentUser.username);
        });

        selector.innerHTML = myChannels.map(channel => {
            return `<option value="${channel.id}"># ${channel.name}</option>`;
        }).join('');

        renderLegacyChatMessages();
    }

    function renderLegacyChatMessages() {
        const body = document.getElementById('chat-body');
        const selector = document.getElementById('chat-channel-selector');
        if (!body || !selector) return;

        const activeChannel = selector.value;
        if (!activeChannel) {
            body.innerHTML = '<div style="text-align: center; color: var(--text-secondary); padding: 20px;">Pilih saluran chat terlebih dahulu.</div>';
            return;
        }

        const messages = (state.chatMessages || []).filter(msg => msg.channel === activeChannel);
        body.innerHTML = '';

        if (messages.length === 0) {
            body.innerHTML = `<div style="text-align: center; color: var(--text-secondary); padding: 20px;">Belum ada pesan di #${activeChannel}. Mulai percakapan!</div>`;
            return;
        }

        messages.forEach(msg => {
            const isMe = currentUser && msg.sender === currentUser.username;
            const bubbleClass = isMe ? 'me' : 'other';
            const senderLabel = isMe ? 'You' : msg.senderName;
            
            const bubble = document.createElement('div');
            bubble.className = `chat-bubble ${bubbleClass}`;
            bubble.innerHTML = `
                <span class="chat-sender">${senderLabel}</span>
                <div class="chat-text">${msg.text}</div>
            `;
            body.appendChild(bubble);
        });

        body.scrollTop = body.scrollHeight;
    }

    function sendLegacyChatMessage() {
        const input = document.getElementById('chat-input-field');
        const selector = document.getElementById('chat-channel-selector');
        if (!input || !selector || !currentUser) return;

        const text = input.value.trim();
        const activeChannel = selector.value;
        if (!text || !activeChannel) return;

        const senderDetails = state.users[currentUser.username] || currentUser;
        
        const newMsg = {
            id: 'msg-' + Date.now(),
            channel: activeChannel,
            sender: currentUser.username,
            senderName: senderDetails.name || currentUser.username,
            text: text,
            timestamp: Date.now()
        };

        if (!state.chatMessages) state.chatMessages = [];
        state.chatMessages.push(newMsg);
        updateState(state);

        input.value = '';
        renderChatMessages();

        // Handle AI query if message mentions "@AI"
        if (text.toLowerCase().includes('@ai')) {
            triggerLegacyAIChatReply(text, activeChannel);
        }
    }

    function triggerLegacyAIChatReply(userText, channel) {
        const body = document.getElementById('chat-body');
        if (!body) return;

        const typingIndicator = document.createElement('div');
        typingIndicator.className = 'chat-bubble other';
        typingIndicator.id = 'ai-typing-indicator';
        typingIndicator.innerHTML = `
            <span class="chat-sender">Suba-Arch AI Copilot</span>
            <div class="chat-text" style="font-style: italic; color: var(--text-secondary);"><i class="ph ph-spinner" style="animation: spin 1s infinite linear; display: inline-block;"></i> AI sedang mengetik...</div>
        `;
        body.appendChild(typingIndicator);
        body.scrollTop = body.scrollHeight;

        setTimeout(() => {
            const indicator = document.getElementById('ai-typing-indicator');
            if (indicator) indicator.remove();

            let aiResponse = "Fitur prototipe lama tidak digunakan.";
            if (userText.toLowerCase().includes('target') || userText.toLowerCase().includes('omzet')) {
                aiResponse = `Berdasarkan data Kanban terkini, total omzet deal kita adalah Rp ${state.leads.filter(l => l.column === 'deal').length * 10.8}M.`;
            } else if (userText.toLowerCase().includes('kerja') || userText.toLowerCase().includes('kpi')) {
                aiResponse = "Anda dapat melihat target performa dan tugas pending masing-masing staf di panel KPI Divisi.";
            }

            const newMsg = {
                id: 'msg-' + Date.now(),
                channel: channel,
                sender: 'ai-copilot',
                senderName: 'ðŸš€ Suba-Arch AI Copilot',
                text: aiResponse,
                timestamp: Date.now()
            };

            if (!state.chatMessages) state.chatMessages = [];
            state.chatMessages.push(newMsg);
            updateState(state);
            renderLegacyChatMessages();
        }, 1200);
    }

    // Server-authoritative chat overrides the legacy local prototype above.
    function renderChatChannels() {
        const selector = document.getElementById('chat-channel-selector');
        if (!selector) return;
        if (!currentUser) {
            selector.innerHTML = '<option value="">Login terlebih dahulu</option>';
            return;
        }

        const serverChannels = Array.isArray(state.chatChannels) ? state.chatChannels : [];
        const channels = serverChannels.length
            ? allChannels.filter(channel => serverChannels.includes(channel.id))
            : allChannels.filter(channel => channel.roles.includes('*') || channel.roles.includes(currentUser.role));
        const previousChannel = selector.value;
        selector.innerHTML = channels.map(channel => `<option value="${channel.id}"># ${channel.name}</option>`).join('');
        if (previousChannel && channels.some(channel => channel.id === previousChannel)) {
            selector.value = previousChannel;
        }
        renderChatMessages();
    }

    function renderChatMessages() {
        const body = document.getElementById('chat-body');
        const selector = document.getElementById('chat-channel-selector');
        if (!body || !selector) return;
        const activeChannel = selector.value;
        const messages = (state.chatMessages || []).filter(message => message.channel === activeChannel);
        body.innerHTML = '';

        if (!activeChannel) {
            body.innerHTML = '<div class="approval-empty-state"><strong>Kanal belum tersedia</strong><p>Silakan tunggu proses sinkronisasi akun.</p></div>';
            return;
        }
        if (!messages.length) {
            body.innerHTML = `<div class="approval-empty-state"><strong>Belum ada percakapan</strong><p>Belum ada pesan terbaru di #${escapeHtml(activeChannel)}. Mulai percakapan untuk tim Anda.</p></div>`;
            return;
        }

        messages.forEach(message => {
            const isMe = message.sender === currentUser?.username;
            const senderLabel = isMe ? 'Anda' : (message.senderName || 'Pengguna');
            const createdAt = message.timestamp
                ? new Intl.DateTimeFormat('id-ID', {
                    timeZone: JAKARTA_TIMEZONE,
                    day: '2-digit',
                    month: 'short',
                    hour: '2-digit',
                    minute: '2-digit'
                }).format(new Date(message.timestamp)).replace(/\./g, ':')
                : '';
            const attachmentHtml = message.attachment ? `
                <a class="chat-attachment-link" href="${escapeHtml(message.attachment.download_url)}" target="_blank" rel="noopener">
                    <i class="ph ph-paperclip"></i>
                    <span>${escapeHtml(message.attachment.name || 'Lampiran')}</span>
                    <i class="ph ph-download-simple" style="margin-left:auto;"></i>
                </a>
            ` : '';
            const deletionHtml = message.id && (isMe || currentUser?.role === 'ceo') ? `
                <button type="button" class="erp-delete-btn chat-delete-message" data-erp-delete
                    data-resource-type="chat_message"
                    data-resource-id="${message.id}"
                    data-resource-label="${message.type === 'holiday_announcement' ? 'Pengumuman hari libur' : 'Pesan chat'} oleh ${escapeHtml(senderLabel)}"
                    title="Redaksi atau ajukan penghapusan pesan">
                    <i class="ph ph-trash"></i> Hapus
                </button>` : '';
            const element = document.createElement('div');

            if (message.type === 'holiday_announcement') {
                const metadata = message.metadata || {};
                element.className = 'chat-announcement';
                element.innerHTML = `
                    <div class="chat-announcement-title"><i class="ph-fill ph-megaphone"></i> ${escapeHtml(metadata.title || 'Pengumuman Hari Libur')}</div>
                    <div class="chat-announcement-period">${escapeHtml(metadata.start_date || '')} s.d. ${escapeHtml(metadata.end_date || '')} Â· ${escapeHtml(senderLabel)}</div>
                    <div class="chat-text" style="padding:0; background:none;">${escapeHtml(message.text || '').replace(/\n/g, '<br>')}</div>
                    ${attachmentHtml}
                    <div class="chat-message-time">${escapeHtml(createdAt)} ${deletionHtml}</div>
                `;
            } else {
                element.className = message.type === 'ai_response'
                    ? 'chat-bubble other ai-response'
                    : `chat-bubble ${isMe ? 'me' : 'other'}`;
                element.innerHTML = `
                    <span class="chat-sender">${escapeHtml(senderLabel)}</span>
                    <div class="chat-text">
                        ${escapeHtml(message.text || '').replace(/\n/g, '<br>')}
                        ${attachmentHtml}
                        <div class="chat-message-time">${escapeHtml(createdAt)} ${deletionHtml}</div>
                    </div>
                `;
            }
            body.appendChild(element);
        });
        body.scrollTop = body.scrollHeight;
    }

    async function syncChatMessages(render = true) {
        if (!currentUser || chatRealtimeBusy) return;
        chatRealtimeBusy = true;
        try {
            const payload = await apiRequest('/api/chat-messages?limit=200');
            state.chatChannels = payload.channels || [];
            state.chatMessages = payload.messages || [];
            if (render) renderChatChannels();
        } catch (error) {
            if (error.status !== 401) console.error('Gagal menyinkronkan chat:', error);
        } finally {
            chatRealtimeBusy = false;
        }
    }

    async function sendChatMessage() {
        const input = document.getElementById('chat-input-field');
        const selector = document.getElementById('chat-channel-selector');
        const attachmentInput = document.getElementById('chat-attachment-input');
        if (!input || !selector || !currentUser) return;

        const text = input.value.trim();
        const attachment = attachmentInput?.files?.[0] || null;
        if ((!text && !attachment) || !selector.value) return;
        const shouldAskGemini = /(^|\s)@ai\b/i.test(text);
        const geminiQuestion = text.replace(/(^|\s)@ai\b/ig, ' ').trim();
        const activeChannel = selector.value;

        const formData = new FormData();
        formData.append('channel', activeChannel);
        if (text) formData.append('message', text);
        if (attachment) formData.append('attachment', attachment);

        const sendButton = document.getElementById('send-chat-btn');
        if (sendButton) sendButton.disabled = true;
        try {
            const payload = await apiRequest('/api/chat-messages', { method: 'POST', body: formData });
            input.value = '';
            if (attachmentInput) attachmentInput.value = '';
            updateChatAttachmentPreview();
            state.chatMessages = [...(state.chatMessages || []), payload.message]
                .filter((item, index, items) => items.findIndex(candidate => candidate.id === item.id) === index);
            renderChatMessages();
            await syncChatMessages(true);
            if (shouldAskGemini && geminiQuestion) {
                await askGeminiInChannel(geminiQuestion, activeChannel);
            }
        } catch (error) {
            showPremiumNotice('Pesan Belum Terkirim', escapeHtml(error.message), { variant: 'danger' });
        } finally {
            if (sendButton) sendButton.disabled = false;
        }
    }

    async function askGeminiInChannel(question, channel) {
        const body = document.getElementById('chat-body');
        const typingId = `gemini-channel-typing-${Date.now()}`;
        if (body) {
            body.insertAdjacentHTML('beforeend', `
                <div class="chat-bubble other ai-response" id="${typingId}">
                    <span class="chat-sender">Suba-Arch Copilot</span>
                    <div class="chat-text"><span class="ai-copilot-typing"><i class="ph ph-spinner ph-spin"></i> Gemini sedang menganalisis data dashboard...</span></div>
                </div>
            `);
            body.scrollTop = body.scrollHeight;
        }

        try {
            await apiRequest('/api/ai/chat', {
                method: 'POST',
                body: {
                    question,
                    channel,
                    persist_to_chat: true
                }
            });
            await syncChatMessages(true);
        } catch (error) {
            showPremiumNotice(
                error.payload?.code === 'GEMINI_NOT_CONFIGURED' ? 'Gemini Belum Diaktifkan' : 'Gemini Belum Merespons',
                escapeHtml(error.message),
                { variant: 'danger' }
            );
            if (error.payload?.code === 'GEMINI_NOT_CONFIGURED') {
                openGeminiSettingsModal();
            }
        } finally {
            document.getElementById(typingId)?.remove();
        }
    }

    if (chatToggleBtn && chatPanel && chatOverlay) {
        const toggleChat = () => {
            chatPanel.classList.toggle('active');
            chatOverlay.classList.toggle('active');
            const opened = chatPanel.classList.contains('active');
            document.body.classList.toggle('chat-panel-open', opened);
            if (opened) {
                document.getElementById('ai-panel')?.classList.remove('active');
            } else if (chatPanel.contains(document.activeElement)) {
                document.activeElement?.blur();
            }
            syncMobileVisualViewport();
            if (chatPanel.classList.contains('active')) {
                renderChatChannels();
                syncChatMessages(true);
            }
        };
        chatToggleBtn.addEventListener('click', toggleChat);
        closeChatBtn.addEventListener('click', toggleChat);
        chatOverlay.addEventListener('click', toggleChat);
    }

    const sendChatBtn = document.getElementById('send-chat-btn');
    const chatInputField = document.getElementById('chat-input-field');
    const chatChannelSelector = document.getElementById('chat-channel-selector');
    const chatAttachmentInput = document.getElementById('chat-attachment-input');
    const chatAttachmentButton = document.getElementById('chat-attachment-btn');
    const chatAttachmentPreview = document.getElementById('chat-attachment-preview');

    if (sendChatBtn) {
        sendChatBtn.addEventListener('click', sendChatMessage);
    }
    if (chatInputField) {
        chatInputField.addEventListener('focus', () => {
            setTimeout(syncMobileVisualViewport, 80);
            setTimeout(syncMobileVisualViewport, 280);
        });
        chatInputField.addEventListener('blur', () => setTimeout(syncMobileVisualViewport, 120));
        chatInputField.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendChatMessage();
            }
        });
    }
    if (chatChannelSelector) {
        chatChannelSelector.addEventListener('change', renderChatMessages);
    }

    function updateChatAttachmentPreview() {
        if (!chatAttachmentPreview || !chatAttachmentInput) return;
        const file = chatAttachmentInput.files?.[0];
        if (!file) {
            chatAttachmentPreview.style.display = 'none';
            chatAttachmentPreview.innerHTML = '';
            return;
        }

        chatAttachmentPreview.style.display = 'block';
        chatAttachmentPreview.innerHTML = `
            <i class="ph ph-paperclip"></i>
            ${escapeHtml(file.name)} Â· ${(file.size / 1024 / 1024).toFixed(2)} MB
            <button type="button" id="chat-remove-attachment" title="Hapus lampiran"><i class="ph ph-x"></i></button>
        `;
        document.getElementById('chat-remove-attachment')?.addEventListener('click', () => {
            chatAttachmentInput.value = '';
            updateChatAttachmentPreview();
        });
    }

    chatAttachmentButton?.addEventListener('click', () => chatAttachmentInput?.click());
    chatAttachmentInput?.addEventListener('change', updateChatAttachmentPreview);

    // ================= HRD Holiday Announcements =================
    const holidayAnnouncementButton = document.getElementById('chat-holiday-announcement-btn');
    const holidayAnnouncementModal = document.getElementById('holiday-announcement-modal');
    const holidayAnnouncementForm = document.getElementById('holiday-announcement-form');
    const holidayAnnouncementCancel = document.getElementById('holiday-announcement-cancel');

    function closeHolidayAnnouncementModal() {
        if (!holidayAnnouncementModal) return;
        holidayAnnouncementModal.style.display = 'none';
        const error = document.getElementById('holiday-announcement-error');
        if (error) error.style.display = 'none';
    }

    holidayAnnouncementButton?.addEventListener('click', () => {
        const permitted = currentUser && (currentUser.role === 'ceo' || currentUser.role.includes('hrd') || currentUser.role === 'hr');
        if (!permitted || !holidayAnnouncementModal) return;
        const startInput = document.getElementById('holiday-announcement-start');
        const endInput = document.getElementById('holiday-announcement-end');
        if (startInput && !startInput.value) startInput.value = todayJakarta();
        if (endInput && !endInput.value) endInput.value = startInput?.value || todayJakarta();
        holidayAnnouncementModal.style.display = 'flex';
    });
    holidayAnnouncementCancel?.addEventListener('click', closeHolidayAnnouncementModal);
    holidayAnnouncementModal?.addEventListener('click', event => {
        if (event.target === holidayAnnouncementModal) closeHolidayAnnouncementModal();
    });

    holidayAnnouncementForm?.addEventListener('submit', async event => {
        event.preventDefault();
        const error = document.getElementById('holiday-announcement-error');
        const submitButton = holidayAnnouncementForm.querySelector('button[type="submit"]');
        const body = {
            channel: 'general',
            type: 'holiday_announcement',
            holiday_title: document.getElementById('holiday-announcement-title')?.value.trim(),
            holiday_start_date: document.getElementById('holiday-announcement-start')?.value,
            holiday_end_date: document.getElementById('holiday-announcement-end')?.value,
            message: document.getElementById('holiday-announcement-message')?.value.trim()
        };
        if (error) error.style.display = 'none';
        if (submitButton) submitButton.disabled = true;
        try {
            await apiRequest('/api/chat-messages', { method: 'POST', body });
            holidayAnnouncementForm.reset();
            closeHolidayAnnouncementModal();
            const selector = document.getElementById('chat-channel-selector');
            if (selector) selector.value = 'general';
            await syncChatMessages(true);
            showPremiumNotice(
                'Pengumuman Telah Terbit',
                'Informasi hari libur sudah diterbitkan di kanal umum dan notifikasi telah dikirim kepada seluruh karyawan aktif.',
                { variant: 'success' }
            );
        } catch (requestError) {
            if (error) {
                error.textContent = requestError.message;
                error.style.display = 'block';
            }
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    });

    // ================= Role-scoped Data Backup =================
    const backupDataButton = document.getElementById('backup-data-btn');
    const backupDataModal = document.getElementById('backup-data-modal');
    const backupDataClose = document.getElementById('backup-data-close');
    const backupDataCopy = document.getElementById('backup-data-copy');
    const backupDataDownload = document.getElementById('backup-data-download');
    let cachedBackupPayload = null;

    function closeBackupModal() {
        if (backupDataModal) backupDataModal.style.display = 'none';
    }

    async function loadBackupPayload() {
        const summary = document.getElementById('backup-data-summary');
        const error = document.getElementById('backup-data-error');
        if (error) error.style.display = 'none';
        if (summary) summary.innerHTML = '<i class="ph ph-spinner ph-spin"></i><span>Menyiapkan data terbaru sesuai hak akses akun...</span>';
        try {
            cachedBackupPayload = await apiRequest('/api/backup');
            const dataGroups = Object.keys(cachedBackupPayload.data || {}).length;
            if (summary) {
                summary.innerHTML = `<i class="ph ph-shield-check"></i><span>${dataGroups} kelompok data berhasil disiapkan. Backup mengikuti cakupan <b>${escapeHtml(cachedBackupPayload.data?.backup_info?.scope || 'akun')}</b>.</span>`;
            }
            return cachedBackupPayload;
        } catch (requestError) {
            if (error) {
                error.textContent = requestError.message;
                error.style.display = 'block';
            }
            if (summary) summary.innerHTML = '<i class="ph ph-warning-circle"></i><span>Data belum dapat disiapkan.</span>';
            throw requestError;
        }
    }

    backupDataButton?.addEventListener('click', async () => {
        if (!backupDataModal) return;
        backupDataModal.style.display = 'flex';
        cachedBackupPayload = null;
        try {
            await loadBackupPayload();
        } catch (_) {
            // Error already displayed in the modal.
        }
    });
    backupDataClose?.addEventListener('click', closeBackupModal);
    backupDataModal?.addEventListener('click', event => {
        if (event.target === backupDataModal) closeBackupModal();
    });

    backupDataCopy?.addEventListener('click', async () => {
        try {
            const payload = cachedBackupPayload || await loadBackupPayload();
            const content = JSON.stringify(payload.data, null, 2);
            if (navigator.clipboard?.writeText) {
                await navigator.clipboard.writeText(content);
            } else {
                const textarea = document.createElement('textarea');
                textarea.value = content;
                textarea.style.position = 'fixed';
                textarea.style.opacity = '0';
                document.body.appendChild(textarea);
                textarea.select();
                document.execCommand('copy');
                textarea.remove();
            }
            showPremiumNotice('Data Berhasil Disalin', 'Seluruh data yang dapat diakses akun ini telah disalin ke clipboard.', { variant: 'success' });
        } catch (error) {
            showPremiumNotice('Gagal Menyalin Backup', escapeHtml(error.message), { variant: 'danger' });
        }
    });

    backupDataDownload?.addEventListener('click', async () => {
        try {
            if (!cachedBackupPayload) await loadBackupPayload();
            const response = await fetch('/api/backup?download=1', {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            });
            if (!response.ok) throw new Error('Server belum dapat membuat berkas backup.');
            const blob = await response.blob();
            const disposition = response.headers.get('content-disposition') || '';
            const filename = disposition.match(/filename="([^"]+)"/)?.[1] || cachedBackupPayload?.filename || 'suba-arch-backup.json';
            const url = URL.createObjectURL(blob);
            const anchor = document.createElement('a');
            anchor.href = url;
            anchor.download = filename;
            document.body.appendChild(anchor);
            anchor.click();
            anchor.remove();
            URL.revokeObjectURL(url);
            showPremiumNotice('Backup Berhasil Diunduh', `Berkas ${escapeHtml(filename)} telah dibuat dari data terbaru.`, { variant: 'success' });
        } catch (error) {
            showPremiumNotice('Gagal Mengunduh Backup', escapeHtml(error.message), { variant: 'danger' });
        }
    });

    // ================= Resignation Workflow =================
    const resignationForm = document.getElementById('resignation-form');

    function renderResignationHistory() {
        const list = document.getElementById('resignation-history-list');
        if (!list || !currentUser) return;
        const dateInput = document.getElementById('resignation-last-date');
        if (dateInput) dateInput.min = todayJakarta();

        const requests = state.resignationRequests || [];
        if (!requests.length) {
            list.innerHTML = `
                <div class="approval-empty-state">
                    <div class="approval-empty-icon"><i class="ph ph-file-dashed"></i></div>
                    <strong>Belum ada pengajuan resign</strong>
                    <p>Riwayat pengajuan dan keputusan terbaru akan tampil di bagian ini.</p>
                </div>
            `;
            return;
        }

        const statusLabels = {
            pending_manager: 'Menunggu Manager',
            pending_ceo: 'Menunggu CEO',
            approved: 'Disetujui',
            rejected: 'Ditolak',
            cancelled: 'Dibatalkan'
        };
        list.innerHTML = requests.map(item => {
            const statusClass = item.status === 'approved' ? 'success' : (item.status === 'rejected' ? 'danger' : '');
            return `
                <article class="resignation-history-item">
                    <header>
                        <strong>Hari terakhir: ${escapeHtml(item.last_working_date)}</strong>
                        <span class="approval-status-pill ${statusClass}">${escapeHtml(statusLabels[item.status] || item.status)}</span>
                    </header>
                    <p><b>Alasan:</b> ${escapeHtml(item.reason)}</p>
                    ${item.handover_notes ? `<p><b>Serah terima:</b> ${escapeHtml(item.handover_notes)}</p>` : ''}
                    <div class="serp-item-actions">
                        ${item.can_edit ? `<button type="button" class="erp-edit-btn" data-edit-resignation="${item.id}"><i class="ph ph-pencil-simple"></i> Edit</button>` : ''}
                        <button type="button" class="erp-delete-btn" data-erp-delete
                            data-resource-type="resignation_request"
                            data-resource-id="${item.id}"
                            data-resource-label="Pengajuan resign tanggal ${escapeHtml(item.last_working_date)}">
                            <i class="ph ph-trash"></i> Hapus / Ajukan
                        </button>
                    </div>
                </article>
            `;
        }).join('');
        list.querySelectorAll('[data-edit-resignation]').forEach(button => {
            button.onclick = () => {
                const item = requests.find(row => Number(row.id) === Number(button.dataset.editResignation));
                if (!item || !resignationForm) return;
                document.getElementById('resignation-last-date').value = item.last_working_date || '';
                document.getElementById('resignation-reason').value = item.reason || '';
                document.getElementById('resignation-handover').value = item.handover_notes || '';
                resignationForm.dataset.editResignationId = String(item.id);
                resignationForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
            };
        });
    }

    resignationForm?.addEventListener('submit', async event => {
        event.preventDefault();
        const error = document.getElementById('resignation-form-error');
        const submitButton = resignationForm.querySelector('button[type="submit"]');
        if (error) error.style.display = 'none';
        if (submitButton) submitButton.disabled = true;
        try {
            const editingId = Number(resignationForm.dataset.editResignationId || 0);
            const result = await apiRequest(editingId ? `/api/resignation-requests/${editingId}` : '/api/resignation-requests', {
                method: editingId ? 'PUT' : 'POST',
                body: {
                    last_working_date: document.getElementById('resignation-last-date')?.value,
                    reason: document.getElementById('resignation-reason')?.value.trim(),
                    handover_notes: document.getElementById('resignation-handover')?.value.trim()
                }
            });
            resignationForm.reset();
            delete resignationForm.dataset.editResignationId;
            await syncDataFromServer();
            renderResignationHistory();
            showPremiumNotice('Pengajuan Berhasil Dikirim', escapeHtml(result.message), { variant: 'success' });
        } catch (requestError) {
            if (error) {
                error.textContent = requestError.message;
                error.style.display = 'block';
            }
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    });

    async function syncAlumniPortal() {
        if (!currentUser) return;
        const selfWorkspace = document.getElementById('alumni-profile-form');
        const adminWorkspace = document.getElementById('alumni-admin-workspace');
        const isAlumni = currentUser.role === 'alumni';
        const isAdmin = currentUser.role === 'ceo' || ['mgr_hrd', 'staff_hrd'].includes(currentUser.role);
        const isManager = currentUser.role?.startsWith('mgr_');
        const invitationForm = document.getElementById('alumni-invitation-form');
        const announcementForm = document.getElementById('alumni-announcement-form');
        const workspaceEyebrow = document.getElementById('alumni-workspace-eyebrow');
        const workspaceTitle = document.getElementById('alumni-workspace-title');

        if (selfWorkspace) selfWorkspace.style.display = isAlumni ? 'flex' : 'none';
        if (adminWorkspace) adminWorkspace.style.display = (isAdmin || isManager) ? 'block' : 'none';
        if (invitationForm) invitationForm.style.display = (isAdmin || isManager) ? 'flex' : 'none';
        if (announcementForm) announcementForm.style.display = (isAdmin || isManager) ? 'flex' : 'none';
        if (workspaceEyebrow) workspaceEyebrow.textContent = isManager ? 'Manager Divisi' : 'CEO / HRD';
        if (workspaceTitle) workspaceTitle.textContent = isManager ? 'Alumni Tim & Komunikasi Divisi' : 'Direktori & Undangan Alumni';

        try {
            if (isAlumni) {
                const profile = await apiRequest('/api/alumni/profile');
                state.alumniProfile = profile;
                const values = {
                    'alumni-current-employer': profile.current_employer,
                    'alumni-current-position': profile.current_position,
                    'alumni-industry': profile.industry,
                    'alumni-city': profile.city,
                    'alumni-linkedin-url': profile.linkedin_url,
                    'alumni-portfolio-url': profile.portfolio_url,
                    'alumni-bio': profile.bio,
                    'alumni-skills': (profile.skills || []).join(', ')
                };
                Object.entries(values).forEach(([id, value]) => {
                    const input = document.getElementById(id);
                    if (input) input.value = value || '';
                });
                const available = document.getElementById('alumni-available');
                const events = document.getElementById('alumni-events-opt-in');
                if (available) available.checked = Boolean(profile.available_for_opportunities);
                if (events) events.checked = Boolean(profile.receive_event_invitations);
                const updated = document.getElementById('alumni-profile-updated');
                if (updated) {
                    updated.innerHTML = profile.last_profile_update_at
                        ? `<i class="ph ph-check-circle"></i> Diperbarui ${new Date(profile.last_profile_update_at).toLocaleDateString('id-ID')}`
                        : '<i class="ph ph-lock-key"></i> Riwayat kerja aman';
                }
            }

            const payload = await apiRequest('/api/alumni');
            state.alumniDirectory = payload.alumni || [];
            state.manageableAlumni = payload.manageable_alumni || [];
            state.alumniAnnouncements = payload.announcements || [];
            renderAlumniPublicDirectory();
            renderAlumniAnnouncements();
            if (isAdmin || isManager) {
                renderAlumniDirectory(isAdmin ? state.alumniDirectory : state.manageableAlumni, isAdmin ? 'alumni' : 'alumni tim Anda');
            }
        } catch (error) {
            if (error.status !== 403 && error.status !== 401) {
                showPremiumNotice('Portal Alumni Tidak Tersedia', escapeHtml(error.message), { variant: 'danger' });
            }
        }
    }

    function renderAlumniDirectory(source = state.alumniDirectory, label = 'alumni') {
        const list = document.getElementById('alumni-directory-list');
        const count = document.getElementById('alumni-directory-count');
        if (!list) return;
        const alumni = source || [];
        if (count) count.textContent = `${alumni.length} ${label}`;
        if (!alumni.length) {
            list.innerHTML = '<div class="approval-empty-state"><strong>Belum ada akun alumni</strong><p>Anggota yang selesai dapat dialihkan menjadi alumni melalui dialog penonaktifan akun.</p></div>';
            return;
        }
        list.innerHTML = alumni.map(person => {
            const selected = selectedAlumniRecipientIds.has(Number(person.id));
            return `
                <button type="button" class="alumni-person-card ${selected ? 'selected' : ''}" data-alumni-id="${person.id}">
                    <span class="alumni-person-avatar">${escapeHtml((person.name || 'SA').split(/\s+/).slice(0, 2).map(part => part[0]).join('').toUpperCase())}</span>
                    <span><strong>${escapeHtml(person.name)}</strong><small>${escapeHtml(person.current_position || 'Profil karier belum dilengkapi')} ${person.current_employer ? `Ã‚Â· ${escapeHtml(person.current_employer)}` : ''}</small></span>
                    <i class="ph ${selected ? 'ph-check-circle' : 'ph-circle'}"></i>
                </button>`;
        }).join('');
        list.querySelectorAll('[data-alumni-id]').forEach(button => {
            button.onclick = () => {
                const id = Number(button.dataset.alumniId);
                if (selectedAlumniRecipientIds.has(id)) selectedAlumniRecipientIds.delete(id);
                else selectedAlumniRecipientIds.add(id);
                renderAlumniDirectory();
            };
        });
    }

    function renderAlumniPublicDirectory() {
        const list = document.getElementById('alumni-public-directory');
        const count = document.getElementById('alumni-public-count');
        if (!list) return;
        const people = state.alumniDirectory || [];
        if (count) count.textContent = `${people.length} alumni`;
        list.innerHTML = people.length ? people.map(person => `
            <div class="alumni-person-card" style="cursor:default;">
                <span class="alumni-person-avatar">${escapeHtml((person.name || 'SA').split(/\s+/).slice(0, 2).map(part => part[0]).join('').toUpperCase())}</span>
                <span><strong>${escapeHtml(person.name)}</strong><small>${escapeHtml(person.current_position || person.former_role || 'Alumni Suba-Arch')}${person.current_employer ? ` Â· ${escapeHtml(person.current_employer)}` : ''}</small></span>
            </div>`).join('') : '<div class="approval-empty-state"><strong>Belum ada alumni</strong><p>Daftar alumni akan muncul setelah anggota menyelesaikan masa kerja atau magang.</p></div>';
    }

    function renderAlumniAnnouncements() {
        const list = document.getElementById('alumni-announcement-list');
        if (!list) return;
        const rows = state.alumniAnnouncements || [];
        list.innerHTML = rows.length ? rows.map(item => `<div class="alumni-person-card" style="cursor:default;align-items:flex-start;"><i class="ph ph-megaphone" style="color:var(--primary);font-size:20px;"></i><span><strong>${escapeHtml(item.title)}</strong><small>${escapeHtml(item.message || '')}<br>${escapeHtml(item.author || 'Suba-Arch')} Â· ${item.date ? new Date(item.date).toLocaleDateString('id-ID') : ''}</small></span></div>`).join('') : '<div class="approval-empty-state"><strong>Belum ada pengumuman</strong><p>Pengumuman CEO atau HRD akan muncul di sini.</p></div>';
    }

    document.getElementById('alumni-profile-form')?.addEventListener('submit', async event => {
        event.preventDefault();
        const button = event.currentTarget.querySelector('button[type="submit"]');
        const original = button?.innerHTML;
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Menyimpan...';
        }
        try {
            const result = await apiRequest('/api/alumni/profile', {
                method: 'PUT',
                body: {
                    current_employer: document.getElementById('alumni-current-employer')?.value.trim() || null,
                    current_position: document.getElementById('alumni-current-position')?.value.trim() || null,
                    industry: document.getElementById('alumni-industry')?.value.trim() || null,
                    city: document.getElementById('alumni-city')?.value.trim() || null,
                    linkedin_url: document.getElementById('alumni-linkedin-url')?.value.trim() || null,
                    portfolio_url: document.getElementById('alumni-portfolio-url')?.value.trim() || null,
                    bio: document.getElementById('alumni-bio')?.value.trim() || null,
                    skills: (document.getElementById('alumni-skills')?.value || '').split(',').map(item => item.trim()).filter(Boolean),
                    available_for_opportunities: Boolean(document.getElementById('alumni-available')?.checked),
                    receive_event_invitations: Boolean(document.getElementById('alumni-events-opt-in')?.checked)
                }
            });
            showPremiumNotice('Profil Alumni Tersimpan', escapeHtml(result.message), { variant: 'success' });
            await syncAlumniPortal();
        } catch (error) {
            showPremiumNotice('Profil Tidak Dapat Disimpan', escapeHtml(error.message), { variant: 'danger' });
        } finally {
            if (button) {
                button.disabled = false;
                button.innerHTML = original;
            }
        }
    });

    document.getElementById('alumni-invitation-form')?.addEventListener('submit', async event => {
        event.preventDefault();
        const button = event.currentTarget.querySelector('button[type="submit"]');
        const original = button?.innerHTML;
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Mengirim email...';
        }
        try {
            const body = {
                title: document.getElementById('alumni-event-title')?.value.trim(),
                message: document.getElementById('alumni-event-message')?.value.trim(),
                event_at: document.getElementById('alumni-event-at')?.value,
                location: document.getElementById('alumni-event-location')?.value.trim() || null,
                registration_url: document.getElementById('alumni-registration-url')?.value.trim() || null
            };
            if (selectedAlumniRecipientIds.size) body.recipient_ids = Array.from(selectedAlumniRecipientIds);
            const result = await apiRequest('/api/alumni/invitations', { method: 'POST', body });
            showPremiumNotice('Undangan Alumni Diproses', escapeHtml(result.message), { variant: 'success' });
            event.currentTarget.reset();
            selectedAlumniRecipientIds.clear();
            await syncAlumniPortal();
        } catch (error) {
            showPremiumNotice('Undangan Tidak Dapat Dikirim', escapeHtml(error.message), { variant: 'danger' });
        } finally {
            if (button) {
                button.disabled = false;
                button.innerHTML = original;
            }
        }
    });

    document.getElementById('alumni-announcement-form')?.addEventListener('submit', async event => {
        event.preventDefault();
        const button = event.currentTarget.querySelector('button[type="submit"]');
        const original = button?.innerHTML;
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Menerbitkan...';
        }
        try {
            const result = await apiRequest('/api/alumni/announcements', {
                method: 'POST',
                body: {
                    title: document.getElementById('alumni-announcement-title')?.value.trim(),
                    message: document.getElementById('alumni-announcement-message')?.value.trim()
                }
            });
            showPremiumNotice('Pengumuman Diterbitkan', escapeHtml(result.message), { variant: 'success' });
            event.currentTarget.reset();
            await syncAlumniPortal();
        } catch (error) {
            showPremiumNotice('Pengumuman Tidak Dapat Diterbitkan', escapeHtml(error.message), { variant: 'danger' });
        } finally {
            if (button) {
                button.disabled = false;
                button.innerHTML = original;
            }
        }
    });

    function renderNotifications() {
        const container = document.getElementById('notifications-list-container');
        const bellBtn = document.getElementById('notif-bell-btn');
        if (!container || !bellBtn || !currentUser) return;
        
        const badgeCount = bellBtn.querySelector('.badge-count');
        const userNotifs = state.serverNotifications || [];
        const unreadCount = Number(state.serverUnreadNotifications ?? userNotifs.filter(n => !n.read).length);
        if (badgeCount) {
            badgeCount.style.display = unreadCount > 0 ? 'block' : 'none';
            badgeCount.title = unreadCount > 0 ? `${unreadCount} notifikasi belum dibaca` : '';
        }
        
        container.innerHTML = '';
        if (userNotifs.length === 0) {
            container.innerHTML = '<div style="color: var(--text-muted); font-size: 11px; padding: 20px; text-align: center; font-style: italic;">Tidak ada notifikasi saat ini.</div>';
            return;
        }
        
        userNotifs.forEach(n => {
            const timestamp = n.created_at
                ? new Date(n.created_at).toLocaleString('id-ID', { timeZone: JAKARTA_TIMEZONE, dateStyle: 'short', timeStyle: 'short' })
                : '';
            container.innerHTML += `
                <div class="notif-item server-notification ${n.read ? 'is-read' : 'is-unread'}" data-id="${n.id}" data-action="${escapeHtml(n.action_url || '')}" style="padding: 12px; border-bottom: 1px solid #30343d; display: flex; gap: 12px; align-items: start; cursor: pointer;">
                    <div class="notif-icon primary" style="background: rgba(242,201,76,0.15); color: var(--primary); padding: 8px; border-radius: var(--radius-sm);"><i class="ph ph-shield-star"></i></div>
                    <div class="notif-content" style="flex: 1;">
                        <h5 style="margin: 0 0 4px 0; font-size: 13px; color: white;">${escapeHtml(n.title)}</h5>
                        <p style="margin: 0; font-size: 11px; color: var(--text-secondary); line-height: 1.4;">${escapeHtml(n.message)}</p>
                        <span style="font-size: 8px; color: var(--text-muted); display: block; margin-top: 4px;">${escapeHtml(timestamp)} WIB</span>
                    </div>
                </div>
            `;
        });

        container.querySelectorAll('.server-notification').forEach(item => {
            item.onclick = async () => {
                const id = item.dataset.id;
                const action = item.dataset.action;
                const notification = userNotifs.find(entry => entry.id === id);
                if (notification && !notification.read) {
                    try {
                        await apiRequest(`/api/notifications/${id}/read`, { method: 'POST' });
                        notification.read = true;
                        state.serverUnreadNotifications = Math.max(0, unreadCount - 1);
                        renderNotifications();
                    } catch (error) {
                        console.error(error);
                    }
                }

                if (action?.startsWith('/#')) {
                    switchView(action.slice(2));
                    notifDropdown?.classList.remove('active');
                    notifBellBtn?.setAttribute('aria-expanded', 'false');
                }
            };
        });
    }

    const notifBellBtn = document.getElementById('notif-bell-btn');
    const notifDropdown = document.getElementById('notif-dropdown');
    if (notifBellBtn && notifDropdown) {
        const setNotificationPanelOpen = (open) => {
            notifDropdown.classList.toggle('active', open);
            notifBellBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        };

        notifBellBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            setNotificationPanelOpen(!notifDropdown.classList.contains('active'));
            renderNotifications();
        });
        document.addEventListener('click', (e) => {
            if (!notifDropdown.contains(e.target) && e.target !== notifBellBtn) {
                setNotificationPanelOpen(false);
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                setNotificationPanelOpen(false);
            }
        });
        
        const markAllReadBtn = document.getElementById('mark-all-read-btn');
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', async (e) => {
                e.preventDefault();
                e.stopPropagation();
                try {
                    await apiRequest('/api/notifications/read-all', { method: 'POST' });
                    (state.serverNotifications || []).forEach(notification => {
                        notification.read = true;
                    });
                    state.serverUnreadNotifications = 0;
                    renderNotifications();
                } catch (error) {
                    console.error(error);
                }
            });
        }
    }

    const aiFloatingBtn = document.getElementById('ai-floating-btn');
    const aiPanel = document.getElementById('ai-panel');
    const closeAiBtn = document.getElementById('close-ai-btn');
    if (aiFloatingBtn && aiPanel) {
        aiFloatingBtn.addEventListener('click', () => {
            aiPanel.classList.add('active');
            checkGeminiStatus();
        });
        closeAiBtn.addEventListener('click', () => aiPanel.classList.remove('active'));
    }

    // ================= Gemini-powered AI Copilot =================
    const aiInput = document.getElementById('ai-copilot-input');
    const aiChatBody = document.getElementById('ai-copilot-body');
    const aiSendButton = document.getElementById('ai-copilot-send');
    const aiStatus = document.getElementById('ai-copilot-status');
    const aiSettingsButton = document.getElementById('ai-copilot-settings-btn');
    const geminiSettingsModal = document.getElementById('gemini-settings-modal');
    const geminiSettingsForm = document.getElementById('gemini-settings-form');
    const geminiSettingsCancel = document.getElementById('gemini-settings-cancel');
    const geminiSettingsRemove = document.getElementById('gemini-settings-remove');
    const geminiSettingsSave = document.getElementById('gemini-settings-save');
    const geminiApiKeyInput = document.getElementById('gemini-settings-api-key');
    const geminiModelSelect = document.getElementById('gemini-settings-model');
    const geminiSettingsError = document.getElementById('gemini-settings-error');
    const geminiSettingsCurrent = document.getElementById('gemini-settings-current');
    const geminiKeyVisibility = document.getElementById('gemini-key-visibility');

    async function checkGeminiStatus() {
        if (!aiStatus || !aiInput || !aiSendButton || !currentUser) return;
        aiStatus.className = 'ai-copilot-status checking';
        aiStatus.innerHTML = '<span></span> Memeriksa koneksi Gemini...';
        try {
            const status = await apiRequest('/api/ai/status');
            geminiStatusCache = status;
            const connected = Boolean(status.configured);
            aiStatus.className = `ai-copilot-status ${connected ? 'connected' : 'disconnected'}`;
            aiStatus.innerHTML = `<span></span> ${connected ? `Terhubung Â· ${escapeHtml(status.model)}` : 'API key belum dikonfigurasi'}`;
            aiInput.disabled = !connected;
            aiSendButton.disabled = !connected;
            aiInput.placeholder = connected
                ? 'Tanya Gemini tentang dashboard...'
                : 'Buka ikon kunci untuk menghubungkan Gemini pribadi';
        } catch (error) {
            geminiStatusCache = null;
            aiStatus.className = 'ai-copilot-status disconnected';
            aiStatus.innerHTML = '<span></span> Status Gemini tidak tersedia';
            aiInput.disabled = true;
            aiSendButton.disabled = true;
        }
    }

    function openGeminiSettingsModal() {
        if (!geminiSettingsModal || !geminiApiKeyInput || !geminiModelSelect) return;
        geminiApiKeyInput.value = '';
        geminiApiKeyInput.type = 'password';
        geminiModelSelect.value = geminiStatusCache?.model || 'gemini-2.5-flash';
        if (geminiSettingsError) geminiSettingsError.style.display = 'none';

        const configured = Boolean(geminiStatusCache?.configured);
        if (geminiSettingsRemove) geminiSettingsRemove.style.display = configured ? 'inline-flex' : 'none';
        if (geminiSettingsCurrent) {
            geminiSettingsCurrent.style.display = configured ? 'block' : 'none';
            geminiSettingsCurrent.innerHTML = configured
                ? `<i class="ph ph-check-circle"></i> Terhubung dengan <b>${escapeHtml(geminiStatusCache.model)}</b>. Masukkan key baru hanya jika ingin mengganti koneksi.`
                : '';
        }
        geminiSettingsModal.style.display = 'flex';
        setTimeout(() => geminiApiKeyInput.focus(), 80);
    }

    function closeGeminiSettingsModal() {
        if (!geminiSettingsModal || !geminiApiKeyInput) return;
        geminiApiKeyInput.value = '';
        geminiSettingsModal.style.display = 'none';
        if (geminiSettingsError) geminiSettingsError.style.display = 'none';
    }

    aiSettingsButton?.addEventListener('click', openGeminiSettingsModal);
    geminiSettingsCancel?.addEventListener('click', closeGeminiSettingsModal);
    geminiSettingsModal?.addEventListener('click', event => {
        if (event.target === geminiSettingsModal) closeGeminiSettingsModal();
    });

    geminiKeyVisibility?.addEventListener('click', () => {
        if (!geminiApiKeyInput) return;
        const showing = geminiApiKeyInput.type === 'text';
        geminiApiKeyInput.type = showing ? 'password' : 'text';
        geminiKeyVisibility.innerHTML = `<i class="ph ph-${showing ? 'eye' : 'eye-slash'}"></i>`;
    });

    geminiSettingsForm?.addEventListener('submit', async event => {
        event.preventDefault();
        const apiKey = geminiApiKeyInput?.value.trim();
        const model = geminiModelSelect?.value;
        if (!apiKey || !model) return;

        if (geminiSettingsError) geminiSettingsError.style.display = 'none';
        if (geminiSettingsSave) {
            geminiSettingsSave.disabled = true;
            geminiSettingsSave.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Menguji koneksi...';
        }

        try {
            const result = await apiRequest('/api/ai/settings', {
                method: 'POST',
                body: { api_key: apiKey, model }
            });
            closeGeminiSettingsModal();
            geminiConversation = [];
            await checkGeminiStatus();
            showPremiumNotice('Gemini Pribadi Terhubung', escapeHtml(result.message), { variant: 'success' });
        } catch (error) {
            if (geminiSettingsError) {
                geminiSettingsError.textContent = error.message;
                geminiSettingsError.style.display = 'block';
            }
        } finally {
            if (geminiSettingsSave) {
                geminiSettingsSave.disabled = false;
                geminiSettingsSave.innerHTML = '<i class="ph ph-plugs-connected"></i> Uji & Simpan';
            }
        }
    });

    geminiSettingsRemove?.addEventListener('click', () => {
        showCustomConfirm(
            'Hapus Koneksi Gemini?',
            'API key pribadi akan dihapus dari akun ini. Akun lain tidak terpengaruh.',
            async () => {
                try {
                    const result = await apiRequest('/api/ai/settings', { method: 'DELETE' });
                    closeGeminiSettingsModal();
                    geminiConversation = [];
                    await checkGeminiStatus();
                    showPremiumNotice('Koneksi Gemini Dihapus', escapeHtml(result.message), { variant: 'success' });
                } catch (error) {
                    showPremiumNotice('Gagal Menghapus Koneksi', escapeHtml(error.message), { variant: 'danger' });
                }
            },
            { confirmText: 'Ya, Hapus', cancelText: 'Batal', variant: 'danger' }
        );
    });

    function appendCopilotBubble(kind, text, temporaryId = '') {
        if (!aiChatBody) return null;
        const bubble = document.createElement('div');
        bubble.className = `chat-bubble ${kind === 'user' ? 'me' : 'other ai-response'}`;
        if (temporaryId) bubble.id = temporaryId;
        const content = document.createElement('div');
        content.className = 'chat-text ai-copilot-answer';
        if (kind === 'typing') {
            content.innerHTML = '<span class="ai-copilot-typing"><i class="ph ph-spinner ph-spin"></i> Gemini sedang menganalisis data terbaru...</span>';
        } else {
            content.textContent = text;
        }
        bubble.appendChild(content);
        aiChatBody.appendChild(bubble);
        aiChatBody.scrollTop = aiChatBody.scrollHeight;
        return bubble;
    }

    async function sendGeminiCopilotQuestion() {
        if (!aiInput || !aiSendButton || !aiChatBody) return;
        const question = aiInput.value.trim();
        if (!question) return;

        aiInput.value = '';
        appendCopilotBubble('user', question);
        const typingId = `gemini-panel-typing-${Date.now()}`;
        appendCopilotBubble('typing', '', typingId);
        aiInput.disabled = true;
        aiSendButton.disabled = true;

        try {
            const response = await apiRequest('/api/ai/chat', {
                method: 'POST',
                body: {
                    question,
                    conversation: geminiConversation.slice(-8)
                }
            });
            document.getElementById(typingId)?.remove();
            appendCopilotBubble('assistant', response.answer);
            geminiConversation.push(
                { role: 'user', text: question },
                { role: 'assistant', text: response.answer }
            );
            geminiConversation = geminiConversation.slice(-8);
        } catch (error) {
            document.getElementById(typingId)?.remove();
            appendCopilotBubble('assistant', error.message);
            if (error.payload?.code === 'GEMINI_NOT_CONFIGURED') {
                await checkGeminiStatus();
                openGeminiSettingsModal();
            }
        } finally {
            if (!aiInput.disabled || aiStatus?.classList.contains('connected')) {
                aiInput.disabled = false;
                aiSendButton.disabled = false;
                aiInput.focus();
            }
        }
    }

    aiSendButton?.addEventListener('click', sendGeminiCopilotQuestion);
    aiInput?.addEventListener('keydown', event => {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendGeminiCopilotQuestion();
        }
    });

    // ================= Offline Spreadsheet (CSV) Downloader =================
    const exportBtns = document.querySelectorAll('.export-btn.sheets');
    exportBtns.forEach(btn => {
        btn.onclick = () => {
            const currentView = document.querySelector('.view-section:not([style*="display: none"])');
            const viewId = currentView ? currentView.id : 'unknown';
            
            let csvContent = "data:text/csv;charset=utf-8,";
            let filename = "suba-arch-report.csv";

            if (viewId === 'view-attendance' || viewId === 'view-hrd') {
                filename = "suba-arch-attendance-report.csv";
                csvContent += "Nama Karyawan,Tanggal,Waktu (In),Waktu (Out),Tipe,Status,Lat/Lng\n";
                state.attendance.forEach(a => {
                    const u = state.users[a.username] || { name: a.username };
                    csvContent += `"${u.name}","${a.date}","${a.time}","${a.timeOut || '-'}","${a.type}","${a.status}","${a.lat || ''}, ${a.lng || ''}"\n`;
                });
            } else if (viewId === 'view-dashboard' || viewId === 'view-ceo') {
                filename = "suba-arch-leads-report.csv";
                csvContent += "Nama Klien,Est/Deal Budget,Source,Tipe Proyek,Tahapan,Tanggal Masuk\n";
                state.leads.forEach(l => {
                    csvContent += `"${l.name}","${l.budget}","${l.source}","${l.type}","${l.column}","${l.date}"\n`;
                });
            } else {
                filename = "suba-arch-employee-list.csv";
                csvContent += "Nama Karyawan,Username,Jabatan/Title,Level,Parent\n";
                Object.values(state.users).forEach(u => {
                    csvContent += `"${u.name}","${u.username}","${u.title}","${u.level}","${u.parent || ''}"\n`;
                });
            }

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", filename);
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            const originalHtml = btn.innerHTML;
            btn.innerHTML = `<i class="ph ph-check-circle"></i> CSV Downloaded`;
            setTimeout(() => {
                btn.innerHTML = originalHtml;
            }, 2000);
        };
    });

    // ================= Dynamic User Profile & Avatar Helper =================
    function updateUserProfileUI(user) {
        const profileAvatar = document.getElementById('sidebar-user-avatar');
        const profileName = document.getElementById('sidebar-user-name');
        const profileRole = document.getElementById('sidebar-user-role');
        const userDetails = state.users[user.username] || user;
        
        if (profileAvatar) {
            profileAvatar.innerHTML = userDetails.avatarImg 
                ? `<img src="${userDetails.avatarImg}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">` 
                : (userDetails.avatar || 'US');
        }
        if (profileName) profileName.innerText = userDetails.name || user.username;
        if (profileRole) profileRole.innerText = userDetails.title || user.role;
    }

    // ================= Profile Edit Modal Listeners & Handlers =================
    let uploadedAvatarBase64 = '';

    const sidebarProfileTrigger = document.getElementById('sidebar-profile-trigger');
    const profileEditModal = document.getElementById('profile-edit-modal');
    const profileModalPreview = document.getElementById('profile-modal-preview');
    const profilePhotoInput = document.getElementById('profile-photo-input');
    const photoSizeInfo = document.getElementById('photo-size-info');
    const profileEditForm = document.getElementById('profile-edit-form');
    
    if (sidebarProfileTrigger && profileEditModal) {
        sidebarProfileTrigger.addEventListener('click', (e) => {
            e.preventDefault();
            if (!currentUser) return;

            const userDetails = state.users[currentUser.username] || currentUser;
            const nameInput = document.getElementById('profile-name-input');
            const bioInput = document.getElementById('profile-bio-input');
            
            if (nameInput) nameInput.value = userDetails.name || '';
            if (bioInput) bioInput.value = userDetails.bio || '';
            
            uploadedAvatarBase64 = userDetails.avatarImg || '';
            
            if (profileModalPreview) {
                profileModalPreview.innerHTML = userDetails.avatarImg 
                    ? `<img src="${userDetails.avatarImg}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">` 
                    : (userDetails.avatar || 'US');
            }
            
            if (photoSizeInfo) {
                photoSizeInfo.innerText = "Maks. 300KB (akan dikompres otomatis jika lebih)";
                photoSizeInfo.style.color = 'var(--text-muted)';
            }
            
            profileEditModal.style.display = 'flex';
        });
    }

    if (profilePhotoInput) {
        profilePhotoInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;

            const fileSizeKB = file.size / 1024;
            if (fileSizeKB <= 300) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    uploadedAvatarBase64 = event.target.result;
                    if (profileModalPreview) {
                        profileModalPreview.innerHTML = `<img src="${uploadedAvatarBase64}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
                    }
                    if (photoSizeInfo) {
                        photoSizeInfo.innerText = `Ukuran foto: ${Math.round(fileSizeKB)}KB (OK)`;
                        photoSizeInfo.style.color = 'var(--success)';
                    }
                };
                reader.readAsDataURL(file);
            } else {
                if (photoSizeInfo) {
                    photoSizeInfo.innerText = `Mengompres foto (${Math.round(fileSizeKB)}KB)...`;
                    photoSizeInfo.style.color = 'var(--warning)';
                }
                compressImage(file, 300, (compressedBase64) => {
                    uploadedAvatarBase64 = compressedBase64;
                    const compressedSizeKB = Math.round((compressedBase64.length * 3) / 4 / 1024);
                    if (profileModalPreview) {
                        profileModalPreview.innerHTML = `<img src="${uploadedAvatarBase64}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">`;
                    }
                    if (photoSizeInfo) {
                        photoSizeInfo.innerText = `Berhasil dikompres menjadi ${compressedSizeKB}KB (Maks 300KB)`;
                        photoSizeInfo.style.color = 'var(--success)';
                    }
                });
            }
        });
    }

    function compressImage(file, maxSizeKB, callback) {
        const reader = new FileReader();
        reader.readAsDataURL(file);
        reader.onload = function (event) {
            const img = new Image();
            img.src = event.target.result;
            img.onload = function () {
                const canvas = document.createElement('canvas');
                let width = img.width;
                let height = img.height;
                
                const maxDim = 600;
                if (width > maxDim || height > maxDim) {
                    if (width > height) {
                        height = Math.round((height * maxDim) / width);
                        width = maxDim;
                    } else {
                        width = Math.round((width * maxDim) / height);
                        height = maxDim;
                    }
                }
                
                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                let quality = 0.85;
                let dataUrl = canvas.toDataURL('image/jpeg', quality);
                
                while (Math.round((dataUrl.length * 3) / 4 / 1024) > maxSizeKB && quality > 0.1) {
                    quality -= 0.05;
                    dataUrl = canvas.toDataURL('image/jpeg', quality);
                }
                
                callback(dataUrl);
            };
        };
    }

    if (profileEditForm) {
        profileEditForm.addEventListener('submit', (e) => {
            e.preventDefault();
            if (!currentUser) return;

            const nameInput = document.getElementById('profile-name-input');
            const bioInput = document.getElementById('profile-bio-input');
            
            const name = nameInput ? nameInput.value.trim() : '';
            const bio = bioInput ? bioInput.value.trim() : '';

            const username = currentUser.username;
            if (state.users[username]) {
                state.users[username].name = name || state.users[username].name;
                state.users[username].bio = bio;
                if (uploadedAvatarBase64) {
                    state.users[username].avatarImg = uploadedAvatarBase64;
                }
                
                currentUser.name = state.users[username].name;
                currentUser.avatarImg = state.users[username].avatarImg || '';
                currentUser.bio = state.users[username].bio || '';
                
                const savedSession = localStorage.getItem('currentUserSession');
                if (savedSession) {
                    try {
                        const session = JSON.parse(savedSession);
                        session.user = currentUser;
                        localStorage.setItem('currentUserSession', JSON.stringify(session));
                    } catch (e) {
                        console.error(e);
                    }
                }
                
                updateState(state);
                
                showPremiumNotice('Profil Diperbarui', 'Perubahan profil Anda berhasil disimpan.', { variant: 'success' });
                if (profileEditModal) profileEditModal.style.display = 'none';
            }
        });
    }

    const closeProfileModalBtn = document.getElementById('close-profile-modal-btn');
    if (closeProfileModalBtn) {
        closeProfileModalBtn.addEventListener('click', () => {
            if (profileEditModal) profileEditModal.style.display = 'none';
        });
    }

    const btnCancelProfile = document.getElementById('btn-cancel-profile');
    if (btnCancelProfile) {
        btnCancelProfile.addEventListener('click', () => {
            if (profileEditModal) profileEditModal.style.display = 'none';
        });
    }

    // ================= Leave Request Modal Handlers =================
    const btnRequestLeave = document.getElementById('btn-request-leave');
    const leaveModal = document.getElementById('leave-request-modal');
    const closeLeaveModalBtn = document.getElementById('close-leave-modal-btn');
    const btnCancelLeave = document.getElementById('btn-cancel-leave');
    const leaveForm = document.getElementById('leave-request-form');

    if (btnRequestLeave && leaveModal) {
        btnRequestLeave.addEventListener('click', () => {
            leaveModal.style.display = 'flex';
        });
    }

    const closeLeaveModal = () => {
        if (leaveModal) {
            leaveModal.style.display = 'none';
            if (leaveForm) leaveForm.reset();
            if (leaveForm) delete leaveForm.dataset.editLeaveId;
        }
    };

    if (closeLeaveModalBtn) closeLeaveModalBtn.addEventListener('click', closeLeaveModal);
    if (btnCancelLeave) btnCancelLeave.addEventListener('click', closeLeaveModal);

    if (leaveForm) {
        leaveForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!currentUser) return;

            const typeSelect = document.getElementById('leave-type-select');
            const startDateInput = document.getElementById('leave-start-date');
            const endDateInput = document.getElementById('leave-end-date');
            const reasonInput = document.getElementById('leave-reason');

            const type = typeSelect ? typeSelect.value : '';
            const startDate = startDateInput ? startDateInput.value : '';
            const endDate = endDateInput ? endDateInput.value : '';
            const reason = reasonInput ? reasonInput.value.trim() : '';

            if (!startDate || !endDate || !reason) {
                showPremiumNotice('Data Belum Lengkap', 'Harap lengkapi tanggal dan alasan pengajuan cuti.');
                return;
            }

            try {
                const editingId = Number(leaveForm.dataset.editLeaveId || 0);
                const result = await apiRequest(editingId ? `/api/leave-requests/${editingId}` : '/api/leave-requests', {
                    method: editingId ? 'PUT' : 'POST',
                    body: {
                        type,
                        start_date: startDate,
                        end_date: endDate,
                        reason
                    }
                });
                closeLeaveModal();
                showPremiumNotice('Pengajuan Cuti Terkirim', escapeHtml(result.message));
                await syncDataFromServer();
            } catch (error) {
                showPremiumNotice('Pengajuan Tidak Dapat Dikirim', escapeHtml(error.message));
            }
        });
    }

    // ================= Kanban Detail Modal Dismiss Handlers =================
    const closeLeadDetailBtn = document.getElementById('close-lead-detail-btn');
    const btnCloseLeadDetail = document.getElementById('btn-close-lead-detail');
    const leadDetailModal = document.getElementById('lead-detail-modal');
    if (closeLeadDetailBtn) {
        closeLeadDetailBtn.addEventListener('click', () => {
            if (leadDetailModal) leadDetailModal.style.display = 'none';
        });
    }
    if (btnCloseLeadDetail) {
        btnCloseLeadDetail.addEventListener('click', () => {
            if (leadDetailModal) leadDetailModal.style.display = 'none';
        });
    }

    // ================= User & Org Management Handlers =================
    const btnAddStaff = document.getElementById('btn-add-staff');
    const userRegModal = document.getElementById('user-registration-modal');
    const btnCancelReg = document.getElementById('btn-cancel-reg');
    const regForm = document.getElementById('user-registration-form');

    async function refreshEmployeeIdentityPreview() {
        const usernameInput = document.getElementById('reg-username');
        const employeeCodeInput = document.getElementById('reg-employee-code');
        const role = document.getElementById('reg-role')?.value;
        const employmentType = document.getElementById('reg-employment-type')?.value || 'Full-Time';
        const name = document.getElementById('reg-name')?.value?.trim() || 'pegawai';
        if (!usernameInput || !employeeCodeInput || !role) return;

        usernameInput.value = 'Membuat pratinjau...';
        employeeCodeInput.value = 'Membuat pratinjau...';
        try {
            const result = await apiRequest('/api/users/identity-preview', {
                method: 'POST',
                body: {
                    role,
                    employment_type: employmentType,
                    name
                }
            });
            usernameInput.value = result.identity?.username || '';
            employeeCodeInput.value = result.identity?.employee_code || '';
        } catch (error) {
            usernameInput.value = '';
            employeeCodeInput.value = '';
            showPremiumNotice('Identitas Belum Dapat Dibuat', escapeHtml(error.message), { variant: 'danger' });
        }
    }
    
    if (btnAddStaff && userRegModal) {
        btnAddStaff.addEventListener('click', async () => {
            const regRoleSelect = document.getElementById('reg-role');
            const employmentTypeContainer = document.getElementById('reg-employment-type-container');
            
            if (employmentTypeContainer) employmentTypeContainer.style.display = 'block';
            
            if (regRoleSelect) {
                regRoleSelect.innerHTML = '';
                let roles = [];
                if (currentUser && currentUser.role === 'mgr_marketing') {
                    roles = [{ value: 'staff_marketing', text: 'ðŸŽ¯ Staff Marketing' }];
                } else if (currentUser && currentUser.role === 'mgr_ops') {
                    roles = [{ value: 'staff_ops', text: 'ðŸ—ï¸ Staff Operasional' }];
                } else if (currentUser && currentUser.role === 'mgr_finance') {
                    roles = [{ value: 'staff_finance', text: 'ðŸ’° Staff Finance' }];
                } else if (currentUser && currentUser.role === 'mgr_hrd') {
                    roles = [{ value: 'staff_hrd', text: 'ðŸ‘¥ Staff HRD' }];
                } else if (currentUser && currentUser.role === 'ceo') {
                    roles = [
                        { value: 'mgr_marketing', text: 'ðŸŽ¯ Manager Marketing' },
                        { value: 'mgr_ops', text: 'ðŸ—ï¸ Manager Operasional' },
                        { value: 'mgr_finance', text: 'ðŸ’° Manager Finance' },
                        { value: 'mgr_hrd', text: 'ðŸ‘¥ HR Manager' },
                        { value: 'staff_marketing', text: 'ðŸŽ¯ Staff Marketing' },
                        { value: 'staff_ops', text: 'ðŸ—ï¸ Staff Operasional' },
                        { value: 'staff_finance', text: 'ðŸ’° Staff Finance' },
                        { value: 'staff_hrd', text: 'ðŸ‘¥ Staff HRD' }
                    ];
                } else {
                    roles = [{ value: 'staff_marketing', text: 'ðŸŽ¯ Staff Marketing' }];
                }
                regRoleSelect.innerHTML = roles.map(r => `<option value="${r.value}">${r.text}</option>`).join('');
            }
            userRegModal.style.display = 'flex';
            await refreshEmployeeIdentityPreview();
        });
    }

    document.getElementById('reg-role')?.addEventListener('change', refreshEmployeeIdentityPreview);
    document.getElementById('reg-employment-type')?.addEventListener('change', refreshEmployeeIdentityPreview);
    document.getElementById('reg-name')?.addEventListener('blur', refreshEmployeeIdentityPreview);
    
    const btnHierarchyAddStaff = document.getElementById('btn-hierarchy-add-staff');
    if (btnHierarchyAddStaff && userRegModal) {
        btnHierarchyAddStaff.addEventListener('click', () => {
            if (btnAddStaff) btnAddStaff.click();
        });
    }
    
    if (btnCancelReg && userRegModal) {
        btnCancelReg.addEventListener('click', () => {
            window.lastSelectedHierarchyParent = null;
            userRegModal.style.display = 'none';
        });
    }
    
    if (regForm && userRegModal) {
        regForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const usernameInputEl = document.getElementById('reg-username');
            const employeeCodeInputEl = document.getElementById('reg-employee-code');
            const nameInputEl = document.getElementById('reg-name');
            const emailInputEl = document.getElementById('reg-email');
            const roleSelectEl = document.getElementById('reg-role');
            const typeSelectEl = document.getElementById('reg-employment-type');
            const jobTitleInputEl = document.getElementById('reg-job-title');
            const regError = document.getElementById('reg-error');
            
            if (!usernameInputEl || !employeeCodeInputEl || !nameInputEl || !emailInputEl || !roleSelectEl || !typeSelectEl || !jobTitleInputEl) return;
            
            const previewUsername = usernameInputEl.value.toLowerCase().trim();
            const previewEmployeeCode = employeeCodeInputEl.value.trim();
            const name = nameInputEl.value.trim();
            const email = emailInputEl.value.trim();
            const role = roleSelectEl.value;
            const employmentType = typeSelectEl.value;
            const jobTitle = jobTitleInputEl.value.trim();
             
            if (!name || !email || !jobTitle) {
                showPremiumNotice('Data Belum Lengkap', 'Lengkapi nama, email OTP, dan nama jabatan sebelum melanjutkan.', { variant: 'danger' });
                return;
            }
            
            const avatarInitials = name
                .split(/\s+/)
                .filter(Boolean)
                .slice(0, 2)
                .map(part => part.charAt(0))
                .join('')
                .toUpperCase();
            const roleTitles = {
                'mgr_marketing': 'Marketing Manager',
                'staff_marketing': 'Marketing Staff',
                'mgr_ops': 'Operations Manager',
                'staff_ops': 'Operations Staff',
                'mgr_finance': 'Finance Manager',
                'staff_finance': 'Finance Staff',
                'mgr_hrd': 'HRD Manager',
                'staff_hrd': 'HRD Staff'
            };
            const roleLevels = {
                'mgr_marketing': 'Level 2 - Manager',
                'staff_marketing': 'Level 3 - Staff',
                'mgr_ops': 'Level 2 - Manager',
                'staff_ops': 'Level 3 - Staff',
                'mgr_finance': 'Level 2 - Manager',
                'staff_finance': 'Level 3 - Staff',
                'mgr_hrd': 'Level 2 - Manager',
                'staff_hrd': 'Level 3 - Staff'
            };
            const roleParents = {
                'mgr_marketing': 'ceo',
                'staff_marketing': 'mgr_marketing',
                'mgr_ops': 'ceo',
                'staff_ops': 'mgr_ops',
                'mgr_finance': 'ceo',
                'staff_finance': 'mgr_finance',
                'mgr_hrd': 'ceo',
                'staff_hrd': 'mgr_hrd'
            };
            
            const userDetails = {
                name: name,
                username: previewUsername,
                employee_code: previewEmployeeCode,
                email: email,
                role: role,
                level: roleLevels[role] || 'Level 3 - Staff',
                parent: window.lastSelectedHierarchyParent || roleParents[role] || 'ceo',
                avatar: avatarInitials,
                title: jobTitle || roleTitles[role] || 'Staff Member',
                job_title: jobTitle,
                employment_type: employmentType
            };
            
            const btnSubmit = document.getElementById('btn-submit-reg');
            const originalText = btnSubmit ? btnSubmit.innerHTML : 'Save to Cloud';
            if (btnSubmit) btnSubmit.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Saving...';
            
            try {
                const isManager = currentUser && currentUser.role.startsWith('mgr_') && currentUser.role !== 'ceo';
                
                if (isManager) {
                    // Kirim pengajuan persetujuan ke CEO
                    const response = await fetch('/api/team-requests', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({
                            requester_username: currentUser.username,
                            action: 'add',
                            details: userDetails
                        })
                    });
                    const resData = await response.json();
                    if (resData.success) {
                        showPremiumNotice('Pengajuan Terkirim', 'Penambahan staf telah dikirim kepada CEO dan akan aktif setelah disetujui.', { variant: 'success' });
                        regForm.reset();
                        userRegModal.style.display = 'none';
                    } else {
                        throw new Error(resData.message || "Gagal mengajukan ke CEO.");
                    }
                } else {
                    // CEO / HRD mendaftarkan langsung ke MySQL
                    const response = await fetch('/api/users', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify(userDetails)
                    });
                    const resData = await response.json();
                    if (resData.success) {
                        const createdUser = resData.user || {};
                        const username = createdUser.username || previewUsername;
                        const employeeCode = createdUser.employee_code || previewEmployeeCode;
                        showPremiumNotice('Staf Berhasil Ditambahkan', `Akun @${escapeHtml(username)} (${escapeHtml(employeeCode)}) dengan jabatan ${escapeHtml(jobTitle)} telah aktif dan akan login menggunakan OTP email.`, { variant: 'success' });
                        
                        state.users[username] = Object.assign({}, userDetails, createdUser, {
                            username,
                            employee_code: employeeCode
                        });
                        updateState(state);
                        
                        regForm.reset();
                        userRegModal.style.display = 'none';
                        syncDataFromServer();
                    } else {
                        throw new Error(resData.message || "Gagal menyimpan ke database.");
                    }
                }
            } catch (err) {
                console.error("Error creating user:", err);
                if (regError) {
                    regError.innerText = "Error: " + err.message;
                    regError.style.display = 'block';
                }
            } finally {
                if (btnSubmit) btnSubmit.innerHTML = originalText;
            }
        });
    }

    // ================= CEO Management Actions (Custom Modals) =================
    const btnCreateDiv = document.getElementById('btn-create-division');
    const divisionModal = document.getElementById('division-modal');
    const closeDivModalBtn = document.getElementById('close-div-modal-btn');
    const btnCancelDiv = document.getElementById('btn-cancel-div');
    const divisionForm = document.getElementById('division-form');

    if (btnCreateDiv && divisionModal) {
        btnCreateDiv.addEventListener('click', () => {
            divisionModal.style.display = 'flex';
        });
    }

    const closeDivModal = () => {
        if (divisionModal) {
            divisionModal.style.display = 'none';
            divisionForm.reset();
        }
    };
    if (closeDivModalBtn) closeDivModalBtn.addEventListener('click', closeDivModal);
    if (btnCancelDiv) btnCancelDiv.addEventListener('click', closeDivModal);

    if (divisionForm) {
        divisionForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const divNameInput = document.getElementById('div-name-input');
            const divName = divNameInput ? divNameInput.value.trim() : '';
            if (!divName) {
                showPremiumNotice('Nama Divisi Belum Diisi', 'Lengkapi nama divisi sebelum melanjutkan.', { variant: 'danger' });
                if (divNameInput) divNameInput.focus();
                return;
            }
            showPremiumNotice('Divisi Berhasil Dibuat', `Divisi â€œ${escapeHtml(divName)}â€ telah ditambahkan ke sistem organisasi.`, { variant: 'success' });
            closeDivModal();
        });
    }

    const btnAppointMgr = document.getElementById('btn-appoint-manager');
    const appointModal = document.getElementById('appoint-modal');
    const closeAppointModalBtn = document.getElementById('close-appoint-modal-btn');
    const btnCancelAppoint = document.getElementById('btn-cancel-appoint');
    const appointForm = document.getElementById('appoint-form');
    const appointStaffSelect = document.getElementById('appoint-staff-select');

    if (btnAppointMgr && appointModal && appointStaffSelect) {
        btnAppointMgr.addEventListener('click', () => {
            appointStaffSelect.innerHTML = '';
            Object.values(state.users).forEach(u => {
                if (u.level.includes('Staff') && u.role !== 'ceo') {
                    appointStaffSelect.innerHTML += `<option value="${u.username}">${u.name} (@${u.username}) - ${u.title}</option>`;
                }
            });
            if (appointStaffSelect.children.length === 0) {
                appointStaffSelect.innerHTML = '<option value="">Tidak ada staf yang tersedia</option>';
            }
            appointModal.style.display = 'flex';
        });
    }

    const closeAppointModal = () => {
        if (appointModal) {
            appointModal.style.display = 'none';
            appointForm.reset();
        }
    };
    if (closeAppointModalBtn) closeAppointModalBtn.addEventListener('click', closeAppointModal);
    if (btnCancelAppoint) btnCancelAppoint.addEventListener('click', closeAppointModal);

    if (appointForm) {
        appointForm.addEventListener('submit', (e) => {
            e.preventDefault();
            const selectedUsername = appointStaffSelect.value;
            if (!selectedUsername) {
                showPremiumNotice('Staf Belum Dipilih', 'Pilih anggota staf yang akan ditunjuk sebagai Manager.', { variant: 'danger' });
                return;
            }
            if (state.users[selectedUsername]) {
                const targetUser = state.users[selectedUsername];
                targetUser.level = 'Level 2 - Manager';
                targetUser.title = targetUser.title.replace('Staff', 'Manager');
                if (targetUser.role.startsWith('staff_')) {
                    targetUser.role = targetUser.role.replace('staff_', 'mgr_');
                }
                updateState(state);
                showPremiumNotice('Manager Berhasil Ditunjuk', `${escapeHtml(targetUser.name)} telah dipromosikan sebagai Manager.`, { variant: 'success' });
                closeAppointModal();
            } else {
                showPremiumNotice('Staf Tidak Valid', 'Pilih staf aktif yang tersedia pada daftar.', { variant: 'danger' });
            }
        });
    }

    // ================= Workspace Clock-in button sync =================
    const workspaceClockBtn = document.getElementById('workspace-clock-btn');
    if (workspaceClockBtn) {
        workspaceClockBtn.addEventListener('click', () => {
            const univClockInBtn = document.getElementById('univ-clock-in-btn');
            if (univClockInBtn) {
                univClockInBtn.click();
            }
        });
    }

    // ================= KPI and Task List Revisions Logic =================
    const defaultKPIs = {
        'ceo': [
            { id: 'kpi-ceo-1', name: 'Strategic Revenue Growth', weight: 40 },
            { id: 'kpi-ceo-2', name: 'Market Share Expansion', weight: 30 },
            { id: 'kpi-ceo-3', name: 'Investor & Stakeholder Satisfaction', weight: 30 }
        ],
        'mgr_marketing': [
            { id: 'kpi-mgr-mkt-1', name: 'Pipeline Omzet Target Achieved', weight: 40 },
            { id: 'kpi-mgr-mkt-2', name: 'Conversion Leads to Sales', weight: 20 },
            { id: 'kpi-mgr-mkt-3', name: 'Fully Loaded CAC < 30%', weight: 20 },
            { id: 'kpi-mgr-mkt-4', name: 'Organic Traffic & Lead Cost', weight: 20 }
        ],
        'maulana': [
            { id: 'kpi-mau-1', name: 'SEO Traffic & Google Rankings', weight: 40 },
            { id: 'kpi-mau-2', name: 'Landing Page Lead Conversions', weight: 30 },
            { id: 'kpi-mau-3', name: 'Website Core Web Vitals Optimization', weight: 30 }
        ],
        'dbest': [
            { id: 'kpi-db-1', name: 'Video Production Output Consistency', weight: 40 },
            { id: 'kpi-db-2', name: 'Video Views & Audience Retention', weight: 30 },
            { id: 'kpi-db-3', name: 'Brand Mentions & Inquiries generated', weight: 30 }
        ],
        'mgr_ops': [
            { id: 'kpi-mgr-ops-1', name: 'Project Timeline Adherence', weight: 40 },
            { id: 'kpi-mgr-ops-2', name: 'Material Cost Efficiency', weight: 30 },
            { id: 'kpi-mgr-ops-3', name: 'Client Quality Approval Rate', weight: 30 }
        ],
        'staff_ops': [
            { id: 'kpi-staff-ops-1', name: 'Site Survey Execution', weight: 50 },
            { id: 'kpi-staff-ops-2', name: 'Denah & Design Drafting accuracy', weight: 50 }
        ],
        'mgr_finance': [
            { id: 'kpi-mgr-fin-1', name: 'Cashflow Buffer Margin Maintenance', weight: 40 },
            { id: 'kpi-mgr-fin-2', name: 'Invoice Collection Cycle Time', weight: 30 },
            { id: 'kpi-mgr-fin-3', name: 'Audit Compliance & Tax reporting', weight: 30 }
        ],
        'staff_finance': [
            { id: 'kpi-staff-fin-1', name: 'Payroll Processing Timeliness', weight: 50 },
            { id: 'kpi-staff-fin-2', name: 'Expense Invoice verification rate', weight: 50 }
        ],
        'staff_marketing': [
            { id: 'kpi-staff-mkt-1', name: 'SEO Traffic & Google Rankings', weight: 40 },
            { id: 'kpi-staff-mkt-2', name: 'Landing Page Lead Conversions', weight: 30 },
            { id: 'kpi-staff-mkt-3', name: 'Social Media Content & Engagements', weight: 30 }
        ],
        'mgr_hrd': [
            { id: 'kpi-mgr-hrd-1', name: 'Employee Retention & Satisfaction', weight: 40 },
            { id: 'kpi-mgr-hrd-2', name: 'Recruitment & Onboarding Efficiency', weight: 30 },
            { id: 'kpi-mgr-hrd-3', name: 'KPI Compliance & Performance Reviews', weight: 30 }
        ],
        'staff_hrd': [
            { id: 'kpi-staff-hrd-1', name: 'Daily Attendance & Leave Logging', weight: 50 },
            { id: 'kpi-staff-hrd-2', name: 'Employee File Documentation', weight: 50 }
        ]
    };

    if (!state.kpiConfig) {
        state.kpiConfig = JSON.parse(JSON.stringify(defaultKPIs));
        updateState(state);
    }

    function getWorkingDaysInMonth(year, month) {
        let count = 0;
        const totalDays = new Date(year, month + 1, 0).getDate();
        const holidays = [
            '01-01', // Tahun Baru Masehi
            '05-01', // Hari Buruh
            '06-01', // Hari Lahir Pancasila
            '08-17', // Hari Kemerdekaan RI
            '12-25'  // Hari Raya Natal
        ];
        
        for (let d = 1; d <= totalDays; d++) {
            const date = new Date(year, month, d);
            const dayOfWeek = date.getDay(); // 0 = Sun, 6 = Sat
            
            const monthStr = String(month + 1).padStart(2, '0');
            const dayStr = String(d).padStart(2, '0');
            const dateKey = `${monthStr}-${dayStr}`;
            const fullDateKey = `${year}-${monthStr}-${dayStr}`;
            
            const isOverriddenToWork = state.calendarOverrides && state.calendarOverrides.includes(fullDateKey);
            
            if (isOverriddenToWork) {
                count++;
            } else {
                if (dayOfWeek !== 0 && dayOfWeek !== 6) {
                    if (!holidays.includes(dateKey)) {
                        count++;
                    }
                }
            }
        }
        return count;
    }

    function calculateHoursWorked(timeInStr, timeOutStr) {
        if (!timeInStr || !timeOutStr) return 0;
        
        const parseTime = (str) => {
            const parts = str.split(' ');
            if (parts.length < 2) {
                const [h, m] = str.split(':').map(Number);
                return h * 60 + (m || 0);
            }
            const [time, period] = parts;
            let [h, m] = time.split(':').map(Number);
            if (period === 'PM' && h < 12) h += 12;
            if (period === 'AM' && h === 12) h = 0;
            return h * 60 + (m || 0);
        };

        const inMin = parseTime(timeInStr);
        const outMin = parseTime(timeOutStr);
        return Math.max(0, outMin - inMin) / 60;
    }

    function downloadCSV(filename, csvData) {
        const blob = new Blob([csvData], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement("a");
        const url = URL.createObjectURL(blob);
        link.setAttribute("href", url);
        link.setAttribute("download", filename);
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function exportPersonalAttendanceCSV() {
        if (!currentUser) return;
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth();
        const monthPrefix = `${year}-${String(month + 1).padStart(2, '0')}`;
        
        let csvContent = "Tanggal,Jam Masuk,Jam Keluar,Durasi (Jam),Target Shift (Jam),Kekurangan (Jam),Status,Lokasi,Tipe\n";
        
        const myLogs = state.attendance.filter(a => a.username === currentUser.username && a.date.startsWith(monthPrefix));
        
        myLogs.forEach(a => {
            let hrs = 0;
            if (a.timeOut || a.status === 'Clocked Out') {
                hrs = calculateHoursWorked(a.time, a.timeOut);
            }
            const deficit = Math.max(0, 9 - hrs);
            const location = (a.location_name || '').replace(/,/g, ' ');
            csvContent += `"${a.date}","${a.time || ''}","${a.timeOut || ''}","${hrs.toFixed(2)}","9.00","${deficit.toFixed(2)}","${a.status || ''}","${location}","${a.type || 'WFO'}"\n`;
        });

        downloadCSV(`Riwayat_Absensi_${currentUser.username}_${monthPrefix}.csv`, csvContent);
    }

    function exportAllStaffAttendanceCSV() {
        const now = new Date();
        const year = now.getFullYear();
        const month = now.getMonth();
        const targetDays = getWorkingDaysInMonth(year, month);
        const targetHours = targetDays * 9;
        const monthPrefix = `${year}-${String(month + 1).padStart(2, '0')}`;
        
        let csvContent = "Username,Nama,Jabatan,Role,Target Hari,Target Jam,Jam Aktual,Defisit Jam,Status Jam\n";
        
        Object.values(state.users).forEach(emp => {
            let actualHours = 0;
            state.attendance.forEach(a => {
                if (a.username === emp.username && a.date.startsWith(monthPrefix)) {
                    if (a.status === 'Clocked Out' || a.timeOut) {
                        actualHours += calculateHoursWorked(a.time, a.timeOut);
                    }
                }
            });
            const deficit = targetHours - actualHours;
            const statusStr = deficit <= 0 ? `Surplus +${Math.abs(deficit).toFixed(1)} Jam` : `Defisit -${deficit.toFixed(1)} Jam`;
            csvContent += `"${emp.username}","${emp.name}","${emp.title || ''}","${emp.role}","${targetDays}","${targetHours}","${actualHours.toFixed(1)}","${deficit.toFixed(1)}","${statusStr}"\n`;
        });

        downloadCSV(`Laporan_Absensi_AllStaff_${monthPrefix}.csv`, csvContent);
    }

    function renderManagerTaskApprovals() {
        const card = document.getElementById('manager-task-approval-card');
        const list = document.getElementById('manager-task-requests-list');
        if (!card || !list) return;

        const isManager = currentUser.role.startsWith('mgr_');
        if (!isManager) {
            card.style.display = 'none';
            return;
        }

        // Find all pending approval tasks for staff under this manager
        const pendingTasks = state.tasks.filter(t => {
            if (t.status !== 'pending_manager') return false;
            const staffUser = state.users[t.username];
            if (!staffUser) return false;
            return staffUser.parent === currentUser.username;
        });

        if (pendingTasks.length === 0) {
            card.style.display = 'block';
            list.innerHTML = `
                <div style="background: rgba(255,255,255,0.02); border: 1px dashed var(--glass-border); border-radius: var(--radius-md); padding: 14px; text-align: center; font-size: 12px; color: var(--text-secondary);">
                    <i class="ph ph-check-circle" style="font-size: 20px; color: var(--success); margin-bottom: 4px; display: block;"></i>
                    <b>Tidak Ada Pengajuan Tugas Staf Pending</b>
                    <p style="margin: 4px 0 0 0; font-size: 11px; color: var(--text-muted);">Jika staf tim membuat tugas baru, tugas tersebut akan otomatis menunggu konfirmasi Anda di sini.</p>
                </div>
            `;
            return;
        }

        card.style.display = 'block';
        list.innerHTML = '';

        pendingTasks.forEach(task => {
            const staffUser = state.users[task.username] || { name: task.username };
            list.innerHTML += `
                <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); padding: 12px 16px; border-radius: var(--radius-sm); display: flex; justify-content: space-between; align-items: center; gap: 12px;">
                    <div style="flex: 1;">
                        <h5 style="margin: 0; font-size: 13px; color: white;">${task.title}</h5>
                        <p style="margin: 4px 0 0 0; font-size: 11px; color: var(--text-secondary);">Pembuat: <b>${staffUser.name}</b> (@${task.username}) &bull; Target KPI: ${task.relation}</p>
                    </div>
                    <div style="display: flex; gap: 6px;">
                        <button class="primary-btn btn-approve-task" data-task-id="${task.id}" style="padding: 4px 10px; font-size: 11px; background: rgba(52, 199, 89, 0.2); border-color: var(--success); color: var(--success); font-family: inherit;"><i class="ph ph-check"></i> Setujui</button>
                        <button class="primary-btn btn-reject-task" data-task-id="${task.id}" style="padding: 4px 10px; font-size: 11px; background: rgba(255, 59, 48, 0.2); border-color: var(--danger); color: var(--danger); font-family: inherit;"><i class="ph ph-x"></i> Tolak</button>
                    </div>
                </div>
            `;
        });

        list.querySelectorAll('.btn-approve-task').forEach(b => {
            b.onclick = async () => {
                const task = state.tasks.find(item => String(item.id) === String(b.dataset.taskId));
                if (!task?.approval_id) return;
                try {
                    const result = await apiRequest(`/api/approvals/${task.approval_id}/approve`, {
                        method: 'POST',
                        body: { note: 'Task staf disetujui manager.' }
                    });
                    showPremiumNotice('Task Disetujui', escapeHtml(result.message));
                    await syncDataFromServer();
                } catch (error) {
                    showPremiumNotice('Tidak Dapat Memproses', escapeHtml(error.message));
                }
            };
        });

        list.querySelectorAll('.btn-reject-task').forEach(b => {
            b.onclick = () => {
                const task = state.tasks.find(item => String(item.id) === String(b.dataset.taskId));
                if (!task?.approval_id) return;
                showTextInputDialog({
                    title: 'Alasan Penolakan Task',
                    description: 'Jelaskan kaitan task yang perlu diperbaiki terhadap KPI atau prioritas tim.',
                    label: 'Alasan penolakan',
                    defaultValue: 'Task belum sesuai dengan prioritas KPI.',
                    submitText: 'Tolak Task'
                }, async note => {
                    try {
                        const result = await apiRequest(`/api/approvals/${task.approval_id}/reject`, {
                            method: 'POST',
                            body: { note }
                        });
                        showPremiumNotice('Task Ditolak', escapeHtml(result.message), { variant: 'success' });
                        await syncDataFromServer();
                    } catch (error) {
                        showPremiumNotice('Tidak Dapat Memproses', escapeHtml(error.message), { variant: 'danger' });
                    }
                });
            };
        });
    }

    function renderKPITasksView() {
        const view = document.getElementById('view-kpi-tasks');
        if (!currentUser) return;

        // 1. Dynamic HRIS Attendance Deficit calculations
        const attendWidget = document.getElementById('user-attendance-summary-widget');
        if (attendWidget) {
            const now = new Date();
            const year = now.getFullYear();
            const month = now.getMonth();
            
            const targetDays = getWorkingDaysInMonth(year, month);
            const targetHours = targetDays * 9;
            
            let totalActualHours = 0;
            let presentDaysCount = 0;
            let todayStatusText = 'Belum Absen Hari Ini';
            
            const monthPrefix = `${year}-${String(month + 1).padStart(2, '0')}`;
            const todayStr = formatDateJakarta(now);

            state.attendance.forEach(a => {
                if (a.username === currentUser.username && a.date.startsWith(monthPrefix)) {
                    if (a.status === 'Clocked Out' || a.timeOut) {
                        const hrs = calculateHoursWorked(a.time, a.timeOut);
                        totalActualHours += hrs;
                        if (a.date === todayStr) {
                            todayStatusText = `Jam Kerja Hari Ini: ${hrs.toFixed(1)} Jam (${hrs >= 9 ? 'Terpenuhi âœ”' : 'Kurang dari 9 Jam âš ï¸'})`;
                        }
                    } else {
                        if (a.date === todayStr) {
                            todayStatusText = `Clocked In pada ${a.time} (Belum Clock Out)`;
                        }
                    }
                    presentDaysCount++;
                }
            });

            const deficit = Math.max(0, targetHours - totalActualHours);
            
            attendWidget.innerHTML = `
                <div style="display: flex; flex-direction: column; gap: 6px;">
                    <div><b>Hari Kerja Aktif Bulan Ini:</b> ${targetDays} Hari (${targetHours} Jam Target Shift @ 9 Jam/Hari)</div>
                    <div><b>Hari Masuk Kerja:</b> ${presentDaysCount} Hari</div>
                    <div><b>Total Jam Kerja Aktual:</b> ${totalActualHours.toFixed(1)} Jam</div>
                    <div style="font-size: 14px; font-weight: 700; color: ${deficit > 0 ? 'var(--danger)' : 'var(--success)'};">
                        <b>Kekurangan Jam (Defisit):</b> ${deficit.toFixed(1)} Jam
                    </div>
                    <div style="background: rgba(255,255,255,0.05); padding: 8px; border-radius: 4px; font-size: 11px; margin-top: 4px; color: var(--text-secondary);">
                        <i class="ph ph-info"></i> ${todayStatusText}
                    </div>
                    <button id="btn-download-my-attendance-csv" class="primary-btn" style="margin-top: 8px; width: 100%; justify-content: center; font-size: 12px; background: rgba(52, 199, 89, 0.15); color: var(--success); border: 1px solid rgba(52, 199, 89, 0.3); font-family: inherit;"><i class="ph ph-file-csv"></i> Unduh Riwayat Presensi Saya (CSV)</button>
                </div>
            `;
            const btnDownloadMyCsv = document.getElementById('btn-download-my-attendance-csv');
            if (btnDownloadMyCsv) {
                btnDownloadMyCsv.onclick = exportPersonalAttendanceCSV;
            }
        }

        const activeUnfinishedTasks = state.tasks.filter(t => t.username === currentUser.username && t.status !== 'done' && t.status !== 'rejected');
        const h3NearingCount = activeUnfinishedTasks.filter(t => {
            const d = t.deadline ? new Date(t.deadline).getTime() : (Date.now() + 3*86400000);
            const diff = Math.ceil((d - Date.now()) / 86400000);
            return diff <= 3;
        }).length;

        const reminderBannerContainer = document.getElementById('daily-task-reminder-banner');
        if (reminderBannerContainer) {
            if (activeUnfinishedTasks.length > 0) {
                reminderBannerContainer.style.display = 'block';
                reminderBannerContainer.innerHTML = `
                    <div style="background: rgba(255, 159, 10, 0.12); border: 1px solid var(--warning); border-radius: var(--radius-md); padding: 12px 16px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center;">
                        <div style="display: flex; gap: 10px; align-items: center; color: var(--warning); font-size: 13px;">
                            <i class="ph ph-bell-ringing" style="font-size: 20px;"></i>
                            <span><b>Pengingat Tugas Harian:</b> Anda memiliki <b>${activeUnfinishedTasks.length} tugas aktif</b> yang belum selesai. ${h3NearingCount > 0 ? `<b style="color: var(--danger);">(âš ï¸ ${h3NearingCount} tugas mendekati deadline â‰¤ 3 hari!)</b>` : ''}</span>
                        </div>
                    </div>
                `;
            } else {
                reminderBannerContainer.style.display = 'none';
            }
        }

        renderManagerTaskApprovals();

        // Find division & personal target KPIs
        let divisionKpiKey = '';
        let personalKpiKey = currentUser.username;

        if (currentUser.role === 'staff_marketing') {
            divisionKpiKey = 'mgr_marketing';
        } else if (currentUser.role === 'staff_ops') {
            divisionKpiKey = 'mgr_ops';
        } else if (currentUser.role === 'staff_finance') {
            divisionKpiKey = 'mgr_finance';
        } else if (currentUser.role === 'staff_hrd') {
            divisionKpiKey = 'mgr_hrd';
        } else {
            divisionKpiKey = 'ceo';
            personalKpiKey = currentUser.role;
        }

        const divKPIs = state.kpiConfig[divisionKpiKey] || [];
        const personalKPIs = state.kpiConfig[personalKpiKey] || state.kpiConfig[currentUser.role] || [];

        // 2. Populate Division KPIs
        const divContainer = document.getElementById('division-kpi-list-container');
        if (divContainer) {
            divContainer.innerHTML = '';
            if (divKPIs.length === 0) {
                divContainer.innerHTML = '<div style="color: var(--text-muted); font-size: 12px;">Tidak ada KPI divisi khusus yang terdaftar.</div>';
            } else {
                divKPIs.forEach(k => {
                    divContainer.innerHTML += `
                        <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 8px 12px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-size: 12px; font-weight: 600; color: white;">${k.name}</div>
                                <div style="font-size: 10px; color: var(--text-secondary); margin-top: 2px;">Bobot KPI: ${k.weight}%</div>
                            </div>
                            <span class="badge warning" style="font-size: 9px; padding: 2px 6px;">Division Goal</span>
                        </div>
                    `;
                });
            }
        }

        // Render Division Goal Comments & Feedback
        const divCommentInput = document.getElementById('division-goal-comment-input');
        const divInputArea = document.getElementById('division-goal-input-area');
        if (divInputArea) {
            divInputArea.style.display = currentUser.role.startsWith('mgr_') ? 'flex' : 'none';
        }

        function renderDivGoalComments() {
            const listEl = document.getElementById('division-goal-comments-list');
            if (!listEl) return;
            listEl.innerHTML = '';
            
            const comments = (state.divisionGoalComments || []).filter(c => c.division === divisionKpiKey);
            if (comments.length === 0) {
                listEl.innerHTML = '<div style="color: var(--text-muted); font-size: 11px; font-style: italic; padding: 4px 0;">Belum ada feedback untuk Goal Divisi ini.</div>';
            } else {
                comments.forEach(c => {
                    listEl.innerHTML += `
                        <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); padding: 6px; border-radius: 4px; font-size: 11px; color: white;">
                            <b>${c.senderName}:</b> ${c.text}
                            <span style="font-size: 8px; color: var(--text-muted); float: right;">${new Date(c.timestamp).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                        </div>
                    `;
                });
            }
            listEl.scrollTop = listEl.scrollHeight;
        }
        renderDivGoalComments();

        const divCommentBtn = document.getElementById('btn-submit-divgoal-comment');
        if (divCommentBtn) {
            divCommentBtn.onclick = () => {
                const txt = divCommentInput ? divCommentInput.value.trim() : '';
                if (txt) {
                    const newComment = {
                        id: 'dgc-' + Date.now(),
                        division: divisionKpiKey,
                        sender: currentUser.username,
                        senderName: currentUser.name || currentUser.username,
                        text: txt,
                        timestamp: Date.now()
                    };
                    if (!state.divisionGoalComments) state.divisionGoalComments = [];
                    state.divisionGoalComments.push(newComment);
                    updateState(state);
                    if (divCommentInput) divCommentInput.value = '';
                    renderDivGoalComments();
                }
            };
        }

        // 3. Populate Personal KPIs
        const kpiContainer = document.getElementById('user-kpi-list-container');
        if (kpiContainer) {
            kpiContainer.innerHTML = '';
            if (personalKPIs.length === 0) {
                kpiContainer.innerHTML = '<div style="color: var(--text-muted); font-size: 12px;">Tidak ada KPI pribadi khusus yang terdaftar.</div>';
            } else {
                personalKPIs.forEach(k => {
                    kpiContainer.innerHTML += `
                        <div style="background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 8px 12px; display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <div style="font-size: 12px; font-weight: 600; color: white;">${k.name}</div>
                                <div style="font-size: 10px; color: var(--text-secondary); margin-top: 2px;">Bobot KPI: ${k.weight}%</div>
                            </div>
                            <span class="badge success" style="font-size: 9px; padding: 2px 6px;">Active</span>
                        </div>
                    `;
                });
            }
        }

        // 4. Populate the KPI select dropdown in the form
        const kpiSelect = document.getElementById('task-kpi-select');
        const assigneeContainer = document.getElementById('task-assignee-container');
        const assigneeSelect = document.getElementById('task-assignee-select');

        function updateTaskKPISelect(targetUser) {
            if (!kpiSelect) return;
            kpiSelect.innerHTML = '<option value="" data-name="Tugas Mandiri">Task mandiri â€” tanpa KPI</option>';
            
            const effectiveUser = targetUser || (currentUser ? currentUser.username : '');
            const targetUserObj = state.users[effectiveUser] || {};
            const targetRole = targetUserObj.role || effectiveUser || '';
            let targetKPIs = state.kpiConfig[effectiveUser] || state.kpiConfig[targetRole];
            
            if (targetKPIs && targetKPIs.length > 0) {
                targetKPIs.forEach(k => {
                    kpiSelect.innerHTML += `<option value="${k.id}" data-name="${escapeHtml(k.name)}">${escapeHtml(k.goalTitle || 'Goal Divisi')} â€” ${escapeHtml(k.name)} (${k.weight}%)</option>`;
                });
            }
            
            if (!targetKPIs || targetKPIs.length === 0) {
                kpiSelect.innerHTML += '<option value="" disabled>Belum ada KPI yang disahkan CEO</option>';
            }
        }

        // Setup Assignee selector for Manager/CEO
        const isManager = currentUser && currentUser.role && currentUser.role.startsWith('mgr_');
        const isCeo = currentUser && currentUser.role === 'ceo';

        if (assigneeContainer && assigneeSelect) {
            if (isManager || isCeo) {
                assigneeContainer.style.display = 'flex';
                const currentVal = assigneeSelect.value || currentUser.username;
                assigneeSelect.innerHTML = `<option value="${currentUser.username}">Diri Sendiri (${currentUser.name})</option>`;
                Object.values(state.users).forEach(u => {
                    let isTim = false;
                    if (isCeo && u.username !== 'ceo') {
                        isTim = true;
                    } else if (isManager) {
                        if (u.parent === currentUser.username) isTim = true;
                        else if (currentUser.role === 'mgr_marketing' && u.role === 'staff_marketing') isTim = true;
                        else if (currentUser.role === 'mgr_ops' && u.role === 'staff_ops') isTim = true;
                        else if (currentUser.role === 'mgr_finance' && u.role === 'staff_finance') isTim = true;
                        else if (currentUser.role === 'mgr_hrd' && u.role === 'staff_hrd') isTim = true;
                    }
                    if (isTim && u.username !== currentUser.username) {
                        assigneeSelect.innerHTML += `<option value="${u.username}">${u.name} (${u.title})</option>`;
                    }
                });
                if (currentVal && Array.from(assigneeSelect.options).some(o => o.value === currentVal)) {
                    assigneeSelect.value = currentVal;
                }
                updateTaskKPISelect(assigneeSelect.value);
                assigneeSelect.onchange = () => {
                    updateTaskKPISelect(assigneeSelect.value);
                };
            } else {
                assigneeContainer.style.display = 'none';
                updateTaskKPISelect(currentUser.username);
            }
        } else {
            updateTaskKPISelect(currentUser ? currentUser.username : '');
        }

        // 5. Populate Team Monitor for Superior Roles (Manager or CEO)
        const teamMonitor = document.getElementById('manager-team-monitor');
        const teamSelect = document.getElementById('team-member-select');

        if (teamMonitor && teamSelect) {
            if (isManager || isCeo) {
                teamMonitor.style.display = 'block';
                if (teamSelect.children.length <= 1) {
                    teamSelect.innerHTML = '<option value="">-- Pilih Anggota Tim --</option>';
                    Object.values(state.users).forEach(u => {
                        let isTim = false;
                        if (isCeo && u.username !== 'ceo') {
                            isTim = true;
                        } else if (isManager) {
                            if (currentUser.role === 'mgr_marketing' && u.role === 'staff_marketing') isTim = true;
                            if (currentUser.role === 'mgr_ops' && u.role === 'staff_ops') isTim = true;
                            if (currentUser.role === 'mgr_finance' && u.role === 'staff_finance') isTim = true;
                            if (currentUser.role === 'mgr_hrd' && u.role === 'staff_hrd') isTim = true;
                        }
                        if (isTim) {
                            teamSelect.innerHTML += `<option value="${u.username}">${u.name} (${u.title})</option>`;
                        }
                    });
                }
            } else {
                teamMonitor.style.display = 'none';
            }
        }

        // Dynamic KPI Weight Editor for Managers
        const weightEditor = document.getElementById('manager-kpi-weight-editor');
        const slidersList = document.getElementById('kpi-weight-sliders-list');
        const saveWeightsBtn = document.getElementById('btn-save-kpi-weights');
        
        if (weightEditor && teamSelect) {
            const selectedUser = teamSelect.value;
            const isManagerUser = currentUser.role.startsWith('mgr_') && currentUser.role !== 'ceo';
            const delStaffBtn = document.getElementById('btn-request-delete-staff');
            
            if (isManagerUser && selectedUser) {
                weightEditor.style.display = 'block';
                if (delStaffBtn) delStaffBtn.style.display = 'block';
                
                const userKPIs = state.kpiConfig[selectedUser] || [];
                slidersList.innerHTML = '';
                if (userKPIs.length === 0) {
                    slidersList.innerHTML = '<div style="color: var(--text-muted); font-size: 11px;">Tidak ada KPI yang terdaftar untuk staf ini.</div>';
                    saveWeightsBtn.style.display = 'none';
                } else {
                    saveWeightsBtn.style.display = 'block';
                    userKPIs.forEach(k => {
                        slidersList.innerHTML += `
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; font-size: 12px; color: white;">
                                <span style="flex: 1; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">${k.name}</span>
                                <input type="number" class="kpi-weight-input" data-kpi-id="${k.id}" value="${k.weight}" disabled aria-label="Bobot KPI disahkan CEO" style="width: 70px; background: var(--bg-sidebar); border: 1px solid var(--glass-border); padding: 4px 8px; border-radius: 4px; color: white; opacity: .75; font-family: inherit;">
                                <span>%</span>
                            </div>
                        `;
                    });
                }
            } else {
                weightEditor.style.display = 'none';
                if (delStaffBtn) delStaffBtn.style.display = 'none';
            }
        }

        const delStaffBtn = document.getElementById('btn-request-delete-staff');
        if (delStaffBtn && teamSelect) {
            delStaffBtn.onclick = () => {
                const selectedUser = teamSelect.value;
                if (!selectedUser) return;
                
                showStaffSeparationDialog(state.users[selectedUser] || { username: selectedUser, name: selectedUser }, 'request', async separation => {
                    const result = await apiRequest('/api/team-requests', {
                        method: 'POST',
                        body: {
                            action: 'delete',
                            target_username: selectedUser,
                            ...separation,
                        },
                    });
                    showPremiumNotice(
                        'Pengajuan Terkirim',
                        escapeHtml(result.message || 'Permintaan penonaktifan staf telah dikirim kepada CEO.'),
                        { variant: 'success' },
                    );
                });
            };
        }

        if (saveWeightsBtn && teamSelect) {
            saveWeightsBtn.onclick = () => {
                switchView('setup');
                showPremiumNotice(
                    'Revisi KPI melalui alur persetujuan',
                    'Susun rencana KPI baru pada menu ini. Perubahan bobot akan aktif setelah disahkan CEO.'
                );
            };
        }

        // 6. Render Task Checklist
        renderKPITaskList();
    }

    function renderKPITaskList() {
        const isManager = currentUser.role.startsWith('mgr_');
        const isCeo = currentUser.role === 'ceo';
        const teamSelect = document.getElementById('team-member-select');

        let targetUsername = currentUser.username;
        let readOnlyMode = false;
        
        if ((isManager || isCeo) && teamSelect && teamSelect.value) {
            targetUsername = teamSelect.value;
            readOnlyMode = true;
            const userObj = state.users[targetUsername];
            document.getElementById('task-list-title').innerText = `Daftar Tugas Tim: ${userObj ? userObj.name : targetUsername}`;
        } else {
            document.getElementById('task-list-title').innerText = 'Daftar Tugas Saya';
        }

        const taskListContainer = document.getElementById('kpi-task-list');
        if (!taskListContainer) return;
        taskListContainer.innerHTML = '';

        const userTasks = state.tasks.filter(t => t.username === targetUsername);
        if (userTasks.length === 0) {
            taskListContainer.innerHTML = '<div style="color: var(--text-muted); padding: 24px; text-align: center; font-size: 13px;">Belum ada tugas terdaftar.</div>';
            return;
        }

        userTasks.forEach(task => {
            const isChecked = ['submitted_for_review', 'verified'].includes(task.status) ? 'checked' : '';
            const checkboxDisabled = readOnlyMode || !['in_progress', 'revision_requested'].includes(task.status);
            const comments = (state.taskComments || []).filter(c => c.taskId === task.id);
            let commentsHtml = '';
            
            comments.forEach(c => {
                const isMyComment = c.sender === currentUser.username;
                commentsHtml += `
                    <div style="background: ${isMyComment ? 'rgba(10, 132, 255, 0.08)' : 'rgba(255,255,255,0.02)'}; border: 1px solid var(--glass-border); padding: 6px 10px; border-radius: 4px; margin-bottom: 4px;">
                        <span style="font-weight: 600; color: ${isMyComment ? 'var(--primary)' : 'var(--warning)'};">${c.senderName}:</span>
                        <span style="color: white; margin-left: 4px;">${c.text}</span>
                        <span style="font-size: 9px; color: var(--text-muted); float: right; margin-top: 2px;">${new Date(c.timestamp).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}</span>
                        <div style="clear: both;"></div>
                    </div>
                `;
            });

            let kpiRelationHtml = '';
            if (task.relation && task.relation !== 'Umum' && task.relation !== 'General Tasks') {
                kpiRelationHtml = `<span class="badge warning" style="font-size: 10px; background: rgba(10, 132, 255, 0.15); color: var(--info); border: 1px solid rgba(10, 132, 255, 0.3); padding: 2px 6px;"><i class="ph ph-target"></i> KPI: ${task.relation}</span>`;
            } else {
                kpiRelationHtml = `<span class="badge" style="font-size: 10px; background: rgba(239, 68, 68, 0.15); color: var(--danger); border: 1px solid rgba(239, 68, 68, 0.3); padding: 2px 6px;"><i class="ph ph-warning-circle"></i> KPI: Belum dikaitkan dengan KPI</span>`;
            }

            const nowMs = Date.now();
            const deadlineMs = task.deadline ? new Date(task.deadline).getTime() : (nowMs + 3 * 86400000);
            const diffDays = Math.ceil((deadlineMs - nowMs) / (1000 * 60 * 60 * 24));
            
            let deadlineReminderBadge = '';
            if (!['verified', 'rejected', 'cancelled'].includes(task.status)) {
                if (diffDays <= 7 && diffDays >= 0) {
                    deadlineReminderBadge = `<span class="badge warning" style="font-size: 10px; background: rgba(255, 159, 10, 0.25); color: var(--warning); border: 1px solid var(--warning); padding: 2px 6px; font-weight: 700;"><i class="ph ph-warning"></i> H-${diffDays} Deadline!</span>`;
                } else if (diffDays < 0) {
                    deadlineReminderBadge = `<span class="badge danger" style="font-size: 10px; background: rgba(255, 59, 48, 0.25); color: var(--danger); border: 1px solid var(--danger); padding: 2px 6px; font-weight: 700;"><i class="ph ph-warning-circle"></i> Overdue (${Math.abs(diffDays)} Hari)</span>`;
                }
            }

            let statusBadgeHtml = `<span class="badge" style="font-size: 10px; text-transform: uppercase; padding: 2px 6px;">${task.status}</span>`;
            if (task.status === 'pending_manager') {
                statusBadgeHtml = `<span class="badge warning" style="font-size: 10px; text-transform: uppercase; padding: 2px 6px; background: rgba(242, 201, 76, 0.2); color: var(--warning); font-weight: 700;"><i class="ph ph-clock"></i> Pending Approval Manager</span>`;
            } else if (task.status === 'submitted_for_review') {
                statusBadgeHtml = '<span class="badge info" style="font-size: 10px; padding: 2px 6px;"><i class="ph ph-hourglass"></i> Menunggu Verifikasi Atasan</span>';
            } else if (task.status === 'verified') {
                statusBadgeHtml = '<span class="badge success" style="font-size: 10px; padding: 2px 6px;"><i class="ph ph-seal-check"></i> Terverifikasi</span>';
            } else if (task.status === 'revision_requested') {
                statusBadgeHtml = '<span class="badge warning" style="font-size: 10px; padding: 2px 6px;"><i class="ph ph-arrow-counter-clockwise"></i> Perlu Revisi</span>';
            }

            const reviewActions = readOnlyMode && task.status === 'submitted_for_review'
                ? `<div style="display:flex; gap:8px; margin-top:8px;">
                       <button class="primary-btn btn-verify-task" data-task-id="${task.id}" style="padding:6px 10px; background:var(--success); color:#020617;"><i class="ph ph-seal-check"></i> Verifikasi</button>
                       <button class="primary-btn btn-revise-task" data-task-id="${task.id}" style="padding:6px 10px; background:var(--warning); color:#020617;"><i class="ph ph-arrow-counter-clockwise"></i> Minta Revisi</button>
                   </div>`
                : '';
            const attachmentLinks = (task.attachments || []).map(file => `
                <a class="task-file-chip" href="${escapeHtml(file.download_url)}" target="_blank" rel="noopener">
                    <i class="ph ph-paperclip"></i> ${escapeHtml(file.name)}
                </a>`).join('');

            taskListContainer.innerHTML += `
                <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 16px; display: flex; flex-direction: column; gap: 8px; margin-bottom: 12px; font-family: inherit;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px;">
                        <div style="display: flex; gap: 12px; align-items: flex-start;">
                            <input type="checkbox" class="task-checkbox" data-task-id="${task.id}" ${isChecked} ${checkboxDisabled ? 'disabled' : ''} style="margin-top: 4px; cursor: ${checkboxDisabled ? 'default' : 'pointer'};">
                            <div>
                                <span style="font-size: 14px; font-weight: 600; color: white; text-decoration: ${task.status === 'verified' ? 'line-through' : 'none'};">${escapeHtml(task.title)}</span>
                                <div style="display: flex; gap: 8px; margin-top: 6px; flex-wrap: wrap;">
                                    ${kpiRelationHtml}
                                    ${deadlineReminderBadge}
                                    ${statusBadgeHtml}
                                </div>
                            </div>
                        </div>
                        <div class="governed-row-actions">
                        ${task.can_edit ? `<button type="button" class="erp-edit-btn icon-only" data-edit-task="${task.id}" title="Edit task"><i class="ph ph-pencil-simple"></i></button>` : ''}
                        <button type="button" class="erp-delete-btn icon-only" data-erp-delete
                            data-resource-type="task"
                            data-resource-id="${String(task.id).replace(/\D/g, '')}"
                            data-resource-label="Task ${escapeHtml(task.title)}"
                            title="Hapus atau ajukan penghapusan task">
                            <i class="ph ph-trash"></i>
                        </button>
                        </div>
                    </div>
                    ${attachmentLinks ? `<div class="task-file-list">${attachmentLinks}</div>` : ''}
                    ${!readOnlyMode && ['in_progress', 'revision_requested'].includes(task.status) ? `<label class="task-evidence-upload"><i class="ph ph-upload-simple"></i><span>Lampirkan laporan / bukti hasil</span><input type="file" data-task-evidence="${task.id}" accept=".pdf,.doc,.docx,.xls,.xlsx,.csv,.txt,.jpg,.jpeg,.png,.webp,.zip"></label>` : ''}
                    ${reviewActions}
                    <!-- Comments section -->
                    <div style="margin-top: 8px; border-top: 1px solid rgba(255,255,255,0.05); padding-top: 8px;">
                        <div style="font-size: 11px; font-weight: 600; color: var(--text-secondary); margin-bottom: 6px;"><i class="ph ph-chats"></i> Umpan Balik Atasan & Tim</div>
                        <div style="max-height: 120px; overflow-y: auto; margin-bottom: 8px; display: flex; flex-direction: column; gap: 4px;">
                            ${commentsHtml || '<div style="color: var(--text-muted); font-size: 11px; font-style: italic;">Belum ada umpan balik.</div>'}
                        </div>
                        <div style="display: flex; gap: 8px; align-items: center;">
                            <input type="text" placeholder="Tulis umpan balik..." id="reply-input-${task.id}" style="flex: 1; background: rgba(255,255,255,0.03); border: 1px solid var(--glass-border); border-radius: 4px; padding: 6px 10px; color: white; font-size: 12px; outline: none; font-family: inherit;">
                            <button class="primary-btn btn-send-task-comment" data-task-id="${task.id}" style="padding: 6px 12px; font-size: 11px; font-family: inherit; justify-content: center;"><i class="ph ph-paper-plane"></i></button>
                        </div>
                    </div>
                </div>
            `;
        });

        // Add listeners to reply buttons
        taskListContainer.querySelectorAll('.btn-send-task-comment').forEach(btn => {
            btn.onclick = () => {
                const taskId = btn.getAttribute('data-task-id');
                const replyInput = document.getElementById(`reply-input-${taskId}`);
                const commentText = replyInput ? replyInput.value.trim() : '';
                if (commentText) {
                    const newComment = {
                        id: 'comment-' + Date.now(),
                        taskId: taskId,
                        sender: currentUser.username,
                        senderName: currentUser.name,
                        text: commentText,
                        timestamp: Date.now()
                    };
                    if (!state.taskComments) state.taskComments = [];
                    state.taskComments.push(newComment);
                    updateState(state);
                    renderKPITasksView();
                }
            };
        });

        // Add listeners to task checklist toggle
        taskListContainer.querySelectorAll('.task-checkbox').forEach(cb => {
            cb.onchange = async () => {
                const taskId = cb.getAttribute('data-task-id');
                if (!cb.checked) return;
                try {
                    const formData = new FormData();
                    formData.append('_method', 'PUT');
                    formData.append('status', 'submitted_for_review');
                    const evidenceInput = taskListContainer.querySelector(`[data-task-evidence="${taskId}"]`);
                    if (evidenceInput?.files?.[0]) formData.append('evidence_attachment', evidenceInput.files[0]);
                    const result = await apiRequest(`/api/tasks/${taskId}/status`, {
                        method: 'POST',
                        body: formData
                    });
                    showPremiumNotice('Hasil Task Terkirim', result.message);
                    await syncDataFromServer();
                } catch (error) {
                    cb.checked = false;
                    showPremiumNotice('Tidak Dapat Mengirim Hasil', escapeHtml(error.message));
                }
            };
        });

        taskListContainer.querySelectorAll('[data-edit-task]').forEach(button => {
            button.onclick = () => {
                const task = state.tasks.find(item => Number(item.id) === Number(button.dataset.editTask));
                if (task) openQuickTaskModal(task.username, task);
            };
        });

        taskListContainer.querySelectorAll('.btn-verify-task').forEach(button => {
            button.onclick = async () => {
                try {
                    const result = await apiRequest(`/api/tasks/${button.dataset.taskId}/status`, {
                        method: 'PUT',
                        body: { status: 'verified', feedback: 'Hasil task telah diverifikasi.' }
                    });
                    showPremiumNotice('Task Terverifikasi', result.message);
                    await syncDataFromServer();
                } catch (error) {
                    showPremiumNotice('Tidak Dapat Memverifikasi', escapeHtml(error.message));
                }
            };
        });

        taskListContainer.querySelectorAll('.btn-revise-task').forEach(button => {
            button.onclick = () => {
                showTextInputDialog({
                    title: 'Catatan Revisi Task',
                    description: 'Berikan arahan yang spesifik agar staf dapat memperbaiki hasil task.',
                    label: 'Catatan revisi',
                    defaultValue: 'Mohon perbaiki hasil task sesuai arahan.',
                    submitText: 'Kirim Revisi'
                }, async feedback => {
                    try {
                        const result = await apiRequest(`/api/tasks/${button.dataset.taskId}/status`, {
                            method: 'PUT',
                            body: { status: 'revision_requested', feedback }
                        });
                        showPremiumNotice('Revisi Diminta', escapeHtml(result.message), { variant: 'success' });
                        await syncDataFromServer();
                    } catch (error) {
                        showPremiumNotice('Tidak Dapat Meminta Revisi', escapeHtml(error.message), { variant: 'danger' });
                    }
                });
            };
        });
    }

    // Task Creation Form Listener
    const createTaskForm = document.getElementById('create-task-form');
    if (createTaskForm) {
        createTaskForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (createTaskForm.dataset.submitting === 'true') return;
            createTaskForm.dataset.submitting = 'true';
            const titleInput = document.getElementById('task-title-input');
            const kpiSelect = document.getElementById('task-kpi-select');
            const assigneeSelect = document.getElementById('task-assignee-select');
            const attachmentInput = document.getElementById('task-attachment-input');
            
            if (!titleInput || !kpiSelect) return;
            
            const title = titleInput.value.trim();
            const kpiTarget = Number(kpiSelect.value || 0);
            const kpiName = kpiSelect.selectedOptions[0]?.dataset.name || '';
            const isManagerOrCeo = currentUser.role.startsWith('mgr_') || currentUser.role === 'ceo';
            const targetUsername = (assigneeSelect && isManagerOrCeo) ? assigneeSelect.value : currentUser.username;
            
            const managerApprover = currentUser.parent || 'ceo';

            if (title) {
                const deadline = addDaysJakarta(3);
                
                try {
                    const formData = new FormData();
                    formData.append('username', targetUsername);
                    formData.append('title', title);
                    formData.append('deadline', deadline);
                    if (kpiTarget) formData.append('kpi_id', String(kpiTarget));
                    formData.append('relation', kpiTarget ? kpiName : 'Tugas Mandiri');
                    if (attachmentInput?.files?.[0]) formData.append('attachment', attachmentInput.files[0]);
                    const data = await apiRequest('/api/tasks', {
                        method: 'POST',
                        body: formData
                    });
                    titleInput.value = '';
                    if (attachmentInput) attachmentInput.value = '';
                    if (isManagerOrCeo) {
                        showPremiumNotice('Task Berhasil Dibuat', escapeHtml(data.message));
                    } else {
                        showPremiumNotice('Pengajuan Task Terkirim', `${escapeHtml(data.message)} Menunggu keputusan @${escapeHtml(managerApprover)}.`);
                    }
                    await syncDataFromServer();
                } catch (err) {
                    showPremiumNotice('Task Tidak Dapat Dibuat', escapeHtml(err.message));
                } finally {
                    createTaskForm.dataset.submitting = 'false';
                }
            } else {
                showPremiumNotice('Judul Task Belum Diisi', 'Isi nama atau judul pekerjaan yang ingin diajukan.');
            }
        });
    }

    // Team Member select dropdown listener
    const teamSelectEl = document.getElementById('team-member-select');
    if (teamSelectEl) {
        teamSelectEl.onchange = () => {
            renderKPITaskList();
            renderKPITasksView();
        };
    }


    // ================= CEO Dashboard Performance Chart & Feedback Logic =================
    let ceoPerformanceChartInstance = null;

    function renderCEOPerformanceChart() {
        const canvas = document.getElementById('ceoEmployeePerformanceChart');
        const viewCeo = document.getElementById('view-ceo');
        if (!canvas || !viewCeo || viewCeo.style.display === 'none') return;
        
        const ctx = canvas.getContext('2d');
        if (!ctx) return;
        
        if (ceoPerformanceChartInstance) {
            ceoPerformanceChartInstance.destroy();
        }

        const labels = [];
        const scores = [];
        
        Object.values(state.users).forEach(u => {
            if (u.username !== 'ceo') {
                labels.push(u.name);
                scores.push(calculateUserKPI(u.username));
            }
        });

        ceoPerformanceChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Skor KPI (%)',
                    data: scores,
                    backgroundColor: 'rgba(52, 199, 89, 0.25)',
                    borderColor: 'var(--success)',
                    borderWidth: 2,
                    borderRadius: 6,
                    barThickness: 30
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: 'rgba(255,255,255,0.05)' },
                        ticks: { color: '#94A3B8' }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94A3B8' }
                    }
                }
            }
        });
    }

    function renderCEOComments() {
        const viewCeo = document.getElementById('view-ceo');
        const feedContainer = document.getElementById('ceo-comments-feed');
        if (!feedContainer || !viewCeo || viewCeo.style.display === 'none') return;
        feedContainer.innerHTML = '';

        const comments = [...(state.ceoComments || [])].reverse();
        if (comments.length === 0) {
            feedContainer.innerHTML = '<div style="color: var(--text-muted); font-size: 13px; font-style: italic;">Belum ada instruksi tertulis yang dikirim.</div>';
            return;
        }

        comments.forEach(c => {
            const targetUser = state.users[c.targetUsername] || { name: c.targetUsername };
            feedContainer.innerHTML += `
                <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); padding: 12px; border-radius: var(--radius-md); margin-bottom: 8px;">
                    <div style="display: flex; justify-content: space-between; font-size: 11px; color: var(--text-muted); margin-bottom: 6px;">
                        <span>Ditujukan ke: <b style="color: var(--primary);">${targetUser.name}</b></span>
                        <span>${new Date(c.timestamp).toLocaleString()}</span>
                    </div>
                    <div style="font-size: 13px; color: white; line-height: 1.5;">${c.text}</div>
                </div>
            `;
        });
    }

    const btnSubmitCeoComment = document.getElementById('btn-submit-ceo-comment');
    if (btnSubmitCeoComment) {
        btnSubmitCeoComment.onclick = () => {
            const targetSelect = document.getElementById('ceo-comment-target');
            const commentTextarea = document.getElementById('ceo-comment-text');
            
            if (!targetSelect || !commentTextarea) return;
            
            const targetUsername = targetSelect.value;
            const text = commentTextarea.value.trim();
            
            if (targetUsername && text) {
                const newComment = {
                    id: 'ceo-comment-' + Date.now(),
                    targetUsername: targetUsername,
                    text: text,
                    timestamp: Date.now()
                };
                
                if (!state.ceoComments) state.ceoComments = [];
                state.ceoComments.push(newComment);
                updateState(state);
                
                commentTextarea.value = '';
                showPremiumNotice('Catatan Terkirim', 'Catatan pengarahan CEO telah diteruskan kepada Manager.', { variant: 'success' });
            } else {
                showPremiumNotice('Komentar Masih Kosong', 'Tuliskan komentar sebelum mengirim.', { variant: 'danger' });
            }
        };
    }



    // ================= HRD Workspace Portal Renderer & Logic =================
    function renderHRDWorkspace() {
        const view = document.getElementById('view-hrd');
        if (!view || view.style.display === 'none') return;
        if (!currentUser) return;

        const employees = Object.values(state.users);
        document.getElementById('hr-stat-employees').innerText = employees.length;

        const todayStr = todayJakarta();
        const presentToday = state.attendance.filter(a => a.date === todayStr && state.users[a.username]).length;
        document.getElementById('hr-stat-present').innerText = presentToday;

        const pendingLeaves = (state.leaveRequests || []).filter(l => l.status === 'pending').length;
        document.getElementById('hr-stat-leave').innerText = pendingLeaves;

        const tableBody = document.getElementById('hr-employee-table-body');
        if (tableBody) {
            tableBody.innerHTML = '';
            const now = authoritativeNow();
            const year = now.getFullYear();
            const month = now.getMonth();
            const targetDays = getWorkingDaysInMonth(year, month);
            const targetHours = targetDays * 9;
            const monthPrefix = `${year}-${String(month + 1).padStart(2, '0')}`;

            employees.forEach(emp => {
                let actualHours = 0;
                state.attendance.forEach(a => {
                    if (a.username === emp.username && a.date.startsWith(monthPrefix)) {
                        if (a.status === 'Clocked Out' || a.timeOut) {
                            actualHours += calculateHoursWorked(a.time, a.timeOut);
                        }
                    }
                });
                
                let deficitOrSurplusHtml = '';
                if (actualHours >= targetHours) {
                    const surplus = actualHours - targetHours;
                    deficitOrSurplusHtml = `<span style="color: var(--success); font-weight: 700;">+${surplus.toFixed(1)} Jam (Kelebihan)</span>`;
                } else {
                    const deficit = targetHours - actualHours;
                    deficitOrSurplusHtml = `<span style="color: var(--danger); font-weight: 700;">-${deficit.toFixed(1)} Jam (Kekurangan)</span>`;
                }
                
                tableBody.innerHTML += `
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <td style="padding: 12px 8px;">
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <div class="member-avatar" style="width: 28px; height: 28px; font-size: 11px; border-radius: 50%; min-width: 28px; color:white; justify-content: center;">${emp.avatar}</div>
                                <b>${emp.name}</b>
                            </div>
                        </td>
                        <td style="padding: 12px 8px;">
                            <div>${emp.title}</div>
                            <div style="font-size: 11px; color: var(--text-secondary);">${emp.role}</div>
                        </td>
                        <td style="padding: 12px 8px;">${targetDays} Hari (${targetHours} Jam)</td>
                        <td style="padding: 12px 8px; font-weight: 600;">${actualHours.toFixed(1)} Jam</td>
                        <td style="padding: 12px 8px;">
                            ${deficitOrSurplusHtml}
                        </td>
                        <td style="padding: 12px 8px;">
                            <span class="badge" style="font-size: 10px;">${emp.level}</span>
                        </td>
                        <td style="padding: 12px 8px;">
                            <div style="display: flex; gap: 6px; align-items: center;">
                                <button class="primary-btn btn-hrd-edit" data-username="${emp.username}" style="padding: 4px 8px; font-size: 11px; background: rgba(255, 255, 255, 0.15); border-color: var(--glass-border); color: white; font-family: inherit;"><i class="ph ph-pencil-simple"></i> Edit Profil</button>
                                <button class="primary-btn btn-hrd-slip" data-username="${emp.username}" style="padding: 4px 8px; font-size: 11px; background: rgba(52, 199, 89, 0.15); border-color: var(--success); color: var(--success); font-family: inherit;"><i class="ph ph-receipt"></i> Slip Gaji</button>
                                <button class="primary-btn btn-hrd-paklaring" data-username="${emp.username}" style="padding: 4px 8px; font-size: 11px; background: rgba(242, 201, 76, 0.15); border-color: var(--warning); color: var(--warning); font-family: inherit;"><i class="ph ph-certificate"></i> Paklaring</button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            tableBody.querySelectorAll('.btn-hrd-edit').forEach(b => {
                b.onclick = () => {
                    const u = b.getAttribute('data-username');
                    openStaffEditModal(u);
                };
            });

            tableBody.querySelectorAll('.btn-hrd-slip').forEach(b => {
                b.onclick = () => {
                    const u = b.getAttribute('data-username');
                    openSalarySlipModal(u);
                };
            });

            tableBody.querySelectorAll('.btn-hrd-paklaring').forEach(b => {
                b.onclick = () => {
                    const u = b.getAttribute('data-username');
                    showPaklaringModal(u);
                };
            });
        }

        const logsContainer = document.getElementById('hr-attendance-logs');
        if (logsContainer) {
            logsContainer.innerHTML = '';
            const logs = [...state.attendance].filter(log => state.users[log.username]).reverse();
            if (logs.length === 0) {
                logsContainer.innerHTML = '<div style="color: var(--text-muted); font-size: 12px; font-style: italic;">Belum ada log absensi.</div>';
            } else {
                logs.forEach(log => {
                    const user = state.users[log.username] || { name: log.username, avatar: 'ST' };
                    let statusColor = 'var(--success)';
                    let statusText = 'On Time';
                    if (!log.is_active && log.timeOut) {
                        statusColor = 'var(--text-muted)';
                        statusText = `Clock out ${log.timeOut}`;
                    } else if (log.status === 'Late') {
                        statusColor = 'var(--warning)';
                        statusText = 'Terlambat';
                    }

                    const mapsLink = (log.lat && log.lng) ? `https://www.google.com/maps/search/?api=1&query=${log.lat},${log.lng}` : null;
                    const mapsHtml = mapsLink ? `<a href="${mapsLink}" target="_blank" style="background: var(--primary); color: #020617; padding: 4px 8px; border-radius: 4px; font-weight: 700; text-decoration: none; font-size: 10px; display: inline-flex; align-items: center; gap: 4px; margin-left: 8px;"><i class="ph-fill ph-map-pin"></i> BUKA PETA</a>` : '';

                    logsContainer.innerHTML += `
                        <div style="background: rgba(255,255,255,0.02); border: 1px solid var(--glass-border); border-radius: var(--radius-md); padding: 12px; display: flex; gap: 12px; align-items: center; width: 100%;">
                            <div class="member-avatar" style="width: 32px; height: 32px; font-size: 12px; border-radius: 50%; min-width: 32px; color:white; justify-content: center;">${user.avatar}</div>
                            <div style="flex: 1;">
                                <div style="font-size: 13px; font-weight: 600; color: white;">${user.name}</div>
                                <div style="font-size: 11px; color: var(--text-secondary); display: flex; gap: 8px; margin-top: 2px; align-items: center; flex-wrap: wrap;">
                                    <span>Tanggal: ${log.date}</span>
                                    <span>In: ${log.time}</span>
                                    <span style="color: ${statusColor};">${statusText}</span>
                                    <span>Tipe: ${log.type}</span>
                                    ${mapsHtml}
                                </div>
                            </div>
                        </div>
                    `;
                });
            }
        }

        // Render Working Calendar overrides checklist
        const overrideListContainer = document.getElementById('hr-calendar-override-list');
        if (overrideListContainer) {
            overrideListContainer.innerHTML = '';
            
            const now = new Date();
            const year = now.getFullYear();
            const month = now.getMonth();
            
            const standardHolidays = [
                { date: `${year}-01-01`, name: "Tahun Baru Masehi" },
                { date: `${year}-05-01`, name: "Hari Buruh" },
                { date: `${year}-06-01`, name: "Hari Lahir Pancasila" },
                { date: `${year}-08-17`, name: "Hari Kemerdekaan RI" },
                { date: `${year}-12-25`, name: "Hari Raya Natal" }
            ];

            const totalDays = new Date(year, month + 1, 0).getDate();
            const monthStr = String(month + 1).padStart(2, '0');
            for (let d = 1; d <= totalDays; d++) {
                const dayDate = new Date(year, month, d);
                const dayOfWeek = dayDate.getDay();
                if (dayOfWeek === 0 || dayOfWeek === 6) {
                    const dayStr = String(d).padStart(2, '0');
                    const fullDateKey = `${year}-${monthStr}-${dayStr}`;
                    const dayName = dayOfWeek === 0 ? "Minggu" : "Sabtu";
                    if (!standardHolidays.some(h => h.date === fullDateKey)) {
                        standardHolidays.push({ date: fullDateKey, name: `Akhir Pekan (${dayName} ${d} ${dayDate.toLocaleString('id-ID', { month: 'short' })})` });
                    }
                }
            }

            standardHolidays.sort((a,b) => new Date(a.date) - new Date(b.date));

            standardHolidays.forEach(h => {
                const isChecked = state.calendarOverrides && state.calendarOverrides.includes(h.date) ? 'checked' : '';
                overrideListContainer.innerHTML += `
                    <div style="display: flex; justify-content: space-between; align-items: center; background: rgba(255,255,255,0.02); padding: 8px 12px; border-radius: 6px; border: 1px solid var(--glass-border); margin-bottom: 6px;">
                        <div>
                            <div style="font-size: 12px; font-weight: 600; color: white;">${h.name}</div>
                            <div style="font-size: 10px; color: var(--text-secondary);">${h.date}</div>
                        </div>
                        <label style="display: flex; align-items: center; cursor: pointer; gap: 8px;">
                            <input type="checkbox" class="calendar-override-checkbox" data-date="${h.date}" ${isChecked} style="cursor: pointer;">
                            <span style="font-size: 10px; color: ${isChecked ? 'var(--success)' : 'var(--danger)'};">${isChecked ? 'Hari Kerja' : 'Libur'}</span>
                        </label>
                    </div>
                `;
            });

            overrideListContainer.querySelectorAll('.calendar-override-checkbox').forEach(cb => {
                cb.onchange = () => {
                    const dateVal = cb.getAttribute('data-date');
                    if (!state.calendarOverrides) state.calendarOverrides = [];
                    if (cb.checked) {
                        if (!state.calendarOverrides.includes(dateVal)) {
                            state.calendarOverrides.push(dateVal);
                        }
                    } else {
                        state.calendarOverrides = state.calendarOverrides.filter(d => d !== dateVal);
                    }
                    updateState(state);
                    renderHRDWorkspace();
                    renderKPITasksView(); 
                };
            });
        }

        const btnExportHrdCsv = document.getElementById('hr-btn-export-attendance-csv');
        if (btnExportHrdCsv) {
            btnExportHrdCsv.onclick = exportAllStaffAttendanceCSV;
        }

        const btnAddCalendarOverride = document.getElementById('btn-add-calendar-override');
        if (btnAddCalendarOverride) {
            btnAddCalendarOverride.onclick = () => {
                const dateInput = document.getElementById('hr-calendar-date-input');
                if (!dateInput || !dateInput.value) {
                    showPremiumNotice("Peringatan", "Pilih tanggal terlebih dahulu!");
                    return;
                }
                const selectedDate = dateInput.value;
                if (!state.calendarOverrides) state.calendarOverrides = [];
                if (!state.calendarOverrides.includes(selectedDate)) {
                    state.calendarOverrides.push(selectedDate);
                    updateState(state);
                    renderHRDWorkspace();
                    renderKPITasksView();
                    showPremiumNotice("Sukses Kalender", `Tanggal ${selectedDate} telah ditetapkan sebagai Hari Kerja Wajib (WIB).`);
                } else {
                    showPremiumNotice("Info Kalender", `Tanggal ${selectedDate} sudah terdaftar sebagai Hari Kerja Wajib.`);
                }
            };
        }

        // Render D-Point User Selection options
        const dpointUserSelect = document.getElementById('hr-dpoint-user-select');
        if (dpointUserSelect && dpointUserSelect.children.length <= 1) {
            dpointUserSelect.innerHTML = '<option value="">-- Pilih Karyawan --</option>';
            employees.forEach(emp => {
                if (emp.role !== 'ceo') {
                    dpointUserSelect.innerHTML += `<option value="${emp.username}">${emp.name} (@${emp.username})</option>`;
                }
            });
        }

        // Bind D-Point Config Form submission
        const dpointForm = document.getElementById('hr-dpoint-form');
        if (dpointForm) {
            dpointForm.onsubmit = (e) => {
                e.preventDefault();
                const username = document.getElementById('hr-dpoint-user-select').value;
                const rate = document.getElementById('hr-dpoint-value-input').value;
                
                if (!username) {
                    showPremiumNotice('Karyawan Belum Dipilih', 'Pilih karyawan sebelum menetapkan tarif D-Point.', { variant: 'danger' });
                    return;
                }
                if (!rate || rate <= 0) {
                    showPremiumNotice('Nominal Tidak Valid', 'Isi tarif D-Point dengan nilai lebih dari nol.', { variant: 'danger' });
                    return;
                }
                
                state.dPointRates[username] = parseInt(rate);
                updateState(state);
                showPremiumNotice('Tarif D-Point Diperbarui', `Tarif untuk @${escapeHtml(username)} ditetapkan menjadi Rp ${parseInt(rate).toLocaleString('id-ID')} per presensi.`, { variant: 'success' });
                renderHRDWorkspace();
                renderAttendanceLogs();
            };
        }
    }

    const hrBtnAddStaff = document.getElementById('hr-btn-add-staff');
    if (hrBtnAddStaff) {
        hrBtnAddStaff.onclick = () => {
            if (btnAddStaff) btnAddStaff.click();
        };
    }

    // CEO Hierarchy Actions Button Listeners
    const btnHierarchyAddDiv = document.getElementById('btn-hierarchy-add-div');
    if (btnHierarchyAddDiv) {
        btnHierarchyAddDiv.onclick = () => {
            const divisionModal = document.getElementById('division-modal');
            if (divisionModal) divisionModal.style.display = 'flex';
        };
    }

    const btnHierarchyAddMgr = document.getElementById('btn-hierarchy-add-mgr');
    if (btnHierarchyAddMgr) {
        btnHierarchyAddMgr.onclick = () => {
            const appointModal = document.getElementById('appoint-modal');
            if (appointModal) {
                const appointStaffSelect = document.getElementById('appoint-staff-select');
                if (appointStaffSelect) {
                    appointStaffSelect.innerHTML = '';
                    Object.values(state.users).forEach(u => {
                        if (u.level.includes('Staff') && u.role !== 'ceo') {
                            appointStaffSelect.innerHTML += `<option value="${u.username}">${u.name} (@${u.username}) - ${u.title}</option>`;
                        }
                    });
                    if (appointStaffSelect.children.length === 0) {
                        appointStaffSelect.innerHTML = '<option value="">Tidak ada staf yang tersedia</option>';
                    }
                }
                appointModal.style.display = 'flex';
            }
        };
    }

    // ================= Revisions State Initializations =================
    if (!state.taskComments) {
        state.taskComments = [];
    }
    if (!state.ceoComments) {
        state.ceoComments = [];
    }

    // ================= Modals Dismiss Window Click Update =================
    const originalWindowClick = window.onclick;
    window.onclick = (e) => {
        if (typeof originalWindowClick === 'function') {
            originalWindowClick(e);
        }
        const confirmModal = document.getElementById('confirm-modal');
        const leadDetailModalEl = document.getElementById('lead-detail-modal');
        const userRegModalEl = document.getElementById('user-registration-modal');
        const divisionModalEl = document.getElementById('division-modal');
        const appointModalEl = document.getElementById('appoint-modal');
        const quickTaskModalEl = document.getElementById('quick-create-task-modal');
        if (e.target === confirmModal) confirmModal.style.display = 'none';
        if (e.target === leadDetailModalEl) leadDetailModalEl.style.display = 'none';
        if (e.target === userRegModalEl) userRegModalEl.style.display = 'none';
        if (e.target === divisionModalEl) divisionModalEl.style.display = 'none';
        if (e.target === appointModalEl) appointModalEl.style.display = 'none';
        if (e.target === quickTaskModalEl) quickTaskModalEl.style.display = 'none';
    };

    // ================= Universal Quick Task Creation Logic (All Roles & Managers) =================
    function openQuickTaskModal(defaultTargetUser, taskToEdit = null) {
        const modal = document.getElementById('quick-create-task-modal');
        const assigneeSelect = document.getElementById('quick-task-assignee-select');
        const kpiSelect = document.getElementById('quick-task-kpi-select');
        const titleInput = document.getElementById('quick-task-title-input');
        const deadlineInput = document.getElementById('quick-task-deadline-input');
        const attachmentInput = document.getElementById('quick-task-attachment-input');
        
        if (!modal || !assigneeSelect || !kpiSelect) return;
        
        if (!currentUser) {
            showPremiumNotice("Akses Ditolak", "Silakan login terlebih dahulu.");
            return;
        }

        const isManager = currentUser.role && currentUser.role.startsWith('mgr_');
        const isCeo = currentUser.role === 'ceo';

        // Populate Assignee Select
        assigneeSelect.innerHTML = `<option value="${currentUser.username}">Diri Sendiri (${currentUser.name})</option>`;
        if (isManager || isCeo) {
            Object.values(state.users || {}).forEach(u => {
                let isTim = false;
                if (isCeo && u.username !== 'ceo') isTim = true;
                else if (isManager) {
                    if (u.parent === currentUser.username) isTim = true;
                    else if (currentUser.role === 'mgr_marketing' && u.role === 'staff_marketing') isTim = true;
                    else if (currentUser.role === 'mgr_ops' && u.role === 'staff_ops') isTim = true;
                    else if (currentUser.role === 'mgr_finance' && u.role === 'staff_finance') isTim = true;
                    else if (currentUser.role === 'mgr_hrd' && u.role === 'staff_hrd') isTim = true;
                }
                if (isTim && u.username !== currentUser.username) {
                    assigneeSelect.innerHTML += `<option value="${u.username}">${u.name} (${u.title})</option>`;
                }
            });
        }

        if (defaultTargetUser && Array.from(assigneeSelect.options).some(o => o.value === defaultTargetUser)) {
            assigneeSelect.value = defaultTargetUser;
        } else {
            assigneeSelect.value = currentUser.username;
        }

        function populateQuickKPISelect(targetUser) {
            kpiSelect.innerHTML = '<option value="" data-name="Tugas Mandiri">Task mandiri â€” tanpa KPI</option>';
            const effectiveUser = targetUser || currentUser.username;
            const targetUserObj = (state.users && state.users[effectiveUser]) || {};
            const targetRole = targetUserObj.role || effectiveUser || '';
            const targetKPIs = (state.kpiConfig && (state.kpiConfig[effectiveUser] || state.kpiConfig[targetRole])) || [];

            if (targetKPIs && targetKPIs.length > 0) {
                targetKPIs.forEach(k => {
                    kpiSelect.innerHTML += `<option value="${k.id}" data-name="${escapeHtml(k.name)}">${escapeHtml(k.goalTitle || 'Goal Divisi')} â€” ${escapeHtml(k.name)} (${Number(k.weight || 0)}%)</option>`;
                });
            }

            if (!targetKPIs || targetKPIs.length === 0) {
                kpiSelect.innerHTML += '<option value="" disabled>Belum ada KPI divisi yang disahkan CEO</option>';
            }
        }

        populateQuickKPISelect(assigneeSelect.value);
        assigneeSelect.onchange = () => {
            populateQuickKPISelect(assigneeSelect.value);
        };

        if (titleInput) titleInput.value = taskToEdit?.title || '';
        if (attachmentInput) attachmentInput.value = '';
        if (deadlineInput) {
            const d3 = addDaysJakarta(3);
            deadlineInput.value = taskToEdit?.deadline
                ? String(taskToEdit.deadline).slice(0, 10)
                : d3;
        }
        if (taskToEdit?.kpi_id && Array.from(kpiSelect.options).some(option => Number(option.value) === Number(taskToEdit.kpi_id))) {
            kpiSelect.value = String(taskToEdit.kpi_id);
        }
        const quickForm = document.getElementById('quick-create-task-form');
        if (quickForm) {
            if (taskToEdit?.id) quickForm.dataset.editTaskId = String(taskToEdit.id);
            else delete quickForm.dataset.editTaskId;
        }

        modal.style.display = 'flex';
    }

    const btnAssignTaskToTeam = document.getElementById('btn-manager-assign-task-to-team');
    if (btnAssignTaskToTeam) {
        btnAssignTaskToTeam.onclick = () => {
            const teamSelect = document.getElementById('team-member-select');
            const selUser = teamSelect ? teamSelect.value : null;
            openQuickTaskModal(selUser);
        };
    }

    const btnCloseQuickTask = document.getElementById('btn-close-quick-task-modal');
    const btnCancelQuickTask = document.getElementById('btn-cancel-quick-task');
    const quickTaskModal = document.getElementById('quick-create-task-modal');

    if (btnCloseQuickTask && quickTaskModal) {
        btnCloseQuickTask.onclick = () => quickTaskModal.style.display = 'none';
    }
    if (btnCancelQuickTask && quickTaskModal) {
        btnCancelQuickTask.onclick = () => quickTaskModal.style.display = 'none';
    }

    const quickTaskForm = document.getElementById('quick-create-task-form');
    if (quickTaskForm) {
        quickTaskForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const titleInput = document.getElementById('quick-task-title-input');
            const kpiSelect = document.getElementById('quick-task-kpi-select');
            const assigneeSelect = document.getElementById('quick-task-assignee-select');
            const deadlineInput = document.getElementById('quick-task-deadline-input');
            const attachmentInput = document.getElementById('quick-task-attachment-input');

            if (!titleInput || !kpiSelect || !assigneeSelect) return;

            const title = titleInput.value.trim();
            const kpiTarget = Number(kpiSelect.value);
            const selectedKpiOption = kpiSelect.options[kpiSelect.selectedIndex];
            const kpiName = selectedKpiOption?.dataset.name || selectedKpiOption?.textContent || '';
            const targetUsername = assigneeSelect.value || currentUser.username;
            const deadline = (deadlineInput && deadlineInput.value) ? deadlineInput.value : addDaysJakarta(3);

            if (title) {
                try {
                    const formData = new FormData();
                    formData.append('username', targetUsername);
                    formData.append('title', title);
                    formData.append('deadline', deadline);
                    if (kpiTarget) formData.append('kpi_id', String(kpiTarget));
                    formData.append('relation', kpiTarget ? kpiName : 'Tugas Mandiri');
                    if (attachmentInput?.files?.[0]) formData.append('attachment', attachmentInput.files[0]);
                    const editingId = Number(quickTaskForm.dataset.editTaskId || 0);
                    if (editingId) formData.append('_method', 'PUT');
                    const data = await apiRequest(editingId ? `/api/tasks/${editingId}` : '/api/tasks', {
                        method: 'POST',
                        body: formData
                    });

                    quickTaskModal.style.display = 'none';
                    const targetUser = (state.users && state.users[targetUsername]) || { name: targetUsername };
                    if (data.task?.status === 'pending_manager') {
                        showPremiumNotice('Task diajukan', `Task "${title}" telah dikirim kepada manager untuk ditinjau.`);
                    } else if (targetUsername === currentUser.username) {
                        showPremiumNotice('Sukses membuat task', `Task "${title}" berhasil ditambahkan ke daftar tugas Anda.`);
                    } else {
                        showPremiumNotice('Penugasan tim sukses', `Task "${title}" berhasil ditugaskan kepada ${targetUser.name}.`);
                    }
                    await syncDataFromServer();
                } catch (err) {
                    showPremiumNotice('Gagal membuat task', err.message || 'Server tidak dapat memproses permintaan.');
                }
            } else {
                showPremiumNotice('Judul Task Belum Diisi', 'Isi nama atau judul pekerjaan yang ingin diajukan.');
            }
        });
    }

    // ================= Finance Data Transfer Klien Engine =================
    async function renderFinanceDashboard(monthFilter) {
        const tbody = document.getElementById('finance-inflow-table-body');
        if (!tbody) return;

        let url = '/api/client-inflows';
        if (monthFilter) {
            url += `?month=${monthFilter}`;
        }

        try {
            const res = await fetch(url);
            if (!res.ok) return;
            const resData = await res.json();
            if (!resData.success) return;

            const inflows = resData.data || [];
            const summary = resData.summary || {};

            const formatRp = (num) => 'Rp ' + Math.round(num || 0).toLocaleString('id-ID');

            document.getElementById('summary-total-inflow').innerText = formatRp(summary.total_inflow);
            document.getElementById('summary-total-outstanding').innerText = formatRp(summary.total_outstanding);
            document.getElementById('summary-total-project-value').innerText = formatRp(summary.total_project_value);
            document.getElementById('summary-new-clients-count').innerText = `${summary.new_clients_count || 0} Klien`;

            if (inflows.length === 0) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="15" style="text-align: center; padding: 32px 16px;">
                            <div style="background: rgba(255,255,255,0.02); border: 1px dashed var(--glass-border); border-radius: var(--radius-md); padding: 24px;">
                                <i class="ph ph-receipt" style="font-size: 36px; color: var(--text-muted); margin-bottom: 8px;"></i>
                                <h4 style="margin: 0 0 6px 0; color: white; font-size: 14px;">Belum Ada Data Transfer Pemasukan</h4>
                                <p style="margin: 0; font-size: 12px; color: var(--text-secondary);">Klik tombol '+ Input Pemasukan Klien' di atas untuk mencatat transaksi transfer baru.</p>
                            </div>
                        </td>
                    </tr>
                `;
                return;
            }

            let html = '';
            inflows.forEach((item, index) => {
                const isLunas = item.payment_status === 'LUNAS';
                const statusBadge = isLunas
                    ? `<span style="background: rgba(52, 199, 89, 0.15); color: var(--success); border: 1px solid rgba(52, 199, 89, 0.3); padding: 3px 8px; border-radius: 4px; font-weight: 700; font-size: 10px;">LUNAS</span>`
                    : `<span style="background: rgba(255, 159, 10, 0.15); color: var(--warning); border: 1px solid rgba(255, 159, 10, 0.3); padding: 3px 8px; border-radius: 700; font-size: 10px;">Belum Lunas</span>`;

                const formattedDate = item.date ? new Date(item.date).toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' }) : '-';

                html += `
                    <tr style="border-bottom: 1px solid rgba(255,255,255,0.05); transition: background 0.2s;" onmouseenter="this.style.background='rgba(255,255,255,0.02)'" onmouseleave="this.style.background='transparent'">
                        <td style="padding: 10px 8px; color: var(--text-muted);">${index + 1}</td>
                        <td style="padding: 10px 8px; white-space: nowrap; font-weight: 500;">${formattedDate}</td>
                        <td style="padding: 10px 8px; font-weight: 600; color: white;">${item.client_name}</td>
                        <td style="padding: 10px 8px;"><span style="background: rgba(255,255,255,0.06); padding: 2px 6px; border-radius: 4px; font-size: 11px;">${item.package}</span></td>
                        <td style="padding: 10px 8px; text-align: right; font-weight: 700; color: var(--success);">${formatRp(item.payment_amount)}</td>
                        <td style="padding: 10px 8px; text-align: right; font-weight: 600; color: ${item.remaining_balance > 0 ? 'var(--warning)' : 'var(--text-muted)'};">${formatRp(item.remaining_balance)}</td>
                        <td style="padding: 10px 8px; text-align: center;">${statusBadge}</td>
                        <td style="padding: 10px 8px; text-align: center;">
                            <div style="display: flex; gap: 6px; justify-content: center;">
                                <button type="button" class="btn-detail-inflow" data-id="${item.id}" style="background: rgba(175, 82, 222, 0.15); color: #af52de; border: 1px solid rgba(175, 82, 222, 0.3); border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 11px;" title="Detail"><i class="ph ph-eye"></i></button>
                                <button type="button" class="btn-edit-inflow" data-id="${item.id}" style="background: rgba(10, 132, 255, 0.15); color: var(--info); border: 1px solid rgba(10, 132, 255, 0.3); border-radius: 4px; padding: 4px 8px; cursor: pointer; font-size: 11px;" title="Edit"><i class="ph ph-pencil"></i></button>
                                <button type="button" class="erp-delete-btn icon-only" data-erp-delete
                                    data-resource-type="client_inflow"
                                    data-resource-id="${item.id}"
                                    data-resource-label="Pemasukan klien ${escapeHtml(item.client_name)}"
                                    title="Ajukan pembalikan dan penghapusan pemasukan">
                                    <i class="ph ph-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;

            document.querySelectorAll('.btn-detail-inflow').forEach(btn => {
                btn.onclick = () => {
                    const id = btn.getAttribute('data-id');
                    const targetItem = inflows.find(i => String(i.id) === String(id));
                    if (targetItem) openInflowDetailModal(targetItem);
                };
            });

            document.querySelectorAll('.btn-edit-inflow').forEach(btn => {
                btn.onclick = () => {
                    const id = btn.getAttribute('data-id');
                    const targetItem = inflows.find(i => String(i.id) === String(id));
                    if (targetItem) openInflowModal(targetItem);
                };
            });

        } catch (err) {
            console.error("Error loading finance inflows:", err);
        }
    }

    function openInflowModal(editData = null) {
        const modal = document.getElementById('inflow-modal');
        const form = document.getElementById('inflow-form');
        const modalTitle = document.getElementById('inflow-modal-title');
        if (!modal || !form) return;

        if (editData) {
            modalTitle.innerHTML = `<i class="ph ph-pencil" style="color: var(--info);"></i> Edit Pemasukan Klien`;
            document.getElementById('inflow-id').value = editData.id;
            document.getElementById('inflow-date').value = editData.date || '';
            document.getElementById('inflow-client-no').value = editData.client_no || '';
            document.getElementById('inflow-client-name').value = editData.client_name || '';
            document.getElementById('inflow-domicile').value = editData.domicile || '';
            document.getElementById('inflow-start-project').value = editData.start_project || 'Jan';
            document.getElementById('inflow-package').value = editData.package || 'Bronze';
            document.getElementById('inflow-pj-survey').value = editData.pj_survey || '';
            document.getElementById('inflow-project-value').value = editData.project_value || '';
            document.getElementById('inflow-payment-amount').value = editData.payment_amount || '';
            document.getElementById('inflow-termin-no').value = editData.termin_no || '1';
            document.getElementById('inflow-total-termin').value = editData.total_termin || '3';
            document.getElementById('inflow-notes').value = editData.notes || '';
        } else {
            modalTitle.innerHTML = `<i class="ph ph-bank" style="color: var(--primary);"></i> Input Pemasukan Klien Baru`;
            form.reset();
            document.getElementById('inflow-id').value = '';
            document.getElementById('inflow-date').value = todayJakarta();
        }

        modal.style.display = 'flex';
    }

    function openInflowDetailModal(item) {
        const modal = document.getElementById('inflow-detail-modal');
        const content = document.getElementById('inflow-detail-content');
        if (!modal || !content) return;

        const formatRp = (num) => 'Rp ' + Math.round(num || 0).toLocaleString('id-ID');
        const formattedDate = item.date ? new Date(item.date).toLocaleDateString('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }) : '-';
        
        content.innerHTML = `
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                <div><span style="color: var(--text-secondary);">Nama Klien:</span><br><strong style="font-size: 14px;">${item.client_name}</strong></div>
                <div><span style="color: var(--text-secondary);">No. Klien:</span><br><strong>${item.client_no || '-'}</strong></div>
                <div><span style="color: var(--text-secondary);">Domisili:</span><br><strong>${item.domicile || '-'}</strong></div>
                <div><span style="color: var(--text-secondary);">Start Project:</span><br><strong>${item.start_project || '-'}</strong></div>
                <div><span style="color: var(--text-secondary);">Paket:</span><br><strong>${item.package || '-'}</strong></div>
                <div><span style="color: var(--text-secondary);">PJ Survey:</span><br><strong>${item.pj_survey || '-'}</strong></div>
                
                <div style="grid-column: 1 / -1; border-top: 1px dashed var(--glass-border); margin: 8px 0;"></div>
                
                <div><span style="color: var(--text-secondary);">Tanggal Transfer:</span><br><strong>${formattedDate}</strong></div>
                <div><span style="color: var(--text-secondary);">Nilai Kontrak:</span><br><strong>${formatRp(item.project_value)}</strong></div>
                <div><span style="color: var(--text-secondary);">Pembayaran (Termin ${item.termin_no}/${item.total_termin}):</span><br><strong style="color: var(--success); font-size: 14px;">${formatRp(item.payment_amount)}</strong></div>
                <div><span style="color: var(--text-secondary);">Sisa Tagihan:</span><br><strong style="color: ${item.remaining_balance > 0 ? 'var(--warning)' : 'var(--text-muted)'}">${formatRp(item.remaining_balance)}</strong></div>
                
                <div style="grid-column: 1 / -1; border-top: 1px dashed var(--glass-border); margin: 8px 0;"></div>
                
                <div style="grid-column: 1 / -1;"><span style="color: var(--text-secondary);">Catatan Tambahan:</span><br><div style="background: rgba(255,255,255,0.02); padding: 12px; border-radius: 6px; margin-top: 6px; border: 1px solid var(--glass-border);">${item.notes || '<em>Tidak ada catatan</em>'}</div></div>
            </div>
        `;
        
        modal.style.display = 'flex';
    }

    const btnCloseInflowDetail = document.getElementById('btn-close-inflow-detail');
    if (btnCloseInflowDetail) {
        btnCloseInflowDetail.onclick = () => {
            document.getElementById('inflow-detail-modal').style.display = 'none';
        };
    }

    // Bind Inflow Action Controls
    const btnOpenInflowModal = document.getElementById('btn-open-inflow-modal');
    if (btnOpenInflowModal) {
        btnOpenInflowModal.onclick = () => openInflowModal();
    }

    const btnCloseInflowModal = document.getElementById('btn-close-inflow-modal');
    const btnCancelInflowModal = document.getElementById('btn-cancel-inflow-modal');
    const inflowModal = document.getElementById('inflow-modal');

    if (btnCloseInflowModal && inflowModal) {
        btnCloseInflowModal.onclick = () => inflowModal.style.display = 'none';
    }
    if (btnCancelInflowModal && inflowModal) {
        btnCancelInflowModal.onclick = () => inflowModal.style.display = 'none';
    }

    const inflowMonthFilter = document.getElementById('inflow-month-filter');
    if (inflowMonthFilter) {
        inflowMonthFilter.onchange = () => {
            renderFinanceDashboard(inflowMonthFilter.value);
        };
    }

    const btnExportInflowCsv = document.getElementById('btn-export-inflow-csv');
    if (btnExportInflowCsv) {
        btnExportInflowCsv.onclick = () => {
            const m = inflowMonthFilter ? inflowMonthFilter.value : '';
            window.location.href = `/api/client-inflows/export-csv${m ? '?month=' + m : ''}`;
        };
    }

    const inflowForm = document.getElementById('inflow-form');
    if (inflowForm) {
        inflowForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const id = document.getElementById('inflow-id').value;
            const payload = {
                date: document.getElementById('inflow-date').value,
                client_name: document.getElementById('inflow-client-name').value.trim(),
                domicile: document.getElementById('inflow-domicile').value.trim(),
                client_no: document.getElementById('inflow-client-no').value.trim(),
                start_project: document.getElementById('inflow-start-project').value,
                package: document.getElementById('inflow-package').value,
                notes: document.getElementById('inflow-notes').value.trim(),
                project_value: parseFloat(document.getElementById('inflow-project-value').value) || 0,
                termin_no: document.getElementById('inflow-termin-no').value,
                total_termin: document.getElementById('inflow-total-termin').value,
                payment_amount: parseFloat(document.getElementById('inflow-payment-amount').value) || 0,
                pj_survey: document.getElementById('inflow-pj-survey').value.trim(),
            };

            const url = id ? `/api/client-inflows/${id}` : '/api/client-inflows';
            const method = id ? 'PUT' : 'POST';

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    inflowModal.style.display = 'none';
                    showPremiumNotice("Sukses Simpan", id ? "Data pemasukan klien berhasil diperbarui." : "Pemasukan klien baru berhasil dicatat.");
                    const filter = inflowMonthFilter ? inflowMonthFilter.value : '';
                    renderFinanceDashboard(filter);
                } else {
                    const err = await res.json();
                    showPremiumNotice("Gagal Simpan", err.message || "Periksa kembali data input.");
                }
            } catch (err) {
                console.error(err);
            }
        });
    }

    const inlineInflowForm = document.getElementById('inline-inflow-form');
    if (inlineInflowForm) {
        const dateInput = document.getElementById('inline-inflow-date');
        if (dateInput && !dateInput.value) {
            dateInput.value = todayJakarta();
        }

        inlineInflowForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            const payload = {
                date: document.getElementById('inline-inflow-date').value,
                client_name: document.getElementById('inline-inflow-client-name').value.trim(),
                domicile: document.getElementById('inline-inflow-domicile').value.trim(),
                client_no: document.getElementById('inline-inflow-client-no').value.trim(),
                start_project: document.getElementById('inline-inflow-start-project').value,
                package: document.getElementById('inline-inflow-package').value,
                notes: document.getElementById('inline-inflow-notes').value.trim(),
                project_value: parseFloat(document.getElementById('inline-inflow-project-value').value) || 0,
                termin_no: document.getElementById('inline-inflow-termin-no').value,
                total_termin: document.getElementById('inline-inflow-total-termin').value,
                payment_amount: parseFloat(document.getElementById('inline-inflow-payment-amount').value) || 0,
                pj_survey: document.getElementById('inline-inflow-pj-survey').value.trim(),
            };

            try {
                const res = await fetch('/api/client-inflows', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    inlineInflowForm.reset();
                    if (dateInput) dateInput.value = todayJakarta();
                    showPremiumNotice("Sukses Simpan", "Pemasukan klien baru berhasil dicatat.");
                    const filter = document.getElementById('inflow-month-filter')?.value;
                    renderFinanceDashboard(filter);
                } else {
                    const err = await res.json();
                    showPremiumNotice("Gagal Simpan", err.message || "Periksa kembali data input.");
                }
            } catch (err) {
                console.error(err);
            }
        });
    }

    // Import Spreadsheet Modal & Auto-Metrics Detector Handler
    const btnOpenImportModal = document.getElementById('btn-open-import-inflow-modal');
    const importModal = document.getElementById('import-inflow-modal');
    const btnCloseImportModal = document.getElementById('btn-close-import-inflow-modal');
    const btnCancelImport = document.getElementById('btn-cancel-import-inflow');
    const importFileInput = document.getElementById('import-file-input');
    const importFilenameDisplay = document.getElementById('import-filename-display');

    if (btnOpenImportModal && importModal) {
        btnOpenImportModal.onclick = () => importModal.style.display = 'flex';
    }
    if (btnCloseImportModal && importModal) {
        btnCloseImportModal.onclick = () => importModal.style.display = 'none';
    }
    if (btnCancelImport && importModal) {
        btnCancelImport.onclick = () => importModal.style.display = 'none';
    }

    if (importFileInput && importFilenameDisplay) {
        importFileInput.onchange = () => {
            if (importFileInput.files && importFileInput.files[0]) {
                importFilenameDisplay.innerText = `File terpilih: ${importFileInput.files[0].name}`;
                importFilenameDisplay.style.color = 'var(--info)';
                importFilenameDisplay.style.fontWeight = 'bold';
            }
        };
    }

    const importInflowForm = document.getElementById('import-inflow-form');
    if (importInflowForm) {
        importInflowForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!importFileInput || !importFileInput.files || !importFileInput.files[0]) {
                showPremiumNotice("Pilih File", "Silakan pilih file spreadsheet (.csv) terlebih dahulu.");
                return;
            }

            const formData = new FormData();
            formData.append('file', importFileInput.files[0]);

            try {
                const res = await fetch('/api/client-inflows/import-csv', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                    },
                    body: formData
                });

                if (res.ok) {
                    const data = await res.json();
                    importModal.style.display = 'none';
                    importInflowForm.reset();
                    if (importFilenameDisplay) importFilenameDisplay.innerText = "Format yang didukung: CSV (Sesuai Struktur Google Sheets Data Transfer Klien)";
                    showPremiumNotice("Import & Deteksi Berhasil", data.message || "Seluruh data transaksi dan metriks berhasil diimpor.");
                    const filter = document.getElementById('inflow-month-filter')?.value;
                    renderFinanceDashboard(filter);
                } else {
                    const err = await res.json();
                    showPremiumNotice("Gagal Import", err.message || "File CSV tidak sesuai format.");
                }
            } catch (err) {
                console.error(err);
            }
        });
    }

    // Wizard Form Logic for Inline Inflow Form
    const wizardSteps = document.querySelectorAll('#inline-inflow-form .wizard-step');
    if (wizardSteps.length > 0) {
        const updateWizardProgressBar = (step) => {
            [1, 2, 3].forEach(s => {
                const el = document.getElementById(`wiz-progress-${s}`);
                if (!el) return;
                const badge = el.querySelector('span:first-child');
                if (s === step) {
                    el.classList.add('active');
                    el.style.color = 'var(--primary)';
                    el.style.background = 'rgba(242, 201, 76, 0.12)';
                    el.style.border = '1px solid rgba(242, 201, 76, 0.3)';
                    if (badge) {
                        badge.style.background = 'var(--primary)';
                        badge.style.color = '#000';
                    }
                } else if (s < step) {
                    el.classList.remove('active');
                    el.style.color = 'var(--success)';
                    el.style.background = 'rgba(52, 199, 89, 0.12)';
                    el.style.border = '1px solid rgba(52, 199, 89, 0.3)';
                    if (badge) {
                        badge.style.background = 'var(--success)';
                        badge.style.color = '#000';
                    }
                } else {
                    el.classList.remove('active');
                    el.style.color = 'var(--text-secondary)';
                    el.style.background = 'transparent';
                    el.style.border = 'none';
                    if (badge) {
                        badge.style.background = 'rgba(255,255,255,0.1)';
                        badge.style.color = 'var(--text-secondary)';
                    }
                }
            });
        };

        document.querySelectorAll('#inline-inflow-form .btn-wizard-next').forEach(btn => {
            btn.onclick = (e) => {
                const currentStepDiv = e.target.closest('.wizard-step');
                const currentStep = parseInt(currentStepDiv.getAttribute('data-step'));
                
                // Validation - check if required fields in current step are filled
                const requiredInputs = currentStepDiv.querySelectorAll('input[required], select[required]');
                let allValid = true;
                requiredInputs.forEach(input => {
                    if (!input.value.trim()) {
                        allValid = false;
                        input.style.border = '1px solid var(--danger)';
                    } else {
                        input.style.border = '1px solid var(--glass-border)';
                    }
                });

                if (!allValid) {
                    showPremiumNotice("Input Belum Lengkap", "Silakan lengkapi field wajib (bertanda *) sebelum melanjutkan.");
                    return;
                }

                if (currentStep < wizardSteps.length) {
                    currentStepDiv.style.display = 'none';
                    currentStepDiv.classList.remove('active');
                    const nextStepDiv = document.querySelector(`#inline-inflow-form .wizard-step[data-step="${currentStep + 1}"]`);
                    if (nextStepDiv) {
                        nextStepDiv.style.display = 'block';
                        nextStepDiv.classList.add('active');
                        updateWizardProgressBar(currentStep + 1);
                        const firstInput = nextStepDiv.querySelector('input, select');
                        if (firstInput) firstInput.focus();
                    }
                }
            };
        });

        document.querySelectorAll('#inline-inflow-form .btn-wizard-prev').forEach(btn => {
            btn.onclick = (e) => {
                const currentStepDiv = e.target.closest('.wizard-step');
                const currentStep = parseInt(currentStepDiv.getAttribute('data-step'));
                
                if (currentStep > 1) {
                    currentStepDiv.style.display = 'none';
                    currentStepDiv.classList.remove('active');
                    const prevStepDiv = document.querySelector(`#inline-inflow-form .wizard-step[data-step="${currentStep - 1}"]`);
                    if (prevStepDiv) {
                        prevStepDiv.style.display = 'block';
                        prevStepDiv.classList.add('active');
                        updateWizardProgressBar(currentStep - 1);
                    }
                }
            };
        });
    }

    initializeErpControlCenterHandlers();

    // Check if there is an active session in this browser on load (resilient session restoration)
    const savedSession = getStoredSession();
    if (savedSession) {
        applyLogin(savedSession.user, true);
    } else {
        localStorage.removeItem('currentUserSession');
        document.documentElement.classList.remove('session-restoring');
        if (loginOverlay) {
            loginOverlay.style.display = 'flex';
            loginOverlay.style.opacity = '1';
        }
        startLiveClock();
    }
});
