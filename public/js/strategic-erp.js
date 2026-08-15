document.addEventListener('DOMContentLoaded', () => {
    const state = {
        loaded: {},
        talent: null,
        documents: null,
        accounting: null,
        analytics: null,
        projects: null,
        pendingReviewStatus: 'draft'
    };
    const strategicViews = new Set(['talent', 'analytics', 'documents', 'accounting', 'project-costing']);

    const escapeHtml = value => {
        const element = document.createElement('div');
        element.textContent = String(value ?? '');
        return element.innerHTML;
    };
    const rupiah = value => `Rp ${Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 0 })}`;
    const percent = value => `${Number(value || 0).toLocaleString('id-ID', { maximumFractionDigits: 1 })}%`;
    const jakartaDate = () => {
        const parts = new Intl.DateTimeFormat('en-CA', {
            timeZone: 'Asia/Jakarta',
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        }).formatToParts(new Date()).reduce((carry, part) => ({ ...carry, [part.type]: part.value }), {});
        return `${parts.year}-${parts.month}-${parts.day}`;
    };
    const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const fullMonthNames = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];

    async function request(url, options = {}) {
        if (window.erpApiRequest) return window.erpApiRequest(url, options);

        const isFormData = options.body instanceof FormData;
        const response = await fetch(url, {
            ...options,
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                ...(!isFormData ? { 'Content-Type': 'application/json' } : {}),
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                ...(options.headers || {})
            },
            body: options.body && typeof options.body !== 'string' && !isFormData
                ? JSON.stringify(options.body)
                : options.body
        });
        const payload = await response.json();
        if (!response.ok) throw new Error(payload.message || 'Permintaan tidak dapat diproses.');
        return payload;
    }

    function notify(title, message, variant = 'success') {
        if (window.erpNotify) {
            window.erpNotify(title, escapeHtml(message), { variant });
        }
    }

    function handleError(error, title = 'Data Tidak Dapat Diproses') {
        notify(title, error.message || 'Terjadi kendala pada server.', 'danger');
    }

    function setLoading(elementId) {
        const element = document.getElementById(elementId);
        if (element) element.innerHTML = '<div class="serp-loading"><i class="ph ph-spinner ph-spin"></i>&nbsp; Memuat data terbaru…</div>';
    }

    function empty(message) {
        return `<div class="serp-empty"><span><i class="ph ph-database" style="font-size:24px;display:block;margin-bottom:8px"></i>${escapeHtml(message)}</span></div>`;
    }

    function attachmentLinks(attachments = []) {
        if (!attachments.length) return '';
        return `<div class="serp-attachment-list">${attachments.map(file => `
            <a class="serp-attachment" href="/api/record-attachments/${Number(file.id)}" target="_blank" rel="noopener">
                <i class="ph ph-paperclip"></i>
                ${escapeHtml(file.original_name || 'Dokumen pendukung')}
                <i class="ph ph-download-simple"></i>
            </a>`).join('')}</div>`;
    }

    function metricCard(label, value, note, icon = 'ph-chart-line-up') {
        return `<article class="serp-metric">
            <div class="serp-metric-label"><i class="ph ${icon}"></i>${escapeHtml(label)}</div>
            <div class="serp-metric-value">${escapeHtml(value)}</div>
            <div class="serp-metric-note">${escapeHtml(note || '')}</div>
        </article>`;
    }

    function fillYearSelect(id, selectedYear) {
        const select = document.getElementById(id);
        if (!select) return;
        const current = Number(selectedYear || new Intl.DateTimeFormat('en', { timeZone: 'Asia/Jakarta', year: 'numeric' }).format(new Date()));
        select.innerHTML = Array.from({ length: 8 }, (_, index) => current + 1 - index)
            .map(year => `<option value="${year}" ${year === current ? 'selected' : ''}>${year}</option>`)
            .join('');
    }

    function formPayload(form) {
        return Object.fromEntries(
            [...new FormData(form).entries()].filter(([, value]) => value !== '')
        );
    }

    async function loadTalent(force = false) {
        if (state.loaded.talent && !force) return;
        setLoading('talent-review-list');
        const year = document.getElementById('talent-year')?.value || new Date().getFullYear();
        try {
            const data = await request(`/api/talent/reviews?year=${encodeURIComponent(year)}`);
            state.talent = data;
            state.loaded.talent = true;
            renderTalent(data);
        } catch (error) {
            document.getElementById('talent-review-list').innerHTML = empty(error.message);
            handleError(error, 'Talent Management Tidak Tersedia');
        }
    }

    function renderTalent(data) {
        document.getElementById('talent-summary').innerHTML = [
            metricCard('Review Dipublikasikan', data.summary.reviewed, `Tahun ${data.year}`, 'ph-check-circle'),
            metricCard('Rata-rata Kinerja', percent(data.summary.average_performance), 'Berdasarkan review terpublikasi', 'ph-trend-up'),
            metricCard('Rata-rata Potensi', percent(data.summary.average_potential), 'Potensi pertumbuhan peran', 'ph-rocket-launch'),
            metricCard('Development Plan', data.summary.development_plans, 'Rencana pengembangan tercatat', 'ph-path')
        ].join('');

        const list = document.getElementById('talent-review-list');
        list.innerHTML = data.reviews.length ? data.reviews.map(review => {
            const readiness = {
                developing: 'Masih dikembangkan',
                ready_1_year: 'Siap ≤ 1 tahun',
                ready_now: 'Siap sekarang'
            }[review.readiness] || review.readiness;
            return `<article class="serp-item-card">
                <div class="serp-item-top">
                    <div><h4 class="serp-item-title">${escapeHtml(review.user?.name)}</h4><div class="serp-item-subtitle">${escapeHtml(review.user?.job_title || review.user?.role)} · ${escapeHtml(review.review_cycle.replaceAll('_', ' '))}</div></div>
                    <span class="serp-chip ${review.status === 'published' ? 'success' : 'warning'}">${escapeHtml(review.status)}</span>
                </div>
                <div class="serp-score-grid">
                    <div class="serp-score"><strong>${Number(review.performance_score)}</strong><span>Kinerja</span></div>
                    <div class="serp-score"><strong>${Number(review.potential_score)}</strong><span>Potensi</span></div>
                    <div class="serp-score"><strong>${Number(review.competency_score)}</strong><span>Kompetensi</span></div>
                </div>
                <div class="serp-item-meta"><span><i class="ph ph-target"></i> ${escapeHtml(readiness)}</span><span><i class="ph ph-user-switch"></i> ${escapeHtml(review.next_role || 'Belum ditentukan')}</span><span>Reviewer: ${escapeHtml(review.reviewer?.name)}</span></div>
                ${review.development_plan ? `<div class="serp-item-subtitle" style="margin-top:10px;line-height:1.5">${escapeHtml(review.development_plan)}</div>` : ''}
                <div class="serp-item-actions"><button class="erp-delete-btn" data-erp-delete data-resource-type="talent_review" data-resource-id="${review.id}" data-resource-label="Review talent ${escapeHtml(review.user?.name)}"><i class="ph ph-trash"></i> Hapus / Ajukan</button></div>
            </article>`;
        }).join('') : empty('Belum ada review talent untuk periode ini.');

        const panel = document.getElementById('talent-review-panel');
        const addButton = document.getElementById('talent-new-review');
        panel.style.display = data.can_manage ? '' : 'none';
        addButton.style.display = data.can_manage ? '' : 'none';
        const people = document.getElementById('talent-person');
        people.innerHTML = data.people.map(person => `<option value="${person.id}">${escapeHtml(person.name)} — ${escapeHtml(person.job_title || person.role)}</option>`).join('');
        const yearInput = document.querySelector('#talent-review-form [name="review_year"]');
        if (yearInput) yearInput.value = data.year;
    }

    async function loadDocuments(force = false) {
        if (state.loaded.documents && !force) return;
        setLoading('document-list');
        try {
            const data = await request('/api/documents');
            state.documents = data;
            state.loaded.documents = true;
            renderDocuments(data);
        } catch (error) {
            document.getElementById('document-list').innerHTML = empty(error.message);
            handleError(error, 'Dokumen Tidak Tersedia');
        }
    }

    function renderDocuments(data) {
        const list = document.getElementById('document-list');
        list.innerHTML = data.documents.length ? data.documents.map(document => {
            const chip = document.status === 'signed' ? 'success' : (document.status === 'revoked' ? 'danger' : 'warning');
            const signedBy = document.signatures?.[0]?.signer?.name;
            return `<article class="serp-item-card">
                <div class="serp-item-top">
                    <div><h4 class="serp-item-title">${escapeHtml(document.owner?.name || document.title)}</h4><div class="serp-item-subtitle">${escapeHtml(document.document_number)} · ${escapeHtml(document.content?.program_name || 'Dokumen')}</div></div>
                    <span class="serp-chip ${chip}">${escapeHtml(document.status)}</span>
                </div>
                <div class="serp-item-meta"><span><i class="ph ph-calendar"></i> ${escapeHtml(document.issued_at || 'Belum terbit')}</span><span><i class="ph ph-pen-nib"></i> ${escapeHtml(signedBy || 'Belum ditandatangani')}</span>${document.document_hash_short ? `<span><i class="ph ph-fingerprint"></i> ${escapeHtml(document.document_hash_short)}</span>` : ''}</div>
                <div class="serp-item-actions">
                    <button class="serp-btn ghost" data-doc-open="${escapeHtml(document.certificate_url)}"><i class="ph ph-eye"></i> Lihat</button>
                    <button class="serp-btn ghost" data-doc-open="${escapeHtml(document.verification_url)}"><i class="ph ph-shield-check"></i> Verifikasi</button>
                    ${document.can_sign ? `<button class="serp-btn primary" data-doc-sign="${document.id}"><i class="ph ph-pen-nib"></i> Tanda Tangani</button>` : ''}
                    ${data.can_issue && document.status === 'signed' ? `<button class="serp-btn danger" data-doc-revoke="${document.id}"><i class="ph ph-warning"></i> Cabut</button>` : ''}
                    <button class="erp-delete-btn" data-erp-delete data-resource-type="erp_document" data-resource-id="${document.id}" data-resource-label="${escapeHtml(document.document_number)}"><i class="ph ph-trash"></i> Hapus / Ajukan</button>
                </div>
            </article>`;
        }).join('') : empty('Belum ada sertifikat magang. HRD atau CEO dapat membuat draft pertama.');

        document.getElementById('certificate-form-panel').style.display = data.can_issue ? '' : 'none';
        document.getElementById('document-new').style.display = data.can_issue ? '' : 'none';
        document.getElementById('certificate-person').innerHTML = data.people.map(person =>
            `<option value="${person.id}">${escapeHtml(person.name)} — ${escapeHtml(person.employee_code || person.username)} · ${escapeHtml(person.job_title || person.role)}</option>`
        ).join('');
        document.getElementById('certificate-supervisor').innerHTML = data.signers.map(person =>
            `<option value="${person.id}">${escapeHtml(person.name)} — ${escapeHtml(person.employee_code || person.username)} · ${escapeHtml(person.job_title || person.role)}</option>`
        ).join('');
        document.getElementById('certificate-template-select').innerHTML = [
            '<option value="">Desain standar ERP</option>',
            ...data.templates.map(template =>
                `<option value="${template.id}" ${template.is_active ? 'selected' : ''}>${escapeHtml(template.name)}${template.is_active ? ' · Aktif' : ''}</option>`
            )
        ].join('');

        const templatePanel = document.getElementById('certificate-template-panel');
        if (templatePanel) templatePanel.style.display = data.can_manage_templates ? '' : 'none';
        const templateStatus = document.getElementById('certificate-template-status');
        if (templateStatus) {
            const activeTemplate = data.templates.find(template => template.is_active);
            templateStatus.textContent = activeTemplate
                ? `Template aktif: ${activeTemplate.name}`
                : 'Belum ada template unggahan. Desain standar ERP akan digunakan.';
        }
        const signatureStatus = document.getElementById('certificate-signature-status');
        if (signatureStatus) {
            signatureStatus.textContent = data.signature_profile_configured
                ? 'Tanda tangan Anda sudah tersimpan dan siap dipakai.'
                : 'Belum ada tanda tangan tersimpan. Sertifikat masih dapat disahkan dengan identitas akun tanpa gambar.';
        }
    }

    async function signDocument(id) {
        const execute = async () => {
            try {
                const result = await request(`/api/documents/${id}/sign`, { method: 'POST' });
                notify('Sertifikat Ditandatangani', result.message);
                state.loaded.documents = false;
                await loadDocuments(true);
            } catch (error) {
                handleError(error);
            }
        };
        if (window.erpConfirm) {
            window.erpConfirm(
                'Tandatangani Sertifikat?',
                'Pastikan nama, program, dan periode sudah benar. Setelah ditandatangani, hash integritas akan diterbitkan.',
                execute,
                { variant: 'primary', confirmText: 'Ya, Tanda Tangani', cancelText: 'Periksa Lagi' }
            );
        } else {
            await execute();
        }
    }

    function revokeDocument(id) {
        if (!window.erpTextInput) return;
        window.erpTextInput({
            title: 'Cabut Sertifikat',
            description: 'Jelaskan alasan pencabutan secara profesional. Status ini langsung terlihat pada verifikasi publik.',
            label: 'Alasan pencabutan',
            placeholder: 'Contoh: Sertifikat diganti karena koreksi data penerima.',
            submitText: 'Cabut Sertifikat'
        }, async reason => {
            try {
                const result = await request(`/api/documents/${id}/revoke`, { method: 'POST', body: { reason } });
                notify('Sertifikat Dicabut', result.message, 'success');
                await loadDocuments(true);
            } catch (error) {
                handleError(error);
            }
        });
    }

    async function loadAccounting(force = false) {
        if (state.loaded.accounting && !force) return;
        setLoading('journal-list');
        const year = document.getElementById('accounting-year')?.value || new Date().getFullYear();
        const month = document.getElementById('accounting-month')?.value || new Date().getMonth() + 1;
        try {
            const data = await request(`/api/accounting?year=${encodeURIComponent(year)}&month=${encodeURIComponent(month)}`);
            state.accounting = data;
            state.loaded.accounting = true;
            renderAccounting(data);
        } catch (error) {
            document.getElementById('journal-list').innerHTML = empty(error.message);
            handleError(error, 'Laporan Akuntansi Tidak Tersedia');
        }
    }

    function renderAccounting(data) {
        const report = data.monthly_profit_loss;
        document.getElementById('accounting-summary').innerHTML = [
            metricCard('Pendapatan Bulan Ini', rupiah(report.revenue), fullMonthNames[data.month - 1], 'ph-arrow-circle-down'),
            metricCard('Total Biaya', rupiah(report.expenses), 'Seluruh akun beban', 'ph-arrow-circle-up'),
            metricCard('Laba Bersih', rupiah(report.net_profit), report.net_profit >= 0 ? 'Positif' : 'Perlu evaluasi', 'ph-wallet'),
            metricCard('Margin Bersih', percent(report.margin_percentage), `Tahun buku ${data.year}`, 'ph-percent')
        ].join('');

        document.getElementById('accounting-months').innerHTML = data.annual_evaluation.months.map(month => `<tr>
            <td>${fullMonthNames[month.month - 1]}</td>
            <td>${rupiah(month.revenue)}</td>
            <td>${rupiah(month.expenses)}</td>
            <td class="${month.net_profit >= 0 ? 'positive' : 'negative'}">${rupiah(month.net_profit)}</td>
            <td>${percent(month.margin_percentage)}</td>
        </tr>`).join('');

        const journals = document.getElementById('journal-list');
        journals.innerHTML = data.recent_entries.length ? data.recent_entries.map(entry => {
            const total = (entry.lines || []).reduce((sum, line) => sum + Number(line.debit || 0), 0);
            const accounts = (entry.lines || []).map(line => `${line.account?.code} ${line.account?.name}`).join(' ↔ ');
            return `<article class="serp-item-card">
                <div class="serp-item-top"><div><h4 class="serp-item-title">${escapeHtml(entry.description)}</h4><div class="serp-item-subtitle">${escapeHtml(entry.reference)} · ${escapeHtml(entry.entry_date)}</div></div><span class="serp-chip success">Balanced</span></div>
                <div class="serp-item-meta"><span>${escapeHtml(accounts)}</span><strong>${escapeHtml(rupiah(total))}</strong></div>
                ${attachmentLinks(entry.attachments)}
                ${data.can_write && entry.status === 'posted' ? `<div class="serp-item-actions"><button class="erp-delete-btn" data-erp-delete data-resource-type="journal_entry" data-resource-id="${entry.id}" data-resource-label="${escapeHtml(entry.reference)}"><i class="ph ph-arrow-u-up-left"></i> Ajukan Pembalikan</button></div>` : ''}
            </article>`;
        }).join('') : empty('Belum ada jurnal. Pemasukan klien dan biaya proyek akan tersinkron otomatis.');

        document.getElementById('transaction-form-panel').style.display = data.can_write ? '' : 'none';
        document.getElementById('accounting-import-open').style.display = data.can_write ? '' : 'none';
        document.getElementById('accounting-import-template').style.display = data.can_write ? '' : 'none';
        document.getElementById('transaction-project').innerHTML = '<option value="">Tanpa proyek</option>' + data.projects
            .map(project => `<option value="${project.id}">${escapeHtml(project.code)} — ${escapeHtml(project.name)}</option>`).join('');
        updateTransactionCategories();
        const transactionForm = document.getElementById('transaction-form');
        if (transactionForm) restoreTransactionDraft(transactionForm);
    }

    function updateTransactionCategories() {
        const kind = document.getElementById('transaction-kind')?.value || 'revenue';
        const categories = kind === 'revenue'
            ? [['design_revenue', 'Pendapatan Jasa Desain'], ['contractor_revenue', 'Pendapatan Kontraktor']]
            : [['direct_project_cost', 'Biaya Langsung Proyek'], ['payroll_expense', 'Beban Gaji & SDM'], ['operating_expense', 'Beban Operasional'], ['marketing_expense', 'Beban Pemasaran']];
        document.getElementById('transaction-category').innerHTML = categories
            .map(([value, label]) => `<option value="${value}">${escapeHtml(label)}</option>`).join('');
    }

    async function loadAnalytics(force = false) {
        if (state.loaded.analytics && !force) return;
        setLoading('analytics-alerts');
        const year = document.getElementById('analytics-year')?.value || new Date().getFullYear();
        try {
            const data = await request(`/api/analytics/overview?year=${encodeURIComponent(year)}`);
            state.analytics = data;
            state.loaded.analytics = true;
            renderAnalytics(data);
        } catch (error) {
            document.getElementById('analytics-alerts').innerHTML = empty(error.message);
            handleError(error, 'Advanced Analytics Tidak Tersedia');
        }
    }

    function renderAnalytics(data) {
        const metrics = [
            metricCard('Karyawan Aktif', data.people.active_people, data.scope === 'company' ? 'Seluruh perusahaan' : 'Sesuai cakupan akun', 'ph-users-three'),
            metricCard('Task Completion', percent(data.execution.task_completion_rate), `${data.execution.overdue_tasks} task overdue`, 'ph-list-checks'),
            metricCard('Attendance On-Time', percent(data.execution.attendance_on_time_rate), 'Berdasarkan attendance tahun berjalan', 'ph-clock'),
            data.financial_visible
                ? metricCard('Laba Bersih Tahunan', rupiah(data.financial?.net_profit), percent(data.financial?.margin_percentage), 'ph-chart-line-up')
                : metricCard('Rata-rata Kinerja', percent(data.people.average_performance), `${data.people.high_potential} high potential`, 'ph-user-focus')
        ];
        document.getElementById('analytics-metrics').innerHTML = metrics.join('');

        const chart = document.getElementById('analytics-monthly-chart');
        if (!data.financial_visible) {
            chart.innerHTML = empty('Grafik keuangan hanya tersedia bagi CEO dan divisi Finance.');
        } else {
            const months = data.financial?.months || [];
            const maxValue = Math.max(1, ...months.flatMap(month => [Number(month.revenue), Number(month.expenses)]));
            chart.innerHTML = months.map(month => `<div class="serp-bar-month">
                <div class="serp-bar-stack" title="${escapeHtml(fullMonthNames[month.month - 1])}: pendapatan ${escapeHtml(rupiah(month.revenue))}, biaya ${escapeHtml(rupiah(month.expenses))}">
                    <span class="serp-bar revenue" style="height:${Math.max(2, (Number(month.revenue) / maxValue) * 100)}%"></span>
                    <span class="serp-bar expense" style="height:${Math.max(2, (Number(month.expenses) / maxValue) * 100)}%"></span>
                </div><span>${monthNames[month.month - 1]}</span>
            </div>`).join('');
        }

        const alerts = document.getElementById('analytics-alerts');
        alerts.innerHTML = data.alerts.length ? data.alerts.map(alert => `<article class="serp-item-card serp-alert">
            <i class="ph ${alert.severity === 'high' ? 'ph-warning-octagon' : 'ph-warning-circle'}"></i>
            <div><h4 class="serp-item-title">${escapeHtml(alert.title)}</h4><p>${escapeHtml(alert.message)}</p></div>
        </article>`).join('') : empty('Tidak ada peringatan prioritas pada data terbaru.');

        const projects = document.getElementById('analytics-projects');
        projects.innerHTML = data.projects.visible && data.projects.portfolio.length
            ? data.projects.portfolio.map(project => `<tr>
                <td><strong>${escapeHtml(project.name)}</strong><br><small>${escapeHtml(project.code)}</small></td>
                <td><span class="serp-chip info">${escapeHtml(project.type)}</span></td>
                <td><div class="serp-progress"><span style="width:${Math.min(100, Number(project.progress))}%"></span></div><small>${percent(project.progress)}</small></td>
                <td>${rupiah(project.actual_cost)} / ${rupiah(project.budget_cost)}</td>
                <td class="${project.estimated_margin >= 0 ? 'positive' : 'negative'}">${rupiah(project.estimated_margin)}</td>
            </tr>`).join('')
            : `<tr><td colspan="5">${data.projects.visible ? 'Belum ada proyek dalam portofolio.' : 'Portofolio proyek tidak tersedia untuk peran ini.'}</td></tr>`;
    }

    async function loadProjects(force = false) {
        if (state.loaded.projects && !force) return;
        setLoading('project-list');
        try {
            const data = await request('/api/projects');
            state.projects = data;
            state.loaded.projects = true;
            renderProjects(data);
        } catch (error) {
            document.getElementById('project-list').innerHTML = empty(error.message);
            handleError(error, 'Project Costing Tidak Tersedia');
        }
    }

    function renderProjects(data) {
        document.getElementById('project-summary').innerHTML = [
            metricCard('Nilai Portofolio', rupiah(data.portfolio.contract_value), `${data.projects.length} proyek`, 'ph-briefcase'),
            metricCard('Anggaran Biaya', rupiah(data.portfolio.budget_cost), 'Budget seluruh proyek', 'ph-calculator'),
            metricCard('Biaya Aktual', rupiah(data.portfolio.actual_cost), 'Terhubung ke jurnal biaya', 'ph-receipt'),
            metricCard('Estimasi Margin', rupiah(data.portfolio.estimated_margin), `${data.portfolio.design_count} desain · ${data.portfolio.contractor_count} kontraktor`, 'ph-trend-up')
        ].join('');

        document.getElementById('project-list').innerHTML = data.projects.length ? data.projects.map(project => {
            const summary = project.summary;
            const utilizationClass = Number(summary.cost_utilization) >= 85 ? 'danger' : (Number(summary.cost_utilization) >= 60 ? 'warning' : 'success');
            return `<article class="serp-item-card">
                <div class="serp-item-top"><div><h4 class="serp-item-title">${escapeHtml(project.name)}</h4><div class="serp-item-subtitle">${escapeHtml(project.code)} · ${escapeHtml(project.client_name)}</div></div><div><span class="serp-chip info">${escapeHtml(project.project_type)}</span> <span class="serp-chip ${project.status === 'completed' ? 'success' : 'warning'}">${escapeHtml(project.status)}</span></div></div>
                <div class="serp-score-grid">
                    <div class="serp-score"><strong>${percent(project.progress)}</strong><span>Progres</span></div>
                    <div class="serp-score"><strong>${percent(summary.cost_utilization)}</strong><span>Budget Terpakai</span></div>
                    <div class="serp-score"><strong>${percent(summary.estimated_margin_percentage)}</strong><span>Margin Est.</span></div>
                </div>
                <div class="serp-progress" style="margin-top:11px"><span style="width:${Math.min(100, Number(summary.cost_utilization))}%"></span></div>
                <div class="serp-item-meta"><span>Kontrak: ${escapeHtml(rupiah(project.contract_value))}</span><span>Aktual: ${escapeHtml(rupiah(summary.actual_cost))}</span><span class="serp-chip ${utilizationClass}">Sisa ${escapeHtml(rupiah(summary.remaining_budget))}</span></div>
                ${(project.costs || []).length ? `<div class="serp-cost-list">${project.costs.slice(0, 5).map(cost => `<div><div class="serp-item-meta"><span>${escapeHtml(cost.cost_date)} - ${escapeHtml(cost.description)}</span><strong>${escapeHtml(rupiah(cost.amount))}</strong>${data.can_write ? `<button class="erp-delete-btn icon-only" data-erp-delete data-resource-type="project_cost" data-resource-id="${cost.id}" data-resource-label="${escapeHtml(cost.description)}" title="Ajukan penghapusan biaya"><i class="ph ph-trash"></i></button>` : ''}</div>${attachmentLinks(cost.attachments)}</div>`).join('')}</div>` : ''}
                <div class="serp-item-actions">
                    ${data.can_write ? `<button class="serp-btn ghost" data-project-edit="${project.id}"><i class="ph ph-pencil"></i> Edit</button><button class="serp-btn primary" data-project-cost="${project.id}"><i class="ph ph-plus-circle"></i> Catat Biaya</button>` : ''}
                    ${data.can_write ? `<button class="erp-delete-btn" data-erp-delete data-resource-type="project" data-resource-id="${project.id}" data-resource-label="${escapeHtml(project.code)} - ${escapeHtml(project.name)}"><i class="ph ph-trash"></i> Hapus / Ajukan</button>` : ''}
                </div>
            </article>`;
        }).join('') : empty('Belum ada proyek. Pemasukan klien baru akan membentuk proyek secara otomatis.');

        document.getElementById('project-form-panel').style.display = data.can_write ? '' : 'none';
        document.getElementById('project-new').style.display = data.can_write ? '' : 'none';
    }

    function editProject(id) {
        const project = state.projects?.projects.find(item => Number(item.id) === Number(id));
        const form = document.getElementById('project-form');
        if (!project || !form) return;
        document.getElementById('project-id').value = project.id;
        document.getElementById('project-form-title').textContent = `Edit ${project.code}`;
        ['name', 'client_name', 'project_type', 'status', 'start_date', 'target_end_date', 'contract_value', 'budget_cost', 'progress', 'notes']
            .forEach(name => {
                const field = form.elements[name];
                if (field) field.value = project[name] ?? '';
            });
        document.getElementById('project-form-panel').scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    function resetProjectForm() {
        const form = document.getElementById('project-form');
        form.reset();
        document.getElementById('project-id').value = '';
        document.getElementById('project-form-title').textContent = 'Proyek Baru';
        form.elements.status.value = 'active';
        form.elements.progress.value = 0;
    }

    function openCostModal(id) {
        const project = state.projects?.projects.find(item => Number(item.id) === Number(id));
        if (!project) return;
        document.getElementById('project-cost-id').value = project.id;
        document.getElementById('project-cost-caption').textContent = `${project.code} — ${project.name}`;
        document.querySelector('#project-cost-form [name="cost_date"]').value = jakartaDate();
        document.getElementById('project-cost-modal').classList.add('open');
        document.getElementById('project-cost-modal').setAttribute('aria-hidden', 'false');
    }

    function closeCostModal() {
        document.getElementById('project-cost-modal').classList.remove('open');
        document.getElementById('project-cost-modal').setAttribute('aria-hidden', 'true');
        document.getElementById('project-cost-form').reset();
    }

    function openAccountingImportModal() {
        const modal = document.getElementById('accounting-import-modal');
        if (!modal) return;
        modal.classList.add('open');
        modal.setAttribute('aria-hidden', 'false');
    }

    function closeAccountingImportModal() {
        const modal = document.getElementById('accounting-import-modal');
        const form = document.getElementById('accounting-import-form');
        if (!modal) return;
        modal.classList.remove('open');
        modal.setAttribute('aria-hidden', 'true');
        form?.reset();
        const fileName = document.getElementById('accounting-import-file-name');
        if (fileName) fileName.textContent = 'Maksimal 500 baris dan 5 MB. Impor dibatalkan jika satu baris tidak valid.';
    }

    const transactionDraftKey = () => `suba-erp:accounting-transaction-draft:${window.ERP_CURRENT_USER?.username || 'anonymous'}`;

    function saveTransactionDraft(form) {
        const data = {};
        [...form.elements].forEach(field => {
            if (!field.name || field.type === 'file' || field.type === 'submit') return;
            data[field.name] = field.value;
        });
        localStorage.setItem(transactionDraftKey(), JSON.stringify({ data, savedAt: Date.now() }));
        const status = document.getElementById('transaction-draft-status');
        if (status) {
            status.innerHTML = `<i class="ph ph-cloud-check"></i> Draft otomatis tersimpan ${new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit' }).format(new Date())}.`;
        }
    }

    function restoreTransactionDraft(form) {
        try {
            const draft = JSON.parse(localStorage.getItem(transactionDraftKey()) || 'null');
            if (!draft?.data || Date.now() - Number(draft.savedAt || 0) > 7 * 24 * 60 * 60 * 1000) return;
            Object.entries(draft.data).forEach(([name, value]) => {
                const field = form.elements[name];
                if (field && value !== '') field.value = value;
            });
            updateTransactionCategories();
            if (draft.data.category && form.elements.category) form.elements.category.value = draft.data.category;
        } catch {
            localStorage.removeItem(transactionDraftKey());
        }
    }

    function bindForms() {
        const talentForm = document.getElementById('talent-review-form');
        talentForm?.querySelectorAll('[data-review-status]').forEach(button => {
            button.addEventListener('click', () => { state.pendingReviewStatus = button.dataset.reviewStatus; });
        });
        talentForm?.addEventListener('submit', async event => {
            event.preventDefault();
            const payload = formPayload(talentForm);
            payload.status = event.submitter?.dataset.reviewStatus || state.pendingReviewStatus;
            payload.training_plan = document.getElementById('talent-training').value
                .split(',').map(item => item.trim()).filter(Boolean);
            ['user_id', 'review_year', 'performance_score', 'potential_score', 'competency_score'].forEach(key => { payload[key] = Number(payload[key]); });
            try {
                const result = await request('/api/talent/reviews', { method: 'POST', body: payload });
                notify('Review Talent Tersimpan', result.message);
                await loadTalent(true);
            } catch (error) {
                handleError(error);
            }
        });

        document.getElementById('certificate-form')?.addEventListener('submit', async event => {
            event.preventDefault();
            const form = event.currentTarget;
            const payload = formPayload(form);
            payload.owner_user_id = Number(payload.owner_user_id);
            payload.supervisor_user_id = Number(payload.supervisor_user_id);
            if (payload.certificate_template_id) {
                payload.certificate_template_id = Number(payload.certificate_template_id);
            }
            try {
                const result = await request('/api/documents/internship-certificates', { method: 'POST', body: payload });
                notify('Draft Sertifikat Dibuat', result.message);
                form.reset();
                form.elements.issued_at.value = jakartaDate();
                await loadDocuments(true);
            } catch (error) {
                handleError(error);
            }
        });

        document.getElementById('certificate-template-form')?.addEventListener('submit', async event => {
            event.preventDefault();
            const form = event.currentTarget;
            try {
                const result = await request('/api/documents/templates', {
                    method: 'POST',
                    body: new FormData(form)
                });
                notify('Template Sertifikat Aktif', result.message);
                form.reset();
                await loadDocuments(true);
            } catch (error) {
                handleError(error, 'Template Tidak Dapat Disimpan');
            }
        });

        document.getElementById('certificate-signature-form')?.addEventListener('submit', async event => {
            event.preventDefault();
            const form = event.currentTarget;
            try {
                const result = await request('/api/documents/signature-profile', {
                    method: 'POST',
                    body: new FormData(form)
                });
                notify('Tanda Tangan Tersimpan', result.message);
                form.reset();
                await loadDocuments(true);
            } catch (error) {
                handleError(error, 'Tanda Tangan Tidak Dapat Disimpan');
            }
        });

        document.getElementById('transaction-form')?.addEventListener('submit', async event => {
            event.preventDefault();
            const form = event.currentTarget;
            try {
                const result = await request('/api/accounting/transactions', {
                    method: 'POST',
                    body: new FormData(form)
                });
                notify('Jurnal Berhasil Diposting', result.message);
                localStorage.removeItem(transactionDraftKey());
                form.reset();
                form.elements.date.value = jakartaDate();
                updateTransactionCategories();
                await loadAccounting(true);
            } catch (error) {
                handleError(error);
            }
        });

        document.getElementById('project-form')?.addEventListener('submit', async event => {
            event.preventDefault();
            const form = event.currentTarget;
            const id = document.getElementById('project-id').value;
            const payload = formPayload(form);
            ['contract_value', 'budget_cost', 'progress'].forEach(key => { payload[key] = Number(payload[key]); });
            try {
                const result = await request(id ? `/api/projects/${id}` : '/api/projects', {
                    method: id ? 'PUT' : 'POST',
                    body: payload
                });
                notify(id ? 'Proyek Diperbarui' : 'Proyek Dibuat', result.message);
                resetProjectForm();
                await loadProjects(true);
            } catch (error) {
                handleError(error);
            }
        });

        document.getElementById('project-cost-form')?.addEventListener('submit', async event => {
            event.preventDefault();
            const form = event.currentTarget;
            const id = document.getElementById('project-cost-id').value;
            try {
                const result = await request(`/api/projects/${id}/costs`, {
                    method: 'POST',
                    body: new FormData(form)
                });
                notify('Biaya Proyek Tersinkron', result.message);
                closeCostModal();
                state.loaded.accounting = false;
                state.loaded.analytics = false;
                await loadProjects(true);
            } catch (error) {
                handleError(error);
            }
        });

        document.getElementById('accounting-import-form')?.addEventListener('submit', async event => {
            event.preventDefault();
            const form = event.currentTarget;
            const submit = form.querySelector('button[type="submit"]');
            const original = submit?.innerHTML;
            if (submit) {
                submit.disabled = true;
                submit.innerHTML = '<i class="ph ph-spinner ph-spin"></i> Memvalidasi...';
            }
            try {
                const result = await request('/api/accounting/import-transactions', {
                    method: 'POST',
                    body: new FormData(form)
                });
                closeAccountingImportModal();
                state.loaded.analytics = false;
                notify(
                    'Impor Keuangan Berhasil',
                    `${result.message} Total nominal ${rupiah(result.summary?.total_amount)}${Number(result.summary?.skipped || 0) ? `; ${result.summary.skipped} duplikat dilewati.` : '.'}`
                );
                await loadAccounting(true);
            } catch (error) {
                handleError(error, 'File Tidak Dapat Diimpor');
            } finally {
                if (submit) {
                    submit.disabled = false;
                    submit.innerHTML = original;
                }
            }
        });
    }

    function bindControls() {
        document.getElementById('talent-year')?.addEventListener('change', () => loadTalent(true));
        document.getElementById('analytics-year')?.addEventListener('change', () => loadAnalytics(true));
        document.getElementById('analytics-refresh')?.addEventListener('click', () => loadAnalytics(true));
        document.getElementById('accounting-year')?.addEventListener('change', () => loadAccounting(true));
        document.getElementById('accounting-month')?.addEventListener('change', () => loadAccounting(true));
        document.getElementById('transaction-kind')?.addEventListener('change', updateTransactionCategories);
        document.getElementById('accounting-import-open')?.addEventListener('click', openAccountingImportModal);
        document.querySelector('[data-close-accounting-import]')?.addEventListener('click', closeAccountingImportModal);
        document.getElementById('accounting-import-modal')?.addEventListener('click', event => {
            if (event.target.id === 'accounting-import-modal') closeAccountingImportModal();
        });
        document.getElementById('accounting-import-file')?.addEventListener('change', event => {
            const fileName = document.getElementById('accounting-import-file-name');
            if (fileName) {
                fileName.textContent = event.target.files?.[0]
                    ? `${event.target.files[0].name} · ${(event.target.files[0].size / 1024).toLocaleString('id-ID', { maximumFractionDigits: 0 })} KB`
                    : 'Maksimal 500 baris dan 5 MB. Impor dibatalkan jika satu baris tidak valid.';
            }
        });
        document.getElementById('talent-new-review')?.addEventListener('click', () => document.getElementById('talent-review-panel')?.scrollIntoView({ behavior: 'smooth' }));
        document.getElementById('document-new')?.addEventListener('click', () => document.getElementById('certificate-form-panel')?.scrollIntoView({ behavior: 'smooth' }));
        document.getElementById('project-new')?.addEventListener('click', () => {
            resetProjectForm();
            document.getElementById('project-form-panel')?.scrollIntoView({ behavior: 'smooth' });
        });
        document.querySelector('[data-close-cost]')?.addEventListener('click', closeCostModal);
        document.getElementById('project-cost-modal')?.addEventListener('click', event => {
            if (event.target.id === 'project-cost-modal') closeCostModal();
        });

        document.addEventListener('click', event => {
            const open = event.target.closest('[data-doc-open]');
            if (open) window.open(open.dataset.docOpen, '_blank', 'noopener');
            const sign = event.target.closest('[data-doc-sign]');
            if (sign) signDocument(sign.dataset.docSign);
            const revoke = event.target.closest('[data-doc-revoke]');
            if (revoke) revokeDocument(revoke.dataset.docRevoke);
            const edit = event.target.closest('[data-project-edit]');
            if (edit) editProject(edit.dataset.projectEdit);
            const cost = event.target.closest('[data-project-cost]');
            if (cost) openCostModal(cost.dataset.projectCost);
        });
    }

    async function loadView(target, force = false) {
        if (!strategicViews.has(target) || !window.ERP_CURRENT_USER) return;
        if (target === 'talent') await loadTalent(force);
        if (target === 'analytics') await loadAnalytics(force);
        if (target === 'documents') await loadDocuments(force);
        if (target === 'accounting') await loadAccounting(force);
        if (target === 'project-costing') await loadProjects(force);
    }

    function initialize() {
        const currentYear = Number(new Intl.DateTimeFormat('en', { timeZone: 'Asia/Jakarta', year: 'numeric' }).format(new Date()));
        fillYearSelect('talent-year', currentYear);
        fillYearSelect('analytics-year', currentYear);
        fillYearSelect('accounting-year', currentYear);
        document.getElementById('accounting-month').innerHTML = fullMonthNames
            .map((month, index) => `<option value="${index + 1}" ${index === new Date().getMonth() ? 'selected' : ''}>${escapeHtml(month)}</option>`).join('');
        document.querySelector('#certificate-form [name="issued_at"]').value = jakartaDate();
        document.querySelector('#transaction-form [name="date"]').value = jakartaDate();
        updateTransactionCategories();
        const transactionForm = document.getElementById('transaction-form');
        if (transactionForm) {
            restoreTransactionDraft(transactionForm);
            transactionForm.addEventListener('input', () => saveTransactionDraft(transactionForm));
            transactionForm.addEventListener('change', () => saveTransactionDraft(transactionForm));
        }
        bindForms();
        bindControls();

        const active = window.location.hash.replace('#', '');
        if (window.ERP_CURRENT_USER && strategicViews.has(active)) loadView(active);
    }

    window.StrategicERP = { loadView };
    window.addEventListener('erp:user-ready', () => {
        const active = window.location.hash.replace('#', '');
        if (strategicViews.has(active)) loadView(active, true);
    });
    window.addEventListener('erp:data-deletion-updated', () => {
        state.loaded.talent = false;
        state.loaded.documents = false;
        state.loaded.accounting = false;
        state.loaded.analytics = false;
        state.loaded.projects = false;
        const active = window.location.hash.replace('#', '');
        if (strategicViews.has(active)) loadView(active, true);
    });
    initialize();
});
