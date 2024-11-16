<?php

namespace App\Http\Controllers;

use App\Models\Reward;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RewardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $data = [
                "rewardType" => $request->input('rewardType'),
                "rewardTitle" => $request->input('rewardTitle'),
                "txnId" => $request->input('txnId'),
                "playerId" => $request->input('playerId'),
                "amount" => $request->input('amount'),
                "currency" => $request->input('currency'),
                "created" => $request->input('created'),
            ];
            DB::beginTransaction();
            $reward = Reward::create($data);
            $user = User::find($request->header('id'));
            if($request->input('completed') === 'true'){
                $user->decrement('balance',$request->input('amount'));
            }
            DB::commit();
            return response()->json([
                'balance' => $user->balance,
                'referenceId' => $reward->id
            ],201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                "code" => "LOGIN_FAILED",
                "message" => "Unauthorized Access"
            ],401);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Reward $reward)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Reward $reward)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Reward $reward)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Reward $reward)
    {
        //
    }
}
