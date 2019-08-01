<?php

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

Route::get('/', 'SentimentController@homepage')->name('homepage');

Route::resource('/sentiment','SentimentController');

Route::get('/sentiment/search/{term?}', 'SentimentController@index');
Route::get('/test', 'SentimentController@test');
Route::get('/update_all', 'SentimentController@update_all_sentiment');

Route::get('/update_training', 'SentimentController@sentiment_training')->name('home');
Route::post('/predict', 'SentimentController@sentiment_training')->name('home');

Auth::routes();
