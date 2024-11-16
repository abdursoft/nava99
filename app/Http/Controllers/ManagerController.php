<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Manager;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ManagerController extends Controller
{
    
    /**
     * Create new Agent
     */
    public function createAgent(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'phone' => 'required|unique:users,phone',
            'user_name' => 'required|unique:users,user_name',
            'password' => 'required|min:4',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Couldn\'t create agent',
                'errors' => $validate->errors()
            ], 400);
        }

        
        try {
            DB::beginTransaction();

            $manager =  json_decode($request->header('manager'));
            if(!empty($request->input('balance'))){
                if($request->input('balance') <= $manager->balance){
                    User::where('id',$manager->id)->decrement('balance',$request->input('balance'));
                }else{
                    return response()->json([
                        'status' => false,
                        'message' => 'Insufficient Balance',
                    ], 400); 
                }
            }

            $agent = User::create([
                'phone' => $request->input('phone'),
                'password' => bcrypt($request->input('password')),
                'user_name' => $request->input('user_name'),
                'balance' => $request->input('balance') ?? 0,
                'email' => $request->input('email') ?? '',
                'currency' => $request->input('currency') ?? 'bdt',
                'country' => $request->input('country') ?? '',
                'city' => $request->input('city') ?? '',
                'street' => $request->input('street') ?? '',
                'manager_id' => $manager->id,
                'role' => 'agent'
            ]);

            if(!empty($request->input('balance'))){
                Transaction::create([
                    'manager_id' => $manager->id,
                    'agent_id' => $agent->id,
                    'amount' => $request->input('balance'),
                    'intent' => 'Deposit',
                    'pay_intent' => 'Debit',
                    'host_role' => 'agent',
                    'client_role' => 'manager',
                    'status' => 'Completed'
                ]);
            }


            DB::commit();
            return response()->json([
                'status' => true,
                'message' => 'Agent successfully created',
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
