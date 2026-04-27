<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Struk - {{ $transaction->kode_transaksi }}</title>
    <style>
        body {
            font-family: monospace;
            font-size: 12px;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        hr { border: 1px dashed #000; margin: 5px 0; }
        table { width: 100%; }
        .total { font-weight: bold; font-size: 14px; }
        @media print {
            body { margin: 0; padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="text-center">
        <h4>TOKO POS SYSTEM</h4>
        <p>{{ now()->format('d/m/Y H:i:s') }}<br>
        Kasir: {{ $transaction->user->name }}<br>
        No: {{ $transaction->kode_transaksi }}
        @if($transaction->customer)
            <br>Member: {{ $transaction->customer->nama }}
        @endif
        </p>
    </div>
    <hr>
    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Qty</th>
                <th>Harga</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->items as $item)
            <tr>
                <td>{{ $item->product->nama_produk }}</td>
                <td class="text-right">{{ $item->qty }}</td>
                <td class="text-right">{{ number_format($item->harga_saat_transaksi,0,',','.') }}</td>
                <td class="text-right">{{ number_format($item->subtotal,0,',','.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <hr>
    <table>
        <tr><td>Subtotal</td><td class="text-right">{{ number_format($transaction->subtotal,0,',','.') }}</td></tr>
        @if($transaction->diskon_persen > 0)
        <tr><td>Diskon {{ $transaction->diskon_persen }}%</td><td class="text-right">-{{ number_format($transaction->subtotal * $transaction->diskon_persen / 100,0,',','.') }}</td></tr>
        @endif
        @if($transaction->diskon_nominal > 0)
        <tr><td>Diskon Nominal</td><td class="text-right">-{{ number_format($transaction->diskon_nominal,0,',','.') }}</td></tr>
        @endif
        <tr class="total"><td>TOTAL</td><td class="text-right">{{ number_format($transaction->total,0,',','.') }}</td></tr>
        <tr><td>Bayar</td><td class="text-right">{{ number_format($transaction->bayar,0,',','.') }}</td></tr>
        <tr><td>Kembalian</td><td class="text-right">{{ number_format($transaction->kembalian,0,',','.') }}</td></tr>
    </table>
    <hr>
    <div class="text-center">
        Terima kasih!<br>
        @if($transaction->customer && $transaction->customer->email)
        <small>Struk telah dikirim ke {{ $transaction->customer->email }}</small>
        @endif
        <br><br>
        <button class="no-print" onclick="window.close()">Tutup</button>
    </div>
</body>
</html>