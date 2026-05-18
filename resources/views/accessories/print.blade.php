<!DOCTYPE html>
<html>
<head>
    <title>LendIT Label - {{ $accessory->name }}</title>
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

        /* Das SVG-Bild (QR Code) darf maximal 18x18mm groß werden */
        .qr-code svg { width: 18mm; height: 18mm; }
    </style>
</head>
<body onload="window.print();">
<div class="name">{{ $accessory->name }}</div>
<div class="id">ID: {{ $accessory->id }}</div>

<div class="qr-code">
    @php
        $barcode = new \Com\Tecnick\Barcode\Barcode;
        // Größe im Code etwas reduziert
        $qrCode = $barcode->getBarcodeObj('QRCODE', route('accessories.show', $accessory->id), 20, 20, 'black', [-2, -2, -2, -2]);
    @endphp
    {!! $qrCode->getSvgCode() !!}
</div>
</body>
</html>