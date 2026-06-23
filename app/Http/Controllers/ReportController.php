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

    public function export(Request $request)
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
        $filename = 'report_pengajuan_dana' . ($triwulan !== 'all' ? '_triwulan_' . $triwulan : '_semua') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($pengajuan) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['No', 'Pengaju', 'Nama Kegiatan', 'Jenis', 'Tanggal Mulai', 'Dana Diajukan', 'Dana Disetujui Kaur Keuangan', 'Dana Disetujui WD2', 'Status']);

            foreach ($pengajuan as $i => $p) {
                fputcsv($file, [
                    $i + 1,
                    $p->user->organization_name ?? $p->user->name,
                    $p->nama_kegiatan,
                    $p->jenis_kegiatan,
                    $p->tanggal_mulai,
                    $p->dana_diajukan,
                    $p->dana_disetujui_keuangan ?? '-',
                    $p->dana_disetujui,
                    $p->status,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
