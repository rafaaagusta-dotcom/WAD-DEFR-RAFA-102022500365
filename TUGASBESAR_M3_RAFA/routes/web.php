<?php
 
use App\Http\Controllers\RestoranController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RestoranController::class, 'index'])->name('meja.index');
Route::get('/edit/{id}', [RestoranController::class, 'edit'])->whereNumber('id')->name('meja.edit');
Route::post('/edit/{id}', [RestoranController::class, 'update'])->whereNumber('id')->name('meja.update');
 
Route::get('/booking', [RestoranController::class, 'booking'])->name('booking.index');
Route::post('/booking', [RestoranController::class, 'simpanBooking'])->name('booking.store');
Route::post('/booking/batal', [RestoranController::class, 'batalBooking'])->name('booking.batal');
Route::delete('/edit/{id}', [RestoranController::class, 'hapus'])->whereNumber('id')->name('meja.hapus');
Route::get('/tambah', [RestoranController::class, 'tambah'])->name('meja.tambah');
Route::post('/tambah', [RestoranController::class, 'simpan'])->name('meja.simpan');