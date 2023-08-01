<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LinkShortening\ShortUrlCreateController;
use App\Http\Controllers\LinkShortening\getAccessTokenController;
use App\Http\Controllers\LinkShortening\ShortUrlConvertController;

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

// Route::get('/', function () {
//     return view('welcome');

// });

Route::post('Link-Shortening/generate/',  [ShortUrlCreateController::class, 'index']);
Route::post('Link-Shortening/getAccessToken.php',  [getAccessTokenController::class, 'index']);
Route::get('/{id}',  [ShortUrlConvertController::class, 'index']);

