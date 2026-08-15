import codecs

content = """    function drag(ev) {
        ev.dataTransfer.setData("text/plain", ev.target.dataset.key);
        ev.dataTransfer.effectAllowed = "move";
    }

    function allowDrop(ev) {
        ev.preventDefault();
        ev.dataTransfer.dropEffect = "move";
        const box = ev.target.closest('.division-box');
        if(box) {
            box.style.borderColor = 'var(--accent)';
        }
    }

    async function drop(ev) {
        ev.preventDefault();
        const key = ev.dataTransfer.getData("text/plain");
        if (!key) return;
        
        const targetDivisionBox = ev.target.closest('.division-box');
        
        // Reset border styles
        document.querySelectorAll('.division-box').forEach(b => {
            b.style.borderColor = b.getAttribute('data-id') ? 'transparent' : 'var(--danger)'; 
        });

        if (!targetDivisionBox) return;
        
        const divisionId = targetDivisionBox.dataset.id || null;
        
        try {
            const res = await fetch('/api/features/assign', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ feature_key: key, division_id: divisionId })
            });
            
            if (res.ok) {
                showToast('Modul berhasil dipindahkan');
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert('Gagal memindahkan modul');
            }
        } catch(e) { console.error(e); }
    }
</script>

<!-- CONFIRM REVOKE MODAL -->
<div id="modal-confirm-revoke" class="modal-overlay" style="display:none; z-index: 10000;">
    <div class="modal-content ios-modal" style="width: 400px; max-width: 90vw; border-radius: 18px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px); border: 1px solid #fee2e2; box-shadow: 0 20px 40px rgba(239, 68, 68, 0.15); padding: 32px 24px; text-align: center;">
        <i class="fa-solid fa-triangle-exclamation" style="font-size: 48px; color: var(--danger); margin-bottom: 20px;"></i>
        <h3 style="margin: 0 0 12px 0; font-size: 18px; font-weight: 800; color: #111827;">Cabut Hak Akses?</h3>
        <p style="color: var(--text-muted); font-size: 14px; margin: 0 0 24px 0; line-height: 1.5;">
            Anda yakin ingin mencabut seluruh hak akses dari <strong><span id="revoke-user-name"></span></strong>?<br>
            Pengguna tidak akan bisa mengakses modul sistem lagi sampai diberikan role baru.
        </p>
        <input type="hidden" id="revoke-user-id" value="">
        <div style="display: flex; gap: 12px; justify-content: center;">
            <button class="ios-btn" style="flex: 1; background: #f1f5f9; color: #475569;" onclick="document.getElementById('modal-confirm-revoke').style.display='none'">Batal</button>
            <button class="ios-btn ios-btn-danger" style="flex: 1;" onclick="executeRevoke()">Ya, Cabut Akses</button>
        </div>
    </div>
</div>

<!-- Modal Create Announcement -->
<div id="modal-create-announcement" class="modal-overlay" style="display:none; z-index: 10000; align-items: center; justify-content: center;">
    <div class="modal-content ios-modal" style="width: 500px; max-width: 90vw; border-radius: 18px; background: rgba(255, 255, 255, 0.98); backdrop-filter: blur(12px); border: 1px solid var(--panel-border); box-shadow: 0 20px 40px rgba(0,0,0,0.1); padding: 32px 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
            <h3 style="margin: 0; font-size: 18px; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                <i class="fa-solid fa-bullhorn" style="color: var(--primary);"></i> Buat Pengumuman Baru
            </h3>
            <button onclick="document.getElementById('modal-create-announcement').style.display='none'" style="background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer;">&times;</button>
        </div>
        
        <form onsubmit="submitAnnouncement(event)">
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-main);">Tujuan (Target Penerima)</label>
                <select id="announcement-target" class="form-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--panel-border); background: var(--bg-main); color: var(--text-main);">
                    <option value="all">Seluruh Karyawan</option>
                    <option value="managers">Seluruh Manager</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom: 16px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-main);">Judul Pengumuman</label>
                <input type="text" id="announcement-title" class="form-control" placeholder="Contoh: Libur Nasional Idul Fitri" required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--panel-border); background: var(--bg-main); color: var(--text-main);">
            </div>
            <div class="form-group" style="margin-bottom: 24px;">
                <label style="display: block; font-size: 13px; font-weight: 600; margin-bottom: 8px; color: var(--text-main);">Isi Pesan (Atau Link)</label>
                <textarea id="announcement-message" class="form-control" rows="4" placeholder="Ketik isi pengumuman secara detail di sini..." required style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--panel-border); background: var(--bg-main); color: var(--text-main); resize: vertical;"></textarea>
            </div>
            
            <div style="display: flex; gap: 12px; justify-content: flex-end;">
                <button type="button" class="ios-btn" style="background: #f1f5f9; color: #475569;" onclick="document.getElementById('modal-create-announcement').style.display='none'">Batal</button>
                <button type="submit" class="ios-btn ios-btn-primary"><i class="fa-solid fa-paper-plane" style="margin-right: 6px;"></i> Siarkan Pengumuman</button>
            </div>
        </form>
    </div>
</div>

<x-ui.toast />
@include('components.global-loading')
@include('components.chat-widget')
"""

with codecs.open(r'd:\suba-erp-master-local-latest\resources\views\master-portal.blade.php', 'a', 'utf-8') as f:
    f.write(content)
