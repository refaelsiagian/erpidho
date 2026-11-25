@extends('pdf.layout', ['title' => 'Nota Borongan ' . $borongan->kode_borongan])

@section('content')
    <div class="doc-title">Nota Pesanan Paket Borongan</div>

    <table class="meta">
        <tr>
            <td style="width: 55%;">
                <strong>No. Borongan:</strong> {{ $borongan->kode_borongan }}<br>
                <strong>Tanggal Mulai:</strong> {{ $borongan->tanggal_mulai->format('d M Y') }}<br>
                <strong>Target Selesai:</strong> {{ $borongan->tanggal_target->format('d M Y') }}<br>
                <strong>Status Proyek:</strong> {{ $borongan->status_proyek->value }}
            </td>
            <td style="width: 45%;">
                <strong>Pelanggan:</strong> {{ $borongan->pelanggan->nama_pelanggan }}<br>
                <strong>No. Telepon:</strong> {{ $borongan->pelanggan->no_telepon ?? '-' }}<br>
                <strong>Lokasi Proyek:</strong> {{ $borongan->lokasi_proyek }}<br>
                <strong>Pembayaran:</strong> {{ $borongan->status_pembayaran->value }} ({{ $borongan->metode_bayar->value }})
                @if ($borongan->tanggal_lunas)
                    — {{ $borongan->tanggal_lunas->format('d M Y') }}
                @endif
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>No</th>
                <th>Material Dialokasikan</th>
                <th class="center">Jumlah</th>
                <th class="right">Harga Satuan</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($borongan->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->material->nama_barang }}</td>
                    <td class="center">{{ $detail->jumlah_material }} {{ $detail->material->satuan->value }}</td>
                    <td class="right">{{ \App\Support\Rupiah::format($detail->harga_satuan) }}</td>
                    <td class="right">{{ \App\Support\Rupiah::format($detail->subtotal_material) }}</td>
                </tr>
            @endforeach
            <tr>
                <td colspan="4" class="right">Biaya Jasa Pemasangan</td>
                <td class="right">{{ \App\Support\Rupiah::format($borongan->biaya_jasa) }}</td>
            </tr>
            @if ($borongan->biaya_pengiriman > 0)
                <tr>
                    <td colspan="4" class="right">Biaya Pengiriman</td>
                    <td class="right">{{ \App\Support\Rupiah::format($borongan->biaya_pengiriman) }}</td>
                </tr>
            @endif
            <tr class="total-row">
                <td colspan="4" class="right">TOTAL BIAYA BORONGAN</td>
                <td class="right">{{ \App\Support\Rupiah::format($borongan->total_biaya) }}</td>
            </tr>
        </tbody>
    </table>
@endsection

