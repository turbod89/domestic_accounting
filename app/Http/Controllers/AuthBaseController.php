<?php

namespace App\Http\Controllers;

use App\Token;
use App\User;
use Illuminate\Http\Request;

/*
 *
 *
 * */


class AuthBaseController extends BaseController
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

        // update session token
        Token::session()->user = $user;
        Token::session()->save();

        return response()
            ->json(['errors' => []]);
    }

    public function logout(Request $request) {

        // update session token
        Token::session()->delete();

        return response()
            ->json(['errors' => []]);
    }

    public function signup(Request $request) {
        $email = $request->input('email');
        $username = $request->input('username');
        $password = $request->input('password');

        // security
        $is_secure = true;
        $is_secure = $is_secure ? $this->checkPasswordStrength($password) : false;
        $is_secure = $is_secure ? $this->checkUsernameSecurity($username) : false;
        $is_secure = $is_secure ? $this->checkEmail($email) : false;

        if (!$is_secure) {
            return response()->json([
                'errors' => [
                    [
                        'code' => 1,
                        'message' => 'This credentials are not valid due to format or security.'
                    ],
                ]
            ],401);
        }

        // disposable
        $is_allowed = $is_secure;

        $is_allowed = $is_allowed ? (!User::where([['username',$username]])->exists()) : false;
        $is_allowed = $is_allowed ? (!User::where([['email', $email]])->exists()) : false;

        if (!$is_allowed) {
            return response()->json([
                'errors' => [
                    [
                        'code' => 1,
                        'message' => 'This credentials are not valid: username or email are not available.'
                    ],
                ]
            ],401);
        }

        // register

        $user = new User([
            'username' => $username,
            'email' => $email,
            'password' => $password,
        ]);

        $user->save();

        // login

        return $this->login($request);

    }

    private function checkEmail($email) {
        return is_string($email)
            && filter_var($email, FILTER_VALIDATE_EMAIL)
            && preg_match('/@.+\./', $email);
    }

    private function checkUsernameSecurity($username) {
        return is_string($username) && strlen($username) > 3;
    }

    private function checkPasswordStrength($password) {
        return is_string($password) && strlen($password) > 3;
    }
}

