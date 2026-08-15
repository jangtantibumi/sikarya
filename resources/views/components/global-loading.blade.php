<!-- Contextual Inline Loading Interceptor -->
<!-- Menghapus modal global (overlay hitam), digantikan oleh UI putar (spinner) pada tombol -->
<!-- Custom Confirm Modal -->
<div id="custom-confirm-modal" style="display: none; position: fixed; inset: 0; z-index: 999999; align-items: center; justify-content: center; background: rgba(0,0,0,0.4); backdrop-filter: blur(4px); opacity: 0; transition: opacity 0.3s ease;">
    <div style="background: var(--panel-bg, #fff); border: 1px solid var(--panel-border, #eee); border-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); width: 100%; max-width: 360px; padding: 24px; text-align: center; transform: scale(0.9); transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);">
        <div style="width: 60px; height: 60px; border-radius: 50%; background: rgba(239, 68, 68, 0.1); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px auto;">
            <i class="fa-solid fa-triangle-exclamation" style="font-size: 24px; color: #ef4444;"></i>
        </div>
        <h3 style="margin: 0 0 12px 0; font-size: 18px; color: var(--text-heading, #111);">Konfirmasi Aksi</h3>
        <p id="custom-confirm-message" style="margin: 0 0 24px 0; font-size: 14px; color: var(--text-muted, #666);">Apakah Anda yakin?</p>
        <div style="display: flex; gap: 12px;">
            <button id="custom-confirm-cancel" type="button" style="flex: 1; padding: 12px; border-radius: 12px; border: 1px solid var(--panel-border, #ddd); background: transparent; color: var(--text-muted, #666); font-weight: 600; cursor: pointer; transition: all 0.2s;">Batal</button>
            <button id="custom-confirm-ok" type="button" style="flex: 1; padding: 12px; border-radius: 12px; border: none; background: #ef4444; color: white; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);">Ya, Lanjutkan</button>
        </div>
    </div>
</div>

