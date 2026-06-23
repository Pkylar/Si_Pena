@extends('layouts.app')
@section('title', 'Report Akhir')

@section('content')
<div class="page-header">
    <h1>Report Akhir Pengajuan Dana</h1>
</div>

<div style="margin-bottom:20px;display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <a href="/report" class="btn {{ $triwulan === 'all' ? 'btn-primary' : '' }}" style="{{ $triwulan !== 'all' ? 'background:#ddd;color:#333;' : '' }}">Semua</a>
    @for($t = 1; $t <= 4; $t++)
        <a href="/report?triwulan={{ $t }}" class="btn {{ $triwulan == $t ? 'btn-primary' : '' }}" style="{{ $triwulan != $t ? 'background:#ddd;color:#333;' : '' }}">Triwulan {{ $t }}</a>
    @endfor
    <a href="/report/export?triwulan={{ $triwulan }}" class="btn btn-success"><i class="fas fa-download"></i> Download CSV</a>
</div>

{{-- Summary per triwulan --}}
<div class="card" style="margin-bottom:20px;">
    <div class="card-header"><span>Ringkasan Per Triwulan</span></div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>Triwulan</th>
                    <th>Jumlah Pengajuan</th>
                    <th>Total Dana Diajukan</th>
                    <th>Total Dana Disetujui</th>
                </tr>
            </thead>
            <tbody>
                @foreach($summaryPerTriwulan as $t => $s)
                <tr>
                    <td>Triwulan {{ $t }}</td>
                    <td>{{ $s['jumlah'] }}</td>
                    <td>Rp. {{ number_format($s['total_diajukan'], 0, ',', '.') }}</td>
                    <td>Rp. {{ number_format($s['total_disetujui'], 0, ',', '.') }}</td>
                </tr>
                @endforeach
                <tr style="font-weight:bold;border-top:2px solid #333;">
                    <td>Total</td>
                    <td>{{ collect($summaryPerTriwulan)->sum('jumlah') }}</td>
                    <td>Rp. {{ number_format(collect($summaryPerTriwulan)->sum('total_diajukan'), 0, ',', '.') }}</td>
                    <td>Rp. {{ number_format(collect($summaryPerTriwulan)->sum('total_disetujui'), 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

{{-- Detail pengajuan --}}
<div class="card">
    <div class="card-header"><span>Detail Pengajuan {{ $triwulan === 'all' ? 'Semua Triwulan' : 'Triwulan ' . $triwulan }}</span></div>
    <div class="card-body">
        @if($pengajuan->isEmpty())
            <div class="empty-state">
                <i class="fas fa-clipboard-check"></i>
                <p>Belum ada pengajuan yang disetujui untuk periode ini.</p>
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Pengaju</th>
                        <th>Nama Kegiatan</th>
                        <th>Jenis</th>
                        <th>Dana Diajukan</th>
                        <th>Dana Disetujui Kaur Keuangan</th>
                        <th>Dana Disetujui WD2</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuan as $i => $p)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ $p->user->organization_name ?? $p->user->name }}</td>
                        <td>{{ $p->nama_kegiatan }}</td>
                        <td>{{ $p->jenis_kegiatan }}</td>
                        <td>Rp. {{ number_format($p->dana_diajukan, 0, ',', '.') }}</td>
                        <td>{{ $p->dana_disetujui_keuangan ? 'Rp. ' . number_format($p->dana_disetujui_keuangan, 0, ',', '.') : '-' }}</td>
                        <td>Rp. {{ number_format($p->dana_disetujui, 0, ',', '.') }}</td>
                        <td><span class="badge badge-success">{{ $p->status }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div style="margin-top:15px;padding:15px;background:#f8f9fa;border-radius:8px;">
                <strong>Total Dana Diajukan:</strong> Rp. {{ number_format($totalDiajukan, 0, ',', '.') }} |
                <strong>Total Dana Disetujui:</strong> Rp. {{ number_format($totalDisetujui, 0, ',', '.') }}
            </div>
        @endif
    </div>
</div>
@endsection
