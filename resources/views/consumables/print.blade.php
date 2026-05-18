<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <title>LendIT Label - {{ $consumable->name }}</title>
    <style>
        body { font-family: sans-serif; margin: 5px; text-align: center; }
        .qr-code { margin-top: 5px; }
        .name { font-weight: bold; font-size: 10pt; margin-bottom: 2px; }
        .id { font-size: 8pt; color: #555; }
    </style>
</head>
<body onload="window.print();">
    <div class="name">{{ $consumable->name }}</div>
    <div class="id">ID: {{ $consumable->id }}</div>
    <div class="qr-code">
        @php
            $barcode = new \Com\Tecnick\Barcode\Barcode;
            $qrCode = $barcode->getBarcodeObj('QRCODE', route('consumables.show', $consumable->id), 3, 3, 'black', [-2, -2, -2, -2]);
        @endphp
        {!! $qrCode->getSvgCode() !!}
    </div>
</body>
</html>