<script>
(function() {
    // Fungsi untuk menampilkan Toast Notification (Alpine.js integration)
    function showToast(message, type = 'success') {
        window.dispatchEvent(new CustomEvent('notify', {
            detail: { message: message, type: type }
        }));
    }

    // Fungsi untuk menampilkan Custom Confirm
    function showCustomConfirm(message, onConfirm) {
        const modal = document.getElementById('custom-confirm-modal');
        const msgEl = document.getElementById('custom-confirm-message');
        const cancelBtn = document.getElementById('custom-confirm-cancel');
        const okBtn = document.getElementById('custom-confirm-ok');
        const content = modal.firstElementChild;
        
        msgEl.textContent = message;
        modal.style.display = 'flex';
        
        requestAnimationFrame(() => {
            modal.style.opacity = '1';
            content.style.transform = 'scale(1)';
        });

        const cleanup = () => {
            modal.style.opacity = '0';
            content.style.transform = 'scale(0.9)';
            setTimeout(() => { modal.style.display = 'none'; }, 300);
            cancelBtn.onclick = null;
            okBtn.onclick = null;
        };

        cancelBtn.onclick = cleanup;
        okBtn.onclick = () => {
            cleanup();
            onConfirm();
        };
    }

    // Convert native confirm to custom
    function attachCustomConfirm() {
        document.querySelectorAll('form[onsubmit*="confirm("]').forEach(form => {
            const code = form.getAttribute('onsubmit');
            const match = code.match(/confirm(['"](.*?)['"])/);
            if (match && match[1]) {
                form.setAttribute('data-confirm', match[1]);
                form.removeAttribute('onsubmit');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', attachCustomConfirm);
    new MutationObserver(() => attachCustomConfirm()).observe(document.body, { childList: true, subtree: true });

    // Fungsi untuk mengunci dan memutar (loading) tombol yang ditekan
    function setButtonLoading(btn) {
        if (!btn || btn.tagName !== 'BUTTON') return null;
        // Simpan state asli agar bisa dikembalikan (graceful fail/success)
        const originalState = {
            html: btn.innerHTML,
            width: btn.style.width,
            disabled: btn.disabled
        };
        
        // Kunci lebar agar tombol tidak mengecil tiba-tiba saat teks hilang
        const computedWidth = window.getComputedStyle(btn).width;
        btn.style.width = computedWidth;
        
        // Terapkan efek spinner & disable
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
        
        return originalState;
    }

    // Fungsi untuk mengembalikan tombol ke keadaan normal
    function revertButton(btn, originalState) {
        if (!btn || !originalState) return;
        btn.innerHTML = originalState.html;
        btn.style.width = originalState.width;
        btn.disabled = originalState.disabled;
    }

    function getSuccessMessage(method) {
        if(method === 'DELETE') return 'Berhasil Dihapus!';
        if(['POST', 'PUT', 'PATCH'].includes(method)) return 'Berhasil Disimpan!';
        return 'Berhasil!';
    }

    // 1. Intercept Form Submissions
    document.addEventListener('submit', function(e) {
        const methodAttr = (e.target.getAttribute('method') || 'GET').toUpperCase();
        if (methodAttr === 'GET') return;
        
        const form = e.target;
        const methodInput = form.querySelector('input[name="_method"]');
        let actualMethod = methodInput ? methodInput.value.toUpperCase() : methodAttr;
        
        if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(actualMethod)) {
            // Abaikan form chat agar murni silent dan instant (Bypass)
            if (form.classList.contains('chat-input') || form.id === 'drawer-chat-form') {
                return;
            }
            e.preventDefault(); 
            
            const submitProcess = () => {
                // Temukan tombol mana yang memicu submit
                const submitBtn = e.submitter || form.querySelector('button[type="submit"]');
                const btnState = setButtonLoading(submitBtn);
                
                const formData = new FormData(form);
                // Fix issue where e.submitter's name is not appended to FormData
                if (e.submitter && e.submitter.name) {
                    formData.append(e.submitter.name, e.submitter.value);
                }

                fetch(form.action, {
                    method: 'POST', // Laravel menerima form via POST (method spoofing diproses server)
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                }).then(res => {
                    if(res.ok || res.redirected || res.type === 'opaqueredirect') {
                        // Tampilkan toast secara independen dan LANGSUNG REFRESH tanpa ditunda
                        showToast(getSuccessMessage(actualMethod), 'success');
                        window.location.reload(); 
                    } else {
                        revertButton(submitBtn, btnState);
                        showToast('Gagal memproses data.', 'error');
                    }
                }).catch(err => {
                    revertButton(submitBtn, btnState);
                    showToast('Terjadi kesalahan koneksi.', 'error');
                });
            };

            if (form.hasAttribute('data-confirm')) {
                showCustomConfirm(form.getAttribute('data-confirm'), submitProcess);
            } else {
                submitProcess();
            }
        }
    });

    // 2. Intercept native fetch() calls (AJAX independen)
    const originalFetch = window.fetch;
    window.fetch = async function() {
        const url = arguments[0];
        const options = arguments[1] || {};
        
        const method = (options.method || 'GET').toUpperCase();
        let actualMethod = method;
        
        if (options.body && typeof options.body === 'string') {
            if (options.body.includes('"_method":"DELETE"')) actualMethod = 'DELETE';
            else if (options.body.includes('"_method":"PUT"')) actualMethod = 'PUT';
            else if (options.body.includes('"_method":"PATCH"')) actualMethod = 'PATCH';
        }

        if (['POST', 'PUT', 'PATCH', 'DELETE'].includes(actualMethod)) {
            // Abaikan endpoint chat
            if (typeof url === 'string' && (url.includes('/chat') || url.includes('/api/chat') || url.includes('/master-demo/chat'))) {
                return originalFetch.apply(this, arguments);
            }
            
            // Karena ini murni fetch, kita tebak tombol asalnya dari activeElement yang difokus pengguna
            let triggerBtn = null;
            if (document.activeElement && (document.activeElement.tagName === 'BUTTON' || document.activeElement.closest('button'))) {
                triggerBtn = document.activeElement.tagName === 'BUTTON' ? document.activeElement : document.activeElement.closest('button');
            }
            
            const btnState = setButtonLoading(triggerBtn);
            
            try {
                const response = await originalFetch.apply(this, arguments);
                if(response.ok) {
                    showToast(getSuccessMessage(actualMethod), 'success');
                    revertButton(triggerBtn, btnState); // Lepas tombol (jika halamannya tidak otomatis direfresh oleh logika asal)
                    return response;
                } else {
                    revertButton(triggerBtn, btnState);
                    showToast('Gagal memproses data.', 'error');
                    return response;
                }
            } catch (err) {
                revertButton(triggerBtn, btnState);
                showToast('Terjadi kesalahan koneksi.', 'error');
                throw err;
            }
        }
        
        return originalFetch.apply(this, arguments);
    };
})();
</script>

