<section id="view-alumni" class="view-section strategic-view" style="display:none;">
    <div class="alumni-hero">
        <div>
            <span class="alumni-eyebrow">Suba-Arch Alumni Network</span>
            <h2>Jejak karier yang tetap terhubung</h2>
            <p>Akun alumni menggunakan email dan OTP yang sama. Data operasional lama tetap terkunci, sedangkan profil karier dan portofolio dapat diperbarui sendiri.</p>
        </div>
        <div class="alumni-hero-mark"><i class="ph ph-graduation-cap"></i></div>
    </div>

    <div class="alumni-grid">
        <form id="alumni-profile-form" class="setup-card alumni-profile-card">
            <div class="section-title-row">
                <div><span class="alumni-eyebrow">Profil Saya</span><h3>Karier & Portofolio</h3></div>
                <span id="alumni-profile-updated" class="locked-data-pill"><i class="ph ph-lock-key"></i> Riwayat kerja aman</span>
            </div>
            <div class="alumni-form-grid">
                <label>Perusahaan saat ini<input id="alumni-current-employer" maxlength="255" placeholder="Contoh: Studio Arsitektur Indonesia"></label>
                <label>Posisi saat ini<input id="alumni-current-position" maxlength="255" placeholder="Contoh: Junior Architect"></label>
                <label>Industri<input id="alumni-industry" maxlength="255" placeholder="Arsitektur / Desain / Teknologi"></label>
                <label>Kota domisili<input id="alumni-city" maxlength="255" placeholder="Bandung"></label>
                <label>LinkedIn<input id="alumni-linkedin-url" type="url" placeholder="https://linkedin.com/in/..."></label>
                <label>Portofolio<input id="alumni-portfolio-url" type="url" placeholder="https://behance.net/..."></label>
            </div>
            <label>Bio profesional<textarea id="alumni-bio" rows="4" maxlength="3000" placeholder="Ceritakan fokus keahlian dan perjalanan karier Anda."></textarea></label>
            <label>Keahlian<input id="alumni-skills" maxlength="1000" placeholder="Pisahkan dengan koma: AutoCAD, SketchUp, Copywriting"></label>
            <div class="alumni-consent-row">
                <label><input id="alumni-available" type="checkbox"> Terbuka untuk peluang profesional</label>
                <label><input id="alumni-events-opt-in" type="checkbox" checked> Bersedia menerima undangan event Suba-Arch</label>
            </div>
            <button type="submit" class="primary-btn"><i class="ph ph-floppy-disk"></i> Simpan Profil Alumni</button>
        </form>

        <div id="alumni-admin-workspace" class="setup-card alumni-admin-card">
            <div class="section-title-row">
                <div><span id="alumni-workspace-eyebrow" class="alumni-eyebrow">Pengelolaan Alumni</span><h3 id="alumni-workspace-title">Direktori & Undangan</h3></div>
                <span id="alumni-directory-count" class="badge warning">0 alumni</span>
            </div>
            <div id="alumni-directory-list" class="alumni-directory-list"></div>
            <form id="alumni-invitation-form" class="alumni-invitation-form">
                <h4><i class="ph ph-envelope-simple-open"></i> Buat Undangan Event</h4>
                <input id="alumni-event-title" maxlength="255" placeholder="Judul event" required>
                <div class="alumni-form-grid">
                    <input id="alumni-event-at" type="datetime-local" required>
                    <input id="alumni-event-location" maxlength="255" placeholder="Lokasi / link meeting">
                </div>
                <input id="alumni-registration-url" type="url" placeholder="Link registrasi (opsional)">
                <textarea id="alumni-event-message" rows="4" maxlength="5000" placeholder="Pesan undangan profesional..." required></textarea>
                <small>Secara default dikirim ke semua alumni yang mengaktifkan izin undangan. Pilih kartu alumni untuk membatasi penerima.</small>
                <button type="submit" class="primary-btn"><i class="ph ph-paper-plane-tilt"></i> Kirim Undangan Email</button>
            </form>
            <form id="alumni-announcement-form" class="alumni-invitation-form">
                <h4><i class="ph ph-megaphone"></i> Pengumuman untuk Alumni Divisi</h4>
                <input id="alumni-announcement-title" maxlength="180" placeholder="Judul pengumuman" required>
                <textarea id="alumni-announcement-message" rows="4" maxlength="5000" placeholder="Tulis informasi yang relevan untuk alumni divisi Anda..." required></textarea>
                <small id="alumni-announcement-help">Pengumuman akan tampil dan menjadi notifikasi bagi alumni pada divisi terkait.</small>
                <button type="submit" class="secondary-btn"><i class="ph ph-broadcast"></i> Terbitkan Pengumuman</button>
            </form>
        </div>
    </div>

    <div class="alumni-grid" style="margin-top:18px;">
        <div class="setup-card alumni-admin-card">
            <div class="section-title-row"><div><span class="alumni-eyebrow">Direktori</span><h3>Jaringan Alumni Suba-Arch</h3></div><span id="alumni-public-count" class="badge warning">0 alumni</span></div>
            <p style="color:var(--text-secondary);font-size:12px;">Informasi karier yang dibagikan alumni. Data profil hanya dapat diubah oleh pemilik akun.</p>
            <div id="alumni-public-directory" class="alumni-directory-list"></div>
        </div>
        <div class="setup-card alumni-admin-card">
            <div class="section-title-row"><div><span class="alumni-eyebrow">Pengumuman</span><h3>Kabar Terbaru Perusahaan</h3></div></div>
            <div id="alumni-announcement-list" class="alumni-directory-list"></div>
        </div>
    </div>
</section>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\partials\alumni-network.blade.php ENDPATH**/ ?>