<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\CandidateController;

Route::post('/candidates', [CandidateController::class, 'store']);
Route::get('/candidates', [CandidateController::class, 'index']);
Route::put('/candidates/{id}', [CandidateController::class, 'update']);
Route::delete('/candidates/{id}', [CandidateController::class, 'destroy']);
