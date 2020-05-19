<?php

namespace Laravel\Passport\Contracts;

interface ClientContract
{
    /**
     * Get the user that the client belongs to.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user();

    /**
     * Get all of the authentication codes for the client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function authCodes();

    /**
     * Get all of the tokens that belong to the client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tokens();

    /**
     * Store a new client.
     *
     * @param  mixed $userId
     * @param  string $name
     * @param  string $redirect
     * @param  bool $personalAccess
     * @param  bool $password
     * @param  bool $confidential
     * @return \Laravel\Passport\Contracts\ClientContract
     */
    public function createClient($userId, $name, $redirect, $personalAccess = false, $password = false, $confidential = true);

    /**
     * Store a new personal access client based on the client.
     *
     * @return \Laravel\Passport\Contracts\PersonalAccessClientContract
     */
    public function createPersonalAccessClient();

    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey();

    /**
     * Get the value of the client secret.
     *
     * @return string
     */
    public function getClientSecret();

    /**
     * Get the grant types this client handles.
     * E.g. authorization_code, personal_access, password, client_credentials
     *
     * @return string|array
     */
    public function getGrantTypes();

    /**
     * Is this a personal access client
     *
     * @return bool
     */
    public function personalAccessClient();

    /**
     * Is this a password client
     *
     * @return bool
     */
    public function passwordClient();

    /**
     * Determine if the client should skip the authorization prompt.
     *
     * @return bool
     */
    public function skipsAuthorization();

    /**
     * Determine if the client is a confidential client.
     * If the secret is populated, return true.
     *
     * @return bool
     */
    public function isConfidential();

    /**
     * Return the client redirect
     *
     * @return string
     */
    public function getRedirect();

    /**
     * Return the client name
     *
     * @return string
     */
    public function getName();

    /**
     * Find a client by its identifier.
     *
     * @param $id
     * @return ClientContract
     */
    public function findById($id);

    /**
     * Get a token by the given user ID and token ID.
     *
     * @param  $clientId
     * @param  $userId
     * @return \Laravel\Passport\Contracts\ClientContract|null
     */
    public function findForUser($clientId, $userId);

    /**
     * Get the client instances for the given user ID.
     *
     * @param  mixed  $userId
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function forUser($userId);

    /**
     * Is this token revoked
     *
     * @return bool
     */
    public function isRevoked();

    /**
     * Update the name and redirect attributes of the client and save it.
     *
     * @param  string  $name
     * @param  string  $redirect
     * @return \Laravel\Passport\Contracts\ClientContract
     */
    public function updateNameAndRedirect($name, $redirect);

    /**
     * Regenerate the client secret and save it in the client.
     *
     * @return \Laravel\Passport\Contracts\ClientContract
     */
    public function regenerateSecret();

    /**
     * Revoke all tokens associated with the client.
     *
     * @return int
     */
    public function revokeTokens();

    /**
     * Revoke the client.
     *
     * @return bool
     */
    public function revoke();
}
