<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Authorization;
use App\Http\Controllers\Siswa;
use App\Http\Controllers\Admin;
use App\Http\Controllers\Printer;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KelasController;
use App\Http\Controllers\TahunController;
use App\Http\Controllers\SiswaImportController;
use App\Http\Controllers\SiswaDataController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StaffController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
// Login Functions
Route::get('/', [Authorization::class, 'index'])->name('login')->middleware('guest');
Route::post('/', [Authorization::class, 'auth'])->middleware('guest');
Route::get('/masuk', [Authorization::class, 'adjust'])->middleware('auth');
Route::post('/keluar', [Authorization::class, 'logout']);

// Students Exclusive Routes
Route::get('/home', [Siswa::class, 'index'])->middleware('auth');
Route::get('/biodata/{biodata}', [Siswa::class, 'bio_form'])->middleware('auth');
Route::put('/biodata/{biodata}', [Siswa::class, 'edit_bio'])->middleware('auth');
Route::get('/data-ayah/{dad}', [Siswa::class, 'dad_form'])->middleware('auth');
Route::put('/data-ayah/{dad}', [Siswa::class, 'edit_dad'])->middleware('auth');
Route::get('/data-ibu/{mom}', [Siswa::class, 'mom_form'])->middleware('auth');
Route::put('/data-ibu/{mom}', [Siswa::class, 'edit_mom'])->middleware('auth');
Route::get('/data-wali/{guardian}', [Siswa::class, 'guardian_form'])->middleware('auth');
Route::put('/data-wali/{guardian}', [Siswa::class, 'edit_guardian'])->middleware('auth');
Route::get('/password/{user}', [Siswa::class, 'password_form'])->middleware('auth');
Route::put('/password/{user}', [Siswa::class, 'change_password'])->middleware('auth');

// Admin Exclusive Routes
Route::get('/dashboard', [Admin::class, 'index'])->middleware('admin');
Route::post('/pindah', [Admin::class, 'move_students'])->middleware('admin');
Route::post('/admin/siswa/cek-duplikat', [UserController::class, 'cekDuplikat'])->middleware('admin');
Route::get('/admin/siswa/import', [SiswaImportController::class, 'form'])->middleware('admin');
Route::post('/admin/siswa/import', [SiswaImportController::class, 'store'])->middleware('admin');
Route::get('/admin/siswa/import/template', [SiswaImportController::class, 'template'])->middleware('admin');
Route::get('/admin/siswa/import-lengkap', [SiswaImportController::class, 'formLengkap'])->middleware('admin');
Route::post('/admin/siswa/import-lengkap', [SiswaImportController::class, 'storeLengkap'])->middleware('admin');
Route::get('/admin/siswa/import-lengkap/template', [SiswaImportController::class, 'templateLengkap'])->middleware('admin');
Route::post('/admin/siswa/ekspor-pilihan', [SiswaDataController::class, 'exportPilihan'])->middleware('admin');
Route::get('/admin/siswa/ekspor-semua-csv', [SiswaDataController::class, 'exportSemuaCsv'])->middleware('admin');
Route::get('/admin/siswa/{siswa}/ekspor', [SiswaDataController::class, 'exportSatuCsv'])->middleware('admin');
Route::resource('/admin/siswa', UserController::class)->middleware('admin');
Route::resource('/admin/grup', KelasController::class)->middleware('admin');
Route::resource('/admin/tahun', TahunController::class)->middleware('admin');
Route::get('admin/ayah/{dad}', [Admin::class, 'dad_form'])->middleware('admin');
Route::put('admin/ayah/{dad}', [Admin::class, 'edit_dad'])->middleware('admin');
Route::get('admin/ibu/{mom}', [Admin::class, 'mom_form'])->middleware('admin');
Route::put('admin/ibu/{mom}', [Admin::class, 'edit_mom'])->middleware('admin');
Route::get('admin/wali/{guardian}', [Admin::class, 'guardian_form'])->middleware('admin');
Route::put('admin/wali/{guardian}', [Admin::class, 'edit_guardian'])->middleware('admin');
Route::get('admin/biodata/{biodata}', [Admin::class, 'bio_form'])->middleware('admin');
Route::put('admin/biodata/{biodata}', [Admin::class, 'edit_bio'])->middleware('admin');
Route::get('admin/mutasi', [Admin::class, 'list_mutasi'])->middleware('admin');
Route::get('admin/mutasi/{siswa}', [Admin::class, 'mutasi_form'])->middleware('admin');
Route::post('admin/mutasi/{siswa}', [Admin::class, 'edit_mutasi'])->middleware('admin');
Route::delete('/admin/mutasi/{mutation}', [Admin::class, 'delete_mutasi'])->name('mutasi.delete');
Route::get('/admin/pengaturan', [SettingController::class, 'edit'])->middleware('superadmin');
Route::post('/admin/pengaturan', [SettingController::class, 'update'])->middleware('superadmin');

// Akun petugas (hanya superadmin): menambah & mengelola akun Tata Usaha
Route::resource('/admin/akun', StaffController::class)
    ->except(['show'])
    ->parameters(['akun' => 'akun'])
    ->middleware('superadmin');
Route::get('admin/print', [Printer::class, 'daftar_siswa'])->middleware('admin');
Route::get('admin/print/daftar/preview', [Printer::class, 'preview_daftar'])->middleware('admin');
Route::post('admin/print/terpilih', [Printer::class, 'buku_induk_pilihan'])->middleware('admin');
Route::post('admin/print/terpilih/preview', [Printer::class, 'siapkan_preview_pilihan'])->middleware('admin');
Route::get('admin/print/terpilih/preview', [Printer::class, 'preview_pilihan'])->middleware('admin');
Route::get('admin/print/terpilih/berkas', [Printer::class, 'berkas_pilihan'])->middleware('admin');
Route::get('admin/print/{siswa}/preview', [Printer::class, 'preview_siswa'])->middleware('auth');
Route::get('admin/print/{siswa}', [Printer::class, 'data_siswa'])->middleware('auth');

// // tampil semua mutasi
// Route::get('/admin/mutasi', [AdminController::class, 'index']);





