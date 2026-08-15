<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title><?php echo e($document->document_number); ?> — Sertifikat Magang Suba Arch</title>
    <style>
        :root { --ink:#172034; --gold:#d3aa34; --soft:#f5f1e7; --green:#17764c; --red:#b42318; }
        * { box-sizing:border-box; }
        body { margin:0; color:var(--ink); background:#e8e8e5; font-family:Inter,Arial,sans-serif; }
        .toolbar { display:flex; justify-content:center; gap:12px; padding:18px; }
        .toolbar a,.toolbar button { border:0; border-radius:10px; padding:11px 16px; font-weight:700; cursor:pointer; text-decoration:none; color:white; background:var(--ink); }
        .toolbar a { background:var(--green); }
        .certificate { width:min(1120px,calc(100vw - 32px)); aspect-ratio:297/210; min-height:730px; margin:0 auto 32px; position:relative; overflow:hidden; background:#fff; border:14px solid var(--ink); box-shadow:0 22px 60px rgba(23,32,52,.18); padding:65px 80px; }
        .certificate-background { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; z-index:0; }
        .certificate > :not(.certificate-background) { position:relative; z-index:1; }
        .certificate.has-template { border:0; }
        .certificate.has-template::before,.certificate.has-template::after { display:none; }
        .certificate.has-template .statement { padding:10px 18px; border-radius:12px; background:rgba(255,255,255,.82); }
        .certificate::before,.certificate::after { content:""; position:absolute; width:240px; height:240px; border:35px solid var(--gold); opacity:.17; transform:rotate(45deg); }
        .certificate::before { top:-155px; left:-155px; }
        .certificate::after { right:-155px; bottom:-155px; }
        .brand { display:flex; align-items:center; justify-content:center; gap:14px; letter-spacing:.16em; font-size:15px; font-weight:800; }
        .mark { display:grid; place-items:center; width:44px; height:44px; border-radius:12px; color:var(--ink); background:var(--gold); font-size:21px; }
        h1 { margin:42px 0 8px; text-align:center; font-family:Georgia,serif; font-size:56px; letter-spacing:.04em; font-weight:500; }
        .subtitle { text-align:center; color:#667085; letter-spacing:.22em; font-size:13px; font-weight:700; }
        .presented { text-align:center; margin-top:42px; color:#667085; }
        .name { margin:10px auto 20px; width:max-content; max-width:90%; border-bottom:2px solid var(--gold); padding:0 28px 9px; font:italic 46px Georgia,serif; text-align:center; }
        .statement { max-width:760px; margin:0 auto; text-align:center; font-size:17px; line-height:1.75; }
        .statement strong { color:#8c6a09; }
        .signatures { display:grid; grid-template-columns:1fr 1fr; align-items:end; gap:70px; margin-top:55px; }
        .meta,.signer { text-align:center; }
        .line { border-top:1px solid #98a2b3; padding-top:9px; margin-top:35px; }
        .signer .signature { color:var(--gold); font:italic 27px Georgia,serif; margin-bottom:4px; }
        .signer .signature-image { display:block; width:170px; height:72px; margin:0 auto 4px; object-fit:contain; }
        .verify { position:absolute; left:32px; right:32px; bottom:18px; display:flex; justify-content:space-between; align-items:center; gap:20px; font-size:10px; color:#667085; }
        .verify code { font-size:9px; }
        .qr-block { position:absolute; right:28px; bottom:48px; display:grid; justify-items:center; gap:3px; padding:6px; border-radius:8px; background:white; box-shadow:0 2px 10px rgba(0,0,0,.12); }
        .qr-block img { width:92px; height:92px; }
        .qr-block span { max-width:100px; text-align:center; font-size:8px; color:#344054; }
        .status { padding:5px 9px; border-radius:20px; color:white; background:<?php echo e($isValid ? 'var(--green)' : 'var(--red)'); ?>; font-weight:800; }
        @media (max-width:700px) {
            .certificate { padding:40px 28px 85px; min-height:850px; }
            h1 { font-size:38px; }
            .name { font-size:34px; }
            .signatures { grid-template-columns:1fr; gap:16px; }
            .verify { flex-direction:column; align-items:flex-start; }
        }
        @media print {
            @page { size:A4 landscape; margin:0; }
            body { background:#fff; }
            .toolbar { display:none; }
            .certificate { width:297mm; height:210mm; min-height:0; margin:0; box-shadow:none; border-width:10mm; padding:16mm 24mm; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
        <a href="<?php echo e(route('certificates.verify', ['token' => $document->verification_token])); ?>">Verifikasi Keaslian</a>
    </div>

    <main class="certificate <?php echo e($backgroundUrl ? 'has-template' : ''); ?>">
        <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($backgroundUrl): ?>
            <img class="certificate-background" src="<?php echo e($backgroundUrl); ?>" alt="">
        <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
        <div class="brand"><span class="mark">SA</span> SUBA ARCH</div>
        <h1>Sertifikat Magang</h1>
        <div class="subtitle">CERTIFICATE OF INTERNSHIP</div>

        <div class="presented">Dengan ini diberikan kepada</div>
        <div class="name"><?php echo e(data_get($document->content, 'recipient_name')); ?></div>
        <p class="statement">
            <?php echo e(data_get($document->content, 'description')); ?>

            Program <strong><?php echo e(data_get($document->content, 'program_name')); ?></strong>,
            periode <?php echo e(\Carbon\Carbon::parse(data_get($document->content, 'start_date'))->translatedFormat('d F Y')); ?>

            sampai <?php echo e(\Carbon\Carbon::parse(data_get($document->content, 'end_date'))->translatedFormat('d F Y')); ?>,
            dengan penilaian <strong><?php echo e(data_get($document->content, 'performance_label')); ?></strong>.
        </p>

        <div class="signatures">
            <div class="meta">
                <div><?php echo e($document->issued_at?->translatedFormat('d F Y')); ?></div>
                <div class="line">Tanggal Diterbitkan</div>
            </div>
            <div class="signer">
                <?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if BLOCK]><![endif]--><?php endif; ?><?php if($signatureUrl): ?>
                    <img class="signature-image" src="<?php echo e($signatureUrl); ?>" alt="Tanda tangan pembimbing">
                <?php else: ?>
                    <div class="signature"><?php echo e($document->signatures->first()?->signer?->name ?? 'Menunggu tanda tangan'); ?></div>
                <?php endif; ?><?php if(\Livewire\Mechanisms\ExtendBlade\ExtendBlade::isRenderingLivewireComponent()): ?><!--[if ENDBLOCK]><![endif]--><?php endif; ?>
                <strong><?php echo e($document->signatures->first()?->signer?->name ?? 'Belum ditandatangani'); ?></strong>
                <div><?php echo e($document->signatures->first()?->signer_role ?? 'Pimpinan Suba Arch'); ?></div>
                <div class="line">Persetujuan elektronik internal</div>
            </div>
        </div>

        <div class="qr-block">
            <img src="<?php echo e($qrDataUri); ?>" alt="QR verifikasi sertifikat">
            <span>Pindai untuk verifikasi keaslian</span>
        </div>

        <footer class="verify">
            <span>No. <?php echo e($document->document_number); ?></span>
            <span>Hash: <code><?php echo e($document->document_hash ?: 'Belum tersedia'); ?></code></span>
            <span>Dibuat mandiri oleh ERP Suba Arch</span>
            <span class="status"><?php echo e($isValid ? 'VALID' : ($document->revoked_at ? 'DICABUT' : 'BELUM VALID')); ?></span>
        </footer>
    </main>
</body>
</html>
<?php /**PATH D:\suba-erp-master-local-latest\resources\views\certificates\internship.blade.php ENDPATH**/ ?>