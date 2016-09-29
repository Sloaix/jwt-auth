<?php

namespace Lsxiao\JWT\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;

class RefreshToken
{
    /**
     * The authentication guard factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory $auth
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }


    /**
     * @param $request
     * @param Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->auth->guard()->getToken()->refreshValidate();

        //刷新token,获得新token
        $newToken = $this->auth->guard()->refreshToken();

        $response = $next($request);

        if ($newToken) {
            $response->headers->set('Authorization', 'Bearer ' . $newToken);
        }

        return $response;
    }
}
