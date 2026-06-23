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
            ->whereNotNull('dana_disetujui_keuangan')
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
        $totalDisetujui = $pengajuan->sum('dana_disetujui_keuangan');

        $summaryPerTriwulan = [];
        for ($t = 1; $t <= 4; $t++) {
            $months = match ($t) {
                1 => [1, 2, 3],
                2 => [4, 5, 6],
                3 => [7, 8, 9],
                4 => [10, 11, 12],
            };
            $items = FundRequest::whereIn('status', ['Disetujui', 'Selesai'])
                ->whereNotNull('dana_disetujui_keuangan')
                ->whereRaw('MONTH(tanggal_mulai) IN (' . implode(',', $months) . ')')
                ->get();
            $summaryPerTriwulan[$t] = [
                'jumlah' => $items->count(),
                'total_diajukan' => $items->sum('dana_diajukan'),
                'total_disetujui' => $items->sum('dana_disetujui_keuangan'),
            ];
        }

        return view('report.index', compact('pengajuan', 'triwulan', 'totalDiajukan', 'totalDisetujui', 'summaryPerTriwulan'));
    }

    public function export(Request $request)
    {
        $triwulan = $request->get('triwulan', 'all');

        $query = FundRequest::whereIn('status', ['Disetujui', 'Selesai'])
            ->whereNotNull('dana_disetujui_keuangan')
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
        $filename = 'report_pengajuan_dana' . ($triwulan !== 'all' ? '_triwulan_' . $triwulan : '_semua') . '.xls';

        $html = '<table border="1" cellpadding="5" cellspacing="0">';
        $html .= '<tr style="background:#1a3c34;color:#fff;font-weight:bold;">';
        $html .= '<th>No</th><th>Pengaju</th><th>Nama Kegiatan</th><th>Jenis</th><th>Tanggal Mulai</th><th>Dana Diajukan</th><th>Dana yang Disetujui</th><th>Status</th>';
        $html .= '</tr>';

        foreach ($pengajuan as $i => $p) {
            $html .= '<tr>';
            $html .= '<td>' . ($i + 1) . '</td>';
            $html .= '<td>' . ($p->user->organization_name ?? $p->user->name) . '</td>';
            $html .= '<td>' . $p->nama_kegiatan . '</td>';
            $html .= '<td>' . $p->jenis_kegiatan . '</td>';
            $html .= '<td>' . $p->tanggal_mulai . '</td>';
            $html .= '<td>Rp ' . number_format($p->dana_diajukan, 0, ',', '.') . '</td>';
            $html .= '<td>' . ($p->dana_disetujui_keuangan ? 'Rp ' . number_format($p->dana_disetujui_keuangan, 0, ',', '.') : '-') . '</td>';
            $html .= '<td>' . $p->status . '</td>';
            $html .= '</tr>';
        }

        $html .= '<tr style="font-weight:bold;background:#f0f0f0;">';
        $html .= '<td colspan="5">TOTAL</td>';
        $html .= '<td>Rp ' . number_format($pengajuan->sum('dana_diajukan'), 0, ',', '.') . '</td>';
        $html .= '<td>Rp ' . number_format($pengajuan->sum('dana_disetujui_keuangan'), 0, ',', '.') . '</td>';
        $html .= '<td></td>';
        $html .= '</tr>';
        $html .= '</table>';

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
