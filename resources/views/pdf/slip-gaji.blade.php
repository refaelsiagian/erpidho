@extends('pdf.layout', ['title' => 'Slip Gaji ' . $gaji->kode_gaji])

@section('content')
    <div class="doc-title">Slip Gaji Karyawan Tukang</div>

    <table class="meta">
        <tr>
            <td style="width: 55%;">
                <strong>No. Slip Gaji:</strong> {{ $gaji->kode_gaji }}<br>
                <strong>Tanggal Diproses:</strong> {{ $gaji->tanggal_diproses->format('d M Y') }}<br>
                <strong>Status Pembayaran:</strong> {{ $gaji->status_bayar->value }}
            </td>
            <td style="width: 45%;">
                <strong>Nama Tukang:</strong> {{ $gaji->karyawanTukang->nama_tukang }} ({{ $gaji->karyawanTukang->kode_tukang }})<br>
                <strong>Proyek:</strong> {{ $gaji->pesananBorongan->kode_borongan }}<br>
                <strong>Lokasi:</strong> {{ $gaji->pesananBorongan->lokasi_proyek }}
            </td>
        </tr>
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Keterangan Pekerjaan</th>
                <th class="right">Nominal Gaji</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Upah pengerjaan instalasi borongan ({{ $gaji->pesananBorongan->kode_borongan }} — {{ $gaji->pesananBorongan->pelanggan->nama_pelanggan }})</td>
                <td class="right">{{ \App\Support\Rupiah::format($gaji->total_gaji) }}</td>
            </tr>
            <tr class="total-row">
                <td class="right">TOTAL GAJI DITERIMA</td>
                <td class="right">{{ \App\Support\Rupiah::format($gaji->total_gaji) }}</td>
            </tr>
        </tbody>
    </table>

    @if ($gaji->catatan)
        <div class="note-box">
            <strong>Catatan Pemilik:</strong><br>
            {{ $gaji->catatan }}
        </div>
    @endif
@endsection

