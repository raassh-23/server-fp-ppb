<?php

use App\Http\Controllers\ImageController;
use App\Http\Controllers\TranslationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/image/upload', [ImageController::class, 'upload']);
Route::get('/image/list', [ImageController::class, 'list']);

Route::get('/translation/history', [TranslationController::class, 'history']);
Route::get('/translation/detect', [TranslationController::class, 'detectLanguage']);
Route::get('/translation/lang/{lang}', [TranslationController::class, 'getAvailableLanguages']);
Route::post('/translation', [TranslationController::class, 'translate']);
