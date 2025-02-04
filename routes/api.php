<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\NasabahController;
use App\Http\Controllers\ReminderController;
use App\Http\Controllers\TargetController;
use App\Http\Controllers\TargetMingguanController;
use Laravel\Sanctum\Http\Controllers\CsrfCookieController;
use App\Http\Controllers\AktivitasController;

Route::get('/sanctum/csrf-cookie', [CsrfCookieController::class, 'show']);
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout']);
Route::post('addUser', [AuthController::class, 'addUser']);
Route::middleware('auth:api')->put('profile/update', [AuthController::class, 'updateProfile']);
Route::middleware('auth:api')->put('password/change', [AuthController::class, 'changePassword']);
Route::middleware('auth:api')->get('/user', [UserController::class, 'getUser']);
Route::middleware('auth:api')->post('update-profile-image', [AuthController::class, 'updateProfileImage']);
Route::post('/input-nasabah', [NasabahController::class, 'tambahNasabah']);
Route::middleware('auth:api')->get('/nasabah', [NasabahController::class, 'index']);
Route::middleware('auth:api')->post('aktivitas', [AktivitasController::class, 'tambahAktivitas']);
Route::middleware(['auth:api'])->get('/aktivitas', [AktivitasController::class, 'index']);
Route::middleware(['auth:api'])->get('/nama-nasabah', [NasabahController::class, 'getNamaNasabah']);
Route::middleware('auth:api')->get('/aktivitas-ditunda', [AktivitasController::class, 'getAktivitasDitunda']);
Route::get('/aktivitas-selesai', [AktivitasController::class, 'getAktivitasSelesai']);
Route::middleware('auth:api')->group(function () {
    Route::post('/reminders', [ReminderController::class, 'addReminder']);
    Route::get('/reminders', [ReminderController::class, 'getUserReminders']);
    Route::delete('/reminders/{id}', [ReminderController::class, 'deleteReminder']);
});
Route::get('/aktivitas-harian', [AktivitasController::class, 'getAktivitasHarian']);
Route::get('/total-aktivitas-bulanan', [AktivitasController::class, 'getTotalAktivitasBulanan']);
Route::get('/aktivitas-bulanan', [AktivitasController::class, 'getAktivitasBulananDetail']);
Route::get('/aktivitas/tahun-per-bulan', [AktivitasController::class, 'getAktivitasBulanan']);
Route::get('total-aktivitas-bulan-ini', [AktivitasController::class, 'getTotalAktivitasBulanIni']);
Route::get('/nasabah/{id}', [NasabahController::class, 'profilNasabah']);
Route::get('/nama-staff', [AuthController::class, 'getStaffData']);
Route::get('/detail-aktivitas/{id}', [AktivitasController::class, 'detailAktivitas']);
Route::get('/total-aktivitas-mingguan', [AktivitasController::class, 'getTotalMingguan']);
Route::get('/aktivitas-mingguan', [AktivitasController::class, 'getAktivitasMingguan']);
Route::get('/jumlah-aktivitas-mingguan', [AktivitasController::class, 'getJumlahMingguan']);
Route::get('/nama-staff-nip', [AuthController::class, 'namaStaffNip']);
Route::get('/aktivitas-staff/{nip}', [AktivitasController::class, 'getAktivitasNip']);
Route::post('/add-target-tahunan/{user_id}', [TargetController::class, 'addTargetTahunan']);
Route::middleware('auth:api')->get('target-tahunan', [TargetController::class, 'getTargetTahunan']);
Route::get('/nilai-kpi/{nip}', [TargetController::class, 'getTotalNilaiKpi']);
Route::middleware('auth:api')->get('/rata-rata-kpi', [TargetController::class, 'getRataRataNilaiKpiUserLogin']);
Route::get('/target-tahunan-staff', [TargetController::class, 'getTargetTahunanByNip']);
Route::post('/add-target-mingguan/{nip}', [TargetController::class, 'addTargetMingguan']);
Route::post('/add-target-harian/{nip}', [TargetController::class, 'addTargetHarian']);
Route::middleware(['auth:api'])->group(function () {
    Route::get('target-mingguan', [TargetController::class, 'getTargetMingguan']);
    Route::get('target-harian', [TargetController::class, 'getTargetHarian']);
});
Route::get('/recent-data', [AktivitasController::class, 'getRecentData']);
Route::middleware('auth:api')->get('/target-kpi', [TargetController::class, 'getKpiByUser']);
Route::put('/update-target-harian/{nip}', [TargetController::class, 'updateTargetHarian']);
Route::put('/update-target-mingguan/{nip}', [TargetController::class, 'updateTargetMingguan']);
Route::put('/update-aktivitas/{id}', [AktivitasController::class, 'updateAktivitas']);

Route::post('/aktivitas/{id}/dokumentasi', [AktivitasController::class, 'tambahDokumentasi']);
Route::delete('/aktivitas/{id}/dokumentasi/{document_id}', [AktivitasController::class, 'hapusDokumentasi']);
Route::delete('/hapus-aktivitas/{id}', [AktivitasController::class, 'hapusAktivitas']);
Route::patch('/update-target-tahunan/{user_id}/{kpi_name}', [TargetController::class, 'updateTargetTahunan']);
Route::middleware('auth:api')->group(function () {
    // Only accessible by admins
    Route::get('admin/target-kpi/{userId}', [TargetController::class, 'getKpiByUserAdmin']);
});
Route::put('/update-nasabah/{id}', [NasabahController::class, 'updateNasabah']);
Route::middleware('auth:api')->get('/aktivitas-ditunda-staff', [AktivitasController::class, 'getAktivitasDitundaStaff']);
Route::delete('/hapus-nasabah/{id}', [NasabahController::class, 'hapusNasabah']);
Route::get('total-aktivitas', [AktivitasController::class, 'totalAktivitas']);



// Route::middleware('auth:api')->get('/user', function (Request $request) {
//     return $request->user();
// });
