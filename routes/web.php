<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelController;

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

// Route::get('/', [ExcelController::class, 'generateExcel']);

Route::get('/', function() {
    return view('welcome');
});

Route::post('/download', [ExcelController::class, 'generateExcel'])->name("download#excel");


