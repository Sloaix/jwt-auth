# 注意!!!

由于该库的主要目的是JWT的实现原理的技术分享，而我目前忙于公司的Android项目，暂没有时间维护当前扩展包(对Laravel5.4的兼容性不好)，并且扩展包稳定性还有待验证，不推荐使用到正式环境。

所以我推荐大家使用 https://github.com/tymondesigns/jwt-auth :)，如果你有JWT相关不懂的，可以提issue，大家一起探讨。

如果你想学习JWT的实现原理，我相信本项目应该会带给你一些帮助:),配合专栏食用更佳 https://zhuanlan.zhihu.com/p/22531819


# jwt-auth [![PHP version](https://badge.fury.io/ph/lsxiao%2Fjwt-auth.svg)](https://badge.fury.io/ph/lsxiao%2Fjwt-auth)
Laravel/Lumen JSON Web Token 认证扩展包

## 待完成

- [ ] 更加详细的单元测试
- [ ] 命令行生成HMAC RSA 秘钥

## 引入jwt-auth到项目中

```bash
composer require "lsxiao/jwt-auth"
```


## 使用方法

### 配置jwt-auth

#### Laravel
```bash
php artisan vendor:publish
```
jwt.php配置文件会被拷贝到项目根目录的config文件夹中

#### Lumen
在项目的lumen项目的根目录创建config文件夹,将```jwt.php```配置文件复制到此处
```php
<?php
return [
    /*
    |--------------------------------------------------------------------------
    | HMAC 签名秘钥
    |--------------------------------------------------------------------------
    |
    | HMAC 签名秘钥是用来为token进行HMAC签名的,必须在.env文件中设置。
    |
    */
    'secret_key' => env('JWT_SECRET_KEY'),
    
    /*
    |--------------------------------------------------------------------------
    | RSA 签名私钥
    |--------------------------------------------------------------------------
    |
    | RSA 签名私钥是用来为token进行RSA签名的,必须在.env文件中设置。
    |
    */
    'private_secret_key' => env('JWT_PRIVATE_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | RSA 签名公钥
    |--------------------------------------------------------------------------
    |
    | RSA 签名公钥是用来为token进行RSA签名解密的,必须在.env文件中设置。
    |
    */
    'public_secret_key' => env('JWT_PUBLIC_SECRET_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Token 有效期
    |--------------------------------------------------------------------------
    |
    | 指定token的有效时间(单位分钟),默认1小时。
    |
    */
    'ttl' => env('JWT_TTL', 60),

    /*
    |--------------------------------------------------------------------------
    | Token 刷新有效期
    |--------------------------------------------------------------------------
    |
    | 指定token过期后,多长一段时间内,使用过期的token能够刷新。默认为3周
    |
    */
    'refresh_ttl' => env('JWT_REFRESH_TTL', 30240),

    /*
    |--------------------------------------------------------------------------
    | JWT 算法ID
    |--------------------------------------------------------------------------
    |
    | Token HMAC签名的HASH算法
    | 对称算法:
    | HS256, HS384, HS512
    | 非对称算法,需提供公私钥:
    | RS256, RS384, RS512
    */
    'algorithm_id' => env('JWT_ALGORITHM', \Lsxiao\JWT\Singer\HMAC::DEFAULT_ALGO_ID),

    /*
    |--------------------------------------------------------------------------
    | 指定Token在某时间之前无法使用
    |--------------------------------------------------------------------------
    |
    | 指定一个时间增量(单位秒),在此签发时间+此事件增量时间之前,Token都不能使用
    |
    */
    'not_before=>' => env('JWT_NOT_BEFORE', 0),

    /*
    |--------------------------------------------------------------------------
    | 刷新Token次数差值
    |--------------------------------------------------------------------------
    |
    | 最新刷新次数会缓存在Server,如果客户端的token刷新次数与Server缓存相差大于此值,就会判定无效Token
    |
    */
    'refresh_diff_limit=>' => env('JWT_REFRESH_DIFF_LIMIT', 2),
    /*
    |--------------------------------------------------------------------------
    | 黑名单宽限时间,单位秒
    |--------------------------------------------------------------------------
    |
    | 每次刷新后,Token会被加入黑名单,在高并发的情况下,后续请求Token会无效,当设置宽限时间后,
    | Token刷新后,加入黑名单的Token只要处于宽限时间内,则是有效的。
    |
    */
    'blacklist_grace_time' => env('JWT_BLACK_LIST_GRACE_TIME', 30)
];

```

