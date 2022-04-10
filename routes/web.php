<?php

use Illuminate\Support\Facades\Route;
use Rentdesk\Nemid\Http\Controllers\NemidController;

Route::get(
    'nemid/javascript',
    [NemidController::class, 'javascript']
)->name('nemid::javascript');

Route::get(
    'nemid/codefile',
    [NemidController::class, 'codefile']
)->name('nemid::codefile');

