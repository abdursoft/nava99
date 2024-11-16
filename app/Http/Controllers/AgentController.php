<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AgentController extends Controller
{
    /***
     * Create new user
     */
    public function createUser(Request $request){
        $validate = Validator::make($request->all(), [
            'phone' => 'required|unique:users,phone',
            'user_name' => 'required|unique:users,user_name',
            'password' => 'required|min:4',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Couldn\'t create user',
                'errors' => $validate->errors()
            ], 400);
        }

        
        try {
            DB::beginTransaction();
            
            $agent =  json_decode($request->header('agent'));
            if(!empty($request->input('balance'))){
                if($request->input('balance') <= $agent->balance){
                    User::where('id',$agent->id)->decrement('balance',$request->input('balance'));
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'Insufficient Balance',
                    ], 400); 
                }
            }

            $user = User::create([
                'phone' => $request->input('phone'),
                'password' => bcrypt($request->input('password')),
                'user_name' => $request->input('user_name'),
                'balance' => $request->input('balance') ?? 0,
                'email' => $request->input('email') ?? '',
                'currency' => $request->input('currency') ?? 'bdt',
                'country' => $request->input('country') ?? '',
                'city' => $request->input('city') ?? '',
                'street' => $request->input('street') ?? '',
                'agent_id' => $agent->id,
                'role' => 'user'
            ]);

            if(!empty($request->input('balance'))){
                Transaction::create([
                    'user_id' => $user->id,
                    'agent_id' => $agent->id,
                    'amount' => $request->input('balance') ?? 0,
                    'intent' => 'Deposit',
                    'pay_intent' => 'Debit',
                    'host_role' => 'user',
                    'client_role' => 'agent',
                    'status' => 'Completed'
                ]);
            }

            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'User successfully created',
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Couldn\'t create agent',
                'errors' => $th->getMessage(),
            ], 400);
        }
    }
}
