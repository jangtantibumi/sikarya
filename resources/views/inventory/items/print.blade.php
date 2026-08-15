<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Print Label - {{ $item->name }}</title>
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
        <h2>{{ $item->name }}</h2>
        <div class="sku">SKU: {{ $item->sku }}</div>
        <div class="barcode">*{{ $item->barcode ?? $item->sku }}*</div>
        <p>Kategori: {{ optional($item->category)->name }} | Brand: {{ optional($item->brand)->name }}</p>
    </div>
</body>
</html>
