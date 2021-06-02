<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UrlController;

Route::get('/', function () {
    return json_encode([
        'description' => 'StudoSlug URL Shortener',
        'version'     => '1.0'
    ]);
});

Route::post('[{url:.*}]', 'UrlController@create');
Route::get('/{slug}', 'UrlController@show');
