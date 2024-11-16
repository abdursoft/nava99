<?php

namespace App\Http\Controllers;

use App\Models\Balance;
use App\Models\Bonus;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BonusController extends Controller
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
        
    }

    /**
     * Display the specified resource.
     */
    public function show(Bonus $bonus)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Bonus $bonus)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Bonus $bonus)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Bonus $bonus)
    {
        //
    }

    /**
     * Bonus rewards
     */
    public function rewards(Request $request){
        $validator = Validator::make($request->all(),[
            "rewardType" => 'required',
            "rewardTitle" => 'required',
            "txnId" => 'required',
            "playerId" => 'required',
            "amount" => 'required',
            "currency" => 'required',
        ]);

        if($validator->fails()){
            return response()->json([
                'code' => 'REQUIRED_DATA_MISSING',
                'message' => 'All required data should be put',
                'errors' => $validator->errors()
            ],400);
        }


        try {
            DB::beginTransaction();
            Bonus::create($validator->validate());
            $transaction = Transaction::create([
                'txnType' => 'CREDIT',
                'amount' => $request->input('amount'),
                'playerId' => $request->input('playerId'),
                'currency' => $request->input('currency'),
                'options' => 'Bonus Rewards'
            ]);
            Balance::where('user_id',$request->input('playerId'))->increment('balance',$request->amount);
            $balance = Balance::find($request->input('playerId'));
            DB::commit();
            return response()->json([
                "balance" => $balance->balance,
                "referenceId" => $transaction->id
            ]); //balance, referenceId
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'code' => 'ECECPTION_ERROR',
                'message' => $th->getMessage()
            ], 400);
        }
    }
}
