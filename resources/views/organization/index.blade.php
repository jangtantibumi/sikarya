<section id="view-organization" class="view-section" style="min-height: calc(100vh - 80px); background: var(--app-bg); padding: 32px 40px; position: relative; overflow-y: auto;">
    
    <!-- Top Header -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
        <div>
            <div style="font-size: 11px; font-weight: 700; color: var(--accent); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Direktori Perusahaan</div>
            <h2 style="margin: 0; font-size: 28px; font-weight: 700; color: var(--text-heading);">Struktur Organisasi</h2>
            <p style="margin: 8px 0 0; color: var(--text-muted); font-size: 14px; max-width: 600px; line-height: 1.5;">Kenali jalur koordinasi, divisi, dan atasan langsung tanpa membuka data pribadi atau kinerja yang bukan kewenangan Anda.</p>
        </div>
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            <button class="ios-btn ios-btn-secondary" onclick="openAddDivisionPopup()"><i class="fa-solid fa-layer-group" style="color: var(--accent); margin-right:6px;"></i> Tambah Divisi</button>
            <button class="ios-btn ios-btn-secondary" onclick="openAddManagerPopup()"><i class="fa-solid fa-user-tie" style="color: var(--accent); margin-right:6px;"></i> Tetapkan Manager</button>
            <button class="ios-btn ios-btn-primary" onclick="openAddStaffPopup()"><i class="fa-solid fa-user-plus" style="margin-right:6px;"></i> Tambah Staff</button>
        </div>
    </div>

    <!-- Toolbar / Search -->
    <div style="display: flex; gap: 16px; align-items: flex-end; margin-bottom: 16px; flex-wrap: wrap;">
        <div style="position: relative; flex: 1; min-width: 250px;">
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; text-transform: uppercase;">Pencarian</div>
            <i class="fa-solid fa-search" style="position: absolute; left: 16px; top: 34px; color: var(--text-muted);"></i>
            <input type="text" id="org-search" placeholder="Cari nama atau jabatan..." style="width: 100%; padding: 12px 16px 12px 44px; background: var(--panel); border: 1px solid var(--panel-border); border-radius: 8px; color: var(--text-heading); font-size: 14px; outline: none;" onkeyup="filterOrgWorkspace()">
        </div>
        <div style="width: 250px;">
            <div style="font-size: 11px; color: var(--text-muted); font-weight: 600; margin-bottom: 4px; text-transform: uppercase;">Filter Divisi</div>
            <select id="org-filter-dept" onchange="filterOrgWorkspace()" style="width: 100%; padding: 12px 16px; background: var(--panel); border: 1px solid var(--panel-border); border-radius: 8px; color: var(--text-heading); font-size: 14px; outline: none; appearance: none; cursor: pointer;">
                <option value="">Semua divisi</option>
                <option value="Perusahaan">Perusahaan</option>
                <option value="Operasional">Operasional</option>
                <option value="Finance">Finance</option>
                <option value="HRD">HRD</option>
                <option value="Marketing">Marketing</option>
            </select>
        </div>
        <div style="padding: 10px 16px; background: rgba(12, 53, 39, 0.1); border: 1px solid rgba(12, 53, 39, 0.2); border-radius: 8px; color: var(--text-accent); font-size: 13px; font-weight: 600; display: flex; align-items: center; gap: 4px; height: 45px; box-sizing: border-box;">
            <i class="fa-solid fa-users" style="margin-right: 4px;"></i>
            <span id="active-members-count">0</span>
            <span>dari</span>
            <span id="total-members-count">0</span>
            <span>anggota aktif</span>
        </div>
    </div>

    <!-- Info Banner -->
    <div style="background: rgba(var(--accent-rgb), 0.05); border: 1px solid rgba(var(--accent-rgb), 0.2); border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; gap: 12px; margin-bottom: 32px;">
        <i class="fa-solid fa-shield-check" style="color: var(--accent); font-size: 16px;"></i>
        <span style="color: var(--text-heading); font-size: 13px; font-weight: 500;">Direktori ini hanya menampilkan informasi organisasi. Email, attendance, KPI, gaji, dan data pribadi tidak dibagikan.</span>
    </div>

    <!-- Workspace Area -->
    <div id="org-workspace-container" style="display: flex; flex-direction: column; gap: 40px; padding-bottom: 80px;">
        <div class="loader" style="margin: 60px auto; border-top-color: var(--accent);"></div>
    </div>
