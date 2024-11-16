<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\GameController;
use App\Http\Middleware\AdminAuthentication;
use App\Http\Middleware\UserAuthentication;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function(){
    Route::prefix('games')->controller(GameController::class)->group(function(){
        Route::get('/', [GameController::class,'gameList']);
        Route::get('category/{category}',[GameController::class,'gameCategory']);
        Route::get('popular',[GameController::class,'gamePopular']);
        Route::get('round/{id}', [GameController::class, 'gameRound']);
        Route::post('transaction}', [GameController::class, 'gameTransaction']);
    });
    Route::prefix('auth')->controller(AuthController::class)->group(function(){
        Route::post('login', 'login');
    });

    Route::prefix('users')->middleware([UserAuthentication::class])->group(function(){
        Route::get('game/history', [GameController::class, 'gameHistory']);
        Route::get("game/play/{id}", [GameController::class, 'gamePlay']);
    });


    Route::prefix('admin')->controller(AdminController::class)->group(function(){
        Route::post('/login', 'login');

        Route::middleware([AdminAuthentication::class])->controller(AdminController::class)->group(function(){
            Route::post('agent/create', 'createAgent');
        });
    });
});
