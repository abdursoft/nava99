<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
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
            DB::beginTransaction();
            $data =  [
                "txnType" => $request->input('txnType'),
                "txnId" => $request->input('txnId'),
                "playerId" => $request->input('playerId'),
                "roundId" => $request->input('roundId'),
                "amount" => $request->input('amount'),
                "currency" => $request->input('currency'),
                "jpContributions" => $request->input('jpContributions'),
                "gameId" => $request->input('gameId'),
                "betId" => $request->input('betId') ?? null,
                "device" => $request->input('device'),
                "clientType" =>$request->input('clientType'),
                "clientRoundId" => $request->input('clientRoundId'),
                "category" => $request->input('category'),
                "created" => $request->input('created'),
                "completed" => $request->input('completed'),
             ];
            $transaction = Transaction::create($data);
            if($request->header('id')){
                $user = User::find($request->header('id'));
                if($request->input('txnType') === 'DEBIT'){
                    if($user->balance >= $request->input('amount')){
                        $user->decrement('balance',$request->input('amount'));
                    }else{
                        return response()->json([
                            "code" => "INSUFFICIENT_FUNDS",
                            "message" => "Not enough funds for the debit operation"
                        ],400);
                    }
                }
                if($request->input('txnType') === 'CREDIT' && $request->input('completed') === 'true'){
                    $user->increment('balance',$request->input('amount'));
                }
            }
            DB::commit();
            return response()->json([
                "balance" => $user->balance,
                "referenceId" => $transaction->id
            ],201);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'code' => 'UNKNOWN_ERROR',
                'message' => 'Unknown server error'
            ],500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Transaction $transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Transaction $transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Transaction $transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Transaction $transaction)
    {
        //
    }

    /**
     * Game rollback
     */
    public function rollback(Request $request){
         try {
            DB::beginTransaction();
            $data =  [
                "txnType" => "DEBIT",
                "txnId" => $request->input('txnId'),
                "playerId" => $request->input('playerId'),
                "roundId" => $request->input('roundId'),
                "amount" => $request->input('amount'),
                "currency" => $request->input('currency'),
                "jpContributions" => $request->input('jpContributions'),
                "gameId" => $request->input('gameId'),
                "betId" => $request->input('betId') ?? null,
                "device" => $request->input('device'),
                "clientType" =>$request->input('clientType'),
                "clientRoundId" => $request->input('clientRoundId'),
                "category" => $request->input('category'),
                "created" => $request->input('created'),
                "completed" => $request->input('completed'),
             ];
            $transaction = Transaction::create($data);
            $user = User::find($request->header('id'));
            if($request->input('completed') === 'true'){
                $user->decrement('balance',$request->input('amount'));
            }
            DB::commit();
            return response()->json([
                "balance" => $user->balance,
                "referenceId" => $transaction->id
            ],201);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'code' => 'UNKNOWN_ERROR',
                'message' => 'Unknown server error'
            ],500);
        }
    }
}