### 配置auth

#### Laravel
在config文件夹中找到auth.php

#### Lumen
将```auth.php```配置文件复制到config文件夹

修改如下:
```php
<?php
return [
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'),
    ],
    'guards' => [
        'api' => ['driver' => 'jwt'],//这里必须是jwt,由JWTGuard驱动
    ],
    'providers' => [
        //
    ],
];
```

### 开启认证
修改 bootstrap/app.php，取消 auth middleware 及 AuthServiceProvider 的注释

修改 app/Providers/AuthServiceProvider.php 的 boot 方法：
```php
public function boot()
{
    $this->app->configure('jwt');
    $this->app['auth']->viaRequest('api', function ($request) {
        $token = \Lsxiao\JWT\Token::fromRequest($request);

        if (!empty($token) && $token->isValid()) {
            $userid = $token->getClaim('sub')->getValue();
            return User::find($userid);
        }
    });
}
```

### 用户类
用户类 User 需要确认已实现 \Illuminate\Contracts\Auth\Authenticatable 接口，默认的 User 类即可

### 在Controller中根据账号密码获取Token
```php
public function login(Request $request)
{
    //通过user返回一个Token
    $credentials = $request->only('email', 'password');
    $user = User::where('email', $credentials[0])->where('password', $credentials[1])->first();
    $token = \Lsxiao\JWT\Token::fromUser($user);
    return response()->json(['token' => $token]);
}
```

### 在需要的地方刷新Token
Controller 中
```php
public function login(Request $request)
{
    //从请求取出证书,也就是邮件密码
    $token = \Lsxiao\JWT\Token::refreshToken($request);
    if (!$token) {
        throw new TokenInvalidException("refresh failed");
    }
    return response()->json(['token' => $token]);
}
```
Middleware 中
```php
public function handle($request, Closure $next, $guard = null)
{
    if ($this->auth->guard($guard)->guest()) {
        return response('Unauthorized.', 401);
    }

    $response = $next($request);

    // RefreshToken : reset HTTP Response Header
    \Lsxiao\JWT\Token::refreshToken($request, $response);

    return $response;
}
```

### 需要处理的异常
所有异常都继承自`Lsxiao\JWT\Exception\BaseJWTException`,建议在`App\Exceptions\Handler`处理异常,返回不同的HTTP status code
- `Lsxiao\JWT\Exception\SecretKeyException` 秘钥在.evn文件中不存在,秘钥不符合规范等
- `Lsxiao\JWT\Exception\TokenExpiredException` Token 过期
- `Lsxiao\JWT\Exception\TokenInvalidException` Token 无效
- `Lsxiao\JWT\Exception\TokenNotInRequestException` Token不存在于Request QueryParam或者Body或者Header中
- `Lsxiao\JWT\Exception\TokenParseException` token解析异常
- `Lsxiao\JWT\Exception\UnauthorizedException` 未授权异常


## 版本说明

- 1.0.4 (2016-11-21)
  - 修复hasBlacklistGraceTimeOrNotInBlacklist函数的bug。

- 1.0.3 (2016-11-21)
  - 修复Auth::refreshToken方法不能刷新成功的严重BUG

- 1.0.2 (2016-10-28)
  - 支持Laravel,提供LaravelServiceProvider

- 1.0.1 (2016-10-28)
  - 修复获取用户的时候没进行身份认证的BUG

- 1.0 (2016-9-29)
  - jwt基本功能提交



## 维护人
知乎 : [@面条](https://www.zhihu.com/people/lsxiao)

Github : [@lsxiao](https://github.com/lsxiao)


## 开源许可

    Copyright 2016 lsxiao, Inc.

    Licensed under the Apache License, Version 2.0 (the "License");
    you may not use this file except in compliance with the License.
    You may obtain a copy of the License at

       http://www.apache.org/licenses/LICENSE-2.0

    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS,
    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
    See the License for the specific language governing permissions and
    limitations under the License.
