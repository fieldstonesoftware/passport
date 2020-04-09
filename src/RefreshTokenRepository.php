<?php

namespace Laravel\Passport;

use DateTimeImmutable;
use Laravel\Passport\Contracts\RefreshTokenContract;

class RefreshTokenRepository
{
    /**
     * Creates a new refresh token.
     *
     * @param  mixed  $id
     * @param  mixed  $access_token_id
     * @param  bool  $revoked
     * @param  DateTimeImmutable  $expires_at
     * @return \Laravel\Passport\Contracts\RefreshTokenContract
     */
    public function create($id, $access_token_id, $revoked, $expires_at)
    {
        return Passport::refreshToken()->createRefreshToken($id, $access_token_id, $revoked, $expires_at);
    }

    /**
     * Gets a refresh token by the given ID.
     *
     * @param  string  $id
     * @return \Laravel\Passport\Contracts\RefreshTokenContract
     */
    public function find($id)
    {
        return Passport::refreshToken()->findById($id);
    }

    /**
     * Stores the given token instance.
     *
     * @param  \Laravel\Passport\Contracts\RefreshTokenContract  $token
     * @return void
     */
    public function save(\Laravel\Passport\Contracts\RefreshTokenContract $token)
    {
        $token->save();
    }

    /**
     * Revokes the refresh token.
     *
     * @param  string  $id
     * @return mixed
     */
    public function revokeRefreshToken($id)
    {
        $token = $this->find($id);
        if($token) return $token->revoke();
        return false;
    }

    /**
     * Revokes refresh tokens by access token id.
     *
     * @param  string  $tokenId
     * @return void
     */
    public function revokeRefreshTokensByAccessTokenId($tokenId)
    {
        Passport::refreshToken()->revokeWithAccessTokenId($tokenId);
    }

    /**
     * Checks if the refresh token has been revoked.
     *
     * @param  string  $id
     * @return bool
     */
    public function isRefreshTokenRevoked($id)
    {
        if ($token = $this->find($id)) {
            return $token->isRevoked();
        }

        return true;
    }
}
