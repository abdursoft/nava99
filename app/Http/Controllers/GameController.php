<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class GameController extends Controller
{

    /**
     * Game token
     */
    public function gameToken()
    {
        try {
            $data = Http::withQueryParameters([
                "grant_type" => "password",
                "response_type" => "token",
                "username" => env("QT_USER_NAME"),
                "password" => env("QT_PASSWORD")
            ])->get(env('QT_END_POINT') . "/v1/auth/token")->throw()->json();
            return $data['access_token'];
        } catch (\Throwable $th) {
            return false;
        }
    }


    /**
     * Game list
     */
    public function gameList()
    {
        try {
            $list = Http::withToken($this->gameToken())->get(env('QT_END_POINT') . "/v2/games")->throw()->json();
            return response()->json($list, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Unauthorized Action"
            ], 401);
        }
    }

    /**
     * Popular game
     */
    public function gamePopular()
    {
        try {
            $list = Http::withToken($this->gameToken())->get(env('QT_END_POINT') . "/v2/games/most-popular")->throw()->json();
            return response()->json($list, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Unauthorized Action"
            ], 401);
        }
    }

    /**
     * Game list by types
     */
    public function gameCategory($category)
    {
        try {
            $payload = [
                "gameTypes" => $category,
                "orderBy" => "DESC"
            ];
            $list = Http::withToken($this->gameToken())->withQueryParameters($payload)->get(env('QT_END_POINT') . "/v2/games")->throw()->json();
            return response()->json($list, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Unauthorized Action"
            ], 401);
        }
    }


    /**
     * Request handler
     */
    public function gameHandler($walletToken, $method, $url, $body = null)
    {
        $header = array(
            'Content-Type:application/json',
            'Wallet-Session:' . $walletToken,
            'Pass-Key:' . env("QT_PASSWORD")
        );
        $url = curl_init($url);
        curl_setopt($url, CURLOPT_HTTPHEADER, $header);
        curl_setopt($url, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
        $body !== null ? curl_setopt($url, CURLOPT_POSTFIELDS, json_encode($body)) : "";
        curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
        $resultdatax = curl_exec($url);
        $obj = json_decode($resultdatax, true);
        curl_close($url);
        return $obj;
    }

    public function gameLobby(Request $request)
    {
        $data = [
            "currency" => env('APP_CURRENCY'),
            "lang" => env('APP_LANG'),
            "mode" => env("APP_MODE"),
            "gender" => "M",
            "birthDate" => "1999-05-22",
            "device" => "desktop",
            "walletSessionId" => "7625187"
        ];
        return Http::withToken($this->gameToken())->post(env('QT_END_POINT') . "/v1/games/lobby-url", $data);
    }

    /**
     * Single game
     */
    public function gamePlay(Request $request, $id)
    {
        try {
            $user = User::find($request->header('id'));
            $wallet = (new WalletController)->walletSession($request->header('id'));
            $payload =  [
                "playerId" => $user->id,
                "currency" => $user->currency,
                "country" => $user->country ?? 'BD',
                "gender" => "M",
                "birthDate" => $user->dob,
                "lang" => "bn_BD",
                "mode" => env("APP_MODE"),
                "device" => "desktop",
                "returnUrl" => "https://api.nava99.pro",
                "walletSessionId" => $wallet
            ];
            $url = Http::withToken($this->gameToken())->post(env('QT_END_POINT') . "/v1/games/$id/launch-url", $payload)->throw()->json();
            return response()->json($url, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 'GAME_NOT_FOUND',
                'message' => "Game Not Found"
            ], 404);
        }
    }

    /**
     * Played game history
     */
    public function gameHistory(Request $request)
    {
        try {
            $user = User::find($request->header('id'));
            $payload = [
                "currency" => $user->currency,
                "country" => $user->country,
            ];
            $url = Http::withToken($this->gameToken())->post(env('QT_END_POINT') . "/v1/players/$user->id/service-ur", $payload)->throw()->json();
            return response()->json($url, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'code' => 'INVALID_TOKEN',
                'message' => "The request could not be processed due to validation error."
            ], 401);
        }
    }

    /**
     * Game round details
     */
    public function gameRound($id)
    {
        try {
            $list = Http::withToken($this->gameToken())->get(env('QT_END_POINT') . "/v1/game-round/$id")->throw()->json();
            return response()->json($list, 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => "Unauthorized Action"
            ], 401);
        }
    }

    /**
     * Game transaction search
     */
    public function gameTransaction(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "from" => 'required',
            'to' => 'required',
            'playerId' => $request->header('id')
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => "INPUT_VALIDATION_ERROR",
                'message' => 'Please fill-up the required fields',
            ], 400);
        }

        try {
            $list = Http::withToken($this->gameToken())->withQueryParameters($validator->validate())->get(env('QT_END_POINT') . "/v1/game-transaction")->throw()->json();
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
}
