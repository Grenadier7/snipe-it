<!DOCTYPE html>
<html>
<head>
    <title>LendIT Label - {{ $accessory->name }}</title>
    <style>
        @page { size: 50mm 30mm; margin: 0; } /* Standard-Etikettengröße */
        body { font-family: sans-serif; margin: 5px; text-align: center; }
        .qr-code { margin-top: 5px; }
        .name { font-weight: bold; font-size: 10pt; margin-bottom: 2px; }
        .id { font-size: 8pt; color: #555; }
    </style>
</head>
<body onload="window.print();"> <div class="name">{{ $accessory->name }}</div>
<div class="id">ID: {{ $accessory->id }}</div>

<div class="qr-code">
    {!! \DNS2D::getBarcodeSVG(route('accessories.show', $accessory->id), 'QRCODE', 3, 3) !!}
</div>
</body>
</html>