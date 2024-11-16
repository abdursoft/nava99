<?php

use App\Http\Controllers\BalanceController;
use App\Http\Controllers\BonusController;
use App\Http\Controllers\GameController;
use App\Http\Controllers\TransactionController;
use App\Http\Middleware\QTAuthentication;
use Illuminate\Support\Facades\Route;

Route::get('/', function(){
    return response()->json([
        'code' => "VISUAL_VIEW_NOT_FOUND",
        'message' => "This site is under construction, Thanks for your query"
    ],200);
});

Route::prefix('staging/qt')->group(function(){

    Route::get('/', [GameController::class, 'gameLobby']);

    Route::get('promotion',function(){
        return "<h2>Welcome to your promotion page</h2>";
    });


    Route::get('rewards', function(){
        return "<h2>Reward page</h2>";
    });


    Route::get("cashier", function(){
        return "<h2>Cashier Page</h2>";
    });

    Route::prefix('user')->group(function(){
        Route::get('login', function(){
            return "<h2>Login page</h2>";
        });

        Route::get('token-verify', function(){
            return "<h2>Verify Token page</h2>";
        });
    });
});

Route::prefix('production/qt')->group(function(){

    Route::get('/', function () {
        return view('welcome');
    });

    Route::get('promotion',function(){
        return "<h2>Welcome to your promotion page</h2>";
    });


    Route::get('rewards', function(){
        return "<h2>Reward page</h2>";
    });


    Route::get("cashier", function(){
        return "<h2>Cashiar Page</h2>";
    });

    Route::prefix('user')->group(function(){
        Route::get('login', function(){
            return "<h2>Login page</h2>";
        });

        Route::get('token-verify', function(){
            return "<h2>Verify Token page</h2>";
        });
    });
});

Route::prefix('accounts')->middleware([QTAuthentication::class])->group(function(){
    Route::get('{playerId}/session', [BalanceController::class, 'playerBalance']);
    Route::get('{playerId}/balance', [BalanceController::class, 'playerBalance']);
});

Route::middleware([QTAuthentication::class])->group(function(){
    Route::post('transactions', [TransactionController::class, 'store']);
    Route::post('transactions/rollback', [TransactionController::class, 'store']);
    Route::post('bonus/reward', [BonusController::class, 'rewards']);
});

