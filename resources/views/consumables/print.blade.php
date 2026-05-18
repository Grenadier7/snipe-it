<!DOCTYPE html>
<html>
<head>
    <title>LendIT Label - {{ $consumable->name }}</title>
    <style>
        @page { size: 50mm 30mm; margin: 0; }

        html, body {
            width: 50mm;
            height: 30mm;
            margin: 0;
            padding: 0;
            overflow: hidden;
            font-family: sans-serif;
            text-align: center;
            background: white;
        }

        .name { font-weight: bold; font-size: 10pt; margin-top: 2mm; margin-bottom: 0; }
        .id { font-size: 8pt; color: #555; }
        .qr-code { margin-top: 1mm; }
        .qr-code svg { width: 18mm; height: 18mm; }
    </style>
</head>
<body onload="window.print();">
<div class="name">{{ $consumable->name }}</div>
<div class="id">ID: {{ $consumable->id }}</div>

<div class="qr-code">
    @php
        $barcode = new \Com\Tecnick\Barcode\Barcode;
        $qrCode = $barcode->getBarcodeObj('QRCODE', route('consumables.show', $consumable->id), 20, 20, 'black', [-2, -2, -2, -2]);
    @endphp
    {!! $qrCode->getSvgCode() !!}
</div>
</body>
</html>