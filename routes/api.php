<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return json_encode([
        'description' => 'StudoSlug URL Shortener',
        'version'     => '1.0'
    ]);
});
