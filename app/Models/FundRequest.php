<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FundRequest extends Model
{
    protected $fillable = [
        'user_id', 'tahun_ajaran', 'tanggal_mulai', 'tanggal_selesai',
        'jenis_kegiatan', 'tingkat_kegiatan', 'nama_kegiatan', 'deskripsi',
        'proposal_file', 'dana_diajukan', 'dana_disetujui_kemahasiswaan',
        'dana_disetujui_keuangan', 'dana_disetujui', 'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function revisions()
    {
        return $this->hasMany(Revision::class);
    }

    public function approvals()
    {
        return $this->hasMany(Approval::class);
    }
}
