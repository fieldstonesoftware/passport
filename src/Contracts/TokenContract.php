<?php

namespace Laravel\Passport\Contracts;

use Illuminate\Support\Carbon;

interface TokenContract
{
    /**
     * Get the client that the token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client();

    /**
     * Get the user that the token belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user();

    /**
     * Store a new token.
     *
     * @param  mixed  $id
     * @param  mixed  $userId
     * @param  mixed  $clientId
     * @param  string  $scopes
     * @param  bool  $revoked
     * @param  Carbon  $createdAt
     * @param  Carbon  $updatedAt
     * @param  Carbon  $expiredAt
     * @return \Laravel\Passport\Contracts\TokenContract
     */
    public function createToken($id, $userId, $clientId, $scopes, $revoked
        , $createdAt, $updateAt, $expiresAt);

    /**
     * Find a token by its identifier.
     *
     * @param $id
     * @return TokenContract
     */
    public function findById($id);

    /**
     * Save the model.
     *
     * @return bool
     */
    public function save();

    /**
     * Get a token by the given user ID and token ID.
     *
     * @param  $tokenId
     * @param  $userId
     * @return \Laravel\Passport\Contracts\TokenContract|null
     */
    public function findForUser($tokenId, $userId);

    /**
     * Return all tokens for the user id.
     *
     * @param \Illuminate\Database\Eloquent\Model $user
     * @param \Laravel\Passport\Contracts\ClientContract $client
     * @return \Laravel\Passport\Contracts\TokenContract|null
     */
    public function findValidToken($user, $client);

    /**
     * Return all tokens for the user id.
     *
     * @param string $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function forUserId($userId);

    /**
     * Is this token revoked
     *
     * @return bool
     */
    public function isRevoked();

    /**
     * Revoke the token and save it.
     *
     * @return bool
     */
    public function revoke();

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
