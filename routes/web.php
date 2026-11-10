<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('login');
});

Route::get('/home', function () {
    return view('home');
});

Route::get('/conversation/{id}', function ($id) {
    return view('conversation', [
        'conversationId' => $id
    ]);
});

Route::get('/archived', function () {
    return view('archived');
});
