<?php

namespace App\Http\Controllers;

use App\Models\FundRequest;
use App\Models\Revision;
use Illuminate\Http\Request;

class FundRequestController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (in_array($user->role, ['mahasiswa', 'ormawa'])) {
            $pengajuan = FundRequest::where('user_id', $user->id)->with('user')->latest()->get();
        } else {
            $pengajuan = FundRequest::with('user')->latest()->get();
        }

        return view('pengajuan.index', compact('pengajuan'));
    }

    public function create()
    {
        return view('pengajuan.create');
    }

    public function store(Request $request)
    {
        $request->merge(['dana_diajukan' => str_replace('.', '', $request->dana_diajukan)]);

        $request->validate([
            'tahun_ajaran' => 'required|string',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'jenis_kegiatan' => 'required|string',
            'tingkat_kegiatan' => 'required|string',
            'nama_kegiatan' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'proposal_file' => 'required|file|mimes:pdf|max:10240',
            'dana_diajukan' => 'required|numeric|min:0',
        ]);

        $file = $request->file('proposal_file');
        $path = $file->store('proposals', 'public');

        FundRequest::create([
            'user_id' => auth()->id(),
            'tahun_ajaran' => $request->tahun_ajaran,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'jenis_kegiatan' => $request->jenis_kegiatan,
            'tingkat_kegiatan' => $request->tingkat_kegiatan,
            'nama_kegiatan' => $request->nama_kegiatan,
            'deskripsi' => $request->deskripsi,
            'proposal_file' => $path,
            'dana_diajukan' => $request->dana_diajukan,
            'status' => 'Belum Diproses',
        ]);

        return redirect('/pengajuan')->with('success', 'Pengajuan berhasil diajukan.');
    }

    public function show($id)
    {
        $pengajuan = FundRequest::with(['user', 'revisions.user'])->findOrFail($id);
        $user = auth()->user();

        if (in_array($user->role, ['mahasiswa', 'ormawa']) && $pengajuan->user_id !== $user->id) {
            abort(403);
        }

        return view('pengajuan.show', compact('pengajuan'));
    }

    public function addRevision(Request $request, $id)
    {
        $request->validate(['catatan' => 'required|string']);
        $pengajuan = FundRequest::findOrFail($id);

        Revision::create([
            'fund_request_id' => $pengajuan->id,
            'user_id' => auth()->id(),
            'catatan' => $request->catatan,
        ]);

        return back()->with('success', 'Revisi berhasil ditambahkan.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate(['status' => 'required|string']);
        $pengajuan = FundRequest::findOrFail($id);
        $user = auth()->user();

        if (!in_array($user->role, ['kemahasiswaan', 'keuangan', 'wd2'])) {
            abort(403, 'Anda tidak memiliki akses untuk mengubah status.');
        }

        $allowedStatuses = $this->getAllowedStatuses($user->role);
        if (!in_array($request->status, $allowedStatuses)) {
            abort(403, 'Status tidak diizinkan.');
        }

        // Kemahasiswaan harus isi dana disetujui sebelum meneruskan ke keuangan
        if ($user->role === 'kemahasiswaan' && $request->status === 'Diteruskan ke Keuangan') {
            if (!$pengajuan->dana_disetujui_kemahasiswaan) {
                return back()->withErrors(['Dana disetujui kemahasiswaan harus diisi sebelum meneruskan ke keuangan.']);
            }
        }

        // Keuangan harus isi dana disetujui sebelum meneruskan ke WD2
        if ($user->role === 'keuangan' && $request->status === 'Menunggu Persetujuan WD2') {
            if (!$pengajuan->dana_disetujui_keuangan) {
                return back()->withErrors(['Dana disetujui keuangan harus diisi sebelum meneruskan ke WD2.']);
            }
        }

        if ($user->role === 'wd2' && $pengajuan->status !== 'Menunggu Persetujuan WD2') {
            abort(403, 'WD2 hanya dapat mengubah status jika pengajuan sedang menunggu persetujuan.');
        }

        $pengajuan->update(['status' => $request->status]);

        return back()->with('success', 'Status berhasil diperbarui.');
    }

    public function updateApprovedFund(Request $request, $id)
    {
        $request->merge(['dana_disetujui' => str_replace('.', '', $request->dana_disetujui)]);
        $request->validate(['dana_disetujui' => 'required|numeric|min:0']);
        $pengajuan = FundRequest::findOrFail($id);
        $pengajuan->update(['dana_disetujui' => $request->dana_disetujui]);

        return back()->with('success', 'Dana disetujui berhasil diperbarui.');
    }

    public function updateApprovedFundKemahasiswaan(Request $request, $id)
    {
        $request->merge(['dana_disetujui_kemahasiswaan' => str_replace('.', '', $request->dana_disetujui_kemahasiswaan)]);
        $request->validate(['dana_disetujui_kemahasiswaan' => 'required|numeric|min:0']);
        $pengajuan = FundRequest::findOrFail($id);
        $pengajuan->update(['dana_disetujui_kemahasiswaan' => $request->dana_disetujui_kemahasiswaan]);

        return back()->with('success', 'Dana disetujui kemahasiswaan berhasil disimpan.');
    }

    public function updateApprovedFundKeuangan(Request $request, $id)
    {
        $request->merge(['dana_disetujui_keuangan' => str_replace('.', '', $request->dana_disetujui_keuangan)]);
        $request->validate(['dana_disetujui_keuangan' => 'required|numeric|min:0']);
        $pengajuan = FundRequest::findOrFail($id);
        $pengajuan->update(['dana_disetujui_keuangan' => $request->dana_disetujui_keuangan]);

        return back()->with('success', 'Dana disetujui keuangan berhasil disimpan.');
    }

    private function getAllowedStatuses($role)
    {
        return match ($role) {
            'kemahasiswaan' => ['Sedang Diproses Kemahasiswaan', 'Revisi', 'Diteruskan ke Keuangan', 'Ditolak'],
            'keuangan' => ['Sedang Diproses Keuangan', 'Revisi', 'Menunggu Persetujuan WD2', 'Ditolak'],
            'wd2' => ['Disetujui', 'Ditolak', 'Selesai'],
            default => [],
        };
    }
}
