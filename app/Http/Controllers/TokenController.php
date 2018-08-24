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

    public function getToken(Request  $request) {

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
            return response()->json(['error' => [
                'code' => 1,
                'message' => 'Unathorited API token.'
            ]],401);
        }

        $userToken = new Token([
            'type' => 'user token',
            'value' => md5(''.(1000*microtime(true))),
            'expire_at' => Carbon::now()->addDays(7),
        ]);

        $userToken->api = $token->api;
        $userToken->save();

        return response()->json(['user_token' => $userToken->value]);
    }
}
