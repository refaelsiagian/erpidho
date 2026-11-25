@extends('pdf.layout', ['title' => 'Daftar Pesanan Bahan ' . $pengadaan->kode_pengadaan])

@section('content')
    <div class="doc-title">Daftar Pesanan Pengadaan Bahan Baku</div>

    <table class="meta">
        <tr>
            <td style="width: 55%;">
                <strong>No. Pengadaan:</strong> {{ $pengadaan->kode_pengadaan }}<br>
                <strong>Tanggal Pesan:</strong> {{ $pengadaan->tanggal_pesan->format('d M Y') }}<br>
                <strong>Status:</strong> {{ $pengadaan->status_penerimaan->value }}
            </td>
            <td style="width: 45%;">
                <strong>Supplier:</strong> {{ $pengadaan->supplier->nama_supplier }}<br>
                <strong>Telepon:</strong> {{ $pengadaan->supplier->no_telepon ?? '-' }}<br>
                <strong>Tanggal Terima:</strong> {{ $pengadaan->tanggal_terima?->format('d M Y') ?? '-' }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Material</th>
                <th class="center">Jumlah Pesan</th>
                <th class="right">Harga Beli Satuan</th>
                <th class="right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pengadaan->details as $index => $detail)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $detail->material->nama_barang }}</td>
                    <td class="center">{{ $detail->jumlah_pesan }} {{ $detail->material->satuan->value }}</td>
                    <td class="right">{{ \App\Support\Rupiah::format($detail->harga_beli_satuan) }}</td>
                    <td class="right">{{ \App\Support\Rupiah::format($detail->subtotal) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="4" class="right">TOTAL PENGADAAN</td>
                <td class="right">{{ \App\Support\Rupiah::format($pengadaan->total_pengadaan) }}</td>
            </tr>
        </tbody>
    </table>
@endsection

