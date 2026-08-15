<section id="view-talent" class="view-section strategic-view" style="display:none;">
    <div class="serp-page-head">
        <div>
            <span class="serp-eyebrow">People intelligence</span>
            <h2>Talent Management</h2>
            <p>Review kinerja, potensi, kompetensi, dan rencana pengembangan dengan akses sesuai struktur organisasi.</p>
        </div>
        <div class="serp-head-actions">
            <select id="talent-year" class="serp-select" aria-label="Tahun review"></select>
            <button type="button" class="serp-btn primary" id="talent-new-review"><i class="ph ph-plus"></i> Buat Review</button>
        </div>
    </div>
    <div class="serp-metric-grid" id="talent-summary"></div>
    <div class="serp-layout">
        <div class="serp-panel">
            <div class="serp-panel-head"><div><h3>Peta Talent Tim</h3><p>Review terbaru dalam cakupan akun Anda.</p></div></div>
            <div id="talent-review-list" class="serp-card-list"></div>
        </div>
        <aside class="serp-panel serp-form-panel" id="talent-review-panel">
            <div class="serp-panel-head"><div><h3>Form Review Talent</h3><p>Draft dapat diedit; publikasi dapat dilihat karyawan terkait.</p></div></div>
            <form id="talent-review-form" class="serp-form">
                <label>Karyawan<select name="user_id" id="talent-person" required></select></label>
                <div class="serp-form-row">
                    <label>Tahun<input type="number" name="review_year" min="2020" max="2100" required></label>
                    <label>Siklus<select name="review_cycle"><option value="annual">Tahunan</option><option value="semester_1">Semester 1</option><option value="semester_2">Semester 2</option><option value="quarter_1">Kuartal 1</option><option value="quarter_2">Kuartal 2</option><option value="quarter_3">Kuartal 3</option><option value="quarter_4">Kuartal 4</option></select></label>
                </div>
                <div class="serp-form-row triple">
                    <label>Kinerja<input type="number" name="performance_score" min="0" max="100" required></label>
                    <label>Potensi<input type="number" name="potential_score" min="0" max="100" required></label>
                    <label>Kompetensi<input type="number" name="competency_score" min="0" max="100" required></label>
                </div>
                <label>Kesiapan<select name="readiness"><option value="developing">Masih dikembangkan</option><option value="ready_1_year">Siap dalam 1 tahun</option><option value="ready_now">Siap sekarang</option></select></label>
                <label>Kekuatan<textarea name="strengths" rows="2" placeholder="Kekuatan utama yang teramati"></textarea></label>
                <label>Rencana pengembangan<textarea name="development_plan" rows="3" placeholder="Langkah konkret, target, dan periode"></textarea></label>
                <label>Peran berikutnya<input name="next_role" placeholder="Contoh: Senior Architect"></label>
                <label>Pelatihan (pisahkan koma)<input id="talent-training" placeholder="Leadership, BIM Advanced"></label>
                <div class="serp-form-row">
                    <button type="submit" class="serp-btn muted" data-review-status="draft">Simpan Draft</button>
                    <button type="submit" class="serp-btn primary" data-review-status="published">Publikasikan</button>
                </div>
            </form>
        </aside>
    </div>
</section>

<section id="view-analytics" class="view-section strategic-view" style="display:none;">
    <div class="serp-page-head">
        <div>
            <span class="serp-eyebrow">Decision intelligence</span>
            <h2>Advanced Analytics</h2>
            <p>Satu ringkasan lintas people, execution, proyek, dan keuangan tanpa membuka data di luar kewenangan.</p>
        </div>
        <div class="serp-head-actions">
            <select id="analytics-year" class="serp-select" aria-label="Tahun analitik"></select>
            <button type="button" class="serp-btn ghost" id="analytics-refresh"><i class="ph ph-arrows-clockwise"></i> Segarkan</button>
        </div>
    </div>
    <div class="serp-metric-grid" id="analytics-metrics"></div>
    <div class="serp-analytics-grid">
        <div class="serp-panel">
            <div class="serp-panel-head"><div><h3>Performa Bulanan</h3><p>Pendapatan, biaya, dan laba berdasarkan jurnal terposting.</p></div></div>
            <div id="analytics-monthly-chart" class="serp-bar-chart"></div>
        </div>
        <div class="serp-panel">
            <div class="serp-panel-head"><div><h3>Peringatan Prioritas</h3><p>Risiko biaya, deadline, dan pembayaran terbaru.</p></div></div>
            <div id="analytics-alerts" class="serp-card-list compact"></div>
        </div>
    </div>
    <div class="serp-panel">
        <div class="serp-panel-head"><div><h3>Portofolio Proyek</h3><p>Kontrak, pemakaian anggaran, progres, dan estimasi margin.</p></div></div>
        <div class="serp-table-wrap"><table class="serp-table"><thead><tr><th>Proyek</th><th>Tipe</th><th>Progres</th><th>Biaya / Anggaran</th><th>Margin Est.</th></tr></thead><tbody id="analytics-projects"></tbody></table></div>
    </div>
