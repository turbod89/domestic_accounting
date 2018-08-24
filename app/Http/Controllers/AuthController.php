<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AuthController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function login(Request $request) {
        $username = $request->input('username');
        $password = $request->input('password');

        $user = User::where([
            ['username', $username],
            ['hashed_password', md5($password)],
        ])->first();

        if (is_null($user)) {
            return response()->json([
                'errors' => [
                    [
                        'code' => 1,
                        'message' => 'Wrong username or password',
                    ],
                ],
            ],401);
        }

        // $token

        return response()
            ->json(['errors' => []]);
    }
}

