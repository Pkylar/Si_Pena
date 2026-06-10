@extends('layouts.app')
@section('title', 'Ajukan Dana')

@section('content')
<div class="page-header">
    <h1>Ajukan Dana</h1>
    <nav style="font-size:13px;color:#888;margin-top:4px;">
        <a href="/dashboard" style="color:#5ba4cf;text-decoration:none;">Dashboard</a> /
        <a href="/pengajuan" style="color:#5ba4cf;text-decoration:none;">Pengajuan</a> /
        <span>Ajukan Dana</span>
    </nav>
</div>

<div class="card">
    <div class="card-header"><span>Form Pengajuan Dana</span></div>
    <div class="card-body">
        <form method="POST" action="/pengajuan" enctype="multipart/form-data">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                <div class="form-group">
                    <label>Tahun Ajaran <span class="required">*</span></label>
                    <select name="tahun_ajaran" class="form-control" required>
                        <option value="">Pilih Tahun Ajaran</option>
                        <option value="2025 Ganjil">2025 Ganjil</option>
                        <option value="2025 Genap">2025 Genap</option>
                        <option value="2026 Ganjil">2026 Ganjil</option>
                        <option value="2026 Genap">2026 Genap</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Jenis Kegiatan <span class="required">*</span></label>
                    <select name="jenis_kegiatan" class="form-control" required>
                        <option value="">Pilih Jenis</option>
                        <option value="Organisasi Kemahasiswaan">Organisasi Kemahasiswaan</option>
                        <option value="Lomba">Lomba</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Tanggal Mulai <span class="required">*</span></label>
                    <input type="date" name="tanggal_mulai" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tanggal Selesai <span class="required">*</span></label>
                    <input type="date" name="tanggal_selesai" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Tingkat Kegiatan <span class="required">*</span></label>
                    <select name="tingkat_kegiatan" class="form-control" required>
                        <option value="">Pilih Tingkat</option>
                        <option value="Fakultas">Fakultas</option>
                        <option value="Universitas">Universitas</option>
                        <option value="Regional">Regional</option>
                        <option value="Nasional">Nasional</option>
                        <option value="Internasional">Internasional</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Dana yang Diajukan (Rp) <span class="required">*</span></label>
                    <input type="text" name="dana_diajukan" class="form-control input-rupiah" placeholder="Contoh: 15.000.000" min="0" required>
                </div>
            </div>
            <div class="form-group">
                <label>Nama Kegiatan <span class="required">*</span></label>
                <input type="text" name="nama_kegiatan" class="form-control" placeholder="Masukkan nama kegiatan" required>
            </div>
            <div class="form-group">
                <label>Deskripsi Singkat Kegiatan <span class="required">*</span></label>
                <textarea name="deskripsi" class="form-control" placeholder="Jelaskan secara singkat tujuan dan rincian kegiatan" required></textarea>
            </div>
            <div class="form-group">
                <label>Proposal Kegiatan (PDF) <span class="required">*</span></label>
                <input type="file" name="proposal_file" class="form-control" accept=".pdf" required>
                <small class="field-hint">Format: PDF, maksimal 10MB</small>
            </div>
            <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
                <a href="/pengajuan" class="btn btn-danger">Batal</a>
                <button type="submit" class="btn btn-primary">Ajukan</button>
            </div>
        </form>
    </div>
</div>
@endsection
