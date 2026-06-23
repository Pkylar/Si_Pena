<?php

namespace App\Http\Controllers;

use App\Models\FundRequest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = FundRequest::query();

        if (in_array($user->role, ['mahasiswa', 'ormawa'])) {
            $query->where('user_id', $user->id);
        }

        $stats = [
            'belum_diproses' => (clone $query)->where('status', 'Belum Diproses')->count(),
            'diproses' => (clone $query)->whereIn('status', [
                'Sedang Diproses Kemahasiswaan', 'Diteruskan ke Kaur Kemahasiswaan',
                'Diteruskan ke Keuangan', 'Sedang Diproses Keuangan',
                'Diteruskan ke Kaur Keuangan', 'Menunggu Persetujuan WD2'
            ])->count(),
            'diterima' => (clone $query)->whereIn('status', ['Disetujui', 'Selesai'])->count(),
            'ditolak' => (clone $query)->where('status', 'Ditolak')->count(),
            'total_dana_acc' => (clone $query)->whereIn('status', ['Disetujui', 'Selesai'])->sum('dana_disetujui_keuangan'),
        ];

        $pengajuan = $query->with('user')->latest()->get();

        return view('dashboard', compact('stats', 'pengajuan'));
    }
}
