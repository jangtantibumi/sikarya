<?php
$file = 'public/js/app.js';
$content = file_get_contents($file);

$search1 = "createTaskForm.addEventListener('submit', async (e) => {\n            e.preventDefault();";
$replace1 = "createTaskForm.addEventListener('submit', async (e) => {\n            e.preventDefault();\n            if (createTaskForm.dataset.submitting === 'true') return;\n            createTaskForm.dataset.submitting = 'true';";
$content = str_replace($search1, $replace1, $content);

$search2 = "showPremiumNotice('Task Tidak Dapat Dibuat', escapeHtml(err.message));\n                }";
$replace2 = "showPremiumNotice('Task Tidak Dapat Dibuat', escapeHtml(err.message));\n                } finally {\n                    createTaskForm.dataset.submitting = 'false';\n                }";
$content = str_replace($search2, $replace2, $content);

file_put_contents($file, $content);
echo "Done app.js";