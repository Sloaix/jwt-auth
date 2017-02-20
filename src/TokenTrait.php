<?php
namespace Lsxiao\JWT;

use Illuminate\Http\Request;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;

use Lsxiao\JWT\Util\Parser;
use Lsxiao\JWT\Builder;

trait TokenTrait
{
    /**
     * 从 request 里获取 token
     * @param $request Illuminate\Http\Request
     * @return Lsxiao\JWT\Token
     */
    public static function fromRequest(Request $request) {
        $TOKEN_KEY = config('jwt.token.key', '_token');

        $token = null;

        // 尝试利用旧值
        try {
            $token = $request->jwtToken();
        } catch (\Exception $er) {
            // ignore
        }

        if (!empty($token)) {
            return $token;
        }

        //尝试从查询参数或body中得到token
        $token = $request->input($TOKEN_KEY);

        //token 为空,尝试从header中分析出token
        if (empty($token)) {
            $token = $request->bearerToken();
        }

        if (!empty($token)) {
            try {
                $token = Parser::parseToken($token);
            } catch (\Lsxiao\JWT\Exception\BaseJWTException $e) {
                app('log')->error($e);
            }
        }

        // 利用 Macroable 复用 token
        $request->macro('jwtToken', function() use ($token){
            return $token;
        });

        return $token;
    }

    /**
     * 根据User获取Token
     * @param $user AuthenticatableContract
     * @return Lsxiao\JWT\Token
     */
    public static function fromUser(AuthenticatableContract $user)
    {
        if (!$user instanceof AuthenticatableContract) {
            throw new InvalidArgumentException('the user has not implemented the \Illuminate\Contracts\Auth\Authenticatable interface.');
        }

        $builder = new Builder();

        $now = time();

        $customClaims = [];
        if (property_exists($user, 'customClaims')) {
            $customClaims = $user->customClaims;
        }

        $agloId = ConfigUtil::getAlgorithmId();
        $blacklistGraceTime = ConfigUtil::getBlackListGraceTime();
        $issuer = $this->request->url();
        $issueAt = $now;
        $expireAt = $now + ConfigUtil::getTTL() * 60;//有效期截止时间
        $refreshExpireAt = $now + ConfigUtil::getRefreshTTL() * 60;//刷新截止时间
        $notBefore = $issueAt - 60;//有效期开始时间
        $jwtId = uniqid();
        $subject = $user->getAuthIdentifier();
        $secretKey = ConfigUtil::getSecretKey();

        if (isset($customClaims) && is_array($customClaims)) {
            foreach ($customClaims as $name => $value) {
                $builder->customClaim($name, $value);
            }
        }

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

        return $token;
    }

    /**
     * 刷新token
     * @return bool|string
     */
    public static function refreshToken(Request $request, $response = null)
    {
        $token = $request->jwtToken();
        if (empty($token) || !$token->canRefresh()) {
            return false;
        }
        $user = $request->user();
        if ($user == null || !$user instanceof AuthenticatableContract) {
            return false;
        }

        //添加到黑名单
        self::addToBlacklist($token);

        $newToken = self::fromUser($user);

        if (!empty($response)) {
            $response->headers->set('Authorization', 'Bearer ' . $newToken->toString());
        }

        return $newToken;
    }

    /**
     * 添加当前token到黑名单
     */
    private static function addToBlacklist($token)
    {
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
}