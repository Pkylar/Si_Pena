<?php

namespace Database\Seeders;

use App\Models\FundBudget;
use App\Models\FundRequest;
use App\Models\Revision;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $keuangan = User::create([
            'name' => 'Keuangan FRI',
            'username' => 'keuanganFRI',
            'password' => 'keuangan123',
            'role' => 'keuangan',
        ]);

        $kemahasiswaan = User::create([
            'name' => 'Kemahasiswaan FRI',
            'username' => 'kemahasiswaanFRI',
            'password' => 'kemahasiswaan123',
            'role' => 'kemahasiswaan',
        ]);

        $wd2 = User::create([
            'name' => 'Wakil Dekan 2',
            'username' => 'wd2FRI',
            'password' => 'wd2123',
            'role' => 'wd2',
        ]);

        $hmti = User::create([
            'name' => 'HMTI',
            'username' => 'HMTI',
            'password' => 'hmti123',
            'role' => 'ormawa',
            'organization_name' => 'HMTI',
        ]);

        $hmsi = User::create([
            'name' => 'HMSI',
            'username' => 'HMSI',
            'password' => 'hmsi123',
            'role' => 'ormawa',
            'organization_name' => 'HMSI',
        ]);

        FundRequest::create([
            'user_id' => $hmti->id,
            'tahun_ajaran' => '2025 Genap',
            'tanggal_mulai' => '2025-03-01',
            'tanggal_selesai' => '2025-03-05',
            'jenis_kegiatan' => 'Organisasi Kemahasiswaan',
            'tingkat_kegiatan' => 'Fakultas',
            'nama_kegiatan' => 'Seminar Teknologi Informasi',
            'deskripsi' => 'Seminar nasional tentang perkembangan teknologi informasi terkini',
            'proposal_file' => 'proposals/sample.pdf',
            'dana_diajukan' => 15000000,
            'status' => 'Belum Diproses',
        ]);

        FundRequest::create([
            'user_id' => $hmsi->id,
            'tahun_ajaran' => '2025 Genap',
            'tanggal_mulai' => '2025-04-10',
            'tanggal_selesai' => '2025-04-12',
            'jenis_kegiatan' => 'Organisasi Kemahasiswaan',
            'tingkat_kegiatan' => 'Universitas',
            'nama_kegiatan' => 'Workshop Data Science',
            'deskripsi' => 'Workshop pelatihan data science untuk mahasiswa',
            'proposal_file' => 'proposals/sample2.pdf',
            'dana_diajukan' => 10000000,
            'dana_disetujui' => 8000000,
            'status' => 'Disetujui',
        ]);

        FundRequest::create([
            'user_id' => $hmti->id,
            'tahun_ajaran' => '2025 Ganjil',
            'tanggal_mulai' => '2025-09-15',
            'tanggal_selesai' => '2025-09-17',
            'jenis_kegiatan' => 'Lomba',
            'tingkat_kegiatan' => 'Nasional',
            'nama_kegiatan' => 'Hackathon Nasional 2025',
            'deskripsi' => 'Kompetisi hackathon tingkat nasional',
            'proposal_file' => 'proposals/sample3.pdf',
            'dana_diajukan' => 20000000,
            'status' => 'Sedang Diproses Kemahasiswaan',
        ]);

        $ormawaUnits = ['HMTI', 'HMSI', 'HMTL', 'SIECA', 'MTO'];
        foreach ($ormawaUnits as $unit) {
            for ($t = 1; $t <= 4; $t++) {
                $total = 15000000;
                $used = rand(3000000, 12000000);
                FundBudget::create([
                    'kategori' => 'ormawa',
                    'nama_unit' => $unit,
                    'triwulan' => $t,
                    'total_dana' => $total,
                    'sisa_dana' => $total - $used,
                ]);
            }
        }

        $lombaUnits = ['TI', 'SI', 'TL', 'MR'];
        foreach ($lombaUnits as $unit) {
            for ($t = 1; $t <= 4; $t++) {
                $total = 10000000;
                $used = rand(2000000, 8000000);
                FundBudget::create([
                    'kategori' => 'lomba',
                    'nama_unit' => $unit,
                    'triwulan' => $t,
                    'total_dana' => $total,
                    'sisa_dana' => $total - $used,
                ]);
            }
        }
    }
}
