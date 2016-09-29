<?php

namespace Lsxiao\JWT\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Factory as Auth;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class Authenticate
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
        $this->auth->guard()->getToken()->validate();
        $this->auth->guard()->user();
        if ($this->auth->guard()->guest()) {
            throw new UnauthorizedHttpException('jwt');
        }

        return $next($request);
    }
}