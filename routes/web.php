<?php

use Illuminate\Support\Facades\Route;

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

Route::get('/', function () {
    return view('welcome');
});
Route::get('/privacy_policy', function () {
    return view('privacy_policy');
});
Route::get('/customer_chat/{id}', "App\Http\Controllers\CustomerController@customer_chat");
Route::get('/create_zone/{id}/{capital_lat}/{capital_lng}', "App\Http\Controllers\CustomerController@create_zone");
Route::post('/save_polygon', "App\Http\Controllers\CustomerController@save_polygon");
Route::get('/phonepe/{amount}',  "App\Http\Controllers\PhonePeController@phonepe");
Route::get('/phonepe_success',  "App\Http\Controllers\PhonePeController@phonepe_success");
Route::get('/phonepe_failed',  "App\Http\Controllers\PhonePeController@phonepe_failed");
Route::any('/phonepe_response',  "App\Http\Controllers\PhonePeController@phonepe_response")->name('response');
