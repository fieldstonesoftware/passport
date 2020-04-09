<?php

namespace Laravel\Passport\Contracts;

use Illuminate\Support\Carbon;

interface AuthCodeContract
{
    /**
     * Get the client that owns the auth code.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client();

    /**
     * Store a new auth code.
     *
     * @param  string $id
     * @param  mixed $userId
     * @param  string $clientId
     * @param  string $scopes
     * @param  bool $revoked
     * @param  Carbon $expiresAt
     * @return AuthCodeContract
     */
    public function createAuthCode($id, $userId, $clientId, $scopes, $revoked, $expiresAt);

    /**
     * Find a client by its identifier.
     *
     * @param  $id
     * @return AuthCodeContract
     */
    public function findById($id);

    /**
     * Is this code revoked.
     *
     * @return bool
     */
    public function isRevoked();

    /**
     * Revoke the auth code.
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
     * @param  Carbon $expired
     * @return bool
     */
    public function deleteExpiredPriorTo(Carbon $expired);

    /**
     * Delete all revoked tokens and those expired prior to the date provided.
     *
     * @param  Carbon $expired
     * @return bool
     */
    public function deleteRevokedOrExpiredPriorTo(Carbon $expired);
}
