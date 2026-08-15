<?php
$f = 'public/js/purchasing.js';
$c = file_get_contents($f);
$c = str_replace(
    'class="ios-input po-line-product"',
    'class="ios-input po-line-product" style="padding: 8px 12px; height: 38px; width: 100%;"',
    $c
);
$c = str_replace(
    'class="ios-input po-line-qty"',
    'class="ios-input po-line-qty" style="padding: 8px 12px; height: 38px; width: 100%; box-sizing: border-box;"',
    $c
);
$c = str_replace(
    'class="ios-input po-line-price"',
    'class="ios-input po-line-price" style="padding: 8px 12px; height: 38px; width: 100%; box-sizing: border-box;"',
    $c
);
file_put_contents($f, $c);
echo "Styling applied!";
