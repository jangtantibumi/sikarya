<?php
$files = glob('resources/views/*/*.blade.php');
foreach ($files as $f) {
    $c = file_get_contents($f);
    if (strpos($c, '\\') !== false || strpos($c, '\\$') !== false) {
        $c = str_replace('\\', '', $c);
        $c = str_replace('\\$', '$', $c);
        file_put_contents($f, $c);
        echo "Fixed " . $f . "\n";
    }
}