</section>

<section id="view-documents" class="view-section strategic-view" style="display:none;">
    <div class="serp-page-head">
        <div>
            <span class="serp-eyebrow">Verified documents</span>
            <h2>Dokumen & E-Sign</h2>
            <p>Penerbitan sertifikat magang dengan persetujuan internal, hash integritas, dan verifikasi publik.</p>
        </div>
        <button type="button" class="serp-btn primary" id="document-new"><i class="ph ph-certificate"></i> Buat Sertifikat</button>
    </div>
    <div class="serp-info-strip"><i class="ph ph-shield-check"></i><div><strong>Integritas dapat diverifikasi.</strong><span>Tanda tangan saat ini adalah persetujuan elektronik internal ERP, bukan TTE tersertifikasi PSrE.</span></div></div>
    <div class="serp-info-strip free-certificate-strip">
        <i class="ph ph-seal-check"></i>
        <div>
            <strong>Generator 100% mandiri tanpa langganan.</strong>
            <span>Buat latar sertifikat dengan Canva Free atau aplikasi desain lain, ekspor sebagai PNG/JPG A4 landscape, lalu unggah di sini. Nama, nomor seri, tanda tangan, QR, dan PDF diproses ERP.</span>
        </div>
    </div>
    <div class="serp-layout certificate-config-layout">
        <div class="serp-panel" id="certificate-template-panel">
            <div class="serp-panel-head"><div><h3>Template Visual Gratis</h3><p>Sisakan area tengah untuk nama dan keterangan; hindari memasukkan data peserta langsung pada desain latar.</p></div></div>
            <form id="certificate-template-form" class="serp-form" enctype="multipart/form-data">
                <label>Nama template<input name="name" required placeholder="Contoh: Sertifikat Magang Arsitektur 2026"></label>
                <label>Latar PNG/JPG A4 landscape<input type="file" name="background" accept=".png,.jpg,.jpeg,.webp" required></label>
                <button type="submit" class="serp-btn primary"><i class="ph ph-upload-simple"></i> Unggah & Aktifkan</button>
            </form>
            <div id="certificate-template-status" class="serp-item-subtitle"></div>
        </div>
        <div class="serp-panel">
            <div class="serp-panel-head"><div><h3>Tanda Tangan Pembimbing</h3><p>Setiap pembimbing mengunggah tanda tangannya sendiri. Salinan dan hash disimpan pada sertifikat saat disahkan.</p></div></div>
            <form id="certificate-signature-form" class="serp-form" enctype="multipart/form-data">
                <label>File tanda tangan<input type="file" name="signature" accept=".png,.jpg,.jpeg,.webp" required></label>
                <label class="control-check"><input type="checkbox" name="consent" value="1" required> Saya menyetujui penggunaan tanda tangan ini hanya untuk dokumen yang saya sahkan sendiri.</label>
                <button type="submit" class="serp-btn ghost"><i class="ph ph-pen-nib"></i> Simpan Tanda Tangan Saya</button>
            </form>
            <div id="certificate-signature-status" class="serp-item-subtitle"></div>
        </div>
    </div>
    <div class="serp-layout">
        <div class="serp-panel">
            <div class="serp-panel-head"><div><h3>Arsip Sertifikat Magang</h3><p>Status draft, signed, atau revoked selalu terlihat.</p></div></div>
            <div id="document-list" class="serp-card-list"></div>
        </div>
        <aside class="serp-panel serp-form-panel" id="certificate-form-panel">
            <div class="serp-panel-head"><div><h3>Sertifikat Magang Baru</h3><p>Pastikan nama dan periode sudah benar sebelum ditandatangani.</p></div></div>
            <form id="certificate-form" class="serp-form">
                <label>Penerima<select name="owner_user_id" id="certificate-person" required></select></label>
                <label>Pembimbing / penandatangan<select name="supervisor_user_id" id="certificate-supervisor" required></select></label>
                <label>Template visual<select name="certificate_template_id" id="certificate-template-select"><option value="">Desain standar ERP</option></select></label>
                <label>Nama program<input name="program_name" required placeholder="Contoh: Internship Arsitektur Digital"></label>
                <div class="serp-form-row">
                    <label>Mulai<input type="date" name="start_date" required></label>
                    <label>Selesai<input type="date" name="end_date" required></label>
                </div>
                <div class="serp-form-row">
                    <label>Tanggal terbit<input type="date" name="issued_at" required></label>
                    <label>Penilaian<input name="performance_label" value="Baik"></label>
                </div>
                <label>Deskripsi<textarea name="description" rows="4" placeholder="Kosongkan untuk memakai kalimat standar profesional."></textarea></label>
                <button type="submit" class="serp-btn primary"><i class="ph ph-file-plus"></i> Buat Draft Sertifikat</button>
            </form>
        </aside>
    </div>
