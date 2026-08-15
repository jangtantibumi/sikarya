<?php
$content = file_get_contents('resources/views/purchasing/index.blade.php');
if (preg_match('/<script>(.*?window\.purchasingApp\s*=.*?)<\/script>/s', $content, $matches)) {
    $js = trim($matches[1]);
    $js = preg_replace('/const\s+isCeo\s*=\s*\{\{.*?\}\};/', 'const isCeo = window.purchasingConfig ? window.purchasingConfig.isCeo : false;', $js);
    file_put_contents('public/js/purchasing.js', $js);
    
    $newScript = "<script>\n    window.purchasingConfig = {\n        isCeo: {{ (auth()->check() && auth()->user()->isCEO()) ? 'true' : 'false' }}\n    };\n</script>\n<script src=\"{{ asset('js/purchasing.js') }}?v={{ time() }}\"></script>";
    
    $newContent = str_replace($matches[0], $newScript, $content);
    file_put_contents('resources/views/purchasing/index.blade.php', $newContent);
    echo "Successfully extracted JS!";
} else {
    echo "Could not find script block";
}