</section>

    <!-- POPUPS -->

    <!-- 1. Task & Performance Popup (The "Chart" Click) -->
    <div id="popup-performance" class="ios-modal-overlay" style="display:none;">
        <div class="ios-modal" style="width: 500px; max-width: 95vw;">
            <button class="ios-btn-close" onclick="closePopup('popup-performance')" style="position: absolute; right: 16px; top: 16px;"><i class="fa-solid fa-times"></i></button>
            <div style="padding: 24px;">
                <h3 id="perf-popup-title" style="margin: 0 0 4px; font-size: 18px; font-weight: 700; color: var(--text-heading);">Daftar Tugas & Performa: Loading...</h3>
                <div id="perf-popup-subtitle" style="color: var(--text-muted); font-size: 14px; margin-bottom: 12px;"></div>
                
                <div id="perf-popup-kpi" style="color: var(--text-accent); font-weight: 600; font-size: 13px; margin-bottom: 16px; display: flex; align-items: center; gap: 6px;">
                    <i class="fa-solid fa-chart-line"></i> KPIM Score: Loading...
                </div>

                <div style="display: flex; gap: 12px; margin-bottom: 24px;">
                    <button id="btn-slip-gaji" onclick="openSlipPreview()" class="ios-btn ios-btn-primary"><i class="fa-solid fa-file-invoice-dollar" style="margin-right:6px;"></i> Cetak Slip Gaji</button>
                    <button id="btn-paklaring" onclick="openPaklaringPreview()" class="ios-btn ios-btn-secondary"><i class="fa-solid fa-file-signature" style="margin-right:6px;"></i> Generate Paklaring</button>
                </div>

                <div id="perf-tasks-container" style="display: flex; flex-direction: column; gap: 12px;">
                    <!-- Tasks injected here -->
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Edit Profile Popup -->
    <div id="popup-edit" class="ios-modal-overlay" style="display:none;">
        <div class="ios-modal" style="width: 500px; max-width: 95vw; max-height: 85vh; overflow-y: auto;">
            <button class="ios-btn-close" onclick="closePopup('popup-edit')" style="position: absolute; right: 16px; top: 16px;"><i class="fa-solid fa-times"></i></button>
            <div style="padding: 24px;">
                <h3 id="edit-popup-title" style="margin: 0 0 24px; font-size: 18px; font-weight: 700; color: var(--text-heading);">Edit Profil Staf Tim</h3>
                
                <form id="edit-emp-form" onsubmit="submitEdit(event)">
                    <div class="form-group">
                        <label>NIK (Kode Pegawai)</label>
                        <input type="text" name="employee_code" class="form-control" readonly style="opacity: 0.7; cursor: not-allowed; background: var(--panel-secondary); font-family: monospace;">
                    </div>
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Email Staf (Untuk Penerimaan OTP)</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Nama Jabatan Kustom</label>
                        <input type="text" name="job_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password Baru (Kosongkan jika tidak diubah)</label>
                        <div style="padding: 12px; border: 1px solid rgba(var(--accent-rgb), 0.2); background: rgba(var(--accent-rgb), 0.05); border-radius: 8px; margin-bottom: 8px;">
                            <p style="margin:0; font-size: 12px; color: var(--text-muted); line-height: 1.4;">Login akun menggunakan OTP email. Perubahan email akan menentukan alamat tujuan OTP berikutnya.</p>
                        </div>
                        <input type="password" name="password" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Tipe Pekerjaan / Kontrak</label>
                        <select name="employment_type" class="form-control">
                            <option value="Full-Time">Full-Time</option>
                            <option value="Part-Time">Part-Time</option>
                            <option value="Contract">Contract</option>
                            <option value="Internship">Internship</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Level/Posisi (Role)</label>
                        <select name="role" class="form-control">
                            <option value="manager">🎯 Manager</option>
                            <option value="supervisor">🎯 Supervisor</option>
                            <option value="pic">🎯 PIC</option>
                            <option value="staff">🎯 Staff</option>
                        </select>
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 32px;">
                        <button type="button" class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="closePopup('popup-edit')">Batal</button>
                        <button type="submit" class="ios-btn ios-btn-primary" style="flex: 1;">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 3. Delete/Deactivate Popup -->
    <div id="popup-delete" class="ios-modal-overlay" style="display:none; z-index: 10000;">
        <div class="ios-modal" style="width: 400px; text-align: center; max-width: 95vw; border-radius: 18px; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(12px); border: 1px solid #ef4444; box-shadow: 0 20px 40px rgba(239, 68, 68, 0.15); padding: 28px;">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px;">
                <i class="fa-solid fa-user-slash"></i>
            </div>
            <h3 style="margin: 0 0 8px; font-size: 18px; font-weight: 700; color: var(--text-accent);">Nonaktifkan Akun</h3>
            <p style="margin: 0 0 24px; color: var(--text-muted); font-size: 14px; line-height: 1.5;">Anda yakin ingin menonaktifkan <strong id="del-emp-name" style="color:var(--text-heading);"></strong>? Akun ini tidak akan dapat login kembali.</p>
            
            <div style="display: flex; gap: 12px;">
                <button type="button" class="ios-btn ios-btn-secondary" style="flex: 1; background: #f3f4f6; color: #374151; font-weight: 600;" onclick="closePopup('popup-delete')">Batal</button>
                <button type="button" class="ios-btn" style="flex: 1; background: #ef4444; color: white; border: none; font-weight: 600;" onclick="submitDelete()">Nonaktifkan</button>
            </div>
        </div>
    </div>

    <!-- 3.1. Delete Division Popup -->
    <div id="popup-delete-division" class="ios-modal-overlay" style="display:none; z-index: 10000;">
        <div class="ios-modal" style="width: 400px; text-align: center; max-width: 95vw; border-radius: 18px; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(12px); border: 1px solid #ef4444; box-shadow: 0 20px 40px rgba(239, 68, 68, 0.15); padding: 28px;">
            <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px;">
                <i class="fa-solid fa-trash"></i>
            </div>
            <h3 style="margin: 0 0 8px; font-size: 18px; font-weight: 700; color: var(--text-accent);">Hapus Divisi</h3>
            <p style="margin: 0 0 24px; color: var(--text-muted); font-size: 14px; line-height: 1.5;">Anda yakin ingin menghapus divisi <strong id="del-org-div-name" style="color:var(--text-heading);"></strong>?</p>
            
            <div style="display: flex; gap: 12px;">
                <button type="button" class="ios-btn ios-btn-secondary" style="flex: 1; background: #f3f4f6; color: #374151; font-weight: 600;" onclick="closePopup('popup-delete-division')">Batal</button>
                <button type="button" class="ios-btn" style="flex: 1; background: #ef4444; color: white; border: none; font-weight: 600;" onclick="submitDeleteDivision()">Hapus Divisi</button>
            </div>
        </div>
    </div>

    <!-- 4. Add Staff Popup -->
    <div id="popup-add-staff" class="ios-modal-overlay" style="display:none;">
        <div class="ios-modal" style="width: 500px; max-width: 95vw; max-height: 85vh; overflow-y: auto;">
            <button class="ios-btn-close" onclick="closePopup('popup-add-staff')" style="position: absolute; right: 16px; top: 16px;"><i class="fa-solid fa-times"></i></button>
            <div style="padding: 24px;">
                <h3 style="margin: 0 0 24px; font-size: 18px; font-weight: 700; color: var(--text-heading);">Tambah Staff Baru</h3>
                <form onsubmit="submitAddStaff(event)">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="name" id="add-staff-name" class="form-control" placeholder="Contoh: Budi Santoso" required oninput="updateGeneratedFields()">
                    </div>
                    
                    <div class="form-group">
                        <label>Username Login Otomatis</label>
                        <input type="text" name="username" id="add-staff-username" class="form-control" readonly style="opacity: 0.7; cursor: not-allowed; background: var(--panel-secondary);">
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px; line-height: 1.4;">Format: perusahaan.divisi.level.nama.nomor. Username merekam posisi awal dan tetap agar jejak audit tidak rusak.</div>
                    </div>
                    
                    <div class="form-group">
                        <label>Kode Pegawai Otomatis</label>
                        <input type="text" name="employee_code" id="add-staff-code" class="form-control" readonly style="opacity: 0.7; cursor: not-allowed; background: var(--panel-secondary);">
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px; line-height: 1.4;">Kode menunjukkan perusahaan, divisi, level, dan nomor urut akun.</div>
                    </div>

                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="e.g. budi@suba-arch.co.id" required>
                    </div>

                    <div class="form-group">
                        <label>Nama Jabatan Kustom</label>
                        <input type="text" name="job_title" class="form-control" placeholder="Contoh: Content Creator" required>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px; line-height: 1.4;">Level dan divisi tetap ditentukan sistem; nama jabatan dapat disesuaikan.</div>
                    </div>

                    <div style="padding: 12px; border: 1px solid rgba(var(--accent-rgb), 0.2); background: rgba(var(--accent-rgb), 0.05); border-radius: 8px; margin-bottom: 20px;">
                        <p style="margin:0; font-size: 12px; color: var(--text-muted); line-height: 1.4;"><i class="fa-solid fa-info-circle" style="color: var(--accent); margin-right: 6px;"></i> Akun akan masuk menggunakan OTP yang dikirim ke email di atas. Sistem tidak membuat password default.</p>
                    </div>

                    <div class="form-group">
                        <label>Tipe Pekerjaan / Kontrak</label>
                        <select name="employment_type" class="form-control">
                            <option value="Full-Time">Full-Time</option>
                            <option value="Part-Time">Part-Time</option>
                            <option value="Contract">Contract</option>
                            <option value="Internship">Internship</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Role</label>
                        <select name="role" id="add-staff-role" class="form-control" required onchange="updateGeneratedFields()">
                            <option value="manager">🎯 Manager</option>
                            <option value="supervisor">🎯 Supervisor</option>
                            <option value="pic">🎯 PIC</option>
                            <option value="staff">🎯 Staff</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Divisi / Departemen</label>
                        <select name="division" id="add-staff-division" class="form-control" required onchange="updateGeneratedFields()">
                            <option value="">-- Pilih Divisi --</option>
                            @foreach(\App\Models\CompanyDivision::where('company_id', $company->id ?? 1)->orderBy('order')->get() as $div)
                                <option value="{{ $div->name }}">{{ $div->name }}</option>
                            @endforeach
                        </select>
                        <div style="font-size: 11px; color: var(--text-muted); margin-top: 6px; line-height: 1.4;">Terisi otomatis jika menekan tombol dari dalam kotak divisi, atau pilih manual.</div>
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 32px;">
                        <button type="button" class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="closePopup('popup-add-staff')">Cancel</button>
                        <button type="submit" class="ios-btn ios-btn-primary" style="flex: 1;">Simpan / Ajukan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 5. Add Manager Popup -->
    <div id="popup-add-manager" class="ios-modal-overlay" style="display:none;">
        <div class="ios-modal" style="width: 500px; max-width: 95vw;">
            <button class="ios-btn-close" onclick="closePopup('popup-add-manager')" style="position: absolute; right: 16px; top: 16px;"><i class="fa-solid fa-times"></i></button>
            <div style="padding: 24px;">
                <h3 style="margin: 0 0 24px; font-size: 18px; font-weight: 700; color: var(--text-heading); display: flex; align-items: center; gap: 8px;">
                    <i class="fa-solid fa-crown" style="color: var(--accent);"></i> Appoint New Manager
                </h3>
                
               <form onsubmit="submitAddManager(event)" id="form-appoint-manager">

    <div class="form-group">

        <label>Pilih Anggota Staff (Promosi)</label>

        <select
            id="mgr-emp-username"
            class="form-control"
            required>

            <option value="">
                -- Pilih Staff --
            </option>

        </select>

    </div>

    <div class="form-group">

        <label>Tempatkan Sebagai Manager Divisi</label>

        <select
            id="mgr-target-division"
            class="form-control"
            onchange="populateManagerCandidates()"
            required>

            <option value="">
                -- Pilih Divisi --
            </option>

            <option value="Marketing">
                Marketing
            </option>

            <option value="Finance">
                Finance
            </option>

            <option value="HRD">
                HRD
            </option>

            <option value="Operasional">
                Operasional
            </option>

            <option value="Perusahaan">
                Perusahaan
            </option>

        </select>

    </div>

    <input
        type="hidden"
        id="mgr-division"
        name="division">

    <div style="display:flex; gap:12px; margin-top:32px;">
                        <button type="button" class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="closePopup('popup-add-manager')">Batal</button>
                        <button type="submit" class="ios-btn ios-btn-primary" style="flex: 1;">Tunjuk Manager</button>
                    </div>
                </form>

                <div style="margin-top: 24px; text-align: center; position: relative;">
                    <hr style="border-color: var(--panel-border); margin: 0;">
                    <span style="position: absolute; top: -10px; left: 50%; transform: translateX(-50%); background: var(--panel); padding: 0 12px; color: var(--text-muted); font-size: 12px; font-weight: 600;">ATAU</span>
                </div>

                <div style="margin-top: 24px;">
                    <button type="button" class="ios-btn ios-btn-secondary" style="width: 100%; border: 1px dashed var(--accent); color: var(--accent);" onclick="openNewManagerForm()">
                        <i class="fa-solid fa-user-plus" style="margin-right: 8px;"></i> Tambah Orang Baru (Rekrut)
                    </button>
                    <div style="font-size: 11px; color: var(--text-muted); text-align: center; margin-top: 8px;">Otomatis akan terposisikan pada bagan hirarki sebagai manager</div>
                </div>
            </div>
        </div>
    </div>

    <!-- 6. Add Division Popup -->
    <div id="popup-add-division" class="ios-modal-overlay" style="display:none; z-index: 10001; position: fixed; inset: 0; background: rgba(0,0,0,0.5); justify-content: center; align-items: center;">
        <div class="modal-content ios-modal" style="width: 400px; max-width: 95vw; border-radius: 18px; background: rgba(255, 255, 255, 0.96); backdrop-filter: blur(12px); border: 1px solid var(--panel-border); padding: 28px;">
            <div style="text-align: left;">
                <h3 style="margin: 0 0 16px 0; font-size: 18px; font-weight: 700; color: var(--text-accent);"><i class="fa-solid fa-users" style="color: var(--accent); margin-right: 8px;"></i> Tambah Divisi Baru</h3>
                <form onsubmit="submitAddDivision(event)">
                    <div class="form-group" style="margin-bottom: 16px;">
                        <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; display: block;">Nama Divisi</label>
                        <input type="text" id="div-name" class="form-control" placeholder="Contoh: Marketing" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--panel-border); font-size: 15px;" required>
                    </div>
                    <div class="form-group" style="margin-bottom: 24px;">
                        <label style="font-size: 13px; font-weight: 600; color: var(--text-muted); margin-bottom: 8px; display: block;">Kode Divisi (Opsional)</label>
                        <input type="text" id="div-code" class="form-control" placeholder="Contoh: MKT" style="width: 100%; padding: 12px; border-radius: 8px; border: 1px solid var(--panel-border); font-size: 15px;">
                    </div>
                    <div style="display: flex; gap: 12px;">
                        <button type="button" class="ios-btn ios-btn-secondary" style="flex: 1; background: #f3f4f6; color: #374151; font-weight: 600;" onclick="closePopup('popup-add-division')">Batal</button>
                        <button type="submit" class="ios-btn ios-btn-primary" style="flex: 1; font-weight: 600;">Simpan Divisi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 7. Replace Manager Confirmation Popup -->
    <div id="popup-replace-manager" class="ios-modal-overlay" style="display:none; z-index: 10000;">
        <div class="ios-modal" style="width: 450px; max-width: 95vw; text-align: center;">
            <button class="ios-btn-close" onclick="closePopup('popup-replace-manager')" style="position: absolute; right: 16px; top: 16px;"><i class="fa-solid fa-times"></i></button>
            <div style="padding: 32px 24px 24px;">
                <div style="width: 64px; height: 64px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 28px; margin: 0 auto 16px;">
                    <i class="fa-solid fa-code-branch"></i>
                </div>
                <h3 style="margin: 0 0 16px; font-size: 18px; font-weight: 700; color: var(--text-heading);">Konfirmasi Penggantian Manager</h3>
                
                <div style="text-align: left; background: var(--panel-secondary); padding: 16px; border-radius: 8px; margin-bottom: 24px; font-size: 14px; line-height: 1.5;">
                    <p style="margin: 0 0 8px;">Divisi <strong><span id="conflict-division"></span></strong> saat ini sudah memiliki manager aktif:</p>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 8px;">
                        <span style="color: var(--text-muted);">Manager Saat Ini:</span>
                        <strong id="conflict-old-manager" style="color: #ef4444;"></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between;">
                        <span style="color: var(--text-muted);">Kandidat Baru:</span>
                        <strong id="conflict-new-manager" style="color: var(--accent);"></strong>
                    </div>
                </div>

                <p style="margin: 0 0 24px; color: var(--text-muted); font-size: 13px; line-height: 1.5;">Jika Anda melanjutkan, Manager saat ini akan diturunkan jabatannya menjadi <strong>Staff</strong> di divisi yang sama.</p>
                
                <div style="display: flex; gap: 12px;">
                    <button type="button" class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="closePopup('popup-replace-manager')">Batal</button>
                    <button type="button" class="ios-btn" style="flex: 1; background: #ef4444; color: white; border: none;" onclick="confirmReplaceManager()">Gantikan Manager</button>
                </div>
            </div>
        </div>
    </div>

    <!-- 8. Slip Gaji Preview Popup -->
    <div id="popup-slip-preview" class="ios-modal-overlay" style="display:none; z-index: 9999;">
        <div class="ios-modal" style="width: 600px; max-width: 95vw; max-height: 90vh; overflow-y: auto;">
            <button class="ios-btn-close" onclick="closePopup('popup-slip-preview')" style="position: absolute; right: 16px; top: 16px;"><i class="fa-solid fa-times"></i></button>
            <div style="padding: 24px;">
                <h3 style="margin: 0 0 8px; font-size: 18px; font-weight: 700; color: var(--text-heading);"><i class="fa-solid fa-file-invoice-dollar" style="color: var(--text-accent); margin-right: 8px;"></i> Preview Slip Gaji</h3>
                <p style="margin: 0 0 24px; color: var(--text-muted); font-size: 13px;">Ubah data berikut jika diperlukan sebelum PDF dibuat. Data ini hanya untuk cetak dan tidak mengubah database.</p>
                
                <form id="form-slip-generate" onsubmit="submitSlipGenerate(event)">
                    <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Nama Lengkap</label>
                            <input type="text" name="name" id="slip-name" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Jabatan</label>
                            <input type="text" name="job_title" id="slip-job" class="form-control" required>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Divisi</label>
                            <input type="text" name="division" id="slip-div" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Nama HR / Penandatangan</label>
                            <input type="text" name="signature" value="HR Department" class="form-control" required>
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Gaji Pokok (Rp)</label>
                            <input type="number" name="base_amount" id="slip-base" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Total Gaji Bersih (Rp)</label>
                            <input type="number" name="net_amount" id="slip-net" class="form-control" required>
                        </div>
                    </div>

                    <input type="hidden" name="period_start" id="slip-period-start">
                    <input type="hidden" name="period_end" id="slip-period-end">

                    <div style="display: flex; gap: 12px; margin-top: 32px;">
                        <button type="button" class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="closePopup('popup-slip-preview')">Batal</button>
                        <button type="submit" class="ios-btn ios-btn-primary" style="flex: 1;"><i class="fa-solid fa-download" style="margin-right: 8px;"></i> Generate PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 9. Paklaring Preview Popup -->
    <div id="popup-paklaring-preview" class="ios-modal-overlay" style="display:none; z-index: 9999;">
        <div class="ios-modal" style="width: 600px; max-width: 95vw; max-height: 90vh; overflow-y: auto;">
            <button class="ios-btn-close" onclick="closePopup('popup-paklaring-preview')" style="position: absolute; right: 16px; top: 16px;"><i class="fa-solid fa-times"></i></button>
            <div style="padding: 24px;">
                <h3 style="margin: 0 0 8px; font-size: 18px; font-weight: 700; color: var(--text-heading);"><i class="fa-solid fa-file-signature" style="color: var(--accent); margin-right: 8px;"></i> Preview Paklaring</h3>
                <p style="margin: 0 0 24px; color: var(--text-muted); font-size: 13px;">Ubah data berikut sebelum mengunduh Surat Keterangan Kerja (Paklaring).</p>
                
                <form id="form-paklaring-generate" onsubmit="submitPaklaringGenerate(event)">
                    <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Nama Karyawan</label>
                            <input type="text" name="name" id="pak-name" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>NIK / Employee Code</label>
                            <input type="text" name="employee_code" id="pak-code" class="form-control" required>
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Jabatan Terakhir</label>
                            <input type="text" name="job_title" id="pak-job" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Divisi</label>
                            <input type="text" name="division" id="pak-div" class="form-control" required>
                        </div>
                    </div>

                    <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Tanggal Mulai Kerja</label>
                            <input type="text" name="join_date" id="pak-join" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Tanggal Selesai Kerja</label>
                            <input type="text" name="resign_date" id="pak-resign" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group" style="margin-bottom: 16px;">
                        <label>Teks Keterangan (Isi Surat)</label>
                        <textarea name="content" id="pak-content" class="form-control" rows="4" required></textarea>
                    </div>

                    <div style="display: flex; gap: 16px; margin-bottom: 16px;">
                        <div class="form-group" style="flex: 1;">
                            <label>Nama Perusahaan</label>
                            <input type="text" name="company_name" id="pak-company" class="form-control" required>
                        </div>
                        <div class="form-group" style="flex: 1;">
                            <label>Penandatangan (HR)</label>
                            <input type="text" name="hr_name" id="pak-hr" class="form-control" required>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 32px;">
                        <button type="button" class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="closePopup('popup-paklaring-preview')">Batal</button>
                        <button type="submit" class="ios-btn ios-btn-primary" style="flex: 1;"><i class="fa-solid fa-download" style="margin-right: 8px;"></i> Generate PDF</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- UI Overlay for Global Status -->
    <div id="global-loading" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99999; justify-content: center; align-items: center; flex-direction: column; color: white;">
        <div class="loader" style="border-top-color: var(--accent); width: 40px; height: 40px; margin-bottom: 16px;"></div>
        <div style="font-weight: 600; font-size: 16px; letter-spacing: 1px;">MEMPROSES...</div>
    </div>
    
    <!-- Success Modal -->
    <div id="global-success" class="ios-modal-overlay" style="display: none; z-index: 99999; background: rgba(0,0,0,0.6);">
        <div class="ios-modal" style="width: 350px; text-align: center; padding: 40px 24px; animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(12, 53, 39, 0.1); color: var(--text-accent); display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 24px;">
                <i class="fa-solid fa-check"></i>
            </div>
            <h3 style="margin: 0 0 12px; font-size: 22px; font-weight: 700; color: var(--text-heading);">Berhasil!</h3>
            <p id="success-msg" style="margin: 0 0 32px; color: var(--text-muted); font-size: 15px; line-height: 1.5;"></p>
            <button class="ios-btn ios-btn-primary" style="width: 100%;" onclick="closeSuccessModal()">Tutup</button>
        </div>
    </div>

    <!-- Error Modal -->
    <div id="global-error" class="ios-modal-overlay" style="display: none; z-index: 99999; background: rgba(0,0,0,0.6);">
        <div class="ios-modal" style="width: 350px; text-align: center; padding: 40px 24px; animation: popIn 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
            <div style="width: 80px; height: 80px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); color: #ef4444; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 24px;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <h3 style="margin: 0 0 12px; font-size: 22px; font-weight: 700; color: var(--text-heading);">Oops, Gagal</h3>
            <p id="error-msg" style="margin: 0 0 32px; color: var(--text-muted); font-size: 15px; line-height: 1.5;"></p>
            <button class="ios-btn" style="width: 100%; background: #ef4444; color: white; border: none;" onclick="closeErrorModal()">Tutup</button>
        </div>
    </div>