</section>

<section id="view-accounting" class="view-section strategic-view" style="display:none;">
    <div class="serp-page-head">
        <div>
            <span class="serp-eyebrow">Financial control</span>
            <h2>Akuntansi Double-Entry</h2>
            <p>Laba rugi bulanan dan evaluasi tahunan dihitung langsung dari jurnal berpasangan yang seimbang.</p>
        </div>
        <div class="serp-head-actions">
            <select id="accounting-month" class="serp-select" aria-label="Bulan laporan"></select>
            <select id="accounting-year" class="serp-select" aria-label="Tahun laporan"></select>
            <button type="button" class="serp-btn primary" id="accounting-import-open"><i class="ph ph-upload-simple"></i> Impor Transaksi</button>
            <a class="serp-btn ghost" id="accounting-import-template" href="/api/accounting/import-template"><i class="ph ph-download-simple"></i> Template CSV</a>
        </div>
    </div>
    <div class="serp-metric-grid" id="accounting-summary"></div>
    <div class="serp-analytics-grid accounting">
        <div class="serp-panel">
            <div class="serp-panel-head"><div><h3>Evaluasi 12 Bulan</h3><p>Perbandingan pendapatan, biaya, laba bersih, dan margin.</p></div></div>
            <div class="serp-table-wrap"><table class="serp-table"><thead><tr><th>Bulan</th><th>Pendapatan</th><th>Biaya</th><th>Laba Bersih</th><th>Margin</th></tr></thead><tbody id="accounting-months"></tbody></table></div>
        </div>
        <aside class="serp-panel serp-form-panel" id="transaction-form-panel">
            <div class="serp-panel-head"><div><h3>Catat Transaksi</h3><p>Sistem membuat debit–kredit otomatis.</p></div></div>
            <form id="transaction-form" class="serp-form">
                <div class="serp-form-row"><label>Tanggal<input type="date" name="date" required></label><label>Jenis<select name="kind" id="transaction-kind"><option value="revenue">Pendapatan</option><option value="expense">Biaya</option></select></label></div>
                <label>Kategori<select name="category" id="transaction-category"></select></label>
                <label>Nilai (Rp)<input type="number" name="amount" min="1" step="1" required></label>
                <label>Proyek terkait<select name="project_id" id="transaction-project"><option value="">Tanpa proyek</option></select></label>
                <label>Keterangan<textarea name="description" rows="3" required></textarea></label>
                <label>Dokumen pendukung <span class="serp-optional">opsional</span>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip">
                    <small class="serp-field-help">Bukti transfer, invoice, nota, atau dokumen lain. Maksimal 10 MB.</small>
                </label>
                <div class="serp-draft-status" id="transaction-draft-status"><i class="ph ph-cloud-check"></i> Draft isian disimpan otomatis di perangkat ini.</div>
                <button type="submit" class="serp-btn primary"><i class="ph ph-check-circle"></i> Posting Jurnal</button>
            </form>
        </aside>
    </div>
    <div class="serp-panel">
        <div class="serp-panel-head"><div><h3>Jurnal Terbaru</h3><p>Setiap transaksi harus memiliki total debit sama dengan kredit.</p></div></div>
        <div id="journal-list" class="serp-card-list compact"></div>
    </div>

    <div class="serp-modal" id="accounting-import-modal" aria-hidden="true">
        <div class="serp-modal-card accounting-import-card">
            <div class="serp-panel-head">
                <div>
                    <h3>Impor Laporan Keuangan</h3>
                    <p>Gunakan CSV agar banyak transaksi dapat membentuk jurnal double-entry sekaligus.</p>
                </div>
                <button type="button" class="serp-icon-btn" data-close-accounting-import aria-label="Tutup"><i class="ph ph-x"></i></button>
            </div>
            <form id="accounting-import-form" class="serp-form">
                <label class="serp-file-drop" for="accounting-import-file">
                    <i class="ph ph-file-csv"></i>
                    <strong>Pilih file CSV transaksi</strong>
                    <span id="accounting-import-file-name">Maksimal 500 baris dan 5 MB. Impor dibatalkan jika satu baris tidak valid.</span>
                    <input id="accounting-import-file" name="file" type="file" accept=".csv,.txt,text/csv" required>
                </label>
                <div class="serp-import-guidance">
                    <i class="ph ph-info"></i>
                    <span>Kolom wajib: tanggal, jenis, kategori, nilai, keterangan. Nomor referensi membuat impor aman dari duplikasi.</span>
                </div>
                <div class="serp-modal-actions">
                    <a class="serp-btn ghost" href="/api/accounting/import-template"><i class="ph ph-download-simple"></i> Unduh Template</a>
                    <button type="submit" class="serp-btn primary"><i class="ph ph-upload-simple"></i> Validasi & Impor</button>
                </div>
            </form>
        </div>
    </div>
