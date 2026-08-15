<?php
$file = 'resources/views/master-portal.blade.php';
$content = file_get_contents($file);

$js = <<<JS
    // Drag leave to reset border
    document.addEventListener('dragleave', function(ev) {
        const box = ev.target.closest('.division-box');
        if(box) {
            box.style.borderColor = box.getAttribute('data-id') ? 'transparent' : 'var(--danger)'; 
        }
    });

    window.createNewDivision = function() {
        const input = document.getElementById('add-div-name-input');
        if(input) input.value = '';
        const codeInput = document.getElementById('add-div-code-input');
        if(codeInput) codeInput.value = '';
        
        const modal = document.getElementById('division-add-modal');
        if(modal) {
            modal.style.display = 'flex';
        } else {
            console.error('Modal division-add-modal tidak ditemukan');
        }
    };

    window.submitNewDivision = async function(e) {
        if(e) e.preventDefault();
        const name = document.getElementById('add-div-name-input').value;
        if(!name || name.trim() === '') return;
        const code = document.getElementById('add-div-code-input') ? document.getElementById('add-div-code-input').value : '';

        try {
            const response = await fetch('/master-demo/divisions', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name: name, code: code })
            });
            
            const result = await response.json();
            if(result.success) {
                closePopup('division-add-modal');
                showToast('Divisi berhasil ditambahkan.');
                setTimeout(() => window.location.reload(), 500); 
            } else {
                alert('Gagal membuat divisi: ' + (result.message || 'Unknown error'));
            }
        } catch(e) {
            console.error(e);
            alert('Gagal membuat divisi.');
        }
    };

    let currentDivEditId = null;
    let currentDivDeleteId = null;

    function openRenameDivisionModal(id, currentName) {
        currentDivEditId = id;
        document.getElementById('edit-div-name-input').value = currentName;
        openPopup('division-edit-modal');
    }

    async function confirmRenameDivision() {
        const name = document.getElementById('edit-div-name-input').value;
        if(!name || name.trim() === '') return;
        const id = currentDivEditId;

        try {
            const response = await fetch(`/master-demo/divisions/\${id}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ name: name })
            });
            
            const result = await response.json();
            if(result.success) {
                document.getElementById('div-name-' + id).innerText = name;
                showToast('Nama divisi berhasil diubah.');
                closePopup('division-edit-modal');
                setTimeout(() => window.location.reload(), 500);
            }
        } catch(e) {
            console.error(e);
            alert('Gagal mengubah nama divisi.');
        }
    }

    function openDeleteDivisionModal(id, currentName) {
        currentDivDeleteId = id;
        document.getElementById('del-div-name').innerText = currentName;
        openPopup('division-delete-modal');
    }

    async function confirmDeleteDivision() {
        const id = currentDivDeleteId;
        try {
            const response = await fetch(`/master-demo/divisions/\${id}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            });
            
            const result = await response.json();
            if(result.success) {
                showToast('Divisi berhasil dihapus.');
                closePopup('division-delete-modal');
                setTimeout(() => window.location.reload(), 500);
            } else {
                alert(result.message || 'Gagal menghapus divisi karena masih ada modul di dalamnya.');
                closePopup('division-delete-modal');
            }
        } catch(e) {
            console.error(e);
            alert('Gagal menghapus divisi.');
        }
    }

    async function removeModuleFromDivision(featureKey) {
        if(!confirm('Keluarkan modul ini dari divisinya?')) return;
        
        try {
            const response = await fetch('/master-demo/features/assign', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    feature_key: featureKey,
                    division_id: null
                })
            });
            
            const result = await response.json();
            if(result.success) {
                showToast('Modul dikeluarkan dari divisi.');
                setTimeout(() => window.location.reload(), 300);
            }
        } catch(e) {
            console.error(e);
            alert('Gagal mengeluarkan modul.');
        }
    }
JS;

$content = preg_replace('/\s*\/\/\s*Drag leave to reset border.*?\}\);.*?(?=<\/script>)/s', "\n" . $js . "\n", $content);
file_put_contents($file, $content);
echo "Fixed JS block!";
