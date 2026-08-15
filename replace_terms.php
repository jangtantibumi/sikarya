<?php

$dir = new RecursiveDirectoryIterator(__DIR__ . '/resources/views');
$ite = new RecursiveIteratorIterator($dir);

$strReplacements = [
    'Goods Receipt' => 'Barang Masuk',
    'Purchase Order' => 'Pesanan Pembelian',
    'Bill of Materials' => 'Resep / Komposisi',
    'Search Inventory' => 'Cari Gudang',
    'Inventory Management System' => 'Sistem Manajemen Gudang & Stok',
    'Inventory Management' => 'Gudang & Stok'
];

$regexReplacements = [
    '/\bInventory\b(?!\.)(?!-)/' => 'Gudang & Stok',
    '/\bProduction\b(?!\.)(?!-)/' => 'Produksi',
    '/\bLedger\b/i' => 'Buku Catatan',
    '/\bInvoice\b(?!\.)(?!-)/' => 'Tagihan',
    '/\bInvoices\b(?!\.)(?!-)/' => 'Tagihan',
    '/\bAsset\b(?!\()/i' => 'Aset',
    '/\bAssets\b/i' => 'Aset',
    '/\bBOM\b/' => 'Resep / Komposisi'
];

$count = 0;

foreach($ite as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $original = $content;
        
        $content = str_replace(array_keys($strReplacements), array_values($strReplacements), $content);
        
        foreach($regexReplacements as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        if($content !== $original) {
            file_put_contents($path, $content);
            echo "Updated: $path\n";
            $count++;
        }
    }
}

echo "Total files updated: $count\n";
