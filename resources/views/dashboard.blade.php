@extends('layouts.app')
@section('title', 'Dashboard')

@section('content')
<div class="page-header">
    <h1 id="greeting-text"></h1>
    <p style="font-size:13px;color:#888;margin-top:4px;">Berikut ringkasan pengajuan dana Anda</p>
</div>

@push('scripts')
<script>
function updateGreeting() {
    const hour = new Date().getHours();
    let greeting;
    if (hour < 12) greeting = 'Selamat Pagi';
    else if (hour < 15) greeting = 'Selamat Siang';
    else if (hour < 18) greeting = 'Selamat Sore';
    else greeting = 'Selamat Malam';
    document.getElementById('greeting-text').textContent = greeting + ', {{ auth()->user()->name }} 👋';
}
updateGreeting();
setInterval(updateGreeting, 60000);
</script>
@endpush

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon pending"><i class="fas fa-stopwatch"></i></div>
        <div class="stat-info">
            <h3>{{ $stats['belum_diproses'] }}</h3>
            <p>Pengajuan Belum Diproses</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon process"><i class="fas fa-sync-alt"></i></div>
        <div class="stat-info">
            <h3>{{ $stats['diproses'] }}</h3>
            <p>Pengajuan Diproses</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon accepted"><i class="fas fa-check-circle"></i></div>
        <div class="stat-info">
            <h3>{{ $stats['diterima'] }}</h3>
            <p>Pengajuan Diterima</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-icon rejected"><i class="fas fa-times-circle"></i></div>
        <div class="stat-info">
            <h3>{{ $stats['ditolak'] }}</h3>
            <p>Pengajuan Ditolak</p>
        </div>
    </div>
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
                        <th>Dana Diajukan</th>
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
