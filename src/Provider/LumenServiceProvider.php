<?php

namespace Lsxiao\JWT\Provider;


use Illuminate\Support\ServiceProvider;
use Lsxiao\JWT\Auth\JWTGuard;
use Lsxiao\JWT\Middleware\Authenticate;
use Lsxiao\JWT\Middleware\RefreshToken;

class LumenServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //从应用根目录的config文件夹中加载用户的jwt配置文件
        $this->app->configure('jwt');

        //获取扩展包配置文件的真实路径
        $path = realpath(__DIR__ . '/../../config/jwt.php');

        //将扩展包的配置文件merge进用户的配置文件中
        $this->mergeConfigFrom($path, 'jwt');

        $this->app->routeMiddleware([
            'jwt.auth' => Authenticate::class,
            'jwt.refresh' => RefreshToken::class,
        ]);

        $this->app['auth']->extend('jwt', function ($app, $name, array $config) {
            $guard = new JWTGuard($app['auth']->createUserProvider($config['provider']), $app['request']);

            return $guard;
        });
    }
}