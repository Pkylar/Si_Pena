@extends('layouts.app')
@section('title', 'Pengajuan Dana')

@section('content')
<div class="page-header">
    <h1>Pengajuan Dana</h1>
</div>

<div class="card">
    <div class="card-header">
        <span>Daftar Pengajuan Dana</span>
        @if(in_array(auth()->user()->role, ['mahasiswa', 'ormawa']))
            <a href="/pengajuan/create" class="btn btn-primary"><i class="fas fa-plus"></i> Ajukan</a>
        @endif
    </div>
    <div class="card-body">
        @if($pengajuan->isEmpty())
            <div class="empty-state">
                <i class="fas fa-clipboard-check"></i>
                <p>Belum ada Pengajuan Dana</p>
            </div>
        @else
            <table class="table">
                <thead>
                    <tr>
                        <th>Diajukan Oleh</th>
                        <th>Nama Kegiatan</th>
                        <th>Dana yang Diajukan</th>
                        <th>Dana yang Disetujui</th>
                        <th>Tanggal Pengajuan</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pengajuan as $p)
                    <tr>
                        <td>{{ $p->user->name }}</td>
                        <td>{{ $p->nama_kegiatan }}</td>
                        <td>Rp. {{ number_format($p->dana_diajukan, 2, ',', '.') }}</td>
                        <td>{{ ($p->dana_disetujui_keuangan ?? $p->dana_disetujui) ? 'Rp. ' . number_format($p->dana_disetujui_keuangan ?? $p->dana_disetujui, 2, ',', '.') : '-' }}</td>
                        <td>{{ $p->created_at->format('d/m/Y') }}</td>
                        <td>
                            @php
                                $badgeClass = match(true) {
                                    str_contains($p->status, 'Ditolak') => 'badge-danger',
                                    str_contains($p->status, 'Disetujui') || str_contains($p->status, 'Selesai') => 'badge-success',
                                    str_contains($p->status, 'Belum') => 'badge-warning',
                                    default => 'badge-info',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $p->status }}</span>
                        </td>
                        <td><a href="/pengajuan/{{ $p->id }}" class="btn btn-primary btn-sm">Detail</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</div>
@endsection
