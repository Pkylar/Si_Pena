<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FundRequestController;
use App\Http\Controllers\GrafikController;

Route::get('/', fn() => redirect('/login'));

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/pengajuan', [FundRequestController::class, 'index'])->name('pengajuan.index');
    Route::get('/pengajuan/create', [FundRequestController::class, 'create'])
        ->middleware('role:mahasiswa,ormawa')->name('pengajuan.create');
    Route::post('/pengajuan', [FundRequestController::class, 'store'])
        ->middleware('role:mahasiswa,ormawa')->name('pengajuan.store');
    Route::get('/pengajuan/{id}', [FundRequestController::class, 'show'])->name('pengajuan.show');
    Route::post('/pengajuan/{id}/revision', [FundRequestController::class, 'addRevision'])
        ->middleware('role:kemahasiswaan,keuangan,kaur_kemahasiswaan,kaur_keuangan')->name('pengajuan.revision');
    Route::patch('/pengajuan/{id}/status', [FundRequestController::class, 'updateStatus'])
        ->middleware('role:kemahasiswaan,keuangan,kaur_kemahasiswaan,kaur_keuangan,wd2')->name('pengajuan.status');
    Route::patch('/pengajuan/{id}/approved-fund-keuangan', [FundRequestController::class, 'updateApprovedFundKeuangan'])
        ->middleware('role:kaur_keuangan')->name('pengajuan.approved-fund-keuangan');

    Route::get('/grafik/ormawa', [GrafikController::class, 'ormawa'])->name('grafik.ormawa');
    Route::get('/grafik/lomba', [GrafikController::class, 'lomba'])->name('grafik.lomba');
    Route::patch('/budget/{id}', [GrafikController::class, 'updateBudget'])
        ->middleware('role:wd2')->name('budget.update');

    Route::get('/report', [\App\Http\Controllers\ReportController::class, 'index'])->name('report.index');
    Route::get('/report/export', [\App\Http\Controllers\ReportController::class, 'export'])->name('report.export');
});
