<?php

namespace App\Http\Controllers;

use App\Token;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Auth Controller
|--------------------------------------------------------------------------
|
| This class is an extension of AuthBaseController.
| All logic should be in the parent class AuthBaseController. When create a
| new function on parent class, then just overload it here as login or
| sign up examples.
|
|
*/

class AuthController extends AuthBaseController
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


    public static function getSessionToken(Request $request,callable $onSuccess,callable $onError) {
        $tokenValue = null;

        if ($request->has('session_token') ) {
            $tokenValue = $request->input('session_token');
        } else if ( $request->headers->has('session-token') ) {
            $tokenValue = $request->header('session-token');
        }

        $sessionToken = Token::where([
            ['type', 'session token'],
            ['value' , $tokenValue],
        ])->first();

        Token::session($sessionToken);

        if (is_null($sessionToken)) {
            return $onError($request);
        }

        return $onSuccess($request);
    }

    public static function unauthorizedSessionToken() {
        return response()
            ->json([
                'error' => [
                    'code' => 1,
                    'message' => 'Unauthorized session token.',
                ],
            ],401);
    }


    public function login(Request $request) {
        return self::getSessionToken(
            $request,
            function (Request $request) {
                return parent::login($request);
            },
            [self::class,'unauthorizedSessionToken']
        );
    }

    public function logout(Request $request) {
        return self::getSessionToken(
            $request,
            function (Request $request) {
                return parent::logout($request);
            },
            [self::class,'unauthorizedSessionToken']
        );
    }

    public function signup(Request $request) {
        return self::getSessionToken(
            $request,
            function (Request $request) {
                return parent::signup($request);
            },
            [self::class,'unauthorizedSessionToken']
        );
    }


}

