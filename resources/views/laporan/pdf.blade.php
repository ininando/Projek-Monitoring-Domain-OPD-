<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>{{ $laporan->judul }}</title>
    <style>
        @page { margin: 28px 32px 48px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 10px; color: #020617; }
        .kop { width: 100%; border-bottom: 3px double #0F172A; padding-bottom: 8px; margin-bottom: 14px; }
        .kop td { vertical-align: middle; }
        .kop .logo { width: 64px; }
        .kop .logo img { width: 56px; height: 56px; }
        .kop .logo-placeholder { width: 56px; height: 56px; border: 1px dashed #94A3B8; font-size: 8px; color: #475569; text-align: center; line-height: 56px; }
        .kop .instansi { text-align: center; }
        .kop .instansi .pemkot { font-size: 12px; font-weight: bold; letter-spacing: .5px; }
        .kop .instansi .dinas { font-size: 15px; font-weight: bold; color: #0F172A; }
        .kop .instansi .alamat { font-size: 9px; color: #475569; }
        h1 { font-size: 14px; text-align: center; margin: 0 0 4px; text-transform: uppercase; }
        .meta { width: 100%; margin-bottom: 10px; font-size: 10px; }
        .meta td { padding: 1px 0; }
        .meta .label { width: 150px; color: #475569; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th { background: #0F172A; color: #fff; font-weight: bold; padding: 6px 5px; text-align: left; border: 1px solid #0F172A; }
        table.data td { padding: 5px; border: 1px solid #CBD5E1; vertical-align: top; }
        table.data tr:nth-child(even) td { background: #F8FAFC; }
        table.data tfoot td { background: #E2E8F0; font-weight: bold; }
        .tengah { text-align: center; }
        .kosong { text-align: center; color: #475569; padding: 16px; }
        .footer { position: fixed; bottom: -32px; left: 0; right: 0; font-size: 8px; color: #475569; border-top: 1px solid #CBD5E1; padding-top: 4px; }
        .critical { color: #B91C1C; font-weight: bold; }
        .warning { color: #92400E; font-weight: bold; }
        .normal { color: #166534; }
    </style>
</head>
<body>
    <div class="footer">
        {{ config('app.name') }} · Dicetak {{ $laporan->dibuatPada->translatedFormat('d F Y, H:i') }} WITA
    </div>

    <table class="kop">
        <tr>
            <td class="logo">
                @if ($logo)
                    <img src="{{ $logo }}" alt="Logo Diskominfo">
                @else
                    <div class="logo-placeholder">LOGO</div>
                @endif
            </td>
            <td class="instansi">
                <div class="pemkot">PEMERINTAH KOTA TOMOHON</div>
                <div class="dinas">DINAS KOMUNIKASI DAN INFORMATIKA</div>
                <div class="alamat">Sistem Informasi Monitoring dan Pemetaan Domain OPD</div>
            </td>
            <td class="logo"></td>
        </tr>
    </table>

    <h1>{{ $laporan->judul }}</h1>

    <table class="meta">
        <tr><td class="label">Tanggal dibuat</td><td>: {{ $laporan->dibuatPada->translatedFormat('l, d F Y H:i') }} WITA</td></tr>
        @foreach ($laporan->keteranganFilter as $label => $nilai)
            <tr><td class="label">{{ $label }}</td><td>: {{ $nilai }}</td></tr>
        @endforeach
    </table>

    <table class="data">
        <thead>
            <tr>
                <th class="tengah" style="width: 28px">No</th>
                <th style="width: 22%">Nama Domain</th>
                <th style="width: 24%">OPD</th>
                <th style="width: 9%">Status</th>
                <th style="width: 11%">Tanggal Expired</th>
                <th>Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($domains as $domain)
                <tr>
                    <td class="tengah">{{ $loop->iteration }}</td>
                    <td>{{ $domain->nama_domain }}</td>
                    <td>{{ $domain->opd->nama_opd }}</td>
                    <td>{{ $domain->status_label }}</td>
                    <td>{{ $domain->tanggal_expired->translatedFormat('d M Y') }}</td>
                    <td>
                        <span class="{{ strtolower($domain->label_expired) }}">{{ $domain->label_expired }}</span>
                        · {{ $domain->teks_sisa_hari }}
                        @if ($domain->keterangan)<br>{{ $domain->keterangan }}@endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="6" class="kosong">Tidak ada domain yang sesuai parameter laporan.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" style="text-align: right">Total Domain</td>
                <td>{{ $domains->count() }} domain</td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
