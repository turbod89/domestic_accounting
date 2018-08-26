<?php

namespace App\Http\Controllers;

use App\Token;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TokenController extends BaseController
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

    public function getSessionToken(Request  $request) {

        $tokenValue = null;


        if ($request->has('api_token') ) {
            $tokenValue = $request->input('api_token');
        } else if ( $request->headers->has('api-token') ) {
            $tokenValue = $request->header('api-token');
        }

        $token = Token::where([
            ['type','api token'],
            ['value',$tokenValue],
        ])->first();

        if (is_null($token)) {
            return response()->json([
                'errors' => [
                    [
                        'code' => 1,
                        'message' => 'Unathorited API token.'
                    ],
                ]
            ],401);
        }

        $sessionToken = new Token([
            'type' => 'session token',
            'value' => md5(''.(1000*microtime(true))),
            'expire_at' => Carbon::now()->addDays(7),
        ]);

        $sessionToken->api = $token->api;
        $sessionToken->save();

        return response()->json([
                'session_token' => $sessionToken->only(['value','expire_at']),
            ]);
    }
}
