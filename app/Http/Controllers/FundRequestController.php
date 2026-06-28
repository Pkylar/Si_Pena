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

        $allowedRoles = ['kemahasiswaan', 'keuangan', 'kaur_kemahasiswaan', 'kaur_keuangan', 'wd2'];
        if (!in_array($user->role, $allowedRoles)) {
            abort(403);
        }

        $allowedStatuses = $this->getAllowedStatuses($user->role);
        if (!in_array($request->status, $allowedStatuses)) {
            abort(403, 'Status tidak diizinkan.');
        }

        // Validasi: role hanya bisa ubah status jika pengajuan sudah di tahap mereka
        $requiredCurrentStatus = match ($user->role) {
            'kemahasiswaan' => ['Belum Diproses', 'Sedang Diproses Kemahasiswaan', 'Selesai Direvisi'],
            'kaur_kemahasiswaan' => ['Diteruskan ke Kaur Kemahasiswaan'],
            'keuangan' => ['Diteruskan ke Keuangan', 'Sedang Diproses Keuangan'],
            'kaur_keuangan' => ['Diteruskan ke Kaur Keuangan'],
            'wd2' => ['Menunggu Persetujuan WD2'],
            default => [],
        };

        if (!in_array($pengajuan->status, $requiredCurrentStatus)) {
            return back()->withErrors(['Pengajuan belum berada di tahap Anda.']);
        }

        // Kaur Keuangan harus isi dana sebelum meneruskan ke WD2
        if ($user->role === 'kaur_keuangan' && $request->status === 'Menunggu Persetujuan WD2') {
            if (!$pengajuan->dana_disetujui_keuangan) {
                return back()->withErrors(['Dana disetujui kaur keuangan harus diisi sebelum meneruskan ke WD2.']);
            }
        }

        $pengajuan->update(['status' => $request->status]);

        // Ketika WD2 approve, set dana_disetujui = dana_disetujui_keuangan
        if ($user->role === 'wd2' && $request->status === 'Disetujui') {
            $pengajuan->update(['dana_disetujui' => $pengajuan->dana_disetujui_keuangan]);
        }

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

    public function resubmit(Request $request, $id)
    {
        $pengajuan = FundRequest::findOrFail($id);
        $user = auth()->user();

        if (!in_array($user->role, ['mahasiswa', 'ormawa']) || $pengajuan->user_id !== $user->id) {
            abort(403);
        }

        if ($pengajuan->status !== 'Revisi') {
            return back()->withErrors(['Pengajuan tidak dalam status revisi.']);
        }

        $request->validate([
            'proposal_file' => 'required|file|mimes:pdf|max:10240',
        ]);

        $file = $request->file('proposal_file');
        $path = $file->store('proposals', 'public');

        $pengajuan->update([
            'proposal_file' => $path,
            'status' => 'Selesai Direvisi',
        ]);

        return back()->with('success', 'Proposal berhasil disubmit ulang. Status diubah menjadi Selesai Direvisi.');
    }

    public function updateApprovedFundKeuangan(Request $request, $id)
    {
        $request->merge(['dana_disetujui_keuangan' => str_replace('.', '', $request->dana_disetujui_keuangan)]);
        $request->validate(['dana_disetujui_keuangan' => 'required|numeric|min:0']);
        $pengajuan = FundRequest::findOrFail($id);
        $pengajuan->update(['dana_disetujui_keuangan' => $request->dana_disetujui_keuangan]);

        return back()->with('success', 'Dana disetujui kaur keuangan berhasil disimpan.');
    }

    private function getAllowedStatuses($role)
    {
        return match ($role) {
            'kemahasiswaan' => ['Sedang Diproses Kemahasiswaan', 'Diteruskan ke Kaur Kemahasiswaan', 'Revisi', 'Ditolak'],
            'kaur_kemahasiswaan' => ['Diteruskan ke Keuangan', 'Revisi', 'Ditolak'],
            'keuangan' => ['Sedang Diproses Keuangan', 'Diteruskan ke Kaur Keuangan', 'Revisi', 'Ditolak'],
            'kaur_keuangan' => ['Menunggu Persetujuan WD2', 'Revisi', 'Ditolak'],
            'wd2' => ['Disetujui', 'Ditolak'],
            default => [],
        };
    }
}