</section>

<section id="view-project-costing" class="view-section strategic-view" style="display:none;">
    <div class="serp-page-head">
        <div>
            <span class="serp-eyebrow">Margin by project</span>
            <h2>Project Costing</h2>
            <p>Model ringan untuk proyek desain dan model lebih ketat untuk proyek kontraktor.</p>
        </div>
        <button type="button" class="serp-btn primary" id="project-new"><i class="ph ph-plus"></i> Proyek Baru</button>
    </div>
    <div class="serp-metric-grid" id="project-summary"></div>
    <div class="serp-layout">
        <div class="serp-panel">
            <div class="serp-panel-head"><div><h3>Portofolio Proyek</h3><p>Proyek dari transfer klien tersinkron otomatis dan dapat dilengkapi anggarannya.</p></div></div>
            <div id="project-list" class="serp-card-list"></div>
        </div>
        <aside class="serp-panel serp-form-panel" id="project-form-panel">
            <div class="serp-panel-head"><div><h3 id="project-form-title">Proyek Baru</h3><p>Isi nilai kontrak, anggaran, dan progres proyek.</p></div></div>
            <form id="project-form" class="serp-form">
                <input type="hidden" id="project-id">
                <label>Nama proyek<input name="name" required></label>
                <label>Nama klien<input name="client_name" required></label>
                <div class="serp-form-row"><label>Tipe<select name="project_type"><option value="design">Desain</option><option value="contractor">Kontraktor</option></select></label><label>Status<select name="status"><option value="planned">Direncanakan</option><option value="active" selected>Aktif</option><option value="on_hold">Ditunda</option><option value="completed">Selesai</option><option value="cancelled">Dibatalkan</option></select></label></div>
                <div class="serp-form-row"><label>Mulai<input type="date" name="start_date"></label><label>Target selesai<input type="date" name="target_end_date"></label></div>
                <div class="serp-form-row"><label>Nilai kontrak<input type="number" name="contract_value" min="0" required></label><label>Anggaran biaya<input type="number" name="budget_cost" min="0" required></label></div>
                <label>Progres (%)<input type="number" name="progress" min="0" max="100" required value="0"></label>
                <label>Catatan<textarea name="notes" rows="3"></textarea></label>
                <button type="submit" class="serp-btn primary"><i class="ph ph-floppy-disk"></i> Simpan Proyek</button>
            </form>
        </aside>
    </div>

    <div class="serp-modal" id="project-cost-modal" aria-hidden="true">
        <div class="serp-modal-card">
            <div class="serp-panel-head"><div><h3>Catat Biaya Proyek</h3><p id="project-cost-caption"></p></div><button type="button" class="serp-icon-btn" data-close-cost><i class="ph ph-x"></i></button></div>
            <form id="project-cost-form" class="serp-form">
                <input type="hidden" id="project-cost-id">
                <div class="serp-form-row"><label>Tanggal<input type="date" name="cost_date" required></label><label>Kategori<select name="category"><option value="design_labor">Tenaga Desain</option><option value="consultant">Konsultan</option><option value="material">Material</option><option value="contractor_labor">Tenaga Kontraktor</option><option value="transport">Transportasi</option><option value="permit">Perizinan</option><option value="software">Software</option><option value="other">Lainnya</option></select></label></div>
                <label>Keterangan<input name="description" required></label>
                <div class="serp-form-row"><label>Nilai (Rp)<input type="number" name="amount" min="1" required></label><label>Vendor<input name="vendor"></label></div>
                <label>Bukti biaya <span class="serp-optional">opsional</span>
                    <input type="file" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.webp,.doc,.docx,.xls,.xlsx,.csv,.txt,.zip">
                    <small class="serp-field-help">Unggah nota, invoice vendor, atau bukti pembayaran. Maksimal 10 MB.</small>
                </label>
                <button type="submit" class="serp-btn primary">Simpan dan Posting Jurnal</button>
            </form>
        </div>
    </div>
</section>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\partials\strategic-erp.blade.php ENDPATH**/ ?>