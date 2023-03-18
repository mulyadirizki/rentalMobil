<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

// backend
use App\Http\Controllers\Backend\MainController;
use App\Http\Controllers\Backend\MobilController;
use App\Http\Controllers\Backend\RekeningController;

// frontend
use App\Http\Controllers\Frontend\FrontController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [FrontController::class, 'index'])->name('home');

Route::get('/login', [AuthController::class, 'loginPage'])->name('login');
Route::post('/login-store', [AuthController::class, 'loginStore'])->name('loginStore');
Route::get('/registrasi', [AuthController::class, 'registrasiPage'])->name('registrasi');
Route::get('logout', [AuthController::class, 'logout'])->name('logout');

Route::group([ 'prefix' => 'admin' ,'middleware' => 'isAdmin'], function() {
    Route::get('/', [MainController::class, 'index'])->name('admin');

    Route::get('/mobil', [MobilController::class, 'getMobilAdmin'])->name('getMobilAdmin');
    Route::get('/mobil/create', [MobilController::class, 'addMobil'])->name('addMobil');
    Route::post('/mobil/store', [MobilController::class, 'addMobilStore'])->name('addMobilStore');
    Route::delete('/mobil/delete/{id_mobil}', [MobilController::class, 'deleteMobil'])->name('deleteMobil');
    Route::get('/mobil/update/{id_mobil}', [MobilController::class, 'upadteMobil'])->name('upadteMobil');
    Route::post('/mobil/update/store', [MobilController::class, 'upadteMobilStore'])->name('upadteMobilStore');

    //data rekening
    Route::get('/rekening', [RekeningController::class, 'getRekeningAdmin'])->name('getRekeningAdmin');
    Route::get('/rekening/create', [RekeningController::class, 'addRekening'])->name('addRekening');
    Route::post('/rekening/store', [RekeningController::class, 'addRekeningStore'])->name('addRekeningStore');
    Route::get('/rekening/update/{id_rek}', [RekeningController::class, 'upadteRekening'])->name('upadteRekening');
    Route::post('/rekening/update/store', [RekeningController::class, 'upadteRekeningStore'])->name('upadteRekeningStore');
    Route::delete('/rekening/delete/{id_rek}', [RekeningController::class, 'deleteRekening'])->name('deleteRekening');

    // data Users
    Route::get('/data-rental', [MainController::class, 'getRentaldmin'])->name('getRentaldmin');
    Route::get('/data-user', [MainController::class, 'getUserdmin'])->name('getUserdmin');

    // transaksi rental kembali
    Route::get('/data-rental-kembali', [MainController::class, 'getRentalKembali'])->name('getRentalKembali');
    Route::post('/data-rental-kembali/store', [MainController::class, 'rentalKembaliStore'])->name('rentalKembaliStore');
});

Route::group([ 'prefix' => 'id/u/user' ,'middleware' => 'isUser'], function() {
    Route::get('/', [FrontController::class, 'getHome'])->name('user');

    Route::get('/mobil', [FrontController::class, 'getMobil'])->name('getMobil');
    Route::get('/mobil/{id_mobil}', [FrontController::class, 'getMobilDetail'])->name('getMobilDetail');
    Route::post('/mobil/rental', [FrontController::class, 'addMobilRental'])->name('addMobilRental');

    // pembayaran
    Route::get('/data/rental', [FrontController::class, 'getDataRental'])->name('getDataRental');
    Route::get('/mobil/pembayaran/{id_rental}', [FrontController::class, 'pembyaranRental'])->name('pembyaranRental');
    Route::post('/mobil/create', [FrontController::class, 'pembayarangAdd'])->name('pembayarangAdd');
});
