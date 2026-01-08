<?php

use App\Http\Controllers\UmlController;
use Illuminate\Support\Facades\Route;

Route::post('/uml/generate', [UmlController::class, 'generate']);
