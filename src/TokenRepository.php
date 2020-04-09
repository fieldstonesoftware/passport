<?php

namespace Laravel\Passport;

use Laravel\Passport\Contracts\TokenContract;

class TokenRepository
{
    /**
     * Creates a new Access Token.
     *
     * @param $id
     * @param $userId
     * @param $clientId
     * @param $scopes
     * @param $revoked
     * @param $created_at
     * @param $updated_at
     * @param $expires_at
     * @return \Laravel\Passport\Contracts\TokenContract
     */
    public function create(
        $id, $userId, $clientId, $scopes, $revoked, $created_at, $updated_at, $expires_at
    ){
        return Passport::token()->createToken(
            $id, $userId, $clientId, $scopes, $revoked, $created_at, $updated_at, $expires_at
        );
    }

    /**
     * Get a token by the given ID.
     *
     * @param  string  $id
     * @return \Laravel\Passport\Contracts\TokenContract
     */
    public function find($id)
    {
        return Passport::token()->findById($id);
    }

    /**
     * Get a token by the given user ID and token ID.
     *
     * @param  string  $id
     * @param  int  $userId
     * @return \Laravel\Passport\Contracts\TokenContract|null
     */
    public function findForUser($id, $userId)
    {
        return Passport::token()->findForUser($id, $userId);
    }

    /**
     * Get the token instances for the given user ID.
     *
     * @param  mixed  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function forUser($userId)
    {
        return Passport::token()->forUserId($userId);
    }

    /**
     * Store the given token instance.
     *
     * @param  \Laravel\Passport\Contracts\TokenContract  $token
     * @return void
     */
    public function save(TokenContract $token)
    {
        $token->save();
    }

    /**
     * Revoke an access token.
     *
     * @param  string  $id
     * @return mixed
     */
    public function revokeAccessToken($id)
    {
        return Passport::token()->findById($id)->revoke();
    }

    /**
     * Check if the access token has been revoked.
     *
     * @param  string  $id
     * @return bool
     */
    public function isAccessTokenRevoked($id)
    {
        if ($token = $this->find($id)) {
            return $token->isRevoked();
        }

        return true;
    }

    /**
     * Find a valid token for the given user and client.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $user
     * @param  \Laravel\Passport\Contracts\ClientContract  $client
     * @return \Laravel\Passport\Contracts\TokenContract|null
     */
    public function findValidToken($user, $client)
    {
        return Passport::token()->findValidToken($user, $client);
    }
}
