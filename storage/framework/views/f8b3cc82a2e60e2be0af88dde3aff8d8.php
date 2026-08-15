<style>
    /* Premium Offcanvas Chat Drawer Styles */
    .global-chat-drawer {
        position: fixed;
        top: 0;
        right: -450px;
        width: 400px;
        max-width: 100vw;
        height: 100vh;
        background: #121212; /* Dark theme matching screenshot */
        z-index: 99999;
        display: flex;
        flex-direction: column;
        box-shadow: -10px 0 30px rgba(0,0,0,0.5);
        transition: right 0.4s cubic-bezier(0.19, 1, 0.22, 1);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }

    .global-chat-drawer.active {
        right: 0;
    }

    .chat-drawer-header {
        padding: 20px 24px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        color: white;
    }

    .chat-drawer-title {
        font-size: 16px;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .chat-drawer-close {
        background: none;
        border: none;
        color: rgba(255,255,255,0.6);
        font-size: 20px;
        cursor: pointer;
        transition: color 0.2s;
    }

    .chat-drawer-close:hover {
        color: white;
    }

    .chat-channel-selector {
        padding: 16px 24px;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .chat-select {
        width: 100%;
        background: #2a2a2a;
        border: 1px solid rgba(255,255,255,0.1);
        color: white;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 14px;
        outline: none;
        cursor: pointer;
    }

    .chat-select:focus {
        border-color: #facc15;
    }

    .chat-drawer-messages {
        flex: 1;
        overflow-y: auto;
        padding: 20px 24px;
        display: flex;
        flex-direction: column;
        gap: 20px;
        background: #1a1a1a;
    }

    .chat-msg-wrapper {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .chat-msg-wrapper.mine {
        align-items: flex-end;
    }

    .chat-msg-meta {
        font-size: 11px;
        color: rgba(255,255,255,0.5);
        display: flex;
        gap: 8px;
        align-items: center;
    }

    .chat-msg-wrapper.mine .chat-msg-meta {
        flex-direction: row-reverse;
    }

    .chat-msg-bubble {
        padding: 12px 16px;
        border-radius: 12px 12px 12px 0;
        font-size: 14px;
        line-height: 1.5;
        max-width: 85%;
        word-break: break-word;
        background: #2d2d2d;
        color: rgba(255,255,255,0.9);
        border: 1px solid rgba(255,255,255,0.05);
    }

    .chat-msg-wrapper.mine .chat-msg-bubble {
        background: #facc15; /* Yellow bubble for mine */
        color: #1a1a1a;
        border-radius: 12px 12px 0 12px;
        border: none;
        font-weight: 500;
    }

    .chat-drawer-input {
        padding: 20px 24px;
        background: #121212;
        border-top: 1px solid rgba(255,255,255,0.1);
    }

    .chat-input-wrapper {
        display: flex;
        align-items: center;
        gap: 12px;
        background: #2a2a2a;
        padding: 8px 16px;
        border-radius: 24px;
        border: 1px solid rgba(255,255,255,0.1);
    }

    .chat-input-wrapper input[type="text"] {
        flex: 1;
        background: transparent;
        border: none;
        color: white;
        font-size: 14px;
        outline: none;
    }

    .chat-input-wrapper input[type="text"]::placeholder {
        color: rgba(255,255,255,0.4);
    }

    .chat-input-action {
        background: none;
        border: none;
        color: rgba(255,255,255,0.5);
        font-size: 16px;
        cursor: pointer;
        transition: color 0.2s;
        padding: 4px;
    }

    .chat-input-action:hover {
        color: white;
    }

    .chat-input-send {
        background: #facc15;
        color: #1a1a1a;
        border: none;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: transform 0.2s;
    }

    .chat-input-send:hover {
        transform: scale(1.1);
    }
    
    .msg-delete-btn {
        background: none;
        border: none;
        color: #ef4444;
        font-size: 10px;
        cursor: pointer;
        padding: 0;
        opacity: 0.5;
    }
    .msg-delete-btn:hover { opacity: 1; }

    .chat-attachment-preview {
        padding: 8px 16px;
        background: #2d2d2d;
        border-radius: 8px;
        font-size: 12px;
        color: white;
        display: none;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 12px;
    }

    .chat-msg-attachment {
        margin-top: 8px;
        padding: 10px;
        background: rgba(0,0,0,0.2);
        border-radius: 8px;
        display: flex;
        align-items: center;
        gap: 12px;
        cursor: pointer;
        color: inherit;
        text-decoration: none;
    }
</style>

<div class="global-chat-drawer" id="globalChatDrawer">
    <div class="chat-drawer-header">
        <div class="chat-drawer-title">
            <i class="fa-solid fa-hashtag" style="color: var(--text-accent);"></i> Internal Chat
        </div>
        <button class="chat-drawer-close" onclick="toggleChatPanel()"><i class="fa-solid fa-xmark"></i></button>
    </div>

    <div class="chat-channel-selector">
        <select class="chat-select" id="chat-channel-select" onchange="activateChannel(this.value, this.options[this.selectedIndex].text)">
            <option value="general"># general</option>
            <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if(auth()->user()->isCEO() || auth()->user()->isManager()): ?>
            <option value="managers"># managers</option>
            <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        </select>
    </div>

    <div class="chat-drawer-messages" id="drawer-messages-container">
        <div style="text-align: center; color: rgba(255,255,255,0.4); font-size: 12px; margin-top: auto; margin-bottom: auto;">
            Memuat obrolan...
        </div>
    </div>

    <div class="chat-drawer-input">
        <form onsubmit="handleChatSubmit(event)" id="drawer-chat-form">
            <input type="file" id="chat-attachment-input" style="display: none;" onchange="handleAttachmentSelect(event)">
            
            <div class="chat-attachment-preview" id="chat-attachment-preview">
                <span id="chat-attachment-name">filename.pdf</span>
                <button type="button" style="background:none; border:none; color:#ef4444; cursor:pointer;" onclick="clearAttachment()"><i class="fa-solid fa-times"></i></button>
            </div>

            <div class="chat-input-wrapper">
                <button type="button" class="chat-input-action" onclick="document.getElementById('chat-attachment-input').click()">
                    <i class="fa-solid fa-paperclip"></i>
                </button>
                <input type="text" id="chat-drawer-input" placeholder="Tulis pesan... (@AI untuk Gemini)">
                <button type="submit" class="chat-input-send" id="chat-send-btn">
                    <i class="fa-solid fa-paper-plane"></i>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    let currentActiveChannel = 'general';
    let currentActiveChannelName = '# general';
    let isChatPanelOpen = false;
    let chatPollInterval = null;
    const isUserCEO = <?php echo e(auth()->check() && auth()->user()->isCEO() ? 'true' : 'false'); ?>;

    function toggleChatPanel() {
        const drawer = document.getElementById('globalChatDrawer');
        isChatPanelOpen = !isChatPanelOpen;
        if(isChatPanelOpen) {
            drawer.classList.add('active');
            loadDivisions();
            fetchDrawerMessages();
            startPolling();
        } else {
            drawer.classList.remove('active');
            stopPolling();
        }
    }

    async function loadDivisions() {
        try {
            const res = await fetch('/master-demo/chat/channels/list');
            if(res.ok) {
                const data = await res.json();
                const select = document.getElementById('chat-channel-select');
                
                // Clear dynamic options
                Array.from(select.options).forEach(opt => {
                    if(opt.value !== 'general' && opt.value !== 'managers') select.remove(opt.index);
                });
                
                if (data.divisions && data.divisions.length > 0) {
                    data.divisions.forEach(div => {
                        const option = document.createElement('option');
                        option.value = div.name;
                        option.text = `# grup ${div.name.toLowerCase()}`;
                        select.appendChild(option);
                    });
                }

                if (data.custom && data.custom.length > 0) {
                    data.custom.forEach(c => {
                        const option = document.createElement('option');
                        option.value = c.name;
                        option.text = `# kustom: ${c.name.toLowerCase()}`;
                        select.appendChild(option);
                    });
                }

                select.value = currentActiveChannel;
            }
        } catch(e) { console.error('Failed loading divisions', e); }
    }

    function activateChannel(channelId, channelName) {
        currentActiveChannel = channelId;
        currentActiveChannelName = channelName;
        fetchDrawerMessages();
    }

    function handleAttachmentSelect(e) {
        const file = e.target.files[0];
        if(file) {
            document.getElementById('chat-attachment-preview').style.display = 'flex';
            document.getElementById('chat-attachment-name').innerText = file.name;
        }
    }

    function clearAttachment() {
        document.getElementById('chat-attachment-input').value = '';
        document.getElementById('chat-attachment-preview').style.display = 'none';
    }

    async function fetchDrawerMessages() {
        if (!isChatPanelOpen) return;
        try {
            const res = await fetch('/master-demo/chat/' + currentActiveChannel);
            if(res.ok) {
                const messages = await res.json();
                renderDrawerMessages(messages);
            }
        } catch(e) { console.error(e); }
    }

    function renderDrawerMessages(messages) {
        const container = document.getElementById('drawer-messages-container');
        container.innerHTML = '';
        const currentUserId = <?php echo e(auth()->id() ?? 0); ?>;

        if (messages.length === 0) {
            container.innerHTML = `<div style="text-align: center; color: rgba(255,255,255,0.4); font-size: 12px; margin-top: auto; margin-bottom: auto;">Belum ada pesan di ${currentActiveChannelName}.</div>`;
            return;
        }

        messages.forEach(msg => {
            const isMine = msg.sender_id === currentUserId;
            const senderName = msg.sender ? msg.sender.name : 'Unknown';
            const timeStr = new Date(msg.created_at).toLocaleTimeString([], {day:'2-digit', month:'short', hour: '2-digit', minute:'2-digit'});

            let attachmentHtml = '';
            if (msg.attachment_path) {
                let icon = 'fa-file';
                if(msg.attachment_mime && msg.attachment_mime.includes('image')) icon = 'fa-image';
                else if(msg.attachment_mime && msg.attachment_mime.includes('pdf')) icon = 'fa-file-pdf';

                attachmentHtml = `
                    <a href="${msg.attachment_path}" target="_blank" class="chat-msg-attachment">
                        <i class="fa-solid ${icon}" style="font-size: 16px;"></i>
                        <div>
                            <div style="font-weight: 600; font-size: 11px;">${msg.attachment_name || 'Attachment'}</div>
                            <div style="font-size: 9px; opacity: 0.8;">${msg.attachment_size ? Math.round(msg.attachment_size/1024) : 0} KB</div>
                        </div>
                    </a>
                `;
            }

            let deleteBtnHtml = '';
            if (isUserCEO) {
                deleteBtnHtml = `<button class="msg-delete-btn" onclick="deleteMessage(${msg.id})" title="Hapus Pesan"><i class="fa-solid fa-trash"></i></button>`;
            }

            const html = `
                <div class="chat-msg-wrapper ${isMine ? 'mine' : ''}">
                    <div class="chat-msg-meta">
                        <span>${isMine ? 'Anda' : senderName}</span>
                        ${deleteBtnHtml}
                    </div>
                    <div class="chat-msg-bubble">
                        ${msg.message ? `<div>${msg.message}</div>` : ''}
                        ${attachmentHtml}
                        <div style="font-size: 9px; margin-top: 4px; text-align: right; opacity: 0.6; color: ${isMine ? 'rgba(0,0,0,0.5)' : 'rgba(255,255,255,0.4)'}">${timeStr}</div>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', html);
        });

        container.scrollTop = container.scrollHeight;
    }

    async function handleChatSubmit(e) {
        e.preventDefault();
        
        const input = document.getElementById('chat-drawer-input');
        const fileInput = document.getElementById('chat-attachment-input');
        const btn = document.getElementById('chat-send-btn');
        
        const formData = new FormData();
        formData.append('channel', currentActiveChannel);
        
        if (input.value.trim()) formData.append('message', input.value.trim());
        if (fileInput.files[0]) formData.append('attachment', fileInput.files[0]);

        if (!input.value.trim() && !fileInput.files[0]) return;

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';

        try {
            const response = await fetch('/master-demo/chat', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: formData
            });
            
            if(response.ok) {
                input.value = '';
                clearAttachment();
                fetchDrawerMessages();
            } else {
                console.error('Gagal mengirim pesan.');
            }
        } catch(err) {
            console.error(err);
        } finally {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-paper-plane"></i>';
        }
    }

    async function deleteMessage(id) {
        if(!confirm('Hapus pesan ini?')) return;
        try {
            const res = await fetch('/master-demo/chat/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') }
            });
            if(res.ok) fetchDrawerMessages();
            else console.error('Gagal menghapus pesan.');
        } catch(e) { console.error(e); }
    }

    function startPolling() {
        if(!chatPollInterval) {
            chatPollInterval = setInterval(fetchDrawerMessages, 3000);
        }
    }

    function stopPolling() {
        if(chatPollInterval) {
            clearInterval(chatPollInterval);
            chatPollInterval = null;
        }
    }
</script>

<!-- Floating Toggle Button for Chat -->
<button class="chat-fab-toggle" onclick="toggleChatPanel()" style="position: fixed; bottom: 24px; right: 24px; width: 60px; height: 60px; border-radius: 50%; background: #0C3527; color: #ffffff; font-size: 24px; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(12, 53, 39, 0.4); border: none; cursor: pointer; z-index: 99998; transition: transform 0.2s;">
    <i class="fa-solid fa-message"></i>
</button>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\components\chat-widget.blade.php ENDPATH**/ ?>