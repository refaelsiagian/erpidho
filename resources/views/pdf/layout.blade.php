<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Dokumen ERPIDHO' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; margin: 24px; }
        .header { border-bottom: 2px solid #0f172a; padding-bottom: 10px; margin-bottom: 16px; }
        .brand { font-size: 18px; font-weight: bold; color: #0f172a; margin: 0; }
        .subtitle { font-size: 10px; color: #475569; margin: 2px 0 0 0; }
        .doc-title { font-size: 14px; font-weight: bold; margin: 12px 0 6px 0; text-transform: uppercase; }
        .meta { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
        .meta td { padding: 3px 0; vertical-align: top; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.items th { background: #f1f5f9; text-align: left; padding: 7px 8px; border: 1px solid #cbd5e1; font-size: 10px; text-transform: uppercase; }
        table.items td { padding: 7px 8px; border: 1px solid #cbd5e1; }
        .right { text-align: right; }
        .center { text-align: center; }
        .total-row td { font-weight: bold; background: #f8fafc; font-size: 12px; }
        .note-box { margin-top: 14px; padding: 8px 10px; border: 1px solid #cbd5e1; background: #f8fafc; }
        .footer { margin-top: 28px; font-size: 10px; color: #64748b; border-top: 1px dashed #cbd5e1; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <p class="brand">USAHA RIDHO GIPSUM (ERPIDHO)</p>
        <p class="subtitle">Penjualan Material Gipsum, PVC, Rangka &amp; Jasa Pemasangan Borongan</p>
    </div>

    @yield('content')

    <div class="footer">
        Dicetak otomatis oleh Sistem Informasi ERPIDHO pada {{ now()->format('d M Y H:i') }}.
    </div>
</body>
</html>

