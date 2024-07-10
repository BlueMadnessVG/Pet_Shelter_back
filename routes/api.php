<?php

use App\Http\Controllers\API\AdoptionController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\PetController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

//  ROUTES FOR AUTH
Route::post('/register', [UserController::class, 'register']);
Route::post('/login', [UserController::class, 'login']);

//  ROUTES FOR PETS (WITH NO AUTH)
Route::get('/pets', [PetController::class, 'index']);
Route::get('/petsList', [PetController::class, 'list']);
Route::get('/pets/{id}', [PetController::class, 'show']);
Route::get('/pets/search/{id}', [PetController::class, 'search']);

// ROUTES FOR BREED (WITH NO AUTH)
Route::get('/breed', [PetController::class, 'show_breeds']);
Route::post('/breed', [PetController::class, 'store_breed']);

Route::post('/pets', [PetController::class,'store']);

Route::middleware('auth:sanctum')->group(function () {
    //  ROUTE CONTROLLER FOR PETS (WITH AUTH)
    Route::patch('/pets/{id}', [PetController::class,'update'])->middleware('admin');
    Route::post('/pets/report/{id}', [PetController::class,'store_report'])->middleware('admin');
    Route::patch('/pets/report/{id}', [PetController::class,'update_report'])->middleware('admin');
    Route::post('adoption/management/{id}', [AdoptionController::class,'acceptAdoption'])->middleware('admin');
    
    //  ROUTE CONTROLLER FOR ADOPTIONS
    Route::get('adoption', [AdoptionController::class,'index']);
    Route::get('adoption/{id}', [AdoptionController::class,'show']);
    Route::post('/adoption', [AdoptionController::class,'store']);
    Route::patch('/adoption/{id}', [AdoptionController::class,'update']);

    Route::fallback(function () {
        return response()->json(['error' => 'Unauthenticated', 401]);
    });
});

Route::fallback(function () {
    return response()->json(['error' => 'Unauthenticated', 401]);
});