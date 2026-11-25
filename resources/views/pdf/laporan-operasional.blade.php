@extends('pdf.layout', ['title' => 'Laporan Operasional'])

@section('content')
    <div class="doc-title">Laporan Operasional Usaha Ridho Gipsum</div>
    <p><strong>Periode:</strong> {{ \Carbon\Carbon::parse($dari)->format('d M Y') }} s/d {{ \Carbon\Carbon::parse($sampai)->format('d M Y') }}</p>

    <table class="items" style="margin-bottom: 16px;">
        <thead>
            <tr>
                <th>Ringkasan Operasional</th>
                <th class="right">Nilai</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Penjualan Langsung ({{ $penjualans->count() }} transaksi)</td>
                <td class="right">{{ \App\Support\Rupiah::format($totalPenjualanLangsung) }}</td>
            </tr>
            <tr>
                <td>Pesanan Borongan ({{ $borongans->count() }} proyek)</td>
                <td class="right">{{ \App\Support\Rupiah::format($totalBorongan) }}</td>
            </tr>
            <tr class="total-row">
                <td>Total Omzet Periode</td>
                <td class="right">{{ \App\Support\Rupiah::format($totalOmzet) }}</td>
            </tr>
            <tr>
                <td>Pengeluaran Pengadaan Bahan (Diterima)</td>
                <td class="right">{{ \App\Support\Rupiah::format($totalPengadaan) }}</td>
            </tr>
            <tr>
                <td>Pengeluaran Gaji Tukang ({{ $penggajians->count() }} slip)</td>
                <td class="right">{{ \App\Support\Rupiah::format($totalGajiTukang) }}</td>
            </tr>
        </tbody>
    </table>

    <div class="doc-title" style="font-size: 12px;">Laporan Sisa Stok Material</div>
    <table class="items">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Material</th>
                <th class="center">Stok Sisa</th>
                <th class="center">Min</th>
                <th class="center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($materials as $m)
                <tr>
                    <td>{{ $m->kode_barang }}</td>
                    <td>{{ $m->nama_barang }}</td>
                    <td class="center">{{ $m->stok_sisa }} {{ $m->satuan->value }}</td>
                    <td class="center">{{ $m->batas_minimum }}</td>
                    <td class="center">{{ $m->isStokKritis() ? 'Kritis' : 'Aman' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endsection

