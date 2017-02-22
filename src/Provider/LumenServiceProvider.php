<?php

namespace Lsxiao\JWT\Provider;


use Illuminate\Support\ServiceProvider;
use Lsxiao\JWT\Auth\JWTGuard;
use Lsxiao\JWT\Middleware\Authenticate;
use Lsxiao\JWT\Middleware\RefreshToken;

use Lsxiao\JWT\Token;

class LumenServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //从应用根目录的config文件夹中加载用户的jwt配置文件
        $this->app->configure('jwt');

        $this->app['auth']->viaRequest('jwt', function ($request) {

            $token = Token::fromRequest($request);

            if (!empty($token) && $token->isValid()) {
                $userid = $token->getClaim('sub')->getValue();
                return User::find($userid);
            }
        });
    }
}