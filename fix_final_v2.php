<?php
$file = 'resources/views/master-portal.blade.php';
$content = file_get_contents($file);

$js = <<<JS
    let currentDeleteTaskId = null;

    function openPopup(id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'flex';
        }
    }

    function closePopup(id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'none';
        }
    }

    function showToast(message) {
        let toast = document.getElementById('global-toast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'global-toast';
            toast.className = 'ios-toast';
            document.body.appendChild(toast);
        }
        toast.innerHTML = `<i class="fa-solid fa-circle-check" style="color: #34D399; font-size: 18px;"></i><span>\${message}</span>`;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 3000);
    }

    function updateAssignTaskDivision(selectElem) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const divisionInput = document.getElementById('assign-task-division');
        if (!selectElem.value) {
            divisionInput.value = '';
            return;
        }
        const division = selectedOption.getAttribute('data-division') || 'Tanpa Divisi';
        divisionInput.value = division;
    }

    function updateEditTaskDivision(selectElem) {
        const selectedOption = selectElem.options[selectElem.selectedIndex];
        const divisionInput = document.getElementById('edit-task-division');
        if (!selectElem.value) {
            divisionInput.value = '';
            return;
        }
        const division = selectedOption.getAttribute('data-division') || 'Tanpa Divisi';
        divisionInput.value = division;
    }

    function handleStoreTask(e) {
        e.preventDefault();
        const form = e.target;
        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Tugas berhasil disimpan.');
                closePopup('task-add-modal');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                alert('Gagal menyimpan tugas.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan/server.');
        });
    }

    function openDeleteTaskModal(taskId) {
        currentDeleteTaskId = taskId;
        openPopup('task-delete-modal');
    }

    function confirmDeleteTask() {
        if (!currentDeleteTaskId) return;

        fetch(`/master-demo/tasks/\${currentDeleteTaskId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}',
                'Accept': 'application/json',
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast('Tugas berhasil dihapus.');
                closePopup('task-delete-modal');
                setTimeout(() => window.location.reload(), 1000);
            } else {
                alert('Gagal menghapus tugas.');
            }
        })
        .catch(err => {
            console.error(err);
            alert('Terjadi kesalahan jaringan/server.');
        });
    }

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

$content = preg_replace('/let currentDeleteTaskId = null;.*?(?=<\/script>)/s', $js . "\n", $content);
file_put_contents($file, $content);
echo "Fixed JS block V2!";
