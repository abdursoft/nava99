<?php

namespace App\Http\Controllers;

use App\Models\Refer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ReferController extends Controller
{
    /**
     * Add new refer code for user|agent|manager
     */
    public function add(Request $request){
        $validator = Validator::make($request->all(),[
            'code' => 'required|unique:refers,refer_code',
            'amount' => 'required|int',
            'status' => 'required|boolean',
            'user_id' => 'required',
            'account_type' => 'required'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'message' => 'Referal code couldn\'t create',
                'errors' => $validator->errors()
            ],400);
        }

        try {
            $type = $request->input('account_type');
            $id = $request->input('user_id');
            Refer::create([
                'refer_code' => $request->input('code'),
                'amount' => $request->input('amount'),
                'status' => $request->input('status'),
                'user_id' => $type === 'user' ? $id : 0,
                'agent_id' => $type === 'agent' ? $id : 0,
                'manager_id' => $type === 'manager' ? $id : 0,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Referal code couldn\'t create',
                'errors' => $th->getMessage()
            ],400);
        }
    }
}
