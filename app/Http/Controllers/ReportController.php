<?php

namespace App\Http\Controllers;

use App\Models\FundRequest;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $triwulan = $request->get('triwulan', 'all');

        $query = FundRequest::whereIn('status', ['Disetujui', 'Selesai'])
            ->whereNotNull('dana_disetujui')
            ->with('user');

        if ($triwulan !== 'all') {
            $months = match ((int) $triwulan) {
                1 => [1, 2, 3],
                2 => [4, 5, 6],
                3 => [7, 8, 9],
                4 => [10, 11, 12],
            };
            $query->whereRaw('MONTH(tanggal_mulai) IN (' . implode(',', $months) . ')');
        }

        $pengajuan = $query->latest()->get();

        $totalDiajukan = $pengajuan->sum('dana_diajukan');
        $totalDisetujui = $pengajuan->sum('dana_disetujui');

        // Summary per triwulan
        $summaryPerTriwulan = [];
        for ($t = 1; $t <= 4; $t++) {
            $months = match ($t) {
                1 => [1, 2, 3],
                2 => [4, 5, 6],
                3 => [7, 8, 9],
                4 => [10, 11, 12],
            };
            $items = FundRequest::whereIn('status', ['Disetujui', 'Selesai'])
                ->whereNotNull('dana_disetujui')
                ->whereRaw('MONTH(tanggal_mulai) IN (' . implode(',', $months) . ')')
                ->get();
            $summaryPerTriwulan[$t] = [
                'jumlah' => $items->count(),
                'total_diajukan' => $items->sum('dana_diajukan'),
                'total_disetujui' => $items->sum('dana_disetujui'),
            ];
        }

        return view('report.index', compact('pengajuan', 'triwulan', 'totalDiajukan', 'totalDisetujui', 'summaryPerTriwulan'));
    }
}
