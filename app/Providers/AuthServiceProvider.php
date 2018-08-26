<?php

namespace App\Providers;

use App\Token;
use App\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Boot the authentication services for the application.
     *
     * @return void
     */
    public function boot()
    {
        // Here you may define how you wish users to be authenticated for your Lumen
        // application. The callback which receives the incoming request instance
        // should return either a User instance or null. You're free to obtain
        // the User instance via an API token or any other method necessary.

        $this->app['auth']->viaRequest('api', function ($request) {

            error_log('AuthServiceProvider/boot()');

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
                return null;
            }

            return $sessionToken->user;
        });
    }
}
