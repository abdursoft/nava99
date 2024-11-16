<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\JWTAuth;
use App\Models\Balance;
use App\Models\User;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    /**
     * Wallet session
     */
    public function walletSession($id){
        $user = User::find($id);
        return JWTAuth::walletSession('Wallet-Session',8740,$user->balance,$user->currency,$id);
    }
}
