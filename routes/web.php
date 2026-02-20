<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PlaylistController;

Route::view('/', 'welcome')->name('home');

// Presentation viewer - accessible without auth (opens in new tab)
Route::get('/presentation', function() {
    return view('presentation-view');
})->name('presentation.view');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Editor handles presentations
    Route::get('/editor/{project?}', [ProjectController::class, 'editor'])->name('editor');
    Route::post('/projects', [ProjectController::class, 'create'])->name('projects.create');
    Route::delete('/projects/{project}', [ProjectController::class, 'destroy'])->name('projects.destroy');
    
    // Playlist
    Route::get('/playlist', [PlaylistController::class, 'index'])->name('playlist.index');
    Route::post('/playlist/reorder', [PlaylistController::class, 'reorder'])->name('playlist.reorder');
    
    // Presentation list (requires auth)
    Route::get('/presentations', [ProjectController::class, 'presentation'])->name('presentation.list');
    Route::get('/play', [PlaylistController::class, 'play'])->name('playlist.play');
    
    // Admin
    Route::middleware(['role:admin'])->group(function () {
        Route::get('/admin/users', [DashboardController::class, 'users'])->name('users.index');
        Route::post('/admin/users', [DashboardController::class, 'storeUser'])->name('users.store');
        Route::put('/admin/users/{user}', [DashboardController::class, 'updateUser'])->name('users.update');
        Route::post('/admin/users/{user}/role', [DashboardController::class, 'updateRole'])->name('users.updateRole');
        Route::delete('/admin/users/{user}', [DashboardController::class, 'destroyUser'])->name('users.destroy');
    });
});

require __DIR__.'/auth.php';
