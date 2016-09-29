<?php
namespace Lsxiao\JWT\Auth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use InvalidArgumentException;
use Lsxiao\JWT\Builder;
use Lsxiao\JWT\Contracts\IClaimProvider;
use Lsxiao\JWT\Exception\BaseJWTException;
use Lsxiao\JWT\Exception\TokenNotFoundException;
use Lsxiao\JWT\Token;
use Lsxiao\JWT\Util\CacheUtil;
use Lsxiao\JWT\Util\ConfigUtil;
use Lsxiao\JWT\Util\Parser;

class JWTGuard implements Guard
{
    use GuardHelpers;

    /**
     * 请求实例
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     *
     * 请求中的token的查询参数名
     *
     * @var string
     */
    protected $inputKey;

    /**
     *
     * @var Token
     */
    protected $token;

    /**
     * 创建一个用于认证身份的Guard
     * JWTGuard constructor.
     * @param UserProvider $provider
     * @param Request $request
     */
    public function __construct(UserProvider $provider, Request $request)
    {
        $this->request = $request;
        $this->provider = $provider;
        $this->inputKey = 'token';
    }

    /**
     * 返回用户
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        //用户如果已经存在,直接返回
        if (!is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        try {
            $token = $this->parseToken();
        } catch (BaseJWTException $e) {
            return null;
        }

        if (!empty($token) && $token->isValid()) {
            $user = $this->provider->retrieveById($token->getClaim('sub')->getValue());
        }
        return $this->user = $user;
    }

    /**
     * 刷新token
     * @return bool|string
     */
    public function refreshToken()
    {
        if (!$this->getToken()->canRefresh()) {
            return false;
        }

        $user = $this->user();

        if ($user == null || !$user instanceof IClaimProvider) {
            return false;
        }

        //新建token
        $token = $this->fromUser($user);

        //添加到黑名单
        $this->addToBlacklist();

        //返回token
        return $token;
    }

    /**
     * 添加当前token到黑名单
     */
    public function addToBlacklist()
    {
        /**
         * @var $token Token
         */
        $token = $this->getToken();

        //身份标识
        $jwtId = $token->getClaim('jti')->getValue();

        //刷新过期unix时间戳
        $refreshExpireTime = $token->getClaim('rexp')->getValue();

        //当前unix时间戳
        $now = time();

        //计算duration,换算成分钟,缓存时间一定要比刷新过期时间长
        $cacheMinutes = ($refreshExpireTime - $now) / 60;

        //添加到黑名单
        CacheUtil::addToBlacklist($jwtId, $cacheMinutes);
    }

    /**
     * 生成一个新的token
     * @param IClaimProvider $user
     * @return string
     */
    public function newToken(IClaimProvider $user)
    {
        return $this->fromUser($user);
    }

    /**
     * 根据User获取Token
     * @param $user IClaimProvider
     * @return string
     */
    private function fromUser(IClaimProvider $user)
    {
        if (!$user instanceof IClaimProvider) {
            throw new InvalidArgumentException('the user has not implemented the IClaimProvider interface.');
        }

        $builder = new Builder();

        $now = time();

        $agloId = ConfigUtil::getAlgorithmId();
        $blacklistGraceTime = ConfigUtil::getBlackListGraceTime();
        $issuer = $this->request->url();
        $issueAt = $now;
        $expireAt = $now + ConfigUtil::getTTL() * 60;//有效期截止时间
        $refreshExpireAt = $now + ConfigUtil::getRefreshTTL() * 60;//刷新截止时间
        $notBefore = $issueAt;//有效期开始时间
        $jwtId = uniqid();
        $subject = $user->getIdentifier();
        $secretKey = ConfigUtil::getSecretKey();

        $token = $builder->algoId($agloId)
            ->issuer($issuer)
            ->issueAt($issueAt)
            ->expire($expireAt)
            ->refreshExpire($refreshExpireAt)
            ->notBefore($notBefore)
            ->blacklistGraceTime($blacklistGraceTime)
            ->subject($subject)
            ->jwtId($jwtId)
            ->secretKey($secretKey)
            ->build();

        return $token->toString();
    }


    /**
     * 返回本次请求的token
     * @return Token
     */
    public function getToken()
    {
        if (!is_null($this->token)) {
            return $this->token;
        }

        $token = $this->parseToken();

        return $this->token = $token;
    }

    /**
     * 从请求解析返回Token
     * @return Token
     * @throws TokenNotFoundException
     */
    private function parseToken()
    {
        $token = $this->request->query($this->inputKey);

        //token 为空,尝试从header中分析出token
        if (empty($token)) {
            $token = $this->request->bearerToken();
        }

        if (!isset($token)) {
            throw new TokenNotFoundException("not found token param from the request");
        }

        $token = Parser::parseToken($token);

        return $token;
    }

    /**
     * 验证用户的证书(账号密码或者Token)是否有效
     *
     * @param  array $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        $user = $this->findUser($credentials);

        if ($user) {
            return true;
        }

        return false;
    }

    /**
     * @param array $credentials
     * @return AuthenticatableContract|null
     */
    private function findUser(array $credentials = [])
    {
        return $user = $this->provider->retrieveByCredentials($credentials);
    }

    /**
     * 尝试通过账号密码登陆,返回token
     * @param array $credentials
     * @return bool|null|string
     */
    public function attempt(array $credentials = [])
    {
        $user = $this->provider->retrieveByCredentials($credentials);
        if (is_null($user)) {
            return false;
        }

        /** @var IClaimProvider $user */
        $token = $this->newToken($user);
        if ($token) {
            return $token;
        }
        return false;
    }

    /**
     * @param AuthenticatableContract $user
     * @return $this
     */
    public function setUser(AuthenticatableContract $user)
    {
        if (!$user instanceof IClaimProvider) {
            return $this;
        }

        $this->user = $user;

        return $this;
    }

    /**
     * @param Request $request
     */
    public function setRequest($request)
    {
        $this->request = $request;
    }

}