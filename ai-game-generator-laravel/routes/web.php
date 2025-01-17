<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameGeneratorController;

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

// Make game generator the default route
Route::get('/', [GameGeneratorController::class, 'showGenerator'])->name('generator.show');

// Game Generator Routes
Route::get('/generate', [GameGeneratorController::class, 'showGenerator']);
Route::post('/generate', [GameGeneratorController::class, 'generateGamePhase'])->name('generator.create');

// Game Display Route
Route::get('/game/{id}', [GameGeneratorController::class, 'showGame'])->name('game.show');

// Game Update Route
Route::put('/game/{id}', [GameGeneratorController::class, 'updateGamePhase'])->name('game.update');
