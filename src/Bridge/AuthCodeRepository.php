<?php

namespace Laravel\Passport\Bridge;

use Laravel\Passport\Passport;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;

class AuthCodeRepository implements AuthCodeRepositoryInterface
{
    use FormatsScopesForStorage;

    /**
     * {@inheritdoc}
     */
    public function getNewAuthCode()
    {
        return new AuthCode;
    }

    /**
     * {@inheritdoc}
     */
    public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
    {
        Passport::authCode()->createAuthCode(
             $authCodeEntity->getIdentifier(),
             $authCodeEntity->getUserIdentifier(),
             $authCodeEntity->getClient()->getIdentifier(),
             $this->formatScopesForStorage($authCodeEntity->getScopes()),
             false,
             $authCodeEntity->getExpiryDateTime()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function revokeAuthCode($codeId)
    {
        $authCode = Passport::authCode()->findById($codeId);
        if($authCode) $authCode->revoke();
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthCodeRevoked($codeId)
    {
        $authCode = Passport::authCode()->findById($codeId);
        if($authCode) return $authCode->isRevoked();
        return true;
    }
}
