<?php
$files = [
    'resources/views/certificates/internship.blade.php',
    'resources/views/components/global-loading.blade.php',
    'resources/views/documents/index.blade.php',
    'resources/views/organization/index.blade.php',
    'resources/views/pdf/salary-slip.blade.php',
    'resources/views/security/user-authority.blade.php'
];
foreach ($files as $f) {
    $c = file_get_contents($f);
    $c = str_replace('AppModelsUser', '\App\Models\User', $c);
    $c = str_replace('CarbonCarbon', '\Carbon\Carbon', $c);
    file_put_contents($f, $c);
    echo "Restored " . $f . "\n";
}
