<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;


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

Route::get('/', function () {
    return view('welcome');
    
});

Route::get('/env-test', function () {
    return response()->json([
        'env_DB_USERNAME' => env('DB_USERNAME'),
        'config_DB_USERNAME' => config('database.connections.mysql.username'),
    ]);
});