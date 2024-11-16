<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\SignupOTP;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "name" => "required|string",
            "email" => "required|email",
            "password" => "required|string|min:6"
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'User registration failed',
                'errors' => $validator->errors()
            ], 400);
        }

        $exists = User::where('email', $request->input('email'))->first();
        if ($exists && !$exists->is_verified) {
            $token = rand(1000, 9999);
            $user = User::where('email', $exists->email)->update(
                [
                    "otp" => $token,
                ]
            );
            Mail::to($request->input('email'))->send(new SignupOTP($token, "Use the OTP to verify your account"));
            $token = JWTAuth::createToken('otp_email', .5, null, $request->input('email'));
            return response()->json([
                'status' => false,
                'message' => 'A verification code has been sent to your email',
                'is_verified' => false,
                'otp_email' => $token
            ], 400);
        } elseif ($exists && $exists->is_verified) {
            return response()->json([
                'status' => false,
                'message' => 'This email already exist, Please login',
            ], 200);
        } else {
            true;
        }

        try {
            DB::beginTransaction();
            $token = rand(1000, 9999);
            $user = User::create(
                [
                    "name" => $request->input('name'),
                    "email" => $request->input('email'),
                    "otp" => $token,
                    "password" => password_hash($request->input('password'), PASSWORD_DEFAULT)
                ]
            );
            Mail::to($request->input('email'))->send(new SignupOTP($token, "Use the OTP to verify your account"));
            $token = JWTAuth::createToken('otp_email', .5, null, $request->input('email'));
            DB::commit();
            return response([
                'status' => true,
                'message' => 'A verification Code has been sent to your email',
                'otp_email' => $token
            ], 201);
        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'status' => "fail",
                'message' => 'User registration failed',
                'errors' => $th->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        try {
            $user = User::find($request->header('id'));
            User::where('id', $request->header('id'))->update([
                'name' => $request->input('name') ?? $user->name,
                'email' => $request->input('email') ?? $user->email,
                'password' => password_hash($request->input('password'), PASSWORD_DEFAULT) ?? $user->password,
                'image' => Storage::disk('public')->put("uploads/profile", $request->file('image')) ?? $user->image
            ]);
            return response()->json([
                'status' => true,
                'message' => 'Profile successfully updated'
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => 'Profile couldn\'t update',
            ], 400);
        }
    }

    /**
     * Profile image upload
     */
    public function profileImage(Request $request)
    {
        if ($request->hasFile('image')) {
            try {
                $user = User::find($request->header('id'));
                User::where('id', $request->header('id'))->update([
                    'image' => Storage::disk('public')->put("uploads/profile", $request->file('image')) ?? $user->image
                ]);
                if (!empty($user->image)) {
                    Storage::disk('public')->delete($user->image);
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Profile image successfully updated'
                ], 200);
            } catch (\Throwable $th) {
                return response()->json([
                    'status' => false,
                    'message' => 'Profile image couldn\'t update',
                    'errors' => $th->getMessage()
                ], 400);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Please provide an image with a post request',
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        //
    }

    /**
     * setup verification to the user
     */
    public function setupVerification($token)
    {
        try {
            $token = JWTAuth::verifyToken($token, false);
            $user = User::where('email', $token->email)->first();
            if ($user->is_verified) {
                return redirect('/user/login')->with(true, "Account already verified");
            } else {
                User::where('email', $token->email)->update([
                    'is_verified' => 1,
                    'otp' => null
                ]);
                return redirect('/user/login')->with(true, "Account successfully verified");
            }
        } catch (Exception $e) {
            return redirect('/user/login')->with('error', "Account couldn\'t verified");
        }
    }

    /**
     * Verifying signup otp
     */
    public function verifySignupOTP(Request $request)
    {
        $token = JWTAuth::verifyToken($request->input('otp_email'), false);
        try {
            $user = User::where('email', $token->email)->first();
            if ($request->input() != '' && $user->otp == $request->input('otp')) {
                User::where('id', $user->id)->update([
                    'otp' => '',
                    'is_verified' => 1
                ]);
                return response()->json(
                    [
                        'status' => true,
                        'message' => "Account successfully verified",
                    ],
                    200
                );
            } else {
                return response()->json(
                    [
                        'status' => false,
                        'message' => "OTP not matched"
                    ],
                    400
                );
            }
        } catch (\Throwable $th) {
            return response()->json(
                [
                    'status' => false,
                    'message' => "Authentication fail",
                    "error" => $th->getMessage()
                ],
                401
            );
        }
    }


    /**
     * Login the user with jwt token
     */
    public function login(Request $request)
    {
        $validator = Validator(
            $request->all(),
            [
                "phone" => 'exists:users,phone',
                "password" => 'required',
            ]
        );

        if ($validator->fails()) {
            return response()->json(
                [
                    'status' => false,
                    'message' => "Authentication fail",
                    "errors" => $validator->errors()
                ],
                401
            );
        }

        $user = User::where('phone', $request->input('phone'))->first();
        if ($user) {
            if (password_verify($request->input('password'), $user->password)) {
                if (!$user->is_verified) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Your account is not verified'
                    ], 400);
                } elseif ($user->is_blocked) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Your account has been blocked'
                    ], 400);
                } else {
                    $token = JWTAuth::createToken($user->role, 8740, $user->id, $user->email);
                    return response()->json([
                        'status' => true,
                        'message' => 'Login successful',
                        'token_type' => 'Bearer',
                        'token' => $token
                    ], 200);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Incorrect password'
                ], 400);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Login failed'
            ], 400);
        }
    }

    public function adminAuth()
    {
        return response()->json([
            'status' => true,
            'message' => 'Authenticated'
        ], 200);
    }

    public function logOut(Request $request)
    {
        $user = User::find($request->header('id'));
        return response()->json([
            'status' => true,
            'message' => 'Logout successful'
        ], 200);
    }

    public function webLogOut(Request $request)
    {
        $request->cookie('token', '', -1, '/');
        $request->headers->set('token', null);
        return redirect('/user/login')->with(true, 'You have successfully logout');
    }

    public function dashboard()
    {
        try {
            JWTAuth::verifyToken('token');
            return view('pages.users.dashboard');
        } catch (\Throwable $th) {
            return redirect('/user/login');
        }
    }

    public function delete(Request $request)
    {
        $user = User::find($request->header('id'));
        if (password_verify($request->input('password'), $user->password)) {
            $user->delete();

            return response()->json([
                'status' => true,
                'message' => 'Account has been deleted'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized access'
            ], 400);
        }
    }

    /**
     * Get user list
     */
    public function getUsers(Request $request)
    {
        try {
            return response()->json([
                'status' => true,
                'message' => 'User list successfully retrieved',
                'data' => User::where('role', '!=', 'admin')->get()
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 400);
        }
    }

    /**
     * Export users
     */
    public function export(Request $request)
    {
        try {
            $token = JWTAuth::verifyToken($request->query('token'),false);
            $admin = User::find($token->id);
            if($admin->role == 'admin'){
                header('Content-Type: text/csv');
                header('Content-Disposition: attachment; filename="user_export_list_'.date('Y-m-d H:i:s').'_.csv"');
                header('Pragma: no-cache');
                header('Expires: 0');

                $fp = fopen('php://output', 'w');
                $result = User::where('role','!=','admin')->select('name','email')->get()->toArray();
                if (!$result) die("Couldn't fetch records");
                $headers = array_keys($result[0]);

                if ($fp && $result) {
                    fputcsv($fp, $headers);
                    foreach($result as $item){
                        fputcsv($fp,$item);
                    }
                }
                fclose($fp);
                exit;
            }else{
                return response()->json([
                    'status' => false,
                    'Unauthorized access'
                ],400);
            }
        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'Unauthorized access or Invalid token'
            ],400);
        }
    }
}
