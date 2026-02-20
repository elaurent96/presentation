<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\AdminController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Public API routes (no auth required)
|
*/

Route::middleware(['web'])->group(function () {
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/all', [ProjectController::class, 'all']);
    Route::get('/playlist', [ProjectController::class, 'playlist']);
    Route::get('/project/{name}', [ProjectController::class, 'show']);
    Route::post('/save', [ProjectController::class, 'save']);
    Route::post('/upload', [ProjectController::class, 'upload']);
});

/*
|--------------------------------------------------------------------------
| Admin API Routes - 94-character token required
|--------------------------------------------------------------------------
|
| All routes require a valid 94-character API token in the Authorization header:
| Authorization: Bearer YOUR_94_CHARACTER_TOKEN
|
*/

Route::middleware(['api.token'])->prefix('admin')->group(function () {
    
    // Users CRUD
    Route::get('/users', [AdminController::class, 'getUsers']);
    Route::post('/users', [AdminController::class, 'createUser']);
    Route::put('/users/{id}', [AdminController::class, 'updateUser']);
    Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);
    
    // Projects
    Route::get('/projects', [AdminController::class, 'getProjects']);
    Route::delete('/projects/{userId}/{name}', [AdminController::class, 'deleteProject']);
    
    // Playlist
    Route::get('/playlist', [AdminController::class, 'getPlaylist']);
    Route::post('/playlist', [AdminController::class, 'updatePlaylist']);
    
    // Stats
    Route::get('/stats', [AdminController::class, 'getStats']);
});

// API documentation route (Swagger UI)
Route::get('/docs', function() {
    return view('swagger');
});
