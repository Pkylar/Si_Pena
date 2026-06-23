<?php

namespace App\Http\Controllers;

use App\Models\FundBudget;
use App\Models\FundRequest;
use Illuminate\Http\Request;

class GrafikController extends Controller
{
    public function ormawa()
    {
        $budgets = FundBudget::where('kategori', 'ormawa')->get();
        $units = $budgets->pluck('nama_unit')->unique()->values();

        $approvedRequests = FundRequest::where('jenis_kegiatan', 'Organisasi Kemahasiswaan')
            ->whereIn('status', ['Disetujui', 'Selesai'])
            ->whereNotNull('dana_disetujui_keuangan')
            ->whereHas('user', fn($q) => $q->whereNotNull('organization_name')->where('organization_name', '!=', ''))
            ->with('user')
            ->get();

        $chartData = [];
        foreach ($units as $unit) {
            $chartData[$unit] = [];
            for ($t = 1; $t <= 4; $t++) {
                $chartData[$unit][$t] = $approvedRequests
                    ->filter(fn($r) => $r->user->organization_name === $unit && $this->getTriwulan($r->tanggal_mulai) === $t)
                    ->sum('dana_disetujui_keuangan');
            }
        }

        $totalDana = $budgets->where('triwulan', 4)->sum('total_dana');
        $sisaDana = $totalDana - $approvedRequests->sum('dana_disetujui_keuangan');

        return view('grafik.ormawa', compact('chartData', 'units', 'totalDana', 'sisaDana', 'budgets'));
    }

    public function lomba()
    {
        $budgets = FundBudget::where('kategori', 'lomba')->get();
        $units = $budgets->pluck('nama_unit')->unique()->values();

        $approvedRequests = FundRequest::where('jenis_kegiatan', 'Lomba')
            ->whereIn('status', ['Disetujui', 'Selesai'])
            ->whereNotNull('dana_disetujui_keuangan')
            ->with('user')
            ->get();

        $orgToUnit = [
            'HMTI' => 'TI',
            'HMSI' => 'SI',
            'HMTL' => 'TL',
            'MTO' => 'MR',
        ];

        $chartData = [];
        foreach ($units as $unit) {
            $chartData[$unit] = [];
            for ($t = 1; $t <= 4; $t++) {
                $chartData[$unit][$t] = $approvedRequests
                    ->filter(fn($r) => ($orgToUnit[$r->user->organization_name] ?? '') === $unit && $this->getTriwulan($r->tanggal_mulai) === $t)
                    ->sum('dana_disetujui_keuangan');
            }
        }

        $budgetsByUnit = [];
        foreach ($units as $unit) {
            $b = $budgets->where('nama_unit', $unit)->where('triwulan', 4)->first();
            $totalDisetujuiUnit = $approvedRequests
                ->filter(fn($r) => ($orgToUnit[$r->user->organization_name] ?? '') === $unit)
                ->sum('dana_disetujui_keuangan');
            $budgetsByUnit[$unit] = [
                'total' => $b ? $b->total_dana : 0,
                'sisa' => $b ? $b->total_dana - $totalDisetujuiUnit : 0,
            ];
        }

        return view('grafik.lomba', compact('chartData', 'units', 'budgetsByUnit', 'budgets'));
    }

    public function updateBudget(Request $request, $id)
    {
        $request->validate(['total_dana' => 'required|numeric|min:0']);
        $budget = FundBudget::findOrFail($id);
        $diff = $request->total_dana - $budget->total_dana;
        $budget->update([
            'total_dana' => $request->total_dana,
            'sisa_dana' => $budget->sisa_dana + $diff,
        ]);

        return back()->with('success', 'Dana berhasil diperbarui.');
    }

    private function getTriwulan($tanggal): int
    {
        $month = (int) date('m', strtotime($tanggal));
        return match (true) {
            $month <= 3 => 1,
            $month <= 6 => 2,
            $month <= 9 => 3,
            default => 4,
        };
    }
}
