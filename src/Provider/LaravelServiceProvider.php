<?php

namespace Lsxiao\JWT\Provider;


use Illuminate\Support\ServiceProvider;
use Lsxiao\JWT\Auth\JWTGuard;

class LaravelServiceProvider extends ServiceProvider
{
    public function boot()
    {
        //获取扩展包配置文件的真实路径
        $path = realpath(__DIR__ . '/../../config/jwt.php');

        //发布配置,包用户执行Laravel的Artisan命令vendor:publish时，配置文件将会被拷贝到指定位置
        $this->publishes([
            $path => config_path('jwt.php'),
        ]);

        //将扩展包的配置文件merge进用户的配置文件中
        $this->mergeConfigFrom($path, 'jwt');

        $this->app['auth']->extend('jwt', function ($app, $name, array $config) {
            $guard = new JWTGuard($app['auth']->createUserProvider($config['provider']), $app['request']);

            return $guard;
        });
    }
}