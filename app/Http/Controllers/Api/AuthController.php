<?php

namespace App\Http\Controllers\Api;

use App\Enums\TokenAbility;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Traits\SendResponse;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    use SendResponse;

    public function UserRegister(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'password' => 'required',
            'user_role' => 'required|in:admin,client',
        ], [
            'required' => 'The :attribute field is required',
            'email' => 'The :attribute must be a valid email',
            'unique' => 'The :attribute already been taken',
            'string' => 'The :attribute must be a text',
            'in' => 'The :attribute only admin or client'
        ], [
            'username' => 'Username',
            'email' => 'Email',
            'password' => 'Password',
            'user_role' => 'Account Type'
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors();
            $response = $this->response_validate($errors); // membuat array yang berisi mesage
            return response()->json($response, 400);
        }

        try {
            DB::beginTransaction();
            $new_user = new User([
                'username' => $request->username,
                'email' => $request->email,
                'password' => bcrypt($request->password)
            ]);

            $role = Role::firstOrCreate(['name' => $request->user_role]);
            $new_user->save(); // simpan data user baru ke db 
            $new_user->assignRole($role); // menetapkan role user


            Log::info('SUCCESS: UserRegister', ['message' => 'new user has been added']);
            $response = $this->response_success(message: 'new user has been added', data: $new_user); // buat response

            DB::commit();
            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error('ERROR: UserRegister', ['error' => $e]);
            $response = $this->response_error(message: 'new user faild to added', data: null); // buat response
            DB::rollBack();
            return response()->json($response, 500);
        }
    }

    public function UserLogin(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'username_or_email' => 'required',
            'password' => 'required',
        ], [
            'required' => 'The :attribute field is required',
        ]);

        if ($validate->fails()) {
            $errors = $validate->errors();
            $response = $this->response_validate($errors); // membuat array yang berisi mesage
            return response()->json($response, 400);
        }
        
        $login_field = filter_var($request->username_or_email, FILTER_VALIDATE_EMAIL) ? 'email' : 'username'; // validasi apakah request email atau bukan 
        
        $auth_credentials = [
            $login_field => $request->username_or_email,
            'password' => $request->password,
        ];
        
        if (Auth::attempt($auth_credentials)) {
            /** @var \App\Models\User $user **/
            $user = Auth::user();
            $access_token = $user->createToken('access_token', [TokenAbility::ACCESS_API()->key()], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
            $refresh_token = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN()->key()], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));

            $res_data = [
                'access_token' => $access_token->plainTextToken,
                'refresh_token' => $refresh_token->plainTextToken,
                'user' => $user,
            ];

            Log::info('SUCCESS: UserLogin ', ['message' => 'user success authenticate']);

            $response = $this->response_success(message: 'user success authenticate', data: $res_data);
            return response()->json($response, 200);
        } else {
            Log::error('ERROR: UserLogin ', ['error' => 'user fail authenticate']);

            $response = $this->response_error(message: 'user fail authenticate', data: null);
            return response()->json($response, 500);
        }

    }

    public function RefreshToken(Request $request)
    {
        try {
            $access_token = $request->user()->createToken('access_token', [TokenAbility::ACCESS_API()->key()], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
            $response = $this->response_success(message: 'access token has been generated', data: ['access_token' => $access_token->plainTextToken]);
            return response()->json($response, 200);
        } catch (\Exception $e) {
            Log::error('ERROR: RefreshToken ', ['error' => $e]);

            $response = $this->response_error(message: 'access token fail to generated', data: null);
            return response()->json($response, 500);
        }
    }
}
