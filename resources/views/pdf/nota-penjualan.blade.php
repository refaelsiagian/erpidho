@extends('pdf.layout', ['title' => 'Nota Penjualan ' . $transaksi->kode_transaksi])

@section('content')
    <div class="doc-title">Nota Penjualan Material</div>

    <table class="meta">
        <tr>
            <td style="width: 55%;">
                <strong>No. Transaksi:</strong> {{ $transaksi->kode_transaksi }}<br>
                <strong>Tanggal:</strong> {{ $transaksi->tanggal->format('d M Y H:i') }}<br>
                <strong>Kasir:</strong> {{ $transaksi->user->nama_lengkap }}
            </td>
            <td style="width: 45%;">
                <strong>Pelanggan:</strong> {{ $transaksi->pelanggan?->nama_pelanggan ?? 'Pelanggan Umum' }}<br>
                <strong>Metode Bayar:</strong> {{ $transaksi->metode_bayar->value }}<br>
                <strong>Status:</strong> {{ $transaksi->status_pembayaran->value }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Material</th>
                <th class="center">Qty</th>
                <th class="right">Harga Satuan</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaksi->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->material->nama_barang }}</td>
                    <td class="center">{{ $detail->jumlah }} {{ $detail->material->satuan->value }}</td>
                    <td class="right">{{ \App\Support\Rupiah::format($detail->harga_satuan) }}</td>
                    <td class="right">{{ \App\Support\Rupiah::format($detail->subtotal) }}</td>
                </tr>
            @endforeach
            @if ($transaksi->biaya_pengiriman > 0)
                <tr>
                    <td colspan="4" class="right">Biaya Pengiriman</td>
                    <td class="right">{{ \App\Support\Rupiah::format($transaksi->biaya_pengiriman) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td colspan="4" class="right">TOTAL BAYAR</td>
                <td class="right">{{ \App\Support\Rupiah::format($transaksi->total_bayar) }}</td>
            </tr>
        </tbody>
    </table>
@endsection

