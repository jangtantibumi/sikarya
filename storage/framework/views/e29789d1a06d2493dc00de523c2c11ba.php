<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Label - <?php echo e($item->name); ?></title>
    <style>
        body { font-family: sans-serif; text-align: center; padding: 20px; }
        .label-box { border: 2px dashed #000; width: 300px; margin: 0 auto; padding: 20px; border-radius: 8px; }
        .sku { font-size: 20px; font-weight: bold; margin-top: 10px; }
        .barcode { font-family: monospace; font-size: 24px; letter-spacing: 4px; margin: 15px 0; background: #eee; padding: 10px; }
    </style>
</head>
<body onload="window.print()">
    <div class="label-box">
        <h3>SUBA ERP - INVENTORY</h3>
        <h2><?php echo e($item->name); ?></h2>
        <div class="sku">SKU: <?php echo e($item->sku); ?></div>
        <div class="barcode">*<?php echo e($item->barcode ?? $item->sku); ?>*</div>
        <p>Kategori: <?php echo e(optional($item->category)->name); ?> | Brand: <?php echo e(optional($item->brand)->name); ?></p>
    </div>
</body>
</html>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\inventory\items\print.blade.php ENDPATH**/ ?>