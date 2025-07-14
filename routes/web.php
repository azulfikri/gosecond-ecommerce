<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Request;
use App\Http\Controllers\LandingController;

Route::get('/', function () {
    return view('landing');
});
