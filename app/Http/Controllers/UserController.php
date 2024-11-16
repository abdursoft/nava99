<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Auth\AuthController;
use App\Models\Agent;
use App\Models\Refer;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    /**
     * Signup the new users
     */
    public function signup(Request $request){
        $validate = Validator::make($request->all(),[
            'phone' => 'required|min:11',
            'birthday' => 'required',
            'password' => 'required|min:4'
        ]);

        if($validate->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Invalid form data',
                'errors' => $validate->errors()
            ],400);
        }

        try {
            if(!empty($request->input('refer'))){
                $refer = Refer::where('refer_code',$request->input('refer'))->first();
                $user = User::create([
                    'phone' => $request->input('phone'),
                    'password' => bcrypt($request->input('password')),
                    'user_name' => $request->input('user_name'),
                    'balance' => $refer->amount,
                    'email' => $request->input('email') ?? '',
                    'currency' => $request->input('currency') ?? 'bdt',
                    'country' => $request->input('country') ?? '',
                    'city' => $request->input('city') ?? '',
                    'street' => $request->input('street') ?? '',
                    'agent_id' => $refer->user_id
                ]);

                Transaction::create([
                    'user_id' => $user->id,
                    'agent_id' => $refer->user_id,
                    'amount' => $request->input('balance') ?? 0,
                    'intent' => 'Reward',
                    'pay_intent' => 'Debit',
                    'status' => 'Completed'
                ]);
                DB::commit();
                return response()->json([
                    'status' => true,
                    'message' => 'User successfully created',
                ],200);
            }else{
                $user = User::create([
                    'phone' => $request->input('phone'),
                    'password' => bcrypt($request->input('password')),
                    'user_name' => $request->input('user_name'),
                    'balance' => 0,
                    'email' => $request->input('email') ?? '',
                    'currency' => $request->input('currency') ?? 'bdt',
                    'country' => $request->input('country') ?? '',
                    'city' => $request->input('city') ?? '',
                    'street' => $request->input('street') ?? '',
                    'agent_id' => 0
                ]);
                return response()->json([
                    'status' => true,
                    'message' => 'User successfully created',
                ],200);
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'User couldn\'t create',
                'errors' => $validate->errors()
            ],400);
        }
    }

    /**
     * Withdrawal request for the user
     */
    public function withdraw(Request $request){
        $user = Auth::guard('user-api')->user();

        if($request->input('amount') > $user->balance){
            return response()->json([
                'status' => false,
                'message' => 'Insufficient Balance'
            ],400);
        }

        try {
            DB::beginTransaction();
            User::where('id',$user->id)->decrement('balance',$request->input('amount'));
            Transaction::create([
                'user_id' => $user->id,
                'agent_id' => $user->agent_id,
                'amount' => $request->input('amount'),
                'intent' => 'Withdraw',
                'pay_intent' => 'Credit',
                'host_role' => 'user',
                'client_role' => 'agent',
                'status' => 'Pending',
                'end_date' => date('Y-m-d',strtotime("+3 days"))
            ]);
            DB::commit();
            return response()->json([
                'status' => true,
                'message' => "Withdrawal request has been sent to agent"
            ],201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => false,
                'message' => 'Withdrawl request have been failed',
                'errors' => $th->getMessage()
            ],400);
        }
    }

    // Use Details
    public function details(){
        return response()->json(Auth::guard('user-api')->user());
    }


    // will return user transaction
    public function transactions(Request $request, $slug=null){
        if(!empty($slug)){

        }else{
            return Transaction::where('user_id',$request->header('id'))->orWhere('user_id',$request->header('id'))->get();
        }
    }
}

// @jvfM$)9_GvSRx7