<style>
    /* Forms inside popups using standard variables */
    .form-group { margin-bottom: 20px; }
    .form-group label { display: block; font-size: 12px; font-weight: 500; color: var(--text-muted); margin-bottom: 8px; }
    .form-control { width: 100%; padding: 12px 14px; background: var(--panel); border: 1px solid var(--panel-border); border-radius: 8px; color: var(--text-heading); font-size: 14px; font-family: inherit; box-sizing: border-box; outline: none; transition: border-color 0.2s; }
    .form-control:focus { border-color: var(--accent); }

    /* Button variants */
    .suba-btn-glow-green { background: rgba(12, 53, 39, 0.1); color: var(--text-accent); border: 1px solid rgba(12, 53, 39, 0.2); padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; box-shadow: 0 0 15px rgba(12, 53, 39, 0.15); transition: all 0.2s; }
    .suba-btn-glow-green:hover { background: rgba(12, 53, 39, 0.2); box-shadow: 0 0 20px rgba(12, 53, 39, 0.3); }
    
    .suba-btn-glow-accent { background: rgba(var(--accent-rgb), 0.1); color: var(--accent); border: 1px solid rgba(var(--accent-rgb), 0.2); padding: 8px 16px; border-radius: 8px; font-size: 12px; font-weight: 600; cursor: pointer; box-shadow: 0 0 15px rgba(var(--accent-rgb), 0.15); transition: all 0.2s; }
    .suba-btn-glow-accent:hover { background: rgba(var(--accent-rgb), 0.2); box-shadow: 0 0 20px rgba(var(--accent-rgb), 0.3); }

    /* Organigram Layout (Native Colors) */
    .org-cards-container { 
        display: flex; gap: 24px; overflow-x: auto; padding-bottom: 24px; justify-content: center;
        scrollbar-width: thin; scrollbar-color: var(--panel-border) transparent; flex-wrap: wrap;
    }
    .org-cards-container::-webkit-scrollbar { height: 8px; }
    .org-cards-container::-webkit-scrollbar-track { background: transparent; }
    .org-cards-container::-webkit-scrollbar-thumb { background-color: var(--panel-border); border-radius: 10px; }

    .org-cards-container-vertical {
        display: flex; flex-direction: column; gap: 16px; padding: 24px;
        align-items: center;
    }

    .org-division-box {
        background: var(--panel);
        border: 1px solid var(--panel-border);
        border-radius: 16px;
        overflow: hidden;
        margin-bottom: 24px;
    }

    .org-division-header {
        padding: 16px 24px; 
        cursor: pointer; 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        background: rgba(var(--accent-rgb), 0.02);
        border-bottom: 1px solid var(--panel-border);
    }

    .org-card-native {
        background: var(--panel);
        border: 1px solid var(--panel-border);
        border-radius: 16px;
        padding: 24px 20px;
        min-width: 300px;
        max-width: 320px;
        flex-shrink: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        position: relative;
        cursor: pointer;
        transition: all 0.2s;
    }
    .org-card-native:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
    }
    
    /* Glow for Roles */
    .org-card-native.glow-accent {
        border-color: rgba(var(--accent-rgb), 0.4);
        box-shadow: 0 0 25px rgba(var(--accent-rgb), 0.1);
    }
    .org-card-native.glow-manager {
        border-color: rgba(12, 53, 39, 0.4);
        box-shadow: 0 0 15px rgba(12, 53, 39, 0.1);
    }
    .org-card-native.glow-manager:hover {
        border-color: rgba(12, 53, 39, 0.8);
        box-shadow: 0 0 25px rgba(12, 53, 39, 0.2);
    }
    .org-card-native.glow-staff {
        border-color: rgba(59, 130, 246, 0.4);
        box-shadow: 0 0 15px rgba(59, 130, 246, 0.1);
    }
    .org-card-native.glow-staff:hover {
        border-color: rgba(59, 130, 246, 0.8);
        box-shadow: 0 0 25px rgba(59, 130, 246, 0.2);
    }

    .suba-avatar-native {
        width: 60px; height: 60px; border-radius: 50%; font-size: 22px; font-weight: 700; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: white;
    }
    
    .suba-badges-row-native { display: flex; gap: 6px; flex-wrap: wrap; justify-content: center; margin: 16px 0; }
    .suba-badge-native { padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 600; }
    
    .suba-card-footer-native {
        display: flex; width: 100%; gap: 8px; margin-top: 16px;
    }
    .suba-btn-card-native {
        flex: 1; padding: 10px; background: transparent; border: 1px solid var(--panel-border); border-radius: 8px; color: var(--text-muted); font-size: 13px; font-weight: 600; cursor: pointer; transition: all 0.2s;
    }
    .suba-btn-card-native:hover { background: var(--panel-secondary); color: var(--text-heading); }
    .suba-btn-card-native.danger { color: #ef4444; border-color: rgba(239, 68, 68, 0.3); }
    .suba-btn-card-native.danger:hover { background: rgba(239, 68, 68, 0.1); border-color: #ef4444; }

    /* Task Item */
    .task-item-native {
        background: var(--panel-secondary); border: 1px solid var(--panel-border); border-radius: 8px; padding: 16px; display: flex; justify-content: space-between; align-items: center;
    }
</style>

<script>
    let workspaceData = [];
    
    
    
    async function loadOrgWorkspace() {
        try {
            const res = await fetch('/organization/tree');
            const data = await res.json();
            workspaceData = Array.isArray(data) ? data : (data.users ? data.users : (data.people ? Object.values(data.people) : []));
            window.allDivisions = data.divisions || [];
            document.getElementById('total-members-count').innerText = workspaceData.length;
            document.getElementById('active-members-count').innerText = workspaceData.length;
            renderWorkspace();
        } catch (e) {
            console.error(e);
            document.getElementById('org-workspace-container').innerHTML =
                '<div style="padding:24px;color:#ef4444">Gagal memuat data organisasi.</div>';
        }
    }

    function toggleDept(deptId) {
        const el = document.getElementById(deptId);
        const icon = document.getElementById(deptId + '-icon');
        if(el.style.display === 'none') {
            el.style.display = 'flex';
            icon.style.transform = 'rotate(180deg)';
        } else {
            el.style.display = 'none';
            icon.style.transform = 'rotate(0deg)';
        }
    }

    function renderWorkspace() {
        const container = document.getElementById('org-workspace-container');
        const searchTerm = document.getElementById('org-search').value.toLowerCase();
        const deptFilter = document.getElementById('org-filter-dept').value;
        
        let departments = {};
        
        if (window.allDivisions) {
            window.allDivisions.forEach(div => {
                departments[div] = { superadmins: [], ceos: [], managers: [], supervisors: [], pics: [], staff: [] };
            });
        }
        
        workspaceData.forEach(user => {
            if (deptFilter && (user.department || user.division_label || user.division || 'Perusahaan') !== deptFilter) return;
            const matchSearch = user.name.toLowerCase().includes(searchTerm) || (user.positionName || user.job_title || '-').toLowerCase().includes(searchTerm) || user.role.toLowerCase().includes(searchTerm);
            if (searchTerm && !matchSearch) return;
            
            let dept = (user.department || user.division_label || user.division || 'Perusahaan') || 'Perusahaan';
            if (!departments[dept]) departments[dept] = { superadmins: [], ceos: [], managers: [], supervisors: [], pics: [], staff: [] };
            
            const role = user.role ? user.role.toLowerCase() : '';
            const tags = user.tags || [];

            if (role === 'superadmin' || role === 'super_admin') {
                departments[dept].superadmins.push(user);
            } else if (role === 'ceo') {
                departments[dept].ceos.push(user);
            } else if (role === 'manager' || tags.includes('manager')) {
                departments[dept].managers.push(user);
            } else if (role === 'supervisor' || tags.includes('supervisor')) {
                departments[dept].supervisors.push(user);
            } else if (role === 'pic' || tags.includes('pic')) {
                departments[dept].pics.push(user);
            } else {
                departments[dept].staff.push(user);
            }
        });
        
        let html = '';
        const deptKeys = Object.keys(departments).sort((a,b) => a === 'Perusahaan' ? -1 : 1);
        
        if (deptKeys.length === 0) {
            container.innerHTML = `<div style="text-align:center; padding: 60px; color: var(--text-muted);">Tidak ada anggota yang ditemukan.</div>`;
            return;
        }
        
        // Render all divisions in vertically stacked boxes
        html += `<div style="display: flex; flex-direction: column; gap: 24px; align-items: stretch;">`;
        deptKeys.forEach(dept => {
            const group = departments[dept];
            const deptId = 'dept-' + dept.replace(/s+/g, '-').toLowerCase();
            const totalMembers = group.superadmins.length + group.ceos.length + group.managers.length + group.supervisors.length + group.pics.length + group.staff.length;
            
            // Render divisions whether empty or not
            let deleteBtnHtml = `<button onclick="confirmOrgDeleteDivision('${dept}')" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.2); color: #ef4444; cursor: pointer; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;"><i class="fa-solid fa-trash"></i> Hapus Divisi</button>`;

            html += `
            <div class="org-division-box" style="background: var(--panel); border: 1px solid var(--panel-border); border-radius: 12px; overflow: hidden; margin-bottom: 16px;">
                <div class="org-division-header" style="background: var(--panel-secondary); padding: 16px 20px; display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--panel-border);">
                    <div onclick="toggleDept('${deptId}')" style="cursor: pointer; flex: 1; font-size: 15px; font-weight: 700; color: var(--text-heading); text-transform: uppercase; letter-spacing: 1px; display: flex; align-items: center; gap: 8px;">
                        <i class="fa-solid fa-users" style="color: var(--accent);"></i> ${dept}
                        <i id="${deptId}-icon" class="fa-solid fa-chevron-down" style="color: var(--text-muted); transition: transform 0.3s; transform: rotate(0deg); margin-left: 8px;"></i>
                    </div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <span class="suba-badge-native" style="background: rgba(var(--accent-rgb), 0.1); color: var(--accent); padding: 6px 12px; border-radius: 20px;">${totalMembers} Karyawan</span>
                        ${dept !== 'Perusahaan' ? deleteBtnHtml : ''}
                    </div>
                </div>
                
                <div id="${deptId}" style="display: none; flex-direction: column; padding: 32px 24px; position: relative;">
            `;

            if (totalMembers === 0) {
                html += `
                    <div style="text-align: center; color: var(--text-muted); font-size: 14px; padding: 32px; border: 2px dashed var(--panel-border); border-radius: 12px; background: rgba(0,0,0,0.02); display: flex; flex-direction: column; gap: 12px; align-items: center;">
                        <i class="fa-solid fa-folder-open" style="font-size: 32px; opacity: 0.5;"></i>
                        Tidak ada anggota di divisi ini. Tambahkan anggota pertama!
                    </div>
                `;
            } else {
                html += `
                    <div style="display: flex; flex-direction: column; align-items: center; gap: 32px; position: relative;">
                        
                        <!-- Superadmins & CEOs -->
                        ${(group.superadmins.length > 0 || group.ceos.length > 0) ? `
                        <div style="display: flex; flex-wrap: wrap; gap: 16px; justify-content: center; z-index: 2;">
                            ${group.superadmins.map(u => renderCard(u, 'superadmin', false)).join('')}
                            ${group.ceos.map(u => renderCard(u, 'ceo', false)).join('')}
                        </div>
                        <div style="width: 2px; height: 32px; background: var(--panel-border); margin: -32px 0; z-index: 1;"></div>
                        ` : ''}

                        <!-- Managers -->
                        ${group.managers.length > 0 ? `
                        <div style="display: flex; flex-wrap: wrap; gap: 16px; justify-content: center; z-index: 2;">
                            ${group.managers.map(u => renderCard(u, 'manager', false)).join('')}
                        </div>
                        <div style="width: 2px; height: 32px; background: var(--panel-border); margin: -32px 0; z-index: 1;"></div>
                        ` : ''}

                        <!-- Supervisors -->
                        ${group.supervisors.length > 0 ? `
                        <div style="display: flex; flex-wrap: wrap; gap: 16px; justify-content: center; z-index: 2;">
                            ${group.supervisors.map(u => renderCard(u, 'supervisor', false)).join('')}
                        </div>
                        <div style="width: 2px; height: 32px; background: var(--panel-border); margin: -32px 0; z-index: 1;"></div>
                        ` : ''}

                        <!-- PICs -->
                        ${group.pics.length > 0 ? `
                        <div style="display: flex; flex-wrap: wrap; gap: 16px; justify-content: center; z-index: 2;">
                            ${group.pics.map(u => renderCard(u, 'pic', false)).join('')}
                        </div>
                        <div style="width: 2px; height: 32px; background: var(--panel-border); margin: -32px 0; z-index: 1;"></div>
                        ` : ''}

                        <!-- Staff -->
                        ${group.staff.length > 0 ? `
                        <div style="display: flex; flex-wrap: wrap; gap: 16px; justify-content: center; z-index: 2; border-top: 2px solid var(--panel-border); padding-top: 32px; width: 80%;">
                            ${group.staff.map(u => renderCard(u, 'staff', false)).join('')}
                        </div>
                        ` : ''}
                    </div>
                `;
            }

            html += `
                    <!-- Add Member Actions -->
                    <div style="display: flex; gap: 12px; margin-top: 48px; justify-content: center; border-top: 1px dashed var(--panel-border); padding-top: 24px;">
                        <button onclick="openAddManagerPopup('${dept}')" class="ios-btn ios-btn-secondary"><i class="fa-solid fa-crown" style="margin-right: 6px;"></i> Tambah Posisi Atas</button>
                        <button onclick="openAddStaffPopup('${dept}')" class="ios-btn ios-btn-primary"><i class="fa-solid fa-user-plus" style="margin-right: 6px;"></i> Tambah Staf</button>
                    </div>
                </div>
            </div>
            `;
        });
        html += `</div>`;

        container.innerHTML = html;
        
        // Remove trailing connector lines visually by hiding the last connector block in each division
        document.querySelectorAll('.org-division-box').forEach(box => {
            const connectors = box.querySelectorAll('div[style*="width: 2px"]');
            if(connectors.length > 0 && box.querySelector('div[style*="border-top: 2px solid"]') === null) {
                // If there is no staff level (which has border-top), hide the last dangling connector
                connectors[connectors.length - 1].style.display = 'none';
            }
        });
    }
    
    function getInitials(name) {
        return name.split(' ').slice(0,2).map(n => n[0]).join('').toUpperCase();
    }

    function renderCard(user, tier, fullWidth = false) {
        const isSuperAdmin = user.role === 'superadmin' || user.role === 'super_admin';
        const isCEO = user.role === 'ceo';
        const isManager = user.tags && user.tags.includes('manager');
        
        let glowClass = 'glow-staff';
        if (isManager) glowClass = 'glow-manager';
        if (isSuperAdmin || isCEO) glowClass = 'glow-accent';

        const avatarBg = 'var(--accent)';
        const initials = getInitials(user.name);
        const styleBlock = fullWidth ? 'width: 100%; max-width: none;' : 'flex: 1; min-width: 300px; max-width: 48%;';
        
        let displayRole = user.role.toUpperCase();
        if(isSuperAdmin) displayRole = "SUPERADMIN";
        else if(isCEO) displayRole = "CEO";
        
        return `
        <div class="org-card-native ${glowClass}" onclick="openPerformancePopup('${user.id}')" style="${styleBlock} background: var(--panel); border: 1px solid var(--panel-border); border-radius: 16px; padding: 24px;">
            <div class="suba-avatar-native" style="background: ${avatarBg}; width: 64px; height: 64px; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700; margin: 0 auto 16px;">
                ${initials}
            </div>
            <div style="font-size: 16px; font-weight: 700; color: var(--text-heading); margin-bottom: 4px; text-align: center;">${user.name}</div>
            <div style="font-size: 13px; color: var(--text-muted); text-align: center;">${(user.positionName || user.job_title || '-')}</div>
            <div style="font-size: 11px; color: var(--text-muted); margin-top: 4px; text-align: center; font-family: monospace; opacity: 0.8;"><i class="fa-solid fa-id-card" style="margin-right: 4px;"></i>${user.employee_code || '-'}</div>
            
            <div class="suba-badges-row-native" style="display: flex; gap: 8px; justify-content: center; margin: 16px 0;">
                <span class="suba-badge-native" style="color: var(--accent); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; border: 1px solid rgba(var(--accent-rgb), 0.3); background: rgba(var(--accent-rgb), 0.1);">${(user.department || user.division_label || user.division || 'Perusahaan')}</span>
                <span class="suba-badge-native" style="background: var(--panel-secondary); color: var(--text-muted); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; border: 1px solid var(--panel-border);">${displayRole}</span>
                <span class="suba-badge-native" style="background: var(--panel-secondary); color: var(--text-muted); padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; border: 1px solid var(--panel-border);">${user.employment_type || 'Full-Time'}</span>
            </div>
            
            <div style="font-size: 11px; color: var(--text-muted); margin-top: auto; padding-top: 16px; border-top: 1px dashed var(--panel-border); width: 100%; text-align: center;">
                <i class="fa-solid fa-arrow-turn-up" style="transform: rotate(90deg); margin-right: 4px;"></i> Atasan: ${(isSuperAdmin || isCEO) ? 'Owner' : (isManager ? 'Moch. Restu Subagya' : 'Manager Divisi')}
            </div>
            
            <div class="suba-card-footer-native" style="display: flex; gap: 12px; margin-top: 16px; width: 100%;" onclick="event.stopPropagation()">
                <button class="ios-btn ios-btn-secondary" style="flex: 1;" onclick="openEditPopup('${user.id}')"><i class="fa-solid fa-pen" style="margin-right:6px;"></i> Edit</button>
                <button class="ios-btn ios-btn-danger" style="flex: 1;" onclick="openDeletePopup('${user.id}', '${user.name.replace(/'/g, "'")}')"><i class="fa-solid fa-trash" style="margin-right:6px;"></i> Hapus</button>
            </div>
        </div>
        `;
    }
    
    let searchTimeout;
    function filterOrgWorkspace() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(renderWorkspace, 300);
    }
    
    function showToastMsg(msg, type='success') {
        if(window.showToast) { showToast(msg, type); } 
        else {
            const el = document.createElement('div');
            el.style.cssText = `position:fixed; bottom:24px; right:24px; background: ${type==='error'?'#ef4444':'#0C3527'}; color: white; padding: 12px 24px; border-radius: 8px; z-index: 99999; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: opacity 0.5s; opacity: 1;`;
            el.innerText = msg;
            document.body.appendChild(el);
            setTimeout(() => { el.style.opacity = '0'; setTimeout(()=>el.remove(), 500); }, 3000);
        }
    }

    // --- POPUP LOGIC ---
    let currentEmpId = null;

    function closePopup(id) { document.getElementById(id).style.display = 'none'; }

    // 1. Performance Popup (Triggered by clicking card)
    async function openPerformancePopup(id) {
        currentEmpId = id;
        document.getElementById('popup-performance').style.display = 'flex';
        document.getElementById('perf-popup-title').innerText = "Daftar Tugas & Performa: Loading...";
        document.getElementById('perf-popup-subtitle').innerText = "";
        document.getElementById('perf-popup-kpi').innerHTML = "<i class='fa-solid fa-chart-line'></i> Memuat data...";
        document.getElementById('perf-tasks-container').innerHTML = '<div class="loader" style="margin: 20px auto; border-top-color: var(--accent);"></div>';

        try {
            // Fetch basic profile & performance
            const [genRes, perfRes] = await Promise.all([
                fetch(`/organization/node/${id}?section=general`),
                fetch(`/organization/node/${id}?section=performance`)
            ]);
            
            const genData = await genRes.json();
            const perfData = await perfRes.json();

            document.getElementById('perf-popup-title').innerText = `Daftar Tugas & Performa: ${genData.profile.name}`;
            document.getElementById('perf-popup-subtitle').innerText = `${genData.profile.job_title} (${genData.profile.role.toUpperCase()})`;
            
            let completed = perfData.performance.completed_tasks || 0;
            let total = perfData.performance.total_tasks || 0;
            let pct = perfData.performance.kpim_score || 0;
            document.getElementById('perf-popup-kpi').innerHTML = `<i class="fa-solid fa-chart-line"></i> KPIM Score: ${pct}% (${completed}/${total} Selesai)`;

            document.getElementById('btn-slip-gaji').href = `/payroll/slip/${id}`;
            document.getElementById('btn-paklaring').href = `/paklaring/${id}`;

            if (perfData.performance.tasks_list && perfData.performance.tasks_list.length > 0) {
                document.getElementById('perf-tasks-container').innerHTML = perfData.performance.tasks_list.map(task => `
                    <div class="task-item-native">
                        <div>
                            <div style="font-weight: 700; color: var(--text-heading); font-size: 14px; margin-bottom: 4px;">${task.title}</div>
                            <div style="font-size: 12px; color: var(--text-muted);">Kategori: ${task.category} &bull; Deadline: ${task.deadline}</div>
                        </div>
                        <div style="background: rgba(59, 130, 246, 0.1); color: var(--text-accent); padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;">${task.status}</div>
                    </div>
                `).join('');
            } else {
                document.getElementById('perf-tasks-container').innerHTML = `
                    <div style="text-align: center; color: var(--text-muted); font-size: 13px; padding: 24px; background: rgba(0,0,0,0.02); border: 1px dashed var(--panel-border); border-radius: 8px;">
                        Tidak ada tugas yang ditugaskan kepada pegawai ini.
                    </div>
                `;
            }

        } catch(e) {
            document.getElementById('perf-tasks-container').innerHTML = `<div style="color:red; font-size:13px; padding: 12px; background: rgba(239,68,68,0.1); border-radius: 8px;">Gagal memuat data performa: ${e.message}</div>`;
        }
    }

    // 2. Edit Profile Popup
    async function openEditPopup(id) {
        currentEmpId = id;
        document.getElementById('popup-edit').style.display = 'flex';
        document.getElementById('edit-popup-title').innerText = "Edit Profil Staf Tim (...)";
        
        const form = document.getElementById('edit-emp-form');
        form.reset();

        try {
            const res = await fetch(`/organization/node/${id}?section=general`);
            const data = await res.json();
            
            document.getElementById('edit-popup-title').innerText = `Edit Profil Staf Tim (NIK: ${data.profile.employee_code || '-'})`;
            form.employee_code.value = data.profile.employee_code || '-';
            form.name.value = data.profile.name;
            form.email.value = data.profile.email;
            form.job_title.value = data.profile.job_title;
            form.role.value = (data.profile.role || 'staff').toLowerCase();
            // Employment type fetch if needed, defaulting for visual
            form.employment_type.value = data.profile.employment_type || 'Full-Time';

        } catch (e) {
            showToastMsg('Gagal memuat profil', 'error');
        }
    }
    
    async function submitEdit(e) {
        e.preventDefault();
        const payload = Object.fromEntries(new FormData(e.target).entries());
        
        try {
            const res = await fetch(`/organization/node/${currentEmpId}/edit`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') },
                body: JSON.stringify(payload)
            });
            if(res.ok) { showToastMsg('Profil diperbarui.', 'success'); closePopup('popup-edit'); loadOrgWorkspace(); }
            else showToastMsg('Gagal menyimpan profil', 'error');
        } catch(e) { showToastMsg('Network Error', 'error'); }
    }

    // 3. Delete Popup
    function openDeletePopup(id, name) {
        currentEmpId = id;
        document.getElementById('del-emp-name').innerText = name;
        document.getElementById('popup-delete').style.display = 'flex';
    }
    async function submitDelete() {
        if(!currentEmpId) return;
        try {
            const res = await fetch(`/organization/node/${currentEmpId}/delete`, {
                method: 'DELETE',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') }
            });
            if(res.ok) { showToastMsg('Akun dinonaktifkan', 'success'); closePopup('popup-delete'); loadOrgWorkspace(); }
            else showToastMsg('Gagal menonaktifkan', 'error');
        } catch(e) { showToastMsg('Network Error', 'error'); }
    }

    // 4. Add Staff
    let currentEmpNum = '';
    
    function updateGeneratedFields() {
        const nameInput = document.getElementById('add-staff-name').value || '';
        const roleSel = document.getElementById('add-staff-role');
        const roleText = roleSel.options[roleSel.selectedIndex].text.replace('🎯 ', '').toLowerCase();
        const division = document.getElementById('add-staff-division').value || 'Corp';
        
        // Format names
        const cleanName = nameInput.trim().toLowerCase().replace(/[^a-z0-9]+/g, '.');
        const finalName = cleanName || 'pegawai';
        
        let divCode = division.toLowerCase();
        if(divCode.includes('marketing')) divCode = 'mkt';
        else if(divCode.includes('operasional')) divCode = 'ops';
        else if(divCode.includes('finance')) divCode = 'fin';
        else if(divCode.includes('hr')) divCode = 'hrd';
        else divCode = divCode.substring(0,3);

        let lvlCode = 'stf';
        if(roleText === 'manager') lvlCode = 'mgr';
        else if(roleText === 'supervisor') lvlCode = 'spv';
        else if(roleText === 'pic') lvlCode = 'pic';

        const usernameStr = `sa.${divCode}.${lvlCode}.${finalName}.${currentEmpNum}`;
        const empCodeStr = `SA-${divCode.toUpperCase()}-${lvlCode.toUpperCase()}-${currentEmpNum}`;
        
        document.getElementById('add-staff-username').value = usernameStr;
        document.getElementById('add-staff-code').value = empCodeStr;
    }

    function openAddStaffPopup(dept = '') { 
        document.getElementById('add-staff-division').value = dept;
        currentEmpNum = String(Math.floor(Math.random() * 9000) + 1000).padStart(4, '0');
        document.getElementById('add-staff-name').value = '';
        updateGeneratedFields();
        document.getElementById('popup-add-staff').style.display = 'flex'; 
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
            if(res.ok) { showToastMsg('Staff berhasil ditambahkan', 'success'); closePopup('popup-add-staff'); loadOrgWorkspace(); }
            else showToastMsg('Gagal menambah staff', 'error');
        } catch(e) { showToastMsg('Network Error', 'error'); }
    }

    // 5. Add Manager
    function openAddManagerPopup(dept = '') {

    document.getElementById("mgr-division").value = dept;
    document.getElementById("mgr-target-division").value = dept;

    populateManagerCandidates();

    document.getElementById("popup-add-manager").style.display = "flex";

}

