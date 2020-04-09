<?php

namespace Laravel\Passport\Contracts;

use DateTimeImmutable;
use Illuminate\Support\Carbon;

interface RefreshTokenContract
{
    /**
     * Get the client that the token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function accessToken();

    /**
     * Store a new token.
     *
     * @param  mixed  $id
     * @param  mixed  $access_token_id
     * @param  bool  $revoked
     * @param  DateTimeImmutable  $expires_at
     * @return \Laravel\Passport\Contracts\RefreshTokenContract
     */
    public function createRefreshToken($id, $access_token_id, $revoked, $expires_at);

    /**
     * Find a refresh token by its identifier.
     *
     * @param $id
     * @return RefreshTokenContract
     */
    public function findById($id);

    /**
     * Save the model.
     *
     * @return bool
     */
    public function save();

    /**
     * Revoke the token and save it.
     * @return bool
     */
    public function revoke();

    /**
     * Revoke all refresh tokens with this access token ID.
     *
     * @param $accessTokenId
     * @return mixed
     */
    public function revokeWithAccessTokenId($accessTokenId);

    /**
     * Is this token revoked
     *
     * @return bool
     */
    public function isRevoked();

    /**
     * Delete all revoked Tokens
     *
     * @return bool
     */
    public function deleteRevoked();

    /**
     * Delete expired tokens prior to the date provided.
     *
     * @param Carbon $expired
     * @return bool
     */
    public function deleteExpiredPriorTo(Carbon $expired);

    /**
     * Delete all revoked tokens and those expired prior to the date provided.
     *
     * @param Carbon $expired
     * @return bool
     */
    public function deleteRevokedOrExpiredPriorTo(Carbon $expired);
}
