@extends('layouts.app')
@section('title', 'Detail Pengajuan')

@section('content')
<div class="page-header">
    <h1>Detail Pengajuan Dana</h1>
</div>

<div class="card">
    <div class="card-body">
        <div class="detail-section">
            <h4><i class="fas fa-calendar-alt"></i> Tanggal Kegiatan</h4>
            <div class="detail-row"><span class="label">Tahun Ajaran</span><span class="value">{{ $pengajuan->tahun_ajaran }}</span></div>
            <div class="detail-row"><span class="label">Tanggal Mulai</span><span class="value">{{ \Carbon\Carbon::parse($pengajuan->tanggal_mulai)->format('d/m/Y') }}</span></div>
            <div class="detail-row"><span class="label">Tanggal Selesai</span><span class="value">{{ \Carbon\Carbon::parse($pengajuan->tanggal_selesai)->format('d/m/Y') }}</span></div>
        </div>

        <div class="detail-section">
            <h4><i class="fas fa-info-circle"></i> Informasi Kegiatan</h4>
            <div class="detail-row"><span class="label">Jenis Kegiatan</span><span class="value">{{ $pengajuan->jenis_kegiatan }}</span></div>
            <div class="detail-row"><span class="label">Tingkat Kegiatan</span><span class="value">{{ $pengajuan->tingkat_kegiatan }}</span></div>
            <div class="detail-row"><span class="label">Nama Kegiatan</span><span class="value">{{ $pengajuan->nama_kegiatan }}</span></div>
            <div class="detail-row"><span class="label">Deskripsi</span><span class="value">{{ $pengajuan->deskripsi }}</span></div>
        </div>

        <div class="detail-section">
            <h4><i class="fas fa-file-pdf"></i> Proposal Kegiatan</h4>
            <div class="detail-row">
                <span class="label">File Proposal</span>
                <span class="value">
                    <a href="{{ asset('storage/' . $pengajuan->proposal_file) }}" target="_blank" style="color:#5ba4cf;">
                        <i class="fas fa-file-pdf" style="color:#e57373;font-size:18px;"></i> Lihat Proposal
                    </a>
                </span>
            </div>
        </div>

        <div class="detail-section">
            <h4><i class="fas fa-edit"></i> Revisi</h4>
            @if($pengajuan->revisions->isEmpty())
                <p style="padding:10px 20px;color:#888;font-size:13px;">Belum ada revisi.</p>
            @else
                <div style="padding:10px 20px;">
                    @foreach($pengajuan->revisions as $rev)
                        <div class="revision-item">
                            <div class="revision-meta">{{ $rev->user->name }} ({{ ucfirst(str_replace('_', ' ', $rev->user->role)) }}) - {{ $rev->created_at->format('d/m/Y H:i') }}</div>
                            <div class="revision-text">{{ $rev->catatan }}</div>
                        </div>
                    @endforeach
                </div>
            @endif

            @if(in_array(auth()->user()->role, ['kemahasiswaan', 'keuangan', 'kaur_kemahasiswaan', 'kaur_keuangan']))
                <form method="POST" action="/pengajuan/{{ $pengajuan->id }}/revision" style="padding:10px 20px;">
                    @csrf
                    <div class="form-group">
                        <label>Tambah Revisi/Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3" required placeholder="Tulis catatan revisi..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm">Kirim Revisi</button>
                </form>
            @endif

            @if(in_array(auth()->user()->role, ['mahasiswa', 'ormawa']) && $pengajuan->status === 'Revisi')
                <form method="POST" action="/pengajuan/{{ $pengajuan->id }}/resubmit" enctype="multipart/form-data" style="padding:10px 20px;background:#fff8e1;border-radius:6px;margin-top:10px;">
                    @csrf
                    <p style="font-size:13px;color:#856404;margin-bottom:12px;"><i class="fas fa-exclamation-triangle"></i> Pengajuan Anda perlu direvisi. Silakan upload ulang proposal yang sudah diperbaiki.</p>
                    <div class="form-group">
                        <label>Upload Ulang Proposal (PDF) <span class="required">*</span></label>
                        <input type="file" name="proposal_file" class="form-control" accept=".pdf" required>
                        <small class="field-hint">Format: PDF, maksimal 10MB</small>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">Submit Ulang & Selesai Direvisi</button>
                </form>
            @endif
        </div>

        <div class="detail-section">
            <h4><i class="fas fa-money-bill-wave"></i> Dana</h4>
            <div class="detail-row"><span class="label">Dana yang Diajukan</span><span class="value">Rp. {{ number_format($pengajuan->dana_diajukan, 2, ',', '.') }}</span></div>
            <div class="detail-row">
                <span class="label">Dana yang Disetujui</span>
                <span class="value">{{ $pengajuan->dana_disetujui_keuangan ? 'Rp. ' . number_format($pengajuan->dana_disetujui_keuangan, 2, ',', '.') : '-' }}</span>
            </div>


            @if(auth()->user()->role === 'kaur_keuangan')
                <form method="POST" action="/pengajuan/{{ $pengajuan->id }}/approved-fund-keuangan" style="padding:10px 20px;">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <label>Dana Disetujui Kaur Keuangan (Rp)</label>
                        <input type="text" name="dana_disetujui_keuangan" class="form-control input-rupiah" value="{{ $pengajuan->dana_disetujui_keuangan ? number_format($pengajuan->dana_disetujui_keuangan, 0, ',', '.') : '' }}" required>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">Simpan Dana</button>
                </form>
            @endif


        </div>

        <div class="detail-section">
            <h4><i class="fas fa-flag"></i> Status</h4>
            <div class="detail-row">
                <span class="label">Status Saat Ini</span>
                <span class="value">
                    @php
                        $badgeClass = match(true) {
                            str_contains($pengajuan->status, 'Ditolak') => 'badge-danger',
                            str_contains($pengajuan->status, 'Disetujui') || str_contains($pengajuan->status, 'Selesai') => 'badge-success',
                            str_contains($pengajuan->status, 'Belum') => 'badge-warning',
                            default => 'badge-info',
                        };
                    @endphp
                    <span class="badge {{ $badgeClass }}">{{ $pengajuan->status }}</span>
                </span>
            </div>

            @php
                $role = auth()->user()->role;
                $canChangeStatus = match($role) {
                    'kemahasiswaan' => in_array($pengajuan->status, ['Belum Diproses', 'Sedang Diproses Kemahasiswaan']),
                    'kaur_kemahasiswaan' => in_array($pengajuan->status, ['Diteruskan ke Kaur Kemahasiswaan']),
                    'keuangan' => in_array($pengajuan->status, ['Diteruskan ke Keuangan', 'Sedang Diproses Keuangan']),
                    'kaur_keuangan' => in_array($pengajuan->status, ['Diteruskan ke Kaur Keuangan']),
                    'wd2' => $pengajuan->status === 'Menunggu Persetujuan WD2',
                    default => false,
                };
            @endphp

            @if($canChangeStatus)
                <form method="POST" action="/pengajuan/{{ $pengajuan->id }}/status" style="padding:10px 20px;">
                    @csrf
                    @method('PATCH')
                    <div class="form-group">
                        <label>Ubah Status</label>
                        <select name="status" class="form-control" required>
                            <option value="">Pilih Status</option>
                            @if($role === 'kemahasiswaan')
                                <option value="Sedang Diproses Kemahasiswaan">Sedang Diproses Kemahasiswaan</option>
                                <option value="Diteruskan ke Kaur Kemahasiswaan">Diteruskan ke Kaur Kemahasiswaan</option>
                                <option value="Revisi">Revisi</option>
                                <option value="Ditolak">Ditolak</option>
                            @elseif($role === 'kaur_kemahasiswaan')
                                <option value="Diteruskan ke Keuangan">Diteruskan ke Keuangan</option>
                                <option value="Revisi">Revisi</option>
                                <option value="Ditolak">Ditolak</option>
                            @elseif($role === 'keuangan')
                                <option value="Sedang Diproses Keuangan">Sedang Diproses Keuangan</option>
                                <option value="Diteruskan ke Kaur Keuangan">Diteruskan ke Kaur Keuangan</option>
                                <option value="Revisi">Revisi</option>
                                <option value="Ditolak">Ditolak</option>
                            @elseif($role === 'kaur_keuangan')
                                <option value="Menunggu Persetujuan WD2">Menunggu Persetujuan WD2</option>
                                <option value="Revisi">Revisi</option>
                                <option value="Ditolak">Ditolak</option>
                            @elseif($role === 'wd2')
                                <option value="Disetujui">Disetujui</option>
                                <option value="Ditolak">Ditolak</option>
                            @endif
                        </select>
                    </div>
                    <button type="submit" class="btn btn-success btn-sm">Simpan Status</button>
                </form>
            @endif
        </div>

        <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;padding-top:20px;border-top:1px solid #eee;">
            <a href="/pengajuan" class="btn btn-danger">Batal</a>
        </div>
    </div>
</div>
@endsection