function populateManagerCandidates() {
    const dept = document.getElementById("mgr-target-division").value;
    const select = document.getElementById("mgr-emp-username");

    select.innerHTML = '<option value="">-- Pilih Anggota Staff --</option>';

    if (!Array.isArray(workspaceData)) {
        console.error("workspaceData bukan array", workspaceData);
        return;
    }

    const users = workspaceData.filter(user => {
        if (!user) return false;

        if (
            user.role === "superadmin" ||
            user.role === "super_admin" ||
            user.role === "ceo" ||
            (user.role && user.role.toLowerCase().includes("mgr")) ||
            user.role === "manager"
        ) {
            return false;
        }

        if (dept) {
            const userDept = user.department || user.division_label || user.division || 'Perusahaan';
            if (userDept !== dept) {
                return false;
            }
        }

        return true;
    });

    users.forEach(user => {
        const option = document.createElement("option");
        option.value = user.id ?? user.username;
        option.textContent = `${user.name} (${user.employee_code ?? "-"})`;
        select.appendChild(option);
    });

    if (users.length === 0) {
        select.innerHTML = '<option value="">Tidak ada staff tersedia</option>';
    }
}    function openNewManagerForm() {
        const dept = document.getElementById('mgr-division').value;
        closePopup('popup-add-manager');
        
        // Open Add Staff popup
        openAddStaffPopup(dept);
        
        // Force Role to Manager
        document.getElementById('add-staff-role').value = 'manager';
        updateGeneratedFields();
    }

    let pendingManagerPayload = null;

    async function submitAddManager(e) {
        e.preventDefault();
        const userId = document.getElementById("mgr-emp-username").value;
        const division = document.getElementById("mgr-target-division").value;
        
        if(!userId || !division) {
            showErrorModal('Pilih staf dan divisi target.');
            return;
        }

        pendingManagerPayload = { user_id: userId, division: division, force_replace: false };
        executeAppointManager();
    }

    async function confirmReplaceManager() {
        if (!pendingManagerPayload) return;
        pendingManagerPayload.force_replace = true;
        closePopup('popup-replace-manager');
        executeAppointManager();
    }

    async function executeAppointManager() {
        showLoading();
        try {
            const res = await fetch('/organization/appoint-manager', {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(pendingManagerPayload)
            });
            const data = await res.json();
            hideLoading();
            
            if (res.ok && data.success) {
                closePopup('popup-add-manager');
                showSuccessModal(`Berhasil mengangkat ${data.new_manager.name} sebagai Manager ${data.new_manager.division}.`);
                loadOrgWorkspace();
                
                // Highlight the newly promoted manager
                setTimeout(() => {
                    const el = document.getElementById(`org-card-${data.new_manager.id}`);
                    if (el) {
                        el.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        el.classList.add('glow-manager');
                        setTimeout(() => el.classList.remove('glow-manager'), 3000);
                    }
                }, 1000);
            } else if (res.status === 409 && data.conflict) {
                // Show conflict modal
                document.getElementById('conflict-division').innerText = data.division;
                document.getElementById('conflict-old-manager').innerText = data.existing_manager.name;
                document.getElementById('conflict-new-manager').innerText = data.new_candidate.name;
                document.getElementById('popup-replace-manager').style.display = 'flex';
            } else {
                showErrorModal(data.error || 'Gagal memproses promosi');
            }
        } catch(e) {
            hideLoading();
            showErrorModal('Terjadi kesalahan jaringan.');
        }
    }

    // 6. Add Division
    function openAddDivisionPopup() { document.getElementById('popup-add-division').style.display = 'flex'; }
    async function submitAddDivision(e) {
        e.preventDefault();
        const name = document.getElementById('div-name').value;
        if(!name) return;

        try {
            const response = await fetch('{{ route('master-demo.divisions.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name: name })
            });
            
            const result = await response.json();
            if(result.success) {
                showToastMsg(`Divisi ditambahkan.`, 'success');
                closePopup('popup-add-division');
                setTimeout(() => window.location.reload(), 500);
            }
        } catch(e) {
            console.error(e);
            showErrorModal('Gagal membuat divisi.');
        }
    }

    // 6.1 Delete Division
    let currentDeleteOrgDivName = '';
    function confirmOrgDeleteDivision(name) {
        event.stopPropagation(); // prevent toggling the accordion
        currentDeleteOrgDivName = name;
        document.getElementById('del-org-div-name').innerText = name;
        document.getElementById('popup-delete-division').style.display = 'flex';
    }
    
    async function submitDeleteDivision() {
        if (!currentDeleteOrgDivName) return;
        try {
            const response = await fetch(`/api/divisions/by-name`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ name: currentDeleteOrgDivName })
            });
            
            const result = await response.json();
            if(result.success) {
                showToastMsg(`Divisi berhasil dihapus.`, 'success');
                closePopup('popup-delete-division');
                setTimeout(() => window.location.reload(), 500);
            } else {
                showErrorModal(result.message || 'Gagal menghapus divisi.');
            }
        } catch(e) {
            console.error(e);
            showErrorModal('Gagal menghapus divisi.');
        }
    }

    // Modal & UX Utilities
    function showLoading() { document.getElementById('global-loading').style.display = 'flex'; }
    function hideLoading() { document.getElementById('global-loading').style.display = 'none'; }
    function showSuccessModal(msg) { document.getElementById('success-msg').innerText = msg; document.getElementById('global-success').style.display = 'flex'; }
    function closeSuccessModal() { document.getElementById('global-success').style.display = 'none'; }
    function showErrorModal(msg) { document.getElementById('error-msg').innerText = msg; document.getElementById('global-error').style.display = 'flex'; }
    function closeErrorModal() { document.getElementById('global-error').style.display = 'none'; }

    // PDF Previews
    let currentSlipItems = [];
    async function openSlipPreview() {
        if (!currentEmpId) return;
        showLoading();
        try {
            const res = await fetch(`/payroll/slip/${currentEmpId}/preview`);
            const data = await res.json();
            hideLoading();

            if (res.ok) {
                document.getElementById('slip-name').value = data.user.name;
                document.getElementById('slip-job').value = data.user.job_title || data.user.division;
                document.getElementById('slip-div').value = data.user.division;
                document.getElementById('slip-base').value = data.payroll.base_amount;
                document.getElementById('slip-net').value = data.payroll.net_amount;
                document.getElementById('slip-period-start').value = data.payroll.period_start;
                document.getElementById('slip-period-end').value = data.payroll.period_end;
                
                currentSlipItems = data.payroll.items || [];
                document.getElementById('popup-slip-preview').style.display = 'flex';
            } else {
                showErrorModal('Gagal mengambil data slip.');
            }
        } catch (e) {
            hideLoading();
            showErrorModal('Network error');
        }
    }

    function submitSlipGenerate(e) {
        e.preventDefault();
        const form = document.getElementById('form-slip-generate');
        const payload = Object.fromEntries(new FormData(form).entries());
        payload.items = currentSlipItems;

        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        // Buat form dinamis untuk submit dan download file POST
        const htmlForm = document.createElement('form');
        htmlForm.method = 'POST';
        htmlForm.action = `/payroll/slip/${currentEmpId}/generate`;
        htmlForm.target = '_blank';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrf;
        htmlForm.appendChild(csrfInput);

        for (const [key, val] of Object.entries(payload)) {
            if (key === 'items') {
                val.forEach((item, index) => {
                    for (const [iKey, iVal] of Object.entries(item)) {
                        const hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = `items[${index}][${iKey}]`;
                        hidden.value = iVal;
                        htmlForm.appendChild(hidden);
                    }
                });
            } else {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = key;
                hidden.value = val;
                htmlForm.appendChild(hidden);
            }
        }

        document.body.appendChild(htmlForm);
        htmlForm.submit();
        document.body.removeChild(htmlForm);
        
        closePopup('popup-slip-preview');
        showSuccessModal('Slip Gaji sedang diunduh.');
    }

    async function openPaklaringPreview() {
        if (!currentEmpId) return;
        showLoading();
        try {
            const res = await fetch(`/paklaring/${currentEmpId}/preview`);
            const data = await res.json();
            hideLoading();

            if (res.ok) {
                document.getElementById('pak-name').value = data.user.name;
                document.getElementById('pak-code').value = data.user.employee_code;
                document.getElementById('pak-job').value = data.user.job_title || data.user.division;
                document.getElementById('pak-div').value = data.user.division;
                
                document.getElementById('pak-company').value = data.letter.company_name;
                document.getElementById('pak-join').value = data.letter.join_date;
                document.getElementById('pak-resign').value = data.letter.resign_date;
                document.getElementById('pak-content').value = data.letter.content;
                document.getElementById('pak-hr').value = data.letter.hr_name;
                
                document.getElementById('popup-paklaring-preview').style.display = 'flex';
            } else {
                showErrorModal('Gagal mengambil data paklaring.');
            }
        } catch (e) {
            hideLoading();
            showErrorModal('Network error');
        }
    }

    function submitPaklaringGenerate(e) {
        e.preventDefault();
        const form = document.getElementById('form-paklaring-generate');
        const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        
        const htmlForm = document.createElement('form');
        htmlForm.method = 'POST';
        htmlForm.action = `/paklaring/${currentEmpId}/generate`;
        htmlForm.target = '_blank';

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = csrf;
        htmlForm.appendChild(csrfInput);

        const formData = new FormData(form);
        for (const [key, val] of formData.entries()) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = key;
            hidden.value = val;
            htmlForm.appendChild(hidden);
        }

        document.body.appendChild(htmlForm);
        htmlForm.submit();
        document.body.removeChild(htmlForm);
        
        closePopup('popup-paklaring-preview');
        showSuccessModal('Paklaring sedang diunduh.');
    }

    setTimeout(loadOrgWorkspace, 100);
</script>
