<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::controller(\App\Http\Controllers\FileController::class)
    ->middleware(['auth', 'verified'])
    ->group(function () {
        Route::get('/my-files/{folder?}', 'myFiles')
            ->where('folder', '(.*)')
            ->name('myFiles');
        Route::get('/trash', 'trash')->name('trash');
        Route::get('/shared-with-me/{folder?}', 'sharedWithMe')
            ->where('folder', '(.*)')
            ->name('SWMe');
        Route::get('/shared-by-me/{folder?}', 'sharedByMe')
            ->where('folder', '(.*)')
            ->name('SBMe');
        Route::post('/folder/create', 'createFolder')->name('folder.create');
        Route::post('/file', 'storeFile')->name('file.store');
        Route::post('/file/restore', 'restore')->name('file.restore');
        Route::delete('/file', 'moveToTrash')->name('file.trash');
        Route::delete('/file/delete', 'deleteForever')->name('file.delete');
        Route::post('/file/add-to-favorite', 'addToFavourite')->name('file.addToFavourite');
        Route::post('/file/add-remove-to-favorite', 'addRemoveFavourite')->name('file.addRemoveFavourite');
        Route::post('/file/share', 'share')->name('file.share');
        Route::get('/file/download', 'download')->name('file.download');
        Route::get('/file/sdownload', 'sdownload')->name('file.sdownload');
    });


Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
