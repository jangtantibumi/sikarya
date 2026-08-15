<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex,nofollow">
    <title>Verifikasi Sertifikat — Suba Arch</title>
    <style>
        :root { --ink:#172034; --gold:#d3aa34; --green:#17764c; --red:#b42318; }
        * { box-sizing:border-box; }
        body { margin:0; min-height:100vh; display:grid; place-items:center; padding:24px; background:linear-gradient(145deg,#0f1728,#172034); color:var(--ink); font-family:Inter,Arial,sans-serif; }
        main { width:min(660px,100%); padding:34px; border-radius:20px; background:white; box-shadow:0 30px 90px rgba(0,0,0,.35); }
        .brand { color:#8c6a09; letter-spacing:.14em; font-size:13px; font-weight:900; }
        .result { display:flex; align-items:center; gap:16px; margin:24px 0; padding:18px; border-radius:14px; background:{{ $isValid ? '#ecfdf3' : '#fef3f2' }}; }
        .icon { display:grid; place-items:center; flex:0 0 48px; height:48px; border-radius:50%; color:white; font-size:24px; font-weight:900; background:{{ $isValid ? 'var(--green)' : 'var(--red)' }}; }
        h1 { margin:0; font-size:25px; }
        .result p { margin:5px 0 0; color:#667085; line-height:1.5; }
        dl { display:grid; grid-template-columns:180px 1fr; gap:13px 18px; border-top:1px solid #e4e7ec; padding-top:22px; }
        dt { color:#667085; }
        dd { margin:0; font-weight:700; overflow-wrap:anywhere; }
        .notice { margin-top:22px; padding:14px; border-radius:10px; color:#5f4a0b; background:#fff8dd; font-size:13px; line-height:1.55; }
        a { display:inline-block; margin-top:22px; padding:11px 16px; border-radius:9px; color:white; background:var(--ink); text-decoration:none; font-weight:700; }
        @media (max-width:550px) { dl { grid-template-columns:1fr; gap:5px; } dd { margin-bottom:10px; } }
    </style>
</head>
<body>
    <main>
        <div class="brand">SUBA ARCH · DOCUMENT VERIFICATION</div>
        <div class="result">
            <div class="icon">{{ $isValid ? '✓' : '!' }}</div>
            <div>
                <h1>{{ $isValid ? 'Dokumen valid dan tidak berubah' : 'Dokumen tidak berlaku' }}</h1>
                <p>
                    @if($document->revoked_at)
                        Sertifikat ini telah dicabut oleh penerbit.
                    @elseif(!$document->signed_at)
                        Sertifikat masih berupa draft dan belum ditandatangani.
                    @else
                        Integritas atau tanda tangan internal dokumen tidak dapat diverifikasi.
                    @endif
                </p>
            </div>
        </div>

        <dl>
            <dt>Nomor dokumen</dt><dd>{{ $document->document_number }}</dd>
            <dt>Penerima</dt><dd>{{ data_get($document->content, 'recipient_name') }}</dd>
            <dt>Program</dt><dd>{{ data_get($document->content, 'program_name') }}</dd>
            <dt>Periode</dt><dd>{{ data_get($document->content, 'start_date') }} s.d. {{ data_get($document->content, 'end_date') }}</dd>
            <dt>Diterbitkan</dt><dd>{{ $document->issued_at?->translatedFormat('d F Y') }}</dd>
            <dt>Penandatangan</dt><dd>{{ $document->signatures->first()?->signer?->name ?? 'Belum ada' }}</dd>
            <dt>Status</dt><dd>{{ strtoupper($document->status) }}</dd>
            <dt>Hash integritas</dt><dd>{{ $document->document_hash ?: 'Belum tersedia' }}</dd>
            @if($document->revoked_at)
                <dt>Alasan pencabutan</dt><dd>{{ $document->revocation_reason }}</dd>
            @endif
        </dl>

        <div class="notice">
            Tanda tangan pada dokumen ini adalah persetujuan elektronik internal berbasis akun ERP Suba Arch
            dan hash integritas. Fitur ini belum merupakan Tanda Tangan Elektronik Tersertifikasi dari PSrE Indonesia.
        </div>
        <a href="{{ route('certificates.show', ['token' => $document->verification_token]) }}">Lihat Sertifikat</a>
    </main>
</body>
</html>
