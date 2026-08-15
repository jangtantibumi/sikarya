<?php
$file = 'resources/views/master-portal.blade.php';
$content = file_get_contents($file);

$search1 = "        const submitBtn = form.querySelector('button[type=\"submit\"]');";
$replace1 = "        // Prevent double submit\n        if (form.dataset.submitting === 'true') return;\n        form.dataset.submitting = 'true';\n\n        const submitBtn = form.querySelector('button[type=\"submit\"]');";
$content = str_replace($search1, $replace1, $content);

$search2 = "submitBtn.disabled = false;\n                submitBtn.innerHTML = originalText;";
$replace2 = "submitBtn.disabled = false;\n                submitBtn.innerHTML = originalText;\n                form.dataset.submitting = 'false';";
$content = str_replace($search2, $replace2, $content);

$content = str_replace('setTimeout(() => window.location.reload(), 1000);', 'setTimeout(() => window.location.reload(), 200);', $content);

file_put_contents($file, $content);
echo "Done";