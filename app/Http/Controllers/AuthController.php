<?php

namespace App\Http\Controllers;

use App\Helpers\API\ResponseBuilder;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|confirmed',
        ]);

        if ($validator->fails()) {
            return ResponseBuilder::response(null, "data required missing", $validator->errors()->all(), ResponseBuilder::Validation_Error);
        }

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password')),
        ]);

        return ResponseBuilder::response(null, "New User Added Successfully", null, ResponseBuilder::Success);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return ResponseBuilder::response(null, null, $validator->errors()->all(), ResponseBuilder::Validation_Error);
        }

        $user = User::where('email', $request->email)->first();
        // print_r($data);
        if (!$user || !Hash::check($request->password, $user->password)) {
            return ResponseBuilder::response(null, "These credentials do not match our records", ["These credentials do not match our records"], ResponseBuilder::Success);
        }

        $token = $user->createToken('my-app-token')->plainTextToken;

        $response = [
            'user' => $user,
            'token' => $token
        ];

        return ResponseBuilder::response($response, "login data", null, ResponseBuilder::Success);
    }
}
