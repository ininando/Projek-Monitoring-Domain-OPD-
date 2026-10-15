{{-- Dirender ke sheet Excel oleh LaporanExport (FromView). Gaya inline dasar didukung PhpSpreadsheet. --}}
<table>
    <tr><td colspan="6" style="font-weight: bold; font-size: 14px;">PEMERINTAH KOTA TOMOHON</td></tr>
    <tr><td colspan="6" style="font-weight: bold; font-size: 12px;">DINAS KOMUNIKASI DAN INFORMATIKA</td></tr>
    <tr><td colspan="6" style="font-weight: bold; font-size: 13px;">{{ $laporan->judul }}</td></tr>
    <tr><td colspan="6">Tanggal dibuat: {{ $laporan->dibuatPada->translatedFormat('d F Y H:i') }} WITA</td></tr>
    @foreach ($laporan->keteranganFilter as $label => $nilai)
        <tr><td colspan="6">{{ $label }}: {{ $nilai }}</td></tr>
    @endforeach
</table>

<table>
    <thead>
        <tr>
            <th style="font-weight: bold; background-color: #0F172A; color: #FFFFFF;">No</th>
            <th style="font-weight: bold; background-color: #0F172A; color: #FFFFFF;">Nama Domain</th>
            <th style="font-weight: bold; background-color: #0F172A; color: #FFFFFF;">OPD</th>
            <th style="font-weight: bold; background-color: #0F172A; color: #FFFFFF;">Status</th>
            <th style="font-weight: bold; background-color: #0F172A; color: #FFFFFF;">Tanggal Expired</th>
            <th style="font-weight: bold; background-color: #0F172A; color: #FFFFFF;">Keterangan</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($domains as $domain)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $domain->nama_domain }}</td>
                <td>{{ $domain->opd->nama_opd }}</td>
                <td>{{ $domain->status_label }}</td>
                <td>{{ $domain->tanggal_expired->format('d/m/Y') }}</td>
                <td>{{ $domain->label_expired }} · {{ $domain->teks_sisa_hari }}{{ $domain->keterangan ? '; '.$domain->keterangan : '' }}</td>
            </tr>
        @empty
            <tr><td colspan="6">Tidak ada domain yang sesuai parameter laporan.</td></tr>
        @endforelse
        <tr>
            <td colspan="5" style="font-weight: bold; text-align: right;">Total Domain</td>
            <td style="font-weight: bold;">{{ $domains->count() }}</td>
        </tr>
    </tbody>
</table>
